<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_mutu extends OWL_Model
{
    function getmutu($user)
    {
        // $data = array();
        // $q = "select * from " . $this->db->dbname . ".kebun_5jenismutu ";
        // // echo $q;
        // $r = $this->fetchdata($q);
        // if (count($r) > 0) {
        //     $data = $r;
        // }
        // return $data;
        $buah = $this->getMutuBuah($user);
        $hancak = $this->getMutuHancak();
        $transport = $this->getMutuTransport();
        $data = array(
            'Mutu Buah' => $buah,
            'Mutu Hancak' => $hancak,
            'Mutu Transport' => $transport
        );
        return $data;
    }

    function getkodedenda($user)
    {
        $data = array();
        $q = "select b.* from " . $this->db->dbname . ".kebun_5dendapanen as a left join " . $this->db->dbname . ".kebun_5kodedendapanen b ON a.kodedenda = b.kodedenda where a.kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%' order by b.nourut";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data[] = $r;
        }

        return $data;
    }

    function getMutuByJenis($column,$tabel,$where){
        $data = array();

        $str = "SELECT {$column} FROM {$this->db->dbname}.{$tabel} {$where}";
        // echo $str;
        $r = $this->fetchdata($str);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }


    function getMutuHancak()
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_5jenismutu " . "where jenis = 'mutu hancak'";
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
                    'lockjjg' => '0',
                    'kode' => $v['kode'] ?? $v['idjenis']
                );
            }
        }
        return $data;
    }

    function getMutuTransport()
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_5jenismutu " . "where jenis = 'mutu transport'";
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
                    'lockjjg' => '0',
                    'kode' => $v['kode'] ?? $v['idjenis']
                );
            }
        }
        return $data;
    }

    function getMutuBuah($user)
    {
        $data = array();
        $q = "select b.* from " . $this->db->dbname . ".kebun_5dendapanen as a left join " . $this->db->dbname . ".kebun_5kodedendapanen b ON a.kodedenda = b.kodedenda where a.kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%' order by b.nourut";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = array(
                    'idjenis' => $v['id'],
                    'jenis' => "Mutu Buah",
                    'satuan' => $v['satuan'],
                    // 'satuan2' => $v['satuan2'],
                    'kriteria' => $v['deskripsi'],
                    'lockjjg' => $v['lockjjg'],
                    'kode' => $v['kodedenda']
                );
            }
        }
        return $data;
    }
}
