<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/sdm_karyawan_helper.php');

$namaperusahaan=checkPostGet('namaperusahaan','');
$bidangusaha=checkPostGet('bidangusaha','');

$blnmasuk=checkPostGet('blnmasuk','');
$thnmasuk=checkPostGet('thnmasuk','');
$blnkeluar=checkPostGet('blnkeluar','');
$thnkeluar=checkPostGet('thnkeluar','');
//hitung masa kerja
$thn=intval($thnkeluar)-intval($thnmasuk);
$bln=intval($blnkeluar)-intval($blnmasuk);
$masakerja=(($thn*12)+$bln)/12;

$blnkeluar=$blnkeluar."-".$thnkeluar;
$blnmasuk=$blnmasuk."-".$thnmasuk;
$jabatan=checkPostGet('jabatan','');
$bagian=checkPostGet('bagian','');
$alamat=checkPostGet('alamat','');
$karyawanid=sdmId(checkPostGet('karyawanid',''));
$nomor=sdmId(checkPostGet('nomor',''));
$method=checkPostGet('method','');

$gajipokok=checkPostGet('gajipokok','');
$alasanberhenti=checkPostGet('alasanberhenti','');
$tunjangan=checkPostGet('tunjangan','');
$lokasicuti=checkPostGet('lokasicuti','');

if($masakerja>0 or (isset($_POST['del']) and $_POST['del']=='true') or isset($_POST['queryonly']))
{
        $galat='';
        try{
                if(isset($_POST['del']) and $_POST['del']=='true')
                {
                        $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawancv where nomor=? and karyawanid=?");
                        $cek->execute(array($nomor,$karyawanid));
                        if($cek->fetchColumn()==0)
                        {
                                $galat=" Gagal  !: Data tidak ditemukan.\n";
                        }
                        else
                        {
                                $st=$owlPDO->prepare("delete from ".$dbname.".sdm_karyawancv where nomor=? and karyawanid=?");
                                $st->execute(array($nomor,$karyawanid));
                        }
                }
                else if(!isset($_POST['queryonly']))
                {
                        if($method=='insert')
                        {
                                $st=$owlPDO->prepare("insert into ".$dbname.".sdm_karyawancv
                                        (`karyawanid`,`namaperusahaan`,`bidangusaha`,`bulanmasuk`,`bulankeluar`,`jabatan`,`bagian`,
                                         `masakerja`,`alamatperusahaan`,`gajipokok`,`alasanberhenti`,`tunjangan`,`lokasicuti`)
                                        values(?,?,?,?,?,?,?,?,?,?,?,?,?)");
                                $st->execute(array($karyawanid,$namaperusahaan,$bidangusaha,$blnmasuk,$blnkeluar,$jabatan,$bagian,
                                        $masakerja,$alamat,$gajipokok,$alasanberhenti,$tunjangan,$lokasicuti));
                        }
                        else
                        {
                                $cek=$owlPDO->prepare("select count(*) from ".$dbname.".sdm_karyawancv where nomor=? and karyawanid=?");
                                $cek->execute(array($nomor,$karyawanid));
                                if($cek->fetchColumn()==0)
                                {
                                        $galat=" Gagal  !: Data tidak ditemukan.\n";
                                }
                                else
                                {
                                        $st=$owlPDO->prepare("update ".$dbname.".sdm_karyawancv set namaperusahaan=?,bidangusaha=?,bulanmasuk=?,bulankeluar=?,jabatan=?,bagian=?,
                                                masakerja=?,alamatperusahaan=?,gajipokok=?,alasanberhenti=? where nomor=? and karyawanid=?");
                                        $st->execute(array($namaperusahaan,$bidangusaha,$blnmasuk,$blnkeluar,$jabatan,$bagian,
                                                $masakerja,$alamat,$gajipokok,$alasanberhenti,$nomor,$karyawanid));
                                }
                        }
                }
        }
        catch(PDOException $e)
        {
                $galat=sdmGalat($e,'Pengalaman kerja dengan nama perusahaan dan bulan masuk yang sama sudah ada.');
        }
        if($galat!='')
        {
                print $galat;
        }
        $st=$owlPDO->prepare("select *,right(bulanmasuk,4) as masup,left(bulanmasuk,2) as busup from ".$dbname.".sdm_karyawancv where karyawanid=? order by masup,busup");
        $st->execute(array($karyawanid));
        $st->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        $mskerja=0;
        while($bar=$st->fetch())
        {
               $no+=1;
        echo"	  <tr class=rowcontent>
                        <td class=firsttd align=center>".$no."</td>
                        <td>".sdmH($bar->namaperusahaan)."</td>
                        <td>".sdmH($bar->bidangusaha)."</td>
                        <td>".sdmH($bar->bulanmasuk)."</td>
                        <td>".sdmH($bar->bulankeluar)."</td>
                        <td>".sdmH($bar->jabatan)."</td>
                        <td>".sdmH($bar->bagian)."</td>
                        <td>".sdmDurasiBulan($bar->bulanmasuk,$bar->bulankeluar)."</td>
                        <td>".sdmH($bar->alamatperusahaan)."</td>
                        <td>".sdmH($bar->gajipokok)."</td>
                        <td>".sdmH($bar->alasanberhenti)."</td>
                        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPengalaman('".$karyawanid."','".sdmJs($bar->nomor)."');\"></td>
                        <td align=center><img src=images/application/application_edit.png class=resicon  title='Delete' onclick=\"editPengalaman('".sdmJs($bar->namaperusahaan)."','".sdmJs($bar->bidangusaha)."','".sdmJs($bar->bulanmasuk)."','".sdmJs($bar->bulankeluar)."','".sdmJs($bar->bagian)."','".sdmJs($bar->jabatan)."','".sdmJs($bar->alamatperusahaan)."','".sdmJs($bar->gajipokok)."','".sdmJs($bar->alasanberhenti)."','".sdmJs($bar->nomor)."');\"></td>
                      </tr>";
        }
}
else
{
        echo" Error: Incorrect Period";
}
?>
