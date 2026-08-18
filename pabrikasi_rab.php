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
<script language=javascript1.2 src='js/pabrikasi_rab.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('r a b').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('r a b').'</span>');
}


$optpt=$optkel=$optsatsch="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$str="select kode,nama from ".$dbname.".pabrikasi_5kelompokrab ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkel.="<option value=" . $bar['kode'] . ">" . $bar['nama'] . "</option>";
}

$optstat="<option value='0'>Waiting</option>";
// $optstat.="<option value='1'>Open</option>";
// $optstat.="<option value='2'>Cancel</option>";
// $optstat.="<option value='3'>Close</option>";				

$optsatsch.="<option value='0'>Waiting</option>";
$optsatsch.="<option value='1'>Open</option>";
$optsatsch.="<option value='2'>Cancel</option>";
$optsatsch.="<option value='3'>Close</option>"; 

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
	echo"
	<tr>
		<td>".$_SESSION['lang']['kode']." Pabrikasi</td>
		<td>:</td>
		<td><input type=text id=schkdpab   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\"></td>
	
		<td>".$_SESSION['lang']['kode']." Sales Order</td>
		<td>:</td>
		<td><input type=text id=schkdso size=4 class=myinputtext maxlength=4 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:100px;\"></td>
	
        <td>Status</td>
        <td>:</td>
        <td><select id=schstat >'".$optsatsch."'</select></td>
    	
            <td><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
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
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=min-width:60%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
					<td  align=center>".$_SESSION['lang']['kodepabrikasi']."</td>
					<td  align=center>".$_SESSION['lang']['namapabrikasi']."</td>
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
        <td width=130px>".$_SESSION['lang']['kodepabrikasi']."</td>
        <td>:</td>
        <td>
			<input type=text id=kdpab readonly  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
		</td>
		<td><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblkdpab class=resicon onclick=carikdpab('".$_SESSION['lang']['find']."',event)></td>
    </tr> 
	<tr>
		<td width=130px>".$_SESSION['lang']['salesorder']."</td>
		<td>:</td>
		<td><input type=text id=kdso  readonly onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
		<td><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblkdso class=resicon onclick=kdsocr('".$_SESSION['lang']['find']."',event)></td>
	</tr>
	<tr hidden>
        <td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td><select id=stat style=\"width:150px;\">'".$optstat."'</select></td>
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

/*
Detail					
	1. Kode Pabrikasi				
	2. Pilihan tahapan pabrikasi				
	3. Kelompok Biaya RAB				
	4. Kode barang (Bila kelompok biaya material) default null				
	5.Jumlah (material, hk, hm, lot jika spk)				
	6. Biaya (RP)			
*/






// style='float:left;'
echo"<fieldset><legend><b>Form ".$_SESSION['lang']['detail']."</b></legend>
<table>
    <tr>
		<td>".$_SESSION['lang']['tahapan']." ".$_SESSION['lang']['pabrikasi']."</td>
		<td>:</td>
		<td colspan=3><select id=tahapan style=\"width:230px;\">'".$opttahap."'</select></td>
	</tr>
	<tr>
		<td width=130px>".$_SESSION['lang']['kelompokbiaya']." RAB</td>
		<td>:</td>
		<td colspan=3><select id=kelby style=\"width:230px;\">'".$optkel."'</select></td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['kodebarang']."</td>
		<td>:</td>
		<td><input type=text disabled  id=kdbrg disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:50px;\"></td>
		<td><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=tambahBarang('".$_SESSION['lang']['find']."',event)></td>
	
		<td><input type=text  id=nmbrg disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:152px;\"></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jumlahbarang']."</td>
		<td>:</td>
		<td colspan=3><input type=text  id=jumlah onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\">
	
		".$_SESSION['lang']['hargasatuan']."
		:
		<input type=text  id=biaya onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:84px;\"></td>
	</tr>
	
	
	<tr>
		<td colspan=2></td>
		<td colspan=100>
		<button id=savedetail class=mybutton onclick=savedetail()>".$_SESSION['lang']['save']."</button>
		<button onclick=canceldt() class=mybutton name=canceldt id=canceldt>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset></fieldset><input type=hidden id=methoddetail value='savedetail'>";

echo"
<div id='listdetail'>

</div>";



CLOSE_BOX();
echo"</div>";

echo close_body();			
?>
    
