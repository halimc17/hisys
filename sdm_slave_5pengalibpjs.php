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

$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

$arrsts=array('1'=>'Aktif','0'=>'Tidak Aktif');


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
	case'getkomponen':
		$tab="<table id=tabledt cellpadding=5 cellspacing=1 ".$border." class=sortable width=100%>
			<thead><tr class=rowheader>
			<td align=center >No</td>
			<td align=center >Kode<br>Komponen</td>
			<td align=center >Nama Komponen</td>
			<td align=center >Action</td>
		</tr></thead><tbody>"; 
		
        $str = "select * from ".$dbname.".sdm_ho_component where id not in (70,71,72,73,80,3,61,67,44,81)";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kdkomp=$bar['id'];
		}
		if(count($res)>0){			
			$dekomp=explode(",",$kdkomp);
			foreach($dekomp as $kompho){
				$dethokomp[$kompho]=$kompho;
			}
		}
		
        $str = "select * from ".$dbname.".sdm_ho_component where id not in (70,71,72,73,80,3,61,67,44,81)";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center id=kodekomponen".$no." name=komponen[]>".$bar['id']."</td>";
			$tab.="<td align=left >".$bar['name']."</td>"; 
			if($dethokomp[$bar['id']]!=""){
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
        
		if($kodeorg==''|| $param['tipekaryawan']=='' || $param['jenisbpjs']=='' || $param['komponengaji']==''){
            echo "Gagal : Semua field harus diisi.";
			exit();
		}
        
		
		$str="select * from ".$dbname.".sdm_5komponenbpjspengali where kodeorg='".$kodeorg."' and tipekaryawan ='".$param['tipekaryawan']."' and jenisbpjs='".$param['jenisbpjs']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			if($id!=''){
				$wh=" id='".$id."'";
			}else{
				$wh=" kodeorg='".$kodeorg."' and tipe ='".$tipetrans."' and kolom='".$kolom."'";
			}
			
			$str="UPDATE ".$dbname.".sdm_5komponenbpjspengali SET 
				kodeorg='".$kodeorg."',
				tipekaryawan='".$param['tipekaryawan']."',
				jenisbpjs='".$param['jenisbpjs']."',
				komponengaji='".$param['komponengaji']."',
				status='".$param['idsts']."',
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
			$str="INSERT INTO ".$dbname.".sdm_5komponenbpjspengali (kodeorg,tipekaryawan,jenisbpjs,komponengaji,status,updateby,updatetime) 
			VALUES ('".$kodeorg."','".$param['tipekaryawan']."','".$param['jenisbpjs']."','".$param['komponengaji']."','".$param['idsts']."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
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
		$str="delete from ".$dbname.".sdm_5komponenbpjspengali where kodeorg='".$param['kodeorg']."' and tipekaryawan='".$param['tipekaryawan']."' and jenisbpjs='".$param['jenisbpjs']."'";
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
				<th rowspan=2>".$_SESSION['lang']['kodeorg']."</th>
				<th rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>
				<th rowspan=2>Jenis BPPJS</th>
				<th rowspan=2>Komponen Gaji</th>
				<th rowspan=2>Status</th>
				<th rowspan=2>".$_SESSION['lang']['createby']."</th>
				<th colspan=2>".$_SESSION['lang']['action']."</th> 
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
		 
		
		$str="select * from ".$dbname.".sdm_ho_component";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmkomponen[$bar['id']]=$bar['name'];
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
		
		$str="select * from ".$dbname.".sdm_5komponenbpjspengali where 1=1 ".$where."";
        $res = fetchdata($str);
        $jlhbrs = count($res);
		
		
		//$str="select * from ".$dbname.".sdm_5komponenbpjspengali where 1=1 ".$where." order by kodeorg limit " . $offset . "," . $limit . "";
		$str="select * from ".$dbname.".sdm_5komponenbpjspengali where 1=1 ".$where." order by kodeorg";
		$bar = fetchdata($str);
		foreach($bar as $res){
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorg']."'");
			$no+=1;
			$tab.="<tr class=rowcontent >
					<td style='text-align:center;'>".$no."</td>
					<td>".$res['kodeorg']." - ".$nmorg[$res['kodeorg']]."</td>
					<td align='center'>".getNamaTipekaryawan($res['tipekaryawan'])."</td>
					<td align='center'>".getNamaKomponenGaji($res['jenisbpjs'])."</td>";
					$djab=explode(",",$res['komponengaji']);
                    $tab.="<td>";
					foreach($djab as $jab){
                        $tab.= $jab." - ".$nmkomponen[$jab]."<br>";
                        }
                        $tab.="</td>";
                        
            $tab.="<td align='center'>".$arrsts[$res['status']]."</td>";

			$tab.="
					<td align=center>".getNamaKaryawan($res['updateby'])." </br> ( ".tanggalnormal($res['updatetime'])." ".substr($res['updatetime'],11,8)." )</td>
					<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$res['kodeorg']."','".$res['tipekaryawan']."','".$res['jenisbpjs']."','".$res['']."','".$res['id']."')\"></td>
					<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$res['kodeorg']."','".$res['tipekaryawan']."','".$res['jenisbpjs']."')\"></td>
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
	$str="select * from ".$dbname.".sdm_5komponenbpjspengali order by kodeorg";
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