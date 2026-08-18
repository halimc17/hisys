<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Index extends OWL_Controller{
	public function __construct(){
		parent::__construct();
		include(APPPATH.'../error/403.html');
	}
}
?>