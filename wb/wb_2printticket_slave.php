<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}


$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case 'generatenotiket':
        echo generatenotiket();
    break;
	
	case'getkontrakbeli':
		$optso="<option value=''>Silahkan pilih</option>";
		
		$str="select * from ".$dbname.".pmn_kontrakbeli where koderekanan='".$param['supplier']."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$param['supplier']."'");
			if($param['so']==$val['nokontrak']){
				$optso.="<option value='".$val['nokontrak']."' selected>".$val['nokontrak']." - ".$optsup[$param['supplier']]."</option>";					
			}else{
				$optso.="<option value='".$val['nokontrak']."'>".$val['nokontrak']." - ".$optsup[$param['supplier']]."</option>";					
			}
		}
		
		$str="select * from ".$dbname.".log_poht";
		$res=fetchdata($str);
		foreach($res as $val){
			if($param['so']==$val['nopo']){
				$optso.="<option value='".$val['nopo']."' selected>".$val['nopo']."</option>";					
			}else{
				$optso.="<option value='".$val['nopo']."'>".$val['nopo']."</option>";					
			}
		}
		
		echo $optso;
	break;
	
	case'getkontrakjual':
		$optso="<option value=''>Silahkan pilih</option>";
		
		$str="select * from ".$dbname.".pmn_kontrakjual where koderekanan='".$param['customer']."' and kodebarang='".$param['produk']."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($param['so']==$val['nokontrak']){
				$optso.="<option value='".$val['nokontrak']."' selected>".$val['nokontrak']."</option>";					
			}else{
				$optso.="<option value='".$val['nokontrak']."'>".$val['nokontrak']."</option>";					
			}
		}
		
		echo $optso;
	break;
	
	case'getsambungso':
		$optso="<option value=''>Silahkan pilih</option>";
		$optinduk=makeOption($dbname,'pmn_kontrakjual','nokontrak,nokontrakinduk',"nokontrak='".$param['so']."'");
		$kontrakinduk=$optinduk[$param['so']];
		
		$str="select * from ".$dbname.".pmn_kontrakjual where koderekanan='".$param['customer']."' and kodebarang='".$param['produk']."' and nokontrak!='".$param['so']."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($param['sambungso']==$val['nokontrak']){
				$optso.="<option value='".$val['nokontrak']."' selected>".$val['nokontrak']."</option>";					
			}else{
				$optso.="<option value='".$val['nokontrak']."'>".$val['nokontrak']."</option>";					
			}
		}
		
		echo $optso;
	break;
	
	case 'loadData':
		$where = "and netto='0'";
		$str="select * from ".$dbname.".wb where 1=1 ".$where." group by notransaksi order by notransaksi asc";
		$res=fetchdata($str);
		echo "
		<div class=table-scroll style='height:200px'>
			<table class=sortable></center>
				<thead>
				<tr class=rowheader>
					<th align=center><b>No. Tiket</b></th>
					<th align=center><b>No Kendaraan</b></th>
					<th align=center><b>No SPB / PO / SO</b></th>
					<th align=center><b>Customer</b></th>
					<th align=center><b>Supplier</b></th>
					<th align=center><b>Produk</b></th>
					<th align=center><b>Waktu Masuk</b></th>
					<th align=center><b>Timbang Masuk</b></th>
					<th align=center><b>Supir</b></th>
				</tr>
				</thead>
				<tbody>";
		if ($res) {
			foreach ($res as $val) {
				$so="";
				$so=$val['nopo'];
				if($so==''){
					$so=$val['kontrakbeli'];
				}
				if($so==''){
					$so=$val['kontrakjual'];
				}
				if($so==''){
					$so=$val['spb'];
				}
				$optcustomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$val['customer']."'");
				$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['supplier']."'");
				$optproduk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
				echo"
				<tr class=rowcontent onmouseover=\"this.style.backgroundColor='#00FF00';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" style='cursor:pointer;' title='Click' onclick=\"fillfield('".$val['notransaksi']."');\">
				<td align=center>".$val['notransaksi']."</td>
				<td align=center>".$val['nokendaraan']."</td>
				<td align=center>".$so."</td>
				<td align=center>".$optcustomer[$val['customer']]."</td>
				<td align=center>".$optsupplier[$val['supplier']]."</td>
				<td align=center>".$optproduk[$val['kodebarang']]."</td>
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
	
	case'showedit':
		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
		$res=fetchdata($str);
		$res[0]['waktumasuk']=tanggalnormald($res[0]['waktumasuk']);
		
		echo json_encode($res);
	break;
	
	case'timbang1':
		try{
			$owlPDO->beginTransaction();
			
			if(str_replace(',','',$param['wei1st']) <= 0){
				throw new PDOException('Timbang 1 harus lebih besar dari 0 (nol)');
			}
			$nopo="";
			$kontrakbeli="";
			$kontrakjual="";
			$notekirim="";
			$keterangan="";
			$spb="";
			$estorigin="";
			$storage="";
			$batch="";
			if($param['tipe']=='I'){
				$str="select nopo from ".$dbname.".log_poht where nopo='".$param['so']."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$nopo=$res[0]['nopo'];
				}
				
				$str="select nokontrak from ".$dbname.".pmn_kontrakbeli where nokontrak='".$param['so']."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$kontrakbeli=$res[0]['nokontrak'];
				}
				$keterangan=$param['keterangan'];
				$supplier=substr($param['supplier'],-4);
				$str="select descode1 from ".$dbname.".organisasi where descode1='".$supplier."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$estorigin=$supplier;
					$storage='CR10';
					$batch='FFB';
				}
			}else{
				$str="select nokontrak from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['so']."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$kontrakjual=$res[0]['nokontrak'];
				}
				$notekirim=$param['keterangan'];
			}
			
			if($nopo=='' and $kontrakjual=='' and $kontrakbeli==''){
				$spb=$param['so'];
			}
			
			$data = array(
				'notransaksi'=>$param['ticketno'],
				'inout'=>$param['tipe'],
				'waktumasuk'=>tanggalsystemn($param['datein']),
				'waktukeluar'=>'',
				'beratmasuk'=>str_replace(',','',$param['wei1st']),
				'beratkeluar'=>'',
				'netto'=>'',
				'satuan'=>'KG',
				'millcode'=>'PABM',
				'kodebarang'=>$param['produk'],
				'nopo'=>$nopo,
				'multi'=>'',
				'kontrakbeli'=>$kontrakbeli,
				'kontrakjual'=>$kontrakjual,
				'kontrakjual2'=>$param['sambungso'],
				'notekirim'=>$notekirim,
				'supir'=>$param['supir'],
				'spb'=>$spb,
				'qr'=>$param['qrcode'],
				'nokendaraan'=>$param['nokendaraan'],
				'segel'=>$param['segel'],
				'keterangan'=>$keterangan,
				'transportir'=>$param['transportir'],
				'supplier'=>$param['supplier'],
				'customer'=>$param['customer'],
				'estorigin'=>$estorigin,
				'storage'=>$storage,
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
				'FLAG'=>'1',
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
			if(str_replace(',','',$param['netto']) <= 0){
				throw new PDOException('Netto timbangan harus lebih besar dari 0 (nol)');
			}
			$nopo="";
			$kontrakbeli="";
			$kontrakjual="";
			$notekirim="";
			$keterangan="";
			$spb="";
			$sisado=0;
			$storage="";
			$ffa="";
			$moist="";
			$dirt="";
			$dobi="";
			if($param['tipe']=='I'){
				$str="select nopo from ".$dbname.".log_poht where nopo='".$param['so']."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$nopo=$res[0]['nopo'];
				}
				
				$str="select nokontrak from ".$dbname.".pmn_kontrakbeli where nokontrak='".$param['so']."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$kontrakbeli=$res[0]['nokontrak'];
				}
				$keterangan=$param['keterangan'];
				
				$supplier=substr($param['supplier'],-4);
				$str="select descode1 from ".$dbname.".organisasi where descode1='".$supplier."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$estorigin=$supplier;
					$storage='CR10';
					$batch='FFB';
				}
			}else{
				$str="select nokontrak from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['so']."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$kontrakjual=$res[0]['nokontrak'];
				}
				$notekirim=$param['keterangan'];
				
				$sisado=getsisaso($param['so']);
				$netto=str_replace(',','',$param['bruto']);
				
				if($sisado < $netto){
					if($param['sambungso']==''){
						throw new PDOException('Gagal, Sisa Kuantitas Sales Order lebih kecil dari Kuantitas Kirim. Silahkan split Sales Order.<br>Kuantitas Sisa SO : '.hidezerodecimal($sisado).'<br>Kuantitas Kirim : '.hidezerodecimal($netto));
					}
					$sisado2=getsisaso($param['sambungso']);
					$sisanetto=$netto-$sisado;
					if($sisado2 < $sisanetto){
						throw new PDOException('Gagal, Sisa Kuantitas Split Sales Order lebih kecil dari Kuantitas Kirim.<br>Kuantitas Sisa SO : '.hidezerodecimal($sisado).'<br>Kuantitas Kirim : '.hidezerodecimal($sisado).'<br>Kuantitas Sisa Split SO : '.hidezerodecimal($sisado2).'<br>Kuantitas Kirim Split : '.hidezerodecimal($sisanetto));
					}
				}
				
				if(count($_SESSION['kualitas']) > 0){
					$storage=$_SESSION['kualitas'][0]['storage'];
					$ffa=$_SESSION['kualitas'][0]['ffa'];
					$moist=$_SESSION['kualitas'][0]['moist'];
					$dirt=$_SESSION['kualitas'][0]['dirt'];
					$dobi=$_SESSION['kualitas'][0]['dobi'];
				}
			}
			
			if($nopo=='' and $kontrakjual=='' and $kontrakbeli==''){
				$spb=$param['so'];
			}
			
			$nettosplit=0;
			$nettosplit2=0;
			if($param['sambungso']!=''){
				$nettosplit=$sisado;
				$nettosplit2=$netto-$sisado;
			}
			
			$data = array(
				'waktukeluar'=>tanggalsystemn($param['dateout']),
				'beratkeluar'=>str_replace(',','',$param['wei2nd']),
				'netto'=>str_replace(',','',$param['netto']),
				'kontrakjual2'=>$param['sambungso'],
				'nettosplit'=>$nettosplit,
				'nettosplit2'=>$nettosplit2,
				'notekirim'=>$notekirim,
				'potongan'=>str_replace(',','',$param['kgpotongan']),
				'qr'=>$param['qrcode'],
				'segel'=>$param['segel'],
				'keterangan'=>$keterangan,
				'storage'=>$storage,
				'batch'=>'',
				'receivedate'=>'',
				'receiveqty'=>'',
				'loses'=>'',
				'gainloses'=>'',
				'ffa'=>$ffa,
				'moist'=>$moist,
				'dirt'=>$dirt,
				'dobi'=>$dobi,
				'krani'=>$_SESSION['standard']['username'],
				'FLAG'=>'0',
			);
			
			$str = updateQuery($dbname,'wb',$data,"notransaksi='".$param['ticketno']."'");
			$owlPDO->exec($str);
			
			if($param['tipe']=='I'){
				if(count($_SESSION['grading']) > 0){
					foreach($_SESSION['grading'] as $key=>$val){
						$cols=array();
						$datadt[$i] = array(
							'notransaksi'=>$param['ticketno'],
							'kode'=>$val['kode'],
							'field'=>$val['field'],
							'value'=>$val['value'],
							'status'=>'1',
						);
						foreach($datadt[$i] as $key=>$row) {
							$cols[] = $key;
						}
						$strx = insertQuery($dbname,'wb_grading',$datadt[$i],$cols);
						$owlPDO->exec($strx);
					}
				}
				
				if(count($_SESSION['sortasi']) > 0){
					foreach($_SESSION['sortasi'] as $key=>$val){
						$cols=array();
						$datadt[$i] = array(
							'notransaksi'=>$param['ticketno'],
							'kode'=>$val['kode'],
							'field'=>$val['field'],
							'value'=>$val['value'],
							'status'=>'1',
						);
						foreach($datadt[$i] as $key=>$row) {
							$cols[] = $key;
						}
						$strx = insertQuery($dbname,'wb_sortasi',$datadt[$i],$cols);
						$owlPDO->exec($strx);
					}
				}
			}
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			echo "error" . addslashes($e->getMessage());
		}
	break;
	
	case'formquality':
		$tab="";
		$optstorage="<option value=''>Silahkan pilih</option>";
		$optstorage.="<option value='1'>1</option>";
		$optstorage.="<option value='2'>2</option>";
		$optstorage.="<option value='3'>3</option>";
		$tab.="<table cellpadding='10'>
			<tr>
				<td>Storage</td>
				<td>:</td>
				<td>
					<select class='select2' style='height:32px;' id='storage' tabindex=1>".$optstorage."</select>
				</td>
			</tr>
			<tr>
				<td>FFA</td>
				<td>:</td>
				<td>
					<input class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=ffa onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>Moist</td>
				<td>:</td>
				<td>
					<input class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=moist onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>Dirt</td>
				<td>:</td>
				<td>
					<input class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=dirt onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td>Dobi</td>
				<td>:</td>
				<td>
					<input class=myinputtext style='width:60%;text-align:right;height:28px' type=text id=dobi onkeypress=\"return isNumberKey(event);\" onkeydown='upperCaseF(this)' value='' placeholder='0'>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton id=simpanquality style=height:30px onclick='simpanquality()'>Simpan</button>
				</td>
			</tr>
		</table>";
		echo $tab;
	break;
	
	case'formgrading':
		$tab="<label style='font-size:20px;font-weight:bold;color:red'>Master data Grading belum ada!!</label>";
		$str="select * from ".$dbname.".wb_5grading where status='1'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$tab="<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
				<tr>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Jjg</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
				</tr>";
						
				$str="select * from ".$dbname.".wb_5grading where status='1'";
				$res=fetchdata($str);
				foreach ($res as $valx) {
					$tab.="<tr>
						<td><label class=label>".$valx['deskripsi']."</label></td>";
					if($valx['jjg']!=''){
						$tab.="<td style='text-align:center'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='jjg' id='".$valx['kode']."__".$valx['jjg']."' value='' placeholder='0'>
						</td>";
					}
					
					if($valx['persen']!=''){
						$tab.="<td style='text-align:center'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='persen' id='".$valx['kode']."__".$valx['persen']."' value='' onblur=hitungsortasi(this.name,this.id) placeholder='0'>
						</td>";
					}
					
					if($valx['kg']!=''){
						$tab.="<td style='text-align:center'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='kg' id='".$valx['kode']."__".$valx['kg']."' value='' placeholder='0'>
						</td>";
					}
					$tab.="</tr>";
				}
			$tab.="</table>
			<center><button class=mybutton style=height:30px onclick='simpangrading()'>Simpan</button></center>";
		}
		
		echo $tab;
	break;
	
	case'formsortasi':
		$tab="<label style='font-size:20px;font-weight:bold;color:red'>Master data Sortasi belum ada!!</label>";
		$str="select * from ".$dbname.".wb_5sortasi where status='1'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$tab="<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
				<tr>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
					<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
				</tr>";
						
				foreach ($res as $valx) {
					$tab.="<tr>
						<td><label class=label>".$valx['deskripsi']."</label></td>";
					if($valx['persen']!=''){
						$tab.="<td style='text-align:center'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='persen' id='".$valx['kode']."__".$valx['persen']."' value='' onblur=hitungsortasi(this.name,this.id) placeholder='0'>
						</td>";
					}
					
					if($valx['kg']!=''){
						$tab.="<td style='text-align:center'>
							<input class=myinputtext type=text style='text-align:right;width:50%' onkeypress='return isNumberKey(event);' name='kg' id='".$valx['kode']."__".$valx['kg']."' value='' placeholder='0'>
						</td>";
					}
					$tab.="</tr>";
				}
			$tab.="</table>
			<center><button class=mybutton style=height:30px onclick='simpansortasi()'>Simpan</button></center>";
		}
		
		echo $tab;
	break;
	
	case'simpanquality':
		$_SESSION['grading']=array();
		$_SESSION['sortasi']=array();
		$_SESSION['kualitas']=array();
		$newdata = array(
			'storage'=>$param['storage'],
			'ffa'=>$param['ffa'],
			'moist'=>$param['moist'],
			'dirt'=>$param['dirt'],
			'dobi'=>$param['dobi']
		);
		array_push($_SESSION['kualitas'],$newdata);
		$tab.="<table cellpadding='3'>
			<tr>
				<td>Storage</td>
				<td>:</td>
				<td>".$param['storage']."</td>
			</tr>
			<tr>
				<td>FFA</td>
				<td>:</td>
				<td>".hidezerodecimal($param['ffa'],3)."</td>
			</tr>
			<tr>
				<td>Moist</td>
				<td>:</td>
				<td>".hidezerodecimal($param['moist'],3)."</td>
			</tr>
			<tr>
				<td>Dirt</td>
				<td>:</td>
				<td>".hidezerodecimal($param['dirt'],3)."</td>
			</tr>
			<tr>
				<td>Dobi</td>
				<td>:</td>
				<td>".hidezerodecimal($param['dobi'],3)."</td>
			</tr>
		</table>";
		echo $tab;
	break;
	
	case'simpangrading':
		$tab="<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
			<tr>
				<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
				<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Jjg</td>
				<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
				<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
			</tr>";
	
		$_SESSION['grading']=array();
		$_SESSION['sortasi']=array();
		$_SESSION['kualitas']=array();
		$arrdt=array();
		for($i=0;$i<count($param['kriteria']);$i++){
			$expkriteria=explode('__',$param['kriteria'][$i]);
			$kode=$expkriteria[0];
			$kriteria=$expkriteria[1];
			$newdata = array(
				'kode'=>$kode,
				'field'=>$kriteria,
				'value'=>$param['nilai'][$i],
			);
			array_push($_SESSION['grading'],$newdata);
			$arrdt[$kode][$kriteria]=$param['nilai'][$i];
		}
		$str="select * from ".$dbname.".wb_5grading where status='1'";
		$res=fetchdata($str);
		foreach ($res as $valx){
			if($arrdt[$valx['kode']][$valx['jjg']]!='' || $arrdt[$valx['kode']][$valx['persen']]!='' || $arrdt[$valx['kode']][$valx['kg']]!=''){
				$tab.="<tr>";
				$tab.="<td style='font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>".$valx['deskripsi']."</td>";
				$tab.="<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>".$arrdt[$valx['kode']][$valx['jjg']]."</td>";
				$tab.="<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>".$arrdt[$valx['kode']][$valx['persen']]."</td>";
				$tab.="<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>".$arrdt[$valx['kode']][$valx['kg']]."</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</table>";
		echo $tab;
	break;
	
	case'simpansortasi':
		$tab="<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF'>
			<tr>
				<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Kriteria</td>
				<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Persen (%)</td>
				<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Berat</td>
			</tr>";
	
		$_SESSION['grading']=array();
		$_SESSION['sortasi']=array();
		$_SESSION['kualitas']=array();
		$arrdt=array();
		for($i=0;$i<count($param['kriteria']);$i++){
			$expkriteria=explode('__',$param['kriteria'][$i]);
			$kode=$expkriteria[0];
			$kriteria=$expkriteria[1];
			$newdata = array(
				'kode'=>$kode,
				'field'=>$kriteria,
				'value'=>$param['nilai'][$i],
			);
			array_push($_SESSION['sortasi'],$newdata);
			$arrdt[$kode][$kriteria]=$param['nilai'][$i];
		}
		$str="select * from ".$dbname.".wb_5sortasi where status='1'";
		$res=fetchdata($str);
		foreach ($res as $valx){
			if($arrdt[$valx['kode']][$valx['persen']]!='' || $arrdt[$valx['kode']][$valx['kg']]!=''){
				$tab.="<tr>";
				$tab.="<td style='font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>".$valx['deskripsi']."</td>";
				$tab.="<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>".$arrdt[$valx['kode']][$valx['persen']]."</td>";
				$tab.="<td style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>".$arrdt[$valx['kode']][$valx['kg']]."</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</table>";
		echo $tab;
	break;
	
	case'printticket':
		$str="select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
		$res=fetchdata($str);
		$inout=$res[0]['inout'];
		$kodebarang=$res[0]['kodebarang'];
		$waktumasuk=$res[0]['waktumasuk'];
		$waktukeluar=$res[0]['waktukeluar'];
		$nokendaraan=$res[0]['nokendaraan'];
		$beratmasuk=$res[0]['beratmasuk'];
		$beratkeluar=$res[0]['beratkeluar'];
		$netto=$res[0]['netto'];
		$potongan=$res[0]['potongan'];
		$potongan=$res[0]['potongan'];
		$supir=$res[0]['supir'];
		$krani=$res[0]['krani'];
		$transportir=$res[0]['transportir'];
		$supplier=$res[0]['supplier'];
		$customer=$res[0]['customer'];
		// $nopo=$res[0]['nopo'];
		// if($nopo==''){
			$nopo=$res[0]['kontrakbeli'];			
		// }
		// if($nopo==''){
			// $nopo=$res[0]['kontrakjual'];			
		// }
		
		#######################
		
		if($inout=='I'){
			$textheader="PENERIMAAN";
		}else{
			$textheader="PENGIRIMAN";
		}
		$optnmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
		$tanggal=substr($waktukeluar,0,10);
		$jammasuk=substr($waktumasuk,11,8);
		$jamkeluar=substr($waktukeluar,11,8);
		$optnmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$supcon=$optnmsupplier[$supplier];
		if($supplier==''){
			$optnmcustomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$customer."'");
			$supcon=$optnmcustomer[$customer];
		}
	
		$tab.="<style>
			@page {
				margin: 10px 20px 0 20px !important;
			}
		</style>";
		$tab.="<table width=100% cellspacing=0 border='0'>
			<tr>
				<td rowspan=3 style='width=15%;border-bottom:1px dashed #000'>x</td>
				<td style='text-align:center;font-size:20px;'>BUKTI ".$textheader." ".$optnmbarang[$kodebarang]."</td>
			</tr>
			<tr>
				<td style='text-align:right;font-size:10px'>Form : BSPMS-0-FR-02</td>
			</tr>
			<tr>
				<td style='text-align:right;font-size:10px;border-bottom:1px dashed #000'>Rev 0</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;width:35%;padding-top:10px'>PT. BAKRIE PASAMAN PLANTATIONS</td>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;width:35%;padding-top:10px;padding-left:10px'>Ticket No : ".$param['ticketno']."</td>
				<td style='text-align:left;font-size:12px;padding-left:10px;padding-top:10px'>Date</td>
				<td style='text-align:center;font-size:12px;padding-top:10px;'>:</td>
				<td style='text-align:right;font-size:12px;padding-top:10px;'>".tglstrip($tanggal)."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:10px;border-right:1px dashed #000'>Desa Sungai Aus, Sungai Aur N Sungai Aur, Sungai Aur</td>
				<td style='text-align:left;font-size:12px;border-right:1px dashed #000;padding-left:10px;border-bottom:1px dashed #000;vertical-align:top;padding-top:10px' rowspan=2>Doc Num : ".$nopo."</td>
				<td style='text-align:left;font-size:12px;padding-left:5px;padding-left:10px'>Time In</td>
				<td style='text-align:center;font-size:12px;'>:</td>
				<td style='text-align:right;font-size:12px;'>".$jammasuk."</td>
			</tr>
			<tr>
				<td style='text-align:left;font-size:10px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>Pasaman Barat Sumatera Barat</td>
				<td style='text-align:left;font-size:12px;padding-left:5px;padding-left:10px;padding-bottom:20px;border-bottom:1px dashed #000'>Time Out</td>
				<td style='text-align:center;font-size:12px;padding-bottom:20px;border-bottom:1px dashed #000'>:</td>
				<td style='text-align:right;font-size:12px;padding-bottom:20px;border-bottom:1px dashed #000'>".$jamkeluar."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:17%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
				<td style='text-align:center;width:23%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>2nd Weight</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Gross</td>
				<td style='text-align:center;width:12%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Sorting</td>
				<td style='text-align:center;width:12%;font-size:12px;padding-top:5px;'>Nett</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
				<td style='text-align:center;padding-top:0px;font-size:12px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratkeluar)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto+$potongan)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($potongan)."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:30%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Supplier / Customer</td>
				<td style='text-align:center;width:18%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Supplier Weight</td>
				<td style='text-align:center;width:32%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Transporter</td>
				<td style='text-align:center;width:20%;font-size:12px;padding-top:5px;'>Driver</td>
			</tr>
			<tr>
				<td style='text-align:center;padding-top:-5px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$supcon."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>0</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$transportir."</td>
				<td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".$supir."</td>
			</tr>
		</table>";
		
		$tab.="<table width=100%>
			<tr>
				<td height=60px>&nbsp;</td>
			</tr>
		</table>";
		
		// $tab.="<table width=100% cellspacing=0>
			// <tr>
				// <td style='text-align:center;width:25%;font-size:12px;border-right:1px dashed #000;padding-top:5px'>Vehicle No</td>
				// <td style='text-align:center;width:25%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>Item</td>
				// <td style='text-align:center;width:25%;font-size:12px;border-right:1px dashed #000;padding-top:5px;'>1st Weight</td>
				// <td style='text-align:center;width:25%;font-size:12px;padding-top:5px;'>Nett</td>
			// </tr>
			// <tr>
				// <td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".$nokendaraan."</td>
				// <td style='text-align:center;padding-top:0px;font-size:12px;border-right:1px dashed #000;border-bottom:1px dashed #000;vertical-align:top'>".$optnmbarang[$kodebarang]."</td>
				// <td style='text-align:center;padding-top:10px;font-size:12px;border-right:1px dashed #000;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($beratmasuk)."</td>
				// <td style='text-align:center;padding-top:10px;font-size:12px;padding-top:5px;padding-bottom:20px;border-bottom:1px dashed #000'>".hidezerodecimal($netto)."</td>
			// </tr>
		// </table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:center;width:50%;font-size:12px;border-right:1px dashed #000;padding-top:5px;border-top:1px dashed #000'>Diterima</td>
				<td style='text-align:center;width:50%;font-size:12px;padding-top:5px;border-top:1px dashed #000'>Disetujui / Disaksikan</td>
			</tr>
			<tr>
				<td style='height:50px;text-align:center;border-right:1px dashed #000;padding-top:5px'>&nbsp;</td>
				<td style='text-align:center;padding-top:5px'>&nbsp;</td>
			</tr>
			<tr>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'><u>&nbsp;&nbsp;".$krani."&nbsp;&nbsp;</u></td>
				<td style='text-align:center;font-size:12px;'><u>&nbsp;&nbsp;".$supir."&nbsp;&nbsp;</u></td>
			</tr>
			<tr>
				<td style='text-align:center;font-size:12px;border-right:1px dashed #000'>Krani Timbang</td>
				<td style='text-align:center;font-size:12px;'>Driver</td>
			</tr>
		</table>";
		
		$tab.="<table width=100% cellspacing=0>
			<tr>
				<td style='text-align:left;width:50%;font-size:12px;padding-top:20px'>Print By ".$krani." ".date('d/m/Y H:i:s')."</td>
				<td style='text-align:right;width:50%;font-size:12px;padding-top:20px'>ORIGINAL PRINT</td>
			</tr>
		</table>";
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A5', 'landscape');
		$dompdf->render();
		$dompdf->stream("Purchase Request", array("Attachment" => false));
	break;
}

function generatenotiket(){
    global $dbname;
    ##generate notiket
    $idwb="BSP";

    $str="select distinct RIGHT(notransaksi,6) as notransaksi from ".$dbname.".wb";
    $res=fetchdata($str);
    if(!$res)
    {
        $no_1=1;
        $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
    }
    else
    {   
        $str2="select RIGHT(notransaksi,6) as notransaksi from ".$dbname.".wb where notransaksi like '".$idwb."%' order by notransaksi desc limit 1";
        $res2=fetchdata($str2);
        if ($res2){
            $ticketno=$res2[0]['notransaksi'];
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

function getsisaso($id){
	global $dbname;
	
	$jlhso=0;
	$jlhreal=0;
	$sisado=0;
	$str="select sum(netto) as countnetto from ".$dbname.".wb where kontrakjual='".$id."'";
    $res=fetchdata($str);
	$jlhreal=$res[0]['countnetto'];
	
	$str="select kuantitas from ".$dbname.".pmn_kontrakjual where nokontrak='".$id."'";
    $res=fetchdata($str);
	$jlhso=$res[0]['kuantitas'];
	$sisado=($jlhso-$jlhreal);
	
	return $sisado;
}
?>
