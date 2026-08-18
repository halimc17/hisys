<?php
//@uhr
require_once('master_validation.php');
require_once('lib/zLib.php');

$tgl = checkPostGet('prd','');
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$pt = checkPostGet('pt', '');
$tt = checkPostGet('tt', '');
$ip = checkPostGet('ip', '');
$divisi = checkPostGet('divisi', '');
$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$whpt=$whtt=$whip='';
if($pt!=''){
	$whpt.=" and c.alokasi='".$pt."'";
}
if($tt!=''){
	$whtt.=" and b.tahuntanam='".$tt."'";
	$whtta.=" and a.tahuntanam='".$tt."'";
}
if($ip!=''){
	$whip.=" and b.intiplasma='".$ip."'";
	$whipa.=" and a.intiplasma='".$ip."'";
}
if($divisi!=''){
	$whdv.=" and b.kodeorg like '".$divisi."%'";
	$whdva.=" and a.kodeorg like '".$divisi."%'";
}
if($pt=='')
{
    echo"Warning : Perusahaan tidak boleh kosong"; 
    exit;
}
// if($kdorg=='')
// {
    // echo"Warning : Unit tidak boleh kosong"; 
    // exit;
// }
// else if ($tgl=='')
// {
	// echo "Warning : Periode tidak boleh kosong";
	// exit;
// }

$expbln=  explode('-', $tgl);
$tahun=$expbln[0];
$bln=$expbln[1];

$blnawal=$tahun."-01";
$blnini= $tahun."-".$bln;


$strjjgbgt="(";
for($i=1;$i<=intval($bln);$i++)
{
    if($i<10)
    {
        $isi="jjg0".$i;
    }
    else 
    {
        $isi="jjg".$i;
    }
    if($i<intval($bln))
    {
        $strjjgbgt.=$isi."+";
    }
    else
    {
        $strjjgbgt.=$isi;
    }
}
$strjjgbgt.=")";


$strkgbgt="(";
for($i=1;$i<=intval($bln);$i++)
{
    if($i<10)
    {
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
    }
    if($i<intval($bln))
    {
        $strkgbgt.=$isi."+";
    }
    else
    {
        $strkgbgt.=$isi;
    }
}
$strkgbgt.=")";

$stream="";
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1>";
	$hide="show";
}

$stream.="
    <thead>
        <tr class=rowheader>
			<th rowspan=3 align=center width=20px >".$_SESSION['lang']['nourut']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['divisi']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['tanam']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['blok']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['intiplasma']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['jenisbibit']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['luas']." ".$_SESSION['lang']['ha']."</th>
			<th rowspan=3 align=center width=50px >".$_SESSION['lang']['pokok']."</th>
			<th colspan=6 align=center>".$_SESSION['lang']['setahun']."</th>
			<th colspan=19 align=center>".$_SESSION['lang']['sd']." ".$_SESSION['lang']['bulan']." ".numtomonth($bln,"E","short")." ".$tahun."</th>
			<th colspan=19 align=center>".numtomonth($bln,"E","short")." ".$tahun."</th>
			
			
		</tr>
		<tr>
			<th colspan=4 align=center>".$_SESSION['lang']['budget']."</th>
			<th colspan=2 align=center>Yield</th>
			<th colspan=4 align=center>".$_SESSION['lang']['realisasi']."</th>
			<th colspan=4 align=center>".$_SESSION['lang']['budget']."</th>
			<th colspan=2 align=center>".$_SESSION['lang']['rotasi']."</th>
			<th colspan=2 align=center>".$_SESSION['lang']['jjg']." / ".$_SESSION['lang']['pokok']."</th>
			<th colspan=2 align=center>".$_SESSION['lang']['bjr']."</th>
			<th colspan=5 align=center>Yield</th>
			<th colspan=4 align=center>".$_SESSION['lang']['realisasi']."</th>
			<th colspan=4 align=center>".$_SESSION['lang']['budget']."</th>
			<th colspan=2 align=center>".$_SESSION['lang']['rotasi']."</th>
			<th colspan=2 align=center>".$_SESSION['lang']['jjg']." / ".$_SESSION['lang']['pokok']."</th>
			<th colspan=2 align=center>".$_SESSION['lang']['bjr']."</th>
			<th colspan=5 align=center>Yield</th>
			
			
			
		</tr>
		<tr>
			<th align=center>".$_SESSION['lang']['ha']."</th>
			<th align=center>".$_SESSION['lang']['jhk']."</th>
			<th align=center>".$_SESSION['lang']['jjg']."</th>
			<th align=center>".$_SESSION['lang']['kg']."</th>
			<th align=center>Std</th>
			<th align=center>Bgt</th>
			<th align=center>".$_SESSION['lang']['ha']."</th>
			<th align=center>".$_SESSION['lang']['jhk']."</th>
			<th align=center>".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['kirim']."</th>
			<th align=center>".$_SESSION['lang']['kg']."</th>
			<th align=center>".$_SESSION['lang']['ha']."</th>
			<th align=center>".$_SESSION['lang']['jhk']."</th>
			<th align=center>".$_SESSION['lang']['jjg']."</th>
			<th align=center>".$_SESSION['lang']['kg']."</th>
			<th align=center>Act</th>
			<th align=center>Bgt</th>
			<th align=center>Act</th>
			<th align=center>Bgt</th>
			<th align=center>Act</th>
			<th align=center>Bgt</th>
			<th align=center>Act</th>
			<th align=center>Std</th>
			<th align=center>Bgt</th>
			<th align=center >% Act Std</th>
			<th align=center >% Act Bgt</th>
			
			<th align=center>".$_SESSION['lang']['ha']."</th>
			<th align=center>".$_SESSION['lang']['jhk']."</th>
			<th align=center>".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['kirim']."</th>
			<th align=center>".$_SESSION['lang']['kg']."</th>
			<th align=center>".$_SESSION['lang']['ha']."</th>
			<th align=center>".$_SESSION['lang']['jhk']."</th>
			<th align=center>".$_SESSION['lang']['jjg']."</th>
			<th align=center>".$_SESSION['lang']['kg']."</th>
			<th align=center>Act</th>
			<th align=center>Bgt</th>
			<th align=center>Act</th>
			<th align=center>Bgt</th>
			<th align=center>Act</th>
			<th align=center>Bgt</th>
			<th align=center>Act</th>
			<th align=center>Std</th>
			<th align=center>Bgt</th>
			<th align=center >% Act Std</th>
			<th align=center >% Act Bgt</th>
			
			
			
		</tr>";
	
$stream.="</thead>
		<tbody>";
		#budget hk, ha, rotasi setahun
		$str="select distinct a.kodeorg, substr(a.kodeorg,1,6) as divisi, a.kodebudget, a.kegiatan, a.JUMLAH as hk, rotasi, a.satuanj, b.tahuntanam, b.intiplasma from 
			 ".$dbname.".bgt_budget a left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
			 left join ".$dbname.".organisasi c on a.kodeorg = c.kodeorganisasi
			 where  1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.kodebudget in('SDM-PHL', 'SDM-KHT') 
			 and a.kodeorg like '".$kdorg."%' and a.kegiatan = '611010101' and a.tahunbudget = '".$tahun."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeorg']]=$bar['kodeorg'];
			$lstrot[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['rotasi'];
			$lsthk[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['hk'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['kodeorg'];
			
		}
		
		#budget jjg bi
		$str="select substr(a.kodeblok,1,6)as divisi,kodeblok, b.tahuntanam, sum(jjg".$bln.") as jjg, b.intiplasma from 
			 ".$dbname.".bgt_produksi_kebun a 
			 left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg 
			 left join ".$dbname.".organisasi c on a.kodeblok = c.kodeorganisasi
			 where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.kodeblok like '".$kdorg."%' and a.tahunbudget = '".$tahun."' group by divisi, tahuntanam, kodeblok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgbgtbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['jjg'];
			@$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]=$bar['kodeblok'];
		}
		#budget jjg sdbi
		$str="select substr(a.kodeblok,1,6)as divisi, a.kodeblok, b.tahuntanam, sum(".$strjjgbgt.") as jjg, b.intiplasma from 
			 ".$dbname.".bgt_produksi_kebun a 
			 left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg 
			 left join ".$dbname.".organisasi c on a.kodeblok = c.kodeorganisasi
			 where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.kodeblok like '".$kdorg."%' and a.tahunbudget = '".$tahun."' group by divisi, tahuntanam, kodeblok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jjgbgtsbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]=$bar['jjg'];
			@$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]=$bar['kodeblok'];
		}

		
		#budget kg bi
		$str="select a.divisi, a.thntnm as tahuntanam, a.kodeblok, sum(a.kg".$bln.") as kg, b.intiplasma from 
			 ".$dbname.".bgt_produksi_kbn_kg_vw a
			 left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg 
			 left join ".$dbname.".organisasi c on a.kodeblok = c.kodeorganisasi
			 
			 where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and kodeblok like '".$kdorg."%' and 
			 tahunbudget = '".$tahun."' group by divisi, tahuntanam, kodeblok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kgbgtbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]+=$bar['kg'];
		}
		#budget kg sd bi
		$str="select a.divisi, a.thntnm as tahuntanam,a.kodeblok, sum(".$strkgbgt.") as kg, b.intiplasma from 
			 ".$dbname.".bgt_produksi_kbn_kg_vw a
			 left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg 
			 left join ".$dbname.".organisasi c on a.kodeblok = c.kodeorganisasi
			 
			 where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and kodeblok like '".$kdorg."%' and tahunbudget = '".$tahun."' 
			 group by divisi, tahuntanam, kodeblok";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kgbgtsbi[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]=$bar['kg'];
		}

		#budget jjg setahun
		$str="select substr(a.kodeblok,1,6)as divisi, a.kodeblok, b.tahuntanam, sum(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12) as jjg, b.intiplasma from 
			 ".$dbname.".bgt_produksi_kebun a 
			 left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg 
			 left join ".$dbname.".organisasi c on a.kodeblok = c.kodeorganisasi
			 
			 where  1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.kodeblok like '".$kdorg."%' and a.tahunbudget = '".$tahun."' group by divisi, tahuntanam, a.kodeblok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$jjgbgtthn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]=$bar['jjg'];
			@$jjgbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['jjg'];
		}
		#budget kg setahun
		$str="select a.divisi, a.kodeblok, a.thntnm as tahuntanam, sum(a.kgsetahun) as kg, b.intiplasma from 
			 ".$dbname.".bgt_produksi_kbn_kg_vw a
			 left join ".$dbname.".setup_blok b on a.kodeblok = b.kodeorg 
			 left join ".$dbname.".organisasi c on a.kodeblok = c.kodeorganisasi
			 
			 where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.kodeblok like '".$kdorg."%' and a.tahunbudget = '".$tahun."' group by a.divisi, tahuntanam, a.kodeblok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeblok']]=$bar['kodeblok'];
			$kgbgtthn[$bar['divisi']][$bar['tahuntanam']][$bar['kodeblok']]=$bar['kg'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kgbgt[$bar['divisi']][$bar['tahuntanam']]+=$bar['kg'];
		}
		
		#ambil yield standar
		$str="select substr(a.kodeorg,1,6) as divisi, a.kodeorg, a.tahuntanam, a.jenisbibit,a.klasifikasitanah,
			 ('".$tahun."'-a.tahuntanam) as umur, b.kgproduksi 
			 from ".$dbname.".setup_blok a
			 left join ".$dbname.".organisasi c on a.kodeorg = c.kodeorganisasi
			 left join ".$dbname.".kebun_5stproduksi b on a.tahuntanam=b.tahuntanam and a.jenisbibit=b.jenisbibit
			 and a.klasifikasitanah=b.klasifikasitanah 
			 
			 where 1=1 ".$whtta." ".$whipa." ".$whpt." ".$whdva." and kodeorg like '".$kdorg."%' group by a.kodeorg";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeorg']]=$bar['kodeorg'];
			@$listyield[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['kgproduksi'];
			// @$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['kodeorg'];
		}
		#rekappnn bi
		$str="select sum(a.luaspanen) as luaspanen, sum(a.jjgpanen) as jjgpanen, sum(a.tenagakerja) as hk, sum(a.kgkebun) as kgkebun, a.tahuntanam, a.divisi, a.blok, b.intiplasma
			  from ".$dbname.".kebun_rekappnn_vw a
			  left join ".$dbname.".setup_blok b on a.blok=b.kodeorg 
			  left join ".$dbname.".organisasi c on a.blok=c.kodeorganisasi
			  
			  where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.divisi like '".$kdorg."%' and substr(a.tanggal,1,7) = '".$tgl."'
			  group by a.divisi, a.tahuntanam, a.blok ";
			 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kdblok[$bar['blok']]=$bar['blok'];
			@$habi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['luaspanen'];
			@$hkbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['hk'];
			@$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
		}
		
		#rekappnn sdbi
		$str="select sum(a.luaspanen) as luaspanen, sum(a.jjgpanen) as jjgpanen, sum(a.tenagakerja) as hk, sum(a.kgkebun) as kgkebun, a.tahuntanam, a.divisi, a.blok, b.jenisbibit, b.intiplasma
			  from ".$dbname.".kebun_rekappnn_vw a 
			  left join ".$dbname.".setup_blok b on a.blok=b.kodeorg 
			  left join ".$dbname.".organisasi c on a.blok=c.kodeorganisasi
			  
			  where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.divisi like '".$kdorg."%' and substr(a.tanggal,1,7) >= '".$blnawal."' and substr(a.tanggal,1,7) <= '".$tgl."'
			  group by divisi, tahuntanam, blok ";
			  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kdblok[$bar['blok']]=$bar['blok'];
			@$hasbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['luaspanen'];
			@$hksbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['hk'];
			@$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
			@$jnsbbt[$bar['jenisbibit']]=$bar['jenisbibit'];
			@$listjnsbbt[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jenisbibit'];
			@$intiplasma[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['intiplasma'];
			@$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
		}

		#spb sdbi
		$str="select a.divisi, a.blok, a.tahuntanam, b.jenisbibit, sum(a.jjg) as jjg, sum(a.kgwb) as kgwb, b.intiplasma from 
			 ".$dbname.".kebun_spb_vw a 
			 left join ".$dbname.".setup_blok b on a.blok=b.kodeorg 
			 left join ".$dbname.".organisasi c on a.blok=c.kodeorganisasi
			 where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.divisi like '".$kdorg."%' and substr(tanggal,1,7) >= '".$blnawal."' and substr(tanggal,1,7) <= '".$tgl."' 
			  group by divisi, tahuntanam, blok";
			  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kdblok[$bar['blok']]=$bar['blok'];
			@$jjgkrmsbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jjg'];
			@$kgwbsbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['kgwb'];
			@$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];
			@$jnsbbt[$bar['jenisbibit']]=$bar['jenisbibit'];
			@$listjnsbbt[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jenisbibit'];
			@$intiplasma[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['intiplasma'];
			@$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
		}
		#spb bi
		$str="select a.divisi, a.blok, a.tahuntanam, sum(a.jjg) as jjg, sum(a.kgwb) as kgwb, b.intiplasma from 
			 ".$dbname.".kebun_spb_vw a
			  left join ".$dbname.".setup_blok b on a.blok=b.kodeorg 
			  left join ".$dbname.".organisasi c on a.blok=c.kodeorganisasi
			 
			  where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and a.divisi like '".$kdorg."%' and 
			  substr(a.tanggal,1,7) = '".$tgl."' group by a.divisi, a.tahuntanam, a.blok";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			@$kddivisi[$bar['divisi']]=$bar['divisi'];
			@$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$kdblok[$bar['blok']]=$bar['blok'];
			@$jjgkrmbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['jjg'];
			@$kgwbbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['kgwb'];
			@$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]=$bar['blok'];			
		}
		#setup blok
		$str="select substr(b.kodeorg,1,6) as divisi, b.kodeorg, b.tahuntanam, b.luasareaproduktif as luas, b.jumlahpokok as pokok, b.klasifikasitanah, b.jenisbibit, b.intiplasma 
		from ".$dbname.".setup_blok_tahunan b
		left join ".$dbname.".organisasi c on b.kodeorg=c.kodeorganisasi
		where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and b.tahun='".str_replace('-', '', $tgl)."' and b.kodeorg like '".$kdorg."%' and b.statusblok in ('TM','TBM') and b.tahuntanam!=''";
		//exit('error : '.$str)
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlbaris($res);
		if($numrows==0){	
			$str="select substr(b.kodeorg,1,6) as divisi, b.kodeorg, b.tahuntanam, b.luasareaproduktif as luas, b.jumlahpokok as pokok, b.klasifikasitanah, b.jenisbibit, b.intiplasma 
			from ".$dbname.".setup_blok b
			left join ".$dbname.".organisasi c on b.kodeorg=c.kodeorganisasi
			where 1=1 ".$whtt." ".$whip." ".$whpt." ".$whdv." and b.kodeorg like '".$kdorg."%' and b.statusblok in ('TM','TBM') and b.tahuntanam!=''";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
		}
		while($bar=$res->fetch())
		{
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			$kdblok[$bar['kodeorg']]=$bar['kodeorg'];
			$jnsbbt[$bar['jenisbibit']]=$bar['jenisbibit'];
			$listjnsbbt[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['jenisbibit'];
			$intiplasma[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['intiplasma'];
			$listblok[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['kodeorg'];
			$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$listluas[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['luas'];
			$listpkk[$bar['divisi']][$bar['tahuntanam']][$bar['kodeorg']]=$bar['pokok'];
			$luasblok[$bar['divisi']][$bar['tahuntanam']]=$bar['luas'];
			$pokokblok[$bar['divisi']][$bar['tahuntanam']]=$bar['pokok'];
		}	

array_multisort($kddivisi,SORT_ASC);
array_multisort($tahuntanam,SORT_ASC);
array_multisort($kdblok,SORT_ASC);

foreach($kddivisi as $divisi)
{
	foreach($tahuntanam as $thntnm)
	{
		foreach($kdblok as $blok)
		{
			if(@$listblok[$divisi][$thntnm][$blok]!=''){
			$no+=1;	
			$stream.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$namaOrg[$divisi]."</td>
				<td align=center>".$thntnm."</td>
				<td align=left>".$namaOrg[$listblok[$divisi][$thntnm][$blok]]."</td>	
				<td align=center>".$intiplasma[$divisi][$thntnm][$blok]."</td>	
				<td align=left>".@$listjnsbbt[$divisi][$thntnm][$blok]."</td>
				<td align=right>".@number_format($listluas[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format($listpkk[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($lstrot[$divisi][$thntnm][$blok]*$listluas[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($lsthk[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format($jjgbgtthn[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($kgbgtthn[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($listyield[$divisi][$thntnm][$blok]/1000,2)."</td>
				
				<td align=right>".@number_format(fixnan(($kgbgtthn[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000),2)."</td>
				
				<td align=right>".@number_format($hasbi[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format($hksbi[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format($jjgkrmsbi[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($kgwbsbi[$divisi][$thntnm][$blok])."</td>
				
				<td align=right>".@number_format((fixnan($lstrot[$divisi][$thntnm][$blok]/12)*$bln)*$listluas[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format(fixnan($kgbgtsbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*$lsthk[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($jjgbgtsbi[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($kgbgtsbi[$divisi][$thntnm][$blok])."</td>
				
				<td align=right>".@number_format(fixnan($hasbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok]),2)."</td>
				<td align=right>".@number_format((fixnan($lstrot[$divisi][$thntnm][$blok]/12)*$bln),2)."</td>
				
				<td align=right>".@number_format(fixnan($jjgkrmsbi[$divisi][$thntnm][$blok]/$listpkk[$divisi][$thntnm][$blok]),2)."</td>
				<td align=right>".@number_format(fixnan($jjgbgtsbi[$divisi][$thntnm][$blok]/$listpkk[$divisi][$thntnm][$blok]),2)."</td>
				
				<td align=right>".@number_format(fixnan($kgwbsbi[$divisi][$thntnm][$blok]/$jjgkrmsbi[$divisi][$thntnm][$blok]),2)."</td>
				<td align=right>".@number_format(fixnan($kgbgtsbi[$divisi][$thntnm][$blok]/$jjgbgtsbi[$divisi][$thntnm][$blok]),2)."</td>
				
				<td align=right>".@number_format(fixnan(($kgwbsbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000),2)."</td>
				<td align=right>".@number_format(fixnan($kgbgtsbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*fixnan($listyield[$divisi][$thntnm][$blok]/1000),2)."</td>
				<td align=right>".@number_format(fixnan(($kgbgtsbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000),2)."</td>
				<td align=right>".@number_format(fixnan((($kgwbsbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000)/(($kgbgtsbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*($listyield[$divisi][$thntnm][$blok]/1000))*100),2)."</td>
				<td align=right>".@number_format(fixnan((($kgwbsbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000)/(($kgbgtsbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000)*100),2)."</td>
				
				<td align=right>".@number_format($habi[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format($hkbi[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format($jjgkrmbi[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($kgwbbi[$divisi][$thntnm][$blok])."</td>
				
				<td align=right>".@number_format(fixnan($lstrot[$divisi][$thntnm][$blok]/12)*$listluas[$divisi][$thntnm][$blok],2)."</td>
				<td align=right>".@number_format(fixnan($kgbgtbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*$lsthk[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($jjgbgtbi[$divisi][$thntnm][$blok])."</td>
				<td align=right>".@number_format($kgbgtbi[$divisi][$thntnm][$blok])."</td>
				
				<td align=right>".@number_format(fixnan($habi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok]),2)."</td>
				<td align=right>".@number_format((fixnan($lstrot[$divisi][$thntnm][$blok]/12)),2)."</td>
				
				<td align=right>".@number_format(fixnan($jjgkrmbi[$divisi][$thntnm][$blok]/$listpkk[$divisi][$thntnm][$blok]),2)."</td>
				<td align=right>".@number_format(fixnan($jjgbgtbi[$divisi][$thntnm][$blok]/$listpkk[$divisi][$thntnm][$blok]),2)."</td>
				
				<td align=right>".@number_format(fixnan($kgwbbi[$divisi][$thntnm][$blok]/$jjgkrmbi[$divisi][$thntnm][$blok]),2)."</td>
				<td align=right>".@number_format(fixnan($kgbgtbi[$divisi][$thntnm][$blok]/$jjgbgtbi[$divisi][$thntnm][$blok]),2)."</td>
				
				<td align=right>".@number_format(fixnan(($kgwbbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000),2)."</td>
				<td align=right>".@number_format(fixnan($kgbgtbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*fixnan($listyield[$divisi][$thntnm][$blok]/1000),2)."</td>
				<td align=right>".@number_format(fixnan(($kgbgtbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000),2)."</td>
				<td align=right>".@number_format(fixnan((($kgwbbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000)/(($kgbgtbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*($listyield[$divisi][$thntnm][$blok]/1000))*100),2)."</td>
				<td align=right>".@number_format(fixnan((($kgwbbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000)/(($kgbgtbi[$divisi][$thntnm][$blok]/$listluas[$divisi][$thntnm][$blok])/1000)*100),2)."</td>
				</tr>";
			}
			@$ttlttluas[$divisi][$thntnm]+=$listluas[$divisi][$thntnm][$blok];
			@$ttlttpkk[$divisi][$thntnm]+=$listpkk[$divisi][$thntnm][$blok];
			@$ttltthathn[$divisi][$thntnm]+=$lstrot[$divisi][$thntnm][$blok]*$listluas[$divisi][$thntnm][$blok];
			@$ttltthkthn[$divisi][$thntnm]+=$lsthk[$divisi][$thntnm][$blok];
			@$ttlttjjgthn[$divisi][$thntnm]+=$jjgbgtthn[$divisi][$thntnm][$blok];
			@$ttlttkgthn[$divisi][$thntnm]+=$kgbgtthn[$divisi][$thntnm][$blok];
			@$ttlttyieldthn[$divisi][$thntnm]+=(($listyield[$divisi][$thntnm][$blok]/1000)*$listluas[$divisi][$thntnm][$blok]);
			@$ttlttharealsbi[$divisi][$thntnm]+=$hasbi[$divisi][$thntnm][$blok];
			@$ttltthkrealsbi[$divisi][$thntnm]+=$hksbi[$divisi][$thntnm][$blok];
			@$ttlttjjgrealsbi[$divisi][$thntnm]+=$jjgkrmsbi[$divisi][$thntnm][$blok];
			@$ttlttkgrealsbi[$divisi][$thntnm]+=$kgwbsbi[$divisi][$thntnm][$blok];
			
			@$ttltthabgtsbi[$divisi][$thntnm]+=(($lstrot[$divisi][$thntnm][$blok]/12)*$bln)*$listluas[$divisi][$thntnm][$blok];
			@$ttltthkbgtsbi[$divisi][$thntnm]+=($kgbgtsbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*$lsthk[$divisi][$thntnm][$blok];
			@$ttlttjjgbgtsbi[$divisi][$thntnm]+=$jjgbgtsbi[$divisi][$thntnm][$blok];
			@$ttlttkgbgtsbi[$divisi][$thntnm]+=$kgbgtsbi[$divisi][$thntnm][$blok];
			@$ttlttyieldstdsbi[$divisi][$thntnm]+=($kgbgtsbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*($listyield[$divisi][$thntnm][$blok]/1000)*$listluas[$divisi][$thntnm][$blok];
			
			@$ttlttharealbi[$divisi][$thntnm]+=$habi[$divisi][$thntnm][$blok];
			@$ttltthkrealbi[$divisi][$thntnm]+=$hkbi[$divisi][$thntnm][$blok];
			@$ttlttjjgrealbi[$divisi][$thntnm]+=$jjgkrmbi[$divisi][$thntnm][$blok];
			@$ttlttkgrealbi[$divisi][$thntnm]+=$kgwbbi[$divisi][$thntnm][$blok];
			
			@$ttltthabgtbi[$divisi][$thntnm]+=(($lstrot[$divisi][$thntnm][$blok]/12))*$listluas[$divisi][$thntnm][$blok];
			@$ttltthkbgtbi[$divisi][$thntnm]+=($kgbgtbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*$lsthk[$divisi][$thntnm][$blok];
			@$ttlttjjgbgtbi[$divisi][$thntnm]+=$jjgbgtbi[$divisi][$thntnm][$blok];
			@$ttlttkgbgtbi[$divisi][$thntnm]+=$kgbgtbi[$divisi][$thntnm][$blok];
			@$ttlttyieldstdbi[$divisi][$thntnm]+=($kgbgtbi[$divisi][$thntnm][$blok]/$kgbgtthn[$divisi][$thntnm][$blok])*($listyield[$divisi][$thntnm][$blok]/1000)*$listluas[$divisi][$thntnm][$blok];
		
		}
	
	if(@$listtahuntanam[$divisi][$thntnm]!=''){
	$stream.="<tr bgcolor=#C8C8FE style='color:#000000'>
		<td align=left colspan=3 >TOTAL TT ".$thntnm."</td>
		<td align=right ></td>
		<td align=right ></td>
		<td align=right ></td>
		<td align=right >".@number_format(fixnan($ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttpkk[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttltthathn[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttltthkthn[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttjjgthn[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttlttkgthn[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttlttyieldthn[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan(($ttlttkgthn[$divisi][$thntnm]/1000)/$ttlttluas[$divisi][$thntnm]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttharealsbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttltthkrealsbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttjjgrealsbi[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttlttkgrealsbi[$divisi][$thntnm]))."</td>
		
		<td align=right >".@number_format(fixnan($ttltthabgtsbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttltthkbgtsbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttjjgbgtsbi[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttlttkgbgtsbi[$divisi][$thntnm]))."</td>
		
		<td align=right >".@number_format(fixnan($ttlttharealsbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttltthabgtsbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttjjgrealsbi[$divisi][$thntnm]/$ttlttpkk[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttjjgbgtsbi[$divisi][$thntnm]/$ttlttpkk[$divisi][$thntnm]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttkgrealsbi[$divisi][$thntnm]/$ttlttjjgrealsbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttkgbgtsbi[$divisi][$thntnm]/$ttlttjjgbgtsbi[$divisi][$thntnm]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttkgrealsbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]/1000),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttyieldstdsbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttkgbgtsbi[$divisi][$thntnm]/1000/$ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan(($ttlttkgrealsbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]/1000)/($ttlttyieldstdsbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm])*100),2)."</td>
		<td align=right >".@number_format(fixnan(($ttlttkgrealsbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]/1000)/($ttlttkgbgtsbi[$divisi][$thntnm]/1000/$ttlttluas[$divisi][$thntnm])*100),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttharealbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttltthkrealbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttjjgrealbi[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttlttkgrealbi[$divisi][$thntnm]))."</td>
		
		<td align=right >".@number_format(fixnan($ttltthabgtbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttltthkbgtbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttjjgbgtbi[$divisi][$thntnm]))."</td>
		<td align=right >".@number_format(fixnan($ttlttkgbgtbi[$divisi][$thntnm]))."</td>
		
		<td align=right >".@number_format(fixnan($ttlttharealbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttltthabgtbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttjjgrealbi[$divisi][$thntnm]/$ttlttpkk[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttjjgbgtbi[$divisi][$thntnm]/$ttlttpkk[$divisi][$thntnm]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttkgrealbi[$divisi][$thntnm]/$ttlttjjgrealbi[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttkgbgtbi[$divisi][$thntnm]/$ttlttjjgbgtbi[$divisi][$thntnm]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttlttkgrealbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]/1000),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttyieldstdbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan($ttlttkgbgtbi[$divisi][$thntnm]/1000/$ttlttluas[$divisi][$thntnm]),2)."</td>
		<td align=right >".@number_format(fixnan(($ttlttkgrealbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]/1000)/($ttlttyieldstdbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm])*100),2)."</td>
		<td align=right >".@number_format(fixnan(($ttlttkgrealbi[$divisi][$thntnm]/$ttlttluas[$divisi][$thntnm]/1000)/($ttlttkgbgtbi[$divisi][$thntnm]/1000/$ttlttluas[$divisi][$thntnm])*100),2)."</td>
		";
		
	@$ttluas[$thntnm]+=$ttlttluas[$divisi][$thntnm];
	@$ttpkk[$thntnm]+=$ttlttpkk[$divisi][$thntnm];
	
	@$ttlhabgtthn[$thntnm]+=$ttltthathn[$divisi][$thntnm];
	@$ttlhkbgtthn[$thntnm]+=$ttltthkthn[$divisi][$thntnm];
	@$ttljjgbgtthn[$thntnm]+=$ttlttjjgthn[$divisi][$thntnm];
	@$ttlkgbgtthn[$thntnm]+=$ttlttkgthn[$divisi][$thntnm];
	@$ttlyieldstdthn[$thntnm]+=$ttlttyieldthn[$divisi][$thntnm];
	
	@$ttlharealsbi[$thntnm]+=$ttlttharealsbi[$divisi][$thntnm];
	@$ttlhkrealsbi[$thntnm]+=$ttltthkrealsbi[$divisi][$thntnm];
	@$ttljjgrealsbi[$thntnm]+=$ttlttjjgrealsbi[$divisi][$thntnm];
	@$ttlkgrealsbi[$thntnm]+=$ttlttkgrealsbi[$divisi][$thntnm];
	
	@$ttlhabgtsbi[$thntnm]+=$ttltthabgtsbi[$divisi][$thntnm];
	@$ttlhkbgtsbi[$thntnm]+=$ttltthkbgtsbi[$divisi][$thntnm];
	@$ttljjgbgtsbi[$thntnm]+=$ttlttjjgbgtsbi[$divisi][$thntnm];
	@$ttlkgbgtsbi[$thntnm]+=$ttlttkgbgtsbi[$divisi][$thntnm];
	
	@$ttlyieldstdsbi[$thntnm]+=$ttlttyieldstdsbi[$divisi][$thntnm];
	
	@$ttlharealbi[$thntnm]+=$ttlttharealbi[$divisi][$thntnm];
	@$ttlhkrealbi[$thntnm]+=$ttltthkrealbi[$divisi][$thntnm];
	@$ttljjgrealbi[$thntnm]+=$ttlttjjgrealbi[$divisi][$thntnm];
	@$ttlkgrealbi[$thntnm]+=$ttlttkgrealbi[$divisi][$thntnm];
	
	@$ttlhabgtbi[$thntnm]+=$ttltthabgtbi[$divisi][$thntnm];
	@$ttlhkbgtbi[$thntnm]+=$ttltthkbgtbi[$divisi][$thntnm];
	@$ttljjgbgtbi[$thntnm]+=$ttlttjjgbgtbi[$divisi][$thntnm];
	@$ttlkgbgtbi[$thntnm]+=$ttlttkgbgtbi[$divisi][$thntnm];
	
	@$ttlyieldstdbi[$thntnm]+=$ttlttyieldstdbi[$divisi][$thntnm];
	
	
	
	
	
	}
	@$ttldivluas[$divisi]+=$ttlttluas[$divisi][$thntnm];
	@$ttldivpkk[$divisi]+=$ttlttpkk[$divisi][$thntnm];
	
	@$ttldivhabgtthn[$divisi]+=$ttltthathn[$divisi][$thntnm];
	@$ttldivhkbgtthn[$divisi]+=$ttltthkthn[$divisi][$thntnm];
	@$ttldivjjgbgtthn[$divisi]+=$ttlttjjgthn[$divisi][$thntnm];
	@$ttldivkgbgtthn[$divisi]+=$ttlttkgthn[$divisi][$thntnm];
	@$ttldivyieldstdthn[$divisi]+=$ttlttyieldthn[$divisi][$thntnm];
	
	@$ttldivharealsbi[$divisi]+=$ttlttharealsbi[$divisi][$thntnm];
	@$ttldivhkrealsbi[$divisi]+=$ttltthkrealsbi[$divisi][$thntnm];
	@$ttldivjjgrealsbi[$divisi]+=$ttlttjjgrealsbi[$divisi][$thntnm];
	@$ttldivkgrealsbi[$divisi]+=$ttlttkgrealsbi[$divisi][$thntnm];
	
	@$ttldivhabgtsbi[$divisi]+=$ttltthabgtsbi[$divisi][$thntnm];
	@$ttldivhkbgtsbi[$divisi]+=$ttltthkbgtsbi[$divisi][$thntnm];
	@$ttldivjjgbgtsbi[$divisi]+=$ttlttjjgbgtsbi[$divisi][$thntnm];
	@$ttldivkgbgtsbi[$divisi]+=$ttlttkgbgtsbi[$divisi][$thntnm];
	
	@$ttldivyieldstdsbi[$divisi]+=$ttlttyieldstdsbi[$divisi][$thntnm];
	
	@$ttldivharealbi[$divisi]+=$ttlttharealbi[$divisi][$thntnm];
	@$ttldivhkrealbi[$divisi]+=$ttltthkrealbi[$divisi][$thntnm];
	@$ttldivjjgrealbi[$divisi]+=$ttlttjjgrealbi[$divisi][$thntnm];
	@$ttldivkgrealbi[$divisi]+=$ttlttkgrealbi[$divisi][$thntnm];
	
	@$ttldivhabgtbi[$divisi]+=$ttltthabgtbi[$divisi][$thntnm];
	@$ttldivhkbgtbi[$divisi]+=$ttltthkbgtbi[$divisi][$thntnm];
	@$ttldivjjgbgtbi[$divisi]+=$ttlttjjgbgtbi[$divisi][$thntnm];
	@$ttldivkgbgtbi[$divisi]+=$ttlttkgbgtbi[$divisi][$thntnm];
	
	@$ttldivyieldstdbi[$divisi]+=$ttlttyieldstdbi[$divisi][$thntnm];
	}


$stream.="<tr bgcolor=#809FFE     style='color:#000000'>
		<td align=left colspan=3 >TOTAL ".$divisi."</td>
		<td align=right ></td>
		<td align=right ></td>
		<td align=right ></td>
		<td align=right >".@number_format(fixnan($ttldivluas[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivpkk[$divisi]))."</td>
		
		<td align=right >".@number_format(fixnan($ttldivhabgtthn[$divisi]))."</td>
		<td align=right >".@number_format(fixnan($ttldivhkbgtthn[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivjjgbgtthn[$divisi]))."</td>
		<td align=right >".@number_format(fixnan($ttldivkgbgtthn[$divisi]))."</td>
		<td align=right >".@number_format($ttldivyieldstdthn[$divisi]/$ttldivluas[$divisi],2)."</td>
		<td align=right >".@number_format($ttldivkgbgtthn[$divisi]/$ttldivluas[$divisi]/1000,2)."</td>
		
		<td align=right >".@number_format(fixnan($ttldivharealsbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivhkrealsbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivjjgrealsbi[$divisi]))."</td>
		<td align=right >".@number_format(fixnan($ttldivkgrealsbi[$divisi]))."</td>
		
		<td align=right >".@number_format(fixnan($ttldivhabgtsbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivhkbgtsbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivjjgbgtsbi[$divisi]))."</td>
		<td align=right >".@number_format(fixnan($ttldivkgbgtsbi[$divisi]))."</td>
		
		<td align=right >".@number_format(fixnan($ttldivharealsbi[$divisi]/$ttldivluas[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivhabgtsbi[$divisi]/$ttldivluas[$divisi]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttldivjjgrealsbi[$divisi]/$ttldivpkk[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivjjgbgtsbi[$divisi]/$ttldivpkk[$divisi]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttldivkgrealsbi[$divisi]/$ttldivjjgrealsbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivkgbgtsbi[$divisi]/$ttldivjjgbgtsbi[$divisi]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttldivkgrealsbi[$divisi]/$ttldivluas[$divisi]/1000),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivyieldstdsbi[$divisi]/$ttldivluas[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivkgbgtsbi[$divisi]/1000/$ttldivluas[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan(($ttldivkgrealsbi[$divisi]/$ttldivluas[$divisi]/1000)/($ttldivyieldstdsbi[$divisi]/$ttldivluas[$divisi])*100),2)."</td>
		<td align=right >".@number_format(fixnan(($ttldivkgrealsbi[$divisi]/$ttldivluas[$divisi]/1000)/($ttldivkgbgtsbi[$divisi]/1000/$ttldivluas[$divisi])*100),2)."</td>
		
		
		<td align=right >".@number_format(fixnan($ttldivharealbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivhkrealbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivjjgrealbi[$divisi]))."</td>
		<td align=right >".@number_format(fixnan($ttldivkgrealbi[$divisi]))."</td>
		
		<td align=right >".@number_format(fixnan($ttldivhabgtbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivhkbgtbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivjjgbgtbi[$divisi]))."</td>
		<td align=right >".@number_format(fixnan($ttldivkgbgtbi[$divisi]))."</td>
		
		<td align=right >".@number_format(fixnan($ttldivharealbi[$divisi]/$ttldivluas[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivhabgtbi[$divisi]/$ttldivluas[$divisi]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttldivjjgrealbi[$divisi]/$ttldivpkk[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivjjgbgtbi[$divisi]/$ttldivpkk[$divisi]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttldivkgrealbi[$divisi]/$ttldivjjgrealbi[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivkgbgtbi[$divisi]/$ttldivjjgbgtbi[$divisi]),2)."</td>
		
		<td align=right >".@number_format(fixnan($ttldivkgrealbi[$divisi]/$ttldivluas[$divisi]/1000),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivyieldstdbi[$divisi]/$ttldivluas[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan($ttldivkgbgtbi[$divisi]/1000/$ttldivluas[$divisi]),2)."</td>
		<td align=right >".@number_format(fixnan(($ttldivkgrealbi[$divisi]/$ttldivluas[$divisi]/1000)/($ttldivyieldstdbi[$divisi]/$ttldivluas[$divisi])*100),2)."</td>
		<td align=right >".@number_format(fixnan(($ttldivkgrealbi[$divisi]/$ttldivluas[$divisi]/1000)/($ttldivkgbgtbi[$divisi]/1000/$ttldivluas[$divisi])*100),2)."</td>

		";
		//total kg hasil x dari yield sd bi per divisi 
		$ttldivyieldstdsbi[$divisi];
		$ttldivyieldstdbi[$divisi];
	
		
	@$gtluas+=$ttldivluas[$divisi];
	@$gtpkk+=$ttldivpkk[$divisi];
	@$gthabgt+=$ttldivhabgtthn[$divisi];
	@$gthkbgt+=$ttldivhkbgtthn[$divisi];
	@$gtjjgbgt+=$ttldivjjgbgtthn[$divisi];
	@$gtkgbgt+=$ttldivkgbgtthn[$divisi];
	@$gtyieldstdthn+=$ttldivyieldstdthn[$divisi];
	
	@$gtharealsbi+=$ttldivharealsbi[$divisi];
	@$gthkrealsbi+=$ttldivhkrealsbi[$divisi];
	@$gtjjgrealsbi+=$ttldivjjgrealsbi[$divisi];
	@$gtkgrealsbi+=$ttldivkgrealsbi[$divisi];
	
	@$gthabgtsbi+=$ttldivhabgtsbi[$divisi];
	@$gthkbgtsbi+=$ttldivhkbgtsbi[$divisi];
	@$gtjjgbgtsbi+=$ttldivjjgbgtsbi[$divisi];
	@$gtkgbgtsbi+=$ttldivkgbgtsbi[$divisi];
	
	@$gtyieldstdsbi+=$ttldivyieldstdsbi[$divisi];
	
	
	@$gtharealbi+=$ttldivharealbi[$divisi];
	@$gthkrealbi+=$ttldivhkrealbi[$divisi];
	@$gtjjgrealbi+=$ttldivjjgrealbi[$divisi];
	@$gtkgrealbi+=$ttldivkgrealbi[$divisi];
	
	@$gthabgtbi+=$ttldivhabgtbi[$divisi];
	@$gthkbgtbi+=$ttldivhkbgtbi[$divisi];
	@$gtjjgbgtbi+=$ttldivjjgbgtbi[$divisi];
	@$gtkgbgtbi+=$ttldivkgbgtbi[$divisi];
	
	@$gtyieldstdbi+=$ttldivyieldstdbi[$divisi];

}
$stream.="<tr bgcolor=#1E90FF   style='color:#000'>
		<td align=left colspan=3 >GRAND TOTAL</td>
		<td></td><td></td><td></td>
		<td align=right >".@number_format($gtluas,2)."</td>
		<td align=right >".@number_format($gtpkk)."</td>
		<td align=right >".@number_format($gthabgt)."</td>
		<td align=right >".@number_format($gthkbgt,2)."</td>
		<td align=right >".@number_format($gtjjgbgt)."</td>
		<td align=right >".@number_format($gtkgbgt)."</td>
		<td align=right >".@number_format($gtyieldstdthn/$gtluas,2)."</td>
		<td align=right >".@number_format($gtkgbgt/$gtluas/1000,2)."</td>
		
		<td align=right >".@number_format($gtharealsbi,2)."</td>
		<td align=right >".@number_format($gthkrealsbi,2)."</td>
		<td align=right >".@number_format($gtjjgrealsbi)."</td>
		<td align=right >".@number_format($gtkgrealsbi)."</td>
		
		<td align=right >".@number_format($gthabgtsbi,2)."</td>
		<td align=right >".@number_format($gthkbgtsbi,2)."</td>
		<td align=right >".@number_format($gtjjgbgtsbi)."</td>
		<td align=right >".@number_format($gtkgbgtsbi)."</td>
		
		<td align=right >".@number_format($gtharealsbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gthabgtsbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtjjgrealsbi/$gtpkk,2)."</td>
		<td align=right >".@number_format($gtjjgbgtsbi/$gtpkk,2)."</td>
		
		<td align=right >".@number_format($gtkgrealsbi/$gtjjgrealsbi,2)."</td>
		<td align=right >".@number_format($gtkgbgtsbi/$gtjjgbgtsbi,2)."</td>
		
		<td align=right >".@number_format($gtkgrealsbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format($gtyieldstdsbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtkgbgtsbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format(($gtkgrealsbi/1000/$gtluas)/($gtyieldstdsbi/$gtluas)*100,2)."</td>
		<td align=right >".@number_format(($gtkgrealsbi/1000/$gtluas)/($gtkgbgtsbi/1000/$gtluas)*100,2)."</td>
		
		<td align=right >".@number_format($gtharealbi,2)."</td>
		<td align=right >".@number_format($gthkrealbi,2)."</td>
		<td align=right >".@number_format($gtjjgrealbi)."</td>
		<td align=right >".@number_format($gtkgrealbi)."</td>
		
		<td align=right >".@number_format($gthabgtbi,2)."</td>
		<td align=right >".@number_format($gthkbgtbi,2)."</td>
		<td align=right >".@number_format($gtjjgbgtbi)."</td>
		<td align=right >".@number_format($gtkgbgtbi)."</td>
		
		<td align=right >".@number_format($gtharealbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gthabgtbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtjjgrealbi/$gtpkk,2)."</td>
		<td align=right >".@number_format($gtjjgbgtbi/$gtpkk,2)."</td>
		
		<td align=right >".@number_format($gtkgrealbi/$gtjjgrealbi,2)."</td>
		<td align=right >".@number_format($gtkgbgtbi/$gtjjgbgtbi,2)."</td>
		
		<td align=right >".@number_format($gtkgrealbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format($gtyieldstdbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtkgbgtbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format(($gtkgrealbi/1000/$gtluas)/($gtyieldstdbi/$gtluas)*100,2)."</td>
		<td align=right >".@number_format(($gtkgrealbi/1000/$gtluas)/($gtkgbgtbi/1000/$gtluas)*100,2)."</td>
		
		";
		
$stream.="<tr><td colspan=5><b>REKAP PER TAHUN TANAM</b></td></tr>";

foreach($tahuntanam as $thntnm)
{	
	if($thntnm!='0'){
	@$no1+=1;
	$stream.="<tr class=rowcontent>
		<td align=center>".$no1."</td>
		<td></td>
		<td align=center>".$thntnm."</td>
		<td></td><td></td><td></td>
		<td align=right>".@number_format($ttluas[$thntnm],2)."</td>
		<td align=right>".@number_format($ttpkk[$thntnm])."</td>
		<td align=right>".@number_format($ttlhabgtthn[$thntnm])."</td>
		<td align=right>".@number_format($ttlhkbgtthn[$thntnm],2)."</td>
		<td align=right>".@number_format($ttljjgbgtthn[$thntnm])."</td>
		<td align=right>".@number_format($ttlkgbgtthn[$thntnm])."</td>
		
		<td align=right>".@number_format($ttlyieldstdthn[$thntnm]/$ttluas[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlkgbgtthn[$thntnm]/$ttluas[$thntnm]/1000,2)."</td>
		
		
		<td align=right>".@number_format($ttlharealsbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlhkrealsbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttljjgrealsbi[$thntnm])."</td>
		<td align=right>".@number_format($ttlkgrealsbi[$thntnm])."</td>
		
		<td align=right>".@number_format($ttlhabgtsbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlhkbgtsbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttljjgbgtsbi[$thntnm])."</td>
		<td align=right>".@number_format($ttlkgbgtsbi[$thntnm])."</td>
		
		<td align=right>".@number_format($ttlharealsbi[$thntnm]/$ttluas[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlhabgtsbi[$thntnm]/$ttluas[$thntnm],2)."</td>
		
		<td align=right>".@number_format($ttljjgrealsbi[$thntnm]/$ttpkk[$thntnm],2)."</td>
		<td align=right>".@number_format($ttljjgbgtsbi[$thntnm]/$ttpkk[$thntnm],2)."</td>
		
		<td align=right>".@number_format($ttlkgrealsbi[$thntnm]/$ttljjgrealsbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlkgbgtsbi[$thntnm]/$ttljjgbgtsbi[$thntnm],2)."</td>
		
		<td align=right>".@number_format($ttlkgrealsbi[$thntnm]/$ttluas[$thntnm]/1000,2)."</td>
		<td align=right>".@number_format($ttlyieldstdsbi[$thntnm]/$ttluas[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlkgbgtsbi[$thntnm]/$ttluas[$thntnm]/1000,2)."</td>
		<td align=right>".@number_format(($ttlkgrealsbi[$thntnm]/$ttluas[$thntnm]/1000)/($ttlyieldstdsbi[$thntnm]/$ttluas[$thntnm])*100,2)."</td>
		<td align=right>".@number_format(($ttlkgrealsbi[$thntnm]/$ttluas[$thntnm]/1000)/($ttlkgbgtsbi[$thntnm]/$ttluas[$thntnm]/1000)*100,2)."</td>
		
		
		<td align=right>".@number_format($ttlharealbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlhkrealbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttljjgrealbi[$thntnm])."</td>
		<td align=right>".@number_format($ttlkgrealbi[$thntnm])."</td>
		
		<td align=right>".@number_format($ttlhabgtbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlhkbgtbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttljjgbgtbi[$thntnm])."</td>
		<td align=right>".@number_format($ttlkgbgtbi[$thntnm])."</td>
		
		<td align=right>".@number_format($ttlharealbi[$thntnm]/$ttluas[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlhabgtbi[$thntnm]/$ttluas[$thntnm],2)."</td>
		
		<td align=right>".@number_format($ttljjgrealbi[$thntnm]/$ttpkk[$thntnm],2)."</td>
		<td align=right>".@number_format($ttljjgbgtbi[$thntnm]/$ttpkk[$thntnm],2)."</td>
		
		<td align=right>".@number_format($ttlkgrealbi[$thntnm]/$ttljjgrealbi[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlkgbgtbi[$thntnm]/$ttljjgbgtbi[$thntnm],2)."</td>
		
		<td align=right>".@number_format($ttlkgrealbi[$thntnm]/$ttluas[$thntnm]/1000,2)."</td>
		<td align=right>".@number_format($ttlyieldstdbi[$thntnm]/$ttluas[$thntnm],2)."</td>
		<td align=right>".@number_format($ttlkgbgtbi[$thntnm]/$ttluas[$thntnm]/1000,2)."</td>
		<td align=right>".@number_format(($ttlkgrealbi[$thntnm]/$ttluas[$thntnm]/1000)/($ttlyieldstdbi[$thntnm]/$ttluas[$thntnm])*100,2)."</td>
		<td align=right>".@number_format(($ttlkgrealbi[$thntnm]/$ttluas[$thntnm]/1000)/($ttlkgbgtbi[$thntnm]/$ttluas[$thntnm]/1000)*100,2)."</td>
		
		";
	}
}
$stream.="<tr bgcolor=#1E90FF   style='color:#000000'>
		<td align=left colspan=3 >GRAND TOTAL</td>
		<td></td><td></td><td></td>
		<td align=right >".@number_format($gtluas,2)."</td>
		<td align=right >".@number_format($gtpkk)."</td>
		<td align=right >".@number_format($gthabgt)."</td>
		<td align=right >".@number_format($gthkbgt,2)."</td>
		<td align=right >".@number_format($gtjjgbgt)."</td>
		<td align=right >".@number_format($gtkgbgt)."</td>
		<td align=right >".@number_format($gtyieldstdthn/$gtluas,2)."</td>
		<td align=right >".@number_format($gtkgbgt/$gtluas/1000,2)."</td>
		
		<td align=right >".@number_format($gtharealsbi,2)."</td>
		<td align=right >".@number_format($gthkrealsbi,2)."</td>
		<td align=right >".@number_format($gtjjgrealsbi)."</td>
		<td align=right >".@number_format($gtkgrealsbi)."</td>
		
		<td align=right >".@number_format($gthabgtsbi,2)."</td>
		<td align=right >".@number_format($gthkbgtsbi,2)."</td>
		<td align=right >".@number_format($gtjjgbgtsbi)."</td>
		<td align=right >".@number_format($gtkgbgtsbi)."</td>
		
		<td align=right >".@number_format($gtharealsbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gthabgtsbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtjjgrealsbi/$gtpkk,2)."</td>
		<td align=right >".@number_format($gtjjgbgtsbi/$gtpkk,2)."</td>
		
		<td align=right >".@number_format($gtkgrealsbi/$gtjjgrealsbi,2)."</td>
		<td align=right >".@number_format($gtkgbgtsbi/$gtjjgbgtsbi,2)."</td>
		
		<td align=right >".@number_format($gtkgrealsbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format($gtyieldstdsbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtkgbgtsbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format(($gtkgrealsbi/1000/$gtluas)/($gtyieldstdsbi/$gtluas)*100,2)."</td>
		<td align=right >".@number_format(($gtkgrealsbi/1000/$gtluas)/($gtkgbgtsbi/1000/$gtluas)*100,2)."</td>
		
		<td align=right >".@number_format($gtharealbi,2)."</td>
		<td align=right >".@number_format($gthkrealbi,2)."</td>
		<td align=right >".@number_format($gtjjgrealbi)."</td>
		<td align=right >".@number_format($gtkgrealbi)."</td>
		
		<td align=right >".@number_format($gthabgtbi,2)."</td>
		<td align=right >".@number_format($gthkbgtbi,2)."</td>
		<td align=right >".@number_format($gtjjgbgtbi)."</td>
		<td align=right >".@number_format($gtkgbgtbi)."</td>
		
		<td align=right >".@number_format($gtharealbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gthabgtbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtjjgrealbi/$gtpkk,2)."</td>
		<td align=right >".@number_format($gtjjgbgtbi/$gtpkk,2)."</td>
		
		<td align=right >".@number_format($gtkgrealbi/$gtjjgrealbi,2)."</td>
		<td align=right >".@number_format($gtkgbgtbi/$gtjjgbgtbi,2)."</td>
		
		<td align=right >".@number_format($gtkgrealbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format($gtyieldstdbi/$gtluas,2)."</td>
		<td align=right >".@number_format($gtkgbgtbi/1000/$gtluas,2)."</td>
		<td align=right >".@number_format(($gtkgrealbi/1000/$gtluas)/($gtyieldstdbi/$gtluas)*100,2)."</td>
		<td align=right >".@number_format(($gtkgrealbi/1000/$gtluas)/($gtkgbgtbi/1000/$gtluas)*100,2)."</td>
		
		
		";

$stream.="
 </tbody>";
 

		
switch ($proses) {
    case 'preview':
        echo $stream;
	break;

    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "Yield Gab unit ". $kdorg ." sd bulan ".$tgl;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
	break;
}

?>