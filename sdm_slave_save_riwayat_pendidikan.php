<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/sdm_karyawan_helper.php');

$levelpendidikan=sdmId(checkPostGet('levelpendidikan',''));
$tahunlulus=checkPostGet('tahunlulus','');
$spesialisasi=checkPostGet('spesialisasi','');
$gelar=checkPostGet('gelar','');
$namasekolah=checkPostGet('namasekolah','');
$nilai=checkPostGet('nilai','');
$pendidikankota=checkPostGet('pendidikankota','');
$keterangan=checkPostGet('pendidikanketerangan','');
$karyawanid=sdmId(checkPostGet('karyawanid',''));
$kode=sdmId(checkPostGet('kode',''));
$method=checkPostGet('method','');

if($nilai=='' or !is_numeric($nilai))
   $nilai=0;

$galat='';
try{
        if(isset($_POST['del']) and $_POST['del']=='true')
        {
                $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawanpendidikan where kode=? and karyawanid=?");
                $cek->execute(array($kode,$karyawanid));
                if($cek->fetchColumn()==0)
                {
                        $galat=" Gagal  !: Data tidak ditemukan.\n";
                }
                else
                {
                        $st=$owlPDO->prepare("delete from ".$dbname.".sdm_karyawanpendidikan where kode=? and karyawanid=?");
                        $st->execute(array($kode,$karyawanid));
                }
        }
        else if(!isset($_POST['queryonly']))
        {
                if($levelpendidikan==0 or $tahunlulus=='' or $namasekolah=='')
                {
                        $galat=" Gagal  !: Data tidak lengkap, tingkat pendidikan, tahun lulus, dan nama sekolah harus diisi.\n";
                }
                else if($method=='insert')
                {
                        $st=$owlPDO->prepare("insert into ".$dbname.".sdm_karyawanpendidikan
                                (`karyawanid`,`levelpendidikan`,`spesialisasi`,`gelar`,`tahunlulus`,`namasekolah`,`nilai`,`kota`,`keterangan`)
                                values(?,?,?,?,?,?,?,?,?)");
                        $st->execute(array($karyawanid,$levelpendidikan,$spesialisasi,$gelar,$tahunlulus,$namasekolah,$nilai,$pendidikankota,$keterangan));
                }
                else
                {
                        $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawanpendidikan where kode=? and karyawanid=?");
                        $cek->execute(array($kode,$karyawanid));
                        if($cek->fetchColumn()==0)
                        {
                                $galat=" Gagal  !: Data tidak ditemukan.\n";
                        }
                        else
                        {
                                $st=$owlPDO->prepare("update ".$dbname.".sdm_karyawanpendidikan set levelpendidikan=?,spesialisasi=?,gelar=?,tahunlulus=?,namasekolah=?,nilai=?,kota=?,keterangan=?
                                        where kode=? and karyawanid=?");
                                $st->execute(array($levelpendidikan,$spesialisasi,$gelar,$tahunlulus,$namasekolah,$nilai,$pendidikankota,$keterangan,$kode,$karyawanid));
                        }
                }
        }
}
catch(PDOException $e)
{
        $galat=sdmGalat($e,'Riwayat pendidikan dengan sekolah, tahun lulus, dan spesialisasi yang sama sudah ada.');
}
if($galat!='')
{
        print $galat;
}
        $st=$owlPDO->prepare("select a.*,b.kelompok from ".$dbname.".sdm_karyawanpendidikan a
                   left join ".$dbname.".sdm_5pendidikan b on a.levelpendidikan=b.levelpendidikan
                   where a.karyawanid=?
                   order by a.levelpendidikan desc");
        $st->execute(array($karyawanid));
        $st->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar=$st->fetch())
        {
        $no+=1;
        echo"	  <tr class=rowcontent>
                       <td class=firsttd align=center>".$no."</td>
                       <td>".sdmH($bar->kelompok)."</td>
                       <td>".sdmH($bar->namasekolah)."</td>
                       <td>".sdmH($bar->kota)."</td>
                       <td>".sdmH($bar->spesialisasi)."</td>
                       <td>".sdmH($bar->tahunlulus)."</td>
                       <td>".sdmH($bar->gelar)."</td>
                       <td>".($bar->nilai==0?'':sdmH($bar->nilai))."</td>
                       <td>".sdmH($bar->keterangan)."</td>
                       <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPendidikan('".$karyawanid."','".sdmJs($bar->kode)."');\"></td>
                       <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPendidikan('".sdmJs($bar->levelpendidikan)."','".sdmJs($bar->spesialisasi)."','".sdmJs($bar->gelar)."','".sdmJs($bar->tahunlulus)."','".sdmJs($bar->namasekolah)."','".sdmJs($bar->nilai)."','".sdmJs($bar->kota)."','".sdmJs($bar->keterangan)."','".sdmJs($bar->kode)."');\"></td>
                     </tr>";
        }
?>
