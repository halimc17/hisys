<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kode=		isset($_POST['kode'])? $_POST['kode']: '';
$keterangan=isset($_POST['keterangan'])? $_POST['keterangan']: '';
$jumlahhk=	isset($_POST['jumlahhk'])? $_POST['jumlahhk']: '';
$group=		isset($_POST['grup'])? $_POST['grup']: '';
$method=	isset($_POST['method'])? $_POST['method']: '';

switch($method)
{
case 'update':
    $sCek="select distinct * from ".$dbname.".keu_5akunbank where namabank like '%".$jumlahhk."%'";
    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
    $qCek->setFetchMode(PDO::FETCH_OBJ);
    $rCek=owlBaris($qCek);
    if($rCek!=0)
    {
        exit("Error:Data Sudah Ada");
    }
	$str="update ".$dbname.".keu_5akunbank set namabank='".$jumlahhk."'
	       where noakun='".$group."'";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	break;
case 'insert':
    $sCek="select distinct * from ".$dbname.".keu_5akunbank where namabank like '%".$jumlahhk."%'";
    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
    $qCek->setFetchMode(PDO::FETCH_OBJ);
    $rCek=owlBaris($qCek);
    if($rCek!=0)
    {
        exit("Error:Data Sudah Ada");
    }
	$str="insert into ".$dbname.".keu_5akunbank
	      (noakun,namabank)
	      values('".$group."','".$jumlahhk."')";
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	break;
case 'delete':
	$str="delete from ".$dbname.".keu_5akunbank
	where namabank='".$jumlahhk."' and noakun='".$group."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
	break;
        case'loadData':
        $str1="select * from ".$dbname.".keu_5akunbank order by namabank";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch())
        {
        echo"<tr class=rowcontent>
                   <td align=center>".$bar1->noakun."</td>
                           <td>".$bar1->namabank."</td>
                           <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->noakun."','".$bar1->namabank."');\"></td></tr>";
        }
        break;
default:
   break;					
}
?>