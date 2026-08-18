<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$notransaksi     =$_POST['notransaksi'];
$hariini = date("Y-m-d");
$tahunini = date("Y");

$namaBiaya = makeOption($dbname,'sdm_5jenisbiayapengobatan','kode,nama');

function getAge($tdate,$dob)
{
        $age = 0;
        while( $tdate > $dob = strtotime('+1 year', $dob))
        {
                ++$age;
        }
        return $age;
}

$str="select a.*, b.*,c.namakaryawan,c.kodegolongan,c.bagian,d.diagnosa as ketdiag,c.jeniskelamin as sex,c.tanggallahir as lahir
      from ".$dbname.".sdm_pengobatanht a 
      left join  ".$dbname.".log_5supplier b on a.rs=b.supplierid 
      left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
      left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id
      where a.notransaksi='".$notransaksi."'
      order by a.updatetime desc, a.tanggal desc";
$stream='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
  $no=0;
  while($bar=$res->fetch())
  {
           $usia=getAge(strtotime($tahunini),strtotime($bar->lahir))+1;
           $periode=substr($bar->periode,5,2)."-".substr($bar->periode,0,4);
           $tanggal=tanggalnormal($bar->tanggal);
           $tanggalkwitansi=tanggalnormal($bar->tanggalkwitansi);
           $tanggalpengajuan=tanggalnormal($bar->tanggalpengajuan);
           $karyawanid=$bar->karyawanid;
           $namakaryawan=$bar->namakaryawan;
           $doagnosa=$bar->ketdiag;
           $namars=$bar->namasupplier;
           $jenisbiaya=$bar->kodebiaya;
           $keterangan=$bar->keterangan;
           $totalbayar=$bar->jlhbayar;
           $totalklaim=$bar->totalklaim;
           $tahunplafon=$bar->tahunplafon;
           $bagian=$bar->bagian;
           $jlhhariistirahat=$bar->jlhhariistirahat;
           $tanggalbayar=tanggalnormal($bar->tanggalbayar);
           $golongan=$bar->kodegolongan;
           $kodebiaya=$bar->kodebiaya;
           $bebanperusahaan=$bar->bebanperusahaan;
           $bebankaryawan=$bar->bebankaryawan;
           $bebanjamsostek=$bar->bebanjamsostek;
           $jasars=$bar->jasars;
           $jasadr=$bar->jasadr;
           $jasalab=$bar->jasalab;
           $byobat=$bar->byobat;
           $bypendaftaran=$bar->bypendaftaran;
		   $jumlahkasbank=$bar->jumlahkasbank;
		   $tanggalkasbank=$bar->tanggalkasbank;

           if($bar->ygsakit==0)
           {
                $ygsakit['namaygsakit']=$namakaryawan;
                $ygsakit['jk']=$bar->sex;
                $ygsakit['hubungankeluarga']="AsIs";
                $ygsakit['umur']=$usia;
           }
           else
           {
               $str1=" select nama,jeniskelamin,hubungankeluarga, 
                           ROUND(DATEDIFF(NOW(),tanggallahir)/365,2) as umur
                           from ".$dbname.".sdm_karyawankeluarga  where nomor=".$bar->ygsakit;
                $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_OBJ);
                   while($bar1=$res1->fetch())
                   {
                         $ygsakit['namaygsakit']=$bar1->nama;
                         $ygsakit['jk']=$bar1->jeniskelamin;
                         $ygsakit['hubungankeluarga']=$bar1->hubungankeluarga;
                         $ygsakit['umur']=$bar1->umur;
                   }
           }
  }	

  
  // #ambil data kasbank
		// $strk="select a.jumlah,a.tanggal,a.notransaksi from ".$dbname.".keu_kasbankdt a left join ".$dbname.".keu_kasbankht b on
				// a.notransaksi=b.notransaksi where b.posting=0 and a.nodok='".$notransaksi."' ";
		// $resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
		// $resk->setFetchMode(PDO::FETCH_ASSOC);
		// $bark = $resk->fetch();
  
  echo"<fieldset><legend><b>".$_SESSION['lang']['karyawan']."</b></legend>
       <table class=sortable cellspacing=1 border=0>
           <thead></thead>
           <tbody>
       <tr class=rowcontent><td width=250px>".$_SESSION['lang']['notransaksi']."</td><td width=1>:</td><td width=200px>".$notransaksi."</td></tr>
	   <tr class=rowcontent><td>".$_SESSION['lang']['tanggalkwitansi']."</td><td>:</td><td>".$tanggalkwitansi."</td></tr>
	   <tr class=rowcontent><td>".$_SESSION['lang']['tanggalpengajuan']."</td><td>:</td><td>".$tanggalpengajuan."</td></tr>
	   <tr class=rowcontent><td>".$_SESSION['lang']['thnplafon']."</td><td>:</td><td>".$tahunplafon."</td></tr>
	   <tr class=rowcontent><td>".$_SESSION['lang']['periode']."</td><td>:</td><td>".$periode."</td></tr>
	   <tr class=rowcontent><td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td><td>".$namakaryawan."</td></tr>
	   <tr class=rowcontent><td>".$_SESSION['lang']['bagian']."</td><td>:</td><td>".$bagian."</td></tr>
	   <tr class=rowcontent><td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td>".$keterangan."</td></tr>
       </tbody>
           <tfoot>
           </tfoot>
           </table>
           </fieldset>
           <fieldset><legend><b>".$_SESSION['lang']['pasien']."</b></legend>
       <table class=sortable cellspacing=1 borde=0>
           <thead></thead>
           <tbody>	   
			<tr class=rowcontent><td width=250px>".$_SESSION['lang']['jenisbiayapengobatan']."</td><td width=1>:</td><td width=200px>".$namaBiaya[$jenisbiaya]."</td></tr>	   
			<tr class=rowcontent><td>".$_SESSION['lang']['namapasien']."</td><td>:</td><td>".$ygsakit['namaygsakit']."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['jeniskelamin']."</td><td>:</td><td>".$ygsakit['jk']."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['umur']."</td><td>:</td><td>".$ygsakit['umur']." ".$_SESSION['lang']['tahun']."</td></tr>	   
           <tr class=rowcontent><td>".$_SESSION['lang']['hubungan']."</td><td>:</td><td>".$ygsakit['hubungankeluarga']."</td></tr>
       </tbody>
           <tfoot>
           </tfoot>
       </table>
           </fieldset>

           <fieldset><legend><b>".$_SESSION['lang']['biaya']." ".$_SESSION['lang']['rumahsakit']." : ".$namars."</b></legend>
       <table class=sortable cellspacing=1 borde=0>	
           <thead></thead>
           <tbody>
       <tr class=rowcontent><td width=250px>".$_SESSION['lang']['biayaadministrasi']."</td><td width=1>:</td><td width=200px align=right>".number_format($bypendaftaran,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['jasars']."</td><td width=1>:</td><td align=right>".number_format($jasars,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['biayadr']."</td><td width=1>:</td><td align=right>".number_format($jasadr,2,'.',',')."</td></tr>	   
           <tr class=rowcontent><td>".$_SESSION['lang']['biayalab']."</td><td width=1>:</td><td align=right>".number_format($jasalab,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['biayaobat']."</td><td width=1>:</td><td align=right>".number_format($byobat,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['total']."</td><td width=1>:</td><td align=right>".number_format($totalklaim,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['perusahaan']."</td><td width=1>:</td><td align=right>".number_format($bebanperusahaan,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['karyawan']."</td><td width=1>:</td><td align=right>".number_format($bebankaryawan,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['jms']."</td><td width=1>:</td><td align=right>".number_format($bebanjamsostek,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>Verivikasi HRD</td><td width=1>:</td><td align=right>".number_format($totalbayar,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>Tanggal Verivikasi HRD</td><td width=1>:</td><td>".$tanggalbayar."</td></tr>
		
			<tr class=rowcontent><td>".$_SESSION['lang']['dibayar']."</td><td width=1>:</td><td align=right>".number_format($jumlahkasbank,2,'.',',')."</td></tr>
           <tr class=rowcontent><td>".$_SESSION['lang']['tanggal']."</td><td width=1>:</td><td>".tanggalnormal($tanggalkasbank)."</td></tr>
       
       	
	   </tbody>
           <tfoot>
           </tfoot>
       </table>
           </fieldset>
           ";	

        //ambil gaji pokok dan plafod
        $str="select jumlah from ".$dbname.".sdm_5gajipokok where idkomponen=1 and karyawanid=".$karyawanid." and tahun=".$tahunplafon." ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $gp=0;
        while($bar=$res->fetch())
        {
                $gp=$bar->jumlah;
        }

        $str="select a.kode,b.rupiah from ".$dbname.".sdm_5jenisbiayapengobatan a
              left join ".$dbname.".sdm_pengobatanplafond b on a.kode=b.kodejenisbiaya
              where b.kodegolongan='".$golongan."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
                $plaf[$bar->kode]=$bar->rupiah;
        }	       	   	  		

        echo "<fieldset><legend>".$_SESSION['lang']['plafon']."</legend>
              <table class=sortable cellspacing=1 borde=0 width=100%>	
                   <thead>
                   <tr clas=rowheader>
                        <td align=center>".$_SESSION['lang']['kodegolongan']."</td>
                        <td align=center>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
						<td align=center>".$_SESSION['lang']['plafon']."</td>";
                        if($jenisbiaya == 'RWINP'){
						echo"<td align=center>".$_SESSION['lang']['jumlahhariinap']."</td>
							 <td align=center>".$_SESSION['lang']['dibayar']."</td>";
                        }else{
						echo"<td align=center>".$_SESSION['lang']['sudahdipakai']."</td>
                        <td align=center>".$_SESSION['lang']['sisa']."(Plafond)</td>";
                        }
                echo"</tr>
                   </thead>
                   <tbody>";
         //ambil jumlah pengobatan sesuai tahunplafon
        $str="select sum(jlhbayar) as jlhbayar,kodebiaya,jlhhariistirahat from ".$dbname.".sdm_pengobatanht
              where karyawanid=".$karyawanid." and tahunplafon=".$tahunplafon." and kodebiaya='".$kodebiaya."'
              group by kodebiaya"; 
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);           
         while($bar=$res->fetch())
         {
                setIt($plaf[$bar->kodebiaya],0);
                  echo"<tr class=rowcontent>
                    <td align=center>".$golongan."</td>
					<td>".$namaBiaya[$bar->kodebiaya]."</td>
					<td align=right>".number_format($plaf[$bar->kodebiaya],2,',','.')."</td>";
					// <td align=right>".number_format($bar->jlhhariistirahat,2,',','.')."</td>";
					if($jenisbiaya == 'RWINP'){
					echo"<td align=right>".number_format($bar->jlhbayar)."</td>
						 <td align=right>".number_format($bar->jlhbayar,2)."</td>";
					}else{
					echo"<td align=right>".number_format($bar->jlhbayar,2,',','.')."</td>
					<td align=right>".number_format($plaf[$bar->kodebiaya]-$bar->jlhbayar,2,',','.')."</td>";
					}
			  echo"</tr>";
         }	     
        echo"</tbody>
                   <tfoot>
                   </tfoot>
               </table>
                  </fieldset>";	  
?>