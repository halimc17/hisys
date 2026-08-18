<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_masterdata extends OWL_Model
{
    function getdata($user)
    {
        $data = array();

        $data['karyawan'] = $this->model('Setup_datakaryawan')->selectDataMobile("where (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='" . date('Y-m-d') . "') and  a.kodeorganisasi = '" . $user['kodeorganisasi'] . "'");
        $data['barang'] = $this->model('Master_brg')->getproduk();
        $data['organisasi'] = $this->model('Setup_org')->getDataMobile();
        $data['kebun_5mandor'] = $this->model('Setup_mandor')->getdata($user);
        $data['blok'] = $this->model('Blok')->getDataBlokMobile("WHERE (kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%' and statusblok <> 'TB') or (statusblok = 'TB')");
        $data['setup_tph'] = $this->model('Setup_tph')->getTph("WHERE kodeorg like '" . substr($user['lokasitugas'], 0, 4) . "%'");
        $data['tphbesar'] = $this->model('Setup_tph')->getTphBesar("WHERE kodeorg = '" . substr($user['lokasitugas'], 0, 4) . "' and status = '1'");
        $data['kebun_5tipepanen'] = $this->model('Mpanen')->setup_tipepanen("WHERE (kodeorg = '" . substr($user['lokasitugas'], 0, 4) . "' OR kodeorg = 'GLOBAL') AND fungsi = 1 AND aktif = 1 ORDER BY kodejenis");
        $data['setup_hama'] = $this->model('Setup_hama')->getHama();
        $data['gudangtransaksi'] = $this->model('Setup_gudangtxn')->getData("WHERE afdeling like '" . substr($user['lokasitugas'], 0, 4) . "%'");
        $data['kendaraan'] = $this->model('Setup_vhc')->getDataApi("where status=1 and kodeorg = '" . $user['lokasitugas'] . "' order by kodevhc");
        $data['customer'] = $this->model('Setup_customer')->getData();
        $data['supplier'] = $this->model('Supplier')->getData();
        $data['gps'] = $this->model('Setup_gps')->getGpsInterval();
        $data['setup_parameterappl'] = $this->model('Setup_app')->getParamAppM($this->user);
        $data['bjr'] = $this->model('Bjr')->getDataBjr("WHERE kodeorg like '" . $user['lokasitugas'] . "%' and periode = DATE_FORMAT(CURRENT_DATE, '%Y-%m')");
        // $data['setup_mutu'] = $this->model('Setup_mutu')->getmutu($user);
        $data['setup_mutu']['Jenis Buah'] =  $this->model('Mmutu')->getMutu("WHERE jenis = 'Jenis Buah' and aktif ='1'");
        $data['setup_mutu']['Mutu Hancak'] =  $this->model('Mmutu')->getMutu("WHERE jenis = 'Mutu Hancak' and aktif ='1'");
        $data['setup_mutu']['Mutu Transport'] =  $this->model('Mmutu')->getMutu("WHERE jenis = 'Mutu Transport' and aktif ='1'");
        $data['setup_mutu']['Mutu Buah'] =  $this->model('Mmutu')->getMutu("WHERE jenis = 'Mutu Buah' and aktif ='1'");
        $data['setup_mutu']['Sensus Produksi'] =  $this->model('Mmutu')->getMutu("WHERE jenis = 'Sensus Produksi' and aktif ='1'");
        $data['kegiatan'] = $this->model('Kegiatan')->getData();
        $data['kegiatannorma'] = $this->model('Kegiatan')->getKegNormaM();
        $data['setup_arah'] = $this->model('Setup_arah')->getArah();
        return $data;
    }
    function getdatafinger($user)
    {
        $data['karyawan'] = $this->model('Setup_datakaryawan')->selectDataMobile("where (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='" . date('Y-m-d') . "') and  a.kodeorganisasi = '" . $user['kodeorganisasi'] . "'");
        $data['fingerprint_template_server'] = $this->model('Mfingerprint')->getDataFinger("where kebun= '" . $user['lokasitugas'] . "'");
        $data['data_app'] = $this->model('Setup_version')->appfinger();
        return $data;
    }
    
}
