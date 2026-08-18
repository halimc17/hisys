<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
    $proses=$_POST['proses'];
}
else{
    $proses=$_GET['proses'];
}
//$arr="##thnId##unitId";

$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sept","10"=>"Oct","11"=>"Nov","12"=>"Dec");
$arrBln1=array("1"=>"Januari","2"=>"Februari","3"=>"March","4"=>"April","5"=>"Mei","6"=>"Juni","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December");
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optInduk=makeOption($dbname, 'organisasi','kodeorganisasi,induk');
$arrDt=array("0"=>"Head Office","1"=>"Local");

$periodeId = checkPostGet('periodeId','');
$unitId = checkPostGet('unitId','');
$kdVhc = checkPostGet('kdVhc','');
$tanggal = checkPostGet('tanggal','');
$mode = checkPostGet('mode','');
$jenis = checkPostGet('jenis','');
if($kdVhc!=''){
    @$addwhr=" and kodevhc='".$kdVhc."'";
}
$brsdt=0;
$brdr=0;
$bgcoloraja=$tab='';


$arrAkun=array("4110201"=>"gaji","4110202"=>"gaji","4110203"=>"bhnbakar","4110204"=>"skcadang","4110206"=>"bnkleks","4110207"=>"othr","4110208"=>"othr","4110209"=>"othr","4110210"=>"gaji");

if(($proses=='preview')||($proses=='excel')){
        if($unitId==''||$periodeId==''){
            exit("Error: Fields are required");
        }
        if($proses=='excel'){
              $bgcoloraja="bgcolor=#DEDEDE ";
              $brdr=1;
        }
        $sTglAktn="select distinct tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where
                   kodeorg='".substr($unitId,0,4)."' and periode='".$periodeId."'";
        $qTglAktn=$owlPDO->query($sTglAktn) or die(print " Gagal: ".PDOException::getMessage());
        $qTglAktn->setFetchMode(PDO::FETCH_ASSOC);
        $rTglAktn=$qTglAktn->fetch();
        // @$whrtg=" tanggal between '".$rTglAktn['tanggalmulai']."' and '".$rTglAktn['tanggalsampai']."'";
        @$whrtg=" tanggal like '".$periodeId."%' ";
        $totBreak=0;
        #query total jam downtime pada bengkel
        $sData="select sum(downtime) as jmbreak,tanggal,kodevhc from ".$dbname.".vhc_penggantianht
                where ".@$whrtg." and kodeorg like '".@substr($unitId,0,4)."%' ".@$addwhr."  
                group by tanggal,kodevhc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
        while($rData=$qData->fetch()){
            $totBreak+=$rData['jmbreak'];
            @$cekdata=makeOption($dbname,'vhc_5master','kodevhc,kodevhc',"kodetraksi='".$unitId."'  ".$addwhr."  ");
            if($cekdata[$rData['kodevhc']]==''){
                continue;
            }
            @$dtKdvhc[str_replace(" ","",$rData['kodevhc'])]=str_replace(" ","",$rData['kodevhc']);
            @$dtTgl[$rData['tanggal']]=$rData['tanggal'];
            @$dtDwnTime[str_replace(" ","",$rData['kodevhc']).$rData['tanggal']]=$rData['jmbreak'];
            @$totDwnTime[str_replace(" ","",$rData['kodevhc'])]+=$rData['jmbreak'];
            @$dtnyaada[str_replace(" ","",$rData['kodevhc']).$rData['tanggal']]=1;
        }
        #query total rupiah untuk bengkel
        $sRpBengkel="select sum(jumlah) as totRupiah from ".$dbname.".keu_jurnaldt where ".$whrtg."
                     and kodeorg='".substr($unitId,0,4)."' and left(noakun,5)='41101' and noakun!='4110199'";
        $qRpBengkel=$owlPDO->query($sRpBengkel) or die(print " Gagal: ".PDOException::getMessage());
        $qRpBengkel->setFetchMode(PDO::FETCH_ASSOC);
        @$rRpBengkel=$qRpBengkel->fetch();
        @$rpperjambengkel=$rRpBengkel['totRupiah']/$totBreak;


        #query total rupiah cost element traksi
        //$arrAkun=array("4110201"=>"gaji","4110202"=>"gaji","4110203"=>"bhnbakar","4110204"=>"skcadang","4110206"=>"bnkleks","4110207"=>"othr","4110208"=>"othr","4110209"=>"othr");
        $lstArr=array("gaji"=>"gaji","bhnbakar"=>"bhnbakar","skcadang"=>"skcadang","byEksternal"=>"byEksternal","othr"=>"othr");
        $sRpBengkel="select noakun,kodevhc,tanggal,sum(jumlah) as totRupiah from ".$dbname.".keu_jurnaldt where ".$whrtg."
                     and kodeorg='".substr($unitId,0,4)."' and left(noakun,5)='41102' and noakun  not in ('4110299','4110205')
                     and kodevhc!=''  ".@$addwhr."  
                     group by kodevhc,tanggal,noakun";
        $qRpBengkel=$owlPDO->query($sRpBengkel) or die(print " Gagal: ".PDOException::getMessage());
        $qRpBengkel->setFetchMode(PDO::FETCH_ASSOC);
        while($rRpBengkel=$qRpBengkel->fetch()){
            $dtKdvhc[str_replace(" ","",$rRpBengkel['kodevhc'])]=str_replace(" ","",$rRpBengkel['kodevhc']);
            $dtkdvhc[str_replace(" ","",$rRpBengkel['kodevhc'])]=$rRpBengkel['kodevhc'];
            $dtTgl[$rRpBengkel['tanggal']]=$rRpBengkel['tanggal'];
            @$dtRpTraksi[str_replace(" ","",$rRpBengkel['kodevhc']).$rRpBengkel['tanggal'].$arrAkun[$rRpBengkel['noakun']]]+=$rRpBengkel['totRupiah'];
            $dtnyaada[str_replace(" ","",$rRpBengkel['kodevhc']).$rRpBengkel['tanggal']]=1;
        }
        $str1="select tanggal,sum(jumlah) as jumlah,kodevhc,kmhmawal,kmhmakhir from ".$dbname.".vhc_rundt a left join 
              ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
              where ".$whrtg."  ".@$addwhr." and kodevhc in (select kodevhc from ".$dbname.".vhc_5master where kodetraksi like '".$unitId."%')  
              group by kodevhc,tanggal order by kodevhc,tanggal asc";
        $qJmvhc=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $qJmvhc->setFetchMode(PDO::FETCH_ASSOC);
        while($rData=$qJmvhc->fetch()){
            if(@$tempVhc!=$rData['kodevhc']){
                $tempVhc=$rData['kodevhc'];
                $hmawal[str_replace(" ","",$rData['kodevhc'])]=$rData['kmhmawal'];
                $hmakhir[str_replace(" ","",$rData['kodevhc'])]=$rData['kmhmakhir'];
            }else{
                $hmakhir[str_replace(" ","",$rData['kodevhc'])]=$rData['kmhmakhir'];
            }
            $dtkdvhc[str_replace(" ","",$rData['kodevhc'])]=$rData['kodevhc'];
            $jumlahjam[str_replace(" ","",$rData['kodevhc']).$rData['tanggal']]=$rData['jumlah'];
            $dtnyaada[str_replace(" ","",$rData['kodevhc']).$rData['tanggal']]=1;
        }
        if(!empty($dtKdvhc)){
            asort($dtKdvhc);
            asort($dtTgl);
            foreach($dtKdvhc as $lstVhc){
				$e="";
				if(getNopol($lstVhc)!=''){
					$e.= " - ".getNopol($lstVhc);
				}
				if(getNopol($lstVhc,'d')!=''){
					$e.= " - ".getNopol($lstVhc,'d');
				}
                $tab.="<table cellspacing=1 border=0 cellpadding=5>";
                $tab.="<tr>
							<td>".$_SESSION['lang']['kodevhc']."</td>
							<td>:</td>
							<td colspan=2><b>".$lstVhc.$e."</b></td>
						</tr>";//Traksi>Laporan>Biaya kendaraan by Cost Element
						
                $tab.="<tr>
							<td>".$_SESSION['lang']['starthmkm']."</td>
							<td>:</td>
							<td align=right style=width:70px><b>".@number_format($hmawal[$lstVhc],0)."</b></td>
							<td></td>
						</tr>";//Start KH/HM
						
                $tab.="<tr>
							<td>".$_SESSION['lang']['endhmkm']."</td>
							<td>:</td>
							<td align=right style=width:70px><b>".@number_format($hmakhir[$lstVhc],0)."</b></td>
							
						</tr>";//End HM/KM
						
                @$totHm[$lstVhc]=$hmakhir[$lstVhc]-$hmawal[$lstVhc];
				
                $tab.="<tr>
							<td>".$_SESSION['lang']['tothmkm']."</td>
							<td>:</td>
							<td align=right style=width:70px><b>".@number_format($totHm[$lstVhc],0)."</b></td>
							
						</tr>";//Total KM/HM
						
                $tab.="<tr>
							<td>".$_SESSION['lang']['hmkmservice']."</td>
							<td>:</td>
							<td align=right style=width:70px><b>".@number_format($totDwnTime[$lstVhc],0)."</b></td>
							
						</tr>";//HM/KM Service
						
                @$netHm[$lstVhc]=$totHm[$lstVhc]-$totDwnTime[$lstVhc];
                if($netHm[$lstVhc]<0){
                    $netHm[$lstVhc]=0;
                }
                $tab.="<tr>
							<td>".$_SESSION['lang']['netthmkm']."</td>
							<td>:</td>
							<td align=right style=width:70px><b>".@number_format($netHm[$lstVhc],0)."</b></td>
							
						</tr>";//Nett HM/KM
                
				$tab.="</table>";
				$bgcoloraja="";
                $tab.="<table class=sortable cellpadding=5 cellspacing=1 border=".$brdr.">";
                $tab.="<thead><tr>";
                $tab.="<th align=center width= 20px ".$bgcoloraja.">No.</th>";
                $tab.="<th align=center width= 100px ".$bgcoloraja.">".$_SESSION['lang']['tanggal']."</th>";
                $tab.="<th align=center width= 75px ".$bgcoloraja.">".$_SESSION['lang']['hmkmworkshop']."</th>";//Hm Workshop
                $tab.="<th align=center width= 100px  ".$bgcoloraja.">".$_SESSION['lang']['byworkshop']."</th>";//Biaya Workshop(Rp)
                foreach($lstArr as $dtArr){
                    $tab.="<th align=center  width= 100px ".$bgcoloraja.">".$_SESSION['lang'][$dtArr]."</th>";
                }
                $tab.="<th align=center  width= 100px ".$bgcoloraja.">".$_SESSION['lang']['jumlahhmkm']."</th>";//Jumlah HM/KM
                $tab.="<th align=center  width= 100px ".$bgcoloraja.">".$_SESSION['lang']['total']."</th>";
                $tab.="</tr></thead><tbody>";
                $no=0;
                foreach($dtTgl as $lstTgl){
                    if(@$dtnyaada[$lstVhc.$lstTgl]!=0){
                        $no+=1;
                        $addDet="";
                        $addDet2="";
                        if(@$jumlahjam[$lstVhc.$lstTgl]!=0){
                            $addDet="onclick=displayDetail('".$dtkdvhc[$lstVhc]."','".$lstTgl."','".$unitId."',1,'',event) style='cursor:pointer' title='Detail Pekerjaan ".$dtkdvhc[$lstVhc]." ".$lstTgl."'";
                        }
                        if(intval(@$dtDwnTime[$lstVhc.$lstTgl])!=0){
                            $addDet2="onclick=displayDetail('".$dtkdvhc[$lstVhc]."','".$lstTgl."','".$unitId."',2,'',event) style='cursor:pointer' title='Detail Rupiah dan Jam Bengkel ".$dtkdvhc[$lstVhc]." ".$lstTgl."'";
                        }

                        $tab.="<tr class=rowcontent>";
                        $tab.="<td align=center >".$no."</td>";
                        $tab.="<td align=center >".tanggalnormal($lstTgl)."</td>";
                        $tab.="<td align=right  ".$addDet2.">".@number_format($dtDwnTime[$lstVhc.$lstTgl],0)."</td>";
                        #rupiah per jam bengkel
                        @$dtRpPerjmBnkl[$lstVhc.$lstTgl]=$rpperjambengkel*$dtDwnTime[$lstVhc.$lstTgl];
                        $tab.="<td align=right ".$addDet2.">".@number_format($dtRpPerjmBnkl[$lstVhc.$lstTgl],0)."</td>";
                        @$totRp[$lstVhc.$lstTgl]=$dtRpPerjmBnkl[$lstVhc.$lstTgl];
                        @$totByBnkgl[$lstVhc]+=$dtRpPerjmBnkl[$lstVhc.$lstTgl];
                        @$totPerKndJmBrk[$lstVhc]+=$dtDwnTime[$lstVhc.$lstTgl];
                        @$totPerKndJmKrj[$lstVhc]+=$jumlahjam[$lstVhc.$lstTgl];
                        foreach($lstArr as $dtArr){
                            $addDet3="";
                            if(@$dtRpTraksi[$lstVhc.$lstTgl.$dtArr]!=0){
                                $addDet3="onclick=displayDetail('".$dtkdvhc[$lstVhc]."','".$lstTgl."','".$unitId."',3,'".$dtArr."',event) style='cursor:pointer' title='Detail Jurnal ".$dtkdvhc[$lstVhc]." ".$lstTgl."'";
                            }
                            $tab.="<td align=right ".$addDet3.">".@number_format($dtRpTraksi[$lstVhc.$lstTgl.$dtArr],0)."</td>";//
                            @$totRp[$lstVhc.$lstTgl]+=$dtRpTraksi[$lstVhc.$lstTgl.$dtArr];
                            @$totPerkend[$lstVhc.$dtArr]+=$dtRpTraksi[$lstVhc.$lstTgl.$dtArr];
                        }
                        $tab.="<td align=right  ".$addDet.">".@number_format($jumlahjam[$lstVhc.$lstTgl],0)."</td>";
                        $tab.="<td align=right>".@number_format($totRp[$lstVhc.$lstTgl],0)."</td>";
                        $tab.="</tr>";

                    }
                }
                // $tab.="<tr>";
				$bgcoloraja="style=background-color:cyan";
				$tab.="<tr class=rowcontent>";
                $tab.="<td colspan=2 ".$bgcoloraja.">".$_SESSION['lang']['total']." ".$lstVhc."</td>";
                $tab.="<td align=right ".$bgcoloraja.">".$totPerKndJmBrk[$lstVhc]."</td>";
                $tab.="<td align=right ".$bgcoloraja.">".@number_format($totByBnkgl[$lstVhc],0)."</td>";
                $grTotal[$lstVhc]=$totByBnkgl[$lstVhc];
                foreach($lstArr as $dtArr){
                    $tab.="<td align=right ".$bgcoloraja.">".@number_format($totPerkend[$lstVhc.$dtArr],0)."</td>";//
                    $grTotal[$lstVhc]+=$totPerkend[$lstVhc.$dtArr];
                }
                $tab.="<td align=right ".$bgcoloraja.">".@number_format($totPerKndJmKrj[$lstVhc])."</td>";
                $tab.="<td align=right ".$bgcoloraja.">".@number_format($grTotal[$lstVhc],0)."</td>";
                $tab.="</tr>";
            }
            // $tab.="<tr><td colspan=11>&nbsp;</td></tr>";
        }else{
            $tab.="<tr><td colspan=11>".$_SESSION['lang']['dataempty']."</td></tr>";    
        }
        
        
        $tab.="</tbody></table>";
} // end of jika preview/excel..start dari line 28

        
switch($proses){
    case'preview':
    echo $tab;
    break;
        case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];    
        $dte=date("Hms");
        $nop_="biayabengkel_".$dte;
         $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
         gzwrite($gztralala, $tab);
         gzclose($gztralala);
         echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls.gz';
            </script>"; 
    break;
    case'ambilkdvhc':
        $optKdvhc="<option value=''>".$_SESSION['lang']['all']."</option>";
        $str1="select  distinct kodevhc from ".$dbname.".vhc_runht where kodeorg='".substr($unitId,0,4)."'  
               order by kodevhc asc";
        $qJmvhc=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $qJmvhc->setFetchMode(PDO::FETCH_ASSOC);
        while($rData=$qJmvhc->fetch()){
            $cekdata=makeOption($dbname,'vhc_5master','kodevhc,kodevhc',"kodetraksi='".$unitId."'");
            if($cekdata[$rData['kodevhc']]==''){
                continue;
            }
			$e="";
			if(getNopol($rData['kodevhc'])!=''){
				$e.= " - ".getNopol($rData['kodevhc']);
			}
			if(getNopol($rData['kodevhc'],'d')!=''){
				$e.= " - ".getNopol($rData['kodevhc'],'d');
			}
			
			$optKdvhc.="<option value='".$rData['kodevhc']."'>".$rData['kodevhc'].$e."</option>";
            
        }
        echo $optKdvhc;
    break;
    case'DetailData':

        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }  
          
    $stream.="<script language=javascript1.2 src=\"js/generic.js\"></script>
              <link rel=stylesheet type=text/css href=style/".$gen.">";
    switch ($mode) {
        case '1':
            $stream.="<table class=sortable cellspacing=1 border=0 cellpadding=5>";
            $stream.="<thead>
              <tr class=rowheader><th align=center>No</th>
                  <th align=center>".$_SESSION['lang']['tanggal']."</th>
                  <th align=center>".$_SESSION['lang']['notransaksi']."</th>
                  <th align=center>".$_SESSION['lang']['alokasibiaya']."</th>
                  <th align=center>".$_SESSION['lang']['keterangan']."</th>
                  <th align=center>".$_SESSION['lang']['jumlah']."(HM/KM)</th> 
              </tr>
              </thead>
              <tbody>";
            $str="select a.notransaksi,a.alokasibiaya,a.keterangan,a.jumlah,b.tanggal from ".$dbname.".vhc_rundt a left join
                ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi
                where kodevhc='".$kdVhc."'  and tanggal='".$tanggal."'";
            $qstr=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $qstr->setFetchMode(PDO::FETCH_ASSOC);
            while($rstr=$qstr->fetch()){
                @$no+=1;
                $stream.="<tr class=rowcontent>";
                $stream.="<td align=center>".$no."</td>";
                $stream.="<td>".tanggalnormal($rstr['tanggal'])."</td>";
                $stream.="<td>".$rstr['notransaksi']."</td>";
                $stream.="<td>".$rstr['alokasibiaya']."</td>";
                $stream.="<td>".$rstr['keterangan']."</td>";
                $stream.="<td align=right>".$rstr['jumlah']."</td>";
                @$tot+=$rstr['jumlah'];
            }
            $stream.="<tr class=rowcontent>";
            $stream.="<td colspan=5>&nbsp;</td>";
            $stream.="<td align=right>".$tot."</td>";
            $stream.="</tr>";
            $stream.="</tbody></table>";
        break;
        case'2':
            $sData="select sum(downtime) as jmbreak,tanggal,kodevhc,kodeorg from ".$dbname.".vhc_penggantianht
                    where left(tanggal,7)='".substr($tanggal,0,7)."' 
                    and left(kodeorg,4)='".substr($unitId,0,4)."'";
            //echo $sData;
            $qstr=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            $qstr->setFetchMode(PDO::FETCH_ASSOC);
            while($rData=$qstr->fetch()){
                @$totBreak+=$rData['jmbreak'];
            }
            $stream.="<table class=sortable cellspacing=1 border=0 cellpadding=5>";
            $stream.="<thead>
                    <tr class=rowcontent>
                    <td bgcolor=#DEDEDE align=center>No.</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>    
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</td>
                    <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>";
            $stream.="</tr></thead><tbody>";
            #query total rupiah untuk bengkel
            $sRpBengkel="select a.tanggal, a.noakun, a.keterangan, jumlah, a.noreferensi,b.namaakun from ".$dbname.".keu_jurnaldt a left join ".$dbname.".keu_5akun b 
                         on a.noakun=b.noakun
                         where left(tanggal,7)='".substr($tanggal,0,7)."' 
                         and a.kodeorg='".substr($unitId,0,4)."' and left(a.noakun,5)='41101' and a.noakun!='4110199'";
            $qRpBengkel=$owlPDO->query($sRpBengkel) or die(print " Gagal: ".PDOException::getMessage());
            $qRpBengkel->setFetchMode(PDO::FETCH_ASSOC);
            while($rRpBengkel=$qRpBengkel->fetch()){
                  @$no2+=1;
                  $stream.="<tr class=rowcontent>
                            <td align=center>".$no2."</td>
                            <td>".tanggalnormal($rRpBengkel['tanggal'])."</td>
                            <td align=right>".$rRpBengkel['noakun']."</td>
                            <td>".$rRpBengkel['namaakun']."</td>    
                            <td>".$rRpBengkel['keterangan']."</td>
                            <td align=right>".number_format($rRpBengkel['jumlah'])."</td>
                            <td>".$rRpBengkel['noreferensi']."</td>";
                 $stream.="</tr>";
             @$total+=$rRpBengkel['jumlah'];
            }
            $stream.="<tr class=rowtitle>
              <td colspan=5 align=right>".$_SESSION['lang']['total']." Biaya Bengkel :</td>
              <td align=right>".number_format($total)."</td>
              <td></td>";
            $stream.="</tr>";
            $stream.="<tr class=rowtitle>
              <td colspan=5 align=right>".$_SESSION['lang']['total']." ".$_SESSION['lang']['downtime']." :</td>
              <td align=right>".number_format($totBreak)."</td>
              <td></td>";
              @$rpJmbengkl=$total/$totBreak;
            $stream.="<tr class=rowtitle>
              <td colspan=5 align=right>".$_SESSION['lang']['rp']."/ ".$_SESSION['lang']['downtime']." :</td>
              <td align=right>".number_format($rpJmbengkl)."</td>
              <td></td>";
            $stream.="</tr>";
            $stream.="</tbody></table>";  
            $stream.="<center><table class=sortable cellpadding=5 cellspacing=1 border=0>";
            $stream.="<thead>
                  <tr class=rowheader>
                  <td align=center>No</td>
                  <td align=center>".$_SESSION['lang']['keterangan']."</td>
                  <td align=center>".$_SESSION['lang']['downtime']."</td>
                  <td align=center>".$_SESSION['lang']['harga']."</td>
                  <td align=center>".$_SESSION['lang']['rp']."</td></tr></thead><tbody>";
            $sJam="select * from ".$dbname.".vhc_penggantianht where
                   kodeorg like '".substr($unitId,0,4)."%' and tanggal='".$tanggal."' 
                   and kodevhc='".$kdVhc."'
                   order by tanggal, kodevhc";
            $qJam=$owlPDO->query($sJam) or die(print " Gagal: ".PDOException::getMessage());
            $qJam->setFetchMode(PDO::FETCH_ASSOC);
            while($rJam=$qJam->fetch()){
                $no+=1;
                $stream.="<tr class=rowcontent>";
                $stream.="<td>".$no."</td>";
                $stream.="<td>".$rJam['kerusakan']."</td>";
                $stream.="<td align=right>".number_format($rJam['downtime'],1)."</td>";
                $stream.="<td align=right>".number_format($rpJmbengkl,2)."</td>";
                $rpPerjm=$rpJmbengkl*$rJam['downtime'];
                $stream.="<td style=background-color:cyan>".number_format($rpPerjm)."</td>";
                $stream.="</tr>";
            }
            $stream.="</tbody></table></center>";                  
        break;
        case'3':
        $akundt="";
        $awl=1;    
        foreach($arrAkun as $rowdt=>$lsnm){
                if($lsnm==$jenis){
                    if($awl==1){
                        $akundt=$rowdt;         
                        $awl=2;
                    }else{
                        $akundt.=",".$rowdt;         
                    }
                }
        }
        //exit('warning'.$akundt);
        $stream.="<table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream.="<thead>
                <tr class=rowcontent>
                <td bgcolor=#DEDEDE align=center>No.</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>    
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodekegiatan']."</td>    
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakegiatan']."</td>    
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>      
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeblok']."</td>
                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>";
        $stream.="</tr></thead><tbody>";
        $str="select c.satuan,a.tanggal, a.noakun, a.keterangan, a.debet as jumlah, a.kodevhc,a.kodeblok,a.noreferensi,b.namaakun,c.kodekegiatan,c.namakegiatan 
              from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".keu_5akun b
              on a.noakun=b.noakun
              left join ".$dbname.".setup_kegiatan c
              on a.kodekegiatan=c.kodekegiatan
              where a.kodevhc = '".$kdVhc."'
              and tanggal='".$tanggal."'
              and (a.noakun in (".$akundt."))
              and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL)";
              //echo $str;
        $qstr=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $qstr->setFetchMode(PDO::FETCH_ASSOC);
        while($rstr=$qstr->fetch()){
            @$no+=1;
            if($rstr['jumlah']>0){
                  $stream.="<tr class=rowcontent>
                  <td align=center>".$no."</td>
                  <td>".tanggalnormal($rstr['tanggal'])."</td>
                  <td align=right>".$rstr['noakun']."</td>
                  <td>".$rstr['namaakun']."</td>    
                  <td>".$rstr['kodekegiatan']."</td>
                  <td>".$rstr['namakegiatan']."</td>
                      <td>".$rstr['satuan']."</td>
                  <td>".$rstr['keterangan']."</td>
                  <td align=right>".number_format($rstr['jumlah'])."</td>
                  <td>".$rstr['kodeblok']."</td>
                  <td>".$rstr['noreferensi']."</td>";
                 $stream.="</tr>";
             @$total+=$rstr['jumlah'];
            }          
        }
        $stream.="<tr class=rowtitle>
              <td colspan=8 align=right>TOTAL :</td>
              <td align=right>".number_format($total)."</td>
              <td></td><td></td>";
        $stream.="</tr>";
        $stream.="</tbody></table>";    
        break;
    }
   
    echo $stream;
    break;
}
      
?>