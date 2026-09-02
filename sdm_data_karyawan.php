<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/datakaryawan.js?v=<?php echo time(); ?>'></script>
<script language=javascript1.2 src='js/option.js?v=<?php echo time(); ?>'></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script>
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});

$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
	$(this).closest(".select2-container").siblings('select:enabled').select2('open');
});




</script>
<style>
.btn-group {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
}

.btn {
	border: none;
	color: white;
	padding: 8px 6px;
	font-size: 12px;
	margin-top: 6px;
	cursor: pointer;
}

.btn-info {
	background-color: #00c0ef;
}

.btn-danger {
	background-color: #dd4b39;
}

.btn-info:hover {
	background-color: #00a0d2;
}

.btn-danger:hover {
	background-color: #d43f3a;
} 
.error-highlight + .select2-container--default .select2-selection--single {
  border-color: red !important;
}


</style>

<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_data_karyawan').'</span>');


$optlokasitugas='';
$saveable='';
$str="select 1=1";

#####parameter aplikasi#############
// $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRJBTN' and kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $bar=$res->fetch();
// $hrjbtn=$bar['nilai'];


// $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRGOL' and kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $bar=$res->fetch();
// $hrgol=$bar['nilai'];



// $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRDEPT' and kodeorg='".$_SESSION['empl']['lokasitugas']."' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $bar=$res->fetch();
// $hrdept=$bar['nilai'];



// #jabatan yang tampil di kebun dan pabrik, tipe parameter nya global tanpa kunci kode org##
// $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='HRJBTNUNIT' ";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $bar=$res->fetch();
// $hrjbtnunit=$bar['nilai'];



#####parameter aplikasi#############

// if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING' || trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
//   $wherejbtn="";
//   $wheregol="";
//   $wheredept="";
// }else{
// 	if($hrjbtn==''){
// 		#exit("Warning : Parameter HRJBTN belum ada, silahkan hubungi admin.");
// 	}
// 	if($hrgol==''){
// 		#exit("Warning : Parameter HRGOL belum ada, silahkan hubungi admin.");
// 	}
// 	if($hrdept==''){
// 		#exit("Warning : Parameter HRDEPT belum ada, silahkan hubungi admin.");
// 	}
	
//   $wherejbtn="and kodejabatan in (".$hrjbtn.")";
//   $wheregol="and kodegolongan in (".$hrgol.")";
//   $wheredept="and kode in (".$hrdept.")";
// }

$opterf="<option value=''></option>";
$whereerf="statuspersetujuan='1' ";


$optlokasitugas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optsubbagian="<option value=''>UMUM</option>";
$optorganisasi="<option value=''></option>";
$optbagian="<option value=''></option>";
$optlevelkaryawan="<option value=''></option>";
// if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') and length(kodeorganisasi)=4 order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$optAll='';
    while($bar=$res->fetch()){
        $optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
        $whereerf.=" or notransaksi like '%".$bar->kodeorganisasi."%' ";
        $optAll.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
    }
	
	// $optsubbagian="<option value='0'></option>";

	$stdy="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('PT','BLOK','GUDANG','STENGINE')";
	$redy=$owlPDO->query($stdy) or die(print " Gagal: ".PDOException::getMessage());
	$redy->setFetchMode(PDO::FETCH_OBJ);
	while($bardy=$redy->fetch()){
		$optsubbagian.="<option value='".$bardy->kodeorganisasi."'>".$bardy->kodeorganisasi." - ".$bardy->namaorganisasi."</option>";
		$optAll.="<option value='".$bardy->kodeorganisasi."'>".$bardy->kodeorganisasi." - ".$bardy->namaorganisasi."</option>";	
	}	
// }else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
//     $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') and length(kodeorganisasi)=4 and (kodeorganisasi in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional != 'JAKARTA') or induk in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional != 'JAKARTA')) order by kodeorganisasi asc";
// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 	$res->setFetchMode(PDO::FETCH_OBJ);
// 	while($bar=$res->fetch()){
// 		$whereerf.=" or notransaksi like '%".$bar->kodeorganisasi."%' ";
// 		$optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
// 		@$optAll.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
// 	}
// 	$optsubbagian="<option value='0'></option>";

// 	$stdy="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('PT','BLOK','STENGINE')
// 		   and induk in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional != 'JAKARTA')
// 		   order by kodeorganisasi asc";
// 	$redy=$owlPDO->query($stdy) or die(print " Gagal: ".PDOException::getMessage());
// 	$redy->setFetchMode(PDO::FETCH_OBJ);
// 	while($bardy=$redy->fetch()){
// 		$optsubbagian.="<option value='".$bardy->kodeorganisasi."'>".$bardy->kodeorganisasi." - ".$bardy->namaorganisasi."</option>";
// 		$optAll.="<option value='".$bardy->kodeorganisasi."'>".$bardy->kodeorganisasi." - ".$bardy->namaorganisasi."</option>";
// 	}	
// }else if(trim($_SESSION['org']['induk']!='')){
// 	//user unit hanya dapat menempatkan pada unitnya dan anak unitnya

//     $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 
//         and kodeorganisasi  like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi";
//     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//     $res->setFetchMode(PDO::FETCH_OBJ);
//     while($bar=$res->fetch()){
//         $whereerf.=" or notransaksi like '%".$bar->kodeorganisasi."%' ";
//         $optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
//     }
// 	$optsubbagian="<option value='0'></option>";
// 	$optAll="";
// 	$stdy="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in('AFDELING','TRAKSI','GUDANG','WORKSHOP','BIBITAN','STATION','SIPIL','MAINTENANCE') and induk = '".$_SESSION['empl']['lokasitugas']."'";
// 	$redy=$owlPDO->query($stdy) or die(print " Gagal: ".PDOException::getMessage());
// 	$redy->setFetchMode(PDO::FETCH_OBJ);
// 	while($bardy=$redy->fetch()){
// 		$optsubbagian.="<option value='".$bardy->kodeorganisasi."'>".$bardy->kodeorganisasi." - ".$bardy->namaorganisasi."</option>";
// 		$optAll.="<option value='".$bardy->kodeorganisasi."'>".$bardy->kodeorganisasi." - ".$bardy->namaorganisasi."</option>";
// 	}	
// }else{
//   $saveable='disabled';
//   echo"<script>
//         alert('You are not authorized');
//        </script>";
// }



$optAll='';
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optAll.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}

	$optAll.="<option value='".$key."'>".$key." - ".$val."</option>";			
	
	$n=$d;
	if($d!=$n){			
		$optAll.="</optgroup>";
	}
}

$optsup=$optkppnpwp='';
$str1=$owlPDO->query("select namalokasi from ".$dbname.".sdm_5lokasibpjs where tipe='BPJS'");
$str1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$str1->fetch()){
  $optsup.="<option value='".$bar1->namalokasi."'>".$bar1->namalokasi."</option>";
}

$str1=$owlPDO->query("select namalokasi from ".$dbname.".sdm_5lokasibpjs where tipe='NPWP'");
$str1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$str1->fetch()){
  $optkppnpwp.="<option value='".$bar1->namalokasi."'>".$bar1->namalokasi."</option>";
}


$str="select kode,keterangan from ".$dbname.".sdm_5catuporsi order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optCatu="<option value=0>Tidak dapat catu</option>";
while($bar=$res->fetch()){
    $optCatu.="<option value='".$bar->kode."'>".$bar->kode."-".$bar->keterangan."</option>";
}

//Tipe karyawan
$opttipekaryawan='';
/* if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING' || trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){//jika user holding dapat memunculkan pilihan Permanen(staff))
	$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif=1 order by tipe";
}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KEBUN' || trim($_SESSION['empl']['tipelokasitugas'])=='PABRIK'){//pilihan staff dihilangkan, input data staff hanya dari pusat
	if($hrjbtnunit==''){
		exit("Warning : Parameter HRJBTNUNIT belum ada, silahkan hubungi admin.");
	}
	$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif=1 and id in (".$hrjbtnunit.") order by tipe";
}else{
	if($hrjbtnunit==''){
		exit("Warning : Parameter HRJBTNUNIT belum ada, silahkan hubungi admin.");
	}
} */
$opttipekaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".sdm_5tipekaryawan a where aktif=1 and id in (select id from ".$dbname.".sdm_5tipekaryawan_detail where unittipe='".$_SESSION['empl']['tipelokasitugas']."') order by tipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$opttipekaryawan.="<option value='".$bar->id."'>".$bar->tipe."</option>";	
}	
echo"<table>
		<tr valign=middle>
			<td align=center style='width:75px;cursor:pointer;' onclick=displayFormInput()>
				<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
			</td>
			
			<td align=center style='width:75px;cursor:pointer;' onclick=displayList()>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			
			<td align=center style='width:75px;cursor:pointer;' onclick=add_posting()>
				<img class=delliconBig src=images/archive.png title='".$_SESSION['lang']['posting']."'><br>".$_SESSION['lang']['posting']."
			</td>
			<td>
				<fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
				echo $_SESSION['lang']['nama']." : <input type=text id=txtsearch onkeyup=cariKaryawan(1); size=20 maxlength=30 class=myinputtext> ";
				echo $_SESSION['lang']['noktp']." : <input type=text id=noktpsch onkeyup=cariKaryawan(1); size=15 maxlength=30 class=myinputtext> ";
				echo $_SESSION['lang']['lokasitugas']." : <select id=schorg style='width:100px' onchange=cariKaryawan(1);><option value='' >".$_SESSION['lang']['all']."</option>".$optAll."</select> ";
				echo $_SESSION['lang']['tipekaryawan']." : <select id=schtipe  style='width:75px' onchange=cariKaryawan(1);><option value=''>".$_SESSION['lang']['all']."</option>".$opttipekaryawan."</select> ";
				echo $_SESSION['lang']['status']." : <select id=schstatus  style='width:75px' onchange=cariKaryawan(1);><option value=''>".$_SESSION['lang']['all']."</option><option value='0000-00-00'>".$_SESSION['lang']['aktif']."</option><option value='*'>".$_SESSION['lang']['tidakaktif']."</select> ";
				echo"<button class=mybutton onclick=cariKaryawan(1)>".$_SESSION['lang']['find']."</button>";
				echo"</fieldset>
			</td>
		</tr>
	</table> "; 
CLOSE_BOX();
OPEN_BOX('','');
echo"<div id='frminput' style='display:none;'>";
	$optagama='';
	$arragama=getEnum($dbname,'datakaryawan','agama');
	$optagama="<option value=''></option>";
	foreach($arragama as $kei=>$fal){
		$optagama.="<option value='".$kei."'>".$fal."</option>";
	}  

    $str="select * from ".$dbname.".sdm_5departemen a left join ".$dbname.".sdm_5departemen_detail b on a.kode=b.kode where (b.unittipe='".$_SESSION['empl']['tipelokasitugas']."' or b.unittipe='GLOBAL') and aktif=1  order by a.kode"; #exit("error".$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$optbagian.="<option value='".$bar->kode."'>".$bar->nama."</option>";	
	}

	## Levelkaryawan
	$str="select * from ".$dbname.".sdm_5levelkaryawan a left join ".$dbname.".sdm_5levelkaryawan_detail b on a.kode=b.kode where (b.unittipe='".$_SESSION['empl']['tipelokasitugas']."' or b.unittipe='GLOBAL') and aktif=1  order by a.kode"; #exit("error".$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$optlevelkaryawan.="<option value='".$bar->kode."'>".$bar->nama."</option>";	
	}

	$optsubdept='';
	$optjabatan='';
	$arrjabbatanx=array();
	$str="select * from ".$dbname.".sdm_5jabatan a left join ".$dbname.".sdm_5jabatan_detail b on a.kodejabatan=b.kodejabatan where (b.unittipe='".$_SESSION['empl']['tipelokasitugas']."' or b.unittipe='GLOBAL') and aktif=1 and namajabatan not like '%available' order by namajabatan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$optjabatan.="<option value='".$bar->kodejabatan."'>".$bar->namajabatan."</option>";
		$arrjabbatanx[$bar->kodejabatan]=$bar->namajabatan;
	}	


	$strerf=$owlPDO->query("select notransaksi,namajabatan from ".$dbname.".sdm_req_employee where ".$whereerf." ");
	$strerf->setFetchMode(PDO::FETCH_OBJ);
	while($barerf=$strerf->fetch()){
	  $opterf.="<option value='".$barerf->notransaksi."'>".$barerf->notransaksi."[".$arrjabbatanx[$barerf->namajabatan]."]"."</option>";
	}

    $optgolongan='';
    $str="select * from ".$dbname.".sdm_5golongan a left join ".$dbname.".sdm_5golongan_detail b on a.kodegolongan=b.kodegolongan where a.aktif=1 and (b.unittipe='".$_SESSION['empl']['tipelokasitugas']."' or b.unittipe='GLOBAL') order by a.kodegolongan";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $optgolongan.="<option value='".$bar->kodegolongan."'>".$bar->namagolongan."</option>";	
    }	

    $country   =readCountry("./config/country.lst");
	$optCountry='';
    for($x=0;$x<count($country);$x++){
       $optCountry.="<option value='".$country[$x][2]."' >".$country[$x][0]."</option>";
    }
     
	// Get provinsi list
	$country   =readCountry("./config/provinsi.lst");
	$optProvinsi='';
	$optProvinsi.="<option value='LOKAL' >Lokal</option>";
	for($x=0;$x<count($country);$x++){
		$optProvinsi.="<option value='".$country[$x][1]."' >".$country[$x][0]."</option>";
	}	 



	// Get status pajak
	$optstatuspajak='';
	$str="select * from ".$dbname.".sdm_5statuspajak order by nama asc";  
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$nostatuspajak = 0;
	while($bar=$res->fetch()){
		if($nostatuspajak == 0){
			$hasilstatuspajak = $bar->kode;
		}
		$optstatuspajak.="<option value='".$bar->inisial."'>".$bar->nama."</option>";    
		$nostatuspajak++;
	}
	//get Golongan darah from enum
	$optGoldar='';
	$arrenum=getEnum($dbname,'datakaryawan','golongandarah');
	foreach($arrenum as $key=>$val){
		$optGoldar.="<option value='".$key."'>".$val."</option>";
	} 	
	//kode organisasi harus PT
	//if(user is under holding)
	// $str="select 1=1";
	// if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
	// 	//user holding dapat memilih semua PT 
	// 	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
	// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_OBJ);
	// 	while($bar=$res->fetch()){
	// 		$optorganisasi.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
	// 	}
	// }else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
	// 	$str="select distinct induk from ".$dbname.".organisasi where kodeorganisasi in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment
	// 		where regional='".$_SESSION['empl']['regional']."') order by namaorganisasi";
	// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_OBJ);
	// 	while($bar=$res->fetch()){
	// 		$sNama="select distinct namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar->induk."'";
	// 		$resw=$owlPDO->query($sNama) or die(print " Gagal: ".PDOException::getMessage());
	// 		$resw->setFetchMode(PDO::FETCH_OBJ);
	// 		$rNama=$resw->fetch();
	// 		$optorganisasi.="<option value='".$bar->induk."'>".$rNama->namaorganisasi."</option>";	
	// 	}
	// }else if(trim($_SESSION['org']['induk'])!=''){
	// 	//hanya kode PT dari user ybs
	// 	$optorganisasi.="<option value='".trim($_SESSION['org']['kodeorganisasi'])."'>".$_SESSION['org']['namaorganisasi']."</option>";
	// }else{
	// 	$optorganisasi.="<option value='".trim($_SESSION['org']['kodeorganisasi'])."'>".$_SESSION['org']['namaorganisasi']."</option>";
	// }

	$arrUnit = getOrgDetail(3);
	foreach($arrUnit as $key=>$val){
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
		$d=$induk[$key];
		if($d!=$n){			
			$optorganisasi.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}

		$optorganisasi.="<option value='".$key."'>".$key." - ".$val."</option>";			
		
		$n=$d;
		if($d!=$n){			
			$optorganisasi.="</optgroup>";
		}
	}



	//===========get suku
	$optSuku = "";
	$optSuku = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select * from ".$dbname.".sdm_5suku order by namasuku asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$optSuku.="<option value='".$bar->idsuku."'>".$bar->namasuku."</option>";	
	}

	//===========get jeniskeamin enum
	//get Golongan darah from enum
	$optJK='';
	$arrenum=getEnum($dbname,'datakaryawan','jeniskelamin');
	$arrJenisKelamin = array('L' => 'Laki - Laki', 'P' => 'Perempuan');
	foreach($arrenum as $key=>$val){
		$optJK.="<option value='".$key."'>".$arrJenisKelamin[$val]."</option>";
	}

	//get option status karyawan
	$optstatkaryawan = "";
	$optstatkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$arrstatkaryawan=getEnum($dbname,'datakaryawan','statuskaryawan');
	foreach ($arrstatkaryawan as $key => $value) {
		$caption_tambahan = "";
		if($value == "Aktif" or $value == "Keluar"){
			$caption_tambahan = "(Khusus KHL)";
			$value = $_SESSION['lang'][strtolower($value)];
		}
		$optstatkaryawan.="<option value='".$value."'>".$value." ".$caption_tambahan."</option>";
	} 

	// get sistem penggajian enum
	$optsisgaji='';
	$arrsgaj=getEnum($dbname,'datakaryawan','sistemgaji');
	foreach($arrsgaj as $kei=>$fal){
		if($_SESSION['language']=='EN' && $fal=='Harian')
			$fal='Daily';
		if($_SESSION['language']=='EN' && $fal=='Bulanan')
			$fal='Monthly';                      
		$optsisgaji.="<option value='".$kei."'>".$fal."</option>";
	}  
	//Get status perkawinan enum
	$optstkawin = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$arrsstk=getEnum($dbname,'datakaryawan','statusperkawinan');
	foreach($arrsstk as $kei=>$fal){
		if($_SESSION['language']=='EN' && $fal=='Menikah')
			$fal='Married';
		if($_SESSION['language']=='EN' && $fal=='Janda')
		   $fal='Widow';       
		if($_SESSION['language']=='EN' && $fal=='Duda')
		   $fal='Widower';      
		if($_SESSION['language']=='EN' && $fal=='Lajang')
		   $fal='Single';              
		$optstkawin.="<option value='".$kei."'>".$fal."</option>";
	} 
	//Get level pendidikan
	// $optlvlpendidikan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select * from ".$dbname.".sdm_5pendidikan order by levelpendidikan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$optlvlpendidikan.="<option value='".$bar->levelpendidikan."'>".$bar->pendidikan."</option>";	
	}

	$optNmBank='';
	$str="select * from ".$dbname.".keu_5daftarbank where status = '1'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$optNmBank="<option value=''></option>";	
	while($bar=$res->fetch()){
		$optNmBank.="<option value='".$bar->kodebank."'>".$bar->namabank."</option>";	
	}	
?>
<style>
	.photoboth{
		background-repeat:no-repeat;
		width:150px;height:175px;
		background-position:center;
		background-size:70%;
		display:block;
		box-shadow:0px 0px 5px rgba(0,0,0,0.6);
		cursor:pointer;
	}
	.photobox{
		border:none;margin-left:5px;margin-right:15px;
	}
	.caption_uploadimage{
		border:solid 0.5px #ffbf00;
		background-color:#f1ff87;
		margin-top:5px;
		padding:2px 5px;
	}

</style>
<?php

$optPerakhirgaji="<option value=''></option>";
$str="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optPerakhirgaji.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

// $optPOH="<optgroup label=''>";
$optPOH.="<option value=''>Pilih Data</option>";
// $optPOH.="</optgroup>";

$str = "select * from ".$dbname.".provinsi where 1=1 order by provinsi asc";
$res = fetchdata($str);
foreach($res as $bar){
	$optPOH.="<option value=".$bar['id'].">".$bar['provinsi']."</option>";
}

$widthfieldset = '100%';
$frm[0]="<fieldset style='width:".$widthfieldset."px;text-align:center;'><legend><b>".$_SESSION['lang']['entryForm']."</b></legend>
		<table border=0 cellspacing=1 style=text-align:left>
			<tr hidden>
				<td>No Pengajuan Karyawan</td>
				<td><select id=noerf   >".$opterf."</select>
					<img id='noerf' onclick=z.elSearch('noerf',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
				<td colspan='7'></td>
			</tr>
		<tr>
			<td rowspan='9' width='100' valign='top'>
				<div class='photobox'>
					<div id='photoboth' class='photoboth' onclick=\"chooseFile('fupload');\" style=\"background-image:url('images/user-alerts.svg');\">
						<img src='' id=displayphoto >
						<div style='height:0px;overflow:hidden'>
							<input id='fupload' type='file' name='fupload' accept='image/jpeg' oninput='readURL(this);'>
						</div>
					</div>
					<div class='caption_uploadimage'>
						<img src='images/box/icon-warning.gif' width='10'> Upload Photo </br> (Max 2 MB) </br> Klik foto untuk mengganti
					</div>
					<div class='btn-group'>
						<button hidden class='btn btn-info' id='savePhoto' onclick=\"savePhoto();\"><i class='fa fa-upload'></i> Save Photo</button>
						<button hidden class='btn btn-danger' id='deletePhoto' onclick=\"deletePhoto()\"><i class='fa fa-trash'></i> Delete Photo</button>
					</div>
				</div>
			</td>

			<td>".$_SESSION['lang']['nik']."</td>
			<td>:</td>
			<td><input type=text class=myinputtext id=nik style=width:175px; maxlength=10 onkeypress=\"return tanpa_kutip(event);\" disabled></td>
			
			<td>".$_SESSION['lang']['pt']."</td><td>:</td>
			<td><select  id=kodeorganisasi onchange=getEstateX(this.value,'lokasitugas','pilihdata') style=border-color:red;width:180px; placeholder='Wajib Terisi.'>".$optorganisasi."</select></td>
			
			<td>".$_SESSION['lang']['tanggalmasuk']."</td><td>:</td>
			<td><input type=text style=border-color:red;width:175px placeholder='Wajib Terisi.' class=myinputtext id=tanggalmasuk  maxlength=10  onmousemove=setCalendar(this) readonly></td> 
			
			<td>".$_SESSION['lang']['tanggalpengangkatan']." Non Staff Organik</td><td>:</td>
			<td><input type=text class=myinputtext id=tanggalpengangkatannonstaff style=width:175px; maxlength=10  onmousemove=setCalendar(this) readonly></td> 
			
		</tr>
		<tr>	
			<td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
			<td><input type=text class=myinputtext style=border-color:red;width:175px; placeholder='Wajib Terisi.' id=namakaryawan  maxlength=40 onkeypress=\"return tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\"></td>
			
			<td>".$_SESSION['lang']['lokasitugas']."</td><td>:</td>
			<td><select id=lokasitugas class='select2 error-highlight' style=border-color:red;width:180px; placeholder='Wajib Terisi.' onchange=getDivisiX2(this.value,'subbagian','pilihdata')>".$optlokasitugas."</select></td>
			
			<td>".$_SESSION['lang']['tanggalpengangkatan']." Pertama</td><td>:</td>
			<td><input type=text class=myinputtext id=tanggalpengangkatan style=width:175px; maxlength=10  onmousemove=setCalendar(this) readonly></td> 
		</tr>
		<tr>		
			<td>".$_SESSION['lang']['tempatlahir']."</td><td>:</td>
			<td><input type=text class=myinputtext style=border-color:red;width:175px; placeholder='Wajib Terisi.' id=tempatlahir  maxlength=30 onkeypress=\"return tanpa_kutip(event);\"></td> 

			<td >".$_SESSION['lang']['divisi']."</td><td>:</td>
			<td ><select id=subbagian class='select2 error-highlight' style='border-color:red;width:180px;'>".$optsubbagian."</select></td>
			
			<td>".$_SESSION['lang']['tanggal']." Berhenti</td><td>:</td>
			<td><input type=text class=myinputtext id=tanggalkeluar style=width:175px; maxlength=10 onmousemove=setCalendar(this) onchange=setGajiTerakhir(this.value)></td> 
		</tr>
		<tr>	
			<td>".$_SESSION['lang']['tanggallahir']."</td><td>:</td>
			<td><input type=text class=myinputtext style=border-color:red;width:175px; placeholder='Wajib Terisi.' id=tanggallahir  onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" readonly></td>
			
			<td>".$_SESSION['lang']['department']."</td><td>:</td>
			<td><select class='select2' style=width:180px id=bagian >".$optbagian."</select></td> 
			
			<td>Periode Gaji Terakhir</td><td>:</td>
			<td><input type=text class=myinputtext id=periodeakhirgaji style='width:175px' readonly></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jeniskelamin']."</td><td>:</td>
			<td><select style=width:180px id=jeniskelamin  >".$optJK."</select></td>
			
			<td hidden>Sub ".$_SESSION['lang']['department']."</td><td hidden>:</td>
			<td hidden><select class='select2' style=width:180px id=subdept >".$optbagian."</select></td> 

			<td >Level ".$_SESSION['lang']['karyawan']."</td><td >:</td>
			<td ><select class='select2 error-highlight' style=width:180px id=levelkaryawan >".$optlevelkaryawan."</select></td> 
			
			<td>".$_SESSION['lang']['statuspajak']."</td><td>:</td>
			<td>
				<input type='hidden' id='vstatuspajak' value='".$hasilstatuspajak."'>
				<select id=statuspajak style=width:180px onchange='getstatuspajak()'>".$optstatuspajak."</select>
			</td>
		</tr>
		<tr>            
			<td>".$_SESSION['lang']['warganegara']."</td><td>:</td>
			<td><select class='select2' style=width:180px id=warganegara >".$optCountry."</select></td> 
			
			<td>".$_SESSION['lang']['kodejabatan']."</td><td>:</td>
			<td><select class='select2' style=\"width:180px;\" id=kodejabatan>" . $optjabatan . "</select>
			</td>
			
			<td>".$_SESSION['lang']['sistemgaji']."</td><td>:</td>
			<td><select id=sistemgaji style=width:180px>".$optsisgaji."</select></td>
		</tr>
		<tr> 
			<td>".$_SESSION['lang']['noktp']."</td><td>:</td>
			<td><input style=border-color:red;width:150px; placeholder='Wajib Terisi.' type=text class=myinputtext id=noktp maxlength=16 onkeypress=\"return tanpa_kutip(event);\"><fieldset style=float:right;height:10px><img src=images/upload-2-xxl.png class=zImgBtn title=Upload onclick=showupload('event','8')></fieldset>
			</td>
			
			<td>".$_SESSION['lang']['tipekaryawan']."</td><td>:</td>
			<td><select class='select2 error-highlight' onchange=getdetailtipekary(); id=tipekaryawan style='width:180px;' placeholder='Wajib Terisi.'>".$opttipekaryawan."</select></td> 
			
			<td>".$_SESSION['lang']['alokasibiaya']."</td><td>:</td>
			<td><select disabled id=alokasi style=width:180px> 
					<option value=0>Unit</option>
					<option value=1>Umum</option>
				</select>
			</td>
			
		</tr>
		<tr> 	
			<td>".$_SESSION['lang']['passport']."</td><td>:</td>
			<td><input type=text class=myinputtext style=border-color:yellow;width:150px; placeholder='Wajib Terisi untuk NS dan Staff.' id=nopassport maxlength=16 onkeypress=\"return tanpa_kutip(event);\"><fieldset style=float:right;height:10px><img src=images/upload-2-xxl.png class=zImgBtn title=Upload onclick=showupload('event','11')></fieldset>
			</td>
			
			<td>".$_SESSION['lang']['levelname']."</td><td>:</td>
			<td><select class='select2' style=width:180px id=kodegolongan >".$optgolongan."</select></td> 
			
			<td>".$_SESSION['lang']['lokasipenerimaan']."</td><td>:</td>
			<td><select class='select2' style=width:180px id=lokasipenerimaan >".$optPOH."</select></td>
		</tr>
		<tr style=vertical-align:top> 			
			<td style=vertical-align:top>Alamat KTP</td><td style=vertical-align:top>:</td>
			<td><textarea id=alamataktif readonly onclick=getpopupalamat(); style=border-color:red;width:160px;cursor:pointer; placeholder='Wajib Terisi.' cols=16 rows=2></textarea>
			</td>
			
			
			<td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['karyawan']."</td><td>:</td>
			<td><select  style=border-color:red;width:180px; onchange=gettanggalangkat(); id=statuskaryawan>".$optstatkaryawan."</select></td>

			<td >".$_SESSION['lang']['pembagiancatu']."</td><td>:</td>
			<td ><select class='select2' style=width:180px id=catu >".$optCatu."</select></td>

		</tr>
	</table>
	</fieldset>
		<div style=clear:both></div>
	";
$frm[0].="<fieldset style=text-align:center><legend>Detail Karyawan</legend>
	<table border=0 style=text-align:left>
		<tr>
			<td rowspan='15' width='150' valign='top'>
		</tr>
		<tr>
			<td colspan=3 style='color:gray'><b><i>DETAIL KARYAWAN</i></b></td>
			<td colspan=3 style='color:gray;padding-left:30px'></td>
			<td colspan=3 style='color:gray;padding-left:30px'><b><i>REKENING</i></b></td>
		</tr>
		<tr>
			<td colspan=6 style='color:gray'><hr></td>
			<td colspan=3 style='color:gray;padding-left:30px'><hr></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['province']."</td><td>:</td>
			<td><input class=myinputtext id=namaprovinsi style=width:175px; onclick=getpopupalamat(); readonly value=''>
				<input class=myinputtext id=provinsi style=width:180px;display:none; onclick=getpopupalamat(); readonly value=''>
				</td> 

			<td>".$_SESSION['lang']['statusperkawinan']."</td><td>:</td>
			<td><select style=\"width:180px;\" id=statusperkawinan >".$optstkawin."</select></td>
			
			<td>".$_SESSION['lang']['namabank']."</td><td>:</td>
			<td><select id=namabank style=border-color:red;\"width:180px;\">".$optNmBank."</select></td>   
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['kabupaten']."</td><td>:</td>
			<td><input class=myinputtext id=namakabupaten style=width:175px; onclick=getpopupalamat(); readonly value=''>
				<input class=myinputtext id=kabupaten style=width:180px;display:none; onclick=getpopupalamat(); readonly value=''>
			</td> 
			
			<td>".$_SESSION['lang']['tanggalmenikah']."</td><td>:</td>
			<td><input type=text class=myinputtext id=tanggalmenikah style=width:175px; maxlength=10 onkeypress=\"return false;\" onmousemove=setCalendar(this) readonly></td>
			
			
			<td>".$_SESSION['lang']['norekeningbank']."</td><td>:</td>
			<td><input type=text class=myinputtext id=norekeningbank placeholder='Untuk STAFF dan NS Wajib.' style=border-color:red;width:175px; maxlength=30 onkeypress=\"return angka_doang(event);\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kecamatan']."</td><td>:</td>
			<td><input class=myinputtext id=namakecamatan style=width:175px; onclick=getpopupalamat(); readonly value=''>
				<input class=myinputtext id=kecamatan style=width:180px;display:none; onclick=getpopupalamat(); readonly value=''>
			</td> 
			
			<td>".$_SESSION['lang']['jumlahanak']."</td><td>:</td>
			<td><input type=text class=myinputtext id=jumlahanak style=width:175px; maxlength=2 onkeypress=\"return angka_doang(event);\"></td> 
			
			<td>A/N Rekening Bank</td><td>:</td>
			<td><input type=text class=myinputtext id=anrekening placeholder='Untuk STAFF dan NS Wajib.'  style=border-color:red;width:175px; maxlength=45 ></td> 
		</tr>
		<tr>
			<td>".$_SESSION['lang']['desa']." / ".$_SESSION['lang']['kelurahan']."</td><td>:</td>
			<td><input class=myinputtext id=namadesa style=width:175px; onclick=getpopupalamat(); readonly value=''>
				<input class=myinputtext id=desa style=width:180px;display:none; readonly value=''>
			</td> 
			
			<td>".$_SESSION['lang']['levelpendidikan']."</td><td>:</td>
			<td><select id=levelpendidikan style=\"width:180px;\">".$optlvlpendidikan."</select></td> 
			
			
			<td colspan=3 style='color:gray;padding-left:30px'><b><i>BPJS</i></b></td>
		</tr>
		<tr>
            <td>".$_SESSION['lang']['kodepos']."</td><td>:</td>
			<td><input type=text readonly class=myinputtext onclick=getpopupalamat(); id=kodepos style=width:175px; maxlength=5 onkeypress=\"return angka_doang(event);\"></td>        
			
			<td>".$_SESSION['lang']['nomor']." SIM / SIO</td><td>:</td>
			<td><input type=text class=myinputtext id=sim style=width:175px; maxlength=45  onkeypress=\"return tanpa_kutip(event);\"></td>
			
			<td colspan=3 style='color:gray;padding-left:30px'><hr></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['telp']."</td><td>:</td>
			<td><input type=text class=myinputtext id=noteleponrumah style=width:175px; maxlength=15 onkeypress=\"return angka_doang(event);\"></td> 
			
			
			<td>".$_SESSION['lang']['golongandarah']."</td><td>:</td>
			<td><select id=golongandarah style=\"width:180px;\">".$optGoldar."</select></td>
			
			<td>Tanggal Daftar</td><td>:</td>
			<td><input type=text class=myinputtext placeholder='Untuk KHL.' id=bulandaftarbpjs style=width:175px; onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" readonly></td>
		</tr>
		 <tr>
			<td>".$_SESSION['lang']['nohp']." (1)</td><td>:</td>
			<td><input type=text class=myinputtext id=nohp style=width:175px; maxlength=15 onkeypress=\"return angka_doang(event);\"></td>
			
			<td>".$_SESSION['lang']['npwp']."</td><td>:</td>
			<td><input type=text id=npwp style=\"width:150px;\" maxlength=30 class=myinputtext onkeypress=\"MaskForm(event,'99.999.999.9-999.999')\" placeholder='NPWP Nummber'><fieldset style=float:right;height:10px><img src=images/upload-2-xxl.png class=zImgBtn title=Upload onclick=showupload('event','9')></fieldset></td>
			
			<td>".$_SESSION['lang']['bpjs']." Ketenaga Kerjaan</td><td>:</td>
			<td><input type=text class=myinputtext id=jms style=\"width:150px;\" maxlength=30 onkeypress=\"return tanpa_kutip(event);\"><fieldset style=float:right;height:10px><img src=images/upload-2-xxl.png class=zImgBtn title=Upload onclick=showupload('event','14')></fieldset></td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nohp']." (2)</td><td>:</td>
			<td><input type=text class=myinputtext id=nohp2 style=width:175px; maxlength=15 onkeypress=\"return angka_doang(event);\"></td>

			<td>KPP Perusahaan</td><td>:</td>
			<td><select id=kppnpwp style=\"width:180px;\">".$optkppnpwp."</select></td>  
			
			<td>".$_SESSION['lang']['bpjs']." ".$_SESSION['lang']['kesehatan']."</td><td>:</td>
			<td><input type=text id=bpjs style=\"width:150px;\" maxlength=30 class=myinputtext onkeypress=\"return angka_doang(event);\"><fieldset style=float:right;height:10px><img src=images/upload-2-xxl.png class=zImgBtn title=Upload onclick=showupload('event','15')></fieldset>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['notelepondarurat']."</td><td>:</td>
			<td><input type=text class=myinputtext id=notelepondarurat style=width:175px; maxlength=15  onkeypress=\"return angka_doang(event);\"></td>
			
			<td>".$_SESSION['lang']['email']." Pribadi</td><td>:</td>
			<td><input type=text class=myinputtext id=email   style=width:175px; maxlength=45  onkeypress=\"return tanpa_kutip(event);\"></td>
			
			<td>".$_SESSION['lang']['bpjs']." Pensiun</td><td>:</td>
			<td><input type=text id=pensiun style=width:175px; maxlength=30 class=myinputtext onkeypress=\"return tanpa_kutip(event);\"></td>
		</tr>
		<tr>
			
			<td>".$_SESSION['lang']['agama']."</td><td>:</td>
			<td><select style=width:180px id=agama >".$optagama."</select></td>
			
			<td>".$_SESSION['lang']['email']." Kantor</td><td>:</td>
			<td><input type=text class=myinputtext id=emailkantor  style=width:175px; maxlength=45  onkeypress=\"return tanpa_kutip(event);\"></td>

			<td>Tambahan Klg (BPJS Kes)</td><td>:</td>
			<td><input type=text class=myinputtext id=jumlahtanggungan style=width:175px; maxlength=2  onkeypress=\"return angka_doang(event);\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['suku']."</td><td>:</td>
			<td><select class=select2 style=\"width:180px;\" id=suku>" . $optSuku . "</select>
			</td>			
		</tr>
		
		<tr style=display:none>
			<td >Sub BPJS</td><td><select id=supbpjs >".$optsup."</select></td>
			<td style='display:none;'>".$_SESSION['lang']['lokasi']." ".$_SESSION['lang']['cuti']." </td>
			<td style='display:none;'><input type=text class=myinputtext id=kota size=27 maxlength=30 onkeypress=\"return tanpa_kutip(event);\" value='-'></td>					
		</tr>
		<tr style='display:none'>
				<td>".$_SESSION['lang']['statusakad']."</td><td><select id=statusakad >
				<option value='1'>akad</option>
				<option value='2' selected >non-akad</option>
				</select></td> 
		 </tr>
		 <tr style='display:none'>
			<td style='display:none'>".$_SESSION['lang']['premi']."</td>
			<td style='display:none'><input type=checkbox id=dptPremi />(* Click Jika Tidak Dapat</td> 
			<td>&nbsp;</td><td>&nbsp;</td>
			<td>&nbsp;</td><td>&nbsp;</td>
			<input type=hidden id=nourut value=''>  
			<input type=hidden id=karyawanid value=''>	
			<input type=hidden id=method value='insert'>
		 </tr>	 
		<tr><td></td>
			<td colspan='20' align=center>
				<button ".$saveable." class=mybutton onclick=simpanKaryawan() type='submit'>".$_SESSION['lang']['save']."</button>
				<button ".$saveable." class=mybutton onclick=cancelDataKaryawan()>".$_SESSION['lang']['cancel']."</button> 
			</td>
	</table>
	</fieldset>
	";

       

//Tab experiences=================================
$optbln="<option value=''>".$_SESSION['lang']['bulan']."</option>";
for($x=1;$x<13;$x++){
	if($x<10)
	   $bln="0".$x;
	else
	   $bln=$x;

	$optbln.="<option value='".$bln."'>".$bln."</option>";
}
$optthn="<option value=''>".$_SESSION['lang']['tahun']."</option>";
for($x=0;$x<60;$x++){
	$thn=date('Y')-$x;
	$optthn.="<option value='".$thn."'>".$thn."</option>";
}

$frm[1]="<fieldset style='width:".$widthfieldset."px;'><legend>".$_SESSION['lang']['pengalamankerja']."</legend>
         <table border=0 cellspacing=1>
                 <tr>
                    <td>".$_SESSION['lang']['orgname']."</td><td><input type=text class=myinputtext id=namaperusahaan style=width:200px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"><img src=images/obl.png title='Obligatory'></td>
                    <td>".$_SESSION['lang']['bidangusaha']."</td><td><input type=text class=myinputtext id=bidangusaha  style=width:200px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"></td>
                 </tr>
                 <tr>
                    <td>".$_SESSION['lang']['bulanmasuk']."</td><td><select id=blnmasuk style='width:115px;'>".$optbln."</select>-<select id=thnmasuk style='width:85px;'>".$optthn."</select></td>
                    <td>".$_SESSION['lang']['bulankeluar']."</td><td><select id=blnkeluar style='width:115px;'>".$optbln."</select>-<select id=thnkeluar style='width:85px;'>".$optthn."</select></td>
                 </tr>
                 <tr>
					<td>".$_SESSION['lang']['bagian']."</td><td><input type=text class=myinputtext id=pengalamanbagian  style=width:200px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"><img src=images/obl.png title='Obligatory'></td>	 
					<td>".$_SESSION['lang']['jabatanterakhir']."</td><td><input type=text class=myinputtext id=pengalamanjabatan  style=width:200px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"></td>
                 </tr>
				 <tr >
                    <td>".$_SESSION['lang']['alasanberhenti']."</td><td ><input type=text class=myinputtext id=alasanberhenti  style=width:200px maxlength=100 onkeypress=\"return tanpa_kutip(event);\"></td>
					
                    <td >Gaji Terakhir</td><td ><input type=text class=myinputtext id=gajipokok  style=width:200px maxlength=100 onkeypress=\"return angka_doang(event);\"></td>	 
                 </tr>	
                 <tr>
					<td>".$_SESSION['lang']['alamat']."</td><td colspan=3><input type=text class=myinputtext id=pengalamanalamat  style=width:530px maxlength=100 onkeypress=\"return tanpa_kutip(event);\"></td>	 
                 </tr>
				 
				  <tr style='display:none;'>
                    <td>".$_SESSION['lang']['tunjangan']."</td><td colspan=3><input type=text class=myinputtext id=tunjangan size=86 maxlength=250 onkeypress=\"return angka_doang(event);\"></td>	 
                 </tr>
				 <tr style='display:none;'>
                    <td>".$_SESSION['lang']['lokasicuti']."</td><td colspan=3><input type=text class=myinputtext id=lokasicuti size=86 maxlength=100 onkeypress=\"return tanpa_kutip(event);\"></td>	 
                 </tr>
                <tr><td><td>
                 <button id=btncv disabled class=mybutton onclick=simpanPengalaman()>".$_SESSION['lang']['save']."</button>
                 <button id=clear class=mybutton onclick=clearpengalaman()>".$_SESSION['lang']['cancel']."</button>
                 <input type=hidden value='insert' id=methodcv>
                 <input type=hidden value='' id=nomor>
                 </table>
                 </fieldset>
                 <fieldset><legend>".$_SESSION['lang']['list']."</legend>
				
                <div style='width:100%;height:250px;overflow:auto;'>
                <table class=sortable cellpadding=5 border=0 cellspacing=1 width=100%>
                        <thead>
                        <tr class=rowheader>
                          <th align=center>No.</th>
                          <th align=center>".$_SESSION['lang']['orgname']."</th>
                          <th align=center>".$_SESSION['lang']['bidangusaha']."</th>
                          <th align=center>".$_SESSION['lang']['bulanmasuk']."</th>
                          <th align=center>".$_SESSION['lang']['bulankeluar']."</th>
                          <th align=center>".$_SESSION['lang']['jabatanterakhir']."</th>
                          <th align=center>".$_SESSION['lang']['bagian']."</th>
                          <th align=center>".$_SESSION['lang']['masakerja']."</th>
                          <th align=center>".$_SESSION['lang']['alamat']."</th>	
                          <th align=center>Gaji terakhir</th>	
                          <th align=center>Alasan Berhenti</th>	
                          <th align=center colspan='2'>Aksi</th>
                        </tr>
                        </thead>
                        <tbody id=container>
                        </tbody>
                        <tfoot>
                        </tfoot>
                </table>
                </div>
                </fieldset>";
//tab Education History=========================================
//get Pendidikan
$str="select kelompok,levelpendidikan from ".$dbname.".sdm_5pendidikan order by levelpendidikan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optpendidikan="";
$optpendidikan.="<option value=''></option>";
while($bar=$res->fetch()){
	$optpendidikan.="<option value='".$bar->levelpendidikan."'>".$bar->kelompok."</option>";
} 
$frm[2]="<fieldset style='width:".$widthfieldset."px;'><legend>".$_SESSION['lang']['educationentry']."</legend>
         <table border=0 cellspacing=1>
                 <tr>
                    <td>".$_SESSION['lang']['edulevel']."</td><td><select id=levelpendidikan2 style='width:190px;'>".$optpendidikan."</select></td>
                    <td>".$_SESSION['lang']['jurusan']."</td><td><input type=text class=myinputtext id=spesialisasi style=width:185px maxlength=30 onkeypress=\"return tanpa_kutip(event);\"></td>
                 </tr>
                 <tr>
                    <td>".$_SESSION['lang']['gelar']."</td><td><input type=text class=myinputtext id=gelar style=width:185px maxlength=20 onkeypress=\"return tanpa_kutip(event);\"></td>
                    <td>".$_SESSION['lang']['tahunlulus']."</td><td><select id=tahunlulus style='width:190px;'>".$optthn."</select></td>
                 </tr>
                 <tr>
                        <td>".$_SESSION['lang']['namasekolah']."</td><td><input type=text class=myinputtext id=namasekolah style=width:185px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"><img src=images/obl.png title='Obligatory'></td>
                        <td>".$_SESSION['lang']['nilai']."</td><td><input type=text class=myinputtextnumber id=nilai style=width:185px maxlength=4 onkeypress=\"return angka_doang(event);\"></td>	 
                 </tr>
                 <tr>
                        <td>".$_SESSION['lang']['kota']."</td><td><input type=text class=myinputtext id=pendidikankota style=width:185px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"></td>
                        <td>".$_SESSION['lang']['keterangan']."</td><td><input type=text class=myinputtextnumber id=pendidikanketerangan style=width:185px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"></td>	 
                 </tr>	 
                <tr><td><td>
                 <button id=btnpendidikan disabled class=mybutton onclick=simpanPendidikan()>".$_SESSION['lang']['save']."</button>
                 <button id=clearpddkn class=mybutton onclick=clearpendidikan()>".$_SESSION['lang']['cancel']."</button>
                 <input type=hidden value='insert' id=methodpddkn>
                 <input type=hidden value='' id=kode>
				 </table>
                 </fieldset> <fieldset><legend>".$_SESSION['lang']['list']."</legend>
                <div style='width:100%;height:250px;overflow:auto;'>
                <table class=sortable border=0 cellpadding=5 cellspacing=1 width=100%>
                        <thead>
                        <tr class=rowheader>
                          <th align=center>No.</th>
                          <th align=center>".$_SESSION['lang']['edulevel']."</th>			  
                          <th align=center>".$_SESSION['lang']['namasekolah']."</th>
                          <th align=center>".$_SESSION['lang']['kota']."</th>			  
                          <th align=center>".$_SESSION['lang']['jurusan']."</th>			  
                          <th align=center>".$_SESSION['lang']['tahunlulus']."</th>
                          <th align=center>".$_SESSION['lang']['gelar']."</th>
                          <th align=center>".$_SESSION['lang']['nilai']."</th>
                          <th align=center>".$_SESSION['lang']['keterangan']."</th>	
                          <th align=center colspan='2'>Aksi</th>
                        </tr>
                        </thead>
                        <tbody id=containerpendidikan>
                        </tbody>
                        <tfoot>
                        </tfoot>
                </table>
                </div>
                </fieldset>";
 //===Tab Courses & Training====================
$optJnsTraining='';
$qJnsTraining = selectQuery($dbname,'sdm_5jenistraining','kodetraining,jenistraining,kelompok',"status='1'")."order by jenistraining";
$rJnsTraining = fetchData($qJnsTraining);
foreach($rJnsTraining as $val){  
	$d=$val['kelompok'];
	if($d!=$n){			
		$optJnsTraining.="<optgroup label='".$d."'>";
	}
	
    $optJnsTraining.="<option value='".$val['kodetraining']."'>".$val['jenistraining']."</option>";
	
	$n=$d;
	if($d!=$n){
		$optJnsTraining.="</optgroup>";
	}
} 	
$frm[3]="<fieldset style='width:".$widthfieldset."px;'><legend>".$_SESSION['lang']['traininginternal']."</legend>
         <table border=0 cellspacing=1>
                 <tr>
                    <td>".$_SESSION['lang']['jeniskursus']."</td><td><select id=jenistraining style='width:205px;'>".$optJnsTraining."</select></td>
                    <td>".$_SESSION['lang']['legend']."</td><td><input type=text class=myinputtext id=judultraining style='width:200px;' maxlength=45 onkeypress=\"return tanpa_kutip(event);\"><img src=images/obl.png title='Obligatory'></td>
                    
                 </tr>
                 <tr>
                    <td>".$_SESSION['lang']['tanggalmulai']."</td><td><input type=text class=myinputtext id=tanggalmulai style='width:200px;' onmousemove=setCalendar(this.id) size=10 maxlength=10 onkeypress=\"return false;\" readonly></td>
                    <td>".$_SESSION['lang']['tanggalselesai']."</td><td><input type=text class=myinputtext id=tanggalselesai style='width:200px;' onmousemove=setCalendar(this.id) size=10 maxlength=10 onkeypress=\"return false;\" readonly></td>
                    <td></td><td></td>
                 </tr>
                 <tr>
                        <td>".$_SESSION['lang']['penyelenggara']."</td><td><input type=text class=myinputtext id=penyelenggara style='width:200px;' maxlength=45 onkeypress=\"return tanpa_kutip(event);\"><img src=images/obl.png title='Obligatory'></td>
                        <td>".$_SESSION['lang']['sertifikat']."</td><td><select id=sertifikat style='width:205px;'><option value=0>".$_SESSION['lang']['no']."</option><option value=1>".$_SESSION['lang']['yes']."</option></select></td>	 
                        <td></td><td></td>
                 </tr> 
                 <tr>
				 
					<td>".$_SESSION['lang']['biaya']." Rp</td><td><input type=text class=myinputtextnumber id=biaya value=0 style='width:200px;' maxlength=15 onkeypress=\"return angka_doang(event);\"></td>
				 
                 </tr> 
                 <tr><td><td>
                 <button id=btntraining disabled class=mybutton onclick=simpanTraining()>".$_SESSION['lang']['save']."</button>
                 <button id=clearpddkn class=mybutton onclick=cleartraining()>".$_SESSION['lang']['cancel']."</button>
                 <input type=hidden value='insert' id=methodtrain>
                 <input type=hidden value='' id=nomortrain>
                
				</table>
                </fieldset>
				<fieldset><legend>".$_SESSION['lang']['traininginternal']."</legend>
                <div style='width:100%;height:250px;overflow:auto;'>
                <table class=sortable border=0 cellpadding=5 cellspacing=1 width=100%>
                        <thead>
                        <tr class=rowheader>
                          <th align=center>No.</th>
                          <th align=center>".$_SESSION['lang']['jeniskursus']."</th>			  
                          <th align=center>".$_SESSION['lang']['legend']."</th>
                          <th align=center>".$_SESSION['lang']['penyelenggara']."</th>			  
                          <th align=center>".$_SESSION['lang']['tanggalmulai']."</th>			  
                          <th align=center>".$_SESSION['lang']['tanggalselesai']."</th>
                          <th align=center>".$_SESSION['lang']['sertifikat']."</th>
                          <th align=center>".$_SESSION['lang']['biaya']."</th>    
                          <th style='text-align:center;' colspan='2'>".$_SESSION['lang']['action']."</th>
                        </tr>
                        </thead>
                        <tbody id=containertraining>
                        </tbody>
                        <tfoot>
                        </tfoot>
                </table>
                </div>		
                </fieldset>";
//Tab Keluarga================================ 
//get enum untuk hub keluarga
$opthubk='';
$arrenum=getEnum($dbname,'sdm_karyawankeluarga','hubungankeluarga');
foreach($arrenum as $key=>$val){
        if($_SESSION['language']=='EN'){
            switch($key){
              case'Pasangan':
                  $val='Couple';
                  break;
              case'Anak':
                  $val='Child';
                  break;
              case'Ibu':
                  $val='Mother';
                  break;
              case'Bapak':
                  $val='Father';
                  break;
              case'Adik':
                  $val='Younger brother/sister';
                  break;        
              case'Kakak':
                  $val='Older brother/sister';
                  break;      
              case'Ibu Mertua':
                  $val='Monther-in-law';
                  break;   
              case'Bapak Mertua':
                  $val='Father-in-law';
                  break;   
              case'Sepupu':
                  $val='Cousin';
                  break;  
              case'Ponakan':
                  $val='Nephew';
                  break;                                
              default:
                  $val='Foster child';
                  break;                         
            } 	
         }    
        $opthubk.="<option value='".$key."'>".$val."</option>";
} 	
//get enum untuk hub keluarga
$optstk='';
$arrenum=getEnum($dbname,'sdm_karyawankeluarga','status');
foreach($arrenum as $key=>$val)
{
    if($_SESSION['language']=='EN' && $val=='Kawin')
       $val='Married';
   if($_SESSION['language']=='EN' && ($val=='BelumKawin' or $val=='Lajang'))
          $val='Single';                          
        $optstk.="<option value='".$key."'>".$val."</option>";
} 
$frm[4]="<fieldset style='width:".$widthfieldset."px;'><legend>".$_SESSION['lang']['keluarga']."</legend>
         <table border=0 cellspacing=1>
                 <tr>
                    <td>".$_SESSION['lang']['nama']."</td><td><input type=text class=myinputtext id=keluarganama style=width:200px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"><img src=images/obl.png title='Obligatory'></td>
                    <td>".$_SESSION['lang']['jeniskelamin']."</td><td><select id=keluargajk  style='width:205px;'>".$optJK."</select></td>
                 </tr>
                 <tr>
                    <td>".$_SESSION['lang']['tempatlahir']."</td><td><input type=text class=myinputtext id=keluargatmplahir  style=width:200px maxlength=30 onkeypress=\"return tanpa_kutip(event);\"></td>
                    <td>".$_SESSION['lang']['tanggallahir']."</td><td><input type=text class=myinputtext id=keluargatgllahir  style=width:200px onmousemove=setCalendar(this.id) size=10 maxlength=10 onkeypress=\"return false;\" readonly></td>
                 </tr>
                 <tr>
                        <td>".$_SESSION['lang']['hubungan']."</td><td><select id=hubungankeluarga  style='width:205px;'>".$opthubk."</select></td>
                        <td>".$_SESSION['lang']['statusperkawinan']."</td><td><select id=keluargastatus style='width:205px;'>".$optstk."</select></td>	 
                 </tr> 
                 <tr>
                        <td>".$_SESSION['lang']['pendidikan']."</td><td><select id=keluargapendidikan  style='width:205px;'>".$optpendidikan."</select></td>
                    <td>".$_SESSION['lang']['pekerjaan']."</td><td><input type=text class=myinputtext id=keluargapekerjaan  style=width:200px maxlength=30 onkeypress=\"return tanpa_kutip(event);\"></td>
                 </tr>	
                 <tr>
                    <td>".$_SESSION['lang']['telp']."</td><td><input type=text class=myinputtext id=keluargatelp  style=width:200px maxlength=15 onkeypress=\"return tanpa_kutip(event);\"></td>
                    <td>".$_SESSION['lang']['email']."</td><td><input type=text class=myinputtext id=keluargaemail  style=width:200px maxlength=45 onkeypress=\"return tanpa_kutip(event);\" onblur=emailCheck(this.value)></td>
                   
                 </tr>			 	 
                 <tr>
                        <td>".$_SESSION['lang']['tanggungan']."</td><td><select id=keluargatanggungan style='width:205px;'><option value=0>".$_SESSION['lang']['no']."</option><option value=1>".$_SESSION['lang']['yes']."</option></select></td>
                        <td>".$_SESSION['lang']['emplasment']."</td><td><select id=keluargaemplasment style='width:205px;'><option value=0>".$_SESSION['lang']['no']."</option><option value=1>".$_SESSION['lang']['yes']."</option></select></td>
                 </tr>
                 <tr>
					<td>Nomor BPJS Tanggungan</td><td><input type=text class=myinputtext id=keluargabpjstanggungan  style=width:200px maxlength=45 onkeypress=\"return tanpa_kutip(event);\"></td>
                 </tr>
                 
                 <input type=hidden value=insert id=keluargamethod>
                 <input type=hidden value='' id=keluarganomor>
                 <tr><td><td>
                 <button id=btnkeluarga disabled class=mybutton onclick=simpanKeluarga()>".$_SESSION['lang']['save']."</button>
                 <button  class=mybutton onclick=clearKeluarga()>".$_SESSION['lang']['cancel']."</button>
                </table>
				</fieldset>
				<fieldset><legend>".$_SESSION['lang']['list']."</legend>
                <div style='width:100%;height:250px;overflow:auto;'>
                <table class=sortable border=0 cellpadding=5 cellspacing=1 width=100%>
                        <thead>
                        <tr class=rowheader>
                          <th align=center>No.</th>
                          <th align=center>".$_SESSION['lang']['nama']."</th>			  
                          <th align=center>".$_SESSION['lang']['jeniskelamin']."</th>
                          <th align=center>".$_SESSION['lang']['hubungan']."</th>	
						  <th align=center>".$_SESSION['lang']['tempatlahir']."</th>							  
                          <th align=center>".$_SESSION['lang']['tanggallahir']."</th>			  
                          <th align=center>".$_SESSION['lang']['statusperkawinan']."</th>
                          <th align=center>".$_SESSION['lang']['umur']."</th> 
                          <th align=center>".$_SESSION['lang']['pendidikan']."</th>
                          <th align=center>".$_SESSION['lang']['pekerjaan']."</th>
                          <th align=center>".$_SESSION['lang']['telp']."</th>
                          <th align=center>".$_SESSION['lang']['email']."</th>
                          <th align=center>".$_SESSION['lang']['tanggungan']."</th>
						  <th align=center>".$_SESSION['lang']['nobpjstanggungan']."</th>
                          <th align=center>".$_SESSION['lang']['emplasment']."</th>
                          <th align=center style='text-align:center;' colspan='2'>".$_SESSION['lang']['action']."</th>
                        </tr>
                        </thead>
                        <tbody id=containerkeluarga>
                        </tbody>
                        <tfoot>
                        </tfoot>
                </table>
                </div>		
                </fieldset>";

$frm[5]="<fieldset style='width:".$widthfieldset."px;'><legend>".$_SESSION['lang']['alamat']." Domisili</legend>
         <table border=0 cellspacing=1>
                 <tr>
                    <td rowspan=2>".$_SESSION['lang']['alamat']." Domisili</td><td rowspan=2><textarea id=alamatalamat cols=24 rows=2 onkeypress=\"return tanpa_kutip(event);\"></textarea><img src=images/obl.png title='Obligatory'></td>
                    <td>".$_SESSION['lang']['kota']."</td><td><input type=text class=myinputtext id=alamatkota style='width:205px;' maxlength=45 onkeypress=\"return tanpa_kutip(event);\"></td>
                 </tr>
                 <tr>
                        <td>".$_SESSION['lang']['province']."</td><td><select id=alamatprovinsi style='width:210px;'>".$optProvinsi."</select></td> 
                 </tr>
                 <tr>
                    <td>".$_SESSION['lang']['kodepos']."</td><td><input type=text class=myinputtext id=alamatkodepos style='width:205px;' maxlength=5 onkeypress=\"return angka_doang(event);\"></td>
                        <td>".$_SESSION['lang']['telp']."</td><td><input type=text class=myinputtext id=alamattelepon style='width:205px;'  maxlength=15 onkeypress=\"return tanpa_kutip(event);\"></td>	 
                 </tr> 
                 <tr>
                    <td>".$_SESSION['lang']['emplasmen']."</td><td><input type=text class=myinputtext id=alamatemplasement style='width:205px;' maxlength=45 onkeypress=\"return tanpa_kutip(event);\"></td>
                        <td>".$_SESSION['lang']['alamataktif']."</td><td colspan=3><select id=alamatstatus  style='width:210px;'><option value='0'>".$_SESSION['lang']['no']."</option><option value='1'>".$_SESSION['lang']['yes']."</option></select></td>
                 </tr>				 	 
                 <tr><td><td>
                 <button id=btnalamat disabled class=mybutton onclick=simpanAlamat()>".$_SESSION['lang']['save']."</button>
                 <button id=clear class=mybutton onclick=clearAlamat()>".$_SESSION['lang']['cancel']."</button>
                 <input type=hidden value='insert' id=methodalamat>
                 <input type=hidden value='' id=nomoralamat>
                </table>
                 </fieldset><fieldset><legend>".$_SESSION['lang']['list']."</legend>
                <div style='width:100%;height:250px;overflow:auto;'>
                <table class=sortable cellpadding=5 border=0 cellspacing=1 width=100%>
                        <thead>
                        <tr class=rowheader>
                          <th align=center>No.</th>
                          <th align=center>".$_SESSION['lang']['alamat']."</th>			  
                          <th align=center>".$_SESSION['lang']['kota']."</th>
                          <th align=center>".$_SESSION['lang']['province']."</th>			  
                          <th align=center>".$_SESSION['lang']['kodepos']."</th>			  
                          <th align=center>".$_SESSION['lang']['emplasmen']."</th>
                          <th align=center>".$_SESSION['lang']['status']."</th>
                          <th colspan='2' align=center>".$_SESSION['lang']['action']."</th>
                        </tr>
                        </thead>
                        <tbody id=containeralamat>
                        </tbody>
                        <tfoot>
                        </tfoot>
                </table>
                </div>		
                </fieldset>";
				
$optSupplier = "";
	$q="select * from ".$dbname.".log_5supplier where badanusaha in ('KOPERASI','YAYASAN')";
	$redy=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
	$redy->setFetchMode(PDO::FETCH_OBJ);
	$opt = "";
	while($r=$redy->fetch())
	{
			$opt.="<option value='".$r->supplierid."'>".$r->supplierid." - ".$r->namasupplier.", ".$r->badanusaha."</option>";
	}
$optSupplier = $opt;	

$optJenisPotongan = "";
	$q="select * from ".$dbname.".keu_5komponenbiaya where kelompokbiaya = 'karyawan'";
	$redy=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
	$redy->setFetchMode(PDO::FETCH_OBJ);
	$opt = "";
	while($r=$redy->fetch())
	{
			$opt.="<option value='".$r->kodebiaya."'>".$r->kodebiaya." - ".$r->keteranganbiaya."</option>";
	}
$optJenisPotongan = $opt;
$frm[8]="<fieldset style='width:".$widthfieldset."px;'>
         <legend>Insert Koperasi/Serikat</legend>
		 <form method='POST' action='#' > 
			<table border='0' cellspacing='1' style='width:100%; max-width:700px;'>
                 <tr>
                    <td align=left width=200>Koperasi ".$_SESSION['lang']['dan']." Serikat : <img src='images/obl.png' title='Obligatory'></td>
					<td align=left width=200>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['potongan']." ".$_SESSION['lang']['karyawan']." : <img src='images/obl.png' title='Obligatory'></td>
                 </tr>
				 <tr>
                    <td align=left ><select id='listkoperasi' style='width:99%;'>$optSupplier</select></td>
					<td align=left ><select id='jenispotongan' style='width:99%;'>$optJenisPotongan</select></td>
                 </tr>
				 <tr>
					<td align=left width=50>".$_SESSION['lang']['tahun']." : <img src='images/obl.png' title='Obligatory'></td>
					<td align=left width=50>".$_SESSION['lang']['jumlah']." : <img src='images/obl.png' title='Obligatory'></td>
				 </tr>
				 <tr>
					<td align='left' width='50' ><input id='tahunpotongan' style='width:99%;' type=text class=myinputtext onkeypress=\"return angka_doang(event);\"></td>
					<td align=left ><input id='jumlahpotongan' style='width:99%;' type=text class=myinputtext onkeypress=\"return angka_doang(event);\"></td>
				 </tr>
				 <tr>
					<td colspan=2><br>Upload ".$_SESSION['lang']['suratpernyataan']." : [File Max. 256Kb][ jpeg/png/pdf ] <img src='images/obl.png' title='Obligatory'></td>
				 </tr>
				 <tr>
					<td colspan=2>
						<input type='file' id='fileupload' class=myinputtext style='width:360px;height:20px;padding:5px;'>
					</td>
				 </tr>
			</table>	
			<br/>
			<button class=mybutton onclick='simpanRAK();return false;'>".$_SESSION['lang']['save']."</button>
			<button type='reset' class=mybutton onclick=clearRAK()>".$_SESSION['lang']['cancel']."</button>
		</form>	
		</fieldset>
		<br/>
		<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
                <div style='width:100%;height:250px;overflow:auto;'>
                <table class=sortable cellpadding=5 border=0 cellspacing=1 width=100%>
					<thead>
					<tr class=rowheader>
					  <th>No.</th>
					  <th>Koperasi ".$_SESSION['lang']['dan']." Serikat</th>			  
					  <th>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['potongan']."</th>
					  <th>".$_SESSION['lang']['tahun']."</th>			  
					  <th>".$_SESSION['lang']['jumlah']."</th>			  
					  <th>".$_SESSION['lang']['namafile']."</th>
					  <th></th>
					</tr>
					</thead>
					<tbody id=containerRAK>
					</tbody>
					<tfoot>
					</tfoot>
                </table>
			</div>		
		</fieldset>";

$q="select * from ".$dbname.".sdm_5tipedokumen where untuk = 'KARYAWAN' and isactive = '1'";
$redy=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
$redy->setFetchMode(PDO::FETCH_OBJ);
$html = "";
while($r=$redy->fetch())
{
	$html .= '<tr><td style="width:300px;padding:5px;">'.$r->namatipe.'</td><td style="padding:5px;"><input id="document_'.$r->id.'" type="file" for="'.$r->id.'"></td><td id="download_'.$r->id.'" width="10"><img src="images/download-file-die.png" width="20" style="opacity:0.5;"></td></tr>'; 
}			

$frm[6]="<fieldset style='width:".$widthfieldset."px;'>
		<legend>".$_SESSION['lang']['uploadfile']."</legend>
		<p>File Format : [jpeg,png,pdf] </p>
		 <form method='POST' action='#' onsubmit='simpanUploadDoc(this);return false;' > 
			<table border='1' style='width:100%; border-collapse:collapse;'> 
			$html
			</table>
		 <br/>
		 <button class=mybutton type='submit'>".$_SESSION['lang']['save']."</button>
		 </form>
</fieldset>";

$frm[7]="<fieldset style='width:".$widthfieldset."px;'>
         <legend>Insert Reward</legend>
     <form method='POST' action='#' > 
      <table border='0' cellspacing='1' style='width:100%; max-width:700px;'>
                
         
         <tr>
          <td align=left width=50>".$_SESSION['lang']['nama']." : Reward<img src='images/obl.png' title='Obligatory'></td>
          <td align=left width=5></td>
          <td align=left width=50>".$_SESSION['lang']['tanggal']." : <img src='images/obl.png' title='Obligatory'></td>
         </tr>
         <tr>
          <td align='left' width='50' ><input id='namareward' style='width:99%;' type=text class=myinputtext \"></td>
          <td hidden align='left' width='50' ><input id='karyawanidreward' style='width:99%;' type=text class=myinputtext \"></td>
          <td align=left width=5></td>
          <td align=left ><input style='width:70%;'type=text class=myinputtext id=tanggalreward size=27 onmousemove=setCalendar(this.id) maxlength=20 onkeypress=\"return false;\" readonly><img src=images/obl.png title='Obligatory'></td>
         </tr>
         <tr>
          <td colspan=2><br>Upload Reward : [File Max. 256Kb][ jpeg/png/pdf ] <img src='images/obl.png' title='Obligatory'></td>
         </tr>
         <tr>
          <td colspan=2>
            <input type='file' id='upload' class=myinputtext style='width:360px;height:20px;padding:5px;'>
          </td>
         </tr>
      </table>  
      <br/>
      <button class=mybutton onclick='submitfile();return false;'>".$_SESSION['lang']['save']."</button>
      <button type='reset' class=mybutton onclick=clearreward()>".$_SESSION['lang']['cancel']."</button>
    </form> 
    </fieldset>
    <br/>
    <fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
                <div style='width:100%;height:250px;overflow:auto;'>
                <table class=sortable border=0 cellpadding=5 cellspacing=1 width=100%>
          <thead>
          <tr class=rowheader>
            <th>No.</th>
            <th>Nama Reward</th>        
            <th>".$_SESSION['lang']['tanggal']."</th>
            <th>".$_SESSION['lang']['namafile']."</th>
            <th>Aksi</th>
           
          </tr>
          </thead>
          <tbody id=containerreward>
          </tbody>
          <tfoot>
          </tfoot>
                </table>
      </div>    
    </fieldset>";

$q="select * from ".$dbname.".sdm_5tipedokumen where untuk = 'KARYAWAN' and isactive = '1'";
$redy=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
$redy->setFetchMode(PDO::FETCH_OBJ);
$html = "";
while($r=$redy->fetch())
{
  $html .= '<tr><td style="width:300px;padding:5px;">'.$r->namatipe.'</td><td style="padding:5px;"><input id="document_'.$r->id.'" type="file" for="'.$r->id.'"></td><td id="download_'.$r->id.'" width="10"><img src="images/download-file-die.png" width="20" style="opacity:0.5;"></td></tr>'; 
}     
				
$hfrm[0]=$_SESSION['lang']['karyawanbaru'];
$hfrm[1]=$_SESSION['lang']['pengalamankerja'];
$hfrm[2]=$_SESSION['lang']['pendidikan'];
$hfrm[3]=$_SESSION['lang']['traininginternal'];
$hfrm[4]=$_SESSION['lang']['keluarga'];
$hfrm[5]=$_SESSION['lang']['alamat'];
$hfrm[6]=$_SESSION['lang']['uploadfile'];
$hfrm[7]='Reward';
//$hfrm[8]=$_SESSION['lang']['registeranggotakoperasi'];
//$hfrm[7]=$_SESSION['lang']['alamat'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,120,"100%");
//========================end input form
echo"</div>";


echo"<div style='display:none;'>".$_SESSION['lang']['daftarkaryawan']." ".$_SESSION['empl']['lokasitugas'].":<span id=cap1></span>-<span id=cap2></span>";
echo"</div>";
echo"<div class='table-scroll' id='searchplace' style='display:none;'>";
echo"
		<table class=sortable border=0 cellspacing=1 cellpadding=7 width=100%>
         <thead>
           <tr class=rowheader>
             <th align=center>No.</th>
                 <th align=center>".$_SESSION['lang']['nik']."</th>
                 <th align=center>".$_SESSION['lang']['nama']."</th>
                 <th align=center>".$_SESSION['lang']['functionname']."</th>
                 <th align=center>".$_SESSION['lang']['kodegolongan']."</th>
                 <th align=center>".$_SESSION['lang']['lokasitugas']."</th>
                 <th align=center>".$_SESSION['lang']['divisi']."</th>
                 <th align=center>".$_SESSION['lang']['pt']."</th>
                 <th align=center>".$_SESSION['lang']['noktp']."</th>
                 <th align=center>".$_SESSION['lang']['pendidikan']."</th>
                 <th align=center>".str_replace(" ","<br>",$_SESSION['lang']['statuspajak'])."</th>
                 <th align=center>".str_replace(" ","<br>",$_SESSION['lang']['statusperkawinan'])."</th>
                 <th align=center>".str_replace(" ","<br>",$_SESSION['lang']['jumlahanak'])."</th>
                 <th align=center>".$_SESSION['lang']['tanggalmasuk']."</th>
                 <th align=center>".$_SESSION['lang']['tanggalkeluar']."</th>
                 <th align=center>".str_replace(" ","<br>",$_SESSION['lang']['tipekaryawan'])."</th>
                 <th align=center>".$_SESSION['lang']['status']." ".$_SESSION['lang']['karyawan']."</th>
                 <th align=center colspan=3>Action</th>
           </tr>
         </thead>
         <tbody id=searchplaceresult>
			<script>displayList();</script>
         </tbody>
         <tfoot>
         <tr><td colspan=20>
			<center><button class=mybutton value=0 onclick=prefDatakaryawan(this,this.value) id=prefbtn>< Prev</button> 
					<button class=mybutton value=2 onclick=nextDatakaryawan(this,this.value) id=nextbtn> Next ></button>         
			</center>
         </tfoot>
		</table>
     </div>";
echo"<div id='postingdata' style='display:none;height:450px;'>";
echo"
		<table class=sortable border=0 cellspacing=1 cellpadding=2>
         <thead>
           <tr class=rowheader>
             <th align=center width=50px>No.</th>
                 <th align=center width=100px>".$_SESSION['lang']['periode']."</th>
                 <th align=center>".$_SESSION['lang']['kodeorganisasi']."</th>
                 <th align=center>".$_SESSION['lang']['namaorganisasi']."</th>
                 <th align=center>".$_SESSION['lang']['status']."</th>
                 <th align=center>Action</th>
           </tr>
         </thead>
         <tbody id=listpostingdata></tbody>
         <tfoot></tfoot>
		</table>
     </div>";


CLOSE_BOX();
close_body('');
?>