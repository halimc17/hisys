<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

include_once('lib/zMysql.php');
require_once('lib/zLib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$tmp=explode(',',$_GET['column']);
$kode=$tmp[0];
$kodeorg=$tmp[1];
$kodeproject=$tmp[2];
$mingguke=$tmp[3];
$tglawal=$tmp[4];
$tglakhir=$tmp[5];
$optKp=makeOption($dbname,'project','kode,nama');
			
		$sInduk="select induk,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
        $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
        $qInduk->setFetchMode(PDO::FETCH_ASSOC);
        $rInduk=$qInduk->fetch();

        $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch()){
            $nama=$bar1->namaorganisasi;
        }

        $arrmk1=array();
        if(intval($mingguke)>1){
            $query="select kodeproject,deskripsi from ".$dbname.".project_dt  where kodeproject='".$kodeproject."' group by deskripsi order by deskripsi";
            $res=fetchData($query);

            for ($xz=1; $xz < intval($mingguke) ; $xz++) {
                foreach ($res as $key => $val) {
                    $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id,c.mingguke from ".$dbname.".project_dt a 
                    left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan 
                    left join ".$dbname.".vhc_progproject c on b.induk=c.kode   where a.kodeproject='".$val['kodeproject']."' and 
                    a.deskripsi='".$val['deskripsi']."' and c.mingguke='".(intval($xz))."'";
                    
                    $res2=fetchData($query2);
                    foreach ($res2 as $key2 => $val2) {
                        $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['volume']+=$val2['volumex'];
                        $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['bobot']+=$val2['bobotx'];
                    }

                }
            }

            for ($xz=1; $xz <= intval($mingguke) ; $xz++) { 
                foreach ($res as $key => $val) {
                    $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id,c.mingguke from ".$dbname.".project_dt a 
                    left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan 
                    left join ".$dbname.".vhc_progproject c on b.induk=c.kode   where a.kodeproject='".$val['kodeproject']."' and 
                    a.deskripsi='".$val['deskripsi']."' and c.mingguke='".(intval($xz))."'";
                    
                    $res2=fetchData($query2);
                        foreach ($res2 as $key2 => $val2) {
                            $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume']+=$val2['volumex'];
                            $arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot']+=$val2['bobotx'];
                        }

                }
            }
        
        }


        $query="select kodeproject,deskripsi from ".$dbname.".project_dt  where kodeproject='".$kodeproject."' group by deskripsi order by deskripsi";
        $res=fetchData($query);

        $tabs="<style>
        @page { margin: 170px 30px; }
        #header { position: fixed; left: 0px; top: -145px; right: 0px; height: 140px;}
        </style>";
        $tabs.="<div id='header'>";
        $tabs.="<table cellspacing=0 border=0;  width=100% align=center>";
        $tabs.="<tr>";
        $tabs.="<td colspan=6 style=font-weight:bold;><font size=3>".$nama."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td colspan=6  style=font-weight:bold;><font size=2>".$rInduk['namaorganisasi']."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td  colspan=6 style='font-weight:bold;text-align:center;'><font size=3>PROGRESS REPORT</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td colspan=6  style='text-align:center;'><font size=1>LAPORAN MINGGUAN</font></td>";
        $tabs.="</tr>";


        $tabs.="<tr>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>PEKERJAAN</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$optKp[$kodeproject]."</font></td>";
        $tabs.="<td style='text-align:right;'><font size=1>Minggu Ke</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$mingguke."</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>LOKASI</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".$nama."</font></td>";
        $tabs.="<td style='text-align:right;'><font size=1>Tanggal</font></td>";
        $tabs.="<td style='text-align:center;width:20px;'><font size=1>:</font></td>";
        $tabs.="<td style='text-align:left;width:200px;'><font size=1>".tanggalnormal($tglawal)." s/d ".tanggalnormal($tglakhir)."</font></td>";
        $tabs.="</tr>";
        $tabs.="</table></div>";
        
        $tabs.="<div id='content'><table cellspacing=0 border=1;  width=100% align=center>";
        $tabs.="<thead>";
        if(intval($mingguke)>1){
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>NO</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>ITEM PEKERJAAN</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>VOLUME</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>SAT</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>BOBOT</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>S/D Minggu Lalu</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Minggu Ini</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>S/D Minggu Ini</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Sisa Pekerjaan</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";

        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";


        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";


        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";
        $tabs.="</tr>";
        }
        else
        {
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>NO</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>ITEM PEKERJAAN</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>VOLUME</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>SAT</font></td>";
        $tabs.="<td style='text-align:center;' rowspan=2><font size=2>BOBOT</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Minggu Ini</font></td>";
        $tabs.="<td style='text-align:center;' colspan=2><font size=2>Sisa Pekerjaan</font></td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";

        $tabs.="<td style='text-align:center;'><font size=2>Volume</font></td>";
        $tabs.="<td style='text-align:center;'><font size=2>Bobot</font></td>";

        $tabs.="</tr>";
        }
        $tabs.="</thead>";
        $tabs.="<tbody>";
        $total=array();
        if(intval($mingguke)>1){
            foreach ($res as $key => $val) {
                $arrsubtot=array();
                $tabs.="<tr>";
                $tabs.="<td style='font-weight:bold;text-align:center;'><font size=2>".romawi($key+1)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2>".$val['deskripsi']."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";

                $tabs.="</tr>";
                $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id from ".$dbname.".project_dt a 
                left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan  where a.kodeproject='".$val['kodeproject']."' and 
                a.deskripsi='".$val['deskripsi']."' and  b.induk='".$kode."'";
                $res2=fetchData($query2);
                foreach ($res2 as $key2 => $val2) {
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume']+=$val2['volume'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot']+=$val2['bobot'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkvolume']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['volume'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkbobot']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['bobot'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex']+=$val2['volumex'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx']+=$val2['bobotx'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdvolume']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdbobot']+=$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume']+=($val2['volume']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume']);
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot']+=($val2['bobot']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot']);
                    $tabs.="<tr>";
                    $tabs.="<td style='text-align:center;'><font size=1>".($key2+1)."</font></td>";
                    $tabs.="<td style='text-align:left;'><font size=1>".$val2['namakegiatan']."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volume'],2)."</font></td>";
                    $tabs.="<td style='text-align:left;'><font size=1>".$val2['satuan']."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobot'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['volume'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['bobot'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volumex'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobotx'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['volume']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdvolume']),2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['bobot']-$arrmk1[$val['kodeproject']][$val['deskripsi']][$val2['kegiatan']]['sdbobot']),2)."</font></td>";
                    $tabs.="</tr>";
                }
                $total['volume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'];
                $total['bobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'];
                $total['volumex']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'];
                $total['bobotx']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'];
                $total['mkvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkvolume'];
                $total['mkbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkbobot'];
                $total['sdvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdvolume'];
                $total['sdbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdbobot'];
                $total['ssvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'];
                $total['ssbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'];
                $tabs.="<tr>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>Sub Jumlah</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkvolume'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['mkbobot'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdvolume'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['sdbobot'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'],2)."</font></td>";
                $tabs.="</tr>";
            }
            $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>TOTAL</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['mkvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['mkbobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volumex'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobotx'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['sdvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['sdbobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssbobot'],2)."</font></td>";
            $tabs.="</tr>";
        }
        else
        {
            foreach ($res as $key => $val) 
            {
                $arrsubtot=array();
                $tabs.="<tr>";
                $tabs.="<td style='font-weight:bold;text-align:center;'><font size=2>".romawi($key+1)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2>".$val['deskripsi']."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=2></font></td>";

                $tabs.="</tr>";
                $query2="select a.*,b.id,b.induk,b.volume as volumex,b.bobot as bobotx,b.id from ".$dbname.".project_dt a 
                left join ".$dbname.".vhc_progproject_dt b on a.kegiatan=b.kegiatan  where a.kodeproject='".$val['kodeproject']."' and 
                a.deskripsi='".$val['deskripsi']."' and  b.induk='".$kode."'";
                $res2=fetchData($query2);
                foreach ($res2 as $key2 => $val2) {
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume']+=$val2['volume'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot']+=$val2['bobot'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex']+=$val2['volumex'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx']+=$val2['bobotx'];
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume']+=($val2['volume']-$val2['volumex']);
                    $arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot']+=($val2['bobot']-$val2['bobotx']);
                    $tabs.="<tr>";
                    $tabs.="<td style='text-align:center;'><font size=1>".($key2+1)."</font></td>";
                    $tabs.="<td style='text-align:left;'><font size=1>".$val2['namakegiatan']."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volume'],2)."</font></td>";
                    $tabs.="<td style='text-align:left;'><font size=1>".$val2['satuan']."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobot'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['volumex'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal($val2['bobotx'],2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['volume']-$val2['volumex']),2)."</font></td>";
                    $tabs.="<td style='text-align:right;'><font size=1>".hidezerodecimal(($val2['bobot']-$val2['bobotx']),2)."</font></td>";
                    $tabs.="</tr>";
                }
                $total['volume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'];
                $total['bobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'];
                $total['volumex']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'];
                $total['bobotx']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'];
                $total['ssvolume']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'];
                $total['ssbobot']+=$arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'];
                $tabs.="<tr>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>Sub Jumlah</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volume'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobot'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['volumex'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['bobotx'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssvolume'],2)."</font></td>";
                $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($arrsubtot[$val['kodeproject']][$val['deskripsi']]['ssbobot'],2)."</font></td>";
                $tabs.="</tr>";
            }
            $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:left;'><font size=1>TOTAL</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:center;'><font size=1></font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobot'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['volumex'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['bobotx'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssvolume'],2)."</font></td>";
            $tabs.="<td style='font-weight:bold;text-align:right;'><font size=1>".hidezerodecimal($total['ssbobot'],2)."</font></td>";
            $tabs.="</tr>";   
        }
            
        $tabs.="</tbody>";
        $tabs.="</table>";

        $tabs.="<table border=0px cellspacing=0 width=100% align=right>";
         $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=2><font size=1>Diketahui oleh</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=1><font size=1>Diperiksa oleh</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;' colspan=1><font size=1>Dilaporkan oleh</font></td>";

            $tabs.="</tr>";

        $tabs.="<tr>";
            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;'><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;'><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;'><font size=1>________________________</font></td>";

            $tabs.="<td style='font-weight:bold;height:70px;text-align:center;'><font size=1>________________________</font></td>";

            $tabs.="</tr>";

        $tabs.="</table>";

        $tabs.="</div>";
            /*echo $tabs;
            exit();*/
			$dompdf = new Dompdf();
            $dompdf->loadHtml($tabs);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("form survey",array("Attachment"=>0));