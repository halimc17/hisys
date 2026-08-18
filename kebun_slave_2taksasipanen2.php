<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
    
$proses = checkPostGet('proses','');
$kebun = checkPostGet('kebun2','');
$afdeling = checkPostGet('afdeling2','');
$periode = checkPostGet('periode2','');

if($proses=='preview'||$proses=='excel'){
    if($periode==''||$kebun==''){
        exit("Error: All field required");
    }
    
    $brd=0;
	$bgcoloraja="";
    if($proses=='excel'){
        $brd=1;
        $bgcoloraja="bgcolor=#DEDEDE align=center";
    }
    #ambil data timbangan
    $sPabrik="select nospb,kodeorg, (jumlahtandan1+jumlahtandan2+jumlahtandan3) as jjgpabrik,beratbersih,
        notransaksi,left(tanggal,10) as tanggal, substr(nospb,9,6) as afdeling from ".$dbname.".pabrik_timbangan 
        where left(tanggal,10)!='' and kodeorg = '".$kebun."' and nospb!='' and substr(nospb,9,6) like '".$afdeling."%'
        and left(tanggal,7) like '".$periode."%' order by substr(nospb,9,6)";  
	$respabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
	$respabrik->setFetchMode(PDO::FETCH_OBJ);
    while($bar0=$respabrik->fetch()){
        $keyAfd[$bar0->afdeling]=$bar0->afdeling;
        $keyTgl[$bar0->tanggal]=$bar0->tanggal;
		setIt($dzArr[$bar0->afdeling][$bar0->tanggal]['p_kg'],0);
		setIt($dzArr[$kebun][$bar0->tanggal]['p_kg'],0);
        $dzArr[$bar0->afdeling][$bar0->tanggal]['p_kg']+=$bar0->beratbersih;
        $dzArr[$kebun][$bar0->tanggal]['p_kg']+=$bar0->beratbersih;
    }
    #ambil data timbangan kemarin
    
    #ambil data taksasi
    $sTaksasi="select afdeling, tanggal, hasisa, haesok, jmlhpokok, persenbuahmatang, jjgmasak, jjgoutput, hkdigunakan, bjr, (bjr*jjgmasak) as kg from ".$dbname.".kebun_taksasi 
        where afdeling like '".$kebun."%' and afdeling like '%".$afdeling."%' and tanggal like '".$periode."%'
        ";    
	$restaksasi=$owlPDO->query($sTaksasi) or die(print " Gagal: ".PDOException::getMessage());
	$restaksasi->setFetchMode(PDO::FETCH_OBJ);
    while($bar1=$restaksasi->fetch()){
        $keyAfd[$bar1->afdeling]=$bar1->afdeling;
        $keyTgl[$bar1->tanggal]=$bar1->tanggal;

        setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['hkpanen'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['counter'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['hasisa'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['haesok'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['jmlhpokok'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['pbm'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['jjgmasak'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['hkdigunakan'],0);
		setIt($dzArr[$bar1->afdeling][$bar1->tanggal]['kg'],0);
		$dzArr[$bar1->afdeling][$bar1->tanggal]['counter']+=1;
        $dzArr[$bar1->afdeling][$bar1->tanggal]['afdeling']=$bar1->afdeling;
        $dzArr[$bar1->afdeling][$bar1->tanggal]['hasisa']+=$bar1->hasisa;
        $dzArr[$bar1->afdeling][$bar1->tanggal]['haesok']+=$bar1->haesok;
        $dzArr[$bar1->afdeling][$bar1->tanggal]['jmlhpokok']+=$bar1->jmlhpokok;
        $dzArr[$bar1->afdeling][$bar1->tanggal]['pbm']+=$bar1->persenbuahmatang;
        @$dzArr[$bar1->afdeling][$bar1->tanggal]['persenbuahmatang']=$dzArr[$bar1->afdeling][$bar1->tanggal]['pbm']/$dzArr[$bar1->afdeling][$bar1->tanggal]['counter'];
        $dzArr[$bar1->afdeling][$bar1->tanggal]['jjgmasak']+=$bar1->jjgmasak;
        $dzArr[$bar1->afdeling][$bar1->tanggal]['hkdigunakan']+=$bar1->hkdigunakan;
        $dzArr[$bar1->afdeling][$bar1->tanggal]['kg']+=$bar1->kg;
        @$dzArr[$bar1->afdeling][$bar1->tanggal]['bjr']=$dzArr[$bar1->afdeling][$bar1->tanggal]['kg']/$dzArr[$bar1->afdeling][$bar1->tanggal]['jjgmasak'];
        
        @$kurangdarienam=($bar1->haesok+$bar1->hasisa)/$bar1->hkdigunakan;
        if($kurangdarienam <= 6){
            $bisapanen=$bar1->hkdigunakan;
        }else{
            $bisapanen=ceil(($bar1->haesok+$bar1->hasisa)/6);
        }
        
		setIt($dzArr[$kebun][$bar1->tanggal]['hkpanen'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['counter'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['hasisa'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['haesok'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['jmlhpokok'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['pbm'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['jjgmasak'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['hkdigunakan'],0);
		setIt($dzArr[$kebun][$bar1->tanggal]['kg'],0);
		$dzArr[$kebun][$bar1->tanggal]['hkpanen']+=$bisapanen;    
        $dzArr[$bar1->afdeling][$bar1->tanggal]['hkpanen']+=$bisapanen;    
        $dzArr[$kebun][$bar1->tanggal]['counter']+=1;
        $dzArr[$kebun][$bar1->tanggal]['afdeling']=$kebun;
        $dzArr[$kebun][$bar1->tanggal]['hasisa']+=$bar1->hasisa;
        $dzArr[$kebun][$bar1->tanggal]['haesok']+=$bar1->haesok;
        $dzArr[$kebun][$bar1->tanggal]['jmlhpokok']+=$bar1->jmlhpokok;
        $dzArr[$kebun][$bar1->tanggal]['pbm']+=$bar1->persenbuahmatang;
        @$dzArr[$kebun][$bar1->tanggal]['persenbuahmatang']=$dzArr[$kebun][$bar1->tanggal]['pbm']/$dzArr[$kebun][$bar1->tanggal]['counter'];
        $dzArr[$kebun][$bar1->tanggal]['jjgmasak']+=$bar1->jjgmasak;
        $dzArr[$kebun][$bar1->tanggal]['hkdigunakan']+=$bar1->hkdigunakan;
        $dzArr[$kebun][$bar1->tanggal]['kg']+=$bar1->kg;
        @$dzArr[$kebun][$bar1->tanggal]['bjr']=$dzArr[$kebun][$bar1->tanggal]['kg']/$dzArr[$kebun][$bar1->tanggal]['jjgmasak'];
    }
    #ambil data taksasi kemarin

    sort($keyTgl);
    sort($keyAfd);
	
	$tab="";
    if($proses!='excel'){
//        $tab.="
//        <table width=100% cellspacing=1 border=".$brd." >
//        <tr>
//            <td align=left><button onclick=pindahtanggal('".$kebun."','".$afd."','".$esok."') class=mybutton name=preview id=preview><- Esok/Tomorrow (".$esok.")</button></td>
//            <td>&nbsp;</td>
//            <td align=right><button onclick=pindahtanggal('".$kebun."','".$afd."','".$kemarin."') class=mybutton name=preview id=preview>(".$kemarin.") Kemarin/Yesterday -></button></td>
//        </tr>
//        </table>    
//        ";
    }else{
        $tab.= $_SESSION['lang']['rencanapanen']."<br>".$_SESSION['lang']['unitkerja'].": ".$kebun." ".$afdeling." ".$periode." ";
    }
    $tab.="
    <table cellspacing=1 cellpadding=5 border=".$brd." class=sortable>
    <thead>
    <tr>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['tanggal']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['kebun']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['afdeling']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['hasisa']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['haesok']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jumlahha']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jmlhpokok']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['persenbuahmatang']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jjgmasak']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jjgoutput']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jhk']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['bjr']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['taksasi']." (kg)</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['realisasi']." (kg)</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['varian']."</th>
    </tr></thead><tbody>";
    
    if(!empty($keyTgl))foreach($keyTgl as $tgl){
		setIt($dzArr[$kebun][$tgl]['hkdigunakan'],0);
		setIt($dzArr[$kebun][$tgl]['hasisa'],0);
		setIt($dzArr[$kebun][$tgl]['haesok'],0);
		setIt($dzArr[$kebun][$tgl]['jmlhpokok'],0);
		setIt($dzArr[$kebun][$tgl]['jjgmasak'],0);
		setIt($dzArr[$kebun][$tgl]['hkpanen'],0);
		setIt($dzArr[$kebun][$tgl]['bjr'],0);
		setIt($dzArr[$kebun][$tgl]['p_kg'],0);
		setIt($dzArr[$kebun][$tgl]['kg'],0);
        $jumlahha=$dzArr[$kebun][$tgl]['hasisa']+$dzArr[$kebun][$tgl]['haesok'];
		@$pbm=$dzArr[$kebun][$tgl]['jjgmasak']*100/$dzArr[$kebun][$tgl]['jmlhpokok'];
        @$varian=100-($dzArr[$kebun][$tgl]['p_kg']-$dzArr[$kebun][$tgl]['kg'])/$dzArr[$kebun][$tgl]['p_kg']*100;
        @$varian_k=100-($dzArr_k[$kebun][$tgl]['p_kg']-$dzArr_k[$kebun][$tgl]['kg'])/$dzArr_k[$kebun][$tgl]['p_kg']*100;
        if($dzArr[$kebun][$tgl]['kg']==0)$varian=0;
        @$jjgoutput=$dzArr[$kebun][$tgl]['jjgmasak']/$dzArr[$kebun][$tgl]['hkdigunakan'];
        $tab.="<tr class=rowcontent style=background:#d4d4d4>
        <td ".$bgcoloraja.">".$tgl."</td>
        <td ".$bgcoloraja."></td>
        <td ".$bgcoloraja."></td>
        <td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['hasisa'],2)."</td>
        <td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['haesok'],2)."</td>
        <td ".$bgcoloraja." align=right>".number_format($jumlahha,2)."</td>
        <td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['jmlhpokok'])."</td>
        <td ".$bgcoloraja." align=right>".number_format(fixnan($pbm),2)."</td>
        <td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['jjgmasak'])."</td>
        <td ".$bgcoloraja." align=right>".number_format(fixnan($jjgoutput))."</td>
        <td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['hkdigunakan'])."</td>";
        //$tab.="<td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['hkpanen'])."</td>";
        $tab.="<td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['bjr'],2)."</td>
        <td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['kg'])."</td>
        <td ".$bgcoloraja." align=right>".number_format($dzArr[$kebun][$tgl]['p_kg'])."</td>
        <td ".$bgcoloraja." align=right>".number_format(fixnan($varian),2)."</td>
        </tr>";                        

        if(!empty($keyAfd))foreach($keyAfd as $afd){
			setIt($dzArr[$afd][$tgl]['hkdigunakan'],0);
			setIt($dzArr[$afd][$tgl]['hasisa'],0);
			setIt($dzArr[$afd][$tgl]['haesok'],0);
			setIt($dzArr[$afd][$tgl]['jmlhpokok'],0);
			setIt($dzArr[$afd][$tgl]['jjgmasak'],0);
			setIt($dzArr[$afd][$tgl]['hkpanen'],0);
			setIt($dzArr[$afd][$tgl]['bjr'],0);
			setIt($dzArr[$afd][$tgl]['p_kg'],0);
			setIt($dzArr[$afd][$tgl]['kg'],0);
			$jumlahha=$dzArr[$afd][$tgl]['hasisa']+$dzArr[$afd][$tgl]['haesok'];
			@$pbm=$dzArr[$afd][$tgl]['jjgmasak']*100/$dzArr[$afd][$tgl]['jmlhpokok'];        
			@$varian=100-($dzArr[$afd][$tgl]['p_kg']-$dzArr[$afd][$tgl]['kg'])/$dzArr[$afd][$tgl]['p_kg']*100;
			@$varian_k=100-($dzArr_k[$afd][$tgl]['p_kg']-$dzArr_k[$afd][$tgl]['kg'])/$dzArr_k[$afd][$tgl]['p_kg']*100;
			if($dzArr[$afd][$tgl]['kg']==0)$varian=0;
			@$jjgoutput=$dzArr[$afd][$tgl]['jjgmasak']/$dzArr[$afd][$tgl]['hkdigunakan'];
			$tab.="<tr class=rowcontent>
			<td></td>
			<td>".$kebun."</td>
			<td>".$afd."</td>
			<td align=right>".number_format($dzArr[$afd][$tgl]['hasisa'],2)."</td>
			<td align=right>".number_format($dzArr[$afd][$tgl]['haesok'],2)."</td>
			<td align=right>".number_format($jumlahha,2)."</td>
			<td align=right>".number_format($dzArr[$afd][$tgl]['jmlhpokok'])."</td>
			<td align=right>".number_format(fixnan($pbm),2)."</td>
			<td align=right>".number_format($dzArr[$afd][$tgl]['jjgmasak'])."</td>
			<td align=right>".number_format(fixnan($jjgoutput))."</td>
			<td align=right>".number_format($dzArr[$afd][$tgl]['hkdigunakan'])."</td>";
			//$tab.="<td align=right>".number_format($dzArr[$afd][$tgl]['hkpanen'])."</td>";
			$tab.="<td align=right>".number_format($dzArr[$afd][$tgl]['bjr'],2)."</td>
			<td align=right>".number_format($dzArr[$afd][$tgl]['kg'])."</td>
			<td align=right>".number_format($dzArr[$afd][$tgl]['p_kg'])."</td>
			<td align=right>".number_format(fixnan($varian),2)."</td>
			</tr>";
        }
    }
        $tab.="</tbody></table>";


}	
switch($proses)
{
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="taksasi_".$kebun."_".$afdeling."_".$periode;
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
            fclose($handle);
        }
    break;
    case'getAfdeling0':
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sPrd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
               where induk = '".$kebun."' and tipe='afdeling' order by namaorganisasi asc";
        $qPrd=$owlPDO->query($sPrd) or die(print " Gagal: ".PDOException::getMessage());
		$qPrd->setFetchMode(PDO::FETCH_ASSOC);
		while($rPrd=$qPrd->fetch()){
            $optAfd.="<option value=".$rPrd['kodeorganisasi'].">".$rPrd['namaorganisasi']."</option>";
        }
        
        // taken from kebun_slave_taksasi... ambil karyawan selain bhl
        $sorg2="select distinct karyawanid,namakaryawan from ".$dbname.".datakaryawan 
                where lokasitugas='".$kebun."' and tipekaryawan!='4' order by namakaryawan asc";
		$qorg2=$owlPDO->query($sorg2) or die(print " Gagal: ".PDOException::getMessage());
		$qorg2->setFetchMode(PDO::FETCH_ASSOC);
        while($rorg2=$qorg2->fetch()){
            if($param['mandor']!=''){
                $optafd2.="<option value='".$rorg2['karyawanid']."' ".($param['mandor']==$rorg2['karyawanid']?"selected":"").">".$rorg2['namakaryawan']."</option>";
            }
            else{
                $optafd2.="<option value='".$rorg2['karyawanid']."'>".$rorg2['namakaryawan']."</option>";
            }
        }
        
        
        echo $optAfd."####".$optafd2;
    break;

    case'getAfdeling':
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sPrd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
               where induk = '".$kebun."' and tipe='afdeling' order by namaorganisasi asc";
		$qPrd=$owlPDO->query($sPrd) or die(print " Gagal: ".PDOException::getMessage());
		$qPrd->setFetchMode(PDO::FETCH_ASSOC);
        while($rPrd=$qPrd->fetch()){
            $optAfd.="<option value=".$rPrd['kodeorganisasi'].">".$rPrd['namaorganisasi']."</option>";
        }
        echo $optAfd;
    break;
    
    default:
    break;
}
?>