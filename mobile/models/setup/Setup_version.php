<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_version extends OWL_Model
{
    function getData()
    {
        $data = array();
        // echo $user;

        $appname = $this->post('appname');
        if(!isset($appname) || empty($appname)){
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : App Name tidak terdaftar.";
            return $this->response;

        }

        $q = "select * from " . $this->db->dbname . ".data_version where nameapp = '".$appname."' order by updatetime DESC limit 1";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) == 0) {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Version App Not Found";
            return $this->response;
        }
        $data ['app_version']= $r[0]['appversion_name'];
        $data ['build_number']= $r[0]['appversion'];
        $data ['app_name']= $r[0]['nameapp'];
        $data ['url']= $r[0]['urlapp'];
        $data ['desc']= $r[0]['updatelog'];
        return $data;
    }

    function appfinger()
    {
        
        $q = "select * from " . $this->db->dbname . ".data_version where nameapp = 'com.owl.fingerprint' order by updatetime DESC limit 1";
        $r = $this->fetchdata($q);
        if (count($r) == 0) {
            $this->response['status'] = 404;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : Version App Not Found";
            return $this->response;
        }
        $data ['appversion_name']= $r[0]['appversion_name'];
        $data ['appversion']= $r[0]['appversion'];
        $data ['nameapp']= $r[0]['nameapp'];
        $data ['urlapp']= $r[0]['urlapp'];
        $data ['updatelog']= $r[0]['updatelog'];
        $data ['updatetime']= $r[0]['updatetime'];
        return $data;
    }
}
