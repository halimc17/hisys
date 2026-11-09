<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zMysql.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language='JavaScript1.2' src='js/supplier.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src='js/zTools.js'></script>
<script language="javascript" src='js/zSelect2.js?ver=1'></script>
<style>
/* dropdown select2 di atas jendela rincian supplier (z-index jendela = 2000) */
body > .select2-container{z-index:2100}
</style>
<?
include('master_mainMenu.php');
include('lib/zLib.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_5dataSupplier').'</span></br>');
#= nomor transaksi dari notifikasi: hanya huruf, angka, garis (dulu dicetak apa adanya ke dalam skrip)
$xxx = preg_replace('/[^A-Za-z0-9_\-\/]/', '', isset($_GET['xxx']) ? (string)$_GET['xxx'] : '');
$jnsapp = "DS";
### Get Value nama badan Suppllier
$optcaribadan="<option value=''>".$_SESSION['lang']['all']."</option>";
$optbadan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrtipebadan=getEnum($dbname,'log_5supplier','badanusaha');
foreach($arrtipebadan as $kei=>$fal)
{
	$optbadan.="<option value='".$kei."'>".ucfirst(strtoupper($fal))."</option>";
	$optcaribadan.="<option value='".$kei."'>".ucfirst(strtoupper($fal))."</option>";
}
//optRejuice([array],[selected value],[option value number],[option text number]);
function optRejuice($arr,$selected,$val,$txt){
	$opt= '';
	if(count($arr) >0){
		for ($x = 0; $x < count($arr); $x++) {
			$add_type_selected = "";
			if($arr[$x][$val] == $selected){
				$add_type_selected = 'selected';
			}
			$opt .="<option value='" . $arr[$x][$val] . "' ".$add_type_selected.">" . $arr[$x][$txt] . "</option>";
		}
	}
	$result = $opt;
	return $result;
}
//log_5klsupplier
$str =$owlPDO->query("select tipe,kode from ".$dbname.".log_5klsupplier where sync = '1'");
$klsup= $str->fetchAll(PDO::FETCH_ASSOC);
//print_r($klsup);
### Get Value Status
$optstatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optcaristatus="<option value=''>".$_SESSION['lang']['all']."</option>";
$optcaristatuscalon=$optcaristatus; // calon supplier: semua status selain AKTIF
$optsupstat=array('0'=>'tidakaktif','1'=>'AKTIF','2'=>'belumsetuju','3'=>'register','4'=>'blacklist');
// foreach ($optsupstat as $key => $value) {
	# code...
// }
foreach ($optsupstat as $key => $val) {
	$selected = "";
	if($key == 1){
		$selected = "selected";
	}
 if($key != 2 && $key != 3){ // BELUM DISETUJUI (status sementara sistem) dan REGISTER (supplier langsung didaftarkan AKTIF) tidak dipilih manual di form; tetap ada di filter
	  @$optstatus.="<option value= '".@$key."' ".$selected.">".strtoupper(@$_SESSION['lang'][strtolower($val)]). "</option>";
	 }
 @$optcaristatus.="<option value= '".@$key."'>".strtoupper(@$_SESSION['lang'][strtolower($val)]). "</option>";
	 if($key != 1){ @$optcaristatuscalon.="<option value= '".@$key."'>".strtoupper(@$_SESSION['lang'][strtolower($val)]). "</option>"; }
}

// $arrtipebadan=getEnum($dbname,'log_5supplier','badanusaha');
// foreach($arrtipebadan as $kei=>$fal)
// {
// 	$optbadan.="<option value='".$kei."'>".ucfirst(strtoupper($fal))."</option>";
// }

### Get Value Enum Status Internal/Eksternal
$optStatusIntExt='';
$arrStatusIntExt=getEnum($dbname,'log_5supplier','statusintext');
foreach($arrStatusIntExt as $kei=>$fal)
{
	$optStatusIntExt.="<option value='".$kei."'>".ucfirst(strtolower($fal))."</option>";
}

 echo"<fieldset style=float:left>
      <legend>".$_SESSION['lang']['form']."</legend>
	  <table>
	  <tr>
	      <td>ID ".$_SESSION['lang']['supplier']." / ".$_SESSION['lang']['kontraktor']."</td><td>:</td>
		  <td><input style=width:200px; placeholder='auto generate' type=text class=myinputtext id=idsupplier disabled></td>
	  </tr>

	   <tr>
		  <td class=bintang>".$_SESSION['lang']['namasupplier']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=namasupplier onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\" size=20 maxlength=45></td>
	   </tr>
	   <tr>
			<td class=bintang>".$_SESSION['lang']['badanusaha']."</td> 
			<td>:</td>
			<td><select class=select2 id=badan style=\"width:203px;\">".$optbadan."</select></td>
		</tr>
	<tr id='trpemilik'>
		  <td class=bintang>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pemilik']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=pemilik onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\" size=20 maxlength=45></td>
	  </tr>
	  <tr id='trdirektur'>
		  <td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['direktur']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=direktur onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\" size=20 maxlength=45></td>
	  </tr>

	  <tr id='trpj'>
		  <td>".$_SESSION['lang']['namapj']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=pj onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\" size=20 maxlength=45></td>
	  </tr>
	  <tr id='trjabatan'>
		  <td>".$_SESSION['lang']['jabatan']."</td><td>:</td><td><input style=width:200px; type=text class=myinputtext id=jabatan onkeypress='key=getKey(event); if(key==13){saveSupplier()}' onkeydown=\"upperCaseF(this)\" size=20 maxlength=45></td>
		</tr>
		<tr>
		  <td>User ".$_SESSION['lang']['email']."</td><td>:</td>
		  <td><input style=width:200px; type=email class=myinputtext id=useremail size=20 maxlength=45 placeholder=email@example.com required></td>
		</tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['supplier'] . "</td> 
			<td>:</td>
			<td><select class=select2 id=statusup style=\"width:203px;\">".$optstatus."</select></td>
		</tr><tbody id='trapproval'>";
		$countApp = getCountApproval($jnsapp,$_SESSION['empl']['lokasitugas']);
		for($i=1;$i<=$countApp;$i++)
		{
			$optApp="";
			$arrlistapp = listApprove($i,$jnsapp,$_SESSION['empl']['lokasitugas']);
			foreach($arrlistapp as $key=>$val)
			{
				$optApp.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
			}
			echo"<tr>
				<td class=bintang>".$_SESSION['lang']['persetujuan']." ".$i."</td>
				<td>:</td>
				<td>
					<select class='select2' id='persetujuan".$i."' style='width:203px;'>".$optApp."</select>
				</td>
			</tr>";
		}
		echo "</tbody><tr id='jenisusaharow'>
			<td class=bintang valign='top'>".$_SESSION['lang']['jenisusaha']."</td> 
			<td valign='top'>:</td>
			<td>
		<select id='jenisusaha' style='height:100px;width:100%;' multiple=''>";
	echo optRejuice($klsup,'','tipe','kode');
	echo "</select><script>create_multipleSelect('jenisusaha');</script></td></tr>
	  <tr><td><td><td>
	  <input type=hidden id=methodSupplier value=insert>
	<button class=mybutton onclick=saveSupplier()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>	  
	  </td></td></td></tr></table></fieldset>";

CLOSE_BOX();
OPEN_BOX('','');
	$form  = "<fieldset>
		 <legend>".$_SESSION['lang']['list']." <span id=captiontipe></span><span id=captionkelompok></span></legend>";
    $form  .= "<fieldset style='float:left'><table cellpadding=2 cellspacing=0>
	  <tr>
	      <td>".$_SESSION['lang']['carisupplierid']."</td>
	      <td>:</td>
	      <td><input type=text id=txtNoakun onkeypress='key=getKey(event); if(key==13){loadData1(0)}' onkeydown=\"upperCaseF(this)\" maxlength=30 class=myinputtext style='width:203px;box-sizing:border-box'></td>
	      <td style='width:30px'></td>
	      <td>".$_SESSION['lang']['carinamasupplier']."</td>
	      <td>:</td>
	      <td><input type=text id=txtsearch onkeypress='key=getKey(event); if(key==13){loadData1(0)}' onkeydown=\"upperCaseF(this)\" maxlength=30 class=myinputtext style='width:203px;box-sizing:border-box'></td>
	  </tr>
	  <tr>
	      <td>".$_SESSION['lang']['status'] . " " . $_SESSION['lang']['supplier']."</td>
	      <td>:</td>
	      <td><select class=select2 id=caristatusup style=\"width:203px;\">".$optcaristatus."</select></td>
	      <td></td>
	      <td>".$_SESSION['lang']['badanusaha']."</td>
	      <td>:</td>
	      <td><select class=select2 id=caribadan style=\"width:203px;\">".$optcaribadan."</select></td>
	  </tr>
	  <tr>
	      <td colspan=3 style='padding-top:6px'>
	          <button class=mybutton onclick=loadData1(0)>".$_SESSION['lang']['find']."</button>
	          <button class=mybutton onclick=cancelsearch()>".$_SESSION['lang']['cancel']."</button>
		          <button class=mybutton onclick=\"tarikSupplier('aktif','excel')\">".$_SESSION['lang']['excel']."</button>
		          <button class=mybutton onclick=\"tarikSupplier('aktif','pdf')\">PDF</button>
	      </td>
	  </tr>
	  </table></fieldset><div style='clear:both;height:10px'></div>";

	$form  .="
	     <table id=container cellspacing=1 border=0 width=100% style='background-color:#76B1DA;font-size:12px'>
			<script>loadData1(0,'".$xxx."')</script>
		 </table>
		 </fieldset>";
	
	$form2  = "<fieldset>
		 <legend>".$_SESSION['lang']['list']." <span id=captiontipe></span><span id=captionkelompok></span></legend>";
    $form2  .= "<fieldset style='float:left'><table cellpadding=2 cellspacing=0>
	  <tr>
	      <td>".$_SESSION['lang']['carisupplierid']."</td>
	      <td>:</td>
	      <td><input type=text id=txtNoakuncalon onkeypress='key=getKey(event); if(key==13){loadDatacalon(0)}' onkeydown=\"upperCaseF(this)\" maxlength=30 class=myinputtext style='width:203px;box-sizing:border-box'></td>
	      <td style='width:30px'></td>
	      <td>".$_SESSION['lang']['carinamasupplier']."</td>
	      <td>:</td>
	      <td><input type=text id=txtsearchcalon onkeypress='key=getKey(event); if(key==13){loadDatacalon(0)}' onkeydown=\"upperCaseF(this)\" maxlength=30 class=myinputtext style='width:203px;box-sizing:border-box'></td>
	  </tr>
	  <tr>
	      <td>".$_SESSION['lang']['status'] . " " . $_SESSION['lang']['supplier']."</td>
	      <td>:</td>
	      <td><select class=select2 id=caristatuscalon style=\"width:203px;\">".$optcaristatuscalon."</select></td>
	      <td></td>
	      <td>".$_SESSION['lang']['badanusaha']."</td>
	      <td>:</td>
	      <td><select class=select2 id=caribadancalon style=\"width:203px;\">".$optcaribadan."</select></td>
	  </tr>
	  <tr>
	      <td colspan=3 style='padding-top:6px'>
	          <button class=mybutton onclick=loadDatacalon(0)>".$_SESSION['lang']['find']."</button>
	          <button class=mybutton onclick=cancelsearchcalon()>".$_SESSION['lang']['cancel']."</button>
		          <button class=mybutton onclick=\"tarikSupplier('calon','excel')\">".$_SESSION['lang']['excel']."</button>
		          <button class=mybutton onclick=\"tarikSupplier('calon','pdf')\">PDF</button>
	      </td>
	  </tr>
	  </table></fieldset><div style='clear:both;height:10px'></div>";

	$form2  .="
	     <table id=caloncontainer cellspacing=1 border=0 width=100% style='background-color:#76B1DA;font-size:12px'>
		 </table>
	</fieldset>";
	
$judul[0] 	= $_SESSION['lang']['supplier'];
$frm[0] 	= $form;
$judul[1] 	= $_SESSION['lang']['calonsupplier'];
$frm[1] 	= $form2;
	
drawTab('FRM', $judul, $frm, 170, '100%');
CLOSE_BOX();
// echo "</div>";
?>
<?php echo close_body(); ?>