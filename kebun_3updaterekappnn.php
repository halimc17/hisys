<?//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>


maxf=0
sekarang=1;
function saveall(maxRow){     
	maxf=maxRow;
	loopsave(1,maxRow);
}


function loopsave(currRow,maxRow){
    tglinput=trim(document.getElementById('tglinput'+currRow).innerHTML);
    blokinput=trim(document.getElementById('blokinput'+currRow).innerHTML);
    hkinput=trim(document.getElementById('hkinput'+currRow).innerHTML);
    jjginput=trim(document.getElementById('jjginput'+currRow).innerHTML);
    bjrinput=trim(document.getElementById('bjrinput'+currRow).innerHTML);
   
	param='tglinput='+tglinput+'&blokinput='+blokinput+'&hkinput='+hkinput;
	param+="&proses=savedata"+'&jjginput='+jjginput+'&bjrinput='+bjrinput;
	tujuan = 'kebun_slave_3updaterekappnn.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row'+currRow).style.backgroundColor='cyan';
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row'+currRow).style.backgroundColor='red';
                   unlockScreen();
                }  else {
                    document.getElementById('row'+currRow).style.display='none';
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow) {
						alert('Done');
						batal();
                    } else {
                            loopsave(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
	
}

    
    
</script>

<?
require_once('master_mainMenu.php');

  OPEN_BOX('','<span class=judul>'.getMenu('kebun_3updaterekappnn').'</span><br>');


$optOrg="";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' order by namaorganisasi asc ";	
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}



$arr="##unit##tgl1##tgl2";	
echo"<fieldset style='float:left;'>
        <legend>Data</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:150px;\">".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td>
						<input type=text readonly class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /> s/d 
						<input type=text readonly class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' />
					</td>	
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('kebun_slave_3updaterekappnn','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";


CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both;max-width:1235px'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
</div></fieldset>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>
CLOSE_BOX();
echo close_body();					
?>