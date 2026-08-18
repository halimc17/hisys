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
<script language=javascript src='js/kebun_alokasimandor.js'></script>
<?
require_once('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';

OPEN_BOX('','<span class=judul>'.getMenu('kebun_alokasimandor').'<br></span>');

$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="SELECT distinct periode FROM ".$dbname.".setup_periodeakuntansi where 
		tutupbuku=0 and kodeorg='".$_SESSION['empl']['lokasitugas']."' order by periode desc limit 10";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}
	
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
		kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}


$arr="##unit##per";	
$frm[0].="
	<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit style=\"width:150px;\">".$optorg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:150px;\">".$optper."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('kebun_slave_alokasimandor','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                     </td>
                </tr>
            </table>
</fieldset>";

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1175px'; >
</div></fieldset>";


##########tab2
// $frm[1].="
// <fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
// <div id='printContainer2' style='overflow:auto;height:400px;max-width:1175px'; >
// </div></fieldset>";


$frm[1].="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table cellpading=1 cellspacing=1 border=0 class=sortable style=width:50%>
            <thead>
                <tr class=rowheader>
                    <td  align=center>".$_SESSION['lang']['nourut']."</td>
                    <td  align=center>".$_SESSION['lang']['tanggal']."</td>
					<td  align=center>".$_SESSION['lang']['namakaryawan']."</td>
					<td  align=center>".$_SESSION['lang']['notransaksi']."</td>
                </tr>  
            </thead>
             <tbody id=contain> 
                <script>loaddata(0)</script>
             </tbody>
            <tfoot id=footData>
             </tfoot>
             </table>
                
	</fieldset>";

$hfrm[0]=$_SESSION['lang']['proses'];
$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,100,1190);	

CLOSE_BOX();
echo close_body();
				
?>