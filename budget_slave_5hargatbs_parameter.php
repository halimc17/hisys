<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
@$param['nilai']  =str_replace(",","",$param['nilai']);

$jab  = getPostingJabatan('budget');




switch ($method) {
    case'html':
        $tab = "
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahun'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeorg'] . "</th>
            <th align=center rowspan='2' style=display:none>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nilai'] . "</th>
		</tr></thead>";
		
        $no = 0;
        $str = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='".$param['tahun']."' and pabrik like '".$param['kodeorg']."%' and kodebarang='".$param['namabarang']."'";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahun'] . "</td>";
            $tab.="<td align=center>" . $bar['kodebarang'] . "</td>";
            $tab.="<td align=left>".getNamaOrg($bar['kodeorg'])."</td>";
            $tab.="<td align=center style=display:none>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['rupiah'], 2) . "</td>";
        }
        $tab.="</tr>";
        $tab.="</table>";
		
        echo $tab;
	break;
    case'insert':
		try {
			$owlPDO->beginTransaction();
		
			$sql = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='" . $param['tahun'] . "' and pabrik='".$_SESSION['empl']['lokasitugas']."' and posting=1 and kodebarang='" . $param['namabarang'] . "'";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah di posting / tutup.");
			}
			
			$sql = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='" . $param['tahun'] . "' and pabrik='".$_SESSION['empl']['lokasitugas']."' and kodeorg ='" . $param['kodeorg'] . "' and  kodebarang='" . $param['namabarang'] . "' and tahuntanam='".$param['tahuntanam']."'";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah ada.");
			}
			
			# Komen untuk Lokasi Tugas Pabrik
			// if(getNamaOrg($_SESSION['empl']['lokasitugas'],'tipe')!='PABRIK'){
			// 	throw new PDOException("Hanya untuk di pabrik, silahkan pindah ke Pabrik terlebih dahulu.");
			// }
			# End Komen
			
			$data = array(
				'tahun'     => $param['tahun'],
				'periode'   => $param['tahun']."-".$param['periode'],
				'pabrik'    => $_SESSION['empl']['lokasitugas'],
				'kodeorg'   => $param['kodeorg'],
				'tahuntanam'=> $param['tahuntanam'],
				'kodebarang'=> $param['namabarang'],
				'rupiah'    => $param['nilai'],
				'jenisbudget' => $param['jenisbudget'],
				'updateby'  => $_SESSION['standard']['userid'],
				'updatetime'=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'bgt_hargatbs_parameter',$data,$cols);
			$owlPDO->exec($query);
			
		
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			
	break;
    case'delete':
		$sql = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='" . $param['tahun'] . "' and pabrik ='" . $param['kodeorg'] . "' and posting=1 and kodebarang='" . $param['namabarang'] . "'";
		$res = fetchdata($sql);
		$jlhbrs = count($res);
		if ($jlhbrs > 0) {
			throw new PDOException("Data sudah di posting / tutup.");
		}
		
        $str = "delete from " . $dbname . ".bgt_hargatbs_parameter where tahun='" . $param['tahun'] . "' and pabrik ='" . $param['kodeorg'] . "' and  kodebarang='" . $param['namabarang'] . "'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'deletedetail':
		$str = "delete from " . $dbname . ".bgt_hargatbs_parameter where tahun='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and  kodebarang='" . $param['namabarang'] . "' and tahuntanam='".$param['tahuntanam']."' and pabrik='".$param['pabrik']."'"; #exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'updatedetail':
	   try {
			$owlPDO->beginTransaction();
		
			$sql = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='" . $param['tahun'] . "' and pabrik='".$_SESSION['empl']['lokasitugas']."'  and posting=1 and kodebarang='" . $param['namabarang'] . "' and pabrik='".$_SESSION['empl']['lokasitugas']."'";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah di posting / tutup.");
			}	
			
			$data = array(
				'periode'	=> $param['tahun']."-".$param['periode'],
				'rupiah'    => $param['nilai'],
				'tahuntanam'=> $param['tahuntanam'],
				'updateby'  => $_SESSION['standard']['userid'],
				'updatetime'=> date('Y-m-d H:i:s')
			);
			
			$where = "tahun='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and  kodebarang='" . $param['namabarang'] . "' and tahuntanam='".$param['tahuntanam']."' and pabrik='".$_SESSION['empl']['lokasitugas']."'";
		
			$query = updateQuery($dbname,'bgt_hargatbs_parameter',$data,$where);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
		break;
    case'formposting':
		$noakun="<option value=''>&nbsp;</option>";
		$noakun1="<option value=''>&nbsp;</option>";
		$noakun2="<option value=''>&nbsp;</option>";
		$str="select * from ".$dbname.".keu_5akun where 1=1 and noakun like '64101%' and length(noakun)=7 order by noakun asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$d=substr($bar['noakun'],0,5);
			if($d!=$n){			
				$noakun.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				$noakun1.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				$noakun2.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$a="";
			if($bar['noakun']=='6410101'){$a="selected";}
			$b="";
			if($bar['noakun']=='6410102'){$b="selected";}
			$c="";
			if($bar['noakun']=='6410103'){$c="selected";}
			
			$noakun.="<option value=".$bar['noakun']." ".$a.">".$bar['noakun']." - ".$bar['namaakun']."</option>";
			$noakun1.="<option value=".$bar['noakun']." ".$b.">".$bar['noakun']." - ".$bar['namaakun']."</option>";
			$noakun2.="<option value=".$bar['noakun']." ".$c.">".$bar['noakun']." - ".$bar['namaakun']."</option>";
			$n=$d;
			if($d!=$n){			
				$noakun.="</optgroup>";
				$noakun1.="</optgroup>";
				$noakun2.="</optgroup>";
			}
		}
		
		$noakunp2="<option value=''>&nbsp;</option>";
		$noakunc2="<option value=''>&nbsp;</option>";
		$str="select * from ".$dbname.".keu_5akun where 1=1 and noakun like '5110%' and length(noakun)=7 order by noakun asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$d=substr($bar['noakun'],0,5);
			if($d!=$n){			
				$noakunc2.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				$noakunp2.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$a="";
			if($bar['noakun']=='5110101'){$a="selected";}
			$b="";
			if($bar['noakun']=='5110201'){$b="selected";}
			
			
			$noakunc2.="<option value=".$bar['noakun']." ".$a.">".$bar['noakun']." - ".$bar['namaakun']."</option>";
			$noakunp2.="<option value=".$bar['noakun']." ".$b.">".$bar['noakun']." - ".$bar['namaakun']."</option>";
			$n=$d;
			if($d!=$n){			
				$noakunc2.="</optgroup>";
				$noakunp2.="</optgroup>";
			}
		}

		$tab = "
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
				<thead><tr class=rowheader>
					<th>Item</th>
					<th id=namabrg>TBS</th>
					<th id=namabrg>CPO</th>
					<th id=namabrg>PK</th>
				</tr>
				</thead>
				<tr class=rowcontent>
					<td>Plasma</td>
					<td><select class='select2' id=plasmatbs style=\"width:150px;\">" . $noakun . "</select></td>
					<td style=background-color:gray></td>
					<td style=background-color:gray></td>
				</tr>
				<tr class=rowcontent>
					<td>Afiliasi</td>
					<td><select class='select2' id=afiliasitbs style=\"width:150px;\">" . $noakun1 . "</select></td>
					<td style=background-color:gray></td>
					<td style=background-color:gray></td>
				</tr>
				<tr class=rowcontent>
					<td>External</td>
					<td><select class='select2' id=externaltbs style=\"width:150px;\">" . $noakun2 . "</select></td>
					<td><select class='select2' id=externalcpo style=\"width:150px;\">" . $noakunc2 . "</select></td>
					<td><select class='select2' id=externalpk style=\"width:150px;\">" . $noakunp2 . "</select></td>
				</tr>
				<tr class=rowcontent>
					<td colspan=4 align=center>
						<button class=mybutton onclick=posting('".$param['tahun']."','".$param['kodeorg']."','".$param['namabarang']."')>" . $_SESSION['lang']['posting'] . "</button>
					</td>
				</tr>
				</table>
            ";
		echo $tab;
	break;
    case'posting':
		try{
		$owlPDO->beginTransaction();
		$str = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='".$param['tahun']."' and pabrik like '".$param['kodeorg']."%' and kodebarang='".$param['namabarang']."'";
        $res = fetchdata($str);#exit("error".$str);
        foreach($res as $bar){
			$whr = " and kodeorg like '".$param['kodeorg']."%' and tipebudget='MILL' and tahunbudget='".$param['tahun']."' and pta='BGT' and kodebudget in ('".$bar['kodebarang']."')";
			$sql = "select * from ".$dbname.".bgt_budget where 1=1 ".$whr." and keterangan='".$bar['kodeorg']."' and keterangan2='".$bar['tahuntanam']."'";
			$req = fetchdata($sql);
			if(count($res)>0){
				foreach($req as $val){					
					$query = "delete from ".$dbname.".bgt_budget  where kunci='".$val['kunci']."' ".$whr."";
					$owlPDO->exec($query);
				}
			}
		}
		
		$str = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='".$param['tahun']."' and pabrik like '".$param['kodeorg']."%' and kodebarang='".$param['namabarang']."'";
        $res = fetchdata($str);#exit("error".$str);
        foreach($res as $bar){
			
			$ip   = getNamaOrg($bar['kodeorg'],'inti'); #inti= 1, plasma= 0
			$pt   = getNamaOrg($bar['kodeorg'],'induk');
			$ptpks= getNamaOrg($bar['pabrik'],'induk');
			
			if($ip==0 and $pt==$ptpks){
				$jenis = 'plasma';
				if($bar['kodebarang']=='TBS'){
					$noakun = $param['plasmatbs'];
				}
			}elseif($pt!=$ptpks){
				$jenis = 'afiliasi';
				if($bar['kodebarang']=='TBS'){
					$noakun = $param['afiliasitbs'];
				}
			}else{
				$jenis = 'external';
				if($bar['kodebarang']=='TBS'){
					$noakun = $param['externaltbs'];
				}
			}
			
			if($bar['kodebarang']=='CPO'){
				$noakun = $param['externalcpo'];
			}
			if($bar['kodebarang']=='KER'){
				$noakun = $param['externalpk'];
			}
			
			if($bar['kodeorg']=='EXTM'){
				$kodeunit='tbsexternal';
			}else{
				$kodeunit=$bar['kodeorg'];
			}
			
			$volume=0;$wh="";
			if($bar['kodebarang']=='TBS'){
				$wh=" and kodeunit='".$kodeunit."'";
			}
			$str = "select * from " . $dbname . ".bgt_produksi_pks where tahunbudget ='".$param['tahun']."' and millcode = '".$param['kodeorg']."' ".$wh.""; #exit("error".$str);
			$res = fetchdata($str);
			foreach($res as $val){
				if($bar['kodebarang']=='TBS'){
					$volume += $val['kgolah'];
				}
				if($bar['kodebarang']=='CPO'){
					$volume += $val['kgolah']*$val['oerbunch']/100;
				}
				if($bar['kodebarang']=='KER'){
					$volume += $val['kgolah']*$val['oerkernel']/100;
				}
			}
			
			$str = "select distinct a.noaruskas, a.nama_aruskas, a.tipetransaksi from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.level='3' and a.status='1' and b.noakun = '".$noakun."'";
			$res=fetchdata($str);
			foreach($res as $val){
				if($bar['kodebarang']=='TBS'){
					if($val['tipetransaksi']=='K'){						
						$aruskas = $val['noaruskas'];
					}
				}else{
					$aruskas = $val['noaruskas'];
				}
			}
			
			if($aruskas==''){
				throw new PDOException("Aruskas tidak ditemukan.");
			}
			
			$data = array(
				'tahunbudget'=> $param['tahun'],
				'kodeorg'    => $param['kodeorg'],
				'tipebudget' => 'MILL',
				'kodebudget' => $bar['kodebarang'],
				'noakun'     => $noakun,
				'volume'     => $volume,
				'satuanv'    => 'KG',
				'rupiah'     => round($volume*$bar['rupiah'],0),
				'rotasi'     => '12',
				'updateby'   => $_SESSION['standard']['userid'],
				'jumlah'     => $volume,
				'satuanj'    => 'KG',
				'aruskas'    => $aruskas,
				'keterangan' => $bar['kodeorg'],
				'keterangan2'=> $bar['tahuntanam']
			);
			$query = insertQuery($dbname,'bgt_budget',$data,array_keys($data));
			$owlPDO->exec($query);
			
			$dataharga[$kodeunit]=$kodeunit;
			
			#sebaran
			if($bar['kodebarang']=='TBS'){
				$wh=" and kodeunit='".$kodeunit."'"; $group = "group by kodeunit";
			}else{
				$group = "group by millcode";
			}
			$range = range(1,12);
			$n="sum(";$e="";
			foreach($range as $bln){
				if($bln<12){					
					$n.="olah".addZero($bln,2)."+";
					$e.="sum(olah".addZero($bln,2).") as olah".addZero($bln,2).", ";
				}else{
					$n.="olah".addZero($bln,2);
					$e.="sum(olah".addZero($bln,2).") as olah".addZero($bln,2);
				}
			}
			$n.=") as ttlkg";
			
			$str = "select tahunbudget,millcode,kodeunit, ".$n.",".$e." from " . $dbname . ".bgt_produksi_pks where tahunbudget ='".$param['tahun']."' and millcode = '".$param['kodeorg']."' ".$wh." ".$group.""; 
			$res = fetchdata($str);
			foreach($res as $val){
				$whr = " and kodeorg like '".$param['kodeorg']."%' and tipebudget='MILL' and tahunbudget='".$param['tahun']."' and pta='BGT' and kodebudget = '".$bar['kodebarang']."'";
				$sql = "select * from ".$dbname.".bgt_budget where 1=1 ".$whr." and keterangan='".$bar['kodeorg']."' and keterangan2='".$bar['tahuntanam']."'"; #exit("error".$sql);
				$req = fetchdata($sql);
				if(count($req)>0){					
					foreach($req as $baq){
						$strinst="insert into ".$dbname.".bgt_distribusi (`kunci`";
						for($i=1;$i<=12;$i++){
							$strinst.=",`rp".addZero($i,2)."`";
							$strinst.=",`fis".addZero($i,2)."`";
						}
						$strinst.=") values('".$baq['kunci']."'";
						for($i=1;$i<=12;$i++){
							$strinst.=",'".@round($val["olah".addZero($i,2)]/$val['ttlkg']*$baq['rupiah'],0)."'";
							$strinst.=",'".round($val["olah".addZero($i,2)],0)."'";
						}
						$strinst.=");";
						
						#echo $baq['rupiah'];
						if($baq['rupiah']>0){
							$owlPDO->exec($strinst);
						}
					}
				}
			}
		}	
		// exit("error".$strinst);
		
		$str = "select * from " . $dbname . ".bgt_produksi_pks where tahunbudget ='".$param['tahun']."' and millcode = '".$param['kodeorg']."'"; 
		$res = fetchdata($str);
		foreach($res as $val){
			$dataprd[$val['kodeunit']]=$val['kodeunit'];
		}
		
		$err="";
		foreach($dataprd as $unit){
			if($dataharga[$unit]==''){
				$err.=$unit.", ";				
			}
		}
		
		if($err!=''){			
			//throw new PDOException("Harga TBS untuk ".$err." belum ada.");
		}
		
		
		$str = "update " . $dbname . ".bgt_hargatbs_parameter set posting='1' where tahun='" . $param['tahun'] . "' and pabrik ='" . $param['kodeorg'] . "' and  kodebarang='" . $param['namabarang'] . "'";
		$owlPDO->exec($str);
        
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'unposting':
		$str = "update " . $dbname . ".bgt_hargatbs_parameter set posting='0' where tahun='" . $param['tahun'] . "' and pabrik ='" . $param['kodeorg'] . "' and  kodebarang='" . $param['namabarang'] . "'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'loaddatadetailori':
        $tab = "
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahun'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeorg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['sumber'] . "</th>
            <th align=center rowspan='2' style=display:none>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nilai'] . "</th>
            <th align=center rowspan='2' colspan=2>" . $_SESSION['lang']['action'] . "</th>
		</tr></thead>";
		
        $no = 0;
        $str = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='".$param['tahun']."' and pabrik like '".$_SESSION['empl']['lokasitugas']."%'";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahun'] . "</td>";
            $tab.="<td align=center>" . $bar['kodebarang'] . "</td>";
            $tab.="<td align=left>".getNamaOrg($bar['pabrik'])."</td>";
            $tab.="<td align=left>".getNamaOrg($bar['kodeorg'])."</td>";
            $tab.="<td align=center style=display:none>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['rupiah'], 2) . "</td>";
			if($bar['posting']=='0'){				
				$tab.="<td align=center width=25px>
						<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editdetail('" . $bar['tahun'] . "','" . $bar['kodebarang'] . "','" . $bar['kodeorg'] . "','" . $bar['tahuntanam'] . "','" . $bar['rupiah'] . "');\" ></td>";
				$tab.="<td align=center width=25px>	
						<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletedetail('" . $bar['tahun'] . "','" . $bar['kodebarang'] . "','" . $bar['kodeorg'] . "','" . $bar['tahuntanam'] . "','" . $bar['pabrik'] . "');\" >
						</td>";
			}else{
				$tab.="<td align=center width=25px></td>";
				$tab.="<td align=center width=25px></td>";
			}
        }
        $tab.="</tr>";
        $tab.="</table>";
		
        echo $tab;
	break;
    case'loaddatadetail':

		$arrbulan = array(
			"01" => "Januari",
			"02" => "Februari",
			"03" => "Maret",
			"04" => "April",
			"05" => "Mei",
			"06" => "Juni",
			"07" => "Juli",
			"08" => "Agustus",
			"09" => "September",
			"10" => "Oktober",
			"11" => "November",
			"12" => "Desember",
		);

        $tab = "
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahun'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['bulan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['jenis'] . " Budget</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeorg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['sumber'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nilai'] . "</th>
            <th align=center rowspan='2' colspan=2>" . $_SESSION['lang']['action'] . "</th>
		</tr></thead>";
		
        $no = 0;
        $str = "select * from " . $dbname . ".bgt_hargatbs_parameter where tahun='".$param['tahun']."' and pabrik like '".$_SESSION['empl']['lokasitugas']."%'";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahun'] . "</td>";
            $tab.="<td align=center>" . $arrbulan[substr($bar['periode'],5,2)] . "</td>";
            $tab.="<td align=center>" . ucfirst($bar['jenisbudget']) . "</td>";
            $tab.="<td align=center>" . $bar['kodebarang'] . "</td>";
            $tab.="<td align=left>".getNamaOrg($bar['pabrik'])."</td>";
            $tab.="<td align=left>".getNamaOrg($bar['kodeorg'])."</td>";
            $tab.="<td align=center>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['rupiah'], 2) . "</td>";
			if($bar['posting']=='0'){				
				$tab.="<td align=center width=25px>
						<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editdetail('" . $bar['tahun'] . "','" . $bar['kodebarang'] . "','" . $bar['kodeorg'] . "','" . $bar['tahuntanam'] . "','" . $bar['rupiah'] . "');\" ></td>";
				$tab.="<td align=center width=25px>	
						<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletedetail('" . $bar['tahun'] . "','" . $bar['kodebarang'] . "','" . $bar['kodeorg'] . "','" . $bar['tahuntanam'] . "','" . $bar['pabrik'] . "');\" >
						</td>";
			}else{
				$tab.="<td align=center width=25px></td>";
				$tab.="<td align=center width=25px></td>";
			}
        }
        $tab.="</tr>";
        $tab.="</table>";
		
        echo $tab;
	break;
	case'getbgtkode':
		$optgol="<option value=''>&nbsp;</option>";
		if($param['kodeorg']!='EXTM'){			
			$where=" and kodeorg like '".$param['kodeorg']."%'";
		}
		$str="select distinct tahuntanam from ".$dbname.".setup_blok where 1=1 ".$where." order by tahuntanam asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$s="";
			if($param['thntnm']==$bar['tahuntanam']){
				$s="selected";
			}
			$optgol.="<option value=".$bar['tahuntanam']." ".$s.">".$bar['tahuntanam']."</option>";
		}
		
		echo $optgol;
	break;
    case'loaddata':
        $where = "";
		$where = " and pabrik in (".getOrgDetail(2).")";
		
		if($param['tahun']!=''){
			$where.=" and tahun = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and pabrik = '".$param['kodeorg']."'";
		}
		
		
        $limit = 15;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = $_POST['page'];if ($page < 0){$page = 0;}}
		
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
        $sql = "select count(*) as jmlhrow from " . $dbname . ".bgt_hargatbs_parameter where 1=1 " . $where . " group by tahun, pabrik, kodebarang";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		
        $no = 0;
        $tab = "";
        $no = $maxdisplay;
		$colspan=9;
		
		$str = "SELECT * FROM " . $dbname . ".bgt_hargatbs_parameter where 1=1 " . $where . " group by tahun,pabrik, kodebarang order by tahun desc, kodeorg asc, kodebarang asc limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahun'] . "</td>";
            $tab.="<td align=center>" . $bar['kodebarang'] . "</td>";
            $tab.="<td>" . getNamaOrg($bar['pabrik']). "</td>";
            $tab.="<td>" . getKary($bar['updateby'],'namakaryawan') . "</td>";
			
            if($bar['posting'] == 0) {
                $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('" . $bar['tahun'] . "','" . $bar['pabrik'] . "','" . $bar['kodebarang'] . "');\" ></td>";
                $tab.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $bar['tahun'] . "','" . $bar['pabrik'] . "','" . $bar['kodebarang'] . "');\" ></td>";
				$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Close ???' onclick=\"formposting('" . $bar['tahun'] . "','" . $bar['pabrik'] . "','" . $bar['kodebarang'] . "');\" ></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('" . $bar['tahun'] . "','" . $bar['pabrik'] . "','" . $bar['kodebarang'] . "');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$tab.="<td align=center width=25px></td><td align=center width=25px></td>";
                $tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $tab.="<td align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"html('" . $bar['tahun'] . "','" . $bar['pabrik'] . "','" . $bar['kodebarang'] . "');\" ></td>";
            $tab.="</tr>";
        }
        
		## PAGING
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
        echo $tab . "####" . $footd;
        break;
}
?>	