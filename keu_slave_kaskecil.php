<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$method        = checkPostGet('method','');
$noaruskas     = checkPostGet('noaruskas','');
$periode       = checkPostGet('periode','');
$notransaksi   = checkPostGet('notransaksi','');
$notransaksisch= checkPostGet('notransaksisch','');
$unit          = checkPostGet('unit','');
$jenis         = checkPostGet('jenis','');
$opening       = checkPostGet('opening','');
$advance       = checkPostGet('advance','');
$tanggal       = tanggalsystemn(checkPostGet('tanggal',''));
if($tanggal=='--'){
	$tanggal='';
}
$tanggalsch	   =tanggalsystemn(checkPostGet('tanggalsch',''));
if($tanggalsch=='--'){
	$tanggalsch='';
}
$tanggalcashdata=tanggalsystemn(checkPostGet('tanggalcashdata',''));
if($tanggalcashdata=='--'){
	$tanggalcashdata='';
}

$noaruskas          = checkPostGet('noaruskas','');
$noakun             = checkPostGet('noakun','');
$keterangan         = checkPostGet('keterangan','');
$keterangan2        = checkPostGet('keterangan2','');
$penerima           = checkPostGet('penerima','');
$jumlahditerima     = checkPostGet('jumlahditerima','');
$jumlahdipakai      = checkPostGet('jumlahdipakai','');
$jumlah             = checkPostGet('jumlah','');
$saldoberjalan      = checkPostGet('saldoberjalan','');
$nourut             = checkPostGet('nourut','');
$novoucher          = checkPostGet('novoucher','');
$unitcashdata       = checkPostGet('unitcashdata','');
$plafoncashdata     = checkPostGet('plafoncashdata','');
$novouchercashdata  = checkPostGet('novouchercashdata','');
$noakuncashdata     = checkPostGet('noakuncashdata','');
$keterangancashdata = checkPostGet('keterangancashdata','');
$penerimacashdata   = checkPostGet('penerimacashdata','');
$noaruskascashdata  = checkPostGet('noaruskascashdata','');
$notransaksicashdata= checkPostGet('notransaksicashdata','');
$totjumlah          = checkPostGet('totjumlah','');

$noreferensi= checkPostGet('noreferensi','');
$file = checkPostGet('file','');
$fileupload = checkPostGet('fileupload','');
$namafile =checkPostGet('namafile','');
$posting =checkPostGet('posting','');
$optaruskas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$wherex='';

$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$kept = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$alokasi=makeOption($dbname,'organisasi','kodeorganisasi,alokasi');
$kary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$tipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
$arrakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$ketaruskas=makeOption($dbname,'keu_5keterangan','noaruskas,keterangan');
$ketaruskasx=makeOption($dbname,'keu_5keterangan','id_ket,keterangan');

if(@$tipe[$unit]=='HOLDING'){
	$wherex.=" and pemilik_aruskas in ('GLOBAL','HOLDING') and status='1' and level='3' and tipetransaksi='K'";
} else if(@$tipe[$unit]=='KANWIL'){
	$wherex.=" and pemilik_aruskas in ('GLOBAL','KANWIL') and status='1' and level='3' and tipetransaksi='K'";
}else{
	$wherex.=" and pemilik_aruskas in ('GLOBAL','UNIT') and status='1' and level='3' and tipetransaksi='K'";
}
$str = "SELECT * FROM ".$dbname.".keu_5aruskas where akses_rekening='KK' ".$wherex." order by noaruskas asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']."-".$bar['nama_aruskas']."</option>";
}

$optpenerima = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "SELECT karyawanid,namakaryawan FROM ".$dbname.".datakaryawan where 
		lokasitugas='".$_SESSION['empl']['lokasitugas']."' and tipekaryawan in ('0','1')
		and tanggalkeluar='0000-00-00'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($method=='prosescash'){
		$sel="";
		if($bar['karyawanid']==$_SESSION['standard']['userid']){
			$sel="selected";
		}
	}
	$optpenerima.="<option value=".$bar['karyawanid']." ".@$sel.">".$bar['namakaryawan']."</option>";
	
}

#= akun ayat silang	
$str="SELECT nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='GL'	and kodeparameter='GLAS'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();		
$akunayatsilang=$bar['nilai'];
$akkasbesar="1112101";
$akkaskecil="1112102";
$aruskasas=makeOption($dbname,'keu_5aruskas_detail','noakun,noaruskas',"noakun='".$akunayatsilang."'");

#= kode jurnal
$kodejurnalkeluar='KK';	
$kodejurnalmasuk='KM';
$noaruskascash = checkPostGet('noaruskascash','');
$path   = "fileupload/kaskecil/";
#= arr jenis
$arrjenis=array("1"=>"Advance","2"=>"Pertanggung Jawaban","3"=>"Pemakaian","6"=>"Masuk");
if(isset($_POST['closing'])){
	$closing=$_POST['closing'];
}
switch($method){
	case'getformNoRef':

		$tab="";
		$tab = "<fieldset  style=width:94%><legend>".$_SESSION['lang']['find']."</legend>";
		$tab.= "<table>";
        $tab.= "<tr><td>".$_SESSION['lang']['novoucher']."</td><td>:</td>";
        $tab.= "<td><input type=text class=myinputtext id=novouchercr></td></tr>";
        $tab.= "</table>";
        $tab.= "<button class=mybutton onclick=findref(0)>Find</button><input type=hidden id=notransaksiData value='".$notransaksi."' /></fieldset>
				 <div id=container2></div>";
        echo $tab;
	break;	

	case'getnoakuncash':
		$optnoakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "SELECT a.noaruskas,a.noakun,b.namaakun FROM ".$dbname.".keu_5aruskas_detail a LEFT JOIN ".$dbname.".keu_5akun b on a.noakun=b.noakun 
				where a.noaruskas='".$noaruskascash."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if($noakun==$bar['noakun']){
				$select='selected';
			}else{
				$select='';
			}
			$optnoakun.="<option value=".$bar['noakun']." ".$select.">".$bar['noakun']."-".$bar['namaakun']."</option>";
		}
		echo $optnoakun;
	break;

	case'detaildata':
		// echo $notransaksi._.$unit._.$periode;
		$str="SELECT * from ".$dbname.".keu_kaskecil_vw where unit='".$unit."' and periode='".$periode."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$notransaksidata[$bar['notransaksi']]=$bar['notransaksi'];
			$nourutdata[$bar['nourut']]=$bar['nourut'];
			// if($bar['tipe']=='M'){
				// $jumlahmasuk[$bar['notransaksi']][$bar['nourut']]=$bar['jumlahditerima'];
				// $jumlahkeluar[$bar['notransaksi']][$bar['nourut']]=0;
			// }else{
				// $jumlahmasuk[$bar['notransaksi']][$bar['nourut']]=0;
				// $jumlahkeluar[$bar['notransaksi']][$bar['nourut']]=$bar['jumlahdipakai'];
			// }
			
			$jenis[$bar['notransaksi']][$bar['nourut']]=$bar['jenis'];
			
			if($bar['jenis']=='1'){
				$jumlahkeluar[$bar['notransaksi']][$bar['nourut']]=0;
				$advancebiaya[$bar['notransaksi']][$bar['nourut']]=$bar['jumlah'];
			}
			
			if($bar['jenis']=='2'){
				$jumlahkeluar[$bar['notransaksi']][$bar['nourut']]=$bar['jumlah'];
				$advancebiaya[$bar['notransaksi']][$bar['nourut']]=0;
			}
			
			if($bar['jenis']=='3'){
				$jumlahkeluar[$bar['notransaksi']][$bar['nourut']]=$bar['jumlah'];
				$advancebiaya[$bar['notransaksi']][$bar['nourut']]=0;
			}
			
			if($bar['jenis']=='6'){
				$jumlahmasuk[$bar['notransaksi']][$bar['nourut']]=$bar['jumlah'];
				$advancebiaya[$bar['notransaksi']][$bar['nourut']]=0;
			}
			
			
			$noakun[$bar['notransaksi']][$bar['nourut']]=$bar['noakun'];
			$keterangan[$bar['notransaksi']][$bar['nourut']]=$arrjenis[$bar['jenis']].' - '.$ketaruskasx[$bar['keterangan']];
			$novoucher[$bar['notransaksi']][$bar['nourut']]=$bar['novoucher'];
			$tanggal[$bar['notransaksi']][$bar['nourut']]=$bar['tanggal'];
			$penerima[$bar['notransaksi']][$bar['nourut']]=$bar['penerima'];
			$listdata[$bar['notransaksi']][$bar['nourut']]=$bar['nourut'];
			$tglterakhir[$bar['notransaksi']]=$bar['tanggal'];
		}		
		// foreach($notransaksidata as $notran){
			// foreach($nourutdata as $nourut){
				// if($listdata[$notran][$nourut]!=''){
					// @$saldoberjalan=$saldoberjalan+$jumlahmasuk[$notran][$nourut]-$jumlahkeluar[$notran][$nourut];
				// }
			// }
		// }
		$str="SELECT * from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".$periode."' order by periode desc limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$saldoberjalan=$bar['saldoberjalan'];
			$plafon=$bar['plafon'];
			$tanggalmulai	=$bar['tanggalmulai'];	
			$tanggalselesai=$bar['tanggalselesai'];	
			$akunkaskecil=$bar['noakun'];
			$saldoawal=$bar['saldoawal'];
		$border="border=0";	
		if($file=='excel'){
			$border="border=1";
		}
		$tab="";
		if($file!='excel'){
			$tab.="<img src=images/excel.jpg class=resicon title='MS.Excel' onclick=detaildata('".$unit."','".$periode."')><br>";
		}
		$tab.="<table align=left cellspacing=1 ".$border." cellpading=1 width=100%>
			<thead>
				<tr>
					<td>Jumlah Autorisasi</td>
					<td align=right>".number_format($plafon)."</td>
					<td>".$_SESSION['lang']['tanggalmulai']."</td>
					<td>".tanggalnormal($bar['tanggalmulai'])."</td>
				</tr>	
				<tr>
					<td>Replenished Date</td>
					<td>15 & 30 each month</td>
					<td>".$_SESSION['lang']['tanggalselesai']."</td>
					<td>".tanggalnormal($bar['tanggalselesai'])."</td>
				</tr>	
			</thead>
			</table>

			<table align=left cellspacing=1 ".$border." cellpading=1 width=100%>
			<thead>
				<tr>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td align=center>".$_SESSION['lang']['novoucher']."</td>
					<td align=center>Diterima Oleh</td>
					<td align=center>".$_SESSION['lang']['noakun']."</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td align=center>".$_SESSION['lang']['kasmasuk']."</td>
					<td align=center>".$_SESSION['lang']['kaskeluar']."</td>
					<td align=center>Advance</td>
					<td align=center>".$_SESSION['lang']['saldo']."</td>
				</tr>
			</thead>";
			$saldoberjalanDt=array();
			$rowDt=0;
			$no=0;
			$tab.="<tr>";
			$tab.="<td colspan=8 align=right>".$_SESSION['lang']['saldoawal']."</td>";
			$tab.="<td align=right>".number_format($saldoawal)."</td>";
			$tab.="</tr>";
			foreach($notransaksidata as $notran){
				$bgcolor="";
				if($notran==$notransaksi){
					$bgcolor="bgcolor=limegreen";
				}
				foreach($nourutdata as $nourut){
					if($listdata[$notran][$nourut]!=''){
						$no+=1;
						$tab.="<tr ".$bgcolor.">";
						$tab.="<td>".$no."</td>";
						$tab.="<td>".tglnmbln($tanggal[$notran][$nourut],'','')."</td>";
						$tab.="<td>".$notran."</td>";
						$tab.="<td>".$novoucher[$notran][$nourut]."</td>";
						$tab.="<td>".$kary[$penerima[$notran][$nourut]]."</td>";
						$tab.="<td>".$noakun[$notran][$nourut]."</td>";
						$tab.="<td>".$keterangan[$notran][$nourut]."</td>";
						$tab.="<td align=right>".@number_format($jumlahmasuk[$notran][$nourut])."</td>";
						$tab.="<td align=right>".@number_format($jumlahkeluar[$notran][$nourut])."</td>";
						$tab.="<td align=right>".@number_format($advancebiaya[$notran][$nourut])."</td>";
						if($jenis[$notran][$nourut]!=1){
							if($rowDt==0){
								@$saldoberjalanDt[$rowDt]=($saldoawal+$jumlahmasuk[$notran][$nourut])-$jumlahkeluar[$notran][$nourut];	
							}else{
								@$saldoberjalanDt[$rowDt]=($saldoberjalanDt[($rowDt-1)]+$jumlahmasuk[$notran][$nourut])-$jumlahkeluar[$notran][$nourut];
							}	
							$rowDt+=1;
						}
						$saldoberjalan=$saldoberjalanDt[($rowDt-1)];
						$tab.="<td align=right>".number_format($saldoberjalan)."</td>";//__".$rowDt."__".$saldoberjalanDt[($rowDt-1)]."
						$tab.="</tr>";
					}
				}
			}
		$tab.="</table><br>";
		if($file=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="excel_";
			if(strlen($tab)>0)
			{
					if ($handle = opendir('tempExcel')) {
							while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != "..") {
									@unlink('tempExcel/'.$file);
							}
							}	
							closedir($handle);
					}
					$handle=fopen("tempExcel/".$nop_.".xls",'w');
					if(!fwrite($handle,$tab))
					{
							echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
							exit;
					}
					else
					{
							echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls';
							</script>";
					}
					fclose($handle);
			}  
		}else{
			echo $tab;
		}
	break;

	case'getopening':
		
		$saldoexpenses=$pengurang=$saldoadvance=$saldoclosing=$saldoopening=0;
		$periode=substr($tanggal,0,7);
				
	
		// if($notransaksi==''){
		// 	$str="SELECT saldoberjalan,periode,saldoadvance from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".$periode."' order by periode desc limit 1 ";
		// 	//exit('warning'.$str);
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	$bar=$res->fetch();
		// 		@$saldoopening=$bar['saldoberjalan'];
		// 		@$periode=$bar['periode'];
		// 		@$saldoclosing=$saldoopening;
		// 		@$saldoadvance=$bar['saldoadvance'];
		// }else{
		// 	#= ambil data pendukung sumber dari notransaksi
		// 	$str="SELECT unit,periode,tanggal,jenis,jumlah,noreferensi,novoucher from ".$dbname.".keu_kaskecil_vw where unit='".$unit."' and periode='".$periode."' and tanggal<='".$tanggal."'";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
		// 		$unit=$bar['unit'];
		// 		$periode=$bar['periode'];
		// 		$tanggal=$bar['tanggal'];
				
		// 		if($bar['jenis']=='1'){
		// 			$kosongKawan=1;
		// 			$sCek="select * from ".$dbname.".keu_kaskecildt where noreferensi='".$bar['novoucher']."'";
		// 			$rCek=fetchData($sCek);
		// 			if(count($rCek)==0){
		// 				$kosongKawan=0;
		// 				@$advance+=$bar['jumlah'];	
		// 			}
		// 		}
				
		// 		if($bar['jenis']=='2'){
		// 			@$pengurang+=$bar['jumlah'];
		// 			if($kosongKawan==0){
		// 				@$pengurangadvance+=$bar['jumlah'];	
		// 			}
		// 		}
				
		// 		if($bar['jenis']=='3'){
		// 			@$pengurang+=$bar['jumlah'];
		// 		}
		// 		if($bar['jenis']=='6'){
		// 			@$penambah+=$bar['jumlah'];
		// 		}
		// 	}
			
		// 	#= perhitungan
		// 	$str="SELECT saldoberjalan,saldoadvance,periode,plafon from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".$periode."' order by periode desc limit 1 ";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	$bar=$res->fetch();
		// 	@$saldoopening=$bar['saldoberjalan']-$pengurang;
		// 	@$saldoadvance=$bar['saldoadvance'];
		// 	@$periode=$bar['periode'];
				
		// 	#= sebelum transaksi
		// 	/*
		// 	$str="SELECT jumlah,tipe,jenis from ".$dbname.".keu_kaskecil_vw where unit='".$unit."' 
		// 	and tanggal between '".substr($tanggal,0,7)."-01' and '".$tanggal."' ";
		// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// 	$res->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($bar=$res->fetch()){
				
		// 		if($bar['jenis']=='1'){
		// 			@$saldoadvance+=$bar['jumlah'];
		// 		}
				
		// 		if($bar['jenis']=='2'){
		// 			@$pengurang+=$bar['jumlah'];
		// 		}
				
		// 		if($bar['jenis']=='3'){
		// 			@$pengurang+=$bar['jumlah'];
		// 		}
		// 		if($bar['jenis']=='6'){
		// 			@$penambah+=$bar['jumlah'];
		// 		}
				
		// 	}
		// 	*/
			
		// 	$saldoclosing=$bar['plafon']-$pengurang;
		// 	$saldoadvance=$advance-$pengurangadvance;
			
		// }
		#update jamhari
		#opening balance,saldo akhir
		$str="SELECT saldoberjalan,periode,saldoadvance,plafon,saldoawal from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".$periode."' order by periode desc limit 1 ";
		//exit('warning'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$saldoopening=floatval($bar['plafon']);
		$saldoawal=floatval($bar['saldoawal']);
		//$saldoberjalan=floatval($bar['saldoberjalan']);
		#ambil transaksi masuk terakhir
		$sMasuk="select tanggal,jumlah from ".$dbname.".keu_kaskecil_vw where unit='".$unit."' and periode='".$periode."' and jenis=6 order by tanggal desc";
		$rMasuk=fetchData($sMasuk);
		foreach ($rMasuk as $key => $val) {
			if($tanggal>=$val['tanggal']){
				$saldoberjalanAja[$tanggal]+=$val['jumlah'];	
			}else{
				$saldoberjalanAja[$tanggal]=0;
			}
			
		}

		#= ambil data pendukung sumber dari notransaksi
		$str="SELECT unit,periode,tanggal,jenis,jumlah,noreferensi,novoucher from ".$dbname.".keu_kaskecil_vw where unit='".$unit."' and periode='".$periode."' and tanggal<='".$tanggal."'";
		//exit('warning'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$unit=$bar['unit'];
			$periode=$bar['periode'];
			//$tanggal=$bar['tanggal'];
			
			if($bar['jenis']=='1'){
				$kosongKawan=1;
				$sCek="select * from ".$dbname.".keu_kaskecildt where noreferensi='".$bar['novoucher']."'";
				$rCek=fetchData($sCek);
				if(count($rCek)==0){
					$kosongKawan=0;
					@$advance+=$bar['jumlah'];	
				}
			}
			
			if($bar['jenis']=='2'){
				@$pengurang+=$bar['jumlah'];
				if($kosongKawan==0){
					@$pengurangadvance+=$bar['jumlah'];	
				}
			}
			
			if($bar['jenis']=='3'){
				@$pengurang+=$bar['jumlah'];
			}
			if($bar['jenis']=='6'){
				@$penambah+=$bar['jumlah'];
			}
		}
		$saldoadvance=$advance-$pengurangadvance;
		$saldoexpenses=$pengurang;
		if($saldoberjalanAja[$tanggal]==0){
			$saldoclosing=$saldoawal-$pengurang;	
		}else{
			$saldoclosing=($saldoawal+$saldoberjalanAja[$tanggal])-$pengurang;
		}
		$saldoopening=$saldoberjalanAja[$tanggal];
		

		$wherex='';
		$tipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
		$optaruskas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($tipe[$unit]=='HOLDING'){
			$wherex.=" and pemilik_aruskas in ('GLOBAL','HOLDING') and status='1' and level='3' and tipetransaksi='K'";
		} else if($tipe[$unit]=='KANWIL'){
			$wherex.=" and pemilik_aruskas in ('GLOBAL','KANWIL') and status='1' and level='3' and tipetransaksi='K'";
		}else{
			$wherex.=" and pemilik_aruskas in ('GLOBAL','UNIT') and status='1' and level='3' and tipetransaksi='K'";
		}
		$str = "SELECT * FROM ".$dbname.".keu_5aruskas where (akses_rekening='KK' or akses_rekening='') ".$wherex." order by noaruskas asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
		}
		#ambil diterima
		$optDtKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($tipe[$unit]=='HOLDING'){
		$sDtKary="select nik,namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas like '%HO%'  and statuskaryawan != 'Keluar' and (tanggalkeluar='0000-00-00' or tanggalkeluar<'".$tanggal."') order by namakaryawan asc";
		}
		else
		{
		$sDtKary="select nik,namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and statuskaryawan != 'Keluar' and (tanggalkeluar='0000-00-00' or tanggalkeluar<'".$tanggal."') order by namakaryawan asc";
		}
		$rDtKary=fetchData($sDtKary);
		foreach ($rDtKary as $key => $val) {
			$optDtKary.="<option value='".$val['karyawanid']."'>".$val['nik']."-".$val['namakaryawan']."</option>";
		}
		
		echo number_format($saldoopening)."#####".number_format($saldoadvance)."#####".number_format($saldoclosing)."#####".number_format($saldoexpenses)."#####".number_format($saldoawal)."#####".$optaruskas."#####".$optDtKary;
		//exit('error');
	break;
	
	case'buatcash':
		$jenis=6;
		#= cek apakah ada transaksi yang tanggalnya lebih maju
		
		if ($tanggalcashdata=='' || $penerimacashdata=='') {
			exit('warning : Tanggal dan Penerima Tidak Boleh Kosong.');
		}
		$_POST['totPlafon']=str_replace(",", "", $_POST['totPlafon']);
		$selisihdt=$_POST['totPlafon']-$totjumlah;
		if($selisihdt<0){
			exit('warning: Melebihi Plafon Yang Ada'.$selisihdt);
		}
		#= cek apakah ada transaksi belum posting
		$data=0;
		$str = "SELECT distinct notransaksi FROM ".$dbname.".keu_kaskecil_vw where posting=0 and unit='".$unit."' and tanggal like '".$_POST['periodecash']."%'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$data+=1;	
			$notransaksiblumposting.=$bar['notransaksi']."\n";
		}
		if($data>0){
			exit("Warning:Ada transaksi yang belum diposting, Silahkan posting transaksi : \n".$notransaksiblumposting);
		}
		

		#Keterangan
		$ket='Top Up pada unit : '.$unitcashdataarr[0].' pada tanggal '.$tanggalcashdata. ' sejumlah : '.number_format($totjumlah);
		$kodejurnalkeluar='KK';	
		$kodejurnalmasuk='KM';	

		#noaruskas kas besar
		@$datadt=getArusKasket($akkasbesar,substr($tanggalcashdata,5,2),substr($tanggalcashdata,0,4));
        @$datadt=explode('##', $datadt);
        $noaruskaskb=$datadt[0];

		#noaruskas kas kecil
		@$datadt=getArusKasket($akkaskecil,substr($tanggalcashdata,5,2),substr($tanggalcashdata,0,4));
        @$datadt=explode('##', $datadt);
        $noaruskaskk=$datadt[0];

		#noaruskas,keterangan2,keterangan2temp ayat silang
        @$datadt=getArusKasket($akunayatsilang,substr($tanggalcashdata,5,2),substr($tanggalcashdata,0,4));
        @$datadt=explode('##', $datadt);
        $noaruskasas=$datadt[0];
        $keterangan2temp=$datadt[1];
        $keterangan2=$datadt[2];		
		//exit('warning'.$keterangan2temp);
		#explode data detail
		$unitcashdataarr=explode('###', $unitcashdata);
		$notransaksicashdataarr=explode('###', $notransaksicashdata);
		$novouchercashdataarr=explode('###', $novouchercashdata);
		$noaruskascashdataarr=explode('###', $noaruskascashdata);
		$noakuncashdataarr=explode('###', $noakuncashdata);
		$keterangancashdataarr=explode('###', $keterangancashdata);
		$plafoncashdataarr=explode('###', $plafoncashdata);
		// echo"<pre>";
		// echo print_r($noakuncashdataarr);
		// echo"</pre>";
		// exit('warning');
		#Notransaksi Kas Kecil	
		$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdataarr[0]."/KM/C";
		$qTrans = selectQuery($dbname,'keu_kaskecilht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
		$resTrans = fetchData($qTrans);
		if(empty($resTrans)) {
			$notransaksikc = $noTrans."00001";
		} else {
			$tmpTrans = substr($resTrans[0]['notransaksi'],18,5);
			$tmpTrans++;
			$notransaksikc = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
		}

		$rekening='';
		$strrek = "SELECT noakun2,rekening FROM ".$dbname.".keu_5kaskecil where periode='".$_POST['periodecash']."' and unit='".$unit."'";
		$resrek = $owlPDO->query($strrek) or die(print " Gagal: " . PDOException::getMessage());
		$resrek ->setFetchMode(PDO::FETCH_ASSOC);
		$barrek = $resrek->fetch();
		$noakunbank=$barrek['noakun2'];
		$rekening=$barrek['rekening'];


											############################
											###Buat Transaksi Kasbank###
											############################

		$dataKasBank['header']=array();
		$dataKasBank['header1']=array();
		$dataKasBank['header2']=array();
		$dataKasBank['detailKM']=array();
		$dataKasBank['detailKK']=array();
		$dataJurnal['header']=array();
		$dataJurnal['header2']=array();
		$dataJurnal['header3']=array();
		$dataJurnal['detailDB']=array();
		$dataJurnal['detailCR']=array();
		$dataRes['header']=array();
		$dataRes['detail']=array();	

		#notransaksi kasbank masuk
	 	$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdataarr[0]."/".$kodejurnalmasuk."/";
        $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
        //echo $qTrans;
        
        $resTrans = fetchData($qTrans);
        // echo $resTrans[0]['notransaksi'];
        // exit('warning');
        if(empty($resTrans)) {
            $notransaksikm = $noTrans."00001";
        } else {
        	$dtTransNo=explode("/",$resTrans[0]['notransaksi']);
            $tmpTrans = intval($dtTransNo[3]);
            $tmpTrans++;
            $notransaksikm = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
        }

        #Header Kasbank Masuk
		$dataKasBank['header'] = array(
			'notransaksi'=>$notransaksikm,
			'tanggal'=>$tanggalcashdata,
			'noakun'=>$akkaskecil,
			'tipetransaksi'=>'M',
			'jumlah'=>($totjumlah),
			'posting'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'keterangan'=>'Top Up '.$unitcashdataarr[0].''.$tanggalcashdata.',transaksi kas kecil masuk, notransaksi :'.$notransaksikc,
			'rekonsiliasi'=>'0',
			'yn'=>'0',
			'cgttu'=>'Cash',
			'kodeorg'=>$unitcashdataarr[0],
			'userid'=>$_SESSION['standard']['userid'],
			'hutangunit'=>'0',
			'noakunhutang'=>'0',
			'pemilikhutang'=>'',
			'lastupdate'=>date("Y-m-d H:i:s"),
			'nocek'=>'',
			'pembayaran'=>0,
			'tanggalinput'=>$tanggalcashdata,
			'novoucher'=>'',
			'bayarkepada'=>'',
			'rekening'=>'',
			'predefined'=>'0'
		);

		#Detail Kasbank Masuk
		$dataKasBank['detailKM'][] = array(
			'notransaksi'=>$notransaksikm,
			'noakun'=>$akunayatsilang,
			'tipetransaksi'=>'M',
			'tanggal'=>$tanggalcashdata,
			'jumlah'=>($totjumlah),
			'noakun2a'=>$akkaskecil,
			'kode'=>$kodejurnalmasuk,
			'keterangan1'=>$notransaksikc,
			'keterangan2'=>$keterangan2,
			'keterangan2temp'=>$keterangan2temp,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kurs2'=>'1',
			'noaruskas'=>$noaruskasas,
			'kodeorg'=>$unitcashdataarr[0],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>$_POST['penerimacashdata'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'kodevhc'=>'',
			'orgalokasi'=>'',
			'nodok'=>$notransaksikc,
			'hutangunit1'=>'0',
			'kodesegment'=>'',
			'tahun'=>substr($tanggalcashdata, 0,4),
			'bulan'=>substr($tanggalcashdata, 5,2),
			'keterangan3'=>''
		);				
		
		#notransaksi Kas kecil Keluar
		$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdataarr[0]."/".$kodejurnalkeluar."/";
        $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
        $resTrans = fetchData($qTrans);
        if(empty($resTrans)) {
            $notransaksikk = $noTrans."00001";
        } else {
        	$dtTransNo=explode("/",$resTrans[0]['notransaksi']);
            $tmpTrans = intval($dtTransNo[3]);
            $notransaksikk = $noTrans.str_pad($tmpTrans+1,5,'0',STR_PAD_LEFT);
        }

        #notransaksi topup keluar
        if ($rekening=='') {
        	$aktopupkeluar=$akkasbesar;
        	$kodejurnalkeluartopup=$kodejurnalkeluar;

        	if(empty($resTrans)) {
	            $notransaksikkbesar = $noTrans."00002";
	        } else {
	            $notransaksikkbesar = $noTrans.str_pad(($tmpTrans+2),5,'0',STR_PAD_LEFT);
	        }
        }else{
        	$aktopupkeluar=$noakunbank;
        	$kodejurnalkeluartopup='BK';
			$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdataarr[0]."/".$kodejurnalkeluartopup."/";
	        $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
	        $resTrans = fetchData($qTrans);
	        if(empty($resTrans)) {
	            $notransaksikkbesar = $noTrans."00001";
	        } else {
	        	$dtTransNo=explode("/",$resTrans[0]['notransaksi']);
	            $tmpTrans = intval($dtTransNo[3]);
	            $notransaksikkbesar = $noTrans.str_pad(($tmpTrans+1),5,'0',STR_PAD_LEFT);
	        }
        }

        
	
		#header kas kecil keluar
        $dataKasBank['header1'] = array(
			'notransaksi'=>$notransaksikk,
			'tanggal'=>$tanggalcashdata,
			'noakun'=>$akkaskecil,
			'tipetransaksi'=>'K',
			'jumlah'=>($totjumlah),
			'posting'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'keterangan'=>'Top Up '.$unitcashdataarr[0].''.$tanggalcashdata.',transaksi kas kecil keluar, notransaksi :'.$notransaksikc,
			'rekonsiliasi'=>'0',
			'yn'=>'0',
			'cgttu'=>'Cash',
			'kodeorg'=>$unitcashdataarr[0],
			'userid'=>$_SESSION['standard']['userid'],
			'hutangunit'=>'0',
			'noakunhutang'=>'',
			'pemilikhutang'=>'0',
			'lastupdate'=>date("Y-m-d H:i:s"),
			'nocek'=>'',
			'pembayaran'=>0,
			'tanggalinput'=>$tanggalcashdata,
			'novoucher'=>'',
			'bayarkepada'=>'',
			'rekening'=>'',
			'predefined'=>'0'
		);

        #detail kasbank besar keluar
		$dataKasBank['header2'] = array(
			'notransaksi'=>$notransaksikkbesar,
			'tanggal'=>'0000-00-00',
			'noakun'=>$aktopupkeluar,
			'tipetransaksi'=>'K',
			'jumlah'=>($totjumlah),
			'posting'=>'0',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'keterangan'=>'Top Up '.$unitcashdataarr[0].''.$tanggalcashdata.',transaksi kas besar keluar, notransaksi :'.$notransaksikc,
			'rekonsiliasi'=>'0',
			'yn'=>'0',
			'cgttu'=>'Cash',
			'kodeorg'=>$unitcashdataarr[0],
			'userid'=>$_SESSION['standard']['userid'],
			'hutangunit'=>'0',
			'noakunhutang'=>'',
			'pemilikhutang'=>'',
			'lastupdate'=>date("Y-m-d H:i:s"),
			'nocek'=>'',
			'pembayaran'=>0,
			'tanggalinput'=>$tanggalcashdata,
			'novoucher'=>'',
			'bayarkepada'=>'',
			'rekening'=>$rekening,
			'predefined'=>'0'
		);

		#detail kasbank besar keluar
		$dataKasBank['detailKK'][] = array(
			'notransaksi'=>$notransaksikkbesar,
			'noakun'=>$akunayatsilang,
			'tipetransaksi'=>'K',
			'tanggal'=>'0000-00-00',
			'jumlah'=>($totjumlah),
			'noakun2a'=>$aktopupkeluar,
			'kode'=>$kodejurnalkeluartopup,
			'keterangan1'=>$notransaksikc,
			'keterangan2'=>$keterangan2,
			'keterangan2temp'=>$keterangan2temp,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kurs2'=>'1',
			'noaruskas'=>$noaruskasas,
			'kodeorg'=>$unitcashdataarr[0],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>$_POST['penerimacashdata'],
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'kodevhc'=>'',
			'orgalokasi'=>'',
			'nodok'=>$notransaksikc,
			'hutangunit1'=>'0',
			'kodesegment'=>'',
			'tahun'=>substr($tanggalcashdata, 0,4),
			'bulan'=>substr($tanggalcashdata, 5,2),
			'keterangan3'=>''
		 );				



											#############################
											####Buat Transaksi Jurnal####
											#############################


		#header jurnal per masing-masing transaksi
	   	$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$alokasi[$unitcashdataarr[0]]."' and kodekelompok='".$kodejurnalkeluar."'");
		$tmpKonter = fetchData($queryJ);
		// echo $tmpKonter[0]['nokounter'];
		// exit('warning');
		$konter = addZero($tmpKonter[0]['nokounter']+1,3);
		# Prep No Jurnal kasbank kecil
		$nojurnal = str_replace('-','',$tanggalcashdata)."/".$unitcashdataarr[0]."/".$kodejurnalkeluar."/".$konter;

		#header jurnal kasbank kecil
		$dataJurnal['header'] = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>$kodejurnalkeluar,
			'tanggal'=>$tanggalcashdata,
			'tanggalentry'=>date('Ymd'),
			'posting'=>'0',
			'totaldebet'=>'0',
			'totalkredit'=>'0',
			'amountkoreksi'=>'0',
			'noreferensi'=>$notransaksikc,
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);


		# Prep No Jurnal kasbank besar
		// $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$alokasi[$unitcashdataarr[0]]."' and kodekelompok='".$kodejurnalkeluar."'");
		// $tmpKonter = fetchData($queryJ);
		if ($rekening=='') {
			$konter = addZero($tmpKonter[0]['nokounter']+2,3);
			$nojurnal2 = str_replace('-','',$tanggalcashdata)."/".$unitcashdataarr[0]."/".$kodejurnalkeluar."/".$konter;
			
			#header jurnal kasbank besar
			$dataJurnal['header2'] = array(
				'nojurnal'=>$nojurnal2,
				'kodejurnal'=>$kodejurnalkeluartopup,
				'tanggal'=>$tanggalcashdata,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>'0',
				'totalkredit'=>'0',
				'amountkoreksi'=>'0',
				'noreferensi'=>$notransaksikc,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);

			#detail debet jurnal kasbank besar
			$dataJurnal['detailDB'][]= array(
				'nojurnal'=>$nojurnal2,
				'tanggal'=>$tanggalcashdata,
				'nourut'=>'1',
				'noakun'=>$akunayatsilang,
				'keterangan'=>$ket,
				'jumlah'=>($totjumlah),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unitcashdataarr[0],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>$_POST['penerimacashdata'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$notransaksikc,
				'noaruskas'=>$noaruskasas,
				'kodevhc'=>'',
				'nodok'=>$notransaksikkbesar,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => ''
			);

			#detail kredit jurnal kasbank besar
			$dataJurnal['detailCR'][]= array(
				'nojurnal'=>$nojurnal2,
				'tanggal'=>$tanggalcashdata,
				'nourut'=>'2',
				'noakun'=>$aktopupkeluar,
				'keterangan'=>$ket,
				'jumlah'=>(($totjumlah)*-1),
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unitcashdataarr[0],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>$_POST['penerimacashdata'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$notransaksikc,
				'noaruskas'=>$noaruskaskb,
				'kodevhc'=>'',
				'nodok'=>$notransaksikkbesar,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => ''
			);
		}
		
		# Prep No Jurnal kas masuk
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
	    "kodeorg='".$alokasi[$unitcashdataarr[0]]."' and kodekelompok='".$kodejurnalmasuk."'");
		$tmpKonter = fetchData($queryJ);
	
		$konter = addZero($tmpKonter[0]['nokounter']+1,3);
		$nojurnal3 = str_replace('-','',$tanggalcashdata)."/".$unitcashdataarr[0]."/".$kodejurnalmasuk."/".$konter;

		#header jurnal kas masuk
		$dataJurnal['header3'] = array(
			'nojurnal'=>$nojurnal3,
			'kodejurnal'=>$kodejurnalmasuk,
			'tanggal'=>$tanggalcashdata,
			'tanggalentry'=>date('Ymd'),
			'posting'=>'0',
			'totaldebet'=>'0',
			'totalkredit'=>'0',
			'amountkoreksi'=>'0',
			'noreferensi'=>$notransaksikc,
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);

		#detail debet jurnal kas masuk
		$dataJurnal['detailDB'][]= array(
			'nojurnal'=>$nojurnal3,
			'tanggal'=>$tanggalcashdata,
			'nourut'=>'1',
			'noakun'=>$akkaskecil,
			'keterangan'=>$ket,
			'jumlah'=>($totjumlah),
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$unitcashdataarr[0],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>$notransaksikc,
			'noaruskas'=>$noaruskasas,
			'kodevhc'=>'',
			'nodok'=>$notransaksikm,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => ''
		);
		#detail kredit jurnal kas masuk
		$dataJurnal['detailCR'][]= array(
			'nojurnal'=>$nojurnal3,
			'tanggal'=>$tanggalcashdata,
			'nourut'=>'3',
			'noakun'=>$akunayatsilang,
			'keterangan'=>$ket,
			'jumlah'=>(($totjumlah)*-1),
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$unitcashdataarr[0],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>$notransaksikc,
			'noaruskas'=>$noaruskaskb,
			'kodevhc'=>'',
			'nodok'=>$notransaksikm,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => ''
		);
			

											##############################
											###Buat Transaksi Kas Kecil###
											##############################


		#voucher kas kecil dan detail data kas kecil masuk
		$etglcash=explode('-',$tanggalcashdata);
		$noTrans='PCV-'.$etglcash[0].$etglcash[1];	
		$qTrans = selectQuery($dbname,'keu_kaskecil_vw','novoucher',"unit='".$unitcashdataarr[0]."' and novoucher like '".$noTrans."%'","novoucher desc",true,1,1);
		$resTrans = fetchData($qTrans);
		if(empty($resTrans)) {
			$novouchercashdata = $noTrans."001";
		} else {
			$tmpTrans = substr($resTrans[0]['novoucher'],10,3);
			$tmpTrans++;
			$novouchercashdata = $noTrans.str_pad($tmpTrans,3,'0',STR_PAD_LEFT);
		}	

		#header kas kecil
		$dataRes['header'] = array(
			'notransaksi'=>$notransaksikc,
			'tipe'=>'M',
			'tanggal'=>$tanggalcashdata,
			'unit'=>$unitcashdataarr[0],
			'createdby'=>$_SESSION['standard']['userid'],
			'createtime'=>date('Ymd'),
			'updateby'=>$_SESSION['standard']['userid'],
			'updatetime'=>date("Y-m-d H:i:s"),
			'posting'=>'1',
			'noreferensi'=>$notransaksikm
		);

		#detail kas kecil
		$dataRes['detail'] = array(
			'notransaksi'=>$notransaksikc,
			'nourut'=>'1',
			'novoucher'=>$novouchercashdata,
			'noaruskas'=>$noaruskasas,
			'noakun'=>$akunayatsilang,
			'keterangan'=>$keterangan2temp,
			'keterangan2'=>'',
			'penerima'=>$penerimacashdata,
			'jumlahditerima'=>'0',
			'jumlahdipakai'=>'0',
			'jumlah'=>($totjumlah),
			'saldoberjalan'=>'0',
			'jenis'=>$jenis,
			'createdby'=>$_SESSION['standard']['userid'],
			'createtime'=>date("Y-m-d H:i:s"),
			'updateby'=>$_SESSION['standard']['userid'],
			'updatetime'=>date('Y-m-d H:i:s'),
			'noreferensi'=>''
		);

		$urutAja=1;
		for ($i=0;$i<count($notransaksicashdataarr);$i++) {
			if(is_null($noakuncashdataarr[$i])){
				exit('warning'.$i);
			}
			#perulangan detail transaksi
			#= update isi noreferensi
			$queryK = updateQuery($dbname,'keu_kaskecilht',array('noreferensi'=>$notransaksikk),"notransaksi='".$notransaksicashdataarr[$i]."'");
			$errCounter = "";
			try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update noreferensi :". $e->getMessage() ; }

			$optketerangan=makeOption($dbname,'keu_5keterangan','id_ket,keterangan'," id_ket='".$keterangancashdataarr[$i]."'");
			#keterangandetail
			$sDet="select keterangan2,penerima from ".$dbname.".keu_kaskecildt where notransaksi='".$notransaksicashdataarr[$i]."' and novoucher='".$novouchercashdataarr[$i]."'";
			$rDet=fetchData($sDet);
			$dataJurnal['detailDB'][]= array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggalcashdata,
				'nourut'=>$urutAja,
				'noakun'=>$noakuncashdataarr[$i],
				'keterangan'=>$rDet[0]['keterangan2'],
				'jumlah'=>$plafoncashdataarr[$i],
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unitcashdataarr[$i],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>$rDet[0]['penerima'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$notransaksikc,
				'noaruskas'=>$noaruskascashdataarr[$i],
				'kodevhc'=>'',
				'nodok'=>$notransaksikk,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => ''
			);

			#noaruskas,keterangan2,keterangan2temp per data
	        $datadt=getSetupKeterangan($noaruskascashdataarr[$i],substr($tanggalcashdata,5,2),substr($tanggalcashdata,0,4));
	        $datadt=explode('##', $datadt);
	        $keterangan2dt=$datadt[0];
	        $keterangan2tempdt=$datadt[1];
	        
	        
			$dataKasBank['detailKK'][] = array(
				'notransaksi'=>$notransaksikk,
				'noakun'=>$noakuncashdataarr[$i],
				'tipetransaksi'=>'K',
				'tanggal'=>$tanggalcashdata,
				'jumlah'=>$plafoncashdataarr[$i],
				'noakun2a'=>$akkaskecil,
				'kode'=>'KK',
				'keterangan1'=>$notransaksikc,
				'keterangan2'=>$i.". ".$keterangan2dt,
				'keterangan2temp'=>$rDet[0]['keterangan2'],
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kurs2'=>'1',
				'noaruskas'=>$noaruskascashdataarr[$i],
				'kodeorg'=>$unitcashdataarr[$i],
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>$rDet[0]['penerima'],
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'kodevhc'=>'',
				'orgalokasi'=>'',
				'nodok'=>$notransaksikc,
				'hutangunit1'=>'0',
				'kodesegment'=>'',
				'tahun'=>substr($tanggalcashdata, 0,4),
				'bulan'=>substr($tanggalcashdata, 5,2),
				'keterangan3'=>''
			);		
			$urutAja+=1;
		}
		 
		$dataJurnal['detailCR'][]= array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tanggalcashdata,
			'nourut'=>($urutAja+1),
			'noakun'=>$akkaskecil,
			'keterangan'=>$ket,
			'jumlah'=>($totjumlah*-1),
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$unitcashdataarr[0],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>$notransaksikc,
			'noaruskas'=>$noaruskaskk,
			'kodevhc'=>'',
			'nodok'=>$notransaksikk,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => ''
		);

			
		$errorDB='';

		$cols = array(
            'notransaksi','tanggal','noakun','tipetransaksi','jumlah','posting','matauang','kurs','keterangan',
			'rekonsiliasi','yn','cgttu','kodeorg','userid','hutangunit','noakunhutang',
            'pemilikhutang','lastupdate','nocek','pembayaran','tanggalinput','novoucher',
			'bayarkepada','rekening','predefined'
        );

		// $str1 = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header'],$cols);
		// $str2 = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header1'],$cols);
		// $str3 = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header2'],$cols);
		// $str4 = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header']);
		// $str5 = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header2']);
		// $str6 = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header3']);
		// // echo "<pre>";
		// // print_r($str);
		// // echo "</pre>";
		// exit('warning : '.$str1.'<br>'.$str2.'<br>'.$str3.'<br>'.$str4.'<br>'.$str5.'<br>'.$str6.'<br>');

		$str = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header'],$cols);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
		$errorDB .= "header kas bank kecil :" . $e->getMessage() . "\n".$str."\n";
		}

		$str = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header1'],$cols);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
		$errorDB .= "header1 kas bank besar:" . $e->getMessage() . "\n".$str."\n";
		}

		$str = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header2'],$cols);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
		$errorDB .= "header2 kas kecil transaksi:" . $e->getMessage() . "\n".$str."\n";
		}

		$str = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header']);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			 $errorDB .= "header jurnal data :" . $e->getMessage() . "\n".$str."\n";
		}

		if ($rekening=='') {
			$str = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header2']);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				 $errorDB .= "header2 jurnaldt:" . $e->getMessage() . "\n".$str."\n";
			}
		}
		

		$str = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header3']);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			 $errorDB .= "header3 jurnal inimah :" . $e->getMessage() . "\n".$str."\n";
		}

		$colsheadkaskecil = array(
            'notransaksi','tipe','tanggal','unit','createdby','createtime','updateby','updatetime','posting','noreferensi'
        );

		$str = insertQuery($dbname,'keu_kaskecilht',$dataRes['header'],$colsheadkaskecil);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			 $errorDB .= "keu_kaskecilht  headernya:" . $e->getMessage() . "\n".$str."\n";
		}

		$colsdtkaskecil = array(
            'notransaksi','nourut','novoucher','noaruskas','noakun','keterangan','keterangan2',
            'penerima','jumlahditerima','jumlahdipakai','jumlah','saldoberjalan','jenis',
            'createdby','createtime','updateby','updatetime','noreferensi'
        );

		$str = insertQuery($dbname,'keu_kaskecildt',$dataRes['detail'],$colsdtkaskecil);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			 $errorDB .= "Detail Kas Kecil :" . $e->getMessage() . "\n".$str."\n";
		}

		$colskasbankdt = array(
            'notransaksi','noakun','tipetransaksi','tanggal','jumlah','noakun2a','kode','keterangan1','keterangan2',
            'keterangan2temp','matauang','kurs','kurs2','noaruskas','kodeorg','kodekegiatan','kodeasset','kodebarang',
            'nik','kodecustomer','kodesupplier','kodevhc','orgalokasi','nodok','hutangunit1','kodesegment','tahun',
            'bulan','keterangan3'
        );

		for ($i=0;$i<count($dataKasBank['detailKM']);$i++) {
			$str = insertQuery($dbname,'keu_kasbankdt',$dataKasBank['detailKM'][$i],$colskasbankdt);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
			$errorDB .= "Detail KM :" . $e->getMessage() . "\n".$str."\n";
			}
		}

		for ($i=0;$i<count($dataKasBank['detailKK']);$i++) {
			$str = insertQuery($dbname,'keu_kasbankdt',$dataKasBank['detailKK'][$i],$colskasbankdt);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
			$errorDB .= "Detail KK Sini Bukan:" . $e->getMessage() . "\n".$str."\n";
			}
		}

		for ($i=0;$i<count($dataJurnal['detailDB']);$i++) {
			$str = insertQuery($dbname,'keu_jurnaldt',$dataJurnal['detailDB'][$i]);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
			$errorDB .= "Detail DB Jurnal :" . $e->getMessage() . "\n".$str."\n";
			}
		}

		for ($i=0;$i<count($dataJurnal['detailCR']);$i++) {
			$str = insertQuery($dbname,'keu_jurnaldt',$dataJurnal['detailCR'][$i]);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
			$errorDB .= "Detail CR :" . $e->getMessage() . "\n".$str."\n";
			}
		}
		if($errorDB!=''){
			#roll back
			$sDel="delete from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksikc."'";
			try{$owlPDO->exec($sDel); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
			$sDel2="delete from ".$dbname.".keu_kasbankht where notransaksi in (select distinct notransaksi from ".$dbname.".keu_kasbankdt where nodok='".$notransaksikc."')";
			try{$owlPDO->exec($sDel2); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
			$sDel3="delete from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksikc."'";
			try{$owlPDO->exec($sDel3); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
			exit('warning:'.$errorDB);
		}
		
		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),"kodeorg='".$alokasi[$unitcashdataarr[$i]]."' and kodekelompok='".$kodejurnalmasuk."'");
		$errCounter = "";
		try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }

		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),"kodeorg='".$alokasi[$unitcashdataarr[$i]]."' and kodekelompok='".$kodejurnalkeluar."'");
		$errCounter = "";
		try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }

		if ($rekening=='') {
			$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),"kodeorg='".$alokasi[$unitcashdataarr[$i]]."' and kodekelompok='".$kodejurnalkeluar."'");
			$errCounter = "";
			try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
		}

		$queryKc = selectQuery($dbname,'keu_5kaskecil','saldoberjalan',"unit='".$unitcashdataarr[0]."' and periode='".substr($tanggalcashdata, 0,7)."'");
		$rqueryKc = fetchData($queryKc);
		$saldonow=$rqueryKc[0]['saldoberjalan']+$totjumlah;
		$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>$saldonow),"unit='".$unitcashdataarr[0]."' and periode='".substr($tanggalcashdata, 0,7)."'");
		$errCounter = "";
		try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
		
	break;
	
	case'buatcashawal':
	#ada 3 kondisi
	#1. memiliki saldo awal dan sudah terdaftar nilainya sebagai saldo awal gak bentuk jurnal
	#2. memiliki saldo awal dan mengambil kembali kasnya membentuk jurnal membentuk jurnal untuk selisihnya
	#3. saldo awal=0 dan mengisi dari nol
		$casedt="";
		if((intval($_POST['saldoawal'])>0)&&(intval($plafoncashdata)==0)){
			$plafoncashdata=$_POST['saldoawal'];
			$saldoberjalan=$_POST['saldoawal'];
			$saldoawal=$_POST['saldoawal'];
			$casedt="isisaldoawal";
		}
		if((intval($_POST['saldoawal'])==0)&&(intval($plafoncashdata)>0)){
			$casedt="isisaldopakplafon";
			$saldoawal=0;
		}
		if((intval($_POST['saldoawal'])>0)&&($plafoncashdata>0)){
			$casedt="isisaldopakplafon";
			$saldoawal=$_POST['saldoawal'];
		}

		$saldoberjalan=$plafoncashdata+$saldoawal;
		//exit('warning'.$casedt."__".$saldoawal);

		switch ($casedt) {
			case 'isisaldoawal':
				#Query Setup Kas Kecil
				$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>$saldoberjalan,'saldoawal'=>$saldoawal),"unit='".$unitcashdata."' and periode='".substr($tanggalcashdata,0,7)."'");
				$errCounter = "";
				try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update 5kaskecil Error :". $e->getMessage() ; }
				if($errCounter!=""){
					$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>0,'saldoawal'=>0),"unit='".$unitcashdata."' and periode='".substr($tanggalcashdata,0,7)."'");
					$errCounter = "";
					try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update 5kaskecil Error :". $e->getMessage() ; }
				}
			break;
			case 'isisaldopakplafon':
				#Query Setup Kas Kecil
				$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>$saldoberjalan,'saldoawal'=>$saldoawal),"unit='".$unitcashdata."' and periode='".substr($tanggalcashdata,0,7)."'");
				$errCounter = "";
				try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update 5kaskecil Error :". $e->getMessage() ; }
				#buat transaksi kas kecilnya
				$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdata."/KM/C";
				$qTrans = selectQuery($dbname,'keu_kaskecilht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
				$resTrans = fetchData($qTrans);
				if(empty($resTrans)) {
					$notransaksi = $noTrans."00001";
				} else {
					$tmpTrans = substr($resTrans[0]['notransaksi'],18,5);
					$tmpTrans++;
					$notransaksi = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
				}

				# Prep Novouchercashdata
				$etglcash=explode('-',$tanggalcashdata);
				$noTrans='PCV-'.$etglcash[0].$etglcash[1];	
				$qTrans = selectQuery($dbname,'keu_kaskecil_vw','novoucher',"unit='".$unitcashdata."' and novoucher like '".$noTrans."%'","novoucher desc",true,1,1);
				$resTrans = fetchData($qTrans);
				if(empty($resTrans)) {
					$novouchercashdata = $noTrans."001";
				} else {
					$tmpTrans = substr($resTrans[0]['novoucher'],10,3);
					$tmpTrans++;
					$novouchercashdata = $noTrans.str_pad($tmpTrans,3,'0',STR_PAD_LEFT);
				}	

				$dataRes['header'] = array();
				$dataRes['detail'] = array();

				#Header Kas Kecil
				$dataRes['header'] = array(
					'notransaksi'=>$notransaksi,
					'tipe'=>'M',
					'tanggal'=>$tanggalcashdata,
					'unit'=>$unitcashdata,
					'createdby'=>$_SESSION['standard']['userid'],
					'createtime'=>date('Y-m-d h:i:s'),
					'updateby'=>$_SESSION['standard']['userid'],
					'updatetime'=>date('Y-m-d h:i:s'),
					'posting'=>'1',
					'noreferensi'=>$notransaksicashdata
				);
				$ketDt=makeOption($dbname,"keu_5keterangan","noaruskas,id_ket","noaruskas='".$_POST['noaruskascashdata']."'");
				#Detail Kas Kecil
				$dataRes['detail'] = array(
					'notransaksi'=>$notransaksi,
					'nourut'=>'1',
					'novoucher'=>$novouchercashdata,
					'noaruskas'=>$_POST['noaruskascashdata'],
					'noakun'=>$_POST['noakuncashdata'],
					'keterangan'=>$ketDt[$_POST['noaruskascashdata']],
					'keterangan2'=>'Top Pertama Kali '.$unit.'__'.$periode,
					'penerima'=>$penerimacashdata,
					'jumlahditerima'=>'0',
					'jumlahdipakai'=>'0',
					'jumlah'=>$plafoncashdata,
					'saldoberjalan'=>'0',
					'jenis'=>'6',
					'createdby'=>$_SESSION['standard']['userid'],
					'createtime'=>date('Y-m-d h:i:s'),
					'updateby'=>$_SESSION['standard']['userid'],
					'updatetime'=>date('Y-m-d h:i:s'),
					'noreferensi'=>''
				);
					
				#Query Header Kas Kecil
				$errorDB='';
				$str = insertQuery($dbname,'keu_kaskecilht',$dataRes['header']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					$errorDB .= "Header :" . $e->getMessage() . "\n";
				}

				#Query Detail Kas Kecil
				$str = insertQuery($dbname,'keu_kaskecildt',$dataRes['detail']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Detail Bikin Detail Kas Kecil:" . $e->getMessage() . "\n";
				}
				if($errorDB!=""){
					$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>0,'saldoawal'=>0),"unit='".$unitcashdata."' and periode='".substr($tanggalcashdata,0,7)."'");
					$errCounter = "";
					try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update 5kaskecil Error :". $e->getMessage() ; }
					$sDel="delete from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."'";
						try{$owlPDO->exec($sDel); }catch (PDOException $e){  print "Gagal: Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
					exit('warning:'.$errorDB);
				}
				#semua keterangan selain dari setup keterangan
				$allket="TOP UP UNIT :".$unitcashdata.",Tanggal ".$tanggalcashdata;

				#noaruskas kas besar
				@$datadt=getArusKasket($akkasbesar,substr($tanggalcashdata,5,2),substr($tanggalcashdata,0,4));
		        @$datadt=explode('##', $datadt);
		        $noaruskaskb=$datadt[0];

		        #noaruskas kas kecil
				@$datadt=getArusKasket($akkaskecil,substr($tanggalcashdata,5,2),substr($tanggalcashdata,0,4));
		        @$datadt=explode('##', $datadt);
		        $noaruskaskk=$datadt[0];

		        #noaruskas,keterangan2,keterangan2temp ayat silang
		        @$datadt=getArusKasket($akunayatsilang,substr($tanggalcashdata,5,2),substr($tanggalcashdata,0,4));
		        @$datadt=explode('##', $datadt);
		        $noaruskasas=$datadt[0];
		        $keterangan2temp=$datadt[1];
		        $keterangan2=$datadt[2];

		        $rekening='';
				$strrek = "SELECT noakun2,rekening FROM ".$dbname.".keu_5kaskecil where periode='".$_POST['periodecash']."' and unit='".$unit."'";
				$resrek = $owlPDO->query($strrek) or die(print " Gagal: " . PDOException::getMessage());
				$resrek ->setFetchMode(PDO::FETCH_ASSOC);
				$barrek = $resrek->fetch();
				$noakunbank=$barrek['noakun2'];
				$rekening=$barrek['rekening'];

		        							############################
											###Buat Transaksi Kasbank###
											############################
		        $dataKasBank['header'] = array();
				$dataKasBank['detailKM'] = array();
				$dataKasBank['header2'] = array();
				$dataKasBank['detailKK'] = array();
				#Notransaksi Kasbank Masuk
				$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdata."/KM/";
		        $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
		        $resTrans = fetchData($qTrans);
		        if(empty($resTrans)) {
		            $notransaksikm = $noTrans."00001";
		        } else {
		            $tmpTrans = substr($resTrans[0]['notransaksi'],17,5);
		            $tmpTrans++;
		            $notransaksikm = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
		        }
		        #Header Kasbank Masuk
				$dataKasBank['header'] = array(
					'notransaksi'=>$notransaksikm,
					'tanggal'=>$tanggalcashdata,
					'tanggalpengajuan'=>$tanggalcashdata,
					'noakun'=>$akkaskecil,
					'tipetransaksi'=>'M',
					'jumlah'=>$plafoncashdata,
					'posting'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'keterangan'=>$allket."_".$akkaskecil,
					'rekonsiliasi'=>'0',
					'yn'=>'0',
					'cgttu'=>'Cash',
					'kodeorg'=>$unitcashdata,
					'userid'=>$_SESSION['standard']['userid'],
					'hutangunit'=>'0',
					'noakunhutang'=>'',
					'pemilikhutang'=>'',
					'lastupdate'=>date("Y-m-d H:i:s"),
					'nocek'=>'',
					'pembayaran'=>0,
					'tanggalinput'=>$tanggalcashdata,
					'novoucher'=>'',
					'bayarkepada'=>'',
					'rekening'=>$rekening,
					'predefined'=>'0',
					'norekpenerima'=>'',
					'namapenerima'=>'',
					'namabank'=>''
				);

				#Detail Kasbank Masuk
				$dataKasBank['detailKM'] = array(
					'notransaksi'=>$notransaksikm,
					'noakun'=>$akunayatsilang,
					'tipetransaksi'=>'M',
					'tanggal'=>$tanggalcashdata,
					'jumlah'=>$plafoncashdata,
					'noakun2a'=>$akkaskecil,
					'kode'=>'KM',
					'keterangan1'=>$notransaksi,
					'keterangan2'=>$keterangan2,
					'keterangan2temp'=>$keterangan2temp,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kurs2'=>'1',
					'noaruskas'=>$noaruskasas,
					'kodeorg'=>$unitcashdata,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'kodevhc'=>'',
					'orgalokasi'=>'',
					'nodok'=>$notransaksi,
					'hutangunit1'=>'0',
					'kodesegment'=>'',
					'tahun'=>substr($tanggalcashdata, 0,4),
					'bulan'=>substr($tanggalcashdata, 5,2),
					'keterangan3'=>''
				);

				#Query Header Kasbank Masuk
				$str = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Header :" . $e->getMessage() . "\n";
				}

				#Query Detail Kasbank Masuk
				$str = insertQuery($dbname,'keu_kasbankdt',$dataKasBank['detailKM']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
				$errorDB .= "Detail1  Kas Bank:" . $e->getMessage() . "\n";
				}

				#Notransaksi Kasbank Keluar
				if ($rekening=='') {
					$aktopupkeluar=$akkasbesar;
		        	$kodejurnalkeluartopup='KK';
					$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdata."/KK/";
			        $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
			        $resTrans = fetchData($qTrans);
			        if(empty($resTrans)) {
			              $notransaksikk = $noTrans."00001";
			        } else {
			            $tmpTrans = substr($resTrans[0]['notransaksi'],17,5);
			            $tmpTrans++;
			            $notransaksikk = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
			        }
				}else{
					$aktopupkeluar=$noakunbank;
		        	$kodejurnalkeluartopup='BK';
					$noTrans = str_replace('-','',$tanggalcashdata)."/".$unitcashdata."/".$kodejurnalkeluartopup."/";
			        $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
			        $resTrans = fetchData($qTrans);
			        if(empty($resTrans)) {
			            $notransaksikk = $noTrans."00001";
			        } else {
			            $tmpTrans = substr($resTrans[0]['notransaksi'],17,5);
			            $tmpTrans++;
			            $notransaksikk = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
			        }
				}

		        #Header Kasbank Keluar
				$dataKasBank['header2'] = array(
					'notransaksi'=>$notransaksikk,
					'tanggal'=>'0000-00-00',
					'tanggalpengajuan'=>$tanggalcashdata,
					'noakun'=>$aktopupkeluar,
					'tipetransaksi'=>'K',
					'jumlah'=>$plafoncashdata,
					'posting'=>'0',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'keterangan'=>$allket."_".$aktopupkeluar,
					'rekonsiliasi'=>'0',
					'yn'=>'0',
					'cgttu'=>'Cash',
					'kodeorg'=>$unitcashdata,
					'userid'=>$_SESSION['standard']['userid'],
					'hutangunit'=>'0',
					'noakunhutang'=>'',
					'pemilikhutang'=>'',
					'lastupdate'=>date("Y-m-d H:i:s"),
					'nocek'=>'',
					'pembayaran'=>0,
					'tanggalinput'=>$tanggalcashdata,
					'novoucher'=>'',
					'bayarkepada'=>'',
					'rekening'=>$rekening,
					'predefined'=>'0',
					'norekpenerima'=>'',
					'namapenerima'=>'',
					'namabank'=>''
				);

				#Detail Kasbank Keluar
				$dataKasBank['detailKK'] = array(
					'notransaksi'=>$notransaksikk,
					'noakun'=>$akunayatsilang,
					'tipetransaksi'=>'K',
					'tanggal'=>'0000-00-00',
					'jumlah'=>$plafoncashdata,
					'noakun2a'=>$aktopupkeluar,
					'kode'=>$kodejurnalkeluartopup,
					'keterangan1'=>$notransaksi,
					'keterangan2'=>$keterangan2,
					'keterangan2temp'=>$keterangan2temp,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kurs2'=>'1',
					'noaruskas'=>$noaruskasas,
					'kodeorg'=>$unitcashdata,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'kodevhc'=>'',
					'orgalokasi'=>'',
					'nodok'=>$notransaksi,
					'hutangunit1'=>'0',
					'kodesegment'=>'',
					'tahun'=>substr($tanggalcashdata, 0,4),
					'bulan'=>substr($tanggalcashdata, 5,2),
					'keterangan3'=>''
				);
				#Query Header Kasbank Keluar
				$str = insertQuery($dbname,'keu_kasbankht',$dataKasBank['header2']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Header :" . $e->getMessage() . "\n";
				}

				#Query Detail Kasbank Keluar
				$str = insertQuery($dbname,'keu_kasbankdt',$dataKasBank['detailKK']);
				//exit('warning'.$str);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
				$errorDB .= "Detail2  Kas Bank Kas Besar Keluar:" . $e->getMessage() . "\n";
				}
				if($errorDB!=""){
					$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>0,'saldoawal'=>0),"unit='".$unitcashdata."' and periode='".substr($tanggalcashdata,0,7)."'");
					$errCounter = "";
					try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update 5kaskecil Error :". $e->getMessage() ; }
					#roll back
					$sDel="delete from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."'";
					try{$owlPDO->exec($sDel); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
					$sDel2="delete from ".$dbname.".keu_kasbankht where notransaksi in (select distinct notransaksi from ".$dbname.".keu_kasbankdt where nodok='".$notransaksi."')";
					try{$owlPDO->exec($sDel2); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
					$sDel3="delete from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksi."'";
					try{$owlPDO->exec($sDel3); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
					exit('warning:'.$errorDB);

				}


											#############################
											####Buat Transaksi Jurnal####
											#############################

				# Prep No Jurnal Kasbank Masuk
				$kodejurnalmasuk='KM';
				$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$alokasi[$unitcashdata]."' and kodekelompok='".$kodejurnalmasuk."'");
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter']+1,3);
				$nojurnal = str_replace('-','',$tanggalcashdata)."/".$unitcashdata."/".$kodejurnalmasuk."/".$konter;

				$dataJurnal['header'] = array();
				$dataJurnal['detailDB'] = array();
				$dataJurnal['detailKR'] = array();
				$dataJurnal['header1'] = array();
				$dataJurnal['detailDB1'] = array();
				$dataJurnal['detailKR1'] = array();

				#Header Jurnal Kasbank Masuk
				$dataJurnal['header'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodejurnalmasuk,
					'tanggal'=>$tanggalcashdata,
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>$plafoncashdata,
					'totalkredit'=>($plafoncashdata*-1),
					'amountkoreksi'=>'0',
					'noreferensi'=>$notransaksi,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);

				#Detail Debet Jurnal Kasbank Masuk
				$dataJurnal['detailDB']= array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggalcashdata,
					'nourut'=>'1',
					'noakun'=>$akkaskecil,
					'keterangan'=>$allket,
					'jumlah'=>$plafoncashdata,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unitcashdata,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$notransaksi,
					'noaruskas'=>$noaruskaskk,
					'kodevhc'=>'',
					'nodok'=>$notransaksikm,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => ''
				);

				#Detail Kredit Jurnal Kasbank Masuk
				$dataJurnal['detailKR']= array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggalcashdata,
					'nourut'=>'2',
					'noakun'=>$akunayatsilang,
					'keterangan'=>$allket,
					'jumlah'=>($plafoncashdata*-1),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unitcashdata,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$notransaksi,
					'noaruskas'=>$noaruskasas,
					'kodevhc'=>'',
					'nodok'=>$notransaksikm,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => ''
				);
				$errorDB="";
				#Query Header Jurnal Kasbank Masuk
				$str = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Header :" . $e->getMessage() . "\n";
				}

				#Query Detail Debet Jurnal Kasbank Masuk
				$str = insertQuery($dbname,'keu_jurnaldt',$dataJurnal['detailDB']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
				$errorDB .= "Detail DB Jurnal Kas Masuk:" . $e->getMessage() . "\n";
				}

				#Query Detail Kredit Jurnal Kasbank Masuk
				$str = insertQuery($dbname,'keu_jurnaldt',$dataJurnal['detailKR']);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
				$errorDB .= "Detail KR :" . $e->getMessage() . "\n";
				}
				if($errorDB!=""){
					#roll back
					$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>0,'saldoawal'=>0),"unit='".$unitcashdata."' and periode='".substr($tanggalcashdata,0,7)."'");
					$errCounter = "";
					try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update 5kaskecil Error :". $e->getMessage() ; }
					$sDel="delete from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."'";
					try{$owlPDO->exec($sDel); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
					$sDel2="delete from ".$dbname.".keu_kasbankht where notransaksi in (select distinct notransaksi from ".$dbname.".keu_kasbankdt where nodok='".$notransaksi."')";
					try{$owlPDO->exec($sDel2); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
					$sDel3="delete from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksi."'";
					try{$owlPDO->exec($sDel3); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
					exit('warning:'.$errorDB);

				}
				#Query Update Counter Kelompok Jurnal
				$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),"kodeorg='".$alokasi[$unitcashdata]."' and kodekelompok='".$kodejurnalmasuk."'");
				$errCounter = "";
				try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
				# Prep No Jurnal
				$kodejurnalkeluar='KK';	
				$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$alokasi[$unitcashdata]."' and kodekelompok='".$kodejurnalkeluar."'");
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter']+1,3);
				$nojurnal = str_replace('-','',$tanggalcashdata)."/".$unitcashdata."/".$kodejurnalkeluar."/".$konter;

				#Header Jurnal Kasbank Keluar
				$dataJurnal['header1'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodejurnalkeluar,
					'tanggal'=>$tanggalcashdata,
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>$plafoncashdata,
					'totalkredit'=>($plafoncashdata*-1),
					'amountkoreksi'=>'0',
					'noreferensi'=>$notransaksi,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);

				#Detail Debet Jurnal Kasbank Keluar
				$dataJurnal['detailDB1']= array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggalcashdata,
					'nourut'=>'1',
					'noakun'=>$akunayatsilang,
					'keterangan'=>$allket,
					'jumlah'=>$plafoncashdata,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unitcashdata,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$notransaksi,
					'noaruskas'=>$noaruskasas,
					'kodevhc'=>'',
					'nodok'=>$notransaksikk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => ''
				);

				#Detail Kredit Jurnal Kasbank Keluar
				$dataJurnal['detailKR1']= array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggalcashdata,
					'nourut'=>'2',
					'noakun'=>$akkasbesar,
					'keterangan'=>$allket,
					'jumlah'=>($plafoncashdata*-1),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unitcashdata,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>'',
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi'=>$notransaksi,
					'noaruskas'=>$noaruskaskb,
					'kodevhc'=>'',
					'nodok'=>$notransaksikk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => ''
				);
				$errorDB="";

				if ($rekening=='') {
					#Query Header Jurnal Kasbank Masuk
					$str = insertQuery($dbname,'keu_jurnalht',$dataJurnal['header1']);
					try{$owlPDO->exec($str); }
					catch (PDOException $e) {
						 $errorDB .= "Header :" . $e->getMessage() . "\n";
					}

					#Query Detail Debet Jurnal Kasbank Masuk
					$str = insertQuery($dbname,'keu_jurnaldt',$dataJurnal['detailDB1']);
					try{$owlPDO->exec($str); }
					catch (PDOException $e) {
					$errorDB .= "Detail DB1 :" . $e->getMessage() . "\n";
					}

					#Query Detail Kredit Jurnal Kasbank Masuk
					$str = insertQuery($dbname,'keu_jurnaldt',$dataJurnal['detailKR1']);
					try{$owlPDO->exec($str); }
					catch (PDOException $e) {
					$errorDB .= "Detail KR1 :" . $e->getMessage() . "\n";
					}

					if($errorDB!=""){
						#roll back
						$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>0,'saldoawal'=>0),"unit='".$unitcashdata."' and periode='".substr($tanggalcashdata,0,7)."'");
						$errCounter = "";
						try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update 5kaskecil Error :". $e->getMessage() ; }
						$sDel="delete from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."'";
						try{$owlPDO->exec($sDel); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
						$sDel2="delete from ".$dbname.".keu_kasbankht where notransaksi in (select distinct notransaksi from ".$dbname.".keu_kasbankdt where nodok='".$notransaksi."')";
						try{$owlPDO->exec($sDel2); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
						$sDel3="delete from ".$dbname.".keu_jurnalht where noreferensi='".$notransaksi."'";
						try{$owlPDO->exec($sDel3); }catch (PDOException $e){ $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
						exit('warning:'.$errorDB);

					}
				}
				
				#Query Counter Kelompok Jurnal
				$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),"kodeorg='".$alokasi[$unitcashdata]."' and kodekelompok='".$kodejurnalkeluar."'");
				$errCounter = "";
				try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
				#update referensi ht kas kecil
				
				$sUpdate=updateQuery($dbname,'keu_kaskecilht',array('noreferensi'=>$notransaksikm,"notransaksi='".$notransaksi."'"));
				try{$owlPDO->exec($sUpdate); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
			break;
		}
		

		 


		

		

		

		

		

										

		

		

        

		

        


										

		

		

	break;

	/*
	case'postingawal':
		#= rekap ayat silang
		#= jurnal 1 : db : kaskecil cr : as
		#= jurnal 2 : db : as cr kasbesar
		$str="SELECT noakun,noakun2,unit from ".$dbname.".keu_5kaskecil where unit='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$akunkasbesar=$bar['noakun2'];
			$akunkaskecil=$bar['noakun'];
			$unit=$bar['unit'];
		#akun ayat silang	
		$str="SELECT nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='GL'	and kodeparameter='GLAS'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();		
			$akunayatsilang=$bar['nilai'];
		$kodejurnalkeluar='KK';	
		$kodejurnalmasuk='KM';
		$tjumlah=$jumlah;
		$tanggal=$periode.'-01';
		#= jurnal 1 ==
		#= query ambil jurnal 
		$strj =  selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
		"kodeorg='".$kept[$unit]."' and kodekelompok='".$kodejurnalmasuk."'");
		$resj = fetchData($strj);
		$counterj = addZero($resj[0]['nokounter']+1,3);
		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-","",$tanggal)."/".$unit."/".$kodejurnalmasuk."/".$counterj;			
			// exit("Error".$nojurnal);
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnalmasuk,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>$tjumlah,
				'totalkredit'=>$tjumlah*-1,
				'amountkoreksi'=>'0',
				'noreferensi'=>'',
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'    
		);
		#db kas kecil
		$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>1,
				'noakun'=>$akunkaskecil,
				'keterangan'=>'Jurnal pembentukan dana kas kecil',
				'jumlah'=>$tjumlah,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			#cr as
		$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>2,
				'noakun'=>$akunayatsilang,
				'keterangan'=>'Jurnal pembentukan dana kas kecil',
				'jumlah'=>$tjumlah*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			$str = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				 $errorDB .= "Header Ayat Silang :" . $e->getMessage() . "\n";
			}
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$str = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Detail Error ".$key." :" . $e->getMessage() . "\n";
				}
			}
		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$resj[0]['nokounter']+1),
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodejurnalmasuk."'");
		$errCounter = "";
		try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
		#== jurnal 2
		#= query ambil jurnal 
		$strj =  selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
		"kodeorg='".$kept[$unit]."' and kodekelompok='".$kodejurnalkeluar."'");
		$resj = fetchData($strj);
		$counterj = addZero($resj[0]['nokounter']+1,3);
		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-","",$tanggal)."/".$unit."/".$kodejurnalkeluar."/".$counterj;		
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnalkeluar,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>$tjumlah,
				'totalkredit'=>$tjumlah*-1,
				'amountkoreksi'=>'0',
				'noreferensi'=>'',
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'    
		);
		#db kas kecil
		$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>1,
				'noakun'=>$akunayatsilang,
				'keterangan'=>'Jurnal pembentukan dana kas kecil',
				'jumlah'=>$tjumlah,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			#cr as
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>2,
				'noakun'=>$akunkasbesar,
				'keterangan'=>'Jurnal pembentukan dana kas kecil',
				'jumlah'=>$tjumlah*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			$str = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				 $errorDB .= "Header Ayat Silang :" . $e->getMessage() . "\n";
			}
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$str = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Detail Error ".$key." :" . $e->getMessage() . "\n";
				}
			}
			$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$resj[0]['nokounter']+1),
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodejurnalkeluar."'");
			$errCounter = "";
			try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
			#update keu_5kaskecil masukan nilai ke saldo berjalan
			$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>$tjumlah),
				"unit='".$unit."' and periode='".$periode."'");
			$errCounter = "";
			try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
	break;
	*/

	case'prosescash':
	if($_POST['tipeDis']==1){
		$tmpPeriod = explode('-',$periode);
		$tahunbulan = implode("",$tmpPeriod);
		$tahun = $tmpPeriod[0];
		$bulan = $tmpPeriod[1];

		// Insert ke Saldo Awal HPP
	    $nxtBulan = ($bulan<12)? $bulan+1: 1;
	    $nxtTahun = ($bulan<12)? $tahun: $tahun+1;
	    $nxtPeriod = $nxtTahun.'-'.str_pad($nxtBulan,2,'0',STR_PAD_LEFT);
		# Prep Tahun Bulan untuk periode selanjutnya
        if($tmpPeriod[1]==12) {
            $bulanLanjut = 1;
            $tahunLanjut = $tmpPeriod[0]+1;
        } else {
            $bulanLanjut = $tmpPeriod[1]+1;
            $tahunLanjut = $tmpPeriod[0];
        }

	    $jmlHari = cal_days_in_month(CAL_GREGORIAN,$bulanLanjut,$tahunLanjut);
        $tglAwal = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-01';
        $tglTopUpt = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-28';
        $tglAkhir = $tahunLanjut.'-'.addZero($bulanLanjut,2).'-'.addZero($jmlHari,2);

		#ambil saldo awal
		$sAwal="select * from ".$dbname.".keu_5kaskecil where periode='".$periode."' and unit='".$unit."'";
		$rAwal=fetchData($sAwal);
		$isiDtAwal=$rAwal[0];

		#keluaran
		$sTransKeluar="select sum(jumlah) as keluar from ".$dbname.".keu_kaskecil_vw where tanggal like '".$periode."%' and unit='".$unit."' and jenis in (2,3) and posting=1";
		$rTransKeluar=fetchData($sTransKeluar);
		$isiDtKeluar=$rTransKeluar[0];

		#masukan
		$sTransKeluar="select sum(jumlah) as masuk from ".$dbname.".keu_kaskecil_vw where tanggal like '".$periode."%' and unit='".$unit."' and jenis=6 and posting=1";
		$rTransKeluar=fetchData($sTransKeluar);
		$isiDtMasuk=$rTransKeluar[0];
		if(count($isiDtMasuk)==0){
			exit('warning: Belum Pernah Melakukan Top Up');
		}

		$saldak=$isiDtAwal['saldoawal']+$isiDtMasuk['masuk']-$isiDtKeluar['keluar'];
		if($saldak<0){
			exit('warning: Silakan lakukan Top Up');
		}
		$errorDB="";
		$sInsert="insert into ".$dbname.".keu_5kaskecil (periode,unit,saldoawal,saldoberjalan,tanggalmulai,tanggalselesai,tanggaltopup,noakun,plafon,noakun2,batasbawah,rekening,saldoadvance,createdby) values ";
		$sInsert.=" ('".$nxtPeriod."','".$unit."','".$saldak."','".$saldak."','".$tglAwal."','".$tglAkhir."','".$tglTopUpt."','".$isiDtAwal['noakun']."','".$isiDtAwal['plafon']."','".$isiDtAwal['noakun2']."','".$isiDtAwal['batasbawah']."','".$isiDtAwal['rekening']."','".$isiDtAwal['saldoadvance']."','".$_SESSION['standard']['userid']."')";
		try{$owlPDO->exec($sInsert); }
			catch (PDOException $e) {
				 $errorDB .= " insert query :" . $e->getMessage() . "\n".$sInsert;
		}
		if($errorDB!=""){
			echo $errorDB;
			exit('warning');
		}else{
			$sUpdate="update ".$dbname.".keu_5kaskecil set close=1 where periode='".$periode."' and unit='".$unit."'";
			try{$owlPDO->exec($sUpdate); }
			catch (PDOException $e) {
				 $errorDB .= " insert query :" . $e->getMessage() . "\n".$sUpdate;
			}
			if($errorDB!=""){
				echo $errorDB;
				exit('warning');	
			}
		}
	}else if($_POST['tipeDis']==0){
		$tab="";

		if ($tanggalcashdata=='') {
			$strx="SELECT tanggaltopup from ".$dbname.".keu_5kaskecil where periode='".$periode."' and unit='".$unit."'";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			$barx=$resx->fetch();
			$tanggalcashdata=$barx['tanggaltopup'];
		}

		#ambil daftar unit yang sudah tersimpan 
		#= cek apakah sudah ada inputan / belum
		$str="SELECT count(*) as jumlahdata,unit from ".$dbname.".keu_kaskecil_vw where 
		      unit='".$unit."' and tanggal<='".$tanggalcashdata."' and noreferensiht='' and posting=1 and tipe='K' and jenis!=1 ";
		// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		@$cdatatransaksi=$bar['jumlahdata'];
			
		if($cdatatransaksi>0){

			$strx="SELECT tanggaltopup from ".$dbname.".keu_5kaskecil 
			 where periode='".$periode."' and unit='".$unit."'";
			 //exit($strx);
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			$barx=$resx->fetch();
			if($_POST['tanggalcashdata']==''){
				$_POST['tanggalcashdata']=$barx['tanggaltopup'];
			}else{
				$_POST['tanggalcashdata']=tanggalsystemn($_POST['tanggalcashdata']);
			}
			$tab.=makeElement('tanggal','label','Tanggal',array('style'=>'width:100px')).":".makeElement('tanggalcashdata','tanggal',tanggalnormal($_POST['tanggalcashdata']),array('style'=>'width:100px','onchange'=>'prosescash()'));
			$tab.=$_SESSION['lang']['penerima']." : <select style=\"width:75px;\" id=penerimacashdata>".$optpenerima."</select></br>
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td hidden align=center>".$_SESSION['lang']['notransaksi']."</td>
					<td align=center>".$_SESSION['lang']['novoucher']."</td>
					<td align=center>".$_SESSION['lang']['noaruskas']."</td>
					<td align=center>".$_SESSION['lang']['noakun']."</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td align=center>".$_SESSION['lang']['nilai']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>	
			</thead>";

			$strtgl="SELECT tanggal from ".$dbname.".keu_kaskecil_vw where tipe='M' and tanggal<'".$_POST['tanggalcashdata']."' and unit='".$unit."' order by tanggal desc limit 1";
			$restgl=$owlPDO->query($strtgl) or die(print " Gagal: ".PDOException::getMessage());
			$restgl->setFetchMode(PDO::FETCH_ASSOC);
			$bartgl=$restgl->fetch();
			$tgltopupterakhir=$bartgl['tanggal'];
			

			$str="SELECT jumlah ,jenis,jumlahdipakai,notransaksi,novoucher,noaruskas,noakun,tanggal,unit,keterangan 
				from ".$dbname.".keu_kaskecil_vw where posting=1 and tipe='K' and unit='".$unit."' and noreferensiht='' 
				and jenis!=1 and tanggal between '".$tgltopupterakhir."' and '".$_POST['tanggalcashdata']."' ";
		    //echo $str;
			$totjum=0;
			$no=0;
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$whr="kodeorganisasi='".$bar['unit']."'";
				$totjum+=floatval($bar['jumlah']);
				$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);
				@$no+=1;
				$tab.="<tr class=rowcontent id=tr_".$no.">
					<td id=notransaksicashdata_".$no." hidden>".$bar['notransaksi']."</td>
					<td id=novouchercashdata_".$no.">".$bar['novoucher']."</td>
					<td hidden id=unitcashdata_".$no.">".$bar['unit']."</td>
					<td id=noaruskascashdata_".$no.">".$bar['noaruskas']."</td>
					<td id=noakuncashdata_".$no.">".$bar['noakun']."</td>
					<td id=keterangancashdata_".$no.">".$bar['keterangan']."</td>
					<td align=right id=plafoncashdata_".$no.">".number_format($bar['jumlah'])."</td>
					<td></td>
					</tr>";//	onchange=getnoakuncash()
			}

			if($totjum>0){
				$tab.="<tr class=rowcontent style=display:none>
					<td>".$_SESSION['lang']['selisih']." ".$_SESSION['lang']['plafon']."</td>
					<td></td>
					<td></td>
					<td></td>
					<td align=right id=selisihTopup>0</td>
					<td align=center>
						&nbsp;
					</td>
					</tr>";
				$tab.="<tr class=rowcontent>
					<td>Total</td>
					<td></td>
					<td></td>
					<td></td>
					<td align=right id=totjumlah>".number_format($totjum)."</td>
					<td align=center>
						<img src='images/skyblue/save.png' class='zImgBtn' onclick=buatcash() title='Posting'>
					</td>
					</tr>";
			}
		}else{

			#noaruskas,keterangan2,keterangan2temp ayat silang
	        @$datadt=getArusKasket($akunayatsilang);
	        @$datadt=explode('##', $datadt);
	        @$noaruskasas=$datadt[0];

			$tab.="Data transaksi belum ada, sehingga harus melakukan topup cash yang bersumber dari setup";
			$tab.="<ol type=1>";
			$tab.="<li>Memiliki Saldo Awal,tidak ingin menambah saldo kas kecil,maka pada jumlah dinolkan</li>";
			$tab.="<li>Memiliki Saldo Awal,ingin menambah saldo kas kecil,maka pada jumlah diisikan</li>";
			$tab.="<li>Tidak memiliki Saldo Awal,pada saldo awal dinolkan</li>";
			$tab.="</ol>
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center hidden>".$_SESSION['lang']['notransaksi']."</td>
					<td align=center>".$_SESSION['lang']['unit']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".$_SESSION['lang']['jumlah']."</td>
					<td align=center>".$_SESSION['lang']['saldoawal']."</td>
					<td align=center hidden>".$_SESSION['lang']['novoucher']."</td>
					<td hidden align=center>".$_SESSION['lang']['noaruskas']."</td>
					<td hidden align=center>".$_SESSION['lang']['noakun']."</td>
					<td hidden align=center>".$_SESSION['lang']['keterangan']."</td>
					<td align=center>".$_SESSION['lang']['penerima']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>	
			</thead>";
			$str="SELECT * from ".$dbname.".keu_5kaskecil where close=0 and unit='".$unit."' and periode='".$periode."' and saldoberjalan=0";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){


				$tanggaldata=$bar['periode'].'01';
				$tab.="<tr class=rowcontent>
						<td id=notransaksicashdata hidden></td>
						<td hidden id=unitcashdata>".$bar['unit']."</td>
						<td id=unitcashdatanama>".$nmorg[$bar['unit']]."</td>
						<td><input type=text class=myinputtext  id=tanggalcashdata value='".tanggalnormal($tanggaldata)."' onmousemove=setCalendar(this.id) onkeypress=return false;   style=\"width:95px;\" /></td>
						<td><input  class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:75px;\" id=plafoncashdata onchange='getNilaiData(2)' value='".$bar['plafon']."' ></td>
						<td><input  class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:75px;\" id=saldoawal onchange='getNilaiData(1)' value='0' ></td>
						<td align=right hidden><input type=text class=myinputtextnumber id=novouchercashdata  style=width:100px maxlength=20 onkeypress='return_tampa_kutip(event)'></td>
						<td hidden><input type=hidden value='".$akkaskecil."' style=\"width:75px;\" id=noakuncashdata></td>
						<td hidden><input type=text  id=keterangancashdata onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:75px;\">
						<input type=text  id=noaruskascashdata onkeypress=\"return_tanpa_kutip(event);\" value='12901' class=myinputtext style=\"width:75px;\"></td>
						<td><select style=\"width:75px;\" id=penerimacashdata>".$optpenerima."</select></td>
						<td align=center>
							<img src='images/skyblue/save.png' class='zImgBtn' onclick=buatcashawal() title='Posting'>
						</td>
				</tr>";
			}
			$tab.="</table>";// onchange=getnoakuncash()
		}
		echo $tab;
	}
		
	break;	

	case'postingmasuk': 
	//indra sampe sini
	// buat insert ke kasbank
	// apakah akan langsung buat transaksi terhadap kas besar?? atau saat posting untuk tipe M (masuk)
	// untuk jurnal sudah oke.
		#= db : kaskecil , cr : ayat silang
		$str="SELECT sum(jumlah) as jumlah,noakun,noaruskas,tanggal,keterangan,unit,notransaksi,novoucher from ".$dbname.".keu_kaskecil_vw 
				where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$unit=$bar['unit'];
		$tanggal=$bar['tanggal'];
		$jumlah=$bar['jumlah'];
		$noreferensi=$bar['notransaksi'];
		$keterangan=$bar['keterangan'];
		$noakun=$bar['noakun'];
		$novoucher=$bar['novoucher'];
		$noaruskas=$bar['noaruskas'];

		$str="SELECT noakun from ".$dbname.".keu_5kaskecil where unit='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$noakunkas=$bar['noakun'];

		#= buat jurnal
		$strj =  selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
		"kodeorg='".$kept[$unit]."' and kodekelompok='".$kodejurnalmasuk."'");
		$resj = fetchData($strj);
		$counterj = addZero($resj[0]['nokounter']+1,3);
		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-","",$tanggal)."/".$unit."/".$kodejurnalmasuk."/".$counterj;		
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnalmasuk,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>$jumlah,
				'totalkredit'=>$jumlah*-1,
				'amountkoreksi'=>'0',
				'noreferensi'=>$noreferensi,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'    
		);
		#db kas kecil
		$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>1,
				'noakun'=>$noakunkas,
				'keterangan'=>$keterangan,
				'jumlah'=>$jumlah,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noreferensi,
				'noaruskas'=>$noaruskas,
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			#cr as
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>2,
				'noakun'=>$noakun,
				'keterangan'=>$keterangan,
				'jumlah'=>$jumlah*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$noreferensi,
				'noaruskas'=>$noaruskas,
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			$str = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				 $errorDB .= "Header Ayat Silang :" . $e->getMessage() . "\n";
			}
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$str = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Detail Error ".$key." :" . $e->getMessage() . "\n";
				}
			}
		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$resj[0]['nokounter']+1),
				"kodeorg='".$kept[$unit]."' and kodekelompok='".$kodejurnalmasuk."'");
		$errCounter = "";
		try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
		$queryP = updateQuery($dbname,'keu_kaskecilht',array('posting'=>1),
				"notransaksi='".$notransaksi."'");
		$errCounter = "";
		try{$owlPDO->exec($queryP); }catch (PDOException $e) { $errCounter.= "Update flag posting kaskecil :". $e->getMessage() ; }
		#= insert kasbank
		#= sebelumnya delete yang lama dulu
		$str="delete from ".$dbname.".keu_kasbankht where notransaksi='".$noreferensi."' ";	
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}		
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		$dataRes['header'] = array(
				'notransaksi'=>$noreferensi,
				'tanggal'=>$tanggal,
				'noakun'=>$noakunkas,
				'tipetransaksi'=>'M',
				'jumlah'=>$jumlah,
				'posting'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'keterangan'=>$keterangan,
				'rekonsiliasi'=>'0',
				'yn'=>'0',
				'cgttu'=>'Cash',
				'kodeorg'=>$unit,
				'userid'=>$_SESSION['standard']['userid'],
				'hutangunit'=>'0',
				'noakunhutang'=>'',
				'pemilikhutang'=>'',
				'lastupdate'=>'',
				'nocek'=>'',
				'pembayaran'=>0,
				'tanggalinput'=>$tanggal,
				'novoucher'=>$novoucher,
				'bayarkepada'=>''
		);
		$dataRes['detail'] = array(
				'notransaksi'=>$noreferensi,
				'noakun'=>$noakun,
				'tipetransaksi'=>'M',
				'tanggal'=>$tanggal,
				'jumlah'=>$jumlah,
				'noakun2a'=>$noakunkas,
				'kode'=>'KM',
				'keterangan1'=>'',
				'keterangan2'=>$keterangan,
				'keterangan2temp'=>$keterangan,
				
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kurs2'=>'1',
				'noaruskas'=>$noaruskas,
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'kodevhc'=>'',
				'orgalokasi'=>'',
				'nodok'=>'',
				'hutangunit1'=>'0',
				'kodesegment'=>'',
				'tahun'=>'',
				'bulan'=>'',
				
				'keterangan3'=>''
		);
		$str = insertQuery($dbname,'keu_kasbankht',$dataRes['header']);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			 @$errorDB .= "Insert Kas Header :" . $e->getMessage() . "\n";
		}
		$str = insertQuery($dbname,'keu_kasbankdt',$dataRes['detail']);
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			 @$errorDB .= "Insert Kas Detail :" . $e->getMessage() . "\n";
		}
		#update keu_5kaskecil masukan nilai ke saldo berjalan dan saldo awal
		#cek apakah sudah ada saldo awalnya?
		$str="select saldoberjalan from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".substr($tanggal,0,7)."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$saldoberjalan=$bar['saldoberjalan'];
		
		$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>$saldoberjalan+$jumlah),
						"unit='".$unit."' and periode='".substr($tanggal,0,7)."'");
			
		$errCounter = "";
		try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update saldo berjalan :". $e->getMessage() ; }
	break;
	
	case'postingkeluar':

		$str="select * from ".$dbname.".listfile_keu_kaskecil where notransaksi = '".$notransaksi."' and status='1'";
        $res=fetchData($str);
        if(empty($res)){
            exit('warning : upload file terlebih dahulu.');
        }

		if($jenis==1){
		
			#= update saldo advance saja
			$str="SELECT sum(jumlah) as jumlah,unit,tanggal from ".$dbname.".keu_kaskecil_vw 
					where notransaksi='".$notransaksi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$jumlah=$bar['jumlah'];
				$unit=$bar['unit'];
				$tanggal=$bar['tanggal'];

			$strz="select batasbawah,saldoberjalan from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".substr($tanggal,0,7)."' ";
			$resz=$owlPDO->query($strz) or die(print " Gagal: ".PDOException::getMessage());
			$resz->setFetchMode(PDO::FETCH_ASSOC);
			$barz=$resz->fetch();
			$saldoberjalan=$barz['saldoberjalan'];
			$batasbawah=$barz['batasbawah'];
			//exit($batasbawah);
			if($batasbawah > 0){
			if(($saldoberjalan-$jumlah) < $batasbawah)
			{
				exit("Saldo Tidak Mencukupi Silahkan Top Up");
			}
			}
			#cek data lama
			$str="SELECT saldoberjalan,periode,saldoadvance from ".$dbname.".keu_5kaskecil where unit='".$unit."' order by periode desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				@$saldoadvance=$bar['saldoadvance'];
			
			#update query
			$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoadvance'=>$saldoadvance+$jumlah),
					"unit='".$unit."' and periode='".substr($tanggal,0,7)."'");
			$errCounter = "";
			try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update saldo berjalan :". $e->getMessage() ; }
			
			#= update flag posting
			$queryP = updateQuery($dbname,'keu_kaskecilht',array('posting'=>1),
					"notransaksi='".$notransaksi."'");
			$errCounter = "";
			try{$owlPDO->exec($queryP); }catch (PDOException $e) { $errCounter.= "Update flag posting kaskecil :". $e->getMessage() ; }
		
		}else{
			
			#= buat jurnal
			

			$str="SELECT noakun,noakun2,unit from ".$dbname.".keu_5kaskecil where unit in 
					(select unit from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$noakunkas=$bar['noakun'];
			$str="SELECT sum(jumlahdipakai) as jumlahdipakai,sum(jumlah) as jumlah,noakun,noaruskas,tanggal,keterangan,unit,notransaksi,novoucher from ".$dbname.".keu_kaskecil_vw 
					where notransaksi='".$notransaksi."' group by noaruskas,noakun;";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$jumlah=$bar['jumlah'];
				@$tjumlah+=$jumlah;
				$tanggal=$bar['tanggal']; //buat ditampung kebawah
				$unit=$bar['unit'];
				$keterangan=$bar['keterangan'];
				$noakun=$bar['noakun'];
				$noaruskas=$bar['noaruskas'];
				$noreferensi=$bar['notransaksi'];
				$novoucher=$bar['novoucher'];
				
			}
			$strz="select batasbawah,saldoberjalan from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".substr($tanggal,0,7)."' ";
			$resz=$owlPDO->query($strz) or die(print " Gagal: ".PDOException::getMessage());
			$resz->setFetchMode(PDO::FETCH_ASSOC);
			$barz=$resz->fetch();
			$saldoberjalan=$barz['saldoberjalan'];
			$batasbawah=$barz['batasbawah'];
			//exit($batasbawah);
			if($batasbawah > 0){
				if(($saldoberjalan) < $batasbawah)
				{
					exit("Saldo Tidak Mencukupi Silahkan Top Up");
				}
			}

			$queryP = updateQuery($dbname,'keu_kaskecilht',array('posting'=>1),
					"notransaksi='".$noreferensi."'");
			$errCounter = "";
			try{$owlPDO->exec($queryP); }catch (PDOException $e) { $errCounter.= "Update flag posting kaskecil :". $e->getMessage() ; }
			#= buat kas 
			#= sebelumnya delete yang lama dulu
			
			#cek apakah sudah ada saldo awalnya?
			$str="select saldoadvance,saldoberjalan from ".$dbname.".keu_5kaskecil where unit='".$unit."' and periode='".substr($tanggal,0,7)."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$saldoberjalan=$bar['saldoberjalan'];
				$saldoadvance=$bar['saldoadvance'];
			
			if($jenis==2){
				#klo pertanggung jawaban mengurangi saldo advance
				$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>$saldoberjalan-$tjumlah,'saldoadvance'=>$saldoadvance-$tjumlah),
					"unit='".$unit."' and periode='".substr($tanggal,0,7)."'");
			}else{
				$queryK = updateQuery($dbname,'keu_5kaskecil',array('saldoberjalan'=>$saldoberjalan-$tjumlah),
					"unit='".$unit."' and periode='".substr($tanggal,0,7)."'");
			}
			$errCounter = "";
			try{$owlPDO->exec($queryK); }catch (PDOException $e) { $errCounter.= "Update saldo berjalan :". $e->getMessage() ; }
			
			##################
			## Insert E-Fill##
			##################
			$createdtime=date('Y-m-d H:i:s');
			$optUnit = makeOption($dbname,'filemanager','namafile,id',"namafile='".$unit."'");
			$idunit = $optUnit[$unit];
			
			$efilnotrans = str_replace('/','',$notransaksi)." (KK)";
			$structure = setlocationfile($idunit)."/KAS KECIL/".substr($tanggal,0,7)."/".$efilnotrans;
			
			deleteefil($notransaksi." (KK)",$structure);
			
			$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".getidfrompath($structure,'1')."','4','".$notransaksi." (KK)','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
			$owlPDO->exec($str);
			$idresult = $owlPDO->lastInsertId();
			if (!mkdir($structure, 0777, true)){}
			
			$path   = "fileupload/kaskecil/";
			$str="select * from ".$dbname.".listfile_keu_kaskecil where notransaksi = '".$notransaksi."' and status='1'";
			$res=fetchData($str);
			foreach($res as $key=>$val){
				$efilename = "Kas Kecil ".$notransaksi." ".$val['namafile'];
				$formaticon = $val['formaticon'];
				$strx="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','".$formaticon."','','1','','".$createdtime."','','".$createdtime."','KK')";
				$owlPDO->exec($strx);
				
				$structureto = $structure."/".str_replace('/','',$efilename);
				copy($path."".$val['namafile'], $structureto);
			}
		
			$efilename = "Payment Voucher ".$notransaksi.".pdf";
			$strx="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','5','".$efilename."','.pdf','','1','','".$createdtime."','','".$createdtime."','KK')";
			$owlPDO->exec($strx);
			// detailPDF('".$bar['notransaksi']."','".$bar['unit']."','".$bar['tipe']."','112102',event)
			
			$str="select * from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);

			$_GET['proses'] = 'pdf';
			$_GET['notransaksi'] = $notransaksi;
			$_GET['kodeorg'] = $res[0]['unit'];
			$_GET['tipetransaksi'] = $res[0]['tipe'];
			$_GET['noakun'] = "112102";
			$_GET['urlefil'] = $structure."/".str_replace('/','',$efilename);
								
			include("keu_slave_kaskecil_print_detail.php");
			
			unset($_GET['proses']);
			unset($_GET['notransaksi']);
			unset($_GET['kodeorg']);
			unset($_GET['tipetransaksi']);
			unset($_GET['noakun']);
			unset($_GET['urlefil']);
		}
		echo 'succes';
	break;
	case'postinglama':
		$str="SELECT noakun,noakun2,unit from ".$dbname.".keu_5kaskecil where unit in 
				(select unit from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$akunkasbesar=$bar['noakun2'];
			$akunkaskecil=$bar['noakun'];
			$unit=$bar['unit'];
		#akun ayat silang	
		$str="SELECT nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='GL'	and kodeparameter='GLAS'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();		
			$akunayatsilang=$bar['nilai'];
		$kodejurnalkeluar='KK';	
		$kodejurnalmasuk='KM';
		$str="SELECT sum(jumlah) as jumlah,sum(jumlahdipakai) as jumlahdipakai,noakun,noaruskas,tanggal,keterangan from ".$dbname.".keu_kaskecil_vw 
				where notransaksi='".$notransaksi."'  group by noaruskas,noakun;";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$tjumlah+=$bar['jumlahdipakai'];
			$tanggal=$bar['tanggal']; //buat ditampung kebawah
			$strkj =  selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$kept[$unit]."' and kodekelompok='".$kodejurnalkeluar."'");
			$resj = fetchData($strkj);
			$counterj = addZero($resj[0]['nokounter']+1,3);
			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-","",$bar['tanggal'])."/".$unit."/".$kodejurnalkeluar."/".$counterj;		
			$dataRes['header'] = array();
			$dataRes['detail'] = array();
			$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnalkeluar,
				'tanggal'=>$bar['tanggal'],
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>$tjumlah,
				'totalkredit'=>$tjumlah*-1,
				'amountkoreksi'=>'0',
				'noreferensi'=>$notransaksi,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'    
			);
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$bar['tanggal'],
				'nourut'=>1,
				'noakun'=>$bar['noakun'],
				'keterangan'=>$bar['keterangan'],
				'jumlah'=>$bar['jumlahdipakai'],
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$notransaksi,
				'noaruskas'=>$bar['noaruskas'],
				'kodevhc'=>'',
				'nodok'=>$invoice,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$bar['tanggal'],
				'nourut'=>2,
				'noakun'=>$akunkaskecil,
				'keterangan'=>$bar['keterangan'],
				'jumlah'=>$bar['jumlahdipakai']*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>$notransaksi,
				'noaruskas'=>$bar['noaruskas'],
				'kodevhc'=>'',
				'nodok'=>$invoice,
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			$str = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				 $errorDB .= "Header Jurnal :" . $e->getMessage() . "\n";
			}
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$str = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Detail Error ".$key." :" . $e->getMessage() . "\n";
				}
			}
			$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$resj[0]['nokounter']+1),
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodejurnalkeluar."'");
			$errCounter = "";
			try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
		}
		#= rekap ayat silang
		#= jurnal 1 : db : kaskecil cr : as
		#= jurnal 2 : db : as cr kasbesar
		#= jurnal 1 ==
		#= query ambil jurnal 
		$strj =  selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
		"kodeorg='".$kept[$unit]."' and kodekelompok='".$kodejurnalmasuk."'");
		$resj = fetchData($strj);
		$counterj = addZero($resj[0]['nokounter']+1,3);
		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-","",$tanggal)."/".$unit."/".$kodejurnalmasuk."/".$counterj;		
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnalmasuk,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>$tjumlah,
				'totalkredit'=>$tjumlah*-1,
				'amountkoreksi'=>'0',
				'noreferensi'=>'',
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'    
		);
		#db kas kecil
		$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>1,
				'noakun'=>$akunkaskecil,
				'keterangan'=>'Pindah KAS',
				'jumlah'=>$tjumlah,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			#cr as
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>2,
				'noakun'=>$akunayatsilang,
				'keterangan'=>'Pindah Kas',
				'jumlah'=>$tjumlah*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			$str = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				 $errorDB .= "Header Ayat Silang :" . $e->getMessage() . "\n";
			}
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$str = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Detail Error ".$key." :" . $e->getMessage() . "\n";
				}
			}
		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$resj[0]['nokounter']+1),
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodejurnalmasuk."'");
		$errCounter = "";
		try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }	
		#== jurnal 2
		#= query ambil jurnal 
		$strj =  selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
		"kodeorg='".$kept[$unit]."' and kodekelompok='".$kodejurnalkeluar."'");
		$resj = fetchData($strj);
		$counterj = addZero($resj[0]['nokounter']+1,3);
		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-","",$tanggal)."/".$unit."/".$kodejurnalkeluar."/".$counterj;		
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnalkeluar,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>$tjumlah,
				'totalkredit'=>$tjumlah*-1,
				'amountkoreksi'=>'0',
				'noreferensi'=>'',
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'    
		);
		#db kas kecil
		$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>1,
				'noakun'=>$akunayatsilang,
				'keterangan'=>'Pindah KAS',
				'jumlah'=>$tjumlah,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			#cr as
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>2,
				'noakun'=>$akunkasbesar,
				'keterangan'=>'Pindah Kas',
				'jumlah'=>$tjumlah*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>'',
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi'=>'',
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' =>''
			);
			$str = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				 $errorDB .= "Header Ayat Silang :" . $e->getMessage() . "\n";
			}
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$str = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					 $errorDB .= "Detail Error ".$key." :" . $e->getMessage() . "\n";
				}
			}
			$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$resj[0]['nokounter']+1),
				"kodeorg='".$_SESSION['org']['kodeorganisasi']."' and kodekelompok='".$kodejurnalkeluar."'");
			$errCounter = "";
			try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
		#= update flag posting
		$str = updateQuery($dbname,'keu_kaskecilht',array('posting'=>1),
				"notransaksi='".$notransaksi."'");
		$errCounter = "";
		try{$owlPDO->exec($str); }catch (PDOException $e) { $errCounter.= "Update falg posting Error :". $e->getMessage() ; }
	break;
	case'update':

		$str="update ".$dbname.".keu_kaskecildt set noaruskas='".$noaruskas."',noakun='".$noakun."',
				keterangan='".$keterangan."',penerima='".$penerima."',jumlahditerima='".$jumlahditerima."',jumlahdipakai='".$jumlahdipakai."',
				jumlah='".$jumlah."',saldoberjalan='".$saldoberjalan."',updateby='".$_SESSION['standard']['userid']."',
				keterangan2='".$keterangan2."'
				where nourut='".$nourut."' and notransaksi='".$notransaksi."' and novoucher='".$novoucher."'";
		// exit('warning '.$str);
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
            die();
		}
		echo $notransaksi;
	break;
	case'detailcashtopup';
		$tab="";
		$tab.="<table align=left cellspacing=1 border=0 cellpading=1>
			<thead><tr>
				<td align=center>".$_SESSION['lang']['noakun']."</td>
				<td align=center>".$_SESSION['lang']['namaakun']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['debet']."</td>
				<td align=center>".$_SESSION['lang']['kredit']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".keu_kaskecildt where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$tab.="<tr class=rowcontent>
				<td align=left>".$bar['noakun']."</td>
				<td align=left>".$nmakun[$bar['noakun']]."</td>
				<td align=left>".$bar['keterangan']."</td>
				<td align=right>".number_format($bar['jumlahdipakai'])."</td>
				<td align=right>".number_format(0)."</td>
			</tr>";
			@$tjumlah+=$bar['jumlahdipakai'];
		}
		$str="SELECT noakun,noakun2 from ".$dbname.".keu_5kaskecil where unit in 
				(select unit from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$akunkasbesar=$bar['noakun2'];
			$akunkaskecil=$bar['noakun'];
		$tab.="<tr class=rowcontent>
			<td align=left>".$akunkaskecil."</td>
			<td align=left>".$nmakun[$akunkaskecil]."</td>
			<td align=left></td>
			<td align=right>".number_format(0)."</td>
			<td align=right>".number_format($tjumlah)."</td>
		</tr>";
		$tab.="<tr class=rowcontent>
			<td align=left colspan=5>-------</td>
		</tr>";
		$tab.="<tr class=rowcontent>
			<td align=left>".$akunkaskecil."</td>
			<td align=left>".$nmakun[$akunkaskecil]."</td>
			<td align=left></td>
			<td align=right>".number_format($tjumlah)."</td>
			<td align=right>".number_format(0)."</td>
		</tr>";
		$tab.="<tr class=rowcontent>
			<td align=left>".$akunkasbesar."</td>
			<td align=left>".$nmakun[$akunkasbesar]."</td>
			<td align=left></td>
			<td align=right>".number_format(0)."</td>
			<td align=right>".number_format($tjumlah)."</td>
		</tr>";
		$tab.="</table><br>";
		echo $tab;
	break;
	case'getnoakun':
		$optket=$optnoakun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		// $str="SELECT a.noaruskas, a.noakun,b.namaakun FROM ".$dbname.".keu_5aruskas_detail a
		// 		LEFT JOIN ".$dbname.".`keu_5akun` b on a.noakun=b.noakun 
		// 		where a.noaruskas='".$noaruskas."' and a.noakun NOT IN ('115' , '127' , '128') and left(a.noakun,1)<>'2'";
		$str="SELECT a.noaruskas, a.noakun,b.namaakun FROM ".$dbname.".keu_5aruskas_detail a
				LEFT JOIN ".$dbname.".`keu_5akun` b on a.noakun=b.noakun where a.noaruskas='".$noaruskas."'";
		$res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) {
			if($noakun==$bar['noakun']){
				$stata='selected';
			}else{
				$stata='';
			}
			$optnoakun.="<option value='".$bar['noakun']."' ".$stata.">".$bar['noakun']."-".$bar['namaakun']."</option>";
		}

		$str="select keterangan,id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskas."'";
	    $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	    $res->setFetchMode(PDO::FETCH_ASSOC);
	    while ($bar=$res->fetch()) {
	    	if($keterangan==$bar['id_ket']){
				$stata='selected';
			}else{
				$stata='';
			}
			$optket.="<option value='".$bar['id_ket']."' ".$stata.">".$bar['keterangan']."</option>";
	    }
	    if($novoucher!=""){
	    	$sKet="select keterangan2 from ".$dbname.".keu_kaskecil_vw where novoucher='".$novoucher."' and unit='".$unit."'";
	    	$rKet=fetchData($sKet);
	    }
	    if($novoucher!=""){
	    	echo $optnoakun."####".$optket."####".$rKet[0]['keterangan2'];
	    }else{
	    	echo $optnoakun."####".$optket;	
	    }
		
	break;
	case'loaddatadetail':
		$tab="";
		$tab="<table cellspacing=1 border=0>
			<thead><tr>
				<td hidden align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['jenis']."</td>
				<td align=center>".$_SESSION['lang']['novoucher']."</td>
				<td align=center>".$_SESSION['lang']['noaruskas']."</td>
				<td align=center>".$_SESSION['lang']['noakun']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['penerima']."</td>
				<td hidden align=center>".$_SESSION['lang']['jumlahditerima']."</td>
				<td align=center>".$_SESSION['lang']['jumlahdipakai']."</td>
				<td hidden align=center>".$_SESSION['lang']['perubahan']."</td>
				<td hidden align=center>".$_SESSION['lang']['saldoberjalan']."</td>
				<td align=center>".$_SESSION['lang']['noreferensi']."</td>
				<td align=center colspan=2>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		#= ambil data pendukung sumber dari notransaksi
		$str="SELECT unit,periode,tanggal from ".$dbname.".keu_kaskecil_vw where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$unit=$bar['unit'];
			$periode=$bar['periode'];
			$tanggal=$bar['tanggal'];
		#= perhitungan
		$str="SELECT saldoawal,periode from ".$dbname.".keu_5kaskecil where unit='".$unit."' order by periode desc limit 1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$sawal=$bar['saldoawal'];
			@$periode=$bar['periode'];
		#= tipe masuk	
		$str="SELECT jumlahditerima,jumlahdipakai,tipe from ".$dbname.".keu_kaskecil_vw where unit='".$unit."' and tanggal < '".$tanggal."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tipe']=='M'){
				@$penambah+=$bar['jumlahditerima'];
			}else{
				if($bar['jumlahdipakai']=='0'){
					@$pengurang+=$bar['jumlahditerima'];
				}else{
					@$pengurang+=$bar['jumlahdipakai'];
				}
			}
		}
		@$saldoberjalan=$sawal+$penambah-$pengurang;		
		$str="SELECT * from ".$dbname.".keu_kaskecildt where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

			//$opketerangan=makeOption($dbname,'keu_5keterangan','id_ket,keterangan'," id_ket='".$bar['keterangan']."'");
			$optaruskasDt=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas'," noaruskas='".$bar['noaruskas']."'");

			#= saldo berjalan
			$saldoberjalan=$saldoberjalan-$bar['jumlahdipakai'];
			@$no+=1;
			//<td>".$opketerangan[$bar['keterangan']]."</td>
			$tab.="<tr class=rowcontent>
				<td hidden>".$bar['notransaksi']."</td>
				<td>".$bar['nourut']."</td>
				<td>".$arrjenis[$bar['jenis']]."</td>
				<td>".$bar['novoucher']."</td>
				<td>".$optaruskasDt[$bar['noaruskas']]."</td>
				<td>".$bar['noakun']."-".$nmakun[$bar['noakun']]."</td>
				
				<td>".$bar['keterangan2']."</td>
				<td>".$nmkar[$bar['penerima']]."</td>
				<td hidden align=right>".number_format($bar['jumlahditerima'])."</td>
				<td hidden align=right>".number_format($bar['jumlahdipakai'])."</td>
				<td   align=right>".number_format($bar['jumlah'])."</td>
				<td align=right hidden>".number_format($saldoberjalan)."</td>
				<td>".$bar['noreferensi']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt(
					'".$bar['nourut']."','".$bar['jenis']."','".$bar['noreferensi']."','".$bar['noaruskas']."','".$bar['noakun']."','".$bar['keterangan']."',
					'".$bar['penerima']."','".$bar['jumlahditerima']."','".$bar['jumlahdipakai']."','".$bar['jumlah']."',
					'".$bar['saldoberjalan']."','".$bar['novoucher']."')\">
				</td>
				<td align=center>
				<img src=images/skyblue/delete.png class=resicon title=Delete onclick=deleteDt('".$bar['notransaksi']."','".$bar['nourut']."','".$bar['novoucher']."')>
				</td>
				</tr>";
			$saldoberjalan=$saldoberjalan;
		}
		echo $tab."####".$saldoberjalan;
	break;
	case 'insert':
		$closing=str_replace(",","",$closing);
		#= cek saldo awal
		if(($closing-$jumlah)<=0){
			exit("Warning:Sisa Saldo Tidak Mencukupi, Silahkan lakukan cash top up".($closing-$jumlah)."__".$closing."__".$jumlah);
		}
		if(intval($jumlah)==0){
			exit('warning: '.$_SESSION['lang']['notifemptyzero']);
		}
		$qtanggaltopup = selectQuery($dbname,'keu_5kaskecil','*',
		"unit='".$unit."' and periode='".substr($tanggal, 0,7)."'");
		$rstanggaltopup = fetchData($qtanggaltopup);
		/*if($tanggal > $rstanggaltopup[0]['tanggaltopup'])
		{
			exit("warning : input tanggal sebelum tanggal top up.");
		}*/

		if ($jenis=='') {
			exit("warning : Jenis tidak boleh kosong.");
		}

		#= cek apakah ada transaksi yang tanggalnya lebih maju
		$str = "SELECT count(*) as data,notransaksi FROM ".$dbname.".keu_kaskecil_vw where tanggal>'".$tanggal."' and unit='".$unit."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$data=$bar['data'];
			$notransaksiblumposting=$bar['notransaksi'];
		if($data>0){
			exit("Warning:sudah ada transaksi terakhir ditanggal ".tanggalnormal($tanggal));
		}	
		
		#= cek apakah ada transaksi belum posting
		$str = "SELECT count(*) as data,notransaksi FROM ".$dbname.".keu_kaskecil_vw where notransaksi!='".$notransaksi."' and 
				posting=0 and unit='".$unit."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$data=$bar['data'];
			$notransaksiblumposting=$bar['notransaksi'];
		if($data>0){
			exit("Warning:Ada transaksi yang belum diposting, Silahkan posting transaksi ".$notransaksiblumposting);
		}	
	
		
		#= cek jika jenisnya 1 (advance) tidak boleh melebihi saldo advance
		/*if($jenis==2){
			if($jumlah>$advance){
				exit("Warning:Nilai Melebihi Advance");
			}
		}*/
		
		#buat notransaksi baru
		if($notransaksi==''){
			$noTrans = str_replace('-','',$tanggal)."/".$unit."/KK/C";
			$qTrans = selectQuery($dbname,'keu_kaskecilht','notransaksi',
													  "notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
			$resTrans = fetchData($qTrans);
			if(empty($resTrans)) {
					$notransaksi = $noTrans."00001";
			} else {
					$tmpTrans = substr($resTrans[0]['notransaksi'],18,5);
					$tmpTrans++;
					$notransaksi = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
			}
			#= insert ht
			$str="insert into ".$dbname.".keu_kaskecilht (tipe,notransaksi,tanggal,unit,createdby,createtime)
				  values('K','".$notransaksi."','".$tanggal."','".$unit."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal : ".addslashes($e->getMessage());
			}
		}		
		$etglcash=explode('-',$tanggal);
		$noTrans='PCV-'.$etglcash[0].$etglcash[1];	
		$qTrans = selectQuery($dbname,'keu_kaskecil_vw','novoucher',"unit='".$unit."' and novoucher like '".$noTrans."%'","novoucher desc",true,1,1);
		//exit('warning'.$qTrans);
		$resTrans = fetchData($qTrans);
		if(empty($resTrans)) {
				$novoucher = $noTrans."001";
		} else {
				$tmpTrans = intval(substr($resTrans[0]['novoucher'],10,3))+1;
				$novoucher = $noTrans.str_pad($tmpTrans,3,'0',STR_PAD_LEFT);
		}	
		#= buat count untuk nourut
		$str = "SELECT count(*) as jumlah FROM ".$dbname.".keu_kaskecildt where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			@$maxnum=$bar['jumlah'];
		if(@$maxnum>0){
			@$nourut=$maxnum+1;
		}else{
			$nourut=1;
		}
		#= insert dt
		$str="insert into ".$dbname.".keu_kaskecildt (notransaksi,nourut,noaruskas,noakun,keterangan,keterangan2,
				penerima,jumlahditerima,jumlahdipakai,jumlah,saldoberjalan,createdby,createtime,novoucher,jenis,noreferensi,updateby)
			  values('".$notransaksi."','".$nourut."','".$noaruskas."','".$noakun."','".$keterangan."','".$keterangan2."','".$penerima."',
			  '".$jumlahditerima."','".$jumlahdipakai."','".$jumlah."','".$saldoberjalan."','".$_SESSION['standard']['userid']."',
			  '".date('Y-m-d H:i:s')."','".$novoucher."','".$jenis."','".$noreferensi."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage()."__".$str);
		}
		#= prepare untuk insert kasbank
		#= cek apakah sudah ada inputan 
		// $str = "SELECT count(*) as jumlahdata FROM ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
		// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar = $res->fetch();
		// 	$jumlahdata=$bar['jumlahdata'];
		// if($jumlahdata<1){
		// 	$str="insert into ".$dbname.".keu_kasbankht (notransaksi,tanggal,noakun,tipetransaksi,kodeorg,posting)
		// 		  values('".$notransaksi."','".$tanggal."','','K','".$unit."','1')";
		// 	try{
		// 		$owlPDO->exec($str); 
		// 	} catch (PDOException $e){
		// 		echo " Gagal : ".addslashes($e->getMessage());
		// 	}
		// }	
		echo $notransaksi;
	break;
	case 'loaddata':
		if($notransaksisch!=''){
			$where.=" and notransaksi like '%".$notransaksisch."%'";
		}
		if($tanggalsch!=''){
			$where.=" and tanggal = '".$tanggalsch."'";
		}

        /*if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            $where.="";
        }else{
            $where.=" and unit='".$_SESSION['empl']['lokasitugas']."'";
        }*/
    
        $where.= "and unit in (".getOrgDetail(2).")";

		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".keu_kaskecil_vw where 1=1 ".$where." ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT sum(jumlahditerima) as jumlahditerima,sum(jumlahdipakai) as jumlahdipakai,
					sum(jumlah) as jumlah,notransaksi,tanggal,unit,posting,tipe,jenis,noreferensiht
					from ".$dbname.".keu_kaskecil_vw
			where 1=1 ".$where." group by notransaksi order by tanggal desc,posting asc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whr="kodeorganisasi='".$bar['unit']."'";

				//$nokasbank=makeOption($dbname,'keu_kasbankdt','keterangan1,notransaksi',"keterangan1='".$bar['notransaksi']."'");
                $optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);
                @$no+=1;
                $tab.="<tr class=rowcontent>";
                     $tab.="<td style='text-align:center;'>".$no."</td>";
                     $tab.="<td>".$bar['notransaksi']."</td>";
                     $tab.="<td>".$bar['noreferensiht']."</td>";
                     $tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
                     $tab.="<td>".$bar['unit']."</td>";
                     $tab.="<td>".$bar['tipe']."</td>";
					 // if($bar['tipe']=='M'){
						 // $tab.="<td align=right>".number_format($bar['jumlah'])."</td>";
					 // }else{
						 // $tab.="<td align=right>".number_format($bar['jumlahdipakai'])."</td>";
					 // }
					 
					 $tab.="<td align=right>".number_format($bar['jumlah'])."</td>";
					 $tab.="<td align=center>";
					 if($bar['posting']==0){
						$tab.="
                    	<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editht(
						'".$bar['notransaksi']."','".$bar['unit']."','".tanggalnormal($bar['tanggal'])."')\">
						<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteht(
						'".$bar['notransaksi']."')\">
						"; $tab.="<img src='images/skyblue/posting.png' class='zImgBtn' onclick=posting('".$bar['notransaksi']."','".$bar['tipe']."','".$bar['jenis']."') title='Posting'>";
						 
					 }else{
						// $tab.="<img src='images/skyblue/posted.png' class='resicon' title='Posted'>"; 
					 }
					 $tab.="<img src='images/upload-2-xxl.png' class='zImgBtn' onclick=showupload(event,'".$bar['notransaksi']."','".$bar['tipe']."','".$bar['jenis']."','".$bar['posting']."') title='Upload Document'>";
					  $tab.="<img src='images/skyblue/zoom.png' class='zImgBtn' onclick=popupdetail('detaildata','3','".$bar['notransaksi']."','".$bar['unit']."','".substr($bar['tanggal'],0,7)."') title='Preview'>";
					  $tab.="<img src=images/pdf.jpg class=resicon  title='Print'  onclick=detailPDF('".$bar['notransaksi']."','".$bar['unit']."','".$bar['tipe']."','112102',event)>";
					  
					$tab.="</td>";
                     $tab.="</td>";
                	 $tab.="</tr>";
            }
            $totrows=ceil($jlhbrs/$limit);
            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=10 align=center>
                <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
    case 'setketerangan':
    echo $arrakun[$noakun];
    break;
    case 'showupload':
        $tab="";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['notransaksi']."</td>
                <td>:</td>
                <td>
                    <label id='noupload' style='display:none'>".$notransaksi."</label>
                    <label style='font-weight:bold'>".$notransaksi."</label>
                </td>
            </tr>";

        $tab.="<tr><td colspan=4><hr></td></tr>
                <tr>
                    <td>Filename</td>
                    <td>:</td>
                    <td>
                        <input type='file' name='upload' id='upload' >
                    </td>
                </tr>";
        if($posting>0)
        {
            $tab.="<tr hidden>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=\"submitfile()\">Submit</button>
                    </td>
                </tr>";
        }
        else
        {
            $tab.="<tr>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=\"submitfile()\">Submit</button>
                    </td>
                </tr>";
        }
        
        $tab.="</table>
            <p />";
            
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center' width=50px>No.</td>
                    <td align='center' width=50px>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center' width=50px>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";
            
        echo $tab;
    break;
    case 'submitfile':
        $tgl = date("YmdHis");
        $his = date("His");
        $nmTemp=str_replace('-','',str_replace('/','',$notransaksi));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
        if($fileupload!=''){
            if($_FILES['file']['error']==0){    
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = $nmTemp."_".$his."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                        $str = "insert into ".$dbname.".listfile_keu_kaskecil values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                        try{
                            $owlPDO->exec($str);
                            if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                            file_put_contents($path.$filename,$file_tmpname);
                        }
                        catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                }else{
                    exit("Warning : Format file upload tidak boleh ".$filetype);
                }
            }
        }
    break;
    case'viewfile':
        $tab="";
        $tab.="<img src='".$path.$data['namafile']."' style='width:600px;height:400px;'>";
        
        echo $tab;
    break;
    
    case 'deletefile':
        $namafile=$namafile;
        $str="delete from ".$dbname.".listfile_keu_kaskecil where notransaksi='".$notransaksi."' and namafile='".$namafile."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    case'viewlistfile':
        $tab.="<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>
                <table class='sortable' cellspacing='1' border='0' style=min-width:350px>
                    <thead>
                    <tr class=rowheader>
                        <td align='center' width=50px>No.</td>
                        <td align='center' width=50px>File Type</td>
                        <td align='center'>Filename</td>
                        <td align='center' width=50px>Action</td>
                    </tr>
                    </thead>
                    <tbody id='loadfilesdetail'>
                    </tbody>
                </table>
            </fieldset> ";
        echo $tab;
    break;
    
    case 'deletefileall':
        $str="select * from ".$dbname.".listfile_keu_kaskecil where notransaksi='".$notransaksi."'"; //exit('error'.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $pathx = $path.$bar['namafile'];
            unlink($pathx);
        }
        
        $str="delete from ".$dbname.".listfile_keu_kaskecil where notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($str);
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    case 'loadfiles':
        $no = 0;
        $tab = "";  
        
        
        $str="select * from ".$dbname.".listfile_keu_kaskecil where notransaksi = '".$notransaksi."' and status='1'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                @$icon = seticonfile($val['formaticon']);   
                $tab.="<td style='text-align:center'>
                    <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";

                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                    <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($posting==0){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";                 
                }
                
                $tab."  </td>
                </tr>";
            }   
        }
        
        echo $tab;
    break;
    case 'getnoref':
    $str="SELECT notransaksi, novoucher from ".$dbname.".keu_kaskecil_vw where novoucher='".$notransaksi."' and unit='".$unit."' and jenis='1' ";
    //exit('warning'.$str);
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();

    echo $bar['novoucher'].'###'.$bar['notransaksi'];
    break;
	case'getdatanoref':
	
		
			$data="";
			$data.= "<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
			$data.="<table cellpadding=0 cellspacing=1 width=100% class=sortable>";
			$data.= "<thead><tr>";
			$data.= "<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
			$data.= "<td align=center>".$_SESSION['lang']['novoucher']."</td>";
			$data.= "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
			$data.= "</tr></thead>";
			$whrdt="";
			if($novoucher!=''){
				$whrdt=" and a.novoucher like '".$novoucher."' ";
			}	
			 
			$str="SELECT a.notransaksi, a.novoucher, a.noreferensi,a.jumlah from ".$dbname.".keu_kaskecildt a 
						left join  ".$dbname.".keu_kaskecilht b on a.notransaksi=b.notransaksi 
                        WHERE b.unit ='".$unit."' and a.jenis='1' ".$whrdt;
			#data
			//exit($str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$sqle = selectQuery($dbname,"keu_kaskecildt","sum(jumlah) as jumlah,noreferensi,notransaksi","noreferensi='".$bar['novoucher']."'");
				$resql = fetchData($sqle);
				$jlhql = count($resql);
				if($jlhql==0){
					$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdok('".$bar['notransaksi']."','".$bar['novoucher']."','".$bar['jumlah']."')>";//indra
					$data.= "<td>".$bar['notransaksi']."</td>";
					$data.= "<td>".$bar['novoucher']."</td>";
					$data.= "<td>".number_format($bar['jumlah'])."</td>";
					$data.= "</tr>";
				}else{
					#jika notransaksi dari pertanggung jawaban sama dengan yang dikirim dari system maka tampilkan satu aja
					if($resql[0]['notransaksi']==$notransaksi){
						$data.= "<tr  class=rowcontent style='cursor:pointer' title='add detail' onclick=getdok('".$bar['notransaksi']."','".$bar['novoucher']."','".($bar['jumlah']-$resql[0]['jumlah'])."')>";//indra
						$data.= "<td>".$bar['notransaksi']."</td>";
						$data.= "<td>".$bar['novoucher']."</td>";
						$data.= "<td>".number_format(($bar['jumlah']-$resql[0]['jumlah']))."</td>";
						$data.= "</tr>";
					}
				}
			}
			$data.= "</table></fieldset>";
			echo $data;
		
	break;
	case'deleteht':
		$str="delete from ".$dbname.".keu_kaskecilht where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str); 
			$str="delete from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal : ".addslashes($e->getMessage());
			}
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;
	case'getPeriodeKas':
		$sPlafon=array();
		$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sPrd="select * from ".$dbname.".keu_5kaskecil where unit='".$unit."' and close=0 order by periode desc";
		//exit('warning'.$sPrd);
		$rPrd=fetchData($sPrd);
		foreach ($rPrd as $key=>$val) {
			$sCekAkn="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and periode='".$val['periode']."' and tutupbuku=1";
			$rCekAkn=fetchData($sCekAkn);
			if(count($rCekAkn)==1){
				continue;
			}
			if($_POST['periodecash']==$val['periode']){
				$sPlafon[$_POST['periodecash']]=$val['plafon'];	
				$optPeriode.="<option value='".$val['periode']."' selected>".$val['periode']."</option>";
			}else{
				$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
			}
			
		}
		$rpData=0;
		if($_POST['periodecash']!=""){
			$rpData=$sPlafon[$_POST['periodecash']];
		}
		echo $optPeriode."####".$rpData;	
	break;
	case'deleteDt':
		$str="delete from ".$dbname.".keu_kaskecildt where notransaksi='".$notransaksi."' and nourut='".$_POST['nourut']."'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal : ".addslashes($e->getMessage());
		}
	break;					
}

function getidfrompath($path,$count='0'){
	global $dbname;
	global $owlPDO;
	
	$path = str_replace("fileupload/filingsystem/","",$path);
	$exppath = explode('/',$path);
	$no = 0;
	$temp = "";
	$where = "";
	foreach($exppath as $key){
		$no++;
		$str="select id from ".$dbname.".filemanager where namafile='".$key."'";
		$res=fetchdata($str);
		if(count($res) > 0 && $no <= (count($exppath)-$count)){
			if($temp==''){
				$where="";
			}else{
				$where= "and induk='".$temp."'";
			}
			$strx="select id,induk from ".$dbname.".filemanager where namafile='".$key."' ".$where."";
			$resx=fetchdata($strx);
			
			$value=$resx[0]['id'];
			$temp=$resx[0]['id'];
		}
	}
	
	return $value;
}

function deleteefil($notransaksi,$structure){
	global $dbname;
	global $owlPDO;
	
	$optId=makeOption($dbname,'filemanager','namafile,id',"namafile='".$notransaksi."'");
	$id=$optId[$notransaksi];
	
	$str="delete from ".$dbname.".filemanager where namafile='".$notransaksi."'";
	$owlPDO->exec($str);
	
	if($id!=''){
		$str="delete from ".$dbname.".filemanager where induk='".$id."'";
		$owlPDO->exec($str);
	}
	
	delete_directory($structure);
	return true;
}

function delete_directory($dirname){
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	
	if (!$dir_handle)
		return false;
	
	while($file = readdir($dir_handle)) 
	{
		if ($file != "." && $file != "..") 
		{
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delete_directory($dirname.'/'.$file);
	       }
	 }
	 closedir($dir_handle);
	 rmdir($dirname);
	 return true;
}
?>