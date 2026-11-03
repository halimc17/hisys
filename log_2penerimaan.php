<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_2penerimaan').'</span><br>');

require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src="js/zSelect2.js?ver=1.9"></script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_2penerimaan.js?v=<?php echo time(); ?>"></script>
<div id="action_list">
    <?php
    $unitDetailAkses = getOrgDetail(2);

    #gudang pada unit yang boleh diakses dan pernah punya penerimaan
    $optGudang = "<option value=''>" . $_SESSION['lang']['pilihgudang'] . "</option>";
    $sGudang = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' and induk in (" . $unitDetailAkses . ") and kodeorganisasi in (select kodegudang from " . $dbname . ".log_transaksiht where tipetransaksi=1) order by kodeorganisasi";
    $qGudang = $owlPDO->query($sGudang) or die(print " Gagal: " . PDOException::getMessage());
    $qGudang->setFetchMode(PDO::FETCH_ASSOC);
    while ($rGudang = $qGudang->fetch()) {
        $optGudang .= "<option value='" . htmlspecialchars($rGudang['kodeorganisasi'], ENT_QUOTES) . "'>" . htmlspecialchars($rGudang['namaorganisasi']) . "</option>";
    }

    #supplier yang pernah mengirim barang, diurutkan menurut nama
    $nmsupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
    $arrSupplier = array();
    $res = fetchdata("select distinct idsupplier from " . $dbname . ".log_transaksiht where tipetransaksi=1 and idsupplier<>'' and substr(kodegudang,1,4) in (" . $unitDetailAkses . ")");
    foreach ($res as $val) {
        $arrSupplier[$val['idsupplier']] = trim(isset($nmsupplier[$val['idsupplier']]) ? $nmsupplier[$val['idsupplier']] : $val['idsupplier']);
    }
    asort($arrSupplier);
    $optSupplier = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
    foreach ($arrSupplier as $kode => $nama) {
        $optSupplier .= "<option value='" . htmlspecialchars($kode, ENT_QUOTES) . "'>" . htmlspecialchars($nama) . "</option>";
    }

    #tahun penerimaan: default tahun sekarang, "Semua Tahun" untuk seluruh tahun
    $tahunSkrg = date('Y');
    $arrTahun = array($tahunSkrg => $tahunSkrg);
    $res = fetchdata("select distinct year(tanggal) as thn from " . $dbname . ".log_transaksiht where tipetransaksi=1 and tanggal>'0000-00-00'");
    foreach ($res as $val) {
        $arrTahun[$val['thn']] = $val['thn'];
    }
    krsort($arrTahun);
    $optTahun = "<option value=''>Semua Tahun</option>";
    foreach ($arrTahun as $thn) {
        $optTahun .= "<option value='" . $thn . "'" . ($thn == $tahunSkrg ? " selected" : "") . ">" . $thn . "</option>";
    }

    $optStatus = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option><option value='1'>Sudah diposting</option><option value='0'>Belum diposting</option>";

    echo "<table>
     <tr valign=top>
        <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
            <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
        </td>
        <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
            <table cellpadding=1 cellspacing=1 border=0>
            <tr>
                <td>" . $_SESSION['lang']['tahun'] . "</td>
                <td>:</td>
                <td style='width:200px'><select id=filterTahun class=select2 style='width:100%'>" . $optTahun . "</select></td>
                <td style='padding-left:15px;'>" . $_SESSION['lang']['pilihgudang'] . "</td>
                <td>:</td>
                <td style='width:220px'><select id=kdGdng class=select2 style='width:100%'>" . $optGudang . "</select></td>
                <td style='padding-left:15px;'>Supplier</td>
                <td>:</td>
                <td style='width:240px'><select id=kodesupplier class=select2 style='width:100%'>" . $optSupplier . "</select></td>
            </tr>
            <tr>
                <td>No. Transaksi</td>
                <td>:</td>
                <td style='width:200px'><input type=text id=txtNoTransaksi style='width:100%;box-sizing:border-box' maxlength=30 class=myinputtext onkeypress='return tanpa_kutip(event)'></td>
                <td style='padding-left:15px;'>" . $_SESSION['lang']['nopo'] . "</td>
                <td>:</td>
                <td style='width:220px'><input type=text id=txtsearch style='width:100%;box-sizing:border-box' maxlength=30 class=myinputtext onkeypress='return tanpa_kutip(event)'></td>
                <td style='padding-left:15px;'>" . $_SESSION['lang']['nopp'] . "</td>
                <td>:</td>
                <td style='width:240px'><input placeholder='" . $_SESSION['lang']['input'] . " " . $_SESSION['lang']['nopp'] . " min 3 char' type=text id=txtsearch2 style='width:100%;box-sizing:border-box' maxlength=30 class=myinputtext onkeypress='return tanpa_kutip(event)'></td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['namabarang'] . "</td>
                <td>:</td>
                <td style='width:200px'><input type=text id=nmBrg style='width:100%;box-sizing:border-box' class=myinputtext onkeypress='return tanpa_kutip(event)'></td>
                <td style='padding-left:15px;'>" . $_SESSION['lang']['tanggal'] . "</td>
                <td>:</td>
                <td style='width:220px'><input type=text class=myinputtext id=tgl_cari style='width:100%;box-sizing:border-box' onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 readonly/></td>
                <td style='padding-left:15px;'>Status</td>
                <td>:</td>
                <td style='width:240px'><select id=statusposting class=select2 style='width:100%'>" . $optStatus . "</select></td>
            </tr>
            <tr>
                <td colspan=9 style='padding-top:4px;'>
                    <button class=mybutton onclick=cariData()>" . $_SESSION['lang']['find'] . "</button>
                    <button class=mybutton onclick=exportExcelDPB()>" . $_SESSION['lang']['excel'] . "</button>
                    <button class=mybutton onclick=exportPdfDPB()>PDF</button>
                </td>
            </tr>
            </table>
        </fieldset></td>
     </tr>
     </table>";
    ?>
</div>
<?php
CLOSE_BOX(); //1 C //2 O
?>
<div id=list_pp_verication>
    <?php OPEN_BOX(); ?>
        <div style="overflow:auto; height:68vh; width:100%;">
            <table class="sortable" cellpadding=5 cellspacing="1" border="0" style="width:100%;">
                <thead>
                    <tr class=rowheader>
                        <th align='center' style="width:30px">No.</th>
                        <th align='center' style="width:190px"><?php echo $_SESSION['lang']['notransaksi']; ?></th>
                        <th align='center' style="width:85px"><?php echo $_SESSION['lang']['tanggal']; ?></th>
                        <th align='center' style="width:190px"><?php echo $_SESSION['lang']['nopo'] ?></th>
                        <th align='center'>Supplier</th>
                        <th align='center' style="width:170px"><?php echo $_SESSION['lang']['gudang']; ?></th>
                        <th align='center' style="width:110px">No. Surat Jalan</th>
                        <th align='center' style="width:60px">Item</th>
                        <th align='center' style="width:300px">Barang (Qty)</th>
                        <th align='center' style="width:120px">Status</th>
                        <th align='center' style="width:80px">Action</th>
                    </tr>
                </thead>
                <tbody id="contain">
                </tbody>
                <tfoot>
                </tfoot>
            </table></div>
        <script>cariData()</script>
    <?php
    CLOSE_BOX();
    ?>
</div>
<input type="hidden" name="method" id="method"  />
<input type="hidden" id="no_po" name="no_po" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid'] ?>" />

<?
echo close_body();
?>
