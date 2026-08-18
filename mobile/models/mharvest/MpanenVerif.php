<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MpanenVerif extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_gerdang_mobile");
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
        // $data['verify_trans']       = $this->post('notransaksi_panen');
        // $data['notransaksi']        = $this->post('notransaksi');
        $data['noreferensi']        = $this->post('notransaksi_panen');
        $data['tanggal']            = $this->post('tanggal');
        $data['gangcode']           = $this->post('kode_kemandoran');
        $data['nikasisten']         = $this->post('asisten');
        $data['nikmandor']          = $this->post('mandor');
        $data['nikmandor1']         = $this->post('mandor1');
        $data['kerani']             = $this->post('kerani_panen');
        $data['tipetransaksi']      = $type;
        $data['kodeorg']            = substr($this->post('asistensi'), 0, 4);
        // $data['divisi']             = $this->post('asistensi');
        $data['createby']           = $user['userid'];

        $data['kodeorg']        = (null !== $this->post('kodeorg') && !empty($this->post('kodeorg'))) ? substr($this->post('kodeorg'), 0, 4) : substr($user['lokasitugas'], 0, 4);
        $data['divisi']         = (null !== $this->post('kodeorg') && !empty($this->post('kodeorg'))) ? $this->post('kodeorg') : $user['subbagian'];


        // if (empty($data['kodeorg']) or $data['kodeorg'] == "" or $data['kodeorg'] == null) {
        //     $data['kodeorg'] = $user['lokasitugas'];
        // }
        // if (empty($data['divisi']) or $data['divisi'] == "" or $data['divisi'] == null) {
        //     $data['divisi'] = $user['subbagian'];
        // }

        $data['deviceid']             = $user['uuid'];
        $data['createtime']         = $this->post('lastupdate');
        // console
        if ($this->uri->segments[5] == 'load') {
            return $data;
        }

        $aktifitas = $this->getAktifitas("WHERE tipetransaksi = 'PNN' AND notransaksi = '" . $this->post('notransaksi_panen') . "'  LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() == 0) {
            $this->response['status'] = 403;
            $this->response['error'] = true;
            $this->response['message'] = "Warning : Data yang diverifikasi {$this->post('notransaksi_panen')} belum dilakukan sinkronisasi..";
        } else {
            $dt = $aktifitas->fetch();
            if ($dt->syn == '0') {
                $this->response['status'] = 403;
                $this->response['error'] = true;
                $this->response['message'] = "Transaksi Panen '" . $this->post('notransaksi_panen') . "' belum Selesai ter-Synchronize";
            } else {
                if ($dt->flag == "1") {
                    $this->response['status'] = 403;
                    $this->response['error'] = true;
                    $this->response['message'] = "Warning : Data yang diverifikasi sudah terposting.";
                } else {
                    $aktifitasVer = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
                    if ($aktifitasVer and $aktifitasVer->rowCount() == 0) {
                        $qexec = $this->insert($data, $this->db->dbname . ".kebun_aktifitas_mobile", false);
                        if ($qexec) {
                            $dtv = $this->getAktifitas("WHERE notransaksi = '" . $this->post('notransaksi') . "' LIMIT 1")->fetch();
                            $this->response['error'] = false;
                            $this->response['message'] = "Success Insert Header Verifikasi";
                            $this->response['notransaksi'] = $dtv->notransaksi;
                            $this->response['no_syncronized'] = $dtv->nosync;
                            $this->response['tanggal'] = $dtv->tanggal;
                        }
                    } else {
                        $dtvr = $aktifitasVer->fetch();
                        if ($dtvr->syn == '1' && $dt->flag == "1") {
                            $this->response['status'] = 403;
                            $this->response['error'] = true;
                            $this->response['message'] = "Warning : Tidak bisa Resyncronize Verifikasi, Data yang diverifikasi sudah terposting.";
                        }else{
                            $this->reSyntransMobile($data['notransaksi'], 'PNV');
                            $this->response['error'] = false;
                            $this->response['message'] = "Success Re-Syncronized Data Verifikasi";
                            $this->response['notransaksi'] = $dtvr->notransaksi;
                            $this->response['no_syncronized'] = $dtvr->nosync;
                            $this->response['tanggal'] = $dtvr->tanggal;
                        }
                    }
                }
            }
        }
        return $this->response;
    }
    public function addDetail($user, $type)
    {
        $data['notransaksi']        = $this->post('notransaksi');
        $data['tanggal']            = $this->post('tanggal');
        // $data['no_syncronized']     = $this->post('no_syncronized');
        // DATA ARRAY ===
        $data['pemanen']            = explode(",", $this->post('pemanen'));
        $data['blok']               = explode(",", $this->post('blok'));
        $data['tph']                = explode(",", $this->post('tph'));
        $data['janjang']            = explode(",", $this->post('janjang'));
        $data['janjang_ai']         = explode(",", $this->post('janjang_ai'));
        $data['brondolan']          = explode(",", $this->post('brondolan'));
        $data['tipepanen']          = explode(",", $this->post('tipe_panen'));
        $data['cetak']              = explode(",", $this->post('cetak'));
        $data['sesi']               = explode(",", $this->post('sesi'));
        $data['createtime']         = explode(",", $this->post('lastupdate'));
        $data['edited']             = explode(",", $this->post('edited'));
        $data['janjang_ai']         = explode(",", $this->post('janjang_ai'));

        $tahuntanam = array();
        $status = array();

        $Blok = $this->model('Blok');
        $dataBlok = $Blok->getDataBlok("where kodeorg like '" . substr($data['blok'][0], 0, 6) . "%'");

        if (count($dataBlok) > 0) {
            for ($i = 0; $i < count($dataBlok); $i++) {
                $tahuntanam[$dataBlok[$i]['kodeorg']] = $dataBlok[$i]['tahuntanam'];
                $status[$dataBlok[$i]['kodeorg']] = $dataBlok[$i]['statusblok'];
            }
        }

        // $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
        $headerPanen = $this->getAktifitas("WHERE notransaksi = '" . $this->post('notransaksi_panen') . "' LIMIT 1");
        
        if ($headerPanen and $headerPanen->rowCount() > 0) {

            $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
            if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('pemanen')) != "") {
                $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();

                for ($i = 0; $i < count($data['pemanen']); $i++) {
                    $statusBlock = array_key_exists($data['blok'][$i], $status) ? $status[$data['blok'][$i]] : '';
                    $tahuntanamValue = array_key_exists($data['blok'][$i], $tahuntanam) ? $tahuntanam[$data['blok'][$i]] : '';

                    // Cek data pada Kebun_prestasi_mobile
                    $qpanen = "notransaksi = '{$this->post('notransaksi_panen')}' AND nik = '{$data['pemanen'][$i]}' AND tph = '{$data['tph'][$i]}' AND sesi = '{$data['sesi'][$i]}'";
                    $dataPanen = $this->getPrestasi("WHERE  ".$qpanen);
                    $dataDetail = [];

                    if ($dataPanen->rowCount() > 0) {
                        $dataUpdate = array(
                            "noreferensi" => $data['notransaksi']
                        );
                        $qexec = $this->update($dataUpdate,$this->db->dbname.".kebun_prestasi_mobile",$qpanen);
                        
                        $maxNum++;
                        $dataArr = array(
                            'notransaksi'   => $data['notransaksi'],
                            'nourut'        => $maxNum,
                            'nik'           => $data['pemanen'][$i],
                            'kodekegiatan'  => $type,
                            'kodeorg'       => $data['blok'][$i],
                            'tph'           => $data['tph'][$i],
                            'sesi'          => $data['sesi'][$i],
                            'cetak'         => $data['cetak'][$i],
                            'tipepanen'     => $data['tipepanen'][$i],
                            'hasilkerja'    => $data['janjang'][$i],
                            'brondolan'     => $data['brondolan'][$i],
                            'kodesegment'   => '1',
                            'statusblok'    => $statusBlock,
                            'tahuntanam'    => $tahuntanamValue,
                            'updateby'      => $user['userid'],
                            'createtime'    => $data['createtime'][$i],
                            'edited_ai'     => $data['edited'][$i],
                            'janjang_ai'    => $data['janjang_ai'][$i],
                            'noreferensi'   => $this->post('notransaksi_panen'),
                        );
                        $dataDetail[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_prestasi_mobile");
                    }
                    
                    // console
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
                            $this->response['message'] = "Failed! : Insert Detail ".$this->response['message']; 
                        }
                    } else {
                        $this->response['status'] = 409;
                        $this->response['error'] = true;
                        $this->response['message'] = "Failed! : Data notransaksi = '{$this->post('notransaksi_panen')}' nik = '{$data['pemanen'][$i]}' Tph = '{$data['tph'][$i]}' Sesi = '{$data['sesi'][$i]}' Tidak Ditemukan";
                    }
                }
            } else {
                $this->response['status'] = 409;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Transaksi Header {$data['notransaksi']} tidak ditemukan";
            }
        }else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Transaksi Panen {$this->post('notransaksi_panen')} tidak ditemukan";
        }
        return $this->response;
    }

    public function addgerdang(){
        $data['notransaksi']        = $this->post('notransaksi');
        $data['tanggal']            = $this->post('tanggal');
        // $data['no_syncronized']     = $this->post('no_syncronized');
        // DATA ARRAY ===
        $data['pemanen']            = explode(",",$this->post('pemanen'));
        // $data['tipe']               = explode(",",$this->post('tipe_panen_pemanen'));
        $data['nik_gerdang']        = explode(",",$this->post('gerdang'));
        $data['tipe_gerdang']       = explode(",",$this->post('tipe_panen'));
        
        $headerPanen = $this->getAktifitas("WHERE notransaksi = '" . $this->post('notransaksi_panen') . "' LIMIT 1");
                
        if ($headerPanen and $headerPanen->rowCount() > 0) {
            $aktifitas = $this->getAktifitas("WHERE notransaksi = '".$data['notransaksi']."' and nosync = '".$this->post('no_syncronized')."' LIMIT 1");
            if($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('pemanen')) != ""){
                $dataInsert = [];
                for($i=0; $i<count($data['pemanen']); $i++){
                    $dataArr = array(
                        'notransaksi'=>$this->post('notransaksi'),
                        'nik'=>$data['pemanen'][$i],
                        // 'tipe'=>$data['tipe'][$i],
                        'nik_gerdang'=>$data['nik_gerdang'][$i],
                        'tipe_gerdang'=>$data['tipe_gerdang'][$i]
                    ); 
                    $dataInsert[$i] = $this->query_insert($dataArr,$this->db->dbname.".kebun_gerdang_mobile");
                }
                // console
                if($this->uri->segments[5]=='load'){
                    return $dataInsert;
                }
                $qexec = $this->exec($dataInsert);
                if($qexec){
                    $this->response['error'] = false;
                    $this->response['message'] = "Success";
                    $this->response['notransaksi'] = $this->post('notransaksi');
                    $this->response['no_syncronized'] = $this->post('no_syncronized');
                    $this->response['tanggal'] = $this->post('tanggal');
                }
            }else{
                $this->response['status'] = 409;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
            }
        }else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Transaksi Panen {$this->post('notransaksi_panen')} tidak ditemukan";
        }
        return $this->response;
    }
    public function addmutubuah($user){
        $data['notransaksi']        = $this->post('notransaksi');
        $data['tanggal']            = $this->post('tanggal');
        // $data['no_syncronized']     = $this->post('no_syncronized');
        // DATA ARRAY ===
        $data['tph']        = explode(",",$this->post('tph'));
        $data['pemanen']    = explode(",",$this->post('pemanen'));
        $data['sesi']       = explode(",",$this->post('sesi'));
        $data['kode']       = explode(",",$this->post('kode'));
        $data['jml']        = explode(",",$this->post('jml'));
        
        $headerPanen = $this->getAktifitas("WHERE notransaksi = '" . $this->post('notransaksi_panen') . "' LIMIT 1");
        
        if ($headerPanen and $headerPanen->rowCount() > 0) {
            $aktifitas = $this->getAktifitas("WHERE notransaksi = '".$data['notransaksi']."' and nosync = '".$this->post('no_syncronized')."' LIMIT 1");


            if($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('pemanen')) != ""){

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
                
                $maxNum = $this->getMutubuah("WHERE notransaksi = '".$data['notransaksi']."'")->rowCount();

                for($i=0; $i<count($data['tph']); $i++){
                    $maxNum++;
                    $valueidjenis = @$kebun_5jenismutu[$data['kode_mutu'][$i]];
                    $dataArr = array(
                        'notransaksi'=> $data['notransaksi'],
                        'kodeorg' 	 => substr($data['tph'][$i],0,9),
                        'tph' 		 => $data['tph'][$i],
                        'nik' 		 => $data['pemanen'][$i],
                        'tglpanen'	 => $data['tanggal'],
                        'sesi' 		  => $data['sesi'][$i],
                        'tipedetail' => @$tipedetail[$data['kode'][$i]],
                        'nourut' 	 => $maxNum,
                        'idjenis' 	 => $valueidjenis,
                        'kodedenda'  => $data['kode'][$i],
                        'nilai' 	 => $data['jml'][$i],
                        'updateby'	 => $user['userid']
                    ); 
                    $dataInsert[$i] = $this->query_insert($dataArr,$this->db->dbname.".kebun_mutubuah_mobile");
                }
                // console
                if($this->uri->segments[5]=='load'){
                    return $dataInsert;
                }
                $qexec = $this->exec($dataInsert);
                if($qexec){
                    $this->response['error'] = false;
                    $this->response['message'] = "Success Insert Mutu Buah";
                    $this->response['notransaksi'] = $this->post('notransaksi');
                    $this->response['no_syncronized'] = $this->post('no_syncronized');
                    $this->response['tanggal'] = $this->post('tanggal');
                }else{
                    $this->response['status'] = 400;
                    $this->response['error'] = true;
                    $this->response['message'] = "Failed! : Tidak Berhasil Insert Mutu Buah ";
                }
            }else{
                $this->response['status'] = 404;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
            }
        }else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Transaksi Panen {$this->post('notransaksi_panen')} tidak ditemukan";
        }
        return $this->response;
    }
    public function checkdatarow(){
        $jmldetail      = $this->post('jumlah_detail');
        $jumlah_gerdang = $this->post('jumlah_gerdang');
        $jumlah_grading = $this->post('jumlah_grading');
        $jumlah_hama    = $this->post('jumlah_hama');
        $jmlRow         = ((int)$jmldetail+(int)$jumlah_grading+(int)$jumlah_gerdang+(int)$jumlah_hama);
        $str ="select  notransaksi from ".$this->db->dbname.".kebun_prestasi_mobile where notransaksi = '".$this->post('notransaksi')."' ";
        $str .=" UNION ALL ";
        $str .=" select  notransaksi from ".$this->db->dbname.".kebun_mutubuah_mobile where notransaksi = '".$this->post('notransaksi')."' ";
        $str .=" UNION ALL ";
        $str .=" select  notransaksi from ".$this->db->dbname.".kebun_gerdang_mobile where notransaksi = '".$this->post('notransaksi')."' ";
        // console
        if($this->uri->segments[5]=='load'){
            return $str;
        }
          
        $headerPanen = $this->getAktifitas("WHERE notransaksi = '" . $this->post('notransaksi') . "' LIMIT 1");
        
        if ($headerPanen and $headerPanen->rowCount() > 0) {
            $datacheck = $this->query($str);
            if($datacheck->rowCount() == $jmlRow){
                $dataUpdate = array(
                    "syn" => "1"
                );
                $qexec = $this->update($dataUpdate,$this->db->dbname.".kebun_aktifitas_mobile","notransaksi='".$this->post('notransaksi')."'");
                if (!$this->response['error']) {
                        $this->response['message'] = "Sinkronisasi Data Telah Selesai.";
                        $this->response['notransaksi'] = $this->post('notransaksi');
                        $this->response['no_syncronized'] = $this->post('no_syncronized');
                        $this->response['tanggal'] = $this->post('tanggal');

                        $this->update(["is_verif" => "1"], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='{$this->post('notransaksi_panen')}'");
                }else{
                    $this->response['status'] = 409;
                    $this->response['error'] = true;
                    $this->response['message'] = "Failed! : Gagal Update " . $this->response['message'];
                }
                
            }else{
                $this->response['status'] = 409;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Data Syncd (".$datacheck->rowCount()."/".$jmlRow.") Belum Lengkap, Mohon Sync Ulang";
            }
        }else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Transaksi Panen {$this->post('notransaksi_panen')} tidak ditemukan";
        }
        return $this->response;
    }
    public function uploadImages()
    {
        $notransaksi    =  $this->post('notransaksi');
        $file           =  $_POST['foto']; //your data in base64 'data:image/png....';
        $file_2         =  $_POST['foto_ai'];
        if ($file != "") {
            try {
                // $path = "upload/panen/";
                // $path = "m_mharvest/upload/imgupload/panen/";
                // $path = 'm_fileDocuments/verifikasi/images/';

                $location = 'm_fileDocuments';
                $linkImg = 'verifikasi/images/';
                $path =  $location.'/'.$linkImg;
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                } //create Folder if not Exists

                if (!is_writable($path)) {
                    $this->response['status'] = 500;
                    $this->response['error'] = true;
                    $this->response['message'] = "tidak memiliki izin untuk membuat atau mengunggah foto ke folder " . $path;
                } else {
                    $prestasi = $this->getPrestasi("WHERE notransaksi = '" . $this->post('notransaksi') . "' AND nik = '" . $this->post('pemanen') . "' AND tph = '" . $this->post('tph') . "' AND sesi='" . $this->post('sesi') . "'");
                    if ($prestasi->rowCount() > 0) {
                        $dataPrestasi = $prestasi->fetch();
                        $newFileName    = $notransaksi . $dataPrestasi->nourut;

                        $newExtention   = ".jpg";
                        $file               = preg_replace('#^data:image/\w+;base64,#i', '', $file);
                        $file               = str_replace(' ', '+', $file);
                        $stream             = base64_decode($file);
                        $filename           = $newFileName . $newExtention;
                        file_put_contents($path. $filename, $stream);

                        $newFileName_2      = $newFileName . "_ai";
                        $file_2             = preg_replace('#^data:image/\w+;base64,#i', '', $file_2);
                        $file_2             = str_replace(' ', '+', $file_2);
                        $stream_2           = base64_decode($file_2);
                        $filename_2         = $newFileName_2 . $newExtention;
                        file_put_contents($path . $filename_2, $stream_2);

                        // if (file_exists($filename)) {
                        if (file_exists($path . $filename)) {
                            $dataUpdate = array(
                                // "photo" => 'http://' . $_SERVER['HTTP_HOST'] . '/mobile/' . $filename,
                                // "photoakhir" => 'http://' . $_SERVER['HTTP_HOST'] . '/mobile/' . $filename_2,
                                "photo" => $this->base_url($linkImg,$location) . $filename,
                                "photo2" => $this->base_url($linkImg,$location) . $filename_2,
                                "latlong" => $this->post('latitude') . "," . $this->post('longitude')
                            );
                            $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_prestasi_mobile", "notransaksi='" . $notransaksi . "' and nik='" . $this->post('pemanen') . "' and tph='" . $this->post('tph') . "' and sesi='" . $this->post('sesi') . "'");
                            $this->response['error'] = false;
                            // $this->response['message'] = "Upload foto berhasil " . $_SERVER['HTTP_HOST'] . '/mobile/' . $filename;
                            $this->response['message'] = "Upload foto berhasil ".$this->base_url($linkImg,$location) . $filename;
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
    public function setup_tipepanen($user)
    {
        $data = array();
        $kodejenis_data = array(); // Menyimpan data berdasarkan kodejenis

        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5tipepanen 
              WHERE (kodeorg = '" . substr($user['lokasitugas'], 0, 4) . "' OR kodeorg = 'GLOBAL') 
              AND fungsi = 1 AND aktif = 1 
              ORDER BY kodejenis";
        $r = $this->fetchdata($q);

        if (count($r) > 0) {
            // Mengelompokkan data berdasarkan kodejenis
            foreach ($r as $s) {
                // Inisialisasi array untuk setiap kodejenis
                if (!isset($kodejenis_data[$s['kodejenis']])) {
                    $kodejenis_data[$s['kodejenis']] = array();
                }

                // Menambahkan data ke dalam array kodejenis_data
                $kodejenis_data[$s['kodejenis']][] = array(
                    'id'        => $s['id'],
                    'kodeorg'   => $s['kodeorg'],
                    'kodejenis' => $s['kodejenis'],
                    'deskripsi' => $s['deskripsi'],
                    'fungsi'    => $s['fungsi'],
                    'aktif'     => $s['aktif'],
                    'flagcode'  => $s['flagcode']
                );
            }

            // Mengelompokkan data berdasarkan kodejenis
            foreach ($kodejenis_data as $kodejenis => $jenis_data) {
                // Memeriksa apakah ada data spesifik untuk kodeorganisasi pengguna
                $user_specific_data = array_filter($jenis_data, function ($item) use ($user) {
                    return $item['kodeorg'] == substr($user['lokasitugas'], 0, 4);
                });

                if (!empty($user_specific_data)) {
                    $data = array_merge($data, $user_specific_data);
                } else {
                    // Jika tidak ada data spesifik untuk kodeorganisasi pengguna, ambil data global
                    $global_data = array_filter($jenis_data, function ($item) {
                        return $item['kodeorg'] == 'GLOBAL';
                    });
                    $data = array_merge($data, $global_data);
                }
            }
        }

        return $data;
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
            $this->delete($this->db->dbname . ".kebun_gerdang_mobile", "notransaksi='" . $notransaksi . "'");
            $this->delete($this->db->dbname . ".kebun_mutubuah_mobile", "notransaksi='" . $notransaksi . "'");
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Resyncronize Verifikasi - (" . $e->getMessage() . ") !!";
        }
    }
}
