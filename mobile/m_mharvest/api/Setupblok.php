<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupBlok
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->datablok = load_class('Blok', $this->pathIndex);
    }
    // function datablok()
    // {
    //     $data =  $this->datablok->getDataBlok("WHERE (kodeorg like '" . substr($this->user['lokasitugas'], 0, 4) . "%' and statusblok <> 'TB') or (statusblok = 'TB')");

    //     $value = array();
    //     if (count($data) > 0) {
    //         // $data = $r;
    //         foreach ($data as $k => $v) {
    //             $d['kodeorg'] = $v['kodeorg'];
    //             $d['tahuntanam'] = ($v['tahuntanam'] == '' ? 0 : $v['tahuntanam']);
    //             $d['statusblok'] = ($v['statusblok'] == '' ? 'TM' : $v['statusblok']);
    //             $d['kegiatangroup'] = ($v['statusblok'] == '' ? null : ($v['statusblok'] == "TM" ? "'PNN','TM'" : ($v['statusblok'] == "TBM" ? "'TBM','LC'" : strval($v['statusblok']))));
    //             $d['luasareaproduktif'] = ($v['luasareaproduktif'] == '' ? 0 : $v['luasareaproduktif']);
    //             $d['kelaspohon'] = ($v['kelaspohon'] == '' ? 0 : (int)$v['kelaspohon']);
    //             $d['jumlahpokok'] = (int)$v['jumlahpokok'];
    //             $d['topografi'] = ($v['topografi'] == '' ? '0' : $v['topografi']);
    //             // $d['kemandoran'] ="";
    //             // $d['latitude'] ="";
    //             // $d['longitude'] = "";

    //             $value[] = $d;
    //         }
    //     }

    //     return $value;
    // }

    function datablok()
    {
        $data = array();
        $data =  $this->datablok->getDataBlokMobile($this->user);
        return $data;
    }
}
