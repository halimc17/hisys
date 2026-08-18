<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zLib.php');
?>
<script language=javascript src='js/log_2daftarcapex.js'></script>
<?php
OPEN_BOX('','<span class=judul>'.getMenu('log_2daftarcapex').'</span>');
echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>			 
			 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
			   <img class=delliconBig src=images/orgicon.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
			 <td><fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>";
	    echo "<table border=0><tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td><input type=text id='notranscr' class='myinputtext' style='width:180px;' onkeypress='return tanpa_kutip(event)' /></td></tr><tr>";
		echo "<td>".$_SESSION['lang']['tanggal'] . "</td><td>:</td><td><input type=text class='myinputtext' id='tglcr' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' readonly/> s.d <input type=text class=myinputtext id=tglcarismp onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td>";
	    echo"<td colspan=2></td><td><button class=mybutton onclick=loadData(0)>" . $_SESSION['lang']['find'] . "</button></td></tr>";
	    echo"</table>";
	    echo"</fieldset></td>
	 </tr>
	 </table>";


	 
CLOSE_BOX();
echo"<div id=listdata style='display:block'>";
OPEN_BOX();
echo"<fieldset>
		<legend><b>".$_SESSION['lang']['list']."</legend>
		    <table class=sortable cellspacing=1 border=0>
            <thead>
            <tr class=rowheader>    
                <td align=center >".$_SESSION['lang']['nourut']."</td>
                <td align=center >" . $_SESSION['lang']['notransaksi'] . "</td>
           		<td align=center >" . $_SESSION['lang']['tanggalpengajuan'] . "</td>
           		<td align=center >" . $_SESSION['lang']['namapt'] . "</td>
                <td align=center >" . $_SESSION['lang']['status'] . "</td>
                <td align=center >No. PP</td>
                <td align=center >Action</td>
            </tr>
        	</thead>
        	<tbody  id=contain>";

	echo"<tfoot id='footData'>
          </tfoot></tbody></table></fieldset>";
CLOSE_BOX();
echo "</div>";
echo close_body('');
?><script type="text/javascript">loadData(0);</script>