<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mspb extends OWL_Model
{
    protected $SPB_InHirarky = array();
    protected $SPB_AllHirarky = array();
    protected $SPB_InHirarky_parent = array();
    protected $SPB_has_tpb = array();
    protected $SPB_has_missing = array();
    protected $SPB_level_double = array();
    function __construct()
    {
        $d['table'] = array("kebun_spbht_mobile", "kebun_spbdt_mobile", "kebun_spbtkbm_mobile");
        $d['key'] = array("nospb", "qr_temp");
        $this->prepareDB = $d;
    }

    function init()
    {
        foreach ($this->prepareDB['table'] as $tbl) {
            if (!$this->table_exists($tbl)) {
                return $this->responseError("Tabel $tbl belum tersedia!", 400);
            }
        }
        return false;
    }

    public function addHeader($user)
    {
        $path = 'm_fileDocuments/SPB/images/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if (!is_writable($path)) {
            return $this->responseError("Tidak memiliki izin untuk membuat atau mengunggah foto ke folder ini", 500);
        }

        $tujuan = array_column($this->model('Mtujuanspb')->getTujuanSPB(), 'id', 'nama');
        $newFileName = $this->post('nospb') . ".jpg";
        $file = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/\w+;base64,#i', '', $this->post('foto_kendaraan'))));
        $filename = "$path$newFileName";
        
        $kodecustomer = $this->model('Setup_customer')->getCustomer("kodecustomer","WHERE namacustomer = '{$this->post('penerimatbs')}' LIMIT 1") ?? [];
        $penerimatbs = count($kodecustomer) > 0 ? $kodecustomer[0]['kodecustomer'] : $this->post('penerimatbs');

        $dataSPB = [
            'nospb' => $this->post('nospb'),
            'tanggal' => $this->post('tanggal'),
            'kodeorg' => substr($this->post('divisi'), 0, 4),
            'divisi' => $this->post('divisi'),
            'tujuan' => $tujuan[$this->post('tujuan')] ?: $this->post('tujuan'),
            'kraniproduksi' => $this->post('keraniproduksi'),
            'penerimatbs' => $penerimatbs,
            'syn' => '0',
            'createby' => $user['userid'],
            'updateby' => $user['userid'],
            'createtime' => $this->post('lastupdate'),
            'deviceid' => $user['uuid'],
            'kerani' => $this->post('driver') ?: $this->post('nama_driver'),
            'nopol' => $this->post('nopol')  ?:  $this->post('kendaraan'),
            'ffbdocument' => $this->base_url('SPB/images/', 'm_fileDocuments') . $newFileName,
            'latitude' => $this->post('latitude'),
            'longitude' => $this->post('longitude')
        ];

        if ($this->uri->segments[5] == 'load') return $dataSPB;

        $aktifitas = $this->getHeaderSPB("WHERE nospb = '{$dataSPB['nospb']}' LIMIT 1");

        if ($aktifitas && $aktifitas->rowCount() == 0) {
            if ($this->insert($dataSPB, $this->db->dbname . ".kebun_spbht_mobile", false)) {
                file_put_contents($filename, $file);
                $dt = $this->getHeaderSPB("WHERE nospb = '{$dataSPB['nospb']}' LIMIT 1")->fetch();
                return $this->responseSuccess('Success Insert Header', [
                    'notransaksi' => $dt->nospb,
                    'no_syncronized' => $dt->nosync,
                    'tanggal' => $dt->tanggal,
                ]);
            } else {
                $this->reSyntransMobile($this->post('nospb'));
                return $this->responseError($this->response['message'], 500);
            }
        } else {
            $dt = $aktifitas->fetch();
            if ($dt->syn == "1") {
				return $this->responseError("Warning : Data sudah tersyncronize.", 403);
			}elseif ($dt->flag == "1") {
                return $this->responseError("Warning : Data sudah terposting.", 403);
			} else {
				$this->reSyntransMobile($this->post('nospb'));
				return $this->responseSuccess('Success re-synchronized!', [
					'notransaksi' => $dt->nospb,
					'no_syncronized' => $dt->nosync,
					'tanggal' => $dt->tanggal,
				]);
			}

            // $this->reSyntransMobile($this->post('nospb'));
            // $dt = $aktifitas->fetch();
            // return $this->responseSuccess('Success re-synchronized!', [
            //     'notransaksi' => $dt->nospb,
            //     'no_syncronized' => $dt->nosync,
            //     'tanggal' => $dt->tanggal,
            // ]);
        }
    }

    public function addDetail($user)
    {
        $data = [
            'nospb' => $this->post('notransaksi'),
            'blok' => explode(",", $this->post('kode_blok')),
            'nik' => explode(",", $this->post('pemanenid')),
            'nospbref' => explode(",", $this->post('nospbref')),
            'tph' => explode(",", $this->post('kode_tph')),
            'sesi' => explode(",", $this->post('sesi')),
            'tipepanen' => explode(",", $this->post('kode_tipe_panen')),
            'status' => explode(",", $this->post('status')),
            'status_flag' => explode(",", $this->post('status_flag')),
            'jjg' => explode(",", $this->post('jjg')),
            'brondolan' => explode(",", $this->post('brondolan')),
            'tanggalpanen' => explode(",", $this->post('tgl_panen')),
            'nopnnref' => explode(",", $this->post('notransaksi_panen')),
            'cetak' => explode(",", $this->post('cetak')),
            'tahuntanam' => explode(",", $this->post('tahun_tanam')),
            'lastupdate' => explode(",", $this->post('lastupdate'))
        ];

        $afdeling = $user['subbagian'];
        $dateTransactionSplit['tlgjoin'] = date("Ymd", strtotime($this->post('tanggal')));

        foreach ($data['blok'] as $k => $kodeblok) {
            $noqr = strlen($data['sesi'][$k]) > 8 ? substr($data['sesi'][$k], 0, 5) . substr($data['sesi'][$k], 5) : substr($data['sesi'][$k], 0, 3) . substr($afdeling, 4, 2) . substr($data['sesi'][$k], 3);
            $noidentifikasi = $dateTransactionSplit['tlgjoin'] . $noqr;
        }

        $aktifitas = $this->getHeaderSPB("WHERE nospb = '{$data['nospb']}' AND nosync = '{$this->post('no_syncronized')}' LIMIT 1");
        if ($aktifitas && $aktifitas->rowCount() > 0 && trim($this->post('status')) != "") {
            $maxNum = $this->getPrestasi("WHERE nospb = '{$data['nospb']}'")->rowCount();
            $has_TPB = false;
            $dataInsert = [];

            for ($i = 0; $i < count($data['status']); $i++) {
                $maxNum++;
                $dataArr = [
                    'nospb'         => $data['nospb'],
                    'blok'          => $data['blok'][$i] ?? null,
                    'nik'           => $data['nik'][$i],
                    'nospbref'      => $data['nospbref'][$i] ?? null,
                    'tph'           => $data['tph'][$i] ?? '',
                    'sesi'          => $data['sesi'][$i],
                    'tipepanen'     => $data['tipepanen'][$i],
                    'status'        => $data['status'][$i],
                    'status_flag'   => $data['status_flag'][$i],
                    'jjg'           => $data['jjg'][$i],
                    'brondolan'     => $data['brondolan'][$i],
                    'tanggalpanen'  => $data['tanggalpanen'][$i],
                    'nopnnref'      => $data['nopnnref'][$i],
                    'cetak'         => $data['cetak'][$i],
                    'lastupdate'    => $data['lastupdate'][$i],
                ];

                $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_spbdt_mobile");

                if ($data['status'][$i] == 'TPB') {
                    $has_TPB = true;
                }
            }

            if ($this->uri->segments[5] == 'load') return $dataInsert;
            if ($has_TPB) {
                $dataUpdate = array(
                    'has_tpb' => '1'
                );
                $this->update($dataUpdate, $this->db->dbname . ".kebun_spbht_mobile", "nospb='" . $data['nospb'] . "' and nosync = '" . $this->post('no_syncronized') . "'");
            }

            if ($this->exec($dataInsert)) {
                if (!$this->response['error']) {
                    return $this->responseSuccess('Success Insert Detail', [
                        'notransaksi' => $data['nospb'],
                        'no_syncronized' => $this->post('no_syncronized'),
                        'tanggal' => $this->post('tanggal'),
                    ]);
                }
            } else {
                $this->execdeleteAllDetailSPB($this->post('notransaksi'));
                return $this->responseError("Failed Insert Detail! : " . $this->response['message'], 409);
            }
        } else {
            $this->execdeleteAllDetailSPB($this->post('notransaksi'));
            return $this->responseError("Failed Insert Detail! : Data Aktifitas Belum terbentuk", 409);
        }
    }


    public function addTkbm()
    {
        $data = [
            'tkbm' => explode(",", $this->post('tkbm')),
            'nama_tkbm' => explode(",", $this->post('nama_tkbm')),
            'kegiatan' => explode(",", $this->post('kegiatan')),
            'sesi' => explode(",", $this->post('sesi')),
            'jjg' => explode(",", $this->post('jjg')),
            'brdl' => explode(",",$this->post('brondolan'))
        ];

        $aktifitas = $this->getHeaderSPB("WHERE nospb = '{$this->post('notransaksi')}' and nosync = '{$this->post('no_syncronized')}' LIMIT 1");

        if ($aktifitas && $aktifitas->rowCount() > 0 && !empty($data['tkbm'])) {
            foreach ($data['tkbm'] as $k => $v) {
                $dataArr = [
                    'nospb' => $this->post('notransaksi'),
                    'karyawanid' => $v,
                    'namakaryawan' => $data['nama_tkbm'][$k],
                    'kegiatan' => $data['kegiatan'][$k],
                    'sesi' => $data['sesi'][$k],
                    'jjg' => $data['jjg'][$k],
                    'brondolan' => $data['brdl'][$k],
                    'jumlah' => 0,
                ];
                $dataInsert[$k] = $this->query_insert($dataArr, $this->db->dbname . ".kebun_spbtkbm_mobile");
            }

            if ($this->uri->segments[5] == 'load') return $dataInsert;

            if ($this->exec($dataInsert)) {
                return $this->responseSuccess("Success Insert TKBM", [
                    'notransaksi' => $this->post('notransaksi'),
                    'no_syncronized' => $this->post('no_syncronized'),
                    'tanggal' => $this->post('tanggal')
                ]);
            } else {
                $this->execdeleteAllDetailSPB($this->post('notransaksi'));
                return $this->responseError("Failed Insert! : " . $this->response['message'], 409);
            }
        } else {
            $this->execdeleteAllDetailSPB($this->post('notransaksi'));
            return $this->responseError("Failed TKBM! : Data Aktifitas Belum terbentuk", 409);
        }
    }

    public function checkdatarow()
    {
        $jmlRow = (int)$this->post('jumlah_detail') + (int)$this->post('jumlah_tkbm');
        $nospb = $this->post('notransaksi');
        $dbname = $this->db->dbname;
        $str = "SELECT nospb FROM {$dbname}.kebun_spbdt_mobile WHERE nospb = '{$nospb}' UNION ALL SELECT nospb FROM {$dbname}.kebun_spbtkbm_mobile WHERE nospb = '{$nospb}'";

        if ($this->uri->segments[5] == 'load') return $str;

        $datacheck = $this->query($str);
        if ($datacheck and $datacheck->rowCount() == $jmlRow) {
            $dataUpdate = ["syn" => "1"];
            $this->update($dataUpdate, "{$dbname}.kebun_spbht_mobile", "nospb='{$nospb}'");
            if (!$this->response['error']) {
                return $this->responseSuccess("Sinkronisasi Data Telah Selesai.", [
                    'notransaksi' => $nospb,
                    'no_syncronized' => $this->post('no_syncronized'),
                    'tanggal' => $this->post('tanggal')
                ]);
            } else {
                $this->execdeleteAllDetailSPB($this->post('notransaksi'));
                return $this->responseError("Failed! : Gagal Update " . $this->response['message'], 409);
            }
        } else {
            $this->execdeleteAllDetailSPB($this->post('notransaksi'));
            return $this->responseError("Failed! : Data Aktifitas Belum terbentuk", 409);
        }
    }


    public function getHeaderSPB($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_spbht_mobile {$where}";
        // echo $q;
        $data = $this->query($q);
        return $data;
    }
    private function getPrestasi($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_spbdt_mobile {$where}";
        $data = $this->query($q);
        return $data;
    }
    private function reSyntransMobile($notransaksi)
    {
        try {
            $dataUpdate = array(
                "syn" => "0"
            );
            $this->update($dataUpdate, $this->db->dbname . ".kebun_spbht_mobile", "nospb='" . $this->post('notransaksi') . "'");
            $this->delete($this->db->dbname . ".kebun_spbdt_mobile", "nospb='" . $notransaksi . "'");

            $this->delete($this->db->dbname . ".kebun_spbtkbm_mobile", "nospb='" . $notransaksi . "'");
        } catch (PDOException $e) {
            return $this->responseError("Failed! Resyncronize SPB : ({$e->getMessage()}) !!", 409);
        }
    }
    private function filter_query($filter, $flag = 'OR')
    {
        $where = array();
        $str = "";
        if (count($filter) > 0) {
            foreach ($filter as $k => $v) {
                if ($filter[$k] != '') {
                    $value = $v;
                    if (is_array($v)) {
                        if ($flag == 'OR') {
                            $value = implode("' OR " . $k . "='", $v);
                            $where[] = "(" . $k . "='" . $value . "')";
                        } else if ($flag == 'IN') {
                            $value = implode("','", $v);
                            $where[] = $k . " IN ('" . $value . "')";
                        }
                    } else {
                        $where[] = $k . "='" . $value . "'";
                    }
                }
            }
        }
        if (count($where) > 0) {
            $str = "WHERE " . implode(" AND ", $where);
        }
        return $str;
    }
    private function getdetail($nospb)
    {
        $result = array();
        if (count($nospb) > 0) {
            $filter_det['nospb'] = $nospb;
            $filter_det['status'] = array("Double", "TPB");
            $whereStr = $this->filter_query($filter_det, 'IN');
            $spb_detail = $this->getPrestasi($whereStr);
            $nospbDouble = array();
            $DataDouble = array();
            if ($spb_detail && $spb_detail->rowCount() > 0) {
                while ($bar = $spb_detail->fetch()) {
                    $nospbDouble[] = $bar->nospbref;
                    $DataDouble[$bar->nospbref] = $bar;
                    $this->SPB_InHirarky_parent[$bar->nospbref] = $bar->nospb;
                }

                $this->SPB_InHirarky = array_merge($this->SPB_InHirarky, $nospbDouble);
            }
            if (count($nospbDouble) > 0) {
                $nospbDouble = array_unique($nospbDouble);
                $result = $this->getHeader($nospbDouble, $DataDouble);
                foreach ($result as $k => $v){
                    $detailSPB = $this->getdetail(array($v['nospb']));
                    if (count($detailSPB) > 0) {
                        $result[$k]['child'] = (array)@$detailSPB;
                    } else {
                        $result[$k]['child'] = array();
                    }
                }
            }
        }
        return $result;
    }
    private function getHeader($nospb, $dataSPB = array())
    {
        $result = array();
        if (count($nospb) > 0) {
            $filter['nospb'] = $nospb;
            $filter['syn'] = "1";
            $whereStr = $this->filter_query($filter, 'IN');
            $spb = $this->getHeaderSPB($whereStr);
            if ($spb && $spb->rowCount() > 0) {
                while ($bar = $spb->fetch()) {
                    $result[] = (array)$bar;
                }
            }
            $catchSPB = array_column($result, 'nospb');
            foreach ($nospb as $v) {
                if (!in_array($v, $catchSPB)) {
                    $d['nospb'] = $v;
                    $d['parent'] = @$dataSPB[$v]->nospb;
                    $d['error'] = TRUE;
                    $d['message'] = "UNDEFINED DATA";
                    $d['tph']   = @$dataSPB[$v]->tph;
                    $d['jjg']   = @$dataSPB[$v]->jjg;
                    $d['brondolan']   = @$dataSPB[$v]->brondolan;
                    $result[] = (array)$d;
                    $this->SPB_has_missing[] = (array)$d;
                }
            }
        }
        return $result;
    }

    private function getSPBHirarky($param)
    {
        $filter['nospb'] = @$param['nospb'];
        if (!empty($param['periode'])) {
            $filter["DATE_FORMAT(tanggal,'%Y-%m')"] = $param['periode'];
        }
        if (!empty($param['tanggal'])) {
            $filter['tanggal'] = @$param['tanggal'];
        }

        $filter['kodeorg'] = @$param['kodeorg'];
        $filter['tujuan'] = (array)@$param['tujuan'];
        $filter['flag'] = @$param['flag'];
        $filter['syn'] = @$param['syn'];
        $whereStr = $this->filter_query($filter);
        $spb = $this->getHeaderSPB($whereStr . " ORDER BY tanggal DESC");
        // var_dump($spb->queryString);
        $spbData = array();
        $result = array();

        if ($spb && $spb->rowCount() > 0) {
            while ($bar = $spb->fetch()) {
                $kodecustomer = $this->model('Setup_customer')->getCustomer("kodecustomer","WHERE namacustomer = '{$bar->penerimatbs}' LIMIT 1") ?? [];
                $kodecustomer = count($kodecustomer) > 0 ? $kodecustomer[0]['kodecustomer'] : null;

                $d = array();
                $d['missing'] = null;
                $d = array_merge($d, (array)$bar);
                if ($kodecustomer !== null) {
                    $d['penerimatbs'] = $kodecustomer;
                }
                $spbData[] = $d;
            }
            $listNospb = array_column($spbData, 'nospb');
            $filter_det['nospbref'] = array_unique($listNospb);
            $filter_det['status'] = array("Double", "TPB");
            $whereStr = $this->filter_query($filter_det, 'IN');
            $spb_detail = $this->getPrestasi($whereStr);

            $spbDataDetail = array();
            if ($spb_detail && $spb_detail->rowCount() > 0) {
                while ($bar = $spb_detail->fetch()) {
                    $spbDataDetail[] = $bar->nospbref;
                }
            }
            $nospbDouble = array_unique($spbDataDetail);
            foreach ($spbData as $k => $v) {
                if (!in_array($v['nospb'], $nospbDouble)) {
                    $result[] = (array)$v;
                }
            }
        }

        return $result;
    }

    private function getSPBHirarkyDetail($param)
    {
        $result = $this->getSPBHirarky($param);
        $result = array_shift($result);
        $this->SPB_InHirarky[] = $result['nospb'];
        $result['child'] = $this->getdetail(array($result['nospb']));
        $this->SPB_InHirarky = array_unique($this->SPB_InHirarky);
        $filter_det['nospb'] = $this->SPB_InHirarky;
        $filter_det['status'] = array("Normal", "Abnormal");
        $whereStr = $this->filter_query($filter_det, 'IN');
        $spb_detail = $this->getPrestasi($whereStr);
        $hasilpanen = array();
        $hasilAbnormal = array();
        $hasilBlock = array();
        $hasilSPB = array();
        function getParent_($nospb,$parent){
            $result = null;
            if(!empty($nospb)){
                $result[] = $nospb;
                if(!empty($par = getParent_($parent[$nospb],$parent))){
                    $result = array_merge($result,$par);
                }
                
            }
            return $result;
        }
        $hasilSPB_parent = array();
        if ($spb_detail && $spb_detail->rowCount() > 0) {
            while ($bar = $spb_detail->fetch()) {
                $bar->tracking = getParent_($bar->nospb,$this->SPB_InHirarky_parent);
                if ($bar->status == 'Normal') {
                    $hasilpanen[] = (array)$bar;
                } else {
                    $hasilAbnormal[] = (array)$bar;
                }
                $hasilBlock[$bar->blok]['jjg'] += $bar->jjg;
                $hasilBlock[$bar->blok]['brondolan'] += $bar->brondolan;
                if(count($bar->tracking)>0){
                    foreach ($bar->tracking as $v) {
                        @$hasilSPB[$v]['jjg_angkut'] += $bar->jjg;
                        @$hasilSPB[$v]['brondolan_angkut'] += $bar->brondolan;
                    }
                }
            }
        }
        if (count($this->SPB_has_missing)) {
            $result['missing'] = $this->SPB_has_missing;
        }
        $result['docket'] = $hasilpanen;
        $result['abnormal'] = $hasilAbnormal;
        $result['blok'] = $hasilBlock;
        
        $result['spb'] = $hasilSPB;
        return $result;
    }

    public function getdspb_notposted($user)
    {
        $filter['tanggal'] = @$this->post('tanggal');
        $filter['kodeorg'] = @$this->post('kodeorg');
        $filter['periode'] = @$this->post('periode');
        $filter['tujuan'] = array("0", "1", "3");
        $filter['flag'] = "0";
        $filter['syn'] = "1";
        return $this->getSPBHirarky($filter);
    }
    public function getspbdetail_notposted($user)
    {
        $filter['nospb'] = @$this->post('nospb');
        $filter['tujuan'] = array("0", "1", "3");
        $filter['flag'] = "0";
        $filter['syn'] = "1";
        return $this->getSPBHirarkyDetail($filter);
    }
    // erp mobile
    public function getdspb_sync($param = array())
    {
        $filter['tujuan'] = array("0", "1", "3");
        $filter['syn'] = "1";
        $filter = array_merge($param, $filter);
        return $this->getSPBHirarky($filter);
    }
    public function getspbdetail_sync($nospb = null)
    {
        $filter['nospb'] = $nospb ?: @$this->post('nospb');
        $filter['tujuan'] = array("0", "1", "3");
        $filter['syn'] = "1";
        return $this->getSPBHirarkyDetail($filter);
    }
    public function updateFlag()
    {
        $nospb = $this->post('nospb');
        $flag = $this->post('flag');
        $listSPBNumber = array($nospb);
        if (empty($nospb) || (empty($flag) and $flag != 0)) {
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Parameter nospb & flag harus diisi";
            return $this->response;
        }
        $spb = $this->getHeaderSPB("WHERE nospb = '$nospb' LIMIT 1");
        if (!$spb || $spb->rowCount() == 0) {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Data tidak ada";
            return $this->response;
        }
        $dt = $spb->fetch();
        if ($dt->flag == $flag  || $dt->syn == '0') {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            if ($dt->syn == '1') {
                $this->response['message'] = ($dt->flag == '1' and $dt->syn == '1') ? "Failed! : data already posted !!" : "Failed! : data already unposted !!";
            } else {
                $this->response['message'] = "Data belum selesai Di Syncronize";
            }
            return $this->response;
        }
        if (!$this->response['error']) {
            $this->getdetail($listSPBNumber);
            //merge parent child SPB number
            $listSPBNumber = array_merge($listSPBNumber, $this->SPB_InHirarky);
            $whereStr = "nospb IN ('" . implode("','", $listSPBNumber) . "')";
            $exec = $this->update(['flag' => $flag], "{$this->db->dbname}.kebun_spbht_mobile", $whereStr);
            if ($exec) {
                $this->response['error'] = false;
                $this->response['message'] = "Success";
                $this->response[(($flag == '1') ? 'Posted' : 'Unposted')] = $listSPBNumber;
            } else {
                return $this->responseError("Failed update! : " . $this->response['message'], 409);
            }
        }
        return $this->response;
    }

    // TPB Not Connection 
    public function getdspb_notpostedNC($user)
    {
        $filter['tanggal'] = @$this->post('tanggal');
        $filter['kodeorg'] = @$this->post('kodeorg');
        $filter['tujuan'] = array("0", "1", "3");
        $filter['has_tpb'] = "0";
        $filter['flag'] = "0";
        $filter['syn'] = "1";
        return $this->getSPBHirarkyNC($filter, false);
    }

    public function getdspb_onlyTPBNC($user)
    {
        $filter['tanggal'] = @$this->post('tanggal');
        $filter['kodeorg'] = @$this->post('kodeorg');
        $filter['flag'] = "0";
        $filter['syn'] = "1";
        $dataTPB = $this->getSPBTPBHasDone($filter);
        return $dataTPB;
    }
    function getdspb_onlyTPBDetailNC()
    {
        $filter['tpb'] = @$this->post('tpb');
        $filter['startdate'] = @$this->post('startdate');
        $filter['todate'] = @$this->post('todate');
        // $filter['nospbref'] = @$this->post('nospbref');

        $filter['flag'] = "0";
        $filter['syn'] = "1";
        $arrRangeTPB = array();
        $arrRangeTPB[] = "(a.tph = '" . $filter['tpb'] . "' and a.lastupdate >= '" . $filter['startdate'] . "' and a.lastupdate <= '" . $filter['todate'] . "')";
        // $arrRangeTPB[] = "(a.nospbref = '".$filter['nospbref']."')";

        $spbref = array();
        $listRangeTPB = "";
        $q = "select a.* from " . $this->db->dbname . ".kebun_spbdt_mobile a
            left join " . $this->db->dbname . ".kebun_spbht_mobile b on a.nospb=b.nospb 
            WHERE a.status = 'TPB' and b.syn = '" . $filter['syn'] . "' and b.flag = '" . $filter['flag'] . "' 
            and (" . implode(" OR ", $arrRangeTPB) . ") ";

        $dataTPB = '';
        $allTPB = $this->query($q);
        if ($allTPB and $allTPB->rowCount() > 0) {
            while ($r = $allTPB->fetch()) {
                $dataTPB = $r->tph;
                $d = array();
                $d['nospbref'] = $r->nospbref;
                $d['nospb'] = $r->nospb;
                $d['tpb'] = $r->tph;
                $d['jjg'] = $r->jjg;
                $d['lastupdate'] = $r->lastupdate;
                $spbref[$r->tph][] = $d;
            }
        }

        $listNospb = array_unique(array_column($spbref[$filter['tpb']], 'nospb'));
        asort($listNospb);
        $parentNospb = array();
        foreach ($listNospb as $nospb) {
            if (!in_array($nospb, $parentNospb)) {
                if (!empty($parent = $this->getParentSPB($nospb))) {
                    $parentNospb[] = $parent;
                } else {
                    $parentNospb[] = $nospb;
                }
            }
        }

        // $result['tpb'] = $filter['tpb'];
        $result['tpb'] =  $dataTPB;
        $result['startdate'] = $filter['startdate'];
        $result['todate']   = $filter['todate'];
        $result['kodeorg'] = substr($filter['tpb'], 0, 4);
        $result['jjg_angkut'] = 0;
        $result['brondolan_angkut'] = 0;
        $result['jjg_TPB'] = 0;
        $result['brondolan_TPB'] = 0;
        $result['jjg_angkut_TPB'] = 0;
        $result['brondolan_angkut_TPB'] = 0;
        $result['missing'] = NULL;
        // $dataTPB[$k]['tpb_ref'] = $spbref[$v['tpb']];
        if (date("Y-m-d", strtotime($filter['startdate'])) == date("Y-m-d", strtotime($filter['todate']))) {
            $param['tanggal'] = date("Y-m-d", strtotime($filter['startdate']));
        } else {
            $param['startdate'] = date("Y-m-d", strtotime($filter['startdate']));
            $param['todate'] = date("Y-m-d", strtotime($filter['todate']));
        }
        $param['kodeorg'] = substr($filter['tpb'], 0, 4);
        $param['penerimatbs'] = $filter['tpb'];
        $spb_tpb = $this->getSPBtoTPB($param);
        if (count($spb_tpb) > 0) {
            foreach ($spb_tpb as $vNospb) {
                $this->SPB_InHirarky_parent = array();
                $this->SPB_InHirarky = array();
                $result['spb_tpb'][] = $this->getSPBHirarkyDetailNC($vNospb);
            }
        }
        if (count($parentNospb) > 0) {
            foreach ($parentNospb as $k => $vNospb) {
                $this->SPB_InHirarky_parent = array();
                $this->SPB_InHirarky = array();
                $param__['nospb'] = $vNospb;
                $param__['tujuan'] = array("0", "1", "3");
                $param__['flag'] = "0";
                $param__['syn'] = "1";
                $result['spb_pabrik'][] = $this->getSPBHirarkyDetailNC(array_shift($this->getSPBHirarkyNC($param__, true)));
            }
        }


        $tpb_blok = array_column($result['spb_tpb'], 'blok');
        if (count($tpb_blok) > 0) {
            foreach ($tpb_blok as $datablok) {
                if (count($datablok) > 0) {
                    foreach ($datablok as $kodeblok => $v) {
                        @$result['blok'][$kodeblok]['jjg'] += $v['jjg'];
                        @$result['blok'][$kodeblok]['brondolan'] += $v['brondolan'];
                        @$result['jjg_TPB'] += $v['jjg'];
                        @$result['brondolan_TPB'] += $v['brondolan'];
                    }
                }
            }
        }
        $spb_pabrik = array_column($result['spb_pabrik'], 'blok');
        if (count($spb_pabrik) > 0) {
            foreach ($spb_pabrik as $datablok) {
                if (count($datablok) > 0) {
                    foreach ($datablok as $kodeblok => $v) {
                        @$result['blok'][$kodeblok]['jjg'] += $v['jjg'];
                        @$result['blok'][$kodeblok]['brondolan'] += $v['brondolan'];
                    }
                }
            }
        }

        if (count($this->SPB_has_tpb) > 0) {
            foreach ($this->SPB_has_tpb as $spb => $dataTPB) {
                $result['subtotal'][$spb]['jjg_angkut'] = (int)array_sum(array_column($dataTPB, 'jjg'));
                $result['subtotal'][$spb]['brondolan_angkut'] = (float)number_format((float)array_sum(array_column($dataTPB, 'brondolan')), 1);
            }
        }
        $spb_pabrik_angkutNormal = array_column($result['spb_pabrik'], 'spb');
        if (count($spb_pabrik_angkutNormal) > 0) {
            foreach ($spb_pabrik_angkutNormal as $listspb) {
                if (count($listspb) > 0) {
                    foreach ($listspb as $spb => $dataTPB) {
                        @$result['subtotal'][$spb]['jjg_angkut'] += (int)$dataTPB['jjg_angkut'];
                        @$result['subtotal'][$spb]['brondolan_angkut'] += (float)number_format((float)$dataTPB['brondolan_angkut'], 1);
                    }
                }
            }
        }

        $spb_pabrik_has_tpb = array_column($result['spb_pabrik'], 'has_tpb');
        if (count($spb_pabrik_has_tpb) > 0) {
            foreach ($spb_pabrik_has_tpb as $has) {
                if (!empty($has) and count($has) > 0) {
                    @$result['jjg_angkut_TPB'] += (int)array_sum(array_column($has, 'jjg'));
                    @$result['brondolan_angkut_TPB'] += (float)number_format((float)array_sum(array_column($has, 'brondolan')), 1);
                }
            }
        }

        if (count($this->SPB_has_missing)) {
            $result['missing'] = $this->SPB_has_missing;
        }
        $result['jjg_angkut'] = (int)array_sum(array_column($result['subtotal'], 'jjg_angkut'));
        $result['brondolan_angkut'] = (int)array_sum(array_column($result['subtotal'], 'subtotal'));

        $result['spb_for_flag'] = $this->SPB_AllHirarky;

        return $result;
    }
    function getParentSPB($noSPB = "")
    {
        $parentNospb = $noSPB;
        if ($noSPB != "") {
            $q = "SELECT f.nospb FROM (SELECT @id AS _id, (SELECT @id := nospb FROM kebun_spbdt_mobile WHERE nospbref = _id  and `status`='Double')
                FROM (SELECT @id := '" . $noSPB . "' ) tmp1
                JOIN kebun_spbdt_mobile ON @id <> 0 ) tmp2
                JOIN kebun_spbdt_mobile f ON tmp2._id = f.nospbref and f.status='Double'
                order by nospb DESC limit 1";
            $res = $this->query($q);
            if ($res and $res->rowCount() > 0) {
                $r = $res->fetch();
                $parentNospb = $r->nospb;
            }
        }
        return $parentNospb;
    }
    function getSPBtoTPB($param = array())
    {
        $filter['startdate'] = $param['startdate']; //@$this->post('tanggal');
        $filter['todate'] = $param['todate']; //@$this->post('tanggal');
        $filter['kodeorg'] = $param['kodeorg']; //@$this->post('kodeorg');
        $filter['penerimatbs'] = $param['penerimatbs']; //@$this->post('periode');
        $filter['tujuan'] = array("2");
        $filter['flag'] = "0";
        $filter['syn'] = "1";
        return $this->getSPBHirarkyNC($filter);
    }
    function getSPBTPBHasDone($param = array())
    {
        $filter['tanggal'] = @$param['tanggal'];
        $filter['kodeorg'] = @$param['kodeorg'];
        $filter['tpb'] = @$param['tpb'];
        $filter['flag'] = @$param['flag'];
        $filter['syn'] = @$param['syn'];
        $filter['addWhere'] = @$param['addWhere'];
        $filter['limit'] = @$param['limit'];
        $kodeorg = $tpb = $tanggal = "";
        if ($filter['kodeorg'] != "") {
            $kodeorg = " and b.kodeorg = '" . $filter['kodeorg'] . "'";
        }
        if ($filter['tpb'] != "") {
            $tpb = " and a.tph = '" . $filter['tpb'] . "'";
        }
        if ($filter['tanggal'] != "") {
            $tanggal = " and b.tanggal = '" . $filter['tanggal'] . "'";
        }
        if ($filter['limit'] != "") {
            $limit = " limit " . $filter['limit'] . "";
        }
        if ($filter['addWhere'] != "") {
            $addWhere = $filter['addWhere'];
        }
        $q = "select a.nospb,a.nospbref as nospbref,a.tph as tpb,a.lastupdate from " . $this->db->dbname . ".kebun_spbdt_mobile a
        left join " . $this->db->dbname . ".kebun_spbht_mobile b on a.nospb=b.nospb 
        WHERE a.status = 'TPB' and a.status_flag = '1' and b.syn = '" . $filter['syn'] . "' and b.flag = '" . $filter['flag'] . "' " . $kodeorg . " " . $tpb . " " . $tanggal . " " . $addWhere . " order by b.tanggal DESC,a.nospbref DESC " . $limit;
        // echo $q;
        $spb = $this->query($q);
        $data = array();
        $resData = array();
        $result = array();
        if ($spb and $spb->rowCount() > 0) {
            while ($bar = $spb->fetch()) {
                $data[$bar->tpb][] = (array)$bar;
            }
        }
        if (count($data) > 0) {
            foreach ($data as $tpb => $v) {
                foreach ($v as $header) {
                    $result['nospb']        = $this->getParentSPB($header['nospb']);
                    $result['nospbref']     = $header['nospbref'];
                    $result['tpb']          = $header['tpb'];
                    $result['startdate']    = date("Y-m-d 00:00:00", strtotime($header['lastupdate'])); //date("Y-m-d 00:00:00",strtotime(str_replace($header['tpb'],"",$header['nospbref'])));
                    $result['todate']       = date("Y-m-d H:i:s", strtotime($header['lastupdate'])); //date("Y-m-d H:i:s",strtotime(str_replace($header['tpb'],"",$header['nospbref'])));
                    $result['status_tpb']   = "DONE";

                    if (count($v) > 1) {
                        $lastRow = (count($v) - 1);
                        $result['startdate'] = date("Y-m-d H:i:s", strtotime(str_replace($v[$lastRow]['tpb'], "", $v[$lastRow]['nospbref'])));
                        continue;
                    }
                }
                if (count($v) == 1 and $filter['limit'] != 1) {
                    $newParam['tpb'] = $result['tpb'];
                    $newParam['syn'] = '1';
                    $newParam['flag'] = '0';
                    $newParam['addWhere'] = " and a.nospbref != '" . $result['nospbref'] . "' and b.tanggal < '" . $filter['tanggal'] . "'";
                    $newParam['limit'] = 1;
                    $hasilPencarian = $this->getSPBTPBHasDone($newParam);
                    if (count($hasilPencarian) > 0) {
                        $done_before = array_shift($hasilPencarian)['todate'];
                        $result['startdate'] = date("Y-m-d H:i:s", strtotime(" +1 second" . $done_before));
                    }
                }
                $resData[] = $result;
            }
        }
        return $resData;
    }

    private function getSPBHirarkyNC($param, $has_tpb = NULL)
    {
        $filter['nospb'] = @$param['nospb'];
        if (!empty($param['periode'])) {
            $filter["DATE_FORMAT(tanggal,'%Y-%m')"] = $param['periode'];
        }
        if (!empty($param['tanggal'])) {
            $filter['tanggal'] = @$param['tanggal'];
        }
        if (!empty($param['startdate'])) {
            $filter['tanggal>'] = @$param['startdate'];
        }
        if (!empty($param['todate'])) {
            $filter['tanggal<'] = @$param['todate'];
        }
        if (!empty($param['penerimatbs'])) {
            $filter['penerimatbs'] = @$param['penerimatbs'];
        }
        $filter['kodeorg'] = @$param['kodeorg'];
        $filter['tujuan'] = (array)@$param['tujuan'];
        $filter['flag'] = @$param['flag'];
        $filter['syn'] = @$param['syn'];
        $whereStr = $this->filter_query($filter);
        $spb = $this->getHeaderSPB($whereStr . " ORDER BY tanggal DESC");
        $spbData = array();
        $result = array();
        if ($spb and $spb->rowCount() > 0) {
            while ($bar = $spb->fetch()) {
                $spbData[] = (array)$bar;
            }
            //pencarian No SPB yang masuk dalam detail
            $listNospb = array_column($spbData, 'nospb');
            $filter_det['nospbref'] = array_unique($listNospb);
            $filter_det['status'] = array("Double");
            $whereStr = $this->filter_query($filter_det, 'IN');
            $spb_detail = $this->getPrestasi($whereStr);
            $spbDataDetail = array();
            if ($spb_detail and $spb_detail->rowCount() > 0) {
                while ($bar = $spb_detail->fetch()) {
                    $spbDataDetail[] = $bar->nospbref;
                }
            }
            $nospbDouble = array_unique($spbDataDetail);
            $res_temp = array();
            foreach ($spbData as $k => $v) {
                if (!in_array($v['nospb'], $nospbDouble)) {
                    $res_temp[] = (array)$v;
                }
            }
            //pencarian No SPB yang memiliki Jaringan TPB dalam detail
            $this->SPB_has_tpb = array();
            $listNospb = array_column($res_temp, 'nospb');
            $param['nospb'] = array_unique($listNospb);
            $hasTPB = $this->getHasFromTPB($param);
            $listSPBhasTPB = array();
            if (count($this->SPB_has_tpb) > 0) {
                foreach ($this->SPB_has_tpb as $kNospb => $listTPB) {
                    $listSPBhasTPB[$kNospb] = $listTPB;
                }
            }
            // print_r($this->SPB_has_tpb);
            foreach ($res_temp as $k => $v) {
                if (!in_array($v['nospb'], array_keys($listSPBhasTPB))) {
                    $res_temp[$k]["has_tpb"] = @$listSPBhasTPB[$res_temp[$k]["nospb"]];
                    if ($has_tpb === true) {
                        unset($res_temp[$k]);
                    }
                } else {
                    $res_temp[$k]["has_tpb"] = @$listSPBhasTPB[$res_temp[$k]["nospb"]];
                    if ($has_tpb === false) {
                        unset($res_temp[$k]);
                    }
                }
            }
            $result = array_merge($result, $res_temp);
        }
        return $result;
    }

    private function getHasFromTPB($param, $parent = array())
    {
        $result = array();
        $filter_det['nospb'] = array_unique($param['nospb']);
        $filter_det['status'] = array("Double", "TPB");
        $whereStr = $this->filter_query($filter_det, 'IN');
        $spb_detail = $this->getPrestasi($whereStr);
        if (empty($parent)) {
            foreach ($filter_det['nospb'] as $nospb) {
                $parent[$nospb] = $nospb;
            }
        }
        $tpb_temp = array();
        $doub_temp = array();
        if ($spb_detail and $spb_detail->rowCount() > 0) {
            while ($bar = $spb_detail->fetch()) {
                $d = array();
                $d['nospb'] = $bar->nospb;
                $d['nospbref'] = $bar->nospbref;
                $d['jjg'] = $bar->jjg;
                $d['brondolan'] = $bar->brondo©lan;
                $d['flag'] = $bar->status_flag;
                if (strtoupper($bar->status) == 'TPB') {
                    $tpb_temp[] = $bar->nospb;
                    if (count($this->SPB_has_tpb) > 0) {
                        $ada = false;
                        foreach ($this->SPB_has_tpb as $kNospb => $listTPB) {
                            $noSPBTPB = array_column($listTPB, 'nospb');
                            if (in_array($bar->nospb, $noSPBTPB)) {
                                $this->SPB_has_tpb[$kNospb][] = $d;
                                $ada = true;
                            }
                        }
                        if (count($this->SPB_level_double) > 0) {
                            foreach ($this->SPB_level_double as $kNospb => $listDouble) {
                                if (in_array($bar->nospb, $listDouble)) {
                                    $this->SPB_has_tpb[$bar->nospb][] = $d;
                                }
                            }
                        }

                        if ($ada == false) {
                            if (count($this->SPB_level_double) > 0) {
                                foreach ($this->SPB_level_double as $kNospb => $listDouble) {
                                    if (in_array($bar->nospb, $listDouble)) {
                                        $this->SPB_has_tpb[$kNospb][] = $d;
                                    }
                                }
                            } else {
                                $this->SPB_has_tpb[$bar->nospb][] = $d;
                            }
                        }
                    } else {
                        $this->SPB_has_tpb[$bar->nospb][] = $d;
                    }
                } elseif (strtoupper($bar->status) == 'DOUBLE') {
                    $doub_temp[] = $bar->nospbref;
                    if (count($this->SPB_level_double) > 0) {
                        $ada = false;
                        foreach ($this->SPB_level_double as $kNospb => $listDouble) {
                            if (in_array($bar->nospb, $listDouble)) {
                                $this->SPB_level_double[$kNospb][] = $bar->nospbref;
                                $ada = true;
                            }
                        }
                        if ($ada == false) {
                            $this->SPB_level_double[$bar->nospb][] = $bar->nospbref;
                        }
                    } else {
                        $this->SPB_level_double[$bar->nospb][] = $bar->nospbref;
                    }
                }
            }
        }

        $parentNotTPB = array();
        $tpb_temp = array_unique($tpb_temp);
        if (count($this->SPB_has_tpb) > 0) {
            // print_r($this->SPB_has_tpb);
            foreach ($this->SPB_has_tpb as $kNospb => $listTPB) {
                $noSPBTPB = array_column($listTPB, 'nospb');
                if (count(array_intersect($tpb_temp, $noSPBTPB)) > 0) {
                    $result[] = $kNospb;
                } else {
                    $parentNotTPB[] = $kNospb;
                }
            }
        }

        $listParent = array();
        $newParent = array();
        if (count($doub_temp) > 0) {
            if (count($this->SPB_level_double) > 0) {
                foreach ($this->SPB_level_double as $kNospb => $listDouble) {
                    if (!in_array($kNospb, $parentNotTPB)) {
                        $listParent = array_intersect($doub_temp, $listDouble);
                        if (count($listParent) > 0) {
                            foreach ($listParent as $noSPBIntersect) {
                                $newParent[$noSPBIntersect] = $kNospb;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($newParent)) {
            $data['nospb'] = array_keys($newParent);
            if (!empty($res = $this->getHasFromTPB($data, $newParent))) {
                $result = array_merge($result, $res);
            }
        }
        return $result;
    }
    // public function getspbdetail_notpostedNC($user)
    // {
    //     $filter['nospb'] = @$this->post('nospb');
    //     $filter['tujuan'] = array("0", "1", "3");
    //     $filter['flag'] = "0";
    //     $filter['syn'] = "1";
    //     return $this->getSPBHirarkyDetailNC($filter);
    // }
    // erp mobile
    // public function getdspb_syncNC($param = array())
    // {
    //     $filter['tujuan'] = array("0", "1", "3");
    //     $filter['has_tpb'] = "0";
    //     $filter['syn'] = "1";
    //     $filter = array_merge($param, $filter);
    //     return $this->getSPBHirarkyNC($filter);
    // }
    // public function getspbdetail_syncNC($nospb = null)
    // {
    //     $filter['nospb'] = $nospb ?: @$this->post('nospb');
    //     $filter['tujuan'] = array("0", "1", "3");
    //     $filter['syn'] = "1";
    //     return $this->getSPBHirarkyDetailNC($filter);
    // }



    private function getSPBHirarkyDetailNC($param)
    {

        $result = $param;
        $this->SPB_InHirarky[] = $result['nospb'];
        $this->SPB_AllHirarky[] = $result['nospb'];
        $result['child'] = $this->getdetailNC(array($result['nospb']));
        $this->SPB_InHirarky = array_unique($this->SPB_InHirarky);
        $filter_det['nospb'] = $this->SPB_InHirarky;
        $filter_det['status'] = array("Normal", "Abnormal");
        $whereStr = $this->filter_query($filter_det, 'IN');
        $spb_detail = $this->getPrestasi($whereStr);
        $hasilpanen = array();
        $hasilAbnormal = array();
        $hasilBlock = array();
        $hasilSPB = array();
        if ($spb_detail and $spb_detail->rowCount() > 0) {
            while ($bar = $spb_detail->fetch()) {
                if ($bar->status == 'Normal') {
                    $hasilpanen[] = (array)$bar;
                } else {
                    $hasilAbnormal[] = (array)$bar;
                }
                $hasilBlock[$bar->blok]['jjg'] += $bar->jjg;
                $hasilBlock[$bar->blok]['brondolan'] += $bar->brondolan;
                $hasilSPB[$bar->nospb]['jjg_angkut'] += $bar->jjg;
                $hasilSPB[$bar->nospb]['brondolan_angkut'] += $bar->brondolan;
            }
        }
        $result['docket'] = $hasilpanen;
        $result['abnormal'] = $hasilAbnormal;
        $result['blok'] = $hasilBlock;
        foreach ($hasilSPB as $child => $v) {
            if (isset($this->SPB_InHirarky_parent[$child])) {
                $parent = $this->SPB_InHirarky_parent[$child];
                if (isset($hasilSPB[$parent])) {
                    $hasilSPB[$parent]['jjg_angkut'] += $v['jjg_angkut'];
                    $hasilSPB[$parent]['brondolan_angkut'] += $v['brondolan_angkut'];
                } else {
                    $hasilSPB[$parent]['jjg_angkut'] = $v['jjg_angkut'];
                    $hasilSPB[$parent]['brondolan_angkut'] = $v['brondolan_angkut'];
                }
            }
        }
        $result['spb'] = $hasilSPB;
        // $result['spb_h'] = $this->SPB_InHirarky_parent;
        return $result;
    }
    private function getHeaderNC($nospb, $dataSPB = array())
    {
        $result = array();
        if (count($nospb) > 0) {
            $filter['nospb'] = $nospb;
            $filter['syn'] = "1";
            $whereStr = $this->filter_query($filter, 'IN');
            $spb = $this->getHeaderSPB($whereStr);
            if ($spb and $spb->rowCount() > 0) {
                while ($bar = $spb->fetch()) {
                    $result[] = (array)$bar;
                }
            }
            $catchSPB = array_column($result, 'nospb');
            foreach ($nospb as $v) {
                if (!in_array($v, $catchSPB)) {
                    $d['nospb'] = $v;
                    $d['parent'] = @$dataSPB[$v]->nospb;
                    $d['error'] = TRUE;
                    $d['message'] = "UNDEFINED DATA";
                    $d['tph']   = @$dataSPB[$v]->tph;
                    $d['jjg']   = @$dataSPB[$v]->jjg;
                    $d['brondolan'] = @$dataSPB[$v]->brondolan;
                    $result[] = (array)$d;
                    $this->SPB_has_missing[] = (array)$d;
                }
            }
        }
        return $result;
    }
    private function getdetailNC($nospb)
    {
        $result = array();
        if (count($nospb) > 0) {
            $filter_det['nospb'] = $nospb;
            $filter_det['status'] = array("Double");
            $whereStr = $this->filter_query($filter_det, 'IN');
            $spb_detail = $this->getPrestasi($whereStr);
            $nospbDouble = array();
            $DataDouble = array();
            if ($spb_detail and $spb_detail->rowCount() > 0) {
                while ($bar = $spb_detail->fetch()) {
                    $nospbDouble[] = $bar->nospbref;
                    $DataDouble[$bar->nospbref] = $bar;
                    $this->SPB_InHirarky_parent[$bar->nospbref] = $bar->nospb;
                }

                $this->SPB_InHirarky = array_merge($this->SPB_InHirarky, $nospbDouble);
                $this->SPB_AllHirarky = array_merge($this->SPB_AllHirarky, $nospbDouble);
            }
            if (count($nospbDouble) > 0) {
                $nospbDouble = array_unique($nospbDouble);
                $result = $this->getHeaderNC($nospbDouble, $DataDouble);
                $listSPB = array_column($result, 'nospb');
                $detailSPB = $this->getdetailNC($listSPB);
                if (count($detailSPB) > 0) {
                    foreach ($result as $k => $v) {
                        // $this->SPB_has_tpb
                        $result[$k]['has_tpb'] = @$this->SPB_has_tpb[$result[$k]['nospb']];
                        $result[$k]['child'] = (array)$detailSPB;
                    }
                } else {
                    foreach ($result as $k => $v) {
                        $result[$k]['has_tpb'] = @$this->SPB_has_tpb[$result[$k]['nospb']];
                        $result[$k]['child'] = array();
                    }
                }
            }
        }
        return $result;
    }
    public function updateFlagNC()
    {
        $nospb = $this->post('nospb');
        $flag = $this->post('flag');
        $listSPBNumber = array($nospb);
        if (empty($nospb) || (empty($flag) and $flag != 0)) {
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Parameter nospb & flag harus diisi";
            return $this->response;
        }
        $spb = $this->getHeaderSPB("WHERE nospb = '$nospb' LIMIT 1");
        if (!$spb || $spb->rowCount() == 0) {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Data tidak ada";
            return $this->response;
        }
        $dt = $spb->fetch();
        if ($dt->flag == $flag  || $dt->syn == '0') {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            if ($dt->syn == '1') {
                $this->response['message'] = ($dt->flag == '1' and $dt->syn == '1') ? "Failed! : data already posted !!" : "Failed! : data already unposted !!";
            } else {
                $this->response['message'] = "Data belum selesai Di Syncronize";
            }
            return $this->response;
        }
        if (!$this->response['error']) {
            $this->getdetailNC($listSPBNumber);
            //merge parent child SPB number
            $listSPBNumber = array_merge($listSPBNumber, $this->SPB_InHirarky);
            $whereStr = "nospb IN ('" . implode("','", $listSPBNumber) . "')";
            $exec = $this->update(['flag' => $flag], "{$this->db->dbname}.kebun_spbht_mobile", $whereStr);
            if ($exec) {
                $this->response['error'] = false;
                $this->response['message'] = "Success";
                $this->response[(($flag == '1') ? 'Posted' : 'Unposted')] = $listSPBNumber;
            } else {
                return $this->responseError("Failed update! : " . $this->response['message'], 409);
            }
        }
        return $this->response;
    }
    private function responseError($message, $status)
    {
        return [
            'status' => $status,
            'error' => true,
            'message' => $message
        ];
    }

    private function responseSuccess($message, $data = null)
    {
        $response = array_merge([
            'status' => 200,
            'error' => false,
            'message' => $message,
        ]);
        return array_merge($data, $response);
    }

    function deleteAlldetailSPBKeepAktifitas($from, $whr, $del)
    {
        $aktifitas = $this->checkAktifitas($from, $whr);
        if ($aktifitas) {
            $this->response['error'] = false;
            $this->exec($this->query_delete('kebun_spbdt_mobile', $del));
            $this->exec($this->query_delete('kebun_spbtkbm_mobile', $del));
        } else {
            $this->response['error'] = true;
        }
        return $this->response;
    }

    function execdeleteAllDetailSPB($notransaksi)
    {
        $from = "(select * from " . $this->db->dbname . ".kebun_spbht_mobile where flag = '0') a ";
        $whr = "where a.syn = '0' and a.nospb = '" . $notransaksi . "'";
        $del = "nospb='" . $notransaksi . "'";
        $this->deleteAlldetailSPBKeepAktifitas($from, $whr, $del);
    }

    function checkAktifitas($from, $whr)
    {
        $data = array();
        $q = "SELECT * FROM " . $from . $whr;
        $data = $this->query($q);
        return $data;
    }
}
