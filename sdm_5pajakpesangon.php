<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/sdm_5pajakpesangon.js?v=<?php echo time(); ?>'></script>
<script>
    
</script>
<?php
$arr="##kodept##penghasilan##persentase##old_kodept##old_penghasilan##old_persentase##method";

$sPT="select * from ".$dbname.".organisasi where tipe='PT'";
$qPT=$owlPDO->query($sPT) or die(print " Gagal: ".PDOException::getMessage());
$qPT->setFetchMode(PDO::FETCH_ASSOC);
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rPT=$qPT->fetch())
{
    $optPT.="<option value=".$rPT['kodeorganisasi'].">".$rPT['namaorganisasi']."</option>";	
}

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pajak']." ".$_SESSION['lang']['pesangon']).'</span><br>');

echo"<fieldset style='width:380px;float:left;'>
     <legend><b>Form</b></legend>
	 <table>
           <tr>
	   <td>".$_SESSION['lang']['kodept']."<input type='hidden' id=old_kodept name=old_kodept /></td>
	    <td><select id=kodept style=width:150px;>".$optPT."</select></td>
	 </tr>
         <tr>
	   <td>".$_SESSION['lang']['penghasilan']."<input type='hidden' id=old_penghasilan name=old_penghasilan /></td>
	   <td><input type=text class=myinputtextnumber id=penghasilan style=width:150px; onkeypress='return angka_doang(event)' \"></td>
	 </tr>	
         <tr>
	   <td>".$_SESSION['lang']['persentase']."<input type='hidden' id=old_persentase name=old_persentase /></td>
	   <td><input type=text class=myinputtextnumber id=persentase style=width:150px; onkeypress='return angka_doang(event)' ></td>
	 </tr>	 
	 </table>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=savePajakPesangon('sdm_slave_5pajakpesangon','".$arr."')>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset>";
 
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset  style=width:100%;><legend>".$_SESSION['lang']['list']."</legend>
     <table>
        <tr>
            <td>".$_SESSION['lang']['kodept']." </td>
            <td><select id=carikodept style='width:150px;' onchange='loadData()'>".$optPT."</select></td>
        </tr>	
     </table>
     <div class='table-scroll'><table class=sortable cellspacing=1 cellpadding=7 style='width:100%;'  border=0>
     <thead>
	  <tr align=center class=rowcontent>
           <th>No.</th>
           <th>".$_SESSION['lang']['kodept']."</th>
           <th>".$_SESSION['lang']['penghasilan']."</th>
	   	   <th>".$_SESSION['lang']['persentase']."</th>
           <th>".$_SESSION['lang']['action']."</th>    
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";

echo"</tbody>
     <tfoot>
     
	 </tfoot>
	 </table></div></fieldset>";
CLOSE_BOX();
echo close_body();
?>