<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$tipe = checkPostGet('tipe','');
$ptoptinduk = checkPostGet('ptoptinduk','');
$ptfreeinduk = checkPostGet('ptfreeinduk','');
$namaptfreeinduk = checkPostGet('namaptfreeinduk','');
$ptopt = checkPostGet('ptopt','');
$ptfree = checkPostGet('ptfree','');
$namaptfree = checkPostGet('namaptfree','');
$noakun = checkPostGet('noakun','');

if($ptopt==''){
	$pt=$ptfree;
}else{
	$pt=$ptopt;
}

if($ptoptinduk==''){
	$ptinduk=$ptfreeinduk;
}else{
	$ptinduk=$ptoptinduk;
}


$find_tipe = checkPostGet('find_tipe','');
$find_ptinduk = checkPostGet('find_ptinduk','');
$find_pt = checkPostGet('find_pt','');
$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");
switch ($method) 
{
	case 'insert':

		if($pt=='' or $ptinduk=='' or $tipe =='' or $noakun==''){
			exit('Warning : Kolom Tipe, Perusahaan Induk, Perusahaan, Nomor Akun tidak boleh kosong');
		}
		if($pt == $ptinduk){
			exit('Warning : Perusahaan Induk tidak boleh sama dengan Perusahaan.');
		}
		//cek apakah pt sudah ada ??
		$str = "select count(*) as jumlah from ".$dbname.".keu_5organisasi where induk ='".$pt."' and kodeorg = '".$ptinduk."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$countitem = $bar['jumlah'];
		// if($countitem >= 1)
		// {
			// exit("Warning : Perusahaan sudah pernah terdaftar sebagai induk.");
		// }
		
		//cek apakah pt sudah ada ??
		$str = "select count(*) as jumlah from ".$dbname.".keu_5organisasi where kodeorg = '".$pt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$countitem = $bar['jumlah'];
		
		#ambil unit HO
		$strx = "select * from ".$dbname.".organisasi where induk = '".$pt."' and tipe ='HOLDING'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($barx = $resx->fetch()){
			$unit=$barx['kodeorganisasi'];
		}
		
		#ambil noakun investasi saham
		$strx = "select noakun from ".$dbname.".keu_5akun where pemilik='".$unit."' and noakun like '12402%'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx = $resx->fetch();
		$noakun_investasisaham=$barx['noakun'];
		
		#ambil noakun hutang modal
		$strx = "select noakun from ".$dbname.".keu_5akun where pemilik='".$unit."' and noakun like '21501%'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx = $resx->fetch();
		$noakun_hutangmodal=$barx['noakun'];
		
		#ambil noakun Piutang Pemegang saham
		$strx = "select noakun from ".$dbname.".keu_5akun where pemilik='".$unit."' and noakun like '11403%'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx = $resx->fetch();
		$noakun_piutangsaham=$barx['noakun'];
		
		#ambil noakun Piutang Dividen
		$strx = "select noakun from ".$dbname.".keu_5akun where pemilik='".$unit."' and noakun like '12101%'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx = $resx->fetch();
		$noakun_piutangdividen=$barx['noakun'];
		
		#ambil HO induk
		$strx = "select * from ".$dbname.".organisasi where induk = '".$ptinduk."' and tipe ='HOLDING'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($barx = $resx->fetch()){
			$unitinduk=$barx['kodeorganisasi'];
		}

		if ($tipe=='EKSTERNAL') {
			$unit=$pt;
			$unitinduk=$ptinduk;
		}
		
		if ($countitem >= 1) {
			exit("Warning : Perusahaan sudah pernah terdaftar sebelumnya.");
		} else {
			$str = "insert into ".$dbname.".keu_5organisasi values ('".$pt."','".$unit."','".$ptinduk."','".$unitinduk."','".$namaptfreeinduk."','".$namaptfree."','".$tipe."','".$noakun."','".$noakun_investasisaham."','".$noakun_hutangmodal."','".$noakun_piutangsaham."','".$noakun_piutangdividen."')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal,".addslashes($e->getMessage());
			}
		}
	break;

    case 'update':
	
		if($pt=='' or $ptinduk=='' or $tipe =='' or $noakun==''){
			exit('Warning : Kolom Tipe, Perusahaan Induk, Perusahaan, Nomor Akun tidak boleh kosong');
		}
		if($pt == $ptinduk){
			exit('Warning : Perusahaan Induk tidak boleh sama dengan Perusahaan.');
		}
		
		#ambil unit HO
		$strx = "select * from ".$dbname.".organisasi where induk = '".$pt."' and tipe ='HOLDING'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($barx = $resx->fetch()){
			$unit=$barx['kodeorganisasi'];
		}
		
		$strx = "select * from ".$dbname.".organisasi where induk = '".$ptinduk."' and tipe ='HOLDING'";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($barx = $resx->fetch()){
			$unitinduk=$barx['kodeorganisasi'];
		}
		
		$str = "update ".$dbname.".keu_5organisasi set induk='".$ptinduk."',indukunit='".$unitinduk."', unit='".$unit."', noakun='".$noakun."' where kodeorg = '".$pt."'";
        try {
        	$owlPDO->exec($str);
        } catch (PDOException $e) {
        	echo " Gagal,".addslashes($e->getMessage());
        }
	break;
	
	case 'delete':
		$pt = checkPostGet('pt','');
		$unit = checkPostGet('unit','');
		$unitinduk = checkPostGet('unitinduk','');
		$ptinduk = checkPostGet('ptinduk','');

		$str = "delete from ".$dbname.".keu_5organisasi where induk='".$ptinduk."' and indukunit='".$unitinduk."' and unit='".$unit."' and kodeorg = '".$pt."'";
		//exit('error'.$str);
        try {
        	$owlPDO->exec($str);
        } catch (PDOException $e) {
        	echo " Gagal,".addslashes($e->getMessage());
        }
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
		if($find_tipe!=''){ 
			$where.=" and tipe LIKE  '%".$find_tipe."%'";
		}
		if($find_ptinduk!=''){ 
			$where.=" and UPPER(induk) LIKE  '%".strtoupper($find_ptinduk)."%'";
		}
		if($find_pt!=''){ 
			$where.=" and UPPER(kodeorg) LIKE  '%".strtoupper($find_pt)."%'";
		}
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_5organisasi
				where 0=0 ".$where.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0 min-width=700px>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['tipe']."</td>
				<td align=center>".$_SESSION['lang']['pt']." ".$_SESSION['lang']['induk']."</td>
				<td align=center>".$_SESSION['lang']['pt']."</td>
				<td align=center>".$_SESSION['lang']['noakun']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$optOrg = makeOption($dbname,"organisasi",'kodeorganisasi,namaorganisasi');
		$optAkun = makeOption($dbname,"keu_5akun",'noakun,namaakun');
		$str = "select * from ".$dbname.".keu_5organisasi 
				where 0=0 ".$where." order by induk asc LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['tipe']."</td>";
            if($bar['tipe']=='EKSTERNAL'){
				$tab.="<td>".$bar['induk']." - ".$bar['namainduk']."</td>";
			}else{
				$tab.="<td>".$bar['induk']." - ".$optOrg[$bar['induk']]."</td>";
			}	
            
			if($bar['tipe']=='EKSTERNAL'){
				$tab.="<td>".$bar['kodeorg']." - ".$bar['namaunit']."</td>";	
			}else{
				$tab.="<td>".$bar['kodeorg']." - ".$optOrg[$bar['kodeorg']]."</td>";
			}
            $tab.="<td>".$bar['noakun']." - ".$optAkun[$bar['noakun']]."</td>";
            
			$tab.="<td align=center>
				<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kodeorg']."','".$bar['induk']."','".$bar['indukunit']."','".$bar['unit']."');\"></td>";
		    
            $tab.="</tr>";
        }
		$totrows=ceil($jlhbrs/$limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=6 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

	case'getkodeorg':
		$str = "SELECT * FROM " . $dbname . ".organisasi where tipe='PT' and kodeorganisasi !='".$ptoptinduk."' and kodeorganisasi not in (SELECT kodeorg FROM keu_5organisasi where induk='".$ptoptinduk."') and kodeorganisasi not in (select induk from ".$dbname.".keu_5organisasi where kodeorg = '".$ptoptinduk."') order by kodeorganisasi asc";
		// exit('error'.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Pt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			$Pt.="<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		
	echo $Pt;
	break;
    default:
}
?>
