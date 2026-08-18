<?php 
session_cache_expire(7200);//25 minutes cache keep by browser 7200 (1 day)
//throw new Error('Sorry, You entering the system like cracker');
session_start();
defined('BASEPATH') OR exit('No direct script access allowed');
class Index extends OWL_Controller{
	public function __construct(){
		parent::__construct();
		if(!$this->sec_sys_serv()){
			$this->blockcase();
		}else{
			$this->opencase();
		}
	}
	function index(){ }
	public function opencase(){
		$masterPageFlag = true;
		$this->redirect('master');
	}
	public function blockcase(){
		$this->redirect('login');
	}
}
?>