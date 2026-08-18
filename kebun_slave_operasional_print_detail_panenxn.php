<?php
// ini_set('display_errors',0);
// error_reporting(0);
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$proses= $_GET['proses'];
$tipe  = $_GET['tipe'];
$param = $_GET;
if(count($param)==0){
	$param = $_POST;
}
$notran= $param['notransaksi'];
$tampil= checkPostGet('tampil', '');
$proses= checkPostGet('proses', '');
$tipe  = checkPostGet('tipe', '');


#tampil = 1 atau 2
$sel1=$sel2="";
if($tampil=='' or $tampil=='undefined' or $tampil=='1'){
	$tampil='1'; $sel1="checked";
}else{	
	$tampil=$tampil; $sel2="checked";
}


/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,nik,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpenalty,upahpremi,premibasis,rupiahpenalty,luaspanen,jurnal,b.noreferensi';
$cols[] = explode(',',$col1);
//$query = selectQuery($dbname,'kebun_prestasi',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query="select ".$col1." from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";
//exit("Error".$query);
$res = fetchData($query);
$data[] = fetchData($query);
$align[] = explode(",","L,L,L,R,R,R,R,R");
$length[] = explode(",","10,10,15,10,10,15,15,15");



//getNamakaryawan
$sDtKaryawn="select karyawanid,namakaryawan from ".$dbname.".datakaryawan order by namakaryawan asc";
$rData=fetchData($sDtKaryawn);
foreach($rData as $brKary =>$rNamakaryawan){
    $RnamaKary[$rNamakaryawan['karyawanid']]=$rNamakaryawan['namakaryawan'];
}



$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi order by namaorganisasi asc";
//exit("Error".$sOrg);
$rDataOrg=fetchData($sOrg);
foreach($rDataOrg as $brOrg =>$rNamaOrg){
    $rNmOrg[$rNamaOrg['kodeorganisasi']]=$rNamaOrg['namaorganisasi'];
}

$title      = strtoupper($_SESSION['lang']['panen']);
$titleDetail= array($_SESSION['lang']['prestasi'],$_SESSION['lang']['absensi'],$_SESSION['lang']['material']);

// Init Total
$totJanjang=$totUpahKerja=$totUpahKerjapenalty=$totUpahPremi=0;
$totUpahPremibasis=$totUpahDenda=$totLuas=$totSisa=0;

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
	#echo"<pre>"; 
	$opttgl=makeOption($dbname,'kebun_aktifitas','notransaksi,tanggal',"notransaksi='".$param['notransaksi']."'");
	$nospk=makeOption($dbname,'kebun_aktifitas','notransaksi,nospk',"notransaksi='".$param['notransaksi']."'");
	
	$tab="<link rel=stylesheet type=text/css href=style/".$gen.">";
	//$tab.="<fieldset><legend>".$title."</legend>";
	$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
	$tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td> ".$_SESSION['empl']['lokasitugas']."</td></tr>";
	$tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td> ".$param['notransaksi']."</td></tr>";
	$tab.="<tr hidden><td>".$_SESSION['lang']['nospk']."</td><td> :</td><td> ".$nospk[$param['notransaksi']]."</td></tr>";
	$tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td><td> :</td><td> ".tanggalnormal($opttgl[$param['notransaksi']])."</td></tr>";
	$tab.="<tr><td>".$_SESSION['lang']['view']."</td><td> :</td>
				<td valign=center><input type=radio ".$sel1." onclick=\"detailData('".$param['notransaksi']."','','event','PNN','1')\">Detail
				<input type=radio ".$sel2." onclick=\"detailData('".$param['notransaksi']."','','".$param['notransaksi']."','event','PNN','2')\">Rekap</td>
				
				</tr>";
	$tab.="</tbody></table>";
	$tab.="<br />".$titleDetail[0]."<br />";
	
	if($proses=='excel' or $proses=='pdf'){
		$tab.="<table class=sortable border=1 cellspacing=0>";
		$border='border=1';
	}else{
		$tab.="<table class=sortable cellpadding=5 border=0 cellspacing=1>";
	}
	switch($tampil){
		case'1':
			$dendapanen=array();
			$iddendapnn=array();

			$kodeorg=makeOption($dbname,'kebun_aktifitas','notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");
			
			$str = "select max(id) as max,a.*,b.* from ".$dbname.".kebun_5dendapanen a left join ".$dbname.".kebun_5kodedendapanen b on a.kodedenda=b.kodedenda where 1=1 and a.kodeorg='".$kodeorg[$param['notransaksi']]."' group by id order by b.id asc";
			$res = fetchdata($str);
			foreach($res as $bar){
				$iddendapnn[$bar['id']]=$bar['id'];
				$dendapanen[$bar['id']]=$bar['kodedenda'];
				$namadenda[$bar['id']]=$bar['deskripsi'];
				$tp[$bar['id']]= "title=\"".$bar['kodedenda']." => ".$bar['deskripsi']." = (".$bar['denda']." / ".$bar['jenisdenda'].")\"";
				$tplistdata[$bar['id']]= "title=\"".$bar['kodedenda']." => ".$bar['deskripsi']." = (".$bar['denda']." / ".$bar['jenisdenda'].")";
				$harga[$bar['id']] = $bar['denda']; 
				$sat[$bar['id']] = $bar['jenisdenda']; 
				$hp[$bar['id']] = $bar['kodedenda'];
				$maxdenda=$bar['max'];
			}

			$rows="rowspan=2";	
			$tab.="<table id=tabledt cellpadding=5 cellspacing=1 ".$border." class=sortable >
					<thead><tr class=rowheader>
					<th align=center ".$rows." width=20px>No</th>
					<th align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</th>
					<th align=center colspan=3>".$_SESSION['lang']['nomor'] . "</th>
					<th align=center ".$rows." width=30px>".$_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center ".$rows." width=30px>Kontanan</th>
					<th align=center ".$rows." width=40px>BJR</th>
					<th align=center colspan=5>".$_SESSION['lang']['hasilkerja2'] . "</th>
					<th align=center colspan=3>".$_SESSION['lang']['jumlah']."</th>
					<th align=center colspan=5>".$_SESSION['lang']['premilebihbasis']."</th>
					<th align=center colspan=".count($dendapanen)." ".$disdenda.">".$_SESSION['lang']['denda']."</th>
					<th align=center ".$rows." title='Click to Unhide' style=width:50px;>".$_SESSION['lang']['denda']." Rp</th>
					<th align=center ".$rows." >Grand Total</th>
				</tr>
				<tr>
					<th align=center>".$_SESSION['lang']['blok'] . "</th>
					<th align=center>TPH</th>
					<th align=center>Sesi</th>
					
					<th align=center>".$_SESSION['lang']['ha'] . "</th>
					<th align=center>".$_SESSION['lang']['jjg'] . "</th>
					<th align=center>Jjg<br>Basis</th>
					<th align=center>Kg Brd</th>
					<th align=center>".$_SESSION['lang']['kg'] . "</th>
					<th align=center>".$_SESSION['lang']['hk2'] . "</th>
					<th align=center>".$_SESSION['lang']['upah'] . "</th>
					<th align=center>".$_SESSION['lang']['denda'] . "</th>
					
					<th align=center>".$_SESSION['lang']['basic'] . " 1</th>
					<th align=center>".$_SESSION['lang']['basic'] . " 2</th>
					<th align=center width=40px>".$_SESSION['lang']['lebihbasis'] . " 1</th>
					<th align=center width=40px>".$_SESSION['lang']['lebihbasis'] . " 2</th>
					<th align=center width=40px>Brondol</th>";
					
					#denda header list data
					foreach($dendapanen as $iddenda => $kddenda){
						$tab.="<th align=center ".$tp[$iddenda]." width=30px ".$disdenda." name=listhenda[] id=pdt##".$iddenda.">".$kddenda."</th>";
					}
					
				$tab.="</tr>
				</thead>";
				
				$no = 0;
				$where = "";
				if($param['karyawanid']!=''){
					$where.= " and a.nik='".$param['karyawanid']."'";					
				}
				if($param['blok']!=''){
					$where.= " and a.kodeorg='".$param['blok']."'";					
				}
				
				$countkar=$data=array();
				$str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $param['notransaksi'] . "' ".$where." order by b.namakaryawan asc";
				$res = fetchdata($str);
				foreach($res as $bar){
					$nmkar[$bar['nik']]=$bar['namakaryawan'];
					$nik2[$bar['nik']]=$bar['nik2'];
					$subbg[$bar['nik']]=$bar['subbagian'];
					$penlty[$bar['nik']]+=$bar['upahpenalty'];
					$ket[$bar['nik']]=$bar['keterangan'];
					$thntnm[$bar['kodeorg']]=$bar['tahuntanam'];
					$bjr[$bar['kodeorg']]=$bar['bjr'];
					
					$data[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]=$bar['noreferensi'];
					$urut[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]=$bar['nourut'];
					$jjg[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['hasilkerja'];
					$ha[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['luaspanen'];
					$brd[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['brondolan'];
					$kg[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['hasilkerjakg'];
					$hk[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['jumlahhk'];
					$upah[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahkerja'];
					$upen[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpenalty'];
					$upre[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpremi'];
					$sb[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['premibasis'];
					$sb2[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['premibasis2'];
					$lb1[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpremilebihbasis'];
					$lb2[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['upahpremilebihbasis2'];
					$rpbrd[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['premibrondol'];
					$rppen[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['rupiahpenalty'];
					$norma[$bar['nik']][$bar['kodeorg']][$bar['tph']][$bar['sesi']][$bar['noreferensi']]+=$bar['norma'];
					
					$countkar[$bar['nik']][$bar['kodeorg']]=1;
					#$countkar[$bar['nik']][$bar['kodeorg']]=1;
				}
				
				$jmlkary=array();
				foreach($countkar as $nik => $vblok){
					foreach($vblok as $blok => $val){
						$jmlkary[$nik]+=$val;
					}			
				}
				
				if($param['showdetail']=='0'){
					$disdet="style=display:none;";
				}else{
					$disdet="style=display:'';";
				}
				if($param['showblok']=='0'){
					$disblok="style=background-color:#C9FEFA;display:none;";
				}else{
					$disblok="style=background-color:#C9FEFA;";
				}
				if($param['showkary']=='0'){
					$diskary="style=background-color:#FDEDEC;display:none;";
				}else{
					$diskary="style=background-color:#FDEDEC;";
				}
				
				if(count($data)==0){
					$tab.="<tr class=rowcontent>
								<td id=datadetailkosong colspan=22 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>
								<td name=listdenda[] ".$disdenda." colspan=".count($dendapanen)."></td>
								<td colspan=3></td>
							</tr>";
				}else{
					$jlhdet=$nokar=$jlhblok=0;
					foreach($data as $nik => $vblok){
						foreach($vblok as $blok => $vtph){
							$nokar++;
							foreach($vtph as $tph => $vsesi){
								foreach($vsesi as $sesi => $vreff){
									foreach($vreff as $reff){
										$jlhdet++;
										$jlhblok=$nokar+$jlhdet;
									}
								}
							}
						}
					}
					
					$no=$nokar=$nokarnik=0;
					foreach($data as $nik => $vblok){
						$rownik=0;$nokarnik++;
						foreach($vblok as $blok => $vtph){
						$nokar++;$row=0;
							foreach($vtph as $tph => $vsesi){
								foreach($vsesi as $sesi => $vreff){
									foreach($vreff as $reff){
										$row++;$rownik++;$no++;
										$bgcolor=$title=$color=$cp=$doublec="";
										$doublec="style=cursor:pointer; title='Double click untuk filter.'";
										if($jmlkary[$nik]>1){
											$bgcolor="style=color:#06BA10;cursor:pointer;";
											$bgcolor.=" title = 'Karyawan Panen lebih dari 1 blok.'";
										}
										if($subbg[$nik]!=substr($blok,0,6)){
											$color="style=color:blue;cursor:pointer;";
											$color.=" title =\"Karyawan melakukan asistensi / lokasi tugas karyawan berbeda dengan lokasi kerjanya.\nLokasi Tugas Karyawan : ".$subbg[$nik]."\nLokasi Bekerja Karyawan : ".substr($blok,0,6)."\"";
										}
										if($penlty[$nik]){
											$cp="style=color:red; title=\"Untuk karyawan KHT jika tidak sampai 1 HK maka akan ada potongan upah.\"";
										}
										if($nik2[$nik]!=''){$nkkry=$nik2[$nik]." - ";}
										if($subbg[$nik]!=''){$divkry=$subbg[$nik]." - ";}
										$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$blok."'");
										if($nmorg[$blok]==$blok){
											$blkkry=substr($blok,6,4);
										}else{
											$blkkry=$nmorg[$blok];
										}
										
										$tab.="<tr class=rowcontent onclick=getmark(this.id); id=rowdetail".$no." ".$disdet.">";
										$tab.="<td align=center>".$no."</td>";
										$tab.="<td align=left ".$color." ".$doublec." ondblclick=cariby('".$nik."','namakary')>".getNamaKaryawan($nik)."</td>";
										$tab.="<td align=center ".$bgcolor." ".$doublec." ondblclick=cariby('".$blok."','blok')>".$blkkry."</td>";
										$tab.="<td align=center>".substr($tph,10,10)."</td>";
										$tab.="<td align=center>".$sesi."</td>";
										$tab.="<td align=center ".$bgcolor." ".$doublec." ondblclick=cariby('".$thntnm[$blok]."','tt') ".$bgcolor.">".$thntnm[$blok]."</td>";
										if($ket[$nik]==''){$ket[$nik]="KERJA";}
										$tab.="<td align=center>".$ket[$nik]."</td>";
										$tab.="<td align=right>".@numb_format($bjr[$blok],2) . "</td>";
										$tab.="<td align=right>".@numb_format($ha[$nik][$blok][$tph][$sesi][$reff],2)."</td>";
										$tab.="<td align=right>".@numb_format($jjg[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($norma[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($brd[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($kg[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($hk[$nik][$blok][$tph][$sesi][$reff],2)."</td>";
										$tab.="<td align=right>".@numb_format($upah[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right ".$cp.">".@numb_format($upen[$nik][$blok][$tph][$sesi][$reff])."</td>";
										//$tab.="<td align=right hidden>".@numb_format($upre[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($sb[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($sb2[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($lb1[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($lb2[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($rpbrd[$nik][$blok][$tph][$sesi][$reff])."</td>";
										
										@$st_ha[$nik][$blok]+=$ha[$nik][$blok][$tph][$sesi][$reff];
										@$st_jjg[$nik][$blok]+=$jjg[$nik][$blok][$tph][$sesi][$reff];
										@$st_norma[$nik][$blok]+=$norma[$nik][$blok][$tph][$sesi][$reff];
										@$st_brd[$nik][$blok]+=$brd[$nik][$blok][$tph][$sesi][$reff];
										@$st_kg[$nik][$blok]+=$kg[$nik][$blok][$tph][$sesi][$reff];
										@$st_upah[$nik][$blok]+=$upah[$nik][$blok][$tph][$sesi][$reff];
										@$st_hk[$nik][$blok]+=$hk[$nik][$blok][$tph][$sesi][$reff];
										@$st_upen[$nik][$blok]+=$upen[$nik][$blok][$tph][$sesi][$reff];
										@$st_sb[$nik][$blok]+=$sb[$nik][$blok][$tph][$sesi][$reff];
										@$st_sb2[$nik][$blok]+=$sb2[$nik][$blok][$tph][$sesi][$reff];
										@$st_lb1[$nik][$blok]+=$lb1[$nik][$blok][$tph][$sesi][$reff];
										@$st_lb2[$nik][$blok]+=$lb2[$nik][$blok][$tph][$sesi][$reff];
										@$st_rpbrd[$nik][$blok]+=$rpbrd[$nik][$blok][$tph][$sesi][$reff];
										@$st_rppen[$nik][$blok]+=$rppen[$nik][$blok][$tph][$sesi][$reff];
										@$st_upre[$nik][$blok]+=$upre[$nik][$blok][$tph][$sesi][$reff];
										
										@$stn_ha[$nik]+=$ha[$nik][$blok][$tph][$sesi][$reff];
										@$stn_jjg[$nik]+=$jjg[$nik][$blok][$tph][$sesi][$reff];
										@$stn_norma[$nik]+=$norma[$nik][$blok][$tph][$sesi][$reff];
										@$stn_brd[$nik]+=$brd[$nik][$blok][$tph][$sesi][$reff];
										@$stn_kg[$nik]+=$kg[$nik][$blok][$tph][$sesi][$reff];
										@$stn_upah[$nik]+=$upah[$nik][$blok][$tph][$sesi][$reff];
										@$stn_hk[$nik]+=$hk[$nik][$blok][$tph][$sesi][$reff];
										@$stn_upen[$nik]+=$upen[$nik][$blok][$tph][$sesi][$reff];
										@$stn_sb[$nik]+=$sb[$nik][$blok][$tph][$sesi][$reff];
										@$stn_sb2[$nik]+=$sb2[$nik][$blok][$tph][$sesi][$reff];
										@$stn_lb1[$nik]+=$lb1[$nik][$blok][$tph][$sesi][$reff];
										@$stn_lb2[$nik]+=$lb2[$nik][$blok][$tph][$sesi][$reff];
										@$stn_rpbrd[$nik]+=$rpbrd[$nik][$blok][$tph][$sesi][$reff];
										@$stn_rppen[$nik]+=$rppen[$nik][$blok][$tph][$sesi][$reff];
										@$stn_upre[$nik]+=$upre[$nik][$blok][$tph][$sesi][$reff];
										
										@$tluas+=$ha[$nik][$blok][$tph][$sesi][$reff];
										@$tjjg+=$jjg[$nik][$blok][$tph][$sesi][$reff];
										@$tnorma+=$norma[$nik][$blok][$tph][$sesi][$reff];
										@$tbrd+=$brd[$nik][$blok][$tph][$sesi][$reff];
										@$tkg+=$kg[$nik][$blok][$tph][$sesi][$reff];
										@$tupah+=$upah[$nik][$blok][$tph][$sesi][$reff];
										@$thk+=$hk[$nik][$blok][$tph][$sesi][$reff];
										@$tdenda+=$upen[$nik][$blok][$tph][$sesi][$reff];
										@$tpbss+=$sb[$nik][$blok][$tph][$sesi][$reff];
										@$tpbss2+=$sb2[$nik][$blok][$tph][$sesi][$reff];
										@$tplb+=$lb1[$nik][$blok][$tph][$sesi][$reff];
										@$tplb2+=$lb2[$nik][$blok][$tph][$sesi][$reff];
										@$trpbrd+=$rpbrd[$nik][$blok][$tph][$sesi][$reff];
										@$trrp+=$rppen[$nik][$blok][$tph][$sesi][$reff];
										@$tupahpremi+=$upre[$nik][$blok][$tph][$sesi][$reff];
										
										#denda list data 
										$strd = ""; $denda=array();
										$strd = "select * from " . $dbname . ".kebun_mutubuah where notransaksi='".$param['notransaksi']."' and kodeorg='".$blok."' and nik='".$nik."' and tph='".$tph."' and nourut='".$urut[$nik][$blok][$tph][$sesi][$reff]."' and sesi='".$sesi."' and noreferensi='".$reff."'";
										$resd = fetchdata($strd);
										foreach($resd as $bard){
											$denda[$bard['idjenis']]=$bard['nilai'];
										}
										$edit=""; $align=" align=right ";$nn=$disdenda;
										foreach($dendapanen as $iddenda => $kddenda){
											@$tab.="<td ".$align." ".$nn." ".$tplistdata[$iddenda]."\nRp => ".$denda[$iddenda]." x ".$harga[$iddenda]." = ".@numb_format($denda[$iddenda]*$harga[$iddenda])." \" width=30px id=pddt##".$iddenda."##".$no.">".@numb_format($denda[$iddenda])."</td>";
											@$ttlp[$iddenda]+=$denda[$iddenda];
											@$edit.="####".$denda[$iddenda];
											
											$st_denda[$iddenda][$nik][$blok]+=$denda[$iddenda];
										}
										$gtperkar[$nik][$blok][$tph][$sesi][$reff]=(($upah[$nik][$blok][$tph][$sesi][$reff]-$upen[$nik][$blok][$tph][$sesi][$reff])+$upre[$nik][$blok][$tph][$sesi][$reff]+$sb[$nik][$blok][$tph][$sesi][$reff]+$sb2[$nik][$blok][$tph][$sesi][$reff]+$lb1[$nik][$blok][$tph][$sesi][$reff]+$lb2[$nik][$blok][$tph][$sesi][$reff]+$rpbrd[$nik][$blok][$tph][$sesi][$reff])-$rppen[$nik][$blok][$tph][$sesi][$reff];
										
										@$st_gtperkar[$nik][$blok]+=$gtperkar[$nik][$blok][$tph][$sesi][$reff];
										@$stn_gtperkar[$nik]+=$gtperkar[$nik][$blok][$tph][$sesi][$reff];
										@$t_gtperkar+=$gtperkar[$nik][$blok][$tph][$sesi][$reff];
										
										$tab.="<td align=right>".@numb_format($rppen[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$tab.="<td align=right>".@numb_format($gtperkar[$nik][$blok][$tph][$sesi][$reff])."</td>";
										$namakary=$nmkar[$nik];
									}
								}
							}
						}
					}
				
			
					
					$tab.="<tr class=rowcontent style=background-color:#A3E4D7>";
					$tab.="<td colspan=2 align=center><b>GRAND TOTAL</b></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td align=right>".@numb_format($tluas,2)."</td>";
					$tab.="<td align=right>".@numb_format($tjjg)."</td>";
					$tab.="<td align=right>".@numb_format($norma)."</td>";
					$tab.="<td align=right>".@numb_format($tbrd)."</td>";
					$tab.="<td align=right>".@numb_format($tkg)."</td>";
					$tab.="<td align=right>".@numb_format($thk,2)."</td>";
					$tab.="<td align=right>".@numb_format($tupah)."</td>";
					$tab.="<td align=right ".$cp.">".@numb_format($tdenda)."</td>";
					//$tab.="<td align=right hidden>".@numb_format($tupahpremi)."</td>";
					$tab.="<td align=right>".@numb_format($tpbss)."</td>";
					$tab.="<td align=right>".@numb_format($tpbss2)."</td>";
					$tab.="<td align=right>".@numb_format($tplb)."</td>";
					$tab.="<td align=right>".@numb_format($tplb2)."</td>";
					$tab.="<td align=right>".@numb_format($trpbrd)."</td>";
					#ttl denda list data
					foreach($dendapanen as $iddenda => $kddenda){
						$tab.="<td ".$align." ".$nn." ".$tp[$iddenda]." width=30px name=listdenda[] id=tpddt##".$iddenda.">".@numb_format($ttlp[$iddenda])."</td>";
					}
					
					$tab.="<td align=right>".@numb_format($trrp)."</td>";
					$tab.="<td align=right>".@numb_format($t_gtperkar)."</td>";
					$tab.="</tr>";
					
					
					#rekapitulasi
					$str = "select a.notransaksi, a.kodeorg,a.nik,a.nourut,a.tahuntanam,sum(upahpenalty) as upahpenalty,sum(a.hasilkerja) as hasilkerja, sum(a.hasilkerjakg) as kg, sum(a.jumlahhk) as hk, sum(a.norma) as norma, sum(a.upahkerja) as upah, sum(a.premibasis) as bss, sum(a.premibasis2) as bss2, sum(a.upahpremilebihbasis) as lbbss,sum(a.upahpremilebihbasis2) as lbbss2, sum(a.brondolan) as brd,sum(a.premibrondol) as rpbrd, sum(a.luaspanen) as ha, sum(a.rupiahpenalty) as rupiahpenalty, sum(a.upahpremi) as upahpremi  
					from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $param['notransaksi'] . "' ".$where." group by kodeorg order by a.kodeorg asc";
					$row=fetchData($str);
					$nox='0';
					foreach($row as $bar) {
						$nox++;
						$tab.="<tr class=rowcontent style=background-color:#AED6F1>";
						$no+=1;
						$tab.="<td align=center>" . $nox . "</td>";
						if($nox==1){
							$tab.="<td align=center><b>REKAPITULASI</b></td>";
						}else{
							$tab.="<td></td>";
						}
						$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
						if($nmorg[$bar['kodeorg']]==$bar['kodeorg']){
							$blkkry=substr($bar['kodeorg'],6,4);
						}else{
							$blkkry=$nmorg[$bar['kodeorg']];
						}
						$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['kodeorg']."','blok')>" . $blkkry. "</td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center style=cursor:pointer; title='Double click untuk filter.' ondblclick=cariby('".$bar['tahuntanam']."','tt')>" . $bar['tahuntanam'] . "</td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=right>" . numb_format($bar['ha'],2) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['hasilkerja']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['norma']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['brd']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['kg']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['hk'],2) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['upah']) . "</td>";
						$tab.="<td align=right ".$cp.">" . numb_format($bar['upahpenalty']) . "</td>";
						//$tab.="<td align=right hidden>" . numb_format($bar['upahpremi']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['bss']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['bss2']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['lbbss']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['lbbss2']) . "</td>";
						$tab.="<td align=right>" . numb_format($bar['rpbrd']) . "</td>";
						
						#denda list data
						$strd = ""; $denda=array();
						$strd = "select * from " . $dbname . ".kebun_mutubuah where notransaksi='" . $bar['notransaksi'] . "' and kodeorg='".$bar['kodeorg']."' and nik='".$bar['nik']."' and nourut='".$bar['nourut']."'";
						$resd = fetchdata($strd);
						foreach($resd as $bard){
							$denda[$bard['idjenis']]=$bard['nilai'];
						}
						
						foreach($dendapanen as $iddenda => $kddenda){
							$tab.="<td align=right ".$nn." ".$tp[$iddenda]." width=30px name=listdenda[] id=rtpddt##".$iddenda."##".$nox.">".@numb_format($denda[$iddenda])."</td>";
						}
						
						$tab.="<td align=right>" . numb_format($bar['rupiahpenalty']) . "</td>";
						$tab.="<td align=right>" . numb_format((($bar['upah']-$bar['upahpenalty'])+$bar['upahpremi']+$bar['bss']+$bar['lbbss']+$bar['lbbss2']+$bar['rpbrd'])-$bar['rupiahpenalty']) . "</td>";
					}
					#rekapitulasi end
				}
				$tab.="</tr>";
				$tab.="</table>";
				
				$tab.="<br />Penjelasan kode denda<br />";
				if($proses=='excel' or $proses=='pdf'){
					$tab.="<table class=sortable border=1 cellspacing=0>";
				}else{
					$tab.="<table class=sortable cellpadding=5 border=0 cellspacing=1>";
				}
				
				$tab.="<thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<th align=center>No</th>";
				$tab.="<th  align=center>".$_SESSION['lang']['kode']."</th>";
				$tab.="<th  align=center>".$_SESSION['lang']['nama']."</th>";
				$tab.="<th  align=center>".$_SESSION['lang']['satuan']."</th>";
				$tab.="<th  align=center>".$_SESSION['lang']['denda']."</th>";
				$tab.="</tr>";
				$tab.="</thead>";
				$nodenda=0;
				foreach($dendapanen as $iddenda => $kddenda){
					$nodenda++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$nodenda."</td>";
					$tab.="<td>".$kddenda."</td>";
					$tab.="<td>".$namadenda[$iddenda]."</td>";
					$tab.="<td>".ucfirst(strtolower($sat[$iddenda]))."</td>";
					$tab.="<td align=right>".numb_format($harga[$iddenda])."</td>";
					$tab.="</tr>";
				}
				$tab.="</table>";
		break;
		
		case'2':
		
			$tab.="<thead>";
			$tab.="<tr class=rowheader>";
			$tab.="<th align=center>No</th>";
			$tab.="<th  align=center>".$_SESSION['lang']['nik']."</th>";
			$tab.="<th  align=center>".$_SESSION['lang']['blok']."</th>";
			$tab.="<th  align=center width=60px>".$_SESSION['lang']['hasilkerja']."</th>";
			$tab.="<th  align=center>".$_SESSION['lang']['luas']."</th>";
			$tab.="<th  align=center>".$_SESSION['lang']['hk2']."</th>";
			$tab.="<th  align=center>".$_SESSION['lang']['upahkerja']."</th>";
			$tab.="<th  align=center width=60px>".$_SESSION['lang']['upahpenalty']."</th>";        
			$tab.="<th  align=center width=60px>Upah Premi (Rp)</th>";        
			$tab.="<th align=center width=60px>".$_SESSION['lang']['premibasis']." 1 (Rp)</th>";
			$tab.="<th align=center width=60px>".$_SESSION['lang']['premibasis']." 2 (Rp)</th>";
			$tab.="<th align=center width=60px>".$_SESSION['lang']['premlebihbasis']." 1 (Rp)</th>";
			$tab.="<th align=center width=60px>".$_SESSION['lang']['premlebihbasis']." 2 (Rp)</th>";
			$tab.="<th align=center width=60px>Premi Brondol (Rp)</th>";
			$tab.="<th align=center width=60px>Total ".$_SESSION['lang']['upahpremi']."</th>";
			$tab.="<th align=center width=60px>".$_SESSION['lang']['rupiahpenalty']."</th>";
			$tab.="<th align=center width=60px>Total Premi Bersih (Rp)</th>";
			$tab.="<th align=center width=60px>".$_SESSION['lang']['total']." (Upah+Premi)</th>";
			$tab.="</tr></thead><tbody>";
			
			$no='0';
			$str="select karyawanid,kodeorg,sum(hasilkerja) as hasilkerja,sum(luaspanen) as luaspanen,sum(jumlahhk) as jumlahhk, sum(upahkerja) as upahkerja, sum(upahpenalty) as upahpenalty, sum(upahpremi) as upahpremi, sum(premibasis) as premibasis, sum(premibasis2) as premibasis2, sum(upahpremilebihbasis) as lbbss1, sum(upahpremilebihbasis2) as lbbss2, sum(premibrondol) as premibrondol, sum(upahpremilebihbasis) as upahpremilebihbasis, sum(rupiahpenalty) as rupiahpenalty  from ".$dbname.".kebun_prestasi_vw where notransaksi='".$param['notransaksi']."' group by karyawanid,kodeorg order by karyawanid asc";
			$res = fetchdata($str);
			foreach($res as $bar){
					$no++;
					$bgcolor=$title=$color='';
					$strx = "select count(nik) as jmlkary, nik from " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."' and nik='".$bar['karyawanid']."' group by nik";
					$barx = fetchdata($strx)[0];
					if(($bar['karyawanid']==$barx['nik']) and ($barx['jmlkary']>1)){
						$bgcolor="style=background-color:orange;";
						$title=" title = 'Karyawan Panen lebih dari 1 blok !'";
					}
						$tab.="<tr class=rowcontent ".$bgcolor." ".$title.">";
						$tab.="<td align=center>".$no."</td>";
						
						$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
						if($nmorg[$bar['kodeorg']]==$bar['kodeorg']){
							$blkkry=substr($bar['kodeorg'],6,4);
						}else{
							$blkkry=$nmorg[$bar['kodeorg']];
						}
						
						$tab.="<td>".getNamaKaryawan($bar['karyawanid'])."</td>";
						$tab.="<td>".$blkkry."</td>";
						$tab.="<td align=right>".$bar['hasilkerja']."</td>";
						$tab.="<td align=right>".numb_format($bar['luaspanen'],2)."</td>";
						$tab.="<td align=right>".numb_format($bar['jumlahhk'],2)."</td>";
						$tab.="<td align=right>".numb_format($bar['upahkerja'],0)."</td>";
						$tab.="<td align=right>".numb_format($bar['upahpenalty'],0)."</td>";                
						$tab.="<td align=right>".numb_format($bar['upahpremi'],0)."</td>";
						$tab.="<td align=right>".numb_format($bar['premibasis'],0)."</td>";
						$tab.="<td align=right>".numb_format($bar['premibasis2'],0)."</td>";
						$tab.="<td align=right>".numb_format($bar['lbbss1'],0)."</td>";
						$tab.="<td align=right>".numb_format($bar['lbbss2'],0)."</td>";
						$tab.="<td align=right>".numb_format($bar['premibrondol'],0)."</td>";
						$totPremi = $bar['upahpremi'] +$bar['premibasis']+$bar['premibasis2'] + $bar['lbbss1']+ $bar['lbbss2']+$bar['premibrondol'];
						$tab.="<td align=right>".numb_format($totPremi,0)."</td>";
						$tab.="<td align=right>".numb_format($bar['rupiahpenalty'],0)."</td>";
						$tab.="<td align=right>".numb_format($totPremi-$bar['rupiahpenalty'],0)."</td>";
						$sisa=($bar['upahkerja']-$bar['upahpenalty'])+($totPremi-$bar['rupiahpenalty']);
						$tab.="<td align=right>".numb_format($sisa,0)."</td>";
					$tab.="</tr>";
					@$totJanjang+=$bar['hasilkerja'];
					@$totLuas+=$bar['luaspanen'];
					@$totUpahKerja+=$bar['upahkerja'];
					@$tothk+=$bar['jumlahhk'];
					@$totupahpre+=$bar['upahpremi'];
					@$totUpahKerjapenalty+=$bar['upahpenalty'];
					@$totUpahPremi+=$bar['premibasis'];
					@$totUpahPremi2+=$bar['premibasis2'];
					@$totlbbss1+=$bar['lbbss1'];
					@$totlbbss2+=$bar['lbbss2'];
					@$totbrd+=$bar['premibrondol'];
					@$totUpahPremiLebihBasis+=$bar['upahpremilebihbasis'];
					@$totPremiAll+=$totPremi;
					@$totUpahDenda+=$bar['rupiahpenalty'];
					@$totSisa+=$sisa;
					
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=3 align=center>".$_SESSION['lang']['total']."</td>";
			$tab.="<td align=right>".numb_format($totJanjang,0)."</td>";
			$tab.="<td align=right>".numb_format($totLuas,2)."</td>";
			$tab.="<td align=right>".numb_format($tothk,2)."</td>";
			$tab.="<td align=right>".numb_format($totUpahKerja,0)."</td>";
			$tab.="<td align=right>".numb_format($totUpahKerjapenalty,0)."</td>";
			$tab.="<td align=right>".numb_format($totupahpre,0)."</td>";
			$tab.="<td align=right>".numb_format($totUpahPremi,0)."</td>";
			$tab.="<td align=right>".numb_format($totUpahPremi2,0)."</td>";
			$tab.="<td align=right>".numb_format($totlbbss1,0)."</td>";
			$tab.="<td align=right>".numb_format($totlbbss2,0)."</td>";
			$tab.="<td align=right>".numb_format($totbrd,0)."</td>";
			$tab.="<td align=right>".numb_format($totPremiAll,0)."</td>";
			$tab.="<td align=right>".numb_format($totUpahDenda,0)."</td>";
			$tab.="<td align=right>".numb_format($totPremiAll-$totUpahDenda,0)."</td>";
			$tab.="<td align=right>".numb_format($totSisa,0)."</td>";
			$tab.="</tr></tbody></table>";
			
		break;
	}
	
	
	## ABSENSI
	
	$str = "select * from ".$dbname.".sdm_absensidt where norefrensi='".$param['notransaksi']."'"; 
	$res = fetchdata($str);
	$absensi = count($res);
	if(count($res)>0){
		$tab.="<br />Absensi";
		if($proses=='excel' or $proses=='pdf'){
			$tab.="<table class=sortable border=1 cellspacing=0>";
		}else{
			$tab.="<table class=sortable border=0 cellpadding=5 cellspacing=1>";
		}

		$tab.="<thead>";
		$tab.="<tr class=rowheader>";
		$tab.="<td align=center>No</td>";
		$tab.="<td  align=center>".$_SESSION['lang']['nik']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['absensi']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['jhk']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['umr']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['upahpremi']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['total']."</td>";
		$tab.="</tr></thead><tbody>";
		
		$no=0;$thk=$tumr=$tpre=0;
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>"; 
			$tab.="<td>".getNamakaryawan($bar['karyawanid'])."</td>";
			$tab.="<td align=center>".$bar['absensi']."</td>";
			$tab.="<td align=right>".$bar['hk']."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['umr'],2)."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['premi'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['umr']+$bar['premi'])."</td>";
			$tab.="</tr>";
			$thk+=$bar['hk'];
			$tumr+=$bar['umr'];
			$tpre+=$bar['premi'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=3>Total</td>"; 
		$tab.="<td align=right>".hidezerodecimal($thk,2)."</td>";
		$tab.="<td align=right>".hidezerodecimal($tumr)."</td>";
		$tab.="<td align=right>".hidezerodecimal($tpre)."</td>";
		$tab.="<td align=right>".hidezerodecimal($tumr+$tpre)."</td>";
		$tab.="</tr>";	
	}
	
	$query="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$param['notransaksi']."'";
	$res = fetchdata($query);
	$posting = $res[0]['jurnal'];
	$noreff = $res[0]['noreferensi'];
	$nospk = $res[0]['nospk'];
	
	$query="select * from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notransaksi']."'";
	$resq = fetchdata($query);
	$panen = count($resq);
	
	$abs="";
	if(($panen==0 and $absensi>0) or $nospk!=''){
		$abs="absensi";
	}
	$jab = getPostingJabatan('panen'); 
	
	if($proses=='html'){		
		if($posting=='0'){
			if ($panen=='0' and $absensi=='0'){
				$tab.="<table width=100%>";
				$tab.="<td align=center style=background-color:cyan;height:25px;>Detail transaksi tidak ada.</td>";
				$tab.="</table>";
			}else{
				/* if(in_array($_SESSION['empl']['jabatan'],$jab) and $noreff==''){
					$tab.="<br><table width=100%>";
					$tab.="<td style=height:25px;><button class=mybutton style=background-color:blue;color:white; onclick=\"perbaikandata('".$param['nobkm']."');\">Kembalikan Ke Kerani</button></td>";
					$tab.="<td align=right style=height:25px;><button class=mybutton style=background-color:red;color:white; onclick=\"postingData('".$param['notransaksi']."','','".$abs."');\">".$_SESSION['lang']['posting']."</button></td>";
					$tab.="</table>";			
				}else{
					$tab.="<br><table width=100%>";
					$tab.="<td align=center style=background-color:cyan;height:25px;>Anda tidak memiliki otorisasi untuk melakukan Posting.</td>";
					$tab.="</table>";
				} */				
			}
		}elseif($posting==''){
			$tab.="<br><table width=100%>";
			$tab.="<td align=center style=background-color:cyan;height:25px;>Detail transaksi tidak ada.</td>";
			$tab.="</table>";
		}
	}
	$tab.="</fieldset>";	
	
	switch ($proses) {
	######PREVIEW
		case 'html':
			echo $tab;
			break;
		case'pdf':
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("Detail_Panen",array("Attachment"=>0));
		break;
	######EXCEL	
		case 'excel':
			$nop = "detail_panen.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("detail_panen", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
			
			/* $stream = $tab;
			$nop_ = "Detail_Panen";
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
			} */
			break;		
	}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>