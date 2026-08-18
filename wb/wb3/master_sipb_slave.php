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
				<th style='text-align:center;'>No. DO</th>
				<th style='text-align:center;'>No. Kontrak</th>
				<th style='text-align:center;'>Tanggal DO</th>
				<th style='text-align:center;'>Produk</th>
				<th style='text-align:center;'>Pelanggan</th>
				<th style='text-align:center;'>Transportir</th>
				<th style='text-align:center;'>Kuantitas (KG)</th>
				<th style='text-align:center;'>Sisa (KG)</th>
				<th style='text-align:center;'>Keterangan</th>
				<th style='text-align:center;'>Status</th>
				<th style='text-align:center;'>User Update</th>
				<th style='text-align:center;'>Waktu Update</th>
				<th style='text-align:center;'>Aksi</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".mssipb order by sipbstatus desc, sipbno desc";
		$res= fetchdata($str);
		foreach($res as $val){
			## GET NAMA PRODUK
			$optproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$val['kodeproduk']."'");
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='min-width:180px;text-align:left;'>".$val['sipbno']."</td>";
			$tab.="<td style='min-width:180px;text-align:left;'>".$val['ctrno']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormal($val['sipbdate'])."</td>";
			$tab.="<td style='text-align:left;'>".$optproduk[$val['kodeproduk']]."</td>";
			$tab.="<td style='text-align:left;'>".getcustomer($val['ctrno'])."</td>";
			$tab.="<td style='text-align:left;'>".getvendor($val['vendorcode'])."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['sipbawal'],2)."</td>";
			$tab.="<td style='text-align:right;'>".hidezerodecimal($val['sipbqty'],2)."</td>";
			$tab.="<td style='text-align:left;'>".$val['description']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['sipbstatus']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['updateby']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['sipbno']."','".$val['ctrno']."','".tanggalnormal($val['sipbdate'])."','".$val['kodeproduk']."','".$val['vendorcode']."','".hidezerodecimal($val['sipbawal'],2)."','".$val['description']."','".$val['sipbstatus']."')\";>
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
		
		## GET CONTRACT
		$no=0;
		$nokontrakx="";
		$str="select ctrno from ".$dbname.".mscontract where ctrstatus='1' order by ctrno desc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			if($no==1){
				$optkontrak.="<option value='".$val['ctrno']."' selected>".$val['ctrno']."</option>";
				$nokontrakx=$val['ctrno'];
			}else{
				$optkontrak.="<option value='".$val['ctrno']."'>".$val['ctrno']."</option>";
			}
		}
		
		## GET CUSTOMER
		$buyernamex=getcustomer($nokontrakx);
		
		## GET PRODUK
		$str="select kodeproduk,namaproduk from ".$dbname.".msproduk where statusproduk='1' and kriteria='Pengiriman' order by namaproduk asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optproduk.="<option value='".$val['kodeproduk']."'>".$val['namaproduk']."</option>";
		}
		
		## GET TRANSPORTIR
		$str="select vendorcode,vendorname from ".$dbname.".msvendor where vendorstatus='1' and transportir='1' order by vendorname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$opttransportir.="<option value='".$val['vendorcode']."'>".$val['vendorname']."</option>";
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>No. DO</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=nodo>
					</td>
				</tr>
				<tr>
					<td>Tanggal DO</td>
					<td>
						<input type='text' class='myinputtext' id='tanggal' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:100px;height:30px;font-size:14px;text-align:center' />
					</td>
				</tr>
				<tr>
					<td>No. Kontrak</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' onchange='getcustomer(this.value)' id='nokontrak'>".$optkontrak."</select>
					</td>
				</tr>
				<tr>
					<td>Pelanggan</td>
					<td>
						<input class=myinputtext maxlength=50 id='customer' style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' value='".$buyernamex."' disabled>
					</td>
				</tr>
				<tr>
					<td>Produk</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='produk'>".$optproduk."</select>
					</td>
				</tr>
				<tr>
					<td>Transportir</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='transportir'>".$opttransportir."</select>
					</td>
				</tr>
				<tr>
					<td>Kuantitas DO</td>
					<td>
						<input type=text class=myinputtextnumber style='width:150px;height:30px;font-size:14px;' id='kuantitas' onkeypress=\"return angka_doang(event)\" onkeyup=\"z.numberFormat('kuantitas',2)\" ondrop=\"return false;\" onpaste=\"return false;\" placeholder='0'> KG
					</td>
				</tr>
				<tr style='display:none'>
					<td>Sisa DO</td>
					<td>
						<input type=text class=myinputtextnumber style='width:150px;height:30px;font-size:14px;' id='sisa' onkeypress=\"return angka_doang(event)\" onkeyup=\"z.numberFormat('sisa',2)\" ondrop=\"return false;\" onpaste=\"return false;\" placeholder='0'> KG
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
			
			## GET KUANTITAS KONTRAK
			$str="select ctrqty from ".$dbname.".mscontract where ctrno='".$param['nokontrak']."'";
			$res=fetchdata($str);
			$kuantitaskontrak=$res[0]['ctrqty'];
			
			## GET KUANTITAS DO REFER TO NO KONTRAK
			$str="select sum(sipbawal) as sipbawal from ".$dbname.".mssipb where ctrno='".$param['nokontrak']."'";
			$res=fetchdata($str);
			$totaldo=($res[0]['sipbawal']+str_replace(',','',$param['kuantitas']));
			
			if($totaldo > $kuantitaskontrak){
				throw new PDOException('Kuantitas DO sudah melebihi Kuantitas Kontrak.');
			}
			
			$data = array(
				'sipbno'		=> $param['nodo'],
				'ctrno'			=> $param['nokontrak'],
				'sipbdate' 		=> tanggalsystem($param['tanggal']),
				'kodeproduk' 	=> $param['produk'],
				'vendorcode' 	=> $param['transportir'],
				'sipbqty' 		=> str_replace(',','',$param['kuantitas']),
				'sipbawal' 		=> str_replace(',','',$param['kuantitas']),
				'description' 	=> $param['keterangan'],
				'sipbstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'mssipb',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'description'	=> $param['keterangan'],
					'sipbstatus'		=> $param['status'],
					'updateby'		=> $_SESSION['standard']['username']
				);
				$where = "sipbno='".$param['nodo']."'";
				$query = updateQuery($dbname,'mssipb',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case 'getcustomer':
		echo getcustomer($param['nokontrak']);
	break;
}

function getcustomer($kode){
	global $dbname;
	global $owlPDO;
	
	## GET CONTRACT
	$buyercodex="";
	$str="select custcode from ".$dbname.".mscontract where ctrno='".$kode."'";
	$res=fetchdata($str);
	$buyercodex=$res[0]['custcode'];
	
	## GET CUSTOMER
	$str="select custname from ".$dbname.".mscustomer where custcode='".$buyercodex."'";
	$res=fetchdata($str);
	$buyernamex=$res[0]['custname'];
	
	return $buyernamex;
}

function getvendor($kode){
	global $dbname;
	global $owlPDO;
	
	## GET VENDOR
	$vendorx="";
	$str="select vendorname from ".$dbname.".msvendor where vendorcode='".$kode."'";
	$res=fetchdata($str);
	$vendorx=$res[0]['vendorname'];
	
	return $vendorx;
}
?>
