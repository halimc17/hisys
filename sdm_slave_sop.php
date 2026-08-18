<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');

$notransaksi = checkPostGet('notransaksi', '');
$norevisi = checkPostGet('norevisi', '');
$departemen = checkPostGet('departemen', '');
$jabatan = checkPostGet('jabatan', '');
$tanggalefektif=tanggalsystemn(checkPostGet('tanggalefektif',''));
$disiapkan = checkPostGet('disiapkan', '');
$tanggaldisiapkan=tanggalsystemn(checkPostGet('tanggaldisiapkan',''));
$diperiksa = checkPostGet('diperiksa', '');
$tanggaldiperiksa=tanggalsystemn(checkPostGet('tanggaldiperiksa',''));
$disahkan = checkPostGet('disahkan', '');
$tanggaldisahkan=tanggalsystemn(checkPostGet('tanggaldisahkan',''));


$useridprosedur = checkPostGet('useridprosedur', '');
$usernamaprosedur = checkPostGet('usernamaprosedur', '');
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');


#= tujuan
$keterangantujuan = checkPostGet('keterangantujuan', '');
$keteranganruanglingkup = checkPostGet('keteranganruanglingkup', '');

#= tanggung jawab
$nouruttanggungjawab = checkPostGet('nouruttanggungjawab', '');
$keterangantanggungjawab = checkPostGet('keterangantanggungjawab', '');

#= referensi
$nourutreferensi = checkPostGet('nourutreferensi', '');
$keteranganreferensi = checkPostGet('keteranganreferensi', '');

#= definisi
$nourutdefinisi = checkPostGet('nourutdefinisi', '');
$keterangandefinisi = checkPostGet('keterangandefinisi', '');

#= ketentuan umum
$nourutketentuanumum = checkPostGet('nourutketentuanumum', '');
$keteranganketentuanumum = checkPostGet('keteranganketentuanumum', '');

#= ketentuan umum
$nourutlampiran = checkPostGet('nourutlampiran', '');
$keteranganlampiran = checkPostGet('keteranganlampiran', '');

#= prosedur
$nourutprosedur = checkPostGet('nourutprosedur', '');
$keteranganprosedur = checkPostGet('keteranganprosedur', '');
$bataswaktuprosedur = checkPostGet('bataswaktuprosedur', '');
$jenispersetujuan="SOP";


switch ($method) {
	
	case'loaddata':
		
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
		
		$where="";
        // if($thnsch!='') {
			// $where.=" and periode like '".$thnsch."%' ";
        // }

        $str="select count(*) as jmlhrow from ".$dbname.".sdm_sopht";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	


        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".sdm_sopht limit ".$offset.",".$limit."";

        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['notransaksi']."</td>";
			$tab.="<td align=center>".$bar['norevisi']."</td>";
			$tab.="<td align=left>".tanggalnormal($bar['tanggalefektif'])."</td>";
            $tab.="
            <td align=center>";
			if($bar['close']=='0'){
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"edithead('".$bar['notransaksi']."','".$bar['norevisi']."','".tanggalnormal($bar['tanggalefektif'])."',
					 '".$bar['disiapkan']."','".$bar['diperiksa']."','".$bar['disahkan']."','".$bar['departemen']."','".$bar['jabatan']."',
					 '".tanggalnormal($bar['tanggaldisiapkan'])."','".tanggalnormal($bar['tanggaldiperiksa'])."','".tanggalnormal($bar['tanggaldisahkan'])."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletehead('".$bar['notransaksi']."','".$bar['norevisi']."');\">
                <img src=images/skyblue/posting.png class=zImgBtn title='Posting' onclick=\"posting('".$bar['notransaksi']."');\">";
               	// <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_sopht','".$bar['notransaksi']."','','sdm_slave_sop',event);\">
			} else if($bar['close']=='9'){
				$tab.="
				<img src=images/icons/04/16/04.png class=zImgBtn title='Submitted' >";
				// <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_sopht','".$bar['notransaksi']."','','sdm_slave_sop',event);\">
			} else if($bar['close']=='2'){
				$tab.="
				<img src=images/icons/04/16/01.png class=zImgBtn title='Rejected' >";
				// <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_sopht','".$bar['notransaksi']."','','sdm_slave_sop',event);\">
			}else{
				$tab.="
				<img src=images/icons/04/16/02.png class=zImgOffBtn title='Approved'>";
				// <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_sopht','".$bar['notransaksi']."','','sdm_slave_sop',event);\">
			}
            $tab.="</td>";
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
            <tr><td colspan=40 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
	
	case 'deletehead':
		$str = "delete from ".$dbname.".sdm_sopht where notransaksi='".$notransaksi."' and norevisi='".$norevisi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'posting':
		$str="update ".$dbname.".sdm_sopht set close='9' where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str);

			$strsop="select diperiksa from ".$dbname.".sdm_sopht where notransaksi='".$notransaksi."'";
			$ressop=$owlPDO->query($strsop) or die(print " Gagal: ".PDOException::getMessage());
			$ressop->setFetchMode(PDO::FETCH_ASSOC);
			$barsop=$ressop->fetch();

			$str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
				  ('".$notransaksi."','".$jenispersetujuan."','1','".$barsop['diperiksa']."')";
			try{
	            $owlPDO->exec($str); 
	        }catch(PDOException $e){
	            echo " Gagal," . addslashes($e->getMessage());
	        }
			
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'updatehead':

		if ($diperiksa=='') {
			exit('Warning : Field empty.');
		}

		$str="update ".$dbname.".sdm_sopht set tanggalefektif='".$tanggalefektif."',
				disiapkan='".$disiapkan."',diperiksa='".$diperiksa."',disahkan='".$disahkan."',departemen='".$departemen."',jabatan='".$jabatan."',
				tanggaldisiapkan='".$tanggaldisiapkan."',tanggaldiperiksa='".$tanggaldiperiksa."',tanggaldisahkan='".$tanggaldisahkan."'
				where notransaksi='".$notransaksi."' and norevisi='".$norevisi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	
	case 'savehead':

		if ($diperiksa=='') {
			exit('Warning : Field empty.');
		}

		$str = "insert into ".$dbname.".sdm_sopht 
			(notransaksi,norevisi,tanggalefektif,
			disiapkan,tanggaldisiapkan,
			diperiksa,tanggaldiperiksa,
			disahkan,tanggaldisahkan,
			departemen,jabatan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','".$tanggalefektif."',
					'".$disiapkan."','".$tanggaldisiapkan."',
					'".$diperiksa."','".$tanggaldiperiksa."',
					'".$disahkan."','".$tanggaldisahkan."',
					'".$departemen."','".$jabatan."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	#= tujuan ==============================================================================================================================

	case 'savetujuan':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' and jenis='tujuan'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,keterangan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','tujuan','".$keterangantujuan."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'loadtujuan':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                   
			<thead><tr>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' and jenis='tujuan' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['keterangan']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"edittujuan(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['keterangan']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletetujuan(
					'".$bar['notransaksi']."','".$bar['norevisi']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		echo $tab;
	break;	
	
	case 'deletetujuan':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' and jenis='tujuan'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	#= ruang lingkup ==============================================================================================================================
	
	case 'saveruanglingkup':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' and jenis='ruanglingkup'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,keterangan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','ruanglingkup','".$keteranganruanglingkup."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'loadruanglingkup':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                   
			<thead><tr>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' and jenis='ruanglingkup' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['keterangan']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editruanglingkup(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['keterangan']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deleteruanglingkup(
					'".$bar['notransaksi']."','".$bar['norevisi']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		echo $tab;
	break;	
	
	case 'deleteruanglingkup':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' and jenis='ruanglingkup'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
		
	#= tanggung jawab ==============================================================================================================================
	
	case 'savetanggungjawab':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='tanggungjawab' and nourut='".$nouruttanggungjawab."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,nourut,keterangan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','tanggungjawab','".$nouruttanggungjawab."','".$keterangantanggungjawab."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	
	case 'loadtanggungjawab':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                   
			<thead><tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
				and jenis='tanggungjawab' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['nourut']."</td>
				<td>".$bar['keterangan']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"edittanggungjawab(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."','".$bar['keterangan']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletetanggungjawab(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		
		echo $tab;
	break;	
	
	case 'deletetanggungjawab':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='tanggungjawab' and nourut='".$nouruttanggungjawab."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	#= referensi ==================================================================================================================================

	case 'savereferensi':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='referensi' and nourut='".$nourutreferensi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,nourut,keterangan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','referensi','".$nourutreferensi."','".$keteranganreferensi."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	
	case 'loadreferensi':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                   
			<thead><tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
				and jenis='referensi' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['nourut']."</td>
				<td>".$bar['keterangan']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editreferensi(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."','".$bar['keterangan']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletereferensi(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		
		echo $tab;
	break;	
	
	case 'deletereferensi':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='referensi' and nourut='".$nourutreferensi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	#= definisi ====================================================================================================================================	
	
	case 'savedefinisi':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='definisi' and nourut='".$nourutdefinisi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,nourut,keterangan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','definisi','".$nourutdefinisi."','".$keterangandefinisi."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	
	case 'loaddefinisi':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                   
			<thead><tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
				and jenis='definisi' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['nourut']."</td>
				<td>".$bar['keterangan']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdefinisi(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."','".$bar['keterangan']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletedefinisi(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		
		echo $tab;
	break;	
	
	case 'deletedefinisi':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='definisi' and nourut='".$nourutdefinisi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	
	#= ketentuanumum ===========================================================================================================================	
	
	case 'saveketentuanumum':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='ketentuanumum' and nourut='".$nourutketentuanumum."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,nourut,keterangan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','ketentuanumum','".$nourutketentuanumum."','".$keteranganketentuanumum."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	
	case 'loadketentuanumum':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                   
			<thead><tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
				and jenis='ketentuanumum' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['nourut']."</td>
				<td>".$bar['keterangan']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editketentuanumum(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."','".$bar['keterangan']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deleteketentuanumum(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		
		echo $tab;
	break;	
	
	case 'deleteketentuanumum':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='ketentuanumum' and nourut='".$nourutketentuanumum."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	#= prosedur =====================================================================================================================================
	
	case 'adduserprosedur':
		if($useridprosedur == ''){
			exit("Warning : user harus diisi");
		}
		
		$newdata = array(
			'useridprosedur'=>$useridprosedur,
			'usernamaprosedur'=>$nmkar[$useridprosedur]
		);
		
		if($_SESSION['userprosedur'] != array()){
			foreach($_SESSION['userprosedur'] as $key=>$row){
				if($row['useridprosedur'] == $useridprosedur){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['userprosedur'],$newdata);
		}else{
			array_push($_SESSION['userprosedur'],$newdata);
		}
		
		echo $useridprosedur."####".$nmkar[$useridprosedur];
	break;
	
	case 'deleteuserprosedur':
		foreach($_SESSION['userprosedur'] as $key=>$row){
			if($row['useridprosedur'] == $useridprosedur){
				unset($_SESSION['userprosedur'][$key]);
			}
		}
	break;
	
	
	case 'saveprosedur':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='prosedur' and nourut='".$nourutprosedur."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,nourut,keterangan,bataswaktu,createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','prosedur','".$nourutprosedur."','".$keteranganprosedur."','".$bataswaktuprosedur."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		foreach($_SESSION['userprosedur'] as $row){
			$str = "insert into ".$dbname.".sdm_sopdt_userprosedur(notransaksi,norevisi,jenis,nourut,karyawanid,createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','prosedur','".$nourutprosedur."','".$row['useridprosedur']."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
			try{
				$owlPDO->exec($str);
			}catch(PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}
	break;	
	
	
	case 'loadprosedur':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead><tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['user']."</td>
				<td align=center>Batas Waktu</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
				and jenis='prosedur' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$daftarkaryawan='';
			#= query daftar usernya
			$str1="SELECT a.karyawanid,b.namakaryawan from ".$dbname.".sdm_sopdt_userprosedur a left join ".$dbname.".datakaryawan b
				on a.karyawanid=b.karyawanid where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
				and jenis='prosedur' and nourut='".$bar['nourut']."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			while($bar1=$res1->fetch()){
				$daftarkaryawan.=$bar1['namakaryawan'].', ';
			}
			
			$tab.="<tr class=rowcontent>
				<td>".$bar['nourut']."</td>
				<td>".$bar['keterangan']."</td>
				<td>".$daftarkaryawan."</td>
				<td align=right>".$bar['bataswaktu']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editprosedur(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."','".$bar['keterangan']."','".$bar['bataswaktu']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deleteprosedur(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		
		echo $tab;
	break;	
	
	case 'deleteprosedur':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='prosedur' and nourut='".$nourutprosedur."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'cancelprosedur':
		$_SESSION['userprosedur'] = array();
	break;
	
	#= lampiran ===========================================================================================================================	
	
	case 'savelampiran':
	
		#= delete
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='lampiran' and nourut='".$nourutlampiran."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		#= insert
		$str = "insert into ".$dbname.".sdm_sopdt 
			(notransaksi,norevisi,jenis,nourut,keterangan,
			createdby,createtime) 
			VALUES ('".$notransaksi."','".$norevisi."','lampiran','".$nourutlampiran."','".$keteranganlampiran."',
					'".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	
	case 'loadlampiran':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
                   
			<thead><tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
				and jenis='lampiran' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['nourut']."</td>
				<td>".$bar['keterangan']."</td>
				<td align=center>
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editlampiran(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."','".$bar['keterangan']."')\">
					
					<img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletelampiran(
					'".$bar['notransaksi']."','".$bar['norevisi']."','".$bar['nourut']."')\">
				</td>
				</tr>";
		
		}
		$tab.="</table></fieldset>";
		
		echo $tab;
	break;	
	
	case 'deletelampiran':
		$str = "delete from ".$dbname.".sdm_sopdt where notransaksi='".$notransaksi."' and norevisi='".$norevisi."' 
			and jenis='lampiran' and nourut='".$nourutlampiran."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	

	#= upload =============================================================================================================================

	case 'submitfile':
        $tgl = date("YmdHis");
        $his = date("His");
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
        $path="fileupload/sop";
		$fileupload = checkPostGet('fileupload', '');
		$namafile = checkPostGet('namafile', '');

        if($fileupload!=''){
            if($_FILES['file']['error']==0){    
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = "SOP_PROSEDUR_".$his."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    if($_FILES['file']['size'] <= 250000){
                        $str = "insert into ".$dbname.".listfile_sdm_sop values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                        try{
                            $owlPDO->exec($str);
                            if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                            file_put_contents($path.$filename,$file_tmpname);
                        }
                        catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }else{
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload harus .jpg atau .jpeg");
                }
            }
        }
    break;

    case'viewfile':
		$namafile = checkPostGet('namafile', '');
        $tab="";
        $tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
        
        echo $tab;
    break;
    
    case 'deletefile':
		$namafile = checkPostGet('namafile', '');
        $str="delete from ".$dbname.".listfile_sdm_sop where notransaksi='".$notransaksi."' and namafile='".$namafile."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    
    
    case 'deletefileall':
        $str="select * from ".$dbname.".listfile_sdm_sop where notransaksi='".$notransaksi."'"; //exit('error'.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $pathx = $path.$bar['namafile'];
            unlink($pathx);
        }
        
        $str="delete from ".$dbname.".listfile_sdm_sop where notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($str);
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    case 'loadfiles':
        $no = 0;
        $tab = "";  
        $str="select * from ".$dbname.".listfile_sdm_sop where notransaksi = '".$notransaksi."' and status='1'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                @$icon = seticonfile($val['formaticon']);   
                $tab.="<td style='text-align:center'>
                    <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";

                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                    <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
             
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";        
                
                $tab."  </td>
                </tr>";
            }   
        }
        
        echo $tab;
    break;

	#= perubahan ===========================================================================================================================	

	
	case 'loadperubahan':
		$tab="";
		$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['data']."</b></legend>
                <table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead><tr>
				<td align=center>".$_SESSION['lang']['nodok']."</td>
				<td align=center>No. Revisi</td>
				<td align=center>Tanggal Efektif</td>
				<td align=center>Uraian Perubahan</td>
			</tr></thead>";
		$str="SELECT * from ".$dbname.".sdm_sopht where notransaksi='".$notransaksi."'  order by norevisi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>
				<td>".$bar['notransaksi']."</td>
				<td>".$bar['norevisi']."</td>
				<td>".$bar['tanggalefektif']."</td>
				<td>".$bar['keterangan']."</td>
				</tr>";
		}
		$tab.="</table></fieldset>";
		
		echo $tab;
	break;	
	default;
	
}
?>