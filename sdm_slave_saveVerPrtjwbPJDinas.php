<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi=checkPostGet('notransaksi','');
$tanggal=tanggalsystem(checkPostGet('tanggal',''));
$jenisby=checkPostGet('jenisby','');
$jumlahhrd=checkPostGet('jumlahhrd',''); 
$method=checkPostGet('method','');
$jumlah=checkPostGet('jumlah',''); 
$detail=checkPostGet('detail',''); 

if($jumlahhrd=='')
  $jumlahhrd=0;


if($method=='update')
{
	$str="update ".$dbname.".sdm_pjdinasdt
	       set jumlahhrd=".$jumlahhrd."
	      where jenisbiaya=".$jenisby." and notransaksi='".$notransaksi."'
		  and detail='".$detail."'"; 
		  /*
		  $str="update ".$dbname.".sdm_pjdinasdt
	       set jumlahhrd=".$jumlahhrd."
	      where jenisbiaya=".$jenisby." and notransaksi='".$notransaksi."'
		  and tanggal=".$tanggal." and jumlah='".$jumlah."'"; 
		  */
	//echo "Error:".$str;	  
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
}
if($method=='finish')
{

    ##cek apakah ada pengambilan uang muka sebelumnya
    $strkas="select * from ".$dbname.".keu_kasbankdt where nodok='".$notransaksi."'";
	$reskas=fetchData($strkas);

    ##get data ht
    $str="select uangmuka,karyawanid,kodeorg from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
	$res=fetchData($str);
	$bar=$res[0];
	$uangmuka=$bar['uangmuka'];
	$kodeorg=$bar['kodeorg'];
	$karyawanidpd=$bar['karyawanid'];

    ##get data dt
    $str="select sum(jumlahhrd) as jumlahpjd from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber='1'";
	$res=fetchData($str);
	$bar=$res[0];
	$jumlahpjd=$bar['jumlahpjd'];

	$str="select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanidpd."'";
	$res=fetchData($str);
	$bar=$res[0];
	$tipekar=$bar['tipekaryawan'];

	##data create tagihan
	$strup="select karyawanid from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='PJDINAS' and level=3";
	$resup=fetchData($strup);
	$barup=$resup[0];
	$create=$barup['karyawanid'];

	##get induk
	$sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
	$ressup=$owlPDO->query($sqlkd);
	$ressup->setFetchMode(PDO::FETCH_ASSOC);
	$barsup=$ressup->fetch();
	$induk=$barsup['induk'];
        
	$kodejurnal='PJPD';
	$sisaum=$uangmuka-$jumlahpjd;
	$sisaumpjd=$sisaum;

	##jika tidak mengambil uang muka
	if (count($reskas)==0){
		$uangmuka=0;
		$sisaum=0;
		$sisaumpjd=$uangmuka-$jumlahpjd;
	}

	if ($tipekar==7) {
		if ($sisaum>0){
			$whr=" where jurnalid='PJPD' and kodeaplikasi='PJDDL'";
		}else{
			$whr=" where jurnalid='PJPD' and kodeaplikasi='PJDDK'";
		}
	}else{
		if ($sisaum>0){
			$whr=" where jurnalid='PJPD' and kodeaplikasi='PJDSL'";
		}else{
			$whr=" where jurnalid='PJPD' and kodeaplikasi='PJDSK'";
		}
	}
	

	##Parameter jurnal noakun debet dan kredit
	$str="select noakundebet,noakunkredit,sampaidebet,sampaikredit from ".$dbname.".keu_5parameterjurnal ".$whr;
	$res=fetchData($str);
	$bar=$res[0];
	$noakundebet=$bar['noakundebet'];
	$sampaidebet=$bar['sampaidebet'];
	$noakunkredit=$bar['noakunkredit'];
	$sampaikredit=$bar['sampaikredit'];

	if ($sisaum>0){

		$noinvoice=$notransaksi;
	    $tipeinvoice='pjd';
	    $keterangan="Pertanggungjawaban Perjalanan Dinas berdasarkan notransaksi: ".$notransaksi."";

	    $insht="insert into ".$dbname.".keu_penagihanht(noinvoice, tipeinvoice, jenis,nokontrak,kodecustomer,tanggal,nilaiinvoice, keterangan, matauang, kurs, posting, jurnalstatus, kodept, kodeorg, debet, kredit,periode,nobuktipotong,jenispph,jenispenghasilan,carabayar,npwp,berikat) values 
	    		('".$noinvoice."','0','".$tipeinvoice."','".$noinvoice."','".$karyawanidpd."','".date('Y-m-d')."','".$sisaumpjd."','".$keterangan."','IDR','1','1','1','".$induk."','".$kodeorg."','','','','','','','','','')";
	    try {
	        $owlPDO->exec($insht);

	    } catch (PDOException $e) {
	        print " Gagal: " . $e->getMessage() . "\n";
	        die();
	    }

	    $kodejurnal="PJPD";  
	    $tgljurnal=date('Ymd');

	    ##Get Journal Counter
	    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
	    $tmpKonter = fetchData($queryJ);
	    $konter = addZero($tmpKonter[0]['nokounter']+1,3);
	    ##Prep No Jurnal
	    $notrans=$tgljurnal."/".$kodeorg."/".$kodejurnal."/".$konter;

	}else{

		##get noaruskas noakundebet
	    $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakundebet."'";
	    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
	    $qtr->setFetchMode(PDO::FETCH_ASSOC);
	    $rtr=$qtr->fetch();
	    $noaruskasdebet=$rtr['noaruskas'];

	    ##get keterangan noakundebet
	    $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdebet."'";
	    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
	    $qtr->setFetchMode(PDO::FETCH_ASSOC);
	    $rtr=$qtr->fetch();
	    $keterangantempdebet=$rtr['id_ket'];

		##get noaruskas noakunkredit
	    $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakunkredit."'";
	    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
	    $qtr->setFetchMode(PDO::FETCH_ASSOC);
	    $rtr=$qtr->fetch();
	    $noaruskaskredit=$rtr['noaruskas'];

	    ##get keterangan noakunkredit
	    $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskaskredit."'";
	    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
	    $qtr->setFetchMode(PDO::FETCH_ASSOC);
	    $rtr=$qtr->fetch();
	    $keterangantempkredit=$rtr['id_ket'];

		##get noaruskas sampaikredit
	    $str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$sampaikredit."'";
	    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
	    $qtr->setFetchMode(PDO::FETCH_ASSOC);
	    $rtr=$qtr->fetch();
	    $noaruskassamkredit=$rtr['noaruskas'];

	    ##get keterangan sampaikredit
	    $str1="select id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskassamkredit."'";
	    $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
	    $qtr->setFetchMode(PDO::FETCH_ASSOC);
	    $rtr=$qtr->fetch();
	    $keterangantempsamkredit=$rtr['id_ket'];

		$noinvoice=date('Ymdhis');
	    $tipeinvoice='pjd';
	    $jumlahht=$sisaumpjd*(-1);
	    $keterangan="Pertanggungjawaban Perjalanan Dinas berdasarkan notransaksi: ".$notransaksi."";

	    $insht="insert into ".$dbname.".keu_tagihanht(noinvoice, tipeinvoice, tanggal, nopo, kodesupplier, nilaiinvoice, keterangan, keterangan2, noakun, matauang, kurs, posting, kodeorg, unit, updateby, postingby) values 
	    		('".$noinvoice."','".$tipeinvoice."','".date('Y-m-d')."','".$notransaksi."','".$karyawanidpd."','".$jumlahht."','','".$keterangan."','".$sampaikredit."','IDR','1','1','".$induk."','".$kodeorg."','".$create."','".$create."')";
	    try {
	        $owlPDO->exec($insht);

	        $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset,noaruskas,keterangan) values 
	          ('".$noinvoice."','".$noakundebet."','".$jumlahpjd."','','','".$noaruskasdebet."','".$keterangantempdebet."')";
	        try{
	            $owlPDO->exec($ins);
	        } catch (PDOException $e) {
	            print " Gagal: " . $e->getMessage() . "\n";
	            die();
	        }

	        if ($uangmuka!=0) {
		        $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset,noaruskas,keterangan) values 
		          ('".$noinvoice."','".$noakunkredit."','".-($uangmuka)."','','','".$noaruskaskredit."','".$keterangantempkredit."')";
		        try{
		            $owlPDO->exec($ins);
		        } catch (PDOException $e) {
		            print " Gagal: " . $e->getMessage() . "\n";
		            die();
		        }

		        $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset,noaruskas,keterangan) values 
		          ('".$noinvoice."','".$sampaikredit."','".$sisaumpjd."','','','".$noaruskassamkredit."','".$keterangantempsamkredit."')";
		        try{
		            $owlPDO->exec($ins);
		        } catch (PDOException $e) {
		            print " Gagal: " . $e->getMessage() . "\n";
		            die();
		        }
	        }
	        
	    } catch (PDOException $e) {
	        print " Gagal: " . $e->getMessage() . "\n";
	        die();
	    }

	    $kodejurnal="TGH01";  
	    $tgljurnal=date('Ymd');

	    # Get Journal Counter
	    $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
	    $tmpKonter = fetchData($queryJ);
	    $konter = addZero($tmpKonter[0]['nokounter']+1,3);
	    # Prep No Jurnal
	    $notrans=$tgljurnal."/".$kodeorg."/".$kodejurnal."/".$konter;

	}

	if ($sisaum>0){
		$noakun1=$noakundebet;#akun biaya perjalanan dinas
		$noakun2=$sampaidebet;#akun piutang karyawan
		$noakun3=$noakunkredit;#akun uang muka perjalanan dinas
		$jumlah1=$jumlahpjd;#rupiah biaya
		$jumlah2=$sisaumpjd;#rupiah piutang
		$jumlah3=-($uangmuka);#rupiah uang muka
		$ket="Jurnal Otomatis Atas Pertanggungjawaban Perjalanan Dinas berdasarkan nota piutang:".$noinvoice." dan notransaksi:".$notransaksi;
	}else{
		$noakun1=$noakundebet;#akun biaya perjalanan dinas
		$noakun2=$noakunkredit;#akun uang muka perjalanan dinas
		$noakun3=$sampaikredit;#akun hutang lainnya / provisi
		$jumlah1=$jumlahpjd;#rupiah biaya
		$jumlah2=-($uangmuka);#rupiah uang muka
		$jumlah3=$sisaumpjd;#rupiah hutang lainnya / provisi
		$ket="Jurnal Otomatis Atas Pertanggungjawaban Perjalanan Dinas berdasarkan nota hutang:".$noinvoice." dan notransaksi:".$notransaksi.";";
	}

    ##insert jurnalht
	$strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
			values ('".$notrans."','".$kodejurnal."','0','0','".$tgljurnal."','".date('Ymd')."','1','".$noinvoice."','IDR','1')";
	try
    {
        $owlPDO->exec($strht);

        $nourut=1;
		##insert jurnaldt 1
        $str="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nik,noreferensi,nodok)
        values ('".$notrans."','".$tgljurnal."','".$nourut."','".$noakun1."','".$ket."','".$jumlah1."','IDR','1','".$kodeorg."','".$karyawanidpd."','".$noinvoice."','".$notransaksi."')";
        try
        {
            $owlPDO->exec($str);
            $nourut++;
        }
        catch (PDOException $e)
        {
            print " Gagal insert jurnal dt 1 : " . $e->getMessage() . "\n";
            die();
        }

        if ($uangmuka!=0) {
	        //insert jurnaldt 2
	        $str="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nik,noreferensi,nodok)
	        values ('".$notrans."','".$tgljurnal."','".$nourut."','".$noakun2."','".$ket."','".$jumlah2."','IDR','1','".$kodeorg."','".$karyawanidpd."','".$noinvoice."','".$notransaksi."')";
	        try
	        {
	            $owlPDO->exec($str);
	            $nourut++;
	        }
	        catch (PDOException $e)
	        {
	            print " Gagal insert jurnal dt 2 : " . $e->getMessage() . "\n";
	            die();
	        }
        }
        
        //insert jurnaldt 3
        $str="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nik,noreferensi,nodok)
        values ('".$notrans."','".$tgljurnal."','".$nourut."','".$noakun3."','".$ket."','".$jumlah3."','IDR','1','".$kodeorg."','".$karyawanidpd."','".$noinvoice."','".$notransaksi."')";
        try
        {
            $owlPDO->exec($str);
        }
        catch (PDOException $e)
        {
            print " Gagal insert jurnal dt 3 : " . $e->getMessage() . "\n";
            die();
        }

        $strht="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
        try{
            $owlPDO->exec($strht);
        }catch (PDOException $e){
            echo "Gagal update kelompok jurnal : ".$e->getMessage();
            die();
        }
        
    }catch (PDOException $e){
        print " Gagal insert jurnal ht : " . $e->getMessage() . "\n";
        die();
    }

	##update flag statuspertanggungjawaban sudah di verifikasi oleh hrd
	$str="update ".$dbname.".sdm_pjdinasht set statuspertanggungjawaban=1,tglpertanggungjawaban='".date('Y-m-d')."' where  notransaksi='".$notransaksi."'"; 
    try{$owlPDO->exec($str);}
    catch (PDOException $e) {
        print " Gagal  !: " . $e->getMessage() . "\n"; 
        die(); 
    }
}

$arrsumber=array('0'=>'Uang Muka','1'=>'Pertanggung Jawaban');

$str="select a.*,b.keterangan as jns,b.id as bid from ".$dbname.".sdm_pjdinasdt a
      left join ".$dbname.".sdm_5jenisbiayapjdinas b on a.jenisbiaya=b.id
	  where a.notransaksi='".$notransaksi."' and sumber=1";
$no=0;
$total=0;
$totalhrd=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

while($bar=$res->fetch()){
	
	##tanya pak listiyo jika ada sisa dari uang muka, 
	##uang muka yang disetujui masih bisa dirubah disisi hrd
	$hidden = "";
	$disabled = "";
	// $hidden=$titlebaris=$disabled='';
	// if($bar->sumber==0){
		// $hidden='hidden';
		// $disabled='disabled';
	// }
	
	$no+=1;
	echo"<tr class=rowcontent>
	     	<td>".$no."</td>
		   
                        <td>".tanggalnormal($bar->tanggal)." s/d ".tanggalnormal($bar->tanggalsampai)."</td>
						 <td>".$bar->jns."</td>
			<td>".$bar->detail."</td>
			<td>".$bar->keterangan."</td>
			<td align=right>".number_format($bar->jumlah,2)."</td>
			<td align=right>
			<img ".$hidden." src='images/puzz.png' style='cursor:pointer;' title='click to get value' onclick=\"document.getElementById('jumlahhrd".$bar->bid.$no."').value='".$bar->jumlah."'\">
			<input ".$disabled." type=text id='jumlahhrd".$bar->bid.$no."' class=myinputtextnumber size=15 onkeypress=\"return angka_doang(event);\" onblur=change_number(this) value='".number_format($bar->jumlahhrd,2,'.',',')."'>
			<img ".$hidden." src='images/save.png' title='Save' class=resicon onclick=\"saveApprvPJD('".$bar->bid."','".$bar->notransaksi."','".tanggalnormal($bar->tanggal)."','".$bar->jumlah."','".$no."','".$bar->detail."')\"></td>
			<td>".$arrsumber[$bar->sumber]."</td>
			</tr>";
	$total += $bar->jumlah;		
	$totalhrd += $bar->jumlahhrd;		
}
	echo"<tr class=rowcontent>
	     	<td colspan=5 align=center>TOTAL</td>
			<td align=right>".number_format($total,2)."</td>
			<td align=right>".number_format($totalhrd,2)."</td>
		    <td></td>
			</tr>";

?>