<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Privilegemap extends OWL_Model{
   function list_admin(){
        $data = false;
        $q = "select * from ".$this->db->dbname.".admin_list where username = '".$_SESSION['standard']['username']."' and karyawanid = '".$_SESSION['standard']['userid']."' limit 1";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = true;
        }
        return $data;
    }
    function imAdmin($user = array()){
        $data = false;
        if(!empty($user)){
            $dataUser = $user;
        }else{
            $dataUser['username'] = $_SESSION['standard']['username'];
            $dataUser['userid'] = $_SESSION['standard']['userid'];
        }
        $result = $this->getAdmin($dataUser);
        $data = $result['is_admin'];

        return $data;
    }
    function getAdmin($user = array()){
        $data['is_admin'] = false;
        $data['is_superadmin'] = false;
        $q = "select * from ".$this->db->dbname.".admin_list where username = '".$user['username']."' and karyawanid = '".$user['userid']."' limit 1";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data['is_admin'] = true;
            $data['is_superadmin'] = false;
            $r = array_shift($r);
            if($r['supperuser'] == '1'){
                $data['is_superadmin'] = true;
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
        }else{
            // Jika tidak ada di database Auth ke database Applications
            $Menumap = $this->model('Menumap');
            $data = $Menumap->auth($where);
        }
        return $data;
    }
    function tipeakses(){
        $data = false;
        $q = "select * from ".$this->db->dbname.".tipeakses where status=1";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r;
        }else{
            // Jika tidak ada di database Auth ke database Applications
            $Menumap = $this->model('Menumap');
            $data = $Menumap->tipeakses('where status=1');
        }
        return $data;
    }
    function is_admin($karyawanid){
        $result = false;
        // print_r($_SESSION);
        if(isset($_SESSION['admin']) and count($_SESSION['admin'])>0){
            $listAdmin = array_column($_SESSION['admin'],'karyawanid');
            if(in_array($karyawanid,$listAdmin)){
                $result = true;
            }

        }
        return $result;
    }
    function orgdetail($username=""){
        $data = false;
        if(!empty($username)){
            $q = "select kodeorganisasi from ".$this->db->dbname.".user_orgdetail where namauser='".$username."'";
            $r = $this->fetchdata($q);
            if(count($r) > 0){
                $data = array_column($r,'kodeorganisasi');
            }
        }
        return $data;
    }
}
?>