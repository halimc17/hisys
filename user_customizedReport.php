<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/admin_validation.php');
echo open_body();
include('master_mainMenu.php');
?>
<script src=js/tool_QueryGenerator.js></script>
<?
echo OPEN_BOX('','<span class=judul>'.getMenu('user_customizedReport').'</span><br>');
echo "<fieldset><div style='width:100%;height:450px;overflow:auto;'>
<table class=sortable cellspacing=1 padding=0 border=0>
	  <head>
			<tr class=rowheader>
				<td>No</td>
				<td>Report Title</td>
				<td>Create Date</td>
				<td>Designer</td>
				<td>HTML</td>
				<td>excel</td>
				<td>PDF</td>				
				<td>Browse</td>
				<td>Owner</td>
			</tr>
		</thead>
		<tbody>";
	$tab='';
	$capStatus='';
	$str="select a.* from ".$dbname.".tool_userdefinedreport a left join ".$dbname.".tool_userdefinedreport_user b
	      on a.rnumber=b.rnumber where b.username='".$_SESSION['standard']['username']."'
		  and a.status=1 order by rnumber"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		if($bar->status=='0'){
			$capStatus='Not Published';
		}else if($bar->status=='1'){
			$capStatus='Active';
		}else{
			$capStatus='Deleted';
		}
		$user='';
			$status=$capStatus;
			if($bar->html=='1'){
				$html='Yes';
			}
			else{
				$html='No';
			}
			if($bar->speadsheet=='1'){
				$excel='Yes';
			}else{
				$excel='No';
			}
			if($bar->pdf=='1'){
				$pdf='Yes';
			}else{
				$pdf='No';
			}
		$tab.="<tr class=rowcontent>
				<td>".$bar->rnumber."</td>
				<td>".$bar->namalaporan."</td>
				<td>".tanggalnormal($bar->createdate)."</td>
				<td>".$bar->owner."</td>
				<td>".$html."</td>
				<td>".$excel."</td>
				<td>".$pdf."</td>
				<td align=center><img src='images/onebit_02.png' class=zImgBtn style='cursor:pointer;' onclick=browseR(event,'".$bar->rnumber."') title='Try Report'></td>
			    <td>".$bar->owner."</td>
			   </tr>";	
	}
	echo $tab;
echo "</tbody><tfoot></tfoot></table></div></fieldset>";
CLOSE_BOX();
echo close_body();	
?>