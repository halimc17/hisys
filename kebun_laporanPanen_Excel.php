<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');

	$pt = checkPostGet('pt','');
	$gudang = checkPostGet('gudang','');
	$afdeling = checkPostGet('afdeling','');
	$intiplasma = checkPostGet('intiplasma','');
	$tgl1 = checkPostGet('tgl1','');
	$tgl2 = checkPostGet('tgl2','');
        
    if($gudang=='')
    {
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
		}
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
              sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk
              ,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma
              from ".$dbname.".kebun_prestasi_vs_hk a
              left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi 
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where c.induk = '".$pt."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%' 
              and a.jurnal=1 ".$where2."
              group by a.tanggal,a.kodeorg";
    }
    else
    {
        $where='';
        if($gudang != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.jurnal=1";
        }
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
		}
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
              sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk  
              ,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma
              from ".$dbname.".kebun_prestasi_vs_hk a 
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where unit = '".$gudang."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%'
              ".$where." ".$where2."
              group by a.tanggal, a.kodeorg";
    }
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
    $no=0;
    if($numrows<1)
    {
        echo"<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    else
    {
        $stream="<table border=0 cellpading=1 ><tr><td colspan=7 align=center>".$_SESSION['lang']['laporanpanen']."</td></tr>
        <tr><td colspan=3>".$_SESSION['lang']['periode']."</td><td colspan=4 align=left>".$tgl1." S/d ".$tgl1."</td></tr>    
        <tr><td colspan=3>".$_SESSION['lang']['unit']."</td><td colspan=4 align=left>".($gudang!=''?$gudang:$_SESSION['lang']['all'])."</td></tr>
        <tr><td colspan=3>".$_SESSION['lang']['pt']."</td><td colspan=4 align=left>".$pt."</td></tr>        
        </table>
        <br />
        <table border=1>
        <tr>
            <td bgcolor=#DEDEDE align=center>No.</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['afdeling']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['lokasi']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['intiplasma']."</td>    
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tahuntanam']."</td>    
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['janjang']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']." (Kg)</td>    
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahkerja']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahpenalty']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['premibasis']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahpremi']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlahhk']."</td>
            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti']."</td>
        </tr>";
		$totberat=$totUpah=$totUpahpenalty=$totJjg=$totPremi=$totPremibasis=$totHk=$totPenalty=0;
        while($bar=$res->fetch())
        {
            $no+=1;
            $periode=date('Y-m-d H:i:s');
            $tanggal=$bar->tanggal; 
            $kodeorg 	=$bar->kodeorg;
           
            $stream.="<tr>
                <td align=center width=20>".$no."</td>

                <td align=center>".$tanggal."</td>
                <td align=center>".substr($kodeorg,0,6)."</td>
                <td align=center>".$kodeorg."</td>
                <td align=center>".$bar->intiplasma."</td>
                <td align=center>".$bar->tahuntanam."</td>
                <td align=right>".number_format($bar->jjg,0)."</td>
                <td align=right>".number_format($bar->berat,2)."</td>    
                <td align=right>".number_format($bar->upah,2)."</td>
                <td align=right>".number_format($bar->upahpenalty,2)."</td>
                <td align=right>".number_format($bar->premibasis,2)."</td>
                <td align=right>".number_format($bar->premi,2)."</td>
                <td align=right>".number_format($bar->hkpanenperhari,2)."</td>
                <td align=right>".number_format($bar->penalty,2)."</td>
            </tr>"; 	
            $totberat+=$bar->berat;
            $totUpah+=$bar->upah;
            $totUpahpenalty+=$bar->upahpenalty;
            $totJjg+=$bar->jjg;
            $totPremi+=$bar->premi;
            $totPremibasis+=$bar->premibasis;
            $totHk+=$bar->hkpanenperhari;
            $totPenalty+=$bar->penalty;
        }
        $stream.="<tr>
            <td align=center width=20 colspan=6>&nbsp;</td>		 
            <td align=right>".number_format($totJjg,0)."</td>
            <td align=right>".number_format($totberat,2)."</td>     
            <td align=right>".number_format($totUpah,2)."</td>
            <td align=right>".number_format($totUpahpenalty,2)."</td>
            <td align=right>".number_format($totPremibasis,2)."</td>
            <td align=right>".number_format($totPremi,2)."</td>
            <td align=right>".number_format($totHk,2)."</td>
            <td align=right>".number_format($totPenalty,2)."</td>
        </tr>";
        $stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
    }	
    $tglSkrg=date("Ymd");
    $nop_="LaporanPanen".$pt."_".$gudang."_".$tgl1;
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
    //closedir($handle);
    }
?>