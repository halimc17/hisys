<?
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


function carikdpab(title,ev)
{
    content= "<div id=formkdpab style=\"height:250px;width:350;overflow:scroll;\"></div>";
    title='Add Transaction';
    height='250';
    width='350';
    showDialog1(title,content,width,height,ev);	
    getkdpab();
}

function getkdpab()
{
    param='method=getkdpab';
    tujuan = 'pabrikasi_slave_rab.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formkdpab').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}

function getlistkdpab()
{
    carilistkdpab=document.getElementById('carilistkdpab').value;
    param='method=getkdpab'+'&carilistkdpab='+carilistkdpab;
  
    tujuan = 'pabrikasi_slave_rab.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formkdpab').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}


function movedata(kdpab)
{
    document.getElementById('kdpab').value=kdpab;
    document.getElementById('listkdpab').style.display='none';
    closeDialog();	
}
    
</script>


<?
require_once('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('Pemakaian Barang').'</span><br>');

$optorg=$optper="<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}


$frm[0]='';
$frm[1]='';

$arr1 = "##kdorg##tgl1##tgl2##kdpab";
//$frm[0]="<fieldset style='float:left;'>
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:159px;\">" . $optorg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
                </tr>
				<tr>
					<td>Kode Pabrikasi</td>
					<td>:</td>
					<td>
						<input type=text id=kdpab   onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\">
					</td>
					<td><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblkdpab class=resicon onclick=carikdpab('".$_SESSION['lang']['find']."',event)></td>
				</tr> 
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('pabrikasi_slave_2pakaibarang','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'pabrikasi_slave_2pakaibarang.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1200px'; >
</div></fieldset>"; 


//$hfrm[0]=$_SESSION['lang']['tanggal'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
//drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();
?>