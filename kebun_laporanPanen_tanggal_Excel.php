<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');

    // ambil yang dilempar javascript
	$pt = checkPostGet('pt','');
	$unit = checkPostGet('unit','');
	$afdeling = checkPostGet('afdeling','');
	$intiplasma = checkPostGet('intiplasma','');
	$tgl1 = checkPostGet('tgl1','');
	$tgl2 = checkPostGet('tgl2','');
	
	// kamus luas
        $str2="select kodeorg, 	luasareaproduktif from ".$dbname.".setup_blok
        where kodeorg like '".$unit."%'";
	$qKmrn=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$qKmrn->setFetchMode(PDO::FETCH_OBJ);
    while($rKmr=$qKmrn->fetch())
    {
            $luasblok[$rKmr->kodeorg]=$rKmr->luasareaproduktif;
    }    
    
    // olah tanggal
    $tanggal1=explode('-',$tgl1);
    $tanggal2=explode('-',$tgl2);
    $date1=$tanggal1[2].'-'.$tanggal1[1].'-'.$tanggal1[0];
    $tanggalterakhir=date('t', strtotime($date1));
    
    // urutin tanggal
    $tanggal=Array();
    if($tanggal2[1]>$tanggal1[1]){ // beda bulan
        for ($i = $tanggal1[0]; $i <= $tanggalterakhir; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
        }
        for ($i = 1; $i <= $tanggal2[0]; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal2[2].'-'.$tanggal2[1].'-'.$ii]=$tanggal2[2].'-'.$tanggal2[1].'-'.$ii;
        }
    }else{ // sama bulan
        for ($i = $tanggal1[0]; $i <= $tanggal2[0]; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
        }
    }
        
    if($unit=='') // script copy-an dari kebun_laporanPanen.php
    {
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
		}
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,
              sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma 
              from ".$dbname.".kebun_prestasi_vs_hk a
              left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where c.induk = '".$pt."'  and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' and b.intiplasma like '%".$intiplasma."%' ".$where2."
              and a.jurnal=1     
              group by a.tanggal,a.kodeorg";
    }
    else
    {
        $where='';
        if($unit != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.jurnal=1";
        }
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
		}
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
              sum(a.upahkerja) as upah,sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,
              sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma   
              from ".$dbname.".kebun_prestasi_vs_hk a 
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where unit = '".$unit."'  and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' and b.intiplasma like '%".$intiplasma."%'
              ".$where." ".$where2."
              group by a.tanggal, a.kodeorg";
    }	
$dzArr=array();
$kmrn=strtotime ('-1 day',strtotime ($date1));
$kmrn=date ('Y-m-d', $kmrn );
if($unit=='') // script copy-an dari kebun_laporanPanen.php
    {
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
		}
        $str2="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
               sum(a.upahkerja) as upah,sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,
               sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma 
               from ".$dbname.".kebun_prestasi_vs_hk a
               left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi 
			   left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
               where c.induk = '".$pt."'  and a.tanggal ='".$kmrn."' 
               and a.jurnal=1 and b.intiplasma like '%".$intiplasma."%' ".$where2."    
               group by a.tanggal,a.kodeorg";
    }
    else
    {
        $where='';
        if($unit != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.jurnal=1";
        }
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
		}
        $str2="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,
               sum(a.upahkerja) as upah,sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,
               sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma   
               from ".$dbname.".kebun_prestasi_vs_hk a 
			   left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
               where unit = '".$unit."'  and a.tanggal ='".$kmrn."' and b.intiplasma like '%".$intiplasma."%'   
               ".$where." ".$where2."
               group by a.tanggal, a.kodeorg";
    }
	$qKmrn=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
	$qKmrn->setFetchMode(PDO::FETCH_OBJ);
    while($rKmr=$qKmrn->fetch())
    {
        $dzArrk[$rKmr->kodeorg][$rKmr->tanggal.'j']=$rKmr->jjg;
    }

    // isi array
    $jumlahhari=count($tanggal);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
    if($numrows<1){
        $jukol=($jumlahhari*3)+5;
        echo $_SESSION['lang']['tidakditemukan'];
        exit;
    }else{
        while($bar=$res->fetch()){
            $dzArrk[$bar->kodeorg][$bar->tanggal.'j']=$bar->jjg;
            $dzArr[$bar->kodeorg][$bar->tanggal]=$bar->tanggal;
            $dzArr[$bar->kodeorg]['kodeorg']=$bar->kodeorg;
            $dzArr[$bar->kodeorg]['intiplasma']=$bar->intiplasma;
            $dzArr[$bar->kodeorg]['tahuntanam']=$bar->tahuntanam;
            $dzArr[$bar->kodeorg][$bar->tanggal.'j']=$bar->jjg;
            $dzArr[$bar->kodeorg][$bar->tanggal.'k']=$bar->berat;
            $dzArr[$bar->kodeorg][$bar->tanggal.'h']=$bar->hkpanenperhari;//jumlahhk
            $dzArr[$bar->kodeorg][$bar->tanggal.'l']=$bar->luas;
        }	
    } 
    if(!empty($dzArr)) { // list isi data on kodeorg
        foreach($dzArr as $c=>$key) { // list tanggal
            $sort_kodeorg[] = $key['kodeorg'];
            $sort_tahuntanam[] = $key['tahuntanam'];
        }
        array_multisort($sort_kodeorg, SORT_ASC, $sort_tahuntanam, SORT_ASC, $dzArr); // urut kodeorg, terus tahun tanam
    }
    
    $stream=$_SESSION['lang']['laporanpanen']." ".$pt." ".$unit." per ".$_SESSION['lang']['tanggal']." ".$tgl1." - ".$tgl2;
    $stream.='<table border=1 cellpading=1>';
    // header
    $stream.="<thead>
        <tr>
            <td bgcolor=#DEDEDE rowspan=2 align=center>No.</td>
            <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['afdeling']."</td>
            <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['kodeblok']."</td>
            <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['intiplasma']."</td>
            <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['luas']."</td>
            <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['tahuntanam']."</td>";    
    foreach($tanggal as $tang){
        $ting=explode('-',$tang);
        $qwe=date('D', strtotime($tang));
        $stream.="<td bgcolor=#DEDEDE colspan=4 align=center>";
        if($qwe=='Sun')$stream.="<font color=red>".$ting[2]."</font>"; else $stream.= $ting[2]; 
        $stream.="</td>";
    }
    $stream.="<td bgcolor=#DEDEDE colspan=4 align=center>Total</td><td bgcolor=#DEDEDE rowspan=2 align=center>BJR</td></tr><tr>";  
    foreach($tanggal as $tang){
              $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jjg']."</td>
                  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kg']."</td>
                  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['luas']."</td>
                  <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlahhk']."</td>";    
    }
    $stream.="<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jjg']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kg']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['luas']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlahhk']."</td></tr>  
        </thead>
	<tbody>";

    // content
    $no=0;
    foreach($dzArr as $arey){ // list isi data on kodeorg
        $no+=1;
        $stream.="<tr class='rowcontent'>
            <td align=center>".$no."</td>
            <td align=center>".substr($arey['kodeorg'],0,6)."</td>
            <td align=center>".$arey['kodeorg']."</td>
            <td align=center>".$arey['intiplasma']."</td>
            <td align=center>".number_format($luasblok[$arey['kodeorg']],2)."</td>
            <td align=center>".$arey['tahuntanam']."</td>";    
        $totalj=0;
        $totalk=0;
        $totalh=0;
        $totall=0;
        $totaltanpanol=0;
        $jumlahtanpanol=0;
        foreach($tanggal as $tang){ // list tanggal
            $qwe=date('D', strtotime($tang));
            $dbg="";
            $tglkmrn=strtotime ('-1 day',strtotime ($tang));
            $tglkmrn2=date ('Y-m-d', $tglkmrn );

            setIt($dzArrk[$arey['kodeorg']][$tglkmrn2.'j'],0);
			setIt($arey[$tang.'j'],0);
			setIt($arey[$tang.'k'],0);
			setIt($arey[$tang.'l'],0);
			setIt($arey[$tang.'h'],0);
			if(($dzArrk[$arey['kodeorg']][$tglkmrn2.'j']!=0)&&($arey[$tang.'j']!=0))
            {
                $dbg="bgcolor=red";
            }
            if($qwe=='Sun'){
                $stream.="<td align=right ".$dbg."><font color=red>".number_format($arey[$tang.'j'])."</font></td>";
                $stream.="<td align=right><font color=red>".number_format($arey[$tang.'k'],2)."</font></td>";    
                $stream.="<td align=right><font color=red>".number_format($arey[$tang.'l'],2)."</font></td>";    
                $stream.="<td align=right><font color=red>".number_format($arey[$tang.'h'],2)."</font></td>";    
            }else{
                $stream.="<td align=right ".$dbg.">".number_format($arey[$tang.'j'])."</td>";
                $stream.="<td align=right>".number_format($arey[$tang.'k'],2)."</td>";    
                $stream.="<td align=right>".number_format($arey[$tang.'l'],2)."</td>";    
                $stream.="<td align=right>".number_format($arey[$tang.'h'],2)."</td>";    
            }
            $stream.="</td>";
			setIt($total[$tang.'j'],0);
			setIt($total[$tang.'k'],0);
			setIt($total[$tang.'l'],0);
			setIt($total[$tang.'h'],0);
			setIt($totalj,0);
			setIt($totalk,0);
			setIt($totall,0);
			setIt($totalh,0);
            $total[$tang.'j']+=$arey[$tang.'j']; // tambahin total bawah
            $total[$tang.'k']+=$arey[$tang.'k']; // tambahin total bawah
            $total[$tang.'h']+=$arey[$tang.'h']; // tambahin total bawah
            $total[$tang.'l']+=$arey[$tang.'l']; // tambahin total bawah
            
            $totalj+=$arey[$tang.'j']; // tambahin total kanan
            $totalk+=$arey[$tang.'k']; // tambahin total kanan
            $totalh+=$arey[$tang.'h']; // tambahin total kanan
            $totall+=$arey[$tang.'l']; // tambahin total kanan
            
            if($arey[$tang.'j']>0){
                $totaltanpanol+=$arey[$tang.'j'];
                $jumlahtanpanol+=1;
            }
        }
        @$rataj=$totaltanpanol/$jumlahtanpanol;
        
         @$bjr=$totalk/$totalj;
        $stream.="<td align=right>".number_format($totalj)."</td>
            <td align=right>".number_format($totalk,2)."</td>
            <td align=right>".number_format($totall,2)."</td>
            <td align=right>".number_format($totalh,2)."</td><td align=right>".number_format($bjr,2)."</td></tr>";
    }
    
    // tampilin total
    $stream.="<tr class='rowcontent'>
        <td colspan=6 align=center>Total</td>";
    $totalj=0;
    $totalk=0;
    $totalh=0;
    $totall=0;
    foreach($tanggal as $tang){ // list tanggal
        
        $stream.="<td align=right>".number_format($total[$tang.'j'])."</td>";   
        $stream.="<td align=right>".number_format($total[$tang.'k'],2)."</td>";    
        $stream.="<td align=right>".number_format($total[$tang.'l'],2)."</td>";    
        $stream.="<td align=right>".number_format($total[$tang.'h'],2)."</td>";    
        $totalj+=$total[$tang.'j']; // tambahin total kanan
        $totalk+=$total[$tang.'k']; // tambahin total kanan
        $totall+=$total[$tang.'l']; // tambahin total kanan
        $totalh+=$total[$tang.'h']; // tambahin total kanan
    }
    $stream.="<td align=right>".number_format($totalj)."</td>
        <td align=right>".number_format($totalk,2)."</td>
        <td align=right>".number_format($totall,2)."</td>
        <td align=right>".number_format($totalh,2)."</td><td></td></tr>";
    $stream.="</tbody>
        <tfoot>
        </tfoot>";		 
                
    $stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	
    // exit("Error:$stream");
    
$tglSkrg=date("Ymd");
$nop_="LaporanPanenTanggal".$pt."_".$unit."_".$tgl1;
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