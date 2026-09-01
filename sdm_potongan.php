<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include_once('lib/zLib.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zReport.js'></script>

<script language="javascript">
    function add_new_data() {
        document.getElementById('headher').style.display = "block";
        document.getElementById('listData').style.display = "none";
        document.getElementById('detailEntry').style.display = "none";
        unlockForm();
        document.getElementById('contentDetail').innerHTML = '';
        statFrm = 0;
    }
    nmTmblDone = '<?php echo $_SESSION['lang']['done'] ?>';
    nmTmblCancel = '<?php echo $_SESSION['lang']['cancel'] ?>';
</script>

<script language=javascript1.2 src='js/sdm_potongan.js?v=<?php echo time(); ?>'></script>

<?
OPEN_BOX('', '<span class=judul>' . getMenu('sdm_potongan') . '</span>');
?>
<input type="hidden" id="proses" name="proses" value="insert" />
<div id="action_list">
    <?php
    $optOrg2 = getOrgDetail(1);
    $dtisi = 1;
    $lstorg = array();
    $optTipePot = $optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
    foreach ($optOrg2 as $key => $nmorg) {
        $sGaji = "select distinct * from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $key . "'";
        $rGaji = fetchData($sGaji);
        if (count($rGaji) > 0) {
            $lstorg[$key] = $key;
            $optOrg .= "<option value=" . $key . ">" . $key . "-" . $nmorg . "</option>";
        }
    }

    #list potongan
    $sTipePot = "select distinct id,name from " . $dbname . ".sdm_ho_component where plus='0' and type='additional' and `lock`='0' and id != 87 order by name asc";
    $qTipePot = $owlPDO->query($sTipePot) or die(print " Gagal: " . PDOException::getMessage());
    $qTipePot->setFetchMode(PDO::FETCH_ASSOC);
    while ($rTipePot = $qTipePot->fetch()) {
        $optTipePot .= "<option value='" . $rTipePot['id'] . "'>" . $rTipePot['name'] . "</option>";
    }

    $optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
    $sGet = "select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg in ('" . implode("','", $lstorg) . "')
             and sudahproses=0 and jenisgaji='H' order by periode desc";
    $qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
    $qGet->setFetchMode(PDO::FETCH_ASSOC);
    while ($rGet = $qGet->fetch()) {
        $optPeriode .= "<option value=" . $rGet['periode'] . ">" . $rGet['periode'] . "</option>";
    }

    // echo $sGet;

    echo "<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
    echo $_SESSION['lang']['unit'] . " : <select id=kdOrgCr style=width:150px; >" . $optOrg . "</select>&nbsp;";
    echo $_SESSION['lang']['periode'] . " : <select id=tgl_cari>" . $optPeriode . "</select>&nbsp";
    echo $_SESSION['lang']['potongan'] . " : <select id=tpPotCr  style=width:150px; >" . $optTipePot . "</select>&nbsp;";
    echo "<button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button>";
    echo "</fieldset></td>
	 </tr>
	 </table> ";
    ?>
</div>
<?php
CLOSE_BOX();
?>
<div id="listData">
    <?php OPEN_BOX() ?>
    <!--display data-->
    <div id="contain" style=height:65vh>
        <script>
            loadData();
        </script>
    </div>
    <?php CLOSE_BOX() ?>
</div>

<div id="headher" style="display:none">
    <?php
    OPEN_BOX();
    ?>
    <fieldset style="float:left;height:183px;">
        <legend><?php echo $_SESSION['lang']['header'] ?></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><?php echo $_SESSION['lang']['unitkerja'] ?></td>
                <td>:</td>
                <td>
                    <select id="kdOrg" name="kdOrg" style="width:200px;"
                        onchange="getPrd()"><?php echo $optOrg; ?></select>
                </td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['periode'] ?></td>
                <td>:</td>
                <td><select id="tglAbsen" style="width:200px"><? echo $optPeriode ?></select></td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['potongan'] ?></td>
                <td>:</td>
                <td><select id="tpPotongan" name="tpPotongan" style="width:200px;"><?php echo $optTipePot; ?></select>
                </td>
            </tr>
            <tr>
                <td colspan="3">&nbsp</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="3">
                    <div id="tombolHeader">
                        <button class=mybutton id=dtlAbn onclick=add_detail()><?php echo $_SESSION['lang']['save'] ?></button>
                        <button class=mybutton id=cancelAbn onclick=displayList()><?php echo $_SESSION['lang']['cancel'] ?></button>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset style="float:left">
        <legend><?php echo "Upload File" ?></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><?php echo $_SESSION['lang']['unitkerja'] ?></td>
                <td>:</td>
                <td>
                    <select id="kodeorg2" name="kodeorg2" style="width:240px;"
                        onchange="getPrd2()"><?php echo $optOrg; ?></select>
                </td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['periode'] ?></td>
                <td>:</td>
                <td><select id="periode2" style="width:240px"><? echo $optPeriode ?></select></td>
            </tr>
            <tr>
                <td><?php echo $_SESSION['lang']['potongan'] ?></td>
                <td>:</td>
                <td><select id="potongan2" name="potongan2" style="width:240px;"><?php echo $optTipePot; ?></select></td>
            </tr>
            <tr>
                <td><?php echo "Get Karyawan" ?></td>
                <td>:</td>
                <td><button style="width:238px" class=mybutton onclick="getkaryawanid()"><?php echo "Get Karyawan" ?></button></td>
            </tr>
            <tr>
                <td><?php echo "File (.xls / .xlsx)" ?></td>
                <td>:</td>
                <td>
                    <input name='filex' type='file' id='filex' size='25' class='mybutton'>
                </td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="3">
                    <div>
                        <button class=mybutton onclick=previewInsertfile()><?php echo $_SESSION['lang']['save'] ?></button>
                        <button class=mybutton onclick=displayList()><?php echo $_SESSION['lang']['cancel'] ?></button>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>

    <?php
    CLOSE_BOX();
    ?>
</div>
<div id="detailEntry" style="display:none">
    <?php
    OPEN_BOX();
    ?>
    <div id="addRow_table">
        <fieldset style="float:left">
            <legend><?php echo $_SESSION['lang']['detail'] ?></legend>
            <div id="detailIsi">
            </div>
            <table cellspacing="1" border="0" style="width:500px;">
                <tr>
                    <td id="tombol">

                    </td>
                </tr>
            </table>
        </fieldset>
    </div><br />
    <br />
    <div style="overflow:auto; height:300px; clear:both;">
        <fieldset style="float:left;">
            <legend><?php echo $_SESSION['lang']['datatersimpan'] ?></legend>
            <table cellspacing='1' cellpadding='5' border='0' class='sortable' style='width:600px'>
                <thead>
                    <tr class="rowheader">
                        <td align='center'>No</td>
                        <td align='center'><?php echo $_SESSION['lang']['nik'] ?></td>
                        <td align='center'><?php echo $_SESSION['lang']['namakaryawan'] ?></td>
                        <td align='center'><?php echo $_SESSION['lang']['potongan'] ?></td>
                        <td align='center'><?php echo $_SESSION['lang']['keterangan'] ?></td>
                        <td align='center'><?php echo $_SESSION['lang']['updateby'] ?></td>
                        <td align='center'>Action</td>
                    </tr>
                </thead>
                <tbody id="contentDetail">

                </tbody>
            </table>
        </fieldset>
    </div>
    <?php
    CLOSE_BOX();
    ?>
</div>
<?php
echo close_body();
?>