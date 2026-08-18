<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('config/connection.php');

$pemisah    = checkPostGet('pemisah', '');
$jenisdata  = checkPostGet('jenisdata', '');
$param=$_POST;
if(count($param)=='0'){
	$param=$_GET;
}


// echo"<pre>";
// print_r($param);
// echo"</pre>";


$path='tempExcel';
if(is_dir($path)){
	writeFile($path,$pemisah);
}else{
	if(mkdir($path)){
		writeFile($path,$pemisah);
	}else{
		echo "<script> alert('Gagal, Can`t create folder for uploaded file');</script>";
		exit(0);
	}
}

function writeFile($path,$pemisah){
	global $jenisdata;
    $dir=$path;
    $ext=explode('.', basename( $_FILES['filex']['name']));
    $ext=$ext[count($ext)-1];
    $ext=strtolower($ext);

	if($ext=='csv'){
		$path = $dir."/".date('ymd').".".$ext;
        @unlink($path);

		try{
			if(move_uploaded_file($_FILES['filex']['tmp_name'], $path)){
				$x=readCSV($path,$pemisah);
                simpanData($x,$jenisdata);
			}
		}catch(Exception $e){
			echo "<script>alert(\"Error Writing File".addslashes($e->getMessage())."\");</script>";
		}
	}else{
		echo "<script>alert('Filetype not support');</script>";
	}
}

function simpanData($x,$jenisdata){
	global $dbname;
    global $conn;
    global $pemisah;
    global $owlPDO;
    global $param;
	$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	
	$jlhbaris=count($x)-2;
    #baris pertama adalah header;
    foreach($x[0] as $val){
		$header[]=trim($val);
	}

	switch ($jenisdata) {
		case 'sensus':
			$nopoisi = 0;
			foreach($x as $key => $arr) {
				if ($key == 0) {
					continue;
				} else {
					foreach($arr as $ids => $rinc) {
						if ($nopoisi != 1) {
							$nopoisi = 1;
						}
					}
				}
			}
			$date1 = $param['tahun']."-".$param['sms']."-"."01";
			$date2 = $param['tahun']."-".$param['sms2']."-"."01";
			$diff = abs(strtotime($date2)-strtotime($date1));
			$years = floor($diff / (365*60*60*24));
			$diffmonth = (floor(($diff - $years * 365*60*60*24) / (30*60*60*24)) + 1);

			$jmlhRow = count($x);

			$theme=$_SESSION['theme'];
			if($theme=='skyblue' || $theme==''){$gen='generic.css';}else if($theme=='red'){$gen='genericRed.css';  }else{$gen='genericGray.css';} 
			echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 
			echo"<div style='border: 1px solid orange; width: 150px; position: fixed; right: 20px; top: 65px; color: rgb(255, 0, 0); font-family: Tahoma; font-size: 13px; font-weight: bolder; text-align: center; background-color: rgb(255, 255, 255); z-index: 10000; display: none;' id='progress'>
			Please wait.....! <br><img src='images/progress.gif'></div>";
			
			echo"<div style=clear:both></div><br>";
			echo"<script language=javascript src=js/kebun_sensus.js?v="; echo time(); "></script>";
			echo"<script language=javascript src=js/generic.js></script>";
			echo"<script language=javascript src=js/generic.min.js></script>";
			echo"<button class=mybutton id='btnupload2' onclick=simpanall('".($jmlhRow-1)."') id=btnupload>".$_SESSION['lang']['startUpload']."</button><p>";
			
			$rows="rowspan=1";	
			echo"<div class='table-scroll'><table class=sortable cellspacing=1 border=0><thead><tr>";
				echo"<th align=center ".$rows." width=20px>No</th>
					<th align=center ".$rows.">".$_SESSION['lang']['tahun']."</th>
					<th align=center ".$rows.">".$_SESSION['lang']['status']."</th>
					<th align=center ".$rows.">".$_SESSION['lang']['divisi']."</th>
					<th align=center ".$rows.">".$_SESSION['lang']['blok']."</th>
					<th align=center ".$rows." width=50px>".$_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center ".$rows.">".$_SESSION['lang']['luas'] . "</th>
					<th align=center ".$rows.">".$_SESSION['lang']['pokok'] . "</th>
					<th align=center ".$rows.">".$_SESSION['lang']['bulan'] . "</th>
					<th align=center ".$rows.">Jjg</th>
					<th align=center ".$rows.">KG</th>
					<th align=center ".$rows.">BJR</th>
					<th align=center ".$rows.">Kerapatan</th>
					<th align=center ".$rows.">Semester</th>";
				echo"<th align=center ".$rows.">Status</th>
				</tr>";
				
			echo"</thead><tbody>";

			// $date1 = $param['tahun']."-".$param['sms']."-"."01";
			// $date2 = $param['tahun']."-".$param['sms2']."-"."01";
			// $diff = abs(strtotime($date2)-strtotime($date1));
			// $years = floor($diff / (365*60*60*24));
			// $diffmonth = (floor(($diff - $years * 365*60*60*24) / (30*60*60*24)) + 1);
			// echo ($diffmonth*3);

			// Query Cek Apakah Sudah ada semseter berdasarkan periode dari dan tujuan
			$sFel = "SELECT semester, MIN(CAST(bulan AS int)) AS bulanawal, MAX(CAST(bulan AS int)) AS bulanakhir FROM $dbname.kebun_rencanapanen 
			WHERE kodeorg = '".$param['div']."' AND statusblok='".$param['sts']."' and tahun='".$param['tahun']."'
			AND bulan between ".$param['sms']." AND ".$param['sms2']."";
			$rFel = fetchData($sFel);


			// Cek Untuk Membentuk Semester
			$sCek = "SELECT distinct semester FROM $dbname.kebun_rencanapanen WHERE kodeorg = '".$param['div']."' AND statusblok='".$param['sts']."' 
			and tahun='".$param['tahun']."' order by semester desc limit 1";
			$rCek = fetchdata($sCek);

			if ($rCek[0]['semester'] == 0 || $rCek[0]['semester'] == "" || $rCek[0]['semester'] == null) {
				// Jika belum ada data terbentuk maka semester 1
				// echo "Masukk 1";
				$smsall = 1;
			} elseif (($rFel[0]['bulanawal'] == $param['sms'] && $rFel[0]['bulanakhir'] == $param['sms2'])) {
				// Jika bulan maksimalnya sama dengan periode dari dan tujuan maka tetap menggunakan semester yang ada
				// echo "Masukk 2";
				// echo "Bulan Awal: ". $rFel[0]['bulanawal'];
				// echo "Bulan Akhir: ". $rFel[0]['bulanakhir'];
				// echo "========";
				// echo $param['sms']."====".$param['sms2'];
				$smsall = $rFel[0]['semester'];
			} else {
				// Jika Sudah ada data lain maka 
				// echo "Masukk 3";
				$smsall = ($rCek[0]['semester'] + 1);
			}


			$no='0'; $err=0;
			for ($row = 1; $row < $jmlhRow; $row++) {
				$tahun= $x[$row][1];
				$sts  = $x[$row][2];
				$div  = $x[$row][3];
				$blok = $x[$row][4];
				$tt   = $x[$row][5];
				$luas = $x[$row][6];
				$pokok= $x[$row][7];
				$bulan= $x[$row][8];
				$jjg  = $x[$row][9];
				$kg   = $x[$row][10];
				$bjr  = $x[$row][11];
				$kerapatan  = $x[$row][12];
				
				$no++;
				$dataerr=0;
				
				if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
				$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0'  and kodeorg='".$blok."'";
				}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
					$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and substr(kodeorg,1,4) in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') and kodeorg='".$blok."'";
				}else{
					$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'  and kodeorg='".$blok."'";
				}
				
				$a='';
				if(count(fetchdata($str))==0){
					$a=" style=background-color:red;align:center; ";$dataerr+=1;
				}
				
				$stsblk=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blok."'");
				if($stsblk[$blok]==""){
					$a=" style=background-color:red;align:center; ";$dataerr+=1;
				}
				$b='';
				if($tahun!=$param['tahun']){
					$b=" style=background-color:red;align:center; ";$dataerr+=1;
				}
				// $c='';
				// if($cawu!=$param['cawu']){
				// 	$c=" style=background-color:red;align:center; ";$dataerr+=1;
				// }
				$d='';
				if($sts!=$param['sts']){
					$d=" style=background-color:red;align:center; ";$dataerr+=1;
				}
				$e='';
				if($div!=$param['div']){
					$e=" style=background-color:red;align:center; ";$dataerr+=1;
				}
				$err+=$dataerr;
				
				// $jlhbrs=0;$post="";$p="";
				echo"<tr class=rowcontent id='trsns_".$no."'>
						<td style='text-align:center'>".$no."</td>
						<td id='thn_".$no."' ".$b." align=center>".$tahun."</td>
						<td hidden id='sms_".$no."' ".$a.">".$param['sms']."</td>
						<td hidden id='sms2_".$no."' ".$a.">".$param['sms2']."</td>
						<td id='sts_".$no."' ".$d." align=center>".$sts."</td>
						<td id='div_".$no."' ".$e." align=center>".$div."</td>
						<td hidden id='blok_".$no."' ".$a.">".$blok."</td>
						<td>".$nmorg[$blok]."</td>
						<td id='tt_".$no."' style='text-align:center'>".$tt."</td>
						<td id='luas_".$no."' style='text-align:right'>".$luas."</td>
						<td id='pokok_".$no."' style='text-align:right'>".$pokok."</td>
						<td id='bln_".$no."' style='text-align:center'>".$bulan."</td>
					";
					echo"<td id='jjg_".$no."' style='text-align:right'>".@number_format($jjg)."</td>";
					echo"<td id='kg_".$no."' style='text-align:right'>".@number_format($kg,2)."</td>";
					echo"<td id='bjr_".$no."' style='text-align:right'>".@number_format($bjr,2)."</td>";
					echo"<td id='kerapatan_".$no."' style='text-align:right'>".@number_format($kerapatan,2)."</td>";
					echo"<td id='semester_".$no."' style='text-align:center'>".$smsall."</td>";

					// for ($x=intval($param['sms']); $x <= intval($param['sms2']); $x++) { 
					// 	echo "bulan=".$x."<br>";
					// 	echo"<input id=temperr hidden value=".$err."id='bln_".$no."_".$x."' style='text-align:right'>".$x."</td>";
					// }
					// for ($i=10; $i <= $akhir ; $i = $i +3) {
					// 	echo"<td hidden id='bln_".$no."_".$i."' style='text-align:right'>".$i."</td>";
					// 	// echo"<td id='jjg_".$no."_".$i."' style='text-align:right'>".@number_format($jjg[$i])."</td>";
					// 	echo"<td id='kg_".$no."_".$i."' style='text-align:right'>".@number_format($kg[$i],2)."</td>";
					// 	// echo"<td id='bjr_".$no."_".$i."' style='text-align:right'>".@number_format($bjr[$i],2)."</td>";
					// }
					// for ($i=11; $i <= $akhir ; $i = $i +3) {
					// 	echo"<td hidden id='bln_".$no."_".$i."' style='text-align:right'>".$i."</td>";
					// 	// echo"<td id='jjg_".$no."_".$i."' style='text-align:right'>".@number_format($jjg[$i])."</td>";
					// 	echo"<td id='kg_".$no."_".$i."' style='text-align:right'>".@number_format($kg[$i],2)."</td>";
					// 	// echo"<td id='bjr_".$no."_".$i."' style='text-align:right'>".@number_format($bjr[$i],2)."</td>";
					// }
					
					if($dataerr>0){
						$m="Salah"; $o=" style=background-color:red ";
					}else{
						$m="OK"; $o="";
					}	
					echo"<td ".$o.">".$m."</td></tr>";
			}
			echo"<input id=temperr hidden value=".$err.">";
			echo"</tbody></table></div>";
		break;

		// case 'sensus':
		// 	$nopoisi = 0;
		// 	foreach($x as $key => $arr) {
		// 		if ($key == 0) {
		// 			continue;
		// 		} else {
		// 			foreach($arr as $ids => $rinc) {
		// 				if ($nopoisi != 1) {
		// 					$nopoisi = 1;
		// 				}
		// 			}
		// 		}
		// 	}
		// 	$date1 = $param['tahun']."-".$param['sms']."-"."01";
		// 	$date2 = $param['tahun']."-".$param['sms2']."-"."01";
		// 	$diff = abs(strtotime($date2)-strtotime($date1));
		// 	$years = floor($diff / (365*60*60*24));
		// 	$diffmonth = (floor(($diff - $years * 365*60*60*24) / (30*60*60*24)) + 1);

		// 	$jmlhRow = count($x);

		// 	$theme=$_SESSION['theme'];
		// 	if($theme=='skyblue' || $theme==''){$gen='generic.css';}else if($theme=='red'){$gen='genericRed.css';  }else{$gen='genericGray.css';} 
		// 	echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 
		// 	echo"<div style='border: 1px solid orange; width: 150px; position: fixed; right: 20px; top: 65px; color: rgb(255, 0, 0); font-family: Tahoma; font-size: 13px; font-weight: bolder; text-align: center; background-color: rgb(255, 255, 255); z-index: 10000; display: none;' id='progress'>
		// 	Please wait.....! <br><img src='images/progress.gif'></div>";
			
		// 	echo"<div style=clear:both></div><br>";
		// 	echo"<script language=javascript src=js/kebun_sensus.js></script>";
		// 	echo"<script language=javascript src=js/generic.js></script>";
		// 	echo"<script language=javascript src=js/generic.min.js></script>";
		// 	// echo"<button class=mybutton id='btnupload2' onclick=simpanall('".($jmlhRow-2)."') id=btnupload>".$_SESSION['lang']['startUpload']."</button><p>";
		// 	echo"<button class=mybutton id='btnupload2' onclick=\"\" id=btnupload>".$_SESSION['lang']['startUpload']."</button><p>";
			
		// 	// if($param['cawu']=='1'){
		// 	// 	$bulan=array('01'=>$param['tahun'].'-01','02'=>$param['tahun'].'-02','03'=>$param['tahun'].'-03','04'=>$param['tahun'].'-04');
		// 	// }elseif($param['cawu']=='2'){
		// 	// 	$bulan=array('05'=>$param['tahun'].'-05','06'=>$param['tahun'].'-06','07'=>$param['tahun'].'-07','08'=>$param['tahun'].'-08');
		// 	// }elseif($param['cawu']=='3'){
		// 	// 	$bulan=array('09'=>$param['tahun'].'-09','10'=>$param['tahun'].'-10','11'=>$param['tahun'].'-11','12'=>$param['tahun'].'-12');
		// 	// }
			
			
			
			
		// 	$rows="rowspan=2";	
		// 	echo"<div class='table-scroll'><table class=sortable cellspacing=1 border=0><thead>";
		// 		echo"<th align=center ".$rows." width=20px>No</th>
		// 			<th align=center ".$rows.">".$_SESSION['lang']['tahun']."</th>
		// 			<th align=center ".$rows.">".$_SESSION['lang']['status']."</th>
		// 			<th align=center ".$rows.">".$_SESSION['lang']['divisi']."</th>
		// 			<th align=center ".$rows.">".$_SESSION['lang']['blok']."</th>
		// 			<th align=center ".$rows." width=50px>".$_SESSION['lang']['tahuntanam'] . "</th>
		// 			<th align=center ".$rows.">".$_SESSION['lang']['luas'] . "</th>
		// 			<th align=center ".$rows.">".$_SESSION['lang']['pokok'] . "</th>";
		// 		for ($i=$param['sms']; $i <= $param['sms2'] ; $i++){
		// 			if ($i < 10) {
		// 				echo"<td align=center colspan=3>".$param['tahun']."-0".$i."</td>";
		// 			} else {
		// 				echo"<td align=center colspan=3>".$param['tahun']."-".$i."</td>";
		// 			}			
		// 			// echo"<th align=center colspan=3>".$i."</th>";
		// 		}	
		// 		echo"<th align=center ".$rows.">Status</th>
		// 		</tr>";
		// 		echo"</tr>";
		// 		echo"<tr class=rowheader  style=background-color:grey>";
		// 		for ($i=$param['sms']; $i <= $param['sms2'] ; $i++){			
		// 			echo"<th align=center>Jjg</th>";
		// 			echo"<th align=center>Kg</th>";
		// 			echo"<th align=center>BJR</th>";
		// 		}
		// 		echo"</tr>";
				
		// 	echo"</thead><tbody>";

		// 	// $date1 = $param['tahun']."-".$param['sms']."-"."01";
		// 	// $date2 = $param['tahun']."-".$param['sms2']."-"."01";
		// 	// $diff = abs(strtotime($date2)-strtotime($date1));
		// 	// $years = floor($diff / (365*60*60*24));
		// 	// $diffmonth = (floor(($diff - $years * 365*60*60*24) / (30*60*60*24)) + 1);
		// 	// echo ($diffmonth*3);

		// 	$no='0'; $err=0;
		// 	for ($row = 2; $row < $jmlhRow; $row++) {
		// 		$tahun= $x[$row][1];
		// 		$sts  = $x[$row][2];
		// 		$div  = $x[$row][3];
		// 		$blok = $x[$row][4];
		// 		$tt   = $x[$row][5];
		// 		$luas = $x[$row][6];
		// 		$pokok= $x[$row][7];
		// 		// $cawu = $x[$row][2];
		// 		// foreach($bulan as $prd => $bln){
		// 		// 	if($prd=='01' or $prd=='05' or $prd=='09'){
		// 		// 		$jjg[$prd]=$x[$row][9];
		// 		// 		$kg[$prd]=$x[$row][10];
		// 		// 	}elseif($prd=='02' or $prd=='06' or $prd=='10'){
		// 		// 		$jjg[$prd]=$x[$row][11];
		// 		// 		$kg[$prd]=$x[$row][12];
		// 		// 	}elseif($prd=='03' or $prd=='07' or $prd=='11'){
		// 		// 		$jjg[$prd]=$x[$row][13];
		// 		// 		$kg[$prd]=$x[$row][14];
		// 		// 	}else{					
		// 		// 		$jjg[$prd]=$x[$row][15];
		// 		// 		$kg[$prd]=$x[$row][16];
		// 		// 	}
		// 		// }
		// 		$akhir = 8+($diffmonth*3);
		// 		for ($i=8; $i <= $akhir ; $i++) {
		// 			$j = $i +1;
		// 			// $nil[$j]=$x[$row][$i];
		// 			$jjg[$j]=$x[$row][$i];
		// 			$kg[$j]=$x[$row][$i];
		// 			$bjr[$j]=$x[$row][$i];
		// 		}

		// 		// echo "<pre>";
		// 		// print_r($nil);
		// 		// echo "</pre>";
				
		// 		$no++;
		// 		$dataerr=0;
				
		// 		if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
		// 		$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0'  and kodeorg='".$blok."'";
		// 		}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
		// 			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and substr(kodeorg,1,4) in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') and kodeorg='".$blok."'";
		// 		}else{
		// 			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'  and kodeorg='".$blok."'";
		// 		}
				
		// 		$a='';
		// 		if(count(fetchdata($str))==0){
		// 			$a=" style=background-color:red;align:center; ";$dataerr+=1;
		// 		}
				
		// 		$stsblk=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blok."'");
		// 		if($stsblk[$blok]==""){
		// 			$a=" style=background-color:red;align:center; ";$dataerr+=1;
		// 		}
		// 		$b='';
		// 		if($tahun!=$param['tahun']){
		// 			$b=" style=background-color:red;align:center; ";$dataerr+=1;
		// 		}
		// 		// $c='';
		// 		// if($cawu!=$param['cawu']){
		// 		// 	$c=" style=background-color:red;align:center; ";$dataerr+=1;
		// 		// }
		// 		$d='';
		// 		if($sts!=$param['sts']){
		// 			$d=" style=background-color:red;align:center; ";$dataerr+=1;
		// 		}
		// 		$e='';
		// 		if($div!=$param['div']){
		// 			$e=" style=background-color:red;align:center; ";$dataerr+=1;
		// 		}
		// 		$err+=$dataerr;
				
		// 		// $jlhbrs=0;$post="";$p="";
		// 		echo"<tr class=rowcontent id='trsns_".$no."'>
		// 				<td style='text-align:center'>".$no."</td>
		// 				<td id='thn_".$no."' ".$b." align=center>".$tahun."</td>
		// 				<td hidden id='sms_".$no."' ".$a.">".$param['sms']."</td>
		// 				<td hidden id='sms2_".$no."' ".$a.">".$param['sms2']."</td>
		// 				<td id='sts_".$no."' ".$d." align=center>".$sts."</td>
		// 				<td id='div_".$no."' ".$e." align=center>".$div."</td>
		// 				<td hidden id='blok_".$no."' ".$a.">".$blok."</td>
		// 				<td>".$nmorg[$blok]."</td>
		// 				<td id='tt_".$no."' style='text-align:center'>".$tt."</td>
		// 				<td id='luas_".$no."' style='text-align:right'>".$luas."</td>
		// 				<td id='pokok_".$no."' style='text-align:right'>".$pokok."</td>
		// 			";

		// 			$k = 10;
		// 			$b = 11;
		// 			for ($i=9; $i <= $akhir ; $i = $i +3) {
		// 				for ($y=$param['sms']; $y <= $param['sms2'] ; $y++) { 
		// 					echo"<td hidden id='bln_".$no."_".$i."_".$y."'>".$y."</td>";
		// 				}
		// 					echo"<td id='jjg_".$no."_".$i."' style='text-align:right'>".@number_format($jjg[$i])."</td>";
		// 					echo"<td id='kg_".$no."_".$k."' style='text-align:right'>".@number_format($kg[$k],2)."</td>";
		// 					echo"<td id='bjr_".$no."_".$b."' style='text-align:right'>".@number_format($bjr[$b],2)."</td>";
		// 					$k=$k+3;
		// 					$b=$b+3;
		// 			}
		// 			// for ($x=intval($param['sms']); $x <= intval($param['sms2']); $x++) { 
		// 			// 	echo "bulan=".$x."<br>";
		// 			// 	echo"<input id=temperr hidden value=".$err."id='bln_".$no."_".$x."' style='text-align:right'>".$x."</td>";
		// 			// }
		// 			// for ($i=10; $i <= $akhir ; $i = $i +3) {
		// 			// 	echo"<td hidden id='bln_".$no."_".$i."' style='text-align:right'>".$i."</td>";
		// 			// 	// echo"<td id='jjg_".$no."_".$i."' style='text-align:right'>".@number_format($jjg[$i])."</td>";
		// 			// 	echo"<td id='kg_".$no."_".$i."' style='text-align:right'>".@number_format($kg[$i],2)."</td>";
		// 			// 	// echo"<td id='bjr_".$no."_".$i."' style='text-align:right'>".@number_format($bjr[$i],2)."</td>";
		// 			// }
		// 			// for ($i=11; $i <= $akhir ; $i = $i +3) {
		// 			// 	echo"<td hidden id='bln_".$no."_".$i."' style='text-align:right'>".$i."</td>";
		// 			// 	// echo"<td id='jjg_".$no."_".$i."' style='text-align:right'>".@number_format($jjg[$i])."</td>";
		// 			// 	echo"<td id='kg_".$no."_".$i."' style='text-align:right'>".@number_format($kg[$i],2)."</td>";
		// 			// 	// echo"<td id='bjr_".$no."_".$i."' style='text-align:right'>".@number_format($bjr[$i],2)."</td>";
		// 			// }
					
		// 			if($dataerr>0){
		// 				$m="Salah"; $o=" style=background-color:red ";
		// 			}else{
		// 				$m="OK"; $o="";
		// 			}	
		// 			echo"<td ".$o.">".$m."</td></tr>";
		// 	}
		// 	echo"<input id=temperr hidden value=".$err.">";
		// 	echo"</tbody></table></div>";
		// break;
	}
}
?>
