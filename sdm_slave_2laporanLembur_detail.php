<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses  = checkPostGet('proses','');
$lksiTgs = $_SESSION['empl']['lokasitugas'];
$kdeOrg  = checkPostGet('kdeOrg','');
$kdOrg   = checkPostGet('kdOrg','');
$tgl1    = tanggalsystem(checkPostGet('tgl1',''));
$tgl2    = tanggalsystem(checkPostGet('tgl2',''));
$tgl_1   = tanggalsystem(checkPostGet('tgl_1',''));
$tgl_2   = tanggalsystem(checkPostGet('tgl_2',''));
$periode = checkPostGet('periode','');
$kdUnit  = checkPostGet('kdUnit','');
$pilihan = checkPostGet('pilihan','');
$pilihan2= checkPostGet('pilihan2','');
$pilihan3= checkPostGet('pilihan3','');

$periodeGaji=$periode;
$periode=explode('-',$periode);
$total=0;
if(!$kdOrg)$kdOrg=$_SESSION['empl']['lokasitugas'];

function dates_inbetween($date1, $date2){
    $day = 60*60*24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);
    for($x = 1; $x < $days_diff; $x++)
        {
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }
    $dates_array[] = date('Y-m-d',$date2);
    return $dates_array;
}

	if(($tgl_1!='')&&($tgl_2!='')){	
		$tgl1=$tgl_1;
		$tgl2=$tgl_2;
	}
	$test = dates_inbetween($tgl1, $tgl2);


    // get namaorganisasi =========================================================================
    $sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kdOrg."' ";
	$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
	$qOrg->setFetchMode(PDO::FETCH_ASSOC);
    while($rOrg=$qOrg->fetch()){
        $nmOrg=$rOrg['namaorganisasi'];
    }

    if(!$nmOrg)$nmOrg=$kdOrg;
    //ambil where untuk data karyawan
    if($kdOrg!=''){
        $kodeOrg=$kdOrg;
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
        {
            $where=" and lokasitugas = '".$kodeOrg."'";
            $where2=" and substr(kodeorg,1,4)='".$kdOrg."'";
        }
        else
        {
            if(strlen($kdOrg)>4)
            {
                    $where=" and subbagian='".$kdOrg."'";
                    $where2=" and kodeorg='".$kdOrg."'";
            }
            else
            {
                    $where=" and lokasitugas='".$kdOrg."'";
                    $where2=" and substr(kodeorg,1,4)='".$kdOrg."'";
            }
        }
    } else {
        $kodeOrg=$_SESSION['empl']['lokasitugas'];
        $where=" and lokasitugas='".$kodeOrg."'";
    }

    // pilihan 2
    if($pilihan2=='semua'){
        $where3 = '';
    }else
    if($pilihan2=='bulanan'){
        $where3 = ' and a.sistemgaji = \'Bulanan\' ';
    }else
    if($pilihan2=='harian'){
        $where3 = ' and a.sistemgaji = \'Harian\' ';
    }

    // pilihan 3
    if($pilihan3=='semua')
        $where4 = '';
    else
        $where4 = " and a.bagian = '".$pilihan3."' ";
    
    // building array: jabatan =========================================================================	
    $strJ="select * from ".$dbname.".sdm_5jabatan";
	$resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
    $resJ->setFetchMode(PDO::FETCH_OBJ);
    while($barJ=$resJ->fetch())
    {
            $jab[$barJ->kodejabatan]=$barJ->namajabatan;
    }

    // building array: bagian =========================================================================	
    $strJ="select * from ".$dbname.".sdm_5departemen";
	$resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
	$resJ->setFetchMode(PDO::FETCH_OBJ);
    while($barJ=$resJ->fetch())
    {
            $bag[$barJ->kode]=$barJ->nama;
    }
    
    $dzArr=array();
    $tot=array();
    $total=0;
    // ambil data lembur konversi ================================================================================
    $resData=array();
    $sGetLembur="select jamaktual, jamlembur,tipelembur from ".$dbname.".sdm_5lembur where kodeorg = '".$kodeOrg."'";
    $rGetLembur=fetchData($sGetLembur);
    foreach($rGetLembur as $row => $kar){
        $GetLembur[$kar['tipelembur'].$kar['jamaktual']]=$kar['jamlembur'];
    }  

    // ambil data lembur ================================================================================
	$rplembur=array();
     $dakarbulanan=0;
     $str = "select karyawanid from ".$dbname.".datakaryawan_hist where approval_status='8' and version_type='B' ".$where." and periodegaji='".$periodeGaji."' "; #exit('error'.$str);
     $res = fetchdata($str);
     if(count($res)>0){ 
		$dakarbulanan=1;
     }

      if($dakarbulanan==0){
        $sPeople="SELECT a.subbagian,b.karyawanid as karyawanid, b.tanggal as tanggal, b.jamaktual as uangkelebihanjam, b.uangkelebihanjam as rupiah , b.tipelembur, a.nik, a.namakaryawan, a.bagian, a.kodejabatan,a.lokasitugas,b.jammulai,b.jamselesai,b.ket
                    FROM ".$dbname.".sdm_lemburdt b LEFT JOIN ".$dbname.".datakaryawan a on a.karyawanid = b.karyawanid
                    WHERE b.tanggal between  '".$tgl1."' and '".$tgl2."' ".$where2." ".$where3." ".$where4." and b.jamaktual>0 order by a.subbagian asc,a.namakaryawan asc";
      }else{
        $sPeople="SELECT a.subbagian,b.karyawanid as karyawanid, b.tanggal as tanggal, b.jamaktual as uangkelebihanjam, b.uangkelebihanjam as rupiah , b.tipelembur, a.nik, a.namakaryawan, a.bagian, a.kodejabatan,a.lokasitugas,b.jammulai,b.jamselesai,b.ket
                    FROM ".$dbname.".sdm_lemburdt b LEFT JOIN ".$dbname.".datakaryawan_hist a on a.karyawanid = b.karyawanid
                    WHERE b.tanggal between  '".$tgl1."' and '".$tgl2."' ".$where2." ".$where3." ".$where4." and approval_status='8' and version_type='B' and  periodegaji='".$periodeGaji."' and b.jamaktual>0 order by a.subbagian asc,a.namakaryawan asc";

      }
	 


    
	$query=$owlPDO->query($sPeople) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
    while($res=$query->fetch()) {
        $karya[$res['karyawanid']]=$res['karyawanid'];
        $dzArr[$res['karyawanid']]['id']=$res['karyawanid'];
        if(($res['subbagian']=='')||is_null($res['subbagian'])){
            $res['subbagian']=$res['lokasitugas'];
        }
        $dzArr[$res['karyawanid']]['sb']=$res['subbagian'];

        $dzArr[$res['karyawanid']]['nm']=$res['namakaryawan'];
        $dzArr[$res['karyawanid']]['bg']=$res['nik'];
        $dzArr[$res['karyawanid']]['jb']=$jab[$res['kodejabatan']];
        $dzArr[$res['karyawanid']][$res['karyawanid']]=$res['karyawanid'];
        
        $dzArr[$res['karyawanid']][$res['tanggal']]=$res['uangkelebihanjam']; 
        $lembur[$res['karyawanid']][$res['tanggal']]=$GetLembur[$res['tipelembur'].$res['uangkelebihanjam']]; 
        $rplembur[$res['karyawanid']][$res['tanggal']]=$res['rupiah']; 
		//,b.jammulai,b.jamselesai,b.ket
        $dtjammulai[$res['karyawanid']][$res['tanggal']]=$res['jammulai']; 
        $dtjamselesai[$res['karyawanid']][$res['tanggal']]=$res['jamselesai']; 
        $dtket[$res['karyawanid']][$res['tanggal']]=$res['ket']; 
    }
	
	$str = "select * from ".$dbname.".sdm_lemburdt2 where kodeorg = '".$kodeOrg."' and tanggal between  '".$tgl1."' and '".$tgl2."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$rowspan[$bar['karyawanid']][$bar['tanggal']]++;
		$detail[$bar['karyawanid']][$bar['tanggal']][]=$bar['karyawanid'];
		$mulai[$bar['karyawanid']][$bar['tanggal']][]=$bar['jammulai'];
		$selesai[$bar['karyawanid']][$bar['tanggal']][]=$bar['jamselesai'];
		$ketx[$bar['karyawanid']][$bar['tanggal']][]=$bar['ket'];
	}
        
        
    //klo ada server busy di sini pasti karena tidak filter kodeorg $kdOrg
    $iGaji="select jumlah,karyawanid from ".$dbname.".sdm_5gajipokok where tahun='".substr($periodeGaji,0,4)."'  and idkomponen=1";
	$nGaji=$owlPDO->query($iGaji) or die(print " Gagal: ".PDOException::getMessage());
	$nGaji->setFetchMode(PDO::FETCH_ASSOC);
    while($dGaji=  $nGaji->fetch()){

        $gajiPokok[$dGaji['karyawanid']]=$dGaji['jumlah'];
        $gajiPokok2[]=$dGaji['jumlah'];
    }
        
// switch($proses)
// {
    // case'preview':
if (($proses=='preview')||($proses=='excel')){

    if($periodeGaji=='')
    {
        echo"warning: Period required";
        exit();
    }

    $stream="";
    if($proses=='excel'){
        $border=1;
        $colatas=count($test)+8;
        $stream.="<table border='0'>
        <tr>
        <td colspan='".$colatas."' align=center>".strtoupper("Overtime Recapitulation")." : ".$nmOrg." (dalam ".$pilihan.") ".$pilihan2."</td>
        </tr>
        <tr>
        <td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d ". tanggalnormal($tgl2)."</td>
        </tr></table>";
    }else{
       $border=0; 
       $stream="";
    }


    // preview: nampilin header ================================================================================
    $stream.="<div class='table-scroll'><table cellspacing='1' border='".$border."' class='sortable'  >
    <thead class=rowheader>
    <tr>
    <th align=center rowspan='2'>No</th>
    <th align=center rowspan='2'>".$_SESSION['lang']['subbagian']."</th>
    <th align=center rowspan='2'>".$_SESSION['lang']['nik']."</th>
	<th align=center rowspan='2'>".$_SESSION['lang']['nama']."</th>
    <th align=center rowspan='2'>".$_SESSION['lang']['jabatan']."</th>";
    
    foreach($test as $ar => $isi)
    {
        $qwe=date('D', strtotime($isi));
        // $stream.="<th width=5px align=center colspan=2>";
        if($qwe=='Sun'){
            $stream.="<th width=5px align=center colspan=6><font color=red>".substr($isi,8,2)."</font></th>"; 
        }else {
            $stream.="<th width=5px align=center colspan=6>".substr($isi,8,2)."</th>"; 
        }
    }

    $stream.="<th align=center colspan=3>".$_SESSION['lang']['jumlah']."</th>";
    $stream.="</tr>
            <tr>";

    foreach($test as $ar => $isi)
    {
        $stream.="<th align=center>Aktual</th>
            <th align=center>Basis Lembur</th>
            <th align=center>Rupiah</th>
            <th align=center>".$_SESSION['lang']['jammulai']."</th>
            <th align=center>".$_SESSION['lang']['jamselesai']."</th>
            <th align=center>".$_SESSION['lang']['keterangan']."</th>";
    }
    $stream.="<th align=center>Aktual</th>
            <th align=center>Basis Lembur</th>
            <th align=center>Rupiah</th>";

    $stream.="</tr>
    </thead>
    <tbody>";
           // echo "<pre>";
           // print_r($gajiPokok2);
           // echo"</pre>";
           // echo "<pre>";
           // print_r($gajiPokok);
           // echo"</pre>";
           // echo"<pre>";
           // print_r($GetLembur);
           // echo"</pre>";
    // preview: nampilin data ================================================================================
           $jmAktualArr=array();
           $jmLemburArr=array();
           $rplemburv=array();
    foreach($dzArr as $idkar=>$qwe){
		$no+=1;
		$stream.="<tr class=rowcontent style=vertical-align:top><td align=center>".$no."</td>
		<td>".$qwe['sb']."</td>
		<td>".$qwe['bg']."</td>
		<td>".$qwe['nm']."</td>
		<td>".$qwe['jb']."</td>";
        $zxc=0;
		$zxcl=0;
		$rplemburh=0;
            foreach($test as $ar => $isi){
                setIt($qwe[$isi],0);
                if($qwe[$isi]!=''){
                    $stream.="<td align=right>".number_format($qwe[$isi],2)."</td>";
                    $stream.="<td align=right>".number_format($lembur[$idkar][$isi],2)."</td>";

					$tipekar = makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$idkar."'");
					$stream.="<td align=right>".number_format($rplembur[$idkar][$isi])."</td>";    
					$stream.="<td align=center>".$dtjammulai[$idkar][$isi]."";
						if($rowspan[$bar['karyawanid']]>1){
							$stream.="<table style=font-style:italic;color:blue;>";
								foreach($detail as $karyid => $v1){
									foreach($v1 as $tnggl => $v2){
										foreach($v2 as $key => $value){
											if($idkar==$karyid and $isi == $tnggl){
												$stream.="<tr class=rowcontent>";
												$stream.="<td align=center>".$mulai[$idkar][$isi][$key]."</td>";
												$stream.="</tr>";								
											}
										}
									}
								}
							$stream.="</table>";
						}
					$stream.="</td>";    
					$stream.="<td align=left>".$dtjamselesai[$idkar][$isi]."";
						if($rowspan[$bar['karyawanid']]>1){
							$stream.="<table style=font-style:italic;color:blue;>";
								foreach($detail as $karyid => $v1){
									foreach($v1 as $tnggl => $v2){
										foreach($v2 as $key => $value){
											if($idkar==$karyid and $isi == $tnggl){
												$stream.="<tr class=rowcontent>";
												$stream.="<td align=center>".$selesai[$idkar][$isi][$key]."</td>";
												$stream.="</tr>";								
											}
										}
									}
								}
							$stream.="</table>";
						}
					$stream.="</td>";    
					$stream.="<td align=left>".$dtket[$idkar][$isi]."";
					if($rowspan[$bar['karyawanid']]>1){
							$stream.="<table style=font-style:italic;color:blue;>";$n=0;
								foreach($detail as $karyid => $v1){
									foreach($v1 as $tnggl => $v2){
										foreach($v2 as $key => $value){
											if($idkar==$karyid and $isi == $tnggl){
												$n++;
												$stream.="<tr class=rowcontent>";
												$stream.="<td align=left>(".$n.") ".$ketx[$idkar][$isi][$key]."</td>";
												$stream.="</tr>";								
											}
										}
									}
								}
							$stream.="</table>";
						}
					$stream.="</td>";    
                }else{
                  $stream.="<td align=right>&nbsp;</td>";  
                  $stream.="<td align=right>&nbsp;</td>";  
                  $stream.="<td align=right>&nbsp;</td>";  
				  $stream.="<td align=right>&nbsp;</td>";  
                  $stream.="<td align=right>&nbsp;</td>";  
                  $stream.="<td align=right>&nbsp;</td>";  
                } 
                setIt($asd[$isi],0);
                $zxc+=$qwe[$isi];
                $zxcl+=$lembur[$idkar][$isi];
                $rplemburh+=$rplembur[$idkar][$isi];
                $jmAktualArr[$isi]+=$qwe[$isi];
                $jmLemburArr[$isi]+=$lembur[$idkar][$isi];
                $rplemburv[$isi]+=$rplembur[$idkar][$isi];
            }
        $stream.="<td align=right>".hidezerodecimal($zxc,3)."</td>";
        $stream.="<td align=right>".hidezerodecimal($zxcl,3)."</td>";
		$tipekar = makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$idkar."'");
		$stream.="<td align=right>".number_format($rplemburh)."</td>";
		
		
        $stream.="</tr>";
        
    }

    // preview: nampilin total ================================================================================
    $stream.="
    <tr class=rowcontent>
    <td colspan=5>".$_SESSION['lang']['total']."</td>";
    foreach($test as $ar => $isi)
    {
		setIt($jmAktualArr[$isi],0);
        setIt($jmLemburArr[$isi],0);
		$stream.="<td align=right>".number_format($jmAktualArr[$isi],2)."</td>";
        $stream.="<td align=right>".number_format($jmLemburArr[$isi],2)."</td>";
		
        $stream.="<td align=right>".number_format($rplemburv[$isi])."</td>";
		 $stream.="<td align=right></td>";
        $stream.="<td align=right></td>";
        $stream.="<td align=right></td>";
		$totalJmAkt+=$jmAktualArr[$isi];
        $totalJmLmbr+=$jmLemburArr[$isi];
        $totalrplembur+=$rplemburv[$isi];
    }
        $stream.="<td align=right>".number_format($totalJmAkt,2)."</td>";
        $stream.="<td align=right>".number_format($totalJmLmbr,2)."</td>";
        $stream.="<td align=right>".number_format($totalrplembur)."</td>";
       
    $stream.="</tr></tbody></table></div>";
}
    // break;

 //    case'excel':
 //        if($periodeGaji=='')
 //    {
 //            echo"warning: Periode tidak boleh kosong";
 //            exit();
 //    }
 //    $colatas=count($test)+8;
 //    $stream.="<table border='0'>
	// <tr>
	// <td colspan='".$colatas."' align=center>".strtoupper("Overtime Recapitulation")." : ".$nmOrg." (dalam ".$pilihan.") ".$pilihan2."</td>
	// </tr>
 //    <tr>
	// <td colspan='".$colatas."' align=center>".strtoupper($_SESSION['lang']['periode'])." :". tanggalnormal($tgl1)." s.d ". tanggalnormal($tgl2)."</td>
	// </tr></table>";

 //    $stream.="<table cellspacing='1' border='1' class='sortable'>
 //    <thead class=rowheader>
 //    <tr>
 //    <td bgcolor=#DEDEDE align=center valign=center rowspan=2>No</td>
    
 //    <td bgcolor=#DEDEDE align=center valign=center rowspan=2>".$_SESSION['lang']['subbagian']."</td>
 //    <td bgcolor=#DEDEDE align=center valign=center rowspan=2>".$_SESSION['lang']['bagian']."</td>
	// <td bgcolor=#DEDEDE align=center valign=center rowspan=2>".$_SESSION['lang']['nama']."</td>
	// <td bgcolor=#DEDEDE align=center valign=center rowspan=2>".$_SESSION['lang']['jabatan']."</td>
 //    ";
 //    foreach($test as $ar => $isi)
 //    {
 //            $qwe=date('D', strtotime($isi));
 //            $stream.="<td bgcolor=#DEDEDE width=5px align=center colspan=2>";
 //            if($qwe=='Sun')$stream.="<font color=red>".substr($isi,8,2)."</font>"; else $stream.=(substr($isi,8,2)); 
 //            $stream.="</td>";
 //    }
 //    $stream.="<td bgcolor=#DEDEDE align=center colspan=2>".$_SESSION['lang']['jumlah']."</td>";

 //    $stream.="</tr>";
 //    $stream.="<tr>";

 //    foreach($test as $ar => $isi)
 //    {
 //        $stream.="<td align=center>Aktual</td>
 //                 <td align=center>Basis Lembur</td>";
 //    }
 //    $stream.="<td align=center>Aktual</td>
 //             <td align=center>Basis Lembur</td>";

 //    $stream.="</tr>";
 //    $stream.="</thead><tbody>";

 //    // preview: nampilin data ================================================================================
 //    foreach($dzArr as $idkar=>$qwe)
 //    {	$no+=1;
 //        $stream.="<tr class=rowcontent><td>".$no."</td>
 //        <td>".$qwe['bg']."</td>
 //        <td>".$qwe['sb']."</td>
 //        <td>".$qwe['nm']."</td>
	// 	<td>".$qwe['jb']."</td>
 //        ";
 //        $zxc=0;
 //        foreach($test as $ar => $isi)
 //        {
	// 		setIt($qwe[$isi],0);
	// 		setIt($asd[$isi],0);
 //                if($qwe[$isi]!=0){
 //                        if($pilihan=='rupiah')$stream.="<td align=right>".number_format($qwe[$isi])."</td>"; 
 //                                else $stream.="<td align=right>".number_format($qwe[$isi],1)."</td>";
 //                } else $stream.="<td align=right></td>";
 //                $zxc+=$qwe[$isi];
 //                $asd[$isi]+=$qwe[$isi];
 //        }
 //            $stream.="<td align=right>".number_format($zxc,1)."</td>";
                
 //        $stream.="</tr>";	
 //    }

 //    // preview: nampilin total ================================================================================
 //    $stream.="<thead class=rowheader>
 //    <tr>
 //    <td colspan=5>".$_SESSION['lang']['total']."</td>";
 //    foreach($test as $ar => $isi)
 //    {
 //        $stream.="<td align=right>".number_format($asd[$isi],1)."</td>";
 //        $total+=$asd[$isi];
 //    }
 //    $stream.="<td align=right>".number_format($total,1)."</td>";
    
 //    $stream.="</tbody></table>";

    if ($proses=='excel'){
        $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];     
    }

	if(!empty($period))
	{
			$art=$period;
			$art=$art[1].$art[0];
	}
	if(!empty($periode))
	{
			$art=$periode;
			$art=$art[1].$art[0];
	}
	if(!empty($kdeOrg))
	{
			$kodeOrg=$kdeOrg;
	}
	if(!empty($kdOrg))
	{
			$kodeOrg=$kdOrg;
	}

    if ($proses=='excel'){

    	$nop_="RekapLemburDetail".$art."__".$kodeOrg;
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
    } else{
        echo $stream;
    }
	// break;

//     default:
//     break;
// }

?>