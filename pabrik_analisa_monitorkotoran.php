<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/pabrik_analisa_monitorkotoran.js?ver=1.5></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_analisa_monitorkotoran').'</span>');

$optper=$optunit=$optkas =$optkas2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

#= option unit
$str = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." &nbsp;&nbsp;&nbsp;".$bar['namaorganisasi']."</option>";
}


$opttipe='';
$arr=getEnum($dbname,'pabrik_analisa_monitorkotoran','tipe');
foreach($arr as $kei=>$fal){
	$opttipe.="<option value='".$kei."'>".$fal."</option>";
}  


$jm=$mnt="";
for($i=0;$i<24;){
	if(strlen($i)<2){
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;){
	if(strlen($i)<2){
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}



echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
<table border=0 cellspacing=0>
  <tr>
    <td>".$_SESSION['lang']['unit']."</td><td> : </td>
    <td ><select style=width:150px id=unit>".$optunit."</select></td>

    <td>".$_SESSION['lang']['tipe']."</td><td> : </td>
    <td><select style=width:150px id=tipe>".$opttipe."</select></td>
  </tr>
  
  <tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:145px maxlength=10 readonly/></td>				

		<td>".$_SESSION['lang']['jam']."</td>
		<td> : </td>
		<td><select id=jam style=width:50px>".$jm."</select>:<select  style=width:50px id=menit>".$mnt."</select></td>
	</tr>  
	<tr>	
		<td>".$_SESSION['lang']['nourut']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=nourut  style=width:145px maxlength=8 onkeypress='return angka_doang(event)' ></td>
 
		<td>".$_SESSION['lang']['kadar']."</td>
		<td> : </td>
		<td><input type=text class=myinputtextnumber id=kadar value=0  style=width:145px maxlength=8 onkeypress='return angka_doang(event)' ></td>
	</tr>
  
	
  <input type=hidden id=method value='insert'>
  <tr>
  	<td><td>
  	<td><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
  </tr>
	 
</table>";

CLOSE_BOX();
OPEN_BOX();

echo "

	<table class=sortable cellspacing=1 cellpadding=5 border=0>
		<thead>
			<tr class=rowheader>
				
				<th align=center>No</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['tipe']."</th>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['jam']."</th>
				<th align=center>".$_SESSION['lang']['kadar']."</th>
				<th style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		<tfoot id='footData'>
		</tfoot>
		</tbody>
	</table>
";
CLOSE_BOX();
echo close_body();
?>