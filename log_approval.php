\<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();

include('master_mainMenu.php');
//<script type="text/javascript" src="js/log_approval.js?v=1.2"></script>
OPEN_BOX('','<span class=judul>'.getMenu('log_approval').'</span>');
?>
<script language="javascript" src="js/zMaster.js?v=1.5"></script>
<script language="javascript" src='js/vhc_detailkmhm.js'></script>
<script language=javascript src='js/log_approval_tbs.js?v=<?php echo time(); ?>'></script>
<script type="text/javascript" src="js/log_approval_spk.js?v=1.3"></script>
<script type="text/javascript" src="js/log_approval_dtk.js?v=1.9"></script>
<script type="text/javascript" src="js/log_approval_rkb.js?v=1.3"></script>
<script type="text/javascript" src="js/log_approval_bapp.js?v=1.9"></script>
<script type="text/javascript" src="js/log_approval_rkh.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_bansos.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_fee.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_srv.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_grl.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_pdo.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_sp.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_kontan.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_borongan.js?v=1.2"></script>
<script type="text/javascript" src="js/pmn_hargabelitbs.js?v=1.2"></script>
<script type="text/javascript" src="js/log_approval_perjalanandinas.js?v=<?php echo time(); ?>'>"></script>
<script type="text/javascript" src="js/log_link.js?v=1.4"></script>
<script languange=javascript1.2 src='js/formReport.js?v=1.1'></script>
<script language=javascript src='js/log_approval.js?v=<?php echo time(); ?>'></script>
<script type="text/javascript" src="js/log_approval_pta.js?v=<?php echo time(); ?>"></script>
<script language=javascript src='js/log_approval_kasbank.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_gantidokumen.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_bafinger.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_kpi.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_ijns.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_ijs.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_ijsc.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_ijnsc.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_kbpt.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/log_approval_cbs.js?v=<?php echo time(); ?>'></script>

<?php
$xxx = $_GET['xxx'];
if($xxx!=''){
	echo"<script>window.addEventListener('load',function(){getdetail('".$xxx."')});</script>";
}
//List Persetujuan
$optListPersetujuan = "<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select distinct jenis, nama from ".$dbname.".setup_jenisapproval where status='1' order by nama asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optListPersetujuan.="<option value='".$bar['jenis']."'>".$bar['nama']." (".$bar['jenis'].")</option>";
}
echo"<table>
	<tr valign=moiddle>
		<td align=center style='width:100px;cursor:pointer;' onclick=showalllist()>
		<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
		</td>
		<td>
			<fieldset><legend>".$_SESSION['lang']['find']."</legend>
			<table>
				<tr>
					<td>".$_SESSION['lang']['jenispersetujuan']."</td>
					<td>:</td>
					<td>
						<select id=crjenispersetujuan>".$optListPersetujuan."</select>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=loaddata()>".$_SESSION['lang']['find']."</button>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>"; 
CLOSE_BOX();

OPEN_BOX();
echo"<div id='body1'>
		<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['jenispersetujuan']."</td>
				<td align=center>".$_SESSION['lang']['jumlah']."</td>
			</tr>
			</thead>
			<tbody id='container'>
				<script>loaddata(0)</script>
			</tbody>
		</table>
</div>";

echo"<div id='body2' style='display:none;overflow:auto;min-height:425px'; ></div>";
CLOSE_BOX();

close_body();
?>