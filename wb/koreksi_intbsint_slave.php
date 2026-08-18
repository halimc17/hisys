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
$datenow=date('Y-m-d');
$datetimenow=date('Y-m-d H:i:s');

$str="select compcode,millcode,idwb from ".$dbname.".mssystem limit 1";
$res=fetchdata($str);
$millcode=$res[0]['millcode'];
$compcode=$res[0]['compcode'];
$idwb=$res[0]['idwb'];

switch($method){
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

    case 'getproductionorder':
        $optproductionorder="";

        $str="select tipeunit from ".$dbname.".msunit where unitcode='".$param['unit']."'";
        $res=fetchData($str);
      	$tipeunit=$res[0]['tipeunit'];

      	$tanggaltimbang="";
      	$strx="select left(waktukeluar,10) as waktukeluar from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
        $resx=fetchData($strx);
      	$tanggaltimbang=$resx[0]['waktukeluar'];

		$str="select * from ".$dbname.".msproductionorder where tanggalselesai >= '".$tanggaltimbang."' and status='1' ORDER BY tanggalmulai desc";
		$res=fetchdata($str);

		if ($tipeunit=='INTERNAL') {
			foreach ($res as $val) {
				if($param['productionorder']==$val['no']){
					$optproductionorder.="<option value='".$val['no']."' selected>".$val['no']."</option>";				
				}else{
					$optproductionorder.="<option value='".$val['no']."'>".$val['no']."</option>";				
				}
			}
		}
        
        echo $optproductionorder;
    break;
	
	case'timbang2':
		try{
			$owlPDO->beginTransaction();
			$str="select waktukeluar from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
			$res=fetchdata($str);
			$dateout=tanggalnormald($res[0]['waktukeluar']);

			###cek shift
	        $tglkmren=date('Y-m-d H:i:s', strtotime("-1 day", strtotime(date($dateout))));            
	        $timedatetrans=substr($dateout,11,5);
	        if (strtotime($timedatetrans) >= strtotime('00:00') && strtotime($timedatetrans) < strtotime('07:00') ) {
	            $postingdate= $tglkmren;
	        }else{
	            $postingdate= tanggalsystemn($dateout);
	        }
	        // exit('Error'.$postingdate);
	        ###cek shift

	        $qrcode = str_replace('{OWL}', '', $param['qrcode']);
			if (strlen($qrcode)==7) {
				$qrcode = substr($qrcode, 0,3)."".substr($param['divisi'], 4,2)."".substr($qrcode, 3,4);
			}

			// exit('Error'.$qrcode);

	        $str="select descode1,tipeunit from ".$dbname.".msunit where unitcode='".$param['unit']."'";
			$res=fetchdata($str);
			if(isset($res)){
				$estorigin=$res[0]['descode1'];
				$tipeunit=$res[0]['tipeunit'];
			}

			if ($tipeunit=='PLASMA') {
				##plasma
				if ($param['so']=='') {
					throw new PDOException('Kontrak tidak boleh kosong.');
				}

				$str="select ctrno, vendorcode from ".$dbname.".mscontractpurchase where ctrno='".$param['so']."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$kontrakbeli=$res[0]['ctrno'];
					$supplier=$res[0]['vendorcode'];
				}
			}else{
				##inti
				if ($param['productionorder']=='') {
					throw new PDOException('Production order tidak boleh kosong.');
				}

				$str="select vendorcode from ".$dbname.".msvendor where RIGHT(vendorcode,4) = '".$estorigin."' ";
				$res=fetchData($str);
				if ($res) {
					$supplier=$res[0]['vendorcode'];
				}else{
					throw new PDOException('Kode vendor untuk unit '.$param['unit'].' masih kosong, silahkan sinkronisasi master vendor');
				}
			}

			$where = "notransaksi='".$param['ticketno']."'";

			##cek dulu data nya sudah terupload ke erp belum, jika belum maka flag tetap N & 0
			$str="select FLAG from ".$dbname.".wb where ".$where."";
			$res=fetchdata($str);
			if ($res[0]['FLAG']=='1') {
				$flag_trx='R';
				$flag='0';
			}else{
				$flag_trx='N';
				$flag='0';
			}
			##cek dulu data nya sudah terupload ke erp belum, jika belum maka flag tetap N & 0
			
			$data = array(
				'unitcode'=>$param['unit'],
				'divcode'=>$param['divisi'],
				'tipeunit'=>$tipeunit,
				'estorigin'=>$estorigin,
				'nopo'=>$param['productionorder'],
				'kontrakbeli'=>$param['so'],
				'transportir'=>$param['transportir'],
				'supplier'=>$supplier,
				'nokendaraan'=>str_replace(" ", "", strtoupper($param['nokendaraan'])),
				'supir'=>$param['supir'],
				'qr'=>$qrcode,
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'janjang'=>$param['jjg'],
				'brondolan'=>$param['brondol'],
				'keterangan'=>$param['keterangan'],
				'updatetime'=>$datetimenow,
				'updateuser'=>$_SESSION['standard']['username'],
				'FLAG_TRX'=>$flag_trx,
				'FLAG'=>$flag,
				'postingdate'=>$postingdate,
			);

			$str = updateQuery($dbname,'wb',$data,$where);
			$owlPDO->exec($str);

			#delete dulu grading nya baru insert lagi
			$str = deleteQuery($dbname,'trxsortasi',$where);
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
				if($tipeunit=='INTERNAL'){
					$ttlgrdpersen = str_replace(',','',$param['ttlgrdpersen']);
					$jjg = str_replace(',','',$param['jjg']);
					if($jjg <= 0){
						throw new PDOException("Jumlah janjang inputan harus lebih besar dari 0");
					}
					if($ttlprsn!=100){
						$errmsg="";
						$nomsx=0;
						foreach($listdesc as $key=>$val){
							$nomsx++;
							$errmsg.=$nomsx.". ".$val."<br>";
						}
						// throw new PDOException("Total Nilai persen grading ".$ttlprsn."<br>Total nilai persen grading harus 100 untuk kriteria : <br>".$errmsg);
					}
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
				
				$ttljjg=0;
				$ttlpersen=0;
				$ttlkg=0;
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
								<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='persen' id='".$valx['kode']."__".$valx['persen']."' value='".$sortasi[$valx['kode']][$valx['persen']]."' onblur=hitungkg(this.id) placeholder='0'>
							</td>";
							$ttlpersen+=$sortasi[$valx['kode']][$valx['persen']];
						}
						
						if($valx['kg']!=''){
							$tabindx++;
							$frm.="<td style='text-align:center'>
								<input tabindex='".$tabindx."' class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='kg' id='".$valx['kode']."__".$valx['kg']."' value='".$sortasi[$valx['kode']][$valx['kg']]."' onblur=hitungpr(this.id) placeholder='0'>
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
