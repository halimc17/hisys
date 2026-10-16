<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
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

#vendor yang pernah punya PO, diurutkan menurut nama
$nmvendor = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$arrVendor=array();
$res=fetchdata("select distinct kodesupplier from ".$dbname.".log_poht where kodesupplier<>''");
foreach($res as $val){
	$arrVendor[$val['kodesupplier']]=trim(isset($nmvendor[$val['kodesupplier']])?$nmvendor[$val['kodesupplier']]:$val['kodesupplier']);
}
asort($arrVendor);
$namasupplier="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrVendor as $kode=>$nama){
	$namasupplier.="<option value='".htmlspecialchars($kode,ENT_QUOTES)."'>".htmlspecialchars($nama)."</option>";
}

#tahun PO: default tahun sekarang, "Semua Tahun" untuk seluruh tahun
$tahunSkrg=date('Y');
$arrTahun=array($tahunSkrg=>$tahunSkrg);
$res=fetchdata("select distinct year(tanggal) as thn from ".$dbname.".log_poht where tanggal>'0000-00-00'");
foreach($res as $val){
	$arrTahun[$val['thn']]=$val['thn'];
}
krsort($arrTahun);
$optTahun="<option value=''>Semua Tahun</option>";
foreach($arrTahun as $thn){
	$optTahun.="<option value='".$thn."'".($thn==$tahunSkrg?" selected":"").">".$thn."</option>";
}

echo"<table>
     <tr valign=top>
	 <td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
		<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
	</td>
	 <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>
		<table>
		<tr>
			<td>".$_SESSION['lang']['tahun']." PO</td>
			<td>:</td>
			<td style='width:220px'>
				<select id=filterTahun class=select2 style='width:100%'>".$optTahun."</select>
			</td>
			<td style='padding-left:15px;'>".$_SESSION['lang']['release_po']."</td>
			<td>:</td>
			<td style='width:260px'>
				<select id=statusreal class=select2 style='width:100%'>".$optrelease."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nopo']."</td>
			<td>:</td>
			<td style='width:220px'>
				<input type=text id=txtsearch style='width:100%;box-sizing:border-box' maxlength=30 class=myinputtext>
			</td>
			<td style='padding-left:15px;'>".$_SESSION['lang']['tgl_po']."</td>
			<td>:</td>
			<td style='width:260px'>
				<input type=text class=myinputtext id=tgl_cari style='width:100%;box-sizing:border-box' onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 readonly/>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['vendor']."</td>
			<td>:</td>
			<td style='width:220px'>
				<select id=kodesupplier class=select2 style='width:100%'>".$namasupplier."</select>
			</td>
			<td colspan=3 style='padding-left:15px;'>
				<button class=mybutton onclick=cariPo()>".$_SESSION['lang']['find']."</button>
				<button class=mybutton onclick=exportExcelCPO()>".$_SESSION['lang']['excel']."</button>
				<button class=mybutton onclick=exportPdfCPO()>PDF</button>
			</td>
		</tr>
		</table>
		</fieldset></td>
     </tr>
	 </table> ";
?>
</div>
<?php
CLOSE_BOX(); //1 C //2 O
?>
<div id=list_pp_verication>
<?php OPEN_BOX();?>

<div style="overflow:auto; height:68vh; width:100%;">
	 <table class="sortable" cellpadding='5' cellspacing="1" border="0" style="width: 100%;"> 
	 <thead>
	 <tr class=rowheader>
	 <th align="center" style="width:30px">No.</th>
	 <th align="center" style="width:200px"><?php echo $_SESSION['lang']['nopo']?></th>
	 <th align="center" style="width:60px"><?php echo $_SESSION['lang']['detail']?></th>
	 <th align="center" style="width:90px"><?php echo $_SESSION['lang']['tgl_po'];?></th> 
	 <th align="center" style="width:200px"><?php echo $_SESSION['lang']['namaorganisasi'];?></th>
	 <th align="center" style="width:220px"><?php echo $_SESSION['lang']['vendor'];?></th>
	 <th align="center" style="width:60px"><?php echo $_SESSION['lang']['tipe'];?></th>
     <th align="center" style="width:120px"><?php echo $_SESSION['lang']['status']?></th>
    
<!--     <td><?php echo $_SESSION['lang']['tandatangan']?></td>-->
	  <?php		
				//for($i=1;$i<4;$i++)
//				 {
//					echo"<td align=center>Persetujuan".$i."</td>";
//				 }
	   ?>
	   <th align="center" style="width:80px">Action</th>
	  <th align="center" style="width:230px"><?php echo $_SESSION['lang']['gudang']?></th>
	
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