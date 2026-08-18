<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$levelpendidikan=checkPostGet('levelpendidikan','');
$tahunlulus=checkPostGet('tahunlulus','');
$spesialisasi=checkPostGet('spesialisasi','');
$gelar=checkPostGet('gelar','');
$namasekolah=checkPostGet('namasekolah','');
$nilai=checkPostGet('nilai','');
$pendidikankota=checkPostGet('pendidikankota','');
$keterangan=checkPostGet('pendidikanketerangan','');
$karyawanid=checkPostGet('karyawanid','');
$kode=checkPostGet('kode','');
// $kota=checkPostGet('pendidikankota','');

if($nilai=='')
   $nilai=0;
if(isset($_POST['del']) and $_POST['del']=='true')
{
	$str="delete from ".$dbname.".sdm_karyawanpendidikan where kode=".$kode;
}
else if( isset($_POST['queryonly']))
{
	$str="";
}
else
{
   if ($_POST['method'] == 'insert') {
         $str="insert into ".$dbname.".sdm_karyawanpendidikan
            (    `karyawanid`,
                                    `levelpendidikan`,
                                    `spesialisasi`,
                                    `gelar`,
                                    `tahunlulus`,
                                    `namasekolah`,
                                    `nilai`,
                                    `kota`,
                                    `keterangan`
                              )
                                    values('".$karyawanid."',
                                    '".$levelpendidikan."',
                                    '".$spesialisasi."',
                                    '".$gelar."',
                                    '".$tahunlulus."',
                                    '".$namasekolah."',
                                    ".$nilai.",
                                    '".$pendidikankota."',
                                    '".$keterangan."'
                                    )";
         }else{
            ##query update
            $str="update ".$dbname.".sdm_karyawanpendidikan set karyawanid='".$karyawanid."',levelpendidikan='".$levelpendidikan."', spesialisasi='".$spesialisasi."', gelar='".$gelar."', tahunlulus='".$tahunlulus."',namasekolah='".$namasekolah."', nilai='".$nilai."', kota='".$pendidikankota."',keterangan='".$keterangan."' where kode='".$kode."'";

         }

}
 if($str!=''){
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";}
 }
        $str="select a.*,b.kelompok from ".$dbname.".sdm_karyawanpendidikan a,".$dbname.".sdm_5pendidikan b
                   where a.karyawanid=".$karyawanid." 
                   and a.levelpendidikan=b.levelpendidikan
                   order by a.levelpendidikan desc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar=$res->fetch())
        {
        $no+=1;	
        echo"	  <tr class=rowcontent>
                       <td class=firsttd align=center>".$no."</td>
                       <td>".$bar->kelompok."</td>			  
                       <td>".$bar->namasekolah."</td>
                       <td>".$bar->kota."</td>			  
                       <td>".$bar->spesialisasi."</td>			  
                       <td>".$bar->tahunlulus."</td>
                       <td>".$bar->gelar."</td>
                       <td>".$bar->nilai."</td>
                       <td>".$bar->keterangan."</td>
                       <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPendidikan('".$karyawanid."','".$bar->kode."');\"></td>
                       <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPendidikan('".$bar->levelpendidikan."','".$bar->spesialisasi."','".$bar->gelar."','".$bar->tahunlulus."','".$bar->namasekolah."','".$bar->nilai."','".$bar->kota."','".$bar->keterangan."','".$bar->kode."');\"></td>
                     </tr>";	 	
        }
?>
