<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');

$noinvoice = checkPostGet('noinvoice','');
$criteria = checkPostGet('criteria','');
$namafile = checkPostGet('namafile','');

$createdtime=date('Y-m-d H:i:s');
$createdby=$_SESSION['standard']['userid'];

switch ($method){
	case'viewefill':
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$noinvoice."'";
		$res=fetchdata($str);
		$showhideefil = ($res[0]['posting']=='1'?"hide":"show");
		
		$tab.="<fieldset style='width:97.5%;'><legend>".$noinvoice."</legend>
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
			
			$tab.=loaddataefil($noinvoice,$showhideefil);
		
		$tab.="</tbody></table>
		</fieldset>";
		echo $tab;
	break;
	
	case'viewefilltgh':
		$str="select * from ".$dbname.".keu_penagihanht where noinvoice='".$noinvoice."'";
		$res=fetchdata($str);
		$showhideefil = ($res[0]['posting']=='1'?"hide":"show");
		
		$tab.="<fieldset style='width:97.5%;'><legend>".$noinvoice."</legend>
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
			
			$tab.=loaddataefilpgh($noinvoice,$showhideefil);
		
		$tab.="</tbody></table>
		</fieldset>";
		echo $tab;
	break;
	
	case'uploadfile':
		$data = $_POST;
		
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$noinvoice."'";
		$res=fetchdata($str);
		$showhideefil = ($res[0]['posting']=='1'?"hide":"show");
		
		$optCriteria = makeOption($dbname,'fil_5mapcriteria','id,kriteria',"id='".$criteria."'");
		$criteriaefil = $optCriteria[$criteria];
	
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $criteriaefil." ".$noinvoice." ".$_FILES['file']['name'];
				$file_tmpname = $_FILES['file']['tmp_name'];
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 10000000){
						$str="select * from ".$dbname.".keu_efillinv where criteria='".$criteria."' and namafile='".$filename."'";
						$res=fetchdata($str);
						if(count($res) <= 0){
							
							## Insert into filemanager / DB
							$str="insert into ".$dbname.".keu_efillinv (noinvoice,namafile,formaticon,criteria,candelete,createdby,createdtime,updateby,updatetime) values('".$noinvoice."','".$filename."','".$filetype."','".$criteria."','1','".$createdby."','".$createdtime."','".$createdby."','".$createdtime."')";
							$owlPDO->exec($str);
							$structure = "fileupload/efillinv/".$noinvoice."/".$filename;
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
		echo loaddataefil($noinvoice,$showhideefil);
	break;
	
	case'uploadfilepgh':
		$data = $_POST;
		
		$str="select * from ".$dbname.".keu_penagihanht where noinvoice='".$noinvoice."'";
		$res=fetchdata($str);
		$showhideefil = ($res[0]['posting']=='1'?"hide":"show");
		
		$optCriteria = makeOption($dbname,'fil_5mapcriteria','id,kriteria',"id='".$criteria."'");
		$criteriaefil = $optCriteria[$criteria];
	
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $criteriaefil." ".$noinvoice." ".$_FILES['file']['name'];
				$file_tmpname = $_FILES['file']['tmp_name'];
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 10000000){
						$str="select * from ".$dbname.".keu_efillinv where criteria='".$criteria."' and namafile='".$filename."'";
						$res=fetchdata($str);
						if(count($res) <= 0){
							
							## Insert into filemanager / DB
							$str="insert into ".$dbname.".keu_efillinv (noinvoice,namafile,formaticon,criteria,candelete,createdby,createdtime,updateby,updatetime) values('".$noinvoice."','".$filename."','".$filetype."','".$criteria."','1','".$createdby."','".$createdtime."','".$createdby."','".$createdtime."')";
							$owlPDO->exec($str);
							$structure = "fileupload/efillinv/".str_replace('/','',$noinvoice)."/".str_replace('/','',$filename);
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
		echo loaddataefilpgh($noinvoice,$showhideefil);
	break;
	
	case'deleteefil':
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$noinvoice."'";
		$res=fetchdata($str);
		$showhideefil = ($res[0]['posting']=='1'?"hide":"show");
	
		$structure = "fileupload/efillinv/".$noinvoice."/".$namafile;
		$str="delete from ".$dbname.".keu_efillinv where noinvoice='".$noinvoice."' and namafile='".$namafile."'";
		$owlPDO->exec($str);
		unlink($structure);
		echo loaddataefil($noinvoice,$showhideefil);
	break;
	
	case'deleteefilpgh':
		$str="select * from ".$dbname.".keu_penagihanht where noinvoice='".$noinvoice."'";
		$res=fetchdata($str);
		$showhideefil = ($res[0]['posting']=='1'?"hide":"show");
	
		$structure = "fileupload/efillinv/".str_replace('/','',$noinvoice)."/".$namafile;
		$str="delete from ".$dbname.".keu_efillinv where noinvoice='".$noinvoice."' and namafile='".$namafile."'";
		$owlPDO->exec($str);
		unlink($structure);
		echo loaddataefilpgh($noinvoice,$showhideefil);
	break;
	
	case'insertefill':
		try{
			$owlPDO->beginTransaction();
			
			$optCriteria = makeOption($dbname,'fil_5mapcriteria','id,kriteria');
			$arrkelompok = gettghefill($noinvoice);
			
			## Create New Folder INV
			$structure = "fileupload/efillinv/".$noinvoice;
			@mkdir($structure, 0777, true);
			
			$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$noinvoice."'";
			$res=fetchdata($str);
			$nopo = $res[0]['nopo'];
			
			if($nopo != ''){
				## Get No DPH
				$arrdph = array();
				$str="select * from ".$dbname.".log_poht where nopo='".$nopo."' and (nodph is not null or nodph!='')";
				$res=fetchdata($str);
				if(count($res) > 0){
					foreach($res as $key=>$val){
						$arrdph[$val['nodph']] = $val['nodph'];
					}
				}
				
				## Get No RPH
				$arrrph = array();
				if(!empty($arrdph)){
					$arrjoindph = join("','",$arrdph);
					$str="select * from ".$dbname.".log_permintaanhargadt where norph in ('".$arrjoindph."') and (nomor is not null or nomor!='')";
					$res=fetchdata($str);
					foreach($res as $key=>$val){
						$arrrph[$val['nomor']] = $val['nomor'];
					}
				}
				
				## Get Detail PR
				$arrpr = array();
				$arrpkb = array();
				$str="select * from ".$dbname.".log_podt where nopo = '".$nopo."'";
				$res=fetchdata($str);
				foreach($res as $key=>$val){
					$arrpr[$val['nopp']] = $val['nopp'];
					
					$exppr = explode('/',$val['nopp']);
					$unitpr = $exppr[4];
					
					$opttglpr = makeOption($dbname,'log_prapoht','nopp,tanggal',"nopp='".$val['nopp']."'");
					$tglpr=$opttglpr[$val['nopp']];
					
					## Get Detail Barang dari PR/SR
					$arrpkb[$val['kodebarang']."".$val['nopp']]['kodebarang'] = $val['kodebarang'];
					$arrpkb[$val['kodebarang']."".$val['nopp']]['nopp'] = $val['nopp'];
					$arrpkb[$val['kodebarang']."".$val['nopp']]['tglpr'] = $tglpr;
					$arrpkb[$val['kodebarang']."".$val['nopp']]['unit'] = $unitpr;
				}
			}
			
			if(count($arrkelompok) > 0){
				foreach($arrkelompok as $key){
					$optModul = makeOption($dbname,'fil_5mapcriteria','id,modul',"id='".$key."'");
					$modul = $optModul[$key];
					
					if($modul!=''){
						$str = "";
						if($modul=='TGH'){
							$path = "filegis";
							$str="select * from ".$dbname.".listfileupload where notransaksi='".$noinvoice."' and kriteriaefil='".$key."'";
							$res=fetchdata($str);
							if(count($res) > 0){
								foreach($res as $key2=>$val2){
									$efilename = $optCriteria[$key]." ".$noinvoice." ".$val2['namafile'];
									insertefillinv($noinvoice,$efilename,$val2['formaticon'],$key);
									
									$structurefrom = $path."/".$val2['namafile'];
									$structureto = $structure."/".str_replace('/','',$efilename);
									copy($structurefrom, $structureto);
								}
							}
						}else if($modul=='PR'){
							if(!empty($arrpr)){
								foreach($arrpr as $keyx){
									$path = "fileupload/pp";
									$str="select * from ".$dbname.".listfileupload where notransaksi='".$keyx."' and kriteriaefil='".$key."'";
									$res=fetchdata($str);
									if(count($res) > 0){
										foreach($res as $key2=>$val2){
											$efilename = $optCriteria[$key]." ".$noinvoice." ".$val2['namafile'];
											insertefillinv($noinvoice,$efilename,$val2['formaticon'],$key);
											
											$structurefrom = $path."/".$val2['namafile'];
											$structureto = $structure."/".str_replace('/','',$efilename);
											copy($structurefrom, $structureto);
										}
									}
								}
							}
						}else if($modul=='RPH'){
							### GET Penawaran Harga Supplier
							if(!empty($arrrph)){
								$arrjoinrph = join("','",$arrrph);
								$str="select * from ".$dbname.".log_permintaanhargafile where nomor in ('".$arrjoinrph."') and status='1' and kriteriaefil='".$key."'";
								$res=fetchdata($str);
								$no=0;
								foreach($res as $key2=>$val2){
									$efilename = $optCriteria[$key]." ".$noinvoice." ".$val2['namafile'];
									insertefillinv($noinvoice,$efilename,$val2['formaticon'],$key);
									
									$structureto = $structure."/".str_replace('/','',$efilename);
									copy("fileupload/rph/".$val2['namafile'], $structureto);
								}
							}
						}else if($modul=='SPK'){
							if($nopo!=''){
								$path = "fileupload/lgl_pengajuanspk";
								$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$nopo."' and kriteriaefil='".$key."'";
								$res=fetchdata($str);
								if(count($res) > 0){
									foreach($res as $key2=>$val2){
										$efilename = $optCriteria[$key]." ".$noinvoice." ".$val2['namafile'];
										insertefillinv($noinvoice,$efilename,$val2['formaticon'],$key);
										
										$structurefrom = $path."/".$val2['namafile'];
										$structureto = $structure."/".str_replace('/','',$efilename);
										copy($structurefrom, $structureto);
									}
								}
							}
						}else if($modul=='GRN'){
							if($nopo!=''){
								$path = "fileupload/log_penerimaanx";
								$str="select * from ".$dbname.".log_transaksidt where nopo='".$nopo."'";
								$res=fetchdata($str);
								foreach($res as $key2=>$val2){
									$strx="select * from ".$dbname.".listfile_log_penerimaan where notransaksi='".$val2['notransaksi']."' and namafile like '%".$val2['kodebarang']."%' and detail='".$key."'";
									$resx=fetchdata($strx);
									if(count($resx) > 0){
										foreach($resx as $keyx=>$valx){
											$efilename = $optCriteria[$key]." ".$noinvoice." ".$valx['namafile'];
											insertefillinv($noinvoice,$efilename,$valx['formaticon'],$key);
											$structurefrom = $path."/".$valx['namafile'];
											$structureto = $structure."/".str_replace('/','',$efilename);
											copy($structurefrom, $structureto);
										}
									}
								}
							}
						}
					}else{
						switch($key){
							### GET Data Pemakaian Barang
							case'EDP':
								if(!empty($arrpkb)){
									foreach($arrpkb as $key2=>$val2){
										$tgl2 = $val2['tglpr'];
										$expperiode1 = explode('-',$tgl2);
										$tgl1 = "01-01-".$expperiode1[0];
										
										$str="select * FROM ".$dbname.".log_transaksi_vw where left(kodegudang,4)='".$val2['unit']."' and tanggal between '".tanggalsystem($tgl1)."' and '".$tgl2."' and tipetransaksi='5' and kodebarang='".$val2['kodebarang']."'";
										$res=fetchdata($str);
										$countdata = count($res);
										
										if($countdata > 0){
											$efilename = $optCriteria[$key]." ".$noinvoice." ".$val2['kodebarang']." ".$val2['nopp'].".xls";
											insertefillinv($noinvoice,$efilename,'.xls',$key);
											
											$_POST['proses'] = "excel";
											$_POST['tgl1'] = $tgl1;
											$_POST['tgl2'] = tanggalnormal($tgl2);
											$_POST['unit'] = $val2['unit'];
											$_POST['barang'] = $val2['kodebarang'];
											$_POST['urlefil'] = $structure."/".str_replace('/','',$efilename);
											
											include("log_slave_2pemakaianbarang.php");
											
											unset($_POST['proses']);
											unset($_POST['tgl1']);
											unset($_POST['tgl2']);
											unset($_POST['unit']);
											unset($_POST['barang']);
											unset($_POST['urlefil']);
										}
									}
								}
							break;
							
							### GET PO
							case'EPO':
								if($nopo!=''){
									$efilename = $optCriteria[$key]." ".$noinvoice." ".$nopo.".pdf";
									insertefillinv($noinvoice,$efilename,'.pdf',$key);
									
									$_GET['table'] = "log_poht";
									$_GET['column'] = $nopo;
									$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									include("log_slave_print_detail_po.php");
									
									unset($_GET['table']);
									unset($_GET['column']);
									unset($_GET['urlefil']);
								}
							break;
							
							### GET PR/SR
							case'EPR':
								if(!empty($arrpr)){
									foreach($arrpr as $key2){
										$efilename = $optCriteria[$key]." ".$noinvoice." ".$key2.".pdf";
										insertefillinv($noinvoice,$efilename,'.pdf',$key);
										
										$_GET['table'] = "log_prapoht";
										$_GET['column'] = $key2;
										$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
										
										include("log_slave_print_log_pp.php");
										
										unset($_GET['table']);
										unset($_GET['column']);
										unset($_GET['urlefil']);
									}
								}
							break;
							
							### GET Riwayat Perbandingan Harga
							case'ERPH':
								if(!empty($arrrph)){
									foreach($arrrph as $key2){
										$efilename = $optCriteria[$key]." ".$noinvoice." ".$key2.".pdf";
										insertefillinv($noinvoice,$efilename,'.pdf',$key);
										
										$_GET['table'] = "log_perintaanhargaht";
										$_GET['column'] = $key2.",1";
										$_GET['cond'] = "";
										$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
										
										include("log_slave_print_permintaan_penawaran_v2.php");
										
										unset($_GET['table']);
										unset($_GET['column']);
										unset($_GET['cond']);
										unset($_GET['urlefil']);
									}
								}
							break;
							
							### GET Nota Hutang
							case'ENH':
								$efilename = $optCriteria[$key]." ".$noinvoice.".pdf";
								insertefillinv($noinvoice,$efilename,'.pdf',$key);
								$_GET['proses'] = 'pdf';
								$_GET['noinvoice'] = $noinvoice;
								$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
								
								include("keu_slave_tagihan_print_detail.php");
								
								unset($_GET['proses']);
								unset($_GET['noinvoice']);
								unset($_GET['urlefil']);
							break;
							
							### GET Pengajuan SPK
							case'EPSPK':
								if($nopo!=''){
									$efilename = $optCriteria[$key]." ".$noinvoice." ".$nopo.".xls";
									insertefillinv($noinvoice,$efilename,'.xls',$key);
									
									$_POST['method'] = "html";
									$_POST['tipe'] = 'excel';
									$_POST['notransaksi'] = $nopo;
									$_POST['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									include("lgl_slave_pengajuanspk.php");
									
									unset($_POST['method']);
									unset($_POST['tipe']);
									unset($_POST['notransaksi']);
									unset($_POST['urlefil']);
								}
							break;
							
							### GET EBAPP
							case'EBAPP':
								if($nopo!=''){
									$str="select * from ".$dbname.".log_spkht where notransaksi='".$nopo."'";
									$res=fetchdata($str);
									$countbapp = count($res);
									$bappkodeorg = $res[0]['kodeorg'];
									$bappkoderekanan = $res[0]['koderekanan'];
									$bappdivisi = $res[0]['divisi'];
									
									if($countbapp > 0){
										$efilename = $optCriteria[$key]." ".$noinvoice." ".$nopo.".pdf";
										insertefillinv($noinvoice,$efilename,'.pdf',$key);
																		
										$_GET['proses'] = "pdf";
										$_GET['notransaksi'] = $nopo;
										$_GET['kodeorg'] = $bappkodeorg;
										$_GET['koderekanan'] = $bappkoderekanan;
										$_GET['divisi'] = $bappdivisi;
										$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
										
										include("log_slave_realisasispk_print_detail.php");
										
										unset($_GET['proses']);
										unset($_GET['notransaksi']);
										unset($_GET['kodeorg']);
										unset($_GET['koderekanan']);
										unset($_GET['divisi']);
										unset($_GET['urlefil']);
									}
								}
							break;
							
							### GET Surat Penerimaan Barang	
							case'EGRN':
								if($nopo!=''){
									$str="select * from ".$dbname.".log_transaksidt where nopo='".$nopo."'";
									$res=fetchdata($str);
									foreach($res as $key2=>$val2){
										$efilename = $optCriteria[$key]." ".$noinvoice." ".$val2['notransaksi'].".pdf";
										insertefillinv($noinvoice,$efilename,'.pdf',$key);
										
										$_GET['notransaksi'] = $val2['notransaksi'];
										$_GET['namespace'] = $key."".$no;
										$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
										
										include("log_slave_print_bapb_pdf.php");
										
										unset($_GET['notransaksi']);
										unset($_GET['namespace']);
										unset($_GET['urlefil']);
									}
								}
							break;
						}
					}
				}
			}
			
			// print_r($arrkelompok);
			
			// exit("\nerror");
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}
	break;
	
	case'insertefilltgh':
		try{
			$owlPDO->beginTransaction();
			
			$optCriteria = makeOption($dbname,'fil_5mapcriteria','id,kriteria');
			$arrkelompok = getpghefill($noinvoice);
			// global $nokontrak;
			$nokontrak = $arrkelompok['nokontrak'];
			$noinvoicex = $noinvoice;
			## Create New Folder INV
			$structure = "fileupload/efillinv/".str_replace('/','',$noinvoice);
			@mkdir($structure, 0777, true);
			unset($arrkelompok['nokontrak']);
			if(count($arrkelompok) > 0){
				foreach($arrkelompok as $key){
					$optModul = makeOption($dbname,'fil_5mapcriteria','id,modul',"id='".$key."'");
					$modul = $optModul[$key];
					
					if($modul!=''){
						$str = "";
						if($modul=='PGH'){
							$path = "fileupload/keu_penagihan/";
							$str="select * from ".$dbname.".listfile_keu_penagihan where nomor='".$noinvoice."' and kriteriaefil='".$key."'";
							$res=fetchdata($str);
							if(count($res) > 0){
								foreach($res as $key2=>$val2){
									$efilename = $optCriteria[$key]." ".$noinvoice." ".$val2['namafile'];
									insertefillinv($noinvoice,$efilename,$val2['formaticon'],$key);
									
									$structurefrom = $path."/".$val2['namafile'];
									$structureto = $structure."/".str_replace('/','',$efilename);
									copy($structurefrom, $structureto);
								}
							}
						}
					}else{
						
						switch($key){
							### GET No Kontrak
							case'EKP':
								if($nokontrak!=''){
									$efilename = $optCriteria[$key]." ".$noinvoice." ".$nokontrak.".pdf";
									insertefillinv($noinvoice,$efilename,'.pdf',$key);
									$_GET['table'] = "pmn_kontrakjual";
									$_GET['column'] = $nokontrak;
									$_GET['cond'] = "";
									$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
									
									include("pmn_kontakjual_pdf.php");
									
									unset($_GET['table']);
									unset($_GET['column']);
									unset($_GET['cond']);
									unset($_GET['urlefil']);
								}
							break;
							
							### GET NO DO
							case'EDO':
								$str="select * from ".$dbname.".pmn_suratperintahpengiriman where nokontrak='".$nokontrak."'";
								$res=fetchdata($str);
								if(count($res) > 0){
									foreach($res as $key2){
										$efilename = $optCriteria[$key]." ".$noinvoice." ".$key2['nodo'].".pdf";
										insertefillinv($noinvoice,$efilename,'.pdf',$key);
										
										$_GET['table'] = "pmn_suratperintahpengiriman";
										$_GET['column'] = $key2['nodo'];
										$_GET['cond'] = "";
										$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
										
										include("pmn_slave_print_pdf_suratperintahpengiriman.php");
										
										unset($_GET['table']);
										unset($_GET['column']);
										unset($_GET['cond']);
										unset($_GET['urlefil']);
									}
								}
							break;
							
							### GET Nota Piutang
							case'ENP':
								$efilename = $optCriteria[$key]." ".$noinvoice.".pdf";
								insertefillinv($noinvoice,$efilename,'.pdf',$key);
								$_GET['table'] = 'keu_penagihanht';
								$_GET['column'] = $noinvoice;
								$_GET['cond'] = '';
								$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
								
								include("keu_slave_print_pengihan.php");
								unset($_GET['table']);
								unset($_GET['column']);
								unset($_GET['cond']);
								unset($_GET['urlefil']);
							break;
						}
					}
				}
			}
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}
	break;
}

## Insert to table keu_efillinv
function insertefillinv($noinvoice,$efilename,$formaticon,$criteria){
	global $dbname;
	global $owlPDO;
	global $createdtime;
	
	$str="select * from ".$dbname.".keu_efillinv where noinvoice='".$noinvoice."' and namafile='".$efilename."' and criteria='".$criteria."'";
	$res=fetchdata($str);
	$countefillinv = count($res);
	
	if($countefillinv <= 0){
		$str="insert into ".$dbname.".keu_efillinv (noinvoice,namafile,formaticon,criteria,candelete,createdby,createdtime,updateby,updatetime) values('".$noinvoice."','".$efilename."','".$formaticon."','".$criteria."','0','','".$createdtime."','','".$createdtime."')";
		$owlPDO->exec($str);
	}
}

function loaddataefil($noinvoice,$showhideefil){
	global $dbname;
	global $owlPDO;
	
	$str="select * from ".$dbname.".keu_efillinv where noinvoice='".$noinvoice."'";
	$res=fetchdata($str);
	$no=0;
	foreach($res as $key=>$val){
		$arrlistfm[$val['criteria']][$no]['id'] = $val['id'];
		$arrlistfm[$val['criteria']][$no]['namafile'] = $val['namafile'];
		$arrlistfm[$val['criteria']][$no]['candelete'] = $val['candelete'];
		$no++;
	}
	
	$arrkelompok = gettghefill($noinvoice);
	
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
						$structure = "fileupload/efillinv/".$noinvoice."/".str_replace('/','',$keyx['namafile']);
						if($keyx['candelete'] == '1'){
							$candelete = "<img src='images/skyblue/delete.png' class='zImgBtn' onclick=\"deleteefil('".$noinvoice."','".$keyx['namafile']."')\" title='Delete Efill'>";
						}else{
							$candelete = "";
						}
						$tab.="<tr>";
							if($showhideefil!='hide'){
								$tab.="<td style='vertical-align:top;width:18px;text-align:center'>".$candelete."</td>";
							}
							$tab.="<td style='vertical-align:top'>".$nox.". </td>";
							$tab.="<td style='vertical-align:top;color:blue;cursor:pointer'><a href='".$structure."' download style='none'>".$keyx['namafile']."</a></td>";
						$tab.="</tr>";
					}
				}
			$tab.="</table>
			</td>";
			if($showhideefil!='hide'){
				$tab.="<td>
					<input type='file' name='upload_".$key."' id='upload_".$key."' class=mybutton>&nbsp;
					<img id='addfile' title='Add New Item' style='width:6%' onclick=\"addfiledata('".$noinvoice."','".$key."')\" src='images/plus.png' style='cursor:pointer'>
				</td>";
			}
		$tab.="</tr>";
	}
	
	return $tab;
}

function loaddataefilpgh($noinvoice,$showhideefil){
	global $dbname;
	global $owlPDO;
	
	$str="select * from ".$dbname.".keu_efillinv where noinvoice='".$noinvoice."'";
	$res=fetchdata($str);
	$no=0;
	foreach($res as $key=>$val){
		$arrlistfm[$val['criteria']][$no]['id'] = $val['id'];
		$arrlistfm[$val['criteria']][$no]['namafile'] = $val['namafile'];
		$arrlistfm[$val['criteria']][$no]['candelete'] = $val['candelete'];
		$no++;
	}
	
	$arrkelompok = getpghefill($noinvoice);
	unset($arrkelompok['nokontrak']);
	
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
						$structure = "fileupload/efillinv/".str_replace('/','',$noinvoice)."/".str_replace('/','',$keyx['namafile']);
						if($keyx['candelete'] == '1'){
							$candelete = "<img src='images/skyblue/delete.png' class='zImgBtn' onclick=\"deleteefil('".$noinvoice."','".$keyx['namafile']."')\" title='Delete Efill'>";
						}else{
							$candelete = "";
						}
						$tab.="<tr>";
							if($showhideefil!='hide'){
								$tab.="<td style='vertical-align:top;width:18px;text-align:center'>".$candelete."</td>";
							}
							$tab.="<td style='vertical-align:top'>".$nox.". </td>";
							$tab.="<td style='vertical-align:top;color:blue;cursor:pointer'><a href='".$structure."' download style='none'>".$keyx['namafile']."</a></td>";
						$tab.="</tr>";
					}
				}
			$tab.="</table>
			</td>";
			if($showhideefil!='hide'){
				$tab.="<td>
					<input type='file' name='upload_".$key."' id='upload_".$key."' class=mybutton>&nbsp;
					<img id='addfile' title='Add New Item' style='width:6%' onclick=\"addfiledata('".$noinvoice."','".$key."')\" src='images/plus.png' style='cursor:pointer'>
				</td>";
			}
		$tab.="</tr>";
	}
	
	return $tab;
}
?>
