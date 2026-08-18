<?
	ini_set('display_errors', 1);
	error_reporting(1);
	
	defined('BASEPATH') or exit('No direct script access allowed');

	class OWL_Model {
		public $rowCount;
		public $rowStart;
		public $rowLimit;
		public $get_config;
		public $response = array();
		public $user = array();
		function __construct()
		{
			$this->response['status']  = 200;
			$this->response['error']   = FALSE;
			$this->response['message'] = NULL;
			$this->response['console'] = NULL;
			$this->rowCount = 0;
			$this->rowStart = 0;
			$this->rowLimit = 0;
			$this->load = load_class('OWL_Loader');
			$this->base_path = $GLOBALS['base_path'];
			$this->connection_mod();
		}
		public function __get($key){
			$class = $this->getClass();
			if ($key == 'db' and !empty($this->clientDb[$class]) and $this->clientDb[$class]->dbserver.$this->clientDb[$class]->username == $this->connection->dbserver.$this->connection->username){
				get_instance()->$key = $this->clientDb[$class];
			} elseif ($key == 'db') {
				$this->connection_mod($this->getClass());
			}
			return get_instance()->$key;
		}
		public static function getClass()
		{
			//Memanggil Class Name model
			return get_called_class();
		}
		private function get_config($model)
		{
			$db = null;
			$reflector = new \ReflectionClass($model);
			$parentFile = explode(DIRECTORY_SEPARATOR, dirname($reflector->getFileName(), 1));
			if (count($parentFile) > 0) {
				$parentFile = end($parentFile);
				$db = $this->load->getConfigDb($parentFile);
			} else {
				header('HTTP/1.1 503 Config Database Unavailable.', TRUE, 503);
				exit(1);
			}

			return $db;
		}
		private function connection_mod($class = "")
		{
			
			if ($class == "") {
				$class = $this->getClass();
			}
			if (!empty($class) and empty($this->clientDb[$class])) {
				$_owl_CI = &get_instance();
				$_owl_CI->clientDb[$class] = $this->get_config($class);
			}
			
			if (!empty($this->clientDb[$class]) and $this->clientDb[$class]->dbname != $this->connection->dbname) {
				$this->load->database($this->clientDb[$class]);
			}else{
				
			}
		}
		private function antiinjection($data)
		{
			$conTest = mysqli_connect($this->connection->dbserver, $this->connection->username, $this->connection->password, $this->connection->dbname);
			$filter_sql = mysqli_real_escape_string($conTest, stripslashes(strip_tags(htmlspecialchars($data, ENT_QUOTES))));
			return $filter_sql;
		}
		private function injetionDataSend($Var)
		{
			if (is_array($Var)) {
				foreach ($Var as $k => $v) {
					if (!is_array($v)) {
						$Var[$k] = $this->antiinjection($v);
					} else {
						foreach ($Var[$k] as $k1 => $v1) {
							$Var[$k][$k1] = $this->antiinjection($v1);
						}
					}
				}
			} else {
				$Var = $this->antiinjection($Var);
			}
			return $Var;
		}
		function model($model)
		{
			$this->load->model($model);
			return $this->$model;
		}

		function table_exists($table) {
			$res = $this->connection->owlPDO->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '".$this->db->dbname."' AND table_name = '".$table."'");
			if ($res->rowCount() == 1) {
				$result = TRUE;
			} else {
				$result = FALSE;
			}

			return $result;
		}

		function get($obj)
		{
			if (isset($obj)) {
				if (isset($_GET[$obj])) {
					$result = $this->injetionDataSend($_GET[$obj]);
				} else {
					$result = NULL;
				}
			} else {
				$result = $this->injetionDataSend($_GET);
			}
			return $result;
		}
		function post($obj)
		{
			$result = array();
			if (isset($obj)) {
				if (isset($_POST[$obj])) {
					$result = $this->injetionDataSend($_POST[$obj]);
				} else {
					$result = NULL;
				}
			} else {
				$result = $this->injetionDataSend($_POST);
			}

			return $result;
		}
		function request($obj)
		{
			$result = array();
			if (isset($obj)) {
				if (isset($_REQUEST[$obj])) {
					$result = $this->injetionDataSend($_REQUEST[$obj]);
				} else {
					$result = NULL;
				}
			} else {
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
		function root($val = "")
		{
			if (is_https()) {
				$root = "https://";
			} else {
				$root = "http://";
			}
			//$root=(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'];
			$root .= $_SERVER['HTTP_HOST'];
			$root .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
			if ($val != "") {
				$root .= $val;
			}
			return $root;
		}
		public function base_url($val = "", $base_path = "")
		{
			if ($base_path == "") {
				$base_path = $this->base_path;
			}
			return $this->root() . $base_path . "/" . $val;
		}
		function query($str, $FETCHBY = 'OBJ')
		{
			$result = FALSE;
			$this->response['status']  = 200;
			$this->response['message'] = '';
			try {
				$res = $this->connection->owlPDO->query($str);
				if ($FETCHBY == 'ASSOC') {
					$res->setFetchMode(PDO::FETCH_ASSOC);
				} else {
					$res->setFetchMode(PDO::FETCH_OBJ);
				}
				$result = $res;
				$this->response['error']   = FALSE;
			} catch (PDOException $e) {
				$this->response['error']   = TRUE;
				$this->response['message'] = $e->getMessage();
			}
			return $result;
		}
		function fetch($data)
		{
			$sesult = array();
			if ($data and $data->rowCount() > 0) {
				while ($bar = $data->fetch()) {

					foreach ($bar as $idx => $val) {
						if (!is_object($bar)) {
							$bar[$idx] = htmlspecialchars_decode($val);
						} else {
							$bar->$idx = htmlspecialchars_decode($val);
						}
					}
					$sesult[] = $bar;
				}
			}
			return $sesult;
		}
		function fetchData($str)
		{
			$sesult = array();
			$data = $this->query($str, 'ASSOC');
			if ($data and $data->rowCount() > 0) {
				while ($bar = $data->fetch()) {
					foreach ($bar as $idx => $val) $bar[$idx] = htmlspecialchars_decode($val);
					$sesult[] = $bar;
				}
			}
			return $sesult;
		}
		function checkDbname($tableName)
		{
			// $this->connection_mod();
			// if($tableName != ""){
			// 	if(strpos($tableName,".")){
			// 		if(!strpos($tableName,$this->connection->dbname)){
			// 			$tableName = explode(".",$tableName)[1];
			// 		}
			// 	}
			// 	$tableName = $this->connection->dbname.".".$tableName;
			// }
			return $tableName;
		}
		function query_insert($insData = array(), $tableName = "")
		{
			// $this->connection_mod();
			$tableName = $this->checkDbname($tableName);
			$query = FALSE;
			if (count($insData) > 0 and $tableName != "") {
				$columns = implode(", ", array_keys($insData));
				foreach ($insData as $idx => $data) $insData[$idx] = "'" . $data . "'";
				$values = implode(", ", $insData);
				$query = "INSERT INTO {$tableName} ({$columns}) VALUES ({$values})";
			}
			return $query;
		}
		function insert($insData = array(), $tableName = "")
		{
			// $this->connection_mod();
			$tableName = $this->checkDbname($tableName);
			$query = $this->query_insert($insData, $tableName);
			// echo $query;
			$result = false;
			if ($query) {
				$result = $this->exec($query);
				// echo json_encode($result);
			}
			return $result;
		}
		function update($insData = array(), $tableName = "", $where = "")
		{
			// $this->connection_mod();
			$tableName = $this->checkDbname($tableName);
			$query = $this->query_update($insData, $tableName, $where);
			// echo $query;
			$result = false;
			if ($query) {
				$result = $this->exec($query);
			}
			return $result;
		}
		function query_update($insData = array(), $tableName = "", $where = "")
		{
			// $this->connection_mod();
			$tableName = $this->checkDbname($tableName);
			$query = FALSE;
			if (count($insData) > 0 and $tableName != "") {
				foreach ($insData as $idx => $data) $insData[$idx] = $idx . "='" . $data . "'";
				$values = implode(", ", $insData);
				if (trim($where) != "") {
					$where = "WHERE " . $where;
				}
				$query = "UPDATE {$tableName} SET {$values} {$where}";
			}
			return $query;
		}
		function query_delete($tableName = "", $where = "")
		{
			// $this->connection_mod();
			$tableName = $this->checkDbname($tableName);
			$query = FALSE;
			if ($tableName != "") {
				if (trim($where) != "") {
					$where = "WHERE " . $where;
				}
				$query = "DELETE FROM {$tableName} {$where}";
			}
			return $query;
		}
		function delete($tableName = "", $where = "")
		{
			// $this->connection_mod();
			$tableName = $this->checkDbname($tableName);
			$query = $this->query_delete($tableName, $where);
			$result = false;
			if ($query) {
				$result = $this->exec($query);
			}
			return $result;
		}
		function exec($query)
		{
			$this->response['status']  = 200;
			$this->response['message'] = '';
			$result = FALSE;
			if (!is_array($query)) {
				$this->connection->owlPDO->prepare($query);
			}
			try {
				$this->connection->owlPDO->beginTransaction();
				if (is_array($query)) {
					foreach ($query as $lq) {
						$this->connection->owlPDO->exec($lq);
					}
				} else {
					$this->connection->owlPDO->exec($query);
				}
				$this->connection->owlPDO->commit();
				$this->response['error']   = FALSE;
				$result = $this->connection->owlPDO;
				$this->response['message'] = "";
			} catch (PDOException $e) {
				$this->connection->owlPDO->rollback();
				$this->response['status']  = 400;
				$this->response['error']   = TRUE;
				$this->response['message'] = $e->getMessage();
			}
			
			return $result;
		}

		function create_table($dbname, $table, $column){
			$dataColumn = []; $PrimaryKey = []; $INDEXES = []; $COLLATION = [];
			foreach ($column as $k => $v) {
				if (!in_array($k, ["Indexes", "COLLATION"])) {
					$dataColumn[] = "`".$k."` ".$v;
					if (strpos($v, "Auto Incremen")) {
						$PrimaryKey[]= $k;
					}
				} else if ($k == "Indexes" and !empty($v)){
					$INDEXES = $v;
				} else if ($k == "COLLATION" and !empty($v)){
					$COLLATION = $v;
				}
			};

			$TABLE_Indexes = [];
			foreach ($INDEXES as $k => $v) {
				if ($k == "PRIMARY") {
					if (count($PrimaryKey) > 0) {
						foreach ($PrimaryKey as $columName) {
							if (!in_array($columName, $v)) {
								$v[] = $columName;
							}
						}
					}
				}

				if ($k == "INDEX") {
					$k = "KEY `".implode("_",$v)."`";
				} else {
					$k = $k." KEY";
				}

				$TABLE_Indexes[] = $k." (`".implode("`,`", $v)."`)";
			};

			if (count($TABLE_Indexes) > 0) {
				$dataColumn = array_merge($dataColumn, $TABLE_Indexes);
			}

			$query = "CREATE TABLE ".$dbname.".`".$table."` (".implode(",", $dataColumn).")";
			$TABLE_COLLATION = [];
			if (!empty($COLLATION)) {
				foreach ($COLLATION as $k => $v) {
					$TABLE_COLLATION[] = $k."=".$v;
				}

				if (!empty($TABLE_COLLATION)) {
					$query .= " ".implode(" ", $TABLE_COLLATION);
				}
			}

			if (!$return = $this->exec($query)) {
				echo $query."</br>";

				echo $this->response['message'];
				
				exit();
			}
		}

		function column_table($column) {
			$dataColumn = [];
			foreach ($column as $k => $v) {
				if (!in_array($k, ["Indexes", "COLLATION"])) {
					$dataColumn[$k] = $v;
				}
			}

			return $dataColumn;
		}

		function alter_table($dbname, $table, $column) {
			$dataColumn = $this->column_table($column);
			if (!empty($readyColumn = $this->column_exists($table))) {
				$missColumn = array();
				foreach ($dataColumn as $name => $type) {
					if (!in_array(strtolower($name), array_map('strtolower', array_column($readyColumn, 'Field')))) {
						if (!$retun = $this->exec("ALTER TABLE ".$dbname.".`".$table."` ADD `".$name."` ".$type.";")){
							echo "ALTER TABLE ".$dbname.".`".$table."` ADD `".$name."` ".$type.";"."<br>";
							
							echo $this->response['message'];
							
							exit();
						}
					}
				}
			}
    }

		function column_exists($table) {
			if ($res = $this->connection->owlPDO->query("SHOW COLUMNS FROM ".$this->connection->dbname.".".$table.";")) {
				if ($res->rowCount() > 0) {
					$result = array();
					while ($d = $res->fetch()) {
						$result[] = (array) $d;
					}

					return $result;
				} else {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		}

		function select($select = "*") {
			return $select;
		}
	}
