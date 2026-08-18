<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method = checkPostGet('method', '');
$param = $_POST;
if(count($param)==0){$param = $_GET;}

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['tipe']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['kriteria']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['persen']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['updateby']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['updatetime']."</th>
				<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";
		$arrdata=array(
			'KPI'=>'Hasil (KPI)',
			'Core Values'=>'FASTER (Core Values)',
			'Man Management'=>'Memimpin Tim (Man Management)'
		);
		
		$str= "select * from ".$dbname.".sdm_5pms order by namatipe, kriteria, persen asc";
		$res= fetchdata($str);
		foreach($res as $bar){
			@$no+=1;
			$tab .= "<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td>".$bar['namatipe']."</td>
						<td>".$arrdata[$bar['kriteria']]."</td>
						<td align=right>".$bar['persen']."%</td>
						<td style='text-align:left;'>".getKary($bar['updateby'])."</td>
						<td style='text-align:left;'>".$bar['updatetime']."</td>
						<td align=center>
							<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['id']."','".$bar['tipe']."','".$bar['kriteria']."','".$bar['persen']."')\";>
						</td>
						<td align=center>
							<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['id']."');>
						</td>
					</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;

	case 'addnew':
		$tab.="<table border=0 cellpadding=2 cellspacing=1>
					<tr>
						<td style=min-width:150px>".$_SESSION['lang']['tipe']."</td>
						<td>
							<select class='select2' style='width:350px;' id=tipe>
								<option value=''>".$_SESSION['lang']['pilihdata']."</option>
								<option value='1'>Mempunyai Anak Buah</option>
								<option value='2'>Tidak Mempunyai Anak Buah</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style=min-width:150px>".$_SESSION['lang']['kriteria']."</td>
						<td>
							<select class='select2' style='width:350px;' id=kriteria>
								<option value=''>".$_SESSION['lang']['pilihdata']."</option>
								<option value='KPI'>Hasil (KPI)</option>
								<option value='Core Values'>FASTER (Core Values)</option>
								<option value='Man Management'>Memimpin Tim (Man Management)</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style=min-width:150px>".$_SESSION['lang']['persen']." (%)</td>
						<td>
							<input type=number class=myinputtext style='width:343px;height:30px;font-size:14px;padding-left:5px;' id=persen> 
						</td>
					</tr>
				
	                <tr>
	                    <td><input type=hidden id=mode value=insert><input type=hidden id=id value=''></td>
	                    <td>
							<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
	                    </td>
	                </tr>
            </table>
		";
		echo $tab;
	break;

	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'tipe'      => $param['tipe'],
				'namatipe'  => $param['namatipe'],
				'kriteria'  => $param['kriteria'],
				'persen'    => $param['persen'],
				'createby'  => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'sdm_5pms',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'tipe'      => $param['tipe'],
					'namatipe'  => $param['namatipe'],
					'kriteria'  => $param['kriteria'],
					'persen'    => $param['persen'],
					'updateby'  => $_SESSION['standard']['userid'],
					'updatetime'=> date("Y-m-d H:i:s")
				);
				$where = "id='".$param['id']."'";
				$query = updateQuery($dbname,'sdm_5pms',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'delete':
		try {
			$owlPDO->beginTransaction();

			$str = "delete from " . $dbname . ".sdm_5pms where id='".$param['id']."'";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
	break;
}
?>
