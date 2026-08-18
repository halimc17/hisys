<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mmdm
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->mdm = load_class('M_mdm', $this->pathIndex);
    }
    // TODO ERP Get Admin MDM
    function getAdminMdm()
    {
        $admin=$this->mdm->getAdmin();
        $allowedPackage=$this->mdm->getAllowedPackage();
        return array_merge($admin,$allowedPackage);
    }
}
