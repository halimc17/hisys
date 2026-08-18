<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$tahun = checkPostGet('tahun0','');
$kebun = checkPostGet('kebun0','');
$afdeling = checkPostGet('afdeling0','');
$tipe = checkPostGet('tipe0','');
$tahuntanam = checkPostGet('tahuntanam0','');

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$optIP = array('I'=>'Inti','P'=>'Plasma');

function putertanggal($tgl){
    $qwe=explode("-",$tgl);
    return $qwe[2]."-".$qwe[1]."-".$qwe[0];
}

if($proses=='preview'||$proses=='excel'||$proses=='pdf'){
    if($kebun==''){
        exit("Error: All field required");
    }

    $tahunini=date("Y");
    
    @$tahunlalu=$tahun-1;
	$where="";
    #ambil data pokok awal tahun
	if ($afdeling == '') {
		$where = " and substr(kodeorg,1,6) in (".getOrgDetail(26).")";
	} else {
		$where = " and kodeorg like '".$afdeling."%'";
	}
    $sWhat="select kodeorg, tahuntanam, kelaspohon, luasareaproduktif, luasareanonproduktif, jumlahpokok, statusblok, 
            cadangan, okupasi, rendahan, sungai, rumah, kantor, pabrik, jalan, kolam, umum, lc, luasbloking
        from ".$dbname.".setup_blok_tahunan 
        where kodeorg like '".$kebun."%' ".$where." and tahuntanam like '".$tahuntanam."%'
            and (luasareaproduktif+luasareanonproduktif)>0 and tahun = '".$tahunlalu."12'";  
		//	echo $sWhat;
	$rWhat=$owlPDO->query($sWhat) or die(print " Gagal: ".PDOException::getMessage());
	$rWhat->setFetchMode(PDO::FETCH_OBJ);
    
    while($bWhat=$rWhat->fetch()){
        $dzData[$bWhat->kodeorg]['jumlahpokok-1']=$bWhat->jumlahpokok;
    }    
    
    #ambil data pokok tahun yang dipilih
	$where2="";
	if ($afdeling == '') {
		$where2 = " and substr(kodeorg,1,6) in (".getOrgDetail(26).")";
	} else {
		$where2 = " and kodeorg like '".$afdeling."%'";
	}
    $sWhat="select kodeorg, tahuntanam, kelaspohon, luasareaproduktif, luasareanonproduktif, jumlahpokok, statusblok, 
            cadangan, okupasi, rendahan, sungai, rumah, kantor, pabrik, jalan, kolam, umum, intiplasma, jenisbibit, lc, luasbloking
        from ".$dbname.".setup_blok_tahunan 
        where kodeorg like '".$kebun."%' ".$where2." and tahuntanam like '".$tahuntanam."%'
            and (luasareaproduktif+luasareanonproduktif)>0 and tahun like '".$tahun."%'";  
	$rWhat=$owlPDO->query($sWhat) or die(print " Gagal: ".PDOException::getMessage());
	$rWhat->setFetchMode(PDO::FETCH_OBJ);
    while($bWhat=$rWhat->fetch()){
        @$luaskerangka=$bWhat->luasareaproduktif+$bWhat->luasareanonproduktif;
        $tTanam[$bWhat->tahuntanam]=$bWhat->tahuntanam;
        $kOrgan[$bWhat->kodeorg]=$bWhat->kodeorg;
        $kDiv[substr($bWhat->kodeorg,0,6)]=substr($bWhat->kodeorg,0,6);
		$dzData2[substr($bWhat->kodeorg,0,6)]=substr($bWhat->kodeorg,0,6);
        $dzData[$bWhat->kodeorg]['kodeorg']=$bWhat->kodeorg;
        $dzData[$bWhat->kodeorg]['tahuntanam']=$bWhat->tahuntanam;
        $dzData[$bWhat->kodeorg]['kelaspohon']=$bWhat->kelaspohon;
		$dzData[$bWhat->kodeorg]['intiplasma']=$optIP[$bWhat->intiplasma];
        $dzData[$bWhat->kodeorg]['luaskerangka']=$luaskerangka;
        $dzData[$bWhat->kodeorg]['luasareaproduktif']=$bWhat->luasareaproduktif;
        $dzData[$bWhat->kodeorg]['luasareanonproduktif']=$bWhat->luasareanonproduktif;
        $dzData[$bWhat->kodeorg]['jumlahpokok']=$bWhat->jumlahpokok;
        $dzData[$bWhat->kodeorg]['statusblok']=$bWhat->statusblok;
        $dzData[$bWhat->kodeorg]['jenisbibit']=$bWhat->jenisbibit;
        $dzData[$bWhat->kodeorg]['cadangan']=$bWhat->cadangan;
        $dzData[$bWhat->kodeorg]['okupasi']=$bWhat->okupasi;
        $dzData[$bWhat->kodeorg]['rendahan']=$bWhat->rendahan;
        $dzData[$bWhat->kodeorg]['sungai']=$bWhat->sungai;
        $dzData[$bWhat->kodeorg]['rumah']=$bWhat->rumah;
        $dzData[$bWhat->kodeorg]['kantor']=$bWhat->kantor;
        $dzData[$bWhat->kodeorg]['pabrik']=$bWhat->pabrik;
        $dzData[$bWhat->kodeorg]['jalan']=$bWhat->jalan;
        $dzData[$bWhat->kodeorg]['kolam']=$bWhat->kolam;
        $dzData[$bWhat->kodeorg]['umum']=$bWhat->umum;
        $dzData[$bWhat->kodeorg]['lc']=$bWhat->lc;
        $dzData[$bWhat->kodeorg]['luasbloking']=$bWhat->luasbloking;
        $dzData[$bWhat->kodeorg]['luasareanonproduktifhitung']=$bWhat->cadangan+$bWhat->okupasi+$bWhat->rendahan+$bWhat->sungai+$bWhat->rumah+
            $bWhat->kantor+$bWhat->pabrik+$bWhat->jalan+$bWhat->kolam+$bWhat->umum;
    }        
    
    if($tahun==$tahunini){
		$where2="";
		if ($afdeling == '') {
			$where2 = " and substr(kodeorg,1,6) in (".getOrgDetail(26).")";
		} else {
			$where2 = " and kodeorg like '".$afdeling."%'";
		}
        #ambil data pokok teraktual (setup blok) jika yang dipilih adalah tahun ini
        $sWhat="select kodeorg, tahuntanam, kelaspohon, luasareaproduktif, luasareanonproduktif, jumlahpokok, statusblok, 
                cadangan, okupasi, rendahan, sungai, rumah, kantor, pabrik, jalan, kolam, umum, intiplasma, jenisbibit, lc, luasbloking
            from ".$dbname.".setup_blok 
            where kodeorg like '".$kebun."%' ".$where2." and tahuntanam like '".$tahuntanam."%'
                and (luasareaproduktif+luasareanonproduktif)>0";        


				
		$rWhat=$owlPDO->query($sWhat) or die(print " Gagal: ".PDOException::getMessage());
		$rWhat->setFetchMode(PDO::FETCH_OBJ);
        while($bWhat=$rWhat->fetch()){
            @$luaskerangka=$bWhat->luasareaproduktif+$bWhat->luasareanonproduktif;
            $tTanam[$bWhat->tahuntanam]=$bWhat->tahuntanam;
            $kOrgan[$bWhat->kodeorg]=$bWhat->kodeorg;
			$kDiv[substr($bWhat->kodeorg,0,6)]=substr($bWhat->kodeorg,0,6);
			$dzData2[substr($bWhat->kodeorg,0,6)]=substr($bWhat->kodeorg,0,6);
            $dzData[$bWhat->kodeorg]['kodeorg']=$bWhat->kodeorg;
            $dzData[$bWhat->kodeorg]['tahuntanam']=$bWhat->tahuntanam;
            $dzData[$bWhat->kodeorg]['kelaspohon']=$bWhat->kelaspohon;
			$dzData[$bWhat->kodeorg]['intiplasma']=$optIP[$bWhat->intiplasma];
            $dzData[$bWhat->kodeorg]['luaskerangka']=$luaskerangka;
            $dzData[$bWhat->kodeorg]['luasareaproduktif']=$bWhat->luasareaproduktif;
            $dzData[$bWhat->kodeorg]['luasareanonproduktif']=$bWhat->luasareanonproduktif;
            $dzData[$bWhat->kodeorg]['jumlahpokok']=$bWhat->jumlahpokok;
            $dzData[$bWhat->kodeorg]['statusblok']=$bWhat->statusblok;
            $dzData[$bWhat->kodeorg]['jenisbibit']=$bWhat->jenisbibit;
            $dzData[$bWhat->kodeorg]['cadangan']=$bWhat->cadangan;
            $dzData[$bWhat->kodeorg]['okupasi']=$bWhat->okupasi;
            $dzData[$bWhat->kodeorg]['rendahan']=$bWhat->rendahan;
            $dzData[$bWhat->kodeorg]['sungai']=$bWhat->sungai;
            $dzData[$bWhat->kodeorg]['rumah']=$bWhat->rumah;
            $dzData[$bWhat->kodeorg]['kantor']=$bWhat->kantor;
            $dzData[$bWhat->kodeorg]['pabrik']=$bWhat->pabrik;
            $dzData[$bWhat->kodeorg]['jalan']=$bWhat->jalan;
            $dzData[$bWhat->kodeorg]['kolam']=$bWhat->kolam;
            $dzData[$bWhat->kodeorg]['umum']=$bWhat->umum;
            $dzData[$bWhat->kodeorg]['lc']=$bWhat->lc;
            $dzData[$bWhat->kodeorg]['luasbloking']=$bWhat->luasbloking;
            $dzData[$bWhat->kodeorg]['luasareanonproduktifhitung']=$bWhat->cadangan+$bWhat->okupasi+$bWhat->rendahan+$bWhat->sungai+$bWhat->rumah+
                $bWhat->kantor+$bWhat->pabrik+$bWhat->jalan+$bWhat->kolam+$bWhat->umum;
        }
               
    }
        
    #ambil data tanam
	$where2="";
	if ($afdeling == '') {
		$where2 = " and substr(kodeorg,1,6) in (".getOrgDetail(26).")";
	} else {
		$where2 = " and kodeorg like '".$afdeling."%'";
	}
    $sWhat="select kodeorg, hasilkerja from ".$dbname.".kebun_perawatan_vw where kodeorg like '".$kebun."%' ".$where2." 
        and tanggal like '".$tahun."%' and jurnal = '1' and kodekegiatan in (select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'TN')
        group by kodeorg order by kodeorg";
	$rWhat=$owlPDO->query($sWhat) or die(print " Gagal: ".PDOException::getMessage());
	$rWhat->setFetchMode(PDO::FETCH_OBJ);
    while($bWhat=$rWhat->fetch()){
		setIt($dzData[$bWhat->kodeorg]['mutasi+'],0);
        $dzData[$bWhat->kodeorg]['mutasi+']+=$bWhat->hasilkerja;
    }
    
    #ambil data pokok mati
	$where2="";
	if ($afdeling == '') {
		$where2 = " and substr(blok,1,6) in (".getOrgDetail(26).")";
	} else {
		$where2 = " and blok like '".$afdeling."%'";
	}
    $sWhat="select blok as kodeorg, pokokmati
        from ".$dbname.".kebun_rencanasisip
        where blok like '".$kebun."%' ".$where2." and periode like '".$tahun."%' and posting ='1'";  
    $rWhat=$owlPDO->query($sWhat) or die(print " Gagal: ".PDOException::getMessage());
	$rWhat->setFetchMode(PDO::FETCH_OBJ);
    while($bWhat=$rWhat->fetch()){
		setIt($dzData[$bWhat->kodeorg]['mutasi-'],0);
        $dzData[$bWhat->kodeorg]['mutasi-']+=$bWhat->pokokmati;
    }    
    
	if($tipe==1){
		if(!empty($tTanam))arsort($tTanam);
		$jdlOrg = $_SESSION['lang']['kodeorganisasi'];
	}else{
		if(!empty($tTanam))sort($tTanam);
		$jdlOrg = $_SESSION['lang']['namaorganisasi'];
	}
    
    if(!empty($kOrgan))sort($kOrgan);
    
    $brd=0;
    if($proses=='excel'){
        $brd=1;
        $bgcoloraja="bgcolor=#DEDEDE align=center";
    } else {
		$bgcoloraja="";
	}

    $tab= $_SESSION['lang']['laporan']." ".$_SESSION['lang']['arealstatement']."<br>
        ".$_SESSION['lang']['unitkerja'].": ".$kebun." ".$afdeling." ".$tahuntanam.".";
    
    $tab.="<div class='table-scroll'><table width=100% class=sortable border=". $brd." cellspacing=1>
    <thead>
    <tr class=rowheader>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['tahuntanam']."</th>
        <th ".$bgcoloraja." align=center>".$jdlOrg."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['kelaspohon']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['statusblok']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jenisbibit']."</th>
		<th ".$bgcoloraja." align=center>".$_SESSION['lang']['intiplasma']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['luaskerangka']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['luasareaproduktif']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['luasareanonproduktif']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jmlhpokok']." ".$tahunlalu."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['Mutasi1']."+</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['Mutasi1']."-</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jmlhpokok']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['cadangan']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['okupasi']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['rendahan']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['sungai']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['rumah']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['kantor']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['pabrik']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['jalan']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['kolam']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['umum']."</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['lc']."</th>
        <th ".$bgcoloraja." align=center>Luas Bloking</th>
        <th ".$bgcoloraja." align=center>".$_SESSION['lang']['kerapatan']."</th>
    </tr>";                  
    $tab.="</thead>
    <tbody>";
	
	if(!empty($tTanam))foreach($tTanam as $tT){
		if($tT==0){
			$subheader="BIBITAN";
		}else{
			if(($tahun-$tT)==1){
				$subheader="TBM 1";
			}else if(($tahun-$tT)==2){
				$subheader="TBM 2";
			}else if(($tahun-$tT)==3){
				$subheader="TBM 3";
			}else{
				$subheader="TM";
			}
		}
		$tab.="<tr class=rowcontent>
				<td colspan=26><b>".$subheader."</b></td>
				</tr>";
		
		
		if(!empty($kDiv))foreach($kDiv as $kD){
			if($dzData2[$kD]==$kD){
				$subheader2=$namaOrg[$dzData2[$kD]];
			}
			
			if($tipe==1 || $tipe==2){
				$tab.="<tr class=rowcontent>
						<td><b></b></td>
						<td colspan=25><b>".$subheader2."</b></td>
						</tr>";
			}
		
			if(!empty($kOrgan))foreach($kOrgan as $kO){
				if($dzData[$kO]['tahuntanam']==$tT && substr($dzData[$kO]['kodeorg'],0,6)==$kD){
					if($tipe==1){
						$hasilOrg = $namaOrg[$dzData[$kO]['kodeorg']];
					}else if($tipe==2){
						$hasilOrg = $namaOrg[$dzData[$kO]['kodeorg']];
					}else{
						$hasilOrg = $namaOrg[$dzData[$kO]['kodeorg']];
					}
					$warnaluasareanonproduktif="";
					if(number_format($dzData[$kO]['luasareanonproduktif'],2)!=number_format($dzData[$kO]['luasareanonproduktifhitung'],2)){
						$warnaluasareanonproduktif=" bgcolor=pink title='Data ".$_SESSION['lang']['luasareanonproduktif']." berbeda dengan perhitungan'";
					}
					$warnajumlahpokok="";
					@$dzData[$kO]['jumlahpokokhitung']=$dzData[$kO]['jumlahpokok'];
					// @$dzData[$kO]['jumlahpokokhitung']=$dzData[$kO]['jumlahpokok-1']+$dzData[$kO]['mutasi+']-$dzData[$kO]['mutasi-'];        
					// if(number_format($dzData[$kO]['jumlahpokok'],2)!=number_format($dzData[$kO]['jumlahpokokhitung'],2)){
						// $warnajumlahpokok=" bgcolor=pink title='Data ".$_SESSION['lang']['jmlhpokok']." berbeda dengan data SETUP - BLOK : ".$dzData[$kO]['jumlahpokok']."'";
					// }
					@$dzData[$kO]['kerapatan']=$dzData[$kO]['jumlahpokokhitung']/$dzData[$kO]['luasareaproduktif'];
					setIt($dzData[$kO]['jumlahpokok-1'],0);
					setIt($dzData[$kO]['mutasi+'],0);
					setIt($dzData[$kO]['mutasi-'],0);
					setIt($dzData[$kO]['cadangan'],0);
					setIt($dzData[$kO]['okupasi'],0);
					setIt($dzData[$kO]['rendahan'],0);
					setIt($dzData[$kO]['sungai'],0);
					setIt($dzData[$kO]['rumah'],0);
					setIt($dzData[$kO]['kantor'],0);
					setIt($dzData[$kO]['pabrik'],0);
					setIt($dzData[$kO]['jalan'],0);
					setIt($dzData[$kO]['kolam'],0);
					setIt($dzData[$kO]['umum'],0);
					setIt($dzData[$kO]['kerapatan'],0);
					if($tipe==1 || $tipe==2){
						$tab.="<tr class=rowcontent>
							<td align=center>".$tT."</td>
							<td align=center>".$hasilOrg."</td>
							<td align=center>".$dzData[$kO]['kelaspohon']."</td>
							<td align=center>".$dzData[$kO]['statusblok']."</td>
							<td align=center>".$dzData[$kO]['jenisbibit']."</td>
							<td align=center>".$dzData[$kO]['intiplasma']."</td>
							<td align=right>".number_format($dzData[$kO]['luaskerangka'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['luasareaproduktif'],2)."</td>
							<td align=right".$warnaluasareanonproduktif.">".number_format($dzData[$kO]['luasareanonproduktif'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['jumlahpokok-1'])."</td>
							<td align=right>".number_format($dzData[$kO]['mutasi+'])."</td>
							<td align=right>".number_format($dzData[$kO]['mutasi-'])."</td>
							<td align=right".$warnajumlahpokok.">".number_format($dzData[$kO]['jumlahpokokhitung'])."</td>
							<td align=right>".number_format($dzData[$kO]['cadangan'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['okupasi'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['rendahan'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['sungai'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['rumah'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['kantor'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['pabrik'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['jalan'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['kolam'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['umum'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['lc'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['luasbloking'],2)."</td>
							<td align=right>".number_format($dzData[$kO]['kerapatan'],2)."</td>
						</tr>";
					}
					setIt($subDiv[$tT][$kD]['luaskerangka'],0);
					setIt($subDiv[$tT][$kD]['luasareaproduktif'],0);
					setIt($subDiv[$tT][$kD]['luasareanonproduktif'],0);
					setIt($subDiv[$tT][$kD]['jumlahpokok-1'],0);
					setIt($subDiv[$tT][$kD]['mutasi+'],0);
					setIt($subDiv[$tT][$kD]['mutasi-'],0);
					setIt($subDiv[$tT][$kD]['jumlahpokokhitung'],0);
					setIt($subDiv[$tT][$kD]['cadangan'],0);
					setIt($subDiv[$tT][$kD]['okupasi'],0);
					setIt($subDiv[$tT][$kD]['rendahan'],0);
					setIt($subDiv[$tT][$kD]['sungai'],0);
					setIt($subDiv[$tT][$kD]['rumah'],0);
					setIt($subDiv[$tT][$kD]['kantor'],0);
					setIt($subDiv[$tT][$kD]['pabrik'],0);
					setIt($subDiv[$tT][$kD]['jalan'],0);
					setIt($subDiv[$tT][$kD]['kolam'],0);
					setIt($subDiv[$tT][$kD]['umum'],0);
					setIt($subDiv[$tT][$kD]['lc'],0);
					setIt($subDiv[$tT][$kD]['luasbloking'],0);
					$subDiv[$tT][$kD]['luaskerangka']+=$dzData[$kO]['luaskerangka'];
					$subDiv[$tT][$kD]['luasareaproduktif']+=$dzData[$kO]['luasareaproduktif'];
					$subDiv[$tT][$kD]['luasareanonproduktif']+=$dzData[$kO]['luasareanonproduktif'];
					$subDiv[$tT][$kD]['jumlahpokok-1']+=$dzData[$kO]['jumlahpokok-1'];
					$subDiv[$tT][$kD]['mutasi+']+=$dzData[$kO]['mutasi+'];
					$subDiv[$tT][$kD]['mutasi-']+=$dzData[$kO]['mutasi-'];
					$subDiv[$tT][$kD]['jumlahpokokhitung']+=$dzData[$kO]['jumlahpokokhitung'];
					$subDiv[$tT][$kD]['cadangan']+=$dzData[$kO]['cadangan'];
					$subDiv[$tT][$kD]['okupasi']+=$dzData[$kO]['okupasi'];
					$subDiv[$tT][$kD]['rendahan']+=$dzData[$kO]['rendahan'];
					$subDiv[$tT][$kD]['sungai']+=$dzData[$kO]['sungai'];
					$subDiv[$tT][$kD]['rumah']+=$dzData[$kO]['rumah'];
					$subDiv[$tT][$kD]['kantor']+=$dzData[$kO]['kantor'];
					$subDiv[$tT][$kD]['pabrik']+=$dzData[$kO]['pabrik'];
					$subDiv[$tT][$kD]['jalan']+=$dzData[$kO]['jalan'];
					$subDiv[$tT][$kD]['kolam']+=$dzData[$kO]['kolam'];
					$subDiv[$tT][$kD]['umum']+=$dzData[$kO]['umum'];
					$subDiv[$tT][$kD]['lc']+=$dzData[$kO]['lc'];
					$subDiv[$tT][$kD]['luasbloking']+=$dzData[$kO]['luasbloking'];
					@$subDiv[$tT][$kD]['kerapatan']=$subDiv[$tT][$kD]['jumlahpokokhitung']/$subDiv[$tT][$kD]['luasareaproduktif'];
					
					
					
					setIt($subtotal[$tT]['luaskerangka'],0);
					setIt($subtotal[$tT]['luasareaproduktif'],0);
					setIt($subtotal[$tT]['luasareanonproduktif'],0);
					setIt($subtotal[$tT]['jumlahpokok-1'],0);
					setIt($subtotal[$tT]['mutasi+'],0);
					setIt($subtotal[$tT]['mutasi-'],0);
					setIt($subtotal[$tT]['jumlahpokokhitung'],0);
					setIt($subtotal[$tT]['cadangan'],0);
					setIt($subtotal[$tT]['okupasi'],0);
					setIt($subtotal[$tT]['rendahan'],0);
					setIt($subtotal[$tT]['sungai'],0);
					setIt($subtotal[$tT]['rumah'],0);
					setIt($subtotal[$tT]['kantor'],0);
					setIt($subtotal[$tT]['pabrik'],0);
					setIt($subtotal[$tT]['jalan'],0);
					setIt($subtotal[$tT]['kolam'],0);
					setIt($subtotal[$tT]['umum'],0);
					setIt($subtotal[$tT]['lc'],0);
					setIt($subtotal[$tT]['luasbloking'],0);
					$subtotal[$tT]['luaskerangka']+=$dzData[$kO]['luaskerangka'];
					$subtotal[$tT]['luasareaproduktif']+=$dzData[$kO]['luasareaproduktif'];
					$subtotal[$tT]['luasareanonproduktif']+=$dzData[$kO]['luasareanonproduktif'];
					$subtotal[$tT]['jumlahpokok-1']+=$dzData[$kO]['jumlahpokok-1'];
					$subtotal[$tT]['mutasi+']+=$dzData[$kO]['mutasi+'];
					$subtotal[$tT]['mutasi-']+=$dzData[$kO]['mutasi-'];
					$subtotal[$tT]['jumlahpokokhitung']+=$dzData[$kO]['jumlahpokokhitung'];
					$subtotal[$tT]['cadangan']+=$dzData[$kO]['cadangan'];
					$subtotal[$tT]['okupasi']+=$dzData[$kO]['okupasi'];
					$subtotal[$tT]['rendahan']+=$dzData[$kO]['rendahan'];
					$subtotal[$tT]['sungai']+=$dzData[$kO]['sungai'];
					$subtotal[$tT]['rumah']+=$dzData[$kO]['rumah'];
					$subtotal[$tT]['kantor']+=$dzData[$kO]['kantor'];
					$subtotal[$tT]['pabrik']+=$dzData[$kO]['pabrik'];
					$subtotal[$tT]['jalan']+=$dzData[$kO]['jalan'];
					$subtotal[$tT]['kolam']+=$dzData[$kO]['kolam'];
					$subtotal[$tT]['umum']+=$dzData[$kO]['umum'];
					$subtotal[$tT]['lc']+=$dzData[$kO]['lc'];
					$subtotal[$tT]['luasbloking']+=$dzData[$kO]['luasbloking'];
					@$subtotal[$tT]['kerapatan']=$subtotal[$tT]['jumlahpokokhitung']/$subtotal[$tT]['luasareaproduktif'];
					
					setIt($grandtotal['luaskerangka'],0);
					setIt($grandtotal['luasareaproduktif'],0);
					setIt($grandtotal['luasareanonproduktif'],0);
					setIt($grandtotal['jumlahpokok-1'],0);
					setIt($grandtotal['mutasi+'],0);
					setIt($grandtotal['mutasi-'],0);
					setIt($grandtotal['jumlahpokokhitung'],0);
					setIt($grandtotal['cadangan'],0);
					setIt($grandtotal['okupasi'],0);
					setIt($grandtotal['rendahan'],0);
					setIt($grandtotal['sungai'],0);
					setIt($grandtotal['rumah'],0);
					setIt($grandtotal['kantor'],0);
					setIt($grandtotal['pabrik'],0);
					setIt($grandtotal['jalan'],0);
					setIt($grandtotal['kolam'],0);
					setIt($grandtotal['umum'],0);
					setIt($grandtotal['lc'],0);
					setIt($grandtotal['luasbloking'],0);
					$grandtotal['luaskerangka']+=$dzData[$kO]['luaskerangka'];
					$grandtotal['luasareaproduktif']+=$dzData[$kO]['luasareaproduktif'];
					$grandtotal['luasareanonproduktif']+=$dzData[$kO]['luasareanonproduktif'];
					$grandtotal['jumlahpokok-1']+=$dzData[$kO]['jumlahpokok-1'];
					$grandtotal['mutasi+']+=$dzData[$kO]['mutasi+'];
					$grandtotal['mutasi-']+=$dzData[$kO]['mutasi-'];
					$grandtotal['jumlahpokokhitung']+=$dzData[$kO]['jumlahpokokhitung'];
					$grandtotal['cadangan']+=$dzData[$kO]['cadangan'];
					$grandtotal['okupasi']+=$dzData[$kO]['okupasi'];
					$grandtotal['rendahan']+=$dzData[$kO]['rendahan'];
					$grandtotal['sungai']+=$dzData[$kO]['sungai'];
					$grandtotal['rumah']+=$dzData[$kO]['rumah'];
					$grandtotal['kantor']+=$dzData[$kO]['kantor'];
					$grandtotal['pabrik']+=$dzData[$kO]['pabrik'];
					$grandtotal['jalan']+=$dzData[$kO]['jalan'];
					$grandtotal['kolam']+=$dzData[$kO]['kolam'];
					$grandtotal['umum']+=$dzData[$kO]['umum'];
					$grandtotal['lc']+=$dzData[$kO]['lc'];
					$grandtotal['luasbloking']+=$dzData[$kO]['luasbloking'];
					@$grandtotal['kerapatan']=$grandtotal['jumlahpokokhitung']/$grandtotal['luasareaproduktif'];
				}
			}
			if($tipe==1 || $tipe==2){
				$tab.="<tr class=rowcontent>
					<td></td>
					<td colspan=5 align=left><b>".$_SESSION['lang']['subtotal']." ".$subheader2."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['luaskerangka'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['luasareaproduktif'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['luasareanonproduktif'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['jumlahpokok-1'])."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['mutasi+'])."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['mutasi-'])."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['jumlahpokokhitung'])."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['cadangan'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['okupasi'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['rendahan'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['sungai'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['rumah'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['kantor'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['pabrik'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['jalan'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['kolam'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['umum'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['lc'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['luasbloking'],2)."</b></td>
					<td align=right><b>".number_format(@$subDiv[$tT][$kD]['kerapatan'],2)."</b></td>
				</tr>";
				$tab.="<tr class=rowcontent><td colspan='26'>&nbsp;</td></tr>";
			}else{
				$tab.="<tr class=rowcontent>
					<td><b>".$tT."</b></td>
					<td align=left><b>".$subheader2."</b></td>
					<td align=left style='text-align:center'><b>-</b></td>
					<td align=left style='text-align:center'><b>-</b></td>
					<td align=left style='text-align:center'><b>-</b></td>
					<td align=left style='text-align:center'><b>-</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['luaskerangka'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['luasareaproduktif'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['luasareanonproduktif'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['jumlahpokok-1'])."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['mutasi+'])."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['mutasi-'])."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['jumlahpokokhitung'])."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['cadangan'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['okupasi'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['rendahan'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['sungai'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['rumah'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['kantor'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['pabrik'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['jalan'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['kolam'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['umum'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['lc'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['luasbloking'],2)."</b></td>
					<td align=right><b>".number_format($subDiv[$tT][$kD]['kerapatan'],2)."</b></td>
				</tr>";
				$tab.="<tr class=rowcontent><td colspan='26'>&nbsp;</td></tr>";
			}
		}
        $tab.="<tr class=rowcontent>
			<td></td>
			<td colspan=5 align=left><b>".$_SESSION['lang']['subtotal']." ".$subheader."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['luaskerangka'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['luasareaproduktif'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['luasareanonproduktif'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['jumlahpokok-1'])."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['mutasi+'])."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['mutasi-'])."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['jumlahpokokhitung'])."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['cadangan'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['okupasi'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['rendahan'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['sungai'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['rumah'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['kantor'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['pabrik'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['jalan'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['kolam'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['umum'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['lc'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['luasbloking'],2)."</b></td>
            <td align=right><b>".number_format($subtotal[$tT]['kerapatan'],2)."</b></td>
        </tr>";
		$tab.="<tr class=rowcontent><td colspan='26'>&nbsp;</td></tr>";
		$tab.="<tr class=rowcontent><td colspan='26'>&nbsp;</td></tr>";
    }
    $tab.="<tr class=rowcontent>
        <td colspan=6 align=right><b>".$_SESSION['lang']['grnd_total']."</b></td>
        <td align=right><b>".number_format($grandtotal['luaskerangka'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['luasareaproduktif'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['luasareanonproduktif'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['jumlahpokok-1'])."</b></td>
        <td align=right><b>".number_format($grandtotal['mutasi+'])."</b></td>
        <td align=right><b>".number_format($grandtotal['mutasi-'])."</b></td>
        <td align=right><b>".number_format($grandtotal['jumlahpokokhitung'])."</b></td>
        <td align=right><b>".number_format($grandtotal['cadangan'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['okupasi'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['rendahan'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['sungai'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['rumah'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['kantor'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['pabrik'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['jalan'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['kolam'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['umum'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['lc'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['luasbloking'],2)."</b></td>
        <td align=right><b>".number_format($grandtotal['kerapatan'],2)."</b></td>
    </tr>";                                                    
    $tab.="</tbody></table></div>";

}	
switch($proses)
{
    case'pdf':
	
    class PDF extends FPDF
    {
        function Header() {
            global $owlPDO;
            global $dbname;
            global $tahun;
            global $kebun;
            global $afdeling;
            global $tahuntanam;
            global $judul;
            
            $query = selectQuery($dbname,'organisasi','alamat,telepon',
                "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
            $orgData = fetchData($query);

            $width = $this->w - $this->lMargin - $this->rMargin;
            $height = 15;
            $path='images/logo.jpg';
            $this->Image($path,$this->lMargin,$this->tMargin,0,60);	
            $this->SetFont('Arial','B',9);
            $this->SetFillColor(255,255,255);	
            $this->SetX(100);   
            $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
            $this->SetX(100); 		
            $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
            $this->SetX(100); 			
            $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
            $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
            $this->Ln();
            
            $this->SetFont('Arial','B',12);
            $this->Cell($width,$height,$_SESSION['lang']['laporan'].' '.$_SESSION['lang']['arealstatement'].': '.$tahun,'',0,'C');
            $this->Ln();
            $this->SetFont('Arial','',8);
            $this->Cell(1/3*$width,$height,$_SESSION['lang']['kebun'].': '.$kebun,'',0,'L');
            if($afdeling!=''){
                $this->Cell(1/3*$width,$height,$_SESSION['lang']['afdeling'].': '.$afdeling,'',0,'C');
            }
            if($tahuntanam!=''){
                $this->Cell(1/3*$width,$height,$_SESSION['lang']['tahuntanam'].': '.$tahuntanam,'',0,'R');
            }
            $this->Ln();

        }
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
        }
    }
    $pdf=new PDF('L','pt','A4');
    $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
    $height = 12;
    $pdf->AddPage();
    $pdf->SetFillColor(255,255,255);
    $pdf->SetFont('Arial','',8);
    
    $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['tahuntanam'],1,0,'C',0);
    $pdf->Cell(8/100*$width,$height,$jdlOrg,1,0,'C',0);
    $pdf->Cell(6/100*$width,$height,$_SESSION['lang']['kelaspohon'],1,0,'C',0);
	$pdf->Cell(6/100*$width,$height,$_SESSION['lang']['intiplasma'],1,0,'C',0);
    $pdf->Cell(5.5/100*$width,$height,$_SESSION['lang']['statusblok'],1,0,'C',0);
    $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['luaskerangka'],1,0,'C',0);
    $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['luasareaproduktif'],1,0,'C',0);
    $pdf->Cell(8/100*$width,$height,$_SESSION['lang']['luasareanonproduktif'],1,0,'C',0);
    $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['jmlhpokok'].' '.$tahunlalu,1,0,'C',0);
    $pdf->Cell(7.5/100*$width,$height,$_SESSION['lang']['Mutasi1'].'+',1,0,'C',0);
    $pdf->Cell(7.5/100*$width,$height,$_SESSION['lang']['Mutasi1'].'-',1,0,'C',0);
    $pdf->Cell(10/100*$width,$height,$_SESSION['lang']['jmlhpokok'],1,0,'C',0);
    $pdf->Cell(7.5/100*$width,$height,$_SESSION['lang']['kerapatan'],1,0,'C',0);
    $pdf->Ln();
	
	if(!empty($tTanam))foreach($tTanam as $tT){
		if($tT==0){
			$subheader="BIBITAN";
		}else{
			if(($tahun-$tT)==1){
				$subheader="TBM 1";
			}else if(($tahun-$tT)==2){
				$subheader="TBM 2";
			}else if(($tahun-$tT)==3){
				$subheader="TBM 3";
			}else{
				$subheader="TM";
			}
		}
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(100/100*$width,$height,$subheader,1,0,'L',0);
		$pdf->Ln();
		
		if(!empty($kDiv))foreach($kDiv as $kD){
			if($dzData2[$kD]==$kD){
				$subheader2=$namaOrg[$dzData2[$kD]];
			}
			
			if($tipe==1 || $tipe==2){
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(8/100*$width,$height,'','TBL',0,'L',0);
				$pdf->Cell(92/100*$width,$height,$subheader2,'TBR',0,'L',0);
				$pdf->Ln();
			}
		
		
			if(!empty($kOrgan))foreach($kOrgan as $kO){
				if($dzData[$kO]['tahuntanam']==$tT  && substr($dzData[$kO]['kodeorg'],0,6)==$kD){
					if($tipe==1){
						$hasilOrg = $dzData[$kO]['kodeorg'];
					}else if($tipe==2){
						$hasilOrg = $namaOrg[$dzData[$kO]['kodeorg']];
					}else{
						$hasilOrg = $namaOrg[$dzData[$kO]['kodeorg']];
					}
					
					if($tipe==1 || $tipe==2){
						$pdf->SetFont('Arial','',8);
						$pdf->Cell(8/100*$width,$height,$tT,1,0,'C',0);
						$pdf->Cell(8/100*$width,$height,$hasilOrg,1,0,'C',0);
						$pdf->Cell(6/100*$width,$height,$dzData[$kO]['kelaspohon'],1,0,'C',0);
						$pdf->Cell(6/100*$width,$height,$dzData[$kO]['intiplasma'],1,0,'C',0);
						$pdf->Cell(5.5/100*$width,$height,$dzData[$kO]['statusblok'],1,0,'C',0);
						$pdf->Cell(8/100*$width,$height,number_format($dzData[$kO]['luaskerangka'],2),1,0,'R',0);
						$pdf->Cell(8/100*$width,$height,number_format($dzData[$kO]['luasareaproduktif'],2),1,0,'R',0);
						$pdf->Cell(8/100*$width,$height,number_format($dzData[$kO]['luasareanonproduktif'],2),1,0,'R',0);
						$pdf->Cell(10/100*$width,$height,number_format($dzData[$kO]['jumlahpokok-1']),1,0,'R',0);
						$pdf->Cell(7.5/100*$width,$height,number_format($dzData[$kO]['mutasi+']),1,0,'R',0);
						$pdf->Cell(7.5/100*$width,$height,number_format($dzData[$kO]['mutasi-']),1,0,'R',0);
						$pdf->Cell(10/100*$width,$height,number_format($dzData[$kO]['jumlahpokokhitung']),1,0,'R',0);
						$pdf->Cell(7.5/100*$width,$height,number_format($dzData[$kO]['kerapatan'],2),1,0,'R',0);
						$pdf->Ln();
					}
				}
			}
			
			//echo $tipe;
			/*echo"<pre>";
		print_r($kOrgan);
		echo"</pre>";*/
			if($tipe==1 || $tipe==2){
			
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(8/100*$width,$height,'','TBL',0,'L',0);
				$pdf->Cell(25.5/100*$width,$height,$_SESSION['lang']['subtotal'].' '.$subheader2,'TBR',0,'L',0);
				$pdf->Cell(8/100*$width,$height,number_format($subDiv[$kD]['luaskerangka'],2),1,0,'R',0);
				$pdf->Cell(8/100*$width,$height,number_format($subDiv[$kD]['luasareaproduktif'],2),1,0,'R',0);
				$pdf->Cell(8/100*$width,$height,number_format($subDiv[$kD]['luasareanonproduktif'],2),1,0,'R',0);
				$pdf->Cell(10/100*$width,$height,number_format($subDiv[$kD]['jumlahpokok-1']),1,0,'R',0);
				$pdf->Cell(7.5/100*$width,$height,number_format($subDiv[$kD]['mutasi+']),1,0,'R',0);
				$pdf->Cell(7.5/100*$width,$height,number_format($subDiv[$kD]['mutasi-']),1,0,'R',0);
				$pdf->Cell(10/100*$width,$height,number_format($subDiv[$kD]['jumlahpokokhitung']),1,0,'R',0);
				$pdf->Cell(7.5/100*$width,$height,number_format($subDiv[$kD]['kerapatan'],2),1,0,'R',0);
				$pdf->Ln();
			}else{
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(8/100*$width,$height,'','TBL',0,'L',0);
				$pdf->Cell(25.5/100*$width,$height,$subheader2,'TBR',0,'L',0);
				$pdf->Cell(8/100*$width,$height,number_format($subDiv[$kD]['luaskerangka'],2),1,0,'R',0);
				$pdf->Cell(8/100*$width,$height,number_format($subDiv[$kD]['luasareaproduktif'],2),1,0,'R',0);
				$pdf->Cell(8/100*$width,$height,number_format($subDiv[$kD]['luasareanonproduktif'],2),1,0,'R',0);
				$pdf->Cell(10/100*$width,$height,number_format($subDiv[$kD]['jumlahpokok-1']),1,0,'R',0);
				$pdf->Cell(7.5/100*$width,$height,number_format($subDiv[$kD]['mutasi+']),1,0,'R',0);
				$pdf->Cell(7.5/100*$width,$height,number_format($subDiv[$kD]['mutasi-']),1,0,'R',0);
				$pdf->Cell(10/100*$width,$height,number_format($subDiv[$kD]['jumlahpokokhitung']),1,0,'R',0);
				$pdf->Cell(7.5/100*$width,$height,number_format($subDiv[$kD]['kerapatan'],2),1,0,'R',0);
				$pdf->Ln();
			}
		}
		$pdf->Cell(33.5/100*$width,$height,$_SESSION['lang']['subtotal'].' '.$subheader,1,0,'R',0);
		$pdf->Cell(8/100*$width,$height,number_format($subtotal[$tT]['luaskerangka'],2),1,0,'R',0);
		$pdf->Cell(8/100*$width,$height,number_format($subtotal[$tT]['luasareaproduktif'],2),1,0,'R',0);
		$pdf->Cell(8/100*$width,$height,number_format($subtotal[$tT]['luasareanonproduktif'],2),1,0,'R',0);
		$pdf->Cell(10/100*$width,$height,number_format($subtotal[$tT]['jumlahpokok-1']),1,0,'R',0);
		$pdf->Cell(7.5/100*$width,$height,number_format($subtotal[$tT]['mutasi+']),1,0,'R',0);
		$pdf->Cell(7.5/100*$width,$height,number_format($subtotal[$tT]['mutasi-']),1,0,'R',0);
		$pdf->Cell(10/100*$width,$height,number_format($subtotal[$tT]['jumlahpokokhitung']),1,0,'R',0);
		$pdf->Cell(7.5/100*$width,$height,number_format($subtotal[$tT]['kerapatan'],2),1,0,'R',0);
		$pdf->Ln();
		$pdf->Ln();
    }
    $pdf->Cell(33.5/100*$width,$height,$_SESSION['lang']['grnd_total'],1,0,'R',0);
    $pdf->Cell(8/100*$width,$height,number_format($grandtotal['luaskerangka'],2),1,0,'R',0);
    $pdf->Cell(8/100*$width,$height,number_format($grandtotal['luasareaproduktif'],2),1,0,'R',0);
    $pdf->Cell(8/100*$width,$height,number_format($grandtotal['luasareanonproduktif'],2),1,0,'R',0);
    $pdf->Cell(10/100*$width,$height,number_format($grandtotal['jumlahpokok-1']),1,0,'R',0);
    $pdf->Cell(7.5/100*$width,$height,number_format($grandtotal['mutasi+']),1,0,'R',0);
    $pdf->Cell(7.5/100*$width,$height,number_format($grandtotal['mutasi-']),1,0,'R',0);
    $pdf->Cell(10/100*$width,$height,number_format($grandtotal['jumlahpokokhitung']),1,0,'R',0);
    $pdf->Cell(7.5/100*$width,$height,number_format($grandtotal['kerapatan'],2),1,0,'R',0);
    $pdf->Ln();
	
	$pdf->Output();
    break;    
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="aresta_".$tahun."_".$kebun."_".$afdeling."_".$tahuntanam;
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
            //closedir($handle);
        }
    break;
    case'getAfdeling0':
        $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sPrd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
               where induk = '".$kebun."' and tipe='afdeling' and kodeorganisasi in (".getOrgDetail(26).") order by namaorganisasi asc";
		$qPrd=$owlPDO->query($sPrd) or die(print " Gagal: ".PDOException::getMessage());
		$qPrd->setFetchMode(PDO::FETCH_ASSOC);
        while($rPrd=$qPrd->fetch()){
            $optAfd.="<option value=".$rPrd['kodeorganisasi'].">".$rPrd['namaorganisasi']."</option>";
        }
        
        $optafd2="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sorg2="select distinct tahuntanam from ".$dbname.".setup_blok 
                where kodeorg like '".$kebun."%' and tahuntanam>0 and (luasareaproduktif+luasareanonproduktif)>0 order by tahuntanam asc";
		$qorg2=$owlPDO->query($sorg2) or die(print " Gagal: ".PDOException::getMessage());
		$qorg2->setFetchMode(PDO::FETCH_ASSOC);
        while($rorg2=$qorg2->fetch()){
            if($param['tahuntanam']!=''){
                $optafd2.="<option value='".$rorg2['tahuntanam']."' ".($param['tahuntanam']==$rorg2['tahuntanam']?"selected":"").">".$rorg2['tahuntanam']."</option>";
            }
            else{
                $optafd2.="<option value='".$rorg2['tahuntanam']."'>".$rorg2['tahuntanam']."</option>";
            }
        }
        
        
        echo $optAfd."####".$optafd2;
    break;

    case'getTahuntanam0':
        $optafd2="<option value=''>".$_SESSION['lang']['all']."</option>";
        $sorg2="select distinct tahuntanam from ".$dbname.".setup_blok 
                where kodeorg like '".$kebun."%' and kodeorg like '".$afdeling."%' and tahuntanam>0 and (luasareaproduktif+luasareanonproduktif)>0 order by tahuntanam asc";
		$qorg2=$owlPDO->query($sorg2) or die(print " Gagal: ".PDOException::getMessage());
		$qorg2->setFetchMode(PDO::FETCH_ASSOC);
        while($rorg2=$qorg2->fetch()){
            if($param['tahuntanam']!=''){
                $optafd2.="<option value='".$rorg2['tahuntanam']."' ".($param['tahuntanam']==$rorg2['tahuntanam']?"selected":"").">".$rorg2['tahuntanam']."</option>";
            }
            else{
                $optafd2.="<option value='".$rorg2['tahuntanam']."'>".$rorg2['tahuntanam']."</option>";
            }
        }
        echo $optafd2."####";
    break;
    
    default:
    break;
}
?>