<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setupasistensi
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_asistensi', $this->pathIndex);
    }
    function karyawaneksdata()
    {
        // $w = "where karyawanid = '" . $this->user['userid'] . "'";
        // WHERE kodeorg like '".substr($user['lokasitugas'],0,4)."%' ".$kodeorgTujuanAsistensi."";
        $data =  $this->qClass->getData($this->user['lokasitugas']);
        return $data;
    }

    function KaryawanAsistensiMobile()
    {
        $data = array();
        $data =  $this->qClass->getDataMobileAsistensi($this->user);
        return $data;
    }

    function getdataasistensi()
    {
        $data = array();
        $data =  $this->qClass->getDataAsistensiMobile($this->user);
        return $data;
    }
}
