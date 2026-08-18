<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Bkm extends OWL_Model
{

    public function selectBkm($where = "")
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile {$where}";
        echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    public function selectPrestasiBkm($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_prestasi_mobile {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    public function selectKehadiranBkm($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_mutubuah_mobile {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    public function bkmSetupKegiatan()
    {
        $model = $this->model('Setup_kegiatan');
        $data = $model->getDataSetupKegiatan();
        return $data;
    }

    public function getNamaKegiatan($where)
    {
        $model = $this->model('Setup_kegiatan');
        $data = $model->namaKegiatan($where);
        return $data;
    }

    public function getMaterialDtl($where)
    {
        $model = $this->model('Setup_kegiatan');
        $data = $model->materialDtl($where);
        return $data;
    }
}
