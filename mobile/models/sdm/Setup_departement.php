<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Setup_departement extends OWL_Model{
    function init(){
        // IF TABLE NOT EXISTS
        if($this->table_exists('sdm_5departemen')){
            $crteate = "CREATE TABLE IF NOT EXISTS `sdm_5departemen` (
                `kode` char(10) NOT NULL,
                `nama` varchar(45) NOT NULL,
                `aktif` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=>tidak aktif;1=>aktif',
                PRIMARY KEY (`kode`),
                KEY `kode_aktif` (`kode`,`aktif`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
            $this->exec($crteate);
        }
    }
    function selectOpt(){
        $data = array();
        $q = "select kode,nama from ".$this->db->dbname.".sdm_5departemen where aktif ='1'";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['kode']] = $v['nama'];
            }
        }
        return $data;
    }
    // function add(){
    //     $data = array();
    //     $q = "select kode,nama from ".$this->db->dbname.".sdm_5departemen where aktif ='1'";
    //     $r = $this->fetchdata($q);
    //     if(count($r) > 0){
    //         foreach($r as $k=>$v){
    //             $data[$v['kode']] = $v['nama'];
    //         }
    //     }
    //     return $data;
    // }
    // function del(){
    //     $data = array();
    //     $q = "select kode,nama from ".$this->db->dbname.".sdm_5departemen where aktif ='1'";
    //     $r = $this->fetchdata($q);
    //     if(count($r) > 0){
    //         foreach($r as $k=>$v){
    //             $data[$v['kode']] = $v['nama'];
    //         }
    //     }
    //     return $data;
    // }
    // function set(){
    //     $data = array();
    //     $q = "select kode,nama from ".$this->db->dbname.".sdm_5departemen where aktif ='1'";
    //     $r = $this->fetchdata($q);
    //     if(count($r) > 0){
    //         foreach($r as $k=>$v){
    //             $data[$v['kode']] = $v['nama'];
    //         }
    //     }
    //     return $data;
    // }



    
}
?>