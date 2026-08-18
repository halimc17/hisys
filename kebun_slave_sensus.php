<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
#require_once('jpgraph/jpgraph.php');
#require_once ('jpgraph/jpgraph_bar.php');
$param=$_POST;
if(count($param)=='0'){
	$param=$_GET;
}

$thn      = checkPostGet('thn', '');
$sms      = checkPostGet('sms', '');
$sms2      = checkPostGet('sms2', '');
$divisi   = checkPostGet('divisi', '');
$method   = checkPostGet('method', '');
$stblok   = checkPostGet('stblok', '');
$luas     = checkPostGet('luas', '');
$blok     = checkPostGet('blok', '');
$pokok    = checkPostGet('pokok', '');
$kerapatan    = checkPostGet('kerapatan', '');
$jjg      = checkPostGet('jjg', '');
$kg       = checkPostGet('kg', '');
$bjr	  = checkPostGet('bjr','');
$mode	  = checkPostGet('mode','');
$thnsch   = checkPostGet('thnsch', '');
$smssch   = checkPostGet('smssch', '');
$stbloksch= checkPostGet('stbloksch', '');
$divisisch= checkPostGet('divisisch', '');
$arrsms   =array(
	"1"=>"I",
	"2"=>"II",
	"3"=>"III",
	"4"=>"IV",
	"5"=>"V",
	"6"=>"VI",
	"7"=>"VII",
	"8"=>"VIII",
	"9"=>"IX",
	"10"=>"X",
	"11"=>"XI",
	"12"=>"XII",
);
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch ($method) {
    case 'excel':
		if ($thn == '') {
			exit("Warning : Tahun masih kosong");
		}
		$stream.="
			<table cellpading=1 cellspacing=1 border=1 class=sortable>
			<tr>
			<td align=center rowspan=3 width=30px bgcolor=gray>".$_SESSION['lang']['nourut']."</td>
			<td align=center rowspan=3 width=100px  bgcolor=gray>".$_SESSION['lang']['blok']."</td>
			<td align=center rowspan=3 width=50px  bgcolor=gray>".$_SESSION['lang']['luas']."</td>
			<td align=center rowspan=3 width=60px  bgcolor=gray>".$_SESSION['lang']['pokok']."</td>
			<td align=center rowspan=3 width=70px  bgcolor=gray>".$_SESSION['lang']['jjg']."</td>
			<td align=center rowspan=3 width=90px  bgcolor=gray>".$_SESSION['lang']['kg']."</td>
			<td align=center rowspan=3 width=50px bgcolor=gray >".$_SESSION['lang']['bjr']."</td>
			<td align=center colspan=24  bgcolor=gray>".$_SESSION['lang']['sebaran']."</td>
			</tr>";
		$stream.="<tr>";
		for ($i = 1; $i <= 12; $i++) {
			$stream.="
				<td align=center colspan=2 bgcolor=gray>".numToMonth($i, 'I', 'long')."</td>
				";
		}
		$stream.="</tr>";
		$stream.="<tr>";
		for ($i = 1; $i <= 12; $i++) {
			$stream.="
				<td align=center bgcolor=gray>".$_SESSION['lang']['jjg']."</td>
				<td align=center bgcolor=gray>".$_SESSION['lang']['kg']."</td>
				";
		}
		$stream.="</tr>";
		$str = "select * from ".$dbname.".kebun_rencanapanen where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and tahun='".$thn."' ";
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$kdblok[$bar['kodeblok']] = $bar['kodeblok'];
			$kddivisi[$bar['kodeorg']] = $bar['kodeorg'];
			$listblok[$bar['kodeorg']][$bar['kodeblok']] = $bar['kodeblok'];
			$jumlahjjg[$bar['kodeorg']][$bar['kodeblok']] += $bar['jumlah'];
			$kgsensus[$bar['kodeorg']][$bar['kodeblok']] += $bar['kgsensus'];
			$jjgx[$bar['kodeorg']][$bar['kodeblok']][$bar['bulan']] = $bar['jumlah'];
			$kgx[$bar['kodeorg']][$bar['kodeblok']][$bar['bulan']] = $bar['kgsensus'];
			$pokokx[$bar['kodeorg']][$bar['kodeblok']] = $bar['jumlahpokok'];
			$luasx[$bar['kodeorg']][$bar['kodeblok']] = $bar['jumlahha'];
		}
		foreach($kddivisi as $divisi) {
			foreach($kdblok as $blok) {
				if ( $listblok[$divisi][$blok] != '') {
					$no += 1;
					$stream.="<tr class=rowcontent id=row".$no.">";
					$stream.="<td align=center>".$no."</td>";
					$stream.="<td align=center>".$blok."</td>";
					$stream.="<td align=right>". number_format($luasx[$divisi][$blok], 2)."</td>";
					$stream.="<td align=right>". number_format($pokokx[$divisi][$blok])."</td>";
					$stream.="<td align=right>". number_format($jumlahjjg[$divisi][$blok])."</td>";
					$stream.="<td align=right>". number_format($kgsensus[$divisi][$blok], 2)."</td>";
					$stream.="<td align=right>". number_format($kgsensus[$divisi][$blok] / $jumlahjjg[$divisi][$blok], 2)."</td>";
					for ($i = 1; $i <= 12; $i++) {
						$stream.="
							<td align=right>". number_format($jjgx[$divisi][$blok][$i])."</td>
							<td align=right>". number_format($kgx[$divisi][$blok][$i], 2)."</td>
							";
						$stjjg[$divisi][$i] += $jjgx[$divisi][$blok][$i];
						$stkg[$divisi][$i] += $kgx[$divisi][$blok][$i];
					}
					$stluas[$divisi] += $luasx[$divisi][$blok];
					$stpokok[$divisi] += $pokokx[$divisi][$blok];
					$stjumlahjjg[$divisi] += $jumlahjjg[$divisi][$blok];
					$stkgsensus[$divisi] += $kgsensus[$divisi][$blok];
				}
			}
			$stream.="
				<tr>
				<td  bgcolor=#80FFFE colspan=2 align=center>".$_SESSION['lang']['subtotal']."</td>
				<td  bgcolor=#80FFFE align=right>". number_format($stluas[$divisi], 2)."</td>
				<td  bgcolor=#80FFFE align=right>". number_format($stpokok[$divisi])."</td>
				<td  bgcolor=#80FFFE align=right>". number_format($stjumlahjjg[$divisi])."</td>
				<td  bgcolor=#80FFFE align=right>". number_format($stkgsensus[$divisi], 2)."</td>
				<td  bgcolor=#80FFFE align=right>". number_format($stkgsensus[$divisi] / $stjumlahjjg[$divisi], 2)."</td>";
			for ($i = 1; $i <= 12; $i++) {
				$stream.="<td  bgcolor=#80FFFE align=right>". number_format($stjjg[$divisi][$i])."</td>";
				$stream.="<td  bgcolor=#80FFFE align=right>". number_format($stkg[$divisi][$i], 2)."</td>";
				$gtjjg[$i] += $stjjg[$divisi][$i];
				$gtkg[$i] += $stkg[$divisi][$i];
			}
			$stream.="</tr>";
			$gtluas += $stluas[$divisi];
			$gtpokok += $stpokok[$divisi];
			$gtjumlahjjg += $stjumlahjjg[$divisi];
			$gtkgsensus += $stkgsensus[$divisi];
		}
		$stream.="
			<tr>
			<td colspan=2 align=center  bgcolor=#48D1CC>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right  bgcolor=#48D1CC>". number_format($gtluas, 2)."</td>
			<td align=right  bgcolor=#48D1CC>". number_format($gtpokok)."</td>
			<td align=right  bgcolor=#48D1CC>". number_format($gtjumlahjjg)."</td>
			<td align=right  bgcolor=#48D1CC>". number_format($gtkgsensus, 2)."</td>
			<td align=right  bgcolor=#48D1CC>". number_format($gtkgsensus / $gtjumlahjjg, 2)."</td>";
		for ($i = 1; $i <= 12; $i++) {
			$stream.="<td align=right  bgcolor=#48D1CC>". number_format($gtjjg[$i])."</td>";
			$stream.="<td align=right  bgcolor=#48D1CC>". number_format($gtkg[$i], 2)."</td>";
		}
		$stream.="</tr>";
		$stream.="</table>";
		// exit("Error:$stream");
		$nop_ = "Laporan_sensus_".$thn;
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						unlink('tempExcel/'.$file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$nop_.".xls", 'w');
			if (!fwrite($handle, $stream)) {
				echo "<script language=javascript1.2>
				parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			closedir($handle);
		}
    break;
    case 'detail':
    	$theme = $_SESSION['theme'];
    	if ($theme == 'skyblue' || $theme == '') {
    		$gen = 'generic.css';
    	} else if ($theme == 'red') {
    		$gen = 'genericRed.css';
    	} else {
    		$gen = 'genericGray.css';
    	}

		if ($sms < 10) {
			$month1 = "0".$sms;
		} else {
			$month1 = $sms;
		}

		if ($sms2 < 10) {
			$month2 = "0".$sms2;
		} else {
			$month2 = $sms2;
		}

		$date1 = $thn."-".$month1."-"."01";
		$date2 = $thn."-".$month2."-"."01";
		$diff = abs(strtotime($date2)-strtotime($date1));
		$years = floor($diff / (365*60*60*24));
		$diffmonth = (floor(($diff - $years * 365*60*60*24) / (30*60*60*24)) + 1);

    	$stream = "";
    	$stream.="<link rel=stylesheet type=text/css href=style/".$gen.">";
    	
    	$stream.="
    		<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>
    		<thead>
    		<tr>
    		<td align=center rowspan=3 width=30px >".$_SESSION['lang']['nourut']."</td>
    		<td align=center rowspan=3 width=100px >".$_SESSION['lang']['blok']."</td>
    		<td align=center rowspan=3 width=50px >".$_SESSION['lang']['luas']."</td>
    		<td align=center rowspan=3 width=60px >".$_SESSION['lang']['pokok']."</td>
    		<td align=center rowspan=3 width=70px >".$_SESSION['lang']['jjg']."</td>
    		<td align=center rowspan=3 width=90px >".$_SESSION['lang']['kg']."</td>
    		<td align=center rowspan=3 width=90px >Kerapatan</td>
    		<td align=center rowspan=3 width=50px >".$_SESSION['lang']['bjr']."</td>
    		<td align=center colspan=".($diffmonth*3).">".$_SESSION['lang']['sebaran']."</td>
    		</tr>";
    	$stream.="<tr>";
			for ($i = $sms; $i <= $sms2; $i++) {
    			$stream.="
					<td align=center colspan=3>".numToMonth($i, 'I', 'long')."</td>
				";
    		}
		$stream.="</tr>";
		$stream.="<tr>";
    		for ($i = $sms; $i <= $sms2; $i++) {
    			$stream.="
    				<td align=center>".$_SESSION['lang']['jjg']."</td>
    				<td align=center>".$_SESSION['lang']['kg']."</td>
    				<td align=center>".$_SESSION['lang']['bjr']."</td>
    				";
    		}
		$stream.="</tr>";
    	$stream.="</thead>";

		$smestr = " and bulan between ".$sms." and ".$sms2." ";
		$jjg=$kg=$bjr=$pokok=$luas=$kgsensus=$jumlahjjg=$bjrsensus=$kdblok=array();

    	$str = "select * from ".$dbname.".kebun_rencanapanen a where kodeorg like '".$divisi."%' and statusblok='".$stblok."' and tahun='".$thn."' ".$smestr." ";
    	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($bar = $res->fetch()) {
    		$kdblok[$bar['kodeblok']] = $bar['kodeblok'];
    		$jumlahjjg[$bar['kodeblok']] += $bar['jumlah'];
    		$kgsensus[$bar['kodeblok']] += $bar['kgsensus'];
    		$bjrsensus[$bar['kodeblok']] += $bar['bjrsensus'];
    		$kerapatanx[$bar['kodeblok']] += $bar['kerapatan'];
    		$jjg[$bar['kodeblok']][$bar['bulan']]=$bar['jumlah'];
    		$kg[$bar['kodeblok']][$bar['bulan']]=$bar['kgsensus'];
    		$bjr[$bar['kodeblok']][$bar['bulan']]=$bar['bjrsensus'];
    		$pokok[$bar['kodeblok']] = $bar['jumlahpokok'];
    		$luas[$bar['kodeblok']] = $bar['jumlahha'];
    	}
    	foreach($kdblok as $blok) {
			$no += 1;
    		$stream.="<tr class=rowcontent id=row".$no.">";
    		$stream.="<td align=center>".$no."</td>";
    		$stream.="<td align=center>".$nmorg[$blok]."</td>";
    		$stream.="<td align=right>". number_format($luas[$blok], 2)."</td>";
    		$stream.="<td align=right>". number_format($pokok[$blok])."</td>";
    		$stream.="<td align=right>". number_format($jumlahjjg[$blok])."</td>";
    		$stream.="<td align=right>". number_format($kgsensus[$blok], 2)."</td>";
    		$stream.="<td align=right>". number_format($kerapatanx[$blok], 2)."</td>";
    		$stream.="<td align=right>". number_format($bjrsensus[$blok], 2)."</td>";
			for ($i = $sms; $i <= $sms2; $i++) {
				$stream.="
					<td align=right>". number_format($jjg[$blok][$i])."</td>
					<td align=right>". number_format($kg[$blok][$i], 2)."</td>
					<td align=right>". number_format($bjr[$blok][$i], 2)."</td>
				";
				$tjjg[$i] += $jjg[$blok][$i];
				$tkg[$i] += $kg[$blok][$i];
				$tbjr[$i] += $bjr[$blok][$i];
			}
    		$stream.="</tr>";
			## total
			$tluas += $luas[$blok];
			$tpokok += $pokok[$blok];
			$tjumlahjjg += $jumlahjjg[$blok];
			$tkgsensus += $kgsensus[$blok];
			$tbjrsensus += $bjrsensus[$blok];
    	}
    	$stream.="
		<tr  bgcolor=#80FFFE>
    		<td colspan=2 align=center>".$_SESSION['lang']['total']."</td>
    		<td align=right>". number_format($tluas, 2)."</td>
    		<td align=right>". number_format($tpokok)."</td>
    		<td align=right>". number_format($tjumlahjjg)."</td>
    		<td align=right>". number_format($tkgsensus, 2)."</td>
    		<td align=right></td>
    		<td align=right>". number_format($tbjrsensus, 2)."</td>";

		for ($i = $sms; $i <= $sms2; $i++) { 
			$stream.="<td align=right>". number_format($tjjg[$i])."</td>";
			$stream.="<td align=right>". number_format($tkg[$i], 2)."</td>";
			$stream.="<td align=right>". number_format($tbjr[$i], 2)."</td>";
		}
    	$stream.="</tr>";
    	$stream.="</table>";
    	echo $stream;
	break;
    case 'posting':
    	$str = "update ".$dbname.".kebun_rencanapanen set posting=1 where "
    		." kodeorg='".$divisi."' and tahun='".$thn."' and statusblok='".$stblok."' and semester='".$sms."' ";
    	try {
    		$owlPDO->exec($str);
    	} catch (PDOException $e) {
    		print " Gagal  !: ".$e->getMessage()."\n";
    		die();
    	}
	break;
    case 'delete':
    	$str = "delete from ".$dbname.".kebun_rencanapanen where "
    		." kodeorg='".$divisi."' and tahun='".$thn."' and statusblok='".$stblok."' and semester='".$sms."' ";
    	try {
    		$owlPDO->exec($str);
    	} catch (PDOException $e) {
    		print " Gagal  !: ".$e->getMessage()."\n";
    		die();
    	}
	break;
    case 'loaddata':
    	$where = "";
    	if ($thnsch != '') {
    		$where.=" and tahun='".$thnsch."' ";
    	}
    	if ($smssch != '') {
    		$where.=" and semester='".$smssch."' ";
    	}
    	if ($stbloksch != '') {
    		$where.=" and statusblok='".$stbloksch."' ";
    	}
    	if ($divisisch != '') {
    		$where.=" and kodeorg like '".$divisisch."%' ";
    	}
    	$limit = 20;
    	$page = 0;
    	if (isset($_POST['page'])) {
    		$page = intval($_POST['page']);
    		if ($page < 0)
    			$page = 0;
    	}
    	$offset = $page * $limit;
    	$maxdisplay = ($page * $limit);
    	$ql2 = "select count(*) as jmlhrow from ".$dbname.".kebun_rencanapanen where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ".$where." group by kodeorg,tahun,semester,statusblok  ";
    	$query2 = $owlPDO->query($ql2)or die(print " Gagal: ".PDOException::getMessage());
    	$jlhbrs = owlBaris($query2);
    	$no = 0;
    	$str = "SELECT updateby,updatetime, posting,statusblok,kodeorg,min(cast(bulan AS INTEGER)) as bulanawal,
    			max(cast(bulan AS INTEGER)) as bulanakhir,tahun,sum(jumlah) as jumlah,sum(kgsensus) as kgsensus,semester 
				from ".$dbname.".kebun_rencanapanen
				where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ".$where." group by kodeorg,tahun,semester,statusblok "
				." order by kodeorg asc,tahun desc,semester desc limit ".$offset.",".$limit."";
    	$tab = "";
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe in ('KEBUN','AFDELING')");
    	$no = $maxdisplay;
    	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($bar = $res->fetch()) {
    		$no += 1;
			
    		$tab.="<tr class=rowcontent style=height:25px>";
    		$tab.="<td align=center>".$no."</td>";
    		$tab.="<td align=center>".substr($bar['kodeorg'], 0, 4)." - ".$nmorg[substr($bar['kodeorg'], 0, 4)]."</td>";
    		$tab.="<td align=center>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
    		$tab.="<td align=center>".$bar['statusblok']."</td>";
    		$tab.="<td align=center>".$arrsms[$bar['semester']]."</td>";
    		$tab.="<td align=center>".$bar['tahun']."</td>";
    		//$tab.="<td align=center>".numToMonth($bar['bulan'],'I','long')."</td>";
    		$tab.="<td align=right>". number_format($bar['jumlah'])."</td>";
    		$tab.="<td align=right>". number_format($bar['kgsensus'])."</td>";
    		$tab.="<td align=right>". number_format($bar['kgsensus'] / $bar['jumlah'], 2)."</td>";
    		$tab.="<td>".getNamaKaryawan($bar['updateby'])."</td>";
    		$tab.="<td>".$bar['updatetime']."</td>";
    		if ($bar['posting'] == 1) {
    			$tab.="
					<td align=center width=20px>
					</td>
					<td align=center width=20px>
					</td>
					<td align=center width=20px>
						<img src=images/skyblue/posted.png class=zImgOffBtn title='Posting');\">
    				</td>
    				<td align=center width=20px>
						<img src=images/skyblue/zoom.png class=zImgBtn title='Detail'
    				onclick=\"detail('".$bar['tahun']."','".$bar['kodeorg']."','".$bar['bulanawal']."','".$bar['bulanakhir']."','".$bar['statusblok']."','event');\">
    				</td>
    				";
    		} else {
    			$tab.="
    				<td align=center width=20px>
    				<img src=images/application/application_edit.png class=zImgBtn title='Edit'
    				onclick=\"edit('".$bar['tahun']."','".$bar['kodeorg']."','".$bar['bulanawal']."','".$bar['statusblok']."','".$bar['bulanakhir']."','edit');\">
    				</td>
					<td align=center width=20px>
					<img src=images/application/application_delete.png class=zImgBtn title='Delete'
    				onclick=\"del('".$bar['tahun']."','".$bar['kodeorg']."','".$bar['semester']."','".$bar['statusblok']."');\">
					</td>
					<td align=center width=20px>
    				<img src=images/skyblue/posting.png class=zImgBtn title='Posting'
    				onclick=\"posting('".$bar['tahun']."','".$bar['kodeorg']."','".$bar['semester']."','".$bar['statusblok']."');\">
    				</td>
					<td align=center width=20px>
    				<img src=images/skyblue/zoom.png class=zImgBtn title='Detail'
    				onclick=\"detail('".$bar['tahun']."','".$bar['kodeorg']."','".$bar['bulanawal']."','".$bar['bulanakhir']."','".$bar['statusblok']."','event');\">
					</td>
    				";
    		}
    		$tab.="</tr>";
    	}
    	$totrows = ceil($jlhbrs / $limit);
    	if ($totrows == 0) {
    		$totrows = 1;
    	}
    	$isiRow = '';
    	for ($er = 1; $er <= $totrows; $er++) {
    		$sel = ($page == $er - 1) ? 'selected' : '';
    		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
    	}
    	$footd = "
    		<tr><td colspan=15 align=center>
    		<button class=mybutton onclick=loaddata(".($page - 1).");>".$_SESSION['lang']['pref']."</button>
    		<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
    		<button class=mybutton onclick=loaddata(".($page + 1).");>".$_SESSION['lang']['lanjut']."</button>
    		</td>
    		</tr>";
    	echo $tab."####".$footd;
	break;
    case 'savedata':
    	if ($jjg == '') {
    		$jjg = 0;
    	}
    	if ($kg == '') {
    		$kg = 0;
    	}

		## validasi
		for ($i=$sms; $i<=$sms2 ; $i++) { 
			$sebaran = checkPostGet('sebaran'.$i, '');
			$sebaranjjg = checkPostGet('sebaranjjg'.$i, '');
			$sebaranbjr = checkPostGet('sebaranbjr'.$i, '');
			$totalsebaran += $sebaran;
			$totalsebaranjjg += $sebaranjjg;
			$totalsebaranbjr += $sebaranbjr;
		}

		// Cek Untuk Membentuk Semester
		$sCek = "SELECT distinct semester FROM $dbname.kebun_rencanapanen WHERE kodeblok = '".$blok."' AND statusblok='".$stblok."' and tahun='".$thn."' order by semester desc limit 1";
		$rCek = fetchdata($sCek);
		// Jika belum ada data terbentuk maka semester 1
		if ($mode == 'edit') {
			$smsall = $rCek[0]['semester'];
		} else {
			if ($rCek[0]['semester'] == 0 || $rCek[0]['semester'] == "" || $rCek[0]['semester'] == null) {
				$smsall = 1;
			} else {
				// Jika sudah add maka tambahkan 1 semesternya
				$smsall = ($rCek[0]['semester'] + 1);
			}
		}
		
		
    	$selisih = abs($totalsebaran - $kg);
    	$selisihjjg = abs($totalsebaranjjg - $jjg);
    	$selisihbjr = abs($totalsebaranbjr - $bjr);
		// exit("Warning: ".$smsall);
		// exit("Warning: ".$totalsebaran."======".$kg."======".$selisih);
    	if ($selisih >= 1) {
    		exit("Error: Ada selisih pada Total Kg sebaran dengan Total Kg di Bulan ".numToMonth($sms,"I","long")." S/D ".numToMonth($sms2,"I","long").", sebanyak : ". number_format($selisih, 2)."  Kg \n\nSilahkan diperbaiki kemudian tekan tombol Proses.");
    	} else if ($selisihjjg >= 1) {
    		exit("Error: Ada selisih pada Total Jjg sebaran dengan Total Jjg di Bulan ".numToMonth($sms,"I","long")." S/D ".numToMonth($sms2,"I","long").", sebanyak : ". number_format($selisihjjg, 2)."  Jjg \n\nSilahkan diperbaiki kemudian tekan tombol Proses.");
    	} elseif ($selisihbjr >= 1) {
			exit("Error: Ada selisih pada Total BJR sebaran dengan Total BJR di Bulan ".numToMonth($sms,"I","long")." S/D ".numToMonth($sms2,"I","long").", sebanyak : ". number_format($selisihbjr, 2)."  BJR \n\nSilahkan diperbaiki kemudian tekan tombol Proses.");
		} else {
			for ($i = $sms; $i <= $sms2; $i++) {
				$sebaran = checkPostGet('sebaran'.$i, '');
				$sebaranjjg = checkPostGet('sebaranjjg'.$i, '');
				$sebaranbjr = checkPostGet('sebaranbjr'.$i, '');

				if ($i < 10) {
					$tgl = $thn.'-0'.$i.'-01';
				} else {
					$tgl = $thn.'-'.$i.'-01';
				}

				$str = "delete from ".$dbname.".kebun_rencanapanen where kodeorg='".$divisi."' and kodeblok='".$blok."' "
					."and tahun='".$thn."' and bulan='".$i."' ";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: ".$e->getMessage()."\n";
					die();
				}
				
				if ($jjg == 0 or $kg == 0) {}
				else {
					$dtSensus = array(
						'kodeorg'		=> $divisi,
						'kodeblok'		=> $blok,
						'tahun'			=> $thn,
						'bulan'			=> $i,
						'tanggal'		=> $tgl,
						'statusblok'	=> $stblok,
						'semester'		=> $smsall,
						'jumlah'		=> $sebaranjjg,
						'jumlahha'		=> $luas,
						'jumlahpremi'	=> '0',
						'jumlahpokok'	=> $pokok,
						'kgsensus'		=> $sebaran,
						'kerapatan'		=> $kerapatan,
						'bjrsensus'		=> $sebaranbjr,
						'updateby'		=> $_SESSION['standard']['userid']
					);

					$colsSensus = array();
					foreach ($dtSensus as $key => $row) {
						$colsSensus[] = $key;
					}
					
					$str = insertQuery($dbname,"kebun_rencanapanen",$dtSensus,$colsSensus);
					try {
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
				}
			}
    	}
    	// exit("Error:MASUK");
	break;
    case 'detailinput':
		## validasi untuk posting
    	// echo $str = "select * from ".$dbname.".kebun_rencanapanen where kodeorg like '".$divisi."%' and statusblok='".$stblok."' and tahun='".$thn."' and semester='".$sms."' and posting=1 ";
		$str = "select * from ".$dbname.".kebun_rencanapanen where kodeorg like '".$divisi."%' and statusblok='".$stblok."' 
		and tahun='".$thn."' and bulan BETWEEN ".$sms." and ".$sms2." and posting='1'";
    	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
    	$rowposting = owlBaris($res);
    	if ($rowposting > 1) {
    		exit("Warning : Data sudah pernah di-input dan di posting.");
    	}
		
		# bentuk data awal
		$luas=$pokok=$tt=array();
    	$str = "select * from ".$dbname.".setup_blok b 
            where b.kodeorg like '".$divisi."%' and b.statusblok='".$stblok."' ";
    	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
    	$row = owlBaris($res);
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($bar = $res->fetch()) {
    		$kdblok[$bar['kodeorg']] = $bar['kodeorg'];
            $namablok[$bar['kodeorg']] = $bar['kodeorg'];
    		$luas[$bar['kodeorg']] = $bar['luasareaproduktif'];
    		$tt[$bar['kodeorg']] = $bar['tahuntanam'];
    		$pokok[$bar['kodeorg']] = $bar['jumlahpokok'];
    	}
        if(count($kdblok)==0)
        {
            exit("Warning : Blok dengan status ".$stblok." tidak ada di divisi ".$divisi);
        }
    	 ## data jika sudah ada

    	$kg = array();
    	$jjg = array();
		$bjr = array();
    	// $str = "select * from ".$dbname.".kebun_rencanapanen where kodeorg like '".$divisi."%' and statusblok='".$stblok."' and tahun='".$thn."' and semester='".$sms."' ";
    	$str = "select * from ".$dbname.".kebun_rencanapanen where kodeorg like '".$divisi."%' and statusblok='".$stblok."' 
				and tahun='".$thn."' and bulan BETWEEN ".$sms." and ".$sms2." ";
    	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($bar = $res->fetch()) {
			@$jumlahjjg[$bar['kodeblok']] += $bar['jumlah'];
			@$kgsensus[$bar['kodeblok']] += $bar['kgsensus'];
			@$bjrsensus[$bar['kodeblok']] += $bar['bjrsensus'];
    		$jjg[$bar['kodeblok']][$bar['bulan']] = $bar['jumlah'];
    		$kg[$bar['kodeblok']][$bar['bulan']] = $bar['kgsensus'];
    		$bjr[$bar['kodeblok']][$bar['bulan']] = $bar['bjrsensus'];
			@$kerapatanx[$bar['kodeblok']] += $bar['kerapatan'];
    	}

		if ($sms < 10) {
			$month1 = "0".$sms;
		} else {
			$month1 = $sms;
		}

		if ($sms2 < 10) {
			$month2 = "0".$sms2;
		} else {
			$month2 = $sms2;
		}

		$date1 = $thn."-".$month1."-"."01";
		$date2 = $thn."-".$month2."-"."01";
		$diff = abs(strtotime($date2)-strtotime($date1));
		$years = floor($diff / (365*60*60*24));
		$diffmonth = (floor(($diff - $years * 365*60*60*24) / (30*60*60*24)) + 4);
		// exit("Warning: ".$diffmonth);
		
    	$stream = "";
		if ($mode == 'edit') {
			$stream.="<button class=mybutton onclick=saveall(".$row.",'edit');>".$_SESSION['lang']['proses']."</button>";
		} else {
			$stream.="<button class=mybutton onclick=saveall(".$row.",'');>".$_SESSION['lang']['proses']."</button>";
		}
		
    	// $stream.="<input type=hidden value='".$mode."' id='mode'>";
    	$stream.="
    		<table border=0  cellspacing=1 class=sortable>
    		<thead>
    		<tr>
    		<td align=center rowspan=3 width=30px >".$_SESSION['lang']['nourut']."</td>
    		<td align=center rowspan=3>".$_SESSION['lang']['blok']."</td>
    		<td align=center rowspan=3 width=50px >".$_SESSION['lang']['tahuntanam']."</td>
    		<td align=center rowspan=3>".$_SESSION['lang']['luas']."</td>
    		<td align=center rowspan=3>".$_SESSION['lang']['pokok']."</td>
    		<td align=center rowspan=3>".$_SESSION['lang']['jjg']."</td>
    		<td align=center rowspan=3>".$_SESSION['lang']['kg']."</td>
    		<td align=center rowspan=3>Kerapatan</td>
    		<td align=center rowspan=3>".$_SESSION['lang']['bjr']."</td>
    		<td align=center colspan=".($diffmonth*3)." >".$_SESSION['lang']['sebaran']."</td>
    		</tr>";
			
		$stream.="<tr>";
			for ($i=$sms; $i <= $sms2 ; $i++) { 
				$stream.="
					<td align=center colspan=3>".numToMonth($i, 'I', 'long')."</td>
				"; 
			}
			$stream.="
    			<td align=center hidden>Total Kg</td>
    			<td align=center hidden>Total JJG</td>
    			<td align=center hidden>Total BJR</td>
    			";
    		$stream.="<tr>";
				for ($i=$sms; $i <= $sms2 ; $i++){
					$stream.="
						<td align=center>".$_SESSION['lang']['jjg']."</td>
						<td align=center>".$_SESSION['lang']['kg']."</td>
						<td align=center>".$_SESSION['lang']['bjr']."</td>
						";
				}
    		$stream.="</tr>";
		$stream.="</tr>";
		
    	$stream.="</thead>";
    	foreach($kdblok as $blok) {
    		$no += 1;
    		$stream.="<tr class=rowcontent id=row".$no.">";
    		$stream.="<td align=center>".$no."</td>";
    		$stream.="<td align=center id=blok".$no." hidden>".$blok."</td>";
            $stream.="<td align=center >".$nmorg[$blok]."</td>";
            $stream.="<td align=center >".$tt[$blok]."</td>";
    		$stream.="<td align=right id=luas".$no.">".number_format($luas[$blok],2)."</td>";
    		$stream.="<td align=right id=pokok".$no.">". @number_format($pokok[$blok])."</td>";
    		$stream.="<td align=right><input type=text value='". @$jumlahjjg[$blok]."' id=jjg".$no." onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:50px;\"></td>";
    		$stream.="<td align=right><input type=text value='". @$kgsensus[$blok]."' id=kg".$no." onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:70px;\"></td>";
    		$stream.="<td align=right><input type=text value='". @$kerapatanx[$blok]."' id=kerapatan".$no." onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:70px;\"></td>";
			// if(@is_nan($kgsensus[$blok] / $jumlahjjg[$blok])){$bjr=0;}else{$bjr=$kgsensus[$blok] / $jumlahjjg[$blok];}
    		$stream.="<td align=right><input type=text id=bjr".$no." value='". @$bjrsensus[$blok]."' onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:50px;\"></td>";
			
    		for ($i=$sms; $i <= $sms2 ; $i++) { 
				$stream.="
					<td align=right><input type=text value='". $jjg[$blok][$i]."' onkeypress='return angka_doang(event)' id=sebaranjjg".$no."#".$i."  class=myinputtextnumber style=\"width:50px;\"></td>
					<td align=right><input type=text value='". $kg[$blok][$i]."' onkeypress='return angka_doang(event)' id=sebaran".$no."#".$i." class=myinputtextnumber style=\"width:50px;\"></td>
					<td align=right><input type=text value='". $bjr[$blok][$i]."' onkeypress='return angka_doang(event)' id=sebaranbjr".$no."#".$i." class=myinputtextnumber style=\"width:50px;\"></td>
				";
			}
    		$stream.="</tr>";
    	}
    	echo $stream;
	break;
		
	case'showupload':
		// echo"<pre>";
		// print_r($param);
		// echo"</pre>";
		
		
		$tab="";
		$tab.="<fieldset style=float:left><legend>Tempalte</legend>";
		$tab.="<table border=0>
				<tr>
					<td>Download : 
					<a href='tool_slave_getExample.php?form=SENSUS&tahun=".$param['thn']."&divisi=".$param['divisi']."&stsblok=".$param['stblok']."&sms=".$param['sms']."&sms2=".$param['sms2']."' target='frame'>Template Upload</a>
					</td>
				</tr><tr>
					<td colspan=3><hr></td>
				</tr><tr>
					<td colspan=3>
						<form id=frm name=frm enctype=multipart/form-data method=post action='kebun_slave_sensus_upload.php' target=frame>
							<input type=hidden name=method id=method value='preupload'>
							<input type=hidden name=jenisdata id=jenisdata value='sensus'>
							<input type=hidden name=tahun id=tahun value='".$param['thn']."'>
							<input type=hidden name=div id=div value='".$param['divisi']."'>
							<input type=hidden name=sts id=sts value='".$param['stblok']."'>
							<input type=hidden name=sms id=sms value='".$param['sms']."'>
							<input type=hidden name=sms2 id=sms2 value='".$param['sms2']."'>
							<input type=hidden name=MAX_FILE_SIZE value=1024000>
							File : <input name=filex type=file id=filex class=mybutton>
							Field separated by : 
							<select name=pemisah>
								<option value=','>, (comma)</option>
								<option value=';'>; (titik comma)</option>
							</select>
							<input type=button class=mybutton id=previewupload value=".$_SESSION['lang']['preview']." title='Submit this File' onclick=submitFile()>
							<input type=button class=mybutton  value=".$_SESSION['lang']['back']." title='Back' onclick=newdata()>
						</form>
					</td>
				</tr></table>";
		$tab.="</fieldset>";
		$tab.="<iframe frameborder=0 style=width:100%;height:450px; name=frame></iframe>";
		
		
		echo $tab;
	break;
	case'uploaddata':
		// if($param['currow']>'1'){
			// echo"<pre>";
			// print_r($param['currow']."\n");
			// print_r($param['currcol']);
			// echo"</pre>";
			// exit("error");	
		// }
		
		try {
			$owlPDO->beginTransaction();			
			$str = "select * from " . $dbname . ".kebun_rencanapanen where kodeorg ='".$param['div']."' and bulan between ".$param['sms']." and ".$param['sms2']." and tahun='".$param['thn']."' and statusblok='".$param['sts']."' and posting='1'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Transaksi sudah diposting.\nProses dibatalkan.");
			}
			
			if($param['currow']=='1' and $param['currcol']=='1'){
				$str = "delete from " . $dbname . ".kebun_rencanapanen where kodeorg ='".$param['div']."' and bulan between ".$param['sms']." and ".$param['sms2']." and tahun='".$param['thn']."' and statusblok='".$param['sts']."'";
				$owlPDO->exec($str);
			}
			
			$data = array(
				'kodeorg'    => $param['div'],
				'kodeblok'   => $param['blok'],
				'tahun'      => $param['thn'],
				'bulan'      => intval($param['bln']),
				'tanggal'    => $param['thn']."-".$param['bln']."-01",
				'jumlah'     => $param['jjg'],
				'jumlahha'   => $param['luas'],
				'jumlahpokok'=> $param['pokok'],
				'kgsensus'   => $param['kg'],
				'bjrsensus'  => $param['bjr'],
				'kerapatan'  => $param['kerapatan'],
				'statusblok' => $param['sts'],
				'semester'   => $param['semester'],
				'updateby'   => $_SESSION['standard']['userid'],
				'updatetime' => date("Y-m-d H:i:s")

			);

			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$query = insertQuery($dbname,'kebun_rencanapanen',$data,$cols);#exit("error".$query);
			$owlPDO->exec($query);

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
		}	
	break;
}

?>