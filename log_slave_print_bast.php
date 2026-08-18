<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zFunction.php');


$notransaksi   = checkPostGet('notransaksi','');

$str="select * from ".$dbname.".log_transaksiht where notransaksi='".$notransaksi."' ";
$res=fetchData($str)[0];


echo"<table>";
echo"<tr>
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td>".getNamaOrg($res['kodept'])."</td>
	
		<td>".$_SESSION['lang']['gudang']."</td>
		<td>:</td>
		<td>".getNamaOrg($res['kodegudang'])."</td>
	</tr>";
echo"<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td>
		<td>:</td>
		<td>".$res['notransaksi']."</td>
	
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>".$res['tanggal']."</td>
	</tr>";
echo"<tr>
		<td>".$_SESSION['lang']['untukunit']."</td>
		<td>:</td>
		<td>".getNamaOrg($res['untukunit'])."</td>
	
		<td>".$_SESSION['lang']['diterima']."</td>
		<td>:</td>
		<td>".getKary($res['namapenerima'])."</td>
	</tr>";
echo"<tr>
		<td>".$_SESSION['lang']['catatan']."</td>
		<td>:</td>
		<td colspan=6>".$res['keterangan']."</td>
	
	</tr>";
echo"</table>";


echo"<table class=sortable cellspacing=1 border=0 cellpadding=5>
	   <thead>
	   <tr class=rowheader>
	   <th align=center>No</th>
		<th align=center>".$_SESSION['lang']['kode']."</th>
		<th align=center>".$_SESSION['lang']['namabarang']."</th>
		<th align=center>".$_SESSION['lang']['satuan']."</th>
		<th align=center>".$_SESSION['lang']['jumlah']."</th>
		<th align=center hidden>".$_SESSION['lang']['pt']."</th>
		<th align=center>".$_SESSION['lang']['untukunit']."</th>
		<th align=center>".$_SESSION['lang']['kodeblok']."</th>
		<th align=center style=display:none>".$_SESSION['lang']['segment']."</th>
		<th align=center>".$_SESSION['lang']['kodenopol']."</th>
		<th align=center>KM / HM</th>
		<th align=center>".$_SESSION['lang']['kegiatan']."</th>
		<th align=center>PIC/Dept</th>
	   </tr>
	   </thead>
		   <tbody>";
		   
   
@$namDept=makeOption($dbname,'sdm_5departemen','kode,nama');
   
//ambil data untuk ditampilkan
$strj_cek1="select a.*,b.untukpt as pt,b.norequest as norequest,
	   b.untukunit as unit from ".$dbname.".log_transaksi_vw_detail a 
	   left join  ".$dbname.".log_transaksiht b
	   on a.notransaksi=b.notransaksi
	   where a.notransaksi='".$notransaksi."' order by waktutransaksi asc ";
	   $res_cekk1 = fetchData($strj_cek1);

$strj_cek2="select a.*,b.untukpt as pt,b.norequest as norequest,
				  b.untukunit as unit from ".$dbname.".log_transaksi_vw a 
				  left join  ".$dbname.".log_transaksiht b
				  on a.notransaksi=b.notransaksi
				  where a.notransaksi='".$notransaksi."' order by waktutransaksi asc ";
	   $res_cekk2 = fetchData($strj_cek2);

	   if(count($res_cekk1) >= count($res_cekk2)){
		$strj="select a.*,b.untukpt as pt,b.norequest as norequest,
		b.untukunit as unit from ".$dbname.".log_transaksi_vw_detail a 
		left join  ".$dbname.".log_transaksiht b
		on a.notransaksi=b.notransaksi
		where a.notransaksi='".$notransaksi."' order by waktutransaksi asc ";
	   }else{
		$strj="select a.*,b.untukpt as pt,b.norequest as norequest,
		b.untukunit as unit from ".$dbname.".log_transaksi_vw a 
		left join  ".$dbname.".log_transaksiht b
		on a.notransaksi=b.notransaksi
		where a.notransaksi='".$notransaksi."' order by waktutransaksi asc ";
	   }
	   

$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
$resj->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($barj=$resj->fetch()) {
	$dept=$barj->kodedptrmn;
	
	$no+=1;
	//ambil namabarang
	$namabarangk='';
	$strk="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$barj->kodebarang."'";
	$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
	$resk->setFetchMode(PDO::FETCH_OBJ);
	while($bark=$resk->fetch()) {
		$namabarangk=$bark->namabarang;
	}
	//ambil kegiatan
	$namakegiatan='';
	$strk="select namakegiatan from ".$dbname.".setup_kegiatan where kodekegiatan='".$barj->kodekegiatan."'";
	$resk=$owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
	$resk->setFetchMode(PDO::FETCH_OBJ);
	while($bark=$resk->fetch())
	{
		$namakegiatan=$bark->namakegiatan;
	}
	
	$optSegment = makeOption($dbname,'keu_5segment','kodesegment,namasegment',"kodesegment='".$barj->kodesegment."'");
	// $nmblok = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barj->kodeblok."'");
	$nmblok = makeOption($dbname,'organisasi','indukblok,namaindukblok',"indukblok='".$barj->kodeblok."'");
	$nmpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barj->pt."'");
	$nmunit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barj->unit."'");
	@$nopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$barj->kodemesin."'");
	if(@$nopol[$barj->kodemesin]!=''){
		$kodemesinx=$barj->kodemesin." - ".$nopol[$barj->kodemesin];
	}else{
		$kodemesinx=$barj->kodemesin;
	}
	
	
	echo"<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td>".$barj->kodebarang."</td>
		<td>".$namabarangk."</td>
		<td>".$barj->satuan."</td>
		<!--<td align=right>".number_format($barj->jumlah,2,'.',',')."</td>-->
		<td align=right>".$barj->jumlah."</td>
		<td hidden align=center>".$barj->pt."</td>
		<td>".$barj->unit." - ".$nmunit[$barj->unit]."</td>
		<td>".$barj->kodeblok." - ".($nmblok[$barj->kodeblok] != '' ? $nmblok[$barj->kodeblok] : getNamaOrg($barj->kodeblok))."</td>
		<td hidden>".$optSegment[$barj->kodesegment]."</td>
		<td>".$kodemesinx."<br>".getNopol($kodemesinx)."</td>
		<td align=right>".$barj->kmhm."</td>
		<td>".$barj->kodekegiatan." - ".$namakegiatan."</td>
		<td>
			<table>";
				if($barj->norequest == ''){
					$where = " notransaksi='".$notransaksi."' and kodebarang='".$barj->kodebarang."'";
				}else{
					$where = " notransaksi='".$barj->norequest."' and kodebarang='".$barj->kodebarang."' and realisasi!='0'";
				}
				$nopic = 0;
				$str="select * from ".$dbname.".log_permintaanpicdt where ".$where."";
				$res = fetchData($str);
				if(count($res)>0){					
					foreach($res as $key=>$val){
						$nopic++;
						$optNmKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
						echo"<tr>
							<td>".$nopic.".</td>
							<td>".$optNmKary[$val['karyawanid']]."</td>
						</tr>";
					}
				}else{
					echo"<tr>
							<td>".$dept."</td>
							<td>".$namDept[$dept]."</td>
						</tr>";
				}
			echo"</table>
		</td>";
		echo"</tr>";
}

echo"</tbody>
		   <tfoot>
		   </tfoot>
	   </table>";


?>
