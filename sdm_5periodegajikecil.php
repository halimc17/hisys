<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/sdm_5periodegajikecil.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5periodegajikecil').'</span><br>');
$optPrd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
for ($x = 0; $x <= 12; $x++) {
    $dte = mktime(0, 0, 0, (date('m') + 2) - $x, 15, date('Y'));
    $optPrd.="<option value=".date("Y-m", $dte).">".date("m-Y", $dte)."</option>";
}

$optunit2=$optperiode=$optStat=$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$whereunit='';

## GET UNIT
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	
	$n=$d;
	if($d!=$n){			
		$optUnit.="</optgroup>";
	}
}

echo"<fieldset style=float:left><legend>Form</legend><table>
<tr>
	<td>".$_SESSION['lang']['unitkerja']."</td>
	<td><select id=kodeorg style=\"width:200px;\">".$optUnit."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['periode']."</td>
	<td><select id=periode name=periode style='width:200px;'>".$optPrd."</select></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['tanggalmulai']."</td>
	<td><input type=text id=tanggalmulai  style='width:195px;'size=10 onkeypress=\"return false;\" class=myinputtext maxlength=10  onmouseover=setCalendar(this) readonly></td>
</tr>
<tr>
	<td>".$_SESSION['lang']['tanggalsampai']."</td><td><input type=text id=tanggalsampai style='width:195px;' size=10 onkeypress=\"return false;\" class=myinputtext maxlength=10 onmouseover=setCalendar(this) readonly></td>
</tr>

<tr><td></td><td>
	<input type=hidden id=method value='insert'>
	<button class=mybutton onclick=simpanJ()>".$_SESSION['lang']['save']."</button>
	<button class=mybutton onclick=cancelJ()>".$_SESSION['lang']['cancel']."</button>
	</td></tr></table>
	</fieldset>";

CLOSE_BOX();
OPEN_BOX();
	echo"<fieldset style=float:left><legend>Filter Pencarian</legend>
	<table>
	<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td>
		<td><select id=unitcari style=width:180px onchange=cariData()>".$optUnit."</select></td>
		
		<td>".$_SESSION['lang']['periode']."</td><td>:</td>
		<td><select id=periodecari style=width:180px onchange=cariData()>".$optPrd."</select></td>
	";
	$arrDt=array("0"=>"Tidak","1"=>"Iya");
	 
	foreach($arrDt as $rw=>$isi){
		$optStat.="<option value='".$rw."'>".$isi."</option>";
	}
	echo"<td>".$_SESSION['lang']['tutup']."</td><td>:</td>
		<td><select id=statcari style=width:80px onchange=cariData()>".$optStat."</select></td>
		<td><button class=mybutton onclick=bersihkanform()>".$_SESSION['lang']['clear']."</button></td>
	</tr>";
	echo"</table>";
	echo"</fieldset>";
	echo"<div style=clear:both></div>";
echo open_theme($_SESSION['lang']['list']);
echo "<div class='table-scroll'>";
        echo"<table class=sortable cellspacing=1 cellpadding=5 border=0 style='min-width:650px;'>
             <thead>
                 <tr class=rowheader>
                    <th style='width:50px;'>".$_SESSION['lang']['kodeorg']."</th>
                        <th>".$_SESSION['lang']['periode']."</th>
                        <th>".$_SESSION['lang']['tanggalmulai']."</th>
                        <th>".$_SESSION['lang']['tanggalsampai']."</th>
                        <th>".$_SESSION['lang']['tutup']."</th>
                        <th style='width:30px;' colspan=2>Action</th></tr>
                 </thead>
                 <tbody id=container>
				 <script>loaddata()</script>
				 "; 
            	 
        echo"	 
                 </tbody>
                 <tfoot>
                 </tfoot>
                 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>