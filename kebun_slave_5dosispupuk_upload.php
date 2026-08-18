<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('config/connection.php');

$pemisah    = checkPostGet('pemisah', '');
$jenisdata  = checkPostGet('jenisdata', '');
$intltiket  = checkPostGet('intltiket', '');
$kodeorg    = checkPostGet('kodeorgupload', '');
$periode    = checkPostGet('periodeupload', '');
$notransaksi= checkPostGet('notransaksiupload', '');

$path='tempExcel';

if(is_dir($path)){
	writeFile($path,$pemisah);
	//chmod($path, 0777);
}else{
	if(mkdir($path)){
		writeFile($path,$pemisah);
		// chmod($path, 0777);
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

	$jlhbaris=count($x)-1;
    #baris pertama adalah header;
    foreach($x[0] as $val){
		$header[]=trim($val);
	}

switch ($jenisdata) {
	case 'REKPUPUK':
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
		$jmlhRow = count($x);

		$theme=$_SESSION['theme'];
		if($theme=='skyblue' || $theme==''){$gen='generic.css';}else if($theme=='red'){$gen='genericRed.css';  }else{$gen='genericGray.css';} 
		echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 
		//echo"<input type='hidden' id='progress'>";
		echo"<div style='border: 1px solid orange; width: 150px; position: fixed; right: 20px; top: 65px; color: rgb(255, 0, 0); font-family: Tahoma; font-size: 13px; font-weight: bolder; text-align: center; background-color: rgb(255, 255, 255); z-index: 10000; display: none;' id='progress'>
		Please wait.....! <br><img src='images/progress.gif'></div>";
		
		echo"<div style=clear:both></div><br>";
		echo"<script language=javascript src=js/kebun_5dosispupuk.js></script>";
		echo"<script language=javascript src=js/generic.js></script>";
		echo"<script language=javascript src=js/generic.min.js></script>";
		echo"<button class=mybutton id='btnupload2' onclick=uploaddataall('".($jmlhRow-1)."') id=btnupload>".$_SESSION['lang']['startUpload']."</button><p>";

		$rows="rowspan=1";	
		echo"<table class=sortable cellspacing=1 border=0><thead>";
			echo"<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok']."</td>
				<td align=center ".$rows." width=50px>".$_SESSION['lang']['tahuntanam'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['luas'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['pokok'] . "</td>
				<td align=center ".$rows.">Jenis Tanah</td>
				<td align=center ".$rows." colspan=2>".$_SESSION['lang']['pupuk'] . "</td>
				<td align=center ".$rows." colspan=2>Aplikasi Ke</td>
				<td align=center ".$rows.">".$_SESSION['lang']['dosis'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jumlah'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['periode'] . "</td>
				<td align=center ".$rows.">".$_SESSION['lang']['posting'] . "</td>
				<td align=center ".$rows.">Status</td>
			</tr>
			</thead>
			<tbody>";
		
		$key = 1;
		$no='0';
		for ($row = 1; $row < $jmlhRow; $row++) {
			$blok      = $x[$row][0];
			$tt        = $x[$row][1];
			$luas      = $x[$row][2];
			$pokok     = $x[$row][3];
			$status    = $x[$row][4];
			$jenistanah= $x[$row][5];
			$pupuk     = $x[$row][6];
			$aplikasi  = $x[$row][7];
			$dosis     = $x[$row][8];
			$jumlah    = $x[$row][9];
			$periode   = $x[$row][10];
			
			$no++;
			$dataerr=0;
			$Optstsblok=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blok."'");
			$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$pupuk."'");
			$cblok='';
			if($Optstsblok[$blok]==""){
				$cblok=" style=background-color:red ";$dataerr=1;
			}
			$cppk="";
			if($optppk[$pupuk]==""){
				$cppk=" style=background-color:red ";$dataerr=1;
			}
			
			$arrapl=array('1'=>'Satu','2'=>'Dua','3'=>'Tiga','4'=>'Empat','5'=>'Lima','6'=>'Enam','7'=>'Tujuh','8'=>'Delapan','9'=>'Sembilan','10'=>'Sepuluh','11'=>'Sebelas','12'=>'Dua Belas','1e'=>'Extra Satu','2e'=>'Extra Dua','3e'=>'Extra Tiga');
			$capl="";
			if($arrapl[$aplikasi]==""){
				$capl=" style=background-color:red ";$dataerr=1;
			}
			
			$jlhbrs=0;$post="";$p="";
			$sql = "select * from " . $dbname . ".kebun_rekomendasipupuk where blok='" . $blok. "' and kodebarang='" . $pupuk. "' and aplikasi='" . $aplikasi. "' and periodepemupukan='".trim($periode)."' and posting='1'";
			$jlhbrs = count(fetchdata($sql));
			if($jlhbrs>0){
				$post=" style=background-color:red ";$dataerr=1; $p="Data sudah diposting.";
			}
			
			echo"<tr class=rowcontent id='trpemel_".$no."'>
					<td hidden id='tdkdorg_".$no."'>".substr($blok,0,4)."</td>
					<td hidden id='tddivisi_".$no."'>".substr($blok,0,6)."</td>
					<td style='text-align:center'>".$no."</td>
					<td id='tdblok_".$no."' ".$cblok.">".$blok."</td>
					<td id='tdtt_".$no."' style='text-align:center'>".$tt."</td>
					<td id='tdluas_".$no."' style='text-align:right'>".$luas."</td>
					<td id='tdpokok_".$no."' style='text-align:right'>".$pokok."</td>
					<td id='tdtanah_".$no."' style='text-align:left'>".$jenistanah."</td>
					<td id='tdpupuk_".$no."' style='text-align:center' ".$cppk.">".$pupuk."</td>
					<td style='text-align:right'  ".$cppk.">".$optppk[$pupuk]."</td>
					<td id='tdapl_".$no."' style='text-align:center' ".$capl.">".$aplikasi."</td>
					<td style='text-align:center' ".$capl.">".$arrapl[$aplikasi]."</td>
					<td id='tddosis_".$no."' style='text-align:right'>".$dosis."</td>
					<td id='tdjlh_".$no."' style='text-align:right'>".$jumlah."</td>
					<td id='tdperiode_".$no."' style='text-align:center'>".$periode."</td>
					<td align=center' ".$post.">".$p."</td>				
				";
				
				if($dataerr==1){
					$m="Salah"; $o=" style=background-color:red ";
				}else{
					$m="OK"; $o="";
				}	
				echo"<td id='info_".$no."' ".$o.">".$m."</td></tr>";
			
		}
		echo"</tbody></table>";
		break;
	}
}
?>
