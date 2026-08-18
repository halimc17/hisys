<?php
    session_start();
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');

    $param          = $_POST;if(count($param)==0){$param = $_GET;}

    $proses         = checkPostGet('proses', '');
    $kodetraksi     = checkPostGet('kodetraksi', '');
    $idkaryawan     = checkPostGet('idkaryawan', '');
    $status_aktif    = checkPostGet('status_aktif', '');

    
    if($_SESSION['org']['period']['start'] ==''){
        exit("Warning : Periode akuntansi/gaji tidak ada ");
    }
    
    $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sNm="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe ='TRAKSI'";
    $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
    $qNm->setFetchMode(PDO::FETCH_ASSOC);
    while($rNm=$qNm->fetch()){    
        $optOrg.="<option value=".$rNm['kodeorganisasi'].">".$rNm['kodeorganisasi']."- [".$rNm['namaorganisasi']."]</option>";
    }

    $optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sDtkry="select namakaryawan,karyawanid,lokasitugas,nik, subbagian from ".$dbname.".datakaryawan where lokasitugas in (".getOrgDetail(2).") and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].") and tipekaryawan != '0' order by namakaryawan asc ";
    $qDtkry=$owlPDO->query($sDtkry) or die(print " Gagal: ".PDOException::getMessage());
    $qDtkry->setFetchMode(PDO::FETCH_ASSOC);
    while($rDtkry=$qDtkry->fetch()){
            $optKary.="<option value=".$rDtkry['karyawanid']." >".$rDtkry['nik']." - ".$rDtkry['namakaryawan']." ".$rDtkry['subbagian']."</option>";
    }

    $arrPos=array("1"=>"Aktif","0"=>"Non Aktif");
    foreach($arrPos as $brs => $isi){
        $optStatus.="<option value=".$brs.">".$isi."</option>";
    }


    switch ($proses) {
        case 'addnew':
            echo "
                    <table cellspacing='1' border='0'>
                        <tr>
                            <td>". $_SESSION['lang']['kodetraksi']."</td>
                            <td>:</td>
                            <td><select class=select2  id='kodetraksi' name='kodetraksi' style='width:300px;'>". $optOrg ."</select></td>
                        </tr>

                        <tr>
                            <td>". $_SESSION['lang']['karyawan']."</td>
                            <td>:</td>
                            <td><select class=select2  id='idkaryawan' name='idkaryawan' style='width:300px;'>". $optKary ."</select></td>
                        </tr>
                        <tr>
                            <td>". $_SESSION['lang']['status']."</td>
                            <td>:</td>
                            <td><select class=select2 id='status_aktif' name='status_aktif'  style='width:300px;'>". $optStatus ."</select></td>
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
            if ($idkaryawan == '') {
               exit("warning : Karyawan wajib diisi" );
            } 

            $sqlCek = "select * from " . $dbname . ".vhc_5mandortraksi where kodetraksi='".$kodetraksi."' and karyawanid='" . $idkaryawan . "'";
            $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
            $rowCek = owlBaris($queryCek);
            if ($rowCek < 1) {
                $sqlIns = "insert into " . $dbname . ".vhc_5mandortraksi (`kodetraksi`,`karyawanid`,`aktif`,`createby`,`createtime`) values ('" . $kodetraksi . "','" . $idkaryawan . "','" . $status_aktif . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
                try {
                    $owlPDO->exec($sqlIns);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            } else {
                echo"Warningsistem : Nama karyawan ".getNamaKaryawan($idkaryawan)." sudah pernah di input.";
                exit();
            }
        break;
        case'loaddata':
            echo "<table id=mytable class='sortable' cellspacing='1' border='0' cellpadding='2' width=100%>
                    <thead>
                        <tr class=rowheader>
                            <th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['nik']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."</th> 
                            <th style='text-align:center;'>".$_SESSION['lang']['lokasitugas']."</th> 
                            <th style='text-align:center;'>".$_SESSION['lang']['status']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['action']."</th>
                        </tr>
                    </thead>
                    <tbody>";
                
            $str="select * from ".$dbname.".vhc_5mandortraksi where karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan where lokasitugas in (".getOrgDetail(2).")) ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $no+=1;
                echo"<tr class=rowcontent id='tr_".$no."'>
                        <td align=center>".$no."</td>
                        <td align=center>".getKary($bar->karyawanid,'nik')."</td>
                        <td align=center>".getKary($bar->karyawanid,'namakaryawan')."</td>
                        <td align=center>".getNamaOrg(getKary($bar->karyawanid,'lokasitugas'))."</td>
                        <td align=center>".$arrPos[$bar->aktif]."</td>
                        <td align=center>".($bar->updateby == '0000000000' ? getNamaKaryawan($bar->createby) : getNamaKaryawan($bar->updateby) )."</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdata('edit','".$bar->kodetraksi."','" . $bar->karyawanid . "','".$bar->aktif."');\">
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
            
            
            $sql = "update " . $dbname . ".vhc_5mandortraksi set aktif='" . $status_aktif . "',updateby='" . $_SESSION['standard']['userid'] . "' where karyawanid='" . $idkaryawan . "' and kodetraksi = '".$kodetraksi."'";
            try {
                $owlPDO->exec($sql);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
            
        break;
            
        default:
        break;
    }
?>