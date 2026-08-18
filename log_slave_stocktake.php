<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$kdgudang=checkPostGet('kdgudang','');
$klbrg=checkPostGet('klbrg','');
$kodeorg=checkPostGet('unit','');
$periode=checkPostGet('periode','');
$kodebarang=checkPostGet('kodebarang','');
$qtysys=checkPostGet('qtysys','');
$phsyqty=checkPostGet('phsyqty','');
$bincardqty=checkPostGet('bincardqty','');
$varian=checkPostGet('varian','');
$remark=checkPostGet('remark','');
$tipe=checkPostGet('tipe','');
$jenis=checkPostGet('jenis','');


switch($method)
{

	case 'simpanht':

    $time=date('Y-m-d H:i:s');
    $sIns="insert into ".$dbname.".log_stocktakeht (`kodegudang`,`kodeorg`,`periode`,`createby`,`createtime`) 
        values ('".$kdgudang."','".$kodeorg."','".$periode."','".$_SESSION['standard']['userid']."','".$time."')";
        try{
            $owlPDO->exec($sIns); 
        }
        catch (PDOException $e){
            echo"Gagal".$e->getMessage();
        }

    break;

    case'savedata':

        #delete 1st
        $str="delete from ".$dbname.".log_stocktakedt where `kodebarang`='".$kodebarang."'";
        try{
            $owlPDO->exec($str); 
            }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }

        
    

        $str="insert into ".$dbname.".log_stocktakedt (`kodebarang`,`kodegudang`,`qtysys`,`phsyqty`,`bincardqty`,
                `varian`,`remark`)
                values ('".$kodebarang."','".$kdgudang."','".$qtysys."','".$phsyqty."','".$bincardqty."',
                '".$varian."','".$remark."')";

            
        try{
            $owlPDO->exec($str); 
            }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }


        
                  
    break; 


    case 'loadformdt':

             $whr='';
            if ($kdgudang!='') {
                $whr.=" and kodegudang='".$kdgudang."'";
            }

            if ($klbrg!='') {
                $whr.=" and kelompokbarang='".$klbrg."'";
                @$whr1.=" and kelompokbarang='".$klbrg."'";
            }
	
            $kelompokbrg=array();
			$str="select distinct (kelompokbarang) as kelompokbarang,kodebarang from ".$dbname.".log_5masterbarang where 1=1 ". @$whr1." group by kelompokbarang";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){


                $kelompokbrg[$bar['kodebarang']]=$bar['kelompokbarang'];
                $kdbarang[$bar['kodebarang']]=$bar['kodebarang'];

                
            }

        
       
            if ($tipe=='save') {
				
                $str="select saldoakhirqty,a.kodebarang,kelompokbarang,namabarang,satuan from ".$dbname.".log_5saldobulanan a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang where 1=1 ".$whr."";
                $tab="";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
				// exit('error');
                while($bar=$res->fetch()){

                    $kdbarang[$bar['kodebarang']]=$bar['kodebarang'];
                    $kodebarang[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['kodebarang'];
                    $namabarang[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['namabarang'];
                    $satuan[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['satuan'];
                    $qtysys[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['saldoakhirqty'];

                    $phsyqty[$bar['kelompokbarang']][$bar['kodebarang']]=0;
                    $bincardqty[$bar['kelompokbarang']][$bar['kodebarang']]=0;
                    $varian[$bar['kelompokbarang']][$bar['kodebarang']]=0;
                    $remark[$bar['kelompokbarang']][$bar['kodebarang']]=0;


                }
            }
            else
            {
				
             $str="select *,b.periode,c.namabarang,c.satuan from ".$dbname.".log_stocktakedt a left join ".$dbname.".log_stocktakeht b on a.kodegudang=b.kodegudang
             left join ".$dbname.".log_5masterbarang c on a.kodebarang=c.kodebarang where a.kodegudang='".$kdgudang."' and periode='".$periode."'";
				// exit("error :".$str);
             $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
             $res->setFetchMode(PDO::FETCH_ASSOC);
             $res->setFetchMode(PDO::FETCH_ASSOC);
             while($bar=$res->fetch()){

                $kdbarang[$bar['kodebarang']]=$bar['kodebarang'];
                $kodebarang[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['kodebarang'];
                $namabarang[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['namabarang'];
                $satuan[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['satuan'];
                $qtysys[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['qtysys'];
                $phsyqty[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['phsyqty'];
                $bincardqty[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['bincardqty'];
                $varian[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['varian'];
                $remark[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['remark'];


            }
            }

           
            
            foreach ($kelompokbrg as $klpbrg) {


                
                $tab.="<tr class=rowcontent>";
                $tab.="<td colspan=9>".$klpbrg."</td>"; 
                foreach ($kdbarang as $kdbrg) {
                    if (@$kodebarang[$klpbrg][$kdbrg]!='') {
                    $no+=1;

                   

                    $tab.="<tr class=rowcontent id=rowitem".$no.">";
                    $tab.="<td id=kdbrg".$no.">".@$kodebarang[$klpbrg][$kdbrg]."</td>"; 
                    $tab.="<td>".@$namabarang[$klpbrg][$kdbrg]."</td>"; 
                    $tab.="<td></td>";
                    $tab.="<td>".@$satuan[$klpbrg][$kdbrg]."</td>"; 
                    $tab.="<td align=right id=qtysys".$no.">".@$qtysys[$klpbrg][$kdbrg]."</td>"; 
                    $tab.="<td align=right><input type=text value=".@$phsyqty[$klpbrg][$kdbrg]." id=phsyqty".$no."  onkeyup=getmat('phsyqty','".$no."') size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:60px;\"></td>";
                    $tab.="<td align=right><input type=text value=".@$bincardqty[$klpbrg][$kdbrg]." id=bincardqty".$no."  size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:60px;\"></td>";
                    $tab.="<td align=right  id=varian".$no.">".@$varian[$klpbrg][$kdbrg]."</td>";
                    $tab.="<td align=right><input type=text value='".@$remark[$klpbrg][$kdbrg]."' id=remark".$no."  size=10  class=myinputtextnumber style=\"width:90px;\"></td>"; 
                    }
                        
                }                      
                $tab.="</tr>";
            }
                 $tab.="<td align=right><button class=mybutton onclick=saveAllitem(".$no.")>" . $_SESSION['lang']['save'] . "</button></td>"; 
          

           echo $tab;
    break;

    case'postingx':
        
        $sPosting = "update " . $dbname . ".log_stocktakeht set posting='1', postingby='".$_SESSION['standard']['userid']."' where periode='" . $periode . "' and kodegudang='" . $kdgudang . "'"; 
        try{
            $owlPDO->exec($sPosting); 
        }catch (PDOException $e){
            echo "DB Error : " . $e->getMessage();
            die();
        }
        break;

    case 'loaddatadt':
            $postJabatan = getPostingJabatan('stockopname');

            $str="select * from log_stocktakeht where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
                  $tab.="<tr class=rowcontent>";
                   $tab.="<td>".$no."</td>";  
                   $tab.="<td>".$bar['kodegudang']."</td>";  
                   $tab.="<td>".$bar['kodeorg']."</td>";  
                   $tab.="<td>".$bar['periode']."</td>";

                   if ($bar['posting']==0) {

                     $tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                     onclick=edit('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."')></td>";
                     $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                     onclick=del('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."')></td>"; 
                     $tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','html');\" ></td>";
                     $tab.="<td align=center><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['kodegudang']."','".$bar['kodeorg']."','".$bar['periode']."','".$no."','event','excel');\" ></td>";
                     if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
                        $tab.="<td>
                        <img src='images/skyblue/posting.png' class='resicon' title='Posting' ></td>";
                    }else{
                        $tab.="<td>
                        <img src='images/skyblue/posting.png' class='resicon'  title='Posting' onclick=\"postingx('" . $bar['kodegudang'] . "','" . $bar['periode'] . "')\"></td>";
                    }
                     $tab.="</tr>";
                 }else{

                 }
            }

           echo $tab;
    break;

    case'delete':
        $sIns="delete from ".$dbname.".log_stocktakeht where kodegudang = '".$kdgudang."' and kodeorg='".$kodeorg."' and periode='".$periode."'";

        try{
			$owlPDO->exec($sIns); 
		}
		catch (PDOException $e){
			echo"Gagal".$e->getMessage();
			die();
		}
    break;
        default:
        break;


    case'html':


    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $men='menu.css';
      $gen='generic.css';
  }else if($theme=='red'){
      $men='menuRed.css';
      $gen='genericRed.css';  
  }else{
      $men='menuGray.css';
      $gen='genericGray.css';  
  }         




  $str="select distinct (kelompokbarang) as kelompokbarang,kodebarang from ".$dbname.".log_5masterbarang group by kelompokbarang";
  $tab="";
  $tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_ASSOC);
  while($bar=$res->fetch()){


    $kelompokbrg[$bar['kodebarang']]=$bar['kelompokbarang'];
    $kdbarang[$bar['kodebarang']]=$bar['kodebarang'];


}


$str="select *,b.periode,c.namabarang,c.satuan from ".$dbname.".log_stocktakedt a left join ".$dbname.".log_stocktakeht b on a.kodegudang=b.kodegudang
left join ".$dbname.".log_5masterbarang c on a.kodebarang=c.kodebarang where a.kodegudang='".$kdgudang."' and periode='".$periode."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){

    $kdbarang[$bar['kodebarang']]=$bar['kodebarang'];
    $kodebarang[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['kodebarang'];
    $namabarang[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['namabarang'];
    $satuan[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['satuan'];
    $qtysys[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['qtysys'];
    $phsyqty[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['phsyqty'];
    $bincardqty[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['bincardqty'];
    $varian[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['varian'];
    $remark[$bar['kelompokbarang']][$bar['kodebarang']]=$bar['remark'];


}



$tab.="<fieldset>";
$tab.="<table>";
$tab.="<tr>";
$tab.="<td width=800px align=center >STOCK TAKE PERIODE ".$periode."</td>";
$tab.="</tr>";
$tab.="</table>";
$tab.="<table class=sortable cellspacing=1 cellpadding=1 border=1>
<thead>
<tr class=rowheader>
<td rowspan=2>Store Code</td>
<td rowspan=2 align=center>Name</td>
<td rowspan=2 align=center>Part Number</td>
<td rowspan=2 align=center>Bin<br>Unit</td>
<td >Computer</td>
<td >Physical</td>
<td >Bin Card</td>
<td  rowspan=2>Variance</td>
<td  rowspan=2>REMARK</td>
<tr>
<td>Quantity</td>
<td>Quantity</td>
<td>Quantity</td>

</tr>
</tr>
</thead>
<tbody>";

foreach ($kelompokbrg as $klpbrg) {

    $tab.="<tr class=rowcontent>";
    $tab.="<td colspan=9>".$klpbrg."</td>"; 
    foreach ($kdbarang as $kdbrg) {
        if (@$kodebarang[$klpbrg][$kdbrg]!='') {
            $no+=1;


            $tab.="<tr class=rowcontent id=rowitem".$no.">";
            $tab.="<td >".@$kodebarang[$klpbrg][$kdbrg]."</td>"; 
            $tab.="<td>".@$namabarang[$klpbrg][$kdbrg]."</td>"; 
            $tab.="<td></td>";
            $tab.="<td>".@$satuan[$klpbrg][$kdbrg]."</td>"; 
            $tab.="<td align=right>".@$qtysys[$klpbrg][$kdbrg]."</td>"; 
            $tab.="<td align=right>".@$phsyqty[$klpbrg][$kdbrg]."</td>";
            $tab.="<td align=right>".@$bincardqty[$klpbrg][$kdbrg]."</td>";
            $tab.="<td align=right >".@$varian[$klpbrg][$kdbrg]."</td>";
            $tab.="<td align=right>".@$remark[$klpbrg][$kdbrg]."</td>"; 
        }

    }                      
    $tab.="</tr>";
}

$tab.="</table></fieldset>";

if($jenis=='html'){
    echo $tab;
} elseif($jenis=='pdf') {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($tab);
    $dompdf->setPaper('Legal', 'landscape');
    $dompdf->render();
    $dompdf->stream("BOR",array("Attachment"=>0));
}else{  $not=str_replace('/','',$param['notransaksi']);
$stream = $tab;
$nop_ = "detail_".$not;
if (strlen($stream) > 0) {
    if ($handle = opendir('tempExcel')) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
                @unlink('tempExcel/' . $file);
            }
        }
        closedir($handle);
    }
    $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
    if (!fwrite($handle, $stream)) {
        echo "<script language=javascript1.2>
        parent.window.alert('Cant convert to excel format');
        </script>";
        exit;
    } else {
        echo "<script language=javascript1.2>
        window.location='tempExcel/" . $nop_ . ".xls';
        </script>";
    }
    closedir($handle);
}
}
    break;
}
?>