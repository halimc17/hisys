<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$idalamat = checkPostGet('idalamat','');
$supplierid2 = checkPostGet('supplierid2','');
$alamatsup = checkPostGet('alamatsup','');
$kota1 = checkPostGet('kota1','');
$cperson = checkPostGet('cperson','');
$telp = checkPostGet('telp','');
$extensi = checkPostGet('extensi','');
$nohp = checkPostGet('nohp','');
$jabatan1 = checkPostGet('jabatan1','');
$fax = checkPostGet('fax','');
$emailkor = checkPostGet('emailkor','');
$emailkon = checkPostGet('emailkon','');
$provinsi1 = checkPostGet('provinsi1','');
$emailkon = checkPostGet('emailkon','');
$negara1 = checkPostGet('negara1','');
$kodepos1 = checkPostGet('kodepos1','');
$statusalamat = checkPostGet('statusalamat','');
$method = checkPostGet('method','');
$strnama = array ("0"=>"Tidak aktif","1"=>"Aktif");
// bikin baru lagi pake array untuk load data yg checkbox
// $strnama = array ("0"=>"tidak aktif","1"=>"aktif");
$strnamaper = array ("0"=>"Proses persetujuan","1"=>"Disetujui","2"=>"Ditolak");
$jnsapp = "DS";

// exit('warning : '.$method);

switch ($method) {

    case 'insert':
    // exit ('warning:gagal');
        $input = "insert into " . $dbname . ".log_5supalamat (supplierid,alamat,kota,telepon,extm,teleponlain,kontakperson,jabatan,fax,email_koresponden,email_konfirmasi,provinsi,negara,kodepos,updateby,status,statusyangdiinginkan,statuspersetujuan)
            values ('" . $supplierid2 . "','" . $alamatsup . "','" . $kota1 . "','" . $telp . "','" . $extensi . "','" . $nohp . "','" . $cperson . "','" . $jabatan1 . "','" . $fax . "','" . $emailkor . "','" . $emailkon . "','" . $provinsi1 . "','" . $negara1 . "','" . $kodepos1 . "','" . $_SESSION['standard']['userid'] . "','0','" . $statusalamat . "','0')";
            // echo($input);
    try{
      $owlPDO->exec($input); 
      $strx="delete from ".$dbname.".approval where notransaksi='".$supplierid2."' and jenispersetujuan='".$jnsapp."'";
            try
            {
              $owlPDO->exec($strx); 
              $listpersetujuan=$_POST['persetujuan'];
              foreach($listpersetujuan as $key=>$val)
              {
                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$supplierid2."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
                try
                {
                  $owlPDO->exec($str);
                }
                catch (PDOException $e) 
                {
                  print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                }
              }
            }catch(PDOException $e){
              echo " Gagal," . addslashes($e->getMessage());
            }
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
        break;

    case 'update':
          $strx = selectQuery($dbname,"log_5supalamat","*","id_alamat='" . $idalamat . "' and supplierid='".$supplierid2."'");
          $resx = fetchData($strx);
          $oldx['supplierid'] = $resx[0]['supplierid'];
          $oldx['alamat'] = $resx[0]['alamat'];
          $oldx['kontakperson'] = $resx[0]['kontakperson'];
          $oldx['kota'] = $resx[0]['kota'];
          $oldx['telepon'] = $resx[0]['telepon'];
          $oldx['extm'] = $resx[0]['extm'];
          $oldx['teleponlain'] = $resx[0]['teleponlain'];
          $oldx['jabatan'] = $resx[0]['jabatan'];
          $oldx['fax'] = $resx[0]['fax'];
          $oldx['email_koresponden'] = $resx[0]['email_koresponden'];
          $oldx['email_konfirmasi'] = $resx[0]['email_konfirmasi'];
          $oldx['provinsi'] = $resx[0]['provinsi'];
          $oldx['negara'] = $resx[0]['negara'];
          $oldx['kodepos'] = $resx[0]['kodepos'];
          $oldx['status'] = $resx[0]['status'];
          $perubahanx = $resx[0]['perubahan'];


          $textubah=$oldx['supplierid']. "##" .$oldx['alamat'] . "##" . $oldx['kontakperson'] . "##" . $oldx['kota'] . "##" . $oldx['telepon'] . "##" . $oldx['extm'] . "##" . $oldx['teleponlain'] . "##" . $oldx['jabatan'] . "##" . $oldx['fax'] . "##" . $oldx['email_koresponden']."##".$oldx['email_konfirmasi'].'##'.$oldx['provinsi'].'##'.$oldx['negara'].'##'.$oldx['kodepos'].'##'.$oldx['status'];

    // exit('warning:gagal');
        $input = "update " . $dbname . ".log_5supalamat set alamat='" . $alamatsup . "',kota='" . $kota1 . "',telepon='" . $telp . "',extm='" . $extensi . "',teleponlain='" . $nohp . "',kontakperson='" . $cperson . "',jabatan='" . $jabatan1 . "',fax='" . $fax . "',email_koresponden='" . $emailkor . "',email_konfirmasi='" . $emailkon . "',provinsi='" . $provinsi1 . "',negara='" . $negara1 . "',kodepos='" . $kodepos1 . "',". " updateby='" . $_SESSION['standard']['userid'] . "',statusyangdiinginkan='" . $statusalamat . "',statuspersetujuan='0',status='0',perubahan='".$textubah."' 
             where id_alamat='" . $idalamat . "' and supplierid='" .$supplierid2."'";
             // exit('warning : gagal'.$input);

          if($perubahanx!='')
            {
              $arrperub=explode('##', $perubahanx);
              if($arrperub[0]!='')
              {
                   $input = "update " . $dbname . ".log_5supalamat set alamat='" . $alamatsup . "',kota='" . $kota1 . "',telepon='" . $telp . "',extm='" . $extensi . "',teleponlain='" . $nohp . "',kontakperson='" . $cperson . "',jabatan='" . $jabatan1 . "',fax='" . $fax . "',email_koresponden='" . $emailkor . "',email_konfirmasi='" . $emailkon . "',provinsi='" . $provinsi1 . "',negara='" . $negara1 . "',kodepos='" . $kodepos1 . "',". " updateby='" . $_SESSION['standard']['userid'] . "',statusyangdiinginkan='" . $statusalamat . "',statuspersetujuan='0',status='0' 
                    where id_alamat='" . $idalamat . "' and supplierid='" .$supplierid2."'";
              }
            }
        try{
      $owlPDO->exec($input); 
        $strx="delete from ".$dbname.".approval where notransaksi='".$supplierid2."' and jenispersetujuan='".$jnsapp."'";
            try
            {
              $owlPDO->exec($strx); 
              $listpersetujuan=$_POST['persetujuan'];
              foreach($listpersetujuan as $key=>$val)
              {
                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$supplierid2."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
                try
                {
                  $owlPDO->exec($str);
                }
                catch (PDOException $e) 
                {
                  print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                }
              }
            }catch(PDOException $e){
              echo " Gagal," . addslashes($e->getMessage());
            }
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
            
        break;

    //perhatikan load data
    case'loadData':
    // exit('warning masukk')
        echo"
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
         <td align=center>" . $_SESSION['lang']['status'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>
    </thead>
    <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5supalamat where supplierid = '".$supplierid2."'" ; // echo $ql2;notran

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $tab='';
  $nor=0;
    
        $input = "select * from " . $dbname . ".log_5supalamat  where supplierid = '".$supplierid2."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        // $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
            //$no+=1;
            echo "<tr class=rowcontent>";
            echo"<td align=left>" . $no . "</td>";
            echo "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
            // echo "<td align=left>" . $d['supplierid'] . "</td>";
            echo"<td align=left>" . $d['alamat'] . "</td>";
            echo"<td align=left>" . $d['kontakperson'] . "</td>";
            echo"<td align=left>" . $d['kota'] . "</td>";
            echo"<td align=left>" . $d['telepon'] . "</td>";
            echo"<td align=left>" . $d['extm'] . "</td>";
            echo"<td align=left>" . $d['teleponlain'] . "</td>";
            echo"<td align=left>" . $d['jabatan'] . "</td>";
            echo"<td align=left>" . $d['fax'] . "</td>";
            echo"<td align=left>" . $d['email_koresponden'] . "</td>";
            echo"<td align=left>" . $d['email_konfirmasi'] . "</td>";
            echo"<td align=left>" . $d['provinsi'] . "</td>";
            echo"<td align=left>" . $d['negara'] . "</td>";
            echo"<td align=left>" . $d['kodepos'] . "</td>";
            echo "<td align=left>" . $strnama[$d['status']]."</td>";
            echo "<td align=left>" . $strnamaper[$d['statuspersetujuan']]."</td>";

            echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editAlamat('" . $d['supplierid'] . "','" . $d['alamat'] . "','" . $d['kota'] . "','" . $d['telepon'] . "','" . $d['extm'] . "','" . $d['teleponlain'] . "','" . $d['kontakperson'] . "','" . $d['jabatan'] . "','" . $d['fax'] . "','" . $d['email_koresponden'] . "','" . $d['email_konfirmasi'] . "','" . $d['provinsi'] . "','" . $d['negara'] . "','" . $d['kodepos'] . "','" . $d['status'] . "','" . $d['id_alamat'] . "' );\">
                            </td>";

            echo "</tr>"; 
            //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['supplierid']."');\"> <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['kode']."');\">
        }

        //#bikin tombol untuk pagingnya
    //     $totrows=ceil($jlhbrs/$limit);
    // if($totrows==0)
    // {
    //   $totrows=1;
    // }
    
    // $isiRow='';
    // for($er=1;$er<=$totrows;$er++)
    // {
    //   $sel = ($page==$er-1)? 'selected': '';
    //   $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
    // }

    // echo"<tr><td colspan=20 align=center>";
    // echo"<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
    // echo"<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
    // echo"<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
    // echo"</td></tr>";
        
    echo"</tbody></table>";
        break;

    default:
}
?>
