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
<script language=javascript1.2 src='js/kebun_rkb.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php
OPEN_BOX('','<span class=judul>'.getMenu('kebun_rkb').'</span>');


$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$opttt=$optdiv=$optper="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$str="select * from ".$dbname.".setup_blok where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$arrkddiv[substr($bar['kodeorg'],0,6)]=substr($bar['kodeorg'],0,6);
	$arrtt[$bar['tahuntanam']]=$bar['tahuntanam'];
}


foreach($arrkddiv as $kddiv){
	$optdiv.="<option value='".$kddiv."'>".$nmorg[$kddiv]."</option>";
}

foreach($arrtt as $tt){
	$opttt.="<option value='".$tt."'>".$tt."</option>";
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
	echo"
	<tr>
            <td>".$_SESSION['lang']['tahun']."</td>
            <td>:</td>
            <td><select id=thnsch style=\"width:85px;\">'".$optthnsch."'</select></td>
	</tr>
            <td colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
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
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:50%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['unit']."</td>
					<td  align=center>".$_SESSION['lang']['periode']."</td>
                    <td  align=center>PDO</td>
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
        <td>No. PDO</td>
        <td>:</td>
        <td><input type=text id=norkb disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr> 
    <tr>
        <td hidden>".$_SESSION['lang']['unit']."</td>
        <td hidden>:</td>
        <td hidden><input type=text id=unit disabled value='".$_SESSION['empl']['lokasitugas']."'  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td><select id=per style=\"width:150px;\">'".$optper."'</select></td>
    </tr>
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['save']."</button>
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
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




$frm[0]='';


// style='float:left;'
$frm[0].="<fieldset><legend><b>".$_SESSION['lang']['form']."</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td><input type=text id=nopnn disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
		
		<td>".$_SESSION['lang']['tahuntanam']."</td>
        <td>:</td>
        <td><select id=ttpnn style='width:150px;'>".$opttt."</select></td>
    </tr> 
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=divpnn style='width:150px;'>".$optdiv."</select></td>
	</tr>
		<td colspan=2></td>
		<td colspan=100>
		<button id=prevupah class=mybutton onclick=prevpnn()>".$_SESSION['lang']['save']."</button>
		<button onclick=cancelpnn() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[0].="
<div id='detailpnn'>
</div>";
$frm[0].="
<div id='listpnn'>
</div>";


$hfrm[0]=strtoupper($_SESSION['lang']['panen']);

drawTab('FRM',$hfrm,$frm,100,1230);	

CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>