<?php
    session_start();
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');

    $param          = $_POST;if(count($param)==0){$param = $_GET;}

    $proses         = checkPostGet('proses', '');
    $kodeOrg        = checkPostGet('kodeOrg', '');
    $setupPremi     = checkPostGet('setupPremi', '');
    $status_aktif   = checkPostGet('status_aktif', '');

    $arrUnit = getOrgDetail(3);
    foreach($arrUnit as $key=>$val){
        $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
        $d=$induk[$key];
        if($d!=$n){			
            $optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
        }
        
        $optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
        
        $n=$d;
        if($d!=$n){			
            $optUnit.="</optgroup>";
        }
    }


    $arrSetupPremi=array("1"=>"Setup Premi 1", "2"=>"Setup Premi 2");
    $optSetupPremi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    foreach($arrSetupPremi as $brs => $isi)
    {
        $optSetupPremi.="<option value=".$brs.">".$isi."</option>";
    }

    $arrPos=array("1"=>"Aktif","0"=>"Non Aktif");
    foreach($arrPos as $brs => $isi)
    {
        $optStatus.="<option value=".$brs.">".$isi."</option>";
    }

    switch ($proses) {
        case 'addnew':
            echo "
                    <table cellspacing='1' border='0'>

                        <tr>
                            <td>". $_SESSION['lang']['kodeorg']."</td>
                            <td>:</td>
                            <td><select class=select2 id='kodeOrg' name='kodeOrg'  style='width:300px;'>". $optUnit ."</select></td>
                        </tr>
                        <tr>
                            <td>Setup Premi</td>
                            <td>:</td>
                            <td><select class=select2 id='setupPremi' name='setupPremi'  style='width:300px;'>". $optSetupPremi ."</select></td>
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

            if($kodeOrg == ''){
                exit("Warning : PT wajib diisi ");
            }

            if($setupPremi == ''){
                exit("Warning : Setup premi wajib diisi ");
            }

            $sqlCek = "select * from " . $dbname . ".vhc_5configpremi where pt='" . $kodeOrg . "'";
            $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
            $rowCek = owlBaris($queryCek);
            if ($rowCek < 1) {
                $sqlIns = "insert into " . $dbname . ".vhc_5configpremi (`pt`,`setuppremi`,`aktif`,`updateby`) values ('" . $kodeOrg . "','" . $setupPremi . "','" . $status_aktif . "','".$_SESSION['standard']['userid']."')";
                try {
                    $owlPDO->exec($sqlIns);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            } else {
                echo"Warningsistem : Config premi sudah pernah di input.";
                exit();
            }
        break;

         case'update':            
            $sqlCek = "select * from " . $dbname . ".vhc_5configpremi where pt='" . $kodeOrg . "' and setuppremi='".$setupPremi."' and aktif='".$status_aktif."'";
            $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
            $rowCek = owlBaris($queryCek);
            if ($rowCek > 1) {
                echo"Warningsistem : Data sudah ada !";
                exit();
            }else{
                $sql = "update " . $dbname . ".vhc_5configpremi set aktif='" . $status_aktif . "',updateby='" . $_SESSION['standard']['userid'] . "' where pt='" . $kodeOrg . "' and setuppremi='".$setupPremi."'";
                try {
                    $owlPDO->exec($sql);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        break;
        case'loaddata':
            echo "<table id=mytable class='sortable' cellspacing='1' border='0' cellpadding='2' width=100%>
                    <thead>
                        <tr class=rowheader>
                            <th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['pt']."</th>
                            <th style='text-align:center;'>Setup Premi</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['status']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['action']."</th>
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

                    
                    $str="select * from ".$dbname.".vhc_5configpremi where pt in (".getOrgDetail(4).")";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    while($bar=$res->fetch()){
                        $no+=1;
                        echo"<tr class=rowcontent>
                                <td align=center>".$no."</td>
                                <td>".$bar->pt." - ".getNamaOrg($bar->pt)."</td>
                                <td align=center>".$arrSetupPremi[$bar->setuppremi]."</td>
                                <td align=center>".$arrPos[$bar->aktif]."</td>
                                <td align=center>".($bar->updateby == '0000000000' ? getNamaKaryawan($bar->createby) : getNamaKaryawan($bar->updateby) )."</td>
                                <td align=center>
                                    <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdata('edit','".$bar->pt."','" . $bar->setuppremi . "','".$bar->aktif."');\">";
                            echo"</td>
                            </tr>";
                    }
            echo"   </tbody>
                    <tfoot>
                    </tfoot>
                </table>";
        break;
        default:
        break;
    }
?>