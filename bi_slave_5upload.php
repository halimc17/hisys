<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses','');
$id = checkPostGet('id','');
$idsvg = checkPostGet('idsvg','');
$file = checkPostGet('file','');
$fileupload = checkPostGet('fileupload','');
$provinsi = checkPostGet('provinsi','');
$tipepeta = checkPostGet('tipepeta','');
$namapeta = checkPostGet('namapeta','');
$kodept = checkPostGet('kodept','');
$typemapshow = checkPostGet('typemapshow','');

$unit = checkPostGet('unit','');
$kdorg = checkPostGet('kdorg','');
$kdkegiatan = checkPostGet('kdkegiatan','');
$tipedokumen = checkPostGet('tipedokumen','');
$tanggal = checkPostGet('tanggal','');
$keterangan = checkPostGet('keterangan','');
$chkDasar = checkPostGet('chkDasar','');

$tipedokumen = checkPostGet('tipedokumen','');
$nodok = checkPostGet('nodok','');
$periode = checkPostGet('periode','');
$nourut = checkPostGet('nourut','');
$nodokcr = checkPostGet('nodokcr','');
$method = checkPostGet('method','');
$namafile = checkPostGet('namafile','');

$fitur = checkPostGet('fitur','');
$warna = checkPostGet('warna','');

$page = checkPostGet('page','0');
	
switch($proses){
	case 'loadalldata':
		echo getLoadData($page)."####".getLoadData2($page)."####".getLoadData3($page);
		break;
		
	case 'loaddata':
		echo getLoadData($page);
		break;
		
	case 'loaddata2':
		echo getLoadData2($page);
		break;
		
	case 'loaddata3':
		echo getLoadData3($page);
		break;
		
	case 'simpan':
		if($tipepeta != "MAP001"){
			if($namapeta == ''){
				exit("warning : Nama Peta harus diisi.");
			}			
		}
		
		if($method == 'insert'){
			if($fileupload == ''){
				exit("warning : File .SVG harus diisi.");
			}else{
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];		
					
					if($filetype=='.svg' or $filetype=='.zip'){
						if($_FILES['file']['size'] <= 250000000){
							// move_uploaded_file($file_tmpname,"filegis/$filename");
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("warning : Format file upload harus .svg atau .zip");
					}
				}
			}
			
			$str = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$tipepeta."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$tipefeature = $bar['tipefeature'];
			
			//inisialisasi variable
			$list = array();
			$listPath = array();
			$lin = 0;
			$linTemp = 0;
			$kdOrg = "";
			$no = 0;
			
			//Extrac and read file .zip
			if($filetype == '.zip'){
				$zip = zip_open($file_tmpname);
				if($zip){
					$count = 0;
					while ($zip_entry = zip_read($zip)) {
						$count++;
						if($count > 1){
							exit("error : Detected multiple file");
						}
						if (zip_entry_open($zip, $zip_entry, "r")) {
							$content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
						}
					}			
				}
				zip_close($zip);
				$fp = fopen('data://text/plain,' . $content,'r');
			}else{
				$fp = fopen($file_tmpname, "r");
			}
			
			while (!feof($fp)) {
				$line = fgets($fp);
				
				//Get ViewBox
				if(mb_ereg("viewBox",$line) AND $line!=''){
					$pieces = explode(' viewBox="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['viewBox'] = $tandaKutip[0];
					}
				}
				
				//Get NoBlok
				if(mb_ereg("NoBlock",$line) AND $line!=''){
					$pieces = explode(' NoBlock="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
					}		
					$no++;
				}
				
				//Get NoBlok
				if(mb_ereg("Nama",$line) AND $line!=''){
					$pieces = explode(' Nama="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
					}		
					$no++;
				}
				
				if($tipefeature == 'path'){
					//Get Path
					if(mb_ereg(' d="',$line) AND $line!=''){
						$pieces = explode(' d="', $line);
						
						foreach($pieces as $content) {
							$listPath[$list['NoBlock'][$lin]] = @$content;
							$linTemp = $lin;
						}
						$lin++;
					}else{
						if(isset($list['NoBlock'][$linTemp])){
							@$listPath[$list['NoBlock'][$linTemp]] .= $line;
						}
					}
				}else{
					//Get Circle
					if(mb_ereg(' cx="',$line) AND mb_ereg(' cy="',$line) AND $line!=''){
						$piecesx = explode(' cx="', $line);
						$piecesy = explode(' cy="', $line);
						
						$tandaKutipx = explode('"', $piecesx[1]);
						$tandaKutipy = explode('"', $piecesy[1]);
						
						$listPath[$list['NoBlock'][$lin]] = $tandaKutipx[0].",".$tandaKutipy[0];
						$linTemp = $lin;
						
						$lin++;
					}
				}
			}
			fclose($fp);
			
			$countloop = 0;
			$maxloop = 5000;
			$arrstr = array();
			$idsvg = getID();
			$plusIdSVG = 0;
			// exit("error : ".count($list['NoBlock']));
			
			foreach($list['NoBlock'] as $row){
				$plusIdSVG++;
				$nextIdSVG = 'SVG'.addZero(intval($idsvg) + $plusIdSVG, 9);
				$expPath = explode('"',$listPath[$row]);
				$listPath[$row] = $expPath[0];
				
				if($tipepeta == 'MAP001'){
					$vNamaPeta = $provinsi;
				}else{
					$vNamaPeta = $namapeta;
				}
				
				$vPath = "";
				if($tipefeature == 'path'){
					$vPath = $expPath[0];
				}else{
					$vPath = $expPath[0];
				}
				$vexp = $row;
				
				if($countloop == 0 or ($countloop % $maxloop) == 0){
					$str = "insert into ".$dbname.".bi_map_basic (idsvg,tipepeta,namapeta,path,viewbox,keterangan,status,updateby) values ";
				}
				
				$str .= "('".$nextIdSVG."','".$tipepeta."','".$vNamaPeta."','".$vPath."','".$list['viewBox']."','".$vexp."','A','".$_SESSION['standard']['userid']."')";
				
				$countloop++;
				
				if(($countloop % $maxloop) == 0 or $countloop == count($list['NoBlock'])){
					$str .= ";";
					
					try{
						$owlPDO->exec($str);
					}catch (PDOException $e){
						echo "error : ".$e->getMessage();
					}
				}else{
					$str .= ",";
				}
			}
		}else{			
			if($fileupload == ''){
				if($tipepeta == 'MAP001'){
					$vNamaPeta = $provinsi;
				}else{
					$vNamaPeta = $namapeta;
				}
				
				$str = "update ".$dbname.".bi_map_basic set tipepeta = '".$tipepeta."', namapeta = '".$vNamaPeta."', updateby = '".$_SESSION['standard']['userid']."' where idsvg = '".$id."'";
								
				try{
					$owlPDO->exec($str);
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
			}else{
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];		
					
					if($filetype=='.svg' or $filetype=='.zip'){
						if($_FILES['file']['size'] <= 250000000){
							// move_uploaded_file($file_tmpname,"filegis/$filename");
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("warning : Format file upload harus .svg");
					}
				}
				
				$str = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$tipepeta."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$tipefeature = $bar['tipefeature'];
				
				//inisialisasi variable
				$list = array();
				$listPath = array();
				$lin = 0;
				$linTemp = 0;
				$kdOrg = "";
				$no = 0;
				
				//Extrac and read file .zip
				if($filetype == '.zip'){
					$zip = zip_open($file_tmpname);
					if($zip){
						$count = 0;
						while ($zip_entry = zip_read($zip)) {
							$count++;
							if($count > 1){
								exit("error : Detected multiple file");
							}
							if (zip_entry_open($zip, $zip_entry, "r")) {
								$content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							}
						}			
					}
					zip_close($zip);
					$fp = fopen('data://text/plain,' . $content,'r');
				}else{
					$fp = fopen($file_tmpname, "r");
				}
				
				while (!feof($fp)) {
					$line = fgets($fp);
					
					//Get ViewBox
					if(mb_ereg("viewBox",$line) AND $line!=''){
						$pieces = explode(' viewBox="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['viewBox'] = $tandaKutip[0];
						}
					}
					
					//Get NoBlok
					if(mb_ereg("NoBlock",$line) AND $line!=''){
						$pieces = explode(' NoBlock="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
						}		
						$no++;
					}
					
					//Get NoBlok
					if(mb_ereg("Nama",$line) AND $line!=''){
						$pieces = explode(' Nama="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
						}		
						$no++;
					}
					
					if($tipefeature == 'path'){
						//Get Path
						if(mb_ereg(' d="',$line) AND $line!=''){
							$pieces = explode(' d="', $line);
							
							foreach($pieces as $content) {
								$listPath[$list['NoBlock'][$lin]] = @$content;
								$linTemp = $lin;
							}
							$lin++;
						}else{
							if(isset($list['NoBlock'][$linTemp])){
								@$listPath[$list['NoBlock'][$linTemp]] .= $line;
							}
						}
					}else{
						//Get Circle
						if(mb_ereg(' cx="',$line) AND mb_ereg(' cy="',$line) AND $line!=''){
							$piecesx = explode(' cx="', $line);
							$piecesy = explode(' cy="', $line);
							
							$tandaKutipx = explode('"', $piecesx[1]);
							$tandaKutipy = explode('"', $piecesy[1]);
							
							$listPath[$list['NoBlock'][$lin]] = $tandaKutipx[0].",".$tandaKutipy[0];
							$linTemp = $lin;
							
							$lin++;
						}
					}
				}
				fclose($fp);
				
				if(count($listPath) > 1 or count($listPath) == 0){
					exit("error : File SVG tidak support (jumlah path lebih dari satu).");
				}
				
				foreach($list['NoBlock'] as $row){
					$expPath = explode('"',$listPath[$row]);
					$listPath[$row] = $expPath[0];
					
					if($tipepeta == 'MAP001'){
						$vNamaPeta = $provinsi;
					}else{
						$vNamaPeta = $namapeta;
					}
					
					$vPath = "";
					if($tipefeature == 'path'){
						$vPath = $expPath[0];
					}else{
						$vPath = $expPath[0];
					}
					$vexp = $row;
					$str = "update ".$dbname.".bi_map_basic set tipepeta = '".$tipepeta."', namapeta = '".$vNamaPeta."', path = '".$vPath."', viewbox = '".$list['viewBox']."', keterangan = '".$vexp."', updateby = '".$_SESSION['standard']['userid']."' where idsvg = '".$id."'";
					
					try{
						$owlPDO->exec($str);
					}catch (PDOException $e){
						echo "error : ".$e->getMessage();
					}
				}
			}
		}	
		break;
		
	case 'checkChkTipe':
		$optTipeDokumen = $optKdOrg = $optKegiatan = $optTipePeta= "";
		// exit("error : ".$chkDasar);
		if($chkDasar == 'false'){
			//Get Tipe Dokumen
			$optTipeDokumen = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str = "select * from ".$dbname.".bi_5tipedok order by nama_tipe asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optTipeDokumen .= "<option value='".$bar['id_tipedok']."'>".$bar['nama_tipe']."</option>";
			}
			
			//Get Kode Organisasi
			$optKdOrg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str = "select * from " . $dbname . ".organisasi where tipe  in('BLOK','AFDELING','KEBUN')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optKdOrg.="<option value='" .$bar['kodeorganisasi'] . "'>".$bar['namaorganisasi']. "</option>";
			}
			
			//Get Kode Kegiatan
			$optKegiatan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str = "select * from " . $dbname . ".setup_kegiatan order by kodekegiatan";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$optKegiatan.="<option value='" .$bar['kodekegiatan'] . "'>".$bar['kodekegiatan']."-".$bar['namakegiatan']. "</option>";
			}
			
			//Get Tipe Peta
			$str = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '1' order by tipe asc, keterangan asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optTipePeta .= "<option value='".$bar['id_tipepeta']."'>".$bar['keterangan']."</option>";
			}
		}else{
			//Get Tipe Peta
			$str = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '0' order by keterangan asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optTipePeta .= "<option value='".$bar['id_tipepeta']."'>".$bar['keterangan']."</option>";
			}
		}
		
		echo $optTipeDokumen."####".$optKdOrg."####".$optKegiatan."####".$optTipePeta;
		break;
		
	case 'getdivnodokumen':
		$tab = "";
		$tab .= "<table>
			<thead>
			<tr align=center>
				<td>".$_SESSION['lang']['nodokumen']."</td>
				<td>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>";
			
		if($nodok != ''){
			$_SESSION['binodok'][$nourut] = $nodok;
		}
		
		$countTemp = 0;
		if(isset($_SESSION['binodok'])){
			$countTemp = count($_SESSION['binodok']);
			for($i = 0;$i <= $countTemp;$i++ ){
				$tab .= "<tr id='trdok_".$i."'>";
					if(isset($_SESSION['binodok'][$i])){
						$tab .= "<td>
							<input id=nodokumen_".$i." class=myinputtext value='".$_SESSION['binodok'][$i]."'>
						</td>
						<td style='text-align:center'>
							<img title=Hapus class=resicon onclick=delnodok(".$i.") src=images/skyblue/delete.png>
						</td>";
					}else{
						$tab .= "<td>
							<input id=nodokumen_".$i." class=myinputtext value='".$_SESSION['binodok'][$i]."'>
							<img id=nodokumen_find onclick=\"z.elSearch('nodokumen',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;'>
						</td>
						<td style='text-align:center'>
							<img title=Tambah class=resicon onclick=addnodok(".$i.") src=images/plus.png>
						</td>";
					}
				$tab .= "</tr>";
			}
		}else{
			$tab .= "<tr id='trdok_0'>
				<td>
					<input id=nodokumen_0 class=myinputtext>
					<img id=nodokumen_find onclick=\"z.elSearch('nodokumen',event)\" class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;'>
				</td>
				<td style='text-align:center'>
					<img id=detail_add_0 title=Tambah class=resicon onclick=addnodok('0') src=images/plus.png>
				</td>
			</tr>";
		}
		
		$tab .= "</table>";
		
		echo $tab;
		break;
		
	case 'delnodok':
		$countTemp = count($_SESSION['binodok']);
		unset($_SESSION['binodok'][$nourut]);
		$no = 0;
		for($i = 0;$i <= $countTemp;$i++ ){
			if($_SESSION['binodok'][$i] != '')	{
				$_SESSION['binodok2'][$no] = $_SESSION['binodok'][$i];
				$no++;
			}
		}
		unset($_SESSION['binodok']);
		$_SESSION['binodok'] = $_SESSION['binodok2'];
		unset($_SESSION['binodok2']);
		break;
		
	case 'showsvg':
		if($typemapshow == 'dasar'){
			$str = "select * from ".$dbname.".bi_map_basic where idsvg = '".$idsvg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			
			$str2 = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$bar['tipepeta']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$tipefeature = $bar2['tipefeature'];
		}else if($typemapshow == 'pt'){
			$str = "select * from ".$dbname.".bi_map_pt where idsvg = '".$idsvg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			
			$str2 = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$bar['tipepeta']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$tipefeature = $bar2['tipefeature'];
		}else{
			$str = "select * from ".$dbname.".bi_map_transaksi where idsvg = '".$idsvg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			
			$str2 = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '".$bar['tipepeta']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2 = $res2->fetch();
			$tipefeature = $bar2['tipefeature'];
			
			$str3 = "select * from ".$dbname.".bi_map_transaksi_dok where idsvg = '".$idsvg."'";
			$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
			$res3->setFetchMode(PDO::FETCH_ASSOC);
			echo"<div>
				<table cellpading=1 cellspacing=1 border=0 class=sortable>
					<thead>
					<tr>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['nodokumen']."</td>
						<td>".$_SESSION['lang']['photo']."</td>
					</tr>
					</thead>
					<tbody>";
					$no=0;
					while($bar3 = $res3->fetch()){
						$str4 = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$bar3['nodok']."'";
						$res4=$owlPDO->query($str4) or die(print " Gagal: ".PDOException::getMessage());
						$res4->setFetchMode(PDO::FETCH_ASSOC);
						$no++;
						echo"<tr class=rowcontent>
							<td style='text-align:right'>".$no."</td>
							<td>".$bar3['nodok']."</td>
							<td>
								<table>";
								while($bar4 = $res4->fetch()){
									echo"<tr>
										<td style='cursor:pointer;' onclick=\"isifile('".$bar4['namafile']."','event');\"><u><font color=blue>".$bar4['namafile']."</font></u></td>
									</tr>";
								}
								echo"</table>
							</td>
						</tr>";
					}
					
					echo"</tbody>
				</table>
			</div>";
		}
		
		echo'<svg id="demo-blok" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="width: 100%; height: 100%; " version="1.1" viewBox="'.$bar['viewbox'].'">';
		echo'<g id=blok>';
		echo'<desc>Layer '.$bar['tipepeta'].'</desc>';
		
		if($tipefeature == 'path'){
			echo "<path id='".$bar['tipepeta']."' d='".$bar['path']."' title='".$bar['keterangan']."' style='fill:#aaaace'/>";
		}else{
			$pieces = explode(',', $bar['path']);
			echo "<circle id='".$bar['tipepeta']."' cx='".$pieces[0]."' cy='".$pieces[1]."' r=0.340055987181  title='".$bar['keterangan']."' style='fill:red'/>";
		}
		
		break;
		
	case'isifile':
		$expNamafile = explode('.',$namafile);
		if($expNamafile[1]=='pdf'){
			echo "<embed src='fileupload/photodok/".$namafile."' width=780px height=370px>";
		}else{
			echo"<img src='fileupload/photodok/".$namafile."'>";
		}
	break;
		
	case 'deldata':
		$str = "delete from ".$dbname.".bi_map_basic where idsvg = '".$idsvg."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'deldata2':
		$str = "delete from ".$dbname.".bi_map_pt where idsvg = '".$idsvg."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'deldata3':
		$str = "delete from ".$dbname.".bi_map_transaksi where idsvg = '".$idsvg."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	//TAB 20
	case 'simpan2':
		if($kodept == ''){
			exit("warning : Perusahaan harus diisi.");
		}	
		if($namapeta == ''){
			exit("warning : Nama Peta harus diisi.");
		}	
		if($unit == ''){
			exit("warning : Kebun harus diisi.");
		}
		
		if($method == 'insert'){			
			if($fileupload == ''){
				exit("warning : File .SVG harus diisi.");
			}else{
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];		
					
					if($filetype=='.svg' or $filetype=='.zip'){
						if($_FILES['file']['size'] <= 250000000){
							// move_uploaded_file($file_tmpname,"filegis/$filename");
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("warning : Format file upload harus .svg atau .zip");
					}
				}
			}
			
			$str = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$tipepeta."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$tipefeature = $bar['tipefeature'];
			
			if($filetype == '.zip'){
				$zip = zip_open($file_tmpname);
				if($zip){
					$count = 0;
					while ($zip_entry = zip_read($zip)) {
						$count++;
						if($count > 1){
							exit("error : Detected multiple file");
						}
						if (zip_entry_open($zip, $zip_entry, "r")) {
							$content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
						}
					}			
				}
				zip_close($zip);
				$fp = fopen('data://text/plain,' . $content,'r');
			}else{
				$fp = fopen($file_tmpname, "r");
			}
			
			$list = array();
			$listPath = array();
			$lin = 0;
			$linTemp = 0;
			$kdOrg = "";
			$no = 0;
			while (!feof($fp)) {
				$line = fgets($fp);
				
				//Get ViewBox
				if(mb_ereg("viewBox",$line) AND $line!=''){
					$pieces = explode(' viewBox="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['viewBox'] = $tandaKutip[0];
					}
				}
				
				//Get NoBlok
				if(mb_ereg("NoBlock",$line) AND $line!=''){
					$pieces = explode(' NoBlock="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
					}		
					$no++;
				}
				
				//Get NoBlok
				if(mb_ereg("Nama",$line) AND $line!=''){
					$pieces = explode(' Nama="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
					}		
					$no++;
				}
				
				if($tipefeature == 'path'){
					//Get Path
					if(mb_ereg(' d="',$line) AND $line!=''){
						$pieces = explode(' d="', $line);
						
						foreach($pieces as $content) {
							$listPath[$list['NoBlock'][$lin]] = @$content;
							$linTemp = $lin;
						}
						$lin++;
					}else{
						if(isset($list['NoBlock'][$linTemp])){
							@$listPath[$list['NoBlock'][$linTemp]] .= $line;
						}
					}
				}else{
					//Get Circle
					if(mb_ereg(' cx="',$line) AND mb_ereg(' cy="',$line) AND $line!=''){
						$piecesx = explode(' cx="', $line);
						$piecesy = explode(' cy="', $line);
						
						$tandaKutipx = explode('"', $piecesx[1]);
						$tandaKutipy = explode('"', $piecesy[1]);
						
						$listPath[$list['NoBlock'][$lin]] = $tandaKutipx[0].",".$tandaKutipy[0];
						$linTemp = $lin;
						
						$lin++;
					}
				}
			}
			fclose($fp);
			
			$countloop = 0;
			$maxloop = 5000;
			$arrstr = array();
			$idsvg = getID2();
			$plusIdSVG = 0;

			if (isset($list['NoBlock'])) {
				foreach($list['NoBlock'] as $row){
					$plusIdSVG++;
					$nextIdSVG = 'SVGPT'.addZero((intval($idsvg) + $plusIdSVG), 9);
					$expPath = explode('"',$listPath[$row]);
					$listPath[$row] = $expPath[0];
					
					$vPath = "";
					if($tipefeature == 'path'){
						$vPath = $expPath[0];
					}else{
						$vPath = $expPath[0];
					}
					$vexp = $row;
					
					if($countloop == 0 or ($countloop % $maxloop) == 0){
						$str = "insert into ".$dbname.".bi_map_pt (idsvg,tipepeta,namapeta,kodeorg,unit,id_provinsi,path,viewbox,keterangan,status,updateby) values ";
					}
					
					$str .= "('".$nextIdSVG."','".$tipepeta."','".$namapeta."','".$kodept."','".$unit."','".$provinsi."','".$vPath."','".$list['viewBox']."','".$vexp."','A','".$_SESSION['standard']['userid']."')";
					
					$countloop++;
					
					if(($countloop % $maxloop) == 0 or $countloop == count($list['NoBlock'])){
						$str .= ";";
						
						try{
							$owlPDO->exec($str);
						}catch (PDOException $e){
							echo "error : ".$e->getMessage();
						}
					}else{
						$str .= ",";
					}
				}
			}
			
		}else{
			if($fileupload == ''){
				$str = "update ".$dbname.".bi_map_pt set tipepeta = '".$tipepeta."', namapeta = '".$namapeta."', kodeorg = '".$kodept."', unit = '".$unit."', id_provinsi = '".$provinsi."', updateby = '".$_SESSION['standard']['userid']."' where idsvg = '".$id."'";
				
				try{
					$owlPDO->exec($str);
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
			}else{
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];		
					
					if($filetype=='.svg' or $filetype=='.zip'){
						if($_FILES['file']['size'] <= 250000000){
							// move_uploaded_file($file_tmpname,"filegis/$filename");
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("warning : Format file upload harus .svg atau .zip");
					}
				}
				
				$str = "select * from ".$dbname.".bi_5tipepeta where id_tipepeta = '".$tipepeta."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$tipefeature = $bar['tipefeature'];
				
				$list = array();
				$listPath = array();
				$lin = 0;
				$linTemp = 0;
				$kdOrg = "";
				$no = 0;
				
				if($filetype == '.zip'){
					$zip = zip_open($file_tmpname);
					if($zip){
						$count = 0;
						while ($zip_entry = zip_read($zip)) {
							$count++;
							if($count > 1){
								exit("error : Detected multiple file");
							}
							if (zip_entry_open($zip, $zip_entry, "r")) {
								$content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							}
						}			
					}
					zip_close($zip);
					$fp = fopen('data://text/plain,' . $content,'r');
				}else{
					$fp = fopen($file_tmpname, "r");
				}			
				
				while (!feof($fp)) {
					$line = fgets($fp);
					
					//Get ViewBox
					if(mb_ereg("viewBox",$line) AND $line!=''){
						$pieces = explode(' viewBox="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['viewBox'] = $tandaKutip[0];
						}
					}
					
					//Get NoBlok
					if(mb_ereg("NoBlock",$line) AND $line!=''){
						$pieces = explode(' NoBlock="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
						}		
						$no++;
					}
					
					//Get NoBlok
					if(mb_ereg("Nama",$line) AND $line!=''){
						$pieces = explode(' Nama="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
						}		
						$no++;
					}
					
					if($tipefeature == 'path'){
						//Get Path
						if(mb_ereg(' d="',$line) AND $line!=''){
							$pieces = explode(' d="', $line);
							
							foreach($pieces as $content) {
								$listPath[$list['NoBlock'][$lin]] = @$content;
								$linTemp = $lin;
							}
							$lin++;
						}else{
							if(isset($list['NoBlock'][$linTemp])){
								@$listPath[$list['NoBlock'][$linTemp]] .= $line;
							}
						}
					}else{
						//Get Circle
						if(mb_ereg(' cx="',$line) AND mb_ereg(' cy="',$line) AND $line!=''){
							$piecesx = explode(' cx="', $line);
							$piecesy = explode(' cy="', $line);
							
							$tandaKutipx = explode('"', $piecesx[1]);
							$tandaKutipy = explode('"', $piecesy[1]);
							
							$listPath[$list['NoBlock'][$lin]] = $tandaKutipx[0].",".$tandaKutipy[0];
							$linTemp = $lin;
							
							$lin++;
						}
					}
				}
				fclose($fp);
								
				if(count($listPath) > 1 or count($listPath) == 0){
					exit("error : File SVG tidak support (jumlah path lebih dari satu).");
				}
				
				foreach($list['NoBlock'] as $row){
					$expPath = explode('"',$listPath[$row]);
					$listPath[$row] = $expPath[0];
					
					$vPath = "";
					if($tipefeature == 'path'){
						$vPath = $expPath[0];
					}else{
						$vPath = $expPath[0];
					}
					$vexp = $row;
					$str = "update ".$dbname.".bi_map_pt set  tipepeta = '".$tipepeta."', namapeta = '".$namapeta."', kodeorg = '".$kodept."', unit = '".$unit."', id_provinsi = '".$provinsi."', path = '".$vPath."', viewbox = '".$list['viewBox']."', keterangan = '".$vexp."', updateby = '".$_SESSION['standard']['userid']."' where idsvg = '".$id."'";
					
					try{
						$owlPDO->exec($str);
					}catch (PDOException $e){
						echo "error : ".$e->getMessage();
					}
				}
			}		
		}
		break;
	
	case 'getkebun2':
		$optKebun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".organisasi where induk = '".$kodept."' and tipe = 'KEBUN'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optKebun .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
		}
		
		echo $optKebun;
		break;
	
	case 'getkebun3':
		$optKebun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".organisasi where induk = '".$kodept."' and tipe = 'KEBUN'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$optKebun .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
		}
		
		echo $optKebun;
		break;
		
	case 'searchnodok':
		$form="<fieldset style=float: left;height:400>
               <legend>".$_SESSION['lang']['find']."</legend>
			   <table>
			   <tr><td>".$_SESSION['lang']['nodokumen']."</td><td><input type=text class=myinputtext id=nodokcr onkeypress='return tanpa_kutip(event)' style='width:150px' /><button class=mybutton onclick=findNodok()>".$_SESSION['lang']['find']."</button></td></tr></table></fieldset>
               <fieldset><legend>".$_SESSION['lang']['result']."</legend><div id=ctner2></div></fieldset>";
        echo $form;
		break;
		
	case 'findNodok':
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
		$tab.="<thead>";
		$tab.="<tr><td>No.</td>";
		$tab.="<td>".$_SESSION['lang']['nodokumen']."</td>";
		$tab.="</tr></thead><tbody>";
		
		$str = "select * from ".$dbname.".bi_5tipedok where id_tipedok = '".$tipedokumen."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$dok = $res->fetch();
		
		$str = "select distinct(".$dok['nodok'].") as nodok from ".$dbname.".".$dok['tabel']." where ".$dok['kodeorg']." like '".$kdorg."%' and ".$dok['jnskgtn']." = '".$kdkegiatan."' and ".$dok['periode']." like '".$periode."%' order by ".$dok['nodok']." asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while($bar = $res->fetch()){
			$no += 1;
			$brt="onclick=\"setDataNodok('".$bar['nodok']."')\"";
			$tab.="<tr ".$brt." class=rowcontent>";
			$tab.="<td>".$no."</td>";
            $tab.="<td>".$bar['nodok']."</td>";
            $tab.="</tr>";
		}
		$tab.="</tbody></table>";
		
		echo $tab;
		break;
		
	case 'addNoDok':
		if($nodok == ''){
			exit("Warning : No Dokumen harus diisi");
		}
		
		$key=array_search($nodok,$_SESSION['nodok']);
		if($key!==false){
			exit("Warning : No. Dokumen sudah diinput sebelumnya");
		}
		
		array_push($_SESSION['nodok'],$nodok);
		
		foreach($_SESSION['nodok'] as $key){
			$result .= "<tr>";
			$result .= "<td>".$key."</d>";
			$result .= "<tr>";
		}
		
		echo $nodok;
		break;
		
	case 'deletenodok':
		$key=array_search($nodok,$_SESSION['nodok']);
		if($key!==false)
		unset($_SESSION['nodok'][$key]);
		break;
		
	case 'getNoDok3':
		if(isset($_SESSION['nodok'])){
			$_SESSION['nodok'] = array();
		}
		
		$optKegiatan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($tipedokumen != ''){
			$str = "select * from ".$dbname.".bi_5tipedok where id_tipedok = '".$tipedokumen."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$dok = $res->fetch();
			
			$str = "select distinct(".$dok['jnskgtn'].") as kegiatan from ".$dbname.".".$dok['tabel']." where ".$dok['kodeorg']." like '".$kdorg."%' and ".$dok['periode']." like '".$periode."%' order by ".$dok['jnskgtn']." asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kegiatan']."'");
				$optKegiatan .= "<option value='".$bar['kegiatan']."'>".$bar['kegiatan']."-".$optNamaKegiatan[$bar['kegiatan']]."</option>";
			}
		}
		echo $optKegiatan;
		break;
		
	case 'simpan3':
		if($method=='insert'){
			if($kdorg == ''){
				exit("warning : Kebun harus dipilih.");
			}
			if($tipedokumen == ''){
				exit("warning : Tipe dokumen harus dipilih.");
			}
			if($kdkegiatan == ''){
				exit("warning : Kegiatan harus dipilih");
			}
			if(count($_SESSION['nodok']) <= 0){
				exit("warning : No. dokumen harus diisi.");
			}
			
			if($warna == ''){
				exit("warning : Warna harus dipilih");
			}
			
			if($fileupload == ''){
				exit("warning : File .SVG harus diisi.");
			}else{
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];		
					
					if($filetype=='.svg'){
						if($_FILES['file']['size'] <= 250000000){
							// move_uploaded_file($file_tmpname,"filegis/$filename");
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("warning : Format file upload harus .svg");
					}
				}
			}
			
			$tipepeta= 2;
			$str = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '".$tipepeta."' and keterangan = '".$tipedokumen."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$tipefeature = $bar['tipefeature'];
			
			$fp = fopen($file_tmpname, "r");
			$list = array();
			$listPath = array();
			$lin = 0;
			$linTemp = 0;
			$kdOrg = "";
			$no = 0;
			while (!feof($fp)) {
				$line = fgets($fp);
				
				//Get ViewBox
				if(mb_ereg("viewBox",$line) AND $line!=''){
					$pieces = explode(' viewBox="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['viewBox'] = $tandaKutip[0];
					}
				}
				
				//Get NoBlok
				if(mb_ereg("NoBlock",$line) AND $line!=''){
					$pieces = explode(' NoBlock="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
					}		
					$no++;
				}
				
				//Get NoBlok
				if(mb_ereg("Nama",$line) AND $line!=''){
					$pieces = explode(' Nama="', $line);
					foreach($pieces as $content) {
						$tandaKutip = explode('"', $content);
						$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
					}		
					$no++;
				}
				
				if($tipefeature == 'path'){
					//Get Path
					if(mb_ereg(' d="',$line) AND $line!=''){
						$pieces = explode(' d="', $line);
						
						foreach($pieces as $content) {
							$listPath[$list['NoBlock'][$lin]] = @$content;
							$linTemp = $lin;
						}
						$lin++;
					}else{
						if(isset($list['NoBlock'][$linTemp])){
							@$listPath[$list['NoBlock'][$linTemp]] .= $line;
						}
					}
				}else{
					//Get Circle
					if(mb_ereg(' cx="',$line) AND mb_ereg(' cy="',$line) AND $line!=''){
						$piecesx = explode(' cx="', $line);
						$piecesy = explode(' cy="', $line);
						
						$tandaKutipx = explode('"', $piecesx[1]);
						$tandaKutipy = explode('"', $piecesy[1]);
						
						$listPath[$list['NoBlock'][$lin]] = $tandaKutipx[0].",".$tandaKutipy[0];
						$linTemp = $lin;
						
						$lin++;
					}
				}
			}
			fclose($fp);
			
			$idsvg = getID3();
			
			foreach($list['NoBlock'] as $row){
				$expPath = explode('"',$listPath[$row]);
				$listPath[$row] = $expPath[0];
				
				$vPath = "";
				if($tipefeature == 'path'){
					$vPath = $expPath[0];
				}else{
					$vPath = $expPath[0];
				}
				$vexp = $row;
				$str = "insert into ".$dbname.".bi_map_transaksi (idsvg,periode,tipepeta,kodeorg,tipedok,kodekegiatan,path,viewbox,fitur,warna,keterangan,keterangansvg,updateby) values ('".$idsvg."','".$periode."','".$tipepeta."','".$kdorg."','".$tipedokumen."','".$kdkegiatan."','".$vPath."','".$list['viewBox']."','".$fitur."','".$warna."','".$keterangan."','".$vexp."','".$_SESSION['standard']['userid']."')";
				
				try{
					$owlPDO->exec($str);
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
			}
			
			foreach($_SESSION['nodok'] as $key){
				$str = "insert into ".$dbname.".bi_map_transaksi_dok (idsvg,nodok) values ('".$idsvg."','".$key."')";
				try{
					$owlPDO->exec($str);
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
			}
		}else{
			if(count($_SESSION['nodok']) <= 0){
				exit("warning : No. dokumen harus diisi.");
			}
			
			if($warna == ''){
				exit("warning : Warna harus dipilih");
			}
			
			if($fileupload == ''){
				$str = "update ".$dbname.".bi_map_transaksi set fitur='".$fitur."',warna='".$warna."',keterangan='".$keterangan."' where idsvg='".$id."'";
				// exit("error : ".$str);
				try{
					$owlPDO->exec($str);

					$str = "delete from ".$dbname.".bi_map_transaksi_dok where idsvg='".$id."'";
					try{
						$owlPDO->exec($str);
						
						foreach($_SESSION['nodok'] as $key){
							$str = "insert into ".$dbname.".bi_map_transaksi_dok (idsvg,nodok) values ('".$id."','".$key."')";
							try{
								$owlPDO->exec($str);
							}catch (PDOException $e){
								echo "error : ".$e->getMessage();
							}
						}
					}catch (PDOException $e){
						echo "error : ".$e->getMessage();
					}
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
			}else{
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];		
					
					if($filetype=='.svg'){
						if($_FILES['file']['size'] <= 250000000){
							// move_uploaded_file($file_tmpname,"filegis/$filename");
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("warning : Format file upload harus .svg");
					}
				}
			
				$tipepeta= 2;
				$str = "select * from ".$dbname.".bi_5tipepeta where tipekelompok = '".$tipepeta."' and keterangan = '".$tipedokumen."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$tipefeature = $bar['tipefeature'];
				
				$fp = fopen($file_tmpname, "r");
				$list = array();
				$listPath = array();
				$lin = 0;
				$linTemp = 0;
				$kdOrg = "";
				$no = 0;
				while (!feof($fp)) {
					$line = fgets($fp);
					
					//Get ViewBox
					if(mb_ereg("viewBox",$line) AND $line!=''){
						$pieces = explode(' viewBox="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['viewBox'] = $tandaKutip[0];
						}
					}
					
					//Get NoBlok
					if(mb_ereg("NoBlock",$line) AND $line!=''){
						$pieces = explode(' NoBlock="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
						}		
						$no++;
					}
					
					//Get NoBlok
					if(mb_ereg("Nama",$line) AND $line!=''){
						$pieces = explode(' Nama="', $line);
						foreach($pieces as $content) {
							$tandaKutip = explode('"', $content);
							$list['NoBlock'][$no] = $tandaKutip[0]."##".$no;
						}		
						$no++;
					}
					
					if($tipefeature == 'path'){
						//Get Path
						if(mb_ereg(' d="',$line) AND $line!=''){
							$pieces = explode(' d="', $line);
							
							foreach($pieces as $content) {
								$listPath[$list['NoBlock'][$lin]] = @$content;
								$linTemp = $lin;
							}
							$lin++;
						}else{
							if(isset($list['NoBlock'][$linTemp])){
								@$listPath[$list['NoBlock'][$linTemp]] .= $line;
							}
						}
					}else{
						//Get Circle
						if(mb_ereg(' cx="',$line) AND mb_ereg(' cy="',$line) AND $line!=''){
							$piecesx = explode(' cx="', $line);
							$piecesy = explode(' cy="', $line);
							
							$tandaKutipx = explode('"', $piecesx[1]);
							$tandaKutipy = explode('"', $piecesy[1]);
							
							$listPath[$list['NoBlock'][$lin]] = $tandaKutipx[0].",".$tandaKutipy[0];
							$linTemp = $lin;
							
							$lin++;
						}
					}
				}
				fclose($fp);
				
				$idsvg = getID3();
				
				foreach($list['NoBlock'] as $row){
					$expPath = explode('"',$listPath[$row]);
					$listPath[$row] = $expPath[0];
					
					$vPath = "";
					if($tipefeature == 'path'){
						$vPath = $expPath[0];
					}else{
						$vPath = $expPath[0];
					}
					$vexp = $row;
					$str = "update ".$dbname.".bi_map_transaksi set fitur='".$fitur."',warna='".$warna."','keterangan='".$keterangan."', path='".$vPath."', viewbox='".$list['viewBox']."', keterangan='".$keterangan."', keterangansvg='".$keterangan."', updateby='".$_SESSION['standard']['userid']."' where idsvg='".$id."'";
					
					try{
						$owlPDO->exec($str);
					}catch (PDOException $e){
						echo "error : ".$e->getMessage();
					}
				}
				
				$str = "delete from ".$dbname.".bi_map_transaksi_dok where idsvg='".$id."'";
				try{
					$owlPDO->exec($str);
					
					foreach($_SESSION['nodok'] as $key){
						$str = "insert into ".$dbname.".bi_map_transaksi_dok (idsvg,nodok) values ('".$id."','".$key."')";
						try{
							$owlPDO->exec($str);
						}catch (PDOException $e){
							echo "error : ".$e->getMessage();
						}
					}
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
			}
		}
		
		break;
		
	case'getListDok':
		$_SESSION['nodok'] = array();
		$str="select * from ".$dbname.".bi_map_transaksi_dok where idsvg='".$id."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			array_push($_SESSION['nodok'],$bar['nodok']);
		}
		
		foreach($_SESSION['nodok'] as $key){
			$result .= "<tr>";
			$result .= "<td>".$key."</d>";
			$result .= "<td style='text-align:center'>
				<img title='Hapus' class=resicon onclick=\"deletenodok(this,'".$key."')\" src='images/delete_32.png'/>
			</td>";
			$result .= "<tr>";
		}
		
		echo $result;
		break;
		
	case'cariwarna':
	echo"<table  style=width:900px  cellpading=0 cellspacing=0 border=0>
			<!--<tr style=height:25px>
				<td colspan=25 onclick=movewarna('','') style='text-align:center;border:solid 1px #000'>None</td>
			</tr>-->
			<tr style=height:25px>
				<td bgcolor='#FBEFEF' style='cursor:pointer;' onclick=movewarna('#FBEFEF','".$jenis."')></td>
				<td bgcolor='#FBF2EF' style='cursor:pointer;' onclick=movewarna('#FBF2EF','".$jenis."')></td>
				<td bgcolor='#FBF5EF' style='cursor:pointer;' onclick=movewarna('#FBF5EF','".$jenis."')></td>
				<td bgcolor='#FBF8EF' style='cursor:pointer;' onclick=movewarna('#FBF8EF','".$jenis."')></td>
				<td bgcolor='#FBFBEF' style='cursor:pointer;' onclick=movewarna('#FBFBEF','".$jenis."')></td>
				<td bgcolor='#F8FBEF' style='cursor:pointer;' onclick=movewarna('#F8FBEF','".$jenis."')></td>
				<td bgcolor='#F5FBEF' style='cursor:pointer;' onclick=movewarna('#F5FBEF','".$jenis."')></td>
				<td bgcolor='#F2FBEF' style='cursor:pointer;' onclick=movewarna('#F2FBEF','".$jenis."')></td>
				<td bgcolor='#EFFBEF' style='cursor:pointer;' onclick=movewarna('#EFFBEF','".$jenis."')></td>
				<td bgcolor='#EFFBF2' style='cursor:pointer;' onclick=movewarna('#EFFBF2','".$jenis."')></td>
				<td bgcolor='#EFFBF5' style='cursor:pointer;' onclick=movewarna('#EFFBF5','".$jenis."')></td>
				<td bgcolor='#EFFBF8' style='cursor:pointer;' onclick=movewarna('#EFFBF8','".$jenis."')></td>
				<td bgcolor='#EFFBFB' style='cursor:pointer;' onclick=movewarna('#EFFBFB','".$jenis."')></td>
				<td bgcolor='#EFF8FB' style='cursor:pointer;' onclick=movewarna('#EFF8FB','".$jenis."')></td>
				<td bgcolor='#EFF5FB' style='cursor:pointer;' onclick=movewarna('#EFF5FB','".$jenis."')></td>
				<td bgcolor='#EFF2FB' style='cursor:pointer;' onclick=movewarna('#EFF2FB','".$jenis."')></td>
				<td bgcolor='#EFEFFB' style='cursor:pointer;' onclick=movewarna('#EFEFFB','".$jenis."')></td>
				<td bgcolor='#F2EFFB' style='cursor:pointer;' onclick=movewarna('#F2EFFB','".$jenis."')></td>
				<td bgcolor='#F5EFFB' style='cursor:pointer;' onclick=movewarna('#F5EFFB','".$jenis."')></td>
				<td bgcolor='#F8EFFB' style='cursor:pointer;' onclick=movewarna('#F8EFFB','".$jenis."')></td>
				<td bgcolor='#FBEFFB' style='cursor:pointer;' onclick=movewarna('#FBEFFB','".$jenis."')></td>
				<td bgcolor='#FBEFF8' style='cursor:pointer;' onclick=movewarna('#FBEFF8','".$jenis."')></td>
				<td bgcolor='#FBEFF5' style='cursor:pointer;' onclick=movewarna('#FBEFF5','".$jenis."')></td>
				<td bgcolor='#FBEFF2' style='cursor:pointer;' onclick=movewarna('#FBEFF2','".$jenis."')></td>
				<td bgcolor='#FFFFFF' style='cursor:pointer;' onclick=movewarna('#FFFFFF','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#F8E0E0' style='cursor:pointer;' onclick=movewarna('#F8E0E0','".$jenis."')></td>
				<td bgcolor='#F8E6E0' style='cursor:pointer;' onclick=movewarna('#F8E6E0','".$jenis."')></td>
				<td bgcolor='#F8ECE0' style='cursor:pointer;' onclick=movewarna('#F8ECE0','".$jenis."')></td>
				<td bgcolor='#F7F2E0' style='cursor:pointer;' onclick=movewarna('#F7F2E0','".$jenis."')></td>
				<td bgcolor='#F7F8E0' style='cursor:pointer;' onclick=movewarna('#F7F8E0','".$jenis."')></td>
				<td bgcolor='#F1F8E0' style='cursor:pointer;' onclick=movewarna('#F1F8E0','".$jenis."')></td>
				<td bgcolor='#ECF8E0' style='cursor:pointer;' onclick=movewarna('#ECF8E0','".$jenis."')></td>
				<td bgcolor='#E6F8E0' style='cursor:pointer;' onclick=movewarna('#E6F8E0','".$jenis."')></td>
				<td bgcolor='#E0F8E0' style='cursor:pointer;' onclick=movewarna('#E0F8E0','".$jenis."')></td>
				<td bgcolor='#E0F8E6' style='cursor:pointer;' onclick=movewarna('#E0F8E6','".$jenis."')></td>
				<td bgcolor='#E0F8EC' style='cursor:pointer;' onclick=movewarna('#E0F8EC','".$jenis."')></td>
				<td bgcolor='#E0F8F1' style='cursor:pointer;' onclick=movewarna('#E0F8F1','".$jenis."')></td>
				<td bgcolor='#E0F8F7' style='cursor:pointer;' onclick=movewarna('#E0F8F7','".$jenis."')></td>
				<td bgcolor='#E0F2F7' style='cursor:pointer;' onclick=movewarna('#E0F2F7','".$jenis."')></td>
				<td bgcolor='#E0ECF8' style='cursor:pointer;' onclick=movewarna('#E0ECF8','".$jenis."')></td>
				<td bgcolor='#E0E6F8' style='cursor:pointer;' onclick=movewarna('#E0E6F8','".$jenis."')></td>
				<td bgcolor='#E0E0F8' style='cursor:pointer;' onclick=movewarna('#E0E0F8','".$jenis."')></td>
				<td bgcolor='#E6E0F8' style='cursor:pointer;' onclick=movewarna('#E6E0F8','".$jenis."')></td>
				<td bgcolor='#ECE0F8' style='cursor:pointer;' onclick=movewarna('#ECE0F8','".$jenis."')></td>
				<td bgcolor='#F2E0F7' style='cursor:pointer;' onclick=movewarna('#F2E0F7','".$jenis."')></td>
				<td bgcolor='#F8E0F7' style='cursor:pointer;' onclick=movewarna('#F8E0F7','".$jenis."')></td>
				<td bgcolor='#F8E0F1' style='cursor:pointer;' onclick=movewarna('#F8E0F1','".$jenis."')></td>
				<td bgcolor='#F8E0EC' style='cursor:pointer;' onclick=movewarna('#F8E0EC','".$jenis."')></td>
				<td bgcolor='#F8E0E6' style='cursor:pointer;' onclick=movewarna('#F8E0E6','".$jenis."')></td>
				<td bgcolor='#FAFAFA' style='cursor:pointer;' onclick=movewarna('#FAFAFA','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#F6CECE' style='cursor:pointer;' onclick=movewarna('#F6CECE','".$jenis."')></td>
				<td bgcolor='#F6D8CE' style='cursor:pointer;' onclick=movewarna('#F6D8CE','".$jenis."')></td>
				<td bgcolor='#F6E3CE' style='cursor:pointer;' onclick=movewarna('#F6E3CE','".$jenis."')></td>
				<td bgcolor='#F5ECCE' style='cursor:pointer;' onclick=movewarna('#F5ECCE','".$jenis."')></td>
				<td bgcolor='#F5F6CE' style='cursor:pointer;' onclick=movewarna('#F5F6CE','".$jenis."')></td>
				<td bgcolor='#ECF6CE' style='cursor:pointer;' onclick=movewarna('#ECF6CE','".$jenis."')></td>
				<td bgcolor='#E3F6CE' style='cursor:pointer;' onclick=movewarna('#E3F6CE','".$jenis."')></td>
				<td bgcolor='#D8F6CE' style='cursor:pointer;' onclick=movewarna('#D8F6CE','".$jenis."')></td>
				<td bgcolor='#CEF6CE' style='cursor:pointer;' onclick=movewarna('#CEF6CE','".$jenis."')></td>
				<td bgcolor='#CEF6D8' style='cursor:pointer;' onclick=movewarna('#CEF6D8','".$jenis."')></td>
				<td bgcolor='#CEF6E3' style='cursor:pointer;' onclick=movewarna('#CEF6E3','".$jenis."')></td>
				<td bgcolor='#CEF6EC' style='cursor:pointer;' onclick=movewarna('#CEF6EC','".$jenis."')></td>
				<td bgcolor='#CEF6F5' style='cursor:pointer;' onclick=movewarna('#CEF6F5','".$jenis."')></td>
				<td bgcolor='#CEECF5' style='cursor:pointer;' onclick=movewarna('#CEECF5','".$jenis."')></td>
				<td bgcolor='#CEE3F6' style='cursor:pointer;' onclick=movewarna('#CEE3F6','".$jenis."')></td>
				<td bgcolor='#CED8F6' style='cursor:pointer;' onclick=movewarna('#CED8F6','".$jenis."')></td>
				<td bgcolor='#CECEF6' style='cursor:pointer;' onclick=movewarna('#CECEF6','".$jenis."')></td>
				<td bgcolor='#D8CEF6' style='cursor:pointer;' onclick=movewarna('#D8CEF6','".$jenis."')></td>
				<td bgcolor='#E3CEF6' style='cursor:pointer;' onclick=movewarna('#E3CEF6','".$jenis."')></td>
				<td bgcolor='#ECCEF5' style='cursor:pointer;' onclick=movewarna('#ECCEF5','".$jenis."')></td>
				<td bgcolor='#F6CEF5' style='cursor:pointer;' onclick=movewarna('#F6CEF5','".$jenis."')></td>
				<td bgcolor='#F6CEEC' style='cursor:pointer;' onclick=movewarna('#F6CEEC','".$jenis."')></td>
				<td bgcolor='#F6CEE3' style='cursor:pointer;' onclick=movewarna('#F6CEE3','".$jenis."')></td>
				<td bgcolor='#F6CED8' style='cursor:pointer;' onclick=movewarna('#F6CED8','".$jenis."')></td>
				<td bgcolor='#F2F2F2' style='cursor:pointer;' onclick=movewarna('#F2F2F2','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#F5A9A9' style='cursor:pointer;' onclick=movewarna('#F5A9A9','".$jenis."')></td>
				<td bgcolor='#F5BCA9' style='cursor:pointer;' onclick=movewarna('#F5BCA9','".$jenis."')></td>
				<td bgcolor='#F5D0A9' style='cursor:pointer;' onclick=movewarna('#F5D0A9','".$jenis."')></td>
				<td bgcolor='#F3E2A9' style='cursor:pointer;' onclick=movewarna('#F3E2A9','".$jenis."')></td>
				<td bgcolor='#F2F5A9' style='cursor:pointer;' onclick=movewarna('#F2F5A9','".$jenis."')></td>
				<td bgcolor='#E1F5A9' style='cursor:pointer;' onclick=movewarna('#E1F5A9','".$jenis."')></td>
				<td bgcolor='#D0F5A9' style='cursor:pointer;' onclick=movewarna('#D0F5A9','".$jenis."')></td>
				<td bgcolor='#BCF5A9' style='cursor:pointer;' onclick=movewarna('#BCF5A9','".$jenis."')></td>
				<td bgcolor='#A9F5A9' style='cursor:pointer;' onclick=movewarna('#A9F5A9','".$jenis."')></td>
				<td bgcolor='#A9F5BC' style='cursor:pointer;' onclick=movewarna('#A9F5BC','".$jenis."')></td>
				<td bgcolor='#A9F5D0' style='cursor:pointer;' onclick=movewarna('#A9F5D0','".$jenis."')></td>
				<td bgcolor='#A9F5E1' style='cursor:pointer;' onclick=movewarna('#A9F5E1','".$jenis."')></td>
				<td bgcolor='#A9F5F2' style='cursor:pointer;' onclick=movewarna('#A9F5F2','".$jenis."')></td>
				<td bgcolor='#A9E2F3' style='cursor:pointer;' onclick=movewarna('#A9E2F3','".$jenis."')></td>
				<td bgcolor='#A9D0F5' style='cursor:pointer;' onclick=movewarna('#A9D0F5','".$jenis."')></td>
				<td bgcolor='#A9BCF5' style='cursor:pointer;' onclick=movewarna('#A9BCF5','".$jenis."')></td>
				<td bgcolor='#A9A9F5' style='cursor:pointer;' onclick=movewarna('#A9A9F5','".$jenis."')></td>
				<td bgcolor='#BCA9F5' style='cursor:pointer;' onclick=movewarna('#BCA9F5','".$jenis."')></td>
				<td bgcolor='#D0A9F5' style='cursor:pointer;' onclick=movewarna('#D0A9F5','".$jenis."')></td>
				<td bgcolor='#E2A9F3' style='cursor:pointer;' onclick=movewarna('#E2A9F3','".$jenis."')></td>
				<td bgcolor='#F5A9F2' style='cursor:pointer;' onclick=movewarna('#F5A9F2','".$jenis."')></td>
				<td bgcolor='#F5A9E1' style='cursor:pointer;' onclick=movewarna('#F5A9E1','".$jenis."')></td>
				<td bgcolor='#F5A9D0' style='cursor:pointer;' onclick=movewarna('#F5A9D0','".$jenis."')></td>
				<td bgcolor='#F5A9BC' style='cursor:pointer;' onclick=movewarna('#F5A9BC','".$jenis."')></td>
				<td bgcolor='#E6E6E6' style='cursor:pointer;' onclick=movewarna('#E6E6E6','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#F78181' style='cursor:pointer;' onclick=movewarna('#F78181','".$jenis."')></td>
				<td bgcolor='#F79F81' style='cursor:pointer;' onclick=movewarna('#F79F81','".$jenis."')></td>
				<td bgcolor='#F7BE81' style='cursor:pointer;' onclick=movewarna('#F7BE81','".$jenis."')></td>
				<td bgcolor='#F5DA81' style='cursor:pointer;' onclick=movewarna('#F5DA81','".$jenis."')></td>
				<td bgcolor='#F3F781' style='cursor:pointer;' onclick=movewarna('#F3F781','".$jenis."')></td>
				<td bgcolor='#D8F781' style='cursor:pointer;' onclick=movewarna('#D8F781','".$jenis."')></td>
				<td bgcolor='#BEF781' style='cursor:pointer;' onclick=movewarna('#BEF781','".$jenis."')></td>
				<td bgcolor='#9FF781' style='cursor:pointer;' onclick=movewarna('#9FF781','".$jenis."')></td>
				<td bgcolor='#81F781' style='cursor:pointer;' onclick=movewarna('#81F781','".$jenis."')></td>
				<td bgcolor='#81F79F' style='cursor:pointer;' onclick=movewarna('#81F79F','".$jenis."')></td>
				<td bgcolor='#81F7BE' style='cursor:pointer;' onclick=movewarna('#81F7BE','".$jenis."')></td>
				<td bgcolor='#81F7D8' style='cursor:pointer;' onclick=movewarna('#81F7D8','".$jenis."')></td>
				<td bgcolor='#81F7F3' style='cursor:pointer;' onclick=movewarna('#81F7F3','".$jenis."')></td>
				<td bgcolor='#81DAF5' style='cursor:pointer;' onclick=movewarna('#81DAF5','".$jenis."')></td>
				<td bgcolor='#81BEF7' style='cursor:pointer;' onclick=movewarna('#81BEF7','".$jenis."')></td>
				<td bgcolor='#819FF7' style='cursor:pointer;' onclick=movewarna('#819FF7','".$jenis."')></td>
				<td bgcolor='#8181F7' style='cursor:pointer;' onclick=movewarna('#8181F7','".$jenis."')></td>
				<td bgcolor='#9F81F7' style='cursor:pointer;' onclick=movewarna('#9F81F7','".$jenis."')></td>
				<td bgcolor='#BE81F7' style='cursor:pointer;' onclick=movewarna('#BE81F7','".$jenis."')></td>
				<td bgcolor='#DA81F5' style='cursor:pointer;' onclick=movewarna('#DA81F5','".$jenis."')></td>
				<td bgcolor='#F781F3' style='cursor:pointer;' onclick=movewarna('#F781F3','".$jenis."')></td>
				<td bgcolor='#F781D8' style='cursor:pointer;' onclick=movewarna('#F781D8','".$jenis."')></td>
				<td bgcolor='#F781BE' style='cursor:pointer;' onclick=movewarna('#F781BE','".$jenis."')></td>
				<td bgcolor='#F7819F' style='cursor:pointer;' onclick=movewarna('#F7819F','".$jenis."')></td>
				<td bgcolor='#D8D8D8' style='cursor:pointer;' onclick=movewarna('#D8D8D8','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#FA5858' style='cursor:pointer;' onclick=movewarna('#FA5858','".$jenis."')></td>
				<td bgcolor='#FA8258' style='cursor:pointer;' onclick=movewarna('#FA8258','".$jenis."')></td>
				<td bgcolor='#FAAC58' style='cursor:pointer;' onclick=movewarna('#FAAC58','".$jenis."')></td>
				<td bgcolor='#F7D358' style='cursor:pointer;' onclick=movewarna('#F7D358','".$jenis."')></td>
				<td bgcolor='#F4FA58' style='cursor:pointer;' onclick=movewarna('#F4FA58','".$jenis."')></td>
				<td bgcolor='#D0FA58' style='cursor:pointer;' onclick=movewarna('#D0FA58','".$jenis."')></td>
				<td bgcolor='#ACFA58' style='cursor:pointer;' onclick=movewarna('#ACFA58','".$jenis."')></td>
				<td bgcolor='#82FA58' style='cursor:pointer;' onclick=movewarna('#82FA58','".$jenis."')></td>
				<td bgcolor='#58FA58' style='cursor:pointer;' onclick=movewarna('#58FA58','".$jenis."')></td>
				<td bgcolor='#58FA82' style='cursor:pointer;' onclick=movewarna('#58FA82','".$jenis."')></td>
				<td bgcolor='#58FAAC' style='cursor:pointer;' onclick=movewarna('#58FAAC','".$jenis."')></td>
				<td bgcolor='#58FAD0' style='cursor:pointer;' onclick=movewarna('#58FAD0','".$jenis."')></td>
				<td bgcolor='#58FAF4' style='cursor:pointer;' onclick=movewarna('#58FAF4','".$jenis."')></td>
				<td bgcolor='#58D3F7' style='cursor:pointer;' onclick=movewarna('#58D3F7','".$jenis."')></td>
				<td bgcolor='#58ACFA' style='cursor:pointer;' onclick=movewarna('#58ACFA','".$jenis."')></td>
				<td bgcolor='#5882FA' style='cursor:pointer;' onclick=movewarna('#5882FA','".$jenis."')></td>
				<td bgcolor='#5858FA' style='cursor:pointer;' onclick=movewarna('#5858FA','".$jenis."')></td>
				<td bgcolor='#8258FA' style='cursor:pointer;' onclick=movewarna('#8258FA','".$jenis."')></td>
				<td bgcolor='#AC58FA' style='cursor:pointer;' onclick=movewarna('#AC58FA','".$jenis."')></td>
				<td bgcolor='#D358F7' style='cursor:pointer;' onclick=movewarna('#D358F7','".$jenis."')></td>
				<td bgcolor='#FA58F4' style='cursor:pointer;' onclick=movewarna('#FA58F4','".$jenis."')></td>
				<td bgcolor='#FA58D0' style='cursor:pointer;' onclick=movewarna('#FA58D0','".$jenis."')></td>
				<td bgcolor='#FA58AC' style='cursor:pointer;' onclick=movewarna('#FA58AC','".$jenis."')></td>
				<td bgcolor='#FA5882' style='cursor:pointer;' onclick=movewarna('#FA5882','".$jenis."')></td>
				<td bgcolor='#BDBDBD' style='cursor:pointer;' onclick=movewarna('#BDBDBD','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#FE2E2E' style='cursor:pointer;' onclick=movewarna('#FE2E2E','".$jenis."')></td>
				<td bgcolor='#FE642E' style='cursor:pointer;' onclick=movewarna('#FE642E','".$jenis."')></td>
				<td bgcolor='#FE9A2E' style='cursor:pointer;' onclick=movewarna('#FE9A2E','".$jenis."')></td>
				<td bgcolor='#FACC2E' style='cursor:pointer;' onclick=movewarna('#FACC2E','".$jenis."')></td>
				<td bgcolor='#F7FE2E' style='cursor:pointer;' onclick=movewarna('#F7FE2E','".$jenis."')></td>
				<td bgcolor='#C8FE2E' style='cursor:pointer;' onclick=movewarna('#C8FE2E','".$jenis."')></td>
				<td bgcolor='#9AFE2E' style='cursor:pointer;' onclick=movewarna('#9AFE2E','".$jenis."')></td>
				<td bgcolor='#64FE2E' style='cursor:pointer;' onclick=movewarna('#64FE2E','".$jenis."')></td>
				<td bgcolor='#2EFE2E' style='cursor:pointer;' onclick=movewarna('#2EFE2E','".$jenis."')></td>
				<td bgcolor='#2EFE64' style='cursor:pointer;' onclick=movewarna('#2EFE64','".$jenis."')></td>
				<td bgcolor='#2EFE9A' style='cursor:pointer;' onclick=movewarna('#2EFE9A','".$jenis."')></td>
				<td bgcolor='#2EFEC8' style='cursor:pointer;' onclick=movewarna('#2EFEC8','".$jenis."')></td>
				<td bgcolor='#2EFEF7' style='cursor:pointer;' onclick=movewarna('#2EFEF7','".$jenis."')></td>
				<td bgcolor='#2ECCFA' style='cursor:pointer;' onclick=movewarna('#2ECCFA','".$jenis."')></td>
				<td bgcolor='#2E9AFE' style='cursor:pointer;' onclick=movewarna('#2E9AFE','".$jenis."')></td>
				<td bgcolor='#2E64FE' style='cursor:pointer;' onclick=movewarna('#2E64FE','".$jenis."')></td>
				<td bgcolor='#2E2EFE' style='cursor:pointer;' onclick=movewarna('#2E2EFE','".$jenis."')></td>
				<td bgcolor='#642EFE' style='cursor:pointer;' onclick=movewarna('#642EFE','".$jenis."')></td>
				<td bgcolor='#9A2EFE' style='cursor:pointer;' onclick=movewarna('#9A2EFE','".$jenis."')></td>
				<td bgcolor='#CC2EFA' style='cursor:pointer;' onclick=movewarna('#CC2EFA','".$jenis."')></td>
				<td bgcolor='#FE2EF7' style='cursor:pointer;' onclick=movewarna('#FE2EF7','".$jenis."')></td>
				<td bgcolor='#FE2EC8' style='cursor:pointer;' onclick=movewarna('#FE2EC8','".$jenis."')></td>
				<td bgcolor='#FE2E9A' style='cursor:pointer;' onclick=movewarna('#FE2E9A','".$jenis."')></td>
				<td bgcolor='#FE2E64' style='cursor:pointer;' onclick=movewarna('#FE2E64','".$jenis."')></td>
				<td bgcolor='#A4A4A4' style='cursor:pointer;' onclick=movewarna('#A4A4A4','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#FF0000' style='cursor:pointer;' onclick=movewarna('#FF0000','".$jenis."')></td>
				<td bgcolor='#FF4000' style='cursor:pointer;' onclick=movewarna('#FF4000','".$jenis."')></td>
				<td bgcolor='#FF8000' style='cursor:pointer;' onclick=movewarna('#FF8000','".$jenis."')></td>
				<td bgcolor='#FFBF00' style='cursor:pointer;' onclick=movewarna('#FFBF00','".$jenis."')></td>
				<td bgcolor='#FFFF00' style='cursor:pointer;' onclick=movewarna('#FFFF00','".$jenis."')></td>
				<td bgcolor='#BFFF00' style='cursor:pointer;' onclick=movewarna('#BFFF00','".$jenis."')></td>
				<td bgcolor='#80FF00' style='cursor:pointer;' onclick=movewarna('#80FF00','".$jenis."')></td>
				<td bgcolor='#40FF00' style='cursor:pointer;' onclick=movewarna('#40FF00','".$jenis."')></td>
				<td bgcolor='#00FF00' style='cursor:pointer;' onclick=movewarna('#00FF00','".$jenis."')></td>
				<td bgcolor='#00FF40' style='cursor:pointer;' onclick=movewarna('#00FF40','".$jenis."')></td>
				<td bgcolor='#00FF80' style='cursor:pointer;' onclick=movewarna('#00FF80','".$jenis."')></td>
				<td bgcolor='#00FFBF' style='cursor:pointer;' onclick=movewarna('#00FFBF','".$jenis."')></td>
				<td bgcolor='#00FFFF' style='cursor:pointer;' onclick=movewarna('#00FFFF','".$jenis."')></td>
				<td bgcolor='#00BFFF' style='cursor:pointer;' onclick=movewarna('#00BFFF','".$jenis."')></td>
				<td bgcolor='#0080FF' style='cursor:pointer;' onclick=movewarna('#0080FF','".$jenis."')></td>
				<td bgcolor='#0040FF' style='cursor:pointer;' onclick=movewarna('#0040FF','".$jenis."')></td>
				<td bgcolor='#0000FF' style='cursor:pointer;' onclick=movewarna('#0000FF','".$jenis."')></td>
				<td bgcolor='#4000FF' style='cursor:pointer;' onclick=movewarna('#4000FF','".$jenis."')></td>
				<td bgcolor='#8000FF' style='cursor:pointer;' onclick=movewarna('#8000FF','".$jenis."')></td>
				<td bgcolor='#BF00FF' style='cursor:pointer;' onclick=movewarna('#BF00FF','".$jenis."')></td>
				<td bgcolor='#FF00FF' style='cursor:pointer;' onclick=movewarna('#FF00FF','".$jenis."')></td>
				<td bgcolor='#FF00BF' style='cursor:pointer;' onclick=movewarna('#FF00BF','".$jenis."')></td>
				<td bgcolor='#FF0080' style='cursor:pointer;' onclick=movewarna('#FF0080','".$jenis."')></td>
				<td bgcolor='#FF0040' style='cursor:pointer;' onclick=movewarna('#FF0040','".$jenis."')></td>
				<td bgcolor='#848484' style='cursor:pointer;' onclick=movewarna('#848484','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#DF0101' style='cursor:pointer;' onclick=movewarna('#DF0101','".$jenis."')></td>
				<td bgcolor='#DF3A01' style='cursor:pointer;' onclick=movewarna('#DF3A01','".$jenis."')></td>
				<td bgcolor='#DF7401' style='cursor:pointer;' onclick=movewarna('#DF7401','".$jenis."')></td>
				<td bgcolor='#DBA901' style='cursor:pointer;' onclick=movewarna('#DBA901','".$jenis."')></td>
				<td bgcolor='#D7DF01' style='cursor:pointer;' onclick=movewarna('#D7DF01','".$jenis."')></td>
				<td bgcolor='#A5DF00' style='cursor:pointer;' onclick=movewarna('#A5DF00','".$jenis."')></td>
				<td bgcolor='#74DF00' style='cursor:pointer;' onclick=movewarna('#74DF00','".$jenis."')></td>
				<td bgcolor='#3ADF00' style='cursor:pointer;' onclick=movewarna('#3ADF00','".$jenis."')></td>
				<td bgcolor='#01DF01' style='cursor:pointer;' onclick=movewarna('#01DF01','".$jenis."')></td>
				<td bgcolor='#01DF3A' style='cursor:pointer;' onclick=movewarna('#01DF3A','".$jenis."')></td>
				<td bgcolor='#01DF74' style='cursor:pointer;' onclick=movewarna('#01DF74','".$jenis."')></td>
				<td bgcolor='#01DFA5' style='cursor:pointer;' onclick=movewarna('#01DFA5','".$jenis."')></td>
				<td bgcolor='#01DFD7' style='cursor:pointer;' onclick=movewarna('#01DFD7','".$jenis."')></td>
				<td bgcolor='#01A9DB' style='cursor:pointer;' onclick=movewarna('#01A9DB','".$jenis."')></td>
				<td bgcolor='#0174DF' style='cursor:pointer;' onclick=movewarna('#0174DF','".$jenis."')></td>
				<td bgcolor='#013ADF' style='cursor:pointer;' onclick=movewarna('#013ADF','".$jenis."')></td>
				<td bgcolor='#0101DF' style='cursor:pointer;' onclick=movewarna('#0101DF','".$jenis."')></td>
				<td bgcolor='#3A01DF' style='cursor:pointer;' onclick=movewarna('#3A01DF','".$jenis."')></td>
				<td bgcolor='#7401DF' style='cursor:pointer;' onclick=movewarna('#7401DF','".$jenis."')></td>
				<td bgcolor='#A901DB' style='cursor:pointer;' onclick=movewarna('#A901DB','".$jenis."')></td>
				<td bgcolor='#DF01D7' style='cursor:pointer;' onclick=movewarna('#DF01D7','".$jenis."')></td>
				<td bgcolor='#DF01A5' style='cursor:pointer;' onclick=movewarna('#DF01A5','".$jenis."')></td>
				<td bgcolor='#DF0174' style='cursor:pointer;' onclick=movewarna('#DF0174','".$jenis."')></td>
				<td bgcolor='#DF013A' style='cursor:pointer;' onclick=movewarna('#DF013A','".$jenis."')></td>
				<td bgcolor='#6E6E6E' style='cursor:pointer;' onclick=movewarna('#6E6E6E','".$jenis."')></td>
		</tr>
			 <tr style=height:25px>
				<td bgcolor='#B40404' style='cursor:pointer;' onclick=movewarna('#B40404','".$jenis."')></td>
				<td bgcolor='#B43104' style='cursor:pointer;' onclick=movewarna('#B43104','".$jenis."')></td>
				<td bgcolor='#B45F04' style='cursor:pointer;' onclick=movewarna('#B45F04','".$jenis."')></td>
				<td bgcolor='#B18904' style='cursor:pointer;' onclick=movewarna('#B18904','".$jenis."')></td>
				<td bgcolor='#AEB404' style='cursor:pointer;' onclick=movewarna('#AEB404','".$jenis."')></td>
				<td bgcolor='#86B404' style='cursor:pointer;' onclick=movewarna('#86B404','".$jenis."')></td>
				<td bgcolor='#5FB404' style='cursor:pointer;' onclick=movewarna('#5FB404','".$jenis."')></td>
				<td bgcolor='#31B404' style='cursor:pointer;' onclick=movewarna('#31B404','".$jenis."')></td>
				<td bgcolor='#04B404' style='cursor:pointer;' onclick=movewarna('#04B404','".$jenis."')></td>
				<td bgcolor='#04B431' style='cursor:pointer;' onclick=movewarna('#04B431','".$jenis."')></td>
				<td bgcolor='#04B45F' style='cursor:pointer;' onclick=movewarna('#04B45F','".$jenis."')></td>
				<td bgcolor='#04B486' style='cursor:pointer;' onclick=movewarna('#04B486','".$jenis."')></td>
				<td bgcolor='#04B4AE' style='cursor:pointer;' onclick=movewarna('#04B4AE','".$jenis."')></td>
				<td bgcolor='#0489B1' style='cursor:pointer;' onclick=movewarna('#0489B1','".$jenis."')></td>
				<td bgcolor='#045FB4' style='cursor:pointer;' onclick=movewarna('#045FB4','".$jenis."')></td>
				<td bgcolor='#0431B4' style='cursor:pointer;' onclick=movewarna('#0431B4','".$jenis."')></td>
				<td bgcolor='#0404B4' style='cursor:pointer;' onclick=movewarna('#0404B4','".$jenis."')></td>
				<td bgcolor='#3104B4' style='cursor:pointer;' onclick=movewarna('#3104B4','".$jenis."')></td>
				<td bgcolor='#5F04B4' style='cursor:pointer;' onclick=movewarna('#5F04B4','".$jenis."')></td>
				<td bgcolor='#8904B1' style='cursor:pointer;' onclick=movewarna('#8904B1','".$jenis."')></td>
				<td bgcolor='#B404AE' style='cursor:pointer;' onclick=movewarna('#B404AE','".$jenis."')></td>
				<td bgcolor='#B40486' style='cursor:pointer;' onclick=movewarna('#B40486','".$jenis."')></td>
				<td bgcolor='#B4045F' style='cursor:pointer;' onclick=movewarna('#B4045F','".$jenis."')></td>
				<td bgcolor='#B40431' style='cursor:pointer;' onclick=movewarna('#B40431','".$jenis."')></td>
				<td bgcolor='#585858' style='cursor:pointer;' onclick=movewarna('#585858','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#8A0808' style='cursor:pointer;' onclick=movewarna('#8A0808','".$jenis."')></td>
				<td bgcolor='#8A2908' style='cursor:pointer;' onclick=movewarna('#8A2908','".$jenis."')></td>
				<td bgcolor='#8A4B08' style='cursor:pointer;' onclick=movewarna('#8A4B08','".$jenis."')></td>
				<td bgcolor='#886A08' style='cursor:pointer;' onclick=movewarna('#886A08','".$jenis."')></td>
				<td bgcolor='#868A08' style='cursor:pointer;' onclick=movewarna('#868A08','".$jenis."')></td>
				<td bgcolor='#688A08' style='cursor:pointer;' onclick=movewarna('#688A08','".$jenis."')></td>
				<td bgcolor='#4B8A08' style='cursor:pointer;' onclick=movewarna('#4B8A08','".$jenis."')></td>
				<td bgcolor='#298A08' style='cursor:pointer;' onclick=movewarna('#298A08','".$jenis."')></td>
				<td bgcolor='#088A08' style='cursor:pointer;' onclick=movewarna('#088A08','".$jenis."')></td>
				<td bgcolor='#088A29' style='cursor:pointer;' onclick=movewarna('#088A29','".$jenis."')></td>
				<td bgcolor='#088A4B' style='cursor:pointer;' onclick=movewarna('#088A4B','".$jenis."')></td>
				<td bgcolor='#088A68' style='cursor:pointer;' onclick=movewarna('#088A68','".$jenis."')></td>
				<td bgcolor='#088A85' style='cursor:pointer;' onclick=movewarna('#088A85','".$jenis."')></td>
				<td bgcolor='#086A87' style='cursor:pointer;' onclick=movewarna('#086A87','".$jenis."')></td>
				<td bgcolor='#084B8A' style='cursor:pointer;' onclick=movewarna('#084B8A','".$jenis."')></td>
				<td bgcolor='#08298A' style='cursor:pointer;' onclick=movewarna('#08298A','".$jenis."')></td>
				<td bgcolor='#08088A' style='cursor:pointer;' onclick=movewarna('#08088A','".$jenis."')></td>
				<td bgcolor='#29088A' style='cursor:pointer;' onclick=movewarna('#29088A','".$jenis."')></td>
				<td bgcolor='#4B088A' style='cursor:pointer;' onclick=movewarna('#4B088A','".$jenis."')></td>
				<td bgcolor='#6A0888' style='cursor:pointer;' onclick=movewarna('#6A0888','".$jenis."')></td>
				<td bgcolor='#8A0886' style='cursor:pointer;' onclick=movewarna('#8A0886','".$jenis."')></td>
				<td bgcolor='#8A0868' style='cursor:pointer;' onclick=movewarna('#8A0868','".$jenis."')></td>
				<td bgcolor='#8A084B' style='cursor:pointer;' onclick=movewarna('#8A084B','".$jenis."')></td>
				<td bgcolor='#8A0829' style='cursor:pointer;' onclick=movewarna('#8A0829','".$jenis."')></td>
				<td bgcolor='#424242' style='cursor:pointer;' onclick=movewarna('#424242','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#610B0B' style='cursor:pointer;' onclick=movewarna('#610B0B','".$jenis."')></td>
				<td bgcolor='#61210B' style='cursor:pointer;' onclick=movewarna('#61210B','".$jenis."')></td>
				<td bgcolor='#61380B' style='cursor:pointer;' onclick=movewarna('#61380B','".$jenis."')></td>
				<td bgcolor='#5F4C0B' style='cursor:pointer;' onclick=movewarna('#5F4C0B','".$jenis."')></td>
				<td bgcolor='#5E610B' style='cursor:pointer;' onclick=movewarna('#5E610B','".$jenis."')></td>
				<td bgcolor='#4B610B' style='cursor:pointer;' onclick=movewarna('#4B610B','".$jenis."')></td>
				<td bgcolor='#38610B' style='cursor:pointer;' onclick=movewarna('#38610B','".$jenis."')></td>
				<td bgcolor='#21610B' style='cursor:pointer;' onclick=movewarna('#21610B','".$jenis."')></td>
				<td bgcolor='#0B610B' style='cursor:pointer;' onclick=movewarna('#0B610B','".$jenis."')></td>
				<td bgcolor='#0B6121' style='cursor:pointer;' onclick=movewarna('#0B6121','".$jenis."')></td>
				<td bgcolor='#0B6138' style='cursor:pointer;' onclick=movewarna('#0B6138','".$jenis."')></td>
				<td bgcolor='#0B614B' style='cursor:pointer;' onclick=movewarna('#0B614B','".$jenis."')></td>
				<td bgcolor='#0B615E' style='cursor:pointer;' onclick=movewarna('#0B615E','".$jenis."')></td>
				<td bgcolor='#0B4C5F' style='cursor:pointer;' onclick=movewarna('#0B4C5F','".$jenis."')></td>
				<td bgcolor='#0B3861' style='cursor:pointer;' onclick=movewarna('#0B3861','".$jenis."')></td>
				<td bgcolor='#0B2161' style='cursor:pointer;' onclick=movewarna('#0B2161','".$jenis."')></td>
				<td bgcolor='#0B0B61' style='cursor:pointer;' onclick=movewarna('#0B0B61','".$jenis."')></td>
				<td bgcolor='#210B61' style='cursor:pointer;' onclick=movewarna('#210B61','".$jenis."')></td>
				<td bgcolor='#380B61' style='cursor:pointer;' onclick=movewarna('#380B61','".$jenis."')></td>
				<td bgcolor='#4C0B5F' style='cursor:pointer;' onclick=movewarna('#4C0B5F','".$jenis."')></td>
				<td bgcolor='#610B5E' style='cursor:pointer;' onclick=movewarna('#610B5E','".$jenis."')></td>
				<td bgcolor='#610B4B' style='cursor:pointer;' onclick=movewarna('#610B4B','".$jenis."')></td>
				<td bgcolor='#610B38' style='cursor:pointer;' onclick=movewarna('#610B38','".$jenis."')></td>
				<td bgcolor='#610B21' style='cursor:pointer;' onclick=movewarna('#610B21','".$jenis."')></td>
				<td bgcolor='#2E2E2E' style='cursor:pointer;' onclick=movewarna('#2E2E2E','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#3B0B0B' style='cursor:pointer;' onclick=movewarna('#3B0B0B','".$jenis."')></td>
				<td bgcolor='#3B170B' style='cursor:pointer;' onclick=movewarna('#3B170B','".$jenis."')></td>
				<td bgcolor='#3B240B' style='cursor:pointer;' onclick=movewarna('#3B240B','".$jenis."')></td>
				<td bgcolor='#3A2F0B' style='cursor:pointer;' onclick=movewarna('#3A2F0B','".$jenis."')></td>
				<td bgcolor='#393B0B' style='cursor:pointer;' onclick=movewarna('#393B0B','".$jenis."')></td>
				<td bgcolor='#2E3B0B' style='cursor:pointer;' onclick=movewarna('#2E3B0B','".$jenis."')></td>
				<td bgcolor='#243B0B' style='cursor:pointer;' onclick=movewarna('#243B0B','".$jenis."')></td>
				<td bgcolor='#173B0B' style='cursor:pointer;' onclick=movewarna('#173B0B','".$jenis."')></td>
				<td bgcolor='#0B3B0B' style='cursor:pointer;' onclick=movewarna('#0B3B0B','".$jenis."')></td>
				<td bgcolor='#0B3B17' style='cursor:pointer;' onclick=movewarna('#0B3B17','".$jenis."')></td>
				<td bgcolor='#0B3B24' style='cursor:pointer;' onclick=movewarna('#0B3B24','".$jenis."')></td>
				<td bgcolor='#0B3B2E' style='cursor:pointer;' onclick=movewarna('#0B3B2E','".$jenis."')></td>
				<td bgcolor='#0B3B39' style='cursor:pointer;' onclick=movewarna('#0B3B39','".$jenis."')></td>
				<td bgcolor='#0B2F3A' style='cursor:pointer;' onclick=movewarna('#0B2F3A','".$jenis."')></td>
				<td bgcolor='#0B243B' style='cursor:pointer;' onclick=movewarna('#0B243B','".$jenis."')></td>
				<td bgcolor='#0B173B' style='cursor:pointer;' onclick=movewarna('#0B173B','".$jenis."')></td>
				<td bgcolor='#0B0B3B' style='cursor:pointer;' onclick=movewarna('#0B0B3B','".$jenis."')></td>
				<td bgcolor='#170B3B' style='cursor:pointer;' onclick=movewarna('#170B3B','".$jenis."')></td>
				<td bgcolor='#240B3B' style='cursor:pointer;' onclick=movewarna('#240B3B','".$jenis."')></td>
				<td bgcolor='#2F0B3A' style='cursor:pointer;' onclick=movewarna('#2F0B3A','".$jenis."')></td>
				<td bgcolor='#3B0B39' style='cursor:pointer;' onclick=movewarna('#3B0B39','".$jenis."')></td>
				<td bgcolor='#3B0B2E' style='cursor:pointer;' onclick=movewarna('#3B0B2E','".$jenis."')></td>
				<td bgcolor='#3B0B24' style='cursor:pointer;' onclick=movewarna('#3B0B24','".$jenis."')></td>
				<td bgcolor='#3B0B17' style='cursor:pointer;' onclick=movewarna('#3B0B17','".$jenis."')></td>
				<td bgcolor='#1C1C1C' style='cursor:pointer;' onclick=movewarna('#1C1C1C','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#2A0A0A' style='cursor:pointer;' onclick=movewarna('#2A0A0A','".$jenis."')></td>
				<td bgcolor='#2A120A' style='cursor:pointer;' onclick=movewarna('#2A120A','".$jenis."')></td>
				<td bgcolor='#2A1B0A' style='cursor:pointer;' onclick=movewarna('#2A1B0A','".$jenis."')></td>
				<td bgcolor='#29220A' style='cursor:pointer;' onclick=movewarna('#29220A','".$jenis."')></td>
				<td bgcolor='#292A0A' style='cursor:pointer;' onclick=movewarna('#292A0A','".$jenis."')></td>
				<td bgcolor='#222A0A' style='cursor:pointer;' onclick=movewarna('#222A0A','".$jenis."')></td>
				<td bgcolor='#1B2A0A' style='cursor:pointer;' onclick=movewarna('#1B2A0A','".$jenis."')></td>
				<td bgcolor='#122A0A' style='cursor:pointer;' onclick=movewarna('#122A0A','".$jenis."')></td>
				<td bgcolor='#0A2A0A' style='cursor:pointer;' onclick=movewarna('#0A2A0A','".$jenis."')></td>
				<td bgcolor='#0A2A12' style='cursor:pointer;' onclick=movewarna('#0A2A12','".$jenis."')></td>
				<td bgcolor='#0A2A1B' style='cursor:pointer;' onclick=movewarna('#0A2A1B','".$jenis."')></td>
				<td bgcolor='#0A2A22' style='cursor:pointer;' onclick=movewarna('#0A2A22','".$jenis."')></td>
				<td bgcolor='#0A2A29' style='cursor:pointer;' onclick=movewarna('#0A2A29','".$jenis."')></td>
				<td bgcolor='#0A2229' style='cursor:pointer;' onclick=movewarna('#0A2229','".$jenis."')></td>
				<td bgcolor='#0A1B2A' style='cursor:pointer;' onclick=movewarna('#0A1B2A','".$jenis."')></td>
				<td bgcolor='#0A122A' style='cursor:pointer;' onclick=movewarna('#0A122A','".$jenis."')></td>
				<td bgcolor='#0A0A2A' style='cursor:pointer;' onclick=movewarna('#0A0A2A','".$jenis."')></td>
				<td bgcolor='#120A2A' style='cursor:pointer;' onclick=movewarna('#120A2A','".$jenis."')></td>
				<td bgcolor='#1B0A2A' style='cursor:pointer;' onclick=movewarna('#1B0A2A','".$jenis."')></td>
				<td bgcolor='#220A29' style='cursor:pointer;' onclick=movewarna('#220A29','".$jenis."')></td>
				<td bgcolor='#2A0A29' style='cursor:pointer;' onclick=movewarna('#2A0A29','".$jenis."')></td>
				<td bgcolor='#2A0A22' style='cursor:pointer;' onclick=movewarna('#2A0A22','".$jenis."')></td>
				<td bgcolor='#2A0A1B' style='cursor:pointer;' onclick=movewarna('#2A0A1B','".$jenis."')></td>
				<td bgcolor='#2A0A12' style='cursor:pointer;' onclick=movewarna('#2A0A12','".$jenis."')></td>
				<td bgcolor='#151515' style='cursor:pointer;' onclick=movewarna('#151515','".$jenis."')></td>
			</tr>
			<tr style=height:25px>
				<td bgcolor='#190707' style='cursor:pointer;' onclick=movewarna('#190707','".$jenis."')></td>
				<td bgcolor='#190B07' style='cursor:pointer;' onclick=movewarna('#190B07','".$jenis."')></td>
				<td bgcolor='#191007' style='cursor:pointer;' onclick=movewarna('#191007','".$jenis."')></td>
				<td bgcolor='#181407' style='cursor:pointer;' onclick=movewarna('#181407','".$jenis."')></td>
				<td bgcolor='#181907' style='cursor:pointer;' onclick=movewarna('#181907','".$jenis."')></td>
				<td bgcolor='#141907' style='cursor:pointer;' onclick=movewarna('#141907','".$jenis."')></td>
				<td bgcolor='#101907' style='cursor:pointer;' onclick=movewarna('#101907','".$jenis."')></td>
				<td bgcolor='#0B1907' style='cursor:pointer;' onclick=movewarna('#0B1907','".$jenis."')></td>
				<td bgcolor='#071907' style='cursor:pointer;' onclick=movewarna('#071907','".$jenis."')></td>
				<td bgcolor='#07190B' style='cursor:pointer;' onclick=movewarna('#07190B','".$jenis."')></td>
				<td bgcolor='#071910' style='cursor:pointer;' onclick=movewarna('#071910','".$jenis."')></td>
				<td bgcolor='#071914' style='cursor:pointer;' onclick=movewarna('#071914','".$jenis."')></td>
				<td bgcolor='#071918' style='cursor:pointer;' onclick=movewarna('#071918','".$jenis."')></td>
				<td bgcolor='#071418' style='cursor:pointer;' onclick=movewarna('#071418','".$jenis."')></td>
				<td bgcolor='#071019' style='cursor:pointer;' onclick=movewarna('#071019','".$jenis."')></td>
				<td bgcolor='#070B19' style='cursor:pointer;' onclick=movewarna('#070B19','".$jenis."')></td>
				<td bgcolor='#070719' style='cursor:pointer;' onclick=movewarna('#070719','".$jenis."')></td>
				<td bgcolor='#0B0719' style='cursor:pointer;' onclick=movewarna('#0B0719','".$jenis."')></td>
				<td bgcolor='#100719' style='cursor:pointer;' onclick=movewarna('#100719','".$jenis."')></td>
				<td bgcolor='#140718' style='cursor:pointer;' onclick=movewarna('#140718','".$jenis."')></td>
				<td bgcolor='#190718' style='cursor:pointer;' onclick=movewarna('#190718','".$jenis."')></td>
				<td bgcolor='#190714' style='cursor:pointer;' onclick=movewarna('#190714','".$jenis."')></td>
				<td bgcolor='#190710' style='cursor:pointer;' onclick=movewarna('#190710','".$jenis."')></td>
				<td bgcolor='#19070B' style='cursor:pointer;' onclick=movewarna('#19070B','".$jenis."')></td>
				<td bgcolor='#000000' style='cursor:pointer;' onclick=movewarna('#000000','".$jenis."')></td>
			</tr>
		</table>
		
		";
		
		break;
		
	default:
		break;
}

function getLoadData($page){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$limit=20;
    
	$offset=$page*$limit;
    $str = "select * from ".$dbname.".bi_map_basic";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$jlhbrs=owlBaris($res);
    	
	$str = "select * from ".$dbname.".bi_map_basic order by idsvg desc limit ".$offset.",".$limit." ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);	
	
	$tab = '';
	$footd = '';
	$no = $page * $limit;
	if($jlhbrs <= 0){
		$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['dataempty']."</tr>";
	}else{
		while($bar = $res->fetch()) {
			$optProvinsi = makeOption($dbname,'provinsi','id,provinsi',"id = '".$bar['namapeta']."'");
			$optNamapeta = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta = '".$bar['tipepeta']."'");
			$no++;
			$tab .= "<tr id='tr_".$no."' class=rowcontent>";
			$tab.="<td style='text-align:right'>".$no."</td>";
			$tab.="<td>".$bar['idsvg']."</td>";
			$tab.="<td>".$bar['tipepeta']."-".$optNamapeta[$bar['tipepeta']]."</td>";
			$tab.="<td>".($bar['tipepeta'] == 'MAP001' ? $optProvinsi[$bar['namapeta']] : $bar['namapeta'])."</td>";
			
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/edit.png' class=zImgBtn  title='Edit' onclick=\"showEdit('".$bar['idsvg']."','".$bar['tipepeta']."','".($bar['tipepeta'] == 'MAP001' ? $optProvinsi[$bar['namapeta']] : $bar['namapeta'])."');\" >
			</td>";
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/delete.png' class=zImgBtn  title='Delete' onclick=\"deldata('".$bar['idsvg']."');\" >
			</td>";
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/zoom.png' class=zImgBtn  title='View Map' onclick=\"showsvg('".$bar['idsvg']."','dasar',event);\" >
			</td>";
			$tab .= "</tr>"; 
		}
	}
	
	$totrows = ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	$isiRow='';
	for($er = 1;$er <= $totrows;$er++){
		$sel = ($page == $er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	$footd.="</tr>
		<tr><td colspan=20 align=center>";
	
	if($page=='0'){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
	}
	
	$footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
	
	if(($page+1) == $totrows){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
	}
	$footd.="</td>
		</tr>";
	return $tab."####".$footd;
}

function getLoadData2($page){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$limit=20;
    
	$offset=$page*$limit;
    $str = "select * from ".$dbname.".bi_map_pt";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$jlhbrs=owlBaris($res);
    	
	$str = "select * from ".$dbname.".bi_map_pt order by idsvg desc limit ".$offset.",".$limit." ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
		
	$tab = '';
	$footd = '';
	$no = $page * $limit;
	if($jlhbrs <= 0){
		$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['dataempty']."</tr>";
	}else{
		while($bar = $res->fetch()) {
			$optPerusahaan = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi = '".$bar['kodeorg']."'");
			$optProvinsi = makeOption($dbname,'provinsi','id,provinsi',"id = '".$bar['id_provinsi']."'");
			$optNamapeta = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta = '".$bar['tipepeta']."'");
			$expKeterangan = explode('##',$bar['keterangan']);
			$no++;
			$tab .= "<tr id='tr_".$no."' class=rowcontent>";
			$tab.="<td style='text-align:right'>".$no."</td>";
			$tab.="<td>".$bar['idsvg']."</td>";
			$tab.="<td>".$optPerusahaan[$bar['kodeorg']]." (".$bar['kodeorg'].")</td>";
			$tab.="<td>".$optPerusahaan[$bar['unit']]." (".$bar['unit'].")</td>";
			$tab.="<td>".$optProvinsi[$bar['id_provinsi']]."</td>";
			$tab.="<td>".$bar['tipepeta']."-".$optNamapeta[$bar['tipepeta']]."</td>";
			$tab.="<td>".$bar['namapeta']."</td>";
			$tab.="<td>".$expKeterangan[0]."</td>";
			
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/edit.png' class=zImgBtn  title='Edit' onclick=\"showEdit2('".$bar['idsvg']."','".$bar['id_provinsi']."','".$bar['kodeorg']."','".$bar['unit']."','".$bar['tipepeta']."','".$bar['namapeta']."');\" >
			</td>";
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/delete.png' class=zImgBtn  title='Delete' onclick=\"deldata2('".$bar['idsvg']."');\" >
			</td>";
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/zoom.png' class=zImgBtn  title='View Map' onclick=\"showsvg2('".$bar['idsvg']."','pt',event);\" >
			</td>";
			$tab .= "</tr>"; 
		}
	}
	
	$totrows = ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	$isiRow='';
	for($er = 1;$er <= $totrows;$er++){
		$sel = ($page == $er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	$footd.="</tr>
		<tr><td colspan=20 align=center>";
	
	if($page=='0'){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loaddata2(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
	}
	
	$footd.="<select id=\"pages2\" name=\"pages2\" style=\"width:50px\" onchange=\"getPage2()\">".$isiRow."</select>";
	
	if(($page+1) == $totrows){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loaddata2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
	}
	$footd.="</td>
		</tr>";
	return $tab."####".$footd;
}

function getLoadData3($page){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$limit=20;
    
	$offset=$page*$limit;
    $str = "select * from ".$dbname.".bi_map_transaksi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$jlhbrs=owlBaris($res);
    	
	$str = "select * from ".$dbname.".bi_map_transaksi order by idsvg desc limit ".$offset.",".$limit." ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
		
	$tab = '';
	$footd = '';
	$no = $page * $limit;
	if($jlhbrs <= 0){
		$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['dataempty']."</tr>";
	}else{
		while($bar = $res->fetch()) {
			$optInduk = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi = '".$bar['kodeorg']."'");
			$optPerusahaan = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
			$optNamapeta = makeOption($dbname,'bi_5tipepeta','id_tipepeta,keterangan',"id_tipepeta = '".$bar['tipepeta']."'");
			$optTipeDok = makeOption($dbname,'bi_5tipedok','id_tipedok,nama_tipe',"id_tipedok = '".$bar['tipedok']."'");
			$optNamaKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan = '".$bar['kodekegiatan']."'");
			$expKeterangan = explode('##',$bar['keterangan']);
			$no++;
			$tab .= "<tr id='tr_".$no."' class=rowcontent>";
			$tab.="<td style='text-align:right'>".$no."</td>";
			$tab.="<td>".$bar['idsvg']."</td>";
			$tab.="<td>".$bar['periode']."</td>";
			$tab.="<td>".$optPerusahaan[$optInduk[$bar['kodeorg']]]." (".$optInduk[$bar['kodeorg']].")</td>";
			$tab.="<td>".$optPerusahaan[$bar['kodeorg']]." (".$bar['kodeorg'].")</td>";
			$tab.="<td>".$bar['tipedok']."-".$optTipeDok[$bar['tipedok']]."</td>";
			$tab.="<td>".$bar['kodekegiatan']."-".$optNamaKeg[$bar['kodekegiatan']]."</td>";
			$tab.="<td>".$bar['fitur']."</td>";
			$tab.="<td>".$bar['warna']."</td>";
			$tab.="<td>".$bar['keterangan']."</td>";
			
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/edit.png' class=zImgBtn  title='Edit' onclick=\"showEdit3('".$bar['idsvg']."','".$bar['periode']."','".$optInduk[$bar['kodeorg']]."','".$bar['kodeorg']."','".$bar['tipedok']."','".$bar['kodekegiatan']."','".$bar['fitur']."','".$bar['warna']."','".$bar['keterangan']."');\" >
			</td>";
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/delete.png' class=zImgBtn  title='Delete' onclick=\"deldata3('".$bar['idsvg']."');\" >
			</td>";
			$tab.="<td align=center>
				<img src='images/".$_SESSION['theme']."/zoom.png' class=zImgBtn  title='View Map' onclick=\"showsvg3('".$bar['idsvg']."','lain',event);\" >
			</td>";
			$tab .= "</tr>"; 
		}
	}
	
	$totrows = ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	$isiRow='';
	for($er = 1;$er <= $totrows;$er++){
		$sel = ($page == $er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	$footd.="</tr>
		<tr><td colspan=20 align=center>";
	
	if($page=='0'){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loaddata3(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
	}
	
	$footd.="<select id=\"pages3\" name=\"pages3\" style=\"width:50px\" onchange=\"getPage3()\">".$isiRow."</select>";
	
	if(($page+1) == $totrows){
		$footd.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
	}else{
		$footd.="<button class=mybutton onclick=loaddata3(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
	}
	$footd.="</td>
		</tr>";
	return $tab."####".$footd;
}

// function getID(){
	// global $conn;
	// global $dbname;
	// global $owlPDO;
	
	// $str = "select idsvg from ".$dbname.".bi_map_basic order by idsvg DESC LIMIT 1";
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $res->setFetchMode(PDO::FETCH_ASSOC);
	// $numrow=$res->rowCount();
	
	// if($numrow == 0){
		// $idno = "SVG0000001";
	// }else{
		// $bar = $res->fetch();
		// $expNo = explode('SVG',$bar['idsvg']);
		// $idno = 'SVG'.addZero(intval($expNo[1]) + 1,7);
	// }
	
	// return $idno;
// }

function getID(){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str = "select idsvg from ".$dbname.".bi_map_basic order by idsvg DESC LIMIT 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrow=$res->rowCount();
	
	if($numrow == 0){
		$idno = 1;
	}else{
		$bar = $res->fetch();
		$expNo = explode('SVG',$bar['idsvg']);
		$idno = $expNo[1];
	}
	
	return $idno;
}



// function getID2(){
	// global $conn;
	// global $dbname;
	// global $owlPDO;
	
	// $str = "select idsvg from ".$dbname.".bi_map_pt order by idsvg DESC LIMIT 1";
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $res->setFetchMode(PDO::FETCH_ASSOC);
	// $numrow=$res->rowCount();
	
	// if($numrow == 0){
		// $idno = "SVGPT00001";
	// }else{
		// $bar = $res->fetch();
		// $expNo = explode('SVGPT',$bar['idsvg']);
		// $idno = 'SVGPT'.addZero(intval($expNo[1]) + 1,5);
	// }
	
	// return $idno;
// }

function getID2(){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str = "select idsvg from ".$dbname.".bi_map_pt order by idsvg DESC LIMIT 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrow=$res->rowCount();
	
	if($numrow == 0){
		$idno = 1;
	}else{
		$bar = $res->fetch();
		$expNo = explode('SVGPT',$bar['idsvg']);
		$idno = $expNo[1];
	}
	
	return $idno;
}

function getID3(){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str = "select idsvg from ".$dbname.".bi_map_transaksi order by idsvg DESC LIMIT 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrow=$res->rowCount();
	
	if($numrow == 0){
		$idno = "SVGKG00001";
	}else{
		$bar = $res->fetch();
		$expNo = explode('SVGKG',$bar['idsvg']);
		$idno = 'SVGKG'.addZero(intval($expNo[1]) + 1,5);
	}
	
	return $idno;
}
?>