<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Organisasi extends OWL_Model
{
    function selectOpt($where = "")
    {
        $data = array();
        $q = "select * from {$this->db->dbname}.organisasi {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[$v['kodeorganisasi']] = $v['namaorganisasi'];
            }
        }
        return $data;
    }
    function selectdata($where = "")
    {
        $q = "select * from " . $this->db->dbname . ".organisasi {$where}";
        $r = $this->fetchdata($q);
        return $r;
    }
    function holding()
    {
        $q = "select namaorganisasi from " . $this->db->dbname . ".organisasi where tipe='HOLDING' and (induk='' or induk is null) limit 1";
        $r = $this->fetchdata($q);
        return $r;
    }
    function selecColumn($column, $where = "")
    {
        $q = "select {$column} from " . $this->db->dbname . ".organisasi {$where}";
        $r = $this->fetchdata($q);
        return $r;
    }
    function regional($where = "")
    {
        $q = "select * from " . $this->db->dbname . ".bgt_regional_assignment {$where}";
        $r = $this->fetchdata($q);
        return $r;
    }
    function gudang($induk=array())
    {   
        $where = "";
        if(count($induk) > 0){
            $where = " and induk in ('".implode("','",$induk)."')";
        }
        $data = $this->selecColumn("kodeorganisasi", "where tipe like 'GUDANG%'".$where);
        return $data;
    }
}
