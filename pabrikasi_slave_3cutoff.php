<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');

$kdpab=checkPostGet('kdpab', '');
$nmpab=checkPostGet('nmpab', '');
$tgl1=tanggalsystemn(checkPostGet('tgl1', ''));
$persen=checkPostGet('persen', '');
$total=checkPostGet('total', '');

$kdbrgdt=checkPostGet('kdbrgdt', '');
$jumlahdt=checkPostGet('jumlahdt', '');
$persendt=checkPostGet('persendt', '');
$hargadt=checkPostGet('hargadt', '');
$hargasatdt=checkPostGet('hargasatdt', '');
$carilistkdso=checkPostGet('carilistkdso', '');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmpbrk=makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi');
$namaBarangCari=checkPostGet('namaBarangCari','');
$schkdpab=checkPostGet('schkdpab', '');
$kdso=checkPostGet('kdso', '');
$stat=checkPostGet('stat', '');
$arrst=array("1"=>"Open","2"=>"Cancel","3"=>"Close");
//exit("Error:$kdso");
switch ($method) {
	
	case'postht':
	
		#update cuttoff
		$str="update  ".$dbname.".`pabrikasi_cutoffht` set status='".$stat."' where kodepabrikasi='".$kdpab."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		#update transaksi pabrikasi
		$str="update  ".$dbname.".`pabrikasi_5masterht` set status='".$stat."' where kodepabrikasi='".$kdpab."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	
	break;
	
	case'getformpostinght':
		$form="";
		$form.="<fieldset>
                <legend>".$_SESSION['lang']['keterangan']."</legend>
                    <table cellspacing=1 border=0 class=data style='width:100%;'>
					<thead>
						<tr>
							<td colspan=2>".$_SESSION['lang']['keterangan']."</td>
							<td>Bahasa Indonesia</td>
							<td>English Language</td>
						</tr>
					</thead>
                        <tr class=rowcontent>
							<td valign=top>Close</td>
							<td valign=top>:</td>
							<td valign=top>Menutup Master Pabrikasi untuk nomor (".$kdpab.")</td>
							<td valign=top>Close all the manufacturing transaction number (".$kdpab.")</td>
						</tr>
						<tr class=rowcontent>
							<td valign=top>Cancel</td>
							<td valign=top>:</td>
							<td valign=top>Meng-cancel Master Pabrikasi untuk nomor (".$kdpab.")</td>
							<td valign=top>Cancel all the manufacturing transaction number (".$kdpab.")</td>
						</tr>
					</table>
				</fieldset><br>";
		$form.="<fieldset style='float:left;'>
                <legend>".$_SESSION['lang']['action']." ".$kdpab."</legend>
                    <table cellspacing=1 border=0 class=data style='width:100%;'>
                        <tr>
						<td><button class=mybutton onclick=postht('".$kdpab."',2)>Cancel</button>
							<button class=mybutton onclick=postht('".$kdpab."',3)>Close</button></td>
						</tr>
					</table>
				</fieldset>";
		echo $form;		
	break;
	
	
	case'getListBarang':
	
        echo"<fieldset  style='float:left;' >
                
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['namabarang']."</td>
                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listCariBarang cellspacing=1 border=0 class=sortable width=100%>
                    <thead>
                    <tr class=rowheader>
                            <td align=center>No</td>
                            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                            <td align=center>".$_SESSION['lang']['namabarang']."</td>
                            <td align=center>".$_SESSION['lang']['satuan']."</td>
                            <td align=center>".$_SESSION['lang']['jumlah']."</td>    
                    </tr></thead>";

                    if($namaBarangCari!=''){
						if($kdso!=''){
							$str="select a.kodebarang,b.namabarang,b.satuan,a.jumlah from ".$dbname.".pabrikasi_salesdt a
								left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
								where b.namabarang like '%".$namaBarangCari."%' and a.kodeso='".$kdso."' ";
							$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while($bar=$res->fetch()){
								@$no+=1;
								echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$bar['kodebarang']."','".$bar['namabarang']."','".$bar['jumlah']."');\">
										<td align=center>".$no."</td>
										<td>".$bar['kodebarang']."</td>
										<td>".$bar['namabarang']."</td>
										<td>".$bar['satuan']."</td>
										<td align=right>".number_format($bar['jumlah'],2)."</td>
										
								</tr>";
							}
						}else{
							$str="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
							$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while($bar=$res->fetch()){							
								@$no+=1;
								echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$bar['kodebarang']."','".$bar['namabarang']."','".$bar['jumlah']."');\">
										<td  align=center>".$no."</td>
										<td>".$bar['kodebarang']."</td>
										<td>".$bar['namabarang']."</td>
										<td>".$bar['satuan']."</td>
										<td align=right>".number_format($bar['jumlah'],2)."</td>
								</tr>";
							}
						}
                        
                    }
                    echo"</table>
        </fieldset>";
	
    break;
	
	
	
	case'getkdso'://hanya nama fungsinya saja getkdso, pencarian untuk kodepabrikasi
	
        echo"<fieldset  style='float:left;' >
                
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>Search</td>
                            <td colspan=5>: 
                                    <input type=text id=carilistkdso class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=getlistkdso()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listkdso cellspacing=1 border=0 class=sortable width=100%>
                    <thead>
                    <tr class=rowheader>
                            <td align=center>No</td>
                            <td align=center>".$_SESSION['lang']['kodepabrikasi']."</td>
							<td align=center>".$_SESSION['lang']['namapabrikasi']."</td>
							<td align=center>".$_SESSION['lang']['kodesalesorder']."</td>
							<td align=center>".$_SESSION['lang']['rupiah']."</td>
                    </tr></thead>";

                     if($carilistkdso!=''){
                        $str="select kodepabrikasi,namapabrikasi,kodesalesorder from ".$dbname.".pabrikasi_5masterht where 1=1
						and status=1 and (kodepabrikasi like '%".$carilistkdso."%' or namapabrikasi like '%".$carilistkdso."%') ";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()) {
							
							$str1="select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodeblok='".$bar['kodepabrikasi']."'
									and (noakun like '634%' and noakun!='6340199') ";
							$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
							$res1->setFetchMode(PDO::FETCH_ASSOC);
							$bar1=$res1->fetch();
								$jumjurnal=$bar1['jumlah'];
								
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movedatakdso('".$bar['kodepabrikasi']."','".$bar['namapabrikasi']."','".$bar['kodesalesorder']."','".$jumjurnal."');\">
                                    <td align=center>".$no."</td>
                                    <td>".$bar['kodepabrikasi']."</td>
									<td>".$bar['namapabrikasi']."</td>
									<td>".$bar['kodesalesorder']."</td>
									 <td align=right>".number_format($jumjurnal)."</td>
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
	
    break;
	
	
	case'updatehead':
		$str="update  ".$dbname.".`pabrikasi_5masterht` set namapabrikasi='".$nmpab."',kodekelompok='".$kdkel."',kodesalesorder='".$kdso."',
				tanggalmulai='".$tgl1."',tanggalselesai='".$tgl2."',status='".$stat."' where kodepabrikasi='".$kdpab."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	
	
	
	
	case'savedetail':
	
		#cek untuk persen total di detail tidak boleh lebih dr 100
		$str="select sum(persenbeban) as totalpersen from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$kdpab."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$totalpersen=$bar['totalpersen'];
			
		$totalx=$totalpersen+$persendt;

		if($totalx>100){
			exit("Warning:Persen melebihi 100%");
		}
	
	
		$str="INSERT INTO `pabrikasi_cutoffdt` (`kodepabrikasi`, `kodebarang`, `jumlah`,`persenbeban`, `hargatotal`,`hargasatuan`,`updateby`)
			VALUES ('".$kdpab."', '".$kdbrgdt."','".$jumlahdt."', '".$persendt."', '".$hargadt."','".$hargasatdt."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'updatedetail':
	
		$str="update  ".$dbname.".`pabrikasi_cutoffdt` set jumlah='".$jumlahdt."',persenbeban='".$persendt."',hargatotal='".$hargadt."',hargasatuan='".$hargasatdt."'
				where kodepabrikasi='".$kdpab."' and kodebarang='".$kdbrgdt."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'listdetail':
	
		$tab="<fieldset  style=width:750px><legend><b>".$_SESSION['lang']['list']."</b></legend>
			<table  cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
				<thead>
					<tr>
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['kodebarang']."</td>
						<td align=center>".$_SESSION['lang']['namabarang']."</td>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>
						<td align=center>".$_SESSION['lang']['persen']."</td>
						<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
						<td align=center>".$_SESSION['lang']['total']."</td>
						
						<td align=center>".$_SESSION['lang']['jumlah']."<br>".$_SESSION['lang']['diterima']." ".$_SESSION['lang']['gudang']."</td>
						
						<td align=center width=50px>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		$no=0;
        $str="SELECT * from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$kdpab."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			#jumlah gudang
			$str1="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where notransaksireferensi='".$bar['kodepabrikasi']."'
				and tipetransaksi='0' and kodebarang='".$bar['kodebarang']."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
				//$jumjurnal=$bar1['jumlah'];
			
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodebarang']."</td>";
			$tab.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=right>".number_format($bar['jumlah'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['persenbeban'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['hargasatuan'],2)."</td>";
			$tab.="<td align=right>".number_format($bar['hargatotal'],2)."</td>";
			
			$tab.="<td align=right>".number_format($bar1['jumlah'],2)."</td>";
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
        if($schkdpab!='') {
			$where.=" and kodepabrikasi like '%".$schkdpab."%' ";
        }

        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_cutoffht where 1=1 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	


        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".pabrikasi_cutoffht where 1=1 ".$where."   limit ".$offset.",".$limit."";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['kodepabrikasi']."</td>";
			$tab.="<td align=left>".$nmpbrk[$bar['kodepabrikasi']]."</td>";
			$tab.="<td align=left>".$bar['kodeso']."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggalcutoff'])."</td>";
			$tab.="<td align=right>".number_format($bar['total'],2)."</td>";
			$tab.="<td align=left>".$arrst[$bar['status']]."</td>";
            $tab.="
            <td align=center>";
			if($bar['status']==1){
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"edit('".$bar['kodepabrikasi']."','".$nmpbrk[$bar['kodepabrikasi']]."','".$bar['tanggalcutoff']."',
					 '".$bar['persen']."','".$bar['total']."','".$bar['kodeso']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['kodepabrikasi']."');\">
				<img src=images/skyblue/posting.png class=zImgBtn title='Posting' onclick=\"postinght('".$_SESSION['lang']['cari']."','event','".$bar['kodepabrikasi']."');\">  	
				";
			}	
			$tab.="<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_3cutoff','".$bar['kodepabrikasi']."','','pabrikasi_slave_3cutoff_pdf',event)\">";	
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
            <tr><td colspan=8 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
 
	
	case'savehead':
	
		
		$str="INSERT INTO ".$dbname.".`pabrikasi_cutoffht` (`kodepabrikasi`, `tanggalcutoff`, `persen`,`total`,`kodeso`,`updateby`,`status`) 
			VALUES ('".$kdpab."','".$tgl1."', '".$persen."','".$total."','".$kdso."','".$_SESSION['standard']['userid']."','1')";
		//	exit("Error:$str");
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
		
	case'deletedetail':
		$str="delete from ".$dbname.".pabrikasi_cutoffdt where kodepabrikasi='".$kdpab."' and kodebarang='".$kdbrgdt."'";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'deletehead':
	
		$str="delete from ".$dbname.".pabrikasi_cutoffht where kodepabrikasi='".$kdpab."'";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
    
    case'postingdt':
		$str="update  ".$dbname.".pabrikasi_cutoffdt set status='1' where kodepabrikasi='".$kdpab."' and kodebarang='".$kdbrgdt."'";		    
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