<?php
ini_set('display_errors',0);
error_reporting(0);
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
		$str="select waktumasuk, waktukeluar, kodebarang from ".$dbname.".wb where in_out='O' and (waktumasuk LIKE '".$tanggal."%' or waktukeluar = '".$tanggal."%') and kodebarang not in ('".$kodeprodukcpo."','".$kodeprodukpk."')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['waktumasuk']!=''){
				$jlhkendaraan['0']+=1;
			}
			
			if($val['waktukeluar']!='0000-00-00 00:00:00'){
				$jlhkendaraan['1']+=1;
			}
		}
		
		$arrhasil['tiket']=generatenotiket('pengiriman',$param['produk']);
		$arrhasil['masuk']=hidezerodecimal(@$jlhkendaraan['0']);
		$arrhasil['keluar']=hidezerodecimal(@$jlhkendaraan['1']);
		
		echo json_encode($arrhasil);
    break;
	
	case 'getso':
        $optso=$optdivisi="<option value=''>Silahkan pilih</option>";
		$attrdis=true;
		$attrdis2=0;
		$nokontrak="";
		$sisaso=0;

		## GET SO
		$str="select * from ".$dbname.".msso where sostatus='1' and compcode='".$compcode."' and custcode='".$param['customer']."' and kodeproduk='".$param['product']."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so']==$val['noso']){
				$optso.="<option value='".$val['noso']."' selected>".$val['noso']."</option>";				
				$nokontrak=$val['nosoinduk'];
				$sisaso=hidezerodecimal($val['sisaso'],2);
			}else{
				$optso.="<option value='".$val['noso']."'>".$val['noso']."</option>";				
			}
		}
		
		## GET AFDELING
		$descode=substr($param['customer'],-4);
		$optunit=makeOption($dbname,'msunit','descode1,unitcode',"descode1='".$descode."'");
		$unit=$optunit[$descode];		
		if($unit!=''){
			$str="select divcode,divname from ".$dbname.".msdivisi where unitcode='".$unit."' and divstatus='1'";
			$res=fetchdata($str);
			foreach($res as $val){
				if($val['divcode']==$param['divisi']){
					$optdivisi.="<option value='".$val['divcode']."' selected>".$val['divname']."</option>";
					$attrdis2++;
				}else{
					$optdivisi.="<option value='".$val['divcode']."'>".$val['divname']."</option>";
				}
				$attrdis=false;
			}
		}
		
		if($attrdis2 > 0){
			$attrdis=true;			
		}
		
		$arrhasil['listso']=$optso;
		$arrhasil['nokontrak']=$nokontrak;
		$arrhasil['sisaso']=$sisaso;
		$arrhasil['divisi']=$optdivisi;
		$arrhasil['attrdis']=$attrdis;
		
		echo json_encode($arrhasil);
    break;
	
	case 'getkualitas':
        $arrhasil=array();

		## GET KUALITAS
		$str="select * from ".$dbname.".mskualitas where kode='".$param['storage']."' and produk='".$product."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$arrhasil['ffa']=$res[0]['ffa'];
			$arrhasil['moist']=$res[0]['moist'];
			$arrhasil['dirt']=$res[0]['dirt'];
			$arrhasil['dobi']=$res[0]['dobi'];
		}
		
		echo json_encode($arrhasil);
    break;
	
	case 'getsambungso':
        $hasil="";

		## GET KUALITAS
		$str="select * from ".$dbname.".msso where noso='".$param['sambungso']."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			if($res[0]['nosoinduk']!=''){
				$hasil.="No. Kontrak : ".$res[0]['nosoinduk']." | ";
			}
			$hasil.="Sisa SO : ".hidezerodecimal($res[0]['sisaso'],3);
		}
		
		echo $hasil;
    break;
	
	case 'loadData':
		$where = "and netto='0' and tipeunit='' and in_out='O' and kodebarang not in ('".$kodeprodukcpo."','".$kodeprodukpk."')";
		$str="select * from ".$dbname.".wb where 1=1 ".$where." group by notransaksi order by notransaksi asc";
		$res=fetchdata($str);
		echo "
		<div class=table-scroll style='height:200px'>
			<table class=sortable></center>
				<thead>
				<tr class=rowheader>
					<th align=center><b>No. Tiket</b></th>
					<th align=center><b>No Kendaraan</b></th>
					<th align=center><b>No. Kontrak</b></th>
					<th align=center><b>Customer</b></th>
					<th align=center><b>Divisi</b></th>
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
				$optdivisi=makeOption($dbname,'msdivisi','divcode,divname',"divcode='".$val['divcode']."'");
				$namaproduk=$optnmproduk[$val['kodebarang']];
				$namadivisi=$optdivisi[$val['divcode']];
				
				echo"
				<tr class=rowcontent onmouseover=\"this.style.backgroundColor='#00FF00';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" style='cursor:pointer;' title='Click' onclick=\"fillfield('".$val['notransaksi']."');\">
				<td align=center>".$val['notransaksi']."</td>
				<td align=center>".$val['nokendaraan']."</td>
				<td align=center>".$val['kontrakjual']."</td>
				<td align=center>".getNamaCustomer($val['customer'])."</td>
				<td align=center>".$namadivisi."</td>
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
				'notransaksi'=>generatenotiket('pengiriman',$param['product']),
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
				'kodebarang'=>$param['product'],
				'nopo'=>'',
				'multi'=>'',
				'kontrakbeli'=>'',
				'kontrakbeli2'=>'',
				'kontrakjual'=>$param['so'],
				'kontrakjual2'=>'',
				'notekirim'=>$param['keterangan'],
				'supir'=>$param['supir'],
				'nosim'=>$param['nosim'],
				'spb'=>'',
				'qr'=>'',
				'nokendaraan'=>$param['nokendaraan'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'keterangan'=>'',
				'transportir'=>$param['transportir'],
				'supplier'=>'',
				'customer'=>$param['customer'],
				'storage'=>'',
				'unitcode'=>'',
				'divcode'=>$param['divisi'],
				'tipeunit'=>'',
				'pemilik'=>'',
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

			$nettosplit=0;
			$nettosplit2=0;
			if ($param['so']!='') {
				$sisaso=getsisaso($param['so']);
				$netto=str_replace(',','',$param['bruto']);
				if($sisaso < $netto){
					if($param['sambungso']==''){
						throw new PDOException('Gagal, Sisa Kuantitas Sales Order lebih kecil dari Kuantitas Kirim. Silahkan split Sales Order.<br>Kuantitas Sisa SO : '.hidezerodecimal($sisaso).'<br>Kuantitas Kirim : '.hidezerodecimal($netto));
					}
					$sisado2=getsisaso($param['sambungso']);
					$sisanetto=$netto-$sisaso;
					if($sisado2 < $sisanetto){
						throw new PDOException('Gagal, Sisa Kuantitas Split Sales Order lebih kecil dari Kuantitas Kirim.<br>Kuantitas Sisa SO : '.hidezerodecimal($sisaso).'<br>Kuantitas Kirim : '.hidezerodecimal($sisaso).'<br>Kuantitas Sisa Split SO : '.hidezerodecimal($sisado2).'<br>Kuantitas Kirim Split : '.hidezerodecimal($sisanetto));
					}
				}
				
				if($param['sambungso']!=''){
					$nettosplit=$sisaso;
					$nettosplit2=$netto-$sisaso;
					$str="update ".$dbname.".msso set sisaso=(sisaso-'".$sisaso."') where noso='".$param['so']."'";
					$owlPDO->exec($str);
					$str="update ".$dbname.".msso set sisaso=(sisaso-'".$nettosplit2."') where noso='".$param['sambungso']."'";
					$owlPDO->exec($str);
				}else{
					$str="update ".$dbname.".msso set sisaso=(sisaso-'".$netto."') where noso='".$param['so']."'";
					$owlPDO->exec($str);
				}
			}
			
			
			$data = array(
				'notekirim'=>$param['keterangan'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'waktukeluar'=>tanggalsystemn($param['dateout']),
				'beratkeluar'=>str_replace(',','',$param['wei2nd']),
				'netto'=>str_replace(',','',$param['netto']),
				'kontrakjual2'=>$param['sambungso'],
				'nettosplit'=>$nettosplit,
				'nettosplit2'=>$nettosplit2,
				'potongan'=>str_replace(',','',$param['kgpotongan']),
				'krani'=>$_SESSION['standard']['username'],
				'FLAG'=>'0',
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
		
		$optso="<option value=''>Silahkan pilih</option>";
		$strx="select * from ".$dbname.".msso where custcode='".$res[0]['customer']."' and kodeproduk='".$res[0]['kodebarang']."' and noso!='".$res[0]['kontrakjual']."' and sostatus='1' and sisaso > '0'";
		$resx=fetchdata($strx);
		foreach($resx as $valx){
			$optso.="<option value='".$valx['noso']."'>".$valx['noso']."</option>";					
		}
		$res[0]['sambungso']=$optso;
		
		$arrhasil=$res[0];
		
		echo json_encode($arrhasil);
	break;
}

function getsisaso($id){
	global $dbname;
	
	$sisaso=0;
	
	$str="select sisaso from ".$dbname.".msso where noso='".$id."'";
    $res=fetchdata($str);
	$sisaso=$res[0]['sisaso'];
	
	return $sisaso;
}
?>
