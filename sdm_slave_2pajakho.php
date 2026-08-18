<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');

?>
<script language=javascript src='js/sdm_2pajak.js'></script>
<script language="JavaScript1.2" src="js/formReport.js"></script>
<?php
$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
  $gen='generic.css';
}else if($theme=='red'){
  $gen='genericRed.css';  
}else{
  $gen='genericGray.css';  
} 
echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
";
   
$proses=checkPostGet('proses','');
$kodeorg=checkPostGet('kodeorg','');
$tahun=checkPostGet('tahun','');

if(($proses=='getDetail')or($proses=='excel2')){
    
    $karid             =checkPostGet('karid','');
    $nama              =checkPostGet('nama','');
    $gajihsthn         =checkPostGet('gajihsthn','');
    $totrapel          =checkPostGet('totrapel','');
    $totbonus          =checkPostGet('totbonus','');
    $totthr            =checkPostGet('totthr','');
    $byjbt             =checkPostGet('byjbt','');
    $penghasilanjbt    =checkPostGet('penghasilanjbt','');
    $pensiun           =checkPostGet('pensiun','');
    $jml_ptkp          =checkPostGet('jml_ptkp','');
    $pkp               =checkPostGet('pkp','');
    $pph21bln          =checkPostGet('pph21bln','');
    $premiasuransi     =checkPostGet('premiasuransi','');
    $pajakterhutang    =checkPostGet('pajakterhutang','');
    $statuspajak       =checkPostGet('statuspajak','');
    $jabatan           =checkPostGet('jabatan','');
    $npwp              =checkPostGet('npwp','');
    $gajipokok         =checkPostGet('gajipokok','');
    $gajih             =checkPostGet('gajih','');
    $pajaksetahun      =$pajakterhutang;
    $tipekaryawan      =checkPostGet('tipekaryawan','');
    $lemburpremi       =checkPostGet('lemburpremi','');
    $premittp          =checkPostGet('premittp','');

    if($pkp<0){
        $pkp=0;
    }
    
echo"Print Excel
 <img onclick=\"detailExcel(event,'sdm_slave_2pajak.php?proses=excel2&nama=".$nama."&karid=".$karid."&tipekaryawan=".$tipekaryawan."&statuspajak=".$statuspajak."&jabatan=".$jabatan."&npwp=".$npwp."&gajipokok=".$gajipokok."&lemburpremi=".$lemburpremi."&premiasuransi=".$premiasuransi."&gajih=".$gajih."&totrapel=".$totrapel."&totbonus=".$totbonus."&totthr=".$totthr."&gajihsthn=".$gajihsthn."&pensiun=".$pensiun."&byjbt=".$byjbt."&penghasilanjbt=".$penghasilanjbt."&jml_ptkp=".$jml_ptkp."&pkp=".$pkp."&pajakterhutang=".$pajakterhutang."&pph21bln=".$pph21bln."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
 "; 
//echo $_GET['type'];
if($_GET['proses']!='excel2')$stream="<table class=sortable border=0 cellspacing=1>"; else
$stream="<table class=sortable border=1 cellspacing=1>";
    $stream.="<thead><tr class=rowheader>
                 <td align=center>".$_SESSION['lang']['nik']."</td>
				 <td align=center>".$_SESSION['lang']['nama']."</td>
                 <td align=center>".$_SESSION['lang']['jabatan']."</td>
				 <td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
                 <td align=center>".$_SESSION['lang']['statuspajak']."</td>
                 
                 <td align=center>".$_SESSION['lang']['npwp']."</td>
                 <td align=center>".$_SESSION['lang']['gajipokok']."</td>
                 <td align=center>".$_SESSION['lang']['lembur']." + ".$_SESSION['lang']['premi']."</td>
                 <td align=center>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['asuransi']."</td>
                 <td align=center>".$_SESSION['lang']['total']."</td>
                 <td align=center>Rapel Kenaikan Gaji</td>
                 <td align=center>Bonus</td> 
                 <td align=center>THR</td> 
                 <td align=center>Total Penghasilan Disetahunkan</td>
                 <td align=center>Iuran Pensiun</td>
                 <td align=center>Biaya Jabatan</td>
                 <td align=center>Penghasilan Netto Setahun</td>
                 <td align=center>PTKP</td>
                 <td align=center>PKP</td>
                 <td align=center>Pajak Terhutang Setahun</td>
                 <td align=center>Total Pajak BI</td>
                </tr></thead>";
    $stream.= "<tbody><tr class=rowcontent>
                 <td>".$karid."</td>
				 <td>".$nama."</td>
                 <td>".$jabatan."</td>
				 <td>".$tipekaryawan."</td>
                 <td>".$statuspajak."</td>
                 
                 <td align=right>".$npwp."</td>
                 <td align=right>".number_format($gajipokok,0)."</td>
                 <td align=right>".number_format($lemburpremi,0)."</td>    
                 <td align=right>".number_format($premiasuransi,0)."</td>
                 <td align=right>".number_format($gajih,0)."</td>
                 <td align=right>".number_format($totrapel,0)."</td>
                 <td align=right>".number_format($totbonus,0)."</td>
                 <td align=right>".number_format($totthr,0)."</td>
                 <td align=right>".number_format($gajihsthn,0)."</td>
                 <td align=right>".number_format($pensiun,0)."</td>
                 <td align=right>".number_format($byjbt,0)."</td>
                 <td align=right>".number_format($penghasilanjbt,0)."</td>
                 <td align=right>".number_format($jml_ptkp,0)."</td>
                 <td align=right>".number_format($pkp,0)."</td>
                 <td align=right>".number_format($pajakterhutang,0)."</td>
                 <td align=right>".number_format($pph21bln,0)."</td>
                </tr>"; 
    $stream.="</tbody></table>";
    if($_GET['proses']=='excel2')
   {
        $nop_="Detail Pajak";
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
   }
   else
   {
       echo $stream;
   }    
    exit();
}  

if(($proses=='preview')or($proses=='excel')){
    if($kodeorg==''){
        echo"Error: Unit tidak boleh kosong."; exit;
    }	
    if($tahun==''){
        echo"Error: Tahun tidak boleh kosong."; exit;
    }	
}
        
if($proses=='excel')
    $stream.="<table border='1'>";
else {
    $stream.="<table cellspacing='1' border='0' class='sortable' width=3500px>";
}
$stream.="<thead>
<tr class=rowheader>
<td align=center>".$_SESSION['lang']['nomor']."</td>
<td align=center>".$_SESSION['lang']['kodeorg']."</td>    
<td align=center>NIK</td>
<td align=center>".$_SESSION['lang']['namakaryawan']."</td>            
<td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
<td align=center>".$_SESSION['lang']['statuspajak']."</td>
<td align=center>".$_SESSION['lang']['npwp']."</td>  
<td align=center>".$_SESSION['lang']['tahun']."</td>
<td align=center>".$_SESSION['lang']['penghasilan']." - 01</td>
<td align=center>".$_SESSION['lang']['gaji']." - 01</td>   
<td align=center>Tunjangan - 01</td>  
<td align=center>".$_SESSION['lang']['pph12']." - 01</td>";    
$stream.="<td align=center>".$_SESSION['lang']['penghasilan']." - 02</td>
<td align=center>".$_SESSION['lang']['gaji']." - 02</td>   
<td align=center>Tunjangan - 02</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 02</td>    
<td align=center>Penghasilan - 03</td>
<td align=center>Gaji - 03</td>   
<td align=center>Tunjangan - 03</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 03</td>    
<td align=center>Penghasilan - 04</td>
<td align=center>Gaji - 04</td>   
<td align=center>Tunjangan - 04</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 04</td>    
<td align=center>Penghasilan - 05</td>
<td align=center>Gaji - 05</td>   
<td align=center>Tunjangan - 05</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 05</td>    
<td align=center>Penghasilan - 06</td>
<td align=center>Gaji - 06</td>   
<td align=center>Tunjangan - 06</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 06</td>    
<td align=center>Penghasilan - 07</td>
<td align=center>Gaji - 07</td>   
<td align=center>Tunjangan - 07</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 07</td>    
<td align=center>Penghasilan - 08</td>
<td align=center>Gaji - 08</td>   
<td align=center>Tunjangan - 08</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 08</td>    
<td align=center>Penghasilan - 09</td>
<td align=center>Gaji - 09</td>   
<td align=center>Tunjangan - 09</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 09</td>    
<td align=center>Penghasilan - 10</td>
<td align=center>Gaji - 10</td>   
<td align=center>Tunjangan - 10</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 10</td>    
<td align=center>Penghasilan - 11</td>
<td align=center>Gaji - 11</td>   
<td align=center>Tunjangan - 11</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 11</td>    
<td align=center>Penghasilan - 12</td>
<td align=center>Gaji - 12</td>   
<td align=center>Tunjangan - 12</td>      
<td align=center>".$_SESSION['lang']['pph12']." - 12</td>    
<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['penghasilan']."</td>
<td align=center>Total Gaji</td>   
<td align=center>Total Tunjangan</td>      
<td align=center>PPh21 Tahunan</td> 
</tr>   
</thead>
<tbody>";

// kamus tipe karyawan
$str="select id, tipe from ".$dbname.".sdm_5tipekaryawan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $kamusTipe[$bar->id]=$bar->tipe;
    }

 $optNmjabatan=makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
// kamus data karyawan
    $kamusKar=Array();
$str="select nik, karyawanid, namakaryawan, tipekaryawan, statuspajak, lokasitugas, subbagian,npwp,kodejabatan from ".$dbname.".datakaryawan 
    where kodeorganisasi='".$kodeorg."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $kamusKar[$bar->karyawanid]['nik']=$bar->nik;
        $kamusKar[$bar->karyawanid]['nama']=$bar->namakaryawan;
        $kamusKar[$bar->karyawanid]['tipe']=$bar->tipekaryawan;
        $kamusKar[$bar->karyawanid]['status']=$bar->statuspajak;
        $kamusKar[$bar->karyawanid]['lokasi']=$bar->lokasitugas;
        $kamusKar[$bar->karyawanid]['bagian']=$bar->subbagian;
        $kamusKar[$bar->karyawanid]['jabatan']=$bar->kodejabatan;
        $kamusKar[$bar->karyawanid]['npwp']=str_replace(" ","",str_replace(".","",$bar->npwp));
        if (!is_numeric($kamusKar[$bar->karyawanid]['npwp'])) {
            $kamusKar[$bar->karyawanid]['npwp']='';
        }
        else if(intval($kamusKar[$bar->karyawanid]['npwp'])>0 and strlen(intval($kamusKar[$bar->karyawanid]['npwp'])>12))
        {
            
        }
        else
        {
           $kamusKar[$bar->karyawanid]['npwp']=$bar->npwp; 
        }   
    }
//ambil porsi JMS dari perusahaan yang kena pajak
    $plusJMS=0;
    $str="select value from ".$dbname.".sdm_ho_hr_jms_porsi where id='pph21'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
      $plusJMS=$bar->value;
    }   
//ambil biaya jabatan    
    $jabPersen=0;
    $jabMax=0;
    $str="select persen,max from ".$dbname.".sdm_ho_pph21jabatan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $jabPersen=$bar->persen/100;
        $jabMax=$bar->max*12;
    }    
    
//Ambil PTKP:
    $ptkp=Array();
    $str="select id,value from ".$dbname.".sdm_ho_pph21_ptkp";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $ptkp[$bar->id]=$bar->value;
    } 
    
//ambil tarif pph21
  $pphtarif=Array();  
  $pphpercent=Array();  
  $str="select level,percent,upto from ".$dbname.".sdm_ho_pph21_kontribusi order by level";
  $urut=0;
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch())
    {
        $pphtarif[$urut]    =$bar->upto;
        $pphpercent[$urut]  =$bar->percent/100;      
        $urut+=1;  
    }   
//ambil gaji pokok yang akan dikali dengan porsi jms dari perusahaan
$str="select sum(jumlah) as gaji, karyawanid, substr(periodegaji,6,2) as bulan from ".$dbname.".sdm_gaji 
    where idkomponen=1 and periodegaji like '".$tahun."%'
    and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."') group by karyawanid, periodegaji order by karyawanid";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$dJMS=Array();  
while($bar=$res->fetch())
{
	setIt($dJMS[$bar->karyawanid]['gptahunan'],0);
    $dJMS[$bar->karyawanid][$bar->bulan]=$bar->gaji*$plusJMS/100;
    $dJMS[$bar->karyawanid]['gapok'][$bar->bulan]=$bar->gaji;
    $dJMS[$bar->karyawanid]['gptahunan']+=$bar->gaji;//gaji pokok tahunan
} 

// rapel,bonus,thr 
$stambah="select jumlah, karyawanid,substr(periodegaji,6,2) as bulan,idkomponen
          from ".$dbname.".sdm_gaji 
          where idkomponen in (14,26,28,40) and periodegaji like '".$tahun."%'
          and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."') ";
//exit("error: ".$stambah);
$rtambah=$owlPDO->query($stambah) or die(print " Gagal: ".PDOException::getMessage());
$rtambah->setFetchMode(PDO::FETCH_OBJ);
while($bar=$rtambah->fetch())
{
	if($bar->idkomponen=='14'){
		setIt($rapel[$bar->karyawanid][$bar->bulan],0);
		$rapel[$bar->karyawanid][$bar->bulan]+=$bar->jumlah;       
	}
	else if($bar->idkomponen=='26'){
		setIt($bonus[$bar->karyawanid][$bar->bulan],0);
		$bonus[$bar->karyawanid][$bar->bulan]+=$bar->jumlah;       
	}
	else if($bar->idkomponen=='28'){
		setIt($thr[$bar->karyawanid][$bar->bulan],0);
		$thr[$bar->karyawanid][$bar->bulan]+=$bar->jumlah;   
	} 
}

$sGapok="select jumlah as gapok, karyawanid from ".$dbname.".sdm_5gajipokokho 
    where idkomponen =1 and tahun like '".$tahun."%'
    group by karyawanid order by karyawanid";
$rGapok=$owlPDO->query($sGapok) or die(print " Gagal: ".PDOException::getMessage());
$rGapok->setFetchMode(PDO::FETCH_OBJ);
while($bar=$rGapok->fetch())
{
   $gapok[$bar->karyawanid]=$bar->gapok;
   $pot2[$bar->karyawanid]=$gapok[$bar->karyawanid]*12;   
//   $pot2[$bar->karyawanid]+=$rapel[$bar->karyawanid][$bar->bulan]+$bonus[$bar->karyawanid][$bar->bulan]+$thr[$bar->karyawanid][$bar->bulan];
//   $pot2[$bar->karyawanid]=$pot2[$bar->karyawanid]*2/100;
   
}  

// ambil potonganHK

// total gaji yang kena pph
$str="select sum(a.jumlah * case b.plus when 0 then -1 else b.plus end) as gaji, a.karyawanid, 
substr(a.periodegaji,6,2) as bulan from ".$dbname.".sdm_gaji a
    left join ".$dbname.".sdm_ho_component b on a.idkomponen = b.id
    where a.idkomponen in (select id from ".$dbname.".sdm_ho_component where pph21=1)
    and a.periodegaji like '".$tahun."%'
    and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."')
	group by a.karyawanid, a.periodegaji order by a.karyawanid";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$dzKar=Array();  
$dzArr=Array();  
while($bar=$res->fetch())
{
	$karid = $bar->karyawanid;
	
	setIt($dzArr[$karid]['pph21']['01'],0);
	setIt($dzArr[$karid]['pph21']['02'],0);
	setIt($dzArr[$karid]['pph21']['03'],0);
	setIt($dzArr[$karid]['pph21']['04'],0);
	setIt($dzArr[$karid]['pph21']['05'],0);
	setIt($dzArr[$karid]['pph21']['06'],0);
	setIt($dzArr[$karid]['pph21']['07'],0);
	setIt($dzArr[$karid]['pph21']['08'],0);
	setIt($dzArr[$karid]['pph21']['09'],0);
	setIt($dzArr[$karid]['pph21']['10'],0);
	setIt($dzArr[$karid]['pph21']['11'],0);
	setIt($dzArr[$karid]['pph21']['12'],0);
	
	setIt($dzArr[$karid]['01'],0);
	setIt($dzArr[$karid]['02'],0);
	setIt($dzArr[$karid]['03'],0);
	setIt($dzArr[$karid]['04'],0);
	setIt($dzArr[$karid]['05'],0);
	setIt($dzArr[$karid]['06'],0);
	setIt($dzArr[$karid]['07'],0);
	setIt($dzArr[$karid]['08'],0);
	setIt($dzArr[$karid]['09'],0);
	setIt($dzArr[$karid]['10'],0);
	setIt($dzArr[$karid]['11'],0);
	setIt($dzArr[$karid]['12'],0);
	
	setIt($rapel[$karid]['01'],0);
	setIt($rapel[$karid]['02'],0);
	setIt($rapel[$karid]['03'],0);
	setIt($rapel[$karid]['04'],0);
	setIt($rapel[$karid]['05'],0);
	setIt($rapel[$karid]['06'],0);
	setIt($rapel[$karid]['07'],0);
	setIt($rapel[$karid]['08'],0);
	setIt($rapel[$karid]['09'],0);
	setIt($rapel[$karid]['10'],0);
	setIt($rapel[$karid]['11'],0);
	setIt($rapel[$karid]['12'],0);
	
	setIt($bonus[$karid]['01'],0);
	setIt($bonus[$karid]['02'],0);
	setIt($bonus[$karid]['03'],0);
	setIt($bonus[$karid]['04'],0);
	setIt($bonus[$karid]['05'],0);
	setIt($bonus[$karid]['06'],0);
	setIt($bonus[$karid]['07'],0);
	setIt($bonus[$karid]['08'],0);
	setIt($bonus[$karid]['09'],0);
	setIt($bonus[$karid]['10'],0);
	setIt($bonus[$karid]['11'],0);
	setIt($bonus[$karid]['12'],0);
	
	setIt($thr[$karid]['01'],0);
	setIt($thr[$karid]['02'],0);
	setIt($thr[$karid]['03'],0);
	setIt($thr[$karid]['04'],0);
	setIt($thr[$karid]['05'],0);
	setIt($thr[$karid]['06'],0);
	setIt($thr[$karid]['07'],0);
	setIt($thr[$karid]['08'],0);
	setIt($thr[$karid]['09'],0);
	setIt($thr[$karid]['10'],0);
	setIt($thr[$karid]['11'],0);
	setIt($thr[$karid]['12'],0);
	
	setIt($premitetap[$karid]['01'],0);
	setIt($premitetap[$karid]['02'],0);
	setIt($premitetap[$karid]['03'],0);
	setIt($premitetap[$karid]['04'],0);
	setIt($premitetap[$karid]['05'],0);
	setIt($premitetap[$karid]['06'],0);
	setIt($premitetap[$karid]['07'],0);
	setIt($premitetap[$karid]['08'],0);
	setIt($premitetap[$karid]['09'],0);
	setIt($premitetap[$karid]['10'],0);
	setIt($premitetap[$karid]['11'],0);
	setIt($premitetap[$karid]['12'],0);
	
	setIt($dJMS[$karid]['01'],0);
	setIt($dJMS[$karid]['02'],0);
	setIt($dJMS[$karid]['03'],0);
	setIt($dJMS[$karid]['04'],0);
	setIt($dJMS[$karid]['05'],0);
	setIt($dJMS[$karid]['06'],0);
	setIt($dJMS[$karid]['07'],0);
	setIt($dJMS[$karid]['08'],0);
	setIt($dJMS[$karid]['09'],0);
	setIt($dJMS[$karid]['10'],0);
	setIt($dJMS[$karid]['11'],0);
	setIt($dJMS[$karid]['12'],0);
	
	setIt($dzArr[$karid]['byjab']['01'],0);
	setIt($dzArr[$karid]['byjab']['02'],0);
	setIt($dzArr[$karid]['byjab']['03'],0);
	setIt($dzArr[$karid]['byjab']['04'],0);
	setIt($dzArr[$karid]['byjab']['05'],0);
	setIt($dzArr[$karid]['byjab']['06'],0);
	setIt($dzArr[$karid]['byjab']['07'],0);
	setIt($dzArr[$karid]['byjab']['08'],0);
	setIt($dzArr[$karid]['byjab']['09'],0);
	setIt($dzArr[$karid]['byjab']['10'],0);
	setIt($dzArr[$karid]['byjab']['11'],0);
	setIt($dzArr[$karid]['byjab']['12'],0);
	
	setIt($dJMS[$karid]['gapok']['01'],0);
	setIt($dJMS[$karid]['gapok']['02'],0);
	setIt($dJMS[$karid]['gapok']['03'],0);
	setIt($dJMS[$karid]['gapok']['04'],0);
	setIt($dJMS[$karid]['gapok']['05'],0);
	setIt($dJMS[$karid]['gapok']['06'],0);
	setIt($dJMS[$karid]['gapok']['07'],0);
	setIt($dJMS[$karid]['gapok']['08'],0);
	setIt($dJMS[$karid]['gapok']['09'],0);
	setIt($dJMS[$karid]['gapok']['10'],0);
	setIt($dJMS[$karid]['gapok']['11'],0);
	setIt($dJMS[$karid]['gapok']['12'],0);
	
	setIt($gapok[$karid],0);
	
	setIt($dzArr[$bar->karyawanid]['total'],0);
    $dzKar[$bar->karyawanid]=$bar->karyawanid;
    $dzArr[$bar->karyawanid]['karyawanid']=$bar->karyawanid;
    $dzArr[$bar->karyawanid][$bar->bulan]=$bar->gaji+$dJMS[$bar->karyawanid][$bar->bulan];//sesuai mba kisni
    $dzArr[$bar->karyawanid]['total']+=$bar->gaji;
    
    //hitung PPH21====================================================
    //penghasilan disetahunkan
    $dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan]=(($bar->gaji+$dJMS[$bar->karyawanid][$bar->bulan])*12);//disetahunkan

    // tambahkan rapel bonus thr dll
	setIt($dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan],0);
	setIt($rapel[$bar->karyawanid][$bar->bulan],0);
	setIt($bonus[$bar->karyawanid][$bar->bulan],0);
	setIt($thr[$bar->karyawanid][$bar->bulan],0);
    $dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan]+=$rapel[$bar->karyawanid][$bar->bulan]+$bonus[$bar->karyawanid][$bar->bulan]+$thr[$bar->karyawanid][$bar->bulan];
    
        
    //periksa By jab dan kurangkan
    $dzArr[$bar->karyawanid]['byjab'][$bar->bulan]=$jabPersen*($dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan]);
    if($dzArr[$bar->karyawanid]['byjab'][$bar->bulan]>$jabMax){//jika lebih dari max maka dibatasi sebesar max
        $dzArr[$bar->karyawanid]['byjab'][$bar->bulan]=$jabMax;
    }    
   
    //penghasilan setela kurang By Jabatan
    $dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan]=$dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan]-$dzArr[$bar->karyawanid]['byjab'][$bar->bulan];
    
	// kurangi 2% gapok
	setIt($dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan],0);
    $dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan]-=(($dJMS[$bar->karyawanid]['gapok'][$bar->bulan]*12)+($rapel[$bar->karyawanid][$bar->bulan]+$bonus[$bar->karyawanid][$bar->bulan]+$thr[$bar->karyawanid][$bar->bulan]))*2/100;
    

	//kurangi dengan PTKP sehingga menghasilkan pkp:
	setIt($dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan],0);
	setIt($kamusKar[$bar->karyawanid]['status'],'');
	setIt($ptkp[str_replace("K","",$kamusKar[$bar->karyawanid]['status'])],0);
    $dzArr[$bar->karyawanid]['pkp'][$bar->bulan]=$dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan]-$ptkp[str_replace("K","",$kamusKar[$bar->karyawanid]['status'])]; 
    
	
    $zz=0;
    $sisazz=0;
// print_r($dzArr);

    if($dzArr[$bar->karyawanid]['pkp'][$bar->bulan]>0){         
    #tahap 1: 
    if($dzArr[$bar->karyawanid]['pkp'][$bar->bulan]<$pphtarif[0])
    {
        $zz+=$pphpercent[0]*$dzArr[$bar->karyawanid]['pkp'][$bar->bulan];
        $sisazz=0; 
    }
    else if($dzArr[$bar->karyawanid]['pkp'][$bar->bulan]>=$pphtarif[0])
    {
        $zz+=$pphpercent[0]*$pphtarif[0];
        $sisazz=$dzArr[$bar->karyawanid]['pkp'][$bar->bulan]-$pphtarif[0];
        #level 2
            if($sisazz<($pphtarif[1]-$pphtarif[0]))
            {
                $zz+=$pphpercent[1]*$sisazz;
                $sisazz=0;        
            }    
            else if($sisazz>=($pphtarif[1]-$pphtarif[0]))
            {
                $zz+=$pphpercent[1]*($pphtarif[1]-$pphtarif[0]);
                $sisazz=$dzArr[$bar->karyawanid]['pkp'][$bar->bulan]-$pphtarif[1]; 
                #level 3   
                    if($sisazz<($pphtarif[2]-$pphtarif[1]))
                    {
                        $zz+=$pphpercent[2]*$sisazz;
                        $sisazz=0;        
                    }    
                    else if($sisazz>=($pphtarif[2]-$pphtarif[1]))
                    {
                        $zz+=$pphpercent[2]*($pphtarif[2]-$pphtarif[1]);
                        $sisazz=$dzArr[$bar->karyawanid]['pkp'][$bar->bulan]-$pphtarif[2];
                         // print_r($sisazz);exit();
                            if($sisazz>0){
                            #level 4  sisanya kali 30% 
                                $zz+=$pphpercent[3]*$sisazz;  
                            }                          
                    } 
            }   
                   
    }
    }
    
	

	
	
    //masukkan ke array utama
   

    $dzArr[$bar->karyawanid]['pph21'][$bar->bulan]=$zz/12;
//    echo "<pre>";
//       print_r($bar->karyawanid."=>".$dzArr[$bar->karyawanid]['pph21'][$bar->bulan]);
//       echo "<pre>";
//     
    //jika tidak memiliki NPWP maka tambahkan 20% dari PPh yang ada
//    if($kamusKar[$bar->karyawanid]['npwp']=='')
//    {
//         $dzArr[$bar->karyawanid]['pph21'][$bar->bulan]= $dzArr[$bar->karyawanid]['pph21'][$bar->bulan]+ ($dzArr[$bar->karyawanid]['pph21'][$bar->bulan]*20/100);
//    }
    
}

//    echo "error gaji:".number_format($dzArr[$bar->karyawanid][$bar->bulan])."_\n";
//    echo "error penghasilan setahun:".number_format((($bar->gaji+$dJMS[$bar->karyawanid][$bar->bulan])*12))."_\n";
//    echo "error jabPersen:".number_format($jabPersen)."_\n";
//    echo "error jabMax:".number_format($jabMax)."_\n";
//    echo "error byjab:".number_format($dzArr[$bar->karyawanid]['byjab'][$bar->bulan])."_\n";
//    echo "error penghasilan setelah jabatan:".number_format($dzArr[$bar->karyawanid]['penghasilan'][$bar->bulan])."_\n";
//    echo "error ptkp:".number_format($ptkp[str_replace("K","",$kamusKar[$bar->karyawanid]['status'])])."_\n";
//    echo "error pkp:".number_format($dzArr[$bar->karyawanid]['pkp'][$bar->bulan])."_\n";
//    echo "error zz:".number_format($zz)."_\n";
//    echo "error pph:".number_format($dzArr[$bar->karyawanid]['pph21'][$bar->bulan])."_\n";
//    echo "error gapok:".number_format($gapok[$bar->karyawanid])."_\n";
//    echo "error tambahan per bulan:".number_format($b[$bar->karyawanid][$bar->bulan])."_\n";
//    echo "error tambahan setahun:".number_format($tambahan[$bar->karyawanid])."_\n";         
//    echo "error p2%:".number_format($pot2[$bar->karyawanid])."_\n";


$no=0;
// display data
if(!empty($dzKar))foreach($dzKar as $karid){
    $no++;
    $id=$karid;
	
	setIt($dzArr[$karid]['pph21']['01'],0);
	setIt($dzArr[$karid]['pph21']['02'],0);
	setIt($dzArr[$karid]['pph21']['03'],0);
	setIt($dzArr[$karid]['pph21']['04'],0);
	setIt($dzArr[$karid]['pph21']['05'],0);
	setIt($dzArr[$karid]['pph21']['06'],0);
	setIt($dzArr[$karid]['pph21']['07'],0);
	setIt($dzArr[$karid]['pph21']['08'],0);
	setIt($dzArr[$karid]['pph21']['09'],0);
	setIt($dzArr[$karid]['pph21']['10'],0);
	setIt($dzArr[$karid]['pph21']['11'],0);
	setIt($dzArr[$karid]['pph21']['12'],0);
	
	setIt($dzArr[$karid]['01'],0);
	setIt($dzArr[$karid]['02'],0);
	setIt($dzArr[$karid]['03'],0);
	setIt($dzArr[$karid]['04'],0);
	setIt($dzArr[$karid]['05'],0);
	setIt($dzArr[$karid]['06'],0);
	setIt($dzArr[$karid]['07'],0);
	setIt($dzArr[$karid]['08'],0);
	setIt($dzArr[$karid]['09'],0);
	setIt($dzArr[$karid]['10'],0);
	setIt($dzArr[$karid]['11'],0);
	setIt($dzArr[$karid]['12'],0);
	
	setIt($rapel[$karid]['01'],0);
	setIt($rapel[$karid]['02'],0);
	setIt($rapel[$karid]['03'],0);
	setIt($rapel[$karid]['04'],0);
	setIt($rapel[$karid]['05'],0);
	setIt($rapel[$karid]['06'],0);
	setIt($rapel[$karid]['07'],0);
	setIt($rapel[$karid]['08'],0);
	setIt($rapel[$karid]['09'],0);
	setIt($rapel[$karid]['10'],0);
	setIt($rapel[$karid]['11'],0);
	setIt($rapel[$karid]['12'],0);
	
	setIt($bonus[$karid]['01'],0);
	setIt($bonus[$karid]['02'],0);
	setIt($bonus[$karid]['03'],0);
	setIt($bonus[$karid]['04'],0);
	setIt($bonus[$karid]['05'],0);
	setIt($bonus[$karid]['06'],0);
	setIt($bonus[$karid]['07'],0);
	setIt($bonus[$karid]['08'],0);
	setIt($bonus[$karid]['09'],0);
	setIt($bonus[$karid]['10'],0);
	setIt($bonus[$karid]['11'],0);
	setIt($bonus[$karid]['12'],0);
	
	setIt($thr[$karid]['01'],0);
	setIt($thr[$karid]['02'],0);
	setIt($thr[$karid]['03'],0);
	setIt($thr[$karid]['04'],0);
	setIt($thr[$karid]['05'],0);
	setIt($thr[$karid]['06'],0);
	setIt($thr[$karid]['07'],0);
	setIt($thr[$karid]['08'],0);
	setIt($thr[$karid]['09'],0);
	setIt($thr[$karid]['10'],0);
	setIt($thr[$karid]['11'],0);
	setIt($thr[$karid]['12'],0);
	
	setIt($premitetap[$karid]['01'],0);
	setIt($premitetap[$karid]['02'],0);
	setIt($premitetap[$karid]['03'],0);
	setIt($premitetap[$karid]['04'],0);
	setIt($premitetap[$karid]['05'],0);
	setIt($premitetap[$karid]['06'],0);
	setIt($premitetap[$karid]['07'],0);
	setIt($premitetap[$karid]['08'],0);
	setIt($premitetap[$karid]['09'],0);
	setIt($premitetap[$karid]['10'],0);
	setIt($premitetap[$karid]['11'],0);
	setIt($premitetap[$karid]['12'],0);
	
	setIt($dJMS[$karid]['01'],0);
	setIt($dJMS[$karid]['02'],0);
	setIt($dJMS[$karid]['03'],0);
	setIt($dJMS[$karid]['04'],0);
	setIt($dJMS[$karid]['05'],0);
	setIt($dJMS[$karid]['06'],0);
	setIt($dJMS[$karid]['07'],0);
	setIt($dJMS[$karid]['08'],0);
	setIt($dJMS[$karid]['09'],0);
	setIt($dJMS[$karid]['10'],0);
	setIt($dJMS[$karid]['11'],0);
	setIt($dJMS[$karid]['12'],0);
	
	setIt($dzArr[$karid]['byjab']['01'],0);
	setIt($dzArr[$karid]['byjab']['02'],0);
	setIt($dzArr[$karid]['byjab']['03'],0);
	setIt($dzArr[$karid]['byjab']['04'],0);
	setIt($dzArr[$karid]['byjab']['05'],0);
	setIt($dzArr[$karid]['byjab']['06'],0);
	setIt($dzArr[$karid]['byjab']['07'],0);
	setIt($dzArr[$karid]['byjab']['08'],0);
	setIt($dzArr[$karid]['byjab']['09'],0);
	setIt($dzArr[$karid]['byjab']['10'],0);
	setIt($dzArr[$karid]['byjab']['11'],0);
	setIt($dzArr[$karid]['byjab']['12'],0);
	
	setIt($dJMS[$karid]['gapok']['01'],0);
	setIt($dJMS[$karid]['gapok']['02'],0);
	setIt($dJMS[$karid]['gapok']['03'],0);
	setIt($dJMS[$karid]['gapok']['04'],0);
	setIt($dJMS[$karid]['gapok']['05'],0);
	setIt($dJMS[$karid]['gapok']['06'],0);
	setIt($dJMS[$karid]['gapok']['07'],0);
	setIt($dJMS[$karid]['gapok']['08'],0);
	setIt($dJMS[$karid]['gapok']['09'],0);
	setIt($dJMS[$karid]['gapok']['10'],0);
	setIt($dJMS[$karid]['gapok']['11'],0);
	setIt($dJMS[$karid]['gapok']['12'],0);
	
	setIt($gapok[$karid],0);
	setIt($kamusKar[$karid]['nik'],'');
	setIt($kamusKar[$karid]['nama'],'');
	setIt($kamusKar[$karid]['status'],'');
	setIt($kamusKar[$karid]['npwp'],'');
	setIt($kamusKar[$karid]['jabatan'],'');
	setIt($kamusKar[$karid]['tipe'],'');
	setIt($kamusKar[$karid]['bagian'],'');
	setIt($kamusKar[$karid]['lokasi'],'');
	setIt($optNmjabatan[$kamusKar[$karid]['jabatan']],'');
	setIt($kamusTipe[$kamusKar[$karid]['tipe']],'');
	
	setIt($dJMS[$karid]['gptahunan'],0);
    
	$gajih              =$dzArr[$karid]['01'];
    $totrapel           =$rapel[$karid]['01'];
    $totbonus           =$bonus[$karid]['01'];
    $totthr             =$thr[$karid]['01'];
    $premiasuransi      =$dJMS[$karid]['01'];
    $gajibln            =$dJMS[$karid]['gapok']['01'];     
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['01'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['01']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['01'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<tr class=rowcontent style='cursor:pointer;'>
    <td align=right>".$no."</td>";
    if($kamusKar[$karid]['bagian']!='')$stream.="<td align=left>".$kamusKar[$karid]['bagian']."</td>"; 
	else $stream.="<td align=left>".$kamusKar[$karid]['lokasi']."</td>";
    
	$stream.="<td align=left>".$kamusKar[$karid]['nik']."</td>
    <td align=left >".$kamusKar[$karid]['nama']."</td>
    <td align=left>".$kamusTipe[$kamusKar[$karid]['tipe']]."</td>
    <td align=left>".$kamusKar[$karid]['status']."</td>
    <td align=left>".$kamusKar[$karid]['npwp']."</td>
    <td align=center>".$tahun."</td>
    <td align=right style='color:#0000FF;'  style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['01'])."</td>
    <td align=right style='color:#0000FF;'  style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['01'])."</td>
    <td align=right style='color:#0000FF;'  style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['01']-$dJMS[$karid]['gapok']['01']-$dJMS[$karid]['01'])."</td>    
    <td align=right style='color:#0000FF;'  style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['01'])."</td>";        

    $gajih              =$dzArr[$karid]['02'];
    $totrapel           =$rapel[$karid]['02'];
    $totbonus           =$bonus[$karid]['02'];
    $totthr             =$thr[$karid]['02'];
    $premiasuransi      =$dJMS[$karid]['02'];
    $gajibln            =$dJMS[$karid]['gapok']['02'];
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['02'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['02']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['02'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['02'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['02'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['02']-$dJMS[$karid]['gapok']['02']-$dJMS[$karid]['02'])."</td>         
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['02'])."</td>";
        
    $gajih              =$dzArr[$karid]['03'];
    $totrapel           =$rapel[$karid]['03'];
    $totbonus           =$bonus[$karid]['03'];
    $totthr             =$thr[$karid]['03'];
    $premiasuransi      =$dJMS[$karid]['03'];
    $gajibln            =$dJMS[$karid]['gapok']['03'];
        
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['03'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['03']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['03'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['03']+$premitetap[$karid]['03'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['03'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['03']-$dJMS[$karid]['gapok']['03']-$dJMS[$karid]['03']+$premitetap[$karid]['03'])."</td>         
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['03'])."</td>";
    
    $gajih              =$dzArr[$karid]['04'];
    $totrapel           =$rapel[$karid]['04'];
    $totbonus           =$bonus[$karid]['04'];
    $totthr             =$thr[$karid]['04'];
    $premiasuransi      =$dJMS[$karid]['04'];
    $gajibln            =$dJMS[$karid]['gapok']['04'];
    $premittp           =$premitetap[$karid]['04'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['04'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['04']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['04'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;

    $stream.="<td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['04']+$premitetap[$karid]['04'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['04'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['04']-$dJMS[$karid]['gapok']['04']-$dJMS[$karid]['04']+$premitetap[$karid]['04'])."</td>         
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['04'])."</td>";
    
    $gajih              =$dzArr[$karid]['05'];
    $totrapel           =$rapel[$karid]['05'];
    $totbonus           =$bonus[$karid]['05'];
    $totthr             =$thr[$karid]['05'];
    $premiasuransi      =$dJMS[$karid]['05'];
    $gajibln            =$dJMS[$karid]['gapok']['05'];
    $premittp           =$premitetap[$karid]['05'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['05'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['05']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['05'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['05']+$premitetap[$karid]['05'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['05'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['05']-$dJMS[$karid]['gapok']['05']-$dJMS[$karid]['05']+$premitetap[$karid]['05'])."</td>         
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['05'])."</td>";
    
    $gajih              =$dzArr[$karid]['06'];
    $totrapel           =$rapel[$karid]['06'];
    $totbonus           =$bonus[$karid]['06'];
    $totthr             =$thr[$karid]['06'];
    $premiasuransi      =$dJMS[$karid]['06'];
    $gajibln            =$dJMS[$karid]['gapok']['06'];
    $premittp           =$premitetap[$karid]['06'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['06'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['06']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['06'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['06']+$premitetap[$karid]['06'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['06'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['06']-$dJMS[$karid]['gapok']['06']-$dJMS[$karid]['06']+$premitetap[$karid]['06'])."</td>         
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['06'])."</td>";
    
    $gajih              =$dzArr[$karid]['07'];
    $totrapel           =$rapel[$karid]['07'];
    $totbonus           =$bonus[$karid]['07'];
    $totthr             =$thr[$karid]['07'];
    $premiasuransi      =$dJMS[$karid]['07'];
    $gajibln            =$dJMS[$karid]['gapok']['07'];
    $premittp           =$premitetap[$karid]['07'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['07'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['07']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['07'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['07']+$premitetap[$karid]['07'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['07'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['07']-$dJMS[$karid]['gapok']['07']-$dJMS[$karid]['07']+$premitetap[$karid]['07'])."</td>         
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['07'])."</td>";
    
    $gajih              =$dzArr[$karid]['08'];
    $totrapel           =$rapel[$karid]['08'];
    $totbonus           =$bonus[$karid]['08'];
    $totthr             =$thr[$karid]['08'];
    $premiasuransi      =$dJMS[$karid]['08'];
    $gajibln            =$dJMS[$karid]['gapok']['08'];
    $premittp           =$premitetap[$karid]['08'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['08'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['08']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['08'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['08']+$premitetap[$karid]['08'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['08'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['08']-$dJMS[$karid]['gapok']['08']-$dJMS[$karid]['08']+$premitetap[$karid]['08'])."</td>         
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['08'])."</td>";
    
    $gajih              =$dzArr[$karid]['09'];
    $totrapel           =$rapel[$karid]['09'];
    $totbonus           =$bonus[$karid]['09'];
    $totthr             =$thr[$karid]['09'];
    $premiasuransi      =$dJMS[$karid]['09'];
    $gajibln            =$dJMS[$karid]['gapok']['09'];
    $premittp           =$premitetap[$karid]['09'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['09'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['09']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['09'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['09']+$premitetap[$karid]['09'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['09'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['09']-$dJMS[$karid]['gapok']['09']-$dJMS[$karid]['09']+$premitetap[$karid]['09'])."</td>         
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['09'])."</td>";
    
    $gajih              =$dzArr[$karid]['10'];
    $totrapel           =$rapel[$karid]['10'];
    $totbonus           =$bonus[$karid]['10'];
    $totthr             =$thr[$karid]['10'];
    $premiasuransi      =$dJMS[$karid]['10'];
    $gajibln            =$dJMS[$karid]['gapok']['10'];
    $premittp           =$premitetap[$karid]['10'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['10'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['10']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['10'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['10']+$premitetap[$karid]['10'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['10'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['10']-$dJMS[$karid]['gapok']['10']-$dJMS[$karid]['10']+$premitetap[$karid]['10'])."</td>         
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['10'])."</td>";
    
    $gajih              =$dzArr[$karid]['11'];
    $totrapel           =$rapel[$karid]['11'];
    $totbonus           =$bonus[$karid]['11'];
    $totthr             =$thr[$karid]['11'];
    $premiasuransi      =$dJMS[$karid]['11'];
    $gajibln            =$dJMS[$karid]['gapok']['11'];
    $premittp           =$premitetap[$karid]['11'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['11'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['11']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['11'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['11']+$premitetap[$karid]['11'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['11'])."</td>
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['11']-$dJMS[$karid]['gapok']['11']-$dJMS[$karid]['11']+$premitetap[$karid]['11'])."</td>         
    <td align=right style='color:#0000FF;' style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['11'])."</td>";
    
    $gajih              =$dzArr[$karid]['12'];
    $totrapel           =$rapel[$karid]['12'];
    $totbonus           =$bonus[$karid]['12'];
    $totthr             =$thr[$karid]['12'];
    $premiasuransi      =$dJMS[$karid]['12'];
    $gajibln            =$dJMS[$karid]['gapok']['12'];
    $premittp           =$premitetap[$karid]['12'];    
    $lemburpremi        =$gajih-$gajibln-$premiasuransi;   
    $gajihsthn          =(($gajih)*12)+$totrapel+$totbonus+$totthr;    
    $byjbt              =$dzArr[$karid]['byjab']['12'];
    $tambahan           =$totrapel+$totbonus+$totthr;
    $pensiun            =(($dJMS[$karid]['gapok']['12']*12)+$tambahan)*0.02;
    $penghasilanjbt     =$gajihsthn-$byjbt-$pensiun;     
    $jml_ptkp           =$ptkp[str_replace("K","",$kamusKar[$karid]['status'])];
    $pkp                =$gajihsthn-$pensiun-$byjbt-$jml_ptkp;
    $pph21bln           =$dzArr[$karid]['pph21']['12'];
    $pajakterhutang     =$pph21bln*12;
    $nama               =$kamusKar[$karid]['nama'];
    $statuspajak        =$kamusKar[$karid]['status'];
    $jabatan            =$optNmjabatan[$kamusKar[$karid]['jabatan']];
    $npwp               =$kamusKar[$karid]['npwp'];
    $gajipokok          =$gapok[$karid];
    $tipekaryawan       =$kamusTipe[$kamusKar[$karid]['tipe']];  
        
    $arr=$id."##".$gajihsthn."##".$byjbt."##".$penghasilanjbt."##".$pensiun."##".$jml_ptkp."##".$pkp."##".$pph21bln."##".$premiasuransi."##".$pajakterhutang."##".$nama."##".$statuspajak."##".$jabatan."##".$npwp."##".$gajipokok."##".$lemburpremi."##".$gajih."##".$tipekaryawan."##".$totrapel."##".$totbonus."##".$totthr."##".$lemburpremi;
    
    $stream.="<td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['12']+$premitetap[$karid]['12'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dJMS[$karid]['gapok']['12'])."</td>
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['12']-$dJMS[$karid]['gapok']['12']-$dJMS[$karid]['12']+$premitetap[$karid]['12'])."</td>         
    <td align=right style='cursor:pointer;' title='Click' onclick=\"detaildata(event,'sdm_slave_2pajak.php','".$arr."');\">".number_format($dzArr[$karid]['pph21']['12'])."</td>";
    
    $stream.="<td align=right style='color:#0000FF;'>".number_format($dzArr[$karid]['total'])."</td>
    <td align=right style='color:#0000FF;'>".number_format($dJMS[$karid]['gptahunan'],0)."</td>
    <td align=right style='color:#0000FF;'>".number_format(($dzArr[$karid]['total']-$dJMS[$karid]['gptahunan']),0)."</td>";
            
           
    //pph 21 tahunan (setelah setahun)============================================================
        $dzArr[$karid]['tpenghasilan']=($dzArr[$karid]['total']+($dJMS[$karid]['gptahunan']*$plusJMS/100));
    //periksa By jab dan kurangkan
    $dzArr[$karid]['tbyjab']=$jabPersen*$dzArr[$karid]['tpenghasilan'];
    if($dzArr[$karid]['tbyjab']>$jabMax){//jika lebih dari max maka dibatasi sebesar max
        $dzArr[$karid]['tbyjab']=$jabMax;
    }  
    
    //penghasilan setelah kurang By Jabatan
    $dzArr[$karid]['tpenghasilan']=$dzArr[$karid]['tpenghasilan']-$dzArr[$karid]['tbyjab'];
    // kurangi 2% gapok
	setIt($pot2[$karid],0);
    $dzArr[$karid]['tpenghasilan']-=$pot2[$karid];
   // tambahkan rapel bonus thr dll    
//    $$dzArr[$karid]['tpenghasilan']+=$rapel[$karid]+$bonus[$karid]+$thr[$karid];
    
    //kurangi dengan PTKP sehingga menghasilkan pkp:
    $dzArr[$karid]['tpkp']=$dzArr[$karid]['tpenghasilan']-$ptkp[str_replace("K","",$kamusKar[$karid]['status'])]; 
    $zz=0;
    $sisazz=0;
    if($dzArr[$karid]['tpkp']>0){
    #tahap 1:
    if($dzArr[$karid]['tpkp']<$pphtarif[0])
    {
        $zz+=$pphpercent[0]*$dzArr[$karid]['tpkp'];
        $sisazz=0;
    }
    else if($dzArr[$karid]['tpkp']>=$pphtarif[0])
    {
        $zz+=$pphpercent[0]*$pphtarif[0];
        $sisazz=$dzArr[$karid]['tpkp']-$pphtarif[0];
            #level 2
            if($sisazz<($pphtarif[1]-$pphtarif[0]))
            {
                $zz+=$pphpercent[1]*$sisazz;
                $sisazz=0;        
            }    
            else if($sisazz>=($pphtarif[1]-$pphtarif[0]))
            {
                $zz+=$pphpercent[1]*($pphtarif[1]-$pphtarif[0]);
                $sisazz=$dzArr[$karid]['tpkp']-$pphtarif[1];        
                    #level 3   
                    if($sisazz<($pphtarif[2]-$pphtarif[1]))
                    {
                        $zz+=$pphpercent[2]*$sisazz;
                        $sisazz=0;        
                    }    
                    else if($sisazz>=($pphtarif[2]-$pphtarif[1]))
                    {
                        $zz+=$pphpercent[2]*($pphtarif[2]-$pphtarif[1]);
                        $sisazz=$dzArr[$karid]['tpkp']-$pphtarif[2];  
                            if($sisazz>0){
                            #level 4  sisanya kali 30% 
                                $zz+=$pphpercent[3]*$sisazz; 
                            }
                    } 
            }          
    }
    }
    //masukkan ke array utama
    $dzArr[$karid]['tpph21']=$zz;
        //jika tidak memiliki NPWP maka tambahkan 20% dari PPh yang ada
//    if($kamusKar[$bar->karyawanid]['npwp']=='')
//    {
//          $dzArr[$karid]['tpph21']=  $dzArr[$karid]['tpph21']+ ( $dzArr[$karid]['tpph21']*20/100);
//    }
    //================================end pph tahunan===================================================================
    
    
    $stream.="<td align=right  style='color:#0000FF;'>".number_format($dzArr[$karid]['tpph21'])."</td>
    </tr>";
	setIt($total['pph01'],0);
	setIt($total['pph02'],0);
	setIt($total['pph03'],0);
	setIt($total['pph04'],0);
	setIt($total['pph05'],0);
	setIt($total['pph06'],0);
	setIt($total['pph07'],0);
	setIt($total['pph08'],0);
	setIt($total['pph09'],0);
	setIt($total['pph10'],0);
	setIt($total['pph11'],0);
	setIt($total['pph12'],0);
	$total['pph01']+=$dzArr[$karid]['pph21']['01'];
    $total['pph02']+=$dzArr[$karid]['pph21']['02'];
    $total['pph03']+=$dzArr[$karid]['pph21']['03'];
    $total['pph04']+=$dzArr[$karid]['pph21']['04'];
    $total['pph05']+=$dzArr[$karid]['pph21']['05'];
    $total['pph06']+=$dzArr[$karid]['pph21']['06'];
    $total['pph07']+=$dzArr[$karid]['pph21']['07'];
    $total['pph08']+=$dzArr[$karid]['pph21']['08'];
    $total['pph09']+=$dzArr[$karid]['pph21']['09'];
    $total['pph10']+=$dzArr[$karid]['pph21']['10'];
    $total['pph11']+=$dzArr[$karid]['pph21']['11'];
    $total['pph12']+=$dzArr[$karid]['pph21']['12'];
    
	setIt($total['01'],0);
	setIt($total['02'],0);
	setIt($total['03'],0);
	setIt($total['04'],0);
	setIt($total['05'],0);
	setIt($total['06'],0);
	setIt($total['07'],0);
	setIt($total['08'],0);
	setIt($total['09'],0);
	setIt($total['10'],0);
	setIt($total['11'],0);
	setIt($total['12'],0);
	setIt($total['pph'],0);
	setIt($total['total'],0);
	$total['01']+=$dzArr[$karid]['01'];
    $total['02']+=$dzArr[$karid]['02'];
    $total['03']+=$dzArr[$karid]['03'];
    $total['04']+=$dzArr[$karid]['04'];
    $total['05']+=$dzArr[$karid]['05'];
    $total['06']+=$dzArr[$karid]['06'];
    $total['07']+=$dzArr[$karid]['07'];
    $total['08']+=$dzArr[$karid]['08'];
    $total['09']+=$dzArr[$karid]['09'];
    $total['10']+=$dzArr[$karid]['10'];
    $total['11']+=$dzArr[$karid]['11'];
    $total['12']+=$dzArr[$karid]['12'];
    $total['total']+=$dzArr[$karid]['total'];
    $total['pph']+=$dzArr[$karid]['tpph21'];
    
	setIt($tgapok['01'],0);
	setIt($tgapok['02'],0);
	setIt($tgapok['03'],0);
	setIt($tgapok['04'],0);
	setIt($tgapok['05'],0);
	setIt($tgapok['06'],0);
	setIt($tgapok['07'],0);
	setIt($tgapok['08'],0);
	setIt($tgapok['09'],0);
	setIt($tgapok['10'],0);
	setIt($tgapok['11'],0);
	setIt($tgapok['12'],0);
	setIt($tgapok['total'],0);
	$tgapok['01']+=$dJMS[$karid]['gapok']['01'];
    $tgapok['02']+=$dJMS[$karid]['gapok']['02'];
    $tgapok['03']+=$dJMS[$karid]['gapok']['03'];
    $tgapok['04']+=$dJMS[$karid]['gapok']['04'];
    $tgapok['05']+=$dJMS[$karid]['gapok']['05'];
    $tgapok['06']+=$dJMS[$karid]['gapok']['06'];
    $tgapok['07']+=$dJMS[$karid]['gapok']['07'];
    $tgapok['08']+=$dJMS[$karid]['gapok']['08'];
    $tgapok['09']+=$dJMS[$karid]['gapok']['09'];
    $tgapok['10']+=$dJMS[$karid]['gapok']['10'];
    $tgapok['11']+=$dJMS[$karid]['gapok']['11'];
    $tgapok['12']+=$dJMS[$karid]['gapok']['12']; 
    $tgapok['total']+=$dJMS[$karid]['gptahunan'];
	
	setIt($ttj['01'],0);
	setIt($ttj['02'],0);
	setIt($ttj['03'],0);
	setIt($ttj['04'],0);
	setIt($ttj['05'],0);
	setIt($ttj['06'],0);
	setIt($ttj['07'],0);
	setIt($ttj['08'],0);
	setIt($ttj['09'],0);
	setIt($ttj['10'],0);
	setIt($ttj['11'],0);
	setIt($ttj['12'],0);
	setIt($ttj['total'],0);
    $ttj['01']+=($dzArr[$karid]['01']-$dJMS[$karid]['gapok']['01']);
    $ttj['02']+=($dzArr[$karid]['02']-$dJMS[$karid]['gapok']['02']);
    $ttj['03']+=($dzArr[$karid]['03']-$dJMS[$karid]['gapok']['03']);
    $ttj['04']+=($dzArr[$karid]['04']-$dJMS[$karid]['gapok']['04']);
    $ttj['05']+=($dzArr[$karid]['05']-$dJMS[$karid]['gapok']['05']);
    $ttj['06']+=($dzArr[$karid]['06']-$dJMS[$karid]['gapok']['06']);
    $ttj['07']+=($dzArr[$karid]['07']-$dJMS[$karid]['gapok']['07']);
    $ttj['08']+=($dzArr[$karid]['08']-$dJMS[$karid]['gapok']['08']);
    $ttj['09']+=($dzArr[$karid]['09']-$dJMS[$karid]['gapok']['09']);
    $ttj['10']+=($dzArr[$karid]['10']-$dJMS[$karid]['gapok']['10']);
    $ttj['11']+=($dzArr[$karid]['11']-$dJMS[$karid]['gapok']['11']);
    $ttj['12']+=($dzArr[$karid]['12']-$dJMS[$karid]['gapok']['12']); 
    $ttj['total']+=($dzArr[$karid]['total']-$dJMS[$karid]['gptahunan']);    
}

// total
$stream.="<tr class=title>
<td colspan=8 align=center>Total</td>
<td align=right>".number_format($total['01'])."</td>
<td align=right>".number_format($tgapok['01'])."</td>    
<td align=right>".number_format($ttj['01'])."</td>     
<td align=right>".number_format($total['pph01'])."</td>    
<td align=right>".number_format($total['02'])."</td>   
<td align=right>".number_format($tgapok['02'])."</td>    
<td align=right>".number_format($ttj['02'])."</td>       
<td align=right>".number_format($total['pph02'])."</td>     
<td align=right>".number_format($total['03'])."</td>
<td align=right>".number_format($tgapok['03'])."</td>    
<td align=right>".number_format($ttj['03'])."</td>       
<td align=right>".number_format($total['pph03'])."</td>     
<td align=right>".number_format($total['04'])."</td>
<td align=right>".number_format($tgapok['04'])."</td>    
<td align=right>".number_format($ttj['04'])."</td>       
<td align=right>".number_format($total['pph04'])."</td>     
<td align=right>".number_format($total['05'])."</td>
<td align=right>".number_format($tgapok['05'])."</td>    
<td align=right>".number_format($ttj['05'])."</td>       
<td align=right>".number_format($total['pph05'])."</td>     
<td align=right>".number_format($total['06'])."</td>
<td align=right>".number_format($tgapok['06'])."</td>    
<td align=right>".number_format($ttj['06'])."</td>       
<td align=right>".number_format($total['pph06'])."</td>     
<td align=right>".number_format($total['07'])."</td>
<td align=right>".number_format($tgapok['07'])."</td>    
<td align=right>".number_format($ttj['07'])."</td>       
<td align=right>".number_format($total['pph07'])."</td>     
<td align=right>".number_format($total['08'])."</td>
<td align=right>".number_format($tgapok['08'])."</td>    
<td align=right>".number_format($ttj['08'])."</td>       
<td align=right>".number_format($total['pph08'])."</td>     
<td align=right>".number_format($total['09'])."</td>
<td align=right>".number_format($tgapok['09'])."</td>    
<td align=right>".number_format($ttj['09'])."</td>       
<td align=right>".number_format($total['pph09'])."</td>     
<td align=right>".number_format($total['10'])."</td>
<td align=right>".number_format($tgapok['10'])."</td>    
<td align=right>".number_format($ttj['10'])."</td>       
<td align=right>".number_format($total['pph10'])."</td>     
<td align=right>".number_format($total['11'])."</td>
<td align=right>".number_format($tgapok['11'])."</td>    
<td align=right>".number_format($ttj['11'])."</td>       
<td align=right>".number_format($total['pph11'])."</td>     
<td align=right>".number_format($total['12'])."</td>
<td align=right>".number_format($tgapok['12'])."</td>    
<td align=right>".number_format($ttj['12'])."</td>       
<td align=right>".number_format($total['pph12'])."</td>     
<td align=right>".number_format($total['total'])."</td>
<td align=right>".number_format($tgapok['total'])."</td>    
<td align=right>".number_format($ttj['total'])."</td>        
<td align=right>".number_format($total['pph'])."</td>
</tr>";
$stream.="</tbody></table>";

if($proses=='preview'){
    echo $stream;    
}
if($proses=='excel'){
    
    $stream.="</table><br>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
    $dte=date("YmdHms");
    $nop_="pph21_".$kodeorg."_".$tahun."_".$dte;
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";            
}

    
?>