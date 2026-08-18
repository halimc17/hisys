<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/vhc_2jurnalkendaraan.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('vhc_2jurnalkendaraan').'</span><br>');


$optPt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi','tipe like "%traksi%"');
$sOrg="select distinct kodetraksi from ".$dbname.".vhc_5master order by kodetraksi asc";
$qOrg=fetchData($sOrg);
foreach($qOrg as $brsOrg)
{
    $optPt.="<option value=".$brsOrg['kodetraksi'].">".$brsOrg['kodetraksi']." - ".$optOrg[$brsOrg['kodetraksi']]."</option>";
}

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}


$iAlokasi="select distinct(alokasibiaya) as alokasibiaya from ".$dbname.".vhc_rundt where alokasibiaya not like 'AK-%' ";

$nAlokasi=$owlPDO->query($iAlokasi) or die(print " Gagal: ".PDOException::getMessage());
$nAlokasi->setFetchMode(PDO::FETCH_ASSOC);
while($dAlokasi=$nAlokasi->fetch()){
    $optLokasi.="<option value='".$dAlokasi['alokasibiaya']."'>".$dAlokasi['alokasibiaya']." - ".getNamaOrg($dAlokasi['alokasibiaya'])."</option>";    
}

$optAkun="<option value=''>".$_SESSION['lang']['all']."</option>";
$nmAkun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$sOrg1="select noakun,namakegiatan from ".$dbname.".vhc_kegiatan order by noakun";
$qOrg1=fetchData($sOrg1);
foreach($qOrg1 as $brsOrg1)
{
    $optAkun.="<option value=".$brsOrg1['noakun'].">".$brsOrg1['noakun']." - ".$brsOrg1['namakegiatan']."</option>";
}

$sjnskrj="select * from ".$dbname.".vhc_kegiatan where tipe ='traksi' and (kelompokvhc='".$kelvhc."' or kelompokvhc='GLOBAL') and (jenisvhc='".$kode_jns."' or jenisvhc='GLOBAL') order by noakun asc";
$optAkun="<option value=''>".$_SESSION['lang']['all']."</option>";
$res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rjnskrj=$res->fetch()){
	$d=substr($rjnskrj['kodekegiatan'],0,5);
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
		$optAkun.="<optgroup label='".$nmorg[$d]."'>";
	}
	$optAkun.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
	$n=$d;
	if($d!=$n){
		$optAkun.="</optgroup>";
	}
}


// OPEN_BOX('','<span class=judul>'.getMenu('vhc_2jurnalkendaraan').'</span><br>');
echo"<fieldset style=float:left><table><legend>".$_SESSION['lang']['pilihdata']."</legend> ";
    echo"
        <tr>
            <td>".$_SESSION['lang']['unitkerja']."</td>
            <td>:</td>
            <td><select class='select2' id=company_id name=company_id onChange=get_jnsVhc() style=width:200px>".$optKodeorg."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['jenisvch']."</td>
            <td>:</td>
            <td><select class='select2' id=jnsVhc name=jnsVhc onchange=\"getKdVhc()\" style=width:200px><option  value=''>".$_SESSION['lang']['all']."</option></select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['kodevhc']."</td>
            <td>:</td>
            <td><select class='select2' id=kdVhc name=kdVhc style=width:200px><option  value=''>".$_SESSION['lang']['all']."</option></select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['alokasi']."</td>
            <td>:</td>
            <td><select class='select2' id=alokasi name=alokasi style=width:200px><option  value=''>".$_SESSION['lang']['all']."</option>".$optLokasi."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type=\"text\" class=\"myinputtext\" id=\"tglAwal\" name=\"tglAwal\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:82px;\" readonly/> S/D
            <input type=\"text\" class=\"myinputtext\" id=\"tglAkhir\" name=\"tglAkhir\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:82px;\" readonly/></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['noakun']." & ".$_SESSION['lang']['pekerjaan']." </td>
            <td>:</td>
            <td><select class='select2' id=akun name=akun style=width:200px>".$optAkun."</select></td>
        </tr>
        <tr>
		<td colspan=2></td>
        <td>
			<button class=mybutton onclick=detailAlokasi()>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=detailAlokasi('excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=ganti_pil()>".$_SESSION['lang']['cancel']."</button></td>";
         echo"</tr>";
                        
echo"
     
         </table></fieldset> "; 
// CLOSE_BOX();
// OPEN_BOX('','');
//	 <img onclick=hutangSupplierKePDF(event,'log_laporanhutangsupplier_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>

CLOSE_BOX();
OPEN_BOX();
echo"
	 <div id=container style='height:55vh;' class=table-scroll>
     </div>";
CLOSE_BOX();
close_body();
  //<td align=center>".$_SESSION['lang']['periode']."</td>
?>