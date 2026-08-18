<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_tph extends OWL_Model
{
    function selectQuery(array $pageLimit = array(), $where = '')
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5tph {$where}
		ORDER BY kodeorg ASC " . $limitPage;

        $data = $this->query($q, 'ASSOC');
        return $data;
    }
    function selectdata(array $pageLimit = array(), $where = '')
    {
        $result = array();
        $data = $this->selectQuery($pageLimit, $where);
        if ($data and $data->rowCount() > 0) {
            $result = $this->fetch($data);
        }
        return $result;
    }

    function getTph($where = "")
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_5tph {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        // echo $q;
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $latitude = str_replace(',', '.', $v['latitude']);
                $logitude = str_replace(',', '.', $v['logitude']);
                $d['kode']    = $v['kode'];
                $d['keterangan']    = $v['keterangan'];
                $d['kodeorg']        = $v['kodeorg'];
                $d['kodetphbesar']        = $v['kodetphbesar'];
                $d['latitude'] = ($latitude !== "" && is_numeric($latitude)) ? $latitude : "0.0";
                $d['longitude'] = ($logitude !== "" && is_numeric($latitude)) ? $logitude : "0.0";
                $d['luas']            = $v['luas'];
                if (strlen($d['kode']) == 11) {
                    $data[] = $d;
                }
            }
        }
        return $data;
    }

    function getTphV2($user)
    {
        $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
        $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));
        // Buat kondisi LIKE untuk setiap elemen dalam array orgaccess
        $like_conditions = array_map(function ($prefix) {
            return "kodeorg LIKE '$prefix%'";
        }, $orgAcsess);
        // $where = "WHERE kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%'";
        $where_like = implode(" OR ", $like_conditions);
        
        if (count($orgAcsess) > 0) {
            $where = "WHERE (" . $where_like . ")";
        } else {
            $where = "WHERE kodeorg = '" . $user['lokasitugas'] . "'";
        }
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_5tph {$where}";
        // echo $q;

        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $latitude = str_replace(',', '.', $v['latitude']);
                $logitude = str_replace(',', '.', $v['logitude']);
                $d['kode']    = $v['kode'];
                $d['keterangan']    = $v['keterangan'];
                $d['kodeorg']        = $v['kodeorg'];
                $d['kodetphbesar']        = $v['kodetphbesar'];
                $d['latitude'] = ($latitude !== "" && is_numeric($latitude)) ? $latitude : "0.0";
                $d['longitude'] = ($logitude !== "" && is_numeric($latitude)) ? $logitude : "0.0";
                $d['luas']            = $v['luas'];
                // if (strlen($d['kode']) == 11) {
                $data[] = $d;
                // }
            }
        }
        return $data;
    }

    function getTphBesar($where = "")
    {
        $data = array();
        $q = "select divisi, notph from " . $this->db->dbname . ".kebun_5tphbesar {$where}";
        // $q = "select divisi, notph, createdby from " . $this->db->dbname . ".kebun_5tphbesar WHERE status = '1'";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    function getTphBesarV2($user)
    {
        $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
        $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));
        // Buat kondisi LIKE untuk setiap elemen dalam array orgaccess
        $like_conditions = array_map(function ($prefix) {
            return "kodeorg = '$prefix'";
        }, $orgAcsess);
        // $where = "WHERE kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%'";
        
        $where_like = implode(" OR ", $like_conditions);

        if (count($orgAcsess) > 0) {
            $where = "WHERE (" . $where_like . ") and status = '1'";
        } else {
            $where = "WHERE kodeorg = '" . $user['lokasitugas'] . "'";
        }

        $data = array();
        $q = "select divisi, notph from " . $this->db->dbname . ".kebun_5tphbesar {$where}";
        // $q = "select divisi, notph, createdby from " . $this->db->dbname . ".kebun_5tphbesar WHERE status = '1'";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }


    // private function getAsistensi()
    // {
    //     $qAsistensi = "SELECT kodeorgasal, kodeorgtujuan FROM " . $this->db->dbname . ".kebun_5asistensi    
    //     WHERE tanggalsampai >= CURDATE() and kodeorgasal = '" . $this->user['lokasitugas'] . "' and posting = '1'";
    // }
}
