<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');



$proses    = checkPostGet('proses','');
$unit      = checkPostGet('unit','');
$per       = checkPostGet('per','');
$jenis     = checkPostGet('jenis','');
$tipe      = checkPostGet('tipe','');
$tahun     = checkPostGet('tahun','');
$tgl       = tanggalsystemn(checkPostGet('tgl',''));
$pengali   = checkPostGet('pengali','');
$makan     = checkPostGet('makan','');
$kawin     = checkPostGet('kawin','');
$bulanawal = checkPostGet('bulanawal','');
$bulanakhir= checkPostGet('bulanakhir','');

$optTk     = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$optJab    = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');


if($jenis!=26){
    $disabled="disabled";
}else {
    $disabled="";
}

if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<div><table class=sortable cellspacing=1 cellpadding=7 style='width:100%;' >";
}

$stKawin='';
if($kawin!=''){
    $stKawin="and statusperkawinan='".$kawin."' ";
}

if($tipe == 4){

    $jumlah_tahunlalu = (substr($per,5,2) + 1 );
    $jumlahnow = (substr($per,5,2) - 1 );
    $tahunlalu= (substr($per,0,4) - 1 );

    $llu=0;
    $now=0;
    
    for ($ii=$jumlah_tahunlalu; $ii <= 12 ; $ii++) { 
        $llu += 1;
        $prdgjllu = addZero($ii,2);
        ## ambil gaji dengan komponen 
        $str="select karyawanid,idkomponen,hk,periodegaji from ".$dbname.".sdm_gaji where periodegaji ='".$tahunlalu."-".$prdgjllu."' and kodeorg='".$unit."' and idkomponen = '1' ";
        $res=fetchdata($str);
        foreach($res as $bar){
            $hkllu[substr($bar['periodegaji'],5,2)][$bar['karyawanid']]=$bar['hk'];
        }
    }
    for ($iii=1; $iii <= $jumlahnow ; $iii++) { 
        $now += 1;
        $prdgjnow = addZero($iii,2);
        ## ambil gaji dengan komponen 
        $str="select karyawanid,idkomponen,hk,periodegaji from ".$dbname.".sdm_gaji where periodegaji ='".($tahunlalu+1)."-".$prdgjnow."' and kodeorg='".$unit."' and idkomponen = '1' ";
        $res=fetchdata($str);
        foreach($res as $bar){
            $hknow[substr($bar['periodegaji'],5,2)][$bar['karyawanid']]=$bar['hk'];
        }
    }
    $rowsp = "rowspan=2";
    $colspl = "colspan=".$llu."";
    $colspn = "colspan=".$now."";
}else{
    $rowsp = "rowspan=1";
    $colspl = "";
    $colspn = "";
}

$stream.="<thead class=rowheader>";
$stream.="      
    <tr class=class=rowcontent style=text-transform:uppercase; >
        <th bgcolor=#CCCCCC align=center ".$rowsp.">".$_SESSION['lang']['nourut']."</th> 
        <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">".$_SESSION['lang']['nik']." </th>    
		<th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">NAMA TK</th>
        <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">GOL</th>
        <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">".$_SESSION['lang']['status']."</th>
        <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">".$_SESSION['lang']['bagian']."</th>
        <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">".$_SESSION['lang']['jabatan']."</th>";
            if($tipe == 4){
                $stream.="
                <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$colspl.">THN ".$tahunlalu."</th>
                <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$colspn.">THN ".($tahunlalu + 1)."</th>
                <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">JUMLAH HK</th>
                <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">PROPORSI UPAH / BULAN</th>
                <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">JUMLAH UPAH RATA2</th>
                ";
            }

$stream.=" <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">BLN</th>";

if($tipe != 4){
    $stream.=" <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">Gaji 1 BLN</th>";
}

$stream.=" <th bgcolor=#CCCCCC style=text-transform:uppercase; align=center ".$rowsp.">THR (".substr($tahun,0,4).")</th>
    </tr>";
    $stream.="<tr class=rowcontent>";
        if($tipe == 4){
            for ($i=$jumlah_tahunlalu; $i <= 12 ; $i++) { 
                $stream.=" 
                        <th bgcolor=#CCCCCC align=center>".$i." </th> ";   
            }
            for ($i=1; $i <= $jumlahnow ; $i++) { 
                $stream.=" 
                        <th bgcolor=#CCCCCC align=center>".$i." </th> ";   
            }
        }
    $stream.="</tr>";
$stream.="</thead><tbody>";

@$lastday = date('t', strtotime($per));	
$tglakhir=$per.'-'.$lastday;

#bentuk list karyawan
$str="select karyawanid,namakaryawan,nik,tipekaryawan,kodejabatan,kodegolongan,lokasitugas,bagian,tanggalpengangkatan,tanggalmasuk,statuspajak from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$tglakhir."') and tipekaryawan='".$tipe."' ".$stKawin." ";	
$res=fetchdata($str);
foreach($res as $bar){
    @$counterKar+=1;
    @$idKar[$bar['karyawanid']]=$bar['karyawanid'];
    $nama[$bar['karyawanid']]=$bar['namakaryawan'];
    $tk[$bar['karyawanid']]=$bar['tipekaryawan'];
    $nik[$bar['karyawanid']]=$bar['nik'];
    $lokasi[$bar['karyawanid']]=$bar['lokasitugas'];
    $golongan[$bar['karyawanid']]=$bar['kodegolongan'];
    $jab[$bar['karyawanid']]=$bar['kodejabatan'];
    $bag[$bar['karyawanid']]=$bar['bagian'];
    $tglMasuk[$bar['karyawanid']]=$bar['tanggalmasuk'];
    $statuspajak[$bar['karyawanid']]=$bar['statuspajak'];
}


## komponen gaji 1 bulan != 4
$komponenx = '';
$str="select component from ".$dbname.".sdm_ho_thr_setup";
$res=fetchdata($str);
foreach ($res as $key => $val) {
    if ($komponenx == '') {
        $komponenx = "'" . $val['component'] . "'";
    } else {
        $komponenx .= ",'" . $val['component'] . "'";
    }
}

$totalgaji1bulan = array();

## ambil gaji dengan komponen 
if($tipe == 4){
    $str="select karyawanid,idkomponen,jumlah from ".$dbname.".sdm_5gajipokok where tahun ='".$tahun."' and kodeorg='".$unit."' and idkomponen = 1";
}else{
    $str="select karyawanid,idkomponen,jumlah from ".$dbname.".sdm_gaji where periodegaji ='".$tahun."' and kodeorg='".$unit."' and idkomponen in (".$komponenx.") ";
}
$res=fetchdata($str);
foreach($res as $bar){
	$totalgaji1bulan[$bar['karyawanid']]+=$bar['jumlah'];
}

if(empty($totalgaji1bulan)){
    exit("Warning : Basis gaji periode ".$tahun." Tidak ada / Proses gaji terlebih dahulu untuk periode ".$tahun." ");
}

$optgolongan=makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');

$interval=array();
$totalMonths=array();

if(is_array(isset($idKar)?$idKar:'')){
    foreach ($idKar as $kar){
        @$no+=1;
        $stream.="<tr class=rowcontent id=row".$no.">";
			if ($proses != 'excel') {
				$stream.="<td hidden id=jenissave".$no.">".$jenis."</td>";
				$stream.="<td hidden id=karyawanidsave".$no.">".$kar."</td>";
                $stream.="<td hidden id=kdorgsave".$no.">".$lokasi[$kar]."</td>";
            }

			$stream.="<td align=center>".$no."</td>";
            $stream.="<td>".$nik[$kar]."</td>";
			$stream.="<td>".$nama[$kar]."</td>";
			$stream.="<td align=center>".$optgolongan[$golongan[$kar]]."</td>";
			$stream.="<td align=center>".$statuspajak[$kar]."</td>";
            $stream.="<td align=center>".$bag[$kar]."</td>";
            $stream.="<td>".$optJab[$jab[$kar]]."</td>";
            
            if($tipe == 4){
                $jmlah = 0;
                for ($i=$jumlah_tahunlalu; $i <= 12 ; $i++) { 
                    $prdgjllu = addZero($i,2);

                    if($hkllu[$prdgjllu][$kar] == ''){
                        $hkllu[$prdgjllu][$kar] = 0;
                    }

                    if($hkllu[$prdgjllu][$kar] != ''){
                        @$bln[$kar] += $jmlah +1;
                    }

                    $stream.=" <td align=center>".$hkllu[$prdgjllu][$kar]." </td> ";   
                    $totalHKlalu[$kar] += $hkllu[$prdgjllu][$kar];
                }
                for ($i=1; $i <= $jumlahnow ; $i++) { 
                    $prdgjnow = addZero($i,2);
                    
                    if($hknow[$prdgjnow][$kar] == ''){
                        $hknow[$prdgjnow][$kar] = 0;
                    }

                    if($hknow[$prdgjnow][$kar] != ''){
                        @$bln[$kar] += $jmlah +1;
                    }

                    $stream.=" <td align=center>".$hknow[$prdgjnow][$kar]." </td> ";  
                    $totalHKnow[$kar] += $hknow[$prdgjnow][$kar]; 
                }

                $jumlahhk[$kar]= $totalHKlalu[$kar] + $totalHKnow[$kar] ;       
                
                $pengaliproporsi[$kar] = ($jumlahhk[$kar] / $bln[$kar]) / 25.0 ;
                $upahperhari[$kar]= $totalgaji1bulan[$kar] /25;

                if($pengaliproporsi[$kar] >= 1){
                    $totalupahRata[$kar] =  (1 * 25) * $upahperhari[$kar];
                }else{
                    $totalupahRata[$kar] =  ($pengaliproporsi[$kar] * 25) * $upahperhari[$kar];
                }

               $THR[$kar] = ($bln[$kar] /12 ) * $totalupahRata[$kar]; 
               $pengalisave[$kar] = $bln[$kar] /12  ;


                
                

                $stream.="<td align=center>".number_format($jumlahhk[$kar],0)."</td>";
                $stream.="<td align=center>".number_format(fixnan($pengaliproporsi[$kar]),2)."</td>";
                $stream.="<td align=center>".number_format(fixnan($totalupahRata[$kar]),0)."</td>";
                $stream.="<td align=center>".number_format($bln[$kar],0)."</td>";

            }else{

                  ## Hitung berapa bulan tgl cutoff ke TMK
                $tgl1 = new DateTime($tgl);
                $tgl2 = new DateTime($tglMasuk[$kar]);

                // Calculate the difference between the two dates
                $interval[$kar] = $tgl1->diff($tgl2);

                // Calculate the total number of months
                $totalMonths[$kar] = ($interval[$kar]->y * 12) + $interval[$kar]->m;

                if($totalMonths[$kar] >= 11 ){
                    $totalMonths[$kar] = 11;
                }

                $stream.="<td align=center>".number_format($totalMonths[$kar],0)."</td>";
                $stream.="<td align=center>".number_format($totalgaji1bulan[$kar],0)."</td>";
                $pengalibulanan[$kar] = $totalMonths[$kar] / 11;
            }

            if($tipe == 4){
                if ($proses != 'excel') {
                    $stream.="<td hidden id=pengalisave".$no." align=center>".number_format(fixnan($pengalisave[$kar]),2)."</td>";
                    $stream.="<td align=center><input type=text disabled id=jumlahsave".$no." value='".@number_format(fixnan($THR[$kar]))."' size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:100px;\"></td>";
                }else{
                    $stream.="<td>".number_format($totalTHR[$kar])."</td>";
                }	
            }else{
                if ($proses != 'excel') {
                    $stream.="<td hidden id=pengalisave".$no." align=center>".number_format($pengalibulanan[$kar],0)."</td>";
                    $stream.="<td align=center><input type=text disabled id=jumlahsave".$no." value='".@number_format($totalgaji1bulan[$kar]*$pengalibulanan[$kar])."' size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:100px;\"></td>";
                }else{
                    $stream.="<td>".number_format($totalgaji1bulan[$kar] * $pengalibulanan)."</td>";
                }	
            }
						
        $stream.="</tr>";           

    }
}

if ($proses != 'excel') {
    $stream.="<button class=mybutton onclick=del(".$no.");>".$_SESSION['lang']['proses']."</button>";
}

$stream.="</tbody></table></div>";
		
switch($proses){
    case'preview':
         echo $stream;
	break;
    
    ######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_tunjangan_".$jenis._.$tglSkrg;
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
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
		break;
                
	default:
}



?>