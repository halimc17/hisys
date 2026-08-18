<?php
    session_start();
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');

    $param          = $_POST;if(count($param)==0){$param = $_GET;}

    $proses         = checkPostGet('proses', '');
    $pt             = checkPostGet('pt', '');
    $kodekegiatan   = checkPostGet('kodekegiatan', '');
    $rupiah         = checkPostGet('rupiah', '');
    $status_aktif   = checkPostGet('status_aktif', '');
    
    $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $arrUnit = getOrgDetail(3);
    foreach($arrUnit as $key=>$val){
        $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
        $d=$induk[$key];
        if($d!=$n){			
            $optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
        }
    
        $optOrg.="<option value='".$key."'>".$key." - ".$val."</option>";			
        
        $n=$d;
        if($d!=$n){			
            $optOrg.="</optgroup>";
        }
    }

    $optKegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sqlKeg = "select * from " . $dbname . ".vhc_kegiatan  where tipe = 'traksi'";
    $res=fetchdata($sqlKeg);
    foreach($res as $val){
        $optKegiatan.="<option value='".$val['kodekegiatan']."'>".$val['kodekegiatan']." - ".$val['namakegiatan']."</option>";
    }


    $arrPos=array("1"=>"Aktif","0"=>"Non Aktif");
    foreach($arrPos as $brs => $isi){
        $optStatus.="<option value=".$brs.">".$isi."</option>";
    }


    switch ($proses) {
        case'getKegiatan':
            $tab="<table id=tabledt cellpadding=5 cellspacing=1 ".$border." class=sortable width=100%>
                <thead><tr class=rowheader>
                <td align=center >No</td>
                <td align=center >Kode<br>Kegiatan</td>
                <td align=center >Nama Kegiatan</td>
                <td align=center >Action</td>
            </tr></thead><tbody>";

            if($pt == ''){
                exit("Warning : PT tidak boleh kosong ");
            }

            $str="select * from ".$dbname.".vhc_5premikegiatanmandor where pt = '".$pt."' ";
            $res = fetchdata($str);
            foreach($res as $bar){
                $kdjab=$bar['kodekegiatan'];
            }

            if(count($res)>0){			
                $djab=explode(",",$kdjab);
                foreach($djab as $jab){
                    $detkeg[$jab]=$jab;
                }
            }
            
            
            $str="select * from ".$dbname.".vhc_kegiatan where tipe = 'traksi' order by kodekegiatan";
            $res = fetchdata($str);
            foreach($res as $bar){
                $no++;
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=center id=kodekgiatan".$no." name=kodekegiatan[]>".$bar['kodekegiatan']."</td>";
                    $tab.="<td align=left >".$bar['namakegiatan']."</td>";
                    if($detkeg[$bar['kodekegiatan']]!=""){
                        $tab.="<td align=center><input id=check".$no." name=check[] type=checkbox checked></td>";
                    }else{				
                        $tab.="<td align=center><input id=check".$no." name=check[] type=checkbox></td>";
                    }
                $tab.="</tr>";
            }
            
            $tab.="<tr class=rowcontent>";
                $tab.="<td align=center colspan=5><button class=mybutton onclick=addKegiatan('".$no."')> Add / Tambahkan</button></td>";
            $tab.="</tr>";
            $tab.="</table>";

            echo $tab;
	    break;
        case 'addnew':
            echo "
                    <table cellspacing='1' border='0'>
                        <tr>
                            <td>". $_SESSION['lang']['pt']."</td>
                            <td>:</td>
                            <td><select class=select2  id='pt' name='pt' style='width:300px;'>". $optOrg ."</select></td>
                        </tr>
                        <tr>
                            <td>". $_SESSION['lang']['kegiatan']."</td>
                            <td>:</td>
                            <td>
                                <input  id='kodekegiatan' class=myinputtext name='kodekegiatan' onclick=getKegiatan() style='width:295px;'>
                            </td>
                        </tr>
                        <tr>
                            <td>". $_SESSION['lang']['rp']."</td>
                            <td>:</td>
                            <td>
                                <input  id='rupiah' class=myinputtext name='rupiah' type='number'  style='width:295px;'>
                            </td>
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

            if ($pt == '') {
               exit("warning : PT wajib diisi" );
            } 

            $sqlCek = "select * from " . $dbname . ".vhc_5premikegiatanmandor where pt = '".$pt."'";
            $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
            $rowCek = owlBaris($queryCek);
            if ($rowCek < 1) {
                $sqlIns = "insert into " . $dbname . ".vhc_5premikegiatanmandor (`pt`,`kodekegiatan`,`rupiah`,`aktif`,`createby`,`createtime`) values ('" . $pt . "','" . $kodekegiatan . "','".$rupiah."','" . $status_aktif . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
                try {
                    $owlPDO->exec($sqlIns);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            } else {
                echo"Warningsistem : Data sudah pernah di input.";
                exit();
            }

        break;
        case'loaddata':
            echo "<table id=mytable class='sortable' cellspacing='1' border='0' cellpadding='2' width=100%>
                    <thead>
                        <tr class=rowheader>
                            <th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['pt']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['kodekegiatan']."</th> 
                            <th style='text-align:center;'>".$_SESSION['lang']['rp']."</th> 
                            <th style='text-align:center;'>".$_SESSION['lang']['status']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
                            <th style='text-align:center;'>".$_SESSION['lang']['action']."</th>
                        </tr>
                    </thead>
                    <tbody>";
        	
            $namaKegiatan=makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
                
            $str="select * from ".$dbname.".vhc_5premikegiatanmandor where pt in (".getOrgDetail(4).")";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $no+=1;

                $dkoegiatan=explode(",",$bar->kodekegiatan);
                echo"<tr class=rowcontent id='tr_".$no."'>
                        <td align=center>".$no."</td>
                        <td align=center>".getNamaOrg($bar->pt)."</td>";

                echo"<td>";
					foreach($dkoegiatan as $keg){
                        echo"".$keg." - ".$namaKegiatan[$keg]." <br>";
                    }
                echo"</td>";
                echo"<td align=center>".number_format($bar->rupiah,2)."</td>";
                echo"<td align=center>".$arrPos[$bar->aktif]."</td>
                        <td align=center>".($bar->updateby == '0000000000' ? getNamaKaryawan($bar->createby) : getNamaKaryawan($bar->updateby) )."</td>
                        <td align=center>
                            <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdata('edit','".$bar->pt."','" . $bar->kodekegiatan . "','" . $bar->rupiah . "','".$bar->aktif."');\">
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
            
            $sql = "update " . $dbname . ".vhc_5premikegiatanmandor set kodekegiatan='".$kodekegiatan."', rupiah ='".$rupiah."', aktif='" . $status_aktif . "',updateby='" . $_SESSION['standard']['userid'] . "' where pt='" . $pt . "'";
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