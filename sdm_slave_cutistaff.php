<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$jenispersetujuan="IJS";

$path	= "fileupload/sdm_ijin/";

switch ($method) {
	case'loaddatakar':
		$strcuti="select tanggalmasuk,tanggalpengangkatan from ".$dbname.".datakaryawan where karyawanid='".$param['karyawanid']."' ";
        $rescuti=$owlPDO->query($strcuti) or die(print " Gagal: ".PDOException::getMessage());
        $rescuti->setFetchMode(PDO::FETCH_ASSOC);
        $barcuti=$rescuti->fetch();
        @$tanggalmasuk=$barcuti['tanggalmasuk'];
        @$tanggalpengangkatan=$barcuti['tanggalpengangkatan'];
       
        
        echo tanggalnormal($tanggalmasuk).'###'.tanggalnormal($tanggalpengangkatan);
	break;
    case'loadperiodecuti':
        $optPeriodec="";
        $str="select periodecuti from ".$dbname.".sdm_cutiht where karyawanid='".$param['karyawanid']."' group by periodecuti ";
        $res=fetchdata($str);
        foreach($res as $val){
            $optPeriodec.="<option value='".$val['periodecuti']."'>".$val['periodecuti']."</option>";
        }
        echo $optPeriodec;
    break;
	case'getjumlahcuti':
		
		$arrlokasitugas=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas'," karyawanid='".$param['karyawanid']."'");
		$arrtipelokasi=makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe'," kodeorganisasi='".$arrlokasitugas[$param['karyawanid']]."'");
		$strcuti="select statuspotongan from ".$dbname.".sdm_5jenisijin where idjenis='".$param['idjenis']."' ";
        $rescuti=$owlPDO->query($strcuti) or die(print " Gagal: ".PDOException::getMessage());
        $rescuti->setFetchMode(PDO::FETCH_ASSOC);
        $barcuti=$rescuti->fetch();
        $statuspotongan=$barcuti['statuspotongan'];
        $jumlahhari=0;
        //if ($statuspotongan!=0) {
            $hariawal=$param['tglAwal'];
            $hariakhir=$param['tglEnd'];
            
            $jumlahhari = selisitgl($hariakhir,$hariawal)+1;
            $n=$jumlahhari;
            $tglcuti='';
            $no="";
            for ($i=0; $i < $n ; $i++) { 

                $whr=" and (kebun='GLOBAL' or kebun='".$arrlokasitugas[$param['karyawanid']]."')";
                if ($arrtipelokasi[$arrlokasitugas[$param['karyawanid']]]=='HOLDING') {
                    $whr=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$arrlokasitugas[$param['karyawanid']]."')";
                }

                #cek apakah tanggal termasuk hari libur
                $tglcuti= date("Ymd",strtotime("+".$i." Day",strtotime($param['tglAwal']))); 
                $str="select * from ".$dbname.".sdm_5harilibur where keterangan='libur' and tanggal='".$tglcuti."'".$whr;
                $res=fetchData($str);
                $jmlhbaris=count($res);
                if ($jmlhbaris>0) {
                    $jumlahhari=$jumlahhari-1;
                }
                
                $no++;  
            }
            
            $jumlahhari=$jumlahhari;
        //}
        

        if($jumlahhari<0){
            exit("Error : Tanggal sampai  tidak boleh lebih kecil dari tanggal dari");
        }
        
        echo $jumlahhari;
	break;
	case'loadSisaCuti':

		$wherex='';
		if($param['notransaksi']!=''){
			$wherex=" and notransaksi !='".$param['notransaksi']."'";
		}
		$sisa=0;
        $str="select statuspotongan from ".$dbname.".sdm_5jenisijin where idjenis='".$param['idjenis']."'";
        $res=fetchdata($str);
        $statuspotongan=0;
        foreach($res as $val){
            $statuspotongan=$val['statuspotongan'];
        }

        if($statuspotongan=='0'){
            $str="select hakcuti from ".$dbname.".sdm_5hakcutijenis where jenisijin='".$param['idjenis']."' ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                $sisa=$bar->hakcuti;
            }

        }elseif($statuspotongan=='1' or $statuspotongan=='2'){
            $pengurangbelumsetuju=0;
            $str="select sum(jumlahhari) as jumlah from ".$dbname.".sdm_ijin where karyawanid=".$param['karyawanid']." 
            and statuspersetujuan in ('0','9','3') and statuspotongan='1' and periodecuti in ('".$param['periodecuti']."') ".$wherex." group by karyawanid";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                $pengurangbelumsetuju=$bar->jumlah;
            }

            $str="select sisa from ".$dbname.".sdm_cutiht where karyawanid=".$param['karyawanid']." 
            and periodecuti in ('".$param['periodecuti']."')";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                $sisa=$bar->sisa;
            }

            $sisa=$sisa-$pengurangbelumsetuju;
            
        }elseif($statuspotongan=='3'){
            $notrx=str_replace('-', '', $tglAwal)."/".$jnsIjin."/".$_SESSION['standard']['userid'];
            $str="select (jumlahharidayoff-diambil) as sisa, akandiambil,notransaksicuti from ".$dbname.".sdm_dayoff_dt_vw where karyawanid='".$param['karyawanid']."' 
                   and tanggaldayoff <='".$tglAwal."' and tanggalberlakusampai >='".$tglAwal."' and status='1'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                if($bar->notransaksicuti==$notrx)
                {
                    $sisa+=$bar->sisa;
                }
                else
                {
                    $sisa+=($bar->sisa-$bar->akandiambil);

                }
            }
        }
        else
        {
            $sisa=0;
        }

        echo $sisa;
	break;	

	case'insert':
		$arrlokasitugas=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas'," karyawanid='".$param['karyawanid']."'");
		$arrtipekaryawan=makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan'," karyawanid='".$param['karyawanid']."'");
		$arrtipelokasi=makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe'," kodeorganisasi='".$arrlokasitugas[$param['karyawanid']]."'");
        $lokasitugas = $arrlokasitugas[$param['karyawanid']];
        $tipekaryawan = $arrtipekaryawan[$param['karyawanid']];
        $tipelokasitugas = $arrtipelokasi[$arrlokasitugas[$param['karyawanid']]];

        $str="select statuspotongan from ".$dbname.".sdm_5jenisijin where idjenis='".$param['idjenis']."'";
        $res=fetchdata($str);
        $statuspotongan=0;
        foreach($res as $val){
            $statuspotongan=$val['statuspotongan'];
        }

        $strApp="select * from ".$dbname.".setup_approval where karyawanid='".$param['karyawanid']."' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0 and trim($param['pengganti']) == ""){
            //Harap pilih Pengganti
            exit("Warning : Harap pilih/isi \"".$_SESSION['lang']['personalpengganti']."\" terlebih dahulu!");
        }else{
            
            //bebas tanpa pengganti .
            $rangetgl = rangeTanggal($param['tglAwal'],$param['tglEnd']);

            foreach($rangetgl as $tglxz){
                $str="select * from ".$dbname.".sdm_ijin where karyawanid='".$param['karyawanid']."'  and statuspersetujuan!='2' and statuspersetujuan_cancel!='1' ";
                $res=fetchData($str);
                foreach ($res as $bar) {
                    $a = $bar['darijam'];
                    $b = $bar['sampaijam'];
                }
                $tglxz = $tglxz." 00:00:00";

                if ($tglxz >= $a && $tglxz <= $b) {
                    exit("warning: Terdapat tanggal yang sudah pernah diinput yakni " . $tglxz);
                }
            }

            $potonggaji=0;
            if($statuspotongan=='0' and $param['sisa']!=0 and $param['jumlahhk']>$param['sisa']){
        		exit("Warning : Jumlah Hari Izin melebihi dari jumlah hak yang ada");
	        }elseif($statuspotongan=='0' and $param['sisa']==0){
        		$sisa=0;
	        }elseif ($statuspotongan=='1' and $param['jumlahhk']>$param['sisa']) {
	        	exit("Warning : Jumlah Hari Izin melebihi dari jumlah hak yang ada");
	        }elseif ($statuspotongan=='1' and $param['jumlahhk']<=$param['sisa']) {
	        	$sisa=$param['sisa']-$param['jumlahhk'];
	        }elseif ($statuspotongan=='2' and $param['jumlahhk']<=$param['sisa']) {
	        	$sisa=$param['sisa']-$param['jumlahhk'];
	        }elseif ($statuspotongan=='2' and $param['jumlahhk']>$param['sisa']) {
	        	$sisa=0;
	        	$potonggaji=$param['jumlahhk']-$param['sisa'];
	        }elseif ($statuspotongan=='3' and $param['jumlahhk']>$param['sisa']) {
	        	exit("Warning : Jumlah Hari Izin melebihi dari jumlah hak yang ada");
	        }

           $str    = "SELECT max(right(notransaksi,5)) as datamax FROM " . $dbname . ".sdm_ijin WHERE idjenis='".$param['idjenis']."'";
            $res    = fetchData($str);
            $jlhbrs     = $res[0]['datamax'];
            $counterid  = intval($res[0]['datamax'])+1;
            $idbaru = addZero($counterid,5);

            $notransaksi=str_replace('-', '', tanggalsystem($param['tglijin']))."/".$param['idjenis']."/".$idbaru;
            $arrtglawal = explode("-", $param['tglAwal']);
			$tglawalbenar = $arrtglawal[2] . "-" . $arrtglawal[1] . "-" . $arrtglawal[0]." ".$param['jam1'].":".$param['mnt1'].":00";

            $arrtglend = explode("-", $param['tglEnd']);
			$tglendbenar = $arrtglend[2] . "-" . $arrtglend[1] . "-" . $arrtglend[0]." ".$param['jam2'].":".$param['mnt2'].":00";

            $ha     = "INSERT INTO " . $dbname . ".sdm_ijin 
                        (`notransaksi`,`karyawanid`,`tanggal`,`keperluan`,`keterangan`,`darijam`,`sampaijam`,`idjenis`,`periodecuti`,`jumlahhari`,`alamatcuti`,`pengganti`,`nohp`,`hometrip`,`tanggalberangkat`,`rutekeberangkatan`,`tglpulang`,`rutekepulangan`,`statuspersetujuan`,`statuspersetujuan_cancel`,`statuspotongan`,`potonggaji`,`updateby`,`updatetime`,`tanggalkerja`,`lokasitugas`,`tipekaryawan`,`sumber`,`approval_pengganti`) VALUES 
                        ('" . $notransaksi . "','" . $param['karyawanid']. "','" . tanggalsystem($param['tglijin']) . "','" . $param['keperluan'] . "','" . $param['ket'] . "','" . $tglawalbenar . "','" .$tglendbenar."','" . $param['idjenis'] . "','" . $param['periodecuti']. "','" . $param['jumlahhk'] . "','" . $param['alamatcuti'] . "','" . $param['pengganti'] . "','" . $param['nohp'] . "','".$param['cb']."','".tanggalsystem($param['tglberangkat'])."','".$param['rutekeberangkatan']."','".tanggalsystem($param['tglpulang'])."','".$param['rutekepulangan']."','0','0','".$statuspotongan."','".$potonggaji."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".tanggalsystem($param['tanggalkerja'])."','".$lokasitugas."','".$tipekaryawan."','CUTISTAFF','".$param['apppengganti']."')";
            
            try{
                $owlPDO->exec($ha);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $instCuti = "";
            if($param['apppengganti'] != ''){
                $rangetgl = rangeTanggalarr(tanggalsystemn($param['tglAwal']),tanggalsystemn($param['tglEnd']));

                $str="select * from ".$dbname.".log_approval_pengganti where notransaksi = '".$notransaksi."'";
                $res = fetchdata($str);
                $jlhbrs = count($res);

                if($jlhbrs > 0){
                    $str1    = "Delete FROM " . $dbname . ".log_approval_pengganti WHERE notransaksi = '".$notransaksi."'";
                    try{
                        $owlPDO->exec($str1);
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }

                foreach($rangetgl as $tgl){		
                    $instCuti.="insert into ".$dbname.".log_approval_pengganti (notransaksi,karyawanid_cuti,karyawanid_pengganti,tanggal) values ('".$notransaksi."','".$param['karyawanid']."','".$param['apppengganti']."','".$tgl."');";
                }

                if($instCuti != ""){
                    try{
                        $owlPDO->exec($instCuti);
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                }
            }

        }
	break;		
		
    case'loadData':
        $tab        ="";
        $footer     ="";
		$limit      = 10;
        $page       = 0;
        $colspan    = 16;
        $pagejs = $param['page'];
		if (isset($pagejs)) {
			$page   = $pagejs;
			if ($page < 0)
				$page = 0;
        }
        
		$offset     = floatval($page) * $limit;
		$maxdisplay =(floatval($page) * $limit);
        $no         =((floatval($page) * $limit));
        $arrorgdet = getOrgDetail(2);
		
        if($param['notransaksisch'] != ''){
            $where.=" and notransaksi like '%".$param['notransaksisch']."%'";
        }
        $str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi = 'HR' AND kodeparameter = 'HRSDMCUTI' AND typenilai = '1'";
        $res=fetchdata($str);
        $nilai = $res[0]['nilai'];

        $kodejabatan = $_SESSION['empl']['kodejabatan'];


        // Check if $kodejabatan exists in $nilai
        if ($nilai == $kodejabatan) {
            $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname.".sdm_ijin WHERE lokasitugas in (".$arrorgdet.") and tipekaryawan='0' ".$where." ORDER BY darijam DESC";
        } else {
            $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname.".sdm_ijin WHERE karyawanid='".$_SESSION['standard']['userid']."' ".$where." ORDER BY darijam DESC";
        }
        
        $res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs     = owlBaris($res);
		$res        = fetchdata($str);
		$jlhbrs     = $res[0]['jmlhrow'];
        $totrows    = ceil($jlhbrs / $limit);
        if($totrows == 0){
            $totrows = 1;
        }
              
        
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++){
            $sel    = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        
        $frompage   = ((floatval($page)*$limit)+1);
        if(((floatval($page)+1)*$limit) > $jlhbrs){
            $topage = $jlhbrs;
        }else{
            $topage = ((floatval($page)+1)*$limit);
        }

        if($jlhbrs < 1){
            $tab.="<tr class=rowcontent>
                        <td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                    </tr>";
        }else{

        // Check if $kodejabatan exists in $nilai
        if ($nilai == $kodejabatan) {
            $iList  = "SELECT * FROM " . $dbname . ".sdm_ijin WHERE lokasitugas in (".$arrorgdet.") and tipekaryawan='0' ".$where." ORDER BY darijam DESC LIMIT ".$offset.",".$limit." ";
        } else {
            
            $iList  = "SELECT * FROM " . $dbname . ".sdm_ijin WHERE karyawanid='".$_SESSION['standard']['userid']."'  ".$where." ORDER BY darijam DESC LIMIT ".$offset.",".$limit." ";
        }

            $style = "";
            $style2 = "";
            $hasil  = fetchdata($iList);
            foreach ($hasil as $dList){
        	$arrnamakar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan'," karyawanid='".$dList['karyawanid']."'");
        	$arrjenisijin=makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin'," idjenis='".$dList['idjenis']."'");
                if($dList['statuspersetujuan'] == 1){
                    $statuscuti = $_SESSION['lang']['disetujui'];
                    $style = "style='background:green;color:#fff;'";

                }
                elseif ($dList['statuspersetujuan'] == 2) {
                    $statuscuti = $_SESSION['lang']['ditolak'];
                    $style = "style='background:red;color:#fff;'";

                }
                elseif ($dList['statuspersetujuan'] == 3) {
                    $statuscuti = 'Perbaikan';
                }
                elseif ($dList['statuspersetujuan'] == 9) {
                    $statuscuti = $_SESSION['lang']['proses']." ".$_SESSION['lang']['approve'];
                    $style = "";
                }else{
                    $statuscuti = $_SESSION['lang']['belumdiajukan'];
                    $style = "";
                }

                if($dList['statuspersetujuan_cancel'] == 1){
                    $statusbatalcuti = $_SESSION['lang']['disetujui'];
                    $style2 = "style='background:green;color:#fff;'";
                }
                elseif ($dList['statuspersetujuan_cancel'] == 2) {
                    $statusbatalcuti = $_SESSION['lang']['ditolak'];
                    $style2 = "style='background:red;color:#fff;'";
                }
                elseif ($dList['statuspersetujuan_cancel'] == 3) {
                    $statusbatalcuti = 'Perbaikan';
                    $style2 = "";
                }
                elseif ($dList['statuspersetujuan_cancel'] == 9) {
                    $statusbatalcuti = $_SESSION['lang']['proses']." ".$_SESSION['lang']['approve'];
                    $style2 = "";
                }else{
                    $statusbatalcuti = $_SESSION['lang']['belumdiajukan'];
                    $style2 = "";
                }

                $no+=1;
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=left>".$dList['notransaksi']."</td>";
                    $tab.="<td align=center>".$arrnamakar[$dList['karyawanid']]."</td>";
                    $tab.="<td align=center>".$dList['tanggal']."</td>";
                    $tab.="<td align=center>".$dList['keperluan']."</td>";
                    $tab.="<td align=center>".$dList['periodecuti']."</td>";
                    $tab.="<td align=center>".$arrjenisijin[$dList['idjenis']]."</td>";
                    $tab.="<td align=center>".$dList['darijam']."</td>";
                    $tab.="<td align=center>".$dList['sampaijam']."</td>";
                    $tab.="<td align=center ".$style.">".$statuscuti."</td>";
                    $tab.="<td align=center ".$style2.">".$statusbatalcuti."</td>";

                    if($dList['statuspersetujuan']==0 || $dList['statuspersetujuan']==3){
                        $tab.="<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $dList['notransaksi'] . "','" . $dList['karyawanid'] . "');\">
                                </td>";
                        $tab.="<td align=center>
                                <img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"del('" . $dList['notransaksi'] . "');\">
                            </td>";
                        $tab.="<td align=center><img src=images/skyblue/submit.jpg class=zImgBtn title='Ajukan ".$dList['notransaksi']."'   onclick=\"form_ajukan('".$dList['notransaksi']."');\"></td> ";
                    } else if($dList['statuspersetujuan']==9){
                        $tab.="<td align=center colspan=3><img src=images/icons/04/16/04.png class=zImgBtn height='30' title='Proses Persetujuan'></td>";
                    } else if($dList['statuspersetujuan']==1){
                        $tab.="<td align=center colspan=3><img src=images/icons/04/16/02.png  class=zImgBtn height='30' title='Disetujui' ></td>";
                    }else{
                        $tab.="<td align=center colspan=3><img src=images/icons/04/16/02.png  class=zImgBtn height='30' title='DiTolak' ></td>";
                    }
                    $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$dList['notransaksi']."');></td>";
                    $tab.="<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$dList['notransaksi']."')\" src='images/upload-2-xxl.png'/></td>";
                    $tab.="<td align=center><img src=images/pdf.jpg class=zImgBtn  title='".$_SESSION['lang']['pdf']."' onclick=\"pdf('".$dList['notransaksi']."');\"></td>";   
                $tab.="</tr>
                    </tbody>";
            }
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>
                                ".$frompage." to ".$topage." Of ".  $jlhbrs."
                            </td>
                        </tr>";
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>";
                            if($page!=0){
                                $footer .= "<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                            }
                $footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                            if((floatval($page)+1) != $totrows){
                                $footer .="<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                            }
            $footer .=     "</td>
                        </tr>";
        }
        echo $tab."####".$footer;
    break;

	case'formbatalcuti':
	break;

	case'batalcuti':	
	break;

    case'getKet':
	break;

    case 'delete':
        $str    = "Delete FROM " . $dbname . ".sdm_ijin WHERE notransaksi ='".$param['notransaksi']."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $str1    = "Delete FROM " . $dbname . ".log_approval_pengganti WHERE notransaksi ='".$param['notransaksi']."'";
        try{
            $owlPDO->exec($str1);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
 
    case'update': 
        $arrlokasitugas=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas'," karyawanid='".$param['karyawanid']."'");
        $arrtipekaryawan=makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan'," karyawanid='".$param['karyawanid']."'");
        $arrtipelokasi=makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe'," kodeorganisasi='".$arrlokasitugas[$param['karyawanid']]."'");
        $lokasitugas = $arrlokasitugas[$param['karyawanid']];
        $tipekaryawan = $arrtipekaryawan[$param['karyawanid']];
        $tipelokasitugas = $arrtipelokasi[$arrlokasitugas[$param['karyawanid']]];

        $str="select statuspotongan from ".$dbname.".sdm_5jenisijin where idjenis='".$param['idjenis']."'";
        $res=fetchdata($str);
        $statuspotongan=0;
        foreach($res as $val){
            $statuspotongan=$val['statuspotongan'];
        }

        $strApp="select * from ".$dbname.".setup_approval where karyawanid='".$param['karyawanid']."' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0 and trim($param['pengganti']) == ""){
            exit("Warning : Harap pilih/isi \"".$_SESSION['lang']['personalpengganti']."\" terlebih dahulu!");
        }else{
            //bebas tanpa pengganti .
            $rangetgl = rangeTanggal($param['tglAwal'],$param['tglEnd']);
            $brax='';
            foreach($rangetgl as $tglxz){
                $str="select * from ".$dbname.".sdm_ijin where karyawanid='".$param['karyawanid']."' and darijam<='".$tglxz."' and sampaijam>='".$tglxz."' and statuspersetujuan!='2' and statuspersetujuan_cancel!='1' and notransaksi!='".$param['notransaksi']."' ";
                $res=fetchData($str);
                $jmlhbaris=count($res);

                if($jmlhbaris>0)
                {
                    $brax.='tanggal '.tanggalnormal($tglxz).',';
                }
            }

            if($brax!=''){
                exit("Warning : terdapat tanggal yang sudah pernah diinput yakni : ".$brax);
            }

            $potonggaji=0;
            if($statuspotongan=='0' and $param['sisa']!=0 and $param['jumlahhk']>$param['sisa']){
                exit("Warning : Jumlah Hari Izin melebihi dari jumlah hak yang ada");
            }elseif($statuspotongan=='0' and $param['sisa']==0){
                $sisa=0;
            }elseif ($statuspotongan=='1' and $param['jumlahhk']>$param['sisa']) {
                exit("Warning : Jumlah Hari Izin melebihi dari jumlah hak yang ada");
            }elseif ($statuspotongan=='1' and $param['jumlahhk']<=$param['sisa']) {
                $sisa=$param['sisa']-$param['jumlahhk'];
            }elseif ($statuspotongan=='2' and $param['jumlahhk']<=$param['sisa']) {
                $sisa=$param['sisa']-$param['jumlahhk'];
            }elseif ($statuspotongan=='2' and $param['jumlahhk']>$param['sisa']) {
                $sisa=0;
                $potonggaji=$param['jumlahhk']-$param['sisa'];
            }elseif ($statuspotongan=='3' and $param['jumlahhk']>$param['sisa']) {
                exit("Warning : Jumlah Hari Izin melebihi dari jumlah hak yang ada");
            }

            
            // echo $sisa;
            // exit('error');
            $str    = "Delete FROM " . $dbname . ".sdm_ijin WHERE notransaksi ='".$param['notransaksi']."'";
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $arrtglawal = explode("-", $param['tglAwal']);
            $tglawalbenar = $arrtglawal[2] . "-" . $arrtglawal[1] . "-" . $arrtglawal[0]." ".$param['jam1'].":".$param['mnt1'].":00";

            $arrtglend = explode("-", $param['tglEnd']);
            $tglendbenar = $arrtglend[2] . "-" . $arrtglend[1] . "-" . $arrtglend[0]." ".$param['jam2'].":".$param['mnt2'].":00";

            $ha     = "INSERT INTO " . $dbname . ".sdm_ijin 
                        (`notransaksi`,`karyawanid`,`tanggal`,`keperluan`,`keterangan`,`darijam`,`sampaijam`,`idjenis`,`periodecuti`,`jumlahhari`,`alamatcuti`,`pengganti`,`nohp`,`hometrip`,`tanggalberangkat`,`rutekeberangkatan`,`tglpulang`,`rutekepulangan`,`statuspersetujuan`,`statuspersetujuan_cancel`,`statuspotongan`,`potonggaji`,`updateby`,`updatetime`,`tanggalkerja`,`lokasitugas`,`tipekaryawan`,`sumber`,`approval_pengganti`) VALUES 
                        ('" . $param['notransaksi'] . "','" . $param['karyawanid']. "','" . tanggalsystem($param['tglijin']) . "','" . $param['keperluan'] . "','" . $param['ket'] . "','" . $tglawalbenar . "','" .$tglendbenar."','" . $param['idjenis'] . "','" . $param['periodecuti']. "','" . $param['jumlahhk'] . "','" . $param['alamatcuti'] . "','" . $param['pengganti'] . "','" . $param['nohp'] . "','".$param['cb']."','".tanggalsystem($param['tglberangkat'])."','".$param['rutekeberangkatan']."','".tanggalsystem($param['tglpulang'])."','".$param['rutekepulangan']."','0','0','".$statuspotongan."','".$potonggaji."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".tanggalsystem($param['tanggalkerja'])."','".$lokasitugas."','".$tipekaryawan."','CUTISTAFF','".$param['apppengganti']."')";
          
            try{
                $owlPDO->exec($ha);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $instCuti = "";
            if($param['apppengganti'] != ''){
                $rangetgl = rangeTanggalarr(tanggalsystemn($param['tglAwal']),tanggalsystemn($param['tglEnd']));

                $str="select * from ".$dbname.".log_approval_pengganti where notransaksi = '".$param['notransaksi']."'";
                $res = fetchdata($str);
                $jlhbrs = count($res);

                if($jlhbrs > 0){
                    $str1    = "Delete FROM " . $dbname . ".log_approval_pengganti WHERE notransaksi = '".$param['notransaksi']."'";
                    try{
                        $owlPDO->exec($str1);
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }

                foreach($rangetgl as $tgl){		
                    $instCuti.="insert into ".$dbname.".log_approval_pengganti (notransaksi,karyawanid_cuti,karyawanid_pengganti,tanggal) values ('".$param['notransaksi']."','".$param['karyawanid']."','".$param['apppengganti']."','".$tgl."');";
                }

                if($instCuti != ""){
                    try{
                        $owlPDO->exec($instCuti);
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                }
            }

        }
	break;

	case 'preview':
        $str="select * from ".$dbname.".sdm_ijin where notransaksi='".$param['notransaksi']."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $karyawancuti=$bar['karyawanid'];
        $idjenis=$bar['idjenis'];
        $periodecuti=$bar['periodecuti'];
        $keperluan=$bar['keperluan'];
        $jumlahhari=$bar['jumlahhari'];
        $daritanggal=substr($bar['darijam'],0,10);
        $sampaitanggal=substr($bar['sampaijam'],0,10);
        $periodegaji=substr($bar['tanggal'],0,7);
        $statuscuti=$bar['statuspersetujuan'];
        $statusbatalcuti=$bar['statuspersetujuan_cancel'];
        $statuspotongan=$bar['statuspotongan'];

        $optnmkaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
        $arrHslx=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['koreksi']);
        
        $str="select * from ".$dbname.".approval_return where notransaksi='".$param['notransaksi']."' and jenispersetujuan='IJS' order by level asc";
        $res=fetchdata($str);

        $str="select * from ".$dbname.".approval where notransaksi='".$param['notransaksi']."' and jenispersetujuan='IJS' order by level asc";
        $res=fetchdata($str);

        $tabx.="<legend>Persetujuan Izin/Cuti Disetujui/Ditolak</legend><br><br><table cellspacing=0 border=1 width=100% style='text-align:center'>";
        $tabx.="<tr>";
        $tabx.="<td> Nama Penyetuju </td>";
        $tabx.="<td> Penyetuju Ke </td>";
        $tabx.="<td> Hasil </td>";
        $tabx.="<td> Alasan </td>";
        $tabx.="</tr>";
        foreach($res as $bar){
            $tabx.="<tr>";
            $tabx.="<td> ".$optnmkaryawan[$bar['karyawanid']]." </td>";
            $tabx.="<td> ".$bar['level']." </td>";
            $tabx.="<td> ".$arrHslx[$bar['status']]." </td>";
            $tabx.="<td> ".$bar['komentar']." </td>";
            $tabx.="</tr>";
        }
        $tabx.="</table><br><br>";

        $str="select * from ".$dbname.".sdm_ijin where notransaksi='".$param['notransaksi']."'";
        $res=fetchData($str);
        $tglawalx = substr($res[0]['darijam'],0,10);
        $tglsampaix = substr($res[0]['sampaijam'],0,10);
        $rangetgl = rangeTanggal($tglawalx,$tglsampaix);
        $brax=0;
        foreach($rangetgl as $tglxz){
            $str="select * from ".$dbname.".sdm_ijin where karyawanid='".$karyawancuti."' and sampaijam>='".$tglxz."' and statuspersetujuan!='2' and statuspersetujuan_cancel!='1' and notransaksi!='".$param['notransaksi']."' ";
            //echo $str;
            $res=fetchData($str);
            $jmlhbaris=count($res);

            if($jmlhbaris>0)
            {
                $brax=0;
            }
        }
       
            $str="select * from ".$dbname.".approval where notransaksi='".$param['notransaksi']."' and jenispersetujuan='IJSC' order by level asc";
            $res=fetchdata($str);

            $tabx.="<legend>Persetujuan Pembatalan Izin</legend><br><br><table cellspacing=0 border=1 width=100% style='text-align:center'>";
            $tabx.="<tr>";
            $tabx.="<td> Nama Penyetuju </td>";
            $tabx.="<td> Penyetuju Ke </td>";
            $tabx.="<td> Hasil </td>";
            $tabx.="<td> Alasan </td>";
            $tabx.="</tr>";
            foreach($res as $bar){
                $tabx.="<tr>";
                $tabx.="<td> ".$optnmkaryawan[$bar['karyawanid']]." </td>";
                $tabx.="<td> ".$bar['level']." </td>";
                $tabx.="<td> ".$arrHslx[$bar['status']]." </td>";
                $tabx.="<td> ".$bar['komentar']." </td>";
                $tabx.="</tr>";
            }
            $tabx.="</table><br><br>"; 

        if($brax==0 and ($statusbatalcuti=='0' or $statusbatalcuti=='2') and $statuscuti=='1'){
            $optJenis='';
            // $arrtipebatal=array("0"=>'Batal Keseluruhan',"1"=>'Masuk Lebih Awal');

            $arrtipebatal=array("0"=>'Batal Keseluruhan');
            foreach ($arrtipebatal as $key => $valx) {
                $optJenis.="<option value='".$key."'>".$valx."</option>";
            }
            $str="select * from ".$dbname.".sdm_ijin where notransaksi='".$param['notransaksi']."'";
            $res=fetchData($str);
            $karyawanid = $res[0]['karyawanid'];

            $str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
            $res=fetchData($str);
            $departemen = $res[0]['bagian'];
            $kodegolongan = $res[0]['kodegolongan'];
            $lokasitugas = $res[0]['lokasitugas'];


            ##CEK PER DEPARTEMEN
            $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='IJSC' and departemen='".$departemen."'";
            $res=fetchdata($str);
            $perdepartemen=$res[0]['kodeunit'];
            $where="";
            if($perdepartemen>0){
                $where.=" and departemen='".$departemen."'";
            }else{
                $where.=" and departemen=''";
            }

            $golongan     = $kodegolongan;
            
            ##CEK PER GOLONGAN
            $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='IJSC' and golongan='".$golongan."'";
            $res=fetchdata($str);
            $pergolongan=$res[0]['kodeunit'];
            if($pergolongan>0){
                $where.=" and golongan='".$golongan."'";
            }else{
                $where.=" and golongan=''";
            }
            
            ## APPROVAL DINAMIS SESUAI SETUP##
        
            //$optper=array();
            $optKryx=array();
            $optKrylevel=array();

            $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select * from ".$dbname.".setup_approval 
                    where jenispersetujuan='IJSC' and kodeunit='".$lokasitugas."' and karyawaniduser='".$karyawanid."' ".$where." order by level";  
            $res=fetchData($str);
            if(count($res) > 0){
                foreach($res as $key => $bar){
                    $whr        =" karyawanid='".$bar['karyawanid']."'";
                    $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                   
                   $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                    $optKrylevel[$bar['level']]=$bar['level'];
                    
                }
            }else{
                
                $str="select * from ".$dbname.".setup_approval 
                where jenispersetujuan='IJSC' and kodeunit='".$lokasitugas."' and karyawaniduser='' ".$where." order by level";  
                $res=fetchData($str);
                if(count($res)>0){
                    foreach($res as $key => $bar){
                        $whr        =" karyawanid='".$bar['karyawanid']."'";
                        $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                        
                        $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                        $optKrylevel[$bar['level']]=$bar['level'];

                    }
                }else{
                     $str="select * from ".$dbname.".setup_approval 
                    where jenispersetujuan='IJSC' and kodeunit='".$lokasitugas."' and karyawaniduser=''  order by level";  
                    $res=fetchData($str);
                     foreach($res as $key => $bar){
                        $whr        =" karyawanid='".$bar['karyawanid']."'";
                        $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                        
                        $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                        $optKrylevel[$bar['level']]=$bar['level'];

                    }
                }
            }

            
            
            $tabx.="<legend>Pembatalan Izin</legend><table cellspacing=0 border=0 width=100%>";
            $jumlahlevel=count($optKrylevel);    
            $tabx.="<input hidden id=jlh value='1'>";
            $tabx.="<input hidden id=notransaksi_ajukan value='".$param['notransaksi']."'>";
            if($jumlahlevel>0){
                //foreach ($optKrylevel as $key) {
                    $optKry='';
                    foreach ($optKryx[1] as $key2 => $val) {
                        $optKry.=$val;
                    }
                        $tabx .= "<tr class=rowcontent>
                            <td>Approval ke-1</td>
                            <td width=5px>:</td>
                            <td><select id=kepada1 style='width:33%;'>".$optKry."</select></td>     
                        </tr>";
                    
                //}

            }else{           $jumlahlevel=1;
                        $tabx .= "<tr class=rowcontent>
                            <td>Approval ke-1</td>
                            <td width=5px>:</td>
                            <td><select id=kepada1 style='width:33%;'></select></td>
                        </tr>";
            }

            $tabx.="<tr>";
				$tabx.="<td> Tipe Pembatalan </td>";
				$tabx.="<td>: </td>";
				$tabx.="<td><select id='tipebatal' name='tipebatal' style='width:250px'  onchange='changetipe()'>".$optJenis."</select></td>";
            $tabx.="</tr>";
            $tabx.="<tr>";
				$tabx.="<td hidden> Tanggal Cuti Terakhir</td>";
				$tabx.="<td hidden>: </td>";
				$tabx.="<td hidden> <input type=text class=myinputtext id='tanggalcutiakhir' style='width:145px;' onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" readonly disabled> </td>";
            $tabx.="</tr>";
            $tabx.="<tr>";
				$tabx.="<td valign=top> Alasan </td>";
				$tabx.="<td valign=top>: </td>";
				$tabx.="<td valign=top><textarea id='alasanbatal'  style='width:228px;'  onkeypress='return tanpa_kutip(event);'></textarea></td>";
            $tabx.="</tr>";
            $tabx.="<tr class=rowcontent>
                    <td></td>
                    <td></td>
                    <td><button id=tomboldetail class=mybutton onclick=ajukanbatalcuti()>" . $_SESSION['lang']['diajukan'] . "</button></td>
                </tr>";
           
            $tabx.="</table>";
        }
        echo $tabx;


    break;
    case 'getedit':
        $str="select * from ".$dbname.".sdm_ijin where notransaksi ='".$param['notransaksi']."' ";
        $res=fetchData($str);

        echo tanggalnormal($res[0]['tanggal']).'###'.$res[0]['keperluan'].'###'.$res[0]['keterangan'].'###'.tanggalnormal($res[0]['darijam']).'###'.tanggalnormal($res[0]['sampaijam']).'###'.$res[0]['idjenis'].'###'.$res[0]['alamatcuti'].'###'.$res[0]['pengganti'].'###'.$res[0]['nohp'].'###'.$res[0]['hometrip'].'###'.tanggalnormal($res[0]['tanggalberangkat']).'###'.$res[0]['rutekeberangkatan'].'###'.tanggalnormal($res[0]['tglpulang']).'###'.$res[0]['rutekepulangan'].'###'.tanggalnormal($res[0]['tanggalkerja']).'###'.'<option value'.$res[0]['periodecuti'].'>'.$res[0]['periodecuti'].'</option>'.'###'.substr($res[0]['darijam'],11,2).'###'.substr($res[0]['darijam'],14,2).'###'.substr($res[0]['sampaijam'],11,2).'###'.substr($res[0]['sampaijam'],14,2).'###'.$res[0]['approval_pengganti'];
    break;
    case 'pdf':
        $pattern = "/[\s,]+/";//space Regex
        $str="select a.*,b.namakaryawan,b.nik,b.bagian,b.kodejabatan,b.subbagian from ".$dbname.".sdm_ijin a 
        left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
        where a.notransaksi='".$param['notransaksi']."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $karyawancuti=$bar['karyawanid'];
        $idjenis=$bar['idjenis'];
        $periodecuti=$bar['periodecuti'];
        $keperluan=$bar['keperluan'];
        $jumlahhari=$bar['jumlahhari'];
        $drtglnya=$bar['darijam'];
        $sptglnya=$bar['sampaijam'];
       
        //tanggal pengajuan
        $tglPengajuan = preg_split($pattern, date("d n Y",strtotime($bar['tanggal'])));
        $tglPengajuan[1] = numToMonth($tglPengajuan[1],substr($_SESSION['language'],0,1),'long');
        $tglPengajuan = implode(" ",$tglPengajuan);

        //dari tanggal
        $daritanggal = preg_split($pattern, date("d n Y",strtotime($bar['darijam'])));
        $daritanggal[1] = numToMonth($daritanggal[1],substr($_SESSION['language'],0,1),'long');
        $daritanggal = implode(" ",$daritanggal);

        $sampaitanggal = preg_split($pattern, date("d n Y",strtotime($bar['sampaijam'])));
        $sampaitanggal[1] = numToMonth($sampaitanggal[1],substr($_SESSION['language'],0,1),'long');
        $sampaitanggal = implode(" ",$sampaitanggal);
        
        //$sampaitanggal=substr($bar['sampaijam'],0,10);
        $periodegaji=$bar['periodecuti'];
        $statuscuti=$bar['statuspersetujuan'];
        $statusbatalcuti=$bar['statuspersetujuan_cancel'];
        $statuspotongan=$bar['statuspotongan'];
        $potonggaji=$bar['potonggaji'];
        

        $hakcutiawal=0;
        $diambilsebelumnya=0;
        
        if($statuspotongan=='1' or $statuspotongan=='2'){
            $str="select * from ".$dbname.".sdm_cutiht where karyawanid ='".$karyawancuti."' and periodecuti='".$periodecuti."' and kodeorg = '".getKary($karyawancuti,'lokasitugas')."'";
            $res=fetchData($str);
            if($res[0]['hakcuti']>0){
                $hakcutiawal=$res[0]['hakcuti']+$res[0]['cutitambahan']+$res[0]['adjs_hakcuti'];
                $diambilcutinya=$res[0]['diambil'];
            }

            $str="select sum(jumlahhari) as diambil from ".$dbname.".sdm_ijin where karyawanid ='".$karyawancuti."' and statuspotongan in (1,2) and tanggal<='".$bar['tanggal']."' and notransaksi!='".$param['notransaksi']."' and statuspersetujuan!=2 and statuspersetujuan_cancel!=1";
            $res=fetchData($str);
            if($res[0]['diambil']>0){
                $diambilsebelumnya=$res[0]['diambil'];
            }

            $str="select sum(jumlahhari) as diambil from ".$dbname.".sdm_ijin where karyawanid ='".$karyawancuti."' and periodecuti = '".$periodecuti."' and statuspotongan in (1,2) and statuspersetujuan = 1";
            $res=fetchData($str);
            if($res[0]['diambil']>0){
                $allHariAmbil = $res[0]['diambil'];
            }

            if($diambilcutinya != $allHariAmbil){
                $dataInjek = $diambilcutinya - $allHariAmbil;
            }
        }

        $sisa=$hakcutiawal-$diambilsebelumnya - $dataInjek ;
        $sisaAfter = $sisa-$jumlahhari;
        
        $arrjenis = makeOption($dbname,'sdm_5jenisijin','idjenis,jenisijin',"idjenis='".$idjenis."'");
        $arrJabatan = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

        $string = "SELECT karyawanid FROM ".$dbname.".`approval` WHERE `notransaksi` = '".$param['notransaksi']."' AND `status` = '1'";
        $hasil = fetchdata($string);

        $ttdpembuat = makeOption($dbname,'setup_ttd','karyawanid,file',"kode='TTD'");


        if($bar['subbagian'] == ''){
            $bagian = "KANTOR";
        }else{
            $bagian = getNamaOrg($bar['subbagian']);
        }

        $style = "style='border:1px solid #000;'";
        $border = "style='border:1px solid #000;'";
        $tab="<style>table{border:1px solid #000;}th{padding:10px;}th.right{border-left:1px solid #000;}td{padding:15px;}th{padding:15px;}
        td.isi{padding:5px 15px;}.title{font-size:20px;}
        td.ttd{height:70px;border-bottom:solid 1px #000;}td.jedattd{height:2%;}td.batas{width:10px;}</style>";
        $tab .="<table class=sortable cellspacing=0 border=0 width='100%'>
                    <thead ".$border.">
                         <tr class=rowcontent>
                            <th colspan='14' align=center ".$border." class='title'><b>Leave Application Form</b></th>
                        </tr>
                         <tr class=rowcontent style='font-size:15px;'>
                            <th ></th>
                            <th colspan='6'>Name: ".@$bar['namakaryawan']."</th>
                            <th colspan='3' class=right>Lokasi Tugas : ".getNamaOrg($bar['lokasitugas'])."</th>
                            <th colspan='2' class=right>Divisi : ".$bagian."</th>
                            <th colspan='2' class=right>Jabatan : ".$arrJabatan[$bar['kodejabatan']]."</th>
                        </tr>
                    </thead>
                    <thead ".$border.">
                        <tr class=rowcontent>
                            <th></th>
                            <th colspan='6'>NIK: ".@$bar['nik']."</th>
                            <th colspan='6' class=right>Form Submission Date: ".@$tglPengajuan."</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                         <tr class=rowcontent>
                            <td colspan='14' class='jedattd'></td>
                        </tr>";

                        if($statuspotongan==1 or $statuspotongan==2){
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='12' class='isi'>Balance of Annual Leave: ".$sisa." days</td>
                            <td></td>
                        </tr>";

                        }
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='6' class='isi'>Date of Leave: <br><b>".$daritanggal."-".$sampaitanggal."</b></td>
                            <td colspan='6' class='isi'>/ ".$jumlahhari." days</td>
                            <td></td>
                        </tr>
                        <tr class=rowcontent>
                            <td></td>
                            <td colspan='6' class='isi'>Reason: <b>".$keperluan."</b></td>
                            <td colspan='6' class='isi'></td>
                            <td></td>
                        </tr>
                        <tr class=rowcontent>
                            <td></td>
                            <td colspan='12'><u>".$arrjenis[$idjenis]."</u></td>
                            <td></td>
                        </tr>
                        ";

                        if($statuspotongan==1 or $statuspotongan==2){
                            $tab .="<tr class=rowcontent>
                                <td></td>
                                <td colspan='6'><b>Balance After: ".$sisaAfter." days</b></td>
                                <td colspan='6'></td>
                                <td></td>
                            </tr>
                            ";
                        }
                        
                $tab.=" 
                </tbody>
                </table><br>";
                
                $tab .="<table class=sortable cellspacing=0 border=0 width='100%' >";
                    $tab .="<thead>";
                        $tab .="<tr align=center>";
                            $tab .="<th>Pemohon,</th>";
                            $tab .="<th>Personalia,</th>";
                            $tab .="<th>Atasan Langsung,</th>";
                            $tab .="<th>Atasan Tidak Langsung,</th>";
                            $tab .="<th>Mengetahui,</th>";
                        $tab .="</tr>";   
                        $tab .="<tr align=center style='margin-top: 30px;' >";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                        $tab .="</tr>";    
                        $tab .="<tr align=center style='margin-top: 30px;' >";
                            $tab .="<th>____________</th>";
                            $tab .="<th>____________</th>";
                            $tab .="<th>____________</th>";
                            $tab .="<th>____________</th>";
                            $tab .="<th>____________</th>";
                        $tab .="</tr>";   
                        $tab .="<tr align=center style='margin-top: 30px;' >";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                            $tab .="<th></th>";
                        $tab .="</tr>";   
                $tab .="</table>";
        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $dompdf->stream("FormCuti_".$bar['namakaryawan']."", array("Attachment" => false));
    break;
    case'form_ajukan';
        $str="select * from ".$dbname.".sdm_ijin where notransaksi='".$param['notransaksi']."'";
        $res=fetchData($str);
        $karyawanid = $res[0]['karyawanid'];

        $str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
        $res=fetchData($str);
        $departemen = $res[0]['bagian'];
        $kodegolongan = getNamaGolongan($res[0]['kodegolongan']);
        $lokasitugas = $res[0]['lokasitugas'];


        ## CEK PER DEPARTEMEN
        $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='IJS' and departemen='".$departemen."'";
        $res=fetchdata($str);
        $perdepartemen=$res[0]['kodeunit'];
        $where="";
        if($perdepartemen>0){
            $where.=" and departemen='".$departemen."'";
        }else{
            $where.=" and departemen=''";
        }

        $golongan     = $kodegolongan;
        
        ##CEK PER GOLONGAN
        $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$lokasitugas."' and jenispersetujuan='IJS' and golongan='".$golongan."'";
        $res=fetchdata($str);
        $pergolongan=$res[0]['kodeunit'];
        if($pergolongan>0){
            $where.=" and golongan='".$golongan."'";
        }else{
            $where.=" and golongan=''";
        }
        
        ## APPROVAL DINAMIS SESUAI SETUP##
    
        //$optper=array();
        $optKryx=array();
        $optKrylevel=array();

        $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select * from ".$dbname.".setup_approval where jenispersetujuan='IJS' and kodeunit='".$lokasitugas."' and karyawaniduser='".$karyawanid."' ".$where." order by level";  
        $res=fetchData($str);
        if(count($res) > 0){
            foreach($res as $key => $bar){
                $whr        =" karyawanid='".$bar['karyawanid']."'";
                $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
               
                $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                $optKrylevel[$bar['level']]=$bar['level'];
            }
        }else{
            
            $str="select * from ".$dbname.".setup_approval where jenispersetujuan='IJS' and kodeunit='".$lokasitugas."' and karyawaniduser='' ".$where." order by level";  
            $res=fetchData($str);
            if(count($res)>0){
                foreach($res as $key => $bar){
                    $whr        =" karyawanid='".$bar['karyawanid']."'";
                    $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                    
                    $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                    $optKrylevel[$bar['level']]=$bar['level'];

                }
            }else{
                 $str="select * from ".$dbname.".setup_approval 
                where jenispersetujuan='IJS' and kodeunit='".$lokasitugas."' and karyawaniduser=''  order by level";  
                $res=fetchData($str);
                 foreach($res as $key => $bar){
                    $whr        =" karyawanid='".$bar['karyawanid']."'";
                    $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                    
                    $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                    $optKrylevel[$bar['level']]=$bar['level'];

                }
            }
        }

        
        $jumlahlevel=count($optKrylevel);    
        $tab.="<input hidden id=notransaksi_ajukan value='".$param['notransaksi']."'>";
        if($jumlahlevel>0){
                $jumlahlevel=1;
                $tab.="<input hidden id=jlh value='".$jumlahlevel."'>";
                $optKry='';
                foreach ($optKryx[1] as $key2 => $val) {
                    $optKry.=$val;
                }
                    $tab .= "<tr class=rowcontent>
                        <td>Approval ke-1</td>
                        <td width=5px>:</td>
                        <td><select id=kepada1 style='width:99%;'>".$optKry."</select></td>     
                    </tr>";
        }else{           
                    $jumlahlevel=1;
                    $tab.="<input hidden id=jlh value='".$jumlahlevel."'>";
                    $tab .= "<tr class=rowcontent>
                        <td>Approval ke-1</td>
                        <td width=5px>:</td>
                        <td><select id=kepada1 style='width:99%;'></select></td>
                    </tr>";
        }
        $tab.="<tr class=rowcontent>
                <td></td>
                <td></td>
                <td><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
            </tr>               
        </table>";
        echo $tab;
    break;
    case'ajukan':
        
        $str="select * from ".$dbname.".sdm_ijin where notransaksi='".$param['notransaksi']."'";
        $res=fetchData($str);
        $karyawanid = $res[0]['karyawanid'];
        $statuspotongan = $res[0]['statuspotongan'];
        $darijam = substr($res[0]['darijam'], 0,10);
        $sampaijam = substr($res[0]['sampaijam'], 0,10);
        $statuspersetujuan = $res[0]['statuspersetujuan'];

        if($statuspersetujuan=!'0' and $statuspersetujuan!='2'  and $statuspersetujuan!='3'){
            exit('Warning : Pengajuan ini sudah diajukan');
        }

        $str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
        $res=fetchData($str);
        $lokasitugas = $res[0]['lokasitugas'];

        for ($i=1; $i <= $param['jlh'] ; $i++) { 
            $per['persetujuan'.$i]=checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or $param['notransaksi']==''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }

        $str = "UPDATE " . $dbname . ".sdm_ijin SET statuspersetujuan='9' WHERE notransaksi= '" . $param['notransaksi'] . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }


        
        $jenispersetujuan='IJS';
        for($i=1; $i<=$param['jlh']; $i++){
            $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$lokasitugas."'";
            // exit("error : $str");
            $res=fetchData($str);
            $tipeapp = $res[0]['tipe'];
            $departemenapp = $res[0]['departemen'];
            $tipekaryawanapp = $res[0]['tipekaryawan'];
            $jabatanapp = $res[0]['jabatan'];
            
            if(count($res) > 0){
                if($tipeapp=='1'){
                    if($departemenapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($tipekaryawanapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($jabatanapp!='0'){
                        $str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            if($per['persetujuan'.$i]!=''){
                                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                                $owlPDO->exec($str);
                            }
                        }
                    }
                }else{
                    if($per['persetujuan'.$i]!=''){
                        $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
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
        }
    break;
    case'ajukanbatalcuti':
        $tipebatal              = checkPostGet('tipebatal','');
        $alasanbatal            = checkPostGet('alasanbatal','');
        $tanggalcutiakhir       = tanggalsystem(checkPostGet('tanggalcutiakhir',''));
        $jlh                    = checkPostGet('jlh','');
        if($tipebatal=='1' and ($tanggalcutiakhir=='' or $tanggalcutiakhir=='00000000')){
            exit('Warning : Untuk tipe batal masuk lebih awal harus disertai tanggal cuti terakhir');
        }

        if($alasanbatal==''){
            exit('Warning : Alasan pembatan cuti tidak boleh kosong');
        }

        $str="select * from ".$dbname.".sdm_ijin where notransaksi='".$param['notransaksi']."'";
        $res=fetchData($str);
        $karyawanid = $res[0]['karyawanid'];
        $akhircutisebelumnya = str_replace('-', '', substr($res[0]['sampaijam'],0,10));
        $awalcuti =str_replace('-', '', substr($res[0]['darijam'],0,10));

        if($tipebatal=='1' and $tanggalcutiakhir>=$akhircutisebelumnya){
            exit('Warning : Tanggal akhir cuti tidak boleh sama atau lebih besar dari tanggal akhir cuti yang telah disetuji');
        }

        if($tipebatal=='1' and $tanggalcutiakhir<$awalcuti){
            exit('Warning : Tanggal akhir cuti tidak boleh lebih kecil dari tanggal awal cuti yang telah disetuji');
        }

        // if($tipebatal=='0' and date('Ymd')>$awalcuti){
        //     $rangetgl = rangeTanggal($akhircutisebelumnya,date('Ymd'));
        //     $jumlah= count($rangetgl)-1;
        //     if($jumlah>7){
        //         exit('Warning : Pembatalan cuti tidak boleh lebih dari 7 hari dari tanggal awal cuti');
        //     }

        // }

        for ($i=1; $i <= $jlh ; $i++) { 
            $per['persetujuan'.$i]=checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or $param['notransaksi']==''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }
        $str = "UPDATE " . $dbname . ".sdm_ijin SET statuspersetujuan_cancel='9', sampaijamreal='".$tanggalcutiakhir."', alasanbatal='".$alasanbatal."' WHERE notransaksi= '" . $param['notransaksi'] . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }


        $str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
        $res=fetchData($str);
        $lokasitugas = $res[0]['lokasitugas'];

        $jenispersetujuan='IJSC';
        for($i=1; $i<=$jlh; $i++){
            $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$lokasitugas."'";
            // exit("error : $str");
            $res=fetchData($str);
            $tipeapp = $res[0]['tipe'];
            $departemenapp = $res[0]['departemen'];
            $tipekaryawanapp = $res[0]['tipekaryawan'];
            $jabatanapp = $res[0]['jabatan'];
            
            if(count($res) > 0){
                if($tipeapp=='1'){
                    if($departemenapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($tipekaryawanapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($jabatanapp!='0'){
                        $str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            if($per['persetujuan'.$i]!=''){
                                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                                $owlPDO->exec($str);
                            }
                        }
                    }
                }else{
                    if($per['persetujuan'.$i]!=''){
                        $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
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
        }
    break;


    case 'showupload':

        $valuehide='';
        if($param['hidevalue']=='1'){
            $valuehide='hidden';
        }
        
		$tab="";
		$tab.="<table ".$valuehide." cellspacing='1' border='0' id='uploadpopup' width=100%>";
		$tab.="<tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td>
                        <label id='notrans'>".$param['notransaksi']."</label>
                    </td>
			    </tr>";
		$tab.="<tr><td colspan=4><hr></td></tr>
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td>
						<input type='file' name='upload' id='upload' >
						<input hidden id='valuehidden' value='".$param['hidevalue']."'  />
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=\"submitfile()\">Submit</button>
					</td>
				</tr>
			</table>
			<p />";
			
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
			
		echo $tab;
	break;

    case 'submitfile':
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;

		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){	
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $pt."_".$his."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 2500000){
						$str = "insert into ".$dbname.".listfile_sdm_ijin values ('','".$data['notrans']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						try{
							$owlPDO->exec($str);
							if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
							file_put_contents($path.$filename,$file_tmpname);
						}
						catch(PDOException $e){
							echo " Gagal," . addslashes($e->getMessage());
						}
					}else{
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
	break;

    case 'loadfiles':

        if ($param['valuehidden']=='1'){
            $hidden = "hidden";
        }
		$no = 0;
		$tab = "";	
		$str="select * from ".$dbname.".listfile_sdm_ijin where notransaksi = '".$param['notransaksi']."' and status='1'";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}elseif($val['formaticon']=='.png'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}elseif($val['formaticon']=='.pdf'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}else{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['id']."')\">".$val['namafile']."</td>
					<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				
				$tab.="<img $hidden src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
				
				$tab."	</td>
				</tr>";
			}	
		}
		
		echo $tab;
	break;

    case 'deletefile':
		$str="delete from ".$dbname.".listfile_sdm_ijin where notransaksi ='".$param['notransaksi']."' and namafile='".$param['namafile']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	

    case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfile_sdm_ijin where id = '".$param['idfile']."'";
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