<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag, e.lokasitugas as loktug,nama from ".$dbname.".sdm_pengobatanht a left join
      ".$dbname.".sdm_5rs b on a.rs=b.id 
	  left join ".$dbname.".datakaryawan c
	  on a.karyawanid=c.karyawanid
	  left join ".$dbname.".sdm_5diagnosa d
	  on a.diagnosa=d.id
          left join ".$dbname.".datakaryawan e
	  on a.karyawanid=e.karyawanid
        left join ".$dbname.".sdm_karyawankeluarga f
        on a.ygsakit=f.nomor
	  where a.periode like '".$_POST['tahun']."%'
	  and a.karyawanid = ".$_POST['karyawanid']."
          order by a.updatetime desc, a.tanggal desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$tab="<div style='width:1800px;font-weight:normal;'><table class=sortable cellspacing=1 border=0 width=100%>
    <thead>
    <tr class=rowheader>
        <td align=center>No</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=center>".$_SESSION['lang']['jenis']."</td>            
        <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td align=center>".$_SESSION['lang']['pasien']."</td>
        <td align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pasien']."</td>
        <td align=center>".$_SESSION['lang']['rumahsakit']."</td>
        <td align=center>".$_SESSION['lang']['jumlah']." Biaya</td>
        <td align=center>".$_SESSION['lang']['jumlah']." Bayar ke Kary</td>
        <td align=center>".$_SESSION['lang']['diagnosa']."</td>
        <td align=center>Obat / Drugs</td>           
    </tr>
    </thead>
    
    <tbody id='container'>";
$no=0;    
    while($bar=$res->fetch())
    { 
        $no+=1;
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
        #ambil obat-obatan
        $str2="select namaobat,jenis from ".$dbname.".sdm_pengobatandt where notransaksi='".$bar->notransaksi."'";
        $resxx=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
		$resxx->setFetchMode(PDO::FETCH_OBJ);
		$obat="";
        while($barxx=$resxx->fetch())
        {
            $obat.= $barxx->namaobat." [".$barxx->jenis."]";
        }
        
	if($pasien=='')$pasien='AsIs';				  
        $tab.="<tr class=rowcontent>
            <td align=center>".$no."</td>
            <td>".tanggalnormal($bar->tanggal)."</td>
            <td>".$bar->kodebiaya."</td>
            <td>".$bar->namakaryawan."</td>
            <td>".$pasien."</td>
            <td>".$bar->nama."</td>
            <td>".$bar->namars."[".$bar->kota."]"."</td>
            <td align=right>".number_format($bar->totalklaim,2,'.',',')."</td>
            <td align=right>".number_format($bar->jlhbayar,2,'.',',')."</td>
            <td>".$bar->ketdiag."</td>
             <td>".$obat."</td>
        </tr>";	 
		$total+=$bar->totalklaim;
		$totalbyr+=$bar->jlhbayar;
    }
$tab.="<tr class=rowcontent>
            <td colspan=7 align=center><b>TOTAL</b></td>
            <td align=right><b>".number_format($total,2)."</b></td>
            <td align=right><b>".number_format($totalbyr,2)."</b></td>
            <td></td>
            <td></td>
		</tr>";
	
$tab.="</tbody>
    <tfoot>
    </tfoot>
    </table></div>";
echo $tab;	
?>
