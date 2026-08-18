<?php
class getContentAPI {   // Contains the cURL response for debug
	public $response = array();       // Contains the cURL response for debug
	public $result = array();       // Contains the cURL response for debug
	public $state = array();       // Contains the cURL response for debug
    public $url;                 // URL of the session
    public $options = array();   // Populates curl_setopt_array
    public $api_key = "";

    function __construct($url = ''){
        $this->json_response = json_encode($this->response);
	}
    
    function init($url,$options){
        $this->api_key = $this->get_key($url,$options);
    }
    function get($url=""){
        $file = $this->_get($url);
        $this->response = $this->responseApiJson($file);
        return $this;
    }
    function post($url="",$data=array()){
        $body = http_build_query($data);
        $file = $this->_post($url,$body);
        $this->response = $this->responseApiJson($file);
        return $this;
    }
    function responseApiJson($dataJson){
        $result = array();
        if($this->is_json($dataJson)){
            $result = json_decode($dataJson, TRUE);
            if(isset($result['result'])){$this->result = $result['result'];}else{$this->result = array();}
            if(isset($result['status'])){$this->state['status'] = $result['status'];}else{$this->state['status']= 200;}
            if(isset($result['error'])){$this->state['error'] = $result['error'];}else{$this->state['error']= false;}
            if(isset($result['message'])){$this->state['message'] = $result['message'];}else{$this->state['message']= "";}
        }
        return $result;
    }
    private function _get($url,$GETKEYS=false){
        $result = false;
        $apiKey = "";
        if($this->api_key != "" OR $GETKEYS != false){
            if($this->api_key != ""){
                $apiKey = "api_key:".$this->api_key."\r\n";
            }
            $opts = array('http' =>
                array(
                    'method'  => 'GET',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n".$apiKey,
                )
            );
            $context = stream_context_create($opts);
            if(!$result = @file_get_contents($url, false, $context)){
                sscanf($http_response_header[0] ,'%s %d %s', $str1,$status, $str2);
                $result['status'] = $status;
                $result['error'] = true;
                $result['message'] = error_get_last()['message'];
                $result['result'] = array();
                $result = json_encode($result);
            }
        }
        return $result;
    }
    private function _post($url,$body,$GETKEYS=false){
        $result = false;
        $apiKey = "";
        if($this->api_key != "" OR $GETKEYS != false){
            if($this->api_key != ""){
                $apiKey = "api_key:".$this->api_key."\r\n";
            }
        
                $opts = array('http' =>
                    array(
                        'method'  => 'POST',
                        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n".
                        "Content-Length: " . strlen($body) . "\r\n".$apiKey,
                        'content' => $body,
                    )
                );
            $context = stream_context_create($opts);
            if(!$result = @file_get_contents($url, false, $context)){
                sscanf($http_response_header[0] ,'%s %d %s', $str1,$status, $str2);
                $result['status'] = $status;
                $result['error'] = true;
                $result['message'] = error_get_last()['message'];
                $result['result'] = array();
                $result = json_encode($result);
            }
       
        }
        return $result;
    }
    
    private function get_key($url,$options){
        $body = http_build_query($options);
        $file = $this->_post($url,$body,"GETKEYS");
        return $file;
    }
    private function is_json($data=""){
        try {
            json_decode($data,$associative=true, $depth=512, JSON_THROW_ON_ERROR);
            return true;
        }catch(Exception $e) {
            return false;
        }
    }
}

?>