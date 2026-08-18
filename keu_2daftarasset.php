<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language=javascript src='js/keu_2daftarasset.js?v=<?php echo time(); ?>'></script>

<?
require_once('master_mainMenu.php');


OPEN_BOX('','<span class=judul>'.getMenu('keu_2daftarasset').'</span><br>');
$optper=$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit=$optsubtipe=$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";


$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";	
$res=fetchdata($str);
foreach($res as $bar){
	$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

// $str="select * from ".$dbname.".keu_5akun where noakun like '118%' and detail=1";	
// $res=fetchdata($str);
// foreach($res as $bar){
	// $optakun.="<option value=".$bar['noakun'].">".$bar['noakun']." - ".$bar['namaakun']."</option>";
// }


$str="select distinct kodetipe,namatipe from ".$dbname.".sdm_5tipeasset order by namatipe asc";
$res=fetchdata($str);
foreach($res as $bar){
    $opttipe.="<option value='".$bar['kodetipe']."'>".$bar['namatipe']."</option>";
}


// $str="select kodesub,namasub from ".$dbname.".sdm_5subtipeasset order by namasub asc";
// $res=fetchdata($str);
// foreach($res as $bar){
    // $optsubtipe.="<option value='".$bar['kodesub']."'>".$bar['namasub']."</option>";
// }


$str="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=fetchdata($str);
foreach($res as $bar){
    $optper.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}



echo"
	<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select id=kdpt onchange=getunit(); style=\"width:150px;\" onchange=clearopt()>".$optpt."</select></td>
					
					
                    <td>".$_SESSION['lang']['tipeasset']."</td>
                    <td>:</td>
                    <td><select id=kdtipeasset  onchange=getsubtipeasset();  style=\"width:150px;\" onchange=getbank()>".$opttipe."</select></td>
               
                </tr> 
				<tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdunit style=\"width:150px;\" onchange=clearopt()>".$optunit."</select></td>
					
					<td>".$_SESSION['lang']['subtipeasset']."</td>
                    <td>:</td>
                    <td><select id=kdsubtipeasset style=\"width:150px;\" onchange=getbank()>".$optsubtipe."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=kdperiode style=\"width:150px;\" onchange=getbank()>".$optper."</select></td>
					
					
                </tr>
				
				
                <tr>
                    <td><td><td colspan=4>
                   <button id=preview class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
                    <button id=excel class=mybutton onclick=excel('event')>".$_SESSION['lang']['excel']."</button>
                    <button id=excel class=mybutton onclick=pdf('event')>".$_SESSION['lang']['pdf']."</button>
                    <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
//  <button id=excel class=mybutton onclick=pdf('event')>".$_SESSION['lang']['pdf']."</button>
echo"
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;width:100%'; >
</div></fieldset>";


CLOSE_BOX();
echo close_body();
				
?>