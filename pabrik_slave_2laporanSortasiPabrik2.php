<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kdPbrk = checkPostGet('kdPbrk','');
$statBuah = checkPostGet('statBuah','');
$tglAkhir = tanggalsystem(checkPostGet('tglAkhir',''));
$tglAwal = tanggalsystem(checkPostGet('tglAwal',''));
$suppId = checkPostGet('suppId','');
$kdOrg = checkPostGet('kdOrg','');
$intextId = checkPostGet('intextId','');
$BuahStat = checkPostGet('BuahStat','');

// kondisi mendapatkan data
$ukuran['S']=$_SESSION['lang']['kecil'];
$ukuran['M']=$_SESSION['lang']['sedang'];
$ukuran['L']=$_SESSION['lang']['besar'];

// Init Sub Total
setIt($subTotal['beratmasuk'],0);
setIt($subTotal['beratkeluar'],0);
setIt($subTotal['beratbersih'],0);
setIt($subTotal['jjgSortasitot'],0);
setIt($subTotal['prsnBrondolan'],0);
setIt($subTotal['jmlhTndn'],0);
setIt($subTotal['kgpotsortasi'],0);

// Init Total
$totkgpwajib=$totkgbmentah=$totkgbmengkal=$totkgjjgbhmentah=
$totkgbrndlnpersen=$totkgtpanjang=$totkgbnginap=$totkgbbasah=$totkgsampah=0;

if(($proses=='preview')or($proses=='excel')){
            if($suppId!='')
            {
                $str="select namasupplier from ".$dbname.".log_5supplier where kodetimbangan='".$suppId."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
                    $namaspl=$_SESSION['lang']['namasupplier'].":".$bar->namasupplier;
                }
            }
            else if($kdOrg!='')
            {
                $str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch()){
                    $namaspl=$_SESSION['lang']['unit'].":".$bar->namaorganisasi;
                }
            }
            else
            {
                $namaspl=$_SESSION['lang']['dari'].":".$_SESSION['lang']['all'];
            }
    
        if(($tglAkhir=='')||($tglAwal==''))
        {
                echo"warning:Date required";
                exit();
        }
        $thn=substr($tglAwal,0,4);
        $bln=substr($tglAwal,4,2);
        $dte=substr($tglAwal,6,2);
        $tglAwal1=$thn."-".$bln."-".$dte;
        $thn2=substr($tglAkhir,0,4);
        $bln2=substr($tglAkhir,4,2);
        $dte2=substr($tglAkhir,6,2);
        $tglAkhir1=$thn2."-".$bln2."-".$dte2;

//        $stream.="<div style=overflow:auto; height:650px;>";
        $stream.="Mill FFB Grading Report ".$kdPbrk."  ".$namaspl." period : ".tanggalnormal($tglAwal)." s/d ".tanggalnormal($tglAkhir)."";

//        $colspand=count($kodeFraksi);bgcolor=#DEDEDE 
        if($proses=='preview'){
            $stream.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
            $bgcolor="";
        }else{
            $stream.="<table cellpadding=0 cellspacing=0 border=1 class=sortable width=100%>";
            $bgcolor=" bgcolor=#DEDEDE";
        }
        $stream.="<thead><tr class=rowheader>";
        $stream.="<td rowspan=3".$bgcolor." align=center>No.</td>";
        $stream.="<td rowspan=3".$bgcolor." align=center>".$_SESSION['lang']['nospb']."</td>";
        $stream.="<td rowspan=3".$bgcolor." align=center>".$_SESSION['lang']['noTiket']."</td>";
        $stream.="<td rowspan=3".$bgcolor." align=center>".$_SESSION['lang']['tanggal']."</td>";
        $stream.="<td rowspan=3".$bgcolor." align=center>".str_replace(" ","<br>",$_SESSION['lang']['nopol'])."</td>";
        $stream.="<td align=center  colspan=3 valign=middle".$bgcolor.">".$_SESSION['lang']['hslTimbangan']."</td>";
        $stream.="<td rowspan=3".$bgcolor." align=center>".str_replace(" ","<br>",$_SESSION['lang']['jmlhTandan'])."</td>";
//        $stream.="<td align=center rowspan=3 valign=middle>".$_SESSION['lang']['bjr']."</td>";
//        $stream.="<td align=center rowspan=3 valign=middle>".$_SESSION['lang']['sortasi']."(JJG)</td>";
//        $stream.="<td align=center rowspan=3 valign=middle>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['sortasi']."</td>";
        
        $stream.="<td align=center valign=middle colspan=18".$bgcolor.">".$_SESSION['lang']['hslSortasi']."</td>";
        $stream.="<td align=center rowspan=3 valign=middle".$bgcolor.">kriteria buah</td>";
        $stream.="<td align=center rowspan=3 valign=middle".$bgcolor.">".$_SESSION['lang']['potongan']."(Kg)</td></tr>";
        $stream.="<tr>
             <td align=center rowspan=2  valign=middle".$bgcolor.">".$_SESSION['lang']['beratMasuk']."</td>
             <td align=center rowspan=2  valign=middle".$bgcolor.">".$_SESSION['lang']['beratkosong']."</td>
             <td align=center rowspan=2  valign=middle".$bgcolor.">".$_SESSION['lang']['beratBersih']."</td>";
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('pwajib', 'Potongan Wajib', 'sortasi', NULL, 'Potongan Wajib', 'Compulsory Deduction');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bmentah', 'Buah Mentah', 'sortasi', NULL, 'Buah Mentah', 'Raw Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bmengkal', 'Buah Mengkal', 'sortasi', NULL, 'Buah Mengkal', 'Half Fresh Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('jjgbhmentah', 'Janjang Buah Mentah', 'sortasi', NULL, 'Janjang Buah Mentah', 'Raw Fruit Bunch');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('brndlnpersen', 'Brondolan', 'sortasi', NULL, 'Brondolan', 'Brondolan');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('tpanjang', 'Tangkai Panjang', 'sortasi', NULL, 'Tangkai Panjang', 'Long Stalk');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bnginap', 'Buah Menginap', 'sortasi', NULL, 'Buah Menginap', 'Over Night Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('bbasah', 'Buah Basah', 'sortasi', NULL, 'Buah Basah', 'Wet Fruits');
//INSERT INTO `bahasa` (`legend`, `ID`, `location`, `idx`, `MY`, `EN`) VALUES ('sampah', 'Sampah', 'sortasi', NULL, 'Sampah', 'Trash');
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['pwajib']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['bmentah']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['bmengkal']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['jjgbhmentah']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['brndlnpersen']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['tpanjang']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['bnginap']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['bbasah']."</td>";
		 $stream.="<td align=center colspan=2".$bgcolor." align=center>".$_SESSION['lang']['sampah']."</td>";
         $stream.="</tr><tr>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            <td".$bgcolor." align=center>KG</td><td".$bgcolor." align=center>%</td>
            </tr>";
        $stream.="</thead>";
        if(($kdPbrk!='')&&($statBuah!='5'))
            {
                    if($statBuah==0)
                    {
                        if($suppId!='')
                        {
                            $add=" and kodecustomer='".$suppId."'";
                        }
                    }
                    elseif($statBuah>0)
                    {
                        if($kdOrg!='')
                        {
                            $add=" and kodeorg='".$kdOrg."'";
                        }
                    }
                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."' and intex='".$statBuah."'  ".$add."";
            }
            else if(($kdPbrk!='')&&($statBuah=='5'))
            {
                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and millcode='".$kdPbrk."'";
            }
            else if(($kdPbrk=='')&&($statBuah!='5'))
            {
                    if($statBuah=='0')
                    {
                        if($suppId!='')
                        {
                            $add=" and kodecustomer='".$suppId."'";
                        }
                    }
                    elseif($statBuah>1)
                    {
                        if($kdOrg!='')
                        {
                            $add=" and kodeorg='".$kdOrg."'";
                        }
                    }
                    $where=" substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."' and intex='".$statBuah."'   ".$add."";
            }
            else if(($kdPbrk=='')&&($statBuah=='5'))
            {
                    $where= "substr(tanggal,1,10) between '".$tglAwal1."' and '".$tglAkhir1."'";
            }
            
            
                 $str="select * from ".$dbname.".pabrik_sortasi where notiket in
                     (select notransaksi
            from ".$dbname.".pabrik_timbangan where ".$where." and kodebarang='40000003')";
			// echo $str;
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch()){
                    $sortazi[$bar->notiket][$bar->kodefraksi]=$bar->jumlah;
                }
            
        $sql="select *
            from ".$dbname.".pabrik_timbangan where ".$where." and kodebarang='40000003' group by notransaksi  order by `tanggal` asc ";
        $resd=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $resd->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$resd->fetch()){
                        $jmlhTndn=$res['jumlahtandan1']+$res['jumlahtandan2']+$res['jumlahtandan3'];
                        if(($jmlhTndn!=0)||($res['jjgsortasi']!=0))
                        {
                            @$jBrt=$res['beratbersih']/$res['jjgsortasi'];
                            @$jBrt2=$res['beratbersih']/$jmlhTndn;
                        }

                        else
                        {
                            $jBrt=0;
                            $jBrt2=0;
                        }
                            $subTotal['beratmasuk']+=$res['beratmasuk'];
                            $subTotal['beratkeluar']+=$res['beratkeluar'];
                            $subTotal['beratbersih']+=$res['beratbersih'];
                            $subTotal['jjgSortasitot']+=$res['jjgsortasi'];
                            $subTotal['prsnBrondolan']+=$res['persenBrondolan'];
                            $subTotal['jmlhTndn']+=$jmlhTndn;
                            //$subTotal['jBrt']+=$jBrt;
                            $subTotal['kgpotsortasi']+=$res['kgpotsortasi'];
                        $no+=1;

                        @$kgpwajib=$sortazi[$res['notransaksi']]['pwajib']*$res['beratbersih']/100;
                        @$kgbmentah=$sortazi[$res['notransaksi']]['bmentah']*$res['beratbersih']/100;
                        @$kgbmengkal=$sortazi[$res['notransaksi']]['bmengkal']*$res['beratbersih']/100;
                        @$kgjjgbhmentah=$sortazi[$res['notransaksi']]['jjgbhmentah']*$res['beratbersih']/100;
                        @$kgbrndlnpersen=$sortazi[$res['notransaksi']]['brndlnpersen']*$res['beratbersih']/100;
                        @$kgtpanjang=$sortazi[$res['notransaksi']]['tpanjang']*$res['beratbersih']/100;
                        @$kgbnginap=$sortazi[$res['notransaksi']]['bnginap']*$res['beratbersih']/100;
                        @$kgbbasah=$sortazi[$res['notransaksi']]['bbasah']*$res['beratbersih']/100;
                        @$kgsampah=$sortazi[$res['notransaksi']]['sampah']*$res['beratbersih']/100;
                        
                        $totkgpwajib+=$kgpwajib;
                        $totkgbmentah+=$kgbmentah;
                        $totkgbmengkal+=$kgbmengkal;
                        $totkgjjgbhmentah+=$kgjjgbhmentah;
                        $totkgbrndlnpersen+=$kgbrndlnpersen;
                        $totkgtpanjang+=$kgtpanjang;
                        $totkgbnginap+=$kgbnginap;
                        $totkgbbasah+=$kgbbasah;
                        $totkgsampah+=$kgsampah;
                        
                        setIt($sortazi[$res['notransaksi']]['jjgbhmentah'],0);
						setIt($sortazi[$res['notransaksi']]['brndlnpersen'],0);
						setIt($sortazi[$res['notransaksi']]['tpanjang'],0);
						setIt($ukuran[$res['kriteriabuah']],'');
                            $stream.="<tr class=rowcontent>
                                    <td align=center>".$no."</td>
                                    <td>".$res['nospb']."</td>
                                    <td>".$res['notransaksi']."</td>
                                    <td>".tanggalnormal($res['tanggal'])."</td>				 
                                    <td>".$res['nokendaraan']."</td>			 		
                                    <td align=right>".number_format($res['beratmasuk'],2)."</td>
                                    <td align=right>".number_format($res['beratkeluar'],2)."</td>
                                    <td align=right>".number_format($res['beratbersih'],2)."</td>
                                    <td align=right>".number_format($jmlhTndn,0)."</td><!--
                                    <td align=right>".number_format($jBrt,2)."</td>
                                    <td align=right>".number_format($res['jjgsortasi'],0)."</td>
                                    <td align=right>".number_format($jBrt2,2)."</td>-->
                                    <td align=right>".number_format($kgpwajib,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['pwajib'],2)."</td>
                                    <td align=right>".number_format($kgbmentah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bmentah'],2)."</td>
                                    <td align=right>".number_format($kgbmengkal,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bmengkal'],2)."</td>
                                    <td align=right>".number_format($kgjjgbhmentah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['jjgbhmentah'],2)."</td>
                                    <td align=right>".number_format($kgbrndlnpersen,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['brndlnpersen'],2)."</td>
                                    <td align=right>".number_format($kgtpanjang,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['tpanjang'],2)."</td>
                                    <td align=right>".number_format($kgbnginap,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bnginap'],2)."</td>
                                    <td align=right>".number_format($kgbbasah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['bbasah'],2)."</td>
                                    <td align=right>".number_format($kgsampah,2)."</td>
                                    <td align=right>".number_format($sortazi[$res['notransaksi']]['sampah'],2)."</td>
                                        "; 
        
                                    $stream.="<td align=center>".$ukuran[$res['kriteriabuah']]."</td>";
                                    $stream.="<td align=right>".number_format($res['kgpotsortasi'],2)."</td>";
                            $stream.="	
                            </tr>
                            ";


                }
                 $stream.="<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['total']."</td>
                    <td align=right>".number_format($subTotal['beratmasuk'],2)."</td>
                    <td align=right>".number_format($subTotal['beratkeluar'],2)."</td>
                    <td align=right>".number_format($subTotal['beratbersih'],2)."</td>
                    <td align=right>".number_format($subTotal['jmlhTndn'])."</td>
                        ";
                 
                $stream.="<td align=right>".number_format($totkgpwajib,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbmentah,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbmengkal,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgjjgbhmentah,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbrndlnpersen,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgtpanjang,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbnginap,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgbbasah,2)."</td>";  
                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($totkgsampah,2)."</td>";       
                $stream.="<td align=right></td>";  

                $stream.="<td align=right></td>";  
                $stream.="<td align=right>".number_format($subTotal['kgpotsortasi'],2)."</td>";  
                $stream.="</tr>";

        $stream.="</tbody></table>";       
}

     
            
switch($proses)
{
        case'preview':
        echo $stream;
        break;

        case'excel':



                        //echo "warning:".$strx;
                        //=================================================
                $stream.="Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
                        $tglSkrg=date("Ymd");
                        $nop_="Laporan Sortasi 2_".$tglSkrg;
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

        case'getkbn':
               // $optkdOrg2="<option value=''></option value=''>".$_SESSION['lang']['all']."</option>";
            if($kdPbrk=='')
            {
                exit("Error: Mill code required");
            }

                if($BuahStat==0)
                {
                        $optkdOrg2.="<option value=''>".$_SESSION['lang']['all']."</option>";
                        $sOrg="SELECT namasupplier,supplierid,kodetimbangan FROM ".$dbname.".log_5supplier WHERE substring(kodekelompok,1,1)='S' and kodetimbangan is not null";//echo "warning:".$sOrg;exit();
                        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                        while($rOrg=$qOrg->fetch()){
                                $optkdOrg2.="<option value=".$rOrg['kodetimbangan']."".($rOrg['kodetimbangan']==$idCust?'selected':'').">".$rOrg['namasupplier']."</option>";
                        }
                        //echo"warning:test";
                        echo $optkdOrg2."###".$BuahStat;exit();
                }
                elseif($BuahStat==5)
                {
                    $optkdOrg2.="<option value=''>".$_SESSION['lang']['all']."</option>";
                    echo $optkdOrg2."###".$BuahStat;exit();
                }
                elseif($BuahStat==1)
                {
                    $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and kodeorganisasi in(select distinct kodeorg from ".$dbname.".pabrik_timbangan where intex='".$BuahStat."' and millcode='".$kdPbrk."')";//echo "warning:".$sOrg;
                        //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk in(select induk from ".$dbname.".organisasi where tipe='PABRIK')";//echo "warning:".$sOrg;
                }
                elseif($BuahStat==2)
                {
                    $sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and kodeorganisasi in(select distinct kodeorg from ".$dbname.".pabrik_timbangan where intex='".$BuahStat."'  and millcode='".$kdPbrk."')";//echo "warning:".$sOrg;
                        //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk not in(select induk from ".$dbname.".organisasi where tipe='PABRIK')"; //echo "warning:".$sOrg;
                }
                $optkdOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
                $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                while($rOrg=$qOrg->fetch()){
                        $optkdOrg.="<option value=".$rOrg['kodeorganisasi']."".($rOrg['kodeorganisasi']==$kdKbn?'selected':'').">".$rOrg['namaorganisasi']."</option>";
                }
                echo $optkdOrg."###".$BuahStat;
                break;

        break;
}

?>