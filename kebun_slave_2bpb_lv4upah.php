<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$blok = checkPostGet('blok', '');
$per2 = checkPostGet('per2', '');
$tipe = checkPostGet('tipe', '');
$tipeakun = checkPostGet('tipeakun', '');

$tahun=substr($per2,0,4);
$per1=$tahun.'-01';
$tgl1=$tahun.'-01-01';

$satkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');

$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
##ambil tanggal akhir
$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per2."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
    $tgl2=$bar['tanggalsampai'];

#bentuk untuk bgt..
$expblnbgt=  explode('-', $per2);
$blnbgt=$expblnbgt[1];



$str="select * from ".$dbname.".setup_blok_tahunan where kodeorg = '".$blok."' and tahun='".str_replace('-', '', $per2)."' ";

//exit('Error :'.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($res);
if($numrows==0){

$str="select * from ".$dbname.".setup_blok where kodeorg = '".$blok."' ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

}
$bar=$res->fetch();
    $luas=$bar['luasareaproduktif'];
    $pkk=$bar['jumlahpokok'];
    $tt=$bar['tahuntanam'];


$stream="";


if ($method=='excel4') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
$stream.="
        <tr>
            <td bgcolor='black'><font color='#FF0000'><b>".$_SESSION['lang']['blok']."</b></font></td>
            <td align=left  bgcolor='black'><font color='#FF0000'><b>".getNamaOrg($blok)."</b></font></td>
        </tr>
		<tr class=rowcontent>
			<td >".$_SESSION['lang']['luas']."</td>
            <td align=right>".$luas."</td>
		</tr>
        <tr class=rowcontent>
            <td>".$_SESSION['lang']['thntnm']."</td>
            <td align=right>".$tt."</td>
        </tr>
		<tr class=rowcontent>
			<td>".$_SESSION['lang']['pokok']."</td>
            <td align=right>".number_format($pkk)."</td>
		</tr>
		</table>";
$stream.="<br>";	


if ($method=='excel4') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
	$stream.="<thead>
	<tr class='rowheader'>
		<th align='center' rowspan='2'>".$_SESSION['lang']['noakun']."</th align='center'>
		<th align='center' rowspan='2'>".$_SESSION['lang']['kodekegiatan']."</th align='center'>
		<th align='center' rowspan='2'>".$_SESSION['lang']['namakegiatan']."</th align='center'>
		
		<th align='center' rowspan='2'>".$_SESSION['lang']['nojurnal']."</th align='center'>
		<th align='center' rowspan='2'>".$_SESSION['lang']['notransaksi']."</th align='center'>
		<th align='center' rowspan='2'>".$_SESSION['lang']['satuan']."</th align='center'>
		<th align='center' colspan='3'>".$_SESSION['lang']['upah']."</th align='center'>
		<th align='center' colspan='4'>".$_SESSION['lang']['material']."</th align='center'>
	</tr>
	<tr>
		<th align='center'>".$_SESSION['lang']['fisik']."</th align='center'>
		<th align='center'>".$_SESSION['lang']['jhk']."</th align='center'>
		<th align='center'>".$_SESSION['lang']['biaya']."</th align='center'>
		<th align='center'>".$_SESSION['lang']['nama']."</th align='center'>
		<th align='center'>".$_SESSION['lang']['satuan']."</th align='center'>
		<th align='center'>".$_SESSION['lang']['volume']."</th align='center'>
		<th align='center'>".$_SESSION['lang']['biaya']."</th align='center'>
		
	</tr>
    </thead>
    <tbody>";

	

$akun=array();
$kodekegiatan=array();
$notransaksi=array();

	

#selain pnn

if($tipeakun=='6110101')
{
	
	$str="select jurnal,notransaksi,kodekegiatan,kodeorg,hasilkerja,hkpanenperhari,tanggal,substr(kodekegiatan,1,7) as noakun 
		from ".$dbname.".kebun_prestasi_vs_hk where tanggal like '".$per2."%' and kodeorg='".$blok."' 
		and kodekegiatan='0'  and jurnal=1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
		$notransaksi[$bar['notransaksi']]=$bar['notransaksi'];
		$akun['6110101']='6110101';
		$kodekegiatan['611010101']='611010101';
		$listkeg['6110101']['611010101']='611010101';
		$listnotransaksi['6110101']['611010101'][$bar['notransaksi']]=$bar['notransaksi'];
		
			$row['6110101']['611010101'][$bar['notransaksi']]=$bar['notransaksi'];
		
		@$upahbifisik['6110101']['611010101'][$bar['notransaksi']]+=$bar['hasilkerja'];
		@$upahbihk['6110101']['611010101'][$bar['notransaksi']]+=$bar['hkpanenperhari'];
	}
}




$str="select b.jurnal,a.notransaksi,a.kodekegiatan,a.kodeorg,a.hasilkerja,a.jumlahhk,b.tanggal,substr(a.kodekegiatan,1,7) as noakun 
		from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
		where b.tanggal like '".$per2."%' and a.kodeorg='".$blok."' 
		and a.kodekegiatan like '".$tipeakun."%' and b.jurnal=1";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$notransaksi[$bar['notransaksi']]=$bar['notransaksi'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listkeg[$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listnotransaksi[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']]=$bar['notransaksi'];
	
		$row[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']]=$bar['notransaksi'];
	
	@$upahbifisik[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']]+=$bar['hasilkerja'];
	@$upahbihk[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']]+=$bar['jumlahhk'];
}


#jurnal

$str="select noakun,kodekegiatan,noreferensi,nojurnal,jumlah 
		from ".$dbname.".keu_jurnaldt_vw where kodeblok='".$blok."' 
		and periode='".$per2."' and noakun like '".$tipeakun."%' and 
		((nojurnal not like '%INV%') and (nojurnal not like '%VHC%') and (nojurnal not like '%SPK%'))";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	if($bar['kodekegiatan']=='')
	{
		$row[$bar['noakun']][$bar['noakun']][$bar['noreferensi']]=$bar['noreferensi'];
		$notransaksi[$bar['noreferensi']]=$bar['noreferensi'];
		$akun[$bar['noakun']]=$bar['noakun'];
		$kodekegiatan[$bar['noakun']]=$bar['noakun'];
		$listkeg[$bar['noakun']][$bar['noakun']]=$bar['noakun'];
		$listnotransaksi[$bar['noakun']][$bar['noakun']][$bar['noreferensi']]=$bar['noreferensi'];
		$jurnal[$bar['noakun']][$bar['noakun']][$bar['noreferensi']]=$bar['nojurnal'];
		@$upahby[$bar['noakun']][$bar['noakun']][$bar['noreferensi']]+=$bar['jumlah'];
	}
	else
	{
		if($bar['noreferensi']=='')
		{
			$row[$bar['noakun']][$bar['kodekegiatan']]['jurnal_memorial']='jurnal_memorial';
			$notransaksi['jurnal_memorial']='jurnal_memorial';
			$akun[$bar['noakun']]=$bar['noakun'];
			$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
			$listkeg[$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
			$listnotransaksi[$bar['noakun']][$bar['kodekegiatan']]['jurnal_memorial']='jurnal_memorial';
			$jurnal[$bar['noakun']][$bar['kodekegiatan']]['jurnal_memorial']=$bar['nojurnal'];
			@$upahby[$bar['noakun']][$bar['kodekegiatan']]['jurnal_memorial']+=$bar['jumlah'];
		}
		else
		{
			$row[$bar['noakun']][$bar['kodekegiatan']][$bar['noreferensi']]=$bar['noreferensi'];
			$notransaksi[$bar['noreferensi']]=$bar['noreferensi'];
			$akun[$bar['noakun']]=$bar['noakun'];
			$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
			$listkeg[$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
			$listnotransaksi[$bar['noakun']][$bar['kodekegiatan']][$bar['noreferensi']]=$bar['noreferensi'];
			$jurnal[$bar['noakun']][$bar['kodekegiatan']][$bar['noreferensi']]=$bar['nojurnal'];
			@$upahby[$bar['noakun']][$bar['kodekegiatan']][$bar['noreferensi']]+=$bar['jumlah'];
		}
	}
}	

#material
$str="select notransaksi,a.kodebarang,kwantitas,hargatotal,kodekegiatan,substr(kodekegiatan,1,7) as noakun ,namabarang,satuan
		from  ".$dbname.".kebun_pakai_material_vw a left join ".$dbname.".log_5masterbarang b
		on a.kodebarang=b.kodebarang where tanggal like '".$per2."%' and kodeorg='".$blok."' and kodekegiatan  like '".$tipeakun."%'  ";						
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kodebarang[$bar['kodebarang']]=$bar['kodebarang'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
	
	$listkeg[$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listbarang[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']][$bar['kodebarang']]=$bar['kodebarang'];
	$nmbrg[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']][$bar['kodebarang']]=$bar['namabarang'];
	$satbrg[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']][$bar['kodebarang']]=$bar['satuan'];
	@$volbrg[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']][$bar['kodebarang']]+=$bar['kwantitas'];
	@$bybrg[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']][$bar['kodebarang']]+=$bar['hargatotal'];
	
	@$rowbrg[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']]+=1;
	
	$rowbrgrow[$bar['noakun']][$bar['kodekegiatan']][$bar['notransaksi']][$bar['kodebarang']]=$bar['kodebarang'];
	
	//array_push($rowbrgrow,$bar['kodebarang']);
	
}


$str="select namabarang,a.satuan,kodeblok,a.kodebarang,jumlah,hartot,
		kodekegiatan,substr(kodekegiatan,1,5) as akunlima,notransaksi,
		substr(kodekegiatan,1,7) as noakun from ".$dbname.".log_transaksi_vw a left join ".$dbname.".log_5masterbarang b
		on a.kodebarang=b.kodebarang where  tanggal like '".$per2."%'  and kodekegiatan  like '".$tipeakun."%' and
		kodeblok='".$blok."' and statusjurnal=1 and tipetransaksi=5 and notransaksireferensi IS NULL";		
	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$rowcont=owlBaris($res);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	if($rowcont==0)
	{
		@$cekgudang[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]=0;
	}
	else
	{
		@$cekgudang[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]+=1;
	}
		//[$noakun][$kdkeg][$kdbrg]
	
	
	$akun[$bar['noakun']]=$bar['noakun'];
	$kodebarang[$bar['kodebarang']]=$bar['kodebarang'];
	$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listkeg[$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	$listbarang[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]=$bar['kodebarang'];
	$nmbrg[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]=$bar['namabarang'];
	$satbrg[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]=$bar['satuan'];
	$nogudang[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]=$bar['notransaksi'];
	//$nojurnal[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]=$bar['notransaksi'];
	@$volbrg[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]+=$bar['jumlah'];
	@$bybrg[$bar['noakun']][$bar['kodekegiatan']][$bar['kodebarang']]+=$bar['hartot'];
}

array_multisort($akun,SORT_ASC);
array_multisort($kodekegiatan,SORT_ASC);
array_multisort($notransaksi,SORT_ASC);


	
	

	
$cek = array();
foreach($akun as $noakun)
{
	foreach($kodekegiatan as $kdkeg)
	{
		if(@$listkeg[$noakun][$kdkeg]!='')
		{
			
			if(@$nmkeg[$kdkeg]=='')
			{
				@$namakeg=$nmakun[$kdkeg];
			}
			else
			{
				@$namakeg=$nmkeg[$kdkeg];
			}
			
			
				foreach($notransaksi as $notran)
				{
					
					if(@$listnotransaksi[$noakun][$kdkeg][$notran]!='')
					{
						if(is_array(@$kodebarang))
						{
							
					
							foreach($kodebarang as $kdbrg)
							{
								if(@$listbarang[$noakun][$kdkeg][$notran][$kdbrg]!='')
								{
									
									if(@$notran==@$notranret)
									{
										// $upahby[$noakun][$kdkeg][$notran]='';
										// $upahbihk[$noakun][$kdkeg][$notran]='';
										// $upahbifisik[$noakun][$kdkeg][$notran]='';
										$by[$noakun][$kdkeg][$notran]='';
										$fis[$noakun][$kdkeg][$notran]='';
										$hk[$noakun][$kdkeg][$notran]='';
									}
									else
									{
										// $upahby[$noakun][$kdkeg][$notran]=$upahby[$noakun][$kdkeg][$notran];
										// $upahbifisik[$noakun][$kdkeg][$notran]=$upahbifisik[$noakun][$kdkeg][$notran];
										// $upahbihk[$noakun][$kdkeg][$notran]=$upahbihk[$noakun][$kdkeg][$notran];
										$by[$noakun][$kdkeg][$notran]=$upahby[$noakun][$kdkeg][$notran];
										$fis[$noakun][$kdkeg][$notran]=$upahbifisik[$noakun][$kdkeg][$notran];
										$hk[$noakun][$kdkeg][$notran]=$upahbihk[$noakun][$kdkeg][$notran];
									}
									
										$tipetran=explode('/',$notran); 	
									
										$stream.="<tr class=rowcontent>
										<td>".$noakun."</td>
										<td>".$kdkeg."</td>
										<td>".$namakeg."</td>
										<td valign='top'  style=cursor:pointer; title='click detail' onclick=detailjurnal('".$jurnal[$noakun][$kdkeg][$notran]."','".$blok."','".$kdkeg."','event','".$per2."')>".$jurnal[$noakun][$kdkeg][$notran]."</td>
										<td valign='top'  style=cursor:pointer; title='click detail' onclick=detaildata('".$notran."','event','".$tipetran[2]."','".$blok."')>".$notran."</td>
										<td valign='top'>".$satkeg[$kdkeg]."</td>
										<td valign='top' align=right>".@number_format($fis[$noakun][$kdkeg][$notran],2)."</td>
										<td valign='top' align=right>".@number_format($hk[$noakun][$kdkeg][$notran],2)."</td>
										<td  align=right valign='top'>".@number_format($by[$noakun][$kdkeg][$notran])."</td>
										<td  style=cursor:pointer; title='click detail' onclick=detailbarang('".$blok."','".$per2."','".$kdkeg."','".$kdbrg."','event')>".$nmbrg[$noakun][$kdkeg][$notran][$kdbrg]."</td>
										<td>".$satbrg[$noakun][$kdkeg][$notran][$kdbrg]."</td>
										<td align=right>".@number_format($volbrg[$noakun][$kdkeg][$notran][$kdbrg],2)."</td>
										<td align=right>".@number_format($bybrg[$noakun][$kdkeg][$notran][$kdbrg])."</td>
											";
											
											// $stupahbihk[$noakun][$kdkeg]+=$upahbihk[$noakun][$kdkeg][$notran];
											// $stupahby[$noakun][$kdkeg]+=$upahby[$noakun][$kdkeg][$notran];
										@$stbybrg[$noakun][$kdkeg]+=$bybrg[$noakun][$kdkeg][$notran][$kdbrg];
										@$stby[$noakun][$kdkeg]+=$by[$noakun][$kdkeg][$notran];
										@$stfis[$noakun][$kdkeg]+=$fis[$noakun][$kdkeg][$notran];
										@$sthk[$noakun][$kdkeg]+=$hk[$noakun][$kdkeg][$notran];
											
											
									$notranret=$notran;		
									
									
								}
							}	
						}
						@$notranretz=$notranret;
						
						//echo $notranret._.$notranretz._________;

						if($notran==$notranretz)
						{	
						}
						else
						{
							
							
							$tipetran=explode('/',$notran); 	
							$by[$noakun][$kdkeg][$notran]=$upahby[$noakun][$kdkeg][$notran];
							@$fis[$noakun][$kdkeg][$notran]=$upahbifisik[$noakun][$kdkeg][$notran];
							@$hk[$noakun][$kdkeg][$notran]=$upahbihk[$noakun][$kdkeg][$notran];
							
							if(substr($notran,0,3)=='ALK')
							{
								$jurnal[$noakun][$kdkeg][$notran]=$notran;
							}
							else
							{
								$jurnal[$noakun][$kdkeg][$notran]=$jurnal[$noakun][$kdkeg][$notran];
							}
							
							$stream.="</tr>";
							$stream.="<tr class=rowcontent>
									<td>".$noakun."</td>
									<td>".$kdkeg."</td>
									<td>".$namakeg."</td>
									<td style=cursor:pointer; title='click detail' onclick=detailjurnal('".$jurnal[$noakun][$kdkeg][$notran]."','".$blok."','".$kdkeg."','event','".$per2."')>".@$jurnal[$noakun][$kdkeg][$notran]."</td>
									<td style=cursor:pointer; title='click detail' onclick=detaildata('".@$notran."','event','".@$tipetran[2]."','".@$blok."')>".@$notran."</td>
									<td>".@$satkeg[$kdkeg]."</td>
									<td align=right>".@number_format($fis[$noakun][$kdkeg][$notran],2)."</td>
									<td align=right>".@number_format($hk[$noakun][$kdkeg][$notran],2)."</td>
									<td align=right>".@number_format($by[$noakun][$kdkeg][$notran])."</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									";
							@$stby[$noakun][$kdkeg]+=$by[$noakun][$kdkeg][$notran];
							@$stfis[$noakun][$kdkeg]+=$fis[$noakun][$kdkeg][$notran];
							@$sthk[$noakun][$kdkeg]+=$hk[$noakun][$kdkeg][$notran];	
						}
								//$stupahbihk[$noakun][$kdkeg]+=$upahbihk[$noakun][$kdkeg][$notran];
								//$stupahby[$noakun][$kdkeg]+=$upahby[$noakun][$kdkeg][$notran];
					}
				}
				if(is_array(@$kodebarang))
				{
					foreach($kodebarang as $kdbrg)
					{
						if(@$listbarang[$noakun][$kdkeg][$kdbrg]!='')
						{
							$stream.="<tr class=rowcontent>
									<td>".$noakun."</td>
									<td>".$kdkeg."</td>
									<td>".$namakeg."</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td style=cursor:pointer; title='click detail' onclick=detailbarang('".$blok."','".$per2."','".$kdkeg."','".$kdbrg."','event')>".$nmbrg[$noakun][$kdkeg][$kdbrg]."</td>
									<td>".$satbrg[$noakun][$kdkeg][$kdbrg]."</td>
									<td align=right>".@number_format($volbrg[$noakun][$kdkeg][$kdbrg],2)."</td>
									<td align=right>".@number_format($bybrg[$noakun][$kdkeg][$kdbrg])."</td>
								</tr>
									";
									@$stbybrg[$noakun][$kdkeg]+=$bybrg[$noakun][$kdkeg][$kdbrg];
						}
					}
				}
			$stream.="</tr>";
		
		}	
		$stream.="
			<tr  bgcolor=#80FFFE>
				<td colspan='7'>".$_SESSION['lang']['total']."</td>
				<td align=right>".@number_format($sthk[$noakun][$kdkeg],2)."</td>
				<td align=right>".@number_format($stby[$noakun][$kdkeg])."</td>
				<td></td>
				<td></td>
				<td></td>
				<td align=right>".@number_format($stbybrg[$noakun][$kdkeg])."</td>
			</tr>
			";
			@$gtupahbihk+=$sthk[$noakun][$kdkeg];	
			@$gtupahby+=$stby[$noakun][$kdkeg];	
			@$gtbybrg+=$stbybrg[$noakun][$kdkeg];
	}
	
}
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan='7'>".$_SESSION['lang']['grnd_total']."</td>
			<td align=right>".@number_format($gtupahbihk,2)."</td>
			<td align=right>".@number_format($gtupahby)."</td>
			<td></td>
			<td></td>
			<td></td>
			<td align=right>".@number_format($gtbybrg)."</td>
		</tr>
		";


$stream.="
 </tbody>
     </table>";

switch ($method) {
######PREVIEW
    case 'html4':
		//echo $blok;
		//echo "<br>";
		echo"
			<button id=tomboldetail class=mybutton onclick=kehtml1()>Level 1</button> 
			<button id=tomboldetail class=mybutton onclick=kehtml2()>Level 2</button>
			<button id=tomboldetail class=mybutton onclick=kehtml3()>Level 3</button>
			<button id=tomboldetail class=mybutton disabled>Level 4</button>
		";
		
		echo"<br>";
		
		echo "
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 1</button>   
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 2</button>   
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 3</button> 
			<button id=tomboldetail class=mybutton onclick=excel4(event,'".$blok."','".$per2."','".$tipeakun."','".$tipe."')>" . $_SESSION['lang']['excel'] . " 4</button>  			
		";

		
        echo "<br>";
		echo "<br>";
        echo $stream;
        break;

######EXCEL	
    case 'excel4':
      
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "BIAYA_DAN_PRODUKSI_PERBLOK_" . $kdorg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
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
?>