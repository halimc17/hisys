<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Blok extends OWL_Model
{
    function selectQuery(array $pageLimit = array(), $whr = '')
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".setup_blok {$whr}
		ORDER BY kodeorg ASC " . $limitPage;
        // echo $q;
        $data = $this->query($q, 'ASSOC');
        return $data;
    }
    function selectdata(array $pageLimit = array(), $whr = '')
    {
        $result = array();
        $data = $this->selectQuery($pageLimit, $whr);
        if ($data and $data->rowCount() > 0) {
            $result = $this->fetch($data);
        }
        return $result;
    }

    function getDataBlok($where)
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".setup_blok {$where}  
		ORDER BY kodeorg ASC ";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }


        // $q = "SELECT kodeorg,tahuntanam,statusblok,luasareaproduktif,kelaspohon,jumlahpokok,topografi FROM " . $this->db->dbname . ".setup_blok
        // WHERE (kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%' and statusblok <> 'TB') or (statusblok = 'TB')";
        // $r = $this->fetchdata($q);
        // if (count($r) > 0) {
        //     // $data = $r;
        //     foreach ($r as $k => $v) {
        //         $d['kodeorg'] = $v['kodeorg'];
        //         $d['tahuntanam'] = ($v['tahuntanam'] == '' ? 0 : $v['tahuntanam']);
        //         $d['statusblok'] = ($v['statusblok'] == '' ? 'TM' : $v['statusblok']);
        //         $d['kegiatangroup'] = ($v['statusblok'] == '' ? null : ($v['statusblok'] == "TM" ? "'PNN','TM'" : ($v['statusblok'] == "TBM" ? "'TBM','LC'" : strval($v['statusblok']))));
        //         $d['luasareaproduktif'] = ($v['luasareaproduktif'] == '' ? 0 : $v['luasareaproduktif']);
        //         $d['kelaspohon'] = ($v['kelaspohon'] == '' ? 0 : (int)$v['kelaspohon']);
        //         $d['jumlahpokok'] = (int)$v['jumlahpokok'];
        //         $d['topografi'] = ($v['topografi'] == '' ? '0' : $v['topografi']);
        //         // $d['kemandoran'] ="";
        //         // $d['latitude'] ="";
        //         // $d['longitude'] = "";

        //         $data[] = $d;
        //     }
        // }

        return $data;
    }

    function getDataBlokInduk()
    {
        $data = array();
        $q = "SELECT indukblok, namaindukblok FROM " . $this->db->dbname . ".organisasi GROUP BY indukblok, namaindukblok";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }

        return $data;
    }

    function getDataBlokIMobile($where)
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".setup_blok {$where}  
		ORDER BY kodeorg ASC ";

        $indukBlok = $this->getDataBlokInduk();
        $indukBlokKey = array_column($indukBlok, 'namaindukblok', 'indukblok');
        // $q = "SELECT kodeorg,tahuntanam,statusblok,luasareaproduktif,kelaspohon,jumlahpokok,topografi FROM " . $this->db->dbname . ".setup_blok
        // WHERE (kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%' and statusblok <> 'TB') or (statusblok = 'TB')";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {


                $d['kodeorg'] = $v['kodeorg'];
                $d['namaindukblok'] = isset($indukBlokKey[$v['indukblok']]) ? $indukBlokKey[$v['indukblok']] : null;

                $d['tahuntanam'] = ($v['tahuntanam'] == '' ? 0 : $v['tahuntanam']);
                $d['statusblok'] = ($v['statusblok'] == '' ? 'TM' : $v['statusblok']);
                $d['kegiatangroup'] = ($v['statusblok'] == '' ? null : ($v['statusblok'] == "TM" ? "'PNN','TM'" : ($v['statusblok'] == "TBM" ? "'TBM','LC'" : strval($v['statusblok']))));
                $d['luasareaproduktif'] = ($v['luasareaproduktif'] == '' ? 0 : $v['luasareaproduktif']);
                $d['kelaspohon'] = ($v['kelaspohon'] == '' ? 0 : (int)$v['kelaspohon']);
                $d['jumlahpokok'] = (int)$v['jumlahpokok'];
                $d['topografi'] = ($v['topografi'] == '' ? '0' : $v['topografi']);
                $d['indukblok'] = ($v['indukblok'] == '' ? '0' : $v['indukblok']);
                // $d['kemandoran'] ="";
                // $d['latitude'] ="";
                // $d['longitude'] = "";

                $data[] = $d;
            }
        }

        return $data;
    }

    function getDataBlokMobile($user)
    {
        $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
        $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));
        // $orgAcsess = array_unique($unitAsistensi);
        // Buat kondisi LIKE untuk setiap elemen dalam array orgaccess
        $like_conditions = array_map(function ($prefix) {
            return "kodeorg LIKE '$prefix%'";
        }, $orgAcsess);
        // Gabungkan semua kondisi LIKE dengan operator OR
        $where_like = implode(" OR ", $like_conditions);

        if (count($orgAcsess) > 0) {
            $where = "WHERE (" . $where_like . ")";
        } else {
            $where = "WHERE kodeorg like '" . $user['lokasitugas'] . "%'";
        }
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".setup_blok {$where} AND status = 'A' 
		ORDER BY kodeorg ASC ";
        // echo $q;
        $indukBlok = $this->getDataBlokInduk();
        $indukBlokKey = array_column($indukBlok, 'namaindukblok', 'indukblok');
        // $q = "SELECT kodeorg,tahuntanam,statusblok,luasareaproduktif,kelaspohon,jumlahpokok,topografi FROM " . $this->db->dbname . ".setup_blok
        // WHERE (kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%' and statusblok <> 'TB') or (statusblok = 'TB')";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $d['kodeorg'] = $v['kodeorg'];
                $d['tahuntanam'] = ($v['tahuntanam'] == '' ? 0 : $v['tahuntanam']);
                $d['statusblok'] = ($v['statusblok'] == '' ? 'TM' : $v['statusblok']);
                $d['luasareaproduktif'] = ($v['luasareaproduktif'] == '' ? 0 : $v['luasareaproduktif']);
                $d['luasareanonproduktif'] = ($v['luasareanonproduktif'] == '' ? 0 : $v['luasareanonproduktif']);
                $d['kelaspohon'] = ($v['kelaspohon'] == '' ? 0 : (int)$v['kelaspohon']);
                $d['jumlahpokok'] = (int)$v['jumlahpokok'];
                $d['topografi'] = ($v['topografi'] == '' ? '0' : $v['topografi']);
                $d['indukblok'] = ($v['indukblok'] == '' ? '0' : $v['indukblok']);
                $d['lc'] = ($v['lc'] == '' ? 0 : $v['lc']);
                $d['luasbloking'] = ($v['luasbloking'] == '' ? 0 : $v['luasbloking']);
                $d['namaindukblok'] = isset($indukBlokKey[$v['indukblok']]) ? $indukBlokKey[$v['indukblok']] : null;
                // $d['kemandoran'] ="";
                // $d['latitude'] ="";
                // $d['longitude'] = "";

                $data[] = $d;
            }
        }

        return $data;
    }

    function getLuasBlok($where)
    {
        $data = "";
        $q = "SELECT sum(luasareaproduktif) as luasblok FROM " . $this->db->dbname . ".setup_blok " . $where;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r[0]['luasblok'];
        }
        return $data;
    }
}
