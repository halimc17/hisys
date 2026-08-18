<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrkategori=array('A'=>'A','B'=>'B','C'=>'C');

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['status']." Pajak</th>
				<th align=center>".$_SESSION['lang']['kategori']."</th>
				<th align=center>".$_SESSION['lang']['min']."</th>
				<th align=center>".$_SESSION['lang']['max']."</th>
				<th align=center>".$_SESSION['lang']['tarif']." (%)</th>
				<th align=center>".$_SESSION['lang']['createby']."</th>
				<th align=center>".$_SESSION['lang']['createtime']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_5tarifpph21 order by status,kategori,minim,maxim,tarif asc";
		$res= fetchdata($str);
		foreach($res as $val){

            if($val['kategori'] == 'A'){
                $style = "style='text-align:center;color:blue;'";
            }elseif($val['kategori'] == 'B'){
                $style = "style='text-align:center;color:red;'";
            }elseif($val['kategori'] == 'C'){
                $style = "style='text-align:center;color:green;'";
            }

			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$style.">".$no."</td>";
			$tab.="<td ".$style.">".$val['status']."</td>";
			$tab.="<td ".$style.">".$val['kategori']."</td>";
			$tab.="<td ".$style.">".number_format($val['minim'],0)."</td>";
			$tab.="<td ".$style.">".number_format($val['maxim'],0)."</td>";
			$tab.="<td ".$style.">".number_format($val['tarif'],2)." %</td>";
			$tab.="<td ".$style.">".getNamaKaryawan($val['createby'])."</td>";
			$tab.="<td ".$style.">".$val['createtime']."</td>";			
			$tab.="<td ".$style.">".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td ".$style.">".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['id']."','".$val['status']."','".$val['kategori']."','".$val['minim']."','".$val['maxim']."','".$val['tarif']."')\";>
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

        foreach($arrkategori as $key => $val){
		    @$optkategori.="<option value=".$key.">".$val."</option>";							
		}

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
						<select class='select2' style='width: 250px;' id='status_pajak' >".$optstatuspajak."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kategori']."</td>
					<td>:</td>
					<td>
                        <select class='select2' style='width: 100px;' id='kategori' >".$optkategori."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['min']."</td>
					<td>:</td>
					<td>
						<input style='width: 250px;' type='number' value='' id='minim' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['max']."</td>
					<td>:</td>
					<td>
                        <input style='width: 250px;' type='number' value='' id='maxim' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tarif']." ( % )</td>
					<td>:</td>
					<td>
                        <input style='width: 250px;' type='number' value='' id='tarif' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
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
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5tarifpph21 where status='".$param['status_pajak']."' and kategori='".$param['kategori']."' and minim='".$param['minim']."' and maxim='".$param['maxim']."' and tarif='".$param['tarif']."'  ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Tarif sudah ada !");
			}
			
			$data = array(
				'status'		=> $param['status_pajak'],
				'kategori' 		=> $param['kategori'],
				'minim'         => $param['minim'],
				'maxim' 	    => $param['maxim'],
				'tarif' 		=> $param['tarif'],
				'createby' 		=> $_SESSION['standard']['userid'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5tarifpph21',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			## VALIDATE
			$str="select count(*) as jlhitem from ".$dbname.".sdm_5tarifpph21 where status='".$param['status_pajak']."' and kategori='".$param['kategori']."' and minim='".$param['minim']."' and maxim='".$param['maxim']."' and tarif='".$param['tarif']."'  ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				throw new PDOException("Tarif sudah ada !");
			}

			$data = array(
				'tarif' 		=> $param['tarif'],
				'updateby' 		=> $_SESSION['standard']['userid'],
				'updatetime'	=> date('Y-m-d H:i:s')
			);

			$where = "status='".$param['status_pajak']."' and kategori='".$param['kategori']."' and minim='".$param['minim']."' and maxim='".$param['maxim']."'";
			$str = updateQuery($dbname,'sdm_5tarifpph21',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>
<script>
	getSelect2();
</script>