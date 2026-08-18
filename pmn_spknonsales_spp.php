<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript src='js/pmn_spknonsales_spp.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/pmn_spk_print.js?v=<?php echo time(); ?>'></script>

<?php


$jenis=$_GET['kdjenis'];


$str="select * from ".$dbname.".pmn_5jenisspk where kode='".$jenis."'";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$table=$bar['file'];
	$namajenis=$bar['nama'];
	

OPEN_BOX('','<span class=judul>'.$namajenis.'</span><br><br>');


$optbuyer=$optpelayaran=$optfranco=$optttd=$optsurveyor=$optbarang=$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "select * from ".$dbname.".organisasi where tipe='PT'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option  value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


$str = "select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."' order by namacustomer asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbuyer.="<option selected value='".$bar['kodecustomer']."'>".$bar['namacustomer']."</option>";
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('TRANSPORTIR') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$optpelayaran.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('JASAANALISA') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$optsurveyor.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
}

$str = "select * from ".$dbname.".pmn_5franco order by franco_name asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optfranco.="<option value='".$bar['id_franco']."'>".$bar['franco_name']."</option>";
}

$str = "select * from ".$dbname.".datakaryawan where tanggalkeluar='0000-00-00' and tipekaryawan in ('0','7','8','9') order by namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optttd.="<option value='".$bar['karyawanid']."'>".$bar['nik']." ".$bar['namakaryawan']."</option>";
}

$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optbarang.="<option value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

// pelabuhanmuat=tarifkapal
// pelabuhanbongkar=tarifponton
// surveyor=namaponton
// keperluan	
// tandatangan2


//print_r($_SESSION['empl']['regional']);
// echo"<fieldset style='width:450px;'>";
echo"<fieldset>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                 
				<tr>
					
					<td>".$_SESSION['lang']['nospk']."</td>
					<td>:</td>		
					<td>
						<input type=text id=nospk size=20 placeholder='Otomatis' disabled class=myinputtext style=\"width:150px;\">
					</td>
					
						<td>".$_SESSION['lang']['jenis']."</td>
					<td>:</td>		
					<td>
						<input type=text id=jenis value='".$jenis."' size=20 disabled class=myinputtext style=\"width:150px;\">
					</td>
					
					
					
					
					
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>		
					<td>
						<select id=kodept style=\"width:150px;\">'".$optpt."'</select>
					</td>
					
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\">
					</td>
					
				
				
					
				</tr>
				 
				<tr>
					
					<td>".$_SESSION['lang']['transportir']."</td>
					<td>:</td>		
					<td>
						<select id=transportir style=\"width:155px;\">'".$optpelayaran."'</select>
					</td>
						<td>".$_SESSION['lang']['keperluan']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=keperluan  class=myinputtext onkeypress=\"return tanpa_kutip(event);\"  style=\"width:150px;\">
					</td>
				
					
				</tr>
				<tr>
					<td>".$_SESSION['lang']['komoditi']."</td>
					<td>:</td>		
					<td>
						<select id=kodebarang  style=\"width:155px;\">'".$optbarang."'</select>
					</td>
					
					<td>".$_SESSION['lang']['kuantitas']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitas  onkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg</td>
					
				</tr>
				
				
			
			
	
				
				<tr>
					<td>".$_SESSION['lang']['namakapal']."</td>
					<td>:</td>		
					<td>
						<input type=text id=namakapal  onkeypress=\"return tanpa_kutip(event)\"  class=myinputtext style=\"width:150px;\">
					</td>
				
					<td>".$_SESSION['lang']['tarifkapal']."</td>
					<td>:</td>		
					<td>
						<input type=text id=tarifkapal  onkeypress=\"return_angka_doang(event)\"  class=myinputtextnumber style=\"width:150px;\">
					</td>
						
					
					
				</tr>	
				
				<tr>
				
					<td>".$_SESSION['lang']['namaponton']."</td>
					<td>:</td>		
					<td>
						<input type=text id=namaponton  onkeypress=\"return_tanpa_kutip(event)\"  class=myinputtext style=\"width:150px;\">
					</td>
					
				
					<td>".$_SESSION['lang']['tarifponton']."</td>
					<td>:</td>		
					<td>
						<input type=text id=tarifponton  onkeypress=\"return_angka_doang(event)\"  class=myinputtextnumber style=\"width:150px;\">
					</td>
					
				
				</tr>
				
			
					
				
					
					
               <tr>
					
					<td>".$_SESSION['lang']['kota']." ".$_SESSION['lang']['tandatangan']."</td>
					<td>:</td>		
					<td>
						<input type=text id=kota size=20 class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>".$_SESSION['lang']['rupiah']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=rupiah  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					</td>
					
					
				</tr>
					<td>".$_SESSION['lang']['tandatangan']."</td>
					<td>:</td>		
					<td><select style=\"width:155px;\" id=tandatangan>" . $optttd . "</select>
						<img id='tandatangan' onclick=z.elSearch('tandatangan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
							<td>".$_SESSION['lang']['tandatangan']." 2</td>
					<td>:</td>		
					
					<td><select style=\"width:155px;\" id=tandatangan2>" . $optttd . "</select>
								<img id='tandatangan2' onclick=z.elSearch('tandatangan2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
						
					
					
						
				 <tr>
					<td>&nbsp;</td>
				 </tr>
						
                <tr><td colspan=9 align=center>
                       
                                <button class=mybutton onclick=save()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Hapus</button>
                               <button class=mybutton onclick=\"kembalispknonsales('pmn_spknonsales')\">Kembali</button>
                </tr>

        </table></fieldset>
		<input type=hidden id=method value='insert'>";

 
CLOSE_BOX();
?>


<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset style='width:1200px;'>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table>
		<tr>
			<td>".$_SESSION['lang']['kodept']." </td>
			<td>:</td>		
			<td>
				<select id=kodeptsch style=\"width:150px;\">'".$optpt."'</select>
			</td>
			<td>".$_SESSION['lang']['nospk']." </td>
			<td>:</td>		
			<td>
				<input type=text id=nospksch size=20 class=myinputtext style=\"width:150px;\">
			</td>
			<td align=center colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button>
			<button class=mybutton onclick=cancelsch() >".$_SESSION['lang']['cancel']."</button></td>
		</tr>
		<tr>
			
		</tr>
		<tr>
			
		</tr>";
		
		echo"<table class=sortable cellpadding=1 width=100% cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kodept']."</td>
				<td align=center>".$_SESSION['lang']['komoditi']."</td>
				<td align=center>".$_SESSION['lang']['nospk']."</td>
				<td align=center>".$_SESSION['lang']['jenis']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['transportir']."</td>
				<td align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</td>
				<td align=center>".$_SESSION['lang']['namakapal']."</td>
				<td align=center>".$_SESSION['lang']['namaponton']."</td>
				<td align=center>".$_SESSION['lang']['tarifkapal']."</td>
				<td align=center>".$_SESSION['lang']['tarifponton']."</td>
				<td align=center>".$_SESSION['lang']['rupiah']."</td>
				<td align=center>".$_SESSION['lang']['keperluan']."</td>
				<td align=center>".$_SESSION['lang']['kota']."</td>
				<td align=center>".$_SESSION['lang']['tandatangan']."</td>
				<td align=center>".$_SESSION['lang']['tandatangan']." 2</td>
				<td align=center style=\"width:60px;\">".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";

			
		echo"<tbody id=container>";
		echo"<script>loaddata(0)</script>";
		echo"</tbody>";
		
		
		echo"<tfoot id=footdata>";
		echo"</tfoot></table></fieldset>";
CLOSE_BOX();
echo close_body();					
?>