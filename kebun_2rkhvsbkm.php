<?//@Copy nangkoelframework//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/kebun_2rkhvsbkm.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script language=javascript>


	function batal(){
		document.getElementById('kdorg').value='';
		document.getElementById('tgl2').value='';	
		document.getElementById('tgl1').value='';
		document.getElementById('printContainer').innerHTML='';	
	}


</script>

<?


$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);                        


$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {

$sql = "select distinct kodeorganisasi,namaorganisasi 
        from ".$dbname.".organisasi 
        where tipe='KEBUN' order by namaorganisasi asc";

}
else
{

$sql = "select distinct kodeorganisasi,namaorganisasi 
        from ".$dbname.".organisasi 
        where kodeorganisasi='".$idOrg."' and tipe='KEBUN' order by namaorganisasi asc";	

}

$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
{
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['kodeorganisasi']." - ".$data['namaorganisasi']."</option>";
}                      
$optdiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "select distinct kodeorganisasi,namaorganisasi 
        from ".$dbname.".organisasi 
        where induk='".$idOrg."' and (tipe='AFDELING' or tipe='BIBITAN') order by kodeorganisasi asc";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
{
	$optdiv.="<option value=".$data['kodeorganisasi'].">".$data['kodeorganisasi']." - ".$data['namaorganisasi']."</option>";
}                    
include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
OPEN_BOX('','<span class=judul>'.strtoupper('Daily work program vs Planting').'</span><br>');
}else{
OPEN_BOX('','<span class=judul>'.strtoupper('Rencana Kerja Harian vs BKM').'</span><br>');	
}

#$arr="##tgl1##tgl2";	
$arr="##kdorg##tgl1##tgl2##kddiv";

echo "<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=kdorg style=\"width:169px;\" onchange='changediv()'>".$optOrg."</select></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=kddiv style=\"width:169px;\" >".$optdiv."</select></td>
    </tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10' >
		s/d
		<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
	</tr>	

	
	<tr>
		<td></td><td></td><td>
		<button onclick=zPreview('kebun_slave_2rkhvsbkm','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_2rkhvsbkm.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>
CLOSE_BOX();
OPEN_BOX();
echo "
<div id='printContainer' class='table-scroll'></div>";

CLOSE_BOX();
echo close_body();




?>