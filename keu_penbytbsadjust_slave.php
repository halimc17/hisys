<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$supplier = checkPostGet('supplier', '');
$jurnal = checkPostGet('jurnal', '');
$jurnalbalik = checkPostGet('jurnalbalik', '');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));
$no=0;

$kodesupplier = checkPostGet('kodesupplier', '');
$notransaksi = checkPostGet('notransaksi', '');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
$hargabudget = checkPostGet('hargabudget', '');
$hargarealisasi = checkPostGet('hargarealisasi', '');
$hargasatuanrealisasi = checkPostGet('hargasatuanrealisasi', '');
$selisih = checkPostGet('selisih', '');

$arrnmsupp = array();
$arrnmasignment = array();
$arrnmorg = array();
$str="select a.namasupplier,a.supplierid from ".$dbname.".log_5supplier a
left join ".$dbname.".log_5supkelompok b on a. supplierid=b.supplierid
where b.tipe in ('SUPPLIERTBS','SUPPLIERTBSKUD')";
/*$str="select a.namasupplier,b.kodetimbangan,a.supplierid from ".$dbname.".log_5supplier a
left join ".$dbname.".log_5suptimbangan b on a.supplierid=b.supplierid
left join ".$dbname.".log_5supkelompok c on a. supplierid=c.supplierid
where c.tipe in ('SUPPLIERTBS','SUPPLIERTBSKUD')";*/
$res=fetchdata($str);
foreach($res as $val)
{
	$arrnmasignment[$val['supplierid']] = $val['namasupplier'];
}


$str="select * from ".$dbname.".organisasi
where tipe in ('KEBUN')";
$res=fetchdata($str);
foreach($res as $val)
{
	$arrnmasignment[$val['kodeorganisasi']] = $val['namaorganisasi'];
}

$arrposting=array("0"=>"&#10006","1"=>"&#10004");

$digit=2;

switch ($method) {
	case'detail':
	
	
		$form.="<table cellpading=1 cellspacing=1 border=0 class=sortable>";
		$form.="<thead>";
		$form.="<table cellpading=1 cellspacing=1 border=0 class=sortable>";
		$form.="<thead>";
		$form.="<tr class=rowheader>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['supplier']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['beratBersih']."<br>(Kg)</td>";
			$form.="<td align=center colspan=3>Accrual</td>";
			$form.="<td align=center colspan=3>".$_SESSION['lang']['realisasi']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['selisih']."</td>";
			
		$form.="</tr>";
		$form.="<tr class=rowheader>";
		for($i=1;$i<=2;$i++){
			$form.="<td align=center>".$_SESSION['lang']['hargasatuan']."</td>";
			$form.="<td align=center>".$_SESSION['lang']['total']."</td>";
			$form.="<td align=center>".$_SESSION['lang']['jurnal']."</td>";
		}
		
		$form.="</thead>";
		$tmpnourutdt='';
	
		if($supplier!=''){
			$where.=" and kodesupplier='".$supplier."'";
		}
	
		$str="SELECT notransaksi,tanggal,updateby,postingby,sum(rupiahjurnal) as totbyr,sum(totalrupiah) as rp,sum(totalrupiahbonus) as rpbonus,
			  sum(rupiahpajakditanggung) as rppajakdtngg,sum(rupiahpajaktdkditanggung) as rppajaktdkdtngg,
			  kodeunit,jurnal,jurnalbalik,sum(total_terima) as kgtbs,kodesupplier,harga_perkg,subsidi,beban_pajak,persenpajak,bonus_perkg,pengajuanbonus
              from ".$dbname.".keu_persediaantbs_vw where jurnalbalik=0 and jurnal=1 
			  and kodeunit='".$unit."' and tanggal between '".$tgl1."' and '".$tgl2."' ".$where."
			  group by notransaksi order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// $kodesup[$bar['kodesupplier']]=$bar['kodesupplier'];
			$notransaksi[$bar['notransaksi']]=$bar['notransaksi'];
			$kodesup[$bar['notransaksi']]=$bar['kodesupplier'];
			$kgtbs[$bar['notransaksi']]=$bar['kgtbs'];
			$tgl[$bar['notransaksi']]=$bar['tanggal'];
			$harga_perkg[$bar['notransaksi']]=$bar['harga_perkg'];
			$rp[$bar['notransaksi']]=$bar['rp'];
			$jurnal[$bar['notransaksi']]=$bar['jurnal'];
			$jurnalbalik[$bar['notransaksi']]=$bar['jurnalbalik'];
			
			
		}
		
	
		foreach($notransaksi as $notran){
			$no++;
			$hargarealisasi=0;
			
			#= query harga realisasi
			// $str="SELECT * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' 
				// and tanggal <= '".$tgl[$notran]."' and supplierid='".$kodesup[$notran]."'
			   // order by tanggal desc limit 1";
			   // // echo $str;
			// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// $bar=$res->fetch();
				// $hargarealisasi=$bar['harga'];
				
			$hargarealisasi=1300;	
			
			$form.="<tr class=rowcontent id=row".$no.">";
				$form.="<td align=left id=notransaksi".$no.">".$notran."</td>";
				$form.="<td align=left id=kodesupplier".$no." hidden>".$kodesup[$notran]."</td>";
					$form.="<td align=left>".$arrnmasignment[$kodesup[$notran]]."</td>";
				$form.="<td align=left id=tanggal".$no.">".tanggalnormal($tgl[$notran])."</td>";
				$form.="<td align=right>".number_format($kgtbs[$notran],$digit)."</td>";
				$form.="<td align=right>".number_format($harga_perkg[$notran],$digit)."</td>";
				$form.="<td align=right id=hargabudget".$no.">".number_format($rp[$notran],$digit)."</td>";
				$form.="<td align=center>".$arrposting[$jurnal[$notran]]."</td>";
				
				$form.="<td align=right id=hargasatuanrealisasi".$no.">".number_format($hargarealisasi,$digit)."</td>";
				$hargatotal[$notran]=$hargarealisasi*$kgtbs[$notran];
				$selisih[$notran]=$hargatotal[$notran]-$rp[$notran];
				$form.="<td align=right id=hargarealisasi".$no.">".number_format($hargatotal[$notran],$digit)."</td>";
				
				$form.="<td align=center hidden id=jurnal".$no.">".$jurnalbalik[$notran]."</td>";
					$form.="<td align=center>".$arrposting[$jurnalbalik[$notran]]."</td>";
				$form.="<td align=right id=selisih".$no.">".number_format($selisih[$notran],$digit)."</td>";
			$form.="</tr>";
			
		}
		
		$form.="<button class=mybutton onclick=saveall('".$no."')>".$_SESSION['lang']['proses']."</button>";
		
		echo $form;
		
	break;
		
	
	
	
	case'loaddata':
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$where="";
		
		if($tgl1=='--'){
			$tgl1='';
		}
		if($tgl2=='--'){
			$tgl2='';
		}
		
		if($unit!=''){ 
			$where.=" and kodeunit='".$unit."'";
		}
		if($supplier!=''){ 
			$where.=" and kodesupplier='".$supplier."'";
		}
		
		if($tgl1!='' and $tgl2!=''){ 
			$where.=" and tanggal between '".$tgl1."' and '".$tgl2."' ";
		}
		if($jurnal!=''){ 
			$where.=" and jurnal='".$jurnal."'";
		}
		if($jurnalbalik!=''){ 
			$where.=" and jurnalbalik='".$jurnalbalik."'";
		}
		
		$str="SELECT count(notransaksi) as jmlhrow
              from ".$dbname.".keu_persediaantbs_vw where 1=1 ".$where." 
			  group by notransaksi order by tanggal asc";
        $query2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$form.="<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
		$form.="<thead>";
		$form.="<tr class=rowheader>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['supplier']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['beratBersih']." <br>(Kg)</td>";
			$form.="<td align=center colspan=3>Accrual</td>";
			$form.="<td align=center colspan=3>".$_SESSION['lang']['realisasi']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['selisih']."</td>";
			// $form.="<td align=center rowspan=2>".$_SESSION['lang']['jurnal']."</td>";
			
		$form.="</tr>";
		$form.="<tr class=rowheader>";
		for($i=1;$i<=2;$i++){
			$form.="<td align=center>".$_SESSION['lang']['hargasatuan']."</td>";
			$form.="<td align=center>".$_SESSION['lang']['total']."</td>";
			$form.="<td align=center>".$_SESSION['lang']['jurnal']."</td>";
		}
		
			
		$form.="</tr>";
		$form.="</thead>";
		$form.="</thead><tbody>";
		$no = 0;
		//kodeunit='".$unit."'
		
		$str="SELECT notransaksi,tanggal,updateby,postingby,sum(rupiahjurnal) as totbyr,sum(totalrupiah) as rp,sum(totalrupiahbonus) as rpbonus,
			  sum(rupiahpajakditanggung) as rppajakdtngg,sum(rupiahpajaktdkditanggung) as rppajaktdkdtngg,
			  kodeunit,jurnal,jurnalbalik,sum(total_terima) as kgtbs,kodesupplier,harga_perkg,subsidi,beban_pajak,
			  sum(totalrupiahrealisasi) as totalrupiahrealisasi,harga_perkgrealisasi,
			  persenpajak,bonus_perkg,pengajuanbonus
              from ".$dbname.".keu_persediaantbs_vw where 1=1  ".$where." 
			  group by notransaksi order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// $kodesup[$bar['kodesupplier']]=$bar['kodesupplier'];
			$notransaksi[$bar['notransaksi']]=$bar['notransaksi'];
			$kodesup[$bar['notransaksi']]=$bar['kodesupplier'];
			$kgtbs[$bar['notransaksi']]=$bar['kgtbs'];
			$tgl[$bar['notransaksi']]=$bar['tanggal'];
			$harga_perkg[$bar['notransaksi']]=$bar['harga_perkg'];
			$harga_perkgrealisasi[$bar['notransaksi']]=$bar['harga_perkgrealisasi'];
			$rp[$bar['notransaksi']]=$bar['rp'];
			$totalrupiahrealisasi[$bar['notransaksi']]=$bar['totalrupiahrealisasi'];
			$jurnal[$bar['notransaksi']]=$bar['jurnal'];
			$jurnalbalik[$bar['notransaksi']]=$bar['jurnalbalik'];
		}
		
		foreach($notransaksi as $notran){
			$no++;
		
			
			$form.="<tr class=rowcontent>";
				$form.="<td align=left>".$notran."</td>";
				$form.="<td align=left>".$arrnmasignment[$kodesup[$notran]]."</td>";
				$form.="<td align=left>".tanggalnormal($tgl[$notran])."</td>";
				$form.="<td align=right>".number_format($kgtbs[$notran],$digit)."</td>";
				$form.="<td align=right>".number_format($harga_perkg[$notran],$digit)."</td>";
				$form.="<td align=right>".number_format($rp[$notran],$digit)."</td>";
				$form.="<td align=center>".$arrposting[$jurnal[$notran]]."</td>";
				
				$form.="<td align=right>".number_format($harga_perkgrealisasi[$notran],$digit)."</td>";
				
				$form.="<td align=right>".number_format($totalrupiahrealisasi[$notran],$digit)."</td>";
				$form.="<td align=center>".$arrposting[$jurnalbalik[$notran]]."</td>";
				$selisih[$notran]=$totalrupiahrealisasi[$notran]-$rp[$notran];
				$form.="<td align=right>".number_format($selisih[$notran],$digit)."</td>";
			$form.="</tr>";
			
		}
		
		
		
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0) {
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++) {
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$form.="<tr><td colspan=11 align=center>";
			$form.="<button class=mybutton onclick=loaddatamaster(".($page-1).");>Prev</button>";
			$form.="<select id=\"pages\" name=\"pages\" onchange=\"getPagemaster(this.value)\">".$isiRow."</select>";
			$form.="<button class=mybutton onclick=loaddatamaster(".($page+1).");>Next</button>";
		$form.="</td></tr>";
	
		echo $form;
		
	break;
	
	
	
	
	case'savedata':
	
	
		#= insert data
		// if($posting==0){
			
			#= update nilai dt
			$str="select * from ".$dbname.".keu_persediaantbs_dt where notransaksi='".$notransaksi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$str="update ".$dbname.".keu_persediaantbs_dt set harga_perkgrealisasi='".$hargasatuanrealisasi."',
						totalrupiahrealisasi='".($hargasatuanrealisasi*$bar['total_terima'])."' 
						where notransaksi='".$notransaksi."' and klasifikasi='".$bar['klasifikasi']."'";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
			
			
			#= update flag ht
			$str="update ".$dbname.".keu_persediaantbs_ht set jurnalbalik='1' where notransaksi='".$notransaksi."'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		
		
			#= insert jurnal
			
			$kodejurnal="INVTB";
			$optInduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
			$whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".$optInduk[$unit]."'";
			$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNoindukph);
			$noKon = fetchData($query);
			$tmpC = $noKon[0]['nokounter'];
			$tmpC++;
			$counterjurnal = addZero($tmpC,3);
			$nojurnal = str_replace('-','',$tanggal)."/".$unit."/".$kodejurnal."/".$counterjurnal;
			

			
			#akun debet = acrrual
			#= akun kredit = 
			$str = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',"jurnalid='".$kodejurnal."' and aktif=1");
			$res = fetchData($str );
			$akunaccrual=$res[0]['noakunkredit'];
			$akunselisih=$res[0]['noakundebet'];
			
			
			#= akun kredit
			$str = selectQuery($dbname,'log_5supkelompok','noakun',"tipe like 'SUPPLIERTBS%' and supplierid='".$kodesupplier."'");
			// exit("Error:$str");
			$res = fetchData($str);
			$akunhutang=$res[0]['noakun'];
			
			
			
			
			

			$optsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$kodesupplier."'");
			$namasupplier = $optsup[$kodesupplier];
			
			
			// exit("Error:".$namasupplier);
			
			$dataRes['header'] = array();
			$dataRes['detail'] = array();

			# Prep Header
			$dataRes['header'] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnal,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>'0',
				'totalkredit'=>'0',
				'amountkoreksi'=>'0',
				'noreferensi'=>$notransaksi,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);
			
			#= debet accrual
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>1,
				'noakun'=>$akunaccrual,
				'keterangan'=>'Accrual Penerimaan TBS dari supplier '.$namasupplier.', pada tanggal : '.tanggalnormal($tanggal).', No Transaksi :'.$notransaksi,
				'jumlah'=>$hargabudget,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$kodesupplier,
				'noreferensi'=>$notransaksi,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => '0000000001');
				
			#= kredit hutang supplier	
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>2,
				'noakun'=>$akunhutang,
				'keterangan'=>'Hutang Supplier Penerimaan TBS dari supplier '.$namasupplier.', pada tanggal : '.tanggalnormal($tanggal).', No Transaksi :'.$notransaksi,
				'jumlah'=>$hargarealisasi*-1,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$kodesupplier,
				'noreferensi'=>$notransaksi,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => '0000000001'
			);
			
			#= selisih bisa debet / kredit
			$dataRes['detail'][] = array(
				'nojurnal'=>$nojurnal,
				'tanggal'=>$tanggal,
				'nourut'=>3,
				'noakun'=>$akunselisih,
				'keterangan'=>'Jurnal selisih Penerimaan TBS dari supplier '.$namasupplier.', pada tanggal : '.tanggalnormal($tanggal).', No Transaksi :'.$notransaksi,
				'jumlah'=>$selisih,
				'matauang'=>'IDR',
				'kurs'=>'1',
				'kodeorg'=>$unit,
				'kodekegiatan'=>'',
				'kodeasset'=>'',
				'kodebarang'=>'',
				'nik'=>'',
				'kodecustomer'=>'',
				'kodesupplier'=>$kodesupplier,
				'noreferensi'=>$notransaksi,
				'noaruskas'=>'',
				'kodevhc'=>'',
				'nodok'=>'',
				'kodeblok'=>'',
				'revisi'=>'0',
				'kodesegment' => '0000000001'
			);
			
			$errorDB = "";
			# Header
			$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			try{$owlPDO->exec($queryH); }catch (PDOException $e) {$errorDB .= "Gagal :Header :". $e->getMessage() ; }
			# Detail
			if($errorDB=='') {
				foreach($dataRes['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					try{$owlPDO->exec($queryD); }catch (PDOException $e) {$errorDB .= "Gagal :Detail: ".$key." ". $e->getMessage()."\n".$queryD ; }
				}
				
				$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpC+1),
				$whereNoindukph);
				$errCounter = "";
				try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }

				if($errCounter!="") {
					$queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),
						$whereNoindukph);
					$errCounter = "";
					try{$owlPDO->exec($queryJRB); }catch (PDOException $e) { $errorJRB .= "Rollback Parameter Jurnal Error :". $e->getMessage() ; }
					echo "DB Error :\n".$errorJRB;
					exit;
				}
			}
			if($errorDB!=''){
				$sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
				try{$owlPDO->exec($sDel); }catch (PDOException $e) { $errorJRB .= "Rollback Parameter Jurnal Error :". $e->getMessage() ; }
				echo "DB Error :\n".$errorDB;
				exit();
			}
		// }
	break;
	
	
	
	
	case'':
	
	break;
	

	
}
?>