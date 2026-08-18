<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$str="select compcode,millcode,idwb from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['millcode'];
$compcode=$res[0]['compcode'];
$idwb=$res[0]['idwb'];

switch($method){
	case 'generatenotiket':
		$tanggal=date("Y-m-d");
        $jlhkendaraan=array();
		$str="select waktumasuk, waktukeluar from ".$dbname.".wb where in_out='I' and (waktumasuk LIKE '".$tanggal."%' or waktukeluar = '".$tanggal."%') and kodebarang != '".$kodeproduktbs."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['waktumasuk']!=''){
				@$jlhkendaraan['0']+=1;
			}
			
			if($val['waktukeluar']!='0000-00-00 00:00:00'){
				@$jlhkendaraan['1']+=1;
			}
		}
		
		$arrhasil['tiket']=generatenotiket('penerimaan',$param['produk']);
		$arrhasil['masuk']=hidezerodecimal(@$jlhkendaraan['0']);
		$arrhasil['keluar']=hidezerodecimal(@$jlhkendaraan['1']);
		
		echo json_encode($arrhasil);
    break;
	
	case 'getkontrak':
        $optkontrak="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		$str="select * from ".$dbname.".mscontractpurchase where vendorcode='".$param['supplier']."' and ctrstatus='1'";
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
		$where = "and netto='0' and tipeunit='' and in_out='I' and kodebarang != '".$kodeproduktbs."'";
		$str="select * from ".$dbname.".wb where 1=1 ".$where." group by notransaksi order by notransaksi asc";
		$res=fetchdata($str);
		echo "
		<div class=table-scroll style='height:200px'>
			<table class=sortable></center>
				<thead>
				<tr class=rowheader>
					<th align=center><b>No. Tiket</b></th>
					<th align=center><b>No Kendaraan</b></th>
					<th align=center><b>QR Code / SPB</b></th>
					<th align=center><b>PO / STO / Kontrak</b></th>
					<th align=center><b>Supplier</b></th>
					<th align=center><b>Produk</b></th>
					<th align=center><b>Waktu Masuk</b></th>
					<th align=center><b>Timbang Masuk</b></th>
					<th align=center><b>Supir</b></th>
				</tr>
				</thead>
				<tbody>";
		if(count($res) > 0){
			foreach ($res as $val) {
				$optnmproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$val['kodebarang']."'");
				$namaproduk=$optnmproduk[$val['kodebarang']];
				
				echo"
				<tr class=rowcontent onmouseover=\"this.style.backgroundColor='#00FF00';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" style='cursor:pointer;' title='Click' onclick=\"fillfield('".$val['notransaksi']."');\">
				<td align=center>".$val['notransaksi']."</td>
				<td align=center>".$val['nokendaraan']."</td>
				<td align=center>".$val['qr']."</td>
				<td align=center>".$val['kontrakbeli']."</td>
				<td align=center>".getNamaSupplier($val['supplier'])."</td>
				<td align=center>".$namaproduk."</td>
				<td align=center>".tanggalnormald($val['waktumasuk'])."</td>
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

			$str="select nokendaraan from ".$dbname.".wb where nokendaraan='".$param['nokendaraan']."' and netto='0'";
			$res=fetchData($str);
			if ($res) {
				throw new PDOException('Kendaraan ini masih ada transaksi belum timbang keluar.');
			}

			$str="select millcode from ".$dbname.".mssystem where millcode like '%m'";
			$res=fetchData($str);
			if ($res) {
				$sumber='PABRIK';
			}else{
				$sumber='KEBUN';
			}
			
			$data = array(
				'notransaksi'=>generatenotiket('penerimaan',$param['produk']),
				'in_out'=>'I',
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
				'kodebarang'=>$param['produk'],
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
				'qr'=>$param['qrcode'],
				'nokendaraan'=>$param['nokendaraan'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'keterangan'=>$param['keterangan'],
				'transportir'=>$param['transportir'],
				'supplier'=>$param['supplier'],
				'customer'=>'',
				'storage'=>'',
				'unitcode'=>'',
				'divcode'=>'',
				'tipeunit'=>'',
				'pemilik'=>$param['pemilik'],
				'estorigin'=>'',
				'batch'=>'',
				'receivedate'=>'',
				'receiveqty'=>'',
				'loses'=>'',
				'gainloses'=>'',
				'ffa'=>'',
				'moist'=>'',
				'dirt'=>'',
				'dobi'=>'',
				'krani'=>$_SESSION['standard']['username'],
				'sumber'=>$sumber,
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
			
			$data = array(
				'waktukeluar'=>tanggalsystemn($param['dateout']),
				'beratkeluar'=>str_replace(',','',$param['wei2nd']),
				'netto'=>str_replace(',','',$param['netto']),
				'potongan'=>str_replace(',','',$param['kgpotongan']),
				'qr'=>$param['qrcode'],
				'keterangan'=>$param['keterangan'],
				'krani'=>$_SESSION['standard']['username'],
				'FLAG'=>'1',
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

?>
