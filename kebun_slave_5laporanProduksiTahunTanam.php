<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}

$periode    = checkPostGet('periodeId','');
$unit       = checkPostGet('unitId','');
$intiplasma = checkPostGet('intiplasma','');
$tahuntanam = checkPostGet('tahuntanam','');

$arrBulan=array('01'=>'JANUARI','02'=>'FEBRUARI','03'=>'MARET','04'=>'APRIL','05'=>'MEI','06'=>'JUNI','07'=>'JULI','08'=>'AGUSTUS','09'=>'SEPTEMBER','10'=>'OKTOBER','11'=>'NOVEMBER','12'=>'DESEMBER');

#================================ PROSES AMBIL TAHUN TANAM (AJAX) ================================
if($proses=='getThnTanam'){
	$optThn="<option value=''>".$_SESSION['lang']['all']."</option>";
	if($unit!=''){
		$inplas="";
		if($intiplasma!=''){
			$inplas=" and intiplasma='".$intiplasma."'";
		}
		# tahun tanam dari setup_blok unit terpilih yang statusnya TM
		$str="select distinct tahuntanam from ".$dbname.".setup_blok
		      where kodeorg like '".$unit."%' and statusblok='TM'".$inplas."
			  and tahuntanam>0 order by tahuntanam asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optThn.="<option value='".$bar['tahuntanam']."'>".$bar['tahuntanam']."</option>";
		}
	}
	echo $optThn;
	exit;
}

#================================ PROSES LAPORAN ================================
if($periode==''){
	exit("Error : Periode tidak boleh kosong");
}
if($unit==''){
	exit("Error : Unit tidak boleh kosong");
}

$thnperiode = substr($periode,0,4);
$blnperiode = substr($periode,5,2);
$nmBulan    = $arrBulan[$blnperiode];

# filter intiplasma dari setup_blok
$inplas="";
if($intiplasma!=''){
	$inplas=" and sb.intiplasma='".$intiplasma."'";
}
# filter tahun tanam
$inthn="";
if($tahuntanam!=''){
	$inthn=" and sb.tahuntanam='".intval($tahuntanam)."'";
}

#================ 1. Master blok : tahun tanam, luas, pokok (TM + filter) =================
$str="select sb.kodeorg,sb.tahuntanam,sb.luasareaproduktif,sb.jumlahpokok,sb.intiplasma
      from ".$dbname.".setup_blok sb
	  where sb.kodeorg like '".$unit."%' and sb.statusblok='TM'".$inplas.$inthn;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$blokDt[$bar->kodeorg]['tahuntanam']=$bar->tahuntanam;
	$blokDt[$bar->kodeorg]['luas']=$bar->luasareaproduktif;
	$blokDt[$bar->kodeorg]['pokok']=$bar->jumlahpokok;
}

#================ 2. Produksi bulan terpilih (SPB / timbangan - brutto) =================
# blok LIKE unit% agar blok lintas kebun (mis. LANE di SPB-nya LAPE) tidak ikut
$str="select blok,sum(jjg) jjg,sum(kgwb) kgwb,sum(brondolan) brondolan
      from ".$dbname.".kebun_spb_vw4
	  where substr(tanggal,1,7)='".$periode."' and blok like '".$unit."%'
	  group by blok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$produksiBulan[$bar->blok]['jjg']=$bar->jjg;
	$produksiBulan[$bar->blok]['kg']=$bar->kgwb;
	$produksiBulan[$bar->blok]['brd']=$bar->brondolan;
}

#================ 3. Produksi kumulatif Jan s/d periode terpilih =================
$str="select blok,sum(jjg) jjg,sum(kgwb) kgwb,sum(brondolan) brondolan
      from ".$dbname.".kebun_spb_vw4
	  where substr(tanggal,1,4)='".$thnperiode."' and substr(tanggal,1,7)<='".$periode."' and blok like '".$unit."%'
	  group by blok";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$produksiSd[$bar->blok]['jjg']=$bar->jjg;
	$produksiSd[$bar->blok]['kg']=$bar->kgwb;
	$produksiSd[$bar->blok]['brd']=$bar->brondolan;
}

#================ 4. TK (HK) panen bulan terpilih : kebun_prestasi + kebun_aktifitas =================
$str="select p.kodeorg,sum(p.jumlahhk) hk
      from ".$dbname.".kebun_prestasi p
	  join ".$dbname.".kebun_aktifitas a on p.notransaksi=a.notransaksi
	  where a.notransaksi like '%PNN%' and substr(a.tanggal,1,7)='".$periode."' and p.kodeorg like '".$unit."%'
	  group by p.kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$hkBulan[$bar->kodeorg]=$bar->hk;
}

#================ 5. TK (HK) panen kumulatif Jan s/d periode =================
$str="select p.kodeorg,sum(p.jumlahhk) hk
      from ".$dbname.".kebun_prestasi p
	  join ".$dbname.".kebun_aktifitas a on p.notransaksi=a.notransaksi
	  where a.notransaksi like '%PNN%' and substr(a.tanggal,1,4)='".$thnperiode."' and substr(a.tanggal,1,7)<='".$periode."' and p.kodeorg like '".$unit."%'
	  group by p.kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$hkSd[$bar->kodeorg]=$bar->hk;
}

#================ 4b. Distribusi HK ke blok real =================
# prestasi panen tercatat per TPH (9 digit, mis. LAPE01C01) sedangkan blok real 10 digit
# (LAPE01C01D) — 1 TPH bisa terdiri dari beberapa blok beda tahun tanam.
# HK tph dibagi ke blok in-scope secara proporsional luasareaproduktif agar tidak double count.
$hkBlok=array();
$hkSdBlok=array();
$luasTph=array();
if(isset($blokDt)){
	foreach($blokDt as $blk=>$dt){
		$tph=substr($blk,0,9);
		$hkBlok[$blk]=0;
		$hkSdBlok[$blk]=0;
		$luasTph[$tph]=(@$luasTph[$tph] + $dt['luas']);
	}
	foreach($blokDt as $blk=>$dt){
		$tph=substr($blk,0,9);
		if($luasTph[$tph]>0){
			if(isset($hkBulan[$tph])){
				$hkBlok[$blk]=$hkBulan[$tph]*($dt['luas']/$luasTph[$tph]);
			}
			if(isset($hkSd[$tph])){
				$hkSdBlok[$blk]=$hkSd[$tph]*($dt['luas']/$luasTph[$tph]);
			}
		}
	}
}

#================ Render =================
$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

if($proses=='excel'){
	$brd=1;
	$bgdt="bgcolor=#F0A0A0 align=center";
}else{
	$brd='0';
	$bgdt="class=rowheader align=center";
}

$tab='';

#---------- Header laporan ----------
$tab.="<table cellspacing=1 cellpadding=1 border=0>";
$tab.="<tr><td colspan=2 align=left><b>LAPORAN PRODUKSI PER TAHUN TANAM</b></td></tr>";
$tab.="<tr><td>".$_SESSION['lang']['unit']."</td><td>: ".$unit." - ".$namaOrg[$unit]."</td></tr>";
$tab.="<tr><td>".$_SESSION['lang']['periode']."</td><td>: ".substr($periode,5,2)."-".substr($periode,0,4)."</td></tr>";
$tab.="<tr><td>".$_SESSION['lang']['intiplasma']."</td><td>: ".($intiplasma==''?$_SESSION['lang']['all']:($intiplasma=='I'?'Inti':'Plasma'))."</td></tr>";
$tab.="<tr><td>".$_SESSION['lang']['tahuntanam']."</td><td>: ".($tahuntanam==''?$_SESSION['lang']['all']:$tahuntanam)."</td></tr>";
$tab.="</table>";

#---------- Header tabel ----------
$tab.="<table cellspacing=1 cellpadding=5 border='".$brd."' class=sortable>";
$tab.="<thead>";
$tab.="<tr class=rowheader>";
$tab.="<th rowspan=2 align=center ".$bgdt.">No</th>";
$tab.="<th rowspan=2 align=center ".$bgdt.">Block</th>";
$tab.="<th rowspan=2 align=center ".$bgdt.">Luas (Ha)</th>";
$tab.="<th rowspan=2 align=center ".$bgdt.">Divisi</th>";
$tab.="<th colspan=9 align=center ".$bgdt.">".strtoupper($nmBulan)."</th>";
$tab.="<th colspan=10 align=center ".$bgdt.">S/D ".strtoupper($nmBulan)." ".substr($periode,0,4)."</th>";
$tab.="</tr>";
$tab.="<tr class=rowheader>";
# bulan : 9 kolom
$tab.="<th align=center ".$bgdt.">JJG</th>";
$tab.="<th align=center ".$bgdt.">BJR</th>";
$tab.="<th align=center ".$bgdt.">KG</th>";
$tab.="<th align=center ".$bgdt.">TK</th>";
$tab.="<th align=center ".$bgdt.">Kg Brondol</th>";
$tab.="<th align=center ".$bgdt.">JJG / HK</th>";
$tab.="<th align=center ".$bgdt.">KG / HK</th>";
$tab.="<th align=center ".$bgdt.">Potensi kg /ha</th>";
$tab.="<th align=center ".$bgdt.">Potensi Jjg /Pkk</th>";
# s/d bulan : 10 kolom
$tab.="<th align=center ".$bgdt.">JJG</th>";
$tab.="<th align=center ".$bgdt.">BJR</th>";
$tab.="<th align=center ".$bgdt.">KG</th>";
$tab.="<th align=center ".$bgdt.">TK</th>";
$tab.="<th align=center ".$bgdt.">Kg Brondol</th>";
$tab.="<th align=center ".$bgdt.">JJG / HK</th>";
$tab.="<th align=center ".$bgdt.">KG / HK</th>";
$tab.="<th align=center ".$bgdt.">Potensi kg /ha</th>";
$tab.="<th align=center ".$bgdt.">Potensi Jjg /ha</th>";
$tab.="<th align=center ".$bgdt.">Potensi Jjg /Pkk</th>";
$tab.="</tr>";
$tab.="</thead><tbody>";

#---------- Body : group per tahun tanam ----------
if(isset($blokDt)){
	# urutkan blok : tahun tanam asc, blok asc
	$arrThn=array();
	foreach($blokDt as $blk=>$dt){
		$arrThn[$dt['tahuntanam']][]=$blk;
	}
	foreach($arrThn as $k=>$v){
		sort($arrThn[$k]);
	}
	ksort($arrThn);

	$no=0;
	$gLuas=$gJjg=$gKg=$gTk=$gBrd=0;
	$gJjgSd=$gKgSd=$gTkSd=$gBrdSd=0;
	$gtLuas=$gtJjg=$gtKg=$gtTk=$gtBrd=0;
	$gtJjgSd=$gtKgSd=$gtTkSd=$gtBrdSd=0;

	foreach($arrThn as $thn=>$arrBlok){
		# reset total per grup
		$gLuas=$gJjg=$gKg=$gTk=$gBrd=$gPokok=0;
		$gJjgSd=$gKgSd=$gTkSd=$gBrdSd=0;

		foreach($arrBlok as $blk){
			$no++;
			$jjg   = isset($produksiBulan[$blk]['jjg'])?$produksiBulan[$blk]['jjg']:0;
			$kg    = isset($produksiBulan[$blk]['kg'])?$produksiBulan[$blk]['kg']:0;
			$brd   = isset($produksiBulan[$blk]['brd'])?$produksiBulan[$blk]['brd']:0;
			$hk    = $hkBlok[$blk];
			$jjgSd = isset($produksiSd[$blk]['jjg'])?$produksiSd[$blk]['jjg']:0;
			$kgSd  = isset($produksiSd[$blk]['kg'])?$produksiSd[$blk]['kg']:0;
			$brdSd = isset($produksiSd[$blk]['brd'])?$produksiSd[$blk]['brd']:0;
			$hkSd  = $hkSdBlok[$blk];

			$luas  = $blokDt[$blk]['luas'];
			$pokok = $blokDt[$blk]['pokok'];
			$afd   = substr($blk,4,2);

			# rumus bulan (fixnan dipanggil dengan argumen aman div-by-zero)
			$bjr     = ($jjg>0)?fixnan($kg/$jjg):0;
			$jjghk   = ($hk>0)?fixnan($jjg/$hk):0;
			$kghk    = ($hk>0)?fixnan($kg/$hk):0;
			$potkgha = ($luas>0)?fixnan($kg/$luas):0;
			$potjjgpkk = ($pokok>0)?fixnan($jjg/$pokok):0;
			# rumus s/d
			$bjrSd     = ($jjgSd>0)?fixnan($kgSd/$jjgSd):0;
			$jjghkSd   = ($hkSd>0)?fixnan($jjgSd/$hkSd):0;
			$kghkSd    = ($hkSd>0)?fixnan($kgSd/$hkSd):0;
			$potkghaSd = ($luas>0)?fixnan($kgSd/$luas):0;
			$potjghaSd = ($luas>0)?fixnan($jjgSd/$luas):0;
			$potjjgpkkSd = ($pokok>0)?fixnan($jjgSd/$pokok):0;

			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$blk." - ".$namaOrg[$blk]."</td>";
			$tab.="<td align=right>".number_format($luas,2)."</td>";
			$tab.="<td align=center>".$afd."</td>";
			# bulan
			$tab.="<td align=right>".number_format($jjg,0)."</td>";
			$tab.="<td align=right>".number_format($bjr,2)."</td>";
			$tab.="<td align=right>".number_format($kg,2)."</td>";
			$tab.="<td align=right>".number_format($hk,2)."</td>";
			$tab.="<td align=right>".number_format($brd,0)."</td>";
			$tab.="<td align=right>".number_format($jjghk,0)."</td>";
			$tab.="<td align=right>".number_format($kghk,0)."</td>";
			$tab.="<td align=right>".number_format($potkgha,0)."</td>";
			$tab.="<td align=right>".number_format($potjjgpkk,2)."</td>";
			# s/d
			$tab.="<td align=right>".number_format($jjgSd,0)."</td>";
			$tab.="<td align=right>".number_format($bjrSd,2)."</td>";
			$tab.="<td align=right>".number_format($kgSd,2)."</td>";
			$tab.="<td align=right>".number_format($hkSd,2)."</td>";
			$tab.="<td align=right>".number_format($brdSd,0)."</td>";
			$tab.="<td align=right>".number_format($jjghkSd,0)."</td>";
			$tab.="<td align=right>".number_format($kghkSd,0)."</td>";
			$tab.="<td align=right>".number_format($potkghaSd,0)."</td>";
			$tab.="<td align=right>".number_format($potjghaSd,0)."</td>";
			$tab.="<td align=right>".number_format($potjjgpkkSd,2)."</td>";
			$tab.="</tr>";

			$gLuas+=$luas;
			$gPokok+=$pokok;
			$gJjg+=$jjg;$gKg+=$kg;$gTk+=$hk;$gBrd+=$brd;
			$gJjgSd+=$jjgSd;$gKgSd+=$kgSd;$gTkSd+=$hkSd;$gBrdSd+=$brdSd;
		}

		# JUMLAH per tahun tanam
		$tab.="<tr class=rowcontent style=font-weight:bold>";
		$tab.="<td colspan=2 align=center>JUMLAH TAHUN TANAM ".$thn."</td>";
		$tab.="<td align=right>".number_format($gLuas,2)."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=right>".number_format($gJjg,0)."</td>";
		$tab.="<td align=right>".number_format(($gJjg>0)?fixnan($gKg/$gJjg):0,2)."</td>";
		$tab.="<td align=right>".number_format($gKg,2)."</td>";
		$tab.="<td align=right>".number_format($gTk,2)."</td>";
		$tab.="<td align=right>".number_format($gBrd,0)."</td>";
		$tab.="<td align=right>".number_format(($gTk>0)?fixnan($gJjg/$gTk):0,0)."</td>";
		$tab.="<td align=right>".number_format(($gTk>0)?fixnan($gKg/$gTk):0,0)."</td>";
		$tab.="<td align=right>".number_format(($gLuas>0)?fixnan($gKg/$gLuas):0,0)."</td>";
		$tab.="<td align=right>".number_format(($gPokok>0)?fixnan($gJjgSd/$gPokok):0,2)."</td>";
		$tab.="<td align=right>".number_format($gJjgSd,0)."</td>";
		$tab.="<td align=right>".number_format(($gJjgSd>0)?fixnan($gKgSd/$gJjgSd):0,2)."</td>";
		$tab.="<td align=right>".number_format($gKgSd,2)."</td>";
		$tab.="<td align=right>".number_format($gTkSd,2)."</td>";
		$tab.="<td align=right>".number_format($gBrdSd,0)."</td>";
		$tab.="<td align=right>".number_format(($gTkSd>0)?fixnan($gJjgSd/$gTkSd):0,0)."</td>";
		$tab.="<td align=right>".number_format(($gTkSd>0)?fixnan($gKgSd/$gTkSd):0,0)."</td>";
		$tab.="<td align=right>".number_format(($gLuas>0)?fixnan($gKgSd/$gLuas):0,0)."</td>";
		$tab.="<td align=right>".number_format(($gLuas>0)?fixnan($gJjgSd/$gLuas):0,0)."</td>";
		$tab.="<td align=right>".number_format(($gPokok>0)?fixnan($gJjgSd/$gPokok):0,2)."</td>";
		$tab.="</tr>";

		# akumulasi grand total
		$gtLuas+=$gLuas;
		$gtJjg+=$gJjg;$gtKg+=$gKg;$gtTk+=$gTk;$gtBrd+=$gBrd;
		$gtJjgSd+=$gJjgSd;$gtKgSd+=$gKgSd;$gtTkSd+=$gTkSd;$gtBrdSd+=$gBrdSd;
	}

	#---------- TOTAL keseluruhan ----------
	$tab.="<tr class=rowcontent style=font-weight:bold>";
	$tab.="<td colspan=2 align=center>TOTAL</td>";
	$tab.="<td align=right>".number_format($gtLuas,2)."</td>";
	$tab.="<td align=center></td>";
	$tab.="<td align=right>".number_format($gtJjg,0)."</td>";
	$tab.="<td align=right>".number_format(($gtJjg>0)?fixnan($gtKg/$gtJjg):0,2)."</td>";
	$tab.="<td align=right>".number_format($gtKg,2)."</td>";
	$tab.="<td align=right>".number_format($gtTk,2)."</td>";
	$tab.="<td align=right>".number_format($gtBrd,0)."</td>";
	$tab.="<td align=right>".number_format(($gtTk>0)?fixnan($gtJjg/$gtTk):0,0)."</td>";
	$tab.="<td align=right>".number_format(($gtTk>0)?fixnan($gtKg/$gtTk):0,0)."</td>";
	$tab.="<td align=right>".number_format(($gtLuas>0)?fixnan($gtKg/$gtLuas):0,0)."</td>";
	$tab.="<td align=right></td>";
	$tab.="<td align=right>".number_format($gtJjgSd,0)."</td>";
	$tab.="<td align=right>".number_format(($gtJjgSd>0)?fixnan($gtKgSd/$gtJjgSd):0,2)."</td>";
	$tab.="<td align=right>".number_format($gtKgSd,2)."</td>";
	$tab.="<td align=right>".number_format($gtTkSd,2)."</td>";
	$tab.="<td align=right>".number_format($gtBrdSd,0)."</td>";
	$tab.="<td align=right>".number_format(($gtTkSd>0)?fixnan($gtJjgSd/$gtTkSd):0,0)."</td>";
	$tab.="<td align=right>".number_format(($gtTkSd>0)?fixnan($gtKgSd/$gtTkSd):0,0)."</td>";
	$tab.="<td align=right>".number_format(($gtLuas>0)?fixnan($gtKgSd/$gtLuas):0,0)."</td>";
	$tab.="<td align=right>".number_format(($gtLuas>0)?fixnan($gtJjgSd/$gtLuas):0,0)."</td>";
	$tab.="<td align=right></td>";
	$tab.="</tr>";
}else{
	$tab.="<tr class=rowcontent><td colspan=23 align=center>".$_SESSION['lang']['dataempty']."</td></tr>";
}

$tab.="</tbody></table>";

switch($proses){
	case'preview':
		echo $tab;
	break;
	case'excel':
		$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];
		$tglSkrg=date("Ymd");
		$nop_="LaporanProduksiTahunTanam_".$unit."_".$periode;
		if(strlen($tab)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/'.$file);
					}
				}
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$tab))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can\'t convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}
	break;
	default:
	break;
}
?>
