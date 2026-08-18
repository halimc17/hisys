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
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['pabrik']."</th>
				<th align=center>".$_SESSION['lang']['shift']."</th>    
				<th align=center>".$_SESSION['lang']['jammulai']."</th>
				<th align=center>".$_SESSION['lang']['jamselesai']."</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".pabrik_5shift order by status desc, kodeorg asc, shift asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($val['kodeorg'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['shift']."</td>";
			$tab.="<td style='text-align:center;'>".$val['jammulai']."</td>";
			$tab.="<td style='text-align:center;'>".$val['jamselesai']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['kodeorg']."','".$val['shift']."','".$val['jammulai']."','".$val['jamselesai']."','".$val['status']."')\";>
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
		
		## GET PABRIK
		$no=0;
		$arrorgdet = getOrgDetail(13);
		foreach($arrorgdet as $key=>$val){
			$no++;
			if($no==1){
				$unitkerja = $key;
			}
			$optpabrik.="<option value='".$key."'>".$key." - ".$val."</option>";	
		}
		
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td>
						<select class='select2' id='pabrik' >".$optpabrik."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['shift']."</td>
					<td>:</td>
					<td>
						<input class=myinputtext style='text-align:center;height:25px;font-size:14px;width:50px;' type=text id=shift onkeypress=\"return isNumberKey2(event);\" onkeydown='upperCaseF(this)' maxlength=2>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jammulai']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' id='jammulai' value='00:00' onkeypress=\"return tanpa_kutip(event)\" style='width:50px;text-align:center;height:25px;font-size:14px' readonly='readonly' onmousemove=\"setCalendar(this.id,'%H:%M')\" autocomplete='off'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jamselesai']."</td>
					<td>:</td>
					<td>
						<input type='text' class='myinputtext' id='jamselesai' value='00:00' onkeypress=\"return tanpa_kutip(event)\" style='width:50px;text-align:center;height:25px;font-size:14px' readonly='readonly' onmousemove=\"setCalendar(this.id,'%H:%M')\" autocomplete='off'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td>
						<select class='select2' id=status >".$optstatus."</select>
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
			
			## VALIDATE
			if($param['jammulai'] == $param['jamselesai']){
				throw new PDOException("waktu mulai dan waktu selesai harus berbeda.");
			}
			$str="select count(kodeorg) as jlhitem from ".$dbname.".pabrik_5shift where kodeorg='".$param['pabrik']."' and shift='".$param['shift']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("pabrik dan shift sudah pernah terdaftar.");
			}
			
			$waktumulai=$param['jammulai'].":00";
			$waktuselesai=$param['jamselesai'].":00";
			
			$data = array(
				'kodeorg'		=> $param['pabrik'],
				'shift' 		=> $param['shift'],
				'jammulai' 		=> $waktumulai,
				'jamselesai' 	=> $waktuselesai,
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'createby'		=> $_SESSION['standard']['userid'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'pabrik_5shift',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			## VALIDATE
			if($param['jammulai'] == $param['jamselesai'] && $param['menitmulai'] == $param['menitselesai'] ){
				throw new PDOException("waktu mulai dan waktu selesai harus berbeda.");
			}
			
			$waktumulai=$param['jammulai'].":00";
			$waktuselesai=$param['jamselesai'].":00";
			
			$data = array(
				'jammulai' 		=> $waktumulai,
				'jamselesai' 	=> $waktuselesai,
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
			);
			$where = "kodeorg='".$param['pabrik']."' and shift='".$param['shift']."'";
			$str = updateQuery($dbname,'pabrik_5shift',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
