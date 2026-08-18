<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$tgl = checkPostGet('tgl','');
$pabrik = checkPostGet('pabrik','');
$supplier = checkPostGet('supplier','');
$harga = checkPostGet('harga','');
$find_tgl = checkPostGet('find_tgl','');
$find_supplier = checkPostGet('find_supplier','');
$find_notransaksi = checkPostGet('find_notransaksi','');
$jab = getPostingJabatan('belitbs');
$jenisApp = 'HBT';

switch ($method) 
{
	case 'getNotransaksi':
		//bentuk nomor transaksi
		$tgl=tanggalSystemn($tgl);
		$tmpTgl = explode('-',$tgl);
		$notran=$tmpTgl[0].$tmpTgl[1];
		$yymmdd=$tmpTgl[0].$tmpTgl[1].$tmpTgl[2];
        
        $str="select max(right(notransaksi,3)) as nomorurut from ".$dbname.".pmn_hargabelitbs where left(notransaksi,6) = '".$notran."' and kodeorg='".$pabrik."' order by right(notransaksi,3) desc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $notran=$yymmdd."/".$pabrik."/".addZero($noawal,3);
		echo $notran;
	
	break;
	
	case 'getApproval':
		$tab.="";
		if($pabrik=='')
		{
			$countApp = '0';
		}
		else
		{
			$countApp = getCountApproval($jenisApp,$pabrik);

			for($i=1;$i<=$countApp;$i++)
			{
				$optpersetujuan="";
				$arrDetail = detailApprove($i,$notransaksi,$jenisApp);
				$listApp = listApprove($i,$jenisApp,$pabrik);
				foreach($listApp as $key=>$val)
				{
					if($arrDetail['karyawanid']==$val['karyawanid'])
					{
						$optpersetujuan.="<option value='".$val['karyawanid']."' selected>".$val['nama']."</option>";
					}
					else
					{
						$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
					}
				}
				$tab.="<tr>";
				$tab.="<td>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				$tab.="<td>:</td>";
				$tab.="<td>
					<select id='persetujuan".$i."' style=\"width:205px;\">".$optpersetujuan."</select>
					<img id='persetujuan".$i."' onclick=z.elSearch('persetujuan".$i."',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab."####".$countApp;
	
	break;
	
	case 'insert':
		//cek apakah merk sudah ada ??
		if(strlen($notransaksi)<17){
			exit('Error : Nomor Transaksi Salah.');
		}
		
		
		$tgl=tanggalSystemn($tgl);
		$tglSkrg = date("Y-m-d");
		$wktSkrg = date("His");
		#pengecualian jika ada masalah server
		$sApl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='PGTBS'";
		$rApl=fetchData($sApl);
		if($rApl[0]['nilai']==0){
			if($tgl < $tglSkrg)
			{
				exit("Gagal, Tanggal transaksi harus lebih besar atau sama dengan tanggal sekarang (".tanggalnormal($tglSkrg).")");
			}
			
			if($tgl==$tglSkrg)
			{
				if($wktSkrg > '140000')
				{
					exit("Gagal, Batas input harga tbs adalah jam 14:00:00");
				}
			}
		}
		
		
		$str = "select count(*) as jumlah from ".$dbname.".pmn_hargabelitbs 
				where tanggal = '".$tgl."' and kodeorg='".$pabrik."' and supplierid='".$supplier."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$countitem = $bar['jumlah'];
		if($countitem >= 1)
		{
			exit("Warning : Harga TBS sudah pernah terdaftar sebelumnya.");
		}
		else
		{
			$str = "insert into ".$dbname.".pmn_hargabelitbs values ('".$notransaksi."','".$tgl."','".$pabrik."','".$supplier."','".$harga."','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','','','')";
			try
			{
				$owlPDO->exec($str);
				
				$listpersetujuan=$_POST['persetujuan'];
				foreach($listpersetujuan as $key=>$val)
				{
					$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$key."' and kodeunit='".$pabrik."'";
					$res=fetchData($str);
					$tipeapp = $res[0]['tipe'];
					$departemenapp = $res[0]['departemen'];
					$tipekaryawanapp = $res[0]['tipekaryawan'];
					$jabatanapp = $res[0]['jabatan'];
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and level='".$key."'";
					$owlPDO->exec($str);
					
					if($tipeapp=='1'){
						if($departemenapp!=''){
							$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
								$owlPDO->exec($str);
							}
						}
						if($tipekaryawanapp!=''){
							$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
								$owlPDO->exec($str);
							}
						}
						if($jabatanapp!='0'){
							$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
								$owlPDO->exec($str);
							}
						}
					}else{
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$listpersetujuan[$key]."','9')";
						try
						{
							$owlPDO->exec($str);
						}
						catch (PDOException $e) 
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
					}
				}
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
	break;

    case 'update':
		$str="select * from ".$dbname.".pmn_hargabelitbs where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$tgl=$res[0]['tanggal'];
		$tglSkrg = date("Y-m-d");
		$wktSkrg = date("His");
		
		if($tgl < $tglSkrg)
		{
			exit("Gagal, Tanggal transaksi harus lebih besar atau sama dengan tanggal sekarang (".tanggalnormal($tglSkrg).")");
		}
		
		if($tgl==$tglSkrg)
		{
			if($wktSkrg > '140000')
			{
				exit("Gagal, Batas input harga tbs adalah jam 14:00:00");
			}
		}
		
	
		$str = "update ".$dbname.".pmn_hargabelitbs set harga='".$harga."', updateby='".$_SESSION['standard']['userid']."' where notransaksi = '".$notransaksi."'";
		
        try
		{
			$owlPDO->exec($str);
			
			$listpersetujuan=$_POST['persetujuan'];
			foreach($listpersetujuan as $key=>$val)
			{
				$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$key."' and kodeunit='".$pabrik."'";
				$res=fetchData($str);
				$tipeapp = $res[0]['tipe'];
				$departemenapp = $res[0]['departemen'];
				$tipekaryawanapp = $res[0]['tipekaryawan'];
				$jabatanapp = $res[0]['jabatan'];
				
				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenisApp."' and level='".$key."'";
				$owlPDO->exec($str);
				if($tipeapp=='1'){
					if($departemenapp!=''){
						$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
							$owlPDO->exec($str);
						}
					}
					if($tipekaryawanapp!=''){
						$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
							$owlPDO->exec($str);
						}
					}
					if($jabatanapp!='0'){
						$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$valx['karyawanid']."','9')";
							$owlPDO->exec($str);
						}
					}
				}else{
					$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$key."','".$listpersetujuan[$key]."','9')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						echo " Gagal," . addslashes($e->getMessage());
					}
				}
				
				// $str="update ".$dbname.".approval set karyawanid='".$listpersetujuan[$key]."' where notransaksi='".$notransaksi."' and level='".$key."' and jenispersetujuan='".$jenisApp."'";
				// try
				// {
					// $owlPDO->exec($str);
				// }
				// catch (PDOException $e) 
				// {
					// print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
				// }
			}
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'delete':
		$str = "delete from ".$dbname.".pmn_hargabelitbs where notransaksi = '".$notransaksi."'";
		
        try
		{
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."'";
			try
			{
				$owlPDO->exec($str); 
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

	case'posting':
        $str = "update " . $dbname . ".pmn_hargabelitbs set posting='9',postingtime='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";

        try {
            $owlPDO->exec($str);
			
			$str="update ".$dbname.".approval set status='0' where notransaksi='".$notransaksi."'";
			try
			{
				$owlPDO->exec($str); 
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
	case'unposting':
		//cek tutup buku
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$pabrik."' and periode ='".substr($tgl,0,7)."'";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$ttp->fetch();
			$tutup=$bar['tutupbuku'];
		if($tutup==1){
			exit("Error : Unposting tidak bisa dilakukan karena periode akuntansi ".substr($tgl,0,7)." unit ".$pabrik." sudah di tutup.");
		} else {
			$str = "update " . $dbname . ".pmn_hargabelitbs set posting='0',postingtime='" . date('Y-m-d') . "',"
					. "postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
	break;
	
    case'loaddata':
	
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$where="";
		if($find_tgl!=''){ 
			$where.=" and a.tanggal LIKE  '%".$find_tgl."%'";
		}
		if($find_supplier!=''){ 
			$where.=" and UPPER(b.namasupplier) LIKE  '%".strtoupper($find_supplier)."%'";
		}
		if($find_notransaksi!=''){ 
			$where.=" and a.notransaksi LIKE  '%".$find_notransaksi."%'";
		}
		
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_hargabelitbs a
			    left join log_5supplier b on a.supplierid=b.supplierid
				where 0=0 ".$where.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$countApp = getCountApproval($jenisApp,'');
		
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center width=50px>".$_SESSION['lang']['kodeorg']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['supplier']."</td>
				<td align=center>".$_SESSION['lang']['harga']."</td>";
		
		for($i=1;$i<=$countApp;$i++){
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']. "".$i."</td>";
		}
				
		$tab.="<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center colspan=3>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan');
		$str = "select * from ".$dbname.".pmn_hargabelitbs a
				left join log_5supplier b on a.supplierid=b.supplierid
				where 0=0 ".$where." order by a.tanggal desc, a.notransaksi desc LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$no++;
			$tab.="<tr class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['notransaksi']."</td>";
            $tab.="<td>".$bar['kodeorg']."</td>";
            $tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
            $tab.="<td>".$bar['supplierid']." - ".$bar['namasupplier']."</td>";
            $tab.="<td align=right>".number_format($bar['harga'],2)."</td>";
			for($i=1;$i<=$countApp;$i++){
				$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$i."' and kodeunit='".$bar['kodeorg']."'";
				$resx=fetchData($strx);
				$tipeapp = $resx[0]['tipe'];
				$departemenapp = $resx[0]['departemen'];
				$tipekaryawanapp = $resx[0]['tipekaryawan'];
				$jabatanapp = $resx[0]['jabatan'];
				
				$arrDetail = detailApprove($i,$bar['notransaksi'],$jenisApp);
				if($tipeapp=='1' && $arrDetail['status']!=''){
					if($arrDetail['status']!='1'){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}
					}
				}
				$tab.="<td align=left>".$arrDetail['nama']." ".(($arrDetail['status']=='9'||$arrDetail['status']=='')?"":"(".$arrDetail['namastatus'].")")."</td>";
			}
			
			$tab.="<td align=left>".$optNamaKar[$bar['updateby']]."</td>";
            
			if ($bar['posting']=='0') {
				$tgl=$bar['tanggal'];
				$tglSkrg = date("Y-m-d");
				$wktSkrg = date("His");
				#pengecualian jika ada masalah server
				$sApl="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='PGTBS'";
				$rApl=fetchData($sApl);
				if($rApl[0]['nilai']==0){
					if($tgl < $tglSkrg)
					{
						$tab.="<td align=center>Created</td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center><img src=images/icons/04/16/09.png class=resicon class=zImgBtn height='30'  title='Submitted' style='cursor:default'></td>";
					}
					else
					{
						if($tgl==$tglSkrg)
						{
							if($wktSkrg > '140000')
							{
								$tab.="<td align=center>Created</td>";
								$tab.="<td align=center></td>";
								$tab.="<td align=center></td>";
								$tab.="<td align=center><img src=images/icons/04/16/09.png class=resicon class=zImgBtn height='30'  title='Submitted' style='cursor:default'></td>";
							}
							else
							{
								$tab.="<td align=center>Created</td>";
								$tab.="<td align=center>
									<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['tanggal']."','".$bar['supplierid']."','".$bar['harga']."');\"></td>";
								$tab.="<td align=center>
									<img src=images/application/application_delete.png class=resicon  title='Delete' 
										onclick=\"del('" . $bar['notransaksi'] . "');\" ></td>";
								$tab.="<td align=center><img src=images/icons/04/16/09.png class=resicon class=zImgBtn height='30'  title='Submitted' onclick=\"posting('" . $bar['notransaksi'] . "','" . $no . "','".($countApp+10)."');\" ></td>";
							}
						}
						else
						{
							$tab.="<td align=center>Created</td>";
							$tab.="<td align=center>
								<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['tanggal']."','".$bar['supplierid']."','".$bar['harga']."');\"></td>";
							$tab.="<td align=center>
								<img src=images/application/application_delete.png class=resicon  title='Delete' 
									onclick=\"del('" . $bar['notransaksi'] . "');\" ></td>";
							$tab.="<td align=center><img src=images/icons/04/16/09.png class=resicon class=zImgBtn height='30'  title='Submitted' onclick=\"posting('" . $bar['notransaksi'] . "','" . $no . "','".($countApp+10)."');\" ></td>";
						}
					}
				}else{
					$tab.="<td align=center>Created</td>";
					$tab.="<td align=center>
						<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['tanggal']."','".$bar['supplierid']."','".$bar['harga']."');\"></td>";
					$tab.="<td align=center>
						<img src=images/application/application_delete.png class=resicon  title='Delete' 
							onclick=\"del('" . $bar['notransaksi'] . "');\" ></td>";
					$tab.="<td align=center><img src=images/icons/04/16/09.png class=resicon class=zImgBtn height='30'  title='Submitted' onclick=\"posting('" . $bar['notransaksi'] . "','" . $no . "','".($countApp+10)."');\" ></td>";
				}
			}
			else if($bar['posting']=='9'){
				$tab.="<td style='text-align:center'>Submitted</td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/04.png class=zImgOffBtn title='Submitted'>
				</td>";
			}else if($bar['posting']=='3'){
				$tab.="<td style='text-align:center'>Rejected</td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/01.png class=zImgOffBtn title='Rejected'>
				</td>";
			}
			else{
				$tab.="<td style='text-align:center'>Approved</td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/02.png class=zImgOffBtn title='Approved'>
				</td>";				
			}

            $tab.="</tr>";
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

		$tab.="<tr><td colspan=11 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

    default:
}
?>
