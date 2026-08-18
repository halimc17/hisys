<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_5mr_parameter_nilai.js'></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5mr_parameter_nilai').'</span>');
 
$optkdnilai="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$skdnilai="select * from ".$dbname.".pabrik_5mr_bfwt";
$rkdnilai=fetchData($skdnilai);
foreach($rkdnilai as $row=>$lstkdnilai){
	$optkdnilai.="<option value='".$lstkdnilai['kd_transaksi']."".$lstkdnilai['kode']."'>".$lstkdnilai['kd_transaksi']." - ".$lstkdnilai['nama']."</option>";	
}

$skdnilai="select * from ".$dbname.".pabrik_5mr_roa_parameter";
$rkdnilai=fetchData($skdnilai);
foreach($rkdnilai as $row=>$lstkdnilai){
	$optkdnilai.="<option value='".$lstkdnilai['parameter']."'>".$lstkdnilai['parameter']." - ".$lstkdnilai['nama']."</option>";	
}

$optnamaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optStation='';
$sStation="select kode_station from ".$dbname.".pabrik_5mr_list_station where left(kode_station,4)='".$_SESSION['empl']['lokasitugas']."'";
$rStation=fetchData($sStation);
foreach($rStation as $row=>$lstBrg){
	$optStation.="<option value='".$lstBrg['kode_station']."'>".$optnamaorg[$lstBrg['kode_station']]."</option>";	
}
?>
<br><fieldset style="float:left;">
	<legend><?echo $_SESSION['lang']['form'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['station'];?>
    </td><td> : </td>
    <td><select id="stationId" style='width:170px'><?php echo $optStation; ?></select></td>
  </tr>
  <tr>
    <td><?echo "Nilai dari Loses"?></td><td> : </td>
    <td><select id="kdnilaidr" style='width:170px' onchange="getData()"><?php echo $optkdnilai; ?></select></td>
  </tr>

  <tr>
    <td><?echo $_SESSION['lang']['kode']." ".$_SESSION['lang']['nilai'];?>
    </td><td> : </td>
    <td><input type=text  id="kdnilai" nkeypress="return_tanpa_kutip(event);"   class=myinputtext style="width:165px;"></td>
  </tr>

  <tr>
    <td><?echo $_SESSION['lang']['nama'];?>
    </td><td> : </td>
    <td><input type=text  id="nama" nkeypress="return_tanpa_kutip(event);"   class=myinputtext style="width:165px;"></td>
  </tr>

  <tr>
    <td><?echo $_SESSION['lang']['standar']." ".$_SESSION['lang']['nilai'];?>
    </td><td> : </td>
    <td><input type=text  id="standarnilai" nkeypress="return_tanpa_kutip(event);"   class=myinputtext style="width:165px;"></td>
  </tr>

  <input type=hidden id=method value='insert'>
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
	<table class=sortable cellspacing=1 cellspacing=1 border=0 >
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['station']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
        <td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>Standard Nilai</td>
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