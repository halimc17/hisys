<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/sdm_karyawan_helper.php');
$keluarganama	=checkPostGet('keluarganama','');
$keluargajk	=checkPostGet('keluargajk','');
$keluargatmplahir	=checkPostGet('keluargatmplahir','');
$keluargatgllahir	=sdmTglSistem(checkPostGet('keluargatgllahir',''));
$keluargapekerjaan	=checkPostGet('keluargapekerjaan','');
$keluargatelp	=checkPostGet('keluargatelp','');
$keluargaemail	=checkPostGet('keluargaemail','');
$hubungankeluarga	=checkPostGet('hubungankeluarga','');
$keluargastatus	=checkPostGet('keluargastatus','');
$keluargapendidikan	=sdmId(checkPostGet('keluargapendidikan',''));
if($keluargapendidikan==0)
   $keluargapendidikan=null;
$keluargatanggungan	=checkPostGet('keluargatanggungan','')=='1'?1:0;
$keluargabpjstanggungan	=checkPostGet('keluargabpjstanggungan','');
$keluargaemplasment	=checkPostGet('keluargaemplasment','')=='1'?'1':'0';

$method=checkPostGet('method','');
$karyawanid=sdmId(checkPostGet('karyawanid',''));
$nomor=sdmId(checkPostGet('nomor',''));

if(isset($_POST['del']) or ($keluarganama!='') or isset($_POST['queryonly']))
{
        $galat='';
        try{
                if(isset($_POST['del']) and $_POST['del']=='true')
                {
                        $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawankeluarga where nomor=? and karyawanid=?");
                        $cek->execute(array($nomor,$karyawanid));
                        if($cek->fetchColumn()==0)
                        {
                                $galat=" Gagal  !: Data tidak ditemukan.\n";
                        }
                        else
                        {
                                $st=$owlPDO->prepare("delete from ".$dbname.".sdm_karyawankeluarga where nomor=? and karyawanid=?");
                                $st->execute(array($nomor,$karyawanid));
                        }
                }
                else if(!isset($_POST['queryonly']))
                {
                        if($method=='insert')
                        {
                                $st=$owlPDO->prepare("insert into ".$dbname.".sdm_karyawankeluarga
                                     (`karyawanid`,`nama`,`jeniskelamin`,`tempatlahir`,`tanggallahir`,`hubungankeluarga`,`status`,
                                      `levelpendidikan`,`pekerjaan`,`telp`,`email`,`tanggungan`,`nobpjstanggungan`,`emplasment`)
                                     values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                                $st->execute(array($karyawanid,$keluarganama,$keluargajk,$keluargatmplahir,$keluargatgllahir,$hubungankeluarga,$keluargastatus,
                                        $keluargapendidikan,$keluargapekerjaan,$keluargatelp,$keluargaemail,$keluargatanggungan,$keluargabpjstanggungan,$keluargaemplasment));
                        }
                        else
                        {
                                $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawankeluarga where nomor=? and karyawanid=?");
                                $cek->execute(array($nomor,$karyawanid));
                                if($cek->fetchColumn()==0)
                                {
                                        $galat=" Gagal  !: Data tidak ditemukan.\n";
                                }
                                else
                                {
                                        $st=$owlPDO->prepare("update ".$dbname.".sdm_karyawankeluarga set
                                                `nama`=?,`jeniskelamin`=?,`tempatlahir`=?,`tanggallahir`=?,`hubungankeluarga`=?,`status`=?,
                                                `levelpendidikan`=?,`pekerjaan`=?,`telp`=?,`email`=?,`tanggungan`=?,`nobpjstanggungan`=?,`emplasment`=?
                                                where nomor=? and karyawanid=?");
                                        $st->execute(array($keluarganama,$keluargajk,$keluargatmplahir,$keluargatgllahir,$hubungankeluarga,$keluargastatus,
                                                $keluargapendidikan,$keluargapekerjaan,$keluargatelp,$keluargaemail,$keluargatanggungan,$keluargabpjstanggungan,$keluargaemplasment,
                                                $nomor,$karyawanid));
                                }
                        }
                }
        }
        catch(PDOException $e)
        {
                $galat=sdmGalat($e,'Nama dan jenis kelamin yang sama sudah terdaftar untuk karyawan ini.');
        }
        if($galat!='')
        {
                print $galat;
        }
        $st=$owlPDO->prepare("select a.*,case a.tanggungan when 0 then 'N' else 'Y' end as tanggungan1,case a.emplasment when 0 then 'N' else 'Y' end as emplasment1,
              b.kelompok
                  from ".$dbname.".sdm_karyawankeluarga a
                  left join ".$dbname.".sdm_5pendidikan b on a.levelpendidikan=b.levelpendidikan
                       where a.karyawanid=?
                       order by a.hubungankeluarga");
        $st->execute(array($karyawanid));
        $st->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar=$st->fetch())
        {
        $no+=1;
       $val=$bar->hubungankeluarga;
       if($_SESSION['language']=='EN'){
                           switch($bar->hubungankeluarga){
                             case'Pasangan':
                                 $val='Couple';
                                 break;
                             case'Anak':
                                 $val='Child';
                                 break;
                             case'Ibu':
                                 $val='Mother';
                                 break;
                             case'Bapak':
                                 $val='Father';
                                 break;
                             case'Adik':
                                 $val='Younger brother/sister';
                                 break;
                             case'Kakak':
                                 $val='Older brother/sister';
                                 break;
                             case'Ibu Mertua':
                                 $val='Monther-in-law';
                                 break;
                             case'Bapak Mertua':
                                 $val='Father-in-law';
                                 break;
                             case'Sepupu':
                                 $val='Cousin';
                                 break;
                             case'Ponakan':
                                 $val='Nephew';
                                 break;
                             default:
                                 $val='Foster child';
                                 break;
                        }
       }
                       $gal = $bar->status;
            if($_SESSION['language']=='EN' && $bar->status=='Kawin')
              $gal='Married';
          if($_SESSION['language']=='EN' && ($bar->status=='Bujang' or $bar->status=='Lajang'))
                 $gal='Single';
        echo"	  <tr class=rowcontent>
                         <td class=firsttd align=center>".$no."</td>
                         <td>".sdmH($bar->nama)."</td>
                         <td>".sdmH($bar->jeniskelamin)."</td>
                         <td>".sdmH($val)."</td>
                         <td>".sdmH($bar->tempatlahir)."</td>
                         <td>".sdmTgl($bar->tanggallahir)."</td>
                         <td>".sdmH($gal)."</td>
                         <td>".sdmUmur($bar->tanggallahir)."</td>
                         <td>".sdmH($bar->kelompok)."</td>
                         <td>".sdmH($bar->pekerjaan)."</td>
                         <td>".sdmH($bar->telp)."</td>
                         <td>".sdmH($bar->email)."</td>
                         <td>".$bar->tanggungan1."</td>
                         <td>".sdmH($bar->nobpjstanggungan)."</td>
                         <td>".$bar->emplasment1."</td>
                         <td style='text-align:center;'>
                           <img src=images/skyblue/edit.png class=resicon  title='Edit' onclick=\"fillField('".sdmJs($bar->nama)."','".sdmJs($bar->jeniskelamin)."','".sdmJs($bar->tempatlahir)."','".sdmJs(sdmTgl($bar->tanggallahir))."','".sdmJs($bar->hubungankeluarga)."','".sdmJs($bar->status)."','".sdmJs($bar->levelpendidikan)."','".sdmJs($bar->pekerjaan)."','".sdmJs($bar->telp)."','".sdmJs($bar->email)."','".sdmJs($bar->tanggungan)."','".sdmJs($bar->nobpjstanggungan)."','".sdmJs($bar->emplasment)."','".sdmJs($bar->nomor)."');\">
                           <img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"delKeluarga('".$karyawanid."','".sdmJs($bar->nomor)."');\">
                         </td>
                       </tr>";
        }
}
else
{
        echo " Error; Data incomplete";
}
?>
