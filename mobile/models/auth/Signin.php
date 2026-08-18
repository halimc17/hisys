<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Signin extends OWL_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->errorArr = $GLOBALS['errorArr'];
        $this->backup_ini = parse_ini_file($this->appath . "lib/backup.ini");
        $this->expireddate = strtotime("+".$this->backup_ini['MAXLIFETIME']." seconds");
        $this->backup_start = strtotime(str_replace('.',':',$this->backup_ini['BACKUP_START']));
        $this->backup_end = strtotime(str_replace('.',':',$this->backup_ini['BACKUP_END']));
        
        $this->senstiveCase = FALSE;
        $this->load->lib("Encryption");
        $this->userMod = $this->model('Setup_user');
    }

    //Get Api Key and 
    private function getApi_key($apikey = "")
    {
        $result = FALSE;
        $q = "select id,username,key_api as api_key,uuid,datelogin,explogin,is_login from " . $this->db->dbname . ".api_key where key_api = '" . $apikey . "' limit 1";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $dataApi = array_shift($r);
            $sens = $this->sensitiveCaseKey('username', $dataApi['username']);
            if (date("Y-m-d H:i:s", strtotime($dataApi['explogin'])) >= date("Y-m-d H:i:s")) {
                if ($dataApi['is_login'] != '1'){
                    $dataUpdate = array(
                        'is_login' => '0'
                    );
                    $this->update($dataUpdate, "api_key", $sens['key'] . "='" . $sens['value'] . "' and key_api!='" . $apikey . "'");
                }
                $dataUpdate2 = array(
                    'is_login' => '1',
                    'explogin' => date("Y-m-d H:i:s", $this->expireddate)
                );
                $this->update($dataUpdate2, "api_key", $sens['key'] . "='" . $sens['value'] . "' and key_api='" . $apikey . "'");
                $result = $dataApi;
            } else {
                $this->response['error'] = TRUE;
                $this->response['message'] = $this->errorArr['session_cookie_expired'];
                $this->response['result'] = "EXPIRED";
            }
        } else {
            $this->response['error'] = TRUE;
            $this->response['message'] = $this->errorArr['session_cookie_expired'];
        }
        return $result;
    }
    function sensitiveCaseKey($key, $value)
    {
        $result = array(
            'key' => $key,
            'value' => $value
        );
        if (!$this->senstiveCase) {
            $result['key'] = 'LOWER(' . $key . ')';
            $result['value'] = strtolower($value);
        }

        return $result;
    }
    //check API_KEY is Ready and Is Login
    private function check_apikey($session)
    {
        $result = FALSE;
        $token = null;
        if(!empty($session)){
            $headers = $session;
        }else{
            $headers = apache_request_headers();
        }
        if (isset($headers['api_key'])) {
            if ($dataApi = $this->getApi_key($headers['api_key'])) {
                $sens = $this->sensitiveCaseKey('namauser', $dataApi['username']);
                // $q = "select lastupdate from " . $this->db->dbname . ".user where " . $sens['key'] . " = '" . $sens['value'] . "'";
                // $r = $this->fetchdata($q);
                $r = $this->userMod->selectData("karyawanid,kodeorg,hak,status", "WHERE " . $sens['key'] . " = '" . $sens['value'] . "'");
                if (count($r) > 0) {
                    $dataUser = array_shift($r);
                    if ($dataUser['status'] == '1') {
                        $this->response['error'] = FALSE;
                        $result = array_merge($dataApi, $dataUser);
                    } else {
                        $this->response['error'] = TRUE;
                        $this->response['message'] = $this->errorArr['user_not_found'];
                    }
                }else{
                    $this->response['error'] = FALSE;
                    $result = $dataApi;
                }
            }
        }
        if (!$result) {
            $this->response['error'] = TRUE;
            $this->response['message'] = $this->errorArr['unauthorized_continue_uri'];
        }
        return $result;
    }
    public function auth($device = '', $type = ''){
        $result = FALSE;
        if (isset($device) and $device == '' and isset($_SESSION['standard']['username'])) {
            $session = $_SESSION['standard'];
        } elseif (isset($device) and $device == 'API') {
            $session = apache_request_headers();
        }
        if ($data = $this->check_apikey($session)) {
            $result = $data;
        } elseif ($type == 'LOGIN' and  $this->post('username') != "" and $this->post('password') != "") {
            if ($data = $this->createApi_key($this->post('uuid'), $this->post('username'), $this->post('password'))) {
                //is_login
                // echo $data;
                $result = $data;
            } else {

                $this->response['error'] = TRUE;
                $this->response['message'] = $this->errorArr['user_not_found'];
            }
        } else {
            $this->response['error'] = TRUE;
            $this->response['message'] = $this->errorArr['user_not_found'];
        }
        
        return $result;
    }
    //Login

    private function loginApi($username = "", $password = "")
    {
        $result = FALSE;
        $sens = $this->sensitiveCaseKey('namauser', $username);
        // $q = "select namauser as username,karyawanid,kodeorg,hak,status from " . $this->db->dbname . ".user where " . $sens['key'] . " = '" . $sens['value'] . "' and password = 
        // CONCAT('*', UPPER(SHA1(UNHEX(SHA1('" . $password . "'))))); ";
        // $r = $this->fetchdata($q);
        $r = $this->userMod->selectData("namauser as username,karyawanid,kodeorg,hak,status", "WHERE " . $sens['key'] . " = '" . $sens['value'] . "' and password = CONCAT('*', UPPER(SHA1(UNHEX(SHA1('" . $password . "'))))); ");
        if (count($r) > 0) {
            $data = array_shift($r);
            if ($data['status'] == '1') {
                $result = $data;
            } else {
                $this->response['message'] = $this->errorArr['user_not_found'];
            }
        }
        return $result;
    }

    private function createApi_key($uuid = "", $username = "", $password = "")
    {

        $result = FALSE;
        if ($uuid == "") {
            // $uuid = get_current_user();
            $uuid = strtolower(preg_replace('/\s+/', '',$this->user_agent->platform));//get_current_user();
            if($this->user_agent->is_browser){
                $uuid .= "_".strtolower(preg_replace('/\s+/', '',$this->user_agent->browser));
            }
        }
        // print_r($this->getApi_key($newApi_key));
        if ($username != "" and $password != "") {
            $sens = $this->sensitiveCaseKey('username', $username);
            $newApi_key = $this->lib->Encryption->encryptionProduct($sens['value'] . $uuid . $password);
            if ($dataApi = $this->getApi_key($newApi_key)) {
                $loginData = $this->loginApi($username, $password);
                if ($loginData) {
                    $arrRes = array(
                        'id' => $dataApi['id'],
                        'username' => $dataApi['username'],
                        'api_key' => $dataApi['api_key'],
                        'uuid' => $dataApi['uuid'],
                        'datelogin' => $dataApi['datelogin'],
                        'explogin' => $dataApi['explogin'],
                        'is_login' => $dataApi['is_login']
                    );
                    $result = array_merge($arrRes, $loginData);
                    $this->update_history($result, $newApi_key);
                } else {
                    $this->response['error'] = TRUE;
                    $this->response['message'] = $this->errorArr['user_not_found'];
                }
            } else {
                if ($user = $this->loginApi($username, $password)) {
                    $appname = $this->post('appname');
                    if(empty($this->post('appname'))){
                        $appname = APP_NAME;
                    }
                    // $version = $this->post('version');
                    // if(empty($this->post('version'))){
                    //     $version = VERSION;
                    // }
                    $version = $this->post('versi');
                    if(empty($this->post('versi'))){
                        $version = VERSION;
                    }
                    $data = array(
                        'username' => $user['username'],//$username,
                        'uuid' => $uuid,
                        'appname' => $appname,
                        'versi' => $version,
                        'key_api' => $newApi_key,
                        'datelogin' => date("Y-m-d H:i:s"),
                        'explogin' => date("Y-m-d H:i:s", $this->expireddate),
                        'is_login' => '1'
                    );
                    if (!empty($this->response['result']) and $this->response['result'] == "EXPIRED") {
                        $sens = $this->sensitiveCaseKey('username', $username);
                        $res = $this->update($data, 'api_key', $sens['key'] . "='" . $sens['value'] . "' and key_api='" . $newApi_key . "'");
                    } else {
                        $res = $this->insert($data, 'api_key');
                    }
                    if ($res) {
                        $dataUpdate = array(
                            'is_login' => '0'
                        );
                        $sens = $this->sensitiveCaseKey('username', $username);
                        $this->update($dataUpdate, "api_key", $sens['key'] . "='" . $sens['value'] . "' and key_api!='" . $newApi_key . "'");
                    }
                    $this->update_history($data, $newApi_key);
                    $result = $this->createApi_key($uuid, $username, $password);
                } else {
                    $this->response['error'] = TRUE;
                    $this->response['message'] = $this->errorArr['user_not_found'];
                }
            }
        }
        return $result;
    }
    function update_history($data, $api_key)
    {
        if (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        $hostname = getenv('HOSTNAME');
        if (!$hostname) $hostname = trim(`hostname`);
        if (!$hostname) $hostname = exec('echo $HOSTNAME');
        if (!$hostname) $hostname = preg_replace('#^\w+\s+(\w+).*$#', '$1', exec('uname -a'));
        $data = array(
            'lastip' => $ip,
            'key_api' => $api_key,
            'lastcomp' => $hostname,
            'lastuser' => $data['username'],
            'appname' => $data['appname'],
            'versi' => $data['versi']
        );
        $this->insert($data, 'login_history');
    }
    private function error_message()
    {
    }

    public function response()
    {
        return $this->response;
    }
    public function session($dataAuth = array())
    {
        // print_r($dataAuth);
        // exit();
        $session = $this->session_def();
        $session['standard'] = $this->standard($dataAuth);
        $session['empl'] = $this->empl($dataAuth);
        $priv = $this->privilege($dataAuth,$session['empl']);
        $session['org'] = $this->org($dataAuth,$session['empl']);
        $session['orgaccess'] = $priv['orgaccess'];
        $session['gudang'] = $this->gudang($dataAuth,$session['orgaccess']);
        $session['allpriv'] = $priv['allpriv'];
        $session['priv'] = $priv['priv'];
        
        return $session;
    }
    function session_def()
    {
        $session = array();
        $session['standard'] = array();
        $session['empl'] = array();
        $session['org'] = array();
        $session['orgaccess'] = array();
        $session['gudang'] = array();
        $Privilege = $this->model('Privilege');
        $tipeakses = $Privilege->tipeakses();
        if (count($tipeakses) > 0) {
            $session['security'] = 'on';
        } else {
            $session['security'] = 'off';
        }
        $session['language'] = ($this->post('language')) ? $this->post('language') : 'ID';
        $q = "select legend," . $session['language'] . " from " . $this->db->dbname . ".bahasa ";
        $bhs = $this->fetchdata($q);
        $session['lang'] = array();
        foreach ($bhs as $v) {
            $session['lang'][$v['legend']] = $v[$session['language']];
        }
        $session['allpriv'] = "";//array();
        $session['priv'] = (object)array();
        $session['MAXLIFETIME'] = (int) $this->backup_ini['MAXLIFETIME'];
        $session['BACKUP_START'] = date('H:i',$this->backup_start);
        $session['BACKUP_END'] = date('H:i',$this->backup_end);
        $session['DIE'] = $this->expireddate;
        if (count($tipeakses) > 0) {
            $session['access_type'] = $tipeakses[0]['access_name'];
        }
        return $session;
    }
    function privilege($param = array(),$empl=array())
    {
        $data = (object) $param;

        $s['allpriv'] = "";//array();
        $s['priv'] = (object)array();
        $Privilege = $this->model('Privilege');
        $orgaccess = $Privilege->orgdetail($data->username)?:array();
        if(!in_array($data->kodeorg,$orgaccess)){
            $orgaccess[] = $data->kodeorg;
        }
        if(!in_array($empl['lokasitugas'],$orgaccess)){
            $orgaccess[] = $empl['lokasitugas'];
        }
        if(!in_array($empl['lokasitugas_temp'],$orgaccess)){
            $orgaccess[] = $empl['lokasitugas_temp'];
        }
        $s['orgaccess'] = $orgaccess;
        $r = $Privilege->auth("where namauser='" . $data->username . "' and status=1");
        $dataPriv = array();
        $dataPrivDetail = array();
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $dataPriv[$k] = (int) $v['menuid'];
                $dataPrivDetail[$v['menuid'] . 'detail'] = (int) $v['detail'];
            }
            $s['allpriv'] = implode(",", $dataPriv);
            $s['priv'] = (object)array_merge($dataPriv, $dataPrivDetail);
            
        }
        return $s;
    }
    function get_token($api_key = "")
    {
        $auth = $this->model("Authorize");
        return $auth->token($api_key);
    }
    function standard($param = array())
    {
        $data = (object) $param;
        $s = array();
        $s['access_id'] = "";
        $s['id'] = @$data->id;
        $s['username'] = @$data->username;
        $s['api_key'] = @$data->api_key;
        $s['token'] = $this->get_token(@$data->api_key);
        $s['uuid'] = @$data->uuid;
        $s['datelogin'] = @$data->datelogin;
        $s['explogin'] = @$data->explogin;
        $s['access_level'] = @$data->hak;
        $s['lastupdate'] = @$data->datelogin;
        $s['userid'] = @$data->karyawanid;
        $s['location'] = @$data->kodeorg;
        $s['status'] = @$data->status;
        $s['logged'] = '1';
        $FilterUser = array(
            'username'=>$data->username,
            'userid' => $data->karyawanid
        );
        $Privilege = $this->model('Privilege');
        $admin = $Privilege->getAdmin($FilterUser);
        $s['is_admin'] = $admin['is_admin'];
        $s['is_superadmin'] = $admin['is_superadmin'];

        return $s;
    }
    function org($param = array(),$empl=array())
    {
        $data = (object) $param;
        $s = array();
        $organisasi = $this->model('Organisasi');
        if(!empty($data->lokasitugas)){
            $lokasitugas = $data->lokasitugas;
        }
        if(!empty($empl['lokasitugas'])){
            $lokasitugas = $empl['lokasitugas'];
        }
        $r = $organisasi->selectdata("where kodeorganisasi='" . $lokasitugas . "' limit 1");
        if (count($r) > 0) {
            $org = (object) array_shift($r);
            $s['kodeorganisasi'] = $org->kodeorganisasi;
            $s['namaorganisasi'] = $org->namaorganisasi;
            $s['tipeorganisasi'] = $org->tipe;
            $s['alamat'] = $org->alamat;
            $s['telepon'] = $org->telepon;
            $s['wilayahkota'] = $org->wilayahkota;
            $s['induk'] = $org->induk;
            $s['tipeinduk'] = $org->tipe;
        }
        $Periode = $this->model('Periode');
        $r = $Periode->akuntantsi("where kodeorg='" . $lokasitugas . "' and tutupbuku=0");
        if (count($r) > 0) {
            $org = (object) array_shift($r);
            $tmpPeriod = date("-", "", $org->periode);
            $tmpPeriod = str_replace("/", "", $tmpPeriod);
            $s['period']['start'] = str_replace("-", "", $org->tanggalmulai);
            $s['period']['end'] = str_replace("-", "", $org->tanggalsampai);
            $s['period']['bulan'] = substr($tmpPeriod, 4, 2);
            $s['period']['tahun'] = substr($tmpPeriod, 0, 4);
        }
        $r = $organisasi->holding();
        if (count($r) > 0) {
            $org = (object) array_shift($r);
            $s['holding'] = trim($org->namaorganisasi);
        }
        return $s;
    }
    function empl($param = array())
    {
        $data = (object) $param;
        $Datakaryawan = $this->model('Setup_datakaryawan');
        $r = $Datakaryawan->selectData("where karyawanid='" . $data->karyawanid . "' limit 1");
        if (count($r) > 0) {
            $datakaryawan = (object) array_shift($r);
            $organisasi = $this->model('Organisasi');
            $kodeOrg = array();
            if(!empty($datakaryawan->kodeorganisasi)){
                $kodeOrg[] = $datakaryawan->kodeorganisasi;
            }
            if(!empty($datakaryawan->lokasitugas)){
                $kodeOrg[] = $datakaryawan->lokasitugas;
            }
            $r = $organisasi->selectdata("where kodeorganisasi in ('" .implode("','",$kodeOrg) . "')");
            if (count($r) > 0) {
                foreach($r as $k=>$org){
                    // echo $datakaryawan->kodeorganisasi."-".$org->kodeorganisasi."<br>";
                    if($datakaryawan->lokasitugas == $org['kodeorganisasi']){
                        $datakaryawan->tipelokasitugas = $org['tipe'];
                    }elseif($datakaryawan->kodeorganisasi == $org['kodeorganisasi']){
                        $datakaryawan->namaorganisasi = $org['namaorganisasi'];
                        
                    }
                }
                
                $jabatan = $this->model('Setup_jabatan');
                $r = $jabatan->selectdata("where kodejabatan='" . $datakaryawan->kodejabatan . "' limit 1");
            
                if (count($r) > 0) {
                    $jbt = (object) array_shift($r);
                    $datakaryawan->namajabatan = $jbt->namajabatan;
                }
            }
            
            $s = array();
            $s['name'] = @$datakaryawan->namakaryawan;
            $s['sex'] = @$datakaryawan->jeniskelamin;
            $s['birthday'] = @$datakaryawan->tanggallahir;
            $s['birthplace'] = @$datakaryawan->tempatlahir;
            $s['address'] = @$datakaryawan->alamataktif;
            $s['noktp'] = @$datakaryawan->noktp; //'identity num/no ktp',
            $s['nopaspor'] = @$datakaryawan->nopaspor;
            $s['nationality'] = @$datakaryawan->warganegara;
            $s['religion'] = @$datakaryawan->agama;
            $s['statusperkawinan'] = @$datakaryawan->statusperkawinan; //'status pajak/k1=kawin 1 anak',
            $s['kodejabatan'] = @$datakaryawan->kodejabatan;
            $s['jabatan'] = @$datakaryawan->namajabatan ?? @$datakaryawan->kodejabatan; //'nama jabatan',
            $s['bagian'] = @$datakaryawan->bagian; //'Id departement',
            $s['tipekaryawan'] = @$datakaryawan->tipekaryawan; //'bentuk ikatan',
            $s['kodeorganisasi'] = @$datakaryawan->kodeorganisasi; //'unit pemberi kerja',
            $s['namaorganisasi'] = @$datakaryawan->namaorganisasi; //'unit pemberi kerja',
            $s['lokasitugas'] = @$datakaryawan->lokasitugas; //'lokasi kerja',
            $s['tipelokasitugas'] = @$datakaryawan->tipelokasitugas;
            $s['subbagian'] = @$datakaryawan->subbagian;
            //ORGIGIN
            $s['kodeorganisasi_temp'] = @$datakaryawan->kodeorganisasi; //'unit pemberi kerja',
            $s['namaorganisasi_temp'] = @$datakaryawan->namaorganisasi; //'unit pemberi kerja',
            $s['lokasitugas_temp'] = @$datakaryawan->lokasitugas; //'lokasi kerja',
            $s['tipelokasitugas_temp'] = @$datakaryawan->tipelokasitugas;
            $s['subbagian_temp'] = @$datakaryawan->subbagian;
            //END
            $s['poh'] = @$datakaryawan->lokasipenerimaan; //'point of hire',
            $s['signdate'] = @$datakaryawan->tanggalmasuk; //'tgl masuk',
            $s['resigndate'] = @$datakaryawan->tanggalkeluar; //'tgl keluar',
            $s['sistemgaji'] = @$datakaryawan->sistemgaji; //'employment is payroll active or not/employee scorrs',
            $s['email'] = @$datakaryawan->email;
            $s['phone'] = @$datakaryawan->noteleponrumah;
            $s['regional'] = '';
            try{
                $ASSIST = $this->model('Setup_asistensi');
                $dataAssist = $ASSIST->getDataAsistensiMobile($s);
                if(count($dataAssist) > 0){
                    $karAsis = array_column($dataAssist,'karyawanid');
                    if(in_array($data->karyawanid,$karAsis)){
                        $key = array_search($data->karyawanid,$karAsis);
                        $s['kodeorganisasi_temp'] = $dataAssist[$key]['induk'];
                        $s['lokasitugas_temp'] = $dataAssist[$key]['kodeorg'];
                        $s['subbagian_temp'] = $dataAssist[$key]['subbagian'];
                        $kodeOrg = array();
                        if(!empty($s['kodeorganisasi_temp'])){
                            $kodeOrg[] = $s['kodeorganisasi_temp'];
                        }
                        if(!empty($s['lokasitugas_temp'])){
                            $kodeOrg[] = $s['lokasitugas_temp'];
                        }
                        if(!empty($s['subbagian_temp'])){
                            $kodeOrg[] = $s['subbagian_temp'];
                        }
                        $r = $organisasi->selectdata("where kodeorganisasi in ('" .implode("','",$kodeOrg) . "')");
                        if (count($r) > 0) {
                            foreach($r as $k=>$org){
                                if($s['lokasitugas_temp'] == $org['kodeorganisasi']){
                                    $s['tipelokasitugas_temp'] = $org['tipe'];
                                }elseif($s['kodeorganisasi_temp'] == $org['kodeorganisasi']){
                                    $s['namaorganisasi_temp'] = $org['namaorganisasi'];
                                }
                            }
                        }
                    }
                }
            }catch(Exception $e){
                
            }
            $r = $organisasi->regional("where kodeunit='" . $datakaryawan->lokasitugas . "' LIMIT 1");
            if (count($r) > 0) {
                $reg = (object) array_shift($r);
                $s['regional'] = $reg->regional;
            }
        }
        return $s;
    }
    function gudang($data,$orgAccess=array())
    {
        $r = array();
        $s = array();
        if(!empty($data->lokasitugas)){
            $lokasitugas = $data->lokasitugas;
        }
        $organisasi = $this->model('Organisasi');
        $orgGudang = $organisasi->gudang($orgAccess);
        $Periode = $this->model('Periode');
        if (count($orgGudang) > 0) {
            $kodeorganisasi = array_column($orgGudang, 'kodeorganisasi');
            $r = $Periode->akuntantsi("where kodeorg in ('" . implode("','", $kodeorganisasi) . "')");
        }
        if (count($r) > 0) {
            foreach ($r as $v) {
                $bulan = date("m",strtotime($v['periode'].'-01'));
                $tahun = date("Y",strtotime($v['periode'].'-01'));
                $s[$v['kodeorg']]['start'] = date("Ymd",strtotime($v['tanggalmulai']));
                $s[$v['kodeorg']]['end'] = date("Ymd",strtotime($v['tanggalsampai']));
                $s[$v['kodeorg']]['bulan'] = $bulan;
                $s[$v['kodeorg']]['tahun'] = $tahun;
            }
        }
        return $s;
    }
}
