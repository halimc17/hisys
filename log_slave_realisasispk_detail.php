<?
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';

		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}

include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zGrid.php');
#include_once('lib/rGrid.php');
include_once('lib/formTable.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

// echo"<pre>";
// print_r($_POST);
// print_r($_GET);
// exit("error");

$proses   = checkPostGet('proses', '');
$param = $_POST;
$optstatus=array("0"=>"Belum Diajukan","1"=>"Disetujui","2"=>"Dikoreksi","3"=>"Ditolak","9"=>"Proses Pengajuan");
switch($proses) {
    case 'showDetail':
		# Options
		#khusus jika project
		//if(substr($param['divisi'],0,2)=='AK' or substr($param['divisi'],0,2)=='PB'){
		if(empty($param['divisi']) or $param['divisi']=='PROJECT') {
			$str="select * from ".$dbname.".log_spkht where notransaksi='".$param['notransaksi']."'";
			$res=fetchData($str);
			$nopengajuan = $res[0]['nopengajuan'];
			
			$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$nopengajuan."'";
			$res=fetchData($str);
			$pengajuandivisi = $res[0]['divisi'];
			
			$optCapex = makeOption($dbname,'project','kode,kodecapex',"kode='".$pengajuandivisi."'");
			// $optProject = makeOption($dbname,'project','kode,nama',"kode='".$row['kodeblok']."'");
			$kodecapex = $optCapex[$pengajuandivisi];
			
			if($kodecapex==''){
				$optAct = makeOption($dbname,'project_dt','kegiatan,namakegiatan',"kodeproject='".$pengajuandivisi."'");
			}
			else{
				$optAct = makeOption($dbname,'spl_capexbangunandt','kegiatan,namakegiatan',"kodeproject='".$kodecapex."'");
			}
			
			$optBlok = makeOption($dbname,'project','kode,nama',"kodeorg='".$param['kebun']."' and posting=0");
			// $optPrj = array();
			// foreach($optBlok as $key=>$row) {
				// $optPrj[] = $key;
			// }
			// $str="select kegiatan,namakegiatan from ".$dbname.".project_dt
				// where kodeproject in ('".implode("','",$optPrj)."')";
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_OBJ);
			// while($bar=$res->fetch()){
				// $optAct[$bar->kegiatan]=$bar->namakegiatan;
			// }               
		} else if($param['divisi']=='S'){
			$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".substr($param['kebun'],0,4)."'");
			$optBlok = makeOption($dbname,'sdm_perumahanht','norumah,keterangan',"kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[substr($param['kebun'],0,4)]."')");
			$optAct = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kelompok = 'SPL'");
		} else if($param['divisi']=='P') {
			//pabrikasi
			$optBlok = makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi','',2);
			$optAct = makeOption($dbname,'pabrikasi_5masterdt','tahapan,tahapan','',2);
		}
		else {
			$optBlok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
							  "kodeorganisasi like '".substr($param['divisi'],0,4)."%'");
            $optAct = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
        }    
		
		
		$whr = "";
		if($param['divisi'] == 'S'){
			$whr = " ";
		}else{
			$whr = " and kodeblok like '".substr($param['divisi'],0,4)."%'";
		}
		# Get Data
		$where = "notransaksi='".$param['notransaksi']."' ".$whr."";
		$cols = "kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp";
		$query = selectQuery($dbname,'log_spkdt',$cols,$where);
		$data = fetchData($query);
		$dataShow = array();
		// echo"<pre>";print_r($query);echo"</pre>";exit;
		foreach($data as $key=>$row) {
			$dataShow[$key]['kodeblok'] = $optBlok[$row['kodeblok']];
			@$dataShow[$key]['kodekegiatan'] = $optAct[$row['kodekegiatan']];
			$dataShow[$key]['hk'] = $row['hk'];
			$dataShow[$key]['hasilkerjajumlah'] = $row['hasilkerjajumlah'];
			$dataShow[$key]['satuan'] = $row['satuan'];
			$dataShow[$key]['jumlahrp'] = $row['jumlahrp'];
		}
		
		#== Grid
		$headName = array(
			$_SESSION['lang']['subunit'],
			$_SESSION['lang']['kodekegiatan'],
			$_SESSION['lang']['hk'],
			$_SESSION['lang']['hasilkerjajumlah'],
			$_SESSION['lang']['satuan'],
			$_SESSION['lang']['jumlahrp'],
		);
		
		# Grid Header
		$grid = "<table class='sortable'><thead><tr class='rowheader'>";
		foreach($headName as $head) {
			$grid .= "<td>".$head."</td>";
		}
		$grid .= "</tr></thead>";

		# Grid Content
		$grid .= "<tbody>";
		if(empty($data)) {
			$grid .= "<tr class='rowcontent'><td colspan='10'>Data Empty</td></tr>";
		} else {
			foreach($dataShow as $key=>$row) {
				$grid .= "<tr class='rowcontent' onclick=\"manageDetail(".$key.")\" style='cursor:pointer'>";
				foreach($row as $head=>$cont) {
					$grid .= "<td id='".$head."_".$key."' ";
					if(isset($data[$key][$head])) {
						$grid .= "value='".$data[$key][$head]."' ";
					} else {
						$grid .= "value='' ";
					}
					if($head=='kodeblok' or $head=='kodekegiatan') {
							$grid .= "align='left'";
					} else {
							$grid .= "align='right'";
					}
					if($head=='jumlahrp') {
						$grid .= ">".number_format($cont)."</td>";
					} else {
						$grid .= ">".$cont."</td>";
					}
                }
                $grid .= "</tr>";
                $grid .= "<tr><td colspan='6'><div id='detail_".$key."'></div></td></tr>";
            }
        }
        $grid .= "</tbody>";
        $grid .= "</table>";
		
		#== Display View
		# Draw Tab
		echo "<fieldset><legend><b>Detail</b></legend>";
		echo $grid;
		echo "</fieldset>";
		break;
    case 'manageDetail':
		# Get Data
		$cols = 'a.*, b.revisihasilkerja, b.revisihk, b.revisijumlah';
		$where = "a.notransaksi='".$param['notransaksi']
			. "' and a.kodekegiatan='".$param['kodekegiatan']
			. "' and a.blokspkdt='".$param['kodeblok']."'";
		$query = "SELECT ".$cols." FROM ".$dbname.".log_baspk a LEFT JOIN ".
			$dbname.".log_baspk_rev b ON
			a.notransaksi = b.notransaksi AND
			a.kodeblok = b.kodeblok AND
			a.kodekegiatan = b.kodekegiatan AND
			a.tanggal = b.tanggal AND
			a.blokspkdt = b.blokspkdt AND
			a.kodesegment = b.kodesegment WHERE ".$where;

		##cek jika kegiatanya termasuk yg perhitungan berbeda
		$strpa="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='KLPLUS'and nilai like '%".$param['kodekegiatan']."%' ";
        $barpa=fetchData($strpa);
        $jmlkgplus=count($barpa);
        
        $resDetail = fetchData($query);
		foreach($resDetail as $key=>$row) {
			$resDetail[$key]['jumlahrealisasi'] = number_format($row['jumlahrealisasi']);
		}
        # Options
		
		if(strlen($param['divisi'])==4){
			$optBlok[$param['divisi']]=$param['divisi'];
		} 
		
        if($_SESSION['empl']['tipelokasitugas']!='KEBUN') {
            $str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM ".$dbname.".organisasi b 
				WHERE b.kodeorganisasi like '".$param['kodeblok']."%'";
			$res_blok=$owlPDO->query($str_blok) or die(print " Gagal: ".PDOException::getMessage());
			$res_blok->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res_blok->fetch()) {
                $optBlok[$bar->kodeorg]=$bar->namaorg;
            }  
        } else {
			$str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM ".$dbname.".setup_blok a LEFT JOIN ".$dbname.".organisasi b 
				ON a.kodeorg = b.kodeorganisasi 
				WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '".substr($param['divisi'],0,6)."%' 
				and length(b.kodeorganisasi)>6";
			$res_blok=$owlPDO->query($str_blok) or die(print " Gagal: ".PDOException::getMessage());
			$res_blok->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res_blok->fetch()) {
                $optBlok[$bar->kodeorg]=$bar->namaorg;
            }
        }
		
		
			
		#khusus jika project
		//if(substr($param['divisi'],0,2)=='AK' or substr($param['divisi'],0,2)=='PB') {
		if(empty($param['divisi'])) {
			$optBlok = makeOption($dbname,'project','kode,nama',
								  "kodeorg='".$param['kebun']."' and kode='".
								  $param['kodeblok']."' and posting=0");
		}
		
		#khusus jika Perumahan
		if($param['divisi'] == 'S'){
			$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".substr($param['kebun'],0,4)."'");
			$optBlok = makeOption($dbname,'sdm_perumahanht','norumah,keterangan',"kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[substr($param['kebun'],0,4)]."')");
		}
		
		if($param['divisi']=='P') {
			//pabrikasi
			$optBlok = makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi','',2);
			//$optAct = makeOption($dbname,'pabrikasi_5masterdt','tahapan,tahapan','',2);
		}
		
		// Init Segment
		$optSegment = array();
		$defaultSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
		// Option Segment
		$listSegment = '';
		foreach($resDetail as $row) {
			if(!empty($listSegment)) $listSegment .= ',';
			$listSegment .= "'".$row['kodesegment']."'";
		}
		if(!empty($listSegment)) {
			$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',
									 "kodesegment in (".$listSegment.")");
		}
		
		# Setting Table
		$header = array(
			$_SESSION['lang']['subunit'],
			'Termin',
			//$_SESSION['lang']['segment'],
			$_SESSION['lang']['tanggal'],
			$_SESSION['lang']['matauang'],
			$_SESSION['lang']['hk'],
			'Real Hasil Kerja',
			'Jlh Real (Rp)',
			//$_SESSION['lang']['hasilkerjarealisasi'],
			//$_SESSION['lang']['jumlahrealisasi'],
            $_SESSION['lang']['keterangan'],
            //$_SESSION['lang']['jjgkontanan'],
			$_SESSION['lang']['action']
		);
		

		# Table
		$table = "";
		$table .= "<table class='sortable' style='margin-bottom:15px' cellpading=1 cellspacing=1 border=0>";
		$table .= "<thead><tr class='rowheader'>";
		$table .= "<input type='hidden' id='jmlkgplus_".$param['kodekegiatan']."' value='".$jmlkgplus."'/>";
		foreach($header as $head) {
			$table .= "<td>".$head."</td>";
		}
		$table .= "</tr></thead>";
		$table .= "<tbody id='detailBody_".$param['numRow']."'>";
		$i=0;
		foreach($resDetail as $row) {
			# Exist Row
			$tanggal = tanggalnormal($row['tanggal']);
			$table .= "<tr id='tr_".$param['numRow'].'_'.$i."' class='rowcontent'>";
			$table .= "<td width=115px>".makeElement('blokalokasi_'.$param['numRow'].'_'.$i,'selectsearch',$row['kodeblok'],array('disabled'=>'disabled'),$optBlok)."</td>";
			
			$table .= "<td>".makeElement('termin_'.$param['numRow'].'_'.$i,'textnum',$row['termin'],array('disabled'=>'disabled','style'=>'width:50px'))."</td>";
			
			$table .= "<td style=display:none>".makeElement('kodesegment_'.$param['numRow'].'_'.$i,'select',$row['kodesegment'],array('disabled'=>'disabled'),$optSegment)."</td>";
			$table .= "<td>".makeElement('tanggal_'.$param['numRow'].'_'.$i,'text',$tanggal,array('disabled'=>'disabled','style'=>'width:80px'))."</td>";
			$table .= "<td>".makeElement('matauang_'.$param['numRow'].'_'.$i,'text',$param['matauang'],array('disabled'=>'disabled','style'=>'width:60px'))."</td>";
			
			if($row['statusjurnal']==0) {
				$table .= "<td>".makeElement('hkrealisasi_'.$param['numRow'].'_'.$i,'textnum',$row['hkrealisasi'],array('style'=>'width:60px'))."</td>";
				$table .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow'].'_'.$i,
					'textnum',$row['hasilkerjarealisasi'],array('onkeyup'=>"calJumlah(".$param['numRow'].",".$i.")",'style'=>'width:120px'))."</td>";
				$table .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow'].'_'.$i,'textnum',
					$row['jumlahrealisasi'],array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)','style'=>'width:120px'))."</td>";
				$table .= "<td style=display:none>".makeElement('jjgkontanan_'.$param['numRow'].'_'.$i,'textnum',
					$row['jjgkontanan'],array('disabled'=>'disabled',
					'onchange'=>'this.value = _formatted(this)'))."</td>";
				$table .= "<td>".makeElement('keterangan_'.$param['numRow'].'_'.$i,
					'text',$row['keterangan'],array('style'=>'width:250px','placeholder'=>'Isi dengan nomor BAPP'))."</td>";	
					
				$table .= "<td align=center>";
				if(($row['statuspengajuan']==0 or $row['statuspengajuan']==3)){
					$table .= "<img id='btn_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
					$table .= "src='images/".$_SESSION['theme']."/save.png' ";
					$table .= "onclick='saveData(".$param['numRow'].",".$i.")'>&nbsp;";
					$table .= "<img id='btnDel_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
					$table .= "src='images/".$_SESSION['theme']."/delete.png' ";
					$table .= "onclick='deleteData(".$param['numRow'].",".$i.")'>";					
				}
				
				if( $row['statuspengajuan']==1){
					$table .= "<img id='btnPost_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
					$table .= "src='images/".$_SESSION['theme']."/posting.png' ";
					$table .= "onclick=\"postingData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\">";					
				}else{
					$table .= "<img id='btnPost_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
					$table .= "src='images/".$_SESSION['theme']."/posting.png' ";
					$table .= "onclick=\"postingData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\"style='display:none'>";					
				}
				
				#$table .= "<img id='btnRev_".$param['numRow'].'_'.$i."' class='zImgBtn' style='display:none'";
				#$table .= "src='images/".$_SESSION['theme']."/zoom.png' ";
				#$table .= "onclick=\"revisiData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."',event)\">";
			} else {
				if($row['revisijumlah']>0) $row['jumlahrealisasi'] = $row['revisijumlah'];
				if($row['revisihk']>0) $row['hkrealisasi'] = $row['revisihk'];
				if($row['revisihasilkerja']>0) $row['hasilkerjarealisasi'] = $row['revisihasilkerja'];
				$table .= "<td>".makeElement('hkrealisasi_'.$param['numRow'].'_'.$i,'textnum',
					$row['hkrealisasi'],array('disabled'=>'disabled','style'=>'width:60px'))."</td>";
				$table .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow'].'_'.$i,
					'textnum',$row['hasilkerjarealisasi'],array('disabled'=>'disabled','style'=>'width:120px'))."</td>";
				$table .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow'].'_'.$i,'textnum',
					$row['jumlahrealisasi'],array('disabled'=>'disabled',
					'onchange'=>'this.value = _formatted(this)','style'=>'width:120px'))."</td>";
				$table .= "<td style=display:none>".makeElement('jjgkontanan_'.$param['numRow'].'_'.$i,'textnum',
					$row['jjgkontanan'],array('disabled'=>'disabled',
					'onchange'=>'this.value = _formatted(this)'))."</td>";
				$table .= "<td>".makeElement('keterangan_'.$param['numRow'].'_'.$i,
					'text',$row['keterangan'],array('disabled'=>'disabled','style'=>'width:250px','placeholder'=>'Isi dengan nomor BAPP'))."</td>";	
					
				$table .= "<td align=center><img id='btnPost_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
				$table .= "src='images/".$_SESSION['theme']."/posted.png'>";
				#if($row['revisijumlah']>0) {
				#	$table .= "<a style='cursor:pointer' title='HK = ".$row['revisihk'].";Hasil = ".
				#		$row['revisihasilkerja'].";Jumlah = ".number_format($row['revisijumlah'])."'>*Rev</a>";
				#} else {
				#	$table .= "<img id='btnRev_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
				#	$table .= "src='images/".$_SESSION['theme']."/zoom.png' ";
				#	$table .= "onclick=\"revisiData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."',event)\">";
				#}
			}
			$table .= "</td>";
			$table .= "</tr>";
			$i++;
		}
		
		// Opt Segment for New Row
		$blok = key($optBlok);
		$query = "select distinct a.kodesegment,a.namasegment from ".$dbname.".keu_5segment a
			left join ".$dbname.".keu_5proporsisegment b on a.kodesegment=b.kodesegment
			where b.kodeblok='".$blok."' or a.kodesegment = '".$defaultSegment."'";
		$res = fetchData($query);
		$optSegment = array();
		foreach($res as $row) {
			$optSegment[$row['kodesegment']] = $row['namasegment'];
		}
		
		# New Row
		$table .= "<tr id='tr_".$param['numRow'].'_'.$i."' class='rowcontent'>";
		$table .= "<td  width=115px>".makeElement('blokalokasi_'.$param['numRow'].'_'.$i,'selectsearch','',
									 array('onchange'=>"getSegment(".$param['numRow'].",".$i.")"),$optBlok)."</td>";
		$table .= "<td>".makeElement('termin_'.$param['numRow'].'_'.$i,'textnum','',array('style'=>'width:50px'))."</td>";
		
		$table .= "<td style=display:none>".makeElement('kodesegment_'.$param['numRow'].'_'.$i,'select','',array(),$optSegment)."</td>";
		$table .= "<td>".makeElement('tanggal_'.$param['numRow'].'_'.$i,'date','',array('style'=>'width:80px'))."</td>";
		$table .= "<td>".makeElement('matauang_'.$param['numRow'].'_'.$i,'text',$param['matauang'],array('disabled'=>'disabled','style'=>'width:60px'))."</td>";
		$table .= "<td>".makeElement('hkrealisasi_'.$param['numRow'].'_'.$i,'textnum',0,array('style'=>'width:60px'))."</td>";
		$table .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow'].'_'.$i,
			'textnum',0,array('onkeyup'=>"calJumlah(".$param['numRow'].",".$i.")",'style'=>'width:120px'))."</td>";
		$table .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow'].'_'.$i,'textnum',0,
			array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)','style'=>'width:120px'))."</td>";
        $table .= "<td style=display:none>".makeElement('jjgkontanan_'.$param['numRow'].'_'.$i,'textnum',
			0,array('disabled'=>'disabled',
			'onchange'=>'this.value = _formatted(this)'))."</td>";
		
		$table .= "<td>".makeElement('keterangan_'.$param['numRow'].'_'.$i,
			'text','',array('style'=>'width:250px','placeholder'=>'Isi dengan nomor BAPP'))."</td>";
			
		$table .= "<td align=center>&nbsp;<img id='btn_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
		$table .= "src='images/".$_SESSION['theme']."/plus.png' ";
		$table .= "onclick=\"addData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\">";
		$table .= "&nbsp;<img id='btnDel_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
		$table .= "src='images/".$_SESSION['theme']."/delete.png' style='display:none'";
		$table .= "onclick='deleteData(".$param['numRow'].",".$i.")'>";
		$table .= "<img id='btnPost_".$param['numRow'].'_'.$i."' class='zImgBtn' ";
		$table .= "src='images/".$_SESSION['theme']."/posting.png' ";
		$table .= "onclick=\"postingData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\" style='display:none'>";
		$table .= "<img id='btnRev_".$param['numRow'].'_'.$i."' class='zImgBtn' style='display:none'";
		$table .= "src='images/".$_SESSION['theme']."/zoom.png' ";
		$table .= "onclick=\"revisiData(".$param['numRow'].",".$i.",'".$_SESSION['theme']."')\">";
		$table .= "</td></tr></tbody>";
		$table .= "</table>";
		$i++;
		
		echo $table;
		break;
    case 'add':
		
		$data = $param;
		unset($data['numRow1']);
		unset($data['divisi']);
		unset($data['matauang']);
		unset($data['blokalokasi']);
		unset($data['numRow2']);
		unset($data['kebun']);
		unset($data['jmlkgplus']);
		$data['kodeblok'] = $param['blokalokasi'];
		$data['posting'] = '0';
		$data['statusjurnal'] = '0';
		$data['blokspkdt'] = $param['kodeblok'];
		
		//print_r($data);exit("Error:asdasd");
        
		$data['jumlahrealisasi'] = str_replace(',','',$data['jumlahrealisasi']);
                $data['jjgkontanan'] = str_replace(',','',$data['jjgkontanan']);
		$dtCol=array('notransaksi', 'kodeblok', 'kodekegiatan','kodesegment', 'tanggal', 'hasilkerjarealisasi', 'hkrealisasi', 'jumlahrealisasi', 'jjgkontanan','termin','keterangan', 'posting', 'statusjurnal', 'blokspkdt');
		# Options
		$optBlok = array();
		if(strlen($param['divisi'])==4){
			$optBlok[$param['divisi']]=$param['divisi'];
		} 
		
		$str_blok="SELECT b.kodeorganisasi as kodeorg, b.namaorganisasi as namaorg FROM ".$dbname.".setup_blok a LEFT JOIN ".$dbname.".organisasi b 
			ON a.kodeorg = b.kodeorganisasi 
			WHERE a.luasareaproduktif >0 and b.kodeorganisasi like '".substr($param['divisi'],0,4)."%' 
			and length(b.kodeorganisasi)>6";
		$res_blok=$owlPDO->query($str_blok) or die(print " Gagal: ".PDOException::getMessage());
		$res_blok->setFetchMode(PDO::FETCH_OBJ);
		
		while($bar=$res_blok->fetch()) {
            $optBlok[$bar->kodeorg]=$bar->namaorg;
        }  
		#khusus jika project
		if(empty($param['divisi'])) {
			$optBlok = makeOption($dbname,'project','kode,nama',
								  "kodeorg='".$param['kebun']."' and kode='".
								  $param['kodeblok']."' and posting=0");
		}
		
		if($param['divisi']=='P') {
			//pabrikasi
			$optBlok = makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi','',2);
			//$optAct = makeOption($dbname,'pabrikasi_5masterdt','tahapan,tahapan','',2);
		}
		
		
		#khusus jika Perumahan
		if($param['divisi'] == 'S'){
			$optRegOrg = makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".substr($param['kebun'],0,4)."'");
			$optBlok = makeOption($dbname,'sdm_perumahanht','norumah,keterangan',"kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$optRegOrg[substr($param['kebun'],0,4)]."')");
		}
		//if(substr($param['divisi'],0,2)=='AK' or substr($param['divisi'],0,2)=='PB'){
		//	$optBlok = makeOption($dbname,'project','kode,nama',"kode='".$param['divisi']."' and posting=0");
		//}    		
		# Empty Data
		foreach($data as $cont) {
			if($cont=='') exit('Warning : Data tidak boleh ada yang kosong');
		}
        
		//cek tanam: april 4, 2014
		//dicopy dari file: kebun_slave_operasional_detail: cegatKegiatan
        $kegiatan = $param['kodekegiatan'];
        $kodeorg = $param['blokalokasi'];
        $hasilkerja = $param['hasilkerjarealisasi'];
        $qwe=explode('-',$param['tanggal']);        
        $tanggal = $qwe[2].'-'.$qwe[1].'-'.$qwe[0];
        
        // cek hasil kerja ga boleh 0
        if($hasilkerja==0){
            echo "error: ".$_SESSION['lang']['hasilkerjad']." = 0.";
            exit();
        }
        
        // ambil kode parameter kegiatan
        $where = "nilai = '".$kegiatan."'";
        $cols = "kodeparameter";
		$query = selectQuery($dbname,'setup_parameterappl',$cols,$where);
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$kodeparameter='';
        while($bar=$res->fetch())
        {
            $kodeparameter=$bar->kodeparameter;
        }
        $luasareanonproduktif=0;
        $jumlahpokok=0;
        $luasareaproduktif=0;
        
        // kalo kegiatan tanam, cek. kalo luas blok = luas kerangka tidak bisa.
        $where = "kodeorg = '".$kodeorg."'";
        $cols = "luasareanonproduktif,jumlahpokok,luasareaproduktif";
        $query = selectQuery($dbname,'setup_blok',$cols,$where);
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $luasareanonproduktif=$bar->luasareanonproduktif;
            $jumlahpokok=$bar->jumlahpokok;
            $luasareaproduktif=$bar->luasareaproduktif;
        }
        @$sph=($jumlahpokok+$hasilkerja)/$luasareaproduktif;
        $maxtanam=$luasareanonproduktif*150;      
        
        // kalo kegiatan sisip, cek. kalo sisa rencanasisip-udahsisip<=0 tidak bisa.
        // ambil rencana sisip s/d pada tahun berjalan
		#update indra : karena table rencana sisip untuk periode format m-Y (05-2017)
		#maka pembentukan where periode mesti diganti
		$perrencana=substr($tanggal,5,2).'-'.substr($tanggal,0,4);
        $where = "blok = '".$kodeorg."' and periode <= '".$perrencana."' and 
				substr(periode,4,4) = '".substr($tanggal,0,4)."' and posting ='1'";
		// $where = "blok = '".$kodeorg."' and periode <= '".substr($tanggal,0,7)."' and 
				// substr(periode,1,4) = '".substr($tanggal,0,4)."' and posting ='1'";		
        $cols = "sum(rencanasisip) as rencanasisip";
        $query = selectQuery($dbname,'kebun_rencanasisip',$cols,$where);
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$rencanasisip=0;
        while($bar=$res->fetch())
        {
            @$rencanasisip+=$bar->rencanasisip; 
        }
        
        // ambil jumlah sisip
        // BKM
        $query="select kodeorg,sum(hasilkerja)as telahsisip from ".$dbname.".kebun_perawatan_vw 
            where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeorg = '".$kodeorg."' and tanggal >= '".$tanggal."' and tanggal like '".substr($tanggal,0,4)."%'";
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$sudahsisip=0;
        while($bar=$res->fetch())
        {
            $sudahsisip+=$bar->telahsisip;
        }
        // PERAWATAN
        $query="select kodeblok,sum(hasilkerjarealisasi)as telahsisip from ".$dbname.".log_baspk 
            where kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeparameter like 'SISIP%')
            and kodeblok = '".$kodeorg."' and tanggal >= '".$tanggal."' and tanggal like '".substr($tanggal,0,4)."%'";    
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $sudahsisip+=$bar->telahsisip;
        }
        $sisasisip=$rencanasisip-($sudahsisip+$hasilkerja);       
        
        if(substr($kodeparameter,0,5)=='TANAM'){
            if($hasilkerja>$maxtanam){
                echo "error: Tidak bisa tanam baru, luas yang belum ditanam: ".number_format($luasareanonproduktif,2)." Ha, pokok bisa ditanam: ".number_format($maxtanam).". Jumlah ditanam: ".number_format($hasilkerja).".";
                exit();
            }
        }
        if(substr($kodeparameter,0,5)=='COMPL'){
            if($sph>150){
                echo "error: SPH setelah transaksi lebih dari 150: ".number_format($sph,2).".";
                exit();
            }
        }
        if(substr($kodeparameter,0,5)=='SISIP'){
            if($sisasisip<0){
                echo "error: Harap diinput data pokok mati dan rencana sisipan, rencana sisip: ".$rencanasisip.", sudah sisip: ".$sudahsisip." + ".$hasilkerja.", sisa rencana sisip: ".$sisasisip.".";
                exit();
            }
        }                
                //
		
		
		
		# Convert Tanggal
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		
		$strKurs="select * from ".$dbname.".setup_matauangrate where daritanggal=".$data['tanggal']."";
		$qryKurs=$owlPDO->query($strKurs) or die(print " Gagal: ".PDOException::getMessage());
		$numRows=owlBaris($qryKurs);
		
		$tbaspk=$tspk=0;
		#buat validasi tidak boleh lebih dari spk
		#ambil dispk
		$str=" select jumlahrp as tspk,kodeblok from ".$dbname.".log_spkdt where 
			notransaksi='".$data['notransaksi']."' and kodeblok='".$data['kodeblok']."'
			and kodekegiatan='".$data['kodekegiatan']."'"; 
			// exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tspk=$bar['tspk'];
			$blk=$bar['kodeblok'];
		
		#ambil data di ba
		$str=" select sum(jumlahrealisasi) as tbaspk from ".$dbname.".log_baspk where 
			notransaksi='".$data['notransaksi']."' and kodeblok='".$data['kodeblok']."'
			and kodekegiatan='".$data['kodekegiatan']."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tbaspk=$bar['tbaspk'];
			
		#cek termin
		$str=" select count(*) as jumlah from ".$dbname.".log_baspk where 
			notransaksi='".$data['notransaksi']."' and kodeblok='".$data['kodeblok']."'
			and kodekegiatan='".$data['kodekegiatan']."' and termin='".$data['termin']."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$cektermin=$bar['jumlah'];
			
		if($cektermin>0){
			exit("Warning : Termin ke ".$data['termin']." sudah ada !");
		}	
		
		
		if ($param['jmlkgplus']==0) {
			if($data['kodeblok']!=$blk)
			{exit("Warning : blok salah atau tidak ditemukan");}
			else if(($data['jumlahrealisasi']+$tbaspk)>$tspk){
				exit("Warning : Data melebihi SPK, nilai BA : ".($data['jumlahrealisasi']+$tbaspk)." nilai SPK : ".$tspk);
			}	
		}	
		
	
		
		if($param['matauang']!='IDR' && $numRows<=0){
			echo "Gagal : Data kurs untuk mata uang ".$param['matauang']." pada tanggal ".tanggalnormal($data['tanggal'])." masih belum ada";
		}else{
			
			$query = insertQuery($dbname,'log_baspk',$data,$dtCol);
			
			//exit("error");
			try{
				$owlPDO->exec($query);

				// Init Segment
				$optSegment = array();
				$defaultSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
				
				// Opt Segment for New Row
				$blok = key($optBlok);
				
			
				
				$query = "select distinct a.kodesegment,a.namasegment from ".$dbname.".keu_5segment a
					left join ".$dbname.".keu_5proporsisegment b on a.kodesegment=b.kodesegment
					where b.kodeblok='".$blok."' or a.kodesegment = '".$defaultSegment."'";
				$res = fetchData($query);
				$optSegment = array();
				foreach($res as $row) {
					$optSegment[$row['kodesegment']] = $row['namasegment'];
				}
				
				# Prepare New
				$i = $param['numRow2']+1;
				$row = "<td width=115px>".makeElement('blokalokasi_'.$param['numRow1'].'_'.$i,'selectsearch','',array(),$optBlok)."</td>";
				$row .= "<td>".makeElement('termin_'.$param['numRow1'].'_'.$i,'textnum','',array('style'=>'width:50px'))."</td>";
				$row .= "<td style=display:none>".makeElement('kodesegment_'.$param['numRow1'].'_'.$i,'select','',array(),$optSegment)."</td>";
				$row .= "<td>".makeElement('tanggal_'.$param['numRow1'].'_'.$i,'date','',array('style'=>'width:80px'))."</td>";
				$row .= "<td>".makeElement('matauang_'.$param['numRow1'].'_'.$i,'text',$param['matauang'],array('disabled'=>'disabled','style'=>'width:60px'))."</td>";
				$row .= "<td>".makeElement('hkrealisasi_'.$param['numRow1'].'_'.$i,'textnum',0,array('style'=>'width:60px'))."</td>";
				$row .= "<td>".makeElement('hasilkerjarealisasi_'.$param['numRow1'].'_'.$i,
				'textnum',0,array('onkeyup'=>"calJumlah(".$param['numRow1'].",".$i.")",'style'=>'width:120px'))."</td>";
				$row .= "<td>".makeElement('jumlahrealisasi_'.$param['numRow1'].'_'.$i,'textnum',0,
				array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)','style'=>'width:120px'))."</td>";
				$row .= "<td style=display:none>".makeElement('jjgkontanan_'.$param['numRow1'].'_'.$i,'textnum',0,
				array('onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)','disabled'=>'disabled'))."</td>";
				
				$row .= "<td>".makeElement('keterangan_'.$param['numRow1'].'_'.$i,'text','',array('style'=>'width:250px','placeholder'=>'Isi dengan nomor BAPP'))."</td>";
				$row .= "<td align=center><img id='btn_".$param['numRow1']."_".$i."' class='zImgBtn' ";
				$row .= "src='images/".$_SESSION['theme']."/plus.png' ";
				$row .= "onclick=\"addData(".$param['numRow1'].",".$i.",'".$_SESSION['theme']."')\">&nbsp;";
				$row .= "<img id='btnDel_".$param['numRow1'].'_'.$i."' class='zImgBtn' ";
				$row .= "src='images/".$_SESSION['theme']."/delete.png' style='display:none'";
				$row .= "onclick='deleteData(".$param['numRow1'].",".$i.")'>&nbsp;";
				$row .= "<img id='btnPost_".$param['numRow1'].'_'.$i."' class='zImgBtn' ";
				$row .= "src='images/".$_SESSION['theme']."/posting.png' ";
				$row .= "onclick=\"postingData(".$param['numRow1'].",".$i.",'".$_SESSION['theme']."')\" style='display:none'>&nbsp;";
				$row .= "<img id='btnRev_".$param['numRow1'].'_'.$i."' class='zImgBtn' style='display:none'";
				$row .= "src='images/".$_SESSION['theme']."/zoom.png' ";
				$row .= "onclick=\"revisiData(".$param['numRow1'].",".$i.",'".$_SESSION['theme']."')\">";
				$row .= "</td>";
				
				echo $row;
			}catch (PDOException $e){
				echo "Gagal : ".$e->getMessage();
			}
		}
		break;
		
    case 'edit':
		$data = $param;
		unset($data['notransaksi']);
		unset($data['kodeblok']);
		unset($data['blokalokasi']);
		unset($data['kodekegiatan']);
		unset($data['kodesegment']);
		unset($data['tanggal']);
		unset($data['matauang']);
		unset($data['numRow1']);
		unset($data['numRow2']);
		$data['jumlahrealisasi'] = str_replace(',','',$data['jumlahrealisasi']);
        $data['jjgkontanan'] = str_replace(',','',$data['jjgkontanan']);
		
		# Empty Data
		foreach($data as $cont) {
			if($cont=='') {
			echo 'Warning : Data tidak boleh ada yang kosong';
			exit;
			}
		}
		
		# Convert Tanggal
		$param['tanggal'] = tanggalsystem($param['tanggal']);	
		
		
		
		
		#buat validasi tidak boleh lebih dari spk
		#ambil dispk
		$str=" select jumlahrp as tspk from ".$dbname.".log_spkdt where 
			notransaksi='".$param['notransaksi']."' and kodeblok='".$param['kodeblok']."'
			and kodekegiatan='".$param['kodekegiatan']."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tspk=$bar['tspk'];
		
		#ambil data di ba
		$str=" select sum(jumlahrealisasi) as tbaspk from ".$dbname.".log_baspk where 
			notransaksi='".$param['notransaksi']."' and kodeblok='".$param['kodeblok']."'
			and kodekegiatan='".$param['kodekegiatan']."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tbaspk=$bar['tbaspk'];
			
		#ambil data lama	
		$str=" select jumlahrealisasi as dtlama from ".$dbname.".log_baspk where 
			notransaksi='".$param['notransaksi']."' and kodeblok='".$param['blokalokasi']."'
			and kodekegiatan='".$param['kodekegiatan']."' and tanggal='".$param['tanggal']."'";			
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$dtlama=$bar['dtlama'];	
			
			
		if(($tbaspk-$dtlama+$data['jumlahrealisasi'])>$tspk){
			exit("Warning:Nilai melebihi estimasi kontrak header");
		}
		$where = "notransaksi='".$param['notransaksi'].
			"' and kodeblok='".$param['blokalokasi'].
			"' and kodekegiatan='".$param['kodekegiatan'].
			"' and tanggal='".$param['tanggal'].
			"' and blokspkdt='".$param['kodeblok'].
			"' and kodesegment='".$param['kodesegment']."'";
		$query = updateQuery($dbname,'log_baspk',$data,$where);
		try{
			$owlPDO->exec($query); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
	
	case'posting':
			$where = "notransaksi='".$param['notransaksi'].
			"' and kodeblok='".$param['blokalokasi'].
			"' and kodekegiatan='".$param['kodekegiatan'].
			"' and tanggal='".tanggalsystemn($param['tanggal']).
			"' and kodesegment='".$param['kodesegment']."'";
			
			$str = "update " . $dbname . ".log_baspk set posting='1' where " . $where . "";
			
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "Gagal : ".$e->getMessage();
			}
	break;
	case'getapprovaldetail':
		$tab="";
		$kodeorg   = checkPostGet('kodeorg', '');
		$notransaksi   = checkPostGet('nopengajuan', '');
		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		
		$tab.="<span><b>Approval</b></span>";
		$tab.="<table  border=0 cellspacing=1 cellpadding=1 class=sortable>";
		$countApprove = getCountApproval('BAPP',$kodeorg);
		$tab.= "<thead>
				<tr style='font-weight:bold'>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>";

		for($i=1;$i<=$countApprove;$i++){
			$arrApp = detailApprove($i,$notransaksi,'BAPP');
			if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($arrApp['tanggal']);
			}
			
			if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
				$tab.= "<td valign=top>x".$arrApp['nama']."
						<br>".$arrHsl[$arrApp['status']]."
						<br>".$tngl."
						<br>".$arrApp['komentar']."
						</td>";
			}else{
				$tab.= "<td>&nbsp;</td>";
			}
		}
		$tab.= "</tbody></table>";
		
		
		#status tolak
		$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				$tab.="<br><table border=0 cellspacing=1 class=sortable>
						<thead>
						<tr style='font-weight:bold'>
							<td colspan='".($val['level'])."'>Return / Tolak - ".$no."</td>
						</tr>
						<tr style='font-weight:bold'>";
							for($i=1;$i<=$val['level'];$i++) {
								$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
							}
						$tab.="</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>";
						for($i=1;$i<=$val['level'];$i++) {
							$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
							$resx=fetchdata($strx);
							$color='';
							if($resx[0]['status']==3){
								$color=" style=background-color:red ";
							}
							$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
								<br>	
								".$arrHsl[$resx[0]['status']]."
								<br>	
								".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
								<br>	
								".$resx[0]['komentar']."
							</td>";
						}
						$tab.="</tr>
					</tbody>
					</table>";
			}
		}
		echo $tab;
	break;
	case'rekapbapp':
		$strPos = "select * from ".$dbname.".log_baspk where notransaksi='".$param['notransaksi']."'";
		$cekpost = count(fetchData($strPos));
		if($cekpost==0){
			$tab="Data Kosong !!!";
		}else{
		$tab="
			<table border=0 style=width:100%>
			<tr>
				<td align=center style=background-color:gray><b>SPK</b></td>
			</tr>";	
		$tab.="<tr>";
		$tab.="<td>";
		#=====================================================
			$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style=width:100%>
				<thead><tr class=rowheader>";
				
				$tab.="<td align=center width=20px>No</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".$_SESSION['lang']['nomor']."</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td align=center>".$_SESSION['lang']['dari']."</td>
					<td align=center>".$_SESSION['lang']['sampai']."</td>
					<td align=center>".$_SESSION['lang']['jumlah'] . " (Rp)</td>
				</tr>
				</thead>
				";
			$where = "notransaksi='".$param['notransaksi']."'";	
			$str = "select * from ".$dbname.".log_spkht where ".$where."";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$no='';
			while ($bar = $res->fetch()) {
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td align=center>".$bar['notransaksi']."</td>";
				$tab.="<td align=left>".$bar['keterangan']."</td>";
				$tab.="<td align=center>".tanggalnormal($bar['dari'])."</td>";
				$tab.="<td align=center>".tanggalnormal($bar['sampai'])."</td>";
				$tab.="<td align=right>".number_format($bar['nilaikontrak'])."</td>";
				$tab.="</tr>";
				@$totalspk+=$bar['nilaikontrak'];
			}		
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=6>T O T A L</td>";
			$tab.="<td align=right>".number_format($totalspk)."</td>";
			$tab.="</tr>";
			
			$tab.="</table>";
		#=====================================================
		
		$tab.="</td>";
		$tab.="</tr>";
			
		$tab.="</table>";	
		$tab.="
			<table border=0 style=width:100%>
			<tr>
				<td align=center style=background-color:gray><b>BAPP</b></td>
			</tr>
			<tr>";
				$tab.="<td valign=top>";
				#=====================================================
				$style='';
				if(@$param['sumber']=='approval'){
					$style="style=display:none";
				}
				$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style=width:100%>
					<thead><tr class=rowheader>";
					
					$tab.="<td align=center width=20px>No</td>
						<td align=center>Termin</td>
						<td align=center>".$_SESSION['lang']['tanggal']."</td>
						<td align=center>Hasil Kerja</td>
						<td align=center>".$_SESSION['lang']['jumlah'] . " (Rp)</td>
						<td align=center>Keterangan</td>
						<td align=center>No Persetujuan</td>
						<td align=center colspan=3>Persetujuan</td>
						<td align=center ".$style." colspan=2>#</td>
					</tr>
					</thead>
					";
				$where = "notransaksi='".$param['notransaksi']."'";	
				$str = "select * from ".$dbname.".log_baspk where ".$where." order by termin desc";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$datashow[$bar['termin']][$bar['tanggal']]=$bar['tanggal'];
					$tanggal[$bar['termin']][$bar['tanggal']]=$bar['tanggal'];
					$ket[$bar['termin']][$bar['tanggal']]=$bar['keterangan'];
					@$real[$bar['termin']][$bar['tanggal']]+=$bar['jumlahrealisasi'];
					@$hasilkerja[$bar['termin']][$bar['tanggal']]+=$bar['hasilkerjarealisasi'];
					$notransaksi[$bar['termin']][$bar['tanggal']]=$bar['notransaksi'];
					$statuspengajuan[$bar['termin']][$bar['tanggal']]=$bar['statuspengajuan'];
					$nopengajuan[$bar['termin']][$bar['tanggal']]=$bar['nopengajuan'];
				}		
				$no='';
				$kodeorgspk=makeOption($dbname,'log_spkht','notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");
				
				$optnmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
				if(count($datashow)>0){
					foreach($datashow as $termin => $valtgl){
						foreach($valtgl as $tanggal){
							$x='';
							if(@$param['nopengajuan']==$nopengajuan[$termin][$tanggal] and @$param['sumber']=='approval'){
								$x="style=background-color:green";
							}
							
							$no+=1;
							$tab.="<tr class=rowcontent ".$x." id=rowdetail_".$no.">";
							$tab.="<td align=center>" . $no . "</td>";
							$tab.="<td align=center>".$termin."</td>";
							$tab.="<td align=center>".tanggalnormal($tanggal)."</td>";
							$tab.="<td align=right>".number_format($hasilkerja[$termin][$tanggal])."</td>";
							$tab.="<td align=right>".number_format($real[$termin][$tanggal])."</td>";
							$tab.="<td align=left>".($ket[$termin][$tanggal])."</td>";
							
							#persetujuan
							$warna='';
							if($statuspengajuan[$termin][$tanggal]=='3'){
								$warna=" style=background-color:red";
							}
							$i='';
							if($nopengajuan[$termin][$tanggal]!=''){
								$i="onclick=getapprovaldetail('".$nopengajuan[$termin][$tanggal]."','".$kodeorgspk[$notransaksi[$termin][$tanggal]]."')";
							}
							$tab.="<td align=center style=cursor:pointer ".$i."><font color=blue>".$nopengajuan[$termin][$tanggal]."</font></td>";
							
							$tab.="<td ".$warna." align=center>".$optstatus[$statuspengajuan[$termin][$tanggal]]."</td>";
							
							# approval		
							$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
							
							$strX = "select * from ".$dbname.".approval where notransaksi='".$nopengajuan[$termin][$tanggal]."' and jenispersetujuan='BAPP' order by level desc limit 1";
							$resX = $owlPDO->query($strX) or die(print " Gagal: " . PDOException::getMessage());
							$resX->setFetchMode(PDO::FETCH_ASSOC);
							$barX = $resX->fetch();
							if($barX['tanggal']==''|| $barX['tanggal']=='0000-00-00 00:00:00'){
								$tngl='';
							}else{
								$tngl=tanggalnormal($barX['tanggal']);
							}
							$tab.="<td ".$warna.">
									    ".@$optnmkary[$barX['karyawanid']]."
									<br>".@$arrHsl[$barX['status']]."
									<br>".$tngl."
									<br>".$barX['komentar']."
									</td>";
							# end approval
							
							if($statuspengajuan[$termin][$tanggal]=='0' or $statuspengajuan[$termin][$tanggal]=='3'){
								$tab.="<td ".$style." align=center><img src='images/skyblue/submit.jpg' class='resicon' height='30' title='Ajukan' onclick=\"form_ajukan('".$kodeorgspk[$param['notransaksi']]."','".$param['notransaksi']."','".$tanggal."','".$termin."','".$no."');\">
								
								
								</td>";
								
								
							}else{
								$tab.="<td ".$style." align=center></td>";
							}
							$tab.="<td ".$style." align=center><img src='images/skyblue/zoom.png' class='resicon' height='30' title='View' onclick=\"view('".$nopengajuan[$termin][$tanggal]."','".$param['notransaksi']."','".$kodeorgspk[$param['notransaksi']]."','".$tanggal."','".$termin."','".$no."','event','html');\"></td>";
							
							
							$tab.="<td ".$style." align=center><img src='images/upload-2-xxl.png' class='resicon' height='30' title='Upload' onclick=\"UploadFile('".$param['notransaksi']."','".$tanggal."','".$termin."','".$no."');\"></td>";
							
							@$totalbapp+=$real[$termin][$tanggal];
							
							$tab.="</tr>";				
						}
					}
					
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=4>T O T A L</td>";
					$tab.="<td align=right>".number_format($totalbapp)."</td>";
					$tab.="<td align=center colspan=7></td>";
					$tab.="</tr>";
				}
				$tab.="</table>";
			#=====================================================
			$tab.="</td>";
			$tab.="</table>";	
			$tab.="
			<table border=0 style=width:100%>
			<tr>
				<td align=center style=background-color:gray><b>Tagihan dan Kas Bank</b></td>
			</tr>
			<tr>";
			$tab.="<td valign=top>";
			#=====================================================
			$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style=width:100%>
					<thead><tr class=rowheader>";
				$tab.="<td align=center width=20px>No</td>
						<td align=center>Tanggal</td>
						<td align=center>No Invoice</td>
						<td align=center>Tipe</td>
						<td align=center>Jumlah</td>
						<td  style=background-color:gray></td>
						<td align=center>Tanggal</td>
						<td align=center>No Kas Bank</td>
						<td align=center>Jumlah</td>
					</tr>
					</thead>";
				$str = "select * from ".$dbname.".keu_tagihanht where nopo='".$param['notransaksi']."' order by noinvoice asc";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$datatagihan[$bar['noinvoice']]=$bar['noinvoice'];
					$tipeinvoice[$bar['noinvoice']]=$bar['tipeinvoice'];
					$tanggalinv[$bar['noinvoice']]=$bar['tanggal'];
					$nilaiinvoice[$bar['noinvoice']]=$bar['nilaiinvoice'];
					$nopo[$bar['noinvoice']]=$bar['nopo'];
				}		
				$no='';
				if(count($datatagihan)>0){
					foreach($datatagihan as $noinvoice){
						$nmtipe=makeOption($dbname,'keu_5jenistagihan','kode,namajenis',"kode='".$tipeinvoice[$noinvoice]."'");
						
						$no+=1;
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>" . $no . "</td>";
						$tab.="<td align=center>".tanggalnormal($tanggalinv[$noinvoice])."</td>";
						$tab.="<td align=center>".$noinvoice."</td>";
						$tab.="<td align=left>".$nmtipe[$tipeinvoice[$noinvoice]]."</td>";
						$tab.="<td align=right>".number_format($nilaiinvoice[$noinvoice])."</td>";
						$tab.="<td align=center  style=background-color:gray></td>";
						
						#kas bank
						$strKb = "select * from ".$dbname.".keu_kasbankdtht_vw where nodok='".$nopo[$noinvoice]."' and keterangan1='".$noinvoice."' and jumlah>'0'";
						$resKb = $owlPDO->query($strKb) or die(print " Gagal: " . PDOException::getMessage());
						$resKb->setFetchMode(PDO::FETCH_ASSOC);
						$barKb = $resKb->fetch();
						if($barKb['tanggal']=='0000-00-00' or $barKb['tanggal']==''){
							$tab.="<td></td>";
						}else{
							$tab.="<td align=center>".tanggalnormal($barKb['tanggal'])."</td>";
						}
						$tab.="<td align=left>".$barKb['notransaksi']."</td>";
						$tab.="<td align=right>".number_format($barKb['jumlah'])."</td>";
						
						$tab.="</tr>";		
						@$totaltagihan+=$nilaiinvoice[$noinvoice];
						@$totalkasbank+=$barKb['jumlah'];
					}
					
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=4>T O T A L</td>";
					$tab.="<td align=right>".number_format($totaltagihan)."</td>";
					$tab.="<td align=center  style=background-color:gray></td>";
					$tab.="<td align=center colspan=2></td>";
					$tab.="<td align=right>".number_format($totalkasbank)."</td>";
					$tab.="</tr>";
				}
				
				$tab.="</table>";
			#=====================================================	
			$tab.="</td>";
			$tab.="</tr>";
			
			$tab.="</table>";
		
		} # ==> tutup if data kosong
		echo $tab;
	break;
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
		  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
		  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='BAPP' and a.level='1' and a.kodeunit='".$param['kodeorg']."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
		
		
		$strPos = "select * from ".$dbname.".log_baspk where notransaksi='".$param['notransaksi']."' and tanggal='".$param['tanggal']."' and termin='".$param['termin']."'";
		$res=$owlPDO->query($strPos) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['nopengajuan']!=''){
				$nopengajuan=$bar['nopengajuan'];
			}else{
				$nopengajuan=$param['kodeorg'].date("Ymdhms");
			}
		}	
		
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$param['notransaksi']."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['tanggal'] . "</td>
					<td width=5px>:</td>
					<td id=tanggal_aju>".$param['tanggal']."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['termin'] . " Ke</td>
					<td width=5px>:</td>
					<td id=termin_aju>".$param['termin']."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>No Pengajuan</td>
					<td width=5px>:</td>
					<td id=nopengajuan_aju>".$nopengajuan."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:99%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$param['numrow']."></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	case'ajukan':
	
		try {
		$owlPDO->beginTransaction();
			if($param['kepada']=='' or $param['notransaksi']==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			if($param['nopengajuan']==''){
				throw new PDOException('No Pengajuan tidak boleh kosong.');
			}
			
			$strPos = "select * from ".$dbname.".log_baspk where notransaksi='".$param['notransaksi']."' and tanggal='".$param['tanggal']."' and termin='".$param['termin']."' and posting='0'";
			$cekpost = count(fetchData($strPos));
			
			if($cekpost>0){
				#throw new PDOException('Ada detail transaksi yang belum di posting.');
			}
			
			//cari dulu apakah sudah pernah di ajukan sebelumnya
			$tglhi = date("Ymd");
			$str="select * from ".$dbname.".approval where jenispersetujuan='BAPP' and notransaksi='".$param['nopengajuan']."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				if($bar['notransaksi']!=''){
					# jika ada pindahkan ke table ini
					$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
					$owlPDO->exec($str);
				}
			}
			
			#kemudian setelah di pindah, hapus persetujuan lama
			$str="delete from ".$dbname.".approval where jenispersetujuan='BAPP' and notransaksi='".$param['nopengajuan']."'";
			$owlPDO->exec($str);
			
			
			# update flag menjadi 1
			$str = "update " . $dbname . ".log_baspk set statuspengajuan='9', nopengajuan='".$param['nopengajuan']."' where notransaksi='".$param['notransaksi']."' and tanggal='".$param['tanggal']."' and termin='".$param['termin']."'";
			$owlPDO->exec($str);
			
			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','".$param['nopengajuan']."','BAPP','1','" . $param['kepada']."','0','','','')";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		echo $param['nopengajuan'];
	break;
	
    case 'delete':
		# Convert Tanggal
		$param['tanggal'] = tanggalsystem($param['tanggal']);
		$where = "notransaksi='".$param['notransaksi'].
			"' and kodeblok='".$param['blokalokasi'].
			"' and kodekegiatan='".$param['kodekegiatan'].
			"' and tanggal='".$param['tanggal'].
			"' and blokspkdt='".$param['kodeblok'].
			"' and kodesegment='".$param['kodesegment']."'";
		$query = "delete from `".$dbname."`.`log_baspk` where ".$where;
		
		try{
			$owlPDO->exec($query); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
		break;
		
	case'preview':
	
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $gen='generic.css';
	}else if($theme=='red'){
	  $gen='genericRed.css';  
	}else{
	  $gen='genericGray.css';  
	} 
	$tab="<link rel=stylesheet type='text/css' href='style/".$gen."'>
	";
	$koderek=makeOption($dbname,'log_spkht','notransaksi,koderekanan',"notransaksi='".$param['notransaksi']."'");
	$nmpek=makeOption($dbname,'log_spkht','notransaksi,keterangan',"notransaksi='".$param['notransaksi']."'");
	$nmsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$koderek[$param['notransaksi']]."'");
	$str="select sum(jumlahrp) as jumlahrp from ".$dbname.".log_spkdt where notransaksi='".$param['notransaksi']."'";
	$res = fetchData($str);
	$click='';
	
	if($param['sumber']=='approval'){
		$kodeorgspk=makeOption($dbname,'log_spkht','notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");
		$click=" style=cursor:pointer; onclick=\"viewdetailbapp('".$param['notransaksi']."','".$kodeorgspk[$param['notransaksi']]."','','".$param['nopengajuan']."');\"";
	}
	
	
	$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td>No Pengajuan</td><td>:</td><td>".$param['nopengajuan']."</td>";
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td>No SPK</td><td>:</td><td ".$click."><font color=blue>".$param['notransaksi']."</font></td>";
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td>Pekerjaan</td><td>:</td><td>".$nmpek[$param['notransaksi']]."</td>";
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td>Kode Rekanan</td><td>:</td><td>".$nmsupp[$koderek[$param['notransaksi']]]."</td>";
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td>Nilai SPK</td><td>:</td><td align=center>".number_format($res[0]['jumlahrp'])."</td>";
	$tab.="</tr>";
	$tab.="</table>";
	$tab.="<hr>";
	
	$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable style=width:100%>
		<thead><tr class=rowheader>";
		$tab.="<td align=center width=20px>No</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>".$_SESSION['lang']['kegiatan']."</td>
			<td align=center>".$_SESSION['lang']['blok']."</td>
			<td align=center>Sat</td>
			<td align=center>Real Hasil Kerja</td>
			<td align=center>Harga Rp</td>
			<td align=center>".$_SESSION['lang']['jumlah'] . " (Rp)</td>
			<td align=left>Keterangan</td>
		</tr>
		</thead>
		";
		$str="select * from ".$dbname.".setup_kegiatan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmkeg[$bar['kodekegiatan']]=$bar['namakegiatan'];
			$nmsat[$bar['kodekegiatan']]=$bar['satuan'];
		}	
		$str="select * from ".$dbname.".project_dt";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmkeg[$bar['kegiatan']]=$bar['namakegiatan'];
			$nmsat[$bar['kegiatan']]=$bar['satuan'];
		}	
		$str="select * from ".$dbname.".organisasi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmblok[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		}
		$str="select * from ".$dbname.".project";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmblok[$bar['kode']]=$bar['nama'];
		}
		
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$param['notransaksi']."' and nopengajuan='".$param['nopengajuan']."' and tanggal='".$param['tanggal']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			
			$no++;
			@$harga=$bar['jumlahrealisasi']/$bar['hasilkerjarealisasi'];
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['tanggal']."</td>";
			$tab.="<td>".$nmkeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td>".$nmblok[$bar['kodeblok']]."</td>";
			$tab.="<td>".$nmsat[$bar['kodekegiatan']]."</td>";
			$tab.="<td align=right>".number_format($bar['hasilkerjarealisasi'])."</td>";
			$tab.="<td align=right>".number_format($harga)."</td>";
			$tab.="<td align=right>".number_format($bar['jumlahrealisasi'])."</td>";
			$tab.="<td>".$bar['keterangan']."</td>";
			$tab.="</tr>";
			@$ttl+=$bar['jumlahrealisasi'];
		}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=7>T O T A L</td>";
			$tab.="<td align=right>".number_format($ttl)."</td>";
			$tab.="<td></td>";
			$tab.="</tr>";
			$tab.="</table>";
			
			
		$tab.="
			<hr>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center' width=30px>Termin</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody>";
				$where='';
				if($param['termin']!='undefined'){
					$where= " and (termin='".$param['termin']."' or termin='')";
				}
				$path               = "fileupload/lgl_pengajuanspk/";
				$nopengajuan = makeOption($dbname,'log_spkht','notransaksi,nopengajuan',"notransaksi='".$param['notransaksi']."'");
				$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$nopengajuan[$param['notransaksi']]."' and status='1' ".$where.""; #exit("error".$str);
				$res=fetchData($str);
				if(empty($res)){
					$tab.="<tr class=rowcontent><td colspan=6 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}else{
					$no='';
					foreach($res as $key=>$val){
						$no++;
						$tab.="<tr class=rowcontent>
								<td style='text-align:center'>".$no."</td>";
						$icon=seticonfile($val['formaticon']);
						$tab.="<td style='text-align:center'>
								<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
							</td>";
						$nfile='';
						if(strlen($val['namafile'])>10){
							$nfile = $val['namafile'];
						}else{
							$nfile = $val['namafile'];
						}
						$tab.="<td style='text-align:center'>".($val['termin'])."</td>
								<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
						<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
							<td align=center>
								<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>";
						$tab."	</td>
							</tr>";
					}
				}
		$tab.="</tbody>
			</table>
		
		";
		$urlefil=checkPostGet('urlefil','0');
		if($urlefil!='0'){			
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();

			if (file_exists($urlefil)){
				unlink($urlefil);
			}
			file_put_contents($urlefil, $dompdf->output());
		}else{			
			echo $tab;		
		}
		
	break;
    default:
	break;
}
?>