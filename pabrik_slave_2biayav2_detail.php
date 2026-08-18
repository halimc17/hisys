<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

?>
    <link rel=stylesheet type=text/css href=style/generic.css>	
<?

$kdorg=checkPostGet('kdorg','');
$thn=checkPostGet('thn','');
$nourut=checkPostGet('nourut','');
$tipe=checkPostGet('tipe','');
$nourutlaporan=checkPostGet('nourutlaporan','');

$perlist=month_inbetween($thn.'-01',$thn.'-12');

if($tipe=='excel')
{
    $border="border=1";
}
else
{
    $border="border=0";
}

$nourutsort="";
if($nourutlaporan!='')
{ 
    $nourutsort=" and nourut='".$nourutlaporan."'";
}



$namaakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$namaorganisasi=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

echo" Print Excel : <img style=cursor:pointer; "
. " onclick=\"parent.lihatDetail('".$kdorg."','".$thn."','".$nourut."','excel',event)\" src=images/excel.jpg  
    title='MS.Excel'>
   ";

$stream="<table $border class=sortable cellspacing=1>";
$stream.="<thead>
  <tr class=rowheader>
    <td rowspan=2 bgcolor=#CCCCCC align=center>No</td>
    <td rowspan=2 bgcolor=#CCCCCC align=center>Account</td>
    <td rowspan=2 bgcolor=#CCCCCC align=center>Nama Perkiraan</td>";
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
                <td align=center bgcolor=#CCCCCC>Realisasi</td>
                <td align=center bgcolor=#CCCCCC>Selisih</td>";
}
$stream.="<td align=center bgcolor=#CCCCCC>%</td>";
$stream.="</tr>";
$stream.="</tr>";
$stream.="</thead>";
$stream.="<tbody id=container>"; 



##ambil sumber akun
$inourut="select * from ".$dbname.".keu_5mesinlaporandt where "
        . " namalaporan='MILL COST'  and tipe='detail' ".$nourutsort." order by nourut asc";
$jumlahnourut=0;
$nnourut=$owlPDO->query($inourut) or die(print " Gagal: ".PDOException::getMessage());
$nnourut->setFetchMode(PDO::FETCH_ASSOC);
while($dnourut=$nnourut->fetch()){ 
    $mlnourut[$dnourut['nourut']]=$dnourut['nourut'];
    $mlnama[$dnourut['nourut']]=$dnourut['keterangandisplay'];
    $mlnoakundari[$dnourut['nourut']]=$dnourut['noakundari'];
    $mlnoakunsampai[$dnourut['nourut']]=$dnourut['noakunsampai'];
    $mlnoakundisplay[$dnourut['nourut']]=$dnourut['noakundisplay'];
    //$dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
}
$rp="";
for($i=1;$i<=12;$i++)
{
    if(strlen($i)<2)
    {
        $i='0'.$i;
    }
    if($i==12)
    {
        $rp.='sum(rp'.$i.') as rp'.$i;
    }
    else
    {
        $rp.='sum(rp'.$i.') as rp'.$i.',';
    }
    
}


foreach($mlnourut as $nourutlaporan)
{
    
    
    
    /*$noakuntidak[$nourutlaporan]=explode(",",$mlnoakundisplay[$nourutlaporan]);
    if($noakuntidak[$nourutlaporan][0]!='' && $noakuntidak[$nourutlaporan][1]=='')
    {
        $where2[$nourutlaporan]=" and noakun not in ('".$noakuntidak[$nourutlaporan][0]."')";
    }
    else if($noakuntidak[$nourutlaporan][0]!='' && $noakuntidak[$nourutlaporan][1]!='')
    {
        $where2[$nourutlaporan]=" and noakun not in ('".$noakuntidak[$nourutlaporan][0]."','".$noakuntidak[$nourutlaporan][1]."')";
    }*/
    
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
    
    
    
    $iakun="select * from ".$dbname.".keu_5akun where "
        . "  noakun between '".$mlnoakundari[$nourutlaporan]."' "
        . " and '".$mlnoakunsampai[$nourutlaporan]."' ".$isiwhere2[$nourutlaporan]." and detail=1 ";
    $nakun=$owlPDO->query($iakun) or die(print " Gagal: ".PDOException::getMessage());
    $nakun->setFetchMode(PDO::FETCH_ASSOC);
    while($dakun=$nakun->fetch())
    {
        //$isidata[$nourutlaporan][$djumakun['periode']]['R']=$djumakun['jumlah'];
        $noakunlist[$dakun['noakun']]=$dakun['noakun'];
        $noakun[$nourutlaporan][$dakun['noakun']]=$dakun['noakun'];
        $namaakun[$nourutlaporan][$dakun['noakun']]=$dakun['namaakun'];
    }
    
    ##ambil data jurnal
    $ijurnal="select sum(jumlah) as jumlah,periode,noakun from ".$dbname.".keu_jurnaldt_vw where "
            . " kodeorg='".$kdorg."' and periode like '".$thn."%' group by periode,noakun ";
    $njurnal=$owlPDO->query($ijurnal) or die(print " Gagal: ".PDOException::getMessage());
    $njurnal->setFetchMode(PDO::FETCH_ASSOC);
    while($djurnal=$njurnal->fetch())    
    {
        @$jurnal[$nourutlaporan][$djurnal['noakun']][$djurnal['periode']]=$djurnal['jumlah'];
    } 
    
    
    ##
    $ibgt="select noakun,".$rp." from ".$dbname.".bgt_budget_detail "
            . " where tahunbudget='".$thn."' and kodeorg like '".$kdorg."%'"
            . "   group by noakun ";    
    $nbgt=$owlPDO->query($ibgt) or die(print " Gagal: ".PDOException::getMessage());
    $nbgt->setFetchMode(PDO::FETCH_ASSOC);
    while($dbgt=$nbgt->fetch())
    {
        for($i=1;$i<=12;$i++)
        {
            if(strlen($i)<2)
            {
                $i='0'.$i;
            }
            $per=$thn.'-'.$i;
            $rpdata='rp'.$i;
            @$bgt[$nourutlaporan][$dbgt['noakun']][$per]=$dbgt[$rpdata];
        }
        
    }
    
}

foreach($mlnourut as $nourutlaporan)
{
    
    $stream.="<tr class=rowcontent>";
        $stream.="<td colspan=43><b>".$mlnama[$nourutlaporan]."</b></td>";
    $stream.="</tr>";
    $nourutakun=0;
    foreach($noakunlist as $listnoakun)
    {  // $nourutakun++;
        
        
        setIt($noakun[$nourutlaporan][$listnoakun],'');
        
        if($noakun[$nourutlaporan][$listnoakun]!='')
        {
            $nourutakun++;
            $stream.="<tr  class=rowcontent>";
                $stream.="<td align=center>".$nourutakun."</td>";
                $stream.="<td>".$noakun[$nourutlaporan][$listnoakun]."</td>";
                $stream.="<td>".$namaakun[$nourutlaporan][$listnoakun]."</td>";
                foreach($perlist as $listper)
                {
                    setIt($bgt[$nourutlaporan][$listnoakun][$listper],'');
                    setIt($jurnal[$nourutlaporan][$listnoakun][$listper],'');
                    
                    @$selisih[$nourutlaporan][$listnoakun][$listper]=$jurnal[$nourutlaporan][$listnoakun][$listper]-$bgt[$nourutlaporan][$listnoakun][$listper];
                        $stream.="<td align=right>".number_format((float)@$bgt[$nourutlaporan][$listnoakun][$listper],2)."</td>";                   
                        $stream.="<td align=right>".number_format((float)@$jurnal[$nourutlaporan][$listnoakun][$listper],2)."</td>";
                        $stream.="<td align=right>".number_format((float)$selisih[$nourutlaporan][$listnoakun][$listper],2)."</td>";
                    
                    //untuk subtotal di kanan
                    @$subtotbgt[$nourutlaporan][$listnoakun]+=$bgt[$nourutlaporan][$listnoakun][$listper];
                    @$subtotjurnal[$nourutlaporan][$listnoakun]+=$jurnal[$nourutlaporan][$listnoakun][$listper];
                    @$subtotselisih[$nourutlaporan][$listnoakun]+=$selisih[$nourutlaporan][$listnoakun][$listper];
                    
                    //untuk subtotal dibawah
                    @$subtotperbulanbgt[$nourutlaporan][$listper]+=$bgt[$nourutlaporan][$listnoakun][$listper];
                    @$subtotperbulanjurnal[$nourutlaporan][$listper]+=$jurnal[$nourutlaporan][$listnoakun][$listper];
                    @$subtotperbulanselisih[$nourutlaporan][$listper]+=$selisih[$nourutlaporan][$listnoakun][$listper];
                    
                    @$gtotbulanbgt[$listper]+=$bgt[$nourutlaporan][$listnoakun][$listper];
                    @$gtotbulanjurnal[$listper]+=$jurnal[$nourutlaporan][$listnoakun][$listper];
                    @$gtotbulanselisih[$listper]+=$selisih[$nourutlaporan][$listnoakun][$listper];
                    
                }
               
                #bentuk sub total di kanan 
                @$persen[$nourutlaporan][$listnoakun]=$subtotselisih[$nourutlaporan][$listnoakun]/$subtotbgt[$nourutlaporan][$listnoakun]*100;
                $stream.="<td align=right>".number_format((float)$subtotbgt[$nourutlaporan][$listnoakun],2)."</td>";
                $stream.="<td align=right>".number_format((float)$subtotjurnal[$nourutlaporan][$listnoakun],2)."</td>";
                $stream.="<td align=right>".number_format((float)$subtotselisih[$nourutlaporan][$listnoakun],2)."</td>";
                $stream.="<td align=right>".number_format((float)$persen[$nourutlaporan][$listnoakun],2)."%</td>";
            $stream.="</tr>";   
            
            
            
            
        }   
    }
    #bentuk gran total dibawah
    $stream.="<tr class=rowcontent>";
    $stream.="<td colspan=3><b>Total</b></td>";
       
        foreach($perlist as $listper)
        {
            $stream.="<td align=right>".number_format((float)$subtotperbulanbgt[$nourutlaporan][$listper],2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotperbulanjurnal[$nourutlaporan][$listper],2)."</td>";
            $stream.="<td align=right>".number_format((float)$subtotperbulanselisih[$nourutlaporan][$listper],2)."</td>";
            
            @$totalsubtotperbulanbgt[$nourutlaporan]+=$subtotperbulanbgt[$nourutlaporan][$listper];
            @$totalsubtotperbulanjurnal[$nourutlaporan]+=$subtotperbulanjurnal[$nourutlaporan][$listper];
            @$totalsubtotperbulanselisih[$nourutlaporan]+=$subtotperbulanselisih[$nourutlaporan][$listper];
            
        }
    
        @$persen[$nourutlaporan]=$totalsubtotperbulanselisih[$nourutlaporan]/$totalsubtotperbulanbgt[$nourutlaporan]*100;
        
    $stream.="<td align=right>".number_format((float)$totalsubtotperbulanbgt[$nourutlaporan],2)."</td>";
    $stream.="<td align=right>".number_format((float)$totalsubtotperbulanjurnal[$nourutlaporan],2)."</td>";
    $stream.="<td align=right>".number_format((float)$totalsubtotperbulanselisih[$nourutlaporan],2)."</td>";
    $stream.="<td align=right>".number_format((float)$persen[$nourutlaporan],2)."%</td>";
    $stream.="</tr>";        
}

#bentuk grand
$stream.="<tr class=rowcontent>";
$stream.="<td colspan=3><b>".$_SESSION['lang']['grnd_total']."</b></td>";
foreach($perlist as $listper)
{
   
    $stream.="<td align=right>".number_format((float)$gtotbulanbgt[$listper],2)."</td>";
    $stream.="<td align=right>".number_format((float)$gtotbulanjurnal[$listper],2)."</td>";
    $stream.="<td align=right>".number_format((float)$gtotbulanselisih[$listper],2)."</td>";
    
    @$supergtotbgt+=$gtotbulanbgt[$listper];
    @$supergtotjurnal+=$gtotbulanjurnal[$listper];
    @$supergtotselisih+=$gtotbulanselisih[$listper];
    
}
    @$persen=$supergtotselisih/$supergtotbgt*100;
    $stream.="<td align=right>".number_format((float)$supergtotbgt,2)."</td>";
    $stream.="<td align=right>".number_format((float)$supergtotjurnal,2)."</td>";
    $stream.="<td align=right>".number_format((float)$supergtotselisih,2)."</td>";
    $stream.="<td align=right>".number_format((float)$persen,2)."%</td>";


















//=================================================
 
if($tipe=='excel')
{
    // echo $stream;
    $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
    $nop_="detail_transaksi_mill_cost".$kdorg;
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
    
}
else
{
   
   echo $stream;
} 


?>