<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_modal.js'></script>

<?php

// $str="select kodeasset,namasset from ".$dbname.".sdm_daftarasset where kodeorg in (".$arrunit.") and status='1' and kodeasset not in (select kodeasset from ".$dbname.".keu_disposalasset where statuspersetujuan not in (0,2) and left(notransaksi,4) in (".$arrunit."))  order by namasset";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while ($bar=$res->fetch()) 
// {
//     $optasset.="<option value='".$bar['kodeasset']."'>".$bar['kodeasset']." - ".$bar['namasset']."</option>";
// }

$optbank=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal)
{
    if (substr($kei, 2,2)!='HO') {
        continue;
    }
    $optunit.="<option value='".$kei."'>".$fal."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_modal').'</span>');
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
                    <td colspan=3 align='center' bgcolor='#C0C0C0' style='font-weight: bold;'>Pemberi Modal</td>
                    <td width='50px'></td>
                    <td colspan=3 align='center' bgcolor='#C0C0C0' style='font-weight: bold;'>Penerima Modal</td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td> 
                    <td>:</td>
                    <td><select id=unitpemberi style=width:150px; onchange='getakun()'>".$optunit."</select></td>
                    <td width='50px'></td>
                    <td>".$_SESSION['lang']['unit']."</td> 
                    <td>:</td>
                    <td><select id=unitpenerima style=width:150px; onchange='getakun()'>".$optunit."</select></td>
                </tr>
                <tr>  
			        <td>".$_SESSION['lang']['namabank']."</td>
			        <td>:</td>
			        <td><select id=norekpemberi style=width:150px; onchange='getmatauang()'>".$optbank."</select></td>
                    <td width='50px'></td>
			        <td>".$_SESSION['lang']['namabank']."</td>
			        <td>:</td>
			        <td><select id=norekpenerima style=width:150px; onchange='getmatauang()'>".$optbank."</select></td>
                </tr>
	            <tr>
	            	<td>".$_SESSION['lang']['matauang']."</td>
			        <td>:</td>
					<td><input type=text id=matauangpemberi class=myinputtext style=width:148px; disabled></td>
                    <td width='50px'></td>
	                <td>".$_SESSION['lang']['matauang']."</td>
			        <td>:</td>
					<td><input type=text id=matauangpenerima class=myinputtext style=width:148px; disabled></td>
                </tr>
	            <tr>
			        <td>".$_SESSION['lang']['tanggal']."</td>
			        <td>:</td>
			        <td><input type=text class=myinputtext id=tanggalpemberi onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:148px; maxlength=10 /></td>
                </tr>
	            <tr>
	            	<td>".$_SESSION['lang']['total']."</td>
			        <td>:</td>
					<td><input type=text id=totalpemberi class=myinputtextnumber onkeyup=\"z.numberFormat('totalpemberi',2);gettotpenerima()\" style=width:148px; ></td>
                    <td width='50px'></td>
	                <td>".$_SESSION['lang']['total']."</td>
			        <td>:</td>
					<td><input type=text id=totalpenerima class=myinputtextnumber style=width:148px; disabled></td>
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
echo"<td>".$_SESSION['lang']['tanggal']."</td>";
echo"<td>".$_SESSION['lang']['unit']." Pemberi Modal</td>";
echo"<td>No. Rekening Pemberi modal</td>";
echo"<td>".$_SESSION['lang']['unit']." Penerima Modal</td>";
echo"<td>No. Rekening Penerima modal</td>";
echo"<td>".$_SESSION['lang']['jumlah']."</td>";
echo"<td>".$_SESSION['lang']['updateby']."</td>";
echo"<td colspan=3>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
CLOSE_BOX();
echo close_body();					
?>