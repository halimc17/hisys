<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/kebun_5getpokokreport.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>"></script>
<?

// $optKeg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $sKeg="SELECT kodekegiatan, namakegiatan, kelompok FROM $dbname.`setup_kegiatan` WHERE kelompok IN ('TM','TBM','TB') ORDER BY kodekegiatan asc";
// $rKeg=$owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
// $rKeg->setFetchMode(PDO::FETCH_OBJ);
// while($bKeg=$rKeg->fetch()){
// 	$optKeg.="<option value='".$bKeg->kodekegiatan."'>".$bKeg->kodekegiatan." - ".$bKeg->namakegiatan." (".$bKeg->kelompok.")</option>";	
// }

$optstatus="";
$arrstatus = array("1" => "Aktif","0"=>"Non Aktif");
foreach ($arrstatus as $key => $bar) {
    $optstatus.="<option value='".$key."'>".$bar."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5getpokokreport').'</span>');
echo"<div>
    <fieldset>
        <legend>".$_SESSION['lang']['form']."</legend>
        <table style='display: inline-block;vertical-align:top'>
            <tr>
                <td>".$_SESSION['lang']['namalaporan']."</td><td>:</td>
                <td>
                    <input type=text class=myinputtext id=namalaporan style='width:145px;' maxlength=255 
                    onkeypress=\"return tanpa_kutip(event)\" onkeydown=\"upperCaseF(this)\">
                    <input type=hidden id=idlaporan>
                </td>
            </tr>
            <tr>
                    <td>".$_SESSION['lang']['status']."</td> 
                <td>:</td>
                <td>
                    <input type=checkbox id=status checked>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>
                    <input type=hidden value=insert id=proses>
                    <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                    <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                </td>
            </tr>
        </table>
        <table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
            <tr>
                <td>
                    <fieldset>
                        <legend>".$_SESSION['lang']['keterangan']."</legend>
                        Status :<br>
                        &nbsp;- Aktif : Centang CheckBox <input type='checkbox' checked disabled><br>
                        &nbsp;- Tidak Aktif : Uncentang CheckBox <input type='checkbox' disabled>
                    </fieldset>
                </td> 
            </tr>
        </table>
            <table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
            <tr>
                <td>
                    <fieldset>
                        <legend>".$_SESSION['lang']['info']."</legend>
                        &nbsp;Setup Ini berfungsi sebagai penentu kegiatan mana saja<br>
                        &nbsp;yang akan memakai satuan pokok untuk melihat data pokok<br>
                        &nbsp;di laporan kebun.<br>
                    </fieldset>
                </td> 
            </tr>
        </table>
    </fieldset>
</div>";

echo "<div id='contDetailView' style='display:none;'>";
echo "</div>";
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=width:350px;float:left;><legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata(0)</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();
?>