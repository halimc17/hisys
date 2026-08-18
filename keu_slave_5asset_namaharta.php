<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');


$jenis = checkPostGet('jenis','');
$kelompok = checkPostGet('kelompok','');
$jenisusaha = checkPostGet('jenisusaha','');
$namaid = checkPostGet('namaid','');
$namaharta = checkPostGet('namaharta','');
$jumlah = checkPostGet('jumlah','');
$status1 = checkPostGet('status1','');
$strnama = array ("0"=>"tidak aktif","1"=>"aktif");
// $tglselesai=tanggalsystemn(checkPostGet('tglselesai',''));
$pages = checkPostGet('page', '');
$method = checkPostGet('method','');
$jenisharta = checkPostGet('jenisharta','');
$jenis_usaha = checkPostGet('jenis_usaha','');

$strx = "";
$data = array();
$data['error'] = 'false';

switch ($method) {

        case 'getkelompok':
        $str = "select id_klmpkharta,nama_kelompokharta from " . $dbname . ".keu_5asset_kelompokharta 
        where id_jnsharta = '" . $jenisharta . "' order by id_klmpkharta";
        $optkelompok = "<option value='' >" . $_SESSION['lang']['pilihdata'] . "</option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        
        $res->setFetchMode(PDO::FETCH_OBJ);
         
            while ($bar = $res->fetch()) {
              if($kelompok==$bar->id_klmpkharta){
                $optkelompok.="<option value='" . $bar->id_klmpkharta . "' selected>[" . $bar->id_klmpkharta . "] " . $bar->nama_kelompokharta . "</option>";
              }else{
                $optkelompok.="<option value='" . $bar->id_klmpkharta . "' >[" . $bar->id_klmpkharta . "] " . $bar->nama_kelompokharta . "</option>"; 
              }
            }
        echo $optkelompok;
        break;


        case 'getjenis':
        $str = "select id_jns_usaha,nama_jenis_usaha from " . $dbname . ".keu_5asset_jenis_usaha 
        where id_klmpkharta = '" . $jenis_usaha . "' and id_jnsharta = '" . $jenisharta . "' order by id_jns_usaha";
        $optjenis = "<option value='' >" . $_SESSION['lang']['pilihdata'] . "</option>";
         // exit('error: '.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        
        $res->setFetchMode(PDO::FETCH_OBJ);
         
            while ($bar = $res->fetch()) {
              if($jenisusaha==$bar->id_jns_usaha){
                $optjenis.="<option value='" . $bar->id_jns_usaha . "' selected>[" . $bar->id_jns_usaha . "] " . $bar->nama_jenis_usaha . "</option>";
              }else{
                $optjenis.="<option value='" . $bar->id_jns_usaha . "' >[" . $bar->id_jns_usaha . "] " . $bar->nama_jenis_usaha . "</option>";
              }
            }
        echo $optjenis;
        break;


        case 'insert':
        #1112
        $namaid=0;
        $nomor=$_POST['jenis'].$_POST['kelompok'].$_POST['jenisusaha'];
        // exit('warning'.$nomor);

        $query="select id_namaharta from keu_5asset_namaharta where id_jns_usaha='".$jenisusaha."' and id_namaharta like '".$nomor."%' order by id_namaharta desc limit 1";
        // exit('error:'.$query);
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
      
        // exit('error'.$nomor);
        if($rp['id_namaharta']==''){
            $namaid =$nomor.'1';
        }else if($rp['id_namaharta']!= ''){
          $namaid = $rp['id_namaharta']+1;
        }

            // exit('error '.$namaid);

        $i = "insert into " . $dbname . ".keu_5asset_namaharta (id_jnsharta,id_klmpkharta,id_jns_usaha,id_namaharta,namaharta,jumlah_bulan,status,createdby)
            values ('" . $jenis . "','" . $kelompok . "','" . $jenisusaha . "','" . $namaid . "','" . $namaharta . "','" . $jumlah . "','" . $status1 . "','" . $_SESSION['standard']['userid'] . "')";
          // exit('error '.$i);  
        try{
          $owlPDO->exec($i); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }
            break;

        case 'delData':
        $i="delete from ".$dbname.".keu_5asset_namaharta where id_namaharta='" . $namaid . "'";
        try{
            $owlPDO->exec($i); 
        }catch(PDOException $e){
           echo " Gagal," . addslashes($e->getMessage());
        }
        break;

        case 'update':
 
            $i = "update " . $dbname . ".keu_5asset_namaharta set id_jnsharta='" . $jenis . "',id_klmpkharta='" . $kelompok . "',id_jns_usaha='" . $jenisusaha . "',namaharta='" . $namaharta . "',jumlah_bulan='" . $jumlah . "',status='" . $status1 . "',". " updateby='" . $_SESSION['standard']['userid'] . "'
                 where id_namaharta='" . $namaid . "'";
            try{
          $owlPDO->exec($i); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
      }
            
        break;

      //  

case'loadData':

 $limit = 10;
    $page = 0;
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
        if ($page < 0)
            $page = 0;
    }
    $offset = $page * $limit;
    $maxdisplay = ($page * $limit);
    // exit('warning masukk')
        // echo"
      echo"
    <table class=sortable cellpadding=1 cellspacing=1 border=0>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['jenisharta']."</td>
         <td align=center>" .$_SESSION['lang']['kelompokharta']. "</td>
         <td align=center>" .$_SESSION['lang']['jenisusaha']. "</td>
         <td align=center>" .$_SESSION['lang']['id']." ".$_SESSION['lang']['nama']. " ".$_SESSION['lang']['harta']."</td>
         <td align=center>" .$_SESSION['lang']['nama']." ".$_SESSION['lang']['harta']. "</td>
         <td align=center>" .$_SESSION['lang']['jumlahbulanpenyusutan']. "</td>
         <td align=center>" .$_SESSION['lang']['status']. "</td>
         <td align=center>" . $_SESSION['lang']['dibuat'] . "</td>
         <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
    </thead>
    <tbody>";


        // $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_hutangbank order by kodeorg asc ".$where.""; 
       $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_5asset_namaharta order by id_namaharta asc ".$where.""; 

       $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        // exit ('error : '.$jlhbrs);
        $tab='';
  $nor=0;

        $i = "select * from " . $dbname . ".keu_5asset_namaharta order by id_namaharta asc ".$where." limit " . $offset . "," . $limit . "";
    $n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $no+=1;
            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $nmJenishar=  makeOption($dbname, 'keu_5asset_jenisharta', 'id_jnsharta,nama_jenisharta');
            $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
            $whr="id_jnsharta='".$d['id_jnsharta']."'";
            $whr2="id_jnsharta='".$d['id_jnsharta']."' and id_klmpkharta='".$d['id_klmpkharta']."'";
            $nmKel=  makeOption($dbname, 'keu_5asset_kelompokharta', 'id_klmpkharta,nama_kelompokharta',$whr);
            $nmUsaha=  makeOption($dbname, 'keu_5asset_jenis_usaha', 'id_jns_usaha,nama_jenis_usaha',$whr2);

            
          
            echo"<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            // echo"<td align=left>" . $d['id_jnsharta'] . "</td>";
            echo "<td align=left>" . (isset($nmJenishar[$d['id_jnsharta']]) ? $nmJenishar[$d['id_jnsharta']] : '') . "</td>";
            echo "<td align=left>" . (isset($nmKel[$d['id_klmpkharta']]) ? $nmKel[$d['id_klmpkharta']] : '') . "</td>";
            echo "<td align=left>" . (isset($nmUsaha[$d['id_jns_usaha']]) ? $nmUsaha[$d['id_jns_usaha']] : '') . "</td>";
            // echo"<td align=left>" . $jenis. "</td>";
            echo"<td align=left>" . $d['id_namaharta'] . "</td>";
            echo"<td align=left>" . $d['namaharta'] . "</td>";
            echo"<td align=left>" . $d['jumlah_bulan'] . "</td>";
            echo "<td align=left>" . $strnama[$d['status']]."</td>";
            echo "<td align=left>" . (isset($nmKar[$d['createdby']]) ? $nmKar[$d['createdby']] : '') . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            
            echo"<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['id_jnsharta'] . "','" . $d['id_klmpkharta'] . "','" . $d['id_jns_usaha'] . "','" . $d['id_namaharta'] . "','" . $d['namaharta'] . "','" . $d['jumlah_bulan'] . "','" . $d['status'] . "');\">";
            echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$d['id_namaharta']."');\">";
                          
             echo"</tr>"; 
            echo"</tr>"; 
        }
        //#bikin tombol untuk pagingnya
        $totrows=ceil($jlhbrs/$limit);
    if($totrows==0)
    {
      $totrows=1;
    }
    
    $isiRow='';
    for($er=1;$er<=$totrows;$er++)
    {
      $sel = ($page==$er-1)? 'selected': '';
      $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
    }

    echo"<tr><td colspan=11 align=center>";
    echo"<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
    echo"<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
    echo"<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
    echo"</td></tr>";
        
    echo"</tbody></table>";
        break;

    default:
        // break;
}

?>