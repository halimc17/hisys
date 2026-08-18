<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mrawat
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->rawat = load_class('M_rawat', $this->pathIndex);
    }

    // TODO Status Done Dev -> Waiting QA
    function rawatHeader()
    {
        if (!$check = $this->rawat->init()) {
            $data =  $this->rawat->addHeader($this->user, 'BKM');
            return $data;
        } else {
            return $check;
        }
    }

    // TODO Status Done Dev -> Waiting QA
    function materialRawat()
    {
        $data =  $this->rawat->rawatMaterial($this->user, 'BKM');
        return $data;
    }

    // TODO Status Done Dev -> Waiting QA
    function rawatKehadiranBKM()
    {
        $data =  $this->rawat->rawatKehadiran($this->user, 'BKM');
        return $data;
    }

    // TODO Status Done Dev -> Waiting QA
    function checktxnrawat()
    {
        $data =  $this->rawat->checkdatarow($this->user, 'BKM');
        return $data;
    }

    // TODO Status Waiting Dev
    function uploadRawatImg()
    {
        $data =  $this->rawat->uploadImage($this->user, 'BKM');
        return $data;
    }

    // TODO ERP Get Data Header
    function getheadererp()
    {
        // $data = $this->rawat->headerERP();
        // return $data;
        return $this->rawat->getHeaders($this->user,'BKM');
    }

    // TODO ERP GET DATA DETAIL
    function getdetailerp()
    {
        $data = $this->rawat->detailERP();
        return $data;
    }

    // TODO ERP UPDATE FLAG
    function postdataerp()
    {
        $res = $this->rawat->postERP();
        return $res;
    }
}
