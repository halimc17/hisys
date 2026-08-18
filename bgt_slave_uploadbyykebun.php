<?php
// ini_set('display_errors',1);

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

switch ($method) {
	case'delete':
		try{
		$owlPDO->beginTransaction();
			$wh="";
			$wh.=" and tipebudget = 'ESTATE' and kodebudget!='UMUM' and pta='BGT'";
			
			$str="delete from ".$dbname.".bgt_budget  where tahunbudget='".$param['tahun']."' and kodeorg like '".$param['divisi']."%' ".$wh."";
			$owlPDO->exec($str);
			
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
    case'loaddataupload':
        $tab = "";
		$where = "";
		if($param['tahun']!=''){
			$where.=" and a.tahunbudget = '".$param['tahun']."'";
		}
		
		$where.=" and a.kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		$where.=" and a.tipebudget = 'ESTATE' and a.kodebudget!='UMUM' and a.pta='BGT'"; //and a.keterangan='UPLOAD'";
        $tab="<table id=mytable cellpadding=10 cellspacing=1 border=0 class='sortable' width=100%>
			<thead>
				<tr class=rowheader style=height:25px>
					<th align=center>No.</th>
					<th align=centers style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
					<th align=center>".$_SESSION['lang']['kebun']."</th>
					<th align=center>".$_SESSION['lang']['divisi']."</th>
					<th align=center>".$_SESSION['lang']['luas']."</th>
					<th align=center>".$_SESSION['lang']['pokok']."</th>
					<th align=center>".$_SESSION['lang']['sph']."</th>
					<th align=center>".$_SESSION['lang']['produksi']."<br>(Kg)</th>
					<th align=center>".$_SESSION['lang']['sdm']."</th>
					<th align=center>".$_SESSION['lang']['material']."</th>
					<th align=center>".$_SESSION['lang']['peralatan']."</th>
					<th align=center>".$_SESSION['lang']['kontrak']."</th>
					<th align=center>".$_SESSION['lang']['kndran']."</th>
					<th align=center>".$_SESSION['lang']['total']."</th>
					<th align=center>Rp/Ha</th>
					<th align=center>Rp/Pkk</th>
					<th align=center>Rp/Kg</th>
					<th align=center width=30px>Action</th>
				</tr>
			</thead>
			<tbody>";
		$colspan=13;
		
		$str = "SELECT a.tutup, kodebudget, sum(rupiah) as jumlah, noakun, kegiatan, tipebudget, substr(kodeorg,1,6) as divisi, a.tahunbudget
		FROM " . $dbname . ".bgt_budget a 
		where substr(a.kodeorg,1,4) in (".getOrgDetail(2).") ".$where." group by a.tahunbudget, substr(kodeorg,1,6), kodebudget";
		$res = fetchData($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){				
				$data[$bar['tahunbudget']][$bar['divisi']]['sdm']+=$bar['jumlah'];
				$data[$bar['tahunbudget']][$bar['divisi']]['vol']+=$bar['hathnini'];
			}
			if(substr($bar['kodebudget'],0,2)=='M-'){				
				$data[$bar['tahunbudget']][$bar['divisi']]['mat']+=$bar['jumlah'];
			}
			if($bar['kodebudget']=='TOOL'){
				$data[$bar['tahunbudget']][$bar['divisi']]['tool']+=$bar['jumlah'];
			}
			if(substr($bar['kodebudget'],0,3)=='VHC'){				
				$data[$bar['tahunbudget']][$bar['divisi']]['vhc']+=$bar['jumlah'];
			}
			if(substr($bar['kodebudget'],0,7)=='KONTRAK'){				
				$data[$bar['tahunbudget']][$bar['divisi']]['kont']+=$bar['jumlah'];
			}
			$posting[$bar['tahunbudget']][$bar['divisi']]+=$bar['tutup'];
		}
		
		$str = "SELECT sum(hathnini) as jumlah,sum(pokokthnini) as pkk, tahunbudget, substr(kodeblok,1,6) as divisi FROM " . $dbname . ".bgt_blok group by tahunbudget, substr(kodeblok,1,6)";
		$res = fetchData($str);
		foreach($res as $bar){
			$data[$bar['tahunbudget']][$bar['divisi']]['luas']+=$bar['jumlah'];
			$data[$bar['tahunbudget']][$bar['divisi']]['pkk']+=$bar['pkk'];
		}
		
		$str = "SELECT sum(totalkg ) as jumlah, tahunbudget, substr(kodeblok,1,6) as divisi FROM " . $dbname . ".bgt_produksi_kebun group by tahunbudget, substr(kodeblok,1,6)";
		$res = fetchData($str);
		foreach($res as $bar){
			$data[$bar['tahunbudget']][$bar['divisi']]['kg']+=$bar['jumlah'];
		}
		//exit("error");
		
		$str="select a.*,substr(kodeorg,1,6) as divisi, sum(a.rupiah) as rupiah from ".$dbname.".bgt_budget a where substr(a.kodeorg,1,4) in (".getOrgDetail(2).") ".$where." group by a.tahunbudget, substr(kodeorg,1,6) order by a.tahunbudget desc,a.kodeorg asc";
		$res=fetchdata($str);
	
		foreach($res as $bar){
			$dt[$bar['tahunbudget']][substr($bar['kodeorg'],0,4)][$bar['divisi']]=$bar['divisi'];
			$ttp[$bar['tahunbudget']][$bar['divisi']]=$bar['tutup'];
		}
		$no=0;
		foreach($dt as $tahunbudget => $v1){
			foreach($v1 as $kodeorg => $v2){
				foreach($v2 as $divisi){					
					$no++;
					$tab.="<tr class='rowcontent'>";
					$tab.="<td style='text-align:center'>".$no."</td>";
					$tab.="<td align=center>".$tahunbudget."</td>";
					$tab.="<td align=left>".$kodeorg."</td>";
					$tab.="<td align=left>".$divisi."</td>";
					
					$popupluas="onclick=getdatadetail('luas','".$tahunbudget."','".$kodeorg."','".$divisi."','','','','')";
					$popupprd="onclick=getdatadetail('prd','".$tahunbudget."','".$kodeorg."','".$divisi."','','','','')";
					
					
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupluas.">".@hidezerodecimal($data[$tahunbudget][$divisi]['luas'],2)."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupluas.">".@hidezerodecimal($data[$tahunbudget][$divisi]['pkk'])."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupluas.">".@hidezerodecimal($data[$tahunbudget][$divisi]['pkk']/$data[$tahunbudget][$divisi]['luas'],2)."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupprd.">".@hidezerodecimal($data[$tahunbudget][$divisi]['kg'])."</td>";
					
					$popupsdm="onclick=getdatadetail('sdm','".$tahunbudget."','".$kodeorg."','".$divisi."','','','','')";
					$popupmat="onclick=getdatadetail('mat','".$tahunbudget."','".$kodeorg."','".$divisi."','','','','')";
					$popuptool="onclick=getdatadetail('tool','".$tahunbudget."','".$kodeorg."','".$divisi."','','','','')";
					$popupkont="onclick=getdatadetail('kont','".$tahunbudget."','".$kodeorg."','".$divisi."','','','','')";
					$popupvhc="onclick=getdatadetail('vhc','".$tahunbudget."','".$kodeorg."','".$divisi."','','','','')";
					
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupsdm.">".@hidezerodecimal($data[$tahunbudget][$divisi]['sdm'])."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupmat.">".@hidezerodecimal($data[$tahunbudget][$divisi]['mat'])."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popuptool.">".@hidezerodecimal($data[$tahunbudget][$divisi]['tool'])."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupkont.">".@hidezerodecimal($data[$tahunbudget][$divisi]['kont'])."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' ".$popupvhc.">".@hidezerodecimal($data[$tahunbudget][$divisi]['vhc'])."</td>";
					
					$ttl=$data[$tahunbudget][$divisi]['sdm']+$data[$tahunbudget][$divisi]['mat']+$data[$tahunbudget][$divisi]['tool']+$data[$tahunbudget][$divisi]['kont']+$data[$tahunbudget][$divisi]['vhc'];
					$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl)."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl/$data[$tahunbudget][$divisi]['luas'])."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl/$data[$tahunbudget][$divisi]['pkk'])."</td>";
					$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl/$data[$tahunbudget][$divisi]['kg'],2)."</td>";
					
					
					$subttl[$tahunbudget][$kodeorg]['sdm']+=$data[$tahunbudget][$divisi]['sdm'];
					$subttl[$tahunbudget][$kodeorg]['mat']+=$data[$tahunbudget][$divisi]['mat'];
					$subttl[$tahunbudget][$kodeorg]['tool']+=$data[$tahunbudget][$divisi]['tool'];
					$subttl[$tahunbudget][$kodeorg]['kont']+=$data[$tahunbudget][$divisi]['kont'];
					$subttl[$tahunbudget][$kodeorg]['vhc']+=$data[$tahunbudget][$divisi]['vhc'];
					$subttl[$tahunbudget][$kodeorg]['luas']+=$data[$tahunbudget][$divisi]['luas'];
					$subttl[$tahunbudget][$kodeorg]['pkk']+=$data[$tahunbudget][$divisi]['pkk'];
					$subttl[$tahunbudget][$kodeorg]['kg']+=$data[$tahunbudget][$divisi]['kg'];
					$subttl[$tahunbudget][$kodeorg]['ttl']+=$ttl;
					
					if($posting[$tahunbudget][$divisi]>0){
						$tab.="<td align=center>Closed</td>";
					}else{
						$tab.="<td align=center><img class=zImgBtn src=images/application/application_delete.png onclick=\"delupload('".$tahunbudget."','".$divisi."');\" title='Delete'></td>";
					}
					$tab.="</tr>";
				}
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='display:none'></td>";
				$tab.="<td style='display:none'></td>";
				$tab.="<td style='display:none'></td>";
				$tab.="<td style='text-align:center;background-color:#d4d2d2' colspan=4>Total</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['luas'],2)."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['pkk'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['pkk']/$subttl[$tahunbudget][$kodeorg]['luas'],2)."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['kg'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['sdm'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['mat'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['tool'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['kont'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['vhc'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl']/$subttl[$tahunbudget][$kodeorg]['luas'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl']/$subttl[$tahunbudget][$kodeorg]['pkk'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'>".@hidezerodecimal($subttl[$tahunbudget][$kodeorg]['ttl']/$subttl[$tahunbudget][$kodeorg]['kg'],2)."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;background-color:#d4d2d2'></td>";
				$tab.="</tr>";
			}
		}
		
		$tab.="</tbody>
			<tfoot>
			</tfoot>
			</table>
			";
        
		if($param['jenis']=='excel'){
			$nop = "bgt_prd_".$param['tahun'].".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("bgt_prd_".$param['tahun'], $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;
		}
	break;
	case'formupload':
		if($param['periode']==''){
			exit("Warning : Periode wajib diisi.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode organisasi wajib diisi.");
		}
		if($param['tipekary']==''){
			exit("Warning : Tipe Karyawan wajib diisi.");
		}
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=formuploadbpjs.csv");
		
		$where = $wh = "";
		if($param['kodeorg']!=''){
			$where.=" and lokasitugas = '".$param['kodeorg']."'";
		}
		if($param['tipekary']!=''){
			$where.=" and tipekaryawan = '".$param['tipekary']."'";
		}
		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tglakhir($param['periode']."-01")."')";
		
		$tab.="karyawanid,nikowl,nikabs,namakaryawan,bpjstk,bpjskes,bpjspen,";
		$str="select * from ".$dbname.".sdm_ho_component where `name` LIKE '%BPJS%' ORDER BY `plus`, `id`";
		$res=fetchdata($str);
		foreach($res as $bar){
			$tab.=$bar['id']."#".$bar['name'].",";
		}
		$tab.="\n";
		
		$str="select * from ".$dbname.".datakaryawan_hist where 1=1 ".$where." and periodegaji='".$param['periode']."' and version_type = 'B' order by namakaryawan asc";
		$res=fetchdata($str);
		if(count($res)==0){			
			$str="select * from ".$dbname.".datakaryawan where 1=1 ".$where." order by namakaryawan asc";
			$res=fetchdata($str);
		}
		
		foreach($res as $bar){
			$tab.=$bar['karyawanid'].",".$bar['nik'].",".$bar['namakaryawan2'].",".$bar['namakaryawan'].",".$bar['jms'].",".$bar['bpjs'].",".$bar['pensiun']."\n";
		}
		
		echo $tab;
	break;
	case'fileSelected':
		$data = $_POST;
		
		$param['kodeorg']= $_SESSION['empl']['lokasitugas'];
		$kodeorg         = $_SESSION['empl']['lokasitugas'];
		
		$str = "select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];

		$str = "select * from ".$dbname.".vhc_5jenisvhc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kelvhc[$bar['jenisvhc']]=$bar['kelompokvhc'];
		}
		
		// $flagblok = 'yes';
		$flagblok = 'no';
		
		if($_FILES['file']['error']==0){
			$filetype= strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file    = $_FILES['file']['tmp_name'];  
			
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);
				
				if($flagblok == 'yes') {
					$range = range('A','R');
					$header= array('tahun','divisi','blok','tt','kegiatan','nama_kegiatan','aruskas','nama_aruskas','kodebudget','rotasi','sat_volume','volume','kodebarang','nama_barang','kodevhc','sat_jumlah','jumlah','rupiah');
				} else {
					$range = range('A','Q');
					$header= array('tahun','divisi','tt','kegiatan','nama_kegiatan','aruskas','nama_aruskas','kodebudget','rotasi','sat_volume','volume','kodebarang','nama_barang','kodevhc','sat_jumlah','jumlah','rupiah');
				}
				
				foreach($header as $head){
					$cekhead[$head]=$head;
				}
				$arritem = $tahunlist = $divisilist = $ttlist = array();
				$validasiht= "";
				$err = "0";
				foreach($sheets as $noitem => $sheet){
					if($noitem>1){						
						$tahun = $sheet['A'];
						$tahunlist[$sheet['A']] = $sheet['A'];
						if($sheet['B']!=''){							
							$divisilist[$sheet['B']] = $sheet['B'];
						}

						if($flagblok == 'yes') {
							if($sheet['D']!=''){							
								$ttlist[$sheet['D']] = $sheet['D'];
							}
						} else {
							if($sheet['C']!=''){							
								$ttlist[$sheet['C']] = $sheet['C'];
							}
						}
					}
				}
				
				$str = "select * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$tahun."' and unitalokasi='".$kodeorg."' group by kodevhc";
				$res = fetchdata($str);
				foreach($res as $bar){					
					$tersedia[$bar['kodevhc']]+=$bar['jumlahjam'];
				}
				
				$str = "select distinct rpperjam,kodevhc from ".$dbname.".bgt_biaya_ken_per_jam where tahunbudget='".$tahun."'";
				$res = fetchdata($str);
				foreach($res as $bar){					
					$rpperhm[$bar['kodevhc']]=$bar['rpperjam'];
				}
				

				$str = "select kodevhc, sum(jumlah) as jumlah from ".$dbname.".bgt_budget where tahunbudget='".$tahun."' and tipebudget<>'TRK' and left(kodeorg,4)='".$kodeorg."' and kodevhc!='' group by kodevhc";
				$res = fetchdata($str);
				foreach($res as $bar){					
					$teralokasi[$bar['kodevhc']] += $bar['jumlah'];
				}
				
				$wh="";
				if(count($ttlist)>0){
					$wh.=" and thntnm in ('".implode("','",$ttlist)."')";
				}
				if(count($divisilist)>0){
					foreach($divisilist as $listd){
						$str = "select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$tahun."' ".$wh." and kodeblok like '".$listd."%'";
						$res = fetchdata($str);
						$jlh = count($res);
						if($jlh==0){
							$validasiht="Budget blok belum ada.<br>"; $err++;
						}
					}
				}else{
					$validasiht.="Budget blok belum ada.<br>"; $err++;
				}
				
				if(count($tahunlist)!=1){
					$validasiht.="Tahun budget tidak boleh lebih dari satu.<br>"; $err++;
				}
				$whr = " and kodeorg like '".$kodeorg."%' and tipebudget='ESTATE' and tahunbudget='".$tahun."' and pta='BGT' and kodebudget!='UMUM'";
				$str = "select * from ".$dbname.".bgt_budget where 1=1 ".$whr." and tutup='1' limit 1";
				$res = fetchdata($str);
				if(count($res)>0){
					$validasiht.="Budget sudah ditutup.<br>";$err++;
				}
				
				foreach($sheets as $noitem => $sheet){
					if($noitem==1){
						$tab.="<table class='sortable' cellspacing=1 cellpadding=5 border=0 >
						<thead>
							<tr class=rowheader style=height:25px>";
							$tab.="<th align=center width=30px>No.</th>";
							foreach($range as $idcol => $col){
								$style="";
								if($cekhead[$sheet[$col]]==""){
									$style="style=color:red; title='Kolom header mengalami perubahan.'";
								}
								$tab.="<th align=center ".$style.">".$sheet[$col]."</th>";
							}								
							$tab.="<th align=center>Status</th>";
						$tab.="</tr>
						</thead>";
						
						$str = "select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$tahun."'";
						$res = fetchData($str);
						foreach($res as $bar){
							$hargabarang[$bar['kodebarang']]=$bar['hargasatuan'];
						}
						
						$str = "select * from ".$dbname.".bgt_blok where tahunbudget='".$tahun."' and kodeblok like '".$param['kodeorg']."%' and closed='1'";
						$res = fetchData($str);
						foreach($res as $bar){
							$listtt[$bar['thntnm']]=$bar['thntnm'];
							$listdiv[substr($bar['kodeblok'],0,6)]=substr($bar['kodeblok'],0,6);
						}
						
						$str = "select * from ".$dbname.".bgt_kode";
						$res = fetchData($str);
						foreach($res as $bar){
							$namakdbgt[$bar['kodebudget']]=$bar['nama'];
							$akunkdbgt[$bar['kodebudget']]=$bar['noakun'];
						}
						$namakdbgt['MATERIAL']='MATERIAL';
					}else{
						$validasi   = "";

						if($flagblok == 'yes') {
							$tahun      = $sheet['A'];
							$divisi     = $sheet['B'];
							$blok       = $sheet['C'];
							$tt         = $sheet['D'];
							$kodekeg    = $sheet['E'];
							$namakeg    = getNamaKeg($sheet['E'],'namakegiatan');
							$aruskas    = $sheet['G'];
							$namaaruskas= getNamaAruskas($sheet['G']);
							$kodebudget = $sheet['I'];
							$rotasi     = $sheet['J'];
							$satvol     = getNamaKeg($sheet['E'],'satuan');
							$volume     = $sheet['L'];
							$kodebarang = $sheet['M'];
							$namabarang = getNamaBrg($sheet['M']);
							$kodevhc    = $sheet['O'];
							$satjlh     = $sheet['P'];
							$jumlah     = $sheet['Q'];
							$rupiah     = $sheet['R'];
						} else {
							$tahun      = $sheet['A'];
							$divisi     = $sheet['B'];
							$tt         = $sheet['C'];
							$kodekeg    = $sheet['D'];
							$namakeg    = getNamaKeg($sheet['D'],'namakegiatan');
							$aruskas    = $sheet['F'];
							$namaaruskas= getNamaAruskas($sheet['F']);
							$kodebudget = $sheet['H'];
							$rotasi     = $sheet['I'];
							$satvol     = getNamaKeg($sheet['D'],'satuan');
							$volume     = $sheet['K'];
							$kodebarang = $sheet['L'];
							$namabarang = getNamaBrg($sheet['L']);
							$kodevhc    = $sheet['N'];
							$satjlh     = $sheet['O'];
							$jumlah     = $sheet['P'];
							$rupiah     = $sheet['Q'];
						}
						
						$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".substr($kodekeg,0,7)."' order by a.noaruskas asc";
						$res = fetchdata($str);
						if(count($res)==1){
							$aruskas    = $res[0]['noaruskas'];
							$namaaruskas= getNamaAruskas($aruskas);
						}else{								
							foreach($res as $bar){
								$ak=false;
								if($aruskas==$bar['noaruskas']){
									$ak=true;										
								}
							}
						}
					
						if($tahun==''){$validasi.="Tahun Kosong.<br>";$err++;}
						if(strlen($tahun)!=4){$validasi.="Panjang tahun budget tidak sesuai.<br>";$err++;}
						if($divisi==''){$validasi.="Divisi tidak boleh Kosong.<br>";$err++;}
						if(strlen($divisi)<6){$validasi.="Panjang divisi tidak sesuai.<br>";$err++;}
						if($kodekeg==''){$validasi.="Kode kegiatan tidak boleh kosong.<br>";$err++;}
						if(strlen($kodekeg)!=9){$validasi.="Panjang kode kegiatan tidak sesuai.<br>";$err++;}
						if($namakeg==''){$validasi.="Nama kegiatan tidak terdaftar.<br>";$err++;}
						if($kodebudget=='VHC' and $kodevhc==''){
							if($aruskas==''){$validasi.="Kode kendaraan harus diisi.<br>";$err++;}
						}
						if(($kodebudget=='MATERIAL' or $kodebudget=='TOOL') and $kodebarang==''){
							if($aruskas==''){$validasi.="Kode barang harus diisi.<br>";$err++;}
						}
						if($kodevhc==''){
							if($aruskas==''){$validasi.="Arus kas kosong.<br>";$err++;}
							if($namaaruskas==''){$validasi.="Nama arus kas tidak terdaftar.<br>";$err++;}
							if($ak==false){
								if($namaaruskas==''){$validasi.="Arus kas tidak sesuai.<br>";$err++;}									
							}
						}
						if($volume==''){$validasi.="Volume tidak boleh kosong.<br>";$err++;}
						
						$varvhc="";
						if($kodevhc!=''){
							if($jumlah<='0'){$validasi.="Jumlah harus diisi.<br>";$err++;}
							if($rpperhm[$kodevhc]==0){$validasi.="Budget traksi belum diinput.<br>";$err++;}
							$sisa[$kodevhc] = $tersedia[$kodevhc]-$teralokasi[$kodevhc];
							if(round($jumlah,5)>round($sisa[$kodevhc],5)){ // kalo ga diginiin, sisa 248, diinput 248 dibilang over?
							  $validasi.="Total HM/KM Kendaraan ".$kodevhc." : Tersedia = ".number_format($tersedia[$kodevhc],2)." Sudah teralokasi = ".number_format($teralokasi[$kodevhc],2)." Sisa = ".number_format($sisa[$kodevhc],2)."<br>";$err++;
							}
							
							if($rupiah!=round($rpperhm[$kodevhc]*$jumlah)){
								$varvhc="Rupiah VRA telah diupdate, Sebelum : ".number_format($rupiah,0)." Selisih : ".number_format($rupiah-round($rpperhm[$kodevhc]*$jumlah),2)."";
								
								$ttlselisih+=$rupiah-round($rpperhm[$kodevhc]*$jumlah);
							}
							$rupiah = round($rpperhm[$kodevhc]*$jumlah);
							
							if($kelvhc[getvhc($kodevhc,'jenisvhc')]=='KD'){
								$satjlh = "KM";
							}else{
								$satjlh = "HM";
							}
						}
						$selisih="";
						if($kodebarang!=''){
							if($jumlah<='0'){$validasi.="Jumlah harus diisi.<br>";$err++;}
							if($hargabarang[$kodebarang]<='0'){$validasi.="Harga barang belum ada.<br>";$err++;}
							if($rupiah!=round($hargabarang[$kodebarang]*$jumlah)){
								$selisih="Rupiah material telah diupdate, Sebelum : ".number_format($rupiah,0)." Selisih : ".number_format($rupiah-round($hargabarang[$kodebarang]*$jumlah),2)."";
								
								$ttlselisih+=$rupiah-round($hargabarang[$kodebarang]*$jumlah);
							}
							$rupiah = round($hargabarang[$kodebarang]*$jumlah);
							$satjlh = getSatBrg($kodebarang);
						}
						if($kodebudget==''){$validasi.="Kode budget tidak boleh kosong.<br>";$err++;}
						if($namakdbgt[$kodebudget]==''){$validasi.="Kode budget tidak terdaftar.<br>";$err++;}
						
						if($rupiah<='0'){
							if($jumlah<='0'){$validasi.="Rupiah harus diisi.<br>";$err++;}
						}
						
						if(substr($divisi,0,4)!=$param['kodeorg']){
							$validasi.="Divisi tidak sesuai dengan lokasi tugas anda.<br>";
						}
						
						$varupah="";
						if(substr($kodebudget,0,3)=='SDM'){
							$str = "select * from " . $dbname . ".bgt_upah where tahunbudget='".$tahun."' and kodeorg='".$param['kodeorg']."' and jumlah>'0' and golongan='".$kodebudget."'";
							$res = fetchdata($str);
							foreach($res as $bar){
								$rpperhkupah=$bar['jumlah'];
							}
							
							
							$varupah="Rupiah Upah telah diupdate, Sebelum : ".number_format($rupiah,0)." Selisih : ".number_format($rupiah-round($rpperhkupah*$jumlah),2)."";
							$rupiah = round($rpperhkupah*$jumlah);
							
							
							$satjlh = "HK";
							$method = "simpansdm";
						}
						if($kodebudget=='KONTRAK'){
							$satjlh = $satvol;
							$method = "simpankont";
						}
						if($kodebudget=='MATERIAL'){
							$method = "simpanmat";
							$kdbudget = "M-".substr($kodebarang,0,3);
						}else{
							$kdbudget=$kodebudget;
						}
						
						if($kodebudget=='TOOL'){
							$method = "simpanalat";
						}
						if($kodebudget=='VHC'){
							$method = "simpanvhc";
						}
						if($method==''){
							$validasi.="Kode budget salah.<br>";$err++;
						}
						
						if(substr($kodekeg,0,3)=='126'){
							$jenis='TBM';
						}
						if(substr($kodekeg,0,3)=='128'){
							$jenis='BBT';
						}
						if(substr($kodekeg,0,3)=='621'){
							$jenis='TM';
						}
						if(substr($kodekeg,0,3)=='611'){
							$jenis='TM';
						}
						
						$color="";
						if($validasiht!='' or $validasi!=''){
							$color="style=color:red";
						}
						
						$no++;

						if($flagblok == 'yes') {
							$tab.="<tr class=rowcontent ".$color." id=baris_".$no.">";
							$tab.="<td hidden>
										<input id=method_".$no." value=".$method.">
										<input id=kodeorg_".$no." value=".$kodeorg.">
										<input id=jenis_".$no." value=".$jenis.">
									</td>";
							$tab.="<td ".$color." align=center>".$no."</td>";
							$tab.="<td ".$color." align=center id=tahun_".$no.">".$tahun."</td>";
							$tab.="<td ".$color." align=center id=divisi_".$no.">".$divisi."</td>";
							$tab.="<td ".$color." align=center id=blok_".$no.">".$blok."</td>";
							$tab.="<td ".$color." align=center id=tt_".$no.">".$tt."</td>";
							$tab.="<td ".$color." align=center id=kodekeg_".$no.">".$kodekeg."</td>";
							$tab.="<td ".$color." align=left>".$namakeg."</td>";
							$tab.="<td ".$color." align=center id=aruskas_".$no.">".$aruskas."</td>";
							$tab.="<td ".$color." align=left>".$namaaruskas."</td>";
							$tab.="<td ".$color." align=left >".$kodebudget."</td>";
							$tab.="<td ".$color." align=left hidden id=kodebudget_".$no.">".$kdbudget."</td>";
							$tab.="<td ".$color." align=right id=rotasi_".$no.">".$rotasi."</td>";
							$tab.="<td ".$color." align=center id=satvol_".$no.">".$satvol."</td>";
							$tab.="<td ".$color." align=right id=volume_".$no.">".$volume."</td>";
							$tab.="<td ".$color." align=center id=kodebarang_".$no.">".$kodebarang."</td>";
							$tab.="<td ".$color." align=left>".$namabarang."</td>";
							$tab.="<td ".$color." align=left id=kodevhc_".$no.">".$kodevhc."</td>";
							$tab.="<td ".$color." align=center id=satjlh_".$no.">".$satjlh."</td>";
							$tab.="<td ".$color." align=right id=jumlah_".$no.">".$jumlah."</td>";
							$tab.="<td ".$color." align=right id=rupiah_".$no.">".number_format(round($rupiah))."</td>";
							$tab.="<td ".$color." align=left id=validasi_".$no.">".trim(nl2br($validasiht)).trim(nl2br($validasi)).$selisih.$varvhc.$varupah."</td>";
							$tab.="</tr>";
						} else {
							$tab.="<tr class=rowcontent ".$color." id=baris_".$no.">";
							$tab.="<td hidden>
										<input id=method_".$no." value=".$method.">
										<input id=kodeorg_".$no." value=".$kodeorg.">
										<input id=jenis_".$no." value=".$jenis.">
									</td>";
							$tab.="<td ".$color." align=center>".$no."</td>";
							$tab.="<td ".$color." align=center id=tahun_".$no.">".$tahun."</td>";
							$tab.="<td ".$color." align=center id=divisi_".$no.">".$divisi."</td>";
							$tab.="<td ".$color." align=center id=tt_".$no.">".$tt."</td>";
							$tab.="<td ".$color." align=center id=kodekeg_".$no.">".$kodekeg."</td>";
							$tab.="<td ".$color." align=left>".$namakeg."</td>";
							$tab.="<td ".$color." align=center id=aruskas_".$no.">".$aruskas."</td>";
							$tab.="<td ".$color." align=left>".$namaaruskas."</td>";
							$tab.="<td ".$color." align=left >".$kodebudget."</td>";
							$tab.="<td ".$color." align=left hidden id=kodebudget_".$no.">".$kdbudget."</td>";
							$tab.="<td ".$color." align=right id=rotasi_".$no.">".$rotasi."</td>";
							$tab.="<td ".$color." align=center id=satvol_".$no.">".$satvol."</td>";
							$tab.="<td ".$color." align=right id=volume_".$no.">".$volume."</td>";
							$tab.="<td ".$color." align=center id=kodebarang_".$no.">".$kodebarang."</td>";
							$tab.="<td ".$color." align=left>".$namabarang."</td>";
							$tab.="<td ".$color." align=left id=kodevhc_".$no.">".$kodevhc."</td>";
							$tab.="<td ".$color." align=center id=satjlh_".$no.">".$satjlh."</td>";
							$tab.="<td ".$color." align=right id=jumlah_".$no.">".$jumlah."</td>";
							$tab.="<td ".$color." align=right id=rupiah_".$no.">".number_format(round($rupiah))."</td>";
							$tab.="<td ".$color." align=left id=validasi_".$no.">".trim(nl2br($validasiht)).trim(nl2br($validasi)).$selisih.$varvhc.$varupah."</td>";
							$tab.="</tr>";
						}
						
						$ttlrp+=round($rupiah);
						
						$cekduplicate[$tahun][$divisi][$kodebudget][$kodekeg][$kodebarang][$kodevhc]+=1;
						$barisduplicate[$tahun][$divisi][$kodebudget][$kodekeg][$kodebarang][$kodevhc]=$no;
					}
				}
				
				$duplicate="<br>";
				foreach($cekduplicate as $t => $v1){
					foreach($v1 as $d => $v2){
						foreach($v2 as $k => $v3){
							foreach($v3 as $g => $v4){
								foreach($v4 as $b => $v5){
									foreach($v5 as $v => $nilai){
										if($nilai>1){
											//$duplicate.=$barisduplicate[$t][$d][$k][$g][$b][$v].", ";
											$duplicate.=$t.",".$d.",".$k.",".$g.",".$b.",".$v.";<br>";
										}
									}
								}
							}
						}
					}
				}
				
				// echo"<pre>";
				// print_r($barisduplicate);
				
				if($flagblok == 'yes') {
					if($duplicate!=''){					
						$tab.="<tr class=rowcontent>";
						$tab.="<td colspan=20 style=background-color:#fcdede;color:blue;>Ada data yang double : <b>".$duplicate."</b> (jika ada data duplicate maka data pada baris sebelumnya akan di replace dengan data baris terakhir)</td>";
						$tab.="</tr>";
					}
					
					$tab.="</tbody>";
					$tab.="<tfoot>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=18 align=center style=background-color:cyan;color:black;>T O T A L</td>";
					$tab.="<td align=right style=background-color:cyan;color:black;>".number_format(round($ttlrp))."</td>";
					$tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
					$tab.="</tr>";
				} else {
					if($duplicate!=''){					
						$tab.="<tr class=rowcontent>";
						$tab.="<td colspan=19 style=background-color:#fcdede;color:blue;>Ada data yang double : <b>".$duplicate."</b> (jika ada data duplicate maka data pada baris sebelumnya akan di replace dengan data baris terakhir)</td>";
						$tab.="</tr>";
					}
					
					$tab.="</tbody>";
					$tab.="<tfoot>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=17 align=center style=background-color:cyan;color:black;>T O T A L</td>";
					$tab.="<td align=right style=background-color:cyan;color:black;>".number_format(round($ttlrp))."</td>";
					$tab.="<td style=background-color:cyan;color:black;>".number_format($ttlselisih)."</td>";
					$tab.="</tr>";
				}
				
				
				if($err>0){
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=100 align=center style=color:black;font-size:20px;><b>Tombol simpan akan muncul jika tidak ditemukan baris yg berwarna merah.</b></td>";
					$tab.="</tr>";
				}else{
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=100 align=center><button id=btnsubmit class=mybutton onclick=\"simpan(".$no.")\">SaveAll</button></td>";
					$tab.="</tr>";
				}
				$tab.="</tfoot>";
				$tab.="</table>";
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
		
		echo $tab;
	break;
}

// function getvhc($kodevhc,$detail='nopol'){
// 	global $dbname;
//     global $owlPDO;
    
// 	$nopol='';
//     $str="select ".$detail." from ".$dbname.".vhc_5master where kodevhc='".$kodevhc."'";
// 	$res=fetchdata($str);
// 	$nopol=$res[0][$detail];
	
// 	return $nopol;    
// }
?>	