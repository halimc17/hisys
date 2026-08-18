<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_2rekapgajipt').'</span>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript>
function getinfo(){
alert('Jika jumlah Orang / TK selisih antara rekap per unit dan rekap per PT,\nhal tersebut disebabkan jika ada terjadi contoh kasus :\n\nRekap per unit =\nBudi bekerja di PT : A dan unit C = 1 TK = 10 HK\nBudi bekerja di PT : A dan unit D = 1 TK = 5 HK\nsehingga jika dijumlahkan dalam PT menjadi 2 TK dan 15 HK\n\nnamun pada rekap PT akan di hitung menjadi 1 TK dan 15 HK dikarenakan orang yg sama.');
	
}

</script>
<?
$optPT=$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT.= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'",'2','0',true);

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optprd.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$frm[0]='';
$frm[1]='';

$arr1 = "##pt##kdorg##prd";
$frm[0]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getEstate()  style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=prd style=\"width:164px;\">" . $optprd . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4 valign=center>
                    <button onclick=zPreview('sdm_slave_2rekapgajipt','" . $arr1 . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'sdm_slave_2rekapgajipt.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<img src=images/info.png class=resicon onclick=getinfo() caption='Info'>
                    </td>
                </tr>
            </table>
</fieldset>
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1200px'; >
</div></fieldset>";

$arr2 = "##pt2##prd2";
$frm[1]="<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt2 style=\"width:164px;\">" .$optPT . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select id=prd2 style=\"width:164px;\">" . $optprd . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('sdm_slave_2rekapgajiptV2','" . $arr2 . "','printContainerV2') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'sdm_slave_2rekapgajiptV2.php','" . $arr2 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
					<img src=images/info.png class=resicon onclick=getinfo() caption='Info'>
                    </td>
                </tr>
            </table>
</fieldset>
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['printArea'] . "</b></legend>
<div id='printContainerV2' style='overflow:auto;height:400px;max-width:1200px'; >
</div></fieldset>";
$hfrm[0]=$_SESSION['lang']['unit'];
$hfrm[1]=$_SESSION['lang']['pt'];

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,1235);	

CLOSE_BOX();
echo close_body();
?>