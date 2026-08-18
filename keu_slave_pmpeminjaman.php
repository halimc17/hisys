<?php
// Updated Atwal 12/20/2017 //
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');

#= ht
$notransaksi = checkPostGet('notransaksi', '');
$kodeunit = checkPostGet('unit', '');
$kodebank = checkPostGet('kodebank', '');
$jenis = checkPostGet('jenis', '');
$noakun = checkPostGet('noakun', '');
$jenisfasilitas = checkPostGet('jenisfasilitas', '');
$tujuan = checkPostGet('tujuan', '');
$jumlahfasilitas = checkPostGet('jumlahfasilitas', '');
$jangkawaktu=checkPostGet('jangkawaktu','');
$komitmenperiode = checkPostGet('komitmenperiode', '');
$availabilityperiode = checkPostGet('availabilityperiode', '');
$graceperiode = checkPostGet('graceperiode', '');
$biayakredit = checkPostGet('biayakredit', '');
$sukubunga = checkPostGet('sukubunga', '');
$pinalti = checkPostGet('pinalti', '');
$keterangan = checkPostGet('keterangan', '');
$jumlahbulan = checkPostGet('jumlahbulan', '');
$jenispinjaman = checkPostGet('jenispinjaman', '');
$noloanangsuran = checkPostGet('noloanangsuran', '');
$bulanke = checkPostGet('bulanke', '');
$rupiahangsuran = checkPostGet('rupiahangsuran', '');

if($jangkawaktu=='--'){
	$jangkawaktu='';
}
$jatuhtempo = checkPostGet('jatuhtempo', '');


#= angsuran
$pokokangsuran = checkPostGet('pokokangsuran', '');
$sukubungaangsuran = checkPostGet('sukubungaangsuran', '');
$periodecalculate = checkPostGet('periodecalculate', '');//periode di angsuran
if($periodecalculate=='--'){
	$periodecalculate='';
}
$bungaangsuran = checkPostGet('bungaangsuran', '');
$totalbungaangsuran = checkPostGet('totalbungaangsuran', '0');
$totalpembayaranangsuran = checkPostGet('totalpembayaranangsuran', '');
$tanggalpembayaranangsuran=tanggalsystemn(checkPostGet('tanggalpembayaranangsuran',''));
if($tanggalpembayaranangsuran=='--'){
	$tanggalpembayaranangsuran='';
}

#= pencairan
$noloanpencairan = checkPostGet('noloanpencairan', '');
$jumlahpencairan = checkPostGet('jumlahpencairan', '');
$tanggalpencairan=tanggalsystemn(checkPostGet('tanggalpencairan',''));
if($tanggalpencairan=='--'){
	$tanggalpencairan='';
}

$notransaksisch = checkPostGet('notransaksisch', '');
$ptsch = checkPostGet('ptsch', '');
$jenissch = checkPostGet('jenissch', '');
$noakunsch = checkPostGet('noakunsch', '');

switch ($method){
	case'sukubunga':
		$str=" select nilai from ".$dbname.".keu_pmsukubunga where  periode<='".$tanggalpembayaranangsuran."' or periode>='".$tanggalpembayaranangsuran."' and kodebank='".$kodebank."' order by periode desc limit 1";
		// echo $str;
		// exit('warning');
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
                $nilai=$bar['nilai'];
		echo $nilai;
	break;
	case'namabank':
		$str = "select distinct a.namabank as kodebank,b.namabank as namabank from ".$dbname.".keu_5akunbank a 
		left join keu_5daftarbank b on a.namabank = b.kodebank where a.pemilik = '".$kodeunit."' order by b.namabank";
		$optnoakun = "";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optnoakun.="<option value='".$bar['kodebank']."'>".$bar['namabank']."</option>";
		}
		echo $optnoakun;
	break;
	case'noakun':
		$str = "select noakun,rekening from ".$dbname.".keu_5akunbank where pemilik = '".$kodeunit."' and namabank = '".$kodebank."'";
		$optnoakun = "";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optnoakun.="<option value='".$bar['noakun']."'>".$bar['rekening']."</option>";
		}
		echo $optnoakun;
	break;
	case'harihutang':
		// $diff=date_diff($tgl1angsuran,$tgl2angsuran);
		// echo $diff;
		if($tgl1angsuran>$tgl2angsuran){
			exit("Warning:Tanggal salah");
		}
		$hari=selisihari($tgl1angsuran,$tgl2angsuran);
		echo $hari;
	break;
	case 'getdata':
		$where='';
		if($notransaksi!=''){
			$where.="and a.notransaksi = '".$notransaksi."'";
		}
		$str="select a.*,b.noakun as rekeningcode,b.rekening as norekening,c.kodebank as bankcode,c.namabank as bankname from ".$dbname.".keu_pmpeminjamanht a 
		left join keu_5akunbank b on a.noakun = b.noakun
		left join keu_5daftarbank c on b.namabank = c.kodebank
		where 1=1 ".$where." ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
		if($jlhbrs > 0){
			echo json_encode($res[0]);
		}
	break;
	case 'loaddata':
		$where='';
		$footd = "";
		if($notransaksisch!=''){
			$where.=" and a.notransaksi like '%".$notransaksisch."%'";
		}
		if($ptsch!=''){
			$where.=" and a.kodeunit = '".$ptsch."'";
		}
		if($jenissch!=''){
			$where.=" and a.jenis = '".$jenissch."'";
		}
		if($noakunsch!=''){
			$where.=" and a.noakun = '".$noakunsch."'";
		}
	
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select a.* from ".$dbname.".keu_pmpeminjamanht a where 1=1 ".$where." ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $arrTipe=array("0"=>"Perpencairan","1"=>"Total Pencairan");
            $str="select a.*,IFNULL(b.notransaksi,'FALSE') as pencairan from ".$dbname.".keu_pmpeminjamanht a
			left join ".$dbname.".keu_pmpeminjamandt_pencairan b on a.notransaksi = b.notransaksi 
			where 1=1 ".$where." group by a.notransaksi limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whr="kodeorganisasi='".$bar['kodeunit']."'";
                //$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);
                @$no+=1;
                $tab.="<tr class=rowcontent>";
					$tab.="<td style='text-align:center;'>".$no."</td>";
					$tab.="<td>".$bar['notransaksi']."</td>";
					$tab.="<td>".$bar['kodeunit']."</td>";
					$tab.="<td>".$bar['jenis']."</td>";
					$tab.="<td>".$arrTipe[$bar['tp_pokok']]."</td>";
					$optRek=makeOption($dbname,"keu_5akunbank","noakun,rekening","noakun='".$bar['noakun']."'");
					$optKdBk=makeOption($dbname,"keu_5akunbank","noakun,namabank","noakun='".$bar['noakun']."'");
					$optNmBk=makeOption($dbname,"keu_5daftarbank","kodebank,namabank","kodebank='".$optKdBk[$bar['noakun']]."'");
					$tab.="<td>".$optNmBk[$optKdBk[$bar['noakun']]]."</td>";
					$tab.="<td>".$optRek[$bar['noakun']]."</td>";
					$tab.="<td align=right>".number_format($bar['jumlahfasilitas'])."</td>";
					$tab.="<td align=right>".tanggalnormal($bar['jangkawaktu'])."</td>";
					$tab.="<td align=center>";
					$tab.="<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editht('".$bar['notransaksi']."')\">";
					$tab.="</td>";
					$isi1="";
					$isi="";
					if($bar['pencairan'] == 'FALSE'){
						$isi1.="<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteht('".$bar['notransaksi']."')\">"; 
					}
					$tab.="<td align=center>".$isi1."</td>";
					
						//$isi.="<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='Add Detail Pinjaman' onclick=\"adddetail('".$bar['notransaksi']."');\">";
					//$tab.="<td align=center>".$isi."</td>";
					$tab.="<td align=center>";
					$tab.="<button class='mybutton' onclick=\"cloneht('".$bar['notransaksi']."')\">".$_SESSION['lang']['tutup']."</button>";
					$tab.="</td>";
				$tab.="</tr>";
            }/* $tab.="<img src='images/skyblue/zoom.png' class='zImgBtn' onclick=popupdetail('detaildata','3','".$bar['notransaksi']."','".$bar['unit']."','".substr($bar['tanggal'],0,7)."') title='Preview'>";
                    */
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
                <tr><td colspan=12 align=center>
                <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
	
	
	case'deleteht':
		$str="delete from ".$dbname.".`keu_pmpeminjamanht` where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str);
			$sdetPin="delete from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($sdetPin);
				$sdetPin="delete from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."'";
				try{
					$owlPDO->exec($sdetPin);}
				catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n".$sdetPin; die(); 
				}
			}
			catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n".$sdetPin; die(); 
			}
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n".$str; 
		   die(); 
		}
	break;
	
	case'savepencairan':
	if($_POST['jatuhtempoCair']==''){
		$_POST['jatuhtempoCair']='00';
	}
		$str="INSERT INTO ".$dbname.".`keu_pmpeminjamandt_pencairan` (`notransaksi`, `tanggal`, `jumlah`,`tgl_jatuhtempo`, `noloan`,createdby,createtime)
		VALUES ('".$notransaksi."','".$tanggalpencairan."', '".$jumlahpencairan."','".$_POST['jatuhtempoCair']."', '".$noloanpencairan."','".$_SESSION['standard']['userid']."','".date('Ymd')."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n".$str; 
		   die(); 
		}
	break;
	
	case'deletepencairan':
		$str="delete from ".$dbname.".`keu_pmpeminjamandt_pencairan` where 
		notransaksi='".$notransaksi."' and noloan='".$_POST['tanggalpencairan']."'";
		try{
			$owlPDO->exec($str);
			$sdel="delete from ".$dbname.".keu_detailpinjaman where noloan='".$_POST['tanggalpencairan']."' and notransaksi='".$notransaksi."'";
			try{$owlPDO->exec($sdel);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n".$sdel; die(); }
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'loadpencairan':
		$tab.="
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['nopencairan']."</td>
					<td align=center>".$_SESSION['lang']['tanggalpencairan']."</td>
					<td align=center>".$_SESSION['lang']['jumlahpencairan']."</td>
					<td align=center>".$_SESSION['lang']['jatuhtempo']."</td>
					<td align=center colspan=2>".$_SESSION['lang']['action']."</td>
				</tr>	
			</thead>";
			$totCair=0;
			$keu_pmpeminjamandt_angsuran="select notransaksi from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."'";
			$reskeu_pmpeminjamandt_angsuran=fetchData($keu_pmpeminjamandt_angsuran);
			$jml_angsuran = count($reskeu_pmpeminjamandt_angsuran);
				$str=" select * from ".$dbname.".keu_pmpeminjamandt_pencairan where  notransaksi='".$notransaksi."' order by tanggal asc";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					@$nopencairan+=1;
					$tab.="<tr class=rowcontent>
						<td>".$nopencairan."</td>
						<td>".$bar['noloan']."</td>
						<td>".tglnmbln($bar['tanggal'],'','')."</td>
						<td align=right>".number_format($bar['jumlah'])."</td>
						<td>".$bar['tgl_jatuhtempo']."</td>
						<td align=center>";
						$totCair+=$bar['jumlah'];
					if($jml_angsuran == 0){	
						$tab.="<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletepencairan(
						'".$bar['notransaksi']."','".$bar['noloan']."')\">";
					}	
					$tab.="</td>";
					$tab.="<td><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title=\"Add Detail Pokok Pinjaman ".$bar['noloan']." \" onclick=\"adddetail('viewdetail','3','".$bar['notransaksi']."','".$bar['noloan']."');\">
					</tr>";/*
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield(
						'".tanggalnormal($bar['tanggal'])."','".$bar['jumlah']."','".$bar['noloan']."')\">
					*/
				}
				@$nopencairan+=1;
				$tab.="<tr class=rowcontent>
					<td colspan=3 align=right>".$_SESSION['lang']['total']."</td>
					<td align=right>".number_format($totCair)."</td>
					<td colspan=3>&nbsp;</td></tr>";
			$tab.="</table>";	
		echo $tab;		
	break;
	
	case'saveangsuran':
	$bungaangsuran=str_replace(",","",$bungaangsuran);
	$pokokangsuran=str_replace(",","",$pokokangsuran);
	$totalpembayaranangsuran=str_replace(",","",$totalpembayaranangsuran);

		$str="INSERT INTO ".$dbname.".`keu_pmpeminjamandt_angsuran` 
		(notransaksi,pokok,sukubunga,noloan,tenor,
		bunga,totalbunga,totalpembayaran,
		tanggalpembayaran,periode,createdby,createtime,updateby)
		VALUES ('".$notransaksi."','".$pokokangsuran."', '".$sukubungaangsuran."','".$noloanangsuran."', '".$bulanke."',
		'".$bungaangsuran."','".floatval($totalbungaangsuran)."', '".$totalpembayaranangsuran."',
		'".$tanggalpembayaranangsuran."','".$periodecalculate."','".$_SESSION['standard']['userid']."','".date('Ymd')."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'deleteangsuran':
		$str="delete from ".$dbname.".`keu_pmpeminjamandt_angsuran` where 
		notransaksi='".$notransaksi."' and noloan='".$noloanangsuran."' and tenor='".$bulanke."'";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
	break;
	
	case'loadangsuran':

		$optbulan=$optloan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		#get noloan
		$sql="select noloan,jumlah from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$notransaksi."' order by tanggal asc";
		$str=$owlPDO->query($sql);
		$str->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$str->fetch()){

			$str1=" select sum(pokok) as jumlahangsuran from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$notransaksi."' and noloan='".$bar['noloan']."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
			if($bar1['jumlahangsuran']==$bar['jumlah']){
                continue;
            }

		    $optloan.="<option value='".$bar['noloan']."'>".$bar['noloan']."</option>";
		}

		#get noloan
		$sJmlhBln="select jumlahbulan from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksi."'";
		$rJmlhBln=fetchData($sJmlhBln);
		$pinjaman=$rJmlhBln[0];
		// $sql="select bulanke from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' order by bulanke asc";
		// $str=$owlPDO->query($sql);
		// $str->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$str->fetch()){
		//     $optbulan.="<option value='".$bar['bulanke']."'>".$bar['bulanke']."</option>";
		// }
		for($awal=1;$awal<=$pinjaman['jumlahbulan'];$awal++){
			$optbulan.="<option value='".$awal."'>".$awal."</option>";
		}
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select a.*,IFNULL(c.tutupbuku,'FALSE') AS tutupbuku from ".$dbname.".keu_pmpeminjamandt_angsuran a
				left join keu_pmpeminjamanht b on a.notransaksi = b.notransaksi 
				left join setup_periodeakuntansi c on a.periode = c.periode and b.kodeunit = c.kodeorg
				where a.notransaksi='".$notransaksi."' order by a.tanggalpembayaran asc  ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        $tab.="<table><tr><td>".$_SESSION['lang']['nopeminjaman']."</td>";
        $tab.="<td>:</td><td><select id=noLoanCr style=width:150px onchange=loadAngsuran2()>".$optloan."</select></td></tr>";
        $tab.="<tr><td>Bulan - ke</td>";
        $tab.="<td>:</td><td><select id=blnCr style=width:150px onchange=loadAngsuran2()>".$optbulan."</select></td></tr>";
        $tab.="<tr><td colspan=2>&nbsp;</td>";
        $tab.="<td><button class=mybutton onclick=resetAngsuran2(0)>".$_SESSION['lang']['clear']."</button></tr>";
        $tab.="</table>";
		$tab.="
			<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['nopencairan']."</td>
					<td align=center>".$_SESSION['lang']['tanggalpembayaran']."</td>
					<td align=center>Bulan - Ke</td>
					<td align=center>".$_SESSION['lang']['pokok']."</td>
					<td align=center>".$_SESSION['lang']['sukubunga']."</td>
					<td align=center>".$_SESSION['lang']['jumlahbunga']."</td>
					<td hidden align=center>".$_SESSION['lang']['totalbunga']."</td>
					<td align=center>".$_SESSION['lang']['totalpembayaran']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>
				</tr>	
			</thead><tbody id=detailDataAngsuran>";
			if($jlhbrs==0){
	            $tab.="<tr class=rowcontent>";
	            $tab.="<td colspan=10>".$_SESSION['lang']['dataempty']."</td>";
	            $tab.="</tr>";
        	}else{
        		$str=" select a.*,IFNULL(c.tutupbuku,'FALSE') AS tutupbuku from ".$dbname.".keu_pmpeminjamandt_angsuran a
				left join keu_pmpeminjamanht b on a.notransaksi = b.notransaksi 
				left join setup_periodeakuntansi c on a.periode = c.periode and b.kodeunit = c.kodeorg
				where a.notransaksi='".$notransaksi."' order by a.tanggalpembayaran asc limit ".$offset.",".$limit."";
				//echo $str;
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					@$noangsuran+=1;
					$tab.="<tr class=rowcontent>
						<td>".$noangsuran."</td>
						<td>".$bar['noloan']."</td>
						<td>".tglnmbln($bar['tanggalpembayaran'],'','')."</td>
						<td>".$bar['tenor']."</td>
						<td align=right>".number_format($bar['pokok'])."</td>
						<td align=right>".number_format($bar['sukubunga'])."</td>
						<td align=right>".number_format($bar['bunga'])."</td>
						<td hidden align=right>".number_format($bar['totalbunga'])."</td>
						<td align=right>".number_format($bar['totalpembayaran'])."</td>
						";
					if($bar['tutupbuku'] == 'FALSE' or $bar['tutupbuku'] == '0'){ //jika periode tidak terdaftar atau tutup buku masih 0, bisa di delete
						if(substr($bar['periode'],0,4)>'2018'){
							$tab.="<td align=center><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteangsuran('".$bar['notransaksi']."','".$bar['noloan']."','".$bar['tenor']."')\"></td>";	
						}else{
							$tab.="<td align=center>&nbsp;</td>";
						}
					}
					$tab.="</tr>";
				}
        	}
				
			$tab.="</tbody>";
			$tab.="<tfoot id=footerAngsuran>";
			$totrows=ceil($jlhbrs/$limit);
            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $tab.="
                <tr><td colspan=10 align=center>
                <button class=mybutton onclick=loadAngsuran2(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pagesAngsran\" name=\"pagesAngsran\" style=\"width:50px\" onchange=\"getPage2()\">".$isiRow."</select>
                <button class=mybutton onclick=loadAngsuran2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr></tfoot></table>";


				
		echo $tab."####".$optloan."####".$optbulan;	
		/*
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield(
						'".$bar['pokok']."','".$bar['sukubunga']."',
						'".tanggalnormal($bar['tanggalmulai'])."','".tanggalnormal($bar['tanggalselesai'])."',
						'".$bar['harihutang']."','".$bar['bunga']."','".$bar['totalbunga']."','".$bar['totalpembayaran']."',
						'".tanggalnormal($bar['tanggalpembayaran'])."')\">
					*/	
	break;
	case'loadAngsuran2':
	$whrData="";
		if($_POST['noLoanCr']!=''){
			$whrData.=" and noloan='".$_POST['noLoanCr']."'";
		}
		if($_POST['blnCr']!=''){
			$whrData.=" and tenor='".$_POST['blnCr']."'";
		}
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select a.*,IFNULL(c.tutupbuku,'FALSE') AS tutupbuku from ".$dbname.".keu_pmpeminjamandt_angsuran a
				left join keu_pmpeminjamanht b on a.notransaksi = b.notransaksi 
				left join setup_periodeakuntansi c on a.periode = c.periode and b.kodeunit = c.kodeorg
				where a.notransaksi='".$notransaksi."'  ".$whrData." order by a.tanggalpembayaran asc  ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
		$str=" select a.*,IFNULL(c.tutupbuku,'FALSE') AS tutupbuku from ".$dbname.".keu_pmpeminjamandt_angsuran a
		left join keu_pmpeminjamanht b on a.notransaksi = b.notransaksi 
		left join setup_periodeakuntansi c on a.periode = c.periode and b.kodeunit = c.kodeorg
		where a.notransaksi='".$notransaksi."' ".$whrData." order by a.tanggalpembayaran asc limit ".$offset.",".$limit."";
		//echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$noangsuran+=1;
			$tab.="<tr class=rowcontent>
				<td>".$noangsuran."</td>
				<td>".$bar['noloan']."</td>
				<td>".tglnmbln($bar['tanggalpembayaran'],'','')."</td>
				<td>".$bar['tenor']."</td>
				<td align=right>".number_format($bar['pokok'])."</td>
				<td align=right>".number_format($bar['sukubunga'])."</td>
				<td align=right>".number_format($bar['bunga'])."</td>
				<td hidden align=right>".number_format($bar['totalbunga'])."</td>
				<td align=right>".number_format($bar['totalpembayaran'])."</td>";
			if($bar['tutupbuku'] == 'FALSE' or $bar['tutupbuku'] == '0'){ //jika periode tidak terdaftar atau tutup buku masih 0, bisa di delete
				// $tab.="<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteangsuran(
				// 	'".$bar['notransaksi']."','".$bar['noloan']."','".$bar['tenor']."')\">";
				if(substr($bar['periode'],0,4)>'2018'){
					$tab.="<td align=center><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteangsuran('".$bar['notransaksi']."','".$bar['noloan']."','".$bar['tenor']."')\"></td>";	
				}else{
					$tab.="<td align=center>&nbsp;</td>";
				}
			}
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
		$foot="
                <tr><td colspan=10 align=center>
                <button class=mybutton onclick=loadAngsuran2(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pagesAngsran\" name=\"pagesAngsran\" style=\"width:50px\" onchange=\"getPage2()\">".$isiRow."</select>
                <button class=mybutton onclick=loadAngsuran2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
		echo $tab."####".$foot;
	break;
	case'savehead':
        
        if($notransaksi==''){
            $str=" select notransaksi from ".$dbname.".keu_pmpeminjamanht where  kodeunit='".$kodeunit."' and jenis='".$jenis."'  order by notransaksi desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
                $notransaksilama=$bar['notransaksi'];
			if($notransaksilama==''){
				$str=" select * from ".$dbname.".keu_pmpeminjamanht where kodeunit='".$kodeunit."' and jenis='".$jenis."' order by notransaksi desc limit 1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$notran=$bar['notransaksi'];
				$num=  explode('/', $notran);
				$num=$num[3]+1;
				if($num<10)
					$num='00'.$num;   
				else if($num<100)
				   $num='0'.$num;
				else
				   $num=$num;
				
				$notransaksibaru=$jenis.'/LM/'.$kodeunit.'/'.$num;
			}
			else{
				$notransaksibaru=$notransaksilama;
			}
        }
        else{
            $notransaksibaru=$notransaksi;
        }
		$sCek="select * from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksibaru."'";
		$rCek=fetchData($sCek);
		if(count($rCek)==1){
			$sDel="delete from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksibaru."'";
			try{$owlPDO->exec($sDel);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n".$sDel; die(); }
		}
		$str="INSERT INTO ".$dbname.".`keu_pmpeminjamanht` 
		(`notransaksi`, `kodeunit`, `jenis`,
		`noakun`,jumlahfasilitas,jangkawaktu,
		`tujuan`,jenisfasilitas,
		jatuhtempo,
		commitment_period,
		availability_period,
		grace_period,
		biayakredit,
		sukubunga,
		pinalti,
		keterangan,
		jumlahbulan,
		jenispinjaman,
		createdby,createtime,tp_pokok)
		VALUES ('".$notransaksibaru."','".$kodeunit."', '".$jenis."',
		'".$noakun."','".$jumlahfasilitas."','".$jangkawaktu."',
		'".$tujuan."','".$jenisfasilitas."',
		'".$jatuhtempo."','".$komitmenperiode."','".$availabilityperiode."',
		'".$graceperiode."','".$biayakredit."','".$sukubunga."','".$pinalti."',
		'".$keterangan."','".$jumlahbulan."','".$jenispinjaman."','".$_SESSION['standard']['userid']."',
		'".date('Y-m-d H:i:s')."','".$_POST['tpPokok']."')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n".$str; 
		   die(); 
		}
        
        echo $notransaksibaru;
        
    break;

    case 'viewdetail':

        $data.="<fieldset><legend>".$_SESSION['lang']['detail']." Pinjaman</legend>";
        $data.="<div style=overflow:auto;width:100%;>";
        $data.="<table>";
        $data.="<tr><td>".$_SESSION['lang']['nopeminjaman']."</td><td>:</td>";
        $data.="<td>".$_POST['notransaksi']."</td></tr>";
        $data.="<tr><td>".$_SESSION['lang']['nopencairan']."</td><td>:</td>";
        $data.="<td>".$_POST['noloan']."<input type=hidden value='".$_POST['noloan']."' id=nopencairan  /></td></tr>";
        $data.="</table>";
        
        // $data.="<tr>";
        #data
        $no=0;
        $str="select jumlahbulan from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksi."' ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $jumlahbulan=$bar['jumlahbulan'];
        $bagiKolom=$jumlahbulan/3;
        $awalKolom=0;
        $tempTable=false;
        $isiData=true;
        $data.="<button onclick=savedetail(".$jumlahbulan.",'".$notransaksi."','".$_POST['noloan']."') class=mybutton>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button>";
        $data.="<table><tr>";
        for($i=1; $i<=$jumlahbulan;$i++){ 
        	$str="select rupiahangsuran,bulanke from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."'  and noloan='".$_POST['noloan']."' and bulanke='".$i."' ";
	        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	        $res->setFetchMode(PDO::FETCH_ASSOC);
	        $bar=$res->fetch();
        	#cek datanya ada kaga dipembayaran
        	$sCek="select * from ".$dbname.".keu_pmpeminjamandt_angsuran where noloan='".$_POST['noloan']."' and tenor='".$i."' and notransaksi='".$notransaksi."'";
        	//echo $sCek;
        	$rCek=fetchData($sCek);
        	$rupiahangsuran=number_format($bar['rupiahangsuran']);
        	$disabled="";
        	if(count($rCek)==1){
        		$disabled='disabled';	
        	}
        
	        	if($tempTable!=$isiData){
	        		$tempTable=$isiData;
	        		$statrtItung=1;
	        		if(($jumlahbulan%3)==0){
	        			$maxRow=$jumlahbulan/3;	
	        		}
	        		if(($jumlahbulan%2)==0){
	        			$maxRow=$jumlahbulan/2;	
	        		}
	        		
	        		$data.="<td valign=top>";
	        		$data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
			        $data.="<thead>";
			        $data.="<tr align=center>";
			        $data.="<td>".$_SESSION['lang']['bulan']." - Ke</td>";
			        $data.="<td>".$_SESSION['lang']['rupiah']."</td>";
			        //$data.="<td colspan=2></td>";
			        $data.="</tr></thead>";
			        $data.="<tr class=rowcontent>";
					$data.="<td><input type=text class=myinputtextnumber id=bulanke_".$i." value='".$i."' disabled  style=width:50px onkeypress='return angka_doang(event)' ".$disabled."></td>";
					$data.="<td><input type=text class=myinputtextnumber id=rupiahangsuran_".$i." value='".$rupiahangsuran."'  style=width:100px onkeypress='return angka_doang(event)' onchange=\"z.numberFormat('rupiahangsuran_".$i."');\" ".$disabled."></td>";
					//$data.="<td><img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=savedetail('".$notransaksi."','".$i."') src='images/save.png'/></td>";
					//$data.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"updatedetail('".$notransaksi."','".$i."')\"</td>";
					$data.="</tr>";  
	        	}else{
	        		if($statrtItung!=$maxRow){
	        			$statrtItung+=1;
	        			$data.="<tr class=rowcontent>";
						$data.="<td><input type=text class=myinputtextnumber id=bulanke_".$i." value='".$i."' disabled  style=width:50px onkeypress='return angka_doang(event)' ".$disabled."></td>";
						$data.="<td><input type=text class=myinputtextnumber id=rupiahangsuran_".$i." value='".$rupiahangsuran."'  style=width:100px onkeypress='return angka_doang(event)' onchange=\"z.numberFormat('rupiahangsuran_".$i."');\" ".$disabled."></td>";
						//$data.="<td><img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=savedetail('".$notransaksi."','".$i."') src='images/save.png'/></td>";
						//$data.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"updatedetail('".$notransaksi."','".$i."')\"</td>";
						$data.="</tr>";  
	        		}elseif($statrtItung==$maxRow){
	        			$tempTable=false;
        				$isiData=true;
        				$i=$i-1;
        				$data.="</table></td>";
	        		}
	        	} 

        }
    
        $data.= "</tr></table></div></fieldset><br><br>";

        echo $data;
    break;

    case 'insertdt':
    	$_POST['noloan']=str_replace("undefined","",$_POST['noloan']);
        // if ($bulanke=='') {
        //     exit('warning : field was empty.');
        // }

    	foreach ($_POST['arrBulan'] as $key => $val) {
    		# code...
    		#cek datanya ada kaga dipembayaran
        	$sCek="select * from ".$dbname.".keu_pmpeminjamandt_angsuran where noloan='".$_POST['noloan']."' and tenor='".$val."' and notransaksi='".$notransaksi."'";
        	$rCek=fetchData($sCek);
        	if(count($rCek)==0){
        		#cek dulu nih ada inputan angsuranya atau belum,klo belum insert
        		$rupiahangsuran=str_replace(',', '', $_POST['arrAngsuran'][$key]);
		        $strdt="delete from ".$dbname.".keu_detailpinjaman where notransaksi='".$notransaksi."' and bulanke='".$val."' and noloan='".$_POST['noloan']."'";
		        try {
		            $owlPDO->exec($strdt);
		        } catch (PDOException $e) {
		            print " Gagal: " . $e->getMessage() . "\n";
		            die();
		        }
		        #ambil tanggal pencairan
		        #create periode
		        $sCair="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where noloan='".$_POST['noloan']."' and notransaksi='".$notransaksi."'";
		        $rCair=fetchData($sCair);
		        $str="select jatuhtempo,jumlahbulan from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$notransaksi."' ";
		        $rHt=fetchData($str);
		        $tgljatuhtem=substr($rCair[0]['tanggal'],0,7)."-".$rHt[0]['jatuhtempo'];
		        //$tglBuatDpnnya=date('Y-m-d',strtotime('+1 month', $awalcair));
		        if($val==1){
		        	if($rCair[0]['tanggal']<$tgljatuhtem){
		        		$tglCair=strtotime((substr($rCair[0]['tanggal'],0,7)."-01"));
		        		$periodeTenor[$val]=substr($rCair[0]['tanggal'],0,7);
		        	}else{
		        		$tglCair=strtotime((substr($rCair[0]['tanggal'],0,7)."-01"));
		        		$periodeTenor[$val]=date("Y-m",strtotime('+1 month',$tglCair));		
		        	}
		        }else{
		        	$prev=$val-1;
	        		$tglCair=strtotime($periodeTenor[$prev]."-01");
	        		$periodeTenor[$val]=date("Y-m",strtotime('+1 month',$tglCair));		
		        }
		        
		        $str="insert into ".$dbname.".keu_detailpinjaman (notransaksi,bulanke,rupiahangsuran,noloan,periode)
		                values ('".$notransaksi."','".$val."','".$rupiahangsuran."','".$_POST['noloan']."','".$periodeTenor[$val]."')";
		        try{
		            $owlPDO->exec($str); 
		        }catch(PDOException $e){
		            echo " Gagal," . addslashes($e->getMessage()."___".$str);
		        }
        	}
    		
    	}
        


    break;
    case'getByrKe':
    	#norek
    	$sPinjamanHt="select * from ".$dbname.".keu_pmpeminjamanht where notransaksi='".$_POST['notransaksi']."'";
    	$rPinjamanHt=fetchData($sPinjamanHt);
    	$dataHt=$rPinjamanHt[0];

    	$periodedate=$_POST['periodecalculate']."-".$_POST['jatuhtempo'];
		$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));
		$periodedate2 = date("Y-m-d",strtotime("+2 Month",strtotime($periodedate1)));
    	#ambil angsuran terakhir
	   	$sData="select tenor from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$_POST['notransaksi']."' and noloan='".$_POST['noloan']."' order by tenor desc limit 1";
    	//exit('warning'.$sData);
    	$rData=fetchData($sData);

    	#ambil tanggal pencarian dan total nilai rupiah dicairkan
    	$sTglCair="select * from ".$dbname.".keu_pmpeminjamandt_pencairan where notransaksi='".$_POST['notransaksi']."' and noloan='".$_POST['noloan']."'";
    	$rTglCair=fetchData($sTglCair);
    	$bar=$rTglCair[0];
    	if($bar['tanggal']<$periodedate&&$bar['tanggal']>$periodedate1){
			// jika tanggal lebih kecil atau lebih besar dari tangal jatuh tempo tiap bulannya
			//maka jumlah hari bukan sebulan alias harus proporsi
			$periodedate1_ = $bar['tanggal'];
			$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
			$totalday_ = datediff($periodedate1_,$periodedate2_);
		}else{
			$periodedate1_ = $periodedate1;
			$periodedate2_ = date("Y-m-d",strtotime("+1 Month",strtotime($periodedate1)));
			$totalday_ = datediff($periodedate1_,$periodedate2_);
		}
		
		#suku bunga
		$str=" select nilai from ".$dbname.".keu_pmsukubunga where  periode<='".$tanggalpembayaranangsuran."' or periode>='".$tanggalpembayaranangsuran."' and kodebank='".$kodebank."' order by periode desc limit 1";
		// echo $str;
		// exit('warning');
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
        $nilai=$bar['nilai'];

		$sPokok="select rupiahangsuran  from ".$dbname.".keu_detailpinjaman 
		         where notransaksi='".$_POST['notransaksi']."' and noloan='".$_POST['noloan']."' and bulanke='".($rData[0]['tenor']+1)."'";
		$rPokok=fetchData($sPokok);

		#9210101
		#bunga realisasi dari kas bank yang sudah terposting
		$sBunga="select sum(a.jumlah) as jumlah from ".$dbname.".keu_kasbankdt a left join ".$dbname.".keu_kasbankht b 
				 on a.notransaksi=b.notransaksi
		         where a.noakun='9210101' and b.kodeorg='".$_POST['unit']."' and b.tanggal='".tanggalsystemn($_POST['tanggalpembayaranangsuran'])."' and b.posting=1 and rekening='".$dataHt['noakun']."'";
		$rBunga=fetchData($sBunga);
		$totRupiah=$rPokok[0]['rupiahangsuran']+$rBunga[0]['jumlah'];
    	echo ($rData[0]['tenor']+1)."####".$totalday_['days_total']."####".number_format($rPokok[0]['rupiahangsuran'],2)."####".number_format($rBunga[0]['jumlah'],2)."####".number_format($totRupiah,2)."####".$nilai;
    break;
    case'getBungaIsi':
    	$periodedate=$_POST['periodecalculate']."-".$_POST['jatuhtempo'];
		$periodedate1 = date("Y-m-d",strtotime("-1 Month",strtotime($periodedate)));
		$periodedate2 = date("Y-m-d",strtotime("+2 Month",strtotime($periodedate1)));
    	#ambil angsuran terakhir
	   	$sData="select tenor from ".$dbname.".keu_pmpeminjamandt_angsuran where notransaksi='".$_POST['notransaksi']."' and noloan='".$_POST['noloan']."' order by tenor desc limit 1";
    	//exit('warning'.$sData);
    	$rData=fetchData($sData);
    
    default;
	
}
?>