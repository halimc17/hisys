<?php 
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$kunci               = checkPostGet('kunci', '');
$rotasi              = checkPostGet('rotasi', '');
$satKeg              = checkPostGet('satKeg', '');
$volKeg              = checkPostGet('volKeg', '');
$thnBudget           = checkPostGet('thnBudget', '');
$uphSprvisi          = checkPostGet('uphSprvisi', '');
$proses              = checkPostGet('proses', '');
$jmlhPerson          = checkPostGet('jmlhPerson', '');
$hkEfektif           = checkPostGet('hkEfektif', '');
$totUpah             = checkPostGet('totUpah', '');
$kdBlok              = checkPostGet('kdBlok', '');
$kgtn                = checkPostGet('kgtn', '');
$noakn               = checkPostGet('noakn', '');
$jmlHk               = checkPostGet('jmlHk', '');
$hkSprvisi           = checkPostGet('hkSprvisi', '');
$rpsuperVisi         = checkPostGet('rpsuperVisi', '');
$divisi              = checkPostGet('divisi', '');
$jenis               = checkPostGet('jenis', '');
$alokasi             = checkPostGet('alokasi', '');
$kegiatan            = checkPostGet('kegiatan', '');
$baris               = checkPostGet('baris', '');


$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');

switch($proses){
	case'getHk':
		if($thnBudget==''){
			exit("Error:Tahun Budget Tidak Boleh Kosong");
		}
		
		$sHk="select distinct * from ".$dbname.".bgt_hk where tahunbudget='".$thnBudget."' and unit = '".substr($_SESSION['empl']['lokasitugas'],0,4)."'";
		$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
		$qHk->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($qHk);
		while($bar=$qHk->fetch()){
			$thrlb=$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
			$thke=$bar['harisetahun']-$thrlb;
			$tsim=$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
			$tothke=$thke-($bar['jlhcuti']+$tsim);
			
			$hkEfektif=$tothke;
		}
		
		$sHk="SELECT sum(jumlah) as jumlahhk FROM ".$dbname.".bgt_budget 
			   where tahunbudget='".$thnBudget."' and 
			   kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and kegiatan is not null";
		$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
		$qHk->setFetchMode(PDO::FETCH_ASSOC);
		$rHk = $qHk->fetch();

		if($numrows==0){
			exit("Error : Tahun Budget : ".$thnBudget." Belum Memilik HK Efektif");
		}else{
			#$hkEfektif=$rHk['jumlahhk'];
			echo $hkEfektif;
		}
	break;
	case'getPreview':
		if($thnBudget==''){
		  exit("Error: Tahun Budget Tidak Boleh Kosong");
		}
		
		$where='';
		if($divisi!=''){
			$where=" and a.kodeorg like '".$divisi."%'";
		}else{
			$where=" and a.kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		}
		if($jenis!=''){
			$where.=" and a.kodeorg in (select kodeorg from ".$dbname.".setup_blok where statusblok='".$jenis."')";
		}

		$sCek="SELECT distinct kodeorg,kegiatan FROM ".$dbname.".bgt_budget a  
			   where tahunbudget='".$thnBudget."' and 
			   kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ".$where." and kegiatan is not null";
		$numrows=fetchdata($sCek);
		$rTot=count($numrows);


		$str="select distinct nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='SB'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);            
		while($bar=$res->fetch()){
			$akun[substr($bar->nilai,0,3)]=$bar->nilai;
		}
		
		
		$strx="SELECT sum(jumlah) as ttlhk FROM ".$dbname.".bgt_budget a where tahunbudget='".$thnBudget."' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' ".$where." and kegiatan is not null";
		$resttl=fetchdata($strx);
		
		$sCek2="SELECT distinct a.kodeorg,a.kegiatan,b.namakegiatan,a.noakun FROM " . $dbname . ".bgt_budget  a 
				left join " . $dbname . ".setup_kegiatan b  on a.kegiatan=b.kodekegiatan
			   where tahunbudget='" . $thnBudget . "' and 
			   a.kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' ".$where." and kegiatan is not null";            
		$numrows=count(fetchdata($sCek2));
		$qCek2=$owlPDO->query($sCek2) or die(print " Gagal: ".PDOException::getMessage());
		$qCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=$numrows;
		if($rCek!=0){
			$tnilai=0;$tnilai2=0;
			while($res=$qCek2->fetch()){
				$no+=1;
				$sHk="SELECT sum(jumlah) as jumlahhk FROM ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg = '".$res['kodeorg']."' and kegiatan ='".$res['kegiatan']."'";
				$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
				$qHk->setFetchMode(PDO::FETCH_ASSOC);
				$rHk = $qHk->fetch();
				
				$nilai2=0;
				
				$nilai=($rHk['jumlahhk']/$resttl[0]['ttlhk'])*($jmlhPerson*$hkEfektif);            
				$nilai2=($nilai/($jmlhPerson*$hkEfektif))*$totUpah;                
				$tab.="<tr class=rowcontent id=rew_".$no.">";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center width=50px>".$thnBudget."</td>";
				$tab.="<td id=kdBlok_".$no.">".$res['kodeorg']."</td>";
				$tab.="<td id=keg_".$no.">".$res['kegiatan']."</td>";
				$tab.="<td>".$res['namakegiatan']."</td>";
				
				if ($alokasi==1){
					$tab.="<td align=center id=noakun_".$no.">".$akun[substr($res['kegiatan'],0,3)]."</td>";
					$tab.="<td align=left>".@$nmakun[$akun[substr($res['kegiatan'],0,3)]]."</td>";
					$tab.="<td align=center id=kegiatanalk_".$no.">".$akun[substr($res['kegiatan'],0,3)]."01</td>";
					$tab.="<td align=left>".@$nmkeg[$akun[substr($res['kegiatan'],0,3)]."01"]."</td>";
				}else{
					$tab.="<td align=center id=noakun_".$no.">".$res['noakun']."</td>";
					$tab.="<td align=left>".$nmakun[$res['noakun']]."</td>";
					$tab.="<td align=center id=kegiatanalk_".$no.">".$res['kegiatan']."</td>";
					$tab.="<td align=left>".$nmkeg[$res['kegiatan']]."</td>";
				}
				
				$tab.="<td hidden id=vol_".$no.">1</td>";
				$tab.="<td id=satuan_".$no.">HK</td>";
				$tab.="<td hidden id=rotsi_".$no.">1</td>";
				$tab.="<td align=right  id=jmlhHk_".$no.">".number_format($rHk['jumlahhk'])."</td>";
				$tab.="<td><input type=text style=width:75px id=hkSupervisi_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' value='".$nilai."' /></td>";
				$tab.="<td><input type=text style=width:100px id=superVisi_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' value='".$nilai2."' /></td>";
				
				$tnilai+=$nilai;
				$tnilai2+=$nilai2;
				
				$tab.="</tr>";
				
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=11>T O T A L</td>";
			$tab.="<td align=right>".number_format($tnilai,2)."</td>";
			$tab.="<td align=right>".number_format($tnilai2,2)."</td>";
			$tab.="</tr>";
			
			
			
			$tab.="<input type=hidden id=jmlhRow value='".$no."' /><input type=hidden id=totalHk value='".$rTot."' />";
		}else{
			exit("Error : Data Kosong");
		}
	echo $tab;
	
	
	break;
	case'insertAll':
		 $thn=date("Y");
		if($thnBudget==''){
			exit("Error : Tahun Budet Tidak Boleh Kosong");
		}elseif(strlen($thnBudget)<4){
			exit("Error : Panjang Tahun Kurang");
		}
		
		if(substr($thn,0,1)!=substr($thnBudget,0,1)){
			exit("Error : Format Tahun Salah");
		}
	if($nmkeg[$kegiatan]==''){
		exit("Warning : Kegiatan dengan kode ".$kegiatan." belum ada di menu Setup - Kegiatan, silahkan tambahkan terlebih dahulu !!!");
	}
	
	# kalo ga ada ini, bakalan nimpa blok yang sama...
	$sPrev="select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kdBlok."' and tipebudget='ESTATE' and noakun='".$noakn."' and kegiatan='".$kegiatan."' and kodebudget='SUPERVISI'";
	$qPrev=$owlPDO->query($sPrev) or die(print " Gagal: ".PDOException::getMessage());
	$qPrev->setFetchMode(PDO::FETCH_ASSOC);
	while($rPrev=$qPrev->fetch()){
		$rpsuperVisi+=$rPrev['rupiah']; 
		$hkSprvisi+=$rPrev['jumlah'];
	}
	$where='';
	if($divisi!=''){
		$where=" and kodeorg like '".$divisi."%'";
	}else{
		$where=" and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
	}
	if($jenis!=''){
		$where.=" and kodeorg in (select kodeorg from ".$dbname.".setup_blok where statusblok='".$jenis."')";
	}
	
	# jika baris pertama maka hapus dulu
	if($baris==1){
		$sDel="delete from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' ".$where." and tipebudget='ESTATE' and kodebudget='SUPERVISI'";
		try{$owlPDO->exec($sDel);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	   
	}

	$sHk="SELECT sum(jumlah) as jumlahhk FROM ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg = '".$kdBlok."' and kegiatan ='".$kgtn."' and kodebudget !='SUPERVISI' ";
	$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
	$qHk->setFetchMode(PDO::FETCH_ASSOC);
	$rHk = $qHk->fetch();
	$hkkegblok=$rHk['jumlahhk'];

	$strx="SELECT sum(jumlah) as ttlhk FROM ".$dbname.".bgt_budget a where tahunbudget='".$thnBudget."' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' ".$where." and kodebudget != 'SUPERVISI' and kegiatan is not null";
	$resttl=fetchdata($strx);	
	$hktotal=$resttl[0]['ttlhk'];

	@$hkSprvisi=($hkkegblok/$hktotal)*($jmlhPerson*$hkEfektif);
	@$rpsuperVisi=($hkkegblok/$hktotal)*$totUpah;
	
	#insert
	$sInsert="insert into ".$dbname.".bgt_budget (tahunbudget, kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,  rotasi, updateby, jumlah, satuanj) 
	value ('".$thnBudget."','".$kdBlok."','ESTATE','SUPERVISI','".$kegiatan."','".$noakn."','".$hkSprvisi."','".$satKeg."','".$rpsuperVisi."','".$rotasi."','".$_SESSION['standard']['userid']."','".$hkSprvisi."','hk')";
	try{$owlPDO->exec($sInsert); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n".$sInsert; die(); }


	break;
	case'getPreviewSebaran':
		if($thnBudget==''){
			exit("Error : Tahun Budget Tidak Boleh Kosong");
		}
		$thn=date("Y");
		if(strlen($thnBudget)<4){
			exit("Error : Panjang Tahun Kurang");
		}
		if(substr($thn,0,1)!=substr($thnBudget,0,1)){
			exit("Error : Format Tahun Salah");
		}
		
		$where='';
		$wherex='';
		if($divisi!=''){
			$where=" and kodeorg like '".$divisi."%'";
			$wherex=" and kodeblok like '".$divisi."%'";
		}else{
			$where=" and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		}
		if($jenis=='PNN'){
			$where.=" and noakun like '611%'";
		}elseif($jenis=='TM'){
			$where.=" and noakun like '621%'";
		}elseif($jenis!=''){
			$where.=" and kodeorg in (select kodeorg from ".$dbname.".setup_blok where statusblok='".$jenis."')";
		}
		
		$sList="select * from ".$dbname.".bgt_budget where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and kodebudget='SUPERVISI' and tahunbudget='".$thnBudget."' ".$where."";
		$jlh = count(fetchdata($sList));
		if($jlh<1){
			exit("Error : Data Kosong");
		}
		$tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>
				<tr class=rowheader>";
		foreach($arrBln as $brsBulan =>$listBln){
			$tab.="<td align=center>".$listBln."</td>";
		}
		$tab.="<td align=center>Action</td>";
		$tab.="</tr></thead><tr class=rowcontent>";
		
		$str="select sum(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12) as totaljjg, sum(jjg01) as jjg01,sum(jjg02) as jjg02, sum(jjg03) as jjg03, 
		sum(jjg04) as jjg04,sum(jjg05) as jjg05,sum(jjg06) as jjg06,sum(jjg07) as jjg07,sum(jjg08) as jjg08, 
		sum(jjg09) as jjg09,sum(jjg10) as jjg10,sum(jjg11) as jjg11,sum(jjg12) as jjg12
		from ".$dbname.".bgt_produksi_kebun where kodeunit like '".$_SESSION['empl']['lokasitugas']."%' and tahunbudget='".$thnBudget."' ".$wherex."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dataprd['1']=$bar['jjg01'];$dataprd['2']=$bar['jjg02'];$dataprd['3']=$bar['jjg03'];$dataprd['4']=$bar['jjg04'];
			$dataprd['5']=$bar['jjg05'];$dataprd['6']=$bar['jjg06'];$dataprd['7']=$bar['jjg07'];$dataprd['8']=$bar['jjg08'];
			$dataprd['9']=$bar['jjg09'];$dataprd['10']=$bar['jjg10'];$dataprd['11']=$bar['jjg11'];$dataprd['12']=$bar['jjg12'];
			$ttljjg=$bar['totaljjg'];
		}

		foreach($arrBln as $brsBulanw =>$listBlnw){
			if($jenis=='PNN'){
				$isi=number_format(($dataprd[$brsBulanw]/$ttljjg)*100,2);
			}else{
				$isi=number_format(100/12,2);
			}
			$tab.="<td><input type=text class=myinputtextnumber onkeyup=sebarulang(".$jlh.") size=3 onkeypress=\"return angka_doang(event);\" id=ss".$brsBulanw." value=".$isi."></td>";
		}
		$tab.="<td align=center><img src=images/clear.png class='resicon' style='position:relative;top:2px;' onclick=bersihkanDonk(".$jlh.") style='height:30px;cursor:pointer' title='bersihkan'></td></tr></table>";
		
		$tab.="<hr>";
		
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
		<thead>
		<tr class=rowheader>
		<td></td>
		<td align=center>No</td>
		<td align=center>".$_SESSION['lang']['index']."</td>
		<td align=center>".$_SESSION['lang']['kodeorg']."</td>
		<td align=center>".$_SESSION['lang']['kodekegiatan']."</td>
		<td align=center>".$_SESSION['lang']['rp']."</td>
		<td align=center>".$_SESSION['lang']['volume']."</td>
		";
		foreach($arrBln as $brsBulanw => $listBln){
			$tab.="<td align=center>".$listBln."</td>";
		}
		$tab.=" </tr>
		</thead><tbody>";
		$tab.="<tr><td colspan=19>";
		$tab.="<button class=mybutton id=save_kepala name=save_kepala onclick=saveSebaran(1) >".$_SESSION['lang']['saveall']."</button>";
		$tab.="<button class=mybutton id=lnjutSebaran name=lnjutSebaran onclick=reSave() style=display:none;>".$_SESSION['lang']['lanjut']."</button>";
		$tab.="</td></tr>";
		
		
		$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
		$qList->setFetchMode(PDO::FETCH_ASSOC);
		while($rList=$qList->fetch()){
			$no+=1;
			$add=" onclick=\"clearForm(".$no.")\" style='cursor:pointer;' title='Kosongkan Isi ".$rList['kunci']."'";
			$tab.="<tr class=rowcontent id=rewBr_".$no.">";
			$tab.="<td><input type=checkbox onclick=sebarkanBoo('".$rList['kunci']."',".$no.",this,".$rList['rupiah'].",".$rList['volume']."); title='Sebarkan sesuai proporsi diatas'></td>";
			$tab.="<td align=center ".$add.">".$no."</td>";
			$tab.="<td align=center ".$add." id=key_".$no.">".$rList['kunci']."</td>";
			$tab.="<td ".$add.">".$rList['kodeorg']."</td>";
			$tab.="<td ".$add.">".$rList['kegiatan']."</td>";
			$tab.="<td align=right ".$add."  id=hrg_".$no.">".hidezerodecimal($rList['rupiah'],2)."</td>";
			$tab.="<td align=right ".$add."  id=vol_".$no.">".$rList['volume']."</td>";
			$arr=0;
			foreach($arrBln as $brsBulanw => $listBln){
				if($jenis=='PNN'){
					$isi=number_format(($dataprd[$brsBulanw]/$ttljjg)*100,2);
				}else{
					$isi=number_format(100/12,2);
				}
				$sbrng=($rList['rupiah']*$isi)/100;
			   $arr+=1;
			   $tab.="<td><input type='text' id='sbrn_".$arr."_".$no."'  onkeyup=\"z.numberFormat('sbrn_".$arr."_".$no."')\" value='".hidezerodecimal($sbrng,2)."' class='myinputtextnumber' style='width:75px' onkeypress='return tanpa_kutip(event)'  /></td>";
			}
			$tab.="</tr>";
			$arr=0;
		}
		$tab.="</tbody></table></fieldset><input type=hidden id=jmlhRow value=".$no." />";
		echo $tab;
	break; 
	case'insertAllData':
		$sCek="select distinct kunci from ".$dbname.".bgt_distribusi where kunci='".$kunci."'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($qCek);
		$rCek=$numrows;
		if($rCek!=1){   
			$sInsert="insert into ".$dbname.".bgt_distribusi (kunci, updateby,rp01, rp02, rp03, rp04, rp05, rp06, rp07, rp08, rp09, rp10, rp11, rp12) 
			value ('".$kunci."','".$_SESSION['standard']['userid']."'";
			for($arb=1;$arb<=12;$arb++){
				$sInsert.=",'".$_POST['arrBrt'][$arb]."'";
			}
			$sInsert.=")";
			try{$owlPDO->exec($sInsert); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	   }else{
			$sDel="delete from ".$dbname.".bgt_distribusi where kunci='".$kunci."'";
			try{
				$owlPDO->exec($sDel); 
				$sInsert="insert into ".$dbname.".bgt_distribusi (kunci, updateby,rp01, rp02, rp03, rp04, rp05, rp06, rp07, rp08, rp09, rp10, rp11, rp12) 
				value ('".$kunci."','".$_SESSION['standard']['userid']."'";
				for($arb=1;$arb<=12;$arb++){
					$sInsert.=",'".$_POST['arrBrt'][$arb]."'";
				}
				$sInsert.=")";
				try{$owlPDO->exec($sInsert); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }              
		}
	break;
	case'deleteAll':
		if($thnBudget==''){
			exit("Error: Tahun Budget Tidak Boleh Kosong");
		}
		$thn=date("Y");
		if(strlen($thnBudget)<4){
			exit("Error:Panjang Tahun Kurang");
		}
		if(substr($thn,0,1)!=substr($thnBudget,0,1)){
			exit("Error:Format Tahun Salah");
		}
		
		$where='';
		if($divisi!=''){
			$where=" and kodeorg like '".$divisi."%'";
		}else{
			$where=" and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		}
		if($jenis=='PNN'){
			$where.=" and noakun like '611%'";
		}elseif($jenis=='TM'){
			$where.=" and noakun like '621%'";
		}elseif($jenis!=''){
			$where.=" and kodeorg in (select kodeorg from ".$dbname.".setup_blok where statusblok='".$jenis."')";
		}
		
		
		$sCek="select distinct tahunbudget from ".$dbname.".bgt_budget where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and kodebudget='SUPERVISI' and tahunbudget='".$thnBudget."' ".$where."";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($qCek);
		$rCek=$numrows;
		if($rCek!=0){
			$SDelbudget="delete from ".$dbname.".bgt_budget where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and kodebudget='SUPERVISI' and tahunbudget='".$thnBudget."' ".$where."";
			try{$owlPDO->exec($SDelbudget); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}else{
			exit("Error: Data Kosong");
		}
	break;
	default:
	break;
}
?>