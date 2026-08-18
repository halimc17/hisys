<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Project_schedule extends OWL_Model{
    public function selectData($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".project_schedule {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
        return $data;
    }
    
}
?>