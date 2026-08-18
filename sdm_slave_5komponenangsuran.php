<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
$arrkomponen=makeOption($dbname, 'sdm_ho_component', 'id,name');
$arrkomponen2=makeOption($dbname, 'sdm_ho_component', 'id,name','plus=0 and id=120','','',true);
$arrtipekar=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe','aktif=1');
$arrtipekar2=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe','aktif=1','','',true);

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['kodept']."</th>
				<th align=center>Kode ".$_SESSION['lang']['angsuran']."</th>
				<th align=center>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['angsuran']."</th>
				<th align=center>".$_SESSION['lang']['namakomponen']."</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_angsuran_komponen order by kodeorg asc, status desc, komponengaji asc, jenisangsuran asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($val['kodeorg'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['inisial']."</td>";
			$tab.="<td style='text-align:center;'>".$val['jenisangsuran']."</td>";
			$tab.="<td style='text-align:center;'>".$arrkomponen[$val['komponengaji']]."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['kodeorg']."','".$val['inisial']."','".$val['jenisangsuran']."','".$val['komponengaji']."','".$val['status']."')\";>
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
		
		## GET Kode PT
		$arrorgdet = getOrgDetail(3);
		foreach($arrorgdet as $key=>$val){
			@$optpt.="<option value='".$key."'>".$key." - ".$val."</option>";	
		}

		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>
					<td>
						<select class='select2' style='width:250px;' id='kodeorg' >".$optpt."</select>
					</td>
				</tr>
				<tr>
					<td>Kode ".$_SESSION['lang']['angsuran']."</td>
					<td>:</td>
					<td>
						<input type='text' style='width:245px;' value='' id='kodeangsuran' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['angsuran']."</td>
					<td>:</td>
					<td>
						<input type='text' style='width:245px;' value='' id='jenisangsuran' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namakomponen']."</td>
					<td>:</td>
					<td>
						<select class='select2' style='width:250px;' id='komponen'>".$arrkomponen2."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td>
						<select class='select2' style='width:250px;' id=status >".$optstatus."</select>
					</td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value=insert></td>
                    <td colspan=4 align=center>
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
			$str="select count(kodeorg) as jlhitem from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$param['kodeorg']."'  and jenisangsuran='".$param['jenisangsuran']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Jenis Angsuran ini sudah pernah terdaftar di PT ini.");
			}

			$str="select count(kodeorg) as jlhitem from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$param['kodeorg']."'  and inisial='".$param['kodeangsuran']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Kode Angsuran ini sudah pernah terdaftar di PT ini.");
			}

			$str="select count(kodeorg) as jlhitem from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$param['kodeorg']."'  and  komponengaji='".$param['komponen']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Komponen Gaji yang dipilih sudah pernah terdaftar di PT ini.");
			}
			
			$data = array(
				'kodeorg'		=> $param['kodeorg'],
				'inisial' 		=> $param['kodeangsuran'],
				'jenisangsuran' => $param['jenisangsuran'],
				'komponengaji' 	=> $param['komponen'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_angsuran_komponen',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			$str="select count(kodeorg) as jlhitem from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$param['kodeorg']."' and  komponengaji!='".$param['komponen']."'  and (jenisangsuran='".$param['jenisangsuran']."' or inisial='".$param['kodeangsuran']."') ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Kode/Jenis Angsuran ini sudah pernah terdaftar di PT ini.");
			}

			$data = array(
				'inisial' 		=> $param['kodeangsuran'],
				'jenisangsuran' => $param['jenisangsuran'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid']
			);
			$where = "kodeorg='".$param['kodeorg']."' and komponengaji='".$param['komponen']."'";
			$str = updateQuery($dbname,'sdm_angsuran_komponen',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>