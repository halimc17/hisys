<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$param = $_POST;
$table='pmn_hargajualtbs';
$jenisapproval='HJT';

$opttangki=$optkodebarang=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $bar){
	$nmorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];	
	$tipeorganisasi[$bar['kodeorganisasi']]=$bar['tipe'];	
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];	
}

$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res=fetchdata($str);
foreach($res as $bar){
	$namabarang[$bar['kodebarang']]=$bar['namabarang'];	
}

$str="select * from ".$dbname.".pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];	
}

#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(13);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 

$tab='';
switch ($method) {
	
	
	case'formajukan':
		$no=0;
		$tab.="Daftar Harga";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
		$tab.="<thead><tr>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center>".$_SESSION['lang']['customer']."</th>
				<th align=center>".$_SESSION['lang']['tahuntanam']."</th>
				<th align=center>".$_SESSION['lang']['hargadisbun']."</th>
				<th align=center>".$_SESSION['lang']['harga']." Aktual</th>
			</tr></thead>";
	
	
		$str="select * from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$kodeorg=$bar['kodeorg'];
			$posting=$bar['posting'];
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center>".$bar['notransaksi']."</td>";
				$tab.="<td align=center>".$namacustomer[$bar['kodecustomer']]."</td>";
				$tab.="<td align=right>".$bar['tahuntanam']."</td>";
				$tab.="<td align=right>".hidezerodecimal($bar['hargadisbun'])."</td>";
				$tab.="<td align=right>".hidezerodecimal($bar['harga'])."</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody></table> <br />";	 
		
		$tab.="Daftar Persetujuan";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
		$tab.="<thead><tr>
			
				<th align=center>".$_SESSION['lang']['level']."</th>
				<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center>".$_SESSION['lang']['status']."</th>
				<th align=center>".$_SESSION['lang']['keterangan']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
			</tr></thead>";
		
		$optposting=array(''=>$_SESSION['lang']['pilihdata'],'0'=>'Proses Persetujuan','1'=>'Disetujui','2'=>'Ditolak','3'=>'Dikoreksi','9'=>'Proses Persetujuan');
		//0; belum proses; 1:disetujui;3:dikoreksi;2:ditolak;9:proses pengajuan
		$str = "select * from ".$dbname.".approval where notransaksi='".$param['notransaksi']."' order by level asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$tab.="<tr class=rowcontent>";
			$nmkaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan'," karyawanid='".$bar['karyawanid']."'");	
				$tab.="<td align=center>".$bar['level']."</td>";
				$tab.="<td>".@$nmkaryawan[$bar['karyawanid']]."</td>";
				$tab.="<td>".$optposting[$bar['status']]."</td>";
				$tab.="<td>".$bar['komentar']."</td>";
				if($bar['tanggal']=='0000-00-00 00:00:00'){
					$tab.="<td></td>";
				}else{
				$tab.="<td>".tanggalnormal(substr($bar['tanggal'],0,10))." ".substr($bar['tanggal'],11,8)."</td>";
				}
			$tab.="</tr>";
			

		}			
		$tab.="</tbody></table> <br />";	 
		
		
		if($posting==0 || $posting==3){
			$tab.="Ajukan Persetujuan";
			$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
			$tab.="<thead>
							<tr style='font-weight:bold'>
								<th align='center' colspan=3>".$_SESSION['lang']['persetujuan']."</th>
							</tr></thead>";
			$countApp = getCountApproval($jenisapproval,$kodeorg);
			for($i=1;$i<=$countApp;$i++){
				$arrList = listApprove($i,$jenisapproval,$kodeorg);
				$optpersetujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				foreach($arrList as $key=>$val){
					$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
				}
				$tab.="<tr  class=rowcontent>
				<td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
				<td>:</td>
				<td colspan=1><select style=\"width:154px;\" id=persetujuan".$i.">".$optpersetujuan."</select></td>
				</tr>";  
			}   
			$tab.="
			<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton onclick=saveajukan('".$param['notransaksi']."','".$param['page']."','".$countApp."')>Simpan</button>
					</td>
			</tr>
			</table>";
		}
		echo $tab;
	break;
	
	
	
	case'saveajukan':
		try {
			$owlPDO->beginTransaction();
			
			## DELETE FROM TABLE APPROVAL
			$str="delete from ".$dbname.".approval where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			
			for($i=1;$i<=$param['maxaproval'];$i++){
				if($param['persetujuan'][$i]==''){
					throw new PDOException("Persetujuan ".$i." belum dipilih.");
				}
			}
			#= delete 1st untuk aprovalnya
			$str = "delete from " . $dbname . ".approval where notransaksi='".$param['notransaksi']."' and jenispersetujuan = '".$jenisapproval."'";
			$owlPDO->exec($str);
			
			$str = "update " . $dbname . ".".$table." set posting=9 where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			for($i=1;$i<=$param['maxaproval'];$i++){
				#= insert
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
					   values('".$param['notransaksi']."','".$jenisapproval."','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";	
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
		$owlPDO->rollback();
		echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());

		}
	break;
	
	
	case'deleteht':
		try{
			$owlPDO->beginTransaction();

			##Delete kas/bank HT
			$str = "delete from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."' and kodecustomer='".$param['kodecustomer']."'  and tahuntanam='".$param['tahuntanam']."' ";
			$owlPDO->exec($str);

			## DELETE FROM TABLE APPROVAL
			$str="delete from ".$dbname.".approval where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}
		
	break;
	
	case'geteditht':
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$param['notransaksi']."' and kodecustomer='".$param['kodecustomer']."'  and tahuntanam='".$param['tahuntanam']."' ";
		$res=fetchdata($str);
		echo 
		$res[0]['notransaksi']."###".
		$res[0]['kodeorg']."###".
		$res[0]['kodecustomer']."###".
		tanggalnormal($res[0]['tanggal'])."###".
		tanggalnormal($res[0]['tanggal2'])."###".
		$res[0]['tahuntanam']."###".
		number_format($res[0]['hargadisbun'],2)."###".
		number_format($res[0]['harga'],2);
	break;
	
	case'saveht':
	
	
		if($param['kodeorg']==''){
			exit("Warning:Unit masih kosong");
		}
		if($param['kodecustomer']==''){
			exit("Warning:Customer masih kosong");
		}
		if($param['tanggal']==''){
			exit("Warning:Tanggal masih kosong");
		}
		if($param['tanggal2']==''){
			exit("Warning:Tanggal masih kosong");
		}
		if($param['harga']==''){
			exit("Warning:Saldo Awal masih kosong");
		}
		if($param['tahuntanam']==''){
			exit("Warning:Tahun tanam / grade masih kosong");
		}
		
		$param['harga']=str_replace(',', '',$param['harga']);
		$param['hargadisbun']=str_replace(',', '',$param['hargadisbun']);
		
		
		
		
		#= cek data sudah ada atau belum
		#bentuk tanggal between
		$arrtanggal=rangeTanggalarr(tanggalsystemn($param['tanggal']),tanggalsystemn($param['tanggal2']));
		// echo"<pre>";
		// print_r($arrtanggal);
		// exit("Error:A");

		if($param['notransaksi'] == '') { # Jika update tidak perlu validasi
			$texterror='';
			foreach($arrtanggal as $tglcek){
				$str="select count(*) as jumlah,notransaksi from ".$dbname.".pmn_hargajualtbs where kodecustomer='".$param['kodecustomer']."' and kodeorg='".$param['kodeorg']."' and tanggal='".$tglcek."' and tahuntanam='".$param['tahuntanam']."'"; 
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				if($bar['jumlah']>0){
					$texterror.="sudah ada data ditanggal tbs ".tanggalnormal($tglcek)." untuk ".$param['kodecustomer']." dengan nomor transaksi ".$bar['notransaksi']."\n ";
				}
				
				$str="select count(*) as jumlah,notransaksi from ".$dbname.".pmn_hargajualtbs where kodecustomer='".$param['kodecustomer']."' and kodeorg='".$param['kodeorg']."' and tanggal2='".$tglcek."' and tahuntanam='".$param['tahuntanam']."'"; 
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				if($bar['jumlah']>0){
					$texterror.="sudah ada data ditanggal tbs ".tanggalnormal($tglcek)."; untuk ".$param['kodecustomer']." \n ";
				}
			}
			
			if($texterror!=''){
				echo $texterror;
				exit("Warningsistem:Gagal melakukan penyimpanan");
			}
		}
		
		
		
		
		if($param['notransaksi']==''){
			$param['notransaksi']=str_replace('-','',tanggalsystemn($param['tanggal'])).$param['kodeorg'].$param['kodecustomer'];
			
			$str = "insert into ".$dbname.".".$table." (notransaksi,kodeorg,kodecustomer,tanggal,tanggal2,tahuntanam,hargadisbun,bjr,harga,createby,createtime) 
				values ('".$param['notransaksi']."','".$param['kodeorg']."','".$param['kodecustomer']."','".tanggalsystemn($param['tanggal'])."','".tanggalsystemn($param['tanggal2'])."','".$param['tahuntanam']."','".$param['hargadisbun']."','".$param['bjr']."','".$param['harga']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "update ".$dbname.".".$table." set 
				harga='".$param['harga']."',hargadisbun='".$param['hargadisbun']."',bjr='".$param['bjr']."',tanggal2='".tanggalsystemn($param['tanggal2'])."',updateby='".$_SESSION['standard']['userid']."' where notransaksi = '".$param['notransaksi']."' and tahuntanam='".$param['tahuntanam']."'";
				// exit("Error".$str);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}	
		}
	break;
	
	case'loaddata':
		#= untuk unit ht
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		$where='';
		
		if($param['tanggal']!='' and $param['tanggal2']!=''){
			$where.=" and tanggal between '".tanggalsystemn($param['tanggal'])."' and '".tanggalsystemn($param['tanggal2'])."'";
		}
		
		if($param['kodecustomer']!=''){
			$where.=" and kodecustomer like '%".$param['kodecustomer']."%'";
		}
		
		if($param['kodeorg']!=''){
			$where.=" and kodeorg = '".$param['kodeorg']."'";
		} else{
			$where.="and  kodeorg in ('".implode("','",$dtunit)."') ";
		}
		
		
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		$colspan=17;
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where 1=1 ".$where."";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jumrow = $bar['jumrow'];
		}
		$no = 0;
		$no=$maxdisplay;
		$statusapp = '';
		$str = "select * from ".$dbname.".".$table." where 1=1 ".$where." order by tanggal desc limit " . $offset . "," . $limit . " ";

		$res=fetchdata($str);
		foreach($res as $bar){
			# Status Approval
			$order = 'ASC';
			if ($bar['posting'] == 0) {
				$statusapp = $_SESSION['lang']['belumdiajukan'];
			} else {
				if ($bar['posting'] == 1) {
					$table = "approval";
					$whereapp = "status = '1'";
					$ket = $_SESSION['lang']['disetujui'];
					$order = 'DESC';
				} else if ($bar['posting'] == 9) {
					$table = "approval";
					$whereapp = "status = '0'";
					$ket = $_SESSION['lang']['wait_approval'];
				} else if ($bar['posting'] == 2) {
					$table = "approval";
					$whereapp = "status = '2'";
					$ket = $_SESSION['lang']['ditolak'];
				} else if ($bar['posting'] == 3) {
					$table = "approval";
					$whereapp = "status = '3'";
					$ket = "Di".$_SESSION['lang']['koreksi'];
				}

				$str = "SELECT a.karyawanid, b.namakaryawan FROM ".$dbname.".".$table." a
						JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
						WHERE notransaksi = '".$bar['notransaksi']."' AND ".$whereapp."
						ORDER BY level ".$order." LIMIT 1";
				$res = fetchdata($str);
				$statusapp = $ket."<br> (".$res[0]['namakaryawan'].")";
			}
			
			#=datakaryawan
			$strdt="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid in ('".$bar['createby']."','".$bar['updateby']."') ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namakaryawan[$bardt['karyawanid']]=$bardt['namakaryawan'];
			}
			
			
			
			$bgcolor='class=rowcontent';
			if($bar['posting']==3){
				$bgcolor="bgcolor='orange'  title='Koreksi'";
			}
			if($bar['posting']==2){
				$bgcolor="bgcolor='red'  title='Ditolak'";
			}
			$no++;
			$tab.="<tr ".$bgcolor.">";
				$tab.="<td align=center valign=top>".$no."</td>";
				$tab.="<td valign=top>".$bar['notransaksi']."</td>";
				$tab.="<td valign=top>".$nmorganisasi[$bar['kodeorg']]."</td>";
				$tab.="<td valign=top>".$bar['kodecustomer']." - ".$namacustomer[$bar['kodecustomer']]."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggal'])." s/d ".tanggalnormal($bar['tanggal2'])."</td>";
				$tab.="<td valign=top align=right>".$bar['tahuntanam']."</td>";
				$tab.="<td valign=top align=right>".$bar['bjr']."</td>";
				$tab.="<td align=right valign=top>".hidezerodecimal($bar['hargadisbun'],2)."</td>";
				$tab.="<td align=right valign=top>".hidezerodecimal($bar['harga'],2)."</td>";
				$tab.="<td valign=top>".$namakaryawan[$bar['createby']]."</td>";
				$tab.="<td valign=top align=center>".$statusapp."</td>";
				
				if($bar['posting']==0 || $bar['posting']==3){
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('".$bar['notransaksi']."','".$bar['kodecustomer']."','".$bar['tahuntanam']."');\"></td>";
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('".$bar['notransaksi']."','".$bar['kodecustomer']."','".$bar['tahuntanam']."','".$page."');\"></td>";		
				} else if($bar['posting']==9){
					$tab.="<td align=center valign=top  style=\"width:20px;\"></td>";
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/icons/04/16/04.png class=zImgBtn height='30'  title='Proses Persetujuan'></td>";
				} else {
					$tab.="<td align=center valign=top  style=\"width:20px;\"></td>";
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/icons/04/16/02.png  class=zImgBtn height='30'  title='Disetujui/Tolak' ></td>";
					
				}						
				$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/skyblue/zoom.png class=zImgBtn height='30'  title='Ajukan Persetujuan' onclick=\"formajukan('".$bar['notransaksi']."','".$page."');\"></td>";
				$tab.="<td align=center valign=top  style=\"width:20px;\"></td>";
				// $tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Nomor Transaksi : ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."','".$bar['noakun']."','".$bar['tipetransaksi']."','".$bar['kodeorg']."');\"></td>";
			$tab.="</tr>";
        }
		$tab2=createpaging($jumrow,$limit,$page,$colspan,'loaddata','getPage');
		//$tab.="</table>";
        echo $tab."####".$tab2;
		
	break;
	
	
	
}
?>