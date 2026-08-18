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
				<th align=center>Masa Kerja Dari (>=)</th>
				<th align=center>Masa Kerja Sampai (<=)</th>
				<th align=center>Nilai Pengali</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_5uangpmkpesangon order by jenispesangon asc, status desc ";
		//echo $str;
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$arrpt[$val['kodept']]."</td>";
			$tab.="<td style='text-align:center;'>".nl2br($arrjenispesangon[$val['jenispesangon']])."</td>";
			$tab.="<td style='text-align:center;'>".$arrtipekar[$val['tipekaryawan']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['masakerjadari']."</td>";
			$tab.="<td style='text-align:center;'>".$val['masakerjasampai']."</td>";
			$tab.="<td style='text-align:center;'>".$val['pengali']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['kode']."','".$val['kodept']."','".$val['jenispesangon']."','".$val['tipekaryawan']."','".$val['masakerjadari']."','".$val['masakerjasampai']."','".$val['pengali']."','".$val['status']."')\";>
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
					<td>Masa Kerja Dari (>=)</td>
					<td>:</td>
					<td><input type='text' id='masakerjadari' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' />
				</tr>
				<tr>
					<td>Masa Kerja Sampai (<=)</td>
					<td>:</td>
					<td><input type='text' id='masakerjasampai' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' />
				</tr>
				<tr>
					<td>Nilai Pengali</td>
					<td>:</td>
					<td><input type='text' id='pengali' class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:150px;' />
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
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5uangpmkpesangon where kodept='".$param['kodept']."' and  jenispesangon='".$param['jenispesangon']."' and tipekaryawan='".$param['tipekaryawan']."'  and masakerjadari<='".$param['masakerjadari']."' and masakerjasampai>='".$param['masakerjadari']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Masa Kerja Dari ini sudah pernah terdaftar pada rentang waktu masa dari dan masa sampai sebelumnya.");
			}

			$str="select count(*) as jlhitem from ".$dbname.".sdm_5uangpmkpesangon where kodept='".$param['kodept']."' and  jenispesangon='".$param['jenispesangon']."' and tipekaryawan='".$param['tipekaryawan']."'  and masakerjadari<='".$param['masakerjasampai']."' and masakerjasampai>='".$param['masakerjasampai']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Masa Kerja Sampai ini sudah pernah terdaftar pada rentang waktu masa dari dan masa sampai sebelumnya.");
			}
			## END VALIDATE
			

			$data = array(
				'kodept' 		=> $param['kodept'],
				'jenispesangon' 		=> $param['jenispesangon'],
				'tipekaryawan' 		=> $param['tipekaryawan'],
				'masakerjadari' 		=> $param['masakerjadari'],
				'masakerjasampai' 		=> $param['masakerjasampai'],
				'pengali' 		=> $param['pengali'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5uangpmkpesangon',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();

			## VALIDATE
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5uangpmkpesangon where kodept='".$param['kodept']."' and  jenispesangon='".$param['jenispesangon']."' and tipekaryawan='".$param['tipekaryawan']."'  and masakerjadari<='".$param['masakerjadari']."' and masakerjasampai>='".$param['masakerjadari']."' and kode!='".$param['kode']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Masa Kerja Dari ini sudah pernah terdaftar pada rentang waktu masa dari dan masa sampai sebelumnya.");
			}

			$str="select count(*) as jlhitem from ".$dbname.".sdm_5uangpmkpesangon where  kodept='".$param['kodept']."' and jenispesangon='".$param['jenispesangon']."' and tipekaryawan='".$param['tipekaryawan']."'  and masakerjadari<='".$param['masakerjasampai']."' and masakerjasampai>='".$param['masakerjasampai']."' and kode!='".$param['kode']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Masa Kerja Sampai ini sudah pernah terdaftar pada rentang waktu masa dari dan masa sampai sebelumnya.");
			}
			## END VALIDATE

			$data = array(
				'kodept' 		=> $param['kodept'],
				'jenispesangon' 		=> $param['jenispesangon'],
				'tipekaryawan' 		=> $param['tipekaryawan'],
				'masakerjadari' 		=> $param['masakerjadari'],
				'masakerjasampai' 		=> $param['masakerjasampai'],
				'pengali' 		=> $param['pengali'],
				'status' 		=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);
			$where = "kode='".$param['kode']."' ";
			$str = updateQuery($dbname,'sdm_5uangpmkpesangon',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>