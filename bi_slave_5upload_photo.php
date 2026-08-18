<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses','');
$tipedokumen = checkPostGet('tipedokumen','');
$kegiatan = checkPostGet('kegiatan','');
$nodok = checkPostGet('nodok','');
$file = checkPostGet('file','');
$fileupload = checkPostGet('fileupload','');
$namafile = checkPostGet('namafile','');
$method = checkPostGet('method','');
$tipedel = checkPostGet('tipedel','');
$namafile = checkPostGet('namafile','');

switch($proses){
	case 'loaddata':
		$result .= "";
		
		$str = "select distinct(t1.nodok) as nodok, t3.tipedok as tipedok, t3.kodekegiatan as kodekegiatan from ".$dbname.".bi_map_transaksi_dok_photo t1
		left join ".$dbname.".bi_map_transaksi_dok t2 on t1.nodok = t2.nodok 
		left join ".$dbname.".bi_map_transaksi t3 on t2.idsvg = t3.idsvg 
		order by t1.nodok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$jlhbrs=owlBaris($res);
		
		if($jlhbrs == 0){
			$tab .= $_SESSION['lang']['datanotfound'];
		}else{
			$nourut = 0;
			$arrDet = array();
			while($bar = $res->fetch()){
				$nourut = $nourut + 1;
				$optTipedok = makeOption($dbname,'bi_5tipedok','id_tipedok,nama_tipe',"id_tipedok = '".$bar['tipedok']."'");
				$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kodekegiatan']."'");
				$result .= "<tr class=rowcontent>
					<td style='text-align:right'>".$nourut."</td>
					<td>".$optTipedok[$bar['tipedok']]." (".$bar['tipedok'].")</td>
					<td>".$bar['kodekegiatan']." - ".$optNamaKegiatan[$bar['kodekegiatan']]."</td>
					<td>".$bar['nodok']."</td>
					<td style='text-align:center'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['tipedok']."','".$bar['kodekegiatan']."','".$bar['nodok']."')\">
					</td>
					<td style='text-align:center'>
						<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$bar['nodok']."')\">
					</td>
					<td style='text-align:center'>
						<img src='images/skyblue/zoom.png' class='resicon' title='Detail' onclick=\"detailfield('".$bar['nodok']."',event)\">
					</td>
				</tr>";
			}
		}		
		echo $result;
		break;
		
	case 'simpan':
		if(count($_SESSION['nodokphoto']) <= 0){
			exit("error : Photo dokumen harus diinput.");
		}
		
		if($method == 'insert'){
			$str = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$nodok."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$jlhbrs=owlBaris($res);
			$count = 1;
			
			if($jlhbrs >= 1){
				exit("error : No dokumen ini sudah pernah terdaftar.");
			}else{
				foreach($_SESSION['nodokphoto'] as $row){
					$str = "insert into ".$dbname.".bi_map_transaksi_dok_photo (nodok,nourut,namafile) value ('".$nodok."','".$count."','".$row['namafile']."')";
					try{
						$owlPDO->exec($str);
					}catch(PDOException $e){
						echo "error : ".$e->getMessage();
					}				
					$count++;
				}
			}
		}else{
			$str = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$nodok."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				foreach($_SESSION['nodokphoto'] as $row){
					if(trim($row['namafile']) != trim($bar['namafile'])){
						// unlink("fileupload/photodok/".$bar['namafile']);
					}
				}
			}
			
			$str = "delete from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$nodok."'";
			try{
				$owlPDO->exec($str);
			}catch(PDOException $e){
				echo "error : ".$e->getMessage();
			}
			
			$count = 0;
			foreach($_SESSION['nodokphoto'] as $row){
				$str = "insert into ".$dbname.".bi_map_transaksi_dok_photo (nodok,nourut,namafile) value ('".$nodok."','".$count."','".$row['namafile']."')";
				try{
					$owlPDO->exec($str);
				}catch(PDOException $e){
					echo "error : ".$e->getMessage();
				}				
				$count++;
			}
		}
		break;
		
	case 'deletefield':
		$str = "select namafile from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$nodok."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			unlink("fileupload/photodok/".$bar['namafile']);
		}
	
		$str = "delete from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$nodok."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		
	case 'fillfield':
		$result = "";
		$_SESSION['nodokphoto'] = array();
		$str = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$nodok."' order by nourut";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while($bar = $res->fetch()){
			$no = $no + 1;
			$_SESSION['nodokphoto'][$no]['namafile'] = $bar['namafile'];
			$result .= "<tr class=rowcontent>
				<td style='cursor:pointer;' onclick=\"isifile('".$bar['namafile']."','event');\"><u><font color=blue>".$bar['namafile']."</td>
				<td style='text-align:center'><img title='Hapus' class=resicon onclick=\"deletephoto(this,'".$bar['namafile']."')\" src='images/delete_32.png'/></td>
			</tr>";
		}	
		
		echo $result;
		break;
	
	case 'getkegiatan':
		$optKegiatan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($tipedokumen != ''){
			$str = "select distinct(kodekegiatan) as kodekegiatan from ".$dbname.".bi_map_transaksi where tipedok = '".$tipedokumen."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kodekegiatan']."'");
				$optKegiatan .= "<option value='".$bar['kodekegiatan']."'>".$bar['kodekegiatan']."-".$optNamaKegiatan[$bar['kodekegiatan']]."</option>";
			}
		}
		echo $optKegiatan;
		break;
		
	case 'getnodok':
		$optNodok = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($tipedokumen != ''){
			$str = "select t2.nodok as nodok from ".$dbname.".bi_map_transaksi t1 
			left join ".$dbname.".bi_map_transaksi_dok t2 on t1.idsvg = t2.idsvg
			where t1.tipedok = '".$tipedokumen."' and t1.kodekegiatan = '".$kegiatan."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$optNodok .= "<option value='".$bar['nodok']."'>".$bar['nodok']."</option>";
			}
		}
		echo $optNodok;
		break;
		
	case 'adddok':
		if($nodok == ''){
			exit("warning : ".$_SESSION['lang']['nodok']." harus dipilih.");
		}
		
		if($fileupload == ''){
			exit("warning : File photo harus diisi.");
		}else{
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if($filetype=='.jpg' || $filetype=='.jpeg' || $filetype=='.png'){
					if($_FILES['file']['size'] <= 250000){
						$nourut = lastnourut($nodok) + 1;
						$countnourut = count($_SESSION['nodokphoto']);
						$resultnourut = $nourut + $countnourut;
						$namafile = $resultnourut.$_FILES['file']['name'];
						$newdata = array(
							'namafile'=>$namafile
						);
						move_uploaded_file($file_tmpname,"fileupload/photodok/".$namafile);
						array_push($_SESSION['nodokphoto'],$newdata);
						echo $namafile;
					}else{
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("warning : Format file upload harus .jpg,.jpeng,.png");
				}
			}
		}		
		break;
	
	case 'deletephoto':
		$count = 0;
		foreach($_SESSION['nodokphoto'] as $key=>$row){
			if($row['namafile'] == $namafile){
				if($tipedel == 'delete'){
					unlink("fileupload/photodok/".$namafile);
				}
				unset($_SESSION['nodokphoto'][$key]);
			}
			$count++;
		}
		break;
		
	case 'batal':
		$_SESSION['nodokphoto'] = array();
		break;
		
	case 'detailfield':
		$result ="<link rel=stylesheet type=text/css href=style/generic.css>";
		$result .= "<table cellpading=5 cellspacing=1 class=sortable >
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td> 
					<td align=center>".$_SESSION['lang']['nodok']."</td>  							
					<td align=center>".$_SESSION['lang']['photo']."</td> 
				</tr>
			</thead>
			<tbody>";
		
		$str = "select * from ".$dbname.".bi_map_transaksi_dok_photo where nodok = '".$nodok."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while($bar = $res->fetch()){
			$no = $no + 1;
			$result .= "<tr class=rowcontent>
				<td align=right>".$no."</td>
				<td>".$nodok."</td>
				<td style='cursor:pointer;' onclick=\"parent.isifile('".$bar['namafile']."','event');\"><u><font color=blue>".$bar['namafile']."</font></u></td>
			</tr>";
		}
			
		$result .= "</tbody></table>";
		
		echo $result;
		break;
		
	case'isifile':
		$expNamafile = explode('.',$namafile);
		if($expNamafile[1]=='pdf'){
			echo "<embed src='fileupload/photodok/".$namafile."' width=780px height=370px>";
		}else{
			echo"<img src='fileupload/photodok/".$namafile."'>";
		}
	break;
}

function lastnourut($sentnodok){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str = "select nourut from ".$dbname.".bi_map_transaksi_dok_photo where nourut = '".$sentnodok."' order by nourut DESC LIMIT 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrow=$res->rowCount();
	
	if($numrow == 0){
		$nourut = 0;
	}else{
		$bar = $res->fetch();
		$nourut = $bar['nourut'] + 1;
	}
	
	return $nourut;
}
?>