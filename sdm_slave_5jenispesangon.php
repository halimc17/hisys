<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
$arrstatuspajak=array('0'=>'KANTOR');

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['jenis']."</th>
				<th align=center>Status Pajak</th>
				<th align=center>Max Pajak (persentase pengali dalam nilai koma)</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_5jenispesangon order by kode asc, status desc ";
		//echo $str;
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".nl2br($val['jenis'])."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatuspajak[$val['statuspajak']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['maxpajak']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['kode']."','".$val['jenis']."','".$val['statuspajak']."','".$val['maxpajak']."','".$val['status']."')\";>
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
		$optstatus='';
		foreach($arrstatus as $key => $val){
			if($key=='1'){
				$optstatus.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$optstatus.="<option value=".$key.">".$val."</option>";							
			}
		}

		$optstatuspajak='';
		foreach($arrstatuspajak as $key => $val){
			if($key=='1'){
				$optstatuspajak.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$optstatuspajak.="<option value=".$key.">".$val."</option>";							
			}
		}
		
		
		
		
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>Jenis</td>
					<td>:</td>
					<td>
						<textarea class=myinputtext style='width:495px;height:150px;font-size:14px;' id=jenis ></textarea>
					</td>
				</tr>
				<tr>
					<td>Status Pajak</td>
					<td>:</td>
					<td>
						<select class='select2' id=statuspajak >".$optstatuspajak."</select>
					</td>
				</tr>
				<tr>
					<td>Max Pajak (Persentase pengali dalam nilai koma)</td>
					<td>:</td>
					<td>
						<input type='text' value='0' id='maxpajak' class='myinputtextnumber' onkeypress='return angka_doang(event);'>
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
                    <td><input type=hidden id=kode value=''></td>
                    <td colspan=4>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
                    </td>
                </tr>
            </table>";
		
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			## VALIDATE
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5jenispesangon where jenis='".$param['jenis']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Jenis ini sudah pernah terdaftar.");
			}
			## END VALIDATE
			
			$data = array(
				'jenis' 		=> $param['jenis'],
				'statuspajak' 		=> $param['statuspajak'],
				'maxpajak' 		=> $param['maxpajak'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5jenispesangon',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();

			## VALIDATE
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5jenispesangon where jenis='".$param['jenis']."' and kode!='".$param['kode']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Jenis ini sudah pernah terdaftar.");
			}
			## END VALIDATE

			$data = array(
				'jenis' 		=> $param['jenis'],
				'statuspajak' 	=> $param['statuspajak'],
				'maxpajak' 		=> $param['maxpajak'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			$where = "kode='".$param['kode']."'";
			$str = updateQuery($dbname,'sdm_5jenispesangon',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>