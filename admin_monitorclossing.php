<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('admin_monitorclossing').'</span><br>');
?>
<script>
	function showhide(id){
		nama = document.getElementsByName(id);
		for (var i = 0; i < nama.length; i++) {
			dis = nama[i].getAttribute("style");
			if(dis!=null && (dis.includes("display:none") || dis.includes("display:none;") || dis.includes("display: none;"))){
				if(nama[i]!=undefined){				
					nama[i].style.display="";
				}
			}else{
				if(nama[i]!=undefined){				
					nama[i].style.display="none";
				}
			}
		}
		if(nama.length==0){
			alert("Detail tidak tersedia.");
		}
	}
</script>
<script language=javascript src='js/zReport.js'></script>
<?
$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$s="";
	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
		#$s="selected";
	}
    $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}


$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $bar){
	$opttipe.="<option value=" . $bar['tipe']. ">".$bar['tipe']. "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}


$arr1 = "##prd##pt##tipe";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt  style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tipe'] . "</td>
                    <td>:</td>
                    <td><select id=tipe  style=\"width:164px;\">" .$opttipe . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=prd style=\"width:164px;\">" . $optper . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('admin_slave_monitorclossing','" . $arr1 . "','printContainer'); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'admin_slave_monitorclossing.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
                
				
            </table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo"<div class='table-scroll'>
	<div id='printContainer'></div>
</div>
";

echo"<div id='getdetail' style=display:none></div>";
CLOSE_BOX();
echo close_body();
?>