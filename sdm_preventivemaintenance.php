<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="application/javascript" src="js/sdm_preventivemaintenance.js"></script>
<script>
    tombolsimpan = '<?php echo $_SESSION['lang']['save'] ?>';
    tombolbatal = '<?php echo $_SESSION['lang']['cancel'] ?>';
    tomboldone = '<?php echo $_SESSION['lang']['selesai'] ?>';

</script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['prevmain']).'</span>');
?>
<input type="hidden" id="proses" name="proses" value="insert" />
<div id="action_list">
    <?php
    echo"<table>
    <tr valign=middle>
    <td align=center style='width:100px;cursor:pointer;' onclick=tambahdata()>
        <img class=delliconBig src=images/newfile.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
    <td align=center style='width:100px;cursor:pointer;' onclick=tampildata()>
        <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
    <td align=center style='width:100px;cursor:pointer;' onclick=overdueData()>
        <img class=delliconBig src=images/book_icon.gif title='Over Due'><br>Over Due List</td>";

    echo"</tr>
</table>";
    ?>
</div>
<?php
CLOSE_BOX();
?>

<div id="listdata">
    <script>tampildata();</script>
</div>

<div id="header" style="display:none">    
    <?php
    OPEN_BOX();
    // get jenis dari schedulerht
    $optjenis = '';
    $arrjenis = getEnum($dbname, 'schedulerht', 'jenis');
    foreach ($arrjenis as $kei => $fal) {
        $optjenis.="<option value='" . $kei . "'>" . $fal . "</option>";
    }
    $optsatuan = "<option value='HM'>HM</option><option value='KM'>KM</option>";
    ?>
    <fieldset>
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td style="width:100px;"><?php echo $_SESSION['lang']['jenis'] ?></td>
                <td>:<input type="hidden" id="id" name="id" value=""></td>
                <td><select id="jenis" name="jenis" style="width:150px;" onchange="loadkodemesin()"><option value=""><?php echo $_SESSION['lang']['pilihdata'] ?></option><?php echo $optjenis; ?></select></td>
                <td rowspan="10" valign="top"><fieldset style="width:350px"><legend>Eg:</legend>
                <?
                if($_SESSION['language']=='EN'){
                        echo "
                        For a truck that routine maintenance on each 5000Km, 
                        Fullfill the form with: Type=TRAKSI, Engine=[Choose Vehicle],UOM=KM,Upperlimit=5000
                        Preventive Main. =4500, Iteration=yes.
                        <p>For general purpose reminder: Type=UMUM,Engine =[ignored],Uom=[ignored],upper limit=[ignored],Preventive Main.=[ignored],
                            Every Date=[choose what date], Iteration=no.</p>";
                }else{

                        echo "Untuk ganti oli kendaraan, akan diganti oli setiap 5000Km, 
                        maka cara pengisian adalah:jenis=TRAKSI,Nama Mesin =[Pilih Kode Mesin/Kendaraan],Satuan=KM,Batas Atas=5000,
                        Peringatan Setiap=4500, iterasi=ya.
                        <p>Untuk reminder akhir kontrak karyawan: jenis=UMUM,Nama Mesin =[],Satuan=[],Batas Atas=[],Peringatan Setiap=[],
                        setiap tanggal=[pilih tanggal], Perulangan=tidak.</p>";            
                }
                ?>
                </fieldset>
                </td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['nmmesin'] ?></td>
                <td>:</td>
                <td><select id="mesin" name="mesin" style="width:150px;"><option value=""><?php echo $_SESSION['lang']['pilihdata'] ?></option></select></td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['satuan'] ?></td>
                <td>:</td>
                <td><select id="satuan" name="satuan" style="width:150px;"><option value=""><?php echo $_SESSION['lang']['pilihdata'] ?></option><?php echo $optsatuan; ?></select></td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['batasatas'] ?></td>
                <td>:</td>
                <td><input type="text" class="myinputtextnumber" id="atas" name="atas" onkeypress="return angka_doang(event);" value=0 maxlength="10" style="width:150px;" /></td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['peringatansetiap'] ?></td>
                <td>:</td>
                <td><input type="text" class="myinputtextnumber" id="peringatan" name="peringatan" onblur="cekperingatan();" onkeypress="return angka_doang(event);" value=0 maxlength="10" style="width:150px;" /> <?php echo "(" . $_SESSION['lang']['dari'] . " " . $_SESSION['lang']['batasatas'] . ")"; ?></td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['setiap'] . " " . $_SESSION['lang']['tanggal'] ?></td>
                <td>:</td>
                <td><input type="text" class="myinputtext" id="tanggal" name="tanggal" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" style="width:150px;" disabled="true"/>(
                <?    
                if($_SESSION['language']=='EN'){
                   echo"If upper limit !=0, date will be ignored";
                }else{
                   echo"Jika batas atas tidak 0 maka tanggal diabaikan";
                }
                ?>)</td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['perulangan'] ?></td>
                <td>:<input type="hidden" id="id2" name="id2" value=""></td>
                <td><select id="sekali" name="sekali" style="width:150px;" >
                        <option value="1">Yes</option><option value="2">No</option></select></td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['namatugas'] ?></td>
                <td>:</td>
                <td><input type="text" class="myinputtext" id="tugas" name="tugas" onkeypress="return tanpa_kutip(event);" maxlength="45" style="width:300px;" /></td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['keterangan'] ?></td>
                <td>:</td>
                <td><input type="text" class="myinputtext" id="keterangan" name="keterangan" onkeypress="return tanpa_kutip(event);" maxlength="90" style="width:300px;" /></td>
            </tr>
            <tr>
                <td style="width:150px;"><?php echo $_SESSION['lang']['email'] ?></td>
                <td>:</td>
                <td><input type="text" class="myinputtext" id="email" name="email" onkeypress="return tanpa_kutip(event);" maxlength="500" style="width:300px;" /> (separate with comma)</td>
            </tr>
            <tr>
                <td></td><td></td><td colspan="2" id="tombolsave">
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div id="detailtable2" style="display:none">
                        <table cellspacing="1" border="0">
                            <thead>
                                <tr>
                                    <td colspan="2" align="center"><?php echo $_SESSION['lang']['kodebarang'] ?></td>
                                    <td align="center"><?php echo $_SESSION['lang']['namabarang'] ?></td>
                                    <td align="center"><?php echo $_SESSION['lang']['satuan'] ?></td>
                                    <td align="center"><?php echo $_SESSION['lang']['jumlah'] ?></td>
                                    <td align="center"><?php echo $_SESSION['lang']['action'] ?></td>
                            </thead>
                            <tbody id="detailisi">
                            </tbody>
                            <tfoot>
                                <tr><td colspan="6" align="center">
                                        <div id="tombolselesai">
                                    </td></tr>
                            </tfoot>
                        </table>
                    </div>

                </td> 
            </tr>
        </table>
    </fieldset>


    <?php
    CLOSE_BOX();
    ?>
</div>

<?php
echo close_body();
?>