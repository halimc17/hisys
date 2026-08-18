<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$tipe=checkPostGet('tipe','');
$proses=checkPostGet('proses','');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
$tahun=substr($tanggal,0,4);

if ($proses!='getunit') {

	## Filter data
	$whr="";
	$whrset="";
	$whrinv="";
	$whrsetkk="";
	if ($unit=='') {
		$whrset=$whr=" and kodeorght in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		$whrsetkk=$whrinv=" and unit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
	}else{
		$whrset=$whr=" and kodeorght ='".$unit."'";
		$whrsetkk=$whrinv=" and unit ='".$unit."'";
	}

	if ($tanggal!='' && $tanggal!='--') {
		$whr.=" and tanggal<='".$tanggal."'";
		$whrinv.=" and tanggal<='".$tanggal."'";
	}

												###############################
												############ Begin ############
												###############################

	## Inisialisasi array
	$arrlist=array();
	$arrkary=array();
	$arrnodok=array();
	$arridkar=array();
	$arrnoinv=array();

	switch ($tipe) {
		case 'upd':

			$tipelap=$_SESSION['lang']['perdin'];
			## Data tagihan uang muka perjalanan dinas
			$str="select noinvoice, nopo, kodesupplier as idkar from ".$dbname.".keu_tagihanht where tipeinvoice='".$tipe."' ".$whrinv." and left(tanggal,4)='".$tahun."' and posting='1'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$arrnodok[$bar['nopo']]=$bar['nopo'];				
				$arridkar[$bar['idkar']]=$bar['idkar'];	
				$arrnoinv[$bar['noinvoice']]=$bar['noinvoice'];		
			}

			## Data pembayaran uang muka perjalanan dinas
			$str="select tanggal,nodok,sum(jumlah) as jumlah,kodesupplier as idkar from ".$dbname.".keu_kasbankdtht_vw where posting='1' ".$whr." and left(tanggal,4)='".$tahun."' and keterangan1 in ('".implode("','",$arrnoinv)."') group by keterangan1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				
				$dt1 = strtotime($bar['tanggal']);
                $dt2 = strtotime($tanggal);
                $diff = abs($dt2-$dt1);
                $jmlhhari = (($diff/86400));

				$arrlist[$bar['nodok']]['jumlahhari']=$jmlhhari;
				$arrlist[$bar['nodok']]['tanggalum']=$bar['tanggal'];
				$arrlist[$bar['nodok']]['idkar']=$bar['idkar'];	
				$arrlist[$bar['nodok']]['um']=$bar['jumlah'];	
  
			}

			## Data pertanggungjawaban perjalanan dinas
			$str="select tipetransaksi,tanggal,nodok,sum(jumlah) as jumlah, kodesupplier, kodecustomer from ".$dbname.".keu_kasbankdtht_vw where posting='1' ".$whrset." and keterangan1 not in ('".implode("','",$arrnoinv)."') and nodok in ('".implode("','",$arrnodok)."') group by keterangan1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$arrlist[$bar['nodok']]['tanggalpjd']=$bar['tanggal'];	
				if ($bar['tipetransaksi']=='M') {
					$arrlist[$bar['nodok']]['pjd']=$arrlist[$bar['nodok']]['um']-$bar['jumlah'];
					$arrlist[$bar['nodok']]['sisa']=$bar['jumlah'];
				}else{
					$arrlist[$bar['nodok']]['pjd']=$arrlist[$bar['nodok']]['um']+$bar['jumlah'];
					$arrlist[$bar['nodok']]['sisa']=$bar['jumlah']*(-1);
				}		
			}

		break;

		case 'kk':

			$tipelap=$_SESSION['lang']['kaskecil'];
			## Data uang muka kas kecil
			$str="select tanggal,novoucher as nodok,sum(jumlah) as jumlah,penerima as idkar from ".$dbname.".keu_kaskecil_vw where posting='1' and jenis='1' ".$whrinv." and left(tanggal,4)='".$tahun."' and penerima!='' group by novoucher,penerima";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				
				$dt1 = strtotime($bar['tanggal']);
                $dt2 = strtotime($tanggal);
                $diff = abs($dt2-$dt1);
                $jmlhhari = (($diff/86400));

				$arrnodok[$bar['nodok']]=$bar['nodok'];				
				$arridkar[$bar['idkar']]=$bar['idkar'];	
				$arrlist[$bar['nodok']][$bar['idkar']]['jumlahhari']=$jmlhhari;
				$arrlist[$bar['nodok']][$bar['idkar']]['tanggalum']=$bar['tanggal'];
				$arrlist[$bar['nodok']][$bar['idkar']]['um']=$bar['jumlah'];	
  
			}

			## Data pertanggungjawaban uang muka kas kecil
			$str="select tanggal,noreferensi as nodok,sum(jumlah) as jumlah,penerima as idkar from ".$dbname.".keu_kaskecil_vw where posting='1' and jenis='2' ".$whrsetkk." and noreferensi in ('".implode("','",$arrnodok)."') group by noreferensi ,penerima";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$arrlist[$bar['nodok']][$bar['idkar']]['tanggalpjd']=$bar['tanggal'];	
				$arrlist[$bar['nodok']][$bar['idkar']]['pjd']=$bar['jumlah'];
				$arrlist[$bar['nodok']][$bar['idkar']]['sisa']=$arrlist[$bar['nodok']][$bar['idkar']]['um']-$bar['jumlah'];		
			}
			
		break;
	}

	## Data karyawan perjalanan dinas
	$str="select karyawanid,nik,namakaryawan,lokasitugas,bagian as departemen from ".$dbname.".datakaryawan where 1=1 and karyawanid in ('".implode("','",$arridkar)."')";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$arrkary[$bar['karyawanid']]['nik']=$bar['nik'];	
		$arrkary[$bar['karyawanid']]['namakaryawan']=$bar['namakaryawan'];	
		$arrkary[$bar['karyawanid']]['lokasitugas']=$bar['lokasitugas'];	
		$arrkary[$bar['karyawanid']]['departemen']=$bar['departemen'];	
	}

	if (count($arrlist)==0) {
		exit('Warning : data empty.');
	}

												################################
												############# End ##############
												################################

	// echo "<pre>";
	// print_r($arrsetel);
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
						<td align=center rowspan=2>".$_SESSION['lang']['nik']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['lokasitugas']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['departemen']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>
						<td align=center colspan=2>".$_SESSION['lang']['uangmuka']."</td>
						<td align=center colspan=2>Penyelesaian</td>
						<td align=center rowspan=2>".$_SESSION['lang']['sisa']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['jumlah']." Hari</td>
					</tr>
					<tr>
						<td align=center>".$_SESSION['lang']['tanggal']."</td>
						<td align=center>".$_SESSION['lang']['rupiah']."</td>
						<td align=center>".$_SESSION['lang']['tanggal']."</td>
						<td align=center>".$_SESSION['lang']['rupiah']."</td>
					</tr>
				</thead>";

				switch ($tipe) {
					case 'upd':

						$no=0;
						foreach ($arrlist as $notransaksi => $data) {
							$no+=1;
							$whrdep="kode='".$arrkary[$data['idkar']]['departemen']."'";
							$optnmdep= makeOption($dbname, 'sdm_5departemen','kode,nama',$whrdep);
							$display.="<tr class=rowcontent>
								<td align=center>".$no."</td>
								<td>".$arrkary[$data['idkar']]['nik']."</td>
								<td>".$arrkary[$data['idkar']]['namakaryawan']."</td>
								<td>".$arrkary[$data['idkar']]['lokasitugas']."</td>
								<td>".$optnmdep[$arrkary[$data['idkar']]['departemen']]."</td>
								<td>".$notransaksi."</td>";
								if ($data['tanggalum']!='') {
									$display.="<td>".tanggalnormal($data['tanggalum'])."</td>";
								}else{
									$display.="<td></td>";
								}
							$display.="<td align='right'>".number_format($data['um'])."</td>";
								if ($data['tanggalpjd']!='') {
									$display.="<td>".tanggalnormal($data['tanggalpjd'])."</td>";
								}else{
									$display.="<td></td>";
								}
							$display.="<td align='right'>".number_format($data['pjd'])."</td>
								<td align='right'>".number_format($data['sisa'])."</td>
								<td align='right'>".$data['jumlahhari']."</td>
								</tr>";
						}
					break;

					case 'kk':
						$no=0;
						foreach ($arrlist as $notransaksi => $listidkar) {
							foreach ($listidkar as $idkar => $data) {
								$no+=1;
								$whrdep="kode='".$arrkary[$idkar]['departemen']."'";
								$optnmdep= makeOption($dbname, 'sdm_5departemen','kode,nama',$whrdep);
								$display.="<tr class=rowcontent>
									<td align=center>".$no."</td>
									<td>".$arrkary[$idkar]['nik']."</td>
									<td>".$arrkary[$idkar]['namakaryawan']."</td>
									<td>".$arrkary[$idkar]['lokasitugas']."</td>
									<td>".$optnmdep[$arrkary[$idkar]['departemen']]."</td>
									<td>".$notransaksi."</td>";
									if ($data['tanggalum']!='') {
										$display.="<td>".tanggalnormal($data['tanggalum'])."</td>";
									}else{
										$display.="<td></td>";
									}
								$display.="<td align='right'>".number_format($data['um'])."</td>";
									if ($data['tanggalpjd']!='') {
										$display.="<td>".tanggalnormal($data['tanggalpjd'])."</td>";
									}else{
										$display.="<td></td>";
									}
								$display.="<td align='right'>".number_format($data['pjd'])."</td>
									<td align='right'>".number_format($data['sisa'])."</td>
									<td align='right'>".$data['jumlahhari']."</td>
									</tr>";
							}
						}
					break;
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
        $nop_ = "Advanced aging ".$tipelap;
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