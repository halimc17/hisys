<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Log_secret extends OWL_Controller{
    public $client_id;
    public $client_secret;
	public function __construct(){
		parent::__construct();
	}
    function index($data){
        $this->client_id = $data['client_id'] ?? '';
        $this->client_secret = $data['client_secret'] ?? '';
        include(VIEWPATH.'log_secret.php');
    }
    function error(){
        include(VIEWPATH.'log_error.php');
    }
    function process($appProfile){
        $this->client_id = $appProfile['client_id'] ?? '';
        $this->client_secret = $appProfile['client_secret'] ?? '';
        $this->api_key = $appProfile['api_key'] ?? '';
        $this->token = $appProfile['token'] ?? '';
        $this->username = $appProfile['username'] ?? '';
        $this->appName = $appProfile['app_name'] ?? '';
        $this->appPath = $appProfile['app_path'] ?? '';
    }
}
?>