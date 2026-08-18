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
				<th align=center>".$_SESSION['lang']['status']." Pajak</th>
				<th align=center>PTKP</th>
				<th align=center>".$_SESSION['lang']['createby']."</th>
				<th align=center>".$_SESSION['lang']['createtime']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";

        $style = "style='text-align:center;'";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_5ptkp_pph21 order by status,ptkp asc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$style.">".$no."</td>";
			$tab.="<td ".$style.">".$val['status']."</td>";
			$tab.="<td ".$style.">".number_format($val['ptkp'],0)."</td>";
			$tab.="<td ".$style.">".getNamaKaryawan($val['createby'])."</td>";
			$tab.="<td ".$style.">".$val['createtime']."</td>";			
			$tab.="<td ".$style.">".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td ".$style.">".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['id']."','".$val['status']."','".$val['ptkp']."')\";>
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

        // Get status pajak
        $optstatuspajak='';
        $str="select * from ".$dbname.".sdm_5statuspajak order by nama asc";  
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){
            $optstatuspajak.="<option value='".$bar->kode."'>".$bar->nama." </option>";    
        }
	

		$tab="";
		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['status']." Pajak</td>
					<td>:</td>
					<td>
						<select class='select2' id='status_pajak' >".$optstatuspajak."</select>
					</td>
				</tr>
				<tr>
					<td>PTKP</td>
					<td>:</td>
					<td>
						<input type='number' value='' id='ptkp' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
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
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5ptkp_pph21 where status='".$param['status_pajak']."' and ptkp='".$param['ptkp']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("PTKP sudah ada !");
			}
			
			$data = array(
				'status'		=> $param['status_pajak'],
				'ptkp' 		=> $param['ptkp'],
				'createby' 		=> $_SESSION['standard']['userid'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5ptkp_pph21',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			## VALIDATE
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5ptkp_pph21 where status='".$param['status_pajak']."' and ptkp='".$param['ptkp']."'";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("PTKP sudah ada !");
			}


			$data = array(
				'ptkp' 		    => $param['ptkp'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);

			$where = "status='".$param['status_pajak']."'";
			$str = updateQuery($dbname,'sdm_5ptkp_pph21',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>