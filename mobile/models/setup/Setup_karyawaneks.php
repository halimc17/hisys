<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_karyawaneks extends OWL_Model
{
    function getData($loc)
    {
        $data = array();
        $supplierIdString = $this->getKoderekanan($loc);
        // echo $user;
        $q = "select karyawanid, nik, noktp, namakaryawan, lokasitugas, supplierid, nospk 
        from " . $this->db->dbname . ".datakaryawan_external 
        where `status` = '1' AND `supplierid` IN ($supplierIdString) group by nik";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    private function getKoderekanan($loc)
    {
        $koderekanan = array();
        $q = "SELECT a.koderekanan as supplierid, a.sampai, a.divisi, a.keterangan, b.*
        FROM " . $this->db->dbname . ".log_spkht a
        inner join " . $this->db->dbname . ".log_spkdt b on a.notransaksi = b.notransaksi 
        WHERE a.kodeorg = '" . $loc . "' AND `posting` = '1' AND sampai>='" . date('Y-m-d') . "' ";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        $supplierIdString = "'" . implode("','", array_unique($koderekanan)) . "'";
        return $supplierIdString;
    }
}
