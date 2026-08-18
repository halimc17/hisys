<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$tgl =tanggalsystem(checkPostGet('tgl', ''));
$tgl1 =tanggalsystem(checkPostGet('tgl1', ''));
$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$stts = checkPostGet('stts', '');
$stream="";

$paynum=array();

if ($unit!='') {
	$unt= "and unit='".$unit."'";
}
else
{
	$unt='';
}
if ($pt!='') {
	$ptt= "and kodeorganisasi='".$pt."'";
}
else
{
	$ptt='';
}

if ($unit!='') {
	$kdorg= "and a.nopo like'%".$unit."%'";
}
else
{
	$kdorg='';
}

                        

switch($proses)
{

	
	case'preview';

	if ($stts==3) {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
			on a.nopo=b.nopo left join log_prapoht c
			on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
			and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg." and statuspo=3
			and a.nopo in (select a.nopo from ".$dbname.".log_poht a left join ".$dbname.".keu_tagihanht b
			on a.nopo=b.nopo where b.tanggal between '".$tgl."' and '".$tgl1."' and posting !=1)";
		}

	if ($stts==2) {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
			on a.nopo=b.nopo left join log_prapoht c
			on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
			and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg." and a.statuspo=3
			and a.nopo in (select a.nopo from ".$dbname.".log_poht a left join ".$dbname.".keu_tagihanht b
			on a.nopo=b.nopo where a.tanggal between '".$tgl."' and '".$tgl1."' and noinvoice is null)";
		}

	if ($stts==1) {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
					on a.nopo=b.nopo left join log_prapoht c
					on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
					and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg." and a.statuspo=2";
		}



	if ($stts=='') {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
					on a.nopo=b.nopo left join log_prapoht c
					on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
					and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg."";

		}

	//echo $str;
	
	//exit('error'.$str);
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $nomorpo[$bar['nopo']]=$bar['nopo'];
        $nomorpp[$bar['nopo']]=$bar['nopp'];
        $tanggalpo[$bar['nopo']]=$bar['tanggal'];
        $tanggalpp[$bar['nopo']]=$bar['tanggalpp'];
    }

	$str="select distinct a.nopo,a.tanggal,c.notransaksi as gnr_number,c.tanggal as gnr_date from ".$dbname.".log_poht a left join ".$dbname.".log_transaksi_vw c 
    on a.nopo=c.nopo where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")";
    
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
    	if($stts=="3"){
    		$scek="select * from ".$dbname.".keu_tagihanht where notransaksi_gr='".$bar['gnr_number']."'";
    		$rcek=fetchData($scek);
    		if(count($rcek)==0){
    			continue;
    		}
    	}
        $gnrno[$bar['gnr_number']]=$bar['gnr_number'];
        $gnrnumber[$bar['nopo']][$bar['gnr_number']]=$bar['gnr_number'];
        $gnrdate[$bar['nopo']]=$bar['gnr_date']; 
    }

     /*echo "<pre>";
    print_r($gnrnumber);
    echo "</pre>";
*/


	$str="select noinvoice,notransaksi_gr,nopo,tanggal from ".$dbname.".keu_tagihanht where tipeinvoice='p' ".$unt.""; 	
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        //$nomorpo[$bar['nopo']]=$bar['nopo'];
        //$gnrnumber[$bar['notransaksi_gr']]=$bar['notransaksi_gr'];
        //$notrs_gr[$bar['nopo']]=$bar['notransaksi_gr'];
        $jmlGr[$bar['nopo']][]=$bar['notransaksi_gr'];
        $noinvDt[$bar['nopo'].$bar['notransaksi_gr']]=$bar['noinvoice'];
        $noinvTglDt[$bar['nopo'].$bar['notransaksi_gr']]=$bar['tanggal'];
    }

    $str="select notransaksi,keterangan1,tanggal,nodok from ".$dbname.".keu_kasbankdtht_vw where posting=1 
    		and keterangan1!='' "; 	

    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        
        $paynumber[$bar['notransasksi']]=$bar['notransaksi'];
        $noinvoice[$bar['keterangan1']]=$bar['keterangan1'];
        $nopo[$bar['nodok']]=$bar['nodok'];

        $paynum[$bar['keterangan1']]=$bar['notransaksi'];
        $paydate[$bar['keterangan1']]=$bar['tanggal'];


        }



	$stream.="<table class=sortable cellspacing=1 cellpadding=1 border=0 width=100%>";
	$stream.="<thead>";

			$stream.="<tr class=rowheader >";
			   $stream.="<td rowspan=4>".$_SESSION['lang']['po']." Number</td>";
			   $stream.="<td rowspan=4>".$_SESSION['lang']['po']." Date</td>";
			   $stream.="<td rowspan=4>PR Number</td>";
			   $stream.="<td rowspan=4>PR Date</td>";
			   $stream.="<td rowspan=4>GRN Number</td>";
			   $stream.="<td rowspan=4>GRN Date</td>";
			   $stream.="<td rowspan=4>Invoice number</td>";
			   $stream.="<td rowspan=4>Invoice receipt</td>";
			   $stream.="<td rowspan=4>Payment number</td>";
			   $stream.="<td rowspan=4>Payment date</td>";
			   $stream.="<td rowspan=4>GRN Aging</td>";
			   $stream.="<td rowspan=4>Invoice Aging</td>";
			   $stream.="<td rowspan=4>Payment Aging</td>";
	

	$stream.="</tr></thead>";
	 $tanggal=date('Y-m-d');
	 

	foreach ($nomorpo as $nopo) {
		
		    $countgrn = count($gnrnumber[$nopo]);
			$stream.= "<tr class=rowcontent>";
			$stream.= "<td rowspan='".$countgrn."'>".$nopo."</td>";
			$stream.= "<td rowspan='".$countgrn."'>".$tanggalpo[$nopo]."</td>";
			$stream.= "<td rowspan='".$countgrn."'>".$nomorpp[$nopo]."</td>";
			$stream.= "<td rowspan='".$countgrn."'>".$tanggalpp[$nopo]."</td>";
		
			if($countgrn > 0){

				$no=0;
				foreach ($gnrnumber[$nopo] as $grnnmbr) {

					if ($tanggalpo[$nopo]!='' and $gnrdate[$nopo]!='') {
						
					 $diffgrn =(strtotime($gnrdate[$nopo])-strtotime($tanggalpo[$nopo]));
                     $difgrn =floor(($diffgrn)/(60*60*24));
					}
					
					elseif ($tanggalpo[$nopo]!='' and $gnrdate[$nopo]=='') {
						
					 $diffgrn =(strtotime($tanggal)-strtotime($tanggalpo[$nopo]));
                     $difgrn =floor(($diffgrn)/(60*60*24));
					}
					else
					{
						$difgrn ='';
					}

					if ($gnrdate[$nopo]!='' and $noinvTglDt[$nopo.$grnnmbr]!='') {
						
					 $diffinv =(strtotime($noinvTglDt[$nopo.$grnnmbr])-strtotime($gnrdate[$nopo]));
                     $difinv=floor(($diffinv)/(60*60*24));
					}
					elseif ($gnrdate[$nopo]!='' and $noinvTglDt[$nopo.$grnnmbr]=='') {
						
					 $diffinv =(strtotime($tanggal)-strtotime($gnrdate[$nopo]));
                     $difinv=floor(($diffinv)/(60*60*24));
					}
					else
					{
						$difinv ='';
					}

					if ($noinvTglDt[$nopo.$grnnmbr]!='' and $paydate[$noinvDt[$nopo.$grnnmbr]]!='') {
						
					 $diffpay =(strtotime($paydate[$noinvDt[$nopo.$grnnmbr]])-strtotime($noinvTglDt[$nopo.$grnnmbr]));
                     $difpay =floor(($diffpay)/(60*60*24));
					}
					elseif ($noinvTglDt[$nopo.$grnnmbr]!='' and $paydate[$noinvDt[$nopo.$grnnmbr]]=='') {
						
					 $diffpay =(strtotime($tanggal)-strtotime($noinvTglDt[$nopo.$grnnmbr]));
                     $difpay =floor(($diffpay)/(60*60*24));
					}
					else
						{
						$difpay ='';
					}
                        					
					$no++;
					if($no < 1){
						$stream.="<tr class=rowcontent>";
					}

					$stream.= "<td class=rowcontent>".$gnrnumber[$nopo][$grnnmbr]."</td>";
				
					$stream.= "<td class=rowcontent>".$gnrdate[$nopo]."</td>";
					$stream.= "<td class=rowcontent>".$noinvDt[$nopo.$grnnmbr]."</td>";
					$stream.= "<td class=rowcontent>".$noinvTglDt[$nopo.$grnnmbr]."</td>";				
					$stream.= "<td class=rowcontent>".$paynum[$noinvDt[$nopo.$grnnmbr]]."</td>";				
					$stream.= "<td class=rowcontent>".$paydate[$noinvDt[$nopo.$grnnmbr]]."</td>";				
					$stream.= "<td class=rowcontent align=right>".$difgrn."</td>";				
					$stream.= "<td class=rowcontent align=right>".$difinv."</td>";				
					$stream.= "<td class=rowcontent align=right>".$difpay."</td>";				
								
					$stream.="</tr>";
				}
			}else{
				$stream.= "<td colspan=9></td>";
			}
			 
		

			$stream.= "</tr>";
	}
	
	
	$stream.="</table>";
	echo $stream;

	
	break;

 case'excel';

 	if ($stts==3) {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
			on a.nopo=b.nopo left join log_prapoht c
			on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
			and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg." and statuspo=3
			and a.nopo in (select a.nopo from ".$dbname.".log_poht a left join ".$dbname.".keu_tagihanht b
			on a.nopo=b.nopo where b.tanggal between '".$tgl."' and '".$tgl1."' and posting !=1)";
		}

	if ($stts==2) {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
			on a.nopo=b.nopo left join log_prapoht c
			on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
			and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg." and a.statuspo=3
			and a.nopo in (select a.nopo from ".$dbname.".log_poht a left join ".$dbname.".keu_tagihanht b
			on a.nopo=b.nopo where a.tanggal between '".$tgl."' and '".$tgl1."' and noinvoice is null)";
		}

	if ($stts==1) {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
					on a.nopo=b.nopo left join log_prapoht c
					on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
					and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg." and a.statuspo=2";
	}



	if ($stts=='') {
			$str="select distinct a.nopo,b.nopp,c.tanggal as tanggalpp, a.tanggal from ".$dbname.".log_poht a left join ".$dbname.".log_podt b 
					on a.nopo=b.nopo left join log_prapoht c
					on b.nopp=c.nopp where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")
					and a.tanggal between '".$tgl."' and '".$tgl1."' ".$kdorg."";

		}

	
	//exit('error'.$str);
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $nomorpo[$bar['nopo']]=$bar['nopo'];
        $nomorpp[$bar['nopo']]=$bar['nopp'];
        $tanggalpo[$bar['nopo']]=$bar['tanggal'];
        $tanggalpp[$bar['nopo']]=$bar['tanggalpp'];
    }

	$str="select distinct a.nopo,a.tanggal,c.notransaksi as gnr_number,c.tanggal as gnr_date from ".$dbname.".log_poht a left join ".$dbname.".log_transaksi_vw c 
    on a.nopo=c.nopo where  a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$ptt.")";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
    	if($stts=="3"){
    		$scek="select * from ".$dbname.".keu_tagihanht where notransaksi_gr='".$bar['gnr_number']."'";
    		$rcek=fetchData($scek);
    		if(count($rcek)==0){
    			continue;
    		}
    	}
        $gnrno[$bar['gnr_number']]=$bar['gnr_number'];
        $gnrnumber[$bar['nopo']][$bar['gnr_number']]=$bar['gnr_number'];
        $gnrdate[$bar['nopo']]=$bar['gnr_date']; 
    }

     /*echo "<pre>";
    print_r($gnrnumber);
    echo "</pre>";
*/


	$str="select noinvoice,notransaksi_gr,nopo,tanggal from ".$dbname.".keu_tagihanht where tipeinvoice='p' ".$unt.""; 	
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        //$nomorpo[$bar['nopo']]=$bar['nopo'];
        //$gnrnumber[$bar['notransaksi_gr']]=$bar['notransaksi_gr'];
        //$notrs_gr[$bar['nopo']]=$bar['notransaksi_gr'];
        $jmlGr[$bar['nopo']][]=$bar['notransaksi_gr'];
        $noinvDt[$bar['nopo'].$bar['notransaksi_gr']]=$bar['noinvoice'];
        $noinvTglDt[$bar['nopo'].$bar['notransaksi_gr']]=$bar['tanggal'];
    }

    $str="select notransaksi,keterangan1,tanggal,nodok from ".$dbname.".keu_kasbankdtht_vw where posting=1 
    		and keterangan1!='' "; 	

    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        
        $paynumber[$bar['notransasksi']]=$bar['notransaksi'];
        $noinvoice[$bar['keterangan1']]=$bar['keterangan1'];
        $nopo[$bar['nodok']]=$bar['nodok'];

        $paynum[$bar['keterangan1']]=$bar['notransaksi'];
        $paydate[$bar['keterangan1']]=$bar['tanggal'];


        }


	$stream="<table class=sortable cellspacing=1 cellpadding=1 border=1 width=100%>";
	$stream.="<thead>";

			   $stream.="<tr class=rowheader >";
			   $stream.="<td >".$_SESSION['lang']['po']." Number</td>";
			   $stream.="<td >".$_SESSION['lang']['po']." Date</td>";
			   $stream.="<td >PR Number</td>";
			   $stream.="<td >PR Date</td>";
			   $stream.="<td >GRN Number</td>";
			   $stream.="<td >GRN Date</td>";
			   $stream.="<td >Invoice number</td>";
			   $stream.="<td >Invoice receipt</td>";
			   $stream.="<td >Payment number</td>";
			   $stream.="<td >Payment date</td>";
			   $stream.="<td >GRN Aging</td>";
			   $stream.="<td >Invoice Aging</td>";
			   $stream.="<td >Payment Aging</td>";
	

	$stream.="</tr></thead>";
	 $tanggal=date('Y-m-d');
	 

	foreach ($nomorpo as $nopo) {
		
		$countgrn = count($gnrnumber[$nopo]);
			$stream.= "<tr class=rowcontent>";
			$stream.= "<td rowspan='".$countgrn."'>".$nopo."</td>";
			$stream.= "<td rowspan='".$countgrn."'>".$tanggalpo[$nopo]."</td>";
			$stream.= "<td rowspan='".$countgrn."'>".$nomorpp[$nopo]."</td>";
			$stream.= "<td rowspan='".$countgrn."'>".$tanggalpp[$nopo]."</td>";
		
			if($countgrn > 0){

				$no=0;
				foreach ($gnrnumber[$nopo] as $grnnmbr) {

					if ($tanggalpo[$nopo]!='' and $gnrdate[$nopo]!='') {
						
					 $diffgrn =(strtotime($gnrdate[$nopo])-strtotime($tanggalpo[$nopo]));
                     $difgrn =floor(($diffgrn)/(60*60*24));
					}
					
					elseif ($tanggalpo[$nopo]!='' and $gnrdate[$nopo]=='') {
						
					 $diffgrn =(strtotime($tanggal)-strtotime($tanggalpo[$nopo]));
                     $difgrn =floor(($diffgrn)/(60*60*24));
					}
					else
					{
						$difgrn ='';
					}

					if ($gnrdate[$nopo]!='' and $noinvTglDt[$nopo.$grnnmbr]!='') {
						
					 $diffinv =(strtotime($noinvTglDt[$nopo.$grnnmbr])-strtotime($gnrdate[$nopo]));
                     $difinv=floor(($diffinv)/(60*60*24));
					}
					elseif ($gnrdate[$nopo]!='' and $noinvTglDt[$nopo.$grnnmbr]=='') {
						
					 $diffinv =(strtotime($tanggal)-strtotime($gnrdate[$nopo]));
                     $difinv=floor(($diffinv)/(60*60*24));
					}
					else
					{
						$difinv ='';
					}

					if ($noinvTglDt[$nopo.$grnnmbr]!='' and $paydate[$noinvDt[$nopo.$grnnmbr]]!='') {
						
					 $diffpay =(strtotime($paydate[$noinvDt[$nopo.$grnnmbr]])-strtotime($noinvTglDt[$nopo.$grnnmbr]));
                     $difpay =floor(($diffpay)/(60*60*24));
					}
					elseif ($noinvTglDt[$nopo.$grnnmbr]!='' and $paydate[$noinvDt[$nopo.$grnnmbr]]=='') {
						
					 $diffpay =(strtotime($tanggal)-strtotime($noinvTglDt[$nopo.$grnnmbr]));
                     $difpay =floor(($diffpay)/(60*60*24));
					}
					else
						{
						$difpay ='';
					}
                        					
					$no++;
					if($no < 1){
						$stream.="<tr class=rowcontent>";
					}

					$stream.= "<td class=rowcontent>".$gnrnumber[$nopo][$grnnmbr]."</td>";
				
					$stream.= "<td class=rowcontent>".$gnrdate[$nopo]."</td>";
					$stream.= "<td class=rowcontent>".$noinvDt[$nopo.$grnnmbr]."</td>";
					$stream.= "<td class=rowcontent>".$noinvTglDt[$nopo.$grnnmbr]."</td>";				
					$stream.= "<td class=rowcontent>".$paynum[$noinvDt[$nopo.$grnnmbr]]."</td>";				
					$stream.= "<td class=rowcontent>".$paydate[$noinvDt[$nopo.$grnnmbr]]."</td>";				
					$stream.= "<td class=rowcontent align=right>".$difgrn."</td>";				
					$stream.= "<td class=rowcontent align=right>".$difinv."</td>";				
					$stream.= "<td class=rowcontent align=right>".$difpay."</td>";				
								
					$stream.="</tr>";
				}
			}else{
				$stream.="<td colspan=9></td>";
				$stream.= "</tr>";
	}
}
	
	

	
	$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			
	$dte=date("Hms");
	$nop_="Progress_Report__".$dte;
	$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
	gzwrite($gztralala, $stream);
	gzclose($gztralala);
	echo "<script language=javascript1.2>
	window.location='tempExcel/".$nop_.".xls.gz';
	</script>";

        break;
    
		
}
?>