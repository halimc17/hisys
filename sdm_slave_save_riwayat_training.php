<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$jenistraining=checkPostGet('jenistraining','');
$judultraining=checkPostGet('judultraining','');
$penyelenggara=checkPostGet('penyelenggara','');
$tanggalmulai=checkPostGet('tanggalmulai','');
$tanggalselesai=checkPostGet('tanggalselesai','');

$sertifikat=checkPostGet('sertifikat','');
$biaya=checkPostGet('biaya','');

$karyawanid=checkPostGet('karyawanid','');
$nomor=checkPostGet('nomor','');

if(isset($nilai) and $nilai=='')
   $nilai=0;
if(isset($_POST['del']) or ($tanggalmulai!='' and $tanggalselesai!='') or isset($_POST['queryonly']))
{
if(isset($_POST['del']) and $_POST['del']=='true')
{
        $str="delete from ".$dbname.".sdm_karyawantraining where nomor=".$nomor;
}
else if(isset($_POST['queryonly']))
{
        $str="";
}
else
{
        if ($_POST['method'] == 'insert') {
                $str="insert into ".$dbname.".sdm_karyawantraining
                (	`karyawanid`,
                                `jenistraining`,
                                `tanggalmulai`,
                                `tanggalselesai`,
                                `judultraining`,
                                `penyelenggara`,
                                `sertifikat`,
                                `biaya`
                        )
                        values(".$karyawanid.",
                        '".$jenistraining."',
                        '".tanggalsystem($tanggalmulai)."',
                        '".tanggalsystem($tanggalselesai)."',
                        '".$judultraining."',
                        '".$penyelenggara."',
                        '".$sertifikat."',
                        '".$biaya."'
                        )";
        }else{
                ##query update
                $str="update ".$dbname.".sdm_karyawantraining set karyawanid='".$karyawanid."',jenistraining='".$jenistraining."', judultraining='".$judultraining."', tanggalmulai='".tanggalsystem($tanggalmulai)."', tanggalselesai='".tanggalsystem($tanggalselesai)."',penyelenggara='".$penyelenggara."', sertifikat='".$sertifikat."', biaya='".$biaya."' where nomor='".$nomor."'";
    
        }
}
 if($str!=''){
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";}
 }
         $str="select a.*,case a.sertifikat when 0 then 'N' else 'Y' end as bersertifikat, b.jenistraining as jnstraining 
               from ".$dbname.".sdm_karyawantraining a
                   left join ".$dbname.".sdm_5jenistraining b
                   on a.jenistraining = b.kodetraining
                        where a.karyawanid=".$karyawanid." 
                        order by a.tanggalmulai desc";	
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
         $no=0;
         while($bar=$res->fetch())
         {
         $no+=1;	
         echo"	  <tr class=rowcontent>
                          <td class=firsttd align=center>".$no."</td>
                          <td>".$bar->jnstraining."</td>			  
                          <td>".$bar->judultraining."</td>
                          <td>".$bar->penyelenggara."</td>			  
                          <td>".tanggalnormal($bar->tanggalmulai)."</td>			  
                          <td>".tanggalnormal($bar->tanggalselesai)."</td>
                          <td>".$bar->bersertifikat."</td>
                          <td align=right>".$bar->biaya."</td>
                          <td style='text-align:center;'><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"delTraining('".$karyawanid."','".$bar->nomor."');\"></td>
                          <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editTraining('".$bar->jenistraining."','".$bar->judultraining."','".tanggalnormal($bar->tanggalmulai)."','".tanggalnormal($bar->tanggalselesai)."','".$bar->penyelenggara."','".$bar->sertifikat."','".$bar->biaya."','".$bar->nomor."');\"></td>
                        </tr>";	 	
         }
}
else
{
        echo " Error; Data incomplete";
}
?>
