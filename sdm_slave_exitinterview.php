<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');

$departemen = checkPostGet('departemen','');
$nama = checkPostGet('nama','');
$email = checkPostGet('email','');
$nohp = checkPostGet('nohp','');
$perusahaan = checkPostGet('perusahaan','');
$jabatan = checkPostGet('jabatan','');

$peranatasan1 = checkPostGet('peranatasan1','');
$peranatasan2 = checkPostGet('peranatasan2','');
$peranatasan3 = checkPostGet('peranatasan3','');
$peranatasan4 = checkPostGet('peranatasan4','');
$peranatasan5 = checkPostGet('peranatasan5','');
$peranatasan6 = checkPostGet('peranatasan6','');
$peranatasan7 = checkPostGet('peranatasan7','');
$peranatasan8 = checkPostGet('peranatasan8','');
$peranatasan9 = checkPostGet('peranatasan9','');
$peranatasan10 = checkPostGet('peranatasan10','');
$peranatasan11 = checkPostGet('peranatasan11','');

$alasan1 = checkPostGet('alasan1','');
$alasan2 = checkPostGet('alasan2','');
$alasan3 = checkPostGet('alasan3','');
$alasan4 = checkPostGet('alasan4','');
$alasan5 = checkPostGet('alasan5','');
$alasan6 = checkPostGet('alasan6','');
$alasan7 = checkPostGet('alasan7','');
$alasan8 = checkPostGet('alasan8','');
$alasan9 = checkPostGet('alasan9','');
$alasan10 = checkPostGet('alasan10','');
$alasan11 = checkPostGet('alasan11','');
$alasan12 = checkPostGet('alasan12','');
$alasan13 = checkPostGet('alasan13','');
$alasan14 = checkPostGet('alasan14','');
$alasan15 = checkPostGet('alasan15','');
$alasan16 = checkPostGet('alasan16','');
$alasan17 = checkPostGet('alasan17','');

$promosi=checkPostGet('promosi','');
    $jarak=checkPostGet('jarak','');
    $jamkerja=checkPostGet('jamkerja','');
    $benefit=checkPostGet('benefit','');
    $gajibaik=checkPostGet('gajibaik','');
    $perubahan=checkPostGet('perubahan','');

$penilaian1 = checkPostGet('penilaian1','');
$penilaian2 = checkPostGet('penilaian2','');
$penilaian3 = checkPostGet('penilaian3','');
$penilaian4 = checkPostGet('penilaian4','');
$penilaian5 = checkPostGet('penilaian5','');
$penilaian6 = checkPostGet('penilaian6','');
$penilaian7 = checkPostGet('penilaian7','');
$penilaian8 = checkPostGet('penilaian8','');

$pendapat = checkPostGet('pendapat','');
$alasankeluar = checkPostGet('alasankeluar','');

$gaji1 = checkPostGet('gaji1','');
$gaji2 = checkPostGet('gaji2','');
$gaji3 = checkPostGet('gaji3','');
$gaji4 = checkPostGet('gaji4','');
// $gaji5 = checkPostGet('gaji5','');
// $gaji6 = checkPostGet('gaji6','');

$umpanbalik = checkPostGet('umpanbalik','');
$diskusi = checkPostGet('diskusi','');
$minat = checkPostGet('minat','');
$suka = checkPostGet('suka','');
$kurangsuka = checkPostGet('kurangsuka','');
$kemajuan = checkPostGet('kemajuan','');
$komentar = checkPostGet('komentar','');
$invent1 = checkPostGet('invent1','');
$invent2 = checkPostGet('invent2','');
$invent3 = checkPostGet('invent3','');
$invent4 = checkPostGet('invent4','');
$keterangan = checkPostGet('keterangan','');

$yaapa = checkPostGet('yaapa','');
$komenlain = checkPostGet('komenlain','');

$baik1 = checkPostGet('baik1','');
$baik2 = checkPostGet('baik2','');
$baik3 = checkPostGet('baik3','');
$baik4 = checkPostGet('baik4','');
$baik5 = checkPostGet('baik5','');
$baik6 = checkPostGet('baik6','');
$baik7 = checkPostGet('baik7','');
$baik8 = checkPostGet('baik8','');
$baik9 = checkPostGet('baik9','');
$baik10 = checkPostGet('baik10','');
$baik11 = checkPostGet('baik11','');
$baik12 = checkPostGet('baik12','');
$baik13 = checkPostGet('baik13','');
$baik14 = checkPostGet('baik14','');
$baik15 = checkPostGet('baik15','');
$baik16 = checkPostGet('baik16','');
$baik17 = checkPostGet('baik17','');
$baik18 = checkPostGet('baik18','');
$baik19 = checkPostGet('baik19','');
$baik20 = checkPostGet('baik20','');
$baik21 = checkPostGet('baik21','');
$baik22 = checkPostGet('baik22','');
$baik23 = checkPostGet('baik23','');
$baik24 = checkPostGet('baik24','');

$cukup1 = checkPostGet('cukup1','');
$cukup2 = checkPostGet('cukup2','');
$cukup3 = checkPostGet('cukup3','');
$cukup4 = checkPostGet('cukup4','');
$cukup5 = checkPostGet('cukup5','');
$cukup6 = checkPostGet('cukup6','');
$cukup7 = checkPostGet('cukup7','');
$cukup8 = checkPostGet('cukup8','');
$cukup9 = checkPostGet('cukup9','');
$cukup10 = checkPostGet('cukup10','');
$cukup11 = checkPostGet('cukup11','');
$cukup12 = checkPostGet('cukup12','');
$cukup13 = checkPostGet('cukup13','');
$cukup14 = checkPostGet('cukup14','');
$cukup15 = checkPostGet('cukup15','');
$cukup16 = checkPostGet('cukup16','');
$cukup17 = checkPostGet('cukup17','');
$cukup18 = checkPostGet('cukup18','');
$cukup19 = checkPostGet('cukup19','');
$cukup20 = checkPostGet('cukup20','');
$cukup21 = checkPostGet('cukup21','');
$cukup22 = checkPostGet('cukup22','');
$cukup23 = checkPostGet('cukup23','');
$cukup24 = checkPostGet('cukup24','');

$kurang1 = checkPostGet('kurang1','');
$kurang2 = checkPostGet('kurang2','');
$kurang3 = checkPostGet('kurang3','');
$kurang4 = checkPostGet('kurang4','');
$kurang5 = checkPostGet('kurang5','');
$kurang6 = checkPostGet('kurang6','');
$kurang7 = checkPostGet('kurang7','');
$kurang8 = checkPostGet('kurang8','');
$kurang9 = checkPostGet('kurang9','');
$kurang10 = checkPostGet('kurang10','');
$kurang11 = checkPostGet('kurang11','');
$kurang12 = checkPostGet('kurang12','');
$kurang13 = checkPostGet('kurang13','');
$kurang14 = checkPostGet('kurang14','');
$kurang15 = checkPostGet('kurang15','');
$kurang16 = checkPostGet('kurang16','');
$kurang17 = checkPostGet('kurang17','');
$kurang18 = checkPostGet('kurang18','');
$kurang19 = checkPostGet('kurang19','');
$kurang20 = checkPostGet('kurang20','');
$kurang21 = checkPostGet('kurang21','');
$kurang22 = checkPostGet('kurang22','');
$kurang23 = checkPostGet('kurang23','');
$kurang24 = checkPostGet('kurang24','');


$method = checkPostGet('method','');

$pages = checkPostGet('page', '');



$txt_search = checkPostGet('txtsearch', '');
$txtNoakun = checkPostGet('txtNoakun', '');
// bikin baru lagi pake array untuk load data yg checkbox
$strnama = array ("0"=>"Kelompok","1"=>"Detail");
$strnama1 = array ("0"=>"Bukan Kasbank","1"=>"Kasbank");

$kodekegiatan = checkPostGet('kodekegiatan', '');
$kodeasset = checkPostGet('kodeasset', '');
$nik = checkPostGet('nik', '');
$kodecustomer = checkPostGet('kodecustomer', '');
$kodesupplier = checkPostGet('kodesupplier', '');
$kodevhc = checkPostGet('kodevhc', '');
$kodeblok = checkPostGet('kodeblok', '');


switch ($method) {

       case'getData':
        ##bentuk tanggal kemarin
        $tglmasuk =  tanggalsystem($_POST['tanggal']);
        $tglKmrn = strtotime('-1 day',strtotime($tgl));
        $tglmasuk = date('Y-m-d', $tglmasuk);
        $tglkeluar = date('Y-m-d', $tglkeluar);


        $str="select * from ".$dbname.".datakaryawan where karyawanid='".$_POST['nama']."'";
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            // $departemen=$bar['bagian'];
            $email=$bar['email'];
            // $jabatan=$bar['kodejabatan'];
            $nohp=$bar['nohp'];
            $perusahaan=$bar['kodeorganisasi'];
            $tglmasuk=$bar['tanggalmasuk'];
            $tglkeluar=$bar['tanggalkeluar'];

             //tampilkan jabatan
             $str="select namajabatan from ".$dbname.".sdm_5jabatan a join datakaryawan b on a.kodejabatan = b.kodejabatan where b.karyawanid='".$_POST['nama']."'";
             // exit('error'.$str);
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $jabatan=$bar['namajabatan'];

            //tampilkan departemen
            $str="select nama from ".$dbname.".sdm_5departemen a join datakaryawan b on a.kode = b.bagian where b.karyawanid='".$_POST['nama']."'";
             // exit('error'.$str);
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
            $departemen=$bar['nama'];
            
        
        echo $departemen."###".tanggalnormal($tglmasuk)."###".$jabatan."###".$email."###".$nohp."###".tanggalnormal($tglkeluar)."###".$perusahaan;
            
       
    break;

    case 'insert':
    // alert('warning : masukk');
    // echo "warning";

        $input = "insert into " . $dbname . ".sdm_exitinterview (karyawanid,alasankeluar,peranatasan1,peranatasan2,peranatasan3,peranatasan4, peranatasan5,peranatasan6,peranatasan7,peranatasan8,peranatasan9,peranatasan10,peranatasan11,penilaian1,penilaian2,penilaian3,penilaian4,penilaian5,penilaian6,penilaian7,penilaian8,pendapat,gaji1,gaji2,gaji3,gaji4,gaji5,gaji6,umpanbalik,diskusi,minat,suka,kurangsuka,kemajuan,komentar,invent1,invent2,invent3,invent4,keterangan)
            values ('" . $nama . "','" . $alasankeluar . "','" . $peranatasan1 . "','" . $peranatasan2 . "','" . $peranatasan3 . "','" . $peranatasan4 . "','" . $peranatasan5 . "','" . $peranatasan6 . "','" . $peranatasan7 . "','" . $peranatasan8 . "','" . $peranatasan9 . "','" . $peranatasan10 . "','" . $peranatasan11 . "','" . $penilaian1 . "','" . $penilaian2 . "','" . $penilaian3 . "','" . $penilaian4 . "','" . $penilaian5 . "','" . $penilaian6 . "','" . $penilaian7 . "','" . $penilaian8 . "','" . $pendapat . "','" . $gaji1 . "','" . $gaji2 . "','" . $gaji3 . "','" . $gaji4 . "','" . $yaapa . "','" . $komenlain . "','" . $umpanbalik . "','" . $diskusi . "','" . $minat . "','" . $suka . "','" . $kurangsuka . "','" . $kemajuan . "','" . $komentar . "','" . $invent1 . "','" . $invent2 . "','" . $invent3 . "','" . $invent4 . "','" . $keterangan . "')";
            // exit('error: '.$input);
    try{
      $owlPDO->exec($input); 
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
        break;



    case 'update':
		$fieldaktif=$kasbank1.$tagihan.$jurnal;
		$fieldaktif.=$kodekegiatan.$kodeasset.$nik.$kodecustomer.$kodesupplier.$kodevhc.$kodeblok;
    // exit ('error:a');
        $input = "update " . $dbname . ".keu_5akun set noakun='" . $noakun . "',namaakun='" . $namaakun . "',namaakun1='" . $namaakun1 . "',
			tipeakun='" . $tipeakun . "',level='" . $level . "',matauang='" . $matauang . "',detail='" . $detail . "',kasbank='" . $kasbank . "',
			fieldaktif='" . $fieldaktif . "',pemilik='" . $namapemilik . "'
             where noakun='" . $noakun . "' and kodeorg='" . $kodeorg."'";
              // exit('error:'.$input);
        try{
      $owlPDO->exec($input); 
    }catch(PDOException $e){
      echo " Gagal," . addslashes($e->getMessage());
    }
            // fdfdfdfdfdf
        break;

      case'cariBarangDlmDtBs':
    $txtfind=$_POST['txtfind'];
        //exit('warning : '.$txtfind);
    $str="select * from ".$dbname.".keu_5akun where namaakun like '%".$txtfind."%'";
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
            <td align=center>".$_SESSION['lang']['noakun']."</td>
            <td align=center>".$_SESSION['lang']['namaakun']."</td>
           
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
            <td align=left>".$bar->noakun."</td>
            <td align=left>".$bar->namaakun."</td>
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
    $str="select * from ".$dbname.".keu_5akun where noakun like '%".$txtfind."%'";
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
            <td align=center>".$_SESSION['lang']['noakun']."</td>
            <td align=center>".$_SESSION['lang']['namaakun']."</td>
           
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
            <td align=left>".$bar->noakun."</td>
            <td align=left>".$bar->namaakun."</td>
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

  case 'delData':
        $i="delete from ".$dbname.".sdm_exitinterview where karyawanid='" . $nama . "'";
        try{
            $owlPDO->exec($i); 
        }catch(PDOException $e){
           echo " Gagal," . addslashes($e->getMessage());
        }
        break;


    //perhatikan load data
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
       <td align=center>" .$_SESSION['lang']['nourut']."</td>
         <td align=center>" .$_SESSION['lang']['id']." ".$_SESSION['lang']['karyawan']. "</td>
         <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
         <td align=center>" . $_SESSION['lang']['alasankeluar']."</td>
         <td align=center>" .$_SESSION['lang']['pendapat']. "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
    </thead>
    <tbody>";


        // $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_hutangbank order by kodeorg asc ".$where.""; 
       $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_exitinterview order by karyawanid asc ".$where.""; 

       $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        // exit ('error : '.$jlhbrs);
        $tab='';
  $nor=0;

        $i = "select * from " . $dbname . ".sdm_exitinterview order by karyawanid asc ".$where." limit " . $offset . "," . $limit . "";
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
            echo"<td align=left>" . $d['karyawanid'] . "</td>";
            echo "<td align=left>" . (isset($nmKar[$d['karyawanid']]) ? $nmKar[$d['karyawanid']] : '') . "</td>";
            echo"<td align=left>" . $d['alasankeluar'] . "</td>";
            echo"<td align=left>" . $d['pendapat'] . "</td>";
            // echo"<td align=left>" . $d['namaharta'] . "</td>";
            // echo"<td align=left>" . $d['jumlah_bulan'] . "</td>";
            // echo "<td align=left>" . $strnama[$d['status']]."</td>";
            // echo "<td align=left>" . (isset($nmKar[$d['createdby']]) ? $nmKar[$d['createdby']] : '') . "</td>";
            // echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
            
            echo"<td align=center>";
            echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$d['karyawanid']."');\">";
                          
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
