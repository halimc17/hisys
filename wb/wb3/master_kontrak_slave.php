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
				<th style='text-align:center;'>No. Kontrak</th>
				<th style='text-align:center;'>Tanggal Kontrak</th>
				<th style='text-align:center;'>Pelanggan</th>
				<th style='text-align:center;'>Kuantitas (KG)</th>
				<th style='text-align:center;'>Keterangan</th>
				<th style='text-align:center;'>Status</th>
				<th style='text-align:center;'>User Update</th>
				<th style='text-align:center;'>Waktu Update</th>
				<th style='text-align:center;'>Aksi</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".mscontract order by ctrstatus desc, ctrno desc";
		$res= fetchdata($str);
		foreach($res as $val){
			## GET NAMA CUSTOMER
			$optcustomer=makeOption($dbname,'mscustomer','custcode,custname',"custcode='".$val['custcode']."'");
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$val['ctrno']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormal($val['ctrdate'])."</td>";
			$tab.="<td style='text-align:left;'>".$optcustomer[$val['custcode']]."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['ctrqty'],2)."</td>";
			$tab.="<td style='text-align:left;'>".$val['description']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['ctrstatus']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['updateby']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['ctrno']."','".tanggalnormal($val['ctrdate'])."','".$val['custcode']."','".hidezerodecimal($val['ctrqty'],2)."','".$val['description']."','".$val['ctrstatus']."')\";>
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
					<td>No. Kontrak</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=nokontrak>
					</td>
				</tr>
				<tr>
					<td>Tanggal Kontrak</td>
					<td>
						<input type='text' class='myinputtext' id='tanggal' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:100px;height:30px;font-size:14px;text-align:center' />
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Pelanggan</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='customer'>".$optcustomer."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Kuantitas Kontrak</td>
					<td>
						<input type=text class=myinputtextnumber style='width:150px;height:30px;font-size:14px;' id='kuantitas' onkeypress=\"return angka_doang(event)\" onkeyup=\"z.numberFormat('kuantitas',2)\" ondrop=\"return false;\" onpaste=\"return false;\" placeholder='0'> KG
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
				'ctrno'			=> $param['nokontrak'],
				'ctrdate' 		=> tanggalsystem($param['tanggal']),
				'custcode'	 	=> $param['customer'],
				'ctrqty' 		=> str_replace(',','',$param['kuantitas']),
				'description' 	=> $param['keterangan'],
				'ctrstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'mscontract',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'ctrdate'		=> tanggalsystem($param['tanggal']),
					'custcode'		=> $param['customer'],
					'ctrqty'		=> str_replace(',','',$param['kuantitas']),
					'description'	=> $param['keterangan'],
					'ctrstatus'		=> $param['status'],
					'updateby'		=> $_SESSION['standard']['username']
				);
				$where = "ctrno='".$param['nokontrak']."'";
				$query = updateQuery($dbname,'mscontract',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
