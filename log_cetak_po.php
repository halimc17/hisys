<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
//require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_cetak_po').'</span>');

?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_link.js"></script>
<script language=javascript src='js/log_cetak_po.js?v=<?php echo time(); ?>'></script>

<div id="action_list">
<?php


$arrFilter=array("1"=>"Release","2"=>"Unrelease","3"=>"Become Out Standing","4"=>"Close","5"=>"Cancel");
$optrelease="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrFilter as $row=>$lst){
	$optrelease.="<option value='".$row."'>".$lst."</option>";
}

#LIST NAMA SUPPLIER
$namasupplier="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$ssupp="select distinct supplierid,namasupplier from ".$dbname.".log_5supplier order by supplierid asc";
$qsupp=$owlPDO->query($ssupp) or die(print " Gagal: ".PDOException::getMessage());
$qsupp->setFetchMode(PDO::FETCH_ASSOC);
while($rsupp=$qsupp->fetch())
{
	$namasupplier.="<option value='".$rsupp['supplierid']."'>".$rsupp['namasupplier']." - ".$rsupp['supplierid']."</option>";
}

echo"<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
			echo $_SESSION['lang']['nopo']." : <input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>&nbsp;";
			echo $_SESSION['lang']['tgl_po']." &nbsp;&nbsp;&nbsp;: <input size=25 maxlength=30 type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>";
			echo "<br>";
			echo $_SESSION['lang']['release_po'] . " : <select id=statusreal style=\"width:174px;\">" . $optrelease . "</select>
			";
			echo $_SESSION['lang']['vendor'] . " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;: <input type=text id=nmsupplier  size=25 maxlength=30 class=myinputtext>
			";
			
			echo"<button class=mybutton onclick=cariPo()>".$_SESSION['lang']['find']."</button>";


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

<div style="overflow:auto; height:450px;">
	 <table class="sortable" cellpadding='5' cellspacing="1" border="0" style="width: 100%;"> 
	 <thead>
	 <tr class=rowheader>
	 <th align="center">No.</td>
	 <th align="center"><?php echo $_SESSION['lang']['nopo']?></td>
	 <th align="center"><?php echo $_SESSION['lang']['detail']?></td>
	 <th align="center"><?php echo $_SESSION['lang']['tgl_po'];?></td> 
	 <th align="center"><?php echo $_SESSION['lang']['namaorganisasi'];?></td>
	 <th align="center"><?php echo $_SESSION['lang']['vendor'];?></td>
	 <th align="center"><?php echo $_SESSION['lang']['tipe'];?></td>
     <th align="center"><?php echo $_SESSION['lang']['status']?></td>
    
<!--     <td><?php echo $_SESSION['lang']['tandatangan']?></td>-->
	  <?php		
				//for($i=1;$i<4;$i++)
//				 {
//					echo"<td align=center>Persetujuan".$i."</td>";
//				 }
	   ?>
	   <th align="center">Action</td>
	  <th align="center"><?php echo $_SESSION['lang']['gudang']?></td>
	
	 </tr>
	 </thead>
	 <tbody id="contain"></tbody>
	 <tfoot id='containfoot'>
	 </tfoot>
	 </table></div>
	 <script>cariPo()</script>


<?php
CLOSE_BOX();
?>
</div>
<input type="hidden" name="method" id="method"  /> 
<input type="hidden" id="no_po" name="no_po" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid']?>" />

<?
echo close_body();
?>