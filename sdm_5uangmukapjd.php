<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src='js/zMaster.js'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/sdm_5uangmukapjd.js'></script>
<script>

</script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['uangmukapjd']).'</span><br>');
$optRegional='';
$sql="select distinct(regional) as regional, nama from ".$dbname.".bgt_regional";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_OBJ);
while($res=$qry->fetch())
{
	$optRegional.="<option value='".$res->regional."'>".$res->nama."</option>";
}

$optTipe='';
$sql2="select * from ".$dbname.".sdm_5golongan order by namagolongan asc";
$qry2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
$qry2->setFetchMode(PDO::FETCH_OBJ);
while($res2=$qry2->fetch())
{
	$optTipe.="<option value='".$res2->kodegolongan."'>".$res2->namagolongan."</option>";
}

$optJenis = makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

echo"<fieldset style='width:400px;float:left;'>
     <legend><b>".$_SESSION['lang']['uangmukapjd']."</b></legend>
	 <table>
		<tr>
			<td>".$_SESSION['lang']['regional']." </td>
			<td>:</td>
			<td><select id='regional' style='width:200px'>".$optRegional."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kodegolongan']." </td>
			<td>:</td>
			<td><select id='kodegolongan' style='width:200px'>".$optTipe."</select></td>
		</tr>
		<tr>
			<td style='vertical-align:top;'>".$_SESSION['lang']['jenis']."</td>
			<td style='vertical-align:top;'>:</td>
			<td>".makeElement('jenis','select','',array('style'=>'width:200px'),$optJenis)."</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['sekali']." (Rp)</td>
			<td>:</td>
			<td><input type='text' id='sekali' class='myinputtextnumber' onKeyPress='return angka_doang(event);' value='0' size='10' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['perhari']." (Rp)</td>
			<td>:</td>
			<td><input type='text' id='perhari' class='myinputtextnumber' onKeyPress='return angka_doang(event);' value='0' size='10' /></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['hariketiga']." (Rp)</td>
			<td>:</td>
			<td><input type='text' id='hariketiga' class='myinputtextnumber' onKeyPress='return angka_doang(event);' value='0' size='10' /></td>
		</tr>
		<tr>
			<td colspan='2'></td>
			<td>
			<input type=hidden value=insert id=method>
			<input type=hidden id=kode>
			<button class=mybutton onclick=save()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
		</tr>
	 </table>
     </fieldset>";
 
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style='float:left;'>
	<legend><b>".$_SESSION['lang']['list']."</b></legend>
	<table class=sortable cellspacing=1 border=0>
     <thead>
		<tr class=rowheader>
			<td>".$_SESSION['lang']['kode']."</td>
			<td>".$_SESSION['lang']['regional']."</td>
			<td>".$_SESSION['lang']['kodegolongan']."</td>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>".$_SESSION['lang']['sekali']." (Rp)</td>
			<td>".$_SESSION['lang']['perhari']." (Rp)</td>
			<td>".$_SESSION['lang']['hariketiga']." (Rp)</td>
			<td colspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>    
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