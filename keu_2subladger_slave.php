<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');



$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$tipe = checkPostGet('tipe', '');
$noakun = checkPostGet('noakun', '');
$tanggal1=tanggalsystemn(checkPostGet('tanggal1',''));
$tanggal2=tanggalsystemn(checkPostGet('tanggal2',''));

$str="select * from ".$dbname.".organisasi where tipe in ('KEBUN') ";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 and b.tipe in ('SUPPLIERTBS') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
}
			
if($tanggal1=='--'){
    $tanggal1='';
}
if($tanggal2=='--'){
    $tanggal2='';
}
// echo $tanggal1;
if($tanggal1==''){
	exit("Warning:Tanggal Masih Kosong");
}

// exit("Error:$method");
#= query


switch($method){	
	case'preview':
	
	
		$str="select sum(debet) as debet,sum(kredit) as kredit,nodok from ".$dbname.".keu_jurnaldt_vw where 
				noakun ='".$noakun."' and tanggal between '".$tanggal1."' and '".$tanggal2."' 
				and kodeorg='".$unit."' group by nodok";
				// echo $str;exit();
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$arrnodok[$bar['nodok']]=$bar['nodok'];
			$debet[$bar['nodok']]=$bar['debet'];
			$kredit[$bar['nodok']]=$bar['kredit'];
		}
		if (count($arrnodok)==0) {
			exit("Warning: Data Kosong !!!!");
		}
		
		
		
		
		if($tipe=='excel'){
			$border='border=1';
		} else {
			
			$border='';
		}
		$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$stream.="Laporan Sub Ledger<br>";
		$stream.="".$unit." - ".$nmorg[$unit]."<br>";
		$stream.="".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal2)."<br><br>";
		$stream.=" <table cellpading=0 cellspacing=1 ".$border." class=sortable  style=width:100%;>";
			$stream.=" <thead>";
			$stream.="  <tr class=rowheader align=center>";
				$stream.="<td>".$_SESSION['lang']['nourut']."</td>";
				$stream.="<td>".$_SESSION['lang']['nodok']."</td>";
				$stream.="<td>".$_SESSION['lang']['debet']."</td>";
				$stream.="<td>".$_SESSION['lang']['kredit']."</td>";
				$stream.="<td>".$_SESSION['lang']['selisih']."</td>";
			$stream.="</tr>";	
			$stream.="</thead>";	
				
				foreach($arrnodok as $nodok){
				@$no+=1;
				$stream.="<tr class=rowcontent>";
					$stream.="<td align=center>".$no."</td>";
					$stream.="<td align=left>".$nodok."</td>";
					$stream.="<td align=right>".number_format($debet[$nodok],2)."</td>";
					$stream.="<td align=right>".number_format($kredit[$nodok],2)."</td>";
					$stream.="<td align=right>".number_format(($debet[$nodok]-$kredit[$nodok]),2)."</td>";
					@$tdebet+=$debet[$nodok];
					@$tkredit+=$kredit[$nodok];
				$stream.="</tr>";
				}
				$stream.="<tr class=rowcontent>";
					$stream.="<td align=center colspan=2>".$_SESSION['lang']['total']."</td>";
					$stream.="<td align=right>".number_format($tdebet,2)."</td>";
					$stream.="<td align=right>".number_format($tkredit,2)."</td>";
					$stream.="<td align=right>".number_format(($tdebet-$tkredit),2)."</td>";
			$stream.="</tr>";
			
		$stream.="</table>";
			
			
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_subledger_".$unit."_".$tanggal1."_".$tanggal2;
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