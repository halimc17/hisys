<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kdPt = checkPostGet('kdPt','');
$kdUnit = checkPostGet('kdUnit','');
$tipe = checkPostGet('tipe','');
$belanja = checkPostGet('belanja','');
$tahun = checkPostGet('tahun','');
$print = checkPostGet('print','');

$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun');

switch($proses){
	case 'getUnit':
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdPt."' and tipe='KEBUN'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		$optKdUnit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while($res=$qry->fetch())
		{
			$optKdUnit.="<option value=".$res['kodeorganisasi'].">".$res['namaorganisasi']."</option>";
		}
		
		echo $optKdUnit;
		break;
		
	case 'preview':
		if($kdPt==''){
			exit("Warning : PT harus dipilih.");
		}
		if($kdUnit==''){
			exit("Warning : Kode Unit harus dipilih.");
		}
		
		$arrJnsBelanja = array("0"=>"Pegawai","1"=>"Barang","2"=>"Modal");
		
		//Get Tahun
		$sthn = selectQuery($dbname, 'keu_jurnaldt_vw', "distinct(substr(tanggal,1,4)) as tahun", "kodeorg = '".$kdUnit."' group by substr(tanggal,1,4)");
		$rthn = fetchData($sthn);
		$arrthn = array();
		$not = -1;
		foreach ($rthn as $row) {
            $not++;
			$arrthn[$not] = $row['tahun'];
        }
		
		//Get Sum Belanja Pegawai (Realisasi)
		$sPgw = selectQuery($dbname, 'keu_jurnaldt_vw', "sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg = '".$kdUnit."' and left(noakun,1) in ('6','7','8','9') and nojurnal not like '%INV%' and nojurnal not like '%VHC%' and nojurnal not like '%SPK%' group by substr(tanggal,1,4)");
		$rPgw = fetchData($sPgw);
		$arrReal = array();
		foreach ($rPgw as $row) {
            $arrReal[0][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Pegawai (Anggaran)
		$sPgw2 = selectQuery($dbname, 'bgt_budget_detail', "sum(rupiah) as jumlah, tahunbudget as tahun", "kodeorg like '".$kdUnit."%' and (kodebudget like 'SDM%' or kodebudget in ('SUPERVISI','EXPL-UPAH','EXPL-LEMBUR','UMUM')) and left(noakun,1) in ('6') group by tahunbudget");
		$rPgw2 = fetchData($sPgw2);
		$arrAgr = array();
		foreach ($rPgw2 as $row) {
            $arrAgr[0][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Barang (Realisasi)
		$sBrg = selectQuery($dbname, 'keu_jurnaldt_vw', "sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg = '".$kdUnit."' and left(noakun,1) in ('6','7','8','9') and (nojurnal like '%INV%' or nojurnal like '%VHC%' or nojurnal like '%SPK%') group by substr(tanggal,1,4)");
		$rBrg = fetchData($sBrg);
		foreach ($rBrg as $row) {
            $arrReal[1][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Barang (Anggaran)
		$sBrg2 = selectQuery($dbname, 'bgt_budget_detail', "sum(rupiah) as jumlah, tahunbudget as tahun", "kodeorg like '".$kdUnit."%' and (kodebudget like 'M%' or kodebudget in ('TOOL','VHC','KONTRAK'  )) and left(noakun,1) in ('6','7','8','9') group by tahunbudget");
		$rBrg2 = fetchData($sBrg2);
		foreach ($rBrg2 as $row) {
            $arrAgr[1][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Modal (Realisasi)
		$sMdl = selectQuery($dbname, 'keu_jurnaldt_vw', "sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg = '".$kdUnit."' and left(noakun,3) in ('115','126','127','128') group by substr(tanggal,1,4)");
		$rMdl = fetchData($sMdl);
		foreach ($rMdl as $row) {
            $arrReal[2][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum total harga (Anggaran)
		$sMdl21 = selectQuery($dbname, 'bgt_kapital', "sum(hargatotal) as jumlah, tahunbudget as tahun", "kodeunit = '".$kdUnit."' group by tahunbudget");
		$rMdl21 = fetchData($sMdl21);
		$arrMdl21Agr = array();
		foreach ($rMdl21 as $row) {
            $arrMdl21Agr[$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum budget detail (Anggaran)
		$sMdl22 = selectQuery($dbname, 'bgt_budget_detail', "sum(rupiah) as jumlah, tahunbudget as tahun", "kodeorg like '".$kdUnit."%' and left(noakun,3) in ('126','127','128') and left(noakun,3) != '115' group by tahunbudget");
		$rMdl22 = fetchData($sMdl22);
		$arrMdl22Agr = array();
		foreach ($rMdl22 as $row) {
            $arrMdl22Agr[$row['tahun']] = $row['jumlah'];
        }
		
		foreach($arrthn as $val){
			@$arrAgr[2][$val] = $arrMdl21Agr[$val] + $arrMdl22Agr[$val];
		}
				
		$tab='';
		if(count($arrthn) <= 0){
			$tab.= $_SESSION['lang']['datanotfound'];
			exit();
		}
		$tab.="<div style='max-width:100%;overflow:auto;padding:10px;'>
			<table class=sortable cellspacing=1 border=0>
			<thead>
			<tr>
				<td align=center rowspan=2>Jenis Belanja</td>";
				foreach($arrthn as $val){
					$tab.="<td colspan=3 align=center>".$val."</td>";
				}
			$tab.="</tr>
			<tr>";
				foreach($arrthn as $val){
					$tab.="<td>Realisasi</td>
						<td>Anggaran</td>
						<td>Variant</td>";
				}
			$tab.="</tr>
			</thead>
			<tbody>";
				$totalReal=array();
				$totalAgr=array();
				foreach($arrJnsBelanja as $key=>$val){
					$tab.="<tr class=rowcontent>
						<td><b>".$val."</b></td>";
						foreach($arrthn as $val){
							$tab.="<td style='text-align:right;cursor:pointer' onclick=\"detail('realisasi','".$key."','".$val."','".$kdPt."','".$kdUnit."','<div id=formDetail></div>',event)\" title='click detail'>".number_format(@$arrReal[$key][$val],0)."</td>
								<td style='text-align:right;cursor:pointer' onclick=\"detail('anggaran','".$key."','".$val."','".$kdPt."','".$kdUnit."','<div id=formDetail></div>',event)\" title='click detail'>".number_format(@$arrAgr[$key][$val],0)."</td>
								<td style='text-align:right'>".number_format((@$arrAgr[$key][$val]-@$arrReal[$key][$val]),0)."</td>";
							@$totalReal[$val] += $arrReal[$key][$val];
							@$totalAgr[$val] += $arrAgr[$key][$val];
						}						
					$tab.="</tr>";
				}
			$tab.="<tr>
					<td style='text-align:center;font-weight:bold'>TOTAL</td>";
					foreach($arrthn as $val){
						$tab.="<td style='text-align:right;font-weight:bold'>".number_format($totalReal[$val],0)."</td>";
						$tab.="<td style='text-align:right;font-weight:bold'>".number_format($totalAgr[$val],0)."</td>";
						$tab.="<td style='text-align:right;font-weight:bold'>".number_format(($totalAgr[$val]-$totalReal[$val]),0)."</td>";
					}
			$tab.="</tr>";
		$tab.="</tbody>
		</table></div>";
		
		
		echo $tab;
		
		break;
		
	case 'excel':
		if($kdPt==''){
			exit("Warning : PT harus dipilih.");
		}
		if($kdUnit==''){
			exit("Warning : Kode Unit harus dipilih.");
		}
		
		$arrJnsBelanja = array("0"=>"Pegawai","1"=>"Barang","2"=>"Modal");
		
		//Get Tahun
		$sthn = selectQuery($dbname, 'keu_jurnaldt_vw', "distinct(substr(tanggal,1,4)) as tahun", "kodeorg = '".$kdUnit."' group by substr(tanggal,1,4)");
		$rthn = fetchData($sthn);
		$arrthn = array();
		$not = -1;
		foreach ($rthn as $row) {
            $not++;
			$arrthn[$not] = $row['tahun'];
        }
		
		//Get Sum Belanja Pegawai (Realisasi)
		$sPgw = selectQuery($dbname, 'keu_jurnaldt_vw', "sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg = '".$kdUnit."' and left(noakun,1) in ('6','7','8','9') and nojurnal not like '%INV%' and nojurnal not like '%VHC%' and nojurnal not like '%SPK%' group by substr(tanggal,1,4)");
		$rPgw = fetchData($sPgw);
		$arrReal = array();
		foreach ($rPgw as $row) {
            $arrReal[0][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Pegawai (Anggaran)
		$sPgw2 = selectQuery($dbname, 'bgt_budget_detail', "sum(rupiah) as jumlah, tahunbudget as tahun", "kodeorg like '".$kdUnit."%' and (kodebudget like 'SDM%' or kodebudget in ('SUPERVISI','EXPL-UPAH','EXPL-LEMBUR','UMUM')) and left(noakun,1) in ('6') group by tahunbudget");
		$rPgw2 = fetchData($sPgw2);
		$arrAgr = array();
		foreach ($rPgw2 as $row) {
            $arrAgr[0][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Barang (Realisasi)
		$sBrg = selectQuery($dbname, 'keu_jurnaldt_vw', "sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg = '".$kdUnit."' and left(noakun,1) in ('6','7','8','9') and (nojurnal like '%INV%' or nojurnal like '%VHC%' or nojurnal like '%SPK%') group by substr(tanggal,1,4)");
		$rBrg = fetchData($sBrg);
		foreach ($rBrg as $row) {
            $arrReal[1][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Barang (Anggaran)
		$sBrg2 = selectQuery($dbname, 'bgt_budget_detail', "sum(rupiah) as jumlah, tahunbudget as tahun", "kodeorg like '".$kdUnit."%' and (kodebudget like 'M%' or kodebudget in ('TOOL','VHC','KONTRAK'  )) and left(noakun,1) in ('6','7','8','9') group by tahunbudget");
		$rBrg2 = fetchData($sBrg2);
		foreach ($rBrg2 as $row) {
            $arrAgr[1][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum Belanja Modal (Realisasi)
		$sMdl = selectQuery($dbname, 'keu_jurnaldt_vw', "sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg = '".$kdUnit."' and left(noakun,3) in ('115','126','127','128') group by substr(tanggal,1,4)");
		$rMdl = fetchData($sMdl);
		foreach ($rMdl as $row) {
            $arrReal[2][$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum total harga (Anggaran)
		$sMdl21 = selectQuery($dbname, 'bgt_kapital', "sum(hargatotal) as jumlah, tahunbudget as tahun", "kodeunit = '".$kdUnit."' group by tahunbudget");
		$rMdl21 = fetchData($sMdl21);
		$arrMdl21Agr = array();
		foreach ($rMdl21 as $row) {
            $arrMdl21Agr[$row['tahun']] = $row['jumlah'];
        }
		
		//Get Sum budget detail (Anggaran)
		$sMdl22 = selectQuery($dbname, 'bgt_budget_detail', "sum(rupiah) as jumlah, tahunbudget as tahun", "kodeorg like '".$kdUnit."%' and left(noakun,3) in ('126','127','128') and left(noakun,3) != '115' group by tahunbudget");
		$rMdl22 = fetchData($sMdl22);
		$arrMdl22Agr = array();
		foreach ($rMdl22 as $row) {
            $arrMdl22Agr[$row['tahun']] = $row['jumlah'];
        }
		
		foreach($arrthn as $val){
			@$arrAgr[2][$val] = $arrMdl21Agr[$val] + $arrMdl22Agr[$val];
		}
				
		$tab='';
		$tab.="<table>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td>".$optOrg[$kdPt]."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>".$optOrg[$kdUnit]."</td>
			</tr>
		</table>";
		$tab.="<table class=sortable cellspacing=1 border=1>
			<thead>
			<tr>
				<td align=center rowspan=2>Jenis Belanja</td>";
				foreach($arrthn as $val){
					$tab.="<td colspan=3 align=center>".$val."</td>";
				}
			$tab.="</tr>
			<tr>";
				foreach($arrthn as $val){
					$tab.="<td>Realisasi</td>
						<td>Anggaran</td>
						<td>Variant</td>";
				}
			$tab.="</tr>
			</thead>
			<tbody>";
				$totalReal=array();
				$totalAgr=array();
				foreach($arrJnsBelanja as $key=>$val){
					$tab.="<tr class=rowcontent>
						<td><b>".$val."</b></td>";
						foreach($arrthn as $val){
							$tab.="<td style='text-align:right;cursor:pointer' onclick=\"detail('realisasi','".$key."','".$val."','".$kdPt."','".$kdUnit."','<div id=formDetail></div>',event)\" title='click detail'>".number_format(@$arrReal[$key][$val],0)."</td>
								<td style='text-align:right;cursor:pointer' onclick=\"detail('anggaran','".$key."','".$val."','".$kdPt."','".$kdUnit."','<div id=formDetail></div>',event)\" title='click detail'>".number_format(@$arrAgr[$key][$val],0)."</td>
								<td style='text-align:right'>".number_format((@$arrAgr[$key][$val]-@$arrReal[$key][$val]),0)."</td>";
							@$totalReal[$val] += $arrReal[$key][$val];
							@$totalAgr[$val] += $arrAgr[$key][$val];
						}						
					$tab.="</tr>";
				}
			$tab.="<tr>
					<td style='text-align:center;font-weight:bold'>TOTAL</td>";
					foreach($arrthn as $val){
						$tab.="<td style='text-align:right;font-weight:bold'>".number_format($totalReal[$val],0)."</td>";
						$tab.="<td style='text-align:right;font-weight:bold'>".number_format($totalAgr[$val],0)."</td>";
						$tab.="<td style='text-align:right;font-weight:bold'>".number_format(($totalAgr[$val]-$totalReal[$val]),0)."</td>";
					}
		$tab.="</tbody>
		</table>";
		
		$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_BELANJA_TAHUNAN".$tglSkrg;
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
				parent.window.alert('Can't convert to excel format');
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
	
	case 'getDetail':
		$tab="";
		$tab.=$_SESSION['lang']['tahun']." : ".$tahun."<br>"
			.$_SESSION['lang']['pt']." : ".$optOrg[$kdPt]."<br>"
			.$_SESSION['lang']['unit']." : ".$optOrg[$kdUnit]."<br>";
		
		if($print=='excel'){
			$border=1;
		}else{
			$border=0;
			$tab.="<button onclick=\"printDetailExcel('".$tipe."','".$belanja."','".$tahun."','".$kdPt."','".$kdUnit."',event)\" class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>";
		}
		
		$count1=0;
		$count2=0;
		if($tipe=='realisasi'){
			if($belanja=='0'){
				$str = "select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodeorg = '".$kdUnit."' and left(noakun,1) in ('6','7','8','9') and nojurnal not like '%INV%' and nojurnal not like '%VHC%' and nojurnal not like '%SPK%' and substr(tanggal,1,4) = '".$tahun."' group by noakun";
			}else if($belanja=='1'){
				$str = "select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodeorg = '".$kdUnit."' and left(noakun,1) in ('6','7','8','9') and (nojurnal like '%INV%' or nojurnal like '%VHC%' or nojurnal like '%SPK%') and substr(tanggal,1,4) = '".$tahun."' group by  noakun";
			}else{
				$str = "select noakun, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where kodeorg = '".$kdUnit."' and left(noakun,3) in ('115','126','127','128') and substr(tanggal,1,4) = '".$tahun."' group by noakun";
			}
		}else{
			if($belanja=='0'){
				$str = "select noakun, sum(rupiah) as jumlah from ".$dbname.".bgt_budget_detail where kodeorg like '".$kdUnit."%' and (kodebudget like 'SDM%' or kodebudget in ('SUPERVISI','EXPL-UPAH','EXPL-LEMBUR','UMUM')) and left(noakun,1) in ('6') and tahunbudget = '".$tahun."' group by noakun";
			}else if($belanja=='1'){
				$str = "select noakun, sum(rupiah) as jumlah from ".$dbname.".bgt_budget_detail where kodeorg like '".$kdUnit."%' and (kodebudget like 'M%' or kodebudget in ('TOOL','VHC','KONTRAK'  )) and left(noakun,1) in ('6','7','8','9') and tahunbudget = '".$tahun."' group by noakun";
			}else{
				$str = "select noakun, sum(rupiah) as jumlah from ".$dbname.".bgt_budget_detail where kodeorg like '".$kdUnit."%' and left(noakun,3) in ('126','127','128') and left(noakun,3) != '115' and tahunbudget = '".$tahun."' group by noakun";
				
				$str2 = "select sum(hargatotal) as jumlah from ".$dbname.".bgt_kapital where kodeunit = '".$kdUnit."'group by tahunbudget";
				$qry2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$qry2->setFetchMode(PDO::FETCH_ASSOC);
				$count2=owlBaris($qry2);
			}
		}
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$count1=owlBaris($qry);
		
		$tab.="<div style='max-height:450px;max-width:100%;overflow:auto;'>";
		$tab.="<table class=sortable cellspacing=1 border=".$border.">
			<thead>
			<tr>
				<td>".$_SESSION['lang']['noakun']."</td>
				<td>".$_SESSION['lang']['namaakun']."</td>
				<td>".$_SESSION['lang']['jumlah']."</td>
			</tr>
			</thead>
			<tbody>";
			if($count1==0 and $count2==0){
				$tab.="<tr class=rowcontent><td colspan=3 style='text-align:center;font-weight:bold'>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}else{
				$total=0;
				while($res=$qry->fetch()){
					$tab.="<tr class=rowcontent>
						<td style='vertical-align:top'>".$res['noakun']."</td>
						<td style='vertical-align:top'>".$optAkun[$res['noakun']]."</td>
						<td style='text-align:right;vertical-align:top'>".number_format($res['jumlah'],0)."</td>
					</tr>";
					$total+=$res['jumlah'];
				}
				if($count2!=0){
					$tab.="<tr class=rowcontent>
						<td style='vertical-align:top'></td>
						<td style='vertical-align:top'>Budget Kapital</td>
						<td style='text-align:right;vertical-align:top'>".number_format($res2['jumlah'],0)."</td>
					</tr>";
				}
			}
				
		$tab.="<tr>
				<td colspan=2 style='text-align:center'><b>TOTAL</b></td>
				<td style='text-align:right'><b>".number_format($total+(isset($res2['jumlah']) ? $res2['jumlah'] : 0),0)."</b></td>
			</tr>
			</tbody>
		</table></div>";
		
		if($print=='excel'){
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
			$tglSkrg=date("Ymd");
			$nop_="LAPORAN_BELANJA_TAHUNAN_DETAIL_".$tipe."_".$tglSkrg;
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
					parent.window.alert('Can't convert to excel format');
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
		}else{
			echo $tab;
		}
		
		break;
}
	
?>
