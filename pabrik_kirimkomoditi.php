<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
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
<script language="javascript" src="js/pabrik_kirimkomoditi.js"></script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper("Pengiriman Komoditi").'</span>');
?>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
    <?php
    #jenis transaksi berdasarkan enum pada table pabrik_blk_kirimht
    $optagama.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $arragama=getEnum($dbname,'pabrik_blk_kirimht','jenis');
    foreach($arragama as $kei=>$fal){
            $optagama.="<option value='".$kei."'>".$fal."</option>";
    }

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
        if(intval($fisKirim[$row['nokontrak']])<$totFsk){
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
    echo "<table border=0><tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td><input type=text id='notransaksiCr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>";
    echo "<td>".$_SESSION['lang']['jenis'] . "</td><td>:</td><td><select id=jnsCr style='width:150px;' onchange=lockForm()>" . $optagama . "</select></td>";
    echo "<td>".$_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class='myinputtext' id='tglCr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td></tr>";
    echo"<tr><td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td><td><input type=text id='nokontrakCr' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>
             <td>".$_SESSION['lang']['noberitaacara']."</td><td>:</td><td><input type=text id='nobaCr' class='myinputtext' style='width:145px;' onkeypress='return tanpa_kutip(event)' /></td>
        </tr>";
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
    <?php OPEN_BOX() ?>
    <fieldset style="float:left;">
        <legend><?php echo $_SESSION['lang']['list'] ?></legend>
        <!--display data-->
        <div>
            <table cellpadding='1' cellspacing='1' border='0' class='sortable'>
                <thead>
                    <tr class='rowheader'>
                        <td align='center'>No.</td>
                        <td align='center'><? echo $_SESSION['lang']['notransaksi']; ?></td>
                        <!-- <td><? echo $_SESSION['lang']['unit']; ?></td> -->
                        <td align='center'><? echo $_SESSION['lang']['jenis']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['tanggal']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['NoKontrak']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['noberitaacara']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['lokasi']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['updateby']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['action']; ?></td>
                    </tr>
                </thead>
                <tbody id='contain'>

                </tbody>
                <tfoot id='footData'>
                </tfoot>
            </table>
            <script type="text/javascript">loadData(0);</script>
        </div>
    </fieldset>
    <?php CLOSE_BOX() ?>
</div>

<div id="headher" style="display:none">
    <?php
    OPEN_BOX();
//$optTipePot
    $sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
    $rOrg=fetchdata($sOrg);
  echo"
    <fieldset style=\"float:left\">
        <legend>".$_SESSION['lang']['header']."</legend>
        <table cellspacing=\"1\" border=\"0\">
            <tr><td>".$_SESSION['lang']['notransaksi']."</td>
                <td>:</td>
                <td><input type=text id='notransaksi' class='myinputtext' style='width:150px;' disabled /></td>
            <td>&nbsp;
            </td>
			<td>".$_SESSION['lang']['unit']."</td>
                <td>:</td><td>
                <select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px;\">";
                foreach($rOrg as $row){
                echo"<option value='".$row['kodeorganisasi']."'>".$row['kodeorganisasi']." - ".$row['namaorganisasi']."</option>";    
                }
                echo"</select>";
        
        echo"</td>
            </tr>
            <tr><td>".$_SESSION['lang']['jenis']."</td>
                <td>:</td>
                <td><select id=\"jenis\" name=\"jenis\" style=\"width:155px;\" >".$optagama."</select>
                </td>
            <td>&nbsp;
            </td>
            <td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td><input type=text class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' style=\"width:145px;\" /></td>
            </tr>
            
            <tr><td>".$_SESSION['lang']['NoKontrak']."</td>
                <td>:</td>
                <td><select id='nokontrak' style='width:155px;'>".$optKtrk."</select></td>
            <td>&nbsp;
            </td>
            <td>".$_SESSION['lang']['noberitaacara']."</td>
                <td>:</td>
                <td><input type=text id='noba' class='myinputtext' style='width:145px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
            <tr><td>".$_SESSION['lang']['lokasi']."</td>
                <td>:</td>
                <td><input type=text id='lokasi' class='myinputtext' style='width:150px;' onkeypress='return tanpa_kutip(event)' /></td>
            </tr>
            <tr>
                <td></td><td></td><td colspan=\"3\">
                    <div id=\"tombolHeader\">
                        <button class=mybutton id=dtlAbn onclick=add_detail()>".$_SESSION['lang']['save']."</button>
                        <button class=mybutton id=cancelAbn onclick=displayList()>".$_SESSION['lang']['cancel']."</button>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>";
    CLOSE_BOX();
    ?>
</div>
<div id="detailEntry" style="display:none">
    <?php
    OPEN_BOX();
    ?>
    <div id="addRow_table">
        <fieldset  style="width:490px">
            <legend><?php echo $_SESSION['lang']['detail'] ?></legend>
            <div id="detailIsi">
            </div>
            <table cellspacing="1" border="0" style="width:100%;">
                <tr><td id="tombol">

                    </td></tr>
            </table>
        </fieldset>
    </div><br />
    <br />
    <div style="overflow:auto; height:200px; clear:both;">
        <fieldset  style="float:left;">
            <legend><?php echo $_SESSION['lang']['datatersimpan'] ?></legend>
            <table cellspacing='1' border='0' class='sortable' style='width:490px'>
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
    </div>
    <?php
    CLOSE_BOX();
    ?>
</div>
<?php
echo close_body();
?>