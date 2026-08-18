<?php defined('BASEPATH') OR exit('No direct script access allowed');
class OWL_Loader {
    protected $_owl_view_paths =	array(VIEWPATH	=> TRUE);
    protected $_owl_model_paths =	array(MODPATH);
	protected $_owl_funct_paths =	array(FUNCTPATH);
	protected $_owl_libraries_paths =	array(BASEPATH.'Libraries');

	protected $_owl_cached_vars =	array();
	protected $_owl_ob_level;
	protected $clientDb		=	array();
	public $db;

	protected $_owl_models =	array();
	protected $_owl_functions =	array();
	protected $_owl_libraries =	array();
	
    public function __construct()
	{
		$this->_owl_ob_level = ob_get_level();
		$this->_owl_classes =& is_loaded();
		//log_message('info', 'Loader Class Initialized');
	}
	
	/*
	* ------------------------------------------------------
	*  Load the global functions
	* ------------------------------------------------------
	*/
	public function initialize()
	{
		$this->_owl_autoloader();
	}
	protected function _owl_autoloader(){
		$this->getConfigDb();
	}
	private function configDb(){
		$result= false;
		if (file_exists('config'.DIRECTORY_SEPARATOR.'setup.php')){
			include('config'.DIRECTORY_SEPARATOR.'setup.php');
			if(!empty($sysConfig)){
				if (file_exists('config'.DIRECTORY_SEPARATOR.'database.php') && !empty($sysConfig['application_model'])){
					include('config'.DIRECTORY_SEPARATOR.'database.php');
					foreach ($config as $appname => $val){
						$config[$appname]= $val[ENVIRONMENT];
					}
					$db['sysconf'] = $sysConfig;
					$db['config'] = $config;
					$result = (object)$db;
				}
			}
		}
		return $result;
	}
	public function getConfigDb($permit=""){
		$result= null;
		if(!$this->configDb()){
			header('HTTP/1.1 503-1 Config Database Unavailable.', TRUE, 503);
			exit(1);
		}else{
			$result = new stdClass();
			$db = $this->configDb();
			if($permit == "" and isset($db->config[$db->sysconf['application_model']])){
				$dbs = (object)$db->config[$this->configDb()->sysconf['application_model']];
			}else if($permit != "" and !empty($db->config[$permit])){
				$dbs = (object)$db->config[$permit];
			}else{
				$dbs = (object)$db->config['default'];
			}
			$result->name 		= $permit;
			$result->dbname 	= $dbs->database;
			$result->username 	= $dbs->username;
			$result->password 	= $dbs->password;
			$result->dbserver 	= $dbs->hostname;
		}		
		return $result;
	}
	public function database($db=null){
		// Grab the super object
		$_owl_CI =& get_instance();
		if(!$db){
			$db = $this->getConfigDb();
			if(!$db){
				header('HTTP/1.1 503-2 Config Database Unavailable.', TRUE, 503);
				exit(1);
			}
		}
		if(empty($_owl_CI->connection) or (!empty($_owl_CI->connection) and $db->dbserver.$db->username !== $_owl_CI->connection->dbserver.$_owl_CI->connection->username)){
			try{
				if (!empty($_owl_CI->connection)){
					unset($_owl_CI->connection);
				}
				$conn =new stdClass();
				$conn = $db;
				$conn->owlPDO = new PDO('mysql:host='.$db->dbserver.';dbname='.$db->dbname, $db->username, $db->password, array(PDO::ATTR_PERSISTENT => false));
				$conn->owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}catch (PDOException $e) {
				header('HTTP/1.1 503-3 Could not connect to DB.', TRUE, 503);
				die();
			}
			if (empty($conn->owlPDO)){
				header('HTTP/1.1 503-4 Could not connect to DB.', TRUE, 503);
			}else{
				if (!empty($_owl_CI->db)){
					unset($_owl_CI->db);
				}
				$_owl_CI->db = new stdClass();
				
				$_owl_CI->connection =& $conn;
				$_owl_CI->db->name = $conn->name;
				$_owl_CI->db->dbname = $conn->dbname;
				$_owl_CI->db->owlPDO = $conn->owlPDO;
				return $this;
			}
		}else{
			$_owl_CI->connection->dbserver = $db->dbserver;
			$_owl_CI->connection->username = $db->username;
			$_owl_CI->connection->name = $db->name;
			$_owl_CI->connection->dbname = $db->dbname;
			$_owl_CI->db->name = $db->name;
			$_owl_CI->db->dbname = $db->dbname;
			return $this;
		}
		
	}
    public function is_loaded($class)
	{
		return array_search(ucfirst($class), $this->_owl_classes, FALSE);
	}
	protected function _owl_prepare_view_vars($vars)
	{
		if ( ! is_array($vars))
		{
			$vars = is_object($vars)
				? get_object_vars($vars)
				: array();
		}

		foreach (array_keys($vars) as $key)
		{
			if (strncmp($key, '_owl_', 5) === 0)
			{
				unset($vars[$key]);
			}
		}

		return $vars;
	}
	public function message($data){
		return $data;
	}
    public function view($view, $vars = array(), $return = FALSE)
	{
		return $this->_owl_load(array('_owl_view' => $view, '_owl_vars' => $this->_owl_prepare_view_vars($vars), '_owl_return' => $return));
	}
	public function lib($funct='',$name = ''){
		
		if($funct == ''){
			return $this;
		}
		$path = '';
		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($funct, DIRECTORY_SEPARATOR)) !== FALSE){
			// The path is in front of the last slash
			$path = substr($funct, 0, ++$last_slash);
			// And the model name behind it
			$funct = substr($funct, $last_slash);
		}
		if (empty($name)){
			$name = $funct;
		}
		if (in_array($name, $this->_owl_functions, TRUE)){
			return $this;
		}
		$_owl_CI =& get_instance();
		if (!isset($_owl_CI->lib)){
			$_owl_CI->lib = new stdClass;
		}
		if (isset($_owl_CI->lib->$name)){
			throw new RuntimeException('The functions name you are loading is the name of a resource that is already being used: '.$name);
		}
		$funct = ucfirst($funct);
		$functFile = $funct;
		if ( ! class_exists($funct, FALSE))
		{
			foreach ($this->_owl_funct_paths as $mod_path)
			{
				$filePath = array();
				$dirctory = $mod_path;
				$dir = new DirectoryIterator($dirctory);
				foreach ($dir as $fileinfo){
					if (!$fileinfo->isDot()){
						if(!$fileinfo->isFile()){
							$filePath[] = $fileinfo->getFilename();
						}
					}
				}
				
				if(count($filePath) > 0){
					$hasModelFile = FALSE;
					foreach ($filePath as $mod_filePath){
						if (file_exists($mod_path.$mod_filePath.DIRECTORY_SEPARATOR.$functFile.'.php')){
							$path = $path.$mod_filePath.DIRECTORY_SEPARATOR;
							break;
						}elseif(file_exists($mod_path.$mod_filePath.DIRECTORY_SEPARATOR.lcfirst($functFile).'.php')){
							$functFile =lcfirst($functFile);
							$path = $path.$mod_filePath.DIRECTORY_SEPARATOR;
							break;
						}
					}
				}else{
					if ( ! file_exists($mod_path.$path.$functFile.'.php')){
						continue;
					}elseif(!file_exists($mod_path.$path.lcfirst($functFile).'.php')){
						$functFile = lcfirst($functFile);
						continue;
					}
				}
				
				require_once($mod_path.$path.$functFile.'.php');
				if ( ! class_exists($funct, FALSE)){
					throw new RuntimeException($mod_path.$path.$funct.".php exists, but doesn't declare class ".$funct);
				}
				break;
			}
			if ( ! class_exists($funct, FALSE)){
				throw new RuntimeException('Unable to locate the Lib you have specified: '.$funct);
			}
		}
		$this->_owl_functions[] = $name;
		$funct = new $funct();
		$_owl_CI->lib->$name = $funct;
		return $this;
	}
	public function library($funct='',$name = ''){
		if($funct == ''){
			return $this;
		}
		$path = '';
		$hasNameSpace = false;
		$hasAutoload = false;
		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($funct,'/')) !== FALSE){
			// The path is in front of the last slash
			$path = substr($funct, 0, ++$last_slash);
			$path = str_replace('/',DIRECTORY_SEPARATOR,$path);
			// And the model name behind it
			$funct = substr($funct, $last_slash);
			$hasNameSpace = true;
		}
		// echo $path;
		// exit();
		if (empty($name)){
			$name = $funct;
		}
		if (in_array($name, $this->_owl_libraries, TRUE)){
			return $this;
		}
		// if (in_array('autoload', $this->_owl_libraries, TRUE)){
		// 	$hasAutoload = true;
		// }
		$_owl_CI =& get_instance();
		if (!isset($_owl_CI->library)){
			$_owl_CI->library = new stdClass;
		}
		$namespace = str_replace('/','\\',$path.$funct);
		if (isset($_owl_CI->library->$name)){
			throw new RuntimeException('The functions name you are loading is the name of a resource that is already being used: '.$name);
		}else{
			foreach ($this->_owl_libraries_paths as $mod_path){
				if($hasNameSpace and !$hasAutoload){
					include($mod_path.DIRECTORY_SEPARATOR.'autoload.php');
					$this->_owl_libraries[] = 'autoload';
				}
				if (!file_exists($mod_path.DIRECTORY_SEPARATOR.$path.$funct.".php")){
					continue;
				}
			}
			require_once($mod_path.DIRECTORY_SEPARATOR.$path.$funct.'.php');
		}
		if ( ! class_exists($namespace, FALSE)){
			throw new RuntimeException($mod_path.DIRECTORY_SEPARATOR.$path.$funct.".php exists, but doesn't declare class ".$namespace);
		}
		$funct = $namespace;
		
		$this->_owl_libraries[] = $name;
		$funct = new $funct();
		$_owl_CI->library->$name = $funct;
		return $this;
	}
	public function model($model,$name = '',$db_conn=FALSE)
	{
		$setup_db = array();
		if(!$this->configDb()){
			header('HTTP/1.1 503-5 Config Database Unavailable.', TRUE, 503);
			exit(1);
		}	
		if (empty($model)){
			return $this;
		}elseif (is_array($model)){
			foreach ($model as $key => $value)
			{
				is_int($key) ? $this->model($value, '', $db_conn) : $this->model($key, $value, $db_conn);
			}

			return $this;
		}
		
		$path = '';
		
		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($model, DIRECTORY_SEPARATOR)) !== FALSE)
		{
			// The path is in front of the last slash
			$path = substr($model, 0, ++$last_slash);

			// And the model name behind it
			$model = substr($model, $last_slash);
		}

		if (empty($name))
		{
			$name = $model;
		}
		if (in_array($name, $this->_owl_models, TRUE))
		{
			return $this;
		}
		$_owl_CI =& get_instance();
		if (isset($_owl_CI->$name))
		{
			throw new RuntimeException('The model name you are loading is the name of a resource that is already being used: '.$name);
		}
		if ( ! class_exists('OWL_Model', FALSE))
		{
			if ( ! class_exists('OWL_Model', FALSE)){
				require_once(BASEPATH.'OWL_Model.php');
			}
		}
		$model = ucfirst($model);
		
		$modelFile = $model;
		$mod_Initial = "";
		if ( ! class_exists($model, FALSE))
		{
			foreach ($this->_owl_model_paths as $mod_path)
			{
				$filePath = array();
				$dirctory = $mod_path;
				$dir = new DirectoryIterator($dirctory);
				foreach ($dir as $fileinfo){
					if (!$fileinfo->isDot()){
						if(!$fileinfo->isFile()){
							$filePath[] = $fileinfo->getFilename();
						}
					}
				}
				// print_r($filePath);
				if(count($filePath) > 0){
					$hasModelFile = FALSE;
					foreach ($filePath as $mod_filePath){
						if (file_exists($mod_path.$mod_filePath.DIRECTORY_SEPARATOR.$modelFile.'.php')){
							$path = $path.$mod_filePath.DIRECTORY_SEPARATOR;
							if(empty($_owl_CI->clientDb[$name])){
								$_owl_CI->clientDb[$name] = $this->getConfigDb($mod_filePath);
							}
							break;
						}elseif (file_exists($mod_path.$mod_filePath.DIRECTORY_SEPARATOR.strtolower($modelFile).'.php')){
							$modelFile = strtolower($modelFile);
							$path = $path.$mod_filePath.DIRECTORY_SEPARATOR;
							if(empty($_owl_CI->clientDb[$name])){
								$_owl_CI->clientDb[$name] = $this->getConfigDb($mod_filePath);
							}
							break;
						}
					}
				}else{
					if (! file_exists($mod_path.$path.$modelFile.'.php')){
						continue;
					}elseif(!file_exists($mod_path.$path.strtolower($modelFile).'.php')){
						$modelFile =strtolower($modelFile);
						continue;
					}
				}
				// echo $mod_path.'modules/'.$path.$model.'.php <br>';
				// $setup_db[$mod_Initial] = $setup[$mod_Initial][ENVIRONMENT];
				require_once($mod_path.$path.$modelFile.'.php');
				if ( ! class_exists($model, FALSE)){
					throw new RuntimeException($mod_path.$path.$model.".php exists, but doesn't declare class ".$model);
				}
				break;
			}
			if ( ! class_exists($model, FALSE)){
				throw new RuntimeException('Unable to locate the model you have specified: '.$model);
			}
		}
		$this->_owl_models[] = $name;
		$model = new $model();
		$_owl_CI->$name = $model;
		return $this;
	}
    protected function _owl_load($_owl_data)
	{
		// Set the default data variables
		foreach (array('_owl_view', '_owl_vars', '_owl_path', '_owl_return') as $_owl_val)
		{
			$$_owl_val = isset($_owl_data[$_owl_val]) ? $_owl_data[$_owl_val] : FALSE;
		}

		$file_exists = FALSE;

		// Set the path to the requested file
		if (is_string($_owl_path) && $_owl_path !== '')
		{
			$_owl_x = explode(DIRECTORY_SEPARATOR, $_owl_path);
			$_owl_file = end($_owl_x);
		}
		else
		{
			$_owl_ext = pathinfo($_owl_view, PATHINFO_EXTENSION);
			$_owl_file = ($_owl_ext === '') ? $_owl_view.'.php' : $_owl_view;

			foreach ($this->_owl_view_paths as $_owl_view_file => $cascade)
			{
				if (file_exists($_owl_view_file.$_owl_file))
				{
					$_owl_path = $_owl_view_file.$_owl_file;
					$file_exists = TRUE;
					break;
				}

				if ( ! $cascade)
				{
					break;
				}
			}
		}
		
		if ( ! $file_exists && ! file_exists($_owl_path))
		{
			show_error('Unable to load the requested file: '.$_owl_file);
		}
		
		$_owl_CI =& get_instance();
		
		foreach (get_object_vars($_owl_CI) as $_owl_key => $_owl_var)
		{
			if ( ! isset($this->$_owl_key))
			{
				$this->$_owl_key =& $_owl_CI->$_owl_key;
			}
		}

		empty($_owl_vars) OR $this->_owl_cached_vars = array_merge($this->_owl_cached_vars, $_owl_vars);
		extract($this->_owl_cached_vars);
		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.
		//echo $_owl_path;
		if ( ! is_php('5.4') && ! ini_get('short_open_tag')){
			echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($_owl_path))));
		}else{
			include($_owl_path); // include() vs include_once() allows for multiple views with the same name
		}
		
		
		// Return the file data if requested
		if ($_owl_return === TRUE){
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}
		if (ob_get_level() > $this->_owl_ob_level + 1){
			ob_end_flush();
		}else{
			$_owl_CI->output->append_output(ob_get_contents());
			@ob_end_clean();
		}

		return $this;
	}
}   
?>