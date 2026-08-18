<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src='js/keu_laporan.js?v=<?php echo time(); ?>'></script>

<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul><b>'.getMenu('keu_2labarugi').'</b></span><br>');

//get existing period

$str="select distinct periode from ".$dbname.".setup_periodeakuntansi
    order by periode desc";  
$optper='';

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	
 $optper.="<option value='2018-06'>06-2018</option>";

    $optrev="<option value='0'>0</option>";
    $optrev.="<option value='1'>1</option>";
    $optrev.="<option value='2'>2</option>";
    $optrev.="<option value='3'>3</option>";
    $optrev.="<option value='4'>4</option>";    
    $optrev.="<option value='5'>5</option>";     
	

 
    //=================ambil PT;  
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
    $optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optregional="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }



$optper1="<option value='akhir'>".$_SESSION['lang']['akhirltahun']."</option>";
$optper1.="<option value='lalu'>".$_SESSION['lang']['tahunlalu']."</option>";

$opttipelaporan="<option value='default'>".$_SESSION['lang']['default']."</option>";
$opttipelaporan.="<option value='detail'>".$_SESSION['lang']['detail']."</option>";


echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select id=pt style='width:180px;' onchange=getReg();>".$optpt."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['regional']."</td>
				<td>:</td>
				<td><select id=regional style='width:180px;'  onchange=getUnit()>".$optregional."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td><select id=gudang style='width:180px;'>".$optgudang."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id=periode  style='width:87px;'> ".$optper."</select>
				<select id=periodepembanding  style='width:87px;'> ".$optper."</select>
				
				<select id=periode1  onchange=hideById('printPanel') hidden>".$optper1."</select>
				</td>
			</tr>
			<tr hidden>
				<td>".$_SESSION['lang']['revisi']."</td>
				<td>:</td>
				<td><select id=revisi onchange=hideById('printPanel')>".$optrev."</select> 
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['laporan']."</td>
				<td>:</td>
				<td><select id=tipelaporan style='width:180px;'>".$opttipelaporan."</select></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td colspan=3><button class=mybutton onclick=getlaporanlabarugi('html')>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=getlaporanlabarugi('excel')>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=getlaporanlabarugi('pdf')>".$_SESSION['lang']['pdf']."</button></td>
			</tr>
		</table>
	
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset>
     <legend>".$_SESSION['lang']['form']."</legend>
    <div id=container style='width:100%;height:300px;overflow:auto;'>
    </div></fieldset>";
CLOSE_BOX();
close_body();
?>