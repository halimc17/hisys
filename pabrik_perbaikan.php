<? //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
if (empty(getOrgDetail(13))) {
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if ($_SESSION['empl']['tipelokasitugas'] != 'PABRIK') {
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
OPEN_BOX('', '<span class=judul>' . getMenu('pabrik_perbaikan') . '</span>');
//print_r($_SESSION['temp']);
?>
<script language=javascript src='js/pabrik_perbaikan.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php
$optKaryawan = $optKaryawanpemohon = $optTuntas = $optStation = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

##untuk pilihan pabrik 	
$optPabrik = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
// $str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
foreach (getOrgDetail(13) as $key => $value) {
	$optPabrik .= "<option value=" . $key . ">" . $value . "</option>";
}

$str = "select kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi "
	. " where induk='" . $_SESSION['empl']['lokasitugas'] . "' and tipe in ('STATION','MAINTENANCE')";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optStation .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$str = "select a.karyawanid,a.namakaryawan,a.nik from " . $dbname . ".datakaryawan a
		left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		where a.lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "' 
                    and a.tipekaryawan not in ('0','7','8') and
			(b.namajabatan like '%mecha%' or b.namajabatan like '%process%' or b.namajabatan like '%Krani%' or b.namajabatan like '%Asisten%' 
                        or b.namajabatan like '%maintenance%' or b.namajabatan like '%elect%' or b.namajabatan like '%elektri%' or b.namajabatan like '%mekanik%' or b.namajabatan like '%Mandor Pabrikasi%' or b.namajabatan like '%Operator Bubut%' or subbagian='" . $_SESSION['empl']['lokasitugas'] . "10' or subbagian='" . $_SESSION['empl']['lokasitugas'] . "17')
                        ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optKaryawan .= "<option value=" . $bar['karyawanid'] . ">" . $bar['namakaryawan'] . " [" . $bar['nik'] . "]</option>";
}

$optTuntas .= "<option value='Rencana'>Rencana</option>";
$optTuntas .= "<option value='Lanjut'>Lanjut</option>";
$optTuntas .= "<option value='Selesai'>Selesai</option>";
$optTuntas .= "<option value='Tunda'>Tunda</option>";

#untuk nama pemohon dibuat jadi opt
// $str="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a
// left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
// where a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' and 
// (b.namajabatan like '%Maintenance%' or b.namajabatan like '%MEKANIK%') ";
$str = "select a.karyawanid,a.namakaryawan,a.nik,a.lokasitugas from " . $dbname . ".datakaryawan a
		left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		where (a.lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d") . "') and subbagian !=''  order by a.namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['karyawanid'] == $_SESSION['standard']['userid'])
	{$select="selected=selected";}
	else
	{$select="";}
	$optKaryawanpemohon .= "<option ".$select." value='" . $bar['karyawanid'] . "'>" . $bar['namakaryawan'] . " [" . $bar['nik'] . "] [" . $bar['lokasitugas'] . "]</option>";
}

#buat pilihan status pemohon
$optStPemohon = "<option value='R'>Manager</option>";
$optStPemohon .= "<option value='A'>Asisten</option>";
$optStPemohon .= "<option value='P'>Processing</option>";
$optStPemohon .= "<option value='M'>Maintenance</option>";
$optStPemohon .= "<option value='L'>Luar</option>";

#buat tipe perbaikan
//8. Type Perbaikan ( default value = Prev. Maintenance, Kalibrasi, Project, Pabrikasi )
$optPerbaikan = "<option value='prev'>Preventive Maintenance</option>";
$optPerbaikan .= "<option value='mayor'>Mayor Maintenance</option>";
// $optPerbaikan.="<option value='kalibrasi'>Kalibrasi</option>";
// $optPerbaikan.="<option value='project'>Project</option>";
// $optPerbaikan.="<option value='pabrikasi'>Pabrikasi</option>";
$optPerbaikan .= "<option value='corrective'>Corrective Maintenance</option>";
// $optPerbaikan.="<option value='service'>Service</option>";
#shift
$optShift = '';
for ($i = 1; $i <= 3; $i++) {
	$optShift .= "<option value='" . $i . "'>" . $i . "</option>";
}

#buat jam dan menit
$jm = $mnt = "";
for ($i = 0; $i < 24;) {
	if (strlen($i) < 2) {
		$i = "0" . $i;
	}
	$jm .= "<option value=" . $i . ">" . $i . "</option>";
	$i++;
}
for ($i = 0; $i < 60;) {
	if (strlen($i) < 2) {
		$i = "0" . $i;
	}
	$mnt .= "<option value=" . $i . ">" . $i . "</option>";
	$i++;
}

$optKondisi = "<option value='normal'>Normal</option>";
$optKondisi .= "<option value='perbaikan'>Perlu Perbaikan</option>";
$optKondisi .= "<option value='rusak'>Rusak</option>";

#default mesin
$optMesin = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";


//EDT=>Emergency Downtime,SDT=>Sequential Downtime,CDT=>"Commercial Downtime"
$nmdownst = array('EDT' => 'EDT - Breakdown', 'SDT' => 'SDT - Non Breakdown', 'CDT' => '-');
$optmaninten = $optjnsperbaikan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arragama = getEnum($dbname, 'pabrik_rawatmesinht', 'downstatus');
foreach ($arragama as $kei => $fal) {
	$optmaninten .= "<option value='" . $kei . "'>" . $nmdownst[$fal] . "</option>";
}
$arrjnsperbaikan = getEnum($dbname, 'pabrik_rawatmesinht', 'jenisperbaikan');
foreach ($arrjnsperbaikan as $kiy => $pal) {
	$optjnsperbaikan .= "<option value='" . $kiy . "'>" . $pal . "</option>";
}
?>

<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
echo "<div id=action_list>"; //buka div
echo "<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
echo "
				<table>
					<tr>
						<td>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=schNodok  style=\"width:150px;\"></td>
						
						<td>" . $_SESSION['lang']['downstatus'] . "</td>
						<td>:</td>
						<td><select id=schdwnStat  style=\"width:150px;\">" . $optmaninten . "</select></td>

						<td>" . $_SESSION['lang']['station'] . "</td>
						<td>:</td>
						<td><select id=schstation style=\"width:150px;\">'" . $optStation . "'</select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['tanggal'] . "</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=schTgl onmousemove=setCalendar(this.id) onkeypress=return false;  style=\"width:150px;\" maxlength=10 readonly/></td>
						<td>" . $_SESSION['lang']['statusketuntasan'] . "</td>
						<td>:</td>	
						<td><select id=schstatusKetuntasan style=\"width:150px;\">'" . $optTuntas . "'</select></td>		
					</tr>
					
					<tr>
						<td></td><td></td><td><button class=mybutton onclick=cari()>" . $_SESSION['lang']['find'] . "</button></td>
					</tr>
				</table>
			";
echo "</fieldset></td>
     </tr>
	 </table> ";
CLOSE_BOX();
echo "</div>"; //tutup div
?>

<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php echo "
<div id=listData style=display:block>"; //buka list data
OPEN_BOX();
echo "
	
		<div id=contain  style=display:block> 
                    <script>loadData(0)</script>
		</div>
	";
CLOSE_BOX();
echo "</div>"; //tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

//<input type=text id=namaPemohon size=50  class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\">
//<td><select id=pabrik onchange=get_isi(this.options[this.selectedIndex].value,this.options[this.selectedIndex].text) style=\"width:150px;\">'".$optOrg."'</select></td>

echo "<div id=headher style=display:none>"; //buka diff
OPEN_BOX(); //<td><select id=kdorg disabled style=\"width:150px;\"><option  value='".$kdor."'>".$nmor."</option></select></td>
echo "
<fieldset>
<legend>Header</legend>
<table cellspacing=1 border=0 cellspacing=1 cellpadding=1>
<tr>
	<td valign=top>
		<fieldset style=height:250px>
				<legend>" . $_SESSION['lang']['formpermintaan'] . "</legend>
				<table cellpadding=1 cellspacing=1 border=0>
					<tr>
					<td>" . $_SESSION['lang']['nodok'] . "</td>
					<td>:</td>		
					<td><input type=text id=nodok size=20 disabled class=myinputtext style=\"width:150px;\"></td>
					<td>" . $_SESSION['lang']['pabrik'] . "</td>
					<td>:</td>		
					<td><select id=pabrik style=\"width:150px;\" onchange=\"getStation()\">'" . $optPabrik . "'</select></td>	
					</tr>
					<tr>
					<td>" . $_SESSION['lang']['tanggal'] . " Order</td>
					<td>:</td>
					<td><input type=text onchange=getNodok() class=myinputtext id=tglOrder onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px; readonly/>
					<select id=jmOrder>" . $jm . "</select>:<select id=mnOrder>" . $mnt . "</select></td>
					<td>" . $_SESSION['lang']['station'] . "</td>
					<td>:</td>		
					<td><select id=station onchange=getMesin() style=\"width:150px;\">'" . $optStation . "'</select></td>
					</tr>
					<tr>
					<td>" . $_SESSION['lang']['namapemohon'] . "</td>
					<td>:</td>	
					<td><select id=namaPemohon style=\"width:150px;\">'" . $optKaryawanpemohon . "'</select><img id=\"namaPemohon\" onclick=\"z.elSearch('namaPemohon',event)\" class=\"zImgBtn\" src=\"images/zoom.png\" style=\"position:relative;top:3px;left:3px;\"></td>
						
					
					
					<td>" . $_SESSION['lang']['mesin'] . "</td>
					<td>:</td>		
					<td>
						<select id=mesin style=\"width:150px;\" onchange='getSubMsn(0,0)'>'" . $optMesin . "'</select>
					 <img src=images/zoom.png title='" . $_SESSION['lang']['find'] . "' id=tmbllistdatalalu class=resicon onclick=listdatalalu('" . $_SESSION['lang']['find'] . "',event)>
					</td>
					
					</tr>
					<tr>
					<td>" . $_SESSION['lang']['statuspemohon'] . "</td>
					<td>:</td>		
					<td><select id=statusPemohon  style=\"width:150px;\">" . $optStPemohon . "</select></td>
					
					<td>Jenis Perbaikan</td>
					<td>:</td>		
					<td><select id=jenisperbaikan  style=\"width:150px;\">" . $optjnsperbaikan . "</select></td>	
					
					</tr>
					<tr>
					<td  valign=top>" . $_SESSION['lang']['downstatus'] . "</td>
					<td  valign=top>:</td>		
					<td  valign=top><select id=dwnStat  style=\"width:150px;\">" . $optmaninten . "</select></td>	
					<td>" . $_SESSION['lang']['tipeperbaikan'] . "</td>
						<td>:</td>		
						<td><select id=tipePerbaikan  style=\"width:150px;\">" . $optPerbaikan . "</select></td>
					</tr>
					
					<tr>
						<td  valign=top>" . $_SESSION['lang']['uraiankerusakan'] . "</td>
					<td  valign=top>:</td>
					<td  valign=top colspan=4><textarea onkeypress=\"return tanpa_kutip(event)\" id=uraianKerusakan style=\"width:372px;\" rows=5></textarea></td>
					
						
					</tr>
					
				</table>
		</fieldset>
	</td>
	<td valign=top>
		<fieldset style=height:250px>
			<legend>" . $_SESSION['lang']['hasilkerjad'] . "</legend>
			<table cellpadding=1 cellspacing=1 border=0>
				<tr>
				<td>Jam Mulai</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tglMulai name=tglMulai onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px; readonly/>
					<select id=jmMulai>" . $jm . "</select>:<select id=mnMulai>" . $mnt . "</select></td>
					
				<td>" . $_SESSION['lang']['jamselesai'] . "</td>
				<td>:</td>
				<td><input onkeypress=\"return tanpa_kutip(event)\" type=text class=myinputtext id=tglSelesai name=tglSelesai onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px; readonly/>
					<select id=jmSelesai>" . $jm . "</select>:<select id=mnSelesai>" . $mnt . "</select></td>
				</tr>
				<tr>
				<td>" . $_SESSION['lang']['statusketuntasan'] . "</td>
				<td>:</td>	
				<td><select id=statusKetuntasan style=\"width:150px;\">'" . $optTuntas . "'</select></td>
				<td>" . $_SESSION['lang']['jumlahjamperbaikan'] . "</td>
				<td>:</td>		
				<td><input type=text id=jumlahJamPerbaikan onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:63px;\"> &nbsp;&nbsp;&nbsp;" . $_SESSION['lang']['shift'] . " <select id=shift  style=\"width:35px;\">" . $optShift . "</select></td>	
				</tr>
				
				<tr>
					<td valign=top>" . $_SESSION['lang']['komentarproses'] . "</td>
					<td valign=top>:</td>
					<td valign=top><textarea onkeypress=\"return tanpa_kutip(event)\" id=komPros style=\"width:133px;\" rows=2></textarea></td>		
					<td valign=top>" . $_SESSION['lang']['komentarperbaikan'] . "</td>
					<td valign=top>:</td>
					<td valign=top><textarea onkeypress=\"return tanpa_kutip(event)\" id=komMain style=\"width:128px;\" rows=2></textarea></td>
				</tr>
				<tr>
					<td valign=top>" . $_SESSION['lang']['hasilkerjad'] . "</td>
					<td valign=top>:</td>
					<td valign=top colspan=4><textarea id=hasilKerja style=\"width:422px;\" onkeypress=\"return tanpa_kutip(event);\" rows=6></textarea></td>
				</tr>
			</table>
		</fieldset>
	</td>
</tr>
</table>
<!--<table cellspacing=1 border=1>
	<tr>
		<td>" . $_SESSION['lang']['nodok'] . "</td>
		<td>:</td>		
		<td><input type=text id=nodok size=20 disabled class=myinputtext style=\"width:150px;\"></td>
		
	
		<td>" . $_SESSION['lang']['pabrik'] . "</td>
		<td>:</td>		
		<td><select id=pabrik disabled style=\"width:150px;\">'" . $optPabrik . "'</select></td>	
		
		 <td>Jam Mulai</td>
		<td>:</td>
		<td><input type=text class=myinputtext id=tglMulai name=tglMulai onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px; readonly/>
			<select id=jmMulai>" . $jm . "</select>:<select id=mnMulai>" . $mnt . "</select></td>
			
		<td>" . $_SESSION['lang']['jumlahjamperbaikan'] . "</td>
		<td>:</td>		
		<td><input type=text id=jumlahJamPerbaikan size=3 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:150px;\"></td>	
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['tanggal'] . " Order</td>
		<td>:</td>
		<td><input type=text onchange=getNodok() class=myinputtext id=tglOrder onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px; readonly/>
		<select id=jmOrder>" . $jm . "</select>:<select id=mnOrder>" . $mnt . "</select></td>
		
		<td>" . $_SESSION['lang']['station'] . "</td>
		<td>:</td>		
		<td><select id=station onchange=getMesin() style=\"width:150px;\">'" . $optStation . "'</select></td>
		
		<td>" . $_SESSION['lang']['jamselesai'] . "</td>
		<td>:</td>
		<td><input onkeypress=\"return tanpa_kutip(event)\" type=text class=myinputtext id=tglSelesai name=tglSelesai onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:63px; readonly/>
			<select id=jmSelesai>" . $jm . "</select>:<select id=mnSelesai>" . $mnt . "</select></td>
			
		<td rowspan=3 valign=top>" . $_SESSION['lang']['komentarproses'] . "</td>
		<td rowspan=3 valign=top>:</td>
		<td rowspan=3 valign=top><textarea onkeypress=\"return tanpa_kutip(event)\" id=komPros style=\"width:150px;\" rows=3></textarea></td>	
		

	</tr>
	<tr>
		<td>" . $_SESSION['lang']['namapemohon'] . "</td>
		<td>:</td>		
		<td><input type=text id=namaPemohon size=50  class=myinputtext onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\"></td>
		
		<td>" . $_SESSION['lang']['mesin'] . "</td>
		<td>:</td>		
		<td><select id=mesin style=\"width:150px;\" onchange='getSubMsn(0,0)'>'" . $optMesin . "'</select></td>
		
		<td>" . $_SESSION['lang']['tipeperbaikan'] . "</td>
		<td>:</td>		
		<td><select id=tipePerbaikan  style=\"width:150px;\">" . $optPerbaikan . "</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['statuspemohon'] . "</td>
		<td>:</td>		
		<td><select id=statusPemohon  style=\"width:150px;\">" . $optStPemohon . "</select></td>
		
		<td>" . $_SESSION['lang']['shift'] . "</td>
		<td>:</td>		
		<td><select id=shift  style=\"width:150px;\">" . $optShift . "</select></td>	
		
		<td>" . $_SESSION['lang']['statusketuntasan'] . "</td>
		<td>:</td>	
		<td><select id=statusKetuntasan style=\"width:150px;\">'" . $optTuntas . "'</select></td>
	
	</tr>
	
	

        
	<tr>
		<td rowspan=3 valign=top>" . $_SESSION['lang']['uraiankerusakan'] . "</td>
		<td rowspan=3 valign=top>:</td>
		<td rowspan=3 valign=top><textarea onkeypress=\"return tanpa_kutip(event)\" id=uraianKerusakan style=\"width:150px;\" rows=5></textarea></td>
		
		<td rowspan=3 valign=top>" . $_SESSION['lang']['komentarperbaikan'] . "</td>
		<td rowspan=3 valign=top>:</td>
		<td rowspan=3 valign=top><textarea onkeypress=\"return tanpa_kutip(event)\" id=komMain style=\"width:150px;\" rows=5></textarea></td>
		
		<td rowspan=3 valign=top>" . $_SESSION['lang']['hasilkerjad'] . "</td>
		<td rowspan=3 valign=top>:</td>
		<td rowspan=3 valign=top><textarea id=hasilKerja style=\"width:150px;\" onkeypress=\"return tanpa_kutip(event);\" rows=5></textarea></td>
	</tr>
    </table>-->
	
	<table>
	<tr>
	<td>
	
			<button id=savehead class=mybutton onclick=saveHeader()>" . $_SESSION['lang']['save'] . "</button>
			<button id=batal class=mybutton onclick=cancelHead()>" . $_SESSION['lang']['cancel'] . "</button>
			<button id=savehead class=mybutton onclick=add_new_data()>" . $_SESSION['lang']['baru'] . "</button>
			<button id=savehead class=mybutton onclick=\"add_form('" . $_SESSION['lang']['find'] . " No. Perbaikan','noperbaikan','<div id=formPencariandata></div>',event)\">Tambah dari Permintaan</button>
	  
		<input type=hidden id=method value='insert'>
		</td>
	</tr>
	
	
		
	
	
</table>
</fieldset>";
CLOSE_BOX();
echo "</div>";
?>



<?php
echo "<div id=detailEntry style=display:none>";
OPEN_BOX();

$frm[0] = '';
$frm[1] = '';
$frm[2] = '';
$frm[3] = '';

$frm[0] .= "<fieldset style=float:left>";
$frm[0] .= "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>";
$frm[0] .= "<table border=0 cellpadding=1 cellspacing=1>";
$frm[0] .= "<tr class=rowheader><thead>		
            <td align=center>" . $_SESSION['lang']['nodok'] . "</td>
            <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
            <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
            <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            <td align=center>*</td>
        </tr></thead>";
$frm[0] .= "<tr class=rowcontent>
			
			<td><input type=text  id=nogudang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:125px;\">
			 <img src=images/zoom.png title='" . $_SESSION['lang']['find'] . "' id=tmblCariNoGudang class=resicon onclick=tambahBarang('" . $_SESSION['lang']['find'] . "',event)></td>
			<td><input type=text  id=kodeBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
                           </td>
			<td><input type=text  id=namaBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
			<td><input type=text  id=satuanBarang disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\"></td>
                        <td><input type=text  id=jumlahBarang onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\"></td>
                        <td><input type=text  id=keteranganBarang onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
			<input type=text hidden id=hargabarang onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:75px;\"></td>
                        <td>
                            <img src=images/save.png class=resicon  title='Save Material' onclick=saveBarang()>
			</td>
     	</tr>";
$frm[0] .= "</table></fieldset>";
$frm[0] .= "<fieldset style=float:left style='display:block;'>";
$frm[0] .= "<legend><b>" . $_SESSION['lang']['list'] . "</b></legend>";
$frm[0] .= "<div id=containListBarang></div></fieldset>";
$optSbMsn = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";


/*
$frm[1].="<fieldset style=float:left>";
$frm[1].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
$frm[1].="<table border=0 cellpadding=1 cellspacing=1>";
$frm[1].="
        <tr>
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>:</td>		
            <td><input type=text id=nomor size=2 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\" disabled></td>
	    </tr>
	    <tr>
            <td>Activity</td>
            <td>:</td>		
            <td><select onchange='getactivity()' id=rincian style=\"width:150px;\"></select></td>
		</tr>
        <tr>
            <td>Rincian Activity</td>
            <td>:</td>
            <td><textarea onkeypress=\"return tanpa_kutip(event)\" id=activity style=\"width:500px;\" rows=5 disabled></textarea></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['kondisi']."</td>
            <td>:</td>		
            <td><select  id=kondisi style=\"width:150px;\">'".$optKondisi."'</select></td>
		</tr>
        <tr>
            <td>".$_SESSION['lang']['submesin']."</td>
            <td>:</td>		
            <td><select  id=sbmesin style=\"width:150px;\">'".$optSbMsn."'</select></td>
		</tr>
        <tr>
            <td><button id=savehead class=mybutton onclick=savePekerjaan()>Simpan</button></td>
        </tr>";


$frm[1].="</table></fieldset>";
$frm[1].="<fieldset style=float:left style='display:block;'>";
$frm[1].="<legend><b>".$_SESSION['lang']['list']."</b></legend>";// 
$frm[1].="<div id=containListPekerjaan></div></fieldset>";	
*/



$frm[1] .= "<fieldset style=float:left>";
$frm[1] .= "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>";
$frm[1] .= "<table border=0 cellpadding=1 cellspacing=1>";
$frm[1] .= "
        <tr>
            <td>" . $_SESSION['lang']['nourut'] . "</td>
            <td>:</td>		
            <td><input type=text id=nomor size=2 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>
	    </tr>
        <tr>
            <td>Item Check</td>
            <td>:</td>
            <td><textarea onkeypress=\"return tanpa_kutip(event)\" id=rincian style=\"width:500px;\" rows=5></textarea></td>
        </tr>
        <tr>
            <td>" . $_SESSION['lang']['kondisi'] . "</td>
            <td>:</td>		
            <td><select  id=kondisi style=\"width:150px;\">'" . $optKondisi . "'</select></td>
		</tr>
        <tr hidden>
            <td>" . $_SESSION['lang']['submesin'] . "</td>
            <td>:</td>		
            <td><select  id=sbmesin style=\"width:150px;\">'" . $optSbMsn . "'</select></td>
		</tr>
        <tr>
            <td><button id=savehead class=mybutton onclick=savePekerjaan()>Simpan</button></td>
        </tr>";


$frm[1] .= "</table></fieldset>";
$frm[1] .= "<fieldset style=float:left style='display:block;'>";
$frm[1] .= "<legend><b>" . $_SESSION['lang']['list'] . "</b></legend>"; // 
$frm[1] .= "<div id=containListPekerjaan></div></fieldset>";



$frm[2] .= "<fieldset style=float:left>";
$frm[2] .= "<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>";
$frm[2] .= "<table border=0 cellpadding=1 cellspacing=1>";
$frm[2] .= "
        <tr>
            <td>" . $_SESSION['lang']['namakaryawan'] . "</td>
            <td>:</td>		
            <td><select id=karyawan style=\"width:150px;\">'" . $optKaryawanpemohon . "'</select><img id=karyawan onclick=z.elSearch('karyawan',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
	</tr>
        <tr>
            <td><button id=save class=mybutton onclick=saveKaryawan()>Simpan</button></td>
        </tr>";

$frm[2] .= "</table></fieldset>";
$frm[2] .= "<fieldset style=float:left style='display:block;'>";
$frm[2] .= "<legend><b>" . $_SESSION['lang']['list'] . "</b></legend>"; // 
$frm[2] .= "<div id=containListKaryawan></div></fieldset>";

/*

$frm[3].="<fieldset style=float:left>";
$frm[3].="<legend><b>".$_SESSION['lang']['form']."</b></legend>";
$frm[3].="<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		<p /><fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";


*/

$hfrm[0] = $_SESSION['lang']['daftarbarang'];
$hfrm[1] = $_SESSION['lang']['listPekerjaan'];
$hfrm[2] = $_SESSION['lang']['karyawan'];
// $hfrm[3]=$_SESSION['lang']['dokumen'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 250, 1245);

//echo "<script>loadDetailBarang()</script>";
CLOSE_BOX();
echo "</div>";
echo close_body();
?>