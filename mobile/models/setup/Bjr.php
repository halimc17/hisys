<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bjr extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5bjr " . $limitPage;
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
    function getDataBjr($where)
    {
        $data = array();
        $q = "SELECT kebun_5bjr.*, setup_blok.indukblok FROM " . $this->db->dbname . ".kebun_5bjr LEFT JOIN " . $this->db->dbname . ".setup_blok ON kebun_5bjr.kodeorg = setup_blok.kodeorg {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {

                $d['kodeorg'] = $v['indukblok'];
                $d['kelaspohon'] = ($v['kelaspohon'] == '' ? '0' : $v['kelaspohon']);
                $d['bjr'] = ($v['bjr'] == '' ? '0' : $v['bjr']);
                $d['tahunproduksi'] = $v['tahunproduksi'];
                $d['periode'] = $v['periode'];
                $data[] = $d;
            }
        }
        return $data;
    }

    function getDataBjrV2($user)
    {
        $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
        $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));

        $like_conditions = array_map(function ($prefix) {
            return "kebun_5bjr.kodeorg like '$prefix%'";
        }, $orgAcsess);

        $where_like = implode(" OR ", $like_conditions);
        if (count($orgAcsess) > 0) {
            $where = "WHERE  (" . $where_like . ") and kebun_5bjr.periode = DATE_FORMAT(CURRENT_DATE, '%Y-%m')";
        } else {
            $where = "WHERE kebun_5bjr.kodeorg like '" . $user['lokasitugas'] . "%' and kebun_5bjr.periode = DATE_FORMAT(CURRENT_DATE, '%Y-%m')";
        }

        $data = array();
        $q = "SELECT kebun_5bjr.*,setup_blok.indukblok, ROUND(AVG(kebun_5bjr.bjr), 2) AS avg_bjr 
          FROM " . $this->db->dbname . ".kebun_5bjr 
          LEFT JOIN " . $this->db->dbname . ".setup_blok ON kebun_5bjr.kodeorg = setup_blok.kodeorg 
          {$where} 
          GROUP BY setup_blok.indukblok";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $d['kodeorg'] = $v['indukblok'];
                $d['kelaspohon'] = ($v['kelaspohon'] == '' ? '0' : $v['kelaspohon']);
                $d['bjr'] = $v['avg_bjr'];
                $d['tahunproduksi'] = $v['tahunproduksi'];
                $d['periode'] = $v['periode'];
                $data[] = $d;
            }
        }
        return $data;
    }
    
    // function getDataBjrV2($user)
    // {
    //     $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
    //     $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));
    //     // $orgAcsess = array_unique($unitAsistensi);
    //     // Buat kondisi LIKE untuk setiap elemen dalam array orgaccess
    //     $like_conditions = array_map(function ($prefix) {
    //         return "kebun_5bjr.kodeorg like '$prefix%'";
    //     }, $orgAcsess);
    //     // Gabungkan semua kondisi LIKE dengan operator OR
    //     $where_like = implode(" OR ", $like_conditions);
    //     if (count($orgAcsess) > 0) {
    //         $where = "WHERE  (" . $where_like . ") and kebun_5bjr.periode = DATE_FORMAT(CURRENT_DATE, '%Y-%m')";
    //     } else {
    //         $where = "WHERE kebun_5bjr.kodeorg like '" . $user['lokasitugas'] . "%' and kebun_5bjr.periode = DATE_FORMAT(CURRENT_DATE, '%Y-%m')";
    //     }
    //     $data = array();
    //     $q = "SELECT kebun_5bjr.*, setup_blok.indukblok FROM " . $this->db->dbname . ".kebun_5bjr LEFT JOIN " . $this->db->dbname . ".setup_blok ON kebun_5bjr.kodeorg = setup_blok.kodeorg {$where}";
    //     // echo $q;
    //     $r = $this->fetchdata($q);
    //     if (count($r) > 0) {
    //         // $data = $r;
    //         foreach ($r as $k => $v) {

    //             $d['kodeorg'] = $v['indukblok'];
    //             $d['kelaspohon'] = ($v['kelaspohon'] == '' ? '0' : $v['kelaspohon']);
    //             $d['bjr'] = ($v['bjr'] == '' ? '0' : $v['bjr']);
    //             $d['tahunproduksi'] = $v['tahunproduksi'];
    //             $d['periode'] = $v['periode'];
    //             $data[] = $d;
    //         }
    //     }
    //     return $data;
    // }
}
