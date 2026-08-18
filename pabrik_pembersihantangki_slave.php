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
$path = "fileupload/cucitangki/";
$table='pabrik_pembersihantangki';
$jenisapproval='PKSBACUCITANGKI';
$str="select * from ".$dbname.".setup_filesize where transaksi='".$table."'";
$res=fetchdata($str);
foreach($res as $bar){
	$filesize=$bar['filesize'];
}
$opttangki="<option value=''>".$_SESSION['lang']['all']."</option>";
$optkodebarang=$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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

#= untuk unit ht
$arrunit=array();
$arrunit=getOrgDetail(13);
foreach($arrunit as $val=>$nama){
    $optunit.="<option value='".$val."'>".$val." - ".$nama."</option>";
} 

$tab='';
switch ($method) {
	
	case'gettangki':
        $str="select kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$param['kodeorg']."' and komoditi in ('CPO','KER') ";
		$res=fetchdata($str);
		foreach($res as $bar){
            $opttangki.="<option ".$select." value=".$bar['kodetangki'].">".$bar['keterangan']."</option>";
        }
        echo $opttangki;
    break;
	
	
	case'getnotransaksi':
		$tab.="<table>";
		$tab.="<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td><input type=text id=notransaksifind value='".date('Y')."' size=50 class=myinputtext style=\"width:145px;\"></td>
			</tr>";
		
		$tab.="<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td><select id=kodeorgfind onchange=gettangki() style=\"width:150px;\">".$optunit."</select></td>
		</tr>";	
		
		$tab.="<tr>
			<td>".$_SESSION['lang']['kodetangki']."</td>
			<td>:</td>
			<td><select id=kodetangkifind style=\"width:150px;\">".$opttangki."</select></td>
		</tr>";			
		
		$tab.="<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>		
			<td>
				<input type=text class=myinputtext id=tanggalfind name=tanggalfind readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:145px;/>
			</td>	
		</tr>";	
		
		$tab.="<tr><td></td><td></td><td><button class=mybutton onclick=findnotransaksi()>".$_SESSION['lang']['find']."</button></td>";
			$tab.="</tr>";
		$tab.="</table>";
	
		$tab.="<hr>";
		$tab.="<div id=formfindnotransaksi></div>";
		
	
		echo $tab;
	break;
	
	case'findnotransaksi':
	 
		
		$stream='';
		
		$stream='Data yang dapat ditarik, belum diproses dan sudah disetujui';
		$stream.="<div style=overflow:auto;height:175px;>";
		$stream.="<table cellpadding=5 cellspacing=1 border=0 class='sortable'>";
		
		$stream.="<thead>";
		$stream.="<tr class='rowheader'>";
			$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['notransaksi']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['kodeunit']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['kodetangki']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['kodebarang']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['keterangan']."</th>";
		$stream.="</tr>"; 
		
		$stream.="</thead>";  
		
		if($param['kodeorg']==''){
			exit("Warningsistem:Unit tidak boleh kosong");
		}
		
		if($param['notransaksi']!=''){
			$where=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		if($param['kodetangki']!=''){
			$where=" and kodetangki like '%".$param['kodetangki']."%'";
		}
		if($param['kodeorg']!=''){
			$where=" and kodeorg like '%".$param['kodeorg']."%'";
		}
		if($param['tanggal']!=''){
			$where=" and tanggal = '".tanggalsystemn($param['tanggal'])."'";
		}
		$no=0;		
		$str="select * from ".$dbname.".".$table." where 1=1 ".$where." and posting=1 and noba='' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$pointer=$klik=$title='';
			$bgcolor='bgcolor=red';
			if($bar['posting']=='1'){
				$klik="onclick=\"movenotransaksi('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['kodetangki']."','".$bar['kodebarang']."','".tanggalnormal($bar['tanggal'])."','".hidezerodecimal($bar['sawal'])."')\" ";
				$pointer="style=cursor:pointer";
				$title="title='Klik Data untuk lanjut transaksi'";
				$bgcolor='';
			}
			$stream.="<tr class=rowcontent ".$klik.">";
				$stream.="<td ".$pointer." ".$title." align=center ".$bgcolor.">".$no."</td>";
				$stream.="<td ".$pointer." ".$title." ".$bgcolor.">".$bar['notransaksi']."</td>";
				$stream.="<td ".$pointer." ".$title." ".$bgcolor.">".$bar['kodeorg']."</td>";
				$stream.="<td ".$pointer." ".$title." ".$bgcolor.">".$bar['kodetangki']."</td>";
				$stream.="<td ".$pointer." ".$title." ".$bgcolor.">".$bar['kodebarang']."</td>";
				$stream.="<td ".$pointer." ".$title." ".$bgcolor.">".$bar['keterangan']."</td>";
			$stream.="</tr>";
		}	
		$stream.="</table><div>";
		echo $stream;
	break;
	
	case'formajukan':
	
		$str="select * from ".$dbname.".".$table." where noba='".$param['noba']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodeorg=$bar['kodeorg'];
			$posting=$bar['postingba'];
			$param['notransaksi']=$bar['notransaksi'];
		}
		$tab.="File Upload";
		$tab.="<table border=0 cellspacing=1 class=sortable cellpadding=5>
			<thead>
			<tr style='font-weight:bold'>
				<td align='center'>No.</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfile'>";
			$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' or notransaksi='".$param['noba']."' ";
			$res=fetchdata($str);
				foreach($res as $key=>$val){
					@$icon = seticonfile($val['formaticon']);
					$no++;
					$tab.="<tr id='ppDetailTable' class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
					$tab.="<td align='center'><img src=".$icon." class=resicon></a></td>";
					$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
						<td style='text-align:left'>".$val['namafile']."</td>
						<td align=center>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
					$tab."	</td>
					</tr>";
				}	
		$tab.="</tbody>
		</table><br>";
		
		$tab.="Daftar Persetujuan";
		$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable><tbody class=rowcontent>";
		$tab.="<thead><tr>
			
				<td align=center>".$_SESSION['lang']['level']."</td>
				<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
			</tr></thead>";
		
		$optposting=array(''=>$_SESSION['lang']['pilihdata'],'0'=>'Proses Persetujuan','1'=>'Disetujui','2'=>'Ditolak','3'=>'Dikoreksi','9'=>'Proses Persetujuan');
		//0; belum proses; 1:disetujui;3:dikoreksi;2:ditolak;9:proses pengajuan
		$str = "select * from ".$dbname.".approval where notransaksi='".$param['noba']."' order by level asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
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
			$tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable  width=100%>";
			$tab.="<thead>
							<tr style='font-weight:bold'>
								<td align='center' colspan=3>".$_SESSION['lang']['persetujuan']."</td>
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
					<button class=mybutton onclick=saveajukan('".$param['noba']."','".$param['page']."','".$countApp."')>Simpan</button>
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
			$str="delete from ".$dbname.".approval where notransaksi='".$param['noba']."'";
			$owlPDO->exec($str);
			
			
			for($i=1;$i<=$param['maxaproval'];$i++){
				if($param['persetujuan'][$i]==''){
					throw new PDOException("Persetujuan ".$i." belum dipilih.");
				}
			}
			#= delete 1st untuk aprovalnya
			$str = "delete from " . $dbname . ".approval where notransaksi='".$param['noba']."' and jenispersetujuan = '".$jenisapproval."'";
			$owlPDO->exec($str);
			
			$str = "update " . $dbname . ".".$table." set postingba=9 where noba='".$param['noba']."'";
			$owlPDO->exec($str);
			for($i=1;$i<=$param['maxaproval'];$i++){
				#= insert
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
					   values('".$param['noba']."','".$jenisapproval."','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";	
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
		$owlPDO->rollback();
		echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());

		}
	break;
	
	case'loaddata':
		#= untuk unit ht
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
		$where="1=1 and  kodeorg in ('".implode("','",$dtunit)."') and noba!='' ";
		
		if($param['tanggalmulai']!='' and $param['tanggalselesai']!=''){
			$where.=" and tanggal between '".tanggalsystemn($param['tanggalmulai'])."' and '".tanggalsystemn($param['tanggalselesai'])."'";
		}
		
		if($param['notransaksi']!=''){
			$where.=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		
		if($param['kodeorg']!=''){
			$where.=" and kodeorg = '".$param['kodeorg']."'";
		}
		
		// echo $where;
		
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		$colspan=18;
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jumrow = $bar['jumrow'];
		}
		$no = 0;
		$no=$maxdisplay;
		$statusapp = '';
		$str = "select * from ".$dbname.".".$table." where ".$where." order by tanggal desc limit " . $offset . "," . $limit . " ";

		$res=fetchdata($str);
		foreach($res as $bar){
				# Status Approval
			$order = 'ASC';
			if ($bar['postingba'] == 0) {
				$statusapp = $_SESSION['lang']['belumdiajukan'];
			} else {
				if ($bar['postingba'] == 1) {
					$table = "approval";
					$whereapp = "status = '1'";
					$ket = $_SESSION['lang']['disetujui'];
					$order = 'DESC';
				} else if ($bar['postingba'] == 9) {
					$table = "approval";
					$whereapp = "status = '0'";
					$ket = $_SESSION['lang']['wait_approval'];
				} else if ($bar['postingba'] == 2) {
					$table = "approval";
					$whereapp = "status = '2'";
					$ket = $_SESSION['lang']['ditolak'];
				} else if ($bar['postingba'] == 3) {
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
			if($bar['postingba']==3){
				$bgcolor="bgcolor='orange'  title='Koreksi'";
			}
			if($bar['postingba']==2){
				$bgcolor="bgcolor='red'  title='Ditolak'";
			}
			$no++;
			$tab.="<tr ".$bgcolor.">";
				$tab.="<td align=center valign=top>".$no."</td>";
				$tab.="<td valign=top>".$bar['noba']."</td>";
				$tab.="<td valign=top>".$bar['notransaksi']."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggalba'])."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggalcuci'])."</td>";
				$tab.="<td valign=top>".$nmorganisasi[$bar['kodeorg']]."</td>";
				$tab.="<td valign=top>".$bar['kodetangki']."</td>";
				
				$tab.="<td align=right valign=top>".hidezerodecimal($bar['sawal'],2)."</td>";
				$tab.="<td align=right valign=top>".hidezerodecimal($bar['jumlah'],2)."</td>";
				$tab.="<td align=left valign=top>".$namabarang[$bar['kodebarang']]."</td>";
				$tab.="<td valign=top>".nl2br($bar['keteranganba'])."</td>";
				$tab.="<td valign=top>".$namakaryawan[$bar['createby']]."</td>";
				$tab.="<td valign=top align=center>".$statusapp."</td>";
				
				if($bar['postingba']==0 || $bar['postingba']==3){
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('".$bar['noba']."');\"></td>";
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('".$bar['noba']."','".$page."');\"></td>";		
				} else if($bar['postingba']==9){
					$tab.="<td align=center valign=top  style=\"width:20px;\"></td>";
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/icons/04/16/04.png class=zImgBtn height='30'  title='Proses Persetujuan'></td>";
				} else {
					$tab.="<td align=center valign=top  style=\"width:20px;\"></td>";
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/icons/04/16/02.png  class=zImgBtn height='30'  title='Disetujui/Tolak' ></td>";
					
				}						
				$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/skyblue/zoom.png class=zImgBtn height='30'  title='Ajukan Persetujuan' onclick=\"formajukan('".$bar['noba']."','".$page."');\"></td>";
				$tab.="<td align=center valign=top  style=\"width:20px;\"></td>";
				// $tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Nomor Transaksi : ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."','".$bar['noakun']."','".$bar['tipetransaksi']."','".$bar['kodeorg']."');\"></td>";
			$tab.="</tr>";
        }
		$tab2=createpaging($jumrow,$limit,$page,$colspan,'loaddata','getPage');
		//$tab.="</table>";
        echo $tab."####".$tab2;
		
	break;
	
	case'deleteht':
		try{
			$owlPDO->beginTransaction();
			
			
			#= cari nomor transaksi pengajuan karna itu PK-nya
			 $str = "select * from  ".$dbname.".".$table." where noba='".$param['noba']."'";
            $res=fetchdata($str);
			foreach($res as $bar){
				$param['notransaksi']=$bar['notransaksi'];
			}

            // ini dia: ambil semua file
            $str = "select id, namafile from ".$dbname.".listfileupload where notransaksi='".$param['noba']."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $listhapusfile[$bar['id']]=$bar['id'];
                $hapusini[$bar['id']]['namafile']=$bar['namafile'];
            }

            if(!empty($listhapusfile))foreach($listhapusfile as $idnyaz){
                $namafile=$hapusini[$idnyaz]['namafile'];
                $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['noba']."' and namafile='".$namafile."'";
                // exit('error'.$str);
                try{
                    $owlPDO->exec($str);
                    // $pathx = $path.$namafile;
                    // unlink($pathx);
                }
                catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
            }
			
			##Delete kas/bank HT
			$str = "update  ".$dbname.".".$table." set noba='',tanggalba='',keteranganba='',tanggalba='' where notransaksi='".$param['notransaksi']."' ";
			$owlPDO->exec($str);

			## DELETE FROM TABLE APPROVAL
			$str="delete from ".$dbname.".approval where notransaksi='".$param['noba']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}
		
		
		
	break;
	
	case'geteditht':
		$str = "select * from ".$dbname.".".$table."  where noba='".$param['noba']."'";
		// echo $str;exit("Error:A");
		$res=fetchdata($str);
		echo 
		$res[0]['notransaksi']."###".
		$res[0]['kodeorg']."###".
		$res[0]['kodetangki']."###".
		$res[0]['kodebarang']."###".
		tanggalnormal($res[0]['tanggal'])."###".
		number_format($res[0]['sawal'],2)."###".
		$res[0]['noba']."###".
		tanggalnormal($res[0]['tanggalba'])."###".
		number_format($res[0]['jumlah'],2)."###".
		$res[0]['keteranganba'];
	break;
	
	case'saveht':
	
		if($param['notransaksi']==''){
			exit("Warning:No. Transaksi Pengajuan masih kosong");
		}
	
		if($param['tanggalba']==''){
			exit("Warning:Tanggal BA masih kosong");
		}
	
		if($param['keteranganba']==''){
			exit("Warning:Keterangan BA tidak boleh kosong");
		}
		
		$param['jumlah']=str_replace(',', '',$param['jumlah']);
		
		if($param['noba']==''){
			
			$kodeorg=$param['kodeorg'];
			$tanggal=tanggalsystemn($param['tanggalba']);
			
			$param['noba'] = generatebacucitangki();		
			$str = "update ".$dbname.".".$table." set 
				noba='".$param['noba']."',
				tanggalba='".tanggalsystemn($param['tanggalba'])."',
				jumlah='".$param['jumlah']."',
				keteranganba='".$param['keteranganba']."',
				updateby='".$_SESSION['standard']['userid']."'	
				where notransaksi = '".$param['notransaksi']."'";
				// exit("Error".$str);
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "update ".$dbname.".".$table." set 
				noba='".$param['noba']."',
				tanggalba='".tanggalsystemn($param['tanggalba'])."',
				jumlah='".$param['jumlah']."',
				keteranganba='".$param['keteranganba']."',
				updateby='".$_SESSION['standard']['userid']."'	
				where notransaksi = '".$param['notransaksi']."'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}	
		}
		
		echo $param['noba'];
		
	break;
	
	
	
	case'submitfile':
	
		// $filesize=1;
	
		#= jadikan try commi
		try {
			
			$owlPDO->beginTransaction();
			
			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp=str_replace('-','',str_replace('/','',$param['noba']));

			if ($_FILES['file']['size'] > $filesize){
				throw new PDOException("Ukuran File melebihi ".number_format($filezie/1024)." KB; ukuran file ini ".number_format($_FILES['file']['size']/1024,2)." Kb");
			}

			if($param['fileupload']!=''){
				if($_FILES['file']['error']==0){    
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $param['kriteriaefil']."_".$nmTemp."_".$his."".$filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
						$str = "insert into ".$dbname.".listfileupload values ('','".$param['noba']."','".$filename."','".$filetype."','".$param['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}else{
						throw new PDOException("Format file upload tidak boleh ".$filetype);
					}
				}
			}
			
			if (!file_exists($path.$filename)) {
				throw new PDOException("File gagal diupload");
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());

		}			
		
    break;
	
	case'loadfiles':
			
		$str = "select * from ".$dbname.".".$table." where noba='".$param['noba']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$param['notransaksi']=$bar['notransaksi'];
		}		
	
	
		$form='';
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=resicon></a></td>";
				$form.= "<td>".$bar['kriteriaefil']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a></td>";
			$form.= "</tr>";
		}
		
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['noba']."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=resicon></a></td>";
				$form.= "<td>".$bar['kriteriaefil']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$bar['noba']."','".$bar['namafile']."');\" ></td>";
			$form.= "</tr>";
		}
		
		echo $form;
    break;  
	
	
	case 'deletefile':
        $namafile=$param['namafile'];
        $str="delete from ".$dbname.".listfileupload where noba='".$param['noba']."' and namafile='".$param['namafile']."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            // $pathx = $path.$namafile;
            // unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
	break;
	
}
?>