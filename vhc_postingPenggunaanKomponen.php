<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="application/javascript" src="js/vhc_postingPenggunaanKomponen.js?v=2.1"></script>
<div id="action_list">
<?php
OPEN_BOX('','<span class=judul>'.getMenu('vhc_postingPenggunaanKomponen').'</span>');
$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
$rPeriode=fetchData($sPeriode);
if(count($rPeriode)!=0){
    foreach($rPeriode as $bars=>$val){
        $optperiode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
    }   
}

echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
           <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		echo $_SESSION['lang']['notransaksi']." : <input type=text id=txtsearch   style=width:150px size=20  maxlength=30 class=myinputtext>";
		echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext  style=width:150px id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>";
		echo $_SESSION['lang']['periode']." : <select id=periodecari style=width:150px>".$optperiode."</select>";
		echo"&nbsp;<button class=mybutton onclick=load_new_data()>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
         </table> "; 
?>
</div>
<?php
CLOSE_BOX();
?>
<div id="list_ganti">
<?php OPEN_BOX()?>
<div id="contain"><script>load_new_data()</script></div>
<?php CLOSE_BOX()?>
</div>
<?php 
echo close_body();
?>