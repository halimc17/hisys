<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');

$kdorg=checkPostGet('kdorg','');
$thn=checkPostGet('thn','');

$perlist=month_inbetween($thn.'-01',$thn.'-12');

$iProdr="select * from ".$dbname.".pabrik_produksi_vw where tahun='".$thn."' and kodeorg='".$kdorg."' ";
$nProdr=$owlPDO->query($iProdr) or die(print " Gagal: ".PDOException::getMessage());
$nProdr->setFetchMode(PDO::FETCH_ASSOC);
while($dProdr=$nProdr->fetch()){
    $tbsr[$dProdr['periode']]['R']=$dProdr['tbsdiolahton'];
    $cpor[$dProdr['periode']]['R']=$dProdr['cpoton'];
    $pkr[$dProdr['periode']]['R']=$dProdr['pkton'];
}

$olah=$kgcpo=$kgker=$rp="";

for($i=1;$i<=12;$i++)
{
    if(strlen($i)<2)
    {
        $i='0'.$i;
    }
    if($i==12)
    {
        $olah.='sum(olah'.$i.') as olah'.$i;
        $kgcpo.='sum(kgcpo'.$i.') as kgcpo'.$i;
        $kgker.='sum(kgker'.$i.') as kgker'.$i;
        $rp.='sum(rp'.$i.') as rp'.$i;
    }
    else
    {
        
        $olah.='sum(olah'.$i.') as olah'.$i.',';   
        $kgcpo.='sum(kgcpo'.$i.') as kgcpo'.$i.',';
        $kgker.='sum(kgker'.$i.') as kgker'.$i.',';
        $rp.='sum(rp'.$i.') as rp'.$i.',';
    }
    
}


$iBgt="select ".$olah.",".$kgcpo.",".$kgker." "
        . " from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".$thn."' and millcode='".$kdorg."'";
$nBgt=$owlPDO->query($iBgt) or die(print " Gagal: ".PDOException::getMessage());
$nBgt->setFetchMode(PDO::FETCH_ASSOC);
while($dBgt=$nBgt->fetch())
{
    for($i=1;$i<=12;$i++)
    {
        if(strlen($i)<2)
        {
            $i='0'.$i;
        }
        $per=$thn.'-'.$i;
        $olah='olah'.$i;
        $kgcpo='kgcpo'.$i;
        $kgker='kgker'.$i;
        
        
        $tbsb[$per]['B']=$dBgt[$olah]/1000;
        $cpob[$per]['B']=$dBgt[$kgcpo]/1000;
        $pkb[$per]['B']=$dBgt[$kgker]/1000;
    }
}



##ambil sumber akun
$iAkun="select * from ".$dbname.".keu_5mesinlaporandt where "
        . " namalaporan='MILL COST'  and tipe='detail' order by nourut asc";
$jumlahnourut=0;
$nAkun=$owlPDO->query($iAkun) or die(print " Gagal: ".PDOException::getMessage());
$nAkun->setFetchMode(PDO::FETCH_ASSOC);
while($dAkun=$nAkun->fetch())
{ 
    $jumlahnourut+=1;
    $mlnurut[$dAkun['nourut']]=$dAkun['nourut'];
    $mlnama[$dAkun['nourut']]=$dAkun['keterangandisplay'];
    $mlnoakundari[$dAkun['nourut']]=$dAkun['noakundari'];
    $mlnoakunsampai[$dAkun['nourut']]=$dAkun['noakunsampai'];
    $mlnoakundisplay[$dAkun['nourut']]=$dAkun['noakundisplay'];
    //$dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
}



$where="kodeorg='".$kdorg."' and periode like '".$thn."%'";

/*foreach($mlnurut as $nourutlaporan)
{
    $noakuntidak[$nourutlaporan]=explode(",",$mlnoakundisplay[$nourutlaporan]);
    if($noakuntidak[$nourutlaporan][0]!='' && $noakuntidak[$nourutlaporan][1]=='')
    {
        $where2[$nourutlaporan]=" and noakun not in ('".$noakuntidak[$nourutlaporan][0]."')";
    }
    else if($noakuntidak[$nourutlaporan][0]!='' && $noakuntidak[$nourutlaporan][1]!='')
    {
        $where2[$nourutlaporan]=" and noakun not in ('".$noakuntidak[$nourutlaporan][0]."','".$noakuntidak[$nourutlaporan][1]."')";
    }
}*/



foreach($mlnurut as $nourutlaporan)
{
    /*$noakuntidak[$nourutlaporan]=explode(",",$mlnoakundisplay[$nourutlaporan]);
    $jumlahnoakuntidak[$nourutlaporan]=$noakuntidak[$nourutlaporan]++;
    
    $count[$nourutlaporan]=0;
    $count[$nourutlaporan]=count($noakuntidak[$nourutlaporan]);
    */
    
    $noakuntidak[$nourutlaporan]=explode(",",$mlnoakundisplay[$nourutlaporan]);
    
    
    if($mlnoakundisplay[$nourutlaporan]!='' || $mlnoakundisplay[$nourutlaporan]!='0')
    {
        $jum[$nourutlaporan]=  count($noakuntidak[$nourutlaporan])-1;
        $where2=" and noakun not in (";
        $penutupwhere2=")";
    }
    
    
    
    for($i=0;$i<=$jum[$nourutlaporan];$i++)
    {
        if($jum[$nourutlaporan]==0)
        {
            $where2.=" '".$noakuntidak[$nourutlaporan][$i]."' ";
        }
        else
        {
            if($i==$jum[$nourutlaporan])
            {
                $where2.=" '".$noakuntidak[$nourutlaporan][$i]."' ";
            }
            else
            {
                $where2.=" '".$noakuntidak[$nourutlaporan][$i]."', ";
            }
        }
    }
    
    $isiwhere2[$nourutlaporan]=$where2.$penutupwhere2;
    
    
    
    
    
    
    /*if($noakuntidak[$nourutlaporan][0]!='' && $noakuntidak[$nourutlaporan][1]=='')
    {
        $where2[$nourutlaporan]=" and noakun not in ('".$noakuntidak[$nourutlaporan][0]."')";
    }
    else if($noakuntidak[$nourutlaporan][0]!='' && $noakuntidak[$nourutlaporan][1]!='')
    {
        $where2[$nourutlaporan]=" and noakun not in ('".$noakuntidak[$nourutlaporan][0]."','".$noakuntidak[$nourutlaporan][1]."')";
    }*/
    /*switch ($nourutlaporan) {
        case '1060':
            //print_r($mlnoakundisplay);
            
          //  echo ;
            if(!empty($noDtakun)){
                $addEx=" and left(noakun,5) not between '".$noDtakun[0]."' and '".$noDtakun[1]."'";    
            }*/
            
    $ijumakun="select sum(jumlah) as jumlah,periode from ".$dbname.".keu_jurnaldt_vw where "
        . " ".$where." and noakun between '".$mlnoakundari[$nourutlaporan]."' "
        . " and '".$mlnoakunsampai[$nourutlaporan]."' ".$isiwhere2[$nourutlaporan]." group by periode ";
    $njumakun=$owlPDO->query($ijumakun) or die(print " Gagal: ".PDOException::getMessage());
    $njumakun->setFetchMode(PDO::FETCH_ASSOC);
    while($djumakun=$njumakun->fetch()){
        $isidata[$nourutlaporan][$djumakun['periode']]['R']=$djumakun['jumlah'];
    }
    $iBgtrp="select ".$rp." from ".$dbname.".bgt_budget_detail "
            . " where tahunbudget='".$thn."' and kodeorg like '".$kdorg."%'"
            . " and noakun between '".$mlnoakundari[$nourutlaporan]."' "
            . " and '".$mlnoakunsampai[$nourutlaporan]."' ".$isiwhere2[$nourutlaporan]." ";
    $nBgtrp=$owlPDO->query($iBgtrp) or die(print " Gagal: ".PDOException::getMessage());
    $nBgtrp->setFetchMode(PDO::FETCH_ASSOC);
    while($dBgtrp=$nBgtrp->fetch()){
        for($i=1;$i<=12;$i++)
        {
            if(strlen($i)<2)
            {
                $i='0'.$i;
            }
            $per=$thn.'-'.$i;
            $rpdata='rp'.$i;
            $bgtrp[$nourutlaporan][$per]['B']=$dBgtrp[$rpdata];
        }
    }           
}





#query budget biaya
if ($proses == 'excel') 
{
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else 
{
    $stream = "<table class=sortable cellspacing=1 width=3200px>";
}

$stream.="<thead>
  <tr class=rowheader>
    <td rowspan=2 bgcolor=#CCCCCC align=center>No</td>
    <td rowspan=2 bgcolor=#CCCCCC align=center>".$_SESSION['lang']['uraian']."</td>";
for($u=1;$u<=12;$u++)
{
    if(strlen($u)<2)
    {
        $u='0'.$u;
    }
    $stream.="<td colspan=3 bgcolor=#CCCCCC align=center>".  strtoupper(numToMonth($u, 'I', 'short'))."</td>";
}
$stream.="<td colspan=4 bgcolor=#CCCCCC align=center>".strtoupper($_SESSION['lang']['total'])."</td>";


$stream.="</tr>";
$stream.="<tr>";
for($u=1;$u<=13;$u++)
{
    $stream.="  <td align=center bgcolor=#CCCCCC>Budget</td>
                <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['realisasi']."</td>
                <td align=center bgcolor=#CCCCCC>".$_SESSION['lang']['selisih']."</td>";
}
$stream.="<td align=center>%</td>";
$stream.="</tr>";
$stream.="</tr>";
$stream.="</thead>";


$stream.="<tr class=rowcontent>";
    $stream.="<td colspan=42><b>".strtoupper($_SESSION['lang']['produksi'])."</b></td>";
    $stream.="</tr>";
$stream.="<tr class=rowcontent>";
    $stream.="<td  align=center>1</td>";
    $stream.="<td>".$_SESSION['lang']['tbsdiolah']." (Ton)</td>";
    $ttbsb=$ttbsr="";
    foreach($perlist as $listper)
    {
        setIt($tbsb[$listper]['B'],'');
        setIt($tbsr[$listper]['R'],'');
        @$selisih=$tbsr[$listper]['R']-$tbsb[$listper]['B'];
        $stream.="
              <td align=right>".number_format((float)$tbsb[$listper]['B'],2)."</td>  
              <td align=right>".number_format((float)$tbsr[$listper]['R'],2)."</td>
              <td align=right>".number_format((float)$selisih,2)."</td>     
            ";
        $ttbsb+=$tbsb[$listper]['B'];
        $ttbsr+=$tbsr[$listper]['R'];
    }
    $selisih=$ttbsr-$ttbsb;
    @$persentbs=$selisih/$ttbsb*100;
    $stream.="
              <td align=right>".number_format((float)$ttbsb,2)."</td>  
              <td align=right>".number_format((float)$ttbsr,2)."</td>
              <td align=right>".number_format((float)$selisih,2)."</td>    
              <td align=right>".number_format((float)$persentbs,2)."%</td>         
            ";
    $stream.="</tr>";
    
    
$stream.="<tr class=rowcontent>";
    $stream.="<td align=center>2</td>";
    $stream.="<td>CPO (Ton)</td>";
    $tcpob=$tcpor="";
    foreach($perlist as $listper)
    {
        setIt($cpob[$listper]['B'],'');
        setIt($cpor[$listper]['R'],'');
        $selisih=$cpor[$listper]['R']-$cpob[$listper]['B'];
        $stream.="
              <td align=right>".number_format((float)$cpob[$listper]['B'],2)."</td>  
              <td align=right>".number_format((float)$cpor[$listper]['R'],2)."</td>
              <td align=right>".number_format((float)$selisih,2)."</td>
            ";
        $tcpob+=$cpob[$listper]['B'];
        $tcpor+=$cpor[$listper]['R'];
    }
    @$selisih=$tcpor-$tcpob;
    @$persencpo=$selisih/$tcpob*100;
    $stream.="
              <td align=right>".number_format((float)$tcpob,2)."</td>  
              <td align=right>".number_format((float)$tcpor,2)."</td>
              <td align=right>".number_format((float)$selisih,2)."</td>    
              <td align=right>".number_format((float)$persencpo,2)."%</td>         
            ";
    $stream.="</tr>";
    
    
    
   ///indrakerenel 
$stream.="<tr class=rowcontent>";
    $stream.="<td align=center>3</td>";
    $stream.="<td>KERNEL (Ton)</td>";
    $tpkb=$tpkr="";
    foreach($perlist as $listper)
    {
        setIt($pkb[$listper]['B'],'');
        setIt($pkr[$listper]['R'],'');
        @$selisih=$pkr[$listper]['R']-$pkb[$listper]['B'];
        $stream.="
              <td align=right>".number_format((float)$pkb[$listper]['B'],2)."</td>  
              <td align=right>".number_format((float)$pkr[$listper]['R'],2)."</td>
              <td align=right>".number_format((float)$selisih,2)."</td>    
            ";
        $tpkb+=$pkb[$listper]['B'];
        $tpkr+=$pkr[$listper]['R'];
    }
    @$selisih=$tpkr-$tpkb;
    @$persenpk=$selisih/$tpkb*100;
    $stream.="
              <td align=right>".number_format((float)$tpkb,2)."</td>  
              <td align=right>".number_format((float)$tpkr,2)."</td>
              <td align=right>".number_format((float)$selisih,2)."</td>    
              <td align=right>".number_format((float)$persenpk,2)."%</td>         
            ";
    $stream.="</tr>";
    
    
$stream.="<tr class=rowcontent>";
    $stream.="<td align=center>4</td>";
    $stream.="<td>TOTAL PALM PRODUCT</td>";//totalpp
    $ttotpalmb=$ttotpalmr="";
    foreach($perlist as $listper)
    {
        $totpalmb[$listper]['B']=$cpob[$listper]['B']+$pkb[$listper]['B'];
        $totpalmr[$listper]['R']=$cpor[$listper]['R']+$pkr[$listper]['R'];
        $selisih=$totpalmr[$listper]['R']-$totpalmb[$listper]['B'];
        $stream.="
            <td align=right>".number_format((float)$totpalmb[$listper]['B'],2)."</td>  
            <td align=right>".number_format((float)$totpalmr[$listper]['R'],2)."</td>  
            <td align=right>".number_format((float)$selisih,2)."</td>    
        ";
        @$ttotpalmb+=$totpalmb[$listper]['B'];
        @$ttotpalmr+=$totpalmr[$listper]['R'];
    }
    @$selisih=$ttotpalmr-$ttotpalmb;
    @$persentotpalm=$selisih/$ttotpalmb*100;
    $stream.="
              <td align=right>".number_format((float)$ttotpalmb,2)."</td>  
              <td align=right>".number_format((float)$ttotpalmr,2)."</td>
              <td align=right>".number_format((float)$selisih,2)."</td>    
              <td align=right>".number_format((float)$persentotpalm,2)."%</td>         
            ";
    $stream.="</tr>";
    
    
$stream.="<tr class=rowcontent>";
    $stream.="<td colspan=42><b>RENDEMEN</b></td>";
    $stream.="</tr>";
    
$stream.="<tr class=rowcontent>";
    $stream.="<td align=center>1</td>";
    $stream.="<td>CPO</td>";
    foreach($perlist as $listper)
    {
        
        @$oerCpob=$cpob[$listper]['B']/$tbsb[$listper]['B']*100;
        @$oerCpor=$cpor[$listper]['R']/$tbsr[$listper]['R']*100;
        @$selisih=$oerCpor-$oerCpob;
        $stream.="
            <td align=right>".number_format((float)$oerCpob,2)."%</td>  
            <td align=right>".number_format((float)$oerCpor,2)."%</td>     
            <td align=right>".number_format((float)$selisih,2)."%</td>    
        ";
    }
    @$toerCpob=$tcpob/$ttbsb*100;
    @$toerCpor=$tcpor/$ttbsr*100;
    @$selisih=$toerCpor-$toerCpob;
    @$persenoerCpo=$selisih/$toerCpob*100;
    
    $stream.="
              <td align=right>".number_format((float)$toerCpob,2)."%</td>  
              <td align=right>".number_format((float)$toerCpor,2)."%</td>
              <td align=right>".number_format((float)$selisih,2)."%</td>    
              <td align=right>".number_format((float)$persenoerCpo,2)."%</td>         
            ";
    $stream.="</tr>";
    
   
    
    
    
    
$stream.="<tr class=rowcontent>";
    $stream.="<td align=center>2</td>";
    $stream.="<td>KERNEL</td>";
    foreach($perlist as $listper)
    {
        @$oerPkb=$pkb[$listper]['B']/$tbsb[$listper]['B']*100;
        @$oerPkr=$pkr[$listper]['R']/$tbsr[$listper]['R']*100;
        @$selisih=$oerPkb-$oerPkr;
        $stream.="
            <td align=right>".number_format((float)$oerPkb,2)."%</td>  
            <td align=right>".number_format((float)$oerPkr,2)."%</td>     
            <td align=right>".number_format((float)$selisih,2)."%</td>    
        ";
    }
    @$toerPkb=$tpkb/$ttbsb*100;
    @$toerPkr=$tpkr/$ttbsr*100;
    @$selisih=$toerPkr-$toerPkb;
    @$persenoerPk=$selisih/$toerPkb*100;
    
    $stream.="
              <td align=right>".number_format((float)@$toerPkb,2)."%</td>  
              <td align=right>".number_format((float)@$toerPkr,2)."%</td>
              <td align=right>".number_format((float)@$selisih,2)."%</td>    
              <td align=right>".number_format((float)@$persenoerPk,2)."%</td>         
            ";
    $stream.="</tr>";
    
    
    
    
    
///indra mill cost   
@$jumlahnourut=$jumlahnourut+1;
$stream.="<tr class=rowcontent style=cursor:pointer; title='clickdetail' onclick=lihatDetail('".$kdorg."','".$thn."','','html',event)>";                    
//$stream.="<tr class=rowcontent>";
    $stream.="<td colspan=42><b>MILL COST</b></td>";
    $stream.="</tr>";
$noHead=0;    
foreach($mlnurut as $nourutlaporan)
{
    $noHead+=1;
    //$stream.="<tr class=rowcontent>";
    $stream.="<tr class=rowcontent style=cursor:pointer; title='clickdetail' onclick=lihatDetail('".$kdorg."','".$thn."','".$nourutlaporan."','html',event)>";                    
    $stream.="<td align=center>".$noHead."</td>";//indra
    $stream.="<td>".$mlnama[$nourutlaporan]."</td>";
    
    foreach($perlist as $listper)
    {
        //$isidata[$nourutlaporan][$djumakun['periode']]=$djumakun['jumlah'];
        @$selisih=$isidata[$nourutlaporan][$listper]['R']-$bgtrp[$nourutlaporan][$listper]['B'];
        $stream.="<td align=right>".number_format((float)@$bgtrp[$nourutlaporan][$listper]['B'])."</td>";
        $stream.="<td align=right>".number_format((float)@$isidata[$nourutlaporan][$listper]['R'])."</td>";
        $stream.="<td align=right>".number_format((float)@$selisih)."</td>";
        
        @$subtotbgtrp[$nourutlaporan]+=$bgtrp[$nourutlaporan][$listper]['B'];
        @$subtotisidata[$nourutlaporan]+=$isidata[$nourutlaporan][$listper]['R'];
        
        @$totbgtrp[$listper]+=$bgtrp[$nourutlaporan][$listper]['B'];
        @$totisidata[$listper]+=$isidata[$nourutlaporan][$listper]['R'];
        
    }
    @$selisih=$subtotisidata[$nourutlaporan]-$subtotbgtrp[$nourutlaporan];
    @$persenrp=$selisih/$subtotbgtrp[$nourutlaporan]*100;
    
    $stream.="
        <td align=right>".number_format((float)@$subtotbgtrp[$nourutlaporan],2)."</td>  
        <td align=right>".number_format((float)@$subtotisidata[$nourutlaporan],2)."</td>
        <td align=right>".number_format((float)@$selisih,2)."</td>    
        <td align=right>".number_format((float)@$persenrp,2)."%</td>         
      ";
    $stream.="</tr>";  
    
    @$gbgtrp+=$subtotbgtrp[$nourutlaporan];
    @$gtotisidata+=$subtotisidata[$nourutlaporan];
    @$gtotselisih+=$selisih;
    
}

$stream.="<tr class=rowcontent style=cursor:pointer; title='clickdetail' onclick=lihatDetail('".$kdorg."','".$thn."','','html',event)>";                    
    $stream.="<td colspan=2 align=right><b>".strtoupper($_SESSION['lang']['total'])."</b></td>";
    foreach($perlist as $listper)
    {
        @$selisih=$totisidata[$listper]-$totbgtrp[$listper];
        $stream.="<td align=right>".number_format((float)@$totbgtrp[$listper],2)."</td>";
        $stream.="<td align=right>".number_format((float)@$totisidata[$listper],2)."</td>";
        $stream.="<td align=right>".number_format((float)@$selisih,2)."</td>";
    } 
    
    @$persengtotrp=$gtotselisih/$gbgtrp*100;
    
    $stream.="
        <td align=right>".number_format((float)@$gbgtrp,2)."</td>  
        <td align=right>".number_format((float)@$gtotisidata,2)."</td>
        <td align=right>".number_format((float)@$gtotselisih,2)."</td>    
        <td align=right>".number_format((float)@$persengtotrp,2)."%</td>         
      ";
    $stream.="</tr>";
    
    
    
#############################################################################################
############### uiniiiiiiiiiiiiiiiiiiiiiiii
#############################################################################################
    

$stream.="<tr class=rowcontent>";
    $stream.="<td colspan=42><b>COST/KG</b></td>";
    $stream.="</tr>";
    
$stream.="<tr class=rowcontent>
    <td rowspan='".$jumlahnourut."'  align=center>Cost /Kg TBS Olah</td>";
$noHead=0;
foreach($mlnurut as $nourutlaporan)
{
    $noHead+=1;
    
    
    if($noHead==1)
    {
        $stream.="<td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$tbsb[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$tbsr[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
        @$subtotcostpertbsbgt=$subtotbgtrp[$nourutlaporan]/$ttbsb/1000;
        @$subtotcostpertbsreal=$subtotisidata[$nourutlaporan]/$ttbsr/1000;
        @$selisih=$subtotcostpertbsreal-$subtotcostpertbsbgt;
        @$persen=$selisih/$subtotcostpertbsbgt*100;
            $stream.="<td align=right>".number_format((float)$subtotcostpertbsbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotcostpertbsreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
    else
    {
    $stream.="
        <tr class=rowcontent>
          <td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$tbsb[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$tbsr[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
            @$subtotcostpertbsbgt=$subtotbgtrp[$nourutlaporan]/$ttbsb/1000;
            @$subtotcostpertbsreal=$subtotisidata[$nourutlaporan]/$ttbsr/1000;
            @$selisih=$subtotcostpertbsreal-$subtotcostpertbsbgt;
            @$persen=$selisih/$subtotcostpertbsbgt*100;
            $stream.="<td align=right>".number_format((float)$subtotcostpertbsbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotcostpertbsreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
} 
$stream.="<tr class=rowcontent>
            <td>".strtoupper($_SESSION['lang']['total'])."</td>";
            foreach($perlist as $listper)
            {
                @$subtotbgt[$listper]+=$totbgtrp[$listper]/$tbsb[$listper]['B']/1000;
                @$subtotreal[$listper]+=$totisidata[$listper]/$tbsr[$listper]['R']/1000;
                @$selisih=$subtotreal[$listper]-$subtotbgt[$listper];
                $stream.="<td align=right>".number_format((float)$subtotbgt[$listper],2)."</td>";
                $stream.="<td align=right>".number_format((float)$subtotreal[$listper],2)."</td>";
                $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            }
            @$gtotbgt=$gbgtrp/$ttbsb/1000;
            @$gtotreal=$gtotisidata/$ttbsr/1000;
            @$selisih=$gtotreal-$gtotbgt;
            @$persen=$selisih/$gtotbgt*100;
            $stream.="<td align=right>".number_format((float)$gtotbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$gtotreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
$stream.="</tr>";
   






###no 2################################################################################
$stream.="<tr class=rowcontent>
    <td rowspan='".$jumlahnourut."' align=center>Cost /Kg CPO</td>";
$noHead=0;
foreach($mlnurut as $nourutlaporan)
{
    $noHead+=1;
    if($noHead==1)
    {
        $stream.="<td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$cpob[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$cpor[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
            @$subtotcostpercpobgt=$subtotbgtrp[$nourutlaporan]/$tcpob/1000;
            @$subtotcostpercporeal=$subtotisidata[$nourutlaporan]/$tcpor/1000;
            @$selisih=$subtotcostpercporeal-$subtotcostpercpobgt;
            @$persen=$selisih/$subtotcostpercpobgt*100;
            $stream.="<td align=right>".number_format((float)$subtotcostpercpobgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotcostpercporeal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
    else
    {
    $stream.="
        <tr class=rowcontent>
          <td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$cpob[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$cpor[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
            @$subtotcostpercpobgt=$subtotbgtrp[$nourutlaporan]/$tcpob/1000;
            @$subtotcostpercporeal=$subtotisidata[$nourutlaporan]/$tcpor/1000;
            @$selisih=$subtotcostpercporeal-$subtotcostpercpobgt;
            @$persen=$selisih/$subtotcostpercpobgt*100;
            $stream.="<td align=right>".number_format((float)$subtotcostpercpobgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotcostpercporeal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
} 

$stream.="<tr class=rowcontent>
            <td>".strtoupper($_SESSION['lang']['total'])."</td>";
            foreach($perlist as $listper)
            {
                @$subtotbgt[$listper]=$totbgtrp[$listper]/$cpob[$listper]['B']/1000;
                @$subtotreal[$listper]=$totisidata[$listper]/$cpor[$listper]['R']/1000;
                @$selisih=$subtotreal[$listper]-$subtotbgt[$listper];
                $stream.="<td align=right>".number_format((float)$subtotbgt[$listper],2)."</td>";
                $stream.="<td align=right>".number_format((float)$subtotreal[$listper],2)."</td>";
                $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            }
            @$gtotbgt=$gbgtrp/$tcpob/1000;
            @$gtotreal=$gtotisidata/$tcpor/1000;
            @$selisih=$gtotreal-$gtotbgt;
            @$persen=$selisih/$gtotbgt*100;
            $stream.="<td align=right>".number_format((float)$gtotbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$gtotreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
$stream.="</tr>";


##########################################
##############    33333333    ############
##########################################
$stream.="<tr class=rowcontent>
    <td rowspan='".$jumlahnourut."' align=center>Cost /Kg Kernel</td>";
$noHead=0;
foreach($mlnurut as $nourutlaporan)
{
    $noHead+=1;
    if($noHead==1)
    {
        $stream.="<td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$pkb[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$pkr[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
            @$subtotcostperpkbgt=$subtotbgtrp[$nourutlaporan]/$tpkb/1000;
            @$subtotcostperpkreal=$subtotisidata[$nourutlaporan]/$tpkr/1000;
            @$selisih=$subtotcostperpkreal-$subtotcostperpkbgt;
            @$persen=$selisih/$subtotcostperpkbgt*100;
            $stream.="<td align=right>".number_format((float)$subtotcostperpkbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotcostperpkreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
    else
    {
    $stream.="
        <tr class=rowcontent>
          <td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$pkb[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$pkr[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
            @$subtotcostperpkbgt=$subtotbgtrp[$nourutlaporan]/$tpkb/1000;
            @$subtotcostperpkreal=$subtotisidata[$nourutlaporan]/$tpkr/1000;
            @$selisih=$subtotcostperpkreal-$subtotcostperpkbgt;
            @$persen=$selisih/$subtotcostperpkbgt*100;
            $stream.="<td align=right>".number_format((float)$subtotcostperpkbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotcostperpkreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
} 

$stream.="<tr class=rowcontent>
            <td>".strtoupper($_SESSION['lang']['total'])."</td>";
            foreach($perlist as $listper)
            {
                @$subtotbgt[$listper]=$totbgtrp[$listper]/$pkb[$listper]['B']/1000;
                @$subtotreal[$listper]=$totisidata[$listper]/$pkr[$listper]['R']/1000;
                @$selisih=$subtotreal[$listper]-$subtotbgt[$listper];
                $stream.="<td align=right>".number_format((float)$subtotbgt[$listper],2)."</td>";
                $stream.="<td align=right>".number_format((float)$subtotreal[$listper],2)."</td>";
                $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            }
            @$gtotbgt=$gbgtrp/$tpkb/1000;
            @$gtotreal=$gtotisidata/$tpkr/1000;
            @$selisih=$gtotreal-$gtotbgt;
            @$persen=$selisih/$gtotbgt*100;
            $stream.="<td align=right>".number_format((float)$gtotbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$gtotreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
$stream.="</tr>";



##################################################
###############       44444      #################
##################################################



$stream.="<tr class=rowcontent>
    <td rowspan='".$jumlahnourut."' align=center>Cost / Kg PP</td>";
$noHead=0;
foreach($mlnurut as $nourutlaporan)
{
    $noHead+=1;
    if($noHead==1)
    {
        $stream.="<td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$totpalmb[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$totpalmr[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
            @$subtotbgt=$subtotbgtrp[$nourutlaporan]/$ttotpalmb/1000;
            @$subtotreal=$subtotisidata[$nourutlaporan]/$ttotpalmr/1000;
            @$selisih=$subtotreal-$subtotbgt;
            @$persen=$selisih/$subtotbgt*100;
            $stream.="<td align=right>".number_format((float)$subtotbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
    else
    {
    $stream.="
        <tr class=rowcontent>
          <td>".$mlnama[$nourutlaporan]."</td>";
        foreach($perlist as $listper)
        {
            @$bgt=$bgtrp[$nourutlaporan][$listper]['B']/$totpalmb[$listper]['B']/1000;
            @$real=$isidata[$nourutlaporan][$listper]['R']/$totpalmr[$listper]['R']/1000;
            @$selisih=$real-$bgt;
            $stream.="<td align=right>".number_format((float)$bgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$real,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
        }
            @$subtotbgt=$subtotbgtrp[$nourutlaporan]/$ttotpalmb/1000;
            @$subtotreal=$subtotisidata[$nourutlaporan]/$ttotpalmr/1000;
            @$selisih=$subtotreal-$subtotbgt;
            @$persen=$selisih/$subtotbgt*100;
            $stream.="<td align=right>".number_format((float)$subtotbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
        $stream.="</tr>";
    }
} 

$stream.="<tr class=rowcontent>
            <td>".strtoupper($_SESSION['lang']['total'])."</td>";
            foreach($perlist as $listper)
            {
                @$subtotbgt=$totbgtrp[$listper]/$totpalmb[$listper]['B']/1000;
                @$subtotreal=$totisidata[$listper]/$totpalmr[$listper]['R']/1000;
                @$selisih=$subtotreal-$subtotbgt;
                //$stream.="<td align=right>".$subtotbgt[$listper]." _ ".$totbgtrp[$listper]." _ ".$totpalmb[$listper]['B']."</td>";
                $stream.="<td align=right>".number_format((float)$subtotbgt,2)."</td>";
                $stream.="<td align=right>".number_format((float)$subtotreal,2)."</td>";
                $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            }
            @$gtotbgt=$gbgtrp/$ttotpalmb/1000;
            @$gtotreal=$gtotisidata/$ttotpalmr/1000;
            @$selisih=$gtotreal-$gtotbgt;
            @$persen=$selisih/$gtotbgt*100;
            $stream.="<td align=right>".number_format((float)$gtotbgt,2)."</td>";
            $stream.="<td align=right>".number_format((float)$gtotreal,2)."</td>";
            $stream.="<td align=right>".number_format((float)$selisih,2)."</td>";
            $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";
$stream.="</tr>";


########################################################################################
##prepare data
########################################################################################



$stream.="<tbody></table>";
switch($proses)
{
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="laporan_biaya_pabrik".$kdorg;
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
}
?>