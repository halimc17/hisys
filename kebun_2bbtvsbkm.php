<?//@Copy nangkoelframework//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
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


                        
                        
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "select distinct kodeorganisasi,namaorganisasi 
        from ".$dbname.".bibitan_mutasi c inner join ".$dbname.".organisasi a on left(c.kodeorg,4)=a.kodeorganisasi
        where tipe='KEBUN' order by namaorganisasi asc";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
{
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['kodeorganisasi']." - ".$data['namaorganisasi']."</option>";
}                        
include('master_mainMenu.php');
if($_SESSION['language']=='EN'){
OPEN_BOX('','<span class=judul>'.strtoupper('Seed delivery vs Planting').'</span><br>');
}else{
OPEN_BOX('','<span class=judul>'.strtoupper('Pengiriman bibit vs BKM').'</span><br>');	
}

#$arr="##tgl1##tgl2";	
$arr="##kdorg##tgl1##tgl2";

echo "<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>
    <tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td><select id=kdorg style=\"width:169px;\" >".$optOrg."</select></td>
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
		<button onclick=zPreview('kebun_slave_2bbtvsbkm','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_2bbtvsbkm.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";//<button onclick=zPdf('pabrik_slave_2hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['pdf']."</button>
CLOSE_BOX();
OPEN_BOX();
echo "
<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
<fieldset style='clear:both;max-width:1235px''><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset></div>";

CLOSE_BOX();
echo close_body();




?>