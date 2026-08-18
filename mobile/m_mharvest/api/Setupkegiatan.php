<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupKegiatan
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->datakegiatan = load_class('Kegiatan', $this->pathIndex);
    }
    function datakegiatan()
    {
        // $data =  $this->datakegiatan->getData();
        $data =  $this->datakegiatan->getDataMobile();
        return $data;
    }
    function datakegiatanmobile()
    {
        $data =  $this->datakegiatan->getDataMobile();
        return $data;
    }

    function kegnormamobile()
    {
        $data = $this->datakegiatan->getKegNormaM();
        return $data;
    }
}
