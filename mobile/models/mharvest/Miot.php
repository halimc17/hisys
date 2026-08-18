<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Miot extends OWL_Model
{
    function __construct()
    {
        $this->prepareDB = [
            'table' => ["api_key", "setup_gateway"],
            'key' => ["notransaksi"]
        ];
    }

    function init()
    {
        foreach ($this->prepareDB['table'] as $tbl) {
            if (!$this->table_exists($tbl)) {
                return $this->responseError("Tabel $tbl belum tersedia!", 400);
            }
        }
        return false;
    }

    public function checkToken()
    {
        $token = $this->post('token') ?: '';
        if (!$token) return $this->responseError("Parameter Token Harus diisi", 400);

        $resData = $this->fetchdata("SELECT * FROM {$this->db->dbname}.setup_gateway WHERE client_secret='$token' OR client_id ='$token'  LIMIT 1");
        if (!$resData) return $this->responseError("Client Secret $token Tidak ditemukan", 404);
        if ($resData[0]['isactive'] == 0) return $this->responseError("Token '{$token}' Tidak Aktif", 400);
        $username = $resData[0]['client_id'];
        $client_secret = $resData[0]['client_secret'];

        $expired_date = new DateTime($resData[0]['expired']);
        if ($expired_date <= new DateTime()) return $this->responseError("Token has expired", 400);

        $qkey_api = "SELECT * FROM {$this->db->dbname}.api_key WHERE username='$username' AND uuid='$client_secret' LIMIT 1";
        $reskey_api = $this->fetchdata($qkey_api);

        // $appname = $resData[0]['client_name'];
        // $apky = md5($username . $appname);
        // $versi = 1;
        if ($this->uri->segments[5] == 'load') {
            return $qkey_api;
        }
        if (!$reskey_api or empty($reskey_api)) {
            return $this->responseError("Connection Failed, Lisensi API tidak tersedia, mohon hubungi Admin ", 400);
            // $strkey = "INSERT INTO {$this->db->dbname}.api_key (username, uuid, appname, key_api, datelogin, explogin, is_login, versi)
            //            VALUES ('$username', '$token', '$appname', '$apky', NOW(), (NOW() + INTERVAL 1 DAY), 1, '$versi')";
        } else {
            // $strkey = "UPDATE {$this->db->dbname}.api_key SET datelogin=NOW(), explogin=(NOW() + INTERVAL 1 DAY), appname='$appname', versi='$versi', is_login=1 
            //            WHERE username='$username' AND uuid='$token'";
            // $strkey = "";
        }

        

        // try {
        //     $this->owlPDO->exec($strkey);
        // } catch (PDOException $e) {
        //     return $this->responseError("Tidak berhasil " . (!$reskey_api ? "Insert" : "Update") . " api_key, " . $e->getMessage(), 400);
        // }

        return $this->responseSuccess("Data Detail", $resData[0]);
    }

    private function responseError($message, $status)
    {
        return [
            'status' => $status,
            'error' => true,
            'message' => $message
        ];
    }

    private function responseSuccess($message, $data)
    {
        return [
            'status' => 200,
            'error' => false,
            'message' => $message,
            'data' => $data
        ];
    }
}

