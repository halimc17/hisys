<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$per=checkPostGet('per','');
$pt=checkPostGet('pt','');
$karyawanid=checkPostGet('karyawanid','');
$tipe=checkPostGet('tipe','');



$optNmKomponen=  makeOption($dbname, 'sdm_ho_component', 'id,name');
$optnmjab=  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');


switch($proses){
	
	
	case'prosespph':
		// exit("Error:A".$karyawanid);
		$str="select * from ".$dbname.".sdm_ho_pph21jabatan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$persenjabatan=$bar['persen'];
			@$maxjabatan=$bar['max'];
			
		$str="select id,value from ".$dbname.".sdm_ho_pph21_ptkp";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$ptkpdata[$bar['id']]=$bar['value']/12;
		} 	

		$str="select level,percent,upto from ".$dbname.".sdm_ho_pph21_kontribusi order by level";
		$urut=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$pphtarif[$urut]    =$bar['upto'];
			$pphpercent[$urut]  =$bar['percent']/100;      
			$urut+=1;  
		}    
		
		
		$str="select a.*,plus from ".$dbname.".sdm_gajiho a left join ".$dbname.".sdm_ho_component b 
				on a.idkomponen=b.id where karyawanid='".$karyawanid."'
				and periodegaji='".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row<1){
			exit("Warning:Data Kosong");
		}
		while($bar=$res->fetch()){
			if($bar['plus']==1){
				@$dtkomplus[$bar['idkomponen']]=$bar['idkomponen'];
			}else{
				@$dtkommin[$bar['idkomponen']]=$bar['idkomponen'];
			}
			$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
			$rupiah[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
		}
		
		
		$str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nik[$bar['karyawanid']]=$bar['nik'];
			$nmkar[$bar['karyawanid']]=$bar['namakaryawan'];
			@$stpajak[$bar['karyawanid']]=$bar['statuspajak'];
			$tpkar[$bar['karyawanid']]=$bar['tipekaryawan'];
			$jabatan[$bar['karyawanid']]=$bar['kodejabatan'];
			$statuspajak[$bar['karyawanid']]=$bar['statuspajak'];
			$jeniskelamin[$bar['karyawanid']]=$bar['jeniskelamin'];
			$npwp[$bar['karyawanid']]=$bar['npwp'];
			$subbagian[$bar['karyawanid']]=$bar['subbagian'];
			$kdunit[$bar['karyawanid']]=$bar['lokasitugas'];
			$kdpt[$bar['karyawanid']]=$bar['kodeorganisasi'];
			
			$ptkp[$bar['karyawanid']]=$ptkpdata[str_replace("K","",$bar['statuspajak'])];	
			
		}
		@$dtkommin['4']='4';
		@$dtkomplus['6']='6';
		
		
		$rupiah[$karyawanid]['6']=0;
		$pph[$karyawanid]=0;
		
		$str="select * from ".$dbname.".sdm_pphkaryawan where 
			karyawanid='".$karyawanid."' and periode='".$per."' and pt='".$pt."'
			order by nourut desc limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$rupiah[$karyawanid]['6']=$bar['pph'];
		}
		
		
		foreach ($dtkarid as $karid){	
			
			// $rupiah[$karid]['6']=0;
			// $pph[$karid]=0;
			
			foreach (@$dtkomplus as $komplus){
				@$tkomplus[$karid]+=$rupiah[$karid][$komplus];
				@$subtkomplus[$komplus]+=$rupiah[$karid][$komplus];
				@$gtkomplus[$komplus]+=$rupiah[$karid][$komplus];
			}
		
			foreach (@$dtkommin as $kommin){
				$rupiah[$karid]['4']=$tkomplus[$karid]*$persenjabatan/100;
				if($rupiah[$karid]['4']>$maxjabatan){
					$rupiah[$karid]['4']=$maxjabatan;
				}
				
				@$tkommin[$karid]+=$rupiah[$karid][$kommin];
				@$subtkommin[$kommin]+=$rupiah[$karid][$kommin];
				@$gtkommin[$kommin]+=$rupiah[$karid][$kommin];
			}

			$tnettokar[$karid]=$tkomplus[$karid]-$tkommin[$karid];
			
			
			
			$pkpawal[$karid]=($tnettokar[$karid]-$ptkp[$karid])/1000;
			$pkp[$karid]=floor($pkpawal[$karid])*1000;
			if($pkp[$karid]<0){
				$pkp[$karid]=0;
			}
			#= bentuk data pph21
			if($pkp[$karid]>0){     
				if($pkp[$karid]<($pphtarif[0]+1)){
					$pph[$karid]=$pkp[$karid]*$pphpercent[0];
				}
				else if($pkp[$karid]<($pphtarif[1]+1)){
					$pph[$karid]=$pphtarif[0]*$pphpercent[0]+($pkp[$karid]-$pphtarif[0])*$pphpercent[1];

				}else if($pkp[$karid]<($pphtarif[2]+1)){
					$pph[$karid]=$pphtarif[0]*$pphpercent[0]+($pphtarif[1]-$pphtarif[0])*$pphpercent[1]+($pkp[$karid]-$pphtarif[1])*$pphpercent[2];
				}else{
					$pph[$karid]=$pphtarif[0]*$pphpercent[0]+($pphtarif[1]-$pphtarif[0])*$pphpercent[1]+$pphtarif[1]*$pphpercent[2]+($pkp[$karid]-$pphtarif[2])*$pphpercent[3];
				}
			}
				
			if($npwp[$karid]==''){
				$pph[$karid]=$pph[$karid]+($pph[$karid]*20/100);
			}
		}
		
		
		#= bentuk no urut
		$str="select count(*) as jumlah from ".$dbname.".sdm_pphkaryawan where karyawanid='".$karyawanid."' and periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nourut=$bar['jumlah']+1;
			
		
		#= insert table jika beda insert
		if($rupiah[$karyawanid]['6']!=$pph[$karyawanid]){
			$str="insert into ".$dbname.".sdm_pphkaryawan (`karyawanid`,`periode`,`pph`,`nourut`,`unit`,`pt`)
			values ('".$karyawanid."','".$per."','".$pph[$karyawanid]."','".$nourut."','".$kdunit[$karyawanid]."','".$kdpt[$karyawanid]."')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}
		}
		
		
		
		echo $karyawanid."####".$per."####".$rupiah[$karyawanid]['6']."####".$pph[$karyawanid];
		
		
	break;
	
	
	
	
	
    case 'preview':
	
		$str="select * from ".$dbname.".sdm_ho_pph21jabatan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$persenjabatan=$bar['persen'];
			@$maxjabatan=$bar['max'];
			
		$str="select id,value from ".$dbname.".sdm_ho_pph21_ptkp";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$ptkpdata[$bar['id']]=$bar['value']/12;
		} 	

		$str="select level,percent,upto from ".$dbname.".sdm_ho_pph21_kontribusi order by level";
		$urut=0;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$pphtarif[$urut]    =$bar['upto'];
			$pphpercent[$urut]  =$bar['percent']/100;      
			$urut+=1;  
		}    

		$str="select a.*,plus from ".$dbname.".sdm_gajiho a left join ".$dbname.".sdm_ho_component b 
				on a.idkomponen=b.id where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') 
				and periodegaji='".$per."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row<1){
			exit("Warning:Data Kosong");
		}
		while($bar=$res->fetch()){
			if($bar['plus']==1){
				@$dtkomplus[$bar['idkomponen']]=$bar['idkomponen'];
			}else{
				@$dtkommin[$bar['idkomponen']]=$bar['idkomponen'];
			}
			$dtkarid[$bar['karyawanid']]=$bar['karyawanid'];
			$rupiah[$bar['karyawanid']][$bar['idkomponen']]=$bar['jumlah'];
		}
		
		$str="select karyawanid,pph from ".$dbname.".sdm_pphkaryawan where periode='".$per."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$rupiah[$bar['karyawanid']]['6']=$bar['pph'];
		}
		

		$str="select * from ".$dbname.".datakaryawan where kodeorganisasi='".$pt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nik[$bar['karyawanid']]=$bar['nik'];
			$nmkar[$bar['karyawanid']]=$bar['namakaryawan'];
			@$stpajak[$bar['karyawanid']]=$bar['statuspajak'];
			$tpkar[$bar['karyawanid']]=$bar['tipekaryawan'];
			$jabatan[$bar['karyawanid']]=$bar['kodejabatan'];
			$statuspajak[$bar['karyawanid']]=$bar['statuspajak'];
			$jeniskelamin[$bar['karyawanid']]=$bar['jeniskelamin'];
			$npwp[$bar['karyawanid']]=$bar['npwp'];
			$subbagian[$bar['karyawanid']]=$bar['subbagian'];
			
			$ptkp[$bar['karyawanid']]=$ptkpdata[str_replace("K","",$bar['statuspajak'])];	
			
		}

		/*****************************************************************************************************************/

		#= query biaya jabatan
		#= dan ambil persentasenya
		@$dtkommin['4']='4';
		@$dtkomplus['6']='6';

		@$tbrskommin=count($dtkommin)+1;
		@$tbrskomplus=count($dtkomplus)+1;

		/*****************************************************************************************************************/

		if ($proses == 'excel') {
			$stream.= "<table class=sortable cellspacing=1 border=1>";
		} else {
			$stream.= "<table class=sortable cellspacing=1>";
		}

		@array_multisort($dtafd,SORT_ASC);

		$stream.="<thead><tr class=rowcontent>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nomor']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jeniskelamin']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['statuspajak']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['jabatan']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['pt']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['subbagian']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['npwp']."</td>";
			
			$stream.="<td align=center colspan=".$tbrskomplus.">".$_SESSION['lang']['penambah']."</td>";
			$stream.="<td align=center colspan=".$tbrskommin.">".$_SESSION['lang']['pengurang']."</td>";
			$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."<br>Netto</td>";
			$stream.="<td align=center rowspan=2>PTKP<br></td>";
			$stream.="<td align=center rowspan=2>PKP<br></td>";
			$stream.="<td align=center rowspan=2>PPH 21<br>Terutang</td>";
			
		$stream.="</tr>";

		$stream.="<tr>";
		foreach (@$dtkomplus as $komplus){
				$stream.="<td align=center>".$optNmKomponen[$komplus]."</td>";
		}
		$stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
		foreach (@$dtkommin as $kommin){
				$stream.="<td align=center>".$optNmKomponen[$kommin]."</td>";
		}
		$stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
		$stream.="</tr>";	
		$stream.="</thead>";


		foreach ($dtkarid as $karid){	
			@$no++;
			$stream.="<tr class=rowcontent id=row".$no.">";
			$stream.="<td align=center>".$no."</td>";
			if($tipe!='excel') {
				$stream.="<td hidden id=karyawanid".$no.">".$karid."</td>";
			}
			$stream.="<td>".$nik[$karid]."</td>";
			$stream.="<td>".$nmkar[$karid]."</td>";
			$stream.="<td>".$jeniskelamin[$karid]."</td>";
			$stream.="<td>".$statuspajak[$karid]."</td>";
			$stream.="<td>".$optnmjab[$jabatan[$karid]]."</td>";
			$stream.="<td>".$pt."</td>";
			$stream.="<td>".$subbagian[$karid]."</td>";
			$stream.="<td>".$npwp[$karid]."</td>";
			
			// $rupiah[$karid]['6']=0;
			// $pph[$karid]=0;
			
			foreach (@$dtkomplus as $komplus){
				$stream.="<td align=right>".@number_format($rupiah[$karid][$komplus])."</td>";
				@$tkomplus[$karid]+=$rupiah[$karid][$komplus];
				@$subtkomplus[$komplus]+=$rupiah[$karid][$komplus];
				@$gtkomplus[$komplus]+=$rupiah[$karid][$komplus];
			}
			$stream.="<td align=right>".@number_format($tkomplus[$karid])."</td>";
			foreach (@$dtkommin as $kommin){
				$rupiah[$karid]['4']=$tkomplus[$karid]*$persenjabatan/100;
				if($rupiah[$karid]['4']>$maxjabatan){
					$rupiah[$karid]['4']=$maxjabatan;
				}
				$stream.="<td align=right>".@number_format($rupiah[$karid][$kommin])."</td>";
				@$tkommin[$karid]+=$rupiah[$karid][$kommin];
				@$subtkommin[$kommin]+=$rupiah[$karid][$kommin];
				@$gtkommin[$kommin]+=$rupiah[$karid][$kommin];
			}

			$stream.="<td align=right>".@number_format($tkommin[$karid])."</td>";
			$tnettokar[$karid]=$tkomplus[$karid]-$tkommin[$karid];
			$pkpawal[$karid]=($tnettokar[$karid]-$ptkp[$karid])/1000;
			$pkp[$karid]=floor($pkpawal[$karid])*1000;
			if($pkp[$karid]<0){
				$pkp[$karid]=0;
			}
			#= bentuk data pph21
			if($pkp[$karid]>0){     
				if($pkp[$karid]<($pphtarif[0]+1)){
					$pph[$karid]=$pkp[$karid]*$pphpercent[0];
				}
				else if($pkp[$karid]<($pphtarif[1]+1)){
					$pph[$karid]=$pphtarif[0]*$pphpercent[0]+($pkp[$karid]-$pphtarif[0])*$pphpercent[1];

				}else if($pkp[$karid]<($pphtarif[2]+1)){
					$pph[$karid]=$pphtarif[0]*$pphpercent[0]+($pphtarif[1]-$pphtarif[0])*$pphpercent[1]+($pkp[$karid]-$pphtarif[1])*$pphpercent[2];
				}else{
					$pph[$karid]=$pphtarif[0]*$pphpercent[0]+($pphtarif[1]-$pphtarif[0])*$pphpercent[1]+$pphtarif[1]*$pphpercent[2]+($pkp[$karid]-$pphtarif[2])*$pphpercent[3];
				}
			}
			
			if($npwp[$karid]==''){
				$pph[$karid]=$pph[$karid]+($pph[$karid]*20/100);
			}
			
			
			
			
			$stream.="<td align=right>".@number_format($tnettokar[$karid])."</td>";
			$stream.="<td align=right>".@number_format($ptkp[$karid])."</td>";
			$stream.="<td align=right>".@number_format($pkp[$karid])."</td>";
			$stream.="<td align=right>".@number_format($pph[$karid])."</td>";
			$stream.="</tr>";
			

			
			@$tpkp+=$pkp[$karid];
			@$tptkp+=$ptkp[$karid];
			@$tpph+=$pph[$karid];
				
		}
			
		#= total

		$stream.="<tr class=rowcontent>";
			$stream.="<td align=center colspan=9>".$_SESSION['lang']['total']."</td>";
			foreach ($dtkomplus as $komplus){
				$stream.="<td align=right>".@number_format($gtkomplus[$komplus])."</td>";
				@$gtsubtkomplus+=$gtkomplus[$komplus];
				
			}
			$stream.="<td align=right>".@number_format($gtsubtkomplus)."</td>";
			
			foreach ($dtkommin as $kommin){
				$stream.="<td align=right>".@number_format($gtkommin[$kommin])."</td>";	
				@$gtsubtkommin+=$gtkommin[$kommin];
			}
			$stream.="<td align=right>".@number_format($gtsubtkommin)."</td>";
			
			@$gtsubtnetto=$gtsubtkomplus-$gtsubtkommin;
			$stream.="<td align=right>".@number_format($gtsubtnetto)."</td>";
			
			
			$stream.="<td align=right>".@number_format($tptkp)."</td>";
			$stream.="<td align=right>".@number_format($tpkp)."</td>";
			$stream.="<td align=right>".@number_format($tpph)."</td>";
			
			$stream.="</tr>";
			// $stream.="<button class=mybutton onclick=prosespph(".$no.");>".$_SESSION['lang']['proses']."</button>";
		$stream.="<tbody></table>";
		if($tipe!='excel') {
			echo $stream."####".$no;
		}
		
		if($tipe=='excel'){
			$tglSkrg=date("Ymd");
			$nop_="laporan_rekap_gaji_perkaryawan";
			if(strlen($stream)>0)
			{
					if ($handle = opendir('tempExcel')) {
							while (false !== ($file = readdir($handle))) {
							if ($file != "." && $file != ".." && $file != "index.html") {
									@unlink('tempExcel/'.$file);
							}
							}	
							closedir($handle);
					}
					$handle=fopen("tempExcel/".$nop_.".xls",'w');
					if(!fwrite($handle,$stream))
					{
							echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
							exit;
					}
					else
					{
							echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls';
							</script>";
					}
					fclose($handle);
			}    
		}
		
		
    break;
	
	

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
         
        break;	
}
?>