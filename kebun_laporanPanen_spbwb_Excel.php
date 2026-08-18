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
	
    // olah tanggal
    $tanggal1=explode('-',$tgl1);
    $tanggal2=explode('-',$tgl2);
    $date1=$tanggal1[2].'-'.$tanggal1[1].'-'.$tanggal1[0];
    $tanggalterakhir=date('t', strtotime($date1));
    
    // kamus blok
    $sdakar="select kodeorg,tahuntanam from ".$dbname.".setup_blok";
    $qdakar=$owlPDO->query($sdakar) or die(print " Gagal: ".PDOException::getMessage());
	$qdakar->setFetchMode(PDO::FETCH_ASSOC);
    while($rdakar=$qdakar->fetch())
    {
        $belok[$rdakar['kodeorg']]=$rdakar['tahuntanam'];
    }
    
    if($unit=='') // script copy-an dari kebun_laporanPanen.php
    {
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.blok,1,6) = '".$afdeling."'";
		}
        $str="select a.blok,a.tanggal,a.nospb,a.notiket,a.nokendaraan,a.jjg,a.kgwb,a.bjr,a.kgbjr,
			  if(b.intiplasma='I','Inti','Plasma') as intiplasma 
              from ".$dbname.".kebun_spb_vw a
              left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi 
			  left join ".$dbname.".setup_blok b on a.blok = b.kodeorg 
              where c.induk = '".$pt."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%' ".$where2."
              and a.posting=1 order by a.blok, a.tanggal";
    }
    else
    {
        $where='';
        if($unit != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.posting=1";
        }
		$where2='';
		if($afdeling!='')
		{
			$where2 = " and substr(a.blok,1,6) = '".$afdeling."'";
		}
        $str="select a.blok,a.tanggal,a.nospb,a.notiket,a.nokendaraan,a.jjg,a.kgwb,a.bjr,a.kgbjr,
			  if(b.intiplasma='I','Inti','Plasma') as intiplasma 
              from ".$dbname.".kebun_spb_vw a 
			  left join ".$dbname.".setup_blok b on a.blok = b.kodeorg 
              where blok like '".$unit."%'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%' ".$where2."
              ".$where." order by a.blok, a.tanggal";
    }	
//    exit('error: '.$str);
    $stream=$_SESSION['lang']['laporanpanen']." ".$pt." ".$unit." SPB vs WB ".$tgl1." - ".$tgl2;
    $stream.='<table border=1 cellpading=1>';
    // header
    $stream.="<thead>
                <tr>
            <td align=center>No.</td>
            <td align=center>".$_SESSION['lang']['afdeling']."</td>
            <td align=center>".$_SESSION['lang']['blok']."</td>
            <td align=center>".$_SESSION['lang']['intiplasma']."</td>
            <td align=center>".$_SESSION['lang']['tahuntanam']."</td>
            <td align=center>".$_SESSION['lang']['tanggal']."</td> 
            <td align=center>".$_SESSION['lang']['nospb']."</td>
            <td align=center>".$_SESSION['lang']['noTiket']."</td>
            <td align=center>".$_SESSION['lang']['kendaraan']."</td>
            <td align=center>".$_SESSION['lang']['jjg']."</td>
            <td align=center>"."KG ".$_SESSION['lang']['kebun']."</td>    
            <td align=center>".$_SESSION['lang']['kgwb']."</td>
            <td align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['aktual']."</td>
            <td align=center>".$_SESSION['lang']['bjr']." Kebun</td>
            <td align=center>%</td>
        </tr>
        </thead>
	<tbody>";

    // content
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
    $no=0;
    if($numrows<1){
        $jukol=15;
        echo"<tr class=rowcontent><td colspan=".$jukol.">".$_SESSION['lang']['tidakditemukan']."</td></tr>";
        exit;
    }else{
		$totalbarjjg=$totalbarkgbjr=$totalbarkgwb=0;
        while($bar=$res->fetch()){
        // content
        $no+=1;
        @$aktual=$bar->kgwb/$bar->jjg;
        $stream.="<tr class='rowcontent'>
            <td align=center>".$no."</td>
            <td align=left>".substr($bar->blok,0,6)."</td>
            <td align=center>".$bar->blok."</td>
            <td align=center>".$bar->intiplasma."</td>
            <td align=center>".$belok[$bar->blok]."</td>
            <td align=center>".$bar->tanggal."</td>
            <td align=center>".$bar->nospb."</td>";
            $notiket=$bar->notiket;
            if($notiket!='')
            $stream.="<td align=right>".$notiket."</td>";else{
                $stream.="<td bgcolor=red title='Belum Masuk PKS' align=right>".$notiket."</td>";
            }
            $stream.="<td align=center>".$bar->nokendaraan."</td>
            <td align=right>".$bar->jjg."</td>";
            $stream.="<td align=right>".number_format($bar->kgbjr,2)."</td>";
            $kgwb=$bar->kgwb;
            if($kgwb!=0){
                $stream.="<td align=right>".number_format($kgwb,2)."</td>";
                $beda=$kgwb-$bar->kgbjr;
                @$persen=($beda/$bar->kgbjr)*100;
//                $beda=abs($kgwb-$bar->kgbjr);
//                @$persen=100-(($beda/$kgwb)*100);
            }
            else{
                $stream.="<td bgcolor=red title='SPB Belum Diinput' align=right>".number_format($kgwb,2)."</td>";
                $persen=0;
            }
            $stream.="<td align=right>".number_format($aktual,2)."</td>
            <td align=right>".$bar->bjr."</td>";
            $stream.="<td align=right>".number_format($persen,2)."</td></tr>";
            $totalbarjjg+=$bar->jjg;
            $totalbarkgbjr+=$bar->kgbjr;
            $totalbarkgwb+=$bar->kgwb;
        }
        $stream.="<tr class='rowcontent'>
            <td align=center></td>
            <td align=left></td>
            <td align=center></td>
            <td align=center></td>
            <td align=center></td>
            <td align=center></td>
            <td align=center>Total</td>";
//            $notiket=$bar->notiket;
//            if($notiket!='')
            $stream.="<td align=right></td>";
//            else{
//                echo"<td bgcolor=red title='Belum Masuk PKS' align=right>".$notiket."</td>";
//            }
            $stream.="<td align=center></td>
            <td align=right>".number_format($totalbarjjg)."</td>";
            $stream.= "<td align=right>".number_format($totalbarkgbjr,2)."</td>";
//            $kgwb=$bar->kgwb;
//            if($kgwb!=0){
                $stream.="<td align=right>".number_format($totalbarkgwb,2)."</td>";
                $beda=$totalbarkgwb-$totalbarkgbjr;
                @$persen=($beda/$totalbarkgbjr)*100;
//            }
//            else{
//                echo"<td bgcolor=red title='SPB Belum Diinput' align=right>".number_format($kgwb,2)."</td>";
//                $persen=0;
//            }
        @$aktual=$totalbarkgwb/$totalbarjjg;
            $stream.="<td align=right>".number_format($aktual,2)."</td>
            <td align=right></td>";
            $stream.="<td align=right>".number_format($persen,2)."</td>";
            $stream.= "</tr>";          
    } 
    $stream.="</tbody>
        <tfoot>
        </tfoot>";		 
                
    $stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
	
$tglSkrg=date("Ymd");
$nop_="LaporanPanenSPBWB".$pt."_".$unit."_".$tgl1;
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