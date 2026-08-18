<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');
$noakuna = checkPostGet('noakuna', '');
$noakuns = checkPostGet('noakuns', '');
$periode = checkPostGet('periode', '');
$tipe = checkPostGet('tipe', '');
$pt = checkPostGet('pt', '');
$unit = checkPostGet('unit', '');
$codeurut = checkPostGet('codeurut', '');
$kodelaporan = checkPostGet('kodelaporan', '');

	$whr="and kodeorg='".$unit."'";
if($unit==''){
	$whr="and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
$periodesaldo=str_replace('-','',$periode);

$t=mktime(0,0,0,substr($periodesaldo,4,2)+1,15,substr($periodesaldo,0,4));
$periodCUR=date('Ym',$t);
$periodCUR2=substr($periodesaldo,0,4).'-'.substr($periodesaldo,4,2);
$kolomCUR="awal".date('m',$t);
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');

switch($method){
	case'html':
	
	$stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
	$stream.="
		<table cellpading=1 cellspacing=1 ".$border." class=sortable>
		
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td> 
				<td align=center>".$_SESSION['lang']['noakun']."</td> 
				<td align=center>".$_SESSION['lang']['namaakun']."</td> 
				<td align=center>".$_SESSION['lang']['jumlah']."</td> 
			</tr>
		</thead>";
		
		
	
	$str="select noakundisplay from ".$dbname.".keu_5mesinlaporandt 
			where nourut='".$codeurut."' and namalaporan='".$kodelaporan."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$noakuntidak=explode(",",$bar['noakundisplay']);
		
	}
	
	
	if($noakuntidak[0]!='' || $noakuntidak[0]!='0')
    {
        $jumakuntidak=  count($noakuntidak)-1;
        $where2=" and left(noakun,3) not in (";
        $penutupwhere2=")";
    }
	
	for($i=0;$i<=$jumakuntidak;$i++)
    {
        if($jumakuntidak==0)
        {
            $where2.=" '".$noakuntidak[$i]."' ";
        }
        else
        {
            if($i==$jumakuntidak)
            {
                $where2.=" '".$noakuntidak[$i]."' ";
            }
            else
            {
                $where2.=" '".$noakuntidak[$i]."', ";
            }
        }
    }
    
    $isiwhere2=$where2.$penutupwhere2;
	
	
	
	$str="select sum(".$kolomCUR.") as ".$kolomCUR.",noakun from ".$dbname.".keu_saldobulanan 
	where noakun between '".$noakuna."' and '".$noakuns."' and periode='".$periodCUR."' ".$whr." ".$isiwhere2." group by noakun";
	//echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$no+=1;
		$vkolomCUR = $bar[$kolomCUR];
		if($bar['noakun']=='3110700'||$bar['noakun']=='3110800'){
			$vkolomCUR = $bar[$kolomCUR]*(-1);
		}
		$stream.="
			<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$bar['noakun']."</td>
				<td>".$nmakun[$bar['noakun']]."</td>
				<td align=right>".number_format($vkolomCUR,2)."</td>
			</tr>
		";
		$tjumlah+=$vkolomCUR;
	}
	$stream.="<tr class=rowcontent>
				<td align=center colspan=3>Total</td>
				<td align=right>".number_format($tjumlah,2)."</td>
			</tr></table>";
	echo $stream;
	
	break;
}












?>