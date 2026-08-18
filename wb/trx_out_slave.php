<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$kodeproduk='4001000001';
$optnmproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$kodeproduk."'");
$namaproduk=$optnmproduk[$kodeproduk];

switch($method){
	case 'generatenotiket':
        echo generatenotiket();
    break;
	
	case'getdivisi':
		$optdivisi="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		$str="select divcode,divname from ".$dbname.".msdivisi where unitcode='".$param['unit']."' and divstatus='1'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$optdivisi="";
			foreach ($res as $val) {
				if($param['divisi']==$val['divcode']){
					$optdivisi.="<option value='".$val['divcode']."' selected>".$val['divname']."</option>";					
				}else{
					$optdivisi.="<option value='".$val['divcode']."'>".$val['divname']."</option>";
				}
			}
		}
        
        echo $optdivisi;
	break;
	
	case 'getkontrak':
        $optkontrak="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		$str="select * from ".$dbname.".mscontractpurchase";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$optkontrak.="<option value='".$val['ctrno']."'>".$val['ctrno']."</option>";
		}
        
        echo $optkontrak;
    break;
	
	case 'loadData':
		$where = "and idwb='".$_SESSION['idwb']."' and kodeproduk='".$kodeproduk."' and intex='1'";
		$str="select *, count(notiket) as tiket from ".$dbname.".trxtimbang where 1=1 ".$where." group by notiket having tiket=1 order by notiket desc";
		$res=fetchdata($str);
		echo "
		<div class=table-scroll style='height:200px'>
			<table class=sortable></center>
				<thead>
				<tr class=rowheader>
					<th align=center><b>No. Tiket</b></th>
					<th align=center><b>No. SPB</b></th>
					<th align=center><b>Produk</b></th>
					<th align=center><b>Waktu Masuk</b></th>
					<th align=center><b>Timbang I</b></th>
					<th align=center><b>No Kendaraan</b></th>
					<th align=center><b>Supir</b></th>
				</tr>
				</thead>
				<tbody>";
		if ($res) {
			foreach ($res as $val) {
				echo"
				<tr class=rowcontent onmouseover=\"this.style.backgroundColor='#00FF00';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" style='cursor:pointer;' title='Click' onclick=\"fillfield('".$val['notiket']."');\">
				<td align=center>".$val['notiket']."</td>
				<td align=center>".$val['nospb']."</td>
				<td align=center>".$namaproduk."</td>
				<td align=center>".tanggalnormald($val['waktumasuk'])."</td>
				<td align=center>".$val['timbang1']."</td>
				<td align=center>".$val['nokendaraan']."</td>
				<td align=center>".$val['supir']."</td>
				</tr>";
			}
		}else{
			echo "<tr class=rowcontent>
			<td colspan=10 align=center>Data kosong</td>
			</tr>";
		}
		echo "
		</tbody>
		</table>
		</div>";
	break;
	
	case'timbang1':
		try{
			$owlPDO->beginTransaction();
			
			$data = array(
				'indc'=>'I',
				'io'=>'1',
				'idwb'=>$_SESSION['idwb'],
				'notiket'=>$param['ticketno'],
				'kodeproduk'=>$kodeproduk,
				'nokontrak'=>'',
				'noso'=>'',
				'sisaqty'=>'',
				'noproductionorder'=>'',
				'transportir'=>$param['transportir'],
				'customer'=>'',
				'supplier'=>'',
				'waktumasuk'=>tanggalsystemn($param['datein']),
				'waktukeluar'=>'',
				'timbang1'=>str_replace(',','',$param['wei1st']),
				'timbang2'=>'',
				'bruto'=>'',
				'kgpotongan'=>'',
				'netto'=>'',
				'satuan'=>'KG',
				'millcode'=>$_SESSION['millcode'],
				'supir'=>$param['supir'],
				'nospb'=>$param['nospb'],
				'nokendaraan'=>$param['nokendaraan'],
				'nosegel'=>'',
				'keterangan'=>$param['keterangan'],
				'deliverynote'=>'',
				'sloc'=>'',
				'batch'=>'',
				'receivedate'=>'',
				'receiveqty'=>'',
				'loses'=>'',
				'gainloses'=>'',
				'jjg'=>$param['jjg'],
				'brondolan'=>$param['brondol'],
				'intex'=>'1',
				'unit'=>$param['unit'],
				'divisi'=>$param['divisi'],
				'user'=>$_SESSION['standard']['username'],
			);
			
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$str = insertQuery($dbname,'trxtimbang',$data,$cols);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			echo $e."##<br>";
		}
	break;
	
	case'timbang2':
		try{
			$owlPDO->beginTransaction();
			
			$data = array(
				'indc'=>'I',
				'io'=>'0',
				'idwb'=>$_SESSION['idwb'],
				'notiket'=>$param['ticketno'],
				'kodeproduk'=>$kodeproduk,
				'nokontrak'=>'',
				'noso'=>'',
				'sisaqty'=>'',
				'noproductionorder'=>'',
				'transportir'=>$param['transportir'],
				'customer'=>'',
				'supplier'=>'',
				'waktumasuk'=>tanggalsystemn($param['datein']),
				'waktukeluar'=>tanggalsystemn($param['dateout']),
				'timbang1'=>str_replace(',','',$param['wei1st']),
				'timbang2'=>str_replace(',','',$param['wei2nd']),
				'bruto'=>str_replace(',','',$param['bruto']),
				'kgpotongan'=>str_replace(',','',$param['kgpotongan']),
				'netto'=>str_replace(',','',$param['netto']),
				'satuan'=>'KG',
				'millcode'=>$_SESSION['millcode'],
				'supir'=>$param['supir'],
				'nospb'=>$param['nospb'],
				'nokendaraan'=>$param['nokendaraan'],
				'nosegel'=>'',
				'keterangan'=>$param['keterangan'],
				'deliverynote'=>'',
				'sloc'=>'',
				'batch'=>'',
				'receivedate'=>'',
				'receiveqty'=>'',
				'loses'=>'',
				'gainloses'=>'',
				'jjg'=>$param['jjg'],
				'brondolan'=>$param['brondol'],
				'intex'=>'1',
				'unit'=>$param['unit'],
				'divisi'=>$param['divisi'],
				'user'=>$_SESSION['standard']['username'],
			);
			
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$str = insertQuery($dbname,'trxtimbang',$data,$cols);
			$owlPDO->exec($str);
			
			## GRADING
			for($i=0;$i<count($param['kriteria']);$i++){
				$cols=array();
				$expkriteria=explode('__',$param['kriteria'][$i]);
				$kode=$expkriteria[0];
				$kriteria=$expkriteria[1];
				$datadt[$i] = array(
					'notransaksi'=>$param['ticketno'],
					'kode'=>$kode,
					'field'=>$kriteria,
					'value'=>$param['nilai'][$i],
					'status'=>'1',
				);
				foreach($datadt[$i] as $key=>$row) {
					$cols[] = $key;
				}
				$strx = insertQuery($dbname,'trxsortasi',$datadt[$i],$cols);
				$owlPDO->exec($strx);
			}
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			exit("error :<br>".$e);
		}
	break;
	
	case'showedit':
		$str="select * from ".$dbname.".trxtimbang where notiket='".$param['ticketno']."' and io='1'";
		$res=fetchdata($str);
		$res[0]['waktumasuk']=tanggalnormald($res[0]['waktumasuk']);
		
		echo json_encode($res);
	break;
}

function generatenotiket(){
    global $dbname;
    ##generate notiket
    $str2="select * from ".$dbname.".mssystem limit 1";
    $res2=fetchdata($str2);
    $idwb=$res2[0]['idwb'];

    $str="select distinct RIGHT(notiket,6) as notiket from ".$dbname.".trxtimbang";
    $res=fetchdata($str);
    if(!$res)
    {
        $no_1=1;
        $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
    }
    else
    {   
        $str2="select RIGHT(notiket,6) as notiket from ".$dbname.".trxtimbang where idwb='".$idwb."' order by notiket desc limit 1";
        $res2=fetchdata($str2);
        if ($res2){
            $ticketno=$res2[0]['notiket'];
            $no_1=intval($ticketno)+1;
            $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
        }
        else
        {
            $no3=1;
            $no=str_pad($no3,6,"0",STR_PAD_LEFT);
        }
    }
    return $idwb."".$no;

}
?>
