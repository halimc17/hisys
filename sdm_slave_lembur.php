<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$path       = "fileupload/lbr/";
$proses     = checkPostGet('proses', '');
$txtFind    = checkPostGet('txtfind', '');
$absnId     = explode("###", checkPostGet('absnId', ''));
$tgl        = count($absnId) > 1 ? tanggalsystem($absnId[1]) : '';
$kdOrg      = $absnId[0];
$kdOrgspl   = checkPostGet('kdOrgspl', '');
$tglspl     = checkPostGet('tglspl', '');
$jabatan    = checkPostGet('jabatan', '');
$tipekar    = checkPostGet('tipekar', '');
$krywnId    = checkPostGet('krywnId', '');
$tpLmbr     = checkPostGet('tpLmbr', '');
$ungTrans   = checkPostGet('ungTrans', '');
$ungMkn     = checkPostGet('ungMkn', '');
$Jam        = checkPostGet('Jam', '');
$ungLbhjm   = checkPostGet('ungLbhjm', '');
$jammulai   = checkPostGet('jammulai', '');
$jamselesai = checkPostGet('jamselesai', '');
$ket        = checkPostGet('ket', '');
$no         = checkPostGet('no', '');
$postJabatan= getPostingJabatan('lembur');
$arrHsl=array("0"=>"Belum diajukan","1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['koreksi'],"2"=>$_SESSION['lang']['ditolak'],"9"=>"Proses persetujuan");
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

// echo"<pre>";
// print_r($param);
// exit("error");

$optKry = '';
$optTipelembur = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrsstk = array("0" => $_SESSION['lang']['haribiasa'], "1" => $_SESSION['lang']['hariminggu'], "2" => $_SESSION['lang']['harilibur'], "4" => "Hari Libur Spesial");
//, "3" => $_SESSION['lang']['hariraya']
$nmOrg        = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmkarya      = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$optjabatan   = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$opttipe      = makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$kodeOrg      = checkPostGet('kodeOrg', '');
$basisJam     = checkPostGet('basisJam', '');
$notransaksisp= checkPostGet('notransaksisp', '');
$thnPeriod    = "";
//$arrsstk=getEnum($dbname,'sdm_5lembur','tipelembur');
foreach ($arrsstk as $kei => $fal) {
    //print_r($kei);exit();
    $optTipelembur.="<option value='" . $kei . "'>" . ucfirst($fal) . "</option>";
}

$tpLembur = checkPostGet('tpLembur', '');
$basisJam = checkPostGet('basisJam', '');
$optOrg2 = getOrgDetail(1);
$inidc=0;
$listOrg="(";
foreach ($optOrg2 as $key => $value) {
    if($inidc==0){
        $listOrg.="'".$key."'";
        $inidc=1;
    }else{
        $listOrg.=",'".$key."'";
    }
}
$listOrg.=")";
switch ($proses) {
	case'updtjam':
		$jammulai=explode(':',$jammulai);
		$jam1=$jammulai[0];
		$jam2=$jammulai[1];
		$jam2=$jam2/60;
		$jmbaru=$jam1+$jam2;
		@$jmtot=$jmbaru+($Jam-$param['ttljampop']);
		
		$jmtot=number_format($jmtot,2);
		$jmtot = explode('.',$jmtot);
		
		$jmbr=$jmtot[0];
		$mntbr=$jmtot[1];
		$mntbr=number_format($mntbr/100*60);
		if($jmbr>=24){
			$jmbr=$jmbr-24;
		}
		
		$jmsl=addZero($jmbr,2).':'.addZero($mntbr,2);
	
	echo $jmsl;
	
	break;
    case'preview':
		$arrTipeLembur=array($_SESSION['lang']['haribiasa'],$_SESSION['lang']['hariminggu'],$_SESSION['lang']['harilibur'],$_SESSION['lang']['hariraya'],"Hari Libur Spesial");
		
		
		$cl="";
		if(strtolower(date('D', strtotime($param['tanggal'])))=='fri'){
			$cl="style=color:blue;";
		}elseif(strtolower(date('D', strtotime($param['tanggal'])))=='sun'){
			$cl="style=color:red;font-weight:bold;";
		}elseif(strtolower(date('D', strtotime($param['tanggal'])))=='sat'){
			$cl="style=color:orange;font-weight:bold;";
		}
		
		$where="";
		$kodeorg=substr($param['kodeorg'],0,4);
		$tipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
		if($tipe[$kodeorg]=='HOLDING'){
			$where=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$kodeorg."')";			
		}else{
			$where=" and (kebun='GLOBAL' or kebun='".$kodeorg."')";			
		}
		
		$day = date('D', strtotime($param['tanggal']));
		$sql = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$param['tanggal']."' ".$where.""; #exit("error".$sql);
		$req = fetchdata($sql);
		if(@$req[0]['keterangan']=='libur'){
			$cl="style=color:red;font-weight:bold;"; $title="title=\"".$req[0]['catatan']."\"";
		} else if (($day=='Sun' and @$req[0]['keterangan']=='') or @$req[0]['keterangan']=='libur'){
			$cl="style=color:red;font-weight:bold;"; $title="title=\"".$req[0]['catatan']."\"";
		}
		
		$tab="<table>
				<tr>
					<td>Tanggal</td>
					<td>:</td>
					<td><b>".tanggalnormal($param['tanggal'])."</b></td>
				</tr>";
		if($req[0]['catatan']!=''){
			$cat=" - ".$req[0]['catatan'];
		}
		$tab.="<tr>
					<td>Hari</td>
					<td>:</td>
					<td ".$cl."><b>".hari($param['tanggal'])."".$cat."</b></b></td>
				</tr>";
		$tab.="<tr>
					<td>Kodeorg</td>
					<td>:</td>
					<td><b>".$param['kodeorg']." - ".getNamaOrg($param['kodeorg'])."</b></b></td>
				</tr>";
		$tab.="</table>";
		$tab.="
			<table cellspacing='1' cellpadding='5' border='0' class='sortable' style='width:100%;' >
				<thead>
				 <tr class=rowheader'>
					<th align='center' rowspan=2>No</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['nik2']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['jabatan']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['divisi']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['bagian']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['tipelembur']."</th>
					<th align='center' colspan=2>".$_SESSION['lang']['jamaktual']."</th>";
					if($param['notransaksi']!=''){
						$tab.="<th align='center' colspan=2>".$_SESSION['lang']['uangkelebihanjam']."</th>";
					}	
					$tab.="<th align='center' rowspan=2>".$_SESSION['lang']['persen']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['mulai']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['selesai']."</th>
					<th align='center' rowspan=2>".$_SESSION['lang']['keterangan']."</th>
				</tr>
				<tr class=rowheader'>
					<th align='center'>HI</th>
					<th align='center'>SD HI</th>";
					if($param['notransaksi']!=''){
						$tab.="<th align='center'>HI</th>
						<th align='center'>SD HI</th>";
					}
				$tab.="</tr>
				</thead>
				<tbody>
			";
		$str = "select * from ".$dbname.".sdm_lemburdt2 where kodeorg='".$param['kodeorg']."' and tanggal='".$param['tanggal']."'";
		$res = fetchdata($str);
        foreach($res as $bar){
			$rowspan[$bar['karyawanid']]++;
			$detail[$bar['karyawanid']][]=$bar['karyawanid'];
			$mulai[$bar['karyawanid']][]=$bar['jammulai'];
			$selesai[$bar['karyawanid']][]=$bar['jamselesai'];
			$ketx[$bar['karyawanid']][]=$bar['ket'];
		}	
		
		// echo"<pre>";
		// print_r($detail);
		$str = "select * from ".$dbname.".sdm_lemburdt where kodeorg='".$param['kodeorg']."' and tanggal like '".substr($param['tanggal'],0,7)."%'";
		$res = fetchdata($str);
        foreach($res as $bar){
			$jamsdhi[$bar['karyawanid']]+=$bar['jamaktual'];
			$rpsdhi[$bar['karyawanid']]+=$bar['uangkelebihanjam'];
		}
		
		
		
		$str = "select * from ".$dbname.".sdm_lemburdt where kodeorg='".$param['kodeorg']."' and tanggal='".$param['tanggal']."'";
		$res = fetchdata($str);
        foreach($res as $bar){

			$per = substr($param['tanggal'], 0, 4) . '-' . substr($param['tanggal'], 5, 2);

			$sql = "select * from ".$dbname.".sdm_5gajipokok where tahun = '".substr($param['tanggal'], 0, 4)."' and idkomponen='87' ";
			$req = fetchdata($sql);
			foreach($req as $val){
				$gapok=$val['jumlah'];
			}
			$no++;
			//$row="rowspan='".($rowspan[$bar['karyawanid']]+1)."'";
			$click="onclick=detaillembur('".substr($param['tanggal'],0,7)."','".$bar['karyawanid']."');";
			
			$tab.="
			<tr ".$click." class=rowcontent style=vertical-align:top;cursor:pointer; title=\"Click untuk melihat detail lembur.\" >
			<td ".$row." align=center>".$no."</td>
			<td ".$row.">".getKary($bar['karyawanid'],'nik')."</td>
			<td ".$row.">".getKary($bar['karyawanid'],'namakaryawan')."</td>
			<td ".$row.">".getNamaJabatan(getKary($bar['karyawanid'],'kodejabatan'))."</td>
			<td ".$row.">".getKary($bar['karyawanid'],'subbagian')."</td>
			<td ".$row.">".getNamaDept(getKary($bar['karyawanid'],'bagian'))."</td>";
			if(getKary($bar['karyawanid'],'tipekaryawan')==4){
				$tab.="<td ".$row." style=color:#ff7b00><b>".getNamaTipeKary(getKary($bar['karyawanid'],'tipekaryawan'))."</b></td>";
			}else{				
				$tab.="<td ".$row.">".getNamaTipeKary(getKary($bar['karyawanid'],'tipekaryawan'))."</td>";
			}
			$wr="";
			if($bar['tipelembur']!='0'){
				$wr="style=font-weight:bold;";
			}
			$tab.="<td ".$row." ".$wr.">".$arrTipeLembur[$bar['tipelembur']]."</td>";
			if($bar['jamaktual']>3){
				$tab.="<td ".$row." align=center  style=color:#ff5100;background-color:#ffb5e6><b>".$bar['jamaktual']."</b></td>";
			}else{				
				$tab.="<td ".$row." align=center>".$bar['jamaktual']."</td>";
			}
			$tab.="<td ".$row." align=center style=color:blue;><b>".$jamsdhi[$bar['karyawanid']]."</b></td>";
			if($param['notransaksi']!=''){				
				$tab.="<td ".$row." align=right>".number_format($bar['uangkelebihanjam'],0)."</td>";
				$tab.="<td ".$row." align=right style=color:blue;><b>".number_format($rpsdhi[$bar['karyawanid']],0)."</b></td>";
			}
			
			$persen = ($rpsdhi[$bar['karyawanid']]/$gapok)*100;
			if($persen<=50){
				$color="";
			}elseif($persen>50 and $persen<=75){
				$color="style=color:#ff8000";
			}elseif($persen>75 and $persen<=100){
				$color="style=color:#ff3300";
			}elseif($persen>100){
				$color="style=color:#ff0000";
			}
			
			$tab.="<td ".$row." align=right ".$color."><b>".@number_format($persen,2)."</b></td>
			<td align=center>".$bar['jammulai']."";
				if($rowspan[$bar['karyawanid']]>1){					
					$tab.="<table style=font-style:italic;>";
						foreach($detail as $karyid => $v1){
							foreach($v1 as $key => $value){
								if($bar['karyawanid']==$karyid){									
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=center>".$mulai[$bar['karyawanid']][$key]."</td>";
									$tab.="</tr>";								
								}
							}
						}
					$tab.="</table>";
				}
			$tab.="</td>
			<td align=center>".$bar['jamselesai']."";
				if($rowspan[$bar['karyawanid']]>1){					
					$tab.="<table style=font-style:italic;>";
						foreach($detail as $karyid => $v1){
							foreach($v1 as $key => $value){
								if($bar['karyawanid']==$karyid){									
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=center>".$selesai[$bar['karyawanid']][$key]."</td>";
									$tab.="</tr>";								
								}
							}
						}
					$tab.="</table>";
				}
			$tab.="</td>
			<td align=left>".$bar['ket']."";
				if($rowspan[$bar['karyawanid']]>1){					
					$tab.="<table style=font-style:italic;>";$n=0;
						foreach($detail as $karyid => $v1){
							foreach($v1 as $key => $value){
								if($bar['karyawanid']==$karyid){
									$n++;
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=left>(".$n.") ".$ketx[$bar['karyawanid']][$key]."</td>";
									$tab.="</tr>";								
								}
							}
						}
					$tab.="</table>";
				}
			$tab.="</td>";
			$tab.="</tr>";
			
			$ttljambi+=$bar['jamaktual'];
			$ttljamsdbi+=$jamsdhi[$bar['karyawanid']];
			$ttlrpbi+=$bar['uangkelebihanjam'];
			$ttlrpsdbi+=$rpsdhi[$bar['karyawanid']];
			
        }
		
		$tab.="
			<tr class=rowcontent style=background-color:cyan>
			<td colspan=8 align=center>TOTAL</td>
			<td align=center>".$ttljambi."</td>
			<td align=center>".$ttljamsdbi."</td>";
			if($param['notransaksi']!=''){				
				$tab.="<td align=right>".number_format($ttlrpbi,0)."</td>
				<td align=right>".number_format($ttlrpsdbi,0)."</td>";
			}
			$tab.="<td colspan=4 align=center></td>
			";
		$tab.="</tr>";
		if($param['notransaksi']!=''){
			$tab.="<tr>";
			$tab.="<td colspan=16 align=center><button class=mybutton onclick=\"getdata_atbs('".$param['notransaksi']."','".$param['kolom']."','".$param['kodeapproval']."','".substr($param['kodeorg'],0,4)."')\">".$_SESSION['lang']['disetujui']."</button></td>";
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		$str= "select * from ".$dbname.".sdm_lemburht where kodeorg='".$param['kodeorg']."' and tanggal='".$param['tanggal']."'";
		$res= fetchData($str);
		$jurnal = $res[0]['posting'];
		$param['notransaksi']=$res[0]['nopengajuan'];
		$param['notransaksiupload']=$res[0]['id'];
		
		$no = 0;
		$tab.="
			<br>
			<label>File Upload:</label>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px colspan=2>Action</td>
				</tr>
				</thead>
				<tbody>";
				
		// $str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksiupload']."' and status='1'";
		$res= fetchData($str);
		// exit("warning".print_r($res));
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
				if($jurnal==0 or $jurnal==2 or $jurnal==3){
					$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
					
					$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
				}else{
					$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				}
				$tab.="</tr>";
			
			}
		}
		$tab.="</tbody></table>";
		
		
		echo $tab;
		if($param['notransaksi']!=''){
			echo"<br><label>Approval:</label><div style=width:40%>".gethistoryapp($param['notransaksi'],'LBR')."</div>";
		}
		
	break;
    case'getsessionlembur':
		$_SESSION['lembur']=array();
		$param['tgl']=tanggalsystemn($param['tgl']);
		$str = "select * from ".$dbname.".sdm_lemburdt2 where kodeorg='".$param['kdorg']."' and tanggal='".$param['tgl']."' and karyawanid='".$param['karyawanid']."'"; #exit('error'.$str);
		$res = fetchData($str);
		foreach($res as $bar){
			$_SESSION['lembur'][]=array(
				'kodeorg'   =>$bar['kodeorg'],
				'tanggal'   =>$bar['tanggal'],
				'karyawanid'=>$bar['karyawanid'],
				'jammulai'  =>$bar['jammulai'],
				'jamselesai'=>$bar['jamselesai'],
				'jumlah'    =>$bar['jumlah'],
				'ket'       =>$bar['ket']
			);
		}
		
		
	break;
    case'popupjam':
		$param['tgl']=tanggalsystemn($param['tgl']);
		$unit = substr($param['kdorg'],0,4);
		$str = "select * from ".$dbname.".sdm_5shift where kodeorg = '".$unit."'";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("errorcode : Master shift untuk kode organisasi ".$unit." belum ada.");
		}
		foreach($res as $val){
			if(substr($val['masuk'],0,2)=='00'){
				$val['masuk'] = "24:".substr($val['masuk'],3,2);
			}
			if(substr($val['keluar'],0,2)=='00'){
				$val['keluar'] = "24:".substr($val['keluar'],3,2);
			}
			
			$jamshiftmasuk[$val['id']]    = $val['masuk'];
			$jamshiftoutist[$val['id']]   = $val['keluar_ist'];
			$jamshiftinist[$val['id']]    = $val['masuk_ist'];
			$jamshiftpulang[$val['id']]   = $val['keluar'];
			$jamshifttoleransi[$val['id']]= $val['toleransi'];
			
			if($val['namashift']=='KTR' and $val['shift']=='1'){
				$defmasuk    = $val['masuk'];
				$defoutist   = $val['keluar_ist'];
				$definist    = $val['masuk_ist'];
				$defpulang   = $val['keluar'];
				$deftoleransi= $val['toleransi'];
				$defidshift  = $val['id'];
			}
		}
		
		$str = "select * from ".$dbname.".sdm_5shiftanggota where kodeorg = '".$unit."' and karyawanid = '".$param['karyawanid']."' and tanggal = '".$param['tgl']."'";
		$res = fetchdata($str);
		foreach($res as $val){
			$jamshift['namashift']= $val['namashift'];
			$jamshift['ke']       = $val['shift'];
			$jamshift['idshift']  = $val['idshift'];
			$jamshift['masuk']    = $jamshiftmasuk[$val['idshift']];
			$jamshift['outist']   = $jamshiftoutist[$val['idshift']];
			$jamshift['inist']    = $jamshiftinist[$val['idshift']];
			$jamshift['pulang']   = $jamshiftpulang[$val['idshift']];
			$jamshift['toleransi']= $jamshifttoleransi[$val['idshift']];
		}
		if(count($res)=='' or $jamshift['pulang']==''){	
			$jamshift['namashift']= "KTR";
			$jamshift['ke']       = "1";
			$jamshift['idshift']  = $defidshift;
			$jamshift['masuk']    = $defmasuk;
			$jamshift['outist']   = $defoutist;
			$jamshift['inist']    = $definist;
			$jamshift['pulang']   = $defpulang;
			$jamshift['toleransi']= $deftoleransi;
		}
		
		if(substr($jamshift['namashift'],0,3)=='SPV'){
			$jamkerja="Masuk sebelum jam : ".$jamshift['masuk'].", Keluar Ist : ".$jamshift['outist'].", Masuk Ist : ".$jamshift['inist'].", Pulang diatas jam : ".$jamshift['pulang']."";
		}else{				
			$jamkerja="Masuk : ".$jamshift['masuk'].", Keluar Ist : ".$jamshift['outist'].", Masuk Ist : ".$jamshift['inist'].", Pulang : ".$jamshift['pulang']."";
		}
		
		$cl="";
		if(strtolower(date('D', strtotime($param['tgl'])))=='sun'){
			$cl=1;
		}
		if(substr($jamshift['namashift'],0,3)=='LBR'){
			$cl=1;
		}
		
		$where="";
		$kodeorg=substr($param['kdorg'],0,4);
		$tipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
		if($tipe[$kodeorg]=='HOLDING'){
			$where=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$kodeorg."')";			
		}else{
			$where=" and (kebun='GLOBAL' or kebun='".$kodeorg."')";			
		}
		
		$day = date('D', strtotime($param['tgl']));
		$sql = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$param['tgl']."' ".$where.""; #exit("error".$sql);
		$req = fetchdata($sql);
		if(@$req[0]['keterangan']=='libur'){
			$cl=1;
		} else if (($day=='Sun' and @$req[0]['keterangan']=='') or @$req[0]['keterangan']=='libur'){
			$cl=1;
		}
		$color="";
		if($cl==1){
			$color="style=color:red;";
		}
		$tab.="<table>";
		$tab.="<tr>
				<tr><td>Nama</td><td>:</td><td>".getKary($param['karyawanid'])."</td></tr>
				<tr><td>Tanggal</td><td>:</td><td ".$color.">".tanggalnormal($param['tgl'])." - ".hari($param['tgl'])."</td></tr>";
				
		#if($cl==""){
			$tab.="<tr><td>Shift</td><td>:</td><td>".$jamshift['namashift']." - ".$jamshift['ke']."</td></tr>
				<tr><td>Jam Kerja</td><td>:</td><td>".$jamkerja."</td></tr>";
		#}		
		$tab.="</table>";
		
		$tab.="<table id=mytable class='sortable nowrap' cellspacing='1' cellpadding='5' border='0'>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>No</th>
				<th style='text-align:center;'>Jam Mulai</th>
				<th style='text-align:center;'>Jam Selesai</th>
				<th style='text-align:center;'>Jumlah</th>
				<th style='text-align:center;'>Keterangan</th>
				<th style='text-align:center;'>Action</th>
			</tr>	
			";
		$tab.="</thead>";
		$tab.="<tbody>";
		$tab.="<tr class=rowcontent>";
	
		$tab.="<td align=center>#</td>";
		$tab.="<td><input type='text' class='myinputtextnumber' id='jam_mulai_pop' onkeyup=updtjam('".$param['no']."','1') name='jam_mulai' style='width:65px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' /></td>";
		$tab.="<td><input type='text' class='myinputtextnumber' id='jam_selesai_pop' onkeyup=updtjam('".$param['no']."','2') name='jam_selesai' style='width:65px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' /></td>";
		$tab.="<td><input style='width:65px' type='text' class='myinputtextnumber' id='jumlah' disabled></td>";
		$tab.="<td><input style='width:200px' type='text' class='myinputtext' id='ket_pop'></td>";
		$tab.="<td align=center><img title=Simpan class=zImgBtn onclick=savesession('".$param['no']."') src=images/save.png></td>";
		$tab.="</tr>";
		$tab.="</tbody>";
		$tab.="<tbody id=listdatasession>
			<script>loaddatasession('".$param['kdorg']."','".$param['tgl']."','".$param['karyawanid']."')</script>
			</tbody>";
		
		$tab.="</table>";
		
		$mingguini= date('W', strtotime($param['tgl']));
		$bulanlalu= tglbulanlalu($param['tgl']);
		$rangetgl = rangeTanggal($bulanlalu,$param['tgl']);
		foreach($rangetgl as $tanggal){
			if(date('W', strtotime($tanggal))==$mingguini){
				$tglweek[$tanggal]=$tanggal;
			}
		}
		
		$str = "select * from " . $dbname . ".sdm_lemburdt where karyawanid='".$param['karyawanid']."' and tanggal in ('".implode("','",$tglweek)."')";
		$res = fetchdata($str);
		if(count($res)>0){
			$tab.="<br><div style=clear:both>Lembur minggu ini.</div>";
			$tab.="<table class='sortable nowrap' cellspacing='1' cellpadding='3' border='0'>
			<thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<th style='text-align:center;'>No</th>";
				$tab.="<th style='text-align:center;'>Tanggal</th>";
				$tab.="<th style='text-align:center;'>Hari</th>";
				$tab.="<th style='text-align:center;'>Jumlah</th>";
				$tab.="</tr>";
			$tab.="</thead>";
			$tab.="<tbody>";
			foreach($res as $val){
				$jam[$val['tanggal']]=$val['jamaktual'];
			}
			foreach($tglweek as $tanggal){
				$n++;
				$tab.="<tr class=rowcontent>";	
				$tab.="<td style='text-align:center;'>".$n."</td>";
				$tab.="<td style='text-align:center;'>".tanggalnormal($tanggal)."</td>";
				$tab.="<td style='text-align:center;'>".hari($tanggal)."</td>";
				$tab.="<td style='text-align:center;'>".$jam[$tanggal]."</td>";
				$tab.="</tr>";
				$tjam+=$jam[$tanggal];
			}
			$color="";
			if($tjam>=40){
				$color="color:white;background-color:red;font-weight:bold;";
			}
			$tab.="<tr class=rowcontent>";	
			$tab.="<td style='text-align:center;' colspan=3>TOTAL</td>";
			$tab.="<td style='text-align:center;".$color."'>".$tjam."</td>";
			$tab.="</tr>";
		}
		
		echo $tab;
	break;
    case'savesession':
		if($param['jumlah']==""){
			$param['jumlah']=0;
		}
		$param['tgl']=tanggalsystemn($param['tgl']);
		if($param['karyawanid']==""){
			exit("Error : Nama karyawan belum dipilih.");
		}
		if($param['jumlah']=="0"){
			exit("Error : Silahkan isi jam mulai dan jam selesai.");
		}
		
		if(strpos($param['mulai'],":")==""){
			exit("Error : Format jam menggunakan titik dua (:).");
		}
		if(strpos($param['selesai'],":")==""){
			exit("Error : Format jam menggunakan titik dua (:).");
		}
		
		$emulai  =explode(":",$param['mulai']);
		$eselesai=explode(":",$param['selesai']);
		
		if($emulai[0]>23 or $eselesai[0]>23){
			exit("Error : Jam maksimal 23, jam 24 isikan dengan 00:00");
		}
		
		if($emulai[1]>59 or $eselesai[1]>59){
			exit("Error : Menit maksimal adalah 59");
		}
		if($param['ket']==""){
			exit("Error : Silahkan isi keterangan.");
		}
		
		$unit = substr($param['kdorg'],0,4);
		$str = "select * from ".$dbname.".sdm_5shift where kodeorg = '".$unit."'";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("errorcode : Master shift untuk kode organisasi ".$unit." belum ada.");
		}
		foreach($res as $val){
			if(substr($val['masuk'],0,2)=='00'){
				$val['masuk'] = "24:".substr($val['masuk'],3,2);
			}
			if(substr($val['keluar'],0,2)=='00'){
				$val['keluar'] = "24:".substr($val['keluar'],3,2);
			}
			
			$jamshiftmasuk[$val['id']]    = $val['masuk'];
			$jamshiftoutist[$val['id']]   = $val['keluar_ist'];
			$jamshiftinist[$val['id']]    = $val['masuk_ist'];
			$jamshiftpulang[$val['id']]   = $val['keluar'];
			$jamshifttoleransi[$val['id']]= $val['toleransi'];
			
			if($val['namashift']=='KTR' and $val['shift']=='1'){
				$defmasuk    = $val['masuk'];
				$defoutist   = $val['keluar_ist'];
				$definist    = $val['masuk_ist'];
				$defpulang   = $val['keluar'];
				$deftoleransi= $val['toleransi'];
				$defidshift  = $val['id'];
			}
		}
		
		$str = "select * from ".$dbname.".sdm_5shiftanggota where kodeorg = '".$unit."' and karyawanid = '".$param['karyawanid']."' and tanggal = '".$param['tgl']."'";
		$res = fetchdata($str);
		foreach($res as $val){
			$jamshift['namashift']= $val['namashift'];
			$jamshift['ke']       = $val['shift'];
			$jamshift['idshift']  = $val['idshift'];
			$jamshift['masuk']    = $jamshiftmasuk[$val['idshift']];
			$jamshift['outist']   = $jamshiftoutist[$val['idshift']];
			$jamshift['inist']    = $jamshiftinist[$val['idshift']];
			$jamshift['pulang']   = $jamshiftpulang[$val['idshift']];
			$jamshift['toleransi']= $jamshifttoleransi[$val['idshift']];
		}
		if(count($res)=='' or $jamshift['pulang']==''){	
			$jamshift['namashift']= "KTR";
			$jamshift['ke']       = "1";
			$jamshift['idshift']  = $defidshift;
			$jamshift['masuk']    = $defmasuk;
			$jamshift['outist']   = $defoutist;
			$jamshift['inist']    = $definist;
			$jamshift['pulang']   = $defpulang;
			$jamshift['toleransi']= $deftoleransi;
		}
		$emasuk =explode(":",$jamshift['masuk']);
		$eoutist=explode(":",$jamshift['outist']);
		$einist =explode(":",$jamshift['inist']);
		$epulang=explode(":",$jamshift['pulang']);
		$rangemnt=range(0,59);
		$jamkerja=array();
		
		if($jamshift['masuk']>$jamshift['pulang']){
			#jam masuk 1
			if($jamshift['masuk']>$jamshift['outist']){
				for($i=$emasuk[0];$i<=23;$i++){					
					$range[]=$i;
				}
				for($i=0;$i<=$eoutist[0];$i++){					
					$range[]=$i;
				}
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." 23:59";
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
						
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= tglbesok($param['tanggal'])." 00:00";
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
			}else{
				for($i=$emasuk[0];$i<=$eoutist[0];$i++){					
					$range[]=$i;
				}
				
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['outist'];
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
			}
			#jam masuk 2
			$range=array();
			if($jamshift['inist']>$jamshift['pulang']){
				for($i=$einist[0];$i<=23;$i++){					
					$range[]=$i;
				}
				for($i=0;$i<=$epulang[0];$i++){					
					$range[]=$i;
				}
				
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." 23:59";
						if($menit>=$einist[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
						
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= tglbesok($param['tanggal'])." 00:00";
						if($menit>=$einist[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
			}else{
				for($i=$einist[0];$i<=$epulang[0];$i++){					
					$range[]=$i;
				}
				
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['pulang'];
						if($menit>=$einist[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
			}
			
			// echo"<pre>";
			// print_r($jamkerja);
			// exit("error");
			
			if($jamshift['outist']!='00:00'){
				#jam masuk pertama
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['outist'];
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
				
				$range=range($einist[0],$epulang[0]);
				#jam masuk kedua
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['pulang'];
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
			}else{
				$range=range($emasuk[0],$epulang[0]);
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['pulang'];
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
			}
		}else{
			if($jamshift['outist']!='00:00'){
				$range=range($emasuk[0],$eoutist[0]);
				#jam masuk pertama
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['outist'];
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
				
				$range=range($einist[0],$epulang[0]);
				#jam masuk kedua
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['pulang'];
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
						}
					}
				}
				
				//exit("error xxxx");
			}else{
				$range=range($emasuk[0],$epulang[0]);
				foreach($range as $jam){
					$jam = addZero($jam,2);
					foreach($rangemnt as $menit){
						$menit = addZero($menit,2);
						$strjampulang  = $param['tanggal']." ".$jam.":".$menit;
						$strshiftpulang= $param['tanggal']." ".$jamshift['pulang'];
						if($menit>=$emasuk[1] and strtotime($strjampulang)<strtotime($strshiftpulang)){
							$jamkerja[$jam.":".$menit]=$jam.":".$menit;
							$isi.=$jam.":".$menit."<".$jamshift['pulang']."<br>";
						}
					}
				}
			}
		}
		
		$jammulai  = addZero($emulai[0],2).":".addZero($emulai[1],2);
		$jamselesai= addZero($eselesai[0],2).":".addZero($eselesai[1],2);
		
		if($jammulai>$jamselesai){
			for($i=$emulai[0];$i<=23;$i++){					
				$rangelb[]=$i;
			}
			for($i=0;$i<=$eselesai[0];$i++){					
				$rangelb[]=$i;
			}
			foreach($rangelb as $jam){
				$jam = addZero($jam,2);
				foreach($rangemnt as $menit){
					$menit = addZero($menit,2);
					if($menit>=$emulai[1] and $jam.":".$menit<=23){
						$jamlembur[$jam.":".$menit]=$jam.":".$menit;
					}
					
					if($menit>=$emulai[1] and $jam.":".$menit>=00){
						$jamlembur[$jam.":".$menit]=$jam.":".$menit;
					}
				}
			}
		}else{
			$rangelb=range($emulai[0],$eselesai[0]);
			foreach($rangelb as $jam){
				$jam = addZero($jam,2);
				foreach($rangemnt as $menit){
					$menit = addZero($menit,2);
					if($menit>=$emulai[1] and $jam.":".$menit<$jamselesai){
						$jamlembur[$jam.":".$menit]=$jam.":".$menit;
					}
				}
			}
		}
		
		$jmshr = range(0,23);
		foreach($jmshr as $jam){
			$jam = addZero($jam,2);
			foreach($rangemnt as $menit){
				$menit = addZero($menit,2);
				$jamsehari[$jam.":".$menit]=$jam.":".$menit;
			}
		}
		$jumlahjamkerja=0;
		$lbrdijamkerja=0;
		foreach($jamkerja as $jam){
			$jumlahjamkerja++;
			if($jamlembur[$jam]!=""){
				$lbrdijamkerja++;
				$jamberapa.=$lbrdijamkerja.". ".$jam."<br>";
			}
		}
		
		$libur="";
		if(strtolower(date('D', strtotime($param['tgl'])))=='sun'){
			$libur=1;
		}
		if(substr($jamshift['namashift'],0,3)=='LBR'){
			$libur=1;
		}
		$where="";
		$kodeorg=substr($param['kdorg'],0,4);
		$tipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
		if($tipe[$kodeorg]=='HOLDING'){
			$where=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$kodeorg."')";			
		}else{
			$where=" and (kebun='GLOBAL' or kebun='".$kodeorg."')";			
		}
		
		$day = date('D', strtotime($param['tgl']));
		$sql = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$param['tgl']."' ".$where.""; #exit("error".$sql);
		$req = fetchdata($sql);
		if(@$req[0]['keterangan']=='libur'){
			$libur=1;
		} else if (($day=='Sun' and @$req[0]['keterangan']=='') or @$req[0]['keterangan']=='libur'){
			$libur=1;
		}

		$sql = "select * from ".$dbname.".sdm_5jamkerja where hari ='".$day."' and kodeunit='".$tipe[$kodeorg]."'"; 
		$req = fetchdata($sql);
		if(count($req)==0){
			$sql = "select * from ".$dbname.".sdm_5jamkerja where hari ='".$day."' and kodeunit='DEFAULT'"; 
			$req = fetchdata($sql);
		}
		
		foreach($req as $val){
			$jamkerjasetup=$val['jamkerja']*60;
		}
		$bolehlbrjamkerja = ($jumlahjamkerja - $jamkerjasetup) - 2;
		
		
		
		
		// $b=strtotime("2022-04-04 13:59");
		// $a=strtotime("2022-04-04 14:00");
		// $b=strtotime("16:30");
		
		// echo $b." = ".$a;
		
		// // if($a<$b){
		// // }
		
		
		
		// echo $selisihjam/60;
		// echo $lbrdijamkerja."<br>";
		// echo $bolehlbrjamkerja."<br>";
		// echo $jamberapa."<br>";
		// // echo "<pre>";
		// // print_r($lbrdijamkerja);
		// exit("error");
		
		
		
		
		#di SDKM jam shift isinya 8 jam
		#senin sd jumat otomatis 1 jam lembur
		#sabtu otomatis 3 jam lembur
		
		
		if($lbrdijamkerja>0 and $libur==""){
			if($lbrdijamkerja>$bolehlbrjamkerja){
				//exit("errorcode : Tidak diperbolehkan lembur dijam kerja.");
			}
			
			// if(substr($jamshift['namashift'],0,4)=='MILL' and $kodeorg=='SDKM'){
				// #khusus SDKM
				// if($day=='Sat'){
					// #khusus sabtu 3 jam
					// if($lbrdijamkerja>180){						
						// exit("errorcode : 1. Tidak diperbolehkan lembur dijam kerja.");
					// }
				// }else{
					// #senin sd jumat 1 jam
					// if($lbrdijamkerja>60){						
						// exit("errorcode : 2. Tidak diperbolehkan lembur dijam kerja.");
					// }
				// }
			// }else{				
				// exit("errorcode : Tidak diperbolehkan lembur dijam kerja.");
			// }
		}
		
		#validasi lembur > 40 jam
		$mingguini = date('W', strtotime(tglkemarin($param['tgl'])));
		$bulanlalu = tglbulanlalu(tglkemarin($param['tgl']));
		$rangetgl  = rangeTanggal($bulanlalu,tglkemarin($param['tgl']));
		foreach($rangetgl as $tanggal){
			if(date('W', strtotime($tanggal))==$mingguini){
				$tglweek[$tanggal]=$tanggal;
			}
		}
		
		$ttljaminput=0;
		foreach($_SESSION['lembur'] as $key => $row){
			if($row['kodeorg'] == $param['kdorg'] and $row['tanggal'] == $param['tgl'] and $row['karyawanid'] == $param['karyawanid']){
				$ttljaminput+=$row['jumlah'];
			}
		}
		
		$str = "select sum(jamaktual) as jamaktual from " . $dbname . ".sdm_lemburdt where karyawanid='".$param['karyawanid']."' and tanggal in ('".implode("','",$tglweek)."')";
		$res = fetchdata($str);
		if(($res[0]['jamaktual']+$ttljaminput+$param['jumlah'])>40){
			//exit("errorcode : Maksimal lembur seminggu adalah 40 Jam.");
		}
		

		
		// echo"<pre>";
		// print_r($jamkerja);
		// exit("error");
		
		$newdata = array();
		$newdata = array(
			'kodeorg'   =>$param['kdorg'],
			'tanggal'   =>$param['tgl'],
			'karyawanid'=>$param['karyawanid'],
			'jammulai'  =>addZero($emulai[0],2).":".addZero($emulai[1],2),
			'jamselesai'=>addZero($eselesai[0],2).":".addZero($eselesai[1],2),
			'jumlah'    =>$param['jumlah'],
			'ket'       =>$param['ket']
		);
		if($_SESSION['lembur'] != array()){
			foreach($_SESSION['lembur'] as $key=>$row){
				if($row['kodeorg'] == $param['kdorg'] and $row['tanggal'] == $param['tgl'] and $row['karyawanid'] == $param['karyawanid'] and $row['jammulai'] == addZero($emulai[0],2).":".addZero($emulai[1],2)){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			if($newdata['jumlah']!=''){
				array_push($_SESSION['lembur'],$newdata);
			}
		}else{
			if($newdata['jumlah']!=''){
				array_push($_SESSION['lembur'],$newdata);
			}
		}
		
		foreach($_SESSION['lembur'] as $key=>$row){
			if($row['kodeorg'] == $param['kdorg'] and $row['tanggal'] == $param['tgl'] and $row['karyawanid'] == $param['karyawanid']){
				$arrmulai[$row['jammulai']]=$row['jammulai'];
				$arrselesai[$row['jamselesai']]=$row['jamselesai'];
			}
		}
		
		sort($arrmulai);
		sort($arrselesai);
		$no=0;
		foreach($arrmulai as $mulai){
			$no++;
			if($no==1){				
				$jammulai=$mulai;
			}
		}
		foreach($arrselesai as $selesai){
			$jamselesai=$selesai;
		}
		
		foreach($_SESSION['lembur'] as $key => $row){
			if($row['kodeorg'] == $param['kdorg'] and $row['tanggal'] == $param['tgl'] and $row['karyawanid'] == $param['karyawanid']){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:right'>".$row['jammulai']."</td>";
				$tab.="<td style='text-align:right'>".$row['jamselesai']."</td>";
				$tab.="<td style='text-align:right'>".number_format($row['jumlah'],2)."</td>";
				$tab.="<td style='text-align:left'>".$row['ket']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletesession('".$key."','".$row['kodeorg']."','".$row['tanggal']."','".$row['karyawanid']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
				$ttl+=$row['jumlah'];
			}
		}
		$tab.="<tr class='rowcontent'>";
		$tab.="<td style='text-align:center' colspan=3>TOTAL</td>";
		$tab.="<td style='text-align:right'>".number_format($ttl,2)."</td>";
		$tab.="<td style='text-align:right'></td>";
		$tab.="<td style='text-align:right'></td>";
		$tab.="</tr><input hidden id=ttljampop value=".$ttl.">";
		
		
			
		echo $jammulai."##".$jamselesai."##".$tab."##".$ttl."##".$param['ket'];
		// echo"<pre>";
		// print_r($arrselesai);
	break;
    case'deletesession':
		unset($_SESSION['lembur'][$param['key']]);
	
		foreach($_SESSION['lembur'] as $key => $row){
			if($row['kodeorg'] == $param['kdorg'] and $row['tanggal'] == $param['tgl'] and $row['karyawanid'] == $param['karyawanid']){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:right'>".$row['jammulai']."</td>";
				$tab.="<td style='text-align:right'>".$row['jamselesai']."</td>";
				$tab.="<td style='text-align:right'>".number_format($row['jumlah'],2)."</td>";
				$tab.="<td style='text-align:left'>".$row['ket']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletesession('".$key."','".$row['kodeorg']."','".$row['tanggal']."','".$row['karyawanid']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
				$ttl+=$row['jumlah'];
			}
		}
		$tab.="<tr class='rowcontent'>";
		$tab.="<td style='text-align:center' colspan=3>TOTAL</td>";
		$tab.="<td style='text-align:right'>".number_format($ttl,2)."</td>";
		$tab.="<td style='text-align:right'></td>";
		$tab.="<td style='text-align:right'></td>";
		$tab.="</tr><input hidden id=ttljampop value=".$ttl.">";
		echo $tab;
		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['lembur']);
		// exit("error");
	break;
    case'loaddatasession':
		$param['tgl']=tanggalsystemn($param['tgl']);
		foreach($_SESSION['lembur'] as $key => $row){
			if($row['kodeorg'] == $param['kdorg'] and $row['tanggal'] == $param['tgl'] and $row['karyawanid'] == $param['karyawanid']){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:right'>".$row['jammulai']."</td>";
				$tab.="<td style='text-align:right'>".$row['jamselesai']."</td>";
				$tab.="<td style='text-align:right'>".number_format($row['jumlah'],2)."</td>";
				$tab.="<td style='text-align:left'>".$row['ket']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletesession('".$key."','".$row['kodeorg']."','".$row['tanggal']."','".$row['karyawanid']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
				$ttl+=$row['jumlah'];
			}
		}
		$tab.="<tr class='rowcontent'>";
		$tab.="<td style='text-align:center' colspan=3>TOTAL</td>";
		$tab.="<td style='text-align:right'>".number_format($ttl,2)."</td>";
		$tab.="<td style='text-align:right'></td>";
		$tab.="<td style='text-align:right'></td>";
		$tab.="</tr><input hidden id=ttljampop value=".$ttl.">";
		echo $tab;
		// echo"<pre>";
		// print_r($_SESSION['lembur']);
		
		// exit("error");
	break;
    case'hitungjam':
		$param['tgl']=tanggalsystemn($param['tgl']);
		$waktuawal   = $param['tgl']." ".$param['mulai'];
		$waktuakhir  = $param['tgl']." ".$param['selesai'];
		$diff        = (strtotime($waktuakhir)-strtotime($waktuawal));
		$years       = floor($diff / (365*60*60*24));
		$months      = floor(($diff - $years * 365*60*60*24)/(30*60*60*24));
		$days        = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		
		$hari        = floor($diff/(60*60*24));
		$jam         = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
		$menit       = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
		
		// echo $param['mulai'];
		// echo $param['selesai'];
		echo $jam+($menit/60);
		
		// exit("error");
	break;
	
	
    case'cekData':
		try {
		$owlPDO->beginTransaction();
	
			$divisi="";
			if(strlen($kdOrg)>4){
				$divisi=$kdOrg;
			}
			validasiInput(substr($kdOrg,0,4),$divisi,'LBR',tanggalsystemn(tanggalnormal($tgl)),$exit='0');
			validasiLembur($krywnId,tanggalsystemn(tanggalnormal($tgl)),$Jam,$tpLmbr, $exit='0');
			
			if(tanggalsystemn(tanggalnormal($tgl))>date("Y-m-d")){
				throw new PDOException("Tanggal lebih besar dari tanggal sekarang.");
			}
			
			$_SESSION['temp']['OrgKd2'] = $kdOrg;
			$sCek = "select kodeorg,tanggal from " . $dbname . ".sdm_lemburht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=owlBaris($qCek);
			if ($rCek < 1) {
				$sIns = "insert into " . $dbname . ".sdm_lemburht (`kodeorg`,`tanggal`) values ('" . $kdOrg . "','" . $tgl . "')";
				$owlPDO->exec($sIns);
				if (($tpLmbr != '') && ($Jam != '')) {
					if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){						
						if($param['noakun']==''){
							throw new PDOException("No akun wajib diisi.");
						}
					}
					
					$sDetIns = "insert into " . $dbname . ".sdm_lemburdt (`kodeorg`,`tanggal`,`karyawanid`,`tipelembur`,`jamaktual`,`uangmakan`,`uangtransport`,`uangkelebihanjam`,`jammulai`,`jamselesai`,`ket`,`noakun`,`alokasi`) values ('" . $kdOrg . "','" . $tgl . "','" . $krywnId . "','" . $tpLmbr . "','" . $Jam . "','" . $ungMkn . "','" . $ungTrans . "','" . $ungLbhjm . "','" . $jammulai . "','" . $jamselesai . "','" . $ket . "','".$param['noakun']."','".$param['alokasi']."')";
					$owlPDO->exec($sDetIns); 
				}else {
					if ($_SESSION['language'] == 'ID') {
						throw new PDOException("Masukkan tipe lembur dan basis jam.");
					} else {
						throw new PDOException("Please choose overtime type and actual hours.");
					}
				}
			} else { 
				if (($tpLmbr != '') && ($Jam != '')) {
					$str = "select kodeorg, tanggal, karyawanid from ".$dbname.".sdm_lemburdt where kodeorg ='".$kdOrg."' and
					tanggal='".$tgl."' and karyawanid='".$krywnId."' ";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					
					if($bar <=0){
						if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){						
							if($param['noakun']==''){
								throw new PDOException("No akun wajib diisi.");
							}
						}
						
						$sDetIns = "insert into " . $dbname . ".sdm_lemburdt (`kodeorg`,`tanggal`,`karyawanid`,`tipelembur`,`jamaktual`,`uangmakan`,`uangtransport`,`uangkelebihanjam`,`jammulai`,`jamselesai`,`ket`,`noakun`,`alokasi`) values ('" . $kdOrg . "','" . $tgl . "','" . $krywnId . "','" . $tpLmbr . "','" . $Jam . "','" . $ungMkn . "','" . $ungTrans . "','" . $ungLbhjm . "','" . $jammulai . "','" . $jamselesai . "','" . $ket . "','".$param['noakun']."','".$param['alokasi']."')";
						$owlPDO->exec($sDetIns); 
					}else{
						throw new PDOException("Data karyawan tersebut sudah pernah diinput, silahkan cek pada List Data dibawah !");
					}
				}else {
					if ($_SESSION['language'] == 'ID') {
						throw new PDOException("Masukkan tipe lembur dan basis jam.");
					} else {
						throw new PDOException("Please choose overtime type and actual hours.");
					}
				}
			}
			
			$str = "select * from ".$dbname.".sdm_lemburdt2 where kodeorg='".$kdOrg."' and tanggal='".tanggalsystemn(tanggalnormal($tgl))."' and karyawanid='".$krywnId."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				 $query = "delete from " . $dbname . ".sdm_lemburdt2 where id='".$bar['id']."'";
				 $owlPDO->exec($query);
			}
			
			foreach($_SESSION['lembur'] as $key => $row){
				if($row['kodeorg'] == $kdOrg and $row['tanggal'] == tanggalsystemn(tanggalnormal($tgl)) and $row['karyawanid'] == $krywnId){
					
					$exmulai = explode(":",$row['jammulai']);
					$exselesai = explode(":",$row['jamselesai']);
					
					$data = array(
							'kodeorg'   => $row['kodeorg'],
							'tanggal'   => $row['tanggal'],
							'karyawanid'=> $row['karyawanid'],
							'jammulai'  => addZero($exmulai[0],2).":".addZero($exmulai[1],2),
							'jamselesai'=> addZero($exselesai[0],2).":".addZero($exselesai[1],2),
							'jumlah'    => $row['jumlah'],
							'ket'       => $row['ket']
					);
					$query = insertQuery($dbname,'sdm_lemburdt2',$data,array_keys($data));
					$owlPDO->exec($query);
					
					unset($_SESSION['lembur'][$key]);
				}
			}
			
			//exit("Error:$tgl");
			$per = substr($tgl, 0, 4) . '-' . substr($tgl, 4, 2);
			$iLembur = "select sum(uangkelebihanjam) as lembur from " . $dbname . ".sdm_lemburdt where karyawanid='" . $krywnId . "'"
					. " and tanggal like '%" . $per . "%' ";
			$nLembur=$owlPDO->query($iLembur) or die(print " Gagal: ".PDOException::getMessage());
			$nLembur->setFetchMode(PDO::FETCH_ASSOC);
			$dLembur = $nLembur->fetch();
			$lembur = $dLembur['lembur'];
			
			$iGaji = "select jumlah from " . $dbname . ".sdm_5gajipokok where tahun='".$per."' and karyawanid='" . $krywnId . "'  and idkomponen=1";
			$nGaji=$owlPDO->query($iGaji) or die(print " Gagal: ".PDOException::getMessage());
			$nGaji->setFetchMode(PDO::FETCH_ASSOC);
			$dGaji = $nGaji->fetch();
			$gaji = $dGaji['jumlah'] * (35 / 100);


			$whKar = "karyawanid='" . $krywnId . "'";
			$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whKar);

			if ($lembur > $gaji) {
				// echo "Lembur untuk karyawan $nmKar[$krywnId] di periode $per telah melebihi 35%  ";
			}
			
			//exit("error masuk");
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
    break;

    case'savedt':
		try {
		$owlPDO->beginTransaction();
		
		$divisi="";
		if(strlen($kdOrg)>4){
			$divisi=$kdOrg;
		}
		validasiInput(substr($kdOrg,0,4),$divisi,'LBR',tanggalsystemn(tanggalnormal($tgl)),$exit='0');
		
		$sDet = "";
		$awl=0;
		for($arDt=0;$arDt<$_POST['totRow'];$arDt++) {
			if(($_POST['tpLembur'][$arDt]!='')&&($_POST['jamlmbr'][$arDt]!='')){
				$sDet="insert into ".$dbname.".sdm_lemburdt (`kodeorg`,`tanggal`,`karyawanid`,`tipelembur`,`jamaktual`,`uangmakan`,`uangtransport`,`uangkelebihanjam`,`jammulai`,`jamselesai`,`ket`,`notransaksisp`,`noakun`,`alokasi`) values";
				$awl++;

				$sCek = "select kodeorg,tanggal from " . $dbname . ".sdm_lemburht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
				$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
				$qCek->setFetchMode(PDO::FETCH_ASSOC);
				$rCek=owlBaris($qCek);
				if ($rCek== 0) {
					$sIns = "insert into " . $dbname . ".sdm_lemburht (`kodeorg`,`tanggal`,`notransaksisp`) values ('" . $kdOrg . "','" . $tgl . "','')";
					$owlPDO->exec($sIns);
				}

				if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){					
					if($_POST['noakun'][$arDt]==''){
						throw new PDOException("No akun wajib diisi.");
					}
				}
				$sCek = "select * from " . $dbname . ".sdm_lemburdt where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "' and karyawanid='" .$_POST['kar'][$arDt]. "'";
				$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
				$qCek->setFetchMode(PDO::FETCH_ASSOC);
				$rCek=owlBaris($qCek);
				if ($rCek== 0) {
					validasiLembur($_POST['kar'][$arDt],tanggalsystemn(tanggalnormal($tgl)),$_POST['jamlmbr'][$arDt],$_POST['tpLembur'][$arDt], $exit='0');
					
					if($_POST['keterangan'][$arDt]=='' and $_POST['uang_lbh'][$arDt]>0){
						throw new PDOException("Keterangan wajib diisi.");
					}
					if($_POST['jamlmbr'][$arDt]>$_POST['ttljam'][$arDt] and $_POST['uang_lbh'][$arDt]>0){
						// echo $_POST['jamlmbr'][$arDt];
						// echo $_POST['ttljam'][$arDt];
						
						throw new PDOException("Masih ada jumlah jam lembur kurang dari Basis Jam Lembur.");
					}
					
					
					if($_POST['uang_lbh'][$arDt]<=0 and $_POST['jamlmbr'][$arDt]>0){
						$whrlm="kodeorg='".substr($kdOrg,0,4)."' and tipelembur='".$_POST['tpLembur'][$arDt]."' and jamaktual='".$_POST['jamlmbr'][$arDt]."'";
						$optJamLembur=makeOption($dbname, 'sdm_5lembur','jamaktual,jamlembur',$whrlm);

						$per = substr($tgl, 0, 4) . '-' . substr($tgl, 4, 2);

						$sGt = "select sum(jumlah) as gapTun from ".$dbname.".sdm_5gajipokok where karyawanid='".$_POST['kar'][$arDt]."' and idkomponen in (1) and tahun='".$per."'";
						$qGt = fetchData($sGt);
						$gpsebulan = $qGt[0]['gapTun'];
						$gajiperjam = ($gpsebulan / 173);
						$jamlbr 	= $optJamLembur[$_POST['jamlmbr'][$arDt]];
						$_POST['uang_lbh'][$arDt] = round($gajiperjam * $jamlbr,0);
					}
					// exit("error");
					
					if($awl==1){
						$awl+=1;
						$sDet.=" ('".$kdOrg."','".$tgl."','".$_POST['kar'][$arDt]."','".$_POST['tpLembur'][$arDt]."','".$_POST['jamlmbr'][$arDt]."','".intval($ungMkn)."','".intval($ungTrans)."','".str_replace(",","",$_POST['uang_lbh'][$arDt])."','".$_POST['jam_mulai'][$arDt]."','".$_POST['jam_selesai'][$arDt]."','".$_POST['keterangan'][$arDt]."','".$_POST['notransp'][$arDt]."','".$_POST['noakun'][$arDt]."','".$_POST['alokasi'][$arDt]."')";
					}else{
						$awl+=1;
						$sDet.=",('".$kdOrg."','".$tgl."','".$_POST['kar'][$arDt]."','".$_POST['tpLembur'][$arDt]."','".$_POST['jamlmbr'][$arDt]."','".intval($ungMkn)."','".intval($ungTrans)."','".str_replace(",","",$_POST['uang_lbh'][$arDt])."','".$_POST['jam_mulai'][$arDt]."','".$_POST['jam_selesai'][$arDt]."','".$_POST['keterangan'][$arDt]."','".$_POST['notransp'][$arDt]."','".$_POST['noakun'][$arDt]."','".$_POST['alokasi'][$arDt]."')";
					}
				}else{
					throw new PDOException($nmkarya[$_POST['kar'][$arDt]].' pada tanggal '.tanggalnormal($tgl).' sudah pernah diinput.');
				}
			}
		}

		if ($awl > 0 && $sDet != "") {
			$owlPDO->exec($sDet); 
		}

		for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
            if(($_POST['tpLembur'][$arDt]!='')&&($_POST['jamlmbr'][$arDt]!='')){
				
				$str = "select * from ".$dbname.".sdm_lemburdt2 where kodeorg='".$kdOrg."' and tanggal='".tanggalsystemn(tanggalnormal($tgl))."' and karyawanid='".$_POST['kar'][$arDt]."'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$query = "delete from " . $dbname . ".sdm_lemburdt2 where id='".$bar['id']."'";
					$owlPDO->exec($query);
				}
				
				foreach($_SESSION['lembur'] as $key => $row){
					if($row['kodeorg'] == $kdOrg and $row['tanggal'] == tanggalsystemn(tanggalnormal($tgl)) and $row['karyawanid'] == $_POST['kar'][$arDt]){
						
						$exmulai = explode(":",$row['jammulai']);
						$exselesai = explode(":",$row['jamselesai']);
						
						$data = array(
								'kodeorg'   => $row['kodeorg'],
								'tanggal'   => $row['tanggal'],
								'karyawanid'=> $row['karyawanid'],
								'jammulai'  => addZero($exmulai[0],2).":".addZero($exmulai[1],2),
								'jamselesai'=> addZero($exselesai[0],2).":".addZero($exselesai[1],2),
								'jumlah'    => $row['jumlah'],
								'ket'       => $row['ket']
						);
						$query = insertQuery($dbname,'sdm_lemburdt2',$data,array_keys($data));
						$owlPDO->exec($query);
						
						unset($_SESSION['lembur'][$key]);
					}
				}
			}
		}	
        
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}

    break;

    case'loadNewData':
        echo"<table cellspacing='1' cellpadding=5 border='0' class='sortable' style='width:100%;'>
			<thead>
			<tr class=rowheader>
			<th align=center>No.</th>
			<th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
			<th align=center>" . $_SESSION['lang']['namaorganisasi'] . "</th>
			<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
			<th align=center colspan=2>" . $_SESSION['lang']['hari'] . "</th>
			<th align=center>" . $_SESSION['lang']['nopengajuan'] . "</th>
			<th align=center>" . $_SESSION['lang']['status'] . "</th>
			<th align=center colspan=6>Action</th>
			</tr>
			</thead><tbody>";
        $limit = 15;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        @$offset = $page * $limit;
        @$maxdisplay = ($page * $limit);
        $no = $maxdisplay;
		
		$where='';
		if ($tgl != '') {
            $where = " and tanggal='" . tanggalsystemn(tanggalnormal($tgl)) . "'";
        }
		if ($kdOrg != '') {
            $where.= " and kodeorg ='" . $kdOrg . "'";
        }else{
            $where.= " and kodeorg like '%" . $_SESSION['empl']['lokasitugas'] . "%'";
		}
		
		
        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_lemburht where left(kodeorg,4) in  ".$listOrg . " ".$where." order by `tanggal` desc";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

		$fileup=array();
        $sql = "select distinct(notransaksi) as notransaksi from " . $dbname . ".listfileupload where 1=1 and notransaksi in (select id from " . $dbname . ".sdm_lemburht where left(kodeorg,4) in  ".$listOrg . " ".$where.") and kriteriaefil='LBR'";
        $res = fetchdata($sql);
		foreach ($res as $bar) {
			$fileup[$bar['notransaksi']]=$bar['notransaksi'];
		}
		
		
        $slvhc = "select * from " . $dbname . ".sdm_lemburht where left(kodeorg,4) in  ".$listOrg . " ".$where." order by `tanggal` desc limit " . $offset . "," . $limit . "";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        $user_online = $_SESSION['standard']['userid'];
        while ($rlvhc = $qlvhc->fetch()) {
            $thnPeriod = substr($rlvhc['tanggal'], 0, 7);

            $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $rlvhc['kodeorg'] . "'";
			$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
			$qOrg->setFetchMode(PDO::FETCH_ASSOC);
            $rOrg = $qOrg->fetch();
            $sGp = "select DISTINCT sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($rlvhc['kodeorg'],0,4) . "' and periode='" . $thnPeriod . "' and tanggalmulai<='" . $rlvhc['tanggal'] . "' and tanggalsampai>='" . $rlvhc['tanggal'] . "'";
			$qGp=$owlPDO->query($sGp) or die(print " Gagal: ".PDOException::getMessage());
			$qGp->setFetchMode(PDO::FETCH_ASSOC);
            $rGp = $qGp->fetch();
			
			$where="";
			$kodeorg=substr($rlvhc['kodeorg'],0,4);
			$tipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
			if($tipe[$kodeorg]=='HOLDING'){
				$where=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$kodeorg."')";			
			}else{
				$where=" and (kebun='GLOBAL' or kebun='".$kodeorg."')";			
			}
			
			$cl="";$title="";
			if(strtolower(date('D', strtotime($rlvhc['tanggal'])))=='fri'){
				$cl="color:blue;";
			}elseif(strtolower(date('D', strtotime($rlvhc['tanggal'])))=='sun'){
				$cl="color:red;font-weight:bold;";
				$title="title=\"".hari($rlvhc['tanggal'])."\"";
			}elseif(strtolower(date('D', strtotime($rlvhc['tanggal'])))=='sat'){
				$cl="color:orange;font-weight:bold;";
			}
			$day = date('D', strtotime($rlvhc['tanggal']));
			$sql = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$rlvhc['tanggal']."' ".$where.""; #exit("error".$sql);
			$req = fetchdata($sql);
			if(@$req[0]['keterangan']=='libur'){
				$cl="color:red;font-weight:bold;"; $title="title=\"".$req[0]['catatan']."\"";
			} else if (($day=='Sun' and @$req[0]['keterangan']=='') or @$req[0]['keterangan']=='libur'){
				$cl="color:red;font-weight:bold;"; $title="title=\"".$req[0]['catatan']."\"";
			}
			
			
            $no+=1;
            echo"
                <tr class=rowcontent height=20px>
                <td align=center>" . $no . "</td>
                <td align=center>" . $rlvhc['kodeorg'] . "</td>
                <td>" . $rOrg['namaorganisasi'] . "</td>
                <td style=text-align:center;".$cl." ".$title.">" . tanggalnormal($rlvhc['tanggal']) . "</td>
                <td style=text-align:center;".$cl." ".$title.">".hari($rlvhc['tanggal'])."</td>
                <td style=text-align:left;".$cl.">".$req[0]['catatan']."</td>
                <td style=cursor:pointer;color:blue; title=\"Click untuk melihat detail approval.\" onclick=gethistoriapproval('".$rlvhc['nopengajuan']."','event')>" . $rlvhc['nopengajuan'] . "</td>";
			if($rlvhc['posting']==2){
				echo"<td style=color:red><b>" . $arrHsl[$rlvhc['posting']]."</b></td>";
			}elseif($rlvhc['posting']==1){
				echo"<td style=color:blue><b>" . $arrHsl[$rlvhc['posting']]."</b></td>";
			}else{				
				echo"<td>" . $arrHsl[$rlvhc['posting']]."</td>";
			}
            if ((@$rlvhc['posting'] == 0 || $rlvhc['posting'] == 2) and @$rGp['sudahproses']== 0) {
				echo"<td width=25px align=center>";
                echo"<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\"></td><td width=25px align=center>
					
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\" >	</td><td width=25px align=center>";
                
				echo"<img src='images/skyblue/submit.jpg' class='zImgBtn'  title='Ajukan' onclick=\"postingx('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "')\"></td>";
				
				// if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
                    // echo"<img src='images/skyblue/posting.png' class='zImgBtn' title='Posting' ></td>";
                // }else{
                // }
            } else {
                echo"</td>";
                echo"<td width=25px align=center></td>";
                echo"<td width=25px align=center></td>";
				echo"<td width=25px align=center>";
				 if(in_array($_SESSION['empl']['kodejabatan'],$postJabatan) and $rlvhc['posting']=='1') {
                    echo"<img src='images/icons/04/16/04.png' class='zImgBtn'  title='Unposting' onclick=\"postingx('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "','".$rlvhc['posting']."')\"></td>";
                }else{
                    echo"</td>";
                }
            }
			
			$style=" style=width:25px; title=\"File pendukung belum diupload\"";
			if($fileup[$rlvhc['id']]!=''){
				$style=" style=width:25px;background-color:#68edaf; title=\"File pendukung sudah diupload\"";
			}
			echo"<td align=center ".$style."><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$rlvhc['id']."','".$rlvhc['kodeorg']."','".$rlvhc['tanggal']."');\" ></td>";
			echo"<td width=25px align=center>
					<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_lemburht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_slave_lemburPdf',event)\"></td>";
			echo"<td width=25px align=center>
					<img src=images/skyblue/zoom.png class=zImgBtn  title='Print' onclick=\"preview('".$rlvhc['kodeorg']."','".$rlvhc['tanggal']."','".$rlvhc['id']."')\">
				</td>";	
            echo"</td>
                </tr>
                ";
        }
        
		@$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = (floatval($page) == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        echo"</tr><tr><td colspan=14 align=center>";

        if (floatval($page) == '0') {
            echo"<button class=mybutton disabled=true>Prev</button>";
        } else {
            echo"<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>Prev</button>";
        }

        echo"<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if ((@floatval($page) + 1) == @$totrows) {
            echo"<button class=mybutton disabled=true>Next</button>";
        } else {
            echo"<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>Next</button>";
        }
        echo"</td>
            </tr>";
			
        echo"</tbody></table>";
	break;
	case'getapprovaldetail':
		$tab="";
		$notransaksi = checkPostGet('nopengajuan', '');
		$kodeorg = substr(checkPostGet('kodeorg', ''),0,4);
		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		
		$tab.="<span><b>Approval</b></span>";
		$tab.="<table  border=0 cellspacing=1 cellpadding=5 class=sortable width=100%>";
		$countApprove = getCountApproval('LBR',$kodeorg);
		$tab.= "<thead>
				<tr style='font-weight:bold'>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>";

		for($i=1;$i<=$countApprove;$i++){
			$arrApp = detailApprove($i,$notransaksi,'LBR');
			if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
				$tngl='';
			}else{
				$tngl=tanggalnormal($arrApp['tanggal']);
			}
			
			if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
				$tab.= "<td valign=top style='text-align:center'>".$arrApp['nama']."
						<br>".$arrHsl[$arrApp['status']]."
						<br>".$tngl."
						<br>".$arrApp['komentar']."
						</td>";
			}else{
				$tab.= "<td>&nbsp;</td>";
			}
		}
		$tab.= "</tbody></table>";
		
		
		#status tolak
		$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
		$res=fetchdata($str);
		$row=count($res);
		if($row>0){
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				$tab.="<br><table border=0 cellspacing=1 class=sortable>
						<thead>
						<tr style='font-weight:bold'>
							<td colspan='".($val['level'])."'>Return / Tolak - ".$no."</td>
						</tr>
						<tr style='font-weight:bold'>";
							for($i=1;$i<=$val['level'];$i++) {
								$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
							}
						$tab.="</tr>
					</thead>
					<tbody>
						<tr class=rowcontent>";
						for($i=1;$i<=$val['level'];$i++) {
							$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
							$resx=fetchdata($strx);
							$color='';
							if($resx[0]['status']==3){
								$color=" style=background-color:red ";
							}
							$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
								<br>	
								".$arrHsl[$resx[0]['status']]."
								<br>	
								".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
								<br>	
								".$resx[0]['komentar']."
							</td>";
						}
						$tab.="</tr>
					</tbody>
					</table>";
			}
		}
		echo $tab;
	break;
    case'delData':
        $sCek = "select posting from " . $dbname . ".sdm_absensiht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek = $qCek->fetch();
        if ($rCek['posting'] == '1') {
            echo"warning: This data has been confirmed, can not continue";
            exit();
        }
        $sDel = "delete from " . $dbname . ".sdm_lemburht where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'"; 
		try{
			$owlPDO->exec($sDel); 
			
			$sDelDetail = "delete from " . $dbname . ".sdm_lemburdt where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'";
			try{
				$owlPDO->exec($sDelDetail); 
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
        break;
	
	case'postingx':
		$kodeunit=substr($kdOrg,0,4);
		$prd=tanggalnormal($tgl);
		$prd=substr($prd,6,4)."-".substr($prd,3,2);
		
		if(substr($kdOrg,0,4)=='SDKM'){
			$nomr="001/HCM/SB-EH/VII/2020";
		}elseif(substr($kdOrg,0,4)=='KSPM'){
			$nomr="003/HCM/SB-EH/VII/2020";
		}elseif(substr($kdOrg,0,4)=='BPJM'){
			$nomr="002/HCM/SB-EH/VII/2020";
		}else{
			$nomr="001/HCM/SB-EH/VII/2020, 002/HCM/SB-EH/VII/2020 dan 003/HCM/SB-EH/VII/2020";
		}
		
		
		if($prd>='2020-07'){
			#exit("Warning : Lembur sudah tidak diperbolehkan lagi berdasarkan Memorandum dari Direksi dengan nomor : ".$nomr."\nsilahkan hapus transaksi ini untuk melanjutkan.");
		}
		
		
		
		$counttutup=0;
		##GET PERIODE PENGGAJIAN
		$str="select sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg='".$kodeunit."' and periode='".$prd."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['sudahproses']=='1'){
				$counttutup++;
			}
		}
		
		$str="select posting, nopengajuan, id from ".$dbname.".sdm_lemburht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'";
		$res=fetchdata($str);
		$valposting=$res[0]['posting'];
		$nopengajuan=$res[0]['nopengajuan'];
		$idnomor=$res[0]['id'];
		
		if($counttutup > 0){
			if($valposting=='1'){
				exit("Gagal, Tidak dapat unposting transaksi, Periode penggajian unit sudah tutup.");			
			}else{
				exit("Gagal, Tidak dapat posting transaksi, Periode penggajian unit sudah tutup.");			
			}
		}
		
		if($valposting=='1'){
			$sPosting = "update " . $dbname . ".sdm_lemburht set posting='0', postingby='0' where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'"; 
			try{
				$owlPDO->exec($sPosting); 
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
		}else{
			#$sPosting = "update " . $dbname . ".sdm_lemburht set posting='1', postingby='".$_SESSION['standard']['userid']."' where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "'"; 
			
			$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$idnomor."' and status='1' and kriteriaefil='LBR'";
			$res= fetchData($str);
			if(count($res)==''){
				exit("Gagal, Silahkan upload SPL terlebih dahulu.");
			}
			
			$unit=$kodeunit;
			$where="";
			
			$str = "select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='LBR' and a.level='1' and a.kodeunit='".substr($unit,0,4)."' ".$where." order by b.namakaryawan asc";
			$res = fetchdata($str);
			$optKry="";
			foreach($res as $rkry){
				$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
			}
			if($nopengajuan==''){			
				$nopengajuan=$kdOrg.date("YmdHis");
				$nopengajuan=$idnomor;
			}
			$tab.="<table cellspacing=1 border=0 cellpadding=3>
				<tr>
					<input hidden id=kodeorgapp value=".$kdOrg.">
					<input hidden id=tanggalapp value=".tanggalsystemn(tanggalnormal($tgl)).">
					<td>No. Pengajuan</td>
					<td>:</td>
					<td><input class=myinputtext style=width:205px type=\"text\" id=\"nopengajuan\" name=\"nopengajuan\" disabled value='".$nopengajuan."' /></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kepada']."</td>
					<td>:</td>
					<td><select style=width:210px  id=\"karywn_id\" name=\"karywn_id\">". $optKry."</select></td>
				</tr>
				<tr>
					<td><td><td>
						<button class=mybutton onclick=save_persetujuan() >".$_SESSION['lang']['diajukan']."</button>
					</td>
				</tr>
			</table>
			</fieldset>";
			echo $tab;
		}
		
		
		
	break;
    case'save_persetujuan':
	try {
		$owlPDO->beginTransaction();

		if($param['karyawanid']==''){
			throw new PDOException("Nama penyetuju harus diisi.");
		}
		if($param['karyawanid']=='0000000000'){
			throw new PDOException("Nama penyetuju harus diisi.");
		}
		$str="select max(nourut) as nourut from ".$dbname.".approval_return where jenispersetujuan='LBR' and notransaksi='".$param['nopengajuan']."' limit 1";
		$res=fetchdata($str);
		if($res[0]['nourut']!=''){			
			$urut=$res[0]['nourut']+1;
		}else{
			$urut=1;
		}
		
		//cari dulu apakah sudah pernah di ajukan sebelumnya
		$tglhi = date("Ymd");
		$str="select * from ".$dbname.".approval where jenispersetujuan='LBR' and notransaksi='".$param['nopengajuan']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['notransaksi']!=''){
				# jika ada pindahkan ke table ini
				$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`, `nourut`)
				values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."','".$urut."')";
				$owlPDO->exec($str);
			}
		}
		
		#kemudian setelah di pindah, hapus persetujuan lama
		$str="delete from ".$dbname.".approval where jenispersetujuan='LBR' and notransaksi='".$param['nopengajuan']."'";
		$owlPDO->exec($str);
		
		$data = array(
				'notransaksi'     => $param['nopengajuan'],
				'jenispersetujuan'=> 'LBR',
				'level'           => '1',
				'karyawanid'      => $param['karyawanid'],
				'status'          => '0',
				'komentar'        => '',
				'keterangan'      => '',
				'tanggal'         => date("Y-m-d H:i:s")
		);

		$query = insertQuery($dbname,'approval',$data,array_keys($data));
		$owlPDO->exec($query);
	
		$query = "update " . $dbname . ".sdm_lemburht set posting='9',nopengajuan='".$param['nopengajuan']."' , postingby='".$_SESSION['standard']['userid']."' where tanggal='" . $param['tanggal'] . "' and kodeorg='" . $param['kodeorg'] . "'"; 
		$owlPDO->exec($query);
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
    case'cekHeader':
        $thn = substr($tgl, 0, 4);
        $bln = substr($tgl, 4, 2);
        $periode = $thn . "-" . $bln;
		if(substr($kdOrg,0,4)=='SDKM'){
			$nomr="001/HCM/SB-EH/VII/2020";
		}elseif(substr($kdOrg,0,4)=='KSPM'){
			$nomr="003/HCM/SB-EH/VII/2020";
		}elseif(substr($kdOrg,0,4)=='BPJM'){
			$nomr="002/HCM/SB-EH/VII/2020";
		}else{
			$nomr="001/HCM/SB-EH/VII/2020, 002/HCM/SB-EH/VII/2020 dan 003/HCM/SB-EH/VII/2020";
		}
		
		$divisi="";
		if(strlen($kdOrg)>4){
			$divisi=$kdOrg;
		}
		validasiInput(substr($kdOrg,0,4),$divisi,'LBR',tanggalsystemn(tanggalnormal($tgl)),$exit='1');
		// exit("Warning : cek 1.");
		validasiLembur($krywnId,tanggalsystemn(tanggalnormal($tgl)),$jamlembur,"0", $exit='1');
		
		if(tanggalsystemn(tanggalnormal($tgl))>date("Y-m-d")){
			exit("Warning : Tanggal lebih besar dari tanggal sekarang.");
		}
		// if($periode>='2020-07'){
		// 	#exit("Warning : Lembur sudah tidak diperbolehkan lagi berdasarkan Memorandum dari Direksi dengan nomor : ".$nomr."");
		// }

        $str = "select * from " . $dbname . ".setup_periodeakuntansi where periode='" . $periode . "' and
                kodeorg='" . substr($kdOrg,0,4) . "' and tutupbuku=1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $numrows=owlBaris($res);
        if ($numrows > 0){
            $aktif = true;
        }
        else{
            $str = "select * from " . $dbname . ".setup_periodeakuntansi where periode='" . $periode . "' and
                kodeorg='" . substr($kdOrg,0,4) . "' and tutupbuku=0";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $numrows=owlBaris($res);
            if ($numrows > 0){
                $aktif = false;
            }
        }
        if ($aktif == true) {
            exit("Error : Accounting period has been closed to this date");
        }

        $str = "select * from " . $dbname . ".sdm_5periodegaji where periode='" . $periode . "' and
                kodeorg='" . substr($kdOrg,0,4) . "' and sudahproses=1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $numrows=owlBaris($res);
        if ($numrows > 0){
            $aktif = true;
        }
        else{
            $str = "select * from " . $dbname . ".sdm_5periodegaji where periode='" . $periode . "' and
                kodeorg='" . substr($kdOrg,0,4) . "' and sudahproses=0";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $numrows=owlBaris($res);
            if ($numrows > 0){
                $aktif = false;
            }
            else{
                exit("Error : Payroll period has not been created");
            }
        }
        if ($aktif == true) {
            exit("Error : Payroll period has been closed to this date");
        }


        $str = "select * from " . $dbname . ".sdm_5harilibur where tanggal='" . $tgl . "' and (kebun='GLOBAL' or kebun='".$kdOrg."')";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $numrows=owlBaris($res);
        echo $numrows;

        break;
		
    case'cariAbsn':
		if (($tgl != '') && ($kdOrg != '')) {
            $where = " kodeorg = '" . $kdOrg . "' and tanggal='" . $tgl . "'";
        } elseif ($kdOrg != '') {
            $where = " kodeorg ='" . $kdOrg . "'";
        } elseif ($tgl != '') {
            $where = "kodeorg like '%" . $_SESSION['empl']['lokasitugas'] . "%' and tanggal='" . $tgl . "'";
        } elseif (($tgl == '') && ($kdOrg == '')) {
            echo"warning: Please insert data";
            exit();
        }
        echo"<div style='overflow:auto;height:400px'>
			<table cellspacing='1' border='0' class='sortable'>
				<thead>
				<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td align=center>" . $_SESSION['lang']['namaorganisasi'] . "</td>
					<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
					<td align=center colspan='4'>Action</td>
				</tr>
				</thead><tbody>";
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        
        //paging data
        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_lemburht where " . $where . " order by `tanggal` desc"; // 
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        //query data
        $slvhc = "select * from " . $dbname . ".sdm_lemburht where " . $where . " order by `tanggal` desc limit " . $offset . "," . $limit . "";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        $user_online = $_SESSION['standard']['userid'];
        while ($rlvhc = $qlvhc->fetch()) {
			$thnPeriod = substr($rlvhc['tanggal'], 0, 7);
			
            $sOrg = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $rlvhc['kodeorg'] . "'";
			$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
			$qOrg->setFetchMode(PDO::FETCH_ASSOC);
            $rOrg = $qOrg->fetch();
            $sGp = "select DISTINCT sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($rlvhc['kodeorg'],0,4) . "' and periode='" . $thnPeriod . "' and tanggalmulai<='" . $rlvhc['tanggal'] . "' and tanggalsampai>='" . $rlvhc['tanggal'] . "'";
			$qGp=$owlPDO->query($sGp) or die(print " Gagal: ".PDOException::getMessage());
			$qGp->setFetchMode(PDO::FETCH_ASSOC);
            $rGp = $qGp->fetch();
            $no+=1;
            echo"
                <tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td>" . $rlvhc['kodeorg'] . "</td>
                <td>" . $rOrg['namaorganisasi'] . "</td>
                <td>" . tanggalnormal($rlvhc['tanggal']) . "</td>";
            
			if ($rlvhc['posting'] == 0 and $rGp['sudahproses'] == 0) {
                echo"<td>
					<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\">
				</td>
				<td>
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "');\" >	
				</td>
				<td>
					<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_lemburht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_slave_lemburPdf',event)\">
				</td>
				<td>
					<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"preview('".$rlvhc['kodeorg']."','".$rlvhc['tanggal']."')\">
				</td>";
				if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan)) {
					echo"<td>
						<img src='images/skyblue/posting.png' class='zImgBtn' title='Posting' ></td>
					</td>";
                }else{
					echo"<td>
						<img src='images/skyblue/posting.png' class='zImgBtn'  title='Posting' onclick=\"postingx('" . $rlvhc['kodeorg'] . "','" . tanggalnormal($rlvhc['tanggal']) . "')\">
					</td>";
                }
            } else {
                echo"<td colspan=2></td>
				<td>
					<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('sdm_lemburht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "','','sdm_slave_lemburPdf',event)\">
				</td>
				<td>
					<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"preview('".$rlvhc['kodeorg']."','".$rlvhc['tanggal']."')\">
				</td>
				<td>
					<img src='images/skyblue/posted.png' class='zImgBtn'  title='Posted'>
				</td>";
            }
            echo"</tr>";
        }
        echo"
                <tr class=rowheader><td colspan=5 align=center>
                " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                <button class=mybutton onclick=cariData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=cariData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        echo"</tbody></table></div>";

        break;
		
    case'updateDetail':
		try {
		$owlPDO->beginTransaction();
        if (($tpLmbr != '') && ($Jam != '')) {
			$divisi="";
			if(strlen($kdOrg)>4){
				$divisi=$kdOrg;
			}
			validasiInput(substr($kdOrg,0,4),$divisi,'LBR',tanggalsystemn(tanggalnormal($tgl)),$exit='0');
			validasiLembur($krywnId,tanggalsystemn(tanggalnormal($tgl)),$Jam,$tpLmbr,$exit='0');
			
			if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
				if($param['noakun']==''){
					throw new PDOException("No akun wajib diisi.");
				}
			}
            $sUp = "update " . $dbname . ".sdm_lemburdt set tipelembur='" . $tpLmbr . "',jamaktual='" . $Jam . "',uangmakan='" . $ungMkn . "',uangtransport='" . $ungTrans . "',uangkelebihanjam='" . $ungLbhjm . "',jammulai='" . $jammulai . "',jamselesai='" . $jamselesai . "',ket='" . $ket . "',noakun='".$param['noakun']."',alokasi='".$param['alokasi']."' where kodeorg='" . $kdOrg . "' and tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
			$owlPDO->exec($sUp); 
			
			
			$str = "select * from ".$dbname.".sdm_lemburdt2 where kodeorg='".$kdOrg."' and tanggal='".tanggalsystemn(tanggalnormal($tgl))."' and karyawanid='".$krywnId."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				 $query = "delete from " . $dbname . ".sdm_lemburdt2 where id='".$bar['id']."'";
				 $owlPDO->exec($query);
			}
			
			foreach($_SESSION['lembur'] as $key => $row){
				if($row['kodeorg'] == $kdOrg and $row['tanggal'] == tanggalsystemn(tanggalnormal($tgl)) and $row['karyawanid'] == $krywnId){
					
					$exmulai = explode(":",$row['jammulai']);
					$exselesai = explode(":",$row['jamselesai']);
					
					$data = array(
							'kodeorg'   => $row['kodeorg'],
							'tanggal'   => $row['tanggal'],
							'karyawanid'=> $row['karyawanid'],
							'jammulai'  => addZero($exmulai[0],2).":".addZero($exmulai[1],2),
							'jamselesai'=> addZero($exselesai[0],2).":".addZero($exselesai[1],2),
							'jumlah'    => $row['jumlah'],
							'ket'       => $row['ket']
					);
					$query = insertQuery($dbname,'sdm_lemburdt2',$data,array_keys($data));
					$owlPDO->exec($query);
					
					unset($_SESSION['lembur'][$key]);
				}
			}
			
        }else {
            if ($_SESSION['language'] == 'ID') {
                throw new PDOException("Masukkan tipe lembur dan basis jam");
            } else {
                throw new PDOException("Please choose overtime type and actual hours");
            }
        }
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	
	break;
    case'delDetail':
        $sDel = "delete from " . $dbname . ".sdm_lemburdt where kodeorg='" . $kdOrg . "' and tanggal='" . $tgl . "' and karyawanid='" . $krywnId . "'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
        break;
	case'getalokasi':
		$whr="";
		$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if(substr($param['noakun'],0,3)=='128'){
			$whr.=" and kodeorganisasi like '".$param['divisi']."%'";
			$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('BIBITAN'))";
		}elseif(substr($param['noakun'],0,3)=='126'){
			$whr.=" and kodeorganisasi like '".$param['divisi']."%'";
			$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('TBM','TB'))";
		}elseif(substr($param['noakun'],0,3)=='621'){
			$whr.=" and kodeorganisasi like '".$param['divisi']."%'";
			$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('TM'))";
		}elseif(substr($param['noakun'],0,3)=='611'){
			$whr.=" and kodeorganisasi like '".$param['divisi']."%'";
			$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('TM'))";
		}elseif(substr($param['noakun'],0,2)=='63'){
			$whr=" and kodeorganisasi like '".substr($param['divisi'],0,4)."%'";
			$whr.=" and tipe='STATION'";
		}else{
			$whr.=" and kodeorganisasi=''";
			$optBlok="<option value=''></option>";
		}
		
		$str = "select * from ".$dbname.".organisasi where 1=1 ".$whr." order by induk,kodeorganisasi asc";
		#exit("error".$str);
		$res=fetchdata($str);
		$n="";
		foreach($res as $bar){
			$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$nminduk[$bar['kodeorganisasi']];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optBlok.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optnmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			if($optnmorg[$bar['kodeorganisasi']]==$bar['kodeorganisasi']){
				$blkkry=substr($bar['kodeorganisasi'],6,4);
			}else{
				$blkkry=$optnmorg[$bar['kodeorganisasi']];
			}
			$e="";
			if($bar['kodeorganisasi']==$param['divisi']){
				$e="selected";
			}
			$optBlok.="<option value=".$bar['kodeorganisasi']." ".$e.">".$blkkry."</option>";
			$n=$d;
			if($d!=$n){
				$optBlok.="</optgroup>";
			}
		}
		echo $optBlok;
	break;
    case'createTable':
        if (strlen($kdOrg) > 4) {
            $where = " subbagian='" . $kdOrg . "'   and statuskaryawan != 'Keluar' and ((tanggalkeluar>" . $tgl . " or tanggalkeluar='0000-00-00') OR (tanggalkeluar!='0000-00-00' AND statuskaryawan = 'Percobaan'))";
        } else {
            $where = " lokasitugas='" . $kdOrg . "'  and statuskaryawan != 'Keluar' and (subbagian IS NULL or subbagian='0' or subbagian='') and ((tanggalkeluar>" . $tgl . " or tanggalkeluar='0000-00-00') OR (tanggalkeluar!='0000-00-00' AND statuskaryawan = 'Percobaan'))";
        }//namakaryawan,karyawanid,nik

        $optTipeKar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

        $strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist a where " . $where . " and tipekaryawan not in ('0','7','8')  and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' "; 
        $resdkar = fetchdata($strdkar);
        if(count($resdkar)>0){ 
			$sKry="select * from ".$dbname.".datakaryawan_hist where  ".$where." and tipekaryawan not in ('0','7','8') and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' order by tipekaryawan,namakaryawan asc";
        }else{
			$sKry="select * from ".$dbname.".datakaryawan where  ".$where." and tipekaryawan not in ('0','7','8') order by tipekaryawan, namakaryawan asc";
        }
		$optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
		$qKry->setFetchMode(PDO::FETCH_ASSOC);
        while ($rKry = $qKry->fetch()) {
			$d=$optTipeKar[$rKry['tipekaryawan']];
			if($rKry['nik']!=''){
				$rKry['nik']=" - ".$rKry['nik'];
			}
		
			if($d!=$n){			
				$optKry.="<optgroup label='".$d."'>";
			}
			$optKry.="<option value=".$rKry['karyawanid'].">".$rKry['namakaryawan'].$rKry['nik']."</option>";
			$n=$d;
			if($d!=$n){
				$optKry.="</optgroup>";
			}
			
            #$optKry.="<option value=" . $rKry['karyawanid'] . ">" . $rKry['namakaryawan'] . " [ " . $rKry['nik'] . " ] " . $optTipeKar[$rKry['tipekaryawan']] . "</option>";
        }
		$e=""; $k="style=display:none;";
		if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
			$e="style=display:none;"; $optJnsKerja=$optBlok="";
		}
        $table="
				<table id='ppDetailTable' cellspacing='1' cellpadding='5' border='0' class='sortable' style='width:100%;'>
                <thead>
                <tr class=rowheader>
                <th align=center>No</th>
                <th align=center colspan=2>" . $_SESSION['lang']['namakaryawan'] . "</th>
                <th align=center colspan=2 ".$e.">" . $_SESSION['lang']['akun'] . "</th>
                <th align=center colspan=2 ".$e." ".$k.">" . $_SESSION['lang']['alokasi'] . "</th>
                <th align=center>" . $_SESSION['lang']['tipelembur'] . "</th>
                <th align=center width=50px>".$_SESSION['lang']['jamaktual']. "</th>
                <th  align=center>" . $_SESSION['lang']['uangkelebihanjam'] . "</th>
                <th hidden align=center>" . $_SESSION['lang']['penggantiantransport'] . "</th>
                <th hidden align=center>" . $_SESSION['lang']['uangmakan'] . "</th>
                
				<th align=center>" . $_SESSION['lang']['jam'] . "<br>" . $_SESSION['lang']['mulai'] . "</th>
				<th align=center>" . str_replace(" ","<br>",$_SESSION['lang']['jamselesai']) . "</th>
                <th align=center width=50px>" . $_SESSION['lang']['jam'] . "<br>" . $_SESSION['lang']['mulai'] . " FP</th>
                <th align=center width=50px>" . str_replace(" ","<br>",$_SESSION['lang']['jamselesai']) . " FP</th>
				<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
                <th align=center >Action</th>
				</tr></thead>
                <tbody id='detailBody'>";
				
		$param['kodeorg']=substr($absnId[0],0,4);
		$param['divisi']=$absnId[0];
		$opttipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
		$whk="";$wh="";$whr="";
		if($opttipe[$param['kodeorg']]=='KEBUN'){
			$whr.=" and kodeorganisasi like '".$param['divisi']."%'";
			if(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='BIBITAN'){				
				$whk.=" and substr(kodekegiatan,1,3) in ('128')";
				$kdjurnal="KBNL0";
				$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('BIBITAN'))";
				$wh.=" and substr(noakun,1,2) in ('71')";
			}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='TRAKSI'){				
				$kdjurnal="VHCG0";
				$wh.=" and (substr(noakun,1,2) in ('71') or substr(noakun,1,5) in ('41102')) and noakun not in ('4110299')";
			}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='WORKSHOP'){				
				$kdjurnal="WSG0";
				$wh.=" and (substr(noakun,1,2) in ('71') or substr(noakun,1,5) in ('41101')) and noakun not in ('4110199')";
			}else{				
				$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('TBM','TM','TB'))";
				$whk.=" and substr(kodekegiatan,1,3) in ('126','621','611')";
				$kdjurnal="KBNB0";
				$wh.=" and substr(noakun,1,2) in ('71')";
			}
		}elseif($opttipe[$param['kodeorg']]=='PABRIK'){
			if(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='TRAKSI'){				
				$kdjurnal="VHCG0";
			}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='WORKSHOP'){				
				$kdjurnal="WSG0";
			}else{				
				$kdjurnal="PKS01";
			}
			$whr.=" and kodeorganisasi like '".$param['kodeorg']."%'";
			$whr.=" and tipe='STATION'";
			$wh.=" and (substr(noakun,1,2) in ('71') or substr(noakun,1,2) in ('63'))";
		}elseif($opttipe[$param['kodeorg']]=='BULKING'){
			$kdjurnal="BLK01";
			$wh.=" and substr(noakun,1,2) in ('81')";
		}elseif($opttipe[$param['kodeorg']]=='RND' or $opttipe[$param['kodeorg']]=='TC'){
			$kdjurnal="RNDB0";
			$wh.=" and substr(noakun,1,2) in ('82')";
		}elseif($opttipe[$param['kodeorg']]=='HOLDING'){
			$kdjurnal="GJHO0";
			$wh.=" and substr(noakun,1,2) in ('82')";
		}else{
			$kdjurnal="";
			$wh.=" and substr(noakun,1,2) in ('82')";
		}
		
		$optBlok="<option value=''></option>";
		$str = "select * from ".$dbname.".organisasi where 1=1 ".$whr." order by induk,kodeorganisasi asc";
		#exit("error".$str);
		$res=fetchdata($str);
		$n="";
		foreach($res as $bar){
			$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$nminduk[$bar['kodeorganisasi']];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optBlok.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optnmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			if($optnmorg[$bar['kodeorganisasi']]==$bar['kodeorganisasi']){
				$blkkry=substr($bar['kodeorganisasi'],6,4);
			}else{
				$blkkry=$optnmorg[$bar['kodeorganisasi']];
			}
			$e="";
			if($bar['kodeorganisasi']==$param['divisi']){
				$e="selected";
			}
			$optBlok.="<option value=".$bar['kodeorganisasi']." ".$e.">".$blkkry."</option>";
			$n=$d;
			if($d!=$n){
				$optBlok.="</optgroup>";
			}
		}
		
		
		#PABRIK => %63%, 7%
		#KANWIL => 82%
		#RND => 82%
		#TC => 82%
		#BULKING => 81%
		$optakun=makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='".$kdjurnal."'");
		$akun=$optakun[$kdjurnal];
		$sjnskrj="select * from ".$dbname.".keu_5akun where length(noakun)='7' and namaakun not like '%NON AKTIF%' ".$wh." and aktif='1' order by noakun asc";
		// exit("error".$akun);
		$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=fetchdata($sjnskrj);
		foreach($res as $rjnskrj){
			$d=substr($rjnskrj['noakun'],0,5);
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
				$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
			}
			$e="";
			if($rjnskrj['noakun']=='7110202'){
				$e="selected";
			}
			if($rjnskrj['noakun']==$akun){
				$e="selected";
			}
			$optJnsKerja.="<option value=".$rjnskrj['noakun']." ".$e.">".$rjnskrj['noakun']." - ".$rjnskrj['namaakun']."</option>";
			$n=$d;
			if($d!=$n){
				$optJnsKerja.="</optgroup>";
			}
		}
		
		/* $sjnskrj="select * from ".$dbname.".setup_kegiatan where length(noakun)='7' and namakegiatan not like '%NON AKTIF%' ".$whk." order by noakun asc";
		$res=fetchdata($sjnskrj);
		foreach($res as $rjnskrj){
			$d=substr($rjnskrj['noakun'],0,5);
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
				$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
			}
			$e="";
			if($rjnskrj['noakun']==$akun){
				$e="selected";
			}
			$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan']." ".$e.">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){
				$optJnsKerja.="</optgroup>";
			}
		} */
		$e=""; $k="style=display:none;";
		if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
			$e="style=display:none;"; $optJnsKerja=$optBlok="";
		}
		
        $table.="
			<tr class=rowcontent align=center>
				<td align=center>#</td>
				<td colspan=2><select class=select2 id=krywnId name=krywnId style='width:200px' onchange='getUangLem()'>" . $optKry . "</select>
				</td>
				
				<td  ".$e." colspan=2><select class=select2 style='width:150px' id=noakun onchange=getalokasi(this.value,'".$param['divisi']."','alokasi')>" . $optJnsKerja . "</select></td>
				
				<td  ".$e." ".$k."><select style='width:100px' id=alokasi>" . $optBlok . "</select></td>
					<td  ".$e." ".$k." align=center width=20px>
					<img id='alokasi' onclick=z.elSearch('alokasi',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
                <td><select id=tpLmbr name=tpLmbr style='width:100px' onchange='getLembur(0,0)' >" . $optTipelembur . "</select></td>
                <td><select id=jam name=jam style='width:100px' onchange='getUangLem()'><option value=''>" . $_SESSION['lang']['pilihdata'] . "</option></select></td>
                
				<td ><input type='text' class='myinputtextnumber' id='uang_lbhjm' name='uang_lbhjm' style='width:100px' onkeypress='return angka_doang(event)' value=0 disabled /></td>
                <td hidden><input type='text' class='myinputtextnumber' id='uang_trnsprt' name='uang_trnsprt' style='width:100px' onkeypress='return angka_doang(event)' value=0  /></td>
                <td hidden><input type='text' class='myinputtextnumber' id='uang_mkn' name='uang_mkn' style='width:100px' onkeypress='return angka_doang(event)' value=0 /></td>
				<td>
					<input readonly onclick=popupjam(''); type='text' class='myinputtextnumber' id='jam_mulai' name='jam_mulai' style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' /></td>
				<td>
					<input readonly onclick=popupjam('');  type='text' class='myinputtextnumber' id='jam_selesai' name='jam_selesai' style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' />
				</td>
                <td>
					<input type='text' class='myinputtextnumber' id='jam_mulaifp' onblur=updtjam() name='jam_mulaifp' style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' />
				</td>
                <td>
					<input type='text' class='myinputtextnumber' id='jam_selesaifp' name='jam_selesaifp' style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' />
				</td>
				<td>
					<input type='text' class='myinputtext' id='keterangan' name='keterangan' style='width:200px' onkeypress='return tanpa_kutip(event)' value='' placeholder='Maximal character 255' maxlength=255 />
				</td>
				
				<td align=center><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/></td>
                <input hidden id=ttljam>
				
				</tr>
                ";
        $table.="</tbody></table>";
        echo $table;
    break;

    case'createTableall':
        $where = "";
        //exit('warning : '.$kdOrg);
        if (strlen($kdOrg) > 4) {
            $where = " subbagian='" . $kdOrg . "'  and (tanggalkeluar>='" . $tgl . "' or tanggalkeluar='0000-00-00')";
        } else {
            $where = " lokasitugas='" . $kdOrg . "' and (subbagian IS NULL or subbagian='0' or subbagian='') and (tanggalkeluar>='" . $tgl . "' or tanggalkeluar='0000-00-00')";
        }

        if ($tipekar != '') {
            $where.=" and tipekaryawan='".$tipekar."' ";
        }

        if ($jabatan != '') {
            $where.=" and kodejabatan='".$jabatan."' ";
        }

        if ($tipekar == '') {
            $where.=" and tipekaryawan not in ('0','7','8')";
        }


        $strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist a where " . $where . "  and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' "; 
        $resdkar = fetchdata($strdkar);
        if(count($resdkar)>0){ 
			$sKry="select * from ".$dbname.".datakaryawan_hist where  ".$where." and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' order by namakaryawan";
        }else{
			$sKry="select * from ".$dbname.".datakaryawan where  ".$where." order by namakaryawan";
        }
        $res=fetchdata($sKry);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            if (($tipekar != '')&&($jabatan == '')) {
                exit('Warning : Karyawan pada '.$nmOrg[$kdOrg].' dengan tipe karyawan '.$opttipe[$tipekar].' tidak ada.');
            } else if (($tipekar == '')&&($jabatan != '')) {
                exit('Warning : Karyawan pada '.$nmOrg[$kdOrg].' dengan jabatan '.$optjabatan[$jabatan].' tidak ada.');
            } else if (($tipekar != '')&&($jabatan != '')) {
                exit('Warning : Karyawan pada '.$nmOrg[$kdOrg].' dengan jabatan '.$optjabatan[$jabatan].' dan tipe karyawan '.$opttipe[$tipekar].' tidak ada.');
            }else{
                exit('Warning : Karyawan pada '.$nmOrg[$kdOrg].' tidak ada.');
            }
        }else{
			$e="";
			$k="style=display:none;";
			if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
				$e="style=display:none;";
			}
			echo"
				<table id='ppDetailTable' cellspacing='1' cellpadding=5 border='0' class='sortable' style='width:100%;'>
				<thead>
				<tr class=rowheader>
				<th align=center>No</th>
				<th align=center>NIK</th>
				<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
				<th align=center>".$_SESSION['lang']['tipekaryawan']."</th>
				<th align=center>".$_SESSION['lang']['jabatan']."</th>
				<th align=center colspan=2 ".$e.">".$_SESSION['lang']['akun']."</th>
				<th align=center colspan=2 ".$e." ".$k.">".$_SESSION['lang']['alokasi']."</th>
				<th align=center>" . $_SESSION['lang']['tipelembur'] . "</th>
				<th align=center width=70px>" . $_SESSION['lang']['jamaktual'] . "</th>
				<th align=center >" . $_SESSION['lang']['uangkelebihanjam'] . "</th>
				<th hidden align=center>" . $_SESSION['lang']['penggantiantransport'] . "</th>
				<th hidden align=center>" . $_SESSION['lang']['uangmakan'] . "</th>
				
				<th align=center width=40px>" . $_SESSION['lang']['jam'] . "<br>" . $_SESSION['lang']['mulai'] . "</th>
				<th align=center width=40px>" . str_replace(" ","<br>",$_SESSION['lang']['jamselesai']) . "</th>
				<th align=center width=40px>" . $_SESSION['lang']['jam'] . "<br>" . $_SESSION['lang']['mulai'] . " FP</th>
				<th align=center width=40px>" . str_replace(" ","<br>",$_SESSION['lang']['jamselesai']) . " FP</th>
				<th align=center width=40px>" . $_SESSION['lang']['keterangan'] . "</th>

				</tr></thead>
				<tbody id='detailBody'>";
			
			$param['kodeorg']=substr($absnId[0],0,4);
			$param['divisi']=$absnId[0];
			$opttipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
			$whk="";$wh="";$whr="";
			if($opttipe[$param['kodeorg']]=='KEBUN'){
				$whr.=" and kodeorganisasi like '".$param['divisi']."%'";
				if(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='BIBITAN'){				
					$wh.=" and substr(noakun,1,2) in ('71')";
					$whk.=" and substr(kodekegiatan,1,3) in ('128')";
					$kdjurnal="KBNL0";
					$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('BIBITAN'))";
				}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='TRAKSI'){				
					$kdjurnal="VHCG0";
					$wh.=" and (substr(noakun,1,2) in ('71') or substr(noakun,1,5) in ('41102')) and noakun not in ('4110299')";
				}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='WORKSHOP'){				
					$kdjurnal="WSG0";
					$wh.=" and (substr(noakun,1,2) in ('71') or substr(noakun,1,5) in ('41101')) and noakun not in ('4110199')";
				}else{				
					$whr.=" and kodeorganisasi in (select kodeorg from ".$dbname.".setup_blok where statusblok in ('TBM','TM','TB'))";
					$whk.=" and substr(kodekegiatan,1,3) in ('126','621','611')";
					$kdjurnal="KBNB0";
					$wh.=" and substr(noakun,1,2) in ('71')";
				}
			}elseif($opttipe[$param['kodeorg']]=='PABRIK'){
				if(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='TRAKSI'){				
					$kdjurnal="VHCG0";
				}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='WORKSHOP'){				
					$kdjurnal="WSG0";
				}else{				
					$kdjurnal="PKS01";
				}
				$whr.=" and kodeorganisasi like '".$param['kodeorg']."%'";
				$whr.=" and tipe='STATION'";
				$wh.=" and (substr(noakun,1,2) in ('71') or substr(noakun,1,2) in ('63'))";
			}elseif($opttipe[$param['kodeorg']]=='BULKING'){
				$kdjurnal="BLK01";
				$wh.=" and substr(noakun,1,2) in ('81')";
			}elseif($opttipe[$param['kodeorg']]=='RND' or $opttipe[$param['kodeorg']]=='TC'){
				$kdjurnal="RNDB0";
				$wh.=" and substr(noakun,1,2) in ('82')";
			}elseif($opttipe[$param['kodeorg']]=='HOLDING'){
				$kdjurnal="GJHO0";
				$wh.=" and substr(noakun,1,2) in ('82')";
			}else{
				$kdjurnal="";
				$wh.=" and substr(noakun,1,2) in ('82')";
			}
			
			$optBlok="<option value=''></option>";
			$str = "select * from ".$dbname.".organisasi where 1=1 ".$whr." order by induk,kodeorganisasi asc";
			#exit("error".$str);
			$res=fetchdata($str);
			$n="";
			foreach($res as $bar){
				$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
				$d=$nminduk[$bar['kodeorganisasi']];
				if($d!=$n){			
					$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
					$optBlok.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
				}
				$optnmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorganisasi']."'");
				if($optnmorg[$bar['kodeorganisasi']]==$bar['kodeorganisasi']){
					$blkkry=substr($bar['kodeorganisasi'],6,4);
				}else{
					$blkkry=$optnmorg[$bar['kodeorganisasi']];
				}
				$e="";
				if($bar['kodeorganisasi']==$param['divisi']){
					$e="selected";
				}
				$optBlok.="<option value=".$bar['kodeorganisasi']." ".$e.">".$blkkry."</option>";
				$n=$d;
				if($d!=$n){
					$optBlok.="</optgroup>";
				}
			}
			
			
			#PABRIK => %63%, 7%
			#KANWIL => 82%
			#RND => 82%
			#TC => 82%
			#BULKING => 81%
			$optakun=makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='".$kdjurnal."'");
			$akun=$optakun[$kdjurnal];
			$sjnskrj="select * from ".$dbname.".keu_5akun where length(noakun)='7' and namaakun not like '%NON AKTIF%' ".$wh." and aktif='1' order by noakun asc";
			$optJnsKerja="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$res=fetchdata($sjnskrj);
			foreach($res as $rjnskrj){
				$d=substr($rjnskrj['noakun'],0,5);
				if($d!=$n){			
					$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
					$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
				}
				$e="";
				if($rjnskrj['noakun']=='7110202'){
					$e="selected";
				}
				if($rjnskrj['noakun']==$akun){
					$e="selected";
				}
				$optJnsKerja.="<option value=".$rjnskrj['noakun']." ".$e.">".$rjnskrj['noakun']." - ".$rjnskrj['namaakun']."</option>";
				$n=$d;
				if($d!=$n){
					$optJnsKerja.="</optgroup>";
				}
			}
			
			/* $sjnskrj="select * from ".$dbname.".setup_kegiatan where length(noakun)='7' and namakegiatan not like '%NON AKTIF%' ".$whk." order by noakun asc";
			$res=fetchdata($sjnskrj);
			foreach($res as $rjnskrj){
				$d=substr($rjnskrj['noakun'],0,5);
				if($d!=$n){			
					$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
					$optJnsKerja.="<optgroup label='".$nmorg[$d]."'>";
				}
				$e="";
				if($rjnskrj['noakun']==$akun){
					$e="selected";
				}
				$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan']." ".$e.">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
				$n=$d;
				if($d!=$n){
					$optJnsKerja.="</optgroup>";
				}
			} */
			$e="";
			if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
				$e="style=display:none;"; $optJnsKerja=$optBlok="";
			}
			
            $thn=substr($tgl,0,4);
            $bln=substr($tgl,4,2);
            $tgal=substr($tgl,6,2);
			
			$wh="";
			#$wh=" and karyawanid not in (select karyawanid from ".$dbname.".sdm_lemburdt where kodeorg='".$kdOrg."' and tanggal='".$tgl."')";
			
            @$no2+=0;$no=0;
            $strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist a where " . $where . "  and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' "; 
            $resdkar = fetchdata($strdkar);
            if(count($resdkar)>0){ 
				$sKry="select * from ".$dbname.".datakaryawan_hist where  ".$where." and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' ".$wh." order by namakaryawan";
            }else{
				$sKry="select * from ".$dbname.".datakaryawan where  ".$where."  ".$wh." order by namakaryawan";
            }
			
			$jlh = count(fetchData($sKry));
            $nKar=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
            $nKar->setFetchMode(PDO::FETCH_ASSOC);
            while($dKar=$nKar->fetch()){
				$opjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$dKar['kodejabatan']."'");
				$optip=makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$dKar['tipekaryawan']."'");
			
				$sKry1="select jam4 as jam_keluar,jam4 as jam_masuk from ".$dbname.".upload_absensi where tanggalabsen = '".tanggalsystemn(tanggalnormal($tgl))."' and karyawanid='".$dKar['karyawanid']."'";
				$dKar1 = fetchdata($sKry1)[0];

                $no++;
				echo"<tr class=rowcontent  id=row_".$no2.">
				<td align=center>".$no."</td>
				<td>".$dKar['nik']."</td>
				<td>".$dKar['namakaryawan']."
                        <input type=hidden id=kar_".$no2." value='".$dKar['karyawanid']."'></td>
				<td>".$optip[$dKar['tipekaryawan']]."</td>
				<td>".@$opjab[$dKar['kodejabatan']]."</td>
				
                <td ".$e." colspan=2><select class=select2 style='width:150px' id=noakun_".$no2." onchange=getalokasi(this.value,'".$param['divisi']."','alokasi_".$no2."')>" . $optJnsKerja . "</select></td>

				<td ".$e." ".$k."><select style='width:100px' id=alokasi_".$no2.">" . $optBlok . "</select></td>
					<td ".$e." ".$k." align=center width=20px>
					<img id='alokasi_".$no2."' onclick=z.elSearch('alokasi_".$no2."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
                <td>
					<select id=tpLembur_".$no2." name=tpLembur_".$no2." style='width:100px' onchange='getLemburulang(0,0,".$no2.",".$jlh.")'>" . $optTipelembur . "</select>
				</td>
                <td>
					<select id=jamlmbr_".$no2." name=jamlmbr_".$no2." style='width:70px' onchange='getUangLemulang(".$no2.")'><option value=''>" . $_SESSION['lang']['pilihdata'] . "</option></select>
				</td>  
                <td >
					<input type='text' class='myinputtextnumber' id=uang_lbh_".$no2." name=uang_lbh_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0 disabled />
				</td>
                <td hidden>
					<input type='text' class='myinputtextnumber' id=uang_trans_".$no2." name=uang_trans_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0  />
				</td>
                <td hidden>
					<input type='text' class='myinputtextnumber' id=uang_mkn_".$no2." name=uang_mkn_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0 />
				</td>         
                <td>
					<input readonly type='text' class='myinputtextnumber' id=jam_mulai_".$no2." onclick=popupjam(".$no2.");  name=jam_mulai_".$no2." style='width:40px;cursor:pointer' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' />
				</td>
                <td>
					<input readonly type='text' class='myinputtextnumber' onclick=popupjam(".$no2."); id=jam_selesai_".$no2." name=jam_selesai_".$no2." style='width:40px;cursor:pointer' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' />
				</td>
                <td>
				 	<input type='text' class='myinputtextnumber' id=jam_mulaifp_".$no2."  name=jam_mulaifp_".$no2." style='width:40px' onkeypress='return tanpa_kutip(event)' value='".substr($dKar1['jam_masuk'],11,5)."' maxlength='5' disabled/></td>
                <td>
					<input type='text' class='myinputtextnumber' id=jam_selesaifp_".$no2." name=jam_selesaifp_".$no2." style='width:40px' onkeypress='return tanpa_kutip(event)' value='".substr($dKar1['jam_keluar'],11,5)."' maxlength='5' disabled />
				</td>
                <td>
					<input type='text' class='myinputtext' id=keterangan_".$no2." name=keterangan_".$no2." style='width:150px' onkeypress='return tanpa_kutip(event)' value='' placeholder='Maximal character 255' maxlength=255 /> 
				</td>
				
				<td hidden><input id=ttljam_".$no2."></td>
                </tr>";
				$no2+=1;
                
            }
            echo"<tr class=rowcontent><td colspan=17 align=center>
                  <button class=mybutton onclick=savedtall(".$no2.")>".$_SESSION['lang']['save']."</button>
                  <button class=mybutton id=cancelAbn onclick=add_new_data()>".$_SESSION['lang']['cancel']."</button>
                  </td></tr>
                  <input type=hidden id=totrows value='".$no2."' />
                  </table>";
        }       

    break;
		
    case'getBasis':
		$kdOrg = checkPostGet('kdorg', '');
		$krywnId = checkPostGet('krywnId', '');
		
		// echo"<pre>";
		// print_r($_POST);
		// print_r($_GET);
		// echo"</pre>";
		// exit("error");
		
        #= ambil datakaryawan 
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$kdOrg."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $tporgx=$bar['tipe'];
			
			
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRTPORGLEM'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrdata=explode(',',$bar['nilai']);
			foreach($arrdata as $key){
				$arrorg[]=$key;
			}	
		
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRTPJABLEM'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$arrdata=explode(',',$bar['nilai']);
			foreach($arrdata as $key){
				$arrjab[]=$key;
			}	
		
		$jabkary=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$krywnId."'");
		
		//echo $jabkary[$krywnId];
		//exit("error");
        $dtOrg = $kdOrg;
		//$optTipe=makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$dtOrg."'");
        $optBasis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sBasis = "select jamaktual from " . $dbname . ".sdm_5lembur where kodeorg='" . substr($dtOrg,0,4) . "' and tipelembur='" . $tpLembur . "'";
		//exit("warning : ".$sBasis);
        $qBasis=$owlPDO->query($sBasis) or die(print " Gagal: ".PDOException::getMessage());
        $qBasis->setFetchMode(PDO::FETCH_ASSOC);
        while ($rBasis = $qBasis->fetch()) {
            if ($tporg!='PABRIK' && !in_array($tporgx,$arrorg) and !in_array($jabkary[$krywnId],$arrjab)){
                // if ($rBasis['jamaktual']>3){
                    // break;
                // }
            }
            $optBasis.="<option value=".$rBasis['jamaktual']." ".($rBasis['jamaktual']==$basisJam ? 'selected' : '').">".$rBasis['jamaktual']."</option>";
        }

        echo $optBasis;
		
		// exit("error");
        break;
		
    case'getUang':

	


        $uangLembur = '';
        $kodeOrg = substr($kodeOrg, 0, 4);
        $sPengali = "select jamlembur from " . $dbname . ".sdm_5lembur  where kodeorg='" . $kodeOrg . "' and tipelembur='" . $tpLmbr . "' and jamaktual='" . $basisJam . "' ";
        $qPengali=$owlPDO->query($sPengali) or die(print " Gagal: ".PDOException::getMessage());
        $qPengali->setFetchMode(PDO::FETCH_ASSOC);
        $rPengali = $qPengali->fetch();

		$getPT = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $kodeOrg . "'");
		$kdpt = $getPT[$kodeOrg];	 

        $sPlmbr = "select komponengaji from " . $dbname . ".sdm_5komponenlemburpengali where kodeorg='" . $kdpt . "' ";
		$res=fetchdata($sPlmbr); 
		$allKomponen = [];
		foreach ($res as $rPlbr) { 
			$komponen = explode(',', $rPlbr['komponengaji']);
			$allKomponen = array_merge($allKomponen, $komponen);
		}
 
		// Hapus duplikat array
		$allKomponen = array_unique($allKomponen);

		$whrid = '';
		if (!empty($allKomponen)) {
			#Check jika ada komponen
			$stspajak=getStatusCatu($krywnId);  
			
			$sCatu = "SELECT jumlah FROM " . $dbname . ".sdm_5catu WHERE kodeorg='" . $kodeOrg . "' AND tahun='" . $_POST['tahun'] . "' AND kelompok='" . $stspajak . "'";
			$qCatu = $owlPDO->query($sCatu) or die(print " Gagal: ".PDOException::getMessage());
			$qCatu->setFetchMode(PDO::FETCH_ASSOC);
			$rCatu = $qCatu->fetch();
			$jlhcatu=$rCatu['jumlah'];


			$sHargaCatu = "SELECT nilai FROM " . $dbname . ".sdm_5hargacatukg WHERE unit='" . $kodeOrg . "' AND status=1";
			$qHargaCatu = $owlPDO->query($sHargaCatu) or die(print " Gagal: ".PDOException::getMessage());
			$qHargaCatu->setFetchMode(PDO::FETCH_ASSOC);
			$rHargaCatu = $qHargaCatu->fetch();
			$ncatu=$rHargaCatu['nilai'];
			$harga_catu=$jlhcatu*$ncatu;
			
			######################################
			if (in_array(118, $allKomponen)) {
				// buang 118 dari array utama
				foreach ($allKomponen as $key => $val) {
					if ((int)$val === 118) {
						unset($allKomponen[$key]);
					}
				}

				// buat string IN() tanpa 118
				$inKomponen = implode(",", array_map('intval', $allKomponen));

				// query khusus (jumlah + harga_catu)
				$sGt = "SELECT SUM(jumlah) + $harga_catu AS gapTun
						FROM ".$dbname.".sdm_5gajipokok
						WHERE idkomponen IN ($inKomponen)
						AND tahun='".substr(tanggalsystemn($_POST['tanggal']),0,7)."'
						AND kodeorg='".$kodeOrg."' and karyawanid ='" . $krywnId . "' ";
			} else {
				// kalau memang 118 tidak ada
				$inKomponen = implode(",", array_map('intval', $allKomponen));

				$sGt = "SELECT SUM(jumlah) AS gapTun
						FROM ".$dbname.".sdm_5gajipokok
						WHERE idkomponen IN ($inKomponen)
						AND tahun='".substr(tanggalsystemn($_POST['tanggal']),0,7)."'
						AND kodeorg='".$kodeOrg."' and karyawanid ='" . $krywnId . "' ";
			}

			
			$qGt=$owlPDO->query($sGt) or die(print " Gagal: ".PDOException::getMessage());
			$qGt->setFetchMode(PDO::FETCH_ASSOC);
			$rGt = $qGt->fetch();
			
			if($rGt['gapTun']==0){
				exit("Warning : Komponen Belum Ada Periode ".substr(tanggalsystemn($_POST['tanggal']),0,7)." !");
			}

		} else {
			$whrid = " and idkomponen ='87' "; #KOMPONEN UMP DAERAH
			$sGt = "select sum(jumlah) as gapTun from " . $dbname . ".sdm_5gajipokok where 1=1 ".$whrid." and tahun='".$_POST['tahun']."' and  kodeorg='" . $kodeOrg . "' ";
			$qGt=$owlPDO->query($sGt) or die(print " Gagal: ".PDOException::getMessage());
			$qGt->setFetchMode(PDO::FETCH_ASSOC);
			$rGt = $qGt->fetch();
			
			if($rGt['gapTun']==0){
				exit("Warning : UMP Belum Ada Tahun ".$_POST['tahun']." !");
			}
		}
		
		
        $whTpKary = "karyawanid='" . $krywnId . "'";
        $tipeKar = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', $whTpKary);
        $pteKar = makeOption($dbname, 'datakaryawan', 'karyawanid,kodeorganisasi', $whTpKary);

        $tpKar = $tipeKar[$krywnId];
        $ptKar = $pteKar[$krywnId];
		
		$uangLembur = $rGt['gapTun'] * $rPengali['jamlembur'] / 173;

        echo $uangLembur;
        break;

    case'getformspl':
        //$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
        foreach ($optOrg2 as $key => $value) {
            $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$key."' or induk='".$key."') ORDER BY `kodeorganisasi` ASC";
            $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $optOrg="";
            while($res=$query->fetch())
            {
                $optOrg.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>"; 
            }
        }

        $form="";
        $form = "<fieldset style='display:none'><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr><td>".$_SESSION['lang']['unitkerja']."</td><td>:</td>";
        $form.= "<td><select id='kdOrgspl' name='kdOrgspl' style='width:150px;'' ><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optOrg."</select></td></tr>";
        $form.= "<tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=tglspl onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style='width:145px;' readonly/></td></tr>";
        $form.= "</table>";
        $form.= "<button class=mybutton onclick=findspl()>Find</button></fieldset>
                 <div id=container2></div>";
        echo $form;
    break;  

    case'getdataspl':

        $data="";
        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr><td align=center >".$_SESSION['lang']['nourut']."</td>";
        $data.="<td align=center >".$_SESSION['lang']['notransaksi']."</td>
                <td align=center >".$_SESSION['lang']['namakaryawan']."</td>
                <td align=center>".$_SESSION['lang']['tipelembur']."</td>
                <td align=center>".$_SESSION['lang']['jamaktual']."</td>
                <td align=center>".$_SESSION['lang']['uangkelebihanjam']."</td>
                <td hidden align=center>".$_SESSION['lang']['penggantiantransport']."</td>
                <td hidden align=center>".$_SESSION['lang']['uangmakan']."</td>
                <td align=center>".$_SESSION['lang']['jam']." ".$_SESSION['lang']['mulai']."</td>
                <td align=center>".$_SESSION['lang']['jamselesai']."</td>
                <td align=center>".$_SESSION['lang']['keterangan']."</td>
                <td align=center width=60px><input type='checkbox' id='checkall' name='checkall' onclick='checkAll()'/></td>
                </tr></thead><tbody id='splTbody'>";
        
        if($param['kdOrgspl']!=''){
            $where.=" and a.kodeorg='".$param['kdOrgspl']."'";
        }
        if($param['tglspl']!=''){
            $where.=" and a.tanggal='".$param['tglspl']."'";
        }

       // $where.=" and a.notransaksi not in (select notransaksisp from ".$dbname.".sdm_lemburdt)";

        #data
        $no=0;
        $row=0;
        $sDt="select b.* from ".$dbname.".sdm_splemburht a left join ".$dbname.".sdm_splemburdt b on a.notransaksi=b.notransaksi where a.kodeorg='".$kdOrg."' and a.tanggal='".$tgl."' ".$where." and a.statuspersetujuan=1 and b.statuslembur=1";
        //echo $sDt;
        $qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
        $qDt->setFetchMode(PDO::FETCH_ASSOC);
        $totum=$totut=$totle=0;
        while($rDet=$qDt->fetch()){
            $sCek="select * from ".$dbname.".sdm_lemburdt where karyawanid='".$rDet['karyawanid']."' and notransaksisp='".$rDet['notransaksi']."'";
            $rCek=fetchData($sCek);
            if(count($rCek)==1){
                continue;
            }
            $sNm="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$rDet['karyawanid']."'";
            $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
            $qNm->setFetchMode(PDO::FETCH_ASSOC);
            $rNm=$qNm->fetch();
            $no+=1;
            // $notransaksi=$rDet['notransaksi'];
            $data.="
            <tr class=rowcontent style='cursor:pointer' title='add detail'>
            <td align=center>".$no."</td>
            <td id=notransp_".$row.">".$rDet['notransaksi']."</td>
            <td>".$rNm['namakaryawan']."
            <input type=hidden id=karspl_".$row." value='".$rDet['karyawanid']."'></td>
            <td >".$arrsstk[$rDet['tipelembur']]."
            <input type=hidden id=tpLemburspl_".$row." value='".$rDet['tipelembur']."'></td>
            <td align=center id=jamlmbrspl_".$row.">".$rDet['jamaktual']."</td>
            <td hidden align=right >".number_format($rDet['uangmakan'],2)."</td>
            <td hidden align=right >".number_format($rDet['uangtransport'],2)."</td>
            <td align=right id=uangspl_".$row.">".number_format($rDet['uangkelebihanjam'],2)."</td>
            <td align=right id=mulaispl_".$row.">".$rDet['jammulai']."</td>
            <td align=right id=selesaispl_".$row.">".$rDet['jamselesai']."</td>
            <td align=left id=ketspl_".$row.">".$rDet['ket']."</td>
            <td align=center><input type='checkbox' id=checkdata_".$row." name=checkdata_".$row."/></td>
            </tr>";
            $row++;
        }
        
        $data.="<tr class=rowcontent>
                <input type='hidden' id=totadd value='".$row."'/>
                <td align=center colspan=10><button class=mybutton onclick=addtodetail('".$kdOrg."','".tanggalnormal($tgl)."') >".$_SESSION['lang']['addtodetail']."</button></td>
                </tr>";
        $data.="</tbody></table></fieldset>";
        echo $data;
    break;
	case 'showupload':
		$tab="";
		$tab.="
		<table border=0 >
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td id='notranupload'>". $param['notransaksi']."</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='status'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."')\">Submit</button>
				</td>
			</tr>
		</table>
		";
		
			
			
		$str = "select * from ".$dbname.".sdm_lemburht where id='".$param['notransaksi']."'";
		$res = fetchData($str);
		
		$sGp = "select DISTINCT sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($res[0]['kodeorg'],0,4) . "' and periode='" . substr($res[0]['tanggal'],0,7) . "' and tanggalmulai<='" . $res[0]['tanggal']. "' and tanggalsampai>='" . $res[0]['tanggal'] . "'";
		$rGp = fetchData($sGp);
		
		if ((@$res[0]['posting'] == 0 || $res[0]['posting'] == 2) and @$rGp[0]['sudahproses']== 0) {
		}else{
			$tab="";
		}
		
		$tab.="
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		";

		echo $tab;
	break;
	
	case 'submitfile':
		try {
		$owlPDO->beginTransaction();
		$data = $_POST;
		if(count($data)==0){
			$data = $_GET;			
		}
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				
				#cek duplikasi nama file
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
				$res=fetchData($str);
				if(count($res)>0){
					throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$filename."','".$filetype."','LBR','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					$owlPDO->exec($str);
					
					if (!file_exists($path)){
						mkdir($path, 0777, true);
					}
					file_put_contents($path.$filename,$file_tmpname);
				}else{
					throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
				if (!file_exists($path.$filename)) {
					throw new PDOException("Upload file gagal.");
				}
			}
		}else{
			throw new PDOException("Upload file gagal.");
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
	}
	break;
	case 'loadfiles':
		$str= "select * from ".$dbname.".sdm_lemburht where id = '".$param['notransaksi']."'";
		$res= fetchData($str);
		$jurnal = $res[0]['posting'];
		
		
		$no = 0;
		$tab= "";
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
		$res= fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
				if($jurnal==0 or $jurnal==2){					
					$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
					
					$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
				}else{
					$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				}
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;
	case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			#sementara tidak boleh ada unlink
			//unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfileupload where id = '".$param['idfile']."'";
		$res= fetchData($str);
		if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}
		
		if($res[0]['formaticon']=='.pdf'){
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:100%;height:97%;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		
		echo $tab;
	break;	
}
?>