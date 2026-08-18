<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mmutuhancak
{
    protected $pathIndex = "mharvest/";
    protected $pathkebun = "kebun/";

    private static $instance;
    function __construct()
    {
        $this->mhc = load_class('Mutuhancak', $this->pathIndex);
        $this->setup_kebun = load_class('Setup_kebun', $this->pathkebun);
    }
    function header()
    {
        if (!$check = $this->mhc->init()) {
            $data =  $this->mhc->addHeader($this->user, 'MHC');
            return $data;
        } else {
            return $check;
        }
    }
    function detail()
    {
        $jenismutu = $this->setup_kebun->select_jenismutu();

        $data =  $this->mhc->addDetail($this->user, 'MHC', $jenismutu);
        return $data;
    }

    function barispokok()
    {
        $jenismutu = $this->setup_kebun->select_jenismutu();

        $data =  $this->mhc->addDetail($this->user, 'MHC', $jenismutu);
        return $data;
    }

    function mutucheck()
    {
        $data =  $this->mhc->checkdatarow($this->user);
        return $data;
    }

    function image()
    {
        $data =  $this->mhc->uploadImages($this->user);
        return $data;
    }
}
