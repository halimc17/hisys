<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Dashboard
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->mMap = load_class('Mmap', $this->pathIndex);
        $this->Mversion = load_class('Mversion', $this->pathIndex);

    }

    function mobileapp(){
        $version = $this->Mversion->getData($this->user);
        $data['p'] = date('Y-m'); // periode ersion
        $data['mv'] = $version['app_version'].";".$version['build_number']; // mobile version
        $data['po'] = ''; //posting transaction
        $data['pvs'] = ''; //panen vs SPB
        $data['log'] = ''; //login
        return $data;
    }
    function traffic_user(){
        $data = $this->mMap->listuser($this->user);
        return $data;
    }
    function traffic_user_date($tanggal="",$user=""){
        $data = $this->mMap->user_date($this->user,$tanggal="",$user="");
        return $data;
    }
    function traffic_locations($tanggal="",$user="",$type=0,$ver="000:00:00",$verPrev="000:00:00"){
        if(empty($tanggal)){
            $tanggal = date('Y-m-d');
        }
        $data = $this->mMap->get_gps($this->user,$tanggal,$user,$type,$ver,$verPrev);
        return $data;
    }
}
