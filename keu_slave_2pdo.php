<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
?>

<link rel=stylesheet type=text/css href=style/generic.css>	

<?php

$method = checkPostGet('method', '');

$nopdo = checkPostGet('nopdo', '');
$unit = checkPostGet('unit', '');
$per = checkPostGet('per', '');
$jenis = checkPostGet('jenis', '');

$tipe = checkPostGet('tipe', '');
$spk = checkPostGet('spk', '');

$arrtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$arrcompgaji=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$arrnmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$arrnmakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$arrnmsupp=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$arrnmkeg=  makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');



$perkemarin=periodelalu($per);


switch ($method) 
{
	
	
	case'REKAP':
		$stream="";
		$stream.="";
		if($tipe!='excel')
		{
			$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
		}
		$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$stream.="<b>Laporan PDO</b><br>";
		$stream.="<b>".$unit." - ".$nmorg[$unit]."</b><br>";
		$stream.="<b>Periode ".$per."</b><br><br>";
		$stream.="<b> REKAPITULASI PERMINTAAN DANA OPERASIONAL </b>";
		$stream.="<br>";
		$stream.="<b> NO : ".$nopdo." </b>";
		$stream.="<br>";
		$stream.="<br>";
		$stream.="<br>";
		
		if($tipe=='excel')
		{
			$border=" border=1";
		}
		else
		{
			$border=" border=0";
		}
		$stream.="
                <table cellpading=1 cellspacing=1 ".$border." class=sortable>
				
                <thead>
                    <tr class=rowheader>
                        <td align=center>".$_SESSION['lang']['nourut']."</td>    
                        <td align=center colspan=3>".$_SESSION['lang']['jenisbiaya']."</td>  
                        <td align=center> ".$_SESSION['lang']['realisasi']." ".$_SESSION['lang']['blnlalu']."</td>        
                        <td align=center>".$_SESSION['lang']['realisasi']." + ".$_SESSION['lang']['estimasi']."  ".$_SESSION['lang']['blnini']."</td>    
                        <td align=center> ".$_SESSION['lang']['estimasi']." Next Month</td>
                        <td align=center>".$_SESSION['lang']['selisih']."</td>
                    </tr>
                </thead>";
			
		
			
			 
		##data gaji bulan lalu
		$str="select * from ".$dbname.".sdm_gaji where kodeorg='".$unit."' and periodegaji='".$perkemarin."'
				and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$komponengaji[$bar['idkomponen']]=$bar['idkomponen'];
			@$gajilalu[$bar['idkomponen']]+=$bar['jumlah'];
			@$totgajilalu+=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".keu_pdo_vw where  kodeorg='".$unit."' and periode='".$per."' and tipepdo = 'UPAH'
				and komponengaji in (select id from ".$dbname.".sdm_ho_component where plus=1) ";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$komponengaji[$bar['komponengaji']]=$bar['komponengaji'];
			@$gajies[$bar['komponengaji']]+=$bar['rupiah'];
			@$totgajies+=$bar['rupiah'];
		}
		
		@$totselisihgaji=abs($totgajies-$totgajilalu);
		$stream.="
			<tr class=rowcontent>
				<td><b>1</b></td>
				<td colspan=3><b>".$_SESSION['lang']['gaji']." / ".$_SESSION['lang']['upah']."</b></td>
				<td align=right><b>".@number_format($totgajilalu)."</b></td>
				<td align=right><b>".@number_format($totgajies)."</b></td>
				<td></td>
				<td align=right><b>".@number_format($totselisihgaji)."</b></td>
			</tr>";	
			
		foreach($komponengaji as $kompgaji)
		{
			@$selisih[$kompgaji]=abs($gajies[$kompgaji]-$gajilalu[$kompgaji]);
			$stream.="
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td></td>
					<td>".$arrcompgaji[$kompgaji]."</td>
					<td  align=right>".@number_format($gajilalu[$kompgaji])."</td>
					<td  align=right>".@number_format($gajies[$kompgaji])."</td>
					<td></td>
					<td  align=right>".@number_format($selisih[$kompgaji])."</td>
				</tr>";
		}
		
		
		$str="select * from ".$dbname.".keu_pdo_vw where  kodeorg='".$unit."' and periode='".$per."' and tipepdo = 'BAPP'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kegiatan[$bar['kegiatan']]=$bar['kegiatan'];
			@$bapp[$bar['kegiatan']]+=$bar['rupiah'];
			@$totbapp+=$bar['rupiah'];
		}
		
		
		$str="select * from ".$dbname.".keu_pdo_vw where  kodeorg='".$unit."' and periode='".$per."' and tipepdo = 'SPK'";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kegiatan[$bar['kegiatan']]=$bar['kegiatan'];
			@$spk[$bar['kegiatan']]+=$bar['rupiah'];
			@$totspk+=$bar['rupiah'];
		}
			
				
		@$totselisibaspk=abs($totbapp-$totspk);
		$stream.="
			<tr class=rowcontent>
				<td><b>2</b></td>
				<td colspan=3><b>".$_SESSION['lang']['spk']." - ".$_SESSION['lang']['borongpanen']."</b></td>
				<td></td>
				<td align=right><b>".@number_format($totbapp)."</b></td>
				<td align=right><b>".@number_format($totspk)."</b></td>
				<td align=right><b>".@number_format($totselisibaspk)."</b></td>
			</tr>";
		if(!empty($kegiatan))
		{	
			foreach($kegiatan as $keg)
			{
				@$selisih[$keg]=abs($bapp[$keg]-$spk[$keg]);
				$stream.="
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td></td>
					<td>".$arrnmkeg[$keg]."</td>
					<td></td>
					<td  align=right>".@number_format($bapp[$keg])."</td>
					<td  align=right>".@number_format($spk[$keg])."</td>
					<td  align=right>".@number_format($selisih[$keg])."</td>
				</tr>";			
			}
		}

		
		$str="select * from ".$dbname.".keu_pdo_vw where  kodeorg='".$unit."' and periode='".$per."' and tipepdo = 'KAS'";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$nomorakunkas[$bar['noakun']]=$bar['noakun'];
			@$jumkas[$bar['noakun']]+=$bar['rupiah'];
			@$totjumkas+=$bar['rupiah'];
			
		}
		
		$stream.="
			<tr class=rowcontent>
				<td><b>3</b></td>
				<td colspan=3><b>".$_SESSION['lang']['pembayaran']." ".$_SESSION['lang']['kas']."</b></td>
				<td align=right></td>
				<td align=right></td>
				<td align=right><b>".@number_format($totjumkas)."</b></td>
				<td align=right><b>".@number_format($totjumkas)."</b></td>
			</tr>";	
		if(!empty($nomorakunkas))
		{	
			foreach($nomorakunkas as $akunkas)
			{
				@$selisih[$keg]=$bapp[$keg]-$spk[$keg];
				$stream.="
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td></td>
					<td>".$arrnmakun[$akunkas]."</td>
					<td></td>
					<td></td>
					<td  align=right>".@number_format($jumkas[$akunkas])."</td>
					<td  align=right>".@number_format($jumkas[$akunkas])."</td>
				</tr>";			
			}
		}

		$str="select * from ".$dbname.".keu_pdo_vw where  kodeorg='".$unit."' and periode='".$per."' and tipepdo = 'HUTANG'";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kdsup[$bar['kodesupplier']]=$bar['kodesupplier'];
			$nomorpo[$bar['nodocument']]=$bar['nodocument'];
			$listnopo[$bar['kodesupplier']][$bar['nodocument']]=$bar['nodocument'];
			@$rppo[$bar['kodesupplier']][$bar['nodocument']]+=$bar['rupiah'];
			@$totpersup[$bar['kodesupplier']]+=$bar['rupiah'];
			@$tothutang+=$bar['rupiah'];
		}
		
	
		$stream.="
			<tr class=rowcontent>
				<td><b>4</b></td>
				<td colspan=3><b>".$_SESSION['lang']['hutang']."  Supplier</b></td>
				<td align=right></td>
				<td align=right><b>".@number_format($tothutang)."</b></td>
				<td align=right></td>
				<td align=right><b>".@number_format($tothutang)."</b></td>
			</tr>";	
		if(!empty($kdsup))
		{	
			foreach($kdsup as $sup)
			{
				$stream.="
					<tr class=rowcontent>
						<td></td>
						<td></td>
						<td>".$arrnmsupp[$sup]."</td>
						<td></td>
						<td></td>
						<td align=right>".@number_format($totpersup[$sup])."</td>
						<td></td>
						<td align=right>".@number_format($totpersup[$sup])."</td>
					</tr>";	
				
				foreach($nomorpo as $nopo)
				{
					if($listnopo[$sup][$nopo]!='')
					{
						$stream.="
						<tr class=rowcontent>
							<td></td>
							<td></td>
							<td></td>
							<td>".$nopo."</td>
							<td></td>
							<td align=right>".@number_format($rppo[$sup][$nopo])."</td>
							<td></td>
							<td align=right>".@number_format($rppo[$sup][$nopo])."</td>
							
						</tr>";	
					}
				}
							
			}
		}
		


		$str="select * from ".$dbname.".keu_pdo_vw where  kodeorg='".$unit."' and periode='".$per."' and tipepdo = 'PAD'";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$nomorakunpad[$bar['noakun']]=$bar['noakun'];
			@$jumpad[$bar['noakun']]+=$bar['rupiah'];
			@$totjumpad+=$bar['rupiah'];
			
		}
		
		$stream.="
			<tr class=rowcontent>
				<td><b>5</b></td>
				<td colspan=3><b>Public Affair Dept.</b></td>
				<td align=right></td>
				<td align=right></td>
				<td align=right><b>".@number_format($totjumpad)."</b></td>
				<td align=right><b>".@number_format($totjumpad)."</b></td>
			</tr>";	
		if(!empty($nomorakunpad))
		{	
			foreach($nomorakunpad as $pad)
			{
				@$selisih[$pad]=$bapp[$pad]-$spk[$pad];
				$stream.="
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td></td>
					<td>".$arrnmakun[$pad]."</td>
					<td></td>
					<td></td>
					<td  align=right>".@number_format($jumpad[$pad])."</td>
					<td  align=right>".@number_format($jumpad[$pad])."</td>
				</tr>";			
			}
		}

		
		$totalbl=$totgajilalu;
		@$totalbi=$totgajies+$totbapp+$tothutang;
		@$totalbd=$totspk+$totjumkas+$totjumpad;
		//$totalselisih=$totselisihgaji+$totselisibaspk+$totjumkas+$tothutang+$totjumpad;
		@$totalselisih=abs($totalbi-$totalbl);	
			
		$stream.="
			<tr class=rowcontent>
				
				<td colspan=4><b>GRAND TOTAL</b></td>
				<td align=right><b>".@number_format($totalbl)."</b></td>
				<td align=right><b>".@number_format($totalbi)."</b></td>
				<td align=right><b>".@number_format($totalbd)."</b></td>
				<td align=right><b>".@number_format($totalselisih)."</b></td>
			</tr>";	
			
			
		
		if($tipe=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
			$nop_ = "excel_pdo_rekap" . $unit;
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
        }
		}
		
		
	
	break;
	
	
	case'detail':
	
	
		#bl
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$spk."' and tanggal < '".$per."-01'  ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$keg[$bar['kodeblok']]=$bar['kodekegiatan'];
			$volbl[$bar['kodeblok']]+=$bar['hasilkerjarealisasi'];
			$rpbl[$bar['kodeblok']]+=$bar['jumlahrealisasi'];
		}
		
		#bi
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$spk."' and tanggal like '".$per."%'  ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$keg[$bar['kodeblok']]=$bar['kodekegiatan'];
			$volbi[$bar['kodeblok']]+=$bar['hasilkerjarealisasi'];
			$rpbi[$bar['kodeblok']]+=$bar['jumlahrealisasi'];
		}
		
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$spk."'  ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$totvol[$bar['kodeblok']]+=$bar['hasilkerjarealisasi'];
			$totrp[$bar['kodeblok']]+=$bar['jumlahrealisasi'];
		}
		
		
		$str="select a.*,b.tanggal,b.kodeorg,b.divisi,b.koderekanan,b.keterangan,b.dari,b.sampai
				from ".$dbname.".log_spkdt a left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi
				where a.notransaksi='".$spk."' ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$sat[$bar['kodeblok']]=$bar['satuan'];
			$vol[$bar['kodeblok']]+=$bar['hasilkerjajumlah'];
			$rpsat[$bar['kodeblok']]+=$bar['rupiahpersatuan'];
			$rp[$bar['kodeblok']]+=$bar['jumlahrp'];
			$divisi=$bar['divisi'];
			$tglmulai=$bar['dari'];
			$tglsampai=$bar['sampai'];
			$kontraktor=$bar['koderekanan'];
		}	
	
	
		$stream="";
		
		if($tipe=='excel')
		{$border=" border=1";}
		else
		{$border=" border=0";}
	
		
		$stream.=" ".$_SESSION['lang']['divisi']." : ".$divisi." ";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['nospk']." : ".$spk." ";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['periode']." : ".tanggalnormal($tglmulai)." s/d ".tanggalnormal($tglsampai)." ";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['kontraktor']." : ".$arrnmsupp[$kontraktor]." ";
		$stream.="<br>";

		$stream.="
			<table cellpading=1 cellspacing=1 ".$border." class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center rowspan=3>".$_SESSION['lang']['nourut']."</td>    
					<td align=center rowspan=3>".$_SESSION['lang']['kodeblok']."</td> 
					<td align=center rowspan=3>".$_SESSION['lang']['kegiatan']."</td> 	
					<td align=center rowspan=3>".$_SESSION['lang']['satuan']."</td>  
					<td align=center colspan=3>".$_SESSION['lang']['nilaikontrak']."</td>  
					<td align=center colspan=6>".$_SESSION['lang']['tagihan']."</td>    
				</tr>
				<tr>
					<td align=center rowspan=2>".$_SESSION['lang']['volume']."</td>    
					<td align=center rowspan=2>".$_SESSION['lang']['hargasatuan']."</td>    
					<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>    
					<td align=center colspan=2>s/d Bulan Lalu</td>    
					<td align=center colspan=2>Bulan Ini</td>    
					<td align=center colspan=2>s/d Bulan Ini</td>    
					
				</tr>
				<tr>
					<td align=center>".$_SESSION['lang']['volume']."</td>    
					<td align=center>".$_SESSION['lang']['jumlah']."</td>    
					
					<td align=center>".$_SESSION['lang']['volume']."</td>    
					<td align=center>".$_SESSION['lang']['jumlah']."</td>    
					<td align=center>".$_SESSION['lang']['volume']."</td>    
					<td align=center>".$_SESSION['lang']['jumlah']."</td>    
				</tr>
				
			</thead>";
		
		
		#bl
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$spk."' and tanggal < '".$per."-01'  ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$keg[$bar['kodeblok']]=$bar['kodekegiatan'];
			$volbl[$bar['kodeblok']]+=$bar['hasilkerjarealisasi'];
			$rpbl[$bar['kodeblok']]+=$bar['jumlahrealisasi'];
		}
		
		#bi
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$spk."' and tanggal like '".$per."%'  ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$keg[$bar['kodeblok']]=$bar['kodekegiatan'];
			$volbi[$bar['kodeblok']]+=$bar['hasilkerjarealisasi'];
			$rpbi[$bar['kodeblok']]+=$bar['jumlahrealisasi'];
		}
		
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$spk."'  ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$totvol[$bar['kodeblok']]+=$bar['hasilkerjarealisasi'];
			$totrp[$bar['kodeblok']]+=$bar['jumlahrealisasi'];
		}
		
		
		$str="select a.*,b.tanggal,b.kodeorg,b.divisi,b.koderekanan,b.keterangan,b.dari,b.sampai
				from ".$dbname.".log_spkdt a left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi
				where a.notransaksi='".$spk."' ";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$sat[$bar['kodeblok']]=$bar['satuan'];
			$vol[$bar['kodeblok']]+=$bar['hasilkerjajumlah'];
			$rpsat[$bar['kodeblok']]+=$bar['rupiahpersatuan'];
			$rp[$bar['kodeblok']]+=$bar['jumlahrp'];
		}	

		
		foreach($kdblok as $blok)
		{
			$no+=1;
			$stream.="
				<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=left>".$blok."</td>
					<td align=left>".$arrnmkeg[$keg[$blok]]."</td>
					<td align=left>".$sat[$blok]."</td>
					
					<td align=right>".@number_format($vol[$blok])."</td>
					<td align=right>".@number_format($rpsat[$blok])."</td>
					<td align=right>".@number_format($rp[$blok])."</td>
					
					<td align=right>".@number_format($volbl[$blok])."</td>
					<td align=right>".@number_format($rpbl[$blok])."</td>
					
					<td align=right>".@number_format($volbi[$blok])."</td>
					<td align=right>".@number_format($rpbi[$blok])."</td>
					
					<td align=right>".@number_format($totvol[$blok])."</td>
					<td align=right>".@number_format($totrp[$blok])."</td>
					";
					
					
					$tvol+=$vol[$blok];
					$trpsat+=$rpsat[$blok];
					$trp+=$rp[$blok];
					
					$tvolbl+=$volbl[$blok];
					$trpbl+=$rpbl[$blok];
					
					$tvolbi+=$volbi[$blok];
					$trpbi+=$rpbi[$blok];
					
					$ttotvol+=$totvol[$blok];
					$ttotrp+=$totrp[$blok];
					
		}
			$stream.="
				<tr class=rowcontent>
					<td align=center colspan=4>Grand Total</td>
					
					<td align=right>".@number_format($tvol)."</td>
					<td align=right>".@number_format($trpsat)."</td>
					<td align=right>".@number_format($trp)."</td>
					
					<td align=right>".@number_format($tvolbl)."</td>
					<td align=right>".@number_format($trpbl)."</td>
					
					<td align=right>".@number_format($tvolbi)."</td>
					<td align=right>".@number_format($trpbi)."</td>
					
					<td align=right>".@number_format($ttotvol)."</td>
					<td align=right>".@number_format($ttotrp)."</td>
					";

		
		if($tipe=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
				$nop_ = "excel_pdo_".$jenis."_".$unit."_".$per;
				if (strlen($stream) > 0) 
				{
					if ($handle = opendir('tempExcel')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
								@unlink('tempExcel/' . $file);
							}
						}
						closedir($handle);
					}
					$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
					if (!fwrite($handle, $stream)) {
						echo "<script language=javascript1.2>
								parent.window.alert('Can't convert to excel format');
								</script>";
						exit;
					} else {
						echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
					}
					fclose($handle);
				}
		}
		
	
	break;
	
	
	
	case'BAPP':
		
		$stream="";
		//$stream.="<br>";$stream.="<br>";
		$stream.="PERINCIAN RENCANA PEMBAYARAN KONTRAKTOR";
		$stream.="<br>";
		$stream.=" ".$per." ";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['unit']." : ".$unit." ";
		$stream.="<br>";
		if($tipe=='excel')
		{$border=" border=1";}
		else
		{$border=" border=0";}

		$stream.="
				<table cellpading=1 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=3>".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=3>".$_SESSION['lang']['divisi']."</td>    
						<td align=center rowspan=3>".$_SESSION['lang']['nospk']."</td>  
						<td align=center rowspan=3>".$_SESSION['lang']['kontraktor']."</td>    
						<td align=center rowspan=3>".$_SESSION['lang']['periode']."</td>    
						<td align=center rowspan=3>".$_SESSION['lang']['kodekegiatan']."</td>   
						<td align=center rowspan=3>".$_SESSION['lang']['namakegiatan']."</td>   
						<td align=center rowspan=3>".$_SESSION['lang']['satuan']."</td> 
						<td align=center colspan=3>".$_SESSION['lang']['nilaikontrak']."</td>    
						<td align=center colspan=8>".$_SESSION['lang']['tagihan']."</td>    
					</tr>
					<tr>
						<td align=center rowspan=2>".$_SESSION['lang']['volume']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['hargasatuan']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>    
						<td align=center colspan=2>s/d Bulan Lalu</td>    
						<td align=center colspan=3>Bulan Ini</td>    
						<td align=center colspan=2>s/d Bulan Ini</td>    
						<td align=center rowspan=2>%</td> 
					</tr>
					<tr>
						<td align=center>".$_SESSION['lang']['volume']."</td>    
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
						<td align=center>BAPP Ke</td>    
						<td align=center>".$_SESSION['lang']['volume']."</td>    
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
						<td align=center>".$_SESSION['lang']['volume']."</td>    
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
					</tr>
					
				</thead>";
		

		$str="select * from ".$dbname.".keu_pdo_vw where kodeorg='".$unit."' and periode='".$per."' and tipepdo='".$jenis."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$nospk[$bar['nodocument']]=$bar['nodocument'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listnospk[$bar['divisi']][$bar['nodocument']]=$bar['nodocument'];
			$sup[$bar['divisi']][$bar['nodocument']]=$bar['kodesupplier'];
			
			@$rpbi[$bar['divisi']][$bar['nodocument']]+=$bar['rupiah'];
			@$volbi[$bar['divisi']][$bar['nodocument']]+=$bar['fisik'];
			$sup[$bar['divisi']][$bar['nodocument']]=$bar['kodesupplier'];
		}
				
				
				
		$str="select a.*,b.tanggal,b.kodeorg,b.divisi,b.koderekanan,b.keterangan,b.dari,b.sampai
				from ".$dbname.".log_spkdt a left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi
				where b.kodeorg='".$unit."'";	

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$tglmulai[$bar['divisi']][$bar['notransaksi']]=$bar['dari'];
			$tglsampai[$bar['divisi']][$bar['notransaksi']]=$bar['sampai'];
			$keg[$bar['divisi']][$bar['notransaksi']]=$bar['kodekegiatan'];
			$sat[$bar['divisi']][$bar['notransaksi']]=$bar['satuan'];
			$sat[$bar['divisi']][$bar['notransaksi']]=$bar['satuan'];
			
			@$vol[$bar['divisi']][$bar['notransaksi']]+=$bar['hasilkerjajumlah'];
			@$rpsat[$bar['divisi']][$bar['notransaksi']]+=$bar['rupiahpersatuan'];
			@$rp[$bar['divisi']][$bar['notransaksi']]+=$bar['jumlahrp'];
		}	



		$str=" select * from ".$dbname.".log_baspk where kodeblok like '".$unit."%' and tanggal < '".$per."-01'  ";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())		
		{
			@$volbl[substr($bar['kodeblok'],0,6)][$bar['notransaksi']]+=$bar['hasilkerjarealisasi'];
			@$rpbl[substr($bar['kodeblok'],0,6)][$bar['notransaksi']]+=$bar['jumlahrealisasi'];
		}

		if(!empty($kddivisi))
		{
			foreach($kddivisi as $divisi)
			{
				foreach($nospk as $spk)
				{
					if(@$listnospk[$divisi][$spk]!='')
					{
						
						@$totvol[$divisi][$spk]=$volbl[$divisi][$spk]+$volbi[$divisi][$spk];
						@$totrp[$divisi][$spk]=$rpbl[$divisi][$spk]+$rpbi[$divisi][$spk];
						@$persen[$divisi][$spk]=$totrp[$divisi][$spk]/$rp[$divisi][$spk]*100;
						
						@$noisi+=1;
						$stream.="
							<tr class=rowcontent onclick=\"detail('".$spk."','".$per."','html','event');\">
								<td align=center>".$noisi."</td>
								<td align=left>".$divisi."</td>
								<td align=left>".$spk."</td>
								<td align=left>".$arrnmsupp[$sup[$divisi][$spk]]."</td>
								<td align=left>".tanggalnormal($tglmulai[$divisi][$spk])." s/d ".tanggalnormal($tglsampai[$divisi][$spk])."</td>
								<td align=left>".$keg[$divisi][$spk]."</td>
								<td align=left>".$arrnmkeg[$keg[$divisi][$spk]]."</td>
								<td align=left>".$sat[$divisi][$spk]."</td>
								
								<td align=right>".@number_format($vol[$divisi][$spk],2)."</td>
								<td align=right>".@number_format($rpsat[$divisi][$spk])."</td>
								<td align=right>".@number_format($rp[$divisi][$spk])."</td>
								
								<td align=right>".@number_format($volbl[$divisi][$spk],2)."</td>
								<td align=right>".@number_format($rpbl[$divisi][$spk])."</td>
								
								<td></td>
								<td align=right>".@number_format($volbi[$divisi][$spk],2)."</td>
								<td align=right>".@number_format($rpbi[$divisi][$spk])."</td>
								
								<td align=right>".@number_format($totvol[$divisi][$spk])."</td>
								<td align=right>".@number_format($totrp[$divisi][$spk])."</td>
								<td>".@number_format($persen[$divisi][$spk],2)."</td>
							</tr>";
							
						@$strp[$divisi]+=$rp[$divisi][$spk];
						@$strpbl[$divisi]+=$rpbl[$divisi][$spk];
						@$strpbi[$divisi]+=$rpbi[$divisi][$spk];
						@$sttotrp[$divisi]+=$totrp[$divisi][$spk];
					}
					
				}
				
				$stpersen[$divisi]=$sttotrp[$divisi]/$strp[$divisi]*100;
				$stream.="
					<tr bgcolor=#80FFFE>
						<td colspan=10>".$_SESSION['lang']['subtotal']."</td>
						<td align=right>".@number_format($strp[$divisi])."</td>
						<td></td>
						<td align=right>".@number_format($strpbl[$divisi])."</td>
						<td></td>
						<td></td>
						<td align=right>".@number_format($strpbi[$divisi])."</td>
						<td></td>
						<td align=right>".@number_format($sttotrp[$divisi])."</td>
						<td align=right>".@number_format($stpersen[$divisi],2)."</td>
					</tr>";
					@$gtrp+=$strp[$divisi];
					@$gtrpbl+=$strpbl[$divisi];
					@$gtrpbi+=$strpbi[$divisi];
					@$gttotrp+=$sttotrp[$divisi];
			}
		
		
			$gtpersen=$gttotrp/$gtrp*100;
			$stream.="
				 <tr bgcolor=#48D1CC>
					<td colspan=10>Grand Total</td>
					<td align=right>".@number_format($gtrp)."</td>
					<td></td>
					<td align=right>".@number_format($gtrpbl)."</td>
					<td></td>
					<td></td>
					<td align=right>".@number_format($gtrpbi)."</td>
					<td></td>
					<td align=right>".@number_format($gttotrp)."</td>
					<td align=right>".@number_format($gtpersen,2)."</td>
					
				</tr>";
		
		}
		
		$stream.="</table>";
		
		if($tipe=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
				$nop_ = "excel_pdo_".$jenis."_".$unit."_".$per;
				if (strlen($stream) > 0) 
				{
					if ($handle = opendir('tempExcel')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
								@unlink('tempExcel/' . $file);
							}
						}
						closedir($handle);
					}
					$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
					if (!fwrite($handle, $stream)) {
						echo "<script language=javascript1.2>
								parent.window.alert('Can't convert to excel format');
								</script>";
						exit;
					} else {
						echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
					}
					fclose($handle);
				}
		}
				
					
	
		
	break;
	
	case'PAD':
		$stream="";
		$stream.="REKAP PERMINTAAN DANA GRTT / CSR / PAD";
		$stream.="<br>";
		$stream.=" ".$per." ";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['unit']." : ".$unit." ";
		$stream.="<br>";
		if($tipe=='excel')
		{$border=" border=1";}
		else
		{$border=" border=0";}

		$stream.="
				<table cellpading=1 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['noakun']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['namaakun']."</td>  
						<td align=center rowspan=2>".$_SESSION['lang']['keterangan']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['satuan']."</td>    
						<td align=center colspan=3>Realisasi Bulan Lalu</td>    
						<td align=center colspan=3>Estimasi Bulan Ini</td>   
					</tr>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
						<td align=center>".$_SESSION['lang']['harga']."</td>    
						<td align=center>".$_SESSION['lang']['total']."</td>    
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
						<td align=center>".$_SESSION['lang']['harga']."</td>    
						<td align=center>".$_SESSION['lang']['total']."</td>    
					<tr>
					</tr>
				</thead>";
				
		$str="select * from ".$dbname.".keu_pdo_vw where kodeorg='".$unit."' and periode='".$per."' and tipepdo='".$jenis."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$noakun[$bar['noakun']]=$bar['noakun'];
			$nourut[$bar['nourut']]=$bar['nourut'];
			$rincian[$bar['noakun']][$bar['nourut']]=$bar['rincian'];
			$listnourut[$bar['noakun']][$bar['nourut']]=$bar['nourut'];
			$satuan[$bar['noakun']][$bar['nourut']]=$bar['satuan'];
						
			$fisik[$bar['noakun']][$bar['nourut']]=$bar['fisik'];
			$total[$bar['noakun']][$bar['nourut']]=$bar['rupiah'];
			
		}			

		if(!empty($noakun))
		{
			foreach($noakun as $akun)
			{
				@$no+=1;
				foreach($nourut as $urut)
				{
					if($listnourut[$akun][$urut]!='')
					{
						if(@$notampung==$no)
						{$noisi='';}
						else{$noisi=$no;}
						
						if(@$akuntampung==$akun)
						{$akunisi='';}
						else{$akunisi=$akun;}
						
						
						@$harga[$akun][$urut]=$total[$akun][$urut]/$fisik[$akun][$urut];
						$stream.="
						<tr class=rowcontent>
							<td align=center>".$noisi."</td>
							<td>".$akunisi."</td>
							<td>".$arrnmakun[$akunisi]."</td>
							<td>".$rincian[$akun][$urut]."</td>
							<td>".$satuan[$akun][$urut]."</td>
							<td></td>
							<td></td>
							<td></td>
							<td align=right>".@number_format($fisik[$akun][$urut])."</td>
							<td align=right>".@number_format($harga[$akun][$urut])."</td>
							<td align=right>".@number_format($total[$akun][$urut])."</td>
						";
						
						$notampung=$no;
						$akuntampung=$akun;
						
						@$sttotal[$akun]+=$total[$akun][$urut];
						
					}
				}
				$stream.="
					<tr bgcolor=#80FFFE>
						<td colspan=7>".$_SESSION['lang']['subtotal']."</td>
						<td></td>
						<td></td>
						<td></td>
						<td align=right>".$sttotal[$akun]."</td>
					";
					@$gttotal+=$sttotal[$akun];
			}
			$stream.="
					<tr bgcolor=#48D1CC>
						<td colspan=7>Grand Total</td>
						<td></td>
						<td></td>
						<td></td>
						<td align=right>".@number_format($gttotal)."</td>
						
					";	
		}
		$stream.="</table>";
		
		

		if($tipe=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
				$nop_ = "excel_pdo_".$jenis."_".$unit."_".$per;
				if (strlen($stream) > 0) 
				{
					if ($handle = opendir('tempExcel')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
								@unlink('tempExcel/' . $file);
							}
						}
						closedir($handle);
					}
					$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
					if (!fwrite($handle, $stream)) {
						echo "<script language=javascript1.2>
								parent.window.alert('Can't convert to excel format');
								</script>";
						exit;
					} else {
						echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
					}
					fclose($handle);
				}
		}
				
				
	break;
	
	
	case'HUTANG':
		$stream="";
		$stream.="PERINCIAN RENCANA PEMBAYARAN HUTANG SUPLIER";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['periode']." : ".$per." ";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['unit']." : ".$unit." ";
		$stream.="<br>";
		if($tipe=='excel')
		{$border=" border=1";}
		else
		{$border=" border=0";}

		$stream.="
				<table cellpading=1 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['kodesupplier']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['namasupplier']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['nopo']."</td>  
						<td align=center colspan=5>".$_SESSION['lang']['hutang']."</td>    
						<td align=center colspan=2>".$_SESSION['lang']['pembayaran']."</td>   
					</tr>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
						<td align=center>".$_SESSION['lang']['ppn']."</td>    
						<td align=center>PPh</td>   
						<td align=center>PBBKB</td>  	
						<td align=center>".$_SESSION['lang']['total']."</td>    
						
						<td align=center>".$_SESSION['lang']['terbayar']."</td>    
						<td align=center>".$_SESSION['lang']['sisa']."</td>     
						
					<tr>
					</tr>
				</thead>";
				
		$str="select * from ".$dbname.".keu_pdo_vw where kodeorg='".$unit."' and periode='".$per."' and tipepdo='".$jenis."'";	

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$nopo[$bar['nodocument']]=$bar['nodocument'];
			$kdsup[$bar['kodesupplier']]=$bar['kodesupplier'];
			$rp[$bar['kodesupplier']][$bar['nodocument']]=$bar['rupiah'];
			$listnopo[$bar['kodesupplier']][$bar['nodocument']]=$bar['nodocument'];
		}
		
		$str=" select * from ".$dbname.".log_poht where nopo like '%".$unit."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
        {
            $nilaipo[$bar['kodesupplier']][$bar['nopo']]=$bar['subtotal'];
            $totalpo[$bar['kodesupplier']][$bar['nopo']]=$bar['nilaipo'];
            $ppn[$bar['kodesupplier']][$bar['nopo']]=$bar['ppn'];
            $pph[$bar['kodesupplier']][$bar['nopo']]=$bar['pph'];
			$pbbkb[$bar['kodesupplier']][$bar['nopo']]=$bar['pbbkb'];
        }
		
		$str=" select a.nopo,b.noinvoice,b.nilaiinvoice,c.jumlah,(c.jumlah-b.nilaiinvoice) as selisih,b.kodesupplier 
				from ".$dbname.".log_po_terima_vw2 a "
                . " left join ".$dbname.".keu_tagihanht b on a.nopo=b.nopo "
                . " left join ".$dbname.".keu_kasbankdt c on b.noinvoice=c.keterangan1"
                . " where a.nopo like '%".$unit."%' and  ((c.jumlah-b.nilaiinvoice < 0) or (jumlah is NULL)) ";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);	
		while($bar=$res->fetch())	
        {
            $nilaikas[$bar['kodesupplier']][$bar['nopo']]=$bar['jumlah'];
        }

		
		if(!empty($kdsup))
		{
			foreach($kdsup as $sup)
			{
				foreach($nopo as $po)
				{
					if($listnopo[$sup][$po]!='')
					{
						@$noisi+=1;
						$stream.="
							<tr class=rowcontent>
								<td align=center>".$noisi."</td>
								<td align=left>".$sup."</td>
								<td align=left>".$arrnmsupp[$sup]."</td>
								<td>".$po."</td>
								
								<td align=right>".@number_format($nilaipo[$sup][$po])."</td>
								<td align=right>".@number_format($ppn[$sup][$po])."</td>
								<td align=right>".@number_format($pph[$sup][$po])."</td>
								<td align=right>".@number_format($pbbkb[$sup][$po])."</td>
								<td align=right>".@number_format($totalpo[$sup][$po])."</td>
								<td align=right>".@number_format($nilaikas[$sup][$po])."</td>
								<td align=right>".@number_format($rp[$sup][$po])."</td>
							</tr>
						";
						@$stnilaipo[$sup]+=$nilaipo[$sup][$po];
						@$stppn[$sup]	+=	$ppn[$sup][$po];
						@$stpph[$sup]	+=	$pph[$sup][$po];
						@$stpbbkb[$sup]+=		$pbbkb[$sup][$po];
						@$sttotalpo[$sup]+=	$totalpo[$sup][$po];
						@$stnilaikas[$sup]	+=	$nilaikas[$sup][$po];
						@$strp[$sup]+=		$rp[$sup][$po];
						
					}
				}
				$stream.="
					<tr bgcolor=#80FFFE>
						<td align=left colspan=4>".$_SESSION['lang']['subtotal']." ".$arrnmsupp[$sup]."</td>
						<td align=right>".@number_format($stnilaipo[$sup])."</td>
						<td align=right>".@number_format($stppn[$sup])."</td>
						<td align=right>".@number_format($stpph[$sup])."</td>
						<td align=right>".@number_format($stpbbkb[$sup])."</td>
						<td align=right>".@number_format($sttotalpo[$sup])."</td>
						<td align=right>".@number_format($stnilaikas[$sup])."</td>
						<td align=right>".@number_format($strp[$sup])."</td>
					</tr>
				";
						@$gtnilaipo+=$stnilaipo[$sup];
						@$gtppn+=$stppn[$sup];
						@$gtpph+=$stpph[$sup];
						@$gtpbbkb+=$stpbbkb[$sup];
						@$gttotalpo+=$sttotalpo[$sup];
						@$gtnilaikas+=$stnilaikas[$sup];
						@$gtrp+=$strp[$sup];
			}				
			$stream.="
					<tr bgcolor=#48D1CC>
						<td align=left colspan=4>Grand Total</td>
						<td align=right>".@number_format($gtnilaipo)."</td>
						<td align=right>".@number_format($gtppn)."</td>
						<td align=right>".@number_format($gtpph)."</td>
						<td align=right>".@number_format($gtpbbkb)."</td>
						<td align=right>".@number_format($gttotalpo)."</td>
						<td align=right>".@number_format($gtnilaikas)."</td>
						<td align=right>".@number_format($gtrp)."</td>
					</tr>
				";		
		}
				
				
	
		echo $stream;
	
	break;
	
	
	
	case'SPK':
		$stream="";
		$stream.="REKAP PENGAJUAN SPK";
		$stream.="<br>";
		$stream.=" ".$per." ";
		$stream.="<br>";
		$stream.="<br>";
		if($tipe=='excel')
		{$border=" border=1";}
		else
		{$border=" border=0";}

		$stream.="
				<table cellpading=1 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['divisi']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['nospk']."</td>  
						<td align=center rowspan=2>".$_SESSION['lang']['kontraktor']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['periode']."</td>   
						<td align=center rowspan=2>".$_SESSION['lang']['kodekegiatan']."</td>  		
						<td align=center rowspan=2>".$_SESSION['lang']['namakegiatan']."</td>  		
						<td align=center rowspan=2>".$_SESSION['lang']['satuan']."</td>  							
						<td align=center colspan=4>".$_SESSION['lang']['nilaikontrak']."</td>   
					</tr>
					<tr>
						<td align=center>".$_SESSION['lang']['blok']."</td>    
						<td align=center>".$_SESSION['lang']['volume']."</td>    
						<td align=center>".$_SESSION['lang']['hargasatuan']."</td>    
						<td align=center>".$_SESSION['lang']['total']."</td>   
					</tr>
				</thead>";
		$str="select * from ".$dbname.".keu_pdo_vw where kodeorg='".$unit."' and periode='".$per."' and tipepdo='".$jenis."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$nospk[$bar['nodocument']]=$bar['nodocument'];
			$kdsup[$bar['kodesupplier']]=$bar['kodesupplier'];
			$kdkeg[$bar['kegiatan']]=$bar['kegiatan'];
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			
			$listkdblok[$bar['divisi']][$bar['nodocument']][$bar['kodesupplier']][$bar['kegiatan']][$bar['kodeblok']]=$bar['kodeblok'];
			
			
			$fis[$bar['divisi']][$bar['nodocument']][$bar['kodesupplier']][$bar['kegiatan']][$bar['kodeblok']]=$bar['fisik'];
			$rp[$bar['divisi']][$bar['nodocument']][$bar['kodesupplier']][$bar['kegiatan']][$bar['kodeblok']]=$bar['rupiah'];
			$sat[$bar['divisi']][$bar['nodocument']][$bar['kodesupplier']][$bar['kegiatan']][$bar['kodeblok']]=$bar['satuan'];
			
			$tglmulai[$bar['divisi']][$bar['nodocument']][$bar['kodesupplier']][$bar['kegiatan']][$bar['kodeblok']]=$bar['tglmulai'];
			$tglsampai[$bar['divisi']][$bar['nodocument']][$bar['kodesupplier']][$bar['kegiatan']][$bar['kodeblok']]=$bar['tglsampai'];
			
			//[$bar['divisi']][$bar['nodocument']][$bar['kegiatan']][$bar['blok']]=$bar[''];
		}
				
			
		@array_multisort($kddivisi,SORT_ASC);
		@array_multisort($nospk,SORT_ASC);	
		@array_multisort($kdblok,SORT_ASC);			

		if(!empty($kddivisi))
		{			
			foreach($kddivisi as $divisi)
			{
				foreach($nospk as $spk)
				{
					foreach($kdsup as $sup)
					{
						foreach($kdkeg as $keg)
						{
							foreach($kdblok as $blok)
							{
								if($listkdblok[$divisi][$spk][$sup][$keg][$blok]!='')
								{
									@$noisi+=1;
									@$rpsat[$divisi][$spk][$sup][$keg][$blok]=$rp[$divisi][$spk][$sup][$keg][$blok]/$fis[$divisi][$spk][$sup][$keg][$blok];
									$stream.="
										<tr class=rowcontent>
											<td align=center>".$noisi."</td>
											<td align=center>".romawi(substr($divisi,5,1))."</td>
											<td>".$spk."</td>
											<td>".$sup."</td>
											<td>".tanggalnormal($tglmulai[$divisi][$spk][$sup][$keg][$blok])." s/d ".tanggalnormal($tglsampai[$divisi][$spk][$sup][$keg][$blok])."</td>
											<td align=right>".$keg."</td>
											<td>".$arrnmkeg[$keg]."</td>
											<td>".$sat[$divisi][$spk][$sup][$keg][$blok]."</td>
											<td>".$blok."</td>
											<td align=right>".@number_format($fis[$divisi][$spk][$sup][$keg][$blok],2)."</td>
											<td align=right>".@number_format($rpsat[$divisi][$spk][$sup][$keg][$blok])."</td>
											<td align=right>".@number_format($rp[$divisi][$spk][$sup][$keg][$blok])."</td>
										</tr>
									";
									@$strp[$divisi]+=$rp[$divisi][$spk][$sup][$keg][$blok];
								}
							}
						}
					}
				}
				
				$stream.="
					<tr bgcolor=#80FFFE>
						<td align=left colspan=11>".$_SESSION['lang']['subtotal']." ".romawi(substr($divisi,5,1))."</td>
						<td align=right>".@number_format($strp[$divisi])."</td>
					</tr>
				";
				@$gtrp+=$strp[$divisi];
			
			$stream.="
					<tr bgcolor=#48D1CC>
						<td align=left colspan=11>Grand Total</td>
						<td align=right>".@number_format($gtrp)."</td>
					</tr>
				";		
			}			
		}
		if($tipe=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
				$nop_ = "excel_pdo_".$jenis."_".$unit."_".$per;
				if (strlen($stream) > 0) 
				{
					if ($handle = opendir('tempExcel')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
								@unlink('tempExcel/' . $file);
							}
						}
						closedir($handle);
					}
					$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
					if (!fwrite($handle, $stream)) {
						echo "<script language=javascript1.2>
								parent.window.alert('Can't convert to excel format');
								</script>";
						exit;
					} else {
						echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
					}
					fclose($handle);
				}
		}
	
	break;

	case'KAS':
		$stream="";
		$stream.="PERINCIAN RENCANA PENGELUARAN TUNAI";
		$stream.="<br>";
		$stream.=" ".$per." ";
		$stream.="<br>";
		$stream.=" ".$_SESSION['lang']['unit']." : ".$unit." ";
		$stream.="<br>";
		if($tipe=='excel')
		{$border=" border=1";}
		else
		{$border=" border=0";}

		$stream.="
				<table cellpading=1 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['noakun']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['namaakun']."</td>  
						<td align=center rowspan=2>".$_SESSION['lang']['keterangan']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['satuan']."</td>    
						<td align=center colspan=3>Realisasi Bulan Lalu</td>    
						<td align=center colspan=3>Estimasi Bulan Ini</td>   
					</tr>
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
						<td align=center>".$_SESSION['lang']['harga']."</td>    
						<td align=center>".$_SESSION['lang']['total']."</td>    
						<td align=center>".$_SESSION['lang']['jumlah']."</td>    
						<td align=center>".$_SESSION['lang']['harga']."</td>    
						<td align=center>".$_SESSION['lang']['total']."</td>    
					<tr>
					</tr>
				</thead>";
				
		$str="select * from ".$dbname.".keu_pdo_vw where kodeorg='".$unit."' and periode='".$per."' and tipepdo='".$jenis."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$noakun[$bar['noakun']]=$bar['noakun'];
			$nourut[$bar['nourut']]=$bar['nourut'];
			$rincian[$bar['noakun']][$bar['nourut']]=$bar['rincian'];
			$listnourut[$bar['noakun']][$bar['nourut']]=$bar['nourut'];
			$satuan[$bar['noakun']][$bar['nourut']]=$bar['satuan'];
			
			
			$fisik[$bar['noakun']][$bar['nourut']]=$bar['fisik'];
			$total[$bar['noakun']][$bar['nourut']]=$bar['rupiah'];
			
		}			

		
		if(!empty($noakun))
		{
			foreach($noakun as $akun)
			{
				@$no+=1;
				foreach($nourut as $urut)
				{
					if(@$listnourut[$akun][$urut]!='')
					{
						if(@$notampung==$no)
						{$noisi='';}
						else{$noisi=$no;}
						
						if(@$akuntampung==$akun)
						{$akunisi='';}
						else{$akunisi=$akun;}
						
						
						$harga[$akun][$urut]=$total[$akun][$urut]/$fisik[$akun][$urut];
						$stream.="
						<tr class=rowcontent>
							<td align=center>".$noisi."</td>
							<td>".$akunisi."</td>
							<td>".$arrnmakun[$akunisi]."</td>
							<td>".$rincian[$akun][$urut]."</td>
							<td>".$satuan[$akun][$urut]."</td>
							<td></td>
							<td></td>
							<td></td>
							<td align=right>".@number_format($fisik[$akun][$urut])."</td>
							<td align=right>".@number_format($harga[$akun][$urut])."</td>
							<td align=right>".@number_format($total[$akun][$urut])."</td>
						";
						
						$notampung=$no;
						$akuntampung=$akun;
						
						@$sttotal[$akun]+=$total[$akun][$urut];
						
					}
				}
				$stream.="
					<tr bgcolor=#80FFFE>
						<td colspan=7>".$_SESSION['lang']['subtotal']."</td>
						<td></td>
						<td></td>
						<td></td>
						<td align=right>".$sttotal[$akun]."</td>
					";
					@$gttotal+=$sttotal[$akun];
			}
			$stream.="
					<tr bgcolor=#48D1CC>
						<td colspan=7>Grand Total</td>
						<td></td>
						<td></td>
						<td></td>
						<td align=right>".@number_format($gttotal)."</td>
						
					";	
		}
		$stream.="</table>";
		
		

		if($tipe=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
				$nop_ = "excel_pdo_".$jenis."_".$unit."_".$per;
				if (strlen($stream) > 0) 
				{
					if ($handle = opendir('tempExcel')) {
						while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
								@unlink('tempExcel/' . $file);
							}
						}
						closedir($handle);
					}
					$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
					if (!fwrite($handle, $stream)) {
						echo "<script language=javascript1.2>
								parent.window.alert('Can't convert to excel format');
								</script>";
						exit;
					} else {
						echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
					}
					fclose($handle);
				}
		}
				
				
	break;
	
	case'UPAH':
		$stream="";
		$stream.="PERINCIAN RENCANA PEMBAYARAN UPAH, PREMI DAN LEMBUR";
		$stream.="<br>";
		$stream.=" ".$per." ";
		$stream.="<br>";
		$stream.="<br>";
		if($tipe=='excel')
		{$border=" border=1";}
		else
		{$border=" border=0";}

		$stream.="
				<table cellpading=1 cellspacing=1 ".$border." class=sortable>
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=3>".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=3>".$_SESSION['lang']['divisi']."</td>    
						<td align=center rowspan=3>".$_SESSION['lang']['tipekaryawan']."</td>    
						<td align=center rowspan=3>".$_SESSION['lang']['komponenpayroll']."</td>    
						<td align=center colspan=3>Realisasi Bulan Lalu</td>    
						<td align=center colspan=6>Estimasi Bulan Ini</td>   
						<td align=center colspan=3>Selisih</td> 	
					</tr>
					<tr>
						<td align=center rowspan=2>".$_SESSION['lang']['orang']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['hk']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>    
						
						<td align=center rowspan=2>".$_SESSION['lang']['orang']."</td>    
						<td align=center colspan=2>Real sd Tgl 20</td>    
						<td align=center colspan=2>Est dr tgl 21 sd 31</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>    
						
						<td align=center rowspan=2>".$_SESSION['lang']['orang']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['hk']."</td>    
						<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>    
					</tr>
					<tr>
						<td align=center>".$_SESSION['lang']['hk']."</td>    
						<td align=center>".$_SESSION['lang']['rupiah']."</td>    
						<td align=center>".$_SESSION['lang']['hk']."</td>    
						<td align=center>".$_SESSION['lang']['rupiah']."</td>

					</tr>
				</thead>";
				
		##data orang
		$str="select distinct(karyawanid) as karyawanid,subbagian,tipekaryawan,kodeorg from ".$dbname.".sdm_gaji_vw where kodeorg='".$unit."' and periodegaji='".$perkemarin."'
				and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			if($bar['subbagian']=='')
			{
				$bar['subbagian']=$bar['kodeorg'];
			}
			@$orgperlalu[$bar['subbagian']][$bar['tipekaryawan']]['1']+=1;
		}
				
			
		##data gaji bulan lalu
		$str="select * from ".$dbname.".sdm_gaji_vw where kodeorg='".$unit."' and periodegaji='".$perkemarin."'
				and idkomponen in (select id from ".$dbname.".sdm_ho_component where plus=1) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			if($bar['subbagian']=='')
			{
				$bar['subbagian']=$bar['kodeorg'];
			}
			$kddivisi[$bar['subbagian']]=$bar['subbagian'];
			$komponengaji[$bar['idkomponen']]=$bar['idkomponen'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];	
			
			$listtipekaryawan[$bar['subbagian']][$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$listkomponengaji[$bar['subbagian']][$bar['tipekaryawan']][$bar['idkomponen']]=$bar['idkomponen'];		
			@$rpperlalu[$bar['subbagian']][$bar['tipekaryawan']][$bar['idkomponen']]+=$bar['jumlah'];
		}

		#data dari pdo
		$str="select * from ".$dbname.".keu_pdo_vw where kodeorg='".$unit."' and periode='".$per."' and tipepdo='".$jenis."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$komponengaji[$bar['komponengaji']]=$bar['komponengaji'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			
			$listtipekaryawan[$bar['divisi']][$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$listkomponengaji[$bar['divisi']][$bar['tipekaryawan']][$bar['komponengaji']]=$bar['komponengaji'];
			
			$orgperskrg[$bar['divisi']][$bar['tipekaryawan']][$bar['komponengaji']]=$bar['jumlahorang'];
			$hkperskrg[$bar['divisi']][$bar['tipekaryawan']][$bar['komponengaji']]=$bar['fisikreal'];
			$rpperskrg[$bar['divisi']][$bar['tipekaryawan']][$bar['komponengaji']]=$bar['rupiahreal'];
			
			
			$hkpertot[$bar['divisi']][$bar['tipekaryawan']][$bar['komponengaji']]=$bar['fisik'];
			$rppertot[$bar['divisi']][$bar['tipekaryawan']][$bar['komponengaji']]=$bar['rupiah'];
			
		}
		
		##ambil HK bulan lalu.
		#absensi
		#untuk selain phl tidak mengambil upahabsen
		$str="select * from ".$dbname.".sdm_absensidt_vw where tanggal like '".$perkemarin."%' and kodeorg like '".$unit."%' and absensi='H' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kddivisi[$bar['kodeorg']]=$bar['kodeorg'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
			$absenkar[$bar['kodeorg']][$bar['tipekaryawan']][$bar['karyawanid']][$bar['tanggal']]=1;
			
		}
		//kehadiran
		$str="select a.tanggal,a.unit,a.karyawanid,a.absensi,a.jhk,b.tipekaryawan,b.subbagian 
					from ".$dbname.".kebun_kehadiran_vw a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where a.unit='".$unit."' 
					and a.tanggal like '".$perkemarin."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kddivisi[$bar['subbagian']]=$bar['subbagian'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
			@$absenkar[$bar['subbagian']][$bar['tipekaryawan']][$bar['karyawanid']][$bar['tanggal']]+=$bar['jhk'];
		}
		//panen
		$str="select a.tanggal,a.unit,a.karyawanid,b.tipekaryawan,b.subbagian
					from ".$dbname.".kebun_prestasi_vw a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where a.unit='".$unit."' and a.tanggal like '".$perkemarin."%' and (a.upahkerja-a.upahpenalty>0) and a.upahkerja>0 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['subbagian']]=$bar['subbagian'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
			$absenkar[$bar['subbagian']][$bar['tipekaryawan']][$bar['karyawanid']][$bar['tanggal']]=1;
		}

		/*nikmandor,nikmandor1,nikasisten,keranimuat*/

		$str="select a.tanggal,a.nikasisten,a.kodeorg,b.subbagian,b.tipekaryawan
			  from ".$dbname.".kebun_aktifitas a left join ".$dbname.".datakaryawan b
			  on a.nikasisten=b.karyawanid
			  where a.kodeorg='".$unit."' and  a.tanggal like '".$perkemarin."%'   and a.nikasisten!=''
			  order by tanggal";   
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['subbagian']]=$bar['subbagian'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$karyawanid[$bar['nikasisten']]=$bar['nikasisten'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
			$absenkar[$bar['subbagian']][$bar['tipekaryawan']][$bar['nikasisten']][$bar['tanggal']]=1;
		}

		$str="select a.tanggal,a.nikmandor1,a.kodeorg,b.subbagian,b.tipekaryawan
			  from ".$dbname.".kebun_aktifitas a left join ".$dbname.".datakaryawan b
			  on a.nikmandor1=b.karyawanid
			  where a.kodeorg='".$unit."' and  a.tanggal like '".$perkemarin."%'   and a.nikmandor1!=''
			  order by tanggal";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kddivisi[$bar['subbagian']]=$bar['subbagian'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$karyawanid[$bar['nikmandor1']]=$bar['nikmandor1'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
			$absenkar[$bar['subbagian']][$bar['tipekaryawan']][$bar['nikmandor1']][$bar['tanggal']]=1;
		}
		$str="select a.tanggal,a.nikmandor,a.kodeorg,b.subbagian,b.tipekaryawan
			  from ".$dbname.".kebun_aktifitas a left join ".$dbname.".datakaryawan b
			  on a.nikmandor=b.karyawanid
			  where a.kodeorg='".$unit."' and  a.tanggal like '".$perkemarin."%'  and a.nikmandor!=''
			  order by tanggal";    
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['subbagian']]=$bar['subbagian'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$karyawanid[$bar['nikmandor']]=$bar['nikmandor'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
			$absenkar[$bar['subbagian']][$bar['tipekaryawan']][$bar['nikmandor']][$bar['tanggal']]=1;
		}

		$str="select a.tanggal,a.keranimuat,a.kodeorg,b.subbagian,b.tipekaryawan
			  from ".$dbname.".kebun_aktifitas a left join ".$dbname.".datakaryawan b
			  on a.keranimuat=b.karyawanid
			  where a.kodeorg='".$unit."' and  a.tanggal like '".$perkemarin."%'    and a.keranimuat!=''
			  order by tanggal";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())	
		{
			$kddivisi[$bar['subbagian']]=$bar['subbagian'];
			$tipekaryawan[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			$karyawanid[$bar['keranimuat']]=$bar['keranimuat'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
			$absenkar[$bar['subbagian']][$bar['tipekaryawan']][$bar['keranimuat']][$bar['tanggal']]=1;
		}
		
		//nampung array absen
		if(isset($kddivisi)){
			foreach($kddivisi as $divisi)
			{
				if(isset($tipekaryawan)){
					foreach($tipekaryawan as $tpkar)
					{
						if(isset($karyawanid)){
							foreach($karyawanid as $kar)
							{
								if(isset($tanggal)){
									foreach($tanggal as $tgl)
									{
										@$hkperlalu[$divisi][$tpkar]['1']+=$absenkar[$divisi][$tpkar][$kar][$tgl];
									}	
								}
							}
						}
					}
				}
			}
		}
		array_multisort($kddivisi,SORT_ASC);
		array_multisort($tipekaryawan,SORT_ASC);
		array_multisort($komponengaji,SORT_ASC);

		echo"<pre>";
		//print_r($rowspan);
		echo"</pre>";

		foreach($kddivisi as $divisi)
		{
			@$nourut+=1;
			foreach($tipekaryawan as $tpkar)
			{
				if(@$listtipekaryawan[$divisi][$tpkar]!='')
				{
					foreach($komponengaji as $komp)
					{
						if(@$listkomponengaji[$divisi][$tpkar][$komp]!='')
						{
							@$hkpertgh[$divisi][$tpkar][$komp]=$hkpertot[$divisi][$tpkar][$komp]-$hkperskrg[$divisi][$tpkar][$komp];
							@$rppertgh[$divisi][$tpkar][$komp]=$rppertot[$divisi][$tpkar][$komp]-$rpperskrg[$divisi][$tpkar][$komp];
							@$selorg[$divisi][$tpkar][$komp]=$orgperskrg[$divisi][$tpkar][$komp]-$orgperlalu[$divisi][$tpkar][$komp];
							@$selrp[$divisi][$tpkar][$komp]=$rppertot[$divisi][$tpkar][$komp]-$rpperlalu[$divisi][$tpkar][$komp];
							@$selhk[$divisi][$tpkar][$komp]=$hkperlalu[$divisi][$tpkar][$komp]-$hkperskrg[$divisi][$tpkar][$komp];
							
							if(@$notampung==$nourut)
							{$noisi='';}
							else{$noisi=$nourut;}
							if(@$divisitampung==$divisi)
							{$divisiisi='';}
							else{$divisiisi=$divisi;}
							if(@$tpkartampung==$tpkar)
							{$tpkarisi='';}
							else{$tpkarisi=$tpkar;}
							
							$stream.="
							<tr class=rowcontent>
								<td>".$noisi."</td>
								<td>".@$arrnmorg[$divisiisi]."</td>
								<td>".@$arrtipekar[$tpkarisi]."</td>
								<td>".@$arrcompgaji[$komp]."</td>
								<td align=right>".@number_format($orgperlalu[$divisi][$tpkar][$komp])."</td>
								<td align=right>".@number_format($hkperlalu[$divisi][$tpkar][$komp],2)."</td>
								<td align=right>".@number_format($rpperlalu[$divisi][$tpkar][$komp])."</td>
								
								<td align=right>".@number_format($orgperskrg[$divisi][$tpkar][$komp])."</td>
								<td align=right>".@number_format($hkperskrg[$divisi][$tpkar][$komp],2)."</td>
								<td align=right>".@number_format($rpperskrg[$divisi][$tpkar][$komp],2)."</td>
								
								<td align=right>".@number_format($hkpertgh[$divisi][$tpkar][$komp],2)."</td>
								<td align=right>".@number_format($rppertgh[$divisi][$tpkar][$komp],2)."</td>
								
								<td align=right>".@number_format($rppertot[$divisi][$tpkar][$komp],2)."</td>
								
								<td align=right>".@number_format($selorg[$divisi][$tpkar][$komp])."</td>
								<td align=right>".@number_format($selhk[$divisi][$tpkar][$komp],2)."</td>
								<td align=right>".@number_format($selrp[$divisi][$tpkar][$komp],2)."</td>
							</tr>";
							
							$notampung=$nourut;
							$divisitampung=$divisi;
							$tpkartampung=$tpkar;
							
							
							@$storgperlalu[$divisi]+=$orgperlalu[$divisi][$tpkar][$komp];
							@$sthkperlalu[$divisi]+=$hkperlalu[$divisi][$tpkar][$komp];
							@$strpperlalu[$divisi]+=$rpperlalu[$divisi][$tpkar][$komp];
							
							@$storgperskrg[$divisi]+=$orgperskrg[$divisi][$tpkar][$komp];
							@$sthkperskrg[$divisi]+=$hkperskrg[$divisi][$tpkar][$komp];
							@$strpperskrg[$divisi]+=$rpperskrg[$divisi][$tpkar][$komp];
							
							@$sthkpertgh[$divisi]+=$hkpertgh[$divisi][$tpkar][$komp];
							@$strppertgh[$divisi]+=	$rppertgh[$divisi][$tpkar][$komp];
							@$strppertot[$divisi]+=	$rppertot[$divisi][$tpkar][$komp];
							
						}
					}		
				}
			}
			@$stselorg[$divisi]=$storgperskrg[$divisi]-$storgperlalu[$divisi];
			@$stselrp[$divisi]=$strppertot[$divisi]-$strpperlalu[$divisi];
			@$stselhk[$divisi]=$sthkperlalu[$divisi]-$sthkperskrg[$divisi];
			
			$stream.="
				<tr bgcolor=#80FFFE>
					<td></td>
					<td colspan=3>".$_SESSION['lang']['subtotal']."</td>
					<td align=right>".@number_format($storgperlalu[$divisi])."</td>
					<td align=right>".@number_format($sthkperlalu[$divisi],2)."</td>
					<td align=right>".@number_format($strpperlalu[$divisi],2)."</td>
					
					<td align=right>".@number_format($storgperskrg[$divisi])."</td>
					<td align=right>".@number_format($sthkperskrg[$divisi],2)."</td>
					<td align=right>".@number_format($strpperskrg[$divisi],2)."</td>
				
					<td align=right>".@number_format($sthkpertgh[$divisi],2)."</td>
					<td align=right>".@number_format($strppertgh[$divisi],2)."</td>
					<td align=right>".@number_format($strppertot[$divisi],2)."</td>
					
					<td align=right>".@number_format($stselorg[$divisi])."</td>
					<td align=right>".@number_format($stselhk[$divisi],2)."</td>
					<td align=right>".@number_format($stselrp[$divisi],2)."</td>
					
				</tr>";
				
					@$gtorgperlalu+=$storgperlalu[$divisi];
					@$gthkperlalu+=$sthkperlalu[$divisi];
					@$gtrpperlalu+=$strpperlalu[$divisi];
					
					@$gtorgperskrg+=$storgperskrg[$divisi];
					@$gthkperskrg+=$sthkperskrg[$divisi];
					@$gtrpperskrg+=$strpperskrg[$divisi];
				
					@$gthkpertgh+=$sthkpertgh[$divisi];
					@$gtrppertgh+=$strppertgh[$divisi];
					@$gtrppertot+=$strppertot[$divisi];
				
		}

		@$gtselorg=$gtorgperskrg-$gtorgperlalu;
		@$gtselrp=$gtrppertot-$gtrpperlalu;
		@$gtselhk=$gthkperlalu-$gthkperskrg;

		
		
		
		$stream.="
			<tr bgcolor=#48D1CC>
				<td colspan=4>Grand Total</td>
				<td align=right>".@number_format($gtorgperlalu)."</td>
				<td align=right>".@number_format($gthkperlalu,2)."</td>
				<td align=right>".@number_format($gtrpperlalu,2)."</td>
				
				<td align=right>".@number_format($gtorgperskrg)."</td>
				<td align=right>".@number_format($gthkperskrg,2)."</td>
				<td align=right>".@number_format($gtrpperskrg,2)."</td>
			
				<td align=right>".@number_format($gthkpertgh,2)."</td>
				<td align=right>".@number_format($gtrppertgh,2)."</td>
				<td align=right>".@number_format($gtrppertot,2)."</td>
				
				<td align=right>".@number_format($gtselorg)."</td>
				<td align=right>".@number_format($gtselhk,2)."</td>
				<td align=right>".@number_format($gtselrp,2)."</td>
				
			</tr></table>";

		if($tipe=='html')
		{
			echo $stream;
		}
		else
		{
			$tglSkrg = date("Ymd");
			$nop_ = "excel_pdo_".$jenis."_".$unit."_".$per;
			if (strlen($stream) > 0) 
			{
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}
	break;
    default;	
}
?>