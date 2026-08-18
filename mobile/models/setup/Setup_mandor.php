<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_mandor extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT a.mandorid, b.namakaryawan, a.karyawanid, b.lokasitugas, b.subbagian FROM " . $this->db->dbname . ".kebun_5mandor a
        LEFT JOIN " . $this->db->dbname . ".datakaryawan b ON a.mandorid = b.karyawanid
        WHERE a.statusaktif = '1'  GROUP BY a.mandorid ORDER BY b.subbagian " . $limitPage;
        // echo $q;
        $data = $this->query($q, 'ASSOC');
        return $data;
    }
    function selectdata(array $pageLimit = array())
    {
        $result = array();
        $data = $this->selectQuery($pageLimit);
        if ($data and $data->rowCount() > 0) {
            $result = $this->fetch($data);
        }
        return $result;
    }

    function getdata($user)
    {

        $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
        $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));
        // $orgAcsess = array_unique($unitAsistensi);
        // Buat kondisi LIKE untuk setiap elemen dalam array orgaccess
        $like_conditions = array_map(function ($prefix) {
            return "b.lokasitugas LIKE '$prefix%'";
        }, $orgAcsess);
        // Gabungkan semua kondisi LIKE dengan operator OR
        $where_like = implode(" OR ", $like_conditions);

        
        if (count($orgAcsess) > 0) {
            $where = "AND (" . $where_like . ")";
        } else {
            $where = "AND b.lokasitugas LIKE '" . $user['lokasitugas'] . "%'";
        }

        $data = array();
        // $str = "select 
        // a.mandorid, b.namakaryawan,
        // a.karyawanid, a.mandor1, b.lokasitugas,b.subbagian, a.krani from " . $this->db->dbname . ".kebun_5mandor a
        // left join " . $this->db->dbname . ".datakaryawan b on a.mandorid = b.karyawanid
        // {$where}";

        $str = "select 
        a.mandorid, b.namakaryawan,
        a.karyawanid, b.lokasitugas,b.subbagian from " . $this->db->dbname . ".kebun_5mandor a
        left join " . $this->db->dbname . ".datakaryawan b on a.mandorid = b.karyawanid
        where a.statusaktif = '1' " . $where;

        // $jabatan = $this->model('Setup_datakaryawan')->selectataAktif();
        // echo $str;
        // $q = "select unit,gangcode,mandor1,krani,mandorid,karyawanid from "
        //     . $this->db->dbname . ".kebun_5mandor {$where}";

        $r = $this->fetchdata($str);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $d['unit']             = $v['lokasitugas'];
                $d['gangcode']         = strval((int) $v['mandorid']);
                $d['nama']         = $v['namakaryawan'];
                $d['mandorid']         = $v['mandorid'];
                $d['karyawanid']    = $v['karyawanid'];
                $d['mandor1']    = null; //$v['mandor1'];
                $d['krani']         = null; //$v['krani'];

                if ($d['unit'] != '') {
                    $data[] = $d;
                }
            }
        }
        return $data;
    }

    function getKemandoran($where)
    {
        $data = array();
        $str = "SELECT * FROM " . $this->db->dbname . ".kebun_5mandor {$where} ORDER BY nourut ASC";
        $r = $this->fetchdata($str);
        // echo $str;
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }
}
