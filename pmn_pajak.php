<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/pmn_pajak.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
include('master_mainMenu.php');			
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optCariPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PT' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch())
{
        $optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
        $optCariPt.="<option value='".$data['kodeorganisasi']."'>".$data['namaorganisasi']."</option>";
}
$optsup=$optFaktur="";
$ha="SELECT namasupplier,`supplierid`,`kodetimbangan` FROM ".$dbname.".log_5supplier WHERE status='1' and kodekelompok='S004' order by namasupplier asc";
$hi=$owlPDO->query($ha) or die(print " Gagal: ".PDOException::getMessage());
$hi->setFetchMode(PDO::FETCH_ASSOC);
while ($hu=$hi->fetch())
{
        $optsup.="<option value=".$hu['supplierid'].">".$hu['namasupplier']."</option>";
}

$optJenis="<option value=''></option>";	
OPEN_BOX('','<span class=judul>'.strtoupper('Faktur Pajak').'</span><br>');

echo"<fieldset style='width:400px;'>
                <legend>".$_SESSION['lang']['entryForm']."</legend> 
                        <table border=0 cellpadding=1 cellspacing=1>
                                <tr>
                                    <td>PT</td>
                                    <td>:</td>
                                    <td><select id=pt onchange=getFaktur() style=\"width:150px;\">".$optOrg."</select></td>
                                </tr>
                                <tr>
                                    <td>No. Faktur</td>
                                    <td>:</td>
                                    <td><select id=faktur  style=\"width:150px;\">".$optFaktur."</select></td>
                                </tr>
                                <tr>
                                    <td>No. Invoice</td>
                                    <td>:</td>
                                    <td><input type=text id=invoice name=nopajak onkeypress=return tanpa_kutip(event); class=myinputtext style=width:145px; /></td>
                                </tr>
                                <tr>
                                    <td style='display:none'>".$_SESSION['lang']['jenis']."</td>
                                    <td style='display:none'>:</td>		
                                    <td style='display:none'><select id=jenis  style=\"width:150px;\">".$optJenis."</select></td>
                                </tr>

                                <tr>
                                    <td>Kurs Pajak</td>
                                    <td>:</td>
                                    <td><input type=text value=1 id=kurs name=kurs onkeypress=return tanpa_kutip(event); class=myinputtext style=width:50px; /></td>
                                </tr>

                                <tr>
                                    <td></td><td></td>
                                    <td><button class=mybutton onclick=simpan()>Simpan</button>
                                    <button class=mybutton onclick=hapus()>Batal</button></td>
                                </tr>
                        </table></fieldset>
                                        <input type=hidden id=method value='insert'>";
CLOSE_BOX();

OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset >
                <table>
                        <tr>
                                <td>".$_SESSION['lang']['perusahaan']."</td>
                                <td><select id=cariPt>".$optCariPt."</select></td>
                                <td><button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button></td>
                        </tr>
                </table>
                <legend>".$_SESSION['lang']['list']."</legend>
                <div id=container> 
                        <script>loadData()</script>
                </div>
        </fieldset>";
CLOSE_BOX();
echo close_body();									
?>