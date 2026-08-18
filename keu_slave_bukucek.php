<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');
include_once('lib/rTable.php');

$pt = checkPostGet('pt','');
$unit = checkPostGet('unit','');
$notrans_cek = checkPostGet('notrans_cek','');
$tipe_buku = checkPostGet('tipetransaksi','');
$noakun = checkPostGet('noakun','');
$nocek_awal = checkPostGet('noawal','');
$nocek_akhir = checkPostGet('noakhir','');
$nocekvoid = checkPostGet('nocekvoid','');
$notransaksi = checkPostGet('notransaksi','');
$method = checkPostGet('method','');
$proses = checkPostGet('proses','');

$alasan = checkPostGet('alasan', '');

$persetujuan = checkPostGet('persetujuan', '');
$tipe = checkPostGet('tipe', '');
$file = checkPostGet('file', '');
$tujuan = checkPostGet('tujuan', '');
$tglcair = tanggalsystemn(checkPostGet('tglcair', ''));
$penerima = checkPostGet('penerima', '');
$doc = checkPostGet('doc', '');
$dir='fileupload/bukucek';

$noawalcr = checkPostGet('noawalcr', '');
$noakhircr = checkPostGet('noakhircr', '');
$tipecr = checkPostGet('tipecr', '');
$unitcr = checkPostGet('unitcr', '');
$notransaksicr = checkPostGet('notransaksicr', '');
$noakuncr = checkPostGet('noakuncr', '');
$statuscr = checkPostGet('statuscr', '');

switch ($method) {

    case 'getakun':
        $akun=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query("select * from ".$dbname.".keu_5akunbank where pemilik='".$unit."' order by namabank ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            $wheredz =" kodebank='".$bar['namabank']."'";
            $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);

            if ($noakun==$bar['noakun']){
                $akun.="<option value='".$bar['noakun']."' selected>".$optnama[$bar['namabank']]."-".$bar['rekening']."</option>";
            }else{
                $akun.="<option value='".$bar['noakun']."'>".$optnama[$bar['namabank']]."-".$bar['rekening']."</option>";
            }
        }

        echo $akun;

    break;

    case 'insert':

        $str="select count(notrans_cek) as jumlahdt from ".$dbname.".keu_bukucekht where noakun='".$noakun."' and tipe_buku='".$tipe_buku."' and status=1";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jumlahdt=$bar['jumlahdt'];

        $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun=".$noakun."";
        $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
        $resak->setFetchMode(PDO::FETCH_ASSOC);
        $barak=$resak->fetch();

        // if($jumlahdt>0){
            // exit('warning : '.$barak['namabank'].'('.$barak['rekening'].') sudah memiliki buku cek yang aktif.');
        // }
        
        if($tipe_buku=='PO'){
            $angkaawal=intval($nocek_awal);
            $angkaakhir=intval($nocek_akhir);
        }else{
            $angkaawal=preg_replace("/[^0-9]/",'',$nocek_awal);
            $angkaakhir=preg_replace("/[^0-9]/",'',$nocek_akhir);
        }
        
        $selisih=$angkaakhir-$angkaawal+1;

        if ($selisih>100){ #buat jaga2 jangan dilepas kejadian di SDK1 yang loop sebanyak 49 juta
            exit('warning : Cek kembali jumlah lembar cek.');
        }

        if ($tipe_buku=='Cheque'){
            $kdtipe='CK';
        }
        if ($tipe_buku=='Giro'){
            $kdtipe='GR';
        }
        if ($tipe_buku=='PO'){
            $kdtipe='PO';
        }

        $optMyPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
        $tahunbulan = $optMyPt[$unit]."-".$kdtipe.date('ymd');

        $query="select right(notrans_cek,3) as nomorurut from ".$dbname.".keu_bukucekht where left(notrans_cek,12) = '".$tahunbulan."' order by right(notrans_cek,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();

        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }

        $notrans_cek=$tahunbulan.addZero($awal,3);

        $str = "insert into " . $dbname . ".keu_bukucekht (notrans_cek,unit,tipe_buku,noakun,nocek_akhir,nocek_awal,createdby,updateby)
                values ('" . $notrans_cek . "','" . $unit . "','" . $tipe_buku. "','" . $noakun . "','" . $nocek_akhir. "','" . $nocek_awal. "','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

        break;

    case'deldt':

        $str="select tipe_buku from ".$dbname.".keu_bukucekht where notrans_cek='".$notrans_cek."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $tipe_buku=$bar['tipe_buku'];

        $str="select count(notrans_cek) as jumlah from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jumlah=$bar['jumlah'];
        if($jumlah>0){
            exit('Warning : Buku '.$tipe_buku.' ini sudah pernah digunakan.');
        }

        $strdt = "delete from ".$dbname.".keu_bukucekht where notrans_cek='".$notrans_cek."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'updatedt':

        $str="select count(notrans_cek) as jumlahdt from ".$dbname.".keu_bukucekht where noakun='".$noakun."' and tipe_buku='".$tipe_buku."' and status=1";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jumlahdt=$bar['jumlahdt'];

        $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun=".$noakun."";
        $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
        $resak->setFetchMode(PDO::FETCH_ASSOC);
        $barak=$resak->fetch();

        if($jumlahdt>0){
            exit('warning : '.$barak['namabank'].'('.$barak['rekening'].') sudah memiliki buku cek yang aktif.');
        }

        if($tipe_buku=='PO'){
            $angkaawal=intval($nocek_awal);
            $angkaakhir=intval($nocek_akhir);
        }else{
            $angkaawal=preg_replace("/[^0-9]/",'',$nocek_awal);
            $angkaakhir=preg_replace("/[^0-9]/",'',$nocek_akhir);
        }
        
        $selisih=$angkaakhir-$angkaawal+1;

        if ($selisih>25){
            exit('warning : Jumlah lembar cek tidak boleh lebih dari 25.');
        }

        $strht = "update ".$dbname.".keu_bukucekht set noakun='".$noakun."',updateby='".$_SESSION['standard']['userid']."', nocek_akhir='".$nocek_akhir."',nocek_awal='".$nocek_awal."'  where notrans_cek='".$notrans_cek."'";             
            try
            {
                $owlPDO->exec($strht);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        break;

    case 'posting':

        $strht = "update ".$dbname.".keu_bukucekht set status='1' where notrans_cek='".$notrans_cek."'";             
            try
            {
                $owlPDO->exec($strht);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        break;

    case 'ajukanvoid':

        $strht = "update ".$dbname.".keu_bukucekdt set status_cek='0',alasan_void='".$alasan."' where notrans_cek='".$notrans_cek."' and notransaksi='".$notransaksi."'";           
            try
            {
                $owlPDO->exec($strht);

                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notrans_cek."','BKCK','1','".$persetujuan."','0')";
                try{
                    $owlPDO->exec($str); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }

            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        break;

    case 'simpannocekvoid':

        $str = "insert into " . $dbname . ".keu_bukucekdt (notrans_cek,notransaksi,nocek,status_cek)
                values ('".$notrans_cek."','".$notransaksi."','".$nocekvoid."','1')";
        try{
            $owlPDO->exec($str); 

            $strht = "update ".$dbname.".keu_bukucekdt set nocek_void='".$nocekvoid."' where notrans_cek='".$notrans_cek."' and notransaksi='".$notransaksi."' and nocek='".$nocek_awal."'";     
            try
            {
                $owlPDO->exec($strht);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $strkb = "update ".$dbname.".keu_kasbankht set nocek='".$nocekvoid."' where notransaksi='".$notransaksi."'";
            try
            {
                $owlPDO->exec($strkb);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

        break;

    case 'loadData':
        $where = "1=1 ";
        // $where = " createdby ='".$_SESSION['standard']['userid']."'";
		$optOrg=array();
		$optOrg = getOrgDetail(10);
		ksort($optOrg);
		
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where.="and unit in ('".implode("','",$optOrg)."')";
        }else{
          $where.= "and unit='".$_SESSION['empl']['lokasitugas']."'";
        }
		
		
	    if ($noawalcr != '') {
            $where.=" and nocek_awal like '%" . $noawalcr . "%' ";
        }if ($noakhircr != '') {
            $where.=" and nocek_akhir like '%" . $noakhircr . "%' ";
        }	
		if ($unitcr != '') {
            $where.=" and unit='" . $unitcr . "' ";
        }
		 if ($tipecr != '') {
            $where.=" and tipe_buku='" . $tipecr . "' ";
        }
		if ($noakuncr != '') {
            $where.=" and noakun='" . $noakuncr . "' ";
        }
		if ($notransaksicr != '') {
            $where.=" and notrans_cek like '%" . $notransaksicr . "%' ";
        }
		if ($tipecr != '') {
            $where.=" and tipe_buku='" . $tipecr . "' ";
        }
		if ($statuscr != '') {
            $where.=" and status='" . $statuscr . "' ";
        }
		
		// if ($unitcr != '') {
            // $where.=" and unit='" . $unitcr . "' ";
        // }else{
			 // $where.= " and unit in (".getOrgDetail(2).")";
		// }
		
       
        $limit=10;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".keu_bukucekht where ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{

            $str="select * from ".$dbname.".keu_5daftarbank";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $nmbank[$bar['kodebank']]=$bar['namabank'];
            }

            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_bukucekht where ".$where." order by notrans_cek desc limit ".$offset.",".$limit."";
			// echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whrpt="kodeorganisasi='".$bar->unit."'";
                $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
                #pembuat
                $whrKar2="karyawanid='".$bar->createdby."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);

                $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun=".$bar->noakun." order by namabank asc";
                $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
                $resak->setFetchMode(PDO::FETCH_OBJ);
                $barak=$resak->fetch();
                
                if ($bar->status==0){
                    $status=$_SESSION['lang']['nonaktif'];
                } else if ($bar->status==1){
                    $status=$_SESSION['lang']['aktif'];
                } else if ($bar->status==2){
                    $status=$_SESSION['lang']['tutup'];
                }

                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->notrans_cek."</td>
                    <td>".$bar->unit." - ".$optpt[$bar->unit]."</td>
                    <td>".$bar->tipe_buku."</td>
                    <td>".$nmbank[$barak->namabank]." - ".$barak->rekening."</td>
                    <td>".$bar->nocek_awal." - ".$bar->nocek_akhir."</td>
                    <td>".$optpembuat[$bar->createdby]."</td>
                    <td align=center>".$status."</td>";
                    if ($bar->status==0){
                        $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt('".$bar->unit."','".$bar->notrans_cek."','".$bar->noakun."','".$bar->tipe_buku."','".$bar->nocek_awal."','".$bar->nocek_akhir."')\"></td>
                               <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldt('" . $bar->notrans_cek. "');\" ></td>
                               <td align=center><img src=images/add.png class=resicon  title='Aktifkan' onclick=\"posting('".$bar->notrans_cek."');\" ></td>
                               <td width=10px;></td>";
                    }else if ($bar->status==1){
                        $tab.="<td width=10px;></td>";
                        $tab.="<td width=10px;></td>";
                        $tab.="<td width=10px;></td>";
                        $tab.="<td width=10px;><img src=images/icons/lock.png class=resicon  title='Closed' onclick=\"closed('".$bar->notrans_cek."');\" ></td>";
                        // $tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar->notrans_cek. "',event);\" ></td>";
                    }else{
                        $tab.="<td width=10px;></td>";
                        $tab.="<td width=10px;></td>";
                        $tab.="<td width=10px;></td>";
                        $tab.="<td width=10px;><img src=images/icons/lock_unlock.png class=resicon  title='Unclosed' onclick=\"unclosed('".$bar->notrans_cek."');\" ></td>";
                        // $tab.="<td align=center ><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar->notrans_cek. "',event);\" ></td>";
                    }
                $tab.="<td><img class=\"zImgBtn\" src=\"images/skyblue/excel.jpg\" style=\"cursor:pointer\" onclick=\"printFile('" . $bar->notrans_cek. "','keu_slave_bukucek.php',event)\" title=\"Print Detail\"></td>";
                $tab.="</tr>";
                
            }
            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=13 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
        break;
		
	case 'viewdetail':	
	
		if ($proses == 'excel') {
            $border = "border=1";
            $color="bgcolor=#CCCCCC";
        } else {
            $border = "border=0";
            $color="";
        }
	
		// exit("Error".$notrans_cek);
		
		
		 $tab="<table ".$border." cellpadding=1 cellspacing=1 class=sortable width=100%>
            <thead>
            <tr class=rowcontent >    
                <td align=center  ".$color.">No. Buku</td>
                <td align=center  ".$color.">".$_SESSION['lang']['notransaksi']."</td>
                <td align=center ".$color.">".$_SESSION['lang']['novoucher']."</td>
                <td align=center ".$color.">".$_SESSION['lang']['tanggal']."</td>
                <td align=center ".$color.">".$_SESSION['lang']['jumlah']."</td>
                <td align=center ".$color.">".$_SESSION['lang']['keterangan']."</td>
            </tr></thead>";
		
	
		$str = "select * from ".$dbname.".keu_bukucekht where notrans_cek='".$notrans_cek."' 
				order by right(notrans_cek,3) desc";
				// echo $str;exit();
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
			$notrans_cek=$bar['notrans_cek'];
			$nocek_awal=$bar['nocek_awal'];
			$jumlahangka=strlen($nocek_awal);
			$nocek_akhir=$bar['nocek_akhir'];

			$angkaawal=preg_replace("/[^0-9]/",'',$nocek_awal);
			$angkaakhir=preg_replace("/[^0-9]/",'',$nocek_akhir);
			
			$selisih=$angkaakhir-$angkaawal+1;
			
			$nocek='';

            #= cek notransaksi kasbank
               
			/*for ($i=1; $i <=$selisih ; $i++) { 
			
				#= cek notransaksi kasbank
                $str1 = "select * from ".$dbname.".keu_kasbankht where nocek='".$nocek_awal."' group by notransaksi";
				$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);
				$bar1=$res1->fetch();
				$tab.="	
					<tr class=rowcontent>    
					<td align=center>".$nocek_awal."fff</td>
					<td align=center>".$bar1['notransaksi']."xxxx</td>
					<td align=center>".$bar1['novoucher']."</td>
					<td align=center>".tanggalnormal($bar1['tanggal'])."</td>
				</tr>";
				$nocek_awal++;
			}*/
        }

         $str1 = "select * from ".$dbname.".keu_kasbankht where nocek between '".$nocek_awal."' and '".$nocek_akhir."' order by nocek";
                $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                while($bar1=$res1->fetch()){
                $tab.=" 
                    <tr class=rowcontent>    
                    <td align=center>".$bar1['nocek']."</td>
                    <td align=center>".$bar1['notransaksi']."</td>
                    <td align=center>".$bar1['novoucher']."</td>
                    <td align=center>".tanggalnormal($bar1['tanggal'])."</td>
                    <td align=right>".number_format($bar1['jumlah'],2)."</td>
                    <td align=left>".$bar1['keterangan']."</td>
                </tr>";

                $totalRupiah += $bar1['jumlah'];
            }

            # Total Rupiah
            $tab.="<tr class=rowcontent>";
                $tab.="<td align=center colspan=4><b>GRAND TOTAL</b></td>";
                $tab.="<td align=right><b>".number_format($totalRupiah,2)."</b></td>";
                $tab.="<td align=center></td>";
            $tab.="</tr>";

		$tab.="</table>";
  /*      echo $tab;
		exit("error:".$tab);*/
		
		
		
		
		if ($proses=='excel') { 
            $tglSkrg = date("Ymd");
            $nop_ = "Detail Buku Cek".$notrans_cek;
            if (strlen($tab) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $tab)) {
                    echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                    exit;
                } else {
                    echo "<script language=javascript1.2>
                    window.location='tempExcel/" . $nop_ . ".xls';
                    </script>";
                }
                fclose($handle);
            }
        }else{
            echo $tab;
        }
	
	break;

    case 'viewdetail_lama':

        if ($proses == 'excel') {
            $border = "border=1";
            $color="bgcolor=#CCCCCC";
        } else {
            $border = "border=0";
            $color="";
        }

        $strht="select * from ".$dbname.".keu_bukucekht where notrans_cek='".$notrans_cek."'";
        $resht= $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $barht = $resht->fetch();
        $unit=$barht['unit'];
        $nocek_awal=$barht['nocek_awal'];
        $tipe_buku=$barht['tipe_buku'];
        $jumlahangka=strlen($nocek_awal);
		  $nocek_akhir=$barht['nocek_akhir'];

        // $str = "select count(notrans_cek) as jumlahdt from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' order by nocek desc";
        // $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_ASSOC);
        // $bar=$res->fetch();
        // $jumlahdt=$bar['jumlahdt'];

        // if($tipe_buku=='PO'){
            // $angkaawal=intval($barht['nocek_awal']);
            // $angkaakhir=intval($barht['nocek_akhir']);
        // }else{
            // $angkaawal=preg_replace("/[^0-9]/",'',$barht['nocek_awal']);
            // $angkaakhir=preg_replace("/[^0-9]/",'',$barht['nocek_akhir']);
        // }
        // $sisa=$angkaakhir-$angkaawal-$jumlahdt+1;
        // $selisih=$angkaakhir-$angkaawal+1;
		
		$angkaawal=preg_replace("/[^0-9]/",'',$nocek_awal);
		$angkaakhir=preg_replace("/[^0-9]/",'',$nocek_akhir);
        $selisih=$angkaakhir-$angkaawal+1;
		
		// echo $angkaakhir._.$angkaawal;

        $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun='".$barht['noakun']."' order by namabank asc";
        $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
        $resak->setFetchMode(PDO::FETCH_ASSOC);
        $barak=$resak->fetch();

        $optNamaBank = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',"kodebank='".$barak['namabank']."'");
        
        $whrpt="kodeorganisasi='".substr($barht['notrans_cek'],0,3)."'";
        $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
        $tab="<table ".$border." cellpadding=1 cellspacing=1 class=sortable ".$color." width=100%>
            <thead>
            <tr class=rowcontent>    
                <td rowspan=3>".$_SESSION['lang']['pt']."</td>
                <td rowspan=3> : </td>
                <td rowspan=3>".$optpt[substr($barht['notrans_cek'],0,3)]."</td>
                <td>".$_SESSION['lang']['nama']." Bank</td>
                <td> : </td>
                <td>".$optNamaBank[$barak['namabank']]."</td>
                <td>".$_SESSION['lang']['tipetransaksi']."</td>
                <td> : </td>
                <td>".$tipe_buku."</td>
            </tr>
            <tr class=rowheader>    
                <td>".$_SESSION['lang']['matauang']."</td>
                <td> : </td>
                <td>IDR</td>
                <td>Buku Cek/Giro/PO</td>
                <td> : </td>
                <td>".$barht['nocek_awal']." - ".$barht['nocek_akhir']."</td>
            </tr>
            <tr class=rowheader>    
                <td>".$_SESSION['lang']['nourut']." Rekening</td>
                <td> : </td>
                <td>".$barak['rekening']."</td>
                <td>".$_SESSION['lang']['sisa']." Lembar</td>
                <td> : </td>
                <td>".$sisa." of ".$selisih."</td>
            </tr>
            </thead>
            </table><br><br>";            
            
        $tab.="<table ".$border." cellpadding=1 cellspacing=1 class=sortable>
            <thead>
            <tr class=rowheader ".$color.">    
                <td align=center>".$_SESSION['lang']['nourut']."</td>
                <td align=center>".$_SESSION['lang']['nourut']." Cek</td>
                <td align=center>".$_SESSION['lang']['notransaksi']." Kasbank</td>
                <td align=center>".$_SESSION['lang']['tanggal']."</td>
                <td align=center>".$_SESSION['lang']['keterangan']."</td>
                <td align=center>".$_SESSION['lang']['jumlah']."</td>
                <td align=center>".$_SESSION['lang']['jumlah']." Terbilang</td>
                <td align=center>".$_SESSION['lang']['file']." Cek</td>
                <td align=center>".$_SESSION['lang']['file']." Cek Void</td>";
        if ($proses != 'excel') { 
            $tab.="<td align=center colspan=4>Aksi</td>";
        }   
        $tab.="</tr>
            </thead>";

        $no = 0;
		// echo $selisih;

        for ($i=1; $i <=$selisih ; $i++) { 

            // if($tipe_buku=='PO'){
                // $nocek_awal=addZero($nocek_awal,$jumlahangka);
            // }

            $str="select * from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' and nocek='".$nocek_awal."'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar = $res->fetch();

                //get data kasbankht
                $strht="SELECT tanggalinput,keterangan,jumlah,kodeorg,tanggal,posting from ".$dbname.".keu_kasbankht 
				where notransaksi='".$bar['notransaksi']."'";
				//exit("Error:$strht");
                $resht=$owlPDO->query($strht) or die(print " Gagal: ".PDOException::getMessage());
                $resht->setFetchMode(PDO::FETCH_ASSOC);
                $barht=$resht->fetch();

                $periode=makeOption($dbname,'setup_periodeakuntansi','kodeorg,periode',"kodeorg = '".$barht['kodeorg']."' and tutupbuku = 0");

                if ($bar['tujuan']!='') {
                    $barht['tanggalinput']=$bar['tglcair'];
                    $barht['keterangan']=$bar['tujuan'];
                }

                if ($barht['jumlah']==0) {
                    $barht['jumlah']='';
                }else{
                    $barht['jumlah']=number_format($barht['jumlah']);
                }

                $no+=1;
                $fvalterbilan=str_replace(',', '', $barht['jumlah']);
						
                $tab.="<tr class=rowcontent>   
                    <td align=center>".$no."</td>
                    <td>".$nocek_awal."</td>
                    <td>".$bar['notransaksi']."</td>
                    <td align=center>".$barht['tanggalinput']."</td>
                    <td>".$barht['keterangan']."</td>
                    <td align=right>".$barht['jumlah']."</td>
                    <td>".@terbilang($fvalterbilan)."</td>
                    <td><a style=cursor:pointer; onclick=\"displayfile('".$bar['filecek']."','event');\" href='#'>".$bar['filecek']."</a></td>
                    <td><a style=cursor:pointer; onclick=\"displayfile('".$bar['filecekvoid']."','event');\" href='#'>".$bar['filecekvoid']."</a></td>";
                   
				   if ($tipe!=2) {
						
                        if ($barht['posting']==1){
                            if(substr($barht['tanggal'],0,7)>=$periode[$barht['kodeorg']]){
                                if ($bar['status_cek']==1){
                                    $isi="<img src=images/icons/04/16/01.png class=resicon  title='Ajukan Void No.Cek ".$nocek_awal."' onclick=\"formajukanvoid('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$unit."','".$nocek_awal."');\" >";
                                    $isi2="";
                                    $isi3="<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                                }
                                if ($bar['status_cek']==0){
                                    $isi="";
                                    $isi2="";
                                    $isi3="<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                                }
                                if ($bar['status_cek']==2){
                                    $isi=$bar['nocek_void'];
                                    if($bar['nocek_void']==''){
                                        $isi2="<img src=images/icons/04/16/02.png class=resicon  title='Ajukan Void No.Cek ".$nocek_awal."' onclick=\"formpilihnocek('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                                    }else{
                                        $isi2="";
                                    }
                                    $isi3="<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                                }
                            }else{
                                $isi=$bar['nocek_void'];
                                $isi2="";
                                $isi3="<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                            }
                        }else{
                            if ($bar['status_cek']==1){
                                $isi="<img src=images/icons/04/16/01.png class=resicon  title='Ajukan Void No.Cek ".$nocek_awal."' onclick=\"formajukanvoid('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$unit."','".$nocek_awal."');\" >";
                                $isi2="";
                                $isi3="<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                            }
                            if ($bar['status_cek']==0){
                                $isi="";
                                $isi2="";
                                $isi3="<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                            }
                            if ($bar['status_cek']==2){
                                $isi=$bar['nocek_void'];
                                if($bar['nocek_void']==''){
                                    /*$isi2="<img src=images/icons/04/16/02.png class=resicon  title='Ajukan Void No.Cek ".$nocek_awal."' onclick=\"formpilihnocek('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";*/
                                }else{
                                    $isi2="";
                                }
                                $isi3="<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar['notrans_cek']."','".$bar['notransaksi']."','".$nocek_awal."');\" >";
                                
                            }
                        }
                    }
					
                    if ($bar['nocek']=='') {
                        $isi4="<img src=images/skyblue/Pengreen.jpg class=resicon class=zImgBtn height='30'  title='Transaksi GRTT' onclick=\"formgrtt('".$notrans_cek."','".$nocek_awal."');\" >";
                    }
                    
                if ($proses!='excel') {
                    $tab.="<td align=center style='width:10px;'>".$isi."</td>";
                    $tab.="<td align=center style='width:10px;'>".$isi2."</td>";
                    $tab.="<td align=center style='width:10px;'>".$isi3."</td>";
                    $tab.="<td align=center style='width:10px;'>".$isi4."</td>";
                }
                $tab.="</tr>";
            
            $nocek_awal++;
        }

        $tab.="</table>";

        if ($proses=='excel') { 
            $tglSkrg = date("Ymd");
            $nop_ = "Detail Buku Cek".$notrans_cek;
            if (strlen($tab) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $tab)) {
                    echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                    exit;
                } else {
                    echo "<script language=javascript1.2>
                    window.location='tempExcel/" . $nop_ . ".xls';
                    </script>";
                }
                fclose($handle);
            }
        }else{
            echo $tab;
        }
        
    break;

    case 'formajukanvoid':
        $str="select distinct a.namakaryawan,a.nik,b.karyawanid from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.jenispersetujuan='BKCK' and b.level='1' and kodeunit='".$unit."' order by a.namakaryawan";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optper2.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."</option>";
        }

        echo"<fieldset>
            <table cellspacing=1 border=0>
                <tr>
                    <td>Alasan</td>
                    <td>:</td>
                    <td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['persetujuan']."</td>
                    <td>:</td>
                    <td><select id=persetujuan style='width:175px;' >".$optper2."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['file']."</td>
                    <td>:</td>
                    <td><input name=fileupload type=file id=fileupload size=1 class=mybutton style='width:150px'></td>
                </tr>
                <td><td><td>
                <input type=hidden id=noawalvoid value='".$nocek_awal."'/>
                <input type=hidden id=notransvoid value='".$notrans_cek."'/>
                <input type=hidden id=notransaksi value='".$notransaksi."'/>
                <button class=mybutton onclick=ajukanvoid() id=ajukanvoid >Ajukan</button>
                <button class=mybutton onclick=cancelvoid()>".$_SESSION['lang']['cancel']."</button>
                </td></tr></table>
            </fieldset>";

        break;

    case 'simpanupload':

        $fileupload = strtolower('.'.substr($_FILES['fileup']['name'],strripos($_FILES['fileup']['name'],'.')+1));
        $fileupload = $fileupload;
        
        $filesize=$_FILES['fileup']['size'];

        if($filesize>= 512000)
        {
            exit("Warning : Besar ukuran file maksimal 512 KB. ");
        }
        $path = $dir."/".basename($_FILES['fileup']['name']);
        if(move_uploaded_file($_FILES['fileup']['tmp_name'], $path)){ 

            if ($tipe==1) {
                $set="filecek";
            }
            if ($tipe==2) {
                $set="filecekvoid";
            }
            
            $str = "update ".$dbname.".keu_bukucekdt set ".$set."='".basename($_FILES['fileup']['name'])."' where notrans_cek='".$notrans_cek."' and notransaksi='".$notransaksi."'and nocek='".$nocek_awal."'";           
            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }   
        
        }
        echo $_SESSION['lang']['datatersimpan'];

    break;

    case 'uploaddata':
        
        echo "
        <fieldset style=float:left>
            <legend>".$_SESSION['lang']['uploaddata']."</legend>
            <table>
                <tr>
                    <td><input name=fileupload1 type=file id=fileupload1 size=1 class=mybutton style=width:160px>
                    </td>
                    <td>
                        <button class=mybutton onclick=simpanupload('".$notrans_cek."','".$notransaksi."',1,'".$nocek_awal."')>".$_SESSION['lang']['save']."</button>
                    </td>
                </tr>
            </table>
        </fieldset><br><br><br><br>";

        break;

    case 'formpilihnocek':

        $strht="select * from ".$dbname.".keu_bukucekht where notrans_cek='".$notrans_cek."'";
        $resht= $owlPDO->query($strht) or die(print " Gagal: " . PDOException::getMessage());
        $resht->setFetchMode(PDO::FETCH_ASSOC);
        $barht = $resht->fetch();
        $noawal=$barht['nocek_awal'];
        $tipe_buku=$barht['tipe_buku'];
        $jumlahangka=strlen($noawal);

        $str = "select count(notrans_cek) as jumlahdt from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' order by nocek desc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $jumlahdt=$bar['jumlahdt'];

        $angkaawal=preg_replace("/[^0-9]/",'',$barht['nocek_awal']);
        $angkaakhir=preg_replace("/[^0-9]/",'',$barht['nocek_akhir']);
        $selisih=$angkaakhir-$angkaawal+1;

        for ($i=1; $i <=$selisih ; $i++) { 
            if($tipe_buku=='PO'){
                $noawal=addZero($noawal,$jumlahangka);
            }

            $str = "select nocek from ".$dbname.".keu_bukucekdt where notrans_cek='".$notrans_cek."' and nocek='".$noawal."' order by nocek asc";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            if($bar['nocek']==''){
                $nocek=$noawal;
            }

            $noawal++;
            
            if($bar['nocek']!=''){
                continue;
            }

            $optnocek.="<option value='".$nocek."'>".$nocek."</option>";
        }
        
        echo"<fieldset>
            <table cellspacing=1 border=0>
                <tr>
                    <td>No.Cek Void</td>
                    <td>:</td>
                    <td><select id=nocekvoid style=width:200px;>".$optnocek."</select></td>
                </tr>
                <tr>
                <td></td><td></td><td>
                <button class=mybutton onclick=simpannocekvoid('".$notrans_cek."','".$notransaksi."','".$nocek_awal."') >".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=cancelvoid()>".$_SESSION['lang']['cancel']."</button>
                </td></tr></table>
            </fieldset>";

        break;


    case'displayfile':
    
        $potong=explode('.',$doc);
        if($potong[1]=='jpeg' || $potong[1]=='jpg' || $potong[1]=='png')
        {
            echo"<img src='fileupload/bukucek/".$doc."'>";
            
        }
        else
        {
            echo"<embed src='fileupload/bukucek/".$doc."' width=780px height=370px>";
        }

        break;

    case 'formgrtt':
        $str="select distinct namasupplier,supplierid from ".$dbname.".log_5supplier order by namasupplier";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optsupp.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']." - ".$bar['supplierid']."</option>";
        }

        echo"<fieldset>
            <table cellspacing=1 border=0 width=200px>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']." Pencairan</td>
                    <td>:</td>
                    <td><input type=text class=myinputtext id=tglcair onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:175px; maxlength=10 /></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tujuan']."</td>
                    <td>:</td>
                    <td><textarea id=tujuan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['penerima']."</td>
                    <td>:</td>
                    <td><input type=text id=penerima class=myinputtext style='width:175px'></td>
                </tr>
                <td><td><td>
                <button class=mybutton onclick=simpangrtt('".$notrans_cek."','".$nocek_awal."') id=simpangrtt >".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=cancelvoid()>".$_SESSION['lang']['cancel']."</button>
                </td></tr></table>
            </fieldset>";

        break;

    case 'simpangrtt':
            
        $str = "insert into " . $dbname . ".keu_bukucekdt (notrans_cek,nocek,status_cek,tujuan,tglcair,penerima)
                values ('".$notrans_cek."','".$nocek_awal."','1','".$tujuan."','".$tglcair."','".$penerima."')";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }   

        break;

    case 'closed':

        $strht = "update ".$dbname.".keu_bukucekht set status='2' where notrans_cek='".$notrans_cek."'";             
        try
        {
            $owlPDO->exec($strht);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;
	
	 case 'unclosed':

        $strht = "update ".$dbname.".keu_bukucekht set status='1' where notrans_cek='".$notrans_cek."'";             
        try
        {
            $owlPDO->exec($strht);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;
    
    default:
        # code...
        break;
}


?>