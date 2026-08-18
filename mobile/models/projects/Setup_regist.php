<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Setup_regist extends OWL_Model{
    public function selectOpt($where=""){
        $data = array();
        $q = "select id,nama from ".$this->db->dbname.".project_5regist {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['id']] = $v['nama'];
            }
        }
        return $data;
    }

    public function selectData($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".project_5regist {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data =  $r;
        }
        return $data;
    }



    
}
?>