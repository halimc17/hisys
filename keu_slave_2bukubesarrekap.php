<?php
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$pt = checkPostGet('pt', '');
$gudang = checkPostGet('gudang', '');
$akundari = checkPostGet('akundari', '');
$akunsampai = checkPostGet('akunsampai', '');
$periode=checkPostGet('periode','');
$periode1=checkPostGet('periode1','');
$revisi=checkPostGet('revisi','');
$regional=checkPostGet('regional','');
$tampilanId=checkPostGet('tampilanId','');
$tipelaporan=checkPostGet('tipelaporan','');


$stream="";
        
//cek periode dan periode1
if($periode1<$periode)
{  #ditukar
    $z=$periode;
    $periode=$periode1;
    $periode1=$z;
}
$where='';
if($akundari!='' and $akunsampai!=''){
	$where.=" and noakun between '".$akundari."' and  '".$akunsampai."'";
}	

$whereakun='';
if($akundari!='' and $akunsampai!=''){
	$whereakun.=" and noakun between '".$akundari."' and  '".$akunsampai."'";
}		
	
//ambil namapt
$str=$owlPDO->query("select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'");
$namapt='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}

//ambil namagudang
$str=$owlPDO->query("select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'");
$namagudang='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $namagudang=strtoupper($bar->namaorganisasi);
}

//ambil akun laba rugi tahun berjalan:
$CLM='';
$str=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=  $str->fetch()){
    $CLM=$bar->noakundebet;
}

//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode=mktime(0,0,0,substr($periode,5,2)-1,4,substr($periode,0,4));
$lmperiode=date('Y-m',$lmperiode);
if($_SESSION['language']=='ID'){
$str="select distinct noakun,namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."'  ".$where." order by noakun";
}
else{
    $str="select distinct noakun,namaakun1 as namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."' ".$where." order by noakun";
}
// echo $str;
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
$TAB=Array();

while($bar=$res->fetch())
{
    $TAB[$bar->noakun]['noakun']=$bar->noakun;
    $TAB[$bar->noakun]['namaakun']=$bar->namaakun;
    $TAB[$bar->noakun]['sawal']=0;
    $TAB[$bar->noakun]['salak']=0;
}

if($regional=='' && $gudang=='')
{
   $where =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
}
else if($regional!='' && $gudang=='')
{
    $where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
}
else
{
    $where =" and kodeorg ='".$gudang."'";
}




#disini tambahin kodeorg
$str="select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun from ".$dbname.".keu_saldobulanan 
      where periode ='".str_replace("-","",$periode)."'  and  noakun!='".$CLM."' ".$where."   group by noakun order by noakun";
// echo $str;
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $TAB[$bar->noakun]['sawal']+=$bar->sawal;
    $TAB[$bar->noakun]['salak']+=$bar->sawal;
}


$str="select sum(debet) as debet,sum(kredit) as kredit, noakun from ".$dbname.".keu_jurnaldt_vw
    where periode>='".$periode."' and periode<='".$periode1."' ".$where." ".$whereakun." 
    and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun"; #tidak sama dengan laba/rugi berjalan
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
		@$TAB[$bar->noakun]['debet']+=$bar->debet;
		@$TAB[$bar->noakun]['kredit']+=$bar->kredit;
} 




$no=0;
$sal_awal=array();
$sal_debet=array();;
$sal_kredit=array();;
$sal_salak=array();;     

if($tipelaporan=='excel'){
    $border = 'border=1';
}else{
    $border ='';
}
$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$gudang."'");
if($tipelaporan!='html'){
	$stream.="Laporan Neraca<br>";
	if($gudang==''){
		$unit 	= 'Seluruh Unit';
		$stream.="".$unit."<br>";
	}else{
		$unit = $gudang;
		$stream.="".$unit." - ".$nmorg[$unit]."<br>";
	}
	$stream.="Periode ".$periode." s/d ".$periode1."<br><br>";
}
$stream.="
        <table class=sortable cellspacing=1 ".$border.">
            <thead>
                <tr>
                    <th align=center style='width:50px;'>".$_SESSION['lang']['nomor']."</th>
                    <th align=center style='width:80px;'>".$_SESSION['lang']['noakun']."</th>
                    <th align=center style='width:450px;'>".$_SESSION['lang']['namaakun']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['saldoawal']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['debet']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['kredit']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['saldoakhir']."</th>
                </tr> 
            </thead>
            <tbody>";

    
        foreach($TAB as $baris => $data){
            if($data['noakun']!=''){
                if($tampilanId==1){
                    if(($data['sawal']==0)&&($data['debet']==0)&&($data['kredit']==0)){
                        continue;
                    }
                }
                $no+=1;
				@$data['salak']=$data['sawal']+$data['debet']-$data['kredit'];

                if($tipelaporan=='excel'){
                    $qsawal=$data['sawal'];
                    $qdebet=isset($data['debet'])? $data['debet']: 0;
                    $qkredit=isset($data['kredit'])? $data['kredit']: 0;
                    $qakhir=$data['salak'];
                }else{
                    $qsawal=number_format($data['sawal'],2);
                    $qdebet=number_format(isset($data['debet'])? $data['debet']: 0,2);
                    $qkredit=number_format(isset($data['kredit'])? $data['kredit']: 0,2);
                    $qakhir=number_format($data['salak'],2);
                }    

                $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$gudang."','".$revisi."',event);\">
                    <td style='width:50px;' align=center>".$no."</td>
                    <td style='width:80px;'>".$data['noakun']."</td>     
                    <td style='width:450px;'>".$data['namaakun']."</td>
                    <td align=right style='width:130px;'>".$qsawal."</td>
                    <td align=right style='width:130px;'>".$qdebet."</td>
                    <td align=right style='width:130px;'>".$qkredit."</td>   
                    <td align=right style='width:130px;'>".$qakhir."</td>    
                </tr>";
				@$gtsawal+=$data['sawal'];
				@$gtdb+=$data['debet'];
				@$gtkr+=$data['kredit'];
				@$gtsalak+=$data['salak'];
              
            }
            
        }
		
    

$stream.="<tr class=rowcontent>
            <td colspan=3 align=center><b>TOTAL</b></td>
            <td align=right><b>".number_format($gtsawal,2)."</b></td>
            <td align=right><b>".number_format($gtdb,2)."</b></td>
            <td align=right><b>".number_format($gtkr,2)."</b></td>   
            <td align=right><b>".number_format($gtsalak,2)."</b></td> 
        </tr>"; 
$stream.="</tbody>
            <tfoot>
            </tfoot>		 
        </table>";

if($tipelaporan=='html'){
	echo $stream;
}else{
	$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
	$qwe=date("YmdHms");
	$nop="NeracaSaldo_".$gudang.$periode."rev".$revisi."___".$qwe.".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("Trend_".$periode, $stream);
	$xls->addSheet("Notes_".$periode, $streamdetail);
	$xls->headers($nop);
	echo $xls->buildFile();
	
	// $stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
	// $qwe=date("YmdHms");
	// $nop_="NeracaSaldo_".$gudang.$periode."rev".$revisi."___".$qwe;
	// if(strlen($stream)>0)
	// {
		 // $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		 // gzwrite($gztralala, $stream);
		 // gzclose($gztralala);
		 // echo "<script language=javascript1.2>
			// window.location='tempExcel/".$nop_.".xls.gz';
			// </script>";
	// }
}






		
       
?>