<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$per=checkPostGet('per','');
$unit=checkPostGet('unit','');
$kom=checkPostGet('kom','');

// $periode=checkPostGet('periode','');
$karyawanid=checkPostGet('karyawanid','');
$premi=checkPostGet('premi','');

$golkar=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan');
$namagol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
$namatipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

switch($proses)
{
    case'preview':
	
		if($per==''){
			exit("Error:Periode masih kosong");
		}
		if($unit==''){
			exit("Error:Unit masih kosong");
		}
		
		
		$arrceklist=array('0'=>'','1'=>'v','2'=>'x');

		$tahunGaji=substr($per,0,4);

		$str="select * from ".$dbname.".sdm_5periodegaji where periode='".$per."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tgl1=$bar['tanggalmulai'];
			$tgl2=$bar['tanggalsampai'];


		$sGetKary="select * from ".$dbname.".datakaryawan  where lokasitugas='".$unit."' 
			and (tanggalkeluar>='".$tgl1."' or tanggalkeluar='0000-00-00')
			and (tanggalmasuk<='".$tgl2."' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)
			order by namakaryawan asc";  
		$rGetkary=fetchData($sGetKary);
		foreach($rGetkary as $row => $kar){ 
			$namakar[$kar['karyawanid']]=$kar['namakaryawan'];
			$nikkar[$kar['karyawanid']]=$kar['nik'];
			@$nmJabatan[$kar['karyawanid']]=$kar['namajabatan'];
			$sbgnb[$kar['karyawanid']]=$kar['subbagian'];
			$tipekaryawan[$kar['karyawanid']]=$kar['tipekaryawan'];
		}  

	
        $xi="select distinct * from ".$dbname.".sdm_5periodegaji where periode='".$per."' 
              and kodeorg='".$unit."' and sudahproses='1'";
        $xu=$owlPDO->query($xi) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($xu);
        if($row>0)    
            $aktif2=false;
               else
             $aktif2=true;
          if(!$aktif2)
          {
              exit("Error:Periode gaji untuk ".$unit." sudah ditutup");
          }
  
  
		#periksa apakah sudah tutup buku
		$str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$per."' and 
		 kodeorg='".$unit."' and tutupbuku=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res);
		if($row>0)    
			$aktif=false;
		else
			$aktif=true;
		if(!$aktif){
		  exit("Error:Periode akuntansi untuk ".$unit." sudah tutup buku");
		} 

		$rtgl=rangeTanggal($tgl1, $tgl2);
		if(($tgl2=="")&&($tgl1=="")){
			echo"warning: Periode Penggajian Belum Terinput";
			exit();
		}
		echo"<table cellspacing='1' border='0' class='sortable'>
		<thead class=rowheader>
		<tr>
			<td align=center>No</td>
			<td align=center>".$_SESSION['lang']['nama']."</td>
			<td align=center>".$_SESSION['lang']['nik']."</td>
			<td align=center>".$_SESSION['lang']['subbagian']."</td>
			<td align=center>".$_SESSION['lang']['karyawanid']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			
		";
		foreach($rtgl as $ar => $isi) {
			$qwe=date('D', strtotime($isi));
			echo"<td width=5px align=center>";
			if($qwe=='Sun')
			echo"<font color=red>".substr($isi,8,2)."</font>"; 
			else echo(substr($isi,8,2)); 
			echo"</td>";
		}
		echo"
		<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['hk']." ".$_SESSION['lang']['absensi']."</td>
		<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['hk']." ".$_SESSION['lang']['potongan']."</td>
		<td align=center>".$_SESSION['lang']['rp']." ".$_SESSION['lang']['premi']."</td>
		<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['rp']." ".$_SESSION['lang']['potongan']."</td>
		<td align=center>".$_SESSION['lang']['premi']." Dapat</td>
		</tr></thead>
		<tbody>";
		
		#query sdm_absensi
		$str="select * from ".$dbname.".sdm_absensidt_vw 
				where tanggal between  '".$tgl1."' and '".$tgl2."' and lokasitugas='".$unit."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['absensi']=='H' or $bar['absensi']=='HL' or $bar['absensi']=='MG' or $bar['absensi']=='L'){
				$data[$bar['karyawanid']][$bar['tanggal']]=1;
			}else{
				$data[$bar['karyawanid']][$bar['tanggal']]=2;
			}
		}	

		#query traksi	
		$str="select * from ".$dbname.".vhc_runhk_vw 
				where tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".$unit."' and hk>0 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$data[$bar['karyawanid']][$bar['tanggal']]=1;
		}			
		
		#query bkm rawat
		$str="select * from ".$dbname.".kebun_kehadiran_vw  where tanggal between '".$tgl1."' and '".$tgl2."' 
				and jhk>0 and unit='".$unit."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data[$bar['karyawanid']][$bar['tanggal']]=1;
		}		
		
		#query bkm panen
		$str="select * from ".$dbname.".kebun_prestasi_vs_hk  where tanggal between '".$tgl1."' and '".$tgl2."' 
				and hkpanenperhari>0 and lokasitugas='".$unit."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data[$bar['karyawanid']][$bar['tanggal']]=1;
		}	
		
		#bentuk absen  mandor
		$str="select * from ".$dbname.".kebun_aktifitas where 
				kodeorg='".$unit."' and tanggal between '".$tgl1."' and '".$tgl2."' and nikmandor!=''  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data[$bar['nikmandor']][$bar['tanggal']]=1;
			$data[$bar['nikmandor1']][$bar['tanggal']]=1;
			$data[$bar['keranimuat']][$bar['tanggal']]=1;
			$data[$bar['nikasisten']][$bar['tanggal']]=1;
		}

		
		#query ambil daftar karyawan yang dapat premi
		$str="select * from ".$dbname.".sdm_5gajipokok where tahun='".substr($per,0,4)."' and idkomponen='".$kom."' 
				and karyawanid in (select karyawanid from ".$dbname.".datakaryawan
				where lokasitugas='".$unit."')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$uangtj[$bar['karyawanid']]=$bar['jumlah'];
            $uangtjhr[$bar['karyawanid']]=$bar['jumlah']/25;
            $arrkar[$bar['karyawanid']]=$bar['karyawanid'];
		}
				
		if(count($arrkar)<1){
			exit("Warning:Tidak ada karyawan yang mendapat premi kerajinan");
		}		
				
		foreach($arrkar as $kar){
			@$no+=1;
			echo"<tr class=rowcontent id=row".$no."><td>".$no."</td>
				<td>".$namakar[$kar]."</td>
				<td>".$nikkar[$kar]."</td>
				<td>".$sbgnb[$kar]."</td>
				<td id=karyawanid".$no.">".$kar."</td>
				<td id=per".$no.">".$per."</td>
				";
				foreach($rtgl as $ar => $isi) {
					if(@$data[$kar][$isi]==1){
						echo"<td align=center>".@$arrceklist[$data[$kar][$isi]]."</td> ";
					}else{
						echo"<td align=center><font color=red>".@$arrceklist[$data[$kar][$isi]]."</font></td> ";
					}
					
					//echo"<td>".@$data[$kar][$isi]."</td>";
					if(@$data[$kar][$isi]==1){
						@$cdata[$kar]+=$data[$kar][$isi];
					}else{
						@$cdatapot[$kar]+=$data[$kar][$isi]/2;
					}
				}
				
			#jumlah potongan rp
			@$premipot[$kar]=@$cdatapot[$kar]*@$uangtjhr[$kar];
			
			echo"<td align=right>".@$cdata[$kar]."</td>";
			echo"<td align=right>".@$cdatapot[$kar]."</td>";
			
            echo"
			<td align=right>".$uangtj[$kar]."</td>
			<td width=5px  align=right>".$premipot[$kar]."</td>";				
            echo"<td width=5px  align=right id=premi".$no.">".($uangtj[$kar]-@$premipot[$kar])."</td>";				
		}	
		echo"<button class=mybutton onclick=deletepremi(".$no.");>".$_SESSION['lang']['proses']."</button>";
		echo"</tbody></table>";
    break;


    case'delete':
        $str="delete from ".$dbname.".sdm_premi where kodeorg='".$unit."' and periode='".$per."' and jenis='".$kom."'  ";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
    break;
    
    
    case'savedata':
        if($premi=='0' or $premi==''){
        }else { 
            $str="insert into ".$dbname.".sdm_premi (`kodeorg`,`periode`,`karyawanid`,`jenis`,`premi`,`updateby`)
            values ('".$unit."','".$per."','".$karyawanid."','".$kom."','".$premi."','".$_SESSION['standard']['userid']."')";
            try{$owlPDO->exec($str);
                }
                catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        }
        
    break;
	
  
    default;	
	
	
}

?>