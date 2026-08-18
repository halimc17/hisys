<?
//@Copy nangkoelframework 

require_once('master_validation.php');
include('lib/nangkoelib.php');

echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/keu_2rekappembayaraninv.js?v=<?php echo time(); ?>></script>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>"></script>
<?

include('master_mainMenu.php'); 
OPEN_BOX('','<span class=judul>'.getMenu('keu_2rekappembayaraninv').'</span><br>');

# Option PT #
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arropt = getOrgDetail(3);
foreach($arropt as $key => $val):
    $optpt.="<option value='".$key."'>".$val."</option>";
endforeach;

# Option UNIT #
$optunit=$optperiode=$optstatuslaporan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optsupplier="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrunit = getOrgDetail(2);

# Option PERIODE #
$sql = selectQuery($dbname,"setup_periodeakuntansi","periode","kodeorg IN (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE kodeorganisasi IN (".$arrunit.") and inti=0)","periode DESC",true);
$res = fetchData($sql,"OBJECT");

foreach($res as $val):
    $optperiode.="<option value='".$val->periode."'>".$val->periode."</option>";
endforeach;

# TIPE LAPORAN #
$sql = selectQuery($dbname,"log_5supplier","*");
$res = fetchData($sql);
foreach($res as $key => $val):
    $optsupplier .= "<option value='".$val['supplierid']."'>[".$val['supplierid']."] - ".$val['namasupplier']."</option>";
endforeach;

# Status Laporan #
$arrstatuslaporan = array('1' => 'Semua (Semua Transaksi)', '2' => 'Invoice Sudah Terbuat', '3' => 'Invoice Belum Dibuat');
foreach($arrstatuslaporan as $key => $val):
    $optstatuslaporan .= "<option value='".$key."'>".$val."</option>";
endforeach;

?>

<fieldset style="width: 450px;"> 
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr>
    <td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td>
    <td><select class='select2' id=kodept style='width:200px;' onchange=getUnit(this.options[this.selectedIndex].value)><?php echo $optpt; ?></select></td>
</tr>
<tr>
    <td><label><?php echo $_SESSION['lang']['kodeorganisasi']?></label></td><td>:</td>
    <td><select class='select2' id=kodeunit style='width:200px;' onchange=document.getElementById('container').innerHTML=''><?php echo $optunit; ?></select></td>
</tr>
<tr>
    <td><label><?php echo $_SESSION['lang']['supplier']?></label></td><td>:</td>
    <td><select class='select2' id=supplier style='width:200px;' onchange=document.getElementById('container').innerHTML=''><?php echo $optsupplier; ?></select></td>
</tr>
<tr>
    <td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td>
    <td><select class='select2' id=periode style='width:200px;'><?php echo $optperiode; ?></select></td>
</tr>
<tr>
    <td><label><?php echo $_SESSION['lang']['status']?> Laporan</label></td><td>:</td>
    <td><select class='select2' id=statuslaporan style='width:200px;'><?php echo $optstatuslaporan; ?></select></td>
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
<?

CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset> 
	 <div id=container style='width:100%;height:300px;overflow:auto;'>
</div>";
CLOSE_BOX();
close_body();

?>
