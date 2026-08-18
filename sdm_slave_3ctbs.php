<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$jab   = getPostingJabatan('pendapatanlain');

$method = checkPostGet('method', '');
$tanggalx = tanggalsystem(checkPostGet('tanggalx', ''));
$kom = checkPostGet('kom', '');
$kar = checkPostGet('kar', '');
$jum = checkPostGet('jum', '');
$ket = checkPostGet('ket', '');
$org = checkPostGet('org', '');
$idxj = checkPostGet('idxj', '');
$tipekar = checkPostGet('tipekar', '');
$txtBarang = checkPostGet('txtBarang', '');
$tanggalxsch = checkPostGet('tanggalxsch', '');
$komSch = checkPostGet('komSch', '');
$jlh = checkPostGet('jlh', '');
$keterangan = checkPostGet('keterangan', '');

$nmKom=makeOption($dbname,'sdm_5jenisijin','idjenis,jenisijin');
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$optjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$opttipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$optLok=makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$kar."'");
$optLoknull=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$kar."'");

switch($method){
    case'loadKar':

        $where = "";
        $where = " lokasitugas='" . $org . "' and (tanggalkeluar>='".$tanggalx."' or tanggalkeluar='0000-00-00')";
        
        if($tipekar==''){
            $xxxx='';
        }else{
            $xxxx="and tipekaryawan='".$tipekar."'";
        }

        $arrkarytdk='';
        $iKar="select * from ".$dbname.".sdm_ijin where  darijam<='".$tanggalx."' and sampaijam>='".$tanggalx."'   order by karyawanid";
        $res=fetchdata($iKar);
        foreach ($res as $key => $value) {
           if($arrkarytdk==''){
                $arrkarytdk="'".$value['karyawanid']."'";
           }else{
                $arrkarytdk.=",'".$value['karyawanid']."'";
           }
        }

        $str="select a.*,b.subbagian from ".$dbname.".sdm_cutiht a 
            left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
            where b.tipekaryawan='".$tipekar."' and lokasitugas='".$org."' and sisa<='0'";  
        $res=fetchData($str);
        foreach ($res as $key => $val) {
           if($arrkarytdk==''){
                $arrkarytdk="'".$value['karyawanid']."'";
           }else{
                $arrkarytdk.=",'".$value['karyawanid']."'";
           }
        }

        if($arrkarytdk!=''){
            $where.=" and karyawanid not in (".$arrkarytdk.")";
        }

        $iKar="select namakaryawan,karyawanid,nik,subbagian,lokasitugas from ".$dbname.".datakaryawan where  ".$where."  ".$xxxx." and tipekaryawan!='4'  order by namakaryawan";
        $nKar=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
        $nKar->setFetchMode(PDO::FETCH_ASSOC);
		$optKar="<option value=''></option>";
        while($dKar=$nKar->fetch()){
            $optKar.="<option value='".$dKar['karyawanid']."'>".$dKar['namakaryawan']." [ ".$dKar['nik']." ] ".$dKar['subbagian']."</option>";
        }
        echo $optKar;
    break;

    case'cekHeader':

        if($tanggalx==''||$kom==''||$org==''){
            exit("Error : Kode Organisasi, Periode Gaji, Jenis wajib diisi !");
        }

        if($tipekar==''){
            $sCek = "select * from " . $dbname . ".sdm_cutibersamaht where tanggal='" . $tanggalx . "' and idjenis='" . $kom . "' and kodeorg='" . $org . "'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=owlBaris($qCek);
            if ($rCek > 0) {
                echo"warning: Data keseluruhan tidak bisa , sudah pernah ada data di input, silahkan cek pada Tab List Data.";
                exit();
            }
    		
    		$sCek = "select * from " . $dbname . ".sdm_cutibersamaht where tanggal='" . $tanggalx . "' and idjenis='" . $kom . "' and kodeorg='" . $org . "' and status='9' ";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=owlBaris($qCek);
            if ($rCek > 0) {
                echo"warning: Keseluruhan tidak bisa , Data sudah ada diajukan.";
                exit();
            }

            $sCek = "select * from " . $dbname . ".sdm_cutibersamaht where tanggal='" . $tanggalx . "' and idjenis='" . $kom . "' and kodeorg='" . $org . "' and status='1' ";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=owlBaris($qCek);
            if ($rCek > 0) {
                echo"warning: Keseluruhan tidak bisa , Data sudah ada disetujui.";
                exit();
            }

        }else{
           $sCek = "select * from " . $dbname . ".sdm_cutibersamaht where tanggal='" . $tanggalx . "' and idjenis='" . $kom . "' and kodeorg='" . $org . "'  and tipekaryawan='" . $tipekar . "'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=owlBaris($qCek);
            if ($rCek > 0) {
                echo"warning: Data sudah pernah di input, silahkan cek pada Tab List Data.";
                exit();
            }
            
            $sCek = "select * from " . $dbname . ".sdm_cutibersamaht where tanggal='" . $tanggalx . "' and idjenis='" . $kom . "' and kodeorg='" . $org . "' and status='9'  and tipekaryawan='" . $tipekar . "'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=owlBaris($qCek);
            if ($rCek > 0) {
                echo"warning: Data sudah diajukan.";
                exit();
            } 

            $sCek = "select * from " . $dbname . ".sdm_cutibersamaht where tanggal='" . $tanggalx . "' and idjenis='" . $kom . "' and kodeorg='" . $org . "' and status='1'  and tipekaryawan='" . $tipekar . "'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=owlBaris($qCek);
            if ($rCek > 0) {
                echo"warning: Data sudah disetujui.";
                exit();
            } 
        }
        break;
    
    case'detail':

        $where = "";
        $where = " lokasitugas='" . $org . "' and (tanggalkeluar>='".$tanggalx."' or tanggalkeluar='0000-00-00')";
        

        if ($tipekar != '') {
            $where.=" and tipekaryawan='".$tipekar."' ";
        }


        if ($tipekar == '') {
            $where.=" and tipekaryawan not in ('4')";
        }

        $arrkarytdk='';
        $iKar="select * from ".$dbname.".sdm_ijin where  darijam<='".$tanggalx."' and sampaijam>='".$tanggalx."'   order by karyawanid";
       // echo $iKar;
        $res=fetchdata($iKar);
        foreach ($res as $key => $value) {
           if($arrkarytdk==''){
                $arrkarytdk="'".$value['karyawanid']."'";
           }else{
                $arrkarytdk.=",'".$value['karyawanid']."'";
           }
        }

        $ijnsijn="select * from ".$dbname.".sdm_5jenisijin where  idjenis='".$kom."' ";
        $resijn=fetchdata($ijnsijn);
        if($resijn[0]['statuspotongan']=='1' or $resijn[0]['statuspotongan']==2){
            $str="select a.*,b.subbagian from ".$dbname.".sdm_cutiht a 
                left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
                where b.tipekaryawan='".$tipekar."' and lokasitugas='".$org."' and sisa<='0' and periodecuti='".substr($tanggalx, 0,4)."'"; 
            //echo $str; 
            $res=fetchData($str);
            foreach ($res as $key => $val) {
               if($arrkarytdk==''){
                    $arrkarytdk="'".$val['karyawanid']."'";
               }else{
                    $arrkarytdk.=",'".$val['karyawanid']."'";
               }
            }
            
        }

        if($arrkarytdk!=''){
            $where.=" and karyawanid not in (".$arrkarytdk.")";
        }

        $iKar="select namakaryawan,karyawanid,nik,subbagian,lokasitugas from ".$dbname.".datakaryawan where  ".$where." and tipekaryawan!='4' order by namakaryawan";
        // exit('warning : '.$iKar);
        $res=fetchdata($iKar);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            if (($tipekar != '')) {
                exit('Warning : Karyawan pada '.$nmOrg[$org].' dengan tipe karyawan '.$opttipe[$tipekar].' tidak ada.');
            }else{
                exit('Warning : Karyawan pada '.$nmOrg[$org].' tidak ada.');
            }
        }else{

            echo"
            <fieldset><legend><b>".$_SESSION['lang']['detail']."</b></legend>
            <table border=0 cellpadding=1 cellspacing=1 class=sortable>
                <thead><tr class=rowheader>
                    <td align=center>".$_SESSION['lang']['nourut']."</td>
                    <td align=center>".$_SESSION['lang']['nik2']."</td>
                    <td align=center>".$_SESSION['lang']['namakaryawan']."</td>
                </tr></thead>";

            $no2+=0;
            $iKar="select namakaryawan,karyawanid,nik,subbagian,lokasitugas from ".$dbname.".datakaryawan where  ".$where."  and tipekaryawan!='4' order by namakaryawan";
            //exit('warning : '.$iKar);
            $nKar=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
            $nKar->setFetchMode(PDO::FETCH_ASSOC);
				$no='';
			while($dKar=$nKar->fetch()){
				$no+=1;
            echo"<tr class=rowcontent>
                    <td align=center>".$no."</td>
                    <td>".$dKar['nik']."</td>
					<td>".$dKar['namakaryawan']."
                        <input type=hidden id=kar_".$no2." value='".$dKar['karyawanid']."'></td>
                </tr>";
            $no2+=1;
            }
            echo "<tr class=rowcontent><td colspan=5 align=center><button class=mybutton onclick=savedt()>".$_SESSION['lang']['save']."</button></td></tr>
                  <input type=hidden id=totrows value='".$no2."' />
                  </table></fieldset>";
        }

    break;

    case'savedt':

            $awl=0;
            $sDet="insert into ".$dbname.".sdm_cutibersamadt (`kodeorg`, `tanggal`,`tipekaryawan`, `karyawanid`, `idjenis`, `updateby`) values ";
            //echo $_POST['totRow'];
            for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
                    if($awl==0){
                        $awl=1;
                        $sDet.=" ('".$org."','".$tanggalx."','".getkary($_POST['kar'][$arDt],'tipekaryawan')."','".$_POST['kar'][$arDt]."','".$kom."','".$_SESSION['standard']['userid']."')";
                    }else{
                        $sDet.=",('".$org."','".$tanggalx."','".getkary($_POST['kar'][$arDt],'tipekaryawan')."','".$_POST['kar'][$arDt]."','".$kom."','".$_SESSION['standard']['userid']."')";
                    }

                    $str1="select count(*) as jumlah from ".$dbname.".`sdm_cutibersamaht` 
                    where tanggal='".$tanggalx."' and idjenis ='".$kom."' and kodeorg = '".$org."' and tipekaryawan = '".getkary($_POST['kar'][$arDt],'tipekaryawan')."'";
                    $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                    $res1->setFetchMode(PDO::FETCH_ASSOC);
                    $bar1=$res1->fetch();
                    $jlh=$bar1['jumlah'];
                    if ($jlh>0){
                        
                    }else{
                       $str="INSERT INTO ".$dbname.".`sdm_cutibersamaht` (`kodeorg`,`tanggal`,`tipekaryawan`, `idjenis`, `keterangan`, `updateby`) values ('".$org."','".$tanggalx."','".getkary($_POST['kar'][$arDt],'tipekaryawan')."','".$kom."','".$keterangan."','".$_SESSION['standard']['userid']."')";
                       //exit("Error : ".$str);
                        try{
                            $owlPDO->exec($str); 
                         } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n"; 
                            //exit('Error');
                            die(); 
                        } 
                    }
                
            }

            //exit('warning : '.$sDet);
            try{ 
                $owlPDO->exec($sDet); 
            }
            catch (PDOException $e){
                echo $str;
            echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
            }

       
    break;

    case'saveDetail':

        $str1="select count(*) as jumlah from ".$dbname.".`sdm_cutibersamadt` 
        where tanggal='".$tanggalx."' and idjenis ='".$kom."' and kodeorg = '".$org."' and karyawanid = '".$kar."'";
        // exit('warning : '.$str1);
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $bar1=$res1->fetch();
        $jlh=$bar1['jumlah'];
        if ($jlh>0){
            exit ("Warning : Karyawan sudah pernah diinput.");
        }else{
             $str="INSERT INTO ".$dbname.".`sdm_cutibersamadt` (`kodeorg`,`tipekaryawan`, `tanggal`, `karyawanid`, `idjenis`, `updateby`)
            values ('".$org."','".getkary($kar,'tipekaryawan')."','".$tanggalx."','".$kar."','".$kom."','".$_SESSION['standard']['userid']."')";
            //exit("Error:$str"); 
            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        }

    
    break;	
	case'updatedetail':
		// $str = "update " . $dbname . ".sdm_cutibersamadt set jumlah='".$jum."', keterangan='".$ket."', updateby ='".$_SESSION['standard']['userid']."' where periodegaji='".$per."' and idkomponen ='".$kom."' and kodeorg = '".$org."' and karyawanid = '".$kar."'"; 
		
		// try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
    #####LOAD DETAIL DATA	
    case 'loadDetail';
        echo"<fieldset><legend>List Data</legend><table class=sortable cellspacing=1 border=0>
         <thead>
                 <tr class=rowheader>
                        <td>".$_SESSION['lang']['nourut']."</td>
                        <td align=center>".$_SESSION['lang']['nik2']."</td>
                        <td align=center >".$_SESSION['lang']['namakaryawan']."</td>
                        <td align=center >".$_SESSION['lang']['lokasitugas']."</td>
                        <td align=center colspan=2>".$_SESSION['lang']['action']."</td>
                 </tr>
        </thead>
        <tbody></fieldset>";
        $no=0;
		
        if ($tipekar == '') {
            if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
                $orgSort=" ";
            }else{
                $orgSort=" and tipekaryawan not in ('0')";
            }
        }else{
             $orgSort=" and tipekaryawan='".$tipekar."'";
        }

        $orgSort.="and kodeorg='".$org."' ";
        $a="select * from ".$dbname.".sdm_cutibersamadt where idjenis='".$kom."' and tanggal='".$tanggalx."' ".$orgSort." ";
        //echo $a;
        $b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
        $b->setFetchMode(PDO::FETCH_ASSOC);
        while($c=$b->fetch()){
                $optLokD=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$c['karyawanid']."'");
                $nik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$c['karyawanid']."'");
                $no+=1;
                echo"<tr class=rowcontent>
                    <td align=center>".$no."</td>
                    <td align=center>".$nik[$c['karyawanid']]."</td>
                    <td>".$nmKar[$c['karyawanid']]."</td>
                    <td>".$nmOrg[$optLokD[$c['karyawanid']]]."</td>
                    <td align=center width=25px>
                            <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"DelDetail('".tanggalnormal($c['tanggal'])."','".$c['karyawanid']."','".$c['idjenis']."');\" >					
                    </td>
                </tr>";
        }		
        echo"
               
                <tr>
                        <td colspan=8 align=center>
                                <button class=mybutton id=cancelDetail onclick=cancel()>".$_SESSION['lang']['selesai']."</button>
                        </td>
                 </tr>";
        echo"</table></fieldset>";
    break;

    case 'preview';
        echo"<fieldset><legend>List Data</legend><table class=sortable cellspacing=1 border=0>
         <thead>
                 <tr class=rowheader>
                        <td>".$_SESSION['lang']['nourut']."</td>
                        <td align=center>".$_SESSION['lang']['nik2']."</td>
                        <td align=center >".$_SESSION['lang']['namakaryawan']."</td>
                        <td align=center >".$_SESSION['lang']['lokasitugas']."</td>
                 </tr>
        </thead>
        <tbody></fieldset>";
        $no=0;
        
        
        $orgSort=" and tipekaryawan='".$tipekar."'";
        

        $orgSort.="and kodeorg='".$org."' ";
        $ql2="select * from ".$dbname.".sdm_cutibersamaht where idjenis='".$kom."' and tanggal='".$tanggalx."' ".$orgSort." ";
        $res = fetchdata($ql2);

        $a="select * from ".$dbname.".sdm_cutibersamadt where idjenis='".$kom."' and tanggal='".$tanggalx."' ".$orgSort." ";
        //echo $a;
        $b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
        $b->setFetchMode(PDO::FETCH_ASSOC);
        while($c=$b->fetch()){
                $optLokD=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$c['karyawanid']."'");
                $nik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$c['karyawanid']."'");
                $no+=1;
                echo"<tr class=rowcontent>
                    <td align=center>".$no."</td>
                    <td align=center>".$nik[$c['karyawanid']]."</td>
                    <td>".$nmKar[$c['karyawanid']]."</td>
                    <td>".$nmOrg[$optLokD[$c['karyawanid']]]."</td>
                </tr>";
        }       
        
        echo"</table></fieldset>";
        echo"<fieldset><legend>Persetujuan</legend><table class=sortable cellspacing=1 border=0>
         <thead>
                 <tr class=rowheader>
                        <td>".$_SESSION['lang']['nourut']."</td>
                        <td align=center>".$_SESSION['lang']['nik2']."</td>
                        <td align=center >".$_SESSION['lang']['namakaryawan']."</td>
                        <td align=center >".$_SESSION['lang']['status']."</td>
                        <td align=center >Komentar</td>
                 </tr>
        </thead>
        <tbody></fieldset>";
        $no=0;
        

        $arrstatus=array('0'=>'Proses Persetujuan','1'=>'Disetujui','2'=>'Ditolak','3'=>'Renconfirm/Diperbaiki');
        $a="select * from ".$dbname.".approval where notransaksi='".$res[0]['id']."' and jenispersetujuan='CBS' order by level asc";
       // echo $a;
        $b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
        $b->setFetchMode(PDO::FETCH_ASSOC);
        while($c=$b->fetch()){
                $optLokD=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$c['karyawanid']."'");
                $nik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$c['karyawanid']."'");
                echo"<tr class=rowcontent>
                    <td align=center>".$c['level']."</td>
                    <td align=center>".$nik[$c['karyawanid']]."</td>
                    <td>".getnamakaryawan($c['karyawanid'])."</td>
                    <td>".$arrstatus[$c['status']]."</td>
                    <td>".$c['komentar']."</td>
                </tr>";
        }     
        
        echo"</table></fieldset>";
 
        echo"<fieldset><legend>Persetujuan Sebelumnya</legend><table class=sortable cellspacing=1 border=0>
         <thead>
                 <tr class=rowheader>
                        <td>".$_SESSION['lang']['nourut']."</td>
                        <td align=center>".$_SESSION['lang']['nik2']."</td>
                        <td align=center >".$_SESSION['lang']['namakaryawan']."</td>
                        <td align=center >".$_SESSION['lang']['status']."</td>
                        <td align=center >Komentar</td>
                 </tr>
        </thead>
        <tbody></fieldset>";
        $no=0;
        

        $arrstatus=array('0'=>'Proses Persetujuan','1'=>'Disetujui','2'=>'Ditolak','3'=>'Renconfirm/Diperbaiki');
        $a="select * from ".$dbname.".approval_return where notransaksi='".$res[0]['id']."' and jenispersetujuan='CBS' order by level asc";
       // echo $a;
        $b=$owlPDO->query($a) or die(print " Gagal: ".PDOException::getMessage());
        $b->setFetchMode(PDO::FETCH_ASSOC);
        while($c=$b->fetch()){
                $optLokD=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$c['karyawanid']."'");
                $nik=makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$c['karyawanid']."'");
                echo"<tr class=rowcontent>
                    <td align=center>".$c['level']."</td>
                    <td align=center>".$nik[$c['karyawanid']]."</td>
                    <td>".getnamakaryawan($c['karyawanid'])."</td>
                    <td>".$arrstatus[$c['status']]."</td>
                    <td>".$c['komentar']."</td>
                </tr>";
        }     
        
        echo"</table></fieldset>";
    break;  	
	

    case'loadData':

        $orgSort = "kodeorg in (".getOrgDetail(2).")";
       
        if($tanggalxsch!=''){
            $tanggalxsch="and tanggal='".$tanggalxsch."'";
        }else{
            $tanggalxsch="";
        }

        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];

            if($page<0){
                $page=0;
            }
        }

        $offset=$page*$limit;
        $maxdisplay=($page*$limit);

        $ql2="select * from ".$dbname.".sdm_cutibersamaht where ".$orgSort." ".$tanggalxsch." ";
        $res = fetchdata($ql2);
        $jlhbrs = count($res);

        $arrstatus = array('0' => 'Belum Diajukan', '1' => 'Disetujui', '2' => 'Ditolak','9' => 'Proses Persetujuan');

		
        $no=$maxdisplay;
        $wh="";
            
            $i="select * from ".$dbname.".sdm_cutibersamaht where ".$orgSort." ".$perSch." ".$wh." order by tanggal desc limit ".$offset.",".$limit."";
            $res = fetchdata($i);
			foreach ($res as $d){
                $no+=1;
				
				
                echo "<tr class=rowcontent style=height:20px>";
                echo "<td align=center>".$no."</td>";
                echo "<td align=left>".$d['kodeorg']." - ".$nmOrg[$d['kodeorg']]."</td>";
                echo "<td align=center>".getNamaTipeKary($d['tipekaryawan'])."</td>";
                echo "<td align=center>".tanggalnormal($d['tanggal'])."</td>";
                echo "<td align=left>".$nmKom[$d['idjenis']]."</td>";
                echo "<td align=left>".$d['keterangan']."</td>";
                echo "<td align=center>".$arrstatus[$d['status']]."</td>";
				echo "<td align=left>".$nmKar[$d['updateby']]."</td>";
				
				if ($d['status'] != 1 and $d['status'] != 9) {					
					echo"<td align=center width=25px>
							<img src=images/application/application_edit.png  title='update' class=zImgBtn  caption='Edit' onclick=\"edit('".tanggalnormal($d['tanggal'])."','".$d['idjenis']."','".$d['kodeorg']."','".$d['tipekaryawan']."','".$d['keterangan']."');\"></td>";
					echo"<td align=center width=25px>
							<img src=images/application/application_delete.png  title='delete' class=zImgBtn caption='Delete' onclick=\"delHead('".tanggalnormal($d['tanggal'])."','".$d['idjenis']."','".$d['kodeorg']."','".$d['tipekaryawan']."');\"></td>";
					echo"<td align=center width=25px><img src=images/skyblue/submit.jpg class=zImgBtn class=zImgBtn height='30'  title='ajukan' onclick=\"form_ajukan('".$d['id']."');\" ></td>";
				}else{
					echo"<td width=25px></td><td width=25px></td><td width=25px></td>";
				}

                echo"<td align=center width=25px>
                            <img src=images/skyblue/zoom.png  title='preview' class=zImgBtn  caption='preview' onclick=\"previewx('".tanggalnormal($d['tanggal'])."','".$d['idjenis']."','".$d['kodeorg']."','".$d['tipekaryawan']."');\"></td>";
                echo "</tr>";
            }
		
		$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {$totrows = 1;}
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
        $footd = "";
        $footd.="</tr><tr><td colspan=12 align=center>";
        if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=loadData(" . ($page - 1) . ");>Prev</button>";}
        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=loadData(" . ($page + 1) . ");>Next</button>";}
        $footd.="</td></tr>";

        echo $tab . "####" . $footd;
    break;
    case'form_ajukan';
        $str="select * from ".$dbname.".sdm_cutibersamaht where id='".$idxj."'";
        $res=fetchData($str);
        $lokasitugas = $res[0]['kodeorg'];
        $optKryx=array();
        $optKrylevel=array();

        $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select * from ".$dbname.".setup_approval 
        where jenispersetujuan='CBS' and kodeunit='".$lokasitugas."' and karyawaniduser=''  order by level";  
        $res=fetchData($str);
            foreach($res as $key => $bar){
            $whr        =" karyawanid='".$bar['karyawanid']."'";
            $optnama    = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
            
            $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
            $optKrylevel[$bar['level']]=$bar['level'];
        }
       
        $jumlahlevel=count($optKrylevel);    
        $tab.="<input hidden id=notransaksi_ajukan value='".$idxj."'>";
        if($jumlahlevel>0){
                $jumlahlevel=1;
                $tab.="<input hidden id=jlh value='".$jumlahlevel."'>";
                $optKry='';
                foreach ($optKryx[1] as $key2 => $val) {
                    $optKry.=$val;
                }
                    $tab .= 
                    "<tr class=rowcontent>
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
        $param = $_POST;if(count($param)==0){$param = $_GET;}
        $param['notransaksi']=$idxj;
        $param['jlh']=$jlh;

        $str="select * from ".$dbname.".sdm_cutibersamaht where id='".$idxj."'";
        $res=fetchData($str);
        $lokasitugas = $res[0]['kodeorg'];

        for ($i=1; $i <= $param['jlh'] ; $i++) { 
            $per['persetujuan'.$i]=checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or $param['notransaksi']==''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }

        $str = "UPDATE " . $dbname . ".sdm_cutibersamaht SET status='9' WHERE id= '" . $idxj . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $jenispersetujuan='CBS';
        $str="select * from ".$dbname.".approval where notransaksi='".$idxj."' and jenispersetujuan= '" . $jenispersetujuan . "'";
        $res=fetchData($str);
        foreach ($res as $key => $val) {
            $str = "INSERT INTO " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status,komentar,keterangan,tanggal,nourut) values ('".$val['notransaksi']."','".$val['jenispersetujuan']."','".$val['level']."','".$val['karyawanid']."','".$val['status']."','".$val['komentar']."','".$val['keterangan']."','".$val['tanggal']."','".$val['nourut']."')";
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }

        $str = "DELETE FROM " . $dbname . ".approval where notransaksi='".$idxj."' and jenispersetujuan= '" . $jenispersetujuan . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        for($i=1; $i<=$param['jlh']; $i++){
            $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$lokasitugas."'";
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
                        //exit("error : $str");
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
       // echo $str;
    break;
    case'delHead':
        
        $i="delete from ".$dbname.".sdm_cutibersamaht where idjenis='".$kom."' and tanggal='".$tanggalx."' and kodeorg='".$org."' and tipekaryawan='".$tipekar."'"; 
        //echo $i;      
        try{
            $owlPDO->exec($i); 
            $x="delete from ".$dbname.".sdm_cutibersamadt where idjenis='".$kom."' and tanggal='".$tanggalx."' and kodeorg='".$org."' and tipekaryawan='".$tipekar."'";              
            try{
                $owlPDO->exec($x); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
        
    break;
		
    case'deleteDetail':
            
            $str="delete from ".$dbname.".sdm_cutibersamadt where karyawanid='".$kar."'  and idjenis='".$kom."' and tanggal='".$tanggalx."'";
            
            //exit("Error : ".$str);
            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }   
    break;
	
	
    default;
}
?>