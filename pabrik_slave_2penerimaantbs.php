<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}

$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk');

//bikin array tanggal 
function dates_inbetween($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);
   
    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[] = date('Y-m-d',$date2);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[] = date('Y-m-d',$date1);        
    }
    return $dates_array;
}


//$arr="##periode##tipeIntex##unit";
$divisi   =checkPostGet('divisi','');
$ip       =checkPostGet('intiplasma','');
$periode  =checkPostGet('periode','');
$tipeIntex=checkPostGet('tipeIntex','');
$unit     =checkPostGet('unit','');
$kodeOrg  =checkPostGet('kodeOrg','');
$brsKe    =checkPostGet('brsKe','');
$tgl_1    =tanggalsystem(checkPostGet('tgl_1','00-00-0000'));
$tgl_2    =tanggalsystem(checkPostGet('tgl_2','00-00-0000'));
$kdBlok   =checkPostGet('kdBlok','');
$nospb    =checkPostGet('nospb','');
$kdPabrik =checkPostGet('kdPabrik','');
$pilTamp  =checkPostGet('pilTamp','');
$optSupp  =makeOption($dbname, 'log_5supplier', 'kodetimbangan,namasupplier');
$optNm    =makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$dateDt   =dates_inbetween($tgl_1,$tgl_2);
$intex    =array('1'=>'Internal','2'=>'Afiliasi','0'=>'External');

function daysBetween($s, $e){
	$s = strtotime($s);
	$e = strtotime($e);

	return ($e - $s)/ (24 *3600);
}
$erd=explode("-",checkPostGet('tgl_1','00-00-0000'));
$erd2=explode("-",checkPostGet('tgl_2','00-00-0000'));
$tgl1=$erd[2]."-".$erd[1]."-".$erd[0];
$tgl2=$erd2[2]."-".$erd2[1]."-".$erd2[0];
$archeck=daysBetween($tgl1,$tgl2);

if($pilTamp==0){
	if($archeck>31){
		exit("error: max 31 days");
	}
}

if($pilTamp==1){
	if($archeck>7){
		exit("error: max 7 days");
	}
}
   
 
switch($proses){
    case 'getdiv':
        $optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
        $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','AFDELING') and induk ='".$unit."' order by namaorganisasi asc";
        $res=fetchData($str);
        foreach ($res as $val) {
            $optdivisi.="<option value=".$val['kodeorganisasi'].">".$val['namaorganisasi']."</option>";
        }

        echo $optdivisi;
    break;

	case'preview':
	case'excel':
	
	if($kdPabrik!=''){
		$where.=" and millcode='".$kdPabrik."'";
	}
	if($ip!=''){
		$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where inti='".$ip."')";
	}        
	
	if(($tgl_1!='')&&($tgl_2!='')){
		$where.=" and tanggal >= ".$tgl_1."000000 and tanggal<=".$tgl_2."235959";
	}
	else{
		echo"warning: Date required";
		exit();
	}

	if($tipeIntex==3){
		$gr=" group by kodeorg,kodecustomer,left(tanggal,10),intex";
	}
	else if($tipeIntex==0){ //external
		if($unit!=''){
            if(getNamaSupplier($unit) != ''){
                $whr.=" and kodesupplier='".$unit."' ";
            }else{
                $whr.=" and kodecustomer='".$unit."' ";
            }
		}else{
            if(getindukPT($unit)=='PPP'){//dibedakan dengan palma khawatir ngefek
                $whr.=" and kodeorg='' and kodecustomer!='' ";
            }else{
                $whr.=" and kodeorg=''";
            }
		}
		$gr=" group by kodecustomer,left(tanggal,10),intex";
		//$whr.="and intex='".$tipeIntex."'";
	} else if ($tipeIntex==1) { //internal
		if($unit!=''){
			$whr.=" and kodeorg='".$unit."' ";
		}else{
			$whr.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$kdPabrik]."') ";
		}

        #divisidisi
        $whrdiv="";
        if ($divisi!='') {
            $whrdiv="and divcode ='".$divisi."'";
        }

	   $gr=" group by kodeorg,left(tanggal,10)"; 
	   //$whr.="and intex='".$tipeIntex."'";
	} else if($tipeIntex==2){
		if($unit!=''){
			$whr.=" and kodeorg='".$unit."' ";
		}else{
			$whr.=" and kodeorg not in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$kdPabrik]."') ";
		}

        #divisidisi
        $whrdiv="";
        if ($divisi!='') {
            $whrdiv="and divcode ='".$divisi."'";
        }

	   $gr=" group by kodeorg,left(tanggal,10)"; 
	}
	$tab="";
	$border="border=0";
	if($proses=='excel'){
		$border="border=1";
		$tab.=$_SESSION['lang']['rPenerimaanTbs'].", ".$_SESSION['lang']['periode']." :".$tgl1." s.d. ".$tgl1."<br>";
	}
	
	$sData="select beratmasuk, divcode, notransaksi,kodeorg,kodetimbangan,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,supir,nokendaraan,nospb,thntm1,intex,kgpotsortasi, jammasuk, jamkeluar, millcode, kriteriabuah, timbang1, timbang2, kodesupplier from ".$dbname.".pabrik_timbangan a left join ".$dbname.".log_5suptimbangan b on a.kodecustomer=b.kodetimbangan where kodebarang='40000003' ".$where." ".$whr." ".$whrdiv." order by tanggal,notransaksi asc";
	
    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$brs=owlBaris($qData);
	if($brs>0) {
		if($pilTamp!=1) {
			$tab.="<table cellspacing=1 cellpadding=5 ".$border." class=sortable>
			<thead class=rowheader>
			<tr>
				<th align=center rowspan=2>No.</th>
				<th align=center colspan=4>".$_SESSION['lang']['tanggal']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['namasupplier']."/".$_SESSION['lang']['unit']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['noTiket']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['kodenopol']."</th>
				<th align=center>Bruto</th>
				<th align=center>Tara</th>
				<th align=center>Netto I</th>
				<th align=center colspan=2>Sortasi</th>
				<th align=center>Netto II</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['sopir']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['nospb']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['jmlhTandan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['bjr']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['keterangan']."</th>
			</tr>
			<tr>
				<th align=center colspan=2>".$_SESSION['lang']['masuk']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['keluar']."</th>
				<th align=center>".$_SESSION['lang']['kg']."</th>
				<th align=center>".$_SESSION['lang']['kg']."</th>
				<th align=center>".$_SESSION['lang']['kg']."</th>
				<th align=center>".$_SESSION['lang']['kg']."</th>
				<th align=center>".$_SESSION['lang']['%']."</th>
				<th align=center>".$_SESSION['lang']['kg']."</th>
			</tr>
			</thead>
			<tbody>";
			$dtIntex="";
			$subtota=$subTnandn=$sbTotaJjg=$subTotNett=$subBrtNor=$subharga=$subBrtPot=0;
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while($rData=$qData->fetch()) {
				$no+=1;
				if($dtIntex!=$rData['intex'])  {
					$dtIntex=$rData['intex'];
					$sData2="select notransaksi,kodeorg,kodetimbangan,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
					supir,nokendaraan,nospb,thntm1,intex, jammasuk, jamkeluar, millcode
					from ".$dbname.".pabrik_timbangan a left join ".$dbname.".log_5suptimbangan b on a.kodecustomer=b.kodetimbangan where kodebarang='40000003' ".$where." ".$whr."  ".$gr."  order by intex desc";//and intex='".$rData['intex']."'
					//exit('error'.$sData2);

					$qData2=$owlPDO->query($sData2) or die(print " Gagal: ".PDOException::getMessage());
					$rowData=owlBaris($qData2);
					
					$rd=0;
				}
				if($rData['intex']!=0){
					$nm=$optNm[$rData['kodeorg']];
				}else{
					// $strs="select b.namasupplier from ".$dbname.".log_5suptimbangan a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.kodetimbangan='".$rData['kodecustomer']."'";
					// $ress=fetchdata($strs);
					$nm = ($rData['kodecustomer'] != '' ? getNamaCustomer($rData['kodecustomer']) : getNamaSupplier($rData['kodesupplier']) );
				}
				$brtNormal=$rData['netto']-$rData['kgpotsortasi'];
				setIt($kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']],0);
				$harga=$brtNormal*$kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']];
				$bgwarna="";
				if($rData['nospb']!=''){
					$scek="select distinct * from ".$dbname.".kebun_spbdt where nospb='".$rData['nospb']."' and substr(nospb,9,6)<>left(blok,6)";
					$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
					$rcek=owlBaris($qcek);
					if($rcek==1){
						$bgwarna="bgcolor=yellow title='ada buah dari afdeling lain'";
					}
				}

				$potonganpersen=($rData['kgpotsortasi']/$rData['netto'])*100;
				
				$tab.="
				<tr class=rowcontent>
				<td align=center >".$no."</td>
				<td style='width:65px'>".tanggalnormal($rData['timbang1'])."</td>
				<td>".$rData['jammasuk']."</td>
				<td style='width:65px'>".tanggalnormal($rData['timbang2'])."</td>
				<td>".$rData['jamkeluar']."</td>
				<td>".$nm."</td>
				<td>".$optNm[$rData['divcode']]."</td>
				<td>".$rData['notransaksi']."</td>
				<td>".$rData['nokendaraan']."</td>
				<td  align=right>".number_format($rData['beratmasuk'],0)."</td>
				<td  align=right>".number_format($rData['beratmasuk']-$rData['netto'],0)."</td>
				<td  align=right>".number_format($rData['netto'],0)."</td>
				<td  align=right>".number_format($rData['kgpotsortasi'],0)."</td>
				<td  align=right>".number_format($potonganpersen,2)."</td>
				<td  align=right>".number_format($brtNormal,0)."</td>";
				$tab.="<td>".$rData['supir']."</td>
				<td ".$bgwarna.">".$rData['nospb']."</td>
				<td align=right>".number_format($rData['jjg'],0)."</td>
				<td align=right>".number_format($rData['netto']/$rData['jjg'],2)."</td>
                <td align=right>".($rData['jjg']!=0?'TBS': 'BRONDOLAN')."</td>
				</tr>";
				
				$submasuk+=$rData['beratmasuk'];
				$subtota+=$rData['netto'];
				$subTnandn+=$rData['jjg'];
				$sbTotaJjg+=$rData['jjg'];
				$subTotNett+=$rData['netto'];
				$subBrtNor+=$brtNormal;
				$subharga+=$harga;
				$subBrtPot+=$rData['kgpotsortasi'];
				$subpotpersen+=$potonganpersen;
				$rd+=1;
				if($rowData==$rd){
					$sbTotaJjg=0;
					$subTotNett=0;
				}
			   $brtNormal=0;
			}
			$tab.="<tr class=rowcontent >
				<td colspan=9 align=right>Total</td>
				<td align=right>".number_format($submasuk,0)."</td>
				<td align=right>".number_format($submasuk-$subtota,0)."</td>
				<td align=right>".number_format($subtota,0)."</td>
				<td align=right>".number_format($subBrtPot,0)."</td>
				<td align=right>".number_format(($subBrtPot/$subtota)*100,2)."</td>
				<td align=right>".number_format($subBrtNor,0)."</td>";
				$tab.="<td ></td>";
				$tab.="<td ></td>";
				$tab.="<td align=right>".number_format($subTnandn,0)."</td>";
				$tab.="<td align=right>".number_format($subtota/$subTnandn,2)."</td>";
				$tab.="
			</tr></table>";
			   
		}else{
			$dateDt="";
			$dateDt=array();
			//exit("error:".$sData);
			$sData="select notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
			supir,nokendaraan,nospb,thntm1,intex
			from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where." ".$whr." 
			order by substr(tanggal,1,10) asc";

			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while($rData=$qData->fetch())
			{
					$dateDt[$rData['tanggal']]=$rData['tanggal'];
					setIt($dtData[$rData['intex']][$rData['kodeorg'].$rData['tanggal']],0);
					setIt($dtDataJg[$rData['intex']][$rData['kodeorg'].$rData['tanggal']],0);
					setIt($dtData2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']],0);
					setIt($dtDataJg2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']],0);
					if($rData['intex']>0)
					{
						$dtSupp[$rData['intex'].$rData['kodeorg']]=$rData['kodeorg'];
						$dtData[$rData['intex']][$rData['kodeorg'].$rData['tanggal']]+=$rData['netto'];
						$dtDataJg[$rData['intex']][$rData['kodeorg'].$rData['tanggal']]+=$rData['jjg'];
					}
					else
					{
						$dtSupp[$rData['intex'].$rData['kodecustomer']]=$rData['kodecustomer'];
						$dtData2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']]+=$rData['netto'];
						$dtDataJg2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']]+=$rData['jjg'];
					}
			   
			}
			
			$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable><thead>";
			$tab.="<tr class=rowheader><th rowspan=2 align=center>".$_SESSION['lang']['namasupplier']." / ".$_SESSION['lang']['unit']."</th>";
			@array_multisort($dtSupp);
			@array_multisort($dateDt);
			foreach($dateDt as $ar => $isi)
			{
					$qwe=date('D', strtotime($isi));
					$tab.="<th align=center colspan=2>";
					if($qwe=='Sun')$tab.="<font color=red>".tanggalnormal($isi)."</font>"; else $tab.=tanggalnormal($isi); 
					$tab.="</th>";
			}
			 $tab.="<th align=center colspan=2>".$_SESSION['lang']['total']."</th>";
			$tab.="</tr><tr>";
			foreach($dateDt as $ar => $isi)
			{
				$tab.="<th align=center width=70px >".$_SESSION['lang']['beratBersih']." (Kg)</th>";
				$tab.="<th align=center width=70px >".$_SESSION['lang']['jmlhTandan']." (JJG)</th>";
			}
			$tab.="<th align=center  width=70px >".$_SESSION['lang']['beratBersih']." (Kg)</th>";
			$tab.="<th align=center  width=70px>".$_SESSION['lang']['jmlhTandan']." (JJG)</th>";
			$tab.="</tr></thead><tbody>";
		
			foreach($intex as $lstIntex=>$isiTex){
				foreach($dtSupp as $lsdtSup){
					 if(!empty($dtSupp[$lstIntex.$lsdtSup]))
					 {
					if($lstIntex==0)
					 {
						 $dtData=$dtData2;
						 $dtDataJg=$dtDataJg2;
					 }
					 
					 if($lstIntex!=0)
					 {
						  $nm=$optNm[$dtSupp[$lstIntex.$lsdtSup]];
					 }
					 else
					 {
						$strs="select b.namasupplier from ".$dbname.".log_5suptimbangan a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.kodetimbangan='".$dtSupp[$lstIntex.$lsdtSup]."'";
						$ress=fetchdata($strs);
						$nm = $ress[0]['namasupplier'];
						 // $optramp = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$dtSupp[$lstIntex.$lsdtSup]."'");
						  // $nm=($optSupp[$dtSupp[$lstIntex.$lsdtSup]]==''?$optramp[$dtSupp[$lstIntex.$lsdtSup]]:$optSupp[$dtSupp[$lstIntex.$lsdtSup]]);
					 }
					 
					$tab.="<tr class=rowcontent><td>".$nm."</td>";
					setIt($totsmpngkg[$lstIntex.$lsdtSup],0);
					setIt($totsmpngjjg[$lstIntex.$lsdtSup],0);
					foreach($dateDt as $ar => $isi)
					{
						setIt($dtData[$lstIntex][$lsdtSup.$isi],0);
						setIt($dtDataJg[$lstIntex][$lsdtSup.$isi],0);
						setIt($totKg[$isi],0);
						setIt($totJjg[$isi],0);
						setIt($totInKg[$lstIntex.$isi],0);
						setIt($totInJjg[$lstIntex.$isi],0);
						$tab.="<td align=right>".number_format($dtData[$lstIntex][$lsdtSup.$isi],0)."</td>";
						$tab.="<td align=right>".number_format($dtDataJg[$lstIntex][$lsdtSup.$isi],0)."</td>";
						$totKg[$isi]+=$dtData[$lstIntex][$lsdtSup.$isi];
						$totJjg[$isi]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
						$totsmpngkg[$lstIntex.$lsdtSup]+=$dtData[$lstIntex][$lsdtSup.$isi];
						$totsmpngjjg[$lstIntex.$lsdtSup]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
						$totInKg[$lstIntex.$isi]+=$dtData[$lstIntex][$lsdtSup.$isi];
						$totInJjg[$lstIntex.$isi]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
					}
					$tab.="<td align=right>".number_format($totsmpngkg[$lstIntex.$lsdtSup],0)."</td>";
					$tab.="<td align=right>".number_format($totsmpngjjg[$lstIntex.$lsdtSup],0)."</td>";
					$tab.="</tr>";
					setIt($totkgsmpng[$lstIntex],0);
					setIt($totjjgsmpng[$lstIntex],0);
					$totkgsmpng[$lstIntex]+=$totsmpngkg[$lstIntex.$lsdtSup];
					$totjjgsmpng[$lstIntex]+=$totsmpngjjg[$lstIntex.$lsdtSup];
					}
				}
				if(!isset($drt) or $drt!=$lstIntex)
				{
					$drt=$lstIntex;
					$tab.="<tr style=background:darkblue><td><font>".$intex[$lstIntex]."</font></td>";
					foreach($dateDt as $ar => $isi)
					{
						$tab.="<td align=right style=background:MediumBlue><font color=white>".number_format($totInKg[$lstIntex.$isi],0)."</font></td>";
						$tab.="<td align=right style=background:darkblue><font color=white>".number_format($totInJjg[$lstIntex.$isi],0)."</font></td>";
					}
					$tab.="<td align=right style=background:MediumBlue><font color=white>".number_format($totkgsmpng[$lstIntex],0)."</font></td>";
					$tab.="<td align=right><font>".number_format($totjjgsmpng[$lstIntex],0)."</font></td>";
					$tab.="</tr>";
				}
				setIt($totSmaKg,0);
				setIt($totSmaJjg,0);
				$totSmaKg+=$totkgsmpng[$lstIntex];
				$totSmaJjg+=$totjjgsmpng[$lstIntex];
			}
			$tab.="<tr style=background:DarkGreen><td><font>".$_SESSION['lang']['total']."</font></td>";
			foreach($dateDt as $ar => $isi)
			{
				$tab.="<td align=right style=background:Green><font color=white>".number_format($totKg[$isi],0)."</font></td>";
				$tab.="<td align=right style=background:DarkGreen><font color=white>".number_format($totJjg[$isi],0)."</font></td>";
			}
			$tab.="<td align=right style=background:Green><font color=white>".number_format($totSmaKg,0)."</font></td>";
			$tab.="<td align=right><font>".number_format($totSmaJjg,0)."</font></td>";
			$tab.="</tr></tbody></table>";
		}
		if($proses!="excel"){				
			echo $tab;
		}else{
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            
            $ttdJabatan = 'KTU PKS';
            $ttdNama = '(......................................................)';
            $namaPT = 'PT. ...............';

            if($kdPabrik != '') {
                $induk = $pt[$kdPabrik];
                if($induk != '') {
                    $namaPT = $optNm[$induk];
                }
                
                $sTtd = "select a.jabatan, b.namakaryawan from ".$dbname.".setup_2ttd a 
                         left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
                         where a.menuid='748' and a.kodeunit='".$kdPabrik."' limit 1";
                $qTtd = $owlPDO->query($sTtd) or die(print " Gagal: ".PDOException::getMessage());
                $qTtd->setFetchMode(PDO::FETCH_ASSOC);
                if($rTtd = $qTtd->fetch()) {
                    if($rTtd['namakaryawan'] != '') {
                        $ttdJabatan = $rTtd['jabatan'];
                        $ttdNama = "<u>".$rTtd['namakaryawan']."</u>";
                    }
                }
            }

            $tab.="<br><br>
            <table border=0>
                <tr>
                    <td colspan=15></td>
                    <td colspan=3 align=center>Disetujui,</td>
                </tr>
                <tr>
                    <td colspan=15></td>
                    <td colspan=3 align=center>".$ttdJabatan." ".$namaPT."</td>
                </tr>
                <tr><td colspan=15></td><td colspan=3>&nbsp;</td></tr>
                <tr><td colspan=15></td><td colspan=3>&nbsp;</td></tr>
                <tr><td colspan=15></td><td colspan=3>&nbsp;</td></tr>
                <tr><td colspan=15></td><td colspan=3>&nbsp;</td></tr>
                <tr>
                    <td colspan=15></td>
                    <td colspan=3 align=center>".$ttdNama."</td>
                </tr>
            </table>";

			$tglSkrg=date("Ymd");
			$qwe=date("Hms");
			$nop_="LaporanPenerimaanTbs".$tglSkrg."__".$qwe;
			if(strlen($tab)>0){
				header("Cache-Control: must-revalidate");
				header("Pragma: must-revalidate");
				header("Content-type: application/vnd.ms-excel");
				header("Content-disposition: attachment; filename=".$nop_.".xls");
				echo $tab;
			}              
		}
		
	}else{
		echo"<tr class=rowcontent><td colspan=10 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
	}
	break;
	
	// case'excel':
	
	// if($kdPabrik!=''){
		// $where.=" and millcode='".$kdPabrik."'";
	// }        
	
	// if(($tgl_1!='')&&($tgl_2!='')){
		// $where.=" and tanggal >= ".$tgl_1."000001 and tanggal<=".$tgl_2."235959";
	// }
	// else{
		// echo"warning: Date required";
		// exit();
	// }

	// if($tipeIntex==3){
		// $gr=" group by kodeorg,kodecustomer,left(tanggal,10),intex";
	// }
	// else if($tipeIntex==0){ //external
		// if($unit!=''){
			// $whr.=" and kodecustomer='".$unit."' ";
		// }else{
			// $whr.=" and kodeorg='' and kodecustomer!='' ";
		// }
		// $gr=" group by kodecustomer,left(tanggal,10),intex";
		// //$whr.="and intex='".$tipeIntex."'";
	// } else if ($tipeIntex==1) { //internal
		// if($unit!=''){
			// $whr.=" and kodeorg='".$unit."' ";
		// }else{
			// $whr.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$kdPabrik]."') ";
		// }

        // #divisidisi
        // $whrdiv="";
        // if ($divisi!='') {
            // $whrdiv="and divcode ='".$divisi."'";
        // }
	   // $gr=" group by kodeorg,left(tanggal,10)"; 
	   // //$whr.="and intex='".$tipeIntex."'";
	// } else if($tipeIntex==2){
		// if($unit!=''){
			// $whr.=" and kodeorg='".$unit."' ";
		// }else{
			// $whr.=" and kodeorg not in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$kdPabrik]."') ";
		// }
        // #divisidisi
        // $whrdiv="";
        // if ($divisi!='') {
            // $whrdiv="and divcode ='".$divisi."'";
        // }
	   // $gr=" group by kodeorg,left(tanggal,10)"; 
	// }

        // $tab=$_SESSION['lang']['rPenerimaanTbs'].", ".$_SESSION['lang']['periode']." :".$_POST['tgl_1']." s.d. ".$_POST['tgl_2']."<br>";
         // //notransaksi, tanggal, kodeorg, kodecustomer, bjr, jumlahtandan1, kodebarang, jammasuk, beratmasuk, jamkeluar, beratkeluar, nokendaraan, supir, nospb, petugassortasi, timbangonoff, statussortasi, nokontrak, nodo, intex, nosipb, thntm1, thntm2, thntm3, jumlahtandan2, jumlahtandan3, brondolan, username, millcode, beratbersih
        // $sData="select divcode,notransaksi,kodeorg,kodetimbangan,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
                // supir,nokendaraan,nospb,thntm1,intex,kgpotsortasi, jammasuk, jamkeluar, millcode, kriteriabuah, timbang1,timbang2
                // from ".$dbname.".pabrik_timbangan a left join ".$dbname.".log_5suptimbangan b on a.kodecustomer=b.kodetimbangan where kodebarang='40000003' ".$where." ".$whr." ".$whrdiv." order by tanggal,notransaksi asc";
		// // exit('error'.$sData);

         // $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
         // $brs=owlBaris($qData);
            // if($brs>0) {

                // if($pilTamp!=1) {
					
                        // $tab.="<table cellspacing=1 border=1 class=sortable>
                        // <thead class=rowheader>
                        // <tr>
                                // <th align=center rowspan=2>No.</th>
                                // <th align=center colspan=4>".$_SESSION['lang']['tanggal']."</th>
                                // <th align=center rowspan=2>".$_SESSION['lang']['namasupplier']."/".$_SESSION['lang']['unit']."</th>
                                // <th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>
                                // <th align=center rowspan=2>".$_SESSION['lang']['noTiket']."</th>
                                // <th align=center rowspan=2>".$_SESSION['lang']['kodenopol']."</th>
                                // <th align=center rowspan=2>Netto I (Kg)</th>
                                // <th align=center rowspan=2>Sortasi (Kg)</th>
                                // <th align=center rowspan=2>Sortasi (%)</th>
                                // <th align=center rowspan=2>Netto II (Kg)</th>";
// //                        if($tipeIntex==0)echo"<td>".$_SESSION['lang']['harga']."</td>";
                                // $tab.="<th align=center rowspan=2>".$_SESSION['lang']['sopir']."</th>
                                // <th align=center rowspan=2>SPB</th>
                                // <th align=center rowspan=2>".$_SESSION['lang']['jjg']."</th>
                                // <th align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</th>
                        // </tr>
                        // <tr>
                                // <th align=center colspan=2>".$_SESSION['lang']['jammasuk']."</th>
                                // <th align=center colspan=2>".$_SESSION['lang']['jamkeluar']."</th>
                        // </tr>
                        // </thead>
                        // <tbody>";


                                // $dtIntex="";
                                // $subtota=$subTnandn=$sbTotaJjg=$subTotNett=$subBrtNor=$subharga=$subBrtPot=0;
                                // $qData->setFetchMode(PDO::FETCH_ASSOC);
                                // while($rData=$qData->fetch()) {
                                        // $no+=1;
                                        // if($dtIntex!=$rData['intex'])  {
                                            // $dtIntex=$rData['intex'];
                                            // $sData2="select notransaksi,kodeorg,c.kodetimbangan,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
                                            // supir,nokendaraan,nospb,thntm1,intex, jammasuk, jamkeluar, millcode
                                            // from ".$dbname.".pabrik_timbangan a left join ".$dbname.".log_5suptimbangan b on a.kodecustomer=b.kodetimbangan left join ".$dbname.".log_5supplier c on a.kodecustomer=b.kodetimbangan  where kodebarang='40000003' ".$where." ".$whr."  ".$gr."  order by intex desc";//and intex='".$rData['intex']."'
                                            // // echo $sData2;
                                            // $qData2=$owlPDO->query($sData2) or die(print " Gagal: ".PDOException::getMessage());
                                            // $rowData=owlBaris($qData2);
                                            
                                            // $rd=0;
                                        // }
                                         // if($rData['intex']!=0)
                                         // {
                                              // $nm=$optNm[$rData['kodeorg']];
                                         // }
                                         // else
                                         // {
												// $optRamp=makeOption($dbname, 'log_5klsupplier', 'kode,kelompok',"kode='".$rData['kodecustomer']."'");
                                              // $nm=($optSupp[$rData['kodecustomer']]==''?$optRamp[$rData['kodecustomer']]:$optSupp[$rData['kodecustomer']]);
                                         // }
                                         // $brtNormal=$rData['netto']-$rData['kgpotsortasi'];
										 // setIt($kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']],0);
                                         // $harga=$brtNormal*$kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']];
                                         // $bgwarna="";
                                         // if($rData['nospb']!=''){
                                            // $scek="select distinct * from ".$dbname.".kebun_spbdt where nospb='".$rData['nospb']."' and substr(nospb,9,6)<>left(blok,6)";
                                            // $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                                            // $rcek=owlBaris($qcek);
                                            // if($rcek==1){
                                                // $bgwarna="bgcolor=yellow title='ada buah dari afdeling lain'";
                                            // }
                                         // }

                                         // $potonganpersen=($rData['kgpotsortasi']/$rData['netto'])*100;
										
                                        // $tab.="
                                        // <tr class=rowcontent>
                                        // <td align=center >".$no."</td>
                                        // <td style='width:65px'>".tanggalnormal($rData['timbang1'])."</td>
                                        // <td>".$rData['jammasuk']."</td>
                                        // <td style='width:65px'>".tanggalnormal($rData['timbang2'])."</td>
                                        // <td>".$rData['jamkeluar']."</td>
                                        // <td>".$nm."</td>
                                        // <td>".$rData['divcode']."</td>
                                        // <td>".$rData['notransaksi']."</td>
                                        // <td>".$rData['nokendaraan']."</td>
                                        // <td  align=right>".number_format($rData['netto'],0)."</td>
                                        // <td  align=right>".number_format($rData['kgpotsortasi'],0)."</td>
                                        // <td  align=right>".number_format($potonganpersen,2)."</td>
                                        // <td  align=right>".number_format($brtNormal,0)."</td>";
// //                                        if($tipeIntex==0)echo"<td align=right>".number_format($harga,0)."</td>";
                                        // $tab.="<td>".$rData['supir']."</td>
                                        // <td ".$bgwarna." style='display:none'>".$rData['nospb']."</td>
                                        // <td align=right style='display:none'>".number_format($rData['jjg'],0)."</td>
                                        // <td align=center style='display:none'>".$rData['thntm1']."</td>
                                        // </tr>";
										
                                        // $subtota+=$rData['netto'];
                                        // $subTnandn+=$rData['jjg'];
                                        // $sbTotaJjg+=$rData['jjg'];
                                        // $subTotNett+=$rData['netto'];
										// $subBrtNor+=$brtNormal;
										// $subharga+=$harga;
                                        // $subBrtPot+=$rData['kgpotsortasi'];
                                        // $subPotPersesn+=$potonganpersen;
                                         // $rd+=1;
                                         // if($rowData==$rd)
                                         // {
// //                                           
                                             // $sbTotaJjg=0;
                                             // $subTotNett=0;
                                        // }
                                       // $brtNormal=0;
                                // }//<td align=right>".number_format(($subBrtPot/$subTotNett)*100,2)."</td>
                                // $tab.="<tr class=rowcontent >
                                    // <td colspan=9 align=right>Total (KG)</td>
                                    // <td align=right>".number_format($subtota,0)."</td>
                                    // <td align=right>".number_format($subBrtPot,0)."</td>
                                    // <td align=right>".number_format(($subBrtPot/$subtota)*100,2)."</td>
                                    // <td align=right>".number_format($subBrtNor,0)."</td>";
// //                                    if($tipeIntex==0)echo"<td align=right>".number_format($subharga,0)."</td>";
                                    // $tab.="<td colspan=2 align=right style='display:none'>Total (JJG)</td>
                                    // <td align=right style='display:none'>".number_format($subTnandn)."</td>
                                    // <td>&nbsp;</td>
                                // </tr>";

                       
                // }
                // else
                // {
					// $dateDt="";
                    // $dateDt=array();
                    // //exit("error:".$sData);
                    // $sData="select notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
                    // supir,nokendaraan,nospb,thntm1,intex
                    // from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where." ".$whr." 
                    // order by substr(tanggal,1,10) asc";

                    // $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                    // $qData->setFetchMode(PDO::FETCH_ASSOC);
                    // while($rData=$qData->fetch())
                    // {
							// $dateDt[$rData['tanggal']]=$rData['tanggal'];
							// setIt($dtData[$rData['intex']][$rData['kodeorg'].$rData['tanggal']],0);
							// setIt($dtDataJg[$rData['intex']][$rData['kodeorg'].$rData['tanggal']],0);
							// setIt($dtData2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']],0);
							// setIt($dtDataJg2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']],0);
                            // if($rData['intex']>0)
                            // {
                                // $dtSupp[$rData['intex'].$rData['kodeorg']]=$rData['kodeorg'];
                                // $dtData[$rData['intex']][$rData['kodeorg'].$rData['tanggal']]+=$rData['netto'];
                                // $dtDataJg[$rData['intex']][$rData['kodeorg'].$rData['tanggal']]+=$rData['jjg'];
                            // }
                            // else
                            // {
                                // $dtSupp[$rData['intex'].$rData['kodecustomer']]=$rData['kodecustomer'];
                                // $dtData2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']]+=$rData['netto'];
                                // $dtDataJg2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']]+=$rData['jjg'];
                            // }
                       
                    // }
                    
                    // $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><thead>";
                    // $tab.="<tr><td rowspan=2 align=center>".$_SESSION['lang']['namasupplier']." / ".$_SESSION['lang']['unit']."</td>";
                    // @array_multisort($dtSupp);
                    // @array_multisort($dateDt);
                    // foreach($dateDt as $ar => $isi)
                    // {
                            // $qwe=date('D', strtotime($isi));
                            // $tab.="<td align=center colspan=2>";
                            // if($qwe=='Sun')$tab.="<font color=red>".tanggalnormal($isi)."</font>"; else $tab.=tanggalnormal($isi); 
                            // $tab.="</td>";
                    // }
                     // $tab.="<td align=center colspan=2>".$_SESSION['lang']['total']."</td>";
                    // $tab.="</tr><tr>";
                    // foreach($dateDt as $ar => $isi)
                    // {
                        // $tab.="<td align=center width=70px >".$_SESSION['lang']['beratBersih']." (Kg)</td>";
                        // $tab.="<td align=center width=70px >".$_SESSION['lang']['jmlhTandan']." (JJG)</td>";
                    // }
                    // $tab.="<td align=center  width=70px >".$_SESSION['lang']['beratBersih']." (Kg)</td>";
                    // $tab.="<td align=center  width=70px>".$_SESSION['lang']['jmlhTandan']." (JJG)</td>";
                    // $tab.="</tr></thead><tbody>";
                
                    // foreach($intex as $lstIntex=>$isiTex){
                        // foreach($dtSupp as $lsdtSup){
                             // if(!empty($dtSupp[$lstIntex.$lsdtSup]))
                             // {
                            // if($lstIntex==0)
                             // {
                                 // $dtData=$dtData2;
                                 // $dtDataJg=$dtDataJg2;
                             // }
                             
                             // if($lstIntex!=0)
                             // {
                                  // $nm=$optNm[$dtSupp[$lstIntex.$lsdtSup]];
                             // }
                             // else
                             // {
								 // $optramp = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$dtSupp[$lstIntex.$lsdtSup]."'");
                                  // $nm=($optSupp[$dtSupp[$lstIntex.$lsdtSup]]==''?$optramp[$dtSupp[$lstIntex.$lsdtSup]]:$optSupp[$dtSupp[$lstIntex.$lsdtSup]]);
                             // }
                             
                            // $tab.="<tr class=rowcontent><td>".$nm."</td>";
							// setIt($totsmpngkg[$lstIntex.$lsdtSup],0);
							// setIt($totsmpngjjg[$lstIntex.$lsdtSup],0);
                            // foreach($dateDt as $ar => $isi)
                            // {
								// setIt($dtData[$lstIntex][$lsdtSup.$isi],0);
								// setIt($dtDataJg[$lstIntex][$lsdtSup.$isi],0);
								// setIt($totKg[$isi],0);
								// setIt($totJjg[$isi],0);
								// setIt($totInKg[$lstIntex.$isi],0);
								// setIt($totInJjg[$lstIntex.$isi],0);
                                // $tab.="<td align=right>".number_format($dtData[$lstIntex][$lsdtSup.$isi],0)."</td>";
                                // $tab.="<td align=right>".number_format($dtDataJg[$lstIntex][$lsdtSup.$isi],0)."</td>";
                                // $totKg[$isi]+=$dtData[$lstIntex][$lsdtSup.$isi];
                                // $totJjg[$isi]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
                                // $totsmpngkg[$lstIntex.$lsdtSup]+=$dtData[$lstIntex][$lsdtSup.$isi];
                                // $totsmpngjjg[$lstIntex.$lsdtSup]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
                                // $totInKg[$lstIntex.$isi]+=$dtData[$lstIntex][$lsdtSup.$isi];
                                // $totInJjg[$lstIntex.$isi]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
                            // }
                            // $tab.="<td align=right>".number_format($totsmpngkg[$lstIntex.$lsdtSup],0)."</td>";
                            // $tab.="<td align=right>".number_format($totsmpngjjg[$lstIntex.$lsdtSup],0)."</td>";
                            // $tab.="</tr>";
							// setIt($totkgsmpng[$lstIntex],0);
							// setIt($totjjgsmpng[$lstIntex],0);
                            // $totkgsmpng[$lstIntex]+=$totsmpngkg[$lstIntex.$lsdtSup];
                            // $totjjgsmpng[$lstIntex]+=$totsmpngjjg[$lstIntex.$lsdtSup];
                            // }
                        // }
                        // if(!isset($drt) or $drt!=$lstIntex)
                        // {
                            // $drt=$lstIntex;
                            // $tab.="<tr bgcolor=darkblue><td><font color=white>".$intex[$lstIntex]."</font></td>";
                            // foreach($dateDt as $ar => $isi)
                            // {
                                // $tab.="<td align=right bgcolor=MediumBlue><font color=white>".number_format($totInKg[$lstIntex.$isi],0)."</font></td>";
                                // $tab.="<td align=right bgcolor=darkblue><font color=white>".number_format($totInJjg[$lstIntex.$isi],0)."</font></td>";
                            // }
                            // $tab.="<td align=right bgcolor=MediumBlue><font color=white>".number_format($totkgsmpng[$lstIntex],0)."</font></td>";
                            // $tab.="<td align=right><font color=white>".number_format($totjjgsmpng[$lstIntex],0)."</font></td>";
                            // $tab.="</tr>";
                        // }
						// setIt($totSmaKg,0);
						// setIt($totSmaJjg,0);
                        // $totSmaKg+=$totkgsmpng[$lstIntex];
                        // $totSmaJjg+=$totjjgsmpng[$lstIntex];
                    // }
                    // $tab.="<tr bgcolor=DarkGreen><td><font color=white>".$_SESSION['lang']['total']."</font></td>";
                    // foreach($dateDt as $ar => $isi)
                    // {
                        // $tab.="<td align=right bgcolor=Green><font color=white>".number_format($totKg[$isi],0)."</font></td>";
                        // $tab.="<td align=right><font color=white>".number_format($totJjg[$isi],0)."</font></td>";
                    // }
                    // $tab.="<td align=right bgcolor=Green><font color=white>".number_format($totSmaKg,0)."</font></td>";
                    // $tab.="<td align=right><font color=white>".number_format($totSmaJjg,0)."</font></td>";
                    // $tab.="</tr></tbody></table>";
                // }
            // }
            // else
            // {
                // $tab.="<tr class=rowcontent><td colspan=10 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
            // }
			
			// $tab.="</tbody></table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			// $tglSkrg=date("Ymd");
                        // $qwe=date("Hms");
			// // echo $tab;
            // $nop_="LaporanPenerimaanTbs".$tglSkrg."__".$qwe;
			// if(strlen($tab)>0)
			// {
                // header("Cache-Control: must-revalidate");
                // header("Pragma: must-revalidate");
                // header("Content-type: application/vnd.ms-excel");
                // header("Content-disposition: attachment; filename=".$nop_.".xls");
                // echo $tab;
			// }              
	
	// break;
	
	/*
	case'pdf':
	$periode=checkPostGet('periode','-');
	$tipeIntex=$_GET['tipeIntex'];
	$unit=$_GET['unit'];
	$tglPeriode=explode("-",$periode);
	$tanggal=$tglPeriode[1]."-".$tglPeriode[0];
	$tgl_1=tanggalsystem($_GET['tgl_1']);
	$tgl_2=tanggalsystem($_GET['tgl_2']);
	$kdPabrik=$_GET['kdPabrik'];
	$pilTamp=$_GET['pilTamp'];
	
	 class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
				global $tipeIntex;
				global $periode;
				global $unit;
				global $kdPabrik;
				global $tgl_2;
				global $tgl_1;
				global $tglPeriode;
				global $tanggal;
				global $rNamaSupp;
				global $owlPDO;
				
				
				$tglPeriode=explode("-",$periode);
				$tanggal=$tglPeriode[1]."-".$tglPeriode[0];

				$arrHead = setheadreport(substr($kdPabrik,0,4));
				
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(110);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(110); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(110); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
				
                $this->Ln();
				$this->Ln();
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height, strtoupper($_SESSION['lang']['rPenerimaanTbs']),0,1,'C');	
			 	$this->SetFont('Arial','',8);
				$sNm="select namasupplier,kodetimbangan from ".$dbname.".log_5supplier order by namasupplier asc";

        $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
        $qNm->setFetchMode(PDO::FETCH_ASSOC);
        while($rNm=$qNm->fetch())
        {
					$rNamaSupp[$rNm['kodetimbangan']]=$rNm;
				}
				$sBrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";

        $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
        $qBrg->setFetchMode(PDO::FETCH_ASSOC);
        while($rBrg=$qBrg->fetch())
        {
					$rNmBrg[$rBrg['kodebarang']]=$rBrg;
				}
				if(($kdPabrik!='')&&($unit!=''))
				{
				$this->Cell($width,$height, $_SESSION['lang']['terimaTbs']." : ".$kdPabrik." atas ".$rNmBrg[40000003]['namabarang']." ".$_SESSION['lang']['dari']." ".$rNamaSupp[$unit]['namasupplier']." ".$_SESSION['lang']['periode']." :".$tgl_1."-".$tgl_2,0,1,'C');	
				}
				else
				{
					$this->Cell($width,$height, $_SESSION['lang']['terimaTbs']." : ".$kdPabrik." atas ".$rNmBrg[40000003]['namabarang']." ".$_SESSION['lang']['dari']." : ".$_SESSION['lang']['all'].", ".$_SESSION['lang']['periode']." :".tanggalnormal($tgl_1)." - ".tanggalnormal($tgl_2),0,1,'C');						
				}
				$this->Ln();$this->Ln();
                $this->SetFont('Arial','B',5);	
                $this->SetFillColor(220,220,220);
				
				$this->Cell(3/100*$width,$height,'No',1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);		
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['namasupplier'],1,0,'C',1);		
				$this->Cell(7/100*$width,$height,$_SESSION['lang']['noTiket'],1,0,'C',1);	
				$this->Cell(9/100*$width,$height,$_SESSION['lang']['kodenopol'],1,0,'C',1);	
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['beratBersih'],1,0,'C',1);	
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['potongankg'],1,0,'C',1);	
                                $this->Cell(8/100*$width,$height,$_SESSION['lang']['beratnormal'],1,0,'C',1);	
				$this->Cell(7/100*$width,$height,$_SESSION['lang']['sopir'],1,0,'C',1);	
//                                if($tipeIntex==0)$this->Cell(10/100*$width,$height,$_SESSION['lang']['harga'],1,0,'C',1); else
				$this->Cell(15/100*$width,$height,$_SESSION['lang']['nospb'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['jmlhTandan'],1,0,'C',1);
				$this->Cell(8/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,1,'C',1);	            
            }
                
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 9;
		$pdf->AddPage();
		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',5);
                if($pilTamp==1)
                {
                    exit("Error: Not privided for PDF");
                }
	if($tipeIntex!='')
	{
		if($tipeIntex!=3) $where.=" and intex='".$tipeIntex."'";
	}
	else
	{
		echo"warning: Choose FFB source";
		exit();
	}
	if($unit!="")
	{
		if($tipeIntex==0)
		{
			$where.=" and kodecustomer='".$unit."'";
		}
		elseif($tipeIntex!=0)
		{
			$where.=" and kodeorg='".$unit."' ";
		}
	}
	if(($tgl_1!='')&&($tgl_2!=''))
	{
		$where.=" and tanggal >= ".$tgl_1."000001 and tanggal<=".$tgl_2."235959";
	}
	else
	{
		echo"warning: Date required";
		exit();
	}
	
	if($kdPabrik!='')
	{
		$where.=" and millcode='".$kdPabrik."'";
		
	}		
        
              // kamus harga
            $sOrg="select pabrik,tanggal,supplier,hargab,hargas,hargak from ".$dbname.".pmn_hargatbsharian";

            $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
            $qOrg->setFetchMode(PDO::FETCH_ASSOC);
            while($rOrg=$qOrg->fetch())
            {
                $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['']=0;
                $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['L']=$rOrg['hargab'];
                $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['M']=$rOrg['hargas'];
                $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['S']=$rOrg['hargak'];
            }        
		$sList="select notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,supir,nokendaraan,nospb,thntm1,kgpotsortasi,millcode,kriteriabuah
                        from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where;
		$subtota=$subTnandn=$sbTotaJjg=$subTotNett=$subbrtnor=$subharga=$subbrtpot=$subjjg=0;

                $qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
                $qList->setFetchMode(PDO::FETCH_ASSOC);
                while($rData=$qList->fetch())
                {
			if($tipeIntex!=0)
			{
				$sNm="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rData['kodeorg']."'";

                                $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
                                $qNm->setFetchMode(PDO::FETCH_ASSOC);
                                $rNm=$qNm->fetch();
                                
				$nm=$rNm['namaorganisasi'];
				$kd=$rData['kodeorg'];
				if(empty($nm)) {
					$sNm="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$rData['kodecustomer']."'";

                                        $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
                                        $qNm->setFetchMode(PDO::FETCH_ASSOC);
                                        $rNm=$qNm->fetch();
					$nm=$rNamaSupp[$rData['kodecustomer']]['namasupplier'];
				}
			}
			else
			{
				$nm=$rNamaSupp[$rData['kodecustomer']]['namasupplier'];	
					
			}
			$no+=1;
                        $pdf->SetFont('Arial','',6);
                        $brtNormal=$rData['netto']-$rData['kgpotsortasi'];
			$pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
			$pdf->Cell(8/100*$width,$height,tanggalnormal($rData['tanggal']),1,0,'C',1);		
			$pdf->Cell(15/100*$width,$height,$nm,1,0,'L',1);	
                       
			$pdf->Cell(7/100*$width,$height,$rData['notransaksi'],1,0,'L',1);	
			$pdf->Cell(9/100*$width,$height,$rData['nokendaraan'],1,0,'L',1);
                       
			$pdf->Cell(8/100*$width,$height,number_format($rData['netto']),1,0,'R',1);	
                        $pdf->Cell(8/100*$width,$height,number_format($rData['kgpotsortasi']),1,0,'R',1);
                        $pdf->Cell(8/100*$width,$height,number_format($brtNormal),1,0,'R',1);
                        $pdf->SetFont('Arial','',5);
			$pdf->Cell(7/100*$width,$height,$rData['supir'],1,0,'L',1);	
                        $pdf->SetFont('Arial','',6);
						setIt($kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']],0);
                        $harga=$brtNormal*$kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']];
//                        if($tipeIntex==0)$pdf->Cell(10/100*$width,$height,number_format($harga),1,0,'R',1); else
			$pdf->Cell(15/100*$width,$height,$rData['nospb'],1,0,'L',1);
                        
			$pdf->Cell(8/100*$width,$height,number_format($rData['jjg'],2),1,0,'R',1);
			$pdf->Cell(8/100*$width,$height,$rData['thntm1'],1,1,'C',1);

			$subtota+=$rData['netto'];
			$subjjg+=$rData['jjg'];
                        $subbrtpot+=$rData['kgpotsortasi'];
                        $subbrtnor+=$brtNormal;
                        $subharga+=$harga;
		}
		$pdf->Cell(42/100*$width,$height,"Total",1,0,'R',1);
                $pdf->SetFont('Arial','',6);
		$pdf->Cell(8/100*$width,$height,number_format($subtota),1,0,'R',1);
                $pdf->Cell(8/100*$width,$height,number_format($subbrtpot),1,0,'R',1);
                $pdf->Cell(8/100*$width,$height,number_format($subbrtnor),1,0,'R',1);
		$pdf->Cell(7/100*$width,$height,"",1,0,'C',1);
//                if($tipeIntex==0)$pdf->Cell(10/100*$width,$height,number_format($subharga),1,0,'R',1); else
		$pdf->Cell(15/100*$width,$height,"",1,0,'C',1);
		$pdf->Cell(8/100*$width,$height,number_format($subjjg),1,0,'R',1);
		$pdf->Cell(8/100*$width,$height,'',1,1,'R',1);
			
    $pdf->Output();
	break;
     */   
        
        
        
        
        
	// case'excel':
	// $periode=checkPostGet('periode','00-00-0000');
	// $tipeIntex=$_GET['tipeIntex'];
	// $unit=$_GET['unit'];
	// $tglPeriode=explode("-",$periode);
	// $tanggal=$tglPeriode[1]."-".$tglPeriode[0];
	// $tgl_1=tanggalsystem($_GET['tgl_1']);
	// $tgl_2=tanggalsystem($_GET['tgl_2']);
	// $kdPabrik=$_GET['kdPabrik'];
        // $pilTamp=$_GET['pilTamp'];
        // $dateDt=dates_inbetween($tgl_1,$tgl_2);
		
		
	// /*	
		
	// if($unit!="")
	// {
               // if($tipeIntex==0)
               // {
                    // $where= "and kodecustomer='".$unit."'";
               // }
               // else
               // {
                    // $where= "and substr(nospb,9,6) like '%".$unit."%'";
               // }
	// }
	// if($kdPabrik!='')
	// {
		// $where.=" and millcode='".$kdPabrik."'";
	// }        
	// if(($tgl_1!='')&&($tgl_2!=''))
	// {
		
                 // $where.=" and tanggal >= ".$tgl_1."000001 and tanggal<=".$tgl_2."235959";
	// }
	// else
	// {
		// echo"warning: Date required";
		// exit();
	// }
         // if($tipeIntex==3)
         // {
              // $gr=" group by kodeorg,kodecustomer,left(tanggal,10),intex";
         // }
         // elseif($tipeIntex==0)
          // {
               // $gr=" group by kodecustomer,left(tanggal,10),intex";
               // $whr.="and intex='".$tipeIntex."'";
          // }
              // else
              // {
                   // $gr=" group by kodeorg,left(tanggal,10)"; 
                   // $whr.="and intex='".$tipeIntex."'";
              // }
            
			
			// */
              // // kamus harga
            // $sOrg="select pabrik,tanggal,supplier,hargab,hargas,hargak from ".$dbname.".pmn_hargatbsharian";

            // $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
            // $qOrg->setFetchMode(PDO::FETCH_ASSOC);
            // while($rOrg=$qOrg->fetch())
            // {
                // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['']=0;
                // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['L']=$rOrg['hargab'];
                // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['M']=$rOrg['hargas'];
                // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['S']=$rOrg['hargak'];
            // }
	
	// ##################################################
	
	// if($kdPabrik!=''){
		// $where.=" and millcode='".$kdPabrik."'";
	// }        
	
	// if(($tgl_1!='')&&($tgl_2!='')){
		// $where.=" and tanggal >= ".$tgl_1."000001 and tanggal<=".$tgl_2."235959";
	// }
	// else{
		// echo"warning: Date required";
		// exit();
	// }

	// if($tipeIntex==3){
		// $gr=" group by kodeorg,kodecustomer,left(tanggal,10),intex";
	// }
	// else if($tipeIntex==0){ //external
		// if($unit!=''){
			// $whr.=" and kodecustomer='".$unit."' ";
		// }else{
			// $whr.=" and kodeorg='' and kodecustomer!='' ";
		// }
		// $gr=" group by kodecustomer,left(tanggal,10),intex";
		// //$whr.="and intex='".$tipeIntex."'";
	// } else if ($tipeIntex==1) { //internal
		// if($unit!=''){
			// $whr.=" and kodeorg='".$unit."' ";
		// }else{
			// $whr.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$kdPabrik]."') ";
		// }
	   // $gr=" group by kodeorg,left(tanggal,10)"; 
	   // //$whr.="and intex='".$tipeIntex."'";
	// } else if($tipeIntex==2){
		// if($unit!=''){
			// $whr.=" and kodeorg='".$unit."' ";
		// }else{
			// $whr.=" and kodeorg not in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt[$kdPabrik]."') ";
		// }
	   // $gr=" group by kodeorg,left(tanggal,10)"; 
	// }
	
	// ##################################################################
	
	
	
	
	
	
	
	
	
	
	
	// $sBrg="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";

        // $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
        // $qBrg->setFetchMode(PDO::FETCH_ASSOC);
        // while($rBrg=$qBrg->fetch())
        // {
		// $rNmBrg[$rBrg['kodebarang']]=$rBrg;
	// }
	
	// $tab.="<table cellspacing=\"1\" border=0><tr><td colspan=10 align=center>".$_SESSION['lang']['rPenerimaanTbs']."</td></tr>
	// ";
	// if(($kdPabrik!='')&&($unit!=''))
	// {
		// $tab.="<tr><td colspan=2 align=right>".$_SESSION['lang']['terimaTbs']."</td><td colspan=8>".$kdPabrik." atas ".$rNmBrg[40000003]['namabarang']." ".$_SESSION['lang']['dari']." ".$rNamaSupp[$unit]['namasupplier']." ".$_SESSION['lang']['periode']." :".$tgl_1." s.d. ".$tgl_2."</td></tr>";
	// }
	// else
	// {
		// $tab.="<tr><td colspan=2 align=right>".$_SESSION['lang']['terimaTbs']."</td><td colspan=8>".$kdPabrik." atas ".$rNmBrg[40000003]['namabarang']." ".$_SESSION['lang']['dari']." ".$_SESSION['lang']['all']." ".$_SESSION['lang']['periode']." :".tanggalnormal($tgl_1)." s.d. ".tanggalnormal($tgl_2)."</td></tr>";
	// }
	// $tab.="</table>";
	
	
	
	
	
         // $sData="select notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
                // supir,nokendaraan,nospb,thntm1,intex,kgpotsortasi, jammasuk, jamkeluar, millcode, kriteriabuah
                // from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where." ".$whr."  order by tanggal asc";
				
         // $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
         // $brs=owlBaris($qData);
         
         // if($brs>0)
         // {

                    // if($pilTamp!=1)
                    // {
                                // $tab.="<table cellspacing=1 border=1 class=sortable>
                                // <thead class=rowheader>
                                // <tr>
                                        // <td bgcolor=#DEDEDE>No.</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['tanggal']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['jammasuk']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['jamkeluar']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['namasupplier']."/".$_SESSION['lang']['unit']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['noTiket']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['kodenopol']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['beratBersih']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['potongankg']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['beratnormal']."</td>";
// //                                if($tipeIntex==0)$tab.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['harga']."</td>";
                                        // $tab.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['sopir']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['nospb']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['jmlhTandan']."</td>
                                        // <td bgcolor=#DEDEDE>".$_SESSION['lang']['tahuntanam']."</td>
                                // </tr>
                                // </thead>
                                // <tbody>";
                                // $dtIntex="";
                                // $subtota=$subTnandn=$sbTotaJjg=$subTotNett=$subBrtNor=$subharga=$subBrtPot=0;
                                // $qData->setFetchMode(PDO::FETCH_ASSOC);
                                // while($rData=$qData->fetch())
                                // {
                                        // $no+=1;
                                        // if($dtIntex!=$rData['intex'])
                                        // {
                                            // $dtIntex=$rData['intex'];
                                            // $sData2="select notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
                                            // supir,nokendaraan,nospb,thntm1,intex, millcode
                                            // from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where." ".$whr." and intex='".$rData['intex']."'  order by intex desc";
                                            // $qData2=$owlPDO->query($sData2) or die(print " Gagal: ".PDOException::getMessage());
                                            // $rowData=owlBaris($qData2);
                                            // $rd=0;
                                        // }
                                         // if($rData['intex']!=0)
                                         // {
                                              // $nm=$optNm[$rData['kodeorg']];
                                         // }
                                         // else
                                         // {
                                              // $nm=$optSupp[$rData['kodecustomer']];
                                         // }
                                         // $brtNormal=$rData['netto']-$rData['kgpotsortasi'];
                                         // $bgwarna="";
                                         // if($rData['nospb']!=''){
                                            // $scek="select distinct * from ".$dbname.".kebun_spbdt where nospb='".$rData['nospb']."' and substr(nospb,9,6)<>left(blok,6)";
                                            // $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                                            // $rcek=owlBaris($qcek);
                                            // if($rcek==1){
                                                // $bgwarna="bgcolor=yellow";
                                            // }
                                         // }
                                        // $tab.="
                                        // <tr class=rowcontent>
                                        // <td>".$no."</td>
                                        // <td>".$rData['tanggal']."</td>
                                        // <td>".$rData['jammasuk']."</td>
                                        // <td>".$rData['jamkeluar']."</td>
                                        // <td>".$nm."</td>
                                        // <td>".$rData['notransaksi']."</td>
                                        // <td>".$rData['nokendaraan']."</td>
                                        // <td  align=right>".number_format($rData['netto'],0)."</td>
                                        // <td  align=right>".number_format($rData['kgpotsortasi'],0)."</td>
                                        // <td  align=right>".number_format($brtNormal,0)."</td>";
										// setIt($kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']],0);
                                        // $harga=$brtNormal*$kamusharga[$rData['millcode']][$rData['tanggal']][$rData['kodecustomer']][$rData['kriteriabuah']];
// //                                            if($tipeIntex==0)$tab.="<td  align=right>".number_format($harga,0)."</td>";
                                        // $tab.="<td>".$rData['supir']."</td>
                                        // <td ".$bgwarna.">".$rData['nospb']."</td>
                                        // <td align=right>".number_format($rData['jjg'],0)."</td>
                                        // <td>".$rData['thntm1']."</td>
                                        // </tr>";
                                        // $subtota+=$rData['netto'];
                                        // $subTnandn+=$rData['jjg'];
                                        // $sbTotaJjg+=$rData['jjg'];
                                        // $subTotNett+=$rData['netto'];
                                        // $subBrtNor+=$brtNormal;
                                        // $subharga+=$harga;
                                        // $subBrtPot+=$rData['kgpotsortasi'];
                                         // $rd+=1;
                                         // if($rowData==$rd)
                                         // {
                                             // $sbTotaJjg=0;
                                             // $subTotNett=0;
                                        // }
                                       // $brtNormal=0;
                                // }
                                // $tab.="<tr class=rowcontent >
                                    // <td colspan=7 align=right>Total (KG)</td>
                                    // <td align=right>".number_format($subtota,0)."</td>
                                    // <td align=right>".number_format($subBrtPot,0)."</td>
                                    // <td align=right>".number_format($subBrtNor,0)."</td>";
// //                                    if($tipeIntex==0)$tab.="<td align=right>".number_format($subharga,0)."</td>";
                                    // $tab.="<td colspan=2 align=right>Total (JJG)</td>
                                    // <td align=right>".number_format($subTnandn,2)."</td>
                                    // <td>&nbsp;</td>
                                // </tr>";                                

                    // }
                    // else
                    // {
                    // $dateDt="";
                    // $dateDt=array();
                    // //exit("error:".$sData);
                    // $sData="select notransaksi,kodeorg,jumlahtandan1 as jjg,beratbersih as netto,kodecustomer,substr(tanggal,1,10) as tanggal,
                    // supir,nokendaraan,nospb,thntm1,intex
                    // from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$where." ".$whr."
                    // HAVING jjg >0 AND netto >0
                        // order by substr(tanggal,1,10) asc";
                    // $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                    // $qData->setFetchMode(PDO::FETCH_ASSOC);
                    // while($rData=$qData->fetch())
                    // {
                           // $dateDt[$rData['tanggal']]=$rData['tanggal'];
                            // if($rData['intex']>0)
                            // {
								// setIt($dtData[$rData['intex']][$rData['kodeorg'].$rData['tanggal']],0);
								// setIt($dtDataJg[$rData['intex']][$rData['kodeorg'].$rData['tanggal']],0);
                                // $dtSupp[$rData['intex'].$rData['kodeorg']]=$rData['kodeorg'];
                                // $dtData[$rData['intex']][$rData['kodeorg'].$rData['tanggal']]+=$rData['netto'];
                                // $dtDataJg[$rData['intex']][$rData['kodeorg'].$rData['tanggal']]+=$rData['jjg'];
                            // }
                            // else
                            // {
                                // $dtSupp[$rData['intex'].$rData['kodecustomer']]=$rData['kodecustomer'];
								// setIt($dtData2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']],0);
								// setIt($dtDataJg2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']],0);
                                // $dtData2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']]+=$rData['netto'];
                                // $dtDataJg2[$rData['intex']][$rData['kodecustomer'].$rData['tanggal']]+=$rData['jjg'];
                            // }
                       
                    // }
                    
                    // array_multisort($dtSupp);
                    // array_multisort($dateDt);
                            // $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
                            // $tab.="<tr><td bgcolor=#DEDEDE rowspan=2>".$_SESSION['lang']['namasupplier']."/".$_SESSION['lang']['unit']."</td>";
                            // foreach($dateDt as $ar => $isi)
                            // {
                                    // $qwe=date('D', strtotime($isi));
                                    // $tab.="<td align=center bgcolor=#DEDEDE colspan=2>";
                                    // if($qwe=='Sun')$tab.="<font color=red>".$isi."</font>"; else $tab.=$isi; 
                                    // $tab.="</td>";
                            // }
                            // $tab.="<td align=center bgcolor=#DEDEDE colspan=2>".$_SESSION['lang']['total']."</td>";
                            // $tab.="</tr><tr>";
                            // foreach($dateDt as $ar => $isi)
                            // {
                                // $tab.="<td bgcolor=#DEDEDE >".$_SESSION['lang']['beratBersih']." (Kg)</td>";
                                // $tab.="<td bgcolor=#DEDEDE >".$_SESSION['lang']['jmlhTandan']." (JJG)</td>";
                            // }
                            // $tab.="<td bgcolor=#DEDEDE >".$_SESSION['lang']['beratBersih']." (Kg)</td>";
                            // $tab.="<td bgcolor=#DEDEDE >".$_SESSION['lang']['jmlhTandan']." (JJG)</td>";
                            // $tab.="</tr></thead><tbody>";
                            
                    // foreach($intex as $lstIntex=>$isiTex){
                        // foreach($dtSupp as $lsdtSup){
                             // if(!empty($dtSupp[$lstIntex.$lsdtSup]))
                             // {
                            // if($lstIntex==0)
                             // {
                                 // $dtData=$dtData2;
                                 // $dtDataJg=$dtDataJg2;
                             // }
                             
                             // if($lstIntex!=0)
                             // {
                                  // $nm=$optNm[$dtSupp[$lstIntex.$lsdtSup]];
                             // }
                             // else
                             // {
                                  // $nm=$optSupp[$dtSupp[$lstIntex.$lsdtSup]];
                             // }
                             
                            // $tab.="<tr class=rowcontent><td>".$nm."</td>";
							// setIt($totkgsmpng[$lstIntex],0);
							// setIt($totjjgsmpng[$lstIntex],0);
                            // foreach($dateDt as $ar => $isi)
                            // {
                                // setIt($dtData[$lstIntex][$lsdtSup.$isi],0);
								// setIt($dtDataJg[$lstIntex][$lsdtSup.$isi],0);
								// setIt($totsmpngkg[$lstIntex.$lsdtSup],0);
								// setIt($totsmpngjjg[$lstIntex.$lsdtSup],0);
								// setIt($totKg[$isi],0);
								// setIt($totJjg[$isi],0);
								// setIt($totInKg[$lstIntex.$isi],0);
								// setIt($totInJjg[$lstIntex.$isi],0);
								
								// $tab.="<td align=right>".number_format($dtData[$lstIntex][$lsdtSup.$isi],0)."</td>";
                                // $tab.="<td align=right>".number_format($dtDataJg[$lstIntex][$lsdtSup.$isi],0)."</td>";
                                // $totKg[$isi]+=$dtData[$lstIntex][$lsdtSup.$isi];
                                // $totJjg[$isi]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
                                // $totsmpngkg[$lstIntex.$lsdtSup]+=$dtData[$lstIntex][$lsdtSup.$isi];
                                // $totsmpngjjg[$lstIntex.$lsdtSup]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
                                // $totInKg[$lstIntex.$isi]+=$dtData[$lstIntex][$lsdtSup.$isi];
                                // $totInJjg[$lstIntex.$isi]+=$dtDataJg[$lstIntex][$lsdtSup.$isi];
                            // }
                            // $tab.="<td align=right>".number_format($totsmpngkg[$lstIntex.$lsdtSup],0)."</td>";
                            // $tab.="<td align=right>".number_format($totsmpngjjg[$lstIntex.$lsdtSup],0)."</td>";
                            // $tab.="</tr>";
                            // $totkgsmpng[$lstIntex]+=$totsmpngkg[$lstIntex.$lsdtSup];
                            // $totjjgsmpng[$lstIntex]+=$totsmpngjjg[$lstIntex.$lsdtSup];
                            // }
                        // }
                        // if(!isset($drt) or $drt!=$lstIntex)
                        // {
                            // $drt=$lstIntex;
                            // $tab.="<tr bgcolor=darkblue><td><font color=white>".$intex[$lstIntex]."</font></td>";
                            // foreach($dateDt as $ar => $isi)
                            // {
                                // $tab.="<td align=right bgcolor=MediumBlue><font color=white>".number_format($totInKg[$lstIntex.$isi],0)."</font></td>";
                                // $tab.="<td align=right bgcolor=darkblue><font color=white>".number_format($totInJjg[$lstIntex.$isi],0)."</font></td>";
                            // }
                            // $tab.="<td align=right bgcolor=MediumBlue><font color=white>".number_format($totkgsmpng[$lstIntex],0)."</font></td>";
                            // $tab.="<td align=right><font color=white>".number_format($totjjgsmpng[$lstIntex],0)."</font></td>";
                            // $tab.="</tr>";
                        // }
						// setIt($totSmaKg,0);
						// setIt($totSmaJjg,0);
                        // $totSmaKg+=$totkgsmpng[$lstIntex];
                        // $totSmaJjg+=$totjjgsmpng[$lstIntex];
                    // }
                    // $tab.="<tr bgcolor=darkgreen><td><font color=white>".$_SESSION['lang']['total']."</font></td>";
                    // foreach($dateDt as $ar => $isi)
                    // {
                        // $tab.="<td align=right bgcolor=Green><font color=white>".number_format($totKg[$isi],0)."</font></td>";
                        // $tab.="<td align=right><font color=white>".number_format($totJjg[$isi],0)."</font></td>";
                    // }
                    // $tab.="<td align=right bgcolor=Green><font color=white>".number_format($totSmaKg,0)."</font></td>";
                    // $tab.="<td align=right><font color=white>".number_format($totSmaJjg,0)."</font></td>";
                    // $tab.="</tr></tbody></table>";
                    
                           
                       // }
         // }
        // else
        // {
                // $tab.="<tr class=rowcontent><td colspan=10 align=center>Data empty</td></tr>";
        // }
	
			// //exit("Error:$tab");
			// //echo "warning:".$strx;
			// //=================================================

			
			// $tab.="</tbody></table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			// $tglSkrg=date("Ymd");
                        // $qwe=date("Hms");
			// $nop_="LaporanPenerimaanTbs".$tglSkrg."__".$qwe;
// if(strlen($tab)>0)
// {
    // $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
    // gzwrite($gztralala, $tab);
    // gzclose($gztralala);
    // echo "<script language=javascript1.2>
        // window.location='tempExcel/".$nop_.".xls.gz';
        // </script>";
// }                            

	break;
	default:
	break;
}
?>