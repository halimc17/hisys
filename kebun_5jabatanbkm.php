<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
?>
<script type="text/javascript" src="js/kebun_5jabatanbkm.js?v=<?php echo time(); ?>"></script>
<?php
//Get Data Kebun
$optKebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$divisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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


$opttipecr="<option value=''>".$_SESSION['lang']['all']."</option>";
$optkolomcr="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrtipe=array('BKM'=>'BKM Rawat','PNN'=>'BKM Panen','RKH'=>'Rencana Kerja Harian','SPL'=>'BKM Sipil');
foreach($arrtipe as $val => $key){
	$opttipe.="<option value='".$val."'>".$key."</option>";
	$opttipecr.="<option value='".$val."'>".$key."</option>";
}

$arrtipe=array('mandor'=>'Mandor','mandor1'=>'Mandor 1','kerani'=>'Kerani','asst'=>'Assisten');
foreach($arrtipe as $val => $key){
	$optkolom.="<option value='".$val."'>".$key."</option>";
	$optkolomcr.="<option value='".$val."'>".$key."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5jabatanbkm').'</span><br>');
?>
<fieldset style='float:left;'>
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
			<td><?php echo $_SESSION['lang']['tipetransaksi']?></td>
			<td>:</td>
			<td>
				<select style="width:200px" id='tipetrans' name='tipetrans'>
					<?php echo $opttipe ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Kolom</td>
			<td>:</td>
			<td>
				<select style="width:200px" id='kolom' name='kolom'>
					<?php echo $optkolom ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top;"><?php echo $_SESSION['lang']['jabatan']?></td>
			<td style="vertical-align:top;">:</td>
			<td><input class=myinputtext style="width:195px" id='jabatan' name='jabatan' readonly onclick=getjabatan()></td>
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