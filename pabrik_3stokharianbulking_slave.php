<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

include_once('lib/zPosting.php');
include_once('lib/zJournal.php');

$jumlahcpo=$jumlahpk=$tinggi=$suhu=0;
$method=checkPostGet('method','');
$unit=checkPostGet('unit','');
$tanggal       =tanggalsystemn(checkPostGet('tanggal',''));
$tipe=checkPostGet('tipe','');
$kodebarang=checkPostGet('kodebarang','');
$kodetangki=checkPostGet('kodetangki','');
$jumlah=checkPostGet('jumlah','');
$kodept=checkPostGet('kodept','');
$tinggi=checkPostGet('tinggi','');
$suhu=checkPostGet('suhu','');
$tanggalkemarin=tglkemarin($tanggal);

// echo $unit._.$tanggal._.$tipe._.$kodetangki._.$tanggalkemarin."<br>";



$karyawanid=checkPostGet('karyawanid','');

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$optpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$pt=$optpt[$unit];

$stream="";


switch($method){
	
	case'savept':
	
		$table='pabrik_stokbulking';
	
		$str = "delete from ".$dbname.".".$table." where  
			kodeunit='".$unit."' and kodept='".$kodept."' and tanggal='".$tanggal."' and kodebarang='".$kodebarang."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	
		$data = array(
			'kodeunit'=>$unit,
			'kodept'=>$kodept,
			'tanggal'=>$tanggal,
			'kodebarang'=>$kodebarang,
			'jumlah'=>$jumlah,
			'keterangan'=>'Otomatis Sistem',
			'createby' => $_SESSION['standard']['userid'],
			'createtime' => date('Y-m-d H:i')
		);
		
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols); 	
		// exit("Error:$str");

		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
	
	break;
	
	case'previewpt':
	
		$str="SELECT distinct(kodept) as kodept FROM ".$dbname.".pabrik_stokbulking where kodeunit='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrkodept[$bar['kodept']]=$bar['kodept'];
		}
	
		
		$str="SELECT * FROM ".$dbname.".pabrik_stokbulking where 
			kodeunit='".$unit."' and tanggal='".$tanggalkemarin."' and kodebarang='".$kodebarang."'";
		// exit("Error:".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$saldoawal[$bar['kodept']]=$bar['jumlah'];
			@$tsaldoawal+=$bar['jumlah'];
		}
		$stream.= "<table class=sortable ".$border." cellspacing=1 style=width:100%;>";
			$stream.= "<thead>";
				$stream.= "<tr class=rowcontent>";
					$stream.= "<td align=center rowspan=2>Keterangan</td>";
					$stream.= "<td align=center rowspan=2>Notransaksi</td>";
					$stream.= "<td align=center rowspan=2>Tipe<br>Transaksi</td>";
					$stream.= "<td align=center colspan=3>PT</td>";
					$stream.= "<td align=center rowspan=2>Total</td>";
				$stream.= "</tr>";
				$stream.= "<tr class=rowcontent>";
				foreach($arrkodept as $kdpt){
					$stream.= "<td align=center>".$kdpt."</td>";
				}
			$stream.= "</tr>";
			$stream.= "</thead>";
			$stream.= "<tr class=rowcontent>";
				$stream.= "<td colspan=3>Stok Awal</td>";
				foreach($arrkodept as $kdpt){
					$stream.= "<td align=right>".number_format($saldoawal[$kdpt])."</td>";
				}
				$stream.= "<td align=right>".number_format($tsaldoawal)."</td>";
			$stream.= "</tr>";
			
			$str="SELECT * FROM ".$dbname.".pabrik_transaksi_bulking_vw where 
				tanggal like '".$tanggal."%' and unit='".$unit."' and kodebarang='".$kodebarang."' order by tanggal asc";
				
				// echo $str;exit();
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$arrtipe[$bar['tipe']]=$bar['tipe'];
				if($bar['tipe']=='IN'){
					$bar['jumlah']=$bar['jumlah'];
					@$jumlahin[$bar['kodept']]+=$bar['jumlah'];
					@$tjumlahin+=$bar['jumlah'];
				}
				if($bar['tipe']=='OUT'){
					$bar['jumlah']=$bar['jumlah']*-1;
					@$jumlahout[$bar['kodept']]+=$bar['jumlah'];
					@$tjumlahout+=$bar['jumlah'];
				}
			}
			
			$stream.= "<tr class=rowcontent>";
				$stream.= "<td colspan=3>Transaksi Masuk</td>";
				foreach($arrkodept as $kdpt){
					$stream.= "<td align=right>".number_format($jumlahin[$kdpt])."</td>";
				}
				$stream.= "<td align=right>".@number_format($tjumlahin)."</td>";
			$stream.= "</tr>";
			
			$stream.= "<tr class=rowcontent>";
				$stream.= "<td colspan=3>Transaksi Keluar</td>";
				foreach($arrkodept as $kdpt){
					$stream.= "<td align=right>".number_format($jumlahout[$kdpt])."</td>";
				}
				$stream.= "<td align=right>".@number_format($tjumlahout)."</td>";
			$stream.= "</tr>";
					
			$stream.= "<tr class=rowcontent>";
				$stream.= "<td colspan=3>Saldo Akhir</td>";
				foreach($arrkodept as $kdpt){
					@$nourut+=1;
					@$jumlahtotal[$kdpt]+=($saldoawal[$kdpt]+$jumlahin[$kdpt]+$jumlahout[$kdpt]);
					$stream.= "<td align=right id=jumlahstok".$nourut.">".number_format($jumlahtotal[$kdpt])."</td>";
					$stream.= "<td align=right id=kodeptstok".$nourut." hidden>".$kdpt."</td>";
					$tjumlahtotal+=$jumlahtotal[$kdpt];
				}
				$stream.= "<td align=right>".number_format($tjumlahtotal)."</td>";
			$stream.= "</tr>";
			$stream.= "</table>";
		$stream.="<br><button class=mybutton onclick=savept(".$nourut.")>".$_SESSION['lang']['proses']."</button><br>";
		
		
		$stream.= "<br><br><table class=sortable ".$border." cellspacing=1 style=width:100%;>";
			$stream.= "<thead>";
				$stream.= "<tr class=rowcontent>";
					$stream.= "<td align=center>Tanggal</td>";
					$stream.= "<td align=center>PT</td>";
					$stream.= "<td align=center>Notransaksi</td>";
					$stream.= "<td align=center>Tipe<br>Transaksi</td>";
					$stream.= "<td align=center>Jumlah</td>";
				$stream.= "</tr>";
			$stream.= "</thead>";
			
			
			$str="SELECT * FROM ".$dbname.".pabrik_transaksi_bulking_vw where 
				tanggal like '".$tanggal."%' and unit='".$unit."' and kodebarang='".$kodebarang."' order by tanggal asc";
				
				// echo $str;exit();
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$stream.= "<tr class=rowcontent>";
					$stream.= "<td align=center>".$bar['tanggal']."</td>";
					$stream.= "<td align=center>".$bar['kodept']."</td>";
					$stream.= "<td align=center>".$bar['notransaksi']."</td>";
					$stream.= "<td align=center>".$bar['tipe']."</td>";
					$stream.= "<td align=right>".number_format($bar['jumlah'])."</td>";
				$stream.= "</tr>";
			}
			
		$stream.= "</table>";	
		
		echo $stream;
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	case'preview':
		
		
		/*
		$str="SELECT * FROM ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and kodetangki='".$kodetangki."'";
		// exit("Error:".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$komoditi=$bar['komoditi'];
		*/	
		
		$str="SELECT * FROM ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' order by komoditi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrkodetangki[$bar['kodetangki']]=$bar['kodetangki'];
			$komoditi[$bar['kodetangki']]=$bar['komoditi'];
		}
		
		// $stream.= "<table class=sortable border=0 cellspacing=0  style='height:330px;width:500px;overflow:auto'>";
		$stream.= "<div  style='height:330px;width:1130px;overflow:auto'>";
		// $stream.= "<tr class=rowcontent>";
		// $stream.= "<td>";
			$no=0;
			foreach($arrkodetangki as $kodetangki){
				$no++;
				if($komoditi[$kodetangki]=='CPO'){
					#= ambil saldo awal
					$str="SELECT * FROM ".$dbname.".pabrik_masukkeluartangki where 
						tanggal='".$tanggalkemarin."' and kodeorg='".$unit."' and kodetangki='".$kodetangki."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$tinggi=$bar['tinggi'];
						$suhu=$bar['suhu'];
						$jumlah2=$bar['kuantitas'];
					// $stream.= "<fieldset style=float:left><legend>Stok Mutasi CPO & PK</legend>";
					$stream.= "<table class=sortable ".$border." cellspacing=1>";
					$stream.= "<thead>";
						$stream.= "<tr class=rowcontent>";
							$stream.= "<td align=center rowspan=2>Keterangan</td>";
							$stream.= "<td align=center rowspan=2>Notransaksi</td>";
							$stream.= "<td align=center rowspan=2>Tipe<br>Transaksi</td>";
							$stream.= "<td align=center colspan=4>Tangki ".$kodetangki."</td>";
						$stream.= "</tr>";
						$stream.= "<tr class=rowcontent>";
							$stream.= "<td align=center>Tinggi</td>";
							$stream.= "<td align=center>Suhu</td>";
							$stream.= "<td align=center>Kg</td>";
							$stream.= "<td align=center>Kg Akhir</td>";
					$stream.= "</tr>";
					$stream.= "</thead>";
					$stream.= "<tr class=rowcontent>";
						$stream.= "<td colspan=3>Stok Awal</td>";
						$stream.= "<td>".number_format($tinggi,2)."</td>";
						$stream.= "<td>".number_format($suhu)."</td>";
						$stream.= "<td>".number_format($jumlah)."</td>";
						$stream.= "<td>".number_format($jumlah2)."</td>";
					$stream.= "</tr>";
					
					$str="SELECT * FROM ".$dbname.".pabrik_transaksi_bulking_vw where kodetangki='".$kodetangki."' 
					and tanggal like '".$tanggal."%' and unit='".$unit."' order by tanggal asc";
					// echo $str;
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$tgl=$bar['tanggal'];
						$notran=$bar['notransaksi'];
						$tinggi=$bar['tinggi'];
						$suhu=$bar['suhu'];
						$jumlah2=$bar['jumlah2'];
						$jumlah=$bar['jumlah'];
						$tipe=$bar['tipe'];
						$stream.= "<tr class=rowcontent>";
							$stream.= "<td>".$tgl."</td>";
							$stream.= "<td>".$notran."</td>";
							$stream.= "<td>".$tipe."</td>";
							$stream.= "<td>".number_format($tinggi,2)."</td>";
							$stream.= "<td>".number_format($suhu)."</td>";
							$stream.= "<td>".number_format($jumlah)."</td>";
							$stream.= "<td>".number_format($jumlah2)."</td>";
						$stream.= "</tr>";
					}
					$stream.= "<tr class=rowcontent>";
							$stream.= "<td colspan=3>Stok Akhir</td>";
							$stream.= "<td id=tinggistok".$no.">".number_format($tinggi,2)."</td>";
							$stream.= "<td id=suhustok".$no.">".number_format($suhu)."</td>";
							$stream.= "<td></td>";
							$stream.= "<td id=jumlahstok".$no.">".number_format($jumlah2)."</td>";
							$stream.= "<td hidden id=kodetangki".$no.">".$kodetangki."</td>";
						$stream.= "</tr>";
						
					$stream.= "</table><br>";
				}
				
				#=========================== KERNEL
				
				if($komoditi[$kodetangki]=='KER'){
					#= ambil saldo awal
					$str="SELECT * FROM ".$dbname.".pabrik_masukkeluartangki where 
						tanggal='".$tanggalkemarin."' and kodeorg='".$unit."' and kodetangki='".$kodetangki."'";
						// echo $str;
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$tinggi=$bar['tinggi'];
						$suhu=$bar['suhu'];
						$sisa=$bar['kernelquantity'];
					// $stream.= "<fieldset style=float:left><legend>Stok Mutasi CPO & PK</legend>"; style=width:100%;
					$stream.= "<table class=sortable ".$border." cellspacing=1>";
					$stream.= "<thead>";
						$stream.= "<tr class=rowcontent>";
							$stream.= "<td align=center rowspan=2>Keterangan</td>";
							$stream.= "<td align=center rowspan=2>Notransaksi</td>";
							$stream.= "<td align=center rowspan=2>Tipe<br>Transaksi</td>";
							$stream.= "<td align=center colspan=4>Tangki ".$kodetangki."</td>";
						$stream.= "</tr>";
						$stream.= "<tr class=rowcontent>";
							$stream.= "<td align=center>Tinggi</td>";
							$stream.= "<td align=center>Suhu</td>";
							$stream.= "<td align=center>Kg</td>";
							$stream.= "<td align=center>Kg Akhir</td>";
					$stream.= "</tr>";
					$stream.= "</thead>";
					$stream.= "<tr class=rowcontent>";
						$stream.= "<td colspan=3>Stok Awal</td>";
						$stream.= "<td>".$tinggi."</td>";
						$stream.= "<td>".number_format($suhu)."</td>";
						$stream.= "<td></td>";
						$stream.= "<td>".number_format($sisa)."</td>";
					$stream.= "</tr>";
					
					$str="SELECT * FROM ".$dbname.".pabrik_transaksi_bulking_vw where kodetangki='".$kodetangki."' 
					and tanggal like '".$tanggal."%' and unit='".$unit."' order by tanggal asc";
					// echo $str;
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch()){
						$tgl=$bar['tanggal'];
						$notran=$bar['notransaksi'];
						$tinggi=$bar['tinggi'];
						$suhu=$bar['suhu'];
						$jumlah2=$bar['jumlah2'];
						$jumlah=$bar['jumlah'];
						$tipe=$bar['tipe'];
						if($tipe=='OUT'){
							$jumlah=$jumlah*-1;
						}
						$sisa=$sisa+$jumlah;
						$stream.= "<tr class=rowcontent>";
							$stream.= "<td>".$tgl."</td>";
							$stream.= "<td>".$notran."</td>";
							$stream.= "<td>".$tipe."</td>";
							$stream.= "<td>".number_format($tinggi,2)."</td>";
							$stream.= "<td>".number_format($suhu)."</td>";
							$stream.= "<td>".number_format($jumlah)."</td>";
							$stream.= "<td>".number_format($sisa)."</td>";
						$stream.= "</tr>";
					}
					$stream.= "<tr class=rowcontent>";
							$stream.= "<td colspan=3>Stok Akhir</td>";
							$stream.= "<td id=tinggistok".$no.">".number_format($tinggi,2)."</td>";
							$stream.= "<td id=suhustok".$no.">".number_format($suhu)."</td>";
							$stream.= "<td></td>";
							$stream.= "<td id=jumlahstok".$no.">".number_format($sisa)."</td>";
							$stream.= "<td hidden id=kodetangki".$no.">".$kodetangki."</td>";
						$stream.= "</tr>";
						
					$stream.= "</table><br>";
					
				}
			}
		
		$stream.="<br><button class=mybutton onclick=save(".$no.")>".$_SESSION['lang']['proses']."</button><br>";
		
		$stream.= "</div>";
		// $stream.= "</td>";
		// $stream.= "</tr>";
		// $stream.= "</table>";
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_hpp_".$unit."_".$per;
			if(strlen($stream)>0){
                if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
								@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
                } else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
                }
                fclose($handle);
			}     
		} else {
			echo $stream;
		}
	
	break;
	
	
	case'save':
	
		$table='pabrik_masukkeluartangki';
		$str = "delete from ".$dbname.".".$table." where  kodeorg='".$unit."' and kodetangki='".$kodetangki."' and tanggal='".$tanggal."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		
		$str="SELECT * FROM ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and kodetangki='".$kodetangki."'";
		// exit("Error:".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$komoditi=$bar['komoditi'];
		
		if($komoditi=='CPO'){
			$jumlahcpo=$jumlah;
		}else{
			$jumlahpk=$jumlah;
		}
		
		$data = array(
			'kodeorg'=>$unit,
			'tanggal'=>$tanggal,
			'kodetangki'=>$kodetangki,
			'kuantitas'=>$jumlahcpo,
			'suhu'=>$suhu,
			'tinggi'=>$tinggi,
			'kernelquantity'=>$jumlahpk
		);
		
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols); 	
		// exit("Error:$str");

		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
	
	break;
	
	
		
	default:
	break;
}



?>