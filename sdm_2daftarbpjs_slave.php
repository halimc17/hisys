<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/nangkoelib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
error_reporting(0);

$stream      ='';
$method      = checkPostGet('method', '');
$unit        = checkPostGet('unit', '');
$periode     = checkPostGet('periode', '');
$tipekaryawan= checkPostGet('tipekaryawan', '');
$jenis       = checkPostGet('jenis', '');
$tipe        = checkPostGet('tipe', '');

$opttporg    = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$nmtpkar     = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$tipeorg     = $opttporg[$unit];

$where='';
if($tipekaryawan!=''){
	$where.= "and tipekaryawan='".$tipekaryawan."' ";
}

$where.=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$periode."-01"."')";
 
switch ($method) {
######PREVIEW
    case 'preview':
		
		$dakarbulanan=0;
		$str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$periode."'"; 
		$res = fetchdata($str);
		if(count($res)>0){ 
			$dakarbulanan=1;
		}
		
		if($dakarbulanan==0){
			$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' ".$where." order by namakaryawan";
		}else{
			$str="select * from ".$dbname.".datakaryawan_hist where lokasitugas='".$unit."' and approval_status='8' and version_type='B' and periodegaji='".$periode."' ".$where." order by namakaryawan";	
		}
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$karid[$bar['karyawanid']]=$bar['karyawanid'];
			$nmkar[$bar['karyawanid']]=$bar['namakaryawan'];
			$nik[$bar['karyawanid']]=$bar['nik'];
			$subbagian[$bar['karyawanid']]=$bar['subbagian'];
			$tpkar[$bar['karyawanid']]=$bar['tipekaryawan'];
			if($jenis=='HRBPJSKER'){
				$nobpjs[$bar['karyawanid']]=$bar['jms'];
			}
			if($jenis=='HRBPJSKES'){
				$nobpjs[$bar['karyawanid']]=$bar['bpjs'];
			}
			if($jenis=='HRBPJSPEN'){
				$nobpjs[$bar['karyawanid']]=$bar['pensiun'];
			}
		} 
		 
		#= jenis bpjs
		if($jenis=='HRBPJSKER'){
			$str="select * from ".$dbname.".setup_parameterappl where kodeparameter='".$jenis."'";	
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$arrdtbpjs=explode(',',$bar['nilai']);
			foreach($arrdtbpjs as $key){
				$arrbpjs[$key]=$key;
			}	
			$dtkomponen=$bar['nilai'];

			$str="select * from ".$dbname.".setup_parameterappl where kodeparameter='HRBPJSPEN'";	
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$arrdtbpjs=explode(',',$bar['nilai']);
			foreach($arrdtbpjs as $key){
				$arrbpjs[$key]=$key;
			}	

			$dtkomponen.=','.$bar['nilai'];


		}
		else
		{
			$str="select * from ".$dbname.".setup_parameterappl where kodeparameter='".$jenis."'";	
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$arrdtbpjs=explode(',',$bar['nilai']);
			foreach($arrdtbpjs as $key){
				$arrbpjs[$key]=$key;
			}	
			
			$dtkomponen=$bar['nilai'];
		}
		
		$countbpjs=count($arrbpjs);
		$spancountbpjs=$countbpjs*2;
		
		
		
		#= daftar komponen bpjs
		$str="select * from ".$dbname.".sdm_5bpjs where lokasibpjs='".$tipeorg."' and jenisbpjs in (".$dtkomponen.")";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$idbpjs[$bar['jenisbpjs']]=$bar['jenisbpjs'];
			$idbpjs[$bar['jenisbpjsplus']]=$bar['jenisbpjsplus'];
			$idbpjsplus[$bar['jenisbpjs']]=$bar['jenisbpjsplus'];
			$nmbpjs[$bar['jenisbpjs']]=$bar['namabpjs'];
		}


		$sUmpDaerah="select distinct jumlah from ".$dbname.".sdm_5gajipokok where tahun='".substr($periode,0,4)."' and idkomponen='87' and kodeorg='".$unit."'";
		$rUmpDaerah=fetchData($sUmpDaerah);
		$umpDaerah=$rUmpDaerah[0]['jumlah']; #UMP Daerah


		## Parameter aplikasi
		#= ambil komponen yang termasuk di gaji pokok
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRGAPOK'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch(); 
		$arrgapok=explode(',',$bar['nilai']);
		foreach($arrgapok as $key){
			$arrcombpjs[]=$key;
		}
		

		$str="select * from ".$dbname.".sdm_gaji where 	kodeorg='".$unit."' and periodegaji='".$periode."'";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$rpbpjs[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
			if(in_array($bar['idkomponen'],$arrcombpjs)){
				$gapok[$bar['karyawanid']]+=$bar['jumlah'];
			}
		}
	
		if($tipe=='excel' or $tipe=='pdf'){
			$border=1;
		} else {
			$border=0;
		}
		
		$stream.="<table class=sortable cellpadding=5 cellspacing=1 border='".$border."' width=100%>";
		$stream.="
			<thead>";
				$stream.="<tr class=rowheader>";
					$stream.="<th align='center' rowspan=3>".$_SESSION['lang']['nourut']."</th>";
					$stream.="<th align='center' rowspan=3>NIK</th>";
					$stream.="<th align='center' rowspan=3>".$_SESSION['lang']['nama']."</th>";
					$stream.="<th align='center' rowspan=3>".$_SESSION['lang']['tipekaryawan']."</th>";
					$stream.="<th align='center' rowspan=3>".$_SESSION['lang']['nokpj']."</th>";
					$stream.="<th align='center' rowspan=3>".$_SESSION['lang']['gaji']."</th>";
					$stream.="<th align='center' colspan='".$spancountbpjs."'>".$_SESSION['lang']['bpjs']."</th>";
		$stream.="</tr>";	
		$stream.="<tr>";	
			
			foreach($arrbpjs as $bpjs){
				$stream.="<th colspan=2 align=center>".$nmbpjs[$bpjs]."</th>";
			}
			
		$stream.="</tr>";	
		$stream.="<tr>";			
					for($i=1;$i<=$countbpjs;$i++){
						$stream.="

						<th align=center>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['karyawan']."</th>
						<th align=center>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['pt']."</th>";
					}
				$stream.="	
				</tr>
			</thead>
		 <tbody>";
		 
		
		foreach($karid as $kar){
			@$no+=1;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td >".$nik[$kar]."</td>";
				$stream.="<td>".$nmkar[$kar]."</td>";
				$stream.="<td>".$nmtpkar[$tpkar[$kar]]."</td>";
				$stream.="<td>".$nobpjs[$kar]."</td>";

				if($gapok[$kar] < $umpDaerah){
					$gapok[$kar] = $umpDaerah;
				}
				
						$stream.="<td align=right>".number_format($gapok[$kar])."</td>";
					foreach($arrbpjs as $bpjs){
						$stream.="<td align=right>".number_format($rpbpjs[$kar][$bpjs])."</td>";
						$stream.="<td align=right>".number_format($rpbpjs[$kar][$idbpjsplus[$bpjs]])."</td>";
					}
				$stream.="</tr>";

		}
		$stream.="
		 </tbody>
			 </table>";
			
			
		
		
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_bpjs_".$periode."-".$unit."";
			if(strlen($stream)>0)
			{
					if ($handle = opendir('tempExcel')) {
							while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
									@unlink('tempExcel/'.$file);
							}
							}	
							closedir($handle);
					}
					$handle=fopen("tempExcel/".$nop_.".xls",'w');
					if(!fwrite($handle,$stream))
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
		}else if($tipe=='pdf'){
			$dompdf = new Dompdf();
            $dompdf->loadHtml($stream);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("form survey",array("Attachment"=>0));
		}else{
			
			echo $stream;
		}
	break;

    case 'getbank':
		$optbank="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($noakun=='1110101' or $noakun=='1111101'){	
			$str="select * from ".$dbname.".keu_5akunbank where pemilik='".$unit."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$bar['namabank']."'");
				$optbank.="<option value=".$bar['noakun'].">".$bar['pemilik'].":".$optNamaBank[$bar['namabank']]." ".$bar['rekening']."</option>";
			}
		}
		echo $optbank;
		
    break;
}
?>