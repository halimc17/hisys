<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
 
$periode=$_GET['periode'];
$kary=$_GET['kary'];
$optJabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$hariini = date("Y-m-d");
$tahunini = date("Y");

function getAge($tdate,$dob)
{
        $age = 0;
        while( $tdate > $dob = strtotime('+1 year', $dob))
        {
                ++$age;
        }
        return $age;
}   

if($periode=='')$periode=date('Y');

$str="select a.*, b.*,g.tipe as tipekaryawan,c.namakaryawan,d.diagnosa as ketdiag, c.lokasitugas as loktug,c.kodejabatan, nama,
        c.jeniskelamin as sex,c.tanggalmasuk as masuk, c.tanggalkeluar as keluar, c.tanggallahir as lahir, c.subbagian as subbag,
        a.jasars as byrs,a.jasadr as bydr,a.jasalab as bylab,a.byobat as byobat,a.bypendaftaran as byadmin,a.tanggalbayar as tglbayar
        from ".$dbname.".sdm_pengobatanht a 
        left join ".$dbname.".sdm_5rs b on a.rs=b.id 
        left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
        left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id
        left join ".$dbname.".sdm_karyawankeluarga f on a.ygsakit=f.nomor
        left join ".$dbname.".sdm_5tipekaryawan g on c.tipekaryawan=g.id
        where a.periode like '".$periode."%'
        and b.namars like '".$rs."%' and a.karyawanid like '".$kary."%' and c.lokasitugas like '".$kodeorg."%'
        order by a.updatetime desc, a.tanggal desc";
echo "error".$str;
//	  and a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
	
$stream="Laporan Klaim Pengobatan Periode ".$periode." ".$kary." 
    <table border=1>
    <thead>
    <tr>
        <td bgcolor=#dedede>No</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['periode']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['tanggal']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['lokasitugas']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['tipekaryawan']."</td>   
        <td bgcolor=#dedede>".$_SESSION['lang']['namakaryawan']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['pasien']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pasien']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['rumahsakit']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['nilaiklaim']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['dibayar']."</td>
        <td bgcolor=#dedede>".$_SESSION['lang']['tanggalbayar']."</td>
    </tr>
    </thead>
    <tbody>";  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar=$res->fetch())
{
    $no+=1;
    
    $masakerja=getAge(strtotime($hariini),strtotime($bar->masuk));
    $usia=getAge(strtotime($tahunini),strtotime($bar->lahir))+1;
        
    $pasien='';
    //get hubungan keluarga
    $stru="select hubungankeluarga from ".$dbname.".sdm_karyawankeluarga 
          where nomor=".$bar->ygsakit;
	$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
	$resu->setFetchMode(PDO::FETCH_OBJ);
    while($baru=$resu->fetch())
    {
        $pasien=$baru->hubungankeluarga;
    }
    if($pasien=='')$pasien='AsIs';	

    $stream.="<tr>
        <td>".$no."</td>
        <td>".$bar->notransaksi."</td>
        <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
        <td>".tanggalnormal($bar->tanggal)."</td>";
    if($bar->subbag=='')
        $stream.="<td>".$bar->loktug."</td>";
    else
        $stream.="<td>".$bar->subbag."</td>";
    $stream.="<td>".$bar->tipekaryawan."</td>    
              <td>".$bar->namakaryawan."</td>";
   
    $stream.="<td>".$pasien."</td>
        <td>".$bar->nama."</td>
        <td>".$bar->namars."[".$bar->kota."]"."</td>
        <td>".$bar->kodebiaya."</td>      
        <td align=right>".$bar->totalklaim."</td>
        <td align=right>".$bar->jlhbayar."</td>
        <td>".$bar->tglbayar."</td>
    </tr>";	  	
}
$s_tot="select sum(a.totalklaim) as totklaim,sum(a.jlhbayar) as totdibayar,b.kode as kodebiaya
                from ".$dbname.".sdm_pengobatanht a 
                left join ".$dbname.".sdm_5jenisbiayapengobatan b on a.kodebiaya=b.kode
                where a.periode like '".$periode."%' and a.karyawanid like '".$kary."%'
                group by a.kodebiaya";
		$r_tot=$owlPDO->query($s_tot) or die(print " Gagal: ".PDOException::getMessage());
		$r_tot->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$r_tot->fetch())
        {
           $stream.="<tr class=rowcontent>
            <td align=right colspan=11>".$bar->kodebiaya."</td>
            <td align=right>".number_format($bar->totklaim,2,'.',',')."</td>
            <td align=right>".number_format($bar->totdibayar,2,'.',',')."</td>
            <td></td></tr>";
           
           $grantot_klaim +=$bar->totklaim;
           $grantot_dibayar +=$bar->totdibayar;
        }
        
        $stream.="<tr class=rowcontent>
            <td align=right colspan=11>TOTAL KLAIM</td>
            <td align=right>".number_format($grantot_klaim,2,'.',',')."</td>
            <td align=right>".number_format($grantot_dibayar,2,'.',',')."</td>
            <td></td></tr>";
        
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";	 
//write exel   
$nop_="LaporanKlaimPengobatan-".$periode.$kodeorg.'_'.$method.'_';
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
