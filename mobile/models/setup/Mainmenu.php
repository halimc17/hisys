<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Mainmenu extends OWL_Model{
    function selectdata($where=""){
        if($_SESSION['language']=='IN'){
            $cell="caption";
        }else if($_SESSION['language']=='EN'){
            $cell="caption2";
        }else{
            $cell="caption3";
        }
        $data = array();
        $q = "select id, type, pathicon, class, {$cell} as caption, action, access_level, parent, urut, hide, lastupdate, lastuser from ".$this->db->dbname.".menu {$where}";
        $data = $this->fetchdata($q);
        return $data;
    }
    function selectByPrivilege(){
        $data = array();
        if(!empty($_SESSION['allpriv'])){
            $idMenu = implode("','",$_SESSION['allpriv']);
            $where = "where hide=0 and menuid in ('".$idMenu."') order by parent,urut";
            $menu = $this->selectdata($where);
            if(count($menu) > 0){
                foreach($menu as $v){
                    $data[$v['id']] = $v;
                }
                $data = $this->susunMenu($data);
            }
        }
        return $data;
    }
    function auth($where=""){
        $data = false;
        $q = "select * from ".$this->db->dbname.".auth {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
        return $data;
    }
    function tipeakses($where=""){
        $data = false;
        $q = "select * from ".$this->db->dbname.".tipeakses {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }
        return $data;
    }
    function susunMenu($dataArr){
        $dataparent = $dataArr;
        foreach($dataparent as $id=>$v){
            if($v['parent'] != '0'){
                if(isset($dataArr[$v['parent']])){
                    $dataArr[$v['parent']]['child'][] = $dataArr[$v['id']];
                    unset($dataArr[$v['id']]);
                }
            }
            
        }
        return $dataArr;
    }
}
?>