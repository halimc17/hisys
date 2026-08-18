<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_dividen.js'></script>

<?php

$optakun=$opttipetrans=$opttrans=$optunit2=$optbank=$opttipe=$optunit1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//get unit1
$res=$owlPDO->query("select a.unit,b.namaorganisasi from ".$dbname.".keu_5organisasi a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi where b.tipe='HOLDING' and char_length(b.kodeorganisasi)=4 order by b.namaorganisasi");
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optunit1.="<option value='".$bar['unit']."'>".$bar['unit']." - ".$bar['namaorganisasi']."</option>";
}

$arrunit=array();
//get unit2
$res=$owlPDO->query("select a.unit,b.namaorganisasi from ".$dbname.".keu_5organisasi a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi where b.tipe='HOLDING' and char_length(b.kodeorganisasi)=4 order by b.namaorganisasi");
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $arrunit[$bar['unit']]=$bar['unit'];
    $optunit2.="<option value='".$bar['unit']."'>".$bar['unit']." - ".$bar['namaorganisasi']."</option>";
}

//get unit2 induk
$res=$owlPDO->query("select a.indukunit as unit,b.namaorganisasi from ".$dbname.".keu_5organisasi a left join ".$dbname.".organisasi b on a.indukunit=b.kodeorganisasi where b.tipe='HOLDING' and char_length(b.kodeorganisasi)=4 and a.indukunit not in ('".implode("','",$arrunit)."') order by b.namaorganisasi");
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optunit2.="<option value='".$bar['unit']."'>".$bar['unit']." - ".$bar['namaorganisasi']."</option>";
}

$str="select unit,namaunit from ".$dbname.".keu_5organisasi where tipe='EKSTERNAL' order by unit";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optunit2.="<option value='".$bar['unit']."'>".$bar['unit']." - ".$bar['namaunit']."</option>";
}

//get noakun
$res=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)=7 and left(noakun,3) in ('114','349','219','913')");
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
}

//Tipe transaksi
$arrtipe=getEnum($dbname,'keu_dividen','tipetransaksi');
foreach($arrtipe as $kei=>$fal)
{
    $opttipetrans.="<option value='".$kei."'>".$fal."</option>";
}

//Transaksi
$arrtipe=getEnum($dbname,'keu_dividen','transaksi');
foreach($arrtipe as $kei=>$fal)
{
    $opttrans.="<option value='".$kei."'>".$fal."</option>";
}

//Status
$arrtipe=getEnum($dbname,'keu_dividen','status');
foreach($arrtipe as $kei=>$fal)
{
    $opttipe.="<option value='".$kei."'>".$fal."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_dividen').'</span>');
echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo $_SESSION['lang']['notransaksi']." : <input type=text id=notranscr size=18 maxlength=30 class=myinputtext>";
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
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:148px; maxlength=10 /></td>
                    <td>".$_SESSION['lang']['tipetransaksi']."</td>
                    <td>:</td>
                    <td><select id=tipetransaksi style=width:150px; >".$opttipetrans."</select></td>
                </tr>
                <tr> 
                    <td>".$_SESSION['lang']['unit']." 1</td> 
                    <td>:</td>
                    <td><select id=unit1 style=width:150px; onchange='getakun()'>".$optunit1."</select></td> 
                    <td>".$_SESSION['lang']['transaksi']."</td>
                    <td>:</td>
                    <td><select id=transaksi style=width:150px; onchange='getunit2()' >".$opttrans."</select></td>
                </tr>
                <tr>  
                    <td>".$_SESSION['lang']['namabank']."</td>
                    <td>:</td>
                    <td><select id=norek style=width:150px; onchange='getmatauang()'>".$optbank."</select></td>
                    <td>".$_SESSION['lang']['unit']." 2</td>
                    <td>:</td>
                    <td><select id=unit2 style=width:150px; >".$optunit2."</select></td>
                </tr>
                <tr>  
                    <td>".$_SESSION['lang']['matauang']."</td>
                    <td>:</td>
                    <td><input type=text id=matauang class=myinputtext style=width:148px; disabled></td>
                    <td>".$_SESSION['lang']['total']."</td>
                    <td>:</td>
                    <td><input type=text id=nilai class=myinputtextnumber onkeyup=\"z.numberFormat('nilai',2);gettotpenerima()\" style=width:148px; ></td>
                </tr>
                <tr>  
			        <td>".$_SESSION['lang']['status']."</td>
			        <td>:</td>
			        <td><select id=status onchange='disakun()' style=width:150px; >".$opttipe."</select></td>
                    <td>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['piutang']." ".$_SESSION['lang']['eksternal']."</td>
                    <td>:</td>
                    <td><select id=akunpiutangeks style=width:150px; >".$optakun."</select></td>
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
echo"<td>".$_SESSION['lang']['tanggal']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['unit']." 1</td>";
echo"<td>".$_SESSION['lang']['norekeningbank']."</td>";
echo"<td>".$_SESSION['lang']['unit']." 2</td>";
echo"<td>".$_SESSION['lang']['jumlah']."</td>";
echo"<td>".$_SESSION['lang']['status']."</td>";
echo"<td>".$_SESSION['lang']['transaksi']."</td>";
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