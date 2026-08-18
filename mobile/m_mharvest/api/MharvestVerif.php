<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MharvestVerif
{
    protected $pathIndex = "mharvest/";
    // protected $pathkebun = "kebun/";

    private static $instance;
    function __construct()
    {
        $this->mverif = load_class('MpanenVerif', $this->pathIndex);
        // $this->setup_kebun = load_class('Setup_kebun', $this->pathkebun);
    }
    function verifikasi()
    {
        if (!$check = $this->mverif->init()) {
            $data =  $this->mverif->addHeader($this->user, 'PNV');
            return $data;
        } else {
            return $check;
        }
    }
    function verifikasidtl()
    {
        $data =  $this->mverif->addDetail($this->user, 'PNV');
        return $data;
    }
    // function verifikasigerdang()
    // {
    //     $data =  $this->mpanen->addgerdang($this->user, 'PNV');
    //     return $data;
    // }
    function verifikasimutubuah()
    {
        // $jenismutu = $this->setup_kebun->select_jenismutu();

        $data =  $this->mverif->addmutubuah($this->user);
        return $data;
    }
    function verifikasicheck()
    {
        $data =  $this->mverif->checkdatarow($this->user, 'PNV');
        return $data;
    }
    function verifyimage()
    {
        $data =  $this->mverif->uploadImages($this->user, 'PNV');
        return $data;
    }
    // function tipepanen()
    // {
    //     $data =  $this->mpanen->setup_tipepanen($this->user);
    //     return $data;
    // }
}
