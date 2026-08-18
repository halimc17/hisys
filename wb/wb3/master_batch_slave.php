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
				<th style='text-align:center;'>No.</th>
				<th style='text-align:center;'>Kode Batch</th>
				<th style='text-align:center;'>Keterangan</th>
				<th style='text-align:center;'>Status</th>
				<th style='text-align:center;'>User Update</th>
				<th style='text-align:center;'>Waktu Update</th>
				<th style='text-align:center;'>Aksi</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".msbatch order by batchstatus desc, batchcode desc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$val['batchcode']."</td>";
			$tab.="<td style='text-align:left;'>".$val['description']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['batchstatus']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['updateby']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['batchcode']."','".$val['description']."','".$val['batchstatus']."')\";>
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
		
		## GET CUSTOMER
		$str="select custcode,custname from ".$dbname.".mscustomer where custstatus='1' order by custname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optcustomer.="<option value='".$val['custcode']."'>".$val['custname']."</option>";	
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td>Kode Batch</td>
					<td>
						<input class=myinputtext maxlength=10 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kode>
					</td>
				</tr>
				<tr>
					<td>Keterangan</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=keterangan>
					</td>
				</tr>
				<tr>
					<td>Status</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id=status >".$optstatus."</select>
					</td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value=insert></td>
                    <td colspan=4>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			$data = array(
				'batchcode'		=> $param['kode'],
				'description' 	=> $param['keterangan'],
				'batchstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msbatch',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'description'	=> $param['keterangan'],
					'batchstatus'	=> $param['status'],
					'updateby'		=> $_SESSION['standard']['username']
				);
				$where = "batchcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msbatch',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
