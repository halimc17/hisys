<?php 
session_start();
ini_get('session.save_path');

defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends OWL_Controller{
	public function __construct(){
		parent::__construct();
		//get fungsi :12355
        $this->load->model('Signin','login');
	}
    function index(){
       include(VIEWPATH.'login.php');
       //$this->load->view('login',array(),TRUE);
    }
    function process(){
        $this->response['error'] = TRUE;
        $this->response['message'] = "";
        try{
            $dataAuth = $this->login->auth('API','LOGIN');
            if($dataAuth){
                $res = $this->login->session($dataAuth);
                if($res){
                    $this->response['error'] = FALSE;
                    $_SESSION = $res;
                    // $_SESSION['token'] = $this->login->get_token();
                }else{
                    session_destroy();
                    $this->response['error'] = TRUE;
                    $this->response['message'] =  "<font color=#AA3322 style='background-color:#FFFFFF'>Wrong username and/or password</font><br><span   style='background-color:#FFFFFF'>Att: This uses case-sensitif</span>";
               
                }
            }else{
                session_destroy();
                $this->response['error'] = TRUE;
                $this->response['message'] =  "<font color=#AA3322 style='background-color:#FFFFFF'>Wrong username and/or password</font><br><span   style='background-color:#FFFFFF'>Att: This uses case-sensitif</span>";
           
            }
        }catch (PDOException $e) {
            session_destroy();
            $this->response['error'] = TRUE;
            $this->response['message'] = $e;
        }

       echo json_encode($this->response);
    }

    function logout(){
        echo "PROSESS logout";
    }


    function loginmobile(){
        echo "PROSESS logout";
    }
}
?>