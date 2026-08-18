<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi = checkPostGet('notransaksi', '');
$tanggal = tanggalsystem(checkPostGet('tanggal', ''));
$jenisby = checkPostGet('jenisby', '');
$keterangan = checkPostGet('keterangan', '');
$jumlah = checkPostGet('jumlah', '');
$method = checkPostGet('method', '');
$detail = checkPostGet('detail', '');
$tanggalsampai = tanggalsystem(checkPostGet('tanggalsampai', ''));


#periksa uraian hasil perjalanan dinas
$hasilkerja = '';
$str = "select hasilkerja from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $hasilkerja = trim($bar->hasilkerja);
}
// if ($hasilkerja == '') {
    // exit("Warning: Deskripsi pekerjaan masih kosong, harap diisikan dahulu");
	// //exit(" Error: description of the work is null, please be filled first");
// }
if ($jumlah == '')
    $jumlah = 0;

if ($method == 'insert') {
	
	//savenya bukan di file ini
	
	$str="INSERT INTO ".$dbname.".`sdm_pjdinasdt` 
			(`notransaksi`, `jenisbiaya`, `detail`,`keterangan`,`tanggal`,`tanggalsampai`,`jumlah`,`jumlahhrd`,`sumber`)
	values ('".$notransaksi."','".$bykel."','".$bydet."','".$byket."','".$bytgl1."','".$bytgl2."','".$byrp."','".$byrp."','1')";
	
	try{
		$owlPDO->exec($str);
	}
	catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "<br/>"; 
		die(); 
	}
	
	$str = "update " . $dbname . ".sdm_pjdinasht set tglpertanggungjawaban=" . date('Ymd') . "
                      where notransaksi='" . $notransaksi . "'";
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		
	}
	
	/*
	
    $str = "insert into " . $dbname . ".sdm_pjdinasdt (
                  `notransaksi`,`jenisbiaya`,`keterangan`,
                  `tanggal`,`jumlah`
                  ) values(
                                '" . $notransaksi . "'," . $jenisby . ",'" . $keterangan . "',
                                " . $tanggal . "," . $jumlah . " 
                  )";
	try{
		$owlPDO->exec($str);
		$str = "update " . $dbname . ".sdm_pjdinasht set tglpertanggungjawaban=" . date('Ymd') . "
                      where notransaksi='" . $notransaksi . "'";
        try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			
		}
	}catch (PDOException $e){
		echo " Gagal:" . addslashes($e->getMessage());
        exit(0);
	}
	*/
} else if ($method == 'delete') {
	
	$str = "delete from " . $dbname . ".sdm_pjdinasdt
              where notransaksi='" . $notransaksi . "'
                  and tanggal='" . checkPostGet('tanggal', '') . "' and sumber = '1'";

	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo " Gagal:" . addslashes($e->getMessage());
        exit(0);
	}
	/*
    $str = "delete from " . $dbname . ".sdm_pjdinasdt
              where jenisbiaya=" . $jenisby . " and notransaksi='" . $notransaksi . "'
                  and tanggal=" . $tanggal . " and jumlah='" . $jumlah."' and sumber='1' and tanggalsampai='".$tanggalsampai."'
				  and detail='".$detail."'";
				  
				
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo " Gagal:" . addslashes($e->getMessage());
        exit(0);
	}
	
	*/
} else if ($method=='loadum'){

}


$str = "select a.*,b.keterangan as jns from " . $dbname . ".sdm_pjdinasdt a
      left join " . $dbname . ".sdm_5jenisbiayapjdinas b on a.jenisbiaya=b.id
          where a.notransaksi='" . $notransaksi . "' and sumber='1'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
$total = 0;
$totalhrd = 0;
$datasumber1 = array();
while ($bar = $res->fetch()){ 
	$sdm_pjdinasdtsumber1['tanggal'] 		= $bar->tanggal;
	$sdm_pjdinasdtsumber1['jenisbiaya']	    = $bar->jenisbiaya;
	$sdm_pjdinasdtsumber1['jns'] 			= $bar->jns;
	$sdm_pjdinasdtsumber1['detail'] 		= $bar->detail;
	$sdm_pjdinasdtsumber1['flag'] 			= $bar->flag;
	$sdm_pjdinasdtsumber1['jumlah'] 		= $bar->jumlah;
	$sdm_pjdinasdtsumber1['jumlahhrd'] 		= $bar->jumlahhrd;
	$sdm_pjdinasdtsumber1['keterangan'] 	= $bar->keterangan;
	$sdm_pjdinasdtsumber1['notransaksi']	= $bar->notransaksi;
	$datasumber1[] = $sdm_pjdinasdtsumber1; 
}
function getUnique($array,$filt){
	$data = array();
	for($i=0; $i<count($array); $i++){
		$data[] = $array[$i][$filt];	
	}
	$result = array_unique($data);
	return $result;
}

function getFiltering($array,$name,$filt){
	$dataterpilih =array();
	if(count($array) > 0 ){
		for($i=0; $i<count($array); $i++){
			if($array[$i][$name] == $filt){
				$dataterpilih[] = $array[$i];
			}
		}
	}
	$result = $dataterpilih;
	return $result;
}

function isset_num($data,$name){
	$d = $data;
	$num = "false";
	if(isset($d[0][$name])){
		$num = $d[0][$name];
	}
	$result = $num;
	return $result;
}
function if_zero($num){
	$format = "";
	if($num <> "false"){
		$format = number_format($num);
	}
	$result = $format;
	return $result;
}
function must_zero($num){
	$format = 0;
	if($num <> "false"){
		$format = $num;
	}
	$result = $format;
	return $result;
}
function getFirstdata($data,$name){
	$result = "";
	if(isset($data)){
		if(count($data) > 0){
			$result = $data[0][$name];
		}
	}
	return $result;
}
$datatanggal = getUnique($datasumber1,'tanggal');
if(count($datasumber1)>0){
foreach($datatanggal as $tgl){
	$no+=1;
	$datanbytanggal 		= getFiltering($datasumber1,'tanggal',$tgl);
	$UANG_PERJALANAN_DINAS 	= getFiltering($datanbytanggal,'jenisbiaya','1');
	$TRANSPORTASI 			= getFiltering($datanbytanggal,'jenisbiaya','2');
	$PENGINAPAN 			= getFiltering($datanbytanggal,'jenisbiaya','3');
	$LAINNYA 				= getFiltering($datanbytanggal,'jenisbiaya','4');
	$PULSA 					= getFiltering($datanbytanggal,'jenisbiaya','5');
	$UANG_MAKAN				= getFiltering($datanbytanggal,'jenisbiaya','6');
	$LAINPULSA				= 0;
	$LAINPULSA				+= must_zero(isset_num($LAINNYA,'jumlahhrd'));
	$LAINPULSA				+= must_zero(isset_num($PULSA,'jumlahhrd'));
	if($no == 3){
		//exit("error".);
	}
	$flag 					= "";
	$jml					= 0;
	$jml					+= 	must_zero(isset_num($UANG_PERJALANAN_DINAS,'jumlah'));
	$jml				   	+=  must_zero(isset_num($UANG_MAKAN,'jumlah'));
	$jml				   	+=  must_zero(isset_num($TRANSPORTASI,'jumlah'));
	$jml				   	+=  must_zero(isset_num($PENGINAPAN,'jumlah'));
	$jml				   	+=  must_zero(isset_num($LAINNYA,'jumlah'));
	$jml				   	+=  must_zero(isset_num($PULSA,'jumlah'));
	
	$jmlhrd					= 0;
	$jmlhrd					+= 	must_zero(isset_num($UANG_PERJALANAN_DINAS,'jumlahhrd'));
	$jmlhrd					+= 	must_zero(isset_num($UANG_MAKAN,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($TRANSPORTASI,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($PENGINAPAN,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($LAINNYA,'jumlahhrd'));
	$jmlhrd				   	+=  must_zero(isset_num($PULSA,'jumlahhrd'));
	
	$hidden=$titlebaris='';

	echo"<tr class=rowcontent>
                <td>" . $no . "</td>
				<td>" . tanggalnormal($tgl) . "</td>
				<!--<td>" . getFirstdata($UANG_PERJALANAN_DINAS,'jns') . "</td>-->
				<td>" . getFirstdata($datanbytanggal,'detail') . "</td>
				<td align='center'>" . if_zero(isset_num($UANG_PERJALANAN_DINAS,'jumlahhrd')) . "</td>
				<td align='center'>" . if_zero(isset_num($UANG_MAKAN,'jumlahhrd')) . "</td>
				";
	if(isset($PENGINAPAN[0]['flag'])){
		$flag = getFirstdata($PENGINAPAN,'flag');
	}
		if($flag == '1'){ 
		// mess
		echo"		<td></td>
					<td align='center'>" . if_zero(isset_num($PENGINAPAN,'jumlahhrd')) . "</td>";	
	  }elseif($flag == '2'){
		// Hotel
		echo"		<td align='center'>" . if_zero(isset_num($PENGINAPAN,'jumlahhrd')) . "</td>
					<td></td>";					
				}else{
		//nothing
		echo"		<td></td>
					<td align='center'>" . if_zero(isset_num($PENGINAPAN,'jumlahhrd')) . "</td>";
		}
				
	echo"		<td align='center'>" . if_zero(isset_num($TRANSPORTASI,'jumlahhrd')) . "</td>	
				<td align='center'>" . number_format($LAINPULSA) . "</td>";
				
	echo"		<td align='center'>" . number_format($jmlhrd) . "</td>
				<td>" . getFirstdata($datanbytanggal,'keterangan') . "</td>
				<td ".$titlebaris." align=center><img ".$hidden." src='images/close.png' class=resicon onclick=\"deleteDetail('" . $notransaksi . "','" . $tgl. "')\" title='delete'></td>
		</tr>";
	
	
/*	
	$hidden=$titlebaris='';
	if($bar->sumber==0){
		$hidden='hidden';
		$titlebaris='Uang muka yang telah disetujui HRD';
	}
    $no+=1;
    echo"<tr class=rowcontent>
                <td>" . $no . "</td>
                    <td>" . tanggalnormal($bar->tanggal) . "</td>
                    <td>" . $bar->jns . "</td>
						 <td>" . $bar->detail . "</td>
                        <td align=right>" . number_format($bar->jumlah, 2, '.', '.') . "</td>
						 <td align=right>" . number_format($bar->jumlahhrd, 2, '.', '.') . "</td>
						 <td>" . $bar->keterangan . "</td>
						<td ".$titlebaris." align=center><img ".$hidden." src='images/close.png' class=resicon onclick=\"deleteDetail('" . $bar->notransaksi . "','" . $bar->jenisbiaya . "','" . tanggalnormal($bar->tanggal) . "','" . $bar->jumlah . "','" . $bar->detail . "','" . tanggalnormal($bar->tanggalsampai) . "')\" title='delete'></td>
                        </tr>";
    $total+=$bar->jumlah;
	$totalhrd+=$bar->jumlahhrd;
*/	
}
}else{
echo "<tr class='rowcontent'><td colspan='12'>".$_SESSION['lang']['dataempty']."</td></tr>";
}
/*
echo"<tr class=rowcontent>
                <td colspan=4>TOTAL</td>
                        <td align=right>" . number_format($total, 2, '.', '.') . "</td>
						<td align=right>" . number_format($totalhrd, 2, '.', '.') . "</td>
                    <td colspan=2></td>
</tr>";
*/
?>