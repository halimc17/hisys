<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_penbytbsadjust.js'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php


$optmill= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optjurnal=$optsupp= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi
		where tipe='PABRIK' order by kodeorganisasi asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optmill.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi
		where tipe='PABRIK' order by kodeorganisasi asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optmill.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$optsupp.="<option value=''>==================== SUPPLIER ====================</option>";
$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
		left join log_5supkelompok b on a.supplierid=b.supplierid
		where a.status=1 and b.tipe in ('SUPPLIERTBS') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optsupp.="<option value=" . $bar['supplierid'] . ">" . $bar['namasupplier'] . "</option>";
}


$optjurnal.="<option value='0'>&#10006</option>";
$optjurnal.="<option value='1'>&#10004</option>";


OPEN_BOX('','<span class=judul>'.getMenu('keu_penbytbsadjust').'</span><br>');


echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td><select id=unit style=\"width:200px;\">" . $optmill . "</select>
		</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input style=\"width:80px;\" type='text' class='myinputtext' id='tgl1' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:70px; \" />
				s/d
				<input style=\"width:80px;\" type='text' class='myinputtext' id='tgl2' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:70px; \" />
			</td>
        </tr>
		<tr>
			<td>".$_SESSION['lang']['supplier']."</td> 
			<td>:</td>
			<td><select id=supplier style=\"width:200px;\">" . $optsupp . "</select>
		</td>
		
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=detail() id=preview>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal() id=batal>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=methodmaster value='insertmaster'>
			</td>
		</tr>
	</table>
</fieldset>";

echo"<fieldset>
    <legend>".$_SESSION['lang']['info']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
	<li>Harga satuan untuk realisasi sudah di-input dahulu</li>
	<li>Harga selisih didapat dari total harga budget dengan total harga realisasi</li>
	<li>Menu ini digudanakan untuk melakukan jurnal balik harga beli tbs</li>
	<li>Harap sudah melakukan transaksi pembelian tbs dimenu : pemasaran->transaksi->pembelian tbs, dan melakukan posting dahulu</li>
	<li>Data yang tersimpan sudah terbuat jurnal hutang untuk supplier/kud</li>

	</table>
</fieldset>";	


echo"<fieldset id=detailform style=display:none><legend>".$_SESSION['lang']['detail']."</legend>
        <div id=detaildata></div>
    </fieldset>";


echo"<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
							
							<table>
								<tr>
									<td>".$_SESSION['lang']['unit']."</td>
									<td>:</td>
									<td><select id=unitcari style=\"width:205px;\">" . $optmill . "</select></td>
									
									<td>".$_SESSION['lang']['jurnal']." Accrual</td>
									<td>:</td>
									<td><select id=jurnalcari style=\"width:205px;\">" . $optjurnal . "</select></td>
									
									<td>".$_SESSION['lang']['supplier']."</td> 
									<td>:</td>
									<td><select id=suppliercari style=\"width:200px;\">" . $optsupp . "</select>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['tanggal']."</td>
									<td>:</td>
									<td>
										<input style=\"width:80px;\" type='text' class='myinputtext' id='tgl1cari' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:70px; \" />
										s/d
										<input style=\"width:80px;\" type='text' class='myinputtext' id='tgl2cari' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:70px; \" />
									</td>
									<td>".$_SESSION['lang']['jurnal']." ".$_SESSION['lang']['realisasi']."</td>
									<td>:</td>
									<td><select id=jurnalbalikcari style=\"width:205px;\">" . $optjurnal . "</select></td>
									
								<tr>
									<td colspan=5><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
									<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button></td>
									
								</tr>
							</table>
						</fieldset>
					</td> 
				</tr>
			</table>
			<br>
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";

	
	
	
	

### HEADER TAB ###

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript

CLOSE_BOX();
echo close_body();                  
?>