<?
// ini_set('display_errors',1);
// error_reporting(1);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg=$_POST['kodeorg'];
$metodepenggajian=$_POST['metodepenggajian'];
$periode=$_POST['periode'];
$tanggalmulai=tanggalsystem($_POST['tanggalmulai']);
$tanggalsampai=tanggalsystem($_POST['tanggalsampai']);
$tutup=$_POST['tutup'];
$natura=$_POST['natura'];
$kg=$_POST['kg'];
$harga=$_POST['harga'];
$method=$_POST['method'];

$kodeorg=checkPostGet('kodeorg','');
$jenisgaji=checkPostGet('jenisgaji','');
$periode=checkPostGet('periode','');

$arr=getOrgDetail(2);
@$whereunitlist=" and kodeorg in (".$arr.")";

$param=$_POST;
if($param['unitcari']!=''){
    $whereunitlist.=" and kodeorg='".$param['unitcari']."'";
}
if($param['periodecari']!=''){
    $whereunitlist.=" and periode='".$param['periodecari']."'";
}
if($param['statcari']!=''){
    $whereunitlist.=" and sudahproses='".$param['statcari']."'";
}

switch($method){
case'tutup':	
	$str="update ".$dbname.".sdm_5periodegaji set sudahproses='1' where kodeorg='".$kodeorg."' and periode='".$periode."' and jenisgaji='".$jenisgaji."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
break;
case'buka':	

    $str="select periode from ".$dbname.".setup_periodeakuntansi where periode = '".$periode."' and kodeorg = '".$kodeorg."' and tutupbuku = 1";
    $res=fetchdata($str);
    $periodeaku = $res[0]['periode'];

    if($periodeaku != ''){
        exit("Warning : Periode akuntansi sudah ditutup silahkan buka periode akuntansi terlebih dahulu untuk membuka periode gaji" );
    }

    ## Karena gak ada HO bisa di unpost dari unit
	// if ($_SESSION['empl']['tipelokasitugas']=='KANWIL' or $_SESSION['empl']['tipelokasitugas']=='HOLDING'){		
		$str="update ".$dbname.".sdm_5periodegaji set sudahproses='0' where kodeorg='".$kodeorg."' and periode='".$periode."' and jenisgaji='".$jenisgaji."'";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	// }else{
	// 	exit("Warning : Untuk melakukan unposting / buka periode gaji silahkan hubungi HR di RO atau HO.");
	// }
break;
case 'update':	
		if($tanggalsampai<$tanggalmulai){
			exit("Error : Tanggal sampai tidak boleh lebih kecil dari tanggal mulai.");
		}
        $str="update ".$dbname.".sdm_5periodegaji set
               tanggalmulai=".$tanggalmulai.",
                   tanggalsampai=".$tanggalsampai.",
                   natura=".$natura.",
                   sudahproses=".$tutup.",kg='".$kg."',harga='".$harga."'
               where kodeorg='".$kodeorg."' and periode='".$periode."'
                   and jenisgaji='".$metodepenggajian."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;
case 'insert':
		if($tanggalsampai<$tanggalmulai){
			exit("Error : Tanggal sampai tidak boleh lebih kecil dari tanggal mulai.");
		}
        $str="insert into ".$dbname.".sdm_5periodegaji 
              (kodeorg,periode,tanggalmulai,tanggalsampai,sudahproses,natura,jenisgaji,kg,harga)
              values('".$kodeorg."','".$periode."',".$tanggalmulai.",".$tanggalsampai.",".$tutup.",".$natura.",'".$metodepenggajian."','".$kg."','".$harga."')";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }	
        break;
case 'delete':
        $str="delete from ".$dbname.".sdm_5periodegaji
              where kodeorg='".$kodeorg."' and periode='".$periode."'
                  and jenisgaji='".$metodepenggajian."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;
case'loaddata':
$str1="select *,
             case jenisgaji when 'H' then '".$_SESSION['lang']['harian']."'
                 when 'B' then '".$_SESSION['lang']['bulanan']."'
                 end as ketgroup, 
                 case sudahproses when '1' then '".$_SESSION['lang']['yes']."'
                 when '0' then '".$_SESSION['lang']['no']."'
                 end as sts, 
                 case natura when '1' then '".$_SESSION['lang']['yes']."'
                 when '0' then '".$_SESSION['lang']['no']."'
                 end as statnatura
             from ".$dbname.".sdm_5periodegaji 
                 where 1=1 ".$whereunitlist."
                 order by periode desc"; 
// echo $str1;
$res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res->fetch()){
	echo"<tr class=rowcontent>
		<td align=center>".$bar1->kodeorg."</td>
		<td>".$bar1->ketgroup."</td>
		<td align=center>".substr(tanggalnormal($bar1->periode),1,7)."</td>
		<td align=center>".tanggalnormal($bar1->tanggalmulai)."</td>
		<td align=center>".tanggalnormal($bar1->tanggalsampai)."</td>
		<td align=right  hidden>".$bar1->kg."</td>
		<td align=right hidden>".number_format($bar1->harga)."</td>
		<td align=center>".$bar1->sts."</td>
		<td align=center hidden>".$bar1->statnatura."</td>";
	if($bar1->sudahproses=='0'){		
		echo"<td align=center><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$bar1->jenisgaji."','".$bar1->periode."','".tanggalnormal($bar1->tanggalmulai)."','".tanggalnormal($bar1->tanggalsampai)."','".$bar1->sudahproses."','".$bar1->kg."','".$bar1->harga."','".$bar1->natura."');\"></td>";
		echo"<td align=center><img src=images/skyblue/posting.png class=zImgBtn height=30 title=Close ??? onclick=tutup('".$bar1->kodeorg."','".$bar1->jenisgaji."','".$bar1->periode."')></td>";
	}else{
		echo"<td></td><td align=center><img src=images/icons/04/16/04.png class=zImgBtn height=30 title=Closed onclick=buka('".$bar1->kodeorg."','".$bar1->jenisgaji."','".$bar1->periode."')></td>";
	}	
	
	echo"</tr>";
}
break;
default:
   break;					
}
?>
