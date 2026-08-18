<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('config/connection.php');

$pemisah               = checkPostGet('pemisah', '');
$jenisdata             = checkPostGet('jenisdata', '');
$intltiket             = checkPostGet('intltiket', '');
$kodeorg             = checkPostGet('kodeorgupload', '');
$periode             = checkPostGet('periodeupload', '');
$notransaksi             = checkPostGet('notransaksiupload', '');

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
    global $kodeorg;
    global $periode;
    global $notransaksi;
    global $owlPDO;

	$jlhbaris=count($x)-1;
    #baris pertama adalah header;
    foreach($x[0] as $val){
		$header[]=trim($val);
	}

switch ($jenisdata) {
	case 'PEMEL':
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
		
		
		
		echo"<input type='hidden' id='kodeorgup' value='".$kodeorg."'>";
		echo"<input type='hidden' id='periodeup' value='".$periode."'>";
		echo"<input type='hidden' id='notransup' value='".$notransaksi."'>";
		echo"<div style=clear:both></div><br>";
		echo"<script language=javascript src=js/kebun_rkbx.js></script>";
		echo"<script language=javascript src=js/generic.js></script>";
		echo"<script language=javascript src=js/generic.min.js></script>";
		echo"<button class=mybutton id='btnupload2' onclick=uploaddataall('".($jmlhRow-1)."') id=btnupload>".$_SESSION['lang']['startUpload']."</button><p>";

		$rows="rowspan=2";	
		echo"<table class=sortable cellspacing=1 border=0><thead>";
			echo"<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['kodekegiatan']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['namakegiatan']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['blok']."</td>
				<td align=center ".$rows.">".$_SESSION['lang']['luas'] . "</td>
				<td align=center colspan=4 >Tenaga Kerja</td>
				<td align=center ".$rows." >Premi</td>
				<td align=center colspan=3 >Borongan</td>
				<td align=center colspan=4 >".$_SESSION['lang']['material']." 1</td>
				<td align=center colspan=4 >".$_SESSION['lang']['material']." 2</td>
				<td align=center colspan=4 >".$_SESSION['lang']['material']." 3</td>
				<td align=center colspan=4 >".$_SESSION['lang']['material']." 4</td>
				<td align=center ".$rows.">Status</td>
			</tr>
			<tr>
				<td align=center >KBL</td>
				<td align=center >KHT</td>
				<td align=center >KHL</td>
				<td align=center>".$_SESSION['lang']['rupiah'] . "</td>
				<td align=center>Luas</td>
				<td align=center>Rp/Ha</td>
				<td align=center>Rupiah</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>Jumlah</td>
				<td align=center>Rupiah</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>Jumlah</td>
				<td align=center>Rupiah</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>Jumlah</td>
				<td align=center>Rupiah</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>Jumlah</td>
				<td align=center>Rupiah</td>
			</tr>
			</thead>
			<tbody>";
		
		$key = 1;
		$no='';
		$nmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		for ($row = 1; $row < $jmlhRow; $row++) {
			$kodekegiatan = $x[$row][0];
			$blok         = $x[$row][1];
			$luas         = $x[$row][2];
			$kbl          = $x[$row][3];
			$kht          = $x[$row][4];
			$khl          = $x[$row][5];
			$ttlrphk      = $x[$row][6];
			$premi        = $x[$row][7];
			$luasbor      = $x[$row][8];
			$rupiahbor     = $x[$row][9];
			$mat1         = $x[$row][10];
			$jlhmat1      = $x[$row][11];
			$mat2         = $x[$row][12];
			$jlhmat2      = $x[$row][13];
			$mat3         = $x[$row][14];
			$jlhmat3      = $x[$row][15];
			$mat4         = $x[$row][16];
			$jlhmat4      = $x[$row][17];
			
			$no++;
			$dataerr=0;
			$hargarata1=$hargarata2=$hargarata3=$hargarata4='0';
			$str = "select * from ".$dbname.".log_5saldobulanan where kodebarang='".$mat1."' and kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."') and hargarata!=0 order by periode desc limit 1"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$hargarata1=$bar['hargarata'];
			
			$str = "select * from ".$dbname.".log_5saldobulanan where kodebarang='".$mat2."' and kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."') and hargarata!=0 order by periode desc limit 1"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$hargarata2=$bar['hargarata'];
			
			$str = "select * from ".$dbname.".log_5saldobulanan where kodebarang='".$mat3."' and kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."') and hargarata!=0 order by periode desc limit 1"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$hargarata3=$bar['hargarata'];
			
			$str = "select * from ".$dbname.".log_5saldobulanan where kodebarang='".$mat4."' and kodeorg in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."') and hargarata!=0 order by periode desc limit 1"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$hargarata4=$bar['hargarata'];
			
			
			$OptNama=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kodekegiatan."'");
			$Optstsblok=makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$blok."'");
			
			$ckeg='';if($OptNama[$kodekegiatan]==''){$ckeg=" style=background-color:red ";$dataerr=1;}
			$ckeg2='';if(substr($kodekegiatan,0,3)!='621' and substr($kodekegiatan,0,3)!='126' and substr($kodekegiatan,0,3)!='611' and substr($kodekegiatan,0,3)!='128'){$ckeg2=" style=background-color:red ";$dataerr=1;}
			
			$arrsts=array('621'=>'TM','126'=>'TBM','611'=>'TM','128'=>'TB');
			$ckegb='';
			if($arrsts[substr($kodekegiatan,0,3)] != $Optstsblok[$blok]){
				$ckegb=" style=background-color:red ";$dataerr=1;
			}
			
			$cblok='';
			if($Optstsblok[$blok]==""){
				$cblok=" style=background-color:red ";$dataerr=1;
			}
			$n='';
			if($ttlrphk==0 and ($kbl!=0 or $kht!=0 or $khl!=0)){
				$n=" style=background-color:red ";
				$dataerr=1;
			}
			$y='';
			if(($ttlrphk+$premi+$rupiahbor)==0){
				$dataerr=1;
				$y=" style=background-color:red ";
			}
			
			echo"<tr class=rowcontent id='trpemel_".$no."'>
					<td style='text-align:center'>".$no."</td>
					<td id='tdkegiatan_".$no."' style='text-align:center'>".$kodekegiatan."</td>
					<td ".$ckeg." ".$ckeg2." ".$ckegb.">".$OptNama[$kodekegiatan]."</td>
					<td id='tdblok_".$no."' ".$cblok." ".$ckegb.">".$blok."</td>
					<td id='tdluas_".$no."' style='text-align:right'>".$luas."</td>
					<td id='tdkbl_".$no."' style='text-align:right'>".$kbl."</td>
					<td id='tdkht_".$no."' style='text-align:right'>".$kht."</td>
					<td id='tdkhl_".$no."' style='text-align:right'>".$khl."</td>
					<td id='tdttlrphk_".$no."' ".$n." ".$y." align=right>".@number_format($ttlrphk)."</td>
					<td id='tdpremi_".$no."' align=right ".$y.">".$premi."</td>
					<td id='tdluabor_".$no."' style='text-align:right'>".$luasbor."</td>
					<td style='text-align:right'>".@number_format($rupiahbor/$luasbor,2)."</td>
					<td id='tdrupiahbor_".$no."' align=right ".$y.">".$rupiahbor."</td>";
				$clr1='';
				if(($jlhmat1>0 and $hargarata1=='0') or ($mat1!='' and $nmbarang[$mat1]=='')){$clr1=" style=background-color:red "; $dataerr=1;}
				echo"<td id='tdmati_".$no."' align=right ".$clr1.">".$mat1."</td>
					<td ".$clr1.">".@$nmbarang[$mat1]."</td>
					<td id='tdjlhmati_".$no."' align=right ".$clr1.">".$jlhmat1."</td>
					<td id='tdrpmati_".$no."' align=right ".$clr1.">".@number_format($jlhmat1*$hargarata1)."</td>";
				$clr2='';
				if(($jlhmat2>0 and $hargarata2=='0') or ($mat2!='' and $nmbarang[$mat2]=='')){$clr2=" style=background-color:red "; $dataerr=1;}
				echo"<td id='tdmatii_".$no."' align=right ".$clr2.">".$mat2."</td>
					<td ".$clr2.">".@$nmbarang[$mat2]."</td>
					<td id='tdjlhmatii_".$no."' align=right ".$clr2.">".$jlhmat2."</td>
					<td id='tdrpmatii_".$no."' align=right ".$clr2.">".@number_format($jlhmat2*$hargarata2)."</td>";
				$clr3='';
				if(($jlhmat3>0 and $hargarata3=='0') or ($mat3!='' and $nmbarang[$mat3]=='')){$clr3=" style=background-color:red "; $dataerr=1;}
				echo"<td id='tdmatiii_".$no."' align=right ".$clr3.">".$mat3."</td>
					<td ".$clr3.">".@$nmbarang[$mat3]."</td>
					<td id='tdjlhmatiii_".$no."' align=right ".$clr3.">".$jlhmat3."</td>
					<td id='tdrpmatiii_".$no."' align=right ".$clr3.">".@number_format($jlhmat3*$hargarata3)."</td>";
				$clr4='';
				if(($jlhmat4>0 and $hargarata4=='0') or ($mat4!='' and $nmbarang[$mat4]=='')){$clr4=" style=background-color:red "; $dataerr=1;}
				echo"<td id='tdmativ_".$no."' align=right ".$clr4.">".$mat4."</td>
					<td ".$clr4.">".@$nmbarang[$mat4]."</td>
					<td id='tdjlhmativ_".$no."' align=right ".$clr4.">".$jlhmat4."</td>
					<td id='tdrpmativ_".$no."' align=right ".$clr4.">".@number_format($jlhmat4*$hargarata4)."</td>";
					
				if($dataerr==1){
					$m="Salah"; $o=" style=background-color:red ";
				}else{
					$m="OK"; $o="";
				}	
				echo"<td ".$o.">".$m."</td>
					<td style=display:none id='ket".$no."' align=right ".$clr4.">".$dataerr."</td>
				</tr>";
			
		}
		echo"</tbody></table>";
		break;
	}
}
?>
