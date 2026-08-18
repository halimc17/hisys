<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

//	$pt=$_POST['pt']; source: log_laporanHutangSupplier.php
$unit=$_POST['unit'];
$periode=$_POST['periode'];

if($periode==''){
    echo "Warning: Period is oblogatory"; exit;
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
} else {
    $zz='b.namaakun';
}
$str="select a.nojurnal as nojurnal, a.tanggal as tanggal, a.keterangan as keterangan, a.noakun as noakun, ".$zz." as namaakun, a.debet as debet, a.kredit as kredit, a.kodeblok as kodeorg, a.noreferensi as kodevhc  
        from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
        where a.tanggal>='".$tanggalmulai."' and a.tanggal<='".$tanggalsampai."' and (a.noreferensi in ('ALK_WS_GYMH','ALK_TRK_GYMH','ALK_WAS','ALK_GAJI') or nojurnal like '%M0%' or nojurnal like '%PNN%') 
        and a.kodeorg = '".$unit."'  order by a.tanggal";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$numrows=owlBaris($res);
if($numrows<1)
{
        echo"<tr class=rowcontent><td colspan=17>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
        $td=$tk=0;
        while($bar=$res->fetch())
                    {
                $no++;
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
                $td+=$bar->debet;
                $tk+= $bar->kredit;                              
        }
        echo"<tr class=rowcontent>
                <td colspan=6>Total</td>
                <td align=right>".number_format($td,2)."</td>
                <td align=right>".number_format($tk,2)."</td>
                <td></td>
                <td></td>
        </tr>";
}