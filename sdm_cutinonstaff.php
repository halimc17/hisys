<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/nangkoelib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

?>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/sdm_cutinonstaff.js?v=<?php echo time(); ?>></script>
<script>
	// getSelect2();
</script>
<?php
	OPEN_BOX('','<span class=judul>'.getMenu('sdm_cutinonstaff').'</span>');

	
	$optkaryawansch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select karyawanid from ".$dbname.".sdm_ijin where sumber = 'CUTINONSTAFF' group by karyawanid";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optkaryawansch.="<option value=".$bar['karyawanid'].">".getNamaKaryawan($bar['karyawanid'])."</option>";
	}


	echo"<div>";
	echo   "<table cellspacing=1 border=0>
				<tbody>
					<tr valign=middle>
						<td style=width:100px;cursor:pointer; onclick=createNew() align=center>
							<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>
							".$_SESSION['lang']['new']."
						</td>
						<td style=width:100px;cursor:pointer; onclick=displayList() align=center>
							<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>
							".$_SESSION['lang']['list']."
							<td>
						</td>
						<td>
							<fieldset style='width:auto;'>
								<legend>".$_SESSION['lang']['find']."</legend>
								<table>
									<tr>
										<td align=left>".$_SESSION['lang']['notransaksi']."</td>
										<td>:</td>
										<td><input type=text id=notransaksisch onkeyup=loadData(); maxlength=20 class=myinputtext size=26 style=\"width:200px;\"></td>

										<td align=left>".$_SESSION['lang']['namakaryawan']."</td>
										<td>:</td>
										<td>
											<select style='width:195px;' id='karyawanidsch' name='periodecuti' onkeyup=loadData(); class='select2'  style='width:200px'>".$optkaryawansch."</select>
											<img id='karyawanidsch' onclick=z.elSearch('karyawanidsch',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
										</td>
										<td></td>
										<td></td>
										<td>
											<button class=mybutton onclick=loadData()>".$_SESSION['lang']['find']."</button>
											<button class=mybutton onclick=displayList()>".$_SESSION['lang']['cancel']."</button>
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>";
	echo"</div>";
	CLOSE_BOX();
?>


<?php

##Option Hours & Minute
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
	$jm.="<option value=".$i.">".$i."</option>";
	$i++;
}

for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
	$mnt.="<option value=".$i.">".$i."</option>";
	$i++;
}

$optkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrorgdet = getOrgDetail(2);
$str="select karyawanid, namakaryawan, nik, subbagian from ".$dbname.".datakaryawan where
		(tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar' and tipekaryawan !='0'   
		and lokasitugas in (".$arrorgdet.") order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	if($bar['subbagian'] == ''){
		$text = 'KANTOR';
	}else{
		$text = $bar['subbagian'];
	}

	$optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$text."</option>";
}


$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

//Pengambilan karyawan HRD
$optHRD="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and bagian in ('HCGA')  and statuskaryawan != 'Keluar' and karyawanid!='".$_SESSION['standard']['userid']."' order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optHRD.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']."</option>";
}

$optpengganti="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select karyawanid, namakaryawan,nik, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and lokasitugas='".$_SESSION['empl']['lokasitugas']."'  and statuskaryawan != 'Keluar' and tipekaryawan IN ('1','0','2','3') order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optpengganti.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}

$str1=$owlPDO->query("select idjenis,jenisijin,statuspotongan from ".$dbname.".sdm_5jenisijin where status='1'
      order by jenisijin");
$str1->setFetchMode(PDO::FETCH_OBJ);
$optJenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";  
while($bar=$str1->fetch()){
    $optJenis.="<option value='".$bar->idjenis."'>".$bar->jenisijin."</option>";
}

   

//ambil cuti ybs
// Ambil tanggal masuk ybs
$str="select right(tanggalmasuk,5) as tanggalmasuk from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid'];
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$tglmasup='';
$hrini=date('md');#default
while($bar=$res->fetch())
{
    $tglmasup=str_replace("-","",$bar->tanggalmasuk);#replace with data karyawan
}

if($tglmasup>$hrini)
{
	$tahunplafon=(date('Y')-1);
}
else
{
    $tahunplafon=date('Y');
}
   
#penguncian agar cuti yang sudah hangus tidak dapat diambil
$optPeriodec="<option value=''> </option>";

$sisa='';

if($sisa=='')
    $sisa=0;
echo "<div id=addNew style=display:none>";
	OPEN_BOX();
echo"<input type='hidden' id='method' name='method' value='insert'  />
<div id='headher'>
<fieldset style='float:left;'>
<legend>".$_SESSION['lang']['form']."</legend>
<table cellspacing='1' cellpadding=2 border='0'>
		<tr>
            <td>".$_SESSION['lang']['notransaksi']." <font size=2px style=color:red><b>*</b></font></td>
            <td>:</td>
            <td>
                <input type='text' class='myinputtext' style='width:245px;' id='notransaksi' name='notransaksi' onkeypress='return tanpa_kutip(event);' style='width:150px;' disabled/>
            </td>

            <td>".$_SESSION['lang']['karyawan']." <font size=2px style=color:red><b>*</b></font></td>
            <td>:</td>
            <td>
                <select style='width:245px;' class='select2' id='karyawanid' onchange='loadperiodecuti();'>".$optkaryawan."</select>
				<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
            </td>
            </tr>
            <tr>
            <td>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['tahun']." <font size=2px style=color:red><b>*</b></font></td>
            <td>:</td>
            <td>
                <select style='width:195px;' id='periodecuti' name='periodecuti' class='select2'  style='width:105px'>".$optPeriodec."</select>
            </td>

            <td>".$_SESSION['lang']['jenisijin']." <font size=2px style=color:red><b>*</b></font></td>
            <td>:</td>
            <td>
                <select style='width:245px;' class='select2' id='idjenis' onchange='loadSisaCuti(1)'>".$optJenis."</select>
				<img id='idjenis' onclick=z.elSearch('idjenis',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>

            </td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['tanggal']." Masuk</td>
			<td>:</td>
			<td>
				<input type='text' style='width:100px;' class='myinputtext' id='tglMasuk' value='' disabled onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
			</td>
		
			<td>".$_SESSION['lang']['tanggal']." Pengangkatan</td>
			<td>:</td>
			<td>
				<input type='text' style='width:100px;' class='myinputtext' id='tglPengangkatan' value='' disabled onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
			</td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['tanggal']." Pengajuan <font size=2px style=color:red><b>*</b></font></td>
			<td>:</td>
			<td>
				<input type='text' style='width:190px;' class='myinputtext' id='tglIzin' value='".date('d-m-Y')."' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly />
			</td>
		
		<td>".$_SESSION['lang']['keperluan']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:242px;' id='keperluan' name='keperluan' onkeypress='return tanpa_kutip(event);' maxlength='30' style='width:150px;' />
		</td>

		
	</tr>
	<tr>
		<td>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['tanggal']." & ".$_SESSION['lang']['jam']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' onchange=getjumlahcuti(); style='width:100px;' id='tglAwal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
			<select id='jam1'>".$jm."></select>:<select id='mnt1'>".$mnt."></select>
		</td>

		<td>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['tanggal']." & ".$_SESSION['lang']['jam']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' onchange=getjumlahcuti(); style='width:100px;' id='tglEnd' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' readonly/>
			<select id='jam2'>".$jm."</select>:<select id='mnt2'>".$mnt."></select>
		</td>

		
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']." Bekerja Kembali <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
				<input type=text style='width:190px;' class=myinputtext id=tanggalkerja size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" readonly>
		</td>	 
		
	
		<td>".$_SESSION['lang']['jumlahhk']." ".$_SESSION['lang']['diambil']."</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:100px;'  id='jumlahhk' name='jumlahhk' onkeypress='return angka_doang(event);' maxlength='5' value='0' disabled> ".$_SESSION['lang']['hari']." - (Hak saat ini : <span id='sis'>0</span> ".$_SESSION['lang']['hari'].")
		</td>
	</tr>
	<tr>
		<td>No. Handphone <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' style='width:190px;' class='myinputtext' onkeypress='return angka_doang(event);' id='nohp' size='10' style='width:150px;' />
		</td>
		

		<td>".$_SESSION['lang']['ganti']."</td>
		<td>:</td>
		<td>
			<select id=pengganti style=width:245px>".$optpengganti."</select>
			<img id='pengganti' onclick=z.elSearch('pengganti',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>	
	</tr>
		<tr>
		
		<td valign=top>".$_SESSION['lang']['keterangan']."</td>
		<td valign=top>:</td>
		<td>
			<textarea id='ket'  style='width:228px;'  onkeypress='return tanpa_kutip(event);'></textarea>
		</td>
		<td valign=top>".$_SESSION['lang']['alamat']."</td>
		<td valign=top>:</td>
		<td valign=top>
			<textarea id='alamatcuti'  style='width:228px;'  onkeypress=return tanpa_kutip(event);></textarea>
		</td>	
		

		</tr>
	<tr style='display:none'>
	<td>Home Trip</td>
		<td>:</td>
		<td>
			<input type='checkbox' id='hometrip' onclick='checkhometrip(this)'>
		</td>
		<td></td>
		<td></td>
		<td>
			
		</td>
	</tr>
	<tr id='trtanggalberangkat' style='display:none'>
		<td>Tanggal Berangkat</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext'  style='width:100px;' id='tglberangkat' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
		</td>
	</tr>
	<tr id='trrutekeberangkatan' style='display:none'>
		<td>Rute Keberangkatan</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:245px;' id='rutekeberangkatan' onkeypress='return tanpa_kutip(event);' maxlength='30' style='width:150px;' />
		</td>
	</tr>
	<tr id='trtanggalpulang' style='display:none'>
		<td>Tanggal Pulang</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext'  style='width:100px;' id='tglpulang' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style='width:150px;' />
		</td>
	</tr>
	<tr id='trrutekepulangan' style='display:none'>
		<td>Rute Kepulangan</td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:245px;' id='rutekepulangan' onkeypress='return tanpa_kutip(event);' maxlength='30' style='width:150px;' />
		</td>
	</tr>
	<tr>
		<td colspan=6 id='tmblHeader' style='text-align:center'>
			<button class=mybutton id=dtlForm onclick=saveForm()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton id=cancelForm onclick=cancelForm()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
	<tr>
		<td><i><b><font size=1px style=color:red;><b>*</b></font>) Kolom yang wajib terisi.</b></i></td>
	</tr>
</fieldset>
</table>
<input type='hidden' id='atsSblm' name='atsSblm' />
<input type='hidden' id='atsSblm2' name='atsSblm2' />
</div>";

CLOSE_BOX();
echo '</div>';
echo"<div id='listData'>";
OPEN_BOX();
echo"<fieldset style='width:auto;'>
<legend>" . $_SESSION['lang']['list'] . "</legend>
				<div>
					<table class=sortable style='width:100%;' cellspacing=1 cellpadding=7 cellspacing=1 border=0>
						<thead>
							<tr class=rowheader>
								<td align=center>".$_SESSION['lang']['notransaksi']."</td>
								<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
								<td align=center>".$_SESSION['lang']['tanggal']."</td>
								<td align=center>".$_SESSION['lang']['keperluan']."</td>
								<td align=center>".$_SESSION['lang']['periode']." ".$_SESSION['lang']['tahun']."</td>
								<td align=center>".$_SESSION['lang']['jenisijin']."</td>
								<td align=center>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']."</td>
								<td align=center>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']."</td>
								<td align=center>".$_SESSION['lang']['status']." pengajuan izin/cuti</td>
								<td align=center>".$_SESSION['lang']['status']." batal cuti</td>
								<td align=center colspan=6 >Action</td>
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
echo"</div>";

echo close_body();
?>