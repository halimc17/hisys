<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Setup_asistensi extends OWL_Model
{
    private function getData($where = "")
    {
        $q = " SELECT * from " . $this->db->dbname . ".kebun_5asistensi {$where}";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    private function getDataDetail($where = "")
    {
        $q = " SELECT * from " . $this->db->dbname . ".kebun_5asistensi_dt {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
    function getDataMobileAsistensi($user)
    {
        $where = "where `posting` = '1' AND kodeorgtujuan= '" . $user['lokasitugas'] . "' and ('" . date('Y-m-d') . "' BETWEEN tanggal AND tanggalsampai) ";
        $asistensi = $this->getData($where);
        $id = array_column($asistensi, 'id');
        $tipetrans = array_column($asistensi, 'tipetrans', 'id');
        $kodeorgtujuan = array_column($asistensi, 'kodeorgtujuan', 'id');
        $dataIdKaryawan = array();
        if (count($id) > 0) {
            $dataIdKaryawan = $this->getDataDetail("where id in ('" . implode("','", $id) . "')");
        }
        if (count($dataIdKaryawan) > 0) {
            foreach ($dataIdKaryawan as $v) {
                $d = array();
                $d['tipe'] = @$tipetrans[$v['id']];
                $d['karyawanid'] = $v['karyawanid'];
                $d['kodeorgtujuan'] = @$kodeorgtujuan[$v['id']];
                $data[] = $d;
            }
        }
        return $data;
    }

    function getDataAsistensiMobile($user)
    {
        $data = array();
        $q = "SELECT b.karyawanid,
            c.induk,
            a.kodeorgtujuan as kodeorg,
            a.divisitujuan as subbagian,
            a.tanggal,
            a.tanggalsampai
        FROM " . $this->db->dbname . ".kebun_5asistensi a
            INNER JOIN " . $this->db->dbname . ".kebun_5asistensi_dt b ON a.id = b.id
            INNER JOIN " . $this->db->dbname . ".organisasi c ON a.kodeorgtujuan = c.kodeorganisasi
            INNER JOIN " . $this->db->dbname . ".datakaryawan d ON d.karyawanid = b.karyawanid
        WHERE a.tanggalsampai >= CURRENT_DATE()
            AND (d.tanggalkeluar = '0000-00-00'
            OR d.tanggalkeluar < CURRENT_DATE())
            AND a.tanggal <= CURRENT_DATE() 
            AND a.kodeorgasal = '" . $user['lokasitugas'] . "'";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = array(
                    'karyawanid' => $v['karyawanid'],
                    'induk' => $v['induk'],
                    'kodeorg' => $v['kodeorg'],
                    'subbagian' => $v['subbagian'],
                    'tanggal' => $v['tanggal'],
                    'tanggalsampai' => $v['tanggalsampai']
                );
            }
        }
        return $data;
    }

    function getKodeOrgAsistensiMobile($karyawanid)
    {
        $data = array();
        $q = "SELECT b.karyawanid,
            c.induk,
            a.kodeorgtujuan as kodeorg,
            a.divisitujuan as subbagian,
            a.tanggal,
            a.tanggalsampai
        FROM " . $this->db->dbname . ".kebun_5asistensi a
            INNER JOIN " . $this->db->dbname . ".kebun_5asistensi_dt b ON a.id = b.id
            INNER JOIN " . $this->db->dbname . ".organisasi c ON a.kodeorgtujuan = c.kodeorganisasi
            INNER JOIN " . $this->db->dbname . ".datakaryawan d ON d.karyawanid = b.karyawanid
        WHERE a.tanggalsampai >= CURRENT_DATE()
            AND (d.tanggalkeluar = '0000-00-00'
            OR d.tanggalkeluar < CURRENT_DATE())
            AND a.tanggal <= CURRENT_DATE() 
            AND b.karyawanid = '" . $karyawanid . "' GROUP BY a.divisitujuan";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = $v['kodeorg'];
            }
        }
        return $data;
    }
}
