<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include('lib/zLib.php');

$lokasitugas=$_POST['lokasitugas'];
$periode=$_POST['periode'];
$tipekaryawan=$_POST['tipekaryawan'];
$mitmk=$periode."1231";
$tglAbis=date('Y-m-d');


if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){	
	$hwr=" and tipekaryawan in(0,1,2,3,4,5,6,7,8,9,10,11,12)";
	if($tipekaryawan!=''){
		$hwr=" and tipekaryawan='".$tipekaryawan."'";
	}
	$str1="select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,tipekaryawan,nik,COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja, tanggalmasuk,kodegolongan from ".$dbname.".datakaryawan
	where lokasitugas='".$lokasitugas."' and tanggalmasuk<>'0000-00-00' and tanggalmasuk<".$mitmk." and (tanggalkeluar>= '".$tglAbis."'  or tanggalkeluar='0000-00-00')  ".$hwr." ";

}else{
	$hwr=" and tipekaryawan in(0,1,2,3,5,7,8,9,10,11,12)";
	if($tipekaryawan!=''){
		$hwr=" and tipekaryawan='".$tipekaryawan."'";
	}
	
	$str1="select karyawanid,namakaryawan,tanggalmasuk,lokasitugas,tipekaryawan,nik,COALESCE(ROUND(DATEDIFF('".$tglAbis."',tanggalmasuk)/365.25,3),0) as masakerja,kodegolongan from ".$dbname.".datakaryawan
	where  lokasitugas='".$lokasitugas."' and tanggalmasuk<>'0000-00-00' and tanggalmasuk<".$mitmk."  and (tanggalkeluar>= '".$tglAbis."'  or tanggalkeluar='0000-00-00')   ".$hwr." ";
}

//echo $str1;
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res1);
$max=$numrows;

echo"<button class=mybutton onclick=simpanAwal(".$max.")>".$_SESSION['lang']['save']."</button>
	 <table class=sortable cellspacing=1 cellpadding=7 style='width:100%;' border=0>
	 <thead>
		 	<tr class=rowheader>
			 	<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
		   		<td align=center>".$_SESSION['lang']['nik']."</td>
				<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center>".$_SESSION['lang']['tipekaryawan']."</td>
				<td align=center>".$_SESSION['lang']['tanggalmasuk']."</td>
				<td align=center>Masa Kerja Saat Ini</td>
				<td hidden align=center>Masa Kerja Periode Awal Cuti</td>
				<td hidden align=center>".$_SESSION['lang']['dari']."</td>
				<td hidden align=center>".$_SESSION['lang']['tanggalsampai']."</td>
				<td align=center>".$_SESSION['lang']['periode']."</td>
				<td align=center>Adjustment ".$_SESSION['lang']['hakcuti']."</td>
				<td align=center> ".$_SESSION['lang']['keterangan']."</td>
				<td align=center>
					<input type=checkbox id=checkdatall onclick='checkdatall(".$max.")'>All
				</td>
			</tr>
		 </thead>
		 <tbody id=container>"; 
		 
	$no=-1;	 
	while($bar1=$res1->fetch())
	{
		$no+=1;
		
		$periodeawal = $periode-1;
		$kodegolongan = $bar1->kodegolongan;
		
		$awal = date_create($bar1->tanggalmasuk);
		// $akhir = date_create($tglresetcuti);
		$skrg = date_create();
		// $diff = date_diff($awal,$akhir);
		$diffskrg = date_diff($awal,$skrg);
		$tpkar = $bar1->tipekaryawan;
		
		$tglawalcuti = date($periodeawal.'-m-d', strtotime($bar1->tanggalmasuk));
		$tglakhircuti = date($periode.'-m-d', strtotime('-1 day',strtotime($bar1->tanggalmasuk)));
		
		$tglawalcutis = str_replace('-','',$tglawalcuti);
		$awals = str_replace('-','',$bar1->tanggalmasuk);
		
		if(strtotime($tglawalcutis) >= strtotime($awals)){
			$awalperiode = date_create($tglawalcuti);
			$xxx = date_diff($awal,$awalperiode);
			$jlhy = $xxx->y;
			$diffawalperiode = $xxx->y." tahun";
		}
		
		if($hakcuti==''){
			$hakcuti=0;
		}
		
		$opttpkr = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tpkar."'");
		$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

		echo"<tr class=rowcontent id=baris".$no.">
				<td hidden id=karyawanid".$no.">".$bar1->karyawanid."</td>
				<td hidden  id=kodeorg".$no.">".substr($bar1->lokasitugas,0,4)."</td>
				<td>".substr($bar1->lokasitugas,0,4)." - ".$nmOrg[substr($bar1->lokasitugas,0,4)]."</td>
				<td align=center>".$bar1->nik."</td>
				<td >".$bar1->namakaryawan."</td>
				<td align=center id=tipekaryawan".$no.">".$opttpkr[$tpkar]."</td>
				<td align=center>".tanggalnormal($bar1->tanggalmasuk)."</td>
				<td align=right>".$diffskrg->y." tahun ".$diffskrg->m." bulan ".$diffskrg->d." hari</td>				   
				<td hidden align=right>".$diffawalperiode."</td>				   
				<td hidden align=center >".tanggalnormal($tglawalcuti)."</td>
				<td hidden align=center >".tanggalnormal($tglakhircuti)."</td>
				<td align=center id=periode".$no.">".$periode."</td>
				<td align=center id=hak".$no.">
					<input type=number id=myeditcuti".$no." value='".$hakcuti."' style='text-align:center;width:50px;'>
				</td>
				<td align=center>
					<textarea  id=keterangan".$no." style='text-align:center' size=2> </textarea>
				</td>				   
				<td align=center><input type=checkbox id=cekdata".$no."></td>";
	}	
	
echo"</tbody><tfoot></tfoot></table>";
?>