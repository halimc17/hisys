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
	    $str="update ".$dbname.".sdm_5periodegaji_kecil set sudahproses='1' where kodeorg='".$kodeorg."' and periode='".$periode."'";
	    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
    break;
    case'buka':	

        $str="select periode from ".$dbname.".setup_periodeakuntansi where periode = '".$periode."' and kodeorg = '".$kodeorg."' and tutupbuku = 1";
        $res=fetchdata($str);
        $periodeaku = $res[0]['periode'];

        if($periodeaku != ''){
            exit("Warning : Periode akuntansi sudah ditutup silahkan buka periode akuntansi terlebih dahulu untuk membuka periode gaji" );
        }

        $str="update ".$dbname.".sdm_5periodegaji_kecil set sudahproses='0' where kodeorg='".$kodeorg."' and periode='".$periode."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	
    break;
    case 'update':	
		if($tanggalsampai<$tanggalmulai){
			exit("Error : Tanggal sampai tidak boleh lebih kecil dari tanggal mulai.");
		}
        $str="update ".$dbname.".sdm_5periodegaji_kecil set tanggalmulai=".$tanggalmulai.",tanggalsampai=".$tanggalsampai." where kodeorg='".$kodeorg."' and periode='".$periode."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;

    case 'insert':
		if($tanggalsampai<$tanggalmulai){
			exit("Error : Tanggal sampai tidak boleh lebih kecil dari tanggal mulai.");
		}
        $str="insert into ".$dbname.".sdm_5periodegaji_kecil 
              (kodeorg,periode,tanggalmulai,tanggalsampai)
              values('".$kodeorg."','".$periode."',".$tanggalmulai.",".$tanggalsampai.")";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }	
    break;

    case'loaddata':

        $str1 = "select * from " . $dbname . ".sdm_5periodegaji_kecil where 1=1 ".$whereunitlist." order by periode asc";                        
        $res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res->fetch()){
            echo"<tr class=rowcontent>
                <td align=center>".$bar1->kodeorg."</td>
                <td align=center>".$bar1->periode."</td>
                <td align=center>".$bar1->tanggalmulai."</td>
                <td align=center>".$bar1->tanggalsampai."</td>";

            if($bar1->sudahproses=='0'){
                $text = "TIDAK";
            }else{
                $text = "YA";
            }

            echo"<td align=center>".$text."</td>";

            if($bar1->sudahproses=='0'){		
                echo"<td align=center><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$bar1->periode."','".tanggalnormal($bar1->tanggalmulai)."','".tanggalnormal($bar1->tanggalsampai)."');\"></td>";
                echo"<td align=center><img src=images/skyblue/posting.png class=zImgBtn height=30 title=Close ??? onclick=tutup('".$bar1->kodeorg."','".$bar1->periode."')></td>";
            }else{
                echo"<td></td><td align=center><img src=images/icons/04/16/04.png class=zImgBtn height=30 title=Closed onclick=buka('".$bar1->kodeorg."','".$bar1->periode."')></td>";
            }	
            
            echo"</tr>";
        }
    break;

    default:
    break;					
}
?>
