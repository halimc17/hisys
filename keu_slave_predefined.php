<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$tanggalmulai=checkPostGet('tanggalmulai','');
$tanggalselesai=checkPostGet('tanggalselesai','');
$arnotrans=checkPostGet('arr','');
$status=checkPostGet('status','');

$optP=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
switch($proses){

	case 'getunit':
		$optakun="<option value=''>".$_SESSION['lang']['pilihsemua']."</option>";
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
			 where induk='".$pt."' and length(kodeorganisasi)='4' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optakun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." : ".$bar['namaorganisasi']."</option>";
		}

		echo $optakun;
	break;

	case 'changeflag':
		$tmpC=explode('###',$arnotrans);
		//echo $arnotrans;
		/*print_r($tmpC);
		exit();*/
		foreach ($tmpC as $key => $value) {
			$whereNo = "notransaksi='".$value."'";
			$updData = array('predefined'=>'1');
	        $str = updateQuery($dbname,'keu_kasbankht',$updData,$whereNo);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		}
		
		$query = selectQuery($dbname,"keu_predefined","notransaksi");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $row['notransaksi']>=$maxid ? $maxid=$row['notransaksi'] : false;
        }
        $maxid++;
        }

		$str="INSERT INTO `keu_predefined` (`notransaksi`, `kodept`, `kodeunit`, `tanggalmulai`,
											`tanggalselesai`, `tanggalprosess`, `prosessby`, `arrnotrans`, `status`)
		VALUES ('".$maxid."', '".$pt."','".$unit."','".tanggalsystem($tanggalmulai)."','".tanggalsystem($tanggalselesai)."','".date("Y-m-d H:i:s")."','".$_SESSION['standard']['userid']."','".$arnotrans."','".$status."')";
		try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !:  " .$str."". $e->getMessage() . "\n"; 
            die(); 
        }
		
	break;

	case 'loadpredefined':
		$limit = 3;
	    $page = 0;
	    if (isset($_POST['page'])) {
	        $page = $_POST['page'];
	        if ($page < 0)
	            $page = 0;
	    }
	    $offset = $page * $limit;

	    $qcount ="select * from ".$dbname.".keu_predefined";
	    $rcount = fetchData($qcount);
	    $jlhbrs = count($rcount);

	    $totalPage = ceil($jlhbrs/$limit);
	    $optPage = array();
	    $totalPage<1 ? $totalPage=1 : null;
	    for($i=1;$i<=$totalPage;$i++) {
	        $optPage[$i-1] = $i;
	    }

		$optKaryawan= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$str="select * from ".$dbname.".keu_predefined order by tanggalprosess desc limit " . $offset . "," . $limit . "";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);

		@$no=1;
		$stream = "<table class=sortable cellspacing=1 >";
		$stream .= "<thead><tr class=rowheader><td>Perusahaan</td><td>Unit</td><td>Tanggal Mulai</td><td>Tanggal Selesai</td><td>Prosess By</td><td>Prosess Date</td><td>status</td><td>Waktu Pembayaran</td><td>Document</td></tr></thead><tbody>";
		while($bar=$res->fetch()){
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td id='kodept".$no."' style='width:120px'>".$optP[$bar['kodept']]."</td>";
		$stream .= "<td hidden id='kodept_".$no."'>".$bar['kodept']."</td>";
		$stream .= "<td id='kodeunit".$no."'>".$optP[$bar['kodeunit']]."</td>";
		$stream .= "<td hidden id='kodeunit_".$no."'>".$bar['kodeunit']."</td>";
		$stream .= "<td id='tanggalmulai_".$no."'>".tanggalnormal($bar['tanggalmulai'])."</td>";
		$stream .= "<td id='tanggalselesai_".$no."'>".tanggalnormal($bar['tanggalselesai'])."</td>";
		$stream .= "<td id='prosessby_".$no."'>".$optKaryawan[$bar['prosessby']]."</td>";
		$stream .= "<td id='tanggalprosess_".$no."'>".date('d-m-Y H:i:s',strtotime($bar['tanggalprosess']))."</td>";
		$stream .= "<td id='status_".$no."'>".$bar['status']."</td>";
		$stream .= "<td id='datetimex_".$no."'><input id='tanggalkirim_".$no."' name='tanggalkirim_".$no."' class='myinputtext' onkeypress='return false;' onmouseover=setCalendar(this,'%Y%m%d%H%M%S') size=20 readonly=readonly for=Waktu type=text></td>";
		$stream .= "<td align=center><a href='#' onclick=dataKeExcel(event,'keu_slave_predefined.php','".$bar['arrnotrans']."','".$no."')><img  src=images/excel.jpg class=resicon title='MS.Excel'></a></td>";
		$stream .= "</tr>";
		$no++;
		}
		$stream .= "</tbody>";
		$stream .="<tfoot><td colspan=9 align=center>
    <img src='images/".$_SESSION['theme']."/first.png'style='cursor:pointer' onclick=cariBast(0);>&nbsp;
    <img src='images/".$_SESSION['theme']."/prev.png'style='cursor:pointer' onclick=cariBast(" . ($page - 1) . ");>&nbsp;
    ".makeElement('pages','select',$page,array('style'=>'width:50px',
        'onchange'=>'cariBast(this.value)'),$optPage)."&nbsp;
    <img src='images/".$_SESSION['theme']."/next.png'style='cursor:pointer' onclick=cariBast(" . ($page + 1) . ");>&nbsp;
    <img src='images/".$_SESSION['theme']."/last.png'style='cursor:pointer' onclick=cariBast(".($totalPage-1).");>
    </td>
    </tfoot></table>";
		echo $stream;
	break;

	case'preview':
	
	if($pt=='' || $tanggalmulai=='' || $tanggalselesai==''){
		exit("Warning:Lengkapi Pengisian");
	}

	$str="SELECT a.notransaksi, b.kodesupplier , e.namasupplier , c.rekening ,c.idbank, c.an , c.email,  
		  c.negara as kodenegara, d.alamat, d.negara ,a.matauang FROM ".$dbname.".keu_kasbankht a
		  left join ".$dbname.".keu_kasbankdt b on b.notransaksi=a.notransaksi
		  left join ".$dbname.".log_5supplier e on b.kodesupplier=e.supplierid
		  left join ".$dbname.".log_5rekbank c on b.kodesupplier=c.supplierid
		  left join ".$dbname.".log_5supalamat d on b.kodesupplier=d.supplierid
		  where a.cgttu = 'Transfer' and a.posting='1' and a.tanggal >= '".tanggalsystem($tanggalmulai)."' 
		  and a.tanggal <= '".tanggalsystem($tanggalselesai)."' and a.predefined='0' and ";

	if($unit==''){
		$str .="(";
		$strx="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi
			 where induk='".$pt."' and length(kodeorganisasi)='4' ";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$no=1;
		while($barx=$resx->fetch()){
			if($no==1)
			{
			$str .=" a.kodeorg='".$barx['kodeorganisasi']."'";
			}
			else
			{
			$str .="or a.kodeorg='".$barx['kodeorganisasi']."'";
			}
		$no++;
		}
		$str .=")";
	}
	else
	{
		$str.=" a.kodeorg='".$unit."'";
	}

	if($status == 'Domestic')
	{
		$str .=" and c.idbank !='10002'";
	}
	else
	{
		$str .=" and c.idbank ='10002'";
	}
	#cek penguncian tutup buku 
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$jlh = count($res);
	
	#bentuk nodokumen
	if($status=='Domestic')
	{
	
			
			$stream = "<table class=sortable cellspacing=1>";
			$stream.="<thead><tr class=rowheader id=rowx>";
			$stream.="<td >Kode Supplier</td>";
			$stream.="<td >Nama Supplier</td>";
			$stream.="<td >No Rekening</td>";
			$stream.="<td >Nama Pemilik Rekening</td>";
			$stream.="<td >Email</td>";
			$stream.="<td >Alamat</td>";
			$stream.="<td >Negara</td>";
			$stream.="<td >Kode Negara</td>";
			$stream.="<td >Kode Rupiah</td>";
			$stream.="<td >Kode Rtgs</td>";
			$stream.="<td >*</td>";
			$stream.="</tr></thead>";

			$arr='';
			while($bar=$res->fetch()){
				$no=1;
				
				
				if($bar['kodesupplier']!='')
				{
					if($no==1)
					{
						$arr=$bar['notransaksi'];
					}
					else
					{
						$arr.='###'.$bar['notransaksi'];
					}
					$stream.="<tbody><tr class=rowcontent id=row".$no.">";
					$stream.="<td >".$bar['kodesupplier']."</td>";
					$stream.="<td >".$bar['namasupplier']."</td>";
					$stream.="<td >".$bar['rekening']."</td>";
					$stream.="<td >".$bar['an']."</td>";
					$stream.="<td >".$bar['email']."</td>";
					$stream.="<td >".$bar['alamat']."</td>";
					$stream.="<td >".$bar['negara']."</td>";
					$stream.="<td >".$bar['kodenegara']."</td>";
					if(trim($bar['matauang'])=='IDR')
					{
					$stream.="<td >".$bar['matauang']."T</td>";
					}
					else
					{
					$stream.="<td >".$bar['matauang']."</td>";
					}
					$strbank="SELECT kodertgs FROM ".$dbname.".keu_5daftarbankdt 
								WHERE kodebank = '".$bar['idbank']."'";
					$resbank=$owlPDO->query($strbank) or die(print " Gagal: ".PDOException::getMessage());
					$resbank->setFetchMode(PDO::FETCH_ASSOC);
					$barbank=$resbank->fetch();
					$stream.="<td >".$barbank['kodertgs']."</td>";
					$stream.="<td >".makeElement('input_'.$no,'checkbox')."</td>";
				$stream.="</tr></tbody>";
				$no++;
				}
			}
		}
		else
		{
			
			$stream = "<table class=sortable cellspacing=1>";
			$stream.="<thead><tr class=rowheader id=rowx>";
			$stream.="<td >Kode Supplier</td>";
			$stream.="<td >Nama Supplier</td>";
			$stream.="<td >No Rekening</td>";
			$stream.="<td >Nama Pemilik Rekening</td>";
			$stream.="<td >Email</td>";
			$stream.="<td >*</td>";
			$stream.="</tr></thead>";

			$arr='';
			while($bar=$res->fetch()){
				$no=1;
				
				
				if($bar['kodesupplier']!='')
				{
					if($no==1)
					{
						$arr=$bar['notransaksi'];
					}
					else
					{
						$arr.='###'.$bar['notransaksi'];
					}
				$stream.="<tbody><tr class=rowcontent id=row".$no.">";
					$stream.="<td >".$bar['kodesupplier']."</td>";
					$stream.="<td >".$bar['namasupplier']."</td>";
					$stream.="<td >".$bar['rekening']."</td>";
					$stream.="<td >".$bar['an']."</td>";
					$stream.="<td >".$bar['email']."</td>";
					$stream.="<td >".makeElement('input_'.$no,'checkbox')."</td>";
				$stream.="</tr></tbody>";
				$no++;
				}
			}
		}
		
		//Jurnal Plasma
		
		
		$stream.="<button class=mybutton onclick=changeflag('".$arr."','".$tanggalmulai."','".$tanggalselesai."','".$pt."','".$unit."','".$status."');>".$_SESSION['lang']['proses']."</button>";	

		echo $stream;
    break;

    case'excel':
    $arnotrans=checkPostGet('arr','');
    $tglkirim=checkPostGet('tanggalkirim','');
    $status=checkPostGet('status','');

	$tmpC=explode('###',$arnotrans);
		$str="SELECT a.notransaksi, b.kodesupplier , e.namasupplier , c.rekening ,c.idbank, c.an , c.email,  
		  c.negara as kodenegara, d.alamat, d.negara ,a.matauang FROM ".$dbname.".keu_kasbankht a
		  left join ".$dbname.".keu_kasbankdt b on b.notransaksi=a.notransaksi
		  left join ".$dbname.".log_5supplier e on b.kodesupplier=e.supplierid
		  left join ".$dbname.".log_5rekbank c on b.kodesupplier=c.supplierid
		  left join ".$dbname.".log_5supalamat d on b.kodesupplier=d.supplierid
		  where  ";
		  $no=1;
		foreach ($tmpC as $key => $value) {
			if($no==1)
			{
			$str .=" a.notransaksi='".$value."' ";
			}
			else
			{
			$str .="or a.notransaksi='".$value."' ";
			}
		$no++;
		}

	
	
	#cek penguncian tutup buku 
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$jlh = count($res);
	
	#bentuk nodokumen
	
	if($status=='Domestic')
	{
	
		$stream = "<table class=sortable cellspacing=1 border=1>";
		$stream.="<tr class=rowcontent id=rowx>";
		$stream.="<td >'".$tglkirim."</td>";
		$stream.="<td >".$jlh."</td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="<td ></td>";
		$stream.="</tr>";

		$arr='';
		while($bar=$res->fetch()){
			$no=1;
			
			
			if($bar['kodesupplier']!='')
			{
				if($no==1)
				{
					$arr=$bar['notransaksi'];
				}
				else
				{
					$arr.='###'.$bar['notransaksi'];
				}
			$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td >'".$bar['kodesupplier']."</td>";
				$stream.="<td >'".$bar['rekening']."</td>";
				$stream.="<td >".$bar['an']."</td>";
				if($bar['email']=='')
				{
					$stream.="<td >N</td>";
				}
				else
				{
					$stream.="<td >Y</td>";
				}

				$stream.="<td >".$bar['email']."</td>";
				$stream.="<td >".$bar['alamat']."</td>";
				$stream.="<td >".$bar['negara']."</td>";
				$stream.="<td >&nbsp;&nbsp;&nbsp;</td>";
				$stream.="<td >".$bar['kodenegara']."</td>";
				if(trim($bar['matauang'])=='IDR')
				{
				$stream.="<td >".$bar['matauang']."T</td>";
				}
				else
				{
				$stream.="<td >".$bar['matauang']."</td>";
				}
				$strbank="SELECT kodertgs FROM ".$dbname.".keu_5daftarbankdt 
							WHERE kodebank = '".$bar['idbank']."'";
				$resbank=$owlPDO->query($strbank) or die(print " Gagal: ".PDOException::getMessage());
				$resbank->setFetchMode(PDO::FETCH_ASSOC);
				$barbank=$resbank->fetch();
				$stream.="<td >".$barbank['kodertgs']."</td>";
			$stream.="</tr>";
			$no++;
			}
		}
	}
	else
	{
		$stream = "<table class=sortable cellspacing=1 border=1>";
		$stream.="<tr class=rowcontent id=rowx>";
		$stream.="<td >'".$tglkirim."</td>";
		$stream.="<td >".$jlh."</td>";
		$stream.="<td ></td>";
		$stream.="</tr>";

		$arr='';
		while($bar=$res->fetch()){
			$no=1;
			
			
			if($bar['kodesupplier']!='')
			{
				if($no==1)
				{
					$arr=$bar['notransaksi'];
				}
				else
				{
					$arr.='###'.$bar['notransaksi'];
				}
			$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td >'".$bar['rekening']."</td>";
				if($bar['email']=='')
				{
					$stream.="<td >N</td>";
				}
				else
				{
					$stream.="<td >Y</td>";
				}

				$stream.="<td >".$bar['email']."</td>";
			$stream.="</tr>";
			$no++;
			}
		}
	}
		
		$tglSkrg = date("Ymd");
        $nop_ = "Predefined_";
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
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
    break;
}