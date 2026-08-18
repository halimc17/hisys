<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setupgudangtxn
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_gudangtxn', $this->pathIndex);
    }
    function gudangtxndata()
    {
        // $w = "where karyawanid = '" . $this->user['userid'] . "'";
        // WHERE kodeorg like '".substr($user['lokasitugas'],0,4)."%' ".$kodeorgTujuanAsistensi."";
        // $data =  $this->qClass->getData("WHERE afdeling like '".substr($this->user['lokasitugas'],0,4)."%'");
        $data =  $this->qClass->getDataV2($this->user);
        return $data;
    }
}
