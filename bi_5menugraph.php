<?php
//@Copy nangkoelframework

require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');

?>


<script language=javascript1.2 src='js/bi_5menugraph.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>



<!--deklarasi untuk option-->
<?php
OPEN_BOX('','<span class=judul>'.strtoupper('Menu Graph').'</span>');
$optthnsch=$optdivsch=$optsmssch=$optstbloksch="<option value=''>".$_SESSION['lang']['all']."</option>";
$optdiv=$optsms =$optthn=$optstblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	$optdivsch.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select distinct tahun from ".$dbname.".kebun_rencanapanen where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optthn.="<option value=".$bar['tahun'].">".$bar['tahun']."</option>";
	$optthnsch.="<option value=".$bar['tahun'].">".$bar['tahun']."</option>";
       
}

$optsms.="<option value=1>I</option>";
$optsms.="<option value=2>II</option>";
$optsmssch.="<option value=1>I</option>";
$optsmssch.="<option value=2>II</option>";

$optstblok.="<option value=TM>TM</option>";
$optstblok.="<option value=TBM>TBM</option>";
$optstbloksch.="<option value=TM>TM</option>";
$optstbloksch.="<option value=TBM>TBM</option>";

?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php

echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td>";


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
OPEN_BOX();//Divisi	Semester	Bulan	Tahun	Jumlah Jjg	Jumlah Kg	BJR	Aksi

	echo "
	<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
               
                <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:50%>
                <thead>
                    <tr class=rowheader>
						<td  align=center>".$_SESSION['lang']['nourut']."</td>
						<td  align=center>".$_SESSION['lang']['menu']."</td>
                        <td  align=center>Icon</td>
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
<legend>Header</legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['menu']."</td>
		<td>:</td>
		<td><input type=text id=menuht onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\"></td>
	</tr>
	<tr>
		<td>Icon</td>
		<td>:</td>
		<td colspan=20><input name=icon type=file id=icon size=1 class=mybutton style=width:160px></td>
	</tr>
	
	
	<tr><td colspan=2></td>
		<td>
			<button id=saveht class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset>";
CLOSE_BOX();//<input type=hidden id=method value='insert'>
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();
echo "
<fieldset>
<legend>Detail</legend>
<table cellspacing=1 border=0>
<input type=text id=iddt hidden onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\">
	<tr>
		<td>".$_SESSION['lang']['kelompok']."</td>
		<td>:</td>
		<td><input type=text id=kel onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\"></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['menu']." Indonesia</td>
		<td>:</td>
		<td><input type=text id=menudt onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\"></td>
	</tr>
	
	<tr>
		<td>File</td>
		<td>:</td>
		<td><input type=text id=file onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:100px;\"></td>
	</tr>
	<tr><td colspan=2></td>
		<td>
			<button id=savehead class=mybutton onclick=savedt()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button>
		</td>
		<input type=hidden id=methoddt value='savedt'>
		
	</tr>
</table>
</fieldset>




<fieldset>
	<legend>Data Detail</legend>
	<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
					<thead>
						<tr class=rowheader>
							<td  align=center>".$_SESSION['lang']['nourut']."</td>
							<td  align=center>".$_SESSION['lang']['kelompok']."</td>
							<td  align=center>".$_SESSION['lang']['menu']." Indonesia</td>
							<td  align=center>File</td>
							<td  align=center>".$_SESSION['lang']['action']."</td>    
						</tr>  
					</thead>
			 
			 <tbody id=containdetail> 
						
					 </tbody>
					   </table>
</fieldset>";//<script>loaddatadetail()</script>


CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>
    
