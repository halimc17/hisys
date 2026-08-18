<?//@Copy nangkoelframework

require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript src='js/sdm_5hakcutitambahan.js?v=<?php echo time(); ?>'></script>
<?php

$optType = "";
$typecuti = getEnum($dbname,"sdm_5hakcuti","type");
foreach($typecuti as $val){
	$optType .= "<option value='".$val."'>".$val."</option>";
}

$optBulanmulai = "";
$bulanmulai = getEnum($dbname,"sdm_5hakcuti","bulanmulai");
foreach($bulanmulai as $val){
	$optBulanmulai .= "<option value='".$val."'>".$val."</option>";
}

## Kode Organisasi
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(1) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($key,0,4)."'");
	$d=$induk[substr($key,0,4)];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

## Level Karyawan
$optLevelkaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql="select * from ".$dbname.". sdm_5levelkaryawan where aktif='1'  ORDER BY `nama` ASC";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch())
{
    $optLevelkaryawan.="<option value=".$res['kode'].">".$res['kode']." - ".$res['nama']."</option>"; 
}

## Golongan
$optGolongan="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql="select * from ".$dbname.". sdm_5golongan where aktif='1'  ORDER BY `namagolongan` ASC";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch())
{
    $optGolongan.="<option value=".$res['kodegolongan'].">".$res['kodegolongan']." - ".$res['namagolongan']."</option>"; 
}

$sStr = selectQuery($dbname, "sdm_5tipekaryawan", "*");
$qStr = fetchData($sStr);
$optTipekaryawan = "<option value=''>".$_SESSION['lang']['all']."</option>";
foreach ($qStr as $val) {
    $optTipekaryawan .= "<option value='".$val['id']."'>".$val['tipe']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_5hakcutitambahan').'</span>');
echo"<br>";

echo"<fieldset style='width:450px;float:left;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=2 cellspacing=1>
                 <tr>
                    <td>".$_SESSION['lang']['kodeorg']."</td> 
                    <td>:</td>
                    <td><select id=kodeorg style='width:155px;' >".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['kodegolongan']."</td> 
                    <td>:</td>
                    <td>
                        <select id=kodegolongan style='width:155px;' >".$optGolongan."</select>
                        <img id='imgkodegolongan' onclick=z.elSearch('kodegolongan',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
                    </td>
                </tr>
                 <tr>
                    <td>Level ".$_SESSION['lang']['karyawan']."</td> 
                    <td>:</td>
                    <td>
                        <select id=levelkaryawan style='width:155px;' >".$optLevelkaryawan."</select>
                    </td>

                </tr>
                 <tr>
                    <td>Tipe ".$_SESSION['lang']['karyawan']."</td> 
                    <td>:</td>
                    <td>
                        <select id=tipekaryawan style='width:155px;' >".$optTipekaryawan."</select>
                    </td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['masakerja']." (Bulan)</td> 
                    <td>:</td>
                    <td><input type=number  id=masakerja onkeypress=\"return angka_doang(event)\"  class=myinputtextnumber style=\"width:150px;\"></td>
                </tr>
                <tr>
                    <td>Masa Berlaku (Bulan)</td> 
                    <td>:</td>
                    <td><input type=number  id=masaaktif onkeypress=\"return angka_doang(event)\"  class=myinputtextnumber style=\"width:150px;\"></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['hakcuti']."</td> 
                    <td>:</td>
                    <td><input type=number  id=hakcuti onkeypress=\"return angka_doang(event)\"  class=myinputtextnumber style=\"width:150px;\"></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=cancel()>Clear</button>
                    </td>
                </tr>
            </table></fieldset>
            <input type=hidden id=method value='insert'></br>";

            echo"<fieldset style='width:650px;float:left;'>
                <legend>Informasi Setup :</legend>
                    <li>Golongan <b>Tidak Sama Dengan </b> Seluruhnya dan Level Karyawan <b> Tidak Sama Dengan </b> Seluruhnya </li>
                    <li>Golongan <b>Sama Dengan </b> Seluruhnya dan Level Karyawan <b> Tidak Sama Dengan </b> Seluruhnya </li>
                    <li>Golongan <b>Tidak Sama Dengan </b> Seluruhnya dan Level Karyawan <b> Sama Dengan </b> Seluruhnya </li>
                    <li>Golongan <b> Sama Dengan </b> Seluruhnya dan Level Karyawan <b> Sama Dengan </b> Seluruhnya </li>
                </fieldset>";
CLOSE_BOX();
?>

<?php
OPEN_BOX();
//ISI UNTUK DAFTAR 
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpadding=7 cellspacing=1 border=0 class=sortable width=100% >";
echo"<thead>";
echo"<td align=center>".$_SESSION['lang']['nourut']."</td>
     <td align=center>".$_SESSION['lang']['kodeorg']."</td>
     <td align=center>".$_SESSION['lang']['kodegolongan']."</td>
     <td align=center>Level ".$_SESSION['lang']['karyawan']."</td>
     <td align=center>Tipe ".$_SESSION['lang']['karyawan']."</td>
     <td align=center>".$_SESSION['lang']['masakerja']." ( Bulan )</td>
     <td align=center>Masa Berlaku ( Bulan )</td>
     <td align=center>".$_SESSION['lang']['hakcuti']."</td>
     <td align=center>".$_SESSION['lang']['createby']."</td>
     <td align=center>".$_SESSION['lang']['createtime']."</td>
     <td align=center>".$_SESSION['lang']['updateby']."</td>
     <td align=center>".$_SESSION['lang']['updatetime']."</td>
     <td align=center>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div>";


CLOSE_BOX();
echo close_body();                  
?>