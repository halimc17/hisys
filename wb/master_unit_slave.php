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
				<th style='text-align:center;'>Kode Unit</th>
				<th style='text-align:center;'>Nama Unit</th>
				<th style='text-align:center;'>Tipe</th>
				<th style='text-align:center;'>Status</th>
			</tr>
		</thead>
		<tbody>";
		
		$optnmperusahaan=makeOption($dbname,'mscompany','compcode,compname');
		$no=0;
		$o="";
		$str= "select * from ".$dbname.".msunit order by compcode asc";
		$res= fetchdata($str);
		foreach($res as $val){
			if($val['unitstatus']=='1'){$checked='checked';}else{$checked='';}
			## GET NAMA WILAYAH
			$e=$optnmperusahaan[$val['compcode']];
			if($e!=$o){
				$tab.="<tr class=rowcontent style='background-color:#e8e8e8;font-weight:bold'>";
				$tab.="<td style='text-align:center;'></td>";
				$tab.="<td style='text-align:left;'>".$e."</td>";
				$tab.="<td></td><td></td><td></td>";
				$tab.="</tr>";
			}
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td id='kodeform_".$no."'>".$val['unitcode']."</td>";
			$tab.="<td style='text-align:left;'>".$val['unitname']."</td>";
			$tab.="<td style='text-align:left;'>".$val['tipe']."</td>";
			$tab.="<td style='text-align:center;'>
				<input type='checkbox' id='actstt_".$no."' name='actstt_".$no."' ".$checked." onclick=\"getvalstt('".$no."')\">
			</td>";
			$tab.="</tr>";
			
			$n=@$d;
			$o=@$e;
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
		
		## GET WILAYAH
		$no=0;
		$kodewilayahx="";
		$str="select wilcode,wilname from ".$dbname.".mswilayah where wilstatus='1' order by wilname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			if($no==1){
				$optwilayah.="<option value='".$val['wilcode']."' selected>".$val['wilname']."</option>";
				$kodewilayahx=$val['wilcode'];
			}else{
				$optwilayah.="<option value='".$val['wilcode']."'>".$val['wilname']."</option>";				
			}
		}
		
		## GET PERUSAHAAN
		$str="select compcode,compname from ".$dbname.".mscompany where compstatus='1' and wilcode='".$kodewilayahx."' order by compname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optperusahaan.="<option value='".$val['compcode']."'>".$val['compname']."</option>";	
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Wilayah</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' onchange='getcompany(this.value)' id='kodewilayah'>".$optwilayah."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Perusahaan</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='kodeperusahaan'>".$optperusahaan."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Kode Unit</td>
					<td>
						<input class=myinputtext maxlength=4 style='width:60px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kode>
					</td>
				</tr>
				<tr>
					<td>Nama Unit</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=deskripsi>
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
				'unitcode'		=> $param['kode'],
				'unitname' 		=> $param['deskripsi'],
				'wilcode' 		=> $param['kodewilayah'],
				'compcode' 		=> $param['kodeperusahaan'],
				'unitstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msunit',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'unitname'	=> $param['deskripsi'],
					'unitstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "unitcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msunit',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case 'getcompany':
		$str="select compcode,compname from ".$dbname.".mscompany where compstatus='1' and wilcode='".$param['kodewilayah']."' order by compname asc";
		$res = fetchdata($str);
		$res=fetchdata($str);
		foreach($res as $val){
			if($param['kodeperusahaan']==$val['compcode']){$sel="selected";}
			$optperusahaan.="<option value='".$val['compcode']."' ".$sel.">".$val['compname']."</option>";					
		}
		
		echo $optperusahaan;
	break;
	
	case'getvalstt':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'unitstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "unitcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msunit',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
