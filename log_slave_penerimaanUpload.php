<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');

$notransaksi = checkPostGet('notransaksi','');
$method = checkPostGet('method','');
$id = checkPostGet('id','');
$namafile = checkPostGet('namafile','');
$kodebarang = checkPostGet('kodebarang','');
$jenisupload = checkPostGet('jenisupload','');
$fileupload = checkPostGet('fileupload','');
$namafile = checkPostGet('namafile','');
$fromapp = checkPostGet('fromapp','');


$strx = "";
$data = array();
$data['error'] = 'false';

//exit("Error : ".$namafile);

$path   = "fileupload/log_penerimaanx/";
$emodul = "GRN";

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
        
        if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
        {
          if($_FILES['file']['size'] <= 250000)
          {
            // $str = "insert into ".$dbname.".log_transaksiht values ('','','','','','','','','','','','','','','','','','','','','','','','','','','".$filename."','".$_SESSION['standard']['userid']."')
            // where notransaksi = '".$notransaksi."'";

            $str = "insert into ".$dbname.".listfilepenerimaan values ('','".$data['notransaksi']."','".$filename."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
            
            try
            {
              $owlPDO->exec($str);
              move_uploaded_file($file_tmpname,"fileupload/penerimaanbarang/$filename");
            }
            catch(PDOException $e)
            {
              echo " Gagal," . addslashes($e->getMessage());
            }
            // echo $str;
            // exit('error'.$str);
          }
          else
          {
            exit("warning : Ukuran file upload maksimal 250kb");
          }
        }else{
          exit("Warning : Format file upload harus .jpg atau .jpeg");
        }
      }
    }
  break;

   case'submitfilex':
        /*echo"<pre>";
        print_r($noarr);
        echo"</pre>";
        //exit('error');*/
        $tgl = date("YmdHis");
        $his = date("His");
        $nmTemp=str_replace('-','',str_replace('/','',$notransaksi));
                        if($fileupload!=''){
                            if($_FILES['file']['error']==0){    
                                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));

                                    
                                    $query = selectQuery($dbname,"listfile_log_penerimaan","namafile","notransaksi='".$notransaksi."' and namafile like '".$jenisupload."_".$nmTemp."_".$kodebarang."%'");
                                    $id = fetchData($query);
                                    $maxid=1;
                                    if(!empty($id)) {
                                        $no=0;
                                        foreach($id as $row) {
                                            $noarr=explode('_', $row['namafile']);
                                            intval($noarr[3])>=$maxid ? $maxid=intval($noarr[3]) : false;
                                        }
                                        $maxid++;
                                    }

                                    $filename = $jenisupload."_".$nmTemp."_".$kodebarang."_".$maxid."".$filetype;
                                    //exit('Error' : $str);

                                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                                
                                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                                    $str = "insert into ".$dbname.".listfile_log_penerimaan values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$jenisupload."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                                    //exit('Error : '. $str);
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
                                    exit("Warning : Format file upload tidak boleh ".$filetype);
                                }
                            }
                        }
    break;

  case 'loadfiles':
		$arrmodul = getmodulefil($emodul);
        $nmTemp=str_replace('-','',str_replace('/','',$notransaksi));
        $dp = '';
    #data
        $str="select * from ".$dbname.".listfile_log_penerimaan where notransaksi='".$notransaksi."' and namafile like '%".$nmTemp."_".$kodebarang."%'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
        if ($fromapp != 'approval') { $dp = "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefilex('".$bar['notransaksi']."','".$bar['namafile']."');\" >"; }
        
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td>".$bar['notransaksi']."</td>";
        $tab.= "<td>".$arrmodul[$bar['detail']]['kriteria']."</td>";
        @$icon = seticonfile($bar['formaticon']);
        $tab.= "<td><a href='".$path.$bar['namafile']."' download><img src=".$icon." class=resicon></a> ".$bar['namafile']."</td>";
        $tab.= "<td><a href='".$path.$bar['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp".$dp."</td>";
        $form.= "<tr>";

        }

        echo $tab;
  break;

 

  case 'loadfiles1':

    $no = 0;
    $tab = "";
    $str="select * from ".$dbname.".log_transaksiht where notransaksi = '".$notransaksi."'";
    $resv=fetchData($str);
    foreach($resv as $bar => $barv){
      $close = $barv['close'];  
    }
    

$tab.="<table class='sortable' cellspacing='1' border='0' width=100%>
        <thead>
        <tr class=rowheader>
          <td align='center'>No.</td>

          <td align='center'>Filename</td>
          <td align='center'>Action</td>
        </tr>
        </thead>";

    $str="select * from ".$dbname.".listfilepenerimaan where notransaksi = '".$notransaksi."' and status='1'";
    $res=fetchData($str);
    if(empty($res))
    {
      $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
      // else if($namafile = ''){

      // }
    }
    else
    {
      foreach($res as $key=>$val)
      {
        $no++;
        if ($val['namafile']!=''){
                  $tab.="<tr id='ppDetailTable' class=rowcontent>
          <td style='text-align:center'>".$no."</td>";
        
        $tab.="<td style='text-align:left'>".$val['namafile']."</td>
          <td align=center>
            <a href='fileupload/penerimaanbarang/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
        if($close==0){
          $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['id']."');\" >";
        }
        $tab."  </td>
        </tr>";

        }

      } 
    }
    $tab.="</table>";
    echo $tab;
  break;

    case 'showupload':
        $tab="";
        
        $arrmodul = getmodulefil($emodul);
        foreach($arrmodul as $key=>$val){
            $optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
        }
        $optnamabarang= makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
        if ($fromapp != 'approval') {
            $tab.="<table cellspacing='1' border='0' id='uploadpopup'>
                <tr>
                    <td>Kriteria</td>
                    <td>:</td>
                    <td>
                        <select id='kriteriaefil'>". $optkriteria."</select>
                    </td>
                </tr>
                <tr>
                    <td>Barang</td>
                    <td>:</td>
                    <td id='kodebarangupload' hidden>".$kodebarang."</td>
                    <td>".$optnamabarang[$kodebarang]."</td>
                </tr>
                <tr>
                    <td>Filename</td>
                    <td>:</td>
                    <td>
                        <input type='file' name='upload' id='upload' class=mybutton>
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=\"save_filex()\">Submit</button>
                    </td>
                </tr>
            </table>";
        }
        $tab.="<p />";
        
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>No.</td>
                    <td align='center'>Kriteria</td>
                    <td align='center'>Filename</td>
                    <td align='center'>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";
        
        echo $tab;
    break;

    case 'deletefilex':
        //$namafile=$param['namafile'];
        $str="delete from ".$dbname.".listfile_log_penerimaan where notransaksi='".$notransaksi."' and namafile='".$namafile."'"; 
        //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;

    case 'deletefile':
       $str="delete from ".$dbname.".listfilepenerimaan where id='".$id."'";
      // exit('error :'.$str);
      try
      {
        $owlPDO->exec($str);
        $path = "fileupload/penerimaanbarang/".$namafile;
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
