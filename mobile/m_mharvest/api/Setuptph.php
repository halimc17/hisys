<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupTph
{
    protected $pathIndex = "kebun/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_tph', $this->pathIndex);
    }
    function setuptphdata()
    {
        // $w = "where karyawanid = '" . $this->user['userid'] . "'";
        // WHERE kodeorg like '".substr($user['lokasitugas'],0,4)."%' ".$kodeorgTujuanAsistensi."";
        // $data =  $this->qClass->getTph("WHERE kodeorg like '".substr($this->user['lokasitugas'],0,4)."%'");
        $data =  $this->qClass->getTphV2($this->user);
        return $data;
    }
    function tphbesardata()
    {
        // $w = "WHERE kodeorg = '".substr($this->user['lokasitugas'],0,4)."' and status = '1'";
        // WHERE kodeorg like '".substr($user['lokasitugas'],0,4)."%' ".$kodeorgTujuanAsistensi."";
        // $data =  $this->qClass->getTphBesar($w);
        $data =  $this->qClass->getTphBesarV2($this->user);
        return $data;
    }
}
