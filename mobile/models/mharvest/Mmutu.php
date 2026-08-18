<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mmutu extends OWL_Model
{
    function selectQuery(array $pageLimit = array(), $where = '')
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5jenismutu {$where}
		ORDER BY idjenis ASC " . $limitPage;

        $data = $this->query($q, 'ASSOC');
        return $data;
    }
    function selectdata(array $pageLimit = array(), $where = '')
    {
        $result = array();
        $data = $this->selectQuery($pageLimit, $where);
        if ($data and $data->rowCount() > 0) {
            $result = $this->fetch($data);
        }
        return $result;
    }

    // function getmutu($where)
    // {
    //     $data = array();
    //     $q = "select * from " . $this->db->dbname . ".kebun_5jenismutu where aktif ='1'";
    //     $r = $this->fetchdata($q);
    //     if (count($r) > 0) {
    //         $data = $r;
    //     }


    //     $dtSetup = [];
    //     $idx = 0;
    //     // $setupMutu = $this->model('Setup_mutu');

    //     foreach ($data as $dt) {

    //         $kode = $dt['idjenis'];
    //         $kriteria = $dt['kriteria'];

    //         // if ($dt['kode'] != '' && $dt['kode'] != null) {
    //         //     $kode = $dt['kode'];

    //         //     $q =  $setupMutu->getMutuByJenis($dt['getnamakolom'], $dt['namatabel'], "WHERE " . $dt['namakolom'] . "= '" . $dt['kode'] . "'");

    //         //     if (count($q) > 0) {
    //         //         $kriteria = array_shift($q)[$dt['getnamakolom']];
    //         //     }
    //         // }

    //         $dtSetup[$dt['jenis']][] = [
    //             'idjenis' => $dt['idjenis'],
    //             'jenis' => $dt['jenis'],
    //             'satuan' => $dt['satuan'],
    //             'satuan2' => $dt['satuan2'],
    //             'kriteria' => $kriteria,
    //             'lockjjg' => $dt['lockjjg'],
    //             'kode' => $kode,
    //         ];
    //         $idx++;
    //     }
    //     return $dtSetup;
    // }


    function getMutu($where)
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_5jenismutu {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = array(
                    'idjenis' => $v['idjenis'],
                    'jenis' => $v['jenis'],
                    'satuan' => $v['satuan'],
                    'satuan2' => $v['satuan2'],
                    'kriteria' => $v['kriteria'],
                    'lockjjg' => $v['lockjjg'],
                    'kode' => $v['kode'] == '' ?  $v['idjenis'] : $v['kode']
                );
            }
        }
        return $data;
    }

    // function getMutuTransport()
    // {
    //     $data = array();
    //     $q = "select * from " . $this->db->dbname . ".kebun_5jenismutu " . "where jenis = 'Mutu Transport'";
    //     // echo $q;
    //     $r = $this->fetchdata($q);
    //     if (count($r) > 0) {
    //         foreach ($r as $k => $v) {
    //             $data[] = array(
    //                 'idjenis' => $v['idjenis'],
    //                 'jenis' => $v['jenis'],
    //                 'satuan' => $v['satuan'],
    //                 'satuan2' => $v['satuan2'],
    //                 'kriteria' => $v['kriteria'],
    //                 'lockjjg' => '0',
    //                 'kode' => $v['kode'] ?? $v['idjenis']
    //             );
    //         }
    //     }
    //     return $data;
    // }

    // function getMutuBuah()
    // {
    //     $data = array();
    //     $q = "select * from " . $this->db->dbname . ".kebun_5jenismutu " . "where jenis = 'Mutu Buah'";
    //     // echo $q;
    //     $r = $this->fetchdata($q);
    //     if (count($r) > 0) {
    //         foreach ($r as $k => $v) {
    //             $data[] = array(
    //                 'idjenis' => $v['idjenis'],
    //                 'jenis' => $v['jenis'],
    //                 'satuan' => $v['satuan'],
    //                 'satuan2' => $v['satuan2'],
    //                 'kriteria' => $v['kriteria'],
    //                 'lockjjg' => $v['lockjjg'],
    //                 'kode' => $v['kode'] ?? $v['idjenis']
    //             );
    //         }
    //     }
    //     return $data;
    // }

    // {
    //     $data = array();
    //     $q = "select b.* from " . $this->db->dbname . ".kebun_5dendapanen as a left join " . $this->db->dbname . ".kebun_5kodedendapanen b ON a.kodedenda = b.kodedenda where a.kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%' order by b.nourut";
    //     // echo $q;
    //     $r = $this->fetchdata($q);
    //     if (count($r) > 0) {
    //         foreach ($r as $k => $v) {
    //             $data[] = array(
    //                 'idjenis' => $v['id'],
    //                 'jenis' => "Mutu Buah",
    //                 'satuan' => $v['satuan'],
    //                 // 'satuan2' => $v['satuan2'],
    //                 'kriteria' => $v['deskripsi'],
    //                 'lockjjg' => $v['lockjjg'],
    //                 'kode' => $v['kodedenda']
    //             );
    //         }
    //     }
    //     return $data;
    // }
}
