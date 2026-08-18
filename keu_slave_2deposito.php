<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
?>
<link rel=stylesheet type=text/css href=style/generic.css>	
<?php

$unit = checkPostGet('unit', '');
$jenis = checkPostGet('jenis', '');
$tipe = checkPostGet('tipe', '');
$method = checkPostGet('method', '');
$arrstatus=array('0'=>'Non Rol-Over','1'=>'Roll Over','2'=>'Closed');
$arrjenis=array('1'=>$_SESSION['lang']['depositoberjangka'],'2'=>$_SESSION['lang']['depositoberjangka'],'3'=>$_SESSION['lang']['sertifikatdeposito'],'4'=>'Deposito on Call');

switch ($method) {
	case 'preview':
		$style="align=center bgcolor='#C0C0C0' style='font-weight: bold;'";
		if($tipe=='excel'){
			$border=" border=1";
			$title="<tr align=center>
						<td rowspan=2 colspan=16 ".$style.">REKAP FIX DEPOSITO</td>
					</tr><tr></tr>";
		}else{
			$border=" border=0";
		}

		$tab="<table cellpading=1 cellspacing=1 ".$border." class=sortable style=width:100%>
			<thead>
			".$title."
			<tr>
				<td rowspan=2 ".$style.">".$_SESSION['lang']['nourut']."</td>
				<td rowspan=2 ".$style.">".$_SESSION['lang']['notransaksi']."</td>
				<td colspan=2 ".$style.">".$_SESSION['lang']['pt']."</td>
				<td colspan=3 ".$style.">".$_SESSION['lang']['databank']."</td>
				<td colspan=9 ".$style.">".$_SESSION['lang']['datadeposito']."</td>
			</tr>
			<tr>
				<td ".$style.">".$_SESSION['lang']['nama']." PT</td>
				<td ".$style.">".$_SESSION['lang']['lokasi']."</td>
				<td ".$style.">".$_SESSION['lang']['namabank']."</td>
				<td ".$style.">".$_SESSION['lang']['matauang']."</td>
				<td ".$style.">".$_SESSION['lang']['norekeningbank']."</td>
				<td ".$style.">".$_SESSION['lang']['jenisdeposito']."</td>
				<td ".$style.">".$_SESSION['lang']['nourut']." Bilyet</td>
				<td ".$style.">".$_SESSION['lang']['nourut']." Deposito</td>
				<td ".$style.">".$_SESSION['lang']['tanggalvaluta']."</td>
				<td ".$style.">".$_SESSION['lang']['tanggaljatuhtempo']."</td>
				<td ".$style.">Jangka Waktu</td>
				<td ".$style.">".$_SESSION['lang']['status']."</td>
				<td ".$style.">".$_SESSION['lang']['sukubunga']." %</td>
				<td ".$style.">".$_SESSION['lang']['jumlahdeposito']."</td>
			</tr></thead><tbody>";

		if ($jenis!='') {
			$whr=" and jnsdeposito='".$jenis."'";
		}

		$no=0;
		$str="select * from ".$dbname.".keu_depositoht where unit='".$unit."' ".$whr."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()) {
			
			//unit
			$whrunit="kodeorganisasi='".$bar['unit']."'";
		    $optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrunit);
			//induk
			$whrinduk="kodeorganisasi='".$bar['unit']."'";
		    $optinduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',$whrinduk);
			//PT
			$whrpt="kodeorganisasi='".$optinduk[$bar['unit']]."'";
		    $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);

		    //get namabank
		    $nmBankDt="";
		    $strak="select b.namabank,matauang,rekening from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where noakun='".$bar['noakun']."'";
		    $barak=fetchData($strak);
		    $dtRek=$barak[0];
		    $rekening=$dtRek['rekening'];
		    $nmBankDt=$dtRek['namabank'];
		    $matauang=$dtRek['matauang'];

			$no+=1;
			$tab.="<tr class=rowcontent onclick=viewdetail('".$bar['notransaksi']."') title='View Detail' style='cursor:pointer;'>
			    <td style='text-align:center;'>".$no."</td>
			    <td>".$bar['notransaksi']."</td>
			    <td>".$optpt[$optinduk[$bar['unit']]]."</td>
			    <td>".$optunit[$bar['unit']]."</td>
			    <td>".$nmBankDt."</td>
			    <td>".$matauang."</td>
			    <td>".$rekening."</td>
			    <td>".$arrjenis[$bar['jnsdeposito']]."</td>
			    <td>".$bar['nobilyet']."</td>
			    <td>".$bar['nodeposito']."</td>
			    <td>".tanggalnormal($bar['tglvaluta'])."</td>
			    <td>".tanggalnormal($bar['tgljatuhtempo'])."</td>
			    <td align=center>".$bar['jangkawaktu']."</td>
			    <td>".$arrstatus[$bar['status']]."</td>
			    <td align=center>".$bar['sukubunga']."</td>
			    <td align=right>".number_format($bar['jmlhdeposito'])."</td>";
		}

		$tab.="</tbody ".$style.">";
		$tab.="</table ".$style.">";

		if($tipe=='html'){
			echo $tab;
		}else{
			$tglSkrg = date("Ymd");
			$nop_ = "rekap_fixdeposito (".$unit."-".$tglSkrg.")";
			if (strlen($tab) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $tab)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script ".$style.">";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script ".$style.">";
				}
				fclose($handle);
		    }
		}
	break;
	
	case 'viewdetail':

        $tab.="<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
        $tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable  style='float:left;'>";
        $tab.="<thead>";
        $tab.="<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['notransaksi']." Kasbank</td>";
        $tab.="<td>".$_SESSION['lang']['tglpencairanbunga']."</td>";
        $tab.="<td>".$_SESSION['lang']['tglpenerimaanbunga']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlahbunga']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlahpajak']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlahpenalti']."</td>";
        $tab.="<td>".$_SESSION['lang']['total']."</td>";
        $tab.="<td>".$_SESSION['lang']['realisasi']."</td>";
        $tab.="<td>Variance</td>";
        $tab.="</tr></thead><tbody >";

        $no=0;
        $str = "select * from ".$dbname.".keu_depositodt where notransaksi='".$notransaksi."' order by tglterima desc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $no+=1;

            $total=$bar['jumlahbunga']-$bar['jumlahpajak']-$bar['jumlahpenalti'];
            $variance=$total-$bar['realisasi'];

            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$bar['notranskasbank']."</td>
                <td>".tanggalnormal($bar['tglcair'])."</td>
                <td>".tanggalnormal($bar['tglterima'])."</td>
                <td align=right>".number_format($bar['jumlahbunga'])."</td>
                <td align=right>".number_format($bar['jumlahpajak'])."</td>
                <td align=right>".number_format($bar['jumlahpenalti'])."</td>
                <td align=right>".number_format($total)."</td>
                <td align=right>".number_format($bar['realisasi'])."</td>
                <td align=right>".number_format($variance)."</td>";
            $tab.="</tr>";
        }

        $tab.="</tbody>";
        $tab.="</table></fieldset>";

        echo $tab;
    break;
	
}



?>