<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

OPEN_BOX('','<span class=judul>'.getMenu('log_2keluarmasukbrg').'</span><br>');
?>
<script type="text/javascript" src="js/log_2keluarmasukbrg.js?ver=1.3" /></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<div id="action_list">
    <?php
    // if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    //     $optPt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
    //     $spt = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi";
    //     $qpt = $owlPDO->query($spt) or die(print " Gagal: " . PDOException::getMessage());
    //     $qpt->setFetchMode(PDO::FETCH_ASSOC);
    //     while ($rpt = $qpt->fetch()) {
    //         $optPt.="<option value=" . $rpt['kodeorganisasi'] . ">" . $rpt['namaorganisasi'] . "</option>";
    //     }
    //     $optGudang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
    // } else {
    //     $optPt = '';
    //     $optPt.="<option value='" . $_SESSION['empl']['kodeorganisasi'] . "'>" . $_SESSION['org']['namaorganisasi'] . "</option>";

    //     $optGudang = '';
    //     $sgdng = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' and induk = '" . $_SESSION['empl']['lokasitugas'] . "'";
    //     $qgdng = $owlPDO->query($sgdng) or die(print " Gagal: " . PDOException::getMessage());
    //     $qgdng->setFetchMode(PDO::FETCH_ASSOC);
    //     while ($rgdng = $qgdng->fetch()) {
    //         $optGudang.="<option value=" . $rgdng['kodeorganisasi'] . ">" . $rgdng['namaorganisasi'] . "</option>";
    //     }
    // }
    
    // GET PT
    $ptDetailAkses = getOrgDetail(3);
    $optPt ="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    foreach($ptDetailAkses as $key => $value){
        $optPt .= '<option value="'.$key.'">'.$value.'</option>';
    }

    $unitDetailAkses = getOrgDetail(2);
    $whereUnit = " and induk in (".$unitDetailAkses.")";

    $optGudang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

    $sql = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' {$whereUnit} ";
    $listGudang = fetchData($sql);
    foreach ($listGudang as $data) {
        $optGudang.="<option value=" . $data['kodeorganisasi'] . ">" . $data['namaorganisasi'] . "</option>";
    }

    $str = "select distinct substr(tanggal,1,7) as tanggal from " . $dbname . ".log_transaksiht
      order by tanggal desc";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $optper = '';
    while ($bar = $res->fetch()) {
        $optper.="<option value='".$bar->tanggal."'>".$bar->tanggal."</option>";
    }
    $optbrg = '';

    echo "
      <fieldset style='float:left;'>
        <legend>" . $_SESSION['lang']['pilihdata'] . "</legend>
            <table border=0 cellpadding=1 cellspacing=1>
               
                <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select class='select2' style=width:195px id=company_id name=company_id onchange='getGudang()'>" . $optPt . "</select></td>
                </tr>
                
                 <tr>
                    <td>".$_SESSION['lang']['pilihgudang']."</td>
                    <td>:</td>
                    <td><select class='select2' style=width:195px id=gudang_id name=gudang_id>" . $optGudang . "</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select class='select2' style=width:195px id=period name=period>".$optper."</select></td>
                </tr>
                
                <tr>
                    <td><td><td>
                 <button class=mybutton onclick=save_pil()>" . $_SESSION['lang']['save'] . "</button>
                   <button class=mybutton onclick=ganti_pil()>" . $_SESSION['lang']['cancel'] . "</button>
                
                    </td>
                </tr>
            </table>
</fieldset>
    ";
    
	// echo"<table>
 //     <tr valign=moiddle>
	// 	 <td><fieldset><legend>" . $_SESSION['lang']['pilihdata'] . "</legend>";
 //    echo $_SESSION['lang']['pt'] . " : <select id=company_id name=company_id onchange='getGudang()'>" . $optPt . "</select>&nbsp;";
 //    echo $_SESSION['lang']['pilihgudang'] . " : <select id=gudang_id name=gudang_id>" . $optGudang . "</select>&nbsp;";
 //    echo $_SESSION['lang']['periode'] . " : <select id=period name=period>" . $optper . "</select>";
 //    echo"<button class=mybutton onclick=\"save_pil()\">" . $_SESSION['lang']['save'] . "</button>
	// 	 <button class=mybutton onclick=ganti_pil()>" . $_SESSION['lang']['ganti'] . "</button>";
 //    echo"</fieldset></td>
 //     </tr>
	//  </table> ";

    ?>
</div>
    <?php
    CLOSE_BOX();
    OPEN_BOX();
    ?>
	
<div id="cari_barang" name="cari_barang">
        <table cellspacing="1" border="0">
            <tr><td><?php echo $_SESSION['lang']['nm_brg'] ?></td><td>:</td>
			<td><select class='select2'  style="width:200px" id="nm_goods" name="nm_goods" onchange="throwThisRow(0);"><?php echo $optbrg ?></select></td></tr>
        </table>
    <div style='clear:both'></div>
	<div id="contain">
	</div>
    <!--<div id="hasil_cari" name="hasil_cari" style="display:none;">
        <fieldset>
            <legend><?php echo $_SESSION['lang']['result'] ?></legend>
            <img onclick="dataKeExcel(event, 'log_laporanKeluarMasukPerBarang_Excel.php')" src=images/excel.jpg class=resicon title='MS.Excel'> 
            <img onclick="dataKePDF(event)" title='PDF' class=resicon src=images/pdf.jpg>

            <table cellspacing="1" border="0" id="table_data_barang">
                <tbody id="isi_conten">
                    <tr id="isi_data_barang">
                        <td><?php echo $_SESSION['lang']['kodebarang'] ?></td>
                        <td>:</td>
                        <td id="kd_brg"></td>
                    </tr>
                    <tr id="isi_data_barang">
                        <td><?php echo $_SESSION['lang']['namabarang'] ?></td>
                        <td>:</td>
                        <td id="nm_brg"></td>
                    </tr>
                    <tr id="isi_data_barang">
                        <td><?php echo $_SESSION['lang']['satuan'] ?></td>
                        <td>:</td>
                        <td id="satuan_brg"></td>
                    </tr>
                </tbody>
            </table>
			
			
            <table class="sortable" cellspacing="1" border="0" style='position:absolut;'>
                <thead>
                    <tr class="rowheader">
                        <td align="center"  style='width:50px;'>No.</td>
                        <td align="center" style='width:200px;'><?php echo $_SESSION['lang']['notransaksi'] ?></td>
                        <td align="center" style='width:100px;'><?php echo $_SESSION['lang']['tanggal'] ?></td>
                        <td align="center" style='width:100px;'><?php echo $_SESSION['lang']['saldoawal'] ?> </td>
                        <td align="center" style='width:100px;'><?php echo $_SESSION['lang']['masuk'] ?> </td>
                        <td align="center" style='width:100px;'><?php echo $_SESSION['lang']['keluar'] ?> </td>
                        <td align="center" style='width:100px;'><?php echo $_SESSION['lang']['saldo'] ?> </td>
                    </tr>
                </thead>
			</table>
			
			
			<div style='height:400px;overflow: auto;'>
			<table class=sortable border=0 cellspacing=1>
			<tbody id=contain>
			</tbody>
					 
			</table>
			</div> 

 </fieldset>-->
</div>



<?php
CLOSE_BOX();
?>







































<?php
echo close_body();
?>