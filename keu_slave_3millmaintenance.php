<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method','');

$kodeorg = checkPostGet('kodeorg','');
$periode = checkPostGet('periode','');
$jenisData = checkPostGet('jenisData','');




$station = checkPostGet('station','');
$jumlah = checkPostGet('jumlah','');




	// exit("Error:$method");	
		
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

$kodeJurnal ='PKS99';


switch ($method) {
	
	
	case'deletemaintenance':
	
		#= delete 1st
		$str = "delete from ".$dbname.".keu_jurnalht where  nojurnal like '%".$kodeorg."%' and 
				kodejurnal='".$kodeJurnal."' and tanggal like '".$periode."%'";
			// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	
	break;
	
	
	case 'savemaintenance':
	
		$str="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$kodeorg."'";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$kodept=$bar['induk'];
			
		$str="select * from ".$dbname.".organisasi where  tipe = 'MAINTENANCE' and induk='".$kodeorg."'";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$kodemaintenance=$bar['kodeorganisasi'];	
			
		$str="select * from ".$dbname.".keu_5parameterjurnal where 
				kodeaplikasi='PKS' and jurnalid='".$kodeJurnal."'";
		// exit("Error:$str");
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$akundebet=$bar['noakundebet'];
			$akunkredit=$bar['noakunkredit'];
		
		#proses data
		$tanggal=$periode.'-28';
		
		#======================== Nomor Jurnal =============================
		# Get Journal Counter
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$kodeorg."' and periode='".$periode."' ");
		$tmpKonter = fetchData($queryJ);
		$konter = addZero($tmpKonter[0]['nokounter']+1,3);

		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-","",$tanggal)."/".$kodeorg."/".$kodeJurnal."/".$konter;
		#======================== /Nomor Jurnal ============================
		
		# Prep Header
        $dataRes['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>$kodeJurnal,
            'tanggal'=>$tanggal,
            'tanggalentry'=>date('Ymd'),
            'posting'=>1,
            'totaldebet'=>$jumlah,
            'totalkredit'=>-1*$jumlah,
            'amountkoreksi'=>'0',
            'noreferensi'=>'ALK_MAINTENANCE',
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'                
        );

        # Data Detail
        $noUrut = 1;

        # Debet
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tanggal,
            'nourut'=>$noUrut,
            'noakun'=>$akundebet,
            'keterangan'=> 'Alokasi maintenance '.$periode,
            'jumlah'=>$jumlah,
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$kodeorg,
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'ALK_MAINTENANCE',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>$station,
            'revisi'=>'0',
            'kodesegment'=>$defSegment
        );
        $noUrut++;

        # Kredit
        $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tanggal,
            'nourut'=>$noUrut,
            'noakun'=>$akunkredit,
            'keterangan'=> 'Alokasi maintenance '.$periode,
            'jumlah'=>-1*$jumlah,
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$kodeorg,
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'ALK_MAINTENANCE',
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>$kodemaintenance,
            'revisi'=>'0',
            'kodesegment'=>$defSegment
        );
        $noUrut++;      

        $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
        try{$owlPDO->exec($insHead); }
        catch (PDOException $e) {
            $headErr .= 'Insert Header Alokasi Gaji MAINTENANCE Error : '.$e->getMessage()."\n";
        }           

		if(empty($headErr)) {
            #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
            $detailErr = '';
            foreach($dataRes['detail'] as $row) {
                $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                try{$owlPDO->exec($insDet); }
                catch (PDOException $e) {
                    $detailErr .= "Insert Detail Alokasi Gaji MAINTENANCE Error : ".$e->getMessage()."\n";
                    break;
                }                 
            }
            if($detailErr=='') {
                # Header and Detail inserted
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
                $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter),
                    "kodeorg='".$kodept."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$kodeorg."' and periode='".$periode."' ");
                    try{$owlPDO->exec($updJurnal); }
                    catch (PDOException $e) {
                        echo "Update Kode Jurnal Error : ".$e->getMessage()."\n";
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                            try{$owlPDO->exec($insHead); }
                            catch (PDOException $e) {
                            echo "Rollback Delete Header Error : ".$e->getMessage()."\n";
                            exit;
                            }               
                      exit;                            
                    }                       
            } else {
                echo $detailErr;
                # Rollback, Delete Header
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($RBDet); }
                catch (PDOException $e) {
                    echo "Rollback Delete Header Error : ".$e->getMessage();
                    exit;
                }                
            }
        } else {
            echo $headErr;
            exit;
        }

	break;
	
	
	case'list':
	
		$tab="
		<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['station']."</td>
				<td align=center>".$_SESSION['lang']['jam']."</td>
				<td align=center>".$_SESSION['lang']['rupiah']."</td>
			</tr>
			</thead>
			<tbody>";
		$no = 0;
		$str = "select * from ".$dbname.".keu_jurnaldt_vw where 0=0 and 
			kodeorg='".$kodeorg."' and periode='".$periode."' and noakun like '632%' and kodejurnal!='".$kodeJurnal."' and noakun='6320108'";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			@$trupiah+=$bar['jumlah'];
		}
		
		$str = "select * from ".$dbname.".pabrik_rawatmesinht where 0=0 and 
			pabrik='".$kodeorg."' and tanggal like '".substr($periode,0,7)."%' ";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$arrkdst[$bar['statasiun']]=$bar['statasiun'];
			@$jam[$bar['statasiun']]+=$bar['jumlahjamperbaikan'];
			@$tjam+=$bar['jumlahjamperbaikan'];
		}
		
		foreach($arrkdst as $kdst){
			$no++;
			$tab.="<tr class=rowcontent id='row".$no."'>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td id=station".$no.">".$kdst."</td>";
            $tab.="<td align=right>".number_format($jam[$kdst],2)."</td>";
            $tab.="<td align=right id=jumlah".$no.">".number_format($jam[$kdst]/$tjam*$trupiah,2)."</td>";
            $tab.="</tr>";
        }
		$tab.="<tr class=rowcontent>";
            $tab.="<td align=center colspan=2>Total</td>";
            $tab.="<td align=right>".$tjam."</td>";
            $tab.="<td align=right>".number_format($trupiah,2)."</td>";
            $tab.="</tr>";
		$tab.="<button class=mybutton onclick=savemaintenance(".$no.") id=btnproses>Process</button>";
		$tab.="</table>";
	
		echo $tab;
	break;
	
    default:
}
?>
