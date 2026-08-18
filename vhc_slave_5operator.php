<?php
    session_start();
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');

    $param          = $_POST;if(count($param)==0){$param = $_GET;}

    $proses         = checkPostGet('proses', '');
    $kdKry          = checkPostGet('kdKry', '');
    $stat           = checkPostGet('status', '');
    $kodeOrg        = checkPostGet('kodeOrg', '');
    $kdVhc          = checkPostGet('kdVhc', '');
    $sim            = checkPostGet('sim', '');
    $jabatan        = checkPostGet('jabatan', '');
    $jabatanlama    = checkPostGet('jabatanlama', '');
    $vhclama        = checkPostGet('vhclama', '');

    if($_SESSION['org']['period']['start'] ==''){
        exit("Warning : Periode akuntansi/gaji tidak ada ");
    }
    
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

    // $optKary='';
    // $skary="select karyawanid,namakaryawan,lokasitugas from ".$dbname.".datakaryawan where tipekaryawan !='0' and lokasitugas in (".getOrgDetail(2).")  order by namakaryawan asc";//echo $skary;
    // $qkary=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
    // $qkary->setFetchMode(PDO::FETCH_ASSOC);
    // while($rkary=$qkary->fetch())
    // {
    //     $optKary.="<option value=".$rkary['karyawanid'].">".$rkary['namakaryawan']."- [".$rkary['karyawanid']."]</option>";
    // }

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
    $sNm="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','KANWIL')";
    $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
    $qNm->setFetchMode(PDO::FETCH_ASSOC);
    while($rNm=$qNm->fetch())
    {    
        $optOrg.="<option value=".$rNm['kodeorganisasi'].">".$rNm['namaorganisasi']."</option>";
    }


    $optKry="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sDtkry="select namakaryawan,karyawanid,lokasitugas,nik, subbagian from ".$dbname.".datakaryawan where alokasi=0 
    and lokasitugas = '".$_SESSION['empl']['lokasitugas']."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].") and tipekaryawan != '0' order by namakaryawan asc ";
    $qDtkry=$owlPDO->query($sDtkry) or die(print " Gagal: ".PDOException::getMessage());
    $qDtkry->setFetchMode(PDO::FETCH_ASSOC);
    while($rDtkry=$qDtkry->fetch())
    {
            $optKry.="<option value=".$rDtkry['karyawanid']." >".$rDtkry['nik']." - ".$rDtkry['namakaryawan']." ".$rDtkry['subbagian']."</option>";
    }


    $optKendaran="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sKendaran="select * from ".$dbname.".vhc_5master where kodeorg = '".$_SESSION['empl']['lokasitugas']."'  order by kodevhc desc";
    $qKendaran=$owlPDO->query($sKendaran) or die(print " Gagal: ".PDOException::getMessage());
    $qKendaran->setFetchMode(PDO::FETCH_ASSOC);
    while($rKendaran=$qKendaran->fetch())
    {
        $optKendaran.="<option value=".$rKendaran['kodevhc'].">".$nmjenis[$rKendaran['jenisvhc']]." [".$rKendaran['kodevhc']."] [".($rKendaran['nopol'] != '' ? $rKendaran['nopol'] : $rKendaran['detailvhc'])."] [".$rKendaran['kodetraksi']."]</option>";
    }


    $str="select * from ".$dbname.".vhc_5master";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $nmkendaraan[$bar['kodevhc']]=$bar['detailvhc'];
        $nopol[$bar['kodevhc']]=$bar['nopol'];
    }


    switch ($proses) {
        case 'addnew':
            echo "
                    <table cellspacing='1' border='0'>
                        <tr>
                            <td>". $_SESSION['lang']['namakaryawan']."</td>
                            <td>:</td>
                            <td><select class=select2 onchange='getnosim()' id='kd_karyawan' name='kd_karyawan' style='width:300px;'>". $optKry ."</select></td></tr>
                        <tr>
                            <td>". $_SESSION['lang']['kodevhc']."</td>
                            <td>:</td>
                            <td><select class=select2 id='kdVhc' name='kdVhc'  style='width:300px;'>". $optKendaran ."</select></td>
                            <td hidden><input type='hidden' id='kdVhclama' name='kdVhclama'></td>
                        </tr
                        <tr>
                            <td>". $_SESSION['lang']['jabatan']."</td>
                            <td>:</td>
                            <td><select class=select2 id='jabatan' name='jabatan'  style='width:300px;'>". $optJabatan ."</select></td>
                            <td hidden><select class=select2 id='jabatanlama' name='jabatanlama'  style='width:300px;'>". $optJabatan ."</select></td>
                        </tr>
                        
                        <tr>
                            <td>". $_SESSION['lang']['status']."</td>
                            <td>:</td>
                            <td><select class=select2 id='status' name='status'  style='width:300px;'>". $optStatus ."</select></td>
                        </tr>
                        
                        <tr>
                            <td>". $_SESSION['lang']['nomor'].' SIM' ."</td>
                            <td>:</td>
                            <td><input disabled id='sim' name='sim' class='myinputtext' style='width:296px;'></td>
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
            if ($kdKry == '') {
                echo"Warningsistem: Harap pilih Karyawan !";
                exit();
            } 
            
            $sqlCek = "select * from " . $dbname . ".vhc_5operator where karyawanid='" . $kdKry . "' and vhc='".$kdVhc."'";
            $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
            $rowCek = owlBaris($queryCek);
            if ($rowCek < 1) {
                $skry = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $kdKry . "'";
                $qkry = $owlPDO->query($skry) or die(print " Gagal: " . PDOException::getMessage());
                $qkry->setFetchMode(PDO::FETCH_ASSOC);
                $rkry = $qkry->fetch();
                $sqlIns = "insert into " . $dbname . ".vhc_5operator (`karyawanid`,`nama`,`aktif`,`vhc`,`jabatan`,`createby`,`createtime`) values ('" . $kdKry . "','" . $rkry['namakaryawan'] . "','" . $stat . "','" . $kdVhc . "','" . $jabatan . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
                //exit("error:$sqlIns");
                try {
                    $owlPDO->exec($sqlIns);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            } else {
                echo"Warningsistem : Nama karyawan ".getNamaKaryawan($kdKry)." sudah pernah di input.";
                exit();
            }
        break;
        case'deleteKry':
            $sdel = "delete from " . $dbname . ".vhc_5operator where karyawanid='" . $kdKry . "' AND vhc='".$kdVhc."'";
            try {
                $owlPDO->exec($sdel);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        break;
        case'loaddata':
            echo "<table id=mytable class='sortable' cellspacing='1' border='0' cellpadding='2' width=100%>
                    <thead>
                        <tr class=rowheader>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nik']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."</th> 
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['lokasitugas']."</th> 
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jabatan']."</th> 
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['status']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodevhc']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['detail']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nomor'].' SIM' ."</th>
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
                    
                    $ql2="select count(*) as jmlhrow from ".$dbname.".vhc_5operator where karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan where lokasitugas in (".getOrgDetail(2).")) order by nama asc";// echo $ql2;
                    $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                    $query2->setFetchMode(PDO::FETCH_OBJ);
                    while($jsl=$query2->fetch())
                    {
                        $jlhbrs= $jsl->jmlhrow;
                    }

                    $str="select * from ".$dbname.".vhc_5operator where karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan where lokasitugas in (".getOrgDetail(2).")) order by nama asc";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    while($bar=$res->fetch()){
                        $no+=1;
                        echo"<tr class=rowcontent id='tr_".$no."'>
                                <td align=center>".$no."</td>
                                <td>".getKary($bar->karyawanid,'nik')."</td>
                                <td>".$bar->nama."</td>
                                <td>".getKary($bar->karyawanid,'lokasitugas')."</td>
                                <td>".$arrJab[$bar->jabatan]."</td>
                                <td>".$arrPos[$bar->aktif]."</td>
                                <td>".$bar->vhc."</td>
                                <td>".$nmkendaraan[$bar->vhc]." ".$nopol[$bar->vhc]."</td>
                                <td>".getKary($bar->karyawanid,'sim')."</td>
                                <td>".($bar->updateby == '0000000000' ? getNamaKaryawan($bar->createby) : getNamaKaryawan($bar->updateby) )."</td>
                                <td align=center>
                                    <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdata('edit','".$bar->karyawanid."','" . $bar->jabatan . "','".$bar->aktif."','".$bar->vhc."','".getKary($bar->karyawanid,'sim')."');\">
                                    <img hidden src=images/application/application_delete.png class=zImgBtn style='padding-left:10px' title='Delete' onclick=\"delOpt('".$bar->karyawanid."','".$bar->vhc."');\">
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
            $where="kodevhc='".$kdVhc."'";
            $ckKlm=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc',$where);
            if($ckKlm[$kdVhc]=='KD'){
                /*if($sim==''&& $jabatan=='0'){
                    echo "warning : Driver / Supir wajib memiliki SIM \nDaftarkan SIM melalui menu : SDM - Transaksi - Data Karyawan";
                    exit();
                }*/
            }
            
            $sqlCek = "select * from " . $dbname . ".vhc_5operator where karyawanid='" . $kdKry . "' and jabatan='".$jabatan."' and aktif='$stat' and jabatan = '$jabatan' and vhc = '$kdVhc'";
            $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
            $rowCek = owlBaris($queryCek);
            if ($rowCek > 1) {
                echo"Warningsistem : data sudah ada !";
                exit();
            }
            else{
                $sql = "update " . $dbname . ".vhc_5operator set aktif='" . $stat . "', jabatan='" . $jabatan . "',vhc='" . $kdVhc . "',updateby='" . $_SESSION['standard']['userid'] . "' where karyawanid='" . $kdKry . "' and vhc = '".$kdVhc."'";
                try {
                    $owlPDO->exec($sql);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        break;
        case'getKrywan':
            $sDtkry = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $kodeOrg . "'";
            $qDtkry = $owlPDO->query($sDtkry) or die(print " Gagal: " . PDOException::getMessage());
            $qDtkry->setFetchMode(PDO::FETCH_ASSOC);
            while ($rDtkry = $qDtkry->fetch()) {
                $optKry.="<option value=" . $rDtkry['karyawanid'] . " " . ($rDtkry['karyawanid'] == $kdKry ? 'selected' : '') . ">" . $rDtkry['namakaryawan'] . "</option>";
            }
            echo $optKry;
        break;
        case'getnosim':
            $sDtkry2 = "select namakaryawan,karyawanid,sim from " . $dbname . ".datakaryawan where karyawanid='" . $kdKry . "'";
            $qDtkry2 = $owlPDO->query($sDtkry2) or die(print " Gagal: " . PDOException::getMessage());
            $qDtkry2->setFetchMode(PDO::FETCH_ASSOC);
            $rDtkry2 = $qDtkry2->fetch();
            echo $rDtkry2['sim'];
        break;
            
        default:
        break;
    }
?>