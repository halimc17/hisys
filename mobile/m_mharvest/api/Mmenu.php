<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mmenu{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct(){
        $this->Menuharvest = load_class('Menuharvest', $this->pathIndex);
    }
    function getusermenu($userid=""){
        if($userid==""){
            $userid = $this->user['username'];
        }
        $data =  $this->Menuharvest->usermenu($userid);
        return $data;
    }
    
}
