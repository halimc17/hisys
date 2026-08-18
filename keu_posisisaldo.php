<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript1.2 src=js/keu_posisisaldo.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('keu_posisisaldo').'</span>');

$optrek=$optunit=$optbank =$optkas2 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optOrg = getOrgDetail(1);
#= option unit
foreach ($optOrg as $key => $nmorg) {
	$str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$key."'";
	$rdata=fetchData($str);
	if(count($rdata)!=0){
		$optunit.="<option value='".$key."'>".$key."-".$nmorg."</option>";	
	}
}
$sDibuat="select distinct createdby from ".$dbname.".keu_posisisaldobank ";
$rDibuat=fetchData($sDibuat);
$optper="<option value='0'>".$_SESSION['lang']['pilihdata'] ."</option>";	
foreach ($rDibuat as $key => $val) {
	$nmKar=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$val['createdby']."'");
	$optper.="<option value='".$val['createdby']."'>".$nmKar[$val['createdby']]."</option>";	
}
$sRek="select distinct norekening from ".$dbname.".keu_posisisaldobank ";
$rRek=fetchData($sRek);
foreach ($rRek as $key => $bar) {
	$sdt="select a.namabank,a.rekening as norek,b.namabank as nmbank from ".$dbname.".keu_5akunbank a 
	      left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where a.noakun='".$bar['norekening']."'";
	$rdt=fetchData($sdt);
    $optbank.="<option value='".$bar['norekening']."' >".$rdt[0]['norek']." - ".$rdt[0]['nmbank']."</option>";
}
// for($x=0;$x<=12;$x++){
//         $dte=mktime(0,0,0,(date('m')+2)-$x,15,date('Y'));
//         $optper.="<option value=".date("Y-m",$dte).">".date("m-Y",$dte)."</option>";
// }

echo"
<br><fieldset style=float:left>
	<legend>".$_SESSION['lang']['form']."</legend>
<table border=0 cellspacing=0>
  <tr>
    <td>".$_SESSION['lang']['unit']."</td><td> : </td>
    <td ><select style=width:150px id=unit onchange='getbank(0)' >".$optunit."</select>
    <img id=unit_find onclick=z.elSearch('unit',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
    </td>
  </tr>
  <tr>
    <td>".$_SESSION['lang']['rekening']."</td><td> : </td>
    <td><select style=width:150px id=rekening>".$optrek."</select></td>
  </tr>
   <tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /><input type='text' class='myinputtext' id='jam' name='jam' style='width:60px' onkeypress='return tanpa_kutip_dan_sepasi(event)' value='00:00' maxlength='5' />
		    <input type=hidden class=myinputtext id=tanggal_lama  /><input type=hidden class=myinputtext id=jam_lama  /></td>				
	</tr>
  <tr>	
	<td>".$_SESSION['lang']['saldoberjalan']."</td>
	<td> : </td>
    <td><input type=text class=myinputtextnumber id=saldoberjalan  style=width:150px  onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('saldoberjalan');\"></td>
  </tr>
  <tr>	
	<td>".$_SESSION['lang']['estimasi']."</td>
	<td> : </td>
    <td><input type=text class=myinputtextnumber id=estimasi  style=width:150px  onkeypress='return angka_doang(event)' onkeyup=\"z.numberFormat('estimasi');\"></td>
  </tr>
  
   <tr>	
	<td>".$_SESSION['lang']['keterangan']."</td>
	<td> : </td>
    <td><input type=text class=myinputtext id=keterangan  style=width:150px  onkeypress='return tanpa_kutip(event)'\"></td>
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
echo"<fieldset style=float:left><legend>".$_SESSION['lang']['find']."</legend><table>";
echo"<tr>
<td>".$_SESSION['lang']['tanggal']."</td>";
echo"<td><input type=text class=myinputtext id=tanggalCari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>";
echo"<td>".$_SESSION['lang']['rekening']."</td>";
echo"<td><select id=rekeningCari style=width:150px>".$optbank."</select></td>";
echo"<td>".$_SESSION['lang']['dibuat']."</td>";
echo"<td><select id=createdCari style=width:150px>".$optper."</select></td>";
echo"</tr>";
echo"</table>
<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button></fieldset><div style=clear:both></div>";
echo "
<fieldset>
	<legend><b>".$_SESSION['lang']['list']."</legend>
	<table class=sortable cellspacing=1 cellspacing=1 border=0 width=100%>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['unit']."</td>
				<td align=center>".$_SESSION['lang']['namabank']."</td>
				<td align=center>".$_SESSION['lang']['rekening']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['saldoberjalan']."</td>
				<td align=center>".$_SESSION['lang']['estimasi']." Kebutuhan Treasury</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				
				<td align=center>".$_SESSION['lang']['dibuat']."</td>
				<!--<td align=center>".$_SESSION['lang']['updateby']."</td>-->
				<td style='text-align:center' colspan=3>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody id=container>
			
		<tfoot id='footData'>
		</tfoot>
		</tbody>
	</table>
</fieldset><script>loadData(0)</script>";
CLOSE_BOX();
echo close_body();
?>