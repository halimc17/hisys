<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$unit=$_POST['unit'];
$periode=$_POST['periode'];

if($periode==''){
        echo "Warning: Period is obligatory"; exit;
}

$str="select tanggalmulai, tanggalsampai from ".$dbname.".setup_periodeakuntansi
      where kodeorg ='".$unit."' and periode='".$periode."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $tanggalmulai=$bar->tanggalmulai;
        $tanggalsampai=$bar->tanggalsampai;
}
if($_SESSION['language']=='EN'){
    $zz=' b.namaakun1';
}
else{
    $zz='b.namaakun';
}
$str="select a.nojurnal as nojurnal, a.tanggal as tanggal, a.keterangan as keterangan, a.noakun as noakun, ".$zz." as namaakun, a.debet as debet, a.kredit as kredit, a.kodeblok as kodeorg, a.kodevhc as kodevhc  
                  from ".$dbname.".keu_jurnaldt_vw a
                  left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
                  where a.tanggal>='".$tanggalmulai."' and a.tanggal<='".$tanggalsampai."' and a.noreferensi in ('ALK_KERJA_AB') and a.kodeorg = '".$unit."' 
                  order by a.tanggal";
//=================================================

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$no=0;
if($numrows<1)
{
      echo"<tr class=rowcontent><td colspan=17>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
while($bar=$res->fetch())
{
    $no+=1; $total=0;
               echo"<tr class=rowcontent>
                                <td align=right>".$no."</td>
                                <td>".$bar->nojurnal."</td>
                                <td align=right>".tanggalnormal($bar->tanggal)."</td>
                                <td>".$bar->keterangan."</td>
                                <td align=right>".$bar->noakun."</td>
                                <td>".$bar->namaakun."</td>
                                <td align=right>".number_format($bar->debet,2)."</td>
                                <td align=right>".number_format($bar->kredit,2)."</td>
                                <td>".$bar->kodeorg."</td>
                                <td>".$bar->kodevhc."</td>
                        </tr>";
        }
}