<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
?>

<?php

$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$jenis = checkPostGet('jenis', '');
$tipe = checkPostGet('tipe', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal', ''));
$tipeform = checkPostGet('tipeform', '');
$method = checkPostGet('method', '');

switch ($method) {
	case 'getunit':
		$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
        	$optorg.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
        }

        echo $optorg;
	break;

	case 'preview':

		if ($pt=='') {
			exit('warning : '.$_SESSION['lang']['pt'].' '.$_SESSION['lang']['kosong']);
		}
		if ($tipeform=='') {
			exit('warning : '.$_SESSION['lang']['tipe'].' '.$_SESSION['lang']['kosong']);
		}
		if ($jenis=='') {
			exit('warning : '.$_SESSION['lang']['jenis'].' '.$_SESSION['lang']['kosong']);
		}

		if ($jenis=='detail') {
			$cols='colspan=21';
		}else{
			$cols='colspan=9';
		}
		
		$style="align=center bgcolor='#C0C0C0' style='font-weight: bold;'";
		if($tipe=='excel'){
			$border=" border=1";
			$title="<tr align=center>
						<td rowspan=2 ".$cols." ".$style.">Account Payable</td>
					</tr><tr></tr>";
		}else{
			$border=" border=0";
		}

		$tab="<table cellpading=1 cellspacing=1 ".$border." class=sortable style=width:100%>
			<thead>
			".$title;

		if ($jenis=='detail') {
			$tab.="<tr>
				<td ".$style." rowspan=2>".$_SESSION['lang']['nourut']."</td>
				<td ".$style." rowspan=2>".$_SESSION['lang']['kodesupplier']."</td>
				<td ".$style." rowspan=2>".$_SESSION['lang']['namasupplier']."</td>
				<td ".$style." rowspan=2>".$_SESSION['lang']['noinvoice']."</td>
				<td ".$style." rowspan=2>".$_SESSION['lang']['tanggalinvoice']."</td>
				<td ".$style." rowspan=2>".$_SESSION['lang']['nopo']."</td>
				<td ".$style." rowspan=2>Tanggal GL</td>
				<td ".$style." rowspan=2>".$_SESSION['lang']['nodok']."</td>
				<td ".$style." rowspan=2>Currency</td>
				<td ".$style." rowspan=2>".$_SESSION['lang']['keterangan']."</td>
				<td ".$style." rowspan=2>Payable (".$_SESSION['lang']['amount'].")</td>
				<td ".$style." rowspan=2>Payment (".$_SESSION['lang']['amount'].")</td>
				<td ".$style." rowspan=2>Balance (".$_SESSION['lang']['amount'].")</td>
				<td ".$style." rowspan=2>Currency Rate</td>
				<td ".$style." rowspan=2>Balance IDR (".$_SESSION['lang']['amount'].")</td>
				<td ".$style." colspan=4>as per GL Date</td>
				<td ".$style." colspan=4>as per Invoice Date</td>
			</tr>
			<tr>
				<td ".$style."><=30</td>
				<td ".$style.">31 - 60</td>
				<td ".$style.">61 - 90</td>
				<td ".$style.">>90</td>
				<td ".$style."><=30</td>
				<td ".$style.">31 - 60</td>
				<td ".$style.">61 - 90</td>
				<td ".$style.">>90</td>
			</tr>";
		}

		if ($jenis=='summary') {
			$tab.="<tr>
				<td ".$style.">".$_SESSION['lang']['nourut']."</td>
				<td ".$style.">".$_SESSION['lang']['kodesupplier']."</td>
				<td ".$style.">".$_SESSION['lang']['namasupplier']."</td>
				<td ".$style.">".$_SESSION['lang']['matauang']."</td>
				<td ".$style.">Payable (".$_SESSION['lang']['amount'].")</td>
				<td ".$style.">Payment (".$_SESSION['lang']['amount'].")</td>
				<td ".$style.">Balance (".$_SESSION['lang']['amount'].")</td>
				<td ".$style.">Currency Rate</td>
				<td ".$style.">Balance IDR (".$_SESSION['lang']['amount'].")</td>
			</tr>";
		}
		$tab.="</thead><tbody>";

		$whrrnv="";
		if ($unit!='') {
			$whrrnv=" and left(kodegudang,4)='".$unit."'";
			$whrrnv2=" and left(kodeblok,4)='".$unit."'";
			// $whrrnv.=" and notransaksi not in (select notransaksi_gr from ".$dbname.".keu_tagihanht where unit='".$unit."')";
		}else{
			$whrrnv=" and left(kodegudang,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$whrrnv2=" and left(kodeblok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			// $whrrnv.=" and notransaksi not in (select notransaksi_gr from ".$dbname.".keu_tagihanht where kodeorg='".$pt."')";
		}

		$whrinv="";
		if ($unit!='') {
			$whrinv=" and unit='".$unit."'";
			$whrinv2=" and left(kodeblok,4)='".$unit."'";
		}else{
			$whrinv=" and kodeorg='".$pt."'";
			$whrrnv2=" and left(kodeblok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		}
 		
        $sDetKas="select sum(jumlah) as jumlah,keterangan1,notransaksi,a.posting, a.tanggal from ".$dbname.".keu_kasbankdtht_vw 
                  a left join ".$dbname.".keu_tagihanht b on a.keterangan1=b.noinvoice 
                  where a.tanggal<='".$tanggal."' and left(a.noakun,3) in ('211','118','121','213')
				group by keterangan1,notransaksi";
        $rDetKas=fetchData($sDetKas);
        foreach($rDetKas as $row=>$lst){
            $payment[$lst['keterangan1']]+=$lst['jumlah'];
        }
		#ambil data dari detail tagihan
		$rDet=array();
        $nilPPn=array();
        $nilUangMuka=array();
        $nilpph=array();
        $sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
               ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$whrinv." 
               and left(a.noakun,3) in ('117','118','213','711','116','115') group by a.noinvoice,a.noakun";
        //echo $sDet;
        // exit('warning:'.$sDet);
        $rDet=fetchdata($sDet);
        foreach($rDet as $row=>$lstData){
            if(substr($lstData['noakun'],0,3)=='117'){
                $nilPPn[$lstData['noinvoice']]+=$lstData['nilai'];    
            }
            if($lstData['nilai']<0){
                if(substr($lstData['noakun'],0,3)=='118'){
                    $nilUangMuka[$lstData['noinvoice']]+=$lstData['nilai'];
                } 
                if(substr($lstData['noakun'],0,3)=='213'){
                    $nilpph[$lstData['noinvoice']]+=$lstData['nilai'];
                } 
                if((substr($lstData['noakun'],0,3)=='711')||(substr($lstData['noakun'],0,3)=='116')||(substr($lstData['noakun'],0,3)=='115')){
                    $bylain[$lstData['noinvoice']]+=$lstData['nilai'];
                }   
            }
            
            
            
        }

        $ayatsilang=array();
        $sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
               ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$whrinv." 
               and nilai<0 and a.noakun='1110401' group by a.noinvoice,b.noakun";
        // exit('warning:'.$sDet);
        $rDet=fetchdata($sDet);
        foreach($rDet as $row=>$lstData){
            $ayatsilang[$lstData['noinvoice']]+=$lstData['nilai'];
        }
        #tipe invoice 
        $optJenis=array();
        $sJenis="select * from ".$dbname.".keu_5jenistagihan";
        $rJenis=fetchData($sJenis);
        foreach($rJenis as $row=>$data){
            if($data['jurnal']==1){
                $optJenis[$data['kode']].="NVM : ".$data['namajenis']."";
            }
            else{
                $optJenis[$data['kode']].="VM : ".$data['namajenis']."";
            }
        }

		if ($tipeform=='rnv' && $jenis=='detail') {
				// select a.notransaksi as notransaksi_gr, a.keterangan as keterangan2, a.tanggal, sum(hartot) as jumlah, idsupplier, a.nopo, b.matauang, b.kurs, c.noinvoice, c.tanggal as tanggalinv from sthdev.log_transaksi_vw a left join sthdev.log_poht b on a.nopo=b.nopo left join sthdev.keu_tagihanht c on a.notransaksi=c.notransaksi_gr where tipetransaksi=1 and statussaldo=1 and post=1 and noinvoice is NULL and a.tanggal<='2018-09-05' and left(kodegudang,4) in (select kodeorganisasi from sthdev.organisasi where induk='TML') group by notransaksi,idsupplier,nopo
				// union
				// where  a.tanggal<='2018-09-05' and left(kodeblok,4) in (select kodeorganisasi from sthdev.organisasi where induk='TML') and statusjurnal=1 group by a.notransaksi,b.koderekanan
			$str="select a.notransaksi as notransaksi_gr, a.keterangan as keterangan2, a.tanggal, sum(hargasatuan*jumlah) as jumlah, idsupplier, a.nopo, b.matauang, b.kurs from ".$dbname.".log_transaksi_vw  a 
				left join ".$dbname.".log_poht b on a.nopo=b.nopo 
				where tipetransaksi=1 and statussaldo=1 and post=1  and a.tanggal<='".$tanggal."' ".$whrrnv." group by notransaksi,idsupplier,nopo
				union 
				select a.notransaksi as notransaksi_gr,d.namakegiatan as keterangan2,a.tanggal,sum(jumlahrealisasi) as jumlah,b.koderekanan as idsupplier,a.notransaksi as nopo,b.matauang,a.statusjurnal as kurs from ".$dbname.".log_baspk a left join  ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi left join  ".$dbname.".setup_kegiatan d on a.kodekegiatan=d.kodekegiatan
				where a.tanggal<='".$tanggal."' ".$whrrnv2." and statusjurnal=1 group by a.notransaksi,b.koderekanan";
		}

		if ($tipeform=='inv' && $jenis=='detail') {
			//$str="select kodesupplier as idsupplier, noinvoice, nopo, notransaksi_gr, matauang, kurs, tanggal as tanggalinv, keterangan2, nilaiinvoice as jumlah from ".$dbname.".keu_tagihanht where tipeinvoice in (select kode from ".$dbname.".keu_5jenistagihan where jurnal=0) and tanggal<='".$tanggal."' ".$whrinv." ";
			$str="select kodesupplier as idsupplier, noinvoice, nopo, notransaksi_gr, matauang, kurs, tanggal as tanggalinv, keterangan2, nilaiinvoice as jumlah,tipeinvoice from ".$dbname.".keu_tagihanht where tipeinvoice in ('p','k') and tanggal<='".$tanggal."' ".$whrinv." ";

		}

		if ($tipeform=='rnv' && $jenis=='summary') {

			//$str="select c.noinvoice, idsupplier, sum(hargasatuan*jumlah) as jumlah, b.matauang, b.kurs from ".$dbname.".log_transaksi_vw  a left join ".$dbname.".log_poht b on a.nopo=b.nopo left join ".$dbname.".keu_tagihanht c on a.notransaksi=c.notransaksi_gr  where tipetransaksi=1 and statussaldo=1 and post=1 and noinvoice is NULL and a.tanggal<='".$tanggal."' ".$whrrnv." group by idsupplier,matauang";
			$str="select a.notransaksi as notransaksi_gr, a.keterangan as keterangan2, a.tanggal, sum(hargasatuan*jumlah) as jumlah, idsupplier, a.nopo, b.matauang, b.kurs, c.noinvoice, c.tanggal as tanggalinv from ".$dbname.".log_transaksi_vw  a 
				left join ".$dbname.".log_poht b on a.nopo=b.nopo 
				left join ".$dbname.".keu_tagihanht c on a.notransaksi=c.notransaksi_gr 
				where tipetransaksi=1 and statussaldo=1 and post=1 and noinvoice is NULL and a.tanggal<='".$tanggal."' ".$whrrnv." group by notransaksi,idsupplier,nopo
				union 
				select a.notransaksi as notransaksi_gr,d.namakegiatan as keterangan2,a.tanggal,sum(jumlahrealisasi) as jumlah,b.koderekanan as idsupplier,a.notransaksi as nopo,b.matauang,a.statusjurnal as kurs,c.noinvoice,c.tanggal as tanggalinv from ".$dbname.".log_baspk a left join  ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi left join  ".$dbname.".keu_tagihanht c on a.notransaksi =c.nopo left join  ".$dbname.".setup_kegiatan d on a.kodekegiatan=d.kodekegiatan
				where a.tanggal<='".$tanggal."' ".$whrrnv2." and statusjurnal=1 and noinvoice is NULL";
		}

		if ($tipeform=='inv' && $jenis=='summary') {
			$str="select kodesupplier as idsupplier, matauang, kurs, sum(nilaiinvoice) as jumlah, noinvoice,tipeinvoice from ".$dbname.".keu_tagihanht where tipeinvoice in (select kode from ".$dbname.".keu_5jenistagihan where jurnal=0) and tanggal<='".$tanggal."' ".$whrinv."  group by kodesupplier,matauang";
		}

		$no=0;
		// echo $str;
		// exit('warning'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) {
			if($tipeform=='rnv' && $jenis=='detail'){
				$scekrnv="select * from ".$dbname.".keu_tagihanht where nopo='".$bar['nopo']."' and tanggal<='".$tanggal."'";
				$rcekrnv=fetchData($scekrnv);
				if(count($rcekrnv)!=0){
						continue;
				}
			}
			#Tanggal GL
			$tglgl=array();
			$str1="select tanggal from ".$dbname.".keu_jurnalht where noreferensi='".$bar['notransaksi_gr']."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
			$tglgl[$bar['notransaksi_gr']]=$bar1['tanggal'];
			#nilai balance
			//$sisaDt=($val['nilaiinvoice']+$nilPPn[$val['noinvoice']]+$ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$nilpph[$val['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$val['noinvoice']])-$totByrKan[$val['noinvoice']];
			if($tipeform=='inv'){
				if($optJenis[$bar['tipeinvoice']]=="NVM"){
					$bar['jumlah']=$bar['jumlah']-$nilPPn[$bar['noinvoice']];
				}
			}else{
				$nilPPn[$bar['noinvoice']]=0;
				$payment[$bar['noinvoice']]=0;
			}
			$sDetnota="select sum(nilaiinvoice*-1) as nildebet from ".$dbname.".keu_notadebet_ht where noinvoice_referensi='".$bar['noinvoice']."' group by noinvoice_referensi";
            $rDetnota=fetchdata($sDetnota);

			$bar['jumlah']=($bar['jumlah']+$nilPPn[$bar['noinvoice']]+$ayatsilang[$bar['noinvoice']]+$nilUangMuka[$bar['noinvoice']]+$nilpph[$bar['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$bar['noinvoice']]);
			if($bar['kurs']==0){
				$bar['kurs']=1;
			}
			$balance=($bar['jumlah']*$bar['kurs'])-$payment[$bar['noinvoice']];
			$balancekurs=$balance; 
			

			#nama supplier
			$whrsup="supplierid='".$bar['idsupplier']."'";
		    $nmsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whrsup);

		    #jumlah hari per GL (tgl GL s/d tgl Invoice)
		    $tglglex="0000-00-00";
		    if($tglgl[$bar['notransaksi_gr']]!=''){
		    	$tglglex = explode("-", $tglgl[$bar['notransaksi_gr']]);	
		    }
            
            $date1 = $tglglex[2];
            $month1 = $tglglex[1];
            $year1 =  $tglglex[0];
			$tglinvex=explode("-",$bar['tanggalinv']);
			$date2 = $tglinvex[2];
            $month2 = $tglinvex[1];
            $year2 = $tglinvex[0]; 
			@$jd1 = GregorianToJD($month1, $date1, $year1);
            @$jd2 = GregorianToJD($month2, $date2, $year2);
            @$jmlHaripergl=@$jd2-@$jd1;
            if ($bar['tanggalinv']=='') {
	          	$jmlHariperinv='';
	        }   
			$flag30gl=$flag60gl=$flag90gl=$flag100gl='';
            if(($jmlHaripergl>=1)and($jmlHaripergl<=30))$flag30gl=$jmlHaripergl;
            if(($jmlHaripergl>=31)and($jmlHaripergl<=60))$flag60gl=$jmlHaripergl;
            if(($jmlHaripergl>=61)and($jmlHaripergl<=90))$flag90gl=$jmlHaripergl1;
            if($jmlHaripergl>90)$flag100gl=$jmlHaripergl;

		    #jumlah hari per invoice (tgl Invoice s/d tgl hari ini)
            $tglhriniex = explode("-", date("Y-m-d"));
            $date2 = $tglhriniex[2];
            $month2 = $tglhriniex[1];
            $year2 =  $tglhriniex[0];
			$tglinvex=explode("-",$bar['tanggalinv']);
			$date1 = $tglinvex[2];
            $month1 = $tglinvex[1];
            $year1 = $tglinvex[0]; 
			@$jd1 = GregorianToJD($month1, $date1, $year1);
            @$jd2 = GregorianToJD($month2, $date2, $year2);
            @$jmlHariperinv=@$jd2-@$jd1; 
            if ($bar['tanggalinv']=='') {
	          	$jmlHariperinv='';
	        }          
			$flag30inv=$flag60inv=$flag90inv=$flag100inv='';
            if(($jmlHariperinv>=1)and($jmlHariperinv<=30))$flag30inv=$jmlHariperinv;
            if(($jmlHariperinv>=31)and($jmlHariperinv<=60))$flag60inv=$jmlHariperinv;
            if(($jmlHariperinv>=61)and($jmlHariperinv<=90))$flag90inv=$jmlHariperinv;
            if($jmlHariperinv>90)$flag100inv=$jmlHariperinv;

			$no+=1;
			if ($jenis=='detail') {
				$tab.="<tr class=rowcontent>
				    <td style='text-align:center;'>".$no."</td>
				    <td>".$bar['idsupplier']."</td>
				    <td>".$nmsupp[$bar['idsupplier']]."</td>
				    <td>".$bar['noinvoice']."</td>";
				if($tipeform=='inv'){
					if($tipe=='html'){
						$tab.="<td>".tanggalnormal($bar['tanggalinv'])."</td>";
					}else{
						$tab.="<td>".$bar['tanggalinv']."</td>";
					}
				}else{
					$tab.="<td>&nbsp;</td>";
				}
				// $tgldt=$tglgl[$bar['notransaksi_gr']];
				// if(($tglgl[$bar['notransaksi_gr']]=='')||(is_null($tglgl[$bar['notransaksi_gr']]))){
				// 	$tgldt="";
				// }

				$tab.="<td>".$bar['nopo']."</td>";
				if($tipe=='html'){
					$tab.="<td>".tanggalnormal($tglgl[$bar['notransaksi_gr']])."</td>";
				}else{
					$tab.="<td>".$tglgl[$bar['notransaksi_gr']]."</td>";
				}
				
				$tab.="<td>".$bar['notransaksi_gr']."</td>
				    <td align=center>".$bar['matauang']."</td>
				    <td align=justify>".$bar['keterangan2']."</td>
				    <td align=right>".number_format($bar['jumlah'])."</td>
				    <td align=right>".number_format($payment[$bar['noinvoice']])."</td>
				    <td align=right>".number_format($balance)."</td>
				    <td align=center>".$bar['kurs']."</td>
				    <td align=right>".number_format($balancekurs)."</td>
				    <td align=center>".$flag30gl."</td>
				    <td align=center>".$flag60gl."</td>
				    <td align=center>".$flag90gl."</td>
				    <td align=center>".$flag100gl."</td>
				    <td align=center>".$flag30inv."</td>
				    <td align=center>".$flag60inv."</td>
				    <td align=center>".$flag90inv."</td>
				    <td align=center>".$flag100inv."</td></tr>";
			}

			if ($jenis=='summary') {
				$tab.="<tr class=rowcontent>
				    <td style='text-align:center;'>".$no."</td>
				    <td>".$bar['idsupplier']."</td>
				    <td>".$nmsupp[$bar['idsupplier']]."</td>
				    <td align=center>".$bar['matauang']."</td>
				    <td align=right>".number_format($bar['jumlah'])."</td>
				    <td align=right>".number_format($payment[$bar['noinvoice']])."</td>
				    <td align=right>".number_format($balance)."</td>
				    <td align=center>".$bar['kurs']."</td>
				    <td align=right>".number_format($balancekurs)."</td></tr>";
			}
			
		}

		$tab.="</tbody ".$style.">";
		$tab.="</table ".$style.">";

		if($tipe=='html'){
			echo $tab;
		}else{
			$tglSkrg = date("Ymd");
			$nop_ = "lap_agingschedule (".$pt."-".$tglSkrg.")";
			if (strlen($tab) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $tab)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script ".$style.">";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script ".$style.">";
				}
				fclose($handle);
		    }
		}
	break;
	
}



?>