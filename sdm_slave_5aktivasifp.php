<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg=$_POST['kodeorg'];
$tutup=$_POST['tutup'];
$method=$_POST['method'];
$tanggal=tanggalsystemn($_POST['tanggal']);
$param = $_POST;

switch($method){
	case 'getjenisval':
		$tab="<table class=sortable cellspacing=1 border=0 cellpadding=5>
			<thead>
			 <tr class=rowheader>
					<th>No</th>
					<th>".$_SESSION['lang']['detail']."</th>
					<th>".$_SESSION['lang']['action']."<br>
					<input id=checkall type=checkbox onclick=clickall()></th>
				</tr>
			 </thead>
			 <tbody>
			";
		$kep = explode(",",$param['detailval']);
		foreach($kep as $kpd){
			$kpda[$kpd]=$kpd;
		}		
		if($param['jenisval']=='transaksi'){
			$arraysumber=array(
						'BKM'=>'BKM - Pemeliharaan',
						'PNN'=>'BKM - Panen',
						'TRK'=>'TRK - Pekerjaan',
						'SDM'=>'SDM - Absensi'
					);
			foreach($arraysumber as $key => $data){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$data."</td>";
				$tab.="<td hidden name=nama[]>".$key."</td>";
				if($kpda[trim($key)]!=''){
					$tab.="<td align=center><input name=check[] type=checkbox checked></td>";
				}else{							
					$tab.="<td align=center><input name=check[] type=checkbox></td>";
				}
				$tab.="</tr>";
			}
		}elseif($param['jenisval']=='subbagian'){
			
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$param['kodeorg']." - ".getNamaOrg($param['kodeorg'])."</td>";
			$tab.="<td hidden name=nama[]>".$param['kodeorg']."</td>";
			if($kpda[$param['kodeorg']]!=''){
				$tab.="<td align=center><input name=check[] type=checkbox checked></td>";
			}else{							
				$tab.="<td align=center><input name=check[] type=checkbox></td>";
			}
			$tab.="</tr>";
			
			$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe not like '%GUDANGTEMP%' and induk='".$param['kodeorg']."' order by induk";   
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</td>";
				$tab.="<td hidden name=nama[]>".$bar['kodeorganisasi']."</td>";
				if($kpda[trim($bar['kodeorganisasi'])]!=''){
					$tab.="<td align=center><input name=check[] type=checkbox checked></td>";
				}else{							
					$tab.="<td align=center><input name=check[] type=checkbox></td>";
				}
				$tab.="</tr>";
			}
		}
		$tab.="<tr class=rowcontent style=height:25px>";
			$tab.="<td align=center colspan=3><button style=width:50px class=mybutton onclick=adddata()>Add</button></td>";
		$tab.="</tr>";
		$tab.="</tbody>
		</table>
		";
		echo $tab;
		// exit("error");
	break;
	case 'getdetail':
		$tipeorg = getNamaOrg($param['kodeorg'],'tipe');
		
		echo $tipeorg;
		// exit("error");
	break;
	case 'update':
		if($param['tanggal']==''){
			exit("Warning : Tanggal harus terisi.");
		}
		if(trim($param['jenisval'])!='' and trim($param['detailval'])==''){
			exit("Warning : Jenis dan Detail Validasi harus terisi.");
		}
		
		if($param['tanggal']==''){
			exit("Warning : Tanggal harus terisi.");
		}
		if(trim($param['jenisval'])!='' and trim($param['detailval'])==''){
			exit("Warning : Jenis dan Detail Validasi harus terisi.");
		}
		$str = "SELECT * FROM ".$dbname.".sdm_5aktivasifp where kodeorg='".$kodeorg."'";  
		$res = fetchdata($str);
		if(count($res)>0){
			$str="delete from ".$dbname.".sdm_5aktivasifp where kodeorg='".$kodeorg."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		}			
		$str="insert into ".$dbname.".sdm_5aktivasifp (kodeorg,status,tanggal,updateby,tipevalidasi,detailvalidasi)
			  values('".$kodeorg."','".$tutup."','".$tanggal."','".$_SESSION['standard']['userid']."','".$param['jenisval']."','".$param['detailval']."')";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		
        // $str="update ".$dbname.".sdm_5aktivasifp set status=".$tutup.", tanggal='".$tanggal."',updateby='".$_SESSION['standard']['userid']."'  where kodeorg='".$kodeorg."' "; #exit("error".$str);
        // try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	break;
	case 'insert':
		if($param['tanggal']==''){
			exit("Warning : Tanggal harus terisi.");
		}
		if(trim($param['jenisval'])!='' and trim($param['detailval'])==''){
			exit("Warning : Jenis dan Detail Validasi harus terisi.");
		}
		$str = "SELECT * FROM ".$dbname.".sdm_5aktivasifp where kodeorg='".$kodeorg."'";  
		$res = fetchdata($str);
		if(count($res)>0){
			$str="delete from ".$dbname.".sdm_5aktivasifp where kodeorg='".$kodeorg."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		}			
		$str="insert into ".$dbname.".sdm_5aktivasifp (kodeorg,status,tanggal,updateby,tipevalidasi,detailvalidasi)
			  values('".$kodeorg."','".$tutup."','".$tanggal."','".$_SESSION['standard']['userid']."','".$param['jenisval']."','".$param['detailval']."')";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }	
	
		
        break;
	case 'delete':
        $str="delete from ".$dbname.".sdm_5aktivasifp where kodeorg='".$kodeorg."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	break;
	default:
	break;					
}
if($method=='update' or $method=='insert' or $method=='delete'){	
	$nmjenis=array(''=>'Seluruhnya','transaksi'=>'Sumber Transaksi','subbagian'=>'Subbagian Data Karyawan');
	$nmorg['BKM']='BKM - Pemeliharaan';
	$nmorg['PNN']='PNN - Panen';
	$nmorg['TRK']='TRK - Pekerjaan';
	$nmorg['SDM']='SDM - Absensi';
	$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where length(kodeorganisasi)<=6 order by induk";   
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	}

	$str1="select a.*,case status when '1' then '".$_SESSION['lang']['yes']."'
	when '0' then '".$_SESSION['lang']['no']."' end as statustampil from ".$dbname.".sdm_5aktivasifp a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi order by induk, kodeorg";
	$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res->fetch()){
		$key = $bar1->kodeorg;
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
		$d=$induk[$key];
		if($d!=$n){			
			echo"<tr class=rowcontent>
				<td colspan=10><b>".getNamaOrg($d)."</b></td>
			</tr>";
		}
		
		$no++;
		echo"<tr class=rowcontent style=vertical-align:top;>
			<td align=center>".$no."</td>
			<td align=center>".$bar1->kodeorg."</td>
			<td align=left>".getNamaOrg($bar1->kodeorg)."</td>
			<td align=center>".$bar1->statustampil."</td>
			<td align=center>".$bar1->tanggal."</td>
			<td align=left>".$nmjenis[$bar1->tipevalidasi]."</td>";
			
			$explx=explode(",",$bar1->detailvalidasi);
			$isidata="";
			if($bar1->detailvalidasi!=''){						
				foreach($explx as $kodex){
					$nomor++;
					$isidata.=$nomor.". ".$kodex." - ".$nmorg[$kodex]."<br>";
				}
			}else{
				$isidata="Seluruhnya";						
			}
		echo"<td align=left>".$isidata."</td>
			<td align=center>".getKary($bar1->updateby)."</td>
			<td align=center>".$bar1->lastupdate."</td>
			<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$bar1->status."','".tanggalnormal($bar1->tanggal)."','".$bar1->tipevalidasi."','".$bar1->detailvalidasi."','".getNamaOrg($bar1->kodeorg,'tipe')."');\"></td></tr>";
		$n=$d;	
	}	 
}
?>
