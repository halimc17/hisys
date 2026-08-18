<?php
// file creator: dhyaz aug 3, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$tahun=$_POST['tahun'];
$pabrik=$_POST['pabrik'];
$method=$_POST['method'];
$tahun = checkPostGet('tahun', '');
$pabrik = checkPostGet('pabrik', '');
$method = checkPostGet('method', '');

$induk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$pabrik."'");

//check, one-two
if($tahun==''){
    echo "WARNING: silakan mengisi tahun."; exit;
}
if($pabrik==''){
    echo "WARNING: silakan mengisi pabrik."; exit;
}

//echo $tahun.$kebun;

// ambil data
    $isidata=array();
$str="select * from ".$dbname.".bgt_produksi_pks_vw where tahunbudget = '".$tahun."' and millcode = '".$pabrik."' order by kodeunit";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$isidata = array();
while($bar=$res->fetch())
{
	$isidata[$bar->kodeunit]['tbstotal']+=$bar->kgolah;
    $isidata[$bar->kodeunit]['tbs01']+=$bar->olah01;
    $isidata[$bar->kodeunit]['tbs02']+=$bar->olah02;
    $isidata[$bar->kodeunit]['tbs03']+=$bar->olah03;
    $isidata[$bar->kodeunit]['tbs04']+=$bar->olah04;
    $isidata[$bar->kodeunit]['tbs05']+=$bar->olah05;
    $isidata[$bar->kodeunit]['tbs06']+=$bar->olah06;
    $isidata[$bar->kodeunit]['tbs07']+=$bar->olah07;
    $isidata[$bar->kodeunit]['tbs08']+=$bar->olah08;
    $isidata[$bar->kodeunit]['tbs09']+=$bar->olah09;
    $isidata[$bar->kodeunit]['tbs10']+=$bar->olah10;
    $isidata[$bar->kodeunit]['tbs11']+=$bar->olah11;
    $isidata[$bar->kodeunit]['tbs12']+=$bar->olah12;
    $isidata[$bar->kodeunit]['cpototal']+=$bar->kgcpo;
    $isidata[$bar->kodeunit]['cpo01']+=$bar->kgcpo01;
    $isidata[$bar->kodeunit]['cpo02']+=$bar->kgcpo02;
    $isidata[$bar->kodeunit]['cpo03']+=$bar->kgcpo03;
    $isidata[$bar->kodeunit]['cpo04']+=$bar->kgcpo04;
    $isidata[$bar->kodeunit]['cpo05']+=$bar->kgcpo05;
    $isidata[$bar->kodeunit]['cpo06']+=$bar->kgcpo06;
    $isidata[$bar->kodeunit]['cpo07']+=$bar->kgcpo07;
    $isidata[$bar->kodeunit]['cpo08']+=$bar->kgcpo08;
    $isidata[$bar->kodeunit]['cpo09']+=$bar->kgcpo09;
    $isidata[$bar->kodeunit]['cpo10']+=$bar->kgcpo10;
    $isidata[$bar->kodeunit]['cpo11']+=$bar->kgcpo11;
    $isidata[$bar->kodeunit]['cpo12']+=$bar->kgcpo12;
    $isidata[$bar->kodeunit]['kertotal']+=$bar->kgkernel;
    $isidata[$bar->kodeunit]['ker01']+=$bar->kgker01;
    $isidata[$bar->kodeunit]['ker02']+=$bar->kgker02;
    $isidata[$bar->kodeunit]['ker03']+=$bar->kgker03;
    $isidata[$bar->kodeunit]['ker04']+=$bar->kgker04;
    $isidata[$bar->kodeunit]['ker05']+=$bar->kgker05;
    $isidata[$bar->kodeunit]['ker06']+=$bar->kgker06;
    $isidata[$bar->kodeunit]['ker07']+=$bar->kgker07;
    $isidata[$bar->kodeunit]['ker08']+=$bar->kgker08;
    $isidata[$bar->kodeunit]['ker09']+=$bar->kgker09;
    $isidata[$bar->kodeunit]['ker10']+=$bar->kgker10;
    $isidata[$bar->kodeunit]['ker11']+=$bar->kgker11;
    $isidata[$bar->kodeunit]['ker12']+=$bar->kgker12;
	
	$strx="select * from ".$dbname.".organisasi where kodeorganisasi='".$bar->kodeunit."'";
	$resx = fetchdata($strx);
	if(count($resx)==0){
		$eksternal[$bar->kodeunit]=$bar->kodeunit;
	}
}

$border=0;
if($method=='excel'){
	$border=1;
}

//header
$tab.="<table class=sortable cellspacing=1 border=".$border." width=100% cellpadding=5>
     <thead>
        <tr class=rowtitle>
            <th rowspan=2 align=center>No.</th>
            <th rowspan=2 align=center>".$_SESSION['lang']['asaltbs']."</th>
             <th rowspan=1 colspan=2 align=center>OER(%)</th>   
            <th rowspan=2 align=center>".$_SESSION['lang']['uraian']."</th>
            <th rowspan=2 align=center>".$_SESSION['lang']['satuan']."</th>
            <th rowspan=2 align=center>".$_SESSION['lang']['total']."</th>
            <th colspan=12 align=center>Distribusi Produksi</th>";
       $tab.="<th rowspan=2 align=center>".$_SESSION['lang']['total']."</th>
        </tr>";
       $tab.="<tr>
           <th align=center>CPO</th>
           <th align=center>KER</th>           
           <th align=center>Jan</th>
           <th align=center>Feb</th>
           <th align=center>Mar</th>
           <th align=center>Apr</th>
           <th align=center>May</th>
           <th align=center>Jun</th>
           <th align=center>Jul</th>
           <th align=center>Aug</th>
           <th align=center>Sep</th>
           <th align=center>Oct</th>
           <th align=center>Nov</th>
           <th align=center>Dec</th>
       </tr>";
     
$tab.="</thead>
    <tbody>";
#internal
	$str="select distinct kodeorganisasi from ".$dbname.".organisasi where induk<>'".$induk[$pabrik]."'"; 
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$afiliasi[$bar->kodeorganisasi]=$bar->kodeorganisasi;
	}
#Affiliasi
	$str="select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$induk[$pabrik]."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$internal[$bar->kodeorganisasi]=$bar->kodeorganisasi;
	}
#external
	$str="select distinct supplierid from ".$dbname.".log_5supplier
			  order by supplierid"; // 0 : eksternal
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$eksternal[$bar->supplierid]=$bar->supplierid;
	}

	$no=1;
//body2
if(!empty($internal))foreach($internal as $int) // kodeorg / row
{
    @$olahdata['internal']['tbstotal']+=$isidata[$int]['tbstotal'];  
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        @$olahdata['internal'][$ii]+=$isidata[$int][$ii];    
    }    

    @$olahdata['internal']['cpototal']+=$isidata[$int]['cpototal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$olahdata['internal'][$ii]+=$isidata[$int][$ii];    
    }    

    @$olahdata['internal']['kertotal']+=$isidata[$int]['kertotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$olahdata['internal'][$ii]+=$isidata[$int][$ii];    
    }    
   
    @$olahdata['internal']['paltotal']+=$isidata[$int]['cpototal']+$isidata[$int]['kertotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1){
			$ii="pal0".$i; $jj="cpo0".$i; $kk="ker0".$i; 
        }else{
            $ii="pal".$i; $jj="cpo".$i; $kk="ker".$i;
        }
        @$olahdata['internal'][$ii]+=$isidata[$int][$jj]+$isidata[$int][$kk];    
    }    
 
}
if(!empty($afiliasi))foreach($afiliasi as $afi) // kodeorg / row
{
    @$olahdata['afiliasi']['tbstotal']+=$isidata[$afi]['tbstotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        @$olahdata['afiliasi'][$ii]+=$isidata[$afi][$ii];    
    }    
  
    @$olahdata['afiliasi']['cpototal']+=$isidata[$afi]['cpototal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$olahdata['afiliasi'][$ii]+=$isidata[$afi][$ii];    
    }    

    @$olahdata['afiliasi']['kertotal']+=$isidata[$afi]['kertotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$olahdata['afiliasi'][$ii]+=$isidata[$afi][$ii];    
    }    
  
    @$olahdata['afiliasi']['paltotal']+=$isidata[$afi]['cpototal']+$isidata[$afi]['kertotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1){
            $ii="pal0".$i; $jj="cpo0".$i; $kk="ker0".$i; 
        }else{
            $ii="pal".$i; $jj="cpo".$i; $kk="ker".$i;
        }
        @$olahdata['afiliasi'][$ii]+=$isidata[$afi][$jj]+$isidata[$afi][$kk];    
    }    
 
} 
if(!empty($eksternal))foreach($eksternal as $eks) // kodeorg / row
{
    @$olahdata['eksternal']['tbstotal']+=$isidata[$eks]['tbstotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        @$olahdata['eksternal'][$ii]+=$isidata[$eks][$ii];    
    }    
   
    @$olahdata['eksternal']['cpototal']+=$isidata[$eks]['cpototal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$olahdata['eksternal'][$ii]+=$isidata[$eks][$ii];    
    }    

    @$olahdata['eksternal']['kertotal']+=$isidata[$eks]['kertotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$olahdata['eksternal'][$ii]+=$isidata[$eks][$ii];    
    }    

    @$olahdata['eksternal']['paltotal']+=$isidata[$eks]['cpototal']+$isidata[$eks]['kertotal'];    
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1){
            $ii="pal0".$i; $jj="cpo0".$i; $kk="ker0".$i; 
        }else{
            $ii="pal".$i; $jj="cpo".$i; $kk="ker".$i;
        }
        @$olahdata['eksternal'][$ii]+=$isidata[$eks][$jj]+$isidata[$eks][$kk];    
    }    
   
}

$olahdata['all']['tbstotal']=$olahdata['internal']['tbstotal']+$olahdata['afiliasi']['tbstotal']+$olahdata['eksternal']['tbstotal'];
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        @$olahdata['all'][$ii]+=$olahdata['internal'][$ii]+$olahdata['afiliasi'][$ii]+$olahdata['eksternal'][$ii];
    }    

$olahdata['all']['cpototal']=$olahdata['internal']['cpototal']+$olahdata['afiliasi']['cpototal']+$olahdata['eksternal']['cpototal'];
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$olahdata['all'][$ii]+=$olahdata['internal'][$ii]+$olahdata['afiliasi'][$ii]+$olahdata['eksternal'][$ii];
    }    

$olahdata['all']['kertotal']=$olahdata['internal']['kertotal']+$olahdata['afiliasi']['kertotal']+$olahdata['eksternal']['kertotal'];
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$olahdata['all'][$ii]+=$olahdata['internal'][$ii]+$olahdata['afiliasi'][$ii]+$olahdata['eksternal'][$ii];
    }    

$olahdata['all']['paltotal']=$olahdata['internal']['paltotal']+$olahdata['afiliasi']['paltotal']+$olahdata['eksternal']['paltotal'];
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="pal0".$i; else $ii="pal".$i;
        @$olahdata['all'][$ii]+=$olahdata['internal'][$ii]+$olahdata['afiliasi'][$ii]+$olahdata['eksternal'][$ii]; 
    }    

#jangan emosi harap bersabar

if(!empty($olahdata)){
    $tab.="<tr class=rowcontent>";
    $tab.="<td rowspan=4 valign=middle align=right>1</td>";
    $tab.="<td rowspan=4 valign=middle align=left>Internal</td>";
    @$RCPO=hidezerodecimal($olahdata['internal']['cpototal']/$olahdata['internal']['tbstotal']*100,2);
    @$RKER=hidezerodecimal($olahdata['internal']['kertotal']/$olahdata['internal']['tbstotal']*100,2);
    $tab.="<td align=left rowspan=4>".$RCPO."</td>";
    $tab.="<td align=left rowspan=4>".$RKER."</td>";
    $tab.="<td align=left>TBS</td>";
    $tab.="<td align=left>Ton</td>";
    @$tonTbs['internal']['tbstotal']=$olahdata['internal']['tbstotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($tonTbs['internal']['tbstotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        $tbsOl['internal'][$ii]=$olahdata['internal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($tbsOl['internal'][$ii],2)."</td>";
        @$totTbs['internal']['tbstotal']+=$olahdata['internal'][$ii];
    }   
    @$toNtotTbs['internal']['tbstotal']=$totTbs['internal']['tbstotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNtotTbs['internal']['tbstotal'],2)."</td>";
    $tab.="</tr>";
    
	$tab.="<tr class=rowcontent>";
    $tab.="<td align=left>CPO</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['internal']['cpototal']=$olahdata['internal']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['internal']['cpototal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$toNolahdata['internal'][$ii]=$olahdata['internal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['internal'][$ii],2)."</td>";
        @$tot['internal']['cpototal']+=$olahdata['internal'][$ii];
    }    

    @$toNtot['internal']['cpototal']=$tot['internal']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNtot['internal']['cpototal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Kernel</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['internal']['kertotal']=$olahdata['internal']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['internal']['kertotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$toNolahdata['internal'][$ii]=$olahdata['internal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['internal'][$ii],2)."</td>";
        @$totAll['internal']['kertotal']+=$olahdata['internal'][$ii];
    }    

    @$toNtotAll['internal']['kertotal']=$totAll['internal']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNtotAll['internal']['kertotal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Produk</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['internal']['paltotal']=$olahdata['internal']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['internal']['paltotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="pal0".$i; else $ii="pal".$i;
        @$toNolahdata['internal'][$ii]=$olahdata['internal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['internal'][$ii],2)."</td>";
        @$jmlhSma['internal']['paltotal']+=$olahdata['internal'][$ii];
    }    
    @$toNjmlhSma['internal']['paltotal']=$jmlhSma['internal']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['internal']['paltotal'],2)."</td>";
    $tab.="</tr>";

    $tab.="<tr class=rowcontent>";
    $tab.="<td rowspan=4 valign=middle align=right>2</td>";
    $tab.="<td rowspan=4 valign=middle align=left>Afiliasi</td>";
    @$RCPO=hidezerodecimal($olahdata['afiliasi']['cpototal']/$olahdata['afiliasi']['tbstotal']*100,2);
    @$RKER=hidezerodecimal($olahdata['afiliasi']['kertotal']/$olahdata['afiliasi']['tbstotal']*100,2);
    $tab.="<td align=left rowspan=4>".$RCPO."</td>";
    $tab.="<td align=left rowspan=4>".$RKER."</td>";    
    $tab.="<td align=left>TBS</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['afiliasi']['tbstotal']=$olahdata['afiliasi']['tbstotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi']['tbstotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        @$toNolahdata['afiliasi'][$ii]=$olahdata['afiliasi'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi'][$ii],2)."</td>";
        @$jmlhSma['afiliasi']['tbstotal']+=$olahdata['afiliasi'][$ii];
    }    
    @$toNjmlhSma['afiliasi']['tbstotal']=$jmlhSma['afiliasi']['tbstotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['afiliasi']['tbstotal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>CPO</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['afiliasi']['cpototal']=$olahdata['afiliasi']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi']['cpototal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$toNolahdata['afiliasi'][$ii]=$olahdata['afiliasi'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi'][$ii],2)."</td>";
        @$jmlhSma['afiliasi']['cpototal']+=$olahdata['afiliasi'][$ii];
    }    
    @$toNjmlhSma['afiliasi']['cpototal']=$jmlhSma['afiliasi']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['afiliasi']['cpototal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Kernel</td>";
    $tab.="<td align=left>Ton</td>";
	@$toNolahdata['afiliasi']['kertotal']=$olahdata['afiliasi']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi']['kertotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$toNolahdata['afiliasi'][$ii]=$olahdata['afiliasi'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi'][$ii],2)."</td>";
        @$jmlhSma['afiliasi']['kertotal']+=$olahdata['afiliasi'][$ii];
    }    
    @$toNjmlhSma['afiliasi']['kertotal']=$jmlhSma['afiliasi']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['afiliasi']['kertotal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Produk</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['afiliasi']['paltotal']=$olahdata['afiliasi']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi']['paltotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="pal0".$i; else $ii="pal".$i;
        @$toNolahdata['afiliasi'][$ii]=$olahdata['afiliasi'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['afiliasi'][$ii],2)."</td>";
        @$jmlhSma['afiliasi']['paltotal']+=$olahdata['afiliasi'][$ii];
    }
	@$toNjmlhSma['afiliasi']['paltotal']=$jmlhSma['afiliasi']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['afiliasi']['paltotal'],2)."</td>";
    $tab.="</tr>";

    $tab.="<tr class=rowcontent>";
    $tab.="<td rowspan=4 valign=middle align=right>3</td>";
    $tab.="<td rowspan=4 valign=middle align=left>Eksternal</td>";
    @$RCPO=hidezerodecimal($olahdata['eksternal']['cpototal']/$olahdata['eksternal']['tbstotal']*100,2);
    @$RKER=hidezerodecimal($olahdata['eksternal']['kertotal']/$olahdata['eksternal']['tbstotal']*100,2);
    $tab.="<td align=left rowspan=4>".$RCPO."</td>";
    $tab.="<td align=left rowspan=4>".$RKER."</td>";     
    $tab.="<td align=left>TBS</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['eksternal']['tbstotal']=$olahdata['eksternal']['tbstotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal']['tbstotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        @$toNolahdata['eksternal'][$ii]=$olahdata['eksternal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal'][$ii],2)."</td>";
        @$jmlhSma['eksternal']['tbstotal']+=$olahdata['eksternal'][$ii];
    }    
    @$toNjmlhSma['eksternal']['tbstotal']=$jmlhSma['eksternal']['tbstotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['eksternal']['tbstotal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>CPO</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['eksternal']['cpototal']=$olahdata['eksternal']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal']['cpototal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$toNolahdata['eksternal'][$ii]=$olahdata['eksternal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal'][$ii],2)."</td>";
        @$jmlhSma['eksternal']['cpototal']+=$olahdata['eksternal'][$ii];
    }    
   @$toNjmlhSma['eksternal']['cpototal']=$jmlhSma['eksternal']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['eksternal']['cpototal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Kernel</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['eksternal']['kertotal']=$olahdata['eksternal']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal']['kertotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$toNolahdata['eksternal'][$ii]=$olahdata['eksternal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal'][$ii],2)."</td>";
        @$jmlhSma['eksternal']['kertotal']+=$olahdata['eksternal'][$ii];
    }
    @$toNjmlhSma['eksternal']['kertotal']=$jmlhSma['eksternal']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['eksternal']['kertotal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Produk</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['eksternal']['paltotal']=$olahdata['eksternal']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal']['paltotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="pal0".$i; else $ii="pal".$i;
        @$toNolahdata['eksternal'][$ii]=$olahdata['eksternal'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['eksternal'][$ii],2)."</td>";
        @$jmlhSma['eksternal']['paltotal']+=$olahdata['eksternal'][$ii];
    }    
    @$toNjmlhSma['eksternal']['paltotal']=$jmlhSma['eksternal']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['eksternal']['paltotal'],2)."</td>";
    $tab.="</tr>"; 
    
    $tab.="<tr class=rowcontent>";
    $tab.="<td rowspan=4 valign=middle align=right>&nbsp;</td>";
    $tab.="<td rowspan=4 valign=middle align=left>Grand Total</td>";
    @$RCPO=hidezerodecimal($olahdata['all']['cpototal']/$olahdata['all']['tbstotal']*100,2);
    @$RKER=hidezerodecimal($olahdata['all']['kertotal']/$olahdata['all']['tbstotal']*100,2);
    $tab.="<td align=left rowspan=4>".$RCPO."</td>";
    $tab.="<td align=left rowspan=4>".$RKER."</td>";       
    $tab.="<td align=left>TBS</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['all']['tbstotal']=$olahdata['all']['tbstotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['all']['tbstotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="tbs0".$i; else $ii="tbs".$i;
        @$toNolahdata[all][$ii]=$olahdata['all'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['all'][$ii],2)."</td>";
        @$jmlhSma['all']['tbstotal']+=$olahdata['all'][$ii];
    }    
    @$toNjmlhSma['all'][tbstotal]=$jmlhSma['all']['tbstotal']/1000; 
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['all']['tbstotal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>CPO</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['all']['cpototal']=$olahdata['all']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['all']['cpototal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="cpo0".$i; else $ii="cpo".$i;
        @$toNolahdata['all'][$ii]=$olahdata['all'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['all'][$ii],2)."</td>";
        @$jmlhSma['all']['cpototal']+=$olahdata['all'][$ii];
    }    
    @$toNjmlhSma['all']['cpototal']=$jmlhSma['all']['cpototal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['all']['cpototal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Kernel</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['all']['kertotal']=$olahdata['all']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['all']['kertotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="ker0".$i; else $ii="ker".$i;
        @$toNolahdata['all'][$ii]=$olahdata['all'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['all'][$ii],2)."</td>";
        @$jmlhSma['all']['kertotal']+=$olahdata['all'][$ii];
    }
	@$toNjmlhSma['all']['kertotal']=$jmlhSma['all']['kertotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['all']['kertotal'],2)."</td>";
    $tab.="</tr>";
	
    $tab.="<tr class=rowcontent>";
    $tab.="<td align=left>Produk</td>";
    $tab.="<td align=left>Ton</td>";
    @$toNolahdata['all']['paltotal']=$olahdata['all']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNolahdata['all']['paltotal'],2)."</td>";
    for ($i = 1; $i <= 12; $i++) {
        if(strlen($i)==1)$ii="pal0".$i; else $ii="pal".$i;
        @$toNolahdata['all'][$ii]=$olahdata['all'][$ii]/1000;
        $tab.="<td align=right>".hidezerodecimal($toNolahdata['all'][$ii],2)."</td>";
        @$jmlhSma['all']['paltotal']+=$olahdata['all'][$ii];
    }    
	@$toNjmlhSma['all']['paltotal']= $jmlhSma['all']['paltotal']/1000;
    $tab.="<td align=right>".hidezerodecimal($toNjmlhSma['all']['paltotal'],2)."</td>";
    $tab.="</tr>";       
}

if(empty($olahdata)){
    $tab.="<tr class=rowcontent><td colspan=18>Data tidak tersedia.</td></tr>";
}
$tab.="    </tbody>
         <tfoot>
         </tfoot>		 
   </table>";    
if($method=='excel'){
	$tab.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];
	$nop_ = 'bgt_prd';
	if (strlen($tab) > 0) {
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/' . $file);
				}
			}
			closedir($handle);
		}
		$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
		if (!fwrite($handle, $tab)) {
			echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
			exit;
		} else {
			echo "<script language=javascript1.2>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
		}
		fclose($handle);
	}
}else{
	echo $tab;
}


