<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_gudangtxn extends OWL_Model
{
    function getData($where)
    {
        $data = array();
        $q = "select afdeling,kodegudang,status from " . $this->db->dbname . ".kebun_5gudangtransaksi {$where} ";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    function getDataV2($user)
    {
        $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
        $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));
        // $orgAcsess = array_unique($unitAsistensi);
        // Buat kondisi LIKE untuk setiap elemen dalam array orgaccess
        $like_conditions = array_map(function ($prefix) {
            return "afdeling LIKE '$prefix%'";
        }, $orgAcsess);
        // Gabungkan semua kondisi LIKE dengan operator OR
        $where_like = implode(" OR ", $like_conditions);

        if (count($orgAcsess) > 0) {
            $where = "WHERE (" . $where_like . ")";
        } else {
            $where = "WHERE afdeling like '" . $user['lokasitugas'] . "%'";
        }


        $data = array();


        $q = "select afdeling,kodegudang,status from " . $this->db->dbname . ".kebun_5gudangtransaksi {$where} ";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
}
