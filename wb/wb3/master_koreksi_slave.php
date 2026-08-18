<?php
ini_set('display_errors',0);
error_reporting(0);
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

$datenow=date('Y-m-d');

switch($method){
	
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

    case 'getkontrak1':
        $optkontrak="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		$str="select * from ".$dbname.".mscontractpurchase where vendorcode='".$param['supplier1']."' and ctrstatus='1'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so1']==$val['ctrno']){
				$optkontrak.="<option value='".$val['ctrno']."' selected>".$val['ctrno']."</option>";				
			}else{
				$optkontrak.="<option value='".$val['ctrno']."'>".$val['ctrno']."</option>";				
			}
		}
        
        echo $optkontrak;
    break;

    case 'getproductionorder':
        $optproductionorder="<option value=''>Silahkan pilih</option>";

		$str="select * from ".$dbname.".msproductionorder where tanggalselesai >= '".$datenow."' and status='1' ORDER BY tanggalmulai desc limit 1";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['productionorder']==$val['no']){
				$optproductionorder.="<option value='".$val['no']."' selected>".$val['no']."</option>";				
			}else{
				$optproductionorder.="<option value='".$val['no']."'>".$val['no']."</option>";				
			}
		}
        
        echo $optproductionorder;
    break;
	
	case'timbangInternal':
		try{
			$owlPDO->beginTransaction();
			
			if(str_replace(',','',$param['netto']) <= 0){
				throw new PDOException('Netto timbangan harus lebih besar dari 0 (nol)');
			}
			
			$data = array(
				'unitcode'=>$param['unit'],
				'divcode'=>$param['divisi'],
				'nopo'=>$param['productionorder'],
				'kontrakbeli'=>$param['so'],
				'transportir'=>$param['transportir'],
				'nokendaraan'=>$param['nokendaraan'],
				'supir'=>$param['supir'],
				'qr'=>$param['qrcode'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'keterangan'=>$param['keterangan'],
				'krani'=>$_SESSION['standard']['username'],
				'FLAG_TRX'=>'R',
				'FLAG'=>'0',
			);
			$where = "notransaksi='".$param['ticketno']."'";
			$str = updateQuery($dbname,'wb',$data,$where);
			$owlPDO->exec($str);
			
			if(isset($param['kriteria'])){
				$strx = deleteQuery($dbname,'trxsortasi',"notransaksi='".$param['ticketno']."'");
				$owlPDO->exec($strx);
				
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
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			exit("Gagal, ".$e->getMessage());
		}
	break;

	case'timbangEksternal':
		try{
			$owlPDO->beginTransaction();
			
			$data = array(
				'qr'=>$param['qrcode'],
				'supplier'=>$param['supplier'],
				'kontrakbeli'=>$param['so'],
				'transportir'=>$param['transportir'],
				'nokendaraan'=>$param['nokendaraan'],
				'supir'=>$param['supir'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'janjang'=>$param['jjg'],
				'brondolan'=>$param['brondol'],
				'keterangan'=>$param['keterangan'],
				'krani'=>$_SESSION['standard']['username'],
				'FLAG_TRX'=>'R',
				'FLAG'=>'0',
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
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			exit("Gagal, ".$e->getMessage());
		}
	break;

	case 'timbangCpopk':
		try{
			$owlPDO->beginTransaction();
			
			$data = array(
				'notekirim'=>$param['keterangan'],
				'keterangan'=>$param['keterangan'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'storage'=>$param['storage'],
				'waktumasuk'=>tanggalsystemn($param['dateout']),
				'waktukeluar'=>tanggalsystemn($param['dateout']),
				'kontrakjual2'=>$param['sambungso'],
				'nettosplit'=>$nettosplit,
				'nettosplit2'=>$nettosplit2,
				'potongan'=>str_replace(',','',$param['kgpotongan']),
				'ffa'=>str_replace(',','',$param['ffa']),
				'moist'=>str_replace(',','',$param['moist']),
				'dirt'=>str_replace(',','',$param['dirt']),
				'dobi'=>str_replace(',','',$param['dobi']),
				'krani'=>$_SESSION['standard']['username'],
				'tiketref'=>$param['tiketref'],
				'wbcond'=>$param['wbcond'],
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
		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."' and tipeunit='INTERNAL'";
		$res=fetchdata($str);

		if (!$res) {
			exit('Warning, No tiket tidak ada.');
		}
		
		$arrhasil=$res[0];
		$arrhasil['bruto']=($res[0]['netto']+$res[0]['potongan']);
		
		echo json_encode($arrhasil);
	break;

	case'showedit1':
		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno1']."' and tipeunit='EKSTERNAL'";
		$res=fetchdata($str);

		if (!$res) {
			exit('Warning, No tiket tidak ada.');
		}
		
		$arrhasil=$res[0];
		$arrhasil['bruto']=($res[0]['netto']+$res[0]['potongan']);
		
		echo json_encode($arrhasil);
	break;

	case'showedit2':
		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno2']."'";
		$res=fetchdata($str);
		
		if (!$res) {
			exit('Warning, No tiket tidak ada.');
		}

		$res[0]['waktumasuk']=tanggalnormald($res[0]['waktumasuk']);
		$res[0]['waktukeluar']=tanggalnormald($res[0]['waktukeluar']);
		$res[0]['netto']=hidezerodecimal($res[0]['netto']);
		
		if($res[0]['kodebarang']==$kodeprodukcpo){
			$res[0]['produk']='1';
		}else{
			$res[0]['produk']='2';
		}
		$optstorage="<option value=''>Silahkan pilih</option>";
		$strx="select * from ".$dbname.".mskualitas where produk='".$res[0]['kodebarang']."' and status='1'";
		$resx=fetchdata($strx);
		foreach($resx as $val){
			$optstorage.="<option value='".$val['kode']."'>Storage ".$val['kode']."</option>";
		}
		$res[0]['storage']=$optstorage;
		
		
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

	case 'getso':
		if (isset($param['product'])) {
			if($param['product']=='1'){
				$product=$kodeprodukcpo;
			}else{
				$product=$kodeprodukpk;
			}
		}

        $optso="<option value=''>Silahkan pilih</option>";
		$nokontrak="";
		$sisaso=0;

		## GET SO
		$str="select * from ".$dbname.".msso where sostatus='1' and compcode='".$compcode."' and custcode='".$param['customer']."' and kodeproduk='".$product."'";
		// exit('Error');
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
		
		$arrhasil['listso']=$optso;
		$arrhasil['nokontrak']=$nokontrak;
		$arrhasil['sisaso']=$sisaso;
		
		echo json_encode($arrhasil);
    break;

	case 'showgrading':
		$tabindx=14;
		
		$str="select * from ".$dbname.".trxsortasi where notransaksi='".$param['ticketno']."'";
		$res=fetchData($str);
		foreach ($res as $val){
			$grading[$val['kode']][$val['field']]=$val['value'];
		}

		$str="select * from ".$dbname.".msgrading where status='1'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$frm="<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
				<tr>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Jjg</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
				</tr>";
						
				foreach ($res as $valx) {
					if($valx['jjg']!='' and $valx['persen']!='' and $valx['kg']!=''){
						$frm.="<tr>
							<td><label class=label>".$valx['deskripsi']."</label></td>";
						if($valx['jjg']!=''){
							$tabindx++;
							$frm.="<td style='text-align:center'>
								<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='jjg' id='".$valx['kode']."__".$valx['jjg']."' value='".$grading[$valx['kode']][$valx['jjg']]."' onblur=hitungpr(this.id) placeholder='0'>
							</td>";
							$ttljjg+=$grading[$valx['kode']][$valx['jjg']];
						}
						
						if($valx['persen']!=''){
							$tabindx++;
							$frm.="<td style='text-align:center'>
								<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='persen' id='".$valx['kode']."__".$valx['persen']."' umberKey(event);' name='jjg' id='".$valx['kode']."__".$valx['jjg']."' value='".$grading[$valx['kode']][$valx['persen']]."' onblur=hitungkg(this.id) placeholder='0'>
							</td>";
							$ttlpersen+=$grading[$valx['kode']][$valx['persen']];
						}
						
						if($valx['kg']!=''){
							$tabindx++;
							$frm.="<td style='text-align:center'>
								<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='kg' id='".$valx['kode']."__".$valx['kg']."' umberKey(event);' name='jjg' id='".$valx['kode']."__".$valx['jjg']."' value='".$grading[$valx['kode']][$valx['kg']]."' onblur=hitungpr(this.id) placeholder='0'>
							</td>";
							$ttlkg+=$grading[$valx['kode']][$valx['kg']];
						}
						$frm.="</tr>";
					}
				}
				
			$frm.="<tr>
					<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>T O T A L</td>
					<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>
						<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlgrdjjg' value='".$ttljjg."' placeholder='0' disabled>
					</td>
					<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>
						<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlgrdpersen' value='".$ttlpersen."' placeholder='0' disabled>
					</td>
					<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;'>
						<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlgrdkg' value='".$ttlkg."' placeholder='0' disabled>
					</td>
				</tr>";
			$frm.="</table>";
		}else{
			$frm.="<label style='font-size:20px;font-weight:bold;color:red'>Master data Grading belum ada!!</label>";
		}

		echo $frm;

	break;

	case 'showsortasi':
		$tabindx=14;

		$str="select * from ".$dbname.".trxsortasi where notransaksi='".$param['ticketno']."'";
		$res=fetchData($str);
		foreach ($res as $val){
			$sortasi[$val['kode']][$val['field']]=$val['value'];
		}

		$str="select * from ".$dbname.".mssortasi where status='1'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$frm="<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
				<tr>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
				</tr>";
						
				foreach ($res as $valx) {
					if($valx['persen']!='' and $valx['kg']!=''){
						$frm.="<tr>
							<td><label class=label>".$valx['deskripsi']."</label></td>";
						if($valx['persen']!=''){
							$tabindx++;
							$frm.="<td style='text-align:center'>
								<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='persen' id='".$valx['kode']."__".$valx['persen']."' value='".$sortasi[$valx['kode']][$valx['persen']]."' onblur=hitungkgEksternal(this.id) placeholder='0'>
							</td>";
							$ttlpersen+=$sortasi[$valx['kode']][$valx['persen']];
						}
						
						if($valx['kg']!=''){
							$tabindx++;
							$frm.="<td style='text-align:center'>
								<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='kg' id='".$valx['kode']."__".$valx['kg']."' value='".$sortasi[$valx['kode']][$valx['kg']]."' onblur=hitungprEksternal(this.id) placeholder='0'>
							</td>";
							$ttlkg+=$sortasi[$valx['kode']][$valx['kg']];
						}
						$frm.="</tr>";
					}
				}
			$frm.="<tr>
					<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>T O T A L</td>
					<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>
						<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlsorpersen' value='".$ttlpersen."' placeholder='0' disabled>
					</td>
					<td style='text-align:center;font-weight:bold;border-top:1px solid #FFFFFF;'>
						<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' id='ttlsorkg' value='".$ttlkg."' placeholder='0' disabled>
					</td>
				</tr>";
			$frm.="</table>";
		}else{
			$frm.="<label style='font-size:20px;font-weight:bold;color:red'>Master data Sortasi belum ada!!</label>";
		}
		echo $frm;
	break;
}
?>
