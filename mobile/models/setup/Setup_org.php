<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_org extends OWL_Model
{
    function getData()
    {
        $data = array();
        // echo $user;
        $q = "select kodeorganisasi,namaorganisasi,tipe,induk from " . $this->db->dbname . ".organisasi
        where (tipe like '%GUDANG%')
        or (length(kodeorganisasi)=4 or tipe='PABRIK') or 
        (tipe in ('AFDELING','BIBITAN'))";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    function getDataMobile()
    {
        $data = array();
        // echo $user;
        $q = "select * from " . $this->db->dbname . ".organisasi
        where (tipe like '%GUDANG%')
        or (length(kodeorganisasi)=3 or length(kodeorganisasi)=4 or tipe='PABRIK') or 
        (tipe in ('AFDELING','BIBITAN'))";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = array(
                    'kodeorganisasi' => $v['kodeorganisasi'],
                    'induk' => $v['induk'],
                    'namaorganisasi' => $v['namaorganisasi'],
                    'tipe' => $v['tipe'],
                    'sertifikat' => $v['sertifikat'],
                    'inisialisasiorganisasi' => $v['inisialisasiorganisasi']
                );
            }
        }
        return $data;
    }
}
