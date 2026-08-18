<?
//@Copy nangkoelframework 

require_once('master_validation.php');
include('lib/nangkoelib.php');

echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/keu_2laporanasistensihutangpiutang.js?v=<?php echo time(); ?>></script>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>"></script>
<?

include('master_mainMenu.php'); 
OPEN_BOX('','<span class=judul>'.getMenu('keu_2laporanasistensihutangpiutang').'</span><br>');

# Option PT #
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arropt = getOrgDetail(3);
foreach($arropt as $key => $val):
    $optpt.="<option value='".$key."'>".$val."</option>";
endforeach;

# Option UNIT #
$optunit=$optperiode=$opttipelaporan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrunit = getOrgDetail(2);

# Option PERIODE #
$sql = selectQuery($dbname,"setup_periodeakuntansi","periode","kodeorg IN (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE kodeorganisasi IN (".$arrunit.") and inti=0)","periode DESC",true);
$res = fetchData($sql,"OBJECT");

foreach($res as $val):
    $optperiode.="<option value='".$val->periode."'>".$val->periode."</option>";
endforeach;

# TIPE LAPORAN #
$arrtipelaporan = array('1'=>'Hutang Investasi','2'=>'Biaya Investasi dan Bulanan','3'=>'Bulanan');
foreach($arrtipelaporan as $key => $val):
    $opttipelaporan .= "<option value='".$key."'>".$val."</option>";
endforeach;

?>

<fieldset style="width: 450px;margin-right:25px;float:left;"> 
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr>
    <td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td>
    <td><select class='select2' id=kodept style='width:200px;' onchange=getUnit(this.options[this.selectedIndex].value)><?php echo $optpt; ?></select></td>
</tr>
<tr hidden>
    <td><label><?php echo $_SESSION['lang']['kodeorganisasi']?></label></td><td>:</td>
    <td><select class='select2' id=kodeunit style='width:200px;' onchange=document.getElementById('container').innerHTML=''><?php echo $optunit; ?></select></td>
</tr>
<tr hidden>
    <td><label><?php echo $_SESSION['lang']['tipe']?> Laporan</label></td><td>:</td>
    <td><select class='select2' id=tipelaporan style='width:200px;' onchange=document.getElementById('container').innerHTML=''><?php echo $opttipelaporan; ?></select></td>
</tr>
<tr>
    <td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td>
    <td><select class='select2' id=periode style='width:200px;'><?php echo $optperiode; ?></select></td>
</tr>
<tr>
    <td><td><td>
        <input type=hidden id=proses value='add' />
        <button class=mybutton onclick=preview("html")><?php echo $_SESSION['lang']['preview'] ?></button>
        <button class=mybutton onclick=preview("excel")><?php echo $_SESSION['lang']['excel'] ?></button>
    </td>
</tr>
</table>
</fieldset>

<fieldset style="width: 700px;" > 
<legend><b style=color:blue;><?php echo $_SESSION['lang']['informasi']?></b></legend>
<ul>
    <li><b><span style=color:blue;>Baris</span> mewakili <span style=color:blue;>Pemilik</span></b></li>
    <li><b><span style=color:blue;>Kolom</span> mewakili <span style=color:blue;>Pemberi</span></b></li>
    <li><b><span style=color:blue;>Kolom Hutang</span> merupakan <span style=color:blue;>Jumlah total hutang yang dimiliki oleh pemberi hutang terhadap penerima</span></b></li>
    <li><b><span style=color:blue;>Kolom Realisasi</span> merupakan <span style=color:blue;>Jumlah hutang yang sudah dibayar atau terealisasi (Menggunakan Jurnal Memorial)</span></b></li>
</ul>
</fieldset>
<?

CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset> 
	 <div id=container style='width:100%;height:70vh;overflow:auto;'>
</div>";
CLOSE_BOX();
close_body();

?>
