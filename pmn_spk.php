<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src='js/formTable.js'></script>
<script language=javascript src='js/pmn_spk.js?v=<?php echo time(); ?>'></script>
<!--deklarasi untuk option-->
<?php



// $nokontrak=$_GET['nokontrak'];
// $nokontraktampung=$_GET['nokontrak'];

$optbuyer = $optbarang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select * from " . $dbname . ".pmn_4customer  order by namacustomer asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optbuyer .= "<option value='" . $bar['kodecustomer'] . "'>" . $bar['namacustomer'] . "</option>";
}

$str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optbarang .= "<option value='" . $bar['kodebarang'] . "'>" . $bar['namabarang'] . "</option>";
}



?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
// echo"<div id=action_list>";//buka div
echo "<div>"; //buka div
OPEN_BOX('', '<span class=judul>' . getMenu('pmn_spk') . '</span>');
echo "<table>
     <tr valign=middle>
	 <td align=center style='width:100px;cursor:pointer;' hidden onclick=newdata()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
echo "<table>";
echo "
	
			<tr>
				<td>" . $_SESSION['lang']['NoKontrak'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=nokontraksch size=50 class=myinputtext style=\"width:150px;\">
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['Pembeli'] . "</td>
				<td>:</td>		
				<td>
					<select id=kodecustomersch style=\"width:154px;\">'" . $optbuyer . "'</select>
				</td>
			</tr>	
			<td></td>
			<td></td>
            <td colspan=3><button class=mybutton onclick=loaddata(0) >" . $_SESSION['lang']['find'] . "</button></td>
        </tr>
	";
echo "</table>";
echo "</fieldset></td>";
echo "
     </tr>
	 </table> ";
CLOSE_BOX();
echo "</div>"; //tutup div
?>
<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
<?php
echo "
<div id=listdata style=display:block>"; //buka list data
OPEN_BOX();
echo "
    
            <table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead>
                <tr class=rowheader>
                    <th  align=center>" . $_SESSION['lang']['nourut'] . "</th>
                    <th  align=center>" . $_SESSION['lang']['NoKontrak'] . "</th>
                    <th  align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                    <th  align=center>" . $_SESSION['lang']['Pembeli'] . "</th>
                    <th  align=center>" . $_SESSION['lang']['action'] . "</th>    
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
	</fieldset>";
CLOSE_BOX();
echo "</div>"; //tutup list data
?>




<!--UNTUK BUAT FORM INPUT HEADER-->
<?php
echo "<div id=header style=display:none>";
OPEN_BOX();

echo "<fieldset>
<legend><b>Header</b></legend>
<table cellspacing=1 border=0>
    <tr>
		<td>" . $_SESSION['lang']['NoKontrak'] . "</td>
		<td>:</td>		
		<td>
			<input type=text id=nokontrak size=20 disabled class=myinputtext style=\"width:150px;\">
			<img src=images/zoom.png title='" . $_SESSION['lang']['find'] . "' id=tombolnokontrak class=resicon onclick=carinokontrak('" . $_SESSION['lang']['find'] . "',event)>
		</td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['kodept'] . "</td>
		<td>:</td>		
		<td>
			<input type=text id=kodept size=20 disabled class=myinputtext style=\"width:150px;\">
		</td>
	</tr>
	  <tr>
		<td>" . $_SESSION['lang']['tanggal'] . "</td>
		<td>:</td>		
		<td>
			<input type=text id=tanggal size=20 disabled class=myinputtext style=\"width:150px;\">
		</td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['Pembeli'] . "</td>
		<td>:</td>		
		<td>
			<select id=kodecustomer disabled style=\"width:155px;\">'" . $optbuyer . "'</select>
		</td>
	</tr>	
	<tr>
		<td>" . $_SESSION['lang']['komoditi'] . "</td>
		<td>:</td>		
		<td>
			<select id=kodebarang disabled style=\"width:155px;\">'" . $optbarang . "'</select>
		</td>
	</tr>
	
	
	
	<tr><td colspan=2></td>
		<td colspan=20>
			<button hidden id=proses class=mybutton onclick=proses()>" . $_SESSION['lang']['proses'] . "</button>
			<button id=batal class=mybutton onclick=displaylist()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
</table>
</fieldset>";/*
			<button id=batal class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>*/
CLOSE_BOX();
echo "</div>";
?>
<?php
echo "<div id=detailhead style=display:none>"; //buka diff
OPEN_BOX();
echo "<div id=detail style=display:none>"; //buka diff
echo "</div>";
CLOSE_BOX();
echo "</div>";
echo close_body();		////<input type=hidden id=method value='insert'>	
?>