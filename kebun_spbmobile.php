<?php
	require_once('master_validation.php');
    require_once('config/connection.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php'); 
 echo open_body();
 include('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>

<link rel="stylesheet" type="text/css" href="style/bagan.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script type="application/javascript" src="js/kebun_spbmobile.js?v=<?php echo time();?>"></script>

<?php

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if(@$d!=@$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		$optorgsch.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$optorgsch.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
		$optorgsch.="</optgroup>";
	}
}

$optDiv="";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optDiv.="<option value=''>".$_SESSION['lang']['all']."</option>";
while ($bar = $res->fetch()) {
	$i="";
	if($bar['kodeorganisasi']==$_SESSION['empl']['subbagian']){
		$i="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$i.">".$bar['namaorganisasi']."</option>";
}




//echo "2<br>";
//1 = internal, 2 =afiliasi, 0 external
// $arrOptIntex = array(
// 	0 => "Internal",
// 	1 => "Afiliasi",
// 	2 => "External"
// );

OPEN_BOX('',''); 
?>

<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="entryForm">
	<fieldset style='width:450px;'>
		<legend><?php echo 'SPB Mobile'?></legend>
		<table cellspacing="1" border="0">
			<tr>
				<td><?php echo $_SESSION['lang']['unit']?></td>
				<td>:</td>
				<td><select id="idKbn" name="idKbn" style="width:200px;" onchange="gantidivisi()"><?php echo $optorgsch ?></select></td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['divisi']?></td>
				<td>:</td>
				<td><select id="divisi" name="divisi" style="width:200px;"></select></td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['tanggal']?></td>
				<td>:</td>
				<td><input type="text" class="myinputtext" id="tglData" autocomplete='off' name="tglData" onmousemove="setCalendar(this.id,'%d-%m-%Y');" onkeypress="return false;"  size="10" maxlength="10" style="width:100px;" /></td>
			</tr>
			<!-- <tr>
				<td><?php echo $_SESSION['lang']['tujuantbs']?></td>
				<td>:</td>
				<td><?php echo makeElement('intex','select',"",array('style'=>'width:105px'),$arrOptIntex)?></td>
			</tr> -->
			<tr>
				<td>
					<td>
						<td id="tmblHeader">
							<button class=mybutton id='dtl_pem' onclick='saveData()'><?php echo $_SESSION['lang']['view']?></button><button class=mybutton id='cancel_gti' onclick='cancelSave()'>Reset</button>
						</td>
					</td>
				</td>
			</tr>
		</table>
	</fieldset>
</div>

<?php CLOSE_BOX(); ?>

<div id="result" style="display:none;">
	<?php OPEN_BOX(); ?>
		<div id="list_ganti"></div>
	<?php CLOSE_BOX();?>
</div>
<script>
<?php
    echo close_body();
?>
