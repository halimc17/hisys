<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Mtk_masterbarang extends OWL_Model{
    function selectOpt($where=""){
        $data = array();
        $q = "select kode,description from ".$this->db->dbname.".mtk_5masterbarang {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['kode']] = $v['description'];
            }
        }
        return $data;
    }
    
}
?>