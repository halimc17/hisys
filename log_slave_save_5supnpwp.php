<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$supplierid = checkPostGet('supplierid', '');
$npwp = checkPostGet('npwp', '');
$namanpwp = checkPostGet('namanpwp', '');
$jalan = checkPostGet('jalan', '');
$blok = checkPostGet('blok', '');
$nomor = checkPostGet('nomor', '');
$rt = checkPostGet('rt', '');
$rw = checkPostGet('rw', '');
$kecamatan = checkPostGet('kecamatan', '');
$kelurahan = checkPostGet('kelurahan', '');
$kabupaten = checkPostGet('kabupaten', '');
$propinsi = checkPostGet('propinsi', '');
$kodepos = checkPostGet('kodepos', '');
$telp_no = checkPostGet('telp_no', '');
$aktif = checkPostGet('aktif', '');
$method = checkPostGet('method', '');
$id = checkPostGet('id', '');
$namafile = checkPostGet('namafile', '');
// $nopp = checkPostGet('rnopp','');
// bikin baru lagi pake array untuk load data yg checkbox
$strnama = array("0" => "tidak aktif", "1" => "aktif");
$strnamaper = array("0" => "Proses persetujuan", "1" => "Disetujui", "2" => "Ditolak");
$jnsapp = "DS";

// exit('warning : '.$method);

switch ($method) {

  case 'insert':
    // exit ('error:a');
    $input = "insert into " . $dbname . ".log_5supnpwp (supplierid,npwp,nama_npwp,jalan,blok,nomor,rt,rw,keluarahan,kecamatan,kabupaten,propinsi,kodepos,telp_no,updateby,active,statusyangdiinginkan,statuspersetujuan)
            values ('" . $supplierid . "','" . $npwp . "','" . $namanpwp . "','" . $jalan . "','" . $blok . "','" . $nomor . "','" . $rt . "','" . $rw . "','" . $kelurahan . "','" . $kecamatan . "','" . $kabupaten . "','" . $propinsi . "','" . $kodepos . "','" . $telp_no . "','" . $_SESSION['standard']['userid'] . "','0','" . $aktif . "','0')";
    try {
      $owlPDO->exec($input);
      $strx = "delete from " . $dbname . ".approval where notransaksi='" . $supplierid . "' and jenispersetujuan='" . $jnsapp . "'";
      try {
        $owlPDO->exec($strx);
        $listpersetujuan = $_POST['persetujuan'];
        foreach ($listpersetujuan as $key => $val) {
          $str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $supplierid . "','" . $jnsapp . "','" . $key . "','" . $listpersetujuan[$key] . "','0')";
          try {
            $owlPDO->exec($str);
          } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
          }
        }
      } catch (PDOException $e) {
        echo " Gagal," . addslashes($e->getMessage());
      }
    } catch (PDOException $e) {
      echo " Gagal," . addslashes($e->getMessage());
    }
    break;

  case 'update':

    $strx = selectQuery($dbname, "log_5supnpwp", "*", "supplierid='" . $supplierid . "'  and npwp='" . $npwp . "'");
    $resx = fetchData($strx);
    $oldx['supplierid'] = $resx[0]['supplierid'];
    $oldx['npwp'] = $resx[0]['npwp'];
    $oldx['nama_npwp'] = $resx[0]['nama_npwp'];
    $oldx['jalan'] = $resx[0]['jalan'];
    $oldx['blok'] = $resx[0]['blok'];
    $oldx['nomor'] = $resx[0]['nomor'];
    $oldx['rt'] = $resx[0]['rt'];
    $oldx['rw'] = $resx[0]['rw'];
    $oldx['keluarahan'] = $resx[0]['keluarahan'];
    $oldx['kecamatan'] = $resx[0]['kecamatan'];
    $oldx['kabupaten'] = $resx[0]['kabupaten'];
    $oldx['propinsi'] = $resx[0]['propinsi'];
    $oldx['kodepos'] = $resx[0]['kodepos'];
    $oldx['telp_no'] = $resx[0]['telp_no'];
    $oldx['active'] = $resx[0]['active'];
    $perubahanx = $resx[0]['perubahan'];

    $textubah = $oldx['supplierid'] . "##" . $oldx['npwp'] . "##" . $oldx['nama_npwp'] . "##" . $oldx['jalan'] . "##" . $oldx['blok'] . "##" . $oldx['nomor'] . "##" . $oldx['rt'] . "##" . $oldx['rw'] . "##" . $oldx['keluarahan'] . "##" . $oldx['kecamatan'] . "##" . $oldx['kabupaten'] . "##" . $oldx['propinsi'] . "##" . $oldx['kodepos'] . "##" . $oldx['telp_no'] . "##" . $oldx['active'];



    $input = "update " . $dbname . ".log_5supnpwp set nama_npwp='" . $namanpwp . "', jalan='" . $jalan . "',blok='" . $blok . "',nomor='" . $nomor . "',rt='" . $rt . "',rw='" . $rw . "',keluarahan='" . $kelurahan . "',kecamatan='" . $kecamatan . "',kabupaten='" . $kabupaten . "',propinsi='" . $propinsi . "',kodepos='" . $kodepos . "',telp_no='" . $telp_no . "'," . " updateby='" . $_SESSION['standard']['userid'] . "',active='0',statusyangdiinginkan='" . $aktif . "',statuspersetujuan='0',perubahan='" . $textubah . "'  
                   where supplierid='" . $supplierid . "' and npwp='" . $npwp . "'";

    if ($perubahanx != '') {
      $arrperub = explode('##', $perubahanx);
      if ($arrperub[0] != '') {
        $input = "update " . $dbname . ".log_5supnpwp set nama_npwp='" . $namanpwp . "', jalan='" . $jalan . "',blok='" . $blok . "',nomor='" . $nomor . "',rt='" . $rt . "',rw='" . $rw . "',keluarahan='" . $kelurahan . "',kecamatan='" . $kecamatan . "',kabupaten='" . $kabupaten . "',propinsi='" . $propinsi . "',kodepos='" . $kodepos . "',telp_no='" . $telp_no . "'," . " updateby='" . $_SESSION['standard']['userid'] . "',active='0',statusyangdiinginkan='" . $aktif . "',statuspersetujuan='0'   
                   where supplierid='" . $supplierid . "' and npwp='" . $npwp . "'";
      }
    }

    try {
      $owlPDO->exec($input);
      $strx = "delete from " . $dbname . ".approval where notransaksi='" . $supplierid . "' and jenispersetujuan='" . $jnsapp . "'";
      try {
        $owlPDO->exec($strx);
        $listpersetujuan = $_POST['persetujuan'];
        foreach ($listpersetujuan as $key => $val) {
          $str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $supplierid . "','" . $jnsapp . "','" . $key . "','" . $listpersetujuan[$key] . "','0')";
          try {
            $owlPDO->exec($str);
          } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
          }
        }
      } catch (PDOException $e) {
        echo " Gagal," . addslashes($e->getMessage());
      }
    } catch (PDOException $e) {
      echo " Gagal," . addslashes($e->getMessage());
    }

    break;


  case 'submitfile':
    $tgl = date("YmdHis");
    // exit("error : ".$tgl);
    $data = $_POST;

    if ($data['fileupload'] != '') {
      if ($_FILES['file']['error'] == 0) {
        $filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
        $newfilename = str_replace($filetype, '', $_FILES['file']['name']);
        $filename = "NPWP_" . $newfilename . "_" . $tgl . "" . $filetype;
        $file_tmpname = $_FILES['file']['tmp_name'];

        if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
          if ($_FILES['file']['size'] <= 250000) {
            $str = "insert into " . $dbname . ".listfilesupplier values ('','" . $data['supplierid'] . "','" . $filename . "','" . $filetype . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
            // echo $str;
            // exit('error:'.$str);
            try {
              $path = "supplier/temp/" . $supplierid . "/";
              if (!file_exists($path)) {
                mkdir($path, 777, true) || chmod($path, 777);
              }
              // exit('error: '.$path);

              $owlPDO->exec($str);
              move_uploaded_file($file_tmpname, $path . $filename);
            } catch (PDOException $e) {
              echo " Gagal," . addslashes($e->getMessage());
            }
          } else {
            exit("warning : Ukuran file upload maksimal 250kb");
          }
        } else {
          exit("Warning : Format file upload harus .jpg atau .jpeg");
        }
      }
    }
    break;

  case 'loadfiles':

    $no = 0;
    $tab = "";
    $str = "select * from " . $dbname . ".log_5supplier where supplierid = '" . $supplierid . "'";
    $resv = fetchData($str);
    foreach ($resv as $bar => $barv) {
      $close = $barv['close'];
    }


    $tab .= "<table class='sortable' cellspacing='1' border='0' width=100%>
        <thead>
        <tr class=rowheader>
          <td align='center'>No.</td>
          <td align='center'>File Type</td>
          <td align='center'>Filename</td>
          <td align='center'>Action</td>
        </tr>
        </thead>";

    $str = "select * from " . $dbname . ".listfilesupplier where supplierid = '" . $supplierid . "' and status='1'";
    $res = fetchData($str);
    if (empty($res)) {
      $tab .= "<tr class=rowcontent><td colspan=4 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
    } else {
      foreach ($res as $key => $val) {
        $no++;
        $tab .= "<tr id='ppDetailTable' class=rowcontent>
          <td style='text-align:center'>" . $no . "</td>";

        if ($val['formaticon'] == '.jpeg' || $val['formaticon'] == '.jpg') {
          $tab .= "<td style='text-align:center'>
            <a href='supplier/temp/" . $supplierid . "/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=zImgBtn title='JPG'></a>
          </td>";
        } elseif ($val['formaticon'] == '.png') {
          $tab .= "<td style='text-align:center'>
            <a href='supplier/temp/" . $supplierid . "/" . $val['namafile'] . "' download><img src=images/uploader/png.png class=zImgBtn  title='PNG'></a>
          </td>";
        } elseif ($val['formaticon'] == '.pdf') {
          $tab .= "<td style='text-align:center'>
            <a href='supplier/temp/" . $supplierid . "/" . $val['namafile'] . "' download><img src=images/uploader/pdf.png class=zImgBtn  title='PDF'></a>
          </td>";
        } elseif ($val['formaticon'] == '.xls' || $val['formaticon'] == '.xlsx') {
          $tab .= "<td style='text-align:center'>
            <a href='supplier/temp/" . $supplierid . "/" . $val['namafile'] . "' download><img src=images/uploader/excel.png class=zImgBtn  title='xls'></a>
          </td>";
        } elseif ($val['formaticon'] == '.doc' || $val['formaticon'] == '.docx') {
          $tab .= "<td style='text-align:center'>
            <a href='supplier/temp/" . $supplierid . "/" . $val['namafile'] . "' download><img src=images/uploader/word.png class=zImgBtn  title='doc'></a>
          </td>";
        } else {
          $tab .= "<td style='text-align:center'>
            <a href='supplier/temp/" . $supplierid . "/" . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=zImgBtn  title='jpg'></a>
          </td>";
        }

        $tab .= "<td style='text-align:left'>" . $val['namafile'] . "</td>
          <td align=center>
            <a href='supplier/temp/" . $supplierid . "/" . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp";
        if ($close == 0) {
          $tab .= "<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('" . $val['id'] . "');\" >";
        }
        $tab . "  </td>
        </tr>";
      }
    }
    $tab .= "
       

      </table>";
    echo $tab;
    break;

  case 'deletefile':
    $str = "delete from " . $dbname . ".listfilesupplier where id='" . $id . "'";
    // exit('error :'.$str);
    try {
      $owlPDO->exec($str);
      $path = "fileupload/npwpsupplier/" . $namafile;
      unlink($path);
    } catch (PDOException $e) {
      echo " Gagal," . addslashes($e->getMessage());
    }
    break;


  //perhatikan load data
  case 'loadData':
    // exit('warning masukk')
    echo "
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
         <td align=center>" . $_SESSION['lang']['status'] . "</td>
         <td align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan'] . "</td>
         <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
         <td align=center>" . $_SESSION['lang']['action'] . "</td>
       </tr>
    </thead>
    <tbody>";

    //paging untuk membatyasi data perhalaman
    // $limit = 10;
    // $page = 0;
    // if (isset($_POST['page'])) {
    //     $page = $_POST['page'];
    //     if ($page < 0)
    //         $page = 0;
    // }
    // $offset = $page * $limit;
    // $maxdisplay = ($page * $limit);

    $ql2 = "select count(*) as jmlhrow from " . $dbname . ".log_5supnpwp where supplierid = '" . $supplierid . "'"; // echo $ql2;notran

    $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
    $query2->setFetchMode(PDO::FETCH_OBJ);
    while ($jsl = $query2->fetch()) {
      $jlhbrs = $jsl->jmlhrow;
    }
    $tab = '';
    $nor = 0;

    $input = "select * from " . $dbname . ".log_5supnpwp  where supplierid = '" . $supplierid . "'";
    $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
    // $no = $maxdisplay;
    while ($d = $n->fetch()) {

      $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
      $no += 1;
      $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
      //$no+=1;
      echo "<tr class=rowcontent>";
      echo "<td align=center>" . $no . "</td>";
      echo "<td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>";
      // echo "<td align=left>" . $d['supplierid'] . "</td>";
      echo "<td align=left>" . $d['npwp'] . "</td>";
      echo "<td align=left>" . $d['nama_npwp'] . "</td>";
      echo "<td align=left>" . $d['jalan'] . "</td>";
      echo "<td align=left>" . $d['blok'] . "</td>";
      echo "<td align=left>" . $d['nomor'] . "</td>";
      echo "<td align=left>" . $d['rt'] . "</td>";
      echo "<td align=left>" . $d['rw'] . "</td>";
      echo "<td align=left>" . $d['keluarahan'] . "</td>";
      echo "<td align=left>" . $d['kecamatan'] . "</td>";
      echo "<td align=left>" . $d['kabupaten'] . "</td>";
      echo "<td align=left>" . $d['propinsi'] . "</td>";
      echo "<td align=left>" . $d['kodepos'] . "</td>";
      echo "<td align=left>" . $d['telp_no'] . "</td>";
      echo "<td align=left>" . $strnama[$d['active']] . "</td>";
      echo "<td align=left>" . $strnamaper[$d['statuspersetujuan']] . "</td>";


      echo "<td align=left>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
      //echo "<td align=left>".$d['updatetime']."</td>";
      //echo "<td align=left>" . $d['jalan'] . "</td>";
      echo "<td align=center>
                            <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $d['supplierid'] . "','" . $d['npwp'] . "','" . $d['nama_npwp'] . "','" . $d['jalan'] . "','" . $d['blok'] . "','" . $d['nomor'] . "','" . $d['rt'] . "','" . $d['rw'] . "','" . $d['keluarahan'] . "','" . $d['kecamatan'] . "'," . "'" . $d['kabupaten'] . "','" . $d['propinsi'] . "','" . $d['kodepos'] . "','" . $d['telp_no'] . "','" . $d['active'] . "' );\">
                            </td>";

      echo "</tr>";
      //<img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"del('".$d['supplierid']."');\"> <img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"del('".$d['kode']."');\">
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

    echo "</tbody></table>";
    break;

  default:
}
