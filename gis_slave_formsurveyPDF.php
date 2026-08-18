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
        }

        $arrHead = setheadreport('',$rInduk['induk']);
        $path=$arrHead['logo'];
        $tabs="<div>";
        $tabs.="<p align=center style=font-weight:bold;><font size='5'>SURVEY FORM</font> </p>";
        $tabs.="<p align=center style=font-weight:bold;>".$nama."</p>";
        $tabs.="<p align=center style=font-weight:bold;>NO TRANSAKSI :".$notransaksi."</p>";
        $tabs.="<hr>";


        $tabs.="<table border=0 align=center>";
        $str=selectQuery($dbname,"pad_5typesurvey","kodesurvey,namasurvey,meliputi");
        $res=fetchData($str);
        $tabs.="<tr>";
            $tabs.="<td style=text-decoration:underline;font-weight:bold; width=200px align=right>Kegiatan Survey:</td>";
            $tabs.="<td width=15px align=center></td>";
            $tabs.="<td style=text-decoration:underline;font-weight:bold; width=200px align=left>Meliputi:</td>";
            $tabs.="</tr>";
        foreach ($res as $key => $val) {
            $tabs.="<tr>";
            $tabs.="<td id=namatipe align=right>".$val['namasurvey']."</td>";
            $strcheck=selectQuery($dbname,"gis_survey_kegiatan","typesurvey","typesurvey='".$val['kodesurvey']."' and notransaksi='".$notransaksi."'");
            $rescheck=fetchData($strcheck);
            $jlhcheck=count($rescheck);
            if($jlhcheck>0)
            {
                $tabs.="<td width=15px align=center>".makeElement('checktipe_'.$key,'checkbox','1')."</td>";
            }
            else
            {
                $tabs.="<td width=15px align=center>".makeElement('checktipe_'.$key,'checkbox')."</td>";
            }

            $tabs.="<td id=meliputi align=left>".$val['meliputi']."</td>";
            $tabs.="</tr>";
        }
        $tabs.="</table>";

        $strcheck=selectQuery($dbname,"gis_survey","*","notransaksi='".$notransaksi."'");
        $rescheck=fetchData($strcheck);
        $tabs.="<hr>";
        $tabs.="<p align=center style=font-weight:bold;><font size='3'>KETENTUAN UMUM</font> </p>";
        $tabs.="<hr>";
        $tabs.="<table cellspacing=0 border=1  width=100%>";
        $tabs.="<tr>";
        $tabs.="<th align=center>1</th>";
        $tabs.="<th align=left  colspan=7>Lokasi Kegiatan Survey</th>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>Status Lokasi : ".$arrstatuslokasi[$rescheck[0]['statuslokasi']]."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>Provinsi : ".$rescheck[0]['provinsi']."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>Kabupaten : ".$rescheck[0]['kabupaten']."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>Kecamatan : ".$rescheck[0]['kecamatan']."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>Desa : ".$rescheck[0]['desa']."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<th align=center>2</th>";
        $tabs.="<th align=left colspan=7>Jangka Waktu Pelaksanaan</th>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>Tanggal Mulai : ".tanggalnormal($rescheck[0]['tanggalmulai'])."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>Tanggal Selesai : ".tanggalnormal($rescheck[0]['tanggalselesai'])."</td>";
        $tabs.="</tr>";
        $tabs.="<tr>";
        $tabs.="<th align=center>3</th>";
        $tabs.="<th align=left colspan=7>Personil Survey</th>";
        $tabs.="</tr>";
        $strtenagakerjax=selectQuery($dbname,"gis_survey_tenagakerja","team","notransaksi='".$notransaksi."' group by team ");
        $rstenagakerjax=fetchData($strtenagakerjax);
        foreach ($rstenagakerjax as $ky => $vl) {
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            $tabs.="<td align=left colspan=7>Team ".$vl['team']." :</td>";
            $tabs.="</tr>";
            $strtenagakerja=selectQuery($dbname,"gis_survey_tenagakerja","*","notransaksi='".$notransaksi."' and team='".$vl['team']."' order by kodetenagakerja asc");
            $rstenagakerja=fetchData($strtenagakerja);
            foreach ($rstenagakerja as $keypekerja => $valpekerja) {
            $tabs.="<tr>";
            $tabs.="<td ></td>";
            if($valpekerja['statuspekerja']=='surveyor' || $valpekerja['statuspekerja']=='pendamping')
            {
            $tabs.="<td align=left colspan=7>".($keypekerja+1).". ".$valpekerja['statuspekerja']." : ".$valpekerja['namapekerja']."</td>";
            }
            else
            {
            $tabs.="<td align=left colspan=7>".($keypekerja+1).". ".$valpekerja['statuspekerja']." : ".$valpekerja['namapekerja']."</td>";
            }
            $tabs.="</tr>";
            }
        }
        $tabs.="<tr>";
        $tabs.="<th align=center>4</th>";
        $tabs.="<th align=left colspan=7>Rencana Anggaran Survey</th>";
        $tabs.="</tr>";
        $tabs.="<tr >";
        $tabs.="<th></th>";
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
        $tabs.="<td></td>";
        $tabs.="<td>Tenaga Kerja</td>";
        $tabs.="<td>".$resanggarandt[0]['jumlah']."</td>";
        $tabs.="<td>Orang</td>";
        $tabs.="<td>".number_format($resanggarandt[0]['biaya'])."</td>";
        $tabs.="<td>".$resanggarandt[0]['hk']."</td>";
        $tabs.="<td>".number_format($resanggarandt[0]['subtotal'])."</td>";
        $tabs.="<td>".$resanggarandt[0]['keterangan']."</td>";
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
        $stranggarandt=selectQuery($dbname,"gis_survey_anggarandt","*","kodeinduk='".$resanggaran[0]['kodeanggaran']."' and jenisbiaya='transport'");
        $resanggarandt=fetchData($stranggarandt);
        $tabs.="<tr>";
        $tabs.="<td></td>";
        $tabs.="<td >Transportasi/Sewa Motor</td>";
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
        $tabs.="<td></td>";
        $tabs.="<td >Bahan Bakar Minyak</td>";
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
        $tabs.="<td></td>";
        $tabs.="<td >Bahan Bakar Minyak</td>";
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
        $tabs.="<tr>";
        $tabs.="<th align=center>5</th>";
        $tabs.="<th align=left colspan=7>Alat Survey Yang Dibutuhkan</th>";
        $tabs.="</tr>";
        $stralat=selectQuery($dbname,"gis_survey_alat","*","notransaksi='".$data['notransaksi']."'");
        $resalat=fetchData($stralat);
        foreach ($resalat as $keyalat => $valalat) {
        $tabs.="<tr>";
        $tabs.="<td ></td>";
        $tabs.="<td align=left colspan=7>".($keyalat+1).". ".$valalat['namaalat']." </td>";
        $tabs.="</tr>";
        }
        $tabs.="</table>";
        $tabs.="<hr>";
        $tabs.="<p align=center style=font-weight:bold;><font size='3'> Penjelasan Singkat Teknis & Target Pelaksanaan Kegiatan Survey </font> </p>";
        $tabs.="<hr>";
        $tabs.="<table cellspacing=0 style=min-height:100px; border=1  width=100%>";
        $tabs.="<tr>";
        if($rescheck[0]['penjelasan']=='')
        {
        $tabs.="<td align=left style=min-height:200px; colspan=7>".$rescheck[0]['penjelasan']." </td>";
        }
        else
        {
        $tabs.="<td align=left style=min-height:200px; colspan=7><textarea>".$rescheck[0]['penjelasan']."</textarea></td>";
        }
        $tabs.="</tr>";
        $tabs.="</table>";
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