<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Menuharvest extends OWL_Model
{
    function selectdata($where = "")
    {
        $data = array();
        $q = "select * from " . $this->db->dbname . ".menu {$where} order by parent,urut";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $bar) {
                $d = array();
                $d['id']            = $bar['id'];
                $d['induk']         = $bar['parent'];
                $d['type']          = $bar['type'];
                $d['class']         = $bar['class'];
                $d['name']          = $bar['caption'];
                $d['caption2']      = $bar['caption2'];
                $d['caption3']      = $bar['caption3'];
                $d['action']        = $bar['action'];
                $d['kodesegment']   = $bar['f_kodesegment'];
                $d['url']             = $bar['f_url'];
                $d['icon_path']     = $bar['f_icon_path'];
                $d['urut']          = $bar['urut'];
                $d['lastupdate']    = $bar['lastupdate'];
                $d['klasifikasi']   = $bar['f_clasifikasi'];
                $data[] = $d;
            }
        }
        return $data;
    }
    function privilage($where = "")
    {
        $data = false;
        $q = "select menuid from " . $this->db->dbname . ".auth {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    private function findChild($parentid, $arr)
    {
        $result = array();
        foreach ($arr as $k => $v) {
            //echo $v['induk']." == ".$parentid."<br>";
            if ($v['induk'] == $parentid) {
                $result[] = $v;
                //print_r($result);
                $result2 = $this->findChild($v['id'], $arr);
                if (count($result2) > 0) {
                    $result = array_merge($result, $result2);
                }
            }
        }
        return  $result;
    }
    function usermenu($username)
    {
        $data = false;
        $privilage = $this->privilage("where namauser='" . $username . "' and status = '1' ");
        if (count($privilage) > 0) {
            $listMenuid = array_column($privilage, "menuid");
            $listMenuid = implode("','", $listMenuid);
            $mainmenu = $this->selectdata("where id IN ('" . $listMenuid . "') and f_hide = '0'");
            $data_temp = array();
            $datafilter = array();
            $datadetail = array();
            if (count($mainmenu) > 0) {
                foreach ($mainmenu as $k => $d) {
                    if ($d['type'] == 'master') {
                        $data_temp[] = $d;
                    } else {
                        $datadetail[] = $d;
                    }
                }
                if (count($datadetail) > 0 and count($data_temp) > 0) {
                    foreach ($data_temp as $k => $v) {
                        $klasifikasi = $v['klasifikasi'];
                        unset($data_temp[$k]['klasifikasi']);
                        $data[$klasifikasi][] = $data_temp[$k];
                        $datafilter = $this->findChild($v['id'], $datadetail);
                        if (count($datafilter) > 0) {
                            $data[$v['klasifikasi']] = array_merge($data[$v['klasifikasi']], $datafilter);
                        }
                    }
                }
            }
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : User Tidak memiliki Authorized!!";
        }
        return $data;
    }
    function loadMenu($username, $parentId)
    {
        $data = false;
        $privilage = $this->privilage("where namauser='" . $username . "' and status = '1' ");
        if (count($privilage) > 0) {
            $listMenuid = array_column($privilage, "menuid");
            $listMenuid = implode("','", $listMenuid);
            $mainmenu = $this->selectdata("where parent = '" . $parentId . "' and id IN ('" . $listMenuid . "') and (f_hide = '0' or hide='0')");
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : User Tidak memiliki Authorized!!";
        }
        return $mainmenu;
    }
    function jumper($username, $jumper)
    {
        $data = false;
        $privilage = $this->privilage("where namauser='" . $username . "' and status = '1' ");
        if (count($privilage) > 0) {
            $listMenuid = array_column($privilage, "menuid");
            $listMenuid = implode("','", $listMenuid);
            $mainmenu = $this->selectdata("where caption2 like '%" . $jumper . "%' and id IN ('" . $listMenuid . "') and f_hide = '0'");
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : User Tidak memiliki Authorized!!";
        }
        return $mainmenu;
    }
    function refresh($username, $action)
    {
        $data = false;
        $privilage = $this->privilage("where namauser='" . $username . "' and status = '1' ");
        if (count($privilage) > 0) {
            $listMenuid = array_column($privilage, "menuid");
            $listMenuid = implode("','", $listMenuid);
            $mainmenu = $this->selectdata("where action = '" . $action . "' and id IN ('" . $listMenuid . "') and f_hide = '0'");
        } else {
            $this->response['status'] = 409;
            $this->response['error'] = true;
            $this->response['message'] = "Failed! : User Tidak memiliki Authorized!!";
        }
        return $mainmenu;
    }
}
