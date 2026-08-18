<?
session_start();
ini_get('session.save_path');
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile extends OWL_Controller{
    public $userlogin;
	public function __construct(){
		parent::__construct();
        $this->userlogin = $this->authMobile();
		if($this->userlogin['login'] == false){
		    echo $this->userlogin['message'];
		}else{
            
            $SELF = "index";
            $this->page = NULL;
            $gantiAction = "";
            $keySlave = "owlMobile.php";
            if(strpos($this->uri->uri_string, $keySlave)){
                $SELFSTRING = str_replace($this->uri->segments[2],"",$this->uri->uri_string);
                $this->pathIndex = APPPATH.$SELFSTRING.$SELF;
            }
            
            if(file_exists($this->pathIndex.'.php')){
                include($this->pathIndex.'.php');
                $this->page = load_class($SELF);
                
                if($gantiAction == ""){
                    $this->page->index($this->userlogin);
                }elseif($gantiAction == "slave"){
                   

                }
            }
        }
	}

    // create new Function======
    function authMobile(){
        //print_r($param);
        $result['login'] = false;
        $result['message'] = "";
        $result['karyawanid'] = "";
        $result['logged'] = "0";
        $result['api_key'] = "";
        $username   = strtolower($this->request('username'));
        $password   = $this->request('password');
        $uuid       = $this->request('uuid');
        $appname    = $this->request('appname');
        $versi      = $this->request('versi');

        if(isset($_SERVER["REMOTE_ADDR"]) ) {
			$ip = $_SERVER["REMOTE_ADDR"];
		}
		else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) {
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else if(isset($_SERVER["HTTP_CLIENT_IP"]) ) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		} 
	   
	    $hostname = gethostbyaddr($ip);

        $q="select karyawanid,logged from ".$this->dbname.".user where LOWER(namauser)='".addslashes(strtolower($username))."' 
            and password=PASSWORD('".addslashes($password)."') and status=1";	
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=> $rOrg){
                $result['karyawanid']=$rOrg['karyawanid'];
                $result['logged']=$rOrg['logged'];
            }
            
            $apky= md5($username.$password.$uuid);
            $updatelogin = "UPDATE ".$this->dbname.".api_key SET is_login=0 WHERE username = '".$username."' AND uuid != '".$uuid."'";
            try{
                $this->owlPDO->exec($updatelogin);
            }catch (PDOException $e){
                $result['login'] = false;
                $result['message'] = "Error Login, ".$e->getMessage();
            }

            $cekuuid = "SELECT * FROM ".$this->dbname.".api_key WHERE username = '".$username."' AND uuid = '".$uuid."'";					
            $quuid=$this->owlPDO->query($cekuuid);
            if($quuid->rowCount() > 0){
                $result['api_key'] = $apky;
                $strkey = "UPDATE ".$this->dbname.".api_key SET datelogin=NOW(), explogin=(NOW() + INTERVAL 7 DAY), appname = '".$appname."',versi = '".$versi."', is_login=1 WHERE username = '".$username."' AND uuid = '".$uuid."'";
                try{
                    $this->owlPDO->exec($strkey);
                }catch (PDOException $e){
                    $result['login'] = false;
                    $result['message'] = "Error Login, ".$e->getMessage();
                }
            }else{
                $strkey = "INSERT INTO ".$this->dbname.".api_key (username, uuid,appname, key_api, datelogin, explogin, is_login, versi)
                VALUES ('".$username."', '".$uuid."','".$appname."', '".$apky."', NOW(), (NOW() + INTERVAL 7 DAY), 1, '".$versi."')";
                try{
                    $this->owlPDO->exec($strkey);
                    $result['api_key'] = $apky;
                }catch (PDOException $e){
                    $result['login'] = false;
                    $result['message'] = "Error Login, ".$e->getMessage();
                }
            }
            
           
            try{
                $strb="insert into ".$this->dbname.".login_history(lastip,lastcomp,lastuser,appname,versi) values('".$ip."','".$hostname."','".$username."','".$appname."','".$versi."')";
                $this->owlPDO->exec($strb);
            }catch (PDOException $e){
               // $result['login'] = false;
               // $result['message'] = "Error Login, ".$e->getMessage();
            }
            try{
                $stra="update ".$this->dbname.".user set logged=1,lastip='".$ip."',lastcomp='".$hostname."' where namauser='".$username."'";
                $this->owlPDO->exec($stra);
            }catch (PDOException $e){
                //$result['login'] = false;
                //$result['message'] = "Error Login, ".$e->getMessage();
            }
            // print_r($result);
            // exit(); 
        }
        $errLogin = "Error: Wrong username/password/device";
        if($result['karyawanid']==''){
			$result['login'] =  false;
            $result['message'] = $errLogin;
		}else{
			$result['login'] =  true;
		}
	
        return $result;
    }
	
}
?>

