<?php
    session_start();
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');

    $param          = $_POST;if(count($param)==0){$param = $_GET;}

    $proses         = checkPostGet('proses', '');

    $str="select * from ".$dbname.".vhc_5master";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $nmkendaraan[$bar['kodevhc']]=$bar['detailvhc'];
        $nopol[$bar['kodevhc']]=$bar['nopol'];
    }


    $nmjenis=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');

    $sNm="select kodeorganisasi from ".$dbname.".organisasi where tipe in ('TRAKSI')";
    $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
    $qNm->setFetchMode(PDO::FETCH_ASSOC);
    $orgtraksi='';
    while($rNm=$qNm->fetch())
    {    if($orgtraksi=='')
        {
            $orgtraksi="'".$rNm['kodeorganisasi']."'";
        }
        else
        {
            $orgtraksi.=",'".$rNm['kodeorganisasi']."'";
        }
    }

    $optKary='';
    $skary="select karyawanid,namakaryawan,lokasitugas from ".$dbname.".datakaryawan where tipekaryawan!='0'  order by namakaryawan asc";//echo $skary;
    $qkary=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
    $qkary->setFetchMode(PDO::FETCH_ASSOC);
    while($rkary=$qkary->fetch())
    {
        $optKary.="<option value=".$rkary['karyawanid'].">".$rkary['namakaryawan']."- [".$rkary['karyawanid']."]</option>";
    }
    $arrPos=array("0"=>"NonAktif","1"=>"Aktif");
    $optStatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    foreach($arrPos as $brs => $isi)
    {
        $optStatus.="<option value=".$brs.">".$isi."</option>";
    }


    $arrJab=array("0"=>"Operator","1"=>"Helper","2"=>"Driver");
    $optJabatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    foreach($arrJab as $brs1 => $isi1)
    {
        $optJabatan.="<option value=".$brs1.">".$isi1."</option>";
    }


    $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sNm="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN')";
    $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
    $qNm->setFetchMode(PDO::FETCH_ASSOC);
    while($rNm=$qNm->fetch())
    {    
        $optOrg.="<option value=".$rNm['kodeorganisasi'].">".$rNm['namaorganisasi']."</option>";
    }
    $optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sDtkry="select namakaryawan,karyawanid,lokasitugas,nik, subbagian from ".$dbname.".datakaryawan where alokasi=0 
    and lokasitugas in (".getOrgDetail(2).") and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].") and tipekaryawan != '0' order by namakaryawan asc ";
    $qDtkry=$owlPDO->query($sDtkry) or die(print " Gagal: ".PDOException::getMessage());
    $qDtkry->setFetchMode(PDO::FETCH_ASSOC);
    while($rDtkry=$qDtkry->fetch())
    {
            $optKry.="<option value=".$rDtkry['karyawanid']." >".$rDtkry['nik']." - ".$rDtkry['namakaryawan']." ".$rDtkry['subbagian']."</option>";
    }
    $optKendaran="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sKendaran="select * from ".$dbname.".vhc_5master where kodeorg in (".getOrgDetail(2).")  order by kodevhc desc";
    $qKendaran=$owlPDO->query($sKendaran) or die(print " Gagal: ".PDOException::getMessage());
    $qKendaran->setFetchMode(PDO::FETCH_ASSOC);
    while($rKendaran=$qKendaran->fetch())
    {
        $optKendaran.="<option value=".$rKendaran['kodevhc'].">".$nmjenis[$rKendaran['jenisvhc']]." [".$rKendaran['kodevhc']."] [".($rKendaran['nopol'] != '' ? $rKendaran['nopol'] : $rKendaran['detailvhc'])."] [".$rKendaran['kodetraksi']."]</option>";
    }
    
    $optsupp = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
    $sql = "SELECT a.* FROM " . $dbname . ".log_spkht a left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi where a.posting='0' and b.close='0' and b.jenis='ANGKUTTBS' order by a.notransaksi asc";
    $res = fetchdata($sql);
    
    foreach($res as $bar){
        $namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
        
        $optsupp.="<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
    }
    

    switch ($proses) {
        case 'addnew':
            echo "
                <table cellspacing='1' border='0'>
                    <tr>
                        <td>". $_SESSION['lang']['kodeorg']."</td>
                        <td>:</td>
                        <td><select class=select2 id='kodeorg' name='kodeorg' style='width:170px;'>". $optOrg ."</select></td></tr>
                    <tr>
                        <td>" . $_SESSION['lang']['tanggalberlaku'] . "</td> 
                        <td>:</td>
                        <td><input type=text class=myinputtext placeholder='Seluruhnya' id=tanggalberlaku onmousemove=setCalendar(this.id) onkeypress=return false; style=\"width:170px;height:31px;font-size:14px;text-align:center\" readonly/></td>
                    </tr
                    <tr>
                        <td>". $_SESSION['lang']['nospk']."</td>
                        <td>:</td>
                        <td><select class=select2 id='nospk' name='nospk'  style='width:170px;'>". $optsupp ."</select></td>
                    </tr>
                    
                    <tr>
                        <td>". $_SESSION['lang']['harga']."</td>
                        <td>:</td>
                        <td><input type=text id=harga onkeypress=\"return angka_doang(event);\" onkeyup=\"z.numberFormat('harga')\" maxlength=8 class=myinputtextnumber style=\"width:170px;height:31px;font-size:14px;\"></td>		
                    </tr>
                    
                    <input type='hidden' id='proses' value='insert' />
                    <tr>
                        <td><td><td>
                        <button class=mybutton onclick=simpan()>". $_SESSION['lang']['save']."</button>
                        <button class=mybutton onclick=batalOpt()>". $_SESSION['lang']['cancel']."</button></td>
                    </tr>
                </table>";
        break;
        case'insert':
            if ($param['kodeorg'] == '') {
                echo"Warningsystem: Harap pilih Karyawan !";
                exit();
            }
            
            $sqlCek = "SELECT * FROM $dbname.lgl_5hargasewahm WHERE kodeorg='{$param['kodeorg']}' AND nospk='{$param['nospk']}' AND tanggalberlaku = '".tanggaldb($param['tanggalberlaku'])."'";
            if (count(fetchData($sqlCek))<1) {
                $sqlIns = "INSERT INTO $dbname.lgl_5hargasewahm (`kodeorg`,`tanggalberlaku`,`nospk`,`harga`,`createby`,`createtime`) 
                            VALUES ('" . $param['kodeorg'] . "','" . tanggaldb($param['tanggalberlaku']) . "','" . $param['nospk'] . "','" . $param['harga'] . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
                try {$owlPDO->exec($sqlIns);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
            } else {
                echo"Warningsystem : Kode Organisasi ".getNamaOrg($param['kodeorg'])." dan Tanggal Berlaku ".$param['tanggalberlaku']." sudah pernah di input.";
            }
        break;
        case'delete':
            $sdel = "DELETE FROM $dbname .lgl_5hargasewahm WHERE kodeorg='{$param['kodeorg']}' AND tanggalberlaku='{$param['tanggalberlaku']}' AND nospk='{$param['nospk']}' ";
            try {$owlPDO->exec($sdel);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

        break;
        case'loaddata':
            echo "<table id=mytable class='sortable' cellspacing='1' border='0' cellpadding='2' width=100%>
                    <thead>
                        <tr class=rowheader>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['lokasitugas']."</th> 
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tanggalberlaku']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nospk']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['harga']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
                            <th style='text-align:center;' colspan=3>".$_SESSION['lang']['action']."</th>
                        </tr>
                        <tr class=rowheader>
                            <th style='display:none;'></th>
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
                    
                    $str="SELECT * FROM $dbname.lgl_5hargasewahm WHERE kodeorg IN (".getOrgDetail(2).") ORDER BY tanggalberlaku";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    while($bar=$res->fetch()){
                        $no+=1;
                        echo"<tr class=rowcontent id='tr_".$no."'>
                                <td align=center>".$no."</td>
                                <td>".getNamaOrg($bar->kodeorg)."</td>
                                <td>".tanggalnormal($bar->tanggalberlaku)."</td>
                                <td>".($bar->nospk == '' ? $_SESSION['lang']['all'] : $bar->nospk)."</td>
                                <td align=right>".number_format($bar->harga)."</td>
                                <td>".($bar->updateby == '0000000000' ? getNamaKaryawan($bar->createby) : getNamaKaryawan($bar->updateby) )."</td>
                                <td align=center>
                                    <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdata('edit','".$bar->kodeorg."','" . tanggalnormal($bar->tanggalberlaku) . "','".$bar->nospk."','".$bar->harga."');\">
                                    <img src=images/application/application_delete.png class=zImgBtn style='padding-left:10px' title='Delete' onclick=\"del('".$bar->kodeorg."','".$bar->tanggalberlaku."','".$bar->nospk."');\">
                                    ";
                            echo"</td>
                            </tr>";
                    }
            echo"   </tbody>
                    <tfoot>
                    </tfoot>
                </table>";
        break;
        case'update':
            $sql = "UPDATE $dbname.lgl_5hargasewahm SET harga='{$param['harga']}',updateby='{$_SESSION['standard']['userid']}' WHERE kodeorg='{$param['kodeorg']}' AND tanggalberlaku = '".tanggaldb($param['tanggalberlaku'])."' AND nospk='".$param['nospk']."'";
            try {$owlPDO->exec($sql);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        break;
        case'getKrywan':
            $sDtkry = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $kodeOrg . "'";
            $qDtkry = $owlPDO->query($sDtkry) or die(print " Gagal: " . PDOException::getMessage());
            $qDtkry->setFetchMode(PDO::FETCH_ASSOC);
            while ($rDtkry = $qDtkry->fetch()) {
                $optKry.="<option value=" . $rDtkry['karyawanid'] . " " . ($rDtkry['karyawanid'] == $param['kodeorg'] ? 'selected' : '') . ">" . $rDtkry['namakaryawan'] . "</option>";
            }
            echo $optKry;
        break;
        case'getnosim':
            $sDtkry2 = "select namakaryawan,karyawanid,sim from " . $dbname . ".datakaryawan where karyawanid='" . $param['kodeorg'] . "'";
            $qDtkry2 = $owlPDO->query($sDtkry2) or die(print " Gagal: " . PDOException::getMessage());
            $qDtkry2->setFetchMode(PDO::FETCH_ASSOC);
            $rDtkry2 = $qDtkry2->fetch();
            echo $rDtkry2['sim'];
        break;
            
        default:
        break;
    }
?>