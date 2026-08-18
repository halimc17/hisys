<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MSensusProduksi extends OWL_Model
{
    function __construct()
    {
        parent::__construct();
        $this->prepareDB = [
            'table' => ["kebun_aktifitas_mobile", "kebun_2sensusproduksi"],
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

    public function addHeader($user, $type)
    {
        $data = [
            'notransaksi'   => $this->post('notransaksi'),
            'tipetransaksi' => $type,
            'tanggal'       => $this->post('tanggal'),
            'kodeorg'       => $this->post('kodeorg') ? substr($this->post('kodeorg'), 0, 4) : $user['lokasitugas'],
            'divisi'        => $this->post('kodeorg') ?: $user['subbagian'],
            'gangcode'      => $this->post('gangcode') ?: '',
            'nikmandor'     => $this->post('mandor') ?: '',
            'nikmandor1'    => $this->post('mandor1') ?: '',
            'nikasisten'    => $this->post('asisten') ?: '',
            'kerani'        => $this->post('kerani') ?: '',
            'deviceid'      => $user['uuid'],
            'createby'      => $user['userid'],
            'createtime'    => $this->post('lastupdate'),
            'updateby'      => $user['userid'],
        ];

        if ($this->uri->segments[5] == 'load') {
            return $data;
        }

        $aktifitas = $this->getAktifitas("WHERE notransaksi = '{$data['notransaksi']}' LIMIT 1");

        if ($aktifitas && $aktifitas->rowCount() == 0) {
            $qexec = $this->insert($data, "{$this->db->dbname}.kebun_aktifitas_mobile");
            if ($qexec) {
                return $this->responseSuccess($this->getAktifitasData($data['notransaksi']));
            }
        } else {
            $dt = $aktifitas->fetch();
            if ($dt->syn == "1") {
				return $this->responseError("Warning : Data sudah tersyncronize.", 403);
			}elseif ($dt->flag == "1") {
                return $this->responseError("Warning : Data Transaksi sudah terposting.", 403);
            } else {
                $this->reSyntransMobile($this->post('notransaksi'));
                return $this->responseSuccess("Success Re-Syncronized", [
					'notransaksi' => $dt->notransaksi,
					'no_syncronized' => $dt->nosync,
					'tanggal' => $dt->tanggal
				]);
            }
        }

        return $this->responseError("Failed! : Gagal Insert Header", 409);
    }

    public function addDetail($user)
    {
        $data = [
            'method' => $this->post('method'),
            'notransaksi' => $this->post('notransaksi'),
            'kodeorg' => explode(",", $this->post('blok')),
            'tph' => explode(",", $this->post('tph') ?: ''),
            'nik' => explode(",", $this->post('karyawan')),
            'kemandoran' => explode(",", $this->post('kemandoran') ?: ''),
            'tglpanen' => $this->post('tanggal'),
            'sesi' => explode(",", $this->post('sesi')),
            'noreferensi' => explode(",", $this->post('absensi')),
            'idjenis' => explode(",", $this->post('idjenis')),
            'kodedenda' => explode(",", $this->post('kode')),
            'nilai' => explode(",", $this->post('nilai')),
            'baris' => explode(",", $this->post('baris')),
            'pokok' => explode(",", $this->post('pokok')),
            'arah' => explode(",", $this->post('arah') ?: ''),
            'lastupdate' => explode(",", $this->post('lastupdate') ?: ''),
            'updateby' => $user['userid']
        ];

        $dataHeader = $this->getAktifitas("WHERE notransaksi = '{$data['notransaksi']}' LIMIT 1");

        if ($dataHeader && count($dataHeader) != 0) {
            $dataInsert = [];
            foreach ($data['kodeorg'] as $i => $kodeorg) {
                $dataInsert[] = $this->query_insert([
                    'notransaksi' => $data['notransaksi'],
                    'kodeorg' => $kodeorg,
                    'tph' => $data['tph'][$i] ?: '',
                    'nik' => $data['nik'][$i],
                    'kemandoran' => $data['kemandoran'][$i] ?: '',
                    'tglpanen' => $data['tglpanen'],
                    'sesi' => $data['sesi'][$i] ?: '1',
                    'noreferensi' => $data['notransaksi'],
                    'tipedetail' => 'sensusproduksi',
                    'nourut' => $data['baris'][$i] . '-' . $data['pokok'][$i],
                    'idjenis' => $data['idjenis'][$i],
                    'kodedenda' => $data['kodedenda'][$i],
                    'nilai' => $data['nilai'][$i],
                    'updateby' => $data['updateby'],
                    'arahsensus' => $data['arah'][$i] ?: '',
                    'lastupdate' => $data['lastupdate'][$i] ?: ''
                ], "{$this->db->dbname}.kebun_2sensusproduksi");
            }

            if ($this->uri->segments[5] == 'load') {
                return $dataInsert;
            }

            if ($this->exec($dataInsert)) {
                return $this->responseSuccess([
                    'notransaksi' => $data['notransaksi'],
                    'no_syncronized' => $this->post('no_syncronized'),
                    'tanggal' => $this->post('tanggal')
                ]);
            }
        } else {
            return $this->responseError("Failed! : No Transaksi '{$data['notransaksi']}' Pada Header Tidak ditemukan", 404);
        }

        return $this->responseError("Failed! Insert Data : No Transaksi '{$data['notransaksi']}'", 409);
    }

    public function checkdatarow()
    {
        $notransaksi = $this->post('notransaksi');
        // $jmldetail = $this->post('jumlah_detail') + $this->post('jumlah_baris_pokok');
        $jmldetail = $this->post('jumlah_baris_pokok_detail');
        $dataHeader = $this->getAktifitas("WHERE notransaksi = '$notransaksi' LIMIT 1");

        if ($dataHeader && count($dataHeader) > 0) {
            $str = "SELECT notransaksi FROM {$this->db->dbname}.kebun_2sensusproduksi WHERE notransaksi = '$notransaksi'";
            
            if ($this->uri->segments[5] == 'load') {
                return $str;
            }

            $datacheck = $this->query($str);
            if ($datacheck->rowCount() == $jmldetail) {
                $this->update(['syn' => '1'], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='$notransaksi'");
                return $this->responseSuccess([
                    'notransaksi' => $notransaksi,
                    'no_syncronized' => $this->post('no_syncronized'),
                    'tanggal' => $this->post('tanggal')
                ], "Sinkronisasi Data Telah Selesai.");
            } else {
                return $this->responseError("Failed! : Data Syncronized ({$datacheck->rowCount()}/$jmldetail) Belum Lengkap, Mohon Syncronized Ulang", 409);
            }
        } else {
            return $this->responseError("Failed! : No Transaksi '$notransaksi' Pada Header Tidak ditemukan", 404);
        }
    }

    private function getAktifitasData($notransaksi)
    {
        $dt = $this->getAktifitas("WHERE notransaksi = '$notransaksi' LIMIT 1")->fetch();
        return [
            'notransaksi' => $dt->notransaksi,
            'no_syncronized' => $dt->nosync,
            'tanggal' => $dt->tanggal
        ];
    }

    private function responseError($message, $status = 400)
    {
        return [
            'status' => $status,
            'error' => true,
            'message' => $message
        ];
    }

    private function responseSuccess($data, $message = "Success")
    {
        return array_merge([
            'status' => 200,
            'error' => false,
            'message' => $message
        ], $data);
    }

    public function getAktifitas($where = '')
    {
        $q = "SELECT * FROM {$this->db->dbname}.kebun_aktifitas_mobile $where";
        return $this->query($q);
    }

    private function getDetail($where = '')
    {
        $q = "SELECT * FROM {$this->db->dbname}.kebun_2sensusproduksi $where";
        return $this->query($q);
    }

    private function reSyntransMobile($notransaksi)
    {
        try {
            $this->update(['syn' => '0'], "{$this->db->dbname}.kebun_aktifitas_mobile", "notransaksi='$notransaksi'");
            $this->delete("{$this->db->dbname}.kebun_2sensusproduksi", "notransaksi='$notransaksi'");
        } catch (PDOException $e) {
            $this->responseError("Failed! : Resyncronized ({$e->getMessage()}) !!", 409);
        }
    }
}
