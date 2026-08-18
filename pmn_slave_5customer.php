<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');

$kelompok = checkPostGet('kelompok','');
$kodecustomer = checkPostGet('kodecustomer','');
// $namasupplier = checkPostGet('namasupplier','');
// $statusup = checkPostGet('statusup','');
$method = checkPostGet('method','');
// $pages = checkPostGet('page', '');
// $txt_search = checkPostGet('txtsearch', '');
// $txtNoakun = checkPostGet('txtNoakun', '');
// $nocust = checkPostGet('rnocust', '');
// $strnama = array ("0"=>"tidak aktif","1"=>"aktif");
$id = checkPostGet('id','');
$namafile = checkPostGet('namafile','');


$strx = "";
$data = array();
$data['error'] = 'false';


switch ($method) {

    case 'submitfile':
    $tgl = date("YmdHis");
    // exit("error : ".$tgl);
    $data = $_POST;
    
    if($data['fileupload']!='')
    {
      if($_FILES['file']['error']==0)
      {
        $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
        $newfilename = str_replace($filetype,'',$_FILES['file']['name']);
        $filename = $newfilename."_".$tgl."".$filetype;
        $file_tmpname = $_FILES['file']['tmp_name'];    
        
        if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.doc')||($filetype=='.docx'))
        {
          if($_FILES['file']['size'] <= 512000)
          {
            $str = "insert into ".$dbname.".listfilecustomer values ('','".$data['kodecustomer']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
            // echo $str;
            // exit('error'.$str);
            try
            {
              $owlPDO->exec($str);
              move_uploaded_file($file_tmpname,"fileupload/customer/$filename");
            }
            catch(PDOException $e)
            {
              echo " Gagal," . addslashes($e->getMessage());
            }
          }
          else
          {
            exit("warning : Ukuran file upload maksimal 512kb");
          }
        }else{
          exit("Warning : Format file upload harus doc, docx, .png, .jpg atau .jpeg");
        }
      }
    }
  break;

  case 'loadfiles':

    $no = 0;
    $tab = "";
    $str="select * from ".$dbname.".pmn_4customer where kodecustomer = '".$kodecustomer."'";
    $resv=fetchData($str);
    foreach($resv as $bar => $barv){
      $close = $barv['close'];  
    }
    

$tab.="<table class='sortable' cellspacing='1' border='0' width=100%>
        <thead>
        <tr class=rowheader>
        <td align='center'>No.</td>
        <td align='center'>Kode Pelanggan</td>
        <td align='center'>Nama Pelanggan</td>
        <td align='center'>Filename</td>
        <td align='center'>Action</td>
      </tr>
        </thead>";

    // $str="select * from ".$dbname.".listfilecustomer where kodecustomer = '".$kodecustomer."' and status='1'";
    $str="select namafile, kodecustomer from ".$dbname.".pmn_4customer where kodecustomer = '".$kodecustomer."'";
    $res=fetchData($str);
    if(empty($res))
    {
      $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
    }
    else
    {
      foreach($res as $key=>$val)
      {
        $no++;
        if ($val['namafile']!='')
        {
          $optNmBarang = makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$val['kodecustomer']."'");
          $tab.="<tr id='ppDetailTable' class=rowcontent>
            <td style='text-align:center'>".$no."</td>
            <td style='text-align:center'>".$val['kodecustomer']."</td>
            <td style='text-align:left'>".$optNmBarang[$val['kodecustomer']]."</td>
            <td style='text-align:left'>".$val['namafile']."</td>
            <td align=center>
              <a href='fileupload/customer/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
            </td>
          </tr>";
        }
      }
    }
    $tab.="</table>";

    echo $tab;
  break;

    case 'deletefile':
      $str="delete from ".$dbname.".listfilecustomer where id='".$id."'";
      // exit('error :'.$str);
      try
      {
        $owlPDO->exec($str);
        $path = "fileupload/customer/".$namafile;
        unlink($path);
      }
      catch(PDOException $e)
      {
        echo " Gagal," . addslashes($e->getMessage());
      }
    break;

    default:
}
?>
