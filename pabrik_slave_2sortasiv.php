<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');



//$tgl1=tanggalsystemn($_POST['tgl1']);

/*catatan nanti buat jelasin selisihnya
 * jika dalam 1 hari ada 10 tiket, dan yang di sortasi hanya 5 / kurang dari jumlah tiketnya
 * pembaginya karena kg pembagi dibagi ke netto total tiket, bukan netto tiket yang di sortasi saja
 * -
 * jika ada lebih dari 1 tiket dan brondolannya yang tiket pertama 10%, tiket ke 2 >12.5, maka
 * di sini saya ambil rata2nya 10+12.5/2 sehingga menjadi 11.25 yang artinya akan menjadi terkena penalty
*/
$proses=checkPostGet('proses','');
$kdorg=checkPostGet('kdorg','');
$sup=checkPostGet('sup','');

$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

$optnmor=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjnvhc=makeOption($dbname, 'vhc_5jenisvhc','jenisvhc,namajenisvhc');
$optnmbar=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$optnamacostumer=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier');
$optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk');



if(($proses=='preview')or($proses=='excel')or($proses=='pdf'))
{
    
    if($kdorg=='')
    {
        echo"Warning: Unit tidak boleh kosong"; 
        exit;
    }
    if($sup=='')
    {
        echo"Warning: Supplier tidak boleh kosong"; 
        exit;
    }
    
    if(($tgl1=='')or($tgl2==''))
    {
        echo"Warning: Tanggal tidak boleh kosong"; 
        exit;
    }

    else if($tgl1>$tgl2)
    {
        echo"Warning: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
        exit;
    }
	
}









##############################
############PREPARE###########
#############DATA#############
##############################


#bentuk range tanggal
$rangetanggal = rangeTanggal($tgl1, $tgl2);

#bentuk netto , sumber pabrik_timbangan
/*$iTim="select tanggal,sum(beratbersih) as netto,sum(jjg) as jjg,sum(beratbersih)/sum(jjg) as bjr,count(notiket) as truk "
        . " from ".$dbname.".pabrik_timbangan_vw where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."' group by tanggal ";*/
$iTim="select tanggal,sum(beratbersih) as netto,sum(jjg) as jjg,sum(beratbersih)/sum(jjg) as bjr "
        . " from ".$dbname.".pabrik_timbangan_vw where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."' group by tanggal ";
$nTim=$owlPDO->query($iTim) or die(print " Gagal: ".PDOException::getMessage());
$nTim->setFetchMode(PDO::FETCH_ASSOC);
while($dTim=$nTim->fetch())
{
    $netto[$dTim['tanggal']]=  $dTim['netto'];
    $jjg[$dTim['tanggal']]=$dTim['jjg'];
    $bjr[$dTim['tanggal']]=$dTim['bjr'];
    
}

##bentuk jumlah yang disortasi //perbedaan dengan query pertama adalah where kgpotsortasi>0
##untuk mengsortir nomor tiket yang disortasi. 
##karena dari beberapa tiket persupplier belum tentu semua di sortasi
$iSortim="select tanggal,count(notiket) as truk,sum(beratbersih) as netto "
        . " from ".$dbname.".pabrik_timbangan_vw where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."'"
        . " and kgpotsortasi>0 group by tanggal ";
$nSortim=$owlPDO->query($iSortim) or die(print " Gagal: ".PDOException::getMessage());
$nSortim->setFetchMode(PDO::FETCH_ASSOC);
while($dSortim=$nSortim->fetch())
{
    $truk[$dSortim['tanggal']]=$dSortim['truk'];
    $sample[$dSortim['tanggal']]=$dSortim['truk']*100;
    $nettosor[$dSortim['tanggal']]=$dSortim['netto'];
}




#bentuk sortasinya , sumber : pabrik_sortasi_vw
$iSor="select tanggal,kodefraksi,sum(jumlah) as jumlah from ".$dbname.".pabrik_sortasi_vw "
        . " where millcode='".$kdorg."' and kodecustomer='".$sup."' "
        . " and kodebarang='40000003' and tanggal between '".$tgl1."' and '".$tgl2."' "
        . " group by tanggal,kodefraksi ";
$nSor=$owlPDO->query($iSor) or die(print " Gagal: ".PDOException::getMessage());
$nSor->setFetchMode(PDO::FETCH_ASSOC);
while($dSor=$nSor->fetch()){
    $jumsor[$dSor['tanggal']][$dSor['kodefraksi']]=$dSor['jumlah'];
}



##buat koefisien
##jika SMA = 12.5, CKS=7

if($optPt[$kdorg]=='CKS')
{
    $koef=7;
}
else if($optPt[$kdorg]=='SMA')
{
    $koef=12.5;
}






if($proses=='excel')
{
    $stream="<table cellspacing='1' border='1' class='sortable'>";
}
else 
{
    $stream.="<table cellspacing='1' border='0' class='sortable' width=1400px>";
}
$stream.="<thead class=rowheader>
        <tr>
            <td align=center rowspan=4>".$_SESSION['lang']['tanggal']."</td>
            <td align=center colspan=3 rowspan=2>".$_SESSION['lang']['jumlahditerima']." (".$_SESSION['lang']['tbs'].")</td>
            <td align=center rowspan=2>Total<br>(Truck)</td>
            <td align=center rowspan=4>Netto<br>Grading</td>
            <td align=center rowspan=4>Grading<br>Sample</td>
            <td align=center colspan=4 rowspan=2>Unripe</td>
            <td align=center colspan=4 rowspan=2>Over Ripe</td>
            <td align=center colspan=4 rowspan=2>Empty Bunch</td>
            <td align=center colspan=2 rowspan=2>Abnormal</td>
            <td align=center colspan=4 rowspan=2>Rotten Bunch</td>
            <td align=center colspan=2 rowspan=2>Ripe</td>
            <td align=center colspan=4 rowspan=2>Long<br />
              Stalk</td>
            <td align=center colspan=4 rowspan=2>Brondolan<br />
              (Loose Fruit)</td>
            <td align=center colspan=2 rowspan=2>Pinalty</td>
            <td align=center rowspan=4>Netto<br />
              (Normal)</td>
            <td align=center rowspan=4>Grading<br>%</td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td align=center>Netto</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>Bjr</td>
            <td align=center rowspan=2>Grading</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>%</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>%</td>
            <td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>Kg</td>
            <td align=center rowspan=2>%</td>
            <td align=center colspan=2>Pinalty</td>
            <td align=center rowspan=2>%</td>
            <td align=center rowspan=2>Kg</td>
        </tr>
        <tr>
            <td align=center>(Kg)</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
            <td align=center>%</td>
            <td align=center>Kg</td>
        </tr>
    </thead>
<tbody>";


foreach($rangetanggal as $listtanggal => $tgl)
{
        $stream.="<tr class=rowcontent>";
        

        
        $stream.="<td align=center>".tanggalnormal($tgl)."</td>";   
        $stream.="<td align=right>".number_format(@$netto[$tgl])."</td>";  
        $stream.="<td align=right>".number_format(@$jjg[$tgl])."</td>";  
        $stream.="<td align=right>".number_format(@$bjr[$tgl],2)."</td>";  
        $stream.="<td align=right>".number_format(@$truk[$tgl])."</td>"; 
        $stream.="<td align=right>".number_format(@$nettosor[$tgl])."</td>";  //
        $stream.="<td align=right>".number_format(@$sample[$tgl])."</td>";
    
    //b=unripe
    $sample[$tgl]>0?$persenb=$jumsor[$tgl]['B']/$sample[$tgl]*100:$persenb=0;
    @$persenpenb=round($persenb*50/100,2);
    $netto[$tgl]>0?$kgpenb=round($persenpenb/100*$netto[$tgl]):$kgpenb=0;
        $stream.="<td align=right>".@$jumsor[$tgl]['B']."</td>";
        $stream.="<td align=right>".number_format($persenb,2)."</td>";
        $stream.="<td align=right>".number_format($persenpenb,2)."</td>";
        $stream.="<td align=right>".number_format($kgpenb)."</td>";
    
    //a=over ripe
    $sample[$tgl]>0?$persena=$jumsor[$tgl]['A']/$sample[$tgl]*100:$persena=0;
    @$persenpena=($persena-5)*25/100;
    if($persenpena<0)
    {@$persenpena=0;}
    else 
    {@$persenpena=round(($persena-5)*25/100,2);}
    $netto[$tgl]>0?$kgpena=round($persenpena/100*$netto[$tgl]):$kgpena=0;
        $stream.="<td align=right>".@$jumsor[$tgl]['A']."</td>";
        $stream.="<td align=right>".number_format($persena,2)."</td>";
        $stream.="<td align=right>".number_format($persenpena,2)."</td>";
        $stream.="<td align=right>".number_format($kgpena)."</td>";
    
    //c=Empty Bunch
    $sample[$tgl]>0?$persenc=round(@$persenpenc=$jumsor[$tgl]['C']/$sample[$tgl]*100,2):$persenc=0;
    $netto[$tgl]>0?$kgpenc=round($persenc/100*$netto[$tgl]):$kgpenc=0;
        $stream.="<td align=right>".@$jumsor[$tgl]['C']."</td>";
        $stream.="<td align=right>".number_format($persenc,2)."</td>";
        $stream.="<td align=right>".number_format($persenpenc,2)."</td>";
        $stream.="<td align=right>".number_format($kgpenc)."</td>";
    
    //D=Abnormal
    $sample[$tgl]>0?$persend=round($jumsor[$tgl]['D']/$sample[$tgl]*100,2):$persend=0;
        $stream.="<td align=right>".@$jumsor[$tgl]['D']."</td>";
        $stream.="<td align=right>".number_format($persend,2)."</td>";
    
    //E=Rotten Bunch
    $sample[$tgl]>0?$persene=round(@$persenpene=$jumsor[$tgl]['E']/$sample[$tgl]*100):$persene=0;
    $netto[$tgl]>0?$kgpene=round($persene/100*$netto[$tgl]):$kgpene=0;
        $stream.="<td align=right>".@$jumsor[$tgl]['E']."</td>";
        $stream.="<td align=right>".number_format($persene,2)."</td>";
        $stream.="<td align=right>".number_format($persenpene,2)."</td>";
        $stream.="<td align=right>".number_format($kgpene)."</td>";
    
    //ripe
    @$ripe=round($sample[$tgl]-($jumsor[$tgl]['B']+$jumsor[$tgl]['A']+
    $jumsor[$tgl]['C']+$jumsor[$tgl]['D']+$jumsor[$tgl]['E']));
    $sample[$tgl]>0?$persenripe=round($ripe/$sample[$tgl]*100,2):$persenripe=0;
        $stream.="<td align=right>".number_format($ripe)."</td>";
        $stream.="<td align=right>".number_format($persenripe,2)."</td>";
    
    //F=RoLong Stalk
    $sample[$tgl]>0?$persenf=round($jumsor[$tgl]['F']/$sample[$tgl]*100,2):$persenf=0;
    @$persenpenf=round($persenf/100,2);
    $netto[$tgl]>0?$kgpenf=round($persenpenf/100*$netto[$tgl]):$kgpenf=0;
        $stream.="<td align=right>".@$jumsor[$tgl]['F']."</td>";
        $stream.="<td align=right>".number_format($persenf,2)."</td>";
        $stream.="<td align=right>".number_format($persenpenf,2)."</td>";
        $stream.="<td align=right>".number_format($kgpenf)."</td>";
    
    //G=Brondolan
    /*@$perseng=round($jumsor[$tgl]['G']/$nettosor[$tgl]*100,2);
    @$persenpeng=round((12.5-$perseng)*0.3,2);
    @$kgpeng=round($persenpeng/100*$netto[$tgl]);
        
        $stream.="<td align=right>".@$jumsor[$tgl]['G']."</td>";
        $stream.="<td align=right>".number_format($perseng,2)."</td>";
        $stream.="<td align=right>".number_format($persenpeng,2)."</td>";
        $stream.="<td align=right>".number_format($kgpeng)."</td>";     */
        
        
        $truk[$tgl]>0?$jumsor[$tgl]['G']=$jumsor[$tgl]['G']/$truk[$tgl]:$jumsor[$tgl]['G']=0;
        if($jumsor[$tgl]['G']>$koef)
        {
            $jumsor[$tgl]['G']=$koef;
        }
        
        @$persenpeng=round((12.5-$jumsor[$tgl]['G'])*0.3,2);
        $netto[$tgl]>0?$kgpeng=round($persenpeng/100*$netto[$tgl]):$kgpeng=0;
        @$kgbrdol=$nettosor[$tgl]*$jumsor[$tgl]['G']/100;
        $stream.="<td align=right>".@$kgbrdol."</td>";
        $stream.="<td align=right>".@$jumsor[$tgl]['G']."</td>";
        $stream.="<td align=right>".number_format($persenpeng,2)."</td>";
        $stream.="<td align=right>".number_format($kgpeng)."</td>";
    
    @$kgpen=$kgpena+$kgpenb+$kgpenc+$kgpend+$kgpene+$kgpenf+$kgpeng;
    $netto[$tgl]>0?$perpen=round($kgpen/$netto[$tgl]*100,2):$perpen=0;
        $stream.="<td align=right>".round($perpen,2)."</td>";
        $stream.="<td align=right>".number_format($kgpen)."</td>";
      
    //netto setelah grading
    @$beratnormal=$netto[$tgl]-$kgpen;
    $jjg[$tgl]>0?$persengrad=round($sample[$tgl]/$jjg[$tgl]*100,2):$persengrad=0;
        $stream.="<td align=right>".number_format($beratnormal)."</td>";
        $stream.="<td align=right>".number_format($persengrad,2)."</td>";
    
        $stream.="</tr>";
        
    #buat total
        $tnetto+=$netto[$tgl];
        $tjjg+=$jjg[$tgl];
        $ttruk+=$truk[$tgl];
        $tnettosor+=$nettosor[$tgl];
        $tsample+=$sample[$tgl];
        
        //b unripe
        $tjumsorb+=$jumsor[$tgl]['B'];
        $tkgpenb+=$kgpenb;
        
        //a
        $tjumsora+=$jumsor[$tgl]['A'];
        $tkgpena+=$kgpena;
        
        //c=Empty Bunch
        $tjumsorc+=$jumsor[$tgl]['C'];
        $tkgpenc+=$kgpenc;
        
        //D=Abnormal
        $tjumsord+=$jumsor[$tgl]['D'];
        
        //E=Rotten Bunch
        $tjumsore+=$jumsor[$tgl]['E'];
        $tkgpene+=$kgpene;
        
        //ripe
        $tripe+=$ripe;
        
        //F=RoLong Stalk
        $tjumsorf+=$jumsor[$tgl]['F'];
        $tkgpenf+=$kgpenf;
        
        //g brondolan
        $tkgbrdol+=$kgbrdol;
        $tkgpeng+=$kgpeng;
        
        //total kg pen
        $tkgpen+=$kgpen;
        
 
}//tutup foreach   


$tjjg>0?$tbjr=$tnetto/$tjjg:$tbjr=0;

//b unripe
$tsample>0?$tpersenb=$tjumsorb/$tsample*100:$tpersenb=0;
$tnetto>0?$tpersenpenb=$tkgpenb/$tnetto*100:$tpersenpenb=0;

//a=over ripe
$tsample>0?$tpersena=$tjumsora/$tsample*100:$tpersena=0;
$tnetto>0?$tpersenpena=$tkgpena/$tnetto*100:$tpersenpena=0;

//c=Empty Bunch
$tsample>0?$tpersenc=$tjumsorc/$tsample*100:$tpersenc=0;
$tnetto>0?$tpersenpenc=$tkgpenc/$tnetto*100:$tpersenpenc=0;

//d abnormal
$tsample>0?$tpersend=round($tjumsord/$tsample):$tpersend=0;

//E=Rotten Bunch
$tsample>0?$tpersene=$tjumsore/$tsample*100:$tpersene=0;
$tnetto>0?$tpersenpene=$tkgpene/$tnetto*100:$tpersenpene=0;

///ripe
$tsample>0?$tpersenripe=round($tripe/$tsample*100):$tpersenripe=0;

//F=RoLong Stalk
$tsamplf>0?$tpersenf=$tjumsorf/$tsamplf*100:$tpersenf=0;
$tnetto>0?$tpersenpenf=$tkgpenf/$tnetto*100:$tpersenpenf=0;

//G brondolan
$tnetto>0?$tperseng=$tkgbrdol/$tnetto*100:$tperseng=0;
$tnetto>0?$tpersenpeng=$tkgpeng/$tnetto*100:$tpersenpeng=0;

//persen pen
$tnetto>0?$tperpen=round($tkgpen/$tnetto*100,2):$tperpen=0;


       //netto setelah grading
$tberatnormal=$tnetto-$tkgpen;
$tjjg>0?$tpersengrad=round($tsample/$tjjg*100,2):$tpersengrad=0;


$stream.="<tr class=rowcontent>";
    $stream.="<td align=right>Total</td>";
    $stream.="<td align=right>".number_format($tnetto)."</td>";
    $stream.="<td align=right>".number_format($tjjg)."</td>";
    $stream.="<td align=right>".number_format($tbjr,2)."</td>";
    $stream.="<td align=right>".number_format($ttruk)."</td>";    
    $stream.="<td align=right>".number_format($tnettosor)."</td>";      
    $stream.="<td align=right>".number_format($tsample)."</td>"; 
    
    $stream.="<td align=right>".number_format($tjumsorb)."</td>"; 
    $stream.="<td align=right>".number_format($tpersenb,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpenb,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpenb)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsora)."</td>"; 
    $stream.="<td align=right>".number_format($tpersena,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpena,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpena)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsorc)."</td>"; 
    $stream.="<td align=right>".number_format($tpersenc,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpenc,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpenc)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsord)."</td>";
    $stream.="<td align=right>".number_format($tpersend,2)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsore)."</td>"; 
    $stream.="<td align=right>".number_format($tpersene,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpene,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpene)."</td>";
    
    $stream.="<td align=right>".number_format($tripe)."</td>";
    $stream.="<td align=right>".number_format($tpersenripe,2)."</td>";
    
    $stream.="<td align=right>".number_format($tjumsorf)."</td>"; 
    $stream.="<td align=right>".number_format($tpersenf,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpenf,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpenf)."</td>";
    
    $stream.="<td align=right>".number_format($tkgbrdol)."</td>"; 
    $stream.="<td align=right>".number_format($tperseng,2)."</td>";
    $stream.="<td align=right>".number_format($tpersenpeng,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpeng)."</td>";
    
    
    $stream.="<td align=right>".number_format($tperpen,2)."</td>";
    $stream.="<td align=right>".number_format($tkgpen)."</td>";
    $stream.="<td align=right>".number_format($tberatnormal)."</td>";
    $stream.="<td align=right>".number_format($tpersengrad,2)."</td>";
$stream.="</tr>";

                
                
        $stream.="
	</tbody></table>";


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch($proses)
{
######HTML
	case 'preview':
		echo $stream;
    break;

######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_sortasi_".$tglSkrg;
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
		break;
	
	
	
###############	
#panggil PDFnya
###############
	
	default:
	break;
}

?>