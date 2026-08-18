<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
//$proses=$_GET['proses'];

$proses=checkPostGet('proses','');
 
$param=$_POST;
// print_r($param);
// exit("Error:a");

$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmSupp=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optMatauang=makeOption($dbname, 'log_poht', 'nopo,matauang');


if($proses=='excel'){
    $bg=" bgcolor=#DEDEDE";
    $brdr=1;
}
else{ 
    $bg="";
    $brdr=0;
}
  
switch($proses) {
    case'preview':
        $isidata=array();
        if(($param['kdPt']!='')&&($param['tgl1']!='')){
            $tgl=tanggalsystemn($param['tgl1']);
            $sData="select idsupplier,notransaksi,nopo,sum(jumlah*hargasatuan) as jumlah,tanggal,left(kodegudang,4) as unit from ".$dbname.".log_transaksi_vw 
                    where tipetransaksi=1 and kodept='".$param['kdPt']."' and left(tanggal,4)>='2021' 
                    and tanggal<='".$tgl."' group by notransaksi order by idsupplier asc";
            $rData=fetchData($sData);
            #array supplier
            if(count($rData)!=0){
                foreach($rData as $brs=>$val){
                    // $isidata[$val['idsupplier']]=$val['idsupplier'];
                    $isidata[$val['idsupplier']][$val['notransaksi']]['tanggal']=$val['tanggal'];
                    $isidata[$val['idsupplier']][$val['notransaksi']]['nopo']=$val['nopo'];
                    $isidata[$val['idsupplier']][$val['notransaksi']]['rupiah']=$val['jumlah'];
                    $isidata[$val['idsupplier']][$val['notransaksi']]['unit']=$val['unit'];
                    
                    $jmlbrs[$val['idsupplier']][]=$val['notransaksi'];
                }
            }

        }
        $tab.="<table cellpadding=1 cellspacing=1 width=100% border=".$brdr." class=sortable>
              <thead>";
        $tab.="<td align=center ".$bg." rowspan=2>No.</td>";
        $tab.="<td align=center ".$bg." rowspan=2>".$_SESSION['lang']['unit']."</td>";
        $tab.="<td align=center ".$bg." rowspan=2>".$_SESSION['lang']['kodesupplier']."</td>";
        $tab.="<td align=center ".$bg." rowspan=2>".$_SESSION['lang']['namasupplier']."</td>";
        $tab.="<td align=center ".$bg." rowspan=2>".$_SESSION['lang']['nopo']."</td>";
        $tab.="<td align=center ".$bg." colspan=2>GR</td>";
        $tab.="<td align=center ".$bg." rowspan=2>30 Hari</td>";
        $tab.="<td align=center ".$bg." rowspan=2>60 Hari</td>";
        $tab.="<td align=center ".$bg." rowspan=2>90 Hari</td>";
        $tab.="<td align=center ".$bg." rowspan=2>Over 120 Hari</td>";
        $tab.="</tr><tr>
               <td align=center ".$bg.">".$_SESSION['lang']['notransaksi']."</td>";
        $tab.="<td align=center ".$bg.">".$_SESSION['lang']['tanggal']."</td></tr>";
         
        $tab.="</thead><tbody>";
        if(count($isidata)!=0){
            foreach($isidata as $suppId=>$arrdt){
                $no+=1;
               
                foreach($arrdt as $nogr=>$data){
                    $optNmSupp=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$suppId."'");
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td>".$no."</td>";
                    $tab.="<td>".$data['unit']."</td>";
                    $tab.="<td>".$suppId."</td>";
                    $tab.="<td>".$optNmSupp[$suppId]."</td>";
                    $tab.="<td>".$data['nopo']."</td>";
                    $tab.="<td>".$nogr."</td>";
                    $tab.="<td>".$data['tanggal']."</td>";

                }
                $tab.="</tr>";
            }
        }
        $tab.="</tbody></table>";
        echo $tab;
    break;
 
    case'getUnit':
         $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
         $sUnit="select distinct left(kodegudang,4) as unit from ".$dbname.".log_transaksi_vw where kodept='".$param['kdPt']."'";
         $rUnit=fetchData($sUnit);
         if(count($rUnit)!=0){
             foreach($rUnit as $brs=>$val){
                $optNamaOrg=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$val['unit']."'");
                 $optUnit.="<option value='".$val['unit']."'>".$val['unit']."-".$optNamaOrg[$val['unit']]."</option>";
             }
         }
         echo $optUnit;
    break;
    default:
    break;
}