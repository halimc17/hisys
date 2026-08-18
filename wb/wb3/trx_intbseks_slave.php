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
		$str="select waktumasuk, waktukeluar from ".$dbname.".wb where in_out='I' and (waktumasuk LIKE '".$tanggal."%' or waktukeluar = '".$tanggal."%') and kodebarang = '".$kodeproduktbs."' and tipeunit in ('EKSTERNAL') and sumber='PABRIK'";
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
	
	case 'getkontrak':
        $optkontrak="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		$str="select * from ".$dbname.".mscontractpurchase where vendorcode='".$param['supplier']."' and jenis='prd' and tanggalsampai >= '".$datenow."' and ctrstatus='1' order by tanggalsampai limit 1";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so']==$val['ctrno']){
				$optkontrak.="<option value='".$val['ctrno']."' selected>".$val['ctrno']."</option>";				
			}else{
				$optkontrak.="<option value='".$val['ctrno']."'>".$val['ctrno']."</option>";				
			}
		}


		$str="select * from ".$dbname.".mscontractpurchase where vendorcode='".$param['supplier']."' and jenis='vol' and ctrstatus='1' and sisactr!='0'";
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

    case 'getpotonganwajib':
    	$potonganwajib=0;
    	$str="select potongan from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."'";
		$res=fetchData($str);
		$potonganwajib = $res[0]['potongan'];

		echo $potonganwajib;
	break;

	case 'getsisactr':
    	$potonganwajib=0;
    	$str="select sisactr from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."'";
		$res=fetchData($str);
		$sisactr = $res[0]['sisactr'];

		echo $sisactr;
	break;
	
	case 'loadData':
		$where = "and netto='0' and kodebarang='".$kodeproduktbs."' and tipeunit in ('EKSTERNAL') and sumber='PABRIK'";
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
					<th align=center><b>Kontrak</b></th>
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
				echo"
				<tr class=rowcontent onmouseover=\"this.style.backgroundColor='#00FF00';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" style='cursor:pointer;' title='Click' onclick=\"fillfield('".$val['notransaksi']."','".$val['kontrakbeli']."');\">
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

			if ($param['so']!='') {
				$strx="select * from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."' limit 1";
				$resx=fetchdata($strx);
				if ($resx[0]['jenis']=='vol') {
					if(str_replace(',','',$param['sisaso']) <= 0){
						throw new PDOException('Sisa Kontrak harus lebih besar dari 0 (nol)');
					}
				}
			}
			
			if(str_replace(',','',$param['wei1st']) <= 0){
				throw new PDOException('Timbang 1 harus lebih besar dari 0 (nol)');
			}

			$str="select nokendaraan from ".$dbname.".wb where nokendaraan='".$param['nokendaraan']."' and netto='0'";
			$res=fetchData($str);
			if ($res) {
				throw new PDOException('Kendaraan ini masih ada transaksi belum timbang keluar.');
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
				'qtysegel'=>'',
				'segel'=>'',
				'keterangan'=>$param['keterangan'],
				'transportir'=>$param['transportir'],
				'supplier'=>$param['supplier'],
				'customer'=>'',
				'storage'=>'',
				'unitcode'=>'',
				'divcode'=>'',
				'tipeunit'=>'EKSTERNAL',
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
			echo "Gagal, " . addslashes($e->getMessage());
		}
	break;
	
	case'timbang2':
		try{
			$owlPDO->beginTransaction();

			if ($param['so']!='') {
				$str="select jenis from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."' and jenis='vol'";
				$res=fetchdata($str);
				if ($res) {
					$sisaso=getsisactr($param['so']);
					$netto=str_replace(',','',$param['netto']);
					if($sisaso < $netto){
						if($param['sambungso']==''){
							throw new PDOException('Sisa Kuantitas Kontrak lebih kecil dari Kuantitas Kirim. Silahkan Sambung kontrak.<br>Kuantitas Sisa Kontrak : '.hidezerodecimal($sisaso).'<br>Kuantitas Kirim : '.hidezerodecimal($netto));
						}
						$sisado2=getsisactr($param['sambungso']);
						$sisanetto=$netto-$sisaso;
						if($sisado2 < $sisanetto){
							throw new PDOException('Sisa Kuantitas Split Kontrak lebih kecil dari Kuantitas Kirim.<br>Kuantitas Sisa Kontrak : '.hidezerodecimal($sisaso).'<br>Kuantitas Kirim : '.hidezerodecimal($sisaso).'<br>Kuantitas Sisa Sambung Kontrak : '.hidezerodecimal($sisado2).'<br>Kuantitas Kirim Split : '.hidezerodecimal($sisanetto));
						}
					}

					if ($param['sambungso']!='') {
						$sisanetto=$sisaso-$netto;
						if ($sisanetto > 0) {
							throw new PDOException('Sisa Kuantitas Kontrak awal masih cukup, tidak bisa di lakukan Sambung Kontrak');
						}
					}

					if($param['sambungso']!=''){
						$nettosplit=$sisaso;
						$nettosplit2=$netto-$sisaso;
						$str="update ".$dbname.".mscontractpurchase set sisactr=(sisactr-'".$sisaso."') where ctrno='".$param['so']."'";
						$owlPDO->exec($str);
						$str="update ".$dbname.".mscontractpurchase set sisactr=(sisactr-'".$nettosplit2."') where ctrno='".$param['sambungso']."'";
						$owlPDO->exec($str);
					}else{
						$str="update ".$dbname.".mscontractpurchase set sisactr=(sisactr-'".$netto."') where ctrno='".$param['so']."'";
						$owlPDO->exec($str);
					}
				}
			}


			###cek shift
	        $tglkmren=date('Y-m-d H:i:s', strtotime("-1 day", strtotime(date($param['dateout']))));            
	        $timedatetrans=substr($param['dateout'],11,5);
	        if (strtotime($timedatetrans) >= strtotime('00:00') && strtotime($timedatetrans) < strtotime('07:00') ) {
	            $postingdate= $tglkmren;
	        }else{
	            $postingdate= tanggalsystemn($param['dateout']);
	        }
	        ###cek shift
			
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
				'potonganwajib'=>str_replace(',','',$param['kgpotonganwajib']),
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
			}


			##potongan wajib berdasarkan setup kontrak
			$str="select potongan from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."' and potongan!='0'";
			$res=fetchData($str);
			if ($res) {
				$persenpotonganwajib=abs($res[0]['potongan']);

				$str="select * from ".$dbname.".mssortasi where deskripsi ='WAJIB'";
				$res=fetchData($str);
				foreach ($res as $val) {
					$potwajib[$val['persen']]=$val['kode'];
					$potwajib[$val['kg']]=$val['kode'];

					$valuepotwajib[$val['persen']]=$persenpotonganwajib;
					$valuepotwajib[$val['kg']]=str_replace(',','',$param['kgpotonganwajib']);
				}

				foreach ($potwajib as $field => $kode) {
					$str="insert into ".$dbname.".trxsortasi (`notransaksi`,`kode`,`field`,`value`,`status`) values ('".$param['ticketno']."','".$kode."','".$field."','".$valuepotwajib[$field]."','1')";
					$owlPDO->exec($str);
				}
			}
			##potongan wajib berdasarkan setup kontrak

			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			exit("Gagal, ".$e->getMessage());
		}
	break;
	
	case'showedit':
		$arrhasil=[];
    	$strx="select potongan as potonganwajib from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."'";
		$resx=fetchData($strx);
		if ($resx) {
			$arrhasil=$resx[0];
		}

		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
		$res=fetchdata($str);
		$res[0]['waktumasuk']=tanggalnormald($res[0]['waktumasuk']);

		$optso="<option value=''>Silahkan pilih</option>";
		$strx="select * from ".$dbname.".mscontractpurchase where vendorcode='".$res[0]['supplier']."' and kodeproduk='".$kodeproduktbs."' and ctrno!='".$res[0]['kontrakbeli']."' and ctrstatus='1' and sisactr > '0'";
		$resx=fetchdata($strx);
		foreach($resx as $valx){
			$optso.="<option value='".$valx['ctrno']."'>".$valx['ctrno']."</option>";					
		}
		$res[0]['sambungso']=$optso;
		
		$arrhasil+=$res[0];
		echo json_encode($arrhasil);
	break;
}

function getsisactr($id){
	global $dbname;
	
	$sisactr=0;
	
	$str="select sisactr from ".$dbname.".mscontractpurchase where ctrno='".$id."'";
    $res=fetchdata($str);
	$sisactr=$res[0]['sisactr'];
	
	return $sisactr;
}

?>
