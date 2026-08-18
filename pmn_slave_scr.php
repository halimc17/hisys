<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');

$notransaksi = checkPostGet('notransaksi', '');
$pt = checkPostGet('pt', '');
$tanggal = checkPostGet('tanggal', '');
$buyer = checkPostGet('buyer', '');
$scn = checkPostGet('scn', '');
$berikat = checkPostGet('berikat', '');
$komoditi = checkPostGet('komoditi', '');
$kuantitas = checkPostGet('kuantitas', '');
$harga = checkPostGet('harga', '');
$ppn = checkPostGet('ppn', '');
$tanggalbayar = checkPostGet('tanggalbayar', '');
$bayarke = checkPostGet('bayarke', '');
$kualitas1 = checkPostGet('kualitas1', '');
$kualitas2 = checkPostGet('kualitas2', '');
$kualitas3 = checkPostGet('kualitas3', '');
$kualitas4 = checkPostGet('kualitas4', '');
$pages = checkPostGet('page', '');

$caript = checkPostGet('caript', '');
$caritanggal = checkPostGet('caritanggal', '');
$carinotransaksi = checkPostGet('carinotransaksi', '');

$tipeApp = "SCR";
$jenisApp = "SCR";

switch($proses)
{
	case'getkomoditi':
		if($_POST['isipros']=='update'){
			$sdata="select * from ".$dbname.".pmn_scr where notransaksi='".$_POST['notransaksi']."'";
			//exit('warning:'.$sdata);
			$rdata=fetchData($sdata);
			$kualitas2=$rdata[0]['kualitas2'];
			$kualitas1=$rdata[0]['kualitas1'];
			$kualitas3=$rdata[0]['kualitas3'];
			$kualitas4=$rdata[0]['kualitas4'];
			if($komoditi=='40000003'){
				$kualitas1=$rdata[0]['nokontrak_tbs'];
				$kualitas2=$rdata[0]['jangkawaktu_tbs'];
			}
		}
		$tab="";
		$klass="class='myinputtextnumber' onkeypress='return angka_doang(event)'";
		if($komoditi=='40000001')
		{
			$lbl1 = "FFA";
			$lbl2 = "M & I";
			$lbl3 = "Dirt";
			$style1=$style2=$style3 = "style='display:'";
			$style4 = "style='display:none'";
			$sat="%";
		}
		else if($komoditi=='40000002')
		{
			$lbl1 = "Broken";
			$lbl2 = "Moisture";
			$lbl3 = "Dirt";
			$lbl4 = "FFA";
			$style1=$style2=$style3 = "style='display:'";
			$sat="%";
		}
		else if($komoditi=='40000003'){
			
			$lbl1 = "Contract No.";
			$lbl2 = "Period of Transaction";
			$lbl3 = "";
			$lbl4 = "";
			$style1=$style2= "style='display:'";
			$style3=$style4 = "style='display:none'";
			$sat="";
			$klass="class='myinputtext' onkeypress='return tanpa_kutip(event)'";
			$kualitas3=0;
		}
		else
		{
			$lbl1 = "";
			$lbl2 = "";
			$lbl3 = "";
			$lbl4 = "";
			$style1=$style2=$style3=$style4 = "style='display:none'";
		}
		
		$tab="<tr ".$style1.">
			<td>".$lbl1."</td>
			<td>:</td>
			<td>
				<input  type='text' ".$klass." id='kualitas1' style='width:80px;'  placeholder='".$lbl1."' value='".$kualitas1."' /> ".$sat."
			</td>
		</tr>
		<tr ".$style2.">
			<td>".$lbl2."</td>
			<td>:</td>
			<td>
				<input  type='text' ".$klass." id='kualitas2' style='width:80px;'   placeholder='".$lbl2."' value='".$kualitas2."' /> ".$sat."
			</td>
		</tr>
		<tr ".$style3.">
			<td>".$lbl3."</td>
			<td>:</td>
			<td>
				<input  type='text' ".$klass." id='kualitas3' style='width:80px;' placeholder='".$lbl3."'  value='".$kualitas3."' /> ".$sat."
			</td>
		</tr>
		<tr ".$style4.">
			<td>".$lbl4."</td>
			<td>:</td>
			<td>
				<input  type='text' ".$klass." id='kualitas4' style='width:80px;' placeholder='".$lbl4."'  value='".$kualitas4."' /> ".$sat."
			</td>
		</tr>";
		
		echo $tab;
	break;
	
	case'getapproval':
		$tab="";
		
		$countApp = getCountApproval($tipeApp,$pt);

		for($i=1;$i<=$countApp;$i++)
		{
			$optpersetujuan="";
			$arrDetail = detailApprove($i,$notransaksi,$tipeApp);
			$listApp = listApprove($i,$tipeApp,$pt);
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
		$optNamabank=makeOption($dbname,"keu_5daftarbank","kodebank,namabank");
		#rekening bank
		$optByrke="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".keu_5akunbank where pemilik='".$pt."' order by namabank";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($_POST['norek']==$bar['noakun']){
				$optByrke.="<option value='".$bar['noakun']."' selected>".$bar['pemilik'].":".$optNamabank[$bar['namabank']]." ".$bar['rekening']."</option>";
			}else{
				$optByrke.="<option value='".$bar['noakun']."'>".$bar['pemilik'].":".$optNamabank[$bar['namabank']]." ".$bar['rekening']."</option>";	
			}
			
		}


		echo $tab.'####'.$optByrke;
	break;
	
	case'loadData':
		$where = "";
		//Inisialisasi Search
		if($caript!='')
		{
            $where.=" and kodeorg = '".$caript."'";
        }
		
		if($carinotransaksi!='')
		{
            $where.=" and notransaksi like '%".$carinotransaksi."%'";
        }
		
		if($caritanggal!='')
		{
			$caritanggal = substr($caritanggal,6,4)."-".substr($caritanggal,3,2)."-".substr($caritanggal,0,2);
			$where.=" and tanggal like '".$caritanggal."%'";
        }
	
		$limit=10;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
        
		$str="select count(*) jmlhrow from ".$dbname.".pmn_scr where 1=1 ".$where."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$jlhbrs= $bar['jmlhrow'];

		$countApp = getCountApproval($jenisApp,'');
		$tab='';
		$nor=0;
		
		$str="select * from ".$dbname.".pmn_scr where 1=1 ".$where." order by tanggal desc limit ".$offset.",".$limit." ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$nor+=1;
			
			$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$optBuyer = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar['buyer']."'");
			$optKomoditi = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['komoditi']."'");
			$optKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			
			$strx="select * from ".$dbname.".pmn_kontrakjual where nokontrak_ref='".$bar['notransaksi']."'";
			$resx=fetchData($strx);
			$nokontrak_ref = $resx[0]['nokontrak'];
			
			$tab.="<tr class=rowcontent>
				<td id='nor_".$nor."' align=center value='".$nor."'>".$bar['notransaksi']."</td>
				<td>".$optUnit[$bar['kodeorg']]."</td>
				<td>".$optBuyer[$bar['buyer']]."</td>
				<td style='text-align:center'>".$nokontrak_ref."</td>
				<td style='text-align:center'>".tanggalnormal($bar['tanggal'])."</td>
				<td>".$optKomoditi[$bar['komoditi']]."</td>
				<td>".$optKaryawan[$bar['updateby']]."</td>";
				
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
			
			if($bar['status']=='0')
			{
				$tab.="<td style='text-align:center'>Created</td>";
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_edit.png class=zImgBtn title='edit' onclick=\"editscr('".$bar['notransaksi']."','".$bar['kodeorg']."','".tanggalnormal($bar['tanggal'])."','".$bar['buyer']."','".$bar['scn']."','".$bar['berikat']."','".$bar['komoditi']."','".$bar['kuantitas']."','".$bar['harga']."','".$bar['ppn']."','".tanggalnormal($bar['paymentdate'])."','".$bar['bayarke']."','".$bar['kualitas1']."','".$bar['kualitas2']."','".$bar['kualitas3']."','".$bar['kualitas4']."','".$bar['nokontrak_tbs']."','".$bar['jangkawaktu_tbs']."');\">
				</td>";
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_delete.png class=zImgBtn title='delete' onclick=\"deletescr('".$bar['notransaksi']."');\">
				</td>";
				$tab.="<td style='text-align:center'>
					<img src=images/icons/04/16/09.png class=zImgBtn title='Submitted' onclick=\"postingscr('".$bar['notransaksi']."');\">
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
		$tglCtr = explode('-',$tanggal);
		$tahunCtr = $tglCtr[2];
		$bulanCtr = $tglCtr[1];
		$intkomoditi = makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kodebarang='".$komoditi."'");
		$str="select notransaksi from ".$dbname.".pmn_scr where notransaksi like '%".$tahunCtr."%' order by notransaksi desc limit 1";
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
		$notransaksi=$nourut."/SCR-".$intkomoditi[$komoditi]."/".romawi($bulanCtr)."/".$tahunCtr;
		if($komoditi=='40000003'){
			$str = "insert into ".$dbname.".pmn_scr (notransaksi,kodeorg,tanggal,buyer,scn,berikat,komoditi,kuantitas,harga,ppn,paymentdate,bayarke,nokontrak_tbs,jangkawaktu_tbs,status,createdby,createdtime,updateby,updatetime) values 
			('".$notransaksi."','".$pt."','".tanggalsystem($tanggal)."','".$buyer."','".$scn."','".$berikat."','".$komoditi."','".$kuantitas."','".$harga."','".$ppn."','".tanggalsystem($tanggalbayar)."','".$bayarke."','".$kualitas1."','".$kualitas2."','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
		}else{
			$str = "insert into ".$dbname.".pmn_scr (notransaksi,kodeorg,tanggal,buyer,scn,berikat,komoditi,kuantitas,harga,ppn,paymentdate,bayarke,kualitas1,kualitas2,kualitas3,kualitas4,status,createdby,createdtime,updateby,updatetime) values 
		('".$notransaksi."','".$pt."','".tanggalsystem($tanggal)."','".$buyer."','".$scn."','".$berikat."','".$komoditi."','".$kuantitas."','".$harga."','".$ppn."','".tanggalsystem($tanggalbayar)."','".$bayarke."','".$kualitas1."','".$kualitas2."','".$kualitas3."','".$kualitas4."','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";	
		}
		
		try
		{
			$owlPDO->exec($str); 
			
			$listpersetujuan=$_POST['persetujuan'];
			foreach($listpersetujuan as $key=>$val)
			{
				$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$key."' and kodeunit='".$pt."'";
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
					$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$tipeApp."','".$key."','".$listpersetujuan[$key]."','9')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
					}
				}
			}
		}
		catch(PDOException $e)
		{
			echo " Gagal,".$str."__" . addslashes($e->getMessage());
		}
	break;
	
	case'update':
	if($komoditi=='40000003'){
		$str = "update ".$dbname.".pmn_scr set 
			tanggal='".tanggalsystem($tanggal)."',
			buyer='".$buyer."',
			scn='".$scn."',
			berikat='".$berikat."',
			komoditi='".$komoditi."',
			kuantitas='".$kuantitas."',
			harga='".$harga."',
			ppn='".$ppn."',
			paymentdate='".tanggalsystem($tanggalbayar)."',
			bayarke='".$bayarke."',
			nokontrak_tbs='".$kualitas1."',
			jangkawaktu_tbs='".$kualitas2."',
			updateby='".$_SESSION['standard']['userid']."'
			where notransaksi='".$notransaksi."'";	
	}else{
		$str = "update ".$dbname.".pmn_scr set 
			tanggal='".tanggalsystem($tanggal)."',
			buyer='".$buyer."',
			scn='".$scn."',
			berikat='".$berikat."',
			komoditi='".$komoditi."',
			kuantitas='".$kuantitas."',
			harga='".$harga."',
			ppn='".$ppn."',
			paymentdate='".tanggalsystem($tanggalbayar)."',
			bayarke='".$bayarke."',
			kualitas1='".$kualitas1."',
			kualitas2='".$kualitas2."',
			kualitas3='".$kualitas3."',
			kualitas4='".$kualitas4."',
			updateby='".$_SESSION['standard']['userid']."'
			where notransaksi='".$notransaksi."'";	
	}
				
		try
		{
			$owlPDO->exec($str); 
			
			$listpersetujuan=$_POST['persetujuan'];
			foreach($listpersetujuan as $key=>$val)
			{
				$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$key."' and kodeunit='".$pt."'";
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
	break;
	
	case'deletescr':
		$str="delete from ".$dbname.".pmn_scr where notransaksi='".$notransaksi."'";
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
	
	case'postingscr':
		$str="update ".$dbname.".pmn_scr set status='9' where notransaksi='".$notransaksi."'";
		try
		{
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
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'printpdf':
	
		$defaultsize=12;
		$defaultsizekop=9;
		$high=6;
		$highkop=4.7;

	
		$str="select * from ".$dbname.".pmn_scr where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$kodeorg = $res[0]['kodeorg'];
		
		$optBuyer = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$res[0]['buyer']."'");
		$optKomoditi = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$res[0]['komoditi']."'");
		$sNmbank="select b.namabank,a.rekening as bayarke from ".$dbname.".keu_5daftarbank b left join ".$dbname.".keu_5akunbank a on b.kodebank=a.namabank 
		          where a.noakun='".$res[0]['bayarke']."'";
		$dtIsi=fetchData($sNmbank);          
		$optBank=$dtIsi[0];
		//$optBank = makeOption($dbname,'keu_5akunbank','noakun,namabank',"noakun='".$res[0]['bayarke']."'");
		
		$buyer = $optBuyer[$res[0]['buyer']];
		$komoditi = $optKomoditi[$res[0]['komoditi']];
		
		class PDF extends FPDF
		{
			function Header()
			{
				global $dbname;
				global $owlPDO;
				global $kodeorg;
				global $defaultsize;
				global $high;
				global $defaultsizekop;
				global $highkop;
				
				$arrHead = setheadreport($kodeorg);
				$path=$arrHead['logo'];
		
				$this->Image($path,10,5,0,25);
				$this->SetFont('Arial','B',$defaultsizekop);
				$this->SetFillColor(255,255,255);	
				
				$this->SetXY(40,8);
				$this->Cell(60,$highkop,$arrHead['nama'],0,1,'L');	 
				
				$this->SetX(40);
				// $this->Cell(60,5,$arrHead['alamat'],0,1,'L');
				$this->MultiCell(150,$highkop,$arrHead['alamat'],0,'L',0);
				
				$this->SetX(40); 			
				$this->Cell(60,$highkop,"Tel: ".$arrHead['telepon'],0,1,'L');
				$this->Ln();
			}
		}
		
		$pdf=new PDF('P','mm','A4');
		$pdf->AddPage();
		
		$pdf->Ln();
		$pdf->SetY(35);
		$pdf->SetFont('Arial','BU',$defaultsize);
		$pdf->Cell(190,5,strtoupper("SALES CONFIRMATION REPORT"),0,1,'C');
		
		$pdf->Ln();
		$pdf->SetFont('Arial','',$defaultsize);
		$pdf->Cell(190,5,strtoupper($res[0]['notransaksi']),0,1,'C');
		
		$pdf->Ln(15);
		
		$pdf->SetFillColor(255,255,255);
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'Buyer',0,0,'L',1);
		$pdf->Cell(4,$high,':',0,0,'L',1);
		$pdf->Cell(130,$high,$buyer,0,1,'L',1);
		
		$strx="select * from ".$dbname.".pmn_kontrakjual where nokontrak_ref='".$res[0]['notransaksi']."'";
		$resx=fetchData($strx);
		@$nokontrak_ref = $resx[0]['nokontrak'];
		if($res[0]['komoditi']=='40000003'){
				$pdf->SetX(35);
				$pdf->Cell(45,$high,"Contract No.",0,0,'L',1);
				$pdf->Cell(4,$high,':',0,0,'L',1);
				$pdf->Cell(130,$high,$res[0]['nokontrak_tbs'],0,1,'L',1);
		}
		if($res[0]['komoditi']!='40000003'){
			$pdf->SetX(35);
			$pdf->Cell(45,$high,'Sales Contract No',0,0,'L',1);
			$pdf->Cell(4,$high,':',0,0,'L',1);
			$pdf->Cell(130,$high,$nokontrak_ref,0,1,'L',1);
		}
		
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'Commodity',0,0,'L',1);
		$pdf->Cell(4,$high,':',0,0,'L',1);
		$pdf->Cell(130,$high,$komoditi,0,1,'L',1);
		
		if($res[0]['komoditi']=='40000001')
		{
			$pdf->SetX(35);
			$pdf->Cell(45,$high,'Specifications',0,0,'L',1);
			$pdf->Cell(4,$high,':',0,0,'L',1);
			$pdf->Cell(30,$high,"FFA",0,0,'L',1);
			$pdf->Cell(4,$high,':',0,0,'C',1);
			$pdf->Cell(40,$high,$res[0]['kualitas1']." % Max",0,1,'L',1);
			
			$pdf->SetX(35);
			$pdf->Cell(45,$high,'',0,0,'L',1);
			$pdf->Cell(4,$high,'',0,0,'L',1);
			$pdf->Cell(30,$high,"M & I",0,0,'L',1);
			$pdf->Cell(4,$high,':',0,0,'C',1);
			$pdf->Cell(40,$high,$res[0]['kualitas2']." % Max",0,1,'L',1);
		}
		else if($res[0]['komoditi']=='40000002')
		{
			if($res[0]['kualitas1'] !=0 || $res[0]['kualitas1']==''){
				$pdf->SetX(35);
				$pdf->Cell(45,$high,'Specifications',0,0,'L',1);
				$pdf->Cell(4,$high,':',0,0,'L',1);
				$pdf->Cell(30,$high,"Broken",0,0,'L',1);
				$pdf->Cell(4,$high,':',0,0,'C',1);
				$pdf->Cell(40,$high,$res[0]['kualitas1']." % Max",0,1,'L',1);
				
				
			}
			
			$pdf->SetX(35);
			$pdf->Cell(45,$high,'',0,0,'L',1);
			$pdf->Cell(4,$high,'',0,0,'L',1);
			$pdf->Cell(30,$high,"Kadar Air",0,0,'L',1);
			$pdf->Cell(4,$high,':',0,0,'C',1);
			$pdf->Cell(40,$high,$res[0]['kualitas2']." % Max",0,1,'L',1);
		}

		if($res[0]['kualitas3']!=0 || $res[0]['kualitas3']=='')
		{
			$pdf->SetX(35);
			$pdf->Cell(45,$high,'',0,0,'L',1);
			$pdf->Cell(4,$high,'',0,0,'L',1);
			$pdf->Cell(30,$high,"Kadar Kotoran",0,0,'L',1);
			$pdf->Cell(4,$high,':',0,0,'C',1);
			$pdf->Cell(40,$high,$res[0]['kualitas3']." % Max",0,1,'L',1);	
		}
		
		if($res[0]['komoditi']=='40000002'){
			if($res[0]['kualitas4'] !=0 || $res[0]['kualitas4']==''){
				$pdf->SetX(35);
				$pdf->Cell(45,$high,'',0,0,'L',1);
				$pdf->Cell(4,$high,'',0,0,'L',1);
				$pdf->Cell(30,$high,"FFA",0,0,'L',1);
				$pdf->Cell(4,$high,':',0,0,'C',1);
				$pdf->Cell(40,$high,$res[0]['kualitas4']." % Max",0,1,'L',1);	
			}
		}
		
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'Quantity',0,0,'L',1);
		$pdf->Cell(4,$high,':',0,0,'L',1);
		$pdf->Cell(130,$high,number_format($res[0]['kuantitas'])." Kg",0,1,'L',1);
		
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'Price',0,0,'L',1);
		$pdf->Cell(4,$high,':',0,0,'L',1);
		$pdf->Cell(130,$high,"Rp. ".number_format($res[0]['harga'],2)."/Kg",0,1,'L',1);
		
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'Total',0,0,'L',1);
		$pdf->Cell(4,$high,':',0,0,'L',1);
		$pdf->Cell(130,$high,"Rp. ".number_format(($res[0]['kuantitas']*$res[0]['harga'])).",-",0,1,'L',1);
		if($res[0]['komoditi']=='40000003'){
				$pdf->SetX(35);
				$pdf->Cell(45,$high,'Period of Transaction',0,0,'L',1);
				$pdf->Cell(4,$high,':',0,0,'L',1);
				$pdf->Cell(130,$high,$res[0]['jangkawaktu_tbs'],0,1,'L',1);
		}
		if($res[0]['komoditi']!='40000003'){
			$pdf->SetX(35);
			$pdf->Cell(45,$high,'Date of Transaction',0,0,'L',1);
			$pdf->Cell(4,$high,':',0,0,'L',1);
			$pdf->Cell(130,$high,tanggalnormal($res[0]['tanggal']),0,1,'L',1);			
		}
		
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'Payment date & term',0,0,'L',1);
		$pdf->Cell(4,$high,':',0,0,'L',1);
		$pdf->Cell(130,$high,tanggalnormal($res[0]['paymentdate']),0,1,'L',1);
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'',0,0,'L',1);
		$pdf->Cell(4,$high,'',0,0,'L',1);
		$pdf->Cell(130,$high,$optBank['namabank'],0,1,'L',1);
		$pdf->SetX(35);
		$pdf->Cell(45,$high,'',0,0,'L',1);
		$pdf->Cell(4,$high,'',0,0,'L',1);
		$pdf->Cell(130,$high,"A/C    : ".$optBank['bayarke'],0,1,'L',1);
		$pdf->SetX(35);
		
		$pdf->Ln(20);
		
		$countApp = getCountApproval('SCR',$kodeorg);
		$widthTtd = (145 / ($countApp + 1));
		$locimg = (($widthTtd/2) - 5)+27;
		$pdf->SetX(35);
		for($i=1;$i<=$countApp;$i++)
		{
			$arrDetail = detailApprove($i,$notransaksi,'SCR');
			$pdf->Cell($widthTtd,$high,"Approved ".$i." by",0,0,'C',1);
		}
		$pdf->Cell($widthTtd,$high,'Prepared by',0,0,'C',1);
		
		$pdf->Ln();
		$y = $pdf->GetY();
		$pdf->SetX(35);
		for($i=1;$i<=$countApp;$i++)
		{
			$arrDetail = detailApprove($i,$notransaksi,'SCR');
			$optTtdp = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$arrDetail['karyawanid']."'");
			if(isset($optTtdp[$arrDetail['karyawanid']]) && file_exists($optTtdp[$arrDetail['karyawanid']]))
			{
				$pdf->Image($optTtdp[$arrDetail['karyawanid']], $locimg, $y, 0, 20);
			}
			$locimg = $locimg + $widthTtd;
		}
		$optTtdp = makeOption($dbname,'setup_ttd','karyawanid,file',"karyawanid='".$res[0]['updateby']."'");
		if(isset($optTtdp[$res[0]['updateby']]) && file_exists($optTtdp[$res[0]['updateby']]))
			$pdf->Image($optTtdp[$res[0]['updateby']], $locimg, $y, 0, 20);
		
		
		$pdf->Ln(20);
		
		$pdf->SetX(35);
		for($i=1;$i<=$countApp;$i++)
		{
			$arrDetail = detailApprove($i,$notransaksi,'SCR');
			$pdf->Cell($widthTtd,7,$arrDetail['nama'],0,0,'C',1);
		}
		$optNmkry = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$res[0]['updateby']."'");
		$pdf->Cell($widthTtd,7,$optNmkry[$res[0]['updateby']],0,0,'C',1);
		
		$pdf->Output();
	break;
}
