<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2panenvsrawat').'</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/kebun_2panenvsrawat.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zComment.js?ver=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css href=style/zComment.css>
<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$optDiv2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt=$optBlk="<option value=''>".$_SESSION['lang']['all']."</option>";

// $str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
// $res=fetchdata($str);
// foreach($res as $bar){
// 	$s="";
// 	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

foreach(getOrgDetail(3) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optPT.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optPT.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optPT.="</optgroup>";
	}
}

// $str="select * from ".$dbname.".organisasi where tipe='KEBUN' and induk='".$_SESSION['empl']['kodeorganisasi']."'";
// $res=fetchdata($str);
// foreach($res as $bar){
// 	$s="";
// 	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
// 		$s="selected";
// 	}
//     $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// }

foreach(getOrgDetail(23) as $key => $val){
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

$str="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."'";
$res=fetchdata($str);
foreach($res as $bar){
	$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select distinct tahuntanam from ".$dbname.".setup_blok where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by tahuntanam asc";
$res=fetchdata($str);
foreach($res as $bar){
    $optTt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=fetchdata($str);
foreach($res as $bar){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrtipe=array('bi'=>'Bulan Ini','sdbi'=>'Sampai Bulan Ini');
foreach($arrtipe as $kode => $nama){
    $opttipe.="<option value=" . $kode . ">" . $nama . "</option>";
}

$arrtampil=array(''=>'Seluruhnya','1'=>'Produksi Capai Target','2'=>'Produksi Tidak Capai Target','4'=>'Tidak ada Produksi ada Budget Produksi','3'=>'Tidak ada Produksi ada Biaya');
foreach($arrtampil as $kode => $nama){
    $opttampil.="<option value=" . $kode . ">" . $nama . "</option>";
}

$arrbyy=array('621'=>'Biaya Pemeliharaan','611'=>'Biaya Panen');
foreach($arrbyy as $kode => $nama){
    $optklbyy.="<option value=" . $kode . ">" . $nama . "</option>";
}

$str = "select * from " . $dbname . ".setup_kegiatan  where 1=1 and substr(noakun,1,3) in ('621')  and namakegiatan not like '%NON AKTIF%' and namakegiatan not like '%TIDAK DIPAKAI%'"; 
$res=fetchdata($str);
foreach($res as $bar){
	$d=substr($bar['noakun'],0,3);
	if($d!=$n){			
		$optbyy.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
	}
	$e=$bar['noakun'];
	if($e!=$m){			
		$optbyy.="<optgroup label='".$e." - ".getNamaAkun($e)."'>";
	}
    $optbyy.="<option value=" . $bar['kodekegiatan'] . ">".$bar['kodekegiatan']." - " . $bar['namakegiatan'] . "</option>";
	$m=$e;
	if($e!=$m){			
		$optbyy.="</optgroup>";
	}
	$n=$d;
	if($d!=$n){			
		$optbyy.="</optgroup>";
	}
}

$arr1 = "##pt##kdorg##prd##divisi##tt##akun##blok";
echo"<fieldset style='float:left;' id=tableheader>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','".$_SESSION['lang']['all']."')  style=\"width:164px;\">" .$optPT . "</select></td>
               
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi onchange=getThnTnm(this,'tt','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
               
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tt onchange=getBlokBig() style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['blok'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=blok style=\"width:164px;\">" . $optBlk . "</select></td>

					<td style=vertical-align:top>" . $_SESSION['lang']['intiplasma'] . "</td>
                    <td style=vertical-align:top>:</td>
                    <td style=vertical-align:top><select class=select2 id=ip style=\"width:164px;\">
							<option value=''>Seluruhnya</option>
							<option value='I' selected>INTI</option>
							<option value='P'>PLASMA</option>
						</select>
					</td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=prd style=\"width:164px;\">" . $optper . "</select></td>

					<td>" . $_SESSION['lang']['jenis'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=jenis style=\"width:164px;\" onchange=getisikegiatan(this);><option value='rupiah'>RUPIAH</option><option value='fisik'>FISIK</option></select></td>
				</tr>
				<tr>	
					<td>" . $_SESSION['lang']['tampilkan'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tampil style=\"width:164px;\">" . $opttampil . "</select></td>
					<td>" . $_SESSION['lang']['kelompokbiaya'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=klp onchange=getkegiatan(); style=\"width:164px;\">" . $optklbyy . "</select></td>
				</tr>
				<tr>		
					<td style=vertical-align:top>" . $_SESSION['lang']['bulanini'] . "</td>
                    <td style=vertical-align:top>:</td>
                    <td style=vertical-align:top><select class=select2 id=bulanini style=\"width:164px;\">
							<option value='show'>Tampilkan</option>
							<option value='hide'>Sembunyikan</option>
						</select>
					</td>
                    <td style=vertical-align:top>" . $_SESSION['lang']['kegiatan'] . "</td>
                    <td style=vertical-align:top>:</td>
                    <td><select class=select2 multiple id=keg style=\"width:165px;\">" . $optbyy . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=preview('preview'); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=preview('excel') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer' class='table-scroll' style=height:73vh></div>";

CLOSE_BOX();
echo close_body();
?>