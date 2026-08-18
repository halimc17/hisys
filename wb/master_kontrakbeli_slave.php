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
				<th style='text-align:center;'>No. Dokumen</th>
				<th style='text-align:center;'>Vendor</th>
				<th style='text-align:center;'>Volume</th>
				<th style='text-align:center;'>Tanggal dari</th>
				<th style='text-align:center;'>Tanggal sampai</th>
				<th style='text-align:center;'>Status</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".mscontractpurchase order by ctrstatus desc, ctrno desc";
		$res= fetchdata($str);
		foreach($res as $val){
			## GET NAMA PT
			$optpt=makeOption($dbname,'mscompany','compcode,compname',"compcode='".$val['compcode']."'");
			
			## GET NAMA VENDOR
			$optvendor=makeOption($dbname,'msvendor','vendorcode,vendorname',"vendorcode='".$val['vendorcode']."'");
			
			## GET NAMA PRODUK
			$optproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$val['kodeproduk']."'");
			if($val['ctrstatus']=='1'){$checked='checked';}else{$checked='';}
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;' id='kodeform_".$no."'>".$val['ctrno']."</td>";
			$tab.="<td style='text-align:left;'>".$optvendor[$val['vendorcode']]."</td>";
			$tab.="<td style='text-align:left;'>".$val['ctrqty']."</td>";
			$tab.="<td style='text-align:left;'>".tanggalnormal($val['tanggaldari'])."</td>";
			$tab.="<td style='text-align:left;'>".tanggalnormal($val['tanggalsampai'])."</td>";
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
		
		## GET CUSTOMER
		$str="select vendorcode,vendorname from ".$dbname.".msvendor where vendorstatus='1' and supplier='1' order by vendorname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optvendor.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";	
		}
		
		## GET PRODUK
		$str="select kodeproduk,namaproduk from ".$dbname.".msproduk where statusproduk='1' and kriteria='Penerimaan' order by namaproduk asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optproduk.="<option value='".$val['kodeproduk']."'>".$val['namaproduk']."</option>";	
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>No. Kontrak</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=nokontrak>
					</td>
				</tr>
				<tr>
					<td>Vendor</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='vendor'>".$optvendor."</select>
					</td>
				</tr>
				<tr>
					<td>Produk</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='produk'>".$optproduk."</select>
					</td>
				</tr>
				<tr>
					<td>Kuantitas Kontrak</td>
					<td>
						<input type=text class=myinputtextnumber style='width:150px;height:30px;font-size:14px;' id='kuantitas' onkeypress=\"return angka_doang(event)\" onkeyup=\"z.numberFormat('kuantitas',2)\" ondrop=\"return false;\" onpaste=\"return false;\" placeholder='0'> KG
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
				'vendorcode'	=> $param['vendor'],
				'kodeproduk'	=> $param['produk'],
				'ctrqty' 		=> str_replace(',','',$param['kuantitas']),
				'ctrstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'mscontractpurchase',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'vendorcode'=> $param['vendor'],
					'kodeproduk'=> $param['produk'],
					'ctrqty'	=> str_replace(',','',$param['kuantitas']),
					'ctrstatus' => $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "ctrno='".$param['nokontrak']."'";
				$query = updateQuery($dbname,'mscontractpurchase',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case'getvalstt':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'ctrstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "ctrno='".$param['kode']."'";
				$query = updateQuery($dbname,'mscontractpurchase',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
