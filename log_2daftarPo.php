<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_2daftarPo').'</span>');

?>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/log_2daftarPo.js?v=<?php echo time(); ?>"></script>
<div id="action_list">
<?php
$arrFilter=array("1"=>"Release PO/SO","0"=>"Unrelease PO/SO","3"=>"Tutup PO : Become Out Standing","4"=>"Tutup PO : Cancel PO/SO");
$optRls="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrFilter as $row=>$lst){
	$optRls.="<option value='".$row."'>".$lst."</option>";
}
$arrBeli=array("0"=>"Pusat","1"=>"Lokal");
$optRls2="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrBeli as $row=>$lst){
	if(($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') && ($_SESSION['empl']['tipelokasitugas'] != 'KANWIL')){
		if($row!=0){
			$optRls2.="<option value='".$row."'>".$lst."</option>";
		}
	}else{
		$optRls2.="<option value='".$row."'>".$lst."</option>";	
	}
	
}
$arrData="";
echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=loaddata()>
	   <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo "<table><tr><td>".$_SESSION['lang']['nopo']."</td><td>:</td><td><input type=text id=txtsearch size=25 maxlength=30 style=width:160px class=myinputtext></td>";
			echo "<td>".$_SESSION['lang']['tgl_po']."</td><td>:</td><td><input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /> s.d <input type=text class=myinputtext id=tgl_carismp onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>";
			echo "<tr><td>".$_SESSION['lang']['status']."</td><td>:</td><td><select id=statr style=width:165px>".$optRls."</select></td><td>".$_SESSION['lang']['lokasiBeli']."</td><td>:</td><td><select id=lokPus style=width:198px>".$optRls2."</select></td></tr><tr>";
			echo"<td colspan=2></td><td><button class=mybutton onclick=cariPo()>".$_SESSION['lang']['find']."</button>&nbsp;<button onclick=\"zExcel(event,'log_slave_2daftarPo.php','##txtsearch##tgl_cari##tgl_carismp##statr##lokPus')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr></table>";
echo"</fieldset></td>
     </tr>
	 </table> "; 
?>
</div>
<?php
CLOSE_BOX(); //1 C //2 O
?>
<div id=list_pp_verication>
<?php OPEN_BOX();?>
<fieldset>
<legend><?php echo $_SESSION['lang']['list_po'];?></legend>
<div style="overflow:auto; height:420px;">
	 <table class="sortable" cellspacing="1" border="0">
	 <thead>
	 <tr class=rowheader>
	 <td align='center'>No.</td>
	 <td align='center'><?php echo $_SESSION['lang']['nopo']?></td>
	 <td align='center'><?php echo $_SESSION['lang']['tgl_po'];?></td> 
	 <td align='center'><?php echo $_SESSION['lang']['namaorganisasi'];?></td>
     <td align='center'><?php echo $_SESSION['lang']['status']?></td>
     <td align='center'><?php echo $_SESSION['lang']['tandatangan']?></td>
	  <?php		
				//for($i=1;$i<4;$i++)
//				 {
//					echo"<td align=center>Persetujuan".$i."</td>";
//				 }
	   ?>
	   <td align='center'>Action</td>
	 
	
	 </tr>
	 </thead>
	 <tbody id="contain">
	<script>loaddata()</script>
	  </tbody>
	 <tfoot>
	 </tfoot>
	 </table></div>
</fieldset
><?php
CLOSE_BOX();
?>
</div>
<input type="hidden" name="method" id="method"  /> 
<input type="hidden" id="no_po" name="no_po" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid']?>" />

<?
echo close_body();
?>