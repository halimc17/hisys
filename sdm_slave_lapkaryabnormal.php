<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/validasiktp.php');
use ZerosDev\NikReader\Reader;
$reader = new Reader();

$method= checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param= $_GET;}

switch ($method) {
    case 'preview':
		$str = "select *  from " . $dbname . ".bgt_regional_assignment";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmreg[$bar['kodeunit']]=$bar['subregional'];
		}

		$str = "select *  from " . $dbname . ".sdm_5jabatan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmjab[$bar['kodejabatan']]=$bar['namajabatan'];
		}
		$str = "select *  from " . $dbname . ".sdm_5tipekaryawan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$tipekar[$bar['id']]=$bar['tipe'];
		}
		
		$tab.="<table id=mytable class='sortable nowrap' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['nourut'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['nik2'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['namakaryawan'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['jabatan'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['tipekaryawan'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['lokasitugas'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['divisi'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['tanggalmasuk'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper($_SESSION['lang']['tanggallahir'])."</th>
				<th style='text-align:center;' rowspan=2>".strtoupper('jk')."</th>
				<th style='text-align:center;' colspan=6>".strtoupper('data ktp')."</th>
				<th style='text-align:center;' colspan=3>".strtoupper('Cek data ktp')."</th>
			</tr>
			<tr class=rowheader>
				<th style='text-align:center;'>".strtoupper($_SESSION['lang']['nomor'])."</th>
				<th style='text-align:center;'>".strtoupper($_SESSION['lang']['provinsi'])."</th>
				<th style='text-align:center;'>".strtoupper($_SESSION['lang']['kabupaten'])."</th>
				<th style='text-align:center;'>".strtoupper($_SESSION['lang']['kecamatan'])."</th>
				<th style='text-align:center;'>".strtoupper($_SESSION['lang']['tanggallahir'])."</th>
				<th style='text-align:center;'>".strtoupper('jk')."</th>
				<th style='text-align:center;'>".strtoupper('valid')."</th>
				<th style='text-align:center;'>".strtoupper($_SESSION['lang']['tanggallahir'])."</th>
				<th style='text-align:center;'>".strtoupper('jk')."</th>
			</tr>
		</thead>
		<tbody >";
		
		$where="";
		$table="datakaryawan";
		
		if($param['tipekaryawan']!=''){
			$where.=" and tipekaryawan='".$param['tipekaryawan']."'";	
		}else{				
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$where.=" and tipekaryawan in ('4')";
			}
		}
		if($param['kodeorg']!=''){
			$where.=" and lokasitugas='".$param['kodeorg']."'";	
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$where.=" and lokasitugas ='".$_SESSION['empl']['lokasitugas']."'";
			}
		}
		
		$valid=['1','BENAR','0'=>'SALAH'];
		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= ".date('Y-m-d').")";
		
		$jk =  array('male'=>'L','female'=>'P');
		$data= array();
		$str = "select * from ".$dbname.".".$table." where 1=1 ".$where." order by namakaryawan";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['tanggalmasuk']=="0000-00-00"){
				$bar['tanggalmasuk']="";
			}
			if($bar['tanggalpengangkatan']=="0000-00-00"){
				$bar['tanggalpengangkatan']="";
			}
			if($bar['tanggalkeluar']=="0000-00-00"){
				$bar['tanggalkeluar']="";
			}
			if($bar['tanggallahir']=="0000-00-00"){
				$bar['tanggallahir']="";
			}
			if($bar['tanggalmenikah']=="0000-00-00"){
				$bar['tanggalmenikah']="";
			}
			
			if($param['periode']=='bi'){
				$tglsekarang=date("Y-m-d");
			}else{
				$tglsekarang=tglakhir($param['periode']."-01");
			}
			if($bar['tanggalkeluar']==''){				
				$statuskeluar="AKTIF";
			}else{
				if($bar['tanggalkeluar']<$tglsekarang){				
					$statuskeluar="KELUAR";
				}else{
					$statuskeluar="AKTIF";					
				}
			}
			
			if($bar['no_keluarga']!=''){
				$bar['no_keluarga']="'".$bar['no_keluarga'];
			}
			if($bar['subbagian']!=''){
				$subbagian = getNamaOrg($bar['subbagian']);
			}else{
				$subbagian = "";
			}
			$result = $reader->read($bar['noktp']);
			$ktp = $result->toArray();
			if(tanggalnormal($bar['tanggallahir'])==$ktp['born_date']){
				$cektgl="BENAR";	$ctgl=1;			
			}else{
				$cektgl="SALAH";	$ctgl=0;			
			}
			if($bar['jeniskelamin']==$jk[$ktp['gender']]){
				$cekjk="BENAR";		$cjk=1;
			}else{
				$cekjk="SALAH";		$cjk=0;
			}
			
			if(($ktp['valid']+$ctgl+$cjk)!='3'){				
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['nik']."</td>";
				$tab.="<td>".$bar['namakaryawan']."</td>";
				$tab.="<td>".$nmjab[$bar['kodejabatan']]."</td>";
				$tab.="<td>".$tipekar[$bar['tipekaryawan']]."</td>";
				$tab.="<td>".getNamaOrg($bar['lokasitugas'])."</td>";
				$tab.="<td>".$subbagian."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggalmasuk'])."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggallahir'])."</td>";
				$tab.="<td>".$bar['jeniskelamin']."</td>";
				$tab.="<td>".$bar['noktp']."</td>";
				$tab.="<td>".$ktp['province']."</td>";
				$tab.="<td>".$ktp['city']."</td>";
				$tab.="<td>".$ktp['subdistrict']."</td>";
				$tab.="<td>".$ktp['born_date']."</td>";
				$tab.="<td>".$jk[$ktp['gender']]."</td>";
				$tab.="<td>".$valid[$ktp['valid']]."</td>";
				$tab.="<td>".$cektgl."</td>";
				$tab.="<td>".$cekjk."</td>";
				$tab.="</tr>";
			}
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		
		echo $tab."####".json_encode($data);
	break;

    case 'excel':
        $nop = "csbm.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('csbm', $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	#$n = number_format($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
function bagi($e,$i){
	if($i!='' and $i!='0'){
		$n=$e/$i;
	}else{
		$n=0;
	}
	return $n;
}
?>
