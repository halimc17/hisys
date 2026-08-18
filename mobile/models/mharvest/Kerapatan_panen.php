<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Kerapatan_panen extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_2taksasi_akp", "kebun_2taksasi_akp_dt");
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
        $data['tanggal']            = $this->post('tanggal_akp');
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
        // console
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
                return $this->responseError("Warning : Data Transaksi sudah terposting.", 403);
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
        // $data['no_syncronized']     = $this->post('no_syncronized');
        // DATA ARRAY ===
        $data['blok']               = explode(",", $this->post('blok'));
        $data['baris']                = explode(",", $this->post('baris'));
        $data['pokok']            = explode(",", $this->post('pokok'));
        $data['idjenis']         = explode(",", $this->post('idjenis'));
        $data['kode']          = explode(",", $this->post('kode'));
        $data['nilai']          = explode(",", $this->post('nilai'));
        $data['createtime']         = explode(",", $this->post('lastupdate'));

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('blok')) != "") {
            // $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();
            for ($i = 0; $i < count($data['blok']); $i++) {
                $dataArr = array(
                    'notransaksi'   => $this->post('notransaksi'),
                    'nourut'        => $data['baris'][$i] . "-" . $data['pokok'][$i],
                    'blok'           => $data['blok'][$i],
                    'idjenis'       => ($data['idjenis'][$i] != null || $data['idjenis'][$i] != "") ? (int)$data['idjenis'][$i] : 35,
                    'kode'           => ($data['kode'][$i] != null || $data['kode'][$i] != "") ? $data['kode'][$i] : "35",
                    'nilai'          => (int)$data['nilai'][$i] ?? 0,
                    'tanggal'         => $data['tanggal'],
                    'createby'         => $user['userid'],
                    'createtime'     => $data['createtime'][$i],
                    'updateby'    => $user['userid'],
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_2taksasi_akp_dt");
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

    public function addAKP($user)
    {
        $data['notransaksi']        = $this->post('notransaksi');
        $data['nosyn']            = $this->post('no_syncronized');
        $data['tanggal']            = $this->post('tanggal');
        $data['blok']               = explode(",", $this->post('blok'));
        $data['akp']                = explode(",", $this->post('akp'));

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '" . $data['notransaksi'] . "' and nosync = '" . $this->post('no_syncronized') . "' LIMIT 1");
        if ($aktifitas and $aktifitas->rowCount() > 0 and trim($this->post('blok')) != "") {
            // $maxNum = $this->getPrestasi("WHERE notransaksi = '" . $data['notransaksi'] . "' and kodekegiatan = '" . $type . "'")->rowCount();
            for ($i = 0; $i < count($data['blok']); $i++) {
                $dataArr = array(
                    'notransaksi'     => $this->post('notransaksi'),
                    'nosyn'           => $data['nosyn'],
                    'blok'            => $data['blok'][$i],
                    'akp'             => $data['akp'][$i],
                    'tanggal'         => $data['tanggal'],
                    'createby'        => $user['userid'],
                );
                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_2taksasi_akp");
            }
            // console
            if ($this->uri->segments[5] == 'load') {
                return $dataInsert;
            }
            $qexec = $this->exec($dataInsert);
            if ($qexec) {
                $this->response['error'] = false;
                $this->response['message'] = "Success Insert Akp";
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
        $jmldetail  = $this->post('jumlah_baris_pokok_detail');
        $jmlakp     = $this->post('jumlah_akp');
        $str        = "select notransaksi from " . $this->db->dbname . ".kebun_2taksasi_akp_dt where notransaksi = '" . $this->post('notransaksi') . "' ";
        $str2       = "select notransaksi from " . $this->db->dbname . ".kebun_2taksasi_akp where notransaksi = '" . $this->post('notransaksi') . "' ";
        // console
        if ($this->uri->segments[5] == 'load') {
            return $str;
        }
        $dtlcheck = $this->query($str);
        $akpcheck = $this->query($str2);
        if ($dtlcheck->rowCount() == (int)$jmldetail && $akpcheck->rowCount() == (int)$jmlakp) {
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
            $this->response['message'] = "Failed! : Data Syncd (" . "detail: " . $dtlcheck->rowCount() . "/" . $jmldetail . " and akp: " . $akpcheck->rowCount() . "/" . $jmlakp . ") Belum Lengkap, Mohon Sync Ulang";
        }
        return $this->response;
    }

    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "' AND tipetransaksi = 'AKP' order by tanggal desc " . $limitPage;
        // $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where tipetransaksi = 'AKP' order by tanggal desc " . $limitPage;
        //
        // echo $q;
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

    function getDataDtl($where)
    {
        $header = array();
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_2taksasi_akp " . $where
            . "order by nourut, idjenis, nilai desc";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // GET DATA BY BLOK NIK & ARAH
            for ($j = 0; $j < count($r); $j++) {
                if ($j == 0) {
                    $hd = array(
                        'txn' => $r[$j]['notransaksi'],
                        'blok' => $r[$j]['blok'],
                        'tgl' => $r[$j]['tanggal'],
                        'akp' => $r[$j]['akp'],
                    );
                    array_push($header, $hd);
                } else {
                    $cp = array(
                        'txn' => $r[$j]['notransaksi'],
                        'blok' => $r[$j]['blok'],
                        'tgl' => $r[$j]['tanggal'],
                        'akp' => $r[$j]['akp'],
                    );
                    if (!in_array($cp, $header)) {
                        array_push($header, $cp);
                    }
                }
            }

            // GET DATA DETAIL BY HEADER
            foreach ($header as $k) {
                $detail = array();
                $temp = array();
                $nourut = array();
                for ($i = 0; $i < count($r); $i++) {
                    if ($k['blok'] == $r[$i]['blok'] && $k['tgl'] == $r[$i]['tanggal']) {
                        $var = array(
                            'urut' => $r[$i]['nourut'],
                            'idjenis' => $r[$i]['idjenis'],
                            'nilai' => $r[$i]['nilai'],
                        );
                        array_push($temp, $var);
                    }
                }

                //! RE ARRANGE DATA DETAIL
                for ($i = 0; $i < count($temp); $i++) {
                    if ($i == 0) {
                        array_push($nourut, $temp[$i]['urut']);
                    } else {
                        if (!in_array($temp[$i]['urut'], $nourut)) {
                            array_push($nourut, $temp[$i]['urut']);
                        }
                    }
                }

                foreach ($nourut as $z) {
                    $temp2 = array();
                    for ($q = 0; $q < count($temp); $q++) {
                        if ($z == $temp[$q]['urut']) {
                            $var2 = array(
                                'jenis' => $temp[$q]['idjenis'],
                                'nilai' => $temp[$q]['nilai'],
                            );
                            array_push($temp2, $var2);
                        }
                    }
                    array_push($detail, array('urut' => $z, 'data' => $temp2));
                }

                $raw = array(
                    'header' => $k,
                    'detail' => $detail,
                );
                array_push($data, $raw);
            }
        }
        return $data;
    }

    function getMutuQty($whr)
    {
        $data = '';
        $q = "SELECT nilai FROM " . $this->db->dbname . ".kebun_2taksasi_akp " . $whr;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r[0]['nilai'];
        }
        return $data;
    }

    public function kodeMutu()
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5jenismutu where jenis = 'Sensus Produksi'";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
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
