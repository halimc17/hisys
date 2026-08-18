<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_gps extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("gps_location");
        $d['key'] = array("username");
        $this->prepareDB = $d;
    }

    function init()
    {
        $result = false;
        foreach ($this->prepareDB['table'] as $tbl) {
            if (!$this->table_exists($tbl)) {
                $this->response['status'] = 400;
                $this->response['error'] = true;
                $this->response['message'] = "Tabel " . $tbl . " belum tersedia!";
                $result = $this->response;
                break;
            }
        }
        return $result;
    }

    function getGpsInterval(){
        $q = "select * from " . $this->db->dbname . ".gps_interval";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $d['interval'] = (int)$v['interval'];
                $d['distance'] = (int)$v['distance'];
                $d['enableupload'] = $v['enableupload'] == '1' ? true : false;
                $data[] = $d;
            }
        }
        return  $data;
    }

    public function syncData($user)
    {

        $data['username'] = $user['username'];
        $data['latitude'] = explode(",", $this->post('latitude'));
        $data['longitude'] = explode(",", $this->post('longitude'));
        $data['altitude'] = explode(",", $this->post('altitude'));
        $data['distance'] = explode(",", $this->post('distance'));
        $data['devicename'] = $user['api_key'];
        $data['tanggal'] = explode(",", $this->post('lastupdate'));
        for ($i = 0; $i < count($data['latitude']); $i++) {
            // $lastID++;
            $dataArr = array(
                // 'id' => $lastID,
                'username' => $data['username'],
                'latitude' => $data['latitude'][$i],
                'logitude' => $data['longitude'][$i],
                'altitude' => $data['altitude'][$i],
                'distance' => $data['distance'][$i],
                'devicename' => $data['devicename'],
                'tanggal' => substr($data['tanggal'][$i], 0, 10),
                'waktu' => substr($data['tanggal'][$i], 11, 19),
                'updatetime' => substr($data['tanggal'][$i], 0, 10) . ' ' . substr($data['tanggal'][$i], 11, 19)
            );
            // print_r($dataArr);

            $dataInsert[$i] = $this->query_insert($dataArr, $this->db->dbname . ".gps_location");
        }

        // console
        if ($this->uri->segments[5] == 'load') {
            return $data;
        }

        $qexec = $this->exec($dataInsert);
        if ($qexec) {
            $this->response['error'] = false;
            $this->response['message'] = "Success";
        }
        return $this->response;
    }

    private function lastId()
    {
        $q = "select * from " . $this->db->dbname . ".gps_location ";
        // echo $q;
        $r = $this->fetchdata($q);
        return count($r);
    }

}
