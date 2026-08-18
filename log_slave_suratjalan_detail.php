<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');

$proses = $_GET['proses'];
$param = $_POST;

switch($proses) {
    case 'showDetail':
		# Get Data
		$where = "nosj='".$param['nosj']."' and kodept='".$param['kodept']."'";
		$cols = "*";
		$query = selectQuery($dbname,'log_suratjalandt',$cols,$where);
		$data = fetchData($query);
		$kodeBarang = "";
		foreach($data as $key=>$row) {
			if($row['jenis']=='po') {
				if($kodeBarang!='') {$kodeBarang .= ",";}
				$kodeBarang .= "'".$row['kodebarang']."'";
			}
		}
		
		# Options
		if(!empty($kodeBarang)) {
			$whereBarang = "kodebarang in (".$kodeBarang.")";
		} else {
			$whereBarang = null;
		}
		$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whereBarang);
		
		$table = "";
		$table .= "<fieldset style='margin-top:10px'><legend><b>Detail</b></legend>";
		$table .= "<table cellpadding=2 cellspacing=1 border=0 class='sortable'>";
		
		// Thead
		$table .= "<thead><tr class=rowheader>";
		$table .= "<td>".$_SESSION['lang']['jenis']."</td>";
		$table .= "<td>".$_SESSION['lang']['notransaksi']."</td>";
		$table .= "<td>".$_SESSION['lang']['kodebarang']."</td>";
		$table .= "<td>".$_SESSION['lang']['namabarang']."</td>";
		$table .= "<td>".$_SESSION['lang']['nopo']."</td>";
		$table .= "<td>".$_SESSION['lang']['nopp']."</td>";
		$table .= "<td>".$_SESSION['lang']['jumlah']."</td>";
		$table .= "<td>".$_SESSION['lang']['satuan']."</td>";
		$table .= "<td colspan=2>".$_SESSION['lang']['action']."</td>";
		$table .= "</tr></thead>";
		
		// Tbody
		$table .= "<tbody>";
		foreach($data as $key=>$row) {
			$table .= "<tr id='detRow_".$key."' class=rowcontent>";
			$table .= "<td id='t_jenis_".$key."'>".$row['jenis']."</td>";
			$table .= "<td id='t_jenis_".$key."'>".$row['nogr']."</td>";
			$table .= "<td id='t_kodebarang_".$key."'>".$row['kodebarang']."</td>";
			if(isset($optBarang[$row['kodebarang']])) {
				$table .= "<td id='t_namabarang_".$key."'>".$optBarang[$row['kodebarang']]."</td>";
			} else {
				$table .= "<td id='t_namabarang_".$key."'>".$row['kodebarang']."</td>";
			}
			if($row['jenis']=='M') {
				$table .= "<td id='t_nopo_".$key."' value='".$row['nopo']."'>";
				$table .= makeElement('el_nopo_'.$key,'text',$row['nopo'],array(
					'onkeyup' => "changeBg(getById('detRow_".$key."'),'#FF00FF')"
				));
				$table .= "</td>";
			} else {
				$table .= "<td id='t_nopo_".$key."' value='".$row['nopo']."'>".$row['nopo']."</td>";
			}
			if($row['jenis']=='M') {
				$table .= "<td id='t_nopp_".$key."' value='".$row['nopp']."'>";
				$table .= makeElement('el_nopp_'.$key,'text',$row['nopp'],array(
					'onkeyup' => "changeBg(getById('detRow_".$key."'),'#FF00FF')"
				));
				$table .= "</td>";
			} else {
				$table .= "<td id='t_nopp_".$key."' value='".$row['nopp']."'>".$row['nopp']."</td>";
			}
			$table .= "<td id='t_jumlah_".$key."' align=right>";
			if($row['jenis']!='PL') {
				$table .= makeElement('el_jumlah_'.$key,'textnum',$row['jumlah'],array(
					'onkeyup' => "changeBg(getById('detRow_".$key."'),'#FF00FF')"
				));
			} else {
				$table .= $row['jumlah'];
			}
			$table .= "</td>";
			$table .= "<td id='t_satuan_".$key."'>".$row['satuanpo']."</td><td>";
			if($row['jenis']!='PL') {
				$table .= "<img src='images/save.png' class=zImgBtn onclick='saveDetail(".$key.")' style='cursor:pointer'>";
			}
			$table .= "</td><td><img src='images/delete_32.png' class=zImgBtn onclick='deleteDetail(".$key.")' style='cursor:pointer'></td>";
			$table .= "</tr>";
		}
		$table .= "</tbody>";
		
		$table .= "</table></fieldset>";
		echo $table;
		break;
    
	// Add Detail
	case 'add2detail':
		$data = array();
			if(is_array($_POST['data'])){
				foreach($_POST['data'] as $key=>$row) {
						if($_POST['jenis']=='po') {
								$jumlah = $row['jumlah'];
								$satuan = $row['satuan'];
						} elseif($_POST['jenis']=='pl') {
								$jumlah = 1;
								$satuan = 'PETI';
								$row['notransaksi'] = "";
						} else {
								$jumlah = 0;
								$satuan = $row['satuan'];
								$row['notransaksi'] = "";
						}
						$data[] = array(
								'nosj' => $_POST['nosj'],
								'nogr' => $row['notransaksi'],
								'kodept' => $_POST['kodept'],
								'kodebarang' => $row['kodebarang'],
								'jenis' => strtoupper($_POST['jenis']),
								'jumlah' => $jumlah,
								'satuanpo' => $satuan,
								'nopo' => isset($row['nopo'])? $row['nopo']: '',
								'nopp' => isset($row['nopp'])? $row['nopp']: '',
								'notransaksireferensi' => '',
								'jumlahditerima' => 0
						);
				}
				$query = insertQuery($dbname, 'log_suratjalandt', $data);
				try{
					$owlPDO->exec($query); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		break;
	
	// Save Detail
	case 'saveDetail':		
		$nopo = $param['nopo'];
		if($nopo==''){
			$nopo = $param['newNopo'];
		}
		$nopp = $param['nopp'];
		if($nopp==''){
			$nopp = $param['newNopp'];
		}
				
		## Saldo Barang Gudang
		$data = array();
		$str="select kodebarang,saldoqty from ".$dbname.".log_5masterbarangdt where kodeorg='".$param['kodept']."' and kodegudang='".$param['kodeorg']."' and kodebarang='".$param['kodebarang']."'"; 
		$res = fetchData($str);
		$jumlahsaldo = $res[0]['saldoqty'];
		if($jumlahsaldo==''){
			$jumlahsaldo=0;
		}
		
		$where="";
		if($nopo!='' and $nopp!=''){
			$where=" and nopo='".$nopo."' and nopp='".$nopp."'";
		}			
			
		## Cek Jumlah yang diterima
		$str="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw where (tipetransaksi= '1' or tipetransaksi= '3') and post=1 and kodept='".$param['kodept']."' and kodegudang='".$param['kodeorg']."' and kodebarang='".$param['kodebarang']."' ".$where."";
		$res = fetchData($str);
		$jumlahterima = $res[0]['jumlah'];
		if($jumlahterima==''){
			$jumlahterima=0;
		}
		
		## cek surat jalan
		$str="select sum(jumlah) as jumlah from ".$dbname.".log_suratjalandt where kodebarang='".$param['kodebarang']."' ".$where." and nosj in (select nosj from ".$dbname.".log_suratjalanht where kodept='".$param['kodept']."' and kodeorg='".$param['kodeorg']."')";
		$res=fetchdata($str);
		$jumlahsj = $res[0]['jumlah'];
		if($jumlahsj==''){
			$jumlahsj = 0;
		}
		
		## cek packing list
		$str="select sum(jumlah) as jumlah from ".$dbname.".log_packingdt where kodebarang='".$param['kodebarang']."' ".$where." and notransaksi in (select notransaksi from ".$dbname.".log_packinght where kodept='".$param['kodept']."' and kodeorg='".$param['kodeorg']."')";
		$res=fetchdata($str);
		$jumlahpl = $res[0]['jumlah'];
		if($jumlahpl==''){
			$jumlahpl = 0;
		}
		
		## cek jumlah awal yang akan dikirim
		$str="select jumlah from ".$dbname.".log_suratjalandt where nosj='".$param['nosj']."' and kodept='".$param['kodept']."' and kodebarang='".$param['kodebarang']."' and nopo='".$param['nopo']."' and nopp='".$param['nopp']."'";
		$res=fetchdata($str);
		$jumlahawal = $res[0]['jumlah'];
		if($jumlahawal==''){
			$jumlahawal = 0;
		}
		
		## Jumlah yang sudah dikirim
		$jumlahterkirim = ($jumlahsj + $jumlahpl) - $jumlahawal;
		$totalkirim = $jumlahterkirim + $param['jumlah'];
		
		// exit("error : ".$jumlahsaldo."__".$jumlahterima."___".$jumlahsj."___".$jumlahpl."___".$jumlahawal."___".$jumlahterkirim);
			
		if($jumlahsaldo < $param['jumlah']){
			exit("warning : Jumlah stok gudang lebih kecil dari jumlah kirim : ".$jumlahsaldo);
		}
		
		if($jumlahterima < $totalkirim){
			exit("warning : Maksimal jumlah pengiriman untuk item barang ini adalah : ".$jumlahterima.", sudah terkirim : ".$jumlahterkirim);
		}
		
	
		$where = "nosj='".$param['nosj']."' and kodept='".$param['kodept'].
			"' and kodebarang='".$param['kodebarang']."' and nopo='".$param['nopo'].
			"' and nopp='".$param['nopp']."'";
		$cols = "*";
		$data = array('jumlah' => $param['jumlah']);
		if(isset($param['newNopo'])) {
			$data['nopo'] = $param['newNopo'];
			$data['nopp'] = $param['newNopp'];
		}
		$query = updateQuery($dbname,'log_suratjalandt',$data,$where);
		try{
			$owlPDO->exec($query); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
		
	// Delete Detail
	case 'deleteDetail':
		$where = "nosj='".$param['nosj']."' and kodept='".$param['kodept'].
			"' and kodebarang='".$param['kodebarang']."' and nopo='".$param['nopo'].
			"' and nopp='".$param['nopp']."'";
		$query = deleteQuery($dbname,'log_suratjalandt',$where);
		try{
			$owlPDO->exec($query); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
    default:
	break;
}
?>