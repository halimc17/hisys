<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['rupiah']."</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$whrunit = getOrgDetail(2);
		$str= "select * from ".$dbname.".sdm_5hargacatukg where 1=1 and unit in (".$whrunit.") order by status desc, unit asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($val['unit'])."</td>";
			$tab.="<td style='text-align:center;'>".number_format($val['nilai'],2)."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['unit']."','".$val['nilai']."','".$val['status']."')\";>
			</td>";
			$tab.="</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
	
	case 'addnew':
		$tab="";
		foreach($arrstatus as $key => $val){
			if($key=='1'){
				$optstatus.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$optstatus.="<option value=".$key.">".$val."</option>";							
			}
		}
		
		## GET Kode PT
		$arrorgdet = getOrgDetail(11);
		foreach($arrorgdet as $key=>$val){
			$optpt.="<option value='".$key."'>".$key." - ".$val."</option>";	
		}

		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td>
						<select class='select2' style='width:200px;' id='unit' >".$optpt."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['rupiah']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtextnumber' style='width:195px;' id='nilai' onkeypress='return tanpa_kutip(event)'  />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td>
						<select class='select2' style='width:200px;' id=status >".$optstatus."</select>
					</td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value=insert></td>
                    <td colspan=4>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
                    </td>
                </tr>
            </table>";
		
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			## VALIDATE
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5hargacatukg where unit='".$param['unit']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Sudah terdaftar,silahkan lihat list data.");
			}
			
			$data = array(
				'unit' 			=> $param['unit'],
				'nilai' 		=> $param['nilai'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5hargacatukg',$data,$cols);
			
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			$data = array(
				'nilai' 		=> $param['nilai'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid']
			);
			$where = "unit='".$param['unit']."'";
			$str = updateQuery($dbname,'sdm_5hargacatukg',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>