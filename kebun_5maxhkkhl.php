<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script type="text/javascript" src="js/kebun_5maxhkkhl.js?v=<?php echo time(); ?>"></script>
<?php
//Get Data Kebun
$optKebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sKebun="select * from ".$dbname.".organisasi where length(kodeorganisasi) = '4' order by induk";
$res = fetchdata($sKebun);
foreach($res as $rBlok){
	$d=getNamaOrg($rBlok['kodeorganisasi'],'induk');
	if($d!=$n){			
		$optKebun.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optKebun.="<option value='".$rBlok['kodeorganisasi']."'>".$rBlok['kodeorganisasi']." - ".$rBlok['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optKebun.="</optgroup>";
	}
}

$optkecuali="<option value=''>&nbsp;</option>";
$str="select * from ".$dbname.".sdm_5jabatan where aktif='1' order by namajabatan";
$res = fetchdata($str);
foreach($res as $rBlok){
	$optkecuali.="<option value='".$rBlok['kodejabatan']."'>".$rBlok['namajabatan']."</option>";
}

$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
foreach($arrsts as $key => $val){
	$optsts.="<option value='".$key."'>".$val."</option>";
}

$arrjns=array('hk'=>'HK','hadir'=>'Kehadiran');
foreach($arrjns as $key => $val){
	$optjenis.="<option value='".$key."'>".$val."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('kebun_5maxhkkhl').'</span><br>');
?>
<fieldset style='float:left;'>
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kodeorg']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='kd_org' name='kd_org'>
					<?php echo $optKebun ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['jenis']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='jenis' name='jenis'>
					<?php echo $optjenis ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo "Kecuali ?"?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='kecuali' name='kecuali' multiple class=select2>
					<?php echo $optkecuali ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo 'Max Sebulan' ?></td>
			<td>:</td>
			<td><input style="width:80px" type="number" min='0' id="nilai" class="myinputtextnumber" onKeyPress="return angka_doang(event);" value="0" size="10" /></td>
		</tr>
		<tr>
			
			<td><?php echo $_SESSION['lang']['tanggalberlaku']?></td>
			<td>:</td>
			<td><input type=text class=myinputtext id=tanggalberlaku onmousemove=setCalendar(this.id) onkeypress=return false; style=width:195px maxlength=10></td>
		</tr>
		<tr>
		
			<td style="vertical-align:top;"><?php echo $_SESSION['lang']['status']?></td>
			<td style="vertical-align:top;">:</td>
			<td><select style="width:200px" id='status' name='status'>
					<?php echo $optsts ?>
				</select></td>
		</tr>
		<tr>
			<td><td><td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=btldendapanen()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<div style='height:60vh;overflow:auto;'>
	<table class="sortable" cellspacing="1" cellpadding="7" style='width:100%;' border="0">
		<thead>
			<tr class=rowheader>
				<th style="text-align:center;"><?php echo $_SESSION['lang']['nourut']?></th>
				<th style="text-align:center;"><?php echo $_SESSION['lang']['kebun']?></th>
				<th style="text-align:center;"><?php echo $_SESSION['lang']['jenis'];?></th> 
				<th style="text-align:center;"><?php echo "Kecuali"?></th> 
				<th style="text-align:center;"><?php echo $_SESSION['lang']['nilai'];?></th> 
				<th style="text-align:center;"><?php echo $_SESSION['lang']['tanggalberlaku'];?></th> 
				<th style="text-align:center;"><?php echo $_SESSION['lang']['status'];?></th>
				<th style="text-align:center;"><?php echo $_SESSION['lang']['updateby'];?></th>
				<th style="text-align:center;"><?php echo $_SESSION['lang']['tanggal'];?></th>
				<th colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></th>
			</tr>
		</thead>
		<tbody id="container">
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</div>
<?
CLOSE_BOX();
echo close_body();
?>