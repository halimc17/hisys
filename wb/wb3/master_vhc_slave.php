<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case 'loaddata':
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>No.</th>
				<th style='text-align:center;'>Transportir</th>
				<th style='text-align:center;'>No. Kendaraan</th>
				<th style='text-align:center;'>No. STNK</th>
				<th style='text-align:center;'>Tara Minimum</th>
				<th style='text-align:center;'>Tara Maksimum</th>
				<th style='text-align:center;'>Status</th>
				<th style='text-align:center;'>User Update</th>
				<th style='text-align:center;'>Waktu Update</th>
				<th style='text-align:center;'>Aksi</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".msvhc order by vhcstatus desc, typecode asc, vhccode asc";
		$res= fetchdata($str);
		foreach($res as $val){
			## GET NAMA WILAYAH
			$opttrp=makeOption($dbname,'msvendor','vendorcode,vendorname',"vendorcode='".$val['vendorcode']."'");
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$opttrp[$val['vendorcode']]."</td>";
			$tab.="<td style='text-align:left;'>".$val['vhccode']."</td>";
			$tab.="<td style='text-align:left;'>".$val['nostnk']."</td>";
			$tab.="<td style='text-align:left;'>".hidezerodecimal($val['vhctarmin'],2)."</td>";
			$tab.="<td style='text-align:left;'>".hidezerodecimal($val['vhctarmax'],2)."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['vhcstatus']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['updateby']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['vhccode']."','".$val['vendorcode']."','".hidezerodecimal($val['vhctarmin'],2)."','".hidezerodecimal($val['vhctarmax'],2)."','".$val['vhcstatus']."','".$val['nostnk']."')\";>
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
		$optstatus="";
		foreach($arrstatus as $key => $val){
			if($key=='1'){
				$optstatus.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$optstatus.="<option value=".$key.">".$val."</option>";							
			}
		}

		$opttrp="";
		$str="select vendorcode,vendorname from ".$dbname.".msvendor where transportir='1' and vendorstatus='1'";
		$res=fetchData($str);
		foreach ($res as $val){
			$opttrp.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";	
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Transportir</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='vendorcode'>".$opttrp."</select>
					</td>
				</tr>
				<tr>
					<td>No. Kendaraan</td>
					<td>
						<input class=myinputtext maxlength=15 style='width:160px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kode>
					</td>
				</tr>
				<tr>
					<td>No. STNK</td>
					<td>
						<input class=myinputtext maxlength=15 style='width:160px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=nostnk>
					</td>
				</tr>
				<tr>
					<td>Tara Minimum</td>
					<td>
						<input type=text class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' id='taramin' onkeypress=\"return angka_doang(event)\" onkeyup=\"z.numberFormat('taramin',2)\" ondrop=\"return false;\" onpaste=\"return false;\" placeholder='0'> KG
					</td>
				</tr>
				<tr>
					<td>Tara Maksimum</td>
					<td>
						<input type=text class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' id='taramax' onkeypress=\"return angka_doang(event)\" onkeyup=\"z.numberFormat('taramax',2)\" ondrop=\"return false;\" onpaste=\"return false;\" placeholder='0'> KG
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
				'vhccode'		=> $param['kode'],
				'vendorcode' 		=> $param['vendorcode'],
				'nostnk' 		=> $param['nostnk'],
				'vhctarmin'		=> str_replace(',','',$param['taramin']),
				'vhctarmax'		=> str_replace(',','',$param['taramax']),
				'vhcstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msvhc',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'vhctarmin'	=> str_replace(',','',$param['taramin']),
					'vhctarmax'	=> str_replace(',','',$param['taramax']),
					'nostnk'=> $param['nostnk'],
					'vhcstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "vhccode='".$param['kode']."'";
				$query = updateQuery($dbname,'msvhc',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
