<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script type="text/javascript" src="js/kebun_5dendapanen.js"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php
	//Get Data Kebun
	$optKebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	// $optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	// 	$sKebun="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' ";
	// }else{
	// 	$sKebun="select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
	// }
	// $qBlok=$owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
	// $qBlok->setFetchMode(PDO::FETCH_ASSOC);
	// while($rBlok=$qBlok->fetch())
	// {
	// 	$optKebun.="<option value='".$rBlok['kodeorganisasi']."'>".$optNmOrg[$rBlok['kodeorganisasi']]."</option>";
	// }

	## GET UNIT
	$optKebun='';
	$unit='';
	$arrUnit = getOrgDetail(1);
	foreach($arrUnit as $key=>$val){
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
		$d=$induk[$key];
		if($d!=$n){			
			$optKebun.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}
		
		if($key==$_SESSION['empl']['lokasitugas']){
			$optKebun.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
			$unit=$key;
		}else{
			$optKebun.="<option value='".$key."'>".$key." - ".$val."</option>";			
		}
		$n=$d;
		if($d!=$n){			
			$optKebun.="</optgroup>";
		}
	}
	
	//Get Data Jenis Denda
	$arrayJenDenda=getEnum($dbname,'kebun_5dendapanen','jenisdenda');
	$optJenDenda="";
	foreach($arrayJenDenda as $value) {
		$optJenDenda.="<option value=".$value.">".$value."</option>";
	}
	
	//Get Data Kode Denda
	$optKdDenda="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sKdDenda="select * from ".$dbname.".kebun_5kodedendapanen";
	$qKdDenda=$owlPDO->query($sKdDenda) or die(print " Gagal: ".PDOException::getMessage());
	$qKdDenda->setFetchMode(PDO::FETCH_ASSOC);
	while($rKdDenda=$qKdDenda->fetch()){
		$optKdDenda.="<option value='".$rKdDenda['kodedenda']."'>".$rKdDenda['kodedenda']." - ".$rKdDenda['deskripsi']."</option>";
	}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['dendapanen']).'</span>');
?>
<fieldset style='width:450px;'>
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kebun']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='kd_org' name='kd_org'>
					<?php echo $optKebun ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['kodedenda']?></td>
			<td>:</td>
			<td>
				<!-- <input type="text" id="kd_denda" size="5" class="myinputtext" maxlength="4" /> -->
				<select style="width:200px" id='kd_denda' name='kd_denda'>
					<?php echo $optKdDenda ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['jenisdenda'] ?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='jenisdenda' name='jenisdenda'>
					<?php echo $optJenDenda ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['nilaidenda'] ?></td>
			<td>:</td>
			<td><input style="width:80px" type="text" id="nilaidenda" class="myinputtextnumber" onKeyPress="return angka_doang(event);" onblur="display_number(event);" value="0" size="10" /></td>
		</tr>
		<tr>
			<td style="vertical-align:top;"><?php echo $_SESSION['lang']['keterangan']?></td>
			<td style="vertical-align:top;">:</td>
			<td><input style="width:195px" class="myinputtext" id='ketdenda' name='ketdenda' onkeypress='return tanpa_kutip(event);' maxlength=40></td>
		</tr>
		<tr>
			<td><td><td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpadendapanen()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=btldendapanen()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset>
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['dendapanen'] ?></legend>
	<div style='height:350px;overflow:auto;'>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo $_SESSION['lang']['kebun']?></td>
				<td><?php echo $_SESSION['lang']['kodedenda'];?></td> 
				<td><?php echo $_SESSION['lang']['jenisdenda'];?></td>
				<td style="text-align:center;"><?php echo $_SESSION['lang']['nilaidenda']; ?></td>
				<td><?php echo $_SESSION['lang']['keterangan']; ?></td>
				<td colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table></div>
</fieldset>
<?
CLOSE_BOX();
echo close_body();
?>