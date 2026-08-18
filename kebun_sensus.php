<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');

?>


<script language=javascript src="js/kebun_sensus.js?v=<?= time(); ?>"></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>



<!--deklarasi untuk option-->
<?php

$optthnsch=$optdivsch=$optsmssch=$optstbloksch="<option value=''>".$_SESSION['lang']['all']."</option>";
$optdiv=$optsms =$optthn=$optstblok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
	$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	$optdivsch.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select distinct tahun from ".$dbname.".kebun_rencanapanen where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())	
{
	$optthn.="<option value=".$bar['tahun'].">".$bar['tahun']."</option>";
	$optthnsch.="<option value=".$bar['tahun'].">".$bar['tahun']."</option>";
       
}

for ($month=1; $month <= 12 ; $month++) { 
	$optsms.="<option value='".$month."'>".numToMonth($month,'I','long')."</option>";	
	$optsmssch.="<option value='".$month."'>".$month."</option>";
}
// $optsms.="<option value=1>I</option>";
// $optsms.="<option value=2>II</option>";
// $optsms.="<option value=3>III</option>";
// $optsmssch.="<option value=1>I</option>";
// $optsmssch.="<option value=2>II</option>";
// $optsmssch.="<option value=3>III</option>";

$optstblok.="<option value=TM>TM</option>";
$optstblok.="<option value=TBM>TBM</option>";
$optstbloksch.="<option value=TM>TM</option>";
$optstbloksch.="<option value=TBM>TBM</option>";

?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_sensus').'</span>');
echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table>";
	echo"
	<tr>
		<td>".$_SESSION['lang']['tahun']."</td>
		<td>:</td>
		<td><select id=thnsch style=\"width:100px;\">'".$optthnsch."'</select></td>
			
		<td>Pilih Semester Periode</td>
		<td>:</td>
		<td>
			<select id=smssch style=\"width:100px;\">'".$optsmssch."'</select>
			<span>" . $_SESSION['lang']['sd'] . "</span>
			<select id=smssch2 style=\"width:100px;\">'".$optsmssch."'</select>
		</td>
	</tr>

	<tr>
		<td>".$_SESSION['lang']['status']."</td>
		<td>:</td>
		<td><select id=stbloksch style=\"width:100px;\">'".$optstbloksch."'</select></td>
	
		<td>".$_SESSION['lang']['divisi']."</td>
		<td>:</td>
		<td colspan=4><select id=divisisch  style=\"width:100px;\">'".$optdivsch."'</select></td>
	</tr>
		
	<tr>
		<td colspan=2></td>
		<td colspan=20>
		<button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
	</tr>
	
	";
        echo "</table>";
		
	
echo"</fieldset></td>";

echo"<td valign=top><fieldset><legend>" . $_SESSION['lang']['print'] . "</legend><table>
                <tr>
                    <td>".$_SESSION['lang']['tahun']."</td>
                    <td>:</td>
				    <td><select id=thnex style=\"width:85px;\">'".$optthn."'</select></td>
				<tr><td><td>
                    <td><button class=mybutton onclick=excel(event,'kebun_slave_sensus.php')>" . $_SESSION['lang']['excel'] . "</button></td>
                </table>
    </fieldset>";


echo"</td>
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
	<div>
	<table class='sortable' cellspacing=1 cellpadding=5 border=0>
                <thead>
                    <tr>
						<th  align=center>".$_SESSION['lang']['nourut']."</th>
						<th  align=center>".$_SESSION['lang']['unit']."</th>
                        <th  align=center>".$_SESSION['lang']['divisi']."</th>
						<th  align=center>".$_SESSION['lang']['statusblok']."</th>
                        <th  align=center>Semester Periode</th>
						<th  align=center>".$_SESSION['lang']['tahun']."</th>
				
						<th  align=center>".$_SESSION['lang']['jjg']."</th>
						<th  align=center>".$_SESSION['lang']['kg']."</th>
						<th  align=center>".$_SESSION['lang']['bjr']."</th>
						<th  align=center>".$_SESSION['lang']['updateby']."</th>
						<th  align=center>".$_SESSION['lang']['tanggalupdate']."</th>
                        <th  align=center colspan=4>".$_SESSION['lang']['action']."</th>    
                    </tr>  
                </thead>
         
		 <tbody id=contain> 
                    <script>loaddata(0)</script>
                 </tbody>
                <tfoot id=footData>
                 </tfoot>
                 </table>
                </div>
	";
CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 
echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
	<tr>
		<td>".$_SESSION['lang']['tahun']."</td>
		<td>:</td>
		<td><input type=text id=thn maxlength=4 onchange=cekthn() onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:175px;\"></td>
	</tr>
	<tr>	
		<td>Pilih Rentang Periode</td>
		<td>:</td>
		<td>
			<select id=sms style=\"width:75px;\">'".$optsms."'</select>
			<span>".$_SESSION['lang']['sd']."</span>
			<select id=sms2 style=\"width:75px;\">'".$optsms."'</select>
		</td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['status']."</td>
		<td>:</td>
		<td><select id=stblok style=\"width:175px;\">'".$optstblok."'</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['divisi']."</td>
		<td>:</td>
		<td colspan=20><select id=divisi  style=\"width:175px;\">'".$optdiv."'</select></td>
	</tr>
	
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead('','','','','','')>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
			<button id=batal class=mybutton onclick=showupload()>" . $_SESSION['lang']['upload'] . "</button>
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
echo "<div id=detailinput></div>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo "<div id=upload style=display:none>";
OPEN_BOX();
echo"<fieldset><legend>Form</legend><div id=viewupload style='overflow:auto;width:100%';></div></fieldset>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>
    
