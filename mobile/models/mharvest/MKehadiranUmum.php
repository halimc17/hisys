<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MKehadiranUmum extends OWL_Model
{
    function __construct()
    {
        parent::__construct();
        $d['table'] = array("kebun_aktifitas_mobile", "sdm_absensi");
        $d['key'] = array("notransaksi");
        $this->prepareDB = $d;
    }

    function init()
    {
        $result = false;
        foreach ($this->prepareDB['table'] as $tbl) {
            if (!$this->table_exists($tbl)) {
                $this->response['status'] = 400;
                $this->response['error'] = true;
                $this->response['message'] = "Tabel " . $tbl . " belum tersedia!";
                $result = $this->response;
                break;
            }
        }
        return $result;
    }

    public function addHeader($user, $type)
    {

        try {
            $noref_rawat = $this->post('notransaksi_rawat');
            $noref_panen = $this->post('notransaksi_panen');

            // Tentukan nilai 'noreferensi' dan 'status' berdasarkan kondisi yang diberikan
            if ($noref_rawat != null && $noref_rawat != 'null') {
                $data['noreferensi'] = $noref_rawat;
                $data['status'] = 'BKM';

                // Aktifitas
                $aktifitasrawat = $this->getAktifitas("WHERE notransaksi = '" . $data['noreferensi'] . "' AND syn = '1' LIMIT 1");

                if ($aktifitasrawat and $aktifitasrawat->rowCount() == 0) {
                    throw new PDOException("Transaksi rawat '" . $data['noreferensi'] . "' Belum di Sinkronisasi");
                }
            }

            if ($noref_panen != null && $noref_panen != 'null') {
                $data['noreferensi'] = $noref_panen;
                $data['status'] = 'PNN';

                // Aktifitas
                $aktifitaspanen = $this->getAktifitas("WHERE notransaksi = '" . $data['noreferensi'] . "' AND syn = '1' LIMIT 1");

                if ($aktifitaspanen and $aktifitaspanen->rowCount() == 0) {
                    throw new PDOException("Transaksi panen '" . $data['noreferensi'] . "' Belum di Sinkronisasi");
                }
            }


            if (($noref_rawat == null || $noref_rawat == 'null') && ($noref_panen == null || $noref_panen == 'null')) {

                // Aktifitas
                $aktifitasrawat = $this->getAktifitas("WHERE nikmandor = '" . $this->post('mandor') . "' AND tanggal = '" . $this->post('tanggal_kehadiran')  . "' AND tipetransaksi = 'BKM' AND syn = '1' LIMIT 1");
                $datarawat = $aktifitasrawat->fetch();

                if ($aktifitasrawat->rowCount() != 0) {
                    $data['noreferensi'] = $datarawat->notransaksi;
                    $data['status'] = 'BKM';
                }
            }

            $data['notransaksi']    = $this->post('notransaksi');
            $data['tipetransaksi']  = $type;
            $data['tanggal']        = $this->post('tanggal_kehadiran');
            $data['kodeorg']        = (null !== $this->post('kodeorg') && !empty($this->post('kodeorg'))) ? substr($this->post('kodeorg'), 0, 4) : $user['lokasitugas'];
            $data['divisi']         = (null !== $this->post('kodeorg') && !empty($this->post('kodeorg'))) ? $this->post('kodeorg') : $user['subbagian'];
            $data['gangcode']       = $this->post('gangcode');
            $data['nikmandor']      = $this->post('mandor');
            $data['nikmandor1']     = $this->post('mandor1');
            $data['nikasisten']     = $this->post('asisten');
            $data['kerani']         = $this->post('kerani_perawatan');
            $data['deviceid']       = $user['uuid'];
            $data['createby']       = $user['userid'];
            $data['createtime']     = $this->post('lastupdate');
            $data['updateby']       = $user['userid'];
            
            if ($this->uri->segments[5] == 'load') $data;

            // Aktifitas
            $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");

            // Exec insert and checking data
            if ($aktifitas and $aktifitas->rowCount() == 0) {
                $qexec = $this->insert($data, $this->db->dbname . ".kebun_aktifitas_mobile");

                if ($qexec) {
                    // if (true) {
                    $dt = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1")->fetch();
                    $this->response['error'] = false;
                    $this->response['message'] = "Success";
                    $this->response['notransaksi'] = $dt->notransaksi;
                    $this->response['no_syncronized'] = $dt->nosync;
                    $this->response['tanggal'] = $dt->tanggal;
                }
            } else {
                $dt = $aktifitas->fetch();
                if ($dt->syn == "1") {
                    return $this->responseError("Warning : Data sudah tersyncronize.", 403);
                }elseif ($dt->flag == "1") {
                    return $this->responseError("Warning : Data Transaksi sudah terposting.", 403);
                } else {
                    $this->reSyntransMobile($this->post('notransaksi'));
                    return $this->responseSuccess("Data successfully re-synchronized!", [
                        'notransaksi' => $dt->notransaksi,
                        'no_syncronized' => $dt->nosync,
                        'tanggal' => $dt->tanggal
                    ]);
                }
            }
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Gagal Insert Header (" . $e->getMessage() . ") !!";
        }
        return $this->response;
    }

    public function addDetail($user)
    {
        try {
            $data['method']             = $this->post('method');
            $data['notransaksi']        = $this->post('notransaksi');
            $data['tanggal']            = explode(",", $this->post('tanggal'));
            $data['tanggal_kehadiran']  = explode(",", $this->post('tanggal_kehadiran'));
            $data['karyawanid']         = explode(",", $this->post('karyawanid'));
            $data['absensi']            = explode(",", $this->post('absensi'));
            $data['jumlah_hk']          = explode(",", $this->post('hk'));
            $data['insentif']           = explode(",", $this->post('premi'));
            $data['createtime']         = explode(",", $this->post('lastupdate'));
            $data['keterangan']         = explode(",", $this->post('keterangan'));
            $data['createby']           = $user['userid'];
            $Setup_app = $this->model('Setup_app');
            $getParam = $Setup_app->getParamAppM($this->user);
            $nilai = $getParam[array_search('mblnoakun', array_column($getParam, 'kodeparameter'))]['nilai'] ?? '';
            $dataHeader = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
            // if ($dataHeader and count($dataHeader) > 0 and trim($this->post('blok')) != "") {
            if ($dataHeader and count($dataHeader) != 0) {
                $maxNum = $this->getDetail("WHERE notransaksi = '" . $data['notransaksi'] . "' ")->rowCount();
                for ($i = 0; $i < count($data['karyawanid']); $i++) {
                    $maxNum++;
                    $dataKehadiran = array(
                        'notransaksi'   => $this->post('notransaksi'),
                        'nobkm'         => '',
                        'nourut'        => $maxNum,
                        'nik'           => $data['karyawanid'][$i],
                        'absensi'       => $data['absensi'][$i],
                        'jhk'           => $data['jumlah_hk'][$i],
                        'insentif'      => $data['insentif'][$i],
                        'hasilkerja'    => '',
                        'keterangan'    => $data['keterangan'][$i],
                        'noakun'        => $nilai,
                    );
                    $dataInsert[$i] = $this->query_insert($dataKehadiran, $this->db->dbname . ".sdm_absensi");
                }
                // console
                if ($this->uri->segments[5] == 'load') {
                    return $dataInsert;
                }
                $qexec = $this->exec($dataInsert);
                if ($qexec) {
                    $this->response['error'] = false;
                    $this->response['message'] = "Success";
                    $this->response['notransaksi'] = $this->post('notransaksi');
                    $this->response['no_syncronized'] = $this->post('no_syncronized');
                    $this->response['tanggal'] = $this->post('tanggal');
                }
            } else {
                $this->response['status'] = 404;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : No Transaksi '" . $data['notransaksi'] . "' Pada Header Tidak ditemukan";
            }
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! Insert Data : No Transaksi '" . $data['notransaksi'] . "' :(" . $e->getMessage() . ")";
        }
        return $this->response;
    }

    //? Check total rows are equals
    public function checkdatarow()
    {
        try {
            $data['notransaksi']    = $this->post('notransaksi');
            $jmldetail              = $this->post('jumlah_detail');

            $dataHeader = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
            if ($dataHeader and count($dataHeader) > 0) {
                $str = "select  notransaksi from " . $this->db->dbname . ".sdm_absensi where notransaksi = '" . $data['notransaksi'] . "' ";
                // console
                if ($this->uri->segments[5] == 'load') {
                    return $str;
                }
                $datacheck = $this->query($str);
                if ($datacheck->rowCount() == $jmldetail) {
                    $dataUpdate = array(
                        "syn" => "1"
                    );
                    $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $data['notransaksi'] . "'");
                    $this->response['error'] = false;
                    $this->response['message'] = "Sinkronisasi Data Telah Selesai.";
                    $this->response['notransaksi'] = $this->post('notransaksi');
                    $this->response['no_syncronized'] = $this->post('no_syncronized');
                    $this->response['tanggal'] = $this->post('tanggal');
                } else {
                    $this->response['status'] = 409;
                    $this->response['error'] = true;
                    $this->response['message'] = "Failed! : Data Syncronized (" . $datacheck->rowCount() . "/" . $jmldetail . ") Belum Lengkap, Mohon Syncronized Ulang";
                }
            } else {
                $this->response['status'] = 404;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : No Transaksi '" . $data['notransaksi'] . "' Pada Header Tidak ditemukan";
            }
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! Insert Data : No Transaksi '" . $data['notransaksi'] . "' :(" . $e->getMessage() . ")";
        }
        return $this->response;
    }

    public function getAktifitas($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }

    //? Get Prestasi
    private function getDetail($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".sdm_absensi {$where}";
        $data = $this->query($q);
        return $data;
    }

    //? Re Synchronize
    private function reSyntransMobile($notransaksi)
    {
        try {
            $dataUpdate = array(
                "syn" => "0"
            );
            $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $notransaksi . "' ");
            $this->delete($this->db->dbname . ".sdm_absensi", "notransaksi='" . $notransaksi . "'");
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Resyncronized (" . $e->getMessage() . ") !!";
        }
    }

    public function getHeaders($user, $type)
    {
        $filters = [
            'dateFrom' => "tanggal >= ':value'",
            'dateTo' => "tanggal <= ':value'",
            'notransaksi' => "notransaksi = ':value'",
            'divisi' => "divisi = ':value'",
            'periode' => "tanggal LIKE '%:value%'",
            'mandor' => "nikmandor = ':value'"
        ];
        // $kodeorg = $this->post('kodeorg') ?: $user['lokasitugas'];

        $whr = '';
        foreach ($filters as $key => $condition) {
            $value = $this->post($key) ?: '';
            if ($value) {
                if ($key == 'periode') {
                    $value = substr($value, 0, 7);
                }
                $condition = str_replace(':value', $value, $condition);
                $whr .= " AND $condition";
            }
        }
        $whr .= $this->post('kodeorg') ? " AND kodeorg IN ('" . str_replace(',', "','", $this->post('kodeorg')) . "')" : '';

        if ($this->post('dateFrom') && $this->post('dateTo')) {
            $whr .= " AND tanggal BETWEEN '" . $this->post('dateFrom') . "' AND '" . $this->post('dateTo') . "'";
        }

        $whr .= " AND noreferensi IN (
                SELECT notransaksi
                FROM kebun_aktifitas_mobile where tipetransaksi='BKM' AND syn='1' and flag='0'
                )
                AND noreferensi NOT IN (
                SELECT notransaksi
                FROM kebun_prestasi_mobile)  ";

        // $Qsql = 'WHERE kodeorg="' . $kodeorg . '" AND tipetransaksi="' . $type . '" AND syn="1" AND flag="0"' . $whr . ' ORDER BY tanggal DESC ';
        $Qsql = 'WHERE tipetransaksi="' . $type . '" AND syn="1" AND flag="0"' . $whr . ' ORDER BY tanggal DESC ';        

        $getData = $this->getAktifitas($Qsql);
        $val = [];
        $nourut = 0;
        $dataKodeorg = [];
        if (count($getData) > 0) {
            foreach ($getData as $key => $value) {
                $nourut++;
                $val[$key] = $value;
                $dataKodeorg[$value->kodeorg][] = $value->kodeorg;
            }
        }

        $dataKodeorgCount = [];
        foreach ($dataKodeorg as $kodeorg => $items) {
            $dataKodeorgCount[$kodeorg] = count($items);
        }

        $informasi = [
            'datatransaksi' => $dataKodeorgCount,
            'jumlahdata' => $nourut,
        ];

        if ($val) {
            return $this->responseSuccess("Data header", [
                'data' => $val,
                'informasi' => $informasi,
            ]);
        } else {
            return $this->responseError("Data Tidak ada", 404);
        }

        // $this->response['error']    = !$val;
        // $this->response['message']  = $val ? "Data header" : "Data Tidak ada";
        // $this->response['data']     = $val ?: null;
        // $this->response['status']   = $val ? 200 : 404;

        // return $this->response;
    }

    public function getDetails()
    {
        $notransaksi = $this->post('notransaksi') ?: '';
        if (!$notransaksi) {
            return $this->responseError("Parameter notransaksi Harus diisi", 400);
        }

        $Qsql = 'WHERE notransaksi="' . $notransaksi . '" AND tipetransaksi="ABS" AND syn="1" AND flag="0"';
        $getHeader = $this->getAktifitas($Qsql)->fetchAll();

        if (empty($getHeader)) {
            return $this->responseError("notransaksi {$notransaksi} tidak ditemukan", 404);
        }

        $dataHeader = $getHeader[0];
        $v = $this->getDetail("where notransaksi = '$notransaksi' ")->fetchAll();

        $this->response = [
            'error' => empty($v),
            'message' => $v ? "Data Detail" : "Data Tidak ada",
            'header' => $dataHeader,
            'sdm_absensi' => $v ?: null,
            'status' => $v ? 200 : 404
        ];
        return $this->response;
    }


    public function updateFlag()
    {
        $notransaksi = $this->post('notransaksi') ?: '';
        $flag = $this->post('flag');

        if (!$notransaksi || $flag == null) {
            return $this->responseError("Parameter notransaksi & flag harus diisi", 400);
        }

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '$notransaksi' LIMIT 1");
        if (!$aktifitas || $aktifitas->rowCount() == 0) {
            return $this->responseError("Data tidak ada", 404);
        }

        $dt = $aktifitas->fetch();
        if ($flag == 1 && ($dt->flag == '1' || $dt->syn == '0')) {
            $message = $dt->flag == '1' ? "Failed! : data already posted !!" : "Data belum selesai Di Syncronize";
            return $this->responseError($message, 400);
        }

        $this->update(['flag' => $flag], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='$notransaksi' AND tipetransaksi='ABS'");
        $this->update(['flag' => $flag], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='$dt->noreferensi' AND tipetransaksi='BKM'");
        return $this->responseSuccess("Data successfully updated !!");
    }

    private function responseError($message, $status)
    {
        return [
            'status' => $status,
            'error' => true,
            'message' => $message
        ];
    }

    private function responseSuccess($message, $data = [])
    {
        $response = array_merge([
            'status' => 200,
            'error' => false,
            'message' => $message,
        ]);
        return array_merge($data, $response);
    }
}
