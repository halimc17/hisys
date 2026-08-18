<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mluaspanen extends OWL_Model
{
    // private $prepareDB;
    function __construct()
    {
        parent::__construct();
        $d['table'] = array("kebun_luaspanen", "kebun_luaspanen_dt");
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

    public function addHeader($user)
    {
        $data = [
            'notransaksi' => $this->post('notransaksi'),
			'kodeorg' => !empty($this->post('divisi')) ? substr($this->post('divisi'), 0, 4) : $user['lokasitugas'],
			'divisi' =>  $this->post('divisi') ?? $user['subbagian'],
			'tanggalpanen' => $this->post('tanggal_panen'),
			'tanggal' => $this->post('tanggal'),
			'mandor' => $this->post('mandor'),
			'syn' => 0,
			'createby' => $user['userid'],
			'createtime' => $this->post('lastupdate'),
			'updateby' => $user['userid'],
			'updatetime' => date("Y-m-d H:i:s"),
			'posting' => 0,
			'postingby' => '0000000000',
			'postingtime' => '0000-00-00',
		];
        
        if ($this->uri->segments[5] == 'load') return $data;
        $dataHeader = $this->getHeader("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");

		if ($dataHeader and $dataHeader->rowCount() == 0) {
			$qexec = $this->insert($data, $this->db->dbname . ".kebun_luaspanen");
			if ($qexec) {
                $dt = $this->getHeader("WHERE notransaksi = '{$data['notransaksi']}' LIMIT 1")->fetch();
				return $this->responseSuccess("Success Insert Header", [
					'notransaksi' => $dt->notransaksi,
					'no_syncronized' => $dt->nosync,
					'tanggal' => $dt->tanggal
				]);
			}
		} else {
            $dt = $dataHeader->fetch();
			if ($dt->syn == "1") {
				return $this->responseError("Warning : Data sudah tersyncronize.", 403);
			}elseif ($dt->flag == "1") {
                return $this->responseError("Warning : Data Luas Panen sudah terposting.", 403);
			} else {
				$this->reSyntransMobile($this->post('notransaksi'));
				return $this->responseSuccess("Success Re-Syncronized", [
					'notransaksi' => $dt->notransaksi,
					'no_syncronized' => $dt->nosync,
					'tanggal' => $dt->tanggal
				]);
			}
		}
        return $this->responseError("Failed! Insert Data Header Luas Panen : No Transaksi '" . $dataHeader . "' :(" . $this->response['message'] . ")", 403);
    }

    public function addDetail()
    {
        try {
            $data['notransaksi']    = $this->post('notransaksi');
            $data['method']         = $this->post('method');
            $data['blok']           = explode(",", $this->post('blok'));
            $data['luas_rencana']   = explode(",", $this->post('luas_rencana'));
            $data['pemanen']        = explode(",", $this->post('pemanen'));
            $data['luas_aktual']    = explode(",", $this->post('luas_aktual'));

            $dataHeader = $this->getHeader("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
            // if ($dataHeader and count($dataHeader) > 0 and trim($this->post('blok')) != "") {
            if ($dataHeader and count($dataHeader) != 0) {
                // $maxNum = $this->getDetail("WHERE notransaksi = '" . $data['notransaksi'] . "' ")->rowCount();

                for ($i = 0; $i < count($data['blok']); $i++) {
                    // $maxNum++;
                    $dataDetail = array(
                        "notransaksi"   => $data['notransaksi'],
                        "blok"          => $data['blok'][$i],
                        "luas_rencana"  => $data['luas_rencana'][$i],
                        "pemanen"       => $data['pemanen'][$i],
                        "luas_aktual"   => number_format($data['luas_aktual'][$i], 2)
                    );

                    $dataInsert[$i] = $this->query_insert($dataDetail, $this->db->dbname . ".kebun_luaspanen_dt");
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
                $this->execdeleteAllDetailMluas($this->post('notransaksi'));
            }
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! Insert Data : No Transaksi '" . $data['notransaksi'] . "' :(" . $e->getMessage() . ")";
            $this->execdeleteAllDetailMluas($this->post('notransaksi'));
        }
        return $this->response;
    }

    //? Check total rows are equals
    public function checkdatarow()
    {
        try {
            $data['notransaksi']    = $this->post('notransaksi');
            $jmldetail              = $this->post('jumlah_detail');

            $dataHeader = $this->getHeader("WHERE notransaksi = '" . $data['notransaksi'] . "' LIMIT 1");
            if ($dataHeader and count($dataHeader) > 0) {
                $str = "select  notransaksi from " . $this->db->dbname . ".kebun_luaspanen_dt where notransaksi = '" . $data['notransaksi'] . "' ";
                // console
                if ($this->uri->segments[5] == 'load') {
                    return $str;
                }
                $datacheck = $this->query($str);
                if ($datacheck->rowCount() == $jmldetail) {
                    $dataUpdate = array(
                        "syn" => "1"
                    );
                    $qexec = $this->update($dataUpdate, $this->db->dbname . ".kebun_luaspanen", "notransaksi='" . $data['notransaksi'] . "'");
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

    function deleteAlldetailMluasKeepAktifitas($from, $whr, $del)
    {
        $aktifitas = $this->checkAktifitas($from, $whr);
        if ($aktifitas) {
            $this->response['error'] = false;
            $this->exec($this->query_delete('kebun_luaspanen_dt', $del));
        } else {
            $this->response['error'] = true;
        }
        return $this->response;
    }

    function execdeleteAllDetailMluas($notransaksi)
    {
        $from = "(select * from " . $this->db->dbname . ".kebun_luaspanen where posting = '0') a ";
        $whr = "where a.syn = '0' and a.notransaksi = '" . $notransaksi . "'";
        $del = "notransaksi='" . $notransaksi . "'";
        $this->deleteAlldetailMluasKeepAktifitas($from, $whr, $del);
    }

    function checkAktifitas($from, $whr)
    {
        $data = array();
        $q = "SELECT * FROM " . $from . $whr;
        $data = $this->query($q);
        return $data;
    }

    //? Get Aktifitas
    public function getHeader($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_luaspanen {$where}";
        $data = $this->query($q);
        return $data;
    }

    //? Get Prestasi
    private function getDetail($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_luaspanen_dt {$where}";
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
            $this->update($dataUpdate, $this->db->dbname . ".kebun_luaspanen", "notransaksi='" . $notransaksi . "' ");
            $this->delete($this->db->dbname . ".kebun_luaspanen_dt", "notransaksi='" . $notransaksi . "'");
        } catch (PDOException $e) {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Resyncronized (" . $e->getMessage() . ") !!";
        }
    }

    public function haPanenDtlView($whr)
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_luaspanen_dt " . $whr;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
            for ($i = 0; $i < count($data); $i++) {
                //! Ambil Nama
                // $arrPnn = array();
                $datakaryawan = $this->model('Setup_datakaryawan');
                $pnnNM = $datakaryawan->selectPemanenNm("where karyawanid = '{$data[$i]['pemanen']}'");
                array_push($data[$i], $data[$i]["nama"] = $pnnNM);
            }
        }
        return $data;
    }

    public function erpheader($user, $type)
    {
        $periode    = $this->post('periode') ?: '';
        $mandor     = $this->post('nikmandor') ?: '';
        $tanggal    = $this->post('tanggal') ?: '';
        $loc        = $this->post('kodeorg');
        // $blok = $this->post('blok') ? explode(",", $this->post('blok')) : [];

        if ($type == 'header') {
            if (!$periode) {
                return $this->responseError("Parameter (periode) harus diisi", 400);
            }
        }


        $whr = "a.syn = '1'";
        // if (!is_null($loc)) {
        //     $whr .= " AND a.kodeorg = '$loc'";
        // } else {
        //     $whr .= " AND a.kodeorg = '{$user['lokasitugas']}'";
        // }
        $whr .= $this->post('kodeorg') ? " AND kodeorg IN ('" . str_replace(',', "','", $this->post('kodeorg')) . "')" : '';
        $whr .= $this->post('divisi') ? " AND divisi IN ('" . str_replace(',', "','", $this->post('divisi')) . "')" : '';
        if (!is_null($tanggal) && $tanggal !== "" && (is_null($periode) || $periode == "")) {
            $whr .= " AND a.tanggalpanen='$tanggal'";
        }
        if (!is_null($periode) && $periode !== "" && (is_null($tanggal) || $tanggal == "")) {
            $whr .= " AND a.tanggalpanen LIKE '%$periode%'";
        }
        if (!is_null($tanggal) && $tanggal !== "" && !is_null($periode) && $periode !== "") {
            $whr .= " AND a.tanggalpanen='$tanggal'";
        }
        $whr .= $mandor ? " AND a.mandor='$mandor'" : '';
        $whr .= $this->post('blok') ? " AND b.blok IN ('" . str_replace(',', "','", $this->post('blok')) . "')" : '';
        $dataHeader = [];

        // if ($this->uri->segments[5] == 'load') {
        // return $listTransaksi;
        // }
        $msg = "";
        if ($type == "header") {
            $q =
                "
            select a.notransaksi, a.mandor, a.tanggalpanen as tanggal, b.blok 
            from " . $this->db->dbname . ".kebun_luaspanen a 
            left join " . $this->db->dbname . ".kebun_luaspanen_dt b on a.notransaksi = b.notransaksi
            where " . $whr . "
            group by a.notransaksi, b.blok
            order by a.tanggal desc, a.mandor asc, b.blok asc
        ";
            $msg = "Data Header";
        } else {
            $q =
                "
            select a.notransaksi, a.mandor, b.pemanen, a.tanggalpanen as tanggal, b.blok, round(sum(b.luas_rencana),2) as luasrencana, 
            round(sum(b.luas_aktual),2) as luasaktual 
            from " . $this->db->dbname . ".kebun_luaspanen a 
            left join " . $this->db->dbname . ".kebun_luaspanen_dt b on a.notransaksi = b.notransaksi
            where " . $whr . "
            group by a.notransaksi, b.blok, b.pemanen
        ";
            $msg = "Data Detail";
        }


        foreach ($this->query($q) as $value) {
            $dataHeader[] = $value;
        }

        return [
            'error' => !$dataHeader,
            'message' => $dataHeader  ? $msg : "Data Tidak ada",
            'data' => $dataHeader ?: null,
            // 'transaksi' => $listTransaksi ?: null,
            'status' => $dataHeader ? 200 : 404
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
