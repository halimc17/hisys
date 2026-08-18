<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/kebun_5tbhnpremi.js?v=<?php echo time(); ?>></script>

<!----------------------------------- Deklarasi ------------------------------------>
<?php

$str = "SELECT kodeorg FROM " . $dbname . ".kebun_5basispanen group by kodeorg";
$res    = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $res->fetch()) {
	@$optorg .= "<option value='" . $rOrg['kodeorg'] . "'>" . $rOrg['kodeorg'] . "</option>";
}

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

?>


<!------------------------- XXXXXXXXXxxxx Form Entry Data XXXXXXXXXxxxx --------------------------->
<?php
OPEN_BOX('', '<span class=judul>' . getMenu('kebun_5tbhnpremi') . '</span>');
echo "<div id=addNew style=display:none>";
echo "<fieldset style='width:400px;'  >
			<tbody>
				<legend>" . $_SESSION['lang']['entryForm'] . "</b></legend> 
				<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
					<tr>
					<input type=hidden id=idpremi>
						<td>Kodeorg</td>
						<td>:</td>
						<td><select id=kodeorg style='width:125px;'>" . $optorg . "</select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['tanggal'] . "</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=tanggal name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false; style=width:120px;/ readonly></td>
					</tr>

					<tr>
						<td>Batas Bawah Kg</td>
						<td>:</td>
						<td> <input type=text id=dari size=10 class=myinputtextnumber onkeypress='return tanpa_kutip(event)'  onkeyup=z.numberFormat('dari',2) style=\"width:120px;  \"></td>
					</tr>
					<tr>
						<td>Batas Atas Kg</td>
						<td>:</td>
						<td> <input type=text id=sampai size=10 class=myinputtextnumber onkeypress='return tanpa_kutip(event)'  onkeyup=z.numberFormat('sampai',2) style=\"width:120px;  \"></td>
					</tr>
					<tr>
						<td>Rp/kg  </td>
						<td>:</td>
						<td> <input type=text id=harga size=10 class=myinputtextnumber onkeypress='return tanpa_kutip(event)'  onkeyup=z.numberFormat('harga',2) style=\"width:120px;  \"></td>
					</tr>

					<tr>
						<td></td>
						<td></td>
						<td>
							<input type=hidden id=method value='simpan'>
							<button class=mybutton onclick=simpan()>" . $_SESSION['lang']['save'] . "</button>
							<button class=mybutton onclick=hapus()>" . $_SESSION['lang']['cancel'] . "</button>
						</td>
					</tr>
				</table>
			</tbody>
		</fieldset>";
CLOSE_BOX();
echo close_body();
echo "</div>";

// <!-------------------------------- LIST DATA --------------------------------------->

echo "<div id=listData>";
OPEN_BOX();
echo " 	<fieldset style='width:auto;'>
				<legend>" . $_SESSION['lang']['list'] . "</legend>
				<div>
					<table class=sortable cellspacing=1 cellpadding=5 border=0>
						<thead>
							<tr class=rowheader>
								<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
								<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
								<td align=center>" . $_SESSION['lang']['tanggal'] . " berlaku</td>
								<td align=center>Batas Bawah <br>>= (Kg)</td>
								<td align=center>Batas Atas <br>< (Kg)</td>
								<td align=center>" . $_SESSION['lang']['harga'] . " Rp/kg</td>   
								<td align=center colspan=4>" . $_SESSION['lang']['action'] . "</td>
							</tr>
						</thead>
						<tbody id=container>
							<script>loadData(0)</script>
						</tbody>
						<tfoot id=footData>
						</tfoot>
					</table>
				</div>
			</fieldset>";
CLOSE_BOX();
echo "</div>";
?>