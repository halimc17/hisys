<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Spb extends OWL_Model{

    public function selectSpbht_m($where=''){
        $data = array();
        $q = "select * from {$this->dbname}.kebun_spbht_mobile {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
        return $data;
    }
    public function selectSpbdt_m($where=''){
        $data = array();
        $q = "select * from {$this->dbname}.kebun_spbdt_mobile {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
        return $data;
    }
    
}
?>