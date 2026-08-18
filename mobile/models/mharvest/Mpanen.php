<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mpanen extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_prestasi_mobile", "kebun_gerdang_mobile", "kebun_mutubuah_mobile");
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
        $data['tanggal']            = $this->post('tanggal');
        $data['gangcode']           = $this->post('kode_kemandoran');
        $data['nikasisten']         = $this->post('asisten');
        $data['nikmandor']          = $this->post('mandor');
        $data['nikmandor1']         = $this->post('mandor1');
        $data['kerani']             = $this->post('kerani_panen');
        $data['tipetransaksi']      = $type;
        // $data['no_syncronized']     = $this->post('notransaksi');
        $data['kodeorg']            = substr($this->post('asistensi'), 0, 4);
        $data['divisi']             = $this->post('asistensi');
        $data['createby']           = $user['userid'];
        $data['updateby']           = $user['userid'];
        if (empty($data['kodeorg']) or $data['kodeorg'] == "" or $data['kodeorg'] == null) {
            $data['kodeorg'] = $user['lokasitugas'];
        }
        if (empty($data['divisi']) or $data['divisi'] == "" or $data['divisi'] == null) {
            $data['divisi'] = $user['subbagian'];
        }
        $data['deviceid']             = $user['uuid'];
        $data['createtime']         = $this->post('lastupdate');
        // console
        if ($this->uri->segments[5] == 'load') {
            return $data;
        }
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
                return $this->responseError("Warning : Data Transaksi sudah terposting.", 403);
            } else {
                $this->reSyntransMobile($this->post('notransaksi'), 'PNN');
                return $this->responseSuccess("Success Re-Syncronized", [
					'notransaksi' => $dt->notransaksi,
					'no_syncronized' => $dt->nosync,
					'tanggal' => $dt->tanggal
				]);
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

        $Blok = $this->model('Blok');
        $dataBlok = $Blok->getDataBlok("where kodeorg like '" . substr($data['blok'][0], 0, 6) . "%'");

        if (count($dataBlok) > 0) {
            for ($i = 0; $i < count($dataBlok); $i++) {
                $tahuntanam[$dataBlok[$i]['kodeorg']] = $dataBlok[$i]['tahuntanam'];
                $status[$dataBlok[$i]['kodeorg']] = $dataBlok[$i]['statusblok'];
            }
        }

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('pemanen')) != "") {
            $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();
            for ($i = 0; $i < count($data['pemanen']); $i++) {
                $statusBlock = array_key_exists($data['blok'][$i], $status) ? $status[$data['blok'][$i]] : '';
                $tahuntanamValue = array_key_exists($data['blok'][$i], $tahuntanam) ? $tahuntanam[$data['blok'][$i]] : '';
                $maxNum++;
                $dataArr = array(
                    'notransaksi'   => $this->post('notransaksi'),
                    'nourut'        => $maxNum,
                    'nik'           => $data['pemanen'][$i],
                    'kodekegiatan'  => $type,
                    'kodeorg'       => $data['blok'][$i],
                    'tph'           => $data['tph'][$i],
                    'sesi'          => $data['sesi'][$i],
                    'cetak'         => $data['cetak'][$i],
                    'cetak'         => $data['cetak'][$i],
                    'tipepanen'     => $data['tipepanen'][$i],
                    'hasilkerja'    => $data['janjang'][$i],
                    'brondolan'     => $data['brondolan'][$i],
                    'kodesegment'   => '1',
                    'statusblok'    => $statusBlock,
                    'tahuntanam'    => $tahuntanamValue,
                    'updateby'      => $user['userid'],
                    'createby'      => $user['userid'],
                    'createtime'    => $data['createtime'][$i],
                    'edited_ai'     => $data['edited'][$i],
                    'janjang_ai'    => $data['janjang_ai'][$i],
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
                $this->response['message'] = "Success Insert Detail";
                $this->response['notransaksi'] = $this->post('notransaksi');
                $this->response['no_syncronized'] = $this->post('no_syncronized');
                $this->response['tanggal'] = $this->post('tanggal');
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Gagal Insert Detail Prestasi !";
                $this->execdeleteAllDetailPanen($this->post('notransaksi'));
            }
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }

    public function addgerdang()
    {
        $data['notransaksi']        = $this->post('notransaksi');
        $data['tanggal']            = $this->post('tanggal');
        // $data['no_syncronized']     = $this->post('no_syncronized');
        // DATA ARRAY ===
        $data['pemanen']            = explode(",", $this->post('pemanen'));
        // $data['tipe']               = explode(",",$this->post('tipe_panen_pemanen'));
        $data['nik_gerdang']        = explode(",", $this->post('gerdang'));
        $data['tipe_gerdang']       = explode(",", $this->post('tipe_panen'));

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('pemanen')) != "") {
            for ($i = 0; $i < count($data['pemanen']); $i++) {
                $dataArr = array(
                    'notransaksi' => $this->post('notransaksi'),
                    'nik' => $data['pemanen'][$i],
                    // 'tipe'=>$data['tipe'][$i],
                    'nik_gerdang' => $data['nik_gerdang'][$i],
                    'tipe_gerdang' => $data['tipe_gerdang'][$i]
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_gerdang_mobile");
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
                $this->response['error'] = true;
                $this->response['message'] = "Gagal Insert Detail Gerdang ! Harap Sinkronisasi Ulang";
                $this->execdeleteAllDetailPanen($this->post('notransaksi'));
            }
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }
    public function addmutubuah($user)
    {
        $data['notransaksi']        = $this->post('notransaksi');
        $data['tanggal']            = $this->post('tanggal');
        // $data['no_syncronized']     = $this->post('no_syncronized');
        // DATA ARRAY ===
        $data['tph']        = explode(",", $this->post('tph'));
        $data['pemanen']    = explode(",", $this->post('pemanen'));
        $data['sesi']       = explode(",", $this->post('sesi'));
        $data['kode']       = explode(",", $this->post('kode'));
        $data['jml']        = explode(",", $this->post('jml'));

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
            $maxNum = $this->getMutubuah("WHERE notransaksi = '" . $data['notransaksi'] . "'")->rowCount();
            for ($i = 0; $i < count($data['tph']); $i++) {
                $maxNum++;
                $valueidjenis = @$kebun_5jenismutu[$data['kode'][$i]];
                $dataArr = array(
                    'notransaksi' => $data['notransaksi'],
                    'kodeorg'      => substr(trim($data['tph'][$i]), 0, 9),
                    'tph'          => $data['tph'][$i],
                    'nik'          => $data['pemanen'][$i],
                    'tglpanen'     => $data['tanggal'],
                    'sesi'           => $data['sesi'][$i],
                    'tipedetail' => @$tipedetail[$data['kode'][$i]],
                    'nourut'      => $maxNum,
                    'idjenis'      => $valueidjenis,
                    'kodedenda'  => $data['kode'][$i],
                    'nilai'      => $data['jml'][$i],
                    'updateby'     => $user['userid']
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
                $this->execdeleteAllDetailPanen($this->post('notransaksi'));
            }
        } else {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Aktifitas Belum terbentuk";
        }
        return $this->response;
    }
    public function checkdatarow()
    {
        $jmldetail      = $this->post('jumlah_detail');
        $jumlah_gerdang = $this->post('jumlah_gerdang');
        $jumlah_grading = $this->post('jumlah_grading');
        $jumlah_hama    = $this->post('jumlah_hama');
        $jmlRow         = ((int)$jmldetail + (int)$jumlah_grading + (int)$jumlah_gerdang + (int)$jumlah_hama);
        $str = "select  notransaksi from " . $this->db->dbname . ".kebun_prestasi_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        $str .= " UNION ALL ";
        $str .= " select  notransaksi from " . $this->db->dbname . ".kebun_mutubuah_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        $str .= " UNION ALL ";
        $str .= " select  notransaksi from " . $this->db->dbname . ".kebun_gerdang_mobile where notransaksi = '" . $this->post('notransaksi') . "' ";
        // console
        // echo $str;
        if ($this->uri->segments[5] == 'load') {
            return $str;
        }
        $ckorg = "select kodeorg from " . $this->db->dbname . ".kebun_prestasi_mobile where notransaksi = '" . $this->post('notransaksi') . "' limit 1";
        $dataorg = $this->fetchData($ckorg);
        $datacheck = $this->query($str);
        if ($datacheck->rowCount() == $jmlRow) {
            $dataUpdate = array(
                "syn" => "1",
                "divisi" => substr($dataorg[0]['kodeorg'], 0, 6),
            );
            $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_aktifitas_mobile", "notransaksi='" . $this->post('notransaksi') . "'");
            if (!$this->response['error']) {
                return $this->responseSuccess("Sinkronisasi Data Telah Selesai.", [
                    'notransaksi' => $this->post('notransaksi'),
                    'no_syncronized' => $this->post('no_syncronized'),
                    'tanggal' => $this->post('tanggal')
                ]);
            } else {
                return $this->responseError("Failed! : Gagal Update " . $this->response['message'], 409);
            }

            $this->response['error'] = false;
            $this->response['message'] = "Sinkronisasi Data Telah Selesai.";
            $this->response['notransaksi'] = $this->post('notransaksi');
            $this->response['no_syncronized'] = $this->post('no_syncronized');
            $this->response['tanggal'] = $this->post('tanggal');
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Data Syncd (" . $datacheck->rowCount() . "/" . $jmlRow . ") Belum Lengkap, Mohon Sync Ulang";
            $this->execdeleteAllDetailPanen($this->post('notransaksi'));
        }
        return $this->response;
    }

    public function locTugas($where)
    {
        $data = array();
        $q = "select kodeorg from " . $this->db->dbname . ".user where " . $where;
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data[] = $r;
        }

        return $data[0];
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
                    $prestasi = $this->getPrestasi("WHERE notransaksi = '" . $this->post('notransaksi') . "' AND nik = '" . $this->post('pemanen') . "' AND tph = '" . $this->post('tph') . "' AND sesi='" . $this->post('sesi') . "'");
                    if ($prestasi->rowCount() > 0) {
                        $dataPrestasi = $prestasi->fetch();
                        $newFileName    = $notransaksi . $dataPrestasi->nourut;
                        $newExtention   = ".jpg";
                        $file               = preg_replace('#^data:image/\w+;base64,#i', '', $file);
                        $file               = str_replace(' ', '+', $file);
                        $stream             = base64_decode($file);
                        $filename           = $newFileName . $newExtention;
                        file_put_contents($path . $filename, $stream);

                        $newFileName_2      = $newFileName . "_ai";
                        $file_2             = preg_replace('#^data:image/\w+;base64,#i', '', $file_2);
                        $file_2             = str_replace(' ', '+', $file_2);
                        $stream_2           = base64_decode($file_2);
                        $filename_2         = $newFileName_2 . $newExtention;
                        file_put_contents($path . $filename_2, $stream_2);

                        if (file_exists($path . $filename)) {
                            $dataUpdate = array(
                                "photo" => $this->base_url($linkImg, $location) . $filename,
                                "photo2" => $this->base_url($linkImg, $location) . $filename_2,
                                // "photo" => 'http://' . $_SERVER['HTTP_HOST'] . '/mobile/' . $filename,
                                // "photoakhir" => 'http://' . $_SERVER['HTTP_HOST'] . '/mobile/' . $filename_2,
                                "latlong" => $this->post('latitude') . "," . $this->post('longitude')
                            );
                            $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_prestasi_mobile", "notransaksi='" . $notransaksi . "' and nik='" . $this->post('pemanen') . "' and tph='" . $this->post('tph') . "' and sesi='" . $this->post('sesi') . "'");
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
    public function setup_tipepanen($where)
    {
        $data = array();
        $kodejenis_data = array(); // Menyimpan data berdasarkan kodejenis

        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5tipepanen {$where}";

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
            $where .= " ORDER BY tanggal DESC LIMIT " . implode(",", $pageLimit);
        }
        $data = $this->getAktifitas($where);
        return $data;
    }

    function getAktifitas($where = '')
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_aktifitas_mobile {$where}";
        // echo $q;
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

    public function getDetailData($notxn)
    {
        $detail = array();
        $dataPrestasi = array();
        $dataRekap = array();
        $dataVerifikasi = array();
        $q = "select a.notransaksi, a.nik, a.kodeorg, a.tph, a.tahuntanam, a.sesi, a.photo, a.photo2, a.hasilkerja, a.brondolan, a.jumlahhk,a.janjang_ai from "
            . $this->db->dbname . ".kebun_prestasi_mobile a left join " . $this->db->dbname . ".kebun_mutubuah_mobile b 
        on a.notransaksi = b.notransaksi and a.tph = b.tph and a.sesi = b.sesi " . "where a.notransaksi = '$notxn' " . "
        group by a.notransaksi, a.nik, a.kodeorg, a.tahuntanam, a.tph, a.sesi, a.photo, a.photo2,a.hasilkerja, a.brondolan, a.jumlahhk,a.janjang_ai";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $dataPrestasi = $r;
            // GET DATA REKAP
            $q2 = "select a.notransaksi, a.nik, a.kodeorg, sum(a.hasilkerja) as hasilkerja, b.tanggal, b.gangcode 
            from " . $this->db->dbname . ".kebun_prestasi_mobile a 
            left join " . $this->db->dbname . ".kebun_aktifitas_mobile b on a.notransaksi = b.notransaksi
            where a.notransaksi = '$notxn'
            group by a.kodekegiatan, a.nik, a.kodeorg, b.tanggal";
            $r2 = $this->fetchdata($q2);
            if (count($r2) > 0) {
                $preArr = array();
                $preArr = $r2;
                $preDiv = substr($preArr[0]['kodeorg'], 0, 6);
                $qPr1 = "SELECT * FROM " .  $this->db->dbname . ".kebun_luaspanen where tanggalpanen = '{$preArr[0]['tanggal']}' and mandor = '{$preArr[0]['gangcode']}'";
                $rPre = $this->fetchdata($qPr1);
                if (count($rPre) > 0) {
                    for ($i = 0; $i < count($rPre); $i++) {
                        $qPre2 = "SELECT * FROM " .  $this->db->dbname . ".kebun_luaspanen_dt where notransaksi = '{$rPre[$i]['notransaksi']}' and blok = '{$preArr[$i]['kodeorg']}' 
                        and pemanen = '{$preArr[$i]['nik']}'";
                        $rPre2 = $this->fetchData($qPre2);
                        if (count($rPre2) > 0) {
                            array_push($preArr[$i], $preArr[$i]["luas_rencana"] = $rPre2[0]['luas_rencana']);
                            array_push($preArr[$i], $preArr[$i]["luas_aktual"] = $rPre2[0]['luas_aktual']);
                        }
                        $datakaryawan = $this->model('Setup_datakaryawan');
                        $pnnNM = $datakaryawan->selectPemanenNm("where karyawanid = '{$preArr[$i]['nik']}'");
                        array_push($preArr[$i], $preArr[$i]["nama"] = $pnnNM);
                    }
                    $dataRekap = $preArr;
                } else {
                    for ($i = 0; $i < count($preArr); $i++) {
                        array_push($preArr[$i], $preArr[$i]["luas_rencana"] = '');
                        array_push($preArr[$i], $preArr[$i]["luas_aktual"] = '');
                        $datakaryawan = $this->model('Setup_datakaryawan');
                        $pnnNM = $datakaryawan->selectPemanenNm("where karyawanid = '{$preArr[$i]['nik']}'");
                        array_push($preArr[$i], $preArr[$i]["nama"] = $pnnNM);
                    }
                    $dataRekap = $preArr;
                }
                // for ($i = 0; $i < count($r2); $i++) {
                //     $datakaryawan = $this->model('Setup_datakaryawan');
                //     $pnnNM = $datakaryawan->selectPemanenNm("where karyawanid = '{$r2[$i]['nik']}'");
                //     array_push($r2[$i], $r2[$i]["nama"] = $pnnNM);
                // }
            }
        }

        $q3 = "SELECT * FROM " . $this->db->dbname . ".kebun_prestasi_mobile " . "where noreferensi = '$notxn' and
        noreferensi is not null and noreferensi != '' and noreferensi != ' '";
        $r3 = $this->fetchdata($q3);
        if (count($r3) > 0) {
            $dataVerifikasi = $r3;
        }
        $detail = array(
            'prestasi' => $dataPrestasi,
            'rekap' => $dataRekap,
            'verifikasi' => $dataVerifikasi
        );
        // print_r($detail);
        return $detail;
    }

    public function dataVerifikasiView($notxn)
    {
        $data = array();
        $q3 = "SELECT * FROM " . $this->db->dbname . ".kebun_prestasi_mobile " . "where notransaksi = '$notxn' and
        noreferensi is not null and noreferensi != '' and noreferensi != ' '";
        $r3 = $this->fetchdata($q3);
        if (count($r3) > 0) {
            $data = $r3;
        }
        return $data;
    }

    public function kodeMutu()
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5jenismutu where jenis = 'Mutu Buah'";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    public function getMutuQty($where)
    {
        $data = '';
        $q = "SELECT nilai FROM " . $this->db->dbname . ".kebun_mutubuah_mobile " . $where;
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r[0]['nilai'];
        }
        return $data;
    }

    public function updateJJG($whr, $val)
    {
        try {
            $data = array(
                "hasilkerja" => $val
            );
            $this->update($data, $this->db->dbname . ".kebun_prestasi_mobile ", $whr);
        } catch (PDOException $e) {
            //throw $th;
        }
    }

    public function saveEditDtl($params, $val)
    {
        $where = "notransaksi = '{$params['notransaksi']}' and kodeorg = '{$params['kodeorg']}' and tph = '{$params['tph']}' and sesi = '{$params['sesi']}' and kodedenda = '{$params['kodedenda']}'";
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_mutubuah_mobile where " . $where;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            //! FIND IF EXISTS
            try {
                $data = array(
                    "nilai" => $val
                );
                $this->update($data, $this->db->dbname . ".kebun_mutubuah_mobile ", $where);
            } catch (PDOException $e) {
                //throw $th;
            }
        } else {
            //! IF NOT EXISTS
            $whr = "notransaksi = '{$params['notransaksi']}' and kodeorg = '{$params['kodeorg']}' and tph = '{$params['tph']}'";
            $q2 = "SELECT * FROM " . $this->db->dbname . ".kebun_mutubuah_mobile where " . $whr . " order by nourut desc";
            $r2 = $this->fetchdata($q2);
            if (count($r2) > 0) {
                $dataArr = array(
                    'notransaksi' => $r2[0]['notransaksi'],
                    'kodeorg' => $r2[0]['kodeorg'],
                    'tph' => $r2[0]['tph'],
                    'nik' => $r2[0]['nik'],
                    'kemandoran' => $r2[0]['kemandoran'] ?? null,
                    'tglpanen' => $r2[0]['tglpanen'],
                    'sesi' => $r2[0]['sesi'],
                    'noreferensi' => $r2[0]['noreferensi'] ?? null,
                    'tipedetail' => $r2[0]['tipedetail'],
                    'nourut' => ((int)$r2[0]['nourut'] + 1),
                    'idjenis' => $r2[0]['idjenis'],
                    'kodedenda' => $params['kodedenda'],
                    'nilai' => $r2[0]['nilai'],
                    'updateby' => $r2[0]['updateby'],
                    'baris' => '',
                    'pokok' => ''
                );
                // $this->query_insert($dataArr, $this->db->dbname . ".kebun_mutubuah_mobile");
                $this->insert($dataArr, $this->db->dbname . ".kebun_mutubuah_mobile");
            }
        }
    }

    public function postingPanen($whr)
    {
        $arr = [
            'jurnal' => 1
        ];
        $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
        return $this->response;
    }

    public function unpostingSync($whr)
    {
        $arr = [
            'flag' => 0,
            'syn' => 0
        ];
        $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
        return $this->response;
    }
    public function unpostingPanen($whr)
    {
        $arr = [
            'jurnal' => 0
        ];
        $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
        return $this->response;
    }

    public function deleteEditDtl()
    {
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
            $this->response['message'] = "Failed! : Re-Syncronized - (" . $e->getMessage() . ") !!";
        }
    }

    function deleteAlldetailPanenKeepAktifitas($whr)
    {
        $aktifitas = $this->getAktifitas("WHERE " . $whr);
        if ($aktifitas) {
            $this->response['error'] = false;
            $this->exec($this->query_delete('kebun_prestasi_mobile', $whr));
            $this->exec($this->query_delete('kebun_gerdang_mobile', $whr));
            $this->exec($this->query_delete('kebun_mutubuah_mobile', $whr));
        } else {
            $this->response['error'] = true;
        }
        return $this->response;
    }

    function execdeleteAllDetailPanen($notransaksi)
    {
        $whr = "notransaksi='" . $notransaksi . "'";
        $this->deleteAlldetailPanenKeepAktifitas($whr);
    }
    public function getHeaders($user)
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
        // $whr .= $this->post('kodeorg') ? ' AND kodeorg="' . $this->post('kodeorg') . '"' : '';
        $whr .= $this->post('kodeorg') ? " AND kodeorg IN ('" . str_replace(',', "','", $this->post('kodeorg')) . "')" : '';

        if ($this->post('dateFrom') && $this->post('dateTo')) {
            $whr .= " AND tanggal BETWEEN '" . $this->post('dateFrom') . "' AND '" . $this->post('dateTo') . "'";
        }
        // $Qsql = 'WHERE kodeorg="' . $kodeorg . '" AND tipetransaksi="PNN" AND syn="1" AND flag="0"' . $whr . ' ORDER BY tanggal DESC ';
        $Qsql = 'WHERE tipetransaksi="PNN" AND syn="1" AND flag="0"' . $whr . ' ORDER BY tanggal DESC ';
        $getData = $this->getAktifitas($Qsql);
        $getData2 = $this->getAktifitas($Qsql);

        if ($this->uri->segments[5] == 'load') {
            return $Qsql;
        }

        $listMandor = [];
        foreach ($getData2 as $key => $value) {
            $listMandor[$value->tanggal . $value->nikmandor][] = $value->notransaksi;
        }

        $val = [];
        $nourut = 0;
        $dataKodeorg = [];
        if (count($getData) > 0) {
            foreach ($getData as $key => $value) {
                $nourut++;
                $value->is_duplikatmandor = count($listMandor[$value->tanggal . $value->nikmandor]) > 1 ? true : false;
                $val[$key] = $value;
                $dataKodeorg[$value->kodeorg][] = $value->kodeorg;
            }
        }

        // Menghitung jumlah data untuk setiap kodeorg
        $dataKodeorgCount = [];
        foreach ($dataKodeorg as $kodeorg => $items) {
            $dataKodeorgCount[$kodeorg] = count($items);
        }

        // Menyusun informasi yang akan dikembalikan
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
    public function getDetailDatas($whr)
    {
        $data = array();
        $q = "select a.notransaksi, a.nik, a.kodeorg, a.tph, a.tahuntanam, a.sesi, a.photo, a.photo2, a.hasilkerja, a.brondolan, a.jumlahhk from "
            . $this->db->dbname . ".kebun_prestasi_mobile a left join " . $this->db->dbname . ".kebun_mutubuah_mobile b 
        on a.notransaksi = b.notransaksi and a.tph = b.tph and a.sesi = b.sesi " . $whr . "
        group by a.notransaksi, a.nik, a.kodeorg, a.tahuntanam, a.tph, a.sesi, a.photo, a.photo2,a.hasilkerja, a.brondolan, a.jumlahhk";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    public function getDetails()
    {
        $notransaksi = $this->post('notransaksi') ?: '';
        if (!$notransaksi) {
            return $this->responseError("Parameter notransaksi Harus diisi", 400);
        }

        // $Qsql = 'WHERE notransaksi="' . $notransaksi . '" AND tipetransaksi="PNN" AND syn="1" AND flag="0"';
        $Qsql = 'WHERE notransaksi="' . $notransaksi . '" AND tipetransaksi="PNN" AND syn="1" ';
        $getHeader = $this->getAktifitas($Qsql);

        $dataHeader = '';
        $tgl = '';
        $divisi = '';
        $flag = 0;

        if (count($getHeader) > 0) {
            foreach ($getHeader as $key => $value) {
                $tgl = $value->tanggal;
                $divisi = $value->divisi;
                $dataHeader = $value;
                $flag = $value->flag;
            }
        } else {
            return $this->responseError("notransaksi {$notransaksi} tidak ditemukan", 404);
        }

        $v = [];
        $valLuasBlok = [];
        $jumlahTPH = [];
        $dataAbsensi = [];

        $getData = $this->getDetailDatas("where a.notransaksi = '$notransaksi' ");
        if (count($getData) > 0) {
            foreach ($getData as $key => $value) {
                // $jumlahTPH[$value['nik']][$value['kodeorg']][$value['tph']][$value['sesi']] = $value['tph'];
                $jumlahTPH[$value['nik']][$value['kodeorg']][$value['nik'] . $value['tph'] . $value['sesi']] = $value['nik'] . $value['tph'] . $value['sesi'];
            }

            $qluas =   "SELECT a.notransaksi,a.divisi,a.tanggalpanen,b.blok,b.luas_aktual,b.pemanen,b.luas_rencana 
                        FROM " . $this->db->dbname . ".kebun_luaspanen a
                        JOIN " . $this->db->dbname . ".kebun_luaspanen_dt b
                        ON a.notransaksi = b.notransaksi
                        WHERE tanggalpanen='" . $tgl . "' ";
                        // WHERE a.divisi='" . $divisi . "' AND tanggalpanen='" . $tgl . "' ";

            $resluas = $this->fetchdata($qluas);
            if (count($resluas) > 0) {
                foreach ($resluas as $key => $value) {
                    $valLuasBlok[$value['pemanen']][$value['blok']] = $value['luas_aktual'];
                }
            }
            

            $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile a 
            left join " . $this->db->dbname . ".sdm_absensi b on a.notransaksi = b.notransaksi  
            where a.noreferensi = '{$notransaksi}' AND tipetransaksi='ABS'";
            $abs = $this->fetchdata($q);
            $dataAbsensi = [];
            if (count($abs) > 0) {
                foreach ($abs as $key => $value) {
                    $dataAbsensi[$key] = $value;
                }
            }

            $mutu = $this->getMutubuah("WHERE notransaksi = '$notransaksi'");

            $dataMutu = [];
            if (count($mutu) > 0) {
                foreach ($mutu as $key => $value) {
                    $dataMutu[$value->nik][$value->tph][$value->sesi][$value->kodedenda] = $value;
                }
            }

            $nourut = 1;
            $countNik = [];
            $informasi = [];
            $luasblok = 0;

            foreach ($getData as $key => $value) {
                $countNik[$value['nik']][$value['kodeorg']][] = $value['nik'];
                $v[$key] = $value;
                $v[$key]['noreferensi'] = $value['notransaksi'];
                $v[$key]['nourut'] = $nourut;
                $dividedValue = floor(($valLuasBlok[$value['nik']][$value['kodeorg']] / count($jumlahTPH[$value['nik']][$value['kodeorg']])) * 100) / 100;

                $jumlahTPHCount = count($jumlahTPH[$value['nik']][$value['kodeorg']]);
                $valLuas = $valLuasBlok[$value['nik']][$value['kodeorg']];
                $nilLuas = (count($countNik[$value['nik']][$value['kodeorg']]) == $jumlahTPHCount)
                    ? $dividedValue + $valLuas - ($dividedValue * $jumlahTPHCount)
                    : $dividedValue;
                $valueluas = number_format($nilLuas, 2, '.', '');
                $v[$key]['luaspanen'] = $valueluas;
                $luasblok += $valueluas;

                $v[$key]['jjngbuahbesar'] = (int)@$dataMutu[$value['nik']][$value['tph']][$value['sesi']]['BMB']->nilai;
                $v[$key]['jjgbuahkecil'] = (int)@$dataMutu[$value['nik']][$value['tph']][$value['sesi']]['BMK']->nilai;

                $filteredData = array_filter($dataMutu[$value['nik']][$value['tph']][$value['sesi']], function ($dt) {
                    return ($dt->kodedenda != 'BMB' && $dt->kodedenda != 'BMK');
                });
                $v[$key]['mutubuah'] = $filteredData;
                $nourut++;
            }

            $informasi = array(
                'luaspanen' => number_format($luasblok, 2, '.', ''),
                'jumlahdata' => $nourut - 1,
            );
        }

        $msg = ($flag == 0) ? "Data Transaksi Belum di download" : "Data Transaksi Sudah di download"; 
        
        $this->response['error']    = !$v;
        $this->response['message']  = $v ? $msg : "Nomot transaksi {$notransaksi} Tidak ada";
        $this->response['header'] = $dataHeader ?: null;
        $this->response['kebun_prestasi_mobile'] = $v ?: null;
        $this->response['kehadiranumum'] = $v ? $dataAbsensi : null;
        $this->response['informasi'] = $v ? $informasi : null;
        $this->response['status']   = $v ? 200 : 404;
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

        $this->update(['flag' => $flag], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='$notransaksi' AND tipetransaksi='PNN'");
        return $this->responseSuccess("Data successfully updated !!");
    }

    public function rekapHancakPNN($user)
    {
        $tanggal = $this->post('tanggal') ?: '';
        $mandor  = $this->post('nikmandor') ?: '';
        $periode  = $this->post('periode') ?: '';
        // $blok = $this->post('blok') ? explode(",", $this->post('blok')) : [];
        // $kodeorg = $this->post('kodeorg') ?: $user['lokasitugas'];

        if (!$tanggal && !$mandor && !$periode) {
            return $this->responseError("Salah satu Parameter (tanggal, nikmandor, periode) harus diisi", 400);
        }

        // $periode = $periode ?: substr($tanggal, 0, 7);

        $whr = '';
        $whr .= $tanggal ? " AND tanggal='$tanggal'" : '';
        $whr .= $mandor ? " AND nikmandor='$mandor'" : '';
        $whr .= $periode ? " AND tanggal LIKE '%$periode%'" : '';
        // $whr .= $this->post('kodeorg') ? ' AND kodeorg="' . $this->post('kodeorg') . '"' : '';
        $whr .= $this->post('kodeorg') ? " AND kodeorg IN ('" . str_replace(',', "','", $this->post('kodeorg')) . "')" : '';


        // $kodeorg = $user['lokasitugas'];
        // $aktifitas = $this->getAktifitas("WHERE syn='1' AND kodeorg='$kodeorg' AND tanggal='$tanggal'");
        // $aktifitas = $this->getAktifitas("WHERE syn='1' AND kodeorg='$kodeorg' $whr ORDER BY tanggal DESC");
        $aktifitas = $this->getAktifitas("WHERE syn='1' $whr ORDER BY tanggal DESC");
        if (!$aktifitas || $aktifitas->rowCount() == 0) {
            return $this->responseError("Tidak ada Transaksi ", 404);
        }

        $datanotransaksi = [];
        $nikmandor = [];
        $tanggalList = [];
        foreach ($aktifitas as $value) {
            if ($value->tipetransaksi != 'BKM') {
                $datanotransaksi[$value->tipetransaksi][] = $value;
                $listTransaksi[$value->tipetransaksi][] = $value->notransaksi;
                $nikmandor[$value->notransaksi] = $value->nikmandor;
                $tanggalList[] = $value->tanggal;
            }
        }

        if ($this->uri->segments[5] == 'load') {
            return $listTransaksi;
        }

        // Menghilangkan duplikasi tanggal
        $tanggalList = array_unique($tanggalList);

        // Mengubah array tanggal menjadi string yang dipisahkan koma untuk klausa IN
        $tanggalInClause = "'" . implode("','", $tanggalList) . "'";

        $qluas = "SELECT a.notransaksi, b.blok, b.luas_aktual, b.pemanen, a.tanggalpanen 
                  FROM {$this->db->dbname}.kebun_luaspanen a
                  JOIN {$this->db->dbname}.kebun_luaspanen_dt b ON a.notransaksi = b.notransaksi
                  WHERE a.tanggalpanen IN ($tanggalInClause)";
        //   WHERE a.kodeorg='$kodeorg' AND tanggalpanen='$tanggal'";

        $valLuasBlok = [];
        foreach ($this->fetchdata($qluas) as $value) {
            $valLuasBlok[$value['tanggalpanen']][$value['pemanen']][$value['blok']] = $value['luas_aktual'];
        }

        $dataPenalti = [];
        foreach (['PNN', 'MHC'] as $type) {
            foreach ($datanotransaksi[$type] as $v) {
                $q = "SELECT nik, kodeorg, kodedenda, sum(nilai) as jumlah 
                      FROM {$this->db->dbname}.kebun_mutubuah_mobile WHERE notransaksi='$v->notransaksi'
                      GROUP BY nik, kodeorg, kodedenda";
                foreach ($this->query($q) as $value) {
                    if ($value->kodedenda) {
                        if (isset($dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda])) {
                            $dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda] += $value->jumlah;
                        } else {
                            $dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda] = $value->jumlah;
                        }
                    }
                }
            }
        }

        $dataDetail = [];
        $informasi = [];
        $luasblok = 0;
        foreach ($datanotransaksi['PNN'] as $v) {
            $q = "SELECT kodeorg, nik, sum(hasilkerja) as totaljjg 
                  FROM {$this->db->dbname}.kebun_prestasi_mobile WHERE notransaksi='$v->notransaksi'
                  GROUP BY nik, kodeorg";

            foreach ($this->query($q) as $value) {

                // if (empty($blok) || in_array($value->kodeorg, $blok)) {
                $penaltiMutuBuah = $dataPenalti[$value->nik][$value->kodeorg] ?? [];
                $filteredData = array_filter($penaltiMutuBuah, function ($kodedenda) {
                    return $kodedenda !== 'BMB' && $kodedenda !== 'BMK';
                }, ARRAY_FILTER_USE_KEY);

                // $luasblok += $valLuasBlok[$value->nik][$value->kodeorg] ?? 0;
                $luasblok += $valLuasBlok[$v->tanggal][$value->nik][$value->kodeorg] ?? 0;
                // $value->tanggal = $tanggal;
                $value->tanggal = $v->tanggal;
                $value->nikmandor = $nikmandor[$v->notransaksi];
                // $value->luaspanen = $valLuasBlok[$value->nik][$value->kodeorg] ?? 0;
                $value->luaspanen = $valLuasBlok[$v->tanggal][$value->nik][$value->kodeorg] ?? 0;

                $value->jjgbuahbesar = $penaltiMutuBuah['BMB'] ?? '0';
                $value->jjgbuahkecil = $penaltiMutuBuah['BMK'] ?? '0';
                $value->penalti = $filteredData;
                $dataDetail[] = $value;
                // }
            }
        }

        $informasi = array(
            'luaspanen' => number_format($luasblok, 2, '.', '')
        );

        return [
            'error' => !$dataDetail,
            'message' => $dataDetail ? "Data Detail" : "Data Tidak ada",
            'data' => $dataDetail ?: null,
            'transaksi' => $listTransaksi ?: null,
            'informasi' => $informasi ?: null,
            'status' => $dataDetail ? 200 : 404
        ];
    }


    public function rekaphancakpanenheader($user)
    {
        $periode    = $this->post('periode') ?: '';
        $mandor     = $this->post('nikmandor') ?: '';
        // $kodeorg = $this->post('kodeorg') ?: $user['lokasitugas'];
        $tanggal = $this->post('tanggal') ?: '';

        $blok = $this->post('blok') ? explode(",", $this->post('blok')) : [];

        if (!$mandor && !$periode) {
            return $this->responseError("Parameter (nikmandor & periode) harus diisi", 400);
        }


        $whr = '';
        $whr .= $tanggal ? " AND tanggal='$tanggal'" : '';
        $whr .= $mandor ? " AND nikmandor='$mandor'" : '';
        $whr .= $periode ? " AND tanggal LIKE '%$periode%'" : '';
        // $whr .= $this->post('kodeorg') ? ' AND kodeorg="' . $this->post('kodeorg') . '"' : '';
        $whr .= $this->post('kodeorg') ? " AND kodeorg IN ('" . str_replace(',', "','", $this->post('kodeorg')) . "')" : '';

        // $aktifitas = $this->getAktifitas("WHERE syn='1' AND kodeorg='$kodeorg' AND tanggal='$tanggal'");
        // $aktifitas = $this->getAktifitas("WHERE syn='1' AND kodeorg='$kodeorg' $whr ORDER BY tanggal DESC");
        $aktifitas = $this->getAktifitas("WHERE syn='1' $whr ORDER BY tanggal DESC");
        if (!$aktifitas || $aktifitas->rowCount() == 0) {
            return $this->responseError("Tidak ada Transaksi ", 404);
        }

        $datanotransaksi = [];
        $nikmandor = [];
        $tanggalList = [];
        foreach ($aktifitas as $value) {
            if ($value->tipetransaksi != 'BKM') {
                $datanotransaksi[$value->tipetransaksi][] = $value;
                $nikmandor[$value->notransaksi] = $value->nikmandor;
                $listTransaksi[$value->tipetransaksi][] = $value->notransaksi;
                $tanggalList[] = $value->tanggal;
            }
        }

        if ($this->uri->segments[5] == 'load') {
            return $listTransaksi;
        }

        $tanggalList = array_unique($tanggalList);

        // Mengubah array tanggal menjadi string yang dipisahkan koma untuk klausa IN
        $tanggalInClause = "'" . implode("','", $tanggalList) . "'";

        $qluas = "SELECT a.notransaksi, b.blok, b.luas_aktual, b.pemanen 
                  FROM {$this->db->dbname}.kebun_luaspanen a
                  JOIN {$this->db->dbname}.kebun_luaspanen_dt b ON a.notransaksi = b.notransaksi
                  WHERE a.tanggalpanen IN ($tanggalInClause)";
        //   WHERE a.kodeorg='$kodeorg' AND tanggalpanen='$tanggal'";

        $valLuasBlok = [];
        foreach ($this->fetchdata($qluas) as $value) {
            $valLuasBlok[$value['pemanen']][$value['blok']] = $value['luas_aktual'];
        }

        // $dataPenalti = [];
        // foreach (['PNN', 'MHC'] as $type) {
        //     foreach ($datanotransaksi[$type] as $v) {
        //         $q = "SELECT nik, kodeorg, kodedenda, sum(nilai) as jumlah 
        //               FROM {$this->db->dbname}.kebun_mutubuah_mobile WHERE notransaksi='$v->notransaksi'
        //               GROUP BY nik, kodeorg, kodedenda";
        //         foreach ($this->query($q) as $value) {
        //             if ($value->kodedenda) {
        //                 if (isset($dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda])) {
        //                     $dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda] += $value->jumlah;
        //                 } else {
        //                     $dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda] = $value->jumlah;
        //                 }
        //             }
        //         }
        //     }
        // }

        $dataDetail = [];
        foreach ($datanotransaksi['PNN'] as $v) {
            $q = "SELECT kodeorg 
                  FROM {$this->db->dbname}.kebun_prestasi_mobile WHERE notransaksi='$v->notransaksi'
                  GROUP BY kodeorg";

            foreach ($this->query($q) as $value) {
                if (empty($blok) || in_array($value->kodeorg, $blok)) {
                    $penaltiMutuBuah = $dataPenalti[$value->nik][$value->kodeorg] ?? [];
                    $filteredData = array_filter($penaltiMutuBuah, function ($kodedenda) {
                        return $kodedenda !== 'BMB' && $kodedenda !== 'BMK';
                    }, ARRAY_FILTER_USE_KEY);

                    // $value->tanggal = $tanggal;
                    $value->tanggal = $v->tanggal;
                    $value->nikmandor = $nikmandor[$v->notransaksi];
                    // $value->luaspanen = $valLuasBlok[$value->nik][$value->kodeorg] ?? 0;
                    // $value->jjgbuahbesar = $penaltiMutuBuah['BMB'] ?? '0';
                    // $value->jjgbuahkecil = $penaltiMutuBuah['BMK'] ?? '0';
                    // $value->penalti = $filteredData;
                    $dataDetail[] = $value;
                }
            }
        }

        return [
            'error' => !$dataDetail,
            'message' => $dataDetail ? "Data Header" : "Data Tidak ada",
            'data' => $dataDetail ?: null,
            'transaksi' => $listTransaksi ?: null,
            'status' => $dataDetail ? 200 : 404
        ];
    }

    public function rekaphancakpanendetail($user)
    {
        $tanggal = $this->post('tanggal') ?: '';
        $mandor  = $this->post('nikmandor') ?: '';
        $periode  = $this->post('periode') ?: '';
        $blok = $this->post('blok') ? explode(",", $this->post('blok')) : [];
        $nik = $this->post('nik') ? explode(",", $this->post('nik')) : [];
        // $kodeorg = $this->post('kodeorg') ?: $user['lokasitugas'];

        // if (!$tanggal && !$mandor && !$periode) {
        //     return $this->responseError("Salah satu Parameter (tanggal, nikmandor, periode) harus diisi", 400);
        // }

        // $periode = $periode ?: substr($tanggal, 0, 7);

        $whr = '';
        $whr .= $tanggal ? " AND tanggal='$tanggal'" : '';
        $whr .= $mandor ? " AND nikmandor='$mandor'" : '';
        $whr .= $periode ? " AND tanggal LIKE '%$periode%'" : '';
        // $whr .= $this->post('kodeorg') ? ' AND kodeorg="' . $this->post('kodeorg') . '"' : '';    
        $whr .= $this->post('kodeorg') ? " AND kodeorg IN ('" . str_replace(',', "','", $this->post('kodeorg')) . "')" : '';

        // $kodeorg = $user['lokasitugas'];
        // $aktifitas = $this->getAktifitas("WHERE syn='1' AND kodeorg='$kodeorg' AND tanggal='$tanggal'");
        // $aktifitas = $this->getAktifitas("WHERE syn='1' AND kodeorg='$kodeorg' $whr ORDER BY tanggal DESC");
        $aktifitas = $this->getAktifitas("WHERE syn='1' $whr ORDER BY tanggal DESC");
        if (!$aktifitas || $aktifitas->rowCount() == 0) {
            return $this->responseError("Tidak ada Transaksi", 404);
        }

        $datanotransaksi = [];
        $nikmandor = [];
        $tanggalList = [];
        $dataTrx = [];
        foreach ($aktifitas as $value) {
            if ($value->tipetransaksi != 'BKM') {
                $datanotransaksi[$value->tipetransaksi][] = $value;
                $listTransaksi[$value->tipetransaksi][] = $value->notransaksi;
                $nikmandor[$value->notransaksi] = $value->nikmandor;
                $tanggalList[] = $value->tanggal;
                $dataTrx[$value->kodeorg][$value->tipetransaksi][] = $value->notransaksi;
            }
        }

        if ($this->uri->segments[5] == 'load') {
            return $listTransaksi;
        }

        $dataKodeorgCount = [];
        $totalTransaksi = 0;
        foreach ($dataTrx as $kodeorg => $type) {
            $totalCount = 0;
            foreach ($type as $items => $item) {
                $totalCount += count($item);
                $dataKodeorgCount[$kodeorg][$items] = count($item);
            }
            $dataKodeorgCount[$kodeorg]['total'] = $totalCount;
            $totalTransaksi += $totalCount;
        }

        $tanggalList = array_unique($tanggalList);

        // Mengubah array tanggal menjadi string yang dipisahkan koma untuk klausa IN
        $tanggalInClause = "'" . implode("','", $tanggalList) . "'";

        $qluas = "SELECT a.notransaksi, b.blok, b.luas_aktual, b.pemanen, a.tanggalpanen 
                  FROM {$this->db->dbname}.kebun_luaspanen a
                  JOIN {$this->db->dbname}.kebun_luaspanen_dt b ON a.notransaksi = b.notransaksi
                  WHERE a.tanggalpanen IN ($tanggalInClause)";
        //   WHERE a.kodeorg='$kodeorg' AND tanggalpanen='$tanggal'";

        $valLuasBlok = [];
        foreach ($this->fetchdata($qluas) as $value) {
            // $valLuasBlok[$value['pemanen']][$value['blok']] = $value['luas_aktual'];
            $valLuasBlok[$value['tanggalpanen']][$value['pemanen']][$value['blok']] = $value['luas_aktual'];
        }

        $dataPenalti = [];
        foreach (['PNN', 'MHC'] as $type) {
            foreach ($datanotransaksi[$type] as $v) {
                $q = "SELECT nik, kodeorg, kodedenda, sum(nilai) as jumlah 
                      FROM {$this->db->dbname}.kebun_mutubuah_mobile WHERE notransaksi='$v->notransaksi'
                      GROUP BY nik, kodeorg, kodedenda";
                foreach ($this->query($q) as $value) {
                    if ($value->kodedenda) {
                        if (isset($dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda])) {
                            $dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda] += $value->jumlah;
                        } else {
                            $dataPenalti[$value->nik][$value->kodeorg][$value->kodedenda] = $value->jumlah;
                        }
                    }
                }
            }
        }

        $dataPhoto = [];
        foreach (['PNN', 'MHC'] as $type) {
            foreach ($datanotransaksi[$type] as $v) {
                $q = "SELECT nik, kodeorg, kodedenda, sum(nilai) as jumlah, photo
                      FROM {$this->db->dbname}.kebun_mutubuah_mobile WHERE notransaksi='$v->notransaksi'
                      GROUP BY nik, kodeorg, kodedenda,photo";
                //   echo $q;
                foreach ($this->query($q) as $value) {
                    if ($value->kodedenda) {
                        if (isset($dataPhoto[$value->nik][$value->kodeorg][$value->kodedenda])) {
                            $dataPhoto[$value->nik][$value->kodeorg][$value->kodedenda] += $value->photo;
                        } else {
                            $dataPhoto[$value->nik][$value->kodeorg][$value->kodedenda] = $value->photo;
                        }
                    }
                }
            }
        }



        $dataDetail = [];
        $informasi = [];
        $luasblok = 0;
        $listData = [];

        foreach (['PNN', 'MHC'] as $type) {
            foreach ($datanotransaksi[$type] as $v) {
                $queries = [
                    "SELECT kodeorg, nik FROM {$this->db->dbname}.kebun_prestasi_mobile WHERE notransaksi='$v->notransaksi' GROUP BY nik, kodeorg",
                    "SELECT nik, kodeorg FROM {$this->db->dbname}.kebun_mutubuah_mobile WHERE notransaksi='$v->notransaksi' GROUP BY nik, kodeorg"
                ];

                foreach ($queries as $query) {
                    foreach ($this->query($query) as $value) {
                        $penaltiMutuBuah = $dataPenalti[$value->nik][$value->kodeorg] ?? [];
                        $filteredData = array_filter($penaltiMutuBuah, function ($kodedenda) {
                            return $kodedenda !== 'BMB' && $kodedenda !== 'BMK';
                        }, ARRAY_FILTER_USE_KEY);

                        $pinaltiPhoto = $dataPhoto[$value->nik][$value->kodeorg] ?? [];
                        $filteredPhoto = array_filter($pinaltiPhoto, function ($kodedenda) {
                            return $kodedenda !== 'BMB' && $kodedenda !== 'BMK';
                        }, ARRAY_FILTER_USE_KEY);


                        $luasblok += $valLuasBlok[$v->tanggal][$value->nik][$value->kodeorg] ?? 0;

                        $value->tanggal = $v->tanggal;
                        $value->nikmandor = $nikmandor[$v->notransaksi];
                        $value->luaspanen = $valLuasBlok[$v->tanggal][$value->nik][$value->kodeorg] ?? 0;
                        $value->jjgbuahbesar = $penaltiMutuBuah['BMB'] ?? '0';
                        $value->jjgbuahkecil = $penaltiMutuBuah['BMK'] ?? '0';
                        $value->penalti = $filteredData;
                        $value->photo = $filteredPhoto;

                        $uniqueKey = $value->kodeorg . $value->nik;
                        $listData[$uniqueKey] = $value;
                    }
                }
            }
        }

        $jmlBlok = 0;
        foreach ($listData as $value) {
            if (empty($blok) || in_array($value->kodeorg, $blok)) {
                $jmlBlok++;
                $dataDetail[] = $value;
            }
        }

        $informasi = [
            'luaspanen' => number_format($luasblok, 2, '.', ''),
            'jumlahblok' => $jmlBlok,
            'transaski' => $dataKodeorgCount,
            'allTransactions' => $totalTransaksi
        ];

        return [
            'error' => !$dataDetail,
            'message' => $dataDetail ? "Data Details" : "Data Tidak ada",
            'data' => $dataDetail ?: null,
            'transaksi' => $listTransaksi ?: null,
            'informasi' => $informasi ?: null,
            'status' => $dataDetail ? 200 : 404
        ];
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
