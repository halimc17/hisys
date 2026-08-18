<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript>
bahasa="<?php echo $_SESSION['lang']['pilihdata']; ?>";
</script>
<script language=javascript src='js/pajak_vatinvatout.js?v=<?php echo time(); ?>'></script>
<?php
include('master_mainMenu.php');
include('lib/zLib.php');


$optNpwp=$optSupplier=$optperiode=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal)
{
    // if (substr($kei,2,2)!='HO' and substr($kei,2,2)!='RO') {
    if (substr($kei,2,2)!='HO' and substr($kei,2,2)!='RO') {
    	continue;
    }
    $optunit.="<option value='".$kei."'>".$fal."</option>";
}


// echo $optunit;

#ambil list supplier
$str="select * from ".$dbname.".log_5supplier where status=1";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optSupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']." (".$bar['supplierid'].")</option>";
}

$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opttipe.="<option value=1>Vat In</option>";
$opttipe.="<option value=2>Vat Out</option>";
$opttipe.="<option value=3>Pembetulan Vat IN</option>";
$opttipe.="<option value=5>Pembetulan Vat Out</option>";

# ambil npwp
$str="select npwp from ".$dbname.".setup_org_npwp";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()){
    $optNpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('pajak_vatinvatout').'</span>');
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
                    <td>".$_SESSION['lang']['tipe']."</td>
                    <td>:</td>
                    <td><select id=tipe style=width:150px; onchange=getForm() >".$opttipe."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td> 
                    <td>:</td>
                    <td><select id=unit style=width:150px; onchange='getperiode()'>".$optunit."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['npwp']."</td>
                    <td>:</td>
                    <td><select id=npwp style=width:150px; >".$optNpwp."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td>
                        <input type=text class=myinputtext id=tanggaldari onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:60px; maxlength=10 /> s/d
                        <input type=text class=myinputtext id=tanggalsampai onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:60px; maxlength=10 />
                    </td>
                </tr>
                <tr>  
			        <td>".$_SESSION['lang']['periode']."</td>
			        <td>:</td>
			        <td><select id=periode style=width:150px; >".$optperiode."</select></td>
                </tr>
                <tr>  
                    <td>".$_SESSION['lang']['supplier']."/".$_SESSION['lang']['customer']."</td>
                    <td>:</td>
                    <td><select id=supplierId style=width:150px; disabled=disabled >".$optSupplier."</select>
                    <img id=supplierId onclick=z.elSearch('supplierId',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'> 
                    (*Aktif Jika Tipe Pembetulan</td>
                </tr>
                <tr>  
                    <td>".$_SESSION['lang']['noinvoice']."</td>
                    <td>:</td>
                    <td><input type=text id=noinvoiceId class=myinputtext onkeypress='return tanpa_kutip(event)' style='width:150px;' placeholder='Aktif Jika Tipe Pembetulan' disabled=disabled ></td>
                </tr>
                
                <tr><td colspan=2></td>
                    <td colspan=3>
                        <button class=mybutton onclick=simpan()>Preview</button>
                        <button class=mybutton onclick=displayFormInput()>Hapus</button>
                        <input type=hidden id=method value='insert'>
                    </td>
                </tr>
        	</table></fieldset></div>";

echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['periode']."</td>";
echo"<td>".$_SESSION['lang']['unit']."</td>";
echo"<td>".$_SESSION['lang']['npwp']."</td>";
echo"<td>Total Vat In</td>";
echo"<td>Total Vat Out</td>";
echo"<td>".$_SESSION['lang']['selisih']."</td>";
echo"<td colspan=3>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
CLOSE_BOX();

echo"<div id=formdetail style=display:none;>";
OPEN_BOX();
echo"<div id=formdetaildata style=display:none;>";
echo"</div>";
CLOSE_BOX();
echo"</div>";

echo close_body();					
?>