<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmcus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');

$nodo = checkPostGet('nodo', '');
$tgldo=tanggalsystemn(checkPostGet('tgldo',''));
$kdbuyer = checkPostGet('kdbuyer', '');
$kdout = checkPostGet('kdout', '');
$kdso = checkPostGet('kdso', '');
$ket = checkPostGet('ket', '');
$ttd1 = checkPostGet('ttd1', '');
$ttd2 = checkPostGet('ttd2', '');
$ttd3 = checkPostGet('ttd3', '');

$nodok = checkPostGet('nodok', '');
$kdbrg = checkPostGet('kdbrg', '');
$qty = checkPostGet('qty', '');
$noseri = checkPostGet('noseri', '');
$tglkad=tanggalsystemn(checkPostGet('tglkad',''));
$carilistnodok= checkPostGet('carilistnodok', '');

$nodosch = checkPostGet('nodosch', '');
$tglsch=tanggalsystemn(checkPostGet('tglsch',''));
if($tglsch=='--'){
	$tglsch='';
}
switch ($method){
	
	case'getso':
//	exit("Error:$kdso");
		$optso="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeso from ".$dbname.".pabrikasi_salesht where kodecustomer='".$kdbuyer."' 
				and kodeso in (select kodeso from ".$dbname.".pabrikasi_salesdt where status='1')  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($kdso==$bar['kodeso'])
            {$select="selected=selected";}
            else
            {$select="";}
			$optso.="<option ".$select." value='".$bar['kodeso']."'>".$bar['kodeso']."</option>";
		}
		echo $optso;
	
	break;
	
	case'savehead':
	
		$nodo=date('YmdHi'); 
	
		$str="
			INSERT INTO ".$dbname.".`pabrikasi_doht` (`nodo`, `tanggaldo`, `kodecustomer`, `kodeoutlet`, `kodeso`,`keterangan`,
			`ttd1`,`ttd2`,`ttd3`,`updateby`)
			VALUES ('".$nodo."','".$tgldo."','".$kdbuyer."','".$kdout."','".$kdso."','".$ket."',
			'".$ttd1."','".$ttd2."','".$ttd3."','".$_SESSION['empl']['kodeorganisasi']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		echo $nodo;
		
	break;
	
	case'updatehead':
		$str="
				update ".$dbname.".`pabrikasi_doht` set tanggaldo='".$tgldo."',kodecustomer='".$kdbuyer."',kodeoutlet='".$kdout."',
				kodeso='".$kdso."',keterangan='".$ket."',ttd1='".$ttd1."',ttd2='".$ttd2."',ttd3='".$ttd3."',updateby='".$_SESSION['standard']['userid']."'
				where nodo='".$nodo."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		echo $nodo;
	break;
	
	case'deletehead':
		$str="
				delete from ".$dbname.".`pabrikasi_doht` where nodo='".$nodo."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	
	
	
	case'savedetail':
		$str="
				INSERT INTO ".$dbname.".`pabrikasi_dodt` (`nodo`, `nodok`, `kodebarang`, `jumlah`, `noseri`,`tanggalkadaluarsa`,`updateby`)
				VALUES ('".$nodo."','".$nodok."','".$kdbrg."','".$qty."','".$noseri."','".$tglkad."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'updatedetail':
		$str="
				update ".$dbname.".`pabrikasi_dodt` set jumlah='".$qty."',noseri='".$noseri."',
				tanggalkadaluarsa='".$tglkad."',updateby='".$_SESSION['standard']['userid']."'
				where nodo='".$nodo."' and nodok='".$nodok."' and kodebarang='".$kdbrg."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'deletedetail':
		$str="
				delete from ".$dbname.".`pabrikasi_dodt` where nodo='".$nodo."' and nodok='".$nodok."' and kodebarang='".$kdbrg."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	
	
	
	
	case'loaddetail':
		$data="";
		$data.="<fieldset style=width:750px><legend>".$_SESSION['lang']['list']."</legend>
			<table  cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
				<thead>
					<tr>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['nodok']."</td>
						<td align=center>".$_SESSION['lang']['kodebarang']."</td>
						<td align=center>".$_SESSION['lang']['namabarang']."</td>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>
						<td align=center>No. Seri</td>
						<td align=center>".$_SESSION['lang']['tanggal']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		
		$str="SELECT * from ".$dbname.".pabrikasi_dodt where nodo='".$nodo."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $data.="<tr class=rowcontent>";
            $data.="<td align=center>".$no."</td>";
            $data.="<td align=left>".$bar['nodok']."</td>";
			$data.="<td align=left>".$bar['kodebarang']."</td>";
			$data.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$data.="<td align=right>".$bar['jumlah']."</td>";
			$data.="<td align=right>".$bar['noseri']."</td>";
			$data.="<td align=right>".tanggalnormal($bar['tanggalkadaluarsa'])."</td>";
			$data.="<td>
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editdt('".$bar['nodok']."','".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."',
					 '".$bar['jumlah']."','".$bar['noseri']."','".tanggalnormal($bar['tanggalkadaluarsa'])."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletedt('".$bar['nodo']."','".$bar['nodok']."','".$bar['kodebarang']."');\">
				</td>";
			$data.="</tr>";	
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
		if($nodosch!='') {
			$where.=" and nodo like '%".$nodosch."%' ";
        }
		
        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_doht where 1=1  ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	
		
        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".pabrikasi_doht where 1=1 ".$where."   limit ".$offset.",".$limit."";
	
		$tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['nodo']."</td>";
			$tab.="<td align=left>".tanggalnormal($bar['tanggaldo'])."</td>";
			$tab.="<td align=left>".$nmcus[$bar['kodecustomer']]."</td>";
			$tab.="<td align=left>".$bar['kodeoutlet']."</td>";
			$tab.="<td align=left>".$bar['kodeso']."</td>";
            $tab.="
            <td align=center>";
			if($bar['status']==0){
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editht('".$bar['nodo']."','".tanggalnormal($bar['tanggaldo'])."','".$bar['kodecustomer']."','".$bar['kodeoutlet']."',
					 '".$bar['kodeso']."','".$bar['keterangan']."','".$bar['ttd1']."','".$bar['ttd2']."','".$bar['ttd3']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deleteht('".$bar['nodo']."');\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_doht','".$bar['nodo']."','','pabrikasi_slave_do_pdf',event)\">
				";
			}else{
				$tab.="<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_doht','".$bar['nodo']."','','pabrikasi_slave_do_pdf',event)\">";
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
            <tr><td colspan=7 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
	
	case'getnodok':
	
        echo"<fieldset  style='float:left;' >
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['nodok']."</td>
                            <td colspan=5>: 
                                    <input type=text id=carilistnodok class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=getlistnodok()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listnodok cellspacing=1 border=0 class=sortable>
                    <thead>
                    <tr class=rowheader>
                            <td align=center>No</td>
                            <td align=center>".$_SESSION['lang']['nodok']."</td>
							<td align=center>".$_SESSION['lang']['kodebarang']."</td>
							<td align=center>".$_SESSION['lang']['namabarang']."</td>
							<td align=center>".$_SESSION['lang']['satuan']."</td>
							<td align=center>".$_SESSION['lang']['jumlah']."</td>
                    </tr></thead>";

                    if($carilistnodok!=''){
						if($kdso!=''){//and left(notransaksireferensi,2)='PB'
							$str="select * from ".$dbname.".log_transaksi_vw where 1=1 and tipetransaksi=5 and notransaksi like '%".$carilistnodok."%'
								and kodept='".$_SESSION['empl']['kodeorganisasi']."'";
							
							$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while($bar=$res->fetch()) {
								
								
								#cek sudah terinput
								$strg="select sum(jumlah) as jumlahsave from ".$dbname.".pabrikasi_dodt where 1=1 and nodok='".$bar['notransaksi']."' 
										and kodebarang='".$bar['kodebarang']."' ";
									
								$resg=$owlPDO->query($strg) or die(print " Gagal: ".PDOException::getMessage());
								$resg->setFetchMode(PDO::FETCH_ASSOC);
								$barg=$resg->fetch();
									$jumlahsave=$barg['jumlahsave'];
									
								#cek kdso
								$str1="select jumlah from ".$dbname.".pabrikasi_salesdt where 1=1 and kodeso='".$kdso."' and kodebarang='".$bar['kodebarang']."' ";
								$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
								$res1->setFetchMode(PDO::FETCH_ASSOC);
								$bar1=$res1->fetch();
									$jumlahso=$bar1['jumlah'];
								
								$jumlahsisa=$bar['jumlah']-$jumlahsave;
								$jumlahtampil=$jumlahsisa-$jumlahso;
								
								if($jumlahtampil>$jumlahso and $jumlahtampil>0){
									$no+=1;
									echo"
									<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movedatadok('".$bar['notransaksi']."','".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."','".$bar['jumlah']."');\">
										<td align=center>".$no."</td>
										<td>".$bar['notransaksi']."</td>
										<td>".$bar['kodebarang']."</td>
										<td>".$nmbrg[$bar['kodebarang']]."</td>
										<td>".$bar['satuan']."</td>
										<td align=right>".$jumlahtampil."</td>
										<td align=right hidden>".$bar['jumlah']."</td>
										<td align=right hidden>".$jumlahso."</td>
										<td align=right hidden>".$jumlahsave."</td>
										<td align=right hidden>".$jumlahtampil."</td>
									</tr>";
								}else{
									echo"Data telah melebihi Saler Order";
								}
							}
						}else{
							$str="select * from ".$dbname.".log_transaksi_vw where 1=1 and tipetransaksi=5 and notransaksi like '%".$carilistnodok."%'
								and kodept='".$_SESSION['empl']['kodeorganisasi']."'";
							$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while($bar=$res->fetch()) {
								$no+=1;
								echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movedatadok('".$bar['notransaksi']."','".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."','".$bar['jumlah']."');\">
									<td align=center>".$no."</td>
									<td>".$bar['notransaksi']."</td>
									<td>".$bar['kodebarang']."</td>
									<td>".$nmbrg[$bar['kodebarang']]."</td>
									<td>".$bar['satuan']."</td>
									<td align=right>".$bar['jumlah']."</td>
								</tr>";
							}
						}
                       
					}
                    echo"</table>
        </fieldset>";
	
    break;
	
	
    default;
	
}
?>