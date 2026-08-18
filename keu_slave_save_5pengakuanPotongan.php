<?

// require_once('config/connection.php');
// require_once('master_validation.php');
// require_once('lib/nangkoelib.php');
// require_once('lib/zLib.php');
// include_once('lib/zFunction.php');

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
include_once('lib/zFunction.php');

// $potongan=isset($_POST['potongan'])? $_POST['potongan']: '';
// $debet=	isset($_POST['debet'])? $_POST['debet']: '';
// $kredit=	isset($_POST['kredit'])? $_POST['kredit']: '';
// $method=	isset($_POST['method'])? $_POST['method']: '';

$potongan = checkPostGet('potongan','');
$debet = checkPostGet('debet','');
$kredit = checkPostGet('kredit','');
$tipeorganisasi = checkPostGet('tipeorganisasi','');
$method = checkPostGet('method','');

#ambil kamus akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun where length(noakun)=7 order by namaakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $arrAkun[$bar->noakun]=$bar->namaakun;
}
switch($method)
{
case 'update':
        $str="update ".$dbname.".keu_5pengakuanpotongan set 
				noakundebet='".$debet."',
				noakunkredit='".$kredit."',
				updateby='".$_SESSION['standard']['userid']."'
               where idkomponen='".$potongan."' and tipeorganisasi='".$tipeorganisasi."'";
        try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    break;
case 'insert':
            $str="insert into ".$dbname.".keu_5pengakuanpotongan
                  (idkomponen,noakundebet,noakunkredit,tipeorganisasi,updateby)
                  values('".$potongan."','".$debet."','".$kredit."','".$tipeorganisasi."',".$_SESSION['standard']['userid'].")";
            try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
        break;
case 'delete':
	$str="delete from ".$dbname.".keu_5pengakuanpotongan
	 where idkomponen='".$potongan."' and tipeorganisasi='".$tipeorganisasi."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
        case'loadData':
        $str1="select a.*,b.name from ".$dbname.".keu_5pengakuanpotongan a
                   left join ".$dbname.".sdm_ho_component b on a.idkomponen=b.id
                    order by tipeorganisasi,idkomponen";
        $res=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res->fetch()){
            echo"<tr class=rowcontent>
                        <td align=center>".$bar1->tipeorganisasi."</td>
                        <td align=center>".$bar1->idkomponen."</td>
                        <td>".$bar1->name."</td>
                        <td>".$bar1->noakundebet.":".$arrAkun[$bar1->noakundebet]."</td>
                        <td>".$bar1->noakunkredit.":".$arrAkun[$bar1->noakunkredit]."</td>                             
                         <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->idkomponen."','".$bar1->noakundebet."','".$bar1->noakunkredit."','".$bar1->tipeorganisasi."');\"></td>
                         <td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"delField('".$bar1->idkomponen."');\"></td>    
                      </tr>";
            }
        break;
default:
   break;					
}
?>
