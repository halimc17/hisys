<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="application/javascript" src="js/kebun_3tutuparesta.js"></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('kebun_3tutuparesta').'</span>');
$lksi=substr($_SESSION['empl']['lokasitugas'],0,4);
// $sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$lksi."'";
// $qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
// $qKbn->setFetchMode(PDO::FETCH_ASSOC);
// $optKbn="";
// while($rKbn=$qKbn->fetch())
// {
    // $optKbn.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['namaorganisasi']."</option>";
// }

// if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
// 	$optKbn.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$lksi."' and tipe='KEBUN'",'2','0',true);
// }else{
// 	$optKbn.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// 	$optKbn.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='KEBUN'",'2','0',true);
// }
$arrUnitKbn = getOrgDetail(23);
foreach ($arrUnitKbn as $key => $val) {
	$optKbn.= "<option value=".$key.">".$key." - ".getNamaOrg($val)."</option>";
}

$tahun=date("Y");

//$arrPeriod = month_inbetween((date("Y")-1).'-01',(date("Y")+1).'-12');
if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
	$bulantahun = $_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan'];
	$arr=array($bulantahun=>$bulantahun);
	foreach($arr as $bar => $res)
	{
		$optPerList.="<option value=".$bar.">".$res."</option>";
	}
}
/*
$periode=(date("Y")-1).'-01';
$perakhir=date("Y-m-d");
//echo $perakhir;
//$perakhir=(date("Y")+1).'-01';

#bentuk daftar periode sampai periode terakhir

$start = new DateTime($periode.'-01');
$end = new DateTime($perakhir.'-01');
$inc = DateInterval::createFromDateString('first day of next month');
$end->modify('+1 day');
$p = new DatePeriod($start,$inc,$end); 
$optPerList = ""; 
foreach ($p as $d)
{
    $optPerList.="<option value='".$d->format('Y-m')."'>".$d->format('Y-m')."</option>";
}   

$optPerList = ""; 
for($x=0;$x<24;$x++){
	$d=mktime(0,0,0,1+$x,15,date('Y')-1);
	$optPerList.="<option value='".date('Y-m',$d)."'>".date('Y-m',$d)."</option>";
}
*/
?>





<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="entryForm">
<fieldset style="float:left">
<legend><?php echo $_SESSION['lang']['entryForm']?></legend>
<table cellspacing="1" border="0">
<tr>
<td><?php echo $_SESSION['lang']['kebun']?></td>
<td>:</td>
<td><select id="idKbn" name="idKbn" onchange='getPrd()' style="width:150px;"><?php echo $optKbn ?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['periode']?></td>
<td>:</td> 
<td>
    <select id="Tahun" name="Tahun" style="width:150px;"><?php echo $optPerList ?></select>
	
</td>
</tr>
<!--<?php echo makeElement('Tahun',"select","",array('style'=>'width:70px'),$arrPeriod);?>-->
<tr>
<td><td><td id="tmblHeader">
<button class=mybutton id='dtl_pem' onclick='saveData()'><?php echo $_SESSION['lang']['preview']?></button><button class=mybutton id='cancel_gti' onclick='cancelSave()'>Reset</button>
</td></td></td>
</tr>
</table>
</fieldset>

</div>

<?php
CLOSE_BOX();

?>
<div id="result" style="display:none;">
<?php OPEN_BOX(); ?>
<div id="list_ganti" >



</div>
<?php CLOSE_BOX();?>
</div>
<?php 

echo close_body();
?>