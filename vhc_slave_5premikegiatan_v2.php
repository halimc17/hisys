<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');

    $param              = $_POST;if(count($param)==0){$param = $_GET;}

    $method             = checkPostGet('method','');
    $pt                 = checkPostGet('pt','');
    $unit               = checkPostGet('unit','');
    $penanda            = checkPostGet('penanda','');
    $keg                = checkPostGet('keg','');
    $basis              = checkPostGet('basis', '');
    $posisi             = checkPostGet('posisi', '');
    $jenishari          = checkPostGet('jenishari', '');
    $jenisbasis         = checkPostGet('jenisbasis', '');
    $premilebihbasis    = checkPostGet('premilebihbasis', '');
    $statuspremi        = checkPostGet('statuspremi', '');
    $vhc                = checkPostGet('vhc', '');

    $nmOrg          =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
    $nmkeg          =makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
    $nmvhc          =makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');


    $optkeg=$optpt=$optvhc=$optunit=$optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

    $str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PT' and kodeorganisasi in (select induk from ".$dbname.".organisasi where kodeorganisasi in (".getOrgDetail(2).") )";   
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
    }

    $str = "SELECT * FROM ".$dbname.".vhc_kegiatan where tipe='traksi'";   
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $optkeg.="<option value='".$bar['kodekegiatan']."'>".$bar['namakegiatan']." [".$bar['satuan']."] [".$bar['noakun']."]</option>";
    }	

    $str = "SELECT * FROM ".$dbname.".vhc_5jenisvhc where kelompokvhc in('KD','AB') order by kelompokvhc asc";   
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $optvhc.="<option value='".$bar['jenisvhc']."'>".$bar['kelompokvhc']." - ".$bar['jenisvhc']." - ".$bar['namajenisvhc']."</option>";
    }

    $opthari="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $arrhari=array("kerja" => "Hari Kerja","jumat" => "Hari Jumat","libur" => "Hari Libur");
    $arrjnshr = getEnum($dbname,'vhc_5premikegiatan_v2','jenishari');
    foreach($arrjnshr as $isi){
        $opthari.="<option value=".$isi.">".$arrhari[$isi]."</option>";
    }

    $optjns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $arrjnsprmi = getEnum($dbname,'vhc_5premikegiatan_v2','jenisbasis');
    foreach($arrjnsprmi as $brs => $isi){
        $optjns.="<option value=".$brs.">".$isi."</option>";
    }

    $optPosition="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $arrPos     =array("0"=>"Operator","1"=>"Helper","2"=>"Driver");
    foreach($arrPos as $brs => $isi){
        $optPosition.="<option value=".$brs.">".$isi."</option>";
    }

    $optpenanda="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optpenanda.="<option value='<'><</option>";
    $optpenanda.="<option value='>'>></option>";
    $optpenanda.="<option value='>='>>=</option>";
    $optpenanda.="<option value='<='><=</option>";
    
    $arraktif=array("0" => "Non Aktif","1" => "Aktif");

    switch($method){
        case'loaddata':
            echo"   <table id=mytable class='sortable' cellspacing='1' border='0' cellpadding='2' width=100%>
                        <thead>
                            <tr class=rowheader>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodekegiatan']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['pt']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['unit']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jenisvch']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['namakegiatan']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['hari']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['premi']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['vhc_posisi']."</th>
                                <th rowspan=2 style='text-align:center;'>Basis</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['premibasis']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['status']."</th>
                                <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
                                <th colspan=2 style='text-align:center;'>".$_SESSION['lang']['action']."</th>
                            </tr>
                            <tr class=rowheader>
                                <th style='display:none;'></th>
                            </tr>
                        </thead>
                        <tbody>";
                        $where="";
                            
                        
                        $limit=20;
                        $page=0;
                        if(isset($_POST['page']))
                        {
                        $page=$_POST['page'];
                        if($page<0)
                        $page=0;
                        }
                        $offset=$page*$limit;
                        $maxdisplay=($page*$limit);

                        $ql2="select count(*) as jmlhrow from ".$dbname.".vhc_5premikegiatan_v2";
                        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                        $query2->setFetchMode(PDO::FETCH_OBJ);
                        while($jsl=$query2->fetch()){  
                            $jlhbrs= $jsl->jmlhrow;
                        }

                        $no=$maxdisplay;
                        $arrPos=array("0" => "Operator","1" => "Helper","2"=>"Driver");
                        $str="select * from ".$dbname.".vhc_5premikegiatan_v2 where unit in (".getOrgDetail(2).")";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()){
                            $no+=1;
                            echo "<tr class=rowcontent>
                                    <td align=left>".$bar['kodekegiatan']."</td>
                                    <td align=left>".$bar['kodept']." - ".$nmOrg[$bar['kodept']]."</td>
                                    <td align=left>".$bar['unit']." - ".$nmOrg[$bar['unit']]."</td>
                                    <td align=left>".$bar['vhc']." - ".$nmvhc[$bar['vhc']]."</td>
                                    <td align=left>".$nmkeg[$bar['kodekegiatan']]."</td>
                                    <td align=center>".$arrjnshr[$bar['jenishari']]."</td>
                                    <td align=center>".$arrjnsprmi[$bar['jenisbasis']]."</td>
                                    <td align=left>".$arrPos[$bar['posisi']]."</td>
                                    <td align=right>".number_format($bar['basis'],2)."</td>
                                    <td align=right>".number_format($bar['premilebihbasis'],2)."</td>
                                    <td align=center>".$arraktif[$bar['statuspremi']]."</td>
                                    <td align=center>".getNamaKaryawan($bar['updateby'])."</td>
                                    <td align=center>
                                        <img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editdata('edit','".$bar['kodept']."','".$bar['kodekegiatan']."','".$bar['basis']."','".$bar['premilebihbasis']."','".$bar['vhc']."','".$bar['jenishari']."','".$bar['jenisbasis']."','".$bar['posisi']."','".$bar['unit']."','".$bar['statuspremi']."');\">
                                    </td>
                                </tr>";
                        }
                    echo"</tbody>
                        <tfoot>
                        </tfoot>
                </table>";
        break;
        case 'addnew':
            echo"<fieldset><legend>".$_SESSION['lang']['entryForm']."</legend> 
                    <table border=0 cellpadding=1 cellspacing=1>
                        <tr>
                            <td>".$_SESSION['lang']['pt']."</td>
                            <td>:</td>
                            <td><select id=pt class=select2 style=\"width:250px;\" onchange=getkebun()>".$optpt."</select></td>

                            <td>".$_SESSION['lang']['unit']."</td>
                            <td>:</td>
                            <td><select id=unit  class=select2 style=\"width:250px;\" >".$optunit."</select></td>

                        </tr>
                        <tr>

                            <td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['kendaraan']."</td>
                            <td>:</td>
                            <td><select id=vhc class=select2 style=\"width:250px;\">".$optvhc."</select></td>

                            <td>".$_SESSION['lang']['kodekegiatan']."</td>
                            <td>:</td>
                            <td><select id=keg class=select2 style=\"width:250px;\" >".$optkeg."</select></td>

                        </tr>
                        <tr>

                            <td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['hari']."</td>
                            <td>:</td>
                            <td><select id=jenishari class=select2 name=jenishari style=width:250px;>".$opthari."</select></td>

                            <td>".$_SESSION['lang']['vhc_posisi']."</td>
                            <td>:</td>
                            <td><select id=posisi class=select2 name=posisi style=width:250px;>".$optPosition."</select></td>
                        </tr>
                        <tr>
                            <td>".$_SESSION['lang']['jenis']." Basis</td>
                            <td>:</td>
                            <td><select id=jenisbasis class=select2 name=jenisbasis style=width:250px;>".$optjns."</select></td>

                            <td>Basis</td> 
                            <td>:</td>
                            <td><input type=text id=basis onkeypress=\"return angka_doang(event);\"  onkeyup=\"z.numberFormat('basis')\" maxlength=6 class=myinputtextnumber style=\"width:80px;\"></td>

                        </tr>
                        <tr>
                            <td>".$_SESSION['lang']['premibasis']." </td> 
                            <td>:</td>
                            <td><input type=text id=premilebihbasis onkeypress=\"return angka_doang(event);\" onkeyup=\"z.numberFormat('premilebihbasis')\" maxlength=8 class=myinputtextnumber style=\"width:80px;\"></td>		

                            <td>".$_SESSION['lang']['status']."</td>
                            <td>:</td>
                            <td valign=middle><input type='checkbox' id=statuspremi name='statuspremi' checked=true>Aktif atau Non Aktif.</td>
                        </tr>
                    </table>
                    <table>
                    <tr>
                        <hr>
                        <td><input type=hidden id=method value='insert'></td><td colspan=5></td>
                        <td><button class=mybutton onclick=simpan()>Simpan</button><button class=mybutton onclick=hapus()>Batal</button></td>
                    </tr></table>
                </fieldset>";
        break;
        case 'getkebun':
            $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in ('KEBUN','PABRIK') and  kodeorganisasi in (".getOrgDetail(2).") ";
            $query=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch()){
                $optkebun.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
            }

            echo $optkebun;
        break;

        case 'insert':
            $sql="select * from ".$dbname.".vhc_5premikegiatan_v2 where unit='".$unit."' and kodekegiatan='".$keg."' and vhc='".$vhc."' and posisi='".$posisi."' and jenishari='".$jenishari."'";
            $hsl=fetchdata($sql);
            if(count($hsl)>0){
                exit("Warningsistem : Data sudah tersedia = Perusahaan ".getNamaOrg($pt).", kegiatan ".$nmkeg[$keg].", Jenis kendaraan ".$nmvhc[$vhc].", posisi ".$arrPos[$posisi].", Jenis hari ".$arrhari[$jenishari]."");
            }

            $str="INSERT INTO ".$dbname.".`vhc_5premikegiatan_v2` 
            (kodept,unit,kodekegiatan,basis,jenishari,jenisbasis,posisi,premilebihbasis,statuspremi,updateby,vhc,createby,createtime)
            values ('".$pt."','".$unit."','".$keg."','".$basis."','".$jenishari."','".$jenisbasis."','".$posisi."','".$premilebihbasis."','".$statuspremi."','".$_SESSION['standard']['userid']."','".$vhc."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')"; 
            try{
                $owlPDO->exec($str);
            }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
        break;

        case 'update':
            $str="update ".$dbname.".vhc_5premikegiatan_v2 set basis='".$basis."',premilebihbasis='".$premilebihbasis."',updateby='".$_SESSION['standard']['userid']."',jenisbasis='".$jenisbasis."',statuspremi='".$statuspremi."' where kodept='".$pt."' and unit = '".$unit."' and kodekegiatan='".$keg."' and vhc='".$vhc."' and posisi='".$posisi."' and jenishari='".$jenishari."'";
            try{
                $owlPDO->exec($str);
            }
            catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
        break;

        default:
        break;
    }
?>