<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Mpdf {
	public function __construct(){
		$this->initialize();
	}
    function initialize(){
		if (file_exists(APPPATH."mpdf/composer/autoload_real.php")){
			require_once APPPATH."mpdf/composer/autoload_real.php";
		}
        ComposerAutoloaderInit60a6e8f90d963c6d1629166fe124ab57::getLoader();
		return $this;
	}
	function create(){
		return new \Mpdf\Mpdf(['tempDir' => APPPATH.'cache/pdf']);
	}
}
?>

