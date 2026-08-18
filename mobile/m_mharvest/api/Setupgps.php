<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setupgps
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_gps', $this->pathIndex);
    }

    function showdata()
    {
        // print_r($this->user);
        echo $this->user['username'];
    }

    function setupgpsinterval()
    {
        $data =  $this->qClass->getGpsInterval();
        return $data;
    }


    function syncdatagps()
    {
        if (!$check = $this->qClass->init()) {
            $data =  $this->qClass->syncData($this->user);
            return $data;
        } else {
            return $check;
        }
    }
}
