<?php
header('Access-Control-Allow-Origin: *');
if($_SERVER['SERVER_NAME'] == '62.72.29.201'){
	define('ENVIRONMENT', 'production');
}elseif($_SERVER['HTTP_HOST'] == '182.23.67.40'){
	define('ENVIRONMENT', 'testing');
}else{
	define('ENVIRONMENT', 'development');
}
switch (ENVIRONMENT){
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;
	case 'testing':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
		// error_reporting(-1);
		// ini_set('display_errors', 1);
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}
	$active_group = "default";
	if(file_exists("config/setup.php")){
		require 'config/setup.php';
		if(!empty($sysConfig)){
			$active_group = $sysConfig['application_folder'];
		}else{
			header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
			exit("Error: sysConfig Undefined.");
		}
	}else{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		exit("Error: Setup App Undefined.");
	}
	$system_path = $sysConfig['system_path'];
	$application_folder = "m_".$sysConfig['application_folder'];
	$application_name = $sysConfig['application_name'];
	$base_path = $application_folder;

	date_default_timezone_set($sysConfig['timezone']);
	define('APP_NAME', $sysConfig['application_name']);
	define('VERSION', $sysConfig['VERSION']);
	define('PRODUCT_KEY', $sysConfig['base_path']);
	define('DEV_DATE', $sysConfig['DEV_DATE']);
	define('QA_DATE', $sysConfig['QA_DATE']);
	define('LIVE_DATE', $sysConfig['LIVE_DATE']);
	define('EXPIRED_DATE', $sysConfig['EXPIRED_DATE']);
	
	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}
	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}
	
	// The name of THIS file "api_dev.php"
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to the system directory "D:\xampp\htdocs\projects\api_dev\api_system\"
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory "D:\xampp\htdocs\projects\"
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	// Name of the "system" directory "api_system"
	define('SYSDIR', basename(BASEPATH));
	
	if (is_dir($application_folder)){
		if(($_temp = realpath($application_folder)) !== FALSE){
			$application_folder = $_temp;
		}else{
			$application_folder = strtr(
				rtrim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}elseif(is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR)){
		$application_folder = BASEPATH.strtr(
			trim($application_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}else{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}
	define('VIEWPATH', $application_folder.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR);
	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);
	define('MODPATH', FCPATH.'models'.DIRECTORY_SEPARATOR);
	define('FUNCTPATH', $application_folder.DIRECTORY_SEPARATOR.'functions'.DIRECTORY_SEPARATOR);
	define('APPINDEX', $application_folder.DIRECTORY_SEPARATOR.SELF);
	define('SELFPATH', $_SERVER['PHP_SELF']);
	
	session_name($application_name);
	require_once BASEPATH.'owlcore.php';
?>