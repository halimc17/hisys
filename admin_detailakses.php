<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>
<script language=javascript1.2 src='js/admin_detailakses.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?

##GET USER
$optnamauser="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select namauser from ".$dbname.".user order by namauser";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optnamauser.="<option value='".$bar['namauser']."'>".$bar['namauser']."</option>";
}

##GET ORGANITATION
$arrOrg = array();
$str = "select kodeorganisasi,namaorganisasi,induk from ".$dbname.".organisasi where length(kodeorganisasi)='4' order by tipe asc,induk asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
	$arrOrg[$bar['induk']][$bar['kodeorganisasi']] = $bar['namaorganisasi'];
}

OPEN_BOX('','<span class=judul>'.getMenu('admin_detailakses').'</span>');

echo"<fieldset style='float:left;'>
        <table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['user']."</td>
				<td>:</td>
				<td>
					<select class=select2 id=namauser style=\"min-width:168px;\" onchange=\"getorg()\">".$optnamauser."</select>
					<img id='namauser' onclick=z.elSearch('namauser',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<fieldset style='clear:both'><legend><b>Permission</b></legend>
						<div id='printContainer'>";
						echo "<li style='list-style-type:none'><input type='checkbox' onclick=getseluruhnya(); id='chseluruhnya' value='0' disabled />Seluruhnya</li>";
						$no=0;
						foreach($arrOrg as $pt => $vunit){
							$no++;
							$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
							if($n!=$pt){
								echo "<div style=clear:both;></div><hr>";
								echo "<div style=clear:both;font-weight:bold;>".$pt." - ".$nmpt[$pt]."</div>";
							}
							foreach($vunit as $key => $val){
								echo "<li style='float:left;width:400px;list-style-type:none'><input type='checkbox' id='chkorg' name='chkorg[]' value='".$key."' disabled /> ".$key." - ".$val."";


								if(getNamaOrg($key,'tipe')=='KEBUN'){
									$str = "select kodeorganisasi,namaorganisasi,induk from ".$dbname.".organisasi where length(kodeorganisasi)='6' and induk='".$key."' and tipe in ('AFDELING','BIBITAN') order by tipe asc,kodeorganisasi asc";
									$res=fetchdata($str);
									foreach($res as $bar){										
										echo "<ul style='float:left;width:400px;list-style-type:none'><input type='checkbox' id='chkorg' name='chkorg[]' value='".$bar['kodeorganisasi']."' disabled /> ".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</ul>";
									}
								}
								echo "</li>";
							}
							$n=$pt;
						}
						
						// foreach($arrOrg as $key=>$val){
							// echo "<li style='float:left;width:400px;list-style-type:none'><input type='checkbox' id='chkorg' name='chkorg[]' value='".$key."' disabled /> ".$key." - ".$val."</li>";
						// }
						echo"</div>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button onclick=simpan() class=mybutton name=simpan id=simpan>".$_SESSION['lang']['save']."</button>
					<button onclick=batal() class=mybutton name=batal id=batal>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>
</fieldset>";

CLOSE_BOX();
echo close_body();

?>