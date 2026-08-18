<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$oldtahunbudget=checkPostGet('oldtahunbudget','');
$oldkodeorg=checkPostGet('oldkodeorg','');
$tahunbudget=checkPostGet('tahunbudget','');
$kodeorg=checkPostGet('kodeorg','');

$kapspabrik=checkPostGet('kapspabrik','');
$threff=checkPostGet('threff','');
$commfac=checkPostGet('commfac','');

$jamo=checkPostGet('jamo','');
$jamb=checkPostGet('jamb','');
$method=checkPostGet('method','');
switch($method)
{

            case 'insert':
            $oldtahunbudget==''?$oldtahunbudget=$_POST['tahunbudget']:$oldtahunbudget=$_POST['oldtahunbudget'];
            $oldkodeorg==''?$oldkodeorg=$_POST['kodeorg']:$oldkodeorg=$_POST['oldkodeorg'];

            if(strlen($tahunbudget)<4)
            {
                    exit("Error:tahun budget belum sesuai");
            }	
            $sRicek="select * from ".$dbname.".bgt_jam_operasioal_pks where tahunbudget='".$oldtahunbudget."' and millcode='".$oldkodeorg."' ";
            $qRicek=$owlPDO->query($sRicek) or die(print " Gagal: ".PDOException::getMessage());
            $qRicek->setFetchMode(PDO::FETCH_OBJ);
            $numrows=owlBaris($qRicek);
            $rRicek=$numrows;

            if($rRicek>0)
            {
                    $sDel="delete from ".$dbname.".bgt_jam_operasioal_pks
                                    where tahunbudget='".$oldtahunbudget."' and millcode='".$oldkodeorg."'  ";
                    try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                    
                    $sDel2="insert into ".$dbname.".bgt_jam_operasioal_pks (`tahunbudget`,`millcode`,`jamolah`,`breakdown`,`kapasitas`,`througputeffeciency`,`commercialfactor`)
                    values ('".$tahunbudget."','".$kodeorg."','".$jamo."','".$jamb."','".$kapspabrik."','".$threff."','".$commfac."')";
                    try{$owlPDO->exec($sDel2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
            }
            else
            {
                    $sDel2="insert into ".$dbname.".bgt_jam_operasioal_pks (`tahunbudget`,`millcode`,`jamolah`,`breakdown`,`kapasitas`,`througputeffeciency`,`commercialfactor`)
                    values ('".$tahunbudget."','".$kodeorg."','".$jamo."','".$jamb."','".$kapspabrik."','".$threff."','".$commfac."')";
                    try{$owlPDO->exec($sDel2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
            }
    break;

    case'loadData':
            $str1="select * from ".$dbname.".bgt_jam_operasioal_pks where millcode='".
                    $_SESSION['empl']['lokasitugas']."' order by tahunbudget desc";
            $no=0;
            $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ);
            while($bar1=$res1->fetch())
            {
                    $no+=1;
                    echo"<tr class=rowcontent>
                    <td align=center>".$no."</td>
                    <td align=right>".$bar1->tahunbudget."</td>
                    <td align=left>".$bar1->millcode."</td>
                    <td align=right>".$bar1->kapasitas."</td>
                    <td align=right>".$bar1->througputeffeciency."</td>
                    <td align=right>".$bar1->commercialfactor."</td>
                    <td align=right>".$bar1->jamolah."</td>
                    <td align=right>".$bar1->breakdown."</td>	
                    <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->tahunbudget."','".$bar1->millcode."','".$bar1->kapasitas."','".$bar1->througputeffeciency."','".$bar1->commercialfactor."','".$bar1->jamolah."','".$bar1->breakdown."');\"></td></tr>";
            }
    break;
}
?>