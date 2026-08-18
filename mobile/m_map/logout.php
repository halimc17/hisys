<?php 
session_start();
ini_get('session.save_path');

defined('BASEPATH') OR exit('No direct script access allowed');
class Logout extends OWL_Controller{
	public function __construct(){
		parent::__construct();
		//get fungsi :12355
	}
    function index(){
      try{
         $uodateUser = "update ".$this->db->dbname.".user set logged=0 where namauser='".$_SESSION['standard']['username']."'";
         $stmt=$this->exec($uodateUser);
        //  $stmt->execute();
        //  $count = $stmt->rowCount();
         session_destroy();   
         $this->redirect('login');
       }catch (Exception $e){
        echo $e->message;
         echo "Can't to Sign Out";
      }
    }
    function expired_session(){
        $this->redirect('logout');
     }
}


?>
