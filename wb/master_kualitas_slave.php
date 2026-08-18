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
				<th style='text-align:center;'>Produk</th>
				<th style='text-align:center;'>Storage</th>
				<th style='text-align:center;'>FFA</th>
				<th style='text-align:center;'>Moist</th>
				<th style='text-align:center;'>Dirt</th>
				<th style='text-align:center;'>Dobi</th>
				<th style='text-align:center;'>Status</th>
				<th style='text-align:center;'>User Update</th>
				<th style='text-align:center;'>Waktu Update</th>
				<th style='text-align:center;'>Aksi</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".mskualitas order by status desc, produk asc, kode asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".getNamaBrg($val['produk'])."</td>";
			$tab.="<td style='text-align:left;'>Storage ".$val['kode']."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['ffa'],3)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['moist'],3)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['dirt'],3)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['dobi'],3)."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['updateby']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['produk']."','".$val['kode']."','".$val['ffa']."','".$val['moist']."','".$val['dirt']."','".$val['dobi']."','".$val['status']."')\";>
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
		
		## GET PRODUK
		$optproduk="<option value=''>Silahkan pilih</option>";
		$optproduk.="<option value='".$kodeprodukcpo."'>".getNamaBrg($kodeprodukcpo)."</option>";
		$optproduk.="<option value='".$kodeprodukpk."'>".getNamaBrg($kodeprodukpk)."</option>";
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Produk</td>
					<td>
						<select class='select2' style='width:250px;height:30px;' id='produk'>".$optproduk."</select>
					</td>
				</tr>
				<tr>
					<td>Storage</td>
					<td>
						<input maxlength='1' class=myinputtext style='width:25px;text-align:center' type=text id=storage onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
					</td>
				</tr>
				<tr>
					<td>FFA</td>
					<td>
						<input maxlength='10' class=myinputtext type=text style='text-align:right;width:75px' onkeypress='return isNumberKey(event);' id='ffa' value='' placeholder='0'>
					</td>
				</tr>
				<tr>
					<td>Moist</td>
					<td>
						<input maxlength='10' class=myinputtext type=text style='text-align:right;width:75px' onkeypress='return isNumberKey(event);' id='moist' value='' placeholder='0'>
					</td>
				</tr>
				<tr>
					<td>Dirt</td>
					<td>
						<input maxlength='10' class=myinputtext type=text style='text-align:right;width:75px' onkeypress='return isNumberKey(event);' id='dirt' value='' placeholder='0'>
					</td>
				</tr>
				<tr>
					<td>Dobi</td>
					<td>
						<input maxlength='10' class=myinputtext type=text style='text-align:right;width:75px' onkeypress='return isNumberKey(event);' id='dobi' value='' placeholder='0'>
					</td>
				</tr>
				<tr>
					<td>Status</td>
					<td>
						<select class='select2' style='width:350px;' id=status >".$optstatus."</select>
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
			
			$str="select kode from ".$dbname.".mskualitas where kode='".$param['storage']."' and produk='".$param['produk']."'";
			$res=fetchdata($str);
			if(count($res) > 0){
				throw new PDOException("Produk dan Storage sudah ada di sistem.");
			}
			
			$data = array(
				'kode'		=> $param['storage'],
				'produk' 	=> $param['produk'],
				'ffa' 		=> $param['ffa'],
				'moist' 	=> $param['moist'],
				'dirt' 		=> $param['dirt'],
				'dobi' 		=> $param['dobi'],
				'status'	=> $param['status'],
				'updateby' 	=> $_SESSION['standard']['username'],
				'createby'	=> $_SESSION['standard']['username'],
				'createtime'=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'mskualitas',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'ffa' 		=> $param['ffa'],
					'moist' 	=> $param['moist'],
					'dirt' 		=> $param['dirt'],
					'dobi' 		=> $param['dobi'],
					'status'	=> $param['status'],
					'updateby' 	=> $_SESSION['standard']['username']
				);
				$where = "kode='".$param['storage']."' and produk='".$param['produk']."'";
				$query = updateQuery($dbname,'mskualitas',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
