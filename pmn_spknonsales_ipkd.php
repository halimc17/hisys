<?//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pmn_spknonsales_ipkd.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/pmn_spk_print.js?v=<?php echo time(); ?>'></script>

<?php

// OPEN_BOX('','<span class=judul>Instruksi Pemuatan Kargo</span><br><br>');

@$nokontrak=$_GET['nokontrak'];
@$tanggalkontrak=$_GET['tanggal'];
@$kodecustomer=$_GET['kodecustomer'];
@$kodebarang=$_GET['kodebarang'];
@$kodept=$_GET['kodept'];
@$jenis=$_GET['kdjenis'];


$str="select * from ".$dbname.".pmn_5jenisspk where kode='".$jenis."'";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$table=$bar['file'];
	$namajenis=$bar['nama'];
	$keterangan=$bar['keterangan'];
	


OPEN_BOX('','<span class=judul>'.strtoupper($keterangan).'</span><br><br>');


$optbuyer=$optpelayaran=$optfranco=$optdebet=$optttd=$optsurveyor=$optbarang=$opttransportirdarat=$optpt=$optkapal=$optponton=$optnoakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

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
	$opttransportirdarat.="<option value=" . $bar['supplierid'] . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
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


$str = "select * from ".$dbname.".organisasi where tipe='PT'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option  value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".pmn_5kapalponton";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($bar['jenis']=='KPL'){
		$optkapal.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}
	if($bar['jenis']=='PNT'){
		$optponton.="<option value=".$bar['kode'].">".$bar['nama']."</option>";
	}
}

$str = "select * from ".$dbname.".keu_5akun where noakun like '81101%' and detail=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optdebet.="<option  value='".$bar['noakun']."'>".$bar['noakun']." ".$bar['namaakun']."</option>";
}	

$str = "SELECT noakun, namaakun FROM ".$dbname.".keu_5akun WHERE noakun LIKE '81101%' OR noakun LIKE '81102%'";
$res = fetchdata($str);
foreach($res as $key=>$val){
	$optnoakun .= "<option value=".$val['noakun'].">(".$val['noakun'].") ".$val['namaakun']."</option>";
}

//print_r($_SESSION['empl']['regional']);
// echo"<fieldset style='width:450px;'>";
echo"<fieldset style='float:left;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                 
				<tr>
					<td>".$_SESSION['lang']['nospk']."</td>
					<td>:</td>		
					<td>
						<input type=text id=nospk size=20 placeholder='Otomatis' disabled class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>".$_SESSION['lang']['kota']." ".$_SESSION['lang']['tandatangan']."</td>
					<td>:</td>		
					<td>
						<input type=text id=kota size=20 class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>".$_SESSION['lang']['tandatangan']." I</td>
					<td>:</td>	
					<td><select style=\"width:150px;\" id=tandatangan1>" . $optttd . "</select>
					<img id='tandatangan1' onclick=z.elSearch('tandatangan1',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>								
										
					<td valign=top rowspan=4>".$_SESSION['lang']['lain']."</td> 
					<td valign=top rowspan=4>:</td>
					<td  rowspan=4><textarea rows='4' id=lain type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:150px;\">".@$isitexarea."</textarea>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>		
					<td>
						<select id=kodept style=\"width:150px;\">'".$optpt."'</select>
					</td>
					
					<td>".$_SESSION['lang']['jenis']."</td>
					<td>:</td>		
					<td>
						<input type=text id=jenis value='".$jenis."' size=20 disabled class=myinputtext style=\"width:150px;\">
					</td>
					
					<td>".$_SESSION['lang']['tandatangan']." II</td>
					<td>:</td>	
					<td><select style=\"width:150px;\" id=tandatangan2>" . $optttd . "</select>
					<img id='tandatangan2' onclick=z.elSearch('tandatangan2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>						
				</tr>
				 <tr>
					
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\">
					</td>
					
					
					<td>".$_SESSION['lang']['rupiah']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=rupiah disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					</td>
					<td>".$_SESSION['lang']['harga']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=harga value='Rp    ,- per Kg (Diluar PPN, termasuk PPH)'  class=myinputtext onkeypress=\"return tanpa_kutip(event);\"  style=\"width:150px;\">
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['transportir']." ".$_SESSION['lang']['kapal']."</td>
					<td>:</td>		
					<td>
						<select id=transportir style=\"width:150px;\">'".$optpelayaran."'</select>
						<img id='transportir' onclick=z.elSearch('transportir',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					<td>".$_SESSION['lang']['transportir']." ".$_SESSION['lang']['darat']."</td>
					<td>:</td>		
					<td>
						<select id=transportirdarat style=\"width:150px;\">'".$opttransportirdarat."'</select>
						<img id='transportirdarat' onclick=z.elSearch('transportirdarat',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					<td>".$_SESSION['lang']['debet']."</td>
					<td>:</td>		
					<td>
						<select id=debet  style=\"width:150px;\">'".$optdebet."'</select>
						<img id='debet' onclick=z.elSearch('debet',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
				</tr>
				<tr>
					<td>".$_SESSION['lang']['komoditi']."</td>
					<td>:</td>		
					<td>
						<select id=kodebarang  style=\"width:150px;\">'".$optbarang."'</select>
						<img id='kodebarang' onclick=z.elSearch('kodebarang',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					<td>".$_SESSION['lang']['kuantitas']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitas value='".number_format(@$kuantitas)."' onkeyup=\"z.numberFormat('kuantitas');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg</td>
										
					<td>Rp/Kg</td>
					<td>:</td>		
					<td>
						<input type=text id=rpkg value='".number_format(@$rpkg)."' onkeyup=\"z.numberFormat('rpkg');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					</td>
				</tr>
				
				
				
				<tr>
					<td>".$_SESSION['lang']['pelabuhanmuat']."</td>
					<td>:</td>		
					<td>
						<select id=pelabuhanmuat style=\"width:150px;\">'".$optfranco."'</select>
						<img id='pelabuhanmuat' onclick=z.elSearch('pelabuhanmuat',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					<td>".$_SESSION['lang']['kuantitaskemasan']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitaskemasan  onkeyup=\"z.numberFormat('kuantitaskemasan');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\"> Kg
					</td>
									
					<td>Toleransi</td>
					<td>:</td>		
					<td>
						<input type=text id=toleransi onkeypress=empty1() class=myinputtextnumber style=\"width:150px;\" value=0> %
					</td>
				</tr>	
				
				<tr>
					<td>".$_SESSION['lang']['pelabuhantujuan']."</td>
					<td>:</td>		
					<td>
						<select id=pelabuhantujuan style=\"width:150px;\">'".$optfranco."'</select>
						<img id='pelabuhantujuan' onclick=z.elSearch('pelabuhantujuan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					<td>".$_SESSION['lang']['tanggalmuat']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext  style=\"width:60px;\" readonly id=tanggalmuat1 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\">
						s/d
						<input type=text class=myinputtext  style=\"width:60px;\" readonly id=tanggalmuat2 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\">
					</td>					
									
					<td>Kg Toleransi</td>
					<td>:</td>		
					<td>
						<input type=text id=kgtoleransi onkeypress=empty2() class=myinputtextnumber style=\"width:150px;\" value=0>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namakapal']."</td>
					<td>:</td>		
					<td>
						
						<select id=namakapal style=\"width:150px;\">'".$optkapal."'</select>
						<img id='namakapal' onclick=z.elSearch('namakapal',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					<td>".$_SESSION['lang']['namaponton']."</td>
					<td>:</td>		
					<td>
						<select id=namaponton style=\"width:150px;\">'".$optponton."'</select>
						<img id='namaponton' onclick=z.elSearch('namaponton',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>	

					<td>" . $_SESSION['lang']['noakun'] . "</td>
					<td>:</td>		
					<td>
						<select id=noakun style=\"width:153px;\">'" . $optnoakun . "'</select>
						<img id='noakun' onclick=z.elSearch('noakun',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;'>
					</td>
				</tr>
				<tr>
					
					<td>
						&nbsp;
					</td>
						
					
				</tr>
						
				
                <tr><td colspan=9 align=center>
                       
                                <button class=mybutton onclick=save()>Simpan</button>
                                <button class=mybutton onclick=cancel()>Hapus</button>
                                <button class=mybutton onclick=\"kembalispknonsales('pmn_spknonsales')\">Kembali</button>
                        </td>
                </tr>

        </table></fieldset>
		<input type=hidden id=method value='insert'>";
 // echo"<br><a href=javascript:history.back(-1)>Back</a>";      
 // $attribut = "style='cursor:pointer;text-decoration: underline' title='Click to Detail' onclick=\"kembalispk('pmn_spk','".$nokontrak."','".$tanggalkontrak."','".$kodecustomer."')\";";
// echo"<table><tr><td ".$attribut.">x</td></tr></table>";

 
CLOSE_BOX();

OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['find']."</legend>
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
		</table>
		</fieldset>
		";
			echo"<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['kodept']."</th>
				<th align=center>".$_SESSION['lang']['komoditi']."</th>
				<th align=center>".$_SESSION['lang']['nospk']."<br><br>".$_SESSION['lang']['jenis']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['transportir']."<br><br>".$_SESSION['lang']['kapal']." & ".$_SESSION['lang']['darat']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</th>
				<th align=center>".$_SESSION['lang']['kuantitaskemasan']."<br>(Kg)</th>
				<th align=center>".$_SESSION['lang']['bongkarmuat']."</th>
				<th align=center>".$_SESSION['lang']['tanggalmuat']."</th>
				<th align=center>".$_SESSION['lang']['namakapal']."<br><br>".$_SESSION['lang']['namaponton']." </th>
				<th align=center>".$_SESSION['lang']['harga']."</th>
				<th align=center>".$_SESSION['lang']['lain']."</th>
				<th align=center>".$_SESSION['lang']['tandatangan']."</th>
				<th align=center>".$_SESSION['lang']['kota']."</th>
				<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
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