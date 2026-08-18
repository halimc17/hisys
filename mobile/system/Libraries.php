<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class DBWAN {
	public function __construct(){
		$this->initialize();
	}
    function initialize(){
		if (file_exists(BASEPATH."..".DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."autoload.php")){
			require_once BASEPATH."..".DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."autoload.php";
		}
		return $this;
	}
    function create(){
        // OWL\SQLBuilder
		return new OWL\SQLBuilder\DB;
    }
}
?>

