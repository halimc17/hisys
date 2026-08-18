<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$optnmproduk=makeOption($dbname,'msproduk','kodeproduk,namaproduk',"kodeproduk='".$kodeproduktbs."'");
$optnmpcustomer=makeOption($dbname,'mscustomer','custcode,custname');
$optnamablok=makeOption($dbname,'msbloktph','indukblok,namaindukblok');
$optnamatph=makeOption($dbname,'mstph','kode,keterangan');
$optnamapemanen=makeOption($dbname,'msdatapemanen','karyawanid,namakaryawan');
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
		$str="select waktumasuk, waktukeluar from ".$dbname.".wb where in_out='I' and (waktumasuk LIKE '".$tanggal."%' or waktukeluar = '".$tanggal."%') and kodebarang = '".$kodeproduktbs."' and tipeunit in ('EXTERNAL','PLASMA')";
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
	
	case'getdivisi':
		$optdivisi="<option value=''>Silahkan pilih</option>";

		// cek apakah inti atau plasma
		$str2="select * from ".$dbname.".msunit where unitcode = '".$param['unit']."' ";
		$res2=fetchdata($str2);
		$unitname = strtoupper($res2[0]['unitname']);
		if (strpos($unitname, 'PLASMA') !== false) {
			$plasma = '1';
		}else{
			$plasma = '0';
		}

		$str="select divcode,divname from ".$dbname.".msdivisi where unitcode='".$param['unit']."' and divstatus='1'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$optdivisi="";
			$optdivisi="<option value=''>Silahkan pilih</option>";
			foreach ($res as $val) {
				if($param['divisi']==$val['divcode']){
					$optdivisi.="<option value='".$val['divcode']."' selected>".$val['divname']."</option>";					
				}else{
					$optdivisi.="<option value='".$val['divcode']."'>".$val['divname']."</option>";
				}
			}
		}
        
        echo $optdivisi."####".$plasma;
	break;
	
	case 'getkontrak':
        $optkontrak="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		// $str="select * from ".$dbname.".mscontractpurchase where vendorcode like '%".getPlant($param['unit'])."' and ctrstatus='1'";
		$str="select * from ".$dbname.".msso where sostatus='1' and custcode='".$param['customer']."' ";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so']==$val['noso']){
				$optkontrak.="<option value='".$val['noso']."' selected>".$val['noso']."</option>";				
			}else{
				$optkontrak.="<option value='".$val['noso']."'>".$val['noso']."</option>";				
			}
		}
        
        echo $optkontrak;
    break;
	
	case 'getcustomer':
        $optcustomer="<option value=''>Silahkan pilih</option>";
		## GET KONTRAK
		// $str="select * from ".$dbname.".mscontractpurchase where vendorcode like '%".getPlant($param['unit'])."' and ctrstatus='1'";
		// $str="select * from ".$dbname.".msso where noso='".$param['so']."' ";
		$str="select * from ".$dbname.".mscustomer where custstatus='1'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			if($param['so']==$val['custcode']){
				$optcustomer.="<option value='".$val['custcode']."' selected>".$optnmpcustomer[$val['custcode']]."</option>";
			}else{
				$optcustomer.="<option value='".$val['custcode']."'>".$optnmpcustomer[$val['custcode']]."</option>";
			}
		}
        
        echo $optcustomer;
    break;
	
	case 'loadData':
		// $where = "and netto='0' and kodebarang='".$kodeproduktbs."' and tipeunit in ('INTERNAL','PLASMA') and sumber='KEBUN' and in_out='I'";
		$where = "and netto='0' and kodebarang='".$kodeproduktbs."' and tipeunit in ('EXTERNAL','PLASMA') and sumber='KEBUN' and in_out='I' and (tahuntanam != '0' or tahuntanam != '' ) ";
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
			
			$str="select * from ".$dbname.".wb_datapanen where notiket='".$param['ticketno']."'";
			$res=fetchdata($str);
			if(count($res) <= 0){
				exit("warning : Data panen masih kosong!!! ");
			}

			// cek apakah inti atau plasma
			$str2="select * from ".$dbname.".msunit where unitcode = '".$param['unit']."' ";
			$res2=fetchdata($str2);
			$unitname = strtoupper($res2[0]['unitname']);
			if (strpos($unitname, 'PLASMA') !== false) {
				
			}else{
				if($param['so'] == '' || $param['customer'] == ''){
					exit("warning : Customer Atau Kontrak tidak boleh kosong... ");
				}
			}

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

			
			if($param['tipeangkut'] == '0'){
				if($param['transportir'] == ''){
					exit("warning : Tipe External Wajib Mengisi Transportir ");
				}
			}


			if($param['customer'] != ''){
				$tipeunit='EXTERNAL';
			}else{
				$tipeunit='PLASMA';
			}

			// cek kendaraan
			$str="select * from ".$dbname.".msvhc where vhccode='".$param['nokendaraan']."' and vhcstatus = '1' ";
			$res=fetchdata($str);
			if(count($res) > 0){
				$beratkendaraan=$res[0]['beratkendaraan'];
				$netto = str_replace(',','',$param['wei1st']) - $beratkendaraan ;

				if($netto < 0){
					exit("warning : Netto ".$netto."!!!");
				}

			}else{
				exit("warning : Kendaraan ".$param['nokendaraan']." tidak terdaftar atau status tidak aktif.");
			}


			if($param['adj_jjg'] != '0' || $param['adj_jjg'] != ''){

				// jika '' maka default kosong
				if($param['adj_jjg'] == ''){
					$adj_jjg = 0;
				}else{
					$adj_jjg = str_replace(',','',$param['adj_jjg']);
				}
				if($param['jjg'] == ''){
					$jjg = 0;
				}else{
					$jjg = str_replace(',','',$param['jjg']);
				}
				// akhir jika '' maka default kosong

				$hasil_jjg = $jjg + $adj_jjg;
			}
			
			if($hasil_jjg < 0){
				exit("warning : Jumlah Tandan tidak boleh minus... ");
			}

			// adjustment brondol
			if($param['adj_brondol'] != '0' || $param['adj_brondol'] != ''){

				// jika '' maka default kosong
				if($param['adj_brondol'] == ''){
					$adj_brondol = 0;
				}else{
					$adj_brondol = str_replace(',','',$param['adj_brondol']);
				}
				if($param['brondol'] == ''){
					$brondol = 0;
				}else{
					$brondol = str_replace(',','',$param['brondol']);
				}
				// akhir jika '' maka default kosong

				$hasil_brondol = $brondol + $adj_brondol;
			}
			
			if($hasil_brondol < 0){
				exit("warning : Jumlah brondolan tidak boleh minus... ");
			}
			// akhir adjustment brondol



			// $qrcode = str_replace('{OWL}', '', $param['qrcode']);
			// if (strlen($qrcode)==7) {
			// 	$qrcode = substr($qrcode, 0,3)."".substr($qrcode, 4,2)."".substr($qrcode, 3,4);
			// }
			
			$data = array(
				'notransaksi'=>generatenotiket('penerimaan',$kodeproduktbs),
				'in_out'=>'I',
				'waktumasuk'=>tanggalsystemn($param['datein']),
				'waktukeluar'=>tanggalsystemn($param['datein']),
				'beratmasuk'=>str_replace(',','',$param['wei1st']),
				'beratkeluar'=>$beratkendaraan,
				'netto'=>$netto,
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
				'kontrakjual'=>$param['so'],
				'kontrakjual2'=>'',
				'notekirim'=>'',
				'supir'=>$param['supir'],
				'nosim'=>'',
				'spb'=>$param['nospb'],
				'qr'=>$param['nospb'],
				'nokendaraan'=>$param['nokendaraan'],
				'qtysegel'=>$param['qtysegel'],
				'segel'=>$param['segel'],
				'janjang'=>str_replace(',','',$param['jjg']),
				'brondolan'=>str_replace(',','',$param['brondol']),
				'keterangan'=>$param['keterangan'],
				'transportir'=>$param['transportir'],
				'supplier'=>$supplier,
				'customer'=>$param['customer'],
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
				'tahuntanam'=>$param['tahuntanam'],
				'adjjjg'=>str_replace(',','',$param['adj_jjg']),
				'adjbrondol'=>str_replace(',','',$param['adj_brondol']),
				'tipeangkut'=>$param['tipeangkut'],
			);
			
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$str = insertQuery($dbname,'wb',$data,$cols);
			$owlPDO->exec($str);

			// update nocounter
			// periode berjalan
			$tgl = date("Y-m");
			// Mengambil 7 digit pertama dari $param['nospb']
			$nocounter = substr($param['nospb'], 0, 7);
			// Mengubah string menjadi bilangan bulat
			$nocounter = intval($nocounter);
			// Menambahkan 1 ke nilai $nocounter
			$nocounter += 1;
			// Mengonversi kembali ke string dan menambahkan nol di depan jika perlu
			$nocounter = str_pad($nocounter, 7, '0', STR_PAD_LEFT);
			$str="update ".$dbname.".setup_nourutspb set counter='".$nocounter."' where unit='".$param['unit']."' and divisi = '".$param['divisi']."' and periode = '".$tgl."' ";
			try
			{
				$owlPDO->exec($str);
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
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

			// $qrcode = str_replace('{OWL}', '', $param['qrcode']);
			// if (strlen($qrcode)==7) {
			// 	$qrcode = substr($qrcode, 0,3)."".substr($qrcode, 4,2)."".substr($qrcode, 3,4);
			// }
			
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
				'qr'=>$param['nospb'],
				'spb'=>$param['nospb'],
				'keterangan'=>$param['keterangan'],
				'krani'=>$_SESSION['standard']['username'],
				'FLAG'=>'0',
				'tahuntanam'=>$param['tahuntanam'],
			);
			// 'qr'=>$qrcode,
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
	// joki
	case'loadPanen':
		$strx="select * from ".$dbname.".wb_datapanen where notiket='".$param['ticketno']."'";
		$resx=fetchdata($strx);

		// blok
		$str="select * from ".$dbname.".msbloktph group by indukblok";
		$res=fetchdata($str);
		if(count($res) > 0){
			$optblok="";
			$optblok="<option value=''>Silahkan pilih</option>";
			foreach ($res as $val) {
					$optblok.="<option value='".$val['indukblok']."'>".$val['indukblok']." - ".$val['namaindukblok']."</option>";
			}
		}
		// tph
		$str="select * from ".$dbname.".mstph group by kode";
		$res=fetchdata($str);
		if(count($res) > 0){
			$opttph="";
			$opttph="<option value=''>Silahkan pilih</option>";
			foreach ($res as $val) {
					$opttph.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['keterangan']."</option>";
			}
		}
		// pemanen
		$str="select * from ".$dbname.".msdatapemanen";
		$res=fetchdata($str);
		if(count($res) > 0){
			$optpemanen="";
			$optpemanen="<option value=''>Silahkan pilih</option>";
			foreach ($res as $val) {
					$optpemanen.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']."</option>";
			}
		}


		$tab = "";
		$tab.="

		<div class='formPanen' style='vertical-align: top;'>
		<table>
			<tr>

				<td>QR Code <span style='color:red'>*</span></td>
				<td>:</td>
				<td><input class='myinputtext' style='width:150px' type='text' name='qrcode' id='qrcode'></td>

				<td style='padding-left: 20px;'>Blok <span style='color:red'>*</span></td>
				<td>:</td>
				<td>
					<!--<input class='myinputtext' type='text' name='blok_d' id='blok_d'>-->
					<select class='select2 myinputtext' style='width:164px' id='blok_d'>".$optblok."</select>
				</td>
	
				
			</tr>
			<tr>
				<td style='padding-left: 20px;'>TPH <span style='color:red'>*</span></td>
				<td>:</td>
				<td>
					<!--<input class='myinputtext' type='text' name='tph_d' id='tph_d'>-->
					<select class='select2 myinputtext' style='width:164px' id='tph_d'>".$opttph."</select>
				</td>
	
				<td style='padding-left: 20px;'>Janjang <span style='color:red'>*</span></td>
				<td>:</td>
				<td><input class='myinputtext' type='number' style='width:150px' name='janjang_d' id='janjang_d'></td>
			</tr>
			<tr>
				<td>Tanggal Panen <span style='color:red'>*</span></td>
				<td>:</td>
				<td>
				<input type=text class=myinputtext style='text-align:center;width:150px' id=tgl_panen onkeypress='return tanpa_kutip(event)' onmousemove='setCalendar(this.id)' readonly=readonly>
				</td>
				
				<td>Brondolan <span style='color:red'>*</span></td>
				<td>:</td>
				<td><input class='myinputtext' type='number' style='width:150px' name='brondolan_d' id='brondolan_d'></td>
				


			</tr>
			<tr>
				<td>Pemanen <span style='color:red'>*</span></td>
				<td>:</td>
				<td>
					<!--<input class='myinputtext' type='number' name='pemanen_d' id='pemanen_d'>-->
					<select class='select2 myinputtext' style='width:164px' id='pemanen_d'>".$optpemanen."</select>
				</td>
				
				<td>Sesi <span style='color:red'>*</span></td>
				<td>:</td>
				<td><input class='myinputtext' type='number' style='width:150px' name='sesi_d' id='sesi_d'></td>

				<td>
					<button class=mybutton id=getweight1 style='height:30px;margin:5px' onclick=tambahPanen_d()>Tambah</button>
				</td>
			</tr>
		</table>
	</div>
	
		<br>
			<div class=table-scroll style='height:200px;width:700px'>
				<table cellspacing=0 cellpadding=3 style='border:1px solid #FFFFFF;width:100%'>
				<thead>
				<tr class='rowcontent'>
				<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>No</th>
				<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>QR Code / No Panen</th>
				<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Tanggal</th>
				<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Blok</th>
				<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>TPH</th>
				<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Sesi</th>
				<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Pemanen</th>
					<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Janjang</th>
					<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Brondolan</th>
					<th style='text-align:center;font-weight:bold;border-bottom:1px solid #FFFFFF;border-right:1px solid #FFFFFF'>Aksi</th>
				</tr>
				</thead>
				<tbody>
				
				
			
		";
		$no = 0;
		foreach($resx as $key=>$row){
			$no++;
			@$jumlahjjg+=$row['jjg'];
			@$jumlahbrondolan+=$row['brondolan'];
			@$qrcode_n=$row['qrcode'];
			$tab.="<tr class='rowcontent'>
				<td style='text-align:center'>".$no."</td>
				<td style='text-align:center'>".$row['qrcode']."</td>
				<td style='text-align:center'>".$row['tanggal']."</td>
				<td style='text-align:center'>".$row['blok']." - ".$optnamablok[$row['blok']]."</td>
				<td style='text-align:center'>".$row['tph']." - ".$optnamatph[$row['tph']]."</td>
				<td style='text-align:center'>".$row['sesi']."</td>
				<td style='text-align:center'>".$optnamapemanen[$row['pemanen']]."</td>
				<td style='text-align:center'>".$row['jjg']."</td>
				<td style='text-align:center'>".$row['brondolan']."</td>
				<td style='text-align:center'>
					<img onclick=\"deletepanen('".$row['qrcode']."','".$row['notiket']."','".$row['tph']."','".$row['blok']."','".$row['pemanen']."','".$row['jjg']."','".$row['brondolan']."')\" class='zImgBtn' src='images/delete1.png' title='Hapus' style='cursor:pointer'>
				</td>
			</tr>";
		}
		$tab.="</tbody></div></table>";

		echo $tab."####".$qrcode_n."####".$jumlahjjg."####".$jumlahbrondolan;



	break;
	case'tambahPanen':
		$notiket=checkPostGet('ticketno','');
		$tph=checkPostGet('tph','');
		$pemanen=checkPostGet('pemanen','');
		$blok=checkPostGet('blok','');
		$janjang=checkPostGet('janjang','');
		$brondolan=checkPostGet('brondolan','');
		$qrcode=checkPostGet('qrcode','');
		$sesi=checkPostGet('sesi','');
		$tgl_panen=checkPostGet('tgl_panen','');
		$tgl = date("YmdHis");

		if (strpos(substr($tgl_panen,0,4), '-') !== false) {
			// Format tanggal adalah "00-00-0000"
			$tanggal_p = tanggalsystemn($tgl_panen);
		} elseif (strpos(substr($tgl_panen,0,4), '-') === false) {
			// Format tanggal adalah "0000-00-00"
			$tanggal_p =  $tgl_panen;
		} else {
			// Format tanggal tidak dikenali
			$tanggal_p =  $tgl_panen;
		}
		// validasi
		// cek apakah sudah insert di tiket lain
		// $str0="select * from ".$dbname.".wb_datapanen where qrcode = '".$qrcode."' and notiket != '".$notiket."'";
		// $res0=fetchdata($str0);
		// if(count($res0) > 0){
		// 	exit("warning : QR CODE '".$qrcode."' sudah ada di no tiket ".$res0[0]['notiket']." ");
		// }
		// cek duplicate
		$str1="select * from ".$dbname.".wb_datapanen where qrcode='".$qrcode."' and tph='".$tph."' and blok='".$blok."' and pemanen='".$pemanen."' and sesi='".$sesi."' ";
		$res1=fetchdata($str1);
		if(count($res1) > 0){
			exit("warning : QR CODE '".$qrcode."' , TPH ".$tph.",Blok ".$blok.", Pemanen ".$pemanen.", Sesi '".$sesi."' Data sudah ada di no tiket ".$res1[0]['notiket']." ");
		}
		$str = "insert into ".$dbname.".wb_datapanen values ('".$qrcode."','".$notiket."','".$tanggal_p."','".$tph."','".$blok."',".$sesi.",'".$pemanen."','".$janjang."','".$brondolan."','".$_SESSION['standard']['userid']."','".$tgl."','".$_SESSION['standard']['userid']."','".$tgl."')";
			try
			{
				$owlPDO->exec($str);
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}

	break;
	case 'deletepanen':
		$qrcode=checkPostGet('qrcode','');
		$notiket=checkPostGet('notiket','');
		$tph=checkPostGet('tph','');
		$blok=checkPostGet('blok','');
		$pemanen=checkPostGet('pemanen','');
		$str="delete from ".$dbname.".wb_datapanen where qrcode='".$qrcode."' and notiket='".$notiket."' and tph='".$tph."' and blok='".$blok."' and pemanen='".$pemanen."' ";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'getnoSPB':
		// periode berjalan
		$tgl = date("Y-m");
		// exit("warning : ".$tgl." ");
		// $str="select counter from ".$dbname.".setup_nourutspb where unit='".$param['unit']."' and divisi='".$param['divisi']."' and periode = '".$tgl."' ";
		// $res=fetchdata($str);
		// if(count($res) > 0){
		// 	$notransaksiNoSPB = $res[0]['counter'].'/'.$param['divisi'].'/'.date("m").'/'.date("Y");
		// }else{
		// 	exit("warning : Belum di setup notransaksi NO SPB Untuk Unit ".$param['unit'].", divisi ".$param['divisi'].", periode ".$tgl." ");
		// }

		// $str="select left(spb,4) as counter from ".$dbname.".wb where unitcode='".$param['unit']."' and divcode='".$param['divisi']."' order by spb desc limit 1";
		$str="select left(spb,4) as counter from ".$dbname.".wb where unitcode='".$param['unit']."' and waktumasuk like '".$tgl."%' order by spb desc limit 1";
		// exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$awal=$bar['counter']+1;
			$notransaksiNoSPB=addZero($awal,4).'/'.$param['unit'].'/'.date("m").'/'.date("Y");
        
        echo $notransaksiNoSPB;
	break;
	case'getkendaraan':
		$optkendaraan="<option value=''>Silahkan pilih</option>";
		if($param['tipeangkut'] == '0'){
			$str="select * from ".$dbname.".msvhc where vhcstatus='1' and vendorcode='".$param['transportir']."' ";
			$res=fetchdata($str);
			foreach ($res as $val) {
				$optkendaraan.="<option value='".$val['vhccode']."'>".$val['vhccode']."</option>";
			}
		}else{
			$str="select * from ".$dbname.".msvhc where vhcstatus='1'";
			$res=fetchdata($str);
			foreach ($res as $val) {
				$optkendaraan.="<option value='".$val['vhccode']."'>".$val['vhccode']."</option>";
			}
		}
        
        echo $optkendaraan;
	break;
	// end joki
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
