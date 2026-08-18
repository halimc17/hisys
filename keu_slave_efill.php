<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');

$notransaksi = checkPostGet('notransaksi','');
$sourceid = checkPostGet('sourceid','');
$idefill = checkPostGet('idefill','');
$showhideefil = checkPostGet('showhideefil','');

$createdtime=date('Y-m-d H:i:s');
$createdby=$_SESSION['standard']['userid'];
$path = "fileupload/filingsystem/";

switch ($method){
	case'viewefill':
		
		$tab.="<fieldset style='width:97.5%;'><legend>".$notransaksi."</legend>
		<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>Checklist E-Fill</td>
				<td align=center>Filename</td>";
				if($showhideefil!='hide'){
					$tab.="<td align=center>Upload File</td>";
				}
			$tab.="</tr>
			</thead>
			<tbody id='bodyefil'>";
			
			$tab.=loaddataefil($notransaksi);
		
		$tab.="</tbody></table>
		</fieldset>";
		echo $tab;
	break;
	
	case'uploadfile':
		$data = $_POST;
		
		## Get Data from E-Fill List
		$arrlistfm = array();
		$str="select * from ".$dbname.".filemanager where namafile='".$notransaksi."'";
		$res=fetchdata($str);
		$idinduk = $res[0]['id'];
		
		$optCriteria = makeOption($dbname,'fil_5mapcriteria','id,kriteria',"id='".$sourceid."'");
		$criteria = $optCriteria[$sourceid];
	
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $criteria." ".$notransaksi." ".$_FILES['file']['name'];
				$file_tmpname = $_FILES['file']['tmp_name'];
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 10000000){
						$str="select * from ".$dbname.".filemanager where induk='".$idinduk."' and sourceid='".$sourceid."' and namafile='".$filename."'";
						$res=fetchdata($str);
						if(count($res) <= 0){
							
							## Insert into filemanager / DB
							$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid,candelete) values('".$idinduk."','5','".$filename."','".$filetype."','','1','".$createdby."','".$createdtime."','".$createdby."','".$createdtime."','".$sourceid."','1')";
							$owlPDO->exec($str);
							
							$structure = setlocationfile($idinduk)."/".$filename;
							move_uploaded_file($file_tmpname,$structure);
						
						}else{
							exit("Warning : Item atau namafile sudah pernah terdaftar.");
						}
					}else{
						exit("Warning : Ukuran file upload maksimal 10Mb");
					}
				}else{
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx");
				}
			}
		}
		echo loaddataefil($notransaksi);
	break;
	
	case'deleteefil':
		$structure = setlocationfile($idefill);
		$str="delete from ".$dbname.".filemanager where id='".$idefill."'";
		$owlPDO->exec($str);
		unlink($structure);
		echo loaddataefil($notransaksi);
	break;
	
	case'insertefill':
		try{
			$owlPDO->beginTransaction();
			
			$optCriteria = makeOption($dbname,'fil_5mapcriteria','id,kriteria');
			$arrkelompok = gettipeefill($notransaksi);
			
			$periode = $arrkelompok['periode'];
			$keterangan = $arrkelompok['keterangan'];
			$kodeunit = $arrkelompok['unit'];
			$efilfold = $arrkelompok['foldername'];
			$noakun = $arrkelompok['noakun'];
			$thn = $arrkelompok['tahun'];
			$bln = $arrkelompok['bulan'];
			$tipe = $arrkelompok['tipe'];
			$tipekb = $arrkelompok['tipekb'];
			$tipetransaksi = $arrkelompok['tipetransaksi'];
			$folderbank=getbankefil($notransaksi);
			
			// ## Get ID Unit
			$optUnit = makeOption($dbname,'filemanager','namafile,id',"namafile='".$kodeunit."'");
			$idunit = $optUnit[$kodeunit];
			
			$levelfile=0;
			$efilnotrans = str_replace('/','',$notransaksi);
			if($tipekb=='Bank'){
				$structure = setlocationfile($idunit)."/".$efilfold."/".$periode."/".$tipekb."/".$folderbank;
				$str="select * from ".$dbname.".filemanager where induk='".getidfrompath($structure,'1')."' and namafile='".$folderbank."'";
				$res=fetchdata($str);
				$countbank=count($res);
				if($countbank <= 0){
					$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".getidfrompath($structure,'1')."','5','".$folderbank."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
					$owlPDO->exec($str);
					if (!mkdir($structure, 0777, true)){}
				}
				$structure = setlocationfile($idunit)."/".$efilfold."/".$periode."/".$tipekb."/".$folderbank."/".$efilnotrans;
				$levelfolder='6';
				$levelfile='7';
			}else{
				$structure = setlocationfile($idunit)."/".$efilfold."/".$periode."/".$tipekb."/".$efilnotrans;
				$levelfolder='5';
				$levelfile='6';
			}
			
			unset($arrkelompok['periode']);
			unset($arrkelompok['keterangan']);
			unset($arrkelompok['unit']);
			unset($arrkelompok['noakun']);
			unset($arrkelompok['tahun']);
			unset($arrkelompok['bulan']);
			unset($arrkelompok['foldername']);
			unset($arrkelompok['idtipe']);
			unset($arrkelompok['tipe']);
			unset($arrkelompok['arrjoinpo']);
			unset($arrkelompok['tipekb']);
			unset($arrkelompok['tipetransaksi']);
						
			if($tipe=='others' && $tipekb=='Bank' && $tipetransaksi=='K'){
				if(count($arrkelompok) > 0){
					$idresult = '';
					deleteefil($notransaksi,$structure);
					
					$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".getidfrompath($structure,'1')."','".$levelfolder."','".$notransaksi."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
					echo $str;
					$owlPDO->exec($str);
					$idresult = $owlPDO->lastInsertId();
					if (!mkdir($structure, 0777, true)){}
					
					foreach($arrkelompok as $key){						
						### GET Pengajuan Bansos
						if($key=="EPB"){
							$efilename = $optCriteria[$key]." ".$notransaksi." ".$keterangan.".pdf";
							$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
							$owlPDO->exec($str);

							$_GET['notransaksi'] = $keterangan;
							$_GET['method'] = 'pdf';
							$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
							
							include("lgl_slave_bansos.php");
							
							unset($_GET['notransaksi']);
							unset($_GET['method']);
							unset($_GET['urlefil']);
						}
						
						### GET Pengajuan Pembayaran
						if($key=="EPP"){
							$efilename = $optCriteria[$key]." ".$notransaksi." ".$keterangan.".pdf";
							$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
							$owlPDO->exec($str);

							$_GET['notransaksi'] = $keterangan;
							$_GET['method'] = 'pdf';
							$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
							
							include("lgl_slave_pp.php");
							
							unset($_GET['notransaksi']);
							unset($_GET['method']);
							unset($_GET['urlefil']);
						}
						
						### GET GRL
						if($key=="PLPG" || $key=="PGRLTT"){
							$optnotrans = makeOption($dbname,'lgl_pembebasanlahan','nosppt,notransaksi',"nosppt='".$keterangan."'");
							$str="select * from ".$dbname.".listfile_lgl_pemblahan where notransaksi='".$optnotrans[$keterangan]."' and kriteriaefil='".$key."'";
							$res=fetchdata($str);
							foreach($res as $key2=>$val2){
								$efilename = $val2['namafile'];
								$formaticon = $val2['formaticon'];
								$criteria = $val2['kriteriaefil'];
								$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
								$owlPDO->exec($str);
								
								$structurefrom = "fileupload/lgl_pemblahan/".str_replace('/','',$efilename);
								$structureto = $structure."/".str_replace('/','',$efilename);
								copy($structurefrom, $structureto);
							}
						}
						
						if($key=="GDM1" || $key=="GDM2" || $key=="GDM3"){
							$optnotrans = makeOption($dbname,'lgl_pembebasanlahan_vw','sppt,namamasyarakat',"sppt='".$keterangan."'");
							$str="select * from ".$dbname.".listfile_lgl_grltt where field3='".$optnotrans[$keterangan]."' and kriteriaefil='".$key."'";
							$res=fetchdata($str);
							foreach($res as $key2=>$val2){
								$efilename = $val2['namafile'];
								$formaticon = $val2['formaticon'];
								$criteria = $val2['kriteriaefil'];
								$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
								$owlPDO->exec($str);
								
								$structurefrom = "fileupload/lgl_GRLTT/".str_replace('/','',$efilename);
								$structureto = $structure."/".str_replace('/','',$efilename);
								copy($structurefrom, $structureto);
							}
						}
						
						if($key=="BAHPP" || $key=="DBKLP" || $key=="DSPPT" || $key=="EKG" || $key=="EPG" || $key=="ESPG" || $key=="FRG" || $key=="SKKDM" || $key=="SPPAW" || $key=="SPPHA" || $key=="SPPTG"){
							$optnotrans = makeOption($dbname,'pad_lahan','shm,pemilik',"shm='".$keterangan."'");
							$str="select * from ".$dbname.".listfile_lgl_grltt where field2='".$optnotrans[$keterangan]."' and kriteriaefil='".$key."'";
							$res=fetchdata($str);
							foreach($res as $key2=>$val2){
								$efilename = $val2['namafile'];
								$formaticon = $val2['formaticon'];
								$criteria = $val2['kriteriaefil'];
								$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
								$owlPDO->exec($str);
								
								$structurefrom = "fileupload/lgl_GRLTT/".str_replace('/','',$efilename);
								$structureto = $structure."/".str_replace('/','',$efilename);
								copy($structurefrom, $structureto);
							}
						}
						
						### GET Payment Voucher
						if($key=="EPV" || $key=="ERV"){
							$efilename = $optCriteria[$key]." ".$notransaksi.".pdf";
							$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
							$owlPDO->exec($str);
														
							$str="select * from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
							$res=fetchdata($str);

							$_GET['proses'] = 'pdf';
							$_GET['notransaksi'] = $notransaksi;
							$_GET['kodeorg'] = $res[0]['kodeorg'];
							$_GET['tipetransaksi'] = $res[0]['tipetransaksi'];
							$_GET['noakun'] = $res[0]['noakun'];
							$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
												
							include("keu_slave_kasbank_print_detail.php");
							
							unset($_GET['proses']);
							unset($_GET['notransaksi']);
							unset($_GET['kodeorg']);
							unset($_GET['tipetransaksi']);
							unset($_GET['noakun']);
							unset($_GET['urlefil']);
						}
						
						$str="select * from ".$dbname.".listfile_keu_kasbank where notransaksi='".$notransaksi."' and detail='".$key."'";
						$res=fetchdata($str);
						foreach($res as $key2=>$val2){
							$efilename = $val2['namafile'];
							$formaticon = $val2['formaticon'];
							$criteria = $val2['detail'];
							$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
							$owlPDO->exec($str);
							
							$structurefrom = "fileupload/keu_kasbankx/".str_replace('/','',$efilename);
							$structureto = $structure."/".str_replace('/','',$efilename);
							copy($structurefrom, $structureto);
						}
					}
				}
			}else{
				$str="select * from ".$dbname.".keu_efillinv where noinvoice='".$keterangan."'";
				$arrefilinv=fetchdata($str);
				$countefil = count($arrefilinv);
				if($countefil > 0){
					$idresult = '';
					deleteefil($notransaksi,$structure);
					
					$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".getidfrompath($structure,'1')."','".$levelfolder."','".$notransaksi."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
					$owlPDO->exec($str);
					$idresult = $owlPDO->lastInsertId();
					if (!mkdir($structure, 0777, true)){}
					
					foreach($arrefilinv as $key=>$val){
						$efilename = $val['namafile'];
						$formaticon = $val['formaticon'];
						$criteria = $val['criteria'];
						$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
						$owlPDO->exec($str);
						
						$structurefrom = "fileupload/efillinv/".str_replace('/','',$keterangan)."/".str_replace('/','',$efilename);
						$structureto = $structure."/".str_replace('/','',$efilename);
						@copy($structurefrom, $structureto);
					}
					
					foreach($arrkelompok as $key){
						if($key=='EPV' || $key=="ERV"){
							$efilename = $optCriteria[$key]." ".$notransaksi.".pdf";
							$strx="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
							$owlPDO->exec($strx);
							
							$str="select * from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
							$res=fetchdata($str);

							$_GET['proses'] = 'pdf';
							$_GET['notransaksi'] = $notransaksi;
							$_GET['kodeorg'] = $res[0]['kodeorg'];
							$_GET['tipetransaksi'] = $res[0]['tipetransaksi'];
							$_GET['noakun'] = $res[0]['noakun'];
							$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
												
							include("keu_slave_kasbank_print_detail.php");
							
							unset($_GET['proses']);
							unset($_GET['notransaksi']);
							unset($_GET['kodeorg']);
							unset($_GET['tipetransaksi']);
							unset($_GET['noakun']);
							unset($_GET['urlefil']);
						}else{
							$str="select * from ".$dbname.".listfile_keu_kasbank where notransaksi='".$notransaksi."' and detail='".$key."'";
							$res=fetchdata($str);
							foreach($res as $key2=>$val2){
								$efilename = $val2['namafile'];
								$formaticon = $val2['formaticon'];
								$criteria = $val2['detail'];
								$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','".$levelfile."','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
								$owlPDO->exec($str);
								
								$structurefrom = "fileupload/keu_kasbankx/".str_replace('/','',$efilename);
								$structureto = $structure."/".str_replace('/','',$efilename);
								@copy($structurefrom, $structureto);
							}
						}
					}
				}
			}
			
			// print_r($arrkelompok);
			
			// ##ARRAY
			// if($arrkelompok['uploadinvoice']!=''){
				// $arrefp[] = $arrkelompok['uploadinvoice'];				
			// }
			// if($arrkelompok['keterangan']!=''){				
				// $arrenh[] = $arrkelompok['keterangan'];
			// }
			
			// ## Get ID Unit
			// $optUnit = makeOption($dbname,'filemanager','namafile,id',"namafile='".$kodeunit."'");
			// $idunit = $optUnit[$kodeunit];		

			// ## Insert No transaction kas bank to efil
			// $efilnotrans = str_replace('/','',$notransaksi);
			// $structure = setlocationfile($idunit)."/".$efilfold."/".$periode."/".$efilnotrans;
			
			// $idresult = '';
			// $str="select * from ".$dbname.".filemanager where namafile='".$notransaksi."'";
			// $res=fetchdata($str);
			// if(count($res) <= 0 && $efilfold != ''){
				// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".getidfrompath($structure,'1')."','4','".$notransaksi."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
				// $owlPDO->exec($str);
				// $idresult = $owlPDO->lastInsertId();
				// if (!mkdir($structure, 0777, true)){
					// // throw new PDOException('Failed to create folders...');
				// }
			// }
			
			// $arrjoinpo = $arrkelompok['arrjoinpo'];
			// $exppo = explode(',',$arrjoinpo);
			
			// foreach($exppo as $key){
				// $arrpo[$key] = $key;
			// }
			
			// if(!empty($arrpo)){
				// ## Get No DPH
				// $arrdph = array();
				// if(!empty($arrpo)){
					// $str="select * from ".$dbname.".log_poht where nopo in ('".$arrjoinpo."') and (nodph is not null or nodph!='')";
					// $res=fetchdata($str);
					// if(count($res) > 0){
						// foreach($res as $key=>$val){
							// $arrdph[$val['nodph']] = $val['nodph'];
						// }
					// }
				// }
								
				// ## Get No RPH
				// $arrrph = array();
				// if(!empty($arrdph)){
					// $arrjoindph = join("','",$arrdph);
					// $str="select * from ".$dbname.".log_permintaanhargadt where norph in ('".$arrjoindph."') and (nomor is not null or nomor!='')";
					// $res=fetchdata($str);
					// foreach($res as $key=>$val){
						// $arrrph[$val['nomor']] = $val['nomor'];
					// }
				// }
				
				// ## Get Penawaran Harga Supplier
				// $arrphs = array();
				// if(!empty($arrrph)){
					// $arrjoinrph = join("','",$arrrph);
					// $str="select * from ".$dbname.".log_permintaanhargafile where nomor in ('".$arrjoinrph."') and status='1'";
					// $res=fetchdata($str);
					// $no=0;
					// foreach($res as $key=>$val){
						// $arrphs[$no]['namafile'] = $val['namafile'];
						// $arrphs[$no]['formaticon'] = $val['formaticon'];
						// $arrphs[$no]['url'] = "fileupload/rph/".$val['namafile'];
						// $no++;
					// }
				// }
				
				// ## Get Surat Penerimaan Barang
				// $arrgrn = array();
				// $arrfpb = array();
				// $str="select * from ".$dbname.".log_transaksidt where nopo in ('".$arrjoinpo."')";
				// $res=fetchdata($str);
				// foreach($res as $key=>$val){
					// $arrgrn[$val['notransaksi']] = $val['notransaksi'];
					// if($val['namafile']!='' && !is_null($val['namafile'])){
						// $arrfpb[$val['namafile']] = $val['namafile'];
					// }
				// }
				
				// ## Get Detail PR
				// $arrpr = array();
				// $arrpkb = array();
				// $str="select * from ".$dbname.".log_podt where nopo in ('".$arrjoinpo."')";
				// $res=fetchdata($str);
				// foreach($res as $key=>$val){
					// $arrpr[$val['nopp']] = $val['nopp'];
					
					// $exppr = explode('/',$val['nopp']);
					// $unitpr = $exppr[4];
					
					// ## Get Detail Barang dari PR/SR
					// $arrpkb[$val['kodebarang']."".$val['nopp']]['kodebarang'] = $val['kodebarang'];
					// $arrpkb[$val['kodebarang']."".$val['nopp']]['nopp'] = $val['nopp'];
					// $arrpkb[$val['kodebarang']."".$val['nopp']]['tglpr'] = $tglpr;
					// $arrpkb[$val['kodebarang']."".$val['nopp']]['unit'] = $unitpr;
				// }
			// }
			
			// if($idresult!=''){
				// foreach($arrkelompok as $key){
					// switch($key){
						// ### GET Data Pemakaian Barang
						// case'EDP':
							// if(!empty($arrpkb)){
								// foreach($arrpkb as $key2=>$val2){
									// $tgl2 = $val2['tglpr'];
									// $expperiode1 = explode('-',$tgl2);
									// $tgl1 = "01-01-".$expperiode1[0];
									
									// $str="select * FROM ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".$val2['unit']."' and tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem(tanggalnormal($tgl2))."' and tipetransaksi='5' and kodebarang='".$val2['kodebarang']."'";
									// $res=fetchdata($str);
									// $countdata = count($res);
									
									// if($countdata > 0){
										// $efilename = $optCriteria[$key]." ".$notransaksi." ".$val2['kodebarang']." ".$val2['nopp'].".xls";
										// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.xls','','1','','".$createdtime."','','".$createdtime."','".$key."')";
										// $owlPDO->exec($str);
										
										// $_POST['proses'] = "excel";
										// $_POST['tgl1'] = $tgl1;
										// $_POST['tgl2'] = tanggalnormal($tgl2);
										// $_POST['unit'] = $val2['unit'];
										// $_POST['barang'] = $val2['kodebarang'];
										// $_POST['urlefil'] = $structure."/".str_replace('/','',$efilename);
										
										// include("log_slave_2pemakaianbarang.php");
										
										// unset($_POST['proses']);
										// unset($_POST['tgl1']);
										// unset($_POST['tgl2']);
										// unset($_POST['unit']);
										// unset($_POST['barang']);
										// unset($_POST['urlefil']);
									// }
								// }
							// }
						// break;
						
						// ### GET Faktur Pajak
						// case'EFP':
							// if(!empty($arrefp) > 0){
								// foreach($arrefp as $key2){
									// $expnamafile = explode('.',$key2);
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2;
									// $formaticon = ".".$expnamafile[1];
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									
									// $structurefrom = "filegis/".$key2;
									// $structureto = $structure."/".str_replace('/','',$efilename);
									// copy($structurefrom, $structureto);
								// }
							// }
						// break;
						
						// ### GET Foto Penerimaan Barang	
						// case'EFPB':
							// if(!empty($arrfpb)){
								// foreach($arrfpb as $key2){
									// $expnamafile = explode('.',$key2);
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2;
									// $formaticon = ".".$expnamafile[1];
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									
									// $structurefrom = "fileupload/penerimaanbarang/".$key2;
									// $structureto = $structure."/".str_replace('/','',$efilename);
									// copy($structurefrom, $structureto);
								// }
							// }
						// break;
						
						// ### GET Nota Hutang
						// case'ENH':
							// if(!empty($arrenh)){
								// foreach($arrenh as $key2){
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".pdf";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									// $_GET['proses'] = 'pdf';
									// $_GET['noinvoice'] = $key2;
									// $_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("keu_slave_tagihan_print_detail.php");
									
									// unset($_GET['proses']);
									// unset($_GET['noinvoice']);
									// unset($_GET['urlefil']);
								// }
							// }
						// break;
						
						// ### GET Penawaran Harga Supplier
						// case'EPHS':
							// if(!empty($arrphs)){
								// foreach($arrphs as $key2=>$val2){
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$val2['namafile'];
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','".$val2['formaticon']."','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									
									// $structureto = $structure."/".str_replace('/','',$efilename);
									// copy($val2['url'], $structureto);
								// }
							// }
						// break;
							
						// ### GET PO
						// case'EPO':
							// if(!empty($arrpo)){
								// foreach($arrpo as $key2){
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".pdf";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									// $_GET['table'] = "log_poht";
									// $_GET['column'] = $key2;
									// $_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("log_slave_print_detail_po.php");
									
									// unset($_GET['table']);
									// unset($_GET['column']);
									// unset($_GET['urlefil']);
								// }
							// }
						// break;
						
						// ### GET PR/SR
						// case'EPR':
							// if(!empty($arrpr)){
								// foreach($arrpr as $key2){
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".pdf";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									// $_GET['table'] = "log_prapoht";
									// $_GET['column'] = $key2;
									// $_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("log_slave_print_log_pp.php");
									
									// unset($_GET['table']);
									// unset($_GET['column']);
									// unset($_GET['urlefil']);
								// }
							// }
						// break;
							
						// ### GET Riwayat Perbandingan Harga
						// case'ERPH':
							// if(!empty($arrrph)){
								// foreach($arrrph as $key2){
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".pdf";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									// $_GET['table'] = "log_perintaanhargaht";
									// $_GET['column'] = $key2.",1";
									// $_GET['cond'] = "";
									// $_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("log_slave_print_permintaan_penawaran_v2.php");
									
									// unset($_GET['table']);
									// unset($_GET['column']);
									// unset($_GET['cond']);
									// unset($_GET['urlefil']);
								// }
							// }
						// break;
						
						// ### GET Surat Penerimaan Barang	
						// case'EGRN':
							// if(!empty($arrgrn)){
								// foreach($arrgrn as $key2){
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".pdf";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									// $_GET['notransaksi'] = $key2;
									// $_GET['namespace'] = $key."".$no;
									// $_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("log_slave_print_bapb_pdf.php");
									
									// unset($_GET['notransaksi']);
									// unset($_GET['namespace']);
									// unset($_GET['urlefil']);
								// }
							// }
						// break;
						
						// ### GET Pengajuan Bansos
						// case'EPB':
							// $efilename = $optCriteria[$key]." ".$notransaksi." ".$keterangan.".pdf";
							// $strx="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
							// $owlPDO->exec($strx);

							// $_POST['notransaksi'] = $keterangan;
							// $_POST['method'] = 'pdf';
							// $_POST['urlefil'] = $structure."/".str_replace('/','',$efilename);
							
							// include("lgl_slave_bansos.php");
							
							// unset($_POST['notransaksi']);
							// unset($_POST['method']);
							// unset($_POST['urlefil']);
						// break;
						
						// ### GET Dokumen Upload
						// case'EDU':
							// if($efilfold=='BANSOS'){
								// $strx="select * from ".$dbname.".listfile_lgl_bansos where notransaksi='".$keterangan."'";
								// $resx=fetchdata($strx);
								// foreach($resx as $keyx=>$valx){
									// $expnamafile = explode('.',$valx['namafile']);
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$valx['namafile'];
									// $formaticon = ".".$expnamafile[1];
									// $stry="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($stry);
									
									// $structurefrom = "fileupload/lgl_bansos/".$valx['namafile'];
									// $structureto = $structure."/".str_replace('/','',$efilename);
									// copy($structurefrom, $structureto);
								// }
							// }
						// break;
						
						// ### GET Draft SPK / Proposal
						// case'EDSPK':
							// if(!empty($arrpo)){
								// foreach($arrpo as $key2){
									// $strx="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$key2."' and status='1'";
									// $resx=fetchdata($strx);
									// if(count($resx) > 0){
										// foreach($resx as $keyx=>$valx){
											// $expnamafile = explode('.',$valx['namafile']);
											// $efilename = $optCriteria[$key]." ".$notransaksi." ".$valx['namafile'];
											// $formaticon = ".".$expnamafile[1];
											// $stry="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$key."')";
											// $owlPDO->exec($stry);
											
											// $structurefrom = "fileupload/lgl_pengajuanspk/".$valx['namafile'];
											// $structureto = $structure."/".str_replace('/','',$efilename);
											// copy($structurefrom, $structureto);
										// }
									// }
									
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".pdf";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									
									// $_GET['proses'] = "pdf";
									// $_GET['notransaksi'] = $key2;
									// $_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("log_slave_spk_print_detailv2.php");
									
									// unset($_GET['proses']);
									// unset($_GET['notransaksi']);
									// unset($_GET['urlefil']);
								// }
							// }
						// break;
						
						// ### GET Pengajuan SPK
						// case'EPSPK':
							// if(!empty($arrpo)){
								// foreach($arrpo as $key2){
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".xls";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.xls','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									
									// $_POST['method'] = "html";
									// $_POST['tipe'] = 'excel';
									// $_POST['notransaksi'] = $key2;
									// $_POST['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("lgl_slave_pengajuanspk.php");
									
									// unset($_POST['method']);
									// unset($_POST['tipe']);
									// unset($_POST['notransaksi']);
									// unset($_POST['urlefil']);
								// }
							// }
						// break;
						
						// ### GET EBAPP
						// case'EBAPP':
							// if(!empty($arrpo)){
								// foreach($arrpo as $key2){
									// $str="select * from ".$dbname.".log_spkht where notransaksi='".$key2."'";
									// $res=fetchdata($str);
									// $bappkodeorg = $res[0]['kodeorg'];
									// $bappkoderekanan = $res[0]['koderekanan'];
									// $bappdivisi = $res[0]['divisi'];
									
									// $efilename = $optCriteria[$key]." ".$notransaksi." ".$key2.".pdf";
									// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
									// $owlPDO->exec($str);
									
									// $_GET['proses'] = "pdf";
									// $_GET['notransaksi'] = $key2;
									// $_GET['kodeorg'] = $bappkodeorg;
									// $_GET['koderekanan'] = $bappkoderekanan;
									// $_GET['divisi'] = $bappdivisi;
									// $_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									// include("log_slave_realisasispk_print_detail.php");
									
									// unset($_GET['proses']);
									// unset($_GET['notransaksi']);
									// unset($_GET['kodeorg']);
									// unset($_GET['koderekanan']);
									// unset($_GET['divisi']);
									// unset($_GET['urlefil']);
								// }
							// }
						// break;
						
						// ### Summary Payroll
						// case'ESP':
							// if($noakun=='2160101'){
								
							// }
						// break;
					// }
				// }
			// }
			
			// exit("\nerror :");
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}
	break;
}

function loaddataefil($notransaksi){
	global $showhideefil;
	global $dbname;
	global $owlPDO;
	
	## Get Data from E-Fill List
	$arrlistfm = array();
	$str="select * from ".$dbname.".filemanager where namafile='".$notransaksi."'";
	$res=fetchdata($str);
	$idinduk = $res[0]['id'];
	
	$str="select * from ".$dbname.".filemanager where induk='".$idinduk."'";
	$res=fetchdata($str);
	$no=0;
	foreach($res as $key=>$val){
		$arrlistfm[$val['sourceid']][$no]['id'] = $val['id'];
		$arrlistfm[$val['sourceid']][$no]['namafile'] = $val['namafile'];
		$arrlistfm[$val['sourceid']][$no]['candelete'] = $val['candelete'];
		$no++;
	}
	
	$arrkelompok = gettipeefill($notransaksi);
	unset($arrkelompok['periode']);
	unset($arrkelompok['keterangan']);
	unset($arrkelompok['unit']);
	unset($arrkelompok['uploadinvoice']);
	unset($arrkelompok['foldername']);
	unset($arrkelompok['idtipe']);
	unset($arrkelompok['tipe']);
	unset($arrkelompok['arrjoinpo']);
	unset($arrkelompok['noakun']);
	unset($arrkelompok['tahun']);
	unset($arrkelompok['bulan']);
	unset($arrkelompok['tipekb']);
	
	foreach($arrkelompok as $key){
		$optChecklist = makeOption($dbname,'fil_5mapcriteria','id,kriteria',"id='".$key."'");
		$tab.="<tr class=rowcontent>
			<td align=left>".$optChecklist[$key]."</td>
			<td>
			<table style='font-weight:normal;font-size:12px'>";
				$nox=0;
				if(isset($arrlistfm[$key])){
					foreach($arrlistfm[$key] as $keyx){
						$nox++;
						if($keyx['candelete'] == '1'){
							$candelete = "<img src='images/skyblue/delete.png' class='zImgBtn' onclick=\"deleteefil('".$notransaksi."','".$keyx['id']."','".$keyx['namafile']."')\" title='Delete Efill'>";
						}else{
							$candelete = "";
						}
						$tab.="<tr>";
							if($showhideefil!='hide'){
								$tab.="<td style='vertical-align:top;width:18px;text-align:center'>".$candelete."</td>";
							}
							$tab.="<td style='vertical-align:top'>".$nox.". </td>";
							$tab.="<td style='vertical-align:top;color:blue;cursor:pointer'><a href='".setlocationfile($keyx['id'])."' download style='none'>".$keyx['namafile']."</a></td>";
						$tab.="</tr>";
					}
				}
			$tab.="</table>
			</td>";
			if($showhideefil!='hide'){
				$tab.="<td>
					<input type='file' name='upload_".$key."' id='upload_".$key."' class=mybutton>&nbsp;
					<img id='addfile' title='Add New Item' style='width:6%' onclick=\"addfile('".$notransaksi."','".$key."')\" src='images/plus.png' style='cursor:pointer'>
				</td>";
			}
		$tab.="</tr>";
	}
	
	return $tab;
}

function getidfrompath($path,$count='0'){
	global $dbname;
	global $owlPDO;
	
	$path = str_replace("fileupload/filingsystem/","",$path);
	$exppath = explode('/',$path);
	$no = 0;
	$temp = "";
	$where = "";
	foreach($exppath as $key){
		$no++;
		$str="select id from ".$dbname.".filemanager where namafile='".$key."'";
		$res=fetchdata($str);
		if(count($res) > 0 && $no <= (count($exppath)-$count)){
			if($temp==''){
				$where="";
			}else{
				$where= "and induk='".$temp."'";
			}
			$strx="select id,induk from ".$dbname.".filemanager where namafile='".$key."' ".$where."";
			$resx=fetchdata($strx);
			
			$value=$resx[0]['id'];
			$temp=$resx[0]['id'];
		}
	}
	
	return $value;
}

function getidfilemanager($id,$level='0'){
	global $dbname;
	global $owlPDO;
	
	$str="select * from ".$dbname.".filemanager where sourceid='".$id."' and level='".$level."'";
	$res=fetchData($str);
	$val=$res[0]['id'];
		
	return $val;
}

function delete_directory($dirname){
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	
	if (!$dir_handle)
		return false;
	
	while($file = readdir($dir_handle)) 
	{
		if ($file != "." && $file != "..") 
		{
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delete_directory($dirname.'/'.$file);
	       }
	 }
	 closedir($dir_handle);
	 rmdir($dirname);
	 return true;
}

function deleteefil($notransaksi,$structure){
	global $dbname;
	global $owlPDO;
	
	$optId=makeOption($dbname,'filemanager','namafile,id',"namafile='".$notransaksi."'");
	$id=$optId[$notransaksi];
	
	$str="delete from ".$dbname.".filemanager where namafile='".$notransaksi."'";
	$owlPDO->exec($str);
	
	if($id!=''){
		$str="delete from ".$dbname.".filemanager where induk='".$id."'";
		$owlPDO->exec($str);
	}
	
	delete_directory($structure);
	return true;
}
?>
