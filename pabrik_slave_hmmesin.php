<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/zFunction.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$caritanggal = checkPostGet('caritanggal', '');
$pages = checkPostGet('page', '');

$unit = checkPostGet('unit', '');
$tanggal = checkPostGet('tanggal', '');
$station = checkPostGet('station', '');

$substation = checkPostGet('substation', '');
$hour = checkPostGet('hour', '0');
$hournonpararel = checkPostGet('hournonpararel', '0');
$hourproses = checkPostGet('hourproses', '0');
$keterangan = checkPostGet('keterangan', '');
$current = checkPostGet('current', '');

$app = 'pabrik';
$postJabatan = getPostingJabatan($app);

switch($proses)
{
	case'insertht':
		##Get array Station
		$str="select * from ".$dbname.".organisasi where induk='".$station."'";
		$arrstation=fetchData($str);
		$countstation = count($arrstation);
		
		$tab.="<fieldset style=float:left;><legend>Detail</legend>
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
				<tr align=center>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['kode']."</td>
					<td>Sub ".$_SESSION['lang']['station']."</td>
					<td hidden>Hour<br>Paralel</td>
					<td hidden>Hour<br>Non-Paralel</td>
					<td>Hour<br>Process</td>
					<td>".$_SESSION['lang']['keterangan']."</td>
				</tr>
				</thead>
				<tbody>";
				$no=0;
				foreach($arrstation as $key=>$val){
					$keteranganx="";
					$strx="select * from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".tanggalsystem($tanggal)."' and station='".$station."' and substation='".$val['kodeorganisasi']."'";
					$resx=fetchData($strx);
					$jumlahx = $resx[0]['hour'];
					$hourprosesx = $resx[0]['hourproses'];
					$hournonpararelx = $resx[0]['hournonpararel'];
					$keteranganx = $resx[0]['keterangan'];
					$jumlahx=($jumlahx==''?"placeholder=0":"value='".$jumlahx."'");
					$hournonpararelx=($hournonpararelx==''?"placeholder=0":"value='".$hournonpararelx."'");
					$hourprosesx=($hourprosesx==''?"placeholder=0":"value='".$hourprosesx."'");
					
					$no++;
					$optsubstt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeorganisasi']."'");
					
					$tab.="<tr class=rowcontent id='tr_".$no."'>";
					$tab.="<td style='text-align:right'>".$no."</td>";
					$tab.="<td style='text-align:left' id='substation_".$no."'>".$val['kodeorganisasi']."</td>";
					$tab.="<td style='text-align:left'>".$optsubstt[$val['kodeorganisasi']]."</td>";
					$tab.="<td style='text-align:center' hidden>
						<input type='text' size='3' id='hour_".$no."' class='myinputtextnumber' onkeypress='return angka_doang(event)' ".$jumlahx." maxlength=5>
					</td>";
					$tab.="<td style='text-align:center' hidden>
						<input type='text' size='3' id='hournonpararel_".$no."' class='myinputtextnumber' onkeypress='return angka_doang(event)' ".$hournonpararelx." maxlength=5>
					</td>";
					$tab.="<td style='text-align:center'>
						<input type='text' size='3' id='hourproses_".$no."' class='myinputtextnumber' onkeypress='return angka_doang(event)' ".$hourprosesx." maxlength=5>
					</td>";
					$tab.="<td style='text-align:left'>
						<input  type='text' class='myinputtext' id='keterangan_".$no."' onkeypress=\"return tanpa_kutip(event);\" style='width:250px;' value='".$keteranganx."' />
					</td>";
					$tab.="</tr>";
				}
			$tab.="<tr>
				<td colspan=7 style='text-align:center'>
					<button class=mybutton id='simpanht' onclick=savedt('".$countstation."')>".$_SESSION['lang']['save']."</button>&nbsp;
					<button class=mybutton id='cancelht' onclick=canceldt()>".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>				
			</tbody>
			</table>
		</fieldset>";
		echo $tab;
	break;
	
	case'savedt':
		if($current=='1'){
			$str="delete from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".tanggalsystem($tanggal)."' and station='".$station."'";
			try{$owlPDO->exec($str);}catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n"; die();}
		}
		
		// if(($hour=='0' or $hour=='') and $keterangan==''){
			
		// }else{
			$str="insert into ".$dbname.".pabrik_hmmesin  (unit,tanggal,station,substation,hour,hournonpararel,hourproses,keterangan,createdby,createdtime,updateby,updatetime) 
			values ('".$unit."','".tanggalsystem($tanggal)."','".$station."','".$substation."','".$hour."','".$hournonpararel."','".$hourproses."','".$keterangan."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			try{$owlPDO->exec($str);}catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n"; die();}
		// }
	break;
	
	case'loadData':
		$arrorgdet = getOrgDetail(2);
		$where = " and unit in (".$arrorgdet.")";
		//Inisialisasi Search
		if($caritanggal!='')
		{
			$caritanggal = substr($caritanggal,6,4)."-".substr($caritanggal,3,2)."-".substr($caritanggal,0,2);
			$where.=" and tanggal like '".$caritanggal."%'";
        }
	
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
        
		$str="select count(*) jmlhrow from ".$dbname.".pabrik_hmmesin where 1=1 ".$where." group by station,tanggal";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$jlhbrs= $bar['jmlhrow'];	
		}
		
		$tab='';
		$nor=0;
		
		$str="select * from ".$dbname.".pabrik_hmmesin where 1=1 ".$where." group by station,tanggal order by tanggal DESC limit ".$offset.",".$limit." ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$nor+=1;
			
			$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['unit']."'");
			$optStation = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['station']."'");
			$optKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			
			$tab.="<tr class=rowcontent>
				<td style='text-align:center'>".$nor."</td>
				<td id='nor_".$nor."' value='".$nor."'>".$bar['unit']." - ".$optUnit[$bar['unit']]."</td>
				<td style='text-align:center'>".tanggalnormal($bar['tanggal'])."</td>
				<td id='nor_".$nor."' value='".$nor."'>".$bar['station']." - ".$optStation[$bar['station']]."</td>
				<td>".$optKaryawan[$bar['updateby']]."</td>";
			
			if($_SESSION['empl']['lokasitugas']==$bar['unit']){
				if($bar['status']=='0')
				{
					$tab.="<td style='text-align:center'>Created</td>";
					$tab.="<td style='text-align:center'>
						<img src=images/application/application_edit.png class=zImgBtn title='edit' onclick=\"editall('".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['station']."');\">
					</td>";
					$tab.="<td style='text-align:center'>
						<img src=images/application/application_delete.png class=zImgBtn title='delete' onclick=\"deleteall('".$bar['unit']."','".$bar['tanggal']."','".$bar['station']."');\">
					</td>";
					$tab.="<td style='text-align:center'>
						<img src=images/icons/04/16/09.png class=zImgBtn title='Posted' onclick=\"postall('".$bar['unit']."','".$bar['tanggal']."','".$bar['station']."');\">
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>Posted</td>";
					$tab.="<td style='text-align:center'></td>";
					$tab.="<td style='text-align:center'></td>";
					if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
						$tab.="<td style='text-align:center'>
							<img src=images/icons/04/16/02.png class=zImgBtn title='Unposted' style='cursor:pointer' onclick=\"unpostall('".$bar['unit']."','".$bar['tanggal']."','".$bar['station']."');\">
						</td>";
					}else{
						$tab.="<td style='text-align:center'>
							<img src=images/icons/04/16/02.png class=zImgOffBtn title='Posted'>
						</td>";
					}
				}
			}else{
				if($bar['status']=='0'){
					$tab.="<td style='text-align:center'>Created</td>";
					$tab.="<td style='text-align:center'></td>";
					$tab.="<td style='text-align:center'></td>";
					$tab.="<td style='text-align:center'></td>";
				}else{
					$tab.="<td style='text-align:center'>Posted</td>";
					$tab.="<td style='text-align:center'></td>";
					$tab.="<td style='text-align:center'></td>";
					$tab.="<td style='text-align:center'></td>";
				}
			}
			$tab.="<td style='text-align:center'>
				<img src=images/zoom.png class=zImgBtn title='print' onclick=\"showdetail('".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['station']."',event);\">
			</td>
			</tr>";
		}
		
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0)
		{
			$totrows=1;
		}
		
		$isiRow='';
		for($er=1;$er<=$totrows;$er++)
		{
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}
		
		$tab.="</tr>
            <tr><td colspan=20 align=center>";
		
		if($page=='0')
		{
			$tab.="<button class=mybutton disabled=true>".$_SESSION['lang']['pref']."</button>";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
		}
		
		$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
		
		if(($page+1) == $totrows)
		{
			$tab.="<button class=mybutton disabled=true>".$_SESSION['lang']['lanjut']."</button>";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
		}
        $tab.="</td></tr>";
		echo $tab;
	break;
	
	case'deleteall':
		$str="delete from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".$tanggal."' and station='".$station."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
	
	case'showdetail':
		$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optStation = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$station."'");
	
		##Get array Station
		$str="select * from ".$dbname.".organisasi where induk='".$station."'";
		$arrstation=fetchData($str);
		$countstation = count($arrstation);
	
		$tab="";
		$tab.="<link rel=stylesheet type='text/css' href='style/".$gen."'>";
		$tab.="<fieldset>
			<table style='font-size: 12px;'>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td>".$unit."-".$optUnit[$unit]."</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td>".($tanggal)."</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['station']."</td>
					<td>:</td>
					<td>".$station."-".$optStation[$station]."</td>
				</tr>
			</table>
			<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
				<thead>
				<tr align=center>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['kode']."</td>
					<td>Sub ".$_SESSION['lang']['station']."</td>
					<td>Hour<br>Paralel</td>
					<td>Hour<br>Non-Paralel</td>
					<td>Hour<br>Process</td>
					<td>".$_SESSION['lang']['keterangan']."</td>
				</tr>
				</thead>
				<tbody>";
				$no=0;
				foreach($arrstation as $key=>$val){
					$keteranganx="";
					$strx="select * from ".$dbname.".pabrik_hmmesin where unit='".$unit."' and tanggal='".tanggalsystem($tanggal)."' and station='".$station."' and substation='".$val['kodeorganisasi']."'";
					$resx=fetchData($strx);
					$jumlahx = $resx[0]['hour'];
					$hournonpararelx = $resx[0]['hournonpararel'];
					$hourprosesx = $resx[0]['hourproses'];
					$keteranganx = $resx[0]['keterangan'];
					$jumlahx=($jumlahx==''?"0":$jumlahx);
					$hournonpararelx=($hournonpararelx==''?"0":$hournonpararelx);
					$hourprosesx=($hourprosesx==''?"0":$hourprosesx);
					
					$no++;
					$optsubstt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeorganisasi']."'");
					
					$tab.="<tr class=rowcontent id='tr_".$no."'>";
					$tab.="<td style='text-align:right'>".$no."</td>";
					$tab.="<td style='text-align:left' id='substation_".$no."'>".$val['kodeorganisasi']."</td>";
					$tab.="<td style='text-align:left'>".$optsubstt[$val['kodeorganisasi']]."</td>";
					$tab.="<td style='text-align:center'>".$jumlahx."</td>";
					$tab.="<td style='text-align:center'>".$hournonpararelx."</td>";
					$tab.="<td style='text-align:center'>".$hourprosesx."</td>";
					$tab.="<td style='text-align:left'>".$keteranganx."</td>";
					$tab.="</tr>";
				}				
			$tab.="</tbody>
			</table>
		</fieldset>";
		
		echo $tab;
	break;
	
	case'postall':
		$str="update ".$dbname.".pabrik_hmmesin set status='1',postedby='".$_SESSION['standard']['userid']."' where unit='".$unit."' and tanggal='".$tanggal."' and station='".$station."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
	
	case'unpostall':
		$str="update ".$dbname.".pabrik_hmmesin set status='0',postedby='0' where unit='".$unit."' and tanggal='".$tanggal."' and station='".$station."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
}