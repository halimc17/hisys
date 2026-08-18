<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$optnmproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$kodeproduktbs."'");
$namaproduk=$optnmproduk[$kodeproduktbs];
$datenow=date('Y-m-d');
$datetimenow=date('Y-m-d H:i:s');

$str="select compcode,millcode,idwb from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['millcode'];
$compcode=$res[0]['compcode'];
$idwb=$res[0]['idwb'];

switch($method){
	case 'generatenotiket':
		$tanggal=date("Y-m-d");
		$jlhkendaraan=array();
		$str="select waktumasuk, waktukeluar from ".$dbname.".wb where in_out='I' and (waktumasuk LIKE '".$tanggal."%' or waktukeluar = '".$tanggal."%') and kodebarang = '".$kodeproduktbs."' and tipeunit in ('INTERNAL','PLASMA') and sumber='PABRIK'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['waktumasuk']!=''){
				@$jlhkendaraan['0']+=1;
			}
			
			if($val['waktukeluar']!='0000-00-00 00:00:00'){
				@$jlhkendaraan['1']+=1;
			}
		}
	
		$arrhasil['tiket']=generatenotiket('penerimaan',$kodeproduktbs);
		$arrhasil['masuk']=hidezerodecimal(@$jlhkendaraan['0']);
		$arrhasil['keluar']=hidezerodecimal(@$jlhkendaraan['1']);

		
		echo json_encode($arrhasil);
    break;

    case 'getkendaraan':
        $optkendaraan="<option value=''>Silahkan pilih</option>";

		$str="select vhccode from ".$dbname.".msvhc where vendorcode='".$param['transportir']."' and vhcstatus='1'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['nokendaraan']==$val['vhccode']){
				$optkendaraan.="<option value='".$val['vhccode']."' selected>".$val['vhccode']."</option>";				
			}else{
				$optkendaraan.="<option value='".$val['vhccode']."'>".$val['vhccode']."</option>";				
			}
		}
		
		echo $optkendaraan;
    break;
	
	case'getdivisi':
		$optdivisi="";

		$str="select tipeunit from ".$dbname.".msunit where unitcode='".$param['unit']."'";
        $res=fetchData($str);
      	$tipeunit=$res[0]['tipeunit'];

		## GET KONTRAK
		$str="select divcode,divname from ".$dbname.".msdivisi where unitcode='".$param['unit']."' and divstatus='1'";
		$res=fetchdata($str);
		if(count($res) > 0){
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

	
	case 'loadData':
		$where = "and netto='0' and kodebarang='".$kodeproduktbs."' and tipeunit in ('INTERNAL','PLASMA') and sumber='PABRIK'";
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
					<th align=center><b>Unit</b></th>
					<th align=center><b>Divisi</b></th>
					<th align=center><b>Transportir</b></th>
					<th align=center><b>Produk</b></th>
					<th align=center><b>Waktu Masuk</b></th>
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
				<td align=center>".$val['qr']."</td>
				<td align=center>".getUnit($val['unitcode'])."</td>
				<td align=center>".getDivisi($val['unitcode'],$val['divcode'])."</td>
				<td align=center>".getNamaSupplier($val['transportir'])."</td>
				<td align=center>".$namaproduk."</td>
				<td align=center>".tanggalnormald($val['waktumasuk'])."</td>
				<td align=center>".$val['beratmasuk']."</td>
				<td align=center>".$val['supir']."</td>
				</tr>";
			}
		}else{
			echo "<tr class=rowcontent>
			<td colspan=9 align=center>Data kosong</td>
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

			$str="select tipeunit from ".$dbname.".msunit where unitcode='".$param['unit']."'";
			$res=fetchdata($str);
			if(count($res) > 0){
				$tipeunit=$res[0]['tipeunit'];
			}

			$strx="select * from ".$dbname.".wb where qr = '".$param['qrcode']."'";
			$resx=fetchdata($strx);
			if ($resx) {
				throw new Exception("No SPB sudah pernah di input, mohon dicek kembali !");
			}

			
			$data = array(
				'notransaksi'=>generatenotiket('penerimaan',$kodeproduktbs),
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
				'kodebarang'=>$kodeproduktbs,
				'nopo'=>'',
				'multi'=>'',
				'kontrakbeli'=>'',
				'kontrakbeli2'=>'',
				'kontrakjual'=>'',
				'kontrakjual2'=>'',
				'notekirim'=>'',
				'supir'=>$param['supir'],
				'kernet1'=>$param['kernet1'],
				'kernet2'=>$param['kernet2'],
				'nosim'=>'',
				'spb'=>'',
				'qr'=>$param['qrcode'],
				'nokendaraan'=>str_replace(" ", "", strtoupper($param['nokendaraan'])),
				'qtysegel'=>'',
				'segel'=>'',
				'janjang'=>$param['jjg'],
				'brondolan'=>$param['brondol'],
				'keterangan'=>$param['keterangan'],
				'transportir'=>$param['transportir'],
				'supplier'=>'',
				'customer'=>'',
				'storage'=>'',
				'unitcode'=>$param['unit'],
				'divcode'=>$param['divisi'],
				'tipeunit'=>$tipeunit,
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
			echo "Warning, " . addslashes($e->getMessage());
		}
	break;
	
	case'timbang2':
		try{
			$owlPDO->beginTransaction();

	        $postingdate= tanggalsystemn($param['datein']);
			
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
				'janjang'=>$param['jjg'],
				'brondolan'=>$param['brondol'],
				'waktukeluar'=>tanggalsystemn($param['dateout']),
				'beratkeluar'=>str_replace(',','',$param['wei2nd']),
				'netto'=>str_replace(',','',$param['netto']),
				'potongan'=>str_replace(',','',$param['kgpotongan']),
				'qr'=>$param['qrcode'],
				'keterangan'=>$param['keterangan'],
				'krani'=>$_SESSION['standard']['username'],
				'FLAG'=>'0',
				'postingdate'=>$postingdate,
			);
			$where = "notransaksi='".$param['ticketno']."'";
			$str = updateQuery($dbname,'wb',$data,$where);
			$owlPDO->exec($str);
			
			if(isset($param['kriteria'])){
				## GET TIPE SUMMARY
				$ttlprsn=0;
				$str="select persen, deskripsi from ".$dbname.".msgrading where tipe='1' and status='1'";
				$res=fetchdata($str);
				foreach($res as $val){
					$listpersen[]=$val['persen'];					
					$listdesc[]=$val['deskripsi'];					
				}
				
				for($i=0;$i<count($param['kriteria']);$i++){
					$cols=array();
					$expkriteria=explode('__',$param['kriteria'][$i]);
					$kode=$expkriteria[0];
					$kriteria=$expkriteria[1];
					
					if(in_array($kriteria, $listpersen)){
						$ttlprsn+=str_replace(',','',$param['nilai'][$i]);
					}
					
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
			}
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
