<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');

$notransaksi = checkPostGet('notransaksi', '');
$jabatan = checkPostGet('jabatan', '');
$departmen = checkPostGet('departmen', '');
$unit = checkPostGet('unit', '');
$tglefektif = checkPostGet('tglefektif', '');
$karyawanid = checkPostGet('karyawanid', '');

$atasan = checkPostGet('atasan', '');
$rekan = checkPostGet('rekan', '');
$bawahan = checkPostGet('bawahan', '');

$tujuanjabatan = checkPostGet('tujuanjabatan', '');

$tipetgg = checkPostGet('tipetgg', '');
$tugas = checkPostGet('tugas', '');
$indkin = checkPostGet('indkin', '');
$deadline = checkPostGet('deadline', '');

$wewenang = checkPostGet('wewenang', '');

$tipehubker = checkPostGet('tipehubker', '');
$deskripsihubker = checkPostGet('deskripsihubker', '');
$hubungankerja = checkPostGet('hubungankerja', '');

$pendidikan = checkPostGet('pendidikan', '');
$pengalamankerja = checkPostGet('pengalamankerja', '');
$pelatihan = checkPostGet('pelatihan', '');
$kompetensi = checkPostGet('kompetensi', '');

$myimage = checkPostGet('myimage', '');
$caript = checkPostGet('caript', '');
$caritanggal = checkPostGet('caritanggal', '');

switch($proses)
{
	case'getkaryawanid':
		$tglskrg = date("Y-m-d");
		$optUnit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and (tanggalkeluar>= '".$tglskrg."' or tanggalkeluar = '0000-00-00')  and statuskaryawan != 'Keluar' ";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			if($karyawanid==$val['karyawanid'])
			{
				$optUnit.="<option value='".$val['karyawanid']."' selected>".$val['namakaryawan']."</option>";
			}
			else
			{
				$optUnit.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']."</option>";
			}
		}
		echo $optUnit;
	break;
	
	case'addbawahan':
		$newdata = array(
			'tipe'=>'bawahan',
			'karyawanid'=>$bawahan
		);
		
		if($_SESSION['jobdesc'] != array())
		{
			foreach($_SESSION['jobdesc'] as $key=>$row)
			{
				if($row['karyawanid'] == $bawahan && $row['tipe'] == 'bawahan')
				{
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['jobdesc'],$newdata);
		}else{
			array_push($_SESSION['jobdesc'],$newdata);
		}
	break;
	
	case'loadlistbawahan':
		$tab="";
		$no=0;
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['tipe']=='bawahan')
			{
				$no++;
				$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$row['karyawanid']."'");
				$optLocTgs = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$row['karyawanid']."'");
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$optNmKar[$row['karyawanid']]." [".$optLocTgs[$row['karyawanid']]."]</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deleterekan('".$row['karyawanid']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case'deletebawahan':
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['karyawanid'] == $bawahan && $row['tipe'] == 'bawahan')
			{
				unset($_SESSION['jobdesc'][$key]);
			}
		}
	break;
	
	case'addtujuanjabatan':
		$newdata = array(
			'tipe'=>'tujuanjabatan',
			'deskripsi1'=>$tujuanjabatan
		);
		
		if($_SESSION['jobdesc'] != array())
		{
			foreach($_SESSION['jobdesc'] as $key=>$row)
			{
				if($row['deskripsi1'] == $tujuanjabatan && $row['tipe'] == 'tujuanjabatan')
				{
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['jobdesc'],$newdata);
		}else{
			array_push($_SESSION['jobdesc'],$newdata);
		}
	break;
	
	case'loadlisttujuanjabatan':
		$tab="";
		$no=0;
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['tipe']=='tujuanjabatan')
			{
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$row['deskripsi1']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletetujuanjabatan('".$row['deskripsi1']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case'deletetujuanjabatan':
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['deskripsi1'] == $tujuanjabatan && $row['tipe'] == 'tujuanjabatan')
			{
				unset($_SESSION['jobdesc'][$key]);
			}
		}
	break;
	
	case'addtanggungjawab':
		$newdata = array(
			'tipe'=>'tanggungjawab',
			'tipedt'=>$tipetgg,
			'deskripsi1'=>$tugas,
			'deskripsi2'=>$indkin,
			'deadline'=>$deadline
		);
		
		if($_SESSION['jobdesc'] != array())
		{
			foreach($_SESSION['jobdesc'] as $key=>$row)
			{
				if($row['tipe'] == 'tanggungjawab' && $row['tipedt'] == $tipetgg && $row['deskripsi1'] == $tugas && $row['deskripsi2'] == $indkin && $row['deadline'] == $deadline)
				{
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['jobdesc'],$newdata);
		}else{
			array_push($_SESSION['jobdesc'],$newdata);
		}
	break;
	
	case'loadlisttanggungjawab':
		$arrTgg = array('1'=>$_SESSION['lang']['rutin'],'2'=>$_SESSION['lang']['berkala'],'3'=>$_SESSION['lang']['insidentil']);
		$tab="";
		$no=0;
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['tipe']=='tanggungjawab')
			{
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$arrTgg[$row['tipedt']]."</td>";
				$tab.="<td>".$row['deskripsi1']."</td>";
				$tab.="<td>".$row['deskripsi2']."</td>";
				$tab.="<td>".$row['deadline']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletetanggungjawab('".$row['tipedt']."','".$row['deskripsi1']."','".$row['deskripsi2']."','".$row['deadline']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case'deletetanggungjawab':
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['tipe'] == 'tanggungjawab' && $row['tipedt'] == $tipetgg && $row['deskripsi1'] == $tugas && $row['deskripsi2'] == $indkin && $row['deadline'] == $deadline)
			{
				unset($_SESSION['jobdesc'][$key]);
			}
		}
	break;
	
	case'addwewenang':
		$newdata = array(
			'tipe'=>'wewenang',
			'deskripsi1'=>$wewenang
		);
		
		if($_SESSION['jobdesc'] != array())
		{
			foreach($_SESSION['jobdesc'] as $key=>$row)
			{
				if($row['deskripsi1'] == $wewenang && $row['tipe'] == 'wewenang')
				{
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['jobdesc'],$newdata);
		}else{
			array_push($_SESSION['jobdesc'],$newdata);
		}
	break;
	
	case'loadlistwewenang':
		$tab="";
		$no=0;
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['tipe']=='wewenang')
			{
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$row['deskripsi1']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletewewenang('".$row['deskripsi1']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case'deletewewenang':
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['deskripsi1'] == $wewenang && $row['tipe'] == 'wewenang')
			{
				unset($_SESSION['jobdesc'][$key]);
			}
		}
	break;
	
	case'addhubungankerja':
		$newdata = array(
			'tipe'=>'hubungankerja',
			'tipedt'=>$tipehubker,
			'deskripsi1'=>$deskripsihubker,
			'deskripsi2'=>$hubungankerja
		);
		
		if($_SESSION['jobdesc'] != array())
		{
			foreach($_SESSION['jobdesc'] as $key=>$row)
			{
				if($row['tipe'] == 'hubungankerja' && $row['tipedt'] == $tipehubker && $row['deskripsi1'] == $deskripsihubker && $row['deskripsi2'] == $hubungankerja)
				{
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['jobdesc'],$newdata);
		}else{
			array_push($_SESSION['jobdesc'],$newdata);
		}
	break;
	
	case'loadlisthubungankerja':
		$arrHubKer = array('1'=>$_SESSION['lang']['pihakinternal'],'2'=>$_SESSION['lang']['pihakeksternal']);
		$tab="";
		$no=0;
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['tipe']=='hubungankerja')
			{
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td>".$arrHubKer[$row['tipedt']]."</td>";
				$tab.="<td>".$row['deskripsi1']."</td>";
				$tab.="<td>".$row['deskripsi2']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletehubungankerja('".$row['tipedt']."','".$row['deskripsi1']."','".$row['deskripsi2']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case'deletehubungankerja':
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			if($row['tipe'] == 'hubungankerja' && $row['tipedt'] == $tipehubker && $row['deskripsi1'] == $deskripsihubker && $row['deskripsi2'] == $hubungankerja)
			{
				unset($_SESSION['jobdesc'][$key]);
			}
		}
	break;
	
	case'loadData':
		$where = "";
		## Inisialisasi Search ##
		if($caript!='')
		{
            $where.=" and unit = '".$caript."'";
        }
		if($caritanggal!='')
		{
			$caritanggal = substr($caritanggal,6,4)."-".substr($caritanggal,3,2)."-".substr($caritanggal,0,2);
			$where.=" and tanggalefektif like '".$caritanggal."%'";
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
        
		$str="select count(*) jmlhrow from ".$dbname.".sdm_jobdescription where 1=1 ".$where."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$jlhbrs= $bar['jmlhrow'];	
		}
		
		$tab='';
		$nor=0;
		
		$str="select * from ".$dbname.".sdm_jobdescription where 1=1 ".$where." order by tanggalefektif desc limit ".$offset.",".$limit." ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$nor+=1;
			
			$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['unit']."'");
			$optJabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['jabatan']."'");
			$optDepartemen = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar['departemen']."'");
			$optKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			
			$tab.="<tr class=rowcontent>
				<td id='nor_".$nor."' align=center value='".$nor."'>".$bar['notransaksi']."</td>
				<td style='text-align:center'>".tanggalnormal($bar['tanggalefektif'])."</td>
				<td>".$optUnit[$bar['unit']]." - ".$bar['unit']."</td>
				<td>".$optJabatan[$bar['jabatan']]."</td>
				<td>".$optDepartemen[$bar['departemen']]."</td>
				<td>".$optKaryawan[$bar['updateby']]."</td>";
			
			if($bar['status']=='0')
			{
				$tab.="<td style='text-align:center'>Created</td>";
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_edit.png class=zImgBtn title='edit' onclick=\"editjobdesc('".$bar['notransaksi']."','".$bar['jabatan']."','".$bar['departemen']."','".$bar['unit']."','".tanggalnormal($bar['tanggalefektif'])."','".$bar['karyawanid']."','".$bar['atasan']."','".$bar['rekan']."','".$bar['pendidikan']."','".$bar['pengalamankerja']."','".$bar['pelatihan']."','".$bar['kompetensi']."');\">
				</td>";
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_delete.png class=zImgBtn title='delete' onclick=\"deletejobdesc('".$bar['notransaksi']."');\">
				</td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/09.png class=zImgBtn title='Submitted' onclick=\"postingjobdesc('".$bar['notransaksi']."');\">
				</td>";
			}
			else if($bar['status']=='9')
			{
				$tab.="<td style='text-align:center'>Submitted</td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/04.png class=zImgOffBtn title='Submitted'>
				</td>";
			}
			else if($bar['status']=='3')
			{
				$tab.="<td style='text-align:center'>Rejected</td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/01.png class=zImgOffBtn title='Rejected'>
				</td>";
			}
			else
			{
				$tab.="<td style='text-align:center'>Approved</td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'></td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/02.png class=zImgOffBtn title='Approved'>
				</td>";
			}
			$tab.="<td style='text-align:center'>
				<img src=images/pdf.jpg class=zImgBtn title='print' onclick=\"printpdf('".$bar['notransaksi']."',event);\">
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
	
	case'insert':
		$msgerr = "";
		
		## Cek Validasi ##
		if($jabatan==''||$departmen==''||$unit==''||$karyawanid=='')
		{
			$msgerr.= "1. Form ".$_SESSION['lang']['identitasjabatan']." ".$_SESSION['lang']['belumlengkap'].".\n";
			if($jabatan==''){$msgerr.="   - ".$_SESSION['lang']['functionname']."\n";}
			if($departmen==''){$msgerr.="   - ".$_SESSION['lang']['departemen']."\n";}
			if($unit==''){$msgerr.="   - ".$_SESSION['lang']['unit']."\n";}
			if($karyawanid==''){$msgerr.="   - ".$_SESSION['lang']['incumbent']."\n";}
		}
		
		$nobawahan = $notujuanjabatan = $notanggungjawab = $nowewenang = $nohubungankerja = 0;
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			// if($row['tipe']=='rekan'){$norekan++;}
			if($row['tipe']=='bawahan'){$nobawahan++;}
			if($row['tipe']=='tujuanjabatan'){$notujuanjabatan++;}
			if($row['tipe']=='tanggungjawab'){$notanggungjawab++;}
			if($row['tipe']=='wewenang'){$nowewenang++;}
			if($row['tipe']=='hubungankerja'){$nohubungankerja++;}
		}
		
		if($atasan==''||$rekan==''||$nobawahan<=0)
		{
			$msgerr.= "2. Form ".$_SESSION['lang']['hubunganpelaporankerja']." ".$_SESSION['lang']['belumlengkap'].".\n";
			if($atasan==''){$msgerr.="   - ".$_SESSION['lang']['atasanlangsung']."\n";}
			if($rekan==''){$msgerr.="   - ".$_SESSION['lang']['rekansederajat']."\n";}
			if($nobawahan<=0){$msgerr.="   - ".$_SESSION['lang']['bawahanlangsung']."\n";}
		}
		
		if($notujuanjabatan<=0)
		{
			$msgerr.= "3. Form ".$_SESSION['lang']['tujuanjabatan']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($notanggungjawab<=0)
		{
			$msgerr.= "4. Form ".$_SESSION['lang']['tanggungjawab']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($nowewenang<=0)
		{
			$msgerr.= "5. Form ".$_SESSION['lang']['wewenang']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($nowewenang<=0)
		{
			$msgerr.= "6. Form ".$_SESSION['lang']['hubungankerja']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($pendidikan==''||$pengalamankerja==''||$pelatihan==''||$kompetensi=='')
		{
			$msgerr.= "7. Form ".$_SESSION['lang']['persyaratanjabatan']." ".$_SESSION['lang']['belumlengkap'].".\n";
			if($pendidikan==''){$msgerr.="   - ".$_SESSION['lang']['pendidikan']."\n";}
			if($pengalamankerja==''){$msgerr.="   - ".$_SESSION['lang']['pengalamankerja']."\n";}
			if($pelatihan==''){$msgerr.="   - ".$_SESSION['lang']['pelatihan']."\n";}
			if($kompetensi==''){$msgerr.="   - ".$_SESSION['lang']['kompetensi']."\n";}
		}
		
		if($msgerr!='')
		{
			exit("Warning : \n".$msgerr);
		}		
		
		$tglCtr = explode('-',$tglefektif);
		$tahunCtr = $tglCtr[2];
		$bulanCtr = $tglCtr[1];
		$str="select notransaksi from ".$dbname.".sdm_jobdescription where notransaksi like '%".$tahunCtr."%' order by notransaksi desc limit 1";
		$res=fetchData($str);
		$notrx = $res[0]['notransaksi'];
		if($notrx=='')
		{
			$nourut = '001';
		}
		else
		{
			$explnotrx = explode('/',$notrx);
			$nourut = addZero(($explnotrx[0]+1),3);
		}
		$notransaksi=$nourut."/JD-".$unit."/".romawi($bulanCtr)."/".$tahunCtr;
		
		$str = "insert into ".$dbname.".sdm_jobdescription (notransaksi,jabatan,departemen,unit,tanggalefektif,karyawanid,atasan,rekan	,pendidikan,pengalamankerja,pelatihan,kompetensi,createdby,createdtime,updateby,updatetime) values 
		('".$notransaksi."','".$jabatan."','".$departmen."','".$unit."','".tanggalsystem($tglefektif)."','".$karyawanid."','".$atasan."','".$rekan."','".$pendidikan."','".$pengalamankerja."','".$pelatihan."','".$kompetensi."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
		try
		{
			$owlPDO->exec($str); 
			
			foreach($_SESSION['jobdesc'] as $key=>$row)
			{
				$str="insert into ".$dbname.".sdm_jobdescriptiondt (notransaksi,subdesciption,tipe,karyawanid,deskripsi1,deskripsi2,deadline) 
				values 
				('".$notransaksi."','".$row['tipe']."','".$row['tipedt']."','".$row['karyawanid']."','".$row['deskripsi1']."','".$row['deskripsi2']."','".tanggalsystem($row['deadline'])."')";
				try
				{
					$owlPDO->exec($str);
				}
				catch(PDOException $e)
				{
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'update':
		$msgerr = "";
		
		## Cek Validasi ##
		if($jabatan==''||$departmen==''||$unit==''||$karyawanid=='')
		{
			$msgerr.= "1. Form ".$_SESSION['lang']['identitasjabatan']." ".$_SESSION['lang']['belumlengkap'].".\n";
			if($jabatan==''){$msgerr.="   - ".$_SESSION['lang']['functionname']."\n";}
			if($departmen==''){$msgerr.="   - ".$_SESSION['lang']['departemen']."\n";}
			if($unit==''){$msgerr.="   - ".$_SESSION['lang']['unit']."\n";}
			if($karyawanid==''){$msgerr.="   - ".$_SESSION['lang']['incumbent']."\n";}
		}
		
		$nobawahan = $notujuanjabatan = $notanggungjawab = $nowewenang = $nohubungankerja = 0;
		foreach($_SESSION['jobdesc'] as $key=>$row)
		{
			// if($row['tipe']=='rekan'){$norekan++;}
			if($row['tipe']=='bawahan'){$nobawahan++;}
			if($row['tipe']=='tujuanjabatan'){$notujuanjabatan++;}
			if($row['tipe']=='tanggungjawab'){$notanggungjawab++;}
			if($row['tipe']=='wewenang'){$nowewenang++;}
			if($row['tipe']=='hubungankerja'){$nohubungankerja++;}
		}
		
		if($atasan==''||$rekan==''||$nobawahan<=0)
		{
			$msgerr.= "2. Form ".$_SESSION['lang']['hubunganpelaporankerja']." ".$_SESSION['lang']['belumlengkap'].".\n";
			if($atasan==''){$msgerr.="   - ".$_SESSION['lang']['atasanlangsung']."\n";}
			if($rekan==''){$msgerr.="   - ".$_SESSION['lang']['rekansederajat']."\n";}
			if($nobawahan<=0){$msgerr.="   - ".$_SESSION['lang']['bawahanlangsung']."\n";}
		}
		
		if($notujuanjabatan<=0)
		{
			$msgerr.= "3. Form ".$_SESSION['lang']['tujuanjabatan']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($notanggungjawab<=0)
		{
			$msgerr.= "4. Form ".$_SESSION['lang']['tanggungjawab']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($nowewenang<=0)
		{
			$msgerr.= "5. Form ".$_SESSION['lang']['wewenang']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($nowewenang<=0)
		{
			$msgerr.= "6. Form ".$_SESSION['lang']['hubungankerja']." ".$_SESSION['lang']['belumlengkap'].".\n";
		}
		
		if($pendidikan==''||$pengalamankerja==''||$pelatihan==''||$kompetensi=='')
		{
			$msgerr.= "7. Form ".$_SESSION['lang']['persyaratanjabatan']." ".$_SESSION['lang']['belumlengkap'].".\n";
			if($pendidikan==''){$msgerr.="   - ".$_SESSION['lang']['pendidikan']."\n";}
			if($pengalamankerja==''){$msgerr.="   - ".$_SESSION['lang']['pengalamankerja']."\n";}
			if($pelatihan==''){$msgerr.="   - ".$_SESSION['lang']['pelatihan']."\n";}
			if($kompetensi==''){$msgerr.="   - ".$_SESSION['lang']['kompetensi']."\n";}
		}
		
		if($msgerr!='')
		{
			exit("Warning : \n".$msgerr);
		}	

		$str="delete from ".$dbname.".sdm_jobdescription where notransaksi='".$notransaksi."'";
		try
		{
			$owlPDO->exec($str);
			
			$str = "insert into ".$dbname.".sdm_jobdescription (notransaksi,jabatan,departemen,unit,tanggalefektif,karyawanid,atasan,rekan	,pendidikan,pengalamankerja,pelatihan,kompetensi,createdby,createdtime,updateby,updatetime) values 
			('".$notransaksi."','".$jabatan."','".$departmen."','".$unit."','".tanggalsystem($tglefektif)."','".$karyawanid."','".$atasan."','".$rekan."','".$pendidikan."','".$pengalamankerja."','".$pelatihan."','".$kompetensi."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			try
			{
				$owlPDO->exec($str); 
				
				foreach($_SESSION['jobdesc'] as $key=>$row)
				{
					$str="insert into ".$dbname.".sdm_jobdescriptiondt (notransaksi,subdesciption,tipe,karyawanid,deskripsi1,deskripsi2,deadline) 
					values 
					('".$notransaksi."','".$row['tipe']."','".$row['tipedt']."','".$row['karyawanid']."','".$row['deskripsi1']."','".$row['deskripsi2']."','".tanggalsystem($row['deadline'])."')";
					try
					{
						$owlPDO->exec($str);
					}
					catch(PDOException $e)
					{
						echo " Gagal," . addslashes($e->getMessage());
					}
				}
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
	
	case'deletejobdesc':
		$str="delete from ".$dbname.".sdm_jobdescription where notransaksi='".$notransaksi."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'editjobdesc':
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$newdata = array(
				'tipe'=>$val['subdesciption'],
				'tipedt'=>$val['tipe'],
				'karyawanid'=>$val['karyawanid'],
				'deskripsi1'=>$val['deskripsi1'],
				'deskripsi2'=>$val['deskripsi2'],
				'deadline'=>$val['deadline']
			);
			array_push($_SESSION['jobdesc'],$newdata);
		}
	break;
	
	case'clearData':
		$_SESSION['jobdesc'] = array();
	break;
	
	case'postingjobdesc':
		$str="update ".$dbname.".sdm_jobdescription set status='1' where notransaksi='".$notransaksi."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'printpdf2':
		$str="select atasan,rekan from ".$dbname.".sdm_jobdescription where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$optAt = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['atasan']."'");
		$optRe = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['rekan']."'");
		
		$atasan = $optAt[$res[0]['atasan']];
		$rekan = $optRe[$res[0]['rekan']];
		
		$tab="<link rel='stylesheet' type='text/css' href='style/fm.css'>";
		$tab.="<div class='tree' width=100%>
			<ul>
				<li>
					<a href='#'>".$atasan."</a>
					<ul>
						<li>
							<a href='#'>".$rekan."</a>
							<ul>";
							$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='bawahan'";
							$res=fetchData($str);
							foreach($res as $key=>$val)
							{
								$optBa = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
								$tab.="<li>
									<a href='#'>".$optBa[$val['karyawanid']]."</a>
								</li>";
							}
							$tab.="</ul>
						</li>
					</ul>
				</li>
			</ul>
		</div>";
		echo $tab;
	break;
	
	case'printpdf3':
		// echo $myimage;
		// $data = $myimage;
		// list($type, $data) = explode(';', $data);
		// list(, $data)      = explode(',', $data);
		// $data = base64_decode($data);
		// file_put_contents("images/qrcode/xxx.png", $data);
		
		$path = "images/qrcode/xxx.jpg";		
		delete_directory($path);
		
		$file =preg_replace('#^data:image/\w+;base64,#i', '', $myimage);
		$file =str_replace(' ', '+', $file);
		$stream = base64_decode($file);
		$filename= $path;
		file_put_contents($filename, $stream);
	break;
	
	case'printpdf':
		$str="select * from ".$dbname.".sdm_jobdescription where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		
		$optJab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$res[0]['jabatan']."'");
		$optDep = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$res[0]['departemen']."'");
		$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res[0]['unit']."'");
		$optKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['karyawanid']."'");
		$optPen = makeOption($dbname,'sdm_5pendidikan','idpendidikan,pendidikan',"idpendidikan='".$res[0]['pendidikan']."'");
		$optAt = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['atasan']."'");
		$optRe = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['rekan']."'");
		
		$jabatan = $optJab[$res[0]['jabatan']];
		$departemen = $optDep[$res[0]['departemen']];
		$unit = $optOrg[$res[0]['unit']];
		$tanggalefektif = $res[0]['tanggalefektif'];
		$karyawanid = $optKar[$res[0]['karyawanid']];
		
		$atasan = $optAt[$res[0]['atasan']];
		$rekan = $optRe[$res[0]['rekan']];
		
		$pendidikan = $optPen[$res[0]['pendidikan']];
		$pengalamankerja = $res[0]['pengalamankerja'];
		$pelatihan = $res[0]['pelatihan'];
		$kompetensi = $res[0]['kompetensi'];
		
		class PDF extends FPDF{}
		
		$pdf=new PDF('P','mm','A4');
		$pdf->AddPage();
		
		
		$arrHead = setheadreport($unit);
		$path=$arrHead['logo'];
		$pdf->Image($path,20,7,0,22);
		
		
		$pdf->Ln();
		$pdf->SetY(35);
		$pdf->SetFont('Arial','B',12);
		$pdf->SetFillColor(0,37,124);
		$pdf->Cell(190,5,'','LRT',1,'C',1);
		$pdf->Cell(190,5,strtoupper("deskripsi jabatan"),'LR',1,'C',1);
		$pdf->Cell(190,5,strtoupper("(job description)"),'LR',1,'C',1);
		$pdf->Cell(190,5,'','LRB',1,'C',1);
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("I. identitas jabatan (job identity)"),'TLRB',1,'L',1);
		
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,5,'','LR',1,'L');
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Nama Jabatan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$jabatan,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Departemen',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$departemen,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Unit Usaha / Seksi',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$unit,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Tanggal Efektif',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,tanggalnormal($tanggalefektif),'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pemegang Jabatan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$karyawanid,'R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("II. hubungan pelaporan kerja (reporting relationship)"),'TLRB',1,'L',1);
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='bawahan'";
		$res=fetchData($str);
		$countba = count($res);
		$no=0;
		foreach($res as $key=>$val)
		{
			$no++;
			$optBa = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
			if($no==$countba)
			{
				$bawahan .= $optBa[$val['karyawanid']];
			}
			else
			{
				$bawahan .= $optBa[$val['karyawanid']].", ";
			}
		}
		
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(190,5,'Struktur Organisasi','RL',1,'C');
		
		$getY=$pdf->GetY();
		
		$pdf->Image("images/qrcode/xxx.jpg",40,$getY,0,70);
		
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(190,60,'','LR',1,'C');
		// $pdf->Cell(50,10,$atasan,1,0,'C');
		// $pdf->Cell(70,10,'','R',1,'C');
		
		// $pdf->Cell(95,5,'','LR',0,'C');
		// $pdf->Cell(95,5,'','R',1,'C');
		
		// $pdf->Cell(70,10,'','L',0,'C');
		// $pdf->Cell(50,10,$rekan,1,0,'C');
		// $pdf->Cell(70,10,'','R',1,'C');
		
		// $pdf->Cell(95,5,'','LR',0,'C');
		// $pdf->Cell(95,5,'','R',1,'C');
		
		// $pdf->Cell(5,5,'','L',0,'C');
		// $pdf->Cell(180,5,'','T',0,'C');
		// $pdf->Cell(5,5,'','R',1,'C');
		
		
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(45,5,'Atasan Langsung','L',0,'L');
		$pdf->Cell(5,5,':',0,0,'L');
		$pdf->Cell(140,5,$atasan,'R',1,'L');
		
		$pdf->Cell(45,5,'Rekan Sederajat (Buddy)','L',0,'L');
		$pdf->Cell(5,5,':',0,0,'L');
		$pdf->Cell(140,5,$rekan,'R',1,'L');
		
		$pdf->Cell(45,5,'Bawahan Langsung','L',0,'L');
		$pdf->Cell(5,5,':',0,0,'L');
		$pdf->Cell(140,5,$bawahan,'R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("III. tujuan jabatan (job purpose)"),'TLRB',1,'L',1);
		
		$pdf->SetFont('Arial','',10);
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='tujuanjabatan'";
		$res=fetchData($str);
		$pdf->Cell(190,2,'','LR',1,'L');
		foreach($res as $key=>$val)
		{
			$pdf->Cell(10,5,'','L',0,'L');
			$pdf->Cell(5,5,chr(127),0,0,'L');
			$pdf->Cell(175,5,$val['deskripsi1'],'R',1,'L');
		}
		
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("IV. tanggung jawab (key responsibilities)"),'TLRB',1,'L',1);
		$pdf->SetFont('Arial','BU',10);
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		$pdf->Cell(80,5,strtoupper("tugas"),'LR',0,'C');
		$pdf->Cell(80,5,strtoupper("indikator kinerja"),'R',0,'C');		
		$pdf->Cell(30,5,strtoupper("batas waktu"),'R',1,'C');		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='tanggungjawab' and tipe='1'";
		$res=fetchData($str);
		if(count($res>0))
		{			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(80,5,strtoupper("rutin :"),'LR',0,'L');
			$pdf->Cell(80,5,'','R',0,'C');
			$pdf->Cell(30,5,'','R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(75,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(75,5,$val['deskripsi2'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(25,5,tanggalnormal($val['deadline']),'R',1,'L');
			}
		}
		
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='tanggungjawab' and tipe='2'";
		$res=fetchData($str);
		if(count($res>0))
		{			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(80,5,strtoupper("berkala :"),'LR',0,'L');
			$pdf->Cell(80,5,'','R',0,'C');
			$pdf->Cell(30,5,'','R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(75,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(75,5,$val['deskripsi2'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(25,5,tanggalnormal($val['deadline']),'R',1,'L');
			}
		}
		
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='tanggungjawab' and tipe='3'";
		$res=fetchData($str);
		if(count($res>0))
		{			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(80,5,strtoupper("insidentil :"),'LR',0,'L');
			$pdf->Cell(80,5,'','R',0,'C');
			$pdf->Cell(30,5,'','R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(75,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(75,5,$val['deskripsi2'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(25,5,tanggalnormal($val['deadline']),'R',1,'L');
			}
		}
		
		$pdf->Cell(80,3,'','LR',0,'L');
		$pdf->Cell(80,3,'','R',0,'L');
		$pdf->Cell(30,3,'','R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("V. wewenang (authority)"),'TLRB',1,'L',1);
		$pdf->SetFont('Arial','',10);
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='wewenang'";
		$res=fetchData($str);
		$pdf->Cell(190,2,'','LR',1,'L');
		foreach($res as $key=>$val)
		{
			$pdf->Cell(10,5,'','L',0,'L');
			$pdf->Cell(5,5,chr(127),0,0,'L');
			$pdf->Cell(175,5,$val['deskripsi1'],'R',1,'L');
		}
		
		$pdf->SetFont('Arial','',12);
		$pdf->Cell(190,5,'','LRB',1,'C');
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("VI. hubungan kerja (work relations)"),'TLRB',1,'L',1);
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='hubungankerja' and tipe='1'";
		$res=fetchData($str);
		if(count($res>0))
		{
			$pdf->SetFont('Arial','BU',10);
			$pdf->Cell(95,3,'','LR',0,'L');
			$pdf->Cell(95,3,'','R',1,'L');
			$pdf->Cell(95,5,strtoupper("pihak internal"),'LR',0,'C');
			$pdf->Cell(95,5,strtoupper("kegiatan"),'R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(90,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(90,5,$val['deskripsi2'],'R',1,'L');
			}
		}
		
		$str="select * from ".$dbname.".sdm_jobdescriptiondt where notransaksi='".$notransaksi."' and subdesciption='hubungankerja' and tipe='2'";
		$res=fetchData($str);
		if(count($res>0))
		{
			$pdf->SetFont('Arial','BU',10);
			$pdf->Cell(95,3,'','LR',0,'L');
			$pdf->Cell(95,3,'','R',1,'L');
			$pdf->Cell(95,5,strtoupper("pihak eksternal"),'LR',0,'C');
			$pdf->Cell(95,5,strtoupper("kegiatan"),'R',1,'C');
			
			$pdf->SetFont('Arial','',10);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				$pdf->Cell(5,5,$no.".",'L',0,'L');
				$pdf->Cell(90,5,$val['deskripsi1'],'R',0,'L');
				$pdf->Cell(5,5,$no.".",'',0,'L');
				$pdf->Cell(90,5,$val['deskripsi2'],'R',1,'L');
			}
		}
		
		$pdf->Cell(95,3,'','LR',0,'L');
		$pdf->Cell(95,3,'','R',1,'L');
		
		$pdf->SetFont('Arial','',12);
		$pdf->SetFillColor(176,176,176);
		$pdf->Cell(190,8,"    ".strtoupper("VII. persyaratan jabatan (job qualifications)"),'TLRB',1,'L',1);
		
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(190,5,'','LR',1,'L');
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pendidikan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$pendidikan,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pengalaman Kerja',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$pengalamankerja,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Pelatihan',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$pelatihan,'R',1,'L');
		
		$pdf->Cell(10,7,'','L',0,'L');
		$pdf->Cell(40,7,'Kompetensi',0,0,'L');
		$pdf->Cell(5,7,':',0,0,'L');
		$pdf->Cell(135,7,$kompetensi,'R',1,'L');
		
		$pdf->Cell(190,5,'','BLR',1,'L');
		
		##======================##
		
		$pdf->SetLineWidth(1);
		$pdf->Line(205,5,5,5);
		$pdf->Line(5,293,5,5);
		$pdf->Line(205,5,205,293);
		$pdf->Line(205,293,5,293);
		
		$pdf->Output();
	break;
}

function delete_directory($dirname) 
{
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	
	if (!$dir_handle)
		return false;
	
	while($file = readdir($dir_handle)) 
	{
		if ($file != "." && $file != "..") 
		{
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delete_directory($dirname.'/'.$file);
	       }
	 }
	 closedir($dir_handle);
	 rmdir($dirname);
	 return true;
}