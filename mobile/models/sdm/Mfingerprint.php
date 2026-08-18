<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mfingerprint extends OWL_Model
{

    function __construct()
    {
        $d['table'] = array("fingerprint_template", "att_log", "att_pegawai", "att_device", "setup_gateway");
        $d['key'] = array("notransaksi");
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

    public function dataFinger($user)
    {
        $data = [
            'sn'        => explode(",", $this->post('serialnumber')),
            'karyawanid' => explode(",", $this->post('nik')),
            'template'  => explode(",", $this->post('template')),
            'sensor'  => explode(",", $this->post('sensor')),
            'datenow'   => date("Y-m-d H:i:s"),
        ];

        $dataHeader = $this->getDataFinger("WHERE template IN ('" . implode("','", array_unique($data['template'])) . "')");

        if ($dataHeader && count($dataHeader) > 0) {
            foreach ($dataHeader as $header) {
                $this->delete($this->db->dbname . ".fingerprint_template", "sn='" . $header['sn'] . "' AND karyawanid='" . $header['karyawanid'] . "'");
            }
        }

        try {
            if (count($data['sn']) > 0) {
                foreach ($data['sn'] as $index => $sn) {
                    $dataDetail = [
                        'sn' => $sn,
                        'karyawanid'    => $data['karyawanid'][$index],
                        'template'      => $data['template'][$index],
                        'sensor'        => $data['sensor'][$index] ?? "0",
                        'id_jari'       => $data['idjari'][$index] ?? 0,
                        'upload_time'   => $data['datenow'],
                        'kebun'         => $this->post('kebun'),
                        'updateby'      => $user['userid'],
                    ];
                    $dataInsert[$index] = $this->query_insert($dataDetail, $this->db->dbname . ".fingerprint_template");
                }

                if ($this->uri->segments[5] == 'load') {
                    return $dataInsert;
                }
                $qexec = $this->exec($dataInsert);
                if ($qexec) {
                    $this->response['error']    = false;
                    $this->response['message']  = "Data Template Berhasil Di Upload";
                    $this->response['status']   = 'SUCCESS';
                }
            }
        } catch (PDOException $e) {
            $this->response['status']   = 409;
            $this->response['error']    = true;
            $this->response['message']  = "Failed! : Data Template Gagal Di Upload (" . $e->getMessage() . ") !!";
        }
        return $this->response;
    }

    public function getDataFinger($where = '')
    {
        $query = "SELECT * FROM {$this->db->dbname}.fingerprint_template {$where}";
        $result = $this->fetchdata($query);

        return $result ?: [];
    }

    public function insertAbsen()
    {

        // if ($this->uri->segments[5] != 'load' || $this->uri->segments[5] != 'send') {
        //     $this->response['status']   = 409;
        //     $this->response['error']    = true;
        //     $this->response['message']  = "Parameter Salah";
        //     return $this;
        // }

        $data = [
            // 'sn' => explode(",", $this->post('sn')),
            'sn' => $this->post('sn'),
            'karyawanid' => explode(",", $this->post('nik')),
            // 'idfinger' => explode(",", $this->post('idfinger')),
            'idfinger' => $this->post('idfinger'),
            'datetime' => explode(",", $this->post('datetime')),
            'inoutmode' => explode(",", $this->post('inoutmode')),
            'latitude' => explode(",", $this->post('latitude')),
            'longitude' => explode(",", $this->post('longitude')),
            'datenow' => date("Y-m-d H:i:s"),
        ];
        $res = [];
        try {
            // $owlPDO->beginTransaction();
            foreach ($data['karyawanid'] as $key => $id) {
                $datafinger[] = [
                    'sn' => $data['sn'],
                    'pin' => $data['idfinger'],
                    'scan_date' => $data['datetime'][$key],
                    'verify_mode' => '', // This value needs to be assigned correctly
                    'inoutmode' => $data['inoutmode'][$key],
                ];

                $dataAtt_log = $this->getAtt_log("WHERE sn='" . $data['sn'] . "' and scan_date='" . $data['datetime'][$key] . "' and pin='" . $data['karyawanid'][$key] . "'");

                if ($dataAtt_log && count($dataAtt_log) > 0) {
                    $this->delete($this->db->dbname . ".att_log", "sn='" . $data['sn'] . "' AND scan_date='" . $data['datetime'][$key] . "'AND pin='" . $data['karyawanid'][$key] . "'");
                }

                // Insert into att_log table
                $str = "INSERT INTO " . $this->db->dbname . ".att_log (`sn`, `scan_date`, `pin`, `inoutmode`, `latitude`, `longitude`)
                          VALUES ('" . $data['sn'] . "','" . $data['datetime'][$key] . "','" . $id . "','" . $data['inoutmode'][$key] . "','" . $data['latitude'][$key] . "','" . $data['longitude'][$key] . "')";

                $datacheck = $this->query($str);


                // Check att_pegawai existence
                $str = "SELECT * FROM " . $this->db->dbname . ".att_pegawai WHERE sn='" . $data['sn'] . "' AND pin='" . $id . "'";
                $res = $this->query($str);

                if ($res->rowCount() == 0) {
                    $karyawan = $this->model('Setup_datakaryawan')->selectDataMobile("WHERE karyawanid='{$id}' LIMIT 1")[0] ?? null;
                    $nik = ($karyawan['nik'] == 'NaN' || $karyawan['nik'] == 'null' || $karyawan['nik'] == null || $karyawan['nik'] == 'NULL') ? '' : $karyawan['nik'];

                    $query = "INSERT INTO {$this->db->dbname}.att_pegawai (`sn`, `pin`, `namafp`, `nik`, `karyawan`)
                              VALUES ('{$data['sn']}', '{$id}', '{$karyawan['namakaryawan']}', '{$nik}', '{$id}')";
                    $this->query($query);
                }


                // Check att_device existence
                $str = "SELECT * FROM " . $this->db->dbname . ".att_device WHERE sn='" . $data['sn'] . "'";

                $res = $this->getatt_device("WHERE sn='" . $data['sn'] . "' ");

                if (count($res) == 0) {

                    $str = "SELECT * FROM " . $this->db->dbname . ".att_device WHERE device_name LIKE 'OWL Fingerprint%' ORDER BY device_name DESC";
                    $res = $this->query($str);

                    // $res = fetchdata($str);
                    if (count($res) > 1) {
                        $nomor = addZero(substr($res[0]['device_name'], -2), 2);
                    } else {
                        $nomor = '01';
                    }

                    $query = "INSERT INTO " . $this->db->dbname . ".att_device (`sn`,`device_name`)
                              VALUES ('" . $data['sn'] . "','OWL Fingerprint " . $nomor . "')";
                    $res = $this->query($query);
                }
            }

            $this->response['nik']      = count($data['karyawanid']);
            $this->response['status']   = 'SUCCESS';
            $this->response['idfinger'] = $this->post('idfinger');
            $this->response['datetime'] = $this->post('datetime');
            $this->response['sn']       = $this->post('sn');
            $this->response['message']  = "Data Absensi Berhasil Di Upload";
        } catch (PDOException $e) {
            header('HTTP/1.1 304 DATA TRANSFER FAILED');
            $this->response['status'] = 'FAILED';
            $this->response['message'] = $e->getMessage();
            $this->response['timeupload'] = $data['datenow'];
        }

        return $this->response;
    }


    public function getAtt_log($where = '')
    {
        $query = "SELECT * FROM {$this->db->dbname}.att_log {$where}";
        $result = $this->fetchdata($query);

        return $result ?: [];
    }


    private function getatt_device($where = '')
    {
        $query = "SELECT * FROM {$this->db->dbname}.att_device {$where}";
        $result = $this->fetchdata($query);

        return $result ?: [];
    }
}
