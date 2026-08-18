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
$jenisapproval='PKSCUCITANGKI';
$str="select * from ".$dbname.".setup_filesize where transaksi='".$table."'";
$res=fetchdata($str);
foreach($res as $bar){
	$filesize=$bar['filesize'];
}
$opttangki=$optkodebarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
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
$tab='';
switch ($method) {
	
	case'formajukan':
	
		$str="select * from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodeorg=$bar['kodeorg'];
			$posting=$bar['posting'];
		}
		$tab.="File Upload";
		$tab.="<table border=0 cellspacing=1 class=sortable  cellpadding=5>
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
			$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."'";
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
		$str = "select * from ".$dbname.".approval where notransaksi='".$param['notransaksi']."' order by level asc";
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
			$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
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
	
	case'loaddata':
		#= untuk unit ht
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
		$where="1=1 and  kodeorg in ('".implode("','",$dtunit)."') ";
		
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
		$colspan=15;
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
				$tab.="<td valign=top>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggalcuci'])."</td>";
				$tab.="<td valign=top>".$nmorganisasi[$bar['kodeorg']]."</td>";
				$tab.="<td valign=top>".$bar['kodetangki']."</td>";
				
				$tab.="<td align=right valign=top>".hidezerodecimal($bar['sawal'],2)."</td>";
				$tab.="<td align=left valign=top>".$namabarang[$bar['kodebarang']]."</td>";
				$tab.="<td valign=top>".nl2br($bar['keterangan'])."</td>";
				$tab.="<td valign=top>".$namakaryawan[$bar['createby']]."</td>";
				$tab.="<td valign=top align=center>".$statusapp."</td>";
				
				if($bar['posting']==0 || $bar['posting']==3){
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('".$bar['notransaksi']."');\"></td>";
					$tab.="<td align=center valign=top  style=\"width:20px;\"><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('".$bar['notransaksi']."','".$page."');\"></td>";		
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
	
	case'deleteht':
		try{
			$owlPDO->beginTransaction();

            // ini dia: ambil semua file
            $str = "select id, namafile from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $listhapusfile[$bar['id']]=$bar['id'];
                $hapusini[$bar['id']]['namafile']=$bar['namafile'];
            }

            if(!empty($listhapusfile))foreach($listhapusfile as $idnyaz){
                $namafile=$hapusini[$idnyaz]['namafile'];
                $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$namafile."'";
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
			$str = "delete from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."' ";
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
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		echo 
		$res[0]['notransaksi']."###".
		$res[0]['kodeorg']."###".
		$res[0]['kodetangki']."###".
		$res[0]['kodebarang']."###".
		tanggalnormal($res[0]['tanggal'])."###".
		number_format($res[0]['sawal'],2)."###".
		$res[0]['keterangan']."###".
		tanggalnormal($res[0]['tanggalcuci']);
	break;
	
	case'saveht':
	
		if($param['kodeorg']==''){
			exit("Warning:Unit masih kosong");
		}
		if($param['kodetangki']==''){
			exit("Warning:Tangk masih kosong");
		}
		if($param['kodebarang']==''){
			exit("Warning:Komoditi/Barang masih kosong");
		}
		if($param['tanggal']==''){
			exit("Warning:Tanggal masih kosong");
		}
		if($param['sawal']==''){
			exit("Warning:Saldo Awal masih kosong");
		}
		if($param['keterangan']==''){
			exit("Warning:Keterangan tidak boleh kosong");
		}
		
		$param['sawal']=str_replace(',', '',$param['sawal']);
		
		if($param['notransaksi']==''){
			
			$kodeorg=$param['kodeorg'];
			$tanggal=tanggalsystemn($param['tanggal']);
			
			$param['notransaksi'] = generatepengajuancucitangki();		
			$str = "insert into ".$dbname.".".$table." (notransaksi,kodeorg,kodetangki,kodebarang,tanggal,sawal,keterangan,createby,createtime,tanggalcuci) 
				values ('".$param['notransaksi']."','".$param['kodeorg']."','".$param['kodetangki']."','".$param['kodebarang']."','".tanggalsystemn($param['tanggal'])."','".$param['sawal']."','".$param['keterangan']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".tanggalsystemn($param['tanggalcuci'])."')";
				// exit("Error".$str);
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		} else {
			$str = "update ".$dbname.".".$table." set 
				kodetangki='".$param['kodetangki']."',
				kodebarang='".$param['kodebarang']."',
				tanggal='".tanggalsystemn($param['tanggal'])."',
				sawal='".$param['sawal']."',
				keterangan='".$param['keterangan']."',
				updateby='".$_SESSION['standard']['userid']."',tanggalcuci='".tanggalsystemn($param['tanggalcuci'])."'	
				where notransaksi = '".$param['notransaksi']."'";
				 //exit("Error".$str);
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}	
		}
		
		echo $param['notransaksi'];
		
	break;
	
	
	
	case'gettangki':
	// exit("Error".$param['kodetangki']);
        $str="select kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$param['kodeorg']."' and komoditi in ('CPO','KER') ";
		// exit("Error:A".$str);
		$res=fetchdata($str);
		foreach($res as $bar){
			$select='';
			if($bar['kodetangki']==$param['kodetangki']){
				$select='selected';
			}
            $opttangki.="<option ".$select." value=".$bar['kodetangki'].">".$bar['keterangan']."</option>";
        }
        echo $opttangki;
    break;
	
	case'getbarang':
        $arrkodebarang=array('CPO'=>'40000001','KER'=>'40000002');
        $arrnamabarang=array('CPO'=>'CRUDE PALM OIL (CPO)','KER'=>'PALM KERNEL (PK)');
		// exit("Error:".$arrkodebarang[$param['kodebarang']]._.$param['kodebarang']);
        $str="select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$param['kodeorg']."' and kodetangki='".$param['kodetangki']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$select='';
			if($arrkodebarang[$bar['komoditi']]==$param['kodebarang']){
				$select='selected';
			}
            $optkodebarang.="<option value=".$arrkodebarang[$bar['komoditi']]." ".$select.">".$arrnamabarang[$bar['komoditi']]."</option>";
        }
        echo $optkodebarang;
    break;
	
	
	case'submitfile':
	
		// $filesize=1;
	
		#= jadikan try commi
		try {
			
			$owlPDO->beginTransaction();
			
			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp=str_replace('-','',str_replace('/','',$param['notransaksi']));

			if ($_FILES['file']['size'] > $filesize){
				throw new PDOException("Ukuran File melebihi ".number_format($filezie/1024)." KB; ukuran file ini ".number_format($_FILES['file']['size']/1024,2)." Kb");
			}

			if($param['fileupload']!=''){
				if($_FILES['file']['error']==0){    
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $param['kriteriaefil']."_".$nmTemp."_".$his."".$filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
						$str = "insert into ".$dbname.".listfileupload values ('','".$param['notransaksi']."','".$filename."','".$filetype."','".$param['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$bar['notransaksi']."','".$bar['namafile']."');\" ></td>";
			$form.= "</tr>";
		}
		echo $form;
    break;  
	
	
	case 'deletefile':
        $namafile=$param['namafile'];
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."'"; //exit('error'.$str);
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