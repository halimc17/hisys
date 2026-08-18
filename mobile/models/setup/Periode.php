<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Periode extends OWL_Model{
    function akuntantsi($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".setup_periodeakuntansi {$where}";
        $r = $this->fetchdata($q);
        return $r;
    }
}
?>