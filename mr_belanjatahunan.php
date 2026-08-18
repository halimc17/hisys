<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();



?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script language=javascript>
function batal()
{
	document.getElementById('kdPt').selectedIndex='0';
	getUnit();
	document.getElementById('kdUnit').selectedIndex='0';	
	document.getElementById('printContainer').innerHTML='';	
}

function getUnit()
{
	pt=document.getElementById('kdPt').options[document.getElementById('kdPt').selectedIndex].value;
	param='kdPt='+pt+'&proses=getUnit';
	tujuan="mr_slave_belanjatahunan.php";
	
	post_response_text(tujuan, param, respog);
	function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                  	document.getElementById('kdUnit').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function detail(tipe,belanja,tahun,kdPt,kdUnit,content,ev){
	width='550';
	height='530';
	title = " "+tipe + " ("+tahun+")"
	showDialog1(title,content,width,height,ev);
	param='proses=getDetail&tipe='+tipe+'&belanja='+belanja+'&tahun='+tahun+'&kdPt='+kdPt+'&kdUnit='+kdUnit;
	
	tujuan="mr_slave_belanjatahunan.php";
	
	post_response_text(tujuan, param, respog);
	function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                  	document.getElementById('formDetail').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function printDetailExcel(tipe,belanja,tahun,kdPt,kdUnit,ev){
	param='proses=getDetail&tipe='+tipe+'&belanja='+belanja+'&tahun='+tahun+'&kdPt='+kdPt+'&kdUnit='+kdUnit+'&print=excel';	
	tujuan="mr_slave_belanjatahunan.php";
	judul=" "+tipe+" ("+tahun+")";
	printFile(param,tujuan,judul,ev)
	// post_response_text(tujuan, param, respog);
	// function respog() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert('ERROR TRANSACTION,\n' + con.responseText);
                // } else {
                  	
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
</script>

<?



$optKlmpk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optKlmpk.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$optKlmpk2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

include('master_mainMenu.php');
OPEN_BOX();
$arr="##kdPt##kdUnit";	

echo "<fieldset style='float:left;'><legend><b>Laporan Belanja Tahunan</b></legend>
<table>
        <tr>
            <td>".$_SESSION['lang']['pt']."</td>
            <td>:</td>
            <td>
				<select id=kdPt style=width:150px onchange=\"getUnit()\">".$optKlmpk."</select>
			</td>
        </tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td>
			<select id=kdUnit style=width:150px>".$optKlmpk2."</select>
		</td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button onclick=zPreview('mr_slave_belanjatahunan','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
			<button onclick=zExcel(event,'mr_slave_belanjatahunan.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
			<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

// echo "
// <fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
// <div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
// </div></fieldset>";

CLOSE_BOX();

OPEN_BOX('','Result:');
echo"<div id='printContainer' style='overflow:auto;'>
	</div>";
CLOSE_BOX();
echo close_body();




?>