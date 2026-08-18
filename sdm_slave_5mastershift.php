<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'Aktif','0'=>'Non Aktif');
switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$where = " and id='".$param['id']."'";
			$str = "delete from " . $dbname . ".sdm_5mastershift where 1=1 ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$param['shift'] = str_replace(" ","",$param['shift']);
				$data = array(
					'shift'    => $param['shift'],
					'namashift'=> $param['namashift'],
					'status'   => $param['status'],
					'updateby' => $_SESSION['standard']['userid']
				);
				$where = "id='".$param['id']."'";
				$query = updateQuery($dbname,'sdm_5mastershift',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$str = "select * from ".$dbname.".sdm_5mastershift where shift='".$param['shift']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Data sudah pernah diinput.");
			}
			
			$param['shift'] = str_replace(" ","",$param['shift']);
			
			$data = array(
				'shift'     => $param['shift'],
				'namashift' => $param['namashift'],
				'status'    => $param['status'],
				'createby'  => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'sdm_5mastershift',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'addnew':
		$arrshift=array('1'=>'Aktif','0'=>'Non Aktif');
		$optshift="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arrshift as $val => $key){
			if($param['status']==$val){
				$optshift.="<option value=".$val." selected>".$key."</option>";
			}else{				
				$optshift.="<option value=".$val.">".$key."</option>";
			}
		}
		if($param['mode']=='update'){
			$tombol='Update';
		}else{
			$tombol='Save';
		}
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<input id=id type=hidden>
					<td style=min-width:100px>".$_SESSION['lang']['kode']." ".$_SESSION['lang']['shift']."</td>
					<td><input type=text class=myinputtext onkeydown=\"upperCaseF(this)\" style='width:300px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" id=kodeshift></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nama']."</td>
					<td><input type=text class=myinputtext onkeydown=\"upperCaseF(this)\" style='width:300px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" id=namashift></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td><select class='select2' style='width:305px;' id=status >".$optshift."</select></td>
				</tr>
                <tr>
                    <td colspan=40 align=center>
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:160px;height:30px' class=mybutton>".$tombol."</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['shift']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['shift']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['status']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updatetime']."</th>
				<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";
		
		

		$str= "select * from ".$dbname.".sdm_5mastershift";
		$res= fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$bar['shift']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['namashift']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($bar['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['updatetime']."</td>";
			
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['shift']."','".$bar['namashift']."','".$bar['status']."','".$bar['id']."')\";></td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['id']."');></td>";
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
}
?>
