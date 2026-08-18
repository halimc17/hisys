<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Sdm{
    protected $pathIndex = "sdm/";
    private static $instance;
    function __construct(){
        $this->datakaryawan = load_class('Setup_datakaryawan',$this->pathIndex);
	}
    function datakaryawan(bool $aktif=true,string $tipekaryawan=""){
        return $this->datakaryawan->selectData();

    }
    function karyawan(bool $aktif=true,string $tipekaryawan=""){
        // return $this->datakaryawan->selectDataMobile("where (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".date('Y-m-d')."') and  a.kodeorganisasi = '".$this->user['kodeorganisasi']."'");
        return $this->datakaryawan->selectDataMobileRev($this->user);
    }
    function jabatan(int $kodejabatan=0,bool $aktif=true){
        $where = "where aktif = '1'";
        if($kodejabatan>0){
            $where .= " and kodejabatan = '".$kodejabatan."'";
        }
        return $this->datakaryawan->selectJabatan($where);
    }

    function setup_absensi(){
        return $this->datakaryawan->selectData();

    }
}       

?>