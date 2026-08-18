<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_kegiatan extends OWL_Model
{
    function getDataSetupKegiatan()
    {
        $data = array();

        $q = "SELECT a.kodeorg, a.kodekegiatan, a.namakegiatan, a.kelompok, a.satuan, a.noakun, a.status, a.premi FROM " . $this->db->dbname . ".setup_kegiatan a 
		ORDER BY a.namakegiatan ASC ";

        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[$v['kodekegiatan']] = array(
                    'kodekegiatan' => $v['kodekegiatan'],
                    'namakegiatan' => $v['namakegiatan'],
                    'satuan' => $v['satuan'],
                    'kelompok' => $v['kelompok'],
                    'noakun' => $v['noakun'],
                    'premi' => $v['premi']
                );
            }
        }
        return $data;
    }

    public function namaKegiatan($where)
    {
        $data = '';
        $q = "select namakegiatan from " . $this->db->dbname . ".setup_kegiatan {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r[0]['namakegiatan'];
        }
        // print_r($data);
        return $data;
    }

    public function statusBlok($where)
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".setup_kegiatan {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        // print_r($data);
        return $data;
    }

    public function materialDtl($where)
    {
        $data = array();
        $q = "select namabarang, satuan from " . $this->db->dbname . ".log_5masterbarang {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        // print_r($data);
        return $data;
    }
}
