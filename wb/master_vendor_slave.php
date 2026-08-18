<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case 'loaddata':
		$tab="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>No.</th>
				<th style='text-align:center;'>Kode Vendor</th>
				<th style='text-align:center;'>Nama Vendor</th>
				<th style='text-align:center;'>Supplier</th>
				<th style='text-align:center;'>Transportir</th>
				<th style='text-align:center;'>Status</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".msvendor order by vendorstatus desc, vendorname asc";
		$res= fetchdata($str);
		foreach($res as $val){
			if($val['vendorstatus']=='1'){$checked='checked';}else{$checked='';}
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;' id='kodeform_".$no."'>".$val['vendorcode']."</td>";
			$tab.="<td style='text-align:left;'>".$val['vendorname']."</td>";
			$tab.="<td style='text-align:center;'>".($val['supplier']=='1'?'v':'')."</td>";
			$tab.="<td style='text-align:center;'>".($val['transportir']=='1'?'v':'')."</td>";
			$tab.="<td style='text-align:center;'>
				<input type='checkbox' id='actstt_".$no."' name='actstt_".$no."' ".$checked." onclick=\"getvalstt('".$no."')\">
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
		
		## GET KODE VENDOR
		$str="select millcode, idwb from ".$dbname.".mssystem";
		$res=fetchdata($str);
		$millcode=$res[0]['millcode'];
		$idwb=$res[0]['idwb']."S";
		
		$nomor=0;
		$str="select max(RIGHT(vendorcode,4)) as nomor from ".$dbname.".msvendor order by vendorcode desc limit 1";
		$res=fetchdata($str);
		$nomor=$res[0]['nomor'];
		$nomor++;
		$kodevendor=$millcode.$idwb.addZero($nomor,4);
		
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Kode Vendor</td>
					<td>
						<input class=myinputtext maxlength=10 style='width:120px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kode value='".$kodevendor."' disabled>
					</td>
				</tr>
				<tr>
					<td>Nama Vendor</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=deskripsi>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Tipe</td>
					<td style='height:35px'>
						<input type='checkbox' id='supplier' value='SUPPLIER' />Supplier
						<input type='checkbox' id='transportir' value='TRANSPORTIR' />Transportir
					</td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=alamat>
					</td>
				</tr>
				<tr>
					<td>Kota</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kota>
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
				'vendorcode'	=> $param['kode'],
				'vendorname' 	=> $param['deskripsi'],
				'vendoraddr'	=> $param['alamat'],
				'vendorcity' 	=> $param['kota'],
				'supplier'	 	=> $param['supplier'],
				'transportir'	=> $param['transportir'],
				'vendorstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msvendor',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'vendorname'	=> $param['deskripsi'],
					'vendoraddr'	=> $param['alamat'],
					'vendorcity'	=> $param['kota'],
					'supplier'		=> $param['supplier'],
					'transportir'	=> $param['transportir'],
					'vendorstatus'	=> $param['status'],
					'updateby'		=> $_SESSION['standard']['username']
				);
				$where = "vendorcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msvendor',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case'getvalstt':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'vendorstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "vendorcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msvendor',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
