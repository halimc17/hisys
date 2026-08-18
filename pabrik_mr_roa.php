<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
    exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript">
    function add_new_data() {
        document.getElementById('headher').style.display = "block";
        document.getElementById('listData').style.display = "none";
        unlockForm();
        statFrm = 0;
    }
    nmTmblDone = '<?php echo $_SESSION['lang']['done'] ?>';
    nmTmblCancel = '<?php echo $_SESSION['lang']['cancel'] ?>';
</script>
<script language="javascript" src="js/pabrik_mr_roa.js?v=1.1.2"></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_mr_roa').'</span>');
?>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
    <?php
    $optjenis="<option value=''>".$_SESSION['lang']['all']."</option>";
    $optjns=$optStation="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str="select jenis,nama from ".$dbname.".pabrik_5mr_roa_jenis";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $optjenis.="<option value=".$bar['jenis'].">".$bar['nama']."</option>";
        $optjns.="<option value=".$bar['jenis'].">".$bar['nama']."</option>";
    }
    $str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi "
            . " where induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('STATION','MAINTENANCE')";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $optStation.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
    }
    echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
    echo "<table border=0><tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td><input type=text class='myinputtext' id='tglCr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' readonly/> s.d <input type=text class='myinputtext' id='tglCr2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' readonly/></td>";
    // echo "<td>".$_SESSION['lang']['jenis'] . "</td><td>:</td><td><select id=jnsCr style='width:150px;'>".$optjenis."</select></td>";
    echo"<td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";

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
        <!--display data-->
            <table cellpadding='5' cellspacing='1' border='0' class='sortable'>
                <thead>
                    <tr class='rowheader'>
                        <td align='center'><? echo $_SESSION['lang']['nourut']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['tanggal']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['station']; ?></td>
                        <?php
                            $sParam="select * from ".$dbname.".pabrik_5mr_roa_jenis ";
                            $rParam=fetchData($sParam);
                            foreach($rParam as $row=>$lstParam){
                                echo"<td align='center'>".ucfirst(strtolower($lstParam['nama']))."</td>";
                            }
                        ?>
                        <td align='center'><? echo $_SESSION['lang']['updateby']; ?></td>
                        <td align='center' colspan=2><? echo $_SESSION['lang']['action']; ?></td>
                    </tr>
                </thead>
                <tbody id='contain'>

                </tbody>
            </table>
            <script type="text/javascript">loadData(0);</script>
        </div>
    <?php CLOSE_BOX() ?>
</div>

<div id="headher" style="display:none">
    <?php
    OPEN_BOX();
//$optTipePot 
  echo"
    <fieldset style=\"float:left\">
        <legend>".$_SESSION['lang']['form']."</legend>
        <table cellspacing=\"1\" border=\"0\">
            <tr><td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td><input type=text class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' style=\"width:145px;\" readonly/></td></tr>
            <tr><td>".$_SESSION['lang']['station']."</td>
                <td>:</td>
                <td><select id=\"station\" name=\"station\" style=\"width:155px;\">".$optStation."</select></td></tr>
            <tr><td>".$_SESSION['lang']['jenis']."</td><td>:</td>
             <td><select id=\"jenis\" name=\"jenis\" style=\"width:155px;\" onchange=getTable() >".$optjns."</select></td></tr>";
        echo"<tr><td colspan=3><div id=dataIsian></div></td>
             </tr>
            <tr>
                <td colspan=\"3\">
                    <div id=\"tombolHeader\">
                        <button class=mybutton id=dtlAbn onclick=saveDt()>".$_SESSION['lang']['save']."</button>
                        <button class=mybutton id=cancelAbn onclick=displayList()>".$_SESSION['lang']['done']."</button>
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>";
    CLOSE_BOX();
    ?>
</div>
<?php
echo close_body();
?>