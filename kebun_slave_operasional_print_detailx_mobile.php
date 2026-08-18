<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$proses = checkPostGet('proses','');
$tipe= checkPostGet('tipe','');
$jenis= checkPostGet('jenis','');
$param = $_POST;
if(count($param)==0){$param = $_GET;}

if($_SESSION['language']=='EN'){
    $optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan1');
}else{
	$optKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
}
$optSatKegiatan=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$optNamaKary=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNIKary=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$optNamaBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
// $optGudang=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optkodeorg=makeOption($dbname,'kebun_aktifitas_mobile','notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");

$str = "select * from ".$dbname.".organisasi where kodeorganisasi like '".$optkodeorg[$param['notransaksi']]."%'"; 
$res = fetchdata($str);
foreach($res as $bar){
	$optGudang[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

$str = "select * from ".$dbname.".project where kodeorg like '".$optkodeorg[$param['notransaksi']]."%'"; 
$res = fetchdata($str);
foreach($res as $bar){
	$optGudang[$bar['kode']]=$bar['nama'];
}

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,kodekegiatan,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$cols[] = explode(',',$col1);
//$query = selectQuery($dbname,'kebun_prestasi_mobile',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query="select ".$col1." from ".$dbname.".kebun_prestasi_mobile a left join ".$dbname.".kebun_aktifitas_mobile b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");

# Kehadiran
$col2 = 'nik,absensi,jhk,umr,insentif';
$cols[] = explode(',',$col2);
$query = selectQuery($dbname,'kebun_kehadiran_mobile',$col2,
    "notransaksi='".$param['notransaksi']."'");
$data[] = fetchData($query);
$align[] = explode(",","L,L,R,R,R");
$length[] = explode(",","20,20,20,20,20");

# Pakai Material
$col3 = 'kodeorg,kodebarang,kwantitas,kwantitasha,hargasatuan';
$cols[] = explode(',',$col3);
$query = selectQuery($dbname,'kebun_pakaimaterial_mobile',$col3,
    "notransaksi='".$param['notransaksi']."'");
$data[] = fetchData($query);
$align[] = explode(",","L,L,R,R,R");
$length[] = explode(",","20,20,20,20,20");

//getNamakaryawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan)
{
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}

switch($tipe) {
    case "LC":
        $title = strtoupper("Land Clearing");
        break;
    case "BBT":
	$title = strtoupper($_SESSION['lang']['pembibitan']);
	break;
    case "TBM":
	$title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
	break;
    case "TM":
	$title = strtoupper("UPKEEP-".$_SESSION['lang']['tm']);
	break;
	case "PNN":
	$title = strtoupper($_SESSION['lang']['panen']);
	break;
    case "TB":
	$title = strtoupper("UPKEEP-".$_SESSION['lang']['tbm']);
	break;
	case "BKM":
	$title = strtoupper("BUKU KEGIATAN MANDOR");
	break;
    default:
	echo "Error : Atribut not Defined";
	exit;
	break;
}
$titleDetail = array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

/** Output Format **/

	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $men='menu.css';
	  $gen='generic.css';
	}else if($theme=='red'){
	  $men='menuRed.css';
	  $gen='genericRed.css';  
	}else{
	  $men='menuGray.css';
	  $gen='genericGray.css';  
	}         
	//echo"<pre>";
	$tab='';
	
	if($jenis=='html'){
		$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
		$border="border=0 cellspacing=1";
	} else {
		$border="border=1 cellspacing=0";
	}
	$opttgl=makeOption($dbname,'kebun_aktifitas_mobile','notransaksi,tanggal',"notransaksi='".$param['notransaksi']."'");
	$tab.="<table cellpadding=5 ".$border." class=sortable>";
	$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".getNamaOrg($optkodeorg[$param['notransaksi']])."</td></tr>";
	$optbkm=makeOption($dbname,'kebun_aktifitas_mobile','notransaksi,nobkm',"notransaksi='".$param['notransaksi']."'");
	$tab.="<tr class=rowcontent><td>No BKM</td><td> :</td><td><b> ".@$optbkm[$param['notransaksi']]."</b></td></tr>";
	$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td><b> ".$param['notransaksi']."</b></td></tr>";
	$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tanggal']."</td><td> :</td><td> ".tanggalnormal($opttgl[$param['notransaksi']])."</td></tr>";
	$tab.="<style>
                    .resiconn {
                        width:12px;
                        height:12px;
                        cursor:pointer;
                        transition: all .1s ease-in-out;
                    }
                    
                    .resiconn:hover{
                        transform: scale(20);
                    }
                    
                </style>";
	$tab.="</table>";
		
	
	$tab.="<br /><b>".$titleDetail[0]."<b><br />";
	$tab.="<table cellpadding=5 ".$border." class=sortable width=100%><thead>";
	$tab.="<tr class=rowheader>";
	$tab.="<th align=center>No</th>";
	$tab.="<th align=center>".$_SESSION['lang']['divisi']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['namakegiatan']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['satuan']."</th>";
	$tab.="<th align=center>Photo Awal</th>";
	$tab.="<th align=center>Photo Akhir</th>";
	$tab.="<th align=center>".$_SESSION['lang']['hasilkerjad']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['jhk']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['umr']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['upahpremi']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['total']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['kodebarang']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['jumlah']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['sloc']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['sloc']."</th>";
	$tab.="</tr></thead><tbody>";
	
	if(@$param['blok']!=''){
		$wh.=" and b.kodeorg='".$param['blok']."'";
	}
	if(@$param['kegiatan']!=''){
		$wh.=" and b.kodekegiatan='".$param['kegiatan']."'";
	}
	$sPres="select sum(a.insentif) as upahpremi, sum(a.umr) as umr,sum(a.jhk) as jumlahhk,kodekegiatan,
			tanggal,b.kodeorg, sum(b.hasilkerja) as hasilkerja,photo,photoakhir 
			from ".$dbname.".kebun_kehadiran_mobile a 
			left join ".$dbname.".kebun_prestasi_mobile b on a.notransaksi=b.notransaksi and a.nik=b.nikpemel and a.nourut=b.nourut
			left join ".$dbname.".kebun_aktifitas_mobile c on a.notransaksi=c.notransaksi 
			where a.notransaksi='".@$param['notransaksi']."' ".@$wh." group by a.notransaksi, kodekegiatan, b.kodeorg order by kodekegiatan asc, b.kodeorg asc"; //exit('error'. $sPres);
	$qPres=$owlPDO->query($sPres) or die(print " Gagal: ".PDOException::getMessage());
	$no=$thk=$tumr=$tpremi=$tpres=0;$whpres="";
	while($rPres=$qPres->fetch()){
		if($param['blok']!=''){			
			$whpres="and b.kodekegiatan='".$rPres['kodekegiatan']."' and b.kodeorg='".$rPres['kodeorg']."'";
		}
        $photoawal = $rPres['photo'];
        $photoakhir = $rPres['photoakhir'];
		
		#=== cek apakah di setup ada materialnya ===
		# Ambil data dari  kebun_pakaimaterial_mobile
		$queryM = selectQuery($dbname,'kebun_pakaimaterial_mobile',"*","notransaksi='".$param['notransaksi']."' and kodekegiatan='".$rPres['kodekegiatan']."' and kodeorg='".$rPres['kodeorg']."'");
		$dataM = fetchData($queryM);

		# Cek data di master kegiatan
		$queryK = selectQuery($dbname,'setup_kegiatannorma',"*","kodekegiatan='".$rPres['kodekegiatan']."'");
		$dataK = fetchData($queryK);
		$c="";
		if(empty($dataM) and !empty($dataK)){
			$c="color:red;";
		}
		$sMat="select * from ".$dbname.".kebun_pakaimaterial_mobile where notransaksi='".$param['notransaksi']."' and kodekegiatan='".$rPres['kodekegiatan']."' and kodeorg='".$rPres['kodeorg']."'";
		$qMat = fetchData($sMat);
		$row="";
		if(count($qMat)>1){			
			$row = "rowspan=".count($qMat)."";		
		}
		$no+=1;
		$tab.="<tr class=rowcontent style=vertical-align:top;".$c.">";
		$tab.="<td align=center ".$row.">".$no."</td>";
		$tab.="<td ".$row.">".@$optGudang[substr($rPres['kodeorg'],0,6)]."</td>";
		$tab.="<td ".$row.">".@$optGudang[$rPres['kodeorg']]."</td>";
		$tab.="<td ".$row.">".@$rPres['kodekegiatan']." - ".@$optKegiatan[$rPres['kodekegiatan']]."</td>";
		$tab.="<td ".$row.">".@$optSatKegiatan[$rPres['kodekegiatan']]."</td>";
		$tab.="<td style='text-align:center;cursor:pointer'><img title='Foto' class='resiconn' style='width:20px;height:20px;' src='{$photoawal}'></td>";
		$tab.="<td style='text-align:center;cursor:pointer'><img title='Foto' class='resiconn' style='width:20px;height:20px;' src='{$photoakhir}'></td>";
		$tab.="<td ".$row." align=right>".@hidezerodecimal($rPres['hasilkerja'],2)."</td>";
		$tab.="<td ".$row." align=right>".@hidezerodecimal($rPres['jumlahhk'],2)."</td>";
		$tab.="<td ".$row." align=right>".@hidezerodecimal($rPres['umr'],0)."</td>";
		$tab.="<td ".$row." align=right>".@hidezerodecimal($rPres['upahpremi'],0)."</td>";
		$tab.="<td ".$row." align=right>".@hidezerodecimal($rPres['umr']+$rPres['upahpremi'],0)."</td>";

		$thk+=$rPres['jumlahhk'];
		$tumr+=$rPres['umr'];
		$tpremi+=$rPres['upahpremi'];
		$tpres+=$rPres['hasilkerja'];
		
		$tgl=tanggalnormal($rPres['tanggal']);

		if(empty($dataM) and !empty($dataK)){
			$brg="";$n=0;
			foreach($dataK as $key => $bar){
				$n++;
				$brg.=$n.". ".$optNamaBrg[$bar['kodebarang']]."<br>";
			}
			
			
			$tab.="<td colspan=4 style=color:red;><b>Salah satu material dibawah ini belum diinput :<br></b>".$brg."</td>";
		}else{
			$sMat="select * from ".$dbname.".kebun_pakaimaterial_mobile where notransaksi='".$param['notransaksi']."' and kodekegiatan='".$rPres['kodekegiatan']."' and kodeorg='".$rPres['kodeorg']."'";
			$qMat = fetchData($sMat);
			if(count($qMat)>0){					
				$nbrg=0;
				foreach($qMat as $rMat){
					$nbrg+=1;
					if($nbrg>1){
						$tab.="</tr>";
						$tab.="<tr class=rowcontent>";
					}
					#$tab.="<tr class=rowcontent>";
					$tab.="<td>".$rMat['kodebarang']."-".$optNamaBrg[$rMat['kodebarang']]."</td>";
					$tab.="<td align=right>".@hidezerodecimal($rMat['kwantitas'],3)."</td>";
					$tab.="<td>".$optGudang[$rMat['kodegudang']]."</td>";
					
					$sMatRef="select * from ".$dbname.".log_transaksidt b left join ".$dbname.".log_transaksiht a on a.notransaksi=b.notransaksi     where a.tipetransaksi='5' and b.kodebarang='".$rMat['kodebarang']."' and notransaksireferensi='".$rMat['notransaksi']."' and b.kodeblok='".$rPres['kodeorg']."' and b.kodekegiatan='".$rPres['kodekegiatan']."'";
					$qMatRef = fetchData($sMatRef);
					foreach($qMatRef as $rMatRef){
						$optTrnsGdng=$rMatRef['notransaksi'];
					}
					$tab.="<td>".(isset($optTrnsGdng) ? $optTrnsGdng : "")."</td>";
					$tab.="</tr>";

				}
			}else{					
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
			}
		$tab.="</tr>";
		}
	}
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		// $tab.="</tr>";
		
		$tab.="<tr class=rowcontent style=background-color:#AED6F1>";
		$tab.="<td align=center colspan=8><b>Sub Total BKM</b></td>";
		$tab.="<td  align=right>".hidezerodecimal($thk,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($tumr,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($tpremi,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($tumr+$tpremi,2)."</td><td colspan=2></td>";
		$tab.="<td align=center colspan=2></td>";
		$tab.="</tr>";
		
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		// $tab.="</tr>";
		
		$kdjurnal="KBNB0";
		$optakun=makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='".$kdjurnal."'");
		$akun=$optakun[$kdjurnal];
		
		$dataabs=$dataabskary=$noakun=array();
		$str = "select * from ".$dbname.".sdm_absensidt where norefrensi='".$param['notransaksi']."'"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['noakun']==''){
				$bar['noakun']=$akun;
			}
			if(getKary($bar['karyawanid'],'tipekaryawan')==4){
				@$umrabs[$bar['noakun']]+=$bar['umr'];
				@$umrabskary[$bar['karyawanid']]+=$bar['umr'];				
			}
			
			$dataabs[$bar['noakun']]=$bar['noakun'];
			@$jhkabs[$bar['noakun']]+=$bar['hk'];
			@$premiabs[$bar['noakun']]+=$bar['premi'];
			
			$noakun[$bar['karyawanid']]=$bar['noakun'];
			$dataabskary[$bar['karyawanid']]=$bar['karyawanid'];
			@$jhkabskary[$bar['karyawanid']]+=$bar['hk'];
			@$premiabskary[$bar['karyawanid']]+=$bar['premi'];
			@$kdabsensi[$bar['karyawanid']]=$bar['absensi'];
		}
		
		
		$ttlhkabs=0;
		$kodeabsen=makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan');
		foreach(@$dataabs as $absen){				
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>"; 
			$tab.="<td>".$tgl."</td>";
			$tab.="<td colspan=2>".$absen." - ".getNamaAkun($absen)."</td>";
			$tab.="<td>HK</td>";
			$tab.="<td></td>";
			$tab.="<td align=right>".hidezerodecimal($jhkabs[$absen],2)."</td>";
			$tab.="<td align=right>".hidezerodecimal($umrabs[$absen])."</td>";
			$tab.="<td align=right>".hidezerodecimal($premiabs[$absen])."</td>";
			$tab.="<td align=right>".hidezerodecimal($umrabs[$absen]+$premiabs[$absen])."</td>";
			$tab.="<td colspan=4></td>";
			$tab.="</tr>";
			
			@$ttlhkabs+=$jhkabs[$absen];
			@$ttlumrabs+=$umrabs[$absen];
			@$ttlpreabs+=$premiabs[$absen];
			
		}
		
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		// $tab.="</tr>";
		
		$tab.="<tr class=rowcontent style=background-color:#AED6F1>";
		$tab.="<td align=center colspan=6><b>Sub Total Absensi</b></td>";
		$tab.="<td  align=right>".($ttlhkabs)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($ttlumrabs,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($ttlpreabs,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($ttlumrabs+$ttlpreabs,2)."</td><td colspan=2></td>";
		$tab.="<td align=center colspan=4></td>";
		$tab.="</tr>";
		
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		// $tab.="</tr>";
		
		 
		 
		$tab.="<tr class=rowcontent style=background-color:#A3E4D7>";
		$tab.="<td align=center colspan=5><b>Total (BKM + Absensi)</b></td>";
		$tab.="<td  align=right>".@hidezerodecimal($tpres,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($thk+$ttlhkabs,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($tumr+$ttlumrabs,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($tpremi+$ttlpreabs,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($tumr+$ttlumrabs+$tpremi+$ttlpreabs,2)."</td><td colspan=2></td>";
		$tab.="<td colspan=4></td>";
		$tab.="</tr>";
		 
	 $tab.="</table>";
	 $tab.="<br /><b>".$titleDetail[1]."</b><br />";
  
		$tab.="<table cellpadding=5  ".$border." class=sortable width=100%><thead>";
		$tab.="<tr class=rowheader>";
		$tab.="<th align=center>No</th>";
		$tab.="<th align=center colspan=2>".$_SESSION['lang']['kegiatan']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
		$tab.="<th align=center colspan=1>".$_SESSION['lang']['nik2']."</th>";
		$tab.="<th align=center colspan=2>".$_SESSION['lang']['nama']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['tipekaryawan']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['absensi']."</th>";
		 $tab.="<th align=center>".$_SESSION['lang']['hasilkerjad']."</th>";
		 $tab.="<th align=center>".$_SESSION['lang']['satuan']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['jhk']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['umr']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['upahpremi']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['total']."</th>";
		$tab.="</tr></thead><tbody>";
		$totJhk=$totUmr=$totInsentif=$tothasilkerja=0;
		$sKhdrn="select a.nik, a.absensi, a.insentif, a.umr, jhk, kodekegiatan,tanggal,b.kodeorg,a.hasilkerja 
			from ".$dbname.".kebun_kehadiran_mobile a 
			left join ".$dbname.".kebun_prestasi_mobile b on a.notransaksi=b.notransaksi and a.nik=b.nikpemel and a.nourut=b.nourut
			left join ".$dbname.".kebun_aktifitas_mobile c on a.notransaksi=c.notransaksi 
			where a.notransaksi='".$param['notransaksi']."' ".$whpres." order by kodekegiatan asc, b.kodeorg asc, nik asc"; 
		$qKhdrn=$owlPDO->query($sKhdrn) or die(print " Gagal: ".PDOException::getMessage());
		$qKhdrn->setFetchMode(PDO::FETCH_ASSOC);                       
		@$no='';
		while($rKhdrn=$qKhdrn->fetch()){
		 @$no+=1;
		 $tab.="<tr class=rowcontent>";
		 $tab.="<td align=center>".$no."</td>";
		 $tab.="<td colspan=1>".$rKhdrn['kodekegiatan']."</td>";
		 $tab.="<td colspan=1>".@$optKegiatan[$rKhdrn['kodekegiatan']]."</td>";
		 $tab.="<td>".$optGudang[$rKhdrn['kodeorg']]."</td>";
		 if(@$optNIKary[$rKhdrn['nik']]!=''){
			 @$optNIKary[$rKhdrn['nik']] = @$optNIKary[$rKhdrn['nik']]." ";
		 }
		 
		 $tab.="<td colspan=1>".@$optNIKary[$rKhdrn['nik']]."</td>";
		 if(getKary($rKhdrn['nik'],'lokasitugas')!=substr($rKhdrn['kodeorg'],0,4)){
			 $tab.="<td colspan=2>".@$optNamaKary[$rKhdrn['nik']]." <font style=font-size:10px;color:red>(".getKary($rKhdrn['nik'],'lokasitugas').")</font></td>";
		 }else{
			 $tab.="<td colspan=2>".@$optNamaKary[$rKhdrn['nik']]."</td>";
		 }
		 $x="";
		 if(getKary($rKhdrn['nik'],'tipekaryawan')!='4'){
			 $x="style=background-color:cyan;color:red;";
		 }
		 
		 $tab.="<td align=center ".$x.">".getNamaTipeKary(getKary($rKhdrn['nik'],'tipekaryawan'))."</td>";
		 $tab.="<td align=center>".$rKhdrn['absensi']."</td>";
		 $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['hasilkerja'],2)."</td>";
		 $tab.="<td  align=center>".getNamaKeg($rKhdrn['kodekegiatan'],'satuan')."</td>";
		 $tab.="<td align=right>".hidezerodecimal($rKhdrn['jhk'],2)."</td>";
		 $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['umr'],2)."</td>";
		 $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['insentif'],2)."</td>";
		 $tab.="<td  align=right>".@hidezerodecimal($rKhdrn['umr']+$rKhdrn['insentif'],2)."</td>";
		 
		 $tab.="</tr>";
		 $totJhk+=$rKhdrn['jhk'];
		 $totUmr+=$rKhdrn['umr'];
		 $totInsentif+=$rKhdrn['insentif'];
		 $tothasilkerja+=$rKhdrn['hasilkerja'];
		}
		
		
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		// $tab.="</tr>";
		
		$tab.="<tr class=rowcontent style=background-color:#AED6F1>";
		$tab.="<td align=center colspan=11><b>Sub Total BKM</b></td>";
		$tab.="<td  align=right>".hidezerodecimal($totJhk,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($totUmr,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($totInsentif,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($totUmr+$totInsentif,2)."</td>";
		$tab.="</tr>";
		
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		// $tab.="</tr>";
		
		$ttlhkabskary=$ttlumrabskary=$ttlpreabskary=0;
		foreach(@$dataabskary as $karya){				
			$no++;
			if(@$optNIKary[$karya]!=''){
				 @$optNIKary[$karya] = @$optNIKary[$karya]." ";
			}
		 
		 
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>"; 
			$tab.="<td align=left>".$noakun[$karya]."</td>";
			$tab.="<td align=left>".getNamaAkun($noakun[$karya])."</td>";
			$tab.="<td></td>";
			$tab.="<td colspan=1>".@$optNIKary[$karya]."</td>";
			$tab.="<td colspan=2>".@$optNamaKary[$karya]."</td>";
			$tab.="<td align=center>".getNamaTipeKary(getKary($karya,'tipekaryawan'))."</td>";
			$tab.="<td align=center>".$kdabsensi[$karya]."</td>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td align=right>".hidezerodecimal($jhkabskary[$karya],2)."</td>";
			if(getKary($karya,'tipekaryawan')==4){
				$tab.="<td align=right>".hidezerodecimal($umrabskary[$karya])."</td>";				
				@$ttlumrabskary+=$umrabskary[$karya];
				
				$ttlupahkaryawan[$karya]+=$umrabskary[$karya];
			}else{
				$tab.="<td align=right>***,***</td>";				
			}
			$tab.="<td align=right>".hidezerodecimal($premiabskary[$karya])."</td>";
			$tab.="<td align=right>".hidezerodecimal($ttlupahkaryawan[$karya]+$premiabskary[$karya])."</td>";
			$tab.="</tr>";
			
			@$ttlhkabskary+=$jhkabskary[$karya];
			@$ttlpreabskary+=$premiabskary[$karya];
			
		}
		
		$tab.="<tr class=rowcontent style=background-color:#AED6F1>";
		$tab.="<td align=center colspan=11><b>Sub Total Absensi</b></td>";
		$tab.="<td  align=right>".($ttlhkabskary)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($ttlumrabskary,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($ttlpreabskary,2)."</td>";
		$tab.="<td  align=right>".@hidezerodecimal($ttlumrabskary+$ttlpreabskary,2)."</td>";
		$tab.="</tr>";
		
		// $tab.="<tr class=rowcontent>";
		// $tab.="<td colspan=12 bgcolor=#2C3E50></td>";
		// $tab.="</tr>";
		
		 $tab.="<tr class=rowcontent style=background-color:#A3E4D7>";
		 $tab.="<td align=center colspan=11><b>Total (BKM + Absensi)</b></td>";
		 $tab.="<td  align=right>".hidezerodecimal($totJhk+$ttlhkabskary,2)."</td>";
		 $tab.="<td  align=right>".@hidezerodecimal($totUmr+$ttlumrabskary,2)."</td>";
		 $tab.="<td  align=right>".@hidezerodecimal($totInsentif+$ttlpreabskary,2)."</td>";
		 $tab.="<td  align=right>".@hidezerodecimal($totUmr+$ttlumrabskary+$totInsentif+$ttlpreabskary,2)."</td>";
		 $tab.="</tr>";
	 $tab.="</table>";
	
	// $tab.="<br><label>File Upload</label>
	// 		<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
	// 			<thead>
	// 			<tr class=rowheader>
	// 				<td align='center' width=30px>No.</td>
	// 				<td align='center' width=50px>File Type</td>
	// 				<td align='center'>Filename</td>
	// 				<td align='center' width=30px colspan=2>Action</td>
	// 			</tr>
	// 			</thead>
	// 			<tbody>";
			
    //         $res = [
    //                 [
    //                     'formaticon' => 'jpg',
    //                 'namafile' => $photoawal,
    //                 'id' => '1'
    //                 ],
    //                 [
    //                     'formaticon' => 'jpg',
    //                 'namafile' => $photoakhir,
    //                 'id' => '1'
    //                 ]
    //         ];
	// 		if(empty($res)){
	// 			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	// 		}else{
	// 			$no=0;
	// 			foreach($res as $key=>$val){
	// 				$no++;
	// 				$tab.="<tr class=rowcontent>
	// 						<td style='text-align:center'>".$no."</td>";
	// 				$icon=seticonfile($val['formaticon']);
	// 				$tab.="<td style='text-align:center'>
	// 						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
	// 					</td>";
	// 				$tab.="<td style='text-align:left;cursor:pointer'><img title='Foto' class='resiconn' style='width:20px;height:20px;' src='".$path.$val['namafile']."'></td>";
					
	// 				$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon title='download'></a></td>";
	// 				$tab.="</tr>";
	// 			}	
	// 		}	
	// 		$tab.="</tbody>
	// 		</table>
	// 	";
	
	if($jenis=='html'){
		echo $tab;
	} else if ($jenis=='pdf'){
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream("Detail_BKM",array("Attachment"=>0));
	} else {
		$not=str_replace('/','',$param['notransaksi']);
		$stream = $tab;
		$nop_ = "detail_".$not;
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
							parent.window.alert('Cant convert to excel format');
							</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
			}
			closedir($handle);
		}
	}
		
   
?>