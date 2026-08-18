<?//@Copy nangkoelframework//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/pabrik_perbaikan.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?
$frm[0]='';
$frm[1]='';
##untuk pilihan pabrik 	
$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPabrik="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' ";
$nPabrik=$owlPDO->query($iPabrik) or die(print " Gagal: ".PDOException::getMessage());
$nPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($dPabrik=$nPabrik->fetch()){
    $optPabrik.="<option value=".$dPabrik['kodeorganisasi'].">".$dPabrik['namaorganisasi']."</option>";
}
$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";                      
			
?>


<?
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';


OPEN_BOX('','<span class=judul>'.strtoupper('MILL MAINTENANCE REPORT').'</span>');
$arr="##pabrik##station##tgl1##tgl2";	
$arrv="##pabrikv##stationv##tgl1v##tgl2v";	

$frm[0].="<fieldset style='float:left;'><legend><b>Form I</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=pabrik onchange=getStation() style=\"width:162px;\" >".$optPabrik."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['station']."</td>
            <td>:</td>
            <td><select id=station style=\"width:162px;\">".$optStation."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
            s/d
            <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
	</tr>	

	<tr>
		<td colspan=100></td>
	</tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('pabrik_slave_2perbaikan','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2perbaikan.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>

		<button onclick=batalLaporan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

$frm[0].="
<div id='printContainer' style='width:1250px;'>
</div>";




###form input II
################


$frm[1].="<fieldset style='float:left;'><legend><b>Form II</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pabrik']."</td>
            <td>:</td>
            <td><select id=pabrikv onchange=getStationv() style=\"width:162px;\" >".$optPabrik."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['station']."</td>
            <td>:</td>
            <td><select id=stationv style=\"width:162px;\">".$optStation."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1v' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
            s/d
            <input type='text' class='myinputtext' id='tgl2v' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
	</tr>	

	<tr>
		<td colspan=100></td>
	</tr>
	<tr>
		<td><td><td>
		<button onclick=zPreview('pabrik_slave_2perbaikan_v2','".$arrv."','printContainerv') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'pabrik_slave_2perbaikan_v2.php','".$arrv."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>

		<button onclick=batalLaporan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

$frm[1].="
<div id='printContainerv' style='width:1250px;'>
</div>";


$hfrm[0]=$_SESSION['lang']['laporan'].' I';
$hfrm[1]=$_SESSION['lang']['laporan'].' II';

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1240);	

CLOSE_BOX();
echo close_body();


?>