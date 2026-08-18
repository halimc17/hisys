<?php
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

// $path = "fileupload/filingsystem/";

// $str="select * from ".$dbname.".filemanager where induk='1'";
// $res=fetchdata($str);
// foreach($res as $key=>$val){
	// $structure = $path."".str_replace('/','',$val['namafile']);

	// $str2="select * from ".$dbname.".filemanager where induk='".$val['id']."'";
	// $res2=fetchdata($str2);
	// foreach($res2 as $key2=>$val2){
		// $structure2 = $structure."/".str_replace('/','',$val2['namafile']);
		
		// $str3="select * from ".$dbname.".filemanager where induk='".$val2['id']."'";
		// $res3=fetchdata($str3);
		// foreach($res3 as $key3=>$val3){
			// $structure3 = $structure2."/".str_replace('/','',$val3['namafile']);
			
			// $str4="select * from ".$dbname.".filemanager where induk='".$val3['id']."'";
			// $res4=fetchdata($str4);
			// foreach($res4 as $key4=>$val4){
				// $structure4 = $structure3."/".str_replace('/','',$val4['namafile']);
				
				// $str5="select * from ".$dbname.".filemanager where induk='".$val4['id']."'";
				// $res5=fetchdata($str5);
				// foreach($res5 as $key5=>$val5){
					// $structure5 = $structure4."/".str_replace('/','',$val5['namafile']);					
					
					// $str6="select * from ".$dbname.".filemanager where induk='".$val5['id']."'";
					// $res6=fetchdata($str6);
					// foreach($res6 as $key6=>$val6){
						// $structure6 = $structure5."/".str_replace('/','',$val6['namafile']);

						// if (!mkdir($structure6, 0777, true)){}
						// echo $structure6."<br>";
					// }
				// }
			// }
		// }
	// }	
// }

//==============================
//==============================
//==============================
$notransaksi = checkPostGet('notransaksi','');
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
	
	// ## Get ID Unit
	$optUnit = makeOption($dbname,'filemanager','namafile,id',"namafile='".$kodeunit."'");
	$idunit = $optUnit[$kodeunit];
	
	$efilnotrans = str_replace('/','',$notransaksi);
	$structure = setlocationfile($idunit)."/".$efilfold."/".$periode."/".$tipekb."/".$efilnotrans;
	
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
	
	
	if($tipe=='others'){
		if(count($arrkelompok) > 0){
			$str="select * from ".$dbname.".filemanager where namafile='".$notransaksi."'";
			$res=fetchdata($str);
			$idresult = $res[0]['id'];
			// deleteefil($notransaksi,$structure);
			
			// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".getidfrompath($structure,'1')."','5','".$notransaksi."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
			// $owlPDO->exec($str);
			// $idresult = $owlPDO->lastInsertId();
			if (!mkdir($structure, 0777, true)){}
			
			foreach($arrkelompok as $key){						
				### GET Pengajuan Bansos
				if($key=="EPB"){
					$efilename = $optCriteria[$key]." ".$notransaksi." ".$keterangan.".pdf";
					// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','6','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
					// $owlPDO->exec($str);

					$_POST['notransaksi'] = $keterangan;
					$_POST['method'] = 'pdf';
					$_POST['urlefil'] = $structure."/".str_replace('/','',$efilename);
					
					include("lgl_slave_bansos.php");
					
					unset($_POST['notransaksi']);
					unset($_POST['method']);
					unset($_POST['urlefil']);
				}
				
				### GET Payment Voucher
				if($key=="EPV"){
					$efilename = $optCriteria[$key]." ".$notransaksi.".pdf";
					// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','6','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
					// $owlPDO->exec($str);
												
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
					// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','6','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
					// $owlPDO->exec($str);
					
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
			$str="select * from ".$dbname.".filemanager where namafile='".$notransaksi."'";
			$res=fetchdata($str);
			$idresult = $res[0]['id'];
			// $idresult = '';
			// deleteefil($notransaksi,$structure);
			
			// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".getidfrompath($structure,'1')."','5','".$notransaksi."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
			// $owlPDO->exec($str);
			// $idresult = $owlPDO->lastInsertId();
			if (!mkdir($structure, 0777, true)){}
			
			foreach($arrefilinv as $key=>$val){
				$efilename = $val['namafile'];
				$formaticon = $val['formaticon'];
				$criteria = $val['criteria'];
				// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','6','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
				// $owlPDO->exec($str);
				
				$structurefrom = "fileupload/efillinv/".$keterangan."/".str_replace('/','',$efilename);
				$structureto = $structure."/".str_replace('/','',$efilename);
				@copy($structurefrom, $structureto);
			}
			
			foreach($arrkelompok as $key){
				if($key=='EPV'){
					$efilename = $optCriteria[$key]." ".$notransaksi.".pdf";
					// $strx="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','6','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','".$key."')";
					// $owlPDO->exec($strx);
					
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
						// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','6','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','".$criteria."')";
						// $owlPDO->exec($str);
						
						$structurefrom = "fileupload/keu_kasbankx/".str_replace('/','',$efilename);
						$structureto = $structure."/".str_replace('/','',$efilename);
						@copy($structurefrom, $structureto);
					}
				}
			}
		}
	}
	$owlPDO->commit();
}catch(PDOException $e){
	$owlPDO->rollback();
	echo "warning, " . addslashes($e->getMessage());
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