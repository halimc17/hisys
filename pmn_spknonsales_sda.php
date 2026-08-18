<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/pmn_spknonsales_sda.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/pmn_spk_print.js?v=<?php echo time(); ?>'></script>
<?php

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
	
OPEN_BOX('','<span class=judul>'.strtoupper($namajenis).'</span><br><br>');

$optbuyer=$optpelayaran=$optfranco=$optttd=$optkapal=$optpt=$optponton=$optsurveyor=$optbarang=$optnosip=$optnpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

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

$str = "select * from ".$dbname.".organisasi where tipe='PT'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option  value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}


$str = "select * from ".$dbname.".pmn_suratperintahpengiriman";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optnosip.="<option  value='".$bar['nodo']."'>".$bar['nodo']."</option>";
}
/*


					
				</tr>		
*/
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
					
					<td>Kota <i>(dibuat di)</i></td>
					<td>:</td>		
					<td>
						<input type=text id=kota  onkeypress=\"return tanpa_kutip(event)\"  class=myinputtext style=\"width:150px;\">
					</td>
					
					
					
					
				
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>		
					<td>
						<select id=kodept style=\"width:155px;\">'".$optpt."'</select>
					</td>
					
					
					
					<td>Parameter</td>
					<td>:</td>		
					<td>
						<input type=text id=parameter  onkeypress=\"return tanpa_kutip(event)\"  class=myinputtext style=\"width:150px;\">
					</td>
					
					
					
				
						<td>".$_SESSION['lang']['tandatangan']." <i><b>I</b></i></td>
					<td>:</td>		
					
					<td><select style=\"width:155px;\" id=tandatangan>" . $optttd . "</select>
						<img id='tandatangan' onclick=z.elSearch('tandatangan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
				
				
				</tr>
				 <tr>
					
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:150px;\">
					</td>
					<td>".$_SESSION['lang']['tanggal']." Pelaksanaan</td>
					<td>:</td>		
					<td>
						<input type='text' class='myinputtext' id='tanggalpelaksanaan' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:150px;' readonly/>
					</td>
					
					<td>".$_SESSION['lang']['tandatangan']." <i><b>II</b></i></td>
					<td>:</td>		
					
					<td><select style=\"width:155px;\" id=tandatangan2>" . $optttd . "</select>
						<img id='tandatangan2' onclick=z.elSearch('tandatangan2',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
					
					
				</tr>
				
				
				<tr>
					<td>".$_SESSION['lang']['komoditi']."</td>
					<td>:</td>		
					<td>
						<select id=kodebarang style=\"width:155px;\">'".$optbarang."'</select>
						<img id='kodebarang' onclick=z.elSearch('kodebarang',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					
					<td>".$_SESSION['lang']['kuantitas']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=kuantitas  onkeyup=\"z.numberFormat('kuantitas');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					Kg</td>
					
					<td valign=top rowspan=3>".$_SESSION['lang']['sample']."</td> 
					<td valign=top rowspan=3>:</td>
					<td  rowspan=3><textarea rows='3' maxlength=1024 id=sample type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:305px;\"></textarea>
					</td>
					
				</tr>

				<tr>
					<td>SIP No.</td>
					<td>:</td>
			
					
					
					<td><select style=\"width:155px;\" id=nosip>" . $optnosip . "</select>
						<img id='nosip' onclick=z.elSearch('nosip',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
				
					
					
					
					<td>".$_SESSION['lang']['transportir']."</td>
					<td>:</td>		
					<td>
						<select id=transportir style=\"width:155px;\" onchange=getkapalponton()>'".$optpelayaran."'</select>
						<img id='transportir' onclick=z.elSearch('transportir',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>	
				</tr>
				
				<tr>
					<td>".$_SESSION['lang']['namakapal']."</td>
					<td>:</td>		
					<td>
						
						<select id=namakapal style=\"width:155px;\">'".$optkapal."'</select>
						<img id='namakapal' onclick=z.elSearch('namakapal',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					<td>".$_SESSION['lang']['namaponton']."</td>
					<td>:</td>		
					<td>
						<select id=namaponton style=\"width:155px;\">'".$optponton."'</select>
						<img id='namaponton' onclick=z.elSearch('namaponton',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['surveyor']."</td>
					<td>:</td>		
					<td>
						<select id=surveyor style=\"width:155px;\">'".$optsurveyor."'</select>
						<img id='surveyor' onclick=z.elSearch('surveyor',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
					
					<td>".$_SESSION['lang']['rupiah']."</td>
					<td>:</td>		
					<td>
						<input type=text  id=rupiah  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:150px;\">
					</td>
					
					<td valign=top rowspan=2>".$_SESSION['lang']['pekerjaan']."</td> 
					<td valign=top rowspan=2>:</td>
					<td  rowspan=2><textarea rows='2' maxlength=1024 id=pekerjaan type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:305px;\"></textarea>
					</td>
					
					
				</tr>	
				
				<tr>
					<td>Tempat Pelaksanaan</td>
					<td>:</td>		
					<td>
					
						<select id=tempatpelaksanaan style=\"width:155px;\">'".$optfranco."'</select>
						<img id='tempatpelaksanaan' onclick=z.elSearch('tempatpelaksanaan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
				
					
					<td>Pelabuhan Tujuan</td>
					<td>:</td>		
					<td>
						<select id=pelabuhantujuan style=\"width:155px;\">'".$optfranco."'</select>
						<img id='pelabuhantujuan' onclick=z.elSearch('pelabuhantujuan',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>&nbsp;
					</td>
					
				</tr>	
				
				
		
				<tr>
					<td>&nbsp</td>
					
					
					
					
				</tr>	
				
				

			

                <tr><td colspan=9 align=center>
						<button class=mybutton onclick=save()>Simpan</button>
						<button class=mybutton onclick=cancel()>Hapus</button>
						 <button class=mybutton onclick=\"kembalispknonsales('pmn_spknonsales')\">Kembali</button>
					</td>
                </tr>

        </table></fieldset>
		<input type=hidden id=method value='insert'>";
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
				<th align=center>".$_SESSION['lang']['surveyor']."</th>
				<th align=center>No. SIP</th>
				<th align=center>".$_SESSION['lang']['transportir']."<br><br>".$_SESSION['lang']['namakapal']."".$_SESSION['lang']['namaponton']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</th>
				<th align=center>Tempat<br>".$_SESSION['lang']['tanggal']."<br>Pelaksanaan</th>
				<th align=center>".$_SESSION['lang']['pelabuhantujuan']."</th>
				<th align=center>Kota<br>(dibuat di)<br><br>".$_SESSION['lang']['tandatangan']."</th>
				<th align=center>".$_SESSION['lang']['rupiah']."</th>
				<th align=center>".$_SESSION['lang']['sample']."</th>
				<th align=center>".$_SESSION['lang']['pekerjaan']."</th>
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