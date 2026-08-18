<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<?php

$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
$gudang_detailAkses=" (".$unitDetailAkses.") ";

// create array 
// Hapus tanda kutip tunggal
$stringData = str_replace("'", "", $unitDetailAkses);

// Ubah menjadi array
$arrayData = explode(',', $stringData);
// GUDANGX
// Array untuk menampung klausa-klausa LIKE
$conditions_kodeorganisasi = [];

// Loop melalui setiap nilai dan buat klausa LIKE
foreach ($arrayData as $value) {
    $conditions_kodeorganisasi[] = "kodeorganisasi LIKE '{$value}%'";
}

// Gabungkan semua klausa LIKE dengan 'OR'
$whereClause_gudangx =  "AND (\n    " . implode(" OR\n    ", $conditions_kodeorganisasi) . "\n)";
// AKHIR GUDANGX


if(count($unitDetailAkses) > 0){
    $optUnit2="<option value=''>Pilih Data</option>";
    $sPeriode="select distinct kodeorg,periode from ".$dbname.".setup_periodeakuntansi where 
    kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' ".$whereClause_gudangx.") and tutupbuku=0 order by periode";
}else{
    $sPeriode="select distinct kodeorg,periode from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%') and tutupbuku=0 order by periode";
}

$rPeriode=fetchData($sPeriode);
foreach ($rPeriode as $key => $val) {
    $lstData[substr($val['kodeorg'],0,4)]=substr($val['kodeorg'],0,4);
    $lstPeriode[$val['periode']]=$val['periode'];
}
$optUnit.="<select id=unitId style=width:150px>";
foreach ($lstData as $key2) {
    $optUnit2.="<option value='".$key2."'>".getNamaOrg($key2)." - ".$key2."</option>";
}
$optUnit .= $optUnit2;
$optUnit.="</select>";
$optPeriode="<select id=periodeId style=width:150px>";
foreach ($lstPeriode as $key2) {
    $optPeriode.="<option value='".$key2."'>".$key2."</option>";
}
$optPeriode.="</select>";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/log_3cekgudang.js?v=<? echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.(getMenu('log_3cekgudang')).'</span><br>');
?>
<div>


<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><?php echo $optUnit?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><?php echo $optPeriode?></td></tr>
<tr><td><td><td><button onclick="prosesCek('printContainer')" class="mybutton" name="preview" id="preview"><? echo $_SESSION['lang']['proses'] ?></button></td></tr>

</table>
</fieldset>
</div>


<div>
<fieldset>
    <td style='vertical-align:top'>
            <table style='float:left'>		
                <div>
                <legend>Note</legend>
                <hr>
                <table  cellpadding=5 cellspacing=1 border=0 style='font-weight:bold'>
                    <ul>
                        <li style='background-color:#d1e3fa !important; color:black;border:unset !important'>
                            Cek Semua Unit Gudang yang ada pada pilihan (options)
                        </li>
                        <li style='background-color:#d1e3fa !important; color:black;border:unset !important'>
                            Jika Ada Akun Belum Balance, tidak bisa lanjut tahap 4. Tutup Buku Fisik
                        </li>
                    </ul>
                </table>    
                </div>
            </table>
        </td>

</fieldset>
</div>


<?
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer' style='overflow:auto;height:400px;'></div>

<?php

CLOSE_BOX();
echo close_body();
?>