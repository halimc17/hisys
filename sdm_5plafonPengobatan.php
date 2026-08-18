<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('MEDICAL PLAFOND').'</span>');
?>
<?php
$optGolongan = '';
$str = "select * from " . $dbname . ". sdm_5golongan order by `kodegolongan` asc"; //echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

$optRegional = '';
$sReg = "select * from " . $dbname . ".bgt_regional order by regional asc";
$rReg=$owlPDO->query($sReg) or die(print " Gagal: ".PDOException::getMessage());
$rReg->setFetchMode(PDO::FETCH_OBJ);
while ($bReg = $rReg->fetch()) {
    $optRegional.="<option value='" . $bReg->regional . "'>" . $bReg->regional . "</option>";
}
?>
<script type="text/javascript" src="js/sdm_setup_plafond.js"></script>
<fieldset style="width:400px;">
    <table>
        <tr><td><?php echo $_SESSION['lang']['regional'] ?></td>
            <td><select id='regional' style='width:200px;'><?php echo $optRegional ?></select></td></tr>
        <tr><td><?php echo $_SESSION['lang']['levelcode'] ?></td><td>
                <?php
                while ($bar = $res->fetch()) {
                    $optGolongan.="<option value='" . $bar->kodegolongan . "'>" . $bar->namagolongan . " - " . $bar->kodegolongan . "</option>";
                }
                $optjenis = '';
                $str = "select * from " . $dbname . ".sdm_5jenisbiayapengobatan order by kode";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
                    $optjenis.="<option value='" . $bar->kode . "'>" . $bar->nama . "</option>";
                }
				
				$optsatuan='';
                $optjeniskelamin = "<option value=''></option>";
                $arrenum = getEnum($dbname, 'sdm_pengobatanplafond', 'satuan');
                foreach ($arrenum as $key => $val) {
                    if ($val == 1) {
                        $hVal = 'per tahun';
                    } else if ($val == 2) {
                        $hVal = 'per hari';
                    } else if ($val == 3) {
                        $hVal = '1 tahun sekali';
                    } else {
                        $hVal = '3 tahun sekali';
                    }
                    $optsatuan.="<option value='" . $key . "'>" . $hVal . "</option>";
                }
                ?>
                <select id="kodegolongan" name="kodegolongan" style='width:200px;'>
                <?php echo $optGolongan; ?>
                </select>
            </td></tr>
        <?
        echo"<tr><td>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
        <td><select id=jenisbiaya style='width:200px;'>".$optjenis."</select></td></tr>
        <tr><td>".$_SESSION['lang']['satuan']."</td>
        <td><select id=satuan style='width:200px;'>".$optsatuan."</select></td></tr>";
        ?>		 
        <tr>
            <td>
<?php echo $_SESSION['lang']['jumlah'] . '(' . $_SESSION['lang']['rupiah'] . ')' ?></td><td><input id="rupiah" style='width:200px;' name="rupiah" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text" value="0" style="width:95px" ></td></tr>
    </table>
    <input type='hidden' id='method' value='insert'>
    <button class='mybutton' onclick='simpanPlafon()'><?php echo $_SESSION['lang']['save'] ?></button>
    <button class='mybutton' onclick='cancelPlafon()'><?php echo $_SESSION['lang']['cancel'] ?></button>
</fieldset>
<?php echo open_theme($_SESSION['lang']['availavel']); ?>
<div>
<?php
$str1 = "select * from " . $dbname . ".sdm_pengobatanplafond order by regional asc, kodegolongan asc, kodejenisbiaya asc";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:650px;'>
	     <thead>
		 <tr class=rowheader>
		 <td>" . $_SESSION['lang']['regional'] . "</td>
		 <td>" . $_SESSION['lang']['levelcode'] . "</td>
		 <td>" . $_SESSION['lang']['jenisbiayapengobatan'] . "</td>
		 <td>" . $_SESSION['lang']['satuan'] . "</td>
		 <td>" . $_SESSION['lang']['jumlah'] . '(' . $_SESSION['lang']['rupiah'] . ')' . "</td>
		 <td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody id=container>";
?>
    <?php
    $vGolognan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
    $vJenisBiaya = makeOption($dbname, 'sdm_5jenisbiayapengobatan', 'kode,nama');
    while ($bar1 = $res1->fetch()) {
        if ($bar1->satuan == 1) {
            $hVal = 'per tahun';
        } else if ($bar1->satuan == 2) {
            $hVal = 'per hari';
        } else if ($bar1->satuan == 3) {
            $hVal = '1 tahun sekali';
        } else {
            $hVal = '3 tahun sekali';
        }
        echo"<tr class=rowcontent>
			<td>" . $bar1->regional . "</td>
			<td>" . $vGolognan[$bar1->kodegolongan] . " - " . $bar1->kodegolongan . "</td>
			<td>" . $vJenisBiaya[$bar1->kodejenisbiaya] . "</td>
			<td>" . $hVal . "</td>
			<td align=right>" . number_format($bar1->rupiah, 2) . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->regional . "','" . $bar1->kodegolongan . "','" . $bar1->rupiah . "','" . $bar1->kodejenisbiaya . "','" . $bar1->satuan . "');\"></td></tr>";
    }
    ?>

</tbody>
<tfoot>
</tfoot>
</table>
</div>

<?php
echo close_theme();
CLOSE_BOX();
echo close_body();
?>