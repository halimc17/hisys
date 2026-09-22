<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method		=checkPostGet('method','');
$pt			=checkPostGet('pt','');
$unit		=checkPostGet('unit','');
$kontraktor	=checkPostGet('kontraktor','');
$jenis		=checkPostGet('jenis','');
$notransaksi=checkPostGet('notransaksi','');
$tgl1		=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2		=tanggalsystemn(checkPostGet('tgl2',''));
$tipe		=checkPostGet('tipe','');

$stream="";

function getNamaBlok($jenisspk,$kodeblok,$dbname){
	if($jenisspk=='PROJECT'){
		$str="select nama from ".$dbname.".project where kode='".$kodeblok."'";
		$res=fetchData($str)[0];
		return $res['nama'] != '' ? $res['nama'] : $kodeblok;
	}
	return getNamaOrg($kodeblok);
}

switch($method){
	case'preview':
		if($tipe=='excel'){
			$border="border=1";
			$bgColor="background-color:#ccc";
			$stream.="<h2>".getNamaOrg($pt)."</h2>";
			$stream.="<h2>Laporan BAPP</h2>";
		}else{
			$border='border=0';
		}
		$sql=selectQuery($dbname,'organisasi','kodeorganisasi',"induk='$pt'");
		$hsl=fetchData($sql);
		foreach ($hsl as $val) {
			$arrunt[$val['kodeorganisasi']]=$val['kodeorganisasi'];
		}

		if($unit == '%%'){
			$whrunt = "and ht.unit in ('".implode("','",$arrunt)."')";
		}else{
			$whrunt = "and ht.unit = '".$unit."'";
		}
		$whrsupp ='';
		if($kontraktor != '%%'){
			$whrsupp = "and ht.koderekanan = '".$kontraktor."'";
		}
		$whrjenis ='';
		if($jenis != '%%' && $jenis != ''){
			$whrjenis = "and ht.jenis = '".$jenis."'";
		}
		$whrnot ='';
		if($notransaksi != ''){
			$whrnot = "and bp.notransaksi like '%".$notransaksi."%'";
		}

		$gttotalkontrak=$gtjumlahrealisasi=0;
		$str="select bp.*, ht.unit, ht.koderekanan, ht.jenis as jenisspk,
					kg.satuan as satuankontrak, kg.volume as volumekontrak, kg.total as totalkontrak, kg.hk as hkkontrak
              from ".$dbname.".log_baspk bp
              inner join ".$dbname.".lgl_pengajuanspkht ht on ht.notransaksi = bp.notransaksi
              left join ".$dbname.".lgl_pengajuanspk_keg kg on kg.notransaksi = bp.notransaksi and kg.subunit = bp.kodeblok and kg.kegiatan = bp.kodekegiatan
              where 1=1 ".$whrunt." ".$whrsupp." ".$whrjenis." ".$whrnot." and bp.tanggal between '".$tgl1."' and '".$tgl2."'
              order by bp.notransaksi asc, bp.tanggal asc";
		$res=fetchData($str);

		if(count($res) < 1){
			echo "kosong";
		}else{
			$stream.="<table class=sortable ".$border."  cellspacing=1 cellpading=5 width=100%>";
			$stream.="<thead>";
			$stream.="<tr class=rowheader>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['nourut']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['unit']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['notransaksi']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['jenis']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['kontraktor']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['blok']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['kegiatan']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['satuan']." ".$_SESSION['lang']['kontrak']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['jhk']." ".$_SESSION['lang']['kontrak']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['kontrak']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['realisasi']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['hasilkerja2']." ".$_SESSION['lang']['realisasi']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['jhk']." ".$_SESSION['lang']['realisasi']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['realisasi']."</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>Termin</th>";
				$stream.="<th align=center style='border: 0.5 px solid black;".$bgColor."'>".$_SESSION['lang']['keterangan']."</th>";
			$stream.="</tr>";
			$stream.="</thead>";

			$subtotalkontrak=$subjumlahrealisasi=0;
			$prevnotransaksi=null;
			foreach($res as $b){
				if($prevnotransaksi!==null && $prevnotransaksi!=$b['notransaksi']){
					$stream.="<tr class=rowcontent>";
						$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right colspan=9>".$_SESSION['lang']['total']." ".$prevnotransaksi."</td>";
						$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right>".number_format($subtotalkontrak)."</td>";
						$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right colspan=3></td>";
						$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right>".number_format($subjumlahrealisasi)."</td>";
						$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right colspan=2></td>";
					$stream.="</tr>";
					$subtotalkontrak=$subjumlahrealisasi=0;
				}
				$prevnotransaksi=$b['notransaksi'];

				@$no+=1;
				$stream.="<tr class=rowcontent>";
					$stream.="<td style='border: 0.5 px solid black;' align=center>".$no."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".$b['unit']." - ".getNamaOrg($b['unit'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".$b['notransaksi']."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=center>".$b['jenisspk']."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".getNamaSupplier($b['koderekanan'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".$b['kodeblok']." - ".getNamaBlok($b['jenisspk'],$b['kodeblok'],$dbname)."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".$b['kodekegiatan']." - ".getNamaKeg($b['kodekegiatan'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=center>".$b['satuankontrak']."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($b['hkkontrak'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($b['totalkontrak'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".tanggalnormal($b['tanggal'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($b['hasilkerjarealisasi'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($b['hkrealisasi'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=right>".number_format($b['jumlahrealisasi'])."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=center>".$b['termin']."</td>";
					$stream.="<td style='border: 0.5 px solid black;' align=left>".$b['keterangan']."</td>";
				$stream.="</tr>";
				$subtotalkontrak += $b['totalkontrak'];
				$subjumlahrealisasi += $b['jumlahrealisasi'];
				$gttotalkontrak += $b['totalkontrak'];
				$gtjumlahrealisasi += $b['jumlahrealisasi'];
			}
				$stream.="<tr class=rowcontent>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right colspan=9>".$_SESSION['lang']['total']." ".$prevnotransaksi."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right>".number_format($subtotalkontrak)."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right colspan=3></td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right>".number_format($subjumlahrealisasi)."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#eee;font-weight:bold' align=right colspan=2></td>";
				$stream.="</tr>";

				$stream.="<tr class=rowcontent>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=center colspan=9>".$_SESSION['lang']['grnd_total']."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right>".number_format($gttotalkontrak)."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right colspan=3></td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right>".number_format($gtjumlahrealisasi)."</td>";
					$stream.="<td style='border: 0.5 px solid black;background-color:#ccc;font-weight:bold' align=right colspan=2></td>";
				$stream.="</tr>";
			$stream.="</table>";

			if($tipe=='excel'){
				$tglSkrg=date("YmdHis");
				$nop_="Laporan BAPP_".$tgl1."_s.d_".$tgl2;
				$stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];
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
			} else if($tipe=='pdf'){
				$dompdf = new Dompdf();
				$dompdf->loadHtml($stream);
				$dompdf->setPaper('A4', 'landscape');
				$dompdf->render();
				$dompdf->stream($stream,array("Attachment"=>0));
			}else{
				echo $stream;
			}
		}
	break;

	case 'getunit':
		$optunit="<option value='%%'>".$_SESSION['lang']['all']."</option>";
		$str="SELECT kodeorganisasi,namaorganisasi FROM $dbname.organisasi WHERE LENGTH(kodeorganisasi)='4' AND induk ='$pt' AND kodeorganisasi IN (".getOrgDetail(2).")";
		$res=fetchData($str);
		foreach ($res as $bar) {
			$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
		}
		echo $optunit;
	break;

	default:
	break;
}



?>
