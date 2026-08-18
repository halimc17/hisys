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
				<th style='text-align:center;'>Kode Produk</th>
				<th style='text-align:center;'>Nama Produk</th>
				<th style='text-align:center;'>Inisial</th>
				<th style='text-align:center;'>Satuan</th>
				<th style='text-align:center;'>Status</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".msproduk order by statusproduk desc, left(kodeproduk,3) desc, kodeproduk asc";
		$res= fetchdata($str);
		foreach($res as $val){
			if($val['statusproduk']=='0'){$checked='checked';}else{$checked='';}
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;' id='kodeform_".$no."'>".$val['kodeproduk']."</td>";
			$tab.="<td style='text-align:left;'>".$val['namaproduk']."</td>";
			$tab.="<td style='text-align:left;'>".$val['inisial']."</td>";
			$tab.="<td style='text-align:left;'>".$val['satuan']."</td>";
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
		
		$kodeproduk=getkode('Pengiriman');
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Kode Produk</td>
					<td>
						<input class=myinputtext maxlength=10 style='width:120px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kode value='".$kodeproduk."' disabled>
					</td>
				</tr>
				<tr>
					<td>Nama Produk</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=deskripsi>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Kriteria</td>
					<td style='height:35px'>
						<input type=radio id=kriteria1 name=kriteria value=Pengiriman onclick=getkode(this.value) checked>Pengiriman
						<input type=radio id=kriteria2 name=kriteria value=Penerimaan onclick=getkode(this.value)>Penerimaan
					</td>
				</tr>
				<tr>
					<td>Satuan</td>
					<td>
						<input class=myinputtext maxlength=10 style='width:100px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=satuan>
					</td>
				</tr>
				<tr>
					<td>Inisial</td>
					<td>
						<input class=myinputtext maxlength=10 style='width:100px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=inisial>
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
				'kodeproduk'	=> $param['kode'],
				'namaproduk' 	=> $param['deskripsi'],
				'satuan' 		=> $param['satuan'],
				'kriteria' 		=> $param['kriteria'],
				'inisial' 		=> $param['inisial'],
				'statusproduk'	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msproduk',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'namaproduk'	=> $param['deskripsi'],
					'satuan'		=> $param['satuan'],
					'kriteria'		=> $param['kriteria'],
					'inisial'		=> $param['inisial'],
					'statusproduk'	=> $param['status'],
					'updateby'		=> $_SESSION['standard']['username']
				);
				$where = "kodeproduk='".$param['kode']."'";
				$query = updateQuery($dbname,'msproduk',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case'getvalstt':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'statusproduk'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "kodeproduk='".$param['kode']."'";
				$query = updateQuery($dbname,'msproduk',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case'getkode':
		echo getkode($param['kriteria']);
	break;
}

function getkode($kriteria){
	global $dbname;
	global $owlPDO;
	
	## GET KODE PRODUCT
	$kode='';
	$nomor=0;
	$str="select max(RIGHT(kodeproduk,6)) as nomor from ".$dbname.".msproduk where kriteria='".$kriteria."' order by kodeproduk desc limit 1";
	$res=fetchdata($str);
	$nomor=$res[0]['nomor'];
	$nomor++;
	if($kriteria=='Pengiriman'){
		$kode="4000".addZero($nomor,6);		
	}else{
		$kode="4001".addZero($nomor,6);		
	}
	
	return $kode;
}
?>
