<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class OWL_Controller {
	public $dbname;
	public $owlPDO;
	public $response = array();
	public $post;
	public $get;
	public $request;
	public $load;
	public $fetchData;
	public $func;
	public $user = array();
	
	private static $instance;

	public function __construct() {
		self::$instance =& $this;
		$this->appath = APPPATH;
		$this->load = load_class('OWL_Loader');
		$this->load->initialize();
		$this->load->database();
		$this->uri = load_class('URI');
		$this->base_path = $GLOBALS['base_path'];
		$this->sysConfig = $GLOBALS['sysConfig'];
		$this->user_agent = load_class('User_agent','Libraries');
		$this->load->database($this->load->getConfigDb());
		$this->dbname = $this->connection->dbname;
		$this->owlPDO = $this->connection->owlPDO;
		
		$this->response['status']  = 200;
		$this->response['error']   = FALSE;
		$this->response['message'] = NULL;
	}

	public static function &get_instance() {
		return self::$instance;
	}
	public static function getClass(){
		//Memanggil Class Name model
        return get_called_class();
    }
	public function antiinjection($data=""){
		return stripslashes(strip_tags(htmlspecialchars($data,ENT_QUOTES)));
	}
	function injetionDataSend($Var){
		if(is_array($Var)){
			foreach($Var as $k => $v){
				if(!is_array($v)){
					$Var[$k] = $this->antiinjection($v);
				}else{
					foreach($Var[$k] as $k1 => $v1){
						$Var[$k][$k1] = $this->antiinjection($v1);
					}
				}
			}
		}else{
			$Var = $this->antiinjection($Var);
		}
		return $Var;
	}
	public function get($obj){
			if(isset($obj)){
				if(isset($_GET[$obj])){
					$result = $this->injetionDataSend($_GET[$obj]);
				}else{
					$result = NULL;
				}
			}else{
				$result = $this->injetionDataSend($_GET);
			}
		return $result;
	}
	public function post($obj){
			$result = array();
			if(isset($obj)){
				if(isset($_POST[$obj])){
					$result = $this->injetionDataSend($_POST[$obj]);
				}else{
					$result = NULL;
				}
			}else{
				$result = $this->injetionDataSend($_POST);
			}

		return $result;
	}
	public function request($obj){
		$result = array();
			if(isset($obj)){
				if(isset($_REQUEST[$obj])){
					$result = $this->injetionDataSend($_REQUEST[$obj]);
				}else{
					$result = NULL;
				}
			}else{
				$result = $this->injetionDataSend($_REQUEST);
			}
		return $result;
	}
	function file($obj)
	{
		$result = array();
		if (isset($obj)) {
			if (isset($_POST[$obj])) {
				$_FILE[$obj]['name'] = $this->injetionDataSend($_FILE[$obj]['name']);
				$result = $_FILE[$obj];
			} else {
				$result = NULL;
			}
		} else {
			foreach ($_FILE as $obj => $file) {
				if(is_array($_FILE[$obj]['name'])){
					foreach ($_FILE[$obj]['name'] as $k => $v) {
						$_FILE[$obj]['name'][$k] = $this->injetionDataSend($v);
					}
				}else{
					$_FILE[$obj]['name'] = $this->injetionDataSend($_FILE[$obj]['name']);
				}
				
			}
			$result = $_FILE;
		}

		return $result;
	}
	function auth22($device = ''){
		$result = FALSE;
		if(ISSET($device) and $device == '' and ISSET($_SESSION['standard']['username'])){
			$result = TRUE;
		}elseif(ISSET($device) and $device == 'API'){
			if($this->get('api_key')){
				//on developing
				$result = TRUE;
			};
		}
		return $result;
	}
	public function sec_sys_api(){
		$this->load->model("Signin");
		$result = FALSE;
		if($authApi = $this->Signin->auth('API')){
			$result = $authApi;
		}else{
			//header('HTTP/1.0 203 Non-Authoritative, Your Session has Expired', true, 2031);
			$result = FALSE;
			$this->response = $this->Signin->response();
		}
		return $result;
	}
	public function sec_sys_serv(){
		$this->load->model("Signin");
		$result = FALSE;
		if($this->Signin->auth()){
			$result = TRUE;
		}else{
			$result = FALSE;
			//$result = $this->auth();
		}
		return $result;
	}
	function master_validation($masterPageFlag = true){
		$bb=array();
		$this->response['error']  = false;
		if(isset($this->post['par'])){
			$bb= explode("/",$this->post['par']);
			$this->response['error']  = false;
		}else if(isset($this->get['par'])){
			$bb= explode("/",$this->get['par']);
			$this->response['error']  = false;
		}
		$isMobilApp = false;
		if(isset($this->get['owlApp']) or isset($this->post['owlApp'])){
			$isMobilApp = true;
			$this->response['error']  = false;
		} 
		if($isMobilApp != true){
			if(count($bb)>2 and $bb[2]!=0){
				@session_destroy();
				if($masterPageFlag){
					//header("Location: login.html");
					$this->response['error']  = true;
				}else{
					if(isset($_POST)){
						//header('HTTP/1.1 307 Temporary Redirect');
						$this->response['status'] = 307;
					}else{
						$this->response['status'] = 4041;
					}
					$this->response['error']  = true;
					//header("Location: error/404.html?error=session&case=expired");
				}
				//exit();  
			}
			//unset the param par
			unset($_POST['par']);
			unset($_GET['par']);
		
			//check for liftime session allowed++++++++++++++++++++++
		
			if(!isset($_SESSION['DIE']) or time()>intval($_SESSION['DIE']))
			{
				//echo " [Gagal/Failed/Error], your session has expired, please press refresh button and login again..!";
				@session_destroy();
				if($masterPageFlag){
					$this->response['error']  = true;
					//header("Location: login.html");
				}else{
					if(isset($_POST)){
						//header('HTTP/1.1 307 Temporary Redirect');
						$this->response['status'] = 307;
					}else{
						$this->response['status'] = 4041;
					}
					$this->response['error']  = true;
					//header("Location: error/404.html?error=session&case=expired");
				}
				//exit();
			}else{
				$_SESSION['DIE']=time()+$_SESSION['MAXLIFETIME'];
				//++++++++++++++++++++++++++++++++++++++++++++++++++++
				if(isset($_SESSION['standard']['username']) AND isset($_SESSION['access_type'])){
					if(strlen($_SESSION['standard']['username'])>=6 AND ($_SESSION['access_type']=='level' OR $_SESSION['access_type']=='detail')){//Go on
						//print_r($_SESSION);
					}else{
						
						if($masterPageFlag){
							//header("Location: login.html");
							$this->response['error']  = true;
						}else{
							if(isset($_POST)){
								//header('HTTP/1.1 307 Temporary Redirect');
								$this->response['status'] = 307;
							}else{
								$this->response['status'] = 4042;
								//header("Location: error/404.html?error=cracker&case=auto");
							}
							$this->response['error']  = true;
						}
						//exit();
					}
				}else{
					if($_SESSION['security']=='on')
					{
		
						if($masterPageFlag){
							//header("Location: login.html");
							$this->response['error']  = true;
						}else{
							if(isset($_POST)){
								//header('HTTP/1.1 307 Temporary Redirect');
								$this->response['status'] = 307;
							}else{
								$this->response['status'] = 4043;
								//header("Location: error/404.html?error=authorized&case=wrong");
							}
							$this->response['error']  = true;
							
						}
						//exit();
					}
					else{
						//doing nothing. Just pass away
					}   
				}
				if(!isset($_SESSION['org']['holding'])){
					//echo " [Gagal/Failed/Error], your session has expired, please press refresh button and login again..!";
					#echo "<script>alert('Session expired. You'll be redirect to login page');location.reload(true)</script>";
					@session_destroy();
					if($masterPageFlag){
						//header("Location: login.html");
						$this->response['error']  = true;
					}else{
						if(isset($_POST)){
							//header('HTTP/1.1 307 Temporary Redirect');
							$this->response['status'] = 307;
						}else{
							$this->response['status'] = 4041;
						}
						$this->response['error']  = true;
					}
					//exit();
				}  
				/*
				$ini_array = parse_ini_file("lib/nangkoel.ini");
				$bStart=str_replace(".","",$ini_array['BACKUP_START']);
				$bEnd=str_replace(".","",$ini_array['BACKUP_END']);
				$now=date('Hi');
				if($now>$bStart and $now<$bEnd){
					echo " [Gagal/Failed/Error], Sorry, Server is on routine backup process,\n
					Please login after ".$ini_array['BACKUP_END'].", thank you"; 
					session_destroy();
					if($masterPageFlag){
						$this->response['error']  = true;
						//header("Location: login.html");
					}else{
						if(isset($_POST)){
							//header('HTTP/1.1 307 Temporary Redirect');
							$this->response['status'] = 307;
						}else{
							$this->response['status'] = 4044;
							//header("Location: error/404.html?error=serverbackup&case=".$ini_array['BACKUP_END']);
						}
						$this->response['error']  = true;
					}
					exit();       
				}*/
			}
		}else{
			//Validations for mobile or API
		}
		return $this->response;
	}
	function root($val=""){
		if(is_https()){
			$root="https://";
		}else{
			$root="http://";
		}
		//$root=(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'];
		$root.=$_SERVER['HTTP_HOST'];
		$root.= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
		if($val!=""){
			$root.=$val;
		}
		return $root;
	}
	public function base_url($val="",$base_path=""){
		if($base_path == ""){
			$base_path = $this->base_path;
		}
		return $this->root().$base_path."/".$val;
	}
	public function base_template($val="",$base_path=""){
		if($base_path == ""){
			if(isset($this->sysConfig['template'])){
				$base_path = $this->sysConfig['template'];
			}else{
				$base_path = 'primary';
			}
		}else{
			$base_path = 'primary';
		}
		return $this->base_url($val,'template/'.$base_path);
	}
	public function site_url($path=""){
		$index ="";
		if(isset($this->sysConfig['url_path'])){
			$index = $this->sysConfig['url_path'];
		}
		return $this->root($index).$path;
	}
	function redirect($path){
		header("Location: ".$this->site_url($path));
	}
	function loadview($path){
		if (file_exists($path.'view/'.$path.'.php')){
			require_once('view/'.$path.'.php');
		}
		
	}
	function query($str,$FETCHBY = 'OBJ'){
		$result = FALSE;
		$this->response['status']  = 200;
		$this->response['message'] = '';
		try{
			$res = $this->owlPDO->query($str);
			if($FETCHBY == 'ASSOC'){
				$res->setFetchMode(PDO::FETCH_ASSOC);
			}else{
				$res->setFetchMode(PDO::FETCH_OBJ);
			}
			$result = $res;
			$this->response['error']   = FALSE;
		}catch (PDOException $e){
			$this->response['error']   = TRUE;
			$this->response['message'] = $e->getMessage();
		}
		return $result;
	}
	public function fetchData($str){
		$sesult = array();
		$data = $this->query($str,'ASSOC');
		if($data and $data->rowCount() > 0){
			while($bar=$data->fetch()){
				foreach($bar as $idx=>$val)$bar[$idx] = htmlspecialchars_decode($val);
				$sesult[] = $bar;
			}
		}
		return $sesult;
	}
	public function print_r($var){
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}
	private function checkDbname($tableName){
		if(strpos($tableName, $this->dbname) === false and $tableName != ""){
			$tableName = $this->dbname.".".$tableName;
		}
		return $tableName;
	}
	function query_insert($insData=array(),$tableName=""){
		$tableName = $this->checkDbname($tableName);
		$query= FALSE;
		if(count($insData) > 0 and $tableName != ""){
			$columns = implode(", ",array_keys($insData));
			foreach ($insData as $idx=>$data) $insData[$idx] = "'".$data."'";
			$values = implode(", ", $insData);
			$query = "INSERT INTO {$tableName} ({$columns}) VALUES ({$values})";
		}
		return $query;
	}
	function insert($insData=array(),$tableName=""){
		$tableName = $this->checkDbname($tableName);
		$query = $this->query_insert($insData,$tableName);
		$result = false;
		if($query){
			try{
				$this->owlPDO->query($query);
				$result = $this->owlPDO;
				$this->response['error']   = FALSE;
				$this->response['message'] = "";
			}catch(PDOException $e){
				$result = false;
				$this->response['error']   = TRUE;
				$this->response['message'] = $e->getMessage();
			}
		}
		return $result;
	}
	function query_update($insData=array(),$tableName="",$where=""){
		$tableName = $this->checkDbname($tableName);
		$query= FALSE;
		if(count($insData) > 0 and $tableName != ""){
			foreach ($insData as $idx=>$data) $insData[$idx] = $idx."='".$data."'";
			$values = implode(", ", $insData);
			if(trim($where) != ""){
				$where = "WHERE ".$where;
			}
			$query = "UPDATE {$tableName} SET {$values} {$where}";
		}
		return $query;
	}
	function update($insData=array(),$tableName="",$where=""){
		$tableName = $this->checkDbname($tableName);
		$query = $this->query_update($insData,$tableName,$where);

		$result = false;
		if($query){
			try{
				$this->owlPDO->query($query);
				$result = $this->owlPDO;
				$this->response['error']   = FALSE;
				$this->response['message'] = "";
			}catch(PDOException $e){
				$result = false;
				$this->response['error']   = TRUE;
				$this->response['message'] = $e->getMessage();
			}
		}
		return $result;
	}
	function query_delete($tableName="",$where=""){
		$tableName = $this->checkDbname($tableName);
		$query= FALSE;
		if($tableName != ""){
			if(trim($where) != ""){
				$where = "WHERE ".$where;
			}
			$query = "DELETE FROM {$tableName} {$where}";
		}
		return $query;
	}
	function delete($tableName="",$where=""){
		$tableName = $this->checkDbname($tableName);
		$query = $this->query_delete($tableName,$where);
		$result = false;
		if($query){
			try{
				$this->owlPDO->query($query);
				$result = $this->owlPDO;
				$this->response['error']   = FALSE;
				$this->response['message'] = "";
			}catch(PDOException $e){
				$result = false;
				$this->response['error']   = TRUE;
				$this->response['message'] = $e->getMessage();
			}
		}
		return $result;
	}
	function exec($query){
		$this->response['status']  = 200;
		$this->response['message'] = '';
		$result= FALSE;
		try{
			$this->owlPDO->beginTransaction();
			$this->owlPDO->exec($query);
			$this->owlPDO->commit();
			$result = $this->owlPDO;
			$this->response['error']   = FALSE;
			$this->response['message'] = "";
		}catch (PDOException $e){
			$this->owlPDO->rollback();
			$this->response['error']   = TRUE;
			$this->response['message'] = $e->getMessage();
		}
		return $result;
	}
	public function getProtocol($apikey, $user = ''){
		if ($apikey == '') {
			$this->response['status']  = 500;
			$this->response['error']   = true;
			$this->response['message'] = 'API KEY Cannot Be Null';

			return false;
		}

		$str = "SELECT a.key FROM api_key AS a WHERE a.key = '". $apikey ."'";
		$res = $this->fetchData($str);
		$row = count($res);

		if ($row < 1) {
			$this->response['status']  = 500;
			$this->response['error']   = true;
			$this->response['message'] = 'API KEY Is Not Valid';

			return false;
		}

		return true;
	}

	public function result($array){
		$array['size'] = $this->formatBytes(strlen(json_encode($array['data'])));

		echo json_encode($array);
	}
	public function toAtrr($arr=array()){
		$result = "";
		if(is_array($arr) and count($arr)>0){
			$arrx=array();
			//foreach ($arr as $idx=>$data) $arr[$idx] = '"'.$($data).'"';
			foreach($arr as $k=>$v){
				$arrx[] = trim($k)."=\"".trim($v)."\"";
			}
			$result = implode(" ",$arrx);
		}
		return $result;
	}
	public function formatBytes($size, $precision = 2) {
	    $base 		= log($size, 1024);
	    $suffixes 	= array('B', 'KB', 'MB', 'GB', 'TB');   

	    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
	}
}