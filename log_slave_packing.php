<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$tgl = checkPostGet('tgl','');
$unit = checkPostGet('unit','');
$ket = checkPostGet('ket','');
$peti = checkPostGet('peti','');
$serah = checkPostGet('serah','');
$terima = checkPostGet('terima','');
$nopo = checkPostGet('nopo','');
$norefrensi = checkPostGet('norefrensi','');
$jumlah = checkPostGet('jumlah','');
$pages = checkPostGet('page','');

## SEARCH
$srcunit = checkPostGet('srcunit','');
$srcperiode = checkPostGet('srcperiode','');
$srcnotrans = checkPostGet('srcnotrans','');
$srcnopr = checkPostGet('srcnopr','');
$srcnopo = checkPostGet('srcnopo','');

$notran=isset($_POST['notran'])? $_POST['notran']: '';
$pt =	isset($_POST['pt'])? $_POST['pt']: '';
$kodeorg=isset($_POST['kodeorg'])? $_POST['kodeorg']: '';




//$=$_POST[''];

$txtBarang=	isset($_POST['txtBarang'])? $_POST['txtBarang']: '';
$kdOrg=		isset($_POST['kdOrg'])? $_POST['kdOrg']: '';
$satuan=	isset($_POST['satuan'])? $_POST['satuan']: '';

$nobpb=	isset($_POST['nobpb'])? $_POST['nobpb']: '';
$nopp=	isset($_POST['nopp'])? $_POST['nopp']: '';
$kodebarang=	isset($_POST['kodebarang'])? $_POST['kodebarang']: '';
$jumlah=	isset($_POST['jumlah'])? $_POST['jumlah']: '';
$satuanpo=	isset($_POST['satuanpo'])? $_POST['satuanpo']: '';
$matauang=	isset($_POST['matauang'])? $_POST['matauang']: '';
$kurs=		isset($_POST['kurs'])? $_POST['kurs']: '';
$hargasatuan=	isset($_POST['hargasatuan'])? $_POST['hargasatuan']: '';
$keteranganpp=	isset($_POST['keteranganpp'])? $_POST['keteranganpp']: '';
$tampung=	isset($_POST['tampung'])? $_POST['tampung']: '';

$notranDet=	isset($_POST['notranDet'])? $_POST['notranDet']: '';
$nobpbDet=	isset($_POST['nobpbDet'])? $_POST['nobpbDet']: '';
$nopoDet=	isset($_POST['nopoDet'])? $_POST['nopoDet']: '';
$kodebarangDet=	isset($_POST['kodebarangDet'])? $_POST['kodebarangDet']: '';

$arrSt=array("0"=>"X","1"=>"V");
//$arrSt=array("0"=>$_SESSION['lang']['no'],"1"=>$_SESSION['lang']['yes']);
$perSch=	isset($_POST['perSch'])? $_POST['perSch']: '';
$kdPtSch=	isset($_POST['kdPtSch'])? $_POST['kdPtSch']: '';

//exit("Error:$mengetahui");

$nmKeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmCust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmBarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmTranp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

function cariyangqty($data,$kodebarang){
	$value = 0;
	if(isset($data[$kodebarang])){
		$value = $data[$kodebarang];
	}
	return $value;
}

//$optMt="<option value=''>".$_SESSION['lang']['pil']."</option>";
$i="select kode,matauang from ".$dbname.".setup_matauang";
$j=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
$j->setFetchMode(PDO::FETCH_ASSOC);
$optMt='';
while($k=$j->fetch())
{
	$optMt.="<option value='".$k['kode']."'>".$k['matauang']."</option>";
}

?>

<?php
switch($method){
	case'insert':
		if($peti==''){
			exit("Warning, Ukuran peti harus diisi.");
		}
		if($peti==''){
			exit("Warning, Ukuran peti harus diisi.");
		}
		if($ket==''){
			exit("Warning, No. Koli harus diisi.");
		}
		if($serah==''){
			exit("Warning, Menyerahkan harus dipilih.");
		}
		if($terima==''){
			exit("Warning, Penerimaan harus diisi.");
		}
		
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
		if($notransaksi==''){
			$notransaksi='PL'.date('YmdHis');
			$str="INSERT INTO ".$dbname.".log_packinght (notransaksi,kodept,kodeorg,tanggal,ukuranpeti,keterangan,createby,menyerahkan,menerima) values ('".$notransaksi."','".$optpt[$unit]."','".$unit."','".tanggalsystem($tgl)."','".$peti."','".$ket."','".$_SESSION['standard']['userid']."','".$serah."','".$terima."')";
		}else{
			$str="update ".$dbname.".log_packinght  set kodept='".$optpt[$unit]."',kodeorg='".$unit."',tanggal='".tanggalsystem($tgl)."',ukuranpeti='".$peti."',keterangan='".$ket."',menyerahkan='".$serah."',menerima='".$terima."',createby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."'";
		}
		
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
		echo $notransaksi;
	break;
	
	case'posting':
		$str="select count(notransaksi) as jlhbrs from ".$dbname.".log_packingdt where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$jlhbrs = $res[0]['jlhbrs'];
		if($jlhbrs <= 0){
			exit("Gagal, Belum ada detail dari no transaksi ini ".$notransaksi);
		}
	
		$sekarang=date('Y-m-d');
		$str="update ".$dbname.".log_packinght set posting=1,postingdate='".$sekarang."' where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case'delHead':
		$str="select nopo,nopp,kodebarang,notransaksireferensi,jumlah from ".$dbname.".log_packingdt where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$strx="update ".$dbname.".log_transit set pl=(pl-'".$val['jumlah']."') where nopo='".$val['nopo']."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."' and notransaksi='".$val['notransaksireferensi']."'";
			$owlPDO->exec($strx);
		}
		
		$str="delete from ".$dbname.".log_packinght where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".log_packingdt where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($str); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case'goCariPo':
		$tab.="<table cellspacing=1 border=0 class=sortable  width=100%>
			<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['nopo']."</td>
				<td align=center>".$_SESSION['lang']['nopp']."</td>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center width=50px>".$_SESSION['lang']['satuan']."</td>
				<td align=center width=50px>".$_SESSION['lang']['jumlah']."</td>
			</tr>
			</thead>
			</tbody>";
		
		$where = "";
		if(!empty($nopo)){
			$where .= " and nopo like '%".$nopo."%'";
		}
		if(isset($unit)){
			$where .= " and unit = '".$unit."'";
		}
		
		$str="select nopo,nopp,kodebarang,qty,pl,sj from ".$dbname.".log_transit where status='0' and statusterima='0' and posting='1' ".$where."";
		$res=fetchdata($str);
		$no=0;
		foreach($res as $val){
			$saldo=0;
			if($val['pl']=='0' and $val['sj']=='0'){
				$saldo=$val['qty'];
			}else{
				if($val['pl'] > 0){
					$saldo=($val['qty']-$val['pl']);
				}else{
					$saldo=($val['qty']-$val['sj']);
				}
			}
			if($saldo > 0){
				$no++;
				$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
				$optnmsatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$val['kodebarang']."'");
				$arrnopo[$no]['nopo'] = $val['nopo'];
				$arrnopo[$no]['nopp'] = $val['nopp'];
				$arrnopo[$no]['kodebarang'] = $val['kodebarang'];
				$arrnopo[$no]['namabarang'] = $optnmbarang[$val['kodebarang']];
				$arrnopo[$no]['satuan'] = $optnmsatuan[$val['kodebarang']];
				$arrnopo[$no]['jumlah'] = $saldo;
			}
		}
		
		$no=0;
		if(isset($arrnopo)){
			foreach($arrnopo as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent style='cursor:pointer' onclick=\"saveDetail('".$notransaksi."','".$val['nopo']."');\">
					<td>".$no."</td>
					<td>".$val['nopo']."</td>
					<td>".$val['nopp']."</td>
					<td style='text-align:center'>".$val['kodebarang']."</td>
					<td>".$val['namabarang']."</td>
					<td style='text-align:center'>".$val['satuan']."</td>
					<td style='text-align:right'>".hidezerodecimal($val['jumlah'],2)."</td>
				</tr>";
			}
		}else{
			$tab.="<tr class=rowcontent>
				<td colspan=7 align=center>".$_SESSION['lang']['errdatanotexist']."</td>
			</tr>";
		}
		
		echo $tab;
		break;
		
	case'saveDetail':
		$str="select notransaksi,nopo,nopp,kodebarang,qty,pl,sj from ".$dbname.".log_transit where status='0' and statusterima='0' and posting='1' and nopo='".$nopo."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$saldo=0;
			if($val['pl']=='0' and $val['sj']=='0'){
				$saldo=$val['qty'];
			}else{
				if($val['pl'] > 0){
					$saldo=($val['qty']-$val['pl']);
				}else{
					$saldo=($val['qty']-$val['sj']);
				}
			}
			
			$optnmsatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$val['kodebarang']."'");
			
			$strx="insert into ".$dbname.".log_packingdt (notransaksi, nopo, nopp, kodebarang, jumlah, satuanpo, matauang, kurs,notransaksireferensi) values ('".$notransaksi."','".$nopo."','".$val['nopp']."','".$val['kodebarang']."',
			'".$saldo."','".$optnmsatuan[$val['kodebarang']]."','IDR','1','".$val['notransaksi']."')";
			try{
				$owlPDO->exec($strx); 
				
				$strx="update ".$dbname.".log_transit set pl='".$saldo."' where nopo='".$nopo."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."' and notransaksi='".$val['notransaksi']."'";
				$owlPDO->exec($strx); 
			}catch(PDOException $e){
				continue;
			}
		}
	break;	
	
	case'deleteDetail':
		$str="delete from ".$dbname.".log_packingdt where notransaksi='".$notran."' and nobpb='".$nobpb."' and nopo='".$nopo."' and kodebarang='".$kodebarang."' and notransaksireferensi='".$norefrensi."'";
		try{
			$owlPDO->exec($str);
			
			$str="update ".$dbname.".log_transit set pl=(pl-'".$jumlah."') where notransaksi='".$norefrensi."' and nopo='".$nopo."' and kodebarang='".$kodebarang."'";
			$owlPDO->exec($str);
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
	
	case'updateAll':
		$i="update ".$dbname.".`log_packingdt` set jumlah='".$jumlah."' where notransaksi='".$notranDet."' and nobpb='".$nobpbDet."' and nopo='".$nopoDet."' and kodebarang='".$kodebarangDet."'";
		try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
	
	
	case'update'://case update header
		$i="update ".$dbname.".`log_packinght`  set kodept='".$pt."',kodeorg='".$kodeorg."',tanggal='".$tgl."',ukuranpeti='".$peti."',keterangan='".$ket."',menyerahkan='".$serah."',menerima='".$terima."',createby='".$_SESSION['standard']['userid']."' where notransaksi='".$notran."'";
		try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
	
	##########cari barang
	case'goCariBarang':
		echo"<table cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center>No</td>
					<td align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center>".$_SESSION['lang']['satuan']."</td>
				</tr>
		</thead>
		</tbody>";
		
		$i="select * from ".$dbname.".log_5masterbarang where kodebarang like '%".$txtBarang."%' or namabarang like '%".$txtBarang."%'";
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
		while ($d=$n->fetch())
		{
			$no+=1;
			echo"
				<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"goPickBarang('".$d['kodebarang']."','".$d['namabarang']."','".$d['satuan']."')\">
					<td align=center>".$no."</td>
					<td>".$d['kodebarang']."</td>
					<td>".$d['namabarang']."</td>
					<td>".$d['satuan']."</td>
				</tr>
			";
		}
		break;
	
	
	
	################################################################## cari barang
	case'getFormBarang':
		echo"<fieldset>
				<legend>".$_SESSION['lang']['form']."</legend>
					<table cellspacing=1 border=0>
						
						<tr>
							<td>".$_SESSION['lang']['notransaksi']."</td> 
							<td>:</td>
							<td><input type=text id=notran value='".$notran."' onkeypress=\"return tanpa_kutip(event);\" class=myinputtext disabled style=\"width:125px;\"></td>
						</tr>
						<tr style='display:none;'>
							<td>".$_SESSION['lang']['penerimaanbarang']."</td>
							<td>:</td>
							<td><input type=text id=nobpb  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'></td>
						</tr>
						<tr style='display:none;'>
							<td>".$_SESSION['lang']['nopo']."</td>
							<td>:</td>
							<td><input type=text id=nopo  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'></td>
						</tr>
						<tr style='display:none;'>
							<td>".$_SESSION['lang']['nopp']."</td>
							<td>:</td>
							<td><input type=text id=nopp  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kodebarang']."</td>
							<td>:</td>
							<td>
								<input type=text id=kodebarang disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'>
								<img src=images/zoom.png title='".$_SESSION['lang']['find']."'  class=resicon onclick=cariBarang('".$_SESSION['lang']['find']."',event)>
							</td>
						</tr>
						
						<tr>
							<td>".$_SESSION['lang']['namabarang']."</td>
							<td>:</td>
							<td>
								<input type=text id=namabarang disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'>
							</td>
						</tr>
						
						<tr>
							<td>".$_SESSION['lang']['jumlah']."</td>
							<td>:</td>
							<td><input type=text id=jumlah class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'></td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['satuan']."</td>
							<td>:</td>
							<td><input type=text id=satuan disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:125px;'></td>
						</tr>
						<tr style='display:none;'>
							<td>".$_SESSION['lang']['matauang']."</td>
							<td>:</td>
							<td><select id=matauang = style=\"width:130px;\">".$optMt."</select></td>						
						</tr>
						
						<tr style='display:none;'>
							<td>".$_SESSION['lang']['kurs']."</td>
							<td>:</td>
							<td><input type=text id=kurs  class=myinputtext maxlength=100 onkeypress=\"return angka_doang(event);\" style='width:125px;'></td>
						</tr>
						
						<tr style='display:none;'>
							<td>".$_SESSION['lang']['harga']."</td>
							<td>:</td>
							<td><input type=text id=hargasatuan  class=myinputtextnumber maxlength=100 onkeypress=\"return angka_doang(event);\" style='width:125px;'></td>
						</tr>
						
						
						<td colspan=3>
						<hr></td>
						<tr>
							<td colspan=3 align=center>
								<button class=mybutton onclick=saveFormBarang()>".$_SESSION['lang']['save']."</button>
								<button class=mybutton onclick=cancelFormBarang()>".$_SESSION['lang']['delete']."</button>
								<button class=mybutton onclick=closeDialog()>".$_SESSION['lang']['selesai']."</button>
							</td>
						</tr>
						
					</table>
				</fieldset>	";
		
		
		
		break;
	
	

	
	#################################################################### cari PO
	
		
	
		
	
	
	case'saveFormBarang':
		$i="INSERT INTO ".$dbname.".`log_packingdt` (`notransaksi`, `nobpb`, `nopo`, `nopp`, `kodebarang`, `jumlah`, `satuanpo`, `matauang`, `kurs`, `harga`)
		values ('".$notran."','".$nobpb."','".$nopo."','".$nopp."',
		'".$kodebarang."','".$jumlah."','".$satuan."','".$matauang."','".$kurs."','".$hargasatuan."')";	
		try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;	
		
	case'updateDetail':
		$i="update ".$dbname.".`log_packingdt`  set jumlah='".$jumlah."' where notransaksi='".$notran."' and nobpb='".$nobpb."' and nopo='".$nopo."' and kodebarang='".$kodebarang."'";
		try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;	
	

	#####LOAD DETAIL DATA	
	case 'loadDetail';/*<td align=center>".$_SESSION['lang']['jumlah']." BPB</td>
					<td align=center>".$_SESSION['lang']['jumlah']." Terkirim</td>*/
			echo"<table class=sortable cellspacing=1 border=0 width=100%>
			 <thead>
				 <tr class=rowheader>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td align=center>".$_SESSION['lang']['nopo']."</td>
					<td align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center>".$_SESSION['lang']['jumlah']."</td>
					<td align=center>".$_SESSION['lang']['satuan']."</td>
					<td align=center>Action</td>
				 </tr>
			</thead>
			<tbody></fieldset>";
		$no=0;
		$a="select * from ".$dbname.".log_packingdt where notransaksi='".$notran."' ";
		$b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
		$b->setFetchMode(PDO::FETCH_ASSOC);
		while($c=$b->fetch())
		{
			$no+=1;
			
			$xCek="	select a.nopo,a.kodebarang,sum(a.jumlah) as jumlah from ".$dbname.".log_packingdt a
					where a.nopo='".$c['nopo']."' and a.kodebarang='".$c['kodebarang']."' group by a.nopo,a.kodebarang
					union
					select b.nopo,b.kodebarang,sum(b.jumlah) as jumlah from ".$dbname.".log_suratjalandt b
					where b.jenis='PO' and b.nopo='".$c['nopo']."' and b.kodebarang='".$c['kodebarang']."' group by b.nopo,b.kodebarang
					union
					select c.nopo,c.kodebarang,sum(c.jumlah) as jumlah from ".$dbname.".log_konosemendt c
					where c.jenis='PO' and c.nopo='".$c['nopo']."' and c.kodebarang='".$c['kodebarang']."' group by c.nopo,c.kodebarang";
			$yCek=$owlPDO->query($xCek) or die(print " Gagal: ".PDOException::getMessage());
			$yCek->setFetchMode(PDO::FETCH_ASSOC);
			$zCek=$yCek->fetch();
			
			
			$i="select * from ".$dbname.".log_po_vw where  statuspo='3' and nopo='".$c['nopo']."' and kodebarang='".$c['kodebarang']."'  ";
			$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
			$n->setFetchMode(PDO::FETCH_ASSOC);
			$d=$n->fetch();
			
			$nobpb=makeOption($dbname,'log_transaksi_vw','nopo,notransaksi');
			
				$whi="nopo='".$d['nopo']."' and kodebarang='".$d['kodebarang']."' and tipetransaksi=1 ";
			$jumlah=makeOption($dbname,'log_transaksi_vw','notransaksi,jumlah',$whi);
			
			//$jumlah[$nobpb[$d['nopo']]];
			
			$jumlahSimpan=$zCek['jumlah']-$c['jumlah'];/*
					<td>".$jumlah[$nobpb[$d['nopo']]]."</td><td>".$jumlahSimpan."</td>*/
			
			echo"<tr class=rowcontent  id=row".$no.">
					<td align=center>".$no."</td>
					<td id=notranDet".$no.">".$c['notransaksi']."</td>
					<td id=nopoDet".$no.">".$c['nopo']."</td>
					<td id=kodebarangDet".$no.">".$c['kodebarang']."</td>
					
					<td>".$nmBarang[$c['kodebarang']]."</td>
					
					<td><input type=text id=jumlah".$no." value=".$c['jumlah']." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:70px;\" disabled></td>
					<td>".$c['satuanpo']."</td>
					<td style='text-align:center'>
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"DelDetail('".$c['notransaksi']."','".$c['nobpb']."','".$c['nopo']."','".$c['kodebarang']."','".$c['notransaksireferensi']."','".$c['jumlah']."');\" >
					</td>
				</tr>";
		}		
		echo"<tr>
				<td colspan=14 align=center>
					<button class=mybutton style='display:none' id=editAll onclick=editAll(".$no.")>".$_SESSION['lang']['saveall']."</button>
					<button class=mybutton id=cancelDetail onclick=cancel()>".$_SESSION['lang']['selesai']."</button>
				</td>
			 </tr>";//<button class=mybutton id=editAll onclick=editAll()>".$_SESSION['lang']['edit']."</button>
		
		echo"</table>";
	break;	
	

	case'loadData':
		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>No. PR</td>
					<td align=center>No. PO</td>
					<td align=center>".$_SESSION['lang']['unit']."</td>
					<td align=center>".$_SESSION['lang']['dibuatoleh']."</td>
					<td align=center>".$_SESSION['lang']['menyerahkan']."</td>
					<td align=center>".$_SESSION['lang']['penerima']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>
			</thead>
			<tbody>";
			
			## PAGING ##
			$limit=25;
			$page=0;
			if(isset($pages)){
				$page=$pages;
				if($page<0 || $page=='') 
					$page=0;
			}
			
			$offset=$page*$limit;
			
			$no=(($page*$limit));
			
			$where="";
			if($srcunit!=''){
				$where.=" and kodeorg='".$srcunit."'";
			}
			
			if($srcperiode!=''){
				$where.=" and tanggal like '".$tanggal."%'";
			}
			
			if($srcnotrans!=''){
				$where.=" and notransaksi like '%".$srcnotrans."%'";
			}
			
			$str="select count(*) as jmlhrow from ".$dbname.".log_packinght where 1=1 ".$where."";
			$res=fetchdata($str);
			$jlhbrs= $res[0]['jmlhrow'];
			
			if($jlhbrs <= 0){
				$tab.="<tr class=rowcontent><td colspan='10' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			}else{
				$str="select * from ".$dbname.".log_packinght where 1=1 ".$where." order by tanggal desc  limit ".$offset.",".$limit."";
				$res=fetchdata($str);
				foreach($res as $val){
					## GET NO PR
					$nourutx = 0;
					$noprx = "";
					$strx="select distinct(nopp) as nopp from ".$dbname.".log_packingdt where notransaksi='".$val['notransaksi']."'";
					$resx=fetchdata($strx);
					foreach($resx as $keyx=>$valx){
						if($nourutx==0){
							$noprx .= $valx['nopp'];
						}else{
							$noprx .= "<br>".$valx['nopp']."";
						}
						$nourutx++;
					}
					
					$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createby']."'");
					$nmKar2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['menyerahkan']."'");
					
					## Get No PO
					$nourutx = 0;
					$nopox = "";
					$strx="select distinct(nopo) as nopo from ".$dbname.".log_packingdt where notransaksi='".$val['notransaksi']."'";
					$resx=fetchdata($strx);
					foreach($resx as $keyx=>$valx){
						if($nourutx==0){
							$nopox .= $valx['nopo'];
						}else{
							$nopox .= "<br>".$valx['nopo']."";
						}
						$nourutx++;
					}
					
					$no++;
					$tab.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$val['notransaksi']."</td>
						<td align=center style='min-width:80px'>".tanggalnormal($val['tanggal'])."</td>
						<td>".$noprx."</td>
						<td>".$nopox."</td>
						<td align=center>".$val['kodeorg']."</td>
						<td align=left>".$nmKar[$val['createby']]."</td>
						<td align=left>".$nmKar2[$val['menyerahkan']]."</td>
						<td>".$val['menerima']."</td>";
						
						if($val['posting']=='0'){
							$tab.="<td align=center>
								<img src=images/application/application_edit.png  title='update' class=resicon  caption='Edit' onclick=\"edit('".$val['notransaksi']."','".$val['kodeorg']."','".tanggalnormal($val['tanggal'])."','".$val['ukuranpeti']."','".$val['keterangan']."','".$val['menyerahkan']."','".$val['menerima']."');\">
								<img src=images/application/application_delete.png  title='delete' class=resicon caption='Delete' onclick=\"delHead('".$val['notransaksi']."');\">
								<img src=images/hot.png  title='Posting' class=zImgBtn caption='Posting' onclick=\"posting('".$val['notransaksi']."');\">
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_splht','".$val['notransaksi']."','','log_slave_packing_pdf',event)\">
							</td>";
						}else{
							$tab.="<td align=center>
								<img src=images/buttongreen.png class=zImgBtn>
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_splht','".$val['notransaksi']."','','log_slave_packing_pdf',event)\">
							   </td>";
						}
						
					$tab.="</tr>";
				}
				
				## PAGING
				$tab.=createpaging($jlhbrs,$limit,$page,'10','loadData','getPage');
				$tab.="</table>";
			}
			
			// $i="";
			// $n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
			// $n->setFetchMode(PDO::FETCH_ASSOC);
			// $no=$maxdisplay;
			// while($d=$n->fetch())
			// {
				// ## Get No PR
				// $nourutx = 0;
				// $noprx = "";
				// $strx="select distinct(nopp) as nopp from ".$dbname.".log_packingdt where notransaksi='".$d['notransaksi']."'";
				// $resx=fetchdata($strx);
				// foreach($resx as $keyx=>$valx){
					// if($nourutx==0){
						// $noprx .= $valx['nopp'];
					// }else{
						// $noprx .= "<br>".$valx['nopp']."";
					// }
					// $nourutx++;
				// }
				
				// ## Get No PO
				// $nourutx = 0;
				// $nopox = "";
				// $strx="select distinct(nopo) as nopo from ".$dbname.".log_packingdt where notransaksi='".$d['notransaksi']."'";
				// $resx=fetchdata($strx);
				// foreach($resx as $keyx=>$valx){
					// if($nourutx==0){
						// $nopox .= $valx['nopo'];
					// }else{
						// $nopox .= "<br>".$valx['nopo']."";
					// }
					// $nourutx++;
				// }
				
				// $no+=1;
				// echo "<tr class=rowcontent>";
				// echo "<td align=center>".$no."</td>";
				// echo "<td align=left>".$d['notransaksi']."</td>";
				// echo "<td align=left>".tanggalnormal($d['tanggal'])."</td>";
				// echo "<td align=left>".$noprx."</td>";
				// echo "<td align=left>".$nopox."</td>";
				// echo "<td align=left>".$d['kodept']."</td>";
				// echo "<td align=left>".@$nmKar[$d['createby']]."</td>";
				// echo "<td>".@$nmKar[$d['menyerahkan']]."</td>";
				// echo "<td>".$d['menerima']."</td>";
				
				// if($d['posting']=='0')
				// {
						// $post="<td align=center>
									// <img src=images/application/application_edit.png  title='update' class=resicon  caption='Edit' onclick=\"edit('".$d['notransaksi']."','".$d['kodept']."','".tanggalnormal($d['tanggal'])."','".$d['ukuranpeti']."','".$d['keterangan']."','".$d['menyerahkan']."','".$d['menerima']."');\">
									// <img src=images/application/application_delete.png  title='delete' class=resicon caption='Delete' onclick=\"delHead('".$d['notransaksi']."');\">
									// <img src=images/hot.png  title='Posting' class=zImgBtn caption='Posting' onclick=\"posting('".$d['notransaksi']."');\">
									// <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_splht','".$d['notransaksi']."','','log_slave_packing_pdf',event)\">
								// </td>";
				// }
				// else
				// {
						// $post="<td align=center>
								// <img src=images/buttongreen.png class=zImgBtn>
								// <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_splht','".$d['notransaksi']."','','log_slave_packing_pdf',event)\">
							   // </td>";
				// }
					
				// echo $post;	
				// echo "</tr>";
			// }
			// echo"
			// <tr class=rowheader><td colspan=10 align=center>
			// ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
			// <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
			// <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			// </td>
			// </tr>";
			// echo"</tbody></table>";
			
			echo $tab;
		break;
		
		
		
		
		
		
		
		
		
		case'getlistgudang':
			$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi
			WHERE left(kodeorganisasi,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe = '".$_SESSION['empl']['tipelokasitugas']."') and `tipe` ='GUDANG'";
			$result = fetchData($str);
			$html ="";
			foreach($result as $key=>$d)
			{
				$html.= "<option value='".$d['kodeorganisasi']."'>".$d['namaorganisasi']."</value>";
			}
			echo $html;
		break;
	default;
}
?>