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
				<th align=center>".$_SESSION['lang']['departemen']."</th>
				<th align=center>".$_SESSION['lang']['createby']."</th>
				<th align=center>".$_SESSION['lang']['createtime']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center >".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		$no=0;
		$str= "select * from ".$dbname.".lgl_5departemen_efiling";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:center;'>".$val['departemen']."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['createby'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['createtime']."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".$val['updatetime']."</td>";
			$tab.="<td style='text-align:center;'>
					<img  style=margin-right: 5px; src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['id']."' ,'".$val['departemen']."')\";>
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
		
		 ## GET Kode Departemen
		 $optdepartemen = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		 $str="select * from ".$dbname.".sdm_5departemen where aktif = '1' order by nama asc ";
		 $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		 $res->setFetchMode(PDO::FETCH_ASSOC);
		 while($bar=$res->fetch())
		 {
			 $optdepartemen.="<option value=".$bar['nama'].">".$bar['nama']."</option>";
		 }

        $tab="";
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
			 <input disabled hidden class=myinputtext id='idx' style=width:247px>
				<tr>
					<td>".$_SESSION['lang']['departemen']."</td>
					<td>:</td>
					<td>
                        <select style='width:250px;' class='select2' id='departemen' >".$optdepartemen."</select>
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
			$str="select count(departemen) as jlhitem from ".$dbname.".lgl_5departemen_efiling where departemen='".$param['departemen']."' ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
			throw new PDOException("Deprtemen sudah ada!  ");
			}
			
			$data = array(
				'departemen'	=> $param['departemen'],
				'createby'		=> $_SESSION['standard']['userid'] ,
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'lgl_5departemen_efiling',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':        
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'departemen'	=> $param['departemen'],
				'updateby'		=> $_SESSION['standard']['userid'] ,
				'updatetime'	=> date('Y-m-d H:i:s')
			);

			$where = " id ='".$param['idx']."' " ;
			$str = updateQuery($dbname,'lgl_5departemen_efiling',$data,$where);
            
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
}
?>
