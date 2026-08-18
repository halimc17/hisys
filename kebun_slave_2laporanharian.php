<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
//include_once('lib/rTable.php');
use Dompdf\Dompdf;
require_once('dompdf/autoload.inc.php');



$param = $_POST;
$method=checkPostGet('method','');
$kebun=checkPostGet('kebun','');
$divisi=checkPostGet('divisi','');
$kegiatan=checkPostGet('kegiatan','');
$konduktor=checkPostGet('konduktor','');
$typereport=checkPostGet('typereport','');
$kodekegiatan=checkPostGet('kodekegiatan','');
$tanggal=date("Y-m-d",strtotime(checkPostGet('tanggal','0000-00-00')));
function getArrayByArray($array1,$arrayParam){
	$result = array();
	foreach($arrayParam as $v){
		if(isset($array1[$v])){
			$result[] = array_shift($array1[$v]);
		}
	}
	return $result;
}
//echo $kebun.' '.$divisi.' '.$tanggal;

switch($method){
	default:
		
	break;
	case 'excel':
		$header="<tr class=rowheader>
						<th align=center colspan=10>".$_SESSION['lang']['lapkerja']."</th>
					</tr>				
					<tr class=rowheader>
						<th align=left colspan=4>".$_SESSION['lang']['kebun']."  : ".$kebun."</th>
						<th align=left colspan=6>".$_SESSION['lang']['tanggal']." : ".$tanggal."</th>
					</tr>	
					<tr class=rowheader>
						<th align=left colspan=10>".$_SESSION['lang']['afdeling']." : ".$divisi."</th>
					</tr>";

			$tab = resultpreview(1,$header,$ttd='true');
			$nop_ = "Daily_Report_and_Programme" . date('Ymd_His');
			if (strlen($tab) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $tab)) {
					echo "<script language=javascript1.2>
								parent.window.alert('Cant convert to excel format');
						  </script>";
				  exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
						  </script>";
				}
				closedir($handle);
			} 
	break;
	case 'preview':
		$button = "<a href=\"#\" onclick='printPDF()'><img src='images/skyblue/pdf.jpg' class='zImgBtn' title='Print'><span style=\"margin-left:5px;\">Print PDF</span></a>";
		
		echo resultpreview(0,$button,"true");
	break;
	case 'getdivisi':
		//divisi
		if($_SESSION['empl']['subbagian'] == ""){
			$optdivisi="<option value=''>Summary</option>";
			$Where  = " kodeorganisasi like '".$kebun."%'";
		}else{
			$Where  = " kodeorganisasi = '".$_SESSION['empl']['subbagian']."'";
		}
		$sdivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)=6
		and ".$Where." ";
		$res=$owlPDO->query($sdivisi) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
		}
		echo $optdivisi;
	break;
	case 'getkonduktor':
		$optkoductor="<option value=''>ALL</option>";
		$addDivisi = "";
		if($divisi != ""){
			$addDivisi = "and b.subbagian ='".$divisi."'";
		}
		$str="select distinct a.nikasisten,b.namakaryawan,b.namakaryawan2 from kebun_aktifitas a 
		left join datakaryawan b on a.nikasisten = b.karyawanid
		where a.tanggal = '".$tanggal."' and a.kodeorg = '".$kebun."' ".$addDivisi;
		//Exit("ERROR".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optkoductor.="<option value='".$bar['nikasisten']."'>".$bar['namakaryawan']." - ".$bar['namakaryawan2']."</option>";
		}
		echo $optkoductor;
	break;
	case 'pdf':
		
		$header="
		<table class=sortable cellspacing=0 cellpadding=7 style='width:100%'>
			<tr class=rowheader>
				<th align=center colspan=10>".getMenu('kebun_2laporanharian')."</th>
			</tr>	
			<tr class=rowheader>
				<th align=center colspan=10>".$_SESSION['lang']['tanggal']." : ".date("d M Y",strtotime($tanggal))."</th>
			</tr>			
			<tr class=rowheader>
				<th align=left colspan=4>".$_SESSION['lang']['kebun']."  : ".$kebun."</th>
			</tr>	
			<tr class=rowheader>
				<th align=left colspan=10>".$_SESSION['lang']['afdeling']." : ".$divisi."</th>
			</tr>
		</table>";
			
		$tab = resultpreview(1,$header,$ttd='true');
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream("Daily Report", array("Attachment" => false));
	break;
	case 'datadetail':
		$where 			= "";
		$allnotran 		= array();
		$allTransaksi 	= "";
		if($kegiatan == 'PNN'){
			$title = $_SESSION['lang']['panen'];
			$notran = array();
			$datanotransaksi = array();
			if($konduktor != ''){
				$where .= "and nikasisten='".$konduktor."'";
			}
			if($divisi!=''){
				$where.=" and left(kodeorg,6)='".$divisi."'";
			}
			//$str="select notransaksi,jurnal from ".$dbname.".kebun_aktifitas where tipetransaksi = 'PNN' and tanggal = '".$tanggal."' and kodeorg = '".$kebun."' ".$where." ";
			$str="select distinct notransaksi,jurnal from ".$dbname.".kebun_prestasi_vw where  tanggal = '".$tanggal."' and unit = '".$kebun."' ".$where." ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);        
			$d = array();
			while($bar=$res->fetch()){  
				$datanotransaksi[] 	=	"'".$bar['notransaksi']."'";
				$d['notransaksi']	=	$bar['notransaksi']; 
				$d['posting']		=	$bar['jurnal']; 
				$notran[] 			=   $d;
				//if($bar['jurnal'] != 1){
				//	$allnotran[]		= 	$bar['notransaksi']; 
				//}
			}
			
			$data = array();
			if(count($datanotransaksi) > 0){
				//$allnotran 		= "'".implode(",",$allnotran)."'";
				$allTransaksi 	= implode(",",$datanotransaksi);
				$str="select b.namakaryawan ,a.* from ".$dbname.".kebun_prestasi_vw a 
				left join datakaryawan b on a.karyawanid = b.karyawanid
				where a.notransaksi in (".$allTransaksi.")";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);        
				while($bar=$res->fetch()){            
					$data[$bar['notransaksi']][] = $bar; 
				}
			}
			# Posting --> Jabatan
			if($kegiatan=='PNN') {
				$app = 'panen';
			} else {
				$app = 'rawatkebun';
			}
			$postJabatan = getPostingJabatan($app);

			# Approvement Data
			$dataApprove = array();
			$queryApp = " select a.notransaksi,a.karyawanid,b.namakaryawan,a.status from approval a 
						  left join datakaryawan b on a.karyawanid = b.karyawanid where a.notransaksi in (".$allTransaksi.") 
						   and a.jenispersetujuan = 'PNN'
						  order by a.level";
			//exit("ERROR/".$allTransaksi);
			$dataApp = fetchData($queryApp);
			foreach($dataApp as $val){
				$dataApprove[$val['notransaksi']]['karyawanid'] 	= $val['karyawanid'];
				$dataApprove[$val['notransaksi']]['namakaryawan'] 	= $val['namakaryawan'];
				$dataApprove[$val['notransaksi']]['status'] 		= $val['status'];
			}
			$postingStat = "";
			if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
				$tab.="<div style='width:995px;height:30px;position:absolute;background-color:#275370;'>
						<span style=\"color:#CFC5CA;\"><input type=\"checkbox\" class=\"allselectivemode\" name=\"allselectivemode\" onchange=\"checkAll('selectivemode',this);\" checked> Check / Uncheck All</span>
						<button class=\"mybutton\" style=\"float:right;margin-right:20px;margin-top:5px;\" onclick=\"postingAlltransaction();\">SUBMIT</button>
					</div>";
				$postingStat = "true";
			}else if($_SESSION['empl']['kodejabatan'] == '74' ){
				$tab.="<div style='width:995px;height:30px;position:absolute;background-color:#275370;'>
						<span style=\"color:#CFC5CA;\"><input type=\"checkbox\" class=\"allselectivemode\" name=\"allselectivemode\" onchange=\"checkAll('selectivemode',this);\" checked> Check / Uncheck All</span>
						<button class=\"mybutton\" style=\"float:right;margin-right:20px;margin-top:5px;\" onclick=\"ApproveAlltransaction();\">SUBMIT</button>
					</div>";
				$tab.="<input type='hidden' id=\"idapprover\" value=\"".$_SESSION['standard']['userid']."\">";
				$tab.="<input type='hidden' id=\"levelapprover\" value=\"1\">";
				$tab.="<input type='hidden' id=\"jenispersetujuan\" value=\"PNN\">";
				$postingStat = "approval";
			}
			$tab.="<fieldset style=\"margin-top:30px;\"><legend>".$title."</legend>";
			foreach($notran as $val){
				$tab.="<table cellpadding=1 cellspacing=0 border=0 width=65% class=sortable><tbody class=rowcontent>";
				$tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
				$tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$val['notransaksi']."</td></tr>";
				if($divisi!=""){
					$tab.="<tr><td>".$_SESSION['lang']['divisi']."</td><td> :</td><td> ".$divisi."</td></tr>";
				}
				$tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td><td> :</td><td> ".date("d M Y",strtotime($tanggal))."</td></tr>";
				$strposting = "";
				$langTitleApp = "posting";
				if($postingStat == "true"){
					if(count($dataApprove)>0){
						if(isset($dataApprove[$val['notransaksi']])){
							$namaApp = $dataApprove[$val['notransaksi']]['namakaryawan'];
							$tab.="<tr><td>".$_SESSION['lang']['disetujui']."</td><td> :</td><td> ".$namaApp."</td></tr>";
						}else{
							$tab.="<tr><td>".$_SESSION['lang']['disetujui']."</td><td> :</td><td>-</td></tr>";
						}

						if($val['posting'] == 0){
							$strposting = "
							<span id='stat_".$val['notransaksi']."' style='color:red;'>";
							if(isset($dataApprove[$val['notransaksi']])){
								$strposting .= "<input type='checkbox' class='selectivemode' name='selectivemode' value='".$val['notransaksi']."' checked>";
							}
							$strposting .= "No Post</span>";
						}else if($val['posting'] == 1){
							$strposting = "<span id='stat_".$val['notransaksi']."' style='color:green;'>".$_SESSION['lang']['post']."</span>";
						}
					}else{
						$tab.="<tr><td>".$_SESSION['lang']['disetujui']." </td><td> :</td><td> Not Yet</td></tr>";
						$strposting = "
							<span id='stat_".$val['notransaksi']."' style='color:red;'>No Post</span>";
					}
					$langTitleApp = "posting";
				}else if($postingStat == "approval"){
					if(count($dataApprove)>0){
						if(isset($dataApprove[$val['notransaksi']])){
							$strposting = "<span id='stat_".$val['notransaksi']."' style='color:green;'>Checked by ".$dataApprove[$val['notransaksi']]['namakaryawan']."</span>";
							
						}else{
							$strposting = "
								<span id='stat_".$val['notransaksi']."' style='color:red;'><input type='checkbox' class='selectivemode' name='selectivemode' value='".$val['notransaksi']."' checked>Not yet checked</span>";
						}
					}else{
						
						$strposting = "
						<span id='stat_".$val['notransaksi']."' style='color:red;'><input type='checkbox' class='selectivemode' name='selectivemode' value='".$val['notransaksi']."' checked>Not yet checked</span>";
					}

					$langTitleApp = "disetujui";
				}
				$tab.="<tr><td>".$_SESSION['lang'][$langTitleApp]."</td><td> :</td><td> ".$strposting."</td></tr>";
				$tab.="</tbody></table>";
				$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%;'><thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<td  align=center>".$_SESSION['lang']['nik']."</td>";
				$tab.="<td  align=center>".$_SESSION['lang']['blok']."</td>";
				$tab.="<td  align=center>".$_SESSION['lang']['hasilkerja']."</td>";
				$tab.="<td  align=center>".$_SESSION['lang']['luaspanen']."</td>";
				$tab.="<td  align=center>".$_SESSION['lang']['brondol']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['penalty1']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['penalty2']."</td>";
				$tab.="</tr></thead><tbody>";
				if(isset($data[$val['notransaksi']])){
					$totJanjang = 0;
					$totLuas = 0;
					$totBrondolan = 0;
					$totPenalty1 = 0;
					$totPenalty2 = 0;
					foreach($data[$val['notransaksi']] as $key => $bar){
						$tab.="<tr class=rowcontent>";
							$tab.="<td>".$bar['namakaryawan']."</td>";
							$tab.="<td>".substr($bar['kodeorg'],6,10)."</td>";
							$tab.="<td align=right>".$bar['hasilkerja']."</td>";
							$tab.="<td align=right>".number_format($bar['luaspanen'],2)."</td>";
							$tab.="<td align=right>".number_format($bar['brondolan'],2)."</td>";
							$tab.="<td align=right>".number_format(@$bar['penalty1'],2)."</td>";
							$tab.="<td align=right>".number_format(@$bar['penalty2'],2)."</td>";
							
							//$sisa=($bar['upahkerja']-$bar['upahpenalty']+$bar['insentiflibur'])+($totPremi-$bar['rupiahpenalty']);
							//$tab.="<td align=right>".number_format($sisa,0)." </td>";
						$tab.="</tr>";
						$totJanjang+=@$bar['hasilkerja'];
						$totLuas+=@$bar['luaspanen'];
						$totBrondolan+=@$bar['brondolan'];
						$totPenalty1+=@$bar['penalty1'];
						$totPenalty2+=@$bar['penalty2'];
						
					}
				}
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=2 align=center>".$_SESSION['lang']['total']."</td>";
				$tab.="<td align=right>".number_format($totJanjang,0)."</td>";
				$tab.="<td align=right>".number_format($totLuas,2)."</td>";
				$tab.="<td align=right>".number_format($totBrondolan,0)."</td>";
				$tab.="<td align=right>".number_format($totPenalty1,0)."</td>";
				$tab.="<td align=right>".number_format($totPenalty2,0)."</td>";
				$tab.="</tr></tbody></table>";
				$tab.="<br>";
			}
			$tab.="</fieldset>";
			
		}else if($kegiatan == 'SPB'){
			$title = $_SESSION['lang']['spb'];
		}else if($kegiatan == 'KONTRAK'){
			$title = $_SESSION['lang']['spk'];
			$wherekontrak='';
			if ($kebun !=''){
				$wherekontrak.=" and a.kodeblok like '".$kebun."%'";
			}
			if ($divisi !=''){
				$wherekontrak.=" and a.kodeblok like '".$divisi."%'";
			}
			if ($tanggal !=''){
				$wherekontrak.=" and a.tanggal = '".$tanggal."'";
			}
			if ($kodekegiatan !=''){
				$wherekontrak.=" and a.kodekegiatan = '".$kodekegiatan."'";
			}
			$str = "select a.*,IFNULL(b.namakegiatan,'') as namakegiatan from ".$dbname.".log_baspk a
			left join ".$dbname.".setup_kegiatan b on b.kodekegiatan=a.kodekegiatan
			where 1=1 ".$wherekontrak."";
			$data = fetchData($str);
			$datanotransaksi = array();
			$allNotransaksi = array();
			if(count($data) > 0){
				foreach($data as $k=>$v){
					$allNotransaksi[] = $v['notransaksi'];
					$datanotransaksi[] = $v['notransaksi']."/".$v['kodekegiatan']."/".$v['tanggal']."/".$v['kodeblok'];
				}
			}
			if(count($allNotransaksi) > 0){
				//get data spk
				$allNotransaksi = array_unique($allNotransaksi);
				$allNotransaksi 	= "'".implode("','",$allNotransaksi)."'";
				$str1="select a.notransaksi,a.kodekegiatan,a.kodeblok,a.rupiahpersatuan, b.kodeorg, b.koderekanan from ".$dbname.".log_spkdt a left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi 
				where a.notransaksi in (".$allNotransaksi.")";
				$data_spk = fetchData($str1);
				foreach($data_spk as $k=>$v){
					$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['kodeblok'] 			= $v['kodeblok'];
					$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['kodeorg'] 			= $v['kodeorg'];
					$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['rupiahpersatuan'] 	= $v['rupiahpersatuan'];
					$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['koderekanan'] 		= $v['koderekanan'];
				}
			}
			$allTransaksi 	= "'".implode("','",$datanotransaksi)."'";
			$app = "baspk";
			$postJabatan = getPostingJabatan($app);
			# Approvement Data
			$dataApprove = array();
			if(count($datanotransaksi) > 0){
				$queryApp = " select a.notransaksi,a.karyawanid,b.namakaryawan,a.status from approval a 
							  left join datakaryawan b on a.karyawanid = b.karyawanid where a.notransaksi in (".$allTransaksi.") 
							   and a.jenispersetujuan = 'baspk'
							  order by a.level";
				//exit("ERROR/".$allTransaksi);
				$dataApp = fetchData($queryApp);
				foreach($dataApp as $val){
					$dataApprove[$val['notransaksi']]['karyawanid'] 	= $val['karyawanid'];
					$dataApprove[$val['notransaksi']]['namakaryawan'] 	= $val['namakaryawan'];
					$dataApprove[$val['notransaksi']]['status'] 		= $val['status'];
				}
			}
			if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
				$tab.="<div style='width:995px;height:30px;position:absolute;background-color:#275370;'>
							<span style=\"color:#CFC5CA;\"><input type=\"checkbox\" class=\"allselectivemode\" name=\"allselectivemode\" onchange=\"checkAll('selectivemode',this);\" checked> Check / Uncheck All</span>
							<button class=\"mybutton\" style=\"float:right;margin-right:20px;margin-top:5px;\" onclick=\"postingAllbaspk();\">SUBMIT</button>
						</div>";
				$postingStat = "true";
			}else if($_SESSION['empl']['kodejabatan'] == '74' ){
				$tab.="<div style='width:995px;height:30px;position:absolute;background-color:#275370;'>
						<span style=\"color:#CFC5CA;\"><input type=\"checkbox\" class=\"allselectivemode\" name=\"allselectivemode\" onchange=\"checkAll('selectivemode',this);\" checked> Check / Uncheck All</span>
						<button class=\"mybutton\" style=\"float:right;margin-right:20px;margin-top:5px;\" onclick=\"ApproveAllbaspk();\">SUBMIT</button>
					</div>";
				$tab.="<input type='hidden' id=\"idapprover\" value=\"".$_SESSION['standard']['userid']."\">";
				$tab.="<input type='hidden' id=\"levelapprover\" value=\"1\">";
				$tab.="<input type='hidden' id=\"jenispersetujuan\" value=\"BASPK\">";
				$postingStat = "approval";
			}
			$tab.="<fieldset style=\"margin-top:30px;\"><legend>".$title."</legend>";
			$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable>
					<thead>
						<tr class='rowheader'>
							<th>".$_SESSION['lang']['nourut']."</th>
							<th>".$_SESSION['lang']['kegiatan']."</th>
							<th>".$_SESSION['lang']['subunit']."</th>
							<th>".$_SESSION['lang']['tanggal']."</th>
							<th>".$_SESSION['lang']['hkrealisasi']."</th>
							<th>".$_SESSION['lang']['hasilkerjarealisasi']."</th>
							<th>".$_SESSION['lang']['jumlahrealisasi']."</th>
							<th>".$_SESSION['lang']['disetujui']."</th>
							<th>".$_SESSION['lang']['action']."</th>
						</tr>
					</thead>
			<tbody>";
			
			if(count($data) > 0){
				foreach($data as $k=>$v){
					
					$blokalokasi		= @$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['kodeblok'];
					$kodeorg 			= @$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['kodeorg'];
					$rupiahpersatuan 	= @$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['rupiahpersatuan'];
					$koderekanan 		= @$dt_spk[$v['notransaksi']][$v['kodekegiatan']][$v['kodeblok']]['koderekanan'];
					
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td>".($k+1)."</td>";
					$tab .= "<td>".$v['namakegiatan']."</td>";
					$tab .= "<td>".date("d F Y",strtotime($v['tanggal']))."</td>";
					$tab .= "<td>".$v['kodeblok']."</td>";
					$tab .= "<td>".$v['hkrealisasi']."</td>";
					$tab .= "<td>".$v['hasilkerjarealisasi']."</td>";
					$tab .= "<td>".$v['jumlahrealisasi']."</td>";
					$notransaksi = $v['notransaksi']."/".$v['kodekegiatan']."/".$v['tanggal']."/".$v['kodeblok'];
					if($postingStat == "true"){
						if(count($dataApprove)>0){
							if(isset($dataApprove[$notransaksi])){
								$namaApp = $dataApprove[$notransaksi]['namakaryawan'];
								$tab.="<td>".$namaApp."</td>";
							}else{
								$tab.="<td>-</td>";
							}

							if($v['statusjurnal'] == 0){
								$tab .= "<td>
								<span id='stat_".$notransaksi."' style='color:red;'>";
								if(isset($dataApprove[$notransaksi])){
									$tab .= "<input type='checkbox' class='selectivemode' name='selectivemode' value='".$notransaksi."' 
									notransaksi=\"".$v['notransaksi']."\"
									kodekegiatan=\"".$v['kodekegiatan']."\"
									kodeblok=\"".$v['kodeblok']."\"
									tanggal=\"".tanggalnormal($v['tanggal'])."\"
									kodeorg=\"".$kodeorg."\"
									koderekanan=\"".$koderekanan."\"
									kodesegment=\"".$v['kodesegment']."\"
									blokalokasi=\"".$blokalokasi."\"
									jumlahrealisasi=\"".$v['jumlahrealisasi']."\"
									checked>";
								}
								$tab .= "</td>";
							}else if($v['statusjurnal'] == 1){
								$tab .= "<td><span id='stat_".$notransaksi."' style='color:green;'>".$_SESSION['lang']['post']."</span></td>";
							}
						}else{
							$tab.="<td>Not Yet</td>";
							$tab .= "<td><span id='stat_".$notransaksi."' style='color:red;'>No Post</span></td>";
						}
						$langTitleApp = "posting";
					}else if($postingStat == "approval"){
						if(count($dataApprove)>0){
							if(isset($dataApprove[$notransaksi])){
								$tab .= "<td><span id='stat_".$notransaksi."' style='color:green;'>".$dataApprove[$notransaksi]['namakaryawan']."</span></td>";
								
							}else{
								$tab .= "
									<td><span id='stat_".$notransaksi."' style='color:red;'><input type='checkbox' class='selectivemode' name='selectivemode' value='".$notransaksi."' checked></span></td>";
							}
						}else{
							
							$tab .= "<td><span id='stat_".$notransaksi."' style='color:red;'><input type='checkbox' class='selectivemode' name='selectivemode' value='".$notransaksi."' checked></span></td>";
						}
						if($v['statusjurnal'] == 0){
							$tab .= "<td><span id='stat_".$notransaksi."' style='color:red;'>No Post</span></td>";
						}else{
							$tab .= "<td><span id='stat_".$notransaksi."' style='color:green;'>".$_SESSION['lang']['post']."</span></td>";
						}
						$langTitleApp = "disetujui";
					}
					$tab .= "</tr>";
				}
			}
			$tab.="</tbody></table>";
			$tab .= "</fieldset>";
			
		}else if($kegiatan == 'ABSEN'){
			$title = "ABSENCE";
			
			$whereabsens='';
			if ($kebun !=''){
				$whereabsens.=" and a.kodeorg like '".$kebun."%'";
			}
			if ($divisi !=''){
				$whereabsens.=" and a.kodeorg like '".$divisi."%'";
			}
			if ($tanggal !=''){
				$whereabsens.=" and a.tanggal = '".$tanggal."'";
			}
			$str = "select a.*,b.namakaryawan,b.namakaryawan2,c.namajabatan from ".$dbname.".sdm_absensidt_vw a 
			left join datakaryawan b on a.karyawanid = b.karyawanid
			left join sdm_5jabatan c on a.kodejabatan = c.kodejabatan
			where 1=1 ".$whereabsens;
			$data = fetchData($str);
			$datadetail = array();
			foreach($data as $k => $bar)
			{
				$datadetail[$bar['tanggal']."/".$bar['kodeorg']][] = $bar;
			}
			$strHT = "select a.tanggal,a.kodeorg,a.posting,a.postingby,b.namakaryawan from ".$dbname.".sdm_absensiht a 
			left join datakaryawan b on a.postingby = b.karyawanid
			where 1=1 ".$whereabsens;
			$dataHT = fetchData($strHT);
			if(count($dataHT) > 0){
				$app = "absen";
				$postJabatan = getPostingJabatan($app);
				if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
					$tab.="<div style='width:995px;height:30px;position:absolute;background-color:#275370;'>
								<span style=\"color:#CFC5CA;\"><input type=\"checkbox\" class=\"allselectivemode\" name=\"allselectivemode\" onchange=\"checkAll('selectivemode',this);\" checked> Check / Uncheck All</span>
								<button class=\"mybutton\" style=\"float:right;margin-right:20px;margin-top:5px;\" onclick=\"postingAllabsen();\">SUBMIT</button>
							</div>";
					$postingStat = "true";
				}
				//Absensi
				$tab.="<fieldset style='margin-top:30px;'><legend> Absence </legend>";
				foreach($dataHT as $kHt => $barHT){
					$tab.= "<table><tr>";
					$tab.="<td><span>".$barHT['kodeorg']."</span></td>";
					if($barHT['posting'] != '1'){
						$tab.="<td>Posting by : <span id='stat_".$barHT['tanggal']."/".$barHT['kodeorg']."' style='color:red;'><input type='checkbox' class='selectivemode' name='selectivemode' value='".$barHT['tanggal']."/".$barHT['kodeorg']."' checked>Not Yet</span></td>";
					}else{
						$tab.="<td><span>Posting by : ".$barHT['namakaryawan']."</span></td>";
					}
					$tab.= "</tr></table>";
					$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable>
					<thead>
						<tr class='rowheader'>
							<th>".$_SESSION['lang']['namakaryawan']."</th>
							<th>Khemer ".$_SESSION['lang']['namakaryawan']."</th>
							<th>".$_SESSION['lang']['tanggal']."</th>
							<th>".$_SESSION['lang']['jabatan']."</th>
							<th>".$_SESSION['lang']['hk']."</th>
							<th>".$_SESSION['lang']['umr']."</th>
							<th>".$_SESSION['lang']['absensi']."</th>
							<th>".$_SESSION['lang']['penjelasan']."</th>
						</tr>
					</thead>
					<tbody>";
					if(isset($datadetail[$barHT['tanggal']."/".$barHT['kodeorg']])){
						$data2 = $datadetail[$barHT['tanggal']."/".$barHT['kodeorg']];
						//print_r($data2);
						//exit('ERROR');
						foreach($data2 as $bar)
						{
							// tambah lagi
							$tab .= "<tr class=rowcontent>";
							$tab .= "<td>".$bar['namakaryawan']."</td>";
							$tab .= "<td>".$bar['namakaryawan2']."</td>";
							$tab .= "<td>".date("d F Y",strtotime($bar['tanggal']))."</td>";
							$tab .= "<td>".$bar['namajabatan']."</td>";
							$tab .= "<td>".$bar['nilaihk']."</td>";
							$tab .= "<td>".$bar['umr']."</td>";
							$tab .= "<td>".$bar['absensi']."</td>";
							$tab .= "<td>".$bar['penjelasan']."</td>";
							$tab .= "</tr>";
						}
					}
					$tab.= "<tbody></table><br/>";
					}
				$tab.="</fieldset>";
			}
		}else{
			$optSatKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
			$optNamaKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
			$optNIKary=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
			$optNamaBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
			$optGudang=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
			$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);
			$notran = array();
			$datanotransaksi = array();
			if($kegiatan != ''){
				$where .= "and b.kodekegiatan='".$kegiatan."'";
			}
			if($konduktor != ''){
				$where .= "and a.nikasisten='".$konduktor."'";
			}
			if($divisi!=''){
				$where.=" and left(b.kodeorg,6)='".$divisi."'";
			}
			$str="select a.notransaksi,a.jurnal,c.namakegiatan,c.satuan from ".$dbname.".kebun_aktifitas a 
			left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
			left join ".$dbname.".setup_kegiatan c on b.kodekegiatan=c.kodekegiatan
			where a.tipetransaksi <> 'PNN' and a.tanggal = '".$tanggal."' and a.kodeorg = '".$kebun."' ".$where." ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);        
			$d = array();
			while($bar=$res->fetch()){  
				$datanotransaksi[] 	=	"'".$bar['notransaksi']."'";
				$d['notransaksi']	=	$bar['notransaksi']; 
				$d['posting']		=	$bar['jurnal']; 
				$d['namakegiatan']	=	$bar['namakegiatan']; 
				$d['satuan']		=	$bar['satuan']; 
				$notran[] 			=   $d;
				//if($bar['jurnal'] 	!= 1){
				//	$allnotran[]	= 	$bar['notransaksi']; 
				//}
			}
			
			$data = array();
			if(count($datanotransaksi) > 0){
				//$allnotran 		= "'".implode(",",$allnotran)."'";
				$allTransaksi 	= implode(",",$datanotransaksi);
				#umr gua gnati buat jumlahhk
				$str="select distinct sum(a.insentif) as upahpremi,sum(a.jhk) as jumlahhk,kodekegiatan,
                tanggal,b.kodeorg,b.hasilkerja,a.notransaksi,b.jumlahhk as umr from ".$dbname.".kebun_kehadiran a 
				left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
                left join ".$dbname.".kebun_aktifitas c on a.notransaksi=c.notransaksi 
				where a.notransaksi in (".$allTransaksi.") 
				group by a.notransaksi ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);  				
				while($bar=$res->fetch()){            
					$data[$bar['notransaksi']] = $bar; 
				}
			}
			# Approvement Data
			$dataApprove = array();
			$queryApp = " select a.notransaksi,a.karyawanid,b.namakaryawan,a.status from approval a 
						  left join datakaryawan b on a.karyawanid = b.karyawanid where a.notransaksi in (".$allTransaksi.") 
						  and a.jenispersetujuan = 'PRW'
						  order by a.level";
			//exit("ERROR/".$allTransaksi);
			$dataApp = fetchData($queryApp);
			foreach($dataApp as $val){
				$dataApprove[$val['notransaksi']]['karyawanid'] 	= $val['karyawanid'];
				$dataApprove[$val['notransaksi']]['namakaryawan'] 	= $val['namakaryawan'];
				$dataApprove[$val['notransaksi']]['status'] 		= $val['status'];
			}
			# Posting --> Jabatan
			if($kegiatan=='PNN') {
				$app = 'panen';
			} else {
				$app = 'rawatkebun';
			}
			$postJabatan = getPostingJabatan($app);
			if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
			$tab.="<div style='width:995px;height:30px;position:absolute;background-color:#275370;'>
						<span style=\"color:#CFC5CA;\"><input type=\"checkbox\" class=\"allselectivemode\" name=\"allselectivemode\" onchange=\"checkAll('selectivemode',this);\" checked> Check / Uncheck All</span>
						<button class=\"mybutton\" style=\"float:right;margin-right:20px;margin-top:5px;\" onclick=\"postingAlltransaction();\">SUBMIT</button>
					</div>";
			$postingStat = "true";
			}else if($_SESSION['empl']['kodejabatan'] == '74' ){
				$tab.="<div style='width:995px;height:30px;position:absolute;background-color:#275370;'>
						<span style=\"color:#CFC5CA;\"><input type=\"checkbox\" class=\"allselectivemode\" name=\"allselectivemode\" onchange=\"checkAll('selectivemode',this);\" checked> Check / Uncheck All</span>
						<button class=\"mybutton\" style=\"float:right;margin-right:20px;margin-top:5px;\" onclick=\"ApproveAlltransaction();\">SUBMIT</button>
					</div>";
				$tab.="<input type='hidden' id=\"idapprover\" value=\"".$_SESSION['standard']['userid']."\">";
				$tab.="<input type='hidden' id=\"levelapprover\" value=\"1\">";
				$tab.="<input type='hidden' id=\"jenispersetujuan\" value=\"PRW\">";
				$postingStat = "approval";
			}
			$tab.="<fieldset style='margin-top:30px;'><legend>".$notran[0]['namakegiatan']."</legend>";
			foreach($notran as $val){
				$tab.="<fieldset style='background:#fff;'>";
				$tab.="<table cellpadding=1 cellspacing=0 border=0 ><tbody >";
				$tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
				if($divisi!=""){
					$tab.="<tr><td>".$_SESSION['lang']['divisi']."</td><td> :</td><td> ".$divisi."</td></tr>";
				}
				$tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td><b> ".$val['notransaksi']."</b></td></tr>";
				$tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td><td> :</td><td><b> ".tanggalnormal($tanggal)."</b></td></tr>";
				$strposting = "";
				if($postingStat == "true"){
					if(count($dataApprove)>0){
						if(isset($dataApprove[$val['notransaksi']])){
							$namaApp = $dataApprove[$val['notransaksi']]['namakaryawan'];
							$tab.="<tr><td>".$_SESSION['lang']['disetujui']."</td><td> :</td><td> ".$namaApp."</td></tr>";
						}else{
							$tab.="<tr><td>".$_SESSION['lang']['disetujui']."</td><td> :</td><td>-</td></tr>";
						}

						if($val['posting'] == 0){
							$strposting = "
							<span id='stat_".$val['notransaksi']."' style='color:red;'>";
							if(isset($dataApprove[$val['notransaksi']])){
								$strposting .= "<input type='checkbox' class='selectivemode' name='selectivemode' value='".$val['notransaksi']."' checked>";
							}
							$strposting .= "No Post</span>";
						}else if($val['posting'] == 1){
							$strposting = "<span id='stat_".$val['notransaksi']."' style='color:green;'>".$_SESSION['lang']['post']."</span>";
						}
					}else{
						$tab.="<tr><td>".$_SESSION['lang']['disetujui']." </td><td> :</td><td> Not Yet</td></tr>";
						$strposting = "
							<span id='stat_".$val['notransaksi']."' style='color:red;'>No Post</span>";
					}
					$langTitleApp = "posting";
				}else if($postingStat == "approval"){
					if(count($dataApprove)>0){
						if(isset($dataApprove[$val['notransaksi']])){
							$strposting = "<span id='stat_".$val['notransaksi']."' style='color:green;'>Checked by ".$dataApprove[$val['notransaksi']]['namakaryawan']."</span>";
							
						}else{
							$strposting = "
								<span id='stat_".$val['notransaksi']."' style='color:red;'><input type='checkbox' class='selectivemode' name='selectivemode' value='".$val['notransaksi']."' checked>Not yet checked</span>";
						}
					}else{
						
						$strposting = "
						<span id='stat_".$val['notransaksi']."' style='color:red;'><input type='checkbox' class='selectivemode' name='selectivemode' value='".$val['notransaksi']."' checked>Not yet checked</span>";
					}

					$langTitleApp = "disetujui";
				}
				$tab.="<tr><td>".$_SESSION['lang'][$langTitleApp]."</td><td> :</td><td> ".$strposting."</td></tr>";
				$tab.="</tbody></table>";
				$tab.="<br /><b>".$titleDetail[0]."<b><br />";
				$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable ><thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<td align=center>".$_SESSION['lang']['blok']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['namakegiatan']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['hasilkerja']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['jumlahhk']."</td>";
				//$tab.="<td align=center>".$_SESSION['lang']['umr']."</td>";
				$tab.="</tr></thead><tbody>";
				
				$rPres=$data[$val['notransaksi']];
		  
					  //'tanggal,kodekegiatan,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
					 $tab.="<tr class=rowcontent>";
					 $tab.="<td>".$rPres['kodeorg']."</td>";
					 $tab.="<td>".$val['namakegiatan']."</td>";
					 $tab.="<td align=right>".number_format($rPres['hasilkerja'],2)."</td>";
					 $tab.="<td>".$val['satuan']."</td>";
					 //$tab.="<td align=right>".number_format($rPres['upahpremi'],0)."</td>";
					 $tab.="<td align=right>".number_format($rPres['umr'],2)."</td>";
					 $tab.="</tr>";
				 $tab.="</table>";
				 $tab.="<br /><b>".$titleDetail[1]."</b><br />";
			  
					$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
					$tab.="<tr class=rowheader>";
					$tab.="<td align=center>No</td>";
					$tab.="<td align=center>".$_SESSION['lang']['nama']."</td>";
					//$tab.="<td align=center>".$_SESSION['lang']['absensi']."</td>";
					//$tab.="<td align=center>".$_SESSION['lang']['hasilkerja']."</td>";
					$tab.="<td align=center>".$_SESSION['lang']['jhk']."</td>";
					$tab.="<td align=center>".$_SESSION['lang']['upahharian']."</td>";
					
					//$tab.="<td align=center>".$_SESSION['lang']['insentif']."</td>";
					$tab.="</tr></thead><tbody>";
					$totJhk=$totUmr=$totInsentif=$tothasilkerja=0;
					$sKhdrn="select distinct * from ".$dbname.".kebun_kehadiran where notransaksi='".$val['notransaksi']."'";
					$qKhdrn=$owlPDO->query($sKhdrn) or die(print " Gagal: ".PDOException::getMessage());
					$qKhdrn->setFetchMode(PDO::FETCH_ASSOC);                       
					@$no='';
					while($rKhdrn=$qKhdrn->fetch())
					{
						 @$no+=1;
						 $tab.="<tr class=rowcontent>";
						 $tab.="<td align=center>".$no."</td>";
						 $tab.="<td>".@$optNIKary[$rKhdrn['nik']]." - ".@$optNamaKary[$rKhdrn['nik']]."</td>";
						 //$tab.="<td align=center>".$rKhdrn['absensi']."</td>";
						 //$tab.="<td  align=right>".number_format($rKhdrn['hasilkerja'],2)."</td>";
						 $tab.="<td align=right>".$rKhdrn['jhk']."</td>";
						 $tab.="<td  align=right>".number_format($rKhdrn['umr'],2)."</td>";
						 
						 //$tab.="<td  align=right>".number_format($rKhdrn['insentif'],2)."</td>";
						 $tab.="</tr>";
						 $totJhk+=$rKhdrn['jhk'];
						 $totUmr+=$rKhdrn['umr'];
						 $totInsentif+=$rKhdrn['insentif'];
						 $tothasilkerja+=$rKhdrn['hasilkerja'];
					}
					 $tab.="<tr class=rowcontent>";
					 $tab.="<td colspan=2>".$_SESSION['lang']['total']."</td>";
					 $tab.="<td  align=right>".$totJhk."</td>";
					 $tab.="<td  align=right>".number_format($totUmr,2)."</td>";
					 //$tab.="<td  align=right>".number_format($tothasilkerja,2)."</td>";
					 //$tab.="<td  align=right>".number_format($totInsentif,2)."</td>";
					 $tab.="</tr>";
				 $tab.="</table><br/><b>".$titleDetail[2]."</b><br />";
				$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable ><thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<td align=center>".$_SESSION['lang']['blok']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['kodebarang']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['kwantitas']." ".$_SESSION['lang']['material']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
				$tab.="<td align=center>".$_SESSION['lang']['kwantitas']." (".$val['satuan'].")</td>";
				//$tab.="<td align=center>".$_SESSION['lang']['hargasatuan']."</td>";
				//$tab.="<td align=center>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['sloc']."</td>";
				$tab.="</tr></thead><tbody>";
				$sMat="select distinct * from ".$dbname.".kebun_pakaimaterial where notransaksi='".$val['notransaksi']."'";
				$qMat=$owlPDO->query($sMat) or die(print " Gagal: ".PDOException::getMessage());
				$qMat->setFetchMode(PDO::FETCH_ASSOC);
				while($rMat=$qMat->fetch()){
					$sSatuan="select satuan from ".$dbname.".log_5masterbarang where kodebarang='".$rMat['kodebarang']."'";
					$rSatuan=fetchData($sSatuan);
					$satuan=$rSatuan[0]['satuan'];
					$kuantitas=$rMat['kwantitas'];
					if($satuan=='KG'){
						$kuantitas=$rMat['kwantitas']/1000;
						$satuan="MT";
					}
					$tab.="<tr class=rowcontent>";
					$tab.="<td>".$rMat['kodeorg']."</td>";
					$tab.="<td>".$rMat['kodebarang']."-".$optNamaBrg[$rMat['kodebarang']]."</td>";
					$tab.="<td align=right>".number_format($kuantitas,2)."</td>";
					$tab.="<td align=right>".$satuan."</td>";
					$tab.="<td align=right>".$rMat['kwantitasha']."</td>";
					//$tab.="<td align=right>".$rMat['hargasatuan']."</td>";
					//$whr="notransaksireferensi='".$rMat['notransaksi']."'";
					//$optTrnsGdng=makeOption($dbname,'log_transaksiht','notransaksireferensi,notransaksi',$whr);
					//$tab.="<td>".(isset($optTrnsGdng[$rMat['notransaksi']]) ? $optTrnsGdng[$rMat['notransaksi']] : "")."</td>";
					$tab.="</tr>";
				}
				$tab.="</table></fieldset><br />";
			}
			$tab.="</fieldset>";
		}
        echo "<div style='overflow:scroll;width:995px;height:496px;'>";
        echo $tab;
        echo "</div>";
	break;
}


function resultpreview($border,$header,$ttd){
	global $dbname;
	global $owlPDO;
	global $kebun;
	global $divisi;
	global $tanggal;
	global $konduktor;
	global $typereport;
	
	if(empty($border)){
		$border = 0;
		$cellspacing = 1;
	}else if($border == 1){
		$cellspacing = 0;
	}
	if(empty($header)){
		$header = "";
	}
	
        $tab= $header."
		<div class='table-scroll'>
		<table class=\"sortable\" cellspacing=\"".$cellspacing."\" cellpadding=\"7\" border=\"".$border."\" style=\"width:100%;\">
              <thead>				
                <tr class=rowheader>
                    <th align=\"center\" colspan=\"4\" rowspan=\"2\">".$_SESSION['lang']['jeniskerja']."</th>
					<th align=\"center\" rowspan=\"2\">".$_SESSION['lang']['blok']."</th>              
                    <th align=\"center\" rowspan=\"2\">".$_SESSION['lang']['kerjatelahselesai']."</th>
					<th align=\"center\" rowspan=\"2\">".$_SESSION['lang']['biayaunit']."</th>
					<th align=\"center\" colspan=\"5\">".$_SESSION['lang']['sumberdaya']."</th>
                    <th align=\"center\" rowspan=\"2\">".$_SESSION['lang']['remark']."</th>
                </tr>	
				<tr class=rowcontent>
					<th align=\"center\">".$_SESSION['lang']['pekerja']."</th>
					<th align=\"center\">".$_SESSION['lang']['alatmesin']."</th>	
					<th align=\"center\">".$_SESSION['lang']['unit']."</th>	
					<th align=\"center\">".$_SESSION['lang']['material']."</th>	
					<th align=\"center\">".$_SESSION['lang']['nomor']."</th>
				</tr>	
				
              </thead>";

		//untuk menampilkan kiriman divisi dan kebun
  
		$where2='';
		if ($kebun !=''){
			$where2.=" and b.kodeorg = '".$kebun."'";
		}
		if ($tanggal !=''){
			$where2.=" and b.tanggal = '".$tanggal."'";
		}
		if ($divisi !=''){
			$where2.=" and a.blok like '".$divisi."%'";
		}
		if ($konduktor !=''){
			$where2.=" and b.kerani = '".$konduktor."'";
		}
		//exit("ERROR: ASU". $where2);
		$str = "select c.statusblok,a.nospb,a.jjg,a.bjr,a.brondolan,a.kgbjr,a.blok as kodeorg
		from ".$dbname.".kebun_spbdt a 
		left join ".$dbname.".kebun_spbht b on a.nospb=b.nospb 
		left join ".$dbname.".setup_blok c on a.blok=c.kodeorg 
		where 1=1 ".$where2."";
		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$data = array();
		
		while ($bar=$res->fetch())
		{
			$bar['kodekegiatan'] = 'SPB';
			$bar['namakegiatan'] = 'FFB Transport';
			$bar['satuan'] = 'Mt';
			//$listnotransaksi[]																= "'".$bar['nospb']."'";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodekegiatan']						=$bar['kodekegiatan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['namakegiatan']						=$bar['namakegiatan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['tipetransaksi']					= "SPB";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['satuan']							= $bar['satuan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['statusblok']						= $bar['statusblok'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodeorg'][$bar['kodeorg']]			=$bar['kodeorg'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeorg']]['mesin']	=$bar['nopol'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeorg']]['unit']	= 1;
			$data[$bar['statusblok']][$bar['kodekegiatan']]['blok'][$bar['kodeorg']]			=substr($bar['kodeorg'],6,10);
			
			if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']])){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']]	=$bar['jjg'];
			}else{
				$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']]	+= $bar['jjg'];
			}
			$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	= "-";
			
			//$data[$bar['statusblok']][$bar['kodekegiatan']]['namabarang'][$bar['kodeorg']]['barang']	= '2';
			//$data[$bar['statusblok']][$bar['kodekegiatan']]['kwantitas'][$bar['kodeorg']]['barang']		= '3';
			//$data[$bar['statusblok']][$bar['kodekegiatan']]['satuanbarang'][$bar['kodeorg']]['satuan']	= '4';	
		}
		//exit("ERROR: ASU");
		//Absensi
		$whereabsens='';
		if ($kebun !=''){
			$whereabsens.=" and a.kodeorg like '".$kebun."%'";
		}
		if ($divisi !=''){
			$whereabsens.=" and a.kodeorg like '".$divisi."%'";
		}
		if ($tanggal !=''){
			$whereabsens.=" and a.tanggal = '".$tanggal."'";
		}

		$str = "select a.*,b.posting from ".$dbname.".sdm_absensidt_vw a 
		left join ".$dbname.".sdm_absensiht b on a.kodeorg = b.kodeorg and a.tanggal = b.tanggal
		where 1=1 ".$whereabsens."";
		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch())
		{
			//exit("ERROR".$str);
			$bar['kodekegiatan'] = 'ABSEN';
			$bar['namakegiatan'] = 'ABSENCE';
			$bar['statusblok']	 = 'ABSEN';
			if($bar['posting'] == '0' or $bar['posting'] == ''){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['jurnal']						= 'false';
			}
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodekegiatan']						= $bar['kodekegiatan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['namakegiatan']						= $bar['namakegiatan'] ;
			$data[$bar['statusblok']][$bar['kodekegiatan']]['tipetransaksi']					= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['satuan']							= "-";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['statusblok']						= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodeorg'][$bar['kodeorg']]			= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeorg']]['mesin']	= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeorg']]['unit']	= 1;
			$data[$bar['statusblok']][$bar['kodekegiatan']]['blok'][$bar['kodeorg']]			= substr($bar['kodeorg'],6,10);
			
			if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']])){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	= 1;
			}else{
				$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	+= 1;
			}
			$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']]	= "-";
			
		}
		//Contract
		$wherecontract='';
		if ($kebun !=''){
			$wherecontract.=" and a.kodeblok like '".$kebun."%'";
		}
		if ($divisi !=''){
			$wherecontract.=" and a.kodeblok like '".$divisi."%'";
		}
		if ($tanggal !=''){
			$wherecontract.=" and a.tanggal = '".$tanggal."'";
		}
		
		$str = "select a.*,IFNULL(b.namakegiatan,'') as namakegiatan from ".$dbname.".log_baspk a
		left join ".$dbname.".setup_kegiatan b on b.kodekegiatan=a.kodekegiatan 
		where 1=1 ".$wherecontract."";
		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch())
		{
			//exit("ERROR".$str);
			$bar['statusblok']	 = 'KONTRAK';
			if($bar['statusjurnal'] == '0' or $bar['statusjurnal'] == ''){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['jurnal']						= 'false';
			}
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodekegiatan']						= $bar['kodekegiatan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['namakegiatan']						= $bar['namakegiatan'] ;
			$data[$bar['statusblok']][$bar['kodekegiatan']]['tipetransaksi']					= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['satuan']							= "-";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['statusblok']						= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodeorg'][$bar['kodeblok']]		= $bar['kodeblok'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeblok']]['mesin']	= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeblok']]['unit']	= "";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['blok'][$bar['kodeblok']]			= substr($bar['kodeblok'],6,10);
			
			if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeblok']])){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeblok']]	= (double)$bar['hkrealisasi'];
			}else{
				$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeblok']]	+= (double)$bar['hkrealisasi'];
			}
			if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeblok']])){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeblok']]	= (double)$bar['hasilkerjarealisasi'];
			}else{
				$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeblok']]	+= (double)$bar['hasilkerjarealisasi'];
			}
			
		}
		$where='';
		if ($kebun !=''){
			$where.=" and a.kodeorg like '".$kebun."%'";
		}
		if ($divisi !=''){
			$where.=" and a.kodeorg like '".$divisi."%'";
		}
		if ($tanggal !=''){
			$where.=" and b.tanggal = '".$tanggal."'";
		}
		if ($konduktor !=''){
			$where.=" and b.nikasisten = '".$konduktor."'";
		}
		// Print_r($data);
		// exit("ERROR".$konduktor);
		//query status blok, jenis kegiatan, satuan, work complete, jumlah pekerja 
		$str = "select a.notransaksi,b.jurnal,b.tipetransaksi,a.kodekegiatan,
		IFNULL(e.namakegiatan,'') as namakegiatan,
		IFNULL(e.satuan,'') as satuan,
		a.kodeorg,a.jumlahhk as jumlahpekerja,b.tanggal,c.statusblok,a.hasilkerja
		from ".$dbname.".kebun_prestasi a 
		left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
		left join ".$dbname.".setup_blok c on a.kodeorg=c.kodeorg 
		left join ".$dbname.".setup_kegiatan e on e.kodekegiatan=a.kodekegiatan 
		where b.tipetransaksi in ('PNN','TM','TBM','BBT','TB') ".$where."";
		
		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		//$data = array();
		$listnotransaksi = array();
		
		while ($bar=$res->fetch())
		{	
			
			if($bar['tipetransaksi'] == "PNN"){
				$bar['kodekegiatan'] = 'PNN';
				$bar['namakegiatan'] = 'Harvesting';
				$bar['satuan'] = 'Bunch';
			}else{
				if ($bar['kodekegiatan'] =='' or $bar['kodekegiatan'] =='0'){
					$bar['kodekegiatan'] = '0';
					$bar['namakegiatan'] = '';
					$bar['satuan'] = '';
				}
			}
		/*
			$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
			$statusblok[$bar['statusblok']]=$bar['statusblok'];
			$kodeorg[$bar['kodeorg']]=$bar['kodeorg'];
			$listkeg[$bar['statusblok']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
			$liststblok[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]=$bar['kodeorg'];
			$prestasi[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['hasilkerja'];
			$jumlahpekerja[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['jumlahpekerja'];
			$namabahan[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['kodebarang'];
			$kwantitas[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['kwantitas'];
		*/
		//print_r($listnotransaksi);
		//exit("ERROR".$bar['hasilkerja']);
			$listnotransaksi[]																	= "'".$bar['notransaksi']."'";
			if($bar['jurnal'] == '0' or $bar['jurnal'] == ''){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['jurnal']						= 'false';
			}
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodekegiatan']						=$bar['kodekegiatan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['namakegiatan']						=$bar['namakegiatan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['tipetransaksi']					=$bar['tipetransaksi'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['satuan']							=$bar['satuan'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['statusblok']						=$bar['statusblok'];
			//hitung
			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodeorg'][$bar['kodeorg']]			=$bar['kodeorg'];
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeorg']]['mesin']	="";
			$data[$bar['statusblok']][$bar['kodekegiatan']]['mesin'][$bar['kodeorg']]['unit']	=0;
			$data[$bar['statusblok']][$bar['kodekegiatan']]['blok'][$bar['kodeorg']]			=substr($bar['kodeorg'],6,10);
			if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']])){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']]	=$bar['hasilkerja'];
			}else{
				$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']]	+= $bar['hasilkerja'];
			}
			if($bar['tipetransaksi'] == "PNN"){
				if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']])){
					$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	= 1;
				}else{
					$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	+=1;
				}
			}else{
				if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']])){
					$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	=$bar['jumlahpekerja'];
				}else{
					$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	+=$bar['jumlahpekerja'];
				}
			}
		}
		
		if(count($listnotransaksi)>0){
			$notrans = implode(",",array_unique($listnotransaksi));
			$str ="select a.kodekegiatan,a.kodebarang,a.kwantitas,a.kwantitasha,c.statusblok,b.namabarang,b.satuan,c.kodeorg from ".$dbname.".kebun_pakai_material_vw a
			left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
			left join ".$dbname.".setup_blok c on a.kodeorg=c.kodeorg 
			where notransaksi in (".$notrans.")";
			$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);

			while ($bar=$res->fetch()){		
				$data[$bar['statusblok']][$bar['kodekegiatan']]['namabarang'][$bar['kodeorg']][$bar['kodebarang']]	= $bar['kodebarang'];
				$data[$bar['statusblok']][$bar['kodekegiatan']]['namabarang'][$bar['kodeorg']][$bar['kodebarang']]	= $bar['namabarang'];
				if(empty($data[$bar['statusblok']][$bar['kodekegiatan']]['kwantitas'][$bar['kodeorg']][$bar['kodebarang']])){
				$data[$bar['statusblok']][$bar['kodekegiatan']]['kwantitas'][$bar['kodeorg']][$bar['kodebarang']]	= $bar['kwantitas'];
				}else{
				$data[$bar['statusblok']][$bar['kodekegiatan']]['kwantitas'][$bar['kodeorg']][$bar['kodebarang']]	+= $bar['kwantitas'];
				}
				$data[$bar['statusblok']][$bar['kodekegiatan']]['satuanbarang'][$bar['kodeorg']][$bar['kodebarang']]= $bar['satuan'];
			}
		}
		
		//query untuk cost per unit
		/*
		$str ="select * from ".$dbname.".keu_jurnaldt_vw where substr(noakun,1,3) in ('126','621','611') and kodeorg in ('ETAE','ETBE','ETCE','ETDE')";
		
		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){		
			if(isset($rupiah[$bar['kodekegiatan']][$bar['kodeblok']])){
				$rupiah[$bar['kodekegiatan']][$bar['kodeblok']]+=$bar['jumlah'];
			}else{
				$rupiah[$bar['kodekegiatan']][$bar['kodeblok']]=$bar['jumlah'];
			}
		}
		*/
		
		$wherekehadiran='';
		if ($kebun !=''){
			$wherekehadiran.=" and b.kodeorg = '".$kebun."'";
		}
		if ($divisi !=''){
			$wherekehadiran.=" and c.kodeorg like '".$divisi."%'";
		}
		if ($tanggal !=''){
			$wherekehadiran.=" and b.tanggal = '".$tanggal."'";
		}
		if ($konduktor !=''){
			//$wherekehadiran.=" and c.nikasisten = '".$konduktor."'";
		}
		$str ="select a.umr,c.kodeorg,c.kodekegiatan  from ".$dbname.".kebun_kehadiran a 
		left join kebun_aktifitas b on a.notransaksi = b.notransaksi
		left join kebun_prestasi c on a.notransaksi = c.notransaksi
		where 1=1 ".$wherekehadiran;
		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){		
			if(isset($rupiah[$bar['kodekegiatan']][$bar['kodeorg']])){
				$rupiah[$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['umr'];
			}else{
				$rupiah[$bar['kodekegiatan']][$bar['kodeorg']]=$bar['umr'];
			}
		}
		

//Khusus TM,TBM
$caseview = $typereport;
$statusBlok = array('TM'=>'Mature','TBM'=>'Immature','BBT'=>'Nursery','TB'=>'Land Clearing','ABSEN'=>'Absence','KONTRAK'=>'Contract');
$wAction = array();
if ($kebun !=''){
	$wAction[] = "kebun=".$kebun."";
}
if ($divisi !=''){
	$wAction[] = "divisi=".$divisi."";
}
if ($tanggal !=''){
	$wAction[] = "tanggal=".$tanggal."";
}
if ($konduktor !=''){
	$wAction[] = "konduktor=".$konduktor."";
}
$whereOnclick = "'".implode("&",$wAction)."'";
$tab.="<tbody>";

if($caseview == 1){
	foreach ($statusBlok as $s => $sv){
		$numb = 0;
		if(isset($data[$s])){
			foreach ($data[$s] as $k =>$v)
			{
				$hasilkerja = 0;
				if(!empty($v['hasilkerja'])){
					$hasilkerja = array_sum($v['hasilkerja']);
				}
				
				$jumlahpekerja = "";
				if(!empty($v['jumlahpekerja'])){
					$jumlahpekerja = number_format(array_sum($v['jumlahpekerja']));
				}
				$kodeorg = array();
				$blok = array();
				$mesin = array();
				$unit = array();
				if(count($v['kodeorg'])>0){
					$kodeorg =array_unique($v['kodeorg']);
					$blok =array_unique($v['blok']);
					foreach($v['kodeorg'] as $valkodeOrg){
						if(isset($v['mesin'][$valkodeOrg]) and count($v['mesin'][$valkodeOrg])>0){
							if($v['mesin'][$valkodeOrg]['mesin'] != ""){
								$mesin[] = $v['mesin'][$valkodeOrg]['mesin'];
							}
							if($v['mesin'][$valkodeOrg]['unit'] != ""){
							$unit[] =	$v['mesin'][$valkodeOrg]['unit'];
							}
						}
					}
				}
				
				$namabarang = "";
				if(isset($v['namabarang'])){
					if(count($v['namabarang'])>0){
						if($v['tipetransaksi'] == "PNN"){
							$namabarang = "Bunch";
						}else{
							//print_r($v['namabarang']);
							$barang = getArrayByArray($v['namabarang'],$kodeorg);
							$barang = array_unique($barang);
							$namabarang = implode(",",$barang);
						}
					}
				}
				$cost = 0;
				if(!empty($rupiah[$v['kodekegiatan']])){
					$cost = array_sum($rupiah[$v['kodekegiatan']]);
					if($hasilkerja == 0){
						$pembagi = 1;
					}else{
						$pembagi = $hasilkerja;
					}
					$costlast = ($cost/$pembagi);
					$cost = number_format($costlast,2);
				}
				$kwantitas = "";
				if(isset($v['kwantitas'])){
					if(count($v['kwantitas'])>0){
						$kwa = getArrayByArray($v['kwantitas'],$kodeorg);
						$kwantitas = number_format(array_sum($kwa));
					}
				}
				$remark = "";
				if(isset($v['satuanbarang']) and count($v['satuanbarang']) > 0){
					$sat = getArrayByArray($v['satuanbarang'],$kodeorg);
					$remark = array_sum($sat);
				}
				$tab.="<tr class=rowcontent>";
				
				if($numb == 0){
					$tab.="<td align=center rowspan='".count($data[$s])."'>".$sv."</td>";
					$numb = 1;
				}
				
				if($v['kodekegiatan'] != "SPB"){
					if($s != "KONTRAK"){
						$fungsiDetail = "viewDataDetail";
					}else{
						$fungsiDetail = "viewDataDetailKontrak";
					}
					if(isset($v['jurnal']) and $v['jurnal'] == 'false'){
						$tab.="<td align=center ><img src='images/skyblue/posting.png' class='resicon' height='30' title='Posting' onclick=\"".$fungsiDetail."('".$v['kodekegiatan']."',".$whereOnclick.");\"></td>";
					}else{
						$tab.="<td align=center ><img src='images/skyblue/posted.png' class='resicon' height='30' title='Posted'></td>";
					}
					$tab.="<td align=left><a href=\"#\" onclick=\"".$fungsiDetail."('".$v['kodekegiatan']."',".$whereOnclick.");\">".$v['namakegiatan']."</a></td>";
				}else{
					$tab.="<td align=center ></td>";
					$tab.="<td align=left>".$v['namakegiatan']."</td>";
					
				}
				$tab.="<td align=center>".$v['satuan']."</td>";
				$tab.="<td align=left>".implode(",",$blok)."</td>";
				$tab.="<td align=center>".number_format($hasilkerja,2)."</td>";
				$tab.="<td align=center>".$cost."</td>";
				$tab.="<td align=center>".$jumlahpekerja."</td>";
				$tab.="<td align=left>".implode(",",$mesin)."</td>";
				$tab.="<td align=left>".number_format(array_sum($unit))."</td>";
				$tab.="<td align=left>".$namabarang."</td>";
				$tab.="<td align=center>".$kwantitas."</td>";
				$tab.="<td align=left>".$remark."</td>";
				$tab.="</tr>";
			}
		}
	}
}else if($caseview == 2){
	foreach ($statusBlok as $s => $sv){
		$numb = 0;
		if(isset($data[$s])){
			$jml[$s] = 0;
			foreach ($data[$s] as $k =>$v)
			{
				$jml[$s] += count($v['kodeorg']);
			}
			foreach ($data[$s] as $k =>$v)
			{
				
				$tab.="<tr class=\"rowcontent\">";
				if($numb == 0){
					$tab.="<td align=\"center\" rowspan=\"".$jml[$s]."\">".$sv."</td>";
					$numb++;
				}
				if($v['kodekegiatan'] != "SPB"){
					if($s != "KONTRAK"){
						$fungsiDetail = "viewDataDetail";
					}else{
						$fungsiDetail = "viewDataDetailKontrak";
					}
					$tab.="<td align=\"left\" rowspan=\"".count($v['kodeorg'])."\" colspan=\"2\"><a href=\"#\" onclick=\"".$fungsiDetail."('".$v['kodekegiatan']."',".$whereOnclick.");\">".$v['namakegiatan']."</a></td>";
				}else{
					$tab.="<td align=left rowspan=\"".count($v['kodeorg'])."\" colspan=\"2\">".$v['namakegiatan']."</td>";
				}
				
				$tab.="<td align=center rowspan=".count($v['kodeorg']).">".$v['satuan']."</td>";
				$no=0;
				foreach($v['kodeorg'] as $blok){
					if($no != 0){
						$tab.="<tr class=rowcontent>";
					}
					$cost = 0;
					if(!empty($rupiah[$v['kodekegiatan']][$blok]) and !empty($v['hasilkerja'][$blok])){
						$cost = $rupiah[$v['kodekegiatan']][$blok];
						$cost = number_format($cost/$v['hasilkerja'][$blok],2);
					}
					$tab.="<td align=\"left\">".$v['blok'][$blok]."</td>";
					$tab.="<td align=\"center\">".number_format($v['hasilkerja'][$blok],2)."</td>";
					$tab.="<td align=\"center\">".$cost."</td>";
					$tab.="<td align=\"center\">".$v['jumlahpekerja'][$blok]."</td>";
					$tab.="<td align=\"center\">".$v['mesin'][$blok]['mesin']."</td>";
					$tab.="<td align=\"center\">".$v['mesin'][$blok]['unit']."</td>";
					$barang = array();
					if(isset($v['namabarang'][$blok])){
						$barang = $v['namabarang'][$blok];
					}
					$kwantitas = array();
					if(isset($v['kwantitas'][$blok])){
						$kwantitas = $v['kwantitas'][$blok];
					}
					$satuanbarang = array();
					if(isset($v['satuanbarang'][$blok])){
						$satuanbarang = $v['satuanbarang'][$blok];
					}
					$tab.="<td align=\"center\">".implode(",",$barang)."</td>";
					$tab.="<td align=\"center\">".implode(",",$kwantitas)."</td>";
					$tab.="<td align=\"center\">".implode(",",$satuanbarang)."</td>";
					$tab.="</tr>";
					$no++;
				}
			}
		}
	}

}
$tab.="</tbody>";
	
	if($ttd == "true"){
		$str ="select namaorganisasi as name from ".$dbname.".organisasi where kodeorganisasi = '".$kebun."'";  
		if($divisi != ""){
			$str .= "union 
			select namaorganisasi  as name from ".$dbname.".organisasi where kodeorganisasi = '".$divisi."' ";
		}else{
			$str .= "union select '' as name ";
		}	
		$str .= "union 
		select namakaryawan  as name from ".$dbname.".datakaryawan where lokasitugas = '".$kebun."' and kodejabatan = '70' ";
		if($divisi != ""){
			$str .= "union select namakaryawan  as name from ".$dbname.".datakaryawan where lokasitugas = '".$kebun."' and subbagian = '".$divisi."' and kodejabatan = '74' ";
		}else{
			$str .= "union select '' as name ";
		}
		if($konduktor != ""){
			$str .= "union
		select namakaryawan  as name from ".$dbname.".datakaryawan where lokasitugas = '".$kebun."' and karyawanid = '".$konduktor."'";
		}else{
			$str .= "union select '' as name";
		}
		$data = fetchData($str);
	$tab.="</table></div>";
	$tab.="<br>
		<div style=\"page-break-inside:always;\">
			<table class=sortable cellspacing=0 cellpadding=7 border=0 style='width:100%;'>
				<tr class=rowcontent >";
			if($konduktor != ""){	
				$tab.="	<td align=left colspan=5>".$_SESSION['lang']['konduktor']." </td>";
			}
			$headorg = "";
			if(isset($data[1]['name'])){
				$headorg = $data[1]['name'];
			}
			if($divisi != ""){
				$tab.="	<td align=left colspan=5>Reported By: Head of ".$headorg."  </td>";
			}
			$managerorg = "";
			if(isset($data[0]['name'])){
				$managerorg = $data[0]['name'];
			}
			$tab.="	<td align=left colspan=5>Checked By: ".$managerorg." Manager</td>
				</tr>
				<tr class=rowcontent>";
			if($konduktor != ""){	
				$tab.="	<td align=left colspan=5><br/><br/><br/></td>";
			}
			if($divisi != ""){
				$tab.="	<td align=left colspan=5><br/><br/><br/></td>";
			}
				$tab.="	<td align=left colspan=5><br/><br/><br/></td>
				</tr>
				<tr class=rowcontent>";
			$konduktorname = "";
			if(isset($data[4]['name'])){
				$konduktorname = $data[4]['name'];
			}
			if($konduktor != ""){	
				$tab.="	<td align=left colspan=5 style='border-top:1px;'>".$konduktorname."</th>";
			}
			$headdivisionname = "";
			if(isset($data[3]['name'])){
				$headdivisionname = $data[3]['name'];
			}
			if($divisi != ""){
				$tab.="	<td align=left colspan=5 style='border-top:1px;'>".$headdivisionname."</th>";
			}
			$manager = "";
			if(isset($data[2]['name'])){
				$manager = $data[2]['name'];
			}
			$tab.="	<td align=left colspan=5 style='border-top:1px;'>".$manager."</th>
				</tr>
			</table>
		</div>";
	}else{		
		$tab.="</table>";
	}	
	return $tab;
}
?>