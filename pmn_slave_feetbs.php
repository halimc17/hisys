<?php
header('Access-Control-Allow-Origin: *');
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
use Dompdf\Dompdf;

$param   = $_POST;
$method  = checkPostGet('method','');
$page    = checkPostGet('page', '');
$notrans = checkPostGet('notrans','');
$unit    = checkPostGet('unit','');
$alokasi = checkPostGet('alokasi','');
$tipetbs = checkPostGet('tipetbs','');
$supplier= checkPostGet('supplier','');
$tgl     = checkPostGet('tgl','');
$tgl1    = checkPostGet('tgl1','');
$tgl2    = checkPostGet('tgl2','');
$rekening= checkPostGet('rekening','');
$bruto   = checkPostGet('bruto','');
$potongan= checkPostGet('potongan','');
$netto   = checkPostGet('netto','');
$rpkg    = checkPostGet('rpkg','');
$total   = checkPostGet('total','');
$debet   = checkPostGet('debet','');
$kredit  = checkPostGet('kredit','');
$nospb   = checkPostGet('nospb','');
$persenppn   = checkPostGet('persenppn','');
$rpppn   = checkPostGet('rpppn','');
$posting   = checkPostGet('posting','');
$tanggalpengajuan= tanggalsystemn(checkPostGet('tanggalpengajuan', ''));
$urlefil         = checkPostGet('urlefil','0');
if ($tipetbs == 'SUPPLIERTBSAFI') {
	$table = 'kebun_tbsafiliasi';
} else if ($tipetbs == 'SUPPLIERTBSEXT') {
	$table = 'kebun_tbsexternal';
} else {
	$table = 'kebun_tbskud';
}

$nmtipe=array('SUPPLIERTBSAFI'=>'Afiliasi','SUPPLIERTBSINT'=>'Inti','SUPPLIERTBSKUD'=>'Plasma / KUD','SUPPLIERTBSEXT'=>'External');

switch ($method) {
    case 'getTipe':
		$opttipeTBS = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT DISTINCT tipetbs FROM ".$dbname.".pmn_5feetbs WHERE kodeunit = '".$unit."' ORDER BY tipetbs ASC";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $opttipeTBS .= "<option value='" . $bar['tipetbs'] . "'>" . $bar['tipetbs'] . "</option>";
        }
        echo $opttipeTBS;
	break;

    case 'getSup':
        $nmSup  = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
        $nmSuporg  = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$optSup = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT DISTINCT kodesupplier FROM ".$dbname.".pmn_5feetbs WHERE kodeunit='".$unit."' AND tipetbs='".$tipetbs."' ORDER BY kodeunit ASC";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            if (strlen($bar['kodesupplier']) > 4) {
                $optSup .= "<option value='" . $bar['kodesupplier'] . "'>" . $bar['kodesupplier'] . " - ".$nmSup[$bar['kodesupplier']]."</option>";
            }else{
                $optSup .= "<option value='" . $bar['kodesupplier'] . "'>" . $bar['kodesupplier'] . " - ".$nmSuporg[$bar['kodesupplier']]."</option>";
            }
        }
        echo $optSup;
	break;

	case 'loaddata':
		$where = '';
		
		#= untuk unit ht
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
		// $where.=" and  unit in ('".implode("','",$dtunit)."') ";

		if($notrans != ''){
			$where .= " AND notransaksi like '%".$notrans."%'";
		}
		if($unit != ''){
			$where .= " AND unit like '%".$unit."%'";
		}
		if($posting != ''){
			$where .= " AND posting = '".$posting."'";
		}
		if($tipetbs != ''){
			$where .= " AND tipetbs like '%".$tipetbs."%'";
		}
		if($supplier != ''){
			$where .= " AND kodesupplier like '%".$supplier."%'";
		}
		if($tgl != ''){
			$where .= " AND tanggal = '".tanggalsystemn($tgl)."'";
		}
		if($tgl1 != '' AND $tgl2 != ''){
			$where .= " AND (tanggaltbs1 >= '".tanggalsystemn($tgl1)."' AND tanggaltbs2 <= '".tanggalsystemn($tgl2)."')";
		}

		//$where.=" AND kodesupplier in (select kodesupplier from ".$dbname.".kebun_5namakud where substr(afdeling,1,4) in ('".implode("','",$dtunit)."')  ) ";

		$tab = "
				<table border=0 cellpadding=5 cellspacing=1 class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>".$_SESSION['lang']['nourut']."</th>
							<th align=center>".$_SESSION['lang']['notransaksi']."</th>
							<th align=center>".$_SESSION['lang']['unit']."</th>
							<th align=center>".$_SESSION['lang']['unit']."<br>".$_SESSION['lang']['alokasi']."</th>
							<th align=center>".$_SESSION['lang']['supplier']."</th>
							<th align=center>".$_SESSION['lang']['tanggal']."</th>
							<th align=center>".$_SESSION['lang']['tanggal']." TBS</th>
							<th align=center>".$_SESSION['lang']['tipe']." TBS</th>
							<th align=center>KG Brutto</th>
							<th align=center>KG ".$_SESSION['lang']['potongan']."</th>
							<th align=center>KG Netto</th>
							<th align=center>".$_SESSION['lang']['rpperkg']."</th>
							<th align=center>".$_SESSION['lang']['total']."</th>
							<th align=center>".$_SESSION['lang']['persenppn']."</th>
							<th align=center>".$_SESSION['lang']['ppn']."</th>
							<th align=center colspan=4>".$_SESSION['lang']['action']."</th>
						</tr>
					</thead>
					<tbody>";

        $limit = 10;
        $page = 1;
        $p = new Paging;

        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 1)
                $page = 1;
        }
        $maxdisplay = ($page * $limit);
        $offset = $p->cariPosisi($limit,$page);

        $str = "SELECT COUNT(*) as jmlhrow FROM ".$dbname.".pmn_feetbs WHERE 1=1 ".$where." GROUP BY notransaksi"; 
        $res = fetchdata($str);
		$jlhbrs = count($res);
        
        $jml = $p->jumlahHalaman($jlhbrs,$limit);

        $nmSup  = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
        $kdAfd  = makeOption($dbname, 'kebun_5namakud', 'kodesupplier,afdeling');
		$str = "SELECT notransaksi, unit, tipetbs, kodesupplier, tanggal, (SUM(totalrp)/SUM(kgnetto)) as rpkg,SUM(totalrp) as totalrp,
				tanggaltbs1, tanggaltbs2, sum(kgbruto) as kgbruto, sum(kgpotongan) as kgpotongan, sum(kgnetto) as kgnetto, posting, unitalokasi,persenppn,sum(rpppn) as rpppn 
				FROM ".$dbname.".pmn_feetbs
				WHERE 1=1 ".$where."
				GROUP BY notransaksi
				ORDER BY tanggal DESC,notransaksi desc
				LIMIT ".$offset.",".$limit; // echo $str;
		$res = fetchdata($str);

        $no = $offset+1;
		foreach($res as $key=>$val){
			// @$no+=1;
			$tab .= "<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td>".$val['notransaksi']."</td>
						<td>".$val['unit']."</td>
						<td>".$val['unitalokasi']."</td>
						<td>".$val['kodesupplier']." - (".$kdAfd[$val['kodesupplier']].") ".$nmSup[$val['kodesupplier']]."</td>
						<td align=center>".tglnormal($val['tanggal'])."</td>
						<td align=center>".tglnormal($val['tanggaltbs1'])." s/d ".tglnormal($val['tanggaltbs2'])."</td>
						<td>".$nmtipe[$val['tipetbs']]."</td>
						<td align=right>".number_format($val['kgbruto'])."</td>
						<td align=right>".number_format($val['kgpotongan'])."</td>
						<td align=right>".number_format($val['kgnetto'])."</td>
						<td align=right>".number_format($val['rpkg'])."</td>
						<td align=right>".number_format($val['totalrp'])."</td>
						<td align=right>".number_format($val['persenppn'])."</td>
						<td align=right>".number_format($val['rpppn'])."</td>
						";

			if($val['posting'] == 0 || $val['posting'] == 3){
				$tab .= "<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedata('".$val['notransaksi']."');\"></td>
						<td align=center width=25px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' title='Posting' onclick=\"formajukan('".$val['unit']."','".$val['notransaksi']."');\"></td>";
			} else if($val['posting'] == 1){ 
				$tab .= "<td align=center width=25px></td>";
				$tab .= "<td align=center width=25px><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30' title='Posted'></td>";
			} else if($val['posting'] == 9){ 
				$tab .= "<td align=center width=25px></td>";
				$tab .= "<td align=center width=25px><img src=images/info.png class=resicon class=zImgBtn height='30' title='Detail Posting' onclick=\"detailpost('".$val['notransaksi']."');\"></td>";
			}

			$tab .= "
						<td align=center width=25px>
							<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detail('".$val['notransaksi']."');\">
						</td>
						<td align=center width=25px>
						<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdf('".$val['notransaksi']."');\">
					</td>
					</tr>";
            $no += 1;
		}

		$tab .= "<tr class=rowheader>
          		<td colspan=16 align=center>".($offset+1)." to ".($page*$limit)." of ". $jlhbrs."<br />";
		        $buttonaction = array(
		            'first' =>  'onclick="loaddata(1);"',
		            'prev'  =>  'onclick="loaddata('.($page-1).');"',
		            'next'  =>  'onclick="loaddata('.($page+1).');"',
		            'last'  =>  'onclick="loaddata('.($jml).')"',
		            'pages' =>  'id="pages" name="pages" onchange="loaddata(this.value);"'
		        );
        $tab .= $p->navHalaman($page,$jml,$buttonaction);
        $tab .= "</td></tr>";


		$tab .= "</tbody></table>";

		echo $tab;
	break;

	case 'proses':
		if(tanggalsystemn($tgl1)>tanggalsystemn($tgl2)){
			exit("Warningsistem:Tanggal mulai lebih besar dari tanggal selesai");
		}
		$arrtanggal=rangeTanggalarr(tanggalsystemn($tgl1),tanggalsystemn($tgl2));
		$texterror='';
		foreach($arrtanggal as $tglcek){
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".pmn_feetbs where kodesupplier='".$supplier."' and unit='".$unit."' and tanggaltbs1='".$tglcek."' and jenis='".$param['jenis']."'"; 
			$res=fetchdata($str);
			if($res[0]['jumlah']>0){
				$texterror.="<br>sudah ada data ditanggal tbs ".$tgl1." dengan nomor transaksi ".$bar['notransaksi']."";
			}
			
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".pmn_feetbs where kodesupplier='".$supplier."' and unit='".$unit."' and tanggaltbs2='".$tglcek."' and jenis='".$param['jenis']."'"; 
			$res=fetchdata($str);
			if($res[0]['jumlah']>0){
				$texterror.="<br>sudah ada data ditanggal tbs ".$tgl2." dengan nomor transaksi ".$bar['notransaksi']."";
			}
		}
		
		if($texterror!=''){
			echo $texterror;
			exit("Warningsistem:Gagal Proses");
		}
		switch ($param['jenis']){
			case'bulanan':
				if($supplier==''){
					exit("Error : Supplier harus diisi.");
				}
				if($tipetbs==''){
					exit("Error : Tipe TBS harus diisi.");
				}
				
				
				$tab = "<br>
					<table border=0 cellspacing=1 class=sortable cellpadding=5>
						<thead>
							<tr class=rowheader>
								<th align=center>".$_SESSION['lang']['nourut']."</th>
								<th align=center>".$_SESSION['lang']['unit']."<br>".$_SESSION['lang']['alokasi']."</th>
								<th align=center>".$_SESSION['lang']['rekening']."</th>
								<th align=center>".$_SESSION['lang']['atasnama']."</th>
								<th align=center>".$_SESSION['lang']['noakundebet']."</th>
								<th align=center>".$_SESSION['lang']['namaakun']."</th>
								<th align=center>".$_SESSION['lang']['noakunkredit']."</th>
								<th align=center>".$_SESSION['lang']['namaakun']."</th>
								<th align=center>Batas Bawah<br>(Kg)</th>
								<th align=center>Batas Atas<br>(Kg)</th>
								<th align=center>Netto<br>(Kg)</th>
								<th align=center>".$_SESSION['lang']['rpperkg']."</th>
								<th align=center>".$_SESSION['lang']['total']."</th>
								<th align=center>".$_SESSION['lang']['persenppn']."</th>
								<th align=center>".$_SESSION['lang']['ppn']."</th>
								<th align=center>".$_SESSION['lang']['action']."<br><input type='checkbox' id=cekall onclick=cekalldata()></th>
							</tr>
						</thead>
					<tbody>";
				$str="SELECT a.supplierid, a.rekening, a.an, b.namabank from ".$dbname.".log_5rekbank a left join keu_5daftarbank b on a.idbank = b.kodebank where 1 ";
				$res = fetchdata($str); 
				foreach($res as $bar){
					$kamusrek[$bar['supplierid']][$bar['rekening']]['an']=$bar['namabank'].' - '.$bar['an'];
				}        
				
				
				// $where=" and tanggaldari in (SELECT max(tanggaldari) as tanggaldari FROM ".$dbname.".pmn_5feetbs WHERE kodeunit = '".$unit."' AND kodesupplier = '".$supplier."' AND tanggaldari <= '".tanggalsystemn($tgl1)."' AND posting = '1' and jenis='".$param['jenis']."')";
				$query = "SELECT * FROM ".$dbname.".pmn_5feetbs WHERE kodeunit = '".$unit."' AND kodesupplier = '".$supplier."' AND tanggaldari <= '".tanggalsystemn($tgl1)."' AND posting = '1' ".$where."  and jenis='".$param['jenis']."' ORDER BY tanggaldari DESC limit 1";//tanggaldari DESC, batasbawah asc
				$rquery = fetchdata($query); 
				if(count($rquery)==0){
					exit("Error : Harga untuk jenis ".$param['jenis']." belum ada.");
				}
				
				$str = "SELECT unit, supplier, SUM(kgbruto) as bruto, SUM(kgpotongan) as potongan, SUM(kgnetto) as netto FROM ".$dbname.".".$table." WHERE (unit = '".$unit."' or pemilik = '".$unit."') AND supplier = '".$supplier."' AND (substr(tanggalpks,1,10) >= '".tanggalsystemn($tgl1)."' AND substr(tanggalpks,1,10) <= '".tanggalsystemn($tgl2)."') group by supplier";
			
				$res = fetchdata($str); 
				foreach($res as $bar){
					$nomor=0;
					foreach($rquery as $val){
						$nomor++;
						if($nomor>1){
							if($bar['netto']>=$val['batasbawah']){
								$kgsisa = $bar['netto']-$val['batasbawah']-$val['batasatas'];
								if($bar['netto']>$val['batasatas']){
									$data[]=array(
												'bawah'=>$val['batasbawah'],
												'atas'=>$val['batasatas'],
												'kg'=>($val['batasatas']-$val['batasbawah'])+1,
												'rp'=>(($val['batasatas']-$val['batasbawah'])+1)*$val['rpkg'],
												'harga'=>$val['rpkg']
											);
								}else{			
									$data[]=array(
												'bawah'=>$val['batasbawah'],
												'atas'=>$val['batasatas'],
												'kg'=>$bar['netto']-$val['batasbawah'],
												'rp'=>($bar['netto']-$val['batasbawah'])*$val['rpkg'],
												'harga'=>$val['rpkg']
											);
								}
							}
						}else{
							$kgpertama=($val['batasatas']+1);
							$data[]=array(
										'bawah'=>$val['batasbawah'],
										'atas'=>$val['batasatas'],
										'kg'=>$kgpertama,
										'rp'=>($kgpertama)*$val['rpkg'],
										'harga'=>$val['rpkg']
									);
						}
						
						
						$anrekening=$kamusrek[$bar['supplier']][$val['rekening']]['an'];
						$rekening=$val['rekening'];
						$noakundebet=$val['noakundebet'];
						$noakunkredit=$val['noakunkredit'];
					}
					$unit=$bar['unit'];
					$supplier=$bar['supplier'];
				}
				
				foreach($data as $key => $val){
					$no++;
					$tab .= "<tr class=rowcontent id=row".$no.">";
					$tab .= "<td align=center>".$no."</td>";
					$tab .= "<td align=left id=alokasi".$no.">".$unit."</td>";
					$tab .= "<td align=left id=rekening".$no.">".$rekening."</td>";
					$tab .= "<td align=left id=atasnama".$no.">".$anrekening."</td>";
					$tab .= "<td align=center id=debet".$no.">".$noakundebet."</td>";
					$tab .= "<td align=left>".getNamaAkun($noakundebet)."</td>";
					$tab .= "<td align=center id=kredit".$no.">".$noakunkredit."</td>";
					$tab .= "<td align=left>".getNamaAkun($noakunkredit)."</td>";
					$tab .= "<td align=right>".number_format($val['bawah'])."</td>";
					$tab .= "<td align=right>".number_format($val['atas'])."</td>";
					$tab .= "<td align=right id=netto".$no.">".number_format($val['kg'])."</td>";
					$tab .= "<td align=right id=rpkg".$no.">".number_format($val['harga'])."</td>";
					$tab .= "<td align=right id=total".$no.">".number_format($val['rp'])."</td>";
					$tab .= "<td align=right id=persenppn".$no.">".number_format($val['persenppn'])."</td>";
					$tab .= "<td align=right id=rpppn".$no.">".hidezerodecimal(floor($val['persenppn']*$val['rp']/100))."</td>";
					$tab .= "<td align=center><input type='checkbox' id=cek_".$no."></td>";
					
					/*
						<td align=right id=persenppn".$no.">".number_format($arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['persenppn'])."</td>
														<td align=right id=rpppn".$no.">".hidezerodecimal(floor($arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['persenppn']*$totalrp/100))."</td>
					*/

					$tab .= "<td hidden id=nospb".$no."></td>";
					$tab .= "<td hidden id=blox".$no."></td>";
					$tab .= "<td hidden id=bruto".$no."></td>";
					$tab .= "<td hidden id=potongan".$no."></td>";					
					
					$tab .= "</tr>";
					
					$totalkg+=$val['kg'];
					$totalrp+=$val['rp'];
				}
				
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td align=center colspan=10>TOTAL</td>";
				$tab .= "<td align=right>".number_format($totalkg)."</td>";
				$tab .= "<td align=right></td>";
				$tab .= "<td align=right>".number_format($totalrp)."</td>";
				$tab .= "<td align=right></td>";
				$tab .= "</tr>";
				$tab .= "</tbody><tfoot>";
				
				$tab .= "<tr>";
				$tab .= "<td colspan=14><center><input type=text class=myinputtext readonly id=totalbaris hidden value='".$no."'><button class=mybutton onclick=saveall(".$no.")>".$_SESSION['lang']['save']."</button></center></td>";
				$tab .= "</tr>";
				$tab .= "</tfoot></table>";
				
				$notr = generatefeetbs();
				echo $tab."###".$notr;
			break;
			default:
				$tab = "<br>
						<table border=0 cellspacing=1 class=sortable>
							<thead>
								<tr class=rowheader>
									<th align=center>".$_SESSION['lang']['nourut']."</th>
									<th align=center>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['alokasi']."</th>
									<th align=center>".$_SESSION['lang']['rekening']."</th>
									<th align=center>".$_SESSION['lang']['atasnama']."</th>
									<th align=center>".$_SESSION['lang']['nospb']."</th>
									<th align=center>".$_SESSION['lang']['blok']."</th>
									<th align=center>No ".$_SESSION['lang']['noakundebet']."</th>
									<th align=center>No ".$_SESSION['lang']['noakunkredit']."</th>
									<th align=center>KG Brutto</th>
									<th align=center>KG ".$_SESSION['lang']['potongan']."</th>
									<th align=center>KG Netto</th>
									<th align=center>".$_SESSION['lang']['rpperkg']."</th>
									<th align=center>".$_SESSION['lang']['total']."</th>
									<th align=center>".$_SESSION['lang']['persenppn']."</th>
								<th align=center>".$_SESSION['lang']['ppn']."</th>
									<th align=center>".$_SESSION['lang']['action']."<br><input type='checkbox' id=cekall onclick=cekalldata()></th>
								</tr>
							</thead>
							<tbody>";
				
				$str = "SELECT unit, supplier, blok, SUM(kgbruto) as bruto, SUM(kgpotongan) as potongan, SUM(kgnetto) as netto,nospb
						FROM ".$dbname.".".$table."
						WHERE (unit = '".$unit."' or pemilik = '".$unit."') AND supplier = '".$supplier."'
						AND (substr(tanggalpks,1,10) >= '".tanggalsystemn($tgl1)."' AND substr(tanggalpks,1,10) <= '".tanggalsystemn($tgl2)."') group by blok,nospb";
				$res = fetchdata($str); 
				if($_SESSION['standard']['userid']=='0000000003'){
					 // echo $str;
				}
	
				$arr = array();
				foreach($res as $key=>$val){
					$blox=$val['blok'];
					$arrblox[$blox]=$blox;
					$arrunit[$unit] = $unit;
					$lsnospb=$val['nospb'];
					$arrnospb[$lsnospb] = $lsnospb;
					$arrsupp[$val['supplier']] = $val['supplier'];

					// AND batasbawah <= '".$val['netto']."' AND batasatas >= '".$val['netto']."'
					$str = "SELECT tanggaldari
							FROM ".$dbname.".pmn_5feetbs
							WHERE kodeunit = '".$unit."'
							AND kodesupplier = '".$val['supplier']."'
							AND tanggaldari <= '".tanggalsystemn($tgl1)."'
							AND posting = '1' and jenis='".$param['jenis']."'
							ORDER BY tanggaldari DESC LIMIT 1";
					// echo $str;exit('warning');
					$res = fetchdata($str);

					// AND batasbawah <= '".$val['netto']."' AND batasatas >= '".$val['netto']."'
					$query = "SELECT kodeunit, kodesupplier, rekening, rpkg, noakundebet, noakunkredit, unitalokasi, posting,persenppn
							FROM ".$dbname.".pmn_5feetbs
							WHERE kodeunit = '".$unit."'
							AND kodesupplier = '".$val['supplier']."'
							AND tanggaldari <= '".tanggalsystemn($tgl1)."'
							AND posting = '1'
							AND tanggaldari <= '".$res[0]['tanggaldari']."'
							and jenis='".$param['jenis']."'
							ORDER BY tanggaldari ASC, batasbawah ASC";
							#ORDER BY tanggaldari DESC, batasbawah ASC LIMIT 1";
						// echo $query;exit('warning');
					$result = fetchdata($query);
					foreach($result as $keys=>$value){
						$arran[$value['rekening']] = $value['rekening'];
						$arrrpkg[$value['rpkg']] = $value['rpkg'];
						$status[$value['rekening']] = $value['posting'];

						// $arr[$unit][$blox][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['nospb'] = $val['nospb'];
						
						$arr[$unit][$blox][$lsnospb][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['persenppn'] = $value['persenppn'];
						
						$arr[$unit][$blox][$lsnospb][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['bruto'] = $val['bruto'];
						$arr[$unit][$blox][$lsnospb][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['potongan'] = $val['potongan'];
						$arr[$unit][$blox][$lsnospb][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['netto'] = $val['netto'];
						// $arr[$value['kodeunit']][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['rpkg'] = $value['rpkg'];
						$arr[$unit][$blox][$lsnospb][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['unitalokasi'] = $value['unitalokasi'];
						$arr[$unit][$blox][$lsnospb][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['debet'] = $value['noakundebet'];
						$arr[$unit][$blox][$lsnospb][$value['kodesupplier']][$value['rekening']][$value['rpkg']]['kredit'] = $value['noakunkredit'];
					}
				}
				$nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');


				$str = "select a.*, b.namabank from ".$dbname.".log_5rekbank a left join ".$dbname.".keu_5daftarbank b on a.idbank = b.kodebank where a.supplierid='".$supplier."' order by a.def desc";
				// kamus rekbank
				$str="SELECT a.supplierid, a.rekening, a.an, b.namabank from ".$dbname.".log_5rekbank a left join keu_5daftarbank b on a.idbank = b.kodebank where 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$kamusrek[$bar['supplierid']][$bar['rekening']]['an']=$bar['namabank'].' - '.$bar['an'];
				}
				$no=0;
				foreach($arrunit as $listunit){
					foreach($arrblox as $blox){
						foreach($arrnospb as $dtnospb){
							foreach($arrsupp as $listsupp){
								foreach($arran as $listan) {
									foreach($arrrpkg as $listrpkg) {
											$totalrp = $arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['netto']*$listrpkg;
											if($totalrp==0)continue;
											$no+=1;

											$tab .= "<tr class=rowcontent id=row".$no.">
														<td align=center>".$no."</td>
														<td id=alokasi".$no.">".$arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['unitalokasi']."</td>
														<td align=right id=rekening".$no.">".$listan."</td>
														<td id=atasnama".$no.">".$kamusrek[$listsupp][$listan]['an']."</td>
														<td id=nospb".$no.">".$dtnospb."</td>
														<td id=blox".$no.">".$blox."</td>
														<td align=left id=debet".$no.">".$arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['debet']."</td>
														<td align=left id=kredit".$no.">".$arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['kredit']."</td>
														<td align=right id=bruto".$no.">".number_format($arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['bruto'])."</td>
														<td align=right id=potongan".$no.">".number_format($arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['potongan'])."</td>
														<td align=right id=netto".$no.">".number_format($arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['netto'])."</td>
														<td align=right id=rpkg".$no.">".number_format($listrpkg)."</td>
														<td align=right id=total".$no.">".number_format($totalrp)."</td>
														
														<td align=right id=persenppn".$no.">".number_format($arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['persenppn'])."</td>
														<td align=right id=rpppn".$no.">".hidezerodecimal(floor($arr[$listunit][$blox][$dtnospb][$listsupp][$listan][$listrpkg]['persenppn']*$totalrp/100))."</td>
														
														
														<td align=center><input type='checkbox' id=cek_".$no."></td>
													</tr>";

											if($status[$listan] != 1){
												exit('Warning : Data belum disetujui, belum bisa memproses data !');
											}
										
									}
								}
							}
						}
					}
				}
				$tab .= "</tbody></table>";
				$tab .= "<br><center><input type=text class=myinputtext readonly id=totalbaris hidden value='".$no."'><button class=mybutton onclick=saveall(".$no.")>".$_SESSION['lang']['save']."</button></center>";

				$notr = generatefeetbs();

				echo $tab."###".$notr;
			break;
		}
	break;

	case 'savedata':
		$bruto   = str_replace(',','',$bruto);
		$potongan= str_replace(',','',$potongan);
		$netto   = str_replace(',','',$netto);
		$rpkg    = str_replace(',','',$rpkg);
		$total   = str_replace(',','',$total);
		$persenppn   = str_replace(',','',$persenppn);
		$rpppn   = str_replace(',','',$rpppn);

        $str = "INSERT INTO ".$dbname.".pmn_feetbs (`notransaksi`,`unit`,`unitalokasi`,`kodesupplier`,`tanggal`,`tanggaltbs1`,`tanggaltbs2`,`rekening`,`kgbruto`,`kgpotongan`,`kgnetto`,`rpkg`,`totalrp`,`tipetbs`,`noakundebet`,`noakunkredit`,`nospb`,`jenis`,`createby`, `createtime`, `updateby`,`persenppn`,`rpppn`) VALUES
		('".$notrans."','".$unit."','".$alokasi."','".$supplier."','".tanggalsystemn($tgl)."','".tanggalsystemn($tgl1)."','".tanggalsystemn($tgl2)."','".$rekening."','".$bruto."','".$potongan."','".$netto."','".$rpkg."','".$total."','".$tipetbs."','".$debet."','".$kredit."','".$nospb."','".$param['jenis']."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."','".$_SESSION['standard']['userid']."','".$persenppn."','".$rpppn."')";
		
		if($param['jenis']=='bulanan'){			
			// exit("error".$str);
			if($total>0){				
				try {$owlPDO->exec($str); } catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}else{
			try {$owlPDO->exec($str); } catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;

	case 'detail':
		$nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
		$nmRek = makeOption($dbname, 'log_5rekbank', 'rekening,an');
		$nmAkun= makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
		$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$str = "SELECT * FROM ".$dbname.".pmn_feetbs WHERE notransaksi = '".$notrans."' ORDER BY rekening ASC";
		$res = fetchdata($str);

		$arr = array();
		$row = '';
		//$row .= "<link rel=stylesheet type=text/css href=style/generic.css>";
		foreach($res as $key=>$val){
			@$no+=1;
			$notrans = $val['notransaksi'];
			$unit = $val['unit'];
			$tipetbs = $val['tipetbs'];
			$supplier = $nmSup[$val['kodesupplier']];
			$tgl = $val['tanggal'];
			$tgl1 = $val['tanggaltbs1'];
			$tgl2 = $val['tanggaltbs2'];

			$row .= "<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td>".$val['unitalokasi']."</td>
						<td align=left>".$val['rekening']." - ".$nmRek[$val['rekening']]."</td>
						<td align=left>".$val['noakundebet']." - ".$nmAkun[$val['noakundebet']]."</td>
						<td align=left>".$val['noakunkredit']." - ".$nmAkun[$val['noakunkredit']]."</td>
						<td>".$val['nospb']."</td>
						<td align=right>".number_format($val['kgbruto'])."</td>
						<td align=right>".number_format($val['kgpotongan'])."</td>
						<td align=right>".number_format($val['kgnetto'])."</td>
						<td align=right>".number_format($val['rpkg'])."</td>
						<td align=right>".number_format($val['totalrp'])."</td>
						<td align=right>".number_format($val['persenppn'])."</td>
						<td align=right>".number_format($val['rpppn'])."</td>
					</tr>";
			$totalbruto+=$val['kgbruto'];
			$totalpot+=$val['kgpotongan'];
			$totalnet+=$val['kgnetto'];
			$totalrp+=$val['totalrp'];
			@$rpppn+=$val['rpppn'];
		}
		
		$row .= "<tr class=rowcontent>
			<td align=center colspan=6>TOTAL</td>
			<td align=right>".number_format($totalbruto)."</td>
			<td align=right>".number_format($totalpot)."</td>
			<td align=right>".number_format($totalnet)."</td>
			<td align=right></td>
			<td align=right>".number_format($totalrp)."</td>
			<td align=right></td>
			<td align=right>".number_format($rpppn)."</td>
		</tr>";

		$countApp = getCountApproval('FTBS',$unit);
		if ($countApp == 0) {
			$html = "<tr class=rowcontent><td colspan=3 style=color:red>Belum ada persetujuan untuk Unit ".$unit." dan Jenis Persetujuan FTBS</td></tr>";
		} else {
			$str = "SELECT karyawanid, level, status FROM ".$dbname.".approval WHERE notransaksi='".$notrans."' ORDER BY level ASC";
			$res = fetchdata($str);

			if (count($res) > 0) {
				foreach($res as $val){
					@$num+=1;

					if ($val['status'] == 0) {
						$status = 'Menunggu Persetujuan';
					} else if ($val['status'] == 1) {
						$status = 'Disetujui';
					} else {
						$status = 'Ditolak';
					}

					$html.= "<tr class=rowcontent>
								<td align=center>".$num."</td>
								<td>".$nmKar[$val['karyawanid']]."</td>
								<td>".$status."</td>
							</tr>";
				}
			} else {
				$html = "<tr class=rowcontent align=center><td colspan=3>Data belum di posting</td></tr>";
			}
		}

		$tab = "<table style='width:100%;'>
					<tr>
						<td>
							<table style=width:100%>
								<tr>
									<td style=width:50% valign=top>
										<table>
											<tr>
												<td>".$_SESSION['lang']['notransaksi']."</td>
												<td>:</td>
												<td>".$notrans."</td>
											</tr>
											<tr>
												<td>".$_SESSION['lang']['unit']."</td>
												<td>:</td>
												<td>".$unit."</td>
											</tr>
											<tr>
												<td>".$_SESSION['lang']['tipe']." TBS</td>
												<td>:</td>
												<td>".$tipetbs." - ".$nmtipe[$tipetbs]."</td>
											</tr>
											<tr>
												<td>Supplier</td>
												<td>:</td>
												<td>".$supplier."</td>
											</tr>
											<tr>
												<td>".$_SESSION['lang']['tanggal']."</td>
												<td>:</td>
												<td>".tglnormal($tgl)."</td>
											</tr>
											<tr>
												<td>".$_SESSION['lang']['tanggal']." TBS</td>
												<td>:</td>
												<td>".tglnormal($tgl1)." s/d ".tglnormal($tgl2)."</td>
											</tr>
										</table>
									</td>
									<td style=width:50%  valign=top>
										<table class=sortable cellspacing=1 cellpadding=5 border=0>
											<thead>
												<tr class=rowheader>
													<th align=center>".$_SESSION['lang']['nourut']."</td>
													<th align=center>".$_SESSION['lang']['persetujuan']."</td>
													<th align=center>".$_SESSION['lang']['status']."</td>
												</tr>
											</thead>
											<tbody>
												".$html."
											</tbody>
										</table>
									</td>
								</tr>
							</table>
							
						</td>
					</tr>

					<tr>
						<td valign=top>
							<table border=0 cellspacing=1 style='width:100%;' cellpadding=5 class=sortable>
								<thead>
									<tr class=rowheader>
										<th align=center>".$_SESSION['lang']['nourut']."</th>
										<th align=center>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['alokasi']."</th>
										<th align=center>".$_SESSION['lang']['rekening']."</th>
										<th align=center>No ".$_SESSION['lang']['noakundebet']."</th>
										<th align=center>No ".$_SESSION['lang']['noakunkredit']."</th>
										<th align=center>".$_SESSION['lang']['nospb']."</th>
										<th align=center>KG Brutto</th>
										<th align=center>KG ".$_SESSION['lang']['potongan']."</th>
										<th align=center>KG Netto</th>
										<th align=center>".$_SESSION['lang']['rpperkg']."</th>
										<th align=center>".$_SESSION['lang']['total']."</th>
										<th align=center>".$_SESSION['lang']['persenppn']."</th>
										<th align=center>".$_SESSION['lang']['ppn']."</th>
									</tr>
								</thead>
								<tbody>
									".$row."
								</tbody>
							</table>
						</td>
					</tr>
				</table>";

		echo $tab;
	break;

	case 'hapus':
		$str = "DELETE FROM ".$dbname.".pmn_feetbs WHERE notransaksi = '".$notrans."' ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;

	case 'posting-old':
	
		try {
			$owlPDO->beginTransaction();
			
			/*
			D:biaya transpor sales
			D:hutang transpor (claim)
			K:hutang transpor
			K:Gain loss In transit CPO / PK
			*/
			
			#=
			$str = "SELECT SUM(totalrp) as totalrp, unit, unitalokasi, notransaksi, kodesupplier, tanggal, tipetbs, noakundebet, noakunkredit FROM ".$dbname.".pmn_feetbs WHERE notransaksi='".$notrans."'"; 
			$res = fetchdata($str);
			foreach($res as $bar){
				$totalrp = $bar['totalrp'];
				$kodeunit = $bar['unit'];
				$alokasi = $bar['unitalokasi'];
				$notransaksi = $bar['notransaksi'];
				$tanggal = $bar['tanggal'];
				$periode = substr($bar['tanggal'],0,7);
				$tipetbs = $bar['tipetbs'];
				$supplier = $bar['kodesupplier'];
				$noakundebet = $bar['noakundebet'];
				$noakunkredit = $bar['noakunkredit'];
			}

			// $query = "SELECT a.induk, a.kodeorganisasi, 
			// 		 (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE induk = a.induk AND tipe = 'KANWIL') as unit
			// 		 FROM ".$dbname.".organisasi a
			// 		 WHERE kodeorganisasi = '".$kodeunit."'";
			// $result = fetchData($query);
			// foreach($result as $val){
			// 	$unit = $val['unit'];
			// }
			
			#= coa transportir
			// $str = "SELECT noakundebet, noakunkredit FROM ".$dbname.".keu_5parameterjurnal WHERE kodeaplikasi = 'PKS' AND jurnalid = 'INVTF'"; 
			// $res = fetchdata($str);
			// foreach($res as $bar){
			// 	$noakunkredit = $bar['noakunkredit'];
			// 	$noakundebet = $bar['noakundebet'];
			// }

			if($noakunkredit == ''){
				exit("Warning: Noakun kredit masih kosong, silahkan daftarkan di master parameter jurnal");
			}
			if($noakundebet == ''){
				exit("Warning: Noakun debet masih kosong, silahkan daftarkan di master parameter jurnal");
			}
		
			$kodejurnal = 'INVTF';
			$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodekelompok='".$kodejurnal."' and kodeunit='".$alokasi."' and periode='".$periode."'");
			$tmpKonter = fetchData($query);
			$konter = addZero($tmpKonter[0]['nokounter']+1,3);
			# Prep No Jurnal
			$nojurnal = str_replace('-','',$tanggal)."/".$alokasi."/".$kodejurnal."/".$konter;
			
			$dataRes['header'][] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnal,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>'0',
				'totalkredit'=>'0',
				'amountkoreksi'=>'0',
				'noreferensi'=>$notrans,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);
			$noUrut=1;
			
			#= debet
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>$noUrut,
				'noakun'=>$noakundebet,
				'keterangan'=>'JURNAL BIAYA ADMINISTRASI TBS : '.$notrans,
				'jumlah'=>$totalrp,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$alokasi,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'40000003',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$supplier,
				'noreferensi'=>$notrans,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => '0000000001'
			);
			$noUrut++;
			
			#= kredit
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>$noUrut,
				'noakun'=>$noakunkredit,
				'keterangan'=>'JURNAL BIAYA ADMINISTRASI TBS : '.$notrans,
				'jumlah'=>$totalrp*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$alokasi,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'40000003',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$supplier,
				'noreferensi'=>$notrans,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => '0000000001'
			);
			$noUrut++;
			
			
			#= kredit
			
			#= update counter jurnal
			$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeunit='".$unit."' and kodekelompok='".$kodejurnal."' and periode='".$periode."' ";	
			$owlPDO->exec($str);
			
			$str = "update ".$dbname.".pmn_feetbs set posting=1, postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notrans."'";
			$owlPDO->exec($str);
			
			#= jurnalht
			$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			$owlPDO->exec($queryH);
			
			#= jurnaldt
			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataRes['detail']);
			$owlPDO->exec($queryD);
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
			
			$owlPDO->rollback();
			echo "Warning Posting Gagal \n" . addslashes($e->getMessage());

		}
	
	break;

    case 'formajukan':
        $countApp = getCountApproval('FTBS',$unit);

        $tab = "
                    <table>
                        <tr>
                            <td>".$_SESSION['lang']['tanggal']."</td>
                            <td>:</td>
                            <td><input type=text class=myinputtext id=tanggalpengajuan readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:200px;/></td>
                        </tr>";

        for($i=1; $i<=$countApp; $i++){
            $arrList = listApprove($i,'FTBS',$unit);
            $optpersetujuan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

            foreach($arrList as $key=>$val){
                $optpersetujuan .= "<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
            }

            $tab .= "<tr>
                        <td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
                        <td>:</td>
                        <td><select style=\"width:205px;\" id=persetujuan".$i.">".$optpersetujuan."</select></td>
                    </tr>";  
        }   

        if ($countApp > 0) {
        	$html = "<button class=mybutton onclick=posting('".$notrans."','".$countApp."')>".$_SESSION['lang']['save']."</button>"; 
        } else {
        	$html = "<span style=color:red>Belum ada persetujuan untuk Unit ".$unit." dan Jenis Persetujuan FTBS</span>";
        }

        $tab .= "       <tr>
                            <td colspan=2></td>
                            <td>".$html."</td>
                        </tr>
                    </table>
                </fieldset>";

        echo $tab;
    break;

    case 'posting':
    
        try {
            $owlPDO->beginTransaction();

			#=
			$str = "SELECT SUM(totalrp) as totalrp, unit, unitalokasi, notransaksi, kodesupplier, tanggal, tipetbs, noakundebet, noakunkredit FROM ".$dbname.".pmn_feetbs WHERE notransaksi='".$notrans."'"; 
			$res = fetchdata($str);
			foreach($res as $bar){
				$totalrp = $bar['totalrp'];
				$kodeunit = $bar['unit'];
				$alokasi = $bar['unitalokasi'];
				$notransaksi = $bar['notransaksi'];
				$tanggal = $bar['tanggal'];
				$periode = substr($bar['tanggal'],0,7);
				$tipetbs = $bar['tipetbs'];
				$supplier = $bar['kodesupplier'];
				$noakundebet = $bar['noakundebet'];
				$noakunkredit = $bar['noakunkredit'];
			}

			if($noakunkredit == ''){
				exit("Warning: Noakun kredit masih kosong, silahkan daftarkan di master parameter jurnal");
			}

			if($noakundebet == ''){
				exit("Warning: Noakun debet masih kosong, silahkan daftarkan di master parameter jurnal");
			}
            
            if($tanggalpengajuan == ''){
                exit("Warning: Tanggal pengajuan masih kosong");
            }
            
            for($i=1; $i<=$param['maxaproval']; $i++){
                if($param['persetujuan'][$i]=='') {
                    exit("Warning: Persetujuan ".$i." belum dipilih.");
                }
            }

            #= delete 1st untuk aprovalnya
            $str = "DELETE FROM ".$dbname.".approval WHERE notransaksi = '".$notrans."' AND jenispersetujuan = 'FTBS'";
            $owlPDO->exec($str);
            
            $str = "UPDATE ".$dbname.".pmn_feetbs set posting = '9', postingtime = '".$tanggalpengajuan."'
                    WHERE notransaksi = '".$notrans."'";
            $owlPDO->exec($str);

            for($i=1;$i<=$param['maxaproval'];$i++){
                #= insert
                $str = "INSERT INTO ".$dbname.".approval 
                       (notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
                       VALUES
                       ('".$notrans."','FTBS','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";   
                $owlPDO->exec($str);
            }
            
            $owlPDO->commit();
            
        } catch(PDOException $e) {
        
        $owlPDO->rollback();
            echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());

        }
    break;

    case 'detailpost':

        $str = "SELECT a.notransaksi, a.unit, a.postingtime, b.karyawanid, b.status, b.level
                FROM ".$dbname.".pmn_feetbs a JOIN approval b ON a.notransaksi = b.notransaksi
                WHERE a.notransaksi = '".$notrans."' ORDER BY b.level ASC";
        $res = fetchdata($str);
        foreach($res as $val){
            $tanggalpengajuan = substr($val['postingtime'],0,10);
            $countApp = getCountApproval('FTBS',$val['unit']);

            @$approver[$val['level']] = $val['karyawanid'];
            @$status[$val['level']] = $val['status'];
        }
        $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

        $tab = "
                    <table>
                        <tr>
                            <td>".$_SESSION['lang']['tanggal']."</td>
                            <td>:</td>
                            <td colspan=2><input type=text class=myinputtext id=tanggalpengajuan readonly value='".tanggalnormal($tanggalpengajuan)."' style=width:150px;/></td>
                        </tr>";

        for($i=1; $i<=$countApp; $i++){
            if($status[$i] == 0){
                $keterangan = 'Menunggu Persetujuan';
            } else if($status[$i] == 1){
                $keterangan = 'Disetujui';
            } else if($status[$i] == 2){
                $keterangan = 'Ditolak';
            } else if($status[$i] == 3){
                $keterangan = 'Dikoreksi';
            }

            $tab .= "<tr>
                        <td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
                        <td>:</td>
                        <td><input type=text class=myinputtext readonly value='".$nmKar[$approver[$i]]."' style=width:150px></td>
                        <td>*".$keterangan."</td>
                    </tr>";  
        }   

        $tab .= "   </table>
                ";

        echo $tab;
    break;

    case 'pdf':

    	$tab = "<table border=1 cellpadding=1 cellspacing=0 style='width:100%;font-size:10px'>
    				<thead>
    					<tr class=rowheader>
    						<th align=center>".$_SESSION['lang']['nourut']."</th>
    						<th align=center>Koperasi</th>
    						<th align=center>".$_SESSION['lang']['desa']."</th>
    						<th align=center>Tonase Per Bln</th>
    						<th align=center>".$_SESSION['lang']['harga']."</th>
    						<th align=center>Total Bayar</th>
    						<th align=center>Tonase</th>
    						<th align=center>".$_SESSION['lang']['harga']."</th>
    						<th align=center>Total Bayar</th>
    						<th align=center>Total Pembayaran <br> Di Terima KUD</th>
    						<th align=center>Total Terima KUD</th>";
    						//<th hidden align=center>No. Rekening<br>Atas Nama</th>
    				$tab.="</tr>
    				</thead>
    				<tbody>";

    	$str = "SELECT * FROM ".$dbname.".pmn_feetbs WHERE notransaksi = '".$notrans."'";
    	$res = fetchdata($str);

    	$nmSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
    	$addSupp = makeOption($dbname, 'log_5supalamat', 'supplierid,kota');
    	$nmRek = makeOption($dbname, 'log_5rekbank', 'rekening,an');
    	foreach($res as $val){
			@$no+=1;
			$tab .= "<tr>
				<td align=center>".$no."</td>
				<td align=left>".$nmSupp[$val['kodesupplier']]."</td>
				<td align=left>".$addSupp[$val['kodesupplier']]."</td>
				<td align=right>".number_format($val['kgnetto'])."</td>
				<td align=right>".number_format($val['rpkg'])."</td>
				<td align=right>".number_format($val['totalrp'])."</td>
				<td align=right></td>
				<td align=right></td>
				<td align=right></td>
				<td align=right>".number_format($val['totalrp'])."</td>
				<td align=right>".number_format($val['totalrp'])."</td>";
				//<td hidden align=center>".$val['rekening']."<br> a/n ".$nmRek[$val['rekening']]."</td>
			$tab.="</tr>";
			@$tkgnetto+=$val['kgnetto'];
			@$ttotalrp+=$val['totalrp'];
    	}
		$tab .= "<tr>
				<td align=center colspan=3>".$_SESSION['lang']['total']."</td>
				<td align=right>".number_format($tkgnetto)."</td>
				<td align=right></td>
				<td align=right>".number_format($ttotalrp)."</td>
				<td align=right colspan=3></td>
				<td align=right>".number_format($ttotalrp)."</td>
				<td align=right>".number_format($ttotalrp)."</td>";
				//<td hidden align=center></td>
			$tab.="</tr>";
    	$tab .= "	</tbdoy>
    			</table>";

    	$tab .= "<br><table style=width:100%>
    				<tr>
    					<td align=center>Di Buat Oleh,</td>
    					<td align=center>Di Periksa Oleh,</td>
    					<td align=center>Di Ketahui Oleh,</td>
    				</tr>
    			</table>";

    	$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		// $dompdf->stream("LAPORAN_FEETBS", array("Attachment" => false));
		
		if($urlefil=='0'){
			// $dompdf->stream("Print RFQ", array("Attachment" => false));
			$dompdf->stream("LAPORAN_FEETBS", array("Attachment" => false));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
		
    break;
}

function generatefeetbs (){
    global $dbname;
    global $owlPDO;
    global $unit;
    global $tgl;
	
	$tahun=explode('-',tanggalsystemn($tgl));
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".pmn_feetbs where unit='".$unit."' and tanggal like '".$tahun."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		if($bar['nomor']==''){
			$nourut=1;
		}else{
			$explnotran=explode('/',$bar['nomor']);
			$nourut=$explnotran[0]+1;

		}
		
	$noba=addZero($nourut,4)."/ADMTBS/".$unit."/".romawi($bulan)."/".$tahun;
	return $noba;
}

?>