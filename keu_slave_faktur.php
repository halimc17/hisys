<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');

$id = checkPostGet('id','');	
$pt = checkPostGet('pt','');	
$ptlama = checkPostGet('ptlama','');	
$npwp = checkPostGet('npwp','');	
$fakturawal = checkPostGet('fakturawal','');	
$fakturawallama = checkPostGet('fakturawallama','');	
$fakturakhir = checkPostGet('fakturakhir','');	
$fakturakhirlama = checkPostGet('fakturakhirlama','');	
$jumlah = checkPostGet('jumlah','');	
$cariPt = checkPostGet('cariPt','');	
$cariStatus = checkPostGet('cariStatus','');	
$nofaktur = checkPostGet('nofaktur','');	
$tipe = checkPostGet('tipe','');	
$method = checkPostGet('method','');

$arrst=array("0"=>$_SESSION['lang']['tidakaktif'],"1"=>$_SESSION['lang']['aktif']);
@$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
@$namaPerusahaan=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
switch($method)
{	
	case'getnpwp':
		$str = "select * from " . $dbname . ".setup_org_npwp where kodeorg ='".$pt."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optnpwp.="<option value='" . $bar['npwp'] . "'>" . $bar['npwp'] . "</option>";
		}
		
	echo $optnpwp;
	break;
    case 'insert':
                $jml=substr($fakturakhir,7,8)-substr($fakturawal,7,8);
				if($fakturawal>$fakturakhir){
					exit('Error : Nomor faktur akhir tidak boleh lebih kecil dari nomor faktur awal.');
				}
				if(($jml+1)>100){
					#exit('Error : Jumlah faktur terlalu banyak, maksimal 100');
				}
				$str="select max(id) as id from ".$dbname.".keu_fakturpajakht order by id desc";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);      
                $bar=$res->fetch();
				$maxno=$bar['id'];
				if($maxno==0){
					$noid=1;
				}else{
					$noid=$maxno+1;
				}
				
				$countFak="select * from ".$dbname.".keu_fakturpajakht where pt='".$pt."' and nofakturawal='".$fakturawal."' and nofakturakhir='".$fakturakhir."'";
				
				$res=$owlPDO->query($countFak) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $numrows=owlBaris($res);
                if($numrows>= 1){
                        echo " Gagal, No Faktur sudah pernah terdaftar sebelumnya.";
                }else{
					//insert ht
					$i="insert into ".$dbname.".keu_fakturpajakht (id,pt,npwp,nofakturawal,nofakturakhir,jumlah,updateby)
						values ('".$noid."','".$pt."','".$npwp."','".$fakturawal."','".$fakturakhir."','".$jumlah."','".$_SESSION['standard']['userid']."')"; 
                        try{$owlPDO->exec($i); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
					//insert dt	
					$pawaltemp=substr($fakturawal,7,8);
					$noawal=substr($fakturawal,0,7);
					
					

					for($i=1;$i<=$jumlah;$i++){
						$n=$pawaltemp+($i-1);
						$potong=strlen($n);

						if ($potong==2){
								$no=substr($pawaltemp,0,6);
						}if ($potong==3) {
								$no=substr($pawaltemp,0,5);
						}else if ($potong==4) {
								$no=substr($pawaltemp,0,4);
						}else if ($potong==5) {
								$no=substr($pawaltemp,0,3);
						}else if ($potong==6) {
								$no=substr($pawaltemp,0,2);
						}else if ($potong==7) {
								$no=substr($pawaltemp,0,1);
						}else if ($potong==8) {
								$no=substr($pawaltemp,0,0);
						}
						$str="insert into ".$dbname.".keu_fakturpajakdt (id,pt,faktur,updateby)
							values ('".$noid."','".$pt."','".$noawal.$no.$n."','".$_SESSION['standard']['userid']."')";
							//exit('error'.$no);
							try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
					}


				}

    break;

    case 'update':
			$jml=substr($fakturakhir,11,8)-substr($fakturawal,11,8);
			if($fakturawal>$fakturakhir){
				exit('Error : Nomor faktur akhir tidak boleh lebih kecil dari nomor faktur awal.');
			}
			if(($jml+1)>100){
				exit('Error : Jumlah faktur terlalu banyak, maksimal 100');
			}
			
			$str="select count(notransaksi) as transaksi from ".$dbname.".keu_fakturpajakdt where id='".$id."' and notransaksi!=''";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			if($bar['transaksi']>0){
				exit('Error : Faktur tidak dapat di edit, sudah ada transaksi atas faktur ini.');
			}
			//hapus detail faktur
			$i="delete from ".$dbname.".keu_fakturpajakdt where id='".$id."'";
               try{$owlPDO->exec($i); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			
			//update ht
            $i="update ".$dbname.".keu_fakturpajakht set pt='".$pt."',npwp='".$npwp."',jumlah='".$jumlah."',"
            . " updateby='".$_SESSION['standard']['userid']."',nofakturawal='".$fakturawal."',nofakturakhir='".$fakturakhir."'
             where id='".$id."'";
             try{$owlPDO->exec($i); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			 
			//insert dt	
			$pawaltemp=substr($fakturawal,11,8);
			$noawal=substr($fakturawal,0,11);
				for($i=1;$i<=$jumlah;$i++){
					$n=$pawaltemp+($i-1);
					$potong=strlen($n);

					if ($potong==2)
					{
							$no=substr($pawaltemp,0,6);
					}
					if ($potong==3) 
					{
							$no=substr($pawaltemp,0,5);
					}
					else if ($potong==4) 
					{
							$no=substr($pawaltemp,0,4);
					}
					else if ($potong==5) 
					{
							$no=substr($pawaltemp,0,3);
					}
					else if ($potong==6) 
					{
							$no=substr($pawaltemp,0,2);
					}
					else if ($potong==7) 
					{
							$no=substr($pawaltemp,0,1);
					}
					else if ($potong==8) 
					{
							$no=substr($pawaltemp,0,0);
					}
					$str="insert into ".$dbname.".keu_fakturpajakdt (id,pt,faktur,updateby)
						values ('".$id."','".$pt."','".$noawal.$no.$n."','".$_SESSION['standard']['userid']."')";
						try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
    break;
	
	case 'exp':
			//update ht
            $i="update ".$dbname.".keu_fakturpajakdt set expired='1',"
            . " updateby='".$_SESSION['standard']['userid']."' where faktur='".$nofaktur."'";
             try{$owlPDO->exec($i); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		 echo"1";
    break;
	case 'unexp':
			//update ht
            $i="update ".$dbname.".keu_fakturpajakdt set expired='0',"
            . " updateby='".$_SESSION['standard']['userid']."' where faktur='".$nofaktur."'";
             try{$owlPDO->exec($i); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    break;

    case'loadData':
        echo"
            <div id=container>
                <table class=sortable cellspacing=1 cellpadding=3 border=0>
                    <thead>
                         <tr class=rowheader>
                                 <td align=center>".$_SESSION['lang']['nourut']."</td>
                                 <td align=center>".$_SESSION['lang']['pt']."</td>
                                 <td align=center>".$_SESSION['lang']['npwp']."</td>
                                 <td align=center>".$_SESSION['lang']['nofakturawal']."</td>
                                 <td align=center>".$_SESSION['lang']['nofakturakhir']."</td>
                                 <td align=center>".$_SESSION['lang']['jumlah']."</td>
                                 <td align=center>".$_SESSION['lang']['status']."</td>
                                 <td align=center>".$_SESSION['lang']['updateby']."</td>
                                 <td align=center colspan=4>".$_SESSION['lang']['action']."</td>
                         </tr>
                </thead>
                <tbody>";


                $limit=15;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
                $maxdisplay=($page*$limit);

                $ql2="select count(*) as jmlhrow from ".$dbname.".keu_fakturpajakht where pt like '%".$cariPt."%' and status like '%".$cariStatus."%'";
                $res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);                
                while($jsl=$res->fetch()){
                $jlhbrs= $jsl->jmlhrow;
                }
                
                $i="select * from ".$dbname.".keu_fakturpajakht where pt like '%".$cariPt."%' and status like '%".$cariStatus."%' order by updatetime desc limit ".$offset.",".$limit."";
                $res=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);      
                $no=$maxdisplay;
                while($d=$res->fetch()){
                    @$no+=1;
                    echo "<tr class=rowcontent id=tr_$no>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$d['pt']." - ".$namaPerusahaan[$d['pt']]."</td>";
                    echo "<td align=left>".$d['npwp']."</td>";
                    echo "<td align=left>".$d['nofakturawal']."</td>";
                    echo "<td align=left>".$d['nofakturakhir']."</td>";
                    echo "<td align=right>".number_format($d['jumlah'])."</td>";
                    echo "<td align=left>".$arrst[$d['status']]."</td>";
                    echo "<td align=left>".$nmKar[$d['updateby']]."</td>";
                    //echo "<td align=left>".$d['updatetime']."</td>";
					if($d['status']==0) {
						echo "<td align=center>
							<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$d['id']."','".$d['pt']."','".$d['npwp']."','".$d['nofakturawal']."','".$d['nofakturakhir']."','".$d['jumlah']."');\"></td>";
						echo"<td><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['id']."');\"></td>";
						echo"<td><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('" . $d['id'] . "','".$no."');\" ></td>";
					} else {
						echo "<td></td><td></td>";
						echo"<td><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted'></td>";
					}
					//echo"<td></td>";
					echo "<td align=center>
						<img src=images/skyblue/zoom.png class=resicon  caption='View Detail' onclick=\"detail('".$d['id']."','html','event');\"></td>";
                    echo "</tr>";
                }
				
				$totrows=ceil($jlhbrs/$limit);
				if($totrows==0)
				{
					$totrows=1;
				}
				$isiRow='';
				for($er=1;$er<=$totrows;$er++)
				{
				  $sel = ($page==$er-1)? 'selected': '';
				  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
				}
				
				
				echo"<tr><td colspan=12 align=center>";
				if($page<=0){
					echo"<button disabled class=mybutton onclick=cariBast(".($page-1).");>Prev</button>";
				}else{
					echo"<button class=mybutton onclick=cariBast(".($page-1).");>Prev</button>";
				}
				echo"<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
				if($page>=($er-2)){
					echo"<button disabled class=mybutton onclick=cariBast(".($page+1).");>Next</button>";
				}else{
					echo"<button class=mybutton onclick=cariBast(".($page+1).");>Next</button>";
				}
				echo"</td></tr>";
                echo"</tbody></table>";
		break;
		case'detail':
			$str="select * from ".$dbname.".keu_fakturpajakht where id = '".$id."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);      
			$bar=$res->fetch();
				$pawal=$bar['nofakturawal'];
				$pakhir=$bar['nofakturakhir'];
				$pjlh=$bar['jumlah'];
			
			if($tipe=='html'){
				$border=" border=0 ";
			}else{
				$border=" border=1 ";
			}
			
			$stream="<img 
			style=cursor:pointer; title='excel' 
			onclick=detailexcel('".$id."','excel','event') src=images/excel.jpg  
			title='MS.Excel' > ";	
			
			$stream.="<table class=sortable cellspacing=1 cellpadding=3 ".$border." width=100%>
                    
					 <tr class=rowcontent>
						 <td align=left>".$_SESSION['lang']['pt']."</td><td>:</td>
						 <td>".$bar['pt']." - ".$namaPerusahaan[$bar['pt']]."</td>
						 <td align=left>".$_SESSION['lang']['nofakturawal']."</td><td>:</td>
						 <td>".$bar['nofakturawal']."</td>
						 <td colspan=3></td>
					 </tr>
					 <tr class=rowcontent>
						 <td align=left>".$_SESSION['lang']['npwp']."</td><td>:</td>
						 <td>".$bar['npwp']."</td>
						 <td align=left>".$_SESSION['lang']['nofakturakhir']."</td><td>:</td>
						 <td>".$bar['nofakturakhir']."</td>
						 <td align=left>".$_SESSION['lang']['jumlah']."</td><td>:</td>
						 <td>".$bar['jumlah']."</td>
					 </tr>
					</table>
					<hr>";
			$stream.="<table class=sortable cellspacing=1 cellpadding=3 ".$border." width=100%>
                    <thead>
					 <tr class=rowheader>
						 <td align=center>".$_SESSION['lang']['nourut']."</td>
						 <td align=center>".$_SESSION['lang']['nofaktur']."</td>
						 <td align=center>".$_SESSION['lang']['notransaksi']."</td>
						 <td align=center>".$_SESSION['lang']['unit']."</td>
						 <td align=center>".$_SESSION['lang']['tanggal']."</td>
						 <td align=center>".$_SESSION['lang']['NoKontrak']."</td>
						 <td align=center colspan=3>Exp</td>
					 </tr>
					</thead>
                <tbody>";
			$arrexp=array("0"=>"","1"=>"Expired");
			$str="select a.*,b.kodeorg,b.kodept,b.tanggal,b.nokontrak,b.nilaiinvoice,b.noinvoice from ".$dbname.".keu_fakturpajakdt a left join ".$dbname.".keu_penagihanht b on a.notransaksi=b.noinvoice where a.id='".$id."'  order by a.faktur asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);      
			$no='';
			while($bar=$res->fetch()){
				$no+=1;
				$stream.="	<tr class=rowcontent id='trdet_".$no."'>
						<td align=center>".$no."</td>
						<td align=left>".$bar['faktur']."</td>";
				$style='';
				if($bar['nokontrak']!=''){
					$style="style=cursor:pointer title='click to view' onclick=\"masterPDF('keu_penagihanht','".$bar['notransaksi']."','','keu_slave_print_pengihan',event);\"";
				}						
				$stream.="	<td align=left ".$style."><font color=blue>".$bar['notransaksi']."</font></td>
						<td align=left>".$bar['kodeorg']."</td>
						<td align=left>".$bar['tanggal']."</td>";
				if($bar['notransaksi']!='' and $bar['nokontrak']==''){
					$stream.="<td align=left><i>Transaksi telah di hapus.</i></td>";
				}else{
					$stream.="<td align=left>".$bar['nokontrak']."</td>";	
				}
				$stream.="	<td align=left  width=50px><font color=red>".$arrexp[$bar['expired']]."</font></td>";
					$ceked='';
					if($bar['expired']==1){
							$ceked="checked";
						}
					if($bar['notransaksi']==''){
						$stream.="<td colspan=2 align=center width=5px><input title='Expired' type=checkbox ".$ceked." onclick=getexp('".$bar['faktur']."','".$no."') id='exp_".$no."'></td>";
					}else{
						$stream.="<td colspan=2 align=center width=5px><input type=checkbox ".$ceked." disabled></td>";
					}
				$stream.="</tr>";
			}
			$stream.="</tbody></table>";
	if($tipe=='excel'){
			$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
			$nop_="detail_faktur_".$pawal." sd ".$pakhir;
			if(strlen($stream)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$stream)){
					echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
						</script>";
					exit;
				}else {
					echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				}
				fclose($handle);
			}
			
		}else{
		   echo $stream;
		} 

		break;
        case 'delete':
			$str="select count(notransaksi) as transaksi from ".$dbname.".keu_fakturpajakdt where id='".$id."' and notransaksi!=''";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$barv = $res->fetch();
			if($barv['transaksi']>0){
				exit('Error : Faktur tidak dapat di hapus, sudah ada transaksi atas faktur ini.');
			}
			
			$dt="delete from ".$dbname.".keu_fakturpajakdt where id='".$id."'";
		    try{$owlPDO->exec($dt); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			
			$ht="delete from ".$dbname.".keu_fakturpajakht where id='".$id."'";
		    try{$owlPDO->exec($ht); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
		case'posting':
			$dt="update ".$dbname.".keu_fakturpajakdt set status='1', updateby='".$_SESSION['standard']['userid']."' where id='".$id."'";
             try{$owlPDO->exec($dt); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			
			$ht="update ".$dbname.".keu_fakturpajakht set status='1', updateby='".$_SESSION['standard']['userid']."' where id='".$id."'";
             try{$owlPDO->exec($ht); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	    break;
	    case'getAngka':
	    	$fakturawal=explode("-", $_POST['fakturawal']);
	    	$kdAwal=$fakturawal[0];
	    	$kdKedua=explode(".",$fakturawal[1]);

	    	#itung totalan
	    	$jmlh=(intval($kdKedua[1])+$_POST['jumlah'])-1;
	    	//$jmlh=intval($kdKedua[1])+$jmlh;
	    	$faktrAwal=$fakturawal[0]."-".$kdKedua[0].".".addZero(intval($kdKedua[1]),8);
	    	if(($_POST['jumlah']==0)||($_POST['jumlah']=='')){
	    		$fakturDt=$faktrAwal;
	    	}else{
	    		$fakturDt=$kdAwal."-".$kdKedua[0].".".addZero($jmlh,8);
	    	}
	    	
	    	echo $fakturDt.'####'.$faktrAwal;
	    break;

}
?>
