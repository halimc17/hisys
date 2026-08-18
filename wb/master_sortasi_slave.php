<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}


$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case 'loaddata':
		$tab="";
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>Move Action</th>
				<th style='text-align:center;'>Kode</th>
				<th style='text-align:center;'>Deskripsi</th>
				<th style='text-align:center;'>Status</th>
			</tr>
		</thead>
		<tbody >";
		
		$str= "select * from ".$dbname.".mssortasi order by nourut asc";
		$res= fetchdata($str);
		$no=0;
		foreach($res as $bar){
			if($bar['status']=='1'){$checked='checked';}else{$checked='';}
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;' style='text-decoration: none;'>
				<a href='#' class='up' style='text-decoration: none;'>
					<i class='fa fa-arrow-circle-up' style='font-size:24px;color:green' title='Up'></i>
				</a>
				&nbsp;&nbsp;
				<a href='#' class='down' style='text-decoration: none;'>
					<i class='fa fa-arrow-circle-down' style='font-size:24px;color:red' title='Down'></i>
				</a>
			</td>";
			$tab.="<td style='text-align:center;' id='kodeform_".$no."'>".$bar['kode']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['deskripsi']."</td>";
			$tab.="<td style='text-align:center;'>
				<input type='checkbox' id='actstt_".$no."' name='actstt_".$no."' ".$checked." onclick=\"getvalstt('".$no."')\">
			</td>";					
			$tab.="</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
	
	case'getvalstt':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'status'=> $param['status']
				);
				$where = "kode='".$param['kode']."'";
				$query = updateQuery($dbname,'mssortasi',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case'changeurut':
		try {
			$owlPDO->beginTransaction();
			
			if($param['nextkode']!=''){
			
				$temp=329027;
					
				## CEK NO URUT
				$str="select nourut from ".$dbname.".mssortasi where kode='".$param['kode']."'";
				$res=fetchdata($str);
				$nourut=$res[0]['nourut'];
				
				$str="select nourut from ".$dbname.".mssortasi where kode='".$param['nextkode']."'";
				$res=fetchdata($str);
				$nourutto=$res[0]['nourut'];
				
				$str="update ".$dbname.".mssortasi set nourut=".$temp." where kode='".$param['nextkode']."'";
				$str1="update ".$dbname.".mssortasi set nourut=".$nourutto." where kode='".$param['kode']."'";
				$str2="update ".$dbname.".mssortasi set nourut=".$nourut." where kode='".$param['nextkode']."'";
				$owlPDO->exec($str);
				$owlPDO->exec($str1);
				$owlPDO->exec($str2);
			}
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
