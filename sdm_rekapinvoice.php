<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('sdm_rekapinvoice')."</span>"); //1 O
?>
<script type="text/javascript" src="js/sdm_rekapinvoice.js" /></script>

<?php

#Tipe Transaksi
$opt=$opttipe=$optnoinvoice="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrtipe=getEnum($dbname,'sdm_rekapinvoice','jenisdata');
foreach($arrtipe as $kei=>$fal)
{
    $opttipe.="<option value='".$kei."'>".$fal."</option>";
}

#noinvoice
$str="select noinvoice from ".$dbname.".keu_tagihanht where tipeinvoice='t' and noinvoice not in (select noinvoice from ".$dbname.".sdm_rekapinvoice where posting=1)";
$res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optnoinvoice.="<option value='".$bar['noinvoice']."'>".$bar['noinvoice']."</option>";
}

echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
         <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo"<table>";
echo"<tr><td>".$_SESSION['lang']['jenis']."</td><td> : </td><td><select id=tipecr style=width:150px;>".$opttipe."</select></td>";
echo"<td>".$_SESSION['lang']['rute']."</td><td> : </td><td><input type=text id=rutecr class=myinputtext style=width:150px; ></td></tr>";
echo"<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> : </td><td><input type=text id=notransaksicr class=myinputtext style=width:150px; ></td>";
echo"<td>".$_SESSION['lang']['noinvoice']."</td><td> : </td><td><input type=text id=noinvoicecr class=myinputtext style=width:150px; ></td></tr>";
echo"<tr><td></td><td></td><td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td></tr>";
echo"</table></fieldset></td>
     </tr>
    </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset style='float:left;clear:right'><legend><b>Cetak</b></legend>&nbsp;&nbsp;<img class='zImgBtn' src='images/skyblue/excel.jpg' style='cursor:pointer' onclick=prexcel('sdm_slave_rekapinvoice.php','event') title='Print XLS'></fieldset>";
echo"<fieldset style='clear:left'><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable >";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['jenis']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['noinvoice']."</td>";
echo"<td>".$_SESSION['lang']['rute']."</td>";
echo"<td>".$_SESSION['lang']['jumlah']."</td>";
echo"<td>".$_SESSION['lang']['keterangan']."</td>";
echo"<td>".$_SESSION['lang']['dibuatoleh']."</td>";
echo"<td colspan=3>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div>";


echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table border=0 >";
echo"</tr>	
		<td>".$_SESSION['lang']['jenis']."</td>
		<td>:</td>
		<td><select id=jenisdata style=width:150px; >".$opttipe."</select></td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
        <td onclick=\"searchnotrans('".$_SESSION['lang']['find']."','<div id=formPencariandata></div>',event)\">
            <input type=text id=notransaksi class=myinputtext style=width:148px; readonly>
            <img src=\"images/onebit_02.png\" style='position:relative;top:3px;' class=\"resicon\" title='".$_SESSION['lang']['find']."'>
        </td>
	 </tr>"; 
echo"</tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>:</td>
        <td><input type=text id=periode disabled class=myinputtext style=width:148px;></td>
        <td>".$_SESSION['lang']['noinvoice']."</td>
        <td>:</td>
        <td onclick=\"searchnoinvoice('".$_SESSION['lang']['find']."','<div id=formnoinvoice></div>',event)\">
            <input type=text id=noinvoice class=myinputtext style=width:148px; readonly>
            <img src=\"images/onebit_02.png\" style='position:relative;top:3px;' class=\"resicon\" title='".$_SESSION['lang']['find']."'>
        </td>
     </tr>";
echo"<tr>
        <td>".$_SESSION['lang']['rute']."</td>
        <td>:</td>
        <td><input type=text id=rute class=myinputtext style=width:148px; ></td>
        <td>".$_SESSION['lang']['jumlah']."</td>
        <td>:</td>
        <td><input type=text id=jumlah disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=width:148px;></td>
     </tr>";
echo"<tr><td>".$_SESSION['lang']['keterangan']."</td>
        <td>:</td>
        <td colspan=4><textarea id=keterangan onkeypress=\"return tanpa_kutip(event);\" style=width:374px;></textarea></td>
     </tr>";
echo"<tr>
        <td></td><td></td>
        <td><button class=mybutton onclick=saveData()>".$_SESSION['lang']['save']."</button>&nbsp;
            <button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button>
        </td>
     </tr>
     <input type=hidden id=tanggalrute value=''/>
     <input type=hidden id=oldnotransaksi value=''/>
     <input type=hidden id=oldnoinvoice value=''/>
     <input type=hidden id=oldtanggalrute value=''/>
     <input type=hidden id=method value='insert'/>
     </table>";
echo"</fieldset>"; 
echo"</div>";
CLOSE_BOX();
echo close_body(); ?>
