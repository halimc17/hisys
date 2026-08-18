<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$where = " id='".$param['id']."'";
			$str = "delete from " . $dbname . ".log_5list_purchaser where ".$where."";
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
				$data = array(
					'tipeorg'         => $param['tipeorg'],
					'managerid'       => $param['managerid'],
					'purchaserid'     => $param['purchaserid'],
					'verifikator'     => $param['verifikator'],
					'createtime'      => date("Y-m-d H:i:s"),
					'createdby'       => $_SESSION['standard']['userid']
				);
				$where = "id='".$param['id']."'";
				$query = updateQuery($dbname,'log_5list_purchaser',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'tipeorg'         => $param['tipeorg'],
				'managerid'       => $param['managerid'],
				'purchaserid'     => $param['purchaserid'],
				'verifikator'     => $param['verifikator'],
				'createtime'      => date("Y-m-d H:i:s"),
				'createdby'        => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'log_5list_purchaser',$data,$cols); #exit("error".$query);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'addnew':
		$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optpurc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$str = "select distinct tipe from ".$dbname.".organisasi  where length(kodeorganisasi)='4' order by tipe";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optkodeorg.="<option value='".$bar['tipe']."'>".$bar['tipe']."</option>";
		}
		
		$where=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tanggalmasuk<='".date("Y-m-d")."'";
		$str = "select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where bagian IN ('PGD','MGT')  ".$where." order by namakaryawan asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optpurc.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."</option>";
		}	
		
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td class=bintang style=min-width:150px>".str_replace("."," ",$_SESSION['lang']['orgtype'])."</td>
					<td><select class='select2' style='width:400px;' id=tipeorg >".$optkodeorg."</select></td>
				</tr>
				<tr  style='display:none'>
					<td class=bintang>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['manager']."</td>
					<td><select class='select2' style='width:400px;' id=managerid >".$optpurc."</select></td>
				</tr>
				<tr>
					<td class=bintang>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['purchaser']."</td>
					<td><select class='select2' style='width:400px;' id=purchaserid >".$optpurc."</select></td>
				</tr>
				<tr  style='display:none'>
					<td class=bintang>".$_SESSION['lang']['diverifikasioleh']."</td>
					<td><select class='select2' style='width:400px;' id=verifikator >".$optpurc."</select></td>
				</tr>
                <tr>
                    <td colspan=4 align=center>
						<input type=hidden id=id >
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
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
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;' rowspan=2>".str_replace("."," ",$_SESSION['lang']['orgtype'])."</th>
				<th style='text-align:center;display:none' rowspan=2>".$_SESSION['lang']['manager']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['purchaser']."</th>
				<th style='text-align:center;display:none' rowspan=2>".$_SESSION['lang']['diverifikasioleh']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['createby']."</th>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['updatetime']."</th>
				<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th style='text-align:center;'>".$_SESSION['lang']['edit']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['delete']."</th>
			</tr>
		</thead>
		<tbody >";
		
		$str = "select * from ".$dbname.".log_5list_purchaser where 1=1 order by managerid, tipeorg";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$bar['tipeorg']."</td>";
			$tab.="<td  style='text-align:center;display:none'>".getKary($bar['managerid'])."</td>";
			$tab.="<td style='text-align:center;'>".getKary($bar['purchaserid'])."</td>";
			$tab.="<td style='text-align:center;display:none'>".getKary($bar['verifikator'])."</td>";
			$tab.="<td style='text-align:center;'>".getKary($bar['createdby'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['createtime']."</td>";
					
			$tab.="<td style='text-align:center;width:25px'><img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['id']."','".$bar['tipeorg']."','".$bar['managerid']."','".$bar['purchaserid']."','".$bar['verifikator']."')\";></td>";
			$tab.="<td style='text-align:center;width:25px'><img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['id']."');></td>";
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
