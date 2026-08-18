<?php
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	@$validasiGetMobile = explode(" ", $_GET['par']);
	if($validasiPostMobile[0] == "owlApp" or $validasiGetMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

isset($_POST['nopp']) ? $nopp = $_POST['nopp'] : null;
$kolom= isset($_POST['kolom']) ? $_POST['kolom'] : null;
$kolom_persetujuan='hasilpersetujuan'.$kolom;
isset($_POST['cm_hasil']) ? $comment=$_POST['cm_hasil'] : null;
isset($_POST['jumlah']) ? $jumlah=$_POST['jumlah'] : null;
isset($_POST['kodebarang']) ? $kodebarang=$_POST['kodebarang'] : null;
$karyawanid=checkPostGet('karyawanid', $session_id);
$user_id=checkPostGet('userid', '');
$tglSkrng=date("Y-m-d");
$nmBarang=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

$nmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');

@$explodepp=explode("/",$_POST['nopp']);
@$kdunit=$explodepp[4];
@$tporg=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
@$tpunit=$tporg[$kdunit];

$method = checkPostGet('method','');

##PARAM CARI
$crnopp = checkPostGet('crnopp','');
$crtanggal = checkPostGet('crtanggal','');
$crnamabarang = checkPostGet('crnamabarang','');
$crstatus = checkPostGet('crstatus','');
$crdibuat = checkPostGet('crdibuat','');

$pages = checkPostGet('page','');

$nopp = checkPostGet('nopp','');
$kolom = checkPostGet('kolom','');
$comment = checkPostGet('comment','');
$dibuat = checkPostGet('user_id','');

$kodebarangbaru = checkPostGet('kodebarangbaru','');
$jumlahbaru = checkPostGet('jumlahbaru','');
$hargaposebelumnyalama = checkPostGet('hargaposebelumnyalama','');
$hargaposebelumnyabaru = checkPostGet('hargaposebelumnyabaru','');

$hasil = checkPostGet('stat_hasil','');

$kode_brg = checkPostGet('kode_brg','');
$alsan = checkPostGet('alsan','');

switch ($method)
{
	case 'loaddata':
		$tab = "";
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$where = "";
		if($crnopp!='')
		{
			$where.=" and b.nopp like '%".$crnopp."%'";
		}
		
		if($crtanggal!='')
		{
			$where.=" and b.tanggal = '".tanggalsystem($crtanggal)."'";
		}
		
		if($crnamabarang!='')
		{
			$where.=" and b.nopp in (select x.nopp from ".$dbname.".log_prapodt x 
				left join ".$dbname.".log_5masterbarang y on x.kodebarang=y.kodebarang
				where y.namabarang like '%".$crnamabarang."%')";
		}
		
		if($crstatus!='')
		{
			// $where.=" and tanggal = '".$crstatus."'";
		}
		
		if($crdibuat!='')
		{
			$where.=" and b.dibuat = '".$crdibuat."'";
		}
		
		$str="select count(*) as jmlhrow from ".$dbname.".approval a 
			left join ".$dbname.".log_prapoht b on a.notransaksi = b.nopp
			where a.jenispersetujuan='PR' and a.status='0' and b.close!='2' ".$where."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$jlhbrs= $bar['jmlhrow'];
		
		$no=(($page*$limit));
		$countApp = getCountApproval('PR');
		
		$str="select a.*, b.nopp, b.tanggal, b.ket_balik, b.close from ".$dbname.".approval a 
			left join ".$dbname.".log_prapoht b on a.notransaksi = b.nopp
			where a.jenispersetujuan='PR' and a.status='0' and b.close!='2' ".$where." order by b.tanggal asc limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$kodeorg=substr($bar['nopp'],15,4);
			$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kodeorg."'");
			
			$bgcolor='';
			$kursor='';
			$title='';
			if($bar['ket_balik']!='')
			{
				$bgcolor='bgcolor=orange';
				$kursor='style=cursor:pointer';
				$title="title=\"PP telah di Return oleh dept Purchasing dg alasan : ".$bar['ket_balik']."\" ";
			}
			
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left ".$bgcolor."  ".$title." ".$kursor.">".$bar['nopp']."</td>
				<td align=left>".tanggalnormal($bar['tanggal'])."</td>
				<td align=left>".$kodeorg."</td>
				<td align=center>
					<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\"> &nbsp
					<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"previewDetail('".$bar['nopp']."',event);\">    
				</td>";
				
				$showaction = 0;
				$countubahjumlah = 0;
				$level = 1;
				for($i=1;$i<=$countApp;$i++)
				{
					$arrDetail = detailApprove($i,$bar['nopp'],'PR');
					if($arrDetail['karyawanid']==$karyawanid && ($arrDetail['status']=='' || $arrDetail['status']==0))
					{
						$level = $arrDetail['level'];
						$showaction = 1;
						if($i>=2)
						{
							$countubahjumlah = 1;
						}
					}
				}
				
				if($showaction==1)
				{
					$tab.="<td style='text-align:center'>
						<a href=# onclick=\"get_data_pp('".$bar['nopp']."','".$level."')\">".$_SESSION['lang']['approve']."</a>
					</td>
					<td>
						<a href=# onclick=rejected_pp('".$bar['nopp']."','".$level."') >".$_SESSION['lang']['ditolak']."</a>
					</td>";
					
					if($countubahjumlah==1)
					{
						$tab.="<td>
							<a href=# onclick=\"rejected_some_proses('".$bar['nopp']."','".$level."')\">".$_SESSION['lang']['ditolak_some']."</a>
						</td>
						<td>
							<a href=# onclick=tambahBarang('".$bar['nopp']."','".$level."','".$_SESSION['lang']['find']."',event)>Ubah Jumlah</a>
						</td>";
					}
					else
					{
						$tab.="<td colspan=2>
							<a href=# onclick=\"rejected_some_proses('".$bar['nopp']."','".$level."')\">".$_SESSION['lang']['ditolak_some']."</a>
						</td>";						
					}
				}
				else
				{
					$tab.="<td colspan=4>&nbsp;</td>";
				}
				
				
				for($i=1;$i<=$countApp;$i++)
				{
					$arrDetail = detailApprove($i,$bar['nopp'],'PR');
					
					if($arrDetail['nama']!='')
					{
						$tab.="<td style='text-align:center'><a href=# onclick=cek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
					}
					else
					{
						$tab.="<td style='text-align:center'>-</td>";
					}
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
		
		$frompage = (($page*$limit)+1);
		if((($page+1)*$limit) > $jlhbrs)
		{
			$topage = $jlhbrs;
		}
		else
		{
			$topage = (($page+1)*$limit);
		}
		$tab.="<tr>
			<td colspan='".($countApp+9)."' align=center>
				".$frompage." to ".$topage." Of ".  $jlhbrs."
			</td>
		</tr>
		<tr>
			<td colspan='".($countApp+9)."' align=center>";
		
		if($page=='0')
		{
			$tab.="";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
		}
		
		$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
		
		if(($page+1) == $totrows)
		{
			$tab.="";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
		}
        $tab.="</td></tr>";
			
			
		echo $tab;
		
		
		
		// if(isset($_POST['page']))
		// {
			// $page=$_POST['page'];
			// if($page<0)
			// $page=0;
			// }
			// $offset=$page*$limit;
			// $maxdisplay=($page*$limit);
			
			// if((!empty($_POST['txtSearch']) ? $_POST['txtSearch'] : '') != '')
			// {
				// $where.="and nopp LIKE  '%".$_POST['txtSearch']."%'  ";
			// }
			// elseif((!empty($_POST['tglCari']) ? $_POST['tglCari'] : '') != '')
			// {
				// $where.="and tanggal LIKE '%".(!empty($_POST['tglCari'])? tanggalsystemn($_POST['tglCari']): '')."%' ";
			// }
			// if((!empty($_POST['pembuat']) ? $_POST['pembuat'] : '') != '')
			// {
				// $where.=" and dibuat='".$_POST['pembuat']."'";
			// }
			 // if((!empty($_POST['pembuat']) ? $_POST['pembuat'] : '') != '')
			// {
				// $where.=" and dibuat='".$_POST['pembuat']."'";
			// }
			// if((!empty($_POST['nmbrg']) ? $_POST['nmbrg'] : '') != '')
			// {
				// $where.="and nopp in (select nopp from ".$dbname.".log_prapodt where kodebarang in 
					// (select distinct kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$_POST['nmbrg']."%'))";
			// }
			
			
			// if((!empty($_POST['statusSch']) ? $_POST['statusSch'] : '') == '0')//belum di setujui
			// {
				// $str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					// . " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='0') or "
					// . "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='0') or "
					// . "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='0') or "
					// . "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='0') or "
					// . "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='0') ) 
					// ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,
					// hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";
				 // $sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					// . " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='0') or "
					// . "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='0') or "
					// . "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='0') or "
					// . "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='0') or "
					// . "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='0') ) ";
			// }
			// else if((!empty($_POST['statusSch']) ? $_POST['statusSch'] : '') == '1')
			// {
				// $str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					// . " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='1') or "
					// . "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='1') or "
					// . "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='1') or "
					// . "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='1') or "
					// . "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='1') ) 
					// ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,
					// hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";
				 // $sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					// . " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='1') or "
					// . "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='1') or "
					// . "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='1') or "
					// . "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='1') or "
					// . "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='1') ) ";

			// }
			// else
			// {
				// $str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') 
						 // ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";//echo $str;

				// $sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."')";      
			// }
			
			// /*$str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') 
						 // ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";//echo $str;

			// $sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."')";
			// */
			
		 // // echo $str;
			// $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			// $jlhbrs=$query->rowCount();
			// //$jlhbrs=owlBaris($query);
			
			// if($res=$owlPDO->query($str))
			// {
				// $no=0;
				// $no=$maxdisplay;
				// $res->setFetchMode(PDO::FETCH_ASSOC);
				// while($bar=$res->fetch())
				// {
					// $bgcolor='';
					// $kursor='';
					// $title='';
					// if($bar['ket_balik']!=''){
						// $bgcolor='bgcolor=orange';
						// $kursor='style=cursor:pointer';
						// $title="title=\"PP telah di Return oleh dept Purchasing dg alasan : ".$bar['ket_balik']."\" ";
					// }
					
					// $koderorg=substr($bar['nopp'],15,4);
					// $spr="select kodeorganisasi, namaorganisasi from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; //echo $spr;
					// $rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
					// $rep->setFetchMode(PDO::FETCH_OBJ);
					// $bas=$rep->fetch();
					// $no+=1;
					// echo"<tr class=rowcontent  id='tr_".$no."'>
						// <td align=center>".$no."</td>
						// <td id=td_".$no."  ".$bgcolor."  ".$title." ".$kursor.">".$bar['nopp']."</td>
						// <td>".tanggalnormal($bar['tanggal'])."</td>
						// <td>".$bas->kodeorganisasi."</td>
						// <td align=center>
						// <img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\"> &nbsp
						// <img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"previewDetail('".$bar['nopp']."',event);\">    
						// </td>";      
					// if($bar['close']==2)
					// {
						// $accept=0;
						// for($i=1;$i<6;$i++)
						// {
							// if($bar['hasilpersetujuan'.$i]=='3')
							// {
								// $accept=3;
								// break;
							// }
							// elseif($bar['hasilpersetujuan'.$i]=='1')
							// {
								// $accept=1;

							// }
						// }
						// if($accept==3) {
							// echo"<td colspan=4>".$_SESSION['lang']['ditolak']."</td>";
						// } elseif($accept==1) {
							// echo"<td colspan=4>".$_SESSION['lang']['disetujui']."</td>";
						// }
					// }
					// elseif($bar['close']<2 || $bar['close']=='3')
					// {
						// for($a=1;$a<6;$a++)
						// {
							// if($bar['persetujuan'.$a]!='')
							// {
								// if(($bar['persetujuan'.$a]==$karyawanid)&&(($bar['hasilpersetujuan'.$a]!='')
								// and $bar['hasilpersetujuan'.$a]!=0))
								// {
										// echo"<td colspan=4>&nbsp;</td>";
								// }
								// elseif(($bar['persetujuan'.$a]==$karyawanid)&&($bar['hasilpersetujuan'.$a]=='' 
								// or $bar['hasilpersetujuan'.$a]==0))
								// {
									// echo"
									// <td><a href=# onclick=\"get_data_pp('".$bar['nopp']."','".$a."')\">".$_SESSION['lang']['approve']."</a></td>
									// <td><a href=# onclick=rejected_pp('".$bar['nopp']."','".$a."') >".$_SESSION['lang']['ditolak']."</a></td>
									// <td><a href=# onclick=\"rejected_some_proses('".$bar['nopp']."','".$a."')\">".$_SESSION['lang']['ditolak_some']."</a></td>";
									// if($a>=2) {
										// echo"<td><a href=# onclick=tambahBarang('".$bar['nopp']."','".$a."','".$_SESSION['lang']['find']."',event)>Ubah Jumlah</a></td>";
									// }
									// else
									// {echo"<td></td>";}  
								// }
							// }
						// }
					// }
					// for($i=1;$i<6;$i++)
					// {
						// //echo $bar['hasilpersetujuan'.$i];
						// if(($bar['persetujuan'.$i]!='')||($bar['persetujuan'.$i]!=0))
						// {	
							// $kr=$bar['persetujuan'.$i];
							// $sql="select * from ".$dbname.".datakaryawan where karyawanid='".$kr."'";
							// $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
							// $query->setFetchMode(PDO::FETCH_ASSOC);
							// $yrs=$query->fetch();
							// echo"<td><a href=# onclick=\"cek_status_pp('".$bar['hasilpersetujuan'.$i]."')\">".$yrs['namakaryawan']."</a></td>";
						// }
						// else
						// {
							// echo"<td>&nbsp;</td>";
						// }
					// }				 
					// echo"</tr>";
				// }	 	
				// echo"
					// <tr><td colspan=13 align=center>
					// ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
					// <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					// <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					// </td>
					// </tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />";   	
			// } else {
				// echo " Gagal,".PDOException::getMessage();
			// }
	break;
	
	case'get_form_approval':
		$tab="";
		$koderorg=substr($nopp,15,4);
		
		##GET REQUESTER
		$str="select requester from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res=fetchdata($str);
		$requester=$res[0]['requester'];
		$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$requester."'");
		$departemen=$optdepartmen[$requester];
		 
		

		##GET KODE_PROJECT 
		$str="SELECT  a.kodeproject, b.kode,b.level,b.karyawanid from ".$dbname.".log_prapodt a left join ".$dbname.".project_approval b ON a.kodeproject=b.kode where nopp='".$nopp."'";
		$res=fetchdata($str);
		@$cekaprproject=count($res[0]['kode']);
		$kodeproject=$res[0]['kodeproject']; 
		$idkarPR=$res[0]['karyawanid']; 
		$lvlPR=$res[0]['level']; 
		
		##GET COUNT APPROVAL 
		if (($kodeproject!='')&&($cekaprproject>0)) { 
			$str="select max(level) as level from ".$dbname.".project_approval where kode='".$kodeproject."'"; 
			$res=fetchdata($str);
			$countApp=$res[0]['level'];  
		} else{ 
			$countApp = getCountApproval('PR',$koderorg,$departemen); 
			// $countApp = getCountApproval('PR',$koderorg); 
		}

		
		// echo "ada :".$countApp;
		for($i=1;$i<=$countApp;$i++)
		{
			$arrDetail = detailApprove($i,$nopp,'PR');
			if($arrDetail['status']!='0')continue;
			
			if($karyawanid==$arrDetail['karyawanid'])
			{
				if($i == $countApp)
				{
					$tab.="<div id=approve>
						<fieldset>
						<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
						<table cellspacing=1 border=0>
							<tr>
								<td colspan=3>Submit to Purchasing Dept.</td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['note']."</td>
								<td>:</td>
								<td>
									<input type=text id=note name=note class=myinputtext onClick=\"return tanpa_kutip(event)\" />
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button class=mybutton onclick=close_pp() >".$_SESSION['lang']['ok']."</button>
								</td>
							</tr>
						</table>
						</fieldset>
                    </div>";
				} else {	
					$level = $i+1; 
					
					
					$optnamaapr=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
					$optlokt=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas');
					##AMBIL KODE PROJECT PADA PO_DT
					// $str="select nopp,kodeproject from ".$dbname.".log_prapodt where nopp='".$nopp."'";
					$str="SELECT  a.kodeproject,a.nopp, b.kode,b.level,b.karyawanid from ".$dbname.".log_prapodt a left join ".$dbname.".project_approval b ON a.kodeproject=b.kode where nopp='".$nopp."'";
					$res=fetchdata($str);
					$kodeproject_a=$res[0]['kodeproject'];
					@$cekdata=count($res[0]['kode']);
					
					if (($kodeproject_a!='') && ($cekdata>0)) {
						$strapr="select * from ".$dbname.".project_approval where kode='".$kodeproject_a."' and level='".$level."'";
						$res=fetchdata($strapr); 
					} else { 
						$res = listApprove($level,'PR',$koderorg,$departemen);
					} 
				
					foreach($res as $key=>$val)
					{
						@$optKry.="<option value='".$val['karyawanid']."'>".$optnamaapr[$val['karyawanid']]." [".$optlokt[$val['karyawanid']]."]</option>";
						// $optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
					}
					  
					$tab.="<div id=test style=display:block>
						<fieldset>
                        <legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
                        <table cellspacing=1 border=0>
							<tr>
								<td colspan=3>Submit to the next approval :</td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['namakaryawan']."</td>
								<td>:</td>
								<td valign=top>
									<select id=user_id name=user_id  style=\"width:150px;\">".$optKry."</select>
								</td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['note']."</td>
								<td>:</td>
								<td>
									<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:145px;\" />
								</td>
							</tr>
								<td colspan=2></td>
								<td>
									<button class=mybutton onclick=forward_pp() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
									<button class=mybutton onclick=cancel_pp() title=\" Close this form \">".$_SESSION['lang']['cancel']."</button>
								</td>
							</tr>
						</table> 
                        <input type=hidden name=method id=method  />
                        <input type=hidden name=nopp id=nopp value=".$_POST['nopp']."  /> 
						</fieldset>
					</div>";
				}
				
				// if(($rest['persetujuan5']!='')&&($rest['persetujuan5']!='0'))
				// {
                    // echo"<div id=approve>
                    // <fieldset>
                    // <legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
                    // <table cellspacing=1 border=0>
                    // <tr>
                    // <td colspan=3>
                    // Submit to Purchasing Dept.</td></tr>
                    // <tr>
                    // <td>".$_SESSION['lang']['note']."</td>
                    // <td>:</td>
                    // <td><input type=text id=note name=note class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
                    // </tr>
                    // <tr><td colspan=3 align=center>
                    // <button class=mybutton onclick=close_pp() >".$_SESSION['lang']['ok']."</button></td></tr></table>
                    // </fieldset>
                    // </div>";
                // } 
				// else 
				// {
                    // echo"
                        // <div id=test style=display:block>
                        // <fieldset>
                        // <legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
                        // <table cellspacing=1 border=0>
                        // <tr>
                        // <td colspan=3>
                         // Submit to the next approval :</td>
                        // </tr>
                        // <td>".$_SESSION['lang']['namakaryawan']."</td>
                        // <td>:</td>
                        // <td valign=top>";
                    // $kd=substr($_POST['nopp'],17,2);
                    // $unit=substr($_POST['nopp'],15,4);
                    // $optPur='';
                    
                    // $setujuKe=$i+1;
                    // $str="select a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a"
                            // . " left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid  "
                            // . " where applikasi='PP".$setujuKe."' and kodeunit='".$unit."' and "
                            // . " a.karyawanid!='".$karyawanid."' ";
                    // $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					// $qry->setFetchMode(PDO::FETCH_ASSOC);
                    // while($rkry=$qry->fetch()) {
                        // $optPur.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
                    // }
                    // echo"
                        // <select id=user_id name=user_id  style=\"width:150px;\">
                                // ".$optPur." 
                        // </select></td></tr>
                        // <tr>
                        // <tr>
                        // <td>".$_SESSION['lang']['note']."</td>
                        // <td>:</td>
                        // <td><input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:145px;\" /></td>
                        // </tr>
                        // <td><td><td>
                        // <button class=mybutton onclick=forward_pp() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>

                        // <button class=mybutton onclick=cancel_pp() title=\" Close this form \">".$_SESSION['lang']['cancel']."</button>
                        // </td></tr></table> 
                        // <input type=hidden name=method id=method  /> 
                        // <input type=hidden name=user_id id=user_id value=".$karyawanid." />
                        // <input type=hidden name=nopp id=nopp value=".$_POST['nopp']."  /> 
                        // </fieldset></div>
                        // ";
                    
                    // #hanya persetujuan ke 3 yang bisa ke purch
                    // // if($i>=3 and $optPur=='')
					
					// if($tpunit=='HOLDING'){
						
						// if($i>=2){
							// echo"
							// <div id=approve style=display:block>
							// <fieldset>
							// <legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
							// <table cellspacing=1 border=0>
							// <tr>
							// <td colspan=3>
							 // Approve and submit directly to Purchasing Dept.</td></tr>
							// <tr>
							// <td>".$_SESSION['lang']['note']."t</td>
							// <td>:</td>
							// <td><input type=text id=note name=note class=myinputtext onClick=\"return tanpa_kutip(event)\" style=\"width:190px;\" /></td>
							// </tr>
							// <tr><td colspan=3 align=center>
							// <button class=mybutton onclick=close_pp() title=\"You are agree to this PR and submit it to Purchasing Dept. \"  >".$_SESSION['lang']['kePurchaser']."</button><button class=mybutton onclick=cancel_pp() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button></td></tr></table>
							// </fieldset>
							// </div>
							// ";
						// }
						
					// }else{
						
						// if($i>=4){
							// echo"
							// <div id=approve style=display:block>
							// <fieldset>
							// <legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
							// <table cellspacing=1 border=0>
							// <tr>
							// <td colspan=3>
							 // Approve and submit directly to Purchasing Dept.</td></tr>
							// <tr>
							// <td>".$_SESSION['lang']['note']."t</td>
							// <td>:</td>
							// <td><input type=text id=note name=note class=myinputtext onClick=\"return tanpa_kutip(event)\" style=\"width:190px;\" /></td>
							// </tr>
							// <tr><td colspan=3 align=center>
							// <button class=mybutton onclick=close_pp() title=\"You are agree to this PR and submit it to Purchasing Dept. \"  >".$_SESSION['lang']['kePurchaser']."</button><button class=mybutton onclick=cancel_pp() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button></td></tr></table>
							// </fieldset>
							// </div>
							// ";
						// }
							
					// }
				
                    
                // }
            }
        }
		echo $tab;
	break;
	
	case 'get_form_rejected':
		#lakukan pengecekan apakah ada barang yg sudah di alokasikan ke purchaser
		$str="select count(*) as jumlah from ".$dbname.".log_prapodt where nopp='".$nopp."' and purchaser!=0";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$jumalokasi=$bar['jumlah'];
			
		if($jumalokasi>0)
		{
			exit("Warning : Sudah ada barang yang dialokasikan, harap gunakan <b>Tolak Beberapa</b>");
		}
		else
		{
			$tab.="<div id=rejected_form>
				<fieldset>
				<legend>
					<input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=".$nopp."  />
				</legend>
				<table cellspacing=1 border=0>
					<tr>
						<td colspan=3>PR/SR Rejection form </td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['note']."</td>
						<td>:</td>
						<td>
							<input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" />
						</td>
					</tr>
					<tr>
						<td colspan=3 align=center>
							<button class=mybutton onclick=\"rejected_pp_proses(".$kolom.")\" >".$_SESSION['lang']['ditolak']."</button>
						</td>
					</tr>
				</table>
				</fieldset>
			</div>";
		}
	break;
	
	case 'insert_forward_pp' :
		$hasil_prstjn=1;
		if($comment==''){
			exit("warning : Komentar harus diisi.");
		}
		
		$koderorg=substr($nopp,15,4);
		
		
		##GET REQUESTER
		$str="select requester from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res=fetchdata($str);
		$requester=$res[0]['requester'];
		$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$requester."'");
		$departemen=$optdepartmen[$requester];

		##GET KODE_PROJECT
		// $str="select kodeproject from ".$dbname.".log_prapodt where nopp='".$nopp."'";
		// $res=fetchdata($str);
		// $kodeproject=$res[0]['kodeproject']; 
		$str="SELECT  a.kodeproject,a.nopp, b.kode,b.level,b.karyawanid from ".$dbname.".log_prapodt a left join ".$dbname.".project_approval b ON a.kodeproject=b.kode where nopp='".$nopp."'";
		$res=fetchdata($str);
		$kodeproject_a=$res[0]['kodeproject'];
		@$cekdata=count($res[0]['kode']);
		
		##GET COUNT APPROVAL
		if (($kodeproject_a!='') && ($cekdata>0)) { 
		// if ($kodeproject!='') {
			$str="select max(level) as level from ".$dbname.".project_approval where kode='".$kodeproject_a."'"; 
			$res=fetchdata($str);
			$countApp=$res[0]['level'];
		} else{ 
			$countApp = getCountApproval('PR',$koderorg,$departemen); 
			// $countApp = getCountApproval('PR',$koderorg);
		}
		  
		// exit('error'. $countApp);

		$tglskrng=date("Y-m-d H:i:s");
		
		##GET REQUESTER
		$str = "select requester from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res = fetchdata($str);
		$requester = $res[0]['requester'];
		$optdepartmen = makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$requester."'");
		$departemen = $optdepartmen[$requester];
		
		
		$arrListNotif = listNotif($kolom,'PR',$koderorg,$departemen);
		
		$str="select * from ".$dbname.".log_prapoht where `nopp`='".$nopp."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['close']==2){
			exit("Warning : Sudah di Approved");
		}elseif($bar['close']==1 || $bar['close']==3){ 
			for($i=1;$i<=$countApp;$i++){ 
				$arrDetail = detailApprove($i,$nopp,'PR');
				// $level = $i+1;
				$level = $kolom+1;
				if($i!=$countApp){
					if($user_id==$arrDetail['karyawanid']){
						exit("Warning: ".getNamaKaryawan($user_id)." Sudah di gunakan");			
					}elseif($user_id==$bar['dibuat']){
						exit("Warning: ".getNamaKaryawan($user_id)." Pembuat PP");										
					}else{
						$strx="insert into ".$dbname.".approval values ('','".$nopp."','PR','".$level."','".$user_id."','0','','','')";
						try{
							$owlPDO->exec($strx);
							
							$strx="update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$nopp."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
							
							try{
								$owlPDO->exec($strx);
								// notifemailpr($nopp,'1',$user_id);
								// notifemailpr($nopp,'2',$karyawanid);
								// if(!empty($arrListNotif)){
								// 	foreach($arrListNotif as $key=>$val){
								// 		onlynotifemailpr($nopp,'1',$val['karyawanid']);
								// 	}
								// }
								exit();
							}catch(PDOException $e){
								print " Gagal  !: " . $e->getMessage() . "\n"; 
								die(); 
							}
						}catch(PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							die(); 
						}
					}
				}else{
					$strx="update ".$dbname.".log_prapoht set close='2' where `nopp`='".$nopp."'";
					try{
						$owlPDO->exec($strx);
						
						$strx="update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$nopp."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
						
						try{
							$owlPDO->exec($strx);
							// notifemailpr($nopp,'2',$karyawanid);
							break;
							exit();
						}catch(PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							die(); 
						}
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
			}
		}
	break;
	
	case 'rejected_pp_ex':
		$ardt=0;
		$koderorg=substr($nopp,15,4);
		$countApp = getCountApproval('PR',$koderorg);
		$arrDetail = detailApprove($kolom,$nopp,'PR');
		$tglskrng=date("Y-m-d H:i:s");
		$hasil=3;
		
		$str="update ".$dbname.".log_prapoht set close='".$hasil."' where nopp='".$nopp."'" ;
		try
		{
			$owlPDO->exec($str); 
			
			$str="update ".$dbname.".log_prapodt set status='3',ditolakoleh='".$karyawanid."' where nopp='".$nopp."'";
			try
			{
				$owlPDO->exec($str); 
				$str="update ".$dbname.".approval set status='3', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$nopp."' and level='".$kolom."'";
				try
				{
					$owlPDO->exec($str); 
					$ardt+=1;
				}
				catch(PDOException $e)
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			catch(PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		catch(PDOException $e)
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
		if($ardt!=0)
		{
			$no=0;
			for($i=1;$i<=$countApp;$i++)
			{
				$arrDetail = detailApprove($i,$nopp,'PR');
				if(isset($arrDetail['karyawanid']))
				{
					if($no==0)
					{
						$to=$arrDetail['karyawanid'];
					}
					else
					{
						$to.=",".$arrDetail['karyawanid'];
					}
					$no++;
				}
			}
			
			$str="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			
			#send an email to incharge person
			// $to=getUserEmail($to);
			$namakaryawan=getNamaKaryawan($bar['dibuat']);
			$nmpnlk=getNamaKaryawan($karyawanid);
			
			## CREATE NOTIFICATION
			// notifemailpr($nopp,'3',$karyawanid);
			
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";die(); 
			}
		}
	break;
	
	case 'get_form_rejected_some':
		$tab="";
        $tab.="<fieldset>
			<legend><input class=myinputtext disabled type=text id=rnopp name=rnopp value=".$nopp." readonly=readonly /></legend>
			<div style=overflow:auto;max-width=850px;max-height:350px;>
			<table cellspacing=1 cellpadding=3 border=0 class=sortable max-width=850px>
				<thead class=rowheader>
				<tr>
					<td align=center>No.</td>
					<td align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center>".$_SESSION['lang']['satuan']."</td>
					<td align=center>Prioritas</td>
					<td align=center>".$_SESSION['lang']['jmlhDiminta']."</td>
					<td align=center>".$_SESSION['lang']['tanggalSdt']."</td>
					<td align=center>".$_SESSION['lang']['keterangan']."</td>
					<td align=center>".$_SESSION['lang']['alasanDtolak']."</td>
					<td>Action</td>
				</tr>
				</thead>
				<tbody id=reject_some class=rowcontent>";
		
		$str="select * from ".$dbname.".log_prapodt where `nopp`='".$nopp."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$no+=1;
			$str2="select * from ".$dbname.".log_5masterbarang where `kodebarang`='".$bar['kodebarang']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$bar2=$res2->fetch();
			
			if($bar['status']==3) 
			{
				$dis="disabled=disabled";
                $stadData="checked";
			}
			else 
			{
				$dis="";
                $stadData="";
			}
			
			//diubah agar mengakomodasi retur parsial barang
			if($bar['create_po']==1 || $bar['purchaser']!=0) 
			{
				$disablepo="disabled=disabled";
				$titlepo="title='barang sudah di PO / barang sudah dialokasikan ke purchaser'";
			}
			else 
			{
				$disablepo=""; 
				$titlepo="";
			}
			$optprioritas = makeOption($dbname,'log_5prioritas','kode,nama',"kode='".$bar['prioritas']."'");
			$prioritas = $optprioritas[$bar['prioritas']];
			$tab.="<tr ".$titlepo.">
				<td align=center>".$no."</td>
				<td align=center id=kd_brg_".$no.">".$bar['kodebarang']."</td>
				<td>".$bar2['namabarang']."</td>
				<td align=center>".$bar2['satuan']."</td>
				<td id=kd_angrn_".$no.">".$bar['prioritas']." - ".$prioritas."</td>
				<td align=right id=jmlh_".$no.">".hidezerodecimal($bar['jumlahpp'],2)."</td>
				<td align=center id=tgl_".$no.">".$bar['tgl_sdt']."</td>
				<td id=ket_".$no.">".$bar['keterangan']."</td>
				<td>
					<input style=width:200px type=text id=alsnDtolak_".$no." name=alsnDtolak_".$no." class=myinputtext style=width:100px ".$dis." value='".$bar['alasanstatus']."' ".$disablepo." />
				</td>
				<td align=center><input type=checkbox onclick='checkAlasan(".$no.")' id='tolak_chk_".$no."' ".$stadData." ".$dis." ".$disablepo." /></td>
			</tr>";
        }
		
			$tab.="</tbody>
			<tfoot>
			<tr>
				<td colspan=10 align=center>
					<button class=mybutton onclick=\"rejected_some_done('".$nopp."','".$kolom."','".$no."')\" >".$_SESSION['lang']['done']."</button>
				</td>
			</tr>
			</tfoot>
		</table>
		</div>
			</fieldset><input type=hidden id=user_id name=user_id value='".$karyawanid."'>";
		echo $tab;
	break;
	
	case'tolakBeberapa':
		$koderorg=substr($nopp,15,4);
		$countApp = getCountApproval('PR',$koderorg);
		$arrDetail = detailApprove($kolom,$nopp,'PR');
		$tglskrng=date("Y-m-d H:i:s");
		$adrt=0;

        foreach($kode_brg as $lstKdBrg=>$kdbrg)
        {
			$str="update ".$dbname.".log_prapodt set status=3, alasanstatus='".$alsan[$lstKdBrg]."',ditolakoleh='".$karyawanid."' where kodebarang='".$kdbrg."' and nopp='".$nopp."'";
            try
			{
				$owlPDO->exec($str);
				$adrt+=1;
			}
			catch(PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
        }
		
		if($adrt!=0)
        {
			$no=0;
			for($i=1;$i<=$countApp;$i++)
			{
				$arrDetail = detailApprove($i,$nopp,'PR');
				if(isset($arrDetail['karyawanid']))
				{
					if($no==0)
					{
						$to=$arrDetail['karyawanid'];
					}
					else
					{
						$to.=",".$arrDetail['karyawanid'];
					}
					$no++;
				}
			}
			
			$str="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			
			#send an email to incharge person
            // $to=getUserEmail($to);
            $namakaryawan=getNamaKaryawan($bar['dibuat']);
            $nmpnlk=getNamaKaryawan($karyawanid);
			$itemtolak = "";
            if($_SESSION['language']=='EN')
			{
				$subject="[Notification] Partially or all items on PR No:".$nopp." submitted by ".$namakaryawan." rejected by ".$nmpnlk;
				$body="<html>
						 <head>
						 <body>
						   <dd>Dear Sir/Madam,</dd><br>
						   <br>
							Purchase Request/Service Request No:".$nopp." rejected by [".$nmpnlk."]  corresponding to below notes:
						   <br>
						   Item rejected : <ul>";
				$sBrg="select kodebarang,alasanstatus from ".$dbname.".log_prapodt where nopp='".$_POST['nopp']."' and status='3'";
				$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
				$qBrg->setFetchMode(PDO::FETCH_ASSOC);
                while($rBrg=$qBrg->fetch())
				{
					$body.="<li>".$nmBarang[$rBrg['kodebarang']].", note : ".$rBrg['alasanstatus']."</li>";
					$itemtolak.="\n".$nmBarang[$rBrg['kodebarang']].", alasan : ".$rBrg['alasanstatus'];
				}
				$body.="</ul><br>
					   <br>
					   Regards,<br>
					   Owl-Plantation System.
					 </body>
					 </head>
				   </html>
				   ";
			}
			else
			{
				$subject="[Notifikasi] Sebagian atau Seluruhnya PR/SR No :".$nopp." dari ".$namakaryawan." ditolak oleh ".$nmpnlk;
                $body="<html>
				 <head>
				 <body>
				   <dd>Dengan Hormat,</dd><br>
				   <br>
				   Permintaan pembelian no.".$nopp." ditolak oleh [".$nmpnlk."] dengan alasan.
				   <br>
				   Item yang ditolak adalah : <ul>";
				   
				$sBrg="select kodebarang,alasanstatus from ".$dbname.".log_prapodt where nopp='".$nopp."' and status='3'";
				$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
				$qBrg->setFetchMode(PDO::FETCH_ASSOC);
                while($rBrg=$qBrg->fetch())
                {
					$body.="<li>".$nmBarang[$rBrg['kodebarang']].", alasan : ".$rBrg['alasanstatus']."</li>";
					$itemtolak.="\n".$nmBarang[$rBrg['kodebarang']].", alasan : ".$rBrg['alasanstatus'];
				}
				$body.="</ul><br>
                <br>
                Regards,<br>
                Owl-Plantation System.
                </body>
                </head>
                </html>";                         
			}
			
			## CREATE NOTIFICATION
			$msgdt = "PR/SR dengan No ".$nopp." ditolak sebagian item oleh [".$nmpnlk."]\nItem yang ditolak adalah : ".$itemtolak;
			$str="insert into ".$dbname.".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('".$nopp."','TSPR','".$msgdt."','".$bar['dibuat']."','0','0','".date('Y-m-d H:i:s')."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";die(); 
			}
		}
	break;
	
	case'getListBarangBaru':
		$tab="";
		$namaBarangCariBaru=$_POST['namaBarangCariBaru'];
		$nourut=$_POST['nourut'];
		
		$tab.="<fieldset>
			<table cellspacing=1 border=0 class=data width=100%>
				<tr>
					<td>".$_SESSION['lang']['namabarang']."</td>
					<td>:</td>
					<td colspan=5>
						<input type=text id=namaBarangCariBaru  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
						<button class=mybutton onclick=cariListBarangBaru('".$nourut."')>cari</button>
					</td>
				<tr>
			</table>
			
			<table id=listCariBarangBaru cellspacing=1 border=0 class=sortable width=100%>
				<thead>
				<tr class=rowheader>
					<td align=center>No</td>
					<td align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center>".$_SESSION['lang']['satuan']."</td>
					<td align=center width=50px>Harga PO sebelumnya</td>
				</tr>
				</thead>";
		
		if($namaBarangCariBaru=='') 
		{
			
		} 
		else 
		{
			$i="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCariBaru."%'";
			$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
			$n->setFetchMode(PDO::FETCH_ASSOC);
			while ($d=$n->fetch())
			{
				$no+=1;
				$iPo="select * from ".$dbname.".log_podt where kodebarang='".$d['kodebarang']."' order by nopo desc ";
				$nPo=$owlPDO->query($iPo) or die(print " Gagal: ".PDOException::getMessage());
				$nPo->setFetchMode(PDO::FETCH_ASSOC);
				$dPo=$nPo->fetch();
				
				$whBrg="kodebarang='".$d['kodebarang']."'";
				
				$tab.="<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$d['kodebarang']."','".$nmBrg[$d['kodebarang']]."','".$satBrg[$d['kodebarang']]."','".$dPo['hargasatuan']."','".$nourut."');\">
					<td align=center>".$no."</td>
					<td align=center>".$d['kodebarang']."</td>
					<td>".$nmBrg[$d['kodebarang']]."</td>
					<td>".$satBrg[$d['kodebarang']]."</td>
					<td align=right>".number_format($dPo['hargasatuan'])."</td>
				</tr>";
			}
		}
		$tab.="</table>
        </fieldset>";
		
		echo $tab;
	break;
	
	case'saveFormBarang':
		if($kodebarangbaru=='' || $jumlahbaru=='')
		{
			exit("Warning : Kode barang dan jumlah tidak boleh kosong !");
		}
		else if($kodebarangbaru!='')
		{
			$str="update ".$dbname.".log_prapodt set kodebarang='".$kodebarangbaru."',jumlah='".$jumlahbaru."',updateby='".$karyawanid."' where nopp='".$nopp."' and kodebarang='".$kodebarang."' ";
		}
		else 
		{
			$str="update ".$dbname.".log_prapodt set jumlah='".$jumlahbaru."',updateby='".$karyawanid."' where nopp='".$nopp."' and kodebarang='".$kodebarang."' ";
		}
		
		try
		{
			$owlPDO->exec($str); 
		}
		catch(PDOException $e)
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case 'insert_close_pp':
		$koderorg=substr($nopp,15,4);
		$countApp = getCountApproval('PR',$koderorg);
		$arrDetail = detailApprove($kolom,$nopp,'PR');
		$tglskrng=date("Y-m-d H:i:s");
		
		if($karyawanid==$arrDetail['karyawanid'])
		{
			$str="update ".$dbname.".log_prapoht set close=2 where nopp='".$nopp."'";
			
			try
			{
				$owlPDO->exec($str);
				
				$str="update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$nopp."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				try
				{
					$owlPDO->exec($str);
					
					#update dtnya jika ada create_po=3 (untuk menampung retur parsial) dikembalikan menjadi 0
					$str="update ".$dbname.".log_prapodt set create_po=0 where nopp='".$nopp."'";
					try
					{
						$owlPDO->exec($str);
						// notifemailpr($nopp,'2',$karyawanid);
					}
					catch(PDOException $e)
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				catch(PDOException $e)
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			catch(PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		else
		{
			echo "Warning: Anda tidak memiliki autorisasi untuk No PP Ini";
			exit();
		}
	break;
	
	case'getListBarang':
		$tab="";
		$tab.="<fieldset>
			<legend><input class=myinputtext disabled type=text value=".$nopp."></legend>
			<table cellspacing=1 border=0 class=sortable width=100%>
				<thead>
				<tr class=rowheader>
					<td align=center rowspan=2>No</td>
					<td align=center rowspan=2>".$_SESSION['lang']['nopp']."</td>
					<td align=center  colspan=4>Data Lama / Old Data</td>
					<td align=center rowspan=2>Jumlah<br>Baru</td>
					<td  align=center rowspan=2>".$_SESSION['lang']['action']."</td>
				</tr>            
				<tr>
					<td align=center width=50px>".$_SESSION['lang']['kodebarang']."</td>
					<td align=center>".$_SESSION['lang']['namabarang']."</td>
					<td align=center width=30px>".$_SESSION['lang']['satuan']."</td>    
					<td align=center>".$_SESSION['lang']['jumlah']."</td>   
					<td style=display:none align=center width=50px>Harga PO Sebelumnya</td> 
					<td style=display:none align=center>".$_SESSION['lang']['kodebarang']."</td>
					<td style=display:none align=center>".$_SESSION['lang']['namabarang']."</td>   
					<td style=display:none align=center>".$_SESSION['lang']['satuan']."</td>
				<td style=display:none align=center width=50px>".$_SESSION['lang']['harga']."</td> 	
			</tr>
			</thead>
			</tbody>";
			
		$str="select * from ".$dbname.".log_prapodt where nopp='".$nopp."' and status!='3' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while ($bar=$res->fetch())
		{
			$strpo="select * from ".$barbname.".log_podt where kodebarang='".$bar['kodebarang']."' and nopp='".$nopp."' 
			order by nopo desc limit 1 ";
			$respo=$owlPDO->query($strpo) or die(print " Gagal: ".PDOException::getMessage());
			$respo->setFetchMode(PDO::FETCH_ASSOC);
			$barpo = $respo->fetch();
			
			if($bar['hargalama']==0 || $bar['hargalama']=='')
			{
				$hargalama=$barpo['hargasatuan'];
			} 
			else 
			{
				$hargalama=$bar['hargalama'];
			}
			
			$ket=$barisabled=$hide='';
			if($bar['purchaser']!='0')
			{
				$hide='hidden';
				$barisabled='disabled';
				$ket='Sudah dialokasi purchaser';				
			}
			
			$no+=1;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$bar['nopp']."</td>
				<td>".$bar['kodebarang']."</td>
				<td>".$nmBrg[$bar['kodebarang']]."</td>
				<td>".$satBrg[$bar['kodebarang']]."</td>  
				<td><input type=text disabled id=jumlah".$no." value=".$bar['jumlah']." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:40px;\"></td>    
				
				<td  style=display:none  >
				<input type=text id=hargaposebelumnyalama".$no." disabled value='".$hargalama."' onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:70px;\">
				</td>
				
				<td style=display:none><input disabled type=text value='".$bar['kodebarang']."' id=kodebarangbaru".$no."  onkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber style=\"width:65px;\">
				</td>
				
				<td style=display:none><input disabled type=text value='".$nmBrg[$bar['kodebarang']]."' id=namabarangbaru".$no." onkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber style=\"width:150px;\"></td>
				
				<td style=display:none><input disabled type=text value='".$satBrg[$bar['kodebarang']]."' id=satuanbarangbaru".$no." onkeypress=\"return tanpa_kutip(event);\" class=myinputtextnumber style=\"width:50px;\"></td>
				
				<td><input ".$barisabled." type=text id=jumlahbaru".$no."  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:40px;\"></td>
				
				<td   style=display:none ><input type=text id=hargaposebelumnyabaru".$no." onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:65px;\"></td>
				<td>
					<button ".$hide." class=mybutton onclick=saveFormBarang('".$bar['nopp']."','".$bar['kodebarang']."',".$no.")>Simpan</button>
					".$ket."
				</td>
			</tr>";
		}
		$tab.="</table></fieldset>";
		echo $tab;
	break;
	
        case 'rejected_some_input' :
			$nopp=$_POST['nopp'];
			$kode_brg=$_POST['kd_brg'];
			$user_id=$_POST['user_id'];
			$alsnDtolak=$_POST['alsnDtolk'];
			$where=" nopp='".$nopp."' and kodebarang='".$kode_brg."'";
			$sCek="select status from ".$dbname.".log_prapodt where nopp='".$nopp."' and status='0' ";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$rCek=owlBaris($qCek);
			if($rCek>1)
			{
				$sql="select * from ".$dbname.".log_prapodt where".$where;
				$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
				$query->setFetchMode(PDO::FETCH_ASSOC);
				$res=$query->fetch();
				
				if(($res['status']=='0')&&($res['ditolakoleh']==0000000000 or $res['ditolakoleh']==''))
				{
					$sql2="update ".$dbname.".log_prapodt set status='3',ditolakoleh='".$user_id."',alasanstatus='".$alsnDtolak."' where".$where;
					
					try{
						$owlPDO->exec($sql2); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				else
				{
					echo"warning: Already Fill";
					exit();
				}
			}
			else
			{	
				echo"warning:Item Barang Hanya Satu";
				exit();
			}
        break;
        

		case'data_refresh':
            
            //exit("Error:MASUK");
            
			$limit=20;
			$page=0;
			if(isset($_POST['page']))
			{
			$page=$_POST['page'];
			if($page<0)
			$page=0;
			}
			$offset=$page*$limit;
			$maxdisplay=($page*$limit);
			
			if((!empty($_POST['txtSearch']) ? $_POST['txtSearch'] : '') != '')
			{
				$where.="and nopp LIKE  '%".$_POST['txtSearch']."%'  ";
			}
			elseif((!empty($_POST['tglCari']) ? $_POST['tglCari'] : '') != '')
			{
				$where.="and tanggal LIKE '%".(!empty($_POST['tglCari'])? tanggalsystemn($_POST['tglCari']): '')."%' ";
			}
			if((!empty($_POST['pembuat']) ? $_POST['pembuat'] : '') != '')
			{
				$where.=" and dibuat='".$_POST['pembuat']."'";
			}
			 if((!empty($_POST['pembuat']) ? $_POST['pembuat'] : '') != '')
			{
				$where.=" and dibuat='".$_POST['pembuat']."'";
			}
			if((!empty($_POST['nmbrg']) ? $_POST['nmbrg'] : '') != '')
			{
				$where.="and nopp in (select nopp from ".$dbname.".log_prapodt where kodebarang in 
					(select distinct kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$_POST['nmbrg']."%'))";
			}
			
			//echo $where;
			
			/*if($_SESSION['org']['tipeinduk']=='HOLDING')
			{
					//close = '1'
			
					$str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') 
						 ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";//echo $str;

					$sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."')";
			} else {
					//close = '1'
					$str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') 
						  ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";
					$sql="SELECT count(*) as jmlhrow FROM  ".$dbname.".log_prapoht where close!=2 ".$where." and  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') ";
			}*/
		  
			if((!empty($_POST['statusSch']) ? $_POST['statusSch'] : '') == '0')//belum di setujui
			{
				$str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					. " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='0') or "
					. "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='0') or "
					. "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='0') or "
					. "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='0') or "
					. "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='0') ) 
					ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,
					hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";
				 $sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					. " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='0') or "
					. "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='0') or "
					. "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='0') or "
					. "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='0') or "
					. "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='0') ) ";
			}
			else if((!empty($_POST['statusSch']) ? $_POST['statusSch'] : '') == '1')
			{
				$str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					. " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='1') or "
					. "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='1') or "
					. "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='1') or "
					. "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='1') or "
					. "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='1') ) 
					ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,
					hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";
				 $sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and "
					. " ( (persetujuan1='".$karyawanid."' and hasilpersetujuan1='1') or "
					. "   (persetujuan2='".$karyawanid."' and hasilpersetujuan2='1') or "
					. "   (persetujuan3='".$karyawanid."' and hasilpersetujuan3='1') or "
					. "   (persetujuan4='".$karyawanid."' and hasilpersetujuan4='1') or "
					. "   (persetujuan5='".$karyawanid."' and hasilpersetujuan5='1') ) ";

			}
			else
			{
				$str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') 
						 ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";//echo $str;

				$sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."')";      
			}
			
			/*$str="SELECT * FROM ".$dbname.".log_prapoht where close!=2 ".$where." and (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') 
						 ORDER BY hasilpersetujuan1,hasilpersetujuan2,persetujuan3,hasilpersetujuan4,hasilpersetujuan5 ASC LIMIT ".$offset.",".$limit."";//echo $str;

			$sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where close!=2 ".$where." and  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."')";
			*/
			
		 // echo $str;
			$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$jlhbrs=$query->rowCount();
			//$jlhbrs=owlBaris($query);
			
			if($res=$owlPDO->query($str))
			{
				$no=0;
				$no=$maxdisplay;
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch())
				{
					$bgcolor='';
					$kursor='';
					$title='';
					if($bar['ket_balik']!=''){
						$bgcolor='bgcolor=orange';
						$kursor='style=cursor:pointer';
						$title="title=\"PP telah di Return oleh dept Purchasing dg alasan : ".$bar['ket_balik']."\" ";
					}
					
					$koderorg=substr($bar['nopp'],15,4);
					$spr="select kodeorganisasi, namaorganisasi from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; //echo $spr;
					$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
					$rep->setFetchMode(PDO::FETCH_OBJ);
					$bas=$rep->fetch();
					$no+=1;
					echo"<tr class=rowcontent  id='tr_".$no."'>
						<td align=center>".$no."</td>
						<td id=td_".$no."  ".$bgcolor."  ".$title." ".$kursor.">".$bar['nopp']."</td>
						<td>".tanggalnormal($bar['tanggal'])."</td>
						<td>".$bas->kodeorganisasi."</td>
						<td align=center>
						<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\"> &nbsp
						<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"previewDetail('".$bar['nopp']."',event);\">    
						</td>";      
					if($bar['close']==2)
					{
						$accept=0;
						for($i=1;$i<6;$i++)
						{
							if($bar['hasilpersetujuan'.$i]=='3')
							{
								$accept=3;
								break;
							}
							elseif($bar['hasilpersetujuan'.$i]=='1')
							{
								$accept=1;

							}
						}
						if($accept==3) {
							echo"<td colspan=4>".$_SESSION['lang']['ditolak']."</td>";
						} elseif($accept==1) {
							echo"<td colspan=4>".$_SESSION['lang']['disetujui']."</td>";
						}
					}
					elseif($bar['close']<2 || $bar['close']=='3')
					{
						for($a=1;$a<6;$a++)
						{
							if($bar['persetujuan'.$a]!='')
							{
								if(($bar['persetujuan'.$a]==$karyawanid)&&(($bar['hasilpersetujuan'.$a]!='')
								and $bar['hasilpersetujuan'.$a]!=0))
								{
										echo"<td colspan=4>&nbsp;</td>";
								}
								elseif(($bar['persetujuan'.$a]==$karyawanid)&&($bar['hasilpersetujuan'.$a]=='' 
								or $bar['hasilpersetujuan'.$a]==0))
								{
									echo"
									<td><a href=# onclick=\"get_data_pp('".$bar['nopp']."','".$a."')\">".$_SESSION['lang']['approve']."</a></td>
									<td><a href=# onclick=rejected_pp('".$bar['nopp']."','".$a."') >".$_SESSION['lang']['ditolak']."</a></td>
									<td><a href=# onclick=\"rejected_some_proses('".$bar['nopp']."','".$a."')\">".$_SESSION['lang']['ditolak_some']."</a></td>";
									if($a>=2) {
										echo"<td><a href=# onclick=tambahBarang('".$bar['nopp']."','".$a."','".$_SESSION['lang']['find']."',event)>Ubah Jumlah</a></td>";
									}
									else
									{echo"<td></td>";}  
								}
							}
						}
					}
					for($i=1;$i<6;$i++)
					{
						//echo $bar['hasilpersetujuan'.$i];
						if(($bar['persetujuan'.$i]!='')||($bar['persetujuan'.$i]!=0))
						{	
							$kr=$bar['persetujuan'.$i];
							$sql="select * from ".$dbname.".datakaryawan where karyawanid='".$kr."'";
							$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
							$query->setFetchMode(PDO::FETCH_ASSOC);
							$yrs=$query->fetch();
							echo"<td><a href=# onclick=\"cek_status_pp('".$bar['hasilpersetujuan'.$i]."')\">".$yrs['namakaryawan']."</a></td>";
						}
						else
						{
							echo"<td>&nbsp;</td>";
						}
					}				 
					echo"</tr>";
				}	 	
				echo"
					<tr><td colspan=13 align=center>
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
					<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					</td>
					</tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />";   	
			} else {
				echo " Gagal,".PDOException::getMessage();
			}
        break;
		
        case 'data_refresh2':
        $limit=10;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;
          if($_SESSION['empl']['tipeinduk']=='HOLDING')
            {
                        //close = '1'
                    $str="SELECT * FROM ".$dbname.".log_prapoht where  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') ORDER BY `tanggal` DESC LIMIT ".$offset.",".$limit."";//echo $str;
                                        $sql="SELECT count(*) as jmlhrow FROM ".$dbname.".log_prapoht where  close!='2'and substring(nopp,16,4)='".$_SESSION['empl']['lokasitugas']."'  and (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') ORDER BY `tanggal` DESC";

            }
            else
            {
                        //close = '1'
                    $str="SELECT * FROM ".$dbname.".log_prapoht where  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') ORDER BY `tanggal` DESC";
                                        $sql="SELECT count(*) as jmlhrow FROM  ".$dbname.".log_prapoht where  (persetujuan1='".$karyawanid."' or persetujuan2='".$karyawanid."' or persetujuan3='".$karyawanid."' or persetujuan4='".$karyawanid."' or persetujuan5='".$karyawanid."') ORDER BY `tanggal` DESC";
            }
              
					$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
					$jlhbrs=owlBaris($query);
          
		  if($res=$owlPDO->query($str))
          {
				$res->setFetchMode(PDO::FETCH_ASSOC);
                while($bar=$res->fetch())
                {
					$bgcolor='';
					$titel='';
					$kursor='';
					if($bar['ket_balik']!=''){
						$bgcolor='bgcolor=orange';
						$kursor='style=cursor:pointer';
						$title="title=\"PP telah di Return oleh dept Purchasing dg alasan : ".$bar['ket_balik']."\" ";
					}
					
                        $koderorg=substr($bar['nopp'],15,4);
                        $spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; //echo $spr;
						$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
						$rep->setFetchMode(PDO::FETCH_OBJ);
                        $bas=$rep->fetch();
                        $no+=1;
                        echo"<tr class=rowcontent  id='tr_".$no."'>
                                  <td>".$no."</td>
                                  <td id=td_".$no." ".$bgcolor." ".$title." ".$kursor.">".$bar['nopp']."</td>
                                  <td>".tanggalnormal($bar['tanggal'])."</td>
                                  <td>".$bas->namaorganisasi."</td>
                                  <td align=center><img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_prapoht','".$bar['nopp']."','','log_slave_print_log_pp',event);\">
                                  <img src=images/zoom.png class=resicon  height='30' title='Preview' onclick=\"previewDetail('".$bar['nopp']."',event);\">     
                                  </td>";                            
                                    for ($a=1;$a<6;$a++)
                                    {	
                                        if($bar['close']==2)
                                        {
                                            if($bar['hasilpersetujuan'.$a]=='3')
                                            {
                                                    //echo"<td colspan=3>".$_SESSION['lang']['ditolak']."</td>";
                                                    $abc=3;
                                            }
                                            elseif($bar['hasilpersetujuan'.$a]=='1')
                                            {
                                                    //echo"<td colspan=3>".$_SESSION['lang']['ditolak']."</td>";
                                                    $abc=1;
                                            }
                                        }
                                        elseif($bar['close']<2)
                                        {
                                            if($bar['persetujuan'.$a]!='')
                                            {
                                                if(($bar['persetujuan'.$a]==$karyawanid)&&(($bar['hasilpersetujuan'.$a]!='')
                                                and $bar['hasilpersetujuan'.$a]!=0))
                                                 {
                                                  echo"<td colspan=3>&nbsp;</td>";
                                                 }
                                                  elseif(($bar['persetujuan'.$a]==$karyawanid)&&($bar['hasilpersetujuan'.$a]=='' 
                                                 or $bar['hasilpersetujuan'.$a]==0))
                                                 {
                                                        echo"
                                                   <td><a href=# onclick=\"get_data_pp('".$bar['nopp']."','".$a."')\">".$_SESSION['lang']['approve']."</a></td>
                                                        <td><a href=# onclick=rejected_pp('".$bar['nopp']."','".$a."') >".$_SESSION['lang']['ditolak']."</a></td>
                                                        <td><a href=# onclick=\"rejected_some_proses('".$bar['nopp']."','".$a."')\" >".$_SESSION['lang']['ditolak_some']."</a></td>
                                                        ";
                                                    if($a>=2)
                                                    {
                                                        echo"<td><a href=# onclick=tambahBarang('".$bar['nopp']."','".$a."','".$_SESSION['lang']['find']."',event)>Ubah Jumlah dan Harga</a></td>";
                                                    }
                                                    else
                                                    {echo"<td></td>";}  
                                                 }
                                            }
                                        }
                                     }
                                     if($abc!='')
                                     {
                                             if($abc==3)
                                             {
                                                     echo"<td colspan=3>".$_SESSION['lang']['ditolak']."</td>";
                                             }
                                             elseif($abc==1)
                                             {
                                                    echo"<td colspan=3>".$_SESSION['lang']['approve']."</td>";
                                             }
                                     }

                                 for($i=1;$i<6;$i++)
                                 {
                                        //echo $bar['hasilpersetujuan'.$i];
                                        if($bar['persetujuan'.$i]!='')
                                        {	
                                                $kr=$bar['persetujuan'.$i];
                                                $sql="select * from ".$dbname.".datakaryawan where karyawanid='".$kr."'";
												$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
												$query->setFetchMode(PDO::FETCH_ASSOC);
                                                $yrs=$query->fetch();

                                                echo"<td><a href=# onclick=\"cek_status_pp('".$bar['hasilpersetujuan'.$i]."')\">".$yrs['namakaryawan']."</a></td>";
                                        }
                                        else
                                        {
                                                echo"<td>&nbsp;</td>";
                                        }
                                 }
                                 echo"</tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />";
                }	
                echo"
                                 <tr><td colspan=13 align=center>
                                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                                </td>
                                </tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />";  	   	
          }	
          else
                {
                        echo " Gagal,".PDOException::getMessage();
                }	

        break;

        default:
        break;
        }
//========================================================================	
function mailCoy($userid)
{
 #send an email to incharge person
    $to=getUserEmail($userid);
    $namakaryawan=getNamaKaryawan($karyawanid);
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
                               <dd>Dengan Hormat,</dd><br>
                               <br>
                               Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Permintaan Pembelian Barang
                               kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
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
}        
?>
