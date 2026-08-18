<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
?>

<?php

$method=checkPostGet('method','');
$pages = checkPostGet('page','');

$nosj=checkPostGet('nosj','');
$unit=checkPostGet('unit','');
$tanggal=checkPostGet('tanggal','');
$tanggalkirim=checkPostGet('tanggalkirim','');
$expeditor=checkPostGet('expeditor','');
$nopol=checkPostGet('nopol','');
$jeniskedaraan=checkPostGet('jeniskedaraan','');
$supir=checkPostGet('supir','');
$hpsupir=checkPostGet('hpsupir','');
$pengirim=checkPostGet('pengirim','');
$cek=checkPostGet('cek','');
$gudangtujuan=checkPostGet('gudangtujuan','');
$transportasi=checkPostGet('transportasi','');


$nopodt=checkPostGet('nopodt','');
$kodebarang=checkPostGet('kodebarang','');
$jenis=checkPostGet('jenis','');
$jumlah=checkPostGet('jumlah','');
$nopo=checkPostGet('nopo','');
$nopp=checkPostGet('nopp','');
$satuan=checkPostGet('satuan','');
$noref=checkPostGet('noref','');

##SEARCH
$srcnosj=checkPostGet('srcnosj','');
$srcnopl=checkPostGet('srcnopl','');
$srcnopp=checkPostGet('srcnopp','');
$srcnopo=checkPostGet('srcnopo','');

switch($method){
	case'loaddata':
		$arrorgdet = getOrgDetail(2);
		$where = "1=1";
		
		if($srcnosj!=''){
			$where.=" and nosj like '%".$srcnosj."%'";
		}
		if($srcnopl!=''){
			$where.=" and nosj in (select nosj from ".$dbname.".log_suratjalandt where kodebarang like '%".$srcnopl."%')";
		}
		if($srcnopp!=''){
			$where.=" and nosj in (select nosj from ".$dbname.".log_suratjalandt where nopp like '%".$srcnopp."%')";
		}
		if($srcnopo!=''){
			$where.=" and nosj in (select nosj from ".$dbname.".log_suratjalandt where nopo like '%".$srcnopo."%')";
		}
	
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$no=(($page*$limit));
		
		$str="select nosj from ".$dbname.".log_suratjalanht where ".$where." and kodeorg in (".$arrorgdet.")";
		$res=fetchdata($str);
		$jlhbrs = count($res);
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='11' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{			
			$str="select * from ".$dbname.".log_suratjalanht where ".$where." and kodeorg in (".$arrorgdet.") order by tanggal desc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				$optnmpost = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['postingby']."'");
				$optnmgdg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['franco']."'");
				
				## DETAIL
				$arrpl=array();
				$arrpo=array();
				$arrpp=array();
				$strx="select kodebarang,nopo,nopp from ".$dbname.".log_suratjalandt where nosj='".$val['nosj']."'";
				$resx=fetchdata($strx);
				foreach($resx as $valx){
					if(substr($valx['kodebarang'],0,2)=='PL'){
						$arrpl[$valx['kodebarang']] = $valx['kodebarang'];
					}
					if($valx['nopp']!=''){
						$arrpp[$valx['nopp']]=$valx['nopp'];
					}
					if($valx['nopo']!=''){
						$arrpo[$valx['nopo']]=$valx['nopo'];
					}
				}
				
				$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center id='nosj_".($no)."' value='".$val['nosj']."'>".$val['nosj']."</td>
					<td align=center id='kodept_".($no)."'  value='".$val['kodept']."'>".$val['kodept']."</td>
					<td align=center style='min-width:80px'>".tanggalnormal($val['tanggal'])."</td>
					<td align=center style='min-width:80px'>".tanggalnormal($val['tanggalkirim'])."</td>
					<td align=center>".$optnmpost[$val['postingby']]."</td>
					<td align=center>".$optnmgdg[$val['franco']]."</td>";
					// <td align=center>";
					// $nox=0;
					// foreach($arrpl as $valx){
						// if($nox==0){
							// $tab.=$valx;
						// }else{
							// $tab.="<br>".$valx;
						// }
						// $nox++;
					// }
					// $tab.="</td>
					$tab.="<td align=center>";
					$nox=0;
					foreach($arrpo as $valx){
						if($nox==0){
							$tab.=$valx;
						}else{
							$tab.="<br>".$valx;
						}
						$nox++;
					}
					$tab.="</td>
					<td align=center>";
					$nox=0;
					foreach($arrpp as $valx){
						if($nox==0){
							$tab.=$valx;
						}else{
							$tab.="<br>".$valx;
						}
						$nox++;
					}
					$tab.="</td>";
					
					if($val['posting']=='0'){
						$tab.="<td align=right nowrap>
							<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$val['nosj']."','".$val['kodeorg']."','".tanggalnormal($val['tanggal'])."','".tanggalnormal($val['tanggalkirim'])."','".$val['expeditor']."','".$val['nopol']."','".$val['jeniskend']."','".$val['driver']."','".$val['hpdriver']."','".$val['pengirim']."','".$val['checkedby']."','".$val['franco']."','".$val['transportasi']."');\" >
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteData('".$val['nosj']."');\" >
							<img src='images/skyblue/posting.png' class='zImgBtn' onclick=\"postingData('".$val['nosj']."')\" title='Release'>
							<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"detailPDF('".($no)."',event);\">
						</td>";
					}else{
						$tab.="<td align=right nowrap>
							<img src='images/skyblue/posted.png' class='zImgOffBtn' title='Release'>
							<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"detailPDF('".($no)."',event);\">
						</td>";
					}
					
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,'11','loaddata','getPage');
			$tab.="</table>";
		}
			
		echo $tab;
	break;
	
	case'posting':
		$str="select count(nosj) as countitem from ".$dbname.".log_suratjalandt where nosj='".$nosj."'";
		$res=fetchdata($str);
		$countitem = $res[0]['countitem'];
		
		if($countitem <= 0){
			exit("Gagal, Belum ada detail untuk No. Surat Jalan ".$nosj);
		}
	
		$str="update ".$dbname.".log_suratjalanht set posting='1', postingby='".$_SESSION['standard']['userid']."' where nosj='".$nosj."'";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case'delete':
		$str="delete from ".$dbname.".log_suratjalandt where nosj='".$nosj."' and nopo='".$nopo."' and nopp='".$nopp."' and notransaksireferensi='".$noref."'";
		try{
			$owlPDO->exec($str);
			
			
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	
		$str="select * from ".$dbname.".log_suratjalandt where nosj='".$nosj."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['jenis']=='PO'){
				$strx="select sum(jumlah) as jumlah from ".$dbname.".log_suratjalandt where kodebarang='".$val['kodebarang']."' and nopp='".$val['nopp']."' and nopo='".$val['nopo']."'";
				$resx=fetchdata($strx);
				$jlh=$resx[0]['jumlah'];
				
				$strx="select notransaksi,nopo,nopp,kodebarang,qty,pl,sj from ".$dbname.".log_transit where kodebarang='".$val['kodebarang']."' and nopp='".$val['nopp']."' and nopo='".$val['nopo']."'";
				$resx=fetchdata($strx);
				$tempqty = $jlh -  $val['jumlah'];
				foreach($resx as $valx){
					if($tempqty > $valx['qty']){
						$hslqty = $valx['qty'];
						$tempqty = $tempqty - $valx['qty'];
					}else{
						$hslqty = $tempqty;
						$tempqty = 0;
					}
					
					$str="update ".$dbname.".log_transit set sj='".$hslqty."' where notransaksi='".$valx['notransaksi']."' and nopo='".$valx['nopo']."' and nopp='".$valx['nopp']."' and kodebarang='".$valx['kodebarang']."'";
					try{
						$owlPDO->exec($str);
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				
				// $str="update ".$dbname.".log_transit set sj=(sj-'".$val['jumlah']."') where nopo='".$val['nopo']."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."' and notransaksi='".$val['notransaksireferensi']."'";
				// try{
					// $owlPDO->exec($str); 
				// }catch(PDOException $e){
					// print " Gagal  !: " . $e->getMessage() . "\n"; 
					// die(); 
				// }
			}
		}
		
		$str="delete from ".$dbname.".log_suratjalandt where nosj='".$nosj."'";
		try{
			$owlPDO->exec($str); 
			
			$str="delete from ".$dbname.".log_suratjalanht where nosj='".$nosj."'";
			try{
				$owlPDO->exec($str); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case'simpan':
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
		if($nosj==''){
			$nosj="SJ".date('Ymdhis');
			$str="insert into ".$dbname.".log_suratjalanht (nosj,kodept,kodeorg,tanggal,tanggalkirim,expeditor,nopol,jeniskend,driver,hpdriver,pengirim,penerima,checkedby,franco,transportasi) values ('".$nosj."','".$optpt[$unit]."','".$unit."','".tanggalsystem($tanggal)."','".tanggalsystem($tanggalkirim)."','".$expeditor."','".$nopol."','".$jeniskedaraan."','".$supir."','".$hpsupir."','".$pengirim."','Diterima Oleh','".$cek."','".$gudangtujuan."','".$transportasi."')";
			try{
				$owlPDO->exec($str); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}else{
			$str="update ".$dbname.".log_suratjalanht set tanggal='".tanggalsystem($tanggal)."', tanggalkirim='".tanggalsystem($tanggalkirim)."', expeditor='".$expeditor."', nopol='".$nopol."', jeniskend='".$jeniskedaraan."', driver='".$supir."', hpdriver='".$hpsupir."', pengirim='".$pengirim."', checkedby='".$cek."', franco='".$gudangtujuan."', transportasi='".$transportasi."' where nosj='".$nosj."'";
			try{
				$owlPDO->exec($str); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		echo $nosj;
	break;
	
	case'showPO':
		$tab="";
		
		$tab.="<fieldset>
			No. PO : <input class='myinputtext' id='nopodt' onkeypress=\"key=getKey(event);if(key==13){findPO()}\">
			<button class='mybutton' onclick=\"findPO()\">Cari</button>
		</fieldset>
		<div id='hasilCari'></div>";
		
		echo $tab;
	break;
	
	case'showPL':
		$tab="";
		
		$tab.="<fieldset>
			Search : <input class='myinputtext' id='nopodt' onkeypress=\"key=getKey(event);if(key==13){findPO()}\">
			<button class='mybutton' onclick=\"findPL()\">Cari</button>
		</fieldset>
		<div id='hasilCari'></div>";
		
		echo $tab;
	break;
	
	case'findPO':
		$tab="";
		$tab.="<fieldset>
			<legend><i>Result</i></legend>
			<button class='mybutton' onclick=\"add2detail('po')\" >Add to Detail</button>
			<div style='max-height:340px;overflow:auto'>
			<table cellpadding=2 cellspacing=1 border=0 class='sortable'>
				<thead>
				<tr class=rowheader>
					<td align='center'></td>
					<td align='center'>".$_SESSION['lang']['nopo']."</td>
					<td align='center'>".$_SESSION['lang']['kodebarang']."</td>
					<td align='center'>".$_SESSION['lang']['namabarang']."</td>
					<td align='center'>".$_SESSION['lang']['jumlah']."</td>
					<td align='center'>".$_SESSION['lang']['nopp']."</td>
					<td align='center'>".$_SESSION['lang']['satuan']."</td>
				</tr>
				</thead>
				<tbody id=bodySearch>";
			
			$where = "";
			if(!empty($nopodt)){
				$where .= " and nopo like '%".$nopodt."%'";
			}
			if(isset($unit)){
				$where .= " and unit = '".$unit."'";
			}
			
			$str="select sum(qty) as qty, sum(pl) as pl, sum(sj) as sj, nopo, nopp, kodebarang, satuan from ".$dbname.".log_transit where status='0' and statusterima='0' and posting='1' ".$where." group by nopp,nopo,kodebarang";
			$res=fetchdata($str);
			$no=0;
			foreach($res as $val){
				$saldo=0;
				$saldo = $val['qty'] - $val['sj'] - $val['pl'];
				// if($val['pl']=='0' and $val['sj']=='0'){
					// $saldo=$val['qty'];
				// }else{
					// if($val['pl'] > 0){
						// $saldo=($val['qty']-$val['pl']);
					// }else{
						// $saldo=($val['qty']-$val['sj']);
					// }
				// }
				if($saldo > 0){
					$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
					$optnmsatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$val['kodebarang']."'");
					
					$tab.="<tr class='rowcontent'>
						<td align='center'>".makeElement('po_'.$no,'checkbox',0)."</td>
						<td id='nopo_".$no."'>".$val['nopo']."</td>
						<td id='kodebarang_".$no."' style='text-align:center'>".$val['kodebarang']."</td>
						<td id='namabarang_".$no."'>".$optnmbarang[$val['kodebarang']]."</td>
						<td id='jumlah_".$no."' style='text-align:right'>".hidezerodecimal($saldo,2)."</td>
						<td id='nopp_".$no."'>".$val['nopp']."</td>
						<td id='satuan_".$no."'>".$val['satuan']."</td>
						<input type='hidden' id='noref_".$no."' value='".$val['notransaksi']."'>
					</tr>";
					$no++;
				}
			}
				
			$tab.="</tbody>
		</fieldset>";
		
		echo $tab;
	break;
	
	case'findPL':
		$tab="";
		$tab.="<fieldset>
			<legend><i>Result</i></legend>
			<button class='mybutton' onclick=\"add2detail('pl')\" >Add to Detail</button>
			<div style='max-height:340px;overflow:auto'>
			<table cellpadding=2 cellspacing=1 border=0 class='sortable'>
				<thead>
				<tr class=rowheader>
					<td align='center'></td>
					<td align='center'>No. Packing List</td>
				</tr>
				</thead>
				<tbody id=bodySearch>";
			
			$where = "";
			if(!empty($nopodt)){
				$where .= " and notransaksi like '%".$nopodt."%'";
			}
			if(isset($unit)){
				$where .= " and kodeorg = '".$unit."'";
			}
			
			$str="select notransaksi from ".$dbname.".log_packinght where posting='1' ".$where."";
			$res=fetchdata($str);
			$no=0;
			foreach($res as $val){
				$strx="select count(kodebarang) as countitem from ".$dbname.".log_suratjalandt where kodebarang='".$val['notransaksi']."'";
				$resx=fetchdata($strx);
				$saldo = $resx[0]['countitem'];
				
				if($saldo <= 0){
					$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
					$optnmsatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$val['kodebarang']."'");
					
					$tab.="<tr class='rowcontent'>
						<td align='center'>".makeElement('pl_'.$no,'checkbox',0)."</td>
						<td id='notransaksi_".$no."'>".$val['notransaksi']."</td>
					</tr>";
					$no++;
				}
			}
				
			$tab.="</tbody>
		</fieldset>";
		
		echo $tab;
	break;
	
	case'add2detail':
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
		$data = array();
		
		if(is_array($_POST['data'])){
			foreach($_POST['data'] as $key=>$val){
				$qtyitem = str_replace(",","",$val['jumlah']);
				if($jenis=='po'){
					$str="insert into ".$dbname.".log_suratjalandt (nosj,kodept,kodebarang,jenis,jumlah,satuanpo,nopo,nopp,notransaksireferensi) values ('".$nosj."','".$optpt[$unit]."','".$val['kodebarang']."','".$jenis."','".$qtyitem."','".$val['satuan']."','".$val['nopo']."','".$val['nopp']."','".$val['noref']."')";
					try{
						$owlPDO->exec($str);
						
						$strx="select sum(jumlah) as jumlah from ".$dbname.".log_suratjalandt where kodebarang='".$val['kodebarang']."' and nopp='".$val['nopp']."' and nopo='".$val['nopo']."'";
						$resx=fetchdata($strx);
						$tempqty=$resx[0]['jumlah'];
						
						$strx="select notransaksi,nopo,nopp,kodebarang,qty,pl,sj from ".$dbname.".log_transit where kodebarang='".$val['kodebarang']."' and nopo='".$val['nopo']."' and nopp='".$val['nopp']."'";
						$resx=fetchdata($strx);
						foreach($resx as $valx){
							if($tempqty > $valx['qty']){
								$hslqty = $valx['qty'];
								$tempqty = $tempqty - $valx['qty'];
							}else{
								$hslqty = $tempqty;
								$tempqty = 0;
							}
							
							$str="update ".$dbname.".log_transit set sj='".$hslqty."' where notransaksi='".$valx['notransaksi']."' and nopo='".$valx['nopo']."' and nopp='".$valx['nopp']."' and kodebarang='".$valx['kodebarang']."'";
							try{
								$owlPDO->exec($str);
							}catch(PDOException $e){
								print " Gagal  !: " . $e->getMessage() . "\n"; 
								die(); 
							}
						}
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				
				if($jenis=='pl'){
					$str="insert into ".$dbname.".log_suratjalandt (nosj,kodept,kodebarang,jenis,jumlah,satuanpo) values ('".$nosj."','".$optpt[$unit]."','".$val['kodebarang']."','PL','1','PETI')";
					try{
						$owlPDO->exec($str);
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
			}
		}
	break;
	
	case'showDetail':
		$tab="";
		
		$str="select * from ".$dbname.".log_suratjalandt where nosj='".$nosj."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$no=0;
			foreach($res as $val){
				$no++;
				$nmBarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$val['jenis']."</td>
					<td style='text-align:center'>".$val['kodebarang']."</td>
					<td style='text-align:left'>".$nmBarang[$val['kodebarang']]."</td>
					<td style='text-align:center'>".$val['nopo']."</td>
					<td style='text-align:center'>".$val['nopp']."</td>
					<td style='text-align:right'>
						<input type='hidden' id='valpar_".$no."' value='".$nosj."#####".$val['nopo']."#####".$val['nopp']."#####".$val['kodebarang']."#####".$val['jumlah']."#####".$val['notransaksireferensi']."'>
						<input type=text id='jlh_".$no."' value='".hidezerodecimal($val['jumlah'],2)."' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('jlh_".$no."',2)\" onblur=\"changejumlah('".$no."')\" style='width:75px' />
					</td>
					<td style='text-align:left'>".$val['satuanpo']."</td>
					<td style='text-align:center'>
						<img src='images/delete_32.png' class='zImgBtn' onclick=\"deleteDetail('".$nosj."','".$val['nopo']."','".$val['nopp']."','".$val['kodebarang']."','".$val['jumlah']."','".$val['notransaksireferensi']."')\" style='cursor:pointer'>
					</td>
				</tr>";
			}
		}else{
			$tab.="<tr class=rowcontent>
				<td colspan=9 align=center>".$_SESSION['lang']['errdatanotexist']."</td>
			</tr>";
		}
		
		echo $tab;
	break;
	
	case'changejumlah':
		$valpar=checkPostGet('valpar','');
		$jlh=str_replace(',','',checkPostGet('jlh',''));
		$expvalpar = explode('#####',$valpar);
		$jlhawal = $expvalpar[4];
		
		## Get Jumlah max
		$str="select sum(qty) as qty from ".$dbname.".log_transit where kodebarang='".$expvalpar[3]."' and nopp='".$expvalpar[2]."' and nopo='".$expvalpar[1]."'";
		$res=fetchdata($str);
		$jlhmax = $res[0]['qty'];
		
		$str="select sum(jumlah) as jumlah from ".$dbname.".log_suratjalandt where kodebarang='".$expvalpar[3]."' and nopp='".$expvalpar[2]."' and nopo='".$expvalpar[1]."'";
		$res=fetchdata($str);
		$jlhsj = $res[0]['jumlah'];
		
		if($jlh > $jlhawal){
			$selisih = $jlh - $jlhawal;
			if(($jlhsj+$selisih) > $jlhmax){
				exit("Gagal, Jumlah sudah melebihi stok yang ada.");
			}else{
				$strx="select notransaksi,nopo,nopp,kodebarang,qty,pl,sj from ".$dbname.".log_transit where kodebarang='".$expvalpar[3]."' and nopp='".$expvalpar[2]."' and nopo='".$expvalpar[1]."'";
				$resx=fetchdata($strx);
				$tempqty = $jlh;
				foreach($resx as $valx){
					$tempqty = $tempqty - $valx['qty'];
					if($tempqty > 0){
						$hslqty = $valx['qty'];
					}else{
						$hslqty = (($tempqty + $valx['qty']) <= 0 ? '0':($tempqty + $valx['qty']));
					}
					
					$str="update ".$dbname.".log_transit set sj='".$hslqty."' where notransaksi='".$valx['notransaksi']."' and nopo='".$valx['nopo']."' and nopp='".$valx['nopp']."' and kodebarang='".$valx['kodebarang']."'";
					try{
						$owlPDO->exec($str);
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				$str="update ".$dbname.".log_suratjalandt set jumlah='".$jlh."' where nosj='".$expvalpar[0]."' and nopo='".$expvalpar[1]."' and nopp='".$expvalpar[2]."' and kodebarang='".$expvalpar[3]."'";
				try{
					$owlPDO->exec($str);
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}else if($jlh < $jlhawal){
			$strx="select notransaksi,nopo,nopp,kodebarang,qty,pl,sj from ".$dbname.".log_transit where kodebarang='".$expvalpar[3]."' and nopp='".$expvalpar[2]."' and nopo='".$expvalpar[1]."'";
			$resx=fetchdata($strx);
			$tempqty = $jlh;
			foreach($resx as $valx){
				$tempqty = $tempqty - $valx['qty'];
				if($tempqty > 0){
					$hslqty = $valx['qty'];
				}else{
					$hslqty = (($tempqty + $valx['qty']) <= 0 ? '0':($tempqty + $valx['qty']));
				}
				
				$str="update ".$dbname.".log_transit set sj='".$hslqty."' where notransaksi='".$valx['notransaksi']."' and nopo='".$valx['nopo']."' and nopp='".$valx['nopp']."' and kodebarang='".$valx['kodebarang']."'";
				try{
					$owlPDO->exec($str);
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			$str="update ".$dbname.".log_suratjalandt set jumlah='".$jlh."' where nosj='".$expvalpar[0]."' and nopo='".$expvalpar[1]."' and nopp='".$expvalpar[2]."' and kodebarang='".$expvalpar[3]."'";
			try{
				$owlPDO->exec($str);				
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}else{
			
		}
		echo $expvalpar[0]."#####".$expvalpar[1]."#####".$expvalpar[2]."#####".$expvalpar[3]."#####".$jlh."#####".$expvalpar[5];
	break;
	
	case'deleteDetail':
		$str="delete from ".$dbname.".log_suratjalandt where kodebarang='".$kodebarang."' and nosj='".$nosj."' and nopo='".$nopo."' and nopp='".$nopp."' and notransaksireferensi='".$noref."'";
		try{
			$owlPDO->exec($str);
			
			$str="select sum(jumlah) as jumlah from ".$dbname.".log_suratjalandt where kodebarang='".$kodebarang."' and nopp='".$nopp."' and nopo='".$nopo."'";
			$res=fetchdata($str);
			$jlh=$res[0]['jumlah'];
			
			$strx="select notransaksi,nopo,nopp,kodebarang,qty,pl,sj from ".$dbname.".log_transit where kodebarang='".$kodebarang."' and nopp='".$nopp."' and nopo='".$nopo."'";
			$resx=fetchdata($strx);
			$tempqty = $jlh;
			foreach($resx as $valx){
				if($tempqty > $valx['qty']){
					$hslqty = $valx['qty'];
					$tempqty = $tempqty - $valx['qty'];
				}else{
					$hslqty = $tempqty;
					$tempqty = 0;
				}
				
				$str="update ".$dbname.".log_transit set sj='".$hslqty."' where notransaksi='".$valx['notransaksi']."' and nopo='".$valx['nopo']."' and nopp='".$valx['nopp']."' and kodebarang='".$valx['kodebarang']."'";
				try{
					$owlPDO->exec($str);
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
}
?>