<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src='js/zTools.js'></script>
<script language="javascript" src='js/zReport.js'></script>
<script type="application/javascript" src="js/kebun_3AmbilKgTimbangan.js?v=<?php echo time(); ?>"></script>
<?php
$lksi=substr($_SESSION['empl']['lokasitugas'],0,4);

$sKbn="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (kodeorganisasi IN (".getOrgDetail(24).")) or (tipe = 'AFDELING' and LEFT(kodeorganisasi,4) IN (".getOrgDetail(24).")) order by kodeorganisasi ";
$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
$qKbn->setFetchMode(PDO::FETCH_ASSOC);
$optKbn="";
while($rKbn=$qKbn->fetch())
{
	if ($rKbn['kodeorganisasi'] == $lksi) {
		$optKbn.="<option value=".$rKbn['kodeorganisasi']." selected>".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
	} else {
		$optKbn.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
	}
}

//1 = internal, 2 =afiliasi, 0 external
$arrOptIntex=array(
	3=>"External",
	0=>"Internal",
	1=>"Afiliasi"
);
OPEN_BOX('','<span class=judul>'.getMenu('kebun_3AmbilKgTimbangan').'</span>');

$arr="##idKbn##tglData##tglData2##intex";
?>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="entryForm">
<fieldset style='float:left;'>
<legend><?php echo $_SESSION['lang']['form']?></legend>
<table cellspacing="1" border="0">
<td><?php echo $_SESSION['lang']['kebun']?>/<?php echo $_SESSION['lang']['divisi']?></td>
<td>:</td>
<td><select id="idKbn" name="idKbn" style="width:172px;" ><?php echo $optKbn ?></select></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['tglNospb']?></td>
<td>:</td>
<td><input type="text" class="myinputtext" readonly='readonly' onchange=gettgl(); id="tglData" name="tglData" onmousemove="setCalendar(this.id);" onkeypress="return false;"  size="10" maxlength="10" style="width:80px;" />
<input type="text" class="myinputtext" readonly='readonly' id="tglData2" name="tglData2" onmousemove="setCalendar(this.id);" onkeypress="return false;"  size="10" maxlength="10" style="width:80px;" />
</td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['tujuantbs']?></td>
<td>:</td>
<td><?php echo makeElement('intex','select',"",array('style'=>'width:172px'),$arrOptIntex)?></td>
</tr>
<tr>
<td><td><td id="tmblHeader">
<button class=mybutton id='dtl_pem' onclick='saveData()'><?php echo $_SESSION['lang']['preview']?></button>&nbsp;
<button class=mybutton id='dtl_pem2' onclick='saveData("excel")'><?php echo $_SESSION['lang']['excel']?></button>&nbsp;
<!-- <button class=mybutton id='preview' onclick='zExcel(event,"kebun_slave_3AmbilKgTimbangan_excel.php","<?php echo $arr?>")'><?php echo $_SESSION['lang']['excel']?></button>&nbsp; -->
<button class=mybutton id='cancel_gti' onclick='cancelSave()'>Reset</button>
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