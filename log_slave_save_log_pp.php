<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$optNm=makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optsklbrg=makeOption($dbname,'log_5subklbarang','kode,namasubkelompok');
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$nopp=		isset($_POST['rnopp'])? $_POST['rnopp']: '';
$tanggal=	isset($_POST['rtgl_pp'])? tanggalsystem($_POST['rtgl_pp']): '';
$kodeorg=	isset($_POST['rkd_bag'])? $_POST['rkd_bag']: '';
$method=	isset($_POST['method'])? $_POST['method']: '';
$user_id=	isset($_POST['usr_id'])? $_POST['usr_id']: '';
$nopp2=		isset($_POST['dnopp'])? $_POST['dnopp']: '';
$stat_cls=	isset($_POST['stat'])? $_POST['stat']: '';
$tgl=  date('Ymd');
$bln = substr($tgl,4,2);
$thn = substr($tgl,0,4);

switch($method){
	case 'delete':
		$strx="delete from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		try{
			$owlPDO->exec($strx);
			$ql="delete from ".$dbname.".log_prapodt where nopp='".$nopp."'";
			try{
				$owlPDO->exec($ql);
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case 'update':
		$strx="update ".$dbname.". log_prapoht set tanggal='".$tanggal."',kodeorg='".$kodeorg."' where nopp='".$nopp."'";
		try{
			$owlPDO->exec($strx);
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die();
		}
	break;	

	case 'insert':	
		if($nopp=='')
		{
			echo"Warning: Please use system properly, PR number not defined";
			exit();
		}
		else
		{
			$sorg="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
			$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
			$qorg->setFetchMode(PDO::FETCH_OBJ);
			$rorg=$qorg->fetch();
			$kd_org=$rorg['induk'];
			
			$strx="insert into ".$dbname.".log_prapoht(`nopp`, `kodeorg`, `tanggal`,`dibuat`)
							values('".$nopp."','".$kd_org."','".$tanggal."','".$user_id."')";
			try{
				$owlPDO->exec($strx); 
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
	break;
	
	case 'delete_temp':
		//echo "test";
		$strx="delete from ".$dbname.".log_prapoht where nopp='".$nopp2."'";	
		try{
			$owlPDO->exec($strx); 
		}catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	
	case 'insert_persetujuan':
		$sql="SELECT * FROM ".$dbname.".`log_prapoht` WHERE `nopp`='".$nopp."' ";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$rest=$query->fetch();
		
		if($rest['close']>1)
		{
			echo"Warning: Status closed, Can't update the status";
			exit();
		}
		elseif(($rest['hasilpersetujuan1']<1))
		{
			$stat_cls=1;
			$strx="update ".$dbname.". log_prapoht set persetujuan1='".$user_id."',close='".$stat_cls."'  where nopp='".$nopp."'";
			try{
				$owlPDO->query($strx);
				#send an email to incharge person
				$to=getUserEmail($user_id);
				$namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
				if($_SESSION['language']=='EN'){    
					$subject="[Notifikasi] PR Submission for approval, submitted by: ".$namakaryawan;
					$body="<html>
							 <head>
							 <body>
							   <dd>Dear Sir/Madam,</dd><br>
							   <br>
							   Today,  ".date('d-m-Y').",  on behalf of ".$namakaryawan." submit a PR, requesting for your approval. To follow up, please follow the link below.
							   <br>
							   <br>
							   <br>
							   Regards,<br>
							   Owl-Plantation System.
							 </body>
							 </head>
						   </html>
						   ";
				}else{
					$subject="[Notifikasi]Persetujuan PP a/n ".$namakaryawan;
					$body="<html>
							 <head>
							 <body>
							   Dengan Hormat,<br>
							   <br>
							   Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Permintaan Pembelian Barang
							   kepada bapak/ibu dengan nomor <b>".$nopp."</b> Untuk menindak-lanjuti, silahkan ikuti link dibawah.
							   <br>
							   <br>
							   <br>
							   Regards,<br>
							   Owl-Plantation System.
							 </body>
							 </head>
						   </html>
						   ";                                            
				}
				$kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		} else {
			echo"Warning: Documents already in the process";
			exit();
		}
	break;
	
	case 'cari_nopp':
		if($tanggal==''){
			$strx="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		}elseif($nopp==''){	
			$strx="select * from ".$dbname.".log_prapoht where nopp='".$nopp."' or tanggal like '%".$tanggal."%'";
		}else{
			$strx="select * from ".$dbname.".log_prapoht where nopp='".$nopp."' and tanggal = '".$tanggal."'";
		} 
	break;
	
	case 'cek_pembuat_pp':
		$user_id=$_SESSION['standard']['userid'];
		$skry="select dibuat from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$qkry=$owlPDO->query($skry) or die(print " Gagal: ".PDOException::getMessage());
		$qkry->setFetchMode(PDO::FETCH_ASSOC);
		$rkry=$qkry->fetch();
		if($rkry['dibuat']!=$user_id)
		{
				echo "warning: Please see your Username";
				exit();
		}
		break;
		
    case'refresh_data':
        $limit=50;
        $page=0;
        if(isset($_POST['page'])) {
			$page=$_POST['page'];
			if($page<0)
			$page=0;
        }
        $offset=$page*$limit;
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
			$sCek="select bagian from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=$qCek->fetch();
			if($rCek['bagian']=='PRO'||$rCek['bagian']=='AGR') {
				$sql="select count(*) jmlhrow from ".$dbname.".log_prapoht order by tanggal desc";
				$str="select * from ".$dbname.".log_prapoht order by tanggal desc limit ".$offset.",".$limit." ";
			} else {
				$sql="select count(*) jmlhrow from ".$dbname.".log_prapoht where dibuat='".$_SESSION['standard']['userid']."' order by tanggal desc";
				$str="select * from ".$dbname.".log_prapoht where  dibuat='".$_SESSION['standard']['userid']."' order by tanggal desc limit ".$offset.",".$limit." ";
			}
        } else {
			$sql="select count(*) jmlhrow from ".$dbname.".log_prapoht where substring(nopp,16,4)='".$_SESSION['empl']['lokasitugas']."'";
			$str="select * from ".$dbname.".log_prapoht where substring(nopp,16,4)='".$_SESSION['empl']['lokasitugas']."' order by tanggal desc limit ".$offset.",".$limit."";
        }
		
		$rCount=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$rCount->setFetchMode(PDO::FETCH_OBJ);
		while($bCount=$rCount->fetch()){
			$jlhbrs= $bCount->jmlhrow;
		}
		
		$no=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()) {
			$koderorg=substr($bar['nopp'],15,4);//substring(nopp,16,4)$bar['kodeorg'];
			$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; 
			$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_ASSOC);
			$bas=$rep->fetch();
			
			$skry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar['dibuat']."'";
			$rkry=$owlPDO->query($skry) or die(print " Gagal: ".PDOException::getMessage());
			$rkry->setFetchMode(PDO::FETCH_ASSOC);
			$bkry=$rkry->fetch();
			
			$cekPt=substr($bar['nopp'],12,4);
			$no+=1;
			$b="";
			if($bar['close']=='0') {
				$b="<a href=# id=seeprog onclick=frm_ajun('".$bar['nopp']."','".$bar['close']."') title=\"Click untuk mengubah status\">Need Approval</a>";
			} elseif($bar['close']=='1') {
				$b="<a href=# id=seeprog onclick=frm_ajun('".$bar['nopp']."','".$bar['close']."') title=\"Menunggu Keputusan\">Waiting Approval</a>";
			} elseif($bar['close']=='2') {
				for($i=1;$i<6;$i++) {
					if($bar['hasilpersetujuan'.$i]==1) {	
						$b="<a href=# id=seeprog  title=\"Available\">".$_SESSION['lang']['disetujui']."</a>";
					} elseif($bar['hasilpersetujuan'.$i]==3) {
						$b="<a href=# id=seeprog  title=\"Not Available\">".$_SESSION['lang']['ditolak']."</a>";
					}
				}
			}
			$ed_kd_org=substr($bar['nopp'],15,4);
			if($bar['tglp1']=='') {$stTgl='0';}
			else {
				$stTgl=5;
			}
			echo"<tr class=rowcontent id='tr_".$no."'>
				<td align=center>".$no."</td>
				<td align=center>".$bar['nopp']."</td>
				<td align=center>".tanggalnormal($bar['tanggal'])."</td>
				<td>".$bas['namaorganisasi']."</td>
				<td>".$bkry['namakaryawan']."</td>
				<td>".$b."</td>";
			if($bar['dibuat']==$_SESSION['standard']['userid']) {
				if($bar['close']!='2'){
					echo"<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['nopp']."','".tanggalnormal($bar['tanggal'])."','".$ed_kd_org."','".$bar['close']."','".$stTgl."');\" >
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPp('".$bar['nopp']."','".$bar['close']."','".$stTgl."');\" >";
					echo"<img onclick=\"previewDetail('".$bar['nopp']."',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\"><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\"></td>";
				}else{
					echo"<td align=center><img onclick=\"previewDetail('".$bar['nopp']."',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\"><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\"></td>";	
				}
			} else {
				echo"<td align=center><img onclick=\"previewDetail('".$bar['nopp']."',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\"></td>";
			}
		}
		echo"</tr>
			<tr>
			<td colspan=7 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
				<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
			</tr>";
	break;
    
	case'getDetailPP':
		echo"<script language=\"javascript\" src=\"js/log_pp.js\"></script>";
		echo"<script language=\"javascript\" src=\"js/log_pp.js\"></script>";
		echo"<div style='width:auto;overflow:auto;'>
			<table border=0 cellspacing=1 class=sortable width=1000px>
		<thead>
		<tr><td>".$_SESSION['lang']['tanggal']." PP</td><td>".$_SESSION['lang']['dbuat_oleh']."</td>";
		for($i=1;$i<6;$i++)
		{
				echo"<td>".$_SESSION['lang']['persetujuan'].$i."</td>";
		}
		echo"<td align=center>No.Capex</td></tr>
		</thead>
		<tbody>";
		$sPP="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$qPP=$owlPDO->query($sPP) or die(print " Gagal: ".PDOException::getMessage());
		$qPP->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$qPP->fetch())
		{

			$ket=explode('/',$bar['keterangan']);
			if (@$ket[1]=='FRM'){
				$ketcapex=$bar['keterangan'];
			}else{
				$ketcapex='-';
			}

			$sql="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar['dibuat']."'";
			$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
			$ret=$query->fetch();
			echo"<tr class=rowcontent><td>".tanggalnormal($bar['tanggal'])."</td><td>".$ret['namakaryawan']."</td>";
			$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);
				for($i=1;$i<6;$i++)
				{
					$tngl='';
					if($bar['tglp'.$i]!='')
					{
						$tngl=$bar['tglp'.$i];
					}
					if(($bar['persetujuan'.$i]!='')&&($bar['persetujuan'.$i]!=0))
					{	
						$kr=$bar['persetujuan'.$i];
						$sql="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$kr."'";
						$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
						$query->setFetchMode(PDO::FETCH_ASSOC);
						$yrs=$query->fetch();
						echo"<td>".$yrs['namakaryawan']."<br />".$arrHsl[$bar['hasilpersetujuan'.$i]].", ".tanggalnormal($tngl)."</td>";
					}
					else
					{
						echo"<td>&nbsp;</td>";
					}
				}				 
			echo"<td align=center>".$ketcapex."</td></tr>";
		}
		echo"
		</tbody>
		</table>
		<br />
		";
		echo"
		<table border=0 cellspacing=1 class=sortable width=1000px>
		<thead>
		<tr>
		<td>No</td>
		<td>Chat</td>
		<td>".$_SESSION['lang']['namabarang']."</td>
		<td>".$_SESSION['lang']['satuan']."</td>
		<td>".$_SESSION['lang']['jmlhDiminta']."</td>
		<td>".$_SESSION['lang']['jmlh_disetujui']."</td>

		<td>".$_SESSION['lang']['realisasi']." Todate</td>
		<td>".$_SESSION['lang']['tanggal']." PR</td>
		 <td>".$_SESSION['lang']['tgldibutuhkan']."</td>   
		<td>".$_SESSION['lang']['status']."</td>
		<td>Out.Std</td>
		<td>".$_SESSION['lang']['lokasiBeli']."</td>
		<td>".$_SESSION['lang']['nopo']."</td>
		<td>".$_SESSION['lang']['tanggal']." PO</td>
		<td>".$_SESSION['lang']['keterangan']."</td>
		</tr>
		</thead>
		";


		$sdhi=date('Y-m-d');

		$sCek="select nopp from ".$dbname.".log_prapodt where nopp='".$nopp."'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);;
		if($rCek>0)
		{
			echo"
			<tbody>";
			$sDet="select a.*,b.tanggal from ".$dbname.".log_prapodt a left join ".$dbname.".log_prapoht b on a.nopp=b.nopp where a.nopp='".$nopp."'";
			$qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
			$qDet->setFetchMode(PDO::FETCH_ASSOC);
			$lokasi=array("Pusat","Lokal");
			$no=0;
			while($res=$qDet->fetch())
			{
				$thnAnggaran=substr($res['tanggal'],0,4);
				$unitAnggaran=substr($nopp,15,4);
				$awalthn=$thnAnggaran."-01-01";
				$sBrg="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
				$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
				$qBrg->setFetchMode(PDO::FETCH_ASSOC);
				$rBrg=$qBrg->fetch();

				$sPoDet="select nopo from ".$dbname.".log_podt where nopp='".$res['nopp']."' and kodebarang='".$res['kodebarang']."'";
				$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
				$rCek=owlBaris($qPoDet);
				
				// $sAnggaran="select sum(jumlah) as jmlhAnggaran from ".$dbname.".bgt_budget_detail where 
					// kodebarang='".$res['kodebarang']."' and tahunbudget='".substr($res['tanggal'],0,4)."' and kodeorg like '".substr($nopp,15,4)."%' group by kodebarang";
					// //exit("");
				// $qAnggaran=$owlPDO->query($sAnggaran) or die(print " Gagal: ".PDOException::getMessage());
				// $qAnggaran->setFetchMode(PDO::FETCH_ASSOC);	
				// $rAnggaran=$qAnggaran->fetch();

				$sSdhi="select sum(jumlahpesan) as sdhi from ".$dbname.". log_po_vw 
					where nopp like '%".substr($nopp,15,4)."%' and kodebarang='".$res['kodebarang']."'
					 and substr(tanggal,1,4)='".$thnAnggaran."'";
				$qDhi=$owlPDO->query($sSdhi) or die(print " Gagal: ".PDOException::getMessage());
				$qDhi->setFetchMode(PDO::FETCH_ASSOC);
				$rDphi=$qDhi->fetch();
				
				// Cek Chat
				$strChat="select * from ".$dbname.".log_pp_chat where "
                . " kodebarang='".$res['kodebarang']."' and nopp='".$nopp."'";
				$resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
				if(owlBaris($resChat)>0)
				{
					$ingChat="chat1";
				} else {
					$ingChat="chat0";
				}
				
				if($rCek>0)
				{
					//echo"warning:A";
					$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
					$rPoDet=$qPoDet->fetch();
					
					$sPo="select tanggal from ".$dbname.".log_poht where nopo='".$rPoDet['nopo']."'";
					$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
					$qPo->setFetchMode(PDO::FETCH_ASSOC);
					$rPo=$qPo->fetch();

					$Tgl2=$rPo['tanggal'];

					$tgl1=$res['tanggal'];
					$pecah1 = explode("-", $tgl1);
					$date1 = $pecah1[2];
					$month1 = $pecah1[1];
					$year1 = $pecah1[0];
					//$tgl1 = $bar->tanggal;

					$pecah2 = explode("-", $Tgl2);
					$date2 = $pecah2[2];
					$month2 = $pecah2[1];
					$year2 =  $pecah2[0];
					$stat=1;
					$nopo=$rPoDet['nopo'];
					$tglPo=tanggalnormal($rPo['tanggal']);
				}
				else
				{	
					//echo"B";
					$tgl1=$res['tanggal'];
					$pecah1 = explode("-", $tgl1);
					$date1 = $pecah1[2];
					$month1 = $pecah1[1];
					$year1 = $pecah1[0];
					//$tgl1 = $bar->tanggal;
					$tgl1 =$year1.$month1.$date1;
					$Tgl2 = date('Y-m-d');			
					$pecah2 = explode("-", $Tgl2);
					$date2 = $pecah2[2];
					$month2 = $pecah2[1];
					$year2 =  $pecah2[0];	
					$tglPo='';
					$stat=0;	
					$nopo="NaN";				
				}

				$jd1 = GregorianToJD($month1, $date1, $year1);
				$jd2 = GregorianToJD($month2, $date2, $year2);
				$jmlHari= $jd2 - $jd1;

				$no+=1;
				//$tolak=array("0"=>$_SESSION['lang']['disetujui'],"3"=>);
				if($res['status']=='3')
				{
					$stat2=$_SESSION['lang']['ditolak'];
					$jmlHari=0;
					$nopo='';
				}
				else
				{
					$stat2="-";
				}
				echo"<tr class=rowcontent style='cursor:pointer;' onclick=detailAnggaran('".$res['kodebarang']."','".$thnAnggaran."','".$unitAnggaran."')>
				<td align=center>".$no."</td>
				<td><img src='images/".$ingChat.".png'
					onclick=\"loadPPChat('".$nopp."','".$res['kodebarang']."',event);\" class='resicon'>
				</td>
				<td>".$rBrg['namabarang']."</td>
				<td>".$rBrg['satuan']."</td>
				<td align=center>".$res['jumlah']."</td>
				<td align=center>".$res['realisasi']."</td>
				
				<td align=center>".number_format($rDphi['sdhi'],0)."</td>
				<td align=center>".tanggalnormal($res['tanggal'])."</td>
				<td align=center>".tanggalnormal($res['tgl_sdt'])."</td>    
				<td align=center>".$stat2."</td>
				<td align=center>".$jmlHari."</td>
				<td align=center>".$lokasi[$res['lokalpusat']]."</td>

				<td>".$nopo."</td>
				<td>".$tglPo."</td>
				<td>".$res['keterangan']."</td>
				</tr>";
            }
		echo"</tbody></table></div><br />";
		echo"<div id=dtFormDetail style=\"overflow:auto; width:500px;height:150px;\">";

		echo"</div>";
		}
		else
		{
				echo"<tbody><tr><td colspan=10>Not Found</td></tr></tbody></table>";
		}
        break;
	case'getTracePP':
		$tab="<script language=\"javascript\" src=\"js/log_pp.js\"></script>";
		$tab.="<div style='width:auto;overflow:auto;'>";
		$tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable>";
		$tab.="<thead>";
		$tab.="<tr class=rowheader>";
		$tab.="<td rowspan=2>".$_SESSION['lang']['kodebarang']."</td>";
		$tab.="<td rowspan=2>".$_SESSION['lang']['namabarang']."</td>";
		$tab.="<td colspan=3 align=center>PP</td>";
		$tab.="<td colspan=3 align=center>Verivikasi</td>";
		$tab.="</tr>";
		$tab.="<tr class=rowheader>";
		$tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
		$tab.="<td>".$_SESSION['lang']['jumlah']."</td>";
		$tab.="<td>".$_SESSION['lang']['satuan']."</td>";
		$tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
		$tab.="<td>".$_SESSION['lang']['realisasi']."</td>";
		$tab.="<td>".$_SESSION['lang']['satuan']."</td>";
		$tab.="</tr>";
		$tab.="</thead><tbody>";
		$whrdt="kodebarang='".$_POST['kodebarang']."' and nopp='".$_POST['rnopp']."'";
		$sData="select * from ".$dbname.".log_prapo_vw where ".$whrdt."";
		$rData=fetchdata($sData);
		$hwr="kodebarang='".$rData[0]['kodebarang']."'";
		$optNm=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$hwr);
		$tab.="<tr class=rowcontent>";
		$tab.="<td>".$rData[0]['kodebarang']."</td>";
		$tab.="<td>".$optNm[$rData[0]['kodebarang']]."</td>";
		$tab.="<td>".tanggalnormal($rData[0]['tanggal'])."</td>";
		$tab.="<td align=right>".number_format($rData[0]['jumlah'])."</td>";
		$tab.="<td>".$rData[0]['satuanpp']."</td>";
		$tab.="<td>".tanggalnormal($rData[0]['tglAlokasi'])."</td>";
		$tab.="<td align=right>".number_format($rData[0]['realisasi'])."</td>";
		$tab.="<td>".$rData[0]['satuankonversi']."</td>";
		$tab.="</tr>";
		$tab.="</tbody></table><br />";

		$tab.="<fieldset><legend>History Material</legend><table cellspacing=1 cellpadding=1 border=0 class=sortable>";
		$tab.="<thead>
			   <tr class=rowheader>
			   <td>".$_SESSION['lang']['tanggal']."</td>
			   <td>".$_SESSION['lang']['notransaksi']."</td>
			   <td>PIC</td>
			   </tr>
		       </thead><tbody>";
		#nopo,surat jalan,penerimaan gudang
		$sPo="select distinct * from ".$dbname.".log_po_vw where  ".$whrdt."";
		$rPo=fetchdata($sPo);
		$tab.="<tr class=rowcontent>";
	 	$tab.="<td>".tanggalnormal($rPo[0]['tanggal'])."</td>";
	 	$tab.="<td>".$rPo[0]['nopo']."</td>";
		$tab.="<td>".$rPo[0]['nm_purchaser']."</td>";
		$tab.="</tr>";

		$sPo="select distinct * from ".$dbname.".log_transaksi_vw where  ".$whrdt."";
		$rPo=fetchdata($sPo);
		if(count($rPo)!=0){
			foreach($rPo as $row=>$isi){
				$optNmOrg=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$isi['updateby']."'");
				$tab.="<tr class=rowcontent>";
			 	$tab.="<td>".tanggalnormal($isi['tanggal'])."</td>";
			 	$tab.="<td>".$isi['notransaksi']."</td>";
				$tab.="<td>".$optNmOrg[$isi['updateby']]."</td>";
				$tab.="</tr>";
			}
			
		}
		$sPo="select  * from ".$dbname.".log_suratjalan_vw where  ".$whrdt."";
		//echo $sPo;
		$rPo=fetchdata($sPo);
		if(count($rPo)!=0){
			foreach($rPo as $row=>$isi){
				$optNmOrg=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$isi['pengirim']."'");
				$optNmOrg2=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$isi['penerima']."'");
				$tab.="<tr class=rowcontent>";
			 	$tab.="<td>".tanggalnormal($isi['tanggal'])."</td>";
			 	$tab.="<td>".$isi['nosj']."</td>";
				$tab.="<td>".$optNmOrg[$isi['pengirim']]."</td>";
				//$tab.="<td>".$optNmOrg2[$isi['penerima']]."</td>";
				$tab.="</tr>";
			}
			
		}
		
		$tab.="</tbody></table></fieldset>";
		$tab.="</div>";
		echo $tab;
	break;
    case'getAnggaran':
		$tab.="<fieldset style=width:400px;><legend>Detail ".$optNm[substr($_POST['kdBarang'],0,3)]."</legend>
			<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
		$tab.="<tr><td>".$optNm[substr($_POST['kdBarang'],0,3)]."</td>";
		$tab.="<td>".$_SESSION['lang']['realisasi']."</td><td>".$_SESSION['lang']['budget']."</td><td>".$_SESSION['lang']['sisa']."</td></tr></thead><tbody>";
		
		$sData="select sum(jumlah) as jmlh,kodebarang from ".$dbname.".bgt_budget_detail where kodebarang like '".substr($_POST['kdBarang'],0,3)."%'
				and tahunbudget='".$_POST['thnAnggaran']."' and kodeorg like '".$_POST['unit']."' group by kodebarang";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($qData);
		if($row==0)
		{
			$tab.="<tr class=rowcontent><td colspan=4>".$_SESSION['lang']['dataempty']."</td></tr>";
		}
		else
		{
			while($rData=$qData->fetch())
			{
				$sSdhi="select sum(jumlahpesan) as sdhi from ".$dbname.". log_po_vw
						where nopp like '%".$_POST['unit']."%' and kodebarang='".$rData['kodebarang']."'
						and substr(tanggal,1,4)='".$_POST['thnAnggaran']."'";
				$rSdhi=$owlPDO->query($sSdhi) or die(print " Gagal: ".PDOException::getMessage());
				$rSdhi->setFetchMode(PDO::FETCH_ASSOC);
				
				$sisaData=$rData['jmlh']-$rSdhi['sdhi'];
				$tab.="<tr class=rowcontent>";
				$tab.="<td>".$optNmBrg[$rData['kodebarang']]."</td>";
				$tab.="<td align=right>".number_format($rSdhi['sdhi'],2)."</td>";
				$tab.="<td align=right>".number_format($rData['jmlh'],2)."</td>";
				$tab.="<td align=right>".number_format($sisaData,2)."</td></tr>";
			}
		}
		$tab.="</tbody></table></fieldset>";
		echo $tab;
	break;
                
	case'cariBarangDlmDtBs':
		$txtfind=$_POST['txtfind'];
		$str="select * from ".$dbname.".log_5masterbarang where left(kodebarang,1) != '9' and (namabarang like '%".$txtfind."%' or kodebarang like '%".$txtfind."%')";
		// $res=$owlPDO->query($str);
		
		if($res=$owlPDO->query($str)){
			echo "<fieldset>
				<legend>Result</legend>
				<div style=\"overflow:auto; max-height:300px;\" >
				<table class=sortable cellspacing=1 cellpadding=2  border=0>
					<thead>
					<tr class=rowheader>
						<td class=firsttd align=center>No.</td>
						<td align=center>".$_SESSION['lang']['kelompokbarang']."</td>
						<td align=center>".$_SESSION['lang']['subkelompokbarang']."</td>
						<td align=center>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td align=center>".$_SESSION['lang']['satuan']."</td>
						<td align=center>".$_SESSION['lang']['saldo']."</td>
					</tr>
					</thead>
					<tbody>";
					
			$no=0;	 
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch()){
				$no+=1;
				//===========================pengambilan saldo
				//ambil saldo barang
				$saldoqty=0;
				$str1="select sum(saldoqty) as saldoqty from ".$dbname.".log_5masterbarangdt where kodebarang='".$bar->kodebarang."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'";
				$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				while($bar1=$res1->fetch()){
					$saldoqty=$bar1->saldoqty;
				}

				//ambil pemasukan barang yang belum di posting
				$qtynotpostedin=0;
				$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' and a.tipetransaksi<5 and a.post=0 group by kodebarang";
				$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_OBJ);
				while($bar2=$res2->fetch()){
					$qtynotpostedin=$bar2->jumlah;
				}
				if($qtynotpostedin=='')
					$qtynotpostedin=0;

				//ambil pengeluaran barang yang belum di posting
				$qtynotposted=0;
				$str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' and a.tipetransaksi>4 and a.post=0 group by kodebarang";
				
				$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_OBJ);
				while($bar2=$res2->fetch()){
					$qtynotposted=$bar2->jumlah;
				}
				if($qtynotposted=='')
					$qtynotposted=0;

				$saldoqty=($saldoqty+$qtynotpostedin)-$qtynotposted;
				//============================================		
				echo "<link rel=stylesheet type=text/css href='style/generic.css'>";
				
				if($bar->inactive==1){
					echo"<tr bgcolor='red' style='cursor:pointer;'  title='Inactive' >";
					$bar->namabarang=$bar->namabarang. " [Inactive]";
					$bgr=" bgcolor='red'";
				}else{				
					echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg('".$bar->kodebarang."','".changeKutipChar($bar->namabarang)."','".$bar->satuan."')\" title='Click' >";
				}
				
				echo "<td class=firsttd  align=center>".$no."</td>
					  <td>".$optNm[substr($bar->kodebarang,0,3)]."</td>
					  <td>".$optsklbrg[substr($bar->kodebarang,0,5)]."</td>
					  <td align=center>".$bar->kodebarang."</td>
					  <td>".$bar->namabarang."</td>
					  <td align=center>".$bar->satuan."</td>
					  <td align=right>".number_format($saldoqty,2,',','.')."</td>
			</tr>";
			}	 
			   
			echo "</tbody>
				<tfoot>
				</tfoot>
				</table></div></fieldset>";
		}else{
			echo " Gagal,".PDOException::getMessage();
		}
	break;
				
	case'formPersetujuan':
		$kd=substr($nopp,17,2);
        $unit=substr($nopp,15,4);
		/*if($kd!='HO')
		{
			$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.applikasi='PP' and a.kodeunit='".$unit."'  order by b.namakaryawan asc";
		}
		else
		{
			$str="select karyawanid,namakaryawan,lokasitugas,bagian from ".$dbname.".`datakaryawan` 
				  where karyawanid!='".$_SESSION['standard']['userid']."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8')
				  and lokasitugas!='' order by namakaryawan asc";
		}*/
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.applikasi='PP1' and a.kodeunit='".$unit."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
		
		$tab="<fieldset style=width:300px;>
			<legend>".$_SESSION['lang']['pengajuan']."</legend>";
		$tab.="<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>:</td>
				<td><input class=myinputtext style=width:165px type=\"text\" id=\"fnopp\" name=\"fnopp\" disabled value='".$nopp."' /></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kepada']."</td>
				<td>:</td>
				<td><select style=width:170px  id=\"karywn_id\" name=\"karywn_id\">". $optKry."</select></td>
			</tr>
			<input type=\"hidden\" id=\"cls_stat\" name=\"cls_stat\" value=0 />
			<tr>
				<td><td><td>
					<button class=mybutton onclick=reset_data_setuju()>".$_SESSION['lang']['cancel']."</button>
					<button class=mybutton onclick=save_persetujuan() >".$_SESSION['lang']['diajukan']."</button>
				</td>
			</tr>
		</table>
		</fieldset>";
		echo $tab;
	break;
		
	default:
	break;	
}
?>