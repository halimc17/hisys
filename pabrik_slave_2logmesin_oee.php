<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unitoee','');
$tgl=tanggalsystemn(checkPostGet('tgloee',''));

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


if($proses=='excel'){
	$border='border=1';
}else{
	$border='border=0';
}


#arr station
$str="select * from ".$dbname.".pabrik_logmesin_oee where station like '".$unit."%' and tanggal = '".$tgl."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$arrstation[$bar['station']]=$bar['station'];
}

$str="select * from ".$dbname.".pabrik_logmesin_oee where tanggal = '".$tgl."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$pehi[$bar['station']]=$bar['pe'];
	$mahi[$bar['station']]=$bar['ma'];
	$rqhi[$bar['station']]=$bar['rq'];
	$oeehi[$bar['station']]=$bar['oee'];
}

$str="select station,avg(pe) as pe,avg(ma) as ma,avg(rq) as rq,avg(oee) as oee 
		from ".$dbname.".pabrik_logmesin_oee where 1=1 and pe>0 and
		tanggal like '".substr($tgl,0,7)."%' group by station ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$pebi[$bar['station']]=$bar['pe'];
	$mabi[$bar['station']]=$bar['ma'];
	$rqbi[$bar['station']]=$bar['rq'];
	$oeebi[$bar['station']]=$bar['oee'];
}

$str="select station,avg(pe) as pe,avg(ma) as ma,avg(rq) as rq,avg(oee) as oee 
		from ".$dbname.".pabrik_logmesin_oee where 1=1 and pe>0 and
		tanggal like '".substr($tgl,0,4)."%' group by station ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$peti[$bar['station']]=$bar['pe'];
	$mati[$bar['station']]=$bar['ma'];
	$rqti[$bar['station']]=$bar['rq'];
	$oeeti[$bar['station']]=$bar['oee'];
}

$cekdata=count($arrstation);
if($cekdata<1){
	exit("Warning:Data Kosong, Silahkan preview diform Data");
}
echo"<pre>";
$stream="";
$stream.="<table cellspacing=1 class=sortable cellpadding=1 ".$border.">";
$stream.="<thead>";
$stream.="<tr>";
$stream.="<td align=center>NO</td>";
$stream.="<td align=center>DESCRIPTION</td>";
$stream.="<td align=center>UoM</td>";
$stream.="<td align=center>TODAY</td>";
$stream.="<td align=center>MTD</td>";
$stream.="<td align=center>YTD</td>";
$stream.="</tr>";
$stream.="</thead>";

foreach($arrstation as $station){
	@$no+=1;
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center>".$no."</td>";
	$stream.="<td align=left colspan=5><b>".$nmorg[$station]."</b></td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- Performance Efficiency (PE)</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($pehi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($pebi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($peti[$station],2)."</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- Machine Availability (MA)</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($mahi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($mabi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($mati[$station],2)."</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- Rate Of Quality (RQ)</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($rqhi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($rqbi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($rqti[$station],2)."</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- OEE</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($oeehi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($oeebi[$station],2)."</td>";
	$stream.="<td align=right>".number_format($oeeti[$station],2)."</td>";
	$stream.="</tr>";
	
	$tpehi+=$pehi[$station];
	$tmahi+=$mahi[$station];
	$trqhi+=$rqhi[$station];
	$toeehi+=$oeehi[$station];
	
	$tpebi+=$pebi[$station];
	$tmabi+=$mabi[$station];
	$trqbi+=$rqbi[$station];
	$toeebi+=$oeebi[$station];
	
	$tpeti+=$peti[$station];
	$tmati+=$mati[$station];
	$trqti+=$rqti[$station];
	$toeeti+=$oeeti[$station];
	
}

### rekap

$stream.="<tr class=rowcontent>";
	$stream.="<td align=center>".($no+1)."</td>";
	$stream.="<td align=left colspan=5><b>REKAP</b></td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- Performance Efficiency (PE)</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($tpehi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($tpebi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($tpeti/$no,2)."</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- Machine Availability (MA)</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($tmahi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($tmabi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($tmati/$no,2)."</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- Rate Of Quality (RQ)</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($trqhi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($trqbi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($trqti/$no,2)."</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td align=center></td>";
	$stream.="<td align=left>- OEE</td>";
	$stream.="<td align=center>%</td>";
	$stream.="<td align=right>".number_format($toeehi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($toeebi/$no,2)."</td>";
	$stream.="<td align=right>".number_format($toeeti/$no,2)."</td>";
	$stream.="</tr>";



$stream.="</thead>";

$stream.="</table>";







$stream.="<tbody></table>";
switch($proses){
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="laporan_oee_".$unit;
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