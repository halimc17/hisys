<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_pengobatan.js'></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?php

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pembayaranclaim']).'</span><br>');
//option periode akuntansi
//ambil daftar pengobatan dengan tahun sekarang

$namaBiaya = makeOption($dbname, 'sdm_5jenisbiayapengobatan', 'kode,nama');
$nmtk = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$arrklaim=array("0"=>"Karyawan","1"=>"Rumah Sakit","2"=>"Internal Clinic");


$optper=$optunit=$opttk="";
// for ($x = 0; $x <= 24; $x++) {
    // $t = mktime(0, 0, 0, date('m') - $x, 15, date('Y'));
    // $optper.="<option value='" . date('Y-m', $t) . "'>" . date('m-Y', $t) . "</option>";
// }


$str="select distinct(periode) as periode from ".$dbname.".sdm_pengobatanht order by  periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $optper.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	// $str="select distinct(kodeorg) as unit from ".$dbname.".sdm_pengobatanht ";
// }else{
	// $str="select distinct(kodeorg) as unit from ".$dbname.".sdm_pengobatanht  where kodeorg not like '%HO' ";
// }
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO') {
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by kodeorganisasi";
}
else if(substr($_SESSION['empl']['lokasitugas'],2,2)=='RO') {
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
            length(kodeorganisasi)=4 and induk='".$_SESSION['org']['kodeorganisasi']."' order by kodeorganisasi";
}else if(substr($_SESSION['empl']['lokasitugas'],2,2)=='LO'){//LO pakai regional saja
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment
	where regional='".$_SESSION['empl']['regional']."')  order by kodeorganisasi";
	/*
	 $strd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
	induk='".$_SESSION['empl']['kodeorganisasi']."' and length(kodeorganisasi)=4 and tipe not in ('HOLDING','KANWIL') order by kodeorganisasi";
	*/
	
} else {
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 
            length(kodeorganisasi)=4 and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi";
}

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".sdm_5jenisbiayapengobatan order by nama";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optJns='';
while($bar=$res->fetch()){
    $optJns.="<option value='".$bar->kode."'>".$bar->nama."</option>";
}

$str="select distinct(b.tipekaryawan) as tipekaryawan from ".$dbname.".sdm_pengobatanht a left join ".$dbname.".datakaryawan b 
		on a.karyawanid=b.karyawanid ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$opttk.="<option value='".$bar['tipekaryawan']."'>".@$nmtk[$bar['tipekaryawan']]."</option>";
}

echo"
<fieldset style=float:left>
	  <legend>" . $_SESSION['lang']['form'] . "</legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id='periode'>".$optper."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select id='unit'>".$optunit."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
		<td>:</td>
		<td><select id=jenisbiaya>".$optJns."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tipekaryawan']."</td>
		<td>:</td>
		<td><select id='tpkar'>".$opttk."</select></td>
	</tr>
</table>
";
//Unit, Jenis Biaya Pengobatan, Tipe Kary







echo"
          <button onclick=getDaftar() class=mybutton>" . $_SESSION['lang']['proses'] . "</button>
		  </fieldset>";

CLOSE_BOX();
OPEN_BOX();

		  
echo "<div id=cont>";


/*
if (isset($_GET['periode'])) {
    if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
        $str = "select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag,c.karyawanid as karyawanid,
              a.totalklaim as totalklaim,a.tahunplafon as tahunplafon 
              from " . $dbname . ".sdm_pengobatanht a 
              left join " . $dbname . ".sdm_5rs b on a.rs=b.id 
              left join " . $dbname . ".datakaryawan c on a.karyawanid=c.karyawanid
              left join " . $dbname . ".sdm_5diagnosa d on a.diagnosa=d.id
              where periode='" . $_GET['periode'] . "' and (c.tipekaryawan in ('0','7','8') or c.alokasi=1)
              order by a.updatetime desc, a.tanggal desc";
    } else {
        $str = "select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag,c.karyawanid as karyawanid,
              a.totalklaim as totalklaim,a.tahunplafon as tahunplafon 
              from " . $dbname . ".sdm_pengobatanht a 
              left join " . $dbname . ".sdm_5rs b on a.rs=b.id 
              left join " . $dbname . ".datakaryawan c on a.karyawanid=c.karyawanid
              left join " . $dbname . ".sdm_5diagnosa d on a.diagnosa=d.id
              where periode='" . $_GET['periode'] . "' and a.kodeorg='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "'
              order by a.updatetime desc, a.tanggal desc";
    }
*/

#flow awal jika HO hanya untuk tpkaryawan 0,7,8 (stat keatas), jika unit maka mengunci lokasitugas

$where='';
if(@$_GET['periode']!=''){
	$where.=" and periode='" . $_GET['periode'] . "'";
}
if(@$_GET['unit']!=''){
	$where.=" and a.kodeorg='" . $_GET['unit'] . "'";
}
if(@$_GET['jenisbiaya']!=''){
	$where.=" and a.kodebiaya='" . $_GET['jenisbiaya'] . "'";
}
if(@$_GET['tpkar']!=''){
	$where.=" and c.tipekaryawan='" . $_GET['tpkar'] . "'";
}


$str = "select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag,c.karyawanid as karyawanid,
              a.totalklaim as totalklaim,a.tahunplafon as tahunplafon 
              from " . $dbname . ".sdm_pengobatanht a 
               left join  ".$dbname.".log_5supplier b on a.rs=b.supplierid 
              left join " . $dbname . ".datakaryawan c on a.karyawanid=c.karyawanid
              left join " . $dbname . ".sdm_5diagnosa d on a.diagnosa=d.id
              where 1=1 ".$where."
              order by a.updatetime desc, a.tanggal desc";	
	
	//echo $str;
	
// $a=$_GET['periode'];
// $b=$_GET['unit'];
// $c=$_GET['jenisbiaya'];
// $d=$_GET['tpkar'];

// echo "Periode : ".$a.", Unit : ".$b.", Jenis : ".$c.", pk : ".$d."";

	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	echo"<fieldset>
	  <legend>" . $_SESSION['lang']['belumbayar'] . "</legend>
	  <div style='overflow:auto;height:470px;max-width:1235px'; >
	  <table class=sortable cellspacing=1 border=0>
	  <thead>
	    <tr class=rowheader>
		<td width=30>Detail</td>
		  <td align=center>No</td>
		  <td align=center width=80>" . $_SESSION['lang']['notransaksi'] . "</td>
		  <td align=center width=50>" . $_SESSION['lang']['periode'] . "</td>
		  <td align=center width=65>" . $_SESSION['lang']['tanggal'] . "</td>
		  <td align=center width=150>" . $_SESSION['lang']['namakaryawan'] . "</td>
		  <td align=center width=150>" . $_SESSION['lang']['rumahsakit'] . "</td>
		  <td align=center width=50>" . $_SESSION['lang']['jenisbiayapengobatan'] . "</td>
		   <td align=center>" . $_SESSION['lang']['klaim'] . "</td>
		  <td align=center>" . $_SESSION['lang']['beban'] . " " . $_SESSION['lang']['perusahaan'] . "</td>    
          <td align=center>" . $_SESSION['lang']['beban'] . " " . $_SESSION['lang']['karyawan'] . "</td>
          <td align=center>" . $_SESSION['lang']['beban'] . " " . $_SESSION['lang']['jms'] . "</td>    
		  <td align=center width=90>" . $_SESSION['lang']['total'] . "</td>
		  <td align=center width=100>Verifikasi HRD</td>
		  <td align=center>" . $_SESSION['lang']['tanggal'] . " Ver HRD</td>
		  <td>" . $_SESSION['lang']['action'] . "</td>
		   <td align=center width=120>" . $_SESSION['lang']['dibayar'] . "</td>
		  <td align=center>" . $_SESSION['lang']['tanggalbayar'] . "</td>
		</tr>
	  </thead>
	  <tbody id='container'>";
    $no = 0;
    while ($bar = $res->fetch()) {
		
		// #ambil data kasbank
		// $strk="select a.jumlah,a.tanggal,a.notransaksi from ".$dbname.".keu_kasbankdt a left join ".$dbname.".keu_kasbankht b on
				// a.notransaksi=b.notransaksi where b.posting=0 and a.nodok='".$bar->notransaksi."' ";
		// $resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
		// $resk->setFetchMode(PDO::FETCH_ASSOC);
		// $bark = $resk->fetch();
		
        $no+=1;
        echo"<tr class=rowcontent>
	   <td>";
        echo"&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPengobatan('" . $bar->notransaksi . "',event)>";

        echo"</td><td>" . $no . "</td>
		  <td>" . $bar->notransaksi . "</td>
		  <td>" . substr($bar->periode, 5, 2) . "-" . substr($bar->periode, 0, 4) . "</td>
		  <td>" . tanggalnormal($bar->tanggal) . "</td>
		  <td>" . $bar->namakaryawan . "</td>
		  <td>" . $bar->namasupplier . "</td>
		  <td>" . $namaBiaya[$bar->kodebiaya] . "</td>
		  <td align=right>".$arrklaim[$bar->klaimoleh]."</td>     
		  <td align=right>" . number_format($bar->bebanperusahaan, 2, '.', ',') . "</td>     
          <td align=right>" . number_format($bar->bebankaryawan, 2, '.', ',') . "</td>
          <td align=right>" . number_format($bar->bebanjamsostek, 2, '.', ',') . "</td>
          <td align=right>" . number_format($bar->totalklaim, 2, '.', ',') . "</td>           
          
           ";
		   
		   //<img src="images/skyblue/posting.png" class="zImgBtn" onclick="postingData(0)" title="Posting">
        if ($bar->tanggalbayar != '0000-00-00') {
            echo"<td align=right>" . number_format($bar->jlhbayar, 2, '.', ',') . "</td>
		    <td align=right>" . tanggalnormal($bar->tanggalbayar) . "</td>";
			echo"<td>";
			if($bar->klaimoleh==1){
				if($bar->postingjurnal==0){
					echo "<img src='images/skyblue/posting.png' title='Posting' class=resicon onclick=posting('" . $bar->notransaksi . "','" . $bar->karyawanid . "','" . $bar->periode . "')>";
				}else{
					echo "<img src='images/skyblue/posted.png' title='Posting' class=resicon>";
				}
			}
		   echo"</td>";
        } else {
            echo"<td>
			<input style=width:50px type=text id=bayar" . $no . " class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=12 onblur=change_number(this) value=" . $bar->jlhbayar . " >
			<img src='images/puzz.png' style='cursor:pointer;' title='click to get value' onclick=\"document.getElementById('bayar" . $no . "').value=" . $bar->bebanperusahaan . "\">
		    <img src='images/application/application_delete.png' style='cursor:pointer;'  class=resicon title='delete' onclick=deltran('".$bar->notransaksi."','".$bar->periode."')>
		       </td>
		  <td align=right><input type=text id=tglbayar" . $no . " class=myinputtext onkeypress=\"return false;\" maxlength=10  size=10 onmouseover=setCalendar(this) value='" . date('d-m-Y') . "'></td>
		  <td align=center><img src='images/save.png' title='Save' id=btnsavePClaim" . $no . " class=resicon onclick=savePClaim('" . $no . "','" . $bar->notransaksi . "','" . $bar->bebanperusahaan . "')></td>";
        }
		
		echo"<td align=right>".number_format($bar->jumlahkasbank,2)."</td>";
		echo"<td align=center>".tanggalnormal($bar->tanggalkasbank)."</td>";
		
        echo "</tr>";
    }
    echo"</tbody>
	 <tfoot>
	 </tfoot>
	 </table>
	 </div>
	 </fieldset> 	 
	 ";
//}
echo "</div>";
CLOSE_BOX();
echo close_body();
?>