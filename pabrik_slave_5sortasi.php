<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
$arrkmdt=array('CPO'=>'CPO','KER'=>'KERNEL');

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['kode']."</th>
				<th align=center>".$_SESSION['lang']['uraian']."</th>
				<th align=center>".$_SESSION['lang']['janjang']."</th>
				<th align=center>".$_SESSION['lang']['persen']."</th>
				<th align=center>".$_SESSION['lang']['kg']."</th>
				<th align=center>".$_SESSION['lang']['persentase']." ".$_SESSION['lang']['pengali']."</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center class='no-sort'>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".wb_5sortasi order by status desc, kode asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$val['kode']."</td>";
			$tab.="<td style='text-align:left;'>".$val['deskripsi']."</td>";
			$tab.="<td style='text-align:center;'>".($val['jjg']==''?'':'&#10004')."</td>";
			$tab.="<td style='text-align:center;'>".($val['persen']==''?'':'&#10004')."</td>";
			$tab.="<td style='text-align:center;'>".($val['kg']==''?'':'&#10004')."</td>";
			$tab.="<td style='text-align:right;'>".$val['persentasepengali']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['kode']."','".$val['deskripsi']."','".$val['jjg']."','".$val['persen']."','".$val['kg']."','".$val['persentasepengali']."','".$val['status']."')\";>
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
		
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['kode']."</td>
					<td>:</td>
					<td>
						<input class=myinputtext style='text-align:left;height:25px;font-size:14px;width:260px;' type=text id=kode onkeydown='upperCaseF(this)' maxlength=4>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['uraian']."</td>
					<td>:</td>
					<td>
						<input class=myinputtext style='text-align:left;height:25px;font-size:14px;width:260px;' type=text id=uraian maxlength=250>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['janjang']."</td>
					<td>:</td>
					<td>
						<input type='checkbox' id='janjang' checked />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['persen']."</td>
					<td>:</td>
					<td>
						<input type='checkbox' id='persen' checked />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kg']."</td>
					<td>:</td>
					<td>
						<input type='checkbox' id='kg' checked />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['persentase']." ".$_SESSION['lang']['pengali']."</td>
					<td>:</td>
					<td>
						<input class=myinputtext style='text-align:right;height:25px;font-size:14px;width:60px;' maxlength=4 type=text id=persentasepengali onkeypress=\"return isNumberKey(event);\">
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>:</td>
					<td>
						<select class='select2' id=status>".$optstatus."</select>
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
			$str="select count(kode) as jlhitem from ".$dbname.".wb_5sortasi where kode='".$param['kode']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Kode sortasi sudah pernah terdaftar.");
			}
			
			$janjang="";
			$persen="";
			$kg="";
			if($param['janjang']==1){
				$janjang=$param['kode']."_JJG";
			}
			if($param['persen']==1){
				$persen=$param['kode']."_PERSEN";
			}
			if($param['kg']==1){
				$kg=$param['kode']."_KG";
			}
			
			$data = array(
				'kode'		 		=> $param['kode'],
				'deskripsi'  		=> $param['uraian'],
				'jjg'		 		=> $janjang,
				'persen'	 		=> $persen,
				'kg'		 		=> $kg,
				'persentasepengali'	=> $param['persentasepengali'],
				'status' 	 		=> $param['status'],
				'updateby' 	 		=> $_SESSION['standard']['userid'],
				'createby'	 		=> $_SESSION['standard']['userid'],
				'createtime' 		=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'wb_5sortasi',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			$janjang="";
			$persen="";
			$kg="";
			if($param['janjang']==1){
				$janjang=$param['kode']."_JJG";
			}
			if($param['persen']==1){
				$persen=$param['kode']."_PERSEN";
			}
			if($param['kg']==1){
				$kg=$param['kode']."_KG";
			}
			
			$data = array(
				'deskripsi'			=> $param['uraian'],
				'jjg'				=> $janjang,
				'persen'			=> $persen,
				'kg'				=> $kg,
				'persentasepengali'	=> $param['persentasepengali'],
				'status' 			=> $param['status'],
				'updateby' 			=> $_SESSION['standard']['userid']
			);
			$where = "kode='".$param['kode']."'";
			$str = updateQuery($dbname,'wb_5sortasi',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
