<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
error_reporting(0);






    $param = $_POST;
    $persenppn    = checkPostGet('persenppn', '');
    $notransaksi    = checkPostGet('notransaksi', '');
    $pt             = checkPostGet('pt', '');
    $alokasi        = checkPostGet('alokasi', '');
    $tipetbs        = checkPostGet('tipetbs', '');
    $supplier       = checkPostGet('supplier', '');
    $tanggaldari    = tanggalsystemn(checkPostGet('tanggaldari', ''));
    $tanggalsampai  = tanggalsystemn(checkPostGet('tanggalsampai', ''));
    $batasbawah     = checkPostGet('batasbawah', '');
    $batasatas      = checkPostGet('batasatas', '');
    $rpkg           = checkPostGet('rpkg', '');
    $rekening       = checkPostGet('rekening', '');
    $kredit         = checkPostGet('kredit', '');
    $debet          = checkPostGet('debet', '');
    $method         = checkPostGet('method', '');
    $page           = checkPostGet('page', '');
    $status         = checkPostGet('status', '');
    $tanggalpengajuan  = tanggalsystemn(checkPostGet('tanggalpengajuan', ''));
    $periodecopy    = tanggalsystemn(checkPostGet('periodecopy', ''));
	  $nmSup  = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
        $nmSuporg  = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

		$str = "SELECT * FROM ".$dbname.".log_5rekbank";
        $res = fetchdata($str);
        foreach ($res as $bar) {
			$nmrek[$bar['rekening']]=$bar['an'];
        }



switch ($method) {
    case 'getAlokasi':
        $optalokasi = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str = "SELECT namaorganisasi, kodeorganisasi FROM ".$dbname.".organisasi 
                WHERE induk = (SELECT induk FROM ".$dbname.".organisasi WHERE kodeorganisasi = '".$pt."')
                ORDER BY namaorganisasi ASC";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $optalokasi .= "<option value='".$bar['kodeorganisasi']."'>[".$bar['kodeorganisasi']."] ".$bar['namaorganisasi']."</option>";
        }
        echo $optalokasi;
    break;

    case 'getTipe':
		$opttipeTBS = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct tipe from ".$dbname.".pmn_5hargabelitbs where kodeunit='".$pt."' order by tipe asc";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $opttipeTBS .= "<option value='" . $bar['tipe'] . "'>" . $bar['tipe'] . "</option>";
        }
        echo $opttipeTBS;
	break;

    case 'getSup':
      
		$optSup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct supplier from ".$dbname.".pmn_5hargabelitbs where kodeunit='".$pt."' and tipe='".$tipetbs."' order by supplier asc";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            if (strlen($bar['supplier']) > 4) {
                $optSup .= "<option value='" . $bar['supplier'] . "'>" . $bar['supplier'] . " - ".$nmSup[$bar['supplier']]."</option>";
            }else{
                $optSup .= "<option value='" . $bar['supplier'] . "'>" . $bar['supplier'] . " - ".$nmSuporg[$bar['supplier']]."</option>";
            }
        }
        echo $optSup;
	break;

    case 'getRek':
        $optrek = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str = "SELECT DISTINCT rekening, an FROM ".$dbname.".log_5rekbank WHERE supplierid = '".$supplier."' ORDER BY an ASC";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $optrek .= "<option value='".$bar['rekening']."'>(".$bar['rekening'].") ".$bar['an']."</option>";
        }

        echo $optrek;
    break;
    case 'insert':
        if($batasbawah > $batasatas){
            exit("Warning: Batas Bawah harus lebih kecil dari Batas Atas");
        }

        $cekq = "select * from " . $dbname . ".pmn_5feetbs where kodeunit='" . $pt . "' and kodesupplier='" . $supplier . "' and rekening='".$rekening."' and tanggaldari='".$tanggaldari."' and batasbawah='".$batasbawah."'";
        $resq = fetchData($cekq);
        $ct = count($resq);
        if ($ct != 0) {
            exit("Warning Data Sudah Ada untuk ".$pt." ".$nmSup[$supplier]." ".$rekening." !!!!");
        }
		
		#= counter baru karna 1 supplier bisa ada 2 rekening
		$cekq = "select * from " . $dbname . ".pmn_5feetbs where kodeunit='" . $pt . "' and kodesupplier='" . $supplier . "'  and tanggaldari='".$tanggaldari."'";
        $resq = fetchData($cekq);
        $ct = count($resq);

        $notransaksi = str_replace('-', '', $tanggaldari).$pt.$supplier.addZero($ct+1,3);

        $str = "insert into " . $dbname . ".pmn_5feetbs (notransaksi,kodeunit,unitalokasi,tipetbs,kodesupplier,tanggaldari,tanggalsampai,batasbawah,batasatas,rpkg,status,createby,createtime,rekening,noakundebet,noakunkredit,posting,persenppn)
            values ('".$notransaksi."', '" . $pt . "','" . $alokasi . "','".$tipetbs."','" . $supplier . "','" . $tanggaldari . "','" . $tanggalsampai . "','" . $batasbawah . "','" . $batasatas . "','" . $rpkg . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','".$rekening."','".$debet."','".$kredit."','0','".$persenppn."')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal Insert," . addslashes($e->getMessage());
        }
    break;

    case 'update':
        if($batasbawah > $batasatas){
            exit("Warning: Batas Bawah harus lebih kecil dari Batas Atas");
        }
        $str = "update " . $dbname . ".pmn_5feetbs set kodeunit='" . $pt . "',unitalokasi='" . $alokasi . "',tipetbs='".$tipetbs."',kodesupplier='" . $supplier . "',tanggaldari='" . $tanggaldari . "',tanggalsampai='" . $tanggalsampai . "',batasbawah='" . $batasbawah . "',batasatas='" . $batasatas . "',rpkg='" . $rpkg . "', updateby='" . $_SESSION['standard']['userid'] . "', updatetime='" . date('Y-m-d H:i:s') . "', rekening='".$rekening."', noakundebet='".$debet."', noakunkredit='".$kredit."', persenppn='".$persenppn."' where kodeunit='" . $pt . "' and kodesupplier='" . $supplier . "' and rekening='".$rekening."' and notransaksi='".$param['notransaksi']."'";
		
		
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal Update," . addslashes($e->getMessage());
        }

    break;

    case 'loadData':
        $where = '';
        if ($pt != '') {
            $where .= " AND kodeunit = '".$pt."'";
        }
        if ($tipetbs != '') {
            $where .= " AND tipetbs = '".$tipetbs."'";
        }
        if ($supplier != '') {
            $where .= " AND kodesupplier = '".$supplier."'";
        }
        if ($status != '') {
            $where .= " AND posting = '".$status."'";
        }

        echo"
	
		<table cellpadding=5 cellspacing=1 border=0 class=sortable >
		
        <thead>
           <tr class=rowheader>
                <th align=center>No</th>
                <th align=center>" . $_SESSION['lang']['unit'] . " </th>
                <th align=center>" . $_SESSION['lang']['unit'] . " " . $_SESSION['lang']['alokasi'] . "</th>
                <th align=center>" . $_SESSION['lang']['tipe'] . " TBS</th>
                <th align=center>" . $_SESSION['lang']['supplier'] . " </th>
                <th align=center>" . $_SESSION['lang']['periode'] . " </th>
                <th align=center>" . $_SESSION['lang']['batasbawah'] . "<br></th>
                <th align=center>" . $_SESSION['lang']['batasatas'] . "<br></th>
                <th align=center>" . $_SESSION['lang']['rpperkg'] . "</th>
                <th align=center>" . $_SESSION['lang']['rekening'] . "</th>
                <th align=center>" . $_SESSION['lang']['noakundebet'] . "</th>
                <th align=center>" . $_SESSION['lang']['noakunkredit'] . "</th>
                <th align=center>" . $_SESSION['lang']['persenppn'] . "</th>
                <th align=center>" . $_SESSION['lang']['updateby'] . "</th>
                <th align=center colspan=3>" . $_SESSION['lang']['action'] . " </th>
            </tr>
		</thead>
		<tbody>";

        $limit = 20;
        $page = 1;
        $p = new Paging;

        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 1)
                $page = 1;
        }
        // $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $offset = $p->cariPosisi($limit,$page);

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_5feetbs WHERE 1=1 ".$where." ORDER BY tanggaldari DESC"; // echo $ql2;notran
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        $jml = $p->jumlahHalaman($jlhbrs,$limit);
        //  limit " . $offset . "," . $limit . "
        $str = "select * from " . $dbname . ".pmn_5feetbs WHERE 1=1 ".$where." ORDER BY tanggaldari DESC,kodesupplier asc limit ".$offset.",".$limit;
		// echo $i;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $no = $offset+1;
        while ($d = $res->fetch()) {
			$nmkaryawan  = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid in ('".$d['createby']."','".$d['updateby']."')");
			$nmAkun  = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun in ('".$d['noakundebet']."','".$d['noakunkredit']."')");
			
            // $no += 1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left nowrap>" . $d['kodeunit'] . "</td>";
            echo "<td align=left nowrap>" . $d['unitalokasi'] . "</td>";
            echo "<td align=left nowrap>" . $d['tipetbs'] . "</td>";
            if (strlen($d['kodesupplier']) > 4) {
				$nmSup  = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier',"supplierid in ('".$d['kodesupplier']."')");
                echo "<td align=left nowrap>" . $d['kodesupplier'] . " - " . $nmSup[$d['kodesupplier']] . "</td>";
            }else {
				$nmPt   = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
                echo "<td align=left nowrap>" . $d['kodesupplier'] . " - " . $nmPt[$d['kodesupplier']] . "</td>";
            }
            echo "<td align=center>" . tanggalnormal($d['tanggaldari']) . "</td>";
            // echo "<td align=left>" . tanggalnormal($d['tanggalsampai']) . "</td>";
            echo "<td align=right>" . number_format($d['batasbawah']) . "</td>";
            echo "<td align=right>" . number_format($d['batasatas']) . "</td>";
            echo "<td align=right>" . number_format($d['rpkg'],2) . "</td>";
            echo "<td align=left>" . $d['rekening'] . " a/n ".$nmrek[$d['rekening']]."</td>";
            echo "<td align=left>[".$d['noakundebet']."] " . $nmAkun[$d['noakundebet']] . "</td>";
            echo "<td align=left>[".$d['noakunkredit']."] " .  $nmAkun[$d['noakunkredit']] . "</td>";
            echo "<td align=left>".hidezerodecimal($d['persenppn'],2)."</td>";
            if ($d['updateby'] == '0000000000') {
                $latupdate = $nmkaryawan[$d['createby']];
            } else {
                $latupdate = $nmkaryawan[$d['updateby']];
            }
            echo "<td align=left>
                " . $latupdate . "
            </td>";
            @$opttipeTBS .= "<option value='" . $d['tipetbs'] . "'>" . $d['tipetbs'] . "</option>";
            if (strlen(@$d['kodesupplier']) > 4) {
                @$optSup .= "<option value='" . $d['kodesupplier'] . "'>" . $d['kodesupplier'] . " - ".$nmSup[$d['kodesupplier']]."</option>";
            }else{
                @$optSup .= "<option value='" . $d['kodesupplier'] . "'>" . $d['kodesupplier'] . " - ".$nmPt[$d['kodesupplier']]."</option>";
            }
            if($d['posting'] == 0 || $d['posting'] == 3){
                echo"<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['kodeunit'] . "','" . $d['kodesupplier'] . "', '".$d['rekening']."','".$d['tipetbs']."', '".$d['notransaksi']."');\"></td>";
				echo"<td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('" . $d['kodeunit'] . "','" . $d['kodesupplier'] . "', '".$d['rekening']."', '".$d['notransaksi']."');\"></td>";
				echo"<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' title='Posting' onclick=\"formajukan('".$d['kodeunit']."','".$d['notransaksi']."','".$page."');\"></td>";
            } else if ($d['posting'] == 9) {
                 echo"<td align=center colspan=3>
                        <img src=images/zoom.png class=zImgBtn title='Detail Posting' caption='Detail' onclick=\"detailpost('".$d['notransaksi']."');\">
                    </td>";
            } 
			else if ($d['posting'] == 1) {
                echo"<td align=center colspan=3>
                        <img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30' title='Posted'>
                    </td>";
            }
            $no += 1;
            echo "</tr>"; //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['parameter']."');\">
        }
        echo "</tbody><tfoot><tr class=rowheader>
              <td colspan=17 align=center>".($offset+1)." to ".($page*$limit)." of ". $jlhbrs."<br />";
        // echo "<button class=mybutton onclick=loadData(".($page-1).");>Prev</button>";
        // if (($page+1)>=ceil($jlhbrs/$limit)){
        //     echo "<button class=mybutton disabled >Next</button>";
        // } else {
        //     echo "<button class=mybutton onclick=loadData(".($page+1).");>Next</button>";
        // }
        $buttonaction = array(
            'first' =>  'onclick="loadData(1);"',
            'prev'  =>  'onclick="loadData('.($page-1).');"',
            'next'  =>  'onclick="loadData('.($page+1).');"',
            'last'  =>  'onclick="loadData('.($jml).')"',
            'pages' =>  'id="pages" name="pages" onchange="loadData(this.value);"'
        );
        echo $p->navHalaman($page,$jml,$buttonaction);
        echo "</td></tr>";

        echo "</tfoot></table>";
    break;

    case 'delete':
        $str = "delete from " . $dbname . ".pmn_5feetbs where kodeunit='" . $pt . "' and kodesupplier='" . $supplier . "' and rekening='".$rekening."' and notransaksi = '".$notransaksi."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal Delete," . addslashes($e->getMessage());
        }
    break;

    case 'edit':
      
        $i = "select * from " . $dbname . ".pmn_5feetbs where kodeunit='".$pt."' and kodesupplier='".$supplier."' and rekening='".$rekening."' and notransaksi = '".$notransaksi."' ";
        $n = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
        $n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) {
            $arr1=$d['kodeunit'];
            $arr2=$d['tipetbs'];
            $arr3=$d['kodesupplier'];
            $arr4=$d['tanggaldari'];
            $arr5=$d['tanggalsampai'];
            $arr6=$d['batasbawah'];
            $arr7=$d['batasatas'];
            $arr8=$d['rpkg'];
            $arr9=$d['rekening'];
            $arr10=$d['unitalokasi'];
            $arr11=$d['noakundebet'];
            $arr12=$d['noakunkredit'];
            $arr13=$d['persenppn'];
        }
        #== GET UNIT
        $optunit .= "<option value='" . $arr1 . "'>" . $arr1 . " - " . $nmSuporg[$arr1] . "</option>";
        #== GET TIPE
        $opttipeTBS .= "<option value='" . $arr2 . "'>" . $arr2 . "</option>";
        #== GET SUPPLIER
        if (strlen($arr3) > 4) {
            $optSup .= "<option value='" . $arr3 . "'>" . $arr3 . " - ".$nmSup[$arr3]."</option>";
        }else{
            $optSup .= "<option value='" . $arr3 . "'>" . $arr3 . " - ".$nmSuporg[$arr3]."</option>";
        }
        #== GET UNIT ALOKASI
        $optalokasi .= "<option value='" . $arr10 . "'>" . $arr10 . " - " . $nmSuporg[$arr10] . "</option>";
        #== GET REKENING
        $optrek .= "<option value='" . $arr9 . "'>" . $arr9 . "</option>";
        
		
		
        echo $optunit."###".$opttipeTBS."###".$optSup."###".tanggalnormal($arr4)."###".tanggalnormal($arr5)."###".$arr6."###".$arr7."###".$arr8."###".$optrek."###".$optalokasi."###".$arr11."###".$arr12."###update###".$arr13."";
		// exit("Error:A");
    break;

    case 'formajukan':
        $countApp = getCountApproval('HFTBS',$pt);
		
        $tab = "
				<table>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=tanggalpengajuan readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:152px;/></td>
					</tr>";

        for($i=1; $i<=$countApp; $i++){
            $arrList = listApprove($i,'HFTBS',$pt);
            // $optpersetujuan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

            foreach($arrList as $key=>$val){
                $optpersetujuan = "<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
            }

            $tab .= "<tr>
                        <td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
                        <td>:</td>
                        <td><select style=\"width:154px;\" id=persetujuan".$i.">".$optpersetujuan."</select></td>
                    </tr>";  
        }   
		if($countApp>0){
			$tab .= "      <tr>";
			$tab .="<td colspan=2></td>";
			$tab .="<td><button class=mybutton onclick=posting('".$notransaksi."','".$countApp."','".$page."')>".$_SESSION['lang']['save']."</button></td>";
			$tab .="</tr>";
		}else{
			$tab .= "      <tr>";
			$tab .="<td colspan=5>Approval belum disetting, silahkan setting approval untuk persetujuan harga fee tbs</td>";
			$tab .="</tr>";
		}
   
		 $tab .="</table>";
		 

        echo $tab;
    break;

    case 'posting':
    
        try {
            $owlPDO->beginTransaction();
            
            if($param['tanggalpengajuan'] == ''){
                exit("Warning: Tanggal pengajuan masih kosong");
            }
            
            for($i=1; $i<=$param['maxaproval']; $i++){
                if($param['persetujuan'][$i]=='') {
                    exit("Warning: Persetujuan ".$i." belum dipilih.");
                }
            }

            #= delete 1st untuk aprovalnya
            $str = "DELETE FROM ".$dbname.".approval WHERE notransaksi = '".$notransaksi."' AND jenispersetujuan = 'HFTBS'";
            $owlPDO->exec($str);
            
            $str = "UPDATE ".$dbname.".pmn_5feetbs set posting = '9', tanggalpengajuan = '".$tanggalpengajuan."'
                    WHERE notransaksi = '".$notransaksi."'";
            $owlPDO->exec($str);

            for($i=1;$i<=$param['maxaproval'];$i++){
                #= insert
                $str = "INSERT INTO ".$dbname.".approval 
                       (notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
                       VALUES
                       ('".$notransaksi."','HFTBS','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";   
                $owlPDO->exec($str);
            }
            
            $owlPDO->commit();
            
        } catch(PDOException $e) {
        
        $owlPDO->rollback();
            echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());

        }
    break;

    case 'detailpost':
		$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
		$tab="<br><table ".$style.">
		<thead><tr class=rowheader>";
		$tab.="<th align=center width=20px>No</th>
			<th align=center >".$_SESSION['lang']['nama']."</th>
			<th align=center >".$_SESSION['lang']['jabatan']."</th>
			<th align=center >".$_SESSION['lang']['status']."</th>
			<th align=center >".$_SESSION['lang']['tanggal']."</th>
			<th align=center >".$_SESSION['lang']['catatan']."</th>
		</tr>
		</thead><tbody>";
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=left colspan=6 style=color:blue;>Persetujuan</td>";
		$tab.="</tr>";
	
		$str="select distinct * from ".$dbname.".approval where  notransaksi = '".$notransaksi."' and jenispersetujuan='HFTBS' order by level";
		$res=fetchdata($str);$no=0;
		$namajab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		foreach($res as $bar){
			$nmkary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid ='".$bar['karyawanid']."'");
			$kodejab = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid ='".$bar['karyawanid']."'");

			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$nmkary[$bar['karyawanid']]."</td>";
			$tab.="<td align=left>".$namajab[$kodejab[$bar['karyawanid']]]."</td>";
			if($bar['status']==0){
				$tab.="<td align=left>".$_SESSION['lang']['wait_approval']."</td>";				
			}else{				
				$tab.="<td align=left>".$arrHsl[$bar['status']]."</td>";
			}
			if($bar['tanggal']=='0000-00-00 00:00:00'){
				$tab.="<td align=left></td>";
			}else{				
				$tab.="<td align=left>".$bar['tanggal']."</td>";
			}
			$tab.="<td align=left>".$bar['komentar']."</td>";
		}
		
        $tab .= "   </table>
                </fieldset>";

        echo $tab;
    break;

    case 'insertcopy':
		/*
        $str = "SELECT * FROM ".$dbname.".pmn_5feetbs WHERE kodeunit='".$pt."' AND unitalokasi='".$alokasi."' AND tipetbs='".$tipetbs."' AND kodesupplier='".$supplier."' AND tanggaldari='".$periodecopy."'";
        $row = fetchData($str);
        if (count($row) > 0) {
            exit("Warning : Data sudah ada untuk ".$pt." ".$nmSup[$kodesupplier]." ".tanggalnormal($periodecopy)." !");
        }
		*/

        $cek = "SELECT * FROM ".$dbname.".pmn_5feetbs WHERE kodeunit='".$pt."' AND unitalokasi='".$alokasi."' AND tipetbs='".$tipetbs."' AND kodesupplier='".$supplier."' AND tanggaldari='".$tanggaldari."'";
        $res = fetchData($cek);
        $ct = count($res);
        if ($ct < 1) {
            exit("Warning : Data yang ingin di Copy tidak ditemukan !");
        }
		
        foreach($res as $val){
			
			#= counter baru karna 1 supplier bisa ada 2 rekening
			$cekq = "select * from " . $dbname . ".pmn_5feetbs where kodeunit='" . $pt . "' and kodesupplier='" . $supplier . "' and tanggaldari='".$periodecopy."'";
			$resq = fetchData($cekq);
			$ct = count($resq);

			// $notransaksi = str_replace('-', '', $tanggaldari).$pt.$supplier.addZero($ct,3);
			
            $notransaksi = str_replace('-', '', $periodecopy).$val['kodeunit'].$val['kodesupplier'].addZero($ct+1,3);;

            $ins = "INSERT INTO ".$dbname.".pmn_5feetbs 
                    (notransaksi, kodeunit, unitalokasi, tipetbs, kodesupplier, 
                    tanggaldari, tanggalsampai, batasbawah, batasatas, 
                    rpkg, status, createby, createtime, 
                    rekening, noakundebet, noakunkredit, posting)
                    VALUES 
                    ('".$notransaksi."', '".$val['kodeunit']."', '".$val['unitalokasi']."', '".$val['tipetbs']."', '".$val['kodesupplier']."',
                    '".$periodecopy."', '0000-00-00', '".$val['batasbawah']."', '".$val['batasatas']."', 
                    '".$val['rpkg']."', '1', '".$_SESSION['standard']['userid']."', '".date('Y-m-d H:i:s')."', 
                    '".$val['rekening']."', '".$val['noakundebet']."', '".$val['noakunkredit']."', '0')";
            try {
                $owlPDO->exec($ins);
            } catch (PDOException $e) {
                echo " Gagal Insert," . addslashes($e->getMessage());
            }
        }
    break;

    default:
}
?>
