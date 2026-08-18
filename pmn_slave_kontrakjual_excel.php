<?php

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses', '');
$nokontrak = checkPostGet('nokontrak', '');


$str = "select * from " . $dbname . ".pmn_kontrakjual  where nokontrak='" . $nokontrak . "' ";
//echo $str;exit();
//$res=mysql_query($str);
//$bar=mysql_fetch_assoc($res);

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();

$kodePt = $bar['kodept'];
$kdBrg = $bar['kodebarang'];
$tlgKontrk = tanggalnormal($bar['tanggalkontrak']);
$kdCust = $bar['koderekanan'];

//echo $posting; exit();	
//ambil nama pt
$str1 = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $kodePt . "'";
//$res1=mysql_query($str1);
// while($bar1=mysql_fetch_object($res1))
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) {
    $nama = $bar1->namaorganisasi;
    $alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
    $telp = $bar1->telepon;
    $wilKota = $bar1->wilayahkota;
}

$sBrg = "select namabarang,kodebarang from " . $dbname . ".log_5masterbarang where kodebarang='" . $kdBrg . "'";
//$qBrg=mysql_query($sBrg) or die(mysql_error());
//$rBrg=mysql_fetch_assoc($qBrg);
$qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
$rBrg = $qBrg->fetch();
$nmBrg = $rBrg['namabarang'];

$nmdt = explode(".", $nama);

$whrpt = "kodeorg='" . $kodePt . "'";
$almtPt = makeOption($dbname, 'setup_org_npwp', 'kodeorg,alamatdomisili', $whrpt);
$npwpPt = makeOption($dbname, 'setup_org_npwp', 'kodeorg,npwp', $whrpt);


$whrpemb = "kodecustomer='" . $kdCust . "'";
$optNm = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer', $whrpemb);
$optNmAlmt = makeOption($dbname, 'pmn_4customer', 'kodecustomer,alamatnpwp', $whrpemb);
$optNpwp = makeOption($dbname, 'pmn_4customer', 'kodecustomer,npwp', $whrpemb);
$optBrk=makeOption($dbname,'pmn_4customer','kodecustomer,statusberikat',$whrpemb);
$optKtrgBrk=makeOption($dbname,'pmn_4customer','kodecustomer,keteranganberikat',$whrpemb);
$nmdt2 = explode(".", $optNm[$kdCust]);
if (count($nmdt2) == 0) {
    $nmdt2 = $optNm[$kdCust];
}

$whrKomo = "kodecustomer='" . $kdCust . "' and kodebarang='" . $kdBrg . "'";
$optKomo = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');


$whrmt = "kode='" . $bar['matauang'] . "'";
$optMtSim = makeOption($dbname, 'setup_matauang', 'kode,simbol', $whrmt);
$optMtuang = makeOption($dbname, 'setup_matauang', 'kode,matauang', $whrmt);
$arrStatPPn = array(0 => "Exclude", 1 => "Include");


$whrfrn = "id_franco='" . $bar['franco'] . "'";
$optFrnc = makeOption($dbname, 'pmn_5franco', 'id_franco,franco_name', $whrfrn);
$optFrncAlamat = makeOption($dbname, 'pmn_5franco', 'id_franco,alamat', $whrfrn);
$arrX = array('franco' => 'Franco', 'loco' => 'Loco', 'fob' => 'FOB');

$iFranco = " select * from " . $dbname . ".pmn_5franco where id_franco='" . $bar['franco'] . "' ";
//$nFranco=  mysql_query($iFranco) or die (mysql_error($conn));
//$dFranco=  mysql_fetch_assoc($nFranco);
$nFranco = $owlPDO->query($iFranco) or die(print " Gagal: " . PDOException::getMessage());
$nFranco->setFetchMode(PDO::FETCH_ASSOC);
$dFranco = $nFranco->fetch();
$francoList = $arrX[$dFranco['penjualan']] . ' ' . $dFranco['franco_name'] . ' ' . $dFranco['alamat'];

$arrRom = array("0" => "I", "1" => "II", "2" => "III", "3" => "IV");
for ($asd = 3; $asd >= 0; $asd--) {
    if ($asd != 0) {
        if ($bar['kuantitaskirim' . $asd] != 0) {
            $kata[$asd] = "Tahap " . $arrRom[$asd] . " sebanyak " . number_format($bar['kuantitaskirim' . $asd], 0) . " " . $bar['satuan'] . " diserahkan pada tanggal " . tanggalnormal($bar['tanggalkirim' . $asd]) . " s.d " . tanggalnormal($bar['sdtanggal' . $asd]) . "\n";
        }
    } else {
        if (count(@$kata) != 0) {
            $kata[$asd] = "Tahap " . $arrRom[$asd] . " sebanyak " . number_format($bar['kuantitaskirim'], 0) . " " . $bar['satuan'] . " diserahkan pada tanggal " . tanggalnormal($bar['tanggalkirim']) . " s.d " . tanggalnormal($bar['sdtanggal']) . "\n";
        } else {
            $kata[$asd] = "Pengiriman sebanyak " . number_format($bar['kuantitaskirim'], 0) . " " . $bar['satuan'] . " diserahkan pada tanggal " . tanggalnormal($bar['tanggalkirim']) . " s.d " . tanggalnormal($bar['sdtanggal']) . "";
        }
    }
}
$kata[0] = isset($kata[0]) ? $kata[0] : '';
$kata[1] = isset($kata[1]) ? $kata[1] : '';
$kata[2] = isset($kata[2]) ? $kata[2] : '';
$kata[3] = isset($kata[3]) ? $kata[3] : '';

$ffaData = number_format($bar['ffa'], 2) . ' ';
$dobiData = number_format($bar['dobi'], 2) . ' ';
$mdaniData = number_format($bar['mdani'], 2) . ' ';
$moistData = number_format($bar['moist'], 2) . ' ';
$dirtData = number_format($bar['dirt'], 2) . ' ';

$sTrmn = "select distinct * from " . $dbname . ".pmn_5terminbayar where kode='" . $bar['kdtermin'] . "'";
//$qTrmn=mysql_query($sTrmn) or die(mysql_error($conn));
//$rTrmn=mysql_fetch_assoc($qTrmn);
$qTrmn = $owlPDO->query($sTrmn) or die(print " Gagal: " . PDOException::getMessage());
$qTrmn->setFetchMode(PDO::FETCH_ASSOC);
$rTrmn = $qTrmn->fetch();

//$sTrmn2="select distinct namabank,rekening from ".$dbname.".keu_5akunbank where pemilik='".$bar['kodept']."' and noakun='".$bar['rekening']."'";
$sTrmn2 = "select distinct namabank,rekening from " . $dbname . ".keu_5akunbank where pemilik='" . $bar['kodept'] . "'";
//$qTrmn2=mysql_query($sTrmn2) or die(mysql_error($conn));
//$rTrmn2=mysql_fetch_assoc($qTrmn2);
$qTrmn2 = $owlPDO->query($sTrmn2) or die(print " Gagal: " . PDOException::getMessage());
$qTrmn2->setFetchMode(PDO::FETCH_ASSOC);
$rTrmn2 = $qTrmn2->fetch();


$bulan = substr($bar['tglpembayarpertama'], 5, 2);
$nmBulan = numToMonth($bulan, 'I', 'long');

$thn = substr($bar['tglpembayarpertama'], 0, 4);
$tglnya = substr($bar['tglpembayarpertama'], 8, 2);

//  echo $tglnya;
$listTgl = $tglnya . ' ' . $nmBulan . ' ' . $thn;
$nmdt[0] = isset($nmdt[0]) ? $nmdt[0] : '';
$nmdt[1] = isset($nmdt[1]) ? $nmdt[1] : '';
$ktTermin = "" . $rTrmn['satu'] . "% Setelah kontrak ditandatangani selambatnya tanggal " . $listTgl . " <br>" . $rTrmn['dua'] . "% Selambatnya 7 (tujuh) hari setelah BA ditandatangani <br><br>";
$ktTermin.="Pembayaran ditransfer ke :<br>";
$ktTermin.="" . $nmdt[0] . "." . ucwords(strtolower($nmdt[1])) . "<br>";
$ktTermin.=$rTrmn2['namabank'] . "<br>Rek : " . $rTrmn2['rekening'];
$nilKontrak = $bar['hargasatuan'] * $bar['kuantitaskontrak'];


$tglTtd = explode("-", $tlgKontrk);

$tglnya = $tglTtd[0];
$blnnya = numToMonth($tglTtd[1], $lang = 'I', $format = 'long');
$thnnya = $tglTtd[2];

$tglbenernya = $tglnya . ' ' . $blnnya . ' ' . $thnnya;


$nmPt = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmPtS = explode(".", $nmPt[$bar['kodept']]);
$nmPtS[0] = isset($nmPtS[0]) ? $nmPtS[0] : '';
$nmPtS[1] = isset($nmPtS[1]) ? $nmPtS[1] : '';
$nmTtd = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jabatanTtd = makeOption($dbname, 'pmn_5ttd', 'nama,jabatan');
$namaTtdBeli = makeOption($dbname, 'pmn_4customer', 'kodecustomer,penandatangan');
$jabTtdBeli = makeOption($dbname, 'pmn_4customer', 'kodecustomer,jabatan');

$optKd=makeOption($dbname,'pmn_4komoditi','kodebarang,kodekomoditi',"kodebarang = '".$kdBrg."'");

switch ($proses) {

    case'excel':
        $stream = "<table cellspacing='1' border='0'>
        <tr>
            <td  colspan=8 align=center><u><b>".strtoupper($_SESSION['lang']['kontrak']." ".$optKd[$kdBrg])."</b></u></td>
        </tr>
        <tr>   
            <td  colspan=8 align=center><b>No. ".$nokontrak."</b></td>    
        </tr>
		<tr>   
            <td  colspan=8 align=center>&nbsp;</td>    
        </tr>
        <tr>
            <td colspan=2><b>".$_SESSION['lang']['penjual']."</b></td>
            <td>:</td>
            <td colspan=5>" . $nmdt[0] . " " . ucwords(strtolower($nmdt[1])) . "</td>
        </tr>
        <tr>
			<td colspan=2>".$_SESSION['lang']['alamat']."</td>
            <td>:</td>
            <td  colspan=5>" . $almtPt[$kodePt] . "</td>
        </tr>
		<tr>
            <td colspan=2>".$_SESSION['lang']['npwp']." ".$_SESSION['lang']['penjual']."</b></td>
            <td>:</td>
            <td  colspan=5 align=left>" . $npwpPt[$kodePt] . "</td>
        </tr>
		
		<tr>   
            <td  colspan=8 align=center>&nbsp;</td>    
        </tr>
        
		<tr>
            <td colspan=2><b>".$_SESSION['lang']['Pembeli']."</b></td>
            <td>:</td>
            <td  colspan=5>" . $nmdt2[0] . " " . ucwords(strtolower($nmdt2[1])) . "</td>
        </tr>
        <tr>
            <td  colspan=2>".$_SESSION['lang']['alamat']."</td>
			<td>:</td>
            <td  colspan=5>" . $optNmAlmt[$kdCust] . "</td>
        </tr>

        <tr>
            <td colspan=2>".$_SESSION['lang']['npwp']." ".$_SESSION['lang']['Pembeli']."</td>
            <td>:</td>
            <td  colspan=5>" . $optNpwp[$kdCust] . "</td>
        </tr>
		
		<tr>   
            <td  colspan=8 align=center>&nbsp;</td>    
        </tr>

        <tr>
            <td colspan=2><b>".$_SESSION['lang']['komoditi']."</b></td>
            <td>:</td>
            <td  colspan=5><b>".$optKomo[$kdBrg]."</b></td>
        </tr>";
	
	$stream.="
            <tr>
            <td colspan=2 style='vertical-align:top'>".$_SESSION['lang']['kualitas']."</td>
            <td>:</td>";

        if ($ffaData != 0) {
            $stream.="<td>FFA</td>";
            $stream.="<td>:</td>";
            $stream.="<td>" . $ffaData . " % Max</td>";
            $stream.="</tr>";
        }
        if ($dobiData != 0) {
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>Dobi</td>";
            $stream.="<td>:</td>";
            $stream.="<td>" . $dobiData . " Min</td>";
            $stream.="</tr>";
        }
        if ($mdaniData != 0) {
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>M & I</td>";
            $stream.="<td>:</td>";
            $stream.="<td>" . $mdaniData . " % Max</td>";
            $stream.="</tr>";
        }
        if ($moistData != 0) {
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>Moisture</td>";
            $stream.="<td>:</td>";
            $stream.="<td>" . $moistData . " % Max</td>";
            $stream.="</tr>";
        }
        if ($dirtData != 0) {
            $stream.="<tr>";
            $stream.="<td  colspan=3></td>";
            $stream.="<td>Impurities</td>";
            $stream.="<td>:</td>";
            $stream.="<td>" . $dirtData . " % Max</td>";
            $stream.="</tr>";
        }
	
	$stream .="<tr>
            <td colspan=2>".$_SESSION['lang']['kuantitas']."</td>
            <td>:</td>
            <td  colspan=5>".number_format($bar['kuantitaskontrak'], 0)." ".$bar['satuan']."</td>
        </tr>
		
        <tr>
            <td colspan=2>".$_SESSION['lang']['hargasatuan']."</td>
            <td>:</td>
            <td  colspan=5>".$optMtSim[$bar['matauang']]." ".number_format($bar['hargasatuan'], 2)."/".$bar['satuan']." (" . $arrStatPPn[$bar['ppn']] . " Ppn)</td>
        </tr>";
	if($optBrk[$kdCust] == '1'){
		$stream.="<tr>
            <td colspan=3></td>
            <td  colspan=5>(".ucfirst($optKtrgBrk[$kdCust]).")</td>
        </tr>";
	}else{
		$stream.="<tr>
            <td colspan=3></td>
			<td  colspan=5>(" . ucfirst($bar['terbilang']) . " " . $optMtuang[$bar['matauang']] . ")</td>
        </tr>";
	}
		
	$stream.="<tr>
            <td colspan=2>".$_SESSION['lang']['jumlah']."</td>
            <td>:</td>
            <td colspan=5>".$optMtSim[$bar['matauang']]." ".number_format($nilKontrak, 0)."</td>
        </tr>
        <tr>
            <td  colspan=2>".$_SESSION['lang']['terbilang']."</td>
            <td>:</td>
            <td  colspan=5>" . ucfirst(terbilang($nilKontrak, 2)) . " " . $optMtuang[$bar['matauang']] . "</td>
        </tr>";
		
	$iFranco=" select * from ".$dbname.".pmn_5franco where id_franco='".$bar['franco']."' ";
	$nFranco=$owlPDO->query($iFranco) or die(print " Gagal: ".PDOException::getMessage());
	$nFranco->setFetchMode(PDO::FETCH_ASSOC);
	$dFranco=$nFranco->fetch();
	// if(tanggalsystem($tlgKontrk) < '20160331'){
		// $valSyaratPenyerahan = "Exmill";
	// }else{
		$valSyaratPenyerahan = $dFranco['franco_name']." - ".$dFranco['alamat'];
	// }
	$stream.="<tr>
            <td colspan=2>".$_SESSION['lang']['syaratpenyerahn']."</td>
            <td>:</td>
            <td  colspan=5>".$valSyaratPenyerahan."</td>
        </tr>";
	
	$arrX=array('franco'=>'Franco','loco'=>'Loco','fob'=>'FOB');
	$optGetInduk = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$dFranco['asalbarang']."'");
	$optGetNmPT = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$optGetInduk[$dFranco['asalbarang']]."'");
	$optGetAlt = makeOption($dbname,'organisasi','kodeorganisasi,alamat',"kodeorganisasi='".$dFranco['asalbarang']."'");
	$stream.="<tr>
            <td colspan=2>".$_SESSION['lang']['asalbarang']."</td>
            <td>:</td>
            <td  colspan=5>Dari PKS ".$optGetNmPT[$optGetInduk[$dFranco['asalbarang']]]."</td>
        </tr>
		<tr>
            <td colspan=3></td>
            <td  colspan=5>".$optGetAlt[$dFranco['asalbarang']]."</td>
        </tr>
		<tr>
            <td colspan=2>".$_SESSION['lang']['dasartimbangan']."</td>
            <td>:</td>
            <td  colspan=5>".($dFranco['dasartimbangan']=='0' ? "Timbangan Pabrik Penjual" : "Timbangan Pembeli ".$nmdt2[0].".".$nmdt2[1])."</td>
        </tr>";
		
	//Penyerahan
	if($bar['sdtanggal3']=='0000-00-00'){
		if($bar['sdtanggal2']=='0000-00-00'){
			if($bar['sdtanggal1']=='0000-00-00'){
				$tglAkhir = $bar['sdtanggal'];
			}else{
				$tglAkhir = $bar['sdtanggal1'];
			}
		}else{
			$tglAkhir = $bar['sdtanggal2'];
		}
	}else{
		$tglAkhir = $bar['sdtanggal3'];
	}
	$kettgl= tanggalnormal($bar['tanggalkirim'])." s/d ".tanggalnormal($tglAkhir);
	$stream .= "<tr>
		<td colspan=2 style='vertical-align:top;'>".ucfirst($_SESSION['lang']['penyerahan'])."</td>
		<td style='vertical-align:top;'>:</td>
		<td  colspan=5>".$_SESSION['lang']['tanggal']." ".$kettgl."</td>
	</tr>";

	//Pembayaran	
	$sTrmn="select distinct * from ".$dbname.".pmn_5terminbayar where kode='".$bar['kdtermin']."'";
	$qTrmn=$owlPDO->query($sTrmn) or die(print " Gagal: ".PDOException::getMessage());
	$qTrmn->setFetchMode(PDO::FETCH_ASSOC);
	$rTrmn=$qTrmn->fetch();
	
	if($rTrmn['satu']==100){
		$stream .= "<tr>
            <td colspan=2 style='vertical-align:top;'>".ucfirst($_SESSION['lang']['pembayaran'])."</td>
            <td style='vertical-align:top;'>:</td>
            <td  colspan=5>Pelunasan ".$rTrmn['satu']."% ".$bar['ketbayarpelunasan']."</td>
        </tr>";
	}else{
		$stream .= "<tr>
            <td colspan=2 style='vertical-align:top;'>".ucfirst($_SESSION['lang']['pembayaran'])."</td>
            <td style='vertical-align:top;'>:</td>
            <td  colspan=5>DP ".$rTrmn['satu']."% ".$bar['ketbayardp']." <br>Pelunasan ".$rTrmn['dua']."% ".$bar['ketbayarpelunasan']."</td>
        </tr>";
	}
	

	$sTrmn2="select distinct namabank,rekening,atasnama from ".$dbname.".keu_5akunbank where rekening='".$bar['rekening']."'";
	$qTrmn2=$owlPDO->query($sTrmn2) or die(print " Gagal: ".PDOException::getMessage());
	$qTrmn2->setFetchMode(PDO::FETCH_ASSOC);
	$rTrmn2=$qTrmn2->fetch();

	$bulan=substr($bar['tglpembayarpertama'],5,2);
	$nmBulan=numToMonth($bulan,'I','long');
	$thn=substr($bar['tglpembayarpertama'],0,4);
	$tglnya=substr($bar['tglpembayarpertama'],8,2);
	$listTgl=$tglnya.' '.$nmBulan.' '.$thn;
			
	// $ktTermin2.="Pembayaran via transfer dapat dilakukan melalui<br>";
	$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$rTrmn2['namabank']."'");
	$ktTermin2.="".$nmdt[0].".".ucwords(strtolower($nmdt[1]))."<br>";
	$ktTermin2.="Bank : ".$optNamaBank[$rTrmn2['namabank']]."<br>No Rekening : ".$rTrmn2['rekening']."<br>".$_SESSION['lang']['nama']." : ".$rTrmn2['atasnama'];
	
	$stream.="
        <tr>
            <td colspan=2 style='vertical-align:top'></td>
            <td></td>
            <td  colspan=5>Pembayaran via transfer dapat dilakukan melalui</td>
        </tr>
		<tr>
            <td colspan=3></td>
            <td colspan=5 style='font-weight:bold'>".$ktTermin2."</td>
        </tr>";
	
	if($bar['forcemajuere']!='')
	{
		$stream.="
			<tr>
				<td colspan=2 style='vertical-align:top'><b>Force Majuere</b></td>
				<td style='vertical-valign:top;'>:</td>
				<td colspan=5>" . $bar['forcemajuere'] . "</td>
			</tr>";
	}
		
	if($bar['perselisihan']!='')
	{
		$stream.="
			<tr>
				<td colspan=2 style='vertical-align:top'><b>Perselisihan</b></td>
				<td style='vertical-valign:top;'>:</td>
				<td colspan=5>" . $bar['perselisihan'] . "</td>
			</tr>";
	}
    
	if($bar['catatanlain']!='')
	{
		$stream.="
			<tr>
				<td colspan=2 style='vertical-align:top'><b>".$_SESSION['lang']['catatan']."</b></td>
				<td style='vertical-valign:top;'>:</td>
				<td colspan=5>" . $bar['catatanlain'] . "</td>
			</tr>";
	}
        
        $stream.="<tr>
            <td colspan=2><b></b></td>
            <td></td>
            <td colspan=5></td>
        </tr>
         <tr>
            <td colspan=2><b></b></td>
            <td></td>
            <td colspan=5></td>
        </tr>
        
        <tr>
            <td colspan=2></td>
            <td colspan=4 align=left style='font-weight:bold'>" . ucwords(strtolower('Jakarta')) . ", " . $tglbenernya . "</td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=left style='font-weight:bold'>Penjual,</td>
            <td align=right style='font-weight:bold'>Pembeli,</td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=center><b>" . $nmPtS[0] . "." . ucwords(strtolower($nmPtS[1])) . "</td>
            <td colspan=2 align=center><b>" . $nmdt2[0] . "." . ucwords(strtolower($nmdt2[1])) . "</td>
        </tr>";


        for ($i = 1; $i <= 5; $i++) {
            $stream.="<tr><td></td></tr>";
        }



        $stream.="<tr>
            <td colspan=2></td>
            <td colspan=2 align=left></td>
            <td colspan=2 align=left></td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=center><b><u>" . ucwords(strtolower($nmTtd[$bar['penandatangan']])) . "</u></b></td>
            <td colspan=2 align=center><b><u>" . ucwords(strtolower($namaTtdBeli[$bar['koderekanan']])) . "</u></b></td>
        </tr>
        <tr>
            <td colspan=2></td>
            <td colspan=2 align=center>" . ucwords(strtolower($jabatanTtd[$bar['penandatangan']])) . "</td>
            <td colspan=2 align=center>" . ucwords(strtolower($jabTtdBeli[$bar['koderekanan']])) . "</td>
        </tr>


       


        </table>";
        /* <tr>
          <td colspan=2><b></b></td>
          <td>:</td>
          <td colspan=5></td>
          </tr> */


        //exit("Error:$nokontrak");
        $stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "KontrakJual";
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
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
        }
		
		// echo $stream;



        /* if(strlen($stream)>0)
          {
          if ($handle = opendir('tempExcel')) {
          while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") {
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
          } */

        break;
    default:
        break;
}
?>