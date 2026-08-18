<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<b>".getMenu('sortasi_harian_tbs')."</b>");
?>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<script type='text/javascript' language='javascript' src='js/zMaster.js'></script>
<script type='text/javascript' language='javascript' src='js/zTools.js'></script>
<script type='text/javascript' language='javascript' src='js/sortasi_harian_tbs.js'></script>

<?php
$_SESSION['sorimage'] = array();
### Get Pabrik
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optakun.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

### Get Kebun
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optkebun.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


### Get Tipe

$opttipe.="<option value='1'>Internal</option>";
$opttipe.="<option value='0'>External</option>";

echo"<table>
	<tr valign=middle>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
		</td>
		<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
		</td>
		<td>
			<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
		
		echo"<table><tr>";
		
		echo"</tr><tr>";
		
		echo"<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=caritanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
		</td>";
		
		echo"</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button></td>
		</tr>";
		echo "</table>";
echo"</fieldset></td>
     </tr>
	 </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"	
<fieldset id=listData>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id=listData style='height:350px;overflow:auto;'>
	<table class=sortable cellspacing=1 cellpadding=3 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['pabrik']."</td>
				<td align=center>Tipe</td>
				<td align=center>".$_SESSION['lang']['kebun']."</td>
				<td align=center>Tanggal</td>
				<td align=center>Updated by</td>
				<td align=center>Posted by</td>
				<td colspan=4 style=text-align:center;>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody id=container>
		<script>loadData(0)</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>	</div>
</fieldset>";
	 

//===========================================================================
echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
	<table style=width:100%;>
		<tr>
			<td>".$_SESSION['lang']['pabrik']."</td>
			<td>:</td>
			<td>
				<select id=pt>".$optakun."</select>
			</td>	
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select id=station onchange='cektipe()'>".$opttipe."</select>
			</td>
		</tr>
		<tr id='trkebun'>
			<td>".$_SESSION['lang']['kebun']."</td>
			<td>:</td>
			<td>
				<select id=kebun>".$optkebun."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tanggal style='text-align:center' onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".date('d-m-Y')."' readonly />
			</td>
		</tr>		
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='method' value='insert'>
				<button class=mybutton id='simpanht' onclick=saveht()>".$_SESSION['lang']['preview']."</button>&nbsp;
				<button class=mybutton id='cancelht' onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>
<div style='clear:both'></div>
<div id='formdt' style='display:none'></div>"; 
echo"</div>";
CLOSE_BOX();
echo close_body(); 
?>