<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
?>
<script type="text/javascript" src="js/sdm_5pengalibpjs.js?v=<?php echo time(); ?>"></script>
<?php
//Get Data Kebun
$optKebun=$optkartipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$divisi=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sKebun="select * from ".$dbname.".organisasi where tipe in ('KEBUN','AFDELING') and kodeorganisasi in (".getOrgDetail(2).")  order by induk, kodeorganisasi";
$res = fetchdata($sKebun);
foreach($res as $bar){
	$d=$bar['induk'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		if($bar['tipe']=='AFDELING'){			
			$divisi.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
		}
		if($bar['tipe']=='KEBUN'){			
			$optKebun.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
		}
	}
	if($bar['tipe']=='KEBUN'){		
		$optKebun.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	}
	if($bar['tipe']=='AFDELING'){		
		$divisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	}

	$n=$d;
	if($d!=$n){
		if($bar['tipe']=='AFDELING'){			
			$divisi.="</optgroup>";
		}
		if($bar['tipe']=='KEBUN'){			
			$divisi.="</optgroup>";
		}
	}
}
$str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optPT.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}
$str = "select * from ".$dbname.".sdm_ho_component where id not in (70,71,72,73,80) and plus=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optjenis.="<option value='".$bar['id']."'>".$bar['name']."</option>";
}

$str = "select * from ".$dbname.".sdm_ho_component where id not in (3,61,67,44,81) and plus=0";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optjenis.="<option value='".$bar['id']."'>".$bar['name']."</option>";
}

$str = "select * from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by id";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optkartipe.="<option value='".$bar['id']."'>".$bar['tipe']."</option>";
}


$opttipecr="<option value=''>".$_SESSION['lang']['all']."</option>";
$optkolomcr="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrtipe=array('BKM'=>'BKM Rawat','PNN'=>'BKM Panen','RKH'=>'Rencana Kerja Harian');
foreach($arrtipe as $val => $key){
	$opttipe.="<option value='".$val."'>".$key."</option>";
	$opttipecr.="<option value='".$val."'>".$key."</option>";
}

$arrstatus=array('1'=>'Aktif','0'=>'Tidak Aktif');
foreach($arrstatus as $val => $key){
	$optstatus.="<option value='".$val."'>".$key."</option>"; 
}

$arrtipe=array('mandor'=>'Mandor','mandor1'=>'Mandor 1','kerani'=>'Kerani','asst'=>'Assisten');
foreach($arrtipe as $val => $key){
	$optkolom.="<option value='".$val."'>".$key."</option>";
	$optkolomcr.="<option value='".$val."'>".$key."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5pengalibpjs').'</span><br>');
?>
<fieldset style='float:left;'>
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kodept']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='kd_org' name='kd_org'>
					<?php echo $optPT ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['tipekaryawan']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='tipekaryawan' name='tipekaryawan'>
					<?php echo $optkartipe ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Jenis BPJS</td>
			<td>:</td>
			<td>
				<select style="width:200px" id='jenisbpjs' name='jenisbpjs'>
					<?php echo $optjenis ?>
				</select>
			</td>
		</tr>

		<tr>
			<td style="vertical-align:top;">Komponen Gaji</td>
			<td style="vertical-align:top;">:</td>
			<td><input class=myinputtext style="width:195px" id='komponengaji' name='komponengaji' readonly onclick=getkomponen()></td>
		</tr>
        <tr>
			<td>Status</td>
			<td>:</td>
			<td>
				<select style="width:200px" id='idsts' name='idsts'>
					<?php echo $optstatus ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><td><td>
			<input type="hidden" id="id"  />
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

<?
echo"<div id='output' style=min-height:400px><script>loadData()</script></div>";
CLOSE_BOX();
echo close_body();
?>