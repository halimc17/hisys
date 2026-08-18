<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
error_reporting(0);
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/kebun_taksasi.js?v=<?php echo time(); ?>'></script> 

<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?php
$str = "select * from ".$dbname.".bgt_regional_assignment
	where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%' ";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$regional = $bar->regional;
}

// Option kode organisasi
$optKodeorg = "<option value=''>".$_SESSION['lang']['all']."</option>";
$optDivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
$whrKodeorg = "left(blok,4) in (select distinct left(kodeorganisasi,4) from ".$dbname.".user_orgdetail where namauser='".$_SESSION['standard']['username']."') OR left(blok,4)='".$_SESSION['empl']['lokasitugas']."'";


$qKodeorg = selectQuery($dbname, 'kebun_taksasi', "left(blok,4) as kodeorg", $whrKodeorg." GROUP BY left(blok,4) ORDER BY blok ASC");

$resKodeorg = fetchData($qKodeorg);
$makeOptKodeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
foreach ($resKodeorg as $bar) {
	$optKodeorg .= "<option value='".$bar['kodeorg']."'>".$bar['kodeorg']." - ".$makeOptKodeorg[$bar['kodeorg']]."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('kebun_taksasi').'</span><br>');
echo "<div><table><tr>";
echo"<td style='min-width:100px' v-align='middle'><img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."' onclick='showAdd()'><br>".$_SESSION['lang']['new']."</td>";
echo"<td style='min-width:100px' v-align='middle'><img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."' onclick='loadData(0)'><br>".$_SESSION['lang']['list']."</td>";
echo"<td style='min-width:100px' v-align='middle'><fieldset><legend>".$_SESSION['lang']['find']."</legend>";
echo "
	<table>
		<tr>
			<td>".$_SESSION['lang']['kodeorg']."</td>
			<td>:</td>
			<td><select id='kodeorg' style='width:150px' onchange=\"getDivisi();\">".$optKodeorg."</select></td>
			
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td><select id='divisi' style='width:150px'>".$optDivisi."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tgldari']."</td>
			<td>:</td>
			<td> <input id=\"tgldari\" name=\"tgldari\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\"  style=\"width:150px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\" readonly></td>
				
			<td>".$_SESSION['lang']['tglsmp']."</td>
			<td>:</td>
			<td><input id=\"tglsmp\" name=\"tglsmp\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\"  style=\"width:150px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\" readonly></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>
				<button onclick=\"loadData(0)\" class=\"mybutton\" name=\"sFind\" id=\"sFind\">".$_SESSION['lang']['find']."</button>
				<button onclick=\"detailExcel('event');\" class=\"mybutton\" name=\"btnExcel\" id=\"btnExcel\">".$_SESSION['lang']['excel']."</button>
				<button onclick=\"cancelData()\" class='mybutton'>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
";
echo"</legend></fieldset></td>";
echo "</tr></table></div>";
CLOSE_BOX();
 $arr="##tanggal##afdeling##blok##seksi##proses##hasisa##haesok##jmlhpokok##persenbuahmatang##jjgmasak##jjgoutput##hkdigunakan##bjr##mandor##rotasi";
echo"<input type=hidden id=proses value=insert /><div id=formData style='display:none'>";
OPEN_BOX();
echo"<fieldset style='float:left'><legend><b>".$_SESSION['lang']['form']."</b></legend>";
echo"<table border=0 style='float:left;'>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['tanggal']."</td><td>:</td>";
echo"<td><input id=\"tanggal\" name=\"tanggal\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\" onchange='getSPH()' style=\"width:80px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\" readonly>
     </td></tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['kebun']."</td><td>:</td>";
echo"<td><select id='kebundt' style=\"width:170px\" onchange='getAfdeling(0,0,0)'><option value=''></option>";
$sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
$qorg->setFetchMode(PDO::FETCH_ASSOC);
while($rorg=$qorg->fetch()){
    echo"<option value='".$rorg['kodeorganisasi']."'>".$rorg['namaorganisasi']."</option>";
}
echo"</select></td></tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['afdeling']."</td><td>:</td>";

$sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
$qorg->setFetchMode(PDO::FETCH_ASSOC);
while($rorg=$qorg->fetch()){
    $div.="<option value='".$rorg['kodeorganisasi']."'>".$rorg['namaorganisasi']."</option>";
}

echo"<td><select onchange='getblok(0,0,0)' id='afdeling' style=\"width:170px\">";
echo"<option value=''>".$div."</option>";
echo"</select>
		</td></tr>";

		
echo"<tr>";
echo"<td>".$_SESSION['lang']['blok']."</td><td>:</td>";


$sorg="select distinct indukblok,namaindukblok from ".$dbname.".organisasi where tipe='BLOK' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
$qorg->setFetchMode(PDO::FETCH_ASSOC);
while($rorg=$qorg->fetch()){
    $blok.="<option value='".$rorg['indukblok']."'>".$rorg['namaindukblok']."</option>";
}

echo"<td><select id='blok'  class=select2 style=\"width:170px\" onchange='getSPH()'>";    
echo"<option value=''>".$blok."</option></select>
		
     </td></tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['luas']." | ".$_SESSION['lang']['pokok']." | ".$_SESSION['lang']['sph']."</td><td>:</td>";
echo"<td><input id=\"luas\" name=\"luas\" class=\"myinputtextnumber\" style=\"width:50px\" maxlength=45 type=\"text\" disabled>
		 <input id=\"pokok\" name=\"pokok\" class=\"myinputtextnumber\" style=\"width:50px\" maxlength=45 type=\"text\" disabled>
		 <input id=\"sph\" name=\"sph\" class=\"myinputtextnumber\" style=\"width:50px\" maxlength=45 type=\"text\" disabled>
         </td></tr>";
echo"<tr>";
echo"<tr>";
echo"<td><td><td><button id=\"addHead\" name=\"addHead\" class=\"mybutton\" onclick=\"saveData('kebun_slave_taksasi','".$arr."')\">".$_SESSION['lang']['save']."</button>
<button class=\"mybutton\" onclick=\"cancelIsi()\">".$_SESSION['lang']['cancel']."</button>


</td>";
echo"</tr>";
echo"</table>";
echo"<table border=0 style=float:left;>";
echo"<tr>";
echo"<td> Ancak Panen </td><td>:</td>";
echo"<td><input id=\"seksi\" name=\"seksi\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\"  style=\"width:70px\" maxlength=45 type=\"text\">
     </td></tr>";
echo"<tr hidden>";
echo"<td hidden>".$_SESSION['lang']['hasisa']." ".$_SESSION['lang']['hi']."</td><td  hidden>:</td>";
echo"<td hidden><input id=\"hasisa\" name=\"hasisa\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\" onchange='getPokok()' style=\"width:70px\" type=\"text\">
         </td></tr>";
echo"<tr >";
echo"<td >".$_SESSION['lang']['tahuntanam']."</td><td >:</td>";
echo"<td ><input id=\"tt\" name=\"tt\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\" disabled style=\"width:70px\" type=\"text\">
         </td></tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['luas']." Ha</td><td>:</td>";
echo"<td><input id=\"haesok\" name=\"haesok\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\" onchange='getPokok()' style=\"width:70px\" type=\"text\">
         </td></tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['jmlhpokok']."</td><td>:</td>";
echo"<td><input id=\"jmlhpokok\" name=\"jmlhpokok\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"  style=\"width:70px\" type=\"text\" disabled>
         </td></tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['persenbuahmatang']."</td><td>:</td>";
echo"<td><input id=\"persenbuahmatang\" name=\"persenbuahmatang\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\" onchange='getMasak()'  style=\"width:70px\" type=\"text\">
         </td></tr>";
echo"</table>";
#form kernel
echo"<table border=0 style=float:left;>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['jjgmasak']."</td><td>:</td>";
echo"<td><input id=\"jjgmasak\" name=\"jjgmasak\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\" disabled>
         </td>
	 <td>".$_SESSION['lang']['kg']." Masak</td><td>:</td>
	 <td><input id=\"kgmasak\" name=\"kgmasak\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\" disabled>
         </td>
		 
		 </tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['jjgoutput']."</td><td>:</td>";
echo"<td><input id=\"jjgoutput\" name=\"jjgoutput\" onkeyup=getjjgkg('kg',this.value) class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\" >
         </td>
	 <td>Kg Output</td><td>:</td>
	 <td><input id=\"kgoutput\" name=\"kgoutput\" onkeyup=getjjgkg('jjg',this.value) class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\">
         </td>	 
		 </tr>";
echo"<tr>";
echo"<td>HK Output</td><td>:</td>";
echo"<td><input id=\"hkdigunakan\" name=\"hkdigunakan\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\" disabled></td>";

echo"<td>".$_SESSION['lang']['bjr']."</td><td>:</td>";
echo"<td><input id=\"bjr\" name=\"bjr\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\" disabled></td>";
echo"</tr>";
echo"<tr>";

$optmandor="<option value=''></option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $add="";
}else{
    $add="and lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
}
$str="select * from ".$dbname.".datakaryawan where 1=1 ".$add."  and (tanggalkeluar='0000-00-00' or tanggalkeluar > NOW()) order by namakaryawan";
$res = fetchData($str); $jlhkeg=count($res);
foreach($res as $key => $val){
	$optmandor.="<option value=".$val['karyawanid']." ".$s.">".$val['nik']." - ".$val['namakaryawan']."</option>";	
}

echo"<td>".$_SESSION['lang']['mandor']."</td><td>:</td>";

echo"<td colspan=4><select class=select2 style=width:220px; id=mandor>".$optmandor."</select>

</td>";

echo"</tr>";
echo"<tr>";
    echo"<td>".$_SESSION['lang']['penggunaanhk']."</td><td>:</td>";
    echo"<td><input id=\"bisapanen\" name=\"bisapanen\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\" disabled></td>";
    echo"<td>".$_SESSION['lang']['rotasi']."</td><td>:</td>";
    echo"<td><input id=\"rotasi\" name=\"rotasi\" class=\"myinputtextnumber\" onkeypress=\"return angka_doang(event)\"   style=\"width:70px\" type=\"text\"></td>";
    echo"</tr>";
echo"</table>";
echo"</fieldset>";
CLOSE_BOX();
OPEN_BOX();

// echo"<fieldset style=float:left><legend>List Data</legend>";
	$tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:800px><thead><tr align=center>";
	$tab.= "<td>".$_SESSION['lang']['afdeling']."</td>";
	$tab.= "<td>".$_SESSION['lang']['mandor']."</td>";
	$tab.= "<td>".$_SESSION['lang']['tanggal']."</td>";
	$tab.= "<td>".$_SESSION['lang']['blok']."</td>";
	$tab.= "<td>Ancak Panen</td>";
	$tab.= "<td>".$_SESSION['lang']['ha']."</td>";
	$tab.= "<td>".$_SESSION['lang']['jmlhpokok']."</td>";
	$tab.= "<td>".$_SESSION['lang']['persenbuahmatang']."</td>";
	$tab.= "<td>".$_SESSION['lang']['jjgmasak']."</td>";
	$tab.= "<td>".$_SESSION['lang']['jjgoutput']."</td>";
	$tab.= "<td>KG Output</td>";
	$tab.= "<td>".$_SESSION['lang']['rotasi']."</td>";
	$tab.= "<td>HK</td>";
	$tab.= "<td colspan=2>".$_SESSION['lang']['action']."</td>";
	$tab.= "</tr></thead><tbody id=loaddatadetail></tbody></table>";

echo $tab;

CLOSE_BOX();
echo"</div>";
echo"<div id=dataList>";
# List
OPEN_BOX();
// echo"<fieldset style='clear:left'><legend><b>".$_SESSION['lang']['list']."</b></legend>";
echo"<div id=container class='table-scroll'><script>loadData(0);</script></div>";
// echo"</fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>