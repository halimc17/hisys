<?php
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/HtmlExcel.php');
error_reporting(0);
$param = $_POST;
// $proses = $_POST['proses'];

$proses = checkPostGet('proses', '');
$kodeorg = checkPostGet('kodeorg', '');
$divisi = checkPostGet('divisi', '');
$tgldari = tanggalsystemn(checkPostGet('tgldari', ''));
$tglsmp = tanggalsystemn(checkPostGet('tglsmp', ''));
$type = checkPostGet('type', '');
// exit('warning'.$type);

$str = "select * from ".$dbname.".bgt_regional_assignment
	where kodeunit LIKE '".$_SESSION['empl']['lokasitugas']."%'
	";
$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$regional = $bar->regional;
}
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmorgblok=makeOption($dbname,'organisasi','indukblok,namaindukblok');
$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

switch ($proses) {
	case'loaddatadetail':
		$str = "select distinct * from ".$dbname.".kebun_taksasi where afdeling = '".$param['divisi']."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
		$res = fetchdata($str);
		foreach($res as $rdata){
			$tab.="<tr class=rowcontent align=center>";
			$tab.="<td>".$rdata['afdeling']."</td>";
			$tab.="<td>".$nmkary[$rdata['mandor']]."</td>";
			$tab.="<td>".tanggalnormal($rdata['tanggal'])."</td>";
			$tab.="<td>".$nmorgblok[$rdata['blok']]."</td>";
			$tab.="<td>".$rdata['seksi']."</td>";
			$tab.="<td align=right>".$rdata['haesok']."</td>";
			$tab.="<td align=right>".$rdata['jmlhpokok']."</td>";
			$tab.="<td align=right>".$rdata['persenbuahmatang']."</td>";
			$tab.="<td align=right>".$rdata['jjgmasak']."</td>";
			$tab.="<td align=right>".$rdata['jjgoutput']."</td>";
			$tab.="<td align=right>".number_format($rdata['jjgoutput']*$rdata['bjr'],0)."</td>";
			$tab.="<td align=right>".$rdata['rotasi']."</td>";
			$tab.="<td align=right>".$rdata['hkdigunakan']."</td>";
			$tab.="<td><img title=\"Edit\" onclick=\"showEdit('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/edit.png\"></td>";
			$tab.="<td><img title=\"Delete\" onclick=\"deleteData('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/delete.png\"></td>";
			$tab.="</tr>";
		}
		$tab.="</tbody><tfoot>";

	echo $tab;	
	break;
    case 'getDivisi':
        if ($kodeorg != '') {
            $where = " AND afdeling LIKE '".$kodeorg."%'";
        }

        // Option divisi
        $optDivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
        $qDivisi = selectQuery($dbname, 'kebun_taksasi', "afdeling", "1=1 AND afdeling != '' ".$where." GROUP BY afdeling ORDER BY afdeling ASC");
        $resDivisi = fetchData($qDivisi);
        $makeOptKodeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        foreach ($resDivisi as $bar) {
            $optDivisi .= "<option value='".$bar['afdeling']."'>".$bar['afdeling']." - ".$makeOptKodeorg[$bar['afdeling']]."</option>";
        }

        echo $optDivisi;
    break;
    case 'loadData':
    $limit = 20;
    $page = 0;
	$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
	if (isset($_POST['page'])) {
        $page = intval($_POST['page']);
		if ($page < 0)
        $page = 0;
	}

	$offset = floatval($page) * floatval($limit);
	$maxdisplay = (floatval($page) * floatval($limit));
	$no = 0;
	$tab = "";
	$no = $maxdisplay;
	$colspan = 16;
	
    $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
    if ($type == 'excel') {
        $tab = "<table class=sortable border=1 cellspacing=1>";
    }
    $tab.= "<thead><tr align=center>";
    $tab.= "<th>".$_SESSION['lang']['nomor']."</th>";
    $tab.= "<th>".$_SESSION['lang']['afdeling']."</th>";
    $tab.= "<th>".$_SESSION['lang']['mandor']."</th>";
    $tab.= "<th>".$_SESSION['lang']['tanggal']."</th>";
    $tab.= "<th>".$_SESSION['lang']['blok']."</th>";
    $tab.= "<th>Ancak Panen</th>";
    $tab.= "<th>".$_SESSION['lang']['ha']."</th>";
    $tab.= "<th>".$_SESSION['lang']['jmlhpokok']."</th>";
    $tab.= "<th>".$_SESSION['lang']['persenbuahmatang']."</th>";
    $tab.= "<th>".$_SESSION['lang']['jjgmasak']."</th>";
    $tab.= "<th>".$_SESSION['lang']['jjgoutput']."</th>";
    $tab.= "<th>KG Output</th>";
    $tab.= "<th>HK</th>";
    $tab.= "<th>".$_SESSION['lang']['rotasi']."</th>";
    $tab.= "<th>".$_SESSION['lang']['updateby']."</th>";
    $tab.= "<th>".$_SESSION['lang']['tanggalupdate']."</th>";
    if ($type != 'excel') {
        $tab.= "<th colspan=2>".$_SESSION['lang']['action']."</th>";
    } 
    $tab.= "</tr></thead><tbody>";
    
    if ($kodeorg == '' && $divisi == '') {
        $where = " AND substr(afdeling,1,4) in (".getOrgDetail(2).")";
    }

	if($tgldari != '--' && $tglsmp != '--'){
        $where .= " and tanggal between '".$tgldari."' AND '".$tglsmp."'";
	}
    if ($kodeorg != '') {
        $where .= " AND left(blok, 4) = '".$kodeorg."'";
    }
    if ($divisi != '') {
        $where .= " AND afdeling = '".$divisi."'";
    }
    // echo "<pre>"; print_r($where); exit;
    
	$sql = "select distinct * from ".$dbname.".kebun_taksasi where 1=1 ".$where."";
	$res = fetchdata($sql);
	$jlhbrs = count($res);
    if ($type != "excel") {
        $sdata = "select distinct * from ".$dbname.".kebun_taksasi where 1=1 ".$where." order by tanggal desc limit ".$offset.",".$limit." ";
    } else {
        $sdata = "select distinct * from ".$dbname.".kebun_taksasi where 1=1 ".$where." order by tanggal desc";
    } 
    $qdata = $owlPDO->query($sdata)or die(print " Gagal: ".PDOException::getMessage());
    $qdata->setFetchMode(PDO::FETCH_ASSOC);
    while ($rdata = $qdata->fetch()) {
		$no++;
    	$tab.="<tr class=rowcontent align=center>";
    	$tab.="<td>".$no."</td>";
    	$tab.="<td align=left>".$nmorg[$rdata['afdeling']]."</td>";
		$tab.="<td align=left>".$nmkary[$rdata['mandor']]."</td>";
    	$tab.="<td>".tanggalnormal($rdata['tanggal'])."</td>";
    	$tab.="<td>".$nmorgblok[$rdata['blok']]."</td>";
    	$tab.="<td>".$rdata['seksi']."</td>";
    	$tab.="<td align=right>".$rdata['haesok']."</td>";
    	$tab.="<td align=right>".$rdata['jmlhpokok']."</td>";
    	$tab.="<td align=right>".$rdata['persenbuahmatang']."</td>";
    	$tab.="<td align=right>".$rdata['jjgmasak']."</td>";
    	$tab.="<td align=right>".$rdata['jjgoutput']."</td>";
    	$tab.="<td align=right>".number_format($rdata['jjgoutput']*$rdata['bjr'],0)."</td>";
    	$tab.="<td align=right>".$rdata['hkdigunakan']."</td>";
    	$tab.="<td align=right>".$rdata['rotasi']."</td>";
		$tab.="<td>".getNamaKaryawan($rdata['updateby'])."</td>";
		$tab.="<td>".$rdata['updatetime']."</td>";
		
    	if ($type != 'excel') {
            $tab.="<td><img title=\"Edit\" onclick=\"showEdit('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/edit.png\"></td>";
    	    $tab.="<td><img title=\"Delete\" onclick=\"deleteData('".$rdata['afdeling']."','".tanggalnormal($rdata['tanggal'])."','".$rdata['blok']."')\" class=\"zImgBtn\" src=\"images/skyblue/delete.png\"></td>";
        }
        $tab.="</tr>";
    }
    $tab.="</tbody>";

    if ($type != 'excel') {
        $tab.="<tfoot>";
        $totrows = ceil($jlhbrs / $limit);
            if ($totrows == 0) {
                $totrows = 1;
            }
            $isiRow = '';
            for ($er = 1; $er <= $totrows; $er++) {
                $sel = ($page == $er - 1) ? 'selected' : '';
                $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
            }
            $tab.="<tr><td colspan=".$colspan." align=center>";
    
            if ($page == '0') {
                $tab.="<button class=mybutton disabled=true>Prev</button>";
            } else {
                $tab.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
            }
    
            $tab.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
    
            if (($page + 1) == $totrows) {
                $tab.="<button class=mybutton disabled=true>Next</button>";
            } else {
                $tab.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
            }
            $tab.="</td>
            </tr>";
            
            $tab.="</tfoot>";
        }
        $tab.="</table>";
        
    if ($type == "excel") {
        $nop = "panen_taksasi_".date('Ymd').".xls";
        $xls = new HtmlExcel();
        $xls->setCss($css);
        $xls->addSheet("panen_taksasi", $tab);
        $xls->headers($nop);
        echo $xls->buildFile();
    } else {
        echo $tab;
    }

    break;
	case'insert':
       #var ek//$arr="##tanggal##afdeling##blok##seksi##proses##hasisa##haesok##jmlhpokok##persenbuahmatang##jjgmasak##jjgoutput##hkdigunakan##bjr";
       $param['hasisa']==''?$param['hasisa']=0:$param['hasisa']=$param['hasisa'];
       $param['haesok']==''?$param['haesok']=0:$param['haesok']=$param['haesok'];
       $param['jmlhpokok']==''?$param['jmlhpokok']=0:$param['jmlhpokok']=$param['jmlhpokok'];
       $param['persenbuahmatang']==''?$param['persenbuahmatang']=0:$param['persenbuahmatang']=$param['persenbuahmatang'];
       $param['jjgmasak']==''?$param['jjgmasak']=0:$param['jjgmasak']=$param['jjgmasak'];
       $param['jjgoutput']==''?$param['jjgoutput']=0:$param['jjgoutput']=$param['jjgoutput'];
       $param['hkdigunakan']==''?$param['hkdigunakan']=0:$param['hkdigunakan']=$param['hkdigunakan'];
       $param['bjr']==''?$param['bjr']=0:$param['bjr']=$param['bjr'];
       $param['rotasi']==''?$param['rotasi']=0:$param['rotasi']=$param['rotasi'];
       // cek luas
        $query = "SELECT sum(luasareaproduktif) as luas
        	FROM ".$dbname.".`setup_blok` a
        	WHERE a.`indukblok` = '".$param['blok']."' and tahuntanam<=".(intval(date('Y'))-3)." group by indukblok
        	";
        $qDetail = $owlPDO->query($query)or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);
        while ($rDetail = $qDetail->fetch()) {
        	$luas = $rDetail['luas'];
        }
        if (($param['hasisa'] + $param['haesok']) > $luas) {
        	exit("error: Luas tidak bisa lebih dari luas blok: ".$luas." Ha.");
        }
       #end var
       $tgl=explode("-",$param['tanggal']);
       $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
       $scek2="select distinct * from ".$dbname.".kebun_taksasi where tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."' and blok='".$param['blok']."'";
		$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
		$rcek2=owlBaris($qcek2);
       if($rcek2!=0){
//            exit("error: Data sudah pernah diinput.");
            $sins="update ".$dbname.".kebun_taksasi  set `seksi`='".$param['seksi']."',
            `hasisa`='".$param['hasisa']."', `haesok`='".$param['haesok']."', `jmlhpokok`='".$param['jmlhpokok']."', 
            `persenbuahmatang`='".$param['persenbuahmatang']."',`jjgmasak`='".$param['jjgmasak']."', `jjgoutput`='".$param['jjgoutput']."', 
            `hkdigunakan`='".$param['hkdigunakan']."', `bjr`='".$param['bjr']."' ,`mandor`='".$param['mandor']."', `rotasi`='".$param['rotasi']."', updateby='".$_SESSION['standard']['userid']."'   
             where tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."' and blok='".$param['blok']."'";
			try{
				$owlPDO->exec($sins); 
			}catch (PDOException $e){
				exit("error:".$e->getMessage()."__".$sins);
				die();
			}
       }else{
            $scek="select distinct * from ".$dbname.".kebun_taksasi 
              where tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."' and blok='".$param['blok']."'";
			$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$rcek=owlBaris($qcek);
            if($rcek!=0){
				exit("error:Data Sudah Ada");
            }
            $sins="insert into ".$dbname.".kebun_taksasi  
            (`afdeling`,`tanggal`, `blok`, `seksi`, `hasisa`, `haesok`, `jmlhpokok`, `persenbuahmatang`, `jjgmasak`, `jjgoutput`, `hkdigunakan`, `bjr`,`createdby`,`updateby`,`createdtime`,`mandor`,`rotasi`)
            values ('".$param['afdeling']."','".$param['tanggal']."','".$param['blok']."','".$param['seksi']."','".$param['hasisa']."','".$param['haesok']."','".$param['jmlhpokok']."','".$param['persenbuahmatang']."','".$param['jjgmasak']."','".$param['jjgoutput']."','".$param['hkdigunakan']."','".$param['bjr']."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."','".$param['mandor']."','".$param['rotasi']."')";
            try{
				$owlPDO->exec($sins); 
			}catch (PDOException $e){
				exit("error:".$e->getMessage()."__".$sins);
				die();
			}
       }
	break;
	case'getData':
		$str = "select * from ".$dbname.".setup_blok where kodeorg='".$param['blok']."'";
		$res = fetchdata($str);
		$luas = $res[0]['luasareaproduktif'];
		$tt = $res[0]['tahuntanam'];
		$pkk = $res[0]['jumlahpokok'];
		$sph = $res[0]['jumlahpokok']/$res[0]['luasareaproduktif'];
		
		$tgl=explode("-",$param['tanggal']);
		$param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
		$str="select distinct * from ".$dbname.".kebun_taksasi where tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."' and blok ='".$param['blok']."'";

		$qstr=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qstr->setFetchMode(PDO::FETCH_ASSOC);
		$rts=$qstr->fetch();
		echo $rts['afdeling']."###".tanggalnormal($rts['tanggal'])."###".$rts['blok']."###".$rts['seksi']."###".$rts['hasisa']."###".$rts['haesok']."###".$rts['jmlhpokok']."###".$rts['persenbuahmatang']."###".$rts['jjgmasak']."###".$rts['jjgoutput']."###".$rts['hkdigunakan']."###".$rts['bjr']."###".$luas."###".$pkk."###".$sph."###".$tt."###".$rts['mandor']."###".$rts['rotasi'];
	break;
    case 'delete': 
    $tgl=explode("-",$param['tanggal']);
    $param['tanggal']=$tgl[2]."-".$tgl[1]."-".$tgl[0];
	$where = "tanggal='".$param['tanggal']."' and afdeling='".$param['afdeling']."'  and blok='".$param['blok']."'";
	$query = "delete from `".$dbname."`.`kebun_taksasi` where ".$where;
    try{
		$owlPDO->exec($query); 
	}catch (PDOException $e){
		echo "DB Error : ".$e->getMessage();
	    exit;
	}
    break;
    case'getAfd':
    $optafd="<option value =''></option>";
    $sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$param['kebun']."'";
	$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
	$qorg->setFetchMode(PDO::FETCH_ASSOC);
    while ($rorg = $qorg->fetch()) {
    	if ($param['afdeling'] != '') {
    		$optafd.="<option value='".$rorg['kodeorganisasi']."' ".($param['afdeling'] == $rorg['kodeorganisasi'] ? "selected" : "").">".$rorg['namaorganisasi']."</option>";
    	} else {
    		$optafd.="<option value='".$rorg['kodeorganisasi']."'>".$rorg['namaorganisasi']."</option>";
    	}
    }
    echo $optafd;
    break;
    case'getblok':
    $optafd2="<option value=''></option>";
	// $a=$param['blok'];
	// exit("Error:$a");
    $sorg="select distinct indukblok,namaindukblok from ".$dbname.".organisasi where tipe='BLOK' and kodeorganisasi like '".$param['afdeling']."%'";
	$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
	$qorg->setFetchMode(PDO::FETCH_ASSOC);
    while($rorg=$qorg->fetch()){
		$select='';
		if($param['blok']==$rorg['kodeorganisasi']){
			$select="selected";
        }
            $optafd2.="<option value='".$rorg['indukblok']."' ".$select.">".$rorg['namaindukblok']."</option>";
	}
	echo $optafd2;
    break;
    case'getSPH':
        $sph=0;
    $sorg="select indukblok, sum(jumlahpokok) as pokokthnini, sum(luasareaproduktif) as hathnini, tahuntanam from ".$dbname.".setup_blok where indukblok ='".$param['blok']."' and tahuntanam<=".(intval(date('Y'))-3)." 
    group by indukblok";
    //echo $sorg;
	$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
	$qorg->setFetchMode(PDO::FETCH_ASSOC);
    while($rorg=$qorg->fetch()){
        $pokok=$rorg['pokokthnini'];
        $luas=$rorg['hathnini'];
        $tt=$rorg['tahuntanam'];
    }
    @$sph=round($pokok/$luas);
        $tahuntahuntahun = substr($param['tanggal'], 6, 4);
        $bulanbulanbulan = substr($param['tanggal'], 3, 2);
        $tanggaltanggaltanggal = substr($param['tanggal'], 0, 2);
        $afdelingafdelingafdeling = substr($param['blok'], 0, 6);
        // cek bjr via SETUP
        $query = "SELECT sum(bjr) as totalbjr, count(kodeorg) as jumlah FROM ".$dbname.".`kebun_5bjr` a WHERE a.`kodeorg` like '".$param['blok']."%' ";
        //echo $query;
        $qDetail = $owlPDO->query($query)or die(print " Gagal: ".PDOException::getMessage());
        $qDetail->setFetchMode(PDO::FETCH_ASSOC);
        $bjrsetup = "";
        while ($rDetail = $qDetail->fetch()) {
        	$bjr = ($rDetail['totalbjr']/$rDetail['jumlah']);
            //echo $bjr.'xxxx';
        }
        $basis = 0;
        // Get Areal Statement (Setup Blok)
        $qBlok = selectQuery($dbname, 'setup_blok', '*', "indukblok='".$param['blok']."'");
        $resBlok = fetchData($qBlok);
        if (empty($resBlok))
        	exit("Warning: Areal Statement blok ".$param['blok']." belum ada");
        $dataBlok = $resBlok[0];
		
		
        // Get Basis Panen dan Ketentuan Premi
        $whereBasis = "kodeorg like '".$param['blok']."%' and  tahun = '".substr(tanggalsystemn($param['tanggal']),0,7)."'";
        $qBasis = selectQuery($dbname, 'kebun_5basispanen2', "*", $whereBasis);
        //exit('warning : '.$qBasis);
        $resBasis = fetchData($qBasis);
        if (empty($resBasis))
        	// exit("Warning: Basis Panen belum ada untuk\nPT => ".
        		// $_SESSION['org']['kodeorganisasi']."\nTopografi => ".
        		// $dataBlok['topografi']."\nBJR => ".number_format($bjr, 2)."");
        $rumusPremi = $resBasis[0];
        $basis = $rumusPremi['basis'];
        if ($bjr == '') {
        	$bjr = 0;
        }
        echo $sph."###".$basis."###".number_format($bjr, 2)."###".$luas."###".$pokok."###".$tt;
        break;
}
?>