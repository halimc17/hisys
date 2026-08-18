<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Setup_kebun extends OWL_Model{
    public function select_jenismutu($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".kebun_5jenismutu {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
        return $data;
    }
}
?>