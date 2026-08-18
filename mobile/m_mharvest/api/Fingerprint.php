<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Fingerprint
{
    protected $pathIndex = "sdm/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Mfingerprint', $this->pathIndex);
    }

    function datatemplate()
    {
        $data =  $this->qClass->dataFinger($this->user);
        return $data;
    }

    function insertAbsensi()
    {
        $data =  $this->qClass->insertAbsen($this->user);
        return $data;
    }

}
