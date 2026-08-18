<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$tanggalmulai=$_GET['tanggalmulai'];
$tanggalsampai=$_GET['tanggalsampai']; 
$noakun=$_GET['noakun'];
$kodeorg=$_GET['kodeorg'];


$whereawal = " and a.tanggal >= '2020-12-31'";

//=================================================
$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kodeorg."'");
$stream ="Laporan Hutang / Piutang<br>";
$stream.="".$kodeorg." - ".$nmorg[$kodeorg]."<br>";
$stream.="".$tanggalmulai." s/d ".$tanggalsampai."<br><br>";
$stream.="<table border=1>
            <thead>
              <tr>
                <td align=center width=50>".$_SESSION['lang']['nourut']."</td>
                <td align=center>".$_SESSION['lang']['organisasi']."</td>
                <td align=center>".$_SESSION['lang']['noakun']."</td>
                <td align=center>".$_SESSION['lang']['namaakun']."</td>
                <td align=center>".$_SESSION['lang']['karyawan']."/".$_SESSION['lang']['supplier']."</td>
                <td align=center>".$_SESSION['lang']['saldoawal']."</td>                             
                <td align=center>".$_SESSION['lang']['debet']."</td>
                <td align=center>".$_SESSION['lang']['kredit']."</td>
                <td align=center>".$_SESSION['lang']['saldoakhir']."</td>                               
              </tr>  
            </thead>
            <tbody id=container>";

$qwe=explode("-",$tanggalmulai); $tanggalmulai=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggalsampai); $tanggalsampai=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$grpWhr='group by  a.kodecustomer';
if(substr($noakun,0,3)=='211'){
  $grpWhr='group by  a.kodesupplier';
}
if(substr($noakun,0,3)=='211'){
#ambil saldo awal supplier
$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
      where a.tanggal<'".$tanggalmulai."' ".$whereawal."  and a.noakun = '".$noakun."'
	  and (a.nojurnal not like '%INVM4%' and a.nojurnal not like '%INVK4%')
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') ".$grpWhr."";
}else{
  #ambil saldo awal customer
$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.kodesupplier,c.namacustomer as namasupplier,a.kodecustomer as kodesupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".pmn_4customer c on a.kodecustomer = c.kodecustomer
      where a.tanggal<'".$tanggalmulai."' ".$whereawal."  and a.noakun = '".$noakun."' 
	  and (a.nojurnal not like '%INVM4%' and a.nojurnal not like '%INVK4%')
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi where induk ='".$kodeorg."') ".$grpWhr."";
}
//$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
//      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
//      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
//      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and kodesupplier!='' and kodesupplier is not null 
//      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by a.kodesupplier
//";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if($bar->kodesupplier==''){
        $sawal['lain']=$bar->sawal;
        $supplier['lain']='lain';        
    }else{
        $sawal[$bar->kodesupplier]=$bar->sawal;
        $supplier[$bar->kodesupplier]=$bar->namasupplier;        
    }
    $akun[$bar->noakun]=$bar->namaakun;
}

#ambil saldo awal  karyawan
$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
      where a.tanggal<'".$tanggalmulai."' ".$whereawal."  and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null 
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by c.namakaryawan
";
//$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
//      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
//      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
//      where a.tanggal<'".$tanggalmulai."'  and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null 
//      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by c.namakaryawan
//";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $sawal[$bar->nik]=$bar->sawal;
    $supplier[$bar->nik]=$bar->namakaryawan;
    $akun[$bar->noakun]=$bar->namaakun;
}

if(substr($noakun,0,3)=='211'){
#ambil  transaksi dalam periode supplier
$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier  from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
      and a.nik = ''
    and a.noakun = '".$noakun."'
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."')  ".$grpWhr."";
}else{
$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,c.namacustomer as namasupplier,a.kodecustomer as kodesupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".pmn_4customer c on a.kodecustomer = c.kodecustomer
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
      and a.nik = ''
    and a.noakun = '".$noakun."'
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."')  ".$grpWhr."";
}

#ambil  transaksi dalam periode supplier
// $str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
//       left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
//       left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
//       where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
//       and a.noakun = '".$noakun."'
//       and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by a.kodesupplier
// ";
//$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
//      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
//      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
//      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."' 
//      and a.noakun = '".$noakun."' and kodesupplier!='' and kodesupplier is not null 
//      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by a.kodesupplier
//";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if($bar->kodesupplier==''){
        $debet['lain']=$bar->debet;
        $kredit['lain']=$bar->kredit;
        $supplier['lain']='lain';
    }else{
        $debet[$bar->kodesupplier]=$bar->debet;
        $kredit[$bar->kodesupplier]=$bar->kredit;
        $supplier[$bar->kodesupplier]=$bar->namasupplier;        
    }
    $akun[$bar->noakun]=$bar->namaakun;
}

#ambil saldo transaksi  karyawan
$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."'  
      and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null 
      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by c.namakaryawan
";
//$str="select sum(a.debet) as debet,sum(a.kredit) as kredit,a.noakun, b.namaakun,a.nik,c.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
//      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
//      left join ".$dbname.".datakaryawan c on a.nik = c.karyawanid     
//      where a.tanggal between'".$tanggalmulai."' and '".$tanggalsampai."'  
//      and a.noakun = '".$noakun."' and a.nik!='' and a.nik is not null 
//      and a.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi  where induk ='".$kodeorg."') group by c.namakaryawan
//";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $debet[$bar->nik]=$bar->debet;
    $kredit[$bar->nik]=$bar->kredit;
    $supplier[$bar->nik]=$bar->namakaryawan;
    $akun[$bar->noakun]=$bar->namaakun;
}


//=================================================
$no=0;
$tsa=$td=$tk=$tak=0;
if($supplier<1)
{
        $stream.="<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
    if(!empty($supplier))foreach($supplier as $kdsupp =>$val){
        if($val!='lain'){
            $no+=1;
                    if(!isset($sawal[$kdsupp])) $sawal[$kdsupp]=0;
                    if(!isset($debet[$kdsupp])) $debet[$kdsupp]=0;
                    if(!isset($kredit[$kdsupp])) $kredit[$kdsupp]=0;
            $stream.="<tr class=rowcontent>
                  <td align=center width=20>".$no."</td>
                  <td align=center>".$kodeorg."</td>
                  <td>".$noakun."</td>
                  <td>".$akun[$noakun]."</td>
                  <td>".$kdsupp." - ".$val."</td>
                   <td align=right width=100>".number_format($sawal[$kdsupp],2)."</td>    
                  <td align=right width=100>".number_format($debet[$kdsupp],2)."</td>
                  <td align=right width=100>".number_format($kredit[$kdsupp],2)."</td>
                  <td align=right width=100>".number_format($sawal[$kdsupp]+$debet[$kdsupp]-$kredit[$kdsupp],2)."</td>
                 </tr>";
                    $tsa+=$sawal[$kdsupp];
                    $td+=$debet[$kdsupp];
                    $tk+=$kredit[$kdsupp];
                    $tak+=($sawal[$kdsupp]+$debet[$kdsupp]-$kredit[$kdsupp]);
        }
    }	
	$no+=1;
	if(!isset($sawal['lain'])) $sawal['lain']=0;
	if(!isset($debet['lain'])) $debet['lain']=0;
	if(!isset($kredit['lain'])) $kredit['lain']=0;
	if($sawal['lain']==0 && $debet['lain']==0 && $kredit['lain']==0){
    
  }else{
    $stream.="<tr class=rowcontent>
                  <td align=center width=20>".$no."</td>
                  <td align=center>".$kodeorg."</td>
                  <td>".$noakun."</td>
                  <td>".$akun[$noakun]."</td>
                  <td></td>
                   <td align=right width=100>".number_format($sawal['lain'],2)."</td>    
                  <td align=right width=100>".number_format($debet['lain'],2)."</td>
                  <td align=right width=100>".number_format($kredit['lain'],2)."</td>
                  <td align=right width=100>".number_format($sawal['lain']+$debet['lain']-$kredit['lain'],2)."</td>
                 </tr>";
  	$tsa+=$sawal['lain'];
  	$td+=$debet['lain'];
  	$tk+=$kredit['lain'];
  	$tak+=($sawal['lain']+$debet['lain']-$kredit['lain']);
  }
} 
$stream.="<tr class=rowcontent>
      <td align=center colspan=5>Total</td>
       <td align=right width=100>".number_format($tsa,2)."</td>   
      <td align=right width=100>".number_format($td,2)."</td>
      <td align=right width=100>".number_format($tk,2)."</td>
      <td align=right width=100>".number_format($tak,2)."</td>
     </tr>"; 
	 
$stream.="</tbody></table>";
$qwe=date("YmdHms");
$nop_="LP_JRNL_Hutang Dan Piutang_".$noakun."_".$qwe;
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
}
?>