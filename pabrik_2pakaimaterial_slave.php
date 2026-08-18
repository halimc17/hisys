<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));


if($tgl1=='--'){
    $tgl1='';
}
if($tgl2=='--'){
    $tgl2='';
}

if($tgl1==''  or $tgl2==''){
	exit("Warning:Tanggal kosong");
}


#= bentuk data tanggal
$arrtgl=rangeTanggalarr($tgl1,$tgl2);
$ctgl=count($arrtgl);
$cekcount=0;
$str="select * from ".$dbname.".pabrik_rawatmesindt_vw where pabrik='".$unit."' and tanggal between '".$tgl1."' and '".$tgl2."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$cekcount+=1;
	$arrstation[$bar['statasiun']]=$bar['statasiun'];
	$arrmaterial[$bar['kodebarang']]=$bar['kodebarang'];
	$listmaterial[$bar['statasiun']][$bar['kodebarang']]=$bar['kodebarang'];
	@$tjumlah[$bar['statasiun']][$bar['kodebarang']]+=$bar['jumlah'];
	@$jumlah[$bar['statasiun']][$bar['kodebarang']][$bar['tanggal']]=$bar['jumlah'];
	$satuan[$bar['kodebarang']]=$bar['satuan'];
	$harga[$bar['kodebarang']]=$bar['harga'];
}

if($cekcount==0){
	exit("Warning:Data kosong");
}

#= array namabarang
$str = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where kodebarang in ('".implode("','",@$arrmaterial)."')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$namabarang[$bar['kodebarang']]=$bar['namabarang'];
}

#= array nama station
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi in ('".implode("','",@$arrstation)."')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$namaorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

// print_r($arrmaterial);


//$stream= "";
if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1 width=100%>";
} else {
    $stream.= "<table class='sortable' cellspacing='1' width=100%>";
}

array_multisort($arrstation,SORT_ASC);



$stream.="<thead>";
$stream.="<tr class=rowcontent>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['kode']."</td>";
	$stream.="<td align=center colspan=3>".$_SESSION['lang']['material']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jumlah']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['harga']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
	$stream.="<td align=center colspan=".$ctgl.">".$_SESSION['lang']['tanggal']."</td>";
	$stream.="<td align=center rowspan=2>".$_SESSION['lang']['keterangan']."</td>";
$stream.="</tr>";

$stream.="<tr>";
$stream.="<td align=center>".$_SESSION['lang']['kode']."</td>";
$stream.="<td align=center>".$_SESSION['lang']['nama']."</td>";
$stream.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
foreach ($arrtgl as $tgl){
	$qwe=date('D', strtotime($tgl));
	if($qwe=='Sun'){
		$stream.="<td align=center><font color=red>".substr($tgl,8,2)."</font></td>";
	}else{
		$stream.="<td align=center>".substr($tgl,8,2)."</td>";
	}
		
}
$stream.="</tr>";	
$stream.="</thead>";


foreach ($arrstation as $station){
		@$no++;
		$stream.="<tr class=rowcontent>";
			$stream.="<td align=left colspan=".($ctgl+8).">".$station." [ ".$namaorg[$station]." ]</td>";
		$stream.="</tr>";
		foreach ($arrmaterial as $material){
			if(@$listmaterial[$station][$material]!=''){
				$stream.="<tr class=rowcontent>";
					$stream.="<td></td>";
					$stream.="<td align=left>".$material."</td>";
					$stream.="<td align=left>".$namabarang[$material]."</td>";
					$stream.="<td align=left>".$satuan[$material]."</td>";
					$stream.="<td align=right>".@number_format($tjumlah[$station][$material],2)."</td>";
					$stream.="<td align=right>".@number_format($harga[$material],2)."</td>";
					$stream.="<td align=right>".@number_format($tjumlah[$station][$material]*$harga[$material],2)."</td>";
					@$st[$station]+=($tjumlah[$station][$material]*$harga[$material]);
					foreach ($arrtgl as $tgl){
						if(@$jumlah[$station][$material][$tgl]==''){
							$stream.="<td align=right></td>";
						}else{
							$stream.="<td align=right>".@number_format($jumlah[$station][$material][$tgl],2)."</td>";
						}
						
					}
					$stream.="<td align=left></td>";
				$stream.="</tr>";
				}
		}
		$stream.="<tr class=rowcontent>";
					$stream.="<td colspan=6>".$_SESSION['lang']['subtotal']."</td>";
					$stream.="<td align=right>".@number_format($st[$station])."</td>";
					$stream.="<td align=right colspan=".($ctgl+1)."></td>";
		$stream.="</tr>";	
		@$gt+=$st[$station];
				
}
$stream.="<tr class=rowcontent>";
	$stream.="<td colspan=6>".$_SESSION['lang']['total']."</td>";
	$stream.="<td align=right>".@number_format($gt,2)."</td>";
	$stream.="<td align=right colspan=".($ctgl+1)."></td>";
$stream.="</tr>";
/*
$stream.="<thead><tr>";
$stream.="<td align=center colspan=7>".$_SESSION['lang']['grnd_total']."</td>";
	$stream.="<td align=right>".@number_format($gthk,2)."</td>";
	$stream.="<td align=right>".@number_format($gtllembur,2)."</td>";
	foreach ($dtkomplus as $komplus){
		$stream.="<td align=right>".@number_format($gtkomplus[$komplus])."</td>";
		@$tgtkomplus+=$gtkomplus[$komplus];
	}
	$stream.="<td align=right>".@number_format($tgtkomplus)."</td>";
	foreach ($dtkommin as $kommin){
		$stream.="<td align=right>".@number_format($gtkommin[$kommin])."</td>";
		@$tgtkommin+=$gtkommin[$kommin];
	}
	$stream.="<td align=right>".@number_format($tgtkommin)."</td>";
	$stream.="<td align=right>".@number_format($gtnetto)."</td>";
	
$stream.="</tr></tdead>";
*/


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
        $nop_="laporan_pakai_material";
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