<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/keu_kasir.js?v=<?php echo time(); ?>'></script>
<!--deklarasi untuk option-->
<?php



// $nokontrak=$_GET['nokontrak'];
// $nokontraktampung=$_GET['nokontrak'];



$optbuyer=$optnoakun=$opttipe=$optpembayaran=$optsupplier=$optctg=$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";


#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(1);
foreach($arrunit as $val=>$nama){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val."'");
	$d=$induk[$val];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$sel="";
	if($_SESSION['empl']['lokasitugas']==$val){
		$sel="selected";
	}
	$optunit.="<option value='".$val."' ".$sel.">".$val." - ".$nama."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
	
} 

$optrekening="<option value=''></option>";
$str = "select * from ".$dbname.".keu_5akunbank where status=1 and pemilik='".$_SESSION['empl']['lokasitugas']."'";
$res=fetchdata($str);
foreach($res as $bar){
	$wheredz =" kodebank='".$bar['namabank']."'";
	$optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
	$optrekening.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
}

$str = "select * from ".$dbname.".pmn_4customer  order by namacustomer asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbuyer.="<option value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}

$whereJam=" kasbank=1 and detail=1 and 
			(pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
$str = "select * from ".$dbname.".keu_5akun where ".$whereJam."";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optnoakun.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
}

$str = "select * from ".$dbname.".log_5supplier";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optsupplier.="<option value='".$bar['supplierid']."'>".$bar['supplierid']." - ".$bar['namasupplier']."</option>";
}


$arrtipe=array('M'=>'Masuk','K'=>'Keluar');
foreach($arrtipe as $key=>$data){
	$opttipe.="<option value='".$key."'>".$data."</option>";
}

$arrtipe=array('0'=>'Belum Dibayar','1'=>'Sudah Dibayar');
foreach($arrtipe as $key=>$data){
	$optpembayaran.="<option value='".$key."'>".$data."</option>";
}


$arrctg = getEnum($dbname,'keu_kasbankht','cgttu');
foreach($arrctg as $kei=>$fal){
	$optctg.="<option value='".$kei."'>".$fal."</option>";	
}
					


?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo"<div>";//buka div
OPEN_BOX('','<span class=judul>'.getMenu('keu_kasir').'</span>');
echo"<table>
     <tr valign=middle>
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	echo"<table>";
	echo"
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>		
			<td>
				<input type=text id=notransaksisch size=50 onkeypress=enterkey(event,loaddata) class=myinputtext style=\"width:150px;\">
			</td>
			
			
			
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>		
			<td>
				<select onchange=loaddata(); id=tipetransaksisch style=\"width:153px;\">'".$opttipe."'</select>
			</td>
			<td>".$_SESSION['lang']['supplier']."</td>
			<td>:</td>		
			<td>
				<select onchange=loaddata(); id=suppliersch style=\"width:153px;\">'".$optsupplier."'</select>
				<img id='suppliersch' onclick=z.elSearch('suppliersch',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
			</td>
			
			<td>".$_SESSION['lang']['bayarke']."</td>
			<td>:</td>		
			<td>
				<input type=text id=bayarkesch size=50 onkeypress=enterkey(event,loaddata) class=myinputtext style=\"width:150px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggalmulai']."</td>
			<td>:</td>		
			<td>
				<input type=text class=myinputtext readonly id=tanggalsch1 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\">
				</td>
				
			<td>".$_SESSION['lang']['tanggalselesai']."</td>
			<td>:</td>		
			<td>
				<input type=text class=myinputtext readonly id=tanggalsch2 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\">
			</td>
			
			<td>".$_SESSION['lang']['novoucher']."</td>
			<td>:</td>		
			<td>
				<input type=text id=novouchersch size=50 onkeypress=enterkey(event,loaddata) class=myinputtext style=\"width:150px;\">
			</td>
			
			<td>".$_SESSION['lang']['catatan']."</td>
			<td>:</td>		
			<td>
				<input type=text id=catatansch size=50 onkeypress=enterkey(event,loaddata) class=myinputtext style=\"width:150px;\">
			</td>
			
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['pembayaran']."</td>
			<td>:</td>		
			<td>
				<select id=pembayaransch onchange=loaddata(); style=\"width:153px;\">'".$optpembayaran."'</select>
			</td>
			
			<td>".$_SESSION['lang']['cgttu']."</td>
			<td>:</td>		
			<td>
				<select id=cgttusch style=\"width:153px;\" onchange=loaddata(); >'".$optctg."'</select>
			</td>
			
			<td>No. Bukti Pembayaran</td>
			<td>:</td>		
			<td>
				<input type=text id=noceksch size=50 onkeypress=enterkey(event,loaddata) class=myinputtext style=\"width:150px;\">
			</td>
			
			<td>".$_SESSION['lang']['jumlah']."</td>
			<td>:</td>		
			<td>
				<input type=text id=jumlahsch size=50 onkeypress=enterkey(event,loaddata) class=myinputtext style=\"width:150px;\">
			</td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>		
				<td>
					<select id=kodeorgsch onchange=\"loaddata();\" onblur=getrekeningsch(); style=\"width:154px;\" >'".$optunit."'</select>
				</td>
				
				<td>".$_SESSION['lang']['akun']." ".$_SESSION['lang']['kas']."/".$_SESSION['lang']['bank']."</td>
				<td>:</td>		
				<td>
					<select onchange=\"loaddata()\" onblur=getrekeningsch(); id=noakunsch style=\"width:153px;\" >'".$optnoakun."'</select>
				</td>
				
				<td>".$_SESSION['lang']['rekening']."</td>
				<td>:</td>		
				<td>
					<select id=rekeningsch onchange=loaddata(); style=\"width:154px;\">'".$optrekening."'</select>
				
				</td>
				
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['preview']."</button></td>
		</tr>
		
	";
        echo "</table>";
echo"</fieldset></td>";
echo"
     </tr>
	 </table> "; 
CLOSE_BOX();
echo "</div>";//tutup div
?>
<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 
// echo"
// <div id=listdata style=display:block>";//buka list data
// OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span><br><br>');

    // echo "<div class=table-scroll style='height:300px'>
            // <table cellpading=1 cellspacing=1 border=0 class=sortable>
echo"
<div id=listdata style=display:block>";//buka list data
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['list'].'</span>');
	echo " <div class=table-scroll style='height:60vh'>";
    echo "
            <table cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <th  align=center>".$_SESSION['lang']['nourut']."</th>
                    <th  align=center>".$_SESSION['lang']['notransaksi']."</th>
                   
                    <th  align=center>".$_SESSION['lang']['unit']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggalinput']."</th>
                    <th  align=center>".$_SESSION['lang']['tanggal']."</th>
                    <th  align=center>".$_SESSION['lang']['noakun']."</th>    
                    <th  align=center>".$_SESSION['lang']['namabank']."</th>    
                    <th  align=center>".$_SESSION['lang']['rekening']."</th>    
                    <th  align=center>".$_SESSION['lang']['tipe']."</th>
                    <th  align=center>".$_SESSION['lang']['matauang']."</th>    
                    <th  align=center>".$_SESSION['lang']['jumlah']."</th>    
					<th  align=center>".$_SESSION['lang']['remark']."</th>
					<th  align=center>".$_SESSION['lang']['bayarke']."</th>
					 <th  align=center>".$_SESSION['lang']['novoucher']."</th>
					<th  align=center>".$_SESSION['lang']['cgttu']."</th>
					<th  align=center>".$_SESSION['lang']['BuktiPembayaran']."</th>
					<th  align=center>".$_SESSION['lang']['dibuat']."</th>  
					<th  align=center>Kasir</th>  
					<th  align=center colspan=7>".$_SESSION['lang']['action']."</th>  
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
	";
echo "</div>";
CLOSE_BOX();
echo "</div>";//tutup list data
echo close_body();		////<input type=hidden id=method value='insert'>	
?>