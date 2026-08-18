<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_Absensi extends OWL_Model
{
    function selectData($where = '')
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".sdm_5absensi {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $d['kodeabsen'] = $v['kodeabsen'];
                $d['keterangan'] = $v['keterangan'];
                $d['nilaihk'] = $v['nilaihk'];
                $d['validasidokumen'] = $v['validasidokumen'];
                $data[] = $d;
            }
        }
        return  $data;
    }


    // tidak terpakai
    function selectJenisIjin()
    {
        $data = array();
        $q = "select jeniskehadiran from " . $this->db->dbname . ".sdm_5jenisijin";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    function materialDtl($where)
    {
        $data = array();
        $q = "select namabarang, satuan from " . $this->db->dbname . ".log_5masterbarang {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        // print_r($data);
        return $data;
    }

    function erpAbsensi($notxn)
    {
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile a 
            left join " . $this->db->dbname . ".sdm_absensi b on a.notransaksi = b.notransaksi  
            where a.noreferensi = '$notxn' AND a.tipetransaksi='ABS'";
        $abs = $this->fetchdata($q);
        $dataAbsensi = [];
        if (count($abs) > 0) {
            foreach ($abs as $key => $value) {
                $dataAbsensi[$key] = $value;
            }
        }
    }
    
    function getDataKaryawan($whr)
    {
        $data = array();
        $q = "select karyawanid,namakaryawan from " . $this->db->dbname . ".datakaryawan " . $whr;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data[0]['namakaryawan'];
    }
}
