<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kodeOrg = checkPostGet('kdUnitCst','');
$thnBudget = checkPostGet('thnBudgetCst','');
$noakun = checkPostGet('noakun','');

$where=" kodeunit='".$kodeOrg."' and tahunbudget='".$thnBudget."' and thntnm in (select distinct thntnm from ".$dbname.".bgt_budget_kebun_perakun_vw where  kodeorg='".$kodeOrg."' and tahunbudget='".$thnBudget."'  ) ";
$sSum="select sum(jlhkg) as ton from ".$dbname.".bgt_produksi_afdeling where ".$where."";
$qSum=$owlPDO->query($sSum) or die(print " Gagal: ".PDOException::getMessage());
$qSum->setFetchMode(PDO::FETCH_ASSOC);
$rSum=$qSum->fetch();

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optAkun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$optBrng=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

//GET NOAKUN
$sNoakun="select distinct kegiatan from ".$dbname.".bgt_budget_kegiatan_vw where 
          tipebudget='ESTATE' and kodebudget!='UMUM' and tahunbudget='".$thnBudget."'
          and afdeling like '".$kodeOrg."%' order by kegiatan asc";
$qNoakun=$owlPDO->query($sNoakun) or die(print " Gagal: ".PDOException::getMessage());
$qNoakun->setFetchMode(PDO::FETCH_ASSOC);
$listNoakun=array();
while($rNoakun=$qNoakun->fetch())
{
    $listNoakun[]=$rNoakun['kegiatan'];
}
$jmlBaris=count($listNoakun);
//GET SDM dan total per kepala akun
$sSdm="select tahunbudget, afdeling, tipebudget, kodebudget, kegiatan,sum(rupiah) as rupiah, namakegiatan,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
       where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and (kodebudget like 'SDM%' or kodebudget like 'SUPERVISI') and kodebudget!='UMUM' group by kegiatan order by kegiatan asc";
$qSdm=$owlPDO->query($sSdm) or die(print " Gagal: ".PDOException::getMessage());
$qSdm->setFetchMode(PDO::FETCH_ASSOC);
$dataKeg=array();
while($rSdm=$qSdm->fetch())
{
    $dataKeg[$rSdm['tahunbudget']][$rSdm['kegiatan']][$rSdm['kepalaAkn']]['sdm']=$rSdm['rupiah'];
}
$sSdm2="select tahunbudget, sum(rupiah) as rupiah,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and (kodebudget like 'SDM%' or kodebudget like 'SUPERVISI') and kodebudget!='UMUM' group by kepalaAkn order by kepalaAkn asc";
$qSdm2=$owlPDO->query($sSdm2) or die(print " Gagal: ".PDOException::getMessage());
$qSdm2->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm2=$qSdm2->fetch())
{
    $totalKplaSDM[$rSdm2['tahunbudget']][$rSdm2['kepalaAkn']]['sdm']=$rSdm2['rupiah'];
}


//GET MATERIAL Dan Total Per Kepala AKun
$sSdm="select tahunbudget, afdeling, tipebudget, kodebudget, kegiatan,sum(rupiah) as rupiah, namakegiatan,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and substr(kodebudget,1,1)='M' and kodebudget!='UMUM' group by kegiatan order by kegiatan asc";
$qSdm=$owlPDO->query($sSdm) or die(print " Gagal: ".PDOException::getMessage());
$qSdm->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm=$qSdm->fetch())
{
    $dataKeg[$rSdm['tahunbudget']][$rSdm['kegiatan']][$rSdm['kepalaAkn']]['mat']=$rSdm['rupiah'];
   
}
$sSdm2="select tahunbudget, sum(rupiah) as rupiah,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and substr(kodebudget,1,1)='M' and kodebudget!='UMUM' group by kepalaAkn order by kepalaAkn asc";
$qSdm2=$owlPDO->query($sSdm2) or die(print " Gagal: ".PDOException::getMessage());
$qSdm2->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm2=$qSdm2->fetch())
{
    $totalKplaSDM[$rSdm2['tahunbudget']][$rSdm2['kepalaAkn']]['mat']=$rSdm2['rupiah'];
}

//GET TOOL dan Total Per Kepala Akun
$sSdm="select tahunbudget, afdeling, tipebudget, kodebudget, kegiatan,sum(rupiah) as rupiah, namakegiatan,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and kodebudget like 'TOOL%' and kodebudget!='UMUM' group by kegiatan order by kegiatan asc";
$qSdm=$owlPDO->query($sSdm) or die(print " Gagal: ".PDOException::getMessage());
$qSdm->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm=$qSdm->fetch()){
    $dataKeg[$rSdm['tahunbudget']][$rSdm['kegiatan']][$rSdm['kepalaAkn']]['tool']+=$rSdm['rupiah'];
}
$sSdm2="select tahunbudget, sum(rupiah) as rupiah,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and kodebudget like 'TOOL%' and kodebudget!='UMUM' group by kepalaAkn order by kepalaAkn asc";
$qSdm2=$owlPDO->query($sSdm2) or die(print " Gagal: ".PDOException::getMessage());
$qSdm2->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm2=$qSdm2->fetch()){
    $totalKplaSDM[$rSdm2['tahunbudget']][$rSdm2['kepalaAkn']]['tool']=$rSdm2['rupiah'];
}

//GET TRANSPORT
$sSdm="select tahunbudget, afdeling, tipebudget, kodebudget, kegiatan,sum(rupiah) as rupiah, namakegiatan,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and kodebudget like 'VHC%' and kodebudget!='UMUM' group by kegiatan order by kegiatan asc";
$qSdm=$owlPDO->query($sSdm) or die(print " Gagal: ".PDOException::getMessage());
$qSdm->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm=$qSdm->fetch()){
    @$dataKeg[$rSdm['tahunbudget']][$rSdm['kegiatan']][$rSdm['kepalaAkn']]['vhc']+=$rSdm['rupiah'];
}
$sSdm2="select tahunbudget, sum(rupiah) as rupiah,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and kodebudget like 'VHC%' and kodebudget!='UMUM' group by kepalaAkn order by kepalaAkn asc";
$qSdm2=$owlPDO->query($sSdm2) or die(print " Gagal: ".PDOException::getMessage());
$qSdm2->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm2=$qSdm2->fetch())
{
    $totalKplaSDM[$rSdm2['tahunbudget']][$rSdm2['kepalaAkn']]['vhc']=$rSdm2['rupiah'];
}


//GET KONTRAK dan Total Per Kepala Akun
$sSdm="select tahunbudget, afdeling, tipebudget, kodebudget, kegiatan,sum(rupiah) as rupiah, namakegiatan,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and kodebudget like 'KONTRAK%' and kodebudget!='UMUM' group by kegiatan order by kegiatan asc";
$qSdm=$owlPDO->query($sSdm) or die(print " Gagal: ".PDOException::getMessage());
$qSdm->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm=$qSdm->fetch())
{
    @$dataKeg[$rSdm['tahunbudget']][$rSdm['kegiatan']][$rSdm['kepalaAkn']][kntrk]+=$rSdm['rupiah'];
    @$totalKplaSDM[$rSdm['tahunbudget']][$rSdm['kepalaAkn']][kntrk]+=$rSdm['rupiah'];
}
$sSdm2="select tahunbudget, sum(rupiah) as rupiah,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw
    where afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and kodebudget like 'KONTRAK%'  and kodebudget!='UMUM' group by kepalaAkn order by kepalaAkn asc";
$qSdm2=$owlPDO->query($sSdm2) or die(print " Gagal: ".PDOException::getMessage());
$qSdm2->setFetchMode(PDO::FETCH_ASSOC);
while($rSdm2=$qSdm2->fetch())
{
    $totalKplaSDM[$rSdm2['tahunbudget']][$rSdm2['kepalaAkn']]['kntrk']=$rSdm2['rupiah'];
}
//exit("error".$sSdm2);

//luas TM
//ambil luas planted per tahuntanam
$str="select sum(hathnini) as luas,thntnm from ".$dbname.".bgt_blok where 
      kodeblok like '".$kodeOrg."%' and statusblok='TM' and tahunbudget='".$thnBudget."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    @$ttlLuastm+=$bar->luas;
}
$str="select sum(hathnini) as luas,thntnm from ".$dbname.".bgt_blok where 
      kodeblok like '".$kodeOrg."%' and statusblok like 'TB%' and tahunbudget='".$thnBudget."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
     @$ttlLuastbm+=$bar->luas;
}
//jumlah LC
// $str="select sum(lcthnini) as luas,thntnm from ".$dbname.".bgt_blok where 
//      kodeblok like '".$kodeOrg."%' and lcthnini!=''
//      group by thntnm";
 $str="select sum(hathnini) as luas,thntnm from ".$dbname.".bgt_blok where 
      kodeblok like '".$kodeOrg."%' and statusblok in ('TB') and tahunbudget='".$thnBudget."' 
      group by thntnm";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $dtJmlhLuasLc[$thnBudget][$bar->thntnm]+=$bar->luas;
    $ttlLuasLc+=$bar->luas;
}
//jumlah pokok
 $str="select sum(pokokthnini) as luas,thntnm from ".$dbname.".bgt_blok where 
      kodeblok like '".$kodeOrg."%' and statusblok ='BBT' and tahunbudget='".$thnBudget."' 
      group by thntnm";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $dtJmlhLuasPkk[$thnBudget][$bar->thntnm]+=$bar->luas;
    $ttlLuasPkk+=$bar->luas;
}
 //total rupaiah perkepala kegiatan
$sTotAkun="select sum(rupiah) as total,substr(kegiatan,1,3) as akunkpla from ".$dbname.".bgt_budget_kegiatan_vw  where  afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."'  group by akunkpla";
$qTotAkun=$owlPDO->query($sTotAkun) or die(print " Gagal: ".PDOException::getMessage());
$qTotAkun->setFetchMode(PDO::FETCH_ASSOC);
while($rTotAkun=$qTotAkun->fetch())
{
    if(substr($rTotAkun['akunkpla'],0,1)!='6')
    {
        $totRupiah[$thnBudget][$rTotAkun['akunkpla']]=$rTotAkun['total'];
    }
    else
    {
        $rTotAkun['akunkpla']= substr($rTotAkun['akunkpla'],0,1);
        @$totRupiah[$thnBudget][$rTotAkun['akunkpla']]+=$rTotAkun['total'];
    }
}

//total rupaiah per kegiatan
$sTotAkun2="select sum(rupiah) as total,kegiatan,substr(kegiatan,1,3) as kepalaAkn from ".$dbname.".bgt_budget_kegiatan_vw  where  afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."'  group by kegiatan";
$qTotAkun2=$owlPDO->query($sTotAkun2) or die(print " Gagal: ".PDOException::getMessage());
$qTotAkun2->setFetchMode(PDO::FETCH_ASSOC);
while($rTotAkun2=$qTotAkun2->fetch())
{
    $totRupiahKegiatan[$thnBudget][$rTotAkun2['kegiatan']][$rTotAkun2['kepalaAkn']]=$rTotAkun2['total'];
}

if($_GET['proses']=='excel')
{
$bg=" bgcolor=#DEDEDE";
$brdr=1;
$tab="<table>
 <tr><td colspan=5 align=left><font size=5>".strtoupper($_SESSION['lang']['lapLangsung'])."</font></td></tr> 
 <tr><td colspan=5 align=left>".$optNm[$kodeOrg]."</td></tr>   
 <tr><td>".$_SESSION['lang']['budgetyear']."</td><td colspan=2 align=left>".$thnBudget."</td></tr>   
 </table>";
}
else
{
   $bg=" ";
   $brdr=0; 
}

//echo"<pre>";
//print_r($dataKeg);
//echo"</pre>";
//exit("error".$ttlLuastbm."__".$ttlLuastm);
if($kodeOrg==''||$thnBudget=='')
{
    exit("Error:Field Tidak Boleh Kosong");
}

            $arrLang=array("0"=>$_SESSION['lang']['sdm'],"1"=>$_SESSION['lang']['material'],"2"=>$_SESSION['lang']['peralatan'],"3"=>$_SESSION['lang']['kndran'],"4"=>$_SESSION['lang']['kontrak']);
            $tab.="<table cellpadding=5 cellspacing=1 border=".$brdr." class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th  rowspan=5 valign='middle' align=center ".$bg." >".$_SESSION['lang']['noakun']."</th>";
            $tab.="<th  rowspan=5 valign='middle' align=center ".$bg." >".$_SESSION['lang']['namakegiatan']."</th>";
            $tab.="<th colspan='2'  align=center ".$bg.">".$_SESSION['lang']['total']."</th>";
            
            for($dtLang=0;$dtLang<=4;$dtLang++)
            {
                $tab.="<th colspan='2' rowspan='4' align=center ".$bg.">".$arrLang[$dtLang]."</th>";
            }
            
            @$hslBagi=($rSum['ton']/1000)/$ttlLuastm;
            $tab.="<tr><th align=right ".$bg.">TM=".numb_format($ttlLuastm,2)." TBM=".numb_format($ttlLuastbm,2)."</th><th ".$bg.">Ha</th></tr>";
            $tab.="<tr><th align=right ".$bg.">".numb_format($rSum['ton'],2)."</th><th ".$bg.">Kg</th></tr>";
            $tab.="<tr><th align=right ".$bg.">".numb_format($hslBagi,2)."</th><th ".$bg.">Ton/Ha</th></tr>";
            $tab.="<tr>";
            for($thList=1;$thList<=6;$thList++)
            {
                $tab.="<th align=center ".$bg.">".$_SESSION['lang']['total']."</th><th align=center ".$bg.">Rp/Ha</th>";
            }
            $tab.="</tr>";

            $tab.="</thead><tbody>";
			$ktKrgng = "";
         if(is_array($listNoakun) && count($listNoakun)>0)    
        {
			
            foreach($listNoakun as $barisNoakun)
            {
             
                $new=substr($barisNoakun,0,3);
                if($ktKrgng!='' and $ktKrgng!=$new)
                {
                    $awal=0;
                    if(substr($ktKrgng,0,1)!=6)
                    {
                        if($ktKrgng=='126')
                        {
                        @$totBagi=$ttlLuastbm>0?$totRupiah[$thnBudget][$ktKrgng]/$ttlLuastbm:0;
                        @$totKepla[$thnBudget][$ktKrgng]['sdm']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['sdm']/$ttlLuastbm:0;
                        @$totKepla[$thnBudget][$ktKrgng]['mat']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['mat']/$ttlLuastbm:0;
                        @$totKepla[$thnBudget][$ktKrgng]['tool']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['tool']/$ttlLuastbm:0;
                        @$totKepla[$thnBudget][$ktKrgng]['vhc']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['vhc']/$ttlLuastbm:0;
                        @$totKepla[$thnBudget][$ktKrgng]['kntrk']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['kntrk']/$ttlLuastbm:0;
                        $pembagi[$ktKrgng]=$ttlLuastbm;
                        $tab.="<thead><tr class=rowheader><td align=right colspan=2>".$_SESSION['lang']['total']." TBM</td>";
                        $tab.="<td align=right>".numb_format($totRupiah[$thnBudget][$ktKrgng],2)."</td>";
                        $tab.="<td align=right>".numb_format($totBagi,2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['sdm'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepla[$thnBudget][$ktKrgng]['sdm'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['mat'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepla[$thnBudget][$ktKrgng]['mat'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['tool'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepla[$thnBudget][$ktKrgng]['tool'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['vhc'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepla[$thnBudget][$ktKrgng]['vhc'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['kntrk'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepla[$thnBudget][$ktKrgng]['kntrk'],2)."</td>";
                        $tab.="</tr></thead>";  
                        }
                        else if($ktKrgng=='128')
                        {
                           @$totBagi=$ttlLuasPkk;
                            $ttlLuastbm=$ttlLuasPkk;
                            $tab.="<thead><tr class=rowheader><td align=right colspan=2>".$_SESSION['lang']['total']." BIBITAN</td>";
                            $tab.="<td align=right>".numb_format($totRupiah[$thnBudget][$ktKrgng],2)."</td>";
                            $tab.="<td align=right>".numb_format($totBagi,2)."</td>";
                             $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['sdm'],2)."</td>";
                                $tab.="<td align=right>".numb_format((@$ttlLuastbm),2)."</td>";
                                $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['mat'],2)."</td>";
                                $tab.="<td align=right>".numb_format((@$ttlLuastbm),2)."</td>";
                                $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['tool'],2)."</td>";
                                $tab.="<td align=right>".numb_format((@$ttlLuastbm),2)."</td>";
                                $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['vhc'],2)."</td>";
                                $tab.="<td align=right>".numb_format((@$ttlLuastbm),2)."</td>";
                                $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['kntrk'],2)."</td>";
                                $tab.="<td align=right>".numb_format((@$ttlLuastbm),2)."</td>";
                            $tab.="</tr></thead>";  
                        }
                    }
                    else 
                    {
                      $ktKrgng=substr($ktKrgng,0,1);
                      $sTotal="select distinct kegiatan from ".$dbname.". bgt_budget_kegiatan_vw  where  afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and substring(kegiatan,1,1)='6'";
                      $qTotal=$owlPDO->query($sTotal) or die(print " Gagal: ".PDOException::getMessage());
					  $qTotal->setFetchMode(PDO::FETCH_ASSOC);
					  $rTotal=owlBaris($qTotal);
					  
                      $awal+=1;
                      if($awal==$rTotal)
                      {
                        @$totBagi=$ttlLuastm>0?$totRupiah[$thnBudget][$ktKrgng]/$ttlLuastm:0;
                        $ttlLuastbm=$ttlLuastm;
                        @$totKepalas[$thnBudget][$ktKrgng]['sdm']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['sdm']/$ttlLuastbm:0;
                        @$totKepalas[$thnBudget][$ktKrgng]['mat']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['mat']/$ttlLuastbm:0;
                        @$totKepalas[$thnBudget][$ktKrgng]['tool']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['tool']/$ttlLuastbm:0;
                        @$totKepalas[$thnBudget][$ktKrgng]['vhc']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['vhc']/$ttlLuastbm:0;
                        @$totKepalas[$thnBudget][$ktKrgng]['kntrk']=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['kntrk']/$ttlLuastbm:0;
                        $tab.="<thead><tr class=rowheader><td align=right colspan=2>".$_SESSION['lang']['total']." TM</td>";
                        $tab.="<td align=right>".numb_format($totRupiah[$thnBudget][$ktKrgng],2)."</td>";
                        $tab.="<td align=right>".numb_format($totBagi,2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['sdm'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepalas[$thnBudget][$ktKrgng]['sdm'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['mat'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepalas[$thnBudget][$ktKrgng]['mat'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['tool'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepalas[$thnBudget][$ktKrgng]['tool'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['vhc'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepalas[$thnBudget][$ktKrgng]['vhc'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totalKplaSDM[$thnBudget][$ktKrgng]['kntrk'],2)."</td>";
                        $tab.="<td align=right>".numb_format($totKepalas[$thnBudget][$ktKrgng]['kntrk'],2)."</td>";
                        $tab.="</tr></thead>";  
                        $awal=0;
                      }
                      
                    }
//                    

                }
                 if(substr($new,0,1)!=6)
                    {
                        if($new=='126')
                        {
                            $pembagi[$new]=$ttlLuastbm;   
//                            $kwe=substr($barisNoakun,0,5);
//                            if($kwe<='12605') // $pembagi[$new]=$ttlLuasLc; 
//                                if($ttlLuasLc==0){
//                                    $pembagi[$new]=$ttlLuastbm;
//                                }
//                         else $pembagi[$new]=$ttlLuastbm;   
                        }
                        elseif($new=='128')
                        {
                            $pembagi[$new]=$ttlLuasPkk;
                        }
                    }
                    else
                    {
                        $pembagi[$new]=$ttlLuastm;
                    }
                   
                    @$kegHa[$thnBudget][$barisNoakun][$new]=$pembagi[$new]>0?$totRupiahKegiatan[$thnBudget][$barisNoakun][$new]/$pembagi[$new]:0;
                    @$kegSdm[$thnBudget][$barisNoakun][$new]=$pembagi[$new]>0?$dataKeg[$thnBudget][$barisNoakun][$new]['sdm']/$pembagi[$new]:0;
                    @$kegMat[$thnBudget][$barisNoakun][$new]=$pembagi[$new]>0?$dataKeg[$thnBudget][$barisNoakun][$new]['mat']/$pembagi[$new]:0;
                    @$kegTool[$thnBudget][$barisNoakun][$new]=$pembagi[$new]>0?$dataKeg[$thnBudget][$barisNoakun][$new]['tool']/$pembagi[$new]:0;
                    @$kegVhc[$thnBudget][$barisNoakun][$new]=$pembagi[$new]>0?$dataKeg[$thnBudget][$barisNoakun][$new]['vhc']/$pembagi[$new]:0;
                    @$kegKntrak[$thnBudget][$barisNoakun][$new]=$pembagi[$new]>0?$dataKeg[$thnBudget][$barisNoakun][$new]['kntrk']/$pembagi[$new]:0;

                    $tab.="<tr class='rowcontent'>";
                    $tab.="<td>".$barisNoakun."</td><td>".$optKegiatan[$barisNoakun]."</td>";
                    $tab.="<td align=right>".numb_format($totRupiahKegiatan[$thnBudget][$barisNoakun][$new],2)."</td>";
                    $tab.="<td align=right>".numb_format($kegHa[$thnBudget][$barisNoakun][$new],2)."</td>";
                    $tab.="<td align=right>".numb_format(@$dataKeg[$thnBudget][$barisNoakun][$new]['sdm'],2)."</td>";
                    $tab.="<td align=right>".numb_format($kegSdm[$thnBudget][$barisNoakun][$new],2)."</td>";
                    $tab.="<td align=right>".numb_format(@$dataKeg[$thnBudget][$barisNoakun][$new]['mat'],2)."</td>";
                    $tab.="<td align=right>".numb_format($kegMat[$thnBudget][$barisNoakun][$new],2)."</td>";
                    $tab.="<td align=right>".numb_format(@$dataKeg[$thnBudget][$barisNoakun][$new]['tool'],2)."</td>";
                    $tab.="<td align=right>".numb_format($kegTool[$thnBudget][$barisNoakun][$new],2)."</td>";
                    $tab.="<td align=right>".numb_format(@$dataKeg[$thnBudget][$barisNoakun][$new]['vhc'],2)."</td>";
                    $tab.="<td align=right>".numb_format($kegVhc[$thnBudget][$barisNoakun][$new],2)."</td>";
                    $tab.="<td align=right>".numb_format(@$dataKeg[$thnBudget][$barisNoakun][$new]['kntrk'],2)."</td>";
                    $tab.="<td align=right>".numb_format($kegKntrak[$thnBudget][$barisNoakun][$new],2)."</td>";
                    $tab.="</tr>";
                
               
                //totalan semuanya
                @$grnTotKeg+=$totRupiahKegiatan[$thnBudget][$barisNoakun][$new];
                @$grnTotKegha+=$kegHa[$thnBudget][$barisNoakun][$new];
                @$grnTotKegSdm+=$dataKeg[$thnBudget][$barisNoakun][$new]['sdm'];
                @$grnTotKeghaSdm+=$kegSdm[$thnBudget][$barisNoakun][$new];
                @$grnTotKegMat+=$dataKeg[$thnBudget][$barisNoakun][$new]['mat'];
                @$grnTotKeghaMat+=$kegMat[$thnBudget][$barisNoakun][$new];
                @$grnTotKegTool+=$dataKeg[$thnBudget][$barisNoakun][$new]['tool'];
                @$grnTotKeghaTool+=$kegTool[$thnBudget][$barisNoakun][$new];
                @$grnTotKegVhc+=$dataKeg[$thnBudget][$barisNoakun][$new]['vhc'];
                @$grnTotKeghaVhc+=$kegVhc[$thnBudget][$barisNoakun][$new];
                @$grnTotKegKntrak+=$dataKeg[$thnBudget][$barisNoakun][$new]['kntrk'];
                @$grnTotKeghaKntrak+=$kegKntrak[$thnBudget][$barisNoakun][$new];
                 if(substr($barisNoakun,0,1)=='6')
                {
                    $ktKrgng=substr($barisNoakun,0,1);
                }
                else
                {
                    $ktKrgng=substr($barisNoakun,0,3);
                }
                
            }
		}	
           $tab.="<thead>";
           if($ktKrgng=='126')
            {
                @$totBagi=$ttlLuastbm>0?$totRupiah[$thnBudget][$ktKrgng]/$ttlLuastbm:0;
                $ttlLuastbm=$ttlLuastbm;
            $tab.="<tr class='rowheader'><td align=right colspan=2>".$_SESSION['lang']['total']." TBM</td>";
            }
            else if($ktKrgng=='128')
            {
                 @$totBagi=$ttlLuasPkk>0?$totRupiah[$thnBudget][$ktKrgng]/$ttlLuasPkk:0;
                 $ttlLuastbm=$ttlLuasPkk;
                $tab.="<tr class='rowheader'><td align=right colspan=2>".$_SESSION['lang']['total']." BIBITAN</td>";
            }
            else if($ktKrgng=='6')
            {
                 @$totBagi=$ttlLuastm>0?$totRupiah[$thnBudget][$ktKrgng]/$ttlLuastm:0;
                 $ttlLuastbm=$ttlLuastm;
                $tab.="<tr class='rowheader'><td align=right colspan=2>".$_SESSION['lang']['total']." TM</td>";
            }
            @$bagiTotalSdm=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['sdm']/$ttlLuastbm:0;
            @$bagiTotalMat=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['mat']/$ttlLuastbm:0;
            @$bagiTotalTool=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['tool']/$ttlLuastbm:0;
            @$bagiTotalVhc=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['vhc']/$ttlLuastbm:0;
            @$bagiTotalKntrk=$ttlLuastbm>0?$totalKplaSDM[$thnBudget][$ktKrgng]['kntrk']/$ttlLuastbm:0;
            $tab.="<td align=right>".numb_format(@$totRupiah[$thnBudget][$ktKrgng],2)."</td>";
            $tab.="<td align=right>".numb_format(@$totBagi,2)."</td>";
            $tab.="<td align=right>".numb_format(@$totalKplaSDM[$thnBudget][$ktKrgng]['sdm'],2)."</td>";
            $tab.="<td align=right>".numb_format(@$bagiTotalSdm,2)."</td>";
            $tab.="<td align=right>".numb_format(@$totalKplaSDM[$thnBudget][$ktKrgng]['mat'],2)."</td>";
            $tab.="<td align=right>".numb_format(@$bagiTotalMat,2)."</td>";
            $tab.="<td align=right>".numb_format(@$totalKplaSDM[$thnBudget][$ktKrgng]['tool'],2)."</td>";
            $tab.="<td align=right>".numb_format(@$bagiTotalTool,2)."</td>";
            $tab.="<td align=right>".numb_format(@$totalKplaSDM[$thnBudget][$ktKrgng]['vhc'],2)."</td>";
            $tab.="<td align=right>".numb_format(@$bagiTotalVhc,2)."</td>";
            $tab.="<td align=right>".numb_format(@$totalKplaSDM[$thnBudget][$ktKrgng]['kntrk'],2)."</td>";
            $tab.="<td align=right>".numb_format(@$bagiTotalKntrk,2)."</td>";
            $tab.="</tr>";  
            $tab.="<tr class='rowheader'>";
            $tab.="<td colspan=2 align=right>".$_SESSION['lang']['grnd_total']."</b></td>";
            $tab.="<td align=right>".numb_format(@$grnTotKeg,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKegha,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKegSdm,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKeghaSdm,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKegMat,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKeghaMat,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKegTool,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKeghaTool,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKegVhc,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKeghaVhc,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKegKntrak,2)."</td>";
            $tab.="<td align=right>".numb_format(@$grnTotKeghaKntrak,2)."</td>";
            $tab.="</tr></thead>";
            $tab.="</tbody></table>";
       
	switch($proses)
        {
            case'preview':
            echo $tab;
            break;
            case'excel':
             
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="lapKebunByLngsng_cst_elmnt_".$dte;
            $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                     gzwrite($gztralala, $tab);
                     gzclose($gztralala);
                     echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls.gz';
                        </script>";

            break;
         
            case'pdf':
           if($kodeOrg==''||$thnBudget=='')
            {
                exit("Error:Field Tidak Boleh Kosong");
            }
      
           class PDF extends FPDF {
            function Header() {
           
            global $dbname;
            global $optAkun;
            global $optKegiatan;
            global $totRupiahKegiatan;
            global $totRupiah;
            global $ttlLuastbm;
            global $arrLang;
            global $rSum;
            global $kodeOrg;
            global $thnBudget;
            global $awal;
            global $optNm;
            global $owlPDO;
           
         		$sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
				$qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
				$qAlamat->setFetchMode(PDO::FETCH_ASSOC);
				$rAlamat=$qAlamat->fetch();
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 10;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$rAlamat['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();	
                $this->Ln();
		$this->Ln();
               
               
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['lapLangsung']),0,1,'C');
                $this->Ln();	
                //$this->Cell(275,5,strtoupper($_SESSION['lang']['rprodksiPabrik']),0,1,'C');
                $this->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNm[$kodeOrg],0,1,'C');
                $this->SetFont('Arial','',8);
                $this->Cell(850,$height,$_SESSION['lang']['tanggal'],0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,date('d-m-Y H:i'),0,1,'R');
                $this->Cell(850,$height,$_SESSION['lang']['page'],0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$this->PageNo(),0,1,'R');
                 $this->Cell(850,$height,'User',0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$_SESSION['standard']['username'],0,1,'R');

                $this->Ln();
                $this->Ln();
                $height = 50;
                $this->SetFillColor(220,220,220);
                $this->SetFont('Arial','B',7);
                $this->Cell(58,$height,$_SESSION['lang']['noakun'],1,0,'C',1);
                $this->Cell(150,$height,$_SESSION['lang']['namakegiatan'],1,0,'C',1);
                $this->SetFont('Arial','B',5);
                $this->Cell(100,10,$_SESSION['lang']['total'],1,1,'C',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                $this->Cell(100,10,'TM='.numb_format(@$ttlLuastm,2).' TBM='.numb_format(@$ttlLuastbm,2)." Ha",1,1,'L',1);
                //$this->Cell(50,10,"Ha",1,1,'L',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                $this->Cell(50,10,numb_format($rSum['ton'],2),1,0,'R',1);
                $this->Cell(50,10,"Kg",1,1,'L',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                
                @$hslBagi=($rSum['ton']/1000)/$ttlLuastm;
                $this->Cell(50,10,numb_format($hslBagi,2),1,0,'R',1);
                $this->Cell(50,10,"Ton/Ha",1,1,'L',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                $this->Cell(50,10,$_SESSION['lang']['total'],1,0,'R',1);
                $this->Cell(50,10,"RP/Ha",1,1,'L',1);
                $br=308;
                $ypertama=$this->GetY();
                
                for($dtLang=0;$dtLang<=4;$dtLang++)
                {
                            $this->SetY($ypertama-50);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(100,40,$arrLang[$dtLang],1,1,'C',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(50,10,$_SESSION['lang']['total'],1,0,'R',1);
                            $this->Cell(50,10,"RP/Ha",1,1,'L',1);
               
                    $br+=100;
                }
  
                
          }
              function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
            }
            //================================

            $pdf=new PDF('L','pt','LEGAL');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 10;
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','B',5);
            $awal=0;
			$ktKrgng2="";
            foreach($listNoakun as $barisNoakun)
            {
             
                $new2=substr($barisNoakun,0,3);
                if($ktKrgng2!='' and $ktKrgng2!=$new2)
                {
                     
                    $pdf->SetFont('Arial','B',5);
                    $xPertama=$pdf->GetX();
                    $pdf->SetX($xPertama);
                    if(substr($ktKrgng2,0,1)!='6')
                    {
                        if($ktKrgng2=='126')
                        {
                            $pdf->Cell(208,$height,$_SESSION['lang']['total']." TBM",1,0,'R',1); 
                            $xPertama=$pdf->GetX();
                            $pdf->SetX($xPertama);
                            @$hsilBagi=$ttlLuastbm>0?$totRupiah[$thnBudget][$ktKrgng2]/$ttlLuastbm:0;
                            $pdf->Cell(50,10,numb_format($totRupiah[$thnBudget][$ktKrgng2],0),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format($hsilBagi,0),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['sdm'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm?0>(@$totalKplaSDM[$thnBudget][$ktKrgng2]['sdm']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['mat'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm?0>(@$totalKplaSDM[$thnBudget][$ktKrgng2]['mat']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['tool'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm?0>(@$totalKplaSDM[$thnBudget][$ktKrgng2]['tool']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['vhc'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm?0>(@$totalKplaSDM[$thnBudget][$ktKrgng2]['vhc']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm?0>(@$totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk']/$ttlLuastbm):0),2),1,1,'L',1);
                           // $pdf->Cell(500,$height,"",1,1,'C',1);
                        }
                        else if($ktKrgng2=='128')
                        {
                            $pdf->Cell(208,$height,$_SESSION['lang']['total']." BIBITAN",1,0,'R',1);
                            $xPertama=$pdf->GetX();
                            $pdf->SetX($xPertama);
                            @$hsilBagi=$ttlLuasPkk;
                            $ttlLuastbm=$ttlLuasPkk;
                            $pdf->Cell(50,10,numb_format($totRupiah[$thnBudget][$ktKrgng2],0),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format($hsilBagi,0),1,0,'R',1);
                             $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['sdm'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['sdm']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['mat'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['mat']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['tool'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['tool']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['vhc'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['vhc']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk']/$ttlLuastbm):0),2),1,1,'L',1);
                            //$pdf->Cell(500,$height,"",1,1,'C',1);
                        }
                    }
                    else
                    {
                        $ktKrgng2=substr($ktKrgng2,0,1);
                        $sTotal="select distinct kegiatan from ".$dbname.". bgt_budget_kegiatan_vw  where  afdeling like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and substring(kegiatan,1,1)='6'";
                        $qTotal=$owlPDO->query($sTotal) or die(print " Gagal: ".PDOException::getMessage());
						$qTotal->setFetchMode(PDO::FETCH_ASSOC);
						$rTotal=owlBaris($qTotal);
                        $awal+=1;
                        if($awal==$rTotal)
                        {
                            $pdf->Cell(208,$height,$_SESSION['lang']['total']." TM",1,0,'R',1);
                            $xPertama=$pdf->GetX();
                            $pdf->SetX($xPertama);
                            @$hsilBagi=$ttlLuastm>0?$totRupiah[$thnBudget][$ktKrgng2]/$ttlLuastm:0;
                            $ttlLuastbm=$ttlLuastm;
                            $pdf->Cell(50,10,numb_format($totRupiah[$thnBudget][$ktKrgng2],0),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format($hsilBagi,0),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['sdm'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['sdm']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['mat'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['mat']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['tool'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['tool']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['vhc'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['vhc']/$ttlLuastbm):0),2),1,0,'L',1);
                            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk'],2),1,0,'R',1);
                            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk']/$ttlLuastbm):0),2),1,1,'L',1);
                            //$pdf->Cell(500,$height,"",1,1,'C',1);
                            $awal=0;
                        }
                    }

                    
                }
                @$kegHa=$ttlLuastbm>0?$totRupiahKegiatan[$thnBudget][$barisNoakun]/$ttlLuastbm:0;
                @$kegSdm=$ttlLuastbm>0?$dataKeg[$thnBudget][$barisNoakun]['sdm']/$ttlLuastbm:0;
                @$kegMat=$ttlLuastbm>0?$dataKeg[$thnBudget][$barisNoakun]['mat']/$ttlLuastbm:0;
                @$kegTool=$ttlLuastbm>0?$dataKeg[$thnBudget][$barisNoakun]['tool']/$ttlLuastbm:0;
                @$kegVhc=$ttlLuastbm>0?$dataKeg[$thnBudget][$barisNoakun]['vhc']/$ttlLuastbm:0;
                @$kegKntrak=$ttlLuastbm>0?$dataKeg[$thnBudget][$barisNoakun]['kntrk']/$ttlLuastbm:0;
                $pdf->Cell(58,$height,$barisNoakun,1,0,'L',1);
                $pdf->Cell(150,$height,$optKegiatan[$barisNoakun],1,0,'L',1);
                $pdf->Cell(50,10,numb_format($totRupiahKegiatan[$thnBudget][$barisNoakun],2),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($kegHa,2),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($dataKeg[$thnBudget][$barisNoakun]['sdm'],2),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($kegSdm,2),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($dataKeg[$thnBudget][$barisNoakun]['mat'],2),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($kegMat,2),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($dataKeg[$thnBudget][$barisNoakun]['tool'],2),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($kegTool,2),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($dataKeg[$thnBudget][$barisNoakun]['vhc'],2),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($kegVhc,2),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($dataKeg[$thnBudget][$barisNoakun]['kntrk'],2),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($kegKntrak,2),1,1,'L',1);
                if(substr($barisNoakun,0,1)=='6')
                {
                    $ktKrgng2=substr($barisNoakun,0,1);
                }
                else
                {
                    $ktKrgng2=substr($barisNoakun,0,3);
                }
                $grnTotKeg2+=$totRupiahKegiatan[$thnBudget][$barisNoakun];
                $grnTotKegha2+=$kegHa;
                $grnTotKegSdm2+=$dataKeg[$thnBudget][$barisNoakun]['sdm'];
                $grnTotKeghaSdm2+=$kegSdm;
                $grnTotKegMat2+=$dataKeg[$thnBudget][$barisNoakun]['mat'];
                $grnTotKeghaMat2+=$kegMat;
                $grnTotKegTool2+=$dataKeg[$thnBudget][$barisNoakun]['tool'];
                $grnTotKeghaTool2+=$kegTool;
                $grnTotKegVhc2+=$dataKeg[$thnBudget][$barisNoakun]['vhc'];
                $grnTotKeghaVhc2+=$kegVhc;
                $grnTotKegKntrak2+=$dataKeg[$thnBudget][$barisNoakun]['kntrk'];
                $grnTotKeghaKntrak2+=$kegKntrak;
            }
            if($ktKrgng2=='126')
            {
             $ttlLuastbm=$ttlLuastbm;
             $pdf->Cell(208,$height,$_SESSION['lang']['total']." TBM",1,0,'R',1); 
            }
            else if($ktKrgng2=='128')
            {
               $ttlLuastbm=$ttlLuasPkk;
               $pdf->Cell(208,$height,$_SESSION['lang']['total']." BIBITAN",1,0,'R',1);
            }
            else if($ktKrgng2=='6')
            {
                $ttlLuastbm=$ttlLuastm;
               $pdf->Cell(208,$height,$_SESSION['lang']['total']." TM",1,0,'R',1);
            }
            $xPertama=$pdf->GetX();
            $pdf->SetX($xPertama);
            @$hsilBagi=$ttlLuastbm>0?$totRupiah[$thnBudget][$ktKrgng2]/$ttlLuastbm:0;
            $pdf->Cell(50,10,numb_format($totRupiah[$thnBudget][$ktKrgng2],0),1,0,'R',1);
            $pdf->Cell(50,10,numb_format($hsilBagi,0),1,0,'L',1);
            //$pdf->Cell(500,$height,"",1,1,'C',1);
            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['sdm'],2),1,0,'R',1);
            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['sdm']/$ttlLuastbm):0),2),1,0,'L',1);
            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['mat'],2),1,0,'R',1);
            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['mat']/$ttlLuastbm):0),2),1,0,'L',1);
            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['tool'],2),1,0,'R',1);
            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['tool']/$ttlLuastbm):0),2),1,0,'L',1);
            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['vhc'],2),1,0,'R',1);
            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['vhc']/$ttlLuastbm):0),2),1,0,'L',1);
            $pdf->Cell(50,10,numb_format($totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk'],2),1,0,'R',1);
            $pdf->Cell(50,10,numb_format(($ttlLuastbm>0?(@$totalKplaSDM[$thnBudget][$ktKrgng2]['kntrk']/$ttlLuastbm):0),2),1,1,'L',1);
            $pdf->Cell(208,$height,$_SESSION['lang']['grnd_total'],1,0,'R',1);
            $pdf->Cell(50,10,numb_format($grnTotKeg2,0),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($grnTotKegha2,0),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($grnTotKegSdm2,0),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($grnTotKeghaSdm2,0),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($grnTotKegMat2,0),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($grnTotKeghaMat2,0),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($grnTotKegTool2,0),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($grnTotKeghaTool2,0),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($grnTotKegVhc2,0),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($grnTotKeghaVhc2,0),1,0,'L',1);
                $pdf->Cell(50,10,numb_format($grnTotKegKntrak2,0),1,0,'R',1);
                $pdf->Cell(50,10,numb_format($grnTotKeghaKntrak2,0),1,1,'L',1);
               
            $pdf->Output();	
            break;
                
            default:
            break;
        }
	function numb_format($a,$d=0){
		$n = hidezerodecimal($a,$d);
		if($n=='0' or $n==''){
			$n="";
		}else{
			$n=$n;
		}
		return $n;
	}	
?>
