<? //@Copy nangkoelframework
?>
<? //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pmn_5feepembeliantbs.js?v=<?php echo time(); ?>'></script>

<?php
$optunit = $opttipe = $optsupplier = $optnoakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
// $arrunit = array();
// $arrunit = getOrgDetail(13);
$str = "SELECT DISTINCT a.kodeunit, b.namaorganisasi FROM ".$dbname.".pmn_5hargabelitbs a
        JOIN organisasi b ON a.kodeunit = b.kodeorganisasi ORDER BY b.namaorganisasi ASC";
$res = fetchdata($str);
foreach ($res as $key=>$val) {
    $optunit .= "<option value='".$val['kodeunit']."'>".$val['namaorganisasi']."</option>";
}

$str = "SELECT noakun, namaakun FROM ".$dbname.".keu_5akun
        WHERE level = '5' AND (noakun LIKE '2%' OR noakun LIKE '6%' OR noakun LIKE '7%' OR noakun LIKE '8%')
        ORDER BY noakun ASC";
$res = fetchdata($str);
foreach ($res as $key=>$val) {
    $optnoakun .= "<option value='".$val['noakun']."'>[".$val['noakun']."] ".$val['namaakun']."</option>";
}

$str = "SELECT DISTINCT tipe FROM ".$dbname.".pmn_5hargabelitbs ORDER BY tipe ASC";
$res = fetchdata($str);
foreach ($res as $key=>$val) {
    $opttipe .= "<option value='".$val['tipe']."'>".$val['tipe']."</option>";
}

$str = "SELECT DISTINCT a.supplier, b.namasupplier FROM ".$dbname.".pmn_5hargabelitbs a
        JOIN log_5supplier b ON a.supplier = b.supplierid ORDER BY b.namasupplier ASC";
$res = fetchdata($str);
foreach ($res as $key=>$val) {
    $optsupplier .= "<option value='".$val['supplier']."'>(".$val['supplier'].") ".$val['namasupplier']."</option>";
}


OPEN_BOX('', '<span class=judul>' . getMenu('pmn_5feepembeliantbs') . '</span>');

echo"<table>
    <tr>
    <td valign=top  width=60%>";

    echo "<fieldset>";
    echo "<legend>" . $_SESSION['lang']['form'] . "</legend>";
    echo "<table border=0 cellpadding=1 cellspacing=1>
            <tr>
                <td>" . $_SESSION['lang']['unit'] . "</td>
                <td>:</td>
                <td><select id=pt style=\"width:125px;\" onchange=getTipe();>" . $optunit . "</select>&nbsp;</td>

                <td>" . $_SESSION['lang']['periode'] . "</td>
                <td>:</td>
                <td>
                    <input type=text class=myinputtext id=tanggaldari readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:125px;/>
                    <input type=hidden class=myinputtext id=tanggalsampai name=tanggalsampai  readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:62px; value=00-00-0000>&nbsp;    
                </td>

                 <td>" . $_SESSION['lang']['persenppn'] . "</td>
                <td>:</td>      
                <td><input type=text id=persenppn style=\"width:120px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('persenppn',2)\" onkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/>&nbsp;</td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['unit'] . " " . $_SESSION['lang']['alokasi'] . "</td>
                <td>:</td>
                <td><select id=alokasi style=\"width:125px;\"></select>&nbsp;</td>

                <td>" . $_SESSION['lang']['batasbawah'] . " (Kg)</td>
                <td>:</td>      
                <td><input type=text id=batasbawah style=\"width:125px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('batasbawah',2)\" onkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/>&nbsp;</td>

				 <td>" . $_SESSION['lang']['rekening'] . "</td>
                <td>:</td>      
                <td><select id=rekening style=\"width:125px;\"></select>&nbsp;</td>
               
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['tipe'] . " TBS</td>
                <td>:</td>
                <td><select id=tipetbs style=\"width:125px;\" onchange=getSup()>&nbsp;</select>
                </td>            

                <td>" . $_SESSION['lang']['batasatas'] . " (Kg)</td>
                <td>:</td>      
                <td><input type=text id=batasatas style=\"width:125px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('batasatas',2)\" onkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/>&nbsp;</td>

				 <td>" . $_SESSION['lang']['noakundebet'] . "</td>
                <td>:</td>      
                <td>
                    <select id=debet style=\"width:125px;\">" . $optnoakun . "</select>
                    <img id=debet onclick=z.elSearch('debet',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
                </td>
				
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['supplier'] . "</td>
                <td>:</td>
                <td><select id=supplier style=\"width:125px;\" onchange=getRek()></select>
                <img id=supplier onclick=z.elSearch('supplier',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
                </td>

                <td>" . $_SESSION['lang']['rpperkg'] . "</td>
                <td>:</td>      
                <td><input type=text id=rpkg style=\"width:125px;\" class=myinputtextnumber onkeyup=\"z.numberFormat('rpkg',2)\" onkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/>&nbsp;</td>
                
				
                <td>" . $_SESSION['lang']['noakunkredit'] . "</td>
                <td>:</td>      
                <td>
                    <select id=kredit style=\"width:125px;\">" . $optnoakun . "</select>
                    <img id=kredit onclick=z.elSearch('kredit',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button class=mybutton onclick=simpan()>Simpan</button>
                    <button class=mybutton onclick=cancel()>Hapus</button>&nbsp;
                </td>
            </tr>

        </table>
    </fieldset>
    <input type=hidden id=method value='insert'>
    <input type=hidden id=notransaksi>";

echo"</td>
    <td valign=top  width=40%>";
    echo"<fieldset>
        <legend>Form Copy Data</legend>
        <table>
            <tr>
                <td>" . $_SESSION['lang']['unit'] . "</td>
                <td>:</td>
                <td><select id=ptcopy style=\"width:125px;\" onchange=getTipecopy();>" . $optunit . "</select></td>

                <td>" . $_SESSION['lang']['periode'] . " Dari</td>
                <td>:</td>
                <td>
                    <input type=text class=myinputtext id=tanggaldaricopy readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:130px;/>
                </td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['unit'] . " " . $_SESSION['lang']['alokasi'] . "</td>
                <td>:</td>
                <td><select id=alokasicopy style=\"width:125px;\"></select></td>

                <td valign=top>" . $_SESSION['lang']['periode'] . " Copy</td>
                <td>:</td>
                <td>
                    <input type=text class=myinputtext id=periodecopy readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:130px;/>
                </td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['tipe'] . " TBS</td>
                <td>:</td>
                <td><select id=tipetbscopy style=\"width:125px;\" onchange=getSupcopy();></select>
                </td>     
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['supplier'] . "</td>
                <td>:</td>
                <td><select id=suppliercopy style=\"width:125px;\"></select>
                <img id=suppliercopy onclick=z.elSearch('suppliercopy',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button class=mybutton onclick=simpancopy()>Simpan</button>
                    <button class=mybutton onclick=cancelcopy()>Hapus</button>&nbsp;
                </td>
            </tr>
        </table>
        </fieldset>";

echo"</td>
    </tr>
    </table>";

CLOSE_BOX();
?>

<?php
OPEN_BOX();
echo"<fieldset style=float:left;>
        <legend>".$_SESSION['lang']['find']."</legend>
        <table>
            <tr>
                <td>".$_SESSION['lang']['unit']."</td>
                <td>:</td>
                <td><select id=cariunit style=\"width:155px;\" >" . $optunit . "</select>&nbsp;</td>

                <td>".$_SESSION['lang']['tipe']." TBS</td>
                <td>:</td>
                <td><select id=caritipe style=\"width:155px;\">" . $opttipe . "</select>&nbsp;</td>

                <td>".$_SESSION['lang']['supplier']."</td>
                <td>:</td>
                <td>
                    <select id=carisupp style=\"width:155px;\">" . $optsupplier . "</select>
                    <img id=carisupp onclick=z.elSearch('carisupp',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
                </td>

                <td>".$_SESSION['lang']['status']."</td>
                <td>:</td>
                <td>
                    <select id=caristatus style=\"width:155px;\">
                        <option value=''>".$_SESSION['lang']['pilihdata']."</option>
                        <option value='0'>Belum di Posting</option>
                        <option value='9'>Menunggu Persetujuan</option>
                        <option value='1'>Disetujui</option>
                        <option value='3'>Ditolak</option>
                    </select>&nbsp;
                </td>

                <td><button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button></td>
            </tr>
        </table>
    </fieldset><div style=clear:both></div>";
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "
		<div id=container style='height:60vh' class='table-scroll'>
			<script>loadData(0)</script>
		</div>
	";
CLOSE_BOX();
echo close_body();
?>