<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupVhc
{
    protected $pathIndex = "traksi/";
    private static $instance;
    function __construct()
    {
        $this->datakendaraan = load_class('Setup_vhc', $this->pathIndex);
    }
    function datakendaraan()
    {
        $data =  $this->datakendaraan->getDataApi("where status=1 and kodeorg = '".$this->user['lokasitugas']."' order by kodevhc");
        return $data;
    }
}
