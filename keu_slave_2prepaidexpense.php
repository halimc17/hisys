<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$pt=checkPostGet('pt','');
$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));

if ($proses!='getunit') {

	## Filter data
	$whr="";
	if ($unit=='') {
		$whr=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
	}else{
		$whr=" and kodeorg='".$unit."'";
	}

												###############################
												############ Begin ############
												###############################

	## Inisialisasi array
	$arrlist=array();

	## Data transaksi rutin
	$str="select notransaksi,kodeorg,keterangan as nama,supplierid,tanggalmulai as tanggal,TIMESTAMPDIFF(MONTH,tanggalmulai,'".$tanggal."') as jlhblnakumulasi,tanggalselesai,tenor,harga_barang as jumlah from ".$dbname.".keu_transaksi_rutin where posting='1' ".$whr."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){			
		$arrlist[$bar['notransaksi']]['nama']=$bar['nama'];		
		$arrlist[$bar['notransaksi']]['supplierid']=$bar['supplierid'];		
		$arrlist[$bar['notransaksi']]['tanggal']=$bar['tanggal'];		
		$arrlist[$bar['notransaksi']]['tenor']=$bar['tenor'];		
		$arrlist[$bar['notransaksi']]['jumlah']=$bar['jumlah'];

		if ($bar['jlhblnakumulasi']>$bar['tenor']) {
			$bar['jlhblnakumulasi']=$bar['tenor'];
		}

		$arrlist[$bar['notransaksi']]['akumulasibulan']=$bar['jlhblnakumulasi'];
		$arrlist[$bar['notransaksi']]['sisabulan']=$bar['tenor']-$arrlist[$bar['notransaksi']]['akumulasibulan'];
		$arrlist[$bar['notransaksi']]['jumlahakumulasi']=($bar['jumlah']/$bar['tenor'])*$arrlist[$bar['notransaksi']]['akumulasibulan'];	
		$arrlist[$bar['notransaksi']]['jumlahsisa']=$bar['jumlah']-$arrlist[$bar['notransaksi']]['jumlahakumulasi'];
	}


	## Data leasing
	$str="select notransaksi,supplierid_asuransi,nokontrak_asuransi,nokontrak_leasing,kodeorg,supplierid_leasing as supplierid,tgl_efektif as tanggal,tenor,totalkredit as jumlah 
	from ".$dbname.".keu_leasinght where posting='1' ".$whr."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){			
		$arrlist[$bar['notransaksi']]['nama']="SUPPLIER ASURANSI : ".$bar['supplierid_asuransi'].", NO KONTRAK ASURANSI : ".$bar['nokontrak_asuransi'].", NO KONTRAK LEASING : ".$bar['nokontrak_leasing'];		
		$arrlist[$bar['notransaksi']]['supplierid']=$bar['supplierid'];		
		$arrlist[$bar['notransaksi']]['tanggal']=$bar['tanggal'];		
		$arrlist[$bar['notransaksi']]['tenor']=$bar['tenor'];		
		$arrlist[$bar['notransaksi']]['jumlah']=$bar['jumlah'];

		$str1="select count(*) as akumulasibulan from ".$dbname.".keu_leasingdt where notransaksi='".$bar['notransaksi']."' and tgl_transaksi<='".$tanggal."' and statuskasbank=1";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		$bar1=$res1->fetch();
		$arrlist[$bar['notransaksi']]['akumulasibulan']=$bar1['akumulasibulan'];	
		$arrlist[$bar['notransaksi']]['sisabulan']=$bar['tenor']-$bar1['akumulasibulan'];
		$sisatenor=$bar['tenor']-1;
		$arrlist[$bar['notransaksi']]['jumlahakumulasi']=($bar['jumlah']/$sisatenor)*$bar1['akumulasibulan'];	
		$arrlist[$bar['notransaksi']]['jumlahsisa']=$bar['jumlah']-$arrlist[$bar['notransaksi']]['jumlahakumulasi'];	
	}

	if (count($arrlist)==0) {
		exit('Warning : data empty.');
	}

												################################
												############# End ##############
												################################

	// echo "<pre>";
	// print_r($arrlist);
	// echo "</pre>";
	// exit('warning : ');

	$border=0;
	if ($proses=='excel') {
		$border=1;
	}

		## Display Data
		$display.="<table class=sortable cellspacing=1 border=".$border." width=100%>
					<thead>
						<tr>
							<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>
							<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>
							<td align=center rowspan=2>".$_SESSION['lang']['nama']."</td>
							<td align=center rowspan=2>".$_SESSION['lang']['namasupplier']."</td>
							<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>
							<td align=center colspan=3>".$_SESSION['lang']['bulan']."</td>
							<td align=center colspan=3>".$_SESSION['lang']['rupiah']."</td>
						</tr>
						<tr>
							<td align=center>".$_SESSION['lang']['jumlah']."</td>
							<td align=center>Akumulasi</td>
							<td align=center>".$_SESSION['lang']['sisa']."</td>
							<td align=center>".$_SESSION['lang']['jumlah']."</td>
							<td align=center>Akumulasi</td>
							<td align=center>".$_SESSION['lang']['sisa']."</td>
						</tr>
					</thead>";

		$no=0;
		foreach ($arrlist as $notransaksi => $data) {
			
			$no+=1;
			$whr="supplierid='".$data['supplierid']."'";
			$optnmsup= makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
			$display.="<tr class=rowcontent>
				<td>".$no."</td>
				<td>".$notransaksi."</td>
				<td>".$data['nama']."</td>
				<td>".$optnmsup[$data['supplierid']]."</td>
				<td>".tanggalnormal($data['tanggal'])."</td>
				<td>".$data['tenor']."</td>
				<td>".$data['akumulasibulan']."</td>
				<td>".$data['sisabulan']."</td>
				<td align='right'>".number_format($data['jumlah'])."</td>";
			$display.="<td align='right'>".number_format($data['jumlahakumulasi'])."</td>";
			$display.="<td align='right'>".number_format($data['jumlahsisa'])."</td>";
			$display.="</tr>";
		}

	$display.="</table>";
}

switch ($proses) {
	case 'getunit':
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."'";
	    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	    $res->setFetchMode(PDO::FETCH_OBJ);        
	    $optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
	    while($bar= $res->fetch())
	    {
	      $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	    }

	    echo $optgudang;
	break;

    case 'preview':
        echo $display;
    break;

    case 'excel':
        $tglSkrg = date("Ymd");

        $kodeorg=$unit;
        if ($unit=='') {
        	$kodeorg=$pt;
        }
        $nop_ = "Prepaid_expense_".$kodeorg;
        if (strlen($display) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $display)) {
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