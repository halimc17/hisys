<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="application/javascript" src="js/pabrik_3posting_perawatan_mesin.js"></script>

<input type="hidden" id="proses" name="proses" value="insert"  />
<div id="action_list">
<?php
	 $arrPil=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
	 $optPost="";
	 foreach($arrPil as $id => $ky)
	 {
		 $optPost.="<option value=".$id.">".$ky."</option>";
	 }
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['postingPerawatanMesin']).'</span>');

echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>

	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['notransaksi']." : <input type=text id=txtsearch size=18 maxlength=30 class=myinputtext>&nbsp;";
			echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=7 maxlength=10 />&nbsp;";
			echo $_SESSION['lang']['posting']." : <select id=statusPosting name=statusPosting><option value=''>".$_SESSION['lang']['all']."".$optPost."</option></select>&nbsp;";
			echo"<button class=mybutton onclick=cariTransaksi()>".$_SESSION['lang']['find']."</button>";
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
<fieldset>
<legend><?php echo $_SESSION['lang']['list']?></legend>
<table cellspacing="1" border="0" class="sortable">
<thead>
<tr class="rowheader">
<td align=center>No.</td>
<td align=center style=width:160px><?php echo $_SESSION['lang']['notransaksi']?></td>
<td align=center style=width:90px><?php echo $_SESSION['lang']['tanggal']?></td>
<td align=center><?php echo $_SESSION['lang']['shift']?></td>
<td align=center style=width:70px><?php echo $_SESSION['lang']['statasiun']?></td>
<td align=center><?php echo $_SESSION['lang']['mesin']?></td>
<td align=center style=width:120px><?php echo $_SESSION['lang']['jammulai']?></td>
<td align=center style=width:120px><?php echo $_SESSION['lang']['jamselesai']?></td>
<td align=center>Action</td>
</tr>
</thead>
<tbody id="contain">
<script>loadNData()</script>
</tbody>
</table>
</fieldset>
<?php CLOSE_BOX()?>
</div>


<?php 
echo close_body();
?>