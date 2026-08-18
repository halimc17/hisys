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
$datetimenow=date('Y-m-d H:i:s');

switch($method){	
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
				'netto'=>str_replace(',','',$param['netto']),
				'potongan'=>str_replace(',','',$param['kgpotongan']),
				'potonganwajib'=>str_replace(',','',$param['kgpotonganwajib']),
				'updatetime'=>$datetimenow,
				'updateuser'=>$_SESSION['standard']['username'],
				'FLAG_TRX'=>$flag_trx,
				'FLAG'=>$flag,
				'postingdate'=>$postingdate,
			);
			$str = updateQuery($dbname,'wb',$data,$where);
			$owlPDO->exec($str);
			
			#delete dulu sortasi nya baru insert lagi
			$str = deleteQuery($dbname,'trxsortasi',$where);
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

		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
		$res=fetchdata($str);
    	
    	$strx="select potongan as persenpotonganwajib from ".$dbname.".mscontractpurchase where ctrno='".$res[0]['kontrakbeli']."'";
		$resx=fetchData($strx);
		if ($resx) {
			$arrhasil=$resx[0];
		}

		$res[0]['waktumasuk']=tanggalnormald($res[0]['waktumasuk']);
		$arrhasil+=$res[0];
		
		echo json_encode($arrhasil);
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
