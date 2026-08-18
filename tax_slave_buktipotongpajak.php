<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
// $proses = $_GET['proses'];
$proses=checkPostGet('proses','');
$data = $_POST;
$idOrg=substr($_SESSION['empl']['lokasitugas'],0,4);
$nmpenghasilan=makeOption($dbname,'pmn_5jenispenghasilan','idpenghasilan,namapenghasilan');
$nmsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');



$optjenis="";
$str="select * from ".$dbname.".pmn_5jenispenghasilan where kodepajak like '2%' 
	and idpenghasilan not in (select idparent from ".$dbname.".pmn_5jenispenghasilan where kodepajak like '2%') 
	and idparent=0 and kodepajak='".@$data['noakun']."' order by idpenghasilan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optjenis.="<option value=".$bar['idpenghasilan'].">".$bar['namapenghasilan']."</option>";
	
}
$arrJenis=array("2130201"=>"p22","2130301"=>"p23","2130101"=>"p21","2130401"=>"ps4","2130901"=>"p25","2130902"=>"p26","2130903"=>"p15");
$arrPil=array("2130201"=>"41","2130301"=>"p23","2130101"=>"p21","2130401"=>"ps4","2130901"=>"p25","2130902"=>"p26","2130903"=>"p15");

$str="select * from ".$dbname.".pmn_5jenispenghasilan where kodepajak like '2%' 
	and idparent!=0  and kodepajak='".@$data['noakun']."' order by idpenghasilan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($arrPil[$data['noakun']]==$bar['idpenghasilan']){
		$optjenis.="<option value=".$bar['idpenghasilan']." selected>".$nmpenghasilan[$bar['idparent']]." - ".$bar['namapenghasilan']."</option>";
	}else{
		$optjenis.="<option value=".$bar['idpenghasilan'].">".$nmpenghasilan[$bar['idparent']]." - ".$bar['namapenghasilan']."</option>";	
	}
	
}
$optJenis2=array();
$sJenis="select * from ".$dbname.".keu_5jenistagihan where status=1";
$rJenis=fetchData($sJenis);
foreach($rJenis as $row=>$data){
    if($data['jurnal']==1){
        $optJenis2[$data['kode']].="NVM : ".$data['namajenis']."";
    }
    else{
        $optJenis2[$data['kode']].="VM : ".$data['namajenis']."";
    }
}
// $arrtax=array(
// "2130101"=>"PPH21",
// "2130201"=>"PPH22",
// "2130301"=>"PPH23",
// "2130401"=>"PPHA4",
// "2130501"=>"PPH29");


$str="select * from ".$dbname.".keu_5akunpajak";
$res=fetchdata($str);
foreach($res as $bar){
	$arrtax[$bar['noakun']]=$bar['kodepajak'];
}



$pt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"char_length(kodeorganisasi)=4");
$inisial=makeOption($dbname,'setup_org_npwp','kodeorg,inisial');
$lstUnit=getOrgDetail(1);
$mulaiaj=0;
foreach($lstUnit as $row=>$data){
   if(substr($row,0,5)=='Pilih'){
        continue;
    }
    if($mulaiaj==0){
        $listOrg="'".$row."'";
        $mulaiaj=1;
    }else{
        $listOrg.=",'".$row."'";
    }
}

// exit("Error:$proses");
switch($proses) {
    case 'loadData' :
    $data=$_POST;
    $limit = 20;
    $page = 0;
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
        if ($page < 0)
            $page = 0;
    }
    $whr="";
    if($data['noakuncr']!=""){
    	$whr.=" and noakun='".$data['noakuncr']."'";
    }
    if($data['periode']!=""){
    	$whr.=" and periode='".$data['periode']."'";
    }
    if($data['kodeorg']!=""){
    	$whr.=" and kodeorg='".$data['kodeorg']."'";
    }
    if($data['nokasCr']!=""){
        $whr.=" and notrans_kasbank like '%".$data['nokasCr']."%'";
    }
    if($data['noinvCr']!=""){
        $whr.=" and noinvoice like '%".$data['noinvCr']."%'";
    }
    if($data['supplierIdKppcr']!=""){
        $whr.=" and supplierid_kpp='".$data['supplierIdKppcr']."'";
    }
    $offset = $page * $limit;
	
    $qcount="select * from ".$dbname.".tax_buktipotongpajak where 1=1 and kodeorg in (".$listOrg.") ".$whr." order by periode desc ";
    $rcount = fetchData($qcount);
    $jlhbrs = count($rcount);

    $totalPage = ceil($jlhbrs/$limit);
    $optPage = array();
    $totalPage<1 ? $totalPage=1 : null;
    for($i=1;$i<=$totalPage;$i++) {
        $optPage[$i-1] = $i;
    }
    $queryAll="select * from ".$dbname.".tax_buktipotongpajak where 1=1 and kodeorg in (".$listOrg.") ".$whr." order by periode desc limit " . $offset . "," . $limit . "";
 	$resAll = fetchData($queryAll);
	$header = array($_SESSION['lang']['nourut'],$_SESSION['lang']['periode'],"No Bukti<br>Potong",
				"Realisasi<br>Kas Bank",
				"Nomor<br>Voucher<br>Kas Bank",
				$_SESSION['lang']['noakun'],
				$_SESSION['lang']['namasupplier'],
				$_SESSION['lang']['noinvoice'],
				$_SESSION['lang']['nodok'],
				$_SESSION['lang']['nofp'],
				"DPP",
				"PPh",
				"Kompensasi",
				$_SESSION['lang']['noinvoice']." Ref");
				
				//"Jenis<br>Pajak");
   
    $table ="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
    $table .="<legend>List Data Pajak</legend>";
    $table .= "<table id='listData' class='sortable' cellspacing='1' border='0' width=100% >";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td align=center>".$head."</td>";
    }
    $table .= "<td align=center colspan=5>Kas Negara</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody id='bodyList'>";
	 $no=$offset;	
	
    foreach ($resAll as $key => $row) {
		
		#= keu_tagihanht
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$row['noinvoice']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$nofp=$bar['nofp'];
			$nopo=$bar['nopo'];
		}

		
		$no++;
        $optTanggal=makeOption($dbname,"keu_kasbankht","notransaksi,tanggal","notransaksi='".$row['notrans_kasbank']."'");
		$optNmAkun=makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$row['noakun']."'");
		$optNmSup=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$row['kodesupplier']."'");
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
		$table .= "<td align=center>".($no)."</td>";
		$table .= "<td id='nourut' hidden align=center>".($key+1)."</td>";
		$table .= "<td>".$row['periode']."</td>";
		$table .= "<td>".$row['no_buktipotong']."</td>";
		$table .= "<td>".tanggalnormal($optTanggal[$row['notrans_kasbank']])."</td>";
		$table .= "<td>".$row['notrans_kasbank']."</td>";
		$table .= "<td>".$optNmAkun[$row['noakun']]."</td>";
		$table .= "<td>".$optNmSup[$row['kodesupplier']]."</td>";
		$table .= "<td>".$row['noinvoice']."</td>";
		$table .= "<td>".$nopo."</td>";
		$table .= "<td>".$nofp."</td>";
		//$table .= "<td align=right>".@number_format($resakun[0]['tarif_pajak'])."</td>";
		$table .= "<td align=right>".@number_format($row['tarif_pajak'])."</td>";
		//$table .= "<td align=right>".@number_format($resakun[0]['tarif_pajak']+$nilppn[$row['keterangan1']])."</td>";
		$table .= "<td align=right>".number_format($row['nilai'])."</td>";
		$table .= "<td align=right>".@number_format($row['kompensasi'])."</td>";
		// $table .= "<td>".@$nmpenghasilan[$row['jenis_penghasilan']]."</td>";
		
		
		$table .= "<td>".$row['noinvoice_ref']."</td>";
		$optNmSupp=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$row['supplierid_kpp']."'");
			$table .= "<td align=center>";
			$table.="<img src=images/skyblue/zoom.png class=zImgBtn onclick=\"viewdetail('".$row['periode']."','".$row['kodeorg']."','".$row['noakun']."','".$row['supplierid_kpp']."');\" title='Print Data Detail ".$optNmSupp[$row['supplierid_kpp']]."'>";
			$table .= "</td>";
			#= tombol posting
			if($row['posting']==0){

			 // $table.="<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' 
    //             onclick=\"posting('" . $row['kodesupplier'] . "','" .$row['periode'] . "','" .$row['noakun'] . "','" .$row['kodeorg'] . "');\" >";
			 $table .= "<td align=center>";
			 $table.="<img src=images/application/application_delete.png class=resicon  title='Delete' class=zImgBtn height='30'
                onclick=\"deleteht('" . $row['kodesupplier'] . "','" .$row['periode'] . "','" .$row['noakun'] . "','" .$row['kodeorg'] . "','" . $row['no_buktipotong'] . "');\" >";
                $table .= "</td>";
                $table .= "<td align=center colspan=3>&nbsp;</td>";
			}else{
				$table .= "<td align=center>";
				$table.="<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='posted'>";
				$table .= "</td>";
				$table .= "<td align=center>";
				$table.="<img src=images/pdf.jpg class=resicon  title='Rekap Bukti Potong ".$row['periode']."' onclick=\"pdf('".$row['no_buktipotong']."','".$row['periode']."','".$row['kodeorg']."');\">";
				$table .= "</td>";
				$table .= "<td align=center>";
				$table.="<img src=images/pdf.jpg class=resicon  title='Bukti Potong PPH ".$row['periode']."' onclick=\"pdfpph('".$row['no_buktipotong']."','".$row['periode']."','".$row['kodeorg']."');\">";
				$table .= "</td>";
				$table .= "<td align=center>";
				$table.="<img src=images/pdf.jpg class=resicon  title='Nota Hutang Pajak ".$row['periode']."' onclick=detailPDF('".$row['noinvoice_ref']."',event);>";
				$table .= "</td>";
			}
			
            $table .= "</tr>";
    }
    $table .= "</tbody>";
    $table .="<tfoot><td colspan=17 align=center>
    <img src='images/".$_SESSION['theme']."/first.png'style='cursor:pointer' onclick=cariBast(0);>&nbsp;
    <img src='images/".$_SESSION['theme']."/prev.png'style='cursor:pointer' onclick=cariBast(" . ($page - 1) . ");>&nbsp;
    ".makeElement('pages','select',$page,array('style'=>'width:50px',
        'onchange'=>'cariBast(this.value)'),$optPage)."&nbsp;
    <img src='images/".$_SESSION['theme']."/next.png'style='cursor:pointer' onclick=cariBast(" . ($page + 1) . ");>&nbsp;
    <img src='images/".$_SESSION['theme']."/last.png'style='cursor:pointer' onclick=cariBast(".($totalPage-1).");>
    </td>
    </tfoot>";
    $table .= "</table>";
    $table .= "</fieldset>";

    echo $table;
    break;
	
	
    case'viewdetail':
    $data=$_POST;
    if($_POST['proses']==""){
    	$data=$_GET;	
    }
   $queryAll2 ="select a.notransaksi, a.noakun, b.namaakun,a.keterangan1 as noinvoice,a.jumlah, a.kodesupplier, c.namasupplier 
			   from ".$dbname.".keu_kasbankdt a ";
   $queryAll2 .=" Left join keu_5akun b on a.noakun=b.noakun";
   $queryAll2 .=" Left join log_5supplier c on a.kodesupplier=c.supplierid";
   $queryAll2 .=" Left join keu_kasbankht d on a.notransaksi=d.notransaksi";
   $queryAll2 .=" WHERE a.noakun='".$data['noakun']."' and d.posting=1 and d.tanggal like '".$data['periode']."%' and d.kodeorg='".$data['kodeorg']."'";
   $queryAll2 .=" order by a.kodesupplier desc";
   $resAll2=fetchData($queryAll2);
   // echo"<pre>";
   // print_r($resAll2);
   // echo"</pre>";

    $queryAll="select * from ".$dbname.".tax_buktipotongpajak where 1=1 and kodeorg in (".$listOrg.") 
               and periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and noakun='".$data['noakun']."' and supplierid_kpp='".$data['kodesupplier']."'";
 	$resAll = fetchData($queryAll);
	$header = array($_SESSION['lang']['nourut'],$_SESSION['lang']['periode'],"No Bukti<br>Potong",
				"Nomor<br>Voucher<br>Kas Bank",
				"Realisasi<br>Kas Bank",
				$_SESSION['lang']['keterangan'].' '.$_SESSION['lang']['kasbank'],
				$_SESSION['lang']['noakun'],
				$_SESSION['lang']['namasupplier'],
				$_SESSION['lang']['noinvoice'],
				$_SESSION['lang']['keterangan'].' '.$_SESSION['lang']['invoice'],
				"DPP",
				"PPh",
				"Kompensasi");
	
	if($data['viewtipe']=='excel'){
			$brd="1";
	}else{
		 $brd="0";
		 $queryAll2="select * from ".$dbname.".tax_buktipotongpajak where 1=1 and kodeorg in (".$listOrg.") 
                       and periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."' and noakun='".$data['noakun']."' and posting=0";
		 $resAll2 = fetchData($queryAll2);
		if(count($resAll2)>0){
			$table.="<div align=right style=background-color: coral;><button class=mybutton onclick=posting('".$data['periode']."','".$data['kodeorg']."','".$data['noakun']."','".$data['kodesupplier']."')>".$_SESSION['lang']['posting']."</button></div>";	
		}
		 $table.="<p align=left><img title='Detail Excel' onclick=dataexceldetail(event,'".$data['periode']."','".$data['kodeorg']."','".$data['noakun']."','".$data['kodesupplier']."','tax_slave_buktipotongpajak.php') src=images/excel.jpg class=resicon></p>";	
		 $table .="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
    	 $table .="<legend>List Data Pajak</legend>";
	}
   	
    $table .= "<table id='listData' class='sortable' cellspacing='1' border='".$brd."' width=100% >";
    $table .= "<thead><tr class='rowheader'>";
    foreach($header as $head) {
        $table .= "<td align=center>".$head."</td>";
    }
    $table .= "<td align=center>KPP</td>";
    $table .= "</tr></thead>";
    $table .= "<tbody id='bodyList'>";
    foreach ($resAll as $key => $row) {
        $optTanggal=makeOption($dbname,"keu_kasbankht","notransaksi,tanggal","notransaksi='".$row['notrans_kasbank']."'");
        $optKet=makeOption($dbname,"keu_kasbankht","notransaksi,keterangan","notransaksi='".$row['notrans_kasbank']."'");
		$optNmAkun=makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$row['noakun']."'");
		$optNmSup=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$row['kodesupplier']."'");
		$optNmSupKpp=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$row['supplierid_kpp']."'");
		$optNmKetTagihan=makeOption($dbname,"keu_tagihanht","noinvoice,keterangan","noinvoice='".$row['noinvoice']."'");
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
		$table .= "<td id='nourut' align=center>".($key+1)."</td>";
		$table .= "<td>".$row['periode']."</td>";
		$table .= "<td>".$row['no_buktipotong']."</td>";
		$table .= "<td>".$row['notrans_kasbank']."</td>";
		$table .= "<td>".tanggalnormal($optTanggal[$row['notrans_kasbank']])."</td>";
		$table .= "<td>".$optKet[$row['notrans_kasbank']]."</td>";
		$table .= "<td>".$optNmAkun[$row['noakun']]."</td>";
		$table .= "<td>".$optNmSup[$row['kodesupplier']]."</td>";
		$table .= "<td>".$row['noinvoice']."</td>";
		$table .= "<td>".$optNmKetTagihan[$row['noinvoice']]."</td>";
		$table .= "<td align=right>".@number_format($row['tarif_pajak'])."</td>";
		$table .= "<td align=right>".number_format($row['nilai'])."</td>";
		$table .= "<td align=right>".@number_format($row['kompensasi'])."</td>";
		// $table .= "<td>".@$nmpenghasilan[$row['jenis_penghasilan']]."</td>";
		$table .= "<td align=center>".$optNmSupKpp[$row['supplierid_kpp']]."</td>";
        $table .= "</tr>";
        $totalPph+=$row['nilai'];
        $totalDpp+=$row['tarif_pajak'];
        $compare[$row['notrans_kasbank']][$row['noinvoice']]=$row['noinvoice'];
        $no=$key;
    }
    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
	$table .= "<td colspan=10>".$_SESSION['lang']['total']."</td>";
	 
	//$table .= "<td align=right>".@number_format($resakun[0]['tarif_pajak'])."</td>";
	$table .= "<td align=right>".@number_format($totalDpp)."</td>";
	//$table .= "<td align=right>".@number_format($resakun[0]['tarif_pajak']+$nilppn[$row['keterangan1']])."</td>";
	$table .= "<td align=right>".number_format($totalPph)."</td>";
	$table .= "<td align=right>".@number_format($row['kompensasi'])."</td>";
	$table .= "<td>&nbsp;</td>";
    $table .= "</tr>";
 //    $no=0;
 //    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
	// $table .= "<td colspan=13>&nbsp;</td></tr>";
 //    foreach ($resAll2 as $key => $val) {
 //    	if(isset($compare[$val['notransaksi']][$val['noinvoice']])){
 //    		continue;
 //    	}
 //    	$no+=1;
 //    	$optTanggal=makeOption($dbname,"keu_kasbankht","notransaksi,tanggal","notransaksi='".$val['notransaksi']."'");
		
 //    	$table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
	// 	$table .= "<td id='nourut' align=center>".$no."</td>";
	// 	$table .= "<td>&nbsp</td>";
	// 	$table .= "<td>&nbsp</td>";
	// 	$table .= "<td>".tanggalnormal($optTanggal[$val['notransaksi']])."</td>";
	// 	$table .= "<td>".$val['notransaksi']."</td>";
	// 	$table .= "<td>".$val['noakun']."</td>";
	// 	$table .= "<td>".$val['namasupplier']."</td>";
	// 	$table .= "<td>".$val['noinvoice']."</td>";
	// 	$table .= "<td align=right>".@number_format($val['tarif_pajak'])."</td>";
	// 	$table .= "<td align=right>".number_format($val['nilai'])."</td>";
	// 	$table .= "<td align=right>".@number_format($val['kompensasi'])."</td>";
	// 	$table .= "<td>&nbsp;</td>";
	// 	$table .= "<td align=center>&nbsp;</td>";
 //        $table .= "</tr>";
 //    }
   
    $table .= "</tbody>";
    $table .= "</table>";
    	if($data['viewtipe']=='html'){
    		$table .= "</fieldset>";
    		
    		echo $table;
    	}
    	if($data['viewtipe']=='excel') { 
            $sNet="select  induk from  ".$dbname.".organisasi  
                    where kodeorganisasi='".$data['kodeorg']."'";
            $rNet=fetchData($sNet);
            $tglSkrg = date("Ymd");
            $nop_ = "Detail_".$rNet[0]['induk']."_bupot_".$tglSkrg;
            if (strlen($table) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $table)) {
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
        } 
    break;
    case'loadData2':
    if($_GET['kodeorg']!=''){
    		$data=$_GET;
    }
    if($_POST['kodeorg']!=''){
    	$data=$_POST;
    }
  
	    $resAll=array();
		$where='';
		if($data['supplier']!=''){
			$where.=" and kodesupplier='".$data['supplier']."'";
		}
		$optInduk=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$data['kodeorg']."'");
    	// $sData="select a.noinvoice as noinv,nilai from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b
    	//         on a.noinvoice=b.noinvoice
    	//         where a.noakun='".$data['noakun']."' and left(b.tanggal,4)='".substr($data['periode'],0,4)."'  
		//			and kodeorg='".$optInduk[$data['kodeorg']]."' 
    	//         and b.tipeinvoice<>'".$arrJenis[$data['noakun']]."' ".$where." order by b.kodesupplier asc";
    	// //echo $sData;
    	// $rData=fetchData($sData);
    	// foreach ($rData as $key => $val) {
			/*
                $periode1=substr($data['periode'],0,4).'-01';
			   $queryAll ="select a.notransaksi, a.noakun, b.namaakun,a.keterangan1 as noinvoice,a.jumlah, a.kodesupplier, c.namasupplier,d.tanggal 
						   from ".$dbname.".keu_kasbankdt a ";
			   $queryAll .=" Left join keu_5akun b on a.noakun=b.noakun";
			   $queryAll .=" Left join log_5supplier c on a.kodesupplier=c.supplierid";
			   $queryAll .=" Left join keu_kasbankht d on a.notransaksi=d.notransaksi";
			   $queryAll .=" WHERE a.noakun='".$data['noakun']."' and d.posting=1 and d.kodeorg='".$data['kodeorg']."'";
               $queryAll .=" and a.kodesupplier not in (select supplierid from ".$dbname.".log_5supkelompok where tipe='PAJAK')";
               //$queryAll .=" and left(d.tanggal,7) between '".$periode1."' and '".$data['periode']."'";
			   $queryAll .=" order by d.tanggal desc,a.kodesupplier desc";
			   //echo $queryAll;
			   $resAll = fetchData($queryAll);
			   //$resAll[$val['noinv']]=$res;
			   */
    	//}
       //echo count($resAll);
	   
	   // $sData="select a.noinvoice as noinv,nilai,noakun from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b
    	//         on a.noinvoice=b.noinvoice
    	//         where a.noakun='".$data['noakun']."' and left(b.tanggal,4)='".substr($data['periode'],0,4)."'  
		//			and kodeorg='".$optInduk[$data['kodeorg']]."' 
    	//         and b.tipeinvoice<>'".$arrJenis[$data['noakun']]."' ".$where." order by b.kodesupplier asc";
    	// //echo $sData;
    	// $rData=fetchData($sData);
		
		
		#= query yg dipakai
		$queryAll=" select a.noakun,a.noinvoice,a.nilai as jumlah,b.kodesupplier,b.tanggal,b.keterangan as keterangan,b.unit,
					c.namaakun,d.namasupplier,b.nopo as nopo,b.nofp as nofp
					from ".$dbname.".keu_tagihandt a 
					left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
					left join ".$dbname.".keu_5akun c on a.noakun=c.noakun
					Left join log_5supplier d on b.kodesupplier=d.supplierid
					WHERE a.noakun='".$data['noakun']."' and b.posting=1 and b.kodeorg='".$optInduk[$data['kodeorg']]."' 
					and b.kodesupplier not in (select supplierid from ".$dbname.".log_5supkelompok where tipe='PAJAK')
					".$where."
					order by b.tanggal desc,b.kodesupplier desc";
					// print_r($data);
					// echo $queryAll;
	   $resAll = fetchData($queryAll);
// echo "<pre>";
// print_r($resAll);
// echo "</pre>";
            foreach ($resAll as $key => $valdt) {
                $sDtDpp="select * from ".$dbname.".keu_tagihanht where noinvoice='".$valdt['noinvoice']."'";
                $rDtDpp=fetchData($sDtDpp);
                $nilaiDpp=$rDtDpp[0]['nilaidpp'];
                $sDtDpp2="select * from ".$dbname.".keu_tagihandt where noinvoice='".$valdt['noinvoice']."' and noakun='1170111'";
                $rDtDpp2=fetchData($sDtDpp2);
				
				#= nilai pph
				 $sDtDpp3="select * from ".$dbname.".keu_tagihandt where noinvoice='".$valdt['noinvoice']."' and left(noakun,3)='213'";
                $rDtDpp3=fetchData($sDtDpp3);
				
                $nilaiPpn=$rDtDpp2[0]['nilai'];
                $nilaiPph=$rDtDpp3[0]['nilai'];
                $optDpp[$rDtDpp[0]['noinvoice']]=$nilaiDpp;
				
				// echo $nilaiDpp._.$nilaiPpn._.$nilaiPph;
				
                // if(substr($optJenis2[$rDtDpp[0]['tipeinvoice']],0,3)=="NVM"){
				
                    // $optDpp[$rDtDpp[0]['noinvoice']]=$nilaiDpp-$nilaiPpn-$nilaiPph;
                // }
                
            }   
            
	   // $header = array($_SESSION['lang']['nourut'],$_SESSION['lang']['noinvoice'],$_SESSION['lang']['unit'],$_SESSION['lang']['keterangan'],"Nomor Voucher",$_SESSION['lang']['tanggal'],$_SESSION['lang']['noakun'],"DPP",$_SESSION['lang']['nilai'],$_SESSION['lang']['namasupplier'],$_SESSION['lang']['jenis'],"Jenis Detail");
	   $header = array($_SESSION['lang']['nourut'],$_SESSION['lang']['noinvoice'],$_SESSION['lang']['nodok'],$_SESSION['lang']['nofp'],$_SESSION['lang']['unit'],$_SESSION['lang']['keterangan'],$_SESSION['lang']['tanggal'],$_SESSION['lang']['noakun'],"DPP",$_SESSION['lang']['nilai'],$_SESSION['lang']['namasupplier']);
	    if($data['tipeView']=='preview'){
		   	$table ="<fieldset id='fieldForm'  clear:right;min-height:auto;'>";
		   	$table .="<legend>List Data Bukti Potong Pajak</legend>";
		   	$brd=0;
		}
		if($data['tipeView']=='excel'){
			$brd=1;
		}
	   $table .= "<table cellspacing='1' border='".$brd."' class='sortable' width=100%>";
	   $table .= "<thead><tr class='rowheader'>";
	   foreach($header as $head) {
	       $table .= "<td align=center>".$head."</td>";
	   }
	   $no=0;
	   $table .= "<td hidden>Kompensasi</td>";
	   if($data['tipeView']=='preview'){
	   	 	$table .= "<td><input type=checkbox id=chckbxAll onclick=checkAllDt() checked /></td>";
	   }
	   $table .= "</tr></thead>";
	   $table .= "<tbody>";
	   foreach ($resAll as $key => $row) {
	   			$sCek="select * from ".$dbname.".tax_buktipotongpajak where noinvoice='".$row['noinvoice']."'";
	   			$rCek=fetchData($sCek);
	   			if(count($rCek)!=0){
	   				continue;
	   			}
		   		$no+=1;
		   		$persenPjk=makeOption($dbname,"log_5pphsup","noakun,tarif","noakun='".$data['noakun']."' and supplierid='".$row['kodesupplier']."'");
		   		// $ketTagihan=makeOption($dbname,"keu_kasbankht","notransaksi,keterangan","notransaksi='".$row['notransaksi']."'");
		   		// $ketUnit=makeOption($dbname,"keu_tagihanht","noinvoice,unit","noinvoice='".$row['noinvoice']."'");
		   		$table.="<tr class=rowcontent>";
		   		$table.="<td>".$no."</td>";
		   		$table.="<td id=noinvoice_".$no.">".$row['noinvoice']."</td>";
		   		$table.="<td>".$row['nopo']."</td>";
		   		$table.="<td>".$row['nofp']."</td>";
		   		// $table.="<td>".$ketUnit[$row['noinvoice']]."</td>";
		   		// $table.="<td>".$ketTagihan[$row['notransaksi']]."</td>";
				$table.="<td>".$row['unit']."</td>";
		   		$table.="<td>".$row['keterangan']."</td>";

		   		if($data['tipeView']=='preview'){
		   		$table.="<td hidden id=novoucher_".$no.">".$row['notransaksi']."</td>";
		   			$table.="<td>".tanggalnormal($row['tanggal'])."</td>";
		   		}
		   		if($data['tipeView']=='excel'){
		   			$table.="<td>".$row['tanggal']."</td>";
		   		}
		   		$table.="<td id=noakun_".$no.">".$row['noakun']."</td>";

		   		//$optDpp=makeOption($dbname,"keu_tagihanht","noinvoice,nilaiinvoice","noinvoice=");
                // $dpp=0;
                // if($persenPjk[$data['noakun']]!=''){
                //     @$dpp=($row['jumlah']/($persenPjk[$data['noakun']]/100))*-1;    
                // }
                
                $dpp=$optDpp[$row['noinvoice']];
		   		$row['jumlah']=$row['jumlah']*-1;
		   		$table.="<td align=right><input type=hidden  id=dpp_".$no." value='".$dpp."' /> ".number_format($dpp)."</td>";
		   		$table.="<td align=right  ><input type=hidden  id=nilai_".$no." value='".$row['jumlah']."' /> ".number_format($row['jumlah'])."</td>";
		   		if($data['tipeView']=='preview'){
			   		$table.="<td onclick=checkfp('".$row['kodesupplier']."') style='cursor:pointer' title='Click All ".$row['namasupplier']."'><input type=hidden id=kodesupplier_".$no." value='".$row['kodesupplier']."' />".$row['namasupplier']."</td>";
					$table.="<td hidden><select id='jenis_".$no."' style=width:100px onchange=getlain('".$no."')>".$optjenis."</select></td>";
					$table .= "<td hidden><input  id='jenisdetail_".$no."'  style=width:100px  disabled type=text class=myinputtext></td>";
					$table .= "<td hidden><input type=text class=myinputtextnumber  style=width:100px   id='kompensasi_".$no."' style=100px onykeyup=z.numberFormat(kompensasi_".$no.") /></td>";
			   		$table.="<td><input type=checkbox id=posting_".$no." checked /></td>";
		   		}
		   		if($data['tipeView']=='excel'){
			   		$table.="<td>".$row['namasupplier']."</td>";
		   			$table .= "<td colspan=1>&nbsp;</td>";
		   		}
		   		$table.="</tr>";
		   		$nilPPh+=$row['jumlah'];		   	
	   }	


// tambahan dari ledger
		#= query yg dipakai
		$queryAll=" select a.noakun,a.noinvoice,a.nilai as jumlah,b.kodesupplier,b.tanggal,b.keterangan as keterangan,b.unit,
					c.namaakun,d.namasupplier,b.nopo as nopo,b.nofp as nofp
					from ".$dbname.".keu_tagihandt a 
					left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
					left join ".$dbname.".keu_5akun c on a.noakun=c.noakun
					Left join log_5supplier d on b.kodesupplier=d.supplierid
					WHERE a.noakun='".$data['noakun']."' and b.posting=1 and b.kodeorg='".$optInduk[$data['kodeorg']]."' 
					and b.kodesupplier not in (select supplierid from ".$dbname.".log_5supkelompok where tipe='PAJAK')
					".$where."
					order by b.tanggal desc,b.kodesupplier desc";
// echo $queryAll;	   
	   // $table.="<tr class=rowcontent>";
	   // 		$table.="<td>qwe</td>";
	   // $table.="</tr>";
// endof tambahan dari ledger

		$table.="<tr class=rowcontent>";
		
			$table.="<td colspan=9>&nbsp;</td>";
		
		
		$table.="<td align=right  id=nilai_".$no.">".number_format($nilPPh)."</td>";
		if($data['tipeView']=='preview'){
			$table.="<td colspan=5>&nbsp; </td>";
		}
		if($data['tipeView']=='excel'){
			$table.="<td colspan=>&nbsp; </td>";
		}
		
	   $table.="</tr>";
	   $table .= "</tbody>";
	   $table .= "</table><input type=hidden id=totrow value='".$no."' />";
	   
	   if($data['tipeView']=='preview'){
	   		$table .=  makeElement('saveButton','button',"Simpan",array('onclick'=>"savedata('".$no."')"));
	   		$table .= "</fieldset>";
	   		echo $table;	
	   }
	   if($data['tipeView']=='excel'){

	   		$sNet="select  induk from  ".$dbname.".organisasi  
                    where kodeorganisasi='".$data['kodeorg']."'";
            $rNet=fetchData($sNet);
            $tglSkrg = date("Ymd");
            $nop_ = "list_".$rNet[0]['induk']."_bupot_".$tglSkrg;
            if (strlen($table) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $table)) {
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
	   }
	   
    break;
	 

    case 'savedata':
	$data=$_POST;
    $notrans_kasbank=explode('###', $data['notransaksi']);
    $noakun=explode('###', $data['noakun']);
    $nilai=explode('###', $data['nilai']);
    $kompensasi=explode('###', $data['kompensasi']);
    $supplier=explode('###', $data['supplier']);
    $jenis=explode('###', $data['jenis']);
    $jenisdetail=explode('###', $data['jenisdetail']);
    $noinvoice=explode('###', $data['noinvoice']);
    $dpp=explode('###', $data['dpp']);
	// print_r($supplier);

	// exit('warning');
	$pt=$pt[$data['kodeorg']];
	
    for($x=0;$x<=($data['num']-1);$x++) {
        $pim=array();
		$initialtax=$arrtax[$noakun[$x]];
		
		$exper=explode('-',$data['periode']);
		$tahun=$exper[0];
		$bulan=$exper[1];
		$bulan=romawi($bulan);
		
		$nobukti=$initialtax.'/'.$pt.'-'.$inisial[$pt].'/'.$bulan.'/'.$tahun;
		// $nobukti=$initialtax.'/'.$pt.'/'.$bulan.'/'.$tahun;
		
		$str="select `no_buktipotong` from ".$dbname.".`tax_buktipotongpajak` 
				where no_buktipotong like '%".$nobukti."' order by `no_buktipotong` desc limit 0,1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nolama=$bar['no_buktipotong'];
			
			if($nolama==''){
				$awal=1;
			}else{
				$exnolama=explode('/',$nolama);
				$awal=intval($exnolama[0])+1;
			}
			$counter=addZero($awal,6);
			$pim['no_buktipotong']=$counter.'/'.$nobukti;
			
        // $pim['no_buktipotong']=str_replace("-", "", $data['periode'])."/".$data['kodeorg']."/".$maxid;
		#cek jika sudah ada maka tidak diinsert kembali
		$sCekBank="select * from ".$dbname.".tax_buktipotongpajak where notrans_kasbank='".$notrans_kasbank[$x]."' and noinvoice='".$noinvoice[$x]."'";
		$rCekBank=fetchData($sCekBank);
		if(count($rCekBank)!=0){
			continue;
		}

		if(is_null($supplier[$x])){
			continue;
		}
        @$pim['notrans_kasbank']=$notrans_kasbank[$x];
        @$pim['noakun']=$noakun[$x];
        @$pim['nilai']=abs(str_replace(",","",$nilai[$x]));
        @$pim['periode']=$data['periode'];
        if($kompensasi[$x]==''){
        	$kompensasi[$x]=0;
        }
        @$pim['kompensasi']=$kompensasi[$x];
        @$pim['kodeorg']=$data['kodeorg'];
        @$pim['kodesupplier']=$supplier[$x];
		@$pim['jenis']=@$jenis[$x];
		@$pim['jenisdetail']=@$jenisdetail[$x];
		$dpp[$x]=str_replace(",","",$dpp[$x]);
		if(intval($dpp[$x])=='0'){
			$dpp[$x]=0;
		}
		@$pim['dpp']=@$dpp[$x];
		@$pim['noinvoice']=@$noinvoice[$x];
		@$pim['npwp']=@$_POST['npwp'];
		@$pim['createdby']=$_SESSION['standard']['userid'];
		@$pim['createtime']=date("Y-m-d H:i:s");
		@$pim['supplier_kpp']=@$_POST['supplier_kpp'];

        $column = array('no_buktipotong','notrans_kasbank','noakun','nilai','periode','kompensasi','kodeorg','kodesupplier',
		'jenis_penghasilan','jenisdetail','tarif_pajak','noinvoice','npwp','createdby','createtime','supplierid_kpp');
        $query = insertQuery($dbname,'tax_buktipotongpajak',$pim,$column);
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
    }

	print_r($pim);

    break;
	
	
	case'posting':
		$data=$_POST;
		$tempnoinv=date('Ymdhis');
		$sData="select sum(nilai) as tothutang,npwp,noakun,supplierid_kpp,createdby,no_buktipotong from ".$dbname.".tax_buktipotongpajak 
		        where kodeorg='".$data['kodeorg']."' and periode='".$data['periode']."' and noakun='".$data['noakun']."' and supplierid_kpp='".$data['supplier_kpp']."' group by noakun";
		$rData=fetchData($sData);
		$data2=$rData[0];
		$arusKas=makeOption($dbname,"keu_5aruskas_detail","noakun,noaruskas","noakun='".$data['noakun']."'");
		$arusKet=makeOption($dbname,"keu_5keterangan","noaruskas,id_ket","noaruskas='".$arusKas[$data['noakun']]."'");
		$optInduk=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$data['kodeorg']."'");
		$sinsert="insert into ".$dbname.".keu_tagihanht (noinvoice,kodesupplier,npwp,npwppph,kodeorg,unit,tanggal,nilaidpp,nilaiinvoice,posting,postingby,kurs,matauang,updateby,tipeinvoice,noakun,nopo) values ";
		$sinsert.=" ('".$tempnoinv."','".$data['supplier_kpp']."','".$data2['npwp']."','".$data2['npwp']."','".$optInduk[$data['kodeorg']]."','".$data['kodeorg']."','".date('Y-m-d')."','".$data2['tothutang']."','".$data2['tothutang']."',1,'".$_SESSION['standard']['userid']."',1,'IDR','".$data2['createdby']."','".$arrJenis[$data2['noakun']]."','".$data['noakun']."','".$data2['no_buktipotong']."')";
		try {
            $owlPDO->exec($sinsert);
            $sInsDet="insert into ".$dbname.".keu_tagihandt (noinvoice,noakun,noaruskas,keterangan,nilai,noinvoice_referensi,nourut) values";
            $sData="select nilai,noakun,noinvoice from ".$dbname.".tax_buktipotongpajak 
		        where kodeorg='".$data['kodeorg']."' and periode='".$data['periode']."' and noakun='".$data['noakun']."' and supplierid_kpp='".$data['supplier_kpp']."'";
		    $rData=fetchData($sData);
			$nourut=0;
		    foreach ($rData as $key => $val) {
				$nourut++;
		    	if($key==0){
		    		$sInsDet.="('".$tempnoinv."','".$val['noakun']."','".$arusKas[$val['noakun']]."','".$arusKet[$arusKas[$val['noakun']]]."','".$val['nilai']."','".$val['noinvoice']."','".$nourut."')";	
		    	}else{
		    		$sInsDet.=",('".$tempnoinv."','".$val['noakun']."','".$arusKas[$val['noakun']]."','".$arusKet[$arusKas[$val['noakun']]]."','".$val['nilai']."','".$val['noinvoice']."','".$nourut."')";
		    	}
		    }
			 try { $owlPDO->exec($sInsDet);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n".$sInsDet;die();}
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n".$sinsert;
            die();
        }
		
		// #= update noinvoice baru di list table tax_buktipotongpajak
		// $str = "update  " . $dbname . ".tax_buktipotongpajak set
				// where 	kodesupplier = '" . $data['kodesupplier'] . "' and
						// periode='" . $data['periode'] . "' and
						// noakun = '" . $data['noakun'] . "' and 
						// kodeorg='" . $data['kodeorg'] . "' and
						// no_buktipotong='" . $data['no_buktipotong'] . "' ";
        // try {
            // $owlPDO->exec($str);
        // } catch (PDOException $e) {
            // print " Gagal  !: " . $e->getMessage() . "\n";
            // die();
        // }
		
		

		#= update flag
		$str = "update " . $dbname . ".tax_buktipotongpajak set posting='1',postingby='" . $_SESSION['standard']['userid'] . "',noinvoice_ref='".$tempnoinv."' 
				where kodeorg = '" . $data['kodeorg'] . "' and periode='" . $data['periode'] . "' and noakun='".$data['noakun']."' and supplierid_kpp='".$data['supplier_kpp']."' ";
	    //exit('warning'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
	case'getlain':
		$data=$_POST;
		#cek bisa manual apa tdk
		$str="select manual from ".$dbname.".pmn_5jenispenghasilan where idpenghasilan='".$data['jenis']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			echo $bar['manual'];
	break;
	
	case'getperiode':
	    $data=$_POST;
        $arrPeriode=array();
        for($x=0;$x<13;$x++){
            $dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
            $bulan=date("Y-m",$dt);
            $arrPeriode[$bulan]=$bulan;
            
        }
		$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select distinct(substr(b.tanggal,1,7)) as periode from ".$dbname.".keu_kasbankdt a 
                left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
                where b.kodeorg='".$data['kodeorg']."' and a.noakun like '213%' order by left(b.tanggal,7) desc";
        $res=fetchData($str);
        foreach ($res as $key => $bar){
            if ($bar['periode']=='2000-00') {
                continue;
            }
            if(count($arrPeriode[$bar['periode']])==1){
                $lstPeriode[$bar['periode']]=$bar['periode'];
                unset($arrPeriode[$bar['periode']]);
            }
        }
		foreach($arrPeriode as $isiPrd) {
            $optperiode.="<option value='".$isiPrd."'>".$isiPrd."</option>";
        }
        foreach($lstPeriode as $isiPrd) {
			$optperiode.="<option value='".$isiPrd."'>".$isiPrd."</option>";
		}
		echo $optperiode;
	
	break;
	
	case'getnoakun':
	$data=$_POST;
		$optnoakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optNpwp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$str="select distinct(a.noakun) as noakun from ".$dbname.".keu_kasbankdt a 
				left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
				where a.kodeorg='".$data['kodeorg']."' and a.noakun like '213%'";

		// ga jadi, akun 117 harusnya ga masuk sini. balikin ke yang atas
		// $str="select distinct(noakun) as noakun from ".$dbname.".keu_tagihandt where (noakun like '213%' or noakun like '117%') and nilai<0";
		$str="select distinct(noakun) as noakun from ".$dbname.".keu_tagihandt where (noakun like '213%') and nilai<0";

                // and (a.tanggal like '".$data['periode']."%' or b.tanggal like '".$data['periode']."%')
				// exit("Error:$str");

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optnoakun.="<option value='".$bar['noakun']."'>".$nmakun[$bar['noakun']]."</option>";
		}
		$optInduk=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$data['kodeorg']."'");
		$sNpwp="select * from ".$dbname.".setup_org_npwp where kodeorg='".$optInduk[$data['kodeorg']]."'";
		$rNpwp=fetchData($sNpwp);
		foreach($rNpwp as $row=>$lst){
			$stat="";
			if($lst['defaults']==1){
				$stat="selected";
			}
			$optNpwp.="<option value='".$lst['npwp']."' ".$stat.">".$lst['npwp']."</option>";	
		}

		echo $optnoakun."####".$optNpwp;
	
	break;
	
	case'getsupp':
	$data=$_POST;
		$optgetsupp="<option value=''>".$_SESSION['lang']['all']."</option>";
		$where='';
		if($data['supplier']!=''){
			$where.=" and a.kodesupplier='".$data['supplier']."'";
		}
		#= indra
		
		
		// print_r($data);
		// exit("Error:A");
		$str=" select distinct(b.kodesupplier) as kodesupplier
					from ".$dbname.".keu_tagihandt a 
					left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
					Left join log_5supplier d on b.kodesupplier=d.supplierid
					WHERE a.noakun like '213%' and b.posting=1 and b.kodeorg='".$pt[$data['kodeorg']]."'
					and b.kodesupplier not in (select supplierid from ".$dbname.".log_5supkelompok where tipe='PAJAK')";
		// exit("Error".$str);
		// $str="select distinct(a.kodesupplier) as kodesupplier from ".$dbname.".keu_kasbankdt a 
				// left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
				// where a.kodeorg='".$data['kodeorg']."' and a.noakun like '213%' and 
				// a.tanggal like '".$data['periode']."%' ".$where."and a.kodesupplier 
				// not in (select supplierid from ".$dbname.".log_5supkelompok where tipe='PAJAK')";
				// exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optgetsupp.="<option value='".$bar['kodesupplier']."'>".$nmsupp[$bar['kodesupplier']]."</option>";
		}
		echo $optgetsupp;
	
	break;
	
	case'deleteht':
		$data=$_POST;
		$str = "delete from " . $dbname . ".tax_buktipotongpajak 
				where 	kodesupplier = '" . $data['kodesupplier'] . "' and
						periode='" . $data['periode'] . "' and
						noakun = '" . $data['noakun'] . "' and 
						kodeorg='" . $data['kodeorg'] . "' and
						no_buktipotong='" . $data['no_buktipotong'] . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	
	break;
	
    
}