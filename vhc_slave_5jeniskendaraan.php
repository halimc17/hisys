<?
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');

    $param          = $_POST;if(count($param)==0){$param = $_GET;}

    $method         = checkPostGet('method','');
    $doc            = checkPostGet('doc','');
    $jenisvhc       = $_POST['jenisvhc'];
    $namajenisvhc   = $_POST['namajenisvhc'];
    $noakun         = $_POST['noakun'];
    $kelompok       = $_POST['kelompok'];
    $path	        = "fileupload/jenis_vhc/";
    //get enum untuk kelompok vhc;
    $optklvhc="";
    $arrklvhc=getEnum($dbname,'vhc_5master','kelompokvhc');
    $arrtipevhc= array('AB'=>'Alat Berat','KD'=>'Kendaraan','MS'=>'Mesin');
    foreach($arrklvhc as $kei=>$fal)
    {
        switch($kei)
        {
            case 'AB':
                $_SESSION['language']!='EN'?$fal='Alat Berat':$fal='Heavy Equipment';
            break;
            case 'KD':                            
                $_SESSION['language']!='EN'?$fal='Kendaraan':$fal='Vehicle';
            break;
            case 'MS':
                $_SESSION['language']!='EN'? $fal='Mesin':$fal='Machinery';
            break;
        }
        $optklvhc.="<option value='".$kei."'>".$kei." - ".$fal."</option>";
    } 

    switch($method)
    {
        case 'addnew':
            echo"<fieldset style='width:100%;height:100%'><legend>".$_SESSION['lang']['entryForm']."</legend>
                <table border=0>
                    <tr>
                        <td>".$_SESSION['lang']['kodekelompok']."</td>
                        <td style='width:70px;'><select class=select2 style='width:100px;' id=kelompokvhc>".$optklvhc."</select></td>
                        
                        <td>".$_SESSION['lang']['tipe']."</td>
                        <td><input style='width:100px;height:28px;' onkeydown=\"upperCaseF(this)\" type=text id=jenisvhc size=5 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=2></td>
                    </tr>
                    <tr hidden>
                        <td>".$_SESSION['lang']['akunservice']."</td>
                        <td><input style='width:98px;' type=text id=noakun size=16 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber maxlength=10></td>
                    </tr>
                    <tr>
                        <td>".$_SESSION['lang']['namajenisvhc']."</td>
                        <td colspan=3><input style='width:233px;height:28px;' type=text id=namajenisvhc size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=45></td>
                    </tr>
                    <tr>
                        <td>".$_SESSION['lang']['namafile']."</td>
                        <td colspan=3><input type=file id=upload class=mybutton><input type=hidden id=hasilupload value=''></td>
                    </tr>
                    <tr>
                    <td><td colspan=3>
                    <input type=hidden id=method value='insert'>
                    <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                    <button class=mybutton onclick=cancelVhc()>".$_SESSION['lang']['cancel']."</button>
                    </table>
                </fieldset>";
        break;
        case 'submitfile':
            $tgl = date("YmdHis");
            $data = $_POST;
                if($_FILES['file']['error']==0)
                {
                    $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                    $newfilename = str_replace($filetype,'',$_FILES['file']['name']);
                    $filename 	= $data['kvhc']."-".$data['jvhc'].$filetype;
                    //$filename = $newfilename."_".$tgl."".$filetype;
                    //$file_tmpname = $_FILES['file']['tmp_name'];	
                    $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	

                    if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
                    {
                        if($_FILES['file']['size'] <= 500000)
                        {   

                            $sql = "select * from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$data['jvhc']."' and kelompokvhc = '".$data['kvhc']."'";
                            $hsl = fetchdata($sql);
                            $str = "update ".$dbname.".vhc_5jenisvhc set file='".$filename."' where jenisvhc='".$data['jvhc']."' and kelompokvhc = '".$data['kvhc']."'";
                            try
                            {
                                $owlPDO->exec($str);
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                if(file_exists($path.$hsl[0]['file']) and $hsl[0]['file']!=''){
                                    unlink($path.$hsl[0]['file']);
                                }
                                file_put_contents($path.$filename,$file_tmpname);
                                //move_uploaded_file($file_tmpname,$path.$filename);
                            }
                            catch(PDOException $e)
                            {
                                echo " Gagal," . addslashes($e->getMessage());
                            }
                        }
                        else
                        {
                            exit("warning : Ukuran file upload maksimal 500kb");
                        }
                    }else{
                        exit("Warning : Format file upload harus .jpg atau .jpeg");
                    }
                }
            
        break;
        case'isifile':
            $potong=explode('.',$doc);
            if($potong[1]=='pdf')
            {
                echo"<embed src=".$doc.">";
            }
            else
            {
                echo"<img src=".$doc." style='width: 1200px;height:440px'>";
            }
        break;
        case 'update':	
            $str="update ".$dbname.".vhc_5jenisvhc set namajenisvhc='".$namajenisvhc."'
                ,noakun='".$noakun."'
                ,kelompokvhc='".$kelompok."'
                ,updateby='" . $_SESSION['standard']['userid'] . "'
                where jenisvhc='".$jenisvhc."'";
            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        break;
        case 'insert':
            $sql = "select * from ".$dbname.".vhc_5jenisvhc where jenisvhc ='".$jenisvhc."'";
            $hsl = fetchdata($sql);
            if(count($hsl) >0){
                exit("Warningsistem : Data dengan Tipe ".$jenisvhc." sudah tersedia !");
            }else{
                $str="insert into ".$dbname.".vhc_5jenisvhc(jenisvhc,namajenisvhc,noakun,kelompokvhc,createby,createtime)
                    values('".$jenisvhc."','".$namajenisvhc."','".$noakun."','".$kelompok."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
                try{$owlPDO->exec($str); }
                catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n"; 
                    die(); 
                }
            }
        break;
        case 'delete':
            $str="delete from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$jenisvhc."'";
            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        break;
        case 'loaddata':
            $tab.=" <table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
                    <thead>
                        <tr class=rowheader>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodekelompok']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tipe']."</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['namajenisvhc']."</th>
                            <th rowspan=2 style='text-align:center;' hidden>".$_SESSION['lang']['noakun']."</th>		   
                            <th rowspan=2 style='text-align:center;'>Gambar<br>(".$_SESSION['lang']['namafile'].")</th>
                            <th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
                            <th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
                        </tr>
                        <tr class=rowheader>
                            <th style='display:none;'></th>
                        </tr>
                    </thead>
                    <tbody>";
                    $str1="select * from ".$dbname.".vhc_5jenisvhc order by kelompokvhc asc, jenisvhc asc";
                    $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                    $res1->setFetchMode(PDO::FETCH_OBJ);
                    while($bar1=$res1->fetch()){
                        @$n++;
                        $tab.="<tr class=rowcontent>
                                <td align=center>".$n."</td>			     
                                <td align=center>".$bar1->kelompokvhc." - ".$arrtipevhc[$bar1->kelompokvhc]."</td>			     
                                <td align=center>".$bar1->jenisvhc."</td>
                                <td>".$bar1->namajenisvhc."</td>
                                <td align=center hidden>".$bar1->noakun."</td>
                                <td align=center>";
                                if($bar1->file != ''){
                                    $tab.="<img src=".$path.$bar1->file." style=\"width:30px;height:30px\"><br>(".$bar1->file.")";
                                }else{
                                    $tab.="";
                                }
                        $tab.=" </td>
                                <td align=center>".getNamaKaryawan($bar1->updateby)."</td>
                                <td align=center><img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editdata('edit','".$bar1->jenisvhc."','".$bar1->namajenisvhc."','".$bar1->noakun."','".$bar1->kelompokvhc."','".$bar1->file."');\">
                                <img src=images/zoom.png class=zImgBtn onclick=\"isifile('".$path.$bar1->file."','event');\" style='padding-left:10px' title='Lihat Gambar'>
                                </td>
                            </tr>";
                    }
                    $tab.="</tbody>
                        <tfoot>
                        </tfoot>
                </table>";
            echo $tab;
        break;
        default:
        break;	
    }
?>
