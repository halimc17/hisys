<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method = checkPostGet('method', '');
$blok = checkPostGet('blok', '');
$per2 = checkPostGet('per2', '');
$tipe = checkPostGet('tipe', '');

$tahun=substr($per2,0,4);
$per1=$tahun.'-01';
$tgl1=$tahun.'-01-01';



##ambil tanggal akhir
$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per2."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
    $tgl2=$bar['tanggalsampai'];

#bentuk untuk bgt..
$expblnbgt=  explode('-', $per2);
$blnbgt=$expblnbgt[1];





$stream="";


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


if ($method=='excel3') {
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
		
if ($method=='excel3') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
		$stream.="<thead>
	<tr>
		
		<th align=center rowspan='3' valign='center'>".$_SESSION['lang']['noakun']."</th>
		<th align=center rowspan='3' valign='center'>".$_SESSION['lang']['namaakun']."</th>
		<th align=center colspan='6'>".$_SESSION['lang']['upah']."</th>
		<th align=center colspan='6'>".$_SESSION['lang']['material']."</th>
		<th align=center colspan='4'>".$_SESSION['lang']['transport']."</th>
		<th align=center colspan='2'>".$_SESSION['lang']['spk']."</th>
		<th align=center colspan='2'>".$_SESSION['lang']['total']."</th>
	</tr>
	<tr>
		<th align=center colspan='3'>".$_SESSION['lang']['bi']."</th>
		<th align=center colspan='3'>".$_SESSION['lang']['sbi']."</th>
		<th align=center rowspan='2' valign='center'>".$_SESSION['lang']['nama']."</th>
		<th align=center rowspan='2' valign='center'>".$_SESSION['lang']['satuan']."</th>
		<th align=center colspan='2'>".$_SESSION['lang']['bi']."</th>
		<th align=center colspan='2'>".$_SESSION['lang']['sbi']."</th>
		
		
		<th align=center colspan='2'>".$_SESSION['lang']['bi']."</th>
		<th align=center colspan='2'>".$_SESSION['lang']['sbi']."</th>
		<th align=center rowspan='2' valign='center'>".$_SESSION['lang']['bi']."</th>
		<th align=center rowspan='2' valign='center'>".$_SESSION['lang']['sbi']."</th>
		<th align=center rowspan='2' valign='center'>".$_SESSION['lang']['bi']."</th>
		<th align=center rowspan='2' valign='center'>".$_SESSION['lang']['sbi']."</th>
	</tr>
	<tr>
		<th align=center>".$_SESSION['lang']['fisik']."</th>
		<th align=center>".$_SESSION['lang']['jhk']."</th>
		<th align=center>".$_SESSION['lang']['biaya']."</th>
		<th align=center>".$_SESSION['lang']['fisik']."</th>
		<th align=center>".$_SESSION['lang']['jhk']."</th>
		<th align=center>".$_SESSION['lang']['biaya']."</th>
		<th align=center>".$_SESSION['lang']['volume']."</th>
		<th align=center>".$_SESSION['lang']['biaya']."</th>
		<th align=center>".$_SESSION['lang']['volume']."</th>
		<th align=center>".$_SESSION['lang']['biaya']."</th>
		<th align=center>".$_SESSION['lang']['volume']."</th>
		<th align=center>".$_SESSION['lang']['biaya']."</th>
		<th align=center>".$_SESSION['lang']['volume']."</th>
		<th align=center>".$_SESSION['lang']['biaya']."</th>
    </tr>
    </thead>
    <tbody>";


#############################################
##############      BI        ###############
#############################################

		
$str="select substr(noakun,1,5) as akunlima,noakun,namaakun from ".$dbname.".keu_5akun 
		where  ((noakun like '611%' or noakun like '621%' or noakun like '126%' or (noakun between '1280101' and '1280222')) and length(noakun)>=5) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$namaakun[$bar['noakun']]=$bar['namaakun'];
}
		
#pnn		
$str="select * from ".$dbname.".kebun_prestasi_vs_hk_detail where tanggal like '".$per2."%' and kodeorg='".$blok."' and jurnal=1 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima['61101']='61101';
	$akun['6110101']='6110101';
	$listakun['61101']['6110101']='6110101';
	@$upahbifisik['61101']['6110101']+=$bar['hasilkerja'];
	@$upahbihk['61101']['6110101']+=$bar['hkpanenperhari'];
}

#selain pnn
$str="select b.jurnal,a.notransaksi,a.kodekegiatan,a.kodeorg,a.hasilkerja,a.jumlahhk,b.tanggal,
		substr(a.kodekegiatan,1,5) as akunlima,substr(a.kodekegiatan,1,7) as noakun 
		from ".$dbname.".kebun_prestasi_detail a left join ".$dbname.".kebun_aktifitas b
		on a.notransaksi=b.notransaksi where b.tanggal like '".$per2."%' and a.kodeorg='".$blok."' 
		and (a.kodekegiatan!='0') and b.jurnal=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima[$bar['akunlima']]=$bar['akunlima'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['akunlima']][$bar['noakun']]=$bar['noakun'];
	@$upahbifisik[$bar['akunlima']][$bar['noakun']]+=$bar['hasilkerja'];
	@$upahbihk[$bar['akunlima']][$bar['noakun']]+=$bar['jumlahhk'];
}
	

$str="select substr(a.noakun,1,5) as akunlima,a.noakun,a.jumlah,a.tanggal,a.kodeblok,a.nojurnal,b.kodejurnal,a.kodebarang from ".$dbname.".keu_jurnaldt_vw a 
		left join ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal 
		where a.kodeblok='".$blok."' and a.tanggal like '".$per2."%' 
		and (a.noakun like '611%' or a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280222')) ";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima[$bar['akunlima']]=$bar['akunlima'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['akunlima']][$bar['noakun']]=$bar['noakun'];
	
	if ($bar['akunlima']=='61102')
	{
		if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$rpbiby[$bar['akunlima']][$bar['noakun']]['lain']+=$bar['jumlah'];
		}
		else
		{
			@$rpbiby[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
	}
	else
	{
		if(substr($bar['kodejurnal'],0,3)=='INV')
		{
			@$rpbiby[$bar['akunlima']][$bar['noakun']]['material']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='VHC')
		{
			@$rpbiby[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$rpbiby[$bar['akunlima']][$bar['noakun']]['lain']+=$bar['jumlah'];
		}
		else
		{
			@$rpbiby[$bar['akunlima']][$bar['noakun']]['upah']+=$bar['jumlah'];	
		}
	}
	@$rpbiby[$bar['akunlima']][$bar['noakun']]['total']+=$bar['jumlah'];	
}	



#material
$str="select namabarang,satuan,kodeorg,a.kodebarang,sum(kwantitas) as kwantitas,sum(hargatotal) as hargatotal,
		kodekegiatan,substr(kodekegiatan,1,5) as akunlima,
		substr(kodekegiatan,1,7) as noakun from ".$dbname.".kebun_pakai_material_detail_vw a left join ".$dbname.".log_5masterbarang b
		on a.kodebarang=b.kodebarang where tanggal like '".$per2."%' and kodeorg='".$blok."' and jurnal=1 group by akunlima,noakun,kodebarang  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kodebarang[$bar['kodebarang']]=$bar['kodebarang'];
	$listbarang[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['kodebarang'];
	$nmbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['namabarang'];
	$satbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['satuan'];
	@$materialbivol[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['kwantitas'];
	@$materialbiby[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['hargatotal'];
	@$rowbi[$bar['akunlima']][$bar['noakun']]+=1;
}


$str="select namabarang,a.satuan,kodeblok,a.kodebarang,sum(jumlah) as jumlah,sum(hartot) as hartot,
		kodekegiatan,substr(kodekegiatan,1,5) as akunlima,
		substr(kodekegiatan,1,7) as noakun from ".$dbname.".log_transaksi_vw a left join ".$dbname.".log_5masterbarang b
		on a.kodebarang=b.kodebarang where  tanggal like '".$per2."%' and 
		kodeblok='".$blok."' and statusjurnal=1 and tipetransaksi=5 and notransaksireferensi IS NULL group by akunlima,noakun,kodebarang  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kodebarang[$bar['kodebarang']]=$bar['kodebarang'];
	$listbarang[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['kodebarang'];
	$nmbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['namabarang'];
	$satbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['satuan'];
	@$materialbivol[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['jumlah'];
	@$materialbiby[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['hartot'];
	@$rowbi[$bar['akunlima']][$bar['noakun']]+=1;
}





$str="select a.jumlah,b.tanggal,a.alokasibiaya,substr(c.noakun,1,5) as akunlima,c.noakun from ".$dbname.".vhc_rundt_detail a 
		left join ".$dbname.".vhc_runht b on a.notransaksi = b.notransaksi
		left join ".$dbname.".vhc_kegiatan c on a.jenispekerjaan = c.kodekegiatan
		where b.tanggal like '".$per2."%' and a.alokasibiaya='".$blok."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$transbihmkm[$bar['akunlima']][$bar['noakun']]+=$bar['jumlah'];
}




#############################################
##############     SD BI      ###############
#############################################

#pnn
$str="select * from ".$dbname.".kebun_prestasi_vs_hk_detail where tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg='".$blok."' and jurnal=1 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima['61101']='61101';
	$akun['6110102']='6110102';
	$listakun['61101']['6110102']='6110102';
	@$upahsdbifisik['61101']['6110102']+=$bar['hasilkerja'];
	@$upahsdbihk['61101']['6110102']+=$bar['hkpanenperhari'];
}


#selain pnn
$str="select b.jurnal,a.notransaksi,a.kodekegiatan,a.kodeorg,a.hasilkerja,a.jumlahhk,b.tanggal,
		substr(a.kodekegiatan,1,5) as akunlima,substr(a.kodekegiatan,1,7) as noakun 
		from ".$dbname.".kebun_prestasi_detail a left join ".$dbname.".kebun_aktifitas b
		on a.notransaksi=b.notransaksi where b.tanggal between '".$tgl1."' and '".$tgl2."' and a.kodeorg='".$blok."' 
		and (a.kodekegiatan!='0') and b.jurnal=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima[$bar['akunlima']]=$bar['akunlima'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['akunlima']][$bar['noakun']]=$bar['noakun'];
	@$upahsdbifisik[$bar['akunlima']][$bar['noakun']]+=$bar['hasilkerja'];
	@$upahsdbihk[$bar['akunlima']][$bar['noakun']]+=$bar['jumlahhk'];
	
}
	

$str="select substr(a.noakun,1,5) as akunlima,a.noakun,a.jumlah,a.tanggal,a.kodeblok,a.nojurnal,
		b.kodejurnal,a.kodebarang from ".$dbname.".keu_jurnaldt_vw a 
		left join ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal 
		where a.kodeblok='".$blok."' and a.tanggal between '".$tgl1."' and '".$tgl2."' 
		and (a.noakun like '611%' or a.noakun like '621%' or a.noakun like '126%' or (a.noakun between '1280101' and '1280222')) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$akunlima[$bar['akunlima']]=$bar['akunlima'];
	$akun[$bar['noakun']]=$bar['noakun'];
	$listakun[$bar['akunlima']][$bar['noakun']]=$bar['noakun'];
	
	if ($bar['akunlima']=='61102')
	{
		if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$rpsdbiby[$bar['akunlima']][$bar['noakun']]['lain']+=$bar['jumlah'];
		}
		else
		{
			@$rpsdbiby[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
	}
	else
	{
		if(substr($bar['kodejurnal'],0,3)=='INV')
		{
			@$rpsdbiby[$bar['akunlima']][$bar['noakun']]['material']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='VHC')
		{
			@$rpsdbiby[$bar['akunlima']][$bar['noakun']]['trans']+=$bar['jumlah'];
		}
		else if(substr($bar['kodejurnal'],0,3)=='SPK')
		{
			@$rpsdbiby[$bar['akunlima']][$bar['noakun']]['lain']+=$bar['jumlah'];
		}
		else
		{
			@$rpsdbiby[$bar['akunlima']][$bar['noakun']]['upah']+=$bar['jumlah'];	
		}
	}
	@$rpsdbiby[$bar['akunlima']][$bar['noakun']]['total']+=$bar['jumlah'];	
}	



$str="select namabarang,satuan,kodeorg,a.kodebarang,sum(kwantitas) as kwantitas,sum(hargatotal) as hargatotal,
		kodekegiatan,substr(kodekegiatan,1,5) as akunlima,
		substr(kodekegiatan,1,7) as noakun from ".$dbname.".kebun_pakai_material_detail_vw a left join ".$dbname.".log_5masterbarang b
		on a.kodebarang=b.kodebarang where tanggal between '".$tgl1."' and '".$tgl2."' and 
		kodeorg='".$blok."' and jurnal=1 group by akunlima,noakun,kodebarang  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kodebarang[$bar['kodebarang']]=$bar['kodebarang'];
	$listbarang[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['kodebarang'];
	$nmbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['namabarang'];
	$satbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['satuan'];
	@$materialsdbivol[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['kwantitas'];
	@$materialsdbiby[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['hargatotal'];
	@$rowsdbi[$bar['akunlima']][$bar['noakun']]+=1;
}

$str="select namabarang,a.satuan,kodeblok,a.kodebarang,sum(jumlah) as jumlah,sum(hartot) as hartot,
		kodekegiatan,substr(kodekegiatan,1,5) as akunlima,
		substr(kodekegiatan,1,7) as noakun from ".$dbname.".log_transaksi_vw a left join ".$dbname.".log_5masterbarang b
		on a.kodebarang=b.kodebarang where tanggal between '".$tgl1."' and '".$tgl2."' and 
		kodeblok='".$blok."' and statusjurnal=1 and tipetransaksi=5 and notransaksireferensi IS NULL group by akunlima,noakun,kodebarang  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$kodebarang[$bar['kodebarang']]=$bar['kodebarang'];
	$listbarang[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['kodebarang'];
	$nmbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['namabarang'];
	$satbrg[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]=$bar['satuan'];
	@$materialsdbivol[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['jumlah'];
	@$materialsdbiby[$bar['akunlima']][$bar['noakun']][$bar['kodebarang']]+=$bar['hartot'];
	@$rowsdbi[$bar['akunlima']][$bar['noakun']]+=1;
}


$str="select a.jumlah,b.tanggal,a.alokasibiaya,substr(c.noakun,1,5) as akunlima,c.noakun from ".$dbname.".vhc_rundt_detail a 
		left join ".$dbname.".vhc_runht b on a.notransaksi = b.notransaksi
		left join ".$dbname.".vhc_kegiatan c on a.jenispekerjaan = c.kodekegiatan
		where b.tanggal between '".$tgl1."' and '".$tgl2."' and a.alokasibiaya='".$blok."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	@$transsdbihmkm[$bar['akunlima']][$bar['noakun']]+=$bar['jumlah'];
}

array_multisort($akunlima,SORT_ASC);
array_multisort($akun,SORT_ASC);

foreach($akunlima as $noakunlima)
{
	if(substr($noakunlima,0,3)==$tipe)
	{
		foreach($akun as $noakun)
		{
			if(@$listakun[$noakunlima][$noakun]!='')
			{
				
				@$rowspanbi=$rowbi[$noakunlima][$noakun];
				@$rowspansdbi=$rowsdbi[$noakunlima][$noakun];
				
				if($rowspanbi>=$rowspansdbi)
				{
					$rowspan=$rowspanbi;
				}
				else
				{
					$rowspan=$rowspansdbi;
				}
				
				
				
				$stream.="
						<tr class=rowcontent>
							<td rowspan='".$rowspan."' valign='top'>".$noakun."</td>
							<td rowspan='".$rowspan."' valign='top'>".$namaakun[$noakun]."</td>
							
							<td valign='top' align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','upah')>".@number_format($upahbifisik[$noakunlima][$noakun])."</td>
							<td valign='top' align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','upah')>".@number_format($upahbihk[$noakunlima][$noakun],2)."</td>
							<td valign='top' align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','upah')>".@number_format($rpbiby[$noakunlima][$noakun]['upah'])."</td>
							<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($upahsdbifisik[$noakunlima][$noakun],2)."</td>
							<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($upahsdbihk[$noakunlima][$noakun],2)."</td>
							<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($rpsdbiby[$noakunlima][$noakun]['upah'])."</td>";
							
						if($rowspan=='')
						{		
							for($i=1;$i<=6;$i++)
							{
								$stream.="<td align=left rowspan='".$rowspan."'></td>";
							}
							$stream.="
							
								<td valign='top' align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','trans')>".@number_format($transbihmkm[$noakunlima][$noakun],2)."</td>
								<td valign='top' align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','trans')>".@number_format($rpbiby[$noakunlima][$noakun]['trans'])."</td>
								<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($transsdbihmkm[$noakunlima][$noakun],2)."</td>
								<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($rpsdbiby[$noakunlima][$noakun]['trans'])."</td>
								<td valign='top' align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','spk')>".@number_format($rpbiby[$noakunlima][$noakun]['lain'])."</td>
								<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($rpsdbiby[$noakunlima][$noakun]['lain'])."</td>
								<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($rpbiby[$noakunlima][$noakun]['total'])."</td>
								<td valign='top' align=right rowspan='".$rowspan."'>".@number_format($rpsdbiby[$noakunlima][$noakun]['total'])."</td>
								";
						}
						else
						{
							$nob=0;
							foreach($kodebarang as $kdbrg)
							{
								
								if(@$listbarang[$noakunlima][$noakun][$kdbrg]!='')
								{
									@$nob++;
									if($nob>1)
									{
										$stream.="<tr class=rowcontent>";
									}
									$stream.="
										<td valign='top' align=left style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','upah')>".$nmbrg[$noakunlima][$noakun][$kdbrg]."</td>
										<td valign='top' align=left style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','upah')>".$satbrg[$noakunlima][$noakun][$kdbrg]."</td>
										<td valign='top' align=right style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','upah')>".@number_format($materialbivol[$noakunlima][$noakun][$kdbrg],2)."</td>
										<td valign='top' align=right style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','upah')>".@number_format($materialbiby[$noakunlima][$noakun][$kdbrg])."</td>
										<td valign='top' align=right>".@number_format($materialsdbivol[$noakunlima][$noakun][$kdbrg],2)."</td>
										<td valign='top' align=right>".@number_format($materialsdbiby[$noakunlima][$noakun][$kdbrg])."</td>
									";
									if($nob==1)
									{
										$stream.="
										<td valign='top'  align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','trans')>".@number_format($transbihmkm[$noakunlima][$noakun],2)."</td>
										<td valign='top'  align=right rowspan='".$rowspan."' style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','trans')>".@number_format($rpbiby[$noakunlima][$noakun]['trans'],2)."</td>
										<td valign='top'  align=right rowspan='".$rowspan."'>".@number_format($transsdbihmkm[$noakunlima][$noakun],2)."</td>
										<td valign='top'  align=right rowspan='".$rowspan."'>".@number_format($rpsdbiby[$noakunlima][$noakun]['trans'])."</td>
										
										<td valign='top'  align=right rowspan='".$rowspan."'  style=cursor:pointer; title='click detail' onclick=html4('".$blok."','".$per2."','".$noakun."','spk')>".@number_format($rpbiby[$noakunlima][$noakun]['lain'])."</td>
										<td valign='top'  align=right rowspan='".$rowspan."'>".@number_format($rpsdbiby[$noakunlima][$noakun]['lain'])."</td>
										
										<td valign='top'  align=right rowspan='".$rowspan."'>".@number_format($rpbiby[$noakunlima][$noakun]['total'])."</td>
										<td valign='top'  align=right rowspan='".$rowspan."'>".@number_format($rpsdbiby[$noakunlima][$noakun]['total'])."</td>
										";
									}
									@$tmaterialbiby[$noakunlima][$noakun]+=$materialbiby[$noakunlima][$noakun][$kdbrg];
									@$tmaterialsdbiby[$noakunlima][$noakun]+=$materialsdbiby[$noakunlima][$noakun][$kdbrg];
									
								}
							}		
						}
						$stream.="</tr>";
						@$stupahbihk[$noakunlima]+=$upahbihk[$noakunlima][$noakun];
						@$strpbiby[$noakunlima]['upah']+=$rpbiby[$noakunlima][$noakun]['upah'];
						@$stupahsdbifisik[$noakunlima]+=$upahsdbifisik[$noakunlima][$noakun];
						@$stupahsdbihk[$noakunlima]+=$upahsdbihk[$noakunlima][$noakun];
						@$strpsdbiby[$noakunlima]['upah']+=$rpsdbiby[$noakunlima][$noakun]['upah'];
						@$stmaterialbiby[$noakunlima]+=$tmaterialbiby[$noakunlima][$noakun];
						@$stmaterialsdbiby[$noakunlima]+=$tmaterialsdbiby[$noakunlima][$noakun];
						
						@$sttransbihmkm[$noakunlima]+=$transbihmkm[$noakunlima][$noakun];
						@$sttranssdbihmkm[$noakunlima]+=$transsdbihmkm[$noakunlima][$noakun];
						
						@$strpbiby[$noakunlima]['trans']+=$rpbiby[$noakunlima][$noakun]['trans'];
						@$strpsdbiby[$noakunlima]['trans']+=$rpsdbiby[$noakunlima][$noakun]['trans'];
						
						@$strpbiby[$noakunlima]['lain']+=$rpbiby[$noakunlima][$noakun]['lain'];
						@$strpsdbiby[$noakunlima]['lain']+=$rpsdbiby[$noakunlima][$noakun]['lain'];
						
						@$strpbiby[$noakunlima]['total']+=$rpbiby[$noakunlima][$noakun]['total'];
						@$strpsdbiby[$noakunlima]['total']+=$rpsdbiby[$noakunlima][$noakun]['total'];
						
			}
		}
		$stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=2>".$_SESSION['lang']['total']."  ".$namaakun[$noakunlima]."</td>
					<td align=right></td>
					<td align=right>".@number_format($stupahbihk[$noakunlima],2)."</td>
					<td align=right>".@number_format($strpbiby[$noakunlima]['upah'])."</td>
					<td align=right>".@number_format($stupahsdbifisik[$noakunlima],2)."</td>
					<td align=right>".@number_format($stupahsdbihk[$noakunlima],2)."</td>
					<td align=right>".@number_format($strpsdbiby[$noakunlima]['upah'])."</td>
					<td align=right></td>
					<td align=right></td>
					<td align=right></td>
					<td align=right>".@number_format($stmaterialbiby[$noakunlima])."</td>
					<td align=right></td>
					<td align=right>".@number_format($stmaterialsdbiby[$noakunlima])."</td>
					<td align=right>".@number_format($sttransbihmkm[$noakunlima],2)."</td>
					<td align=right>".@number_format($strpbiby[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($sttranssdbihmkm[$noakunlima],2)."</td>
					<td align=right>".@number_format($strpsdbiby[$noakunlima]['trans'])."</td>
					<td align=right>".@number_format($strpbiby[$noakunlima]['lain'])."</td>
					<td align=right>".@number_format($strpsdbiby[$noakunlima]['lain'])."</td>
					<td align=right>".@number_format($strpbiby[$noakunlima]['total'])."</td>
					<td align=right>".@number_format($strpsdbiby[$noakunlima]['total'])."</td>
				";
			
			@$gtupahbihk+=$stupahbihk[$noakunlima];
			@$gtrpbiby['upah']+=$strpbiby[$noakunlima]['upah'];
			@$gtupahsdbifisik+=$stupahsdbifisik[$noakunlima];
			@$gtupahsdbihk+=$stupahsdbihk[$noakunlima];
			@$gtrpsdbiby['upah']+=$strpsdbiby[$noakunlima]['upah'];
			@$gtmaterialbiby+=$stmaterialbiby[$noakunlima];
			@$gtmaterialsdbiby+=$stmaterialsdbiby[$noakunlima];
			
			@$gtrpbiby['trans']+=$strpbiby[$noakunlima]['trans'];
			@$gtrpsdbiby['trans']+=$strpsdbiby[$noakunlima]['trans'];
			@$gttransbihmkm+=$sttransbihmkm[$noakunlima];
			@$gttranssdbihmkm+=$sttranssdbihmkm[$noakunlima];
			
			@$gtrpbiby['lain']+=$strpbiby[$noakunlima]['lain'];
			@$gtrpsdbiby['lain']+=$strpsdbiby[$noakunlima]['lain'];
			@$gtrpbiby['total']+=$strpbiby[$noakunlima]['total'];
			@$gtrpsdbiby['total']+=$strpsdbiby[$noakunlima]['total'];
				
	}
}
$stream.="
		<tr  bgcolor=#48D1CC>
			<td colspan=2>".$_SESSION['lang']['grnd_total']."</td>
			<td></td>
			<td align=right>".@number_format($gtupahbihk,2)."</td>
			<td align=right>".@number_format($gtrpbiby['upah'])."</td>
			<td align=right>".@number_format($gtupahsdbifisik,2)."</td>
			<td align=right>".@number_format($gtupahsdbihk,2)."</td>
			<td align=right>".@number_format($gtrpsdbiby['upah'])."</td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right>".@number_format($gtmaterialbiby)."</td>
			<td align=right></td>
			<td align=right>".@number_format($gtmaterialsdbiby)."</td>
			<td align=right>".@number_format($gttransbihmkm,2)."</td>
			<td align=right>".@number_format($gtrpbiby['trans'])."</td>
			<td align=right>".@number_format($gttranssdbihmkm,2)."</td>
			<td align=right>".@number_format($gtrpsdbiby['trans'])."</td>

			<td align=right>".@number_format($gtrpbiby['lain'])."</td>
			<td align=right>".@number_format($gtrpsdbiby['lain'])."</td>
			
			<td align=right>".@number_format($gtrpbiby['total'])."</td>
			<td align=right>".@number_format($gtrpsdbiby['total'])."</td>
		";

	
$stream.="
 </tbody>
     </table>";

switch ($method) {
######PREVIEW
    case 'html3':
		//echo $blok;
		//echo "<br>";
		echo"
			<button id=tomboldetail class=mybutton onclick=kehtml1()>Level 1</button> 
			<button id=tomboldetail class=mybutton onclick=kehtml2()>Level 2</button>
			<button id=tomboldetail class=mybutton disabled>Level 3</button>
		";
		
		echo"<br>";
		
		echo "
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 1</button>   
			<button id=tomboldetail class=mybutton disabled>" . $_SESSION['lang']['excel'] . " 2</button>   
			<button id=tomboldetail class=mybutton onclick=excel3(event,'".$blok."','".$per2."','".$tipe."')>" . $_SESSION['lang']['excel'] . " 3</button>  
		";
		
        echo "<br>";
		echo "<br>";
        echo $stream;
        break;

######EXCEL	
    case 'excel3':
      
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "BIAYA_DAN_PRODUKSI_PERBLOK_LV2_" . $blok;
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