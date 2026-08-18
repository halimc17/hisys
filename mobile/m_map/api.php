<?php 
//header('Access-Control-Allow-Origin: http://localhost/*');
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends OWL_Controller{
    function __construct(){
		parent::__construct();
        $this->load->model("Signin","userdata");
        $this->errorArr = $GLOBALS['errorArr'];
        if(empty($this->uri->segments[2]) or ($this->uri->segments[2] != 'login' and $this->uri->segments[2] != 'access_token')){
            $this->user = $this->init();
        }
        $this->json_response = json_encode($this->response);
	}
    function __destruct(){
        header('Content-Type: application/json;charset=UTF-8');
        if(isset($this->response['error']) and $this->response['error'] ==true){
            header('HTTP/1.0 '.$this->response['status'].' '.$this->response['message'], true, 4161);
        }else if(!isset($this->response['error']) ){
            header('HTTP/1.0 500 '.$this->userdata->errorArr['invalid_argument'], true, 4161);
        }
        if(ENVIRONMENT == 'development'){};
        echo $this->json_response;
    }
    private function error_api(){
        $this->json_response = json_encode($this->response);
    }
    private function init(){
        $getAuthAPI = $this->sec_sys_api();
        if(!$getAuthAPI){
            $this->response['error'] = true;
            $this->response['status'] = 416;
            $this->response['message'] = $this->userdata->errorArr['user_not_found'];
			$this->error_api();
            exit();
		}
        return $getAuthAPI;
    }
    function getuser(){
        $data=$this->response;
        $data['result'] = $this->user;
        $this->json_response = json_encode($data);
    }
    function token(){
        $getToken = true;
        // on Develope
        if(!$getToken){
            $this->response['error'] = true;
            $this->response['status'] = 416;
            $this->response['message'] = $this->userdata->errorArr['id_token_revoked'];
			$this->error_api();
            exit();
		}
        return $getToken;
    }
    function access_token()
    {
        $this->load->model("Authorize");
        $data =  $this->Authorize->get_apikey();
        if(!$data or count($data) == 0){
            $this->response['error'] = true;
            $this->response['status'] = 416;
            $this->response['message'] = $this->userdata->errorArr['user_not_found'];
			$this->error_api();
            exit();
		}else{
            if(!empty($this->uri->segments[3]) and isset($data[$this->uri->segments[3]])){
                if(empty($data[$this->uri->segments[3]])){
                    $this->json_response = NULL;
                }else{
                    $this->json_response = $data[$this->uri->segments[3]];
                }
            }else{
                $this->json_response = json_encode($data);
            }
        }
    }
    function profile(){
        $getAuthAPI = $this->user;
        $data=$this->response;
        $data['result'] = $this->userdata->session($getAuthAPI);
        $this->json_response = json_encode($data);
    }
    function login(){
        $result=$this->userdata->auth('API','LOGIN');
        $data=$this->userdata->response;
        $data['result']=$result;
        $this->json_response = json_encode($data,JSON_FORCE_OBJECT);
    }
    private function invoke_method($model,$method,$param=array()){
        try{
            $result = call_user_func_array(array(&$model,$method), $param);
        }catch(Exception $e){
            $result = FALSE;
            $this->response['error'] = TRUE;
        }
        return  $result;
    }
    
    private function load_method($model,$method,$load=false){
        $result = array();
        if (method_exists($model,$method)) {
            $paramMethod = array();
            $reflect = new ReflectionMethod($model, $method);
            $result[$method] = array();
            if(count($reflect->getParameters()) > 0){
                foreach($reflect->getParameters() AS $arg){
                    foreach($arg as $k => $v){
                        $val = '';
                        $argType = (string)$arg->getType();
                        if(isset($_GET[$v])){
                            $val = $_GET[$v];
                        }
                        if($load){
                            $paramMethod[$method][] = $this->setType($val,$argType);
                        }else{
                            $result[$method][$v] = ((isset($_GET[$v]))?$this->setType($val,$argType):$argType);
                        }
                    }
                }
            }
            if(!$load){
                $this->response['status'] = 405;
                $this->response['error'] = TRUE;
                $this->response['message'] = "Method Not Allowed";
            }
            if($load){
                $result = array();
                $parameters = array();
                if(isset($paramMethod[$method])){
                    $parameters = $paramMethod[$method];
                }
                $result[$method] = $this->invoke_method($model,$method,$parameters);
            }
        }
        return $result;
    }
    
    private function setType($value,$type){
        $result = $value;
        switch($type){
            case 'bool':
            case 'boolean':
                $result = (bool)$value;
            break;
            case 'int':
                $result = (int) $value;
            break;
            case 'double':
                $result = (double) $value;
            break;
            case 'array':
                $result = (array) $value;
            break;
            case 'string':
                $result = (string) $value;
            break;
        }
        return $result;
    }
    function module(){
        $result = FALSE;
        $this->response['status'] = 200;
        $this->response['error'] = FALSE;
        $this->response['message'] = "";
        $path = APPPATH.'api';
        $filename=$method  = '';
        $load = FALSE;
        $is_Private = array('__construct','__destruct','__invoke','init');
        $is_exec = array('load','send');
        if(!empty($this->uri->segments[3])){
            $filename = ucfirst($this->uri->segments[3]);
        }
        if(!empty($this->uri->segments[4])){
            if(in_array($this->uri->segments[4],$is_exec)){
                $load = $this->uri->segments[4];
            }else{
                $method = ucfirst($this->uri->segments[4]);
            }
        }
        if(!empty($this->uri->segments[5]) and in_array($this->uri->segments[5],$is_exec)){
            $load = $this->uri->segments[5];
        }
        if(file_exists($path)){
            if($filename != ''){
                if (file_exists($path.DIRECTORY_SEPARATOR.$filename.'.php')){
                    $fileName = strtolower($filename);
                    if($is_exec == 'send'){
                        $this->token();
                    }
                    $userData = array();
                    if(!empty($userData = $this->userdata->standard($this->user)) and count($userData)>0){
                        if(!empty($this->userdata->empl($this->user))){
                            $empl = $this->userdata->empl($this->user);
                            $userData = array_replace_recursive($userData,$empl);
                            if(!empty($this->userdata->privilege($this->user,$empl))){
                                $userData['orgaccess'] = $this->userdata->privilege($this->user,$empl)['orgaccess'];
                            }
                        }
                    }
                    $db = $this->clientDb[$filename];
                    if(empty($db)){
                        $pathIndex = str_replace("/","",$model->pathIndex);
                        $_owl_CI =& get_instance();
                        $_owl_CI->clientDb[$filename] = $this->load->getConfigDb($pathIndex);
                        $db = $_owl_CI->clientDb[$filename];
                    }
                    $this->load->database($db);
                    $model = load_class($filename,"api");
                    $model->user = $userData;
                    $allMethode = array();
                    $resultMetod = array();
                    if($method != '' and !in_array($method,$is_Private)){
                        $d = $this->load_method($model,$method,$load);
                        $resultMetod = $d[$method];
                    }else{
                        $methods = get_class_methods($model);
                        foreach ($methods as $method) {
                            if(in_array($method,$is_Private)){
                                continue;
                            }else{
                                $d = $this->load_method($model,$method);
                                array_push($resultMetod,$d);
                            }
                           
                        }
                    }
                    $result->$fileName = $resultMetod;
                }
            }else{
                $dir = new DirectoryIterator($path);
                if(count($dir) > 0){
                    foreach ($dir as $fileinfo){
                        if (!$fileinfo->isDot()){
                            if($fileinfo->isFile()){
                                if(strpos($fileinfo->getFilename(),".php")){
                                    $filename = ucfirst(str_replace(".php","",$fileinfo->getFilename()));
                                    $fileName = strtolower($filename);
                                    $resultMetod = array();
                                        $methods = get_class_methods(load_class($filename,"api"));
                                        foreach ($methods as $method) {
                                            if(in_array($method,$is_Private)){
                                                continue;
                                            }else{
                                                array_push($resultMetod,$method);
                                            }
                                        }
                                    
                                     $result->$fileName = $resultMetod;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if($result){
            if(in_array($load,$is_exec)){
                $response = $result->$fileName;
                if(isset($response['status'])){
                    $this->response['status']=$response['status'];
                    unset($response['status']);
                }
                if(isset($response['error'])){
                    $this->response['error']=$response['error'];
                    unset($response['error']);
                }
                if(isset($response['message'])){
                    $this->response['message']=$response['message'];
                    unset($response['message']);
                }
                $this->response['result']=$response;
            }else{
                $this->response['result']=$result;
            }
            $this->json_response = json_encode($this->response);
        }else{
            $this->response['status'] = 404;
            $this->response['error'] = TRUE;
            $this->response['message'] = "Undefined Modules";
            $this->error_api();
            exit();
        }
    }


}
?>