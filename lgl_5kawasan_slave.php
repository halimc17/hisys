<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}


switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['jenis']."</th>
				<th align=center>".$_SESSION['lang']['createby']."</th>
				<th align=center>".$_SESSION['lang']['createtime']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center >".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		$no=0;
		$str= "select * from ".$dbname.".lgl_5kawasan";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$val['jenis']."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['createby'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['createtime']."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['updatetime']."</td>";
			$tab.="<td style='text-align:center;'>
					<img  style=margin-right: 5px; src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['id']."' ,'".$val['jenis']."')\";>
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
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
			 <input disabled hidden class=myinputtext id='idx' style=width:247px>
				<tr>
					<td>".$_SESSION['lang']['jenis']."</td>
					<td>:</td>
					<td>
                        <input class=myinputtext id='jenis' style=width:247px>
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
			$str="select count(jenis) as jlhitem from ".$dbname.".lgl_5kawasan where jenis='".$param['jenis']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Jenis surat sudah ada!  ");
			}
			
			$data = array(
				'jenis'	=> $param['jenis'],
				'createby'		=> $_SESSION['standard']['userid'] ,
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'lgl_5kawasan',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':        
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'jenis'	=> $param['jenis'],
				'updateby'		=> $_SESSION['standard']['userid'] ,
				'updatetime'	=> date('Y-m-d H:i:s')
			);

			$where = " id ='".$param['idx']."' " ;
			$str = updateQuery($dbname,'lgl_5kawasan',$data,$where);
            
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
