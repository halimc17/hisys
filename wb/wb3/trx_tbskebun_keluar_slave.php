<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$optnmproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$kodeproduktbs."'");
$namaproduk=$optnmproduk[$kodeproduktbs];

$str="select compcode,millcode,idwb from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['millcode'];
$compcode=$res[0]['compcode'];
$idwb=$res[0]['idwb'];

switch($method){
	case 'generatenotiket':
		$tanggal=date("Y-m-d");
		$jlhkendaraan=array();
		$str="select waktumasuk, waktukeluar from ".$dbname.".wb where in_out='O' and (waktumasuk LIKE '".$tanggal."%' or waktukeluar = '".$tanggal."%') and kodebarang = '".$kodeproduktbs."' and tipeunit in ('INTERNAL','PLASMA')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['waktumasuk']!=''){
				@$jlhkendaraan['0']+=1;
			}
			
			if($val['waktukeluar']!='0000-00-00 00:00:00'){
				@$jlhkendaraan['1']+=1;
			}
		}
	
		$arrhasil['tiket']=generatenotiket('pengiriman',$kodeproduktbs);
		$arrhasil['masuk']=hidezerodecimal(@$jlhkendaraan['0']);
		$arrhasil['keluar']=hidezerodecimal(@$jlhkendaraan['1']);

		
		echo json_encode($arrhasil);
    break;
	
	case'getdivisi':
		$optdivisi="<option value=''>Silahkan pilih</option>";

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
		$str="select * from ".$dbname.".mscontractpurchase where vendorcode like '%".getPlant($param['unit'])."' and ctrstatus='1'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so']==$val['ctrno']){
				$optkontrak.="<option value='".$val['ctrno']."' selected>".$val['ctrno']."</option>";				
			}else{
				$optkontrak.="<option value='".$val['ctrno']."'>".$val['ctrno']."</option>";				
			}
		}
        
        echo $optkontrak;
    break;
	
	case 'loadData':
		$where = "and netto='0' and kodebarang='".$kodeproduktbs."' and tipeunit in ('INTERNAL','PLASMA') and sumber='KEBUN' and in_out='O'";
		$str="select * from ".$dbname.".wb where 1=1 ".$where." group by notransaksi order by notransaksi asc";
		$res=fetchdata($str);
		echo "
		<div class=table-scroll style='height:200px'>
			<table class=sortable></center>
				<thead>
				<tr class=rowheader>
					<th align=center><b>No. Tiket</b></th>
					<th align=center><b>No Kendaraan</b></th>
					<th align=center><b>Waktu Masuk</b></th>
					<th align=center><b>Unit</b></th>
					<th align=center><b>Divisi</b></th>
					<th align=center><b>Timbang Masuk</b></th>
					<th align=center><b>Supir</b></th>
				</tr>
				</thead>
				<tbody>";
		if(count($res) > 0){
			foreach ($res as $val) {
				echo"
				<tr class=rowcontent onmouseover=\"this.style.backgroundColor='#00FF00';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" style='cursor:pointer;' title='Click' onclick=\"fillfield('".$val['notransaksi']."');\">
				<td align=center>".$val['notransaksi']."</td>
				<td align=center>".$val['nokendaraan']."</td>
				<td align=center>".tanggalnormald($val['waktumasuk'])."</td>
				<td align=center>".getUnit($val['unitcode'])."</td>
				<td align=center>".getDivisi($val['unitcode'],$val['divcode'])."</td>
				<td align=center>".$val['beratmasuk']."</td>
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
			
			if(str_replace(',','',$param['wei1st']) <= 0){
				throw new PDOException('Timbang 1 harus lebih besar dari 0 (nol)');
			}
			
			$estorigin='';
			$storage='';
			$batch='';
			$supplier='';
			$tipeunit='';
			
			$str="select ctrno, vendorcode from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."'";
			$res=fetchdata($str);
			if(count($res) > 0){
				$kontrakbeli=$res[0]['ctrno'];
				$supplier=$res[0]['vendorcode'];
			}
			
			$str="select descode1,tipeunit from ".$dbname.".msunit where unitcode='".$param['unit']."'";
			$res=fetchdata($str);
			if(count($res) > 0){
				$estorigin=$res[0]['descode1'];
				$tipeunit=$res[0]['tipeunit'];
				$storage='CR10';
				$batch='FFB';
			}

			$qrcode = str_replace('{OWL}', '', $param['qrcode']);
			if (strlen($qrcode)==7) {
				$qrcode = substr($qrcode, 0,3)."".substr($qrcode, 4,2)."".substr($qrcode, 3,4);
			}
			
			$data = array(
				'notransaksi'=>generatenotiket('pengiriman',$kodeproduktbs),
				'in_out'=>'O',
				'waktumasuk'=>tanggalsystemn($param['datein']),
				'waktukeluar'=>'',
				'beratmasuk'=>str_replace(',','',$param['wei1st']),
				'beratkeluar'=>'',
				'netto'=>'',
				'nettosplit'=>'',
				'nettosplit2'=>'',
				'potongan'=>'',
				'satuan'=>'KG',
				'millcode'=>$millcode,
				'kodebarang'=>$kodeproduktbs,
				'nopo'=>'',
				'multi'=>'',
				'kontrakbeli'=>$param['so'],
				'kontrakbeli2'=>'',
				'kontrakjual'=>'',
				'kontrakjual2'=>'',
				'notekirim'=>'',
				'supir'=>$param['supir'],
				'nosim'=>'',
				'spb'=>'',
				'qr'=>$qrcode,
				'nokendaraan'=>$param['nokendaraan'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'keterangan'=>$param['keterangan'],
				'transportir'=>$param['transportir'],
				'supplier'=>$supplier,
				'customer'=>$param['tujuan'],
				'storage'=>$storage,
				'unitcode'=>$param['unit'],
				'divcode'=>$param['divisi'],
				'tipeunit'=>$tipeunit,
				'estorigin'=>$estorigin,
				'batch'=>$batch,
				'receivedate'=>'',
				'receiveqty'=>'',
				'loses'=>'',
				'gainloses'=>'',
				'ffa'=>'',
				'moist'=>'',
				'dirt'=>'',
				'dobi'=>'',
				'krani'=>$_SESSION['standard']['username'],
				'sumber'=>'KEBUN',
				'FLAG'=>'0',
			);
			
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$str = insertQuery($dbname,'wb',$data,$cols);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			echo "error" . addslashes($e->getMessage());
		}
	break;
	
	case'timbang2':
		try{
			$owlPDO->beginTransaction();
			
			if(str_replace(',','',$param['wei1st']) <= 0){
				throw new PDOException('Timbang 1 harus lebih besar dari 0 (nol)');
			}
			if(str_replace(',','',$param['wei2nd']) <= 0){
				throw new PDOException('Timbang 2 harus lebih besar dari 0 (nol)');
			}
			if(str_replace(',','',$param['netto']) <= 0){
				throw new PDOException('Netto timbangan harus lebih besar dari 0 (nol)');
			}

			$qrcode = str_replace('{OWL}', '', $param['qrcode']);
			if (strlen($qrcode)==7) {
				$qrcode = substr($qrcode, 0,3)."".substr($qrcode, 4,2)."".substr($qrcode, 3,4);
			}
			
			$data = array(
				'divcode'=>$param['divisi'],
				'nokendaraan'=>$param['nokendaraan'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'janjang'=>$param['jjg'],
				'brondolan'=>$param['brondol'],
				'waktukeluar'=>tanggalsystemn($param['dateout']),
				'beratkeluar'=>str_replace(',','',$param['wei2nd']),
				'netto'=>str_replace(',','',$param['netto']),
				'potongan'=>str_replace(',','',$param['kgpotongan']),
				'qr'=>$qrcode,
				'keterangan'=>$param['keterangan'],
				'krani'=>$_SESSION['standard']['username'],
				'FLAG'=>'0',
				'customer'=>$param['tujuan']
			);
			$where = "notransaksi='".$param['ticketno']."'";
			$str = updateQuery($dbname,'wb',$data,$where);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			exit("Gagal, ".$e->getMessage());
		}
	break;
	
	case'showedit':
		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
		$res=fetchdata($str);
		$res[0]['waktumasuk']=tanggalnormald($res[0]['waktumasuk']);
		$arrhasil=$res[0];
		
		echo json_encode($arrhasil);
	break;
}

/*function generatenotiket(){
    global $dbname;
    ##generate notiket
    $str2="select * from ".$dbname.".mssystem limit 1";
    $res2=fetchdata($str2);
    $idwb=$res2[0]['idwb'];

    $str="select distinct RIGHT(notransaksi,6) as notiket from ".$dbname.".wb";
    $res=fetchdata($str);
    if(!$res)
    {
        $no_1=1;
        $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
    }
    else
    {   
        $str2="select RIGHT(notransaksi,6) as notiket from ".$dbname.".wb where notransaksi like '".$idwb."%' order by notransaksi desc limit 1";
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

}*/
?>
