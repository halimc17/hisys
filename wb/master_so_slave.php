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
				<th style='text-align:center;'>No. Urut</th>
				<th style='text-align:center;'>Perusahaan</th>
				<th style='text-align:center;'>No. Kontrak</th>
				<th style='text-align:center;'>Customer</th>
				<th style='text-align:center;'>Kuantitas Kontrak</th>
				<th style='text-align:center;'>No. DO</th>
				<th style='text-align:center;'>Transportir</th>
				<th style='text-align:center;'>Produk</th>
				<th style='text-align:center;'>Kuantitas</th>
				<th style='text-align:center;'>Sisa</th>
				<th style='text-align:center;'>Status</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".msso order by sostatus desc, noso desc";
		$res= fetchdata($str);
		foreach($res as $val){
			## GET NAMA PT
			$optpt=makeOption($dbname,'mscompany','compcode,compname',"compcode='".$val['compcode']."'");
			
			## GET NAMA CUSTOMER
			$optcustomer=makeOption($dbname,'mscustomer','custcode,custname',"custcode='".$val['custcode']."'");
			
			## GET NAMA TRANSPORTER
			$optvendor=makeOption($dbname,'msvendor','vendorcode,vendorname',"vendorcode='".$val['vendorcode']."'");
			
			## GET NAMA PRODUK
			$optproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$val['kodeproduk']."'");
			if($val['sostatus']=='1'){$checked='checked';}else{$checked='';}
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$optpt[$val['compcode']]."</td>";
			$tab.="<td style='text-align:left;'>".$val['nosoinduk']."</td>";
			$tab.="<td style='text-align:left;'>".$optcustomer[$val['custcode']]."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['kontrakqty'],2)."</td>";
			$tab.="<td style='text-align:left;' id='kodeform_".$no."'>".$val['noso']."</td>";
			$tab.="<td style='text-align:left;'>".@$optvendor[$val['vendorcode']]."</td>";
			$tab.="<td style='text-align:left;'>".$optproduk[$val['kodeproduk']]."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['soqty'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['sisaso'],2)."</td>";
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
		$str="select custcode,custname from ".$dbname.".mscustomer where custstatus='1' order by custname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optcustomer.="<option value='".$val['custcode']."'>".$val['custname']."</option>";	
		}
		
		## GET PRODUK
		$str="select kodeproduk,namaproduk from ".$dbname.".msproduk where statusproduk='1' and kriteria='Pengiriman' order by namaproduk asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optproduk.="<option value='".$val['kodeproduk']."'>".$val['namaproduk']."</option>";	
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td>No. SO</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=noso>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Pelanggan</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='customer'>".$optcustomer."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Produk</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='produk'>".$optproduk."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Kuantitas</td>
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
				'noso'			=> $param['noso'],
				'custcode'	 	=> $param['customer'],
				'kodeproduk'	=> $param['produk'],
				'soqty' 		=> str_replace(',','',$param['kuantitas']),
				'sostatus' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msso',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'custcode'		=> $param['customer'],
					'kodeproduk'	=> $param['produk'],
					'soqty'			=> str_replace(',','',$param['kuantitas']),
					'sostatus'		=> $param['status'],
					'updateby'		=> $_SESSION['standard']['username']
				);
				$where = "noso='".$param['noso']."'";
				$query = updateQuery($dbname,'msso',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case'getvalstt':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'sostatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "noso='".$param['kode']."'";
				$query = updateQuery($dbname,'msso',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
