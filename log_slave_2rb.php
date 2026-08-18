<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$proses = checkPostGet('proses', '');
$kodept = checkPostGet('kodept', '');
$unit = checkPostGet('unit', '');
$periode = checkPostGet('periode', '');

$optOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
if ($kodept == '') {
    echo"Warning: PT tidak boleh kosong";
    exit;
}

######################################
############# prepare data ###########
######################################
if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1 cellpadding=5 width=100%>";
}
$stream.="
    <thead>
        <tr class=rowheader>
			<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
			<th align=center>" . $_SESSION['lang']['pt'] . "</th>
			<th align=center>" . $_SESSION['lang']['gudang'] . "</th>
			<th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
			<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			<th align=center>" . $_SESSION['lang']['satuan'] . "</th>
			<th align=center>" . $_SESSION['lang']['minstok'] . "</th>
			<th align=center>" . $_SESSION['lang']['saldodigudang'] . "</th>
        </tr>";
$stream.="
        </tr>
    </thead>
	<tbody>";
	$where='';
	if($unit!=''){
		$where.=" and kodegudang='".$unit."' and periode='".$periode."' ";
	}
	$xxx=" and left(gudang,4) in (select kodeorganisasi from organisasi WHERE induk = '".$kodept."')";
	if($unit!=''){
		$xxx.=" and a.gudang = '".$unit."'";
	}
	
	#reminder  stok minimum
	// $str = "select a.*,b.namabarang,b.satuan from " . $dbname . ".log_5masterbarang_minstock a left join log_5masterbarang b on a.kodebarang=b.kodebarang where a.minstok>0 order by a.kodebarang";
	
	$str = "select a.*,b.namabarang,b.satuan 
			from " . $dbname . ".log_5minimunstok a 
			left join log_5masterbarang b on a.kodebarang=b.kodebarang 
			where a.stok>0 ".$xxx." order by a.kodebarang";
	// exit('error'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {

		$barang[$bar->kodebarang] = $bar->kodebarang;
		$namabarang[$bar->kodebarang] = $bar->namabarang;
		$satuan[$bar->kodebarang] = $bar->satuan;
		$minstok[$bar->kodebarang] = $bar->minstok;
		if ($unit == '') {
			$bar->gudang = $_SESSION['lang']['all']; 	
		}
		$arrkodebarang[$bar->gudang][$bar->kodebarang]['saldomin'] += $bar->stok;
	}
	// echo $str;
	// echo '<pre>';
	// print_r($namabarang);
	// echo '</pre>';
	
	#ambil saldo per PT
	// $str = "select a.*, sum(saldoqty) as saldo  ".$xxx." from " . $dbname . ".log_5masterbarang_minstock a
	// 		  left join " . $dbname . ".log_5masterbarangdt b on a.kodebarang=b.kodebarang and a.kodeunit=b.kodeorg
	// 		  where a.minstok>0 and a.kodeunit='".$kodept."' ".$where."
	// 		  group by a.kodeunit".$xxx.",a.kodebarang
	// 		  having (saldo < a.minstok or saldo=a.minstok)";

	$str = "select a.kodegudang,a.kodebarang,sum(a.saldoakhirqty) as saldo,b.namabarang,b.satuan 
			from " . $dbname . ".log_5saldobulanan a 
			left join log_5masterbarang b on a.kodebarang=b.kodebarang 
			where kodeorg='".$kodept."' ".$where."
			group by kodegudang, kodebarang";
	// exit('error'.$str);

	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
	
	if ($numrows > 0) {
		$no = 0;
		while ($bar = $res->fetch()) {
			if ($unit == '') {
				$bar->kodegudang = $_SESSION['lang']['all']; 	
			}
			$barang[$bar->kodebarang] = $bar->kodebarang;
			$namabarang[$bar->kodebarang] = $bar->namabarang;
			$satuan[$bar->kodebarang] = $bar->satuan;
			$arrkodebarang[$bar->kodegudang][$bar->kodebarang]['saldogudang'] += $bar->saldo;
			// echo "<pre>"; print_r($arrkodebarang); exit;
		}

		foreach ($arrkodebarang as $kodegudang => $arr) {
			foreach ($arr as $kodebarang => $saldo) {
					
					$skstok = $saldo['saldogudang'] - $saldo['saldomin'];
					$bgcolormin="";
					if($skstok < 0){
						$bgcolormin="red";
					}


				$no+=1;
				$stream.="<tr class=rowcontent style='background-color:".$bgcolormin."'>
							<td align=center>" . $no . "</td>
							<td align=center>" . $optOrg[$kodept] . " - ".$kodept."</td>";
				$stream.="  <td>" . $optOrg[$kodegudang] . " - ".$kodegudang."</td>";
				$stream.="	<td>" . $kodebarang . "</td>
							<td>" . $namabarang[$kodebarang] . "</td>
							<td align=center>" . $satuan[$kodebarang] . "</td>
							<td align=right>" . numberformat_kasih_koma($saldo['saldomin']) . "</td>
							<td align=right>" . numberformat_kasih_koma($saldo['saldogudang']) . "</td>
					</tr>";
			}
		}

	}else{
		exit('warning : Data kosong.');
	}

$stream.="
	</tbody>
    </table>";

switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "lap_minimum_stok " . $kodept." ".$unit;
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