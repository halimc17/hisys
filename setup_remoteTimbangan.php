<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('IP and Mail Gateway').'</span>');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src="js/zTools.js"></script>
<script language="javascript" src="js/setup_remoteTimbangan.js"></script>

<?php
$optPeriode='';
for($x=0;$x<=24;$x++)
{
	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$optPeriode.="<option value=".date("Y-m",$dt).">".date("Y-m",$dt)."</option>";
}
	$lokasi=$_SESSION['empl']['lokasitugas'];
	$sql=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and kodeorganisasi='".$lokasi."'");
	$sql->setFetchMode(PDO::FETCH_ASSOC);
	$optOrg='';
	while($res=$sql->fetch())
	{
		$optOrg.="<option value=".$res['kodeorganisasi'].">".$res['namaorganisasi']."</option>"; 
	}
	$arrPrm="##loksi##ipAdd##idRemote##userName##passwrd##dbnm##port";
?>
<div id="headher">

<fieldset style='float:left;'>
<legend><?php echo $_SESSION['lang']['entryForm']?></legend>
<table cellspacing="1" border="0">
<tr>
<td><?php echo $_SESSION['lang']['lokasi']?></td>
<td>:</td>
<td>
<input type="hidden" name="idRemote" id="idRemote" />
<input type="text" style="width:170px" id="loksi" name="loksi" class="myinputtext" />
<!--<select id="loksi" name="loksi" style="width:170px;" onchange="getAfdeling(0,0)" ><option value=""></option><?php echo $optOrg;?></select>-->
</td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['ip']?></td>
<td>:</td>
<td>
<input type="text" style="width:170px" id="ipAdd" name="ipAdd" class="myinputtext" />
<!--<select id="kodeAfdeling" name="kodeAfdeling" style="width:170px;" onchange="getBlok(0,0)" ><option value=""></option></select>-->
</td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['username']?></td>
<td>:</td>
<td>
<input type="text" style="width:170px" id="userName" name="userName" class="myinputtext" />
</td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['password']?></td>
<td>:</td>
<td>
<input type="password" style="width:170px" id="passwrd" name="passwrd" class="myinputtext" onKeyPress="return tanpa_kutip(event)" /></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['dbname']?></td>
<td>:</td>
<td>
<input type="text" style="width:170px" id="dbnm" name="dbnm" class="myinputtext" onKeyPress="return tanpa_kutip(event)" /></td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['port']?></td>
<td>:</td>
<td>
<input type="text" style="width:70px" id="port" name="port" class="myinputtextnumber" maxlength="5" onKeyPress="return angka_doang(event)" /></td>
</tr>
<tr>
<td><td><td id="tmbLheader">
<button class="mybutton" id="dtlAbn" onclick="saveData('<?php echo $arrPrm ?>')"><?php echo $_SESSION['lang']['save']?></button><button class="mybutton" id="cancelAbn" onclick="cancelSave()"><?php echo $_SESSION['lang']['cancel']?></button>
</td></td></td>
</tr>
</table><input type="hidden" id="proses" name="proses" value="insert"  />
</fieldset>

</div>
<?php
CLOSE_BOX();
?>
<div id="listData">
<?php OPEN_BOX() ?>
<fieldset style='float:left'>
<legend><?php echo $_SESSION['lang']['list']?></legend>

<table class=sortable cellspacing="1" border="0">
<thead>
<tr class="rowheader">
<td align='center'>No.</td>
<td align='center'><?php echo $_SESSION['lang']['lokasi']?></td>
<td align='center'><?php echo $_SESSION['lang']['ip'];?></td> 
<td align='center'><?php echo $_SESSION['lang']['username'];?></td>
<td align='center'><?php echo $_SESSION['lang']['password'];?></td>	
<td align='center'><?php echo $_SESSION['lang']['port'];?></td>	 
<td align='center'><?php echo $_SESSION['lang']['dbname'];?></td>	 
<td align='center'>Action</td>
</tr>
</thead>
<tbody id="contain">
<script>loadData()</script>

</tbody>
</table>
</fieldset>

<?php CLOSE_BOX()?>
</div>

<?php 
echo close_body();

?>