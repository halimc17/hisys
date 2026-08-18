<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src=js/log_5masterfranco.js></script>
<?
$arr="##idFranco##nmFranco##almtFranco##cntcPerson##hdnPhn##method";
include('master_mainMenu.php');

$optBagian='';
$arrorgdet = getOrgDetail(1);
$no=0;
foreach($arrorgdet as $key=>$val){
	$no++;
	if($no==1){
		$unitkerja = $key;
	}
	$optBagian.="<option value='".$key."'>".$key." - ".$val."</option>";	
}


OPEN_BOX('','<span class=judul>'.getMenu('log_5masterfranco').'</span><br>');
echo"<fieldset style=float:left>
     <legend>Form</legend>
	 <table>
	 <tr>
	   <td class=bintang>Franco Name</td><td>:</td>
	   <td><input type=text class=myinputtext id=nmFranco name=nmFranco onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" maxlength=100 /></td>

	   <td class=bintang>Contac Person</td><td>:</td>
	   <td><input type=text class=myinputtext id=cntcPerson name=cntcPerson onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" /> </td>
	 </tr>
	 <tr>
	   <td class=bintang valign=top>".$_SESSION['lang']['alamat']."</td><td valign=top>:</td>
	   <td colspan=90><textarea style=\"width:400px;\" id=almtFranco name=almtFranco></textarea></td>
	 </tr>

	 <tr>
	   <td class=bintang>".$_SESSION['lang']['telp']."</td><td>:</td>
	   <td><input type=text class=myinputtext id=hdnPhn name=hdnPhn  onkeypress=\"return angka_doang(event);\" style=\"width:150px;\" maxlength=20></td>
	
	   <td>".$_SESSION['lang']['email']."</td><td>:</td>
	   <td><input type=text class=myinputtext id=email name=email  onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" maxlength=100></td>
	 </tr>	 
	 <tr>
	 <td>".$_SESSION['lang']['unit']."</td><td>:</td>
	 <td>
			 <!--<input type=text class=myinputtext id=kodeunit name=kodeunit  onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" maxlength=100>-->
		 <select id='kodeunit' style='width:150px'>".$optBagian."</select>
	  </td>
  
	 <td>".$_SESSION['lang']['status']."</td><td>:</td>
	 <td><input type='checkbox' id=statFr name=statFr />".$_SESSION['lang']['tidakaktif']."</td>
   </tr> 
	 <tr><td><td><td>
	 <input type=hidden value=insert id=method>
	 <button class=mybutton onclick=saveFranco()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </table>
	 </fieldset><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();
echo"<table class=sortable cellspacing=1 border=0 cellpadding=5>
     <thead>
	  <tr class=rowheader>
	   <th align=center>No</th>
	   <th align=center>Franco Name</th>
	   <th align=center>".$_SESSION['lang']['alamat']."</th>
	   <th align=center>Contact Person</th>
	   <th align=center>".$_SESSION['lang']['telp']."</th>
	   <th align=center>".$_SESSION['lang']['email']."</th>
	   <th align=center>".$_SESSION['lang']['unit']."</th>
	   <th align=center>".$_SESSION['lang']['status']."</th>
	   <th align=center>Action</th>
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