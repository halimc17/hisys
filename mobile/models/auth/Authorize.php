<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Authorize extends OWL_Model{
    private function get_token($api_key=""){
        $data = false;
        if(!empty($_SESSION['standard']['api_key'])){
            $apiKey = $_SESSION['standard']['api_key'];
        }else{
            $apiKey = $api_key;
        }
        $q = "select id,explogin from ".$this->db->dbname.".api_key where key_api = '".$apiKey."' limit 1";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = (object)array_shift($r);
        }
        return $data;
    }
    //encryption key generator
    private function encryption_token($api_key=""){
        $this->load->lib("Encryption");
        $Encryption = $this->lib->Encryption;
        $result = false;
        // with MD5
        if($token = $this->get_token($api_key)){
            $result = $Encryption->encryptionProduct($token->id.$token->explogin);
        }
        return $result;
    }

    function token($api_key=""){
        $result = false;
        if($tokenRes = $this->encryption_token($api_key)){
            $result = $tokenRes;
        }
        return $result;
    }
    function check_token($api_key="",$token=""){
        $result = false;
        if($tokenRes = $this->token($api_key)){
            $result = ($tokenRes == $token)?true:false;
        }
        return $result;
    }
    function get_apikey(){
        $data = false;
        if(!empty($_SESSION['standard']['uuid'])){
            $uuid = $_SESSION['standard']['uuid'];
        }else{
            $uuid = $this->post('client_secret');
        }
        $client_id = $this->post('client_id');
        $username = $this->post('username');
        
        $q = "select * from ".$this->db->dbname.".api_key where uuid = '".$uuid."' and username = '".$username."'";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$dataApi){
                if (date("Y-m-d H:i:s", strtotime($dataApi['explogin'])) >= date("Y-m-d H:i:s")) {
                    $dataInsert = $dataApi;
                }else{
                    $dataApi[$k]['datelogin'] = date("Y-m-d H:i:s");
                    $dataApi[$k]['explogin'] = date("Y-m-d H:i:s", strtotime("+1 day"));
                    $dataApi[$k]['is_login'] = '1';
                    $res = $this->update($dataApi, 'api_key', "username='" . $dataApi['username']  . "' and key_api='" . $dataApi['key_api'] . "'");
                    $dataInsert = $dataApi;
                }
            }
            $data = array(
                'username' => $dataInsert['username'],
                'client_secret' => $dataInsert['uuid'],
                'appname' => $dataInsert['appname'],
                'versi' => $dataInsert['versi'],
                'api_key' => $dataInsert['key_api'],
                'datelogin' => $dataInsert['datelogin'],
                'explogin' => $dataInsert['explogin']
            );
        }else{
            $q = "select * from ".$this->db->dbname.".api_key where uuid = '".$uuid."' and username = '".$this->post('client_id')."' limit 1";
            $r = $this->fetchdata($q);
            if(count($r) > 0){
                foreach($r as $dataApi){
                    if (date("Y-m-d H:i:s", strtotime($dataApi['explogin'])) >= date("Y-m-d H:i:s")) {
                        $newApi_key = md5($username.$dataApi['key_api']);
                        $dataInsert = array(
                            'username' => $username,
                            'uuid' => $dataApi['key_api'],
                            'appname' => $dataApi['appname'],
                            'versi' => $dataApi['versi'],
                            'key_api' => $newApi_key,
                            'datelogin' => date("Y-m-d H:i:s"),
                            'explogin' => date("Y-m-d H:i:s", strtotime("+1 day")),
                            'is_login' => '1'
                        );
                        $q = "select * from ".$this->db->dbname.".api_key where uuid = '".$dataApi['key_api']."' and key_api = '".$newApi_key."' and username = '".$username."' limit 1";
                        $r = $this->fetchdata($q);
                        if(count($r) > 0){
                            $dataUpdate = array(
                                'datelogin' => date("Y-m-d H:i:s"),
                                'explogin' => date("Y-m-d H:i:s", strtotime("+1 day")),
                                'is_login' => '1'
                            );
                            $res = $this->update($dataUpdate, 'api_key', "uuid = '".$dataApi['key_api']."' and key_api='" . $newApi_key. "' and username = '".$username."' ");
                        }else{
                            $res = $this->insert($dataInsert, 'api_key');
                        }
                    }
                }
                $data = array(
                    'username' => $dataInsert['username'],
                    'client_secret' => $dataInsert['uuid'],
                    'appname' => $dataInsert['appname'],
                    'versi' => $dataInsert['versi'],
                    'api_key' => $dataInsert['key_api'],
                    'datelogin' => $dataInsert['datelogin'],
                    'explogin' => $dataInsert['explogin']
                );
            }
        }
        return $data;
    }
}
?>