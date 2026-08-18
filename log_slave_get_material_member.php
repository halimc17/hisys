<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('phpqrcode/qrlib.php');

	$kelompok	= isset($_POST['mayor'])? $_POST['mayor']: '';
    $kodebarang = isset($_POST['kodebarang'])? $_POST['kodebarang']: '';
	$namabarang = isset($_POST['namabarang'])? $_POST['namabarang']: '';
	$satuan     = isset($_POST['satuan'])? $_POST['satuan']: '';
	$statusbarang= isset($_POST['statusbarang'])? $_POST['statusbarang']: '';
	$subkelompokbarangcr= isset($_POST['subkelompokbarangcr'])? $_POST['subkelompokbarangcr']: '';
	$minstok    = isset($_POST['minstok'])? $_POST['minstok']: '';
	$konversi   = isset($_POST['konversi'])? $_POST['konversi']: '';
	$nokartu    = isset($_POST['nokartu'])? $_POST['nokartu']: '';
	$method	    = isset($_POST['method'])? $_POST['method']: '';
	$jenis	    = isset($_POST['jenis'])? $_POST['jenis']: '';
	$ongkir	    = isset($_POST['ongkir'])? $_POST['ongkir']: '';
	$idkategori	    = isset($_POST['kategoribarang'])? $_POST['kategoribarang']: '';
	$jenissch	    = isset($_POST['jenissch'])? $_POST['jenissch']: '';
	$inisial = isset($_POST['inisial'])? $_POST['inisial']: '';
	$kodevhc = isset($_POST['kodevhc'])? $_POST['kodevhc']: '';
	$skonversi = array ("0"=>"No","1"=>"Yes");
	$jnsapp = "MB";
	
	$persetujuan    = checkPostGet('persetujuan','');
	$kodevhc    = checkPostGet('kodevhc','');
	
	//$strx='select 1=1';
	switch($method){
		case 'delete':
			$strx="delete from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."' and kelompokbarang='".$kelompok."'";
			try{
				$owlPDO->exec($strx); 
				
				$str="delete from ".$dbname.".approval where notransaksi='".$kodebarang."' and jenispersetujuan='".$jnsapp."'";
				try
				{
					$owlPDO->exec($str); 
				}
				catch(PDOException $e)
				{
					echo " Gagal," . addslashes($e->getMessage());
				}
			}catch (PDOException $e){
				echo " Gagal,".addslashes($e->getMessage());
			}
		break;
		case 'update':
			$hargasatuan="";
			if(isset($_SESSION['thargasatuan'])){
				$nourut = 0;
				foreach($_SESSION['thargasatuan'] as $key=>$val){
					if($nourut==0){
						$hargasatuan .= $val['kodeorganisasi'];
					}else{
						$hargasatuan .= ",".$val['kodeorganisasi'];
					}
					$nourut++;
				}
			}
			
			$qData = selectQuery($dbname,'log_5masterbarang','*',"kodebarang='".$kodebarang."'");
			$resData = fetchData($qData);
			$oldData = $resData[0];
			
			
			
			// $strx="update ".$dbname.".log_5masterbarang set 
			       // namabarang='".addslashes($namabarang)."',
			       // inisial='".saveKutip($inisial)."',
			       // satuan='".$satuan."',minstok=".$minstok.",ongkir=".$ongkir.",
				   // nokartubin='".$nokartu."',konversi='".$konversi."',jenis='".$jenis."',inactive='1',
				   // hargasatuan='".$hargasatuan."',
				   // updateby='".$_SESSION['standard']['userid']."',
				   // updatetime='".date('Y-m-d H:i:s')."',
				   // idkategorinya='".$idkategori."', 
				   // kodevhc='".$kodevhc."' 
				   // where kelompokbarang='".$kelompok."'
				   // and kodebarang='".$kodebarang."'";
				   
			$arrdatabaru=array(
				'namabarang'   =>addslashes($namabarang),
				'inisial'      =>saveKutip($inisial),
				'satuan'       =>$satuan,
				'minstok'      =>$minstok,
				'ongkir'       =>$ongkir,
				'nokartubin'   =>$nokartu,
				'konversi'     =>$konversi,
				'jenis'        =>$jenis,
				'inactive'     =>'1',
				'hargasatuan'  =>$hargasatuan,
				'updateby'     =>$_SESSION['standard']['userid'],
				'updatetime'   =>date('Y-m-d H:i:s'),
				'idkategorinya'=>$idkategori, 
				'kodevhc'      =>$kodevhc
			);
			
			$textchange='';
			foreach ($arrdatabaru as $field => $val) {
				$oldData[$field] = preg_replace("/\r|\n/", "", $oldData[$field]);
				if($val=='*'){
					$val='';
				}

				if($oldData[$field]!=$val){
					if($textchange==''){
						$textchange='###'.$field.'###';
					}else{
						$textchange.=$field.'###';
					}
				}
			}
			
			if($textchange==''){
				exit("Error: Tidak ada perubahan.");
			}
			foreach($resData as $val){
				$data = array(
					'kelompokbarang'=>$val['kelompokbarang'],
					'kodebarang'    =>$val['kodebarang'],
					'namabarang'    =>$val['namabarang'],
					'satuan'        =>$val['satuan'],
					'minstok'       =>$val['minstok'],
					'nokartubin'    =>$val['nokartubin'],
					'konversi'      =>$val['konversi'],
					'inactive'      =>$val['inactive'],
					'inisial'       =>$val['inisial'],
					'ongkir'        =>$val['ongkir'],
					'jenis'         =>$val['jenis'],
					'hargasatuan'   =>$val['hargasatuan'],
					'createby'      =>$val['createby'],
					'createtime'    =>$val['createtime'],
					'updateby'      =>$val['updateby'],
					'updatetime'      =>$val['updatetime'],
					'namasales'     =>$val['namasales'],
					'idkategorinya' =>$val['idkategorinya'],
					'kodevhc'       =>$val['kodevhc'],
					'datachange'    =>$textchange
				);
			}
			$query = insertQuery($dbname,'log_5masterbarang_hist',$data,array_keys($data));
			try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
			$wheredatabaru = "kelompokbarang='".$kelompok."' and kodebarang='".$kodebarang."'";
			$strx = updateQuery($dbname,'log_5masterbarang',$arrdatabaru,$wheredatabaru);
			try{
				$owlPDO->exec($strx);
				
				$strx="delete from ".$dbname.".approval where notransaksi='".$kodebarang."' and jenispersetujuan='".$jnsapp."'";
				try{
					$owlPDO->exec($strx); 
					$msgdt = "Update Master Barang dengan detail ".$kodebarang." : ".addslashes($namabarang)." menunggu approval dari Bapak/Ibu";
					
					$listpersetujuan=$_POST['persetujuan'];
					foreach($listpersetujuan as $key=>$val){
						$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jnsapp."' and level='".$key."' and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
						//exit('Error : '.$str);
						$res=fetchData($str);
						$tipeapp = $res[0]['tipe'];
						$departemenapp = $res[0]['departemen'];
						$tipekaryawanapp = $res[0]['tipekaryawan'];
						$jabatanapp = $res[0]['jabatan'];
						
						if($tipeapp=='1'){
							if($departemenapp!=''){
								$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
								$res=fetchdata($str);
								foreach($res as $keyx=>$valx){
									$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
									$owlPDO->exec($str);
									createnotif($kodebarang,'AMB',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
								}
							}
							if($tipekaryawanapp!=''){
								$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
								$res=fetchdata($str);
								foreach($res as $keyx=>$valx){
									$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
									$owlPDO->exec($str);
									createnotif($kodebarang,'AMB',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
								}
							}
							if($jabatanapp!='0'){
								$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
								$res=fetchdata($str);
								foreach($res as $keyx=>$valx){
									$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
									$owlPDO->exec($str);
									createnotif($kodebarang,'AMB',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
								}
							}
						}else{
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
							try{
								$owlPDO->exec($str);
								createnotif($kodebarang,'AMB',$msgdt,$listpersetujuan[$key],date('Y-m-d H:i:s'));
							}catch (PDOException $e){
								print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
							}
						}
					}
				}catch(PDOException $e){
					echo " Gagal," . addslashes($e->getMessage());
				}
				
				$filename = "/images/qrcode/".$kodebarang.".png";
				if(file_exists($filename)){	
				}else{
					$folder="images/qrcode/";
					$file_name=$kodebarang.".png";
					$file_name=$folder.$file_name;
					QRcode::png($kodebarang,$file_name);
					
					header("Content-type: image/png");
					$imgPath = $file_name;
					$image = imagecreatefrompng($imgPath);
					$color = imagecolorallocate($image, 0, 0, 0);
					$string = $kodebarang;
					$fontSize = 2;
					$x = 20;
					$y = 74;
					imagestring($image, $fontSize, $x, $y, $string, $color);
					
					imagepng($image,$file_name);
					imagedestroy($image);
				}
			}catch (PDOException $e){
				echo " Gagal,".addslashes($e->getMessage());
			}				   
		break;	
		
		case'createqrcode':
			// $str="select * from ".$dbname.".log_5masterbarang";
			// $res=fetchdata($str);
			// foreach($res as $key=>$val){
				// $kodebarang=$val['kodebarang'];
				// $filename = "/images/qrcode/".$kodebarang.".png";
				// if(file_exists($filename)) 
				// {}
				// else 
				// {
					// $folder="images/qrcode/";
					// $file_name=$kodebarang.".png";
					// $file_name=$folder.$file_name;
					// QRcode::png($kodebarang,$file_name);
					
					// header("Content-type: image/png");
					// $imgPath = $file_name;
					// $image = imagecreatefrompng($imgPath);
					// $color = imagecolorallocate($image, 0, 0, 0);
					// $string = $kodebarang;
					// $fontSize = 2;
					// $x = 20;
					// $y = 74;
					// imagestring($image, $fontSize, $x, $y, $string, $color);
					
					// imagepng($image,$file_name);
					// imagedestroy($image);
				// }
			// }
		break;
		
		case 'insert':
			$hargasatuan="";
			if(count($_SESSION['thargasatuan']<=0)){
				$nourut = 0;
				foreach($_SESSION['thargasatuan'] as $key=>$val){
					if($nourut==0){
						$hargasatuan .= $val['kodeorganisasi'];
					}else{
						$hargasatuan .= ",".$val['kodeorganisasi'];
					}
					$nourut++;
				}
			}
		
			$strx="insert into ".$dbname.".log_5masterbarang(
			       kelompokbarang,kodebarang,namabarang,inisial,satuan,minstok,
				   nokartubin,konversi,jenis,inactive,ongkir,hargasatuan,createby,updateby,idkategorinya,kodevhc,createtime)
			values('".$kelompok."','".$kodebarang."','"
			         .saveKutip($namabarang)."','"
			         .saveKutip($inisial)."','".$satuan."',".$minstok.",
					 '".$nokartu."',".$konversi.",'".$jenis."','1','".$ongkir."','".$hargasatuan."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$idkategori."','".$kodevhc."','".date('Y-m-d H:i:s')."')";
			
			try{
				$owlPDO->exec($strx); 
				
				$msgdt = "Penambahan Master Barang baru dengan detail ".$kodebarang." : ".addslashes($namabarang)." menunggu approval dari Bapak/Ibu";
				
				$str="delete from ".$dbname.".approval where notransaksi='".$kodebarang."' and jenispersetujuan='".$jnsapp."' ";
				$owlPDO->exec($str);
				
				$listpersetujuan=$_POST['persetujuan'];
				foreach($listpersetujuan as $key=>$val){
					$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jnsapp."' and level='".$key."'";
					$res=fetchData($str);
					$tipeapp = $res[0]['tipe'];
					$departemenapp = $res[0]['departemen'];
					$tipekaryawanapp = $res[0]['tipekaryawan'];
					$jabatanapp = $res[0]['jabatan'];
					
					if($tipeapp=='1'){
						if($departemenapp!=''){
							$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
								
								createnotif($kodebarang,'AMB',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
							}
						}
						if($tipekaryawanapp!=''){
							$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
								createnotif($kodebarang,'AMB',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
							}
						}
						if($jabatanapp!='0'){
							$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
								createnotif($kodebarang,'AMB',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
							}
						}
					}else{
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kodebarang."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
						try{
							$owlPDO->exec($str);
							createnotif($kodebarang,'AMB',$msgdt,$valx['karyawanid'],date('Y-m-d H:i:s'));
						}catch (PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
						}
					}
				}
				
				$folder="images/qrcode/";
				$file_name=$kodebarang.".png";
				$file_name=$folder.$file_name;
				QRcode::png($kodebarang,$file_name);
			}catch (PDOException $e){
				echo " Gagal,".addslashes($e->getMessage());
			}					    
		break;
		default:
        break;	
	}//end switch	

// kamus spek        
	$arrjeni=array('slow'=>'Slow Moving','fast'=>'Fast Moving','non'=>'Non Moving');
	$str = "select * from ".$dbname.".log_5klbarang";
	$res = fetchdata($str);
	foreach($res as $bar){
		$klbarang[$bar['kode']]=$bar['kelompok'];
	}

	$str = "select * from ".$dbname.".log_5subklbarang";
	$res = fetchdata($str);
	foreach($res as $bar){
		$subklbarang[$bar['kode']]=$bar['namasubkelompok'];
	}

	$str="select kodebarang, depan, samping, atas, spesifikasi from ".$dbname.".log_5photobarang
		where kodebarang like '".$kelompok."%'"; #tidak sama dengan laba/rugi berjalan  
	//=================================================
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$spek[$bar->kodebarang]=$bar->spesifikasi;
		$depan[$bar->kodebarang]=$bar->depan;
		$samping[$bar->kodebarang]=$bar->samping;
		$atas[$bar->kodebarang]=$bar->atas;
	}         
	$optkodevh=['0'=>'Tidak','1'=>'Wajib Terisi'];
	$where='';
	if($jenissch!=''){
		$where.=" and jenis='".$jenissch."'";
	}
	
	if($subkelompokbarangcr!=''){
		$where.=" and kodebarang like '".$subkelompokbarangcr."%'";
	}
	
	if($persetujuan=='all'){
	}elseif($persetujuan=='1'){
		$where.=" and inactive like '".$persetujuan."%' and kodebarang in (select notransaksi from ".$dbname.".approval where jenispersetujuan = 'MB' and status!='1')";
	}else{
		$where.=" and inactive like '".$persetujuan."%'";
	}
	
	if($kodebarang!=''){
		$where.=" and kodebarang like '%".$kodebarang."%'";
	}
	#= Convert
	$convertKategori = makeOption($dbname,"log_5kategoribarang","id,jenis");
	
	//if search text is passing then search the item on given group
	$txtfind= isset($_POST['txtcari'])? trim($_POST['txtcari']): '';
	if(isset($_POST['txtcari']) && $txtfind!='' && $kelompok!='All')
		$str="select * from ".$dbname.".log_5masterbarang where namabarang like '%".$txtfind."%' and kelompokbarang='".$kelompok."' ".$where." order by namabarang";
	else if(isset($_POST['txtcari']) && $txtfind!=='' && $kelompok=='All')
		$str="select * from ".$dbname.".log_5masterbarang where namabarang like '%".$txtfind."%' ".$where." order by namabarang";
	else if(isset($_POST['txtcari']) && $txtfind=='' && $kelompok=='All')
		$str="select * from ".$dbname.".log_5masterbarang where 1=1 ".$where."  order by kodebarang asc";
	else
	    $str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='".$kelompok."' ".$where." order by namabarang";
	
// echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($bar=$res->fetch())
	{
		$stru="select * from ".$dbname.".log_5photobarang where kodebarang='".$bar->kodebarang."'";
		//echo $stru;
		$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
		if(owlBaris($resu)>0)
		{
			//if(empty($spek[$depan[$bar->kodebarang]]) and empty($samping[$bar->kodebarang]) and empty($atas[$bar->kodebarang]) and empty($spek[$bar->kodebarang])){
			//	$adx="<img src=images/tool.png class=resicon height=16px title='Edit Detail' onclick=editDetailbarang('".$bar->kodebarang."',event)>";
			//}else{
				$adx="<img src=images/zoom.png class=resicon height=16px title='View detail'  onclick=viewDetailbarang('".$bar->kodebarang."',event)>
					<img src=images/tool.png class=resicon height=16px title='Edit Detail'  onclick=editDetailbarang('".$bar->kodebarang."',event)>";
			//}
		}
		else
		{
			$adx="<img src=images/zoom.png class=resicon height=16px title='View detail'  onclick=viewDetailbarang('".$bar->kodebarang."',event)>
			<img src=images/tool.png class=resicon height=16px title='Edit Detail' onclick=editDetailbarang('".$bar->kodebarang."',event)>";
		}
		
		$no+=1;
		echo"<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td align=center>".$bar->kelompokbarang."</td>
		<td align=left>".$klbarang[$bar->kelompokbarang]."</td>
		<td align=center>".substr($bar->kodebarang,0,5)."</td>
		<td align=left>".$subklbarang[substr($bar->kodebarang,0,5)]."</td>
		<td align=center>".$bar->kodebarang."</td>
		<td>".$bar->namabarang."</td>
		<td align=center>".$bar->satuan."</td>
		<td align=center>".$arrjeni[$bar->jenis]."</td>
		<td style=display:none>".(isset($spek[$bar->kodebarang])? $spek[$bar->kodebarang]: '')."</td>
		<td align=right style=display:none>".$bar->minstok."</td>
		<td style=display:none>".$bar->nokartubin."</td>
		<td align=center>".$skonversi[$bar->konversi]."</td>
		<td align=center>".$bar->inisial."</td>
		<td align=center>".($bar->inactive=='1' ? 'Non-Aktif' : ($bar->inactive=='3' ? 'Ditolak' : '<label style="color:blue;cursor:pointer" title="Klik untuk non-aktif barang" onclick="nonaktifbarang('.$bar->kodebarang.')">Aktif</label>'))."<input style='display:none' type=checkbox id='br".$bar->kodebarang."' value='".$bar->kodebarang."' ".($bar->inactive==0?"":" checked")." onclick=setInactive(this.value);></td>
		
		<td align=center>".getKary($bar->createby)."<br>".($bar->updatetime!='0000-00-00 00:00:00'?$bar->updatetime:"")."</td>
		<td align=center>".getKary($bar->updateby)."<br>".($bar->updatetime!='0000-00-00 00:00:00'?$bar->updatetime:"")."</td>
		
		";
		## APPROVAL ##
		$countApp = getCountApproval($jnsapp);
		//for($i=1;$i<=$countApp;$i++){
			$arrdetail = detailApprove($countApp,$bar->kodebarang,$jnsapp);
			echo"<td align=center style=cursor:pointer;color:blue; title='Click untuk melihat detail approval.' onclick=gethistoriapproval('".$bar->kodebarang."','event','".$jnsapp."');>".($arrdetail['status']=='0'?'Menunggu Keputusan':($arrdetail['status']=='3'?'Ditolak':'Disetujui'))."</td>";
		// }
	if(file_exists("images/qrcode/".$bar->kodebarang.".png")){		
		echo"<td align=center><img src='images/qrcode/".$bar->kodebarang. ".png'></td>";
	}else{
		echo"<td align=center></td>";
	}	
	echo"<td align=center nowrap>" . $convertKategori[$bar->idkategorinya] . "</td>
	<td align=center nowrap>" . $optkodevh[$bar->kodevhc] . "</td>
	<td align=center nowrap>".$adx."</td>
		<td align=center nowrap>";
			// if($arrdetail['status']!='0'){
				echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kelompokbarang."','".substr($bar->kodebarang,0,5)."','".changeKutipChar($bar->kodebarang)."','".($bar->namabarang)."','".$bar->satuan."','".$bar->minstok."','".$bar->nokartubin."','".$bar->konversi."','".$bar->inisial."','".$bar->jenis."','".$bar->inactive."','".$bar->ongkir. "','" . $bar->idkategori . "','" . $bar->kodevhc . "');\">&nbsp;";
				echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delBarang('".$bar->kodebarang."','".$bar->kelompokbarang."');\">";
			// }
			echo"&nbsp;<img src=images/addplus.png class=resicon  title='Detail Minimum Stock' onclick=\"minstok('".$bar->kodebarang."','".$bar->namabarang."');\">";
	echo"</td>
		</tr>";
	}

?>
