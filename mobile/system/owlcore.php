<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
require_once(BASEPATH.'Common.php');

$errorArr = include(BASEPATH."Error_auth.php");
/*
 * ------------------------------------------------------
 *  Instantiate the URI class
 * ------------------------------------------------------
 */
$URI =& load_class('URI');

/*
 * ------------------------------------------------------
 *  Instantiate the output class
 * ------------------------------------------------------
 */
//$OUT =& load_class('Output');

// ------
require_once BASEPATH.'OWL_Controller.php';


function &get_instance()
	{
		return OWL_Controller::get_instance();
	}
function set_class($class)
{
	$this->class = str_replace(array('/', '.'), '', $class);
}

function set_routeFile(){
	$URI = load_class('URI');
	$class = "";
	$method = "index";
	if(isset($URI->segments[1])){
		$class = ucfirst($URI->segments[1]);
	}
	if(isset($URI->segments[2])){
		$method = $URI->segments[2];
	}
	$http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
	$URI->rsegments = array(
		1 => $class,
		2 => $method
	);
	
	$e404 = FALSE;
	$fileName ="";
	if (file_exists(APPPATH.strtolower($URI->rsegments[1]).'.php')){
		$fileName = strtolower($URI->rsegments[1]);
	}else{
		$fileName = $URI->rsegments[1];
	}
	
	if(!file_exists(APPPATH.$fileName.'.php')){
		$e404 = TRUE;
		require_once(APPPATH.'index.php');
		load_class('index');
	}else{
		
		if (class_exists($class, FALSE) === FALSE)
		{
			require_once(APPPATH.$fileName.'.php');
		}
		if($method != ""){
			$fileOwlClass = new $class();
			@call_user_func_array(array(&$fileOwlClass, $method), $params=array()); 
			
		}
	}
	
}
//$OUT->_display();
set_routeFile();?>