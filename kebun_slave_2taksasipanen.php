<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kebun = checkPostGet('kebun','');
$afdeling = checkPostGet('afdeling','');
$tanggal = checkPostGet('tanggal','');

function putertanggal($tgl){
    $qwe=explode("-",$tgl);
    return $qwe[2]."-".$qwe[1]."-".$qwe[0];
}

if($proses=='preview'||$proses=='excel'){
    if($tanggal==''||$kebun==''){
        exit("Error: All field required");
    }
    
    $tanggal=putertanggal($tanggal);

    $esok=putertanggal(date('Y-m-d', strtotime('+1 day', strtotime($tanggal))));
    $kemarin=putertanggal(date('Y-m-d', strtotime('-1 day', strtotime($tanggal))));

    $tanggalkemarin=putertanggal($kemarin);

    $brd=0;
	$bgcoloraja="align=center width=70px";
    if($proses=='excel'){
        $brd=1;
        $bgcoloraja="bgcolor=#DEDEDE align=center";
    }
    #ambil data timbangan
    $sPabrik="select nospb,kodeorg, (jumlahtandan1+jumlahtandan2+jumlahtandan3) as jjgpabrik,beratbersih,
        notransaksi,left(tanggal,10) as tanggal, substr(nospb,9,6) as afdeling from ".$dbname.".pabrik_timbangan 
        where left(tanggal,10)!='' and kodeorg = '".$kebun."' and nospb!='' and substr(nospb,9,6) like '".$afdeling."%'
        and left(tanggal,10) like '".$tanggal."%' order by substr(nospb,9,6)";  
    $respabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
	$respabrik->setFetchMode(PDO::FETCH_OBJ);
    while($bar0=$respabrik->fetch()){
        $keyAfd[$bar0->afdeling]=$bar0->afdeling;
		setIt($dzArr[$bar0->afdeling]['p_kg'],0);
		setIt($dzArr[$kebun]['p_kg'],0);
        $dzArr[$bar0->afdeling]['p_kg']+=$bar0->beratbersih;
		$dzArr[$kebun]['p_kg']+=$bar0->beratbersih;
    }
    #ambil data timbangan kemarin
    
    #ambil data taksasi
    $sTaksasi="select afdeling, tanggal, hasisa, haesok, jmlhpokok, persenbuahmatang, jjgmasak, jjgoutput, hkdigunakan, bjr, (bjr*jjgmasak) as kg from ".$dbname.".kebun_taksasi 
        where afdeling like '".$kebun."%' and afdeling like '%".$afdeling."%' and tanggal = '".$tanggal."'
        ";    
    $restaksasi=$owlPDO->query($sTaksasi) or die(print " Gagal: ".PDOException::getMessage());
	$restaksasi->setFetchMode(PDO::FETCH_OBJ);
    while($bar1=$restaksasi->fetch()){
		setIt($dzArr[$bar1->afdeling]['hkpanen'],0);
		setIt($dzArr[$bar1->afdeling]['counter'],0);
		setIt($dzArr[$bar1->afdeling]['hasisa'],0);
		setIt($dzArr[$bar1->afdeling]['haesok'],0);
		setIt($dzArr[$bar1->afdeling]['jmlhpokok'],0);
		setIt($dzArr[$bar1->afdeling]['pbm'],0);
		setIt($dzArr[$bar1->afdeling]['jjgmasak'],0);
		setIt($dzArr[$bar1->afdeling]['hkdigunakan'],0);
		setIt($dzArr[$bar1->afdeling]['kg'],0);
		
        $keyAfd[$bar1->afdeling]=$bar1->afdeling;
        $dzArr[$bar1->afdeling]['counter']+=1;
        $dzArr[$bar1->afdeling]['afdeling']=$bar1->afdeling;
        $dzArr[$bar1->afdeling]['hasisa']+=$bar1->hasisa;
        $dzArr[$bar1->afdeling]['haesok']+=$bar1->haesok;
        $dzArr[$bar1->afdeling]['jmlhpokok']+=$bar1->jmlhpokok;
        $dzArr[$bar1->afdeling]['pbm']+=$bar1->persenbuahmatang;
        @$dzArr[$bar1->afdeling]['persenbuahmatang']=$dzArr[$bar1->afdeling]['pbm']/$dzArr[$bar1->afdeling]['counter'];
        $dzArr[$bar1->afdeling]['jjgmasak']+=$bar1->jjgmasak;
        $dzArr[$bar1->afdeling]['hkdigunakan']+=$bar1->hkdigunakan;
        $dzArr[$bar1->afdeling]['kg']+=$bar1->kg;
        @$dzArr[$bar1->afdeling]['bjr']=$dzArr[$bar1->afdeling]['kg']/$dzArr[$bar1->afdeling]['jjgmasak'];
        
        @$kurangdarienam=($bar1->haesok+$bar1->hasisa)/$bar1->hkdigunakan;
        if($kurangdarienam <= 6){
            $bisapanen=$bar1->hkdigunakan;
        }else{
            $bisapanen=ceil(($bar1->haesok+$bar1->hasisa)/6);
        }
        $dzArr[$bar1->afdeling]['hkpanen']+=$bisapanen;    
        
		setIt($dzArr[$kebun]['counter'],0);
		setIt($dzArr[$kebun]['hasisa'],0);
		setIt($dzArr[$kebun]['haesok'],0);
		setIt($dzArr[$kebun]['jmlhpokok'],0);
		setIt($dzArr[$kebun]['pbm'],0);
		setIt($dzArr[$kebun]['jjgmasak'],0);
		setIt($dzArr[$kebun]['hkdigunakan'],0);
		setIt($dzArr[$kebun]['hkpanen'],0);
		setIt($dzArr[$kebun]['kg'],0);
        $dzArr[$kebun]['counter']+=1;
        $dzArr[$kebun]['afdeling']=$kebun;
        $dzArr[$kebun]['hasisa']+=$bar1->hasisa;
        $dzArr[$kebun]['haesok']+=$bar1->haesok;
        $dzArr[$kebun]['jmlhpokok']+=$bar1->jmlhpokok;
        $dzArr[$kebun]['pbm']+=$bar1->persenbuahmatang;
        @$dzArr[$kebun]['persenbuahmatang']=$dzArr[$kebun]['pbm']/$dzArr[$kebun]['counter'];
        $dzArr[$kebun]['jjgmasak']+=$bar1->jjgmasak;
//        $dzArr[$kebun]['jjgoutput']+=$bar1->jjgoutput;
        $dzArr[$kebun]['hkdigunakan']+=$bar1->hkdigunakan;
        $dzArr[$kebun]['hkpanen']+=$bisapanen;
        $dzArr[$kebun]['kg']+=$bar1->kg;
        @$dzArr[$kebun]['bjr']=$dzArr[$kebun]['kg']/$dzArr[$kebun]['jjgmasak'];
    }
    #ambil data taksasi kemarin

    #ambil data jjg terpanen
        // if ($afdeling != '') {
        //     $where = "AND divisi = '".$afdeling."'";
        // } else {
        //     $where = "AND divisi in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE induk = '".$kebun."')";
        // }

        foreach ($keyAfd as $key => $val) {
            $str = "SELECT divisi, tanggal, sum(tenagakerja) as jjg
                    FROM ".$dbname.".kebun_rekappnn
                    WHERE tanggal = '".$tanggal."'
                    AND divisi = '".$key."'
                    GROUP BY divisi";
            $res = fetchdata($str);

            foreach ($res as $key2 => $val2) {
                $dzArr[$key]['terpanen'] = $val2['jjg'];
                @$dzArr[$kebun]['terpanen'] += $dzArr[$key]['terpanen'];
            }
        }

    #

	$tab="";
    if($proses!='excel'){
        $tab.="
        <table width=100% cellspacing=1 border=".$brd." >
        <tr>
            <td align=left><button onclick=pindahtanggal('".$kebun."','".$afdeling."','".$kemarin."') class=mybutton name=preview id=preview><= (".$kemarin.") Kemarin / Yesterday</button></td>
			
			<td align=right><button onclick=pindahtanggal('".$kebun."','".$afdeling."','".$esok."') class=mybutton name=preview id=preview>Esok / Tomorrow (".$esok.") =></button></td>
            
        </tr>
        </table>    
        ";
    }else{
        $tab.= $_SESSION['lang']['rencanapanen']."<br>".$_SESSION['lang']['unitkerja'].": ".$kebun." ".$afdeling." ".putertanggal($tanggal)." ";
    }
	
    $tab.="
    <table width=100% cellspacing=1  cellpadding=5 border=".$brd." class=sortable>
    <thead>
    <tr class=rowheader>
        <th ".$bgcoloraja.">".$_SESSION['lang']['kebun']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['afdeling']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['hasisa']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['haesok']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jumlahha']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jmlhpokok']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['persenbuahmatang']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jjgmasak']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jjgoutput']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jjg']." Terpanen</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['jhk']." ".$_SESSION['lang']['hasil']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['lapPersonel']."  ".$_SESSION['lang']['digunakan']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['bjr']."</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['taksasi']." (kg)</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['realisasi']." (kg)</th>
        <th ".$bgcoloraja.">".$_SESSION['lang']['varian']."</th>
    </tr>";
        @$jumlahha=$dzArr[$kebun]['hasisa']+$dzArr[$kebun]['haesok'];
        
        @$pbm=$dzArr[$kebun]['jjgmasak']*100/$dzArr[$kebun]['jmlhpokok'];
        @$varian=100-($dzArr[$kebun]['p_kg']-$dzArr[$kebun]['kg'])/$dzArr[$kebun]['p_kg']*100;
        @$varian_k=100-($dzArr_k[$kebun]['p_kg']-$dzArr_k[$kebun]['kg'])/$dzArr_k[$kebun]['p_kg']*100;
        if(@$dzArr[$kebun]['kg']==0)$varian=0;
        @$jjgoutput=$dzArr[$kebun]['jjgmasak']/$dzArr[$kebun]['hkdigunakan'];
      $tab.="<tr class=rowcontent>
        <th align=center colspan=2>Total ".$kebun."</th>
        
        <th align=right>".number_format(@$dzArr[$kebun]['hasisa'],2)."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['haesok'],2)."</th>
        <th align=right>".number_format(@$jumlahha,2)."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['jmlhpokok'])."</th>
        <th align=right>".number_format(@$pbm,2)."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['jjgmasak'])."</th>
        <th align=right>".number_format(@$jjgoutput)."</th>
        <th align=right>".number_format($dzArr[$kebun]['terpanen'])."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['hkdigunakan'])."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['hkpanen'])."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['bjr'],2)."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['kg'])."</th>
        <th align=right>".number_format(@$dzArr[$kebun]['p_kg'])."</th>
        <th align=right>".number_format(@$varian,2)."</th>
      </tr>";                        
    $tab.="</thead>
    <tbody>";
//        <td ".$bgcoloraja.">&nbsp;</td>
//        <td ".$bgcoloraja.">".$_SESSION['lang']['taksasi']." (kg)</td>
//        <td ".$bgcoloraja.">".$_SESSION['lang']['realisasi']." (kg)</td>
//        <td ".$bgcoloraja.">".$_SESSION['lang']['varian']."</td>
    
    if(!empty($keyAfd))foreach($keyAfd as $afd){
        @$jumlahha=$dzArr[$afd]['hasisa']+$dzArr[$afd]['haesok'];
        
        @$pbm=$dzArr[$afd]['jjgmasak']*100/$dzArr[$afd]['jmlhpokok'];        
        @$varian=100-($dzArr[$afd]['p_kg']-$dzArr[$afd]['kg'])/$dzArr[$afd]['p_kg']*100;
        @$varian_k=100-($dzArr_k[$afd]['p_kg']-$dzArr_k[$afd]['kg'])/$dzArr_k[$afd]['p_kg']*100;
        if(@$dzArr[$afd]['kg']==0)$varian=0;
        @$jjgoutput=$dzArr[$afd]['jjgmasak']/$dzArr[$afd]['hkdigunakan'];
      $tab.="<tr class=rowcontent>
        <td>".$kebun."</td>
        <td>".$afd."</td>
        <td align=right>".number_format(@$dzArr[$afd]['hasisa'],2)."</td>
        <td align=right>".number_format(@$dzArr[$afd]['haesok'],2)."</td>
        <td align=right>".number_format(@$jumlahha,2)."</td>
        <td align=right>".number_format(@$dzArr[$afd]['jmlhpokok'])."</td>
        <td align=right>".number_format(@$pbm,2)."</td>
        <td align=right>".number_format(@$dzArr[$afd]['jjgmasak'])."</td>
        <td align=right>".number_format(@$jjgoutput)."</td>
        <td align=right>".number_format($dzArr[$afd]['terpanen'])."</td>
        <td align=right>".number_format(@$dzArr[$afd]['hkdigunakan'])."</td>
        <td align=right>".number_format(@$dzArr[$afd]['hkpanen'])."</td>
        <td align=right>".number_format(@$dzArr[$afd]['bjr'],2)."</td>
        <td align=right>".number_format(@$dzArr[$afd]['kg'])."</td>
        <td align=right>".number_format(@$dzArr[$afd]['p_kg'])."</td>
        <td align=right>".number_format(@$varian,2)."</td>
      </tr>";                        
//        <td align=right>&nbsp;</td>
//        <td align=right>".number_format($dzArr_k[$afd]['kg'],2)."</td>
//        <td align=right>".number_format($dzArr_k[$afd]['p_kg'],2)."</td>
//        <td align=right>".number_format($varian_k,2)."</td>
    }
    $tab.="</tbody></table></td></tr></tbody><table>";

}	
switch($proses)
{
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="taksasi_".$kebun."_".$afdeling."_".$tanggal;
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