<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mutuhancak extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_prestasi_mobile", "kebun_mutubuah_mobile");
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
        $data['tanggal']            = $this->post('tanggal_panen');
        $data['nikmandor']          = $this->post('mandor');
        $data['gangcode']           = ($this->post('kode_kemandoran') !== null) ? (int)$this->post('kode_kemandoran') : (int)$this->post('mandor');
        $data['nikasisten']         = $this->post('asisten');
        $data['nikmandor1']         = $this->post('mandor1');
        $data['kerani']             = $this->post('kerani_panen');
        $data['tipetransaksi']      = $type;
        $data['createby']           = $user['userid'];

        $data['kodeorg']        = (null !== $this->post('kodeorg') && !empty($this->post('kodeorg'))) ? substr($this->post('kodeorg'), 0, 4) : substr($user['lokasitugas'], 0, 4);
        $data['divisi']         = (null !== $this->post('kodeorg') && !empty($this->post('kodeorg'))) ? $this->post('kodeorg') : $user['subbagian'];


        $data['deviceid']             = $user['uuid'];
        $data['createtime']         = $this->post('lastupdate');
        // console
        if ($this->uri->segments[5] == 'load') {
            return $data;
        }

        $aktifitas = $this->getAktifitas("WHERE tipetransaksi = '{$type}' AND notransaksi = '{$data['notransaksi']}'  LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() == 0) {
            $qexec = $this->insert($data, $this->db->dbname . ".kebun_aktifitas_mobile", false);
            if ($qexec) {
                $dt = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1")->fetch();
                $this->response['error'] = false;
                $this->response['message'] = "Success Insert Header";
                $this->response['notransaksi'] = $dt->notransaksi;
                $this->response['no_syncronized'] = $dt->nosync;
                $this->response['tanggal'] = $dt->tanggal;
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
        return $this->response;
    }

    public function addDetail2($user, $type, $jenisMutu)
    {
        $data['notransaksi']    = $this->post('notransaksi');
        $data['tanggal']        = $this->post('tanggal');
        $data['blok']           = explode(",", $this->post('blok'));
        $data['kodeorg']        = explode(",", $this->post('divisi'));
        $data['pemanen']        = explode(",", $this->post('pemanen'));
        $data['idjenis']        = explode(",", $this->post('idjenis'));
        $data['kodedenda']      = explode(",", $this->post('kodedenda'));
        $data['nilai']          = explode(",", $this->post('nilai'));
        $data['sesi']           = explode(",", $this->post('sesi'));
        $data['baris']          = explode(",", $this->post('baris'));
        $data['pokok']          = explode(",", $this->post('pokok'));


        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");

        if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('pemanen')) != "") {
            $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();

            foreach ($jenisMutu as $jn => $j) {

                if (trim($j['kode']) == "") {
                    $kode = $j['idjenis'];
                } else {
                    $kode = $j['kode'];
                }
                $kebun_5jenismutu[$kode] = $j['idjenis'];
                $tipedetail[$kode] = $j['jenis'];
            }

            for ($i = 0; $i < count($data['pemanen']); $i++) {
                $valueidjenis = array_key_exists(@$data['kodedenda'][$i], $kebun_5jenismutu) ? $kebun_5jenismutu[@$data['kodedenda'][$i]] : @$data['kodedenda'][$i];

                $maxNum++;
                $dataArr[] = array(
                    'notransaksi'   => $data['notransaksi'],
                    'nourut'        => $maxNum,
                    'kodeorg'      => substr($data['blok'][$i], 0, 10),
                    'tph'          => $data['blok'][$i],
                    'nik'          => $data['pemanen'][$i],
                    'tglpanen'     => $data['tanggal'],
                    'sesi'          => $data['sesi'][$i],
                    'tipedetail' => 'Mutu Hancak',
                    'idjenis'      => $valueidjenis,
                    'kodedenda'  => @$data['kodedenda'][$i],
                    'nilai'      => @$data['nilai'][$i],
                    'updateby'     => $user['userid'],
                    'baris'         => @$data['baris'][$i],
                    'pokok'         => @$data['pokok'][$i],
                );

                $dataDetail[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_mutubuah_mobile");
            }



            if ($this->uri->segments[5] == 'load') {
                return $dataDetail;
            }

            if (count($dataDetail) > 0) {
                $qexec = $this->exec($dataDetail);
                if ($qexec) {
                    $this->response['error'] = false;
                    $this->response['message'] = "Success Insert Detail";
                    $this->response['notransaksi'] = $data['notransaksi'];
                    $this->response['no_syncronized'] = $this->post('no_syncronized');
                    $this->response['tanggal'] = $this->post('tanggal');
                } else {
                    $this->response['status'] = 409;
                    $this->response['error'] = true;
                    $this->response['message'] = "Failed! : Insert Detail " . $qexec;
                    // $this->execdeleteAllDetail($this->post('notransaksi'));
                }
            }
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Transaksi Header {$data['notransaksi']} tidak ditemukan";
            // $this->execdeleteAllDetail($this->post('notransaksi'));
        }
        return $this->response;
    }

    public function addDetail($user, $type)
    {

        $data['notransaksi']    = $this->post('notransaksi');
        $data['tanggal']        = $this->post('tanggal');
        $data['blok']           = explode(",", $this->post('blok'));
        $data['kodeorg']        = explode(",", $this->post('divisi'));
        $data['pemanen']        = explode(",", $this->post('pemanen'));
        $data['idjenis']        = explode(",", $this->post('idjenis'));
        $data['kodedenda']      = explode(",", $this->post('kodedenda'));
        $data['nilai']          = explode(",", $this->post('nilai'));
        $data['sesi']           = explode(",", $this->post('sesi'));
        $data['baris']          = explode(",", $this->post('baris'));
        $data['pokok']          = explode(",", $this->post('pokok'));


        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");


        if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('pemanen')) != "") {

            // $Setup_kebun = $this->model('Setup_kebun');
            // $jenisMutu = $Setup_kebun->select_jenismutu();

            $Setup_mutu = $this->model('mmutu');
            $jenisMutu = $Setup_mutu->getMutu("WHERE aktif ='1'");

            foreach ($jenisMutu as $rH) {
                if (trim($rH['kode']) == "") {
                    $kode = $rH['idjenis'];
                } else {
                    $kode = $rH['kode'];
                }
                $kebun_5jenismutu[$kode] = $rH['idjenis'];
                $tipedetail[$kode] = $rH['jenis'];
            }
            $dendapanen = array_column($jenisMutu, 'kode', 'idjenis');

            $maxNum = $this->getMutubuah("WHERE notransaksi = '" . $data['notransaksi'] . "'")->rowCount();


            for ($i = 0; $i < count($data['pemanen']); $i++) {
                $maxNum++;
                $valueidjenis = @$kebun_5jenismutu[$data['kode_mutu'][$i]];
                $dataArr = array(
                    'notransaksi'   => $data['notransaksi'],
                    'nourut'        => $maxNum,
                    'kodeorg'      => substr($data['blok'][$i], 0, 10),
                    'tph'          => $data['blok'][$i],
                    'nik'          => $data['pemanen'][$i],
                    'tglpanen'     => $data['tanggal'],
                    'sesi'          => $data['sesi'][$i],
                    'tipedetail' => 'Mutu Hancak',
                    // 'idjenis' 	 => $valueidjenis,
                    'idjenis'      => @$data['idjenis'][$i],
                    'kodedenda'  => @$data['kodedenda'][$i],
                    // 'kodedenda'  => $dendapanen[@$data['idjenis'][$i]],
                    'nilai'      => @$data['nilai'][$i],
                    'updateby'     => $user['userid'],
                    'baris'         => @$data['baris'][$i],
                    'pokok'         => @$data['pokok'][$i],
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_mutubuah_mobile");
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
            } else {
                $this->response['status'] = 400;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Tidak Berhasil Insert Mutu Buah ";
                // $this->execdeleteAllDetail($this->post('notransaksi'));
            }
        } else {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
            // $this->execdeleteAllDetail($this->post('notransaksi'));
        }

        return $this->response;
    }

    public function uploadImages()
    {
        $notransaksi    =  $this->post('notransaksi');
        $file           =  $_POST['foto']; //your data in base64 'data:image/png....';
        if ($file != "") {
            try {
                // $path = "upload/panen/";
                // $path = "m_mharvest/upload/imgupload/panen/";
                // $path = 'm_fileDocuments/panen/images/';

                $location = 'm_fileDocuments';
                $linkImg = 'panen/images/';
                $path =  $location . '/' . $linkImg;

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                } //create Folder if not Exists

                if (!is_writable($path)) {
                    $this->response['status'] = 500;
                    $this->response['error'] = true;
                    $this->response['message'] = "tidak memiliki izin untuk membuat atau mengunggah foto ke folder " . $path;
                } else {
                    $mutubuah = $this->getMutubuah("WHERE notransaksi = '" . $this->post('notransaksi') . "' AND kodeorg = '" . $this->post('blok') . "' AND nik = '" . $this->post('pemanen') . "' AND tglpanen = '" . $this->post('tanggal') . "' AND baris='" . $this->post('baris') . "'
                    AND pokok='" . $this->post('pokok') . "'");
                    if ($mutubuah->rowCount() > 0) {
                        $dataPrestasi = $mutubuah->fetch();
                        $retVal = ($dataPrestasi->kodedenda != null) ? $dataPrestasi->kodedenda :"-" ;
                        $newFileName    = "{$notransaksi}{$retVal}{$this->post('pemanen')}{$this->post('baris')}{$this->post('pokok')}";
                        $newExtention   = ".jpg";
                        $file               = preg_replace('#^data:image/\w+;base64,#i', '', $file);
                        $file               = str_replace(' ', '+', $file);
                        $stream             = base64_decode($file);
                        $filename           = $newFileName . $newExtention;
                        file_put_contents($path . $filename, $stream);
                        if (file_exists($path . $filename)) { 
                            $dataUpdate = array(
                                "photo" => $this->base_url($linkImg, $location) . $filename,
                                "latlong" => $this->post('latitude') . "," . $this->post('longitude')
                            );
                            $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_mutubuah_mobile", "notransaksi='" . $notransaksi . "' and kodeorg='" . $this->post('blok') . "' and nik='" . $this->post('pemanen') . "' and tglpanen = '" . $this->post('tanggal') . "' and baris='" . $this->post('baris') . "'
                            and pokok='" . $this->post('pokok') . "' ");
                            $this->response['error'] = false;
                            $this->response['message'] = "Upload foto berhasil " . $this->base_url($linkImg, $location) . $filename;
                            $this->response['notransaksi'] = $this->post('notransaksi');
                            $this->response['no_syncronized'] = $this->post('no_syncronized');
                            $this->response['tanggal'] = $this->post('tanggal');
                        } else {
                            $this->response['status'] = 409;
                            $this->response['error'] = true;
                            $this->response['message'] = "Failed! : Foto tidak mendapatkan akses, Location : " . $filename;
                        }
                    } else {
                        $this->response['status'] = 409;
                        $this->response['error'] = true;
                        $this->response['message'] = "Failed! : Data Prestasi tidak ditemukan";
                    }
                }
            } catch (PDOException $e) {
                $this->response['status'] = 409;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Upload Foto - (" . $e->getMessage() . ") !!";
            }
        }
        return $this->response;
    }

    public function checkdatarow()
    {
        $jmldetail      = $this->post('jumlah_detail');
        $jumlah_baris = $this->post('jumlah_baris_pokok');
        $jmlRow         = ((int)$jmldetail + (int)$jumlah_baris);
        $str = "select  notransaksi from " . $this->db->dbname . ".kebun_prestasi_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        $str .= " UNION ALL ";
        $str .= " select  notransaksi from " . $this->db->dbname . ".kebun_mutubuah_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";

        // console
        if ($this->uri->segments[5] == 'load') {
            return $str;
        }

        $headerPanen = $this->getAktifitas("WHERE notransaksi = '" . $this->post('notransaksi') . "' LIMIT 1");

        if ($headerPanen and $headerPanen->rowCount() > 0) {
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
                $this->response['message'] = "Failed! : Data Sync (" . $datacheck->rowCount() . "/" . $jmlRow . ") Belum Lengkap, Mohon Sync Ulang";
                // $this->execdeleteAllDetail($this->post('notransaksi'));
            }
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Transaksi {$this->post('notransaksi')} tidak ditemukan";
            // $this->execdeleteAllDetail($this->post('notransaksi'));
        }
        return $this->response;
    }

    public function deleteAlldetailKeepAktifitas($whr, $del)
    {
        $aktifitas = $this->getAktifitas("WHERE " . $whr);
        if ($aktifitas) {
            $this->response['error'] = false;
            $this->exec($this->query_delete('kebun_prestasi_mobile', $del));
            $this->exec($this->query_delete('kebun_mutubuah_mobile', $del));
        } else {
            $this->response['error'] = true;
        }
        return $this->response;
    }

    function execdeleteAllDetail($notransaksi)
    {
        $whrDel = "notransaksi='" . $notransaksi . "'";
        $whr = "notransaksi='" . $notransaksi . "' and flag != '1'";
        $this->deleteAlldetailKeepAktifitas($whr, $whrDel);
    }

    function aktifitas(array $dataWhere = array(), array $pageLimit = array(), $tipetrans = "PNN")
    {
        $data = array();
        $where  = "WHERE tipetransaksi = '" . $tipetrans . "' ";
        if (count($dataWhere) > 0) {
            foreach ($dataWhere as $v) {
                $where .= $v;
            }
        }
        if (count($pageLimit) > 0) {
            $where .= "LIMIT " . implode(",", $pageLimit);
        }
        $data = $this->getAktifitas($where);
        return $data;
    }
    function getAktifitas($where = '')
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }
    function getPeriode($where = "")
    {
        $data = array();
        $q = "SELECT DATE_FORMAT(`tanggal`,'%Y-%m') as `key`,DATE_FORMAT(`tanggal`,'%Y-%m') as `value` FROM " . $this->db->dbname . ".kebun_aktifitas_mobile {$where} group by DATE_FORMAT(`tanggal`,'%Y-%m') order by `key` DESC LIMIT 10";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    function getDivisi($where = "")
    {
        $data = array();
        $q = "SELECT `divisi` as `key`,`divisi` as `value` FROM " . $this->db->dbname . ".kebun_aktifitas_mobile {$where} group by `divisi` order by `key` DESC;";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    function getUnit($where = "")
    {
        $data = array();
        $q = "SELECT `kodeorg` as `key`,`kodeorg` as `value` FROM " . $this->db->dbname . ".kebun_aktifitas_mobile {$where} group by `divisi` order by `key` DESC;";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    private function getPrestasi($where = '')
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_prestasi_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }
    private function getMutubuah($where = '')
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_mutubuah_mobile {$where}";
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
            $this->delete($this->db->dbname . ".kebun_prestasi_mobile", "notransaksi='" . $notransaksi . "'");
            $this->delete($this->db->dbname . ".kebun_mutubuah_mobile", "notransaksi='" . $notransaksi . "'");
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Resyncronize - (" . $e->getMessage() . ") !!";
        }
    }
    private function responseError($message, $status) {
        return [
            'status' => $status,
			'error' => true,
            'message' => $message
		];
	}
    
    private function responseSuccess($message, $data = null) {
        $response = array_merge([
            'status' => 200,
			'error' => false,
            'message' => $message,
		]);
        return array_merge($data, $response);
	}
}

function selectQuery(array $pageLimit = array())
{
    $limitPage = "";
    if (count($pageLimit) > 0) {
        $limitPage = "LIMIT " . implode(",", $pageLimit);
    }
    $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where tipetransaksi = 'MHC' " . $limitPage;
    //
    // echo $q;
    $data = $this->query($q, 'ASSOC');
    return $data;
}
