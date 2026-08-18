<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg  =checkPostGet('kodeorg','');
$jabatan  =checkPostGet('jabatan','');
$method   =checkPostGet('method','');
$tipetrans=checkPostGet('tipetrans','');
$kolom    =checkPostGet('kolom','');
$id       =checkPostGet('id','');

switch($method){
	case'getkary':
		$tab="<table id=tabledt cellpadding=5 cellspacing=1 ".$border." class=sortable width=100%>
			<thead><tr class=rowheader>
			<td align=center >No</td>
			<td align=center >NIK</td>
			<td align=center >Nama</td>
			<td align=center >Divisi</td>
		</tr></thead><tbody>";
		
		$whereKary=" and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
		$str="select * from ".$dbname.".datakaryawan where lokasitugas ='".$kodeorg."' and kodejabatan='".$jabatan."' ".$whereKary." order by subbagian,namakaryawan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['nik']."</td>";
			$tab.="<td align=left>".$bar['namakaryawan']."</td>";
			$tab.="<td align=center>".$bar['subbagian']."</td>";
		}
		
		$tab.="</tr>";
		$tab.="</table>";
		echo $tab;
	break;
	case'getjabatan':
		$tab="<table id=tabledt cellpadding=5 cellspacing=1 ".$border." class=sortable width=100%>
			<thead><tr class=rowheader>
			<td align=center >No</td>
			<td align=center >Kode<br>Jabatan</td>
			<td align=center >Nama Jabatan</td>
			<td align=center >Jlh</td>
			<td align=center >Action</td>
		</tr></thead><tbody>";
		if($kodeorg=='' or $tipetrans=='' or $kolom==''){
			exit("Kebun, Tipe Transaksi, Kolom tidak boleh kosong.");
		}
		
		$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg ='".$kodeorg."' and tipe ='".$tipetrans."' and kolom='".$kolom."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kdjab=$bar['jabatan'];
		}
		if(count($res)>0){			
			$djab=explode(",",$kdjab);
			foreach($djab as $jab){
				$detjab[$jab]=$jab;
			}
		}
		
		$jlh=array();
		$whereKary=" and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
		
		$str="select count(karyawanid) as jlh, kodejabatan from ".$dbname.".datakaryawan where lokasitugas ='".$kodeorg."' ".$whereKary." group by kodejabatan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$jlh[$bar['kodejabatan']]=$bar['jlh'];
		}
		
		
		$str="select * from ".$dbname.".sdm_5jabatan_detail a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where unittipe ='KEBUN' and aktif='1' order by namajabatan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center id=kodejabatan".$no." name=jabatan[]>".$bar['kodejabatan']."</td>";
			$tab.="<td align=left style=cursor:pointer;color:blue; onclick=getkary('".$bar['kodejabatan']."','".$kodeorg."')>".$bar['namajabatan']."</td>";
			$tab.="<td align=right>".$jlh[$bar['kodejabatan']]."</td>";
			if($detjab[$bar['kodejabatan']]!=""){
				$tab.="<td align=center><input id=check".$no." name=check[] type=checkbox checked></td>";
			}else{				
				$tab.="<td align=center><input id=check".$no." name=check[] type=checkbox></td>";
			}
		}
		
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=5><button class=mybutton onclick=addjab('".$no."')>Add / Tambahkan</button></td>";
		$tab.="</tr>";
		$tab.="</table>";
		echo $tab;
	break;
	case 'insert':
		if($kodeorg==''|| $tipetrans=='' || $kolom=='' || $jabatan==''){
			echo "Gagal : Semua field harus diisi.";
			exit();
		}
		if($tipetrans=='SPB' and $kolom!='kerani'){
			exit("Warning : Kolom hanya boleh diisi dengan Kerani.");
		}
		
		$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg='".$kodeorg."' and tipe ='".$tipetrans."' and kolom='".$kolom."'";
		$res = fetchdata($str);
		if(count($res)>0){
			if($id!=''){
				$wh=" id='".$id."'";
			}else{
				$wh=" kodeorg='".$kodeorg."' and tipe ='".$tipetrans."' and kolom='".$kolom."'";
			}
			
			$str="UPDATE ".$dbname.".kebun_5pejabatbkm SET 
				kodeorg='".$kodeorg."',
				tipe='".$tipetrans."',
				kolom='".$kolom."',
				jabatan='".$jabatan."',
				lastupdate='" . $_SESSION['standard']['userid'] . "',
				lastupdatetime='" .date('Y-m-d H:i:s'). "' WHERE ".$wh."";
			try{
				$owlPDO->exec($str); 
				#getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}	
		}else{
			$str="INSERT INTO ".$dbname.".kebun_5pejabatbkm (kodeorg,tipe,kolom,jabatan,lastupdate,lastupdatetime) 
			VALUES ('".$kodeorg."','".$tipetrans."','".$kolom."','".$jabatan."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
			try{
				$owlPDO->exec($str); 
				#getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".kebun_5pejabatbkm where id='".$id."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	case'loaddata':
		$tab="<table id=pvtTable cellpadding=1 cellspacing=1 border=0 class='sortable nowrap hover' width='100%' data-scroll-x='true' scroll-collapse='false'>
		<thead>
			<tr>
				<th rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2>".$_SESSION['lang']['kebun']."</th>
				<th rowspan=2>".$_SESSION['lang']['tipetransaksi']."</th>
				<th rowspan=2>Kolom</th>
				<th rowspan=2>".$_SESSION['lang']['jabatan']."</th>
				<th rowspan=2>".$_SESSION['lang']['updateby']."</th>
				<th rowspan=2>".$_SESSION['lang']['tanggal']."</th>
				<th colspan='2'>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr>
				<th style=display:none></th>
				<th style=display:none></th>
			</tr>
		</thead><tbody>";
		
		$where="";
		if ($kodeorg != '') {
            $where.=" and kodeorg like '%" . $kodeorg . "%' ";
        }
		if ($tipetrans != '') {
            $where.=" and tipe like '%" . $tipetrans . "%' ";
        }
		if ($kolom != '') {
            $where.=" and kolom like '%" . $kolom . "%' ";
        }
		
		$arrtipe=array('mandor'=>'Mandor','mandor1'=>'Mandor 1','kerani'=>'Kerani','asst'=>'Assisten');
		$arrbkm=array('BKM'=>'BKM Rawat','PNN'=>'BKM Panen','SPL'=>'BKM Sipil','RKH'=>'Rencana Kerja Harian');
		
		$str="select * from ".$dbname.".sdm_5jabatan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmjab[$bar['kodejabatan']]=$bar['namajabatan'];
		}	
		$no=0;
		
		$limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = floatval($page) * floatval($limit);
        $maxdisplay = (floatval($page) * floatval($limit));
        $no = 0;
        $no = $maxdisplay;
		
		$str="select * from ".$dbname.".kebun_5pejabatbkm where 1=1 and kodeorg in (".getOrgDetail(2).") ".$where."";
        $res = fetchdata($str);
        $jlhbrs = count($res);
		
		
		//$str="select * from ".$dbname.".kebun_5pejabatbkm where 1=1 ".$where." order by kodeorg limit " . $offset . "," . $limit . "";
		$str="select * from ".$dbname.".kebun_5pejabatbkm where 1=1 ".$where." and kodeorg in (".getOrgDetail(2).") order by kodeorg";
		$bar = fetchdata($str);
		foreach($bar as $res){
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorg']."'");
			$no+=1;
			$tab.="<tr class=rowcontent >
					<td style='text-align:center;'>".$no."</td>
					<td>".$res['kodeorg']." - ".$nmorg[$res['kodeorg']]."</td>
					<td>".$arrbkm[$res['tipe']]."</td>
					<td>".$arrtipe[$res['kolom']]."</td>";
					$djab=explode(",",$res['jabatan']);
				$tab.="<td>";
					foreach($djab as $jab){
						$tab.= $jab." - ".$nmjab[$jab]."<br>";
					}
				$tab.="</td>";
					
			$tab.="
					<td>".getNamaKaryawan($res['lastupdate'])."</td>
					<td>".tanggalnormal($res['lastupdatetime'])."</td>
					<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$res['kodeorg']."','".$res['tipe']."','".$res['kolom']."','".$res['jabatan']."','".$res['id']."')\"></td>
					<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$res['id']."')\"></td>
				</tr>";
		}
		
		$tab.="</tbody>";
		$tab.="</table>";
		
		echo $tab;
		
	break;
	default:
	break;	
}

function getContainer(){
	global $owlPDO;
	global $dbname;
	
	$arrtipe=array('mandor'=>'Mandor','mandor1'=>'Mandor 1','kerani'=>'Kerani','asst'=>'Assisten');
	$arrbkm=array('BKM'=>'BKM Rawat','PNN'=>'BKM Panen','SPB'=>'SPB');
	
	$str="select * from ".$dbname.".sdm_5jabatan";
	$res = fetchdata($str);
	foreach($res as $bar){
		$nmjab[$bar['kodejabatan']]=$bar['namajabatan'];
	}	
	$no=0;
	$str="select * from ".$dbname.".kebun_5pejabatbkm order by kodeorg";
	$bar = fetchdata($str);
	foreach($bar as $res){
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorg']."'");
		$no+=1;
		echo"<tr class=rowcontent>
				<td style='text-align:center;'>".$no."</td>
				<td>".$res['kodeorg']." - ".$nmorg[$res['kodeorg']]."</td>
				<td>".$arrbkm[$res['tipe']]."</td>
				<td>".$arrtipe[$res['kolom']]."</td>";
				$djab=explode(",",$res['jabatan']);
			echo"<td>";
				foreach($djab as $jab){
					echo $jab." - ".$nmjab[$jab]."<br>";
				}
			echo"</td>";
				
		echo"
				<td>".getNamaKaryawan($res['lastupdate'])."</td>
				<td>".tanggalnormal($res['lastupdatetime'])."</td>
				<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$res['kodeorg']."','".$res['tipe']."','".$res['kolom']."','".$res['jabatan']."','".$res['id']."')\"></td>
				<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$res['id']."')\"></td>
			</tr>";
	}
}
?>