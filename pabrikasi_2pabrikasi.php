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

// function lihatDetail(kdpab,tipe,ev){
   // param='kdpab='+kdpab+'&tipe='+tipe;
   // tujuan='pabrik_slave_2biaya_detail.php'+"?"+param;  
   // tujuan='pabrikasi_slave_2pabrikasi_detail.php'+"?"+param;  
   // width='700';
   // height='400';
   // content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   // showDialog1('Detail Transaksi'+content,width,height,ev);     
// }


function lihatDetail(kdpab,tipe,ev){
   param='kdpab='+kdpab+'&tipe='+tipe;
   tujuan='pabrikasi_slave_2pabrikasi_detail.php'+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Detail Transaksi'+kdpab,content,width,height,ev);     
}
    
    
</script>


<?
require_once('master_mainMenu.php');

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Laporan Pabrikasi').'</span><br>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Manufacturing Report').'</span><br>');
}
$optorg=$optper="";
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

$arr1 = "##kdorg##tgl1##tgl2";
//$frm[0]="<fieldset style='float:left;'>
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:163px;\">" . $optorg . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' >
                    s/d
                    <input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' ></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('pabrikasi_slave_2pabrikasi','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'pabrikasi_slave_2pabrikasi.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1200px'; >
</div></fieldset>"; 


//$hfrm[0]=$_SESSION['lang']['tanggal'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
//drawTab('FRM',$hfrm,$frm,300,1150);	

CLOSE_BOX();
echo close_body();
?>