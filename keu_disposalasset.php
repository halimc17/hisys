<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_disposalasset.js'></script>

<?php

$arrunit=getOrgDetail(2); 
$arrstatus=array('1' => 'Disposal','2' => 'Write-off');
$optunit=$optJenisket=$optasset=$optJenis.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrJenis=getEnum($dbname,'keu_5jenisdisposalasset','jenis');
foreach($arrJenis as $kei=>$fal)
{
    $optJenis.="<option value='".$kei."'>".$arrstatus[$fal]."</option>";
}

$str="select kodeasset,namasset from ".$dbname.".sdm_daftarasset where kodeorg in (".$arrunit.") and status='1' and kodeasset not in (select kodeasset from ".$dbname.".keu_disposalasset where statuspersetujuan not in (0,2) and left(notransaksi,4) in (".$arrunit."))  order by namasset";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) 
{
    $optasset.="<option value='".$bar['kodeasset']."'>".$bar['kodeasset']." - ".$bar['namasset']."</option>";
}

$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal)
{
    $optunit.="<option value='".$kei."'>".$fal."</option>";
}


$optper1=$optper2=$optper3=$optper4.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
##persetujuan1
$str="select distinct b.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit in (".$arrunit.") and b.jenispersetujuan='DISPO' and b.level='1' order by a.namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper1.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."</option>";
}

##persetujuan2
$str="select distinct b.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit in (".$arrunit.") and b.jenispersetujuan='DISPO' and b.level='2' order by a.namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper2.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."</option>";
}

##persetujuan3
$str="select distinct b.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit in (".$arrunit.") and b.jenispersetujuan='DISPO' and b.level='3' order by a.namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper3.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."</option>";
}

##persetujuan4
$str="select distinct b.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit in (".$arrunit.") and b.jenispersetujuan='DISPO' and b.level='4' order by a.namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper4.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_disposalasset').'</span>');
echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo $_SESSION['lang']['notransaksi']." : <input type=text id=notranscr size=18 maxlength=30 class=myinputtext>";
echo $_SESSION['lang']['jenis']." : <select id=jeniscr style=width:150px; >".$optJenis."</select>";
echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
         </table> "; 
CLOSE_BOX();

OPEN_BOX();
echo"<div id=formInput style=display:none;>
    <fieldset style='float:left;'>";
    echo"<legend>".$_SESSION['lang']['form']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td> 
                    <td>:</td>
                    <td><select id=unit style=width:200px; onchange=getasset()>".$optunit."</select></td>
                    <td>".$_SESSION['lang']['namaasset']."</td> 
                    <td>:</td>
                    <td >
                        <select id=kodeasset style=width:200px; onchange=getdata()>".$optasset."</select>
                        <img src=\"images/onebit_02.png\" style='float:right;' id=tombasset style='position:relative;top:3px;' class=\"resicon\" title='".$_SESSION['lang']['find']."' onclick=\"searchkodeasset('".$_SESSION['lang']['find']."','<div id=formPencariandata></div>',event)\">
                    </td>
                </tr>
                <tr>
                    <td valign=top>".$_SESSION['lang']['nilaibuku']."</td> 
                    <td valign=top>:</td>
                    <td valign=top><input type=text onkeypress=\"return angka_doang(event);\" onkeyup=\"z.numberFormat('nilaibuku',2);\" class=myinputtextnumber id=nilaibuku style=width:197px; disabled></td>
                    <td valign=top>".$_SESSION['lang']['akumulasipenyusutan']."</td> 
                    <td valign=top>:</td>
                    <td valign=top><input type=text onkeypress=\"return angka_doang(event);\" class=myinputtextnumber id=akumulasipenyusutan style=width:197px; disabled></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['jenis']."</td> 
                    <td>:</td>
                    <td><select id=jenis style=width:200px; onchange=getjenisket()>".$optJenis."</select></td>
                    <td>".$_SESSION['lang']['status']."</td> 
                    <td>:</td>
                    <td><select id=jenisket style=width:200px; onchange=checkdata()>".$optJenisket."</select></td>
                </tr>
                <tr>
                    <td valign=top>".$_SESSION['lang']['keterangan']."</td> 
                    <td valign=top>:</td>
                    <td colspan=4><textarea id=ket nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style='width:530px;height:30px' ></textarea></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['persetujuan']." 1</td> 
                    <td>:</td>
                    <td><select id=persetujuan1 style=width:200px; >".$optper1."</select></td>
                    <td>".$_SESSION['lang']['persetujuan']." 2</td> 
                    <td>:</td>
                    <td><select id=persetujuan2 style=width:200px; >".$optper2."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['persetujuan']." 3</td> 
                    <td>:</td>
                    <td><select id=persetujuan3 style=width:200px; >".$optper3."</select></td>
                    <td>".$_SESSION['lang']['persetujuan']." 4</td> 
                    <td>:</td>
                    <td><select id=persetujuan4 style=width:200px; >".$optper4."</select></td>
                </tr>
                <tr><td colspan=2></td>
                    <td colspan=3>
                        <button class=mybutton onclick=simpan()>Simpan</button>
                        <button class=mybutton onclick=cancel()>Hapus</button>
                        <input type=hidden id=method value='insert'>
                        <input type=hidden id=notransaksi value=''>
                    </td>
                </tr>
        	</table></fieldset></div>";

echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['namaasset']."</td>";
echo"<td>".$_SESSION['lang']['jenis']."</td>";
echo"<td>".$_SESSION['lang']['status']."</td>";
echo"<td>".$_SESSION['lang']['catatan']."</td>";
echo"<td>".$_SESSION['lang']['updateby']."</td>";
echo"<td colspan=4>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
CLOSE_BOX();
echo close_body();					
?>