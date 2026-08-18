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
        unlockForm();
        statFrm = 0;
    }
    nmTmblDone = '<?php echo $_SESSION['lang']['done'] ?>';
    nmTmblCancel = '<?php echo $_SESSION['lang']['cancel'] ?>';
</script>
<script language="javascript" src="js/pabrik_logmesin.js"></script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('pabrik_logmesin')).'</span>');
?>
<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
    <?php
    
    
    echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
     <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
       <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
     <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
       <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
     <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
    echo "<table border=0><tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td><input type=text class='myinputtext' id='tglCr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /> s.d <input type=text class='myinputtext' id='tglCr2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' /></td>";
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
     
    <fieldset style="float:left;width:99%">
        <legend><?php echo $_SESSION['lang']['list'] ?></legend>
        <!--display data-->
            <table cellpadding='1' cellspacing='1' border='0' class='sortable' style='width:100%'>
                <thead>
                    <tr class='rowheader'>
                        <td align='center'><? echo $_SESSION['lang']['tanggal']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['nourut']; ?></td>
                        <?php
                            $arrNama=array("HU"=>"HEATING UP","PR"=>"PROSES","CN"=>"COOLING DOWN","BN"=>"BREAKDOWN");
                            $arragama=getEnum($dbname,'pabrik_logmesin','klasifikasi');
                            foreach($arragama as $kei=>$fal){

                                echo"<td align='center'>".ucfirst(strtolower($arrNama[$fal]))."</td>";
                            }
                        ?>
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
  echo"
    <fieldset style=\"float:left;width:850px;\">
        <legend>".$_SESSION['lang']['form']."</legend>
        <table cellspacing=\"1\" border=\"0\">
            <tr><td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td align=left><input type=text class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' style=\"width:145px;\" onchange=getTable() /></td></tr>";
        echo"
        </table>
        <div id=dataIsian></div>
    </fieldset>";
    CLOSE_BOX();
    ?>
</div>
<?php
echo close_body();
?>