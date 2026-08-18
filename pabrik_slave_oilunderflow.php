<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

function valdefinition($post){
	$result = "";
	if(isset($_POST[$post])){
		$result	= $_POST[$post];
	}
	return $result;
}
$proses = "";
$where  = "";
$sInsert = "";
if(isset($_GET['proses'])){
	$proses	= $_GET['proses'];
}
$pabrik		= valdefinition('pabrik');
$tanggal	= valdefinition('tanggal');
$oil		= valdefinition('oil');
$moisture	= valdefinition('moisture');
$sludge		= valdefinition('sludge');
$keterangan	= valdefinition('keterangan');


$result['err'] = "false";
switch($proses){
	default:
		OPEN_BOX('','<span class=judul></span>');
		 $html = "";
		?>
	<fieldset style="margin-bottom: 10px;">
		<legend id="title_Form"><b><?php echo $_SESSION['lang']['list']; ?></b></legend>
		<table class="sortable" cellspacing="1" style="" border="0">
			<thead>
				<tr>
					<th width="20">No.</th>
					<th width="100"><?php echo $_SESSION['lang']['tanggal']; ?></th>
					<th width="100"><?php echo $_SESSION['lang']['oil']; ?></th>
					<th width="100"><?php echo $_SESSION['lang']['moisture']; ?></th>
					<th width="100"><?php echo $_SESSION['lang']['sludge']; ?></th>
					<th width="300"><?php echo $_SESSION['lang']['keterangan']; ?></th>
					<th width="300"><?php echo $_SESSION['lang']['updateby']; ?></th>
					<th width="50">#</th>
				</tr>
			</thead>
			<tbody>
			<?php
			if($tanggal != ""){
				$where .= " and DATE_FORMAT(tanggal,'%Y-%m') = '".date('Y-m',strtotime($tanggal))."'";
			}
			if($pabrik != ""){
				$where .= " and pabrik = '".$pabrik."'";
			}
				$query = "select * from pabrik_oilunderflow where 1=1 ".$where;
				$r = fetchData($query);
				if(count($r) > 0){
					$num = 1;
					for($i=0; $i<count($r); $i++){ ?>
						<tr class="rowcontent">
							<td align="center"><?php echo $num; ?></td>
							<td align="center"><?php echo date('d-m-Y',strtotime($r[$i]['tanggal'])); ?></td>
							<td align="center"><?php echo $r[$i]['oil']; ?></td>
							<td align="center"><?php echo $r[$i]['moisture']; ?></td>
							<td align="center"><?php echo $r[$i]['sludge']; ?></td>
							<td align="left"><?php echo $r[$i]['keterangan']; ?></td>
							<td align="left"><?php echo getNamaKaryawan($r[$i]['updateby']); ?></td>
							<td align="center">
							<img src="images/application/application_edit.png" class="resicon" title="Edit" param="tanggal,pabrik" data='<?php echo $r[$i]['tanggal']; ?>,<?php echo $r[$i]['pabrik']; ?>' onclick="editList(this);">
							<img src="images/application/application_delete.png" class="resicon" title="Delete" param="tanggal,pabrik" data='<?php echo $r[$i]['tanggal']; ?>,<?php echo $r[$i]['pabrik']; ?>' onclick="deleteList(this);">
							</td>
						</tr>
				<?php	
						$num++;
					}
				}else{
					echo "<tr><td colspan='8' align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}
			?>
			</tbody>
		</table>
<?php
		echo $html;
		CLOSE_BOX();
	break;
	case 'insert':
			if($tanggal != ""){
				$where .= " and tanggal = '".date('Y-m-d',strtotime($tanggal))."'";
			}
			if($pabrik != ""){
				$where .= " and pabrik = '".$pabrik."'";
			}
			$str = "select * from " . $dbname . ".pabrik_oilunderflow where 1=1 ".$where." limit 1";
			$strPDO = fetchData($str);
			if(count($strPDO) > 0){
				$sInsert .= "UPDATE ".$dbname.".pabrik_oilunderflow set
				oil = '".$oil."',
				moisture = '".$moisture."',
				sludge = '".$sludge."',
				keterangan =  '".$keterangan."',
				updateby='" . $_SESSION['standard']['userid'] . "'
				where 1=1 ".$where.";";
			}else{
				$sInsert .= "insert into ".$dbname.".pabrik_oilunderflow (tanggal,pabrik,oil,moisture,sludge,keterangan,createby)
				value (
				'".date("Y-m-d",strtotime($tanggal))."',
				'".$pabrik."',
				'".$oil."',
				'".$moisture."',
				'".$sludge."',
				'".$keterangan."',
				'".$_SESSION['standard']['userid']."'
				);";
			}	
				
			//Execution All Data Insert
			try{$owlPDO->exec($sInsert); }
			catch (PDOException $e) {
				$result['err'] = "Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
			
		echo json_encode($result);
	break;
	case 'select':
		if($tanggal != ""){
			$where .= " and tanggal = '".date('Y-m-d',strtotime($tanggal))."'";
		}
		if($pabrik != ""){
			$where .= " and pabrik = '".$pabrik."'";
		}
		$str = "select * from " . $dbname . ".pabrik_oilunderflow where 1=1 ".$where." limit 1";
		$strPDO = fetchData($str);
		if(count($strPDO) > 0){
			$result['data'] = $strPDO;
		}else{
			$result['err'] ="Data Belum ada.";
		}
		echo json_encode($result);
	break;
	case 'update':
			if($tanggal != ""){
				$where .= " and tanggal = '".date('Y-m-d',strtotime($tanggal))."'";
			}
			if($pabrik != ""){
				$where .= " and pabrik = '".$pabrik."'";
			}
			$str = "select * from " . $dbname . ".pabrik_oilunderflow where 1=1 ".$where." limit 1";
			$strPDO = fetchData($str);
			if(count($strPDO) > 0){
				$sInsert .= "UPDATE ".$dbname.".pabrik_oilunderflow set
				oil = '".$oil."',
				moisture = '".$moisture."',
				sludge = '".$sludge."',
				keterangan =  '".$keterangan."',
				updateby='" . $_SESSION['standard']['userid'] . "'
				);";
				//Execution All Data Insert
				try{$owlPDO->exec($sInsert); }
				catch (PDOException $e) {
					$result['err'] = "Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}else{
				$result['err'] ="Data Belum ada.";
			}
		echo json_encode($result);
	break;
	case 'delete':
		
		if($tanggal != "" and $pabrik != ""){
			$where .= "tanggal = '".date('Y-m-d',strtotime($tanggal))."' and pabrik = '".$pabrik."'";
		}
		$sInsert = "delete from ".$dbname.".pabrik_oilunderflow where ".$where."";
		try{$owlPDO->exec($sInsert);}
		catch (PDOException $e) {
			$result['err'] = "Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		echo json_encode($result);
	break;
}
?>