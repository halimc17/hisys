<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method         = checkPostGet('method','');

$kodeunit       = checkPostGet('kodeunit','');
$kodeunitcari   = checkPostGet('kodeunitcari','');
$tipe    = checkPostGet('tipe','');
$tipecari    = checkPostGet('tipecari','');
$supplier       = checkPostGet('supplier','');
$aktif          = checkPostGet('aktif','');
$tahuntanam     = checkPostGet('tahuntanam','');
$harga          = checkPostGet('harga','');
$budgetharga    = checkPostGet('budgetharga','');
$disbunharga    = checkPostGet('disbunharga','');
$awalrealisasi  = checkPostGet('awalrealisasi','');
$awaldisbun     = checkPostGet('awaldisbun','');
// $suppliercari= checkPostGet('suppliercari','');
$notransaksi    = checkPostGet('notransaksi','');

$tanggal        =tanggalsystemn(checkPostGet('tanggal',''));
$tanggal2       =tanggalsystemn(checkPostGet('tanggal2',''));
$jam        =checkPostGet('jam','');
$jam2       =checkPostGet('jam2','');
$menit        =checkPostGet('menit','');
$menit2       =checkPostGet('menit2','');

$tanggaljam       =checkPostGet('tanggaljam','');
$tanggaljam2       =checkPostGet('tanggaljam2','');


$tanggalcopy    =tanggalsystemn(checkPostGet('tanggalcopy',''));
$tanggal2copy   =tanggalsystemn(checkPostGet('tanggal2copy',''));

$jamcopy        =checkPostGet('jamcopy','');
$jam2copy       =checkPostGet('jam2copy','');
$menitcopy        =checkPostGet('menitcopy','');
$menit2copy       =checkPostGet('menit2copy','');
$tipecopy    = checkPostGet('tipecopy','');
$kodeunitcopy       = checkPostGet('kodeunitcopy','');


$kode           = checkPostGet('kode','');
$batasbawah     = checkPostGet('batasbawah','');
$batasatas      = checkPostGet('batasatas','');

$tahuntanamcari = checkPostGet('tahuntanamcari','');
$tipehargacari = checkPostGet('tipehargacari','');
$tanggalcari    =tanggalsystemn(checkPostGet('tanggalcari',''));



#= approval karyawan
$str = "select * from ".$dbname.".datakaryawan  where karyawanid in (select karyawanid from ".$dbname.".approval where jenispersetujuan='HBT') ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmkaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
	
}

$optposting=array(''=>$_SESSION['lang']['pilihdata'],'0'=>'Belum Disetujui','1'=>'Disetujui','3'=>'Ditolak','9'=>'Proses Persetujuan');
$maxaproval = checkPostGet('maxaproval','');

$kodept=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$nmorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$arraktif=array("0"=>"Tidak","1"=>"Ya");

// echo $method;


		$str="select * from ".$dbname.".organisasi where tipe in ('KEBUN') ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$nmorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
		}
		
		$str = "SELECT a.supplierid,a.namasupplier,b.tipe FROM " . $dbname . ".log_5supplier a
		left join log_5supkelompok b on a.supplierid=b.supplierid
		where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSAFI','SUPPLIERTBSKUD') order by a.namasupplier asc";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
			$tipesupplier[$bar['supplierid']]=$bar['tipe'];
		}

switch ($method) {

	
	case 'examplecsv':
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=FileUploadHargaBeliTBS.csv");
        echo "tanggaldari,tanggalsampai,unit,hargarealisasi,hargadisbun,tahuntanam\n";
        echo "2025-04-01,2025-04-30,CARE,2000,2100,2012\n";
        echo "2025-04-01,2025-04-30,LANE,2500,2200,2011\n";
        exit();
    break;

	case 'formupload':
        $form = "
        <fieldset>
        <legend id=fieldsetnotransaksi><b>".$notransaksi."</b></legend>
        <table border=0>
			<tr>
				<td style=width:150px;>Format</td>
				<td>:</td>
				<td>Format wajib mengikuti contoh berikut, <a href=pmn_kontrakbeli_slave.php?method=examplecsv target=frame>Click here for example</a></td>
			</tr>
			<tr>
				<td>File</td>
				<td>:</td>
				<td><input name=upload type=file id=upload class=mybutton style=width:160px;></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td><button id=uploadcsv class=mybutton onclick=uploadcsv()>" . $_SESSION['lang']['upload'] . "</button></td>
			</tr>
        </table>
        </fieldset>
        ";

        $form.="<table class='sortable' cellspacing='1' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=2 align=center>No</th>
				<th colspan=2 align=center>".$_SESSION['lang']['periode']." </th> 
				<th rowspan=2 align=center>".$_SESSION['lang']['tahuntanam']."<br></th> 
				<th rowspan=2 align=center>Kelas Buah</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['harga']."<br>(Rp/Kg)</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['ppn']."<br>(%)</th> 
			</tr>  
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['mulai']." </th>
				<th align=center>".$_SESSION['lang']['akhir']." </th>
			</tr>  
		</thead>";

			$str = "select * from ".$dbname.".".$tabledt."  where notransaksi='".trim($param['notransaksi'])."' order by id desc"; ## YG GW RUBAH
			$res=fetchdata($str);
			if(empty($res))
            {
                $form.="<tr class=rowcontent><td colspan=10 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }else{
				foreach($res as $bar){
					$no++;
					$optkls=makeOption($dbname,'pmn_5kelasbuah','kode,namakelas',"kode='".$bar['kodeklsbuah']."'");
					$form.="<tr class=rowcontent style=height:25px>";
					$form.="<td align=center>".$no."</td>";
					$form.="<td >".tanggalnormal($bar['tanggaldari'])."</td>";
					$form.="<td >".tanggalnormal($bar['tanggalsampai'])."</td>";
					$form.="<td align=center>".$bar['tahuntanam']."</td>";
					$form.="<td align=left>".@$optkls[$bar['kodeklsbuah']]."</td>";
					$form.="<td align=right>".number_format($bar['harga'],2)."</td>";
					// $form.="<td align=right>".number_format($bar['hargabrondolan'],2)."</td>";
					$form.="<td align=right>".number_format($bar['ppn'],2)."</td>";
					
					
					// $form.="<td align=center style=\"width:25px;\"><img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"editdt('".$bar['id']."');\"></td>";						
					// $form.="<td align=center style=\"width:25px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deletedt('".$bar['id']."','".trim($param['notransaksi'])."');\"></td>";
					
					// $form.="</tr>";
				}
			}
        $form.="</table>";
        echo $form;    
    break;
 
 	case 'uploadcsv':
	
        if (isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != '') {
            $file = $_FILES['file']['tmp_name'];

            // buka file CSV
            $handle = fopen($file, "r");
            if (!$handle) {
                echo "Gagal membuka file CSV.";
                exit;
            }
	
            // Lewati baris header
            fgetcsv($handle);

            $baris = 0;
			
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $baris++;

                // ambil data dari kolom csv
                $tanggaldari   = date('Y-m-d', strtotime(str_replace('/', '-', trim($data[0]))));
                $tanggalsampai = date('Y-m-d', strtotime(str_replace('/', '-', trim($data[1]))));
                $unit          = trim($data[2]);
                $harga         = (float) $data[3];
                $hargadisbun   = (float) $data[4];
                $tahuntanam    = trim($data[5]);

                // buat nomor transaksi otomatis: YYYYMMDD + 0000 + UNIT + tahuntanam
                $notransaksi = date('Ymd') . sprintf("%04d", $baris) . $unit . $tahuntanam;

                // insert ke tabel
                $sql = "INSERT INTO pmn_hargabelitbs (
                            notransaksi, tanggal, tanggal2, kodeorg, supplierid, harga, hargadisbun, tahuntanam,
                            posting, createby, createtime
                        ) VALUES (
                            '$notransaksi',
                            '$tanggaldari',
                            '$tanggalsampai',
                            '$unit',
                            '$unit',
                            '$harga',
                            '$hargadisbun',
                            '$tahuntanam',
                            0,
                            '0000000001',
                            '2025-10-23 15:29:28'
                        )";

				try {
					$owlPDO->exec($sql);
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}
            }

            fclose($handle);
            echo "Upload CSV berhasil. Total data: $baris baris.";
        } else {
            echo "File CSV tidak ditemukan.";
        }
    break;

	
	case'gettipesupplier':
		// exit("Error:A");
		

		$opttipesupplier= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		// $str = "SELECT distinct(tipe) as tipe FROM " . $dbname . ".pmn_5hargabelitbs where kodeunit='".$kodeunit."'";
		// // echo $str;
		// exit("warning :".$str);
		// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while ($bar = $res->fetch()) {
		//    $opttipesupplier.="<option value=" . $bar['tipe'] . ">" . $bar['tipe'] . "</option>";
		// }
	
		echo $opttipesupplier;
	break;
	
	case'loaddatagrade':
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['grade']."</th>
				<th align=center>".$_SESSION['lang']['batasbawah']."</th>
				<th align=center>".$_SESSION['lang']['batasatas']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>
			<tbody>";
		$no = 0;
		$str = "select * from ".$dbname.".pmn_5gradetbs order by kodeunit asc,kode asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=center>".$bar['kodeunit']."</td>";
            $tab.="<td align=center>".$bar['kode']."</td>";
            $tab.="<td align=right>>= ".number_format($bar['batasbawah'],2)."</td>";
            $tab.="<td align=right>< ".number_format($bar['batasatas'],2)."</td>";
            $tab.="<td align=center>".getNamaKaryawan($bar['updateby'])."</td>";
            $tab.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='delete' onclick=\"hapusgrade('".$bar['kodeunit']."','".$bar['kode']."')\"></td>";          					
            $tab.="</tr>";
        }
		echo $tab;
	break;
	
	
	case'simpangrade':
		$str = "INSERT INTO  ".$dbname.".`pmn_5gradetbs` 
		(`kodeunit`,`kode`,`batasbawah`,`batasatas`,`createby`, `createtime`,`updateby`)
		values ('".$kodeunit."','".$kode."','".$batasbawah."','".$batasatas."','".$_SESSION['standard']['userid']."',
		'".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."')";
		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	
	case'hapusgrade':
		$str = "delete from ".$dbname.".`pmn_5gradetbs` where kodeunit='".$kodeunit."' and kode='".$kode."'";			
				// exit("Error:$str");
		// $str = "delete from ".$dbname.".`pmn_hargabelitbs` where notransaksi='".$notransaksi."'";			
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	
	case'hapushargatbs':
		$str = "delete from ".$dbname.".`pmn_hargabelitbs` where 
				notransaksi='".$notransaksi."'";			
		// $str = "delete from ".$dbname.".`pmn_hargabelitbs` where notransaksi='".$notransaksi."'";			
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'postinght':
		$str = "update ".$dbname.".`pmn_hargabelitbs` set posting='1' where 
				notransaksi='".$notransaksi."'";			
 		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'prosescopy':
		//indra
		#===== cek data
		
		//validasi
		if($kodeunit==''||$tanggal==''||$tanggal2==''||$tanggalcopy==''||$tanggal2copy==''||$tipe==''||$tipecopy==''||$kodeunitcopy==''){
			exit("Warning:Lengkapi pengisian");
		}
		
		#= sudah ada data ditgl dan diunit ini
		$whereket=$wherethntnm="";
		if($tahuntanam!=''){
			$wherethntnm=" and tahuntanam='".$tahuntanam."'";
			$whereket=" tahun tanam / grade ".$tahuntanam." ";
		}
		
		$tanggal=$tanggal." ".$jam.":".$menit;
		$tanggal2=$tanggal2." ".$jam2.":".$menit2;
		
		$tanggalcopytransaksi=$tanggalcopy;
		$tanggalcopy=$tanggalcopy." ".$jamcopy.":".$menitcopy;
		$tanggal2copy=$tanggal2copy." ".$jam2copy.":".$menit2copy;
		
		$str = "select count(*) as jumlah from ".$dbname.".`pmn_hargabelitbs` where tanggal='".$tanggal."'  and tanggal2='".$tanggal2."' and kodeorg='".$kodeunit."' ".$wherethntnm." and tipe='".$tipe."'";	
		$res=fetchdata($str);
		foreach($res as $bar){
			$jumlahdata=$bar['jumlah'];
		}			
			
		if($jumlahdata<1){
			exit("Warning:Gagal proses, Data tidak ada untuk unit ".$kodeunit." tipe ".$tipe." ditanggal ".tanggalnormal($tanggal)." ".$jam.":".$menit." s/d ".tanggalnormal($tanggal2)." ".$jam2.":".$menit2." ".$whereket." dan tipe ".$tipe." ");
		}	
		
		
		#= cek sudah ada data untuk tanggal berikutnya
		$str = "select count(*) as jumlah from ".$dbname.".`pmn_hargabelitbs` where tanggal='".$tanggalcopy."'  and tanggal2='".$tanggal2copy."' and kodeorg='".$kodeunitcopy."' ".$wherethntnm." and tipe='".$tipecopy."'";	
		$res=fetchdata($str);
		foreach($res as $bar){
			$jumlahdata=$bar['jumlah'];
		}
		if($jumlahdata>0){
			exit("Warning:Gagal proses, Sudah ada data untuk ".$kodeunitcopy." tipe ".$tipecopy." ditanggal ".tanggalnormal($tanggalcopy)." ".$jamcopy.":".$menitcopy." s/d ".tanggalnormal($tanggal2copy)." ".$jam2copy.":".$menit2copy."  ".$whereket."  ");
		}


		# Jika tipesupplier berbeda dan unit berbeda maka, hanya harga saja sesuai grade / tahun tanam yang ambil datanya, daftar supplier unit copy sesuai dengan tipesuppliertbs disetup
		# unit awal : BPJM
		# tipesupplier awal : AFI
		# KBPE,KSPE,SD1E,SD2E,SD3E,SD4E,SNPE,AA1E,AA2E
		# unit tujuan : KSPM
		# tipesupplier tujuan : AFI
		# AA1E,AA2E,BPJE,KBPE,SD1E,SD2E,SD3E,SD4E,SNPE
		
		# proses copy data
		if($kodeunit==$kodeunitcopy and $tipe==$tipecopy){
			$str = "select * from ".$dbname.".`pmn_hargabelitbs` where tanggal='".$tanggal."'  and tanggal2='".$tanggal2."' and kodeorg='".$kodeunit."' ".$wherethntnm." and tipe='".$tipe."'";	
			$res=fetchdata($str);
			foreach($res as $bar){
				# insert
				$notransaksi=str_replace('-','',$tanggalcopytransaksi).$jamcopy.$menitcopy.$kodeunitcopy.$bar['tahuntanam'].$tipecopy;
				$str = "INSERT INTO  ".$dbname.".`pmn_hargabelitbs` 
				(`notransaksi`,`tanggal`,`tanggal2`, `kodeorg`, `supplierid`,`tahuntanam`, `harga`,`hargabudget`, `hargadisbun`, `posting`, `createby`, `createtime`,
				`updateby`, `postingby`, `postingtime`,`tipe`) 
				values ('".$notransaksi."','".$tanggalcopy."','".$tanggal2copy."','".$kodeunitcopy."','".$bar['supplierid']."',
				'".$bar['tahuntanam']."','".$bar['harga']."',
				'".$bar['hargabudget']."','".$bar['hargadisbun']."','0','".$_SESSION['standard']['userid']."',
				'".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','','','".$tipecopy."')";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}
			}	
		} else {
			# daftar nama supplier
			$str = "select * from ".$dbname.".pmn_5hargabelitbs where kodeunit='".$kodeunitcopy."' and tipe='".$tipecopy."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$arrsupplier[$bar['supplier']]=$bar['supplier'];
			}
			
			$arrthntnm=array();
			$str = "select * from ".$dbname.".`pmn_hargabelitbs` where tanggal='".$tanggal."'  and tanggal2='".$tanggal2."' and kodeorg='".$kodeunit."' ".$wherethntnm." and tipe='".$tipe."' group by tahuntanam";	
			$res=fetchdata($str);
			foreach($res as $bar){
				$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
				$dtharga[$bar['tahuntanam']]=$bar['harga'];
				$dthargabudget[$bar['tahuntanam']]=$bar['hargabudget'];
				$dthargadisbun[$bar['tahuntanam']]=$bar['hargadisbun'];
			}
			
			array_multisort($arrthntnm,SORT_ASC);
			
			#= insert
			foreach($arrthntnm as $dttahuntanam){
				foreach($arrsupplier as $dtsupplier){
					$notransaksi=str_replace('-','',$tanggalcopytransaksi).$jamcopy.$menitcopy.$kodeunitcopy.$dttahuntanam.$tipecopy;
					$str = "INSERT INTO  ".$dbname.".`pmn_hargabelitbs` 
					(`notransaksi`,`tanggal`,`tanggal2`, `kodeorg`, `supplierid`,`tahuntanam`, `harga`,`hargabudget`, `hargadisbun`, `posting`, `createby`, `createtime`,
					`updateby`, `postingby`, `postingtime`,`tipe`) 
					values ('".$notransaksi."','".$tanggalcopy."','".$tanggal2copy."','".$kodeunitcopy."','".$dtsupplier."',
					'".$dttahuntanam."','".$dtharga[$dttahuntanam]."',
					'".$dthargabudget[$dttahuntanam]."','".$dthargadisbun[$dttahuntanam]."','0','".$_SESSION['standard']['userid']."',
					'".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','','','".$tipecopy."')";
					try {
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						echo " Gagal," . addslashes($e->getMessage());
					}
				}
			}
		}
	break;
	
	
	case'getsupmaster':
				
		$optsupp= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		
		$optsupp.="<option value=''>==================== INTERNAL ====================</option>";
		$str = "SELECT * FROM " . $dbname . ".organisasi where tipe in ('KEBUN') and induk='".$kodept[$kodeunit]."' and inti=1";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optsupp.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
		}

		
		$optsupp.="<option value=''>==================== AFILIASI ====================</option>";
		$str = "SELECT * FROM " . $dbname . ".organisasi where tipe in ('KEBUN') and induk!='".$kodept[$kodeunit]."' and inti=1";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optsupp.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
		}
		
		$optsupp.="<option value=''>==================== KUD ====================</option>";
		$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
				left join log_5supkelompok b on a.supplierid=b.supplierid
				where a.status=1 and b.status=1 and b.tipe in ('SUPPLIERTBSKUD') order by a.namasupplier asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optsupp.="<option value=" . $bar['supplierid'] . ">" . $bar['namasupplier'] . "</option>";
		}
		
		$optsupp.="<option value=''>==================== SUPPLIER EXTERNAL ====================</option>";
		$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
				left join log_5supkelompok b on a.supplierid=b.supplierid
				where a.status=1 and b.tipe in ('SUPPLIERTBSEXT') order by a.namasupplier asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optsupp.="<option value=" . $bar['supplierid'] . ">" . $bar['namasupplier'] . "</option>";
		}

		$optsupp.="<option value=''>==================== SUPPLIER EXTERNAL ====================</option>";
		$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
				left join log_5supkelompok b on a.supplierid=b.supplierid
				where a.status=1 order by a.namasupplier asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optsupp.="<option value=" . $bar['supplierid'] . ">" . $bar['namasupplier'] . "</option>";
		}
		// exit('error '.$str);
		echo $optsupp;
	break;
	
	
	case'simpanharga':
	
		$notransaksi=str_replace('-','',$tanggal).$jam.$menit.$kodeunit.$tahuntanam.$tipe;
		$tanggal=$tanggal." ".$jam.":".$menit;
		$tanggal2=$tanggal2." ".$jam2.":".$menit2;
		// exit("Error:$notransaksi");
	
		$str = "delete from ".$dbname.".`pmn_hargabelitbs` where 
				notransaksi='".$notransaksi."' and  tanggal='".$tanggal."'  and tanggal2='".$tanggal2."' and 
				kodeorg='".$kodeunit."' and tahuntanam='".$tahuntanam."' and supplierid='".$supplier."'";			
		// $str = "delete from ".$dbname.".`pmn_hargabelitbs` where notransaksi='".$notransaksi."'";			
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}

		$str = "INSERT INTO  ".$dbname.".`pmn_hargabelitbs` 
		(`notransaksi`,`tanggal`,`tanggal2`, `kodeorg`, `supplierid`, `tahuntanam`, `harga`,
		`hargabudget`, `hargadisbun`, `posting`, `createby`, `createtime`,
		`updateby`, `postingby`, `postingtime`,`tipe`)
		values ('".$notransaksi."','".$tanggal."','".$tanggal2."','".$kodeunit."','".$supplier."','".$tahuntanam."','".$harga."',
		'".$budgetharga."','".$disbunharga."','0','".$_SESSION['standard']['userid']."',
		'".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','','','".$tipe."')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'previewharga':
		
		$tanggal=$tanggal." ".$jam.":".$menit;
		
		#= cek jika sudah disetujui data tidak bisa diubah
		$str = "select count(*) as jumlah from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' 
		and tanggal='".$tanggal."' and tahuntanam='".$tahuntanam."' and posting=1  ";
		// echo"<pre></pre>";
		// print_r($param);
		// exit('error '.$str);
		$res=fetchdata($str);
		foreach($res as $bar){
			$jumlahdata=$bar['jumlah'];
		}
			
		if($jumlahdata>0){
			exit("Warning:Data untuk unit : ".$kodeunit." tahun tanam ".$tahuntanam." ditanggal ".tanggalnormal($tanggal)." ".$jam.":".$menit." s/d ".tanggalnormal($tanggal2)." ".$jam2.":".$menit2." untuk tipe supplier ".$tipe." sudah disetujui ");
		}			
		
		
	
		// if(substr($tanggal,-2)!='01' and  substr($tanggal,-2)!='16'){
			// exit("Warning:Tanggal yang diperbolehkan hanya tanggal 1 / 16");
		// }
	
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kodesupplier']."</td>
				<td align=center>".$_SESSION['lang']['namasupplier']."</td> 
				<td align=center>".$_SESSION['lang']['harga']."<br>Disbun</td>
				<td align=center>".$_SESSION['lang']['harga']."<br>".$_SESSION['lang']['realisasi']."</td>
				
			</tr>
			</thead>
			<tbody>";
		
		
		$no = 0;
		$str = "select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and tanggal='".$tanggal."' and tahuntanam='".$tahuntanam."' and tipe='".$tipe."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$hargadisbun[$bar['kodeorg']]=$bar['hargadisbun'];
			$hargarealisasi[$bar['kodeorg']]=$bar['harga'];
			$hargabudget[$bar['kodeorg']]=$bar['hargabudget'];
		}

		// $str = "select * from ".$dbname.".pmn_5hargabelitbs where kodeunit='".$kodeunit."' and tipe='".$tipe."'  ORDER BY LENGTH(supplier) asc";
		// $res=fetchdata($str);
		// foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent id=row".$no.">";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td id=supplierharga".$no.">".$kodeunit."</td>";
			
			// if(strlen($bar['supplier'])>6){
				//   $tab.="<td>".$nmsupplier[$bar['supplier']]."</td>";
			// }else{
				  $tab.="<td>".$nmorganisasi[$kodeunit]."</td>";
			// }
			
			
			if($hargarealisasi[$kodeunit]!='' || $hargarealisasi[$kodeunit]!=0){
				$hargarealisasi[$kodeunit]=$hargarealisasi[$kodeunit];
			}else{
				$hargarealisasi[$kodeunit]=$awalrealisasi;
			}
			
			if($hargadisbun[$kodeunit]!='' || $hargadisbun[$kodeunit]!=0){
				$hargadisbun[$kodeunit]=$hargadisbun[$kodeunit];
			}else{
				$hargadisbun[$kodeunit]=$awaldisbun;
			}
          
  
            $tab.="<td hidden><input type=text  id=budgetharga".$no." value='".number_format($hargabudget[$kodeunit],2)."' onkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>";
            $tab.="<td><input type=text  id=disbunharga".$no." value='".number_format($hargadisbun[$kodeunit],2)."' onkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>";
            $tab.="<td><input type=text  id=harga".$no." value='".number_format($hargarealisasi[$kodeunit],2)."' onkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>";
            // $tab.="<td><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editmaster('".$bar['kodeunit']."','".$kodeunit."','".$bar['aktif']."');\"></td>";
           
				

            $tab.="</tr>";
        // }
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=6 align=center><button class=mybutton onclick=simpanharga(".$no.");>".$_SESSION['lang']['proses']."</button></td>";
		$tab.="</tr>";
		echo $tab;
	break;
	
	case'loaddataharga':
	
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$where="";
		
		
		if($kodeunitcari!=''){ 
			$where.=" and kodeorg   ='".$kodeunitcari."'";
		}
		
		if($tanggalcari=='--'){
			$tanggalcari='';
		}
		
		if($tanggalcari!=''){ 
			$where.=" and tanggal like '%".$tanggalcari."%'";
		}
		
		if($tahuntanamcari!=''){ 
			$where.=" and tahuntanam   ='".$tahuntanamcari."'";
		}
		
		if($tipehargacari!=''){ 
			$where.=" and tipe   ='".$tipehargacari."'";
		}
		
		$ql2 = "select count(distinct(notransaksi)) as jmlhrow from ".$dbname.".pmn_hargabelitbs
				where 0=0 ".$where.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['tahuntanam']." / ".$_SESSION['lang']['grade']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center colspan=4>".$_SESSION['lang']['view']."</th>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$no = $maxdisplay;
		
		
		
		$str = "select * from ".$dbname.".pmn_hargabelitbs where 0=0 ".$where." group by notransaksi order by tanggal desc,tahuntanam desc LIMIT ".$offset.",".$limit."";
		$res=fetchdata($str);
		foreach($res as $bar){
		// $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
        // while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['notransaksi']."</td>";
				$tab.="<td>".$bar['kodeorg']."</td>";
				$tab.="<td>".$nmorganisasi[$bar['kodeorg']]."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])." ".substr($bar['tanggal'],11,8)." s/d ".tanggalnormal($bar['tanggal2'])." ".substr($bar['tanggal2'],11,8)."</td>";
				$tab.="<td align=right>".$bar['tahuntanam']."</td>";
				$tab.="<td align=right>".getNamaKaryawan($bar['updateby'])."</td>";
				
				$tab.="<td align=center width=25px>";
				$tab.="<img src='images/skyblue/zoom.png' class='zImgBtn' title='Add Detail ".$bar['notransaksi']."' onclick=\"viewhargatbs('".$bar['notransaksi']."')\"></td>";
					
				if($bar['posting']==0){
					$tab.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='delete' onclick=\"hapushargatbs('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['tahuntanam']."','".tanggalnormal($bar['tanggal'])."','".tanggalnormal($bar['tanggal2'])."')\" ></td>";
					$tab.="<td align=center width=25px>";
					$tab.="<img src=images/icons/04/16/01.png class=zImgBtn  title='Posting' caption='Posting Harga' onclick=\"postinght('".$bar['notransaksi']."');\">";
					$tab.="</td>";
					// $tab.="<td align=center><label style='color:blue;cursor:pointer;'  onclick=\"posting('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['tahuntanam']."','".$bar['tanggal']."','".$bar['tanggal2']."','".$bar['tipe']."')\" title='Posting Ajukan Persetujuan'>Ajukan</label></td>";
				}else{
					$tab.="<td align=center></td>";
					$tab.="<td align=center colspan=2><label style='color:green;' title='Disetujui ".$bar['notransaksi']."'>Disetujui</label></td>";           
				}
				$tab.="</td>";	
				$tab.="</tr>";
		}
		
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0) {
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++) {
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=9 align=center>";
		$tab.="<button class=mybutton onclick=loaddataharga(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPageharga(this.value)\">".$isiRow."</select>";
		
		$tab.="<button class=mybutton onclick=loaddataharga(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;
	
	
	#= indra
	case'viewhargatbsperunit':
		$param = $_POST;
		#= tbs kud
		$expltanggal1=explode('-',$tanggal);
		$tahun=$expltanggal1[0];
		$arrthntnm=array();
		$str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and tanggal='".$param['tanggal']."' and tipe='".$tipe."' order by tanggal asc";
		// echo $str;
		// $str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and 
		// tanggal='".$tanggal."' and supplierid not in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN') 
		// order by tanggal asc";
		
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
			$hargadisbun[$bar['tahuntanam']]=$bar['hargadisbun'];
			$hargarealisasi[$bar['tahuntanam']]=$bar['harga'];
		}
		
		$str="select max(harga) as harga from ".$dbname.".pmn_hargabelitbs where 
		kodeorg='".$unit."' and tanggal='".$tanggal1."' order by tanggal asc";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hargamax=$bar['harga'];
		
		// echo"<pre>";
		// if(count($arrthntnm)<1){
			// exit("Warning:Data Kosong");
		// }
		$cthntnm=count($arrthntnm);
		array_multisort($arrthntnm,SORT_ASC);
		
		// $stream.="<br><br>TBS KUD";
		$stream.="<table class=sortable border=0 cellspacing=1 style:width='200%'><tbody>";
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['tahuntanam']." / ".$_SESSION['lang']['grade']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['harga']." Disbun</td>";
			$stream.="<td align=center>".$_SESSION['lang']['harga']." Realisasi</td>";
		$stream.="</tr>";
		$stream.="</thead>";
			foreach($arrthntnm as $thntnm){
				@$no+=1;
				$stream.="<tr class=rowcontent>";
						$stream.="<td align=center>".$no."</td>";
						$stream.="<td align=center>".$thntnm."</td>";
						// if(strlen($thntnm)<4){
						// 	$stream.="<td align=center></td>";
						// }else{
						// 	$stream.="<td align=center>".($tahun-$thntnm)."</td>";
						// }
						$stream.="<td align=right>".number_format($hargadisbun[$thntnm],2)."</td>";
						if($hargarealisasi[$thntnm]==$hargamax){
							$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$thntnm],2)."</font></td>";
						}else{
							$stream.="<td align=right>".number_format($hargarealisasi[$thntnm],2)."</td>";
						}
						
				$stream.="</tr>"; 
		}
		$stream.="</table>"; 
		
		
		
		
		
		echo $stream;
	
	break;
	
	/*
	case'viewhargatbsperunit':
	
	
		
		$expltanggal1=explode('-',$tanggal);
		$tahun=$expltanggal1[0];
		$arrthntnm=array();
		// $str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and tanggal='".$tanggal."' order by tanggal asc";
		$str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and 
		tanggal='".$tanggal."' and supplierid in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN') 
		order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
			$hargadisbun[$bar['tahuntanam']]=$bar['hargadisbun'];
			$hargarealisasi[$bar['tahuntanam']]=$bar['harga'];
		}
		
		$str="select max(harga) as harga from ".$dbname.".pmn_hargabelitbs where 
		kodeorg='".$unit."' and tanggal='".$tanggal1."' order by tanggal asc";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hargamax=$bar['harga'];
		
		// echo"<pre>";
		// if(count($arrthntnm)<1){
			// exit("Warning:Data Kosong");
		// }
		$cthntnm=count($arrthntnm);
		array_multisort($arrthntnm,SORT_ASC);
		$stream.="TBS Inti";
		$stream.="<table class=sortable border=0 cellspacing=1 style:width='200%'><tbody>";
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['umur']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['harga']." Disbun</td>";
			$stream.="<td align=center>".$_SESSION['lang']['harga']." Realisasi</td>";
		$stream.="</tr>";
		$stream.="</thead>";
			foreach($arrthntnm as $thntnm){
				@$no+=1;
				$stream.="<tr class=rowcontent>";
						$stream.="<td align=center>".$no."</td>";
						$stream.="<td align=center>".$thntnm."</td>";
						$stream.="<td align=center>".($tahun-$thntnm)."</td>";
						$stream.="<td align=right>".number_format($hargadisbun[$thntnm],2)."</td>";
						if($hargarealisasi[$thntnm]==$hargamax){
							$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$thntnm],2)."</font></td>";
						}else{
							$stream.="<td align=right>".number_format($hargarealisasi[$thntnm],2)."</td>";
						}
						
				$stream.="</tr>"; 
		}
		$stream.="</table>"; 
		
		
		
		
		
		
		#= tbs kud
		$expltanggal1=explode('-',$tanggal);
		$tahun=$expltanggal1[0];
		$arrthntnm=array();
		// $str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and tanggal='".$tanggal."' order by tanggal asc";
		
		$str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and 
		tanggal='".$tanggal."' and supplierid not in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN') 
		order by tanggal asc";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
			$hargadisbun[$bar['tahuntanam']]=$bar['hargadisbun'];
			$hargarealisasi[$bar['tahuntanam']]=$bar['harga'];
		}
		
		$str="select max(harga) as harga from ".$dbname.".pmn_hargabelitbs where 
		kodeorg='".$unit."' and tanggal='".$tanggal1."' order by tanggal asc";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hargamax=$bar['harga'];
		
		// echo"<pre>";
		// if(count($arrthntnm)<1){
			// exit("Warning:Data Kosong");
		// }
		$cthntnm=count($arrthntnm);
		array_multisort($arrthntnm,SORT_ASC);
		
		$stream.="<br><br>TBS KUD";
		$stream.="<table class=sortable border=0 cellspacing=1 style:width='200%'><tbody>";
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['umur']."</td>";
			$stream.="<td align=center>".$_SESSION['lang']['harga']." Disbun</td>";
			$stream.="<td align=center>".$_SESSION['lang']['harga']." Realisasi</td>";
		$stream.="</tr>";
		$stream.="</thead>";
			foreach($arrthntnm as $thntnm){
				@$no+=1;
				$stream.="<tr class=rowcontent>";
						$stream.="<td align=center>".$no."</td>";
						$stream.="<td align=center>".$thntnm."</td>";
						$stream.="<td align=center>".($tahun-$thntnm)."</td>";
						$stream.="<td align=right>".number_format($hargadisbun[$thntnm],2)."</td>";
						if($hargarealisasi[$thntnm]==$hargamax){
							$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$thntnm],2)."</font></td>";
						}else{
							$stream.="<td align=right>".number_format($hargarealisasi[$thntnm],2)."</td>";
						}
						
				$stream.="</tr>"; 
		}
		$stream.="</table>"; 
		
		
		
		
		
		echo $stream;
	
	break;
	*/
	
	case'viewhargatbs':
		$tab="";
		$tab.="<label>".$_SESSION['lang']['persetujuan']."</label>";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead><tr>
                <th>".$_SESSION['lang']['level']."</th>
                <th>".$_SESSION['lang']['namakaryawan']."</th>
                <th>".$_SESSION['lang']['status']."</th>
                <th>".$_SESSION['lang']['keterangan']."</th>
            </tr></thead>";
		#= isi data level	karyawanid	status	komentar
		$str = "select * from ".$dbname.".approval where notransaksi='".$notransaksi."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$nmkaryawan[$bar['karyawanid']]."</td>";
            $tab.="<td>".$optposting[$bar['status']]."</td>";
            $tab.="<td>".$bar['komentar']."</td>";
		
            $tab.="</tr>";
        }
		
		$tab.="</table>";	
		// $tab.="</fieldset>";
	
		$no=0;
		$tab.="<br><label>".$_SESSION['lang']['list']."</label>";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead><tr>
                <th>".$_SESSION['lang']['nourut']."</th>
                <th>".$_SESSION['lang']['kodesupplier']."</th>
                <th>".$_SESSION['lang']['namasupplier']."</th>
                <th>".$_SESSION['lang']['harga']." Disbun</th>
                <th>".$_SESSION['lang']['harga']." Realisasi</th>
            </tr></thead>";
		#= isi data
		$str = "select * from ".$dbname.".pmn_hargabelitbs where notransaksi='".$notransaksi."' ORDER BY LENGTH(supplierid) asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['supplierid']."</td>";
			if(strlen($bar['supplierid'])>6){
				  $tab.="<td>".$nmsupplier[$bar['supplierid']]."</td>";
			}else{
				  $tab.="<td>".$nmorganisasi[$bar['supplierid']]."</td>";
			}
            $tab.="<td align=right>".number_format($bar['hargadisbun'],2)."</td>";
            $tab.="<td align=right>".number_format($bar['harga'],2)."</td>";
           
            $tab.="</tr>";
        }
		
		$tab.="</table>";	
		// $tab.="</fieldset>";
		
		echo $tab;
		
	break;
	
	
	case'showform':
		
		$countApp = getCountApproval('HBT',$kodeunit);
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
			for($i=1;$i<=$countApp;$i++){
				$arrList = listApprove($i,'HBT',$kodeunit);
				$optpersetujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$arrDetail = detailApprove($i,$param['notransaksi'],'HBT');
				foreach($arrList as $key=>$val){
					$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
				}
                //if($param['tipetransaksi']!='M'){
                    $tab.="<tr  class=rowcontent>
                    <td>Approval ".$i."</td> 
                    <td>:</td>
                    <td colspan=1><select style=\"width:154px;\" id=persetujuan".$i.">".$optpersetujuan."</select></td>
                    </tr>";    
                //}
				
			}   
			$tab.="
			
			<tr class=rowcontent>
				<td colspan=3 style='text-align:center'>
					<button class=mybutton onclick=\"saveposting('".$notransaksi."','".$kodeunit."','".$tahuntanam."','".$tanggaljam."','".$tanggaljam2."','".$countApp."','".$tipe."')\">".$_SESSION['lang']['save']."</button>
					
				</td>
			</tr>
		</table>
		";
		
		//<tr class=rowcontent><td></td><td></td><td><button class=mybutton onclick=savePosting('".$param['notransaksi']."','".$param['kodeorg']."','".$param['noakun']."','".$param['tipetransaksi']."','".$param['numRow']."','".$countApp."')>Simpan</button></td></tr>
		
				
		echo $tab;
	
	break;
	
	
	
	case'persetujuan':
	
		$param = $_POST;
		
		$notransaksi='';
		$arrnotransaksi=array();
		#= ambil notransaksi
		$str = "select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$kodeunit."' and tanggal='".$tanggaljam."' and tanggal2='".$tanggaljam2."' and posting=0 and tipe='".$tipe."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnotransaksi[$bar['notransaksi']]=$bar['notransaksi'];
		}
		
		foreach($arrnotransaksi as $notransaksi){
	
			#= delete 1st untuk aprovalnya
			$str = "delete from " . $dbname . ".approval where notransaksi='".$notransaksi."' and jenispersetujuan='HBT'";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				
			}
			
			$str = "update " . $dbname . ".pmn_hargabelitbs set posting=9  where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($str); 
			}catch(PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
			
			for($i=1;$i<=$maxaproval;$i++){
				#= insert
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
					   values('".$notransaksi."','HBT','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";
					   // exit("Error:$str");
				try{
					$owlPDO->exec($str); 
				}catch(PDOException $e){
					$str = "update " . $dbname . ".pmn_hargabelitbs set posting=0 where notransaksi='".$notransaksi."'";
					try{
						$owlPDO->exec($str); 
					}catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
		}
	
	break;
	
	
	case 'insertmaster':
	
		$str = "insert into  ".$dbname.".pmn_5hargabelitbs (`kodeunit`, `tipe`, `supplier`, `createdby`, `createtime`, `updateby`, `aktif`) 
		values ('".$kodeunit."','".$tipesupplier[$supplier]."','".$supplier."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','".$aktif."')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'updatemaster':
	
		$str = "update ".$dbname.".pmn_5hargabelitbs set aktif='".$aktif."',updateby='" . $_SESSION['standard']['userid'] . "' where kodeunit = '".$kodeunit."' and supplier = '".$supplier."'";
		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;

   case'loaddatamaster':
	
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$where="";
		if($kodeunitcari!=''){ 
			$where.=" and kodeunit   ='".$kodeunitcari."'";
		}
		if($tipecari!=''){ 
			$where.=" and tipe   ='".$tipecari."'";
		}
		
		$ql2 = "select count(*) as jmlhrow from ".$dbname.".pmn_5hargabelitbs where 0=0 ".$where; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$tab="<br><table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['kodesupplier']."</th>
				<th align=center>".$_SESSION['lang']['namasupplier']."</th> 
				<th align=center>".$_SESSION['lang']['aktif']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$no = $maxdisplay;
		$str = "select * from ".$dbname.".pmn_5hargabelitbs where 0=0 ".$where." LIMIT ".$offset.",".$limit."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['kodeunit']."</td>";
            $tab.="<td>".$bar['supplier']."</td>";
			
			if(strlen($bar['supplier'])>6){
				  $tab.="<td>".$nmsupplier[$bar['supplier']]."</td>";
			}else{
				  $tab.="<td>".$nmorganisasi[$bar['supplier']]."</td>";
			}
			
          
            // $tab.="<td>".$bar['tipe']."</td>";
            $tab.="<td align=center>".$arraktif[$bar['aktif']]."</td>";
            $tab.="<td>".getNamaKaryawan($bar['updateby'])."</td>";
            $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editmaster('".$bar['kodeunit']."','".$bar['supplier']."','".$bar['aktif']."');\"></td>";
           
				

            $tab.="</tr>";
        }
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0)
		{
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++)
		{
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=7 align=center>";
		$tab.="<button class=mybutton onclick=loaddatamaster(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pagesmaster\" name=\"pagesmaster\" onchange=\"getPagemaster(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddatamaster(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
		break;

    default:
}
function insertcsv($notransaksi){
    global $dbname;
    global $owlPDO;
    $pemisah=',';
    $path='tempExcel';
	

    $dir=$path;
    $ext=explode('.', basename( $_FILES['file']['name']));
    $ext=$ext[count($ext)-1];
    $ext=strtolower($ext);
    
    if($ext=='csv'){
        $path = $dir."/".date('ymd').".".$ext;
        unlink($path);
        try{
            if(move_uploaded_file($_FILES['file']['tmp_name'], $path)){
                $x=readCSV($path,$pemisah);
                $jmlhRow=count($x);
                for($row=1;$row<$jmlhRow;$row++){

					// echo"<pre>";
					// print_r(trim($x[$row][0]));
					// echo"</pre>";
					// exit();012/FB/RWKM/SC/I/2022 012/FB/RWKM/SC/I/2022
					//echo $x[$row][0];
                    if (trim($x[$row][0]) != $notransaksi) {
                        throw new PDOException("Nokontrak tidak sesuai dengan transaksi!!");
                    }

                    // if (trim($x[$row][5]) != $nodo) {
                    //     throw new PDOException("Nodo tidak sesuai dengan transaksi!!");
                    // }
					@$optsup=makeOption($dbname,'pmn_kontrakbeli','notransaksi,kodesupplier',"notransaksi='".trim($x[$row][0])."'");
					@$optunit=makeOption($dbname,'pmn_kontrakbeli','notransaksi,unit',"notransaksi='".trim($x[$row][0])."'");
					//print_r($optsup);
					// exit("Error".$optunit[trim($param['notransaksi'])]);
					// $data = array(
					// 	'notransaksi'  =>trim($x[$row][0]),
					// 	'tanggaldari'  =>tanggalsystemn(trim($x[$row][1])),
					// 	'tanggalsampai'=>tanggalsystemn(trim($x[$row][2])),
					// 	'kodeklsbuah'  =>trim($x[$row][3]),
					// 	'unit'      =>@$optunit[trim($x[$row][4])],
					// 	'kodesupplier'   =>@$optsup[trim($x[$row][5])],
					// 	'ppn'          =>trim($x[$row][6]),
					// 	'harga'        =>trim($x[$row][7]),
					// 	'tahuntanam'   =>trim($x[$row][8]),
					// 	'hargabrondolan'   =>trim($x[$row][9]),
					// 	'createby'     =>$_SESSION['standard']['userid'],
					// 	'createtime'   =>date("Y-m-d H:i:s"),
					// 	'updateby'     =>$_SESSION['standard']['userid'],
					// 	'updatetime'   =>date("Y-m-d H:i:s")
					// );
					 
					
					// $owlPDO->commit();

					$del = "DELETE FROM ".$dbname.".pmn_hargabelitbs 
							WHERE notransaksi='".trim($x[$row][0])."' AND tanggaldari='".tanggalsystemn(trim($x[$row][1]))."'
							AND tanggalsampai='".tanggalsystemn(trim($x[$row][2]))."' AND unit='".@$optunit[trim($x[$row][0])]."'
							AND kodesupplier='".@$optsup[trim($x[$row][0])]."' AND tahuntanam='".trim($x[$row][8])."'";
					$owlPDO->exec($del);

                    $ha = "insert into ".$dbname.".pmn_hargabelitbs 
                    (`notransaksi`,
					`tanggaldari`,
					`tanggalsampai`,
					`kodeklsbuah`,
					`unit`,
					`kodesupplier`,
					`ppn`,
					`harga`,
					`tahuntanam`,
					`hargabrondolan`,
					`createby`,
					`createtime`,
					`updateby`,
					`updatetime`) VALUES
                    ('".trim($x[$row][0])."','".tanggalsystemn(trim($x[$row][1]))."','".tanggalsystemn(trim($x[$row][2]))."','".trim($x[$row][3])."','".@$optunit[trim($x[$row][0])]."','".@$optsup[trim($x[$row][0])]."','".trim($x[$row][6])."','".trim($x[$row][7])."','".trim($x[$row][8])."','".trim($x[$row][9])."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
                    //echo $ha;
                    //exit('Error');
                    try{
                      $owlPDO->exec($ha);      
                    }
                    catch (PDOException $e)
                    {
                      print " Gagal  !: " . $e->getMessage() . "<br/>";
                      die();
                    }
                } 
            }
        }catch(Exception $e){
            echo " Gagal, " . addslashes($e->getMessage());
        }
    }else{
   		exit("Error : Mohon upload file tipe CSV");
        echo " Gagal, " . addslashes($e->getMessage());         
    }

}
?>