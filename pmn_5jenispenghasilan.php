<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script languange=javascript1.2 src='js/zSearch.js'></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script languange=javascript1.2 src='js/formReport.js'></script>
<script languange=javascript1.2 src='js/zGrid.js'></script>
<script languange=javascript1.2 src='js/pmn_5jenispenghasilan.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>

<?

$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".keu_5akun where substr(noakun,1,3) in ('117','213') and detail=1";//where : noakun pajak (117 dan 213) dan detail=5
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('pmn_5jenispenghasilan').'</span>');

echo"<fieldset style='width:300px;'>
	<legend>Form</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['noakun']."</td>
			<td>:</td>
			<td><select id=kodepajak style=width:155px>".$optakun."</select></td>
		</tr>
		
		<tr hidden>
			<td>Kode Penghasilan</td>
			<td>:</td>
			<td><input type=text id=kodepenghasilan maxlength=80 style=width:150px disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext ></td>
		</tr>
		
		<tr hidden>
			<td>ID Parent</td>
			<td>:</td>
			<td><input type=text id=idparent maxlength=80 style=width:150px disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		</tr>

		<tr>
			<td>Nama Penghasilan</td>
			<td>:</td>
			<td><input type=text id=namapenghasilan maxlength=80 style=width:150px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		</tr>	
		<tr>
			<td>No Urut</td>
			<td>:</td>
			<td><input type=text id=nourut maxlength=80 style=width:150px  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
		
	 </table>
	 <input type=hidden id=proses value='insert'>
	
	 </fieldset>";
	 
echo"	
<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div style='height:350px;overflow:auto;'>
	<table class=sortable cellspacing=1 cellpadding=3 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>Nama<br>Penghasilan</td>
				<td align=center>".$_SESSION['lang']['noakun']."</td>
				<td align=center>".$_SESSION['lang']['namaakun']."</td>
				<td align=center>".$_SESSION['lang']['nourut']."<br>".$_SESSION['lang']['laporan']."</td>
				<td colspan=3 style=text-align:center;>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody id=container>
		<script>loaddata(0)</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>	</div>
</fieldset>";
	 



CLOSE_BOX();
echo close_body();
?>