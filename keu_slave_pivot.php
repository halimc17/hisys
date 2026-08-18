<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$nmtt =makeOption($dbnamerpt,'setup_blok','kodeorg,tahuntanam');

$str = "select *  from " . $dbnamerpt . ".organisasi";
$res = fetchdatarpt($str);
foreach($res as $bar){
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	$nmpt[$bar['kodeorganisasi']]=$bar['induk'];
}

$str = "select *  from " . $dbnamerpt . ".keu_5akun";
$res = fetchdatarpt($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=$bar['namaakun'];
	$tipeakun[$bar['noakun']]=$bar['tipeakun'];
}
$str = "select *  from " . $dbnamerpt . ".bgt_regional_assignment";
$res = fetchdatarpt($str);
foreach($res as $bar){
	$nmreg[$bar['kodeunit']]=$bar['regional'];
}

$datasort= array();
$where=$whereakun="";
if($param['kegiatan']!=''){
	if($param['kegiatan']=='i'){
		$where.=" and kodekegiatan!='' and substr(noakun,1,3) in ('126','128','611','621','629','619')";
	}
	if($param['kegiatan']=='k'){
		$where.=" and kodekegiatan=''";
	}
	if($param['kegiatan_c']!=''){			
		$where.=" and kodekegiatan in (select kodekegiatan from ".$dbnamerpt.".setup_kegiatan where namakegiatan like '%".$param['kegiatan_c']."%')  and substr(noakun,1,3) in ('126','128','611','621','629','619')";
	}
}
if($param['blok']!=''){
	if($param['blok']=='i'){
		$where.=" and kodeblok!='' and substr(noakun,1,3) in ('126','128','611','621','629','619')";
	}
	if($param['blok']=='k'){
		$where.=" and kodeblok=''";
	}
	if($param['blok_c']!=''){			
		$where.=" and kodeblok like '%".$param['blok_c']."%'  and substr(noakun,1,3) in ('126','128','611','621','629','619')";
	}
}
if($param['barang']!=''){
	if($param['barang']=='i'){
		$where.=" and kodebarang!=''";
	}
	if($param['barang']=='k'){
		$where.=" and kodebarang=''";
	}
	if($param['barang_c']!=''){			
		$where.=" and kodebarang in (select kodebarang from ".$dbnamerpt.".log_5masterbarang where namabarang like '%".$param['barang_c']."%')";
	}
}
if($param['supplier']!=''){
	if($param['supplier']=='i'){
		$where.=" and kodesupplier!=''";
	}
	if($param['supplier']=='k'){
		$where.=" and kodesupplier=''";
	}
	if($param['supplier_c']!=''){			
		$where.=" and kodesupplier in (select supplierid from ".$dbnamerpt.".log_5supplier where namasupplier like '%".$param['supplier_c']."%')";
	}
}
if($param['karyawan']!=''){
	if($param['karyawan']=='i'){
		$where.=" and nik in (select karyawanid from ".$dbnamerpt.".datakaryawan)";
	}
	if($param['karyawan']=='k'){
		$where.=" and nik not in (select karyawanid from ".$dbnamerpt.".datakaryawan)";
	}
	if($param['karyawan_c']!=''){			
		$where.=" and nik in (select karyawanid from ".$dbnamerpt.".datakaryawan where namakaryawan like '%".$param['karyawan_c']."%')";
	}
}
if($param['noreff']!=''){
	if($param['noreff']=='i'){
		$where.=" and noreferensi!=''";
	}
	if($param['noreff']=='k'){
		$where.=" and noreferensi=''";
	}
	if($param['noreff_c']!=''){			
		$where.=" and noreferensi like '%".$param['noreff_c']."%'";
	}
}
if($param['nodok']!=''){
	if($param['nodok']=='i'){
		$where.=" and nodok!=''";
	}
	if($param['nodok']=='k'){
		$where.=" and nodok=''";
	}
	if($param['nodok_c']!=''){			
		$where.=" and nodok like '%".$param['nodok_c']."%'";
	}
}
if($param['keterangan']!=''){
	if($param['keterangan']=='i'){
		$where.=" and keterangan!=''";
	}
	if($param['keterangan']=='k'){
		$where.=" and keterangan=''";
	}
	if($param['keterangan_c']!=''){			
		$where.=" and keterangan like '%".$param['keterangan_c']."%'";
	}
}
if($param['noakun']!='' and $param['noakun2']!=''){	
	$whereakun=" and noakun between '".$param['noakun']."' and '".$param['noakun2']."'";	
}

switch($method){
	case'sumber':
		switch($param['jenis']){
			case'jurnal':
				$tab="<fieldset><legend>Info</legend><div>";
				$tab.="Sumber data :<li>Data Jurnal Detail, untuk menampilkan data lebih dari 1 bulan sebaiknya gunakan jenis Rekap Jurnal.</li>";
				$tab.="<li style=color:blue;font-weight:bold;>Jika pilihan Jenis berbeda dengan pilihan sebelumnya, pastikan anda Reload Frame terlebih dahulu.</li>";
				$tab.="</div></fieldset>";
			break;
			case'rekap':
				$tab="<fieldset><legend>Info</legend><div>";
				$tab.="Sumber data :<li>Data jurnal rekap per Periode, Noakun.</li>";
				$tab.="<li style=color:blue;font-weight:bold;>Jika pilihan Jenis berbeda dengan pilihan sebelumnya, pastikan anda Reload Frame terlebih dahulu.</li>";
				$tab.="</div></fieldset>";
			break;
		}
	break;
	case'rekap':
	case'rekapkeg':
	case'rekapsupp':
		$wh="";$whr="";
		if($param['pt']!=''){
			$wh.=" and kodeorg in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')";
			$whr.=" and kodeorg in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')";
		}else{
			if($param['jenis']=='data'){				
				exit("Warning: Kode PT harus diisi.");
			}
		}
		if($param['regional']!='' && $param['kodeorg']==''){
			$wh.=" and kodeorg in (select kodeunit from ".$dbnamerpt.".bgt_regional_assignment where regional='".$param['regional']."' and kodeunit in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')) "; 
			$whr.=" and kodeorg in (select kodeunit from ".$dbnamerpt.".bgt_regional_assignment where regional='".$param['regional']."' and kodeunit in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')) "; 
		}
		if($param['jenis']=='data' and $param['regional']==''){				
			exit("Warning: Kode Regional harus diisi.");
		}
		
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg='".$param['kodeorg']."'";	
			$whr.=" and kodeorg='".$param['kodeorg']."'";	
		}else{
			if($param['jenis']=='data'){				
				exit("Warning: Kode organisasi harus diisi.");
			}
		}
		if($param['periode']!=''){
			$wh.=" and periode between '".$param['periode']."' and '".$param['periode2']."'";	
			$whr.=" and periode between '".str_replace("-","",$param['periode'])."' and '".str_replace("-","",$param['periode2'])."'";	
		}
		$wh.=" and keu_jurnaldt_vw.nojurnal NOT LIKE '%CLSM%'";	
		
		if($param['method']=='rekap'){			
			$datae[]=array('PT','REGIONAL','UNIT','PERIODE','DIVISI','GROUP AKUN','KEL AKUN','NAMA KEL AKUN','TIPE AKUN','NOAKUN','NAMA AKUN','RUPIAH','D/K');
			$numb=array(10);
			
			$group="group by kodeorg, periode, divisi, kelbyy, noakun, debet, kredit";
		}
		if($param['method']=='rekapkeg'){
			$datae[]=array('PT','REGIONAL','UNIT','PERIODE','DIVISI','GROUP AKUN','KEL AKUN','NAMA KEL AKUN','TIPE AKUN','NOAKUN','NAMA AKUN','BLOK','KODE KEGIATAN','NAMA KEGIATAN','RUPIAH','D/K');
			$numb=array(13);
			
			$group="group by kodeorg, periode, divisi, kelbyy, noakun, debet, kredit, kodeblok, kodekegiatan";
		}
		if($param['method']=='rekapsupp'){			
			$datae[]=array('PT','REGIONAL','UNIT','PERIODE','GROUP AKUN','KEL AKUN','NAMA KEL AKUN','TIPE AKUN','NOAKUN','NAMA AKUN','ASSIGNMENT','CUSTOMER','RUPIAH','D/K');
			$numb=array(11);
			
			$group="group by kodeorg, periode, divisi, kelbyy, noakun, debet, kredit, kodesupplier, kodecustomer";
		}
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT");
		$col = array("D/K");
		$val = array("RUPIAH");
		
		
		$nmkeg   =makeOption($dbnamerpt,'setup_kegiatan','kodekegiatan,namakegiatan');
		$nmbrg   =makeOption($dbnamerpt,'log_5masterbarang','kodebarang,namabarang');
		$nmjurnal=makeOption($dbnamerpt,'keu_5parameterjurnal','jurnalid,keterangan');
		$nmsupp  =makeOption($dbnamerpt,'log_5supplier','supplierid,namasupplier');
		$nmcust  =makeOption($dbnamerpt,'pmn_4customer','kodecustomer,namacustomer');
		
		$rangeperiode = month_inbetween($param['periode'],$param['periode2']);
		
		$str = "select * from " . $dbnamerpt . ".keu_saldobulanan where 1=1 ".$whr." and noakun not in (select noakundebet from " . $dbnamerpt . ".keu_5parameterjurnal where jurnalid in ('CLM')) ".$whereakun."";
		$res = fetchdatarpt($str);
		foreach($res as $bar){
			foreach($rangeperiode as $bln){
				if(str_replace("-","",$bln)==$bar['periode']){
					$r="awal".addZero(substr($bar['periode'],-2),2);
					
					if($param['method']=='rekap'){						
						$data[]=array($nmpt[$bar['kodeorg']],$nmreg[$bar['kodeorg']],$bar['kodeorg'],substr($bar['periode'],0,4)."-".substr($bar['periode'],-2),"",substr($bar['noakun'],0,1)."-".$nmakun[substr($bar['noakun'],0,1)],substr($bar['noakun'],0,3),$nmakun[substr($bar['noakun'],0,3)],$tipeakun[$bar['noakun']],$bar['noakun'],$nmakun[$bar['noakun']],$bar[$r],"Saldo Awal");
					}
					
					if($param['method']=='rekapkeg'){						
						$data[]=array($nmpt[$bar['kodeorg']],$nmreg[$bar['kodeorg']],$bar['kodeorg'],substr($bar['periode'],0,4)."-".substr($bar['periode'],-2),"",substr($bar['noakun'],0,1)."-".$nmakun[substr($bar['noakun'],0,1)],substr($bar['noakun'],0,3),$nmakun[substr($bar['noakun'],0,3)],$tipeakun[$bar['noakun']],$bar['noakun'],$nmakun[$bar['noakun']],"","","",$bar[$r],"Saldo Awal");
					}
					
					if($param['method']=='rekapsupp'){						
						$data[]=array($nmpt[$bar['kodeorg']],$nmreg[$bar['kodeorg']],$bar['kodeorg'],substr($bar['periode'],0,4)."-".substr($bar['periode'],-2),substr($bar['noakun'],0,1)."-".$nmakun[substr($bar['noakun'],0,1)],substr($bar['noakun'],0,3),$nmakun[substr($bar['noakun'],0,3)],$tipeakun[$bar['noakun']],$bar['noakun'],$nmakun[$bar['noakun']],"","",$bar[$r],"Saldo Awal");
					}
				}
			}
		}
		
		$str = "select substr(noakun,1,3) as kelbyy, kodekegiatan, kodeorg, kodeblok as blok, substr(kodeblok,1,6) as divisi, periode as prd, noakun, sum(jumlah) as jlh, sum(debet) as db, sum(kredit) as kr, kodesupplier, kodecustomer from " . $dbnamerpt . ".keu_jurnaldt_vw where 1=1 ".$where." ".$wh."  ".$whereakun." ".$group."";
		$res = fetchdatarpt($str);
		foreach($res as $bar){
			if($param['method']=='rekap'){
				$data[]=array(
					cleanSpecialChar($nmpt[$bar['kodeorg']]),
					cleanSpecialChar($nmreg[$bar['kodeorg']]),
					$bar['kodeorg'],
					$bar['prd'],
					$bar['divisi'],
					substr($bar['kelbyy'],0,1)."-".cleanSpecialChar($nmakun[substr($bar['kelbyy'],0,1)]),
					$bar['kelbyy'],
					cleanSpecialChar($nmakun[$bar['kelbyy']]),
					cleanSpecialChar($tipeakun[$bar['noakun']]),
					$bar['noakun'],
					cleanSpecialChar($nmakun[$bar['noakun']]),
					$bar['jlh'],
					($bar['jlh']>=0?"Debet":"Kredit")
				);				
			}
			
			if($param['method']=='rekapkeg'){
				$data[]=array(
					cleanSpecialChar($nmpt[$bar['kodeorg']]),
					cleanSpecialChar($nmreg[$bar['kodeorg']]),
					$bar['kodeorg'],
					$bar['prd'],
					$bar['divisi'],
					substr($bar['kelbyy'],0,1)."-".cleanSpecialChar($nmakun[substr($bar['kelbyy'],0,1)]),
					$bar['kelbyy'],
					cleanSpecialChar($nmakun[$bar['kelbyy']]),
					cleanSpecialChar($tipeakun[$bar['noakun']]),
					$bar['noakun'],
					cleanSpecialChar($nmakun[$bar['noakun']]),
					cleanSpecialChar($nmorg[$bar['blok']]),
					$bar['kodekegiatan'],
					cleanSpecialChar($nmkeg[$bar['kodekegiatan']]),
					$bar['jlh'],
					($bar['jlh']>=0?"Debet":"Kredit")
				);
			}
			if($param['method']=='rekapsupp'){
				$data[]=array(
					$nmpt[$bar['kodeorg']],
					$nmreg[$bar['kodeorg']],
					$bar['kodeorg'],
					$bar['prd'],
					substr($bar['kelbyy'],0,1)."-".$nmakun[substr($bar['kelbyy'],0,1)],
					$bar['kelbyy'],
					$nmakun[$bar['kelbyy']],
					$tipeakun[$bar['noakun']],
					$bar['noakun'],
					$nmakun[$bar['noakun']],
					($nmsupp[$bar['kodesupplier']]==""?$bar['kodesupplier']:$nmsupp[$bar['kodesupplier']]),
					($nmcust[$bar['kodecustomer']]==""?$bar['kodecustomer']:$nmcust[$bar['kodecustomer']]),
					$bar['jlh'],
					($bar['jlh']>=0?"Debet":"Kredit")
				);
			}
		}
	break;
	case'jurnal':
		$wh="";$whr="";
		if($param['pt']!=''){
			$wh.=" and kodeorg in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')";
			$whr.=" and kodeorg in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')";
		}else{
			if($param['jenis']=='data'){				
				exit("Warning: Kode PT harus diisi.");
			}
		}
		if($param['regional']!='' && $param['kodeorg']==''){
			$wh.=" and kodeorg in (select kodeunit from ".$dbnamerpt.".bgt_regional_assignment where regional='".$param['regional']."' and kodeunit in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')) "; 
			$whr.=" and kodeorg in (select kodeunit from ".$dbnamerpt.".bgt_regional_assignment where regional='".$param['regional']."' and kodeunit in (select kodeorganisasi from ".$dbnamerpt.".organisasi where induk='".$param['pt']."')) "; 
		}
		if($param['jenis']=='data' and $param['regional']==''){				
			exit("Warning: Kode Regional harus diisi.");
		}
		
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg='".$param['kodeorg']."'";	
			$whr.=" and kodeorg='".$param['kodeorg']."'";	
		}else{
			if($param['jenis']=='data'){				
				exit("Warning: Kode organisasi harus diisi.");
			}
		}
		if($param['periode']!=''){
			$wh.=" and periode between '".$param['periode']."' and '".$param['periode2']."'";	
			$whr.=" and periode between '".str_replace("-","",$param['periode'])."' and '".str_replace("-","",$param['periode2'])."'";	
		}
		$wh.=" and keu_jurnaldt_vw.nojurnal NOT LIKE '%CLSM%'";	
	
		$datae[]=array('NOJURNAL','PT','REGIONAL','UNIT','PERIODE','TANGGAL','BLOK','TT','DIVISI','GROUP AKUN','KEL AKUN','NAMA KEL AKUN','TIPE AKUN','NOURUT','NOAKUN','NAMA AKUN','RUPIAH','D/K','KETERANGAN','KODE KEGIATAN','NAMA KEGIATAN','KODE BARANG','NAMA BARANG','NIK KARY','NAMA KARY','No REFF','NODOK','KODE VHC','KODE ASSET','KODE JURNAL','ASSIGNMENT','CUSTOMER');
		$numb=array(14);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT");
		$col = array("D/K");
		$val = array("RUPIAH");
		
		
		$nmkeg   =makeOption($dbnamerpt,'setup_kegiatan','kodekegiatan,namakegiatan');
		$nmbrg   =makeOption($dbnamerpt,'log_5masterbarang','kodebarang,namabarang');
		$nmjurnal=makeOption($dbnamerpt,'keu_5parameterjurnal','jurnalid,keterangan');
		$nmsupp  =makeOption($dbnamerpt,'log_5supplier','supplierid,namasupplier');
		$nmcust  =makeOption($dbnamerpt,'pmn_4customer','kodecustomer,namacustomer');
		
		$rangeperiode = month_inbetween($param['periode'],$param['periode2']);
		
		$str = "select * from " . $dbnamerpt . ".keu_saldobulanan where 1=1 ".$whr." and noakun not in (select noakundebet from " . $dbnamerpt . ".keu_5parameterjurnal where jurnalid in ('CLM'))  ".$whereakun."";
		$res = fetchdatarpt($str);
		foreach($res as $bar){
			foreach($rangeperiode as $bln){
				if(str_replace("-","",$bln)==$bar['periode']){
					$r="awal".addZero(substr($bar['periode'],-2),2);
					
					$data[]=array("",$nmpt[$bar['kodeorg']],$nmreg[$bar['kodeorg']],$bar['kodeorg'],substr($bar['periode'],0,4)."-".substr($bar['periode'],-2),"","","","",substr($bar['noakun'],0,1)."-".$nmakun[substr($bar['noakun'],0,1)],substr($bar['noakun'],0,3),$nmakun[substr($bar['noakun'],0,3)],$tipeakun[$bar['noakun']],"",$bar['noakun'],$nmakun[$bar['noakun']],$bar[$r],"Saldo Awal","","","","","","","","","","","","","");
				}
			}
		}
		
		$str = "select substr(noakun,1,3) as kelbyy,kodekegiatan,kodeorg,kodeblok as blok, substr(kodeblok,1,6) as divisi,periode as prd, noakun, jumlah,keu_jurnaldt_vw.*  from " . $dbnamerpt . ".keu_jurnaldt_vw where 1=1 ".$where."  ".$whereakun." ".$wh." "; #exit("error$str");
		$res = fetchdatarpt($str);
		foreach($res as $bar){
			$nikkary=makeOption($dbnamerpt,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['nik']."'");
			
			$data[]=array(
				cleanSpecialChar($bar['nojurnal']),
				cleanSpecialChar($nmpt[$bar['kodeorg']]),
				cleanSpecialChar($nmreg[$bar['kodeorg']]),
				cleanSpecialChar($bar['kodeorg']),
				$bar['prd'],
				$bar['tanggal'],
				cleanSpecialChar($nmorg[$bar['blok']]),
				cleanSpecialChar(getBlok($bar['blok'],'tahuntanam')),
				cleanSpecialChar($bar['divisi']),
				substr($bar['noakun'],0,1)."-".$nmakun[substr($bar['noakun'],0,1)],
				cleanSpecialChar($bar['kelbyy']),
				$nmakun[$bar['kelbyy']],
				cleanSpecialChar($tipeakun[$bar['noakun']]),
				$bar['nourut'],
				$bar['noakun'],
				$nmakun[$bar['noakun']],
				$bar['jumlah'],
				($bar['jumlah']>=0?"Debet":"Kredit"),
				cleanSpecialChar($bar['keterangan']),
				cleanSpecialChar($bar['kodekegiatan']),
				$nmkeg[$bar['kodekegiatan']],
				$bar['kodebarang'],
				cleanSpecialChar($nmbrg[$bar['kodebarang']]),
				cleanSpecialChar($nikkary[$bar['nik']]),
				cleanSpecialChar(getNamaKaryawan($bar['nik'])),
				cleanSpecialChar($bar['noreferensi']),
				cleanSpecialChar($bar['nodok']),
				cleanSpecialChar($bar['kodevhc']),
				cleanSpecialChar($bar['kodeasset']),
				cleanSpecialChar($nmjurnal[$bar['kodejurnal']]),
				cleanSpecialChar(($nmsupp[$bar['kodesupplier']]==""?$bar['kodesupplier']:$nmsupp[$bar['kodesupplier']])),
				cleanSpecialChar(($nmcust[$bar['kodecustomer']]==""?$bar['kodecustomer']:$nmcust[$bar['kodecustomer']]))
			);
		}
	break;
}


// echo"<pre>";
// print_r($data);
// echo"</pre>";
// exit("error");

if($param['jenis']=='data'){
	$tab="<table id=pvtTable cellpadding=1 cellspacing=1 border=0 class='sortable' width='100%' data-scroll-x='true' scroll-collapse='false'>
		<thead>
			<tr>";
			foreach($datae as $key => $var){
				foreach($var as $val){
					if($key==0){
						$tab.="<th>".$val."</th>";
						$jlhcolhead++;
					}
				}
			}
		
		$tab.="</tr>
			</thead><tbody>";
		$tab.="</tbody><tfoot>
			<tr>";
			foreach($datae as $key => $var){
				foreach($var as $val){
					if($key==0){					
						$tab.="<th>".$val."</th>";
					}
				}
			}
		
	$tab.="</tr></tfoot>";	
	$tab.="</table>";
	$tab.="<fieldset style=float:left;><legend>Show/Hide</legend><div>";
		$e=0;
		foreach($datae as $key => $var){
			foreach($var as $val){
				if($key==0){
					if($jlhcolhead>8){						
						$tab.="<button class=\"dt-button\" data-column=".$e.">".substr($val,0,4)."...</button>";
					}else{
						$tab.="<button class=\"dt-button\" data-column=".$e.">".$val."</button>";
					}
					$e++;
				}
			}
		}
	$tab.="</div></fieldset>";
	
	echo $tab."####".json_encode($data)."####".json_encode($numb);
}elseif($method=='sumber'){
	echo $tab;
}else{
	
	$path   = $_SERVER['HTTP_REFERER'];
	$path   = explode('/',$path);
	$rowfile= count($path)-1;
	$file   = $path[$rowfile];
	$file   = str_replace(".php","",$file);
	$idmenu = makeOption($dbnamerpt,'menu','action,id');

	$str = "select * from ".$dbnamerpt.".pivot_favorit where karyawanid='".$_SESSION['standard']['userid']."' and idmenu='".$idmenu[$file]."' and jenis ='".$param['method']."' order by id asc";
	$res = fetchdatarpt($str);
	$optlap="<option value=''>&nbsp;</option>";
	foreach($res as $key => $bar){
		$judullap.=$bar['id']."$$$$";
		$datalap.=$bar['data']."$$$$";
		$listopt[$bar['id']]=$bar['label'];
	}

	$str = "select a.*, b.label, b.data, a.id as id from ".$dbnamerpt.".pivot_favoritdt a left join ".$dbnamerpt.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' and b.jenis ='".$param['method']."' order by a.id asc";
	$res = fetchdatarpt($str);
	foreach ($res as $bar){
		$listopt[$bar['id']]=$bar['label'];
		$judullap.=$bar['id']."$$$$";
		$datalap.=$bar['data']."$$$$";
	}

	$str = "select distinct a.id, a.label from ".$dbnamerpt.".pivot_favorit a left join ".$dbnamerpt.".pivot_favoritdt b on a.id=b.id where 1=1 and ( a.karyawanid='".$_SESSION['standard']['userid']."' or b.karyawanid='".$_SESSION['standard']['userid']."') and a.idmenu='".$idmenu[$file]."' and a.jenis ='".$param['method']."' order by a.id asc";
	$res = fetchdatarpt($str);
	foreach ($res as $bar){
		$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
	}

	//echo json_encode($data)."####".json_encode($row)."####".json_encode($col)."####".json_encode($val)."####".json_encode($datasort);
	
	echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
}

function cleanSpecialChar($string) {
    // $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.
    // $string = preg_replace('/-+/',' ',$string);
    // $string = preg_replace('/\s+/', ' ', trim($string)); 
    // return $string;
	
	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string); //remove non-ascii chars
	return $hasil;
}

function clearsym($tulisan){
	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $tulisan); //remove non-ascii chars
	return $hasil;
}

?>
