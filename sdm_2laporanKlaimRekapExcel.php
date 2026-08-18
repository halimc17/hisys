<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
 
$periode=$_GET['periode'];
$optJabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

//ambil data keluarga
$stry="select nomor,nama,hubungankeluarga,tanggungan from ".$dbname.".sdm_karyawankeluarga";
$res=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $nama[$bar->nomor]=$bar->nama;
    $hubungan[$bar->nomor]=$bar->hubungankeluarga;
    $tanggungan[$bar->nomor]=$bar->tanggungan;
}


	
$stream="Laporan Rekap Pengobatan Periode ".$periode."
    <table border=1>
    <thead>
    <tr>
        <td bgcolor=#dedede rowspan=2 align=center>No</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['lokasitugas']."</td>            
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['jabatan']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pasien']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['rumahsakit']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>Biaya Rumah Sakit</td>
        <td bgcolor=#dedede rowspan=2 align=center>Biaya Pendaftaran</td>  
        <td bgcolor=#dedede rowspan=2 align=center>Biaya Lab.</td>  
        <td bgcolor=#dedede rowspan=2 align=center>Biaya Obat</td>  
        <td bgcolor=#dedede rowspan=2 align=center>Jasa Dokter</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['nilaiklaim']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['perusahaan']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['karyawan']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['jamsostek']."</td>            
        <td bgcolor=#dedede colspan=3 align=center>".$_SESSION['lang']['dibayar']."</td>  
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['total']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['diagnosa']."</td>
        <td bgcolor=#dedede rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td>
    </tr>
    <tr>
            <td bgcolor=#dedede align=center>".$_SESSION['lang']['internal']."</td>
            <td bgcolor=#dedede align=center>Providers</td>
            <td bgcolor=#dedede align=center>".$_SESSION['lang']['klaim']."</td>
    </tr>
    </thead>
    <tbody>";  

if($_SESSION['empl']['lokasitugas']=='MJHO'){
    $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag,c.lokasitugas as lokasitugas,c.kodejabatan as kodejabatan,
          a.kodebiaya as kodebiaya,a.jasars as byrs,a.jasadr as bydr, a.jasalab as bylab,a.byobat as byobat,
          a.bypendaftaran as byadmin
          from ".$dbname.".sdm_pengobatanht a left join
          ".$dbname.".sdm_5rs b on a.rs=b.id 
          left join ".$dbname.".datakaryawan c
          on a.karyawanid=c.karyawanid
          left join ".$dbname.".sdm_5diagnosa d
          on a.diagnosa=d.id
          where a.periode='".$periode."'
          and (c.tipekaryawan in ('0','7','8') or c.alokasi=1) 
          order by a.updatetime desc, a.tanggal desc";
}
else{
    $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag,c.lokasitugas as lokasitugas,c.kodejabatan as kodejabatan,
          a.kodebiaya as kodebiaya,a.jasars as byrs,a.jasadr as bydr, a.jasalab as bylab,a.byobat as byobat,
          a.bypendaftaran as byadmin
          from ".$dbname.".sdm_pengobatanht a left join
          ".$dbname.".sdm_5rs b on a.rs=b.id 
          left join ".$dbname.".datakaryawan c
          on a.karyawanid=c.karyawanid
          left join ".$dbname.".sdm_5diagnosa d
          on a.diagnosa=d.id
          where a.periode='".$periode."' 
          and a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
          order by a.updatetime desc, a.tanggal desc";
}
$res=$owlPDO->query($stry) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$res->fetch()){
    $no+=1;
    $pasien='';
    if($bar->ygsakit!='0'){
        $pasien=$nama[$bar->ygsakit];
    }else{
         $pasien=$bar->namakaryawan;
    }
    if($bar->klaimoleh==0){
        $claim=$bar->jlhbayar;
        $tclaim+=$claim;
    }    
    if($bar->klaimoleh==1){
        $prov=$bar->jlhbayar;
        $tprov+=$prov;
    }
    if($bar->klaimoleh==2){
        $int=$bar->jlhbayar;
        $tint+=$int;
    }
   
    $stream.="<tr>
        <td>".$no."</td>
        <td>".$bar->notransaksi."</td>
        <td>".$bar->namakaryawan."</td>
        <td>".$bar->lokasitugas."</td>
        <td>".$bar->tanggal."</td>
        <td>".$optJabatan[$bar->kodejabatan]."</td>
        <td>".$pasien."[".$hubungan[$bar->ygsakit]."]</td>
        <td>".$bar->namars."</td>
        <td>".$bar->kodebiaya."</td>
        <td align=right>".number_format($bar->byrs,2,'.',',')."</td>
        <td align=right>".number_format($bar->byadmin,2,'.',',')."</td>
        <td align=right>".number_format($bar->bylab,2,'.',',')."</td>
        <td align=right>".number_format($bar->byobat,2,'.',',')."</td>
        <td align=right>".number_format($bar->bydr,2,'.',',')."</td>    
        <td align=right>".number_format($bar->totalklaim,0)."</td>
        <td align=right>".number_format($bar->bebanperusahaan,0)."</td>    
        <td align=right>".number_format($bar->bebankaryawan,0)."</td>
        <td align=right>".number_format($bar->bebanjamsostek,0)."</td>
        <td align=right>".number_format($int,0)."</td>    
        <td align=right>".number_format($prov,0)."</td>   
        <td align=right>".number_format($claim,0)."</td>
        <td align=right>".number_format($bar->jlhbayar,0)."</td>
       
        <td>".$bar->ketdiag."</td>
       <td>".$bar->keterangan."</td>
    </tr>";	  
    $tklaim+=$bar->totalklaim;
    $tbbnperusahaan+=$bar->bebanperusahaan;
    $tbbnkaryawan+=$bar->bebankaryawan;
    $tbbnjamsostek+=$bar->bebanjamsostek;
    $tbayar+=$bar->jlhbayar;
}
    $stream.="<tr>
        <td colspan=9 align=center>TOTAL</td>
        <td align=right>".number_format($tklaim,0)."</td>
        <td align=right>".number_format($tbbnperusahaan,0)."</td>
        <td align=right>".number_format($tbbnkaryawan,0)."</td>
        <td align=right>".number_format($tbbnjamsostek,0)."</td>
        <td align=right>".number_format($tint,0)."</td>    
        <td align=right>".number_format($tprov,0)."</td>   
        <td align=right>".number_format($tclaim,0)."</td>
        <td align=right>".number_format($tbayar,0)."</td>    
        
       <td></td>
    </tr>";	  
    
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";	 
//write exel   
$nop_="LaporanRekapPengobatan-".$periode;
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
            parent.window.alert('Cant convert to excel format');
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
?>
