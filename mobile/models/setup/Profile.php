<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Profile extends OWL_Model{
    function change_password($old_password="",$new_password="",$rep_password=""){
        $result = false;
        if($this->check_password($old_password,$new_password,$rep_password)){
            $id = $_SESSION['standard']['username'];
            $q = "UPDATE ".$this->db->dbname.".user SET password = PASSWORD('".$new_password."') where namauser='{$id}'";
            $this->exec($q);
            $result = true;
            if($this->response['error']){
                $result = false;
                $this->response['message'] = "Gagal Update!";
            }
        }else{
            $result = false;
        }
        return $result;
    }
    function check_password($old_password="",$new_password,$rep_password){
        $result = false;
        if($new_password == $rep_password){
            $q = "select namauser from ".$this->db->dbname.".user where password = PASSWORD('".$old_password."')";
            if(count($this->fetchdata($q)) > 0){
                $result = true;
            }else{
                $this->response['error'] = true;
                $this->response['message'] = "Passwords Don't Match";
                $result = false;
            }
        }else{
            $this->response['error'] = true;
            $this->response['message'] = "Passwords Don't Match";
            $result = false;
        }
        return $result;
    }




    
}
?>