<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language=javascript src=js/zTools.js></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
    nmTmblDone = '<?php echo $_SESSION['lang']['done'] ?>';
    nmTmblCancel = '<?php echo $_SESSION['lang']['cancel'] ?>';
</script>
<script language="javascript" src="js/pabrik_dispacth.js"></script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper("BA Pengapalan (Dispatch)").'</span>');
?>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
    <?php
	$optagama=$optKtrk='';
    #jenis transaksi berdasarkan enum pada table pabrik_blk_kirimht
    $optagama.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    

    #query total pengiriman
    $sdata="select nokontrak,sum(beratbersih) as totKirim from ".$dbname.".pabrik_blk_kirimdt a left join 
            ".$dbname.".pabrik_blk_kirimht b on a.nokirim=b.nokirim where millcode='".$_SESSION['empl']['lokasitugas']."' and jenis='SALES'
            group by nokontrak";
    $rdata=fetchdata($sdata);
    foreach($rdata as $row){
        $fisKirim[$row['nokontrak']]=$row['totKirim'];
    }
    #mengambil kontrak yg didaftarkan
    $sKontrak="select nokontrak,totalkontrak,toleransi from ".$dbname.".pabrik_blk_daftar where millcode='".$_SESSION['empl']['lokasitugas']."' order by tanggal desc";
    $rKontrak=fetchdata($sKontrak);
    $optKtrk.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    foreach($rKontrak as $row){
        $totFsk=$row['totalkontrak']+($row['totalkontrak']*$row['toleransi']);
        if(intval(@$fisKirim[$row['nokontrak']])<$totFsk){
            $optKtrk.="<option value='".$row['nokontrak']."'>".$row['nokontrak']."</option>";    
        }
    }
    echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
    echo "<table border=0><tr><td>".$_SESSION['lang']['noberitaacara']."</td><td>:</td><td><input type=text id='notransaksiCr' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>";
    echo "<td>".$_SESSION['lang']['NoKontrak'] . "</td><td>:</td><td><input type=text id='nokontrakCr' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>";
    echo "<td>".$_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class='myinputtext' id='tglCr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td></tr>";
   
    echo"<tr><td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";

    echo"</table>";
    echo"</fieldset></td>
	 </tr>
	 </table> ";
    ?>
</div>
<?php
CLOSE_BOX();
?>
<div id="listData">
    <?php 
    OPEN_BOX();  
    //<!--display data-->
    echo"<fieldset style='float:left;'>
        <legend>".$_SESSION['lang']['list']."</legend>
        
        <div>
            <table cellpadding='1' cellspacing='1' border='0' class='sortable'>
                <thead>
                   <tr class=rowheader align=center>
                    <td rowspan=2>No.</td>
                    <td rowspan=2>".$_SESSION['lang']['noberitaacara']."</td>
                    <td rowspan=2>".$_SESSION['lang']['tanggal']."</td>
                    <td rowspan=2>".$_SESSION['lang']['NoKontrak']."</td>
                    <td rowspan=2>Nama Kapal</td>
                    <td colspan=2>".$_SESSION['lang']['tanggal']." Muat</td>
                    <td rowspan=2>".$_SESSION['lang']['action']."</td>
                </tr>
                <tr class=rowheader align=center>
                    <td>".$_SESSION['lang']['mulai']."</td>
                    <td>".$_SESSION['lang']['selesai']."</td>
                </tr>
                </thead>
                <tbody id='contain'>

                </tbody>
                <tfoot id='footData'>
                </tfoot>
            </table>
            <script type=\"text/javascript\">loadData(0);</script>
        </div>
    </fieldset>";
      CLOSE_BOX() ?>
</div>

<div id="headher" style="display:none">
    <?php
    OPEN_BOX();
//$optTipePot
    $sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
    $rOrg=fetchdata($sOrg);
    $arr="##notransaksi##kdOrg##tgl##nokontrak##komoditi##surveyor##ptsurveyor##chief##hdBlking##nmKapal";
    $arr.="##tglMulai##tglSlsi##aslKrm##tujuan##kgAwal##kgAkhir##TotMuat##proses";
    $arr.="##tglMulaiJm##tglMulaiMnt##tglSlsiJm##tglSlsiMnt##tinggiAwal##suhuAwal##tinggiAkhir##suhuAkhir";
    $jm = $mnt = "";
    for ($t = 0; $t < 24;) {
        if (strlen($t) < 2) {
            $t = "0" . $t;
        }
        $jm.="<option value=" . $t . " " . ($t == 00 ? 'selected' : '') . ">" . $t . "</option>";
        $t++;
    }
    for ($y = 0; $y < 60;) {
        if (strlen($y) < 2) {
            $y = "0" . $y;
        }
        $mnt.="<option value=" . $y . " " . ($y == 00 ? 'selected' : '') . ">" . $y . "</option>";
        $y++;
    }
  echo"
    <fieldset style=float:left;height:280px>
        <legend>".$_SESSION['lang']['header']."</legend>
        <table cellspacing=\"1\" border=\"0\">
            <tr><td>No. BA Pengapalan</td>
                <td>:</td>
                <td><input type=text id='notransaksi' class='myinputtext' style='width:200px;' disabled /></td>
            </tr>
            <tr><td>".$_SESSION['lang']['pt']."</td>
                <td>:</td><td>
                <select id=\"kdOrg\" name=\"kdOrg\" style=\"width:200px;\">";
                foreach($rOrg as $row){
                echo"<option value='".$row['kodeorganisasi']."'>".$row['kodeorganisasi']."</option>";    
                }
                echo"</select>";
        echo"</td>
            </tr>
            <tr><td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td><input type=text class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td>
            </tr>
            <tr><td>".$_SESSION['lang']['NoKontrak']."</td>
                <td>:</td>
                <td><select id='nokontrak' style='width:200px;' onchange=getKomoditi()>".$optKtrk."</select></td>
            </tr>

            <tr><td>".$_SESSION['lang']['komoditi']."</td>
                <td>:</td>
                <td><select id=\"komoditi\" name=\"komoditi\" style=\"width:200px;\" >".$optagama."</select>
                </td>
            </tr>
            <tr><td>Tinggi Awal</td>
                <td>:</td>
                <td><input type=text id='tinggiAwal' class='myinputtextnumber' style='width:200px;' onkeypress='return angka_doang(event)' /></td>
            </tr>
            <tr><td>Suhu Awal</td>
                <td>:</td>
                <td><input type=text id='suhuAwal' class='myinputtextnumber' style='width:200px;' onkeypress='return angka_doang(event)' /></td>
            </tr>
            <tr><td>Kg Awal</td>
                <td>:</td>
                <td><input type=text id='kgAwal' class='myinputtextnumber' onchange=getTonase() style='width:200px;' onkeypress='return angka_doang(event)' /></td>
            </tr>
            <tr><td>Tinggi Akhir</td>
                <td>:</td>
                <td><input type=text id='tinggiAkhir' class='myinputtextnumber' style='width:200px;' onkeypress='return angka_doang(event)' /></td>
            </tr>
            <tr><td>Suhu Akhir</td>
                <td>:</td>
                <td><input type=text id='suhuAkhir' class='myinputtextnumber' style='width:200px;' onkeypress='return angka_doang(event)' /></td>
            </tr>
            <tr><td>Kg Akhir</td>
                <td>:</td>
                <td><input type=text id='kgAkhir' class='myinputtextnumber'  onchange=getTonase() style='width:200px;' onkeypress='return angka_doang(event)' /></td>
            </tr>
            <tr><td>Total Muat</td>
                <td>:</td>
                <td><input type=text id='TotMuat' class='myinputtextnumber' style='width:200px;' onkeypress='return angka_doang(event)' readonly=readonly /></td>
            </tr>
            
            </table></fieldset>
          <fieldset style=float:left;height:280px><legend>Data Loading</legend>
          <table border=0>
          <tr><td>Surveyor</td>
                <td>:</td>
                <td><input type=text id='surveyor' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
			<tr><td>PT Surveyor</td>
                <td>:</td>
                <td><input type=text id='ptsurveyor' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
            <tr><td>Chief</td>
                <td>:</td>
                <td><input type=text id='chief' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
            <tr><td>Head Bulking</td>
                <td>:</td>
                <td><input type=text id='hdBlking' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
            <tr><td>Nama Kapal</td>
                <td>:</td>
                <td><input type=text id='nmKapal' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
          <tr><td>".$_SESSION['lang']['tanggalmulai']."</td>
                <td>:</td>
                <td><input type=text class='myinputtext' id='tglMulai' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' />
                <select id=tglMulaiJm>".$jm."</select>:<select id=tglMulaiMnt>".$mnt."</select>
                </td>
            </tr>
            <tr><td>".$_SESSION['lang']['tanggalselesai']."</td>
                <td>:</td>
                <td><input type=text class='myinputtext' id='tglSlsi' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' />
                <select id=tglSlsiJm>".$jm."</select>:<select id=tglSlsiMnt>".$mnt."</select>
                </td>
            </tr>
            <tr><td>Asal Kirim</td>
                <td>:</td>
                <td><input type=text id='aslKrm' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
            <tr><td>".$_SESSION['lang']['tujuan']."</td>
                <td>:</td>
                <td><input type=text id='tujuan' class='myinputtext' style='width:200px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
            
            
        </table>
    </fieldset><br />
    <div style=clear:both;padding:3px; ></div> 
    <div  id=\"tombolHeader\">
        <button class=mybutton id=dtlAbn onclick=add_detail('pabrik_slave_dispacth','".$arr."')>".$_SESSION['lang']['save']."</button>
        <button class=mybutton id=cancelAbn onclick=displayList()>".$_SESSION['lang']['cancel']."</button>
    </div>";
    CLOSE_BOX();
    ?>
</div>
<div id="detailEntry" style="display:none">
    <?php
    OPEN_BOX();
    ?>
    <div id="addRow_table">
        <fieldset  style="float:left">
            <legend><?php echo $_SESSION['lang']['detail'] ?></legend>
            <div id="detailIsi">
            </div>
            <table cellspacing="1" border="0" style="width:500px;">
                <tr><td id="tombol">

                    </td></tr>
            </table>
        </fieldset>
    </div><br />
    <br />
    <!-- <div style="overflow:auto; height:300px; clear:both;">
        <fieldset  style="float:left;">
            <legend><?php echo $_SESSION['lang']['datatersimpan'] ?></legend>
            <table cellspacing='1' border='0' class='sortable' style='width:600px'>
                <thead>
                    <tr class="rowheader">
                        <td align='center'>No.</td>
                        <td align='center'><?php echo $_SESSION['lang']['noTiket'] ?></td>
                        <td align='center'><?php echo $_SESSION['lang']['komoditi'] ?></td>
                        <td align='center'><?php echo $_SESSION['lang']['beratBersih'] ?></td>
                        <td align='center'>Action</td>
                    </tr>
                </thead>
                <tbody id="contentDetail">

                </tbody>
            </table>
        </fieldset>
    </div> -->
    <?php
    CLOSE_BOX();
    ?>
</div>
<?php
echo close_body();
?>