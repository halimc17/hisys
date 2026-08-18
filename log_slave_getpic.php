<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method       = checkPostGet('method','');
$picpic       = checkPostGet('picpic','');
$departemenpic= checkPostGet('departemenpic','');
$qtypic       = checkPostGet('qtypic','');
$kodebarang   = checkPostGet('kodebarang','');
$qty          = checkPostGet('qty','');
$nodok        = checkPostGet('nodok','');
$norequest    = checkPostGet('norequest','');
$gudang       = checkPostGet('gudang','');
$pemilikbarang= checkPostGet('pemilikbarang','');
$urut         = checkPostGet('urut','');
$untukunit    = checkPostGet('untukunit','');
$crnorequest  = checkPostGet('crnorequest','');
$today        = date("Y-m-d");

switch ($method){
	case'getpicform':
		$tab = "";
		
		$tab.="<table class=sortable border=0 cellspacing=1 cellpadding=5>
		<thead> 
		<tr>
			<th align=center>".$_SESSION['lang']['nourut']."</th>
			<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
			<th align=center>".$_SESSION['lang']['departemen']."</th>
			<th align=center>".$_SESSION['lang']['jumlah']."</th>
			<th align=center>".$_SESSION['lang']['action']."</th>
		</tr>
		</thead>
		<tbody id='trpic'>";
		$no=0;
		foreach($_SESSION['pic'] as $key=>$row){
			if($row['kodebarang'] == $kodebarang && $row['qty'] == $qty){
				$str="select karyawanid, namakaryawan, subbagian, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar>= '".$today."' or tanggalkeluar = '0000-00-00') and karyawanid='".$row['picpic']."' order by namakaryawan";
				$res=fetchData($str);
				if($res[0]['subbagian']==''){
					$res[0]['subbagian']=$res[0]['lokasitugas'];
				}
				$nmkaryawan = ($res[0]['karyawanid']==''?'':$res[0]['namakaryawan']." [".$res[0]['subbagian']."]");
				
				$str="select * from sdm_5departemen where kode='".$row['departemenpic']."' order by nama asc";
				$res=fetchData($str);
				$departemen = $res[0]['nama']; 
				
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$nmkaryawan."</td>";
				$tab.="<td>".$departemen."</td>";
				$tab.="<td style='text-align:right'>".$row['qtypic']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepic('".$kodebarang."','".$qty."','".$row['picpic']."','".$row['departemenpic']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		
		##LIST KARYAWAN##
		$optKaryawan = "<option value=''>&nbsp;</option>";
		$str="select karyawanid, namakaryawan, subbagian,lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar>= '".$today."' or tanggalkeluar = '0000-00-00') and lokasitugas = '".$untukunit."' order by subbagian, namakaryawan";
		$res=fetchData($str);
		foreach($res as $key=>$val){
			if($val['subbagian']==''){
				$val['subbagian']=$val['lokasitugas'];
			}
			$d=$val['subbagian'];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optKaryawan.="<optgroup label='".$nmorg[$d]."'>";
			}
			
			$optKaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['subbagian']."]</option>";
			$n=$d;
			if($d!=$n){
				$optKaryawan.="</optgroup>";
			}
		}
		
		##LIST Departemen##
		$optDepartemen = "<option value=''>&nbsp;</option>";
		$str="select * from sdm_5departemen order by nama asc";
		$res=fetchData($str);
		foreach($res as $val){
			$optDepartemen.="<option value='".$val['kode']."'>".$val['nama']."</option>";
		}
		
		$tab.="<tr class=rowcontent>
			<td></td>
			<td>
				<select id=picpic class=select2 style='width:150px;' onchange='setblankdepartment()'>".$optKaryawan."</select>
			</td>
			<td>
				<select id=departemenpic class=select2 style='width:150px;' onchange='setblankpic()'>".$optDepartemen."</select>
			</td>
			<td>
				<input type=text size=5 maxlength=10 id=qtypic value=0 class=myinputtextnumber onkeypress=\"return angka_doang(event);\">
			</td>
			<td style='text-align:center'>
				<img src='images/plus.png' class='resicon' title='Add PIC/Departement' onclick=\"addpic();\">
			</td>
		</tr>
		</table>";
		
		echo $tab;
	break;
	
	case'addpic':
		$newdata = array(
			'kodebarang'=>$kodebarang,
			'qty'=>$qty,
			'picpic'=>$picpic,			
			'departemenpic'=>$departemenpic,			
			'qtypic'=>$qtypic,			
		);
		
		$totalqty = 0;
		foreach($_SESSION['pic'] as $key=>$row){
			if($row['kodebarang'] == $kodebarang && $row['qty'] == $qty){
				$totalqty = $totalqty + $row['qtypic'];
			}
		}
		
		if(($totalqty+$qtypic) > $qty){
			exit("Warning : Jumlah item sudah melebihi nilai realisasi.");
		}
		
		if($_SESSION['pic'] != array()){
			foreach($_SESSION['pic'] as $key=>$row){
				if($row['kodebarang'] == $kodebarang && $row['qty'] == $qty && $row['picpic'] == $picpic && $row['departemenpic'] == $departemenpic){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['pic'],$newdata);
		}else{
			array_push($_SESSION['pic'],$newdata);
		}
		
		$no=0;
		foreach($_SESSION['pic'] as $key=>$row){
			if($row['kodebarang'] == $kodebarang && $row['qty'] == $qty){
				$str="select karyawanid, namakaryawan, subbagian, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar>= '".$today."' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar' and karyawanid='".$row['picpic']."' order by namakaryawan";
				$res=fetchData($str);
				if($res[0]['subbagian']==''){
					$res[0]['subbagian']=$res[0]['lokasitugas'];
				}
				$nmkaryawan = ($res[0]['karyawanid']==''?'':$res[0]['namakaryawan']." [".$res[0]['subbagian']."]");
				$nmkaryawan2 = ($res[0]['karyawanid']==''?'':$res[0]['namakaryawan']);
				
				$str="select * from sdm_5departemen where kode='".$row['departemenpic']."' order by nama asc";
				$res=fetchData($str);
				$departemen = $res[0]['nama']; 
				
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$nmkaryawan."</td>";
				$tab.="<td>".$departemen."</td>";
				$tab.="<td style='text-align:right'>".$row['qtypic']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepic('".$kodebarang."','".$qty."','".$row['picpic']."','".$row['departemenpic']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
				
				$tab2.="<tr class='rowcontent'>";
				$tab2.="<td>".$no.". ".($nmkaryawan==''?$departemen:$nmkaryawan2)."</td>";
				$tab2.="</tr>";
			}
		}
		
		echo $tab."####".$tab2;
	break;
	
	case'deletepic':
		foreach($_SESSION['pic'] as $key=>$row){
			if($row['kodebarang'] == $kodebarang && $row['qty'] == $qty && $row['picpic'] == $picpic && $row['departemenpic'] == $departemenpic){
				unset($_SESSION['pic'][$key]);
			}
		}
		
		$no=0;
		foreach($_SESSION['pic'] as $key=>$row){
			if($row['kodebarang'] == $kodebarang && $row['qty'] == $qty){
				$str="select karyawanid, namakaryawan, subbagian, lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar>= '".$today."' or tanggalkeluar = '0000-00-00') and statuskaryawan != 'Keluar' and karyawanid='".$row['picpic']."' order by namakaryawan";
				$res=fetchData($str);
				if($res[0]['subbagian']==''){
					$res[0]['subbagian']=$res[0]['lokasitugas'];
				}
				$nmkaryawan = ($res[0]['karyawanid']==''?'':$res[0]['namakaryawan']." [".$res[0]['subbagian']."]");
				$nmkaryawan2 = ($res[0]['karyawanid']==''?'':$res[0]['namakaryawan']);
				
				$str="select * from sdm_5departemen where kode='".$row['departemenpic']."' order by nama asc";
				$res=fetchData($str);
				$departemen = $res[0]['nama']; 
				
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$nmkaryawan."</td>";
				$tab.="<td>".$departemen."</td>";
				$tab.="<td style='text-align:right'>".$row['qtypic']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepic('".$kodebarang."','".$qty."','".$row['picpic']."','".$row['departemenpic']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
				
				$tab2.="<tr class='rowcontent'>";
				$tab2.="<td>".$no.". ".($nmkaryawan==''?$departemen:$nmkaryawan2)."</td>";
				$tab2.="</tr>";
			}
		}
		
		echo $tab."####".$tab2;
	break;
	
	case'nextItem':
		$_SESSION['pic'] = array();
	break;
	
	case'editBast':
		$_SESSION['pic'] = array();
		if($norequest==''){
			$where = " notransaksi='".$nodok."' and kodebarang='".$kodebarang."'";
		}else{
			$where = " notransaksi='".$norequest."' and kodebarang='".$kodebarang."' and realisasi!='0'";
		}
		$str="select * from ".$dbname.".log_permintaanpicdt where ".$where."";
		$res=fetchData($str);
		$no = 0;
		foreach($res as $key=>$val){
			$no++;
			$optNmKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
			$optDep = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$val['dept']."'");
			
			$tab.="<tr class='rowcontent'>
				<td>".$no.". ".($optNmKary[$val['karyawanid']]==''?$optDep[$val['dept']]:$optNmKary[$val['karyawanid']])."</td>
			</tr>";
			
			$newdata = array(
				'kodebarang'=>$kodebarang,
				'qty'=>$qty,
				'picpic'=>$val['karyawanid'],			
				'departemenpic'=>$val['dept'],			
				'qtypic'=>$val['realisasi'],			
			);
			array_push($_SESSION['pic'],$newdata);
		}
		
		echo $tab;
	break;
	
	case'searchrequest':
		$tab="<table>
			<tr>
				<td>Cari No. Request</td>
				<td>:</td>
				<td>
					<input type=text id=crnorequest size=25 style=width:100px class=myinputtext>
				</td>
				<td>
					<button onclick=carinorequest() class=mybutton>".$_SESSION['lang']['find']."</button>
				</td>
			</tr>
		</table><hr>";
		
		$tab.="<div id='listnorequest'><table class=sortable border=0 cellspacing=1 cellpadding=5>
		<thead> 
		<tr>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>No. Request</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
		</tr>
		</thead>
		<tbody>";
		
		$str="select * from ".$dbname.".log_permintaanht where notransaksi not in (select notransaksi from ".$dbname.".log_permintaanpicdt where realisasi!='0') order by notransaksi";
		$res=fetchData($str);
		$no = 0;
		foreach($res as $key=>$val)
		{
			$no++;
			$tab.="<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('".$val['notransaksi']."')\">";
			$tab.="<td style='text-align:right'>".$no."</td>";
			$tab.="<td>".$val['notransaksi']."</td>";
			$tab.="<td>".$val['untukunit']."</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody>
		</table>
		</div>";
		echo $tab;
	break;
	
	case'carinorequest':
		$tab.="<table class=sortable border=0 cellspacing=1 cellpadding=5>
		<thead> 
		<tr>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>No. Request</td>
			<td align=center>".$_SESSION['lang']['unit']."</td>
		</tr>
		</thead>
		<tbody>";
		
		$str="select * from ".$dbname.".log_permintaanht where notransaksi not in (select notransaksi from ".$dbname.".log_permintaanpicdt where realisasi!='0') and notransaksi like '%".$crnorequest."%' order by notransaksi";
		$res=fetchData($str);
		$no = 0;
		if(count($res) <= 0)
		{
			$tab.="<tr class='rowcontent'>";
			$tab.="<td colspan=3 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";
			$tab.="</tr>";
		}
		else
		{
			foreach($res as $key=>$val)
			{
				$no++;
				$tab.="<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('".$val['notransaksi']."')\">";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$val['notransaksi']."</td>";
				$tab.="<td>".$val['untukunit']."</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>
		</table>";
		echo $tab;
	break;
	
	case'showdetail':
		$tab="No. Request : ".$norequest;
		$_SESSION['pic'] = array();
		
		$tab.="<table class=sortable border=0 cellspacing=1>
		<thead> 
		<tr>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>".$_SESSION['lang']['kodebarang']."</td>
			<td align=center>".$_SESSION['lang']['namabarang']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['stok']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']." Permintaan</td>
		</tr>
		</thead>
		<tbody>";
		
		$str="select * from ".$dbname.".log_permintaandt where notransaksi = '".$norequest."' order by kodebarang";
		$res=fetchData($str);
		$no = 0;
		foreach($res as $key=>$val)
		{
			$optNmBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			##ambil saldo barang##
			$saldoqty=0;
			$str1="select saldoqty from ".$dbname.".log_5masterbarangdt where kodebarang='".$val['kodebarang']."' and kodegudang='".$gudang."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while($bar1=$res1->fetch())
			{
				$saldoqty=$bar1->saldoqty;
			}
			
			##ambil pengeluaran barang yang belum di posting##
			$qtynotposted=0;
			$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
				   b on a.notransaksi=b.notransaksi where kodept='".$pemilikbarang."' and b.kodebarang='".$val['kodebarang']."' 
				   and a.tipetransaksi>4
				   and a.kodegudang='".$gudang."'
				   and a.post=0
				   group by kodebarang";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_OBJ); 
			while($bar2=$res2->fetch())
			{
				$qtynotposted=$bar2->jumlah;
			}
			if($qtynotposted=='')
				$qtynotposted=0;
		   
			$saldoqty=$saldoqty-$qtynotposted;
			
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:right;vertical-align:top'>".$no."</td>";
			$tab.="<td id='trkodebarang_".$no."' style='vertical-align:top'>".$val['kodebarang']."</td>";
			$tab.="<td style='vertical-align:top'>".$optNmBarang[$val['kodebarang']]."</td>";
			$tab.="<td style='text-align:right;vertical-align:top'>
				<input type=text size=5 maxlength=10 id='jumlahstok_".$no."' value='".$saldoqty ."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" disabled>
			</td>";
			$tab.="<td style='text-align:right'>
				Total : <input type=text size=5 maxlength=10 id='jumlahpermintaan_".$no."' value='".$val['jumlah'] ."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" disabled>";
			$tab.="<div id='tddetailpic'>";
			$tab.=savesession($norequest,$val['kodebarang']);
			$tab.=getdetailpic($norequest,$val['kodebarang'],$no);
			$tab.="</div>";
			$tab.="</td>";
			$tab.="</tr>";
		}
		$tab.="<tr>
			<td colspan=5 class='rowcontent' style='text-align:center'>
				<button onclick=\"insertnorequest('".$norequest."','".$no."')\" class=mybutton>".$_SESSION['lang']['save']."</button>
			</td>
		</tr></tbody>
		</table>";
		echo $tab;
	break;
	
	case'deletepicrequest':
		foreach($_SESSION['pic'] as $key=>$row)
		{
			if($row['kodebarang'] == $kodebarang && $row['picpic'] == $picpic && $row['departemenpic'] == $departemenpic)
			{
				unset($_SESSION['pic'][$key]);
			}
		}
		
		echo getdetailpic($norequest,$kodebarang,$urut);
	break;
	
	case'insertnorequest':
		$msgError = "";
		$no=0;
		$data = array();
		$data['head'] = array();
		$data['detail'] = array();
		
		$str="select * from ".$dbname.".log_permintaanht where notransaksi='".$norequest."'";
		$res=fetchData($str);
		$data['head']['keterangan'] = $res[0]['keterangan'];		
		
		$no2=0;
		foreach($_POST['kodebarang'] as $key=>$val)
		{
			if($_POST['jumlahstok'][$key] < $_POST['jumlahpermintaan'][$key] && $_POST['jumlahstok'][$key] > 0)
			{
				$no++;
				$optNmBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val."'");
				if($no==1)
				{
					$msgError.="\n* Jumlah Stok tidak mencukupi untuk jumlah permintaan\n";
				}
				$msgError.="  - ".$optNmBarang[$val]." : Stok = ".$_POST['jumlahstok'][$key]." ; Permintaan : ".$_POST['jumlahpermintaan'][$key]."\n";
			}
			else
			{
				if($_POST['jumlahstok'][$key] > 0)
				{
					$str="select * from ".$dbname.".log_permintaandt where notransaksi='".$norequest."' and kodebarang='".$val."'";
					$res=fetchData($str);
					
					foreach($res as $key2=>$val2)
					{
						$no2++;
						$d['kodebarang'] = $val2['kodebarang'];
						$d['satuan'] = $val2['satuan'];
						$d['jumlah'] = $_POST['jumlahpermintaan'][$key];
						$d['subunit'] = $val2['subunit'];
						$d['kodeblok'] = $val2['kodeblok'];
						$d['kodemesin'] = $val2['kodemesin'];
						$d['kodekegiatan'] = $val2['kodekegiatan'];
						$data['detail'][] = $d;
					}
				}
			}
		}
		
		//$datagroup[] = $data; 
		if($msgError!='')
		{
			exit("Gagal : ".$msgError);
		}
		
		echo json_encode($data);
	break;
}   

function getdetailpic($norequest,$kodebarang,$urut)
{
	global $dbname;
	global $owlPDO;
	
	$tab="";
	
	$str="select * from ".$dbname.".log_permintaanpicdt where notransaksi='".$norequest."' and kodebarang='".$kodebarang."'";
	$res=fetchData($str);
	if(count($res) > 0)
	{
		$tab.="<table id='tablepicrequest_".$kodebarang."' class=sortable border=0 cellspacing=1>
			<thead> 
			<tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['untukunit']."</td>
				<td align=center>".$_SESSION['lang']['jumlah']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		$no2=0;
		
		foreach($_SESSION['pic'] as $key3=>$val3)
		{
			if($val3['kodebarang']==$kodebarang)
			{
				$optNmKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val3['picpic']."'");
				$optDep = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$val3['departemenpic']."'");
				$no2++;
				$tab.="<tr class='rowcontent'>
					<td>".$no2."</td>
					<td style='text-align:left'>".($optNmKary[$val3['picpic']]==''?$optDep[$val3['departemenpic']]:$optNmKary[$val3['picpic']])."</td>
					<td style='text-align:center'>
						<input type=text size=5 maxlength=10 id='jumlahpermintaanpic' value='".$val3['qtypic']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" disabled>
					</td>
					<td style='text-align:center'>
						<img title='Delete' class=resicon onclick=\"deletepicrequest('".$norequest."','".$kodebarang."','".$val3['picpic']."','".$val3['departemenpic']."','".$val3['qtypic']."','".$urut."')\" src='images/delete_32.png'/>
					</td>
				</tr>";
			}
		}
		$tab.="</tbody>
			</table>";
	}
	
	return $tab;
} 

function savesession($norequest,$kodebarang)
{
	global $dbname;
	global $owlPDO;
	
	$tab="";
	
	$str="select * from ".$dbname.".log_permintaanpicdt where notransaksi='".$norequest."' and kodebarang='".$kodebarang."'";
	$res=fetchData($str);
	if(count($res) > 0)
	{
		foreach($res as $key=>$val)
		{
			$newdata = array(
				'kodebarang'=>$val['kodebarang'],
				'picpic'=>$val['karyawanid'],			
				'departemenpic'=>$val['dept'],			
				'qtypic'=>$val['qty'],			
			);
			
			array_push($_SESSION['pic'],$newdata);
		}
	}
	return;
} 
?>
