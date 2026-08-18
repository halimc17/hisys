<?
ini_set('display_errors',0);
error_reporting(0);
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$param = $_POST;


switch ($param['proses']) {

    case 'gettermin':
            // $opttermin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select * from ".$dbname.".log_potermin 
                where nopo='".$param['nopo']."' and bayar ='0' ";
                // exit('error'.$str);
            $query=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($query->fetch()){    
                $opttermin.="<option value=".$res['nopo'].">".$res['termin']."</option>";
            }
            echo $opttermin;
            break;


    case'posting':
    
            
        #update flag
        $str = "update " . $dbname . ".log_baservis set posting='1' where  id='" . $param['id'] . "' ";
                
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    
    
    case'loadData':
		$where="1=1";
        // if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
            // $where.="";
			 // $where.= " and b.lokasitugas in (".getOrgDetail(2).")";
        // }else{
             // $where.= " and b.lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
        // }
		 $where.= " and b.lokasitugas in (".getOrgDetail(2).")";
		
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $maxdisplay=($page*$limit);
        
        $offset = $page * $limit;
        $sql = "select * from " . $dbname . ".log_baservis";
        $query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        
        $str = "select a.*, b.namakaryawan from " . $dbname . ".log_baservis a left join datakaryawan b on b.karyawanid=a.createby where ".$where." limit " . $offset . "," . $limit . " ";

        $tab = '';
        $nor = 0;
        $nor=$maxdisplay;
        $qstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $qstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($rstr = $qstr->fetch()) {
        
        $hide="";
        $post="<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' 
                    onclick=\"posting('" . $rstr['id'] . "');\" >";
        if($rstr['posting']==1){
            $hide="hidden";
            $post="<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting');\" >";
        }
        
      
            $nor+=1;
            $tab.="<tr class=rowcontent>
                <td align=center>".$nor."</td>
                <td>" . $rstr['noso'] . "</td>
                <td style='min-width:80px;text-align:center'>" . tanggalnormal($rstr['tanggal']) . "</td>
                <td>" . $rstr['noba'] . "</td>
                <td>" . $rstr['keterangan'] . "</td>
                <td>" . $rstr['namakaryawan'] . "</td>
                
                <td align=center><img  ".$hide." src=images/application/application_edit.png class=resicon  title='Edit " . $rstr['noso'] . "' onclick=\"fillField('".$rstr['id']."','" . $rstr['noso'] . "','".tanggalnormal($rstr['tanggal'])."','" . $rstr['noba'] . "','" . $rstr['keterangan'] . "');\" ></td>
                <td align=center><img ".$hide." src=images/application/application_delete.png class=resicon  title='Hapus " . $rstr['noso'] . "' onclick=\"delData('" . $rstr['id'] . "');\" ></td>

                 <td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$rstr['noso']."')\" src='images/upload-2-xxl.png'/></td> 
                <td align=center>".$post."</td>
                </tr>";
        }
        $skeupenagih = "select count(*) as rowd from " . $dbname . ".log_baservis";
        $qkeupenagih = $owlPDO->query($skeupenagih) or die(print " Gagal: " . PDOException::getMessage());
        $rkeupenagih = owlBaris($qkeupenagih);
        $totrows = ceil($rkeupenagih / $limit);
        
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd = "</tr>
            <tr><td colspan=12 align=center>
            
            <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        echo $tab . "####" . $footd;
        break;

    case'getFormNoso':
        $form = "<fieldset style=float: left;>
               <legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['noso'] . "</legend>
               " . $_SESSION['lang']['noso'] . " &nbsp;<input type=text class=myinputtext id=nosocr />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=findNoso('" . $param['noso'] . "')>" . $_SESSION['lang']['find'] . "</button></fieldset>
               <fieldset><legend>" . $_SESSION['lang']['result'] . "</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
        break;

        case'getFormNoba':
        $form = "<fieldset style=float: left;>
               <legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['noso'] . "</legend>
               " . $_SESSION['lang']['noso'] . " &nbsp;<input type=text class=myinputtext id=nosocr />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=findNoba('" . $param['status'] . "')>" . $_SESSION['lang']['find'] . "</button></fieldset>
               <fieldset><legend>" . $_SESSION['lang']['result'] . "</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
        break;

    case'getnoso':    
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
        $tab.="<thead>";
        $tab.="<tr><td>" . $_SESSION['lang']['noso'] . "</td>";
        $tab.="<td style='text-align:center'>Nilai</td></tr></thead><tbody>";


        $sdata = "select * from ".$dbname.". log_poht where nopo like '%SO%' and nopo not in (select noso from ".$dbname.".log_baservis) and nopo like '%".$param['txtfind']."%' ";

        // exit('error'.$sdata);
        

        $qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        while($rdata=$qdata->fetch())
        {
            $brt = "style=cursor:pointer; onclick=setData('" . $rdata['nopo'] . "')";
            $tab.="<tr " . $brt . " class=rowcontent><td>" . $rdata['nopo'] . "</td>";
          
            $tab.="<td style='text-align:right'>" . number_format($rdata['nilaipo'], 2) . "</td></tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
        break;

        case'getnoba':    
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
        $tab.="<thead>";
        $tab.="<tr><td>NO. BA</td>";
        $tab.="<td style='text-align:center'>Nilai</td></tr></thead><tbody>";


        $sdata = "select * from ".$dbname.". log_baspk where notransaksi not in (select noba from ".$dbname.".log_baservis) and notransaksi like '%".$param['txtfind']."%' ";
        

        $qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        while($rdata=$qdata->fetch())
        {
            $brt = "style=cursor:pointer; onclick=setDataba('" . $rdata['notransaksi'] . "')";
            $tab.="<tr " . $brt . " class=rowcontent><td>" . $rdata['notransaksi'] . "</td>";
          
            $tab.="<td style='text-align:right'>" . number_format($rdata['jumlahrealisasi'], 2) . "</td></tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
        break;

    case'insert':
        if ($param['noso'] == '') 
        {
            exit("error: NO. SO tidak boleh kosong");
        }
        if ($param['noba'] == '') 
        {
            exit("error: NO. BA tidak boleh kosong");
        }


        $sinser = "insert into " . $dbname . ".log_baservis 
            (noso,tanggal,noba,keterangan,createby) values 
            ('" . $param['noso'] . "','".tanggalsystem($param['tanggal'])."','" . $param['noba'] . "',"
                . "'" . $param['keterangan'] . "','".$_SESSION['standard']['userid']."')";
                // exit('error'. $sinser);
        try{
                $owlPDO->exec($sinser); 
            }catch (PDOException $e){
                echo "Gagal : ".$e->getMessage();
            }
        
        break;

        case 'update':
        
        $str="update ".$dbname.".log_baservis set noso='".$param['noso']."', tanggal='".tanggalsystem($param['tanggal'])."', noba='".$param['noba']."', keterangan='".$param['keterangan']."'
         where id='".$param['id']."'";
         // exit('error'. $str);
        try{
            $owlPDO->exec($str); 
        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
        }
    break;

    case'getData':
    

    
        $sdata = "select distinct a.*,b.koderekanan,b.kodept from " . $dbname . ".pmn_suratperintahpengiriman a
                left join " . $dbname . ".pmn_kontrakjual b
                on a.nokontrak = b.nokontrak
                where a.nodo='" . $param['nodo'] . "'";
        $qdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        $rdata = $qdata->fetch();
        echo $rdata['nokontrak'] . "###" . $rdata['nodo'] . "###" . $rdata['koderekanan'] . "###" . $rdata['kodept'] . "###" . tanggalnormal($rdata['tanggaldo']) . "###" . $rdata['waktupenyerahan'] . "###" . $rdata['tempatpenyerahan'] . "###" . $rdata['dibuatoleh'] . "###" . $rdata['keterangan'] . "###" . $rdata['jabatan'] . "###" . $rdata['kepada'] . "###" . $rdata['ttd'] . "###" . number_format($rdata['qty'], 2) . "###" . $rdata['nokontrakinternal'] . "###" . $rdata['pphditanggung'] . "###" . $rdata['subsidi'] . "###" . $rdata['harga'] . "###" . $rdata['status_timbangan'];
        break;

         case 'showupload':
        $tab="";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['nomor']."</td>
                <td>:</td>
                <td>
                    <label id='noupload' style='display:none'>".$param['noso']."</label>
                    <label style='font-weight:bold'>".$param['noso']."</label>
                </td>
            </tr>";

        $tab.="<tr><td colspan=4><hr></td></tr>
                <tr>
                    <td>Filename</td>
                    <td>:</td>
                    <td>
                        <input type='file' name='upload' id='upload' >
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
   
        $path   = "fileupload/baservis/";
        $tgl = date("YmdHis");
        $his = date("His");
        $data = $_POST;
        $nmTemp=str_replace('-','',str_replace('/','',$data['noso']));
        if($data['fileupload']!=''){
            if($_FILES['file']['error']==0){    
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = $nmTemp."_".$his."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    if($_FILES['file']['size'] <= 250000){
                        $str = "insert into ".$dbname.".listfile_log_baservis values ('','".$data['noso']."','','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                        // exit('error'.$str);
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
	
	case'delData':
		$str="delete from ".$dbname.".log_baservis where id='".$param['id']."'";
		$owlPDO->exec($str);
	break;

    case 'loadfiles':
        $no = 0;
        $tab = "";  
        $str="select * from ".$dbname.".log_baservis where noso = '".$param['noso']."'";
        $res=fetchData($str);
        $posting=$res[0]['posting'];
        
        $str="select * from ".$dbname.".listfile_log_baservis where noso = '".$param['noso']."' and status='1'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                @$icon = seticonfile($val['formaticon']);   
                $tab.="<td style='text-align:center'>
                    <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>
                
                ";

                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                    <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($posting==0){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['id']."','".$val['noso']."');\" >";                 
                }
                
                $tab."  </td>
                </tr>";
            }   
        }
        
        echo $tab;
    break;



    case 'deletefile':
        $str="delete from ".$dbname.".listfile_log_baservis where id='".$param['id']."' and noso='".$param['noso']."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    default:
        break;
}

// case 'loadfiles':
//         $no = 0;
//         $tab = "";  
        
//         $str="select * from ".$dbname.".listfile_log_baservis where noso = '".$param['noso']."' and status='1'";
//         $res=fetchData($str);
//         if(empty($res)){
//             $tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
//         }else{
//             foreach($res as $key=>$val){
//                 $no++;
//                 $tab.="<tr class=rowcontent>
//                     <td style='text-align:center'>".$no."</td>";
//                 @$icon = seticonfile($val['formaticon']);   
//                 $tab.="<td style='text-align:center'>
//                     <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
//                 </td>
                
//                 <td style='text-align:center'>".getcriterianame($val['kriteriaefil'])."</td>";

//                 $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
//                 <td align=center>
//                     <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
//                 if($posting==0){
//                     $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['nomor']."','".$val['namafile']."');\" >";                 
//                 }
                
//                 $tab."  </td>
//                 </tr>";
//             }   
//         }
        
//         echo $tab;
//     break;

