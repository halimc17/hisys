<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$prd   = trim(checkPostGet('prd',''));
$proses= trim(checkPostGet('proses', ''));
$kdorg = trim(checkPostGet('kdorg', ''));
$kdorg0= trim(checkPostGet('kdorg0', ''));
$tgl1  = trim(checkPostGet('tgl1', ''));
$tgl2  = trim(checkPostGet('tgl2', ''));
$tipe0 = trim(checkPostGet('tipe0', ''));
$tipe1 = trim(checkPostGet('tipe1', ''));
$afdelingFilter = trim(checkPostGet('afdeling', ''));
$mandorFilter = trim(checkPostGet('mandor', ''));

if ($proses == 'excel') {
    $tbl ="<table class=sortable cellspacing=1 border=1>";
} else {
    $tbl ="<table class=sortable cellpadding=5 cellspacing=1>";
}

if ($tipe0 == '' && $tipe1 == 'persen') {

	$stream="";
	$stream.=$tbl;

	$namafile = 'Rekap Persen AKP unit '.$kdorg;
	$expbln=  explode('-', $prd);
	$tahun=$expbln[0];
	$bln=$expbln[1];

	$blawal=$tahun."-01";

	$rangebulan = month_inbetween($blawal, $prd);

	if($kdorg==''){
	    echo"Warning: Unit tidak boleh kosong"; 
	    exit;
	}else if ($prd==''){
		echo "Warning : Periode tidak boleh kosong";
		exit;
	}

	$stream.="
	    <thead>
	        <tr class=rowheader>
					<td rowspan=2 align=center width=20px >".$_SESSION['lang']['nourut']."</td>
					<td rowspan=2 align=center width=50px >".$_SESSION['lang']['divisi']."</td>
					<td rowspan=2 align=center width=50px >".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['tanam']."</td>
					<td rowspan=2 align=center width=50px >".$_SESSION['lang']['luas']." ".$_SESSION['lang']['ha']."</td>
					<td rowspan=2 align=center width=50px >".$_SESSION['lang']['pokok']."</td>
					<td colspan=".intval($bln)." align=center>".$_SESSION['lang']['luas']." ".$_SESSION['lang']['panen']."</td>
					<td colspan=".intval($bln)." align=center>".$_SESSION['lang']['jjg']." ".$_SESSION['lang']['panen']."</td>
					<td colspan=".intval($bln)." align=center>".$_SESSION['lang']['kgwb']."</td>
					<td colspan=".intval($bln)." align=center>% ".$_SESSION['lang']['kerapatan']."</td>
			</tr>";		
				$stream.="<tr>";
				foreach ($rangebulan as $listbulan ){
					$stream.="<td align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</td>";
				}
				foreach ($rangebulan as $listbulan ){
					$stream.="<td align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</td>";
				}
				foreach ($rangebulan as $listbulan ){
					$stream.="<td align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</td>";
				}
				foreach ($rangebulan as $listbulan ){
					$stream.="<td align=center>".numToMonth(intval(substr($listbulan,5,2)),'E','short')."</td>";
				}
				$stream.="</tr>";

					
	$stream.="</thead>
			<tbody>";
			#setup blok tahunan
			$str="select substr(kodeorg,1,6) as divisi, tahuntanam, sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from 
				 ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdorg."%' and tahun='".str_replace('-', '', $prd)."' group by divisi, tahuntanam";
			// exit("Error: ".$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
	        $numrows=owlBaris($res);
			//exit('Error :'.$numrows);
	        if($numrows==0){
	        	#setup blok
				$str="select substr(kodeorg,1,6) as divisi, tahuntanam, sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from 
					 ".$dbname.".setup_blok where kodeorg like '".$kdorg."%' group by divisi, tahuntanam";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
	        }

			while($bar=$res->fetch())
			{
				$kddivisi[$bar['divisi']]=$bar['divisi'];
				$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
				$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
				$luasblok[$bar['divisi']][$bar['tahuntanam']]=$bar['luas'];
				$pokokblok[$bar['divisi']][$bar['tahuntanam']]=$bar['pokok'];
			}	

			
			
			#rekappnn
			$str="select sum(luaspanen) as luaspanen, sum(jjgpanen) as jjgpanen, tahuntanam, divisi, substr(tanggal,1,7) as prd from 
				 ".$dbname.".kebun_rekappnn_vw where divisi like '".$kdorg."%' and left(tanggal,7) >= '".$tahun."-01' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam, prd";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$kddivisi[$bar['divisi']]=$bar['divisi'];
				$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
				$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
				$luaspnn[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['luaspanen'];
				$jjgpnn[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['jjgpanen'];			
			}	
			$str="select divisi, tahuntanam, sum(jjg) as jjg, sum(kgwb) as kgwb, substr(tanggal,1,7) as prd from 
				 ".$dbname.".kebun_spb_vw where divisi like '".$kdorg."%' and left(tanggal,7) >= '".$tahun."-01' and left(tanggal,7) <= '".$prd."' group by divisi, tahuntanam, prd";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$kddivisi[$bar['divisi']]=$bar['divisi'];
				$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
				$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
				$jjgkrm[$bar['divisi']][$bar['tahuntanam']]=$bar['jjg'];
				$kgwb[$bar['divisi']][$bar['tahuntanam']][$bar['prd']]=$bar['kgwb'];
			}	

	array_multisort($kddivisi,SORT_ASC);
	array_multisort($tahuntanam,SORT_ASC);

	foreach($kddivisi as $divisi)
	{
		foreach($tahuntanam as $thntnm)
		{
			if(@$listtahuntanam[$divisi][$thntnm]!=''){
			$no+=1;	
			$stream.="<tr class=rowcontent>
	            <td align=center>".$no."</td>
				<td align=center>".$divisi."</td>
				<td align=center>".$thntnm."</td>	
				<td align=right>".@number_format($luasblok[$divisi][$thntnm],2)."</td>	
				<td align=right>".@number_format($pokokblok[$divisi][$thntnm])."</td>	
				";
			foreach($rangebulan as $lstbln)
				{
				$stream.="
					    <td align=right >".@number_format($luaspnn[$divisi][$thntnm][$lstbln],2)."</td>				
					    ";
				}
			foreach($rangebulan as $lstbln)
				{
				$stream.="
					    <td align=right >".@number_format($jjgpnn[$divisi][$thntnm][$lstbln],0)."</td>				
					    ";
				}
			foreach($rangebulan as $lstbln)
				{
				$stream.="
					    <td align=right >".@number_format($kgwb[$divisi][$thntnm][$lstbln],0)."</td>				
					    ";
				}
			foreach($rangebulan as $lstbln)
				{
				$stream.="
					    <td align=right >".@number_format((($jjgpnn[$divisi][$thntnm][$lstbln]/($luaspnn[$divisi][$thntnm][$lstbln]/$luasblok[$divisi][$thntnm]))/$pokokblok[$divisi][$thntnm])*100,2)."</td>				
					    ";
				}
			@$ttlluas[$divisi]+=$luasblok[$divisi][$thntnm];
			@$ttlpkk[$divisi]+=$pokokblok[$divisi][$thntnm];
			foreach($rangebulan as $lstbln){
				@$ttlluaspnn[$divisi][$lstbln]+=$luaspnn[$divisi][$thntnm][$lstbln];
			}
			foreach($rangebulan as $lstbln){
				@$ttljjgpnn[$divisi][$lstbln]+=$jjgpnn[$divisi][$thntnm][$lstbln];
			}
			foreach($rangebulan as $lstbln){
				@$ttlkgwb[$divisi][$lstbln]+=$kgwb[$divisi][$thntnm][$lstbln];
			}
			}
		}


	$stream.="<tr bgcolor=#00BFFF  style='color:#000000'>
			<td align=left colspan=3 ><b>TOTAL ".$divisi."</b></td>
			<td align=right ><b>".@number_format($ttlluas[$divisi],2)."</b></td>
			<td align=right ><b>".@number_format($ttlpkk[$divisi])."</b></td>";
		
			foreach($rangebulan as $lstbln)
			{
			$stream.="<td align=right ><b>".@number_format($ttlluaspnn[$divisi][$lstbln],2)."</b></td>";
			}	
			
			foreach($rangebulan as $lstbln)
			{
			$stream.="<td align=right ><b>".@number_format($ttljjgpnn[$divisi][$lstbln])."</b></td>";
			}
			foreach($rangebulan as $lstbln)
			{
			$stream.="<td align=right ><b>".@number_format($ttlkgwb[$divisi][$lstbln])."</b></td>";
			}
			foreach($rangebulan as $lstbln)
			{
			$stream.="<td align=right ><b>".@number_format(($ttljjgpnn[$divisi][$lstbln]/($ttlluaspnn[$divisi][$lstbln]/$ttlluas[$divisi])/$ttlpkk[$divisi])*100,2)."</b></td>";
			}
			
		@$gtluas+=$ttlluas[$divisi];
		@$gtpkk+=$ttlpkk[$divisi];
		foreach($rangebulan as $lstbln){
				@$gtluaspnn[$lstbln]+=$ttlluaspnn[$divisi][$lstbln];
			}
		foreach($rangebulan as $lstbln){
				@$gtjjgpnn[$lstbln]+=$ttljjgpnn[$divisi][$lstbln];
			}
		foreach($rangebulan as $lstbln){
				@$gtkgwb[$lstbln]+=$ttlkgwb[$divisi][$lstbln];
			}		
	}
	$stream.="<tr bgcolor=#1E90FF   style='color:#000000'>
			<td align=left colspan=3 ><b>GRAND TOTAL</b></td>
			<td align=right ><b>".@number_format($gtluas,2)."</b></td>
			<td align=right ><b>".@number_format($gtpkk,0)."</b></td>";
			foreach($rangebulan as $lstbln)
			{
				$stream.="<td align=right ><b>".@number_format($gtluaspnn[$lstbln],2)."</b></td>";
			}
			foreach($rangebulan as $lstbln)
			{
				$stream.="<td align=right ><b>".@number_format($gtjjgpnn[$lstbln])."</b></td>";
			}
			foreach($rangebulan as $lstbln)
			{
				$stream.="<td align=right ><b>".@number_format($gtkgwb[$lstbln])."</b></td>";
			}
			foreach($rangebulan as $lstbln)
			{
				$stream.="<td align=right ><b>".@number_format((($gtjjgpnn[$lstbln]/($gtluaspnn[$lstbln]/$gtluas))/$gtpkk)*100,2)."</b></td>";
			}
			
	$stream.="</tbody>";
} else if ($tipe1 == '' && $tipe0 == 'laporan') {
	
	//$stream="<div class='table-scroll' overflow-x:hidden'>";
	$stream.=$tbl;
	$namafile = 'Laporan AKP unit '.$kdorg0;

	if($kdorg0=='')
	{
	    echo"Warning: Unit tidak boleh kosong"; 
	    exit;
	}
	else if ($tgl1 == '' || $tgl2 == '') {
		echo "Warning : Periode tidak boleh kosong";
		exit;
	}
	else if ($tgl2 < $tgl1) {
		echo "Warning : Periode sampai tidak boleh lebih kecil dari Periode awal";
		exit;
	}
	else if (substr($tgl1,3,7) != substr($tgl2,3,7)) {
		echo "Warning : Bulan dan Tahun antar periode harus sama";
		exit;
	}

	$stream.="<thead>
				<tr class=rowheader>
					<th colspan=16 align=center>BUDGET</th>
				</tr>
				<tr class=rowheader>
					<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['kebun']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['blok']."</th>
                    <th align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</th>
					<th align=center colspan=2>".$_SESSION['lang']['luas']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['sph']."</th>
                    <th align=center rowspan=2>".$_SESSION['lang']['pokok']."</th>
                    <th align=center rowspan=2>HK</th>
					<th align=center rowspan=2>".$_SESSION['lang']['jjg']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['bjr']."</th>
                    <th align=center rowspan=2>TONASE</th>
					<th align=center rowspan=2>".$_SESSION['lang']['mandor']."</th>
					<th align=center rowspan=2>AKP (%)</th>
				</tr>
				<tr class=rowheader>
					<th align=center>".$_SESSION['lang']['blok']."</th>
					<th align=center>".$_SESSION['lang']['panen']."</th>
					
				</tr>
			</thead>
			<tbody>";

	$where = "tanggal BETWEEN '".tanggalsystemn($tgl1)."' AND '".tanggalsystemn($tgl2)."'";
    if($afdelingFilter != '' && $afdelingFilter != '0' && $afdelingFilter != 'null') {
        $where .= " AND afdeling = '".$afdelingFilter."'";
    } else {
        $where .= " AND afdeling like '".$kdorg0."%'";
    }
    if($mandorFilter != '' && $mandorFilter != '0' && $mandorFilter != 'null') $where .= " AND mandor = '".$mandorFilter."'";

	$str = "SELECT t.*, t.blok as kodeorg, b.tahuntanam, b.luasareaproduktif as luasblok, b.jumlahpokok 
            FROM ".$dbname.".kebun_taksasi t
            LEFT JOIN ".$dbname.".setup_blok b ON t.blok = b.kodeorg
			WHERE ".$where." ORDER BY t.tanggal asc, t.afdeling asc";

	// $str = "SELECT t.*, t.blok as kodeorg, b.tahuntanam, b.luasblok, b.jumlahpokok 
    //         FROM ".$dbname.".kebun_taksasi t
    //         LEFT JOIN (
    //             SELECT indukblok, MIN(tahuntanam) as tahuntanam, 
    //             SUM(luasareaproduktif) as luasblok, SUM(jumlahpokok) as jumlahpokok 
    //             FROM ".$dbname.".setup_blok 
    //             GROUP BY indukblok
    //         ) b ON t.blok = b.indukblok
	// 		WHERE ".$where." ORDER BY t.tanggal asc, t.afdeling asc";

	$res = fetchdata($str);
    foreach($res as $val) {
		@$blk[$val['tanggal']][$val['kodeorg']] = $val['kodeorg'];
		@$tanggal[$val['tanggal']][$val['kodeorg']] = $val['tanggal'];
		@$divisi[$val['tanggal']][$val['kodeorg']] = $val['afdeling'];
		@$mandor[$val['tanggal']][$val['kodeorg']] = $val['mandor'];
		@$jjg[$val['tanggal']][$val['kodeorg']] = $val['jjgmasak'];
		@$bjr[$val['tanggal']][$val['kodeorg']] = $val['bjr'];
		@$hitk[$val['tanggal']][$val['kodeorg']] = $val['hkdigunakan'];
		@$akp[$val['tanggal']][$val['kodeorg']] = $val['persenbuahmatang'];
		@$haesok[$val['tanggal']][$val['kodeorg']] = $val['haesok'];
        @$tt[$val['tanggal']][$val['kodeorg']] = $val['tahuntanam'];
        @$mature[$val['kodeorg']] = $val['luasblok'];
        @$jumlahpokok[$val['kodeorg']] = $val['jumlahpokok'];
	}

	// echo"<pre>";
	// print_r($hitk);
	// echo"</pre>";
	$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	
	if (count($res) < 1) {
		$stream.="<tr class=rowcontent>
					<td align=center colspan=16><b>".$_SESSION['lang']['errdatanotexist']."</b></td>
				 </tr>";
	} else {
		foreach ($blk as $tgl => $v1){
			foreach ($v1 as $blok){
			
				$phpdate = strtotime($tanggal[$tgl][$blok]);
				$mysqldate = date('d-m-Y', $phpdate);

				$sph = fixnan($jumlahpokok[$blok]/$mature[$blok]);
                $tonase = $jjg[$tgl][$blok] * $bjr[$tgl][$blok];

				@$no += 1;
				$stream.="<tr class=rowcontent>
							<td align=center>".$no."</td>
							<td>".$mysqldate."</td>
							<td>".$kdorg0."</td>
							<td>".$divisi[$tgl][$blok]."</td>
							<td align=center>".substr($blok, -3)."</td>
                            <td align=center>".$tt[$tgl][$blok]."</td>
							<td align=right>".number_format($mature[$blok],2)."</td>
							<td align=right>".number_format($haesok[$tgl][$blok],2)."</td>
							<td align=right>".number_format($sph)."</td>
                            <td align=right>".number_format($jumlahpokok[$blok])."</td>
                            <td align=right>".number_format($hitk[$tgl][$blok],2)."</td>
							<td align=right>".number_format($jjg[$tgl][$blok])."</td>
							<td align=right>".number_format($bjr[$tgl][$blok],2)."</td>
                            <td align=right>".number_format($tonase,2)."</td>
							<td>".getNamaKaryawan($mandor[$tgl][$blok])."</td>
							<td align=right>".number_format($akp[$tgl][$blok],2)." %</td>
						 </tr>";
			}
		}
	}

	$stream.="</tbody></table><br /><br />";
	
	$stream.=$tbl."<thead>
				<tr class=rowheader>
					<th colspan=9 align=center>REALISASI</th>
				</tr>
				<tr class=rowheader>
					<th align=center>".$_SESSION['lang']['tahuntanam']."</th>
					<th align=center>".$_SESSION['lang']['blok']."</th>
					<th align=center>".$_SESSION['lang']['luas']."</th>
					<th align=center>".$_SESSION['lang']['pokok']."</th>
					<th align=center>HK</th>
					<th align=center>".$_SESSION['lang']['jjg']."</th>
					<th align=center>".$_SESSION['lang']['bjr']."</th>
					<th align=center>TONASE</th>
					<th align=center>AKP (%)</th>
				</tr>
			</thead>
			<tbody>";

    $dt1 = substr($tgl1,6,4).substr($tgl1,3,2).substr($tgl1,0,2);
    $dt2 = substr($tgl2,6,4).substr($tgl2,3,2).substr($tgl2,0,2);
    $prd = substr($tgl1,6,4)."-".substr($tgl1,3,2);
	// exit('warning: '.$prd);

	$strA = "SELECT a.kodeorg, a.tahuntanam, b.bjr,
	         SUM(a.hasilkerja) as jjg, 
	         SUM(a.luaspanen) as luas, 
	         SUM(a.jumlahhk) as hk
	         FROM ".$dbname.".kebun_prestasi_detail a
			 INNER JOIN ".$dbname.".kebun_5bjr b ON a.kodeorg = b.kodeorg AND b.periode = '".$prd."'
	         WHERE a.notransaksi LIKE '%PNN%' 
             AND substring(a.notransaksi,1,8) BETWEEN '".$dt1."' AND '".$dt2."'";
	// $strA = "SELECT a.kodeorg, a.tahuntanam, b.bjr,
	//          SUM(a.hasilkerja) as jjg, 
	//          SUM(a.luaspanen) as luas, 
	//          SUM(a.jumlahhk) as hk
	//          FROM ".$dbname.".kebun_prestasi_detail a
	//          INNER JOIN ".$dbname.".kebun_5bjr b ON a.kodeorg = b.kodeorg AND b.tahunproduksi = '".substr($tgl1,6,4)."'
	//          WHERE a.notransaksi LIKE '%PNN%' 
    //          AND substring(a.notransaksi,1,8) BETWEEN '".$dt1."' AND '".$dt2."'";
	// exit('warning: '.$strA);

	if($afdelingFilter != '' && $afdelingFilter != '0' && $afdelingFilter != 'null') {
	    $strA .= " AND a.kodeorg LIKE '".$afdelingFilter."%'";
	} else {
	    $strA .= " AND a.kodeorg LIKE '".$kdorg0."%'";
	}

	$strA .= " GROUP BY a.kodeorg, a.tahuntanam, b.bjr ORDER BY a.kodeorg ASC";

    // exit('Warning: '.$strA);

	$resA = fetchdata($strA);

    $sBlokDefault = "SELECT kodeorg, luasareaproduktif, jumlahpokok FROM ".$dbname.".setup_blok WHERE kodeorg LIKE '".$kdorg0."%'";
    $resBlokDefault = fetchdata($sBlokDefault);
    $dBlokDefault = array();
    foreach($resBlokDefault as $bDf) {
        $dBlokDefault[$bDf['kodeorg']]['luas'] = $bDf['luasareaproduktif'];
        $dBlokDefault[$bDf['kodeorg']]['pokok'] = $bDf['jumlahpokok'];
    }

	if(count($resA) < 1) {
		$stream.="<tr class=rowcontent>
					<td align=center colspan=9><b>".$_SESSION['lang']['errdatanotexist']."</b></td>
				 </tr>";
	} else {
		foreach($resA as $bar) {
            $tt_real   = $bar['tahuntanam'];
            $luas_real = $bar['luas'];
            $jjg_real  = $bar['jjg'];
            $hk_real   = $bar['hk'];
            $bjr_real  = $bar['bjr'];
            
            $luas_def  = @$dBlokDefault[$bar['kodeorg']]['luas'];
            $pokok_def = @$dBlokDefault[$bar['kodeorg']]['pokok'];
            
            // Pokok = Pokok Setup Blok * (Luas Panen / Luas Default)
            $jmlpokok_real = 0;
            if($luas_def > 0) {
                $jmlpokok_real = $pokok_def * ($luas_real / $luas_def);
            }
            
            // Akp = JJG / Jumlah Pokok Real
            $akp_real = 0;
            if($jmlpokok_real > 0) {
                $akp_real = ($jjg_real / $jmlpokok_real) * 100;
            }
            
            $tonase_real = $jjg_real * $bjr_real;

			$stream.="<tr class=rowcontent>
						<td align=center>".$tt_real."</td>
						<td align=center>".$bar['kodeorg']."</td>
						<td align=right>".number_format($luas_real, 2)."</td>
						<td align=right>".number_format($jmlpokok_real, 0)."</td>
						<td align=right>".number_format($hk_real, 2)."</td>
						<td align=right>".number_format($jjg_real, 0)."</td>
						<td align=right>".number_format($bjr_real, 2)."</td>
						<td align=right>".number_format($tonase_real, 2)."</td>
						<td align=right>".number_format($akp_real, 2)." %</td>
					 </tr>";
		}
	}

	$stream.="</tbody></table>";

}


switch ($proses) {
    case 'preview':
        echo $stream;
        break;

    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = $namafile;
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