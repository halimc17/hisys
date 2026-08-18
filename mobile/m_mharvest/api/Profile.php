<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Profile
{
    protected $pathIndex = "sdm/";
    private static $instance;
    function __construct()
    {
        $this->dataprofile = load_class('Setup_datakaryawan', $this->pathIndex);
    }

    function dataprofile()
    {
        $data = $this->dataprofile->selectDataProfile("where karyawanid = '" . $this->user['userid'] . "'", $this->user);
        return $data;
        // return print_r($this->user);
    }
}
