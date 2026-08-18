<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/setup_gantiLokasiTugas.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>

<?
include('master_mainMenu.php');
$jabatan = array('IT','OFA','HCGA','ACT');  // Departement / Bagian
$jab = array('40','80','131','76','34'); // Jabatan => Kasie HRGA, IT Kebun, Kasie Administrasi
$lok = array('KANWIL');        // tipelokasitugas => KANWIL


## BERDASARKAN ORG DETAIL
$notif="";
$str="select distinct(a.kodeorganisasi) as kodeorganisasi, b.namaorganisasi, b.alokasi from ".$dbname.".user_orgdetail a left join ".$dbname.".organisasi b on a.kodeorganisasi=b.kodeorganisasi where length(b.kodeorganisasi)=4 and a.namauser='".$_SESSION['standard']['username']."' and b.kodeorganisasi !='".$_SESSION['empl']['lokasitugas']."' order by b.induk";
if(count(fetchdata($str))==''){	
	$notif="<span>Jika daftar unit tujuan kosong silahkan daftarkan terlebih dahulu melalui menu : Administrator - Menu Manager - Detail Akses</span>";
}

$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar->kodeorganisasi."'");
	$d=$induk[$bar->kodeorganisasi];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$opt.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	
	$opt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	$n=$d;
	if($d!=$n){			
		$opt.="</optgroup>";
	}
}


$str="select * from ".$dbname.".user where status = '1' and namauser!='".$_SESSION['standard']['username']."' order by namauser asc";
$res = fetchData($str);
$optuser="<option value=''>&nbsp;</option>";			
foreach($res as $bar){
	$optuser.="<option value='".$bar['karyawanid']."'>".$bar['namauser']."</option>";			
}

$str="select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
$res = fetchData($str);
if(count($res)>0){
	$style="";
}else{
	$style="style=display:none";
}


OPEN_BOX('','<span class=judul>'.getMenu('setup_pindahLokasiTugas').'</span><br>');
echo "<table></tr><td>";
echo "<fieldset style='float:left;'><legend>Pindahkan Lokasi Tugas untuk user yang bersangkutan</legend>
		<table border=0 cellpadding=1 cellspacing=1 style='width:500px;'>
		<tr>
			<td width=100px>You are ON </td>
			<td width=10px>:</td>
			<td><b>".$_SESSION['empl']['lokasitugas']."</b></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tujuan']."</td>
			<td>:</td>
			<td><select class='select2' id=tjbaru>".$opt."</select></td>
		</tr>
		<tr>
			<td colspan=3>&nbsp;</td>
		</tr>
		<tr>
			<td><td><td colspan=3>
			<button class=mybutton onclick=gantiLokasitugas('ybs')>".$_SESSION['lang']['save']."</button>
		</tr>
	  </table>
	  ".$notif."
	  </fieldset>";
echo "</td><td>";	  
echo "<div id=header ".$style.">";
echo "<fieldset style=float:left>
		<legend>Pindahkan Lokasi Tugas untuk Karyawan</legend>
		<table cellspacing=1 border=0 style='width:500px;'>
			<tr>
				<td width=100px>".$_SESSION['lang']['user']."</td>
				<td>:</td>
				<td><select class='select2' id=username onchange=getlokasiawal();>".$optuser."</select>
				</td>
			</tr> 
			<tr>
				<td>Lokasi Asal</td>
				<td>:</td>
				<td style=font-weight:bold; id=lokasiasal></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tujuan']."</td>
				<td>:</td>
				<td><select class='select2' id=tujuanbaru>".$opt."</select></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=tomboldetail class=mybutton onclick=gantiLokasitugas('kary')>" . $_SESSION['lang']['save'] . "</button>
				</td>
			</tr>
		</table>
	</fieldset>";
echo"</div>";

echo "</td></tr></table>";	  
CLOSE_BOX();



echo close_body();
?>