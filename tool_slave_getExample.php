<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$data=$_POST;
if(count($data)=='0'){
	$data=$_GET;
}

$param=$_GET['form'];

if($param=='ACCBAL'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=expampleaccbal.csv");
        echo "kodeorg,periode,noakun,saldo\n";
        echo "SOGE,201304,1110001,190000\n";
        echo "SOGE,201304,2110004,40000000\n";
        echo "SOGE,201304,1150001,2550500\n";
        echo "SOGE,201304,3110002,3000000\n";
        echo "SOGE,201304,1260001,10500\n";
        exit();
}
if($param=='JOURNAL'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=expamplejournalhistory.csv");
        echo "nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,noreferensi,kodevhc,kodeblok,revisi,kodesegment\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,1,0,Histori hutang spl,1000000,IDR,1,SOGE,,,,,,,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,2,2111101,Histori hutang spl,-300000,IDR,1,SOGE,,,,,,S001000001,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,3,2111101,Histori hutang spl,-200000,IDR,1,SOGE,,,,,,S001000079,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,4,2111101,Histori hutang spl,-250000,IDR,1,SOGE,,,,,,S001000602,,,,0,0000000001\n";
        echo "20130631/SOGE/HIS/001,2013-06-31,5,2111101,Histori hutang spl,-250000,IDR,1,SOGE,,,,,,S001000101,,,,0,0000000001\n";
        exit();        
}
if($param=='INV'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=saldomaterial.csv");
            echo "kodeorg,kodebarang,saldoakhirqty,hargarata,periode,kodegudang\n";
            echo "NFS,31200026,1,275000,2013-07,LGRM22\n";
            echo "NFS,32100001,6,1856500.667,2013-07,LGRM22\n";
            echo "NFS,32100003,7.5,170375.0667,2013-07,LGRM22\n";
            echo "NFS,32100005,2,37000,2013-07,LGRM22\n";
            echo "NFS,32100008,4,32500,2013-07,LGRM22\n";
            echo "NFS,32100009,5,53000,2013-07,LGRM22\n";
            echo "NFS,32100013,1,132500,2013-07,LGRM22\n";
            echo "NFS,32100014,3,65556,2013-07,LGRM22\n";
            echo "NFS,32100018,6,20500,2013-07,LGRM22\n";
            exit();        
}       
if($param=='PO'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=listpomanual.csv");
            echo "nopo,kodeorg,tanggal,kodesupplier,matauang,kurs,diskonpersen,nilaidiskon,ppn,subtotal,nilaipo,kodebarang,satuan,jumlahpesan,hargasatuan\n";
            echo "612/08/2013/PO/MA/NFS,NFS,2013-08-02,S001110341,IDR,1,0,0,50650,506500,557150,32102901,ROLL,1,270000\n";
            echo "612/08/2013/PO/MA/NFS,,,,,,,,,,,32102902,PCS,2,20000\n";
            echo "612/08/2013/PO/MA/NFS,,,,,,,,,,,32103182,PCS,2,5500\n";
            echo "612/08/2013/PO/MA/NFS,,,,,,,,,,,32201055,PCS,7,26500\n";
            echo "987/12/2012/PO/MA/NFS,NFS,2012-12-17,S001110070,IDR,1,0,0,0,25720000,25720000,37701061,BUKU,120,29000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701269,BUKU,120,10000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701270,BUKU,120,10000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701271,LEMBAR,500,2600\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701272,BUKU,1200,5000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701273,BUKU,240,11000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701274,BUKU,120,11000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701275,BUKU,120,14000\n";
            echo "987/12/2012/PO/MA/NFS,,,,,,,,,,,37701276,BUKU,300,23000\n";
            exit();        
}
if($param=='ABSENSI'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=absensi.csv");
            // echo "tahun,bulan,tanggal,nik,shift,absensi,jammasuk,jampulang,keterangan\n";
            // echo "2016,2,1,xxxx,1,H,07:00:00,15:00:00,masuk\n";
            // echo "2016,2,1,xxxx,1,H,07:00:00,15:00:00,masuk\n";
			echo "No. ID,Nama,Tanggal,TIME\n";
            echo "112050037,YOHAN,1/2/2019,8:30\n";
            echo "212050037,YOHAN,1/2/2019,17:50\n";
            echo "112050037,YOHAN,2/2/2019,8:30\n";
            echo "212050037,YOHAN,2/2/2019,17:50\n";
            echo "112050037,YOHAN,3/2/2019,8:30\n";
            echo "212050037,YOHAN,3/2/2019,17:50\n";
            echo "112050037,YOHAN,4/2/2019,8:30\n";
            exit();      
}
if($param=='PEMEL'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=pemel.csv");
			echo "kode_kegiatan,blok,luas,hk_kbl,hk_kht,hk_khl,ttl_rupiah_hk,premi,luas_borongan,rupiah_borongan,kode_material_1,jlh_mat_1,kode_material_2,jlh_mat_2,kode_material_3,jlh_mat_3,kode_material_4,jlh_mat_4\n";
            echo "621100201,TPRE01A00b,50,5,,1,591200,50000,,,31201007,25,31201010,1,,,,\n";
            exit();      
}
if($param=='HARGAHARIANPASAR'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=HARGAHARIANPASAR.csv");
            echo "tahun,bulan,tanggal,kodeproduk,pasar,satuan,harga,matauang,statusharga,ffa,mni\n";
            echo "2014,12,1,40000001,Rotterdam,KG,6000,IDR,Best Bidder,2,6\n";
            echo "2014,12,2,40000001,Astra - Dumai,KG,7000,IDR,Price Idea,3,0\n";
            echo "2014,12,3,40000001,ID - Indonesia,KG,8000,IDR,Traded,4,6.5\n";
            echo "2014,12,12,40000001,Medco - Papua,KG,5000,IDR,Best Bidder,5,8\n";
            echo "2014,12,14,40000001,Medco - Papua,KG,5000,IDR,Best Bidder,5,0\n";
            echo "2014,12,15,40000001,Rotterdam,KG,5000,IDR,Best Bidder,5,4\n";
            exit();        
}
if($param=='TIMBANGANPEMBELI'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=TIMBANGANPEMBELI.csv");
            echo "notransaksi,nokontrak,kgpembeli\n";
            echo "A000437,001/BAS-AAJ/CPO/I/2017,10000\n";
            echo "A000472,001/BAS-AAJ/CPO/I/2017,8000\n";
            echo "A000473,001/BAS-AAJ/CPO/I/2017,7000\n";
            echo "A000532,001/BAS-AAJ/CPO/I/2017,9000\n";
            exit();        
}
if($param=='KURSMATAUANG'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=examplekurs.csv");
        echo "kode,daritanggal,kursjual,kursbeli,kurs\n";
        echo "EUR,20170709,16500,17000,16750\n";
        echo "EUR,20170710,16500,17000,16750\n";
        echo "EUR,20170711,16500,17000,16750\n";
        echo "EUR,20170712,16500,17000,16750\n";
        echo "EUR,20170713,16500,17000,16750\n";
        exit();
}
if($param=='GAPOK'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=examplegapok.csv");
        echo "tahun,nik,idkomponen,jumlah\n";
        echo "2019,8,1,5000000\n";
        echo "2019,1,1,7000000\n";
        echo "2019,2,1,8000000\n";
        echo "2019,3,1,9000000\n";
        exit();
}
if($param=='PTKP'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=exampleptkp.csv");
        echo "nik,statuspajak\n";
        echo "8,K1\n";
        echo "1,TK\n";
        echo "2,K3\n";
        echo "3,K2\n";
        exit();
}
if($param=='RAPEL'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=exampleprapel.csv");
        echo "periodegaji,komponengaji,nik,jumlah,keterangan\n";
        echo "2019-11,14,8,2000000,Rapel gaji bulan lalu\n";
        echo "2019-11,14,2,2000000,Rapel gaji bulan lalu\n";
        echo "2019-11,14,3,2000000,Rapel gaji bulan lalu\n";
        echo "2019-11,14,345,2000000,Rapel gaji bulan lalu\n";
        exit();
}

if($param=='KEGIATAN'){ 
		$tab="";
		$tab.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Kode Kegiatan</td>
				<td align=center>Nama Kegiatan</td>
				<td align=center>Kelompok</td>
				<td align=center>Satuan</td>
				<td align=center>Noakun</td>
				</tr>
				</thead>
				";
		$str="select * from ".$dbname.".setup_kegiatan where status='1' and substr(kodekegiatan,1,3) in ('126','128','611','621')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td>" . $bar['kodekegiatan'] . "</td>";
			$tab.="<td>" . $bar['namakegiatan'] . "</td>";
			$tab.="<td>" . $bar['kelompok'] . "</td>";
			$tab.="<td>" . $bar['satuan'] . "</td>";
			$tab.="<td>" . $bar['noakun'] . "</td>";
		}
		$tab.="</tr>";
		$tab.="<table>";
       
	   $tab2="";
		$tab2.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Blok</td>
				<td align=center>tahuntanam</td>
				<td align=center>luas</td>
				<td align=center>pokok</td>
				<td align=center>status</td>
				</tr>
				</thead>
				";
		if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0'";
		}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and substr(kodeorg,1,4) in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
		}else{
			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$tab2.="<tr class=rowcontent>";
			$tab2.="<td align=center>" . $no . "</td>";
			$tab2.="<td>" . $bar['kodeorg'] . "</td>";
			$tab2.="<td>" . $bar['tahuntanam'] . "</td>";
			$tab2.="<td>" . $bar['luasareaproduktif'] . "</td>";
			$tab2.="<td>" . $bar['jumlahpokok'] . "</td>";
			$tab2.="<td>" . $bar['statusblok'] . "</td>";
		}
		$tab2.="</tr>";
		$tab2.="<table>";
		
		$tab3="";
		$tab3.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Kode Barang</td>
				<td align=center>Nama Barang</td>
				<td align=center>Satuan</td>
				</tr>
				</thead>
				";
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang in ('311','312','313','361','371','372','373','381','382','383','385') and inactive=0";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$tab3.="<tr class=rowcontent>";
			$tab3.="<td align=center>" . $no . "</td>";
			$tab3.="<td>" . $bar['kodebarang'] . "</td>";
			$tab3.="<td>" . $bar['namabarang'] . "</td>";
			$tab3.="<td>" . $bar['satuan'] . "</td>";
		}
		$tab3.="</tr>";
		$tab3.="<table>";
		
		
	    $tempnm = explode("/",$_SERVER['PHP_SELF']);
		$nop = substr($tempnm[2],0,strripos($tempnm[2],'.')).".xls";
		$nop = "Master.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("kegiatan", $tab);
		$xls->addSheet("blok", $tab2);
		$xls->addSheet("barang", $tab3);
		$xls->headers($nop);
		echo $xls->buildFile();
}
if($param=='BGTVHC'){ 
		$tab="";
		$tab.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>Kode Barang</td>
				<td align=center>Jumlah</td>
				</tr>
				</thead>
				";
		
			$tab.="<tr class=rowcontent>";
			$tab.="<td>351010003</td>";
			$tab.="<td>2000</td>";
		
		$tab.="</tr>";
		$tab.="<table>";
       
	    
		$nop = "Master.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("budget_vhc", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
}


if($param=='SENSUS'){
		$tab2="";
		$tab2.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader  style=background-color:grey>
				<td align=center rowspan=1>No</td>
				<td align=center rowspan=1>Tahun</td>
				<td align=center rowspan=1>Status</td>
				<td align=center rowspan=1>Divisi</td>
				<td align=center rowspan=1>Blok</td>
				<td align=center rowspan=1>TT</td>
				<td align=center rowspan=1>Luas</td>
				<td align=center rowspan=1>Pokok</td>
				<td align=center rowspan=1>Bulan</td>
				<td align=center rowspan=1>Jjg</td>
				<td align=center rowspan=1>Kg</td>
				<td align=center rowspan=1>BJR</td>
				<td align=center rowspan=1>Kerapatan</td>
				";
		// for ($i=$data['sms']; $i <= $data['sms2'] ; $i++) { 
		// 	if ($i < 10) {
		// 		$tab2.="<td align=center colspan=3>".$data['tahun']."-0".$i."</td>";
		// 	} else {
		// 		$tab2.="<td align=center colspan=3>".$data['tahun']."-".$i."</td>";
		// 	}
		// }
		$tab2.="</tr>";
		// $tab2.="<tr class=rowheader  style=background-color:grey>";
		// for ($i=$data['sms']; $i <= $data['sms2'] ; $i++){			
		// 	$tab2.="<td align=center>Jjg</td>";
		// 	$tab2.="<td align=center>Kg</td>";
		// 	$tab2.="<td align=center>BJR</td>";
		// }
		// $tab2.="</tr>";
		$tab2.="</thead>";
		
		$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and kodeorg like '".$data['divisi']."%' and statusblok='".$data['stsblok']."'";
		$res=fetchdata($str);
		$no='0';
		for ($i=$data['sms']; $i <= $data['sms2'] ; $i++){			
			foreach($res as $bar){
				$no+=1;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td align=center style=background-color:grey>" . $no . "</td>";
				$tab2.="<td style=background-color:grey>" . $data['tahun'] . "</td>";
				$tab2.="<td style=background-color:grey>" . $bar['statusblok'] . "</td>";
				$tab2.="<td style=background-color:grey>" . $data['divisi'] . "</td>";
				$tab2.="<td style=background-color:grey>" . $bar['kodeorg'] . "</td>";
				$tab2.="<td style=background-color:grey>" . $bar['tahuntanam'] . "</td>";
				$tab2.="<td style=background-color:grey>" . $bar['luasareaproduktif'] . "</td>";
				$tab2.="<td style=background-color:grey>" . $bar['jumlahpokok'] . "</td>";
					$tab2.="<td align=center>".$i."</td>";
					$tab2.="<td align=center></td>";
					$tab2.="<td align=center></td>";
					$tab2.="<td align=center></td>";
					$tab2.="<td align=center></td>";
				$tab2.="</tr>";
			}
		}
		$tab2.="<table>";
		
		// echo"<pre>";
		// print_r($tab2);
		// echo"</pre>";
		// exit("Warning");

		
		#echo $tab2;
		#exit("error");
		/* $stream=$tab2;
		$nop_ = "sensus";
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
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        } */
		$tab5="";
		$tab5.="<table border=1>";
		$tab5.="<tr><td>Dilarang merubah kolom header (insert, delete, hide kolom)</td></tr>";
		$tab5.="<tr><td>Isi hanya pada area Janjang, Kg, Bjr saja</td></tr>";
		$tab5.="<tr><td>Save As dengan format *.csv</td></tr>";
		$tab5.="<table>";
		
		$nop = "sensus.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("info", $tab5);
		$xls->addSheet("sensus", $tab2);
		$xls->headers($nop);
		echo $xls->buildFile();   
}
if($param=='MANDOR'){ 
	header("Cache-Control: must-revalidate");
	header("Pragma: must-revalidate");
	header("Content-type: application/vnd.ms-excel");
	header("Content-disposition: attachment; filename=Contoh Format Data Mandor.csv");
	echo "NIK MANDOR,NAMA MANDOR,NIK KARYAWAN,NAMA KARYAWAN,NO URUT\n";
	echo "13700378,MANDOR RAWAT RWKE 03,13700379,TENAGA RAWAT 05 RWKE,1\n";
	exit();      
}


if($param=='REKPUPUK'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=rekomendasipupuk.csv");
			echo "blok,tahuntanam,luas,pokok,status,jenistanah,kodepupuk,aplikasi,dosis,jumlah,periode\n";
            echo "ASSE01J025,1992,25,3250,TM,Inceptisol,311010006,1,1,3250,2020-05\n";
            exit();      
}
if($param=='TPH'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=tph.csv");
            echo "blok,notph,latitude,longitude,luas\n";
            echo "KAGE01A54a,KAGE01TPB00001,KAGE01A54a01,-6.2772923,106.7965134,3.5\n";
            echo "KAGE01A54a,,KAGE01A54a02,-6.2773943,106.7966334,3.5\n";
            exit();      
}
if($param=='MASTERREKPPK'){ 
		$tab="";
		$tab2="";
		$tab2.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Blok</td>
				<td align=center>tahuntanam</td>
				<td align=center>luas</td>
				<td align=center>pokok</td>
				<td align=center>status</td>
				<td align=center>jenistanah</td>
				</tr>
				</thead>
				";
		if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0'";
		}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and substr(kodeorg,1,4) in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."')";
		}else{
			$str = "select * from " . $dbname . ".setup_blok where luasareaproduktif>'0' and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$tab2.="<tr class=rowcontent>";
			$tab2.="<td align=center>" . $no . "</td>";
			$tab2.="<td>" . $bar['kodeorg'] . "</td>";
			$tab2.="<td>" . $bar['tahuntanam'] . "</td>";
			$tab2.="<td>" . $bar['luasareaproduktif'] . "</td>";
			$tab2.="<td>" . $bar['jumlahpokok'] . "</td>";
			$tab2.="<td>" . $bar['statusblok'] . "</td>";
			$tab2.="<td>" . $bar['kodetanah'] . "</td>";
		}
		$tab2.="</tr>";
		$tab2.="<table>";
		
		$tab3="";
		$tab3.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Kode Barang</td>
				<td align=center>Nama Barang</td>
				<td align=center>Satuan</td>
				</tr>
				</thead>
				";
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang in ('311') and inactive=0";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no='';
		while($bar=$res->fetch()){
			$no+=1;
			$tab3.="<tr class=rowcontent>";
			$tab3.="<td align=center>" . $no . "</td>";
			$tab3.="<td>" . $bar['kodebarang'] . "</td>";
			$tab3.="<td>" . $bar['namabarang'] . "</td>";
			$tab3.="<td>" . $bar['satuan'] . "</td>";
		}
		$tab3.="</tr>";
		$tab3.="<table>";
		$tab4="";
		$tab4.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Kode</td>
				<td align=center>Nama</td>
				</tr>
				</thead>
				";
		$arrapl=array('1'=>'Satu','2'=>'Dua','3'=>'Tiga','4'=>'Empat','5'=>'Lima','6'=>'Enam','7'=>'Tujuh','8'=>'Delapan','9'=>'Sembilan','10'=>'Sepuluh','11'=>'Sebelas','12'=>'Dua Belas','1e'=>'Extra Satu','2e'=>'Extra Dua','3e'=>'Extra Tiga');
		$no='';
		foreach ($arrapl as $key => $val){
			$no+=1;
			$tab4.="<tr class=rowcontent>";
			$tab4.="<td align=center>" . $no . "</td>";
			$tab4.="<td>" . $key. "</td>";
			$tab4.="<td>" . $val. "</td>";
		}
		$tab4.="</tr>";
		$tab4.="<table>";
		
		$tab5="";
		$tab5.="<table border=1>";
		$tab5.="<tr><td>Blok</td><td>:</td><td>Isi dengan kode blok (10 Digit, lihat di Master pada sheet blok)</td></tr>";
		$tab5.="<tr><td>Tahuntanam</td><td>:</td><td>Isi dengan tahun tanam (4 Digit, lihat di Master pada sheet blok)</td></tr>";
		$tab5.="<tr><td>Luas</td><td>:</td><td>Isi dengan Luas (lihat di Master pada sheet blok)</td></tr>";
		$tab5.="<tr><td>Pokok</td><td>:</td><td>Isi dengan Pokok (lihat di Master pada sheet blok)</td></tr>";
		$tab5.="<tr><td>Status</td><td>:</td><td>Isi dengan Status Tanaman (lihat di Master pada sheet blok)</td></tr>";
		$tab5.="<tr><td>Jenis tanah</td><td>:</td><td>Isi dengan Jenis tanah (lihat di Master pada sheet blok)</td></tr>";
		$tab5.="<tr><td>Kode Pupuk</td><td>:</td><td>Isi dengan Kode Barang (lihat di Master pada sheet barang)</td></tr>";
		$tab5.="<tr><td>Aplikasi</td><td>:</td><td>Isi dengan Kode Aplikasi (lihat di Master pada sheet aplikasi)</td></tr>";
		
		
		$tab5.="<table>";
		
	    $tempnm = explode("/",$_SERVER['PHP_SELF']);
		$nop = substr($tempnm[2],0,strripos($tempnm[2],'.')).".xls";
		$nop = "Master.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("penjelasan", $tab5);
		$xls->addSheet("blok", $tab2);
		$xls->addSheet("barang", $tab3);
		$xls->addSheet("aplikasi", $tab4);
		$xls->headers($nop);
		echo $xls->buildFile();
}
if($param=='BGTPKS'){ 
		$tab="";
		$tab.="<table cellspacing=1 border=1>
				<thead><tr class=rowheader>
				<td align=center>Kode Barang</td>
				<td align=center>Jumlah</td>
				<td align=center>Noakun</td>
				<td align=center>Aruskas</td>
				<td align=center>Jenis</td>
				</tr>
				</thead>
				";
		
			$tab.="<tr class=rowcontent>";
			$tab.="<td>351010003</td>";
			$tab.="<td>2000</td>";
			$tab.="<td>6310107</td>";
			$tab.="<td>119007</td>";
			$tab.="<td>isikan : consumables atau recurrent atau nonrecurrent</td>";
		
		$tab.="</tr>";
		$tab.="<table>";
       
	    
		$nop = "budget_pks.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("budget_pks", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
}