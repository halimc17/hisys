<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$mandor=checkPostGet('mandor','');
$karyawan=checkPostGet('karyawan','');
$urut=checkPostGet('urut','');
$aktif=checkPostGet('aktif','');
$status=checkPostGet('status','');
$divisi=checkPostGet('divisi','');

switch($method){
	case'lihatdatakary':
		if($divisi==''){
			exit("error : divisi harus diisi.");
		}
		$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg ='".substr($divisi,0,4)."' and tipe in ('BKM','PNN') and kolom='mandor'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			if($bar['kolom']=='mandor'){
				if($no==1){
					$mdr=$bar['jabatan'];					
				}else{
					$mdr=$mdr.",".$bar['jabatan'];
				}
			}
		}
		$d=$n="";
		if($mdr!=''){
			$whr=" and t1.kodejabatan in (".$mdr.")";
		}else{
			$whr=" and t2.namajabatan like '%mandor%' and t2.namajabatan not like '%mandor%1%'";
		}
		$optmandor='<option value=\'\'>'.$_SESSION['lang']['pilihdata'].'</option>';
		$str="select t1.karyawanid, t1.nik, t1.namakaryawan, t2.namajabatan from ".$dbname.".datakaryawan t1
			left join ".$dbname.".sdm_5jabatan t2 on t1.kodejabatan=t2.kodejabatan where 1=1 ".$whr."
			and t1.lokasitugas like '".substr($divisi,0,4)."%' and t1.subbagian like '".$divisi."%' 
			and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") 
			and t1.alokasi = 0 order by t1.namakaryawan";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("error : Data mandor tidak ada.");
		}
		$mdr=array();
		foreach($res as $bar){
			$optmandor.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." [".$bar['nik']."] [".$bar['namajabatan']."]</option>";
			$mdr[$bar['karyawanid']]=$bar['karyawanid'];
		}
		
		$tab="
			<table class=sortable cellspacing=1 cellpadding=5 border=0><thead>
			<tr class=rowheader align=center>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['nik2']."</td>
				<td>".$_SESSION['lang']['karyawan']."</td>
				<td>".$_SESSION['lang']['tipekaryawan']."</td>
				<td>".$_SESSION['lang']['jabatan']."</td>
				<td>".$_SESSION['lang']['divisi']."</td>
				<td width=50px>No Urut</td>
				<td colspan=2>".$_SESSION['lang']['mandor']."</td>
				<td colspan=1>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead><tbody>";
		
		$str="select * from ".$dbname.".datakaryawan t1 where t1.subbagian ='".$divisi."' and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") and t1.alokasi = 0 and tipekaryawan in ('3','4','6') and t1.karyawanid not in ('".implode("','",$mdr)."') order by kodejabatan, t1.namakaryawan";
		$res = fetchdata($str);$no=0;
		$strx = "";
		$no=0;
		$nmtipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
		foreach($res as $bar){
			$no++;
			$strx = "select * from ".$dbname.".kebun_5mandor where karyawanid = '".$bar['karyawanid']."'";
			$resx = fetchdata($strx);
			if(count($resx)>0){
				foreach($resx as $barx){					
					$nourut=$barx['nourut'];
					$optmd="";
					$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$barx['mandorid']."'");
					$optmd.="<option value='".$barx['mandorid']."' selected>".$nmkar[$barx['mandorid']]."</option>";
				}					
				$optmandorx=$optmandor;
				$optmandorx.=$optmd;
			}else{
				$nourut=$no;
				$optmandorx=$optmandor;
			}
			
			$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan',"kodejabatan='".$bar['kodejabatan']."'");
			$tab.="<tr class=rowcontent id=row".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left hidden id=kary".$no.">".$bar['karyawanid']."</td>";
			$tab.="<td align=left>".$bar['nik']."</td>";
			$tab.="<td align=left>".$bar['namakaryawan']."</td>";
			$tab.="<td align=left>".$nmtipe[$bar['tipekaryawan']]."</td>";
			$tab.="<td align=left>".$nmjab[$bar['kodejabatan']]."</td>";
			$tab.="<td align=left>".$bar['subbagian']."</td>";
			$tab.="<td align=center><input type=text class='myinputtextnumber' onkeypress=\"return angka_doang(event);\" id=urut".$no." size=4 maxlength='3' value='".$nourut."'></td>";
			$tab.="<td align=center><select class=select2s id=mandor".$no." onchange=copymandor(".$no.") style='width:200px'>".$optmandorx."</select></td>
			<td align=center width=25px><img id='mandor".$no."' onclick=z.elSearch('mandor".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png'></td>";
			$tab.="<td align=center><img title='Simpan' class='zImgBtn' onclick=\"savedetail('".$no."')\" src=images/save.png></td>";
        }
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=right colspan=10><input hidden id=ttlrow value=".$no."><button class=mybutton onclick=\"savealldetail('".$no."')\">SaveAll</button></td>";
		
		echo $tab;
	break;
	case'getnourut':
		$str="select max(nourut) as nourut from ".$dbname.".kebun_5mandor  where mandorid= ".$mandor."";
		$res = fetchdata($str);
		
		echo ($res[0]['nourut']+1);
	
	break;
    case'tampilmandor': // nampilin data mandor yang punya karyawan

		
    // if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	// 	$where = "";
	// } else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	// 	$where = " and b.kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."'";
	// } else {
	// 	$where = " and b.lokasitugas = '".$_SESSION['empl']['lokasitugas']."'";
	// }

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}
	
	$str="select distinct(a.mandorid), b.namakaryawan,b.* from ".$dbname.".kebun_5mandor a left join ".$dbname.".datakaryawan b on a.mandorid = b.karyawanid where 1=1 and b.lokasitugas in (".$dataunitx.")  order by b.lokasitugas, b.subbagian, b.namakaryawan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $no+=1;	
        echo"<tr class=rowcontent>
        <td align=center>".$no."</td>
        <td align=left>".$bar['nik']."</td>
        <td align=left>".$bar['namakaryawan']."</td>
        <td align=left>".getNamaOrg($bar['lokasitugas'])."</td>
        <td align=left>".getNamaOrg($bar['subbagian'])."</td>
		<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"pilihmandor('".$bar['mandorid']."');\"></td>
        <td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"hapusmandor('".$bar['mandorid']."');\"></td>
        </tr>";	
    }     
    break;
    
    case'tampilkaryawan': // nampilin pilihan karyawan setelah pilih mandor

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='CAR' or getindukPT($_SESSION['empl']['lokasitugas'])=='LAN'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='CAR' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='LAN' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='DMA' or getindukPT($_SESSION['empl']['lokasitugas'])=='MHA'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='DMA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}

			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='MHA' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}

		if(getindukPT($_SESSION['empl']['lokasitugas'])=='PPP'){
			$dataunitx='';
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='PPP' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($dataunitx==""){
					$dataunitx.="'".$val['kodeorganisasi']."'";				
				}else{
					$dataunitx.=",'".$val['kodeorganisasi']."'";				
				}
			}
		}


        $optkaryawan='<option value=\'\'>'.$_SESSION['lang']['pilihdata'].'</option>';
        $str="select t1.karyawanid, t1.nik, t1.namakaryawan, t1.subbagian from ".$dbname.".datakaryawan t1
            where t1.lokasitugas IN (".$dataunitx.") and t1.subbagian!='' and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") and t1.alokasi = 0
                and t1.karyawanid != '".$mandor."' and not exists (select t2.karyawanid from ".$dbname.".kebun_5mandor t2 where t1.karyawanid=t2.karyawanid) and not exists (select t2.mandorid  from ".$dbname.".kebun_5mandor t2 where t1.karyawanid=t2.mandorid)
            order by t1.namakaryawan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $optkaryawan.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [".$bar->nik."] ".$bar->subbagian."</option>";
        }
        echo $optkaryawan;
    break;
	
	case'syntampilmandor': 
        $optmandor='<option value=\'\'>'.$_SESSION['lang']['pilihdata'].'</option>';
        $str="select t1.karyawanid, t1.namakaryawan from ".$dbname.".datakaryawan t1
			where t1.lokasitugas like '".$_SESSION['empl']['lokasitugas']."%' and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") and t1.alokasi = 0 and not exists (select t2.karyawanid from ".$dbname.".kebun_5mandor t2 where t1.karyawanid=t2.karyawanid)
			order by t1.namakaryawan";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
        {
			if($bar->karyawanid==$mandor){
				$optmandor.="<option value='".$bar->karyawanid."' selected>".$bar->namakaryawan." [".$bar->karyawanid."]</option>";
			}else{
				$optmandor.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [".$bar->karyawanid."]</option>";
			}
        }
        echo $optmandor;
    break;
	
	case'pilihmandor': // nampilin data karyawan yang dimandori
    $no=0;
    $str="select a.karyawanid, b.namakaryawan,b.nik,b.lokasitugas,b.subbagian, a.statusaktif, a.mandorid, a.nourut from ".$dbname.".kebun_5mandor a
        left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
        where a.mandorid='".$mandor."'
        order by a.nourut ASC";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrows=owlBaris($res);
    if($numrows<=0){
		echo 'Tidak ada daftar karyawan';
	}else{
		echo"<table class=sortable cellspacing=1 cellpadding=5 width=100% border=0>
			<thead>
			<tr class=rowheader>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['nik2']."</th>
				<th>".$_SESSION['lang']['karyawan']."</th>
				<th>".$_SESSION['lang']['kodeorg']."</th>
				<th>".$_SESSION['lang']['divisi']."</th>
				<th width=50px>".$_SESSION['lang']['urutan']."</th>
				<th width=50px>".$_SESSION['lang']['status']."</th>
				<th colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>";
		$statusaktif['0']='Tidak Aktif';
		$statusaktif['1']='Aktif';
		while($bar=$res->fetch())
		{
			$no+=1;	
			echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=left>".$bar['nik']."</td>
			<td align=left>".$bar['namakaryawan']."</td>
			<td align=left>".$bar['lokasitugas']."</td>
			<td align=left>".$bar['subbagian']."</td>
			<td align=center>".$bar['nourut']."</td>
			<td align=center title='Set Aktif'>".$statusaktif[$bar['statusaktif']]."</td>
			<td align=center width=25px>
			<img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"editkaryawan('".$bar['karyawanid']."','".$bar['namakaryawan']."','".$bar['statusaktif']."','".$bar['nourut']."');\">
			</td>
			<td align=center width=25px>
			<img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"hapuskaryawan('".$bar['karyawanid']."');\">
			</td>
			</tr>";	
		}     
		echo"</table>";
	}
    break;
    case'simpankary':
		$str="delete from ".$dbname.".kebun_5mandor where karyawanid='".$karyawan."'";
		try{$owlPDO->exec($str); }catch (PDOException $e){echo"Gagal : ".$e->getMessage();die();}
			
		if($mandor!=''){			
			$str="insert into ".$dbname.".kebun_5mandor (`mandorid`,`karyawanid`,`statusaktif`,`nourut`,`updateby`) 
			values ('".$mandor."','".$karyawan."','1','".$urut."','".$_SESSION['standard']['userid']."')";
			try{$owlPDO->exec($str); }catch (PDOException $e){echo"Gagal : ".$e->getMessage();die();}
		}
	break;
    case'tambahkaryawan': // tambah karyawan mandor
	$strUr="select * from ".$dbname.".kebun_5mandor
        where karyawanid='".$mandor."'";
	$queryUr=$owlPDO->query($strUr) or die(print " Gagal: ".PDOException::getMessage());
	$numrowsUr=owlBaris($queryUr);
    
	$str="select * from ".$dbname.".kebun_5mandor
        where mandorid='".$mandor."' and nourut='".$urut."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($res);
    if($numrowsUr>0){
		echo"Gagal : Periksa kembali Nama Mandor. Sudah pernah terdaftar didatabase";
	}else if($numrows>0){
		echo"Gagal : Periksa kembali no urut. Sudah pernah terdaftar didatabase";
	}else{
		$sIns="insert into ".$dbname.".kebun_5mandor (`mandorid`,`karyawanid`,`statusaktif`,`nourut`,`updateby`) 
			values ('".$mandor."','".$karyawan."','1','".$urut."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($sIns); 
		}catch (PDOException $e){
			echo"Gagal : ".$e->getMessage();
			die();
		}
	}
    break;
	
	case'editkaryawan': 
	$strUr="select * from ".$dbname.".kebun_5mandor
        where mandorid='".$mandor."' and karyawanid='".$karyawan."'";
	$queryUr=$owlPDO->query($strUr) or die(print " Gagal: ".PDOException::getMessage());
	$queryUr->setFetchMode(PDO::FETCH_ASSOC);
	$restUr=$queryUr->fetch();
	
	$str="select * from ".$dbname.".kebun_5mandor
        where mandorid='".$mandor."' and nourut='".$urut."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($res);
    if($numrows>0 && $urut != $restUr['nourut']){
		echo"Gagal : Periksa kembali no urut. Sudah pernah terdaftar didatabase";
	}else{
		$sIns="update ".$dbname.".kebun_5mandor set statusaktif ='".$status."', nourut='".$urut."' where mandorid='".$mandor."' and karyawanid = '".$karyawan."'";
		try{
			$owlPDO->exec($sIns); 
		}catch (PDOException $e){
			echo"Gagal : ".$e->getMessage();
			die();
		}
	}
    break;

    case'hapuskaryawan': // hapus karyawan mandor        
    $sIns="delete from ".$dbname.".kebun_5mandor where mandorid='".$mandor."' and karyawanid='".$karyawan."'";
	try{
		$owlPDO->exec($sIns); 
	}catch (PDOException $e){
		echo"Gagal : ".$e->getMessage();
		die();
	}
    break;

    case'hapusmandor': // hapus mandor beserta karyawannya
    $sIns="delete from ".$dbname.".kebun_5mandor where mandorid='".$mandor."'";
	try{
		$owlPDO->exec($sIns); 
	}catch (PDOException $e){
		echo"Gagal : ".$e->getMessage();
		die();
	}
    break;
    
    case'aktifkaryawan': // update status aktif karyawan 
    if($aktif=='1')$aktif='0'; else $aktif='1';
    // UPDATE `owlv2`.`kebun_5mandor` SET `statusaktif` = '0' WHERE `kebun_5mandor`.`mandorid` =0000012456 AND `kebun_5mandor`.`karyawanid` =0000013591;    
    $sIns="update ".$dbname.".kebun_5mandor set statusaktif ='".$aktif."' where mandorid='".$mandor."' and karyawanid = '".$karyawan."'";
	try{
		$owlPDO->exec($sIns); 
	}catch (PDOException $e){
		echo"Gagal : ".$e->getMessage();
		die();
	}
    break;
    
	default:
    break;
}
?>