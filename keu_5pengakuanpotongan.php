<?//@Copy nangkoelframework
require_once('config/connection.php');
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>

<script language=javascript1.2 src='js/keu_5pengakuanHutang.js'></script>
<?
include('master_mainMenu.php');
#ambil komponen gaji

$optKomponen="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sAkun="select  id,name from ".$dbname.".sdm_ho_component where plus=0 and id order by name";
$res=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$res->fetch())
{
    $optKomponen.="<option value='".$rAkun['id']."'>".$rAkun['name']."</option>";
}
if($_SESSION['language']=='EN'){
    OPEN_BOX('','<span class=judul>'.getMenu('keu_5pengakuanpotongan').'</span><br>');
        $zz="namaakun1 as namaakun";
}
else{
	OPEN_BOX('','<span class=judul>'.getMenu('keu_5pengakuanpotongan').'</span><br>');
        $zz="namaakun";
}

$optAkun=$opttipeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sAkun="select  noakun,".$zz." from ".$dbname.".keu_5akun where length(noakun)=7 order by noakun asc";
$res=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$res->fetch())
{
    $optAkun.="<option value='".$rAkun['noakun']."'>".$rAkun['noakun']." - ".$rAkun['namaakun']."</option>";
}

$str="select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $opttipeorg.="<option value='".$bar['tipe']."'>".$bar['tipe']."</option>";
}

echo"
<fieldset style='width:350px;float:left'><legend>".$_SESSION['form']['form']."</legend>
	<table border=0>
		<tr>
			<td>Component ".$_SESSION['lang']['tipeorganisasi']."</td><td>:</td>
			<td><select class='select2' id=tipeorganisasi style=width:150px>".$opttipeorg."</select></td>
		</tr>
		<tr>
			<td>Component ".$_SESSION['lang']['potongan']."</td><td>:</td>
			<td><select class='select2' id=potongan style=width:150px>".$optKomponen."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['debet']."</td><td>:</td>
			<td><select class='select2' id=debet style=width:150px>".$optAkun."</select>
			<img id='debet' onclick=z.elSearch('debet',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kredit']."</td><td>:</td>
			<td><select class='select2' id=kredit style=width:150px>".$optAkun."</select>
			<img id='kredit' onclick=z.elSearch('kredit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>        
	<tr><td><td><td>
	<input type=hidden id=method value='insert'>
	<button class=mybutton onclick=simpanJ()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancelJ()>".$_SESSION['lang']['cancel']."</button>
	</table>
</fieldset>


<fieldset style='width:500px;height:83px'>
<table  border=0>
	<tr>
		<td colspan=2 style='font-weight:bold'>Note:</td>
	</tr>
	<tr>
		<td style='vertical-align:top'>*</td>
		<td style='vertical-align:top'>Komponen gaji yang perlu di mapping adalah yang terkait dengan pihak ketiga seperti Jamsostek,BPJS dan Koperasi serta yang terkait dengan hutang piutang seperti hutang / piutang karyawan.</td>
	</tr>
	<tr>
		<td style='vertical-align:top'>*</td>
		<td style='vertical-align:top'>".$_SESSION['lang']['keteranganjrnlpotongan']."</td>
	</tr>
</table></fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "
	<div style='clear:both;padding-top:10px;overflow:auto;height:350px;'>
        <table class=sortable cellpadding=5 cellspacing=1 border=0>
             <thead>
                 <tr class=rowheader>
                    <td align=center>".$_SESSION['lang']['tipe']."</td>                 
                    <td align=center>Component ID</td>                 
                    <td align=center>Component Name</td>
                    <td align=center>".$_SESSION['lang']['debet']."</td>
                    <td align=center>".$_SESSION['lang']['kredit']."</td>                     
                    <td colspan=2 align=center style='width:30px;'>".$_SESSION['lang']['action']."</td></tr>
                 </thead>
                 <tbody id=container>"; 
                echo"<script>loadData()</script>";
                echo" </tbody>
                 <tfoot>
                 </tfoot>
                 </table>";
echo "</div>";
CLOSE_BOX();
echo close_body();
?>