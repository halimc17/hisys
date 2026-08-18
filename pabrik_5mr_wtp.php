<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_5mr_wtp.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5mr_wtp').'</span>');

$optagama='';
$arragama=getEnum($dbname,'pabrik_5mr_material_usage','kd_transaksi');
foreach($arragama as $kei=>$fal){
    $optagama.="<option value='".$kei."'>".$fal."</option>";
} 
$optBrg='';
$sBrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang like '31202%' order by namabarang";
$rBrg=fetchData($sBrg);
foreach($rBrg as $row=>$lstBrg){
	$optBrg.="<option value='".$lstBrg['kodebarang']."'>".$lstBrg['namabarang']."</option>";	
}
$optStation='';
$sStation="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='STATION'";
$rStation=fetchData($sStation);
foreach($rStation as $row=>$lstBrg){
	$optStation.="<option value='".$lstBrg['kodeorganisasi']."'>".$lstBrg['namaorganisasi']."</option>";	
}
?>
<br><fieldset style="float:left;">
	<legend><?echo $_SESSION['lang']['form'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['kode'];?>
    </td><td> : </td>
    <td><select id="kdTrans" style='width:150px'><?php echo $optagama; ?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['station'];?>
    </td><td> : </td>
    <td><select id="stationId" style='width:170px'><?php echo $optStation; ?></select></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['namabarang'];?></td><td> : </td>
    <td><select id="kdBrg" style='width:170px'><?php echo $optBrg; ?></select></td>
  </tr>

  <input type=hidden id=method value='insert'>
  <input type=hidden id=primId value=''>
  <tr>
  	<td><td>
  	<td><button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save'] ?></button>
	<button class=mybutton onclick=cancel()><?php echo $_SESSION['lang']['cancel'] ?></button></td>
  </tr>
	 
</table>
<?php
CLOSE_BOX();
OPEN_BOX();

echo "
<fieldset style='width:98%'>
	<legend><b>".$_SESSION['lang']['list']."</legend>
	<table class=sortable cellspacing=1 cellspacing=1 border=0 style='width:68%''>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['station']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		<tfoot id='footData'>
		</tfoot>
		</tbody>
	</table>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>