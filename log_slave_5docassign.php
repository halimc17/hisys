<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');


$idbar = checkPostGet('idbar','');
$kelompok = checkPostGet('kelompok','');
$subkelompok = checkPostGet('subkelompok','');
$kodebarang = checkPostGet('kodebarang','');
$status1 = checkPostGet('status1','');
$strnama = array ("0"=>"tidak aktif","1"=>"aktif");
$pages = checkPostGet('page', '');
$method = checkPostGet('method','');

$kodesub = checkPostGet('kodesub','');
$kodebar = checkPostGet('kodebar','');

$strx = "";
$data = array();
$data['error'] = 'false';

switch ($method) {

        case 'getkelompok':
        $str = "select kode,namasubkelompok from " . $dbname . ".log_5subklbarang 
        where left(kode,3)='".$kodesub."' order by kode";
        $optkelompok = "<option value='' >" . $_SESSION['lang']['pilihdata'] . "</option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        
        $res->setFetchMode(PDO::FETCH_OBJ);
         
            while ($bar = $res->fetch()) {
              if($subkelompok==$bar->kode){
                $optkelompok.="<option value='" . $bar->kode . "' selected>[" . $bar->kode . "] " . $bar->namasubkelompok . "</option>";
              }else{
                $optkelompok.="<option value='" . $bar->kode . "' >[" . $bar->kode . "] " . $bar->namasubkelompok . "</option>"; 
              }
            }
        echo $optkelompok;
        break;


        case 'getkode':
        $str = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang 
        where left(kodebarang,5)='".$kodebar."' order by kodebarang";
        $optjenis = "<option value='' >" . $_SESSION['lang']['pilihdata'] . "</option>";
         // exit('error: '.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        
        $res->setFetchMode(PDO::FETCH_OBJ);
         
            while ($bar = $res->fetch()) {
              if($kodebarang==$bar->kodebarang){
                $optjenis.="<option value='" . $bar->kodebarang . "' selected>[" . $bar->kodebarang . "] " . $bar->namabarang . "</option>";
              }else{
                $optjenis.="<option value='" . $bar->kodebarang . "' >[" . $bar->kodebarang . "] " . $bar->namabarang . "</option>";
              }
            }
        echo $optjenis;
        break;


        case 'insert':

        $i = "insert into " . $dbname . ".log_5docassign (id,kodebarang,status,createdby)
            values ('','" . $kodebarang . "','" . $status1 . "','" . $_SESSION['standard']['userid'] . "')";
          // exit('error '.$i);  
        try{
          $owlPDO->exec($i); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }
            break;

        case 'update':
 
            $i = "update " . $dbname . ".log_5docassign set kodebarang='" . $kodebarang . "',status='" . $status1 . "',". " updateby='" . $_SESSION['standard']['userid'] . "'
                 where id='" . $idbar . "'";
                 // exit('error:'.$i);
            try{
          $owlPDO->exec($i); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
      }
            
        break;

      //  

case'loadData':

 $limit = 20;
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
    <table class=sortable cellpadding=1 cellspacing=1 border=0 style='min-width:600px'>
                    <thead>
       <tr class=rowheader>
         <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
         <td align=center>" . $_SESSION['lang']['kodebarang']."</td>
         <td align=center>" . $_SESSION['lang']['namabarang']."</td>
         <td align=center>" .$_SESSION['lang']['status']. "</td>
         <td align=center>" . $_SESSION['lang']['dibuat'] . "</td>
         <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
         <td  align=center>" . $_SESSION['lang']['action'] . "</td>
    </thead>
    <tbody>";


        // $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_hutangbank order by kodeorg asc ".$where.""; 
       $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5docassign order by id asc ".$where.""; 

       $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        // exit ('error : '.$jlhbrs);
        $tab='';
  $nor=0;

        $i = "select * from " . $dbname . ".log_5docassign order by id asc ".$where." limit " . $offset . "," . $limit . "";
    $n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {

            $no+=1;
            $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $nmJenishar=  makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
            $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

            
            echo"<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            // echo"<td align=left>" . $d['id_jnsharta'] . "</td>";
            echo "<td align=left>" . (isset($d['kodebarang']) ? $d['kodebarang'] : '') . "</td>";
            echo "<td align=left>" . (isset($nmJenishar[$d['kodebarang']]) ? $nmJenishar[$d['kodebarang']] : '') . "</td>";
            echo "<td align=left>" . $strnama[$d['status']]."</td>";
            echo "<td align=left>" . (isset($nmKar[$d['createdby']]) ? $nmKar[$d['createdby']] : '') . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            
            echo"<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['id'] . "','" . substr($d['kodebarang'],0,3) . "','" . substr($d['kodebarang'],0,5) . "','" . $d['kodebarang'] . "','" . $d['status'] . "');\">";
            // echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$d['id']."');\">";
                          
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

    echo"<tr><td colspan=7 align=center>";
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