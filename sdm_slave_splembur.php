<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$txtFind = checkPostGet('txtfind', '');
$kdOrg = checkPostGet('kdorg', '');
$tgl = tanggalsystemn(checkPostGet('tgl', ''));
$persetujuan1 = checkPostGet('persetujuan1', '');
$persetujuan2 = checkPostGet('persetujuan2', '');
$krywnId = checkPostGet('krywnId', '');
$tpLmbr = checkPostGet('tpLmbr', '');
$ungTrans = checkPostGet('ungTrans', '');
$ungMkn = checkPostGet('ungMkn', '');
$Jam = checkPostGet('Jam', '');
$ungLbhjm = checkPostGet('ungLbhjm', '');
$jammulai = checkPostGet('jammulai', '');
$jamselesai = checkPostGet('jamselesai', '');
$ket = checkPostGet('ket', '');
$no = checkPostGet('no', '');
$optKry = '';
$optTipelembur = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrsstk = array("0" => $_SESSION['lang']['haribiasa'], "1" => $_SESSION['lang']['hariminggu'], "2" => $_SESSION['lang']['harilibur'], "3" => $_SESSION['lang']['hariraya']);
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmkarya=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$optjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$opttipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$kodeOrg = checkPostGet('kodeOrg', '');
$basisJam = checkPostGet('basisJam', '');
$notransaksi = checkPostGet('notransaksi', '');
$level = checkPostGet('level', '');
$thnPeriod = "";
foreach ($arrsstk as $kei => $fal) {
    $optTipelembur.="<option value='" . $kei . "'>" . ucfirst($fal) . "</option>";
}

$tpLembur = checkPostGet('tpLembur', '');
$basisJam = checkPostGet('basisJam', '');

$str="select * from ".$dbname.".organisasi where kodeorganisasi='".substr($kdOrg,0,4)."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$tporg=$bar['tipe'];

switch ($proses) {
    
    case'updtjam':
        $jammulai=explode(':',$jammulai);
        $jam1=$jammulai[0];
        $jam2=$jammulai[1];
        $jam2=$jam2/60;
        $jmbaru=$jam1+$jam2;
        $jmtot=$jmbaru+$Jam;
        
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
    
    case'cekData':            
        if (($tpLmbr!='') && ($Jam!='')) {
            // $notransaksi=$kdOrg.str_replace('-', '', $tgl);
            $sDetIns="insert into ".$dbname.".sdm_splemburdt (`notransaksi`,`kodeorg`,`tanggal`,`karyawanid`,`tipelembur`,`jamaktual`,`uangmakan`,`uangtransport`,`uangkelebihanjam`,`jammulai`,`jamselesai`,`ket`) values ('".$notransaksi."','".$kdOrg."','".$tgl."','".$krywnId."','".$tpLmbr."','".$Jam."','".$ungMkn."','".$ungTrans."','".$ungLbhjm."','".$jammulai."','".$jamselesai."','".$ket."')";
            
            try{
                $owlPDO->exec($sDetIns); 
            }catch (PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            }
        } else {
            if ($_SESSION['language'] == 'ID') {
                echo"warning: Masukkan tipe lembur dan basis jam";
            } else {
                echo"warning: Please choose overtime type and actual hours";
            }
            exit();
        }
    break;

    case'savedt':
	try {
	$owlPDO->beginTransaction();
	
        #bentuk jumlah minggu dan tanggal
        $sPeriodeGaji1="select * from ".$dbname.".sdm_5periodegaji where '".$tgl."' between tanggalmulai and tanggalsampai and kodeorg='".substr($kdOrg,0,4)."' and sudahproses=0";
        $rPeriodeGaji1=fetchData($sPeriodeGaji1);
        if($rPeriodeGaji1[0]['periode']==''){
			throw new PDOException('Periode Gaji '.substr($tgl,0,7).' belum ada / belum dibuat, silahkan buat terlebih dahulu melalui menu : SDM - Setup - Periode Penggajian Unit');
        }

        $minggu=0;
        $listTgl=array();
        $batasAtas=intval(substr($rPeriodeGaji1[0]['tanggalsampai'],-2,2));
        for($tglawal=1;$tglawal<=$batasAtas;$tglawal++){
            if($tglawal<10){
                $strTgl=$rPeriodeGaji1[0]['periode']."-0".$tglawal;
            }else{
                $strTgl=$rPeriodeGaji1[0]['periode']."-".$tglawal;
            }
            $hariapa=date('D', strtotime($strTgl));//cari hari minggu
            if($hariapa=='Sun'){
                $minggu+=1;
            }else{
                $listTgl[$minggu][]=$strTgl;
            }
        }

        #ambil minggu keberapa dari periode gaji
        foreach($listTgl as $row=>$data){
            foreach($data as $isi){
                if($isi==$tgl){
                    $ambilMingg=$listTgl[$row];
                }
            }
        }
        // echo"<pre>";
        // print_r($ambilMingg);
        // echo"</pre>";
        // exit('warning'.$ambilMingg[0]);

        $sisa=0;
        $sisa2=0;
        $tgllbr="";
        $whr="";
        $notelebih1="";
        $notekurang1="";
        $str="select * from " . $dbname . ".sdm_5harilibur where tanggal='" . $tgl . "'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $numrows=owlBaris($res);
        if ($numrows==0) {
            if ($tporg=='HOLDING'&&$tporg=='PABRIK'){
                break;
            }

            $hari=date('D', strtotime($tgl));
            if ($hari=='Fri' || $hari=='Sat') {
                $sCek = "select tanggal from ".$dbname.".sdm_5harilibur where tanggal between '".$ambilMingg[0]."' and '".$tgl."' and (kebun='".$kdOrg."' or kebun='GLOBAL')";
                @$qCek=fetchData($sCek);
                @$tgllbr=implode(',', $qCek[0]);
                if ($tgllbr!="") {
                    $whr=" and tanggal not in (".$tgllbr.") ";
                }


                for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
                    
                    $sCek = "select sum(jamaktual) as jmlhjam from " . $dbname . ".sdm_splemburdt where tanggal between '".$ambilMingg[0]."' and '".$tgl."' and kodeorg='".$kdOrg."' and karyawanid='" .$_POST['kar'][$arDt]. "' ".$whr." ";
                    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                    $qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $jcek = $qCek->fetch();
                    $jmlhjam=$jcek['jmlhjam'];
                    $sisa=14-$jmlhjam;

                     if($tporg=='HOLDING'&&$tporg=='PABRIK'){
                        if(($_POST['tpLembur'][$arDt]!='')&&($_POST['jamlmbr'][$arDt]!='')){
                            if ($sisa>0) {
                                $sisa2=$sisa-$_POST['jamlmbr'][$arDt];

                                if ($sisa2<0) {
                                    $notelebih="Karyawan dibawah ini memiliki sisa lembur kurang dari jam lembur yang dipilih : \n";
                                    $notelebih1.="- ".$nmkarya[$_POST['kar'][$arDt]]." sisa ".$sisa." jam ;\n";
                                }

                            }else{
        						if($tporg!='PABRIK'){
        							$notekurang="Karyawan dibawah ini telah mencapai batas jam lembur : \n";
        							$notekurang1.="- ".$nmkarya[$_POST['kar'][$arDt]]." ;\n";
        						}
                            }
                        }
                    }
                }
            }
        }               

        if ($notelebih1!="") {
			throw new PDOException(" \n".$notelebih.$notelebih1."\n\n");
        }             

        if ($notekurang1!="") {
			throw new PDOException(" \n".$notekurang.$notekurang1."\n");
        }

        $jenispersetujuan='SPL';
        $sCek="select kodeorg,tanggal from ".$dbname.".sdm_splemburht where notransaksi='".$notransaksi."'";
        $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
        $qCek->setFetchMode(PDO::FETCH_ASSOC);
        $rCek=owlBaris($qCek);
        if ($rCek== 0) {
            $sIns="insert into ".$dbname.".sdm_splemburht (`notransaksi`,`kodeorg`,`tanggal`,`createdby`,`updateby`) values 
                ('".$notransaksi."','".$kdOrg."','".$tgl."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
			$owlPDO->exec($sIns);
			for ($i=1; $i<3; $i++) { 
				if ($i==1) {
					$persetujuan=$persetujuan1;
				}else{
					$persetujuan=$persetujuan2;
				}
				$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$persetujuan."','9')";
				$owlPDO->exec($str); 
			}
        } 
        
        $awl=0;
        $sDet="insert into ".$dbname.".sdm_splemburdt (`notransaksi`,`kodeorg`,`tanggal`,`karyawanid`,`tipelembur`,`jamaktual`,`uangmakan`,`uangtransport`,`uangkelebihanjam`,`jammulai`,`jamselesai`,`ket`) values";
        for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
            if(($_POST['tpLembur'][$arDt]!='')&&($_POST['jamlmbr'][$arDt]!='')){
                $sCek = "select * from " . $dbname . ".sdm_splemburdt where tanggal='" . $tgl . "' and kodeorg='" . $kdOrg . "' and karyawanid='" .$_POST['kar'][$arDt]. "'";
                $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $qCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=owlBaris($qCek);
				if ($rCek== 0) {
					if($awl==0){
						$awl=1;
						$sDet.=" ('".$notransaksi."','".$kdOrg."','".$tgl."','".$_POST['kar'][$arDt]."','".$_POST['tpLembur'][$arDt]."','".$_POST['jamlmbr'][$arDt]."','".intval($ungMkn)."','".intval($ungTrans)."','".$_POST['uang_lbh'][$arDt]."','".$_POST['jam_mulai'][$arDt]."','".$_POST['jam_selesai'][$arDt]."','".$_POST['keterangan'][$arDt]."')";
					}else{
						$sDet.=",('".$notransaksi."','".$kdOrg."','".$tgl."','".$_POST['kar'][$arDt]."','".$_POST['tpLembur'][$arDt]."','".$_POST['jamlmbr'][$arDt]."','".intval($ungMkn)."','".intval($ungTrans)."','".$_POST['uang_lbh'][$arDt]."','".$_POST['jam_mulai'][$arDt]."','".$_POST['jam_selesai'][$arDt]."','".$_POST['keterangan'][$arDt]."')";
					}
				}else{
					throw new PDOException(''.$nmkarya[$_POST['kar'][$arDt]].' pada tanggal '.tanggalnormal($tgl).' sudah pernah diinput.');
				}
            }
        }
		if($awl>0){
			$owlPDO->exec($sDet);			
		}else{
			throw new PDOException('Data detail tidak ada');
		}
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

    break;

    case'loadData':

        if ($kdOrg!='') {
            $where=" and kodeorg ='".$kdOrg."'";
        }
        if ($tgl!='--') {
            $where=" and tanggal='".$tgl."'";
        }
        
        $limit=20;
        $page=0;
        if (isset($_POST['page'])) {
            $page=$_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay=($page*$limit);
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
        $ql2 = "select count(*) as jmlhrow from ".$dbname.".sdm_splemburht where substring(kodeorg,1,4) in ".$listOrg." ".$where." order by `tanggal` desc";
        // exit("warning : ".$ql2);
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_ASSOC);
        $jsl = $query2->fetch();
        $jlhbrs = $jsl['jmlhrow'];
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=15>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{

            $slvhc="select * from ".$dbname.".sdm_splemburht where substring(kodeorg,1,4) in ".$listOrg." ".$where." order by `tanggal` desc limit ".$offset.",".$limit."";
            $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
            $qlvhc->setFetchMode(PDO::FETCH_ASSOC);
            $user_online=$_SESSION['standard']['userid'];
            $arrps=array("0"=>"Belum Diajukan","1"=>"Disetujui","2"=>"Ditolak","9"=>"Proses Persetujuan");
            while ($rlvhc=$qlvhc->fetch()) {
                $thnPeriod=substr($rlvhc['tanggal'], 0, 7);

                $sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$rlvhc['kodeorg']."'";
                $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
                $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                $rOrg=$qOrg->fetch();

                $str1="select karyawanid,level from ".$dbname.".approval where notransaksi='".$rlvhc['notransaksi']."'";
                $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar1=$res1->fetch()) {
                    $rlvhc['persetujuan'.$bar1['level']]=$bar1['karyawanid'];
                }

                $no+=1;

                $tab.="
                    <tr class=rowcontent>
                    <td>".$no."</td>
                    <td>".$rlvhc['notransaksi']."</td>
                    <td>".$rlvhc['kodeorg']."</td>
                    <td>".$rOrg['namaorganisasi']."</td>
                    <td>".tanggalnormal($rlvhc['tanggal'])."</td>
                    <td>".$arrps[$rlvhc['statuspersetujuan']]."</td>";
                if ($rlvhc['statuspersetujuan']==0){
                    $tab.="
                    <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rlvhc['notransaksi']."','".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."','".$rlvhc['persetujuan1']."','".$rlvhc['persetujuan2']."');\"></td>
                    <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$rlvhc['notransaksi']."','".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" ></td>
                    <td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_splemburht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "," .$rlvhc['notransaksi']. "','','sdm_slave_spllemburPdf',event)\"></td>
                    <td align=center><img src=images/skyblue/posting.png class=resicon  title='Ajukan' onclick=\"ajukan('".$rlvhc['notransaksi']."','".$rlvhc['kodeorg']."','".tanggalnormal($rlvhc['tanggal'])."');\" ></td>";
                }else{
                    $tab.="<td colspan=4 align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_splemburht','" . $rlvhc['kodeorg'] . "," . tanggalnormal($rlvhc['tanggal']) . "," .$rlvhc['notransaksi']. "','','sdm_slave_spllemburPdf',event)\">&nbsp;";
                    $tab.="<img src=images/zoom.png class=resicon  title='progress' onclick=\"previewDetail('".$rlvhc['notransaksi']."',event);\"></td>";
                }
                $tab.="</tr>";
            }
            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=15 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";

        }
        echo $tab."####".@$footd;
        break;

    case 'ajukan':

        // $notransaksi=$kdOrg.str_replace('-', '', $tgl);
        $jenispersetujuan='SPL';
        $slvhc="select count(notransaksi) as jumlahdt from ".$dbname.".sdm_splemburdt where notransaksi='".$notransaksi."'";
        $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
        $qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        $rlvhc=$qlvhc->fetch();
        $jumlahdt=$rlvhc['jumlahdt'];

        if ($jumlahdt==0) {
            exit('warning : Karyawan terpilih belum ada.');
        }

        $sUp = "update ".$dbname.".approval set status='0' where notransaksi='".$notransaksi."' and jenispersetujuan='".$jenispersetujuan."'";
        try{
            $owlPDO->exec($sUp); 

            $sht = "update ".$dbname.".sdm_splemburht set statuspersetujuan=9 where notransaksi='".$notransaksi."'";
            try{
                $owlPDO->exec($sht); 
            }catch (PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            } 

        }catch (PDOException $e){
            echo "DB Error : " . $e->getMessage();
            die();
        } 
    break;

    case'editfromapproval':

        // $sKry="select * from ".$dbname.".datakaryawan where  ".$where." order by namakaryawan";
        // $res=fetchdata($sKry);
        // $jlhbrs=count($res);
        
        echo"<fieldset style='float:left;'>
            <legend>".$_SESSION['lang']['detail']."</legend>
            <table cellspacing='1' border='0' class='sortable'>
            <thead>
            <tr class=rowheader>
            <td align=center width=225px>".$_SESSION['lang']['namakaryawan']."</td>
            <td align=center>".$_SESSION['lang']['tipelembur']."</td>
            <td align=center>".$_SESSION['lang']['jamaktual']."</td>
            <td align=center>".$_SESSION['lang']['uangkelebihanjam']."</td>
            <td hidden align=center>".$_SESSION['lang']['penggantiantransport']."</td>
            <td hidden align=center>".$_SESSION['lang']['uangmakan']."</td>
            <td align=center>".$_SESSION['lang']['jam']." ".$_SESSION['lang']['mulai']."</td>
            <td align=center>".$_SESSION['lang']['jamselesai']."</td>
            <td align=center>".$_SESSION['lang']['keterangan']."</td>
            <td align=center><input type='checkbox' id='btnall' onclick='checkAll()'></td>
            </tr>
            </thead><tbody>";

            $no2+=0;
            $sKry="select * from ".$dbname.".sdm_splemburdt where notransaksi='".$notransaksi."' ";
            $nKar=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
            $nKar->setFetchMode(PDO::FETCH_ASSOC);
            while($dKar=$nKar->fetch())
            {
                // foreach ($optBasis as $tplem => $jamakt) {
                //     if ($tplem!=$dKar['tipelembur']) {
                //         continue;
                //     }
                //     $optaktual.="<option value=".$jamakt." ".($jamakt==$dKar['tipelembur'] ? 'selected' : '').">".$jamakt."</option>";
                // }

                $sBasis = "select jamaktual from " . $dbname . ".sdm_5lembur where kodeorg='".substr($kdOrg,0,4)."' and tipelembur='".$dKar['tipelembur']."'";
                $qBasis=$owlPDO->query($sBasis) or die(print " Gagal: ".PDOException::getMessage());
                $qBasis->setFetchMode(PDO::FETCH_ASSOC);
                while ($rBasis = $qBasis->fetch()) {

                    if($tporg!='HOLDING'&&$tporg!='PABRIK'){
                        if ($rBasis['jamaktual']>3){
                            continue;
                        }
                    }
                    
                    $optBasis.="<option value=".$rBasis['jamaktual']." ".($rBasis['jamaktual']==$dKar['jamaktual'] ? 'selected' : '').">".$rBasis['jamaktual']."</option>";
            
                }

                $check='';
                if ($dKar['statuslembur']==1) {
                    $check='checked';
                }

                $optnama=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan'," karyawanid='".$dKar['karyawanid']."' ");

                echo"<tr class=rowcontent><td>".$optnama[$dKar['karyawanid']]."
                    <input type=hidden id=kar_".$no2." value='".$dKar['karyawanid']."'></td>
                    <td><select id=tpLembur_".$no2." name=tpLembur_".$no2." style='width:100px' disabled><option value='".$dKar['tipelembur']."'>".$arrsstk[$dKar['tipelembur']]."</option></select></td>
                    <td><select id=jamlmbr_".$no2." name=jamlmbr_".$no2." style='width:100px' onchange='getUangLemulang(".$no2.")'><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optBasis."</select></td>  
                    <td ><input type='text' class='myinputtextnumber' id=uang_lbh_".$no2." name=uang_lbh_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0 disabled /></td>
                    <td hidden><input type='text' class='myinputtextnumber' id=uang_trans_".$no2." name=uang_trans_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0  /></td>
                    <td hidden><input type='text' class='myinputtextnumber' id=uang_mkn_".$no2." name=uang_mkn_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0 /></td>         
                    <td><input type='text' class='myinputtextnumber' id=jam_mulai_".$no2." onblur=updtjamulang(".$no2.") name=jam_mulai_".$no2." style='width:60px' onkeypress='return tanpa_kutip(event)' value='".$dKar['jammulai']."' maxlength='5' /></td>
                    <td><input type='text' class='myinputtextnumber' id=jam_selesai_".$no2." name=jam_selesai_".$no2." style='width:60px' onkeypress='return tanpa_kutip(event)' value='".$dKar['jamselesai']."' maxlength='5' /></td>
                    <td><input type='text' class='myinputtext' disabled id=keterangan_".$no2." name=keterangan_".$no2." style='width:150px' onkeypress='return tanpa_kutip(event)' value='".$dKar['ket']."' placeholder='Maximal character 255' maxlength=255 /></td>
                    <td style=cursor:pointer><input type='checkbox' id='no_".$no2."' ".$check." ></td>
                    </tr>";
                $no2+=1;
            }
        echo"<tr class=rowcontent><td colspan=8 align=center>
              <button class=mybutton onclick=editdt('SPL','".$notransaksi."','".$level."')>".$_SESSION['lang']['save']." ".$_SESSION['lang']['detail']."</button></td>
              </tr>
              <input type=hidden id=totrows value='".$no2."'/>
              <input type=hidden id=orgapp value='".$kdOrg."'/>
              <input type=hidden id=tglapp value='".tanggalnormal($tgl)."'/>
              </table></fieldset>";

    break;

    case'editdt':

        #bentuk jumlah minggu dan tanggal
        $sPeriodeGaji1="select * from ".$dbname.".sdm_5periodegaji where '".$tgl."' between tanggalmulai and tanggalsampai and kodeorg='".substr($kdOrg,0,4)."' and sudahproses=0";
        $rPeriodeGaji1=fetchData($sPeriodeGaji1);
        if($rPeriodeGaji1[0]['periode']==''){
            exit('warning: Tanggal Tidak Terdaftar Pada Periode Gaji '.substr($tgl,0,7));
        }

        $minggu=0;
        $listTgl=array();
        $batasAtas=intval(substr($rPeriodeGaji1[0]['tanggalsampai'],-2,2));
        for($tglawal=1;$tglawal<=$batasAtas;$tglawal++){
            if($tglawal<10){
                $strTgl=$rPeriodeGaji1[0]['periode']."-0".$tglawal;
            }else{
                $strTgl=$rPeriodeGaji1[0]['periode']."-".$tglawal;
            }
            $hariapa=date('D', strtotime($strTgl));//cari hari minggu
            if($hariapa=='Sun'){
                $minggu+=1;
            }else{
                $listTgl[$minggu][]=$strTgl;
            }
        }

        #ambil minggu keberapa dari periode gaji
        foreach($listTgl as $row=>$data){
            foreach($data as $isi){
                if($isi==$tgl){
                    $ambilMingg=$listTgl[$row];
                }
            }
        }
        // echo"<pre>";
        // print_r($ambilMingg);
        // echo"</pre>";
        // exit('warning'.$ambilMingg[0]);

        $sisa=0;
        $sisa2=0;
        $tgllbr="";
        $whr="";
        $notelebih1="";
        $notekurang1="";
        $str="select * from " . $dbname . ".sdm_5harilibur where tanggal='" . $tgl . "'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $numrows=owlBaris($res);
        if ($numrows==0) {

            if ($tporg=='HOLDING' && $tporg=='PABRIK'){
                break;
            }

            $hari=date('D', strtotime($tgl));
            if ($hari=='Fri' || $hari=='Sat') {
                $sCek = "select tanggal from ".$dbname.".sdm_5harilibur where tanggal between '".$ambilMingg[0]."' and '".$tgl."' and (kebun='".$kdOrg."' or kebun='GLOBAL')";
                @$qCek=fetchData($sCek);
                @$tgllbr=implode(',', $qCek[0]);
                if ($tgllbr!="") {
                    $whr=" and tanggal not in (".$tgllbr.") ";
                }


                for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
                    
                    $sCek = "select sum(jamaktual) as jmlhjam from " . $dbname . ".sdm_splemburdt where tanggal between '".$ambilMingg[0]."' and '".$tgl."' and kodeorg='".$kdOrg."' and karyawanid='" .$_POST['kar'][$arDt]. "' ".$whr." ";
                    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                    $qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $jcek = $qCek->fetch();
                    $jmlhjam=$jcek['jmlhjam'];
                    $sisa=14-$jmlhjam;


                    if ($sisa>0) {
                        $sisa2=$sisa-$_POST['jamlmbr'][$arDt];

                        if ($sisa2<0) {
                            $notelebih="Karyawan dibawah ini memiliki sisa lembur kurang dari jam lembur yang dipilih : \n";
                            $notelebih1.="- ".$nmkarya[$_POST['kar'][$arDt]]." sisa ".$sisa." jam ;\n";
                        }

                    }else{
                        if($tporg!='PABRIK'){
                            $notekurang="Karyawan dibawah ini telah mencapai batas jam lembur : \n";
                            $notekurang1.="- ".$nmkarya[$_POST['kar'][$arDt]]." ;\n";
                        }
                    }

                }
            }
        }               

        if ($notelebih1!="") {
            exit("Warning : \n".$notelebih.$notelebih1."\n\n");
        }             

        if ($notekurang1!="") {
            exit("Warning : \n".$notekurang.$notekurang1."\n");
        }


        $notransaksi=$kdOrg.str_replace('-', '', $tgl);
        $jenispersetujuan='SPL';
        $awl=0;
        for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
            if(($_POST['tpLembur'][$arDt]!='')&&($_POST['jamlmbr'][$arDt]!='')){
                $sUp = "update " . $dbname . ".sdm_splemburdt set jamaktual='".$_POST['jamlmbr'][$arDt]."',uangkelebihanjam='".$_POST['uang_lbh'][$arDt]."',jammulai='".$_POST['jam_mulai'][$arDt]."',jamselesai='".$_POST['jam_selesai'][$arDt]."',statuslembur='".$_POST['statlembur'][$arDt]."' where kodeorg='" . $kdOrg . "' and tanggal='" . $tgl . "' and karyawanid='" .$_POST['kar'][$arDt]. "'";
                try{
                    $owlPDO->exec($sUp); 
                }catch (PDOException $e){
                    echo "DB Error : " . $e->getMessage();
                    die();
                } 
            }
        }
        

    break;

    case'prevprog':
            $arrps=array("1"=>"Disetujui","3"=>"Ditolak","0"=>"Proses Persetujuan");
            $rlvhc=array();
            $str1="select karyawanid,level,status,komentar from ".$dbname.".approval where notransaksi='".$no."'";
            $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar1=$res1->fetch()) {
                $rlvhc[$bar1['level']]['id']=$bar1['karyawanid'];
                $rlvhc[$bar1['level']]['status']=$bar1['status'];
                $rlvhc[$bar1['level']]['komentar']=$bar1['komentar'];
            }

            $tabs="<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>";
            $tabs.="<thead>";
            $tabs.="<tr class=rowcontent>";
            foreach ($rlvhc as $key => $val) {
               $tabs.="<td>Persetujuan Ke-".$key."</td>";
               $tabs.="<td>Status</td>";
               $tabs.="<td>Komentar</td>";
            }
            $tabs.="</tr>";
            $tabs.="</thead>";

            $tabs.="<tbody>";
            $tabs.="<tr class=rowcontent>";
            foreach ($rlvhc as $key => $val) {
                 $tabs.="<td>".$nmkarya[$val['id']]."</td>";
                 $tabs.="<td>".$arrps[$val['status']]."</td>";
                 $tabs.="<td>".$val['komentar']."</td>";
            }
            $tabs.="</tr>";
            $tabs.="</tbody>";
            $tabs.="</table>";

            echo $tabs;

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
        $sDel = "delete from ".$dbname.".sdm_splemburht where notransaksi='".$notransaksi."'"; 
        try{
            $owlPDO->exec($sDel); 
            
            $sDelDetail = "delete from " . $dbname . ".sdm_splemburdt where notransaksi='".$notransaksi."'";
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
        
    case'cekHeader':
        $thn = substr($tgl,0,4);
        $bln = substr($tgl,4,2);
        $periode = $thn . "-" . $bln;

        // $sCek="select kodeorg,tanggal from ".$dbname.".sdm_splemburht where tanggal='".$tgl."' and kodeorg='".$kdOrg."'";
        // $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
        // $qCek->setFetchMode(PDO::FETCH_ASSOC);
        // $rCek=owlBaris($qCek);
        // if ($rCek>0) {
        //     exit('Warning : Surat perintah lembur pada tanggal '.tanggalnormal($tgl).' sudah ada. Silahkan di cek di list data.');
        // }
        $sCek="select * from ".$dbname.".setup_approval where karyawanid='".$_POST['persetujuan1']."' and jenispersetujuan='SPL' and level='1' and kodeunit='".substr($kdOrg,0,4)."' ";
        //exit('warning'.$sCek);
        $rCek=fetchData($sCek);
        $sCek2="select * from ".$dbname.".setup_approval where karyawanid='".$_POST['persetujuan2']."' and jenispersetujuan='SPL' and level='2'  and kodeunit='".substr($kdOrg,0,4)."' ";
        $rCek2=fetchData($sCek2);
        if((count($rCek2)==0)||(count($rCek)==0)){
            exit('warning: Pastikan Sesuai dengan Unitnya');
        }

        $notransaksi=$kdOrg.str_replace('-', '', $tgl);
        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".sdm_splemburht 
				where notransaksi like '".$notransaksi."%' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();

        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
        $notransaksi=$notransaksi.addZero($awal,3);

        $str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and
              kodeorg='".$kdOrg."' and tutupbuku=1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $numrows=owlBaris($res);
        if($numrows > 0){
            $aktif = true;
        }else{
            $aktif = false;
        }

        if ($aktif == true) {
            exit("Error : Accounting period has been closed to this date");
        }
		
		
		#= parameter nilai selisih hari maksimal
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRHARILEM'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$setuphari=$bar['nilai'];
			$setuphari=0-$setuphari;
			
		
		
		$tglpengajuan = str_replace('-','',$tgl);
		
		//if($tglpengajuan >= 20180713){
			$selisih = selisihtgl(date('Y-m-d'),$tgl);
			//if($selisih < $setuphari){
				//exit("Error : SPL tidak bisa diinput. Toleransi tidak boleh melebihi ".abs($setuphari)." Hari ");
			//}
		//}
		
        $str = "select * from " . $dbname . ".sdm_5harilibur where tanggal='" . $tgl . "'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($res);
		if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$numrows=0;
		}

        echo $numrows."####".$notransaksi;
        break;
        
    case'updateDetail':
        if (($tpLmbr != '') && ($Jam != '')) {
            $sUp = "update " . $dbname . ".sdm_splemburdt set tipelembur='" . $tpLmbr . "',jamaktual='" . $Jam . "',uangmakan='" . $ungMkn . "',uangtransport='" . $ungTrans . "',uangkelebihanjam='" . $ungLbhjm . "',jammulai='" . $jammulai . "',jamselesai='" . $jamselesai . "',ket='".$ket."' where notransaksi='".$notransaksi."' and karyawanid='".$krywnId."'";
            try{
                $owlPDO->exec($sUp); 
            }catch (PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            }                
        }
        else {
            if ($_SESSION['language'] == 'ID') {
                echo"warning: Masukkan tipe lembur dan basis jam";
            } else {
                echo"warning: Please choose overtime type and actual hours";
            }
            exit();
        }
        break;
        
    case'delDetail':
        $sDel = "delete from " . $dbname . ".sdm_splemburdt where notransaksi='".$notransaksi."' and karyawanid='" . $krywnId . "'";
        try{
            $owlPDO->exec($sDel); 
        }catch (PDOException $e){
            echo "DB Error : " . $e->getMessage();
            die();
        }
        break;
        
    case'createTable':
        if (strlen($kdOrg)>4) {
            $where=" subbagian='".$kdOrg."'  and statuskaryawan != 'Keluar' and (tanggalkeluar>".$tgl." or tanggalkeluar='0000-00-00')";
        } else {
            $where=" lokasitugas='".$kdOrg."'  and statuskaryawan != 'Keluar' and (subbagian IS NULL or subbagian='0' or subbagian='') and (tanggalkeluar>".$tgl." or tanggalkeluar='0000-00-00')";
        }

        $optTipeKar=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
        $sKry="select * from ".$dbname.".datakaryawan where ".$where." and tipekaryawan in ('1','2','3','4') order by namakaryawan asc";
        $qKry=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
        $qKry->setFetchMode(PDO::FETCH_ASSOC);
        while ($rKry = $qKry->fetch()) {
            $optKry.="<option value=" . $rKry['karyawanid'] . ">" . $rKry['namakaryawan'] . " [ " . $rKry['nik'] . " ] " . $optTipeKar[$rKry['tipekaryawan']] . "</option>";
        }

        $table = "<fieldset><legend>".$_SESSION['lang']['input']."</legend>
				<table id='ppDetailTable' cellspacing='1' border='0' class='sortable'>
                <thead>
                <tr class=rowheader>
                <td align=center colspan=2>".$_SESSION['lang']['namakaryawan']."</td>
                <td align=center>".$_SESSION['lang']['tipelembur']."</td>
                <td align=center>".$_SESSION['lang']['jamaktual']."</td>
                <td align=center>".$_SESSION['lang']['uangkelebihanjam']."</td>
                <td hidden align=center>".$_SESSION['lang']['penggantiantransport']."</td>
                <td hidden align=center>".$_SESSION['lang']['uangmakan']."</td>
                <td align=center>".$_SESSION['lang']['jam']." ".$_SESSION['lang']['mulai']."</td>
                <td align=center>".$_SESSION['lang']['jamselesai']."</td>
                <td align=center>".$_SESSION['lang']['keterangan']."</td>
                <td align=center>Action</td>
                </tr></thead>
                <tbody id='detailBody'>";

        $table.="<tr class=rowcontent>
                <td><select id=krywnId name=krywnId style='width:200px' onchange='getUangLem()'>".$optKry."</select></td>
                <td width=20px align=center><img id='krywnId' onclick=z.elSearch('krywnId',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
                </td>
                <td><select id=tpLmbr name=tpLmbr style='width:100px' onchange='getLembur(0,0)'>".$optTipelembur ."</select></td>
                <td><select id=jam name=jam style='width:100px' onchange='getUangLem()'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
                <td ><input type='text' class='myinputtextnumber' id='uang_lbhjm' name='uang_lbhjm' style='width:100px' onkeypress='return angka_fdoang(event)' value=0 disabled /></td>
                <td hidden><input type='text' class='myinputtextnumber' id='uang_trnsprt' name='uang_trnsprt' style='width:100px' onkeypress='return angka_doang(event)' value=0  /></td>
                <td hidden><input type='text' class='myinputtextnumber' id='uang_mkn' name='uang_mkn' style='width:100px' onkeypress='return angka_doang(event)' value=0 /></td>
                <td><input type='text' class='myinputtextnumber' id='jam_mulai' onblur=updtjam() name='jam_mulai' style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' /></td>
                <td><input type='text' class='myinputtextnumber' id='jam_selesai' name='jam_selesai' style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5'/></td>
                <td><input type='text' class='myinputtext' id='keterangan' name='keterangan' style='width:150px' onkeypress='return tanpa_kutip(event)' value='' placeholder='Maximal character 255' maxlength=255 /></td>
                <td align=center><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"cek_data()\" src='images/save.png'/></td>
                </tr>
                <tr>
                    <td><button class=mybutton onclick=displayList()>".$_SESSION['lang']['done']."</button></td>
                </tr>";
        $table.="</tbody></table></fieldset>";
        echo $table;
    break;

    case'loadDetail':
		$arrTipeLembur=array($_SESSION['lang']['haribiasa'],$_SESSION['lang']['hariminggu'],$_SESSION['lang']['harilibur'],$_SESSION['lang']['hariraya']);
        echo "<fieldset>
              <legend>".$_SESSION['lang']['datatersimpan']."</legend>
                <table cellspacing='1' border='0' class='sortable'>
                <thead>
                <tr class=rowheader>
                <td align=center>No</td>
				<td align=center>NIK</td>
				<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
				<td align=center>".$_SESSION['lang']['jabatan']."</td>
                <td align=center>".$_SESSION['lang']['tipelembur']."</td>
                <td align=center width=50px >".$_SESSION['lang']['jamaktual']."</td>
                <td hidden align=center>".$_SESSION['lang']['uangmakan']."</td>
                <td hidden align=center>".$_SESSION['lang']['penggantiantransport']."</td>
                <td align=center>".$_SESSION['lang']['uangkelebihanjam']."</td>    
                <td align=center width=50px >".$_SESSION['lang']['jam']." ".$_SESSION['lang']['mulai']."</td>
                <td align=center width=50px >".$_SESSION['lang']['jamselesai']."</td>
                <td align=center>".$_SESSION['lang']['keterangan']."</td>
                <td align=center>Action</td>
                </tr></thead>
                <tbody>";

        $sDt="select * from ".$dbname.".sdm_splemburdt where notransaksi='".$notransaksi."'";
        $qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
        $qDt->setFetchMode(PDO::FETCH_ASSOC);
        $totum=$totut=$totle=0;
        while($rDet=$qDt->fetch()){
			
            $sNm="select namakaryawan,tipekaryawan,kodejabatan,nik from ".$dbname.".datakaryawan where karyawanid='".$rDet['karyawanid']."'";
            $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
            $qNm->setFetchMode(PDO::FETCH_ASSOC);
            $rNm=$qNm->fetch();
            $no+=1;

			$opjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$rNm['kodejabatan']."'");
			$optip=makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$rNm['tipekaryawan']."'");

            echo"
            <tr class=rowcontent>
            <td align=center>".$no."</td>
			<td>".$rNm['nik']."</td>
            <td>".$rNm['namakaryawan']."</td>
			<td>".$optip[$rNm['tipekaryawan']]."</td>
			<td>".@$opjab[$rNm['kodejabatan']]."</td>
            <td>".$arrTipeLembur[$rDet['tipelembur']]."</td>
            <td align=center>".$rDet['jamaktual']."</td>
            <td hidden align=right>".number_format($rDet['uangmakan'],2)."</td>
            <td hidden align=right>".number_format($rDet['uangtransport'],2)."</td>
            <td align=right>".number_format($rDet['uangkelebihanjam'],2)."</td>
            <td align=right>".$rDet['jammulai']."</td>
            <td align=right>".$rDet['jamselesai']."</td>
            <td align=left>".$rDet['ket']."</td>
            
            <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editDetail('".$rDet['karyawanid']."','".$rDet['tipelembur']."','".$rDet['jamaktual']."','".$rDet['uangmakan']."','".$rDet['uangtransport']."','".$rDet['uangkelebihanjam']."','".$rDet['jammulai']."','".$rDet['jamselesai']."','".$rDet['ket']."');\">
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('".$rDet['notransaksi']."','".$rDet['kodeorg']."','".tanggalnormal($rDet['tanggal'])."','".$rDet['karyawanid']."');\" ></td>
            </tr>
            ";
            $totum+=$rDet['uangmakan'];
            $totut+=$rDet['uangtransport'];
            $totle+=$rDet['uangkelebihanjam'];
        }
            echo"
            <tr class=rowcontent>
            <td colspan=7 align=center>Total</td>
            <td hidden align=right>".number_format($totum,2)."</td>
            <td hidden  align=right>".number_format($totut,2)."</td>
            <td align=right>".number_format($totle,2)."</td>
            <td colspan=4></td>
            </tr>
            </tbody></table></fieldset>";
        break;

    case'add_detail':
        $where="";

        if (strlen($kdOrg) > 4) {
            $where=" subbagian='".$kdOrg."'  and statuskaryawan != 'Keluar' and (tanggalkeluar>='".$tgl."' or tanggalkeluar='0000-00-00')";
        } else {
            $where=" lokasitugas='".$kdOrg."'  and statuskaryawan != 'Keluar' and (subbagian IS NULL or subbagian='0' or subbagian='') and (tanggalkeluar>='".$tgl."' or tanggalkeluar='0000-00-00')";
        }

        if (@$tipekar=='') {
            $where.=" and tipekaryawan in ('1','2','3','4')";
        }

        $sKry="select * from ".$dbname.".datakaryawan where  ".$where." order by namakaryawan";
        $res=fetchdata($sKry);
        $jlhbrs=count($res);
        if($jlhbrs==0){
                
            exit('Warning : Karyawan pada '.$nmOrg[$kdOrg].' tidak ada.');

        }else{
        echo"<fieldset>
            <legend>".$_SESSION['lang']['detail']."</legend>
            <table cellspacing='1' border='0' class='sortable'>
            <thead>
            <tr class=rowheader>
            <td align=center>No</td>
            <td align=center>NIK</td>
            <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
            <td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
            <td align=center>".$_SESSION['lang']['jabatan']."</td>
            <td align=center>".$_SESSION['lang']['tipelembur']."</td>
            <td align=center>".$_SESSION['lang']['jamaktual']."</td>
            <td align=center>".$_SESSION['lang']['uangkelebihanjam']."</td>
            <td hidden align=center>".$_SESSION['lang']['penggantiantransport']."</td>
            <td hidden align=center>".$_SESSION['lang']['uangmakan']."</td>
            <td align=center>".$_SESSION['lang']['jam']." ".$_SESSION['lang']['mulai']."</td>
            <td align=center>".$_SESSION['lang']['jamselesai']."</td>
            <td align=center>".$_SESSION['lang']['keterangan']."</td>
            </tr>
            </thead><tbody>";

            @$no2+=0;
			$no=0;
            $sKry="select * from ".$dbname.".datakaryawan where  ".$where." order by namakaryawan";
			$jlh = count(fetchData($sKry)); 
           // exit($sKry);
            $nKar=$owlPDO->query($sKry) or die(print " Gagal: ".PDOException::getMessage());
            $nKar->setFetchMode(PDO::FETCH_ASSOC);
            while($dKar=$nKar->fetch()){//getBasis
			$opjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$dKar['kodejabatan']."'");
			$optip=makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$dKar['tipekaryawan']."'");
			
			$no+=1;
            echo"<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td>".$dKar['nik']."</td>
				<td>".$dKar['namakaryawan']."
                        <input hidden id=kar_".$no2." value='".$dKar['karyawanid']."'></td>
				<td>".$optip[$dKar['tipekaryawan']]."</td>
				<td>".@$opjab[$dKar['kodejabatan']]."</td>
                
				<td><select id=tpLembur_".$no2." name=tpLembur_".$no2." style='width:100px' onchange='getLemburulang(0,0,".$no2.",".$jlh.")'>" . $optTipelembur . "</select></td>
                <td><select id=jamlmbr_".$no2." name=jamlmbr_".$no2." style='width:100px' onchange='getUangLemulang(".$no2.")'><option value=''>" . $_SESSION['lang']['pilihdata'] . "</option></select></td>  
                <td ><input type='text' class='myinputtextnumber' id=uang_lbh_".$no2." name=uang_lbh_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0 disabled /></td>
                <td hidden><input type='text' class='myinputtextnumber' id=uang_trans_".$no2." name=uang_trans_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0  /></td>
                <td hidden><input type='text' class='myinputtextnumber' id=uang_mkn_".$no2." name=uang_mkn_".$no2." style='width:100px' onkeypress='return angka_doang(event)' value=0 /></td>         
                <td><input type='text' class='myinputtextnumber' id=jam_mulai_".$no2." onblur=updtjamulang(".$no2.") name=jam_mulai_".$no2." style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' /></td>
                <td><input type='text' class='myinputtextnumber' id=jam_selesai_".$no2." name=jam_selesai_".$no2." style='width:60px' onkeypress='return tanpa_kutip(event)' value='00:00' maxlength='5' /></td>
                <td><input type='text' class='myinputtext' id=keterangan_".$no2." name=keterangan_".$no2." style='width:250px' onkeypress='return tanpa_kutip(event)' value='' placeholder='Maximal character 255' maxlength=255 /></td>
                </tr>";
            $no2+=1;
            }
            echo"<tr class=rowcontent><td colspan=11 align=center>
                  <button class=mybutton onclick=savedt()>".$_SESSION['lang']['save']." ".$_SESSION['lang']['detail']."</button></td>
                  </tr>
                  <input type=hidden id=totrows value='".$no2."'/>
                  </table></fieldset>";
        }       

    break;
        
    case'getBasis':
	
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
        $qBasis=$owlPDO->query($sBasis) or die(print " Gagal: ".PDOException::getMessage());
        $qBasis->setFetchMode(PDO::FETCH_ASSOC);
        while ($rBasis = $qBasis->fetch()) {
            if ($tporg!='PABRIK' && !in_array($tporgx,$arrorg) and !in_array($jabkary[$krywnId],$arrjab)){
                if ($rBasis['jamaktual']>3){
                    break;
                }
            }
            $optBasis.="<option value=".$rBasis['jamaktual']." ".($rBasis['jamaktual']==$basisJam ? 'selected' : '').">".$rBasis['jamaktual']."</option>";
        }

        echo $optBasis;
        break;
        
    case'getUang':

	
        $sjb = "select kodejabatan from " . $dbname . ".datakaryawan  where karyawanid='".$krywnId."'";
        $qjb=$owlPDO->query($sjb) or die(print " Gagal: ".PDOException::getMessage());
        $qjb->setFetchMode(PDO::FETCH_ASSOC);
        $rjb = $qjb->fetch();
        $kdjab=$rjb['kodejabatan'];
        $uangLembur = '';
        $kodeOrg = substr($kodeOrg, 0, 4);
        $sPengali = "select jamlembur from " . $dbname . ".sdm_5lembur  where kodeorg='" . $kodeOrg . "' and tipelembur='" . $tpLmbr . "' and jamaktual='" . $basisJam . "' ";
        $qPengali=$owlPDO->query($sPengali) or die(print " Gagal: ".PDOException::getMessage());
        $qPengali->setFetchMode(PDO::FETCH_ASSOC);
        $rPengali = $qPengali->fetch();

        $sGt = "select sum(jumlah) as gapTun from " . $dbname . ".sdm_5gajipokok where karyawanid='" . $krywnId . "' 
		and idkomponen=1 and tahun=" . $_POST['tahun'];
        $qGt=$owlPDO->query($sGt) or die(print " Gagal: ".PDOException::getMessage());
        $qGt->setFetchMode(PDO::FETCH_ASSOC);
        $rGt = $qGt->fetch();
        if(intval($rGt['gapTun'])==0){
            $sGt = "select sum(jumlah) as gapTun from " . $dbname . ".sdm_5gajipokokho where karyawanid='" . $krywnId . "' 
            and idkomponen=1 and tahun=" . $_POST['tahun'];
            $qGt=$owlPDO->query($sGt) or die(print " Gagal: ".PDOException::getMessage());
            $qGt->setFetchMode(PDO::FETCH_ASSOC);
            $rGt = $qGt->fetch();
        }

        $whTpKary = "karyawanid='" . $krywnId . "'";
        $tipeKar = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', $whTpKary);
        $pteKar = makeOption($dbname, 'datakaryawan', 'karyawanid,kodeorganisasi', $whTpKary);

        $tpKar = $tipeKar[$krywnId];
        $ptKar = $pteKar[$krywnId];

		
		#= ambil natura unit
		$str = "select * from " . $dbname . ".sdm_5periodegaji where 
			kodeorg='" . $kodeOrg . "' and periode='".substr($tgl,0,7)."' and natura=1";
        $resdtck=fetchData($str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $naturabln=0;
        if(count($resdtck)!=0){
            $naturabln=$bar['kg']*$bar['harga'];
        }
			
			
		// $uangLembur = ($rGt['gapTun'] * $rPengali['jamlembur']) / 173;
		
		if($tpKar==4){
			$uangLembur = (($rGt['gapTun']) * $rPengali['jamlembur']) / 173;
		}else{
			$uangLembur = (($rGt['gapTun']+$naturabln) * $rPengali['jamlembur']) / 173;
		}
		
		
			
		
        echo $uangLembur;
        break;
    default:
        break;
}

function selisihtgl($tgl1,$tgl2){
	$selisih = (((strtotime ($tgl2) - strtotime ($tgl1)))/(60*60*24));
	return $selisih;
}

?>