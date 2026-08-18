<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Setup_bycolumn extends OWL_Model{
    function selectOpt($where=""){
        $data = array();
        if($where == ""){
            $where = "where tablename = 'datakaryawan' and isactive ='1'";
        }else{
            $where = $where." and tablename = 'datakaryawan' and isactive ='1'";
        }
        $q = "select id,description from ".$this->db->dbname.".sdm_5bycolumn {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['id']] = $v['description'];
            }
        }
        return $data;
    }
    function selectData($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".sdm_5bycolumn {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
        return $data;
    }





    
}
?>