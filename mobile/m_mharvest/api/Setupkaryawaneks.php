<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setupkaryawaneks
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_karyawaneks', $this->pathIndex);
    }
    function karyawaneksdata()
    {
        // $w = "where karyawanid = '" . $this->user['userid'] . "'";
        // WHERE kodeorg like '".substr($user['lokasitugas'],0,4)."%' ".$kodeorgTujuanAsistensi."";
        $data =  $this->qClass->getData($this->user['lokasitugas']);
        return $data;
    }
}
