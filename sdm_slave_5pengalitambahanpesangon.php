<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

$arrpt=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"tipe='PT'");
$arrpt2=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"tipe='PT'",'','',true);

$arrjenispesangon=makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis','status=1');
$arrjenispesangon2=makeOption($dbname, 'sdm_5jenispesangon', 'kode,jenis','status=1','','',true);

$arrtipekar=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe','aktif=1');
$arrtipekar2=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe','aktif=1','','',true);

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['pt']."</th>
				<th align=center>".$_SESSION['lang']['jenis']." pesangon</th>
				<th align=center>".$_SESSION['lang']['tipekaryawan']."</th>
				<th align=center>Uang Pisah</th>
				<th align=center>UPMK</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_5pengalitambahanpesangon order by jenispesangon asc, status desc ";
		//echo $str;
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$arrpt[$val['kodept']]."</td>";
			$tab.="<td style='text-align:center;'>".nl2br($arrjenispesangon[$val['jenispesangon']])."</td>";
			$tab.="<td style='text-align:center;'>".$arrtipekar[$val['tipekaryawan']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['uangpisah']."</td>";
			$tab.="<td style='text-align:center;'>".$val['upmk']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['kodept']."','".$val['jenispesangon']."','".$val['tipekaryawan']."','".$val['uangpisah']."','".$val['upmk']."','".$val['status']."')\";>
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
		
		
		
		
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
			 	<tr>
					<td>PT</td>
					<td>:</td>
					<td>
						<select class='select2' id=kodept >".$arrpt2."</select>
					</td>
				</tr>
				<tr>
					<td>Jenis Pesangon</td>
					<td>:</td>
					<td>
						<select class='select2' id=jenispesangon >".$arrjenispesangon2."</select>
					</td>
				</tr>
				<tr>
					<td>Tipe Karyawan</td>
					<td>:</td>
					<td>
						<select class='select2' id=tipekaryawan >".$arrtipekar2."</select>
					</td>
				</tr>
				<tr>
					<td>Uang Pisah</td>
					<td>:</td>
					<td><input type='text' id='uangpisah' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' />
				</tr>
				<tr>
					<td>UPMK</td>
					<td>:</td>
					<td><input type='text' id='upmk' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' />
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
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5pengalitambahanpesangon where kodept='".$param['kodept']."' and jenispesangon='".$param['jenispesangon']."' and tipekaryawan='".$param['tipekaryawan']."'  ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Jenis Pesangon dan Tipe Karyawan ini sudah pernah terdaftar pada rentang waktu masa dari dan masa sampai sebelumnya.");
			}
			## END VALIDATE
			
			$data = array(
				'kodept' 		=> $param['kodept'],
				'jenispesangon' 		=> $param['jenispesangon'],
				'tipekaryawan' 		=> $param['tipekaryawan'],
				'uangpisah' 		=> $param['uangpisah'],
				'upmk' 		=> $param['upmk'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5pengalitambahanpesangon',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();

			$data = array(
				'uangpisah' 		=> $param['uangpisah'],
				'upmk' 		=> $param['upmk'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			$where = "kodept='".$param['kodept']."' and jenispesangon='".$param['jenispesangon']."' and tipekaryawan='".$param['tipekaryawan']."'";
			$str = updateQuery($dbname,'sdm_5pengalitambahanpesangon',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>