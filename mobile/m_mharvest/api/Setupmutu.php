<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupMutu
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Mmutu', $this->pathIndex);
    }

    function getsetupmutu()
    {
        $data['Jenis Buah'] =  $this->qClass->getMutu("WHERE jenis = 'Jenis Buah' and aktif ='1'");
        $data['Mutu Hancak'] =  $this->qClass->getMutu("WHERE jenis = 'Mutu Hancak' and aktif ='1'");
        $data['Mutu Transport'] =  $this->qClass->getMutu("WHERE jenis = 'Mutu Transport' and aktif ='1'");
        $data['Mutu Buah'] =  $this->qClass->getMutu("WHERE jenis = 'Mutu Buah' and aktif ='1'");
        $data['Sensus Produksi'] =  $this->qClass->getMutu("WHERE jenis = 'Sensus Produksi' and aktif ='1'");
        $data['Akp'] =  $this->qClass->getMutu("WHERE jenis = 'Akp' and aktif ='1'");
        return $data;
    }

    function getsetupmutubuah()
    {
        $data = $this->qClass->getMutu("WHERE jenis = 'Mutu Buah' and aktif ='1'");
        return $data;
    }
    function getsetupmutuhancak()
    {
        $data = $this->qClass->getMutu("WHERE jenis = 'Mutu Hancak' and aktif ='1'");
        return $data;
    }
}
