<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
?>
<script language=javascript1.2 src='js/master_barang.js'></script>
<?php
$kodebarang = false;
$kodebarang = checkPostGet('kodebarang','');
$namabarang = checkPostGet('namabarang','');
$prosses = '';
$prosses = checkPostGet('prosses','');
if($kodebarang == false){
	exit();
}

if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$sql = "SELECT kodeorganisasi, namaorganisasi FROM " . $dbname . ".organisasi where tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."' order by namaorganisasi asc";
	$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $qry->fetch()) {
		$optPT.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	}
}else{
	$sql = "SELECT kodeorganisasi, namaorganisasi FROM " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi asc";
	$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	$optPT = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	while ($bar = $qry->fetch()) {
		$optPT.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	}
}
echo "
		<span class='judul'>".strtoupper($_SESSION['lang']['detail']." ".$_SESSION['lang']['minstok'])."</span><br>
			
		
			<input id='methoddetail' type='hidden' class='myinputtext' name='prosses'  value='insert'>
			<input id='detailkodebarang' type='hidden' class='myinputtext' name='detailkodebarang'  value='<?php echo $kodebarang; ?>'>
				<table>
				<tr>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>:</td>
					<td><input type=text id=kodebarang_det nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\" value='".$kodebarang."' disabled>
					<input type=text id=namabarang_det nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:225px;\" value='".$namabarang."' disabled></td>
				</tr>				
				<tr>
					<td>".$_SESSION['lang']['pt']."</td> 
					<td>:</td>
					<td><select id=pt  style=\"width:315px;\">" . $optPT . "</select>
					</td>
				</tr>
				<tr style=display:none>
					<td>".$_SESSION['lang']['spesifikasi']."</td> 
					<td>:</td>
					<td><input type=text id=spesifikasi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:310px;\">
				</tr>
				<tr>
					<td>".$_SESSION['lang']['minstok']."</td> 
					<td>:</td>
					<td><input type=text  id=minstok_det nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtextnumber style=\"width:75px;\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){simpan()};return angka_doang(event);\"></td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td colspan=3>
						<button class=mybutton onclick=saveBarang()>Simpan</button>
						<button class=mybutton onclick=cancelBarang_det()>Reset</button>
					</td>
				</tr>
				</table>
		
			<div style='clear: both;'></div>
			<div style=max-height:300px;overflow:auto;'>	
			<table>
				
					<div id=containerbarang> 
					</div>
				
			</table>
			</div>
		</div>
		</div>
		</div>
	</div>";
?>