<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script language=javascript1.2 src='js/log_biayakirim.js'></script>
<?
include('master_mainMenu.php');

// Options
$str="select a.supplierid,b.namasupplier from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.tipe='TRANSPORTIR'";
$res=fetchData($str);
$optTrp=array();
foreach($res as $key=>$val)
{
	$optTrp[$val['supplierid']] = $val['namasupplier'];
}
// $optTrp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',
                     // "(kodekelompok like 'T%' or kodekelompok like 'S001%')");
if($_SESSION['language']=='EN'){
    OPEN_BOX('','<span class=judul>'.strtoupper('Material Transport cost allocation').'</span><br>');
}else{
    OPEN_BOX('','<span class=judul>'.strtoupper('Pembebanan biaya pengiriman').'</span><br>');
}
echo"<fieldset style='float:left;'><legend><b>Form</b></legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
            <tr>
                <td>".$_SESSION['lang']['notransaksi']."</td>
                <td>:</td>
                <td colspan=2>
                    <input type=text  disabled id=notransaksi onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:195px;\">
                </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['nodok']."</td>
                <td>:</td>
                <td colspan=2>
                    <input type=text  disabled id=nodok onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:195px;\"></td>
                <td>

                <button class=mybutton  id=tmblCariNoDok onclick=tambahDok('".$_SESSION['lang']['find']."',event)>".$_SESSION['lang']['find']."</button>                       
                </td>
            </tr>
            
            <tr>
                <td>".$_SESSION['lang']['transporter']."</td> 
                <td>:</td>
                <td>".makeElement('transporter','select','',array('style'=>'width:200px','onchange'=>'getakunpajak()'),$optTrp)."</td>
                <td hidden>".makeElement('kodeorg','text','',array('style'=>'width:200px','disabled'=>'disabled'))."</td>
                <td hidden>".makeElement('jenisx','text','',array('style'=>'width:200px','disabled'=>'disabled'))."</td>
            </tr>

            <tr>
                <td>".$_SESSION['lang']['jumlah']." (Rp)</td> 
                <td>:</td>
                <td colspan=3><input type=text class=myinputtextnumber  id=jumlah  value=0 style=width:150px onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('jumlah');\"> </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['pajak']."</td> 
                <td>:</td>
                <td>".makeElement('pajak','text','',array('style'=>'width:200px','disabled'=>'disabled'))."</td>
                <td hidden>".makeElement('noakun','text','',array('style'=>'width:200px','disabled'=>'disabled'))."</td>
                <td hidden>".makeElement('noaruskas','text','',array('style'=>'width:200px','disabled'=>'disabled'))."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['tanggal']." Input</td> 
                <td>:</td>
                <td>".makeElement('tanggalinput','tanggal',date('d-m-Y'),array('style'=>'width:200px','disabled'=>'disabled'))."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['tanggal']." Posting</td> 
                <td>:</td>
                <td>".makeElement('tanggalposting','tanggal','',array('style'=>'width:200px'))."</td>
            </tr>
            <tr><td colspan=2></td>
                    <td colspan=3>
                            <button class=mybutton id=simpan onclick=simpan()>".$_SESSION['lang']['save']."</button>
                            <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                    </td>
            </tr>

        </table></fieldset>
        <input type=hidden id=method value='insert'>";
        
        
        echo"<fieldset style='float:left;height:137px '><legend><b>".$_SESSION['lang']['keterangan']."</b></legend>
    <table>
        <tr>
            <td>No. Dok</td>
            <td>:</td>
            <td>Nomor document yang akan dilakukan proses penambahan biaya, gunakan tombol cari</td>
        </tr>
        <tr>
            <td>Kode Barang</td>
            <td>:</td>
            <td>Barang yang akan dilakukan proses penambahan biaya</td>
        </tr>
        <tr>
            <td>Kode Gudang</td>
            <td>:</td>
            <td>Gudang Barang diterimakan</td>
        </tr>
        <tr>
            <td>Transporter</td>
            <td>:</td>
            <td>Transporter yang digunakan</td>
        </tr>
        <tr>
            <td>Jumlah</td>
            <td>:</td>
            <td>Jumlah rupiah didalam dokumen yang akan diproses</td>
        </tr>
    </table></fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset style=float:left>
		<legend>".$_SESSION['lang']['list']."</legend>
                    ".$_SESSION['lang']['nodok']." : <input type=text   id=nodoksch onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:150px;\">
                        <button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>