<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/sdm_5hargaTicket.js'></script>
<?
$arr="##thnBudget##kdGol##region##tktPes##tksi##airport##visa##byaLain##method";
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('TICKET').'</span>');
$optGol="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sGol="select * from ".$dbname.".sdm_5golongan order by namagolongan asc";
$qGol=$owlPDO->query($sGol) or die(print " Gagal: ".PDOException::getMessage());
$qGol->setFetchMode(PDO::FETCH_ASSOC);
while($rGol=  $qGol->fetch())
{
    $optGol.="<option value='".$rGol['kodegolongan']."'>".$rGol['namagolongan']."</option>";
}
$optReg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sReg="select * from ".$dbname.".bgt_regional order by nama asc";
$qReg=$owlPDO->query($sReg) or die(print " Gagal: ".PDOException::getMessage());
$qReg->setFetchMode(PDO::FETCH_ASSOC);
while($rReg=  $qReg->fetch())
{
    $optReg.="<option value='".$rReg['regional']."'>".$rReg['nama']."</option>";
}
echo"<input type='hidden' id='method' name='method' value='insert' />";

echo"<fieldset style=width:250px;>
     <legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['hargatiket']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['budgetyear']."</td>
	   <td><input type=text class=myinputtextnumber id=thnBudget name=thnBudget onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=4 /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['kodegolongan']."</td>
	   <td><select id=kdGol name=kdGol style=\"width:150px;\" >".$optGol."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['tujuan']."</td>
	   <td><select id=region name=region style=\"width:150px;\" >".$optReg."</select></td>
	 </tr>	
	 <tr>
	   <td>".$_SESSION['lang']['tiketPes']."</td>
	   <td><input type=text class=myinputtextnumber id=tktPes name=tktPes  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	 </tr>	 
	  <tr>
	   <td>".$_SESSION['lang']['taksi']."</td>
	   <td><input type=text class=myinputtextnumber id=tksi name=tksi  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	 </tr>	
           <tr>
	   <td>".$_SESSION['lang']['airportax']."</td>
	   <td><input type=text class=myinputtextnumber id=airport name=airport  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	 </tr>	
           <tr>
	   <td>".$_SESSION['lang']['visa']."</td>
	   <td><input type=text class=myinputtextnumber id=visa name=visa  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	 </tr>	
           <tr>
	   <td>".$_SESSION['lang']['biayalain']."</td>
	   <td><input type=text class=myinputtextnumber id=byaLain name=byaLain  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	 </tr>	
	 </table>
	 
	 <button class=mybutton onclick=saveFranco('sdm_slave_5hargaTicket','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();
$optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str="select distinct tahunbudget from ".$dbname.".sdm_5transportpjd order by tahunbudget desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$res->fetch())
{
    $optData.="<option value='".$rData['tahunbudget']."'>".$rData['tahunbudget']."</option>";
}
echo"<table><tr>
    <td>".$_SESSION['lang']['budgetyear']." <select id=thnBudgetHead style='width:100px' onchange='loadData()'>".$optData."</select></td>
    <td>".$_SESSION['lang']['kodegolongan']." <select id=kdGOlHead style='width:100px' onchange='loadData()'>".$optGol."</select></td>
    <td>".$_SESSION['lang']['tujuan']." <select id=tujuanHead style='width:100px' onchange='loadData()'>".$optReg."</select></td>
    </tr></table>";
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>".$_SESSION['lang']['budgetyear']."</td>
	   <td>".$_SESSION['lang']['kodegolongan']."</td>
	   <td>".$_SESSION['lang']['tujuan']."</td>
	   <td>".$_SESSION['lang']['tiketPes']."</td>
	   <td>".$_SESSION['lang']['taksi']."</td>
            <td>".$_SESSION['lang']['airportax']."</td>
            <td>".$_SESSION['lang']['visa']."</td>
            <td>".$_SESSION['lang']['biayalain']."</td>
	   <td>Action</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";   
echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>