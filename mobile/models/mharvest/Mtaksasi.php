<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mtaksasi extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_2taksasi_dt");
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
        $data['notransaksi']        = $this->post('notransaksi');
        $data['tipetransaksi']      = $type;
        $data['tanggal']            = $this->post('tanggal_taksasi_panen');
        $data['kodeorg']            = substr($this->post('divisi'), 0, 4);
        if (empty($data['kodeorg']) or $data['kodeorg'] == "" or $data['kodeorg'] == null) {
            $data['kodeorg'] = substr($data['divisi'], 0, 4);
        }
        $data['divisi']             = $this->post('divisi');
        if (empty($data['divisi']) or $data['divisi'] == "" or $data['divisi'] == null) {
            $data['divisi'] = $user['subbagian'];
        }
        $data['gangcode']           = $this->post('mandor');
        $data['nikmandor']          = $this->post('mandor');
        // $data['no_syncronized']     = $this->post('notransaksi');
        $data['createby']           = $user['userid'];
        $data['updateby']           = $user['userid'];
        $data['deviceid']           = $user['uuid'];
        $data['createtime']         = $this->post('lastupdate');

        if ($this->uri->segments[5] == 'load') return $data;
        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() == 0) {
            $qexec = $this->insert($data, $this->db->dbname . ".kebun_aktifitas_mobile", false);
            if ($qexec) {
                $dt = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1")->fetch();
                return $this->responseSuccess("Success Insert Header", [
					'notransaksi' => $dt->notransaksi,
					'no_syncronized' => $dt->nosync,
					'tanggal' => $dt->tanggal
				]);
            }
        } else {
            $dt = $aktifitas->fetch();
			if ($dt->syn == "1") {
				return $this->responseError("Warning : Data sudah tersyncronize.", 403);
			}elseif ($dt->flag == "1") {
				return $this->responseError("Warning : Data Panen sudah terposting.", 403);
			} else {
                $this->reSyntransMobile($this->post('notransaksi'), $type);
				return $this->responseSuccess("Success Re-Syncronized", [
					'notransaksi' => $dt->notransaksi,
					'no_syncronized' => $dt->nosync,
					'tanggal' => $dt->tanggal
				]);
			}
        }
    }

    public function addDetail($user)
    {
        $data['notransaksi']        = $this->post('notransaksi');
        $data['tanggal']            = $this->post('tanggal');
        $data['no_syncronized']     = $this->post('no_syncronized');
        // DATA ARRAY ===
        $data['blok']               = explode(",", $this->post('blok'));
        $data['luas_blok']          = explode(",", $this->post('luas_blok'));
        $data['sph']                = explode(",", $this->post('sph'));
        $data['akp']                = explode(",", $this->post('akp'));
        $data['bjr']                = explode(",", $this->post('bjr'));
        $data['luas_panen']         = explode(",", $this->post('luas_panen'));
        $data['jjg']                = explode(",", $this->post('janjang'));
        $data['kg']                 = explode(",", $this->post('kilogram'));
        $data['tk_panen']           = explode(",", $this->post('tk_panen'));
        $data['lastupdate']         = explode(",", $this->post('lastupdate'));

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
        //! Tambah validasi, cek tiap blok sudah ada luas AKP nya. kalo belum transaksi !200 kasih pesan error.
        $isExists = false;
        for ($j = 0; $j < count($data['blok']); $j++) {
            $resp = $this->cekLuasAkp($data['blok'][$j], $data['tanggal']);
            if ($resp) {
                $isExists = true;
                break;
            }
        }
        if ($isExists) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Terdapat data Akp yang belum di sinkronisasi!";
        } else if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('blok')) != "") {
            // $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();
            for ($i = 0; $i < count($data['blok']); $i++) {
                $dataArr = array(
                    'notransaksi'   => $data['notransaksi'],
                    'tanggal'       => $data['tanggal'],
                    'blok'          => $data['blok'][$i],
                    'luasblok'      => $data['luas_blok'][$i],
                    'luaspanen'     => $data['luas_panen'][$i],
                    'bjr'           => $data['bjr'][$i],
                    'jjg'           => $data['jjg'][$i],
                    'kg'            => $data['kg'][$i],
                    'tk_panen'      => $data['tk_panen'][$i],
                    'akp'           => $data['akp'][$i],
                    'sph'           => $data['sph'][$i],
                    'output'        => number_format((float)($data['kg'][$i] / $data['tk_panen'][$i]), 2, '.', ''),
                    'manual'        => 0,
                    'createby'      => $user['userid'],
                    'createtime'    => $data['lastupdate'][$i],
                    'updateby'      => $user['userid'],
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_2taksasi_dt");
            }
            // console
            if ($this->uri->segments[5] == 'load') {
                return $dataInsert;
            }
            $qexec = $this->exec($dataInsert);
            if ($qexec) {
                $this->response['error'] = false;
                $this->response['message'] = "Success Insert Detail";
                $this->response['notransaksi'] = $this->post('notransaksi');
                $this->response['no_syncronized'] = $this->post('no_syncronized');
                $this->response['tanggal'] = $this->post('tanggal');
            }
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }

    public function checkdatarow()
    {
        $jmldetail  = $this->post('jumlah_detail');
        $str        = "select notransaksi from " . $this->db->dbname . ".kebun_2taksasi_dt where notransaksi = '" . $this->post('notransaksi') . "' ";
        // console
        if ($this->uri->segments[5] == 'load') {
            return $str;
        }
        $dtlcheck = $this->query($str);
        if ($dtlcheck->rowCount() == (int)$jmldetail) {
            $dataUpdate = array(
                "syn" => "1"
            );
            $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $this->post('notransaksi') . "'");
            $this->response['error'] = false;
            $this->response['message'] = "Sinkronisasi Data Telah Selesai.";
            $this->response['notransaksi'] = $this->post('notransaksi');
            $this->response['no_syncronized'] = $this->post('no_syncronized');
            $this->response['tanggal'] = $this->post('tanggal');
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Syncd (" . "detail: " . $dtlcheck->rowCount() . "/" . $jmldetail . ") Belum Lengkap, Mohon Sync Ulang";
        }
        return $this->response;
    }

    function getAktifitas($where = '')
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }

    private function reSyntransMobile($notransaksi, $type)
    {
        try {
            $dataUpdate = array(
                "syn" => "0"
            );
            $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $this->post('notransaksi') . "' and tipetransaksi = '" . $type . "'");
            $this->delete($this->db->dbname . ".kebun_2taksasi_akp", "notransaksi='" . $notransaksi . "'");
            $this->delete($this->db->dbname . ".kebun_2taksasi_akp_dt", "notransaksi='" . $notransaksi . "'");
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : (" . $e->getMessage() . ") !!";
        }
    }

    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "' AND tipetransaksi = 'AKP' order by tanggal desc " . $limitPage;
        // $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where tipetransaksi = 'TKS' order by tanggal desc " . $limitPage;
        //
        $data = $this->query($q, 'ASSOC');
        return $data;
    }

    function selectdata(array $pageLimit = array())
    {
        $result = array();
        $data = $this->selectQuery($pageLimit);
        if ($data and $data->rowCount() > 0) {
            $result = $this->fetch($data);
        }
        return $result;
    }


    private function cekLuasAkp($blok, $tgl)
    {
        $data = false;
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_2taksasi_akp where blok = '$blok' and tanggal = '$tgl' and (akp = '' or akp = ' ' or akp is null)";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = true;
        }
        return $data;
    }

    function getDataDtl($where)
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile " . $where . " order by nikmandor desc";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            //CEK DATA
            // foreach ($r as $key => $value) {
            //     $num = 1;
            //     echo "DATA MASTER KE: " . $num;
            //     echo "\n";
            //     echo "\n";
            //     print_r($value);
            //     echo "\n";
            //     echo "\n";
            //     $num++;
            // }

            for ($i = 0; $i < count($r); $i++) {
                //! SEARCH DATA FROM TBL KEBUN_2TAKSASI_DT
                $qTss = "SELECT * FROM " . $this->db->dbname . ".kebun_2taksasi_dt where notransaksi = '{$r[$i]['notransaksi']}'";
                // echo $qTss;
                $rTss = $this->fetchdata($qTss);
                if (count($rTss) > 0) {
                    $temp = array();

                    $dk = $this->model('Setup_datakaryawan');
                    $nmk = $dk->selectPemanenNm("where karyawanid='{$r[$i]['nikmandor']}'");

                    $luasblok = 0.00;
                    $luaspanen = 0.00;
                    $totjjg = 0.00;
                    $tothk = 0.00;
                    $output = 0.00;
                    // $daya = 0.00;
                    $totProd = 0.00;

                    for ($j = 0; $j < count($rTss); $j++) {
                        $luasblok = $luasblok + (float)$rTss[$j]['luasblok'];
                        $luaspanen = $luaspanen + (float)$rTss[$j]['luaspanen'];
                        $totjjg = $totjjg + (float)$rTss[$j]['jjg'];
                        $tothk = $tothk + (float)$rTss[$j]['tk_panen'];
                        // $totoutput = $totoutput + (float)$rTss[$j]['output'];
                        // $daya = $daya + ((float)$rTss[$j]['luaspanen'] / (float)$rTss[$j]['tk_panen']);
                        $totProd = $totProd + number_format(((float)$rTss[$j]['jjg'] ?? 0 * (float)$rTss[$j]['bjr'] ?? 0), 2, '.', '');

                        $div = substr($rTss[$j]['blok'], 4, 2);
                        $prod = ceil(number_format(((float)$rTss[$j]['jjg'] ?? 0 * (float)$rTss[$j]['bjr'] ?? 0), 2, '.', ''));
                        $day = number_format(((float)$rTss[$j]['luaspanen'] ?? 1 / (float)$rTss[$j]['tk_panen'] ?? 0), 2, '.', '');


                        $push = array(
                            "divisi"          => $div,
                            "namakaryawan"    => $nmk,
                            "blok"            => $rTss[$j]['blok'],
                            "luasblok"        => $rTss[$j]['luasblok'],
                            "luaspanen"       => $rTss[$j]['luaspanen'],
                            "sph"             => $rTss[$j]['sph'],
                            "akp"             => $rTss[$j]['akp'],
                            "jjg"             => $rTss[$j]['jjg'],
                            "bjr"             => $rTss[$j]['bjr'],
                            "tk_panen"        => $rTss[$j]['tk_panen'],
                            "output"          => $rTss[$j]['output'],
                            "daya"            => $day,
                            "produksi"        => $prod,
                        );
                        array_push($temp, $push);
                    }
                    $idaya = number_format(((float)$luaspanen / (float)$tothk), 2, '.', '');
                    $output = number_format(((float)$totProd ?? 1 / (float)$tothk ?? 1), 2, '.', '');
                    $output = number_format(((float)$totProd ?? 1 / (float)$tothk ?? 1), 2, '.', '');
                    $insert = array(
                        "data" => $temp,
                        "luasblok" => $luasblok,
                        "luaspanen" => $luaspanen,
                        "totjjg" => $totjjg,
                        "tothk" => $tothk,
                        "totoutput" => $output,
                        "dayajelajah" => $idaya,
                        "produksi" => $totProd,
                    );
                }
                array_push($data, $insert);
            }
        }

        // foreach ($data as $key => $val) {
        //     $num1 = 1;
        //     echo "DATA JADI KE: " . $num1;
        //     echo "\n";
        //     echo "\n";
        //     print_r($val['data']);
        //     echo "\n";
        //     echo "\n";
        //     $num1++;
        // }

        return $data;
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
        return array_merge([
            'status' => 200,
            'error' => false,
            'message' => $message
        ], $data);
    }
}
