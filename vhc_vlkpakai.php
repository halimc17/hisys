<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/vhc_vlkpakai.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$opttipe=$optOrg=$optvhc=$optbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}	

$str = "SELECT kodevhc,detailvhc FROM ".$dbname.".vhc_5master where kodeorg='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optvhc.="<option value='".$bar['kodevhc']."'>".$bar['kodevhc']." - ".$bar['detailvhc']."</option>";
}

$str = "SELECT kodebarang,namabarang FROM ".$dbname.".log_5masterbarang where namabarang like 'Ban %' and kodebarang not like '9%' order by kodebarang asc";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbrg.="<option value='".$bar['kodebarang']."'>".$bar['kodebarang']." - ".$bar['namabarang']."</option>";
}
//$opttipe="<option value=''>Pemakaian</option>";
$opttipe.="<option value='1'>Pemasangan</option>";
$opttipe.="<option value='2'>Pelepasan</option>";

?>


<?php
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>Vulkanisir Tyre Use</span>');
}else{
	OPEN_BOX('','<span class=judul>Pemasangan Ban Vulkanisir</span>');
	
}
echo "<br><br>";
/*
<tr>
	<td>".$_SESSION['lang']['unit']."</td>
	<td>:</td>
	<td><select id=kodeorg style=\"width:150px;\" >".$optOrg."</select></td>
</tr>
style=float:left
*/
$frm[0].="<fieldset style=\"width:550px;float:left\" >
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td width=100>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input type=text id=notran disabled placeholder='otomatis saat simpan' size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tipetransaksi']."</td>
					<td>:</td>
					<td><select id=tipe style=\"width:150px;\" >".$opttipe."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodevhc']."</td>
					<td>:</td>
					<td><select id=kdvhc style=\"width:150px;\" >".$optvhc."</select></td>
				</tr>
				 <tr>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>:</td>
					<td><select id=kdbrg style=\"width:150px;\" >".$optbrg."</select>
						<img id=kdbrg_find onclick=z.elSearch('kdbrg',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;>									
					</td>
				</tr>
				<tr>
					<td width=100>Posisi Roda</td>
					<td>:</td>
					<td><input type=text id=posroda size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>
				 <tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext id=tgl name=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:60px;  />
						</td>
				</tr>
				<tr>
					<td width=100>Km/Hm Pasang</td>
					<td>:</td>
					<td><input type=text id=kmhm size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" /></td>
				</tr>
				<tr>
					<td width=100>Tekanan Angin</td>
					<td>:</td>
					<td><input type=text id=tekangin size=10 class=myinputtextnumber value=0 maxlength=50 o
					ny keypress=\"return angka_doang(event);\"  style=\"width:105px;\"/></td>
				</tr>
				<tr>
					<td width=100>Keterangan</td>
					<td>:</td>
					<td><input type=text id=ket size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
				</tr>								
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
					
    $frm[0].="<fieldset  fieldset style=\"float:left\"  >
                <legend>Find</legend> 
                        <table border=0 cellpadding=1 cellspacing=1>
							<tr>
								<td width=100>".$_SESSION['lang']['notransaksi']."</td>
								<td>:</td>
								<td><input type=text id=notransch size=10 class=myinputtext  onkeypress=\"return tanpa_kutip(event);\"  style=\"width:145px;\"></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['tipetransaksi']."</td>
								<td>:</td>
								<td><select id=tipesch style=\"width:150px;\" >".$opttipe."</select></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['kodevhc']."</td>
								<td>:</td>
								<td><select id=kdvhcsch style=\"width:150px;\" >".$optvhc."</select></td>
							</tr>
                            <tr>
                                <td>".$_SESSION['lang']['kodebarang']."</td>
                                <td>:</td>
                                <td><select id='kdbrgsch' style='width:150px;'>".$optbrg."</select>
									<img id=kdbrgsch_find onclick=z.elSearch('kdbrgsch',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
								</td>
                            </tr>
							<tr>
                                <td>".$_SESSION['lang']['tanggal']."</td>
                                <td>:</td>
                                <td><input type='text' class='myinputtext' id='tglsch' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:150px; /></td>
                            </tr>
							<tr>
								<td colspan=2></td>
                                <td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
                                     <button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button></td>
                            </tr>
                        </table></fieldset>";




					


//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
$frm[0].="<fieldset fieldset style=\"width:1000px;\" >
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";



/*
//
$arr="##pabrikRep##brgRep##tgl1Rep##tgl2Rep";
$frm[1].="<fieldset style='float:left;'><legend><b>Laporan BA Pembersihan Tangki</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=pabrikRep style=\"width:163px;\" >".$optOrg."</select></td>
        </tr>
	<tr>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>:</td>
		<td><select id=brgRep style='width:163px;'>".$optBrgSch."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
		s/d
		<input type='text' class='myinputtext' id='tgl2Rep' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>	

	
	<tr>
		<td><td><td>
		<button onclick=zPreview('pabrik_slave_2pembersihantangki','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2pembersihantangki.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>		
		<button onclick=batalRep() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:300px;max-width:1150'; >
</div></fieldset>";
*/

$hfrm[0]=$_SESSION['lang']['form'];
//$hfrm[1]=$_SESSION['lang']['laporan'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();




?>

<?php
/*
OPEN_BOX();
//ISI UNTUK DAFTAR 
echo "<fieldset>";
echo "<legend><b>".$_SESSION['lang']['datatersimpan']."</b></legend>";
//echo "<div id=container>";
echo" <div id=container style='width:500px;height:400px;overflow:scroll'>";	
	echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
			 <td align=center>No</td>
                         <td align=center>".$_SESSION['lang']['pabrik']."</td>
			 <td align=center>".$_SESSION['lang']['namasupplier']."</td>
			 <td align=center>".$_SESSION['lang']['tanggal']."</td>
			 <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
			 <td align=center>".$_SESSION['lang']['harga']."</td>
			 <td align=center>*</td></tr>
		 </thead>
		 <tbody id='containerData'><script>loadData()</script>";
        
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
echo "</fieldset>";
CLOSE_BOX();
echo close_body();*/					
?>