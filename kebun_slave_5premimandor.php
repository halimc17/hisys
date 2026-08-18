<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/zLib.php');

$method 			= checkPostGet('method','');
$kodept 			= checkPostGet('kodept','');
$kodeunit 			= checkPostGet('kodeunit','');
$jenis 				= checkPostGet('jenis','');
$minimalpembagi 	= checkPostGet('minimalpembagi','');
$nilaipengali 		= checkPostGet('nilaipengali','');
$kodepremi 			= checkPostGet('kodepremi','');
$find_jenis 		= checkPostGet('find_jenis','');
$find_unit 			= checkPostGet('find_unit','');
$find_status 		= checkPostGet('find_status','');

// exit('warning'.$method);

switch($method){
	case 'getunit':
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING' ) {
			$str = "select * from ".$dbname.".organisasi where induk = '".$kodept."' and tipe = 'KEBUN'";
		} else {
			$str = "select * from ".$dbname.".organisasi where induk = '".$kodept."' and tipe = 'KEBUN' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
		}

		$str = "select * from ".$dbname.".organisasi where induk = '".$kodept."' and tipe = 'KEBUN'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optPilihunit="<option value='' hidden>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($res as $bar) {
			$optPilihunit.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}

		echo $optPilihunit;
	break;

	case 'getedit':
		// $str = "select * from owl_agrina.kebun_5premimandor where kodepremi ='".$kodepremi."'";
		// $str="select * from ".$dbname.".kebun_5premimandor where kodepremi ='' ";
		$str = "select * from " . $dbname . ".kebun_5premimandor where kodepremi ='".$kodepremi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();

		$str1 = "select * from ".$dbname.".organisasi where induk = '".$bar['kodept']."' and tipe = 'KEBUN'";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		$optPilihunit="<option value='' hidden>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($res1 as $bar1) {
			if ($bar['kodeorg'] == $bar1['kodeorganisasi']) {
				$optPilihunit.="<option value=" . $bar1['kodeorganisasi'] . " selected>" . $bar1['kodeorganisasi'] . " - " . $bar1['namaorganisasi'] . "</option>";
			} else { 
				$optPilihunit.="<option value=" . $bar1['kodeorganisasi'] . ">" . $bar1['kodeorganisasi'] . " - " . $bar1['namaorganisasi'] . "</option>";
			}
		}

		echo $bar['kodept']."###".$optPilihunit."###".$bar['jenis']."###".$bar['maxpic']."###".$bar['minpic']."###".$bar['rpbrondolan']."###".$bar['nilaipembagi']."###".$bar['nilaipengali']."###".$bar['status'];
	break;


	case 'simpandatapremi':
	//cek data berhasil dikirim di tombol simpan
	// echo $kodept.",".$kodeunit.",".$jenis.",".$minimalpemanen.",".$maksimalpemanen.",".$rpbrondolan.",".$nilaipengali.",".$nilaipembagi.",".$status;
	// exit(' error');

		$str = "select * from " . $dbname . ".kebun_5premimandor where kodeorg='".$kodeunit."' and jenis='".$jenis."'";
		$hasil=fetchdata($str);
		$hitung=count($hasil);
		//validasi data redudansi data
		if($hitung > 0){

			exit('error: Data Sudah Ada!');
		}else{
			if ($kodeunit =="" OR $jenis =="" OR $minimalpembagi OR $nilaipengali =="") {
			exit('error : Kode Unit, Jenis, Minimal Pembagi, Nilai Pengali tidak boleh kosong');
			}
		

			$str="insert into ".$dbname.".kebun_5premimandor (`kodeorg`,`jenis`,`minimalpembagi`,`nilaipengali`)
				  values ('".$kodeunit."','".$jenis."','".$minimalpembagi."','".$nilaipengali."')";		
			try{
				$owlPDO->exec($str); 
				}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}		
	break;

	case 'ubahdatapremi':

		$str="update ".$dbname.".kebun_5premimandor set minimalpembagi='".$minimalpembagi."',nilaipengali='".$nilaipengali."' where kodeorg='".$kodeunit."' and jenis='".$jenis."'";		
			//exit('error'.$str);
			try{
				$owlPDO->exec($str); 
				}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;

	case 'loaddata':
	//pagination
	$limit=10;
    $page=0;
    if(isset($_POST['page'])){
		$page=floatval($_POST['page']); if($page<0){$page=0;}
    }
    $offset=$page*$limit;
    $maxdisplay=($page*$limit);

    //hitung jmlh baris list data
    $ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5premimandor";
    $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while($jsl=$query2->fetch()){  
        $jlhbrs= $jsl->jmlhrow;
    }

    $no=$maxdisplay;
    $str="select * from ".$dbname.".kebun_5premimandor limit ".$offset.",".$limit."";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);

    //validasi search button
	$where="";
	if($find_unit!=''){ 
		$where.=" and kodeorg LIKE '%".$find_unit."%'";
	}
	if($find_jenis!=''){ 
		$where.=" and jenis LIKE '%".$find_jenis."%'";
	}

	$str = "select * from " . $dbname . ".kebun_5premimandor where 1=1 ".$where." order by jenis asc, minimalpembagi asc limit ".$offset.",".$limit."";	
	$q = fetchdata($str);
	$hasil = count($q);
	$show = '';

	//validasi tdk ada data
	if ($hasil < 1) {
		$show .= "
			<tr class=rowcontent>
				<td style='text-align:center' colspan='11'>Tidak Ada Data</td>
			</tr>
		";
	} else {
		foreach ($q as $bar) {
			$no+=1;
			$namapt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
			$show .="
			<tr class=rowcontent>
				<td style='text-align:center'>".$no.".</td>
				<td>".$bar['kodeorg']." - ".$namapt[$bar['kodeorg']]."</td>
				<td>".$bar['jenis']."</td>
				<td style='text-align:right'>".$bar['minimalpembagi']."</td>
				<td style='text-align:right'>".$bar['nilaipengali']."</td>
				<td style='text-align:center'>
				<img src=images/application/application_edit.png class=resicon title='Edit'			onclick=editlistpremi('".$bar['kodeorg']."','".$bar['jenis']."','".$bar['minimalpembagi']."','".$bar['nilaipengali']."')></td>
			</tr>
			";
		}
			$show .="
            <tr class=rowheader><td colspan=11 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
	}

	echo $show;
	break;

	// case 'dellistpremi':
	// 	$str="delete from ".$dbname.".kebun_5premimandor where kodept='".$kodept."' and kodeorg='".$kodeunit."' and jenis='".$jenis."' and minpic='".$minimalpemanen."' and maxpic='".$maksimalpemanen."' and rpbrondolan='".$rpbrondolan."' and status='".$status."'";
	// 	// echo $str;
	// 	// exit('error:$str');
	// 		try{
	// 			$owlPDO->exec($str);
	// 			}
	// 		catch (PDOException $e) {
	// 			print " Gagal  !: " . $e->getMessage() . "\n"; 
	// 			die(); 
	// 		}
	// break;


	default:
	break;					
}

?>
