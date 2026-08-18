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
				<th style='text-align:center;'>Kode Divisi</th>
				<th style='text-align:center;'>Nama Divisi</th>
				<th style='text-align:center;'>Status</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".msdivisi order by compcode asc, unitcode asc, divname asc";
		$res= fetchdata($str);
		foreach($res as $val){
			if($val['divstatus']=='1'){$checked='checked';}else{$checked='';}
			## GET NAMA WILAYAH
			$optnmperusahaan=makeOption($dbname,'mscompany','compcode,compname',"compcode='".$val['compcode']."'");
			$optnmunit=makeOption($dbname,'msunit','unitcode,unitname',"unitcode='".$val['unitcode']."'");
			
			$e=$optnmperusahaan[$val['compcode']];
			if(@$e!=@$o){
				$tab.="<tr class=rowcontent style='background-color:#28e8e8;font-weight:bold'>";
				$tab.="<td style='text-align:center;'></td>";
				$tab.="<td style='text-align:left;'>".$e."</td>";
				$tab.="<td></td><td></td>";
				$tab.="</tr>";
			}
			
			$d=$optnmunit[$val['unitcode']];
			if(@$d!=@$n){
				$tab.="<tr class=rowcontent style='background-color:#e8e8e8;font-weight:bold'>";
				$tab.="<td style='text-align:center;'></td>";
				$tab.="<td style='text-align:left;'>&nbsp;&nbsp;&nbsp;&nbsp;".$d."</td>";
				$tab.="<td></td><td></td>";
				$tab.="</tr>";
			}
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td id='kodeform_".$no."'>".$val['divcode']."</td>";
			$tab.="<td style='text-align:left;'>".$val['divname']."</td>";
			$tab.="<td style='text-align:center;'>
				<input type='checkbox' id='actstt_".$no."' name='actstt_".$no."' ".$checked." onclick=\"getvalstt('".$no."')\">
			</td>";
			$tab.="</tr>";
			
			$n=$d;
			$o=$e;
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
		
		## GET PERUSAHAAN
		$no=0;
		$kodeperusahaanx="";
		$str="select compcode,compname from ".$dbname.".mscompany where compstatus='1' order by compname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			if($no==1){
				$optperusahaan.="<option value='".$val['compcode']."' selected>".$val['compname']."</option>";
				$kodeperusahaanx=$val['compcode'];
			}else{
				$optperusahaan.="<option value='".$val['compcode']."'>".$val['compname']."</option>";				
			}
		}
		
		## GET UNIT
		$str="select unitcode,unitname from ".$dbname.".msunit where unitstatus='1' and compcode='".$kodeperusahaanx."' order by unitname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optunit.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";	
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Perusahaan</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' onchange='getunit(this.value)' id='kodeperusahaan'>".$optperusahaan."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Unit</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='kodeunit'>".$optunit."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Kode Divisi</td>
					<td>
						<input class=myinputtext maxlength=6 style='width:70px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kode>
					</td>
				</tr>
				<tr>
					<td>Nama Divisi</td>
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
				'compcode'		=> $param['kodeperusahaan'],
				'unitcode' 		=> $param['kodeunit'],
				'divcode' 		=> $param['kode'],
				'divname' 		=> $param['deskripsi'],
				'divstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msdivisi',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'divname'	=> $param['deskripsi'],
					'divstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "divcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msdivisi',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case 'getunit':
		$str="select unitcode,unitname from ".$dbname.".msunit where unitstatus='1' and compcode='".$param['kodeperusahaan']."' order by unitname asc";
		$res = fetchdata($str);
		$res=fetchdata($str);
		foreach($res as $val){
			if($param['kodeunit']==$val['unitcode']){$sel="selected";}
			$optperusahaan.="<option value='".$val['unitcode']."' ".$sel.">".$val['unitname']."</option>";					
		}
		
		echo $optperusahaan;
	break;
	
	case'getvalstt':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'divstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "divcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msdivisi',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
