<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_payrollHO.js?v=<?php echo time(); ?>></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?php
include('master_mainMenu.php');
//+++++++++++++++++++++++++++++++++++++++++++++
$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
/*$str = "select * from " . $dbname . ".sdm_ho_component
      where name like '%Angs%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$arr = Array();
$opt = '';
while ($bar = $res->fetch()) {
    $opt.="<option value=" . $bar->id . ">" . $bar->name . "</option>";
    $arr[$bar->id] = $bar->name;
}*/

$str = "select * from " . $dbname . ".setup_parameterappl
      where kodeaplikasi='AN'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$arr = Array();
$opt = '';
while ($bar = $res->fetch()) {
    $nilai= $bar->nilai;
    
}

$jns=explode(',', $nilai);

foreach ($jns as $jn) {
	$opt.="<option value=" . $jn . ">" . $jn . "</option>";
}
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $str1 = "select * from " . $dbname . ".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar > " . $_SESSION['org']['period']['start'] . ")  and statuskaryawan != 'Keluar' 
          and alokasi=1
          order by namakaryawan";
    // $str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where lenght(kodeorganisasi)='4'";	  	  
} else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {//and LEFT(lokasitugas,4)='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
    $str1 = "select * from " . $dbname . ".datakaryawan
 	 where (tanggalkeluar = '0000-00-00' or tanggalkeluar > " . $_SESSION['org']['period']['start'] . ") 
	   and statuskaryawan != 'Keluar'  and tipekaryawan not in ('0','7','8') 
	  and lokasitugas in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $_SESSION['empl']['kodeorganisasi'] . "' and kodeorganisasi not like '%HO%')
	  order by namakaryawan";
} else {
    $str1 = "select * from " . $dbname . ".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar > " . $_SESSION['org']['period']['start'] . ") 
           and statuskaryawan != 'Keluar'  and tipekaryawan not in ('0','7','8')  and LEFT(lokasitugas,4)='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "'
          order by namakaryawan";
    // $str2="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".substr($_SESSION['empl']['lokasitugas'],0,4)."'";	  
}
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$opt1="<option value=''>". $_SESSION['lang']['pilihdata'] ."</option>";
while ($bar1 = $res1->fetch()) {
    $opt1.="<option value=" . $bar1->karyawanid . ">" . $bar1->namakaryawan . " - " . $bar1->nik . " - " . $bar1->subbagian . "</option>";
}
$str2 = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi in 
   		 (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $_SESSION['empl']['kodeorganisasi'] . "' and kodeorganisasi not like '%HO%')";
$optOrg = "<option value=0>" . $_SESSION['lang']['all'] . "</option>";
;
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_ASSOC);
while ($bar2 = $res2->fetch()) {
    $optOrg.="<option value=" . $bar2['kodeorganisasi'] . ">" . $bar2['kodeorganisasi'] . " - " . $bar2['namaorganisasi'] . "</option>";
}
$sortOrg="";
if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
    $sortOrg = "
			<tr>
				<td>
					<table >
						<tr>
							<td>Filter</td>
							<td>:</td>
							<td><select id=kdOrg style=\"width:150px;\" onchange=getKar()>" . $optOrg . "</select></td>
						</tr>
					</table>						
				</td>
			</tr>";
}else{
	$sortOrg = "<select hidden id=kdOrg style=\"width:150px;\">
				<option value=".$_SESSION['empl']['lokasitugas']."></option></select>";
}
$opt3 = '';
for ($z = -64; $z <= 24; $z++) {
    $da = mktime(0, 0, 0, date('m') - $z, '1', date('Y'));
	
	if(date('Y-m', $da)==date('Y-m')){
		$opt3.="<option value='" . date('Y-m', $da) . "' selected>" . date('m-Y', $da) . "</option>";
	}else{
		$opt3.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
	}
}
OPEN_BOX('','<span class=judul>'.getMenu('sdm_angsurankaryawan').'</span>');
echo"<div id=EList><fieldset>";
//echo OPEN_THEME($_SESSION['lang']['angsuran'] . ':');
echo"<table border=0 width=100%>";
echo $sortOrg;
echo"<tr>
	 <td>";
echo"<fieldset><legend><b>" . $_SESSION['lang']['entryForm'] . "</b></legend>
	<table border=0 cellpadding=1 cellspacing=1 class=sortable>
	  <thead>
		  <tr>
				<td align=center style='width:200px;'>" . $_SESSION['lang']['namakaryawan'] . "</b></td>
				<td align=center>" . $_SESSION['lang']['jennisangsuran'] . "</b></td>
				<td align=center>" . $_SESSION['lang']['bulanawal'] . "<br>" . $_SESSION['lang']['potongan'] . "</td>
				<td align=center>" . $_SESSION['lang']['bulanakhir'] . "<br>" . $_SESSION['lang']['potongan'] . "</td>
				<td align=center style='width:80px;'>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['nilaihutang'] . "<br>(Rp.)</b></td>
				<td align=center>" . $_SESSION['lang']['jumlah'] . "<br>(" . $_SESSION['lang']['bulan'] . ")</td>
				<td align=center>Rp Per Bulan</td>
				<td align=center>" . $_SESSION['lang']['keterangan'] . "</b></td>
				<td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "1)</td>
				<td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "2)</td>
				<td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "3)</td>
				<td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "4)</td>
				<td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "5)</td>
				<td align=center>" . $_SESSION['lang']['status'] . "</td>
				<td align=center>" . $_SESSION['lang']['action'] . "</td>
		  </tr> 
		  </thead>
		  <tbody>
		  <tr class=rowcontent>
			  <td>
				<select id=userid style='width:170px;'>" . $opt1 . "</select>
				<img id='userid' onclick=z.elSearch('userid',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
					</td>
			  <td><select id=idx>" . $opt . "</select></td>
			 
			 
			  <td><select id=start onchange=\"getbulan();\">" . $opt3 . "</select></td>
			  <td><select id=finish onchange=\"getbulan();\">" . $opt3 . "</select></td>
			   <td><input type=text id=total onkeyup=\"gettot();hitung()\" class=myinputtextnumber size=9 maxlength=14 value=0 onkeypress=\"return angka_doang(event);\" onblur=change_number(this)></td>
			  <td><input type=text id=lama class=myinputtextnumber onkeyup=\"gettot()\" size=4 maxlength=3 onkeypress=\"return angka_doang(event);\" value=0></td>
			  <td><input type=text id=rpbulan onkeyup=\"hitung()\" class=myinputtextnumber size=9  onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this)></td>
			   <td><input type=text id=keterangan class=myinputtext size=25 maxlength=100 ></td>
			  <td hidden><input type=text id=tahap1 class=myinputtextnumber size=4 onkeypress=\"return angka_doang(event);\" value=0 onkeyup=\"z.numberFormat('tahap1',2)\"></td>
			  <td hidden><input type=text id=tahap2 class=myinputtextnumber size=4 onkeypress=\"return angka_doang(event);\" value=0 onkeyup=\"z.numberFormat('tahap2',2)\"></td>
			  <td hidden><input type=text id=tahap3 class=myinputtextnumber size=4 onkeypress=\"return angka_doang(event);\" value=0 onkeyup=\"z.numberFormat('tahap3',2)\"></td>
			  <td hidden><input type=text id=tahap4 class=myinputtextnumber size=4 onkeypress=\"return angka_doang(event);\" value=0 onkeyup=\"z.numberFormat('tahap4',2)\"></td>
			  <td hidden><input type=text id=tahap5 class=myinputtextnumber size=4 onkeypress=\"return angka_doang(event);\" value=0 onkeyup=\"z.numberFormat('tahap5',2)\"></td>
			  <td><select id=active><option value=1>Active</option>
			  <option value=0>Not Active</option></select>
			  <input type=hidden value='insert' id=method>
			  </td>
			  <td>
				<button class=mybutton onclick=saveAngsuran()>" . $_SESSION['lang']['save'] . "</button>
				<button class=mybutton onclick=cancelAngsuran()>" . $_SESSION['lang']['cancel'] . "</button>
			  
			  </td>
			  
		  </tr>
		  </body>
		  <tfoot>
		  <tr style=display:none><td colspan=7 align=right>
			<button class=mybutton onclick=showupload(event)>Upload Files</button>
		  </td></tr>
		  </tfoot>
		  </table>";
	echo"<div id=detailpot></div>";
/* if ($_SESSION['language'] == 'ID') {
	echo "</td><td valign=top>
	<fieldset style='text-align:left;'>
	<legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
	<p>Satu karyawan hanya dapat memiliki satu setiap jenis angsuran.
	Jika angsuran sudah ada dan diinput dengan tipe yang  sama maka angsuran lama akan ditimpah. Untuk menambah komponen angsuran
	gunakan menu <b>Setup - Pengaturan Penggajian HO - Komponen Gaji</b> dengan syarat, awal nama komponen harus '<b>Angsuran</b>'.
	</p>
	</fieldset>
	</td></tr>
	</table>";
} else {
	echo "</td><td valign=top>
	<fieldset style='text-align:left;height:95px'>
	<legend><b><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
	<p>Each employee can only has one type of loan.
	If the installments already exist and in the same type of input with the old installment will be overwritten.
	If there is a new component, please register on the setup menu <b>Payroll Component</b> with condition:  component name must be preceded by the word '<b>Angsuran</b>'.
	</p>
	</fieldset>
	</td></tr>
	</table>";
} */
//echo CLOSE_THEME();
echo"</fieldset><hr><fieldset><legend>".$_SESSION['lang']['list']."</legend><div id=laporan style='width:100%; height:340px;overflow:auto;'>";
echo"<img src='images/excel.jpg' class='resicon' title='Excel' onclick=viewexcel('excel')>";
echo"<table class=sortable  border=0 cellspacing=1>
	<thead>
		<tr class=rowheader>
		  <td align=center>No.</td>
				  <td align=center width:50px>" . $_SESSION['lang']['nik'] . "</td>
				  <td align=center width:50px>" . $_SESSION['lang']['periode'] . "</td>
				  <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
				  <td align=center>" . $_SESSION['lang']['lokasitugas'] . "</td>
				  <td align=center>" . $_SESSION['lang']['jennisangsuran'] . "</td>
				  <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
				  <td align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['nilaihutang'] . "<br>(Rp.)</td>
				  <td align=center>" . $_SESSION['lang']['bulanawal'] . "</td>
				  <td align=center>" . $_SESSION['lang']['sampai'] . "</td>
				  <td align=center>" . $_SESSION['lang']['jumlah'] . "<br>(" . $_SESSION['lang']['bulan'] . ")</td>
				  <td align=center>" . $_SESSION['lang']['potongan'] . " / " . $_SESSION['lang']['bulan'] . "<br>(Rp.)</td>				
				  <td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "1</td>
				  <td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "2</td>
				  <td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "3</td>
				  <td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "4</td>
				  <td hidden align=center>" . $_SESSION['lang']['premi'] . "<br>(" . $_SESSION['lang']['tahap'] . "5</td>
				  <td align=center>" . $_SESSION['lang']['status'] . "</td>
				  <td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
		</tr> 
		</thead>
		<tbody id=tbody>
			<script>loadData(0)</script>
		";
echo "</tbody>
<tfoot></tfoot>
</table>";
echo "</div>";
echo "</div>";
CLOSE_BOX();
echo close_body();
?>