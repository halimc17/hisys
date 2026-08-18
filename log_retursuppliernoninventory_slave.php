<?php
//Umar
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/cekakun.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/terbilang.php');
use Dompdf\Dompdf;
error_reporting(0);


$urlefil=checkPostGet('urlefil','0');
$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

$stylehidden = "style='display:none'";
$path    	 = "fileupload/keu_kasbankx/";

$str = "select * from ".$dbname.".setup_filesize where transaksi='keu_kasbank'";
$res = fetchdata($str);
foreach($res as $bar){
	$filesize = $bar['filesize'];
}


$table 		 	= 'log_retursuppliernoninventory';
$tabledt 	 	= 'log_retursuppliernoninventorydt';
$arrhutangunit  = array("0"=>"Tidak","1"=>"Ya"); 

$tab = "";

$optunit = $optsupplier=$optsumberlain=$optunitap=$optcustomer="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str  	 = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res 	 = fetchdata($str);
foreach($res as $bar){
	$nmorganisasi[$bar['kodeorganisasi']]  	= $bar['namaorganisasi'];	
	$tipeorganisasi[$bar['kodeorganisasi']] = $bar['tipe'];	
	$kodept[$bar['kodeorganisasi']] 		= $bar['induk'];	
	$optunit .= "<option value='".$bar['kodeorganisasi']."'>[".$bar['kodeorganisasi']."] ".$bar['namaorganisasi']."</option>";
}


$str = "select * from ".$dbname.".log_5supplier order by namasupplier asc";
$res = fetchdata($str);
foreach($res as $bar){
	$optsupplier .= "<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
	$nmsupplier[$bar['supplierid']] = $bar['namasupplier'];	
}

$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res = fetchdata($str);
foreach($res as $bar){
	$nmbarangpabrik[$bar['kodebarang']] = $bar['namabarang'];	
}

$arrtipeinvoice = makeOption($dbname,'keu_5jenistagihan','kode,namajenis');

// $arrtransaksi=array("PJDUM"=>"Uang Muka Perjalanan Dinas",
					// "PJD"=>"Perjalanan Dinas",
					// "OBAT"=>"Pengobatan",
					// "KONTAN"=>"Kontanan",
					// "VATINOUT"=>"Tax PPn"); 
// foreach($arrtransaksi as $val=>$nama){
    // $optsumberlain.="<option value='".$val."'>".$nama."</option>";
// }  

$optsumberlain .= "<option value='feepanen'>Fee Panen</option>";
$optsumberlain .= "<option value='umpjdinas'>Pemby Uang Muka Pj. Dinas</option>";
// $optsumberlain.="<option value='realpjdinas'>Pemby Pj. Dinas (tiket pesawat dll)</option>";
$optsumberlain .= "<option value='claimpjdinas'>Pemby Klaim Pj. Dinas</option>";
$optsumberlain .= "<option value='batalpjd'>Pengembalian UM Pj. Dinas (BATAL DINAS)</option>";



$str="select * from ".$dbname.".keu_5akun";
$res=fetchdata($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=$bar['namaakun'];
}

$str="select * from ".$dbname.".keu_5akunbank_vw";
$res=fetchdata($str);
foreach($res as $bar){
	$dtnamabank[$bar['noakun']]=$bar['namabank'];
}

$str="select * from ".$dbname.".keu_5aruskas where level=3";
$res=fetchdata($str);
foreach($res as $bar){
	$nmaruskas[$bar['noaruskas']]=$bar['nama_aruskas'];
}

$str="select * from ".$dbname.".pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$optcustomer.="<option value='".$bar['kodecustomer']."'>".$bar['kodecustomer']." - ".$bar['namacustomer']."</option>"; 
	$nmcustomer[$bar['kodecustomer']]=$bar['namacustomer'];
}

// exit("Error:$method");
switch ($method) {
	
	;
	
	case'posting':
	
		#= penambahan setup posting
			// exit("Error:A");
		// $jab = getPostingJabatan('gudangnoninventory');	
		// if(!in_array($_SESSION['empl']['jabatan'],$jab)){
			// exit("warningsistem:Jabatan anda tidak diperbolehkan untuk posting  noninventory, hubungi IT untuk mendaftarkan disetup posting");
		// }

		$str="select * from ".$dbname.".log_retursuppliernoninventory where notransaksi='".$param['notransaksi']."'";
		// exit("warning:".$str);
		$res=fetchdata($str);
		$unit=$res[0]['unit'];
		$pt=$res[0]['pt'];
		$tanggal=$res[0]['tanggal'];
		$tanggal=$res[0]['tanggal'];
		$tipe=$res[0]['tipe'];
		$supplierid=$res[0]['supplierid'];
		$kodejurnal="RETNI";
		
		$jurnalfound='';
		// cek apakah sudah ada jurnal?
		$str = "select nojurnal from ".$dbname.".keu_jurnalht where noreferensi = '".$param['notransaksi']."' ";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$jurnalfound.=$val['nojurnal'].',';
		}
		if($jurnalfound!=''){
			exit ("error : Sudah ada jurnal: ".$jurnalfound." silakan refresh.");			
		}
		
		## Prepare jurnal
		## Ambil noakun supplier
		
		$kodekl = "SUPPLIER";
		$noakunkr = "2110101";
		if($tipe=='SO'){
			$kodekl = "JASA";
			$noakunkr = "2110301";
		}

		// GRIR 2021
		$noakungrir='2110501';
		$noakunkr=$noakungrir;
		$str = "select kodeorganisasi from ".$dbname.".organisasi where induk in (select induk from ".$dbname.".organisasi where kodeorganisasi = '".$unit."' ) and tipe = 'KANWIL'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$ronya = $val['kodeorganisasi'];
		}

		$str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$ronya."' and jenis = 'intra'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$akuncacoro=$val['akunpiutang'];
		}
		$str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$unit."' and jenis = 'intra'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$akuncacoes=$val['akunhutang'];
		}

		// cek apakah RO sudah closing
        $str = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".$ronya."'";
		$res=fetchdata($str);
        $close = $res[0]['tutupbuku'];
        if ($close == '1'){
			exit ("error : ".$ronya." sudah tutup buku");
        }

		
		if($noakunkr==''){
			exit("Warning:No. Akun masih kosong kredit masih kosong, silahkan cek di setup kelompok supplier, jika memakai konsep GR/IR cek juga akun GR/IR disetup tersebut");
		}

		
		// $str = "select noakun from ".$dbname.".log_5klsupplier where tipe like '".$kodekl."%' and noakun!='' limit 1";
		// $res=fetchdata($str);
		// $noakunkr = $res[0]['noakun'];
		
		
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$pt."' and kodekelompok='".$kodejurnal."' 
			and kodeunit='".$unit."' and periode='".substr($tanggal,0,7)."'");
		$tmpKonter = fetchData($queryJ);
		$konter = $tmpKonter[0]['nokounter'];
		// $konter = addZero($tmpKonter[0]['nokounter']+1,3);
		// GRIR 2021
		$queryJro = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$pt."' and kodekelompok='".$kodejurnal."' 
			and kodeunit='".$ronya."' and periode='".substr($tanggal,0,7)."'");
		$tmpKonterro = fetchData($queryJro);
		$konterro = $tmpKonterro[0]['nokounter'];

		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
	
		try {
			$owlPDO->beginTransaction();
			
			$tglskrg=date("Y-m-d H:i:s");
			
			##MAINKAN JURNAL NYA
			#= oke boi
			// Default Segment
			$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
			$notemp=0;
			$notempro=0;
			$str="select * from ".$dbname.".log_retursuppliernoninventorydt_vw where notransaksi='".$param['notransaksi']."'";
			// exit("warning:".$str);
			$res=fetchdata($str);
			foreach($res as $bar){
				

				$kodeblok='';
				$kodevhc='';
				$kodeasset='';

				#= cek data subunit
				if($klkegiatan[$bar['kodekegiatan']]=='TM' || $klkegiatan[$bar['kodekegiatan']]=='TBM' || 
					$klkegiatan[$bar['kodekegiatan']]=='PNN' || $klkegiatan[$bar['kodekegiatan']]=='BBT' ||
					$klkegiatan[$bar['kodekegiatan']]=='TB' || $klkegiatan[$bar['kodekegiatan']]=='LC'){
					$kodeblok=$bar['subunitdt'];
				}

				if($klkegiatan[$bar['kodekegiatan']]=='TRK'){
					$kodevhc=$bar['subunitdt'];
				}

				if($klkegiatan[$bar['kodekegiatan']]=='KNT' and substr($bar['subunitdt'],0,3)=='AK-'){
				// if(substr($bar['subunitdt'],0,3)=='AK-'){
					$kodeasset=$bar['subunitdt'];
				}

				$data=array();
				$dataro=array();
				$noUrut=1;
				$notemp++;
				$notempro++;
				// @$no+=1;
				// $konter = addZero($no,3);
				
				# Prep No Jurnal
				$nojurnal = str_replace('-','',$tanggal)."/".$unit."/".$kodejurnal."/".addZero($konter+$notemp,3);
				// GRIR 2021
				$nojurnalro = str_replace('-','',$tanggal)."/".$ronya."/".$kodejurnal."/".addZero($konterro+$notempro,3);

				#== header
				#= jurnal ht
				$data['header'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodejurnal,
					'tanggal'=>$bar['tanggal'],
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>'0',
					'totalkredit'=>'0',
					'amountkoreksi'=>'0',
					'noreferensi'=>$bar['notransaksi'],
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
				// GRIR 2021
				$dataro['header'] = array(
					'nojurnal'=>$nojurnalro,
					'kodejurnal'=>$kodejurnal,
					'tanggal'=>$bar['tanggal'],
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>'0',
					'totalkredit'=>'0',
					'amountkoreksi'=>'0',
					'noreferensi'=>$bar['notransaksi'],
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
				
				#== detail
				#= kredit
				$data['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>substr($bar['kodekegiatan'],0,7),
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot']*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$bar['unit'],
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				// GRIR 2021
				$dataro['detail'][] = array(
					'nojurnal'=>$nojurnalro,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$akuncacoes,
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot']*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$ronya,
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				$noUrut++;
				
				#= debet
				// kalo ini RO, langsung ke GRIR 2021
				if($unit==$ronya){
					$akuncacoro=$noakunkr;
				}
				$data['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$akuncacoro,
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot'],
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$bar['unit'],
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				// GRIR 2021
				$dataro['detail'][] = array(
					'nojurnal'=>$nojurnalro,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$noakunkr,
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot'],
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$ronya,
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				// echo "<pre>";
				// print_r($data);
				// print_r($dataro);
				// echo "</pre>";
				// exit("error!!!");
				
				$queryH = insertQuery($dbname,'keu_jurnalht',$data['header']);
				$owlPDO->exec($queryH);
				
				foreach($data['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					$owlPDO->exec($queryD);
				}
				// GRIR 2021
				if($unit!=$ronya){
				$queryHro = insertQuery($dbname,'keu_jurnalht',$dataro['header']);
				$owlPDO->exec($queryHro);
				
				foreach($dataro['detail'] as $key=>$dataDetro) {
					$queryDro = insertQuery($dbname,'keu_jurnaldt',$dataDetro);
					$owlPDO->exec($queryDro);
				}
				}
					
			}
			
			
			
			
			# Get Journal Counter
			$queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>($konter+$notemp)),
							"kodeorg='".$pt."' and kodeunit='".$unit."' and  
							periode='".substr($tanggal,0,7)."' and kodekelompok='".$kodejurnal."'");	
							// exit("Error:".$queryJRB);
			$owlPDO->exec($queryJRB);						
			if($unit!=$ronya){
			$queryJRBro = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>($konterro+$notempro)),
							"kodeorg='".$pt."' and kodeunit='".$ronya."' and  
							periode='".substr($tanggal,0,7)."' and kodekelompok='".$kodejurnal."'");	
							// exit("Error:".$queryJRB);
			$owlPDO->exec($queryJRBro);
			}
			if($tanggalselesai){
				$tglselesai = tanggalsystemn($tanggalselesai);
			}else{
				$tglselesai = '0000-00-00';
			}
			##UBAH FLAG Posting
			$str="update ".$dbname.".log_retursuppliernoninventory set posting='1', postedby='".$_SESSION['standard']['userid']."', postedtime='".$tglskrg."', tanggalselesai='".$tanggal."' where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	
	
	
	case'loaddata':
		#= untuk unit ht
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
		$where="1=1 and  unit in ('".implode("','",$dtunit)."') ";
		
		// if($param['tanggalmulai']!='' and $param['tanggalselesai']!=''){
			// $where.=" and tanggal between '".tanggalsystemn($param['tanggalmulai'])."' and '".tanggalsystemn($param['tanggalselesai'])."'";
		// }
		
		if($param['notransaksi']!=''){
			$where.=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		
		if($param['unit']!=''){
			$where.=" and unit = '".$param['unit']."'";
		}
		
		
		
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		$colspan=30;
	
		$offset = $page * $limit;
		
		
		// echo $limit._.$page._.$maxdisplay._.$offset;
		// $str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."  group by notransaksi  ";
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jumrow = $bar['jumrow'];
		}
		

		$no = 0;
		$no=$maxdisplay;
		$statusapp = '';
		$str = "select * from ".$dbname.".".$table." where ".$where." order by tanggal desc,notransaksi desc limit " . $offset . "," . $limit . " ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$nmkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', 'karyawanid="'.$bar['createdby'].'"');
			# Status Approval
			
			#=datakaryawan
			$strdt="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid in ('".$bar['createby']."','".$bar['updateby']."','".$createbydt."','".$updatebydt."','".$createdbyfileupload."') ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namakaryawan[$bardt['karyawanid']]=$bardt['namakaryawan'];
			}
			
			if($fileupload>0){
				$fileupload="<img src=images/done.png class=resicon title='True'>";
			}else{
				$fileupload="";
			}
			
			// $jumrow++;
			$bgcolor='class=rowcontent';
			if($bar['posting'] == 3){
				$bgcolor="bgcolor='orange'  title='Koreksi'";
			}
			if($bar['posting'] == 2){
				$bgcolor="bgcolor='red'  title='Ditolak'";
			}
			$no++;
			$tab .= "<tr ".$bgcolor." ".$style.">";
				$tab .= "<td align='center' valign='top'>".$no."</td>";
				$tab .= "<td valign='top'>".$bar['notransaksi']."</td>";
				$tab .= "<td valign='top'>".$bar['tipe']."</td>";
				$tab .= "<td valign='top'>".$bar['pt']."</td>";
				$tab .= "<td valign='top'>".$bar['unit']."</td>";
				$tab .= "<td valign='top'>".tanggalnormal($bar['tanggal'])."</td>";
				$tab .= "<td valign='top'>".$bar['nopo']."</td>";
				$tab .= "<td valign='top'>".$nmsupplier[$bar['supplierid']]."</td>";
				$tab .= "<td valign='top'>".$bar['termin']."</td>";
				$tab .= "<td valign='top'>".$nmkaryawan[$bar['createdby']]."</td>";
				if($bar['posting'] == 0){
					$tab .= "<td align=center valign=top  style=\"width:20px;\">";
						$tab .= "<img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('".$bar['notransaksi']."','".$bar['unit']."','".$bar['notransaksireferensi']."','".tanggalnormal($bar['tanggal'])."','".$bar['keterangan']."','".$bar['tipe']."','".$bar['pt']."','".$bar['nopo']."','".$bar['nosj']."','".$bar['penerima']."','".$bar['supplierid']."','".$bar['termin']."','".$nmsupplier[$bar['supplierid']]."');\">";
					$tab .= "</td>";
					$tab .= "<td align=center valign=top  style=\"width:20px;\">";
						$tab .= "<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('".$bar['notransaksi']."');\">";		
					$tab .= "</td>";
					$tab .= "<td align=center valign=top  style=\"width:20px;\">";
						$tab.="<img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"posting('".$bar['notransaksi']."');\">";
					$tab .= "</td>";
				}  else {
					$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
					$tab .= "<td align=center valign=top  style=\"width:20px;\"></td>";
					$tab .= "<td align=center valign=top  style=\"width:20px;\">";
						$tab .= "<img src='images/skyblue/posted.png' class='zImgOffBtn' title='Posted'>";
					$tab .= "</td>";
					
				}
				
				
				if($bar['tipe'] == 'SO'){
					$tab.="<td align=center width=25px valign=top><img src='images/skyblue/pdf.jpg' class='resicon' title='Print PDF BAPP'' onclick=\"previewpdfbars(event,'".$bar['notransaksi']."');\"></td>";
				}else{
					$tab .= "<td align=center valign=top  style=\"width:20px;\">";
					$tab.="<img src='images/skyblue/pdf.jpg' class='resicon' title='Print PDF Retur Supplier'' onclick=\"previewpdfrs(event,'".$bar['notransaksi']."');\"></td>";
				}
				
				
				
			$tab .= "</tr>";
        }
		$tab2 = createpaging($jumrow,$limit,$page,$colspan,'loaddata','getpage');
		//$tab.="</table>";
        echo $tab."####".$tab2;
	break;
	
	case'deleteht':
		try {
			$owlPDO->beginTransaction();
			
			// $str 	= "select nopo, termin from ".$dbname.".log_noninventory where notransaksi='".$param['notransaksi']."'";
			// $res 	= fetchdata($str);
			// $nopo 	= $res[0]['nopo'];
			// $termin = $res[0]['termin'];
			
			$str = "delete from ".$dbname.".log_retursuppliernoninventory where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			// $str="update ".$dbname.".log_potermin set ba='0' where nopo='".$nopo."' and termin='".$termin."'";
			// $owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'formdata':
		$tab.="<fieldset><legend>".$_SESSION['lang']['form']."</legend>";
			$tab.="<table>";
			$tab.="<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input type=text id=notransaksidata  size=50 class=myinputtext style=\"width:150px;\"></td>
					
					<td>".$_SESSION['lang']['nopo']."</td>
					<td>:</td>
					<td><input type=text id=nopodata  size=50 class=myinputtext style=\"width:150px;\"></td>
				</tr>";
			
			$tab.="<tr>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=finddata()>".$_SESSION['lang']['find']."</button></td>";
			$tab.="</tr>";
			$tab.="</table>";
		$tab.="</fieldset>";
		$tab.="<br>";
		
			// $tab.=" <div class=table-scroll>";
			$tab.="<table cellpadding=5 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
			$tab.="<thead><tr class=rowheader>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center>".$_SESSION['lang']['notransaksi']."</th>
					<th align=center>".$_SESSION['lang']['tanggal']."</th>
					<th align=center>".$_SESSION['lang']['tipe']."</th>
					<th align=center>".$_SESSION['lang']['nopo']."</th>
					<th align=center>".$_SESSION['lang']['supplier']."</th>";
			$tab.="</tr>";
			$tab.="</thead>";
			$tab.="<tbody id=formdatadetail></tbody>";
			$tab.="</table>";
			$tab.="</div>";
		echo $tab;
	break;
	
	case'finddata':
		
		if($param['notransaksi']!=''){
			$where.="  and notransaksi like '%".$param['notransaksi']."%' ";
		}
		if($param['nopo']!=''){
			$where.="  and nopo like '%".$param['nopo']."%' ";
		}
		$str="select * from ".$dbname.".log_noninventory where 1=1 and unit='".$param['unit']."' and posting=1 ".$where."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$data="style='cursor:pointer' title='lihat detail data' onclick=\"movedata('".$bar['notransaksi']."','".$bar['nopo']."','".$bar['tipe']."','".$bar['supplierid']."','".$nmsupplier[$bar['supplierid']]."','".$bar['termin']."');\" ";
			$tab.="<tr class=rowcontent ".$data.">";
					$tab.="<td align=left>".$no."</td>";
					$tab.="<td>".$bar['notransaksi']."</td>";
					$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
					$tab.="<td>".$bar['tipe']."</td>";
					$tab.="<td>".$bar['nopo']."</td>";
					$tab.="<td>".$nmsupplier[$bar['supplierid']]."</td>";
			$tab.="</tr>";		
			
		}
		echo $tab;
	break;
	
	case'saveht':
	
		if($param['notransaksireferensi']==''){
			exit("Warning:Nomor Transaksi penerimaan belum terisi");
		}
		if($param['tanggal']==''){
			exit("Warning:Tanggal masih belum terisi");
		}
		if($param['keterangan']==''){
			exit("Warning:Keterangan Harus Terisi");
		}
		
		##CEK PERIODE AKUTANSI
		$periode=substr($param['tanggal'],6,4)."-".substr($param['tanggal'],3,2);
		$str="select count(periode) as periode from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".$param['unit']."' and tutupbuku='1'";
		$res=fetchdata($str);
		$countperiode=$res[0]['periode'];
		if($countperiode > 0){
			throw new PDOException("Periode ".$periode." untuk unit ".$param['unit']." sudah tutup buku, silahkan ganti tanggal penerimaan");
		}
		
		if($param['notransaksi']==''){
			##NO TRANSAKSI
			$str="select notransaksi from ".$dbname.".".$table." where unit='".$param['unit']."' and tanggal like '".$periode."%' order by notransaksi desc limit 1";
			$res=fetchdata($str);
			$tempnotransaksi=$res[0]['notransaksi'];
			if($tempnotransaksi==''){
				$param['notransaksi']=substr($param['tanggal'],6,4)."".substr($param['tanggal'],3,2)."00001-RETGRNI-".$param['unit'];
			}else{
				$param['notransaksi']=substr($param['tanggal'],6,4)."".substr($param['tanggal'],3,2)."".addZero((substr($tempnotransaksi,6,7)+1),5)."-RETGRNI-".$param['unit'];
			}
			$str = "insert into ".$dbname.".".$table." (`notransaksi`, `tipe`, `tanggal`, `pt`, `unit`, `nopo`, `nosj`, `keterangan`, `penerima`, `supplierid`, `termin`, `tanggalselesai`, `posting`, `postedby`, `postedtime`, `updateby`, `updatetime`, `createdby`, `createtime`, `disetujui`, `diperiksa`, `persetujuan`, `notransaksireferensi`) values ('".$param['notransaksi']."','".$param['tipe']."','".tanggalsystemn($param['tanggal'])."','".$kodept[$param['unit']]."','".$param['unit']."','".$param['nopo']."','".$param['nosj']."','".$param['keterangan']."','".$param['penerima']."','".$param['supplierid']."','".$param['termin']."','".$param['tanggalselesai']."','0','','','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$param['disetujui']."','".$param['diperiksa']."','".$param['persetujuan']."','".$param['notransaksireferensi']."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "update ".$dbname.".".$table." set 
				tanggal='".tanggalsystemn($param['tanggal'])."',
				keterangan='".$param['keterangan']."',
				updateby='".$_SESSION['standard']['userid']."'			
				where notransaksi = '".$param['notransaksi']."'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}	
		}
		
		echo $param['notransaksi'];
	break;
	
	case'loaddatadt':
	
		$no=0;	
		$str = "select * from ".$dbname.".log_noninventorydt  where notransaksi='".$param['notransaksireferensi']."'";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			
			$datajumlah=0;
			$strdata = "select * from ".$dbname.".log_retursuppliernoninventorydt  where notransaksi='".$param['notransaksi']."'";
			$resdata=fetchdata($strdata);
			foreach($resdata as $bardata){
				$datajumlah=$bardata['jumlah'];
			}
			
			
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=left valign=top id=noppdt".$no.">".$bar['nopp']."</td>";
				$tab.="<td align=left valign=top id=kodebarangdt".$no.">".$bar['kodebarang']."</td>";
				$tab.="<td align=left valign=top id=satuandt".$no.">".$bar['satuan']."</td>";
				$tab.="<td align=right valign=top id=jumlahtransaksidt".$no.">".$bar['jumlah']."</td>";
				$tab.="<td><input class=myinputtextnumber id=jumlahdt".$no." value=".$datajumlah." style=\"width: 150px;\" onkeyup=z.numberFormat('jumlahdt',2); onkeypress='return_tanpa_kutip_dan_sepasi(event)' /></td>";
				$tab.="<td align=left valign=top id=hargasatuandt".$no.">".$bar['hargasatuan']."</td>";
				$tab.="<td align=left valign=top id=subunitdt".$no.">".$bar['subunit']."</td>";
				$tab.="<td align=left valign=top id=subunitdtdt".$no.">".$bar['subunitdt']."</td>";
				$tab.="<td align=left valign=top id=kodekegiatandt".$no.">".$bar['kodekegiatan']."</td>";
				$tab.="<td align=left valign=top id=nopodt".$no.">".$bar['nopo']."</td>";
				$tab.="<td align=left valign=top id=catatandt".$no.">".$bar['catatan']."</td>";
				$tab.="<td align=center  valign=top width=20px><img src=images/save.png class=zImgBtn caption='Save' onclick=\"savedt('".$no."');\">";
            $tab.="</tr>";
        }

		echo $tab;
	break;
	
	case'savedt':

		#= validasi
		if($param['jumlah']>$param['jumlahtransaksidt']){
			exit("Warning:Jumlah melebihi nilai detail transaksi lama");
		}
		
		
	
		#= delete 1st 
		$str=" delete from ".$dbname.".".$tabledt." where notransaksi='".$param['notransaksi']."' and kodebarang='".$param['kodebarang']."' and subunit='".$param['subunit']."' and subunitdt='".$param['subunitdt']."' and nopo='".$param['nopo']."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		
		$str = "insert into ".$dbname.".".$tabledt." (notransaksi,nopp,kodebarang,satuan,jumlah,hargasatuan,subunit,subunitdt,kodekegiatan,nopo,catatan) values  ('".$param['notransaksi']."','".$param['nopp']."','".$param['kodebarang']."','".$param['satuan']."','".$param['jumlah']."','".$param['hargasatuan']."','".$param['subunit']."','".$param['subunitdt']."','".$param['kodekegiatan']."','".$param['nopo']."','".$param['catatan']."')";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	
	case'previewpdfrs':
		$tab="";
	
		$str="select pt,unit,penerima,tanggal,nopo,supplierid,postedby,disetujui,createdby from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."'";
			// exit("error:".$str);
		$res=fetchdata($str);
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$supplier=$res[0]['supplierid'];
		$postedby=$res[0]['postedby'];
		$disetujui=$res[0]['disetujui'];
		$createdby=$res[0]['createdby'];
	
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
		$optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$optpurchaser=makeOption($dbname,'log_poht','nopo,purchaser',"nopo='".$nopo."'");
		
		$pt=$optpt[$pt];
		$unit=$optunit[$unit];
		$supplier=$optsupplier[$supplier];
		$purchaser=$optpurchaser[$nopo];
	
		$tab.="<table cellspacing=0 border=0 width=100% align=center>
			<tr>
				<td align=center style='border-bottom:0.1px solid #000;font-weight:bold'>BUKTI PENERIMAAN BARANG</td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:12px;' width=100%>
			<tr>
				<td width=60% style='font-weight:bold'>".$pt."</td>
				<td width=40% rowspan=2 style='vertical-align:bottom'>
				<table>
					<tr>
						<td>No. Transaksi</td>
						<td>:</td>
						<td>".$param['notransaksi']."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>".$tanggal."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['nopo']."</td>
						<td>:</td>
						<td>".$nopo."</td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td>
				<table>
					<tr>
						<td>Bisnis Unit</td>
						<td>:</td>
						<td>".$unit."</td>
					</tr>
					<tr>
						<td>Diterima Dari</td>
						<td>:</td>
						<td>".$supplier."</td>
					</tr>
				</table>
				</td>
			</tr>
		</table>";
		
		$tab.="<table style='width:100%;font-size:12px' cellpadding=3 cellspacing=0>
			<tr style='font-weight:bold'>
				<td align=center style='border:0.1px solid #000'>".$_SESSION['lang']['nourut']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['kodebarang']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['namabarang']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['satuan']."</td>
				<td align=right style='border:0.1px solid #000'>".$_SESSION['lang']['jumlah']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['keterangan']."</td>
			</tr>";
			
		$str="select * from ".$dbname.".log_retursuppliernoninventorydt where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		$no=0;
		foreach($res as $val){
	
			$no++;
			$optbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			##SUDAH DITERIMAKAN
			$sudahditerima=0;
			$strx="select sum(jumlah) as jumlah from ".$dbname.".log_retursuppliernoninventorydt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."' and notransaksi!='".$notransaksi."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$sudahditerima+=$valx['jumlah'];				
			}
				
			##KUANTITAS PO/SO
			$jumlahpesan=0;
			$strx="select jumlahpesan from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$jumlahpesan=$valx['jumlahpesan'];				
			}
			
			$tab.="<tr>";
			$tab.="<td align=center style='border-left:0.1px solid #000'>".$no."</td>";
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$val['kodebarang']."</td>";
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$optbarang[$val['kodebarang']]."</td>";
			
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$val['satuan']."</td>";
			$tab.="<td align=right style='border-left:0.1px solid #000'>".hidezerodecimal($val['jumlah'],3)."</td>";
			if($urlefil=='0'){
				$tab.="<td align=left style='border-left:0.1px solid #000;border-right:0.1px solid #000'>".getkegiatan(@$val['subunit'],@$val['subunitdt'],@$val['kodekegiatan'],2)."</td>";
			}else{
				$tab.="<td align=left style='border-left:0.1px solid #000;border-right:0.1px solid #000'></td>";
			}
				
			$tab.="</tr>";
			
		}
			
		$tab.="<tr><td colspan=6 style='border-top:0.1px solid #000'>&nbsp;</td></tr></table>";
		
		$tab.="<table width=100% cellpadding=0 cellspacing=0 style='font-size:12px'>
			<tr style='text-align:center'>
				<td>Dibuat Oleh</td>
			
			</tr>
			<tr>
				<td height=25px colspan=3>&nbsp;</td>
			</tr>
			<tr style='text-align:center;text-decoration:underline;'>
				<td>".getNamaKaryawan($createdby)."</td>
			
			</tr>
			<tr style='text-align:center;'>
				<td>".getJabatanKaryawan($createdby)."</td>
				
			</tr>
		</table>";
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
	
		## Print Out
		if($urlefil=='0'){
			$dompdf->stream("Print RFQ", array("Attachment" => false));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
	break;
	

	case'previewpdfbars': 
		$tab="";
		
		$str="select a.*,b.* from ".$dbname.".".$table." a 
				left join ".$dbname.".".$tabledt." b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";

		$res=fetchdata($str);
		foreach ($res as $val) {
			$jlhsat[$val['notransaksi']] += $val['jumlah'];
		}
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		// $penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$supplier=$res[0]['supplierid'];
		$postedby=$res[0]['postedby'];
		$tanggalselesai=$res[0]['tanggalselesai'];
		$keterangan=$res[0]['keterangan'];
		$kodebarang=$res[0]['kodebarang'];
		// $disetujui=$res[0]['disetujui'];
		$satuan=$res[0]['satuan'];
		$createdby=$res[0]['createdby'];

		//Umar
		$penerima  = '';
		$disetujui = '';

		$str  = "SELECT karyawanid,level FROM ".$dbname.".approval WHERE notransaksi='".$param['notransaksi']."'";
		$res  = fetchdata($str); 
		$data = array();
		$jml  = count($res);
		
		if ($res > 0) {
			foreach ($res as $key => $value) {
				$data[$value['level']] = $value['karyawanid'];
			}

			$penerima  = $data['1'];
			$disetujui = $data['2'];
		}
		//End Umar
		
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
		$optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optalamat=makeOption($dbname,'organisasi','kodeorganisasi,alamat',"kodeorganisasi='".$pt."'");
		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$optbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
		
		
		$alamat=$optalamat[$pt];
		$pt=$optpt[$pt];
		$unit=$optunit[$unit];
		$supplier=$optsupplier[$supplier];
	
		$tab.="<table cellspacing=0 border=0 width=100% align=center>
			<tr>
				<td align=left width=55px><img src='images/ksp.jpg'  class='zImgOffBtn' style='width:50px;height:50px'></td>
				<td align=center style='font-weight:bold;font-size:24px'>".$pt."</td>
			</tr>
			<tr>
				<td align=center style='border-bottom:0.1px solid #000;font-size:12px' colspan=2>Alamat : ".$alamat."</td>
			</tr>
			<br>
			<tr>
				<td align=center style='font-weight:bold;padding-top:19px;font-size:19px' colspan=2><u>BERITA ACARA RETUR BARANG SUPPLIER</u></td>
			</tr>
			<tr>
				<td align=center style='font-size:15px' colspan=2>No : ".$param['notransaksi']."</td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:15px;text-align: justify;' width=100%>
			<tr>
				<td>Pada hari ini ".hari($tanggalselesai)." Tanggal ".kekata(substr($tanggalselesai,8,2))." Bulan ".numToMonth(substr($tanggalselesai,5,2),'I','long')." Tahun ".kekata(substr($tanggalselesai,0,4))." telah dicek dan dioperasikan ".$optbarang[$kodebarang]." dengan spesifikasi seperti dibawah ini :<br><br></td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:15px;padding-left:100px;padding-right:100px;' width=100%>
			<tr>
				<td width=40% rowspan=2 style='vertical-align:bottom'>
				<table>
					<tr>
						<td style='width:100px'>1. Jenis Pekerjaan</td>
						<td>=</td>
						<td>".$optbarang[$kodebarang]."</td>
					</tr>
					<tr>
						<td>2. ".$_SESSION['lang']['jumlah']."</td>
						<td>=</td>
						<td>".$jlhsat[$param['notransaksi']]." ".$satuan."</td>
					</tr>
					<tr>
						<td>3. ".$_SESSION['lang']['kontraktor']."</td>
						<td>=</td>
						<td>".$supplier."</td>
					</tr>
					<tr>
						<td>4. Service Order</td>
						<td>=</td>
						<td>".$nopo."</td>
					</tr>
					<tr>
						<td>5. Selesai Pekerjaan</td>
						<td>=</td>
						<td>".tglnmblnhr($tanggalselesai,'I','long')."</td>
					</tr>
					<tr>
						<td>6. Keterangan</td>
						<td>=</td>
						<td>".$keterangan."</td>
					</tr>
					<br>
				</table>
				</td>
			</tr>
		</table>";
		
		$tab.="<table style='width:100%;font-size:15px' cellspacing=0>
			<tr>
				<td>Dengan data ini maka pembayaran  dapat dilaksanakan oleh Finance ke Kontraktor ".$supplier.".</td>
			</tr>
			<tr>
				<td><p>Demikian Berita Acara Penyelesaian Pekerjaan ini dibuat agar dipergunakan sebagai mana mestinya.</p><br><br></td>
			</tr>
			</table>";
		
		$tab.="<table width=100% cellpadding=0 cellspacing=0 style='font-size:13px'>
			<tr style='text-align:center'>
				<td>Dibuat Oleh </td>
			
			</tr>
			<tr>
				<td height=100px colspan=3>&nbsp;</td>
			</tr>
			<tr style='text-align:center;text-decoration:underline;'>
				<td><b>".getNamaKaryawan($createdby)."</b></td>
				
			</tr>
			<tr style='text-align:center;'>
				<td>".getJabatanKaryawan($createdby)."</td>
				
			</tr>
		</table>";		
		
	
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		// $dompdf->stream("Print BA Penerimaan Barang", array("Attachment" => false));

		// if($urlefil=='0'){
			$dompdf->stream("Print Retur Supplier", array("Attachment" => false));
		// }else{
			// file_put_contents($urlefil, $dompdf->output());
		// }
		
	break;
	
	case'getsubunitdt':
		echo getsubunitdt($subunit,'','1');
	break;
	
	case'getkegiatan':
		echo getkegiatan($subunit,$subunitdt,'','1');
	break;
	
    default:
	break;
}


function getkegiatan($subunit,$subunitdt,$kegiatan,$stipe){
	global $dbname;
	global $owlPDO;
	
	$unit=substr($subunit,0,4);
	$hasil="";
	$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
	if($subunit!=''){
		if($unit==$subunit){
			$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun, kelompok,namakegiatan";
			if(substr($unit,2,2)=='HO'){
			$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok in ('KNT','KNT1') and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun,kelompok,namakegiatan";
			}
		}else{
			$strx="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$subunit."'";
			$resx=fetchdata($strx);
			$temptipe="";
			if(count($resx)>0){
				$temptipe=$resx[0]['tipe'];
			}
			
			if($temptipe=='WORKSHOP'){
				$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='WSH' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by noakun,kelompok desc,namakegiatan asc";
			}else{
				if($temptipe!=''){
					if($subunitdt!=''){
						######
						if($temptipe=='STENGINE' or $temptipe=='STATION' or $temptipe=='MAINTENANCE'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='MIL' and status = '1' order by noakun,kelompok,namakegiatan";
						}
						
						######
						if($temptipe=='AFDELING'){
							$strx="select statusblok from ".$dbname.".setup_blok where kodeorg='".$subunitdt."'";
							$resx=fetchdata($strx);
							$statusblok=$resx[0]['statusblok'];
							if($statusblok=='TM'){
								$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where (kelompok='TM' or kelompok='PNN') and status = '1' order by noakun,kelompok,namakegiatan";
							}else{
								$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='".$statusblok."' and status = '1' order by noakun,kelompok,namakegiatan";
							}
						}
						
						######
						if($temptipe=='SIPIL'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where (kelompok='SPL' or kelompok='KNT') and status = '1' order by noakun,kelompok,namakegiatan"; 
						}
						
						######
						if($temptipe=='TRAKSI'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='TRK' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by noakun,kelompok desc,namakegiatan";	   
						}
						
						######
						if($temptipe=='BIBITAN'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where  kelompok in ('BBT','MN','PN','KNT') and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun,kelompok,namakegiatan";
						}
					}
				}else{
					if($subunitdt!=''){
						
						$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun,kelompok,namakegiatan";
						if($subunit=='PROJECT'){
							if(substr($subunitdt,0,2)=='AK'){
								$tipeasset=substr($subunitdt,3,2);
								$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where noakun in (select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."') order by noakun,kelompok,namakegiatan";
							}
						}
						
						if($subunit=='KONTRAKTOR' || $subunit=='KUD' || $subunit=='SUPPLIER' || $subunit=='PETANI'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' and kodekegiatan like '116%' order by noakun,kelompok,namakegiatan";
						}
					}
				}
			}
		}
		// $str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$subunitdt."'";
		// $res=fetchdata($str);
		// $tipe=$res[0]['tipe'];
		// if($tipe!=''){
			// ######
			// if($tipe=='STENGINE' or $tipe=='STATION' or $tipe=='MAINTENANCE'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='MIL' and status = '1' order by kelompok,namakegiatan";
			// }
			
			// ######
			// if($tipe=='BLOK'){
				// $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$subunitdt."'";
				// $res=fetchdata($str);
				// $statusblok=$res[0]['statusblok'];
				
				// if($statusblok=='TM'){
					// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where (kelompok='TM' or kelompok='PNN') and status = '1' order by kelompok,namakegiatan";
				// }else{
					// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='".$statusblok."' and status = '1' order by kelompok,namakegiatan";
				// }
			// }
			
			// ######
			// if($tipe=='WORKSHOP'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='WSH' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok desc,namakegiatan asc";
			// }
			
			// ######
			// if($tipe=='SIPIL'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where (kelompok='SPL' or kelompok='KNT') and status = '1' order by kelompok,namakegiatan"; 
			// }
			
			// ######
			// if($tipe=='TRAKSI'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='TRK' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok desc,namakegiatan";	   
			// }
			
			// ######
			// if($tipe=='BIBITAN'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where  kelompok in ('BBT','MN','PN','KNT') and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok,namakegiatan";
			// }
		// }else{
			// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok,namakegiatan";
		// }
		
		if(isset($str)){
			$res=fetchdata($str);
			foreach($res as $val){
				$e=substr($val['noakun'],0,3);
				if($e!=$m){			
					$optkegiatan.="<optgroup label='".$e." - ".getNamaAkun($e)."'>";
				}
				$d=substr($val['noakun'],0,5);
				if($d!=$n){			
					$optkegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				}
				if($kegiatan==$val['kodekegiatan']){
					$hasil="[".$val['kelompok']."] - ".$val['namakegiatan'];
					$optkegiatan.="<option value='".$val['kodekegiatan']."' selected>".$val['noakun']." - ".$val['namakegiatan']."</option>";				
				}else{
					$optkegiatan.="<option value='".$val['kodekegiatan']."'>".$val['noakun']." - ".$val['namakegiatan']."</option>";
				}
				$n=$d;
				if($d!=$n){			
					$optkegiatan.="</optgroup>";
				}
				$m=$e;
				if($e!=$m){			
					$optkegiatan.="</optgroup>";
				}
			}
		}
	}
	
	if($stipe=='1'){
		return $optkegiatan;		
	}else if($tipe='2'){
		return $hasil;
	}
}

?>
