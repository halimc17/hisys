<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script type="application/javascript" src="js/kebun_2historisaresta.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2historisaresta').'</span><br>');
// $sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by induk asc";
// $qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
// $qKbn->setFetchMode(PDO::FETCH_ASSOC);
// while($rKbn=$qKbn->fetch()){
	// 	$d=getNamaOrg($rKbn['kodeorganisasi'],'induk');
	// 	if($d!=$n){			
// 		$optKbn.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
//     $optKbn.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
// 	$n=$d;
// 	if($d!=$n){			
	// 		$optKbn.="</optgroup>";
// 	}
// }


$optKbn="";
$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optKbn.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optKbn.="<option value='".$key."'>".$key." - ".$val."</option>";			
	$n=$d;
	if($d!=$n){
		$optKbn.="</optgroup>";
	}
}

$optTahun = array();
for($i=date('Y');$i>date('Y')-10;$i--) {
	$optTahun[$i] = $i;
}
?>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="entryForm">
<fieldset style='float:left'>
<legend><?php echo $_SESSION['lang']['form']?></legend>
<table cellspacing="1" border="0">
<tr>
<td><?php echo $_SESSION['lang']['kebun']?></td>
<td>:</td>
<td><select id="idKbn" class=select2 name="idKbn" style="width:170px;"><?php echo $optKbn ?></select></td>

<td><?php echo $_SESSION['lang']['tahun']?></td>
<td>:</td>
<td><?php echo makeElement('tahun','select',date('Y'),array(),$optTahun)?></td>
</tr>
<tr>
<td colspan="2"><td colspan="3" id="tmblHeader">
<button class=mybutton id='dtl_pem' onclick='previewData();'><?php echo $_SESSION['lang']['preview']?></button>
<button class=mybutton id='dtl_xls' onclick='detexcel(event);'><?php echo $_SESSION['lang']['excel']?></button>
</td>
</tr>
</table>
</fieldset>

</div>

<?php
CLOSE_BOX();

?>
<div id="result" style="display:none;" >
<?php OPEN_BOX(); ?>
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='list_ganti' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='list_ganti' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
<div id="list_ganti" >

	</div>


</div>
<?php CLOSE_BOX();?>
</div>
<?php 

echo close_body();
?>