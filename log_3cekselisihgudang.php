<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>



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

<?php

$id_user = $_SESSION['standard']['userid'];

$allowed_users = ['0000000001','0000000002','0000000003','0000000004','0000000005','0000000006','0000000007','0000000008','0000010163'];

if (!in_array($id_user, $allowed_users)) {
    exit("warning: No access");
}

if($_SESSION['org']['tipeinduk'] != 'HOLDING' || !in_array($id_user, $allowed_users)){
  $lstData[substr($val['kodeorg'], 0, 4)] = $_SESSION['empl']['lokasitugas'];
}else{
  $sPeriode = "select distinct kodeorg,periode from " . $dbname . ".setup_periodeakuntansi group by kodeorg";
  $rPeriode = fetchData($sPeriode);
  foreach ($rPeriode as $key => $val) {
      $lstData[substr($val['kodeorg'], 0, 4)] = substr($val['kodeorg'], 0, 4);
      // $lstPeriode[$val['periode']] = $val['periode'];
  }
}

$optUnit = "<select id=unitId class='select2' style=width:200px onchange='getperiodegudang()'>";
$optUnit .= "<option value=''>Pilih Data</option>";
foreach ($lstData as $key2) {
    $optUnit .= "<option value='" . $key2 . "'>" . getNamaOrg($key2) . " - ".$key2."</option>";
}
$optUnit .= "</select>";
$optPeriode = "<select id=periodeId class='select2' style=width:200px>";
foreach ($lstPeriode as $key2) {
    $optPeriode .= "<option value='" . $key2 . "'>" . $key2 . "</option>";
}
$optPeriode .= "</select>";
// Akun persediaan
$sAkun = "SELECT * FROM " . $dbname . ".log_5klbarang WHERE noakun like '115%'";
$rAkun = fetchData($sAkun);
$optAKUN = "<select id=akunpersediaan class='select2' style=width:200px>";
$optAKUN .="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach ($rAkun as $key2) {
    $optAKUN .= "<option value='" . $key2['noakun'] . "'>" . $key2['kode'] . " - ".$key2['kelompok']."</option>";
}
$optAKUN .= "</select>";


$optDivisi = "<select id=divisiId class='select2' style=width:200px>";
$optDivisi .= "</select>";

$optBarang = "<select id=barangId class='select2' style=width:200px>";
$optBarang .= "</select>";


?>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>" /></script>
<script language=javascript src='js/log_3cekselisihgudang.js?v=<? echo time(); ?>'></script>
<?
OPEN_BOX('', '<span class=judul>' . (getMenu('log_3cekselisihgudang')) . '</span><br>');
?>
<div>

    <!-- tipe pengecekan -->

    <?php
    $arrTipe = array(
        '1' => 'Saldo Gudang Divisi',
        // '2' => 'Cek BKM Belum Posting', 
        // '3' => 'Cek Transaksi Gudang Belum Posting', 
        // '4' => 'Cek Transaksi Mutasi Belum Diterimakan',
        '5' => 'Cek Saldo Awal Gudang VS Akunting VS Transaksi Gudang',
        '8' => 'Cek hargarata belum sesuai',
        '8.1' => 'Rekalkulasi hargarata',
        '6' => 'Transaksi Masuk Gudang VS Jurnal',
        '7' => 'Transaksi Keluar Gudang VS Jurnal',
        // '9' => 'Bentuk / Perbaiki Jurnal'
    );
    $opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $nox=1;
    foreach($arrTipe as $key => $val){
        $opttipe.="<option value=".$key.">".$nox++." - ".$val."</option>";			
    }
    $SelectTipe = "<select id=tipe style='width:200px' class='select2' onchange='cekTipe()'>".$opttipe."</select>";
    ?>

    <!-- Akhir tipe pengecekan -->


    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0">
            <tr>
                <td><label><?php echo $_SESSION['lang']['unit'] ?></label></td>
                <td>:</td>
                <td><?php echo $optUnit ?></td>
            </tr>
            <tr>
                <td><label><?php echo $_SESSION['lang']['periode'] ?></label></td>
                <td>:</td>
                <td><?php echo $optPeriode ?></td>
            </tr>
            <tr>
                <td><label>Kelompok Barang</label></td>
                <td>:</td>
                <td><?php echo $optAKUN ?></td>
            </tr>
            <tr>
                <td><label><?php echo $_SESSION['lang']['tipe'] ?></label></td>
                <td>:</td>
                <td><?php echo $SelectTipe ?></td>
            </tr>
            <tr>
                <td><label>Divisi</label></td>
                <td>:</td>
                <td><?php echo $optDivisi ?></td>
            </tr>
            <tr>
                <td><label>Barang</label></td>
                <td>:</td>
                <td><?php echo $optBarang ?></td>
            </tr>
            <tr>
                <td>
                <td>
                <td><button onclick="preview('html',event)" class="mybutton" name="preview" id="preview"><? echo $_SESSION['lang']['preview'] ?></button></td>
            </tr>

        </table>
    </fieldset>
</div>


<style>
  .container-info {
    display: flex;
    gap: 15px;
    align-items: flex-start;
  }
  .fieldset-1 {
    /* border: 1px solid #999;
    border-radius: 6px;
    padding: 10px; */
    /* background: #f9f9f9; */
  }
  .fieldset-1 legend {
    font-weight: bold;
    /* padding: 0 5px; */
  }
  .col-right {
    display: flex;
    flex-direction: column;
    gap: 15px;
    flex: 1;
  }
  .col-left {
    flex: 1;
  }
</style>

<div class="container-info">
  <!-- Kolom Kiri -->
  <div class="col-left">
    <fieldset class='fieldset-1' style="font-size:10px">
      <legend>Pra Tutup Buku Fisik</legend>
      <div id="praTutupBuku"></div>
    </fieldset>
  </div>

  <!-- Kolom Kanan -->
  <div class="col-right">
    <fieldset style="font-size:10px">
      <legend>Info</legend>
      <div id="infoBisaCek"></div>
    </fieldset>

    <fieldset style="font-size:10px">
      <legend>Note</legend>
      <table cellpadding="5" cellspacing="1" border="0" style="font-weight:bold">
        <tr>
          <td style="width:20px;background:green">&nbsp;</td>
          <td style="background-color:#d1e3fa; color:black;">Atau 
            <span class="icon" style="font-size:11px;color:green;">&#10003;</span> :
          </td>
          <td style="background-color:#d1e3fa; color:black;">Tidak Bermasalah</td>
        </tr>
        <tr>
          <td style="width:20px;background:red">&nbsp;</td>
          <td style="background-color:#d1e3fa; color:black;">Atau 
            <span class="icon" style="font-size:11px;color:red;">&#10007;</span> :
          </td>
          <td style="background-color:#d1e3fa; color:black;">Bermasalah</td>
        </tr>
        <tr>
          <td>1.</td>
          <td colspan="2" style="background-color:#d1e3fa; color:black;">
            <ul>
              <li><a href="javascript:do_load('log_3integrity')">Lakukan Integrity Check BKM</a></li>
              <li><a href="javascript:do_load('log_3rekalkulasi_stock')">Lakukan Rekalkulasi Stock</a></li>
            </ul>
          </td>
        </tr>
        <tr>
          <td>2.</td>
          <td colspan="2" style="background-color:#d1e3fa; color:black;">
            Jalankan tipe 1 - 6, lalu cek 
            <a href="javascript:do_load('log_3cekgudang')">Pra Tutup Buku Fisik</a>
          </td>
        </tr>
      </table>

    </fieldset>
  </div>
</div>



<?
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer' style='overflow:auto;height:400px;'></div>

<?php

CLOSE_BOX();
echo close_body();
?>
<!-- a -->