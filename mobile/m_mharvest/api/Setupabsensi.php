<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupAbsensi
{
    protected $pathIndex = "sdm/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_Absensi', $this->pathIndex);
    }
    function setupdata()
    {
        // $jeniskehadiran =  $this->qClass->selectJenisIjin();
        $data =  $this->qClass->selectData("WHERE status = '1' AND mobile = '1'");
        return $data;
    }
}
