<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$method = checkPostGet('method', '');
$kdpab = checkPostGet('kdpab', '');
$kdso = checkPostGet('kdso', '');
$stat = checkPostGet('stat', '');
$carilistkdpab=checkPostGet('carilistkdpab','');
$carilistkdso=checkPostGet('carilistkdso','');

$tahapan=checkPostGet('tahapan','');
$kelby=checkPostGet('kelby','');
$jumlah=checkPostGet('jumlah','');
$biaya=checkPostGet('biaya','');

$kdbrg=checkPostGet('kdbrg', '');
$namaBarangCari=checkPostGet('namaBarangCari','');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmpb=makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi');
$schkdso=checkPostGet('schkdso', '');
$schkdpab=checkPostGet('schkdpab', '');
$schstat=checkPostGet('schstat', '');
$arrst=array("0"=>"Waiting","1"=>"Open","2"=>"Cancel","3"=>"Close");


$cs=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');

switch ($method) 
{
	case'getkdso':
	
        echo"<fieldset>
                    <table cellspacing=1 border=0 class=data width=100%>
                        <tr>
                            <td colspan=2>Search </td>
                            <td colspan=5>: 
                                    <input type=text id=carilistkdso class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=getlistkdso()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listkdso cellspacing=1 border=0 class=sortable width=100%>
                    <thead>
                    <tr class=rowheader>
                            <td align=center width=30px>No</td>
                            <td align=center width=100px>".$_SESSION['lang']['kodesalesorder']."</td>
                            <td align=center>".$_SESSION['lang']['nmcust']."</td>
                    </tr></thead>";

                    if($carilistkdso!=''){
						$str="SELECT distinct(a.kodeso) as kodeso, b.kodecustomer from ".$dbname.".pabrikasi_salesdt a left join ".$dbname.".pabrikasi_salesht b on a.kodeso=b.kodeso	
								where a.kodeso like '%".$carilistkdso."%' and a.status=1 and b.kodept='".$_SESSION['empl']['kodeorganisasi']."' ";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch())
                        {
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movedatakdso('".$bar['kodeso']."');\">
                                    <td align=center>".$no."</td>
                                    <td>".$bar['kodeso']."</td>
                                    <td>".$cs[$bar['kodecustomer']]."</td>
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
	
    break;
	
	case'gettahapan':
		$str="select idtahapan,tahapan from ".$dbname.".pabrikasi_5masterdt where kodepabrikasi='".$kdpab."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$opttahapan.="<option value=" . $bar['idtahapan'] . ">" . $bar['idtahapan'] . " - " . $bar['tahapan'] . "</option>";
		}
		echo $opttahapan;
	break;
	
	case'getListBarang':
	echo"<fieldset  style='float:left;' >
			<legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
				<table cellspacing=1 border=0 class=data>
					<tr>
						<td colspan=2>".$_SESSION['lang']['namabarang']."</td>

						<td colspan=5>: 
								<input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								<button class=mybutton onclick=cariListBarang()>cari</button>
						<td>
					<tr>
				</table>

				<table id=listCariBarang >
				<thead>
				<tr class=rowheader>
						<td>No</td>
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>    
				</tr></thead>";

				if($namaBarangCari!=''){
					$str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch())
					{
						$no+=1;
						echo"
						<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$bar['kodebarang']."','".$bar['namabarang']."');\">
								<td>".$no."</td>
								<td>".$bar['kodebarang']."</td>
								<td>".$bar['namabarang']."</td>
						</tr>";
					}
				}
				echo"</table>
        </fieldset>";
	
    break;
	
	
	

	case'getkdpab':
	
        echo"<fieldset  style='float:left;' >
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>Search </td>
                            <td colspan=5>: 
                                    <input type=text id=carilistkdpab  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=getlistkdpab()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listkdpab cellspacing=1 border=0 class=sortable width=100%>
                    <thead>
                    <tr class=rowheader>
                            <td align=center>No</td>
                            <td align=center>".$_SESSION['lang']['kodepabrikasi']."</td>
                            <td align=center>".$_SESSION['lang']['namapabrikasi']."</td>
                    </tr></thead>";

                    if($carilistkdpab!=''){
                        $str="select kodepabrikasi,namapabrikasi from ".$dbname.".pabrikasi_5masterht where kodepabrikasi like '%".$carilistkdpab."%' or 
						namapabrikasi like '%".$carilistkdpab."%' ";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch())
                        {
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movedata('".$bar['kodepabrikasi']."');\">
                                    <td align=center>".$no."</td>
                                    <td>".$bar['kodepabrikasi']."</td>
                                    <td>".$bar['namapabrikasi']."</td>
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
	
    break;

	
	case'updatehead':

		$str="update  ".$dbname.".`pabrikasi_rabht` set kodeso='".$kdso."',status='".$stat."',updateby='".$_SESSION['standard']['userid']."'
				where kodepabrikasi='".$kdpab."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
	break;
	
	
	
	case'savedetail':
	
	
		$str="select count(*) as jumlah from ".$dbname.".pabrikasi_rabdt where kodepabrikasi='".$kdpab."' and idtahapan='".$tahapan."'
				and kelompokrab='".$kelby."' and kodebarang='".$kdbrg."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		if($bar['jumlah']>0){
			exit("Error:Data sudah di-input");
		}
	
		$str="select tahapan from ".$dbname.".pabrikasi_5masterdt where kodepabrikasi='".$kdpab."' and idtahapan='".$tahapan."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namatahapan=$bar['tahapan'];
	
		$str="INSERT INTO ".$dbname.".`pabrikasi_rabdt` 
			(`kodepabrikasi`,`idtahapan`,`tahapan`,`kelompokrab`, `kodebarang`, `jumlah`,`biaya`,`updateby`) 
			VALUES ('".$kdpab."','".$tahapan."','".$namatahapan."','".$kelby."', '".$kdbrg."','".$jumlah."','".($biaya*$jumlah)."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
	break;
	
	case'updatedetail':
	
	
	
		$str="update  ".$dbname.".`pabrikasi_rabdt` set jumlah='".$jumlah."' ,biaya='".($biaya*$jumlah)."'
				where 	kodepabrikasi='".$kdpab."' and idtahapan='".$tahapan."' and kelompokrab='".$kelby."' and kodebarang='".$kdbrg."' ";
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
						<td>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['tahapan']."</td>
						<td>".$_SESSION['lang']['kelompokrab']."</td>
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>".$_SESSION['lang']['biaya']."</td>
						<td>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		$no=0;
        $str="SELECT * from ".$dbname.".pabrikasi_rabdt where kodepabrikasi='".$kdpab."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['tahapan']."</td>";
			$tab.="<td align=left>".$bar['kelompokrab']."</td>";
            $tab.="<td align=left>".$bar['kodebarang']."</td>";
			$tab.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=right>".number_format($bar['jumlah'])."</td>";
			$tab.="<td align=left>".number_format($bar['biaya'])."</td>";
            $tab.="
            <td align=center>";
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editdetail('".$bar['idtahapan']."','".$bar['kelompokrab']."','".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."',
					 '".$bar['jumlah']."','".($bar['biaya']/$bar['jumlah'])."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletedetail('".$bar['kodepabrikasi']."','".$bar['idtahapan']."','".$bar['kelompokrab']."','".$bar['kodebarang']."');\">     
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
		if($schkdso!='') {
			$where.=" and kodeso like '%".$schkdso."%' ";
        }
		if($schstat!='') {
			$where.=" and status like '%".$schstat."%' ";
        }

        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_rabht where 1=1 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	

        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".pabrikasi_rabht where 1=1 ".$where."   limit ".$offset.",".$limit."";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodepabrikasi']."</td>";
			$tab.="<td align=left>".$nmpb[$bar['kodepabrikasi']]."</td>";
			$tab.="<td align=left>".$bar['kodeso']."</td>";
			$tab.="<td align=center>".$arrst[$bar['status']]."</td>";
			
            $tab.="
            <td align=center>";
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"edit('".$bar['kodepabrikasi']."','".$bar['kodeso']."','".$bar['status']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['kodepabrikasi']."');\">
				
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_rabht','".$bar['kodepabrikasi']."','','pabrikasi_slave_rab_pdf',event)\">";
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
            <tr><td colspan=6 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;

	case'savehead':
		
		#cek apakah sudah ada di-input atau belum
		$str="select count(*) as jumlah from ".$dbname.".pabrikasi_rabht where kodepabrikasi='".$kdpab."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		if($bar['jumlah']>0){
			exit("Error:Data sudah di-input");
		}
		
		$str="INSERT INTO ".$dbname.".`pabrikasi_rabht` 
			(`kodepabrikasi`, `kodeso`, `status`,`updateby`) 
			VALUES ('".$kdpab."', '".$kdso."', '".$stat."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	
		
	break;
		
	case'deletedetail':
		$str="delete from ".$dbname.".pabrikasi_rabdt where kodepabrikasi='".$kdpab."' and idtahapan='".$tahapan."' 
				and kelompokrab='".$kelby."' and kodebarang='".$kdbrg."'";		    
				//exit("Error:$str");
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'deletehead':
		$str="delete from ".$dbname.".pabrikasi_rabht where kodepabrikasi='".$kdpab."' ";	
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