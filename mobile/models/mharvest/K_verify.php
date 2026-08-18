<?php
defined('BASEPATH') or exit('No direct script access allowed');
class K_verify extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_mutubuah_mobile", "kebun_prestasi_mobile");
        $d['key'] = array("notransaksi");
        $this->prepareDB = $d;
    }

    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        // $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where tipetransaksi = 'PNV' ORDER BY tanggal DESC " . $limitPage;
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile where kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "' AND tipetransaksi = 'PNV' ORDER BY tanggal DESC " . $limitPage;
        // echo $q . "<br>\r\n";
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

    function getMutu($where)
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_mutubuah_mobile " . $where . "order by nourut";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
            for ($i = 0; $i < count($data); $i++) {
                //! Ambil Nama
                // $arrPnn = array();
                $q2 = "select kriteria from " . $this->db->dbname . ".kebun_5jenismutu where kode = " . "'{$data[$i]['kodedenda']}'";
                echo $q2;
                $r2 = $this->fetchdata($q2);
                if (count($r2) > 0) {
                    print_r($r2);
                    array_push($data[$i], $data[$i]["kriteria"] = $r2[0]['kriteria']);
                }
            }
        }
        return $data;
    }

    public function getDataDtl($txn)
    {
        $data = array();
        $panen = $this->model('Mpanen');
        $data = $panen->dataVerifikasiView($txn);
        return $data;
    }

    public function kodeMutu()
    {
        $data = array();
        $panen = $this->model('Mpanen');
        $data = $panen->kodeMutu();
        return $data;
    }

    public function getMutuQty($where)
    {
        $data = array();
        $panen = $this->model('Mpanen');
        $data = $panen->getMutuQty($where);
        return $data;
    }

    // public function postingMutuhancak($whr)
    // {
    //     $arr = [
    //         'jurnal' => 1
    //     ];
    //     $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
    //     //    echo $aktifitas;
    //     return $this->response;
    // }

    // public function deleteMutuhancak($whr)
    // {
    //     // $this->exec($this->query_update($arr, 'kebun_aktifitas_mobile', $whr));
    //     $this->exec($this->query_delete('kebun_aktifitas_mobile', $whr));
    //     //    echo $aktifitas;
    //     return $this->response;
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
}
