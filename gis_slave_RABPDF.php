<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

include_once('lib/zMysql.php');
require_once('lib/zLib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$tmp=explode(',',$_GET['column']);
$notransaksi=$tmp[0];
$kodeorg=$tmp[1];
$optKry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$arrstatusaju = array('0'=>'Belum Diperoses','1'=>'Disetujui','2'=>'Dikoreksi','3'=>'Ditolak');
$arrstatuslokasi = array('1'=>'Area Wilayah STH Group','0'=>'Diluar Wilayah STH Group');
			
		$sInduk="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
        $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
        $qInduk->setFetchMode(PDO::FETCH_ASSOC);
        $rInduk=$qInduk->fetch();

        $str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$rInduk['induk']."'"; 
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch()){
            $nama=$bar1->namaorganisasi;
            $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
            $telp=$bar1->telepon;
        }

        $arrHead = setheadreport('',$rInduk['induk']);
        $path=$arrHead['logo'];
        $tabs="<div>";
        $tabs.="<table cellspacing=0 border=0  width=100% align=center>";
        $tabs.="<tr>";
        $tabs.="<td rowspan=3 style=font-weight:bold;><img src='".$path."' height='100'/></td>";
        $tabs.="<td style=font-weight:bold;>".$nama."</td>";
        $tabs.="</tr>";

        $tabs.="<tr>";
        $tabs.="<td style=font-weight:bold;>".$alamatpt."</td>";
        $tabs.="</tr>";


        $tabs.="<tr>";
        $tabs.="<td style=font-weight:bold;>".$telp."</td>";
        $tabs.="</tr>";
        $tabs.="</table>";
        $tabs.="<hr>";


        $tabs.="<p align=center style=font-weight:bold;><font size='3'>Renaca Anggaran Biaya Survey</font> </p>";
        

        $strcheck=selectQuery($dbname,"gis_survey","*","notransaksi='".$notransaksi."'");
        $rescheck=fetchData($strcheck);
        $tabs.="<table cellspacing=0 border=1  width=100%>";
        
        $strtenagakerja=selectQuery($dbname,"gis_survey_tenagakerja","*","notransaksi='".$notransaksi."' order by statuspekerja desc");
        $rstenagakerja=fetchData($strtenagakerja);
        $tabs.="<tr>";
        $tabs.="<th align=center></th>";
        $tabs.="<th align=left colspan=7>Rencana Anggaran Survey</th>";
        $tabs.="</tr>";
        $tabs.="<tr >";
        $tabs.="<th>No</th>";
        $tabs.="<th align=center >Jenis Biaya</th>";
        $tabs.="<th align=center >Jumlah</th>";
        $tabs.="<th align=center >Satuan</th>";
        $tabs.="<th align=center >Harga</th>";
        $tabs.="<th align=center >Hari Kerja</th>";
        $tabs.="<th align=center >Sub Total</th>";
        $tabs.="<th align=center >Keterangan</th>";
        $tabs.="</tr>";
        $stranggaran=selectQuery($dbname,"gis_survey_anggaranht","*","notransaksi='".$notransaksi."'");
        $resanggaran=fetchData($stranggaran);
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='tenagakerja'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<tr>";
        $tabs.="<td>1</td>";
        $tabs.="<th>Tenaga Kerja</th>";
        $tabs.="<td>".$resanggarandt[0]['jumlah']."</td>";
        $tabs.="<td>Orang</td>";
        $tabs.="<td>".number_format($resanggarandt[0]['biaya'])."</td>";
        $tabs.="<td>".$resanggarandt[0]['hk']."</td>";
        $tabs.="<td>".number_format($resanggarandt[0]['subtotal'])."</td>";
        $tabs.="<td>".$resanggarandt[0]['keterangan']."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td>2</td>";
        $tabs.="<th>Konsumsi</th>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="</tr>";
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='konsumsi' and kode='surveyor'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<tr>";
        $tabs.="<td></td>";
        $tabs.="<td >Surveyor</td>";
        $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
        $tabs.="<td >Orang</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
        $tabs.="</tr>";
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='konsumsi' and kode='tenagakerjarintis'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<tr>";
        $tabs.="<td></td>";
        $tabs.="<td >Tenaga Kerja Rintis</td>";
        $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
        $tabs.="<td >Orang</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td>3</td>";
        $tabs.="<th>Peralatan</th>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="<td></td>";
        $tabs.="</tr>";
        $stralat=selectQuery($dbname,"gis_survey_alat","*","notransaksi='".$notransaksi."' and status='Consumable'");
        $resalat=fetchData($stralat);
        foreach ($resalat as $keyalat => $valalat) {
            $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='peralatan' and kode='".$valalat['kodealat']."'");
            $resanggarandt=fetchData($stranggarandt);
            $tabs.="<tr>";
            $tabs.="<td></td>";
            $tabs.="<td >".$valalat['namaalat']."</td>";
            $tabs.="<td>".$resanggarandt[0]['jumlah']."</td>";
            $tabs.="<td>".$resanggarandt[0]['satuan']."</td>";
            $tabs.="<td>".number_format($resanggarandt[0]['biaya'])."</td>";
            $tabs.="<td></td>";
            $tabs.="<td>".number_format($resanggarandt[0]['subtotal'])."</td>";
            $tabs.="<td>".$resanggarandt[0]['keterangan']."</td>";
            $tabs.="</tr>";
        }
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='transport'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<tr>";
        $tabs.="<td>4</td>";
        $tabs.="<th >Transportasi/Sewa Motor</th>";
        $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
        $tabs.="<td >Unit</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
        $tabs.="</tr>";
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='bbm'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<tr>";
        $tabs.="<td>5</td>";
        $tabs.="<th >Bahan Bakar Minyak</th>";
        $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
        $tabs.="<td >Liter</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
        $tabs.="</tr>";
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='biayacadangan'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<tr>";
        $tabs.="<td>6</td>";
        $tabs.="<th >Bahan Bakar Minyak</th>";
        $tabs.="<td >".$resanggarandt[0]['jumlah']."</td>";
        $tabs.="<td >".$resanggarandt[0]['satuan']."r</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['biaya'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['hk']."</td>";
        $tabs.="<td >".number_format($resanggarandt[0]['subtotal'])."</td>";
        $tabs.="<td >".$resanggarandt[0]['keterangan']."</td>";
        $tabs.="</tr>";
        $tabs.="<tr bgcolor='#77ff77'>";
        $tabs.="<td></td>";
        $tabs.="<td colspan=5>Total</td>";
        $tabs.="<td>".number_format($resanggaran[0]['totalbiaya'])."</td>";
        $tabs.="<td ></td>";
        $tabs.="</tr>";
        $tabs.="</table>";
        $tabs.="<hr>";
        $tabs.="<table cellspacing=0 style=min-height:100px; border=0  width=100%>";
        $tabs.="<tr>";
        $tabs.="<td align=center colspan=7>Yang Mengajukan</td>";
        $tabs.="<td align=center colspan=7>Persetujuan 1</td>";
        $tabs.="<td align=center colspan=7>Persetujuan 2</td>";
        $tabs.="<td align=center colspan=7>Persetujuan 3</td>";
        $tabs.="</tr>";
        $data['karyawanid']=array();
        $data['tanggal']=array();
        $data['status']=array();
        $src="SELECT * FROM `approval` WHERE `notransaksi` = '".$notransaksi."' order by level";
        $resc=fetchData($src);
        foreach ($resc as $kye => $vle) {
           $data['karyawanid'][]=$vle['karyawanid'];

           $std="SELECT * FROM `setup_ttd` WHERE `karyawanid` = '".$vle['karyawanid']."'";
           $retd=fetchData($std);
           $data['ttd'][]=$retd[0]['file'];

           $data['tanggal'][]=$vle['tanggal'];
           $data['status'][]=$vle['status'];
           $data['komentar'][]=$vle['komentar'];
        }
        $tabs.="<tr>";
        $tabs.="<td align=center colspan=7>".tanggalnormal($rescheck[0]['createdtime'])."</td>";
        foreach ($data['tanggal'] as $kuy => $vul) {
            $tabs.="<td align=center colspan=7>".tanggalnormal($vul)."</td>";
        }
        $std="SELECT * FROM `setup_ttd` WHERE `karyawanid` = '".$rescheck[0]['createdby']."'";
        $retd=fetchData($std);
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td align=center colspan=7><img src='".$retd[0]['file']."' width=110px height=100px/></td>";
        foreach ($data['status'] as $kuy => $vul) {
            if($vul==1)
            {
            $tabs.="<td align=center colspan=7><img src='".$data['ttd'][$kuy]."' width=110px height=100px/></td>";
            }
            else
            {
            $tabs.="<td align=center colspan=7 height=115px><b><u>".$arrstatusaju[$vul]."</u></b><br>Komentar : <br>".$data['komentar'][$kuy]."</td>";
            }
        }
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td align=center colspan=7>".$optKry[$rescheck[0]['createdby']]."</td>";
        foreach ($data['karyawanid'] as $kuy => $vul) {
            $tabs.="<td align=center colspan=7>".$optKry[$vul]."</td>";
        }
        $tabs.="</tr>";
        $tabs.="</table>";
        

        $tabs.="</div>";

			$dompdf = new Dompdf();
            $dompdf->loadHtml($tabs);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("form survey",array("Attachment"=>0));