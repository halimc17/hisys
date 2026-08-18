<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$cnotransaksi = checkPostGet('cnotransaksi','');

//tab sensus
$nosensus = checkPostGet('nosensus','');
$nosensus2 = checkPostGet('nosensus2','');
$notransaksi = checkPostGet('notransaksi','');
$blok = checkPostGet('blok','');
$luas = checkPostGet('luas','');
$tanggal = tanggalsystem(checkPostGet('tanggal',''));

//tab penanggulangan
$cnosensus = checkPostGet('cnosensus','');
$cnotransaksi2 = checkPostGet('cnotransaksi2','');
$nopenanggulangan = checkPostGet('nopenanggulangan','');


$method = checkPostGet('method','');
$sus_ht_nosensus = checkPostGet('sus_ht_nosensus','');
$sus_ht_notransaksi = checkPostGet('sus_ht_notransaksi','');
$sus_ht_tanggal = checkPostGet('sus_ht_tanggal','');
$sus_ht_blok = checkPostGet('sus_ht_blok','');

$sus_dt_jenishama = checkPostGet('sus_dt_jenishama','');
$sus_dt_jumlah = checkPostGet('sus_dt_jumlah','');


$png_ht_nopenanggulangan = checkPostGet('png_ht_nopenanggulangan','');
$png_ht_nosensus = checkPostGet('png_ht_nosensus','');
$png_ht_notransaksi = checkPostGet('png_ht_notransaksi','');
$png_ht_tanggal = checkPostGet('png_ht_tanggal','');
$png_ht_blok = checkPostGet('png_ht_blok','');

$png_dt_jenishama = checkPostGet('png_dt_jenishama','');
$png_dt_jumlah = checkPostGet('png_dt_jumlah','');




switch($method) {
	####### BEGIN LOAD ALL TAB #######
	case 'loadAllTabData':
		echo loadDataTab1()."####".loadDataTab2();
		break;
	####### END LOAD ALL TAB #######
	
	####### BEGIN LOAD TAB SENSUS #######
	case 'loadData':
		echo loadDataTab1();
		break;
		
	case 'sus_dt_loaddata':
		$tab = "";
		$tab .= "<table class=sortable cellspacing=1 cellpadding=3 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<td colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['kode']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['jenishama']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['satuan']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['jumlah']."</td>
			</tr>
			</thead>
			<tbody>";
			
		$str = "select * from ".$dbname.".kebun_hpt_sensus_dt where nosensus = '".$sus_ht_nosensus."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		if($sus_ht_nosensus == '' || $numrows <= 0){
			$tab .= "<tr><td colspan=7 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td></tr>";
		}else{
			$no = 0;
			while($bar = $res->fetch()){
				$optNmHama = makeOption($dbname,'kebun_5jenishama','kodehama,namahama',"kodehama='".$bar['kodehama']."'");
				$optSatuanHama = makeOption($dbname,'kebun_5jenishama','kodehama,satuan',"kodehama='".$bar['kodehama']."'");
				$no++;
				$tab .= "<tr class=rowcontent>
					<td style='text-align:center'>
						<img src=images/application/application_edit.png class=resicon  title='Edit ".$bar['kodehama']."' onclick=\"sus_dt_fillfield('".$bar['kodehama']."','".$bar['jumlah']."');\" >
					</td>
					<td style='text-align:center'>
						<img src=images/application/application_delete.png class=resicon  title='Delete ".$bar['kodehama']."' onclick=\"sus_dt_delete('".$bar['kodehama']."');\" >
					</td>
					<td style='text-align:center'>".$no."</td>
					<td>".$bar['kodehama']."</td>
					<td>".$optNmHama[$bar['kodehama']]."</td>
					<td>".$optSatuanHama[$bar['kodehama']]."</td>
					<td style='text-align:right'>".number_format($bar['jumlah'])."</td>
				</tr>";
			}
		}
			
		$tab .= "</tbody>
		</table>";
		echo $tab;
		
		break;
		
	case 'sus_ht_insert':
		if($sus_ht_notransaksi == ''){
			exit("warning : ".$_SESSION['lang']['notransaksi']." required");
		}
		
		$vNoSensus = setNoSensus($sus_ht_tanggal);
		$str = "insert into ".$dbname.".kebun_hpt_sensus_ht (nosensus,notransaksi,blok) values 
		('".$vNoSensus."','".$sus_ht_notransaksi."','".$sus_ht_blok."')";
		try{
			$owlPDO->exec($str); 
			echo $vNoSensus;
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		
		break;
		
	case 'sus_dt_insert':
		if($sus_dt_jenishama == ''){
			exit("warning : ".$_SESSION['lang']['jenishama']." required");
		}
		if($sus_dt_jumlah == '' || $sus_dt_jumlah == 0){
			exit("warning : ".$_SESSION['lang']['jumlah']." required");
		}
		
		$str = "select * from ".$dbname.".kebun_hpt_sensus_dt where kodehama = '".$sus_dt_jenishama."' and nosensus = '".$sus_ht_nosensus."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		if($numrows > 0){
			exit("warning : Duplicate Entry");
		}
		
		$str = "insert into ".$dbname.".kebun_hpt_sensus_dt (id,nosensus,kodehama,jumlah) values 
		('','".$sus_ht_nosensus."','".$sus_dt_jenishama."','".$sus_dt_jumlah."')";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'sus_dt_update':
		if($sus_dt_jumlah == '' || $sus_dt_jumlah == 0){
			exit("warning : ".$_SESSION['lang']['jumlah']." required");
		}
		
		$str = "update ".$dbname.".kebun_hpt_sensus_dt set jumlah  = '".$sus_dt_jumlah."' where nosensus = '".$sus_ht_nosensus."' and kodehama = '".$sus_dt_jenishama."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'delData':
		$str = "delete from ".$dbname.".kebun_hpt_sensus_ht where nosensus='".$nosensus."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		
		$str = "delete from ".$dbname.".kebun_hpt_penanggulangan_ht where nosensus='".$nosensus."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'sus_dt_delete':
		$str = "delete from ".$dbname.".kebun_hpt_sensus_dt where nosensus = '".$sus_ht_nosensus."' and kodehama = '".$sus_dt_jenishama."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'getFormNoTransaksi':
		$str = "select distinct(t1.notransaksi), t1.kodeorg, t2.luasareaproduktif from ".$dbname.".kebun_prestasi t1 inner join ".$dbname.".setup_blok t2 ON t1.kodeorg = t2.kodeorg where left(t1.kodeorg,4) = '".$_SESSION['empl']['lokasitugas']."' and t1.kodekegiatan in (select kodekegiatan from ".$dbname.".kebun_5hpt where tipe = 's') and notransaksi not in (select distinct(notransaksi) from ".$dbname.".kebun_hpt_sensus_ht where notransaksi like '%".$_SESSION['empl']['lokasitugas']."%') order by t1.notransaksi DESC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
	
		$tab = "<div>
			<table>
				<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input id=cnotransaksi class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\"></td>
					<td>
						<button class=mybutton onclick=csearch()>".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
		</div>
		<div style='overflow:auto; max-height:300px;' id='listnotransaksi'>
			<table class=sortable cellspacing=1 cellpadding=3 border=0 width=100%>
				<thead>
				<tr class=rowheader>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>".$_SESSION['lang']['blok']."</td>
					<td>".$_SESSION['lang']['luas']." (Ha)</td>
				</tr>
				</thead>
				<tbody>";
			if($numrows<=0){
				$tab .= "<tr>
					<td colspan=4 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td>
				</tr>";
			}else{
				$no=0;
				while($bar = $res->fetch()){
					$no++;
					$tab .= "<tr class=rowcontent style=cursor:pointer onclick=\"fillfield('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['luasareaproduktif']."')\">
					<td style='text-align:right;'>".$no."</td>
					<td>".$bar['notransaksi']."</td>
					<td>".$bar['kodeorg']."</td>
					<td style='text-align:right;'>".$bar['luasareaproduktif']."</td>
				</tr>";
				}
			}
			$tab .= "</tbody>
			</table>
		</div>";
		
		echo $tab;
		break;
		
	case 'csearch':
		$str = "select distinct(t1.notransaksi), t1.kodeorg, t2.luasareaproduktif from ".$dbname.".kebun_prestasi t1 
		left join ".$dbname.".setup_blok t2 ON t1.kodeorg = t2.kodeorg
		where left(t1.kodeorg,4) = '".$_SESSION['empl']['lokasitugas']."' and t1.kodekegiatan in (select kodekegiatan from ".$dbname.".kebun_5hpt where tipe = 's') and t1.notransaksi like '%".$cnotransaksi."%' and notransaksi not in (select distinct(notransaksi) from ".$dbname.".kebun_hpt_sensus_ht where notransaksi like '%".$_SESSION['empl']['lokasitugas']."%') order by t1.notransaksi DESC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		$tab = "<table class=sortable cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>".$_SESSION['lang']['blok']."</td>
					<td>".$_SESSION['lang']['luas']." (Ha)</td>
				</tr>
				</thead>
				<tbody>";
			if($numrows<=0){
				$tab .= "<tr>
					<td colspan=4 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td>
				</tr>";
			}else{
				$no=0;
				while($bar = $res->fetch()){
					$no++;
					$tab .= "<tr class=rowcontent onclick=\"fillfield('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['luasareaproduktif']."')\">
					<td style='text-align:right;'>".$no."</td>
					<td>".$bar['notransaksi']."</td>
					<td>".$bar['kodeorg']."</td>
					<td style='text-align:right;'>".$bar['luasareaproduktif']."</td>
				</tr>";
				}
			}
			$tab .= "</tbody>
			</table>";
		
		echo $tab;
		break;
	####### END LOAD TAB SENSUS #######
	
	
	
	
	####### BEGIN LOAD TAB PENANGGULANGAN #######
	case 'loadData2':
		echo loadDataTab2();
		break;
		
	case 'png_dt_loaddata':
		$tab = "";
		$tab .= "<table class=sortable cellspacing=1 cellpadding=3 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<td colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['kode']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['jenishama']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['satuan']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['jumlah']."</td>
			</tr>
			</thead>
			<tbody>";
			
		$str = "select * from ".$dbname.".kebun_hpt_penanggulangan_dt where nopenanggulangan = '".$png_ht_nopenanggulangan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		if($png_ht_nopenanggulangan == '' || $numrows <= 0){
			$tab .= "<tr><td colspan=7 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td></tr>";
		}else{
			$no = 0;
			while($bar = $res->fetch()){
				$optNmHama = makeOption($dbname,'kebun_5jenishama','kodehama,namahama',"kodehama='".$bar['kodehama']."'");
				$optSatuanHama = makeOption($dbname,'kebun_5jenishama','kodehama,satuan',"kodehama='".$bar['kodehama']."'");
				$no++;
				$tab .= "<tr class=rowcontent>
					<td style='text-align:center'>
						<img src=images/application/application_edit.png class=resicon  title='Edit ".$bar['kodehama']."' onclick=\"png_dt_fillfield('".$bar['kodehama']."','".$bar['jumlah']."');\" >
					</td>
					<td style='text-align:center'>
						<img src=images/application/application_delete.png class=resicon  title='Delete ".$bar['kodehama']."' onclick=\"png_dt_delete('".$bar['kodehama']."');\" >
					</td>
					<td style='text-align:right'>".$no."</td>
					<td>".$bar['kodehama']."</td>
					<td>".$optNmHama[$bar['kodehama']]."</td>
					<td>".$optSatuanHama[$bar['kodehama']]."</td>
					<td style='text-align:right'>".number_format($bar['jumlah'])."</td>
				</tr>";
			}
		}
			
		$tab .= "</tbody>
		</table>";
		echo $tab;
		break;
		
	case 'png_ht_insert':
		if($png_ht_notransaksi == ''){
			exit("warning : ".$_SESSION['lang']['notransaksi']." required");
		}		
		
		if($png_ht_nosensus != ""){
			$getBlokSensus = makeOption($dbname,'kebun_hpt_sensus_ht','nosensus,blok',"nosensus='".$png_ht_nosensus."'");
			$getBlokPenanggulangan = makeOption($dbname,'kebun_prestasi','notransaksi,kodeorg',"notransaksi='".$png_ht_notransaksi."'");
			
			if($getBlokSensus[$png_ht_nosensus] != $getBlokPenanggulangan[$png_ht_notransaksi]){
				exit("warning : Kode blok sensus dan penanggulangan berbeda");
			}
		}	

		$vNoPenanggulangan = setNoPenanggulangan($png_ht_tanggal);
		
		$str = "insert into ".$dbname.".kebun_hpt_penanggulangan_ht  (nopenanggulangan,nosensus,notransaksi,kodeorg) values 
		('".$vNoPenanggulangan."','".$png_ht_nosensus."','".$png_ht_notransaksi."','".$png_ht_blok."')";
		try{
			$owlPDO->exec($str); 
			echo $vNoPenanggulangan;
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		
		break;
		
	case 'png_dt_insert':
		if($png_dt_jenishama == ''){
			exit("warning : ".$_SESSION['lang']['jenishama']." required");
		}
		if($png_dt_jumlah == '' || $png_dt_jumlah == 0){
			exit("warning : ".$_SESSION['lang']['jumlah']." required");
		}
		
		if($png_ht_nosensus != ''){
			$str = "select * from ".$dbname.".kebun_hpt_sensus_dt where nosensus = '".$png_ht_nosensus."' and kodehama = '".$png_dt_jenishama."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$numVal=owlBaris($res);
			
			if($numVal <= 0){
				exit("warning : Jenis Hama tidak terdaftar di list sensus.");
			}
		}
		
		$str = "select * from ".$dbname.".kebun_hpt_penanggulangan_dt where kodehama = '".$png_dt_jenishama."' and nopenanggulangan = '".$png_ht_nopenanggulangan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		if($numrows > 0){
			exit("warning : Duplicate Entry");
		}
		
		$str = "insert into ".$dbname.".kebun_hpt_penanggulangan_dt (id,nopenanggulangan,kodehama,jumlah) values 
		('','".$png_ht_nopenanggulangan."','".$png_dt_jenishama."','".$png_dt_jumlah."')";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'png_dt_update':
		if($png_dt_jumlah == '' || $png_dt_jumlah == 0){
			exit("warning : ".$_SESSION['lang']['jumlah']." required");
		}
		
		$str = "update ".$dbname.".kebun_hpt_penanggulangan_dt set jumlah  = '".$png_dt_jumlah."' where nopenanggulangan = '".$png_ht_nopenanggulangan."' and kodehama = '".$png_dt_jenishama."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'delData2':
		$str = "delete from ".$dbname.".kebun_hpt_penanggulangan_ht where nopenanggulangan='".$nopenanggulangan."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'png_dt_delete':
		$str = "delete from ".$dbname.".kebun_hpt_penanggulangan_dt where nopenanggulangan = '".$png_ht_nopenanggulangan."' and kodehama = '".$png_dt_jenishama."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case 'getFormNoSensus':
		$str = "select * from ".$dbname.".kebun_hpt_sensus_ht
		where blok like '%".$_SESSION['empl']['lokasitugas']."%' order by nosensus DESC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
	
		$tab = "<div>
			<table>
				<tr>
					<td>".$_SESSION['lang']['nosensus']."</td>
					<td>:</td>
					<td><input id=cnosensus class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\"></td>
					<td>
						<button class=mybutton onclick=csearchsensus()>".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
		</div>
		<div style='overflow:auto; max-height:300px;' id='listnosensus'>
			<table class=sortable cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['nosensus']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['notransaksi']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['blok']."</td>
				</tr>
				</thead>
				<tbody>";
			if($numrows<=0){
				$tab .= "<tr>
					<td colspan=4 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td>
				</tr>";
			}else{
				$no=0;
				while($bar = $res->fetch()){
					$no++;
					$tab .= "<tr class=rowcontent style=cursor:pointer onclick=\"fillSearchSensus('".$bar['nosensus']."')\">
					<td style='text-align:right;'>".$no."</td>
					<td>".$bar['nosensus']."</td>
					<td>".$bar['notransaksi']."</td>
					<td>".$bar['blok']."</td>
				</tr>";
				}
			}
			$tab .= "</tbody>
			</table>
		</div>";
		
		echo $tab;
		break;
		
	case 'getFormNoTransaksi2':
		$str = "select distinct(t1.notransaksi), t1.kodeorg, t2.luasareaproduktif from ".$dbname.".kebun_prestasi t1 
		inner join ".$dbname.".setup_blok t2 ON t1.kodeorg = t2.kodeorg
		where left(t1.kodeorg,4) = '".$_SESSION['empl']['lokasitugas']."' and t1.kodekegiatan in (select kodekegiatan from ".$dbname.".kebun_5hpt where tipe = 'p') order by t1.notransaksi DESC";
		// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
	
		$tab = "<div>
			<table>
				<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input id=cnotransaksi2 class=myinputtext type=text onkeypress=\"return tanpa_kutip(event)\"></td>
					<td>
						<button class=mybutton onclick=csearch2()>".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
		</div>
		<div style='overflow:auto; max-height:300px;' id='listnotransaksi2'>
			<table class=sortable cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>".$_SESSION['lang']['blok']."</td>
					<td>".$_SESSION['lang']['luas']." (HA)</td>
				</tr>
				</thead>
				<tbody>";
			if($numrows<=0){
				$tab .= "<tr>
					<td colspan=4 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td>
				</tr>";
			}else{
				$no=0;
				while($bar = $res->fetch()){
					$no++;
					$tab .= "<tr class=rowcontent style=cursor:pointer onclick=\"fillfield2('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['luasareaproduktif']."')\">
					<td style='text-align:right;'>".$no."</td>
					<td>".$bar['notransaksi']."</td>
					<td>".$bar['kodeorg']."</td>
					<td style='text-align:right;'>".$bar['luasareaproduktif']."</td>
				</tr>";
				}
			}
			$tab .= "</tbody>
			</table>
		</div>";
		
		echo $tab;
		break;
		
		case 'csearchsensus':
		$str = "select * from ".$dbname.".kebun_hpt_penanggulangan_ht where left(kodeorg,4)='".$_SESSION['empl']['lokasitugas']."' and nosensus like '%".$cnosensus."%' order by nosensus DESC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		$tab = "<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
				<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['nosensus']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['notransaksi']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['blok']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['luas']." (HA)</td>
				<td style='text-align:center'>".$_SESSION['lang']['rayap']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['oryctes']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['ulatkantung']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['ulatapi']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['tikus']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['babi']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['lain']."</td>
			</tr>
			</thead>
			<tbody>";
		if($numrows<=0){
			$tab .= "<tr>
				<td colspan=12 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td>
			</tr>";
		}else{
			$no=0;
			while($bar = $res->fetch()){
				$no++;
				$tab .= "<tr class=rowcontent onclick=\"fillSearchSensus('".$bar['nosensus']."')\">
				<td style='text-align:right;'>".$no."</td>
				<td>".$bar['nosensus']."</td>
				<td>".$bar['notransaksi']."</td>
				<td>".$bar['kodeorg']."</td>
				<td style='text-align:right;'>".$bar['luas']."</td>
				<td style='text-align:right;'>".number_format($bar['rayap'])."</td>
				<td style='text-align:right;'>".number_format($bar['oryctes'])."</td>
				<td style='text-align:right;'>".number_format($bar['ulatkantung'])."</td>
				<td style='text-align:right;'>".number_format($bar['ulatapi'])."</td>
				<td style='text-align:right;'>".number_format($bar['tikus'])."</td>
				<td style='text-align:right;'>".number_format($bar['babi'])."</td>
				<td style='text-align:right;'>".number_format($bar['lain'])."</td>
			</tr>";
			}
		}
		$tab .= "</tbody>
		</table>";
		
		echo $tab;		
		break;
	
	
		
	case 'csearch2':
		$str = "select distinct(t1.notransaksi), t1.kodeorg, t2.luasareaproduktif from ".$dbname.".kebun_prestasi t1 
		inner join ".$dbname.".setup_blok t2 ON t1.kodeorg = t2.kodeorg
		where left(t1.kodeorg,4) = '".$_SESSION['empl']['lokasitugas']."' and t1.kodekegiatan in (select kodekegiatan from ".$dbname.".kebun_5hpt where tipe = 'p') and t1.notransaksi like '%".$cnotransaksi2."%' order by t1.notransaksi DESC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		$tab = "<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>".$_SESSION['lang']['blok']."</td>
				<td>".$_SESSION['lang']['luas']." (Ha)</td>
			</tr>
			</thead>
			<tbody>";
		if($numrows<=0){
			$tab .= "<tr>
				<td colspan=4 style='text-align:center'>".$_SESSION['lang']['dataempty']."</td>
			</tr>";
		}else{
			$no=0;
			while($bar = $res->fetch()){
				$no++;
				$tab .= "<tr class=rowcontent onclick=\"fillfield2('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['luasareaproduktif']."')\">
				<td style='text-align:right;'>".$no."</td>
				<td>".$bar['notransaksi']."</td>
				<td>".$bar['kodeorg']."</td>
				<td style='text-align:right;'>".$bar['luasareaproduktif']."</td>
			</tr>";
			}
		}
		$tab .= "</tbody>
		</table>";
		
		echo $tab;
		break;
	####### END LOAD TAB PENANGGULANGAN #######
	
	case 'getDetailSensus':
		$tab = "";
		
		$str = "select * from ".$dbname.".kebun_hpt_penanggulangan_ht where nosensus = '".$nosensus."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$optLuas = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$bar['kodeorg']."'");
		$nopenanggulangan = $bar['nopenanggulangan'];
		$notransaksi = $bar['notransaksi'];
		$tab = "<div style='overflow:auto'>
		<div style=clear:both;>&nbsp;</div>
		<table>
			<tr class=rowcontent>
				<td>".$_SESSION['lang']['nosensus']."</td>
				<td>:</td>
				<td style='padding-right:10px;'>".$bar['nosensus']."</td>
				
				<td>".$_SESSION['lang']['blok']."</td>
				<td>:</td>
				<td>".$bar['kodeorg']."</td>
			</tr>
			<tr class=rowcontent>
				<td>".$_SESSION['lang']['nopenanggulangan']."</td>
				<td>:</td>
				<td style='padding-right:10px;'>".$bar['nopenanggulangan']."</td>
				
				<td>".$_SESSION['lang']['luas']." (HA)</td>
				<td>:</td>
				<td>".$optLuas[$bar['kodeorg']]."</td>
			</tr>
			<tr class=rowcontent>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td style='padding-right:10px;'>".$bar['notransaksi']."</td>
				
				<td>".$_SESSION['lang']['tglsensus']."</td>
				<td>:</td>
				<td>".tanggalnormal(substr($bar['nosensus'],0,8))."</td>
			</tr>
			<tr class=rowcontent>
				<td colspan=3></td>
				
				<td>".$_SESSION['lang']['tglpenanggulangan']."</td>
				<td>:</td>
				<td>".tanggalnormal(substr($bar['nopenanggulangan'],0,8))."</td>
			</tr>
		</table>
		<div style=clear:both;>&nbsp;</div>
		<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
				<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['kode']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['jenishama']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['satuan']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['sensus']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['penanggulangan']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$arrHama = array();
		$arrValHama = array();
		$str = "select * from ".$dbname.".kebun_hpt_sensus_dt where nosensus = '".$nosensus."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);			
		while($bar = $res->fetch()){
			$arrHama[$bar['kodehama']] = $bar['kodehama'];
			$arrValHama[$bar['kodehama']]['sensus'] = $bar['jumlah'];
		}
		
		$str = "select * from ".$dbname.".kebun_hpt_penanggulangan_dt where nopenanggulangan = '".$nopenanggulangan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$arrHama[$bar['kodehama']] = $bar['kodehama'];
			$arrValHama[$bar['kodehama']]['penanggulangan'] = $bar['jumlah'];
		}
		
		$no = 0;
		foreach($arrHama as $key){
			$optNmHama = makeOption($dbname,'kebun_5jenishama','kodehama,namahama',"kodehama='".$key."'");
			$optSatuanHama = makeOption($dbname,'kebun_5jenishama','kodehama,satuan',"kodehama='".$key."'");
			$no++;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td>".$no."</td>";
			$tab .= "<td>".$key."</td>";
			$tab .= "<td>".$optNmHama[$key]."</td>";
			$tab .= "<td>".$optSatuanHama[$key]."</td>";
			$tab .= "<td style='text-align:right;'>".number_format($arrValHama[$key]['sensus'])."</td>";
			$tab .= "<td style='text-align:right;'>".number_format($arrValHama[$key]['penanggulangan'])."</td>";
			$tab .= "</tr>";
		}
		$tab .= "</tbody>
		</table>";
		
		$str = "select t2.namabarang, t2.satuan, t1.kwantitas from ".$dbname.".kebun_pakaimaterial t1
					left join ".$dbname.".log_5masterbarang t2 ON t1.kodebarang = t2.kodebarang
					where t1.notransaksi = '".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		if($numrows >= 1){
			$tab .= "<div style=clear:both;>&nbsp;</div>";
			$tab .= "<table>
				<tr>
					<td><b>".$_SESSION['lang']['material']."</b></td>
				</tr>
			</table>
			<table class=sortable cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['namabarang']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['satuan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['jumlah']."</td>
				</tr>
				</thead>
				<tbody>";
			$no = 0;
			while($bar = $res->fetch()){
				$no++;
				$tab .= "<tr class=rowcontent>
					<td style='text-align:right'>".$no."</td>
					<td>".$bar['namabarang']."</td>
					<td>".$bar['satuan']."</td>
					<td style='text-align:right'>".number_format($bar['kwantitas'])."</td>
				<tr>";
			}
			$tab .= "</tbody>
			</table>";
		}
		
		$tab .= "<div>";
		
		echo $tab;
		break;
		
	case 'sus_change_satuan':
		$str = "select satuan from ".$dbname.".kebun_5jenishama where kodehama = '".$sus_dt_jenishama."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		echo $bar['satuan'];
		break;
		
	case 'png_change_satuan':
		$str = "select satuan from ".$dbname.".kebun_5jenishama where kodehama = '".$png_dt_jenishama."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		echo $bar['satuan'];
		break;
	
	default:
		break;
}

function loadDataTab1(){
	global $dbname;
	global $_POST;
	global $owlPDO;

	$cariNoTransaksi = checkPostGet('cariNoTransaksi','');
	$limit=10;
	$page=0;
	if(isset($_POST['page'])) {
		$page=$_POST['page'];
		if($page<0) $page=0;
	}
	
	$offset=$page*$limit;
	$maxdisplay=($page*$limit);
	
	
	$str="select count(*) jmlhrow from ".$dbname.".kebun_hpt_sensus_ht
	where notransaksi like '%".$cariNoTransaksi."%' and blok like '".$_SESSION['empl']['lokasitugas']."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$jlhbrs= $bar->jmlhrow;
	}
	
	$str="select * from ".$dbname.".kebun_hpt_sensus_ht
	where notransaksi like '%".$cariNoTransaksi."%' and blok like '".$_SESSION['empl']['lokasitugas']."%' order by nosensus desc
		  limit ".$offset.",".$limit." ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$tab='';
	$nor=0;
	$nor=$maxdisplay;
	if($jlhbrs <= 0){
		$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['dataempty']."</tr>";
	}else{
		while($bar = $res->fetch()) {
			$optLuas = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$bar['blok']."'");
			$nor++;
			$tab.="<tr class=rowcontent>
				<td style='text-align:right;'>".$nor."</td>
				<td>".$bar['nosensus']."</td>
				<td>".$bar['notransaksi']."</td>
				<td>".$bar['blok']."</td>
				<td style='text-align:right;'>".$optLuas[$bar['blok']]."</td>
				<td style='text-align:center'>".tanggalnormal(substr($bar['nosensus'],0,8))."</td>
				<td style='text-align:center'>";
				$sChild = "select count(t2.kodehama) as countHama from ".$dbname.".kebun_hpt_penanggulangan_ht t1 
				left join ".$dbname.".kebun_hpt_penanggulangan_dt t2 ON t1.nopenanggulangan = t2.nopenanggulangan 
				where t1.nosensus = '".$bar['nosensus']."'";
				$rChild=$owlPDO->query($sChild) or die(print " Gagal: ".PDOException::getMessage());
				$rChild->setFetchMode(PDO::FETCH_ASSOC);
				$bChild = $rChild->fetch();
				$numrows = $bChild['countHama'];
				if($numrows > 0){
					$tab .= "<img src=images/skyblue/posted.png class=zImgOffBtn style='cursor:pointer' title=detailed onclick=\"detailSensus('".$_SESSION['lang']['detail']." ".$_SESSION['lang']['penanggulangan']." (".$bar['nosensus'].")','".$bar['nosensus']."','<div id=formPencarianTransaksi></div>',event);\">";
				}else{
					$tab .= "<img src=images/skyblue/posting.png class=zImgOffBtn title=not detailed>";
				}
			$tab .= "</td>
				<td style='text-align:center'>
					<img src=images/application/application_edit.png class=resicon  title='Edit ".$bar['nosensus']."' onclick=\"sus_fillfield('".$bar['nosensus']."','".$bar['notransaksi']."','".tanggalnormal(substr($bar['nosensus'],0,8))."','".$bar['blok']."','".$optLuas[$bar['blok']]."');\" >
				</td>";
				if($numrows > 0){
					$tab .= "<td></td>";
				}else{
					$tab .= "<td style='text-align:center'>
						<img src=images/application/application_delete.png class=resicon  title='Delete ".$bar['nosensus']."' onclick=\"delData('".$bar['nosensus']."');\" >
					</td>";
				}
				
			$tab .= "</tr>"; 
		}
	}
	$totrows=ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	$isiRow='';
	for($er=1;$er<=$totrows;$er++){
		$sel = ($page==$er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	$footd="</tr>
		<tr><td colspan=9 align=center>";
	
	if($page=='0'){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
	}
	
	$footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
	
	if(($page+1) == $totrows){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
	}
	$footd.="</td>
		</tr>";
	return $tab."####".$footd;
}

function loadDataTab2(){
	global $dbname;
	global $_POST;
	global $owlPDO;
	
	$cariNoTransaksi = checkPostGet('cariNoTransaksi2','');
	
	$limit=10;
	$page=0;
	if(isset($_POST['page2'])) {
		$page=$_POST['page2'];
		if($page<0) $page=0;
	}
	
	$offset=$page*$limit;
	$maxdisplay=($page*$limit);

	$str="select count(*) jmlhrow from ".$dbname.".kebun_hpt_penanggulangan_ht
	where notransaksi like '%".$cariNoTransaksi."%'  and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$jlhbrs= $bar->jmlhrow;
	}
	
	$str="select * from ".$dbname.".kebun_hpt_penanggulangan_ht
	where notransaksi like '%".$cariNoTransaksi."%' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by nopenanggulangan desc
		  limit ".$offset.",".$limit." ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$tab='';$nor=0;$nor=$maxdisplay;
	if($jlhbrs <= 0){
		$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['dataempty']."</tr>";
	}else{
		while($bar = $res->fetch()) {
			$optLuas = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$bar['kodeorg']."'");
			$nor++;
			$tab.="<tr class=rowcontent>
				<td style='text-align:right;'>".$nor."</td>
				<td>".$bar['nopenanggulangan']."</td>
				<td>".($bar['nosensus']=='' ? '-' : $bar['nosensus'])."</td>
				<td>".$bar['notransaksi']."</td>
				<td>".$bar['kodeorg']."</td>
				<td style='text-align:right;'>".$optLuas[$bar['kodeorg']]."</td>
				<td style='text-align:center'>".tanggalnormal(substr($bar['nopenanggulangan'],0,8))."</td>
				<td style='text-align:center'>
					<img src=images/application/application_edit.png class=resicon  title='Edit ".$bar['nopenanggulangan']."' onclick=\"png_fillfield('".$bar['nopenanggulangan']."','".$bar['nosensus']."','".$bar['notransaksi']."','".tanggalnormal(substr($bar['nopenanggulangan'],0,8))."','".$bar['kodeorg']."','".$optLuas[$bar['kodeorg']]."');\" >
				</td>
				<td style='text-align:center'>
					<img src=images/application/application_delete.png class=resicon  title='Delete ".$bar['nopenanggulangan']."' onclick=\"delData2('".$bar['nopenanggulangan']."');\" >
				</td>
			</tr>"; 
		}
	}
	$totrows=ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	$isiRow='';
	for($er=1;$er<=$totrows;$er++){
		$sel = ($page==$er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	$footd="</tr>
		<tr><td colspan=9 align=center>";
	
	if($page=='0'){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loadData2(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
	}
	
	$footd.="<select id=\"pages2\" name=\"pages2\" style=\"width:50px\" onchange=\"getPage2()\">".$isiRow."</select>";
	
	if(($page+1) == $totrows){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loadData2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
	}
	$footd.="</td>
		</tr>";
	return $tab."####".$footd;
}

function setNoSensus($tanggal){
	global $dbname;
	
	$data = "";
	
	#=== Generate No Hpt
	# Get Existing Data
	$fWhere = "nosensus like '%".tanggalsystem($tanggal)."%' and nosensus like '%".$_SESSION['empl']['lokasitugas']."%'";
	$fQuery = selectQuery($dbname,'kebun_hpt_sensus_ht','nosensus',$fWhere);
	$tmpNo = fetchData($fQuery);
	
	# Generate No Hpt
	if(count($tmpNo)==0) {
	    $data = tanggalsystem($tanggal)."/".$_SESSION['empl']['lokasitugas']."/HPT/001";
	} else {
	    # Get Max No Urut
	    $maxNo = 1;
	    foreach($tmpNo as $row) {
		$tmpRow = explode('/',$row['nosensus']);
		$noUrut = (int)$tmpRow[3];
		if($noUrut>$maxNo)
		    $maxNo = $noUrut;
	    }
	    $currNo = addZero($maxNo+1,3);
	    $data = tanggalsystem($tanggal)."/".$_SESSION['empl']['lokasitugas']."/HPT/".$currNo;
	}
	
	return $data;
}

function setNoPenanggulangan($tanggal){
	global $dbname;
	
	$data = "";
	
	#=== Generate No Penanggulangan
	# Get Existing Data
	$fWhere = "nopenanggulangan like '%".tanggalsystem($tanggal)."%' and nopenanggulangan like '%".$_SESSION['empl']['lokasitugas']."%'";
	$fQuery = selectQuery($dbname,'kebun_hpt_penanggulangan_ht','nopenanggulangan',$fWhere);
	$tmpNo = fetchData($fQuery);
	
	# Generate No Penanggulangan
	if(count($tmpNo)==0) {
	    $data = tanggalsystem($tanggal)."/".$_SESSION['empl']['lokasitugas']."/PNG/001";
	} else {
	    # Get Max No Urut
	    $maxNo = 1;
	    foreach($tmpNo as $row) {
		$tmpRow = explode('/',$row['nopenanggulangan']);
		$noUrut = (int)$tmpRow[3];
		if($noUrut>$maxNo)
		    $maxNo = $noUrut;
	    }
	    $currNo = addZero($maxNo+1,3);
	    $data = tanggalsystem($tanggal)."/".$_SESSION['empl']['lokasitugas']."/PNG/".$currNo;
	}
	
	return $data;
}
?>