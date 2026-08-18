<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method      = checkPostGet('method','');
$regional    = checkPostGet('regional','');
$levelkaryawan= checkPostGet('levelkaryawan','');
$tipekaryawan = checkPostGet('tipekaryawan','');
$kodegolongan = checkPostGet('kodegolongan','');
$kode         = checkPostGet('kode','');
$jenis        = checkPostGet('jenis','');
$pt           = checkPostGet('pt','');
$unit         = checkPostGet('unit','');
$param        = $_POST;

switch($method){

	case 'loadData':  
		listData();
	break;

	case 'getunit':  

		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where.="and tipe = 'KEBUN' or tipe = 'HOLDING'";
		}else{
			$where.=" and tipe = 'KEBUN' ";
		}
		
		$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' ".$where." order by namaorganisasi asc "; #exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $optun;
	break;
	
	case 'insert':

		$str="select * from ".$dbname.".sdm_5plafondinas where pt='".$pt."' and unit ='".$unit."' and regiontujuan='".$regional."' and levelkaryawan='".$levelkaryawan."' and tipekaryawan='".$tipekaryawan."' and golongan = '".$kodegolongan."'  and jenis = '".$jenis."'";
		$qry=fetchdata($str);
		if(count($qry)>0){
			echo "Error: Data sudah pernah terdaftar sebelumnya.";
		}else{
			$data = array(
				'pt'          	 => $param['pt'],
				'unit'           => $param['unit'],
				'jenis'          => $param['jenis'],
				'levelkaryawan'  => $param['levelkaryawan'],
				'tipekaryawan'   => $param['tipekaryawan'],
				'golongan'   	 => $param['kodegolongan'],
				'jabatan'        => $param['jabatan'],
				'regiontujuan'   => $param['regional'],
				'jumlah'         => $param['rupiah'],
				'status'         => '1',
				'updateby'       => $_SESSION['standard']['userid'],
				'lastupdate'     => date("Y-m-d H:i:s")
			);
			
			$cols = array();
			foreach($data as $keyn=>$rown) {
					$cols[] = $keyn;
			}
			$str = insertQuery($dbname,'sdm_5plafondinas',$data,$cols); #exit("error".$str);
			try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	
	case 'update':
		$data = array(
			'jenis'          => $param['jenis'],
			'levelkaryawan'  => $param['levelkaryawan'],
			'tipekaryawan'   => $param['tipekaryawan'],
			'golongan'   	 => $param['kodegolongan'],
			'jabatan'        => $param['jabatan'],
			'regiontujuan'   => $param['regional'],
			'jumlah'         => $param['rupiah'],
			'status'         => '1',
			'updateby'       => $_SESSION['standard']['userid'],
			'lastupdate'     => date("Y-m-d H:i:s")
		);
		$where = "kode='".$kode."'";
		$str = updateQuery($dbname,'sdm_5plafondinas',$data,$where);
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".sdm_5plafondinas where kode='".$kode."'";
		try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	default:
	break;
}

function listData(){
	global $dbname;
	global $conn;
	global $owlPDO;
	global $param;

	$wh="";
	if($param['regional']!=''){
		$wh.="and regiontujuan = '".$param['regional']."'";
	}
	if($param['levelkaryawan']!=''){
		$wh.="and levelkaryawan = '".$param['levelkaryawan']."'";
	}
	if($param['tipekaryawan']!=''){
		$wh.="and tipekaryawan = '".$param['tipekaryawan']."'";
	}

	if($param['kodegolongan']!=''){
		$wh.="and golongan = '".$param['kodegolongan']."'";
	}

	if($param['jenis']!=''){
		$wh.="and jenis = '".$param['jenis']."'";
	}
	if($param['pt']!=''){
		$wh.="and pt = '".$param['pt']."'";
	}
	if($param['unit']!=''){
		$wh.="and unit = '".$param['unit']."'";
	}


	$footer     ="";
	$limit      = 15;
	$page 		= checkPostGet('page',1);
	$colspan    = 10;

	
	$offset     = floatval($page) * $limit;
	$maxdisplay =(floatval($page) * $limit);
	$no         =((floatval($page) * $limit));
	

	$str		= "select COUNT(*) AS jmlhrow from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh." order by pt asc, unit asc, regiontujuan asc,tipekaryawan asc,levelkaryawan asc,golongan asc,jenis asc";
	$res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$jlhbrs     = owlBaris($res);
	$res        = fetchdata($str);
	$jlhbrs     = $res[0]['jmlhrow'];
	$totrows    = ceil($jlhbrs / $limit);
	
	if($totrows == 0){
		$totrows = 1;
	}

	$isiRow = '';
	for ($er = 1; $er <= $totrows; $er++){
		$sel    = ($page==$er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}

	$frompage   = ((floatval($page)*$limit)+1);
	if(((floatval($page)+1)*$limit) > $jlhbrs){
		$topage = $jlhbrs;
	}else{
		$topage = ((floatval($page)+1)*$limit);
	}
	
	$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh." order by pt asc, unit asc, regiontujuan asc,tipekaryawan asc,levelkaryawan asc,golongan asc,jenis asc limit ".$offset.",".$limit."";
	$qry=fetchdata($str);
	if(count($qry)<1){
		echo"<tr class=rowcontent><td colspan='10' style='text-align:center;'>".$_SESSION['lang']['datanotfound']."</td></tr>";
	}else{
		$no=0;
		$nmreg = makeOption($dbname, 'sdm_5regionalpjd', 'regional,nama');
		$nmreg['OTH']='LAIN-LAIN';
		$nmlvlkar = makeOption($dbname, 'sdm_5levelkaryawan', 'kode,nama');
		$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$nmtpkar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
		$nmjns = makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
		$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$nmdri = makeOption($dbname, 'sdm_5setupdinasdriver', 'id,keterangan');
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		foreach($qry as $row){
			$no+=1;
			echo"<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$row['pt']." - ".$nmorg[$row['pt']]." </td>
				<td>".$row['unit']." - ".$nmorg[$row['unit']]." </td>
				<td>".$nmreg[$row['regiontujuan']]."</td>
				<td>".$nmtpkar[$row['tipekaryawan']]."</td>
				<td>".$nmlvlkar[$row['levelkaryawan']]."</td>
				<td>".$nmgol[$row['golongan']]."</td>
				<td>".$nmjns[$row['jenis']]."</td>
				<td style='text-align:right;'>".number_format($row['jumlah'],2)."</td>
				<td align=center width=20px><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$row['kode']."','".$row['pt']."','".$row['unit']."','".$row['regiontujuan']."','".$row['tipekaryawan']."','".$row['levelkaryawan']."','".$row['golongan']."','".$row['jenis']."','".$row['jumlah']."')\"></td>
				<td align=center width=20px><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deleteData('".$row['kode']."')\"></td>
			</tr>";
		}

		$footer .= "<tr>
						<td colspan=".$colspan." align=center>
							".$frompage." to ".$topage." Of ".  $jlhbrs."
						</td>
					</tr>";
		$footer .= "<tr>
						<td colspan=".$colspan." align=center>";
						if($page!=0){
							$footer .= "<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>Prev</button>";
						}
		$footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
						if((floatval($page)+1) != $totrows){
							$footer .="<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>Next</button>";
						}
		$footer .= "</td></tr>";

		echo $footer;
	}
}
?>