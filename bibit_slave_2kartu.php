<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$kodeunit = checkPostGet('kodeunit','');
$kodebatch = checkPostGet('kodebatch','');
$proses = checkPostGet('proses','');

if($kodeunit=='')
{
    exit("Error: Unit code required.".$kodeunit);
}

$where='';
if($kodeunit!='')
    $where=" b.kodeorg like '%".$kodeunit."%'";
if($kodebatch!='')
    $where.=" and a.batch='".$kodebatch."'";

$adadata=false;
//        $str="select batch from ".$dbname.".bibitan_batch_vw
//            where ".$where;
$str="select distinct a.batch from ".$dbname.".bibitan_batch a
    left join ".$dbname.".bibitan_mutasi b on a.batch=b.batch
    where ".$where."
    order by a.batch desc";

if($proses=='excel'){
    $border=1;
    $bg=" bgcolor='#dedede'";
}else{
    $border=0;
    $bg=" ";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$tab='';
while($bar=$res->fetch())
{
    $adadata=true;
    $tab.=$_SESSION['lang']['batch']." : ".$bar->batch."<br>";

    if($_SESSION['language']=='EN'){
           $tab.="A. SEED SELECTION and REJECT"."<br>";    
    }else{
        $tab.="A. SELEKSI KECAMBAH"."<br>";   
    }
    $tab.="<table cellpadding=5 cellspacing=1 border=".$border." class=sortable>
    <thead>
        <tr class=rowheader ".$bg.">
        <th rowspan=2 align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['diterima']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['diterima']."</th>
        <th colspan=2 align=center>".$_SESSION['lang']['afkirbibit']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['ditanam']."</th>
        </tr><tr class=rowheader ".$bg.">    
        <th align=center>".$_SESSION['lang']['jumlah']."</th>   
        <th align=center>%</th>
        </tr>
    </thead><tbody id=containdata>";

    $no=0;
    $sData="select * 
        from ".$dbname.".bibitan_batch_vw where kodeorg like '%".$kodeunit."%' and batch = '".$bar->batch."' 
        ";
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	while($rData=$qData->fetch())
    {
        $no+=1;
        @$persen=100*$rData['jumlahafkir']/$rData['jumlahterima'];
        $ditanam=$rData['jumlahterima']-$rData['jumlahafkir'];
        $tab.="<tr class=rowcontent>";
        if($proses=='excel')$tampiltanggal=$rData['tanggal']; else $tampiltanggal=tanggalnormal($rData['tanggal']);
        $tab.="<td align=center>".$tampiltanggal."</td>";
        $tab.="<td align=right>".number_format($rData['jumlahterima'])."</td>";
        $tab.="<td align=right>".number_format($rData['jumlahafkir'])."</td>";
        $tab.="<td>".number_format($persen,2)."</td>";
        $tab.="<td align=right>".number_format($ditanam)."</td>";
        $tab.="</tr>";
        @$terimaDt+=$rData['jumlahterima'];
        @$afkirDt+=$rData['jumlahafkir'];
        @$dataa[$rData['tanggal']]['tanam']+=$ditanam;
    }
	
	setIt($terimaDt,0);
	setIt($afkirDt,0);
	setIt($dataa[$rData['tanggal']]['tanam'],0);
    if($no==0) {$tab.="<tr class=rowcontent><td colspan=5>No data.</td></tr>";}
    $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['total']."</td>";
    $tab.="<td align=right>".number_format($terimaDt)."</td>";
    $tab.="<td align=right>".number_format(abs($afkirDt))."</td>";
    $tab.="<td colspan=2></td></tr>";
    $tab.="</tbody></table></br>";            

    $tab.="B. PRE NURSERY"."<br>";    
    $datab=array();
    $tab.="<table cellpadding=5 cellspacing=1 border=".$border." class=sortable>
    <thead>
        <tr class=rowheader ".$bg.">
        <th rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['ditanam']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['transplatingbibit']."</th>
        <th colspan=2 align=center>".$_SESSION['lang']['afkirbibit']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['saldo']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['catatan']."</th>
        </tr><tr class=rowheader ".$bg.">    
        <th align=center>".$_SESSION['lang']['jumlah']."</th>   
        <th align=center>%</th>
        </tr>
    </thead><tbody id=containdata>";

    $sData="select * 
        from ".$dbname.".bibitan_mutasi where batch = '".$bar->batch."' and kodeorg like '%PN%'  and post=1
        order by tanggal asc";
    //echo $sData;
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
    while($rData=$qData->fetch())
    {
        $datab[$rData['tanggal']]['tanggal']=$rData['tanggal'];
        if($rData['kodetransaksi']=='TPB')
            @$datab[$rData['tanggal']]['TPB']+=$rData['jumlah'];
        else if($rData['kodetransaksi']=='AFB')
            @$datab[$rData['tanggal']]['AFB']+=$rData['jumlah'];
        else @$datab[$rData['tanggal']]['TMB']+=$rData['jumlah'];
    }

    $no=0;
    if(!empty($datab)) {
        foreach($datab as $data) {
            $no+=1;
            @$persen=100*$data['AFB']/$data['TMB'];
            @$saldo+=$data['TMB']+$data['TPB']+$data['AFB'];
            $tab.="<tr class=rowcontent>";
            if($proses=='excel')$tampiltanggal=$data['tanggal']; else $tampiltanggal=tanggalnormal($data['tanggal']);
            $tab.="<td align=center>".$tampiltanggal."</td>";
            $tab.="<td align=right>".number_format(@$data['TMB'])."</td>";
            $tab.="<td align=right>".number_format(@$data['TPB'])."</td>";
            $tab.="<td align=right>".number_format(@$data['AFB'])."</td>";
            $tab.="<td>".number_format($persen,2)."</td>";
            $tab.="<td align=right>".number_format(@$saldo)."</td>";
            $tab.="<td></td>";
            $tab.="</tr>";
            @$dtmb+=$data['TMB'];
            @$dtpb+=$data['PNB'];
            @$afbd+=$data['AFB'];
        }
	} else {
		$tab.="<tr class=rowcontent><td colspan=7>No data.</td></tr>";
	}
	setIt($dtmb,0);
	setIt($dtpb,0);
	setIt($afbd,0);
    $tab.="<tr class=rowcontent><td >".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($dtmb)."</td>";
        $tab.="<td align=right>".number_format(abs($dtpb))."</td>";
        $tab.="<td align=right>".number_format(abs($afbd))."</td><td colspan=3></td></tr>";
    $tab.="</tbody></table></br>";   

    $tab.="C. MAIN NURSERY"."<br>";    
    $datac=array();
    $tab.="<table cellpadding=5 cellspacing=1 border=".$border." class=sortable>
    <thead>
        <tr class=rowheader ".$bg.">
        <th rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['kodeorg']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['ditanam']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['pengiriman']."</th>
        <th colspan=6 align=center>".$_SESSION['lang']['afkirbibit']."</th>
                
        <th rowspan=2 align=center>".$_SESSION['lang']['saldo']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['almt_kirim']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['catatan']."</th>
        </tr><tr class=rowheader ".$bg.">    
        <th align=center>".$_SESSION['lang']['jumlah']."</th>   
        <th align=center>%</th>
            <th  align=center>".$_SESSION['lang']['ditanam']."</th>
            <th  align=center>".$_SESSION['lang']['sisip']."</th>
            <th  align=center>".$_SESSION['lang']['dijual']."</th>
            <th  align=center>".$_SESSION['lang']['afiliasi']."</th>
        </tr>
    </thead><tbody id=containdata>";

    $sData="select * 
        from ".$dbname.".bibitan_mutasi where batch = '".$bar->batch."' and kodeorg like '%MN%' and post=1
        order by tanggal asc";
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
    while($rData=$qData->fetch())
    {
		setIt($datac[$rData['tanggal']]['TPB'],0);
		setIt($datac[$rData['tanggal']]['AFB'],0);
		setIt($datac[$rData['tanggal']]['PNB'],0);
		setIt($datac[$rData['tanggal']]['TMB'],0);
		setIt($datac[$rData['tanggal']]['Ditanam'],0);
		setIt($datac[$rData['tanggal']]['disisip'],0);
		setIt($datac[$rData['tanggal']]['dijual'],0);
		setIt($datac[$rData['tanggal']]['afiliasi'],0);
		setIt($datac[$rData['tanggal']]['lokasi'],'');
		setIt($datac[$rData['tanggal']]['kodeorg'],'');
        $datac[$rData['tanggal']]['tanggal']=$rData['tanggal'];
		if($rData['intex']==1) $datac[$rData['tanggal']]['Ditanam']+= $rData['jumlah'];
		if($rData['intex']==2) $datac[$rData['tanggal']]['Disisip']+= $rData['jumlah'];
		if($rData['intex']==3) $datac[$rData['tanggal']]['dijual']+= $rData['jumlah'];
		if($rData['intex']==4) $datac[$rData['tanggal']]['afiliasi']+= $rData['jumlah'];
        if($rData['kodetransaksi']=='TPB')
            $datac[$rData['tanggal']]['TPB']+=$rData['jumlah'];
        else if($rData['kodetransaksi']=='AFB')
            $datac[$rData['tanggal']]['AFB']+=$rData['jumlah'];
        else if($rData['kodetransaksi']=='PNB')
            $datac[$rData['tanggal']]['PNB']+=$rData['jumlah'];
        else $datac[$rData['tanggal']]['TMB']+=$rData['jumlah'];
        $datac[$rData['tanggal']]['lokasi'].=' '.$rData['lokasipengiriman'];
        $datac[$rData['tanggal']]['kodeorg']=$rData['kodeorg'];
    }

    $no=$saldo=0;
    if(!empty($datac)) 
        foreach($datac as $data) {
            $no+=1;
           
            $saldo+=$data['TMB']+$data['TPB']+$data['AFB']+$data['PNB'];
            @$persen=($data['AFB']/$saldo)*100;
            $tab.="<tr class=rowcontent>";
            if($proses=='excel')$tampiltanggal=$data['tanggal']; else $tampiltanggal=tanggalnormal($data['tanggal']);
            $tab.="<td align=center>".$tampiltanggal."</td>";
            $tab.="<td align=left>".$data['kodeorg']."</td>";
            $tab.="<td align=right>".number_format(abs($data['TMB']))."</td>";
            $tab.="<td align=right>".number_format(abs($data['PNB']))."</td>";
            $tab.="<td align=right>".number_format(abs($data['AFB']))."</td>";
            $tab.="<td>".number_format($persen,2)."</td>";
                $tab.="<td align=right>".number_format(abs(@$data['Ditanam']))."</td>";
                $tab.="<td align=right>".number_format(abs(@$data['Disisip']))."</td>";
                $tab.="<td align=right>".number_format(abs(@$data['dijual']))."</td>";
                $tab.="<td align=right>".number_format(abs(@$data['afiliasi']))."</td>";
            $tab.="<td align=right>".number_format(@$saldo)."</td>";
            $tab.="<td>".$data['lokasi']."</td>";
            $tab.="<td></td>";
            $tab.="</tr>";
            @$dtnm+=$data['TMB'];
            @$dkirim+=$data['PNB'];
            @$dafb+=$data['AFB'];
        } else {
            $tab.="<tr class=rowcontent><td colspan=20>No data.</td></tr>";
        }
		setIt($dtnm,0);
		setIt($dkirim,0);
		setIt($dafb,0);
        $tab.="<tr class=rowcontent><td colspan=2>".$_SESSION['lang']['total']."</td>";
        $tab.="<td align=right>".number_format($dtnm)."</td>";
        $tab.="<td align=right>".number_format(abs($dkirim))."</td>";
        $tab.="<td align=right>".number_format(abs($dafb))."</td><td colspan=8></td></tr>";
    $tab.="</tbody></table></br>";   

    
        if($_SESSION['language']=='EN'){
               $tab.="D. REJECTION RECAP"."<br>";      
    }else{
            $tab.="D. REKAP SELEKSI BIBIT"."<br>";    
    }
    

    $datad=array();
    $tab.="<table cellpadding=5 cellspacing=1 border=".$border." class=sortable>
    <thead>
        <tr class=rowheader ".$bg.">
        <th rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['blok']."</th>
        <th colspan=2 align=center>".$_SESSION['lang']['afkirbibit']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['catatan']."</th>
        </tr><tr class=rowheader ".$bg.">    
        <th align=center>".$_SESSION['lang']['jumlah']."</th>   
        <th align=center>%</th>
        </tr>
    </thead><tbody id=containdata>";

    $sData="select * 
        from ".$dbname.".bibitan_mutasi where batch = '".$bar->batch."' and kodetransaksi = 'AFB'  and post=1
        order by tanggal desc";
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
    while($rData=$qData->fetch())
    {
        $datad[$rData['tanggal']]['tanggal']=$rData['tanggal'];
        if($rData['kodetransaksi']=='TPB')
            @$datad[$rData['tanggal']]['TPB']+=$rData['jumlah'];
        else if($rData['kodetransaksi']=='AFB')
            @$datad[$rData['tanggal']]['AFB']+=$rData['jumlah'];
        else @$datad[$rData['tanggal']]['TMB']+=$rData['jumlah'];
        @$datad[$rData['tanggal']]['blok'].=' '.$rData['kodeorg'];
        @$datad[$rData['tanggal']]['ket'].=' '.$rData['keterangan'];
    }

    $no=0;
    if(!empty($datad)) 
        foreach($datad as $data) {
            $no+=1;
            @$saldo+=$data['TMB']+$data['TPB']+$data['AFB']+$data['PNB'];
            @$persen=($data['AFB']/$saldo)*100;
            $tab.="<tr class=rowcontent>";
            if($proses=='excel')$tampiltanggal=$data['tanggal']; else $tampiltanggal=tanggalnormal($data['tanggal']);
            $tab.="<td align=center>".$tampiltanggal."</td>";
            $tab.="<td align=right>".$data['blok']."</td>";
            $tab.="<td align=right>".number_format(abs($data['AFB']))."</td>";
            $tab.="<td>".number_format($persen,2)."</td>";
            $tab.="<td>".$data['ket']."</td>";
            $tab.="</tr>";
            @$afdData+=$data['TMB'];
        } else {
            $tab.="<tr class=rowcontent><td colspan=5>No data.</td></tr>";
        }
	setIt($afdData,0);
    $tab.="<tr class=rowcontent><td colspan=2>".$_SESSION['lang']['total']."</td>";
    $tab.="<td>".number_format(abs($afdData))."</td><td colspan=2></td></tr>";

    $tab.="</tbody></table><br>";               
}
if(!$adadata)$tab='No data.';

 
switch($proses)
{
    case'preview':        
        echo $tab;
    break;
    case'excel':
        $tab.="<br>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

        $nop_="kartubibit_".$kodeunit.".".$kodebatch;
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
           // closedir($handle);
        }
    break;        
        
     
	
    default:
    break;
}

?>