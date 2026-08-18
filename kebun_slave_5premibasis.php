<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method     =checkPostGet('method','');
$afd        =checkPostGet('afd','');
$tipebuah   =checkPostGet('tipebuah','');
$tipehari   =checkPostGet('tipehari','');
$basiskg    =checkPostGet('basiskg','');
$basisha    =checkPostGet('basisha','');
$brondol    =checkPostGet('brondol','');
$kglb1      =checkPostGet('kglb1','');
$persenlb1  =checkPostGet('persenlb1','');
$rplb1      =checkPostGet('rplb1','');
$rplb2      =checkPostGet('rplb2','');
$tahun      =checkPostGet('tahun','');
$prdakhir   =checkPostGet('prdakhir','');
$prdawal    =checkPostGet('prdawal','');

$basiskg    =str_replace(',','',$basiskg);
$basisha    =str_replace(',','',$basisha);
$brondol    =str_replace(',','',$brondol);
$kglb1      =str_replace(',','',$kglb1);
$persenlb1  =str_replace(',','',$persenlb1);
$rplb1      =str_replace(',','',$rplb1);
$rplb2      =str_replace(',','',$rplb2);
$kehadiran  =str_replace(',','',$kehadiran);

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$optInduk = makeOption($dbname,'organisasi','indukblok,namaindukblok');


switch($method){
	case'copy':
		if($prdakhir==$prdawal){exit("Warning : Periode Dari dan Periode Tujuan tidak boleh sama");}
		if($prdakhir<$prdawal){exit("Warning : Periode Tujuan tidak boleh lebih kecil dari Periode Awal");}
		$where='';
		// if($tt!=''){
		// 	$where=" and tahuntanam='".$tt."'";
		// }
		$str="select * from ".$dbname.".kebun_5basispanen2 where 1=1 ".$where." and tahun='".$prdawal."' and kodeorg='".$afd."'";
		// exit("warning:$str");
		$res=fetchData($str);
		$ct=count($res);
		if ($ct==0) {
			exit("Warning, Data Periode ".$prdawal." kosong !!!");
		}
		foreach($res as $bar) {
			$sIns="insert into ".$dbname.".kebun_5basispanen2 (kodeorg,tahun,basis,premilebihbasis,premibrondolan,createby,createdate,updateby,lastupdate,posting) 
			values ('".$bar['kodeorg']."','".$prdakhir."','".$bar['basis']."','".$bar['premilebihbasis']."','".$bar['premibrondolan']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','0')"; #exit("error".$sIns);
			try{$owlPDO->exec($sIns); }catch (PDOException $e){echo"Gagal".$e->getMessage();die();}
		}
	break;
    case'insert':
		
		if($afd=='') exit("Warning: Silakan pilih Kode Blok.");
		if($tahun=='') exit("Warning: Silakan isi Periode.");
		if($tipehari=='') exit("Warning: Silakan isi Tipe Hari.");
		if($tipebuah=='') exit("Warning: Silakan isi Tipe Basis.");
		

		$scek="select * from ".$dbname.".kebun_5basispanen2 
	    where kodeorg='".$afd."' and tipehari='".$tipehari."' and tipebuah='".$tipebuah."' and tahun='".$tahun."'";
		$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
		$rcek=owlBaris($qcek);
		if($rcek!=0){
			exit("Error : Data sudah pernah diinput.");
		}
		
		try{
			$dataIns = array(
				"kodeorg" => $afd,
				"tahun" => $tahun,
				"tipehari" => $tipehari,
				"tipebuah" => $tipebuah,
				"basis" => $basiskg,
				"basisha" => $basisha,
				"premilebihbasis" => $rplb1,
				"premibrondolan" => $brondol
			);
			$cols = array();
			foreach ($dataIns as $key => $row) {
				$cols[] = $key;
			}
			$sIns = insertQuery($dbname,"kebun_5basispanen2",$dataIns,$cols);
			$owlPDO->exec($sIns); 
		}catch (PDOException $e){
			echo"Gagal".$e->getMessage();
			die();
		}
	break;
    
    case'loadData':
		echo"<hr>
            <table class=sortable cellspacing=1 cellpadding=5 border=0 >
                <thead>
					<tr class=rowheader>
						<th align=center rowspan=2>No</th>
						<th align=center rowspan=2>".$_SESSION['lang']['unit']."</th>
						<th align=center rowspan=2 width=50px>".$_SESSION['lang']['periode']."</th>
						<th align=center rowspan=2 width=50px>Tipe Hari</th>
						<th align=center rowspan=2 width=50px>Tipe Basis</th>
						<th align=center rowspan=2 width=75px>".$_SESSION['lang']['norma']."</th>
						<th align=center rowspan=2 width=75px>Basis (HA)</th>
						<th align=center rowspan=2 width=75px >".$_SESSION['lang']['premlebihbasis']." Rp/Kg</th>
						<th align=center rowspan=2 width=50px>".$_SESSION['lang']['brondol']." Rp/Kg</th>
						<th align=center rowspan=2>".$_SESSION['lang']['persetujuan']."</th>
						<th align=center rowspan=2>".$_SESSION['lang']['status']."</th>
						<th align=center rowspan=2 colspan=3 width=50px>".$_SESSION['lang']['action']."</th>
					</tr>

				</thead>
				<tbody>";
        $limit=20;
		$page=0;
		if(isset($_POST['page'])) {
			$page=$_POST['page'];
			if($page<0) $page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		$optJenis = array(
						'0' => 'Normal',
						'1' => 'Banjir'
					);
		$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
		$arrapproval=array("0"=>"Belum diajukan","1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak'],'9'=>'Proses Persetujuan');
		
		$where='';
		if($_SESSION['empl']['tipelokasitugas']!='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where.= " and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
		}
		if($afd!=''){
			$where.=" and kodeorg like '%".$afd."%'";
		}
		if($tahun!=''){
			$where.=" and tahun like '%".$tahun."%'";
		}
		if($tipehari!=''){
			$where.=" and tipehari='".$tipehari."'";
		}
		if($tipebuah!=''){
			$where.=" and tipebuah='".$tipebuah."'";
		}
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5basispanen2 where 1=1 ".$where."";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}
		$tahunini=date("Y");
		
		$str="select * from ".$dbname.".kebun_5basispanen2 where 1=1 ".$where." order by tahun desc, kodeorg asc limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=$maxdisplay;
		while($bar=$res->fetch()) {
			$no+=1;	
			echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".$bar['kodeorg']." - ".$optInduk[$bar['kodeorg']]."</td>
			<td style='text-align:center'>".$bar['tahun']."</td>
			<td style='text-align:center'>".$bar['tipehari']."</td>
			<td style='text-align:center'>".$bar['tipebuah']."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['basis'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['basisha'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premilebihbasis'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premibrondolan'],2)."</td>";
			
			if($bar['posting']=='0' or $bar['posting']=='2'){
				$color="background-color:yellow;";
			}elseif($bar['posting']=='3'){
				$color="background-color:red;color:white;";
			}elseif($bar['posting']=='9'){
				$color="background-color:orange;";
			}elseif($bar['posting']=='1'){
				$color="background-color:green;color:white;";
			}
			
			echo"<td style=text-align:center;cursor:pointer; title='Click...' onclick=getdatapengajuan('".$bar['nopengajuan']."');><font style=color:blue>".$bar['nopengajuan']."</font></td>";
            echo"<td style=text-align:center;cursor:pointer;".$color." title='Click...' onclick=gethistoriapproval('".$bar['nopengajuan']."');>".$arrapproval[$bar['posting']]."</td>";
			
			
			
			
			if($bar['posting']=='0' or $bar['posting']=='2'){				
				echo"<td align=center style=width:20px><img src='images/skyblue/submit.jpg' class='resicon' height='30' title='Ajukan' onclick=\"form_ajukan('".substr($bar['kodeorg'],0,4)."','".$bar['tahun']."','".$bar['kodeorg']."','".$bar['tipehari']."','".$bar['tipebuah']."');\"></td>";
			}else{
				echo"<td align=center></td>";
			}
			if($bar['posting']=='0' or $bar['posting']=='2'){
				echo"<td align=center style=width:20px><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kodeorg']."','".$bar['tahun']."','".$bar['tipehari']."','".$bar['tipebuah']."', '".$bar['basis']."','".$bar['basisha']."','".$bar['premilebihbasis']."','".$bar['premibrondolan']."');\"></td>";

				echo"<td align=center style=width:20px><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['kodeorg']."','".$bar['tahun']."','".$bar['tipehari']."','".$bar['tipebuah']."');\"></td>";
			}else{
				echo"<td align=center></td>";
				echo"<td align=center></td>";
			}
			
			echo"</tr>";	
		}
		$from = ($page * $limit) + 1;
		$to   = min((($page + 1) * $limit), $jlhbrs);

		// kalau tidak ada data
		if ($jlhbrs == 0) {
			$from = 0;
			$to   = 0;
		}

		echo "
		<tr class='rowheader'>
			<td colspan='15' align='center'>
				{$from} to {$to} Of {$jlhbrs}<br />
		";
		echo"<button class=mybutton onclick=cariBast(".($page-1).");>Prev</button>";
		if(($page+1)>=ceil($jlhbrs/$limit)){
			echo"<button class=mybutton disabled >Next</button>";
		}else{
			echo"<button class=mybutton onclick=cariBast(".($page+1).");>Next</button>";
		}
		echo"</td>
		</tr>";
 
	break;   
	case'form_ajukan':
		$kodeapproval="PNN";
		
		$optKry="";
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='".$kodeapproval."' and a.level='1' and a.kodeunit='".$param['unit']."'  order by b.namakaryawan asc";// exit('error'.$str);
		$res = fetchdata($str);
		if(count($res)==0)	{
			$tab.="Silahkan lakukan setup terlebih dahulu melalui menu :<br><b>Setup - Persetujuan</b>, dengan data sebagai berikut :<br>Kode Organisasi : <b>".$param['unit']."</b><br>Kode Approval : <b>".$kodeapproval."</b>";
		}else{			
			foreach($res as $val){
				$optKry.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";
			}
			$tab.="<table><input hidden id=unitajukan value=".$param['unit'].">
					<tr>
						<td>No Pengajuan</td><td>:</td> 
						<td id=nopengajuan>".$param['unit']."/".$kodeapproval."/".date("YmdHis")."</td> 
					</tr>
					<tr>
						<td>Kepada</td><td>:</td> 
						<td><select id=kepada style=\"width:200px;\">" . $optKry . "</select></td> 
					</tr>
					<tr>
						<td valign=top>Keterangan</td><td valign=top>:</td> 
						<td><textarea rows=3 maxlength=400 id=komentar  type='text' onkeypress='return tanpa_kutip(event)' style='width:180px;'></textarea></td> 
					</tr>
					<tr>
						<td valign=top></td><td valign=top></td> 
						<td><button onclick=ajukan('".$kodeapproval."','".$param['periode']."','".$param['kodeorg']."','".$param['tipehari']."','".$param['tipebuah']."') class=mybutton style=width:200px>Ajukan</button></td> 
					</tr>
				</table>";
		}
		echo $tab;	
	break;
	case'ajukan':
	try {
		$owlPDO->beginTransaction();
			if($param['kepada']==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			if($param['nopengajuan']==''){
				throw new PDOException('Nomor pengajuan wajib terisi.');
			}
			if($param['jenispersetujuan']==''){
				throw new PDOException('Jenis Persetujuan wajib terisi.');
			}
			
			
			# update flag menjadi 1
			$str = "update " . $dbname . ".kebun_5basispanen2 set posting='9', nopengajuan ='".$param['nopengajuan']."'
				 where kodeorg like '".$param['unit']."%' and posting in ('0','2') and tahun='".$param['periode']."' and kodeorg='".$param['kodeorg']."'
				 and tipehari='".$param['tipehari']."' and tipebuah='".$param['tipebuah']."'"; 
			#EXIT("error".$str);
			$owlPDO->exec($str);
			
			
			$str = "delete from ".$dbname.".approval where jenispersetujuan='PNN' and status='0' and notransaksi not in (select nopengajuan from ".$dbname.".kebun_5basispanen2)";
			$owlPDO->exec($str);
			
			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','".$param['nopengajuan']."','PNN','1','" . $param['kepada']."','0','".$param['komentar']."','','')";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	
    case'update':
		if($afd=='') exit("Warning: Silakan pilih Kode Blok.");
		if($tahun=='') exit("Warning: Silakan isi Periode.");
		if($tipehari=='') exit("Warning: Silakan isi Tipe Hari.");
		if($tipebuah=='') exit("Warning: Silakan isi Tipe Basis.");
		

		try{
			$dataUpd = array(
				"basis" => $basiskg,
				"basisha" => $basisha,
				"premilebihbasis" => $rplb1,
				"premibrondolan" => $brondol
			);
			$cols = array();
			foreach ($dataUpd as $key => $row) {
				$cols[] = $key;
			}
			$sUpdt = updateQuery($dbname,"kebun_5basispanen2",$dataUpd,"kodeorg='".$afd."' and tahun='".$tahun."' and tipehari='".$tipehari."' and tipebuah='".$tipebuah."'");
			$owlPDO->exec($sUpdt); 
		}catch (PDOException $e){
			echo"Gagal".$e->getMessage();
		}
    break;
    case 'deletedata':
		$sDel= deleteQuery($dbname,"kebun_5basispanen2","kodeorg='".$afd."' and tahun='".$tahun."' and tipehari='".$tipehari."' and tipebuah='".$tipebuah."'");
		try{
			$owlPDO->exec($sDel); 
			echo"";
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();  
		}
	break;
    
	case'getdatapengajuan':
		$tab.="<table border=0 cellpadding=5 class=sortable cellspacing=1 width=100%>
				<thead>
					<tr class=rowheader>
						<th align=center rowspan=2>No</th>
						<th align=center rowspan=2>".$_SESSION['lang']['unit']."</th>
						<th align=center rowspan=2 width=50px>".$_SESSION['lang']['periode']."</th>
						<th align=center rowspan=2 width=50px>Tipe Hari</th>
						<th align=center rowspan=2 width=50px>Tipe Basis</th>
						<th align=center rowspan=2 width=75px>".$_SESSION['lang']['norma']."</th>
						<th align=center rowspan=2 width=75px >".$_SESSION['lang']['premlebihbasis']." Rp/Kg</th>
						<th align=center rowspan=2 width=50px>".$_SESSION['lang']['brondol']." Rp/Kg</th>
					</tr>
				</thead>
			<tbody>";
		if($param['nopengajuan']==''){			
			exit("warning : nopengajuan tidak boleh kosong");
		}
		$tahunini=date("Y");
			
		$where="and nopengajuan='".$param['nopengajuan']."'";
		$str = "select * from ".$dbname.".kebun_5basispanen2 where 0=0 ".$where." order by kodeorg,tahun asc";
		$res = fetchdata($str);
        foreach($res as $bar){
			$no++;
			$tab.="<tr style=vertical-align:top class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['kodeorg']." - ".$optInduk[$bar['kodeorg']]."</td>
			<td style='text-align:center'>".$bar['tahun']."</td>
			<td style='text-align:center'>".$bar['tipehari']."</td>
			<td style='text-align:center'>".$bar['tipebuah']."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['basis'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premilebihbasis'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premibrondolan'],2)."</td>";
		}
		
		if($namafile!=''){
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			//$customPaper = array(0,0,850,1500);
			$dompdf->set_paper('A4','landscape');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			
			if (file_exists($namafile)){
				unlink($namafile);
			}
			file_put_contents($namafile, $dompdf->output());
		}else{			
			echo $tab;
		}	
	break;
	
    default:
    break;
}
?>