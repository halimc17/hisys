<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$notransaksi=$_POST['notransaksi'];
$karyawanid=$_POST['karyawanid'];
$kodeorg=$_POST['kodeorg'];
$tanggalperjalanan=tanggalsystem($_POST['tanggalperjalanan']);
$tanggalkembali=tanggalsystem($_POST['tanggalkembali']);
$tujuan2=$_POST['tujuan2'];	
$tujuan2            = checkPostGet('tujuan2', '');
$tujuan1=$_POST['tujuan1'];	
$unit=$_POST['unit'];
$method=$_POST['method'];
$jenis=$_POST['jenis'];
if($tujuan1 == ""){
	$tujuan1 = "-";//karena Not NULL
}
$persetujuan=$_POST['persetujuan'];
$per['persetujuan1']=$_POST['persetujuan1'];
$per['persetujuan2']=$_POST['persetujuan2'];
$per['persetujuan3']=$_POST['persetujuan3'];
$jenispersetujuan='PJDINASNS';
$countApp = getCountApproval($jenispersetujuan);

//author - atwal
//Param Array
$rutedari = $_POST['rutedari'];	
$rutetujuan = $_POST['rutetujuan'];	
$rutewaktu = $_POST['rutewaktu'];	
$rutetrans = $_POST['rutetrans'];	
$rencanatanggal = $_POST['rencanatanggal'];	
$rencanakegiatan = $_POST['rencanakegiatan'];	
// END:


switch ($method) {

	case 'getpersetujuan':
		//persetujuan
		if ($kodeorg!='') {
			$whrper=" and kodeunit='".$kodeorg."'";
			$whrkar=" and lokasitugas='".$kodeorg."'";
		}else{
			if ($_SESSION['empl']['tipelokasitugas']!='HOLDING') {
				$whrper=" and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
				$whrkar=" and lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
			}
		}

		$str="select karyawanid from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' ".$whrper;
		$res=fetchData($str);
		if (count($res)==0) {
			exit('warning : Persetujuan untuk unit '.$kodeorg.' belum di setting. Silahkan setting pada menu setup>persetujuan.');
		}

		$formper="<table>";
	  	for ($i=1; $i <=$countApp; $i++) { 

	  		$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str="select karyawanid from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' ".$whrper;
			$res=fetchData($str);
			foreach ($res as $key => $bar) {
				$whr=" karyawanid='".$bar['karyawanid']."'";
				$optnama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

				if ($notransaksi!='') {
					$strcek="select karyawanid from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$i."'";
					$rescek=$owlPDO->query($strcek) or die(print " Gagal: ".PDOException::getMessage());
					$rescek->setFetchMode(PDO::FETCH_ASSOC);
					$barcek=$rescek->fetch();
				}
				
				if ($barcek['karyawanid']==$bar['karyawanid']) {
					$optper1.="<option value=".$bar['karyawanid']." selected>".$optnama[$bar['karyawanid']]."</option>";
				}else{
					$optper1.="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
				}
			}

	  		$formper.="<tr>
					<td>".$_SESSION['lang']['approve']." ".$i."</td> 
					<td>:</td>
					<td><select id=persetujuan".$i." style='width:150px'>".$optper1."</select></td>
				</tr>";
	  	}
	  	$formper.="<input type=hidden id=countper value='".$countApp."'>
	  			<table>";

		//nama karyawan
		$str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in ('1','2','3','4','5','6') and karyawanid!='".$_SESSION['standard']['userid']."' and statuskaryawan != 'Keluar'  and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') ".$whrkar;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar=$res->fetch()){

			if ($notransaksi!='') {
				$strkar="select karyawanid from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
				$reskar=$owlPDO->query($strkar) or die(print " Gagal: ".PDOException::getMessage());
				$reskar->setFetchMode(PDO::FETCH_ASSOC);
				$barkar=$reskar->fetch();
			}

			if ($barkar['karyawanid']==$bar['karyawanid']) {
				$optkaryawan.="<option value=".$bar['karyawanid']." selected>".$bar['namakaryawan']."</option>";
			}else{
				$optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['nik']." - ".$bar['namakaryawan']."</option>";
			}
		}

		echo $optkaryawan."####".$formper;
	break;

	case 'insert':
		
		//get number
		$potSK=$kodeorg.date('Y');
		$str="select notransaksi from ".$dbname.".sdm_pjdinasht where  notransaksi like '".$potSK."%' order by notransaksi desc limit 1";
		 
		$notrx=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			$notrx=substr($bar->notransaksi,10,5);
		}
		$notrx=intval($notrx);
		$notrx=$notrx+1;
		$notrx=str_pad($notrx, 5, "0", STR_PAD_LEFT);
		$notrx=$potSK.$notrx;
		 
		$str="insert into ".$dbname.".sdm_pjdinasht (`notransaksi`,`karyawanid`,`tanggalbuat`,`tanggalperjalanan`,`kodeorg`,`tujuan1`,`tujuan2`,unit,`tanggalkembali`,`jenis`,`createdby`,`jeniskaryawan`) values 
				('".$notrx."','".$karyawanid."','".date('Ymd')."','".$tanggalperjalanan."','".$kodeorg."','".$tujuan1."','".$tujuan2."','".$unit."','".$tanggalkembali."','".$jenis."','".$_SESSION['standard']['userid']."','1')";
		try{

            $owlPDO->exec($str);

            for($i=0; $i<count($rencanatanggal); $i++){
			$qstr[] ="insert into ".$dbname.".sdm_pjdinasdt2 (`notransaksi`,`tanggal`,`keterangan`) values 
					 ('".$notrx."','".tanggalsystem($rencanatanggal[$i])."','".$rencanakegiatan[$i]."')";
			}

			for($i=0; $i<count($rutedari); $i++){
			$datetime = explode('_',$rutewaktu[$i]);
			$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt_rute (`notransaksi`,`waktu`,`tujuan`,`dari`,`transportasi`) values 
					  ('".$notrx."','".tanggalsystemn($datetime[0])." ".$datetime[1]."','".$rutetujuan[$i]."','".$rutedari[$i]."','".$rutetrans[$i]."')";
			}

			for($i=0; $i<count($qstr); $i++){
				$owlPDO->exec($qstr[$i]); 
			}


			for ($i=1; $i <=$countApp; $i++) { 

				if ($i==1) {
					$status=0;
				}else{
					$status=9;
				}

				if($_POST['persetujuan'.$i]!=''){
					$strap="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`status`,`karyawanid`) values 
						  ('".$notrx."','".$jenispersetujuan."','".$i."','".$status."','".$_POST['persetujuan'.$i]."')";
					try{
			            $owlPDO->exec($strap); 
			        }catch(PDOException $e){
			            echo " Gagal," . addslashes($e->getMessage());
			        }
				}
			}

        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        echo $notrx;

	break;

	case 'update':

		$str="update ".$dbname.".sdm_pjdinasht set `tanggalperjalanan`='".$tanggalperjalanan."',`kodeorg`='".$kodeorg."',
			 `tujuan1`='".$tujuan1."',`tujuan2`='".$tujuan2."',`tanggalkembali`='".$tanggalkembali."',`jenis`='".$jenis."'
			 where karyawanid='".$karyawanid."' and notransaksi='".$notransaksi."' and jeniskaryawan=1"; 	
		try{

            $owlPDO->exec($str);

            $qstr[]="delete from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."'"; 	

			$qstr[]="delete from ".$dbname.".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'"; 
				    
			for($i=0; $i<count($rencanatanggal); $i++){
				$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt2 (`notransaksi`,`tanggal`,`keterangan`) values 
					  	  ('".$notransaksi."','".tanggalsystem($rencanatanggal[$i])."','".$rencanakegiatan[$i]."')";
			}

			for($i=0; $i<count($rutedari); $i++){
				$datetime = explode('_',$rutewaktu[$i]);
				$qstr[] = "insert into ".$dbname.".sdm_pjdinasdt_rute (`notransaksi`,`waktu`,`tujuan`,`dari`,`transportasi`) values 
						  ('".$notransaksi."','".tanggalsystemn($datetime[0])." ".$datetime[1]."','".$rutetujuan[$i]."','".$rutedari[$i]."','".$rutetrans[$i]."')";
			}

			for ($i=1; $i <=$countApp; $i++) { 

				if ($i==1) {
					$status=0;
				}else{
					$status=9;
				}

				if($_POST['persetujuan'.$i]!=''){
					$qstr[]="update ".$dbname.".approval set karyawanid='".$_POST['persetujuan'.$i]."' where notransaksi='".$notransaksi."' 
					    and jenispersetujuan='".$jenispersetujuan."' and level='".$i."'";
				}
			}

			for($i=0; $i<count($qstr); $i++){
				$owlPDO->exec($qstr[$i]); 
			}

        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        echo $notransaksi;

	break;

	case 'delete':
		
		$notransaksi=$_POST['notransaksi'];
		$str="delete from ".$dbname.".sdm_pjdinasht where karyawanid='".$karyawanid."' and notransaksi='".$notransaksi."'"; 
		try{

            $owlPDO->exec($str);

            $qstr[]="delete from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."'"; 	

			$qstr[]="delete from ".$dbname.".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'"; 

			$qstr[]="delete from ".$dbname.".approval where notransaksi='".$notransaksi."'"; 

			for($i=0; $i<count($qstr); $i++){
				$owlPDO->exec($qstr[$i]); 
			}

        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
	break;

	case 'loaddata':

		if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
			$whereKary = " and bagian = 'HHRS'";
		} else {
			$whereKary = " and bagian = 'HRA' and kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
		}

		//limit/page
		$limit=20;
		$page=0;
		//========================
		//ambil jumlah baris dalam tahun ini
		$notransaksi="";
		if(isset($_POST['tex']))
		{
			$notransaksi.=$_POST['tex'];
		}

		$str="select count(*) as jlhbrs from ".$dbname.".sdm_pjdinasht where notransaksi like '%".$notransaksi."%'
				and createdby=".$_SESSION['standard']['userid']." and jeniskaryawan=1 order by jlhbrs desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			$jlhbrs=$bar->jlhbrs;
		}		
		//==================
				 
		if(isset($_POST['page'])){
		 	$page=$_POST['page'];
		    if($page<0)
			  $page=0;
		}
		$offset=$page*$limit;
		  
		$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi like '%".$notransaksi."%' and createdby=".$_SESSION['standard']['userid']."
			 and jeniskaryawan=1 order by tanggalbuat desc limit ".$offset.",20";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);		
		$no=$page*$limit;
		while($bar=$res->fetch())
		{
		  	$no+=1;
			$namakaryawan='';
			$strx="select namakaryawan from ".$dbname.".datakaryawan where karyawanid=".$bar->karyawanid;
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_OBJ);
			while($barx=$resx->fetch())
			{
				$namakaryawan=$barx->namakaryawan;
			}

			$add='';
			if($bar->statuspersetujuan==0)
			{
				$add.="&nbsp <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">
			 	&nbsp <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPJD('".$bar->notransaksi."','".$bar->karyawanid."');\">";
			}

			$ttl='';
			$stat = array('0' =>$_SESSION['lang']['wait_approve'],'1' =>$_SESSION['lang']['disetujui'],'3'=>$_SESSION['lang']['ditolak'] );
			$statper = array('0' =>$_SESSION['lang']['wait_approve'],'1' =>$_SESSION['lang']['disetujui'],'2'=>$_SESSION['lang']['ditolak'] );
			$strap="select * from ".$dbname.".approval where notransaksi='".$bar->notransaksi."' order by level asc";	
			$resap=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
			$resap->setFetchMode(PDO::FETCH_ASSOC);
			while($barap=$resap->fetch())
			{
				$ttl.="Persetujuan ".$barap['level']." : ".$stat[$barap['status']]." (".$barap['komentar'].")\n";
			}

			echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".$bar->notransaksi."</td>
			<td>".$namakaryawan."</td>
			<td>".tanggalnormal($bar->tanggalbuat)."</td>
			<td align=center title='".$ttl."'>".$statper[$bar->statuspersetujuan]."</td>
			<td align=center>
			 <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewPJD('".$bar->notransaksi."','".$bar->jeniskaryawan."',event);\"> 
			".$add."
			</td>
			</tr>";
		}

		echo"<tr><td colspan=11 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
		<br>
		<button class=mybutton onclick=cariPJD(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariPJD(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";

	break;
	
	case 'getunit':

		$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$tujuan2."' and length(kodeorganisasi)='4'";
		$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while($rKbn=$qKbn->fetch())
		{
			$optKebun.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
		}
		if($tujuan2=='umum'){			
			$optKebun.="<option value='umum'>Umum</option>";
		}
		
		$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($tujuan2!=''){
			$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$tujuan2."' and tipe='KEBUN') and tipe='AFDELING'";
			$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
			$qKbn->setFetchMode(PDO::FETCH_ASSOC);
			while($rKbn=$qKbn->fetch())
			{
				$optAfd.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
			}
		}
		
		echo $optKebun."##".$optAfd;
	break;
	default:
		# code...
		break;
}


?>