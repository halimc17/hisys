<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_release_po').'</span>');

?>
<script language="javascript" src="js/zMaster.js"></script>
<!--<script type="text/javascript" src="js/log_persetujuan_po.js"></script>
-->
<script type="text/javascript" src="js/log_release_po.js?<?= time(); ?>"></script>
<script type="text/javascript" src="js/log_link.js"></script>
<div id="action_list">
<?php
$jenisApp = "PO";
$countApp = getCountApproval($jenisApp,'');
$arrFilter=array("1"=>"Release","2"=>"Unrelease","3"=>"Become Out Standing","4"=>"Close","5"=>"Cancel");
$optFilter="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrFilter as $row=>$lst){
	$optFilter.="<option value='".$row."'>".$lst."</option>";
}

$namasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$optFilterSupplier="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".log_poht group by kodesupplier";
$res=fetchdata($str);
foreach($res as $val){
	$optFilterSupplier.="<option value='".$val['kodesupplier']."'>".$namasupplier[$val['kodesupplier']]."</option>";

}

echo "<table>
     <tr valign=moiddle>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylistdata()>
		<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
	</td>
	<td>
	<fieldset>
	<legend>".$_SESSION['lang']['find']."</legend>"; 
	echo "<table>
		<tr>
			<td>".$_SESSION['lang']['nopo']."</td>
			<td>:</td>
			<td>
				<input type=text id=txtsearch_rpo style=width:150px maxlength=30 class=myinputtext>
			</td>
			<td style='padding-left:10px;'>".$_SESSION['lang']['tgl_po']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=tgl_cari_rpo onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
			</td>

			</tr>
			</tr>
		<tr>
			<td style='padding-left:10px;'>".$_SESSION['lang']['status']."</td>
			<td>:</td>
			<td>
				<select id=filterId>".$optFilter."</select>
			</td>
			<td style='padding-left:10px;'>".$_SESSION['lang']['supplier']."</td>
			<td>:</td>
			<td>
				<select id=filterSupplier class=select2 >".$optFilterSupplier."</select>
			</td>
			<td>
				<img id='filterSupplier' onclick=z.elSearch('filterSupplier',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>
			</td>
		</tr>
		</table>
	</fieldset>
	</td>

	<td>
	<fieldset>
	<legend>".$_SESSION['lang']['info']."</legend>"; 
	// joky : penambahan info 4 botton
	echo "<table>
	<style>
	.s_t .icon {
		display: inline-block;
		width: 20px;
		text-align: center;
		font-weight: bold;
	  }
	  
	  .s_t .icon::before {
		content: '';
	  }
	  .s_t{
		width:150px;
		padding:5px;
	}
	  
	</style>
		<tr>
			<td class='s_t'><span class='icon'>&#x1F5D9;</span> Cancel</td>
			<td>:</td>
			<td>Mengembalikan item PO ke <b>PERBANDINGAN HARGA</b> dengan membentuk nomor transaksi yang baru.</td>
		</tr>
		<tr>
			<td class='s_t'><span class='icon'>&#10007;</span> Close</td>
			<td>:</td>
			<td>Menutup PO yang sedang aktif, agar PO tersebut tidak dapat dilanjutkan.</td>
			</tr>
		<tr>
			<td class='s_t'><span class='icon'>&#128203;</span> Become Out Standing</td>
			<td>:</td>
			<td>Kembali ke <b>PERBANDINGAN HARGA</b> sesuai QTY yang di kembalikan, membentuk no PR baru dan <b>NO.PR</b> lama dijadikan referensi</td>
		</tr>
		</table>
	</fieldset>
	</td>


	</tr>
</table> "; 
?>
</div>
<?php
CLOSE_BOX();
?>
<div id=list_pp_verication>
<?php OPEN_BOX();?>

<div style="overflow:auto;height:60vh">
	<table class="sortable" cellspacing="1" cellpadding="3" border="0">
		<thead>
		<tr class=rowheader>
			<th rowspan='2' align="center">No.</th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['nopo']?></th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['tgl_po'];?></th> 
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['perusahaan'];?></th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['supplier'];?></th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['uraian'];?></th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['chat'];?></th>
			<th colspan='<?php echo $countApp;?>' align="center" width="50px"><?php echo $_SESSION['lang']['status'];?> Persetujuan</th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['status'];?></th>
			<th rowspan='2' align="center">PIC</th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['keterangan']; ?></th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['gudang']; ?></th>
			<th rowspan='2' align="center"><?php echo $_SESSION['lang']['detail']; ?></th>
			<th rowspan='2' align="center" colspan="3" align="center"><?php echo $_SESSION['lang']['action']; ?></th>
		</tr>
		<tr class=rowheader>
		<?php
			for($i=1;$i<=$countApp;$i++){
				echo"<th align=center>".$_SESSION['lang']['persetujuan']. "".$i."</th>";
			}
		?>
		</tr>
		</thead>
		<tbody id="contain">
			<script>loadData(0);</script>
		</tbody>
	</table>* Display items base on release date</div>
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