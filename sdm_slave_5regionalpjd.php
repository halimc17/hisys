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
				<th align=center>Kode ".$_SESSION['lang']['regional']."</th>
				<th align=center>Nama ".$_SESSION['lang']['regional']."</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_5regionalpjd order by regional asc ";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$val['regional']."</td>";
			$tab.="<td style='text-align:center;'>".$val['nama']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['regional']."','".$val['nama']."','".$val['status']."')\";>
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
				@$optstatus.="<option value=".$key." selected>".$val."</option>";							
			}else{
				@$optstatus.="<option value=".$key.">".$val."</option>";							
			}
		}
		
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>Regional</td>
					<td>:</td>
					<td>
                        <input type='text' value='' id='regional' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>Nama Regional</td>
					<td>:</td>
					<td>
						<input type='text' value='' id='nama' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
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
            </table>";
		
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			## VALIDATE
			$str="select count(regional) as jlhitem from ".$dbname.".sdm_5regionalpjd where regional='".$param['regional']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Regional Sudah Ada....");
			}
			
			$data = array(
				'regional'		=> $param['regional'],
				'nama' 		    => $param['nama'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5regionalpjd',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();

			$data = array(
				'nama' 		     => $param['nama'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid']
			);
			$where = "regional='".$param['regional']."' and nama='".$param['nama']."'";
			$str = updateQuery($dbname,'sdm_5regionalpjd',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Warning, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>