<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kodeOrg = checkPostGet('kdUnit2','');
$thnBudget = checkPostGet('thnBudget2','');
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmbrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid=".$_SESSION['standard']['userid']. "";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$namakar[$bar->karyawanid]=$bar->namakaryawan;
}

$daftarmobil="(";
$where=" kodetraksi='".$kodeOrg."' and tahunbudget='".$thnBudget."'";
$sKodeOrg="select * from ".$dbname.".bgt_biaya_jam_ken_vs_alokasi where  ".$where." order by tahunbudget asc";
$qKodeOrg=$owlPDO->query($sKodeOrg) or die(print " Gagal: ".PDOException::getMessage());
$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rKode=$qKodeOrg->fetch())
{
    $dtKdtraksi[]=$rKode['kodetraksi'];
    $dtKdvhc[]=$rKode['kodevhc'];
    $daftarmobil.="'".$rKode['kodevhc']."',";
    $dtRpSthn[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['rpsetahun'];
    $dtJamSthn[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['jamsetahun'];
    $dtRpJam[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['rpperjam'];
    $dtAlokasi[$rKode['tahunbudget']][$rKode['kodetraksi']][$rKode['kodevhc']]=$rKode['teralokasi'];
}
$daftarmobil=substr($daftarmobil,0,-1);
$daftarmobil.=')';
if($daftarmobil==')')$daftarmobil="('')";

$sKodeOrg="select substr(kodeorg,1,4) as kodeorg, kodevhc, jumlah,volume from ".$dbname.".bgt_budget where kodevhc in ".$daftarmobil." and tahunbudget = '".$thnBudget."' AND tipebudget != 'TRK'";
$qKodeOrg=$owlPDO->query($sKodeOrg) or die(print " Gagal: ".PDOException::getMessage());
$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rKode=$qKodeOrg->fetch()){
    $daftaruser[$rKode['kodeorg']]=$rKode['kodeorg'];
    @$penggunaan[$rKode['kodevhc']][$rKode['kodeorg']]+=$rKode['jumlah'];
}

$cek=count($dtKdtraksi);

			//$no=0;
			if($kodeOrg==''||$thnBudget=='')
			{
				exit("Error:Field Tidak Boleh Kosong");
			}
            if($cek==0)
            {
            exit("Error: Data Kosong");
            }
            
//			$tab2.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
if($proses=='preview'){
            $tab="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";

}   else {
    			$tab.="<table cellpadding=5 cellspacing=1 border=1 class=sortable><thead>";
    
}        
            $tab.="<tr class=rowheader>";
            $tab.="<th align=center>No.</th>";
            $tab.="<th align=center>".$_SESSION['lang']['kodetraksi']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['kodevhc']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['nopol']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['detail']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['rupiah']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['kmperthn']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['jamperthn']."</th>";
            $tab.="<th align=center>".$_SESSION['lang']['alokasi']."</th>";
            if(!empty($daftaruser))foreach($daftaruser as $user){
                $tab.="<th align=center>".$user."</th>";
            }
            //$tab.="<th align=center>".$_SESSION['lang']['alokasirp']."</th>";
//            $tab.="<th align=center>".$_SESSION['lang']['action']."</th>";
            $tab.="</tr></thead><tbody>";
            
            
                
			foreach($dtKdvhc as $lisTraksi){
                @$terAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]=$dtAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]*$dtRpJam[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]];
				$no+=1;
				 $tab.="<tr class=rowcontent>";
                 $tab.="<td align=center>".$no."</td>";
                 $tab.="<td align=center>".getNamaOrg($kodeOrg)."</td>";
                 $tab.="<td align=center>".$lisTraksi."</td>";
				 $tab.="<td align=left>".getNopol($lisTraksi)."</td>";
				 $tab.="<td align=left>".getNopol($lisTraksi,'d')."</td>";
                 $tab.="<td align=right>".number_format($dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
                 $tab.="<td align=right>".number_format($dtRpJam[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
                 $tab.="<td align=right>".number_format($dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
                 $tab.="<td align=right>".number_format($dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi])."</td>";
                 //$tab.="<td align=right>".number_format($terAlokasi[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
            if(!empty($daftaruser))foreach($daftaruser as $user){
                $tab.="<td align=right>".number_format($penggunaan[$lisTraksi][$user])."</td>";
                @$total[$user]+=$penggunaan[$lisTraksi][$user];
            }                 
                 
                 $tab.="</tr>";
                 @$totJam+=$dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi];
                 @$totRup+=$dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi];
                 @$totKmThn+=$dtRpJam[$thnBudget][$kodeOrg][$lisTraksi];
				 @$totAlokasiJam+=$dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
				 
                 //$totAlokasiRp+=$terAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
            }
            //$no!=0?$rataRupi=$totRup/$no:$rataRupi=0;
            //$totJam!=0?$totRpkm=$rataRupi/$totJam:$totRpkm=0;
            
            $tab.="</tbody><thead><tr class=rowheader>";
            $tab.="<td align=center  colspan=5 align=center>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".number_format($totRup)."</td>";
            $tab.="<td align=right>".number_format($totKmThn)."</td>";
            $tab.="<td align=right>".number_format($totJam)."</td>";
            $tab.="<td align=right>".number_format($totAlokasiJam)."</td>";
            //$tab.="<td align=right>".number_format($totAlokasiRp,2)."</td>";
            if(!empty($daftaruser))foreach($daftaruser as $user){
                $tab.="<td align=right>".number_format($total[$user])."</td>";
                
            }   
            $tab.="</tr>";
            $tab.="</thead></table>";

switch($proses)
{			
			
	case'preview':
			

            echo $tab;
	break;
            
			
			
			
	case 'excel':
//			
//			if($thnBudget=='')
//			{
//				echo "warning : Tahun masih kosong";
//				exit();	
//			}
//			else if($kodeOrg=='')
//			{
//				echo "warning : Kode organisasi masih kosong";
//				exit();	
//			}
//			
//			$tab2="Laporan Alokasi Jam per Kendaraan <br>";
//			$tab2.=" ".$optNm[$kodeOrg]."  tahun ".$thnBudget." ";
//			$tab2.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
//            $tab2.="<tr class=rowheader bgcolor=#CCCCCC>";
//            $tab2.="<td align=center>No.</td>";
//            $tab2.="<td align=center>".$_SESSION['lang']['kodetraksi']."</td>";
//            $tab2.="<td align=center>".$_SESSION['lang']['kodevhc']."</td>";
//            $tab2.="<td align=center>".$_SESSION['lang']['jamperthn']."</td>";
//            $tab2.="<td align=center>".$_SESSION['lang']['rpperthn']."</td>";
//            $tab2.="<td align=center>".$_SESSION['lang']['kmperthn']."</td>";
//            $tab2.="<td align=center>".$_SESSION['lang']['alokasijam']."</td>";
//            if(!empty($daftaruser))foreach($daftaruser as $user){
//                $tab.="<td align=center>".$user."</td>";
//            }
//            $tab2.="</tr></thead><tbody>";
//            
//            
//                
//                 $terAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]=$dtAlokasi[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]]*$dtRpJam[$thnBudget][$lisTraksi][$dtKdvhc[$thnBudget][$lisTraksi]];
//                foreach($dtKdvhc as $lisTraksi)
//            {
//				$no+=1;
//				 $tab2.="<tr class=rowcontent>";
//                 $tab2.="<td align=center>".$no."</td>";
//                 $tab2.="<td align=center>".$kodeOrg."</td>";
//		   
//                 $tab2.="<td align=center>".$lisTraksi."</td>";
//                 $tab2.="<td align=right>".number_format($dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
//                 $tab2.="<td align=right>".number_format($dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
//                 $tab2.="<td align=right>".number_format($dtRpJam[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
//                 $tab2.="<td align=right>".number_format($dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
//                 //$tab.="<td align=right>".number_format($terAlokasi[$thnBudget][$kodeOrg][$lisTraksi],2)."</td>";
//                 $tab2.="</tr>";
//                 $totJam+=$dtJamSthn[$thnBudget][$kodeOrg][$lisTraksi];
//                 $totRup+=$dtRpSthn[$thnBudget][$kodeOrg][$lisTraksi];
//                 $totKmThn+=$dtRpJam[$thnBudget][$kodeOrg][$lisTraksi];
//				 $totAlokasiJam+=$dtAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
//				 
//                 //$totAlokasiRp+=$terAlokasi[$thnBudget][$kodeOrg][$lisTraksi];
//            }
//            //$no!=0?$rataRupi=$totRup/$no:$rataRupi=0;
//            //$totJam!=0?$totRpkm=$rataRupi/$totJam:$totRpkm=0;
//            
//            $tab2.="</tbody><thead><tr class=rowheader bgcolor=#CCCCCC>";
//            $tab2.="<td align=center  colspan=3 align=center>".$_SESSION['lang']['total']."</td>";
//            $tab2.="<td align=right>".number_format($totJam,2)."</td>";
//            $tab2.="<td align=right>".number_format($totRup,2)."</td>";
//            $tab2.="<td align=right>".number_format($totKmThn,2)."</td>";
//            $tab2.="<td align=right>".number_format($totAlokasiJam,2)."</td>";
//            //$tab.="<td align=right>".number_format($totAlokasiRp,2)."</td>";
//            $tab2.="</tr>";
//            $tab2.="</thead></table>";
		
		$tglSkrg=date("Ymd");
		$nop_="Laporan_Alokasi_Exel_".$tglSkrg;
		//$nop_"Laporan Daftar Asset ".$nmOrg."_".$nmAst;
		//$nop_="Daftar Asset : ".$nmOrg." ".$nmAst;
		if(strlen($tab)>0)
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
			//closedir($handle);
		}           
		break;			
		// tutup tampilakn panggil exel //
		
                
            default:
            break;
        }
	
?>