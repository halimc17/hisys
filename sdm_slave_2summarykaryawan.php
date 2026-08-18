<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
$param = (($_POST==array())?$_GET:$_POST);
$proses = checkPostGet('proses','');
$pt = checkPostGet('pt','');
$unit = checkPostGet('unit','');
$tipe = checkPostGet('tipe','');
$tanggal = checkPostGet('tanggal','');
$periode = checkPostGet('periode','');
$region = checkPostGet('region','');
$lokTgs = checkPostGet('lokTgs','');
$tipekary = checkPostGet('tipekary','');
$tglPrd=explode("-",$tanggal);
@$periodeGj=$tglPrd[2]."-".$tglPrd[1];
$optTip=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$optGol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
$optOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$opttipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
$optPend=makeOption($dbname,'sdm_5pendidikan','levelpendidikan,kelompok');



    
    $str="select * from ".$dbname.".sdm_5tipekaryawan
        where 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);	
    while($bar=$res->fetch()){
        $tipekar[$bar->id]=$bar->id;
        $artitkr[$bar->id]=$bar->tipe;
    }

    if($region!=''){
        $str="select * from ".$dbname.".bgt_regional_assignment
            where regional = '".$region."'";        
    }else{
        $str="select * from ".$dbname.".bgt_regional_assignment
            where 1";
        
    }
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        if($region!=''){
            $regional[$bar->kodeunit]=$bar->kodeunit;            
        }else{
            $unitreg[$bar->kodeunit]=$bar->regional;
            $regional[$bar->regional]=$bar->regional;            
        }        
    }
    
if($proses=='preview'||$proses=='excel')
{

	
	if($unit==''){
		$where = "where induk='".$pt."' and length(kodeorganisasi)='4'";
	}else{
		$where = "where kodeorganisasi = '".$unit."'";
	}


	$arrgolongan=array();
	$arrpendi=array();
	$arrtipekaryawan=array();
	$arrumur=array();
	$arrjk=array();
	$arrunit=array();
	$arrdetail=array();
	$arrlevel=array();
	$arrNpwp=array();
	$artitkr=array();
	$arrunit=array();
	$jlhsd=$jlhsmp=$jlhsma=$jlhdip=$jlhsone=$jlhstwo=$jmhsthree=array();

	$strunit="select * from ".$dbname.".organisasi  ".$where." ";
	$resunit=fetchdata($strunit);
	foreach($resunit as $barunit){
		$rowcont=0;
		$strcount="select count(*) as jumlah from ".$dbname.".datakaryawan_bulanan where 
		tanggalmasuk <= '".$periode."-01' and 
		(tanggalkeluar>= '".$periode."-01' or tanggalkeluar = '0000-00-00') and lokasitugas='".$barunit['kodeorganisasi']."' and periode='".$periode."'";
		
		$rescount=fetchdata($strcount);
		foreach($rescount as $barcount){
			$rowcont=$barcount['jumlah'];
		}
			
		if($rowcont>0){
			$str="select * from ".$dbname.".datakaryawan_bulanan where 
			tanggalmasuk <= '".$periode."-01' and 
			(tanggalkeluar>= '".$periode."-01' or tanggalkeluar = '0000-00-00')  and lokasitugas='".$barunit['kodeorganisasi']."'  and periode='".$periode."' order by kodegolongan asc,tipekaryawan desc";
			
		}else{
			$str="select * from ".$dbname.".datakaryawan where 
			tanggalmasuk <= '".$periode."-01' and 
			(tanggalkeluar>= '".$periode."-01' or tanggalkeluar = '0000-00-00') and  lokasitugas='".$barunit['kodeorganisasi']."'  order by kodegolongan asc,tipekaryawan desc";
		}

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		
		while($bar=$res->fetch()){
			$arrgolongan[$bar['lokasitugas']][$bar['kodegolongan']] = $bar['kodegolongan'];
			$arrpendi[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['levelpendidikan']] = $bar['levelpendidikan'];
			$arrtipekaryawan[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']] = $bar['kodejabatan'];
			if($bar['jeniskelamin']=='L'){
				@$arrjk[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]['L']+= 1;
			}else{
				@$arrjk[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]['P']+= 1;
			}

			$tglcetak= new DateTime(tglakhir($periode."-01"));
			$tgllahir= new DateTime($bar['tanggallahir']);
			$selesih= $tglcetak->diff($tgllahir);
			if($selesih->y>=18 && $selesih->y<=30){
				@$arrumur[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]['U1']+=1;
			}else if($selesih->y>=31 && $selesih->y<=40){
				@$arrumur[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]['U2']+=1;
			}else if($selesih->y>=41 && $selesih->y<=50){
				@$arrumur[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]['U3']+=1;
			}else if($selesih->y>=51 && $selesih->y<=55){
				@$arrumur[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]['U4']+=1;
			}else if($selesih->y>=56){
				@$arrumur[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]['U5']+=1;
			}
	
			$jlhpendidikan[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['levelpendidikan']][$bar['kodejabatan']]+=1;

			@$arrlevel[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['levelpendidikan']]+= 1;
			@$arrdetail[$bar['lokasitugas']][$bar['kodegolongan']][$bar['tipekaryawan']][$bar['kodejabatan']]+= 1;
		}
	}

	if($proses=='preview'){
		$tab.="<table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>";
	}else{
		$tab.="<table class=sortable cellspacing=1 border=1>";
	}

	$tab.="	<thead>
			<tr class=rowheader>
				<th rowspan=2>Lokasi Tugas</th>
				<th rowspan=2>Golongan</th>
				<th rowspan=2>Tipe Karyawan</th>
				<th rowspan=2>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan=2>Jumlah<br>Orang</th>
				<th colspan=".count($optPend)." align=center>Pendidikan</th>
				<th colspan=2 align=center>Jenis Kelamin</th>
				<th colspan=5 align=center>Usia</th>
			</tr>
			<tr class=rowheader>";ksort($optPend);
			foreach ($optPend as $id => $nma) {
				$tab.="<th>".$nma."</th>";
			}
			$tab.="
				<th align=center>L</th>
				<th align=center>P</th>
				<th nowrap>18-30</th>
				<th nowrap>31-40</th>
				<th nowrap>41-50</th>
				<th nowrap>51-55</th>
				<th nowrap><u>></u> 56</th>
			</tr></thead><tbody>";

		$str="select * from ".$dbname.".organisasi   ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrunit[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
		}

		if(!empty($arrunit)){ 
			$totjlhorg=$totsd=$totsmp=$totsma=$totdip=$totsone=$totstwo=$totsthree=0;
			$totjkl=$totjkp=0;$nmkbn='';
			if(!empty($arrunit)){
			foreach ($arrunit as $key => $val) {
				$subtot=$subtotsd=$subtotsmp=$subtotsma=$subtotdip=$subtotsone=$subtotstwo=$subtotsthree=0;
				$subtotjkl=$subtotjkp=array();
				if(!empty($arrtipekaryawan[$key])){
					ksort($arrtipekaryawan[$key]);
					foreach ($arrtipekaryawan[$key] as $kgol => $arrtipe) {
						foreach ($arrtipe as $valtipe => $arrjab) {
							foreach ($arrjab as $kdjab) {
								$tab.="<tr class=rowcontent>";
								$tab.="<td nowrap>".($nmkbn != $val ? $key." - ".$val : '')."</td>";
								$tab.="<td align=center>".$optGol[$kgol]."</td>";
								$tab.="<td align=center>".$optTip[$valtipe]."</td>";
								$tab.="<td align=center>".getNamaJabatan($kdjab)."</td>";
								$tab.="<td align=center>".$arrdetail[$key][$kgol][$valtipe][$kdjab]."</td>";
								$subtotjlhorg[$key] += $arrdetail[$key][$kgol][$valtipe][$kdjab];
								$totjlhorg += $arrdetail[$key][$kgol][$valtipe][$kdjab];
								if ($proses == 'excel') {
									$xxx ='';
								}else{
									$xxx ='color:blue;';
								}
								foreach ($optPend as $id => $nma) {
									if($jlhpendidikan[$key][$kgol][$valtipe][$id][$kdjab] != '' || $jlhpendidikan[$key][$kgol][$valtipe][$id][$kdjab] > 0){
										$click = "onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','PENDIDIKAN','".$id."','".$kdjab."')";
										$pointer = "cursor:pointer;";
									}else{
										$click = '';
										$pointer = '';
									}
									$tab.="<td align=center title='Klik untuk melihat detail' style='".$xxx."".$pointer."' ".$click.">".$jlhpendidikan[$key][$kgol][$valtipe][$id][$kdjab]."</td>";
									$subtotpendidikan[$id][$key] += $jlhpendidikan[$key][$kgol][$valtipe][$id][$kdjab];
									$totpendidikan[$id] += $jlhpendidikan[$key][$kgol][$valtipe][$id][$kdjab];
								}
		
								$jlhP=$jlhL=0;
								$jlhU1=$jlhU2=$jlhU3=$jlhU4=$jlhU5=0;
								@$subtotjkl[$key]+=$arrjk[$key][$kgol][$valtipe][$kdjab]['L'];
								@$subtotjkp[$key]+=$arrjk[$key][$kgol][$valtipe][$kdjab]['P'];
		
								@$subtotU1[$key]+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U1'];
								@$subtotU2[$key]+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U2'];
								@$subtotU3[$key]+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U3'];
								@$subtotU4[$key]+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U4'];
								@$subtotU5[$key]+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U5'];
		
								$totjkl+=$arrjk[$key][$kgol][$valtipe][$kdjab]['L'];
								$totjkp+=$arrjk[$key][$kgol][$valtipe][$kdjab]['P'];
		
								$totU1+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U1'];
								$totU2+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U2'];
								$totU3+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U3'];
								$totU4+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U4'];
								$totU5+=$arrumur[$key][$kgol][$valtipe][$kdjab]['U5'];
								if($arrjk[$key][$kgol][$valtipe][$kdjab]['L'] != ''){
									$clickl = " onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','KELAMIN','L','".$kdjab."')";
								}else{
									$clickl = "";
								}
								if($arrjk[$key][$kgol][$valtipe][$kdjab]['P'] != ''){
									$clickp = " onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','KELAMIN','P','".$kdjab."')";
								}else{
									$clickp = "";
								}
								$tab.="<td align=center title='Klik untuk melihat detail' style='cursor:pointer;".$xxx."' ".$clickl.">".$arrjk[$key][$kgol][$valtipe][$kdjab]['L']."</td>";
								$tab.="<td align=center title='Klik untuk melihat detail' style='cursor:pointer;".$xxx."' ".$clickp.">".$arrjk[$key][$kgol][$valtipe][$kdjab]['P']."</td>";
								$tab.="<td align=center title='Klik untuk melihat detail' style='cursor:pointer;".$xxx."'  onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','USIA','U1','".$kdjab."')>".$arrumur[$key][$kgol][$valtipe][$kdjab]['U1']."</td>";
								$tab.="<td align=center title='Klik untuk melihat detail' style='cursor:pointer;".$xxx."'  onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','USIA','U2','".$kdjab."')>".$arrumur[$key][$kgol][$valtipe][$kdjab]['U2']."</td>";
								$tab.="<td align=center title='Klik untuk melihat detail' style='cursor:pointer;".$xxx."'  onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','USIA','U3','".$kdjab."')>".$arrumur[$key][$kgol][$valtipe][$kdjab]['U3']."</td>";
								$tab.="<td align=center title='Klik untuk melihat detail' style='cursor:pointer;".$xxx."'  onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','USIA','U4','".$kdjab."')>".$arrumur[$key][$kgol][$valtipe][$kdjab]['U4']."</td>";
								$tab.="<td align=center title='Klik untuk melihat detail' style='cursor:pointer;".$xxx."'  onclick=lihatdetail('".$key."','".$kgol."','".$valtipe."','USIA','U5','".$kdjab."')>".$arrumur[$key][$kgol][$valtipe][$kdjab]['U5']."</td>";
								$tab.="</tr>";
								$nmkbn = $val;
							}
						}
					}
				}
				if(!empty($arrtipekaryawan[$key])){
					$tab.="	<tr class=rowcontent style='background-color:#ccc;font-weight:bold;text-transform: uppercase;'>
								<td align=center colspan=4 >TOTAL ".$key." - ".getNamaOrg($key)."</td>
								<td align=center>".$subtotjlhorg[$key]."</td>";
								foreach ($optPend as $id => $nma) {
									$tab.="<td align=center>".$subtotpendidikan[$id][$key]."</td>";
								}
								$tab.="<td align=center>".$subtotjkl[$key]."</td>";
								$tab.="<td align=center>".$subtotjkp[$key]."</td>";
								$tab.="<td align=center>".$subtotU1[$key]."</td>";
								$tab.="<td align=center>".$subtotU2[$key]."</td>";
								$tab.="<td align=center>".$subtotU3[$key]."</td>";
								$tab.="<td align=center>".$subtotU4[$key]."</td>";
								$tab.="<td align=center>".$subtotU5[$key]."</td>";
					$tab.=" </tr>";
				}
			}
			
			if(!empty($arrunit)){
				$tab.="	<tr class=rowcontent style='background-color:#ccc;font-weight:bold;text-transform: uppercase;'>
							<td align=center colspan=4 >GRAND TOTAL ".$pt." - ".getNamaOrg($pt)."</td>
							<td align=center>".$totjlhorg."</td>";
							foreach ($optPend as $id => $nma) {
								$tab.="<td align=center>".$totpendidikan[$id]."</td>";
							}
							$tab.="<td align=center>".$totjkl."</td>";
							$tab.="<td align=center>".$totjkp."</td>";
							$tab.="<td align=center>".$totU1."</td>";
							$tab.="<td align=center>".$totU2."</td>";
							$tab.="<td align=center>".$totU3."</td>";
							$tab.="<td align=center>".$totU4."</td>";
							$tab.="<td align=center>".$totU5."</td>";
				$tab.=" </tr>";
			}
		}
		$tab.="</tbody></table>";
	}
}
switch($proses)
{
	case'getunit':
		$optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)='4'";
		$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while($rKbn=$qKbn->fetch())
		{
			$optKebun.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
		}
		
		echo $optKebun;
	break;
	case'getperiode':
		$optPeriode='';
		if($unit=='')
		{
			$str="select distinct(a.periode) from ".$dbname.".sdm_5periodegaji a 
					left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
					where b.induk ='".$pt."' and a.sudahproses='1' order by periode desc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				$optPeriode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
			}
		}
		else
		{
			if($opttipeorg[$unit]=='HOLDING'){
				$str="select distinct(a.periode) from ".$dbname.".sdm_5periodegaji a 
				left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
				where b.induk ='".$pt."' and a.sudahproses='1' order by periode desc";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch())
				{
					$optPeriode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
				}
				
			}else{
				$str="select distinct(periode) from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."'
						and sudahproses='1' order by periode desc";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch())
				{
					$optPeriode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
				}
			}
		}
		echo $optPeriode;
	break;
    case'preview':
        echo $tab;
    break;
    case'level1':
        echo $tab;
    break;
    case'getNmKar':
        $tab.="<script language=javascript src=js/generic.js></script><script language=javascript src=js/zTools.js></script>
               <script language=javascript src=js/sdm_2summarykaryawan.js></script>";
        $tab.="<link rel=stylesheet type=text/css href=style/generic.css>";
        $tab.="<img onclick=parent.detexcel(event,'".$lokTgs."','".$tipekary."','".$tanggal."') src=images/excel.jpg class=zImgBtn title='MS.Excel'>";
        $tab.="<table width=100% cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr><td align=center>".$_SESSION['lang']['nomor']."</td>";
        $tab.="<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
			   <td align=center>".$_SESSION['lang']['jabatan']."</td></tr></thead><tbody>";
        if($tipekary!=4){
        $sdatakar="select namakaryawan,tipekaryawan,lokasitugas, kodejabatan from ".$dbname.".datakaryawan 
                   where tanggalmasuk <= '".tanggalsystem($tanggal)."' and 
                   (tanggalkeluar>= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
                   and lokasitugas='".$lokTgs."' and tipekaryawan='".$tipekary."' order by namakaryawan asc";
        }else{
            $sdatakar="select a.*  from ".$dbname.".datakaryawan a 
          left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
          tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar>= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
          and periodegaji='".$periodeGj."'   and tipekaryawan=4 and lokasitugas='".$lokTgs."'
          and idkomponen=1";
        }
        $qdatakar=$owlPDO->query($sdatakar) or die(print " Gagal: ".PDOException::getMessage());
		$qdatakar->setFetchMode(PDO::FETCH_ASSOC);
		while($rdatakar=  $qdatakar->fetch()){
            $nor+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$nor."</td>";
            $tab.="<td>".$rdatakar['namakaryawan']."</td>";
            $tab.="<td>".$jab[$rdatakar['kodejabatan']]."</td>";
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
    break;
    case'excelDt':
        $bgcolor="bgcolor=#DEDEDE";
        $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
        $tab.="<tr ".$bgcolor."><td>".$_SESSION['lang']['nomor']."</td>";
        $tab.="<td>".$_SESSION['lang']['namakaryawan']."</td></tr></thead><tbody>";
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$optTip[$tipekary]."</td>";
        if($tipekary!=4){
            $sdatakar="select namakaryawan,tipekaryawan,lokasitugas from ".$dbname.".datakaryawan 
                       where tanggalmasuk <= '".tanggalsystem($tanggal)."' and 
                       (tanggalkeluar>= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
                       and lokasitugas='".$lokTgs."' and tipekaryawan='".$tipekary."' order by namakaryawan asc";
        }else{
            $sdatakar="select a.*  from ".$dbname.".datakaryawan a 
              left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
              tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar>= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
              and periodegaji='".$periodeGj."'   and tipekaryawan=4 and lokasitugas='".$lokTgs."'
              and idkomponen=1";
        }
        $qdatakar=$owlPDO->query($sdatakar) or die(print " Gagal: ".PDOException::getMessage());
		$qdatakar->setFetchMode(PDO::FETCH_ASSOC);
        while($rdatakar=  $qdatakar->fetch()){
            $nor+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$nor."</td>";
            $tab.="<td>".$rdatakar['namakaryawan']."</td>";
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";  
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="dtDet_".$tanggal."_".$lokTgs;
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
            fclose($handle);
        }
    break;
    case'excel2':
        $bgcolor="bgcolor=#DEDEDE";
                     $str="select * from ".$dbname.".datakaryawan
                    where tipekaryawan!=4  and tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar>= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') ";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch()){
                    if($region!=''){
                        $qwe=$bar->lokasitugas;
                    }else{
                        $qwe=$unitreg[$bar->lokasitugas];
                    }
                    $jumlahkar[$qwe][$bar->tipekaryawan]+=1;
                }
                $sdmGaji="select a.*  from ".$dbname.".datakaryawan a 
                      left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
                      tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar>= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
                      and periodegaji='".$periodeGj."'   and tipekaryawan=4
                      and idkomponen=1";
            $qsdmGaji=$owlPDO->query($sdmGaji) or die(print " Gagal: ".PDOException::getMessage());
			$qsdmGaji->setFetchMode(PDO::FETCH_ASSOC);
            while($rsdmGaji=$qsdmGaji->fetch()){
                   if($region!=''){
                        $qwe=$rsdmGaji['lokasitugas'];
                    }else{
                        $qwe=$unitreg[$rsdmGaji['lokasitugas']];
                    }
                    $jumlahkar[$qwe][$rsdmGaji['tipekaryawan']]+=1;
            }
                   $brd=0;
                   $bgcolor="";
                if($region==''){
                    $region=$_SESSION['lang']['regional'];
                }else{
                    if($proses!='excel')
                    $tab.="<img onclick=level1excel(event,'sdm_slave_2summarykaryawan.php','".$tanggal."','".$region."') src=images/excel.jpg class=zImgBtn title='MS.Excel'>";
                }
            $tab.="
                <table width=100% cellspacing=1 border=1>
                <thead>
                <tr>
                    <td ".$bgcolor.">".$region."</td>";
                    if(!empty($regional))foreach($regional as $reg)
                        if($region!='')
                        $tab.="<td ".$bgcolor." align=center>".$reg."</td>";
                    $tab.="
                    <td ".$bgcolor." align=center>".$_SESSION['lang']['total']."</td>
                </tr>        
                </thead>
                <tbody>";
                if(!empty($tipekar))foreach($tipekar as $tkr){
                    $tab.="<tr class=rowcontent>
                    <td>".$artitkr[$tkr]."</td>";
                    $total[$tkr]=0;
                    if(!empty($regional))foreach($regional as $reg){
                        $islnk="";
                        if($jumlahkar[$reg][$tkr]!=0){
                        $islnk=" style='cursor:pointer;' onclick=getKary('".$reg."','".$tkr."',event)";
                        }
                        $tab.="<td align=right  ".$islnk.">".number_format($jumlahkar[$reg][$tkr])."</td>";
                        $total[$tkr]+=$jumlahkar[$reg][$tkr];
                        $totalgrand[$reg]+=$jumlahkar[$reg][$tkr];            
                    }

                    $tab.="
                    <td align=right >".number_format($total[$tkr])."</td>
                    </tr>";            
                }
                $tab.="<tr class=rowcontent>
                <td>".$_SESSION['lang']['total']."</td>";
                $totalnya=0;
                if(!empty($regional))foreach($regional as $reg){
                    $tab.="<td align=right>".number_format($totalgrand[$reg])."</td>";
                    $totalnya+=$totalgrand[$reg];            
                }
                $tab.="
                <td align=right>".number_format($totalnya)."</td>
                </tr>";            

                $tab.="</tbody></table>";
                $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="summary_karyawan_".$tanggal."_".$region;
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
            fclose($handle);
        }
    break;
    case'excel':
	

        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="summary_karyawan_".$pt."_".($unit == ''?'ALL UNIT':$unit)."_".$periode."_".$region;
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
            fclose($handle);
        }
    break;
    case 'lihatdetail':
		echo"<div class='table-scroll'>";
		echo"
				<table class=sortable border=0 cellspacing=1 cellpadding=5 width=100%>
				<thead>
				<tr class=rowheader>
					<th align=center>No.</th>
						<th align=center>".$_SESSION['lang']['nik']."</th>
						<th align=center>".$_SESSION['lang']['nama']."</th>
						<th align=center>".$_SESSION['lang']['functionname']."</th>
						<th align=center>".$_SESSION['lang']['kodegolongan']."</th>
						<th align=center>".$_SESSION['lang']['lokasitugas']."</th>
						<th align=center>".$_SESSION['lang']['pt']."</th>
						<th align=center>".$_SESSION['lang']['noktp']."</th>
						<th align=center>".$_SESSION['lang']['pendidikan']."</th>
						<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['statuspajak'])."</th>
						<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['statusperkawinan'])."</th>
						<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['jumlahanak'])."</th>
						<th align=center>".$_SESSION['lang']['tanggalmasuk']."</th>
						<th align=center>".$_SESSION['lang']['tanggalkeluar']."</th>
						<th align=center>".str_replace(" ","<br>",$_SESSION['lang']['tipekaryawan'])."</th>
						<th align=center>".$_SESSION['lang']['status']." ".$_SESSION['lang']['karyawan']."</th>
				</tr>
				</thead>
				<tbody>";
				$tglakhir = tglakhir($periode."-01");
				$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
				//Query 
				$strcount="SELECT COUNT(*) AS jumlah FROM $dbname.datakaryawan_bulanan WHERE 
				tanggalmasuk <= '$periode-01' AND (tanggalkeluar>= '$periode-01' or tanggalkeluar = '0000-00-00') AND lokasitugas='$unit' AND periode='$periode'";
				$rescount=fetchdata($strcount);
				foreach($rescount as $barcount){
					$rowcont=$barcount['jumlah'];
				}
				if($param['jenis'] == 'PENDIDIKAN'){
					$whrjns = " AND levelpendidikan='".$param['isijenis']."' ";
				}else if($param['jenis'] == 'KELAMIN'){
					$whrjns = " AND jeniskelamin='".$param['isijenis']."' ";
				}elseif ($param['jenis'] == 'USIA') {
					switch ($param['isijenis']) {
						case 'U1':
							$whrusia = " AND tanggallahir BETWEEN DATE_SUB('".$tglakhir."', INTERVAL 30 YEAR) AND DATE_SUB('".$tglakhir."', INTERVAL 18 YEAR)";
							break;
						case 'U2':
							$whrusia = " AND tanggallahir BETWEEN DATE_SUB('".$tglakhir."', INTERVAL 40 YEAR) AND DATE_SUB('".$tglakhir."', INTERVAL 31 YEAR)";
							break;
						case 'U3':
							$whrusia = " AND tanggallahir BETWEEN DATE_SUB('".$tglakhir."', INTERVAL 50 YEAR) AND DATE_SUB('".$tglakhir."', INTERVAL 41 YEAR)";
							break;
						case 'U4':
							$whrusia = " AND tanggallahir BETWEEN DATE_SUB('".$tglakhir."', INTERVAL 55 YEAR) AND DATE_SUB('".$tglakhir."', INTERVAL 51 YEAR)";
							break;
						case 'U5':
							$whrusia = " AND tanggallahir BETWEEN DATE_SUB('".$tglakhir."', INTERVAL 75 YEAR) AND DATE_SUB('".$tglakhir."', INTERVAL 56 YEAR)";
							break;
					}
				}
				if($rowcont>0){
					$str="SELECT * FROM $dbname.datakaryawan_bulanan WHERE 
					tanggalmasuk <= '$periode-01' AND 
					(tanggalkeluar>= '$periode-01' OR tanggalkeluar = '0000-00-00')  AND lokasitugas='$unit'  AND periode='$periode' AND kodegolongan='".$param['golongan']."' AND tipekaryawan='".$param['tipekaryawan']."' ".$whrjns." ".$whrusia."  AND kodejabatan='".$param['jab']."' ORDER BY kodegolongan ASC,tipekaryawan DESC";
				}else{
					$str="SELECT * FROM $dbname.datakaryawan WHERE 
					tanggalmasuk <= '$periode-01' AND 
					(tanggalkeluar>= '$periode-01' OR tanggalkeluar = '0000-00-00') AND  lokasitugas='$unit' AND kodegolongan='".$param['golongan']."' AND tipekaryawan='".$param['tipekaryawan']."' ".$whrjns." ".$whrusia."  AND kodejabatan='".$param['jab']."' ORDER BY kodegolongan ASC,tipekaryawan DESC";
				}
				$no =1;
				foreach (fetchData($str) as $v) {
					echo "
						<tr class=rowcontent>
							<td align=center>".$no."</td>
							<td align=center>".$v['nik']."</td>
							<td align=left>".$v['namakaryawan']."</td>
							<td align=center>".$optjab[$v['kodejabatan']]."</td>
							<td align=center>".$optGol[$v['kodegolongan']]."</td>
							<td align=center>".$v['lokasitugas']." - ".getNamaOrg($v['lokasitugas'])."</td>
							<td align=center>".$v['induk']." - ".getNamaOrg($v['induk'])."</td>
							<td align=center>".$v['noktp']."</td>
							<td align=center>".$optPend[$v['levelpendidikan']]."</td>
							<td align=center>".$v['statuspajak']."</td>
							<td align=center>".$v['statusperkawinan']."</td>
							<td align=center>".$v['jumlahanak']."</td>
							<td align=center>".tanggalnormal($v['tanggalmasuk'])."</td>
							<td align=center>".($v['tanggalkeluar'] == '0000-00-00' ? '-' : tanggalnormal($v['tanggalkeluar']))."</td>
							<td align=center>".$optTip[$v['tipekaryawan']]."</td>
							<td align=center>".strtoupper($v['statuskaryawan'])."</td>
						</tr>";
					$no++;
				}	
			echo"</tbody>
				</table>
			</div>";
		break;
    default:
    break;
}
?>