<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sensusproduksi extends OWL_Model
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

    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "' AND tipetransaksi = 'BBC' order by tanggal desc " . $limitPage;
        // $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where tipetransaksi = 'BBC' order by tanggal desc " . $limitPage;
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

    function getDataDtl($where)
    {
        $header = array();
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_2sensusproduksi " . $where
            . "order by nik, arahsensus, nourut, idjenis, nilai desc";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // GET DATA BY BLOK NIK & ARAH
            for ($j = 0; $j < count($r); $j++) {
                if ($j == 0) {
                    $hd = array(
                        'txn' => $r[$j]['notransaksi'],
                        'blok' => $r[$j]['kodeorg'],
                        'nik' => $r[$j]['nik'],
                        'tgl' => $r[$j]['tglpanen'],
                        'arah' => $r[$j]['arahsensus']
                    );
                    array_push($header, $hd);
                } else {
                    $cp = array(
                        'txn' => $r[$j]['notransaksi'],
                        'blok' => $r[$j]['kodeorg'],
                        'nik' => $r[$j]['nik'],
                        'tgl' => $r[$j]['tglpanen'],
                        'arah' => $r[$j]['arahsensus']
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
                    if ($k['blok'] == $r[$i]['kodeorg'] && $k['nik'] == $r[$i]['nik'] && $k['arah'] == $r[$i]['arahsensus']) {
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
        $q = "SELECT nilai FROM " . $this->db->dbname . ".kebun_2sensusproduksi " . $whr;
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
}
