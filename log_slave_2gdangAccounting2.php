<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$param=$_POST;
if(!empty($_GET['proses'])){
    if($_GET['proses']=='excel'){
        $param=$_GET;
    }else{
        $param['proses']=$_GET['proses'];
    }
}

#arrays
$optNmakun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$optNmbarang=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optKlmpKbrg=makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optNmSup=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$drpt="kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$param['ptId2']."' and tipe!='HOLDING')";

$whrd=$whr="";
if(($param['prdIdDr2']!='')||($param['prdIdSmp2']!='')) {
    $whrd.="and left(tanggal,7) between '".$param['prdIdDr2']."' and '".$param['prdIdSmp2']."'";
    $dert="select TIMESTAMPDIFF(MONTH,'".$param['prdIdDr2']."-01','".$param['prdIdSmp2']."-01') as difdrt 
           from ".$dbname.".keu_jurnaldt 
           where ".$drpt."";
    $qert=$owlPDO->query($dert) or die(print " Gagal: ".PDOException::getMessage());
	$qert->setFetchMode(PDO::FETCH_ASSOC);
    $rdert=$qert->fetch();
    if(($rdert['difdrt']>=0)&&($rdert['difdrt']<=6)) {
        $whr.="and left(tanggal,7) between '".$param['prdIdDr2']."' and '".$param['prdIdSmp2']."'";
    } else {
        exit("error: Periode Salah atau lebih dari 6 bulan");
    }
}
 if($param['proses']!='getUnit'){
    if($param['unitId2']!=''){
        $whr.=" and kodeorg='".$param['unitId2']."'";
    }
    if($param['ptId2']==''){
        exit("error: ".$_SESSION['lang']['pt']." tidak boleh kosong");
    }else{
        $drpt="kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$param['ptId2']."' and tipe!='HOLDING')";
    }
     #getnoakun dari kelompok barang
     $snoakun="select distinct noakun,kode from ".$dbname.".log_5klbarang 
               where (noakun!='' and noakun is not null) order by kode asc";
     $qnoakun=$owlPDO->query($snoakun) or die(print " Gagal: ".PDOException::getMessage());
	 $qnoakun->setFetchMode(PDO::FETCH_ASSOC);
     while($rnoakun=  $qnoakun->fetch()){
         $lstNoakun[$rnoakun['kode']]=$rnoakun['noakun'];
     }
     #row kelompok barang
      $sddr="select left(kodebarang,3) as klmpk,count(nojurnal) as jmlhrow
           from ".$dbname.".`keu_jurnaldt` 
           where ".$drpt." ".$whr."  and (nojurnal like '%INVK%' and jumlah<0) or (nojurnal like '%INVM%' and jumlah>0)
           group by left(kodebarang,3) order by kodebarang asc";
	  $qddr=$owlPDO->query($sddr) or die(print " Gagal: ".PDOException::getMessage());
	  $qddr->setFetchMode(PDO::FETCH_ASSOC);
      while($rddr = $qddr->fetch()){
          $rowKlmkBrg[$rddr['klmpk']]=$rddr['jmlhrow'];
      }
      #row barang
      $sddr="select kodebarang as klmpk,count(nojurnal) as jmlhrow
           from ".$dbname.".`keu_jurnaldt` 
           where ".$drpt." ".$whr."  and (nojurnal like '%INVK%' and jumlah<0) or (nojurnal like '%INVM%' and jumlah>0)
           group by kodebarang order by kodebarang asc";
	  $qddr=$owlPDO->query($sddr) or die(print " Gagal: ".PDOException::getMessage());
	  $qddr->setFetchMode(PDO::FETCH_ASSOC);
      while($rddr = $qddr->fetch()){
          $rowBrg[$rddr['klmpk']]=$rddr['jmlhrow'];
      }
      
    
    $bgex="";
    $brd=0;
	if($param['proses']=='excel'){
		$bgex=" bgcolor=#DEDEDE align=center";
		$brd=1;
	}
		
	$tab="<table cellpadding=5 cellspacing=1 border=".$brd." class=sortable>";
	$tab.="<thead><tr ".$bgex.">";
	$tab.="<th>".$_SESSION['lang']['kodebarang']."</th>";
	$tab.="<th>".$_SESSION['lang']['namabarang']."</th>";
	$tab.="<th>".$_SESSION['lang']['nojurnal']."</th>";
	$tab.="<th>".$_SESSION['lang']['noreferensi']."</th>";
	$tab.="<th>".$_SESSION['lang']['noakun']."</th>";
	$tab.="<th>".$_SESSION['lang']['rp']."</th>";
	$tab.="<th>".$_SESSION['lang']['namasupplier']."</th>";
	$tab.="<th>".$_SESSION['lang']['nodok']."</th>";
	$tab.="<th>".$_SESSION['lang']['kodevhc']."</th>";
	$tab.="<th>".$_SESSION['lang']['kodeblok']."</th>
		   <th>".$_SESSION['lang']['keterangan']."</th> 
		  </tr><tbody>";
	 #get data dr log_transaksi
	$sdt="select left(kodebarang,3) as klmpk,kodebarang,nojurnal,noreferensi,`jumlah` as uang,kodevhc,kodeblok,kodesupplier,noakun,keterangan,nodok
		  from ".$dbname.".`keu_jurnaldt` 
		  where ".$drpt." ".$whr."  and ((nojurnal like '%INVK%' and jumlah<0) or (nojurnal like '%INVM%' and jumlah>0))
		  order by kodebarang asc";
	$qdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
	$qdt->setFetchMode(PDO::FETCH_ASSOC);
	while($rdt=  $qdt->fetch()){
		if(!isset($klmpkbrg) or $klmpkbrg!=$rdt['klmpk']){
			$klmpkbrg=$rdt['klmpk'];
			$tab.="<tr class=rowcontent>";
			$tab.="<td>".$klmpkbrg."</td>";
			$tab.="<td>".(isset($optKlmpKbrg[$klmpkbrg]) ? $optKlmpKbrg[$klmpkbrg] : '')."</td>";
			$tab.="<td>".(isset($lstNoakun[$klmpkbrg]) ? $lstNoakun[$klmpkbrg] : '')."</td>";
			$tab.="<td>".(isset($optNmakun[(isset($lstNoakun[$klmpkbrg]) ? $lstNoakun[$klmpkbrg] : '')]) ? $optNmakun[$lstNoakun[$klmpkbrg]] : "")."</td>";
			$tab.="<td colspan=7>&nbsp;</td>";
			$tab.="</tr>"; 
			$rertklm=$rowKlmkBrg[$klmpkbrg];
			setIt($subtRps[$klmpkbrg],0);
			$subtRps[$klmpkbrg]+=$rdt['uang'];
			$ad=1;
		}else{
			setIt($subtRps[$klmpkbrg],0);
			setIt($subtJmlhs[$klmpkbrg],0);
			$subtRps[$klmpkbrg]+=$rdt['uang'];
			$subtJmlhs[$klmpkbrg]+=$rdt['uang'];
			$ad+=1;
		}
		setIt($optNmbarang[$rdt['kodebarang']],'');
		setIt($optNmSup[$rdt['kodesupplier']],'');
		$tab.="<tr class=rowcontent>";
		$tab.="<td>".$rdt['kodebarang']."</td>";
		$tab.="<td>".$optNmbarang[$rdt['kodebarang']]."</td>";
		$tab.="<td>".$rdt['nojurnal']."</td>";
		$tab.="<td>".$rdt['noreferensi']."</td>";
		$tab.="<td>".$rdt['noakun']."</td>";
		$tab.="<td align=right>".number_format($rdt['uang'],2)."</td>";
		$tab.="<td>".$optNmSup[$rdt['kodesupplier']]."</td>";
		$tab.="<td>".strtoupper($rdt['nodok'])."</td>";
		$tab.="<td>".$rdt['kodevhc']."</td>";
		$tab.="<td>".$rdt['kodeblok']."</td>
			   <td>".$rdt['keterangan']."</td></tr>";
		if(!isset($kdbrg) or $kdbrg!=$rdt['kodebarang']){
			$aret=1;
			$kdbrg=$rdt['kodebarang'];
			setIt($subtRp[$kdbrg],0);
			$subtRp[$kdbrg]+=$rdt['uang'];
			$rert=$rowBrg[$kdbrg];
		} else {
			$aret+=1;
			setIt($subtRp[$kdbrg],0);
			$subtRp[$kdbrg]+=$rdt['uang'];
		}
		if($rert==$aret){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=5 align=right>".$_SESSION['lang']['subtotal']." ".$optNmbarang[$kdbrg]."</td>";
			$tab.="<td  align=right>".number_format($subtRp[$kdbrg],2)."</td>";
			$tab.="<td colspan=5>&nbsp;</td>";
			$tab.="</tr>"; 
		}
		if($rertklm==$ad){
			$tab.="<tr bgcolor=orange>";
			$tab.="<td  align=right>".$_SESSION['lang']['subtotal']."</td>";
			$tab.="<td  align=right>".$klmpkbrg."</td>";
			$tab.="<td  align=right>".(isset($optKlmpKbrg[$klmpkbrg]) ? $optKlmpKbrg[$klmpkbrg] : '')."</td>";
			$tab.="<td  align=right>".(isset($lstNoakun[$klmpkbrg]) ? $lstNoakun[$klmpkbrg] :'')."</td>";
			$tab.="<td  align=right>".(isset($optNmakun[(isset($lstNoakun[$klmpkbrg]) ? $lstNoakun[$klmpkbrg] : '')]) ? $optNmakun[$lstNoakun[$klmpkbrg]] : '')."</td>";
			$tab.="<td  align=right>".number_format($subtRps[$klmpkbrg],2)."</td>";
			$tab.="<td colspan=5>&nbsp;</td>";
			$tab.="</tr>"; 
		}
	}
	$tab.="</tbody></table>";
}

switch($param['proses']){
    case'getUnit':
        $optUnit2="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sunit="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where induk='".$param['ptId2']."' order by namaorganisasi asc";
        $qunit=$owlPDO->query($sunit) or die(print " Gagal: ".PDOException::getMessage());
		$qunit->setFetchMode(PDO::FETCH_ASSOC);
		while($runit=  $qunit->fetch()){
            $optUnit2.="<option value='".$runit['kodeorganisasi']."'>".$runit['namaorganisasi']."</option>";
        }
        echo $optUnit2;
    break;
    case'preview':
    echo $tab;
    break;
    case'excel':
        $tab.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $thisDate=date("YmdHms");
        //$nop_="Laporan_Pembelian";
        $nop_="laptransaksiGudangAccv_".$thisDate;
        $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
    break;
}
?>
