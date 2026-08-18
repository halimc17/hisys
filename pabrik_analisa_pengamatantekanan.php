<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_analisa_pengamatantekanan.js?v=<?php echo time(); ?>'></script>
<?php



include('master_mainMenu.php');

$sOrg = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where length (kodeorganisasi)=4";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) 
{
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$optOrg.="<option value='" . $rOrg['kodeorganisasi'] . "'>" . $rOrg['kodeorganisasi'] . "-" . $rOrg['namaorganisasi'] . "</option>";
	}else{
		if($_SESSION['empl']['lokasitugas']==$rOrg['kodeorganisasi']){
			$optOrg.="<option value='" . $rOrg['kodeorganisasi'] . "'>" . $rOrg['kodeorganisasi'] . "-" . $rOrg['namaorganisasi'] . "</option>";
		}
	}
}
$optPt=$optperiode=$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$optjenis.="<option value='BOILER'>Boiler</option>";
$optjenis.="<option value='BPV'>Bpv</option>";
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_analisa_pengamatantekanan').'</span>');

	 echo"<table>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
			<tr>
            <td>".$_SESSION['lang']['kodeorg']."</td>
            <td><select id=kodeorg1 name=kodeorg1  style=width:200px>".$optOrg ."</select></td>
    		</tr>
				<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td><input type=\"text\" class=\"myinputtext\" id=\"tanggal\" name=\"tanggal\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:82px;\" readonly/>
    		</td>

    </tr>";

echo"<tr><td><td>
<button class=mybutton onclick=priview1()>" . $_SESSION['lang']['find'] . "</button>

</td></td></tr></table>";
echo"</fieldset></td>
		<td>
		<fieldset><legend>" . $_SESSION['lang']['print'] . "</legend> 
        <table>
			<tr>
            <td>".$_SESSION['lang']['kodeorg']."</td>
            <td><select id=kodeorg name=kodeorg  style=width:200px>".$optOrg ."</select></td>
    		<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>
    			<input type=\"text\" class=\"myinputtext\" id=\"tanggal1\" name=\"tanggal1\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:82px;\" readonly/>
            <input type=\"text\" class=\"myinputtext\" id=\"tanggal2\" name=\"tanggal2\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:82px;\" readonly/>
            </td>
    </tr>
			";

echo"<tr><td><td>
<button class=mybutton onclick=excel(event,'pabrik_slave_analisa_pengamatantekanan.php')>Excel</button>
<button class=mybutton onclick=priview()>" . $_SESSION['lang']['preview'] . "</button>
</td></td></tr></table>";
echo"</fieldset></td>
     </tr>
	 </table> ";

CLOSE_BOX();
echo"<div id=listData style=display:block>"; //buka list data
OPEN_BOX();
 //<div style=overflow:scroll>
//<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%
	echo"
		<div id=contain>    
				<script>priview1()</script>
             </div>
	";

CLOSE_BOX();
echo "</div>"; //tutup list data
##UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>"; //buka diff
OPEN_BOX();
echo "
<fieldset style=float:left>
<legend>Header</legend>
<table cellspacing=1 border=0>
    <tr>
            <td>".$_SESSION['lang']['kodeorg']." :</td>
            
            <td><select id=kodeorght name=kodeorght  style=width:200px>".$optOrg ."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td><input type=\"text\" class=\"myinputtext\" id=\"tanggalht\" name=\"tanggalht\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:82px;\" readonly/>
    </tr>
	<tr>
	<td>".$_SESSION['lang']['jenis']."</td>
            <td><select id=jenis name=jenis  style=width:200px>".$optjenis ."</select></td>
	</tr>
		<tr>
		<td></td>
		<td>
			<input type=hidden id=method value='insert'>
			<input type='hidden' id='user_id' name='user_id' value='".$_SESSION['standard']['userid']."' />
			<button id='simpanht' class=mybutton onclick=simpan(this)>" . $_SESSION['lang']['save'] . "</button>

			<button class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
		</tr>

</table>

</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo" 
<div id=containerdetail>";
echo"</div>";


echo "<div id=container>";
CLOSE_BOX();

echo close_body();
?>