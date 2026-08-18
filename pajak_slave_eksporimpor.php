<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$unit = checkPostGet('unit','');
$noakun = checkPostGet('noakun','');
$method = checkPostGet('method','');
$npwp = checkPostGet('npwp','');
$tanggal1 = tanggalsystemn(checkPostGet('tanggal1',''));
$tanggal2 = tanggalsystemn(checkPostGet('tanggal2',''));


switch ($method) {
	case 'getnpwp':

	$optnpwp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select distinct npwp from ".$dbname.".keu_fakturpajakht where id in (select distinct id from ".$dbname.".keu_fakturpajakdt where pt='".$unit."')";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())	
	{
		$optnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
	}

	echo $optnpwp;

	break;


	case 'getnoakun':

	$optnoakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str = "select distinct a.jenispph as noakun, b.namaakun from ".$dbname.".keu_penagihanht a
	        left join ".$dbname.".keu_5akun b on a.jenispph = b.noakun 
	        where a.kodept='".$unit."' and (a.jenispph<>'' and a.jenispph<>'false')  and a.noinvoice in (select distinct notransaksi from ".$dbname.".keu_fakturpajakdt a left join ".$dbname.".keu_fakturpajakht b on a.id=b.id where a.pt='".$unit."' and b.npwp='".$npwp."')";
	        //exit('warning'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())	{
		$optnoakun.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
	}

	echo $optnoakun;

	break;


	case 'prosesdata':

	global $conn;
	global $dbname;
	global $owlPDO;

	$strr="select a.*,c.npwp as npwp_pemotong, c.namacustomer as nama_pemotong, c.alamatnpwp as alamat_pemotong
	from ".$dbname.".keu_penagihanht a
	left join pmn_4customer c on a.kodecustomer = c.kodecustomer
	where a.tanggal between '".tanggalsystemn($_POST['tanggal1'])."' and '".tanggalsystemn($_POST['tanggal2'])."' and a.kodept='".$unit."' and a.noinvoice in (select distinct notransaksi from ".$dbname.".keu_fakturpajakdt a left join ".$dbname.".keu_fakturpajakht b on a.id=b.id where a.pt='".$unit."' and b.npwp='".$npwp."') and a.jenispph ='".$noakun."' ";
	//echo $strr;
	$ress=$owlPDO->query($strr) or die(print " Gagal: ".PDOException::getMessage());
	$ress->setFetchMode(PDO::FETCH_ASSOC);
	$no = 0;
	while($barr = $ress->fetch()){
		// if($barr['tgl_cetak'] == '0000-00-00 00:00:00' ){
		// 	$color='';
		// }else{
		// 	$color='cyan';
		// }
		$barr['npwp_pemotong']=str_replace(".","",$barr['npwp_pemotong']);
		$barr['npwp_pemotong']=str_replace("-","",$barr['npwp_pemotong']);
		$akun=makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$barr['jenispph']."'");
		$no++;
		echo"<tr class=rowcontent style=background-color:".$color.";>
		<td style='text-align:right;'>".$no."</td>
		<td align=center>".substr($akun[$barr['jenispph']],-2,2)."</td>
		<td align=center>".$barr['carabayar']."</td>
		<td align=center>".$barr['nobuktipotong']."</td>
		<td align=center>".$barr['jenispenghasilan']."</td>
		
		<td align=right>".number_format($barr['nilaiinvoice'],2)."</td>
		<td align=right>".number_format($barr['pphrupiah'],2)."</td>
		
		<td align=center>".tanggalnormal($barr['tglbuktipotong'])."</td>
		<td align=center>".$barr['npwp_pemotong']."</td>
		<td align=center>".$barr['nama_pemotong']."</td>
		<td align=center>".$barr['alamat_pemotong']."</td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		</tr>";
	}

	break;




	case  'csv' :
	$tglnow=date('y-m-d h:i:s');

	// $str="update ".$dbname.".tax_buktipotongpajak a
	// left join keu_kasbankht b on b.notransaksi = a.notrans_kasbank
	// left join log_5supnpwp c on c.supplierid = a.kodesupplier
	// set a.tgl_cetak ='".$tglnow."'
	// where a.tgl_cetak = '0000-00-00 00:00:00' and b.tanggal between '".tanggalsystemn($_POST['tanggal1'])."' and '".tanggalsystemn($_POST['tanggal2'])."' and a.kodeorg='".$unit."' and a.npwp='".$npwp."' and a.noakun ='".$noakun."'  ";
	// try{
	// 	$owlPDO->exec($str); 
	// }catch (PDOException $e){
	// 	echo "Gagal : ".$e->getMessage();
	// }
	
	break;





	case'printcsv' :
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=eksporimpor-".$unit.".csv");
//Jenis PPh yang dipotong	Cara pembayaran	Nomor bukti potong/pungut	Jenis penghasilan	Objek pemotongan/ pemungutan	PPh yang dipotong/ dipungut	Tgl bukti potong/pungut	NPWP pemotong/ pemungut	Nama pemotong/ pemungut	Alamat pemotong/ pemungut	Kode MAP/ iuran pembayaran	NTPP	Jumlah pembayaran	Tanggal setor


        echo"Jenis PPh yang dipotong,Cara pembayaran,Nomor bukti potong/pungut,Jenis penghasilan,Objek pemotongan/ pemungutan,PPh yang dipotong/ dipungut,Tgl bukti potong,NPWP_PEMOTONG/PEMUNGUT,NAMA_PEMOTONG_ATAU_PEMUNGUT,ALAMAT_PEMOTONG/PEMUNGUT,KODE_MAP/IURAN_PEMBAYARAN,NTPP,JUMLAH_PEMBAYARAN,TANGGAL_SETOR\n";

		$strr="select a.*,c.npwp as npwp_pemotong, c.namacustomer as nama_pemotong, c.alamatnpwp as alamat_pemotong
		from ".$dbname.".keu_penagihanht a
		left join pmn_4customer c on a.kodecustomer = c.kodecustomer
		where a.tanggal between '".$tanggal1."' and '".$tanggal2."' and a.kodept='".$unit."' and a.noinvoice in (select distinct notransaksi from ".$dbname.".keu_fakturpajakdt a left join ".$dbname.".keu_fakturpajakht b on a.id=b.id where a.pt='".$unit."' and b.npwp='".$npwp."') and a.jenispph ='".$noakun."' ";
	    //echo $strr;
	    //exit('warning');
		$ress=$owlPDO->query($strr) or die(print " Gagal: ".PDOException::getMessage());
		$ress->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while($barr = $ress->fetch()){
			$barr['npwp_pemotong']=str_replace(".","",$barr['npwp_pemotong']);
			$barr['npwp_pemotong']=str_replace("-","",$barr['npwp_pemotong']);
			$akun=makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$barr['jenispph']."'");

	        echo"".substr($akun[$barr['jenispph']],-2,2).",".$barr['carabayar'].",".$barr['nobuktipotong'].",".$barr['jenispenghasilan'].",".str_replace(",","",number_format($barr['nilaiinvoice'],0)).",".str_replace(",","",number_format($barr['pphrupiah'],0)).",".str_replace("-","/",tanggalnormal($barr['tglbuktipotong'])).",'".$barr['npwp_pemotong'].",".$barr['nama_pemotong'].",".$barr['alamat_pemotong']." , , , \n";
		}
                
 
	break;

	
	
}

