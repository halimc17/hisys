<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');

$idtahapan=checkPostGet('idtahapan', '');
$kdpab=checkPostGet('kdpab', '');
$nmpab=checkPostGet('nmpab', '');
$kdkel=checkPostGet('kdkel', '');
$tgl1=tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2=tanggalsystemn(checkPostGet('tgl2', ''));
$kdso=checkPostGet('kdso', '');
$stat=checkPostGet('stat', '');
$ketdt=checkPostGet('ketdt', '');

$tgldt1=tanggalsystemn(checkPostGet('tgldt1', ''));
$tgldt2=tanggalsystemn(checkPostGet('tgldt2', ''));
$tahapan=checkPostGet('tahapan', '');

$schkdpab=checkPostGet('schkdpab', '');
$schnmpab=checkPostGet('schnmpab', '');
$schkdkel=checkPostGet('schkdkel', '');
$schkdso=checkPostGet('schkdso', '');

$schstat=checkPostGet('schstat', '');

$jum=checkPostGet('jum', '');
$kdbrg=checkPostGet('kdbrg', '');

$nmkel=makeOption($dbname,'pabrikasi_5kelompok','kode,nama');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

$arrst=array("0"=>"Waiting","1"=>"Open","2"=>"Cancel","3"=>"Close","4"=>"Pending");

switch ($method) {
	
	case'deletedetailbarang':
		$str="delete from ".$dbname.".pabrikasi_5mastermaterial where kodepabrikasi='".$kdpab."' and kodesalesorder='".$kdso."' and kodebarang='".$kdbrg."'";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'savedetailbarang':
		$str="INSERT INTO ".$dbname.".`pabrikasi_5mastermaterial` 
			(`kodepabrikasi`,`kodesalesorder`, `kodebarang`,`targetproduksi`) 
			VALUES ('".$kdpab."','".$kdso."', '".$kdbrg."','".$jum."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'listdetailbarang':
		$tab="<fieldset><legend><b>".$_SESSION['lang']['list']."</b></legend>
			<table  cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		$no=0;
        $str="SELECT * from ".$dbname.".pabrikasi_5mastermaterial where kodepabrikasi='".$kdpab."' and kodesalesorder='".$kdso."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodebarang']."</td>";
			$tab.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=right>".$bar['targetproduksi']."</td>";
			
			#cek master so
			$strbrg="SELECT count(*) as data from ".$dbname.".pabrikasi_salesdt where kodeso='".$kdso."' and kodebarang='".$bar['kodebarang']."'";
			$resbrg=$owlPDO->query($strbrg) or die(print " Gagal: ".PDOException::getMessage());
			$resbrg->setFetchMode(PDO::FETCH_ASSOC);
			$barbrg=$resbrg->fetch();
				$data=$barbrg['data'];
				
			$tab.="<td align=center> ";
			if($data<1){
				$tab.="<img src=images/application/application_delete.png class=zImgBtn title='Delete' 
					onclick=\"deletedetailbarang('".$bar['kodepabrikasi']."','".$bar['kodesalesorder']."','".$bar['kodebarang']."');\">";
			}
            $tab.="</td></tr>";
        }
		$tab.="</table></fieldset>";
		echo $tab;
	break;
	
	case'updatehead':
		#liat yg detail apakah ada tanggal dibawah?
		$str="select * from ".$dbname.".pabrikasi_5masterdt where kodepabrikasi='".$kdpab."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tgldt1=$bar['tanggalmulai'];
			$tgldt2=$bar['tanggalselesai'];
		
		if($kdso!=''){
			#delete material yang sudah tersimpan dahulu
			$str="delete from ".$dbname.".`pabrikasi_5mastermaterial` where kodepabrikasi='".$kdpab."'";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
			
			#ambil daftar barang di setup so
			$str="SELECT * from ".$dbname.".pabrikasi_salesdt where kodeso='".$kdso."' and status=1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$strdt="INSERT INTO ".$dbname.".`pabrikasi_5mastermaterial` 
				(`kodepabrikasi`,`kodesalesorder`, `kodebarang`,`targetproduksi`) 
				VALUES ('".$kdpab."','".$kdso."', '".$bar['kodebarang']."','".$bar['jumlah']."')";
				try{
					$owlPDO->exec($strdt);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}
		}
		
	
		$str="update  ".$dbname.".`pabrikasi_5masterht` set namapabrikasi='".$nmpab."',kodekelompok='".$kdkel."',kodesalesorder='".$kdso."',
				tanggalmulai='".$tgl1."',tanggalselesai='".$tgl2."',status='".$stat."' where kodepabrikasi='".$kdpab."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		echo $kdpab;
		
	break;
	
	
	
	
	
	case'savedetail':
	
		if($tgldt1>$tgl2){
			exit("Error:Tanggal diluar header");
		}
		if($tgldt2>$tgl2){
			exit("Error:Tanggal diluar header");
		}
		
		if($tgldt1<$tgl1){
			exit("Error:Tanggal diluar header");
		}
	

		$str="select max(idtahapan) as maxid from ".$dbname.".pabrikasi_5masterdt where 
				kodepabrikasi='".$kdpab."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$maxid=$bar['maxid'];
			
			$idthpn=$maxid+1;
			
	
		$str="INSERT INTO ".$dbname.".`pabrikasi_5masterdt` 
			(`kodepabrikasi`,`idtahapan`, `tahapan`,`tanggalmulai`, `tanggalselesai`,`keterangan`,`updateby`) 
			VALUES ('".$kdpab."',".$idthpn.", '".$tahapan."','".$tgldt1."', '".$tgldt2."', '".$ketdt."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'updatedetail':
	
		if($tgldt1>$tgl2){
			exit("Error:Tanggal diluar header");
		}
		if($tgldt2>$tgl2){
			exit("Error:Tanggal diluar header");
		}
	
		$str="update  ".$dbname.".`pabrikasi_5masterdt` set tahapan='".$tahapan."',tanggalmulai='".$tgldt1."',tanggalselesai='".$tgldt2."',keterangan='".$ketdt."'
				where kodepabrikasi='".$kdpab."' and idtahapan='".$idtahapan."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'listdetail':
	
		$tab="<fieldset><legend><b>".$_SESSION['lang']['list']."</b></legend>
			<table  cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['tahapan']."</td>
						<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
						<td align=center>".$_SESSION['lang']['tanggalselesai']."</td>
						<td align=center>".$_SESSION['lang']['keterangan']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		$no=0;
        $str="SELECT * from ".$dbname.".pabrikasi_5masterdt where kodepabrikasi='".$kdpab."' order by tanggalmulai asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['tahapan']."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggalmulai'])."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggalselesai'])."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
            $tab.="
            <td align=center>";
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editdetail('".$bar['idtahapan']."','".$bar['tahapan']."','".tanggalnormal($bar['tanggalmulai'])."','".tanggalnormal($bar['tanggalselesai'])."','".$bar['keterangan']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletedetail('".$bar['kodepabrikasi']."','".$bar['idtahapan']."');\">     
				";
            $tab.="</td>";
            $tab.="</tr>";
        }
		$tab.="</table></fieldset>";
		echo $tab;
	break;
	
	
	case'loaddata':

        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
		
		
		
		$where="";
        if($schkdpab!='') {
			$where.=" and kodepabrikasi like '%".$schkdpab."%' ";
        }
		if($schnmpab!='') {
			$where.=" and namapabrikasi like '%".$schnmpab."%' ";
        }
		if($schkdkel!='') {
			$where.=" and kodekelompok='".$schkdkel."' ";
        }
		if($schkdso!='') {
			$where.=" and kodesalesorder like '%".$schkdso."%' ";
        }
		if($schstat!='') {
			$where.=" and status='".$schstat."' ";
        }
		
		
        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_5masterht where 1=1 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	


        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".pabrikasi_5masterht where 1=1 ".$where."   limit ".$offset.",".$limit."";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodeorg']."</td>";
			$tab.="<td align=left>".$bar['kodepabrikasi']."</td>";
			$tab.="<td align=left>".$bar['namapabrikasi']."</td>";
			$tab.="<td align=left>".$bar['kodekelompok']."</td>";
			$tab.="<td align=left>".$nmkel[$bar['kodekelompok']]."</td>";
			$tab.="<td align=left>".$bar['kodesalesorder']."</td>";
			$tab.="<td align=left>".$arrst[$bar['status']]."</td>";
            $tab.="
            <td align=center>";
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"edit('".$bar['kodepabrikasi']."','".$bar['namapabrikasi']."','".$bar['kodekelompok']."',
					 '".tanggalnormal($bar['tanggalmulai'])."','".tanggalnormal($bar['tanggalselesai'])."','".$bar['kodesalesorder']."','".$bar['status']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['kodepabrikasi']."');\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_5masterht','".$bar['kodepabrikasi']."','','pabrikasi_slave_5pabrikasi_pdf',event)\">";
            $tab.="</td>";
            $tab.="</tr>";
        }
        $totrows=ceil($jlhbrs/$limit);
        if($totrows==0){
                $totrows=1;
        }
        $isiRow='';
        for($er=1;$er<=$totrows;$er++){
                $sel = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd="
            <tr><td colspan=9 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
 
	
	case'savehead':
		//$kdpab='PB'.date('ymdHi');
		
		#cek apakah kodeso sudah pernah dibuat
		$str="SELECT kodesalesorder,kodepabrikasi from ".$dbname.".pabrikasi_5masterht where kodesalesorder='".$kdso."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$kdsocek=$bar['kodesalesorder'];
			$kdpabcek=$bar['kodepabrikasi'];
		
		if($kdsocek!=''){
			exit("Warning:Data dengan kode sales order ".$kdsocek." sudah terpakai dikode pabrikasi ".$kdpabcek." ");
		}
		
		
		$thnini=substr($tgl1,0,4);
		
		$str="select right(max(kodepabrikasi),5) as jummax from ".$dbname.".pabrikasi_5masterht where tanggalmulai like '".$thnini."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$num=$bar['jummax'];
			if($num!=''){
				$num=$num+1;
			}
			else{
			  $num=1;	
			}	
		}
		$kdpab='PB'.$kdkel.addZero($num,5);
		$str="INSERT INTO ".$dbname.".`pabrikasi_5masterht` 
			(`kodeorg`, `kodepabrikasi`, `namapabrikasi`, `kodekelompok`,
			`tanggalmulai`, `tanggalselesai`, `kodesalesorder`, `status`, `updateby`) 
			VALUES ('".$_SESSION['empl']['lokasitugas']."', '".$kdpab."', '".$nmpab."', '".$kdkel."',
					'".$tgl1."', '".$tgl2."','".$kdso."', '".$stat."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		#jika kdso!='' maka ambil daftar barang di setup so
		$str="SELECT * from ".$dbname.".pabrikasi_salesdt where kodeso='".$kdso."' and status=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$strdt="INSERT INTO ".$dbname.".`pabrikasi_5mastermaterial` 
			(`kodepabrikasi`,`kodesalesorder`, `kodebarang`,`targetproduksi`) 
			VALUES ('".$kdpab."','".$kdso."', '".$bar['kodebarang']."','".$bar['jumlah']."')";
			try{
				$owlPDO->exec($strdt);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}
		echo $kdpab;
		
	break;
		
	case'deletedetail':
		$str="delete from ".$dbname.".pabrikasi_5masterdt where kodepabrikasi='".$kdpab."' and idtahapan='".$idtahapan."'";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'deletehead':
	
		#cek apakah sudah dipakai ditransaksi
		$str="select count(kodepabrikasi) as jumlah from ".$dbname.".pabrikasi_rabht where kodepabrikasi='".$kdpab."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlah=$bar['jumlah'];
			
		if($jumlah>0){
			exit("Warning : Data sudah terpakai ditransaksi RAB");
		}
	
		$str="delete from ".$dbname.".pabrikasi_5masterht where kodepabrikasi='".$kdpab."'";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
    
    case'posting':
		$str="update  ".$dbname.".keu_pdoht set posting='1',postingby='".$_SESSION['standard']['userid']."'
				,postingtime=now() where nopdo='".$nopdo."' and kodeorg='".$unit."' and periode='".$per."'  ";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
    
    
    default;
	
}
?>