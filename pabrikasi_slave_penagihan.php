<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');

$nodo=checkPostGet('nodo', '');
$notran=checkPostGet('notran', '');	
$kdbuyer=checkPostGet('kdbuyer', '');	
$kdso=checkPostGet('kdso', '');	
$ttd1=checkPostGet('ttd1', '');	
$ttd2=checkPostGet('ttd2', '');	
$ttd3=checkPostGet('ttd3', '');	
$tglpen=tanggalsystemn(checkPostGet('tglpen',''));
$tgljth=tanggalsystemn(checkPostGet('tgljth',''));	

$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$satbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$nmpab=makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi');
$nmcus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
	
$kdbrg = checkPostGet('kdbrg', '');
$qty = checkPostGet('qty', '');
$harsat = checkPostGet('harsat', '');
$total = checkPostGet('total', '');
$ppn = checkPostGet('ppn', '');
$cek = checkPostGet('cek', '');
$carilistnodok = checkPostGet('carilistnodok', '');

$notransch=checkPostGet('notransch', '');
$tglsch=tanggalsystemn(checkPostGet('tglsch',''));
if($tglsch=='--'){
	$tglsch='';
}
$optstat="<option value='0'>Waiting</option>";
$optstat.="<option value='1'>Open</option>";
$optstat.="<option value='2'>Cancel</option>";
$optstat.="<option value='3'>Close</option>";		


$arrst=array("0"=>"Waiting","1"=>"Open","2"=>"Cancel","3"=>"Close");

switch ($method) {
	
	case'posting':
	
		$str="select tanggal,kodecustomer from ".$dbname.".pabrikasi_penagihanht where notransaksi='".$notran."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tgl=$bar['tanggal'];
			$kdbuyer=$bar['kodecustomer'];
	
		$str="select sum(hargatotal) as hargatotal,sum(ppn) as ppn from ".$dbname.".pabrikasi_penagihandt where notransaksi='".$notran."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$niltotal=$bar['hargatotal'];
			$nilppn=$bar['ppn'];
			$nilaidgppn=$niltotal+$nilppn;
		

	
	
		#########################  bentuk junal 1  ##################################
	
		
		$str="select * from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='PNJ' and jurnalid='PNJ1' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$kodejurnal=$bar['jurnalid'];
			$db=$bar['noakundebet'];
			$kr=$bar['noakunkredit'];
			
		#buat parameternya
		$str="select nokounter from ".$dbname.".keu_5kelompokjurnal where kodekelompok='".$kodejurnal."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'  ";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$nokounter=$bar['nokounter']+1;
			$nokounter=addZero($nokounter,3);
		//exit("Error:$nokounter");
		$kodeorg=$_SESSION['empl']['lokasitugas'];

		$notgl=str_replace('-','',$tgl);
		//$noref=$notgl.'/'.$kodeorg;
		$nojurnal=$notgl.'/'.$kodeorg.'/'.$kodejurnal.'/'.$nokounter;

		
		
	
		
		#jurnal ht	
		$str="INSERT INTO `keu_jurnalht` (`nojurnal`, `kodejurnal`, `tanggal`, `tanggalentry`, `posting`, `totaldebet`, `totalkredit`,
					`amountkoreksi`, `noreferensi`, `autojurnal`, `matauang`, `kurs`, `revisi`)
		VALUES ('".$nojurnal."', '".$kodejurnal."', '".$tgl."','".date('Y-m-d')."', '1','".$nilaidgppn."', '".($nilaidgppn*-1)."',
				'0','".$notran."', '1','IDR', '1', '0')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}	
		
	
		#insert dt db
		$str="INSERT INTO `keu_jurnaldt` (`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`,
				`kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`,
				`kodevhc`, `nodok`, `kodeblok`, `revisi`, `kodesegment`)
				VALUES ('".$nojurnal."', '".$tgl."', '1', '".$db."','PABRIKASI ".$notran."', '".$nilaidgppn."','IDR', '1','".$kodeorg."',
				NULL, '', NULL, '','".$kdbuyer."', '','".$notran."', NULL, NULL, NULL, NULL, '0', '0000000001')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		#kr
		$str="INSERT INTO `keu_jurnaldt` (`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`,
				`kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`,
				`kodevhc`, `nodok`, `kodeblok`, `revisi`, `kodesegment`)
				VALUES ('".$nojurnal."', '".$tgl."', '2', '".$kr."','PABRIKASI ".$notran."', '".($niltotal*-1)."','IDR', '1','".$kodeorg."',
				NULL, '', NULL, '','".$kdbuyer."', '','".$notran."', NULL, NULL, NULL, NULL, '0', '0000000001')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	
		#kr ppn
		$str="INSERT INTO `keu_jurnaldt` (`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`,
				`kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`,
				`kodevhc`, `nodok`, `kodeblok`, `revisi`, `kodesegment`)
				VALUES ('".$nojurnal."', '".$tgl."', '3', '2120500','PPN KELUARAN ".$notran."', '".($nilppn*-1)."','IDR', '1','".$kodeorg."',
				NULL, '', NULL, '','".$kdbuyer."', '','".$notran."', NULL, NULL, NULL, NULL, '0', '0000000001')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		#update nomor kounter
		$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$nokounter."' 
			where kodekelompok='".$kodejurnal."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'  ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		#########################  selesai junal 1  ##################################
		
		
		
		#########################  bentuk junal 2  ##################################
		
		$str="select * from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='PBR' and jurnalid='PBR4' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$kodejurnal=$bar['jurnalid'];
			$db=$bar['noakundebet'];
			$kr=$bar['noakunkredit'];
			
		#buat parameternya
		$str="select nokounter from ".$dbname.".keu_5kelompokjurnal where kodekelompok='".$kodejurnal."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$nokounter=$bar['nokounter']+1;
			$nokounter=addZero($nokounter,3);
		
		//$kodeorg=$_SESSION['empl']['lokasitugas'];

		//$notgl=str_replace('-','',$tgl);
		$nojurnal=$notgl.'/'.$kodeorg.'/'.$kodejurnal.'/'.$nokounter;
		
		$str="select nodo,kodebarang from ".$dbname.".pabrikasi_penagihandt where notransaksi='".$notran."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrdo[$bar['nodo']]=$bar['nodo'];
			$arrkdbrg[$bar['kodebarang']]=$bar['kodebarang'];
		}	
		
		foreach($arrdo as $nodo){
			$str="select nodok from ".$dbname.".pabrikasi_dodt where nodo='".$nodo."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$nogudang[$bar['nodok']]=$bar['nodok'];
			}
		}
		
		foreach($nogudang as $nogdg){
			foreach($arrkdbrg as $kdbrg){
				$str="select jumlah,hargasatuan,notransaksi from ".$dbname.".log_transaksi_vw where notransaksi='".$nogdg."' and kodebarang='".$kdbrg."' ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$qtygudang[$bar['nodok']]=$bar['nodok'];
					$rp+=$bar['jumlah']*$bar['hargasatuan'];
				}
			}
		}
		
		#jurnal ht	
		$str="INSERT INTO `keu_jurnalht` (`nojurnal`, `kodejurnal`, `tanggal`, `tanggalentry`, `posting`, `totaldebet`, `totalkredit`,
					`amountkoreksi`, `noreferensi`, `autojurnal`, `matauang`, `kurs`, `revisi`)
		VALUES ('".$nojurnal."', '".$kodejurnal."', '".$tgl."','".date('Y-m-d')."', '1','".$rp."', '".($rp*-1)."',
				'0','".$notran."', '1','IDR', '1', '0')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}	
		
	
		#insert dt db
		$str="INSERT INTO `keu_jurnaldt` (`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`,
				`kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`,
				`kodevhc`, `nodok`, `kodeblok`, `revisi`, `kodesegment`)
				VALUES ('".$nojurnal."', '".$tgl."', '1', '".$db."','PABRIKASI ".$notran."', '".$rp."','IDR', '1','".$kodeorg."',
				NULL, '', NULL, '','".$kdbuyer."', '','".$notran."', NULL, NULL, NULL, NULL, '0', '0000000001')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		#kr
		$str="INSERT INTO `keu_jurnaldt` (`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`,
				`kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`,
				`kodevhc`, `nodok`, `kodeblok`, `revisi`, `kodesegment`)
				VALUES ('".$nojurnal."', '".$tgl."', '2', '".$kr."','PABRIKASI ".$notran."', '".($rp*-1)."','IDR', '1','".$kodeorg."',
				NULL, '', NULL, '','".$kdbuyer."', '','".$notran."', NULL, NULL, NULL, NULL, '0', '0000000001')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$nokounter."' 
			where kodekelompok='".$kodejurnal."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'  ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		
		#update flag	
	
		$str="	update ".$dbname.".`pabrikasi_penagihanht` set status='1' where notransaksi='".$notran."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	
	break;
	
	case'updatehead':
		$str="	update ".$dbname.".`pabrikasi_penagihanht` set tanggal='".$tglpen."',jatuhtempo='".$tgljth."',kodecustomer='".$kdbuyer."',
				kodeso='".$kdso."',ttd1='".$ttd1."',ttd2='".$ttd2."',ttd3='".$ttd3."',updateby='".$_SESSION['standard']['userid']."'
				where notransaksi='".$notran."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		echo $notran;
	break;
	
	case'savehead':
		$str="select max(notransaksi) as notransaksi from ".$dbname.".pabrikasi_penagihanht";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$num=$bar['notransaksi'];
			if($num!=''){
				$num=intval(substr($num,6,5))+1;
			}
			else{
			  $num=1;	
			}	
		}
		
		if($num<10)
			   $num='0000'.$num;
			else if($num<100)
			   $num='000'.$num;
			else if($num<1000)
			   $num='00'.$num;	   
			else if($num<10000)
				   $num='0'.$num;
				else
			   $num=$num;
		
		$date=date('Ym');//Hi 		
	    $notran=$date.$num;
		
		$str=" INSERT INTO `pabrikasi_penagihanht` (`notransaksi`, `tanggal`, `jatuhtempo`, `kodecustomer`, `kodeso`,
					`ttd1`, `ttd2`, `ttd3`,`updateby`)
					VALUES ('".$notran."','".$tglpen."','".$tgljth."','".$kdbuyer."','".$kdso."',
					'".$ttd1."','".$ttd2."','".$ttd3."','".$_SESSION['standard']['userid']."')";
					
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		echo $notran;
	break;
	
	
	
	case'deletedetail':
		$str="delete from ".$dbname.".pabrikasi_penagihandt where notransaksi='".$notran."' and nodo='".$nodo."' and kodebarang='".$kdbrg."' ";
	
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'deletehead':
		$str="delete from ".$dbname.".pabrikasi_penagihanht where notransaksi='".$notran."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	
	case'savedetail':
		if($cek>0){
			if($harsat>0){
				$str=" INSERT INTO `pabrikasi_penagihandt` (`notransaksi`, `nodo`, `kodebarang`, `jumlah`, `hargasatuan`, `hargatotal`, `ppn`, `updateby`)
						VALUES ('".$notran."','".$nodo."','".$kdbrg."','".$qty."','".$harsat."','".$total."',
						'".$ppn."','".$_SESSION['standard']['userid']."')";
				try{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) {
				   print " Gagal  !: " . $e->getMessage() . "\n"; 
				   die(); 
				}
			}
			
		}
	break;	
	
 
 
	case'loaddetail':
		$data="";
		$data.="<fieldset  style=width:600px><legend>".$_SESSION['lang']['list']."</legend>
			<table  cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
				<thead>
					<tr>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['nodo']."</td>
						<td align=center>".$_SESSION['lang']['kodebarang']."</td>
						<td align=center>".$_SESSION['lang']['namabarang']."</td>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>
						<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
						<td align=center>".$_SESSION['lang']['total']."</td>
						<td align=center>".$_SESSION['lang']['ppn']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		
		$str="SELECT * from ".$dbname.".pabrikasi_penagihandt where notransaksi='".$notran."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $data.="<tr class=rowcontent>";
            $data.="<td align=center>".$no."</td>";
            $data.="<td align=left>".$bar['nodo']."</td>";
			$data.="<td align=left>".$bar['kodebarang']."</td>";
			$data.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$data.="<td align=right>".number_format($bar['jumlah'])."</td>";
			$data.="<td align=right>".number_format($bar['hargasatuan'])."</td>";
			$data.="<td align=right>".number_format($bar['hargatotal'])."</td>";
			$data.="<td align=right>".number_format($bar['ppn'])."</td>";
			$data.="<td align=center>
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletedt('".$bar['notransaksi']."','".$bar['nodo']."','".$bar['kodebarang']."');\">
				</td>";
			$data.="</tr>";	
			/*
			<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editdt('".$bar['nodo']."','".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."',
					 '".$bar['jumlah']."','".$bar['noseri']."','".tanggalnormal($bar['tanggalkadaluarsa'])."');\">
			*/
		}	
		echo $data;		
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
        if($tglsch!='') {
			$where.=" and tanggal='".$tglsch."%' ";
        }
		if($notransch!='') {
			$where.=" and notransaksi like '%".$notransch."%' ";
        }
		
        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_penagihanht where 1=1 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	
		
        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".pabrikasi_penagihanht where 1=1  ".$where."   limit ".$offset.",".$limit."";
		$tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['notransaksi']."</td>";
			$tab.="<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=left>".$nmcus[$bar['kodecustomer']]."</td>";
			$tab.="<td align=left>".$bar['kodeso']."</td>";
            $tab.="
            <td align=center>";
			if($bar['status']==0){
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editht('".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".tanggalnormal($bar['jatuhtempo'])."',
					 '".$bar['kodecustomer']."','".$bar['kodeso']."',
					 '".$bar['ttd1']."','".$bar['ttd2']."','".$bar['ttd3']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deleteht('".$bar['notransaksi']."');\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_penagihandt','".$bar['notransaksi']."','','pabrikasi_slave_penagihan_pdf',event)\">
				<img src=images/skyblue/posting.png class=resicon  title='posting' onclick=\"posting('".$bar['notransaksi']."');\">	
				";
			}else{
				$tab.="<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_penagihandt','".$bar['notransaksi']."','','pabrikasi_slave_penagihan_pdf',event)\">";
			}

			$tab.="</td>";
            $tab.="</tr>";
			/*
			<img src=images/skyblue/posting.png class=zImgBtn title='Posting' 
                     onclick=\"postinght('".$bar['kodepabrikasi']."');\">
			*/
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
            <tr><td colspan=6 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
	
	
 
	case'listdo':
		$data="<fieldset><legend><b>".$_SESSION['lang']['detail']."</b></legend>
				<table  cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
					<thead>
						<tr>
							<td align=center>No</td>
							<td style=\"width:50px;\" align=center>".$_SESSION['lang']['kodebarang']."</td>
							<td align=center>".$_SESSION['lang']['namabarang']."</td>
							<td style=\"width:30px;\" align=center>".$_SESSION['lang']['satuan']."</td>
							<td style=\"width:50px;\" align=center>".$_SESSION['lang']['jumlah']."</td>
							<td style=\"width:60px;\" align=center>".$_SESSION['lang']['hargasatuan']."</td>
							<td style=\"width:80px;\" align=center>".$_SESSION['lang']['total']."</td>
							<td style=\"width:60px;\" align=center>".$_SESSION['lang']['ppn']."</td>
							<td align=center>
								".$_SESSION['lang']['action']." 
								<br>
								<input type=checkbox id=cekall onclick=cekall()>
							</td>
						</tr>
			</thead><tbody id=contentdetail>";
		$str="select * from ".$dbname.".pabrikasi_dodt where nodo in 
				(select nodo from ".$dbname.".pabrikasi_doht where nodo='".$nodo."' and kodeso='".$kdso."' and kodecustomer='".$kdbuyer."') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
		
			#cek disini
			$str1="select sum(jumlah) as jumsave from ".$dbname.".pabrikasi_penagihandt where nodo='".$bar['nodo']."' and kodebarang='".$bar['kodebarang']."' ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
				$jumsave=$bar1['jumsave'];
				
			
			
			$defjumlah=$bar['jumlah']-$jumsave;
			
		
			if($defjumlah<1){
				$disabled="disabled";
			}else{
				$disabled="";
			}
		
			$no+=1;
			$data.="<tr class=rowcontent id=row".$no.">";
            $data.="<td align=center ".$disabled.">".$no."</td>";
            $data.="<td align=left id=kdbrg".$no." ".$disabled.">".$bar['kodebarang']."</td>";
			$data.="<td align=left ".$disabled.">".$nmbrg[$bar['kodebarang']]."</td>";
			$data.="<td align=left ".$disabled.">".$satbrg[$bar['kodebarang']]."</td>";
			$data.="<td align=right id=qty".$no." ".$disabled.">".$bar['jumlah']."</td>";
			$data.="<td align=right><input ".$disabled." type=text  id=harsat".$no." onblur=gettotal(".$no.") onkeypress='return tanpa_angka_doang(event)' class=myinputtextnumber style=\"width:60px;\"></td>";
			$data.="<td align=right id=total".$no." ".$disabled."></td>";
			$data.="<td align=right id=ppn".$no." ".$disabled."></td>";
			$data.="<td align=center ><input type=checkbox id=cek".$no." ".$disabled." ></td>";
			$data.="</tr>";
		}
		$data.="<tr  class=rowcontent>";
        $data.="<td colspan=9 align=right ><button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['save']."</button></td>";
        $data.="</tr>";  
		echo $data;
			
	break;

	case'getnodok':
	// echo $kdso;
	// echo $kdbuyer;
	
	
	echo"<fieldset  style='float:left;' >
			<table cellspacing=1 border=0 class=data>
				<tr>
					<td colspan=2>Search</td>
					<td colspan=5>: 
							<input type=text id=carilistnodok class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
							<button class=mybutton onclick=getlistnodok()>cari</button>
					<td>
				<tr>
			</table>

			<table id=listnodok cellspacing=1 border=0 class=sortable width=100%>
			<thead>
			<tr class=rowheader>
					<td align=center>No</td>
					<td align=center>".$_SESSION['lang']['nodo']."</td>
					
			</tr></thead>";

                    if($carilistnodok!=''){
						$str="select distinct(nodo) as nodo from ".$dbname.".pabrikasi_dodt where nodo in 
							 (select nodo from ".$dbname.".pabrikasi_doht where nodo like '%".$carilistnodok."%' and kodeso='".$kdso."' and kodecustomer='".$kdbuyer."') ";
						// echo $str;
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						while($bar=$res->fetch()){
						$no+=1;
							echo"
							<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movedatadok('".$bar['nodo']."');\">
								<td align=center>".$no."</td>
								<td>".$bar['nodo']."</td>
								
							</tr>";		
							}
							
						}
                    echo"</table>
        </fieldset>";
		
	break;
	
	
    default;
	
}



?>