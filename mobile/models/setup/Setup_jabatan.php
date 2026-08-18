<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Setup_jabatan extends OWL_Model{
    function selectdata($where=""){
        $q = "select * from ".$this->db->dbname.".sdm_5jabatan {$where}";
        $r = $this->fetchdata($q);
        return $r;
    }
    function selectataAktif(){
        $r = $this->selectdata("Where aktif = '1'");
        return $r;
    }
}   
?>