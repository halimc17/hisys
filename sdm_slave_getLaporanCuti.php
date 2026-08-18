<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$kodeorg=checkPostGet('kodeorg','');
$periode=checkPostGet('periode','');
$karyawan=checkPostGet('karyawan','');
$method=checkPostGet('method','');

switch($method){
	case 'preview':
		$whr='';
		$arrdata=array();
		if($kodeorg!=''){
			$whr=" and b.lokasitugas='".$kodeorg."'";
		}
		$hariini = date("Y-m-d");
		// in(0,1,2,3,6,7,8,9,12)
		$str1="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
	       from ".$dbname.".sdm_cutiht a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	       where 1=1 ".$whr." 
		   and a.periodecuti='".$periode."' 
		   and a.karyawanid like '%".$karyawan."%'
		   and b.tipekaryawan!='4'
		   and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by b.namakaryawan"; 
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res1);
		
		$dat_ = fetchData($str1);
		foreach($dat_ as $du){
			$arrkarid[] = $du['karyawanid'];
		}
		// echo "<pre>"; print_r($arrkarid); exit;

		$implode_id = implode("','",$arrkarid);

		#idjenis yang memotong hakcuti
		$potonganijin = makeOption($dbname,"sdm_5jenisijin","idjenis,idjenis","statuspotongan = 1");

		$imp_pot = implode("','",array_values($potonganijin));

		## CEK STAFF
		$str="select SUM(jumlahhari) as jumlahhari,karyawanid from ".$dbname.".sdm_ijin where karyawanid IN ('$implode_id') and periodecuti='".$periode."' and idjenis IN ('$imp_pot') and statuspersetujuan = '1' and statuspersetujuan_cancel not in ('1', '9', '2') GROUP BY karyawanid order by darijam desc";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['karyawanid']]['ambil']+=$val['jumlahhari'];
		}
		
		## CEK NON STAFF
		$str="select SUM(jumlahhari) as jumlahhari,karyawanid from ".$dbname.".sdm_ijinnonstaff where karyawanid IN ('$implode_id') and periodecuti='".$periode."' and idjenis IN ('$imp_pot') and statuspersetujuan = '1' and statuspersetujuan_cancel not in ('1', '9', '2') GROUP BY karyawanid order by darijam desc";
		$res=fetchdata($str);
		foreach($res as $val){
			@$arrdata[$val['karyawanid']]['ambil']+=$val['jumlahhari'];
		}

		if($numrows <= 0){
			echo $_SESSION['lang']['datanotfound'];
		}else{
			echo"<table class=sortable cellspacing=1 border=0>
				 <thead>
				 <tr class=rowheader>
					<td>No</td>
					<td align=center width=50px>".$_SESSION['lang']['kodeorganisasi']."</td>		 
					<td align=center>".$_SESSION['lang']['nik2']."</td>
					<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
					<td align=center>".$_SESSION['lang']['tanggalmasuk']."</td>			
					<td align=center>".$_SESSION['lang']['periode']."</td>			
					<td align=center>".$_SESSION['lang']['dari']."</td>
					<td align=center>".$_SESSION['lang']['tanggalsampai']."</td>
					<td align=center width=50px>".$_SESSION['lang']['hakcuti']."</td>
					<td align=center width=50px>".$_SESSION['lang']['diambil']."</td>
					<td align=center width=50px>".$_SESSION['lang']['sisa']."</td>
					</tr>
				 </thead>
				 <tbody id=container>"; 
			$no=0;	 
			while($bar1=$res1->fetch())
			{
				$no+=1;

				if($periode == '2020'){
					$sisa = $bar1->sisa;
				}else{
					// if ($periode < date('Y') && $bar1->hakcuti >= 6) {
					// 	$hakcuti = 6;
					// } else {
					// 	$hakcuti = $bar1->hakcuti;
					// }

					$sisa = ($bar1->hakcuti - @$arrdata[$bar1->karyawanid]['ambil']);
				}
				
				echo"<tr class=rowcontent id=baris".$no.">
						   <td align=center>".$no."</td>
						   <td align=center>".substr($bar1->kodeorg,0,4)."</td>
						   <td align=center>".$bar1->nik."</td>
						   <td>".$bar1->namakaryawan."</td>
						   <td align=center>".tanggalnormal($bar1->tanggalmasuk)."</td>
						   <td align=center>".$periode."</td>				   
						   <td align=center>".tanggalnormal($bar1->dari)."</td>
						   <td align=center>".tanggalnormal($bar1->sampai)."</td>
						   <td align=center>".$bar1->hakcuti."</td>";
						if((@$arrdata[$bar1->karyawanid]['ambil']) > 0){
							
							echo"<td align=center onclick=\"getdetail('".$bar1->karyawanid."','".$periode."',event)\" style='color:blue;cursor:pointer' title='Klik untuk lihat detail'>".$arrdata[$bar1->karyawanid]['ambil']."</td>";
						}else{
							if (@$arrdata[$bar1->karyawanid]['ambil']=='') {
								@$arrdata[$bar1->karyawanid]['ambil']=0;
							}
							echo"<td align=center onclick=\"getdetail('".$bar1->karyawanid."','".$periode."',event)\" style='color:blue;cursor:pointer' title='Klik untuk lihat detail'>".$arrdata[$bar1->karyawanid]['ambil']."</td>";
						}
				echo "<td align=center>".($sisa)."</td>";
				echo "</tr>";
			}	 
			echo"	 
				 </tbody>
				 <tfoot>
				 </tfoot>
				 </table>";
		}
	break;
	
	case 'loadkaryawan':
		$hariini = date("Y-m-d");
		$optkaryawan="";
		$whr='';
		if($kodeorg!=''){
			$whr=" and lokasitugas='".$kodeorg."'";
		}
		$dakarbulanan=0;
		$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' ".$whr." and periodegaji like '".$periode."%'"; 
		$res = fetchdata($str);
		if(count($res)>0)
		{ 
		$dakarbulanan=1;
		}
		// in(0,1,2,3,7,6,8) 
		if($dakarbulanan==0){
			$str="select nik,namakaryawan, karyawanid from ".$dbname.".datakaryawan where tipekaryawan!='4' ".$whr." and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by namakaryawan";
		}else{
			$str="select distinct(karyawanid) from ".$dbname.".datakaryawan_hist where tipekaryawan!='4' ".$whr." and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') and periodegaji like '".$periode."%' order by namakaryawan";
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$optkaryawan.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($bar=$res->fetch())
		{
			$niknya = makeOption($dbname, 'datakaryawan', 'karyawanid,nik',"karyawanid='".$bar->karyawanid."'");
			$nmnya = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar->karyawanid."'");
			$optkaryawan.="<option value='".$bar->karyawanid."'>".$niknya[$bar->karyawanid]." - ".$nmnya[$bar->karyawanid]."</option>";
		}
		echo $optkaryawan;
	break;
	
	case'getdetail':
		$karyawanid = checkPostGet('karyawanid','');
		$periode = checkPostGet('periode','');
		$arrdata=array();
		$nox=0;
		$opttrans="";
		
		## AMBIL TIPEKARYAWAN
		$qry ="select a.tipekaryawan,b.tipe from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id where a.karyawanid='".$karyawanid."' ";
		$rslt = fetchdata($qry);
		$tipekaryawan = $rslt[0]['tipe'];

		## CEK STAFF
		$str="select * from ".$dbname.".sdm_ijin where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and statuspersetujuan in('1','2') order by darijam desc";
		$res=fetchdata($str);
		foreach($res as $val){
			$nox++;
			$arrdata[$val['notransaksi']]['notransaksi']=$val['notransaksi'];
			$arrdata[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$arrdata[$val['notransaksi']]['keperluan']=$val['keperluan'];
			$arrdata[$val['notransaksi']]['jenisijin']=$val['jenisijin'];
			$arrdata[$val['notransaksi']]['darijam']=$val['darijam'];
			$arrdata[$val['notransaksi']]['sampaijam']=$val['sampaijam'];
			if($nox==1){
				$opttrans .= "'".$val['notransaksi']."'";
			}else{
				$opttrans .= ",'".$val['notransaksi']."'";
			}
		}

		
		## CEK NON STAFF
		$str="select * from ".$dbname.".sdm_ijinnonstaff where karyawanid='".$karyawanid."' and periodecuti='".$periode."' order by darijam desc"; 
		$res=fetchdata($str);
		foreach($res as $val){
			$nox++;
			$arrdata[$val['notransaksi']]['notransaksi']=$val['notransaksi'];
			$arrdata[$val['notransaksi']]['tanggal']=$val['tanggal'];
			$arrdata[$val['notransaksi']]['keperluan']=$val['keperluan'];
			$arrdata[$val['notransaksi']]['jenisijin']=$val['jenisijin'];
			$arrdata[$val['notransaksi']]['darijam']=$val['darijam'];
			$arrdata[$val['notransaksi']]['sampaijam']=$val['sampaijam'];
			$arrdata[$val['notransaksi']]['idjenis']=$val['idjenis'];
			// $arrdata[$val['karyawanid']]['ambil']=$val['jumlahhari'];
			if($nox==1){
				$opttrans .= "'".$val['notransaksi']."'";
			}else{
				$opttrans .= ",'".$val['notransaksi']."'";
			}
		}
		// var_dump($arrdata); exit();


		## APPROVAL
		$arrStatus = array('0'=>'Waiting','1'=>'Disetujui','2'=>'Dikoreksi','3'=>'Ditolak');
		$arrlevel=array();
		$arrapp=array();
		if ($opttrans=='') {
				$str="select * from ".$dbname.".approval where notransaksi in ('".$opttrans."')";
		}else{
			$str="select * from ".$dbname.".approval where notransaksi in (".$opttrans.")";
		}
		$res=fetchdata($str);
		foreach($res as $val){
			$arrlevel[$val['level']]=$val['level'];
			$arrapp[$val['notransaksi']][$val['level']]['nama']=getNamaKaryawan($val['karyawanid']);
			$arrapp[$val['notransaksi']][$val['level']]['status']=$arrStatus[$val['status']];
		}
		
		$arrjeniscuti = makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin');

		if(count($arrdata) > 0){
			$tab.="<table cellspacing='1' border='0' class='sortable'>
			<thead>
			<tr class='rowheader'>
				<td align=center>No.</td>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['keperluan']."</td>
				<td align=center>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['tahun']."</td>
				<td align=center>".$_SESSION['lang']['jenisijin']."</td>";
				
				foreach($arrlevel as $key){
					$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$key."</td>";
					$tab.="<td align=center>".$_SESSION['lang']['approval_status']." ".$key."</td>";
				}
			$tab.="<td align=center>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']."</td>
				<td align=center>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']."</td>
				<td align=center>Action</td>
			</tr>
			</thead>
			<tbody>";
			$no=0;
			foreach($arrdata as $key=>$val){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".$val['notransaksi']."</td>";
				$tab.="<td align=left>".getNamaKaryawan($karyawanid)."</td>";
				$tab.="<td style='text-align:center;min-width:70px'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td align=left>".$val['keperluan']."</td>";
				$tab.="<td align=center>".$periode."</td>";
				$tab.="<td align=left>".$arrjeniscuti[$val['idjenis']]."</td>";
				foreach($arrlevel as $key2){
					$tab.="<td align=center>".$arrapp[$key][$key2]['nama']."</td>";
					$tab.="<td align=center>".$arrapp[$key][$key2]['status']."</td>";
				}
				$tab.="<td style='text-align:center;min-width:70px'>".tanggalnormald($val['darijam'])."</td>";
				$tab.="<td style='text-align:center;min-width:70px'>".tanggalnormald($val['sampaijam'])."</td>";
				$tab.="<td align=center>
					<img src='images/pdf.jpg' class='resicon' title='Print' onclick=\"previewPdf('".tanggalnormal($val['tanggal'])."','".$karyawanid."','".$tipekaryawan."',event)\">
				</td>";
				
				$tab.="</tr>";
			}

			$tab.="</tbody>
			</table>";
		}else{
			$tab.="<label stlye='color:red'>".$_SESSION['lang']['errdatanotexist']."</label>";
		}
		echo $tab;
	break;
	
	default:
	break;
}
?>