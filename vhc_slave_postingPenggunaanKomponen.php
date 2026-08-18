<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$proses=checkPostGet('proses','');
$notransaksi=checkPostGet('notrans','');
$kepada=checkPostGet('kepada','');
$txt_search=checkPostGet('txtSearch','');
$periodecari=checkPostGet('periodecari','');
$txt_tgl=tanggalsystemn(checkPostGet('txtTgl',''));
if($txt_tgl='--'){
	$txt_tgl='';
}

$lokasi=$_SESSION['empl']['lokasitugas'];
$user_online=$_SESSION['standard']['userid'];
$kdVhc=checkPostGet('kdVhc','');

$sOrg="select kodeorganisasi from ".$dbname.".organisasi where  tipe in ('KEBUN','KANWIL','TRAKSI')";
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$kodeOrg="";
while($rOrg=$res->fetch())
{
        $kodeOrg.="'".$rOrg['kodeorganisasi']."',";
}
$svhc2="select kodeorg from ".$dbname.".vhc_5master group by kodeorg"; //echo $svhc;
$res=$owlPDO->query($svhc2) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rvhc2=$res->fetch()){
	$kodeOrg.="'".$rvhc2['kodeorg']."',";
}

$pnjgn=strlen($kodeOrg)-1;
$personPosting = getPostingJabatan('traksi');
switch($proses)
{	
    case'load_data':
	
        echo"<table cellspacing='1' border='0' class='sortable' cellpadding=5>
        <thead>
        <tr class='rowheader'>
        <th align=center>No</th>
        <th align=center>".$_SESSION['lang']['notransaksi']."</th>
        <th align=center>".$_SESSION['lang']['tanggal']."</th>
        <th align=center>".$_SESSION['lang']['kodevhc']."</th>
        <th align=center>".$_SESSION['lang']['jenisvch']."</th>
        <th align=center>".$_SESSION['lang']['action']."</th>
        </tr>
        </thead>
        <tbody>";
                $limit=20;
                $page=0;
                if(isset($_POST['page']))
                {
                        $page=$_POST['page'];
                        if($page<0)
                                $page=0;
                        }
                $offset=$page*$limit;
				$where='';
				if($txt_search!=''){
						$where.=" and notransaksi LIKE  '%".$txt_search."%' ";
				}
				if($txt_tgl!=''){
						$where.=" and tanggal LIKE '%".$txt_tgl."%' ";
				}
				if($periodecari!=''){
					$where.=" and tanggal LIKE '".$periodecari."%' ";
				}
                $sql2="select count(*) as jmlhrow from ".$dbname.".vhc_penggantianht where 
                           kodeorg like '%".$_SESSION['empl']['lokasitugas']."%'
                           and posting=0 ".$where." order by tanggal asc";
				// exit('error'.$sql2);
                $res=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$res->fetch()){
                        $jlhbrs= $jsl->jmlhrow;
                }
                $slvhc="select * from ".$dbname.".vhc_penggantianht where
                                kodeorg like '%".$_SESSION['empl']['lokasitugas']."%'
                                and posting=0 ".$where." order by tanggal asc limit ".$offset.",".$limit."";
                $res=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $no=0;
				while($rlvhc=$res->fetch()){
					$no+=1;
					$pvhc="select kodevhc,jenisvhc from ".$dbname.".vhc_5master where kodevhc='".$rlvhc['kodevhc']."'order by kodevhc";
					$res1=$owlPDO->query($pvhc) or die(print " Gagal: ".PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_ASSOC);
					$rpvhc=$res1->fetch();
					echo"
					<tr class=rowcontent>
					<td align=center>". $no."</td>
					<td>". $rlvhc['notransaksi']."</td>
					<td>". tanggalnormal($rlvhc['tanggal'])."</td>
					<td>". $rlvhc['kodevhc']."</td>
					<td>". $rpvhc['jenisvhc']."</td>";
					$sCek="select kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
					$res2=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
					$res2->setFetchMode(PDO::FETCH_ASSOC);
					$rCek=$res2->fetch();
				if(in_array($rCek['kodejabatan'],$personPosting)){
					echo"<td>
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$rlvhc['notransaksi'].",".$rlvhc['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\">&nbsp;
					<img src=images/skyblue/zoom.png class=resicon  title='Preview' onclick=\"html('".$rlvhc['notransaksi']."');\">
					&nbsp;";
						if($rlvhc['posting']<1){
							echo
							"<a href=# onClick=\"posting_data('".$rlvhc['notransaksi']."','".$rlvhc['kodevhc']."')\" ><img src=images/icons/04/16/01.png class=resicon  title='Posting'></a>
							
							</td>";
						}else{ echo $_SESSION['lang']['posting'];}
				} else {
					if($rlvhc['posting']<1)
						$post="<img src=images/icons/04/16/01.png class=resicon  title='Posting'>";
					else
						$post="<img src=images/icons/04/16/02.png class=resicon  title='Posted'>";
					echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$rlvhc['notransaksi'].",".$rlvhc['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\">&nbsp;
					<img src=images/skyblue/zoom.png class=resicon  title='Preview' onclick=\"html('".$rlvhc['notransaksi']."');\">&nbsp;".$post."
						</td>";
				}
					echo"</tr>";
			}
        echo"
        <tr><td colspan=6 align=center>
        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
        </td>
        </tr></tbody></table>";
	break;

	case 'postingData':	
	$scek="select * from ".$dbname.".vhc_penggantianht where notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$rcek=$res->fetch();
	// if($rcek['external']=='external'){
	// 	$str="select * from ".$dbname.".setup_approval where kodeunit = '".substr($rcek['kodeorg'],0,4)."' and jenispersetujuan='SVC' and level='1'";
	// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$row=$res->rowCount();
	// 	$res->setFetchMode(PDO::FETCH_ASSOC);
	// 	$aju=$res->fetch();
	// 	if($row<1){
	// 		exit('Error : Silahkan tambahkan nama penyetuju melalui menu : Setup - Persetujuan');
	// 	}
	// }
	
	
	#validasi posting
	validasiInput(substr($rcek['kodeorg'],0,4),'','WSPOST',$rcek['tanggal'],$exit='1');

	$scek="select * from ".$dbname.".vhc_penggantianht where tanggal < '".$rcek['tanggal']."' and kodeorg='".$rcek['kodeorg']."' and posting='0'";
	//exit("error.$scek");
	$res=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
	$row=$res->rowCount();
	if($row!=0){
		exit('Error : Silahkan posting dari tanggal terkecil.');
	}
	
	$sCekv="select kodejabatan from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
	$res=$owlPDO->query($sCekv) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$rCekv=$res->fetch();
	if(!in_array($_SESSION['empl']['kodejabatan'],$personPosting)){
			echo"warning : Anda tidak memiliki autorisasi atau No Transaksi ini sudah di posting!!";
			exit();
	}		
	$sudPost="update ".$dbname.".vhc_penggantianht set posting='1',postingby='".$user_online."',postedtime='".date('Y-m-d H:i:s')."' where notransaksi='".$notransaksi."' and kodevhc='".$kdVhc."'";
	try{$owlPDO->exec($sudPost); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	
	$str="select * from ".$dbname.".vhc_penggantiandt_pengembalian where notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$row=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if($row>0){
		while($bar=$res->fetch()){
			$jam=date("H:i:s");
			$tgljam=$rcek['tanggal']." ".$jam;
			$ins="insert into ".$dbname.".vhc_stokbarangbekas (`kodeorg`,`notransaksi`,`tanggal`,`tanggaljam`,`kodebarang`,`masuk`,`updateby`,`keterangan`) values 
			('".$rcek['kodeorg']."','".$rcek['notransaksi']."','".$rcek['tanggal']."','".$tgljam."','".$bar['kodebarang']."','".$bar['jumlah']."','".$_SESSION['standard']['userid']."','".$bar['keterangan']."')";
			try{$owlPDO->exec($ins);}catch (PDOException $e){print " Gagal !: ".$e->getMessage()."\n"; die(); }
		}
	}
	// echo $rcek['external'];
	break;
		
	case'form_ajukan';
	$scek="select * from ".$dbname.".vhc_penggantianht where notransaksi='".$notransaksi."'";
	$res=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$rcek=$res->fetch();
	
	$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
		  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
		  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SVC' and a.level='1' and a.kodeunit = '".substr($rcek['kodeorg'],0,4)."'  order by b.namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$optKry="";
	while($rkry=$res->fetch())
	{
		$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
	}
	
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:150px;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td></td>
					<td align=center><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	case'ajukan':
	
		if($kepada=='' or $notransaksi==''){
			exit('Error : Isikan nama penyetuju.');
		}
		//update flag menjadi 1
        $str = "update " . $dbname . ".vhc_penggantianht set persetujuan='".$kepada."',tglpersetujuan='".date('Y-m-d')."',postedtime='".date('Y-m-d H:i:s')."' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		//insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$notransaksi."','SVC','1','" . $kepada."','0','','','" . date('Y-m-d') . "')";
		
		// exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
        default:
        break;
}
?>