<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/setup_varianharga.js'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php


$optsupp=$optunit=$opttipe=$optkelbrg=$optvhc= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi
		where length(kodeorganisasi)=4 and tipe not in ('HOLDING','KANWIL') order by kodeorganisasi asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}



$opttipe.="<option value='inv'>".$_SESSION['lang']['barang']."</option>";
$opttipe.="<option value='vhc'>".$_SESSION['lang']['kendaraan']."</option>";

OPEN_BOX('','<span class=judul>'.getMenu('setup_varianharga').'</span>');

echo"<fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td>
				<select id=unit  onchange=getdata();  style=\"width:150px;\">" . $optunit . "</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td>
				<input style=\"width:150px;\" type='text' class='myinputtext' id='tgl' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:70px; \" />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td> 
			<td>:</td>
			<td>
				<select id=tipe onchange=getdata(); style=\"width:150px;\">" . $opttipe . "</select>
			</td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['kodevhc']."</td> 
			<td>:</td>
			<td><select id=vhc  style=\"width:150px;\">" . $optvhc . "</select>
				<img id='vhc' onclick=z.elSearch('vhc',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['kelompokbarang']."</td> 
			<td>:</td>
			<td><select id=kelbrg  style=\"width:150px;\">" . $optkelbrg . "</select>
				<img id='kelbrg' onclick=z.elSearch('kelbrg',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['persen']."</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber  id=persen onkeypress='return angka_doang(event)' value='0' style=\"width:150px;\"></td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['rupiah']."</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber  id=rupiah onkeypress='return angka_doang(event)' value='0' style=\"width:150px;\"></td>
		</tr>
		
		
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=method value='insert'>
			</td>
		</tr>
	</table>

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
									<td><select id=unitcari style=\"width:205px;\">" . $optunit . "</select></td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['tipe']."</td>
									<td>:</td>
									<td><select id=tipecari style=\"width:205px;\">" . $opttipe . "</select></td>
								</tr>
								<tr>
									<td colspan=5><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
									<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button></td>
									
								</tr>
							</table>
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";

	
	
CLOSE_BOX();
echo close_body();                  
?>