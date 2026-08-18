<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method=checkPostGet('method','');

$periode=checkPostGet('periode','');
$periode1=checkPostGet('periode1','');
$akundari=checkPostGet('akundari','');
$akunsampai=checkPostGet('akunsampai','');
$tipe=checkPostGet('tipe','');
$tampilanId=checkPostGet('tampilanId','');



$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode=mktime(0,0,0,substr($periode,5,2)-1,4,substr($periode,0,4));
$lmperiode=date('Y-m',$lmperiode);


$str="SELECT * FROM ".$dbname.".organisasi where (length(kodeorganisasi)=4) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];
	$arrkdpt[$bar['induk']]=$bar['induk'];
	// $namaorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

$str="SELECT * FROM ".$dbname.".organisasi where (length(kodeorganisasi)=3) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$namaorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}



switch($method){
	
	
	
	case'preview':
	
		$where=$whereakun='';
		
		if($akundari!='' and $akunsampai!=''){
			$whereakun.=" and noakun between '".$akundari."' and  '".$akunsampai."' ";
		}	
		
		
		
		$CLM='';
		$str=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=  $str->fetch()){
			$CLM=$bar->noakundebet;
		}
	

		$str="SELECT * FROM ".$dbname.".keu_5akun where noakun!='".$CLM."' ".$whereakun."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nomorakun[$bar['noakun']]=$bar['noakun'];
			$namaakun[$bar['noakun']]=$bar['namaakun'];
		}
		
				
		#= akun kas/bank
		// $str="select noakun from ".$dbname.".keu_5akun
			// where left(noakun,3) = '111' and detail=1";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_OBJ);
		// while($bar=$res->fetch()){
			// $arrnoakunkb[$bar->noakun]=$bar->noakun;
		// }
		
		
		$str="select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun,kodeorg from ".$dbname.".keu_saldobulanan 
		where periode ='".str_replace("-","",$periode)."'  and  noakun!='".$CLM."'   group by noakun,kodeorg order by noakun";
		// echo $str;
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			@$sawal[$bar->noakun][$kodept[$bar->kodeorg]]+=$bar->sawal;
		}



		// $str=" SELECT 
		// if(sum(jumlah)>0,sum(jumlah),'0') as debet,
		// if(sum(jumlah)<0,(sum(jumlah)*-1),'0') as kredit,
		// noakun,kodeorg
		// FROM ".$dbname.".`keu_jurnaldt_vw`
		// WHERE periode>='".$periode."' and periode<='".$periode1."' ".$whereakun." 
		// and noakun!='".$CLM."' group by noakun,kodeorg,noreferensi,keterangan"; 
		 // // echo $str;exit();
		// $res=$owlPDO->query($str);
		// $res->setFetchMode(PDO::FETCH_OBJ);
		// while($bar=$res->fetch()){
			// if(in_array($bar->noakun,$arrnoakunkb)){
				// @$debet[$bar->noakun][$kodept[$bar->kodeorg]]+=$bar->debet;
				// @$kredit[$bar->noakun][$kodept[$bar->kodeorg]]+=$bar->kredit;
			// }
		// } 

		$str="select sum(debet) as debet,sum(kredit) as kredit, noakun,kodeorg from ".$dbname.".keu_jurnaldt_vw
			where periode>='".$periode."' and periode<='".$periode1."'  ".$whereakun." 
			and noakun!='".$CLM."' group by noakun,kodeorg"; 
			// echo $str;exit();
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			// if(!in_array($bar->noakun,$arrnoakunkb)){
				@$debet[$bar->noakun][$kodept[$bar->kodeorg]]+=$bar->debet;
				@$kredit[$bar->noakun][$kodept[$bar->kodeorg]]+=$bar->kredit;
			// }
		} 


		$spankdpt=count($arrkdpt);
		
		$border='border=0';
		if($tipe=='excel'){
			$border='border=1';
			$stream.="Laporan Neraca Saldo PT<br>";
			$stream.="Periode ".$periode." s/d ".$periode1."<br><br>";
		}
		
		
		$stream.= "<table class=sortable ".$border."  cellspacing=1>";
		$stream.="<thead>";	
		$stream.="<tr class=rowheader>";	 
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=3>".$_SESSION['lang']['nourut']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=3>".$_SESSION['lang']['noakun']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=3>".$_SESSION['lang']['namaakun']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center colspan=".($spankdpt*4).">".$_SESSION['lang']['pt']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=2 colspan=4>".$_SESSION['lang']['total']."</th>";
		
		$stream.="</tr>";  
		$stream.="<tr class=rowheader>";	 
			foreach($arrkdpt as $kdpt){
				$stream.="<th bgcolor=#CCCCCC align=center colspan=4>".$namaorg[$kdpt]."</th>";
			}
		$stream.="</tr>";  
		$stream.="<tr class=rowheader>";	 
			foreach($arrkdpt as $kdpt){
				$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['saldoawal']."</th>";
				$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['debet']."</th>";
				$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kredit']."</th>";
				$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['saldoakhir']."</th>";
			}
			$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['saldoawal']."</th>";
				$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['debet']."</th>";
				$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kredit']."</th>";
				$stream.="<th bgcolor=#CCCCCC align=center>".$_SESSION['lang']['saldoakhir']."</th>";
		$stream.="</tr>";  
		
		$stream.="</thead>";
		
		
	
		
		foreach($nomorakun as $noakun){
			foreach($arrkdpt as $kdpt){
				@$salak[$noakun][$kdpt]=$sawal[$noakun][$kdpt]+$debet[$noakun][$kdpt]-$kredit[$noakun][$kdpt];
				if($tampilanId==1){
					if($salak[$noakun][$kdpt]==0 && $sawal[$noakun][$kdpt]==0 && $debet[$noakun][$kdpt]==0 && $kredit[$noakun][$kdpt]==0){
						continue;
					}
				}
				$arrnoakun[$noakun]=$noakun;
			}
		}
		
		
		
		
		
		
		foreach($arrnoakun as $noakun){
			@$no+=1;
			$stream.="<tr class=rowcontent>";	 
				$stream.="<td>".$no."</td>";	 
				$stream.="<td>".$noakun."</td>";	 
				$stream.="<td>".$namaakun[$noakun]."</td>";	 
				foreach($arrkdpt as $kdpt){
					
					$stream.="<td align=right onclick=\"lihatDetail('".$noakun."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$kdpt."','".$revisi."',event);\">".number_format($sawal[$noakun][$kdpt],2)."</td>";	 
					$stream.="<td align=right onclick=\"lihatDetail('".$noakun."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$kdpt."','".$revisi."',event);\">".number_format($debet[$noakun][$kdpt],2)."</td>";	 
					$stream.="<td align=right onclick=\"lihatDetail('".$noakun."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$kdpt."','".$revisi."',event);\">".number_format($kredit[$noakun][$kdpt],2)."</td>";	 
					$stream.="<td align=right onclick=\"lihatDetail('".$noakun."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$kdpt."','".$revisi."',event);\">".number_format($salak[$noakun][$kdpt],2)."</td>";	 
					
					
					@$stkanansawal[$noakun]+=$sawal[$noakun][$kdpt];
					@$stbawahsawal[$kdpt]+=$sawal[$noakun][$kdpt];
					
					@$stkanandebet[$noakun]+=$debet[$noakun][$kdpt];
					@$stbawahdebet[$kdpt]+=$debet[$noakun][$kdpt];
					
					@$stkanankredit[$noakun]+=$kredit[$noakun][$kdpt];
					@$stbawahkredit[$kdpt]+=$kredit[$noakun][$kdpt];
					
					@$stkanansalak[$noakun]+=$salak[$noakun][$kdpt];
					@$stbawahsalak[$kdpt]+=$salak[$noakun][$kdpt];
					
				} 
				$stream.="<td align=right>".number_format($stkanansawal[$noakun],2)."</td>";	 
				$stream.="<td align=right>".number_format($stkanandebet[$noakun],2)."</td>";	 
				$stream.="<td align=right>".number_format($stkanankredit[$noakun],2)."</td>";	 
				$stream.="<td align=right>".number_format($stkanansalak[$noakun],2)."</td>";	 
			$stream.="</tr>";  
		}
		
		// echo"<pre>";
		// print_r($stbawahsalak);
		// echo"</pre>";
		
		$stream.="<tr class=rowcontent>";	 
				$stream.="<td colspan=3>".$_SESSION['lang']['total']."</td>";	 
				foreach($arrkdpt as $kdpt){
					$stream.="<td align=right>".number_format($stbawahsawal[$kdpt],2)."</td>";	
					$stream.="<td align=right>".number_format($stbawahdebet[$kdpt],2)."</td>";	
					$stream.="<td align=right>".number_format($stbawahkredit[$kdpt],2)."</td>";	
					$stream.="<td align=right>".number_format($stbawahsalak[$kdpt],2)."</td>";	
					@$gtsawal+=$stbawahsawal[$kdpt];
					@$gtdebet+=$stbawahdebet[$kdpt];
					@$gtkredit+=$stbawahkredit[$kdpt];
					@$gtsalak+=$stbawahsalak[$kdpt];
				} 
				$stream.="<td align=right>".number_format($gtsawal,2)."</td>";	 
				$stream.="<td align=right>".number_format($gtdebet,2)."</td>";	 
				$stream.="<td align=right>".number_format($gtkredit,2)."</td>";	 
				$stream.="<td align=right>".number_format($gtsalak,2)."</td>";	 
			$stream.="</tr>";  
				
		
		$stream.="</table>";  
		
		if($tipe=='excel'){
			$tglSkrg=date("YmdHis");
			$nop_="laporan_tb_".$pt._.$periode._.$periode1;
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
	
	
	
	default:
	break;
}



?>