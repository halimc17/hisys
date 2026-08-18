<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_datakaryawan extends OWL_Model
{
    function selectQuery(array $pageLimit = array(), $where = '')
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".datakaryawan {$where} " . $limitPage;
        $data = $this->query($q, 'ASSOC');
        return $data;
    }
    function selectdatakaryawan(array $pageLimit = array(), $where = '')
    {
        $result = array();
        $data = $this->selectQuery($pageLimit, $where);
        if ($data and $data->rowCount() > 0) {
            $result = $this->fetch($data);
        }
        return $result;
    }
    function selectOpt($where = "")
    {
        $data = array();
        $q = "select * from {$this->db->dbname}.datakaryawan {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[$v['karyawanid']] = $v['namakaryawan'];
            }
        }
        return $data;
    }
    function selectOptDetail($where = "")
    {
        $data = array();
        $q = "select * from {$this->db->dbname}.datakaryawan {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[$v['karyawanid']] = $v;
            }
        }
        return $data;
    }
    function selectDataMobile($where = "")
    {
        $data = array();
        $jabatan = $this->model('Setup_jabatan')->selectataAktif();
        /* Result :
            $jabatan =  array(
                array(
                    'kodejabatan' => '4',
                    'namajabatan' => 'STAFF'
                ),
                array(
                    'kodejabatan' => '5',
                    'namajabatan' => '????'
                )
            );
        */
        // $jabatan = array_column($a, 'namajabatan', 'kodejabatan');
        /* Result :
            $jabatan =  
                array(
                    '4' => 'STAFF',
                    '5' => '????'
                );
        */
        $jabatanKey = array_column($jabatan, 'namajabatan', 'kodejabatan');

        $q = "select a.* from {$this->db->dbname}.datakaryawan a {$where}";
        $r = $this->fetchdata($q);

        if (count($r) > 0) {

            foreach ($r as $k => $v) {
                // var_dump($v['kodejabatan']);
                // var_dump($jabatan[$v['kodejabatan']]);
                $d['karyawanid'] = $v['karyawanid'];
                $d['nik'] = ($v['nik'] == '' ? 'NaN' : $v['nik']);
                $d['lokasitugas'] = ($v['lokasitugas'] == '' ? null : $v['lokasitugas']);
                $d['subbagian'] = ($v['subbagian'] == '' ? null : $v['subbagian']);
                $d['namakaryawan'] = ($v['namakaryawan'] == '' ? 'NaN' : $v['namakaryawan']);
                $d['tipekaryawan'] = ($v['tipekaryawan'] == '' ? '0' : $v['tipekaryawan']);
                $d['namajabatan'] =  isset($jabatanKey[$v['kodejabatan']]) ? $jabatanKey[$v['kodejabatan']] : null;
                //($v['namajabatan'] == '' ? 'NaN' : $v['namajabatan']);
                $d['kodejabatan'] = ($v['kodejabatan'] == '' ? 'NaN' : $v['kodejabatan']);
                $d['tanggalkeluar'] = ($v['tanggalkeluar'] == '' ? 'NaN' : $v['tanggalkeluar']);

                if ($d['nik'] != null && $d['namakaryawan'] != null && $d['tipekaryawan'] != null && $d['namajabatan'] != null) {
                    $data[] = $d;
                }
            }
        }
        return $data;
    }

    function selectDataMobileRev($user)
    {
        $data = array();
        $jabatan = $this->model('Setup_jabatan')->selectataAktif();
        $asistensi = $this->model('Setup_asistensi')->getDataMobileAsistensi($user);
        $unitAsistensi = $this->model('Setup_asistensi')->getKodeOrgAsistensiMobile($user['userid']);
        $orgAcsess = array_unique(array_merge($user['orgaccess'], $unitAsistensi));

        // $orgAcsess = array_unique($unitAsistensi);

        /* Result :
            $jabatan =  array(
                array(
                    'kodejabatan' => '4',
                    'namajabatan' => 'STAFF'
                ),
                array(
                    'kodejabatan' => '5',
                    'namajabatan' => '????'
                )
            );
        */
        // $jabatan = array_column($a, 'namajabatan', 'kodejabatan');
        /* Result :
            $jabatan =  
                array(
                    '4' => 'STAFF',
                    '5' => '????'
                );
        */

        // Buat kondisi LIKE untuk setiap elemen dalam array orgaccess
        $like_conditions = array_map(function ($prefix) {
            return "lokasitugas LIKE '$prefix%'";
        }, $orgAcsess);
        // Gabungkan semua kondisi LIKE dengan operator OR
        $where_like = implode(" OR ", $like_conditions);

        if (count($orgAcsess) > 0) {
            $whereUnitAsis = "OR(" . $where_like . ")";
        }

        $jabatanKey = array_column($jabatan, 'namajabatan', 'kodejabatan');
        $karIdAsist = array_column($asistensi, 'karyawanid');
        $inAsis = "";

        if (count($karIdAsist) > 0) {
            $inAsis = "OR karyawanid in ('" . implode("','", $karIdAsist) . "')";
        }


        $q = "select a.* from {$this->db->dbname}.datakaryawan a 
        where (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='" . date('Y-m-d') . "') 
        and  (((a.kodeorganisasi = '" . $user['kodeorganisasi'] . "' and a.lokasitugas= '" . $user['lokasitugas'] . "') " . $inAsis .")" . $whereUnitAsis . ")";
        // echo $q;    
        $r = $this->fetchdata($q);

        if (count($r) > 0) {

            foreach ($r as $k => $v) {
                // var_dump($v['kodejabatan']);
                // var_dump($jabatan[$v['kodejabatan']]);

                $lokasitugas = $v['lokasitugas'];
                $kodejabatan = $v['kodejabatan'];
                // if (in_array($v['karyawanid'], $karIdAsist)) {
                //     //$asistensi = {["tipe"=>"BKM","karyawanid"=>"000000001"],..];
                //     foreach ($asistensi as $key => $value) {
                //         $lokasitugas = $value['kodeorgtujuan'];
                //         if ($value['tipe'] = "BKM") {
                //             $kodejabatan = "193"; //TENAGA KERJA perawatan
                //         } else if ($value['tipe'] = "PNN") {
                //             $kodejabatan = "191"; //TENAGA KERJA PANEN
                //         }
                //     }
                // }
                $d['karyawanid'] = $v['karyawanid'];
                $d['nik'] = ($v['nik'] == '' ? 'NaN' : $v['nik']);
                $d['lokasitugas'] = ($lokasitugas == '' ? null : $lokasitugas);
                $d['subbagian'] = ($v['subbagian'] == '' ? null : $v['subbagian']);
                $d['namakaryawan'] = ($v['namakaryawan'] == '' ? 'NaN' : $v['namakaryawan']);
                $d['tipekaryawan'] = ($v['tipekaryawan'] == '' ? '0' : $v['tipekaryawan']);
                $d['namajabatan'] =  isset($jabatanKey[$v['kodejabatan']]) ? $jabatanKey[$v['kodejabatan']] : null;
                //($v['namajabatan'] == '' ? 'NaN' : $v['namajabatan']);
                $d['kodejabatan'] = ($kodejabatan == '' ? 'NaN' : $kodejabatan);
                $d['tanggalkeluar'] = ($v['tanggalkeluar'] == '' ? 'NaN' : $v['tanggalkeluar']);
                if ($d['nik'] != null && $d['namakaryawan'] != null && $d['tipekaryawan'] != null && $d['namajabatan'] != null) {
                    $data[] = $d;
                }
            }
        }

        return $data;
    }
    function selectDataProfile($where = "", $user)
    {
        $result = false;
        $data = array();
        $q = "select a.*, d.namajabatan from {$this->db->dbname}.datakaryawan a "
            . "LEFT JOIN " . $this->db->dbname . ".sdm_5jabatan d on a.kodejabatan = d.kodejabatan "
            . "{$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
            $result = array(
                "username" => $user['username'],
                "karyawanid" => $data[0]['karyawanid'],
                "namakaryawan" => $data[0]['namakaryawan'],
                "namakaryawan2" => '',
                "nik" => $data[0]['nik'],
                "tanggallahir" => $data[0]['tanggallahir'],
                "sistemgaji" => $data[0]['sistemgaji'],
                "tanggalmasuk" => $data[0]['tanggalmasuk'],
                "tipekaryawan" => $data[0]['tipekaryawan'],
                "regional" => '',
                "tipeUser" => 'KEBUN',
                "logged" => '1',
                "pt" => $data[0]['kodeorganisasi'],
                "bagian" => $data[0]['bagian'],
                "lokasitugas" => $data[0]['lokasitugas'],
                "subbagian" => $data[0]['subbagian'],
                "kodegolongan" => $data[0]['kodegolongan'],
                "kodejabatan" => $data[0]['kodejabatan'],
                "namajabatan" => $data[0]['namajabatan'],
                "userid" => $user['id'],
                "api_key" => $user['api_key'],
                "datelogin" => $user['datelogin'],
                "explogin" => $user['explogin'],
                "photo" => ''
            );
            // $this->response['error'] = false;
            // $this->response['status'] = 200;
        }
        return $result;
    }
    function selectData($where = "")
    {
        $data = array();
        $q = "select * from {$this->db->dbname}.datakaryawan {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        // var_dump($data);
        return $data;
    }
    function actived()
    {
        $data = array();
        $r = $this->selectData("where kodeorganisasi = 'OWL' and tanggalkeluar = '0000-00-00'  order by photo DESC");
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }
    function selectJabatan($where = "")
    {
        $data = array();
        $q = "select * from {$this->db->dbname}.sdm_5jabatan {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }
    function selectDataBagianKaryawan()
    {
        $data = array();
        $q = "select karyawanid,bagian from {$this->db->dbname}.datakaryawan ";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[$v['karyawanid']] = $v['bagian'];
            }
        }
        return $data;
    }


    function selectPemanenNm($whr)
    {
        $data = array();
        $q = "SELECT namakaryawan FROM " . $this->db->dbname . ".datakaryawan " . $whr;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data[0]['namakaryawan'] ?? " ";
    }

    // function getDataKaryawan($whr)
    // {
    //     $data = array();
    //     $q = "select karyawanid,namakaryawan from " . $this->db->dbname . ".datakaryawan " . $whr;
    //     $r = $this->fetchdata($q);
    //     if (count($r) > 0) {
    //         $data = $r;
    //     }
    //     return $data[0]['namakaryawan'];
    // }

    function getKaryawanidInUnitOrgAccess($orgAcsessunit)
    {

        $data = array();
        if (count($orgAcsessunit) == 0) {
            return $data;
        }
        $where =  "a.kodeorganisasi IN ('" . implode("','", $orgAcsessunit) . "')";
        $q = "SELECT b.karyawanid from {$this->db->dbname}.user_orgdetail AS a LEFT JOIN {$this->db->dbname}.user AS b ON a.namauser = b.namauser WHERE {$where} AND b.namauser NOT LIKE '%owl%'";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {

                $data[] = $v['karyawanid'];
            }
        }
        return array_unique($data);
    }
}
