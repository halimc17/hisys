<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$proses = checkPostGet('proses', '');
$absnId = checkPostGet('absnId', '');
$kdOrg = checkPostGet('kdOrg', '');
$krywnId = checkPostGet('krywnId', '');
$tgAbsn = tanggalsystem(checkPostGet('tgAbsn', ''));

$tipeorg =makeOption($dbname,'organisasi','kodeorganisasi,tipe');

switch ($proses) {
	
    case'createTable':
        //$thisDate=date("Y-m-d");
        $table = "<table id='ppDetailTable' class=sortable border=0 cellspacing=1 cellpadding=5 style='width:100%;'>";


        $k="style=display:none;";
		$e="style=display:none;"; $optJnsKerja=$optBlok="";
		
        # Header
        $table .= "<thead>";
        $table .= "<tr class=rowheader>";
        $table .= "<th align=center>No</th>";
        $table .= "<th align=center colspan=2>" . $_SESSION['lang']['namakaryawan'] . "</th>";

        $table .= "<th align=center colspan=2 >" . $_SESSION['lang']['akun'] . "</th>";
        $table .= "<th align=center>" . $_SESSION['lang']['kegiatan'] . "</th>";
        $table .= "<th hidden align=center>" . $_SESSION['lang']['alokasi'] . "</th>";

        $table .= "<th align=center>" . $_SESSION['lang']['shift'] . "</th>";
        $table .= "<th align=center style='width:50px;display:none'>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['premi'] . "</th>";
        $table .= "<th align=center>" . $_SESSION['lang']['absensi'] . "</th>";
        $table .= "<th align=center style='width:40px'>" . $_SESSION['lang']['jumlahhk'] . "</th>";
        $table .= "<th align=center style='width:80px'>" . $_SESSION['lang']['upah'] . "</th>";
        $table .= "<th align=center style='width:80px' colspan=2>" . $_SESSION['lang']['jamMsk'] . "</th>";
        $table .= "<th align=center style='width:80px' colspan=2>" . $_SESSION['lang']['jamistirahatdari'] . "</th>";
        $table .= "<th align=center style='width:80px' colspan=2>" . $_SESSION['lang']['jamistirahatsampai'] . "</th>";
        $table .= "<th align=center style='width:80px' colspan=2>" . $_SESSION['lang']['jamPlg'] . "</th>";
        $table .= "<th align=center style='display:none'>" . $_SESSION['lang']['pembagiancatu'] . "</th>";

        $table .= "<th align=center  style='width:80px;display:none' title='kehadiran kurang dari 7 jam / Presence under 7 hours'>" . $_SESSION['lang']['penaltykehadiran'] . "</th>";
        $table .= "<th align=center style='width:50px'>" . $_SESSION['lang']['premi'] . "</th>";
        $table .= "<th align=center style='width:50px;display:none'>Insentif Kehadiran</th>";
        $table .= "<th align=center hidden style='width:50px;'>Extra Fooding</th>";
        $table .= "<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>";
        // $table .= "<th align=center>Upload Dokumen</th>";
        $table .= "<th align=center width=50px colspan=2>Action</th>";
        $table .= "</tr>";
        $table .= "</thead>";

        # Data
        $table .= "<tbody id='detailBody'>";
        $idAbn = explode("###", $absnId);
        $tgl = tanggalsystem($idAbn[1]);
		
        if (strlen($idAbn[0]) > 4) {
            $where = " a.subbagian='" . $idAbn[0] . "'   and a.statuskaryawan != 'Keluar'  and (a.tanggalkeluar>=" . $tgl . " or a.tanggalkeluar='0000-00-00')";
        } else {
            $where = " a.lokasitugas='" . $idAbn[0] . "'  and a.statuskaryawan != 'Keluar'  and (a.subbagian IS NULL or a.subbagian='0' or a.subbagian='') and (a.tanggalkeluar>=" . $tgl . " or a.tanggalkeluar='0000-00-00')";
        }

		$year = substr($tgl, 0, 4); // ambil 4 digit pertama: 2025
		$month = substr($tgl, 4, 2); // ambil 2 digit berikutnya: 05

		$per = $year . '-' . $month;

        $optKry = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

        $dakarbulanan=0;
        $strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist a where " . $where . "  and approval_status='8' and version_type='B'  and periodegaji='".$per."'"; 
        $resdkar = fetchdata($strdkar);
        if(count($resdkar)>0){ 
          $dakarbulanan=1;
        }

        if($dakarbulanan==1){
         $iKar = "select a.karyawanid,a.namakaryawan,a.nik,a.subbagian,a.tipekaryawan,a.kodejabatan,b.tipe "
                . "from " . $dbname . ".datakaryawan_hist a left join " . $dbname . ".sdm_5tipekaryawan b "
                . "on a.tipekaryawan=b.id where " . $where . " and approval_status='8' and version_type='B' and periodegaji='".$per."' order by b.tipe, a.namakaryawan asc";
        }else{
         $iKar = "select a.karyawanid,a.namakaryawan,a.nik,a.subbagian,a.tipekaryawan,a.kodejabatan,b.tipe "
                . "from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5tipekaryawan b "
                . "on a.tipekaryawan=b.id where " . $where . "  order by b.tipe, a.namakaryawan asc";
        }

        $arr='';
        $nKar = $owlPDO->query($iKar) or die(print " Gagal: " . PDOException::getMessage());
        $nKar->setFetchMode(PDO::FETCH_ASSOC);
        while ($dKar = $nKar->fetch()) {
			$d=$dKar['tipe'];
			if($dKar['nik']!=''){
				$dKar['nik']=" - ".$dKar['nik'];
			}
		
			if($d!=$n){			
				$optKry.="<optgroup label='".$d."'>";
			}
			$optKry.="<option value=".$dKar['karyawanid'].">".$dKar['namakaryawan'].$dKar['nik']."</option>";
			$n=$d;
			if($d!=$n){
				$optKry.="</optgroup>";
			}			
        }
       
        $whre = " kodeorg='" . $idAbn[0] . "'";
        $optShift = makeOption($dbname, 'pabrik_5shift', 'shift,shift', $whre);
		$optAbsen = makeOption($dbname, 'sdm_5absensi', 'kodeabsen,keterangan',"status='1'",'',true);
        		
		$optAbsen="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_5absensi where status='1' order by nilaihk desc";
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['nilaihk']>0){				
				$d="DIBAYAR";
			}else{
				$d="TIDAK DIBAYAR";
			}
			if($d!=$n){			
				$optAbsen.="<optgroup label=\"".$d."\">";
			}
			$optAbsen.="<option value=".$bar['kodeabsen'].">".strtoupper($bar['keterangan'])."</option>";
			$n=$d;
			if($d!=$n){
				$optAbsen.="</optgroup>";
			}
		}
		
		
        $jm = $mnt = "";
        for ($t = 0; $t < 24;) {
            if (strlen($t) < 2) {
                $t = "0" . $t;
            }
            $jm.="<option value=" . $t . " " . ($t == 00 ? 'selected' : '') . ">" . $t . "</option>";
            $t++;
        }
        for ($y = 0; $y < 60;) {
            if (strlen($y) < 2) {
                $y = "0" . $y;
            }
            $mnt.="<option value=" . $y . " " . ($y == 00 ? 'selected' : '') . ">" . $y . "</option>";
            $y++;
        }
		
		$param['kodeorg']=substr($idAbn[0],0,4);
		$param['divisi']=$idAbn[0];
		$opttipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
		
		$whk="";$wh="";$whr="";
		if($opttipe[$param['kodeorg']]=='KEBUN'){
			$whr.=" and kodeorganisasi like '".$param['divisi']."%'";
			$wh.=" and substr(noakun,1,3) like '71%'";
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
		
		// $optBlok="<option value=''></option>";
		// $str = "select * from ".$dbname.".organisasi where 1=1 ".$whr." order by induk,kodeorganisasi asc";
		// $res=fetchdata($str);
		// $n="";
		// foreach($res as $bar){
		// 	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
		// 	$d=$nminduk[$bar['kodeorganisasi']];
		// 	if($d!=$n){			
		// 		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		// 		$optBlok.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
		// 	}
		// 	$optnmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorganisasi']."'");
		// 	if($optnmorg[$bar['kodeorganisasi']]==$bar['kodeorganisasi']){
		// 		$blkkry=substr($bar['kodeorganisasi'],6,4);
		// 	}else{
		// 		$blkkry=$optnmorg[$bar['kodeorganisasi']];
		// 	}
		// 	$e="";
		// 	if($bar['kodeorganisasi']==$param['divisi']){
		// 		$e="selected";
		// 	}
		// 	$optBlok.="<option value=".$bar['kodeorganisasi']." ".$e.">".$blkkry."</option>";
		// 	$n=$d;
		// 	if($d!=$n){
		// 		$optBlok.="</optgroup>";
		// 	}
		// }

		#=Ambil kode kendaraan
		$optkodevhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sbrg="select kodevhc,nopol,detailvhc from ".$dbname.".vhc_5master where status ='1'";
		$rbrg=fetchData($sbrg);
		foreach ($rbrg as $val) {
			$optkodevhc.="<option value=".$val['kodevhc'].">".$val['kodevhc']." - ".($val['nopol'] != '' ? $val['nopol']." - ".$val['detailvhc'] : $val['detailvhc'])."</option>";
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

			$optJnsKerja.="</optgroup>";
			$e="";
			if($rjnskrj['noakun']==$akun){
				$e="selected";
			}
			$optJnsKerja.="<option value=".$rjnskrj['noakun']." ".$e.">".$rjnskrj['noakun']." - ".$rjnskrj['namaakun']."</option>";
			$n=$d;
			if($d!=$n){
				$optJnsKerja.="</optgroup>";
			}
		}

		## AMBIL AKUN TEMP BUAT TRAKSI
		$coaTemp = "select * from ".$dbname.".setup_parameterappl where kodeparameter = 'COATEMP'";
		$res=fetchdata($coaTemp);
		$nilaiTemp= $res[0]['nilai'];
		if($nilaiTemp != ''){
			$sjnskrj="select * from ".$dbname.".keu_5akun where noakun in (".$nilaiTemp.") and namaakun not like '%NON AKTIF%' and aktif='1' order by noakun asc";
			$res=fetchdata($sjnskrj);
			foreach($res as $rjnskrj){
				$nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
				$optJnsKerja.="<optgroup label='".$nmorg[$rjnskrj['noakun']]."'> </optgroup>";
				$optJnsKerja.="<option value=".$rjnskrj['noakun']." ".$e.">".$rjnskrj['noakun']." - ".$rjnskrj['namaakun']."</option>";
			}
		}

		## AMBIL KEGIATAN
		$optKegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sjnskrj="select * from ".$dbname.".setup_kegiatan where noakun like '71%' and status ='1' and kelompok = 'KNT' order by kodekegiatan asc";
		$res=fetchdata($sjnskrj);
		foreach($res as $rjnskrj){
			$optKegiatan.="<option value=".$rjnskrj['kodekegiatan']." ".$e.">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
		}

		$k="style=display:none;";
		$e="style=display:none;";

        $table .= "<tr align=center id='detail_tr' class=rowcontent>";
        $table .= "<td align=center>#</td>";
        $table .= "<td colspan=2><select class=select2 style='width:200px' id=krywnId name=krywnId onchange=bersihFormDetail() >" . $optKry . "</select></td>";

		$table .= "<td colspan=2><select class=select2 style='width:150px' id=noakun onchange=getKegiatan(this.value)>" . $optJnsKerja . "</select></td>";
		$table .= "<td><select class=select2 style='width:150px' id=kodekegiatan >" . $optKegiatan . "</select></td>";
		$table .= "<td hidden><select class=select2 style='width:150px' id=alokasi>" . $optkodevhc . "</select></td>";
			
        $table .= "<td>" . makeElement("shiftId", 'text', '', array('style' => 'width:40px', 'onkeypress' => 'return tanpa_kutip(event)')) . "</td>";
        $table .= "<td style='display:none'><select style='width:50px;display:none' id=premiPil name=premiPil ><option value=1>Yes</option><option value=0>No</option></select></td>";
		
		$table .= "<td><select class=select2 style='width:150px' id=absniId onchange=getHk('absen');>" . $optAbsen . "</select></td>";		
        $table .= "<td><input style='width:40px;' type=text id=jmlHk maxlength=6 class=myinputtextnumber size=12 onkeypress=\"return angka_doang(event)\" onkeyup=getHk('hk');></td>";
		$table .= "<td><input style='width:80px;' type=text disabled id=rupiahhk onkeyup=getHk('upah'); class=myinputtextnumber onkeypress=\"return angka_doang(event)\"></td>";
		
        $table .= "<td align=center><select class=select2 style='width:45px;' id=jmId name=jmId  >" . $jm . "</select></td><td><select style='width:45px;' class=select2 id=mntId name=mntId >" . $mnt . "</select></td>";
        $table .= "<td align=center><select class=select2 style='width:45px;' id=jmId3 name=jmId3 >" . $jm . "</select></td><td><select style='width:45px;' class=select2 id=mntId3 name=mntId3 >" . $mnt . "</select></td>";
        $table .= "<td align=center><select class=select2 style='width:45px;' id=jmId4 name=jmId4 >" . $jm . "</select></td><td><select style='width:45px;' class=select2 id=mntId4 name=mntId4 >" . $mnt . "</select></td>";
        $table .= "<td align=center><select class=select2 style='width:45px;' id=jmId2 name=jmId2 >" . $jm . "</select></td><td><select style='width:45px;' class=select2 id=mntId2 name=mntId2 >" . $mnt . "</select></td>";
        $table .= "<td style='display:none'><select id=catu name=catu><option value=1>Yes</option><option value=0>No</option></select></td>";

        $table .= "<td style='display:none'><input style='width:80px;display:none' type=text id=dendakehadiran class=myinputtextnumber size=12 onkeypress=\"return angka_doang(event)\" value=0></td>";
        $table .= "<td><input style='width:50px' type=text id=premiInsentif class=myinputtextnumber size=12 onkeypress=\"return angka_doang(event)\"/></td>";
        $table .= "<td style=display:none><input style='width:50px' type=text id=insentif class=myinputtextnumber size=12 onkeypress=\"return angka_doang(event)\" /></td>";
        $table .= "<td style=display:none><input style='width:50px' type=text id=insentiflibur class=myinputtextnumber size=12 onkeypress=\"return angka_doang(event)\" /></td>";
        $table .= "<td>" . makeElement("ktrng", 'text', '', array('style' => 'width:170px', 'onkeypress' => 'return tanpa_kutip(event)')) . "</td>";
        $table .= "<td align=center><img src=images/uploader/dwnld8.png class=zImgBtn title=Upload onclick=showuploadV2('".$tgAbsn."');></td>";

        # Add, Container Delete
        $table .= "<td align=center><input type=hidden id=premi /><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>";
        $table .= "&nbsp;<img id='detail_delete' /></td>";
        $table .= "</tr>";
        $table .= "</tbody>";
        $table .= "</table>";
        echo $table;
        break;
		
    case'loadDetail':
		$optTipeKar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
		
		$str="select * from ".$dbname.".keu_5akun";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optakun[$bar['noakun']]=$bar['namaakun'];
		}
		$str="select * from ".$dbname.".setup_kegiatan";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optakun[$bar['kodekegiatan']]=$bar['namakegiatan'];
		}
		
		
        $sDt = "select * from " . $dbname . ".sdm_absensidt where kodeorg='" . $kdOrg . "' and tanggal='" . $tgAbsn . "'";
		$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
		$qDt->setFetchMode(PDO::FETCH_ASSOC);
        while ($rDet = $qDt->fetch()) {
            $dakarbulanan=0;
			$strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist where karyawanid='" . $rDet['karyawanid'] . "' and approval_status='8' and version_type='B' and periodegaji='".substr(tanggalsystemn(tanggalnormal($tgAbsn)),0,7)."'"; 
				$resdkar = fetchdata($strdkar);
				if(count($resdkar)>0){ 
					$dakarbulanan=1;
				}

            if($dakarbulanan==1){
                $sNm = "select namakaryawan,nik,tipekaryawan,alokasi from " . $dbname . ".datakaryawan_hist where karyawanid='" . $rDet['karyawanid'] . "' and approval_status='8' and version_type='B' and periodegaji='".substr(tanggalsystemn(tanggalnormal($tgAbsn)),0,7)."' order by namakaryawan asc";
            }else{
                $sNm = "select namakaryawan,nik,tipekaryawan,alokasi from " . $dbname . ".datakaryawan where karyawanid='" . $rDet['karyawanid'] . "' order by namakaryawan asc";
            }
            
			$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
			$qNm->setFetchMode(PDO::FETCH_ASSOC);
            $rNm = $qNm->fetch();

            $sAbsn = "select keterangan from " . $dbname . ".sdm_5absensi where kodeabsen='" . $rDet['absensi'] . "'";
            $qAbsn=$owlPDO->query($sAbsn) or die(print " Gagal: ".PDOException::getMessage());
			$qAbsn->setFetchMode(PDO::FETCH_ASSOC);
			$rAbsn = $qAbsn->fetch();
            $no+=1;
            $strot = 0;
            $drpermi = $rDet['premi'] + $rDet['insentif'];
            if ($drpermi != 0) {
                $strot = 1;
            }
			// $e="";
			// $k="style=display:none;";
			// if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
			// 	$e="style=display:none;";
			// }
			
            echo"
				<tr class=rowcontent >
				<td align=center>" . $no . "</td>
				<td>" . $rNm['nik'] . "</td>
				<td>" . $rNm['namakaryawan'] . "</td>
				<td>" . $optTipeKar[$rNm['tipekaryawan']] . "</td>
				<td >".$rDet['noakun']." - " . $optakun[$rDet['noakun']] . "</td>
				<td >" . $rDet['kegiatan'] . " - ".getNamaKeg($rDet['kegiatan'])."</td>
				<td >" . $rDet['alokasi'] . "</td>
				<td>" . $rDet['shift'] . "</td>
				<td>" . strtoupper($rAbsn['keterangan']) . "</td>
				<td align=center>" . number_format($rDet['hk'],2) . "</td>
				<td align=center >" . $rDet['jam'] . "</td>
				<td align=center >" . $rDet['jamistirahatdari'] . "</td>
				<td align=center >" . $rDet['jamistirahatsampai'] . "</td>
				<td align=center >" . $rDet['jamPlg'] . "</td>
				<td style='display:none'>" . ($rDet['catu'] == '1' ? 'Yes' : 'No') . "</td>
				<td align=right style=display:none>" . number_format($rDet['penaltykehadiran']) . "</td>
				<td align=right>" . number_format($rDet['premi']) . "</td>
				<td align=right style=display:none>" . number_format($rDet['insentiflibur']) . "</td>";
				echo"<td>" . $rDet['penjelasan'] . "</td>";
				echo"<td>".$rDet['norefrensi']."</td>";
				if($rDet['norefrensi']!='' or $rDet['penjelasan'] == 'Auto Form Fingerprint' ){
					echo"<td></td>";
					echo"<td></td>";
					echo"<td align=center width=30px>
							<img ".$style." src=images/application/application_edit.png class=zImgBtn  title='Edit'onclick=\"editDetail('" . $rDet['karyawanid'] . "','" . $rDet['shift'] . "','" . $rDet['absensi'] . "','" . $rDet['jam'] . "','" . $rDet['jamPlg'] . "','" . $rDet['jamistirahatdari'] . "','" . $rDet['jamistirahatsampai'] . "','" . $rDet['penjelasan'] . "','" . $rDet['catu'] . "','" . $rDet['penaltykehadiran'] . "','" . $rDet['premi'] . "','" . $rDet['insentif'] . "','" . ($rDet['premi'] + $rDet['insentif']) . "','" . $strot . "','".$rDet['hk']."','".$rDet['insentiflibur']."','".$rDet['noakun']."','".$rDet['alokasi']."','".$rDet['umr']."','".getKary($rDet['karyawanid'],'tipekaryawan')."','".$rDet['norefrensi']."','".$rDet['kegiatan']."');\">
						</td>";
				}else{							
					$tanggalnya = explode("-",$rDet['tanggal']);
					$rTanggal = $tanggalnya[0].$tanggalnya[1].$tanggalnya[2];
					echo"
					<td align=center width=30px>
						<img ".$style." src=images/uploader/dwnld8.png class=zImgBtn  title='Upload' onclick=\"showupload('" . $rDet['karyawanid'] . "', '" . $rTanggal . "');\">
					</td>
					<td align=center width=30px>
						<img ".$style." src=images/application/application_edit.png class=zImgBtn  title='Edit'onclick=\"editDetail('" . $rDet['karyawanid'] . "','" . $rDet['shift'] . "','" . $rDet['absensi'] . "','" . $rDet['jam'] . "','" . $rDet['jamPlg'] . "','" . $rDet['jamistirahatdari'] . "','" . $rDet['jamistirahatsampai'] . "','" . $rDet['penjelasan'] . "','" . $rDet['catu'] . "','" . $rDet['penaltykehadiran'] . "','" . $rDet['premi'] . "','" . $rDet['insentif'] . "','" . ($rDet['premi'] + $rDet['insentif']) . "','" . $strot . "','".$rDet['hk']."','".$rDet['insentiflibur']."','".$rDet['noakun']."','".$rDet['alokasi']."','".$rDet['umr']."','".getKary($rDet['karyawanid'],'tipekaryawan')."','".$rDet['norefrensi']."','".$rDet['kegiatan']."');\">
					</td>
					<td align=center width=30px>
						<img ".$style." src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delDetail('" . $rDet['kodeorg'] . "','" . tanggalnormal($rDet['tanggal']) . "','" . $rDet['karyawanid'] . "');\" >
					</td>
					</tr>
					";
				}
						
        }
        break;
        case'getHk':

			$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$param['karyawanid']."' and tahun='".$param['periode']."' and idkomponen in ('1')";
			$res=fetchdata($str);
			$umrHarian=$res[0]['nilai']/25;
			if(getKary($param['karyawanid'],'tipekaryawan')!='4'){
				$umrHarian=0;
			}
			
            $sGtHk="select nilaihk,status from ".$dbname.".sdm_5absensi where kodeabsen='".$absnId."'";
            $rGtHk=fetchData($sGtHk);
            echo $rGtHk[0]['nilaihk'].'####'.$rGtHk[0]['status'].'####'.$umrHarian.'####'.getKary($param['karyawanid'],'tipekaryawan');
			
        break;
        case'checkSecurity':
            $arrKary='';
            $strKary="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$krywnId."' and kodejabatan in ('54','55')";
            $rsKary=fetchData($strKary);
            $jlhKary=count($rsKary);
            $arrKary.=$jlhKary;

            $strSecur="select a.karyawanid, b.namashift, b.jamawal, b.jamakhir from ".$dbname.".sdm_jadwalsecuritydt a
                        left join sdm_5shiftsecurity b on a.kodeshift=b.kodeshift 
                        where a.karyawanid='".$rsKary[0]['karyawanid']."'";
            $rsSecur=fetchData($strSecur);
            $jlhSecur=count($rsSecur);

            $arrKary.='##'.$rsSecur[0]['namashift'];
            $arrKary.='##'.$rsSecur[0]['jamawal'];
            $arrKary.='##'.$rsSecur[0]['jamakhir'];
            $arrKary.='##'.$jlhSecur;
            $arrKary.='##'.$rsKary[0]['namakaryawan'];
			
		
		echo $arrKary;

        break;
		case 'showupload':

			#Ambil UMR
			// if($tipeorg[$kdOrg]=='HOLDING'){
			// 	$tablename='sdm_5gajipokokho';
			// }else{
			// 	$tablename='sdm_5gajipokok';
			// }

			$tablename='sdm_5gajipokok';

			$year = substr($param['tanggal'], 0, 4);
			$month = substr($param['tanggal'], 4, 2);
			$newFormat = $year . '-' . $month;

			$str = "select * from ".$dbname.".".$tablename." where karyawanid='".$param['karyawanid']."' and tahun='".$newFormat."' and idkomponen='1'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$jumlahumr+=$bar['jumlah'];

			if(getKary($param['karyawanid'],'tipekaryawan') != '0'){
				if($jumlahumr==''){
					exit("Error : Gaji Pokok untuk periode ".$newFormat." belum ada !");
				}
			}

			$tab="";
			$tab.="
			<table border=0 >
				<tr>
					<td>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td>:</td>
					<td id='notranupload'>".$param['tanggal']. $param['karyawanid']. "</td>
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
						<input type=hidden id=premi />
						<button id=btnsubmit title='Proses Input File dan Absensi bersamaan' class=mybutton onclick=\"submitfile('".$param['tanggal'].$param['karyawanid']."'); addDetailUploadAll();\">Submit</button>
					</td>
				</tr>
			</table>
			";
	
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
			$path = 'fileupload/dtkaryawanabsen/';
			try {
				$owlPDO->beginTransaction();
			$data = $_POST;
			if(count($data)==0){
				$data = $_GET;			
			}
			if($data['fileupload']!=''){
				if($_FILES['file']['error']==0){
					if (!file_exists($path)){
						mkdir($path, 0777, true);
					}
					
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
						values ('".$param['notransaksi']."','".$filename."','".$filetype."','ABSEN','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
						$owlPDO->exec($str);
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
			// $str= "select * from ".$dbname.".bgt_budget where notransaksi = '".$param['notransaksi']."' and pta='PTA'";
			// $res= fetchData($str);
			// $jurnal = $res[0]['statuspta'];
			$path = 'fileupload/dtkaryawanabsen/';
			
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
					if($jurnal!='1' or $jurnal!='9'){
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
    default:
        break;
}
?>