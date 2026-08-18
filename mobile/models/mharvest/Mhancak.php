<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mhancak extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile","kebun_mutubuah_mobile", "kebun_prestasi_mobile", "setup_blok", "kebun_pakaimaterial_mobile");
        $d['key'] = array("notransaksi");
        $this->prepareDB = $d;
    }

    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "' AND tipetransaksi = 'MHC' ORDER BY tanggal DESC " . $limitPage;
        //
        // echo $q;
        $data = $this->query($q, 'ASSOC');
        return $data;
    }
    function selectQueryDetail($where)
    {
        $q = "select * from " . $this->db->dbname . ".kebun_mutubuah_mobile " . $where;
        // echo $q;
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
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

    public function postingMutuhancak($whr)
    {
        $arr = [
            'jurnal' => 1
        ];
        $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
        //    echo $aktifitas;
        return $this->response;
    }

    public function deleteMutuhancak($whr)
    {
        // $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
        $this->exec($this->query_delete('kebun_aktifitas_mobile', $whr));
        //    echo $aktifitas;
        return $this->response;
    }

    // function posting($id)
    // {
    //     $s = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where notransaksi = " . $id;
    //     $res = $this->query($s);
    //     $data = $res->fetch();
    //     $where = "WHERE notransaksi = " . $data['notransaksi'];
    //     $data = array(
    //         'nosync' => $data['nosync'],
    //         'notransaksi'    => $data['notransaksi'],
    //         'tipetransaksi' => $data['tipetransaksi'],
    //         'tanggal' => $data['tanggal'],
    //         'kodeorg'   => $data['kodeorg'],
    //         'divisi'     => $data['divisi'],
    //         'gangcode'     => $data['gangcode'],
    //         'nikmandor'    => $data['nikmandor'],
    //         'nikmandor1'     => $data['nikmandor1'],
    //         'nikasisten'     => $data['nikasisten'],
    //         'kerani'   => $data['kerani'],
    //         'jurnal'     => $data['jurnal'],
    //         'syn'    => $data['syn'],
    //         'nospk'    => $data['nospk'],
    //         'noreferensi'    => '1',
    //         'approved'    => $data['approved'],
    //         'jenis'    => $data['jenis'],
    //         'deviceid'    => $data['deviceid'],
    //         'createby'    => $data['createby'],
    //         'createtime'    => $data['createtime'],
    //         'updateby'    => $data['updateby'],
    //         'updatetime'    => $data['updatetime'],
    //         'uploadtime'    => $data['uploadtime'],
    //         'flag_trx'    => $data['flag_trx'],
    //         'flag'    => $data['flag']
    //     );
    //     // $q = "UPDATE" . $this->db->dbname . ".kebun_aktifitas_mobile " .
    //     //     "SET jurnal = 1 where notransaksi = " . $notrans;
    //     $q = $this->update($this->db, 'kebun_aktifitas_mobile', $data, $where);
    // }

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
        $data['tanggal']        = $this->post('tanggal');
        $data['tanggal_panen']        = $this->post('tanggal_panen');
        $data['mandor']        = $this->post('mandor');
        $data['status']        = $this->post('status');
        $data['no_syncronized']        = $this->post('no_syncronized');
        $data['last_update']        = $this->post('last_update');
        $data['kodeorg']        = $this->post('kodeorg');
        // $data['kodeorg']        = substr($this->post('asistensi'), 0, 4);

        // if (empty($data['kodeorg']) or $data['kodeorg'] == "" or $data['kodeorg'] == null) {
        //     $data['kodeorg'] = $user['lokasitugas'];
        // }

        $str = "  select notransaksi,flag,syn from " . $this->db->dbname . ".kebun_aktifitas_mobile 
        where tipetransaksi = '" . $type . "' 
        and kodeorg='" . $data['notransaksi'] . "' 
        and noreferensi='" . $data['notransaksi'] . "' limit 1";

        // Get aktifitas
        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");

        $res = $this->query($str);
        $isCreated = false;
        if ($res->rowCount() > 0) {
            $d = $res->fetch();
            $isCreated = true;
            if ($d->flag == "1") {
                $errprop['status'] = 403;
                $errprop['error'] = true;
                $errprop['message'] = "Warning : Data Transaksi sudah di Download.";
            } else if ($d->syn == '1') {
                $errprop['status']  = 403;
                $errprop['error']  = true;
                $errprop['message'] = "Data sudah ter-Synchronize dengan baik";
            } else {
                $errprop['message'] = "ReSyncronize transaction " . $data['notransaksi'] . " Berhasil Dilakukan";
                // $this->reSyntransMobile($this->post('notransaksi'), $type);
                // $dt = $aktifitas->fetch();
                $this->response['error'] = false;
                $this->response['message'] = "Data successfully re-synchronized!";
                $this->response['notransaksi'] = $dt->notransaksi;
                $this->response['no_syncronized'] = $dt->nosync;
                $this->response['tanggal'] = $dt->tanggal;
            }
        }

        // TODO Array Data to insert!
        if (!$isCreated and !$errprop['error']) {
            // $notransaksi = $this->createNoTrx($data['tanggal'], $data['kodeorg'], $type);
            $data = array(
                'notransaksi'      => $notransaksi,
                'tipetransaksi'    => $type,
                'jenis'            => $type,
                'tanggal'          => $data['tanggal'],
                'nobkm'            => $notransaksi,
                'kodeorg'          => $param['kodeorg'],
                'nikmandor'        => empty($param['mandor']) ? $user['karyawanid'] : $param['mandor'],
                'nikmandor1'       => @$param['mandor1'],
                'nikasisten'       => @$param['asisten'],
                'keranimuat'       => @$param['kerani_perawatan'],
                'noreferensi'      => $data['no_syncronized'],
                'flag'             => '0',
                'nospk'            => '',
                'createby'         => $user['karyawanid'],
                'createdate'       => $data['last_update'],
                'updateby'         => $user['karyawanid'],
                'lastupdate'       => $data['last_update'],
                'keterangan'       => '',
                'tanggalinput'     => $dateTransaction,
                'syn'              => 0,
                'deviceid'         => $headers['uuid'],
            );
        }

        // TODO Insert Data
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
            $this->reSyntransMobile($this->post('notransaksi'), $type);
            $dt = $aktifitas->fetch();
            $this->response['error'] = false;
            $this->response['message'] = "Data successfully re-synchronized!";
            $this->response['notransaksi'] = $dt->notransaksi;
            $this->response['no_syncronized'] = $dt->nosync;
            $this->response['tanggal'] = $dt->tanggal;
        }

        // console
        if ($this->uri->segments[5] == 'load') {
            return $data;
        }

        return $this->response;
    }

    public function mmhancakdtl($user, $type)
    {
        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
        // echo $aktifitas->rowCount();
        // $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('karyawanid')) != "") {
            $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();
            // echo $maxNum;
            // echo count($data['karyawanid']);
            for ($i = 0; $i < count($data['karyawanid']); $i++) {
                $statusBlock = array_key_exists($data['blok'][$i], $status) ? $status[$data['blok'][$i]] : '';
                $tahuntanamValue = array_key_exists($data['blok'][$i], $tahuntanam) ? $tahuntanam[$data['blok'][$i]] : '';
                $maxNum++;
                $dataArr = array(
                    'notransaksi'             => $this->post('notransaksi'),
                    'nourut'                  => ($i + 1),
                    'nik'                     => $data['karyawanid'][$i],
                    'noreferensi'             => $this->post('no_syncronized'),
                    'kodekegiatan'            => $type,
                    'kodeorg'                 => $data['kodeorg'][$i],
                    'statusblok'              => $statusBlock,
                    'tahuntanam'              => $tahuntanamValue,
                    'tph'                     => '',
                    'identifikasi'            => '',
                    'hasilkerjapremi'         => '0',
                    'bjr'                     => '0',
                    'norma'                   => '0',
                    'outputminimal'           => '0',
                    'flag'                    => '0',
                    'sesi'                    => '',
                    'hasilkerjakg'            => '0',
                    'brondolan'               => '0',
                    'keterangan'              => 'BORONGAN',
                    'cetak'                   => '0',
                    'tipepanen'               => '',
                    'latitude'                => '',
                    'logitude'                => '',
                    'processed'               => '',
                    'message'                 => '',
                    'hasilkerja'              => $data['hasil_kerja'][$i],
                    'jumlahhk'                => $data['jumlah_hk'][$i],
                    'createby'                => $data['createby'],
                    'createtime'              => $data['createtime'],
                    'kodesegment'             => '1'
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_prestasi_mobile");
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
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }

    public function barispokok()
    {
        $data['no_syncronized'] = $this->post('no_syncronized');
        $data['notransaksi']    = $this->post('notransaksi');
        $data['kodeorg']        = explode(",", $this->post('blok'));
        $data['gudang']         = explode(",", $this->post('gudang'));
        $data['kodebarang']     = explode(",", $this->post('kodebarang'));
        $data['kwantitas']      = explode(",", $this->post('kwantitas'));

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() > 0) {
            for ($i = 0; $i < count($data['kodebarang']); $i++) {
                $str = "SELECT notransaksi, sum(ifnull(hasilkerja,0)) as hasilkerja " .
                    "FROM kebun_prestasi_mobile " .
                    "WHERE `notransaksi` = '" . $data['notransaksi'] . "' " .
                    "GROUP BY notransaksi ";
                // echo $str;
                $res = $this->fetchdata($str);
                // echo print_r($res);
                $dataArr = array(
                    'notransaksi'    =>  $data['notransaksi'],
                    'kodeorg'        => $data['kodeorg'][$i],
                    'kodebarang'    => $data['kodebarang'][$i],
                    'kwantitas'        => ($data['kwantitas'][$i] == null) ? 0 : $data['kwantitas'][$i],
                    'kwantitasha'    => $res[0]['hasilkerja'],
                    'hargasatuan'    => 0.0,
                    'kodegudang'    => $data['gudang'][$i]
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_pakaimaterial_mobile");
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
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }

    public function checkTxn()
    {
    }

    //? Check total rows are equals
    public function checkdatarow()
    {
        $jmldetail      = $this->post('jumlah_kehadiran');
        $jumlah_bkm = $this->post('jumlah_bkm');
        $jumlah_material = $this->post('jumlah_material');
        $jmlRow         = ((int)$jmldetail + (int)$jumlah_material + (int)$jumlah_bkm);
        $str = "select  notransaksi from " . $this->db->dbname . ".kebun_aktifitas_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        $str .= " UNION ALL ";
        $str .= " select  notransaksi from " . $this->db->dbname . ".kebun_prestasi_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        $str .= " UNION ALL ";
        $str .= " select  notransaksi from " . $this->db->dbname . ".kebun_pakaimaterial_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        // console
        if ($this->uri->segments[5] == 'load') {
            return $str;
        }
        $datacheck = $this->query($str);
        if ($datacheck->rowCount() == $jmlRow) {
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
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }

    //? Get Aktifitas
    private function getAktifitas($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }

    //? Get Prestasi
    private function getPrestasi($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_prestasi_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }

    //? Re Synchronize
    private function reSyntransMobile($notransaksi, $type)
    {
        try {
            $dataUpdate = array(
                "syn" => "0"
            );
            $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $this->post('notransaksi') . "' and tipetransaksi = '" . $type . "'");
            $this->delete($this->db->dbname . ".kebun_prestasi_mobile", "notransaksi='" . $notransaksi . "'");
            $this->delete($this->db->dbname . ".kebun_pakaimaterial_mobile", "notransaksi='" . $notransaksi . "'");
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Upload Foto - (" . $e->getMessage() . ") !!";
        }
    }
}
