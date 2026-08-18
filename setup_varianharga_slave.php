<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$tipe = checkPostGet('tipe', '');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$vhc = checkPostGet('vhc', '');
$kelbrg = checkPostGet('kelbrg', '');
$persen = checkPostGet('persen', '');
$rupiah = checkPostGet('rupiah', '');

$nmvhc=makeOption($dbname,'vhc_5master','kodevhc,detailvhc');
$nmkelbrg=makeOption($dbname,'log_5klbarang','kode,kelompok');

$no=0;


$digit=2;

switch ($method) {
	
	case'getdata':
		
		$optvhc=$optkelbrg= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		
		if($tipe=='vhc'){
			
			
			
			$str = "SELECT * FROM " . $dbname . ".vhc_5master
				where kodeorg='".$unit."' order by kodevhc asc";
				// echo $str;exit("Error:A");
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if($vhc==$bar['kodevhc']){
					$select="selected=selected";
				} else {
					$select="";
				}
				$optvhc.="<option ".$select." value=" . $bar['kodevhc'] . ">" . $bar['kodevhc'] . " - " . $bar['detailvhc'] . "</option>";
			}
		} 
		
		if($tipe=='inv'){
			$str = "SELECT * FROM " . $dbname . ".log_5klbarang
				where kode like '3%' order by kode asc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if($kelbrg==$bar['kode']){
					$select="selected=selected";
				} else {
					$select="";
				}
				$optkelbrg.="<option ".$select." value=" . $bar['kode'] . ">" . $bar['kode'] . " - " . $bar['kelompok'] . "</option>";
			}
		}
		
		echo $optvhc."####".$optkelbrg;

	break;	
	
	
	case'insert':
		
		$str = "INSERT INTO  ".$dbname.".`setup_varianharga` 
			(`unit`, `tanggal`, `tipe`, `kodevhc`, `kelompokbarang`,
			`persen`,`rupiah`,`createby`, `createtime`,`updateby`)
			values ('".$unit."','".$tgl."','".$tipe."','".$vhc."','".$kelbrg."',
			'".$persen."','".$rupiah."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	
	case'update':
		
		$str = "update ".$dbname.".`setup_varianharga` set 
				`persen`='".$persen."',`rupiah`='".$rupiah."',`updateby`='".$_SESSION['standard']['userid']."'
				where `unit`='".$unit."' and `tanggal`='".$tgl."' and `tipe`='".$tipe."' and `kodevhc`='".$vhc."' and`kelompokbarang`='".$kelbrg."'";
			
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
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
		
		$where='';
		
		if($unit!=''){ 
			$where.=" and unit='".$unit."'";
		}
		if($tipe!=''){ 
			$where.=" and tipe='".$tipe."'";
		}
		
		$str="SELECT count(*) as jmlhrow
              from ".$dbname.".setup_varianharga where 1=1 ".$where;
        $query2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$form.="<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>";
		$form.="<thead>";
		$form.="<tr class=rowheader>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['unit']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['tipe']."</td>";
			$form.="<td align=center colspan=2>".$_SESSION['lang']['kodevhc']."</td>";
			$form.="<td align=center colspan=2>".$_SESSION['lang']['kelompokbarang']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['persen']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['rupiah']."</td>";
			$form.="<td align=center rowspan=2>".$_SESSION['lang']['action']."</td>";
		$form.="</tr>";
		$form.="<tr class=rowheader>";
			$form.="<td align=center>".$_SESSION['lang']['kode']."</td>";
			$form.="<td align=center>".$_SESSION['lang']['nama']."</td>";
			$form.="<td align=center>".$_SESSION['lang']['kode']."</td>";
			$form.="<td align=center>".$_SESSION['lang']['nama']."</td>";
		$form.="</tr>";
		$form.="</thead>";
		$form.="</thead><tbody>";
		$no = 0;
		//kodeunit='".$unit."'
		
		$str="select * from ".$dbname.".setup_varianharga  where 1=1 ".$where." order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$form.="<tr class=rowcontent>";
				$form.="<td align=center>".$no."</td>";
				$form.="<td align=left>".$bar['unit']."</td>";
				$form.="<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
				$form.="<td align=left>".$bar['tipe']."</td>";
				$form.="<td align=left>".$bar['kodevhc']."</td>";
				$form.="<td align=left>".$nmvhc[$bar['kodevhc']]."</td>";
				$form.="<td align=left>".$bar['kelompokbarang']."</td>";
				$form.="<td align=left>".$nmkelbrg[$bar['kelompokbarang']]."</td>";
				$form.="<td align=right>".number_format($bar['persen'],$digit)."</td>";
				$form.="<td align=right>".number_format($bar['rupiah'],$digit)."</td>";
				$form.="<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' 
					onclick=\"edit('".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['tipe']."',
								'".$bar['kodevhc']."','".$bar['kelompokbarang']."','".$bar['persen']."','".$bar['rupiah']."');\"></td>";
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
	
	
	
	
	
	
	
	
	case'':
	
	break;
	

	
}
?>