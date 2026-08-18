<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');

$kelompok = checkPostGet('kelompok','');
$idsupplier = checkPostGet('idsupplier','');
$namasupplier = checkPostGet('namasupplier','');
$statusup = checkPostGet('statusup','');
$badan = checkPostGet('badan','');
$pemilik = checkPostGet('pemilik','');
$direktur = checkPostGet('direktur','');
$pj = checkPostGet('pj','');
$jabatan = checkPostGet('jabatan','');
$jenisusaha = checkPostGet('jenisusaha','');
$method = checkPostGet('method','');
$pages = checkPostGet('page', '');
$txt_search = checkPostGet('txtsearch', '');
$txtsearchcalon = checkPostGet('txtsearchcalon', '');
$caristatusup = checkPostGet('caristatusup', '');
$caribadan = checkPostGet('caribadan', '');
$txtNoakun = checkPostGet('txtNoakun', '');
$txtNoakuncalon = checkPostGet('txtNoakuncalon', '');
$useremail = checkPostGet('useremail', '');
$password = checkPostGet('password', '');
$jenisview = checkPostGet('jenisview', '');
$strnama = array('0'=>'TIDAK AKTIF','1'=>'AKTIF','2'=>'BELUM SETUJU','3'=>'REGISTER','4'=>'BLACKLIST');
$strnamaper = array ("0"=>"Proses persetujuan","1"=>"Disetujui","2"=>"Ditolak");
$strnamaperx = array ("0"=>"Proses persetujuan","1"=>"Disetujui","3"=>"Ditolak");
$jnsapp = "DS";
function site_url(){
  return sprintf(
    "%s://%s%s",isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',$_SERVER['SERVER_NAME'],''
  );
}
function segment($num){
	$result = "";
	$list = explode("/",$_SERVER['REQUEST_URI']);
	if(isset($list[$num])){
		$result = $list[$num];
	}
  return $result; 
}
function rand_passwd( $length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ) {
    return substr( str_shuffle( $chars ), 0, $length );
}
function  kirimEmailkeSupplier($to,$cc = "",$subject,$body,$mailType='text/html')//multiple recipient separated by comma
{
    global $owlPDO;
    global $dbname;
    #default
    $port=25;
    $ssl='YES';
    $str=$owlPDO->query("select * from ".$dbname.".setup_remotetimbangan where lokasi='MAILSYS'");
    $str->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$str->fetch()){
        $host=trim($bar->ip);
        $username=trim($bar->username);
        $password=trim($bar->password);
        $port=trim($bar->port);
        $ssl=strtoupper(trim($bar->dbname));
    }

    if($ssl=='YES' or $ssl=='TRUE' or strtoupper($ssl)=='SSL')
    {
        $host="ssl://".$host;
    }
    #mailType posible value 'text/html' or 'text/text'
    
     require_once "Mail.php";   
     $from = "Owl-Plantation<noreply@owl-plantation.com>";
     $headers = array ('From' => $from,
       'To' => $to,
       'Cc' => $cc,  
       'Subject' => $subject,
       'Content-Type'=> $mailType);
     $mail = Mail::factory('smtp',
       array ('host' => $host,
         'auth' => true,
         'port' => $port,
         'username' => $username,
         'password' => $password));     
/*
     echo "<pre>";
     print_r($headers);
     echo "<br>";
     print_r($mail);
     echo "<br>";
     echo "</pre>";
*/
     if($mailType=='text/html')
     {
         $body.="";
     }    
	 $toto=explode(",",$to);
	 foreach($toto as $key =>$val){
           $kirim = $mail->send($val, $headers, $body);
       }
     if (PEAR::isError($kirim)) {
       return $kirim->getMessage();
     	//return true;
      } else {
       return true;
      }
     return true;
} 
$strx = "";
$data = array();
$data['error'] = 'false';
function getformat_companyname($namasupplier,$badan){
  $result = "";
  if($namasupplier != ""){
    $namasupplier  = strtoupper($namasupplier);
    $badan    = strtoupper($badan);
    $bdntxt = "";
    if($badan != ""){
      $bdntxt = ", ".$badan;
    }
    $result = $namasupplier.$bdntxt;
  }
  return $result;
}

switch ($method) {
    case 'detailsupp':
    if($jenisview!='')
    {
      $countApprove = getCountApproval($jnsapp,$_SESSION['empl']['lokasitugas']);
      echo "
        <table border=0 cellspacing=1 class=sortable width=100%>
        <thead>
        <tr style='font-weight:bold'>";
          for($i=1;$i<=$countApprove;$i++){
            echo "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
          }       
      echo "
        </tr>
        </thead>
        <tbody>";
        echo "<tr class=rowcontent>";
          for($i=1;$i<=$countApprove;$i++){
            
            
            $arrApp = detailApprove($i,$idsupplier,'DS');
            
            if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
              $tngl='';
            }else{
              $tngl=tanggalnormal($arrApp['tanggal']);
            }
            
            if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
              echo "<td>".$arrApp['nama']."
                <br />".$strnamaperx[$arrApp['status']]."
                <br />".$tngl."
                <br />".$arrApp['komentar']."
              </td>";
            }else{
              echo "<td>&nbsp;</td>";
            }
          }
          
        
        echo "</tbody>
        </table><hr>";
    }

    $nmkel = makeOption($dbname, 'log_5klsupplier', 'tipe,kode');

    $selectx = "select a.*,b.email,b.full_name from " . $dbname . ".log_5supplier a
    left join " . $dbname . ".log_5supuser b on a.supplierid = b.id_supplier where a.supplierid='".$idsupplier."'";
    $restx=$owlPDO->query($selectx) or die(print " Gagal: ".PDOException::getMessage());
    $restx->setFetchMode(PDO::FETCH_ASSOC);
    $barx = $restx->fetch();
    
    $selecty = "select * from " . $dbname . ".log_5supkelompok where supplierid='".$idsupplier."'";
    $resty=$owlPDO->query($selecty) or die(print " Gagal: ".PDOException::getMessage());
    $resty->setFetchMode(PDO::FETCH_ASSOC);
    $arrkel=array();
    $noy=0;
    while($bary = $resty->fetch())
    {
      $arrkel[$noy]=$bary['tipe'];
      $noy++;
    }
    if($barx['perubahan']=='')
    {
      $frm[0]='<table class=sortable cellspacing=0 border=1 width=100% ><thead>';
      $frm[0].="<tr class='rowheader'>";
      $frm[0].="<td colspan=2 align=center>Data Header</td>";
      $frm[0].="</tr>";
      $frm[0].="<tr class='rowheader'>";
      $frm[0].="<td colspan=2 align=center>Data Baru</td>";
      $frm[0].="</tr>";
      $frm[0].="<tr class='rowheader'>";
      $frm[0].="<td align=center>Data</td>";
      $frm[0].="<td align=center>Nilai</td>";
      $frm[0].="</tr></thead>";


      $frm[0].="<tbody><tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Supplier</td>";
      $frm[0].="<td align=left>".$barx['namasupplier']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Badan Usaha</td>";
      $frm[0].="<td align=left>".$barx['badanusaha']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Pemilik</td>";
      $frm[0].="<td align=left>".$barx['namapemilik']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Direktur</td>";
      $frm[0].="<td align=left>".$barx['namadirektur']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Penanggung Jawab</td>";
      $frm[0].="<td align=left>".$barx['namapenanggungjawab']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jabatan</td>";
      $frm[0].="<td align=left>".$barx['jabatan']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>User Email</td>";
      $frm[0].="<td align=left>".$barx['email']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Status</td>";
      $frm[0].="<td align=left>".$strnama[$barx['status']]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 1</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[0]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 2</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[1]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 3</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[2]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 4</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[3]]."</td>";
      $frm[0].="</tr><tbody>";
    }
    else
    {
      $arrdatax=explode('##', $barx['perubahan']);
      $arrdatax2=explode(',', $arrdatax[9]);
      $frm[0]='<table class=sortable cellspacing=1 border=0 width=100% ><thead>';
      $frm[0].="<tr class='rowheader'>";
      $frm[0].="<td colspan=4 align=center>Data Header</td>";
      $frm[0].="</tr>";
      $frm[0].="<tr class='rowheader'>";
      if($arrdatax[0]=='')
      {
      $frm[0].="<td colspan=2 align=center>Data Tidak Ada Perubahan</td>";$frm[0].="</tr>";
      $frm[0].="<tr class='rowheader'>";
      $frm[0].="<td align=center>Data</td>";
      $frm[0].="<td align=center>Nilai</td>";
      $frm[0].="</tr></thead>";


      $frm[0].="<tbody><tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Supplier</td>";
      $frm[0].="<td align=left>".$barx['namasupplier']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Badan Usaha</td>";
      $frm[0].="<td align=left>".$barx['badanusaha']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Pemilik</td>";
      $frm[0].="<td align=left>".$barx['namapemilik']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Direktur</td>";
      $frm[0].="<td align=left>".$barx['namadirektur']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Penanggung Jawab</td>";
      $frm[0].="<td align=left>".$barx['namapenanggungjawab']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jabatan</td>";
      $frm[0].="<td align=left>".$barx['jabatan']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>User Email</td>";
      $frm[0].="<td align=left>".$barx['email']."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Status</td>";
      $frm[0].="<td align=left>".$strnama[$barx['status']]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 1</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[0]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 2</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[1]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 3</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[2]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 4</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[3]]."</td>";
      $frm[0].="</tr><tbody>";

      }
      else
      {

      $frm[0].="<td colspan=2 align=center>Sesudah</td>";
      $frm[0].="<td colspan=2 align=center>Sebelum</td>";$frm[0].="</tr>";
      $frm[0].="<tr class='rowheader'>";
      $frm[0].="<td align=center>Data</td>";
      $frm[0].="<td align=center>Nilai</td>";
      $frm[0].="<td align=center>Data</td>";
      $frm[0].="<td align=center>Nilai</td>";
      $frm[0].="</tr></thead>";


      $frm[0].="<tbody><tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Supplier</td>";
      $frm[0].="<td align=left>".$barx['namasupplier']."</td>";
      $frm[0].="<td align=left>Nama Supplier</td>";
      $frm[0].="<td align=left>".$arrdatax[1]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Badan Usaha</td>";
      $frm[0].="<td align=left>".$barx['badanusaha']."</td>";
      $frm[0].="<td align=left>Badan Usaha</td>";
      $frm[0].="<td align=left>".$arrdatax[2]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Pemilik</td>";
      $frm[0].="<td align=left>".$barx['namapemilik']."</td>";
      $frm[0].="<td align=left>Nama Pemilik</td>";
      $frm[0].="<td align=left>".$arrdatax[3]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Direktur</td>";
      $frm[0].="<td align=left>".$barx['namadirektur']."</td>";
      $frm[0].="<td align=left>Nama Direktur</td>";
      $frm[0].="<td align=left>".$arrdatax[4]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Nama Penanggung Jawab</td>";
      $frm[0].="<td align=left>".$barx['namapenanggungjawab']."</td>";
      $frm[0].="<td align=left>Nama Penanggung Jawab</td>";
      $frm[0].="<td align=left>".$arrdatax[5]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jabatan</td>";
      $frm[0].="<td align=left>".$barx['jabatan']."</td>";
      $frm[0].="<td align=left>Jabatan</td>";
      $frm[0].="<td align=left>".$arrdatax[6]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>User Email</td>";
      $frm[0].="<td align=left>".$barx['email']."</td>";
      $frm[0].="<td align=left>User Email</td>";
      $frm[0].="<td align=left>".$arrdatax[8]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Status</td>";
      $frm[0].="<td align=left>".$strnama[$barx['statusyangdiinginkan']]."</td>";
      $frm[0].="<td align=left>Status</td>";
      $frm[0].="<td align=left>".$strnama[$arrdatax[7]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 1</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[0]]."</td>";
      $frm[0].="<td align=left>Jenis Usaha 1</td>";
      $frm[0].="<td align=left>".$nmkel[$arrdatax2[0]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 2</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[1]]."</td>";
      $frm[0].="<td align=left>Jenis Usaha 2</td>";
      $frm[0].="<td align=left>".$nmkel[$arrdatax2[1]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 3</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[2]]."</td>";
      $frm[0].="<td align=left>Jenis Usaha 3</td>";
      $frm[0].="<td align=left>".$nmkel[$arrdatax2[2]]."</td>";
      $frm[0].="</tr>";

      $frm[0].="<tr class='rowcontent'>";
      $frm[0].="<td align=left>Jenis Usaha 4</td>";
      $frm[0].="<td align=left>".$nmkel[$arrkel[3]]."</td>";
      $frm[0].="<td align=left>Jenis Usaha 4</td>";
      $frm[0].="<td align=left>".$nmkel[$arrdatax2[3]]."</td>";
      $frm[0].="</tr><tbody>";

      }
      


    }

    $namasupplier=$barx['namasupplier'];
    $badanusaha=$barx['badanusaha'];
     $frm[0].="</table>";
     $lampiran = "";
$strlampiran =$owlPDO->query("select a.kode_jenis,a.badanusaha,a.nama_jenis,IFNULL(b.namafile,'') as namafile
            from ".$dbname.".log_5jenislampiran a
            left join ".$dbname.".log_fileupload b on b.idlampiran = a.kode_jenis and b.supplierid = '".$idsupplier."'
            ");
$strlampiran->setFetchMode(PDO::FETCH_OBJ);
$jmlLam = $strlampiran->rowCount();
$frm[0].="

      
        <table class='sortable' border='0' cellspacing='1' cellpadding='0' style='width:100%;'>
          <thead>
            <tr class='rowheader'>
              <th width='1' align='center'>No</th>
              <th align='left'>Jenis Lampiran</th>
              <th width='200' align='center'>File</th>
              <th width='1' align='center'></th>
            </tr>
          </thead>
          <tbody>";
          if($jmlLam>0){
            $num = 1;
            $path = 'fileupload/supplier/'.$idsupplier;
            while($r=$strlampiran->fetch()){
              $spiter = array();
              $spiter = explode(',',$r->badanusaha);
              if(in_array($badanusaha,$spiter)){
              $frm[0].="
              <tr class='rowcontent' >
                <td width='1' align='center'>".$num."</td>
                <td>".$r->nama_jenis."</td>
                <td align='center'>";
                  if($r->namafile!=''){
                    $frm[0].="<a href='".$path."/".$r->namafile."' download>".$r->namafile."</a>"; 
                  }/*else{
                    $frm[0].="
                    <input type='file' name='file_".$r->kode_jenis."' id='file_".$r->kode_jenis."'class='myinputtext' style='max-width: 50%;'>
                    <input type='button' onclick='upload_fileaftersign('file_".$r->kode_jenis."','".$idsupplier."','".$namasupplier."','".$badanusaha."','".$r->kode_jenis."','');' class='mybutton' value='Upload' style='max-width: 50%;'>";
                  }*/$frm[0].="
                </td>
                <td align='center'>";
                  /*if($r->namafile!=''){ $frm[0].="
                  <a onclick='delete_fileaftersign('".$r->kode_jenis."','".$idsupplier."','".$namasupplier."','".$badanusaha."');'><img src='images/delete_32.png' class='zImgBtn'></a>
                  "; }*/
                  $frm[0].="
                </td>
              </tr>";
              $num++;
              }
            } 
          } $frm[0].="
          </tbody>";
         
          $strlegitimate =$owlPDO->query("select supplierid,idlampiran,'legitimate' as badanusaha,'File After Sign' as nama_jenis,namafile
              from ".$dbname.".log_fileupload where lokasifile = 'legitimate' 
              and supplierid = '".$idsupplier."' ");
          $strlegitimate->setFetchMode(PDO::FETCH_OBJ);
          $jmlLg = $strlegitimate->rowCount();
          if($jmlLg>0){
            $path = 'fileupload/supplier/'.$idsupplier;
            $frm[0].="<tfoot>";
            while($r=$strlegitimate->fetch()){
              $tgl = date('YmdHis');
              $frm[0].="
              <tr class='rowcontent' >
                <td width='1' align='center'><img src='images/newfile.png' class='zImgBtn'></td>
                <td>".$r->nama_jenis."</td>
                <td align='center'><a href='".$path."/".$r->namafile."' download>".$r->namafile."</a>
                </td>
                
              </tr>";
            $num++;
            } 
          $frm[0].= '</tfoot>';
          }
       $frm[0].= '</table>';


    $strnama1a = array ("0"=>"TIDAK","1"=>"YA");
    $strnama1b = array ("0"=>"Tidak Aktif","1"=>"Aktif");
    $nmbank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
    $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

    $frm[1]="<table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['namabank'] . "</td>
         <td align=center>" . $_SESSION['lang']['norek'] . "</td>
         <td align=center>" . $_SESSION['lang']['atasnama'] . "</td>
         <td align=center>" . $_SESSION['lang']['cabang'] . "</td>
         <td align=center>" . $_SESSION['lang']['kota'] . "</td>
         <td align=center>" . $_SESSION['lang']['negara'] . "</td>
         <td align=center>" . $_SESSION['lang']['matauang'] . "</td>
         <td align=center>" . $_SESSION['lang']['default'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Aktif</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Data</td>
       </tr>
    </thead>
    <tbody>";

    $input = "select * from " . $dbname . ".log_5rekbank where supplierid = '".$idsupplier."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        while ($d = $n->fetch()) {
            $no+=1;
            $statdata='';
            if($d['perubahan']=='')
            {
              $statdata='Data Baru';
              $frm[1].=  "<tr class=rowcontent>";
              $frm[1].=  "<td align=center>" . $no . "</td>";
              $frm[1].=  "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
              $frm[1].=  "<td align=left>" . $nmbank[$d['idbank']] . "</td>";
              $frm[1].=  "<td align=left>" . $d['rekening'] . "</td>";
              $frm[1].=  "<td align=left>" . $d['an'] . "</td>";
              $frm[1].=  "<td align=left>" . $d['cabang'] . "</td>";
              $frm[1].=  "<td align=left>" . $d['kota'] . "</td>";
              $frm[1].=  "<td align=left>" . $d['negara'] . "</td>";
              $frm[1].=  "<td align=left>" . $d['matauang'] . "</td>";
              $frm[1].=  "<td align=left>" . $strnama1a[$d['def']]."</td>";
              $frm[1].=  "<td align=left>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
              $frm[1].=  "<td align=left>" . $statdata."</td>";
              $frm[1].=  "</tr>"; 
            }
            else
            {
               $arrdatax=explode('##', $d['perubahan']);
               if($arrdatax[0]!='')
               {
                $statdata='Edit Data';
                $frm[1].=  "<tr class=rowcontent>";
                $frm[1].=  "<td align=center>" . $no . "</td>";
                $frm[1].=  "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[1].=  "<td align=left>" . $nmbank[$arrdatax[1]] . "</td>";
                $frm[1].=  "<td align=left>" . $arrdatax[3] . "</td>";
                $frm[1].=  "<td align=left>" . $arrdatax[4] . "</td>";
                $frm[1].=  "<td align=left>" . $arrdatax[5] . "</td>";
                $frm[1].=  "<td align=left>" . $arrdatax[6] . "</td>";
                $frm[1].=  "<td align=left>" . $arrdatax[7] . "</td>";
                $frm[1].=  "<td align=left>" . $arrdatax[8] . "</td>";
                $frm[1].=  "<td align=left>" . $strnama1a[$arrdatax[9]]."</td>";
                $frm[1].=  "<td align=left>" . $strnama1b[$arrdatax[10]]."</td>";
                $frm[1].=  "<td align=left>Sebelum(edit)</td>";
                $frm[1].=  "</tr>"; 
                $frm[1].=  "<tr class=rowcontent>";
                $frm[1].=  "<td align=center bgcolor='orange'>&#8627</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $nmbank[$d['idbank']] . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $d['rekening'] . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $d['an'] . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $d['cabang'] . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $d['kota'] . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $d['negara'] . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $d['matauang'] . "</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $strnama1a[$d['def']]."</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
                $frm[1].=  "<td align=left bgcolor='orange'>Sesudah(edit)</td>";
                $frm[1].=  "</tr>"; 
               }
               else
              {
                $statdata='Tidak Ada Perubahan';
                $frm[1].=  "<tr class=rowcontent>";
                $frm[1].=  "<td align=center>" . $no . "</td>";
                $frm[1].=  "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[1].=  "<td align=left>" . $nmbank[$d['idbank']] . "</td>";
                $frm[1].=  "<td align=left>" . $d['rekening'] . "</td>";
                $frm[1].=  "<td align=left>" . $d['an'] . "</td>";
                $frm[1].=  "<td align=left>" . $d['cabang'] . "</td>";
                $frm[1].=  "<td align=left>" . $d['kota'] . "</td>";
                $frm[1].=  "<td align=left>" . $d['negara'] . "</td>";
                $frm[1].=  "<td align=left>" . $d['matauang'] . "</td>";
                $frm[1].=  "<td align=left>" . $strnama1a[$d['def']]."</td>";
                $frm[1].=  "<td align=left>" . $strnama1b[$d['isactive']]."</td>";
                $frm[1].=  "<td align=left>" . $statdata."</td>";
                $frm[1].=  "</tr>"; 
              }
            }
            

        }
        
    $frm[1].= "</tbody></table>";
          

  $no=0;
        $frm[2]="
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['npwp'] . "</td>
         <td align=center>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['npwp'] . "</td>
         <td align=center>" . $_SESSION['lang']['jalan'] . "</td>
         <td align=center>" . $_SESSION['lang']['blok'] . "</td>
         <td align=center>" . $_SESSION['lang']['nomor'] . "</td>
         <td align=center>" . $_SESSION['lang']['rt'] . "</td>
         <td align=center>" . $_SESSION['lang']['rw'] . "</td>
         <td align=center>" . $_SESSION['lang']['kelurahan'] . "</td>
         <td align=center>" . $_SESSION['lang']['kecamatan'] . "</td>
         <td align=center>" . $_SESSION['lang']['kabupaten'] . "</td>
         <td align=center>" . $_SESSION['lang']['provinsi'] . "</td>
         <td align=center>" . $_SESSION['lang']['kodepos'] . "</td>
         <td align=center>" . $_SESSION['lang']['telp'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Aktif</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Data</td>
       </tr>
    </thead>
    <tbody>";

       $input = "select * from " . $dbname . ".log_5supnpwp  where supplierid = '".$idsupplier."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        while ($d = $n->fetch()) {
            $no+=1;
            $statdata='';
            if($d['perubahan']=='')
            {
              $statdata='Data Baru';
              $frm[2].= "<tr class=rowcontent>";
              $frm[2].= "<td align=center>" . $no . "</td>";
              $frm[2].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
              $frm[2].= "<td align=left>" . $d['npwp'] . "</td>";
              $frm[2].= "<td align=left>" . $d['nama_npwp'] . "</td>";
              $frm[2].= "<td align=left>" . $d['jalan'] . "</td>";
              $frm[2].= "<td align=left>" . $d['blok'] . "</td>";
              $frm[2].= "<td align=left>" . $d['nomor'] . "</td>";
              $frm[2].= "<td align=left>" . $d['rt'] . "</td>";
              $frm[2].= "<td align=left>" . $d['rw'] . "</td>";
              $frm[2].= "<td align=left>" . $d['keluarahan'] . "</td>";
              $frm[2].= "<td align=left>" . $d['kecamatan'] . "</td>";
              $frm[2].= "<td align=left>" . $d['kabupaten'] . "</td>";
              $frm[2].= "<td align=left>" . $d['propinsi'] . "</td>";
              $frm[2].= "<td align=left>" . $d['kodepos'] . "</td>";
              $frm[2].= "<td align=left>" . $d['telp_no'] . "</td>";        
              $frm[2].= "<td align=left>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
              $frm[2].= "<td align=left>" . $statdata."</td>";
              $frm[2].= "</tr>"; 
            }
            else
            {
              $arrdatax=explode('##', $d['perubahan']);
              if($arrdatax[0]!='')
              {
                $statdata='Edit Data';
                $frm[2].= "<tr class=rowcontent>";
                $frm[2].= "<td align=center>" . $no . "</td>";
                $frm[2].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[1] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[2] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[3] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[4] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[5] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[6] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[7] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[8] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[9] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[10] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[11] . "</td>";
                $frm[2].= "<td align=left>" . $arrdatax[12] . "</td>";   
                $frm[2].= "<td align=left>" . $arrdatax[13] . "</td>";     
                $frm[2].= "<td align=left>" . $strnama1b[$d['active']]."</td>";
                $frm[2].= "<td align=left>Sebelum(edit)</td>";
                $frm[2].= "</tr>"; 
                $frm[2].= "<tr class=rowcontent>";
                $frm[2].= "<td align=center bgcolor='orange'>&#8627</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['npwp'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['nama_npwp'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['jalan'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['blok'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['nomor'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['rt'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['rw'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['keluarahan'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['kecamatan'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['kabupaten'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['propinsi'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['kodepos'] . "</td>";
                $frm[2].= "<td align=left bgcolor='orange'>" . $d['telp_no'] . "</td>";        
                $frm[2].= "<td align=left bgcolor='orange'>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
                $frm[2].= "<td align=left bgcolor='orange'>Sesudah(edit)</td>";
              }
              else
              {
                $statdata='Tidak Ada Perubahan';
                $frm[2].= "<tr class=rowcontent>";
                $frm[2].= "<td align=center>" . $no . "</td>";
                $frm[2].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[2].= "<td align=left>" . $d['npwp'] . "</td>";
                $frm[2].= "<td align=left>" . $d['nama_npwp'] . "</td>";
                $frm[2].= "<td align=left>" . $d['jalan'] . "</td>";
                $frm[2].= "<td align=left>" . $d['blok'] . "</td>";
                $frm[2].= "<td align=left>" . $d['nomor'] . "</td>";
                $frm[2].= "<td align=left>" . $d['rt'] . "</td>";
                $frm[2].= "<td align=left>" . $d['rw'] . "</td>";
                $frm[2].= "<td align=left>" . $d['keluarahan'] . "</td>";
                $frm[2].= "<td align=left>" . $d['kecamatan'] . "</td>";
                $frm[2].= "<td align=left>" . $d['kabupaten'] . "</td>";
                $frm[2].= "<td align=left>" . $d['propinsi'] . "</td>";
                $frm[2].= "<td align=left>" . $d['kodepos'] . "</td>";
                $frm[2].= "<td align=left>" . $d['telp_no'] . "</td>";        
                $frm[2].= "<td align=left>" . $strnama1b[$d['active']]."</td>";
                $frm[2].= "<td align=left>" . $statdata."</td>";
                $frm[2].= "</tr>"; 
              }
            }
        }

        
    $frm[2].="</tbody></table>";
        
        $frm[3]="
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['alamat'] . "</td>
         <td align=center>" . $_SESSION['lang']['cperson'] . "</td>
         <td align=center>" . $_SESSION['lang']['kota'] . "</td>
         <td align=center>" . $_SESSION['lang']['telp'] . "</td>
         <td align=center>" . $_SESSION['lang']['extensi'] . "</td>
         <td align=center>" . $_SESSION['lang']['nohp'] . "</td>
         <td align=center>" . $_SESSION['lang']['jabatan'] . "</td>
         <td align=center>" . $_SESSION['lang']['fax'] . "</td>
         <td align=center>" . $_SESSION['lang']['email'] . " " . $_SESSION['lang']['koresponden'] . "</td>
        <td align=center>" . $_SESSION['lang']['email'] . " " . $_SESSION['lang']['konfirm'] . "</td>
         <td align=center>" . $_SESSION['lang']['provinsi'] . "</td>
         <td align=center>" . $_SESSION['lang']['negara'] . "</td>
         <td align=center>" . $_SESSION['lang']['kodepos'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Aktif</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Data</td>
       </tr>
    </thead>
    <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5supalamat where supplierid = '".$idsupplier."'" ; 

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $tab='';
  $no=0;
    
        $input = "select * from " . $dbname . ".log_5supalamat  where supplierid = '".$idsupplier."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        while ($d = $n->fetch()) {
            $no+=1;
            //$no+=1;
            if($d['perubahan']=='')
            {
            $statdata='Data Baru';
            $frm[3].= "<tr class=rowcontent>";
            $frm[3].="<td align=left>" . $no . "</td>";
            $frm[3].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            $frm[3].="<td align=left>" . $d['alamat'] . "</td>";
            $frm[3].="<td align=left>" . $d['kontakperson'] . "</td>";
            $frm[3].="<td align=left>" . $d['kota'] . "</td>";
            $frm[3].="<td align=left>" . $d['telepon'] . "</td>";
            $frm[3].="<td align=left>" . $d['extm'] . "</td>";
            $frm[3].="<td align=left>" . $d['teleponlain'] . "</td>";
            $frm[3].="<td align=left>" . $d['jabatan'] . "</td>";
            $frm[3].="<td align=left>" . $d['fax'] . "</td>";
            $frm[3].="<td align=left>" . $d['email_koresponden'] . "</td>";
            $frm[3].="<td align=left>" . $d['email_konfirmasi'] . "</td>";
            $frm[3].="<td align=left>" . $d['provinsi'] . "</td>";
            $frm[3].="<td align=left>" . $d['negara'] . "</td>";
            $frm[3].="<td align=left>" . $d['kodepos'] . "</td>";
            $frm[3].= "<td align=left>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
            $frm[3].="<td align=left>" . $statdata . "</td>";
            $frm[3].= "</tr>"; 
             }
            else
            {
              $arrdatax=explode('##', $d['perubahan']);
              if($arrdatax[0]!='')
              {

                $statdata='Edit Data';
                $frm[3].= "<tr class=rowcontent>";
                $frm[3].="<td align=left>" . $no . "</td>";
                $frm[3].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[1] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[2] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[3] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[4] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[5] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[6] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[7] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[8] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[9] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[10] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[11] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[12] . "</td>";
                $frm[3].="<td align=left>" . $arrdatax[13] . "</td>";
                $frm[3].= "<td align=left>" . $strnama1b[$arrdatax[14]]."</td>";
                $frm[3].="<td align=left>Sebelum(Edit)</td>";
                $frm[3].= "</tr>"; 
                $frm[3].= "<tr class=rowcontent>";
                $frm[3].= "<td align=center bgcolor='orange'>&#8627</td>";
                $frm[3].= "<td align=left bgcolor='orange'>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['alamat'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['kontakperson'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['kota'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['telepon'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['extm'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['teleponlain'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['jabatan'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['fax'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['email_koresponden'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['email_konfirmasi'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['provinsi'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['negara'] . "</td>";
                $frm[3].="<td align=left bgcolor='orange'>" . $d['kodepos'] . "</td>";
                $frm[3].= "<td align=left bgcolor='orange'>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
                $frm[3].="<td align=left bgcolor='orange'>Sesudah(Edit)</td>";
                $frm[3].= "</tr>"; 
              }
              else
              {

                $statdata='Tidak Ada Perubahan';
                $frm[3].= "<tr class=rowcontent>";
                $frm[3].="<td align=left>" . $no . "</td>";
                $frm[3].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
                $frm[3].="<td align=left>" . $d['alamat'] . "</td>";
                $frm[3].="<td align=left>" . $d['kontakperson'] . "</td>";
                $frm[3].="<td align=left>" . $d['kota'] . "</td>";
                $frm[3].="<td align=left>" . $d['telepon'] . "</td>";
                $frm[3].="<td align=left>" . $d['extm'] . "</td>";
                $frm[3].="<td align=left>" . $d['teleponlain'] . "</td>";
                $frm[3].="<td align=left>" . $d['jabatan'] . "</td>";
                $frm[3].="<td align=left>" . $d['fax'] . "</td>";
                $frm[3].="<td align=left>" . $d['email_koresponden'] . "</td>";
                $frm[3].="<td align=left>" . $d['email_konfirmasi'] . "</td>";
                $frm[3].="<td align=left>" . $d['provinsi'] . "</td>";
                $frm[3].="<td align=left>" . $d['negara'] . "</td>";
                $frm[3].="<td align=left>" . $d['kodepos'] . "</td>";
                $frm[3].= "<td align=left>" . $strnama1b[$d['status']]."</td>";
                $frm[3].="<td align=left>" . $statdata . "</td>";
                $frm[3].= "</tr>"; 
              }
            }
        }

    $frm[3].="</tbody></table>";

       $frm[4]="
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
         <td align=center>" . $_SESSION['lang']['pph'] . "</td>
         <td align=center>" . $_SESSION['lang']['tarif'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Aktif</td>
         <td align=center>" . $_SESSION['lang']['status'] . " Data</td>
       </tr>
    </thead>
    <tbody>";


  $no=0;

      $input = "select * from " . $dbname . ".log_5pphsup where supplierid = '".$idsupplier."'";
      $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
      $n->setFetchMode(PDO::FETCH_ASSOC);
        while ($d = $n->fetch()) {
          $no+=1;
          $nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
          if($d['perubahan']=='')
            {

            $statdata='Data Baru';
            $frm[4].= "<tr class=rowcontent>";
            $frm[4].= "<td align=center>" . $no . "</td>";
            $frm[4].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            $frm[4].= "<td align=left>" . (isset($nmAkun[$d['noakun']]) ? $nmAkun[$d['noakun']] : '') . "</td>";
            $frm[4].= "<td align=left>" . $d['tarif'] . "</td>";
            $frm[4].= "<td align=left>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
            $frm[4].="<td align=left>" . $statdata . "</td>";
            $frm[4].= "</tr>";
            }
            else
            {
              $arrdatax=explode('##', $d['perubahan']);
              if($arrdatax[0]!='')
              {

                $statdata='Edit Data';
            $frm[4].= "<tr class=rowcontent>";
            $frm[4].= "<td align=center>" . $no . "</td>";
            $frm[4].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            $frm[4].= "<td align=left>" . (isset($nmAkun[$d['noakun']]) ? $nmAkun[$d['noakun']] : '') . "</td>";
            $frm[4].= "<td align=left>" . $arrdatax[2] . "</td>";
            $frm[4].= "<td align=left>" . $strnama1b[$arrdatax[3]]."</td>";
            $frm[4].="<td align=left>Sebelum(Edit)</td>";
            $frm[4].= "</tr>";

            $frm[4].= "<tr class=rowcontent>";
            $frm[4].= "<td align=center bgcolor='orange'>&#8627</td>";
            $frm[4].= "<td align=left bgcolor='orange'>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            $frm[4].= "<td align=left bgcolor='orange'>" . (isset($nmAkun[$d['noakun']]) ? $nmAkun[$d['noakun']] : '') . "</td>";
            $frm[4].= "<td align=left bgcolor='orange'>" . $d['tarif'] . "</td>";
            $frm[4].= "<td align=left bgcolor='orange'>" . $strnama1b[$d['statusyangdiinginkan']]."</td>";
            $frm[4].="<td align=left bgcolor='orange'>Sesudah(Edit)</td>";
            $frm[4].= "</tr>";
              }
              else
              {
            $statdata='Tidak Ada Perubahan';
            $frm[4].= "<tr class=rowcontent>";
            $frm[4].= "<td align=center>" . $no . "</td>";
            $frm[4].= "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            $frm[4].= "<td align=left>" . (isset($nmAkun[$d['noakun']]) ? $nmAkun[$d['noakun']] : '') . "</td>";
            $frm[4].= "<td align=left>" . $d['tarif'] . "</td>";
            $frm[4].= "<td align=left>" . $strnama1b[$d['status']]."</td>";
            $frm[4].="<td align=left>" . $statdata . "</td>";
            $frm[4].= "</tr>";

              }
            }
             
        }

        
    $frm[4].="</tbody></table>";

    $xfrm[0]="Data Header";
    $xfrm[1]="Rek Bank";
    $xfrm[2]="NPWP Supplier";
    $xfrm[3]="Alamat Supplier";
    $xfrm[4]="Pajak";
    drawTab('FRMX',$xfrm,$frm,100,'100%');
    break;
    case 'delete':
        $strx = "delete from " . $dbname . ".log_5supplier where supplierid='" . $idsupplier . "'";
        break;

        // case 'checknamecompany':
        //   $companyname = strtoupper(checkPostGet('companyname', ''));
        //   $badanusaha = strtoupper(checkPostGet('badanusaha', ''));
        //   $ripStr = array('.', ' ',',');
          
        //   $New_companyname = getformat_companyname($companyname,$badanusaha);
        //   $trimedName = trim(str_replace($ripStr,'',$New_companyname));
          
        //   $strSup =$owlPDO->query("select namasupplier,badanusaha from ".$dbname.".log_5supplier where supplierid != '".$_SESSION['standard']['idsupplier']."'");
        //   $results = $strSup->fetchAll(PDO::FETCH_ASSOC );
        //   $res['create'] = true;
        //   if(count($results) > 0){
        //     for($i=0; $i<count($results); $i++){
        //       $dataCompany = getformat_companyname($results[$i]['namasupplier'],$results[$i]['badanusaha']);
        //       $trimedCompany = trim(str_replace($ripStr,'',$dataCompany));
        //       if($trimedName == $trimedCompany){
        //         $res['create'] = false;
        //       }
        //     }
        //   }
        //   echo json_encode($res);
        // break;

        case 'insert':
		$textubah='';
		$email = strtolower(trim($useremail));
		$strSup =$owlPDO->query("select email from ".$dbname.".log_5supuser where email ='".$email."' and full_name='".$namasupplier."' ");
		$results = $strSup->fetchAll(PDO::FETCH_ASSOC );
		if(count($results) > 0){
			exit("ERROR: NAMA DAN EMAIL SUDAH DIPAKAI");
		}
		
        //CEK Supplier apakah sudah ada apa belum
        $ripStr = array('.', ' ',',');
        $New_companyname = getformat_companyname($namasupplier,$badan);
        $trimedName = trim(str_replace($ripStr,'',$New_companyname));

		$strSup =$owlPDO->query("select namasupplier,badanusaha from ".$dbname.".log_5supplier");
		$results = $strSup->fetchAll(PDO::FETCH_ASSOC );
          if(count($results) > 0){
            for($i=0; $i<count($results); $i++){
              $dataCompany = getformat_companyname($results[$i]['namasupplier'],$results[$i]['badanusaha']);
              $trimedCompany = trim(str_replace($ripStr,'',$dataCompany));
              if($trimedName == $trimedCompany){
                exit("ERROR: DATA SUDAH ADA");
              }
            }
          }

        #S201707001
		$newpas = rand_passwd(4);
        $tahunbulan = "S".date("Ym");
        $query="select right(supplierid,3) as nomorurut from ".$dbname.".log_5supplier where left(supplierid,7) = '".$tahunbulan."' order by right(supplierid,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
      
        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
        // echo $query;
        // exit('warning :'.$_SESSION['lang']['datasudahada']);

        // $counter=$awal;
        // if($awal<1000){
        //   $counter=addZero($awal,3);
        // }

        $idsupplier=$tahunbulan.addZero($awal,3);
        // exit('error: '.$idsupplier);
        
        $input = "insert into " . $dbname . ".log_5supplier (supplierid,namasupplier,badanusaha,namapemilik,
		namadirektur,namapenanggungjawab,jabatan,status,statuspersetujuan,statusyangdiinginkan,perubahan,createby)
            values ('" . $idsupplier . "','" . $namasupplier . "','" . $badan . "','" . $pemilik . "',
			'" . $direktur . "','" . $pj . "','" . $jabatan . "','0','0','".$statusup."',
			'".$textubah."','".$_SESSION['standard']['userid']."')";
        // $input2 = "insert into " . $dbname . ".log_5supuser (supplierid,namasupplier,email,status)
        // values ('" . $idsupplier . "','" . $namasupplier . "','" . $email . "','" . $statusup . "')";
        // exit('error:'.$input);
        try{
          $owlPDO->exec($input);
          
          $listpersetujuan=$_POST['persetujuan'];
          foreach($listpersetujuan as $key=>$val)
          {
            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$idsupplier."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
            try
            {
              $owlPDO->exec($str);
            }
            catch (PDOException $e) 
            {
              print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
          }
        

			$log_5supuser = "insert into " . $dbname . ".log_5supuser (id_supplier,full_name,email,password,date_reg,isactive)
            values ('" . $idsupplier . "','" . $namasupplier . "','" . $email . "',PASSWORD('" . $newpas . "'),'" . date("Y-m-d") . "','1')";
			try{
				$owlPDO->exec($log_5supuser); 
			}catch(PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }

        // try{
        //   $owlPDO->exec($input2; 
        // }catch(PDOException $e){
        //   echo " Gagal," . addslashes($e->getMessage());
        // }
        break;

        case 'update':
        $email = strtolower(trim($useremail));


          $strx = selectQuery($dbname,"log_5supplier","*","supplierid='".$idsupplier."'");
          $resx = fetchData($strx);
          $oldx['supplierid'] = $resx[0]['supplierid'];
          $oldx['namasupplier'] = $resx[0]['namasupplier'];
          $oldx['badanusaha'] = $resx[0]['badanusaha'];
          $oldx['namapemilik'] = $resx[0]['namapemilik'];
          $oldx['namadirektur'] = $resx[0]['namadirektur'];
          $oldx['namapenanggungjawab'] = $resx[0]['namapenanggungjawab'];
          $oldx['jabatan'] = $resx[0]['jabatan'];
          $oldx['status'] = $resx[0]['status'];
          $perubahanx = $resx[0]['perubahan'];


          $strx = selectQuery($dbname,"log_5supuser","*","id_supplier='".$idsupplier."'");
          $resx = fetchData($strx);
          $oldx['full_name'] = $resx[0]['full_name'];
          $oldx['email'] = $resx[0]['email'];

          $strx = selectQuery($dbname,"log_5supkelompok","*","supplierid='".$idsupplier."'");
          $resx = fetchData($strx);
          $oldx['jenisusaha1'] = $resx[0]['tipe'];
          $oldx['jenisusaha2'] = $resx[1]['tipe'];
          $oldx['jenisusaha3'] = $resx[2]['tipe'];
          $oldx['jenisusaha4'] = $resx[3]['tipe'];
        

        $textubah=$oldx['supplierid']. "##" .$oldx['namasupplier'] . "##" . $oldx['badanusaha'] . "##" . $oldx['namapemilik'] . "##" . $oldx['namadirektur'] . "##" . $oldx['namapenanggungjawab'] . "##" . $oldx['jabatan'] . "##" . $oldx['status'] . "##" . $oldx['email']."##".$oldx['jenisusaha1'].','.$oldx['jenisusaha2'].','.$oldx['jenisusaha3'].','.$oldx['jenisusaha4'];

        // $textubah=$idsupplier. "##" .$namasupplier . "##" . $badan . "##" . $pemilik . "##" . $direktur . "##" . $pj . "##" . $jabatan . "##" . $statusup . "##" . $email."##".$jenisusaha;


            $input = "update " . $dbname . ".log_5supplier set namasupplier='".$namasupplier."',badanusaha='".$badan."',namapemilik='".$pemilik."',namadirektur='".$direktur."',namapenanggungjawab='".$pj."',jabatan='".$jabatan."',statusyangdiinginkan='".$statusup."',statuspersetujuan='0',status='0',perubahan='".$textubah."' where supplierid='" . $idsupplier . "'";
            if($perubahanx!='')
            {
              $arrperub=explode('##', $perubahanx);
              if($arrperub[0]!='')
              {
                 $input = "update " . $dbname . ".log_5supplier set namasupplier='".$namasupplier."',badanusaha='".$badan."',namapemilik='".$pemilik."',namadirektur='".$direktur."',namapenanggungjawab='".$pj."',jabatan='".$jabatan."',statusyangdiinginkan='".$statusup."',statuspersetujuan='0',status='0'  where supplierid='" . $idsupplier . "'";
              }
            }
            try{
				  $owlPDO->exec($input); 
          $strx="delete from ".$dbname.".approval where notransaksi='".$idsupplier."' and jenispersetujuan='".$jnsapp."'";
        try
        {

          $strdelsup="delete from ".$dbname.".log_5supuser where id_supplier='".$idsupplier."'";
          //$owlPDO->exec($strdelsup);
          try{
            $owlPDO->exec($strdelsup); 
          }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
          }
          $log_5supuser = "insert into " . $dbname . ".log_5supuser (id_supplier,full_name,email,password,date_reg,isactive)
            values ('" . $idsupplier . "','" . $namasupplier . "','" . $email . "',PASSWORD('" . $newpas . "'),'" . date("Y-m-d") . "','1')";
            //echo 'error : '.$log_5supuser;
            //exit();
          try{
            $owlPDO->exec($log_5supuser); 
          }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
          }
          $owlPDO->exec($strx); 
          $listpersetujuan=$_POST['persetujuan'];
          foreach($listpersetujuan as $key=>$val)
          {
            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$idsupplier."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
            try
            {
              $owlPDO->exec($str);
            }
            catch (PDOException $e) 
            {
              print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
          }
        }
        catch(PDOException $e)
        {
          echo " Gagal," . addslashes($e->getMessage());
        }
				$exec = array();
				$datajenisusaha = array();
				$val_jenisusaha = explode(',',$jenisusaha);
				for($i=0; $i<count($val_jenisusaha); $i++){
					$d['supplierid']	= $idsupplier;
					$d['tipe'] 			= $val_jenisusaha[$i];
					$d['updateby']	    = $_SESSION['standard']['userid'];
          $log_5klsupplier = selectQuery($dbname,"log_5klsupplier","noakun","tipe='".$val_jenisusaha[$i]."'");
          $resultlog_5klsupplier = fetchData($log_5klsupplier);
          $d['noakun'] = $resultlog_5klsupplier[0]['noakun'];
					$datajenisusaha[]	= $d;
				}
				$log_5supkelompok = selectQuery($dbname,"log_5supkelompok","supplierid","supplierid='".$idsupplier."'");
				$result = fetchData($log_5supkelompok);
				if(count($result) > 0){
					$exec[] = deleteQuery($dbname,'log_5supkelompok',"supplierid = '".$idsupplier."'");
				}
				for($i=0; $i<count($datajenisusaha); $i++){
					$col = array();
					$dat = array();
					foreach($datajenisusaha[$i] as $k => $val){
						$col[] = $k;
						$dat[] = $val;
					}
					$exec[] = insertQuery($dbname,'log_5supkelompok',$dat,$col);
				}
				//print_r($exec);
				//exit("ERROR");
				for($i=0; $i<count($exec); $i++){
					try{
						$owlPDO->exec($exec[$i]);
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; die();
					}
				}
        }catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;
case'cariBarangDlmDtBs':
    $txtfind=$_POST['txtfind'];
        //exit('warning : '.$txtfind);
    $str="select * from ".$dbname.".log_5supplier where namasupplier like '%".$txtfind."%'";
    // echo $str;
    // $res=$owlPDO->query($str);
    
    if($res=$owlPDO->query($str)){
      echo "<fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; max-height:300px;\" >
        <table class=sortable cellspacing=1 cellpadding=2  border=0>
          <thead>
          <tr class=rowheader>
            <td class=firsttd align=center>No.</td>
            <td align=center>".$_SESSION['lang']['supplierid']."</td>
            <td align=center>".$_SESSION['lang']['namasupplier']."</td>          
          </tr>
          </thead>
          <tbody>";
          
      $no=0;   
      $res->setFetchMode(PDO::FETCH_OBJ);
      while($bar=$res->fetch()){
        $no+=1;
        
        echo "
       <tr class=rowcontent>
        <td class=firsttd  align=center>".$no."</td>
            <td align=left>".$bar->supplierid."</td>
            <td align=left>".$bar->namasupplier."</td>
      </tr>";
      }  

      echo "</tbody>
        <tfoot>
        </tfoot>
        </table></div></fieldset>";
    }else{
      echo " Gagal,".PDOException::getMessage();
    }
  break;

  case'cariNoAkun':
    $txtfind=$_POST['txtfind'];
        //exit('warning : '.$txtfind);
    $str="select * from ".$dbname.".log_5supplier where supplierid like '%".$txtfind."%'";
    // echo $str;
    // $res=$owlPDO->query($str);
    
    if($res=$owlPDO->query($str)){
      echo "<fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; max-height:300px;\" >
        <table class=sortable cellspacing=1 cellpadding=2  border=0>
          <thead>
          <tr class=rowheader>
            <td class=firsttd align=center>No.</td>
            <td align=left>".$bar->supplierid."</td>
            <td align=left>".$bar->namasupplier."</td>
           
          </tr>
          </thead>
          <tbody>";
          
      $no=0;   
      $res->setFetchMode(PDO::FETCH_OBJ);
      while($bar=$res->fetch()){
        $no+=1;
        
        echo "
       <tr class=rowcontent>
        <td class=firsttd  align=center>".$no."</td>
            <td align=left>".$bar->supplierid."</td>
            <td align=left>".$bar->namasupplier."</td>
      </tr>";
      }  
         
      echo "</tbody>
        <tfoot>
        </tfoot>
        </table></div></fieldset>";
    }else{
      echo " Gagal,".PDOException::getMessage();
    }
  break;


case'loadData':
     ?>
	<thead>
		<tr class=header>
			<!--<th align=center>".$_SESSION['lang']['kodekelompok']."</th>-->
			<th align=center><?php echo $_SESSION['lang']['nourut']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['kodesupplier']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['namasupplier']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['badanusaha']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['pemilik']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['direktur']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['namapj']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['jabatan']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['status'] . " " . $_SESSION['lang']['supplier']; ?></th>
      <th align=center><?php echo $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan']; ?></th>
			<th  align='center' colspan='6'><?php echo $_SESSION['lang']['action']; ?></th>
		</tr>
	</thead> 
	<tbody> 
	<?php 
	//paging untuk membatyasi data perhalaman
	if($txt_search!='')
    {
       $where=" and namasupplier LIKE  '%".$txt_search."%'";
    }
    if($txtNoakun!='')
    {
       $where=" and supplierid LIKE  '%".$txtNoakun."%'";
    }
	if($caristatusup!='')
    {
       $where=" and status LIKE  '%".$caristatusup."%'";
    }
	if($caribadan!='')
    {
       $where=" and badanusaha LIKE  '%".$caribadan."%'";
    }
        $limit = 20;
        $page = 1;
		$p = new Paging; // -Paging- Class paging
		
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 1)
                $page = 1;
        }
		$posisi = $p->cariPosisi($limit,$page);// -Paging- Posisi Data
        $ql2 = "select supplierid from " . $dbname . ".log_5supplier where status = '1' ".$where." order by namasupplier asc";
		$rjml = fetchData($ql2);
		$jlhbrs = count($rjml);
        $jml = $p->jumlahHalaman($jlhbrs,$limit);//-Paging- jumlah data
		
        $tab='';
		$nor=0;
    
        $input = "select a.*,b.email,b.full_name from " . $dbname . ".log_5supplier a 
		left join " . $dbname . ".log_5supuser b on a.supplierid = b.id_supplier
		where status = '1' ".$where."  order by namasupplier asc LIMIT $posisi,$limit";
		$n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $posisi+1;
        while ($d = $n->fetch()) {

            // $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
			$whereJam = "supplierid='".$d['supplierid']."'";
            $nmSup = makeOption($dbname, 'log_5supkelompok', 'tipe,tipe',$whereJam);
			$sup="";
			foreach($nmSup as $k =>$v){
				$sup .= $v.",";
			}
			$sup = substr($sup, 0, -1);
            //$no+=1;
            echo"<tr class=rowcontent>";
            echo"<td align=center>" . $no . "</td>";
			echo"<td align=left>" . $d['supplierid'] . "</td>";
			echo"<td align=left>" . $d['namasupplier'] . "</td>";
			echo"<td align=left>" . $d['badanusaha'] . "</td>";
			echo"<td align=left>" . $d['namapemilik'] . "</td>";
			echo"<td align=left>" . $d['namadirektur'] . "</td>";
			echo"<td align=left>" . $d['namapenanggungjawab'] . "</td>";
			echo"<td align=left>" . $d['jabatan'] . "</td>";
			echo "<td align=center>" . $strnama[$d['status']]."</td>";
      echo "<td align=center>" . $strnamaper[$d['statuspersetujuan']]."</td>";
			echo"<td align=center>
				<img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editSupplier2('" . $d['supplierid'] . "','" . $d['namasupplier'] . "','" . $d['badanusaha'] . "','" . $d['namapemilik'] . "','" . $d['namadirektur'] . "','" . $d['namapenanggungjawab'] . "','" . $d['jabatan'] . "','" . $d['status'] . "','" . $d['email'] . "','" . $sup . "');\">
			</td><td align=center>
				<img src=images/addplus.png class=zImgBtn  title='Add Detail Supplier' onclick=\"detaildt('".$_SESSION['lang']['detail']."','" . $d['supplierid'] . "','" .$d['namasupplier']."','" .$d['status']."');\"></td>
        <td align=center><img src=images/application/application_edit_lama.png class=zImgBtn  title='Detail Perubahan Supplier' onclick=\"detailsupp('".$_SESSION['lang']['detail']."','" . $d['supplierid'] . "');\">
			</td><td align=center>
        <img src=images/zoom.png class=zImgBtn  title='Add Detail Supplier' onclick=\"detailupload('List Upload','" . $d['supplierid'] . "','" .$d['namasupplier']."','" .$d['badanusaha']."');\">
      </td>";

    if ($d['status']==1) {
      echo"<td align=center>
        <img src=images/chat1.png class=zImgBtn  title='Portal Supplier' onclick=\"portaltender('".$d['supplierid']."','" .$d['namasupplier']."',event);\">
      </td>";
    }

      echo"</tr>"; 
		$no++;
        }
      echo"</tbody>";?>
		<tfoot>
			<tr>
				<td colspan="15" align="center">
				<?php 
					//insert Attribute action ex: href/onclick/onchange/etc..
					$buttonaction = array(
						'first' =>	'onclick="loadData1(1);"',
						'prev' 	=> 	'onclick="loadData1('.($page-1).');"',
						'next' 	=> 	'onclick="loadData1('.($page+1).');"',
						'last' 	=> 	'onclick="loadData1('.($jml).')"',
						'pages'	=> 	'id="pages" name="pages" onchange="loadData1(this.value);"'
					);
					echo $p->navHalaman($page,$jml,$buttonaction); //-Paging- Create Element Nav halaman; 
				?>
				</td>
			</tr>
		</tfoot>
		</table>
	<?php
break;

case'loadDatacalon':
    ?>
	<thead>
		<tr class=header>
			<!--<th align=center>".$_SESSION['lang']['kodekelompok']."</th>-->
			<th align=center><?php echo $_SESSION['lang']['nourut']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['kodesupplier']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['namasupplier']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['badanusaha']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['pemilik']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['nama']." ".$_SESSION['lang']['direktur']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['namapj']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['jabatan']; ?></th>
			<th align=center><?php echo $_SESSION['lang']['status'] . " " . $_SESSION['lang']['supplier']; ?></th>
      <th align=center><?php echo $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan']; ?></th>
			<th  align='center' colspan='5'><?php echo $_SESSION['lang']['action']; ?></th>
		</tr>
	</thead> 
	<tbody> 
	
	<?php 
	//paging untuk membatyasi data perhalaman
  $where='';
	if($txtsearchcalon !='')
    {
       $where.=" and namasupplier LIKE  '%".$txtsearchcalon ."%'";
    }
    if($txtNoakuncalon!='')
    {
       $where.=" and supplierid LIKE  '%".$txtNoakuncalon."%'";
    }

        $limit = 20;
        $page = 1;
		$p = new Paging; // -Paging- Class paging
		
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 1)
                $page = 1;
        }
		$posisi = $p->cariPosisi($limit,$page);// -Paging- Posisi Data berdasarkan limit
        $ql2 = "select supplierid from " . $dbname . ".log_5supplier where status in ('0','2','3','4') ".$where."";
        //exit('Error '.$ql2);
		$rjml = fetchData($ql2);
		$jlhbrs = count($rjml);
        $jml = $p->jumlahHalaman($jlhbrs,$limit);//-Paging- jumlah data
        $tab='';
		$nor=0;
   
		$input = "select a.*,b.email,b.full_name from " . $dbname . ".log_5supplier a
		left join " . $dbname . ".log_5supuser b on a.supplierid = b.id_supplier
		where a.status in ('0','2','3','4') ".$where."  LIMIT $posisi,$limit";
		$n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $posisi+1;
        while ($d = $n->fetch()) {

            // $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $whereJam = "supplierid='".$d['supplierid']."'";
            $nmSup = makeOption($dbname, 'log_5supkelompok', 'tipe,tipe',$whereJam);
			$sup="";
			foreach($nmSup as $k =>$v){
				$sup .= $v.",";
			}
			$sup = substr($sup, 0, -1);
            $nor+=1;
            echo"<tr class=rowcontent>";
            echo"<td align=center>" . $nor . "</td>";
			echo"<td align=left>" . $d['supplierid'] . "</td>";
			echo"<td align=left>" . $d['namasupplier'] . "</td>";
			echo"<td align=left>" . $d['badanusaha'] . "</td>";
			echo"<td align=left>" . $d['namapemilik'] . "</td>";
			echo"<td align=left>" . $d['namadirektur'] . "</td>";
			echo"<td align=left>" . $d['namapenanggungjawab'] . "</td>";
			echo"<td align=left>" . $d['jabatan'] . "</td>";
			echo "<td align=center>" . $strnama[$d['status']]."</td>";
      echo "<td align=center>" . $strnamaper[$d['statuspersetujuan']]."</td>";
			echo"<td align=center>
				<img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editSupplier2('" . $d['supplierid'] . "','" . $d['namasupplier'] . "','" . $d['badanusaha'] . "','" . $d['namapemilik'] . "','" . $d['namadirektur'] . "','" . $d['namapenanggungjawab'] . "','" . $d['jabatan'] . "','" . $d['status'] . "','" . $d['email'] . "','" .$sup. "');\">
			</td><td align=center>
				<img src=images/addplus.png class=zImgBtn  title='Add Detail Supplier' onclick=\"detaildt('".$_SESSION['lang']['detail']."','" . $d['supplierid'] . "','" .$d['namasupplier']."','" .$d['status']."');\"></td>
        <td align=center><img src=images/application/application_edit_lama.png class=zImgBtn  title='Detail Perubahan Supplier' onclick=\"detailsupp('".$_SESSION['lang']['detail']."','" . $d['supplierid'] . "');\">
			</td><td align=center>
				<img src=images/zoom.png class=zImgBtn  title='Add Detail Supplier' onclick=\"detailupload('List Upload','" . $d['supplierid'] . "','" .$d['namasupplier']."','" .$d['badanusaha']."');\">
			</td>";
			if($d['status'] == '2' or $d['status'] == '3'){
				echo"<td align=center>
					<button class='mybutton' onclick=\"sendUrl('" . $d['supplierid'] . "');\">Kirim link ke Email</button>
				</td>";
			}else{
				echo"<td align=center></td>";
			}
            echo"</tr>"; 
        }
    echo"</tbody>";?>
		<tfoot>
			<tr>
				<th colspan="15" align="center">
				<?php 
					//insert Attribute action ex: href/onclick/onchange/etc..
					$buttonaction = array(
						'first' =>	'onclick="loadDatacalon(1);"',
						'prev' 	=> 	'onclick="loadDatacalon('.($page-1).');"',
						'next' 	=> 	'onclick="loadDatacalon('.($page+1).');"',
						'last' 	=> 	'onclick="loadDatacalon('.($jml).')"',
						'pages'	=> 	'id="pages" name="pages" onchange="loadDatacalon(this.value);"'
					);
					echo $p->navHalaman($page,$jml,$buttonaction); //-Paging- Create Element Nav halaman; 
				?>
				</th>
			</tr>
		</tfoot>
	</table>
	<?php
	break;
	case 'kirimemail':
		$newpas = rand_passwd(4);
		$exp 	= urldecode(base64_encode(date("Ymd")));
		$url 	=  site_url()."/".segment(1);
		$qstr = "select * from " . $dbname . ".log_5supuser where id_supplier = '".$idsupplier."' limit 1";
		$r = fetchData($qstr);
		if(count($r) >0){
		$log_5supuser = "UPDATE " . $dbname . ".log_5supuser set password = PASSWORD('" . $newpas . "'), sessionid = 'register' where id_supplier = '".$idsupplier."' limit 1";
			try{
				$owlPDO->exec($log_5supuser); 
			}catch(PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
			$subject	=	"Aplikasi Data Supplier";
			$from		=	"STH Grup";
			$to			=	trim($r[0]['email']);
			$link 		=   $url."/supplier/?log=".$exp;
			$content	= 	"<table>";
			$content	.= "<tr><td>Register Data Supplier</td></tr>";
			$content	.= "<tr><td>Url </td><td>: $link</td></tr>";
			$content	.= "<tr><td>Email  </td><td>: $to</td></tr>";
			$content	.= "<tr><td>Password  </td><td>: $newpas</td></tr>";
			$content	.= "<tr><td>Email ini dikirim untuk membuka halaman Register Supplier.</td></tr>";
			$content	.= "</table>";
		kirimEmailkeSupplier($to,$cc = "",$subject,$content,$mailType='text/html');
		}
	break;
    default:
    break;
}

?>