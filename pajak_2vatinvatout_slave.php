<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method                         = checkPostGet('method','');
$tanggal1 = tanggalsystemn(checkPostGet('tanggal1',''));	
$tanggal2 = tanggalsystemn(checkPostGet('tanggal2',''));	
$unit = checkPostGet('unit','');
$npwp = checkPostGet('npwp','');
$tipe = checkPostGet('tipe','');
$flag = checkPostGet('flag','');
$tipelaporan = checkPostGet('tipelaporan','');

#akun Vin Vout
$sappl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='VIVO'";
$rappl=fetchData($sappl);
$noakunvv=$rappl[0]['nilai'];
$noakunvv=explode(',', $noakunvv);
$noakunIn=$noakunvv[0];
$noakunOut=$noakunvv[1];

#nama akun Vin
$wheredz=" noakun='".$noakunIn."' ";
$optnamain=makeOption($dbname,'keu_5akun','noakun,namaakun',$wheredz);

#nama akun Vout
$wheredz=" noakun='".$noakunOut."' ";
$optnamaout=makeOption($dbname,'keu_5akun','noakun,namaakun',$wheredz);

$arrstatus=array("0"=>"Belum Posting","1"=>"Posting");

switch ($method) {
	
	case'preview':
	
		 switch ($tipe) {
            case '1':
				$optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
                $lang=$_SESSION['lang']['supplier'];
                $noakun=$noakunIn;
                $namaakun=$optnamain[$noakunIn];
                $str="select a.tanggal as tanggaljurnal,a.noreferensi as noinvoice,a.noakun as noakun,b.tanggal as tanggalinvoice,a.kodesupplier,b.tanggalnofp  as tanggalnofp,b.nofp as nofakturpajak,b.historynofp as hisnsfp,b.historytanggalfp as historytanggalfp,a.jumlah 
					from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".keu_tagihanht b on a.noreferensi=b.noinvoice where a.noakun ='".$noakun."' and a.jumlah>0 and a.kodeorg='".$unit."' and b.tanggal between '".$tanggal1."' and '".$tanggal2."' and b.npwp='".$npwp."' order by b.nofp asc";
            break;

            case '2':
                #pengecekan jika data sudah tersimpan tidak ditampilankan kembali
			
				$optsup=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
                $scek="select nofakturawal,nofakturakhir from ".$dbname.".keu_fakturpajakht where npwp='".$npwp."'";
                $sres=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                $sres->setFetchMode(PDO::FETCH_ASSOC);
                $sbar=$sres->fetch();
                $noawal=$sbar['nofakturawal'];
                $noakhir=$sbar['nofakturakhir'];

                $lang=$_SESSION['lang']['customer'];
                $noakun=$noakunOut;
                $namaakun=$optnamaout[$noakunOut];
                 $str="select distinct a.tanggal as tanggaljurnal,b.noinvoice as noinvoice,a.noakun as noakun,b.tanggal as tanggalinvoice,b.kodecustomer as kodesupplier,b.tanggal  as tanggalnofp,b.nofakturpajak as nofakturpajak,(a.jumlah*(-1)) as jumlah,c.berikat,b.nilaiinvoice as nilinv from ".$dbname.".keu_jurnaldt_vw a 
                    left join ".$dbname.".keu_penagihanht b on a.noreferensi=b.noinvoice 
                    left join ".$dbname.".pmn_kontrakjual c on b.nokontrak=c.nokontrak 
                    where a.noakun='".$noakun."' and a.noreferensi in (select noinvoice from ".$dbname.".keu_penagihanht where  kodeorg='".$unit."' and tanggal between '".$tanggal1."' and '".$tanggal2."') order by b.nofakturpajak asc ";
            break;
        }
	
	// echo $str;
		
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$arrnoinvoice[$bar['noinvoice']]=$bar['noinvoice'];
			$tanggalinvoice[$bar['noinvoice']]=$bar['tanggalinvoice'];
			$tanggaljurnal[$bar['noinvoice']]=$bar['tanggaljurnal'];
			$noakunjurnal[$bar['noinvoice']]=$bar['noakun'];
			$tanggalnofp[$bar['noinvoice']]=$bar['tanggalnofp'];
			$nofakturpajak[$bar['noinvoice']]=$bar['nofakturpajak'];
			$kodesupplier[$bar['noinvoice']]=$bar['kodesupplier'];
			if(intval($bar['berikat'])==1){
                $dpp[$bar['noinvoice']]=$bar['nilinv'];
            }else{
                $dpp[$bar['noinvoice']]=$bar['jumlah']*10;
            }
			$nilaippn[$bar['noinvoice']]=$bar['jumlah'];
		}
		
		
		#= transaksi vateinout
		$str="select * from ".$dbname.".tax_vatin_vatout where noakun ='".$noakun."' and unit='".$unit."' and npwp='".$npwp."'";
		// echo $str;
        $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$novat[$bar['noinvoice']]=$bar['notransaksi'];
			$periodevat[$bar['noinvoice']]=$bar['periode'];
			$postingvat[$bar['noinvoice']]=$bar['posting'];
			
		}
		
		
	
		
		if($tipelaporan=='excel'){
			$border='border=1';
		} else {
			
			$border='';
		}
		
		
            $stream.=" <table cellpading=0 cellspacing=1 ".$border." class=sortable  style=width:100%;>";
            $stream.=" <thead>";
			$stream.="  <tr class=rowheader align=center>";
				$stream.="<td rowspan=8>".$_SESSION['lang']['nourut']."</td>";
				$stream.="<td colspan=9>".$_SESSION['lang']['invoice']."</td>";
				$stream.="<td colspan=3>Vat in/out</td>";
			$stream.="</tr>";	
			$stream.="  <tr class=rowheader align=center>";
				$stream.="<td>".$_SESSION['lang']['tanggalinvoice']."</td>";
				$stream.="<td>".$_SESSION['lang']['noinvoice']."</td>";
				$stream.="<td>".$_SESSION['lang']['tanggal'].". GL</td>";
				$stream.="<td>".$_SESSION['lang']['noakun']."</td>";
				$stream.="<td>Tgl. Faktur Pajak</td>";
				$stream.="<td>No. Faktur Pajak</td>";
				$stream.="<td>".$lang."</td>";
				$stream.="<td>DPP</td>";
				$stream.="<td>".$_SESSION['lang']['ppn']."</td>";
				$stream.="<td>".$_SESSION['lang']['notransaksi']."</td>";
				$stream.="<td>".$_SESSION['lang']['periode']."</td>";
				$stream.="<td>".$_SESSION['lang']['status']."</td>";
			$stream.="</tr>";	
			$stream.="</thead>";	
			
             
			foreach(@$arrnoinvoice as $noinvoice){
				
				if($flag!=''){
					if($flag==0 and $novat[$noinvoice]==''){
						@$no+=1;
						$stream.="<tr class=rowcontent>";
							$stream.="<td align=center>".$no."</td>";
							$stream.="<td>".tanggalnormal($tanggalinvoice[$noinvoice])."</td>";
							$stream.="<td>".$noinvoice."</td>";
							$stream.="<td>".tanggalnormal($tanggaljurnal[$noinvoice])."</td>";
							$stream.="<td>".$noakunjurnal[$noinvoice]."</td>";
							$stream.="<td>".tanggalnormal($tanggalnofp[$noinvoice])."</td>";
							$stream.="<td>".$nofakturpajak[$noinvoice]."</td>";
							$stream.="<td>".$optsup[$kodesupplier[$noinvoice]]."</td>";
							$stream.="<td align=right>".number_format($dpp[$noinvoice],2)."</td>";
							$stream.="<td align=right>".number_format($nilaippn[$noinvoice],2)."</td>";
							$stream.="<td>".$novat[$noinvoice]."</td>";
							$stream.="<td>".$periodevat[$noinvoice]."</td>";
							$stream.="<td>".$arrstatus[$postingvat[$noinvoice]]."</td>";
						$stream.="</tr>";
						@$tdpp+=$dpp[$noinvoice];
						@$tnilaippn+=$nilaippn[$noinvoice];
					}
					if($flag==1 and $novat[$noinvoice]!=''){
						@$no+=1;
						$stream.="<tr class=rowcontent>";
							$stream.="<td align=center>".$no."</td>";
							$stream.="<td>".tanggalnormal($tanggalinvoice[$noinvoice])."</td>";
							$stream.="<td>".$noinvoice."</td>";
							$stream.="<td>".tanggalnormal($tanggaljurnal[$noinvoice])."</td>";
							$stream.="<td>".$noakunjurnal[$noinvoice]."</td>";
							$stream.="<td>".tanggalnormal($tanggalnofp[$noinvoice])."</td>";
							$stream.="<td>".$nofakturpajak[$noinvoice]."</td>";
							$stream.="<td>".$optsup[$kodesupplier[$noinvoice]]."</td>";
							$stream.="<td align=right>".number_format($dpp[$noinvoice],2)."</td>";
							$stream.="<td align=right>".number_format($nilaippn[$noinvoice],2)."</td>";
							$stream.="<td>".$novat[$noinvoice]."</td>";
							$stream.="<td>".$periodevat[$noinvoice]."</td>";
							$stream.="<td>".$arrstatus[$postingvat[$noinvoice]]."</td>";
						$stream.="</tr>";
						@$tdpp+=$dpp[$noinvoice];
						@$tnilaippn+=$nilaippn[$noinvoice];
					}
				} else {
					@$no+=1;
					$stream.="<tr class=rowcontent>";
						$stream.="<td align=center>".$no."</td>";
						$stream.="<td>".tanggalnormal($tanggalinvoice[$noinvoice])."</td>";
						$stream.="<td>".$noinvoice."</td>";
						$stream.="<td>".tanggalnormal($tanggaljurnal[$noinvoice])."</td>";
						$stream.="<td>".$noakunjurnal[$noinvoice]."</td>";
						$stream.="<td>".tanggalnormal($tanggalnofp[$noinvoice])."</td>";
						$stream.="<td>".$nofakturpajak[$noinvoice]."</td>";
						$stream.="<td>".$optsup[$kodesupplier[$noinvoice]]."</td>";
						$stream.="<td align=right>".number_format($dpp[$noinvoice],2)."</td>";
						$stream.="<td align=right>".number_format($nilaippn[$noinvoice],2)."</td>";
						$stream.="<td>".$novat[$noinvoice]."</td>";
						$stream.="<td>".$periodevat[$noinvoice]."</td>";
						$stream.="<td>".$arrstatus[$postingvat[$noinvoice]]."</td>";
					$stream.="</tr>";
					@$tdpp+=$dpp[$noinvoice];
					@$tnilaippn+=$nilaippn[$noinvoice];
				}
				
				
				
			}
			
			$stream.="<tr class=rowcontent>";
				$stream.="<td align=center colspan=8>Total</td>";
				
				$stream.="<td align=right>".number_format($tdpp,2)."</td>";
				$stream.="<td align=right>".number_format($tnilaippn,2)."</td>";
				$stream.="<td align=center colspan=3></td>";
			$stream.="</tr>";
			
			
			$stream.="</table>";
			
			
		
		if($tipelaporan=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="rekap_kontrak_".$unit."_".$per;
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
