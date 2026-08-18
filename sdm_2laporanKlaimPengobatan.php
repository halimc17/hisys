<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_pengobatan.js'></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?php
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['adm_peng']).'</span>');
$optJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optthn = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
for ($x = -1; $x < 10; $x++) {
    if ($x == 0)
        $qwe = 'selected = "selected"';
    else
        $qwe = '';
    $mk = mktime(0, 0, 0, 1, 15, date('Y') - $x);
    $optthn.="<option value='" . (date('Y', $mk)) . "'>" . (date('Y', $mk)) . "</option>";
}

$optkodeorg = "";
if (substr($_SESSION['empl']['lokasitugas'], 3, 1) == 'O') {
    $optkodeorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

    $sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where CHAR_LENGTH(kodeorganisasi)='4' 
    and tipe in ('KEBUN', 'PABRIK', 'KANWIL', 'TRAKSI','HOLDING')  
    order by namaorganisasi asc";
} else {
    $sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi like '" . $_SESSION['empl']['lokasitugas'] . "'";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    if (substr($_SESSION['empl']['lokasitugas'], 0, 4) == $rOrg['kodeorganisasi'])
        $qwe = 'selected = "selected"';
    else
        $qwe = '';
    $optkodeorg.="<option value=" . $rOrg['kodeorganisasi'] . " " . $qwe . ">" . $rOrg['namaorganisasi'] . "</option>";
//    $optkodeorg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

//ambil daftar rumah sakit
if (substr($_SESSION['empl']['lokasitugas'],2,2) == 'HO' or substr($_SESSION['empl']['lokasitugas'],2,2) == 'RO') {
    $str = "select distinct namars,kota from " . $dbname . ".sdm_pengobatanht a left join
      " . $dbname . ".sdm_5rs b on a.rs=b.id 
          order by b.namars";
} else {
    $str = "select distinct b.namars as namars,b.kota from " . $dbname . ".sdm_pengobatanht a left join
     " . $dbname . ".sdm_5rs b on a.rs=b.id where a. kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "' order by b.namars";
}
$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$optrs = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
while ($bar = $res1->fetch()) {
    $optrs.="<option value='" . $bar->namars . "'>" . $bar->namars . " [" . $bar->kota . "]</option>";
}

#ambil data karyawn
$optKaryawan = "<option value=''>Seluruhnya</option>";
$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".sdm_pengobatanht a left join
      " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid order by namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optKaryawan.="<option value='" . $bar->karyawanid . "'>" . $bar->namakaryawan . "[" . $bar->lokasitugas . "]</option>";
}

if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING')
    $lokasi = substr($_SESSION['empl']['lokasitugas'], 0, 4);
else
    $lokasi = '';
// echo $optthn;
//<button class=mybutton id='preview' onclick=loadPengobatanPrint()>".$_SESSION['lang']['preview']."</button>
$frm[0] = "<fieldset>
    <legend>" . $_SESSION['lang']['list'] . "</legend>
    " . $_SESSION['lang']['thnplafon'] . " : <select id=optplafon onchange=loadPengobatanPrint()>" . $optthn . "</select>
    <img src=images/excel.jpg onclick=printKlaim() class=resicon>
    
    " . $_SESSION['lang']['kodeorganisasi'] . " : <select id=optkodeorg onchange=loadPengobatanPrint() style='width:200px;'>" . $optkodeorg . "</select>
    " . $_SESSION['lang']['rumahsakit'] . " : <select id=optrs onchange=loadPengobatanPrint() style='width:200px;'>" . $optrs . "</select>
    " . $_SESSION['lang']['namakaryawan'] . " : <select id=optkary onchange=loadPengobatanPrint() style='width:200px;'>" . $optKaryawan . "</select>
    <iframe id=frmku frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\">
    <table class=sortable cellspacing=1 border=0 width=250%>
    <thead>
    <tr class=rowheader>
        <td></td>
        <td align=center>No</td>
        <td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
        <td align=center width=70px>" . $_SESSION['lang']['periode'] . "</td>
        <td align=center width=80px>" . $_SESSION['lang']['tanggal'] . "</td>
        <td align=center>" . $_SESSION['lang']['lokasitugas'] . "</td>
        <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
        <td align=center width=30px>" . $_SESSION['lang']['jeniskelamin'] . "</td>
        <td align=center width=30px>" . $_SESSION['lang']['usia'] . " (" . $_SESSION['lang']['tahun'] . ")</td>
        <td align=center>" . $_SESSION['lang']['tanggalmasuk'] . "</td>
        <td align=center>" . $_SESSION['lang']['tanggalkeluar'] . "</td>        
        <td align=center width=30px>" . $_SESSION['lang']['masakerja'] . " (" . $_SESSION['lang']['tahun'] . ")</td>
        <td align=center>" . $_SESSION['lang']['jabatan'] . "</td>
        <td align=center>" . $_SESSION['lang']['pasien'] . "</td>
        <td align=center>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['pasien'] . "</td>
        <td align=center>" . $_SESSION['lang']['rumahsakit'] . "</td>
        <td align=center>" . $_SESSION['lang']['jenisbiayapengobatan'] . "</td>
        <td align=center>Biaya Rumah Sakit</td>
        <td align=center>Biaya Pendaftaran</td>  
        <td align=center>Biaya Lab.</td>  
        <td align=center>Biaya Obat</td>  
        <td align=center>Jasa Dokter</td>
        <td align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
        <td align=center>" . $_SESSION['lang']['perusahaan'] . "</td>
        <td align=center>" . $_SESSION['lang']['karyawan'] . "</td>
        <td align=center>BPJS</td>      
        <td>" . $_SESSION['lang']['diagnosa'] . "</td>
        <td>" . $_SESSION['lang']['keterangan'] . "</td>
    </tr>
    </thead>
    
    <tbody id='container'><script>loadPengobatanPrint()</script>";

$frm[0].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset> 	 
    ";

//ambil daftar tab 1
$str1 = "select a.diagnosa, count(*) as kali,d.diagnosa as ketdiag from " . $dbname . ".sdm_pengobatanht a 
	  left join " . $dbname . ".sdm_5diagnosa d
	  on a.diagnosa=d.id 
          left join " . $dbname . ".datakaryawan e
	  on a.karyawanid=e.karyawanid
	  where a.periode like '" . date('Y') . "%'
	  and e.lokasitugas='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "'
        group by a.diagnosa order by kali desc
    ";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);

$frm[1] = "<fieldset>
    <legend>Ranking " . $_SESSION['lang']['diagnosa'] . "</legend>
    " . $_SESSION['lang']['thnplafon'] . " :
    <select id=optplafon1 onchange=loadPengobatanPrint1()>" . $optthn . "</select>
    <img src=images/excel.jpg onclick=printKlaim1() class=resicon>
    " . $_SESSION['lang']['kodeorganisasi'] . " :
    <select style=width:200px id=optkodeorg1 onchange=loadPengobatanPrint1()>" . $optkodeorg . "</select>
    <iframe id=frmku1 frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\">
	<table class=sortable cellspacing=1 border=0>
    <thead>
    <tr class=rowheader>
        <td>Rank</td>
        <td>Diagnose</td>
        <td>Number of visit</td>
    </tr>
    </thead>
    <tbody id='container1'>";
//        <td width=50></td>
$no = 0;
while ($bar1 = $res1->fetch()) {
    $no+=1;
    $frm[1].="<tr class=rowcontent>
            <td>" . $no . "</td>
            <td>" . $bar1->ketdiag . "</td>
            <td align=right>" . $bar1->kali . "</td>
        </tr>";
//            <td>&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPengobatan1('".$bar->notransaksi."',event)></td>
}
$frm[1].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset> 	 
    ";

//ambil daftar tab 2
$str2 = "select a.karyawanid,count(a.karyawanid) as xberobat, sum(jlhbayar) as klaim,sum(totalklaim) as biaya, d.namakaryawan,d.lokasitugas,
    COALESCE(ROUND(DATEDIFF('" . date('Y-m-d') . "',d.tanggallahir)/365.25,1),0) as umur
    from " . $dbname . ".sdm_pengobatanht a 
	  left join " . $dbname . ".datakaryawan d on a.karyawanid=d.karyawanid 
	  where a.periode like '" . date('Y') . "%'
	  and d.lokasitugas like '" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "%'
        group by a.karyawanid order by klaim desc
    ";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_OBJ);

$frm[2] = "<fieldset>
    <legend>Ranking " . $_SESSION['lang']['biaya'] . " / " . $_SESSION['lang']['karyawan'] . "</legend>
    " . $_SESSION['lang']['thnplafon'] . ":
    <select id=optplafon2 onchange=loadPengobatanPrint2()>" . $optthn . "</select>
    <img src=images/excel.jpg onclick=printKlaim2() class=resicon>
    " . $_SESSION['lang']['kodeorganisasi'] . ":
    <select style=width:200px id=optkodeorg2 onchange=loadPengobatanPrint2()>" . $optkodeorg . "</select>
    <iframe id=frmku2 frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\"><table class=sortable cellspacing=1 border=0 width=60%>
    <thead>
    <tr class=rowheader>
        <td align=center>Rank</td>
        <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
        <td align=center width=50px>" . $_SESSION['lang']['umur'] ." ( " . $_SESSION['lang']['tahun'] ." )</td>
        <td align=center width=75px>" . $_SESSION['lang']['lokasitugas'] . "</td>
        <td align=center width=50px>" . $_SESSION['lang']['jumlah'] . " Berobat</td>
        <td align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
        <td align=center>Detail</td>
    </tr>
    </thead>
    <tbody id='container2'>";
//        <td width=50></td>
$no = $total = 0;
while ($bar2 = $res2->fetch()) {
    $no+=1;
    $frm[2].="<tr class=rowcontent>
            <td>" . $no . "</td>
            <td>" . $bar2->namakaryawan . "</td>
            <td align=center>" . $bar2->umur . "</td>       
            <td align=center>" . $bar2->lokasitugas . "</td>
			<td align=right>" . $bar2->xberobat . "</td>
			<td align=right>" . number_format($bar2->biaya) . "</td>
		    <td align=right>" . number_format($bar2->klaim) . "</td>
               <td align=center>&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPerorang('" . $bar2->karyawanid . "',event)></td>
            </tr>";
    $totalby+=$bar2->biaya;
    $total+=$bar2->klaim;
}
$frm[2].="<tr class=rowcontent>
              <td></td>
               <td><b>" . $_SESSION['lang']['total'] . "</b></td>
               <td></td>
               <td></td>
               <td></td>
               <td align=right><b>" . number_format($totalby) . "</b></td>
               <td align=right><b>" . number_format($total) . "</b></td>
                <td></td></tr>";
$frm[2].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset> 	 
    ";

//ambil daftar tab 3
$str3 = "select a.diagnosa,  sum(jasars) as rs, sum(jasadr) as dr, sum(jasalab) as lab, sum(byobat) as obat, sum(bypendaftaran) as administrasi, a.periode, sum(a.jlhbayar) as bayar, sum(totalklaim) as klaim,d.diagnosa as ketdiag from " . $dbname . ".sdm_pengobatanht a 
	  left join " . $dbname . ".sdm_5diagnosa d
	  on a.diagnosa=d.id 
        left join " . $dbname . ".datakaryawan c
        on a.karyawanid=c.karyawanid
              where a.periode like '" . $periode . "%'
              and c.lokasitugas like '" . $kodeorg . "%'
        group by a.diagnosa order by klaim desc
    ";
// echo $str3;
$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
$res3->setFetchMode(PDO::FETCH_OBJ);

$frm[3] = "<fieldset>
    <legend>Ranking " . $_SESSION['lang']['biaya'] . " / " . $_SESSION['lang']['diagnosa'] . "</legend>
    " . $_SESSION['lang']['thnplafon'] . " :
    <select id=optplafon3 onchange=loadPengobatanPrint3()>" . $optthn . "</select>
    <img src=images/excel.jpg onclick=printKlaim3() class=resicon>
    " . $_SESSION['lang']['kodeorganisasi'] . " :
    <select style=width:200px id=optkodeorg3 onchange=loadPengobatanPrint3()>" . $optkodeorg . "</select>
    <iframe id=frmku3 frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\"><table class=sortable cellspacing=1 border=0>
    <thead>
    <tr class=rowheader>
        <td>Rank</td>
        <td>Diagnose</td>
        <td align=center>" . $_SESSION['lang']['biayars'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayadr'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayalab'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayaobat'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayapendaftaran'] . "</td>
        <td align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
    </tr>
    </thead>
    <tbody id='container3'>";
//        <td width=50></td>
$no = 0;
while ($bar3 = $res3->fetch()) {
    $no+=1;
    $frm[3].="<tr class=rowcontent>
            <td align=center>" . $no . "</td>
            <td>" . $bar3->ketdiag . "</td>
            <td align=right>" . number_format($bar3->rs) . "</td>
            <td align=right>" . number_format($bar3->dr) . "</td>
            <td align=right>" . number_format($bar3->lab) . "</td>
            <td align=right>" . number_format($bar3->obat) . "</td>
            <td align=right>" . number_format($bar3->administrasi) . "</td>
            <td align=right>" . number_format($bar3->klaim) . "</td>
            <td align=right>" . number_format($bar3->bayar) . "</td>
        </tr>";
			
			$trs+=$bar3->rs;
		$tdr+=$bar3->dr;
		$tlab+=$bar3->lab;
		$tobat+=$bar3->obat;
		$tadm+=$bar3->administrasi;
		$ttl+=$bar3->klaim;
		$tbyr+=$bar3->bayar;
//            <td>&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPengobatan1('".$bar->notransaksi."',event)></td>
}
	$frm[3].="<tr class=rowcontent>
            <td align=center colspan=2><b>TOTAL</b></td>
            <td><b>" . number_format($trs) . "</b></td>
            <td><b>" . number_format($tdr) . "</b></td>
            <td><b>" . number_format($tlab) . "</b></td>
            <td><b>" . number_format($tobat) . "</b></td>
            <td><b>" . number_format($tadm) . "</b></td>
            <td><b>" . number_format($ttl) . "</b></td>
            <td><b>" . number_format($tbyr) . "</b></td>
			</tr>";
		
$frm[3].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset> 	 
    ";

$frm[4] = "<fieldset>
    <legend>Monthly Trend</legend>
    " . $_SESSION['lang']['thnplafon'] . " :
    <select id=optplafon4 onchange=loadPengobatanPrint4()>" . $optthn . "</select>
    <img src=images/excel.jpg onclick=printKlaim4() class=resicon> 
    " . $_SESSION['lang']['kodeorganisasi'] . " :
    <select  style=width:200px id=optkodeorg4 onchange=loadPengobatanPrint4()>" . $optkodeorg . "</select>
    <iframe id=frmku4 frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\"><table class=sortable cellspacing=1 border=0 width=900px>
    <thead>
    <tr class=rowheader>
        <td>No</td>
        <td>Period</td>
		<td align=center>" . $_SESSION['lang']['biayars'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayadr'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayalab'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayaobat'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayapendaftaran'] . "</td>
        <td align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
    </tr>
    </thead>
    <tbody id='container4'>";
$frm[4].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset>";
$frm[5] = "<fieldset>
    <legend>Trend " . $_SESSION['lang']['biaya'] . "</legend>
	" . $_SESSION['lang']['thnplafon'] . " :
    <select id=optplafon5 onchange=loadPengobatanPrint5()>" . $optthn . "</select>
	<img src=images/excel.jpg onclick=printKlaim5() class=resicon> &nbsp;
    " . $_SESSION['lang']['nama'] . " :
    <select id=karyawanid onchange=loadPengobatanPrint5()>" . $optKaryawan . "</select>
    <iframe id=frmku5 frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\"><table class=sortable cellspacing=1 border=0 width=70%>
    <thead>
    <tr class=rowheader>
        <td align=center>No</td>
        <td align=center>Period</td>
        <td align=center>" . $_SESSION['lang']['biayars'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayadr'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayalab'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayaobat'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayapendaftaran'] . "</td>
        <td align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
    </tr>
    </thead>
    <tbody id='container5'>";
$frm[5].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset>";


$frm[6] = "<fieldset>
    <legend>Per Jenis Perawatan</legend>
    " . $_SESSION['lang']['thnplafon'] . " :
    <select id=optplafon6 onchange=loadPengobatanPrint6()>" . $optthn . "</select>
    <img src=images/excel.jpg onclick=printKlaim6() class=resicon>
    " . $_SESSION['lang']['kodeorganisasi'] . " :
    <select style=width:200px id=optkodeorg6 onchange=loadPengobatanPrint6()>" . $optkodeorg . "</select>
    <iframe id=frmku6 frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\"><table class=sortable cellspacing=1 border=0>
    <thead>
    <tr class=rowheader>
        <td align=center rowspan=2>No</td>
        <td align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</td>
        <td align=center rowspan=2>" . $_SESSION['lang']['tahun'] . "</td>            
        <td align=center rowspan=2>Treatment Type</td>
        <td  align=center colspan=2>Jan</td>
        <td  align=center colspan=2>Feb</td>
        <td  align=center colspan=2>Mar</td>
        <td  align=center colspan=2>Apr</td>
        <td  align=center colspan=2>Mei</td>
        <td  align=center colspan=2>Jun</td>
        <td  align=center colspan=2>Jul</td>
        <td  align=center colspan=2>Aug</td>
        <td  align=center colspan=2>Sep</td>
        <td  align=center colspan=2>Oct</td>
        <td  align=center colspan=2>Nov</td>
        <td  align=center colspan=2>Dec</td>
        <td align=center colspan=2>" . $_SESSION['lang']['total'] . "</td>
    </tr>
	<tr>
	    <td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
	</tr>
    </thead>
    <tbody id='container6'>";
$frm[6].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset>";
$frm[7] = "<fieldset>
    <legend>Rincian Per Orang</legend>
    " . $_SESSION['lang']['thnplafon'] . " : <select id=optplafon8 onchange=loadPengobatanPrint8()>" . $optthn . "</select>
    <img src=images/excel.jpg onclick=printKlaim8() class=resicon>
    " . $_SESSION['lang']['namakaryawan'] . " : <select id=optkary8 onchange=loadPengobatanPrint8()>" . $optKaryawan . "</select>
    <iframe id=frmku8 frameborder=0 style='width:0px;height:0px;'></iframe>
    <div style=\"width:1235px;height:400px;overflow:auto\">
    <table class=sortable cellspacing=1 border=0'>
    <thead>
    <tr class=rowheader>
        <td align=center>No</td>
        <td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
        <td align=center>" . $_SESSION['lang']['periode'] . "</td>
        <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
        <td align=center>" . $_SESSION['lang']['lokasitugas'] . "</td>
        <td align=center>" . $_SESSION['lang']['tipekaryawan'] . "</td>
        <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
        <td align=center>" . $_SESSION['lang']['pasien'] . "</td>
        <td align=center>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['pasien'] . "</td>
        <td align=center>" . $_SESSION['lang']['rumahsakit'] . "</td>
        <td align=center>" . $_SESSION['lang']['jenisbiayapengobatan'] . "</td>
        <td align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
        <td align=center>" . $_SESSION['lang']['tanggalbayar'] . "</td>
    </tr>
    </thead>
    
    <tbody id='container8'><script>loadPengobatanPrint8()</script>";
$frm[7].="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset>";
//========================
$hfrm[0] = $_SESSION['lang']['detail'];
$hfrm[1] = "Rank " . $_SESSION['lang']['diagnosa'];
$hfrm[2] = "Rank " . $_SESSION['lang']['biaya'] . " / " . $_SESSION['lang']['karyawan'];
$hfrm[3] = "Rank " . $_SESSION['lang']['biaya'] . " / " . $_SESSION['lang']['diagnosa'];
$hfrm[4] = "Monthly Trend";
$hfrm[5] = "By Cost Type";
$hfrm[6] = "By Treatment Type";
$hfrm[7] = "Detail by Employee";
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 120, 1235);
//===============================================
CLOSE_BOX();
echo close_body();
?>