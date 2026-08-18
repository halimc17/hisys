<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrtipe=array('0'=>'Header','1'=>'Sub Header','2'=>'Inputan','3'=>'Total/Subtotal/Score');
$arrtipenilai=array('0'=>'Inputan Manual','1'=>'Dari Setup');
$arrtotaloperator=array('0'=>'-','1'=>'+','2'=>'/','3'=>'X','4'=>'Rata-Rata','5'=>'Dari Setup');

switch($method){
	case 'loaddata':
		$sudahadadata=array();
		$str = "SELECT tahun FROM ".$dbname.".sdm_presentasi group by tahun"; 
        $res = fetchdata($str);
        foreach($res as $bar){
            $sudahadadata[$bar['tahun']] = 1;
        }

		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>Tahun</th>
				<th align=center>Tipe</th>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>Text</th>
				<th align=center>Tipe Nilai</th>
				<th align=center>Setup Tipe Nilai</th>
				<th align=center>Bobot</th>
				<th align=center>No Urut Header/Subheader/Total/Subtotal/Score</th>
				<th align=center>Operator</th>
				<th align=center>Setup Operator</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$str= "select * from ".$dbname.".sdm_5presentasi order by tahun desc, nourut asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$val['tahun']."</td>";
			$tab.="<td style='text-align:center;'>".@$arrtipe[$val['tipe']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['nourut']."</td>";
			$tab.="<td style='text-align:center;'>".$val['text']."</td>";
			$tab.="<td style='text-align:center;'>".@$arrtipenilai[$val['tipenilai']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['kodetipenilai']."</td>";
			$tab.="<td style='text-align:center;'>".$val['bobot']."</td>";
			$tab.="<td style='text-align:center;'>".$val['nouruttotal']."</td>";
			$tab.="<td style='text-align:center;'>".@$arrtotaloperator[$val['totaloperator']]."</td>";
			$tab.="<td style='text-align:center;'>".$val['kodetotaloperator']."</td>";
			if(isset($sudahadadata[$val['tahun']])){
				$tab.="<td style='text-align:center;width:25px'>Sudah Ada Data Transaksi Disiplin</td>";
			}else{
				$tab.="<td style='text-align:center;width:25px'>
					<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['id']."','".$val['tahun']."','".$val['tipe']."','".$val['nourut']."','".$val['text']."','".$val['tipenilai']."','".$val['kodetipenilai']."','".$val['bobot']."','".$val['nouruttotal']."','".$val['totaloperator']."','".$val['kodetotaloperator']."')\";>
					<img src='images/application/application_delete.png' class='resicon' title='Edit' onclick=\"deletedata('".$val['id']."')\";>
				</td>";
			}
			$tab.="</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
	case 'deletedata':
		$where = " and id='".$param['id']."'";
		$str = "delete from ".$dbname.".sdm_5presentasi WHERE 1=1 ".$where."";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'addnew':
		$tab="";

		$optsetupnilai="<option value=''></option>";
		$str= "select * from ".$dbname.".sdm_5setuppenilaian where status='1' group by judul order by judul desc";
		$res= fetchdata($str);
		foreach($res as $val){
			$optsetupnilai.="<option value=".$val['judul'].">".$val['judul']."</option>";
		}

		$optsetuptotaloperator="<option value=''></option>";
		$str= "select * from ".$dbname.".sdm_5setupscore where status='1' group by judul order by judul desc";
		$res= fetchdata($str);
		foreach($res as $val){
			$optsetuptotaloperator.="<option value=".$val['judul'].">".$val['judul']."</option>";
		}

		$opttipe="<option value=''></option>";
		foreach($arrtipe as $key => $val){
			if($key=='1'){
				$opttipe.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$opttipe.="<option value=".$key.">".$val."</option>";							
			}
		}

		$opttipenilai="<option value=''></option>";
		foreach($arrtipenilai as $key => $val){
			if($key=='1'){
				$opttipenilai.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$opttipenilai.="<option value=".$key.">".$val."</option>";							
			}
		}

		$opttotaloperator="<option value=''></option>";
		foreach($arrtotaloperator as $key => $val){
			if($key=='1'){
				$opttotaloperator.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$opttotaloperator.="<option value=".$key.">".$val."</option>";							
			}
		}

		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>Tahun</td>
					<td>:</td>
					<td>
						<input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=tahun >
					</td>
				</tr>
				<tr>
					<td>Tipe</td>
					<td>:</td>
					<td>
						<select class='select2' id=tipe >".$opttipe."</select>
					</td>
				</tr>
				<tr>
					<td>No. Urut</td>
					<td>:</td>
					<td>
						<input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=nourut >
					</td>
				</tr>
				<tr>
					<td>Text</td>
					<td>:</td>
					<td>
						<textarea  class=myinputtext style='width:495px;height:150px;font-size:14px;' id=text ></textarea></td>
					</td>
				</tr>
				<tr>
					<td>Tipe Nilai</td>
					<td>:</td>
					<td>
						<select class='select2' id=tipenilai >".$opttipenilai."</select>
					</td>
				</tr>
				<tr>
					<td>Setup Tipe Nilai</td>
					<td>:</td>
					<td>
						<select class='select2' id=kodetipenilai >".$optsetupnilai."</select>
					</td>
				</tr>
				<tr>
					<td>Bobot/Pengali(bukan dalan persentase)</td>
					<td>:</td>
					<td>
						<input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=bobot >
					</td>
				</tr>
				<tr>
					<td>No. Urut Header/Subheader/Total/Subtotal/Score</td>
					<td>:</td>
					<td>
						<input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=nouruttotal >
					</td>
				</tr>
				<tr>
					<td>Operator</td>
					<td>:</td>
					<td>
						<select class='select2' id=totaloperator >".$opttotaloperator."</select>
					</td>
				</tr>
				<tr>
					<td>Setup Operator</td>
					<td>:</td>
					<td>
						<select class='select2' id=kodetotaloperator >".$optsetuptotaloperator."</select>
					</td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value=insert></td>
                    <td><input type=hidden id=id value=''></td>
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
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5presentasi where nourut='".$param['nourut']."'  and tahun='".$param['tahun']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("No.Urut dan Tahun ini sudah terdaftar");
			}

			if($param['tipe']=='0' or $param['tipe']=='1' or $param['tipe']=='3'){
				if($param['nouruttotal']==''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." No. Urut Header/Subheader/Total/Subtotal/Score Harus Berisi");
				}

				if($param['tipe']=='0'){
					$str="select count(*) as jlhitem from ".$dbname.".sdm_5presentasi where tipe='0'  and tahun='".$param['tahun']."'";
					$res=fetchdata($str);
					if($res[0]['jlhitem'] > 0){
						throw new PDOException("Untuk Tipe Header hanya ada boleh 1 dan Tahun ini sudah terdaftar");
					}
				}

				if($param['tipenilai']!='' or $param['kodetipenilai']!=''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." Tipe Nilai Harus Tidak Berisi");
				}

				if($param['totaloperator']=='5' and $param['kodetotaloperator']==''){
					throw new PDOException("Operator ".$arrtipenilai[$param['tipenilai']]." Setup Operator Harus Berisi");
				}
			}else{
				if($param['nouruttotal']!='' or $param['totaloperator']!='' or $param['kodetotaloperator']!=''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." Tipe Nilai Harus Tidak Berisi");
				}

				if($param['tipenilai']==''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." Tipe Nilai Harus Berisi");
				}

				if($param['tipenilai']=='1' and $param['kodetipenilai']==''){
					throw new PDOException("Tipe Nilai ".$arrtipenilai[$param['tipenilai']]." Setup Tipe Nilai Harus Berisi");
				}
			}
			
			$data = array(
				'tahun'				=> $param['tahun'],
				'tipe' 				=> $param['tipe'],
				'nourut' 			=> $param['nourut'],
				'text'	 			=> $param['text'],
				'tipenilai' 		=> $param['tipenilai'],
				'kodetipenilai' 	=> $param['kodetipenilai'],
				'nouruttotal' 		=> $param['nouruttotal'],
				'totaloperator' 	=> $param['totaloperator'],
				'kodetotaloperator' => $param['kodetotaloperator']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5presentasi',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			## VALIDATE
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5presentasi where id!='".$param['id']."' and nourut='".$param['nourut']."'  and tahun='".$param['tahun']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("No.Urut dan Tahun ini sudah terdaftar");
			}

			if($param['tipe']=='0' or $param['tipe']=='1' or $param['tipe']=='3'){
				if($param['nouruttotal']==''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." No. Urut Header/Subheader/Total/Subtotal/Score Harus Berisi");
				}

				if($param['tipenilai']!='' or $param['kodetipenilai']!=''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." Tipe Nilai Harus Tidak Berisi");
				}

				if($param['totaloperator']=='5' and $param['kodetotaloperator']==''){
					throw new PDOException("Operator ".$arrtipenilai[$param['tipenilai']]." Setup Operator Harus Berisi");
				}
			}else{
				if($param['nouruttotal']!='' or $param['totaloperator']!='' or $param['kodetotaloperator']!=''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." Tipe Nilai Harus Tidak Berisi");
				}

				if($param['tipenilai']==''){
					throw new PDOException("Tipe ".$arrtipe[$param['tipe']]." Tipe Nilai Harus Berisi");
				}

				if($param['tipenilai']=='1' and $param['kodetipenilai']==''){
					throw new PDOException("Tipe Nilai ".$arrtipenilai[$param['tipenilai']]." Setup Tipe Nilai Harus Berisi");
				}
			}

			$data = array(
				'tahun'				=> $param['tahun'],
				'tipe' 				=> $param['tipe'],
				'nourut' 			=> $param['nourut'],
				'text'	 			=> $param['text'],
				'tipenilai' 		=> $param['tipenilai'],
				'kodetipenilai' 	=> $param['kodetipenilai'],
				'nouruttotal' 		=> $param['nouruttotal'],
				'totaloperator' 	=> $param['totaloperator'],
				'kodetotaloperator' => $param['kodetotaloperator']
			);
			$where = "id='".$param['id']."'";
			$str = updateQuery($dbname,'sdm_5presentasi',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>