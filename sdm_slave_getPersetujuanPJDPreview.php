<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi=$_GET['notransaksi'];

$namajenis=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

$statsurat = array('PD' =>$_SESSION['lang']['perdin'],'ST' =>$_SESSION['lang']['surattugas']);

$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);	
while($bar=$res->fetch())
{

	$jabatan='';
	$namakaryawan='';
	$bagian='';	
	$karyawanid='';
	$strc="select a.namakaryawan,a.karyawanid,a.bagian,b.namajabatan, a.kodeorganisasi, a.tipekaryawan ,a.kodegolongan
		from ".$dbname.".datakaryawan a left join  ".$dbname.".sdm_5jabatan b
		on a.kodejabatan=b.kodejabatan
		where a.karyawanid=".$bar->karyawanid;
	$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
	$resc->setFetchMode(PDO::FETCH_OBJ);
	while($barc=$resc->fetch())
	{
		$kodegolongankar=$barc->kodegolongan;
	  	$jabatan=$barc->namajabatan;
		$namakaryawan=$barc->namakaryawan;
		$bagian=$barc->bagian;
		$karyawanid=$barc->karyawanid;
		$kKodeorganisasi=$barc->kodeorganisasi;
		$kTipeKaryawan=$barc->tipekaryawan;
	}

	//===============================
	
	$jenissurat=$bar->jenis;
	$namatamu=$bar->namatamu;
	$kodeorg=$bar->kodeorg;
	$persetujuan=$bar->persetujuan;
	$hrd=$bar->hrd; 
	$tujuan3=$bar->tujuan3;
	$tujuan2=$bar->tujuan2;	
	$tujuan1=$bar->tujuan1;
	$tanggalperjalanan=tanggalnormal($bar->tanggalperjalanan);
	$tanggalkembali=tanggalnormal($bar->tanggalkembali);
	$uangmuka=$bar->uangmuka;
	$tugas1=$bar->tugas1;
	$tugas2=$bar->tugas2;
	$tugas3=$bar->tugas3;
	$tujuanlain=$bar->tujuanlain;
	$tugaslain=$bar->tugaslain;
	$pesawat=$bar->pesawat;
	$darat=$bar->darat;
	$laut=$bar->laut;
	$mess=$bar->mess;
	$hotel=$bar->hotel;	
	$statushrd=$bar->statushrd;
	$xhrd=$bar->statushrd;
	$xper=$bar->statuspersetujuan;
	if($statushrd==0)
		$statushrd=$_SESSION['lang']['wait_approval'];
	else if($statushrd==1)
		$statushrd=$_SESSION['lang']['disetujui'];
	else 
		$statushrd=$_SESSION['lang']['ditolak'];
	
	$statuspersetujuan=$bar->statuspersetujuan;
	if($statuspersetujuan==0)
		$perstatus=$_SESSION['lang']['wait_approval'];
	else if($statuspersetujuan==1)
		$perstatus=$_SESSION['lang']['disetujui'];
	else 
		$perstatus=$_SESSION['lang']['ditolak'];

	if ($namatamu!='') {
		$namakaryawan=$namatamu;
	}
	
	//ambil bagian,jabatan persetujuan
		$perjabatan='';
		$perbagian='';
		$pernama='';
	$strf="select a.bagian,b.namajabatan,a.namakaryawan from ".$dbname.".datakaryawan a left join
	       ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		   where karyawanid=".$persetujuan;	   
	$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
	$resf->setFetchMode(PDO::FETCH_OBJ);
	while($barf=$resf->fetch())
	{
		$perjabatan=$barf->namajabatan;
		$perbagian=$barf->bagian;
		$pernama=$barf->namakaryawan;
	}	 
	
	//ambil jabatan, hrd
	$hjabatan='';
	$hbagian='';
	$hnama='';
	$hgolongan='';
	$strf="select a.bagian,b.namajabatan,a.namakaryawan,a.kodegolongan from ".$dbname.".datakaryawan a left join
	       ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
		   where karyawanid=".$hrd;	
	$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
	$resf->setFetchMode(PDO::FETCH_OBJ);
	while($barf=$resf->fetch())
	{
		$hjabatan=$barf->namajabatan;
		$hbagian=$barf->bagian;
		$hnama=$barf->namakaryawan;
		$hgolongan=$barf->kodegolongan;
	}
	
	//Get Lokasi Tugas
	$strLTgs="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$tujuan1."'";
	$resLTgs=$owlPDO->query($strLTgs) or die(print " Gagal: ".PDOException::getMessage());
	$resLTgs->setFetchMode(PDO::FETCH_OBJ);
	while($barLTgs=$resLTgs->fetch()){
		$LTgs=$barLTgs->namaorganisasi;
	}
	
	// PT Tujuan
	$qTujuan = selectQuery($dbname,'organisasi','induk',"kodeorganisasi='".$tujuan2."'");
	$resTujuan = fetchData($qTujuan);
	@$ptTujuan = $resTujuan[0]['induk'];
	
	// Regional Tujuan
	$qRegional = selectQuery($dbname,'bgt_regional_assignment','regional',"kodeunit='".$tujuan2."'");
	$resRegional = fetchData($qRegional);
	@$reg = $resRegional[0]['regional'];
	
	//Get Uang Muka
	function getRangeTanggal($tglAwal,$tglAkhir){
		$jlh = strtotime($tglAkhir) -  strtotime($tglAwal);
		$jlhHari = $jlh / (3600*24);
		return $jlhHari + 1;
	}
	$jlhHari=getRangeTanggal($bar->tanggalperjalanan,$bar->tanggalkembali);

	$sUangMuka="select sum(sekali) as satu, sum(perhari) as dua, sum(hariketiga) as tiga
		from ".$dbname.".sdm_5uangmukapjd where regional='".$reg."' and
		kodegolongan='".$kodegolongankar."' and jenis not in (7,11)";
	$rUangMuka=$owlPDO->query($sUangMuka) or die(print " Gagal: ".PDOException::getMessage());
	$rUangMuka->setFetchMode(PDO::FETCH_OBJ);
	// exit("error : ".$sUangMuka);
	if($rUangMuka) {
		// Uang Non Uang Saku
		while($bUangMuka=$rUangMuka->fetch()) {
			if($jlhHari > 2){
				$jlhUangMuka = (($bUangMuka->satu)+(($bUangMuka->dua)*$jlhHari)) + (($bUangMuka->tiga)*($jlhHari-2));
			}else{
				$jlhUangMuka = (($bUangMuka->satu)+(($bUangMuka->dua)*$jlhHari));
			}
		}
		
		// Uang Saku
		$jenisUS = ($jlhHari > 1)? 11: 7;
		$qUangSaku = selectQuery($dbname,'sdm_5uangmukapjd','sekali,perhari,hariketiga',
								 "regional='".$reg."' and kodegolongan='".$hgolongan."' and jenis = ".$jenisUS);
		$resUangSaku = fetchData($qUangSaku);
		if(!empty($resUangSaku)) {
			$rpUS = $resUangSaku[0];
			$jlhUangMuka += $rpUS['sekali'] + ($rpUS['perhari'] * $jlhHari);
			if($jlhHari > 2){
				$jlhUangMuka += $rpUS['perhari'] * ($jlhHari - 2);
			}
		}
	}
	
  }
  
	


  echo"<div style=\"height:400px;width:100%;overflow:auto;\">";
   echo $_SESSION['lang']['perjalanandinas']." :
      <table class=standard cellspacing=1>
	 <tr class=rowcontent>
	    <td width=150px>".$_SESSION['lang']['nama']."</td><td>:</td>
		<td width=250px>".$namakaryawan."</td>
	 </tr>
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['kodeorg']."</td><td>:</td>
		<td>".$kodeorg."</td>
	 </tr>	 
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['tanggaldinas']."</td><td>:</td>
		<td>".$tanggalperjalanan." &nbsp 
		    ".$_SESSION['lang']['tanggalkembali']." &nbsp 
			".$tanggalkembali."
		</td>
	 </tr>	
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['pemberitugas']."</td><td>:</td>
		<td>".$LTgs."</td>
	 </tr>
	 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['surat']."</td><td>:</td>
		<td>".$statsurat[$jenissurat]."</td>
	 </tr>

	 <tr class=rowcontent>
	   <td>
	      ".$_SESSION['lang']['uangmuka']."
	   </td><td>:</td>
	   <td>
	    <input type=hidden id=nitransaksipjd value='".$notransaksi."'>";
	     
	/*
		 <tr class=rowcontent>
	    <td>".$_SESSION['lang']['transportasi']."/".$_SESSION['lang']['akomodasi']."</td>
		<td>
		     <input type=checkbox id=pesawat disabled ".($pesawat==1?'checked':'')."> ".$_SESSION['lang']['pesawatudara']."
			 <input type=checkbox id=darat disabled ".($darat==1?'checked':'')."> ".$_SESSION['lang']['transportasidarat']."
			 <input type=checkbox id=laut disabled ".($laut==1?'checked':'')."> ".$_SESSION['lang']['transportasiair']."
			 <input type=checkbox id=mess disabled ".($mess==1?'checked':'')."> ".$_SESSION['lang']['mess']."
			 <input type=checkbox id=hotel disabled ".($hotel==1?'checked':'')."> ".$_SESSION['lang']['hotel']."
        </td>
	 </tr>	
	*/	
	// if($xhrd==0 or $xper==0)
	//   {	 
		echo "<span id=oldval style='display:block;'>".number_format($uangmuka,2,'.',',')."</span>";
		if ($jenissurat=='ST') {
			echo "<input type=text class=myinputtextnumber id=newvalpjd onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=17 value='".$uangmuka."'>";
			echo "<button class=mybutton onclick=saveUpdateValPJD()>".$_SESSION['lang']['ganti']."</button>";
		}
	 //  }else{
		// echo "<span id=oldval style='display:block;'>".number_format($uangmuka,2,'.',',')."</span>";
	 //  }
	echo"   
	   </td>
	 </tr> 	 	 
	 </table>";
	 // <table class=standard  cellspacing=1>
	 //   <tr class=rowcontent>
	 //     <td width=150px>
		//      ".$_SESSION['lang']['tujuan']."1
		//  </td><td>:</td>
	 //     <td width=250px>
		//    ".$tujuan2." :
		//    ".$tugas2."
		//   </td> 
		// </tr>
		// <tr class=rowcontent> 
	 //     <td>
		//     ".$_SESSION['lang']['tujuan']."2
		//  </td><td>:</td>
	 //     <td>
		//    ".$tujuan3." :
		//    ".$tugas3."		 
		//   </td>		 		 		 
	 //   </tr>
	   
	 //   <tr class=rowcontent>
	 //     <td>
		//      ".$_SESSION['lang']['tujuan']."3
		//  </td><td>:</td>
	 //     <td>
		//    ".$tujuanlain." :
		//    ".$tugaslain."		 
		//  </td>
		// </tr>
	 // </table>";
       
/*	   
        echo"<br>";
        
				echo"<table class=standard cellspacing=1>";
	echo"
		 <thead><tr class=rowcontent>
			<td>Jenis</td>
			<td>Rupiah</td>
		 </tr></thead>";
  	$str="select * from ".$dbname.".sdm_5uangmukapjd where regional='".$reg."' and
		kodegolongan='".$kodegolongankar."' and jenis not in (7,11)";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){  
		if($jlhHari > 2){
			$detum = (($bar['sekali'])+(($bar['perhari'])*$jlhHari)) + (($bar['hariketiga'])*($jlhHari-2));
		}else{
			$detum = (($bar['sekali'])+(($bar['perhari'])*$jlhHari));
		}
		echo"
		 <tr class=rowcontent>
			<td>".$namajenis[$bar['jenis']]."</td>
			<td>".$detum."</td>
		 </tr>";
	}
	echo"</table><br>";
		*/
		
		
		
	$hidden='';
	$disabled='';
	if($xhrd!=0){
		$hidden='hidden';
		$disabled='disabled';
	}		
		
		
	//echo"<br>";

	if ($jenissurat=='PD' && $namatamu=='' ) {
		
		echo"<hr>";	
        echo"
       	<table width=100%>
        	<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['namakelompok']."</td>
				<td align=center>".$_SESSION['lang']['rupiah']."</td> 
				<td align=center>".$_SESSION['lang']['rupiah']." HRD</td> 
				<td align=center>".$_SESSION['lang']['keterangan']."</td> 
			</tr></thead>";
        
		$nmtipe=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
        $arrJenis=makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
        
        $str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=0 ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		$tby	= 0;
		$tbyhrd	= 0;
        while($bar=$res->fetch()){
			$notran=$bar['notransaksi'];
			$jml=$bar['jumlah'];
			$jmlhrd=$bar['jumlahhrd'];
            $no+=1;
            echo "<tr class=rowcontent>";
            echo "<td align=center>".$no."</td>";
			echo "<td align=left>".$nmtipe[$bar['jenisbiaya']]."</td>";
			echo "<td align=right id=byawal".$no.">".number_format($jml,2)."</td>";
			//<img src='images/puzz.png' style='cursor:pointer;' title='click to get value' ".$hidden." onclick=\"bypindah('".$no."');\">
			echo "<td align=right>
					<input type=text class=myinputtextnumber id=byrp".$no."  ".$disabled." onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=17 value='".number_format($jmlhrd,2)."'>
					<img src=images/gray/save.png class=resicon ".$hidden."  title='Ganti' onclick=\"byganti('".$bar['notransaksi']."','".$bar['jenisbiaya']."','".$bar['detail']."','".$no."');\">
					</td>";
			echo "<td align=left>".$bar['keterangan']."</td>";
            echo "</tr>";
			$tby+=$jmlhrd;
			$tbyhrd+=$jmlhrd;
        }

		/*
		 echo "<tr class=rowcontent>";
            echo "<td align=center colspan=5>".$_SESSION['lang']['total']."</td>";
			echo "<td align=right><b>".number_format($tby,2)."</b></td>";
			echo "<td align=right><b>".number_format($tbyhrd,2)."</b></td>";
			echo "<td><button ".$hidden." class=mybutton onclick=bysetuju('".$notran."')>Jika Uang Muka Pengajuan = Persetujuan HRD</button></td>";
            echo "</tr>";
		
		*/
			
		echo"</table></div>";
	}
	
	
?>
