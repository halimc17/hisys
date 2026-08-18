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
<script language="javascript" src="js/pabrik_mr_metp.js?v=<?php echo time(); ?>"></script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_mr_metp').'</span>');
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
                        <td align='center'><? echo $_SESSION['lang']['nomor']; ?></td>
                        <td align='center'><? echo $_SESSION['lang']['tanggal']; ?></td>
                        <?php
                            $sParam="select * from ".$dbname.".pabrik_5mr_metp ";
                            $rParam=fetchData($sParam);
                            foreach($rParam as $row=>$lstParam){
                                echo"<td align='center'>".ucfirst(strtolower($lstParam['nama']))."</td>";
                            }
                        ?>
                        <!--<td align='center'><? echo $_SESSION['lang']['updateby']; ?></td>-->
                        <td colspan=2 align='center'><? echo $_SESSION['lang']['action']; ?></td>
                    </tr>
                </thead>
                <tbody id='contain'>

                </tbody>
                <tfoot id='footData'>
                </tfoot>
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
            <tr><td width=100px>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td><input type=text class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' style=\"width:145px;\" onchange=getTable() readonly/></td></tr>";
        echo"<tr><td colspan=30><div id=dataIsian></div></td>
             </tr>
            <tr>
                <td colspan=\"2\"></td>
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