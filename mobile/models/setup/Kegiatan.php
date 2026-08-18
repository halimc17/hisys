<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kegiatan extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        // $q = "SELECT a.kodeorg, a.kodekegiatan, a.namakegiatan, a.kelompok, a.kodesegment, a.satuan, a.noakun, a.status, a.premi, a.jenis FROM " . $this->db->dbname . ".setup_kegiatan a
        $q = "SELECT a.kodeorg, a.kodekegiatan, a.namakegiatan, a.kelompok, a.satuan, a.noakun, a.status, a.premi FROM " . $this->db->dbname . ".setup_kegiatan a
		ORDER BY a.kodekegiatan ASC " . $limitPage;

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
    function getData()
    {
        $data = array();
        // $q = "SELECT a.kodeorg, a.kodekegiatan, a.namakegiatan, a.kelompok, a.kodesegment, a.satuan, a.noakun, a.status, a.premi, a.jenis FROM " . $this->db->dbname . ".setup_kegiatan a
        // ORDER BY a.kodesegment ASC ";
        $q = "select kodekegiatan,namakegiatan,satuan,kelompok,noakun,premi from " . $this->db->dbname . ".setup_kegiatan
        where status=1 and kelompok in('BBT','PNN','TB','TBM','TM','SPL')";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }
    function getDataMobile()
    {
        $data = array();
        // $q = "SELECT a.kodeorg, a.kodekegiatan, a.namakegiatan, a.kelompok, a.kodesegment, a.satuan, a.noakun, a.status, a.premi, a.jenis FROM " . $this->db->dbname . ".setup_kegiatan a
        // ORDER BY a.kodesegment ASC ";

        $q = "select kodekegiatan,namakegiatan,satuan,kelompok,noakun,premi,pilihanluas from " . $this->db->dbname . ".setup_kegiatan
        where status=1 and kelompok in('BBT','PNN','TB','TBM','TM','SPL')";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = array(
                    'kodekegiatan' => $v['kodekegiatan'],
                    'namakegiatan' => $v['namakegiatan'],
                    'satuan' => $v['satuan'],
                    'kelompok' => $v['kelompok'] == 'PNN' ? 'TM' : $v['kelompok'],
                    // 'kelompok' => $v['kelompok'],
                    'pilihanluas' => (int)$v['pilihanluas'],
                    'noakun' => $v['noakun'],
                    'premi' => $v['premi']
                );
            }
        }
        return $data;
    }
    function getKegNormaM()
    {
        $data = array();
        $q = "select kodekegiatan,kelompok,tipeanggaran,kodebarang from " . $this->db->dbname . ".setup_kegiatannorma";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = array(
                    'kodekegiatan' => $v['kodekegiatan'],
                    'kelompok' => $v['kelompok'],
                    'tipeanggaran' => $v['tipeanggaran'],
                    'kodebarang' => $v['kodebarang']
                );
            }
        }
        return $data;
    }
}
