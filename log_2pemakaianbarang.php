<?php
//@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript1.2 src="js/log_2pemakaianbarang.js?v=<?php echo time(); ?>" /></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>

<?php
##untuk pilihan pabrik 	
$optUnit=$optDivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$unitDetailAkses = getOrgDetail(2);
$iUnit="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$unitDetailAkses.")";
$nUnit=$owlPDO->query($iUnit) or die(print " Gagal: ".PDOException::getMessage());
$nUnit->setFetchMode(PDO::FETCH_ASSOC);
while($dUnit=$nUnit->fetch()){
    $optUnit.="<option value=".$dUnit['kodeorganisasi'].">".$dUnit['kodeorganisasi']." - ".$dUnit['namaorganisasi']."</option>";
}                 


$optBrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

## Pengambilan barang dari table barang
$optkelompok = "<option value=''></option>";
$optsearch = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select distinct(kodebarang) as kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang order by namabarang";
$res=fetchdata($str);
foreach($res as $val){
	$optBrg.="<option value='" . $val['kodebarang'] . "'>" . $val['namabarang'] . " (" . $val['kodebarang'] . ")</option>";
}

$datefirst = date("01-m-Y");
$datenow = date("d-m-Y");

			
?>


<?php
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('log_2pemakaianbarang').'</span><br>');

$arr="##unit##tgl1##tgl2##barang##divisi";	

echo "<fieldset style='float:left;'><legend>Form</legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['unit']."</td>
            <td>:</td>
            <td><select class='select2' id=unit style=\"width:163px;\" onchange=getdivisi()>".$optUnit."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['divisi']."</td>
            <td>:</td>
            <td><select class='select2' id=divisi style=\"width:163px;\" >".$optDivisi."</select></td>
        </tr>
	<tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' value='".$datefirst."' readonly>
            s/d
            <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' value='".$datenow."' readonly></td>
	</tr>
       
    <tr>
            <td>".$_SESSION['lang']['kodebarang']."</td>
            <td>:</td>
            <td>
            <select  class='select2' style='width:170px;' id=barang>" . $optBrg . "</select>
			<img id=kodebarang_find onclick=z.elSearch('barang',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
        </tr>

	
	<tr>
            <td><td><td>
            <button onclick=zPreview('log_slave_2pemakaianbarang','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
            <button onclick=zExcel(event,'log_slave_2pemakaianbarang.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
            <button onclick=batalLaporan() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
            </td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>

// if($proses=='excel'){
	// $vBorder = "border=1";
// }else{
	// $vBorder = "";
// }
CLOSE_BOX();
OPEN_BOX();
//echo"<legend><b>".$_SESSION['lang']['printArea']."</b></legend>";

echo"
 <div class='table-scroll' id=printContainer style='height:400px;overflow: auto;'></div>
 ";

// echo "

// <div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
// </div>";

CLOSE_BOX();
echo close_body();


?>