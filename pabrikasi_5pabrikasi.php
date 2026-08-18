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
<script language=javascript1.2 src='js/pabrikasi_5pabrikasi.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php
if($_SESSION['language']=='EN'){
OPEN_BOX('','<span class=judul>'.strtoupper('List Fabrication').'</span>');
}else{
OPEN_BOX('','<span class=judul>'.strtoupper('Daftar Pabrikasi').'</span>');
}
$optkel=$optsatsch="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str="select distinct(kode) as kode,nama from ".$dbname.".pabrikasi_5kelompok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkel.="<option value=" . $bar['kode'] . ">" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
}

$str="select * from ".$dbname.".sdm_5tipekaryawan where id between 1 and 6 "; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $opttk.="<option value=" . $bar['id'] . ">" . $bar['tipe'] . "</option>";
}

$str="select * from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'"
        . " and length(kodeorganisasi)<=6   order by kodeorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optdivisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$str="select * from ".$dbname.".setup_kegiatan order by kodekegiatan asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
    $optkeg.="<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
}
		
//$optsat="<option value='0'>Waiting</option>";
$optsat="<option value='1'>Open</option>";
// $optsat.="<option value='2'>Cancel</option>";
// $optsat.="<option value='3'>Close</option>"; 


$optsatsch.="<option value='0'>Waiting</option>";
$optsatsch.="<option value='1'>Open</option>";
$optsatsch.="<option value='2'>Cancel</option>";
$optsatsch.="<option value='3'>Close</option>"; 

$str="select distinct(substr(periode,1,4)) as tahun from ".$dbname.".keu_pdoht where kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optthnsch.="<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}


?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php

echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table>";
	
	/*
	<tr>
        <td>Kode Pabrikasi</td>
        <td>:</td>
        <td><input type=text id=kdpab disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr> 
	<tr>
        <td>Nama Pabrikasi</td>
        <td>:</td>
        <td><input type=text id=nmpab   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr>
    <tr>
        <td>Kode Kelompok</td>
        <td>:</td>
        <td><select id=kdkel style=\"width:150px;\">'".$optkel."'</select></td>
    </tr>
	*/
	
	
	
	echo"
	<tr>
		<td>".$_SESSION['lang']['kodepabrikasi']."</td>
		<td>:</td>
		<td><input type=text id=schkdpab   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:145px;\"></td>
	
        <td>".$_SESSION['lang']['namapabrikasi']."</td>
        <td>:</td>
        <td><input type=text id=schnmpab   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr>
	 <tr>
        <td>".$_SESSION['lang']['kodekelompok']."</td>
        <td>:</td>
        <td><select id=schkdkel style=\"width:150px;\">'".$optkel."'</select></td>
   
        <td>".$_SESSION['lang']['kodesalesorder']."</td>
        <td>:</td>
        <td><input type=text id=schkdso   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
	</tr>
	<tr>
        <td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td><select id=schstat style=\"width:150px;\">'".$optsatsch."'</select></td>
    </tr>
	<tr>
            <td><td><td colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
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

echo"
<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
    <fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:70%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
					<td  align=center>".$_SESSION['lang']['unit']."</td>
                    <td  align=center>".$_SESSION['lang']['kode']."</td>
					<td  align=center>".$_SESSION['lang']['nama']."</td>
					<td  align=center>".$_SESSION['lang']['kodekelompok']."</td>
					<td  align=center>".$_SESSION['lang']['namakelompok']."</td>
					<td  align=center>".$_SESSION['lang']['kodesalesorder']."</td>
					<td  align=center>".$_SESSION['lang']['status']."</td>
                    <td  align=center>".$_SESSION['lang']['action']."</td>    
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
                
	</fieldset>";
CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 
echo "
<fieldset>
<legend><b>Header</b></legend>
<table cellspacing=1 border=0>
    <tr>
        <td>".$_SESSION['lang']['kodepabrikasi']."</td>
        <td>:</td>
        <td><input type=text id=kdpab disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:158px;\"></td>
    </tr> 
	<tr>
        <td>".$_SESSION['lang']['namapabrikasi']."</td>
        <td>:</td>
        <td><input type=text id=nmpab   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:158px;\"></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['kodekelompok']."</td>
        <td>:</td>
        <td><select id=kdkel style=\"width:162px;\">'".$optkel."'</select></td>
    </tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']." Order</td>
		<td>:</td>
		<td>
			<input type=text onchange=tgl1 class=myinputtext id=tgl1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			S/D
			<input type=text onchange=tgl2 class=myinputtext id=tgl2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
		</td>
	</tr>
	
	<tr>
        <td>".$_SESSION['lang']['kodesalesorder']."</td>
        <td>:</td>
        <td><input type=text id=kdso  readonly onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:158px;\"></td>
		<td><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblkdso class=resicon onclick=kdsosch('".$_SESSION['lang']['find']."',event)></td>
    </tr> 
	<tr hidden>
        <td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td><select id=stat style=\"width:158px;\">'".$optsat."'</select></td>
    </tr>
	
	
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset><input type=hidden id=method value='savehead'>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();
$frm[0]='';
$frm[1]='';

// style='float:left;'
$frm[0].="<fieldset><legend><b>Form ".$_SESSION['lang']['tahapan']."</b></legend>
<table>
	 <tr hidden>
        <td>".$_SESSION['lang']['tahapan']."</td>
        <td>:</td>
        <td><input type=text id=idtahapan   onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:190px;\"></td>
    </tr> 
    <tr>
        <td>".$_SESSION['lang']['tahapan']."</td>
        <td>:</td>
        <td><input type=text id=tahapan   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:158px;\"></td>
    </tr> 
    <tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text onchange=tgldt1 class=myinputtext id=tgldt1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
			S/D
			<input type=text onchange=tgldt2 class=myinputtext id=tgldt2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px;/>
		</td>
	</tr>
	<tr>
        <td>".$_SESSION['lang']['keterangan']."</td>
        <td>:</td>
        <td><input type=text id=ketdt   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:158px;\"></td>
    </tr> 
		<td colspan=2></td>
		<td colspan=100>
		<button id=savedetail class=mybutton onclick=savedetail()>".$_SESSION['lang']['save']."</button>
		<button onclick=canceldt() class=mybutton name=canceldt id=canceldt>".$_SESSION['lang']['cancel']."</button>
		<button id=selesai class=mybutton onclick=cancel()>".$_SESSION['lang']['selesai']."</button>
		</td>
	</tr>
</table>
</fieldset><input type=hidden id=methoddetail value='savedetail'>";

$frm[0].="<div id='listdetail'></div>";


$frm[1].="<fieldset><legend><b>Form ".$_SESSION['lang']['detail']." Barang</b></legend>
<table>
	 <tr>
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><input type=text  id=kdbrg disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:65px;\" >
		</td>
		<td><input type=text  id=nmbrg disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:275px;\">
		<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)>
		</td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['jumlah']."</td>
		<td>:</td>
		<td><input type=text  id=jum onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:65px;\"></td>
	</tr>
		<td colspan=2></td>
		<td colspan=100>
		<button id=savedetailbarang class=mybutton onclick=savedetailbarang()>".$_SESSION['lang']['save']."</button>
		<button onclick=canceldtbarang() class=mybutton name=canceldt id=canceldtbarang>".$_SESSION['lang']['cancel']."</button>
		<button id=selesai class=mybutton onclick=cancel()>".$_SESSION['lang']['selesai']."</button>
		</td>
	</tr>
</table>
</fieldset><input type=hidden id=methoddetailbarang value='savedetailbarang'>";

$frm[1].="<div id='listdetailbarang'></div>";

$hfrm[0]=strtoupper($_SESSION['lang']['tahapan']);
$hfrm[1]=strtoupper($_SESSION['lang']['daftarbarang']);

drawTab('FRM',$hfrm,$frm,200,1250);	

CLOSE_BOX();
echo"</div>";

echo close_body();			
?>
    
