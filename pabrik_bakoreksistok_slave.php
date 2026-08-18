<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$unit = checkPostGet('unit','');
$kodept = checkPostGet('kodept','');
$tipe = checkPostGet('tipe','');
$kodebarang = checkPostGet('kodebarang','');
$kodetangki = checkPostGet('kodetangki','');
$tanggal = tanggalsystemn(checkPostGet('tanggal',''));
$jm = checkPostGet('jm','');
$mn = checkPostGet('mn','');
$jumlah = checkPostGet('jumlah','');
$keteranganht = checkPostGet('keteranganht','');
$waktu=$tanggal." ".$jm.":".$mn.":00";
$tanggalmulaisch=checkPostGet('tanggalmulaisch','');
$tanggalselesaisch=checkPostGet('tanggalselesaisch','');

if($tanggalmulaisch==''){
	$tanggalmulaisch='';
}else{
	$tanggalmulaisch = tanggalsystemn(checkPostGet('tanggalmulaisch',''));	
}

if($tanggalselesaisch==''){
	$tanggalselesaisch='';
}else{
	$tanggalselesaisch=tanggalsystemn(checkPostGet('tanggalselesaisch',''));
}
$notransaksisch=checkPostGet('notransaksisch','');
$kodetangkisch=checkPostGet('kodetangkisch','');

$table='pabrik_bakoreksistok';

$optbuyer=$optbarang=$opttipe=$optunit=$opttangki="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$unitsch = checkPostGet('unitsch','');
$kodeptsch = checkPostGet('kodeptsch','');
$tipesch = checkPostGet('tipesch','');
$kodebarangsch = checkPostGet('kodebarangsch','');
$kodetangkisch = checkPostGet('kodetangkisch','');

$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$namaorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"(tipe='PABRIK' or tipe='BULKING')");
$nmcustsomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmkapalponton=makeOption($dbname,'pmn_5kapalponton','kode,nama');
$nmfranco=makeOption($dbname,'pmn_5franco','id_franco,franco_name');

// exit("Error:".$method);
switch ($method) {
	
	case'pdf';
		
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$unit=$bar['unit'];
			$kodetangki=$bar['kodetangki'];
			$keterangan=$bar['keterangan'];
			@$totalqty+=$bar['jumlah'];
			$notransaksi=$bar['notransaksi'];
			$kodecustomer=$bar['kodecustomer'];
			$kodebarang=$bar['kodebarang'];
			$tanggal=$bar['tanggal'];
			$pelabuhantujuan=$bar['pelabuhantujuan'];
			$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
			
			if($bar['kodept']!=''){
				$kodept=$bar['kodept'];
			}
			$tanggalmuat1=$bar['tanggalmuat1'];
			$tanggalmuat2=$bar['tanggalmuat2'];
		}
		
		$str = "select distinct(kodept) as kodept from ".$dbname.".".$table." ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrkodept[$bar['kodept']]=$bar['kodept'];
		}
		
		
		
		#= ambil saldo awal 1 tangki tersebut
		$str = "select * from ".$dbname.".pabrik_masukkeluartangki where kodetangki='".$kodetangki."' and tanggal='".tglkemarin(substr($tanggal,0,10))."'
				and kodeorg='".$unit."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$saldoawaltangki=$bar['kuantitas'];
			
		#= ambil saldo awal 1 tangki tersebut
		$str = "select sum(jumlah) as jumlah from ".$dbname.".".$table." where kodetangki='".$kodetangki."' and tanggal like '".substr($tanggal,0,10)."%'
				and unit='".$unit."'";
				// echo $str;exit("Error");
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nilaiadjusttangki=$bar['jumlah'];	
		
		$saldoakhirtangki=$saldoawaltangki+$nilaiadjusttangki;
			
		#= stok setelah diadjust
		$str = "select * from ".$dbname.".pabrik_stokbulking where kodebarang='".$kodebarang."' and tanggal='".substr($tanggal,0,10)."'
				and kodeunit='".$unit."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$saldoakhirpt[$bar['kodept']]=$bar['jumlah'];
		}
		
	// print_r($arrkodept);exit();
	
		$tab="<style>
			@page {
				margin-top: 30px;
				margin-left: 30px;
				margin-right: 30px;
				margin-bottom: 30px;
			}
			body {
				font-family: Tahoma, Verdana, Segoe, sans-serif;
			}
			
			footer {
				position: fixed; 
				bottom: -20px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			div.page_break {
				page-break-before: always;
			}
		</style>";
	
	
	
		$cellpadding=3;
	
		
		// $tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:20px'><b><u>BERITA ACARA ADJUST STOK ".$nmkomoditi[$kodebarang]."</u></b></td>"; 
			$tab.="</tr>";
			
			
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'>".$nmpt[$kodept]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'>No : ".$notransaksi."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
	
		
		$tab.="<br>";
		
		
		
		$tab.="Data Stok";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>&nbsp;&nbsp;".$_SESSION['lang']['stok']." ".$_SESSION['lang']['awal']."</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($saldoawaltangki)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";	
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>&nbsp;&nbsp;".$_SESSION['lang']['penerimaan']."</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";				
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>&nbsp;&nbsp;".$_SESSION['lang']['selisih']."</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($nilaiadjusttangki)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";	
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>&nbsp;&nbsp;".$_SESSION['lang']['stok']." ".$_SESSION['lang']['akhir']."</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($saldoakhirtangki)."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";	
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;' colspan=2>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";	
				$tab.="<td style='text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0px solid #000000;' colspan=2>&nbsp;</td>"; 
			$tab.="</tr>";
			
			
			
			$tab.="<tr>";	
				$tab.="<td style='text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>&nbsp;&nbsp;".$_SESSION['lang']['keterangan']." : ".$keterangan."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Stock setelah dilakukan Adjust Stock tanggal ".tanggalnormal($tanggal)."</td>"; 
			$tab.="</tr>";
			
			foreach($arrkodept as $kdpt){
				$tab.="<tr>";	
					$tab.="<td style='text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$nmpt[$kdpt]."</td>"; 
					$tab.="<td style='text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($saldoakhirpt[$kdpt])."</td>"; 
				$tab.="</tr>";
			}

	
		$tab.="</table>";
	
		
		$tab.="<br>";
		
		
		$kota='Pontianak';
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
		
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=6></td>"; 
				$tab.="<td colspan=3 align=center>".$kota.", ".tglnmbln($tanggal,'long','I')."</td>"; 
			$tab.="</tr>";
			// exit("Error:ASD");
			
		for($i=1;$i<6;$i++){
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			}
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Nama & Cap</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Nama & Cap</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Security</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Staff</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Manager</td>"; 
			$tab.="</tr>";

		$tab.="</table>";	
		
		$tab.="<br>";
	
		$table='';
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
	
	break;
	
	
	
	
	case'posting':
		$str = "update ".$dbname.".".$table." set 
				posting='1',postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'deleteht':
		$str = "delete from ".$dbname.".".$table." where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
		
	case 'insert':
		
		$notransaksi = generatebakoreksistok();	
	// exit("Error".$notransaksi._.$waktu);
		$data = array(
			'notransaksi'=>$notransaksi,
			'unit'=>$unit,
			'kodept'=>$kodept,
			'tipe'=>$tipe,
			'kodebarang'=>$kodebarang,
			'kodetangki'=>$kodetangki,
			'tanggal'=>$waktu,
			'jumlah'=>$jumlah,
			'keterangan'=>$keteranganht,
			'createby' => $_SESSION['standard']['userid'],
			'createtime' => date('Y-m-d H:i'),
			'updateby' => $_SESSION['standard']['userid']
		);
		// exit("Error:$data");
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols); 	
		// exit("Error:$str");

		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
		
		echo $notransaksi;

	break;
	
	

   case'loaddata':
	
		// $unit=$_SESSION['empl']['lokasitugas'];
		// namauser	kodeorganisasi
	   
		// $where=" unit in (select kodeorganisasi from ".$dbname.".user_orgdetail where namauser='".$_SESSION['standard']['username']."')";
		
		if($unitsch!=''){
			$where.=" and unit='".$unitsch."'";
		}
		if($kodeptsch!=''){
			$where.=" and kodept='".$kodeptsch."'";
		}
		if($tipesch!=''){
			$where.=" and tipe='".$tipesch."'";
		}
		if($kodebarangsch!=''){
			$where.=" and kodebarang='".$kodebarangsch."'";
		}
		if($kodetangkisch!=''){
			$where.=" and kodetangki='".$kodetangkisch."'";
		}
		
		// print_r($_SESSION);
	   
		if($tanggalselesaisch!='' and $tanggalmulaisch!=''){
			$where.=" and tanggal between '".$tanggalmulaisch." 00:00:00' and '".$tanggalselesaisch." 23:59:59'";
		}

		if($notransaksisch!=''){
			$where.=" and notransaksi like '%".$notransaksisch."%'";
		}
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
	
		
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where 1=1 ".$where." group by notransaksi  ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jumrow = $bar['jumrow'];
		}
			
		$no = 0;
		$no=$maxdisplay;
		$str = "select * from ".$dbname.".".$table." where 1=1 ".$where."  order by tanggal desc limit " . $offset . "," . $limit . " ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['notransaksi']."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".$bar['tipe']."</td>";
				$tab.="<td>".$bar['unit']."</td>";
				$tab.="<td>".$bar['kodept']."</td>";
				$tab.="<td>".$bar['kodetangki']."</td>";
				$tab.="<td>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td align=right>".number_format($bar['jumlah'])."</td>";
				$tab.="<td>".$bar['keterangan']."</td>";
				$tab.="<td align=left>".getNamaKaryawan($bar['updateby'])."</td>";
				// $tab.="<td align=center>";
				if($bar['posting']==0){
					$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' 
						onclick=\"editht('".$bar['notransaksi']."');\"></td>";
					$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"deleteht('".$bar['notransaksi']."','".$bar['jenis']."');\"></td>";		
					$tab.="<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['notransaksi']."');\"></td>";							
				} else{
					$tab.="<td></td><td></td>";
					$tab.="<td align=center><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting'></td>";
				}
				$tab.="<td align=center><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print Document PDF ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."');\"></td>";	
				
				// $tab.="&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print Internal ".$bar['notransaksi']."' onclick=\"pdfinternal('".$bar['notransaksi']."');\">";	
				// $tab.="&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print External ".$bar['notransaksi']."' onclick=\"pdfexternal('".$bar['notransaksi']."');\">";	
				// $tab.="</td>";
			$tab.="</tr>";
        }
		
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where 1=1 ".$where."  group by notransaksi";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $bar = owlBaris($res);
        $totrows = ceil($bar / $limit);
		
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd = "</tr>
            <tr><td colspan=22 align=center>
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getpage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        echo $tab . "####" . $footd;
	break;
	
	case'geteditht':
	
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		// exit("Error:$str");
		echo $bar['notransaksi']."###".$bar['unit']."###".$bar['kodept']
		."###".$bar['tipe']."###".$bar['kodebarang']."###".$bar['kodetangki']
		."###".tanggalnormal(substr($bar['tanggal'],0,10))."###".substr($bar['tanggal'],11,2)."###".substr($bar['tanggal'],14,2)
		."###".$bar['jumlah']."###".$bar['keterangan'];
		// exit("Error:a");
	break;
	
	
	/********************** detail ***************************/
	
	case 'update':
		$str = "update ".$dbname.".".$table." set 
			kodept='".$kodept."',
			tipe='".$tipe."',
			kodebarang='".$kodebarang."',
			kodetangki='".$kodetangki."',
			jumlah='".$jumlah."',
			tanggal='".$waktu."',
			keterangan='".$keteranganht."',
			updateby='".$_SESSION['standard']['userid']."'
			where notransaksi = '".$notransaksi."'";
			// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
    default:
	break;
}
?>
