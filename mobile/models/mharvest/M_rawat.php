<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_rawat extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_prestasi_mobile", "kebun_pakaimaterial_mobile", "sdm_absensi");
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
            $location = 'm_fileDocuments';
            $linkImg = 'rawat/images/';
            $path =  $location . '/' . $linkImg;
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            } //create Folder if not Exists

            if (!is_writable($path)) {
                // $errprop['status']     = 500;
                // $errprop['error']      = true;
                // $errprop['message']    = "tidak memiliki izin untuk membuat atau mengunggah foto ke folder ini";
                return $this->responseError("tidak memiliki izin untuk membuat atau mengunggah foto ke folder ini", 500);
            } else {
                $newFileName = $this->post('notransaksi') . "BKM" . $this->post('kodeorg');
                $newExtention   = ".jpg";
                $foto_awal      =  $this->post('foto_awal'); //your data in base64 'data:image/png....';
                $foto_akhir     =  $this->post('foto_akhir');
                // $cob = str_replace(' ', '', str_replace(']', '', str_replace('[', '', $foto_akhir)));
                // $cob2 = str_replace(' ', '', str_replace(']', '', str_replace('[', '', $foto_awal)));
                // $excob = explode(",", $cob);
                // $excob2 = explode(",", $cob2);
                // $strcob = pack('C*', ...$excob);
                // $strcob2 = pack('C*', ...$excob2);

                $foto_awal      = preg_replace('#^data:image/\w+;base64,#i', '', $foto_awal);
                $foto_awal      = str_replace(' ', '+', $foto_awal);
                $stream         = base64_decode($foto_awal);
                $filename        = $newFileName . $newExtention;
                file_put_contents($path . $filename, $stream);


                $foto_akhir     = preg_replace('#^data:image/\w+;base64,#i', '', $foto_akhir);
                $foto_akhir     = str_replace(' ', '+', $foto_akhir);
                $stream_2       = base64_decode($foto_akhir);
                $filename_2     = $newFileName . '_akhir' . $newExtention;
                file_put_contents($path . $filename_2, $stream_2);

                $data['notransaksi']    = $this->post('notransaksi');
                $data['tipetransaksi']  = $type;
                $data['tanggal']        = $this->post('tanggal');
                $data['kodeorg']        = (null !== $this->post('asistensi') && !empty($this->post('asistensi'))) ? substr($this->post('asistensi'), 0, 4) : substr($this->post('kodeorg'), 0, 4);
                // $data['kodeorg']        = substr($this->post('kodeorg'), 0, 4);
                $data['divisi']         = (null !== $this->post('kodeorg') && !empty($this->post('kodeorg'))) ? $this->post('kodeorg') : $user['subbagian'];
                $data['gangcode']       = $this->post('kode_kemandoran');
                $data['nikmandor']      = $this->post('mandor');
                $data['nikmandor1']     = $this->post('mandor1');
                $data['nikasisten']     = $this->post('asisten');
                $data['kerani']         = $this->post('kerani_perawatan');
                $data['deviceid']       = $user['uuid'];
                $data['createby']       = $user['userid'];
                // $data['createby']       = '0000000001';
                $data['createtime']     = $this->post('lastupdate');
                $data['updateby']       = $user['userid'];
                // $data['updateby']       = '0000000001';
                $data['photo']          = $this->base_url($linkImg, $location) . $filename;
                $data['photo2']         = $this->base_url($linkImg, $location) . $filename_2;
                $data['latlong']        = $this->post('lat_foto_awal') . "," . $this->post('long_foto_awal');
                $data['latlong2']       = $this->post('lat_foto_akhir') . "," . $this->post('long_foto_akhir');

                // console
                if ($this->uri->segments[5] == 'load') {
                    return $data;
                }

                // Aktifitas
                $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");

                // Exec insert and checking data
                if ($aktifitas and $aktifitas->rowCount() == 0) {
                    $qexec = $this->insert($data, $this->db->dbname . ".kebun_aktifitas_mobile");
                    if ($qexec) {
                        // if (true) {
                        $dt = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1")->fetch();
                        return $this->responseSuccess("Sinkronisasi Data Telah Selesai.", [
                            'notransaksi' => $dt->notransaksi,
                            'no_syncronized' => $dt->nosync,
                            'tanggal' => $dt->tanggal
                        ]);
                        // $this->response['error'] = false;
                        // $this->response['message'] = "Success";
                        // $this->response['notransaksi'] = $dt->notransaksi;
                        // $this->response['no_syncronized'] = $dt->nosync;
                        // $this->response['tanggal'] = $dt->tanggal;
                    }
                } else {
                    $dt = $aktifitas->fetch();
                    if ($dt->syn == "1") {
                        return $this->responseError("Warning : Data sudah tersyncronize.", 403);
                    }elseif ($dt->flag == "1") {
                        return $this->responseError("Warning : Data Transaksi sudah terposting.", 403);
                    } else {
                        $this->reSyntransMobile($this->post('notransaksi'), 'BKM');
                        return $this->responseSuccess("Data successfully re-synchronized!", [
                            'notransaksi' => $dt->notransaksi,
                            'no_syncronized' => $dt->nosync,
                            'tanggal' => $dt->tanggal
                        ]);
                        // $this->response['error'] = false;
                        // $this->response['message'] = "Data successfully re-synchronized!";
                        // $this->response['notransaksi'] = $dt->notransaksi;
                        // $this->response['no_syncronized'] = $dt->nosync;
                        // $this->response['tanggal'] = $dt->tanggal;
                    }
                }
            }
        } catch (PDOException $e) {
            return $this->responseError("Failed! : Gagal Insert Header (" . $e->getMessage() . ") !!", 409);
            // $this->response['status'] = 409;
            // $this->response['error'] = true;
            // $this->response['message'] = "Failed! : Gagal Insert Header (" . $e->getMessage() . ") !!";
        }
        // }

        if ($this->response['error']) {
            return $this->responseError("Failed! : Gagal Insert " . $this->response['message'], 409);
        }
        // return $this->response;
    }

    public function rawatKehadiran($user, $type)
    {
        try {
            $data['method']          = $this->post('method');
            $data['notransaksi']     = $this->post('notransaksi');
            $data['no_syncronized']  = $this->post('no_syncronized');
            $data['tanggal']         = $this->post('tanggal');
            $data['blok']            = explode(",", $this->post('blok'));
            // $data['kodeorg']         = explode(",", substr($this->post('blok'), 0, 6));
            $data['kodekegiatan']    = explode(",", $this->post('kegiatan'));
            $data['karyawanid']      = explode(",", $this->post('karyawanid'));
            $data['jumlah_hk']       = explode(",", $this->post('jumlah_hk'));
            $data['hasil_kerja']     = explode(",", $this->post('hasil_kerja'));
            $data['insentif']        = explode(",", $this->post('premi'));
            $data['createtime']      = explode(",", $this->post('lastupdate'));
            $data['createby']        = $user['userid'];

            $tahuntanam = array();
            $status = array();

            $Blok = $this->model('Blok');
            $dataBlok = $Blok->getDataBlok("where kodeorg like '" . substr($data['blok'][0], 0, 6) . "%'");
            $sttusBlok = $this->model('Setup_kegiatan')->getDataSetupKegiatan();

            // Divisi ==  unit nya
            // ambil header, klo ada asistensi update divisinya sesuai data detail

            // if ($res->rowCount() > 0) {
            //     while ($bar = $res->fetch()) {
            //         $tahuntanam[$bar->kodeorg] = $bar->tahuntanam;
            //         $status[$bar->kodeorg] = $bar->statusblok;
            //     }
            // }
            if (count($dataBlok) > 0) {
                for ($i = 0; $i < count($dataBlok); $i++) {
                    $tahuntanam[$dataBlok[$i]['kodeorg']] = $dataBlok[$i]['tahuntanam'];
                    $status[$dataBlok[$i]['kodeorg']] = $dataBlok[$i]['statusblok'];
                }
            }
            $db = $this->db->dbname;
            $listHeader = $this->getAktifitas("WHERE tanggal = '{$data['tanggal']}' AND syn = '1'");
            $dataHeader = [];
            if (count($listHeader) > 0) {
                foreach ($listHeader as $key => $value) {
                    $dataHeader[] = $value->notransaksi;
                }
            }
            // print_r($dataHeader);

            $listHk = [];
            foreach ($dataHeader as $value) {
                foreach (['sdm_absensi' => 'jhk'] as $table => $field) {
                    // foreach (['kebun_prestasi_mobile' => 'jumlahhk', 'sdm_absensi' => 'jhk'] as $table => $field) {
                    $query = "SELECT nik, $field AS hk FROM " . $this->db->dbname . ".`$table` WHERE `notransaksi` = '{$value}'";
                    // echo $query;
                    foreach ($this->fetchdata($query) as $record) {
                        $listHk[$record['nik']] = ($listHk[$record['nik']] ?? 0) + $record['hk'];
                    }
                }
            }

            // print_r($listHk);

            $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
            $Setup_app = $this->model('Setup_app');

            $getParam = $Setup_app->getParamAppM($this->user);
            $nilai = $getParam[array_search('mblnoakun', array_column($getParam, 'kodeparameter'))]['nilai'] ?? '';

            // echo $aktifitas->rowCount();
            // $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
            // if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('karyawanid')) != "") {
            if ($aktifitas and count($aktifitas) > 0 and trim($this->post('karyawanid')) != "") {
                // $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();
                $maxNum = $this->getPrestasi('', "WHERE notransaksi = '" . $data['notransaksi'] . "' ")->rowCount();
                // echo $maxNum;
                // echo count($data['karyawanid']);

                for ($i = 0; $i < count($data['karyawanid']); $i++) {
                    // $statusBlock = array_key_exists($data['blok'][$i], $status) ? $status[$data['blok'][$i]] : '';
                    $tahuntanamValue = array_key_exists($data['blok'][$i], $tahuntanam) ? $tahuntanam[$data['blok'][$i]] : '';

                    // $dataAbsensi = $this->getPrestasi('x', "WHERE nik = '" . $data['karyawanid'][$i] . "' AND notransaksi like '%" . substr($data['notransaksi'], 0, 8) . "%' ");

                    $jmlHK = 0;
                    $jmlttl = 0;

                    // if (count($dataAbsensi) > 0) {
                    //     for ($j = 0; $j < count($dataAbsensi); $j++) {
                    //         // echo $jmlHK . " + " . $dataAbsensi[$j]['jumlahhk'];
                    //         $jmlHK += $dataAbsensi[$j]['jumlahhk'];
                    //     }
                    // }
                    // echo $jmlHK;
                    // echo $listHk[$data['karyawanid'][$i]];
                    $jmlHK += (int)$listHk[$data['karyawanid'][$i]];
                    // echo $jmlHK;
                    $jmlttl = $jmlHK + $data['jumlah_hk'][$i];
                    // echo $jmlttl;


                    $Datakaryawan = $this->model('Setup_datakaryawan');
                    $namakaryawan = $Datakaryawan->selectOpt("where karyawanid='" . $data['karyawanid'][$i] . "' limit 1");

                    if ($jmlttl > 1) {
                        $this->response['status'] = 409;
                        $this->response['error'] = true;
                        $this->response['message'] = "Nama karyawan " . $namakaryawan[$data['karyawanid'][$i]] . ", sudah melebihi 1 HK. di System : {$jmlHK} data yang di kirim : {$data['jumlah_hk'][$i]} total : {$jmlttl} HK";
                        break;
                    } else {
                        $maxNum++;
                        $dataArr = array(
                            'notransaksi'             => $this->post('notransaksi'),
                            'nourut'                  => $maxNum,
                            'nik'                     => $data['karyawanid'][$i],
                            // 'noreferensi'             => $this->post('no_syncronized'),
                            // 'kodekegiatan'            => $type,
                            'kodekegiatan'            => $data['kodekegiatan'][$i],
                            'kodeorg'                 => $data['blok'][$i],
                            // 'statusblok'              => $statusBlock,
                            'statusblok'              => $sttusBlok[$data['kodekegiatan'][$i]]['kelompok'],
                            'tahuntanam'              => $tahuntanamValue,
                            'tph'                     => '',
                            'identifikasi'            => '',
                            'hasilkerjapremi'         => $data['insentif'][$i],
                            'bjr'                     => '0',
                            'norma'                   => '0',
                            'outputminimal'           => '0',
                            'flag'                    => '0',
                            'sesi'                    => '',
                            'hasilkerjakg'            => '0',
                            'brondolan'               => '0',
                            'keterangan'              => 'BORONGAN',
                            'tipepanen'               => '',
                            // 'latitude'                => '',
                            // 'longitude'               => '',
                            'processed'               => '',
                            'message'                 => '',
                            'hasilkerja'              => $data['hasil_kerja'][$i],
                            'jumlahhk'                => $data['jumlah_hk'][$i],
                            'createby'                => '0000000180',
                            // 'createby'                => $data['createby'],
                            'createtime'              => $data['createtime'][$i],
                            'kodesegment'             => '1',
                        );

                        $dataInsert[$i] = $this->query_insert($dataArr, $db . ".kebun_prestasi_mobile");

                        $dataKehadiran = array(
                            'notransaksi'    => $this->post('notransaksi'),
                            'nobkm'         => '',
                            'nourut'        => ($i + 1),
                            'nik'           => $data['karyawanid'][$i],
                            'absensi'       => 'H',
                            'jhk'           => $data['jumlah_hk'][$i],
                            // 'umr'           => $data['insentif'][$i],
                            'umr'           => '0',
                            'insentif'      => $data['insentif'][$i],
                            'hasilkerja'    => $data['hasil_kerja'][$i],
                            'keterangan'    => '',
                            'noakun'        => $nilai
                        );

                        $dataInsertKehadiran[$i] = $this->query_insert($dataKehadiran, $db . ".sdm_absensi");
                        $dataUpdateHeader[$i] = $this->query_update(
                            array(
                                "divisi" => substr($data['blok'][$i], 0, 6),
                                "kodeorg" => substr($data['blok'][$i], 0, 4),
                            ),
                            $this->db->dbname . ".kebun_aktifitas_mobile",
                            "notransaksi='" . $this->post('notransaksi') . "' and tipetransaksi = 'BKM' "
                        );
                    }
                }

                if ($this->uri->segments[5] == 'load') {
                    return $dataInsert;
                }
                if (!$this->response['error']) {
                    if (count($dataInsert) > 0) {
                        $qAbsensi = $this->exec($dataInsertKehadiran);
                        $qexeHeader = $this->exec($dataUpdateHeader);
                        if ($qAbsensi) {
                            $this->response['error'] = false;
                            $this->response['message'] = "Success";
                            $this->response['notransaksi'] = $this->post('notransaksi');
                            $this->response['no_syncronized'] = $this->post('no_syncronized');
                            $this->response['tanggal'] = $this->post('tanggal');
                        } else {
                            $this->response['error'] = true;
                            $this->response['message'] = "Failed! : Gagal Insert sdm_absensi kehadiran";
                            // $this->reSyntransMobile($this->post('notransaksi'), 'BKM');
                        }
                        $qexec = $this->exec($dataInsert);
                        if ($qexec) {
                            $this->response['error'] = false;
                            $this->response['message'] = "Success";
                            $this->response['notransaksi'] = $this->post('notransaksi');
                            $this->response['no_syncronized'] = $this->post('no_syncronized');
                            $this->response['tanggal'] = $this->post('tanggal');
                        } else {
                            $this->response['error'] = true;
                            $this->response['message'] = "Failed! : Gagal Insert kebun_prestasi_mobile";
                        }
                    }
                }
                // console

            } else {
                $this->response['status'] = 409;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
            }
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Gagal Insert Kehadiran (" . $e->getMessage() . ") !!";
            $this->execdeleteAllDetail($this->post('notransaksi'));
        }
        return $this->response;
    }

    public function uploadImage()
    {
        $notransaksi    = $this->post('notransaksi');
        $kegiatan       = $this->post('kegiatan');
        $blok           = $this->post('blok');
        // if ($foto != "") {
        try {
            // $path = 'm_fileDocuments/rawat/images/';
            $location = 'm_fileDocuments';
            $linkImg = 'rawat/images/';
            $path =  $location . '/' . $linkImg;

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            } //create Folder if not Exists

            if (!is_writable($path)) {
                $errprop['status']     = 500;
                $errprop['error']      = true;
                $errprop['message']    = "tidak memiliki izin untuk membuat atau mengunggah foto ke folder ini";
            } else {
                $newFileName = $notransaksi . $kegiatan . $blok;
                // $newFileName    = $notransaksi . $this->post('pemanen') . $this->post('tph') . $this->post('sesi');
                $newExtention   = ".jpg";
                $foto_awal      =  $_POST['foto_awal']; //your data in base64 'data:image/png....';
                $foto_akhir     =  $_POST['foto_akhir'];


                $foto_awal      = preg_replace('#^data:image/\w+;base64,#i', '', $foto_awal);
                $foto_awal      = str_replace(' ', '+', $foto_awal);
                $stream         = base64_decode($foto_awal);
                $filename        = $newFileName . $newExtention;
                file_put_contents($path . $filename, $stream);


                $foto_akhir     = preg_replace('#^data:image/\w+;base64,#i', '', $foto_akhir);
                $foto_akhir     = str_replace(' ', '+', $foto_akhir);
                $stream_2       = base64_decode($foto_akhir);
                $filename_2     = $newFileName . '_akhir' . $newExtention;
                file_put_contents($path . $filename_2, $stream_2);

                // $url         = explode("index", $_SERVER['SCRIPT_NAME']);
                // $locationFile = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $url[0];

                if (file_exists($path . $filename)) {
                    $dataUpdate = array(
                        // "photo" => 'http://' . $_SERVER['HTTP_HOST'] . '/mobile/' . $filename,
                        // "photo2" => 'http://' . $_SERVER['HTTP_HOST'] . '/mobile/' . $filename_2,
                        "photo" => $this->base_url($linkImg, $location) . $filename,
                        "photo2" => $this->base_url($linkImg, $location) . $filename_2,
                        "latlong" => $this->post('lat_foto_awal') . "," . $this->post('long_foto_awal'),
                        "latlong2" => $this->post('lat_foto_akhir') . "," . $this->post('long_foto_akhir')
                    );

                    $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_prestasi_mobile", "notransaksi='" . $notransaksi . "' and kodeorg='" . $this->post('blok') . "' and kodekegiatan='" . $this->post('kegiatan') . "' ");
                    if (!$this->response['error']) {
                        $this->response['message'] = "Upload foto berhasil " . $this->base_url($linkImg, $location) . $filename;
                        $this->response['notransaksi'] = $this->post('notransaksi');
                        $this->response['no_syncronized'] = $this->post('no_syncronized');
                        $this->response['tanggal'] = $this->post('tanggal');
                    }
                } else {
                    $this->response['status'] = 409;
                    $this->response['error'] = true;
                    $this->response['message'] = "Failed! : Foto tidak mendapatkan akses untuk di Upload";
                }
            }
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Upload Foto - (" . $e->getMessage() . ") !!";
            $this->execdeleteAllDetail($this->post('notransaksi'));
        }
        // }
        return $this->response;
    }

    public function rawatMaterial()
    {
        $data['no_syncronized'] = $this->post('no_syncronized');
        $data['notransaksi']    = $this->post('notransaksi');
        $data['kodekegiatan']   = explode(",", $this->post('kodekegiatan'));
        $data['kodeorg']        = explode(",", $this->post('blok'));
        $data['gudang']         = explode(",", $this->post('gudang'));
        $data['kodebarang']     = explode(",", $this->post('kodebarang'));
        $data['kwantitas']      = explode(",", $this->post('kwantitas'));
        $data['kwantitasha']    = '';

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() > 0) {
            for ($i = 0; $i < count($data['kodebarang']); $i++) {
                $str = "SELECT notransaksi, sum(ifnull(hasilkerja,0)) as hasilkerja " .
                    "FROM kebun_prestasi_mobile " .
                    "WHERE `notransaksi` = '" . $data['notransaksi'] . "' " .
                    "GROUP BY notransaksi ";
                $res = $this->fetchdata($str);
                $data['kwantitasha'] = $res[0]['hasilkerja'];
                $dataArr = array(
                    'notransaksi'    =>  $data['notransaksi'],
                    'kodekegiatan'   => $data['kodekegiatan'][$i],
                    'kodeorg'        => $data['kodeorg'][$i],
                    'kodebarang'     => $data['kodebarang'][$i],
                    'kwantitas'      => ($data['kwantitas'][$i] == null) ? 0 : $data['kwantitas'][$i],
                    'kwantitasha'    => $data['kwantitasha'],
                    'hargasatuan'    => 0.0,
                    'kodegudang'     => $data['gudang'][$i]
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_pakaimaterial_mobile");
            }
            // console
            if ($this->uri->segments[5] == 'load') {
                return $dataInsert;
            }
            $qexec = $this->exec($dataInsert);
            try {
                if ($qexec) {
                    $log = array(
                        'notransaksi' => $this->post('notransaksi'),
                        'nosync' => $this->post('no_syncronized'),
                        'tanggal' => $this->post('tanggal'),
                        'msg' => 'Success synchronize!',
                        'sts' => '1',
                    );
                    $this->addlogmaterial($log);
                    $this->response['error'] = false;
                    $this->response['message'] = "Success";
                    $this->response['notransaksi'] = $this->post('notransaksi');
                    $this->response['no_syncronized'] = $this->post('no_syncronized');
                    $this->response['tanggal'] = $this->post('tanggal');
                } else {
                    $log = array(
                        'notransaksi' => $this->post('notransaksi'),
                        'nosync' => $this->post('no_syncronized'),
                        'tanggal' => $this->post('tanggal'),
                        'msg' => 'Failed to synchronize!',
                        'sts' => '0',
                    );
                    $this->addlogmaterial($log);
                    $this->response['status'] = 400;
                    $this->response['error'] = true;
                    $this->response['message'] = "Failed! : Gagal Insert kebun_pakaimaterial_mobile";
                    $this->execdeleteAllDetail($this->post('notransaksi'));
                }
            } catch (PDOException $e) {
                $log = array(
                    'notransaksi' => $this->post('notransaksi'),
                    'nosync' => $this->post('no_syncronized'),
                    'tanggal' => $this->post('tanggal'),
                    'msg' => 'Failed to synchronize!',
                    'sts' => '0',
                );
                $this->addlogmaterial($log);
                $this->response['status'] = 400;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : Gagal Insert kebun_pakaimaterial_mobile (" . $e->getMessage() . ") !!";
                $this->execdeleteAllDetail($this->post('notransaksi'));
            }
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }

    public function addlogmaterial($info)
    {
        $data = array(
            'notransaksi' => $info['notransaksi'],
            'tanggal' => $info['tanggal'],
            'nosync' => $info['nosync'],
            'message' => $info['msg'],
            'status' => $info['sts'],
        );

        $res = $this->insert($data, $this->db->dbname . ".log_material");
    }

    //? Check total rows are equals
    public function checkdatarow()
    {
        // $jmldetail      = $this->post('jumlah_kehadiran');
        $jumlah_bkm = $this->post('jumlah_bkm');
        $jumlah_material = $this->post('jumlah_material');
        // $jmlRow         = ((int)$jmldetail + (int)$jumlah_material + (int)$jumlah_bkm);
        $jmlRow         = ((int)$jumlah_material + (int)$jumlah_bkm);
        // $str = "select  notransaksi from " . $this->db->dbname . ".kebun_aktifitas_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        // $str .= " UNION ALL ";
        $str = " select  notransaksi from " . $this->db->dbname . ".kebun_prestasi_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        $str .= " UNION ALL ";
        $str .= " select  notransaksi from " . $this->db->dbname . ".kebun_pakaimaterial_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        if ($this->uri->segments[5] == 'load') {
            return $str;
        }
        $strPrestasi = "select kodeorg from " . $this->db->dbname . ".kebun_prestasi_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        $prestasiCheck = $this->fetchdata($strPrestasi);
        $datacheck = $this->query($str);
        $jmlData = $datacheck->rowCount();
        if ($jmlData == $jmlRow && count($prestasiCheck) >= 0) {
            $dataUpdate = array(
                "syn" => "1",
                "divisi" => substr($prestasiCheck[0]['kodeorg'], 0, 6),
                "kodeorg" => substr($prestasiCheck[0]['kodeorg'], 0, 4),
            );
            $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $this->post('notransaksi') . "'");
            // $this->response['error'] = false;
            // $this->response['message'] = "Sinkronisasi Data Telah Selesai.";
            // $this->response['notransaksi'] = $this->post('notransaksi');
            // $this->response['no_syncronized'] = $this->post('no_syncronized');
            // $this->response['tanggal'] = $this->post('tanggal');
            if (!$this->response['error']) {
                return $this->responseSuccess("Sinkronisasi Data Telah Selesai.", [
                    'notransaksi' => $this->post('notransaksi'),
                    'no_syncronized' => $this->post('no_syncronized'),
                    'tanggal' => $this->post('tanggal')
                ]);
            } else {
                return $this->responseError("Failed! : Gagal Update " . $this->response['message'], 409);
            }
        } else {
            // $this->response['status'] = 409;
            // $this->response['error'] = true;
            // $this->response['message'] = "Failed! : Data Syncronized (" . $datacheck->rowCount() . "/" . $jmlRow . ") Belum Lengkap, Mohon untuk diSyncronized Ulang " . $str;
            // $this->execdeleteAllDetail($this->post('notransaksi'));
            return $this->responseError("Failed! : Data Syncronized ({$jmlData}/{$jmlRow}) Belum Lengkap, Mohon Sync Ulang $this->response['message']", 409);
        }
        // return $this->response;
        return $this->responseError("Failed! : Gagal Syncronize " . $this->response['message'], 409);
    }

    function checkAktifitas($from, $whr)
    {
        $data = array();
        $q = "SELECT * FROM " . $from . $whr;
        $data = $this->query($q);
        return $data;
    }

    //? Get Aktifitas
    public function getAktifitas($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }

    public function getAktifitasErp($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile " . $where;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    //? Get Prestasi
    private function getPrestasi($f = '', $where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_prestasi_mobile {$where}";
        // echo $q;
        $data = ($f == '') ? $this->query($q) : $this->fetchdata($q);
        return $data;
    }
    //? Get Prestasi
    public function getDetailPrestasi($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_prestasi_mobile {$where}";
        $data = $this->fetchdata($q);
        // print_r($data);
        return $data;
    }

    public function getDetailPrestasi2($whr)
    {
        $data = array();
        $q = "
        SELECT SUM(hasilkerjapremi) as hasilkerjapremi,
        nik as karyawanid,left(createtime, 10)as tanggal, 
        kodeorg, 
        kodekegiatan, SUM(hasilkerja) as hasilkerja, SUM(jumlahhk) as jumlahhk,
        photo, photo2, statusblok
        FROM " . $this->db->dbname . ".kebun_prestasi_mobile " . $whr . " 
        GROUP BY nik,kodekegiatan, kodeorg, photo, photo2, statusblok, createtime;
        ";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $temp = array();
            for ($i = 0; $i < count($r); $i++) {
                $hk = 0.0;
                $jmlHK = 0.0;
                $hk = (float)$r[$i]['hasilkerja'];
                $jmlHK = (float)$r[$i]['jumlahhk'];
                if (!in_array($i, $temp)) {
                    for ($j = 0; $j < count($r); $j++) {
                        if ($i != $j) {
                            if ($r[$i]['kodeorg'] == $r[$j]['kodeorg'] && $r[$i]['kodekegiatan'] == $r[$j]['kodekegiatan']) {
                                if (!in_array($j, $temp)) {
                                    $hk = $hk + (float)$r[$j]['hasilkerja'];
                                    $jmlHK = $jmlHK + (float)$r[$j]['jumlahhk'];
                                    array_push($temp, $j);
                                }
                            }
                        }
                    }
                    $hk = number_format($hk, 2, '.', '');
                    $jmlHK = number_format($jmlHK, 2, '.', '');
                    array_push($data, array(
                        "tanggal" => $r[$i]['tanggal'],
                        "kodeorg" => $r[$i]['kodeorg'],
                        "kodekegiatan" => $r[$i]['kodekegiatan'],
                        "hasilkerja" => $hk,
                        "jumlahhk" => $jmlHK,
                        "photo" => $r[$i]['photo'],
                        "photo2" => $r[$i]['photo2'],
                        "karyawanid" => $r[$i]['karyawanid'],
                        "statusblok" => $r[$i]['statusblok'],
                        "hasilkerjapremi" => $r[$i]['hasilkerjapremi'],
                    ));
                }
            }
            $temp = array();
        }
        // print_r($data);  
        return $data;
    }

    public function detailprestasi($whr)
    {
        $data = array();
        $q = "
        SELECT SUM(hasilkerjapremi) as hasilkerjapremi,
        nik as karyawanid,left(createtime, 10)as tanggal, 
        kodeorg, 
        kodekegiatan, SUM(hasilkerja) as hasilkerja, SUM(jumlahhk) as jumlahhk,
        photo, photo2, statusblok
        FROM " . $this->db->dbname . ".kebun_prestasi_mobile " . $whr . " 
        GROUP BY nik,kodekegiatan, kodeorg, photo, photo2, statusblok, createtime;
        ";
        $data = $this->fetchdata($q);
        // print_r($data);  
        return $data;
    }

    public function getDetailMaterial($whr)
    {
        $data = array();
        $q = "
        SELECT * FROM " . $this->db->dbname . ".kebun_pakaimaterial_mobile " . $whr;
        $data = $this->fetchdata($q);
        // print_r($data);
        return $data;
    }

    public function getStatusBlok($whr)
    {
        $data = array();
        $model = $this->model('Setup_kegiatan');
        $data = $model->statusBlok($whr);
        return $data;
    }


    public function getDetailKehadiran($where)
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_prestasi_mobile {$where}";
        // $q = "select * from owlMobile.kebun_prestasi_mobile {$where}";
        // echo $q;
        $data = $this->fetchdata($q);
        // print_r($data);
        // echo count($data);
        return $data;
    }

    private function getAbsensi($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".sdm_absensi {$where}";
        $data = $this->fetchdata($q);
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
            $this->delete($this->db->dbname . ".sdm_absensi", "notransaksi='" . $notransaksi . "'");
            $this->delete($this->db->dbname . ".kebun_pakaimaterial_mobile", "notransaksi='" . $notransaksi . "'");
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Resyncronize (" . $e->getMessage() . ") !!";
        }
    }

    public function deleteAlldetailRawat($whr)
    {
        $aktifitas = $this->query_delete('kebun_aktifitas_mobile', $whr);
        $qexec = $this->exec($aktifitas);
        if ($qexec) {
            $this->response['error'] = false;
            $this->exec($this->query_delete('kebun_prestasi_mobile', $whr));
            $this->exec($this->query_delete('sdm_absensi', $whr));
            $this->exec($this->query_delete('kebun_pakaimaterial_mobile', $whr));
        } else {
            $this->response['error'] = true;
        }
        return $this->response;
    }
    // public function deleteAlldetailRawatKeepAktifitas($whr, $del)
    public function deleteAlldetailRawatKeepAktifitas($from, $whr, $del)
    {
        $aktifitas = $this->checkAktifitas($from, $whr);
        if ($aktifitas) {
            $this->response['error'] = false;
            $this->exec($this->query_delete('kebun_prestasi_mobile', $del));
            $this->exec($this->query_delete('sdm_absensi', $del));
            $this->exec($this->query_delete('kebun_pakaimaterial_mobile', $del));
        } else {
            $this->response['error'] = true;
        }
        return $this->response;
    }

    function execdeleteAllDetail($notransaksi)
    {
        // $from = "(select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where flag = '0') a ";
        // $whr = "where a.syn = '0' and a.notransaksi = '" . $notransaksi . "'";
        // $del = "notransaksi='" . $notransaksi . "'";
        // $this->deleteAlldetailRawatKeepAktifitas($from, $whr, $del);
    }
    public function postingBkm($whr)
    {
        $arr = [
            'jurnal' => 1
        ];
        $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
        //    echo $aktifitas;
        return $this->response;
    }
    public function getNamaKegiatan($where)
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".setup_kegiatan {$where}";
        $data = $this->fetchdata($q);
        // echo $q;
        return $data;
    }
    public function namaHari($tanggal)
    {
        $day = date('D', strtotime($tanggal));
        $dayList = array(
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu'
        );
        return $dayList[$day];
    }

    public function headerERP()
    {
        $param['dtFrom'] = $this->post('dtFrom');
        $param['dtTo'] = $this->post('dtTo');
        $param['notransaksi'] = $this->post('notransaksi');
        $param['divisi'] = $this->post('divisi');
        $param['periode'] = $this->post('periode');
        $param['mandor'] = $this->post('mandor');

        $where = "where tipetransaksi = 'BKM'";

        ($param['notransaksi'] != null || $param['notransaksi'] != "") ? $where = $where . " and notransaksi = '{$param['notransaksi']}'" : null;
        ($param['divisi'] != null || $param['divisi'] != "") ? $where = $where . " and divisi = '{$param['divisi']}'" : null;
        ($param['mandor'] != null || $param['mandor'] != "") ? $where = $where . " and gangcode = '{$param['mandor']}'" : null;
        ($param['periode'] != null || $param['periode'] != "") ? $where = $where . " and tanggal like '%{$param['periode']}%'" : null;

        if (($param['dtFrom'] != null && $param['dtFrom'] != "") && ($param['dtTo'] == null || $param['dtTo'] == "")) {
            $where = $where . " and tanggal >= '{$param['dtFrom']}'";
        }
        if (($param['dtTo'] != null && $param['dtTo'] != "") && ($param['dtFrom'] == null || $param['dtFrom'] == "")) {
            $where = $where . " and tanggal <= '{$param['dtTo']}'";
        }
        if (($param['dtFrom'] != null && $param['dtFrom'] != "") && ($param['dtTo'] != null && $param['dtTo'] != "")) {
            $where = $where . " and tanggal >= '{$param['dtFrom']}' and tanggal <= '{$param['dtTo']}'";
        }
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile " . $where;
        $res = $this->fetchData($q);
        if (count($res) > 0) {
            foreach ($res as $k => $v) {
                $d['notransaksi'] = $v['notransaksi'];
                $d['tipetransaksi'] = $v['tipetransaksi'];
                $d['tanggal'] = $v['tanggal'];
                $d['kodeorg'] = $v['kodeorg'];
                $d['divisi'] = $v['divisi'];
                $d['gangcode'] = $v['gangcode'];
                $d['nikmandor'] = $v['nikmandor'];
                $d['nikasisten'] = $v['nikasisten'];
                $d['kerani'] = $v['kerani'];
                $d['noreferensi'] = $v['noreferensi'];
                $d['createby'] = $v['createby'];
                $d['createtime'] = $v['createtime'];
                $data[] = $d;
            }
        }
        return $data;
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
        // $kodeorg = $this->post('kodeorg') ?: $user['lokasitugas'];
        $whr .= $this->post('kodeorg') ? " AND kodeorg IN ('" . str_replace(',', "','", $this->post('kodeorg')) . "')" : '';

        if ($this->post('dateFrom') && $this->post('dateTo')) {
            $whr .= " AND tanggal BETWEEN '" . $this->post('dateFrom') . "' AND '" . $this->post('dateTo') . "'";
        }

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

    public function detailERP()
    {
        $param['notransaksi'] = $this->post('notransaksi');
        if (!$param['notransaksi']) {
            return $this->responseError("Parameter notransaksi Harus diisi", 400);
        }
        $db = $this->db->dbname;

        $model = $this->model('Setup_kegiatan');
        // $karyawan = $model->getDataKaryawan();
        $kegiatan = $model->getDataSetupKegiatan();
        $prestasiData = array();
        $kehadiranData = array();
        $materialData = array();
        $headerData = array();

        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile where notransaksi = '{$param['notransaksi']}' and tipetransaksi = 'BKM' and flag = '1'";
        $r = $this->fetchdata($q);

        if (count($r) > 0) {
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : data already posted !!";
        } else {

            $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile where notransaksi = '{$param['notransaksi']}' and tipetransaksi = 'BKM'";
            $r = $this->fetchdata($q);
            if (count($r) > 0) {
                $headerData = array(
                    'notransaksi' => $r[0]['notransaksi'],
                    'tipetransaksi' => $r[0]['tipetransaksi'],
                    'tanggal' => $r[0]['tanggal'],
                    'kodeorg' => $r[0]['kodeorg'],
                    'divisi' => $r[0]['divisi'],
                    'gangcode' => $r[0]['gangcode'],
                    'nikmandor' => $r[0]['nikmandor'],
                    'nikasisten' => $r[0]['nikasisten'],
                    'kerani' => $r[0]['kerani'],
                    'noreferensi' => $r[0]['noreferensi'],
                    'photo' => $r[0]['photo'],
                    'photo2' => $r[0]['photo2'],
                    'createby' => $r[0]['createby'],
                    'createtime' => $r[0]['createtime'],
                    'deviceid' => $r[0]['deviceid']
                );
            }

            $prestasi = $this->detailprestasi("where notransaksi = '{$param['notransaksi']}'");
            if ($prestasi > 0) {
                foreach ($prestasi as $k => $v) {
                    // TODO
                    $d['karyawanid'] = $v['karyawanid'];
                    $d['tanggal'] = $v['tanggal'];
                    $d['kodeorg'] = $v['kodeorg'];
                    $d['kodekegiatan'] = $v['kodekegiatan'];
                    $d['namakegiatan'] = $kegiatan[$v['kodekegiatan']]['namakegiatan'];
                    $d['satuan'] = $kegiatan[$v['kodekegiatan']]['satuan'];
                    $d['hasilkerja'] = $v['hasilkerja'];
                    $d['jumlahhk'] = $v['jumlahhk'];
                    $d['hasilkerjapremi'] = $v['hasilkerjapremi'];
                    $d['photo'] = $v['photo'];
                    $d['photo2'] = $v['photo2'];
                    // $d['statusblok'] = $v['statusblok'];
                    $d['statusblok'] = $v['statusblok'] ?: $kegiatan[$v['kodekegiatan']]['kelompok'];
                    $prestasiData[] = $d;
                }
            }

            // $kehadiran = $this->getDetailKehadiranNew("where a.notransaksi = '{$param['notransaksi']}'");
            $kehadiran = $this->getDetailKehadiran("where notransaksi = '{$param['notransaksi']}'");
            if ($kehadiran > 0) {
                foreach ($kehadiran as $a => $b) {
                    // TODO
                    // echo $b['nik'] . ", ";
                    // var_dump($karyawan);
                    $mdlab = $this->model('Setup_Absensi');
                    $karyawanNm = $mdlab->getDataKaryawan("where karyawanid like '%{$b['nik']}%'");
                    $h['namakegiatan'] = $kegiatan[$b['kodekegiatan']]['namakegiatan'];
                    $h['kodeorg'] = $b['kodeorg'];
                    $h['namakaryawan'] = $karyawanNm;
                    // $h['namakaryawan'] = $karyawan[$b['nik']]['namakaryawan'];
                    $h['karyawanid'] = $b['nik'];
                    $h['kodekegiatan'] = $b['kodekegiatan'];
                    $h['hasilkerja'] = $b['hasilkerja'];
                    $h['hasilkerjapremi'] = $b['hasilkerjapremi'];
                    $h['jumlahhk'] = $b['jumlahhk'];
                    $kehadiranData[] = $h;
                }
            }

            $q = "SELECT * 
                FROM {$this->db->dbname}.kebun_aktifitas_mobile a 
                LEFT JOIN {$this->db->dbname}.sdm_absensi b ON a.notransaksi = b.notransaksi  
                WHERE a.noreferensi = '{$param['notransaksi']}' AND a.tipetransaksi='ABS'";
            $abs = $this->fetchdata($q);

            $dataAbsensi = [];
            if (count($abs) > 0) {
                foreach ($abs as $key => $value) {
                    $dataAbsensi[$key] = $value;
                }
            }

            $material = $this->getDetailMaterial("where notransaksi = '{$param['notransaksi']}'");

            if ($material > 0) {
                foreach ($material as $c => $d) {
                    $modelMaterial = $this->model('Setup_Absensi');
                    $mat = $modelMaterial->materialDtl("where kodebarang = '{$d['kodebarang']}'");
                    // TODO
                    $m['namakegiatan'] = $kegiatan[$d['kodekegiatan']]['namakegiatan'];
                    $m['kodeorg'] = $d['kodeorg'];
                    $m['namabarang'] = $mat[0]['namabarang'];
                    $m['satuan'] = $mat[0]['satuan'];
                    $m['kwantitas'] = $d['kwantitas'];
                    $m['kodegudang'] = $d['kodegudang'];
                    $m['kodebarang'] = $d['kodebarang'];
                    $m['kodekegiatan'] = $d['kodekegiatan'];
                    $materialData[] = $m;
                }
            }

            $data = array(
                'Header' => $headerData,
                'Prestasi' => $prestasiData,
                'Kehadiran' => $kehadiranData,
                'Material' => $materialData,
                'kehadiranumum' => $dataAbsensi
            );

            // $this->response['result'] = $data;
            return $data;
        }
    }

    function postERPlast()
    {
        $param['notransaksi'] = $this->post('notransaksi');
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile where notransaksi = '{$param['notransaksi']}' and tipetransaksi = 'BKM' and flag = '1'";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : data already posted !!";
        } else {
            try {
                $dataUpdate = array(
                    "flag" => "1"
                );
                $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $param['notransaksi'] . "' and tipetransaksi = 'BKM'");
                $this->response['message'] = "Data successfully posted !!";
            } catch (PDOException $e) {
                $this->response['status'] = 409;
                $this->response['error'] = true;
                $this->response['message'] = "Failed! : (" . $e->getMessage() . ") !!";
            }
        }
        return $this->response;
    }

    public function postERP()
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

        $abs = $this->getAktifitas("WHERE noreferensi = '{$dt->notransaksi}' AND tipetransaksi='ABS' LIMIT 1")->fetch();
        
        $this->update(['flag' => $flag], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='$notransaksi' AND tipetransaksi='BKM'");
        $this->update(['flag' => $flag], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='$abs->notransaksi' ");
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
