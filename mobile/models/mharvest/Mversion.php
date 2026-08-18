<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mversion extends OWL_Model
{
    function getData($user = array()){
        $data = array();
        $appname = $this->post('appname');
        $appid = $this->post('appid');
        if(!isset($appname) || empty($appname)){
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : App Name tidak terdaftar.";
            return $this->response;

        }
        if($appid_except = $this->getData_except($appname,$user)){
            $appid = $appid_except;
        }
        
        $q = "select * from " . $this->db->dbname . ".data_version where appid = '".$appid."' and nameapp = '".$appname."' order by updatetime DESC limit 1";
        $r = $this->fetchdata($q);
        if (count($r) == 0) {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Version App Not Found";
            return $this->response;
        }
        $data ['app_version']= $r[0]['appversion_name'];
        $data ['build_number']= $r[0]['appversion'];
        $data ['appid']= $appid;
        $data ['app_name']= $r[0]['nameapp'];
        $data ['url']= $r[0]['urlapp'];
        $data ['desc']= $r[0]['updatelog'];
        $data ['kodeorg']= $user['lokasitugas'];
        return $data;
    }
    function getData_except($appname,$user){
        $data = false;
        if(!empty($user['lokasitugas'])){
            $q = "select appid from " . $this->db->dbname . ".data_version_except where kodeorg = '".$user['lokasitugas']."' and nameapp = '".$appname."' and isactive = '1' order by updatetime DESC limit 1";
            $r = $this->fetchdata($q);
            if (count($r) > 0) {
                $limitData = array_shift($r);
                $data = $limitData['appid'];
            }
        }
        
        return $data;
    }
}

