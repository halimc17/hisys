<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');

$noarus = checkPostGet('noarus','');
$namaarus = checkPostGet('namaarus','');
$tipetrans = checkPostGet('tipetrans','');
$noinduk = checkPostGet('noinduk','');
$noarus_detail = checkPostGet('noarus_detail','');
$noakundt = checkPostGet('noakundt','');
$pemilik = checkPostGet('pemilik','');
$status1 = checkPostGet('status1','');

$no_arus = checkPostGet('no_arus','');
$nama_arus = checkPostGet('nama_arus','');
$tipe_trans = checkPostGet('tipe_trans','');
$pemilik2 = checkPostGet('pemilik2','');
$status2 = checkPostGet('status2','');

$strnama = array ("0"=>"tidak aktif","1"=>"aktif");
$strtipe = array ("M"=>"Masuk","K"=>"Keluar");
$nomorarus = checkPostGet('nomorarus','');
$noakun = checkPostGet('noakun','');
$noakun_detail = checkPostGet('noakun_detail','');
$pages = checkPostGet('page', '');
$method = checkPostGet('method','');
$level = checkPostGet('level','');
$tpExpns = checkPostGet('tpExpns','');
$aksesRek = checkPostGet('aksesRek','');
$indukkas = checkPostGet('indukkas','');
$checkakun = checkPostGet('checkakun','');
$strx = "";
$data = array();
$data['error'] = 'false';
$param=$_POST;
switch ($method) {

    case 'forminduk':

        $optinduk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select noaruskas,nama_aruskas from keu_5aruskas where level='2' and tipetransaksi='".$tipetrans."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()){
            $optinduk.="<option value='".$bar['noaruskas']."'>".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
        }

        echo $optinduk;

    break;
    
    case 'insert':

        $str="select count(noaruskas) as jlhbrs from keu_5aruskas where level='1'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $jlhbrs=$bar['jlhbrs'];

        if ($jlhbrs==0){

            if ($level==2 || $level==3) {
              exit('warning : noaruskas level 1 harus disimpan terlebih dahulu.');
            }

            $noaruskas=10000;

        } else {

            $str="select noaruskas from keu_5aruskas where level=1 and tipetransaksi='".$tipetrans."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $noaruskas=$bar['noaruskas'];

            if ($noaruskas==''){
                if ($level==2 || $level==3) {
                  exit('warning : noaruskas level 1 harus disimpan terlebih dahulu.');
                }
            }

            if ($level==1){
                if ($noaruskas!=''){
                    exit('warning : noaruskas dengan tipe '.$strtipe[$tipetrans].' sudah ada.');
                }else{
                    $nourut=10000*$jlhbrs;
                    $noaruskas=10000+$nourut;
                }
            }else{

                if ($level==2) {
                  $whr=" and left(noaruskas,1)='".substr($noaruskas,0,1)."'";
                }

                if ($level==3) {
                  $whr=" and left(noaruskas,3)='".substr($indukkas,0,3)."'";
                }

                $str="select noaruskas from keu_5aruskas where level='".$level."' and tipetransaksi='".$tipetrans."' ".$whr." order by noaruskas desc";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
                $noaruskasdt=$bar['noaruskas'];

                if ($level==2) {
                    if ($noaruskasdt==''){
                        $noaruskas=$noaruskas+100;
                    }else{
                        $noaruskas=$noaruskasdt+100;
                    }
                }

                if ($level==3) {

                    if ($indukkas=='') {
                      exit('warning : noaruskas level 2 harus disimpan terlebih dahulu.');
                    }

                    if ($noaruskasdt==''){
                        $noaruskas=$indukkas+1;
                    }else{
                        $noaruskas=$noaruskasdt+1;
                    }
                }
            }

        }

        $str = "insert into ".$dbname.".keu_5aruskas (noaruskas,pemilik_aruskas,tipetransaksi,nama_aruskas,akses_rekening,createdby,status,level,induk,jenis_pengeluaran)
                values ('".$noaruskas."','".$pemilik."','". $tipetrans . "','" . $namaarus . "','" . $aksesRek . "','" . $_SESSION['standard']['userid'] . "','" . $status1 . "','" . $level . "','".$indukkas."','".$tpExpns."')";
        try{
          $owlPDO->exec($str); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    //insert DETAIL ARUS KAS
    case 'insertDetail':
			
        $query="select max(substr(noaruskas,-2)) as noaruskas from keu_5aruskas where induk='".$noinduk."' and induk != '' order by noaruskas desc limit 1";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
		
    		if($rp['noaruskas']==99){
    			exit('error : Jumlah detail sudah maksimal.');
    		}
	  
	  
        if($rp['noaruskas']==''){
            $no_arus =$noinduk.'01';
        }else if($rp['noaruskas']!= ''){
          $no_arus = addZero($rp['noaruskas']+1,2);
        }
        $no_arus=$noinduk."".$no_arus;

        $str = "insert into " . $dbname . ".keu_5aruskas (induk,noaruskas,pemilik_aruskas,tipetransaksi,nama_aruskas,createdby,status,jenis_pengeluaran)
            values ('" . $noinduk . "','" . $no_arus . "','" . $pemilik2 . "','" . $tipe_trans . "','" . $nama_arus . "','" . $_SESSION['standard']['userid'] . "','" . $status2 . "','".$tpExpns."')";
        try{
          $owlPDO->exec($str); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    //insert ARUS KAS DETAIL NO AKUN
	case'simpandata':
		foreach($param['noakundt'] as $key => $akundt){
			$sDet="insert into ".$dbname.".keu_5aruskas_detail (noaruskas,noakun,createdby,updateby) 
			values ('".$nomorarus."','".$akundt."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
			try{$owlPDO->exec($sDet); }catch (PDOException $e){echo " Gagal ".addslashes($e->getMessage()."__".$sDet);}
		}
		// echo "<pre>";
		// echo $sDet;
		
		// print_r($param['noakundt']);
		// exit("error");
		
	break;
    case 'insertAkun':
	
		if($checkakun==1){
			$sDet="insert into ".$dbname.".keu_5aruskas_detail (noaruskas,noakun,createdby,updateby) 
			values ('".$nomorarus."','".$noakundt."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
			try{ 
			  $owlPDO->exec($sDet); 
			}catch (PDOException $e){
			  echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
			}
		}
		
	
		// for($arDt=1;$arDt<=$_POST['totrow'];$arDt++){
		  // if ($_POST['checkakun'][$arDt]==1){
			// $sDet="insert into ".$dbname.".keu_5aruskas_detail (noaruskas,noakun,createdby,updateby) 
			// values ('".$nomorarus."','".$_POST['noakundt'][$arDt]."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
			// exit("Error:".$sDet);
			// try{ 
			  // $owlPDO->exec($sDet); 
			// }catch (PDOException $e){
			  // echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
			// }
		  // }
		// }
		/*
		*/
		
    break;

    //Update ARUS KAS
    case 'update':
 
        $str = "update " . $dbname . ".keu_5aruskas set pemilik_aruskas='" . $pemilik . "',tipetransaksi='" . $tipetrans . "',nama_aruskas='" . $namaarus . "',akses_rekening='" . $aksesRek . "',". " updateby='" . $_SESSION['standard']['userid'] . "',status='" . $status1 . "',jenis_pengeluaran='" . $tpExpns . "'
             where noaruskas='" . $noarus . "'";
        try{
          $owlPDO->exec($str); 
        }catch(PDOException $e){
          echo " Gagal," . addslashes($e->getMessage());
        }
            
    break;

    //UPDATE DETAIL ARUS KAS
    case 'updateDetail':

        $str = "update " . $dbname . ".keu_5aruskas set induk='" . $noinduk . "', pemilik_aruskas='" . $pemilik2 . "',tipetransaksi='" . $tipe_trans . "',nama_aruskas='" . $nama_arus . "',". " updateby='" . $_SESSION['standard']['userid'] . "',status='" . $status2 . "',jenis_pengeluaran='" . $tpExpns . "'
             where noaruskas='" . $no_arus . "'";
        
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        
    break;

    //UPDATE DETAIL ARUS KAS NO AKUN
    case 'updateAkun':

        $str = "update " . $dbname . ".keu_5aruskas_detail set noakun='" . $noakun . "',". " updateby='" . $_SESSION['standard']['userid'] . "'
             where noaruskas='" . $nomorarus . "' and noakun='" . $noakun . "' ";
        
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        
    break;

    //LOAD DATA ARUS KAS
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
        
        echo"
        <table class=sortable cellpadding=5 cellspacing=1 border=0>
        <thead>
           <tr class=rowheader>
             <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
             <th align=center>" . $_SESSION['lang']['level'] . "</th>
             <th align=center width=80px>" . $_SESSION['lang']['nomor']." " . $_SESSION['lang']['aruskas']."</th>
             <th align=center>" .$_SESSION['lang']['nama']. " " .$_SESSION['lang']['aruskas']. "</th>
             <th align=center> Akses ".$_SESSION['lang']['rekening']."</th>
             <th align=center>" .$_SESSION['lang']['tipetransaksi']. "</th>
             <th align=center>" .$_SESSION['lang']['pemilik']." </th>
             <th align=center>" .$_SESSION['lang']['status']. "</th>
             <th  colspan=2 align=center>" . $_SESSION['lang']['action'] . "</th>
        </thead>
        <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_5aruskas where 1=1 order by noaruskas asc ".$where.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $tab='';
        $nor=0;

        $str = "select * from " . $dbname . ".keu_5aruskas where 1=1 order by noaruskas asc ".$where." ";//limit " . $offset . "," . $limit . "
        $n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {
            $no+=1;$bold="";
			if ($d['level']!=3){
				$bold="style=font-weight:bold;background-color:grey;";
			}
            echo"<tr class=rowcontent ".$bold.">";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=center>" . $d['level'] . "</td>";
            echo"<td align=left>" . $d['noaruskas'] . "</td>";
            echo"<td align=left>" . $d['nama_aruskas'] . "</td>";
            echo"<td align=left width=50px>" . $d['akses_rekening'] . "</td>";
            echo "<td align=left>" . $strtipe[$d['tipetransaksi']]."</td>";
            echo"<td align=left>" . $d['pemilik_aruskas'] . "</td>";
            echo "<td align=left>" . $strnama[$d['status']]."</td>";
            echo "<td align=center width=30px>
                    <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['noaruskas'] . "','" . $d['nama_aruskas'] . "','" . $d['tipetransaksi'] . "','" . $d['pemilik_aruskas'] . "','" . $d['status'] . "','".$d['level']."','".$d['jenis_pengeluaran']."','".$d['akses_rekening']."');\">
                  </td>";
            // echo "<td>
            //         <img src=images/addplus.png class=resicon  title='Add Detail' onclick=\"detaildt('" . $d['noaruskas'] . "');\">
            //       </td>";
            if ($d['level']==3){
              echo "<td  width=30px align=center>
                    <img src=images/addplus.png class=resicon  title='Add Detail Noakun' onclick=\"detailAkun('".$d['noaruskas']."');\">
                  </td>";
            }else{
              echo "<td width='30px'></td>";
            }
            echo"</tr>"; 
        }
            //#bikin tombol untuk pagingnya
            //     $totrows=ceil($jlhbrs/$limit);
            // if($totrows==0)
            // {
            //   $totrows=1;
            // }
            
            // $strsiRow='';
            // for($er=1;$er<=$totrows;$er++)
            // {
            //   $sel = ($page==$er-1)? 'selected': '';
            //   $strsiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            // }

            // echo"<tr><td colspan=8 align=center>";
            // echo"<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
            // echo"<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage(this.value)\">".$strsiRow."</select>";
            // echo"<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
            // echo"</td></tr>";
            
        echo"</tbody></table>";
    break;

    ////////////////LOAD DATA DETAIL ARUS KAS
    case'loadDataDetail':

        echo"
        <table class=sortable cellpadding=5 cellspacing=1 border=0 style='width:100%'>
        <thead>
           <tr class=rowheader>
             <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
             <th align=center width=80px>" . $_SESSION['lang']['nomor']." " . $_SESSION['lang']['induk']." " . $_SESSION['lang']['aruskas']."</th>
             <th align=center>" . $_SESSION['lang']['nomor']." Detail " . $_SESSION['lang']['aruskas']."</th>
             <th align=center>" .$_SESSION['lang']['nama']. " Detail " .$_SESSION['lang']['aruskas']. "</th>
             <th align=center>" .$_SESSION['lang']['tipetransaksi']. "</th>
             <th align=center>" .$_SESSION['lang']['pemilik']." </th>
             <th align=center>" .$_SESSION['lang']['status']. "</th>
             <th align=center>" . $_SESSION['lang']['action'] . "</th>
        </thead>
        <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_5aruskas where induk != '' and noaruskas like '".$noarus_detail."%'";  

        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $tab='';
        $nor=0;

        $str = "select * from " . $dbname . ".keu_5aruskas where induk != '' and noaruskas like '".$noarus_detail."%' order by noaruskas asc";
        $n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo"<td align=left>" . $d['induk'] . "</td>";
            echo"<td align=left>" . $d['noaruskas'] . "</td>";
            echo"<td align=left>" . $d['nama_aruskas'] . "</td>";
            echo "<td align=left>" . $strtipe[$d['tipetransaksi']]."</td>";
            echo"<td align=left>" . $d['pemilik_aruskas'] . "</td>";
            echo "<td align=left>" . $strnama[$d['status']]."</td>";
            echo"<td align=center>
                    <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editDetail('" . $d['induk'] . "','" . $d['noaruskas'] . "','" . $d['nama_aruskas'] . "','" . $d['tipetransaksi'] . "','" . $d['pemilik_aruskas'] . "','" . $d['status'] . "');\">";
            echo"</tr>"; 
        }
            
        echo"</tbody></table>";
    break;


    //LOAD DATA DETAIL NO AKUN
    case'loadDataAkun':

        echo"
        <table class=sortable cellpadding=5 cellspacing=1 border=0 style='width:100%;overflow:auto;'>
        <thead>
          <tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center>" . $_SESSION['lang']['noaruskas']."</th>
            <th align=center>" . $_SESSION['lang']['noakun']."</th>
            <th align=center>" . $_SESSION['lang']['action'] . "</th>
          </tr>
        </thead>
        <tbody>";

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_5aruskas_detail where noaruskas like '".$noakun_detail."%'";    
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
          $jlhbrs = $jsl->jmlhrow;
        }
        $tab='';
        $nor=0;

        $str = "select * from " . $dbname . ".keu_5aruskas_detail where noaruskas like '".$noakun_detail."%'";
        $n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {
            $no+=1;

            $whrnm="noakun='".$d['noakun']."'";
            $nmakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun',$whrnm);
            $whr="noaruskas='".$d['noaruskas']."'";
            $nmarus=  makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas',$whr);
            echo"<tr class=rowcontent>";
            echo "<td align=center>".$no."</td>";
            echo"<td align=left>".$d['noaruskas']." - ".$nmarus[$d['noaruskas']]."</td>";
            echo"<td align=left>".$d['noakun']." - ".(isset($nmakun[$d['noakun']]) ? $nmakun[$d['noakun']] : '')."</td>";
            echo"<td align=center width=25px>
              <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDataakun('".$d['noaruskas']."','".$d['noakun']."');\"></td>";
            echo"</tr>"; 
        }
            
        echo"</tbody></table></fieldset>";
    break;

    case'deleteakundt':

        $strht = "delete from " . $dbname . ".keu_5aruskas_detail where noaruskas='" . $noakun_detail . "' and noakun='".$noakundt."'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

}

?>