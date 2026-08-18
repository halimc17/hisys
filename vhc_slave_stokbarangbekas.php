<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
	
$method=checkPostGet('method','');
$kdOrg=checkPostGet('kdOrg','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$saldo=checkPostGet('saldo','');
$keluar=checkPostGet('keluar','');
$ket=checkPostGet('ket','');
$kdBrg=checkPostGet('kdBrg','');
$tgljam=checkPostGet('tgljam','');
$tglSch=tanggalsystemn(checkPostGet('tglSch',''));
$kdBrgSch=checkPostGet('kdBrgSch','');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

if($tglSch=='--'){
    $tglSch='';
}

switch($method){
	case 'getstok':
		$str="SELECT sum(masuk-keluar) as sawal FROM ".$dbname.".vhc_stokbarangbekas where kodeorg='".$kdOrg."' and kodebarang='".$kdBrg."' and tanggal <='".$tgl."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		echo @number_format($bar['sawal'],2);
	break;
	case 'insert':
		$str="SELECT count(keluar) as jumlah FROM ".$dbname.".vhc_stokbarangbekas where kodeorg='".$kdOrg."' and kodebarang='".$kdBrg."' and tanggal >'".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['jumlah']!=0){
			exit('Error : Sudah ada transaksi pengeluaran ditanggal lebih besar dari tanggal : '.$tgl);
		}
		
		$tglTemp=explode('-',$tgl);
		$notranTemp=$kdOrg."/K/".$tglTemp[0]."/".$tglTemp[1]."/";
		
		$str="SELECT count(*) as jumlah FROM ".$dbname.".vhc_stokbarangbekas where notransaksi like '".$notranTemp."%'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['jumlah']==0){
			$nourut=1;
		}else{
			$nourut=$bar['jumlah']+1;
		}
		$nomor=$notranTemp.addZero($nourut,3);
		
		$jam=date("H:i:s");
		$tgljam=$tgl." ".$jam;
		$iSave="INSERT INTO ".$dbname.".`vhc_stokbarangbekas` (`kodeorg`,`notransaksi`,`tanggal`,`tanggaljam`,`kodebarang`, `keluar`, 
				`updateby`,`keterangan`)
				values ('".$kdOrg."','".$nomor."','".$tgl."','".$tgljam."','".$kdBrg."','".$keluar."','".$_SESSION['standard']['userid']."','".$ket."')";
		try{
			$owlPDO->exec($iSave);
		}catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
        
	case 'update':
		$iUpdate="update ".$dbname.".vhc_stokbarangbekas set keluar='".$keluar."',updateby='".$_SESSION['standard']['userid']."',keterangan='".$ket."' "." where kodeorg='".$kdOrg."' and kodebarang='".$kdBrg."' and tanggal='".$tgl."' and tanggaljam='".$tgljam."'";
		try{
			$owlPDO->exec($iUpdate);
		}catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
        

	case'loadData':
	echo"<div id=container>
		<table class=sortable cellspacing=1 border=0>
		 <thead>
			 <tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['gudang']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>    
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['saldoawal']."</td>
				<td align=center>".$_SESSION['lang']['masuk']."</td>
				<td align=center>".$_SESSION['lang']['keluar']."</td>
				<td align=center>".$_SESSION['lang']['saldoakhir']."</td>    
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>Action</td></tr>
			 </tr>
		</thead>
		<tbody>";

	$tmbh2=$tmbh3="";
		if($kdBrgSch!=''){
			$tmbh2=" and kodebarang='".$kdBrgSch."' ";
		}
		if($tglSch!=''){
			$tmbh3=" and tanggal like '".$tglSch."' ";
		}
	$limit=20;
	$page=0;
	if(isset($_POST['page'])){
	$page=$_POST['page'];
	if($page<0)
	$page=0;
	}
	$offset=$page*$limit;
	$maxdisplay=($page*$limit);

	$ql2="select count(*) as jmlhrow from ".$dbname.".vhc_stokbarangbekas where kodeorg like '".$_SESSION['empl']['lokasitugas']."%'  ".$tmbh2." ".$tmbh3." ";
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){  
		$jlhbrs= $jsl->jmlhrow;
	}
	$no=$maxdisplay;
	$iList="select * from ".$dbname.".vhc_stokbarangbekas where kodeorg like '".$_SESSION['empl']['lokasitugas']."%'  ".$tmbh2." ".$tmbh3." order by tanggaljam desc limit ".$offset.",".$limit."";
	$nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
	$nList->setFetchMode(PDO::FETCH_ASSOC);
	while($dList=$nList->fetch()){
	$no+=1;
		echo "<tr class=rowcontent>";
		echo "<td align=center>".$no."</td>";
		echo "<td align=left>".$nmOrg[$dList['kodeorg']]."</td>";
		echo "<td align=left>".tanggalnormal($dList['tanggal'])."</td>";
		
		echo "<td align=left>".$dList['kodebarang']."</td>";
		echo "<td align=left>".$nmBrg[$dList['kodebarang']]."</td>";
		$str="select sum(masuk-keluar) as sawal from ".$dbname.".vhc_stokbarangbekas where kodebarang='".$dList['kodebarang']."' and tanggaljam<'".$dList['tanggaljam']."' and kodeorg='".$dList['kodeorg']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$sawal=$bar['sawal']+$dList['masuk'];
		$sakhir=$bar['sawal']+$dList['masuk']-$dList['keluar'];
		echo "<td align=right>".number_format($sawal)."</td>";
		echo "<td align=right>".number_format($dList['masuk'])."</td>";
		echo "<td align=right>".number_format($dList['keluar'])."</td>";
		echo "<td align=right>".number_format($sakhir)."</td>";
		echo "<td align=left>".$dList['keterangan']."</td>";
		echo "<td align=center>";
		
		if($_SESSION['standard']['userid']=$dList['updateby'] and $dList['keluar']!=0){
			$hapus="<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$dList['kodeorg']."','".tanggalnormal($dList['tanggal'])."','".$dList['kodebarang']."','".$dList['keluar']."','".$dList['keterangan']."','".$dList['tanggaljam']."');\">
			
			<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kodeorg']."','".tanggalnormal($dList['tanggal'])."','".$dList['kodebarang']."','".$dList['tanggaljam']."');\">";
		}else{
			$hapus="<img src=images/application/application_edit_gray.png class=resicon  caption='Edit'>
					<img src=images/application/application_delete_gray.png class=resicon  caption='Delete'>";
		}
		echo "".$hapus."";
		
		echo "</td>";
		echo "</tr>";
	}
	echo"
	<tr class=rowheader><td colspan=11 align=center>
	".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />";
	if($page==0){
		echo"<button class=mybutton>".$_SESSION['lang']['pref']."</button>";
	}else{
		echo"<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
	}
	if(($page+1)==ceil($jlhbrs/$limit)){
		echo"<button class=mybutton>".$_SESSION['lang']['lanjut']."</button>";
	}else{
		echo"<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
	}
	echo"</td>
	</tr>";
	break;        

	case 'delete':
            $iDelete="delete from ".$dbname.".vhc_stokbarangbekas where kodeorg='".$kdOrg."' and tanggal='".$tgl."' "
            . " and kodebarang='".$kdBrg."' and tanggaljam='".$tgljam."'";
            try{
                $owlPDO->exec($iDelete);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
	break;
	
default:
}
?>