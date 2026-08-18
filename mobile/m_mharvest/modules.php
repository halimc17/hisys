<?php
try{
    session_start();
    ini_get('session.save_path');
    defined('BASEPATH') OR exit('No direct script access allowed');
    class Modules extends OWL_Controller{
        public function __construct(){
            parent::__construct();
            
                if(!$this->sec_sys_serv()){
                    $headers = apache_request_headers();
                    if($headers['api_key']){
                        header('HTTP/1.0 203 Non-Authoritative', true, 2031);
                        header('message: Your Session has Expired');
                        exit();
                    }else{
                        $this->redirect('login');
                    }
                }else{
                    $SELF = $this->uri->segments[2];
                    $this->page = NULL;
                    $this->pathIndex =  APPPATH.$this->uri->uri_string;
                    $gantiAction = "";
                    $keySlave = "_slave";
                    if(strpos($this->uri->segments[2], $keySlave)){
                        $gantiAction = 'slave';
                        $SELF = str_replace($keySlave,"",$this->uri->segments[2]);
                        $SELFSTRING = str_replace($this->uri->segments[2],"",$this->uri->uri_string);
                        $this->pathIndex = APPPATH.$SELFSTRING.$SELF;
                    }
                    $error = false;
                    if(file_exists($this->pathIndex.'.php')){
                        $this->page = load_class(ucfirst($SELF),'modules');
                        if($gantiAction == ""){
                            if (method_exists(ucfirst($SELF), 'options')) {
                                if($options = $this->page->options(strtolower($SELF),$this->getMenu($SELF," > "))){
                                    echo "<script>var options = ".$options.";</script>";
                                }
                                include(APPPATH.'refreshaction.php');
                            }else{
                                //Full HTML
                                include(APPPATH.'refreshaction.php');
                            }
                        }elseif($gantiAction == "slave"){
                            if (method_exists(ucfirst($SELF), 'slave')) {
                                try{
                                    $this->page->slave();
                                }catch(Exception $e){
                                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                                }
                            }else{
                                $error = 404;
                                //exit('Method "slave" Not Found!');
                            }
                        }
                    }else{
                        $error = 404;
                    }
                    if($error){
                        //set_status_header($error,'File Not Found');
                        if(file_exists(VIEWPATH.$SELF.'.php')){
                            $this->page = load_class('Master_view','modules');
                            $this->page->index(VIEWPATH.$SELF.'.php');
                            
                        }elseif(file_exists(VIEWPATH.DIRECTORY_SEPARATOR.'error'.DIRECTORY_SEPARATOR.$error.'.php')){
                            include(VIEWPATH.DIRECTORY_SEPARATOR.'error'.DIRECTORY_SEPARATOR.$error.'.php');
                        }else{
                            include(VIEWPATH.DIRECTORY_SEPARATOR.'error'.DIRECTORY_SEPARATOR.'404.php');
                        }
                    }
                }

            
        }

        // create new Function======

        //============================
        private function getMenu($name,$type=false){
            $jsonR = array();
            $menu = array();
            $s="select id, caption, caption2, caption3 from ".$this->dbname.".menu where action='".$name."'";
            $res=$this->owlPDO->query($s) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
                if($r=$res->fetch()){
                    if ($_SESSION['language']=='EN'){
                        $postMenu=strtoupper($r['caption2']);
                    } else if ($_SESSION['language']=='MY'){
                        $postMenu=strtoupper($r['caption3']);
                    } else {
                        $postMenu=strtoupper($r['caption']);
                    }
                    $jsonR['caption'] = $postMenu;
                    $str="SELECT f.*
                    FROM (
                        SELECT @id AS _id, (SELECT @id := parent FROM ".$this->dbname.".menu WHERE id = _id)
                        FROM (SELECT @id := ".$r['id']." ) tmp1
                        JOIN ".$this->dbname.".menu ON @id <> 0
                        ) tmp2
                    JOIN ".$this->dbname.".menu f ON tmp2._id = f.Id
                    order by action,parent";
                    $res=$this->owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while($bar=$res->fetch()){
                        if ($_SESSION['language']=='EN'){
                            $menu[$bar['id']]=strtoupper(strtolower($bar['caption2']));
                        } else if ($_SESSION['language']=='MY'){
                            $menu[$bar['id']]=strtoupper(strtolower($bar['caption3']));
                        } else {
                            $menu[$bar['id']]=strtoupper(strtolower($bar['caption']));
                        }
                    }
                }
                $jsonR['menu'] = $menu;
                if($type){
                    if($type == 'json'){
                        $result = $jsonR;
                    }else{
                        $result =  implode($type,$menu);
                    }
                }else{
                    $result = $postMenu."<span style='font-size:13px;font-weight:normal;float:right'>".implode(" > ",$menu)."</span>";
                }
            
            return $result;
        }
        
    }
}catch(Exception $e){
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}