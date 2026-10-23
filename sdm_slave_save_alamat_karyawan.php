<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/sdm_karyawan_helper.php');

$alamatalamat=checkPostGet('alamatalamat','');
$alamatkota=checkPostGet('alamatkota','');
$alamatkodepos=checkPostGet('alamatkodepos','');
$alamattelepon=checkPostGet('alamattelepon','');
$alamatemplasement=checkPostGet('alamatemplasement','');
$alamatstatus=checkPostGet('alamatstatus','')=='1'?1:0;
$alamatprovinsi=checkPostGet('alamatprovinsi','');
$karyawanid=sdmId(checkPostGet('karyawanid',''));
$nourut=sdmId(checkPostGet('nomor',''));
$method=checkPostGet('method','');

if($alamatalamat!='' or (isset($_POST['del']) and $_POST['del']=='true') or isset($_POST['queryonly']))
{
        $galat='';
        try{
                if(isset($_POST['del']) and $_POST['del']=='true')
                {
                        $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawanalamat where nomor=? and karyawanid=?");
                        $cek->execute(array($nourut,$karyawanid));
                        if($cek->fetchColumn()==0)
                        {
                                $galat=" Gagal  !: Data tidak ditemukan.\n";
                        }
                        else
                        {
                                $st=$owlPDO->prepare("delete from ".$dbname.".sdm_karyawanalamat where nomor=? and karyawanid=?");
                                $st->execute(array($nourut,$karyawanid));
                        }
                }
                else if(!isset($_POST['queryonly']))
                {
                        if($method=='insert')
                        {
                                $st=$owlPDO->prepare("insert into ".$dbname.".sdm_karyawanalamat
                                        (`karyawanid`,`alamat`,`kota`,`kodepos`,`telepon`,`emplasemen`,`aktif`,`provinsi`)
                                        values(?,?,?,?,?,?,?,?)");
                                $st->execute(array($karyawanid,$alamatalamat,$alamatkota,$alamatkodepos,$alamattelepon,$alamatemplasement,$alamatstatus,$alamatprovinsi));
                        }
                        else
                        {
                                $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawanalamat where nomor=? and karyawanid=?");
                                $cek->execute(array($nourut,$karyawanid));
                                if($cek->fetchColumn()==0)
                                {
                                        $galat=" Gagal  !: Data tidak ditemukan.\n";
                                }
                                else
                                {
                                        $st=$owlPDO->prepare("update ".$dbname.".sdm_karyawanalamat set alamat=?,kota=?,kodepos=?,telepon=?,emplasemen=?,aktif=?,provinsi=?
                                                where nomor=? and karyawanid=?");
                                        $st->execute(array($alamatalamat,$alamatkota,$alamatkodepos,$alamattelepon,$alamatemplasement,$alamatstatus,$alamatprovinsi,$nourut,$karyawanid));
                                }
                        }
                }
                //jika alamat adalah aktif, update table datakaryawan
                if($alamatstatus==1 and $galat=='' and !isset($_POST['del']) and !isset($_POST['queryonly']))
                {
                        $st=$owlPDO->prepare("update ".$dbname.".datakaryawan set alamataktif=?,kota=?,provinsi=? where karyawanid=?");
                        $st->execute(array($alamatalamat,$alamatkota,$alamatprovinsi,$karyawanid));
                }
        }
        catch(PDOException $e)
        {
                $galat=sdmGalat($e);
        }
        if($galat!='')
        {
                print $galat;
        }
        $st=$owlPDO->prepare("select *,case aktif when 1 then 'Y' when 0 then 'N' end as status from ".$dbname.".sdm_karyawanalamat where karyawanid=? order by nomor desc");
        $st->execute(array($karyawanid));
        $st->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar=$st->fetch())
        {
        $no+=1;
        echo"<tr class=rowcontent>
                                  <td class=firsttd align=center>".$no."</td>
                                  <td>".sdmH($bar->alamat)."</td>
                                  <td>".sdmH($bar->kota)."</td>
                                  <td>".sdmH($bar->provinsi)."</td>
                                  <td>".sdmH($bar->kodepos)."</td>
                                  <td>".sdmH($bar->emplasemen)."</td>
                                  <td>".$bar->status."</td>
                                  <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delAlamat('".$karyawanid."','".sdmJs($bar->nomor)."');\"></td>
                                  <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editAlamat('".sdmJs($bar->alamat)."','".sdmJs($bar->kota)."','".sdmJs($bar->kodepos)."','".sdmJs($bar->provinsi)."','".sdmJs($bar->telepon)."','".sdmJs($bar->emplasemen)."','".sdmJs($bar->aktif)."','".sdmJs($bar->nomor)."');\"></td>
                                </tr>";
        }
}
else
{
        echo" Error: Incorrect Period";
}
?>
