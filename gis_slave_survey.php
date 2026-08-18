<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
# Get Attr
$proses = $_GET['proses'];
$data = $_POST;

$optPt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)='4'");
$optKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$optKry2 = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas,namakaryawan','',4,true);
$optProvinsi = makeOption($dbname,'provinsi','provinsi,provinsi','','',true);
$optKabupaten = makeOption($dbname,'kabupaten','kabupaten,kabupaten','','',true);
$optKecamatan = makeOption($dbname,'kecamatan','kecamatan,kecamatan','','',true);
$optDesa = makeOption($dbname,'desa','desa,desa','','',true);
$optSatuan = array('Pcs'=>'Pcs','Kg'=>'Kg','Meter'=>'Meter');
$arrstatusaju = array('0'=>'Belum Diperoses','1'=>'Disetujui','2'=>'Dikoreksi','3'=>'Ditolak');
$arrstatuslokasi = array('1'=>'Area Wilayah STH Group','0'=>'Diluar Wilayah STH Group');
$arrstatusalat = array('Consumable'=>'Consumable','Inventory'=>'Inventory');

$path   = "fileupload/gis_survey/";
switch($proses) {
    case 'addDataForm' :
    $tabs ="<fieldset id='fieldForm' style='min-width:autopx; clear:right;min-height:auto;'>";
    $tabs.="<legend align=center> Form Survey </legend>";
    $tabs.="<table border=0 style=float:left;>";
    $str=selectQuery($dbname,"pad_5typesurvey","kodesurvey,namasurvey,meliputi");
    $res=fetchData($str);
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Kegiatan Survey:</td>";
    $tabs.="<td></td>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Meliputi:</td>";
    $tabs.="</tr>";
    foreach ($res as $key => $val) {
        $tabs.="<tr>";
        $tabs.="<td hidden id='kodetipe_".$key."'>".$val['kodesurvey']."</td>";
        $tabs.="<td id=namatipe>".$val['namasurvey']."</td>";
        $tabs.="<td >".makeElement('checktipe_'.$key,'checkbox')."</td>";
        $tabs.="<td id=meliputi>".$val['meliputi']."</td>";
        $tabs.="</tr>";
    }
    $tabs.="</table>";

    $tabs.="<table border=0 style=float:right;>";
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Nomor Transaksi:</td>";
    $tabs.="<td>".makeElement('notrans','text','',array('disabled'=>'disabled'))."</td>";
    $tabs.="</tr>";
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Status Lokasi:</td>";
    $tabs.="<td>".makeElement('checkstatus','checkbox','1')." Wilayah STH Group/Lainnya</td>";
    $tabs.="</tr>";
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Perusahaan:</td>";
    $tabs.="<td>".makeElement('kodeorg','select','','',$optPt)."</td>";
    $tabs.="</tr>";
    $tabs.="</table>";

    $tabs.="<fieldset id='fieldForm2' style='min-width:autopx;float:left; clear:right;min-height:auto;'>";
    $tabs.="<legend align=center> Ketentuan Umum </legend>";

    $tabs.="<fieldset id='fieldForm3' style=float:left;width:400px>";
    $tabs.="<legend align=center> Lokasi Kegiatan  </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center>Provinsi</td>";
    $tabs.="<td align=center>kabupaten</td>";
    $tabs.="<td align=center>Kecamatan</td>";
    $tabs.="<td align=center>Desa</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td >".makeElement('provinsi','selectsearch','',array('style'=>'width:125px'),$optProvinsi)."</td>";
    $tabs.="<td >".makeElement('kabupaten','selectsearch','',array('style'=>'width:125px'),$optKabupaten)."</td>";
    $tabs.="<td >".makeElement('kecamatan','selectsearch','',array('style'=>'width:125px'),$optKecamatan)."</td>";
    $tabs.="<td >".makeElement('desa','selectsearch','',array('style'=>'width:125px'),$optDesa)."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm4' style=float:left>";
    $tabs.="<legend align=center> Jangka Waktu Pelaksanaan </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center>Tanggal Mulai</td>";
    $tabs.="<td align=center></td>";
    $tabs.="<td align=center>Tanggal Selesai</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td >".makeElement('tanggalmulai_from','tanggal','',array('style'=>'width:100px','onchange'=>'hitunghk()'))."</td>";
    $tabs.="<td >s/d</td>";
    $tabs.="<td >".makeElement('tanggalmulai_until','tanggal','',array('style'=>'width:100px','onchange'=>'hitunghk()'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";

    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm5' style=float:left>";
    $tabs.="<legend align=center> Personil </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td colspan=6 align=center>Team : ".makeElement('team','textnum','1',array('style'=>'width:50px'))."</td>";
    $tabs.="</tr>";
    $tabs.="<tr>";
    $tabs.="<td align=center>surveyor</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick=tambahpekerja('surveyor')></td>";
    $tabs.="<td align=center>Pendamping</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick=tambahpekerja('pendamping')></td>";
    $tabs.="<td align=center>Saksi</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick=tambahpekerja('saksi')></td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td colspan=2>".makeElement('surveyor','text','',array('style'=>'width:100px'))."</td>";
    $tabs.="<td colspan=2 >".makeElement('pendamping','text','',array('style'=>'width:100px'))."</td>";
    $tabs.="<td colspan=2 >".makeElement('saksi','text','',array('style'=>'width:100px'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listsurveyor></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listpendamping></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listsaksi></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhsurveyor></td><td hidden id=jlhpendamping></td><td hidden id=jlhsaksi></td></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhsurveyor2></td><td hidden id=jlhpendamping2></td><td hidden id=jlhsaksi2></td></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm6' style=float:left>";
    $tabs.="<legend align=center>Perlengkapan Kerja</legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center>Alat</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick='tambahalat()'></td>";
    $tabs.="<td align=center>Status</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td  colspan=2 >".makeElement('alat','text','',array('style'=>'width:110px'))."</td>";
    $tabs.="<td>".makeElement('statusalat','select','',array('style'=>'width:110px'),$arrstatusalat)."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listalat></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhalat></td></tbody>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhalat2></td></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm7' style='min-width:autopx; clear:right;min-height:auto;'>";
    $tabs.="<legend align=center> Rencana Anggaran </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Tenaga Kerja</td>";
    $tabs.="</tr>";
    $tabs.="<tr class=rowheader>";
    $tabs.="<td align=center style=width:200px;>Jenis Biaya</td>";
    $tabs.="<td align=center style=width:100px;>Jumlah</td>";
    $tabs.="<td align=center style=width:100px;>Satuan</td>";
    $tabs.="<td align=center style=width:100px;>Harga</td>";
    $tabs.="<td align=center style=width:100px;>Hari Kerja</td>";
    $tabs.="<td align=center style=width:100px;>Sub Total</td>";
    $tabs.="<td align=center style=width:400px;>Keterangan</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=tenagakerjabody><tr>";
    $tabs.="<td style=width:200px;>".makeElement('jenistenagakerja','text','Tenaga Kerja',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahtenagakerja','textnum','0',array('style'=>'width:100px','disabled'=>'disabled','onblur'=>"hitungsubtotal('tenagakerja')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuantenagakerja','text','Orang',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargatenagakerja','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('tenagakerja')",'onkeyup'=>"z.numberFormat('hargatenagakerja')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hktenagakerja','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('tenagakerja')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotaltenagakerja','textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangantenagakerja','text','',array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Konsumsi</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=konsumsibody>";
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('konsumsisurveyor','text','Surveyor',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahkonsumsisurveyor','textnum','0',array('style'=>'width:100px','disabled'=>'disabled','onblur'=>"hitungsubtotal('konsumsisurveyor')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuankonsumsisurveyor','text','Orang',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargakonsumsisurveyor','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsisurveyor')",'onkeyup'=>"z.numberFormat('hargakonsumsisurveyor')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkkonsumsisurveyor','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsisurveyor')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalkonsumsisurveyor','textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangankonsumsisurveyor','text','',array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('konsumsitkr','text','Tenaga Kerja Rintis',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahkonsumsitkr','textnum','0',array('style'=>'width:100px','disabled'=>'disabled','onblur'=>"hitungsubtotal('konsumsitkr')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuankonsumsitkr','text','Orang',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargakonsumsitkr','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsitkr')",'onkeyup'=>"z.numberFormat('hargakonsumsitkr')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkkonsumsitkr','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsitkr')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalkonsumsitkr','textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangankonsumsitkr','text','',array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Perlengkapan Kerja</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=alatbody>";
    $tabs.="<tr>";
    $tabs.="<td style=width:1138px;></td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<div id=alatdiv></div>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Transportasi</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=transbody>";
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('transport','text','Transportasi/Sewa Motor',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahtransport','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('transport')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuantransport','text','Unit',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargatransport','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('transport')",'onkeyup'=>"z.numberFormat('hargatransport')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hktransport','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('transport')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotaltransport','textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangantransport','text','',array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Bahan Bakar Minyak</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=bbmbody>";
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('bbm','text','Bahan Bakar Minyak',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahbbm','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('bbm')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuanbbm','text','Liter',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargabbm','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('bbm')",'onkeyup'=>"z.numberFormat('hargabbm')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkbbm','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('bbm')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalbbm','textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keteranganbbm','text','',array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Biaya Cadangan</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=bcbody>";
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('bcd','text','Biaya Cadangan',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahbcd','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('bcd')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuanbcd','text','',array('style'=>'width:100px'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargabcd','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('bcd')",'onkeyup'=>"z.numberFormat('hargabcd')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkbcd','textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('bcd')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalbcd','textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keteranganbcd','text','',array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="<tr bgcolor='#77ff77'>";
    $tabs.="<td colspan=5 align=center>Total</td>";
    $tabs.="<td style=width:100px;>".makeElement('Total','textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td colspan=2></td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";
    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm8' style=float:center>";
    $tabs.="<legend align=center> Penjelasan Singkat Teknis & Target Pelaksanaan Kegiatan Survey </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td colspan=2>".makeElement('penjelasan','textarea','',array('style'=>'min-width:1130px;min-height:300px'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";

    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td>".makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>"simpan('insert')"))."</td>";
    $tabs.="<td>".makeElement('cancelButton','button',$_SESSION['lang']['cancel'],array('onclick'=>'batal()'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";


    $tabs .= "</fieldset>";
    echo $tabs;
    break;
    case 'editform' :
    
    $tabs ="<fieldset id='fieldForm' style='min-width:autopx; clear:right;min-height:auto;'>";
    $tabs.="<legend align=center> Form Survey </legend>";
    $tabs.="<table border=0 style=float:left;>";
    $str=selectQuery($dbname,"pad_5typesurvey","kodesurvey,namasurvey,meliputi");
    $res=fetchData($str);
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Kegiatan Survey:</td>";
    $tabs.="<td></td>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Meliputi:</td>";
    $tabs.="</tr>";
    foreach ($res as $key => $val) {
        $tabs.="<tr>";
        $tabs.="<td hidden id='kodetipe_".$key."'>".$val['kodesurvey']."</td>";
        $tabs.="<td id=namatipe>".$val['namasurvey']."</td>";
        $strcheck=selectQuery($dbname,"gis_survey_kegiatan","typesurvey","typesurvey='".$val['kodesurvey']."' and notransaksi='".$data['notransaksi']."'");
        $rescheck=fetchData($strcheck);
        $jlhcheck=count($rescheck);
        if($jlhcheck>0)
        {
            $tabs.="<td >".makeElement('checktipe_'.$key,'checkbox','1')."</td>";
            $tabs.="<td id=meliputi>".$val['meliputi']."</td>";
        }
        else
        {
            $tabs.="<td >".makeElement('checktipe_'.$key,'checkbox')."</td>";
            $tabs.="<td id=meliputi>".$val['meliputi']."</td>";
        }
        $tabs.="</tr>";
    }
    $tabs.="</table>";

    $tabs.="<table border=0 style=float:right;>";
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Nomor Transaksi:</td>";
    $tabs.="<td>".makeElement('notrans','text',$data['notransaksi'],array('disabled'=>'disabled'))."</td>";
    $tabs.="</tr>";
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Status Lokasi:</td>";
    $strcheck=selectQuery($dbname,"gis_survey","*","notransaksi='".$data['notransaksi']."'");
    $rescheck=fetchData($strcheck);
    if($rescheck[0]['statuslokasi']==1)
    {
        $tabs.="<td>".makeElement('checkstatus','checkbox','1')." Wilayah STH Group/Lainnya</td>";
    }
    else
    {
        $tabs.="<td>".makeElement('checkstatus','checkbox','0')." Wilayah STH Group/Lainnya</td>"; 
    }
    $tabs.="</tr>";
    $tabs.="<tr>";
    $tabs.="<td style=text-decoration:underline;font-weight:bold;>Perusahaan:</td>";
    $tabs.="<td>".makeElement('kodeorg','select',$rescheck[0]['kodeorg'],'',$optPt)."</td>";
    $tabs.="</tr>";
    $tabs.="</table>";

    $tabs.="<fieldset id='fieldForm2' style='min-width:autopx;float:left; clear:right;min-height:auto;'>";
    $tabs.="<legend align=center> Ketentuan Umum </legend>";

    $tabs.="<fieldset id='fieldForm3' style=float:left;width:400px>";
    $tabs.="<legend align=center> Lokasi Kegiatan  </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center>Provinsi</td>";
    $tabs.="<td align=center>kabupaten</td>";
    $tabs.="<td align=center>Kecamatan</td>";
    $tabs.="<td align=center>Desa</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td >".makeElement('provinsi','selectsearch',$rescheck[0]['provinsi'],array('style'=>'width:125px'),$optProvinsi)."</td>";
    $tabs.="<td >".makeElement('kabupaten','selectsearch',$rescheck[0]['kabupaten'],array('style'=>'width:125px'),$optKabupaten)."</td>";
    $tabs.="<td >".makeElement('kecamatan','selectsearch',$rescheck[0]['kecamatan'],array('style'=>'width:125px'),$optKecamatan)."</td>";
    $tabs.="<td >".makeElement('desa','selectsearch',$rescheck[0]['desa'],array('style'=>'width:125px'),$optDesa)."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";


    $tabs.="<fieldset id='fieldForm4' style=float:left>";
    $tabs.="<legend align=center> Jangka Waktu Pelaksanaan </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=2>Tanggal Mulai</td>";
    $tabs.="<td align=center>Tanggal Selesai</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td >".makeElement('tanggalmulai_from','tanggal',tanggalnormal($rescheck[0]['tanggalmulai']),array('style'=>'width:100px','onchange'=>'hitunghk()'))."</td>";
    $tabs.="<td >s/d</td>";
    $tabs.="<td >".makeElement('tanggalmulai_until','tanggal',tanggalnormal($rescheck[0]['tanggalselesai']),array('style'=>'width:100px','onchange'=>'hitunghk()'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";

    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm5' style=float:left>";
    $tabs.="<legend align=center> Personil </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td colspan=6 align=center>Team : ".makeElement('team','textnum','1',array('style'=>'width:50px'))."</td>";
    $tabs.="</tr>";
    $tabs.="<tr>";
    $tabs.="<td align=center>surveyor</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick=tambahpekerja('surveyor')></td>";
    $tabs.="<td align=center>Pendamping</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick=tambahpekerja('pendamping')></td>";
    $tabs.="<td align=center>Saksi</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick=tambahpekerja('saksi')></td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td colspan=2>".makeElement('surveyor','text','',array('style'=>'width:100px'))."</td>";
    $tabs.="<td colspan=2 >".makeElement('pendamping','text','',array('style'=>'width:100px'))."</td>";
    $tabs.="<td colspan=2 >".makeElement('saksi','text','',array('style'=>'width:100px'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listsurveyor>";
    $strsurveyor=selectQuery($dbname,"gis_survey_tenagakerja","*","statuspekerja='surveyor' and notransaksi='".$data['notransaksi']."'");
    $ressurveyor=fetchData($strsurveyor);
    $jlhsurveyor=count($ressurveyor);
    foreach ($ressurveyor as $keysurveyor => $valsurveyor) {
        $tabs.="<tr id='list_surveyor_".$keysurveyor."'>";
        $tabs.="<td title='Team:".$valsurveyor['team']." surveyor' style=max-width:90px;width:90px; bgcolor='#DEDEDE'; ><span style=max-width:90px;width:90px; id='listdet_surveyor_".$keysurveyor."'>".$valsurveyor['namapekerja']."</span></td><td id='listteamdet_surveyor_".$keysurveyor."' hidden>".$valsurveyor['team']."</td><td id='listiddet_surveyor_".$keysurveyor."' hidden>".$valsurveyor['namapekerja']."</td>";
        $tabs.="<td align=center><img src=images/close.png ";
        $tabs.="class=resicon  title='Delete surveyor' onclick=hapuspekerja('".$valsurveyor['team']."','surveyor','0')></td>";
        $tabs.="</tr>";
    }
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listpendamping>";
    $strpendamping=selectQuery($dbname,"gis_survey_tenagakerja","*","statuspekerja='pendamping' and notransaksi='".$data['notransaksi']."'");
    $respendamping=fetchData($strpendamping);
    $jlhpendamping=count($respendamping);
    foreach ($respendamping as $keypendamping => $valpendamping) {
        $tabs.="<tr id='list_pendamping_".$keypendamping."'>";
        $tabs.="<td title='Team:".$valpendamping['team']." pendamping' style=max-width:90px;width:90px; bgcolor='#DEDEDE'; ><span style=max-width:90px;width:90px; id='listdet_pendamping_".$keypendamping."'>".$valpendamping['namapekerja']."</span></td><td id='listteamdet_pendamping_".$keypendamping."' hidden>".$valpendamping['team']."</td><td id='listiddet_pendamping_".$keypendamping."' hidden>".$valpendamping['namapekerja']."</td>";
        $tabs.="<td align=center><img src=images/close.png ";
        $tabs.="class=resicon  title='Delete pendamping' onclick=hapuspekerja('".$valpendamping['team']."','pendamping','".$keypendamping."')></td>";
        $tabs.="</tr>";
    }
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listsaksi>";
    $strsaksi=selectQuery($dbname,"gis_survey_tenagakerja","*","statuspekerja='saksi' and notransaksi='".$data['notransaksi']."'");
    $ressaksi=fetchData($strsaksi);
    $jlhsaksi=count($ressaksi);
    foreach ($ressaksi as $keysaksi => $valsaksi) {
        $tabs.="<tr id='list_saksi_".$keysaksi."'>";
        $tabs.="<td title='Team:".$valsaksi['team']." saksi' style=max-width:90px;width:90px; bgcolor='#DEDEDE'; ><span style=max-width:90px;width:90px; id='listdet_saksi_".$keysaksi."'>".$valsaksi['namapekerja']."</span></td><td id='listteamdet_saksi_".$keysaksi."' hidden>".$valsaksi['team']."</td>";
        $tabs.="<td align=center><img src=images/close.png ";
        $tabs.="class=resicon  title='Delete saksi' onclick=hapuspekerja('".$valsaksi['team']."','saksi','".$keysaksi."')></td>";
        $tabs.="</tr>";
    }
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhsurveyor>".$jlhsurveyor."</td><td hidden id=jlhpendamping>".$jlhpendamping."</td><td hidden id=jlhsaksi>".$jlhsaksi."</td></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhsurveyor2>".$jlhsurveyor."</td><td hidden id=jlhpendamping2>".$jlhpendamping."</td><td hidden id=jlhsaksi2>".$jlhsaksi."</td></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm6' style=float:left>";
    $tabs.="<legend align=center> Alat </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center>Alat</td>";
    $tabs.="<td align=center><img src=images/plus.png ";
    $tabs.="class=resicon  title='Add Detail ' onclick='tambahalat()'></td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td  colspan=2 >".makeElement('alat','text','',array('style'=>'width:110px'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 style=float:left>";
    $tabs.="<tbody id=listalat>";
    $stralat=selectQuery($dbname,"gis_survey_alat","*","notransaksi='".$data['notransaksi']."'");
    $resalat=fetchData($stralat);
    $jlhalat=count($resalat);
    foreach ($resalat as $keyalat => $valalat) {
        $tabs.="<tr id='list_alat_".$keyalat."'>";
        $tabs.="<td title='alat' style=max-width:90px;width:90px; bgcolor='#DEDEDE'; ><span style=max-width:90px;width:90px; id='listdet_alat_".$keyalat."'>".$valalat['namaalat']."</span></td><td id='alatstatus_".$keyalat."' hidden>".$valalat['status']."</td>";
        $tabs.="<td align=center><img src=images/close.png ";
        $tabs.="class=resicon  title='Delete alat' onclick=hapusalat('".$keyalat."')></td>";
        $tabs.="</tr>";
    }
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhalat>".$jlhalat."</td></tbody>";
    $tabs.="<table class=sortable cellspacing=1 border=0 hidden>";
    $tabs.="<tbody><td hidden id=jlhalat2>".$jlhalat."</td></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm7' style='min-width:autopx; clear:right;min-height:auto;'>";
    $tabs.="<legend align=center> Rencana Anggaran </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Tenaga Kerja</td>";
    $tabs.="</tr>";
    $tabs.="<tr class=rowheader>";
    $tabs.="<td align=center style=width:200px;>Jenis Biaya</td>";
    $tabs.="<td align=center style=width:100px;>Jumlah</td>";
    $tabs.="<td align=center style=width:100px;>Satuan</td>";
    $tabs.="<td align=center style=width:100px;>Harga</td>";
    $tabs.="<td align=center style=width:100px;>Hari Kerja</td>";
    $tabs.="<td align=center style=width:100px;>Sub Total</td>";
    $tabs.="<td align=center style=width:400px;>Keterangan</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=tenagakerjabody><tr>";
    $stranggaran=selectQuery($dbname,"gis_survey_anggaranht","*","notransaksi='".$data['notransaksi']."'");
    $resanggaran=fetchData($stranggaran);
    $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='tenagakerja'");
    $resanggarandt=fetchData($stranggarandt);
        //print_r($resanggarandt);
        //exit();
    $tabs.="<td style=width:200px;>".makeElement('jenistenagakerja','text','Tenaga Kerja',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahtenagakerja','textnum',$jlhsurveyor+$jlhpendamping+$jlhsaksi,array('style'=>'width:100px','disabled'=>'disabled','onblur'=>"hitungsubtotal('tenagakerja')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuantenagakerja','text','Orang',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargatenagakerja','textnum',number_format($resanggarandt[0]['biaya']),array('style'=>'width:100px','onblur'=>"hitungsubtotal('tenagakerja')",'onkeyup'=>"z.numberFormat('hargatenagakerja')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hktenagakerja','textnum',$resanggarandt[0]['hk'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('tenagakerja')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotaltenagakerja','textnum',number_format($resanggarandt[0]['subtotal']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangantenagakerja','text',$resanggarandt[0]['keterangan'],array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";

    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Konsumsi</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=konsumsibody>";
    $tabs.="<tr>";
    $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='konsumsi' and kode='surveyor'");
    $resanggarandt=fetchData($stranggarandt);
    $tabs.="<td style=width:200px;>".makeElement('konsumsisurveyor','text','Surveyor',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahkonsumsisurveyor','textnum',$jlhsurveyor,array('style'=>'width:100px','disabled'=>'disabled','onblur'=>"hitungsubtotal('konsumsisurveyor')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuankonsumsisurveyor','text','Orang',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargakonsumsisurveyor','textnum',number_format($resanggarandt[0]['biaya']),array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsisurveyor')",'onkeyup'=>"z.numberFormat('hargakonsumsisurveyor')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkkonsumsisurveyor','textnum',$resanggarandt[0]['hk'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsisurveyor')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalkonsumsisurveyor','textnum',number_format($resanggarandt[0]['subtotal']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangankonsumsisurveyor','text',$resanggarandt[0]['keterangan'],array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='konsumsi' and kode='tenagakerjarintis'");
    $resanggarandt=fetchData($stranggarandt);
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('konsumsitkr','text','Tenaga Kerja Rintis',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahkonsumsitkr','textnum',$jlhpendamping+$jlhsaksi,array('style'=>'width:100px','disabled'=>'disabled','onblur'=>"hitungsubtotal('konsumsitkr')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuankonsumsitkr','text','Orang',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargakonsumsitkr','textnum',number_format($resanggarandt[0]['biaya']),array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsitkr')",'onkeyup'=>"z.numberFormat('hargakonsumsitkr')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkkonsumsitkr','textnum',$resanggarandt[0]['hk'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('konsumsitkr')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalkonsumsitkr','textnum',number_format($resanggarandt[0]['subtotal']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangankonsumsitkr','text',$resanggarandt[0]['keterangan'],array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Perlengkapan Kerja</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=alatbody>";
    $tabs.="<tr>";
    $tabs.="<td style=width:1138px;></td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<div id=alatdiv>";
    $stralat=selectQuery($dbname,"gis_survey_alat","*","notransaksi='".$data['notransaksi']."' and status='Consumable'");
    $resalat=fetchData($stralat);
    $jlhalat=count($resalat);
    foreach ($resalat as $keyalat => $valalat) {
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='peralatan' and kode='".$valalat['kodealat']."'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<table class=sortable cellspacing=1 border=0 align=center><tr id='daftar_alat_".$keyalat."'>";
        $tabs.="<td style=width:200px;>".makeElement('namaalat_'.$keyalat,'text',$valalat['namaalat'],array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
        $tabs.="<td style=width:100px;>".makeElement('jumlahalat_'.$keyalat,'textnum',$resanggarandt[0]['jumlah'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('alat',".$keyalat.")"))."</td>";
        $tabs.="<td style=width:100px;>".makeElement('satuanalat_'.$keyalat,'select',$resanggarandt[0]['satuan'],array('style'=>'width:100px'),$optSatuan)."</td>";
        $tabs.="<td style=width:100px;>".makeElement('hargaalat_'.$keyalat,'textnum',number_format($resanggarandt[0]['biaya']),array('style'=>'width:100px','onblur'=>"hitungsubtotal('alat',".$keyalat.")",'onkeyup'=>"z.numberFormat('hargaalat_".$keyalat."')"))."</td>";
        $tabs.="<td style=width:100px;>".makeElement('hkkalat_'.$keyalat,'textnum','',array('style'=>'width:100px'))."</td>";
        $tabs.="<td style=width:100px;>".makeElement('subtotalalat_'.$keyalat,'textnum',number_format($resanggarandt[0]['subtotal']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
        $tabs.="<td style=width:400px;>".makeElement('keteranganalat_'.$keyalat,'text',$resanggarandt[0]['keterangan'],array('style'=>'width:400px'))."</td>";
        $tabs.="</tr></table>";
    }
    $tabs.="</div>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Transportasi</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=transbody>";
    $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='transport'");
    $resanggarandt=fetchData($stranggarandt);
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('transport','text','Transportasi/Sewa Motor',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahtransport','textnum',$resanggarandt[0]['jumlah'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('transport')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuantransport','text','Unit',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargatransport','textnum',number_format($resanggarandt[0]['biaya']),array('style'=>'width:100px','onblur'=>"hitungsubtotal('transport')",'onkeyup'=>"z.numberFormat('hargatransport')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hktransport','textnum',$resanggarandt[0]['hk'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('transport')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotaltransport','textnum',number_format($resanggarandt[0]['subtotal']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keterangantransport','text',$resanggarandt[0]['keterangan'],array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Bahan Bakar Minyak</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=bbmbody>";
    $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='bbm'");
    $resanggarandt=fetchData($stranggarandt);
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('bbm','text','Bahan Bakar Minyak',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahbbm','textnum',$resanggarandt[0]['jumlah'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('bbm')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuanbbm','text','Liter',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargabbm','textnum',number_format($resanggarandt[0]['biaya']),array('style'=>'width:100px','onblur'=>"hitungsubtotal('bbm')",'onkeyup'=>"z.numberFormat('hargabbm')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkbbm','textnum',$resanggarandt[0]['hk'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('bbm')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalbbm','textnum',number_format($resanggarandt[0]['subtotal']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keteranganbbm','text',$resanggarandt[0]['keterangan'],array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<thead><tr class=rowheader>";
    $tabs.="<td align=center colspan=7>Biaya Cadangan</td>";
    $tabs.="</tr></thead>";
    $tabs.="<tbody id=bcbody>";
    $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='biayacadangan'");
    $resanggarandt=fetchData($stranggarandt);
    $tabs.="<tr>";
    $tabs.="<td style=width:200px;>".makeElement('bcd','text','Biaya Cadangan',array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('jumlahbcd','textnum',$resanggarandt[0]['jumlah'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('bcd')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('satuanbcd','text',$resanggarandt[0]['satuan'],array('style'=>'width:100px'))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hargabcd','textnum',number_format($resanggarandt[0]['biaya']),array('style'=>'width:100px','onblur'=>"hitungsubtotal('bcd')",'onkeyup'=>"z.numberFormat('hargabcd')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('hkbcd','textnum',$resanggarandt[0]['hk'],array('style'=>'width:100px','onblur'=>"hitungsubtotal('bcd')"))."</td>";
    $tabs.="<td style=width:100px;>".makeElement('subtotalbcd','textnum',number_format($resanggarandt[0]['subtotal']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td style=width:400px;>".makeElement('keteranganbcd','text',$resanggarandt[0]['keterangan'],array('style'=>'width:400px'))."</td>";
    $tabs.="</tr>";
    $tabs.="<tr bgcolor='#77ff77'>";
    $tabs.="<td colspan=5 align=center>Total</td>";
    $tabs.="<td style=width:100px;>".makeElement('Total','textnum',number_format($resanggaran[0]['totalbiaya']),array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $tabs.="<td colspan=2></td>";
    $tabs.="</tr>";
    $tabs.="</tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";
    $tabs.= "</fieldset>";

    $tabs.="<fieldset id='fieldForm8' style=float:center>";
    $tabs.="<legend align=center> Penjelasan Singkat Teknis & Target Pelaksanaan Kegiatan Survey </legend>";
    $tabs.="<table class=sortable cellspacing=1 border=0 >";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td colspan=2>".makeElement('penjelasan','textarea',$rescheck[0]['penjelasan'],array('style'=>'min-width:1130px;min-height:300px'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";

    $tabs.="<table class=sortable cellspacing=1 border=0 align=center>";
    $tabs.="<tbody><tr class=rowcontent>";
    $tabs.="<td>".makeElement('saveButton','button',$_SESSION['lang']['save'],array('onclick'=>"simpan('update')"))."</td>";
    $tabs.="<td>".makeElement('cancelButton','button',$_SESSION['lang']['cancel'],array('onclick'=>'batal()'))."</td>";
    $tabs.="</tr></tbody>";
    $tabs.="</table>";
    $tabs.= "</fieldset>";


    $tabs .= "</fieldset>";

    echo $tabs;
    break;
    case 'hitunghk':
    $start_date= strtotime($data['tanggalmulai']);
    $end_date= strtotime($data['tanggalselesai']);
    if($start_date > $end_date)
    {
        exit('0');
    }
    $interval= $end_date-$start_date;
    $diff= floor($interval/(60 * 60 * 24));
    //exit($diff);
    echo $diff;

    break;
    case 'tambahpekerja':
    
    if($data['type']=='surveyor' || $data['type']=='pendamping')
    {
        $tabs="<tr id='list_".$data['type']."_".$data['num']."'>";
        $tabs.="<td title='Team:".$data['team']." ".$data['type']."' style=max-width:95px;width:95px; bgcolor='#DEDEDE'; ><span style=max-width:95px;width:95px; id='listdet_".$data['type']."_".$data['num']."'>".$data['namapekerja']."</span></td><td id='listteamdet_".$data['type']."_".$data['num']."' hidden>".$data['team']."</td><td id='listiddet_".$data['type']."_".$data['num']."' hidden>".$data['namapekerja']."</td>";
        $tabs.="<td align=center><img src=images/close.png ";
        $tabs.="class=resicon  title='Delete Team:".$data['team']." ".$data['type']."' onclick=hapuspekerja('".$data['team']."','".$data['type']."','".$data['num']."')></td>";
        $tabs.="</tr>";
    }
    else
    {
        $tabs="<tr id='list_".$data['type']."_".$data['num']."'>";
        $tabs.="<td title='Team:".$data['team']." ".$data['type']."' style=max-width:90px;width:90px; bgcolor='#DEDEDE'; ><span style=max-width:90px;width:90px; id='listdet_".$data['type']."_".$data['num']."'>".$data['namapekerja']."</span></td><td id='listteamdet_".$data['type']."_".$data['num']."' hidden>".$data['team']."</td>";
        $tabs.="<td align=center><img src=images/close.png ";
        $tabs.="class=resicon  title='Delete Team:".$data['team']." ".$data['type']."' onclick=hapuspekerja('".$data['team']."','".$data['type']."','".$data['num']."')></td>";
        $tabs.="</tr>";
    }
    
    
    echo $tabs;
    break;
    case 'tambahalat':
    
    $tabs="<tr id='list_alat_".$data['num']."'>";
    $tabs.="<td style=max-width:100px;width:100px; bgcolor='#DEDEDE';><span style=max-width:100px;width:100px; id='listdet_alat_".$data['num']."'>".$data['namaalat']."</span></td><td id='alatstatus_".$data['num']."' hidden>".$data['statusalat']."</td>";
    $tabs.="<td align=center><img src=images/close.png ";
    $tabs.="class=resicon  title='Delete Alat' onclick=hapusalat('".$data['num']."')></td>";
    $tabs.="</tr>";
    
    
    $jabs="<table class=sortable cellspacing=1 border=0 align=center><tr id='daftar_alat_".$data['num']."'>";
    $jabs.="<td style=width:200px;>".makeElement('namaalat_'.$data['num'],'text',$data['namaalat'],array('style'=>'width:200px','disabled'=>'disabled'))."</td>";
    $jabs.="<td style=width:100px;>".makeElement('jumlahalat_'.$data['num'],'textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('alat',".$data['num'].")"))."</td>";
    $jabs.="<td style=width:100px;>".makeElement('satuanalat_'.$data['num'],'select','',array('style'=>'width:100px'),$optSatuan)."</td>";
    $jabs.="<td style=width:100px;>".makeElement('hargaalat_'.$data['num'],'textnum','0',array('style'=>'width:100px','onblur'=>"hitungsubtotal('alat',".$data['num'].")",'onkeyup'=>"z.numberFormat('hargaalat_".$data['num']."')"))."</td>";
    $jabs.="<td style=width:100px;>".makeElement('hkkalat_'.$data['num'],'textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $jabs.="<td style=width:100px;>".makeElement('subtotalalat_'.$data['num'],'textnum','0',array('style'=>'width:100px','disabled'=>'disabled'))."</td>";
    $jabs.="<td style=width:400px;>".makeElement('keteranganalat_'.$data['num'],'text','',array('style'=>'width:400px'))."</td>";
    $jabs.="</tr></table>";

    
    echo $jabs."###".$tabs;    
    break;
    case 'insert':
        #data for gis_survey-------------------------------------------------------------------------------------------------------------------------------------
    $query = selectQuery($dbname,"gis_survey","notransaksi");
    $id = fetchData($query);
    $maxid=1;
    if(!empty($id)) {
        foreach($id as $row) {
            intval(substr($row['notransaksi'],0,3))>=$maxid ? $maxid=intval(substr($row['notransaksi'],0,3)) : false;
        }
        $maxid++;
    }
    $konter = addZero($maxid,3);

    $notransaksi=$konter.'/SRV'.'/'.$data['kodeorg'].'/'.substr($data['tanggalmulai'], 3,2).'/'.substr($data['tanggalmulai'], 6,4);
    $datains= array();
    $datains['survey']['notransaksi']=$notransaksi;
    $datains['survey']['kodeorg']=$data['kodeorg'];
    $datains['survey']['tanggalmulai']=tanggalsystem($data['tanggalmulai']);
    $datains['survey']['tanggalselesai']=tanggalsystem($data['tanggalselesai']);
    $datains['survey']['provinsi']=$data['provinsi'];
    $datains['survey']['kabupaten']=$data['kabupaten'];
    $datains['survey']['kecamatan']=$data['kecamatan'];
    $datains['survey']['desa']=$data['desa'];
    $datains['survey']['statuslokasi']=$data['statuslokasi'];
    $datains['survey']['penjelasan']=$data['penjelasan'];
    $datains['survey']['posting']=0;
    $datains['survey']['statuspengajuan']=0;
    $datains['survey']['createdby']=$_SESSION['standard']['userid'];
    $datains['survey']['createdtime']=date("Y-m-d H:i:s");
    $datains['survey']['updateby']='';
    $datains['survey']['updatetime']='';


        #insert gis_survey_anggaranht------------------------------
    $strinsert=insertQuery($dbname,'gis_survey',$datains['survey']);
            /*print_r($strinsert);
            exit();*/
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "survey :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggaranht","kodeanggaran");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggaran']>=$maxid ? $maxid=$row['kodeanggaran'] : false;
                }
                $maxid++;
            }
            $kodeanggaran=$maxid;
            $datains['anggaran'][$key]['kodeanggaran']=$kodeanggaran;
            $datains['anggaran'][$key]['notransaksi']=$notransaksi;
            $datains['anggaran'][$key]['totalbiaya']=$data['Total'];
        #insert gis_survey_anggaranht----------------------------------
            $strinsert=insertQuery($dbname,'gis_survey_anggaranht',$datains['anggaran'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggaran :". $e->getMessage() ; }

        #data for gis_survey_kegiatan------------------------------------------------------------------------------------------------------------------------------
            echo $data['kodetipe'];
            $arrkodekegiatan = explode('###', $data['kodetipe']);
            foreach ($arrkodekegiatan as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_kegiatan","id");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['id']>=$maxid ? $maxid=$row['id'] : false;
                    }
                    $maxid++;
                }
                $datains['kegiatan'][$key]['id']=$maxid;
                $datains['kegiatan'][$key]['notransaksi']=$notransaksi;
                $datains['kegiatan'][$key]['typesurvey']=$val;
            #insert gis_survey_kegiatan----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_kegiatan',$datains['kegiatan'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "kegiatan :". $e->getMessage() ; }
            }

        #data for gis_survey_tenagakerja---------------------------------------------------------------
            $arrnamasurveyor = explode('###', $data['namasurveyor']);
            foreach ($arrnamasurveyor as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_tenagakerja","kodetenagakerja");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodetenagakerja']>=$maxid ? $maxid=$row['kodetenagakerja'] : false;
                    }
                    $maxid++;
                }
                $arrnamasurveyorteam = explode('/', $val);
                $datains['namasurveyor'][$key]['kodetenagakerja']=$maxid;
                $datains['namasurveyor'][$key]['notransaksi']=$notransaksi;
                $datains['namasurveyor'][$key]['team']=$arrnamasurveyorteam[1] ;
                $datains['namasurveyor'][$key]['statuspekerja']='surveyor';
                $datains['namasurveyor'][$key]['namapekerja']=$arrnamasurveyorteam[0] ;
            #insert gis_survey_tenagakerja----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_tenagakerja',$datains['namasurveyor'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "tenagakerja :". $e->getMessage() ; }
            }
            $arrnamapendamping = explode('###', $data['namapendamping']);
            foreach ($arrnamapendamping as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_tenagakerja","kodetenagakerja");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodetenagakerja']>=$maxid ? $maxid=$row['kodetenagakerja'] : false;
                    }
                    $maxid++;
                }
                $arrnamapendampingteam = explode('/', $val);
                $datains['namapendamping'][$key]['kodetenagakerja']=$maxid;
                $datains['namapendamping'][$key]['notransaksi']=$notransaksi;
                $datains['namapendamping'][$key]['team']=$arrnamapendampingteam[1] ;
                $datains['namapendamping'][$key]['statuspekerja']='pendamping';
                $datains['namapendamping'][$key]['namapekerja']=$arrnamapendampingteam[0] ;
            #insert gis_survey_tenagakerja----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_tenagakerja',$datains['namapendamping'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "tenagakerja :". $e->getMessage() ; }
            }
            $arrnamasaksi = explode('###', $data['namasaksi']);
            foreach ($arrnamasaksi as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_tenagakerja","kodetenagakerja");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodetenagakerja']>=$maxid ? $maxid=$row['kodetenagakerja'] : false;
                    }
                    $maxid++;
                }
                $arrnamasaksiteam = explode('/', $val);
                $datains['namasaksi'][$key]['kodetenagakerja']=$maxid;
                $datains['namasaksi'][$key]['notransaksi']=$notransaksi;
                $datains['namasaksi'][$key]['team']=$arrnamasaksiteam[1] ;
                $datains['namasaksi'][$key]['statuspekerja']='saksi';
                $datains['namasaksi'][$key]['namapekerja']=$arrnamasaksiteam[0] ;
            #insert gis_survey_tenagakerja----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_tenagakerja',$datains['namasaksi'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "tenagakerja :". $e->getMessage() ; }
            }

        #data for gis_survey_alat-------------------------------------------------------------------------------
            $arrjenisalat = explode('###', $data['jenisalat']);
            foreach ($arrjenisalat as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_alat","kodealat");
                $id = fetchData($query);
                $kodealat=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodealat']>=$kodealat ? $kodealat=$row['kodealat'] : false;
                    }
                    $kodealat++;
                }
            //echo $val;
                $arrjenisalatdt = explode('/', $val);
            //echo $arrjenisalatdt;

                $datains['jenisalat'][$key]['kodealat']=$kodealat;
                $datains['jenisalat'][$key]['notransaksi']=$notransaksi;
                $datains['jenisalat'][$key]['status']=$arrjenisalatdt[1];
                $datains['jenisalat'][$key]['jenisalat']=$arrjenisalatdt[0];
            #insert gis_survey_alat----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_alat',$datains['jenisalat'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "jenisalat :". $e->getMessage() ; }

                if($arrjenisalatdt[1]=='Consumable'){
                    $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
                    $id = fetchData($query);
                    $maxid=1;
                    if(!empty($id)) {
                        foreach($id as $row) {
                            $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                        }
                        $maxid++;
                    }
                    $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
                    $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
                    $datains['anggarandt'][$key]['kode']=$kodealat;
                    $datains['anggarandt'][$key]['jenisbiaya']='peralatan';
                    $datains['anggarandt'][$key]['satuan']=$arrjenisalatdt[3];
                    $datains['anggarandt'][$key]['biaya']=$arrjenisalatdt[4];
                    $datains['anggarandt'][$key]['jumlah']=$arrjenisalatdt[2];
                    $datains['anggarandt'][$key]['hk']='';
                    $datains['anggarandt'][$key]['subtotal']=$arrjenisalatdt[5];
                    $datains['anggarandt'][$key]['keterangan']=$arrjenisalatdt[6];

                    $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
                    $errorDB='';
                    try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }
                }
            }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='tenagakerja';
            $datains['anggarandt'][$key]['satuan']=$data['satuantenagakerja'];
            $datains['anggarandt'][$key]['biaya']=$data['hargatenagakerja'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahtenagakerja'];
            $datains['anggarandt'][$key]['hk']=$data['hktenagakerja'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotaltenagakerja'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangantenagakerja'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='surveyor';
            $datains['anggarandt'][$key]['jenisbiaya']='konsumsi';
            $datains['anggarandt'][$key]['satuan']=$data['satuankonsumsisurveyor'];
            $datains['anggarandt'][$key]['biaya']=$data['hargakonsumsisurveyor'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahkonsumsisurveyor'];
            $datains['anggarandt'][$key]['hk']=$data['hkkonsumsisurveyor'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalkonsumsisurveyor'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangankonsumsisurveyor'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='tenagakerjarintis';
            $datains['anggarandt'][$key]['jenisbiaya']='konsumsi';
            $datains['anggarandt'][$key]['satuan']=$data['satuankonsumsitkr'];
            $datains['anggarandt'][$key]['biaya']=$data['hargakonsumsitkr'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahkonsumsitkr'];
            $datains['anggarandt'][$key]['hk']=$data['hkkonsumsitkr'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalkonsumsitkr'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangankonsumsitkr'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='transport';
            $datains['anggarandt'][$key]['satuan']=$data['satuantransport'];
            $datains['anggarandt'][$key]['biaya']=$data['hargatransport'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahtransport'];
            $datains['anggarandt'][$key]['hk']=$data['hktransport'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotaltransport'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangantransport'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='bbm';
            $datains['anggarandt'][$key]['satuan']=$data['satuanbbm'];
            $datains['anggarandt'][$key]['biaya']=$data['hargabbm'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahbbm'];
            $datains['anggarandt'][$key]['hk']=$data['hkbbm'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalbbm'];
            $datains['anggarandt'][$key]['keterangan']=$data['keteranganbbm'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='biayacadangan';
            $datains['anggarandt'][$key]['satuan']=$data['satuanbcd'];
            $datains['anggarandt'][$key]['biaya']=$data['hargabcd'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahbcd'];
            $datains['anggarandt'][$key]['hk']=$data['hkbcd'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalbcd'];
            $datains['anggarandt'][$key]['keterangan']=$data['keteranganbcd'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }



        //print_r($arrjenisalatdt);

            break;
            case 'update':
        #data for gis_survey-------------------------------------------------------------------------------------------------------------------------------------
            $strct= selectQuery($dbname,'gis_survey','*',"notransaksi='".$data['notransaksi']."'");
            $resct= fetchData($strct);
            $posting=$resct[0]['posting'];
            $statuspersetujuan=$resct[0]['statuspersetujuan'];
            $createdby=$resct[0]['createdby'];
            $createdtime=$resct[0]['createdtime'];
        //exit($strct);
            $strdeletes=deleteQuery($dbname,'gis_survey',"notransaksi='".$data['notransaksi']."'");
            $errorDB='';
            try{$owlPDO->exec($strdeletes); }catch (PDOException $e) {$errorDB .= "delete :". $e->getMessage() ; }
            
            $query = selectQuery($dbname,"gis_survey","notransaksi");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    intval(substr($row['notransaksi'],0,3))>=$maxid ? $maxid=intval(substr($row['notransaksi'],0,3)) : false;
                }
                $maxid++;
            }
            $konter = addZero($maxid,3);

            $notransaksi=$konter.'/SRV'.'/'.$data['kodeorg'].'/'.substr($data['tanggalmulai'], 3,2).'/'.substr($data['tanggalmulai'], 6,4);
            $datains= array();
            $datains['survey']['notransaksi']=$notransaksi;
            $datains['survey']['kodeorg']=$data['kodeorg'];
            $datains['survey']['tanggalmulai']=tanggalsystem($data['tanggalmulai']);
            $datains['survey']['tanggalselesai']=tanggalsystem($data['tanggalselesai']);
            $datains['survey']['provinsi']=$data['provinsi'];
            $datains['survey']['kabupaten']=$data['kabupaten'];
            $datains['survey']['kecamatan']=$data['kecamatan'];
            $datains['survey']['desa']=$data['desa'];
            $datains['survey']['statuslokasi']=$data['statuslokasi'];
            $datains['survey']['penjelasan']=$data['penjelasan'];
            $datains['survey']['posting']=$posting;
            $datains['survey']['statuspengajuan']=$statuspersetujuan;
            $datains['survey']['createdby']=$createdby;
            $datains['survey']['createdtime']=$createdtime;
            $datains['survey']['updateby']=$_SESSION['standard']['userid'];
            $datains['survey']['updatetime']=date("Y-m-d H:i:s");

            

        #insert gis_survey_anggaranht------------------------------
            $strinsert=insertQuery($dbname,'gis_survey',$datains['survey']);
            /*print_r($strinsert);
            exit();*/
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "survey :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggaranht","kodeanggaran");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggaran']>=$maxid ? $maxid=$row['kodeanggaran'] : false;
                }
                $maxid++;
            }
            $kodeanggaran=$maxid;
            $datains['anggaran'][$key]['kodeanggaran']=$kodeanggaran;
            $datains['anggaran'][$key]['notransaksi']=$notransaksi;
            $datains['anggaran'][$key]['totalbiaya']=$data['Total'];
        #insert gis_survey_anggaranht----------------------------------
            $strinsert=insertQuery($dbname,'gis_survey_anggaranht',$datains['anggaran'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggaran :". $e->getMessage() ; }

        #data for gis_survey_kegiatan------------------------------------------------------------------------------------------------------------------------------
            echo $data['kodetipe'];
            $arrkodekegiatan = explode('###', $data['kodetipe']);
            foreach ($arrkodekegiatan as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_kegiatan","id");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['id']>=$maxid ? $maxid=$row['id'] : false;
                    }
                    $maxid++;
                }
                $datains['kegiatan'][$key]['id']=$maxid;
                $datains['kegiatan'][$key]['notransaksi']=$notransaksi;
                $datains['kegiatan'][$key]['typesurvey']=$val;
            #insert gis_survey_kegiatan----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_kegiatan',$datains['kegiatan'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "kegiatan :". $e->getMessage() ; }
            }

        #data for gis_survey_tenagakerja---------------------------------------------------------------
            $arrnamasurveyor = explode('###', $data['namasurveyor']);
            foreach ($arrnamasurveyor as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_tenagakerja","kodetenagakerja");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodetenagakerja']>=$maxid ? $maxid=$row['kodetenagakerja'] : false;
                    }
                    $maxid++;
                }
                $arrnamasurveyorteam = explode('/', $val);
                $datains['namasurveyor'][$key]['kodetenagakerja']=$maxid;
                $datains['namasurveyor'][$key]['notransaksi']=$notransaksi;
                $datains['namasurveyor'][$key]['team']=$arrnamasurveyorteam[1] ;
                $datains['namasurveyor'][$key]['statuspekerja']='surveyor';
                $datains['namasurveyor'][$key]['namapekerja']=$arrnamasurveyorteam[0] ;
            #insert gis_survey_tenagakerja----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_tenagakerja',$datains['namasurveyor'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "tenagakerja :". $e->getMessage() ; }
            }
            $arrnamapendamping = explode('###', $data['namapendamping']);
            foreach ($arrnamapendamping as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_tenagakerja","kodetenagakerja");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodetenagakerja']>=$maxid ? $maxid=$row['kodetenagakerja'] : false;
                    }
                    $maxid++;
                }
                $arrnamapendampingteam = explode('/', $val);
                $datains['namapendamping'][$key]['kodetenagakerja']=$maxid;
                $datains['namapendamping'][$key]['notransaksi']=$notransaksi;
                $datains['namapendamping'][$key]['team']=$arrnamapendampingteam[1] ;
                $datains['namapendamping'][$key]['statuspekerja']='pendamping';
                $datains['namapendamping'][$key]['namapekerja']=$arrnamapendampingteam[0] ;
            #insert gis_survey_tenagakerja----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_tenagakerja',$datains['namapendamping'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "tenagakerja :". $e->getMessage() ; }
            }
            $arrnamasaksi = explode('###', $data['namasaksi']);
            foreach ($arrnamasaksi as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_tenagakerja","kodetenagakerja");
                $id = fetchData($query);
                $maxid=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodetenagakerja']>=$maxid ? $maxid=$row['kodetenagakerja'] : false;
                    }
                    $maxid++;
                }
                $arrnamasaksiteam = explode('/', $val);
                $datains['namasaksi'][$key]['kodetenagakerja']=$maxid;
                $datains['namasaksi'][$key]['notransaksi']=$notransaksi;
                $datains['namasaksi'][$key]['team']=$arrnamasaksiteam[1] ;
                $datains['namasaksi'][$key]['statuspekerja']='saksi';
                $datains['namasaksi'][$key]['namapekerja']=$arrnamasaksiteam[0] ;
            #insert gis_survey_tenagakerja----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_tenagakerja',$datains['namasaksi'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "tenagakerja :". $e->getMessage() ; }
            }

        #data for gis_survey_alat-------------------------------------------------------------------------------
            $arrjenisalat = explode('###', $data['jenisalat']);
            foreach ($arrjenisalat as $key => $val) {
                $query = selectQuery($dbname,"gis_survey_alat","kodealat");
                $id = fetchData($query);
                $kodealat=1;
                if(!empty($id)) {
                    foreach($id as $row) {
                        $row['kodealat']>=$kodealat ? $kodealat=$row['kodealat'] : false;
                    }
                    $kodealat++;
                }
            //echo $val;
                $arrjenisalatdt = explode('/', $val);
            //echo $arrjenisalatdt;

                $datains['jenisalat'][$key]['kodealat']=$kodealat;
                $datains['jenisalat'][$key]['notransaksi']=$notransaksi;
                $datains['jenisalat'][$key]['status']=$arrjenisalatdt[1];
                $datains['jenisalat'][$key]['jenisalat']=$arrjenisalatdt[0];
            #insert gis_survey_alat----------------------------------
                $strinsert=insertQuery($dbname,'gis_survey_alat',$datains['jenisalat'][$key]);
                $errorDB='';
                try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "jenisalat :". $e->getMessage() ; }
                
                if($arrjenisalatdt[1]=='Consumable'){
                    $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
                    $id = fetchData($query);
                    $maxid=1;
                    if(!empty($id)) {
                        foreach($id as $row) {
                            $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                        }
                        $maxid++;
                    }
                    $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
                    $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
                    $datains['anggarandt'][$key]['kode']=$kodealat;
                    $datains['anggarandt'][$key]['jenisbiaya']='peralatan';
                    $datains['anggarandt'][$key]['satuan']=$arrjenisalatdt[3];
                    $datains['anggarandt'][$key]['biaya']=$arrjenisalatdt[4];
                    $datains['anggarandt'][$key]['jumlah']=$arrjenisalatdt[2];
                    $datains['anggarandt'][$key]['hk']='';
                    $datains['anggarandt'][$key]['subtotal']=$arrjenisalatdt[5];
                    $datains['anggarandt'][$key]['keterangan']=$arrjenisalatdt[6];

                    $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
                    $errorDB='';
                    try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }
                }
            }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='tenagakerja';
            $datains['anggarandt'][$key]['satuan']=$data['satuantenagakerja'];
            $datains['anggarandt'][$key]['biaya']=$data['hargatenagakerja'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahtenagakerja'];
            $datains['anggarandt'][$key]['hk']=$data['hktenagakerja'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotaltenagakerja'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangantenagakerja'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='surveyor';
            $datains['anggarandt'][$key]['jenisbiaya']='konsumsi';
            $datains['anggarandt'][$key]['satuan']=$data['satuankonsumsisurveyor'];
            $datains['anggarandt'][$key]['biaya']=$data['hargakonsumsisurveyor'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahkonsumsisurveyor'];
            $datains['anggarandt'][$key]['hk']=$data['hkkonsumsisurveyor'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalkonsumsisurveyor'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangankonsumsisurveyor'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='tenagakerjarintis';
            $datains['anggarandt'][$key]['jenisbiaya']='konsumsi';
            $datains['anggarandt'][$key]['satuan']=$data['satuankonsumsitkr'];
            $datains['anggarandt'][$key]['biaya']=$data['hargakonsumsitkr'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahkonsumsitkr'];
            $datains['anggarandt'][$key]['hk']=$data['hkkonsumsitkr'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalkonsumsitkr'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangankonsumsitkr'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='transport';
            $datains['anggarandt'][$key]['satuan']=$data['satuantransport'];
            $datains['anggarandt'][$key]['biaya']=$data['hargatransport'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahtransport'];
            $datains['anggarandt'][$key]['hk']=$data['hktransport'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotaltransport'];
            $datains['anggarandt'][$key]['keterangan']=$data['keterangantransport'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='bbm';
            $datains['anggarandt'][$key]['satuan']=$data['satuanbbm'];
            $datains['anggarandt'][$key]['biaya']=$data['hargabbm'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahbbm'];
            $datains['anggarandt'][$key]['hk']=$data['hkbbm'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalbbm'];
            $datains['anggarandt'][$key]['keterangan']=$data['keteranganbbm'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }

            $query = selectQuery($dbname,"gis_survey_anggarandt","kodeanggarandt");
            $id = fetchData($query);
            $maxid=1;
            if(!empty($id)) {
                foreach($id as $row) {
                    $row['kodeanggarandt']>=$maxid ? $maxid=$row['kodeanggarandt'] : false;
                }
                $maxid++;
            }
            $datains['anggarandt'][$key]['kodeanggarandt']=$maxid;
            $datains['anggarandt'][$key]['kodeinduk']=$kodeanggaran;
            $datains['anggarandt'][$key]['kode']='';
            $datains['anggarandt'][$key]['jenisbiaya']='biayacadangan';
            $datains['anggarandt'][$key]['satuan']=$data['satuanbcd'];
            $datains['anggarandt'][$key]['biaya']=$data['hargabcd'];
            $datains['anggarandt'][$key]['jumlah']=$data['jumlahbcd'];
            $datains['anggarandt'][$key]['hk']=$data['hkbcd'];
            $datains['anggarandt'][$key]['subtotal']=$data['subtotalbcd'];
            $datains['anggarandt'][$key]['keterangan']=$data['keteranganbcd'];

            $strinsert=insertQuery($dbname,'gis_survey_anggarandt',$datains['anggarandt'][$key]);
            $errorDB='';
            try{$owlPDO->exec($strinsert); }catch (PDOException $e) {$errorDB .= "anggarandt :". $e->getMessage() ; }



        //print_r($arrjenisalatdt);

            break;
            case 'loadData' :
            $limit = 20;
            $page = 0;
            if (isset($_POST['page'])) {
                $page = $_POST['page'];
                if ($page < 0)
                    $page = 0;
            }
            $offset = $page * $limit;

            $qcount ="select a.notransaksi, a.kodeorg, b.namaorganisasi, a.tanggalmulai, a.tanggalselesai, a.posting from ".$dbname.".gis_survey a 
            Left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi";
            $rcount = fetchData($qcount);
            $jlhbrs = count($rcount);

            $totalPage = ceil($jlhbrs/$limit);
            $optPage = array();
            $totalPage<1 ? $totalPage=1 : null;
            for($i=1;$i<=$totalPage;$i++) {
                $optPage[$i-1] = $i;
            }

            $queryAll ="select a.notransaksi, a.kodeorg, b.namaorganisasi, a.tanggalmulai, a.tanggalselesai, a.posting from ".$dbname.".gis_survey a 
            Left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
            order by a.tanggalmulai desc limit " . $offset . "," . $limit . "";
            $resAll = fetchData($queryAll);
    //exit($queryAll);
            $header = array("No urut","No Transaksi","Nama Perusahaan","Tanggal Mulai","Tanggal Selesai","Status");
            $table ="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
            $table .="<legend>List Data</legend>";
            $table .= "<table id='listData' class='sortable' cellspacing='1' border='0' >";
            $table .= "<thead><tr class='rowheader'>";
            foreach($header as $head) {
                $table .= "<td>".$head."</td>";
            }
            $table .= "<td align=center>*</td>";
            $table .= "</tr></thead>";
            $table .= "<tbody id='bodyList'>";
            foreach ($resAll as $key => $row) {
                if($row['posting']=='2')
                {
                    $qcountkas ="select * from ".$dbname.".keu_tagihanht where nopo='".$row['notransaksi']."'";
                    $rcountkas = fetchData($qcountkas);
            //exit($rcountkas);
                    $qcountkas2 ="select * from ".$dbname.".keu_kasbankdt where keterangan1='".$rcountkas[0]['noinvoice']."'";
                    $rcountkas2 = fetchData($qcountkas2); 
                    $jlhcountkas2 = count($rcountkas2);

                    if($jlhcountkas2>0)
                    {
                        $strupdate = "update " . $dbname . ".gis_survey set posting='3' where notransaksi = '".$row['notransaksi']."'";
                        try {
                            $owlPDO->exec($strupdate);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                }
                
                $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
                foreach($row as $col=>$dat) 
                {
                    if($col=='kodeorg')
                    {
                        $table .= "<td hidden id='".$col."_".$key."'>".$dat."</td>"; 
                    }
                    elseif($col=='posting')
                    {
                        if($dat==0)
                        {
                            $dat='Belum Posting';
                        }
                        elseif($dat==1)
                        {
                            $dat='Tahap Pesetujuan';
                        }
                        elseif($dat==15)
                        {
                            $dat='Ditolak';
                        }
                        elseif($dat==17)
                        {
                            $dat='Koreksi';
                        }
                        elseif($dat==2)
                        {
                            $dat='Menunggu Pencairan Dana';
                        }
                        elseif($dat==3)
                        {
                            $dat='Dalam Proses Survey & Kelengkapan Berkas';
                        }
                        else
                        {
                            $dat='Selesai';
                        }
                        $table .= "<td id='".$col."_".$key."'>".$dat."</td>"; 
                    }
                    elseif($col=='notransaksi')
                    {
                        $table .= "<td id='nourut' align=center>".($key+1)."</td>";
                        $table .= "<td id='notransaksi_".$key."'>".$dat."</td>";
                    }
                    else
                    {
                        $table .= "<td id='".$col."_".$key."'>".$dat."</td>";   
                    }
                }
                
                $table.="<td>";
                if($row['posting']==0 || $row['posting']==17){
                    $table .= "<img src='images/application/application_edit.png' ";
                    $table .= "class=resicon  title='Edit' onclick=edit('".$row['notransaksi']."')>";
                    $table .= "<img src='images/application/application_delete.png' ";
                    $table .= "class=resicon  title='Delete' onclick=deletes('".$row['notransaksi']."')>";
                }

                $srcx="SELECT * FROM `approval` WHERE `notransaksi` = '".$row['notransaksi']."' and `karyawanid`='".$_SESSION['standard']['userid']."' order by level";
                $rescx=fetchData($srcx);
                $jlhcx=count($rescx);
                
                $table .= "<img src='images/pdf.jpg' ";
                $table .= "class=resicon  title='Print Form Survey' onclick=\"masterPDF('gis_survey','" . $row['notransaksi'] . "," . $row['kodeorg'] . "','','gis_slave_RABPDF',event)\">";
                $table .= "<img src='images/pdf.jpg' ";
                $table .= "class=resicon  title='Print Form Survey' onclick=\"masterPDF('gis_survey','" . $row['notransaksi'] . "," . $row['kodeorg'] . "','','gis_slave_formsurveyPDF',event)\">";
                $table .= "<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$row['notransaksi']."','".$row['posting']."')\" src='images/upload-2-xxl.png'/>";
                if($row['posting']==0 || $row['posting']==17){
                    $table .="<img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Ajukan ???' 
                    onclick=\"form_ajukan('".$row['notransaksi']."','".$row['kodeorg']."','".$key."');\" >";
                    
                }
                else
                {
                    if($row['posting']>=3 && $row['posting']!=15){
                        $table .= "<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$row['notransaksi']."','".$row['posting']."')\" src='images/upload-2-xxl.png'/>";
                        $srcx="SELECT * FROM `approval` WHERE `notransaksi` = '".$row['notransaksi']."' and `karyawanid`='".$_SESSION['standard']['userid']."' order by level";
                            //exit($srcx);
                        $rescx=fetchData($srcx);
                        $jlhcx=count($rescx);
                        if($jlhcx==1 && $row['posting']==3)
                        {
                            $table .= "<img src=images/skyblue/posting.png class=resicon class=zImgBtn height='30'  title='Posting Kelengkapan Dokumen' 
                            onclick=\"posting('".$row['notransaksi']."','".$row['kodeorg']."');\" >";
                        }
                        if($jlhcx==1 && ($row['posting']>3 && $row['posting']!=15))
                        {
                            $table .= "<img src=images/skyblue/posted.png class=resicon class=zImgBtn height='30'  title='Dokumen Telah Lengkap' >";
                        }
                    }
                }

                
                $table .="<img src=images/zoom.png class=resicon title='View' onclick=\"html('".$row['notransaksi']."','".$row['kodeorg']."','html');\"></td>";
                $table .= "</tr>";

            }

            $table .= "</tbody>";
            $table .="<tfoot><td colspan=14 align=center>
            <img src='images/".$_SESSION['theme']."/first.png'style='cursor:pointer' onclick=cariBast(0);>&nbsp;
            <img src='images/".$_SESSION['theme']."/prev.png'style='cursor:pointer' onclick=cariBast(" . ($page - 1) . ");>&nbsp;
            ".makeElement('pages','select',$page,array('style'=>'width:50px',
                'onchange'=>'cariBast(this.value)'),$optPage)."&nbsp;
            <img src='images/".$_SESSION['theme']."/next.png'style='cursor:pointer' onclick=cariBast(" . ($page + 1) . ");>&nbsp;
            <img src='images/".$_SESSION['theme']."/last.png'style='cursor:pointer' onclick=cariBast(".($totalPage-1).");>
            </td>
            </tfoot>";
            $table .= "</table>";
            $table .= "</fieldset>";

            echo $table;
            
            break;
            case 'posting':
            $strupdate = "update " . $dbname . ".gis_survey set posting='5' where notransaksi = '".$data['notransaksi']."'";
            try {
                $owlPDO->exec($strupdate);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
            break;
            case 'html':
            
            $sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$data['kodeorg']."'";
            $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
            $qInduk->setFetchMode(PDO::FETCH_ASSOC);
            $rInduk=$qInduk->fetch();

            $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
            $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ);
            while(@$bar1=$res1->fetch()){
                $nama=$bar1->namaorganisasi;
            }

            $arrHead = setheadreport('',$rInduk['induk']);
            $path=$arrHead['logo'];
            $tabs="<div>";
            $tabs.="<p align=center style=font-weight:bold;><font size='5'>SURVEY FORM</font> </p>";
            $tabs.="<p align=center style=font-weight:bold;>".$nama."</p>";
            $tabs.="<hr>";


            $tabs.="<table border=0 align=center>";
            $str=selectQuery($dbname,"pad_5typesurvey","kodesurvey,namasurvey,meliputi");
            $res=fetchData($str);
            $tabs.="<tr>";
            $tabs.="<td style=text-decoration:underline;font-weight:bold; width=200px align=right>Kegiatan Survey:</td>";
            $tabs.="<td width=100px align=center></td>";
            $tabs.="<td style=text-decoration:underline;font-weight:bold; width=200px align=left>Meliputi:</td>";
            $tabs.="</tr>";
            foreach (@$res as $key => $val) {
                $tabs.="<tr>";
                $tabs.="<td id=namatipe align=right>".$val['namasurvey']."</td>";
                $strcheck=selectQuery($dbname,"gis_survey_kegiatan","typesurvey","typesurvey='".$val['kodesurvey']."' and notransaksi='".$data['notransaksi']."'");
                $rescheck=fetchData($strcheck);
                $jlhcheck=count($rescheck);
                if($jlhcheck>0)
                {
                    $tabs.="<td align=center>".makeElement('checktipe_'.$key,'checkbox','1',array('disabled'=>'disabled'))."</td>";
                }
                else
                {
                    $tabs.="<td align=center>".makeElement('checktipe_'.$key,'checkbox',array('disabled'=>'disabled'))."</td>";
                }

                $tabs.="<td id=meliputi align=left>".$val['meliputi']."</td>";
                $tabs.="</tr>";
            }
            $tabs.="</table>";

            $strcheck=selectQuery($dbname,"gis_survey","*","notransaksi='".$data['notransaksi']."'");
            $rescheck=fetchData($strcheck);
            $tabs.="<hr>";
            $tabs.="<p align=center style=font-weight:bold;><font size='3'>KETENTUAN UMUM</font> </p>";
            $tabs.="<hr>";
            $tabs.="<table cellspacing=0 border=1  width=100%>";
            $tabs.="<tr>";
            $tabs.="<th align=center>1</th>";
            $tabs.="<th align=left  colspan=7>Lokasi Kegiatan Survey</th>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Status Lokasi : ".$arrstatuslokasi[$rescheck[0]['statuslokasi']]."</td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Provinsi : ".$rescheck[0]['provinsi']."</td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Kecamatan : ".$rescheck[0]['kabupaten']."</td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Kecamatan : ".$rescheck[0]['kecamatan']."</td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Desa : ".$rescheck[0]['desa']."</td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<th align=center>2</th>";
            $tabs.="<th align=left colspan=7>Jangka Waktu Pelaksanaan</th>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Tanggal Mulai : ".tanggalnormal($rescheck[0]['tanggalmulai'])."</td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Tanggal Selesai : ".tanggalnormal($rescheck[0]['tanggalselesai'])."</td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<th align=center>3</th>";
            $tabs.="<th align=left colspan=7>Personil Survey</th>";
            $tabs.="</tr>";
            $strtenagakerjax=selectQuery($dbname,"gis_survey_tenagakerja","team","notransaksi='".$data['notransaksi']."' group by team ");
            $rstenagakerjax=fetchData($strtenagakerjax);
            foreach (@$rstenagakerjax as $ky => $vl) {
                $tabs.="<tr>";
                $tabs.="<td ></td>";
                $tabs.="<td align=left colspan=7>Team ".$vl['team']." :</td>";
                $tabs.="</tr>";
                $strtenagakerja=selectQuery($dbname,"gis_survey_tenagakerja","*","notransaksi='".$data['notransaksi']."' and team='".$vl['team']."' order by kodetenagakerja asc");
                $rstenagakerja=fetchData($strtenagakerja);
                foreach (@$rstenagakerja as $keypekerja => $valpekerja) {
                    $tabs.="<tr>";
                    $tabs.="<td ></td>";
                    if($valpekerja['statuspekerja']=='surveyor' || $valpekerja['statuspekerja']=='pendamping')
                    {
                        $tabs.="<td align=left colspan=7>".($keypekerja+1).". ".$valpekerja['statuspekerja']." : ".$valpekerja['namapekerja']."</td>";
                    }
                    else
                    {
                        $tabs.="<td align=left colspan=7>".($keypekerja+1).". ".$valpekerja['statuspekerja']." : ".$valpekerja['namapekerja']."</td>";
                    }
                    $tabs.="</tr>";
                }
            }
            $tabs.="<tr>";
            $tabs.="<th align=center>4</th>";
            $tabs.="<th align=left colspan=7>Rencana Anggaran Survey</th>";
            $tabs.="</tr>";
            $tabs.="<tr >";
            $tabs.="<th></th>";
            $tabs.="<th align=center >Jenis Biaya</th>";
            $tabs.="<th align=center >Jumlah</th>";
            $tabs.="<th align=center >Satuan</th>";
            $tabs.="<th align=center >Harga</th>";
            $tabs.="<th align=center >Hari Kerja</th>";
            $tabs.="<th align=center >Sub Total</th>";
            $tabs.="<th align=center >Keterangan</th>";
            $tabs.="</tr>";
            $stranggaran=selectQuery($dbname,"gis_survey_anggaranht","*","notransaksi='".$data['notransaksi']."'");
            $resanggaran=fetchData($stranggaran);
            $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='tenagakerja'");
            $resanggarandt=fetchData($stranggarandt);
            $tabs.="<tr>";
            $tabs.="<td></td>";
            $tabs.="<td>Tenaga Kerja</td>";
            $tabs.="<td>".$resanggarandt[0]['jumlah']."</td>";
            $tabs.="<td>Orang</td>";
            $tabs.="<td>".number_format($resanggarandt[0]['biaya'])."</td>";
            $tabs.="<td>".$resanggarandt[0]['hk']."</td>";
            $tabs.="<td>".number_format($resanggarandt[0]['subtotal'])."</td>";
            $tabs.="<td>".$resanggarandt[0]['keterangan']."</td>";
            $tabs.="</tr>";
            $stralat=selectQuery($dbname,"gis_survey_alat","*","notransaksi='".$data['notransaksi']."' and status='Consumable'");
            $resalat=fetchData($stralat);
            foreach (@$resalat as $keyalat => $valalat) {
                $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='peralatan' and kode='".$valalat['kodealat']."'");
                $resanggarandt=fetchData($stranggarandt);
                $tabs.="<tr>";
                $tabs.="<td></td>";
                $tabs.="<td >".$valalat['namaalat']."</td>";
                $tabs.="<td>".$resanggarandt[0]['jumlah']."</td>";
                $tabs.="<td>".$resanggarandt[0]['satuan']."</td>";
                $tabs.="<td>".number_format($resanggarandt[0]['biaya'])."</td>";
                $tabs.="<td></td>";
                $tabs.="<td>".number_format($resanggarandt[0]['subtotal'])."</td>";
                $tabs.="<td>".$resanggarandt[0]['keterangan']."</td>";
                $tabs.="</tr>";
            }
            $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='konsumsi' and kode='surveyor'");
            $resanggarandt=fetchData($stranggarandt);
            $tabs.="<td></td>";
            $tabs.="<td >Surveyor</td>";
            $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
            $tabs.="<td >Orang</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
            $tabs.="</tr>";
            $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='konsumsi' and kode='tenagakerjarintis'");
            $resanggarandt=fetchData($stranggarandt);
            $tabs.="<tr>";
            $tabs.="<td></td>";
            $tabs.="<td >Tenaga Kerja Rintis</td>";
            $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
            $tabs.="<td >Orang</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
            $tabs.="</tr>";
            $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='transport'");
            $resanggarandt=fetchData($stranggarandt);
            $tabs.="<tr>";
            $tabs.="<td></td>";
            $tabs.="<td >Transportasi/Sewa Motor</td>";
            $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
            $tabs.="<td >Unit</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
            $tabs.="</tr>";
            $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='bbm'");
            $resanggarandt=fetchData($stranggarandt);
            $tabs.="<tr>";
            $tabs.="<td></td>";
            $tabs.="<td >Bahan Bakar Minyak</td>";
            $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
            $tabs.="<td >Liter</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
            $tabs.="</tr>";
            $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='biayacadangan'");
            $resanggarandt=fetchData($stranggarandt);
            $tabs.="<tr>";
            $tabs.="<td></td>";
            $tabs.="<td >Bahan Bakar Minyak</td>";
            $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
            $tabs.="<td >".$resanggarandt[0]['satuan']."r</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
            $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
            $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
            $tabs.="</tr>";
            $tabs.="<tr bgcolor='#77ff77'>";
            $tabs.="<td></td>";
            $tabs.="<td colspan=5>Total</td>";
            $tabs.="<td>".number_format($resanggaran[0]['totalbiaya'])."</td>";
            $tabs.="<td ></td>";
            $tabs.="</tr>";
            $tabs.="<tr>";
            $tabs.="<th align=center>5</th>";
            $tabs.="<th align=left colspan=7>Alat Survey Yang Dibutuhkan</th>";
            $tabs.="</tr>";
            $stralat=selectQuery($dbname,"gis_survey_alat","*","notransaksi='".$data['notransaksi']."'");
            $resalat=fetchData($stralat);
            foreach (@$resalat as $keyalat => $valalat) {
                $tabs.="<tr>";
                $tabs.="<td ></td>";
                $tabs.="<td align=left colspan=7>".($keyalat+1).". ".$valalat['namaalat']." </td>";
                $tabs.="</tr>";
            }
            $tabs.="</table>";
            $tabs.="<hr>";
            $tabs.="<p align=center style=font-weight:bold;><font size='3'> Penjelasan Singkat Teknis & Target Pelaksanaan Kegiatan Survey </font> </p>";
            $tabs.="<hr>";
            $tabs.="<table cellspacing=0 style=min-height:100px; border=1  width=100%>";
            $tabs.="<tr>";
            $tabs.="<td align=left colspan=7><textarea disabled style='width:900px;height:300px;'>".$rescheck[0]['penjelasan']."</textarea></td>";
            $tabs.="</tr>";
            $tabs.="</table>";
            $tabs.="</table>";
            $tabs.="<hr>";
            $tabs.="<table cellspacing=0 style=min-height:100px; border=1  width=100%>";
            $tabs.="<tr>";
            $tabs.="<td align=center colspan=7>Yang Mengajukan</td>";
            $tabs.="<td align=center colspan=7>Persetujuan 1</td>";
            $tabs.="<td align=center colspan=7>Persetujuan 2</td>";
            $tabs.="<td align=center colspan=7>Persetujuan 3</td>";
            $tabs.="</tr>";
            $src="SELECT * FROM `approval` WHERE `notransaksi` = '".$data['notransaksi']."' order by level";
            $resc=fetchData($src);

            $data['karyawanid']=array();
            $data['tanggal']=array();
            $data['status']=array();
            $data['komentar']=array();
            foreach (@$resc as $kye => $vle) {
               $data['karyawanid'][]=$vle['karyawanid'];
               $data['tanggal'][]=$vle['tanggal'];
               $data['status'][]=$vle['status'];
               $data['komentar'][]=$vle['komentar'];
           }
           $tabs.="<tr>";
           $tabs.="<td align=center colspan=7>".$optKry[$rescheck[0]['createdby']]."</td>";
           foreach ($data['karyawanid'] as $kuy => $vul) {
            $tabs.="<td align=center colspan=7>".$optKry[$vul]."</td>";
        }
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td align=center colspan=7></td>";
        foreach ($data['status'] as $kuy => $vul) {
            $tabs.="<td align=center colspan=7>".$arrstatusaju[$vul]."</td>";
        }
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td align=center colspan=7></td>";
        foreach ($data['komentar'] as $kuy => $vul) {
            $tabs.="<td align=center colspan=7>".$vul."</td>";
        }
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td align=center colspan=7>".tanggalnormal($rescheck[0]['createdtime'])."</td>";
        foreach ($data['tanggal'] as $kuy => $vul) {
            $tabs.="<td align=center colspan=7>".tanggalnormal($vul)."</td>";
        }
        $tabs.="</tr>";
        $tabs.="</table>";
        

        $tabs.="</div>";
        
        echo $tabs;
        break;
        
        case 'deletes':
        $strdelete=deleteQuery($dbname,'gis_survey',"notransaksi='".$data['notransaksi']."'");
        $errorDB='';
        try{$owlPDO->exec($strdelete); }catch (PDOException $e) {$errorDB .= "delete :". $e->getMessage() ; }
        break;

        
        case'pengajuan';
        $str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
                  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SRV' and a.level='1' and a.kodeunit='".$data['kodeorg']."'  order by b.namakaryawan asc";// exit('error'.$str);
                  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                  $res->setFetchMode(PDO::FETCH_ASSOC);
                  $optKry="";
                  while($rkry=$res->fetch()){
                    $optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
                }

                

                $tab = "<table cellspacing=1 border=0 width=100%>
                <tr class=rowcontent>
                <td width=200px>" . $_SESSION['lang']['notransaksi'] . "</td>
                <td width=5px>:</td>
                <td id=notran_aju>".$data['notransaksi']."</td>
                </tr>
                
                <tr class=rowcontent>
                <td width=200px>" . $_SESSION['lang']['kepada'] . "</td>
                <td width=5px>:</td>
                <td><select id=kepada style='width:99%;'>".$optKry."</select></td>
                </tr>

                <tr class=rowcontent>
                <td></td><td><input id=numrow style=display:none value=".$data['numrow']."></td>
                <td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
                </tr>               
                </table>";
                
                echo $tab;
                break;
                case'ajukan':
                
                if($data['kepada']=='' || $data['notransaksi']==''){
                    exit('Error : Isikan nama penyetuju.');
                }
        //update flag menjadi 1
                $str = "update " . $dbname . ".gis_survey set posting='1' where notransaksi = '".$data['notransaksi']."'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                
                $str = "select * from " . $dbname . ".approval where notransaksi = '".$data['notransaksi']."' and jenispersetujuan='SRV'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $numrows=owlBaris($res);
                $brs=$numrows;
                if($brs==0){
        //insert ke table approval
                    $str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                        `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
                        values ('','".$data['notransaksi']."','SRV','1','" . $data['kepada']."','0','','','')";
                    }
                    else
                    {
                        $str = "update " . $dbname . ".approval set notransaksi='".$data['notransaksi']."/koreksi' where notransaksi = '".$data['notransaksi']."'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        $str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                            `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
                            values ('','".$data['notransaksi']."','SRV','1','" . $data['kepada']."','0','','','')";
                        }
        // exit('error'.$str);
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        
                        break;
                        case 'showupload':
                        $tab="";
                        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
                        $tab.="<tr>
                        <td>".$_SESSION['lang']['notransaksi']."</td>
                        <td>:</td>
                        <td>
                        <label id='noupload' style='display:none'>".$data['notransaksi']."</label>
                        <label style='font-weight:bold'>".$data['notransaksi']."</label>
                        </td>
                        </tr>";

                        $tab.="<tr><td colspan=4><hr></td></tr>
                        <tr>
                        <td>Filename</td>
                        <td>:</td>
                        <td>
                        <input type='file' name='upload' id='upload' >
                        </td>
                        </tr>";
                        if($data['posting']<2 or $data['posting']=17 or $data['posting']=3)
                        {
                            $tab.="<tr>
                            <td colspan=2></td>
                            <td>
                            <button class=mybutton onclick=\"submitfile()\">Submit</button>
                            </td>
                            </tr>";
                        }
                        else
                        {
                            $tab.="<tr hidden>
                            <td colspan=2></td>
                            <td>
                            <button class=mybutton onclick=\"submitfile()\">Submit</button>
                            </td>
                            </tr>";
                           
                        }
                        
                        $tab.="</table>
                        <p />";
                        
                        $tab.="<fieldset>
                        <legend>".$_SESSION['lang']['list']."</legend>
                        <table class='sortable' cellspacing='1' border='0' width=100%>
                        <thead>
                        <tr class=rowheader>
                        <td align='center' width=50px>No.</td>
                        <td align='center' width=50px>File Type</td>
                        <td align='center'>Filename</td>
                        <td align='center' width=50px>Action</td>
                        </tr>
                        </thead>
                        <tbody id='listfiles'>
                        </tbody>
                        </table>
                        </fieldset> ";
                        
                        echo $tab;
                        break;
                        case 'submitfile':
                        $tgl = date("YmdHis");
                        $his = date("His");
                        $nmTemp=str_replace('-','',str_replace('/','',$data['notransaksi']));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
                        if($data['fileupload']!=''){
                            if($_FILES['file']['error']==0){    
                                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                                $filename = $nmTemp."_".$his."".$filetype;
                                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                                
                                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                                    $str = "insert into ".$dbname.".listfile_gis_survey values ('','".$data['notransaksi']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                                    try{
                                        $owlPDO->exec($str);
                                        if (!file_exists($path)) {
                                            mkdir($path, 0777, true);
                                        }
                                        file_put_contents($path.$filename,$file_tmpname);
                                    }
                                    catch(PDOException $e){
                                        echo " Gagal," . addslashes($e->getMessage());
                                    }
                                }else{
                                    exit("Warning : Format file upload tidak boleh ".$filetype);
                                }
                            }
                        }
                        break;
                        case'viewfile':
                        $tab="";
                        $tab.="<img src='".$path.$data['namafile']."' style='width:600px;height:400px;'>";
                        
                        echo $tab;
                        break;
                        
                        case 'deletefile':
                        $namafile=$data['namafile'];
        $str="delete from ".$dbname.".listfile_gis_survey where notransaksi='".$data['notransaksi']."' and namafile='".$data['namafile']."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
        case'viewlistfile':
        $tab.="<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
        <table class='sortable' cellspacing='1' border='0' style=min-width:350px>
        <thead>
        <tr class=rowheader>
        <td align='center' width=50px>No.</td>
        <td align='center' width=50px>File Type</td>
        <td align='center'>Filename</td>
        <td align='center' width=50px>Action</td>
        </tr>
        </thead>
        <tbody id='loadfilesdetail'>
        </tbody>
        </table>
        </fieldset> ";
        echo $tab;
        break;
        
        case 'deletefileall':
        $str="select * from ".$dbname.".listfile_gis_survey where notransaksi='".$data['notransaksi']."'"; //exit('error'.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $pathx = $path.$bar['namafile'];
            unlink($pathx);
        }
        
        $str="delete from ".$dbname.".listfile_gis_survey where notransaksi='".$data['notransaksi']."'";
        try{
            $owlPDO->exec($str);
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
        case 'loadfiles':
        $no = 0;
        $tab = "";  
        $str="select * from ".$dbname.".gis_survey where notransaksi = '".$data['notransaksi']."'";
        $res=fetchData($str);
        $posting=$res[0]['posting'];
        
        $str="select * from ".$dbname.".listfile_gis_survey where notransaksi = '".$data['notransaksi']."' and status='1'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>";
                @$icon = seticonfile($val['formaticon']);   
                $tab.="<td style='text-align:center'>
                <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";

                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($data['posting']<2 or $data['posting']=17 or $data['posting']=3){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";                 
                }
                
                $tab."  </td>
                </tr>";
            }   
        }
        
        echo $tab;
        break;
        case 'excel':
        
        $stream.= "</table>";
        
        $tglSkrg = date("Ymd");
        $nop_ = "RAB_SURVEY_".$tglSkrg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                window.location='tempExcel/" . $nop_ . ".xls';
                </script>";
            }
            fclose($handle);
        }
        
        break;

        
    }