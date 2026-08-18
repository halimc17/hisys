<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');

$kdpab=checkPostGet('kdpab', '');
$carilistkdso=checkPostGet('carilistkdso', '');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$satbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$nmpab=makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi');
$hargasatuan=checkPostGet('hargasatuan', '');

$notran=checkPostGet('notran', '');
$gudang=checkPostGet('gudang', '');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$tglcek=tanggalsystem(checkPostGet('tgl',''));
$kdpab=checkPostGet('kdpab', '');
$kdbrg=checkPostGet('kdbrg', '');
$qty=checkPostGet('qty', '');
$cek=checkPostGet('cek', '');

$qtycek=checkPostGet('qtycek', '');
$qtyawal=checkPostGet('qtyawal', '');

	
$notransch=checkPostGet('notransch', '');
$tglsch=tanggalsystemn(checkPostGet('tglsch',''));
if($tglsch=='--'){
	$tglsch='';
}

$harsat=checkPostGet('harsat', '');
$arrpost=array("0"=>"Not Confirm","1"=>"Confirm");

switch ($method) 
{
	
	
	//Kodebarang	nama barang	Satuan	Diterima	aksi

	
	case'list':
		if($tglcek>$_SESSION['gudang'][$gudang]['end'] or $tglcek<$_SESSION['gudang'][$gudang]['start']){
			exit("Warning:Tanggal diluar periode aktif");
		}
	
		#buat notransaksi
		$num=1;//default value 
		$str="select max(notransaksi) as notransaksi from ".$dbname.".log_transaksiht where 
		tanggal>=".$_SESSION['gudang'][$gudang]['start']." and tanggal<=".$_SESSION['gudang'][$gudang]['end']."
		and kodegudang='".$gudang."' order by notransaksi";	
		
		  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		  $res->setFetchMode(PDO::FETCH_OBJ);
		  if(owlBaris($res)>0)
		  {
			while($bar=$res->fetch())
			{
				$num=$bar->notransaksi;
				if($num!=''){
					$num=intval(substr($num,6,5))+1;
				}
				else{
				  $num=1;	
				}	
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
        
		$num=$_SESSION['gudang'][$gudang]['tahun'].$_SESSION['gudang'][$gudang]['bulan'].$num."-GR-".$gudang;	
	
	
	
		if($notran!=''){
			$num=$notran;
		}else{
			$num=$num;
		}
	
	
		$tab="<fieldset><legend><b>".$_SESSION['lang']['list']."</b></legend>
			<table  cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['kodebarang']."</td>
						<td align=center>".$_SESSION['lang']['namabarang']."</td>
						<td align=center>".$_SESSION['lang']['satuan']."</td>
						<td align=center>".$_SESSION['lang']['harga']."</td>
						<td align=center>".$_SESSION['lang']['jumlah']."<br>Awal</td>
						<td align=center>".$_SESSION['lang']['jumlah']."<br>Tersimpan</td>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>
						<td align=center>
							".$_SESSION['lang']['action']." 
							<br>
							<input type=checkbox id=cekall onclick=cekall()>
						</td>
					</tr>
				</thead><tbody id=contentdetail>";
		//echo $tab;	
		
		#jika data sudah tersimpan (mode edit)
		$str="select * from ".$dbname.".log_transaksi_vw where notransaksi='".$num."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jumbrgtran[$bar['kodebarang']]+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$kdpab."' and status=1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			$str1="select sum(jumlah) as jumlah,kodebarang,kodeblok from ".$dbname.".log_transaksi_vw where 
					notransaksireferensi='".$bar['kodepabrikasi']."' and kodebarang='".$bar['kodebarang']."' ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
				$jumlahsave=$bar1['jumlah'];
				if($jumlahsave==''){
					$jumlahsave=0;
				}
			if($jumbrgtran[$bar['kodebarang']]==''){
				$defjumlah=$bar['jumlah']-$jumlahsave;
			}else{
				$defjumlah=$jumbrgtran[$bar['kodebarang']];
			}
			
			
			if($defjumlah<1){
				$disabled="disabled";
			}else{
				$disabled="";
			}
			$no+=1;
            $tab.="<tr class=rowcontent id=row".$no.">";
            $tab.="<td align=center ".$disabled.">".$no."</td>";
            $tab.="<td align=left id=kdbrg".$no." ".$disabled.">".$bar['kodebarang']."</td>";
			$tab.="<td align=left ".$disabled.">".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=left ".$disabled.">".$satbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=right id=harsat".$no." ".$disabled.">".$bar['hargasatuan']."</td>";
			$tab.="<td align=right id=qtyawal".$no." ".$disabled.">".$bar['jumlah']."</td>";
			$tab.="<td align=right id=qtysave".$no." ".$disabled.">".$jumlahsave."</td>";
			$tab.="<td align=right ><input type=text ".$disabled."  id=qty".$no." value='".$defjumlah."' onkeypress='return angka_doang(event)' class=myinputtextnumber  style=width:90px></td>";
			$tab.="<td align=center ><input type=checkbox id=cek".$no." ".$disabled." ></td>";
			$tab.="</tr>";
		}
		$tab.="<tr  class=rowcontent>";
        $tab.="<td colspan=9 align=right ><button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['save']."</button></td>";
        $tab.="</tr>";  
		echo $num."###".$tab;
		
		
	break;
	
	case'deletehead':
	
		#update detail di cut off terhadapt detail gudang
		$str="select * from ".$dbname.".log_transaksidt where notransaksi='".$notran."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			#ambil dulu jumlah sudah di terima gudang 
			$stra="select jumlahterimagudang from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$bar['kodeblok']."' and kodebarang='".$bar['kodebarang']."' ";
			$resa=$owlPDO->query($stra) or die(print " Gagal: ".PDOException::getMessage());
			$resa->setFetchMode(PDO::FETCH_ASSOC);
			$bara=$resa->fetch();
				$jumcutgdg=$bara['jumlahterimagudang'];
	
			#bentuk pengurangan
			$jumlahupd=$jumcutgdg-$bar['jumlah'];
			
			#update cutoff jumlah terima gudang
			$strv="update ".$dbname.".pabrikasi_cutoffdt set jumlahterimagudang='".$jumlahupd."' where 
							kodepabrikasi='".$bar['kodeblok']."' and kodebarang='".$bar['kodebarang']."' ";
			try{
				$owlPDO->exec($strv);	
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		}
		
	
	
		$str="delete from  ".$dbname.".log_transaksiht where notransaksi='".$notran."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
	break;
	
	
	case'savedata':
		//exit("Error:$kdpab._.$kdbrg._.$qty._.$cek");
		#cek HT sudah ada apa belum
		if($cek>0){
			#delete 1st
			$str="delete from  ".$dbname.".log_transaksidt where notransaksi='".$notran."' and kodebarang='".$kdbrg."' and kodeblok='".$kdpab."' ";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
			if($qty>0){
				$str="select count(*) as jumlah from ".$dbname.".log_transaksiht where notransaksi='".$notran."' ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$adatran=$bar['jumlah'];
				
				if($adatran<1){
					#insert HT
					$str="
					INSERT INTO ".$dbname.".`log_transaksiht` (`tipetransaksi`, `notransaksi`, `tanggal`, `kodept`, `notransaksireferensi`,`kodegudang`)
					VALUES ('0','".$notran."','".$tgl."','".$_SESSION['empl']['kodeorganisasi']."','".$kdpab."','".$gudang."')";
					
					try{
							$owlPDO->exec($str);
						}
						catch (PDOException $e) {
						   print " Gagal  !: " . $e->getMessage() . "\n"; 
						   die(); 
						}
				}
				
					$qtycek=$qty+$qtysave;
					if($qtycek>$qtyawal){
						exit("Warning:Jumlah melebihi dari cutoff");
					}
				
					$str="
					INSERT INTO ".$dbname.".`log_transaksidt` (`notransaksi`, `kodebarang`, `satuan`, `jumlah`, `hargasatuan`, `kodeblok`, `updateby`,`kodesegment`)
					VALUES ('".$notran."','".$kdbrg."', '".$satbrg[$kdbrg]."', '".$qty."', '".$harsat."', '".$kdpab."','".$_SESSION['standard']['userid']."','0000000001')";				
					try{
						$owlPDO->exec($str);	
					}
					catch (PDOException $e) {
					   print " Gagal  !: " . $e->getMessage() . "\n"; 
					   die(); 
					}
					
					$str="select sum(jumlah) as jumlah,kodebarang,kodeblok from ".$dbname.".log_transaksi_vw where 
							notransaksireferensi='".$kdpab."' and kodebarang='".$kdbrg."' ";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$jumlahgudang=$bar['jumlah'];
						if($jumlahgudang==''){
							$jumlahgudang=0;
						}
					
					$str="update ".$dbname.".pabrikasi_cutoffdt set jumlahterimagudang='".$jumlahgudang."' where 
							kodepabrikasi='".$kdpab."' and kodebarang='".$kdbrg."' ";
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
	
	
	
	case'getkdso':
	
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." Kode Sales Order</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>Kode / Nama</td>
                            <td colspan=5>: 
                                    <input type=text id=carilistkdso class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                    <button class=mybutton onclick=getlistkdso()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listkdso>
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>Kode</td>
							<td>Nama</td>
                    </tr></thead>";

                    // if($carilistkdso!=''){
                        $str="select kodepabrikasi,namapabrikasi from ".$dbname.".pabrikasi_5masterht where 1=1
						and status=1 and (kodepabrikasi like '%".$carilistkdso."%' or namapabrikasi like '%".$carilistkdso."%') ";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()) {
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movedatakdso('".$bar['kodepabrikasi']."','".$bar['namapabrikasi']."');\">
                                    <td>".$no."</td>
                                    <td>".$bar['kodepabrikasi']."</td>
									 <td>".$bar['namapabrikasi']."</td>
                            </tr>";
                        }
                   // }
                    echo"</table>
        </fieldset>";
	
    break;
	
	
	
	
	case'listdetail':
	
		$tab="<fieldset><legend><b>".$_SESSION['lang']['list']."</b></legend>
			<table  cellpading=1 cellspacing=1 border=0 class=sortable>
				<thead>
					<tr>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['kodebarang']."</td>
						<td align=center>".$_SESSION['lang']['namabarang']."</td>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>
						<td align=center>".$_SESSION['lang']['persen']."</td>
						<td align=center>".$_SESSION['lang']['total']."</td>
						<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
						<td align=center>Jumlah Terima Gudang</td>
						
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		$no=0;
        $str="SELECT * from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$kdpab."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodebarang']."</td>";
			$tab.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=right>".$bar['jumlah']."</td>";
			$tab.="<td align=right>".$bar['persenbeban']."</td>";
			$tab.="<td align=right>".$bar['hargatotal']."</td>";
			$tab.="<td align=right>".$bar['hargasatuan']."</td>";
			$tab.="<td align=right></td>";
            $tab.="
            <td align=center>";
			if($bar['status']==1){
				$tab.="Posted";
			}else{
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editdetail('".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."','".$bar['jumlah']."',
										  '".$bar['persenbeban']."','".$bar['hargatotal']."','".$bar['hargasatuan']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletedetail('".$bar['kodepabrikasi']."','".$bar['kodebarang']."');\">  
				<img src=images/skyblue/posting.png class=zImgBtn title='Posting' 
                     onclick=\"postingdt('".$bar['kodepabrikasi']."','".$bar['kodebarang']."');\">  		
				";
			}
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
        if($tglsch!='') {
			$where.=" and tanggal='".$tglsch."%' ";
        }
		if($notransch!='') {
			$where.=" and notransaksi like '%".$notransch."%' ";
        }
		
        $str="select count(*) as jmlhrow from ".$dbname.".log_transaksiht where 1=1 and tipetransaksi=0 and notransaksireferensi like 'PB%' ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	
		
        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".log_transaksiht where 1=1 and tipetransaksi=0 and notransaksireferensi like 'PB%' ".$where."   limit ".$offset.",".$limit."";
	
		$tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['notransaksi']."</td>";
			$tab.="<td align=left>".$bar['notransaksireferensi']."</td>";
			$tab.="<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=left>".$arrpost[$bar['post']]."</td>";
            $tab.="
            <td align=center>";
			if($bar['post']==0){
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editht('".$bar['notransaksi']."','".$bar['kodegudang']."','".tanggalnormal($bar['tanggal'])."',
					 '".$bar['notransaksireferensi']."','".$nmpab[$bar['notransaksireferensi']]."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['notransaksi']."');\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_transaksi_vw','".$bar['notransaksi']."','','log_slave_pakaibarang_pabrikasi_pdf',event)\">
				";
			}else{
				$tab.="<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_transaksi_vw','".$bar['notransaksi']."','','log_slave_pakaibarang_pabrikasi_pdf',event)\">";
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
            <tr><td colspan=20 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
 
	
    
    
    default;
	
}
?>