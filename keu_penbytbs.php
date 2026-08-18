<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');

?>
<script languange=javascript1.2>
pild="<?php echo $_SESSION['lang']['pilihdata'];?>";
</script>
<script language=javascript1.2 src='js/keu_penbytbs.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>

<!--deklarasi untuk option-->
<?php
$optJurnal=$optPabrik=$optunit="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
// $optregional.="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str="select * from ".$dbname.".organisasi where tipe='PABRIK' order by namaorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$arrReg=array();
while($bar=$res->fetch()){
    $optPabrik.="<option value='".$bar['kodeorganisasi']."'>" .$bar['namaorganisasi']. "</option>";
}
$dirJurnal=array("0"=>$_SESSION['lang']['belumposting'],"1"=>$_SESSION['lang']['posting']);
foreach($dirJurnal as $key=>$lstNm){
$optJurnal.="<option value='".$key."'>".$lstNm."</option>";
}
OPEN_BOX('','<span class=judul>Pengakuan Pembelian TBS</span>');
#HEADER UNTUK BUAT BARU SAMA LIST
echo"<div id=action_list>";//buka div
echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
	 
	 <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
	 
	echo"<table>";
	echo"
	<tr><td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unitMillCr style=\"width:135px;\">'".$optPabrik."'</select></td>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input style=\"width:80px;\" type='text' class='myinputtext' id='tglcari' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:60px; /></td>
        <td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td><select id=statJrnl style=\"width:135px;\">'".$optJurnal."'</select></td>
    </tr><td><td>
        <td colspan=3><button class=mybutton onclick=loaddata(0) >".$_SESSION['lang']['find']."</button></td>
    </tr>";
        echo "</table>";
echo"</fieldset></td>";

echo"
     </tr>
	 </table> "; 
echo "</div>";//tutup div
CLOSE_BOX();

?>



<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php 

echo"
<div id=listdata style=display:block>";//buka list data
OPEN_BOX();
    echo "
    <fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['unit']."</td>
					<td  align=center>".$_SESSION['lang']['notransaksi']."</td>
                    <td  align=center>".$_SESSION['lang']['namasupplier']."</td>
                    <td  align=center>".$_SESSION['lang']['tanggal']."</td>
                    <td  align=center>".$_SESSION['lang']['kgtbs']."</td>
                    <td  align=center>".$_SESSION['lang']['rp']." ".$_SESSION['lang']['supplier']."</td>
                    <td  align=center>".$_SESSION['lang']['rp']." ".$_SESSION['lang']['bonus']."</td>
                    <td  align=center>".$_SESSION['lang']['rp']." ".$_SESSION['lang']['pajak']." Ditanggung</td>
                    <td  align=center>".$_SESSION['lang']['notiftotalbayar']."</td>
                    <td  align=center>".$_SESSION['lang']['updateby']."</td>
                    <td  align=center>".$_SESSION['lang']['posted']."</td>
                    <td  align=center>".$_SESSION['lang']['action']."</td>    
                </tr>  
            </thead>
             <tbody id=contain> 
               
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
                 <script languange=javascript1.2>loaddata(0)</script>
	</fieldset>";

CLOSE_BOX();
echo "</div>";//tutup list data
?>

<!--UNTUK BUAT FORM INPUT HEADER-->
<?php

echo "<div id=header style=display:none>";//buka diff
OPEN_BOX();// 
echo "
<fieldset style=float:left;width:660px>
<legend>".$_SESSION['lang']['header']."</legend>
<table cellspacing=1 border=0>
    <tr>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input style=\"width:145px;\" type='text' class='myinputtext' id='tgl' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:70px; onchange=\"getpricetbs();	\" /></td>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=unit style=\"width:150px;\" onchange=getPabrik(0,0)>'".$optPabrik."'</select></td>
    </tr>
     <tr>
        <td>".$_SESSION['lang']['namasupplier']."</td>
        <td>:</td>
        <td><select id=suppId style=\"width:150px;\" onchange='getpricetbs()'>'".$optunit."'</select></td>
        <td>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']." </td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:150px;\" id=hrgkgAll onchange=itungSemua()></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['bonus']."/".$_SESSION['lang']['kg']." </td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' value='0' style=\"width:150px;\" id=bonuskgAll ></td>
        <td>Subsidi Angkut/".$_SESSION['lang']['kg']." </td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:150px;\" id=subsidiangkut onchange=itungSemua()></td>
    </tr>
	<tr hidden>
        <td >Beban Pajak PPh 22 </td>
        <td>:</td>
        <td><select id=bbnPajak style=\"width:150px;\"  onchange=itungSemua()> <option value='0'>".$_SESSION['lang']['tidak']."</option><option value='1'>Di Tanggung</option></selected></td>
        <td>".$_SESSION['lang']['persen']."</td>
        <td>:</td>
        <td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:150px;\" value=0.25 id=prsnAll  onchange=itungSemua()></td>
    </tr>
	<tr><td colspan=2></td>
		<td colspan=20>
			<button id=savehead class=mybutton onclick=savehead()>".$_SESSION['lang']['proses']."</button>
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
		</td>
		
	</tr>
</table>
</fieldset> <input type=hidden id=notransaksi value=''>";
CLOSE_BOX();
echo"</div>";
?>



<?php
echo "<div id=detail style=display:none>";//buka diff
OPEN_BOX();
echo"<div id='listdetail'> </div>";
CLOSE_BOX();
echo"</div>";
?>

<?php
echo close_body();			
?>