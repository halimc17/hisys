<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/sdm_karyawan_helper.php');

$jenistraining=checkPostGet('jenistraining','');
$judultraining=checkPostGet('judultraining','');
$penyelenggara=checkPostGet('penyelenggara','');
$tanggalmulai=checkPostGet('tanggalmulai','');
$tanggalselesai=checkPostGet('tanggalselesai','');

$sertifikat=checkPostGet('sertifikat','')=='1'?1:0;
$biaya=checkPostGet('biaya','');
if($biaya=='' or !is_numeric($biaya))
   $biaya=0;

$karyawanid=sdmId(checkPostGet('karyawanid',''));
$nomor=sdmId(checkPostGet('nomor',''));
$method=checkPostGet('method','');

if(isset($_POST['del']) or ($tanggalmulai!='' and $tanggalselesai!='') or isset($_POST['queryonly']))
{
        $galat='';
        try{
                if(isset($_POST['del']) and $_POST['del']=='true')
                {
                        $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawantraining where nomor=? and karyawanid=?");
                        $cek->execute(array($nomor,$karyawanid));
                        if($cek->fetchColumn()==0)
                        {
                                $galat=" Gagal  !: Data tidak ditemukan.\n";
                        }
                        else
                        {
                                $st=$owlPDO->prepare("delete from ".$dbname.".sdm_karyawantraining where nomor=? and karyawanid=?");
                                $st->execute(array($nomor,$karyawanid));
                        }
                }
                else if(!isset($_POST['queryonly']))
                {
                        $tglmulai=sdmTglSistem($tanggalmulai);
                        $tglselesai=sdmTglSistem($tanggalselesai);
                        if($tglmulai===null or $tglselesai===null)
                        {
                                $galat=" Gagal  !: Tanggal mulai dan tanggal selesai tidak valid.\n";
                        }
                        else if($method=='insert')
                        {
                                $st=$owlPDO->prepare("insert into ".$dbname.".sdm_karyawantraining
                                        (`karyawanid`,`jenistraining`,`tanggalmulai`,`tanggalselesai`,`judultraining`,`penyelenggara`,`sertifikat`,`biaya`)
                                        values(?,?,?,?,?,?,?,?)");
                                $st->execute(array($karyawanid,$jenistraining,$tglmulai,$tglselesai,$judultraining,$penyelenggara,$sertifikat,$biaya));
                        }
                        else
                        {
                                $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawantraining where nomor=? and karyawanid=?");
                                $cek->execute(array($nomor,$karyawanid));
                                if($cek->fetchColumn()==0)
                                {
                                        $galat=" Gagal  !: Data tidak ditemukan.\n";
                                }
                                else
                                {
                                        $st=$owlPDO->prepare("update ".$dbname.".sdm_karyawantraining set jenistraining=?,judultraining=?,tanggalmulai=?,tanggalselesai=?,
                                                penyelenggara=?,sertifikat=?,biaya=? where nomor=? and karyawanid=?");
                                        $st->execute(array($jenistraining,$judultraining,$tglmulai,$tglselesai,$penyelenggara,$sertifikat,$biaya,$nomor,$karyawanid));
                                }
                        }
                }
        }
        catch(PDOException $e)
        {
                $galat=sdmGalat($e,'Training dengan penyelenggara dan tanggal mulai yang sama sudah ada.');
        }
        if($galat!='')
        {
                print $galat;
        }
         $st=$owlPDO->prepare("select a.*,case a.sertifikat when 0 then 'N' else 'Y' end as bersertifikat, b.jenistraining as jnstraining
               from ".$dbname.".sdm_karyawantraining a
                   left join ".$dbname.".sdm_5jenistraining b
                   on a.jenistraining = b.kodetraining
                        where a.karyawanid=?
                        order by a.tanggalmulai desc");
        $st->execute(array($karyawanid));
        $st->setFetchMode(PDO::FETCH_OBJ);
         $no=0;
         while($bar=$st->fetch())
         {
         $no+=1;
         echo"	  <tr class=rowcontent>
                          <td class=firsttd align=center>".$no."</td>
                          <td>".sdmH($bar->jnstraining)."</td>
                          <td>".sdmH($bar->judultraining)."</td>
                          <td>".sdmH($bar->penyelenggara)."</td>
                          <td>".sdmTgl($bar->tanggalmulai)."</td>
                          <td>".sdmTgl($bar->tanggalselesai)."</td>
                          <td>".$bar->bersertifikat."</td>
                          <td align=right>".sdmUang($bar->biaya)."</td>
                          <td style='text-align:center;'><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"delTraining('".$karyawanid."','".sdmJs($bar->nomor)."');\"></td>
                          <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editTraining('".sdmJs($bar->jenistraining)."','".sdmJs($bar->judultraining)."','".sdmJs(sdmTgl($bar->tanggalmulai))."','".sdmJs(sdmTgl($bar->tanggalselesai))."','".sdmJs($bar->penyelenggara)."','".sdmJs($bar->sertifikat)."','".sdmJs($bar->biaya)."','".sdmJs($bar->nomor)."');\"></td>
                        </tr>";
         }
}
else
{
        echo " Error; Data incomplete";
}
?>
