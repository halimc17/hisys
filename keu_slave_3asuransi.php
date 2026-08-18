<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$proses = $_GET['proses'];
$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$maxDay = cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit('error');
/**
 * Proses Get Posted Mutasi
 */
// Get Data

$arrhutangunit=array("0"=>"Tidak","1"=>"Ya");

$qTrans = "SELECT * FROM ".$dbname.".keu_transaksi_rutin WHERE kodeorg='".$param['kodeorg']."' and posting in ('1','2') 
and  tanggalmulai<='".$param['periode']."-31' and  tanggalselesai>='".$param['periode']."-01'";

/*
SELECT * FROM ithaca.keu_transaksi_rutin WHERE kodeorg='SDKM' and posting=1 and tanggalmulai<='2019-11-01' and tanggalselesai>='2019-11-01';
*/
// 201910	202007
// 201911

// echo $qTrans;exit();
/*
$qTrans = "SELECT * FROM ".$dbname.".keu_transaksi_rutin WHERE kodeorg='".$param['kodeorg']."' 
and tipewaktu='TAHUNAN' and posting=1 and '".$param['periode']."' between left(tanggalmulai,7) and left(tanggalselesai,7)";
*/
// echo $qTrans;
$data = fetchData($qTrans);

switch($proses) {
	case 'list':
		// Tampilan
		if(empty($data)) {
			$attr = array('disabled'=>'disabled');
		} else {
			$attr = array('onclick'=>"postasuransi()");
		}
		$tab = makeElement($dbname,'button',$_SESSION['lang']['posting'], $attr);
		$tab.="<button class=mybutton onclick=exportTableToExcel()>Excel</button>";
		$tab .= "<table class=sortable cellpadding=5 cellspacing=1 id=mytable><thead><tr class=rowheader>";
		$tab .= "<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['kodeorg']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['keterangan']."</th>";
		
		$tab .= "<th align=center>".$_SESSION['lang']['tanggalmulai']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['tanggalselesai']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['tanggal']." Stop</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['rp']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['tenor']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['rp']."/".$_SESSION['lang']['bulan']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['rp']."/".$_SESSION['lang']['bulan']."<br>".$_SESSION['lang']['jurnal']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['sisa']." ".$_SESSION['lang']['pembulatan']."</th>";
		$tab .= "<th align=center>".$_SESSION['lang']['jurnal']." ".$_SESSION['lang']['periode']."<br>".$param['periode']."</th>";
		$tab .= "</tr></thead><tbody>";
		if(empty($data)) {
			$tab .= "<tr class=rowcontent><td colspan=3>".
				$_SESSION['lang']['tidakditemukan']."</td></tr>";
		} else {
			foreach($data as $row) {
				if($row['posting']==2){
					if($param['periode']>=substr($row['tanggalstop'],0,7)){
						continue;
					}
				}
				$row['rpperbulan']=$row['harga_barang']/$row['tenor'];
				$explrpperbulan=explode('.',$row['rpperbulan']);
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td>".$row['notransaksi']."</td>";
				$tab .= "<td>".$row['kodeorg']."</td>";
				$tab .= "<td>".$row['keterangan']."</td>";
				$tab .= "<td>".tanggalnormal($row['tanggalmulai'])."</td>";
				$tab .= "<td>".tanggalnormal($row['tanggalselesai'])."</td>";
				if($row['tanggalstop']=='0000-00-00'){
					$tab .= "<td></td>";
				}else{
					$tab .= "<td>".tanggalnormal($row['tanggalstop'])."</td>";
				}
				$tab .= "<td align=right>".hidezerodecimal($row['harga_barang'],2)."</td>";
				$tab .= "<td align=right>".hidezerodecimal($row['tenor'],2)."</td>";
				$tab .= "<td align=right>".hidezerodecimal($row['rpperbulan'],2)."</td>";
				$tab .= "<td align=right>".hidezerodecimal($explrpperbulan[0],2)."</td>";
				$nilaisisa=0;
				if(substr($row['tanggalselesai'],0,7)==$param['periode']){
					$nilaisisa=$row['harga_barang']-($explrpperbulan[0]*$row['tenor']);
				}
				$tab .= "<td align=right>".hidezerodecimal($nilaisisa,2)."</td>";
				$tab .= "<td align=right>".hidezerodecimal($explrpperbulan[0]+$nilaisisa,2)."</td>";
				$tab .= "</tr>";
			}
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	case 'post':
		$tglJurnl=$param['periode']."-28";
		$dtTgl=explode("-", $param['periode']);
		$awalJrn=$dtTgl[0].$dtTgl[1]."28";
		$awal = 1;
		$cekawal=0;
		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";
		// exit('warning');
		// bersihkan all jurnal SAS bulan ini
		$kodejurnal="SAS01";  
	        		$i = "delete from ".$dbname.".keu_jurnalht where nojurnal like '%".$param['kodeorg']."/".$kodejurnal."%' and tanggal='".$tglJurnl."' and kodejurnal = '".$kodejurnal."'  "; // exit("error:".$i);
			        try {
		                $owlPDO->exec($i);
		            } catch (PDOException $e) {		            	
		                print " Gagal: " . $e->getMessage() . "\n";
		                die();
		            }

		foreach($data as $row) {
			
			if($row['posting']==2){
				if($param['periode']>=substr($row['tanggalstop'],0,7)){
					continue;
				}
			}
			
			$kodejurnal="SAS01";  
			
			
			 
	        $notrans=$awalJrn."/".$param['kodeorg']."/".$kodejurnal."/".addZero($awal,3);
	        $row['rpperbulan']=$row['harga_barang']/$row['tenor'];
			$explrpperbulan=explode('.',$row['rpperbulan']);
			$nilaisisa=0;
			if(substr($row['tanggalselesai'],0,7)==$param['periode']){
				$nilaisisa=$row['harga_barang']-($explrpperbulan[0]*$row['tenor']);
			}
			$row['rpperbulan']=$explrpperbulan[0]+$nilaisisa;
			$jlhbrs=0;
			$str="select count(*) as jmlhrow from ".$dbname.".keu_jurnalht where nojurnal like '%".$param['kodeorg']."/".$kodejurnal."%' and tanggal='".$tglJurnl."' and kodejurnal = '".$kodejurnal."' and noreferensi = '".$row['notransaksi']."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $jsl=$res->fetch();
	        $jlhbrs = $jsl['jmlhrow'];

	        if ($jlhbrs>0){
	        	if($cekawal==0){
	        		$i = "delete from ".$dbname.".keu_jurnalht where nojurnal like '%".$param['kodeorg']."/".$kodejurnal."%' and tanggal='".$tglJurnl."' and kodejurnal = '".$kodejurnal."' and noreferensi = '".$row['notransaksi']."'  ";
			        try {
		                $owlPDO->exec($i);
		            } catch (PDOException $e) {
		            	
		                print " Gagal: " . $e->getMessage() . "\n";
		                die();
		            }
	        	}
	        	$cekawal=1;
	        }
			
			
			$i = "insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) values ('".$notrans."','".$kodejurnal."','".$row['rpperbulan']."','".-($row['rpperbulan'])."','".$tglJurnl."','".date('Ymd')."','1','".$row['notransaksi']."','IDR','1')";
			try {
		        $owlPDO->exec($i);

	            $i = "insert into " . $dbname . ".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,kodevhc,kodesupplier,nodok)
	            values ('" . $notrans . "','" . $tglJurnl . "','1','" .$row['noakun_debet']. "','Jurnal Otomatis ".$row['keterangan']."','" .$row['rpperbulan']. "','IDR','1','".$param['kodeorg']."','" . $row['notransaksi'] . "','" . $row['kodevhc'] . "','" . $row['supplierid'] . "','" . $row['notransaksi'] . "')";
	            try{
                    $owlPDO->exec($i);
                } catch (PDOException $e) {
                    print " Gagal: " . $e->getMessage() . "\n";
                    die();
                }

	            $i = "insert into " . $dbname . ".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,kodevhc,kodesupplier,nodok)
	            values ('" . $notrans . "','" . $tglJurnl . "','2','" .$row['noakun_kredit']. "','Jurnal Otomatis ".$row['keterangan']."','" .-($row['rpperbulan']). "','IDR','1','".$param['kodeorg']."','" . $row['notransaksi'] . "','" . $row['kodevhc'] . "','" . $row['supplierid'] . "','" . $row['notransaksi'] . "')";
	            try{
                    $owlPDO->exec($i);
                } catch (PDOException $e) {
                    print " Gagal: " . $e->getMessage() . "\n";
                    die();
                }
	            
	        } catch (PDOException $e) {
                print " Gagal: " . $e->getMessage() . "\n";
                die();
            }
	         $awal++;
		}
		
		break;
	
	default:
		echo 'Process Undefined\nProcess Undefined\nProcess Undefined';
		break;
}