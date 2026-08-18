<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$method=checkPostGet('method','');

$pt=checkPostGet('pt','');
$gudang=checkPostGet('gudang','');
$periode=checkPostGet('periode','');
$periode1=checkPostGet('periode1','');
$revisi=checkPostGet('revisi','');
$regional=checkPostGet('regional','');
$tampilanId=checkPostGet('tampilanId','');
$akundari=checkPostGet('akundari','');
$akunsampai=checkPostGet('akunsampai','');
$tipe=checkPostGet('tipe','');



$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode=mktime(0,0,0,substr($periode,5,2)-1,4,substr($periode,0,4));
$lmperiode=date('Y-m',$lmperiode);

switch($method){
	
	
	case'getunit':
		$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="SELECT * FROM ".$dbname.".organisasi where induk='".$pt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
		}
		echo $optunit;
	break;
	
	
	case'preview':
	
		$where=$whereakun='';
		
		if($akundari!='' and $akunsampai!=''){
			$whereakun.=" and noakun between '".$akundari."' and  '".$akunsampai."' ";
		}	
		
		if($regional=='' && $gudang==''){
		   $where =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
		}else if($regional!='' && $gudang==''){
			$where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
					. " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
		}else{
			$where =" and kodeorg ='".$gudang."'";
		}
		// if($_SESSION['standard']['username']=='tim.owl3'){
			// echo $where;	
			// }

		
		$CLM='';
		$str=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'");
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=  $str->fetch()){
			$CLM=$bar->noakundebet;
		}
	
		$str="SELECT * FROM ".$dbname.".organisasi where induk='".$pt."' and showlaporan=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodeunit[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		}


		$str="SELECT * FROM ".$dbname.".keu_5akun where  noakun!='".$CLM."' ".$whereakun."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nomorakun[$bar['noakun']]=$bar['noakun'];
			$namaakun[$bar['noakun']]=$bar['namaakun'];
		}
		
				
		#= akun kas/bank
		$str="select noakun from ".$dbname.".keu_5akun
			where left(noakun,3) = '111' and detail=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$arrnoakunkb[$bar->noakun]=$bar->noakun;
		}
		
		
		
		$str="select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun,kodeorg from ".$dbname.".keu_saldobulanan 
		where periode ='".str_replace("-","",$periode)."'  and  noakun!='".$CLM."' ".$where."   group by noakun,kodeorg order by noakun";
		// echo $str;
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$sawal[$bar->noakun][$bar->kodeorg]=$bar->sawal;
		}



		// $str=" SELECT 
		// if(sum(jumlah)>0,sum(jumlah),'0') as debet,
		// if(sum(jumlah)<0,(sum(jumlah)*-1),'0') as kredit,
		// noakun,kodeorg
		// FROM ".$dbname.".`keu_jurnaldt_vw`
		// WHERE periode>='".$periode."' and periode<='".$periode1."' ".$where." ".$whereakun." 
		// and noakun!='".$CLM."' and revisi <= '".$revisi."'
		 // group by noakun,kodeorg,noreferensi,keterangan"; 
		 // // echo $str;exit();
		// $res=$owlPDO->query($str);
		// $res->setFetchMode(PDO::FETCH_OBJ);
		// while($bar=$res->fetch()){
			// if(in_array($bar->noakun,$arrnoakunkb)){
				// @$debet[$bar->noakun][$bar->kodeorg]+=$bar->debet;
				// @$kredit[$bar->noakun][$bar->kodeorg]+=$bar->kredit;
			// }
		// } 

		$str="select sum(debet) as debet,sum(kredit) as kredit, noakun,kodeorg from ".$dbname.".keu_jurnaldt_vw
			where periode>='".$periode."' and periode<='".$periode1."' ".$where." ".$whereakun." 
			and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun,kodeorg"; #tidak sama dengan laba/rugi berjalan
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			// if(!in_array($bar->noakun,$arrnoakunkb)){
				@$debet[$bar->noakun][$bar->kodeorg]+=$bar->debet;
				@$kredit[$bar->noakun][$bar->kodeorg]+=$bar->kredit;
			// }
		} 


		$spankdunit=count($kodeunit);
		
		$border='border=0';
		if($tipe=='excel'){
			$border='border=1';
		}
		$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$gudang."'");
		if($tipe=='excel'){			
			$stream.="Laporan Neraca Saldo Unit<br>";
			$stream.="".$gudang." - ".$nmorg[$gudang]."<br>";
			$stream.="Periode ".$periode." s/d ".$periode1."<br><br>";
		}
		
		$stream.= "<table class=sortable ".$border."  width='100%' cellspacing=1 cellpadding=3>";
		$stream.="<thead>";	
		$stream.="<tr class=rowheader>";	 
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=2>".$_SESSION['lang']['noakun']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=2>".$_SESSION['lang']['namaakun']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center colspan=".($spankdunit).">".$_SESSION['lang']['unit']."</th>";
			$stream.="<th bgcolor=#CCCCCC align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
		
		$stream.="</tr>";  
		$stream.="<tr class=rowheader>";	 
			foreach($kodeunit as $kdunit){
				$stream.="<th bgcolor=#CCCCCC align=center>".$kdunit."</th>";
			}
		$stream.="</tr>";  
		
		$stream.="</thead>";
		
		
	
		#= jika nilai salak 0 maka filter disini
		foreach($nomorakun as $noakun){
			foreach($kodeunit as $kdunit){
				$salak[$noakun][$kdunit]=$sawal[$noakun][$kdunit]+$debet[$noakun][$kdunit]-$kredit[$noakun][$kdunit];
				if($tampilanId==1){
					if($salak[$noakun][$kdunit]==0){
						continue;
					}
				}
				$arrnoakun[$noakun]=$noakun;
			}
		}
	
		#= tampilkan data
		foreach($arrnoakun as $noakun){
			@$no+=1;
			$stream.="<tr class=rowcontent>";	 
			 // $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$kdorg."','".$revisi."',event);\">
                
				$stream.="<td>".$no."</td>";	 
				$stream.="<td>".$noakun."</td>";	 
				$stream.="<td>".$namaakun[$noakun]."</td>";	 
				foreach($kodeunit as $kdunit){
					// @$salak[$noakun][$kdunit]=$sawal[$noakun][$kdunit]+$debet[$noakun][$kdunit]-$kredit[$noakun][$kdunit];
					$stream.="<td align=right onclick=\"lihatDetail('".$noakun."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$kdunit."','".$revisi."',event);\">".number_format($salak[$noakun][$kdunit],2)."</td>";	 
					$stkanansalak[$noakun]+=$salak[$noakun][$kdunit];
					$stbawahsalak[$kdunit]+=$salak[$noakun][$kdunit];
				} 
				$stream.="<td align=right>".number_format($stkanansalak[$noakun],2)."</td>";	 
			$stream.="</tr>";  
		}
		
		$stream.="<tr class=rowcontent>";	 
				$stream.="<td colspan=3>".$_SESSION['lang']['total']."</td>";	 
				foreach($kodeunit as $kdunit){
					$stream.="<td align=right>".number_format($stbawahsalak[$kdunit],2)."</td>";	
					@$gtsalak+=$stbawahsalak[$kdunit];
				} 
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