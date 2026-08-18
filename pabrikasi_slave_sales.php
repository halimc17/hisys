<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$method = checkPostGet('method', '');
$kdpt=checkPostGet('kdpt', '');
$kdso=checkPostGet('kdso', '');
$kdcus=checkPostGet('kdcus', '');
$tglorder=tanggalsystemn(checkPostGet('tglorder', ''));
$tglmulai=tanggalsystemn(checkPostGet('tglmulai', ''));
$tglselesai=tanggalsystemn(checkPostGet('tglselesai', ''));
$nopo=checkPostGet('nopo', '');

$salesid=checkPostGet('salesid', '');

$kdbrg=checkPostGet('kdbrg', '');
$jum=checkPostGet('jum', '');
$ket=checkPostGet('ket', '');
$stat=checkPostGet('stat', '');

$schkdso=checkPostGet('schkdso', '');

$namaBarangCari=checkPostGet('namaBarangCari','');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmcus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$arrst=array("0"=>"Waiting","1"=>"Open","2"=>"Cancel","3"=>"Close");

switch ($method) 
{
	case'getListBarang':
	
        echo"<fieldset  style='float:left;' >
               
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>Search</td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listCariBarang  cellspacing=1 border=0 class=sortable>
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>".$_SESSION['lang']['kodebarang']."</td>
                            <td>".$_SESSION['lang']['namabarang']."</td>
                            <td>".$_SESSION['lang']['satuan']."</td>
                            <td>".$_SESSION['lang']['jumlah']."</td>
                            <td>".$_SESSION['lang']['harga']."</td>    
                    </tr></thead>";

                    if($namaBarangCari!=''){
                        $str="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()){
							
							$str1="select hargalastout,saldoqty from ".$dbname.".log_5masterbarangdt where kodebarang='".$bar['kodebarang']."' 
									and kodeorg='".$_SESSION['empl']['kodeorganisasi']."' and kodegudang='".$_SESSION['empl']['lokasitugas']."20' ";
							$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
							$res1->setFetchMode(PDO::FETCH_ASSOC);
							$bar1=$res1->fetch();
							
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$bar['kodebarang']."','".$bar['namabarang']."','".$bar1['hargalastout']."');\">
                                    <td align=center>".$no."</td>
                                    <td>".$bar['kodebarang']."</td>
                                    <td>".$bar['namabarang']."</td>
									<td>".$bar['satuan']."</td>
									<td align=right>".number_format($bar1['saldoqty'],2)."</td>
									<td align=right>".number_format($bar1['hargalastout'])."</td>
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
	
    break;

	
	case'updatehead':

		$str="update  ".$dbname.".`pabrikasi_salesht` set kodecustomer='".$kdcus."',tanggalpesan='".$tglorder."',nopo='".$nopo."',
				tanggalmulai='".$tglmulai."',tanggalsampai='".$tglselesai."',salesid='".$salesid."',
				updateby='".$_SESSION['standard']['userid']."'
				where kodeso='".$kdso."' and kodept='".$kdpt."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
	break;
	
	
	
	case'savedetail':
		$str="INSERT INTO ".$dbname.".`pabrikasi_salesdt` 
			(`kodeso`, `kodebarang`,`keterangan`, `jumlah`,`status`,`updateby`) 
			VALUES ('".$kdso."', '".$kdbrg."','".$ket."', '".$jum."','".$stat."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
	break;
	
	case'updatedetail':
	
		$str="update  ".$dbname.".`pabrikasi_salesdt` set keterangan='".$ket."',jumlah='".$jum."' ,status='".$stat."'
				where 	kodeso='".$kdso."' and kodebarang='".$kdbrg."'";
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
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>".$_SESSION['lang']['keterangan']."</td>
						<td>".$_SESSION['lang']['status']."</td>
						<td>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>";
		$no=0;
        $str="SELECT * from ".$dbname.".pabrikasi_salesdt where kodeso='".$kdso."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodebarang']."</td>";
			$tab.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=right>".$bar['jumlah']."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
			$tab.="<td align=left>".$arrst[$bar['status']]."</td>";
            $tab.="
            <td align=center>";
				if($bar['status']=='1' or $bar['status']=='2'){
					
				}else{
					$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"editdetail('".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."','".$bar['jumlah']."','".$bar['keterangan']."','".$bar['status']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletedetail('".$bar['kodeso']."','".$bar['kodebarang']."');\">     
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
        if($schkdso!='') {
			$where.=" and kodeso like '%".$schkdso."%' ";
        }

        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_salesht where 1=1 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	

        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".pabrikasi_salesht where 1=1 ".$where."   limit ".$offset.",".$limit."";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodept']."</td>";
			$tab.="<td align=left>".$bar['kodeso']."</td>";
			$tab.="<td align=left>".$nmcus[$bar['kodecustomer']]."</td>";
			$tab.="<td align=left>".tanggalnormal($bar['tanggalpesan'])."</td>";
            $tab.="
            <td align=center>";
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"edit('".$bar['kodept']."','".$bar['kodeso']."','".$bar['kodecustomer']."',
					 '".tanggalnormal($bar['tanggalpesan'])."','".$bar['nopo']."','".tanggalnormal($bar['tanggalmulai'])."',
					 '".tanggalnormal($bar['tanggalsampai'])."','".$bar['salesid']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['kodept']."','".$bar['kodeso']."');\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_salesht','".$bar['kodeso']."','','pabrikasi_slave_sales_pdf',event)\">";
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
		
		$str="INSERT INTO ".$dbname.".`pabrikasi_salesht` 
			(`kodept`, `kodeso`, `kodecustomer`, `tanggalpesan`,
			`nopo`, `tanggalmulai`, `tanggalsampai`,`salesid`,`updateby`) 
			VALUES ('".$kdpt."', '".$kdso."', '".$kdcus."', '".$tglorder."',
					'".$nopo."', '".$tglmulai."','".$tglselesai."','".$salesid."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
	break;
		
	case'deletedetail':
		$str="delete from ".$dbname.".pabrikasi_salesdt where kodeso='".$kdso."' and kodebarang='".$kdbrg."'";		    
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'deletehead':
		#cek status di dt
		$str="SELECT sum(status) as jumlah from ".$dbname.".pabrikasi_salesdt where kodeso='".$kdso."' and status<3 ";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jum=$bar['jumlah'];
				
		if($jum>0){
			exit("Error:Sudah ada persetujuan didetail");
		}	
		
	
		$str="delete from ".$dbname.".pabrikasi_salesht where kodeso='".$kdso."' and kodept='".$kdpt."'";
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