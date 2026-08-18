<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;

// echo"<pre>";
// print_r($param);

// echo"</pre>";


	$str="select * from ".$dbname.".keu_5jenistagihan where status=1";
	$res=fetchData($str);
	foreach ($res as $bar) {
		$statusjurnal[$bar['kode']]=$bar['jurnal'];
	}

// echo"<pre>";
// print_r($param);
// echo"</pre>";

$jenisInvoice = $_POST['jnsInvoice'];
$optJenis=makeOption($dbname,'keu_5jenistagihan','kode,source');
// Get Akun Ppn
$qPpn = selectQuery($dbname,'setup_parameterappl','nilai',"kodeaplikasi='TX' and kodeparameter='PPNINV'");
$resPpn = fetchData($qPpn);
$akunPpn = '';
if(!empty($resPpn)) $akunPpn = $resPpn[0]['nilai'];
$noakunHtg="";
$optNmsupp=makeOption($dbname, 'log_5supplier','supplierid,namasupplier');
$dat="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
$dat.="<div style=overflow:auto;width:100%;height:310px;>";
#buat ambil periode hutang pph
if(substr($optJenis[$jenisInvoice],0,3)=='htg'){
	switch ($optJenis[$jenisInvoice]) {
		case 'htg22':
			$noakunHtg="2130201";
		break;
		case 'htg21':
			$noakunHtg="2130101";
		break;
		case 'htg23':
			$noakunHtg="2130301";
		break;
		case 'htg4':
			$noakunHtg="2130401";
		break;
		
	}
	if(($param['periodeHtgId']!='')&&($param['periodeHtgId2']!='')){
		if($param['periodeHtgId']>$param['periodeHtgId2']){
			exit('warning: Periode Awal Lebih Besar Perode Kedua');
		}
	}
	$optSupplier=$optPeriodeHtg2=$optPeriodeHtg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sPrdHtg="select distinct left(tanggal,7) as periode from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_tagihandt b on a.noinvoice=b.noinvoice 
	          where b.noakun='".$noakunHtg."' and a.unit='".$param['unit']."' order by left(tanggal,7) desc ";
	$rPrdHtg=fetchData($sPrdHtg);
	foreach ($rPrdHtg as $key => $val) {
		if($param['periodeHtgId']==$val['periode']){
			$optPeriodeHtg.="<option value='".$val['periode']."' selected>".$val['periode']."</option>";
		}else{
			$optPeriodeHtg.="<option value='".$val['periode']."'>".$val['periode']."</option>";	
		}
		if($param['periodeHtgId2']==$val['periode']){
			$optPeriodeHtg2.="<option value='".$val['periode']."' selected>".$val['periode']."</option>";
		}else{
			$optPeriodeHtg2.="<option value='".$val['periode']."'>".$val['periode']."</option>";
		}
		
	}
	$sPrdHtg="select a.supplierid,b.namasupplier from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid 
	          where a.tipe='PAJAK' ";
	$rPrdHtg=fetchData($sPrdHtg);
	foreach ($rPrdHtg as $key => $val){
		$optSupplier.="<option value='".$val['supplierid']."'>".$val['namasupplier']."</option>";
	}
	$dat.=$_SESSION['lang']['periode'] .": <select id=periodeHtgId onchange=ambilHtgPO()>".$optPeriodeHtg."</select> ".$_SESSION['lang']['sd']." <select id=periodeHtgId2 onchange=ambilHtgPO()>".$optPeriodeHtg2."</select>";
	$dat.=$_SESSION['lang']['namasupplier'] .": <select id=suppIdHtg>".$optSupplier."</select>";
}

$dat.="<table cellpadding=1 cellspacing=1 border=0 class='sortable'><thead>";
$dat.="<tr class='rowheader'><td>No.</td>";
$rPo['ppn']=0;

$where = '';

if(substr($optJenis[$jenisInvoice],0,2)=='sw'){
	$optJenis[$jenisInvoice]="sw";
}

if(substr($optJenis[$jenisInvoice],0,2)=='as'){
	$optJenis[$jenisInvoice]="as";
}

switch($optJenis[$jenisInvoice])
{
	
	case'sip':
		$dat.="<td align=center>".$_SESSION['lang']['nosip']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['NoKontrak']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['kg']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['rupiah']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['subtotal']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['ppn']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['pph']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['total']."</td>";
        $dat.="</tr></thead><tbody>"; 
		
		if($param['txtfind']!='') {
			$where.=" and nodo like '%".$param['txtfind']."%'";
		}
		
		if($param['tglcariinv1']!='' and $param['tglcariinv2']!='') {
			$where.=" and tanggaldo between '".tanggalsystemn($param['tglcariinv1'])."' and '".tanggalsystemn($param['tglcariinv2'])."' ";
		}
		
		if($param['tglcariinv1']!='' and $param['tglcariinv2']!='') {
			$where.=" and tanggaldo between '".tanggalsystemn($param['tglcariinv1'])."' and '".tanggalsystemn($param['tglcariinv2'])."' ";
		}
	
		$sPo="select * from ".$dbname.".pmn_suratperintahpengiriman where pt='".$param['kodeorg']."'  ".$where." ";
	break;
	
	case'spks':
	
	
	// print_r($param);exit("Error:A");
		// if($param['jeniscari']==''){
			// $param['jeniscari']='ETC';
		// }
		$dat.="<td align=center>".$_SESSION['lang']['nospk']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['jenis']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['NoKontrak']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['kg']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['rupiah']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['subtotal']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['ppn']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['pph']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['total']."</td>";
        $dat.="</tr></thead><tbody>"; 
		
		if($param['txtfind']!='') {
			$where.=" and nospk like '%".$param['txtfind']."%'";
		}
		
		if($param['tglcariinv1']!='' and $param['tglcariinv2']!='') {
			$where.=" and tanggal between '".tanggalsystemn($param['tglcariinv1'])."' and '".tanggalsystemn($param['tglcariinv2'])."' ";
		}
		
		if($param['tglcariinv1']!='' and $param['tglcariinv2']!='') {
			$where.=" and tanggal between '".tanggalsystemn($param['tglcariinv1'])."' and '".tanggalsystemn($param['tglcariinv2'])."' ";
		}
	
		$tablespks="pmn_spk_".$param['jeniscari'];
		$sPo="select * from ".$dbname.".".$tablespks." where kodept='".$param['kodeorg']."' ".$where." ";
		// echo $sPo;
		
	break;


	case'ffb':
	case'ffbe':
	case'ffba':
		$dat.="<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['unit']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['total']."</td>";
        // $dat.="<td align=center>".$_SESSION['lang']['pajak']."</td>";
		// $dat.="<td align=center>Check<br><input type=checkbox id=cekall onclick=cekalldata()></td>";
		$dat.="<td align=center>Check</td>";
        $dat.="</tr></thead><tbody>"; 
		
		if($param['txtfind']!='') {
			$where.=" and notransaksi like '%".$param['txtfind']."%'";
		}
		
		
		if($param['supplier']!='') {
			$where.=" and supplier like '%".$param['supplier']."%'";
		}
		
		
		if($param['tglcariinv1']!='' and $param['tglcariinv2']!='') {
			$where.=" and tanggal between '".tanggalsystemn($param['tglcariinv1'])."' and '".tanggalsystemn($param['tglcariinv2'])."' ";
		}
		
		
		$sPo="select * from ".$dbname.".kebun_tbskud limit 1";
	break;

	case'bfb':
		
		$dat.="<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['unit']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['total']." Bonus</td>";
        $dat.="<td align=center>".$_SESSION['lang']['pajak']."</td>";
        $dat.="</tr></thead><tbody>"; 
		
		if($param['txtfind']!='') {
			$where=" and notransaksi like '%".$param['txtfind']."%'";
		}
		$sPo="select * from ".$dbname.".keu_persediaantbs_vw where bonus_perkg!=0 limit 1";
	break;
	
	case'ram':
		$dat.="<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['unit']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['total']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['pajak']."</td>";
        $dat.="</tr></thead><tbody>"; 
		
		if($param['txtfind']!='') {
			$where=" and (concat(left(datein,10),'/',kodesupplier) like '%".$param['txtfind']."%')";
		}
		
		// $optHo = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$param['unit']."'");
		
		// $sPo="select (a.totalrupiah) as totalrupiah, (a.rupiahpajak) as rupiahpajakditanggung,a.notransaksi,
			// a.tanggal,a.unit,a.koderamp,b.kelompok 
			// from ".$dbname.".pmn_penerimaantbsramp_vw a 
			// left join ".$dbname.".log_5klsupplier b on a.koderamp=b.kode
			// where a.kodeorg='".$optHo[$param['unit']]."' ".$where." ";
		$sPo="select * 
			from ".$dbname.".pmn_penerimaantbsramp 
			limit 1";
	break;
	
	
	case'bykrm':
		$sortHold=" and right(nodok,3)='".$_SESSION['empl']['kodeorganisasi']."'";
        
		if($param['txtfind']!='')
        {
			$where=" and nodok like '%".$param['txtfind']."%'";
		}

		$sPo="select * from ".$dbname.".log_biayakirim where 1=1 ".$where." and posting=1 ".$sortHold."  order by updatetime desc ";
		
		$dat.="<td>".$_SESSION['lang']['nopo']."</td>";
        $dat.="<td>".$_SESSION['lang']['kodebarang']."</td>";
        $dat.="<td>".$_SESSION['lang']['namabarang']."</td>";
        $dat.="<td>Transportir</td>";
        $dat.="<td>".$_SESSION['lang']['jumlah']."</td></tr></thead><tbody>";
	break;
	
	case'rms':
		if($param['txtfind']!='')
		{
			$where=" and notransaksi like '%".$param['txtfind']."%'";
		}
		
		$sPo="select * from ".$dbname.".sdm_pengobatanht where kodeorg='".$_SESSION['org']['kodeorganisasi']."' ".$where."  and posting='1' order by tanggalkwitansi desc";
		
		// Table Header
        $dat.="<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['karyawan']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namars']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['jumlah']."</td></tr></thead><tbody>"; 
	break;
	
	case 'po':
		if($param['txtfind']!='')
		{
			$where=" and nopo like '%".$param['txtfind']."%'";
		}
		
		// $addlokal=" and lokalpusat=0 ";
  //       $addkdorg=" and kodeorg='".$param['kodeorg']."'";
  //       if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
  //       {
		// 	$addlokal=" and lokalpusat=1 ";
  //           $addkdorg="";
		// }
		
		// $sPo="select distinct nopo,((subtotal + pbbkb + addcost) - nilaidiskon) as nilaipo,ppn,kodesupplier,
		// stat_release,matauang,nilaidiskon,kurs from ".$dbname.".log_po_vw where 
		//       kodeorg='".$param['kodeorg']."' and stat_release=1 and statuspo=3
		// 	  ".$where.$addlokal."  order by tanggal desc";

		#= update pengambilan data hartot itu = harga rata * jumlah;
		#= seharusnya memakai hargasatuan * jumlah

		/*
		$sPo="select nopo,notransaksi,sum(hartot) as terima from ".$dbname.".log_transaksi_vw 
		where kodept='".$param['kodeorg']."' and left(kodegudang,4)='".$param['unit']."' ".$where." 
		and tipetransaksi=1 and post=1 and statussaldo=1 and statusjurnal=1 group by nopo,notransaksi order by tanggal desc";
		*/
		
		$sPo="select nopo,notransaksi,sum(hargasatuan*jumlah) as terima, idsupplier as kodesupplier,kodebarang from ".$dbname.".log_transaksi_vw 
		where kodept='".$param['kodeorg']."' and left(kodegudang,4)='".$param['unit']."' ".$where." 
		and tipetransaksi=1 and post=1 and statussaldo=1 and statusjurnal=1 
		and left(kodebarang,1) not in (8,9) group by nopo,notransaksi order by tanggal desc";

		// Table Header
        $dat.="<td align=center>".$_SESSION['lang']['nopo']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
        // $dat.="<td align=center>".$_SESSION['lang']['sisa']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['uangmuka']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilai']." PO</td>";
        $dat.="<td align=center>Sisa PO</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilai']." Penerimaan</td>";
        $dat.="<td align=center>Sisa Penerimaan</td>";
        $dat.="<td align=center>".$_SESSION['lang']['matauang']."</td></tr></thead><tbody>";  
	break;
	
	case 'sj':
		if($param['txtfind']!='')
        {
			$where="where nosj like '%".$param['txtfind']."%'";
		}
		
		$sPo="select distinct nosj as nopo,expeditor as kodesupplier,kodeorg from ".$dbname.". log_suratjalanht ".$where."  order by nosj desc";
		
		// Table Header
        $dat.="<td>".$_SESSION['lang']['nosj']."</td>";
        $dat.="<td>".$_SESSION['lang']['expeditor']."</td></tr></thead><tbody>";
	break;
	
	case 'ns':
		if($param['txtfind']!='')
        {
			$where="where nokonosemen like '%".$param['txtfind']."%'";
		}
		
		$sPo="select distinct nokonosemen as nopo,shipper as kodesupplier from ".$dbname.".log_konosemenht ".$where."  order by nokonosemen desc";
		
		// Table Header
        $dat.="<td>".$_SESSION['lang']['nokonosemen']."</td>";
        $dat.="<td>".$_SESSION['lang']['shipper']."</td></tr></thead><tbody>";
	break;

	/*case'kt':
		if($param['txtfind']!='')
        {
			$where=" and a.notransaksi like '%".$param['txtfind']."%'";
		}
		
		// if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
        // {
			// $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier,matauang from ".$dbname.".log_spkht where kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."')  ".$where."  order by tanggal desc";
		// }
		// else
		// {
			// $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier,matauang from ".$dbname.".log_spkht where  kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') ".$where."   order by tanggal desc";
		// }
		
		$sPo=" select a.notransaksi as nopo,b.kodeorg,sum(a.jumlahrealisasi) as nilaipo,b.koderekanan as kodesupplier,b.matauang 
				from ".$dbname.".log_baspk a left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi 
				where statusjurnal=1 and b.kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodeorg']."') 
				".$where." group by a.notransaksi order by a.tanggal desc ";
		// echo $sPo;
				
		$dat.="<td>".$_SESSION['lang']['kontrak']."</td>";
        $dat.="<td>".$_SESSION['lang']['kontraktor']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilai']." po</td>";
        $dat.="<td align=center>".$_SESSION['lang']['uangmuka']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['sisa']."</td>
        </tr></thead><tbody>";
	break;*/

	case'kt':
		if($param['txtfind']!='')
        {
			$where=" and a.notransaksi like '%".$param['txtfind']."%'";
		}
		
		// if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
        // {
			// $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier,matauang from ".$dbname.".log_spkht where kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."')  ".$where."  order by tanggal desc";
		// }
		// else
		// {
			// $sPo="select distinct notransaksi as nopo,kodeorg,nilaikontrak as nilaipo,koderekanan as kodesupplier,matauang from ".$dbname.".log_spkht where  kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') ".$where."   order by tanggal desc";
		// }
		
		$sPo=" select a.notransaksi as nopo,a.termin,b.kodeorg,sum(a.jumlahrealisasi) as nilaipo,b.koderekanan as kodesupplier,b.matauang 
				from ".$dbname.".log_baspk a left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi 
				where statusjurnal=1 and b.kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodeorg']."') 
				".$where." group by a.notransaksi,termin order by a.tanggal desc,a.notransaksi desc ";
		// echo $sPo;
				
		$dat.="<td>".$_SESSION['lang']['kontrak']."</td>";
        $dat.="<td>".$_SESSION['lang']['kontraktor']."</td>";
        $dat.="<td>".$_SESSION['lang']['termin']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilai']." po</td>";
        $dat.="<td align=center>".$_SESSION['lang']['uangmuka']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['sisa']."</td>
        </tr></thead><tbody>";
	break;

	case'rmhSakit':
		if($param['txtfind']!='')
		{
			$where=" and notransaksi like '%".$param['txtfind']."%'";
		}
		$sPo="select notransaksi as nopo,bebanperusahaan as nilaipo,rs as kodesupplier,karyawanid,ygsakit from ".$dbname.".sdm_pengobatanht 
		      where postingjurnal=1 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and char_length(kodeorganisasi)=4) ".$where."";
		$dat.="<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['karyawan']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namars']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['jumlah']."</td></tr></thead><tbody>"; 
	break;
	case'sw':
		if($param['txtfind']!='')
        {
			$where=" and notransaksi like '%".$param['txtfind']."%'";
		}
		
		$sPo="select notransaksi as nopo, harga_barang as nilaipo , noakun_kredit as noakun, supplierid as kodesupplier from ".$dbname.".keu_transaksi_rutin where tipe_transaksi='SEWA' and tipewaktu='TAHUNAN' and jenistipe='".$jenisInvoice."' ".$where." and notransaksi not in (select nopo from keu_tagihanht where left(tipeinvoice,2)='sw') and '".date('Y-m-d')."' between tanggalmulai and tanggalselesai order by notransaksi desc";
		// echo $sPo;
		//(harga_barang/tenor) as nilaipo
		// Table Header
        $dat.="<td>".$_SESSION['lang']['notransaksi']."</td>";
        $dat.="<td>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td>".$_SESSION['lang']['jumlah']."</td></tr></thead><tbody>"; 
	break;
	case 'as':
		if($param['txtfind']!='')
        {
			$where=" and notransaksi like '%".$param['txtfind']."%'";
		}
		
		$sPo="select notransaksi as nopo, harga_barang as nilaipo, supplierid as kodesupplier, noakun_kredit as noakun from ".$dbname.".keu_transaksi_rutin where tipe_transaksi='ASURANSI' and tipewaktu='TAHUNAN' and jenistipe='".$jenisInvoice."' ".$where."  and notransaksi not in (select nopo from keu_tagihanht where left(tipeinvoice,2)='as') and '".date('Y-m-d')."' between tanggalmulai and tanggalselesai order by notransaksi desc";
		//(harga_barang/tenor) as nilaipo
		// echo $sPo;
		// Table Header
        $dat.="<td>".$_SESSION['lang']['notransaksi']."</td>";
        $dat.="<td>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td>".$_SESSION['lang']['jumlah']."</td></tr></thead><tbody>";
	break;
	case'pojs':
		if($param['txtfind']!='')
		{
			$where=" and nopo like '%".$param['txtfind']."%'";
		}
		
		// $addlokal=" and lokalpusat=0 ";
  //       $addkdorg=" and kodeorg='".$param['kodeorg']."'";
  //       if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
  //       {
		// 	$addlokal=" and lokalpusat=1 ";
  //           $addkdorg="";
		// }
		
		$sPo="select distinct nopo,((subtotal + pbbkb + addcost+ppn-pph) - nilaidiskon) as nilaipo,ppn,kodesupplier,stat_release,matauang,nilaidiskon from ".$dbname.".log_po_vw 
		      where kodeorg='".$param['kodeorg']."' and stat_release=1 and left(kodebarang,1)='8' ".$where.$addlokal."  order by tanggal desc";
		// echo $sPo;
		/*
		
		*/
		// Table Header
        $dat.="<td align=center>".$_SESSION['lang']['nopo']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['sisa']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilai']." po</td>";
        $dat.="<td align=center>".$_SESSION['lang']['uangmuka']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['matauang']."</td></tr></thead><tbody>"; 
	break;
	case'poa':
		if($param['txtfind']!='')
		{
			$where=" and nopo like '%".$param['txtfind']."%'";
		}
		
		// $addlokal=" and lokalpusat=0 ";
  //       $addkdorg=" and kodeorg='".$_SESSION['org']['kodeorganisasi']."'";
  //       if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
  //       {
		// 	$addlokal=" and lokalpusat=1 ";
  //           $addkdorg="";
		// }
		
		$sPo="select distinct nopo,((subtotal + pbbkb + addcost+ppn-pph) - nilaidiskon) as nilaipo,ppn,kodesupplier,stat_release,matauang,nilaidiskon,kurs from ".$dbname.".log_po_vw 
		      where kodeorg='".$param['kodeorg']."' and stat_release=1 and left(kodebarang,1)='9' ".$where.$addlokal."  order by tanggal desc";
		
		// Table Header
        $dat.="<td align=center>".$_SESSION['lang']['nopo']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['sisa']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['nilai']." po</td>";
        $dat.="<td align=center>".$_SESSION['lang']['uangmuka']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['matauang']."</td></tr></thead><tbody>"; 
	break;
	case'getNodo':
		if($jenisInvoice=='tpk'){
			$kdBarang="40000002";//kernel
		}
		if($jenisInvoice=='tck'){
			$kdBarang="40000001";//cpo
		}
		if($jenisInvoice=='tbs'){
			$kdBarang="40000003";//cpo
		}
		#cek unitnya klo bukan tipe holding gak bisa
		$optTipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$param['unit']."'");
		$optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$param['unit']."'");
		if($optTipe[$param['unit']]!='HOLDING'){
			exit('warning: Unit Bertipe Holding Untuk Transaksi Ini');
		}
		if($param['txtfind']!=''){
			$where=" and a.nodo like '%".$param['txtfind']."%'";
		}
		$sJumlah="select noakun,sum(jumlah) as jumlah,noreferensi as nodo,kodesupplier from ".$dbname.".keu_jurnaldt 
		         where noakun in ('2110201') and kodeorg='".$param['unit']."' group by noreferensi,kodesupplier,noakun";
		//echo $sJumlah;
	    $rJumlah=fetchData($sJumlah);
	    foreach ($rJumlah as $key => $val) {
	    	$nilRup[$val['kodesupplier'].$val['nodo']]+=($val['jumlah']*(-1));
	    	if(substr($val['noakun'],0,3)=='212'){
	    		$nilRupPph[$val['kodesupplier'].$val['nodo']]+=($val['jumlah']*(-1));
	    	}
	    }
	    #data tagihan
	    $sJmlhDt="select sum(nilaiinvoice) as jumlah,nopo as nodo,kodesupplier from ".$dbname.".keu_tagihanht 
		          where unit='".$param['unit']."' and tipeinvoice='".$jenisInvoice."' and unit='".$param['unit']."' group by nopo,kodesupplier";
		//echo $sJmlhDt;
	    $rJmlhDt=fetchData($sJmlhDt);
	    foreach ($rJmlhDt as $key => $val) {
	    	$nilRupTag[$val['kodesupplier'].$val['nodo']]=$val['jumlah'];
	    }
		$sPo="select distinct a.nodo as nopo,c.supplierid as kodesupplier, a.subsidi, a.pphditanggung from ".$dbname.".pabrik_timbangan b 
		     left join ".$dbname.".pmn_suratperintahpengiriman a on a.nodo=b.nosipb 
		     left join ".$dbname.".log_5suptimbangan c on b.kodecustomer=c.kodetimbangan
		     where b.kodebarang='".$kdBarang."' and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$optPt[$param['unit']]."' and tipe='PABRIK') ".$where." ";
		if($jenisInvoice=='tbs'){
			$sPo="select distinct a.nodo as nopo,a.transportir as kodesupplier, a.subsidi, a.pphditanggung from ".$dbname.".pabrik_timbangan b 
		     left join ".$dbname.".pmn_suratperintahpengiriman a on a.nodo=b.nodo 
		     where b.kodebarang='".$kdBarang."' and millcode = 'EXTM' ".$where." ";
		}
		//echo $sPo;exit('warning');
		$dat.="<td align=center>".$_SESSION['lang']['nodo']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
        $dat.="<td align=center>".$_SESSION['lang']['rp']."</td>";
        $dat.="<td align=center>Pajak</td></tr>";
	break;
	case 'um':
		
		if ($param['jeniscari']=='p'){

			if($param['txtfind']!=''){
				$where=" and nopo like '%".$param['txtfind']."%'";
			}

			// $addlokal=" and lokalpusat=0 ";
	  //       $addkdorg=" and kodeorg='".$_SESSION['org']['kodeorganisasi']."'";
	  //       if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			// 	$addlokal=" and lokalpusat=1 ";
	  //           $addkdorg="";
			// }

			$sPo="select distinct nopo,ppn,kodesupplier,stat_release,matauang,nilaidiskon from ".$dbname.".log_po_vw where 
			      kodeorg='".$param['kodeorg']."' and stat_release=1 
				  ".$where." order by tanggal desc";

			// Table Header
        	$dat.="<td align=center>".$_SESSION['lang']['nopo']."</td>";
        	$dat.="<td align=center>".$_SESSION['lang']['supplier']."</td></tr></thead><tbody>";

		}else{

			if($param['txtfind']!='')
	        {
				$where=" and a.notransaksi like '%".$param['txtfind']."%'";
			}

			// $sPo=" select a.notransaksi as nopo,b.kodeorg,b.koderekanan as kodesupplier,b.matauang 
			// 	from ".$dbname.".log_baspk a left join ".$dbname.".log_spkht b on a.notransaksi=b.notransaksi 
			// 	where statusjurnal=1 and b.kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."') 
			// 	".$where." group by a.notransaksi order by a.tanggal desc ";
			$sPo=" select b.notransaksi as nopo,b.kodeorg,b.koderekanan as kodesupplier,b.matauang 
				from ".$dbname.".log_spkht b where b.kodeorg in (select distinct kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodeorg']."') 
				".$where." group by b.notransaksi order by b.tanggal desc ";

			// Table Header
			$dat.="<td>".$_SESSION['lang']['kontrak']."</td>";
        	$dat.="<td>".$_SESSION['lang']['kontraktor']."</td></tr></thead><tbody>";

		}
		 
	break;
	case'htg21':
	case'htg22':
	case'htg23':
	case'htg4':
		$dat.="<td align=center>".$_SESSION['lang']['namasupplier']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['noinvoice']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['noinvoicesupplier']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['nilai']."</td>";
		$dat.="<td align=center><input type=checkbox id=chkAll onclick=chkDtAll() checked /></td></tr></thead><tbody>";
		$whrPrd="";
		if(($param['periodeHtgId']!='')&&($param['periodeHtgId2']!='')){
			$whrPrd=" and left(b.tanggal,7) between '".$param['periodeHtgId']."' and '".$param['periodeHtgId2']."'" ;
		}

		$sPo="select a.*,b.* from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice 
		      where a.noakun='".$noakunHtg."' and b.unit='".$param['unit']."' ".$whrPrd." and (noinvoice_referensi is null or noinvoice_referensi='') order by kodesupplier,tanggal asc";
	break;
	
	/*
	case'ipk':
	case'sda':
	case'sp':
	case'spp':
	case'sub':
	case'sum':
	case'tkbm':
	
		$where='';
		if($param['txtfind']!=''){
			$where.=" and nospk like '%".$param['txtfind']."%'";
		}
		
		if($param['tglcariinv1']!='' and $param['tglcariinv2']=='') {
			exit("Warning:Lengkapi pengisian tanggal");
		}
		
		if($param['tglcariinv1']=='' and $param['tglcariinv2']!='') {
			exit("Warning:Lengkapi pengisian tanggal");
		}
		
		if($param['tglcariinv1']!='' and $param['tglcariinv2']!='') {
			$where.=" and tanggal between '".tanggalsystemn($param['tglcariinv1'])."' and '".tanggalsystemn($param['tglcariinv2'])."' ";
		}
	
		$dat.="<td align=center>".$_SESSION['lang']['nospk']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['NoKontrak']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['transportir']." / ".$_SESSION['lang']['surveyor']."</td>";
		$dat.="<td align=center>".$_SESSION['lang']['nilai']."</td>";
			
			$table='pmn_spk_'.$param['jnsInvoice'];
			$sPo = "select * from ".$dbname.".".$table." where kodept='".$param['kodeorg']."' and jenis='".$param['jnsInvoice']."' and posting=1 ".$where." ";
			// echo"<pre>";
			// print_r($str);
			// echo"</pre>";
			// exit("Error:".$str);
		// exit("Error:".$sPo);
	break;
	*/
	
	
	
	
	
	
	default:
		$optJenis=makeOption($dbname,'keu_5jenistagihan','kode,namajenis');
		exit('warning: Jenis Tagihan '.$optJenis[$jenisInvoice].', Belum Terdaftar Pada Script. Silakan Hubungi IT Dept');
	break;
}
if($jenisInvoice!=''){
	 $dat.="</thead><tbody>";
	$qPo=$owlPDO->query($sPo);
$qPo->setFetchMode(PDO::FETCH_ASSOC);
$no=0;
// echo $optJenis[$jenisInvoice];exit("Error:A");
while($rPo=$qPo->fetch()){
	// if(substr($optJenis[$jenisInvoice],0,2)=='po'){
	// 	$optJenis[$jenisInvoice]="po";
	// }
		switch ($optJenis[$jenisInvoice]) {
			
			case'sip':
				$sisaterima=1;
				$nilrupiah=0;
				$nilppn=0;
				$nilpph=0;
				$trupiah=0;
				if($sisaterima>0){
					@$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
					@$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
					// echo $opttipesup[$jenisInvoice];
					@$no+=1;
					$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nodo']."','";
					$dat.=isset($sisaterima)? number_format($sisaterima,2): 0;
					$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
					$dat.=isset($bar1['ppn'])? $bar1['ppn']: 0;
					$dat.="','".$rPo['transportir']."','".@$optNoAkun[$rPo['transportir']]."','','IDR','1','','','','')\" style='pointer:cursor;'>";
					
					// $dat.="<tr class='rowcontent' onclick=\"setPo('".$val['notransaksi']."','";
						// $dat.=isset($val['totalrupiah'])? number_format($val['totalrupiah'],2): 0;
						// $dat.="','".$param['jnsInvoice']."','".$val['rupiahpajakditanggung'];
						// $dat.="','".$val['koderamp']."','".$optNoAkun[$val['koderamp']]."','".substr($param['unit'],0,4)."','IDR','1')\" style='pointer:cursor;'>";
						
					
					
					$dat.="<td style=cursor:pointer align=center>".$no."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['nodo']."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['tanggaldo']."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['nokontrak']."</td>";
					$dat.="<td style=cursor:pointer>".$optNmsupp[$rPo['transportir']]."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($kg)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($rPo['rupiah'],2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($nilrupiah,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($nilppn,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($nilpph,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($trupiah,2)."</td>";
					$dat.="</tr>";
				}
			
			break;
			
			case'spks':
			
				$sisaterima=1;
				$tablespks="pmn_spk_".$param['jeniscari'];
				/*
				if($param['jeniscari']=='ipkd'){
					
					$persenppn='10';	
					$persenpph='2';
					
					#= ambil data timbangan total
					$str="select sum(beratbersih) as kg,nosipb from ".$dbname.".pabrik_timbangan_vw where nosipb='".$rPo['nospk']."'";
					$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$kg=$bar['kg'];
						$nilrupiah=$kg*$rPo['rupiah'];
						$nilppn=$nilrupiah*$persenppn/100;
						$nilpph=$nilrupiah*$persenpph/100;
						$trupiah=$nilrupiah+$nilppn-$nilpph;
						
						#= cek nilai tagihan
						$str="select sum(nilai) as nilai  from ".$dbname.".keu_tagihandt 
							where noinvoice in (select noinvoice from ".$dbname.".keu_tagihanht where nopo='".$rPo['nospk']."')";
						$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar=$res->fetch();
						$tinvoice=$bar['nilai'];
				
					$sisaterima=$trupiah-$tinvoice;
						
				} else  if($param['jeniscari']=='ipk'){
					
					$persenppn='1.2';	
					$persenpph='2';
					
					#= ambil data timbangan total
					$str="select sum(beratbersih) as kg,nosipb from ".$dbname.".pabrik_timbangan_vw where nosipb='".$rPo['nospk']."'";
					$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$kg=$bar['kg'];
						$nilrupiah=$kg*$rPo['rupiah'];
						$nilppn=$nilrupiah*$persenppn/100;
						$nilpph=$nilrupiah*$persenpph/100;
						$trupiah=$nilrupiah+$nilppn-$nilpph;
						
				} else{
					$nilrupiah=0;
					$nilppn=0;
					$nilpph=0;
					$trupiah=0;
				}*/
				
				$nilrupiah=0;
				$nilppn=0;
				$nilpph=0;
				$trupiah=0;
				if($sisaterima>0){
					@$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
					@$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
					// echo $opttipesup[$jenisInvoice];
					@$no+=1;
					$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nospk']."','";
					$dat.=isset($sisaterima)? number_format($sisaterima,2): 0;
					$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
					$dat.=isset($bar1['ppn'])? $bar1['ppn']: 0;
					$dat.="','".$rPo['transportir']."','".@$optNoAkun[$rPo['transportir']]."','','IDR','1')\" style='pointer:cursor;'>";
					
					// $dat.="<tr class='rowcontent' onclick=\"setPo('".$val['notransaksi']."','";
						// $dat.=isset($val['totalrupiah'])? number_format($val['totalrupiah'],2): 0;
						// $dat.="','".$param['jnsInvoice']."','".$val['rupiahpajakditanggung'];
						// $dat.="','".$val['koderamp']."','".$optNoAkun[$val['koderamp']]."','".substr($param['unit'],0,4)."','IDR','1')\" style='pointer:cursor;'>";
						
					
					
					$dat.="<td style=cursor:pointer align=center>".$no."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['nospk']."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['jenis']."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['tanggal']."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['nokontrak']."</td>";
					$dat.="<td style=cursor:pointer>".$optNmsupp[$rPo['transportir']]."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($kg)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($rPo['rupiah'],2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($nilrupiah,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($nilppn,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($nilpph,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($trupiah,2)."</td>";
					$dat.="</tr>";
				}

			break;
			
			
			case 'ffba':
				$arrList = array();
				$strH = "select kgnetto as total_terima,rpkg as harga_perkg,totalrp as totalrupiah,notransaksi,tanggal,unit as kodeho,supplier as kodesupplier 
				from ".$dbname.".kebun_tbsafiliasi where posting=1 and rounit='".$param['unit']."' ".$where." ";
			
				$resH=$owlPDO->query($strH);
				$resH->setFetchMode(PDO::FETCH_ASSOC);
				while($barH = $resH->fetch()){
					$rupiahpajakditanggung = 0;
					$rpgross = $barH['totalrupiah'];
					if($barH['beban_pajak']=='1'){
						$rpgross = ($barH['totalrupiah'] * (100 / (100-$barH['persenpajak'])));
						$rupiahpajakditanggung = ($rpgross * $barH['persenpajak']) / 100;
					}
					$listTransaksi[$barH['notransaksi']]=$barH['notransaksi'];
					$arrList[$barH['notransaksi']]['notransaksi'] = $barH['notransaksi'];
					$arrList[$barH['notransaksi']]['tanggal'] = $barH['tanggal'];
					$arrList[$barH['notransaksi']]['kodeho'] = $barH['kodeho'];
					$arrList[$barH['notransaksi']]['kodesupplier'] = $barH['kodesupplier'];
					$arrList[$barH['notransaksi']]['supplier'] = $optNmsupp[$barH['kodesupplier']];
					$arrList[$barH['notransaksi']]['totalrupiah'] += $rpgross;
					$arrList[$barH['notransaksi']]['rupiahpajakditanggung'] += $rupiahpajakditanggung;
				}

				if(isset($listTransaksi))
					foreach($listTransaksi as $key){
						$val=$arrList[$key];
						$sCek=$owlPDO->query("select sum(nilai) as jmlhinvoice,noinvoice from ".$dbname.".keu_tagihandt where notransaksi='".$val['notransaksi']."' order by noinvoice");
						// $a="select sum(nilai) as jmlhinvoice,noinvoice from ".$dbname.".keu_tagihandt where notransaksi='".$val['notransaksi']."' order by noinvoice";
						// echo $a;exit();
						$sCek->setFetchMode(PDO::FETCH_ASSOC);
						$rCek=  $sCek->fetch();
							$val['totalrupiah']=$val['totalrupiah']-$rCek['jmlhinvoice'];
			
						$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
						$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$val['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
						if($val['totalrupiah']>1){
							$no+=1;
							$dat.="<tr class='rowcontent' style='pointer:cursor;' id=row".$no."><td>".$no."</td>";
							$dat.="<td id=notransaksi".$no.">".$val['notransaksi']."</td>";  
							$dat.="<td>".$val['tanggal']."</td>";
							$dat.="<td>".$val['kodeho']."</td>";
							$dat.="<td>".$optNmsupp[$val['kodesupplier']]."</td>";
							$dat.="<td align=right id=nilai".$no.">".number_format($val['totalrupiah'],2)."</td>";
							// $dat.="<td align=right>".number_format($val['rupiahpajakditanggung'])."</td>";
							$dat.="<td align=right><input type=checkbox id=cekdata".$no."></td>";
							$dat.="</tr>";
						}
					}
					$dat.="<button class=mybutton onclick=addDataTable(".$no.");>".$_SESSION['lang']['proses']."</button>";
			break;
			case 'ffbe':
				$arrList = array();
				$strH = "select kgnetto as total_terima,rpkg as harga_perkg,totalrp as totalrupiah,notransaksi,tanggal,unit as kodeho,supplier as kodesupplier 
				from ".$dbname.".kebun_tbsexternal where posting=1 and unit='".$param['unit']."' ".$where." ";
			
				$resH=$owlPDO->query($strH);
				$resH->setFetchMode(PDO::FETCH_ASSOC);
				while($barH = $resH->fetch()){
					$rupiahpajakditanggung = 0;
					$rpgross = $barH['totalrupiah'];
					if($barH['beban_pajak']=='1'){
						$rpgross = ($barH['totalrupiah'] * (100 / (100-$barH['persenpajak'])));
						$rupiahpajakditanggung = ($rpgross * $barH['persenpajak']) / 100;
					}
					$listTransaksi[$barH['notransaksi']]=$barH['notransaksi'];
					$arrList[$barH['notransaksi']]['notransaksi'] = $barH['notransaksi'];
					$arrList[$barH['notransaksi']]['tanggal'] = $barH['tanggal'];
					$arrList[$barH['notransaksi']]['kodeho'] = $barH['kodeho'];
					$arrList[$barH['notransaksi']]['kodesupplier'] = $barH['kodesupplier'];
					$arrList[$barH['notransaksi']]['supplier'] = $optNmsupp[$barH['kodesupplier']];
					$arrList[$barH['notransaksi']]['totalrupiah'] += $rpgross;
					$arrList[$barH['notransaksi']]['rupiahpajakditanggung'] += $rupiahpajakditanggung;
				}

				if(isset($listTransaksi))
					foreach($listTransaksi as $key){
						$val=$arrList[$key];
						$sCek=$owlPDO->query("select sum(nilai) as jmlhinvoice,noinvoice from ".$dbname.".keu_tagihandt where notransaksi='".$val['notransaksi']."' order by noinvoice");
						// $a="select sum(nilai) as jmlhinvoice,noinvoice from ".$dbname.".keu_tagihandt where notransaksi='".$val['notransaksi']."' order by noinvoice";
						// echo $a;exit();
						$sCek->setFetchMode(PDO::FETCH_ASSOC);
						$rCek=  $sCek->fetch();
							$val['totalrupiah']=$val['totalrupiah']-$rCek['jmlhinvoice'];
			
						$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
						$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$val['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
						if($val['totalrupiah']>1){
							$no+=1;
							$dat.="<tr class='rowcontent' style='pointer:cursor;' id=row".$no."><td>".$no."</td>";
							$dat.="<td id=notransaksi".$no.">".$val['notransaksi']."</td>";  
							$dat.="<td>".$val['tanggal']."</td>";
							$dat.="<td>".$val['kodeho']."</td>";
							$dat.="<td>".$optNmsupp[$val['kodesupplier']]."</td>";
							$dat.="<td align=right id=nilai".$no.">".number_format($val['totalrupiah'],2)."</td>";
							// $dat.="<td align=right>".number_format($val['rupiahpajakditanggung'])."</td>";
							$dat.="<td align=right><input type=checkbox id=cekdata".$no."></td>";
							$dat.="</tr>";
						}
					}
					$dat.="<button class=mybutton onclick=addDataTable(".$no.");>".$_SESSION['lang']['proses']."</button>";
			break;
			case 'ffb':
				$arrList = array();
				$strH = "select kgnetto as total_terima,rpkg as harga_perkg,totalrp as totalrupiah,notransaksi,tanggal,unit as kodeho,supplier as kodesupplier 
				from ".$dbname.".kebun_tbskud where posting=1 and (unit='".$param['unit']."' or pemilik='".$param['unit']."') ".$where." ";
				$resH=$owlPDO->query($strH);
				$resH->setFetchMode(PDO::FETCH_ASSOC);
				while($barH = $resH->fetch()){
					$rupiahpajakditanggung = 0;
					$rpgross = $barH['totalrupiah'];
					if($barH['beban_pajak']=='1'){
						$rpgross = ($barH['totalrupiah'] * (100 / (100-$barH['persenpajak'])));
						$rupiahpajakditanggung = ($rpgross * $barH['persenpajak']) / 100;
					}
					$listTransaksi[$barH['notransaksi']]=$barH['notransaksi'];
					$arrList[$barH['notransaksi']]['notransaksi'] = $barH['notransaksi'];
					$arrList[$barH['notransaksi']]['tanggal'] = $barH['tanggal'];
					$arrList[$barH['notransaksi']]['kodeho'] = $barH['kodeho'];
					$arrList[$barH['notransaksi']]['kodesupplier'] = $barH['kodesupplier'];
					$arrList[$barH['notransaksi']]['supplier'] = $optNmsupp[$barH['kodesupplier']];
					$arrList[$barH['notransaksi']]['totalrupiah'] += $rpgross;
					$arrList[$barH['notransaksi']]['rupiahpajakditanggung'] += $rupiahpajakditanggung;
				}

				if(isset($listTransaksi))
					foreach($listTransaksi as $key){
						$val=$arrList[$key];
						$sCek=$owlPDO->query("select sum(nilai) as jmlhinvoice,noinvoice from ".$dbname.".keu_tagihandt where notransaksi='".$val['notransaksi']."' order by noinvoice");
						// $a="select sum(nilai) as jmlhinvoice,noinvoice from ".$dbname.".keu_tagihandt where notransaksi='".$val['notransaksi']."' order by noinvoice";
						// echo $a;exit();
						$sCek->setFetchMode(PDO::FETCH_ASSOC);
						$rCek=  $sCek->fetch();
							$val['totalrupiah']=$val['totalrupiah']-$rCek['jmlhinvoice'];
			
						$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
						$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$val['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
						if($val['totalrupiah']>1){
							$no+=1;
							$dat.="<tr class='rowcontent' style='pointer:cursor;' id=row".$no."><td>".$no."</td>";
							$dat.="<td id=notransaksi".$no.">".$val['notransaksi']."</td>";  
							$dat.="<td>".$val['tanggal']."</td>";
							$dat.="<td>".$val['kodeho']."</td>";
							$dat.="<td>".$optNmsupp[$val['kodesupplier']]."</td>";
							$dat.="<td align=right id=nilai".$no.">".number_format($val['totalrupiah'],2)."</td>";
							// $dat.="<td align=right>".number_format($val['rupiahpajakditanggung'])."</td>";
							$dat.="<td align=right><input type=checkbox id=cekdata".$no."></td>";
							$dat.="</tr>";
						}
					}
					$dat.="<button class=mybutton onclick=addDataTable(".$no.");>".$_SESSION['lang']['proses']."</button>";
			break;
			case 'bfb':
				$arrList = array();
				$strH = "select total_terima,bonus_perkg,beban_pajak,persenpajak,totalrupiahbonus,rupiahpajakditanggung,
				notransaksi,tanggal,kodeho,kodesupplier 
				from ".$dbname.".keu_persediaantbs_vw where kodeunit='".$param['unit']."' ".$where." ";
				$resH=$owlPDO->query($strH);
				$resH->setFetchMode(PDO::FETCH_ASSOC);
				while($barH = $resH->fetch()){
					$rupiahpajakditanggung = 0;
					$rpgross = $barH['totalrupiahbonus'];
					if($barH['beban_pajak']=='1'){
						$rpgross = ($barH['totalrupiahbonus'] * (100 / (100-$barH['persenpajak'])));
						$rupiahpajakditanggung = ($rpgross * $barH['persenpajak']) / 100;
					}
					$listTransaksi[$barH['notransaksi']]=$barH['notransaksi'];
					$arrList[$barH['notransaksi']]['notransaksi'] = $barH['notransaksi'];
					$arrList[$barH['notransaksi']]['tanggal'] = $barH['tanggal'];
					$arrList[$barH['notransaksi']]['kodeho'] = $barH['kodeho'];
					$arrList[$barH['notransaksi']]['kodesupplier'] = $barH['kodesupplier'];
					$arrList[$barH['notransaksi']]['supplier'] = $optNmsupp[$barH['kodesupplier']];
					$arrList[$barH['notransaksi']]['totalrupiah'] += $rpgross;
					$arrList[$barH['notransaksi']]['rupiahpajakditanggung'] += $rupiahpajakditanggung;
				}

				if(isset($listTransaksi))
					foreach($listTransaksi as $key){
						$val=$arrList[$key];
						$sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$val['notransaksi']."' and tipeinvoice='bfb' order by noinvoice");
						$sCek->setFetchMode(PDO::FETCH_ASSOC);
						$rCek=  $sCek->fetch();
						#cek apakah terdaftar dilist PPN
						$sPPh="select * from ".$dbname.".log_5pphsup where supplierid='".$val['kodesupplier']."' and noakun='1171101' and status='1'";
						$rPPh=fetchData($sPPh);
						$val['totalrupiahberppn']=$val['totalrupiah'];
						if(count($rPPh)!=0){
							#jika belum pernah ada invoice gak usah dikali 10%
							#jika sudah ada maka nilainya ditambahin
							if($rCek['jmlhinvoice']!=0){
								$val['totalrupiahberppn']=($val['totalrupiah']*$rPPh[0]['tarif']/100)+$val['totalrupiah'];		
							}
						}
						
						if($rCek['jmlhinvoice']!=''){
							$val['totalrupiah']=($val['totalrupiahberppn']-$val['rupiahpajakditanggung'])-$rCek['jmlhinvoice'];
						}
						if($val['totalrupiah']<=0){
							break;
						}else{
							$val['totalrupiah']=$val['totalrupiahberppn']-$rCek['jmlhinvoice'];
						}

						$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
						$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$val['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
					
						$no+=1;
						$dat.="<tr class='rowcontent' onclick=\"setPo('".$val['notransaksi']."','";
						$dat.=isset($val['totalrupiah'])? number_format($val['totalrupiah'],2): 0;
						$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','".$val['rupiahpajakditanggung'];
						$dat.="','".$val['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".substr($val['kodeho'],0,4)."','IDR','1')\" style='pointer:cursor;'><td>".$no."</td>";
						$dat.="<td>".$val['notransaksi']."</td>";  
						$dat.="<td>".$val['tanggal']."</td>";
						$dat.="<td>".$val['kodeho']."</td>";
						$dat.="<td>".$optNmsupp[$val['kodesupplier']]."</td>";
						$dat.="<td align=right>".number_format($val['totalrupiah'],2)."</td>";
						$dat.="<td align=right>".number_format($val['rupiahpajakditanggung'])."</td></tr>";
					}
			break;
			case'ram':
				$optHo = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$param['unit']."'");
			
				$arrList = array();
				$strH = "select concat(left(datein,10),'/',kodesupplier) as notransaksi, left(datein,10) as tanggal, kodesupplier as koderamp, beratmasuk, beratkeluar, potongan, harga, beban_pajak, persenpajak from ".$dbname.".pmn_penerimaantbsramp where kodeorg='".$optHo[$param['unit']]."' ".$where." order by concat(left(datein,10),'/',kodesupplier) ASC";
				//echo $strH;
				$resH=$owlPDO->query($strH);
				$resH->setFetchMode(PDO::FETCH_ASSOC);
				while($barH = $resH->fetch())
				{
					$optSp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$barH['kodesupplier']."'");
					$potongan = round($barH['potongan']);
					// $potongan = ($barH['beratmasuk'] - $barH['beratkeluar'] - $barH['potongan']);
					// $dataAp=explode(".",$potongan);
					// if((intval($dataAp[1])>=1)&&(intval($dataAp[1])<=5)){
			        	// $dtNetto=floor($potongan);
			      	// }else{
			        	// $dtNetto=round($potongan,0);
			      	// }
					// $jumlah =  $dtNetto * $barH['harga'];
					$jumlah = ($barH['beratmasuk'] - $barH['beratkeluar'] - $potongan) * $barH['harga'];
					$rupiahpajakditanggung = 0;
					$rpgross = $jumlah;
					if($barH['beban_pajak']=='1')
					{
						$rpgross = ($jumlah * (100 / (100-$barH['persenpajak'])));
						$rupiahpajakditanggung = ($rpgross * $barH['persenpajak']) / 100;
					}


						
			
					$arrList[$barH['notransaksi']]['notransaksi'] = $barH['notransaksi'];
					$arrList[$barH['notransaksi']]['tanggal'] = $barH['tanggal'];
					$arrList[$barH['notransaksi']]['koderamp'] = $barH['koderamp'];
					$arrList[$barH['notransaksi']]['supplier'] = $optSp[$barH['koderamp']];
					$arrList[$barH['notransaksi']]['totalrupiah'] += $rpgross;
					$arrList[$barH['notransaksi']]['rupiahpajakditanggung'] += $rupiahpajakditanggung;
				}
				
				if(isset($arrList))
					foreach($arrList as $key=>$val)
					{
						$sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$val['notransaksi']."' and tipeinvoice='ram' order by noinvoice");
						$sCek->setFetchMode(PDO::FETCH_ASSOC);
						$rCek=  $sCek->fetch();
						if($rCek['jmlhinvoice']!=''){
							$val['totalrupiah']=($val['totalrupiah']-$val['rupiahpajakditanggung'])-$rCek['jmlhinvoice'];
						}
						if($val['totalrupiah']<=0){
							break;
						}

						$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
						$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$val['koderamp']."' and tipe='".$opttipesup[$jenisInvoice]."'");

						$no+=1;
						$dat.="<tr class='rowcontent' onclick=\"setPo('".$val['notransaksi']."','";
						$dat.=isset($val['totalrupiah'])? number_format($val['totalrupiah'],2): 0;
						$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','".$val['rupiahpajakditanggung'];
						$dat.="','".$val['koderamp']."','".$optNoAkun[$val['koderamp']]."','".substr($param['unit'],0,4)."','IDR','1')\" style='pointer:cursor;'>";
						$dat.="<td>".$no."</td>";
						$dat.="<td>".$val['notransaksi']."</td>";
						$dat.="<td>".tanggalnormal($val['tanggal'])."</td>";
						$dat.="<td>".$param['unit']."</td>";
						$dat.="<td>".$val['supplier']."</td>";
						$dat.="<td style='text-align:right'>".number_format(@$val['totalrupiah'])."</td>";
						$dat.="<td style='text-align:right'>".number_format(@$val['rupiahpajakditanggung'])."</td>";
						$dat.="</tr>";
						// $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['notransaksi']."','";
						// $dat.=isset($rPo['totalrupiah'])? number_format($rPo['totalrupiah'],2): 0;
						// $dat.="','".$param['jnsInvoice']."','".$rPo['rupiahpajakditanggung'];
						// $dat.="','".$rPo['koderamp']."','','".substr($param['unit'],0,4)."','IDR','1')\" style='pointer:cursor;'><td>".$no."</td>";
						// $dat.="<td>".$rPo['notransaksi']."</td>";  
						// $dat.="<td>".$rPo['tanggal']."</td>";
						// $dat.="<td>".$param['unit']."</td>";
						// $dat.="<td>".$rPo['kelompok']."</td>";
						// $dat.="<td>".number_format($rPo['totalrupiah'])."</td>";
						// $dat.="<td>".number_format($rPo['rupiahpajakditanggung'])."</td></tr>";
					}
		
			
			// $sPo="select (a.totalrupiah) as totalrupiah, (a.rupiahpajak) as rupiahpajakditanggung,a.notransaksi,
				// a.tanggal,a.unit,a.koderamp,b.kelompok 
				// from ".$dbname.".pmn_penerimaantbsramp_vw a 
				// left join ".$dbname.".log_5klsupplier b on a.koderamp=b.kode
				// where a.kodeorg='".$optHo[$param['unit']]."' ".$where." ";
			
			// $sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['notransaksi']."' and tipeinvoice='ram' order by noinvoice");
			// $sCek->setFetchMode(PDO::FETCH_ASSOC);
			// $rCek=  $sCek->fetch();
			// if($rCek['jmlhinvoice']!=''){
				// $rPo['totalrupiah']=$rPo['totalrupiah']-$rCek['jmlhinvoice'];
			// }
			
			
			// $no+=1;
			// $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['notransaksi']."','";
			// $dat.=isset($rPo['totalrupiah'])? number_format($rPo['totalrupiah'],2): 0;
			// $dat.="','".$param['jnsInvoice']."','".$rPo['rupiahpajakditanggung'];
			// $dat.="','".$rPo['koderamp']."','','".substr($param['unit'],0,4)."','IDR','1')\" style='pointer:cursor;'><td>".$no."</td>";
			// $dat.="<td>".$rPo['notransaksi']."</td>";  
			// $dat.="<td>".$rPo['tanggal']."</td>";
			// $dat.="<td>".$param['unit']."</td>";
			// $dat.="<td>".$rPo['kelompok']."</td>";
			// $dat.="<td>".number_format($rPo['totalrupiah'])."</td>";
			// $dat.="<td>".number_format($rPo['rupiahpajakditanggung'])."</td></tr>";
			break;
			case'bykrm':
				#cek sudah pernah ada inv apa belum
		        $sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nodok']."' and tipeinvoice='b' order by noinvoice");
				$sCek->setFetchMode(PDO::FETCH_ASSOC);
				$rCek=  $sCek->fetch();
				if($rCek['jmlhinvoice']!=''){
					$rPo['jumlah']=$rPo['jumlah']-$rCek['jmlhinvoice'];
				}
				
				$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrg);
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodetrp']."'");
				if($rPo['jumlah']>0)
				{
					$whbrg="kodebarang='".$rPo['kodebarang']."'";
		            $no+=1;
		            $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nodok']."','";
		            $dat.=isset($rPo['jumlah'])? number_format($rPo['jumlah'],2): 0;
		            $dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
		            $dat.="','".$rPo['kodetrp']."','".$optNoAkun[$rPo['kodetrp']]."','".substr($rPo['kodegudang'],0,4)."','IDR','1')\" style='pointer:cursor;'><td>".$no."</td>";
		            $dat.="<td>".$rPo['nodok']."</td>";  
		            $dat.="<td>".$rPo['kodebarang']."</td>";
		            $dat.="<td>".$nmBrg[$rPo['kodebarang']]."</td>";
		            $dat.="<td>".$optNmsupp[$rPo['kodetrp']]."</td>";
		            $dat.="<td>".number_format($rPo['jumlah'])."</td></tr>";
				}
			break;

			case'po':
				// if($rPo['nilaidiskon']=='')
				// {
				// 	$rPo['nilaidiskon']=0;
				// }
				
				// $nilPo=($rPo['nilaipo']-$rPo['nilaidiskon']);
		  		// $rPo['nilaipo']=$nilPo;

				#ambil data PO
				$str1="select distinct nopo,((subtotal + pbbkb + addcost) - nilaidiskon) as nilaipo,ppn,kodesupplier,stat_release,matauang,
							nilaidiskon,kurs,catatan from ".$dbname.".log_po_vw where nopo='".$rPo['nopo']."'";
				$res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $bar1=$res1->fetch();

                #jumlah per no.GR
                $sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' and 
		        	notransaksi_gr='".$rPo['notransaksi']."' order by noinvoice");
				$sCek->setFetchMode(PDO::FETCH_ASSOC);
		        $rCek= $sCek->fetch();
		        $sisaterima=0;
		        $sisaterima=$rPo['terima']-$rCek['jmlhinvoice'];
				
                #jumlah per nopo
                $sCek1=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoicenopo,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' order by noinvoice");
				$sCek1->setFetchMode(PDO::FETCH_ASSOC);
		        $rCek1= $sCek1->fetch();
		        $sisapo=0;
		        $sisapo=$bar1['nilaipo']-$rCek1['jmlhinvoicenopo'];

				// $sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' and 
				// notransaksi_gr in (select notransaksi from ".$dbname.".log_transaksiht where nopo='".$rPo['nopo']."') order by noinvoice");
				// $sCek->setFetchMode(PDO::FETCH_ASSOC);
		  		// $rCek=  $sCek->fetch();


				/*
		        #Uang Muka PO
		        $dtRupUm=0;
				$lstInv=array();
				$lstInvUm=array();
				
				$sC="select noinvoice,sum(nilaiinvoice) as jmlhum from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='um' and posting=1 group by noinvoice";
				$tC=$owlPDO->query($sC) or die(print " Gagal: ".PDOException::getMessage());
		        $tC->setFetchMode(PDO::FETCH_ASSOC);
		        while($rC = $tC->fetch()){
					$dtRupUm+=$rC['jmlhum'];
					$lstInv[$rC['noinvoice']]=$rC['noinvoice'];
				}

				if(!empty($lstInv)){
					foreach ($lstInv as $key => $val) {
						$sdetInvUm="select sum(nilai) as nil from ".$dbname.".keu_tagihandt where noinvoice='".$val."' and noakun not like '118%'";
						$rdetInvUm=fetchData($sdetInvUm);
						$dtRupUm+=$rdetInvUm[0]['nil'];
					}
				}
				*/
				
				#= uang muka baru ambil dari keu_kasbank
				$dtRupUm=0;
				$strum="select jumlah from ".$dbname.".keu_jurnaldt_vw where nodok='".$rPo['nopo']."' and noakun like '11803%'";
				// echo $strum;
          		$resum=$owlPDO->query($strum)or die(print " Gagal: ".PDOException::getMessage());
          		$resum->setFetchMode(PDO::FETCH_ASSOC);
          		while ($barum=$resum->fetch()) {
					$dtRupUm+=$barum['jumlah'];
				}
				
				
				// exit("Error:".$param['tanggal']);
				// exit("Error:".$param['tanggalinvoice']);
				#= diganti tanggal invoice
				#= ambil data tanggal terima terakhir
				$tglterima=$kdbrgterima='';
				$strtgl="select tanggal,kodebarang from ".$dbname.".log_transaksi_vw 
				where kodept='".$param['kodeorg']."' and left(kodegudang,4)='".$param['unit']."' ".$where." 
				and tipetransaksi=1 and post=1 and statussaldo=1 and statusjurnal=1 
				and left(kodebarang,1) not in (8,9) and tanggal <= '".tanggalsystemn($param['tanggalinvoice'])."' 
				and nopo='".$rPo['nopo']."' order by tanggal desc limit 1";
				// echo $strtgl;
				$restgl=$owlPDO->query($strtgl)or die(print " Gagal: ".PDOException::getMessage());
          		$restgl->setFetchMode(PDO::FETCH_ASSOC);
          		$bartgl=$restgl->fetch();
					$tglterima=$bartgl['tanggal'];
					$kdbrgterima=$bartgl['kodebarang'];
					
					// echo "<br>";
					
				#= query nama barang	
				$whbrgterima="kodebarang='".$kdbrgterima."'";
				$nmbrgterima=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrgterima);
		        // $terima=array();
		        // $str1="select nopo,notransaksi,sum(hartot) as terima from ".$dbname.".log_transaksi_vw where tipetransaksi=1 and nopo='".$rPo['nopo']."' group by nopo,notransaksi";
          		// $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
          		// $res1->setFetchMode(PDO::FETCH_ASSOC);
          		// while ($bar1=$res1->fetch()) {
          		// @$terima[$bar1['nopo']][$bar1['notransaksi']]=$bar1['terima']/$rPo['kurs'];
          		// // @$terima=$bar1['terima']/$rPo['kurs'];
          		// }
				
				//echo $rCek['jmlhinvoice']."__".$rCek['jmlppn']."___".$rPo['nilaipo']."___".$rPo['nilaipo']."___".$rPo['nilaidiskon'];
				// Get Kurs from Setup Mata Uang
		        
		        #kurs
		        $qKurs = selectQuery($dbname,'setup_matauangrate','*',"daritanggal<='".tanggalsystem($param['tanggalinvoice'])."' and kode='".$bar1['matauang']."'","daritanggal desc, jam desc",false,1,1);
				$resKurs = fetchData($qKurs);
		        $kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."'");
				
				if($sisaterima>0){
					$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
					$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
					// echo $opttipesup[$jenisInvoice];
					@$no+=1;
					$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
					$dat.=isset($sisaterima)? number_format($sisaterima,2): 0;
					$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
					$dat.=isset($bar1['ppn'])? $bar1['ppn']: 0;
					$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','','".
					$bar1['matauang']."','".$bar1['kurs']."','".$rPo['notransaksi']."','','".tanggalnormal($tglterima)."','".$nmbrgterima[$kdbrgterima]."')\" style='pointer:cursor;'>";
					$dat.="<td style=cursor:pointer align=center>".$no."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['nopo']."</td>";
					$dat.="<td style=cursor:pointer>".$rPo['notransaksi']."</td>";

					$sisa=$bar1['nilaipo']-$dtRupUm;

					// $dat.="<td style=cursor:pointer align=right>".number_format($sisa,2)."</td>";
					$dat.="<td style=cursor:pointer>".$optNmsupp[$rPo['kodesupplier']]."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($dtRupUm,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($bar1['nilaipo'],2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($sisapo,2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($rPo['terima'],2)."</td>";
					$dat.="<td style=cursor:pointer align=right>".number_format($sisaterima,2)."</td>";
					$dat.="<td style=cursor:pointer align=center>".$bar1['matauang']."</td></tr>";
				}

			break;

			case'pojs':
					// if($rPo['nilaidiskon']=='')
				// {
				// 	$rPo['nilaidiskon']=0;
				// }
				
				// $nilPo=($rPo['nilaipo']-$rPo['nilaidiskon']);
		  //       $rPo['nilaipo']=$nilPo;
		        $sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' order by noinvoice");
				$sCek->setFetchMode(PDO::FETCH_ASSOC);
		        $rCek=  $sCek->fetch();

		        $dtRupUm=0;
				$lstInv=array();
				$lstInvUm=array();
				$sC="select noinvoice,sum(nilaiinvoice) as jmlhum from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='um' and posting=1 group by noinvoice";
				$tC=$owlPDO->query($sC) or die(print " Gagal: ".PDOException::getMessage());
		        $tC->setFetchMode(PDO::FETCH_ASSOC);
		        while($rC = $tC->fetch()){
					$dtRupUm+=$rC['jmlhum'];
					$lstInv[$rC['noinvoice']]=$rC['noinvoice'];
				}
				if(!empty($lstInv)){
					foreach ($lstInv as $key => $val) {
						$sdetInvUm="select sum(nilai) as nil from ".$dbname.".keu_tagihandt where noinvoice='".$val."' and noakun not like '118%'";
						$rdetInvUm=fetchData($sdetInvUm);
						$dtRupUm+=$rdetInvUm[0]['nil'];
					}
				}

		        if($optJenis[$jenisInvoice]=='po'){
		        	$sPpn="select sum(nilai) as jmlppn,noinvoice from ".$dbname.".keu_tagihandt where noinvoice in (select noinvoice from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."') and noakun='1160100' order by noinvoice";
		        	//echo $sPpn;
					$rPpn=fetchdata($sPpn);
					$rCek['jmlppn']=$rPpn[0]['jmlppn'];
		        }
		        $totalPO=$rPo['nilaipo'];
				if($rCek['jmlhinvoice']!=''){
					$rPo['nilaipo']=($rPo['nilaipo']-($rCek['jmlhinvoice']+$rCek['jmlppn']))-$dtRupUm;
		            //$rPo['ppn']=$rPo['ppn']-$rCek['jmlppn'];
				}
				//echo $rCek['jmlhinvoice']."__".$rCek['jmlppn']."___".$rPo['nilaipo']."___".$rPo['nilaipo']."___".$rPo['nilaidiskon'];
				// Get Kurs from Setup Mata Uang
		        $qKurs = selectQuery($dbname,'setup_matauangrate','*',"daritanggal<='".tanggalsystem($param['tanggal'])."' and kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
				$resKurs = fetchData($qKurs);
		        $kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];
				$optNoAkun = makeOption($dbname,'log_5klsupplier','kode,noakun',"kode='".substr($rPo['kodesupplier'],0,4)."'");
				
				
				#pengecekan barang sudah diterima gudang atau belum
				$str="select count(*) as jumtrans from ".$dbname.".log_transaksiht where nopo='".$rPo['nopo']."' and post=1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				// $dat.=isset($sisaterima)? number_format($sisaterima,2): 0;
				
				if($rPo['nilaipo']>0){
					if($rPo['stat_release']==1){
						//if($bar['jumtrans']>0){
							@$no+=1;
							$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
							$dat.=isset($rPo['nilaipo'])? number_format($rPo['nilaipo'],2): 0;
							$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
							$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
							$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[substr($rPo['kodesupplier'],0,4)]."','','".
							$rPo['matauang']."','".$kurs."','','','','','')\" style='pointer:cursor;'>";
							$dat.="<td style=cursor:pointer align=center>".$no."</td>";
							$dat.="<td style=cursor:pointer>".$rPo['nopo']."</td>";

							$sisa=$rPo['nilaipo']-$dtRupUm;

							$dat.="<td style=cursor:pointer align=right>".number_format($sisa,2)."</td>";
							$dat.="<td style=cursor:pointer>".$optNmsupp[$rPo['kodesupplier']]."</td>";
							$dat.="<td style=cursor:pointer align=right>".number_format($totalPO,2)."</td>";
							$dat.="<td style=cursor:pointer align=right>".number_format($dtRupUm,2)."</td>";
							$dat.="<td style=cursor:pointer align=center>".$rPo['matauang']."</td></tr>";
						//}
					}
				}
			break;

			case'poa':
				// if($rPo['nilaidiskon']=='')
				// {
				// 	$rPo['nilaidiskon']=0;
				// }
				$scekgr="select notransaksi from ".$dbname.".log_transaksi_vw where nopo='".$rPo['nopo']."'";
				
				$rcekgr=fetchData($scekgr);

				// $nilPo=($rPo['nilaipo']-$rPo['nilaidiskon']);
		  		//       $rPo['nilaipo']=$nilPo;
		        $sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,sum(nilaippn) as jmlppn,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' order by noinvoice");
				$sCek->setFetchMode(PDO::FETCH_ASSOC);
		        $rCek=  $sCek->fetch();

		        $dtRupUm=0;
				$lstInv=array();
				$lstInvUm=array();
				$sC="select noinvoice,sum(nilaiinvoice) as jmlhum from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='um' and posting=1 group by noinvoice";
				$tC=$owlPDO->query($sC) or die(print " Gagal: ".PDOException::getMessage());
		        $tC->setFetchMode(PDO::FETCH_ASSOC);
		        while($rC = $tC->fetch()){
					$dtRupUm+=$rC['jmlhum'];
					$lstInv[$rC['noinvoice']]=$rC['noinvoice'];
				}
				if(!empty($lstInv)){
					foreach ($lstInv as $key => $val) {
						$sdetInvUm="select sum(nilai) as nil from ".$dbname.".keu_tagihandt where noinvoice='".$val."' and noakun not like '118%'";
						$rdetInvUm=fetchData($sdetInvUm);
						$dtRupUm+=$rdetInvUm[0]['nil'];
					}
				}

		      
		        $totalPO=$rPo['nilaipo'];
				if($rCek['jmlhinvoice']!=''){
					$rPo['nilaipo']=($rPo['nilaipo']-($rCek['jmlhinvoice']+$rCek['jmlppn']))-$dtRupUm;
		            //$rPo['ppn']=$rPo['ppn']-$rCek['jmlppn'];
				}
				//echo $rCek['jmlhinvoice']."__".$rCek['jmlppn']."___".$rPo['nilaipo']."___".$rPo['nilaipo']."___".$rPo['nilaidiskon'];
				// Get Kurs from Setup Mata Uang
		        $qKurs = selectQuery($dbname,'setup_matauangrate','*',"daritanggal<='".tanggalsystem($param['tanggal'])."' and kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
				$resKurs = fetchData($qKurs);
		        $kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];
				$optNoAkun = makeOption($dbname,'log_5klsupplier','kode,noakun',"kode='".substr($rPo['kodesupplier'],0,4)."'");
				
				
				
				
				
				
				
				#= ambil data tanggal terima terakhir
				$tglterima=$kdbrgterima='';
				$strtgl="select tanggal,kodebarang from ".$dbname.".log_transaksi_vw 
				where kodept='".$param['kodeorg']."' and left(kodegudang,4)='".$param['unit']."' ".$where." 
				and tipetransaksi=1 and post=1 and statussaldo=1 and statusjurnal=1 
				and left(kodebarang,1) in (9) and tanggal <= '".tanggalsystemn($param['tanggal'])."' 
				and nopo='".$rPo['nopo']."' order by tanggal desc limit 1";
				// echo $strtgl;
				$restgl=$owlPDO->query($strtgl)or die(print " Gagal: ".PDOException::getMessage());
          		$restgl->setFetchMode(PDO::FETCH_ASSOC);
          		$bartgl=$restgl->fetch();
					$tglterima=$bartgl['tanggal'];
					$kdbrgterima=$bartgl['kodebarang'];
					
					// echo "<br>";
					
				#= query nama barang	
				$whbrgterima="kodebarang='".$kdbrgterima."'";
				$nmbrgterima=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrgterima);
				
				
				#pengecekan barang sudah diterima gudang atau belum
				$str="select count(*) as jumtrans from ".$dbname.".log_transaksiht where nopo='".$rPo['nopo']."' and post=1 ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				// if (count($rcekgr)>0) {
					if($rPo['nilaipo']>0){
						if($rPo['stat_release']==1){
							//if($bar['jumtrans']>0){
				
								
								
								@$no+=1;
								$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
								$dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
								$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
								$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
								$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[substr($rPo['kodesupplier'],0,4)]."','','".
								$rPo['matauang']."','".$kurs."','".$rPo['notransaksi']."','','".tanggalnormal($tglterima)."','".$nmbrgterima[$kdbrgterima]."')\" style='pointer:cursor;'>";
								$dat.="<td style=cursor:pointer align=center>".$no."</td>";
								$dat.="<td style=cursor:pointer>".$rPo['nopo']."</td>";

								$sisa=$rPo['nilaipo']-$dtRupUm;

								$dat.="<td style=cursor:pointer align=right>".number_format($sisa,2)."</td>";
								$dat.="<td style=cursor:pointer>".$optNmsupp[$rPo['kodesupplier']]."</td>";
								$dat.="<td style=cursor:pointer align=right>".number_format($totalPO,2)."</td>";
								$dat.="<td style=cursor:pointer align=right>".number_format($dtRupUm,2)."</td>";
								$dat.="<td style=cursor:pointer align=center>".$rPo['matauang']."</td></tr>";
							//}
						}
					}
				// }
				
			break;

			case'sj':
				// $optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."'");
				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");

				$no+=1;
		        $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
		        $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
		        $dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
		        $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
		        $dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$rPo['kodeorg']."','IDR',1)\" style='pointer:cursor;'><td>".$no."</td>";
		        $dat.="<td>".$rPo['nopo']."</td>";
		        $dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td></tr>";
			break;
			case'rmhSakit':
				$no+=1;
				if($rPo['ygsakit']==0){
					$karyId=$rPo['karyawanid'];
					$optNmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$rPo['karyawanid']."'");
				}else{
					$karyId=$rPo['ygsakit'];
					$optNmKar=makeOption($dbname,'sdm_karyawankeluarga','nomor,nama',"nomor='".$rPo['ygsakit']."'");
				}

				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");

				$optLksiTgs=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$rPo['karyawanid']."'");
		        $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
		        $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
		        $dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
		        $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
		        $dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$optLksiTgs[$rPo['karyawanid']]."','IDR',1)\" style='pointer:cursor;'><td>".$no."</td>";
		        $dat.="<td>".$rPo['nopo']."</td>";
		        $dat.="<td>".$optNmKar[$karyId]."</td>
		               <td>".$optNmsupp[$rPo['kodesupplier']]."</td>
		               <td>".number_format($rPo['nilaipo'])."</td>
		        </tr>";
			break;

			/*case'kt':
				$notransaksi = $rPo['nopo'];
				// Get Tax
				// $optTax = makeOption($dbname,'log_spk_tax','noakun,nilai',"notransaksi='".$notransaksi."' and kodeorg='".$rPo['kodeorg']."'");
				// // Nilai Invoice ditambahkan dengan Ppn dan dikurangi PPh
				// foreach($optTax as $noakun=>$nilai) {
				// 	if($akunPpn==$noakun){
				// 		$rPo['nilaipo'] += $nilai;
				// 	}
				// 	else{
				// 		$rPo['nilaipo'] -= $nilai;
				// 	}
				// }

				$sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' order by noinvoice");
				$sCek->setFetchMode(PDO::FETCH_ASSOC);
		        $rCek=  $sCek->fetch();

				$dtRupUm=0;
				$lstInv=array();
				$lstInvUm=array();
				$sC="select noinvoice,sum(nilaiinvoice) as jmlhum from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='um' and posting=1 group by noinvoice";
				$tC=$owlPDO->query($sC) or die(print " Gagal: ".PDOException::getMessage());
		        $tC->setFetchMode(PDO::FETCH_ASSOC);
		        while($rC = $tC->fetch()){
					$dtRupUm+=$rC['jmlhum'];
					$lstInv[$rC['noinvoice']]=$rC['noinvoice'];
				}
				if(!empty($lstInv)){
					foreach ($lstInv as $key => $val) {
						$sdetInvUm="select sum(nilai) as nil from ".$dbname.".keu_tagihandt where noinvoice='".$val."' and noakun not like '118%'";
						$rdetInvUm=fetchData($sdetInvUm);
						$dtRupUm+=$rdetInvUm[0]['nil'];
					}
				}

				$sisa=$rPo['nilaipo']-$dtRupUm-floatval($rCek['jmlhinvoice']);

				// $optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."'");
				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
				$qKurs = selectQuery($dbname,'setup_matauangrate','*',"daritanggal<='".tanggalsystem($param['tanggal'])."' and kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
				$resKurs = fetchData($qKurs);
		        $kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];

				$no+=1;
				$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
				$dat.=isset($sisa)? $sisa: 0;
				$dat.="','".$param['jnsInvoice']."','";
				$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
				$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$rPo['kodeorg']."','".$rPo['matauang']."','".$kurs."')\" style='pointer:cursor;'><td>".$no."</td>";
				$dat.="<td>".$rPo['nopo']."</td>";
				$dat.="<td>".$optNmsupp[$rPo['kodesupplier']]." kontrak</td>";
				$dat.="<td>".$rPo['nilaipo']."</td>";
				$dat.="<td>".$dtRupUm."</td>";
				$dat.="<td>".$sisa."</td></tr>";
			break;*/

			case'kt':
				$notransaksi = $rPo['nopo'];
				// Get Tax
				// $optTax = makeOption($dbname,'log_spk_tax','noakun,nilai',"notransaksi='".$notransaksi."' and kodeorg='".$rPo['kodeorg']."'");
				// // Nilai Invoice ditambahkan dengan Ppn dan dikurangi PPh
				// foreach($optTax as $noakun=>$nilai) {
				// 	if($akunPpn==$noakun){
				// 		$rPo['nilaipo'] += $nilai;
				// 	}
				// 	else{
				// 		$rPo['nilaipo'] -= $nilai;
				// 	}
				// }
				$sCek=$owlPDO->query("select tanggal,termin from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' order by noinvoice");
				$sCek->setFetchMode(PDO::FETCH_ASSOC);
		        $rCek=  $sCek->fetch();
		        $cektanggal=$rCek['tanggal'];
		        $cektermin=$rCek['termin'];

		        $whrter='';
		        if ($cektanggal>'2019-04-04' || $cektermin!='') {
		        	$whrter=" and termin='".$rPo['termin']."' ";
		        }

				$sCek=$owlPDO->query("select sum(nilaiinvoice) as jmlhinvoice,noinvoice,updateby from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='".$jenisInvoice."' ".$whrter." order by noinvoice");
				$sCek->setFetchMode(PDO::FETCH_ASSOC);
		        $rCek=  $sCek->fetch();

				$dtRupUm=0;
				$lstInv=array();
				$lstInvUm=array();
				$sC="select noinvoice,sum(nilaiinvoice) as jmlhum from ".$dbname.".keu_tagihanht where nopo='".$rPo['nopo']."' and tipeinvoice='um' and posting=1 group by noinvoice";
				$tC=$owlPDO->query($sC) or die(print " Gagal: ".PDOException::getMessage());
		        $tC->setFetchMode(PDO::FETCH_ASSOC);
		        while($rC = $tC->fetch()){
					$dtRupUm+=$rC['jmlhum'];
					$lstInv[$rC['noinvoice']]=$rC['noinvoice'];
				}
				if(!empty($lstInv)){
					foreach ($lstInv as $key => $val) {
						$sdetInvUm="select sum(nilai) as nil from ".$dbname.".keu_tagihandt where noinvoice='".$val."' and noakun not like '118%'";
						$rdetInvUm=fetchData($sdetInvUm);
						$dtRupUm+=$rdetInvUm[0]['nil'];
					}
				}

				$sisa=$rPo['nilaipo']-$dtRupUm-floatval($rCek['jmlhinvoice']);

				// $optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."'");
				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
				$qKurs = selectQuery($dbname,'setup_matauangrate','*',"daritanggal<='".tanggalsystem($param['tanggal'])."' and kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
				$resKurs = fetchData($qKurs);
		        $kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];

		        if ($sisa>0) {
			        $no+=1;
					$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
					$dat.=isset($sisa)? $sisa: 0;
					$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
					$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
					$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$rPo['kodeorg']."','".$rPo['matauang']."','".$kurs."','','".$rPo['termin']."')\" style='pointer:cursor;'><td>".$no."</td>";
					$dat.="<td>".$rPo['nopo']."</td>";
					$dat.="<td>".$optNmsupp[$rPo['kodesupplier']]." kontrak</td>";
					$dat.="<td>".$rPo['termin']."</td>";
					$dat.="<td>".$rPo['nilaipo']."</td>";
					$dat.="<td>".$dtRupUm."</td>";
					$dat.="<td>".$sisa."</td></tr>";
		        }
				
			break;

			case'getNodo':
				$rPo['nilaipo']=$nilRup[$rPo['kodesupplier'].$rPo['nopo']];
				$pph = 0;
				$nilaigross = $rPo['nilaipo'];
				if($rPo['pphditanggung']=='1'){
					$nilaigross = ($rPo['nilaipo'] * (100 / (100-$rPo['subsidi'])));
					$pph = ($nilaigross * $rPo['subsidi']) / 100;
				}
				
				$rPo['nilaipo'] = $nilaigross;					
				$rPo['matauang']="IDR";
				$val['totalrupiah']=$rPo['nilaipo']-$nilRupTag[$rPo['kodesupplier'].$rPo['nopo']];
				if($val['totalrupiah']==0){
					break;
				}
				if($rPo['nilaipo']==0){
					break;
				}

				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				// $optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");
				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");

				$no+=1;
				$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
				$dat.=isset($rPo['nilaipo'])? number_format($rPo['nilaipo'],2): 0;
				$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
				$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
				$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$rPo['kodeorg']."','".$rPo['matauang']."')\" style='pointer:cursor;'><td>".$no."</td>";
				$dat.="<td>".$rPo['nopo']."</td>";
				$dat.="<td>".$optNmsupp[$rPo['kodesupplier']]."</td>";
				$dat.="<td align=right>".number_format($rPo['nilaipo'],2)."</td>";
				$dat.="<td align=right>".number_format($pph,2)."</td></tr>";
			break;
			case'um':

				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optXsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rPo['kodesupplier']."'");

				if($param['jeniscari']=='p'){

		        	$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe in (select tipe from ".$dbname.".log_5klsupplier where kelompok='SUPPLIER')");
					$qKurs = selectQuery($dbname,'setup_matauangrate','*',"daritanggal<='".tanggalsystem($param['tanggal'])."' and kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
					$resKurs = fetchData($qKurs);
			        $kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];
					
					if($rPo['stat_release']==1){
						@$no+=1;
						$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
						$dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
						$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
						$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
						$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','','".
						$rPo['matauang']."','".$kurs."')\" style='pointer:cursor;'>";
						$dat.="<td style=cursor:pointer align=center>".$no."</td>";
						$dat.="<td style=cursor:pointer>".$rPo['nopo']."</td>";
						$dat.="<td style=cursor:pointer>".$optXsup[$rPo['kodesupplier']]."</td></tr>";
					}
				}else{
					// $optNoAkun = makeOption($dbname,'log_5klsupplier','kode,noakun',"kode='".substr($rPo['kodesupplier'],0,4)."'");
					
					$qKurs = selectQuery($dbname,'setup_matauangrate','*',"daritanggal<='".tanggalsystem($param['tanggal'])."' and kode='".$rPo['matauang']."'","daritanggal desc, jam desc",false,1,1);
					$resKurs = fetchData($qKurs);
			        $kurs = empty($resKurs)? 1: $resKurs[0]['kurs'];

					$no+=1;
					$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='KONTRAKTOR'");
					$dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
					$dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
					$dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
					$dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
					$dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$rPo['kodeorg']."','".$rPo['matauang']."','".$kurs."')\" style='pointer:cursor;'><td>".$no."</td>";
					$dat.="<td>".$rPo['nopo']."</td>";
					$dat.="<td>".$optNmsupp[$rPo['kodesupplier']]." kontrak</td></tr>";
				}
				
			break;
			case'sw':

				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optXsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rPo['kodesupplier']."'");
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");

				$no+=1;
		        $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
		        $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
		        $dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
		        $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
		        $dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$rPo['kodeorg']."','IDR',1)\" style='pointer:cursor;'><td>".$no."</td>";
		        $dat.="<td>".$rPo['nopo']."</td>";
		        $dat.="<td>".$optXsup[$rPo['kodesupplier']]."</td>";
		        $dat.="<td>".$rPo['nilaipo']."</td></tr>";
			break;
			case'as':

				$opttipesup=makeOption($dbname,'keu_5jenistagihan','kode,tipesupplier',"kode='".$jenisInvoice."'");
				$optXsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
				$optNoAkun = makeOption($dbname,'log_5supkelompok','supplierid,noakun',"supplierid='".$rPo['kodesupplier']."' and tipe='".$opttipesup[$jenisInvoice]."'");

				$no+=1;
		        $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nopo']."','";
		        $dat.=isset($rPo['nilaipo'])? $rPo['nilaipo']: 0;
		        $dat.="','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','";
		        $dat.=isset($rPo['ppn'])? $rPo['ppn']: 0;
		        $dat.="','".$rPo['kodesupplier']."','".$optNoAkun[$rPo['kodesupplier']]."','".$rPo['kodeorg']."','IDR',1)\" style='pointer:cursor;'><td>".$no."</td>";
		        $dat.="<td>".$rPo['nopo']."</td>";
		        $dat.="<td>".$optXsup[$rPo['kodesupplier']]."</td>";
		        $dat.="<td>".$rPo['nilaipo']."</td></tr>";
			break;
			
			
			// $dat.="<td align=center>".$_SESSION['lang']['nospk']."</td>";
			// $dat.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
			// $dat.="<td align=center>".$_SESSION['lang']['nokontrak']."</td>";
			// $dat.="<td align=center>".$_SESSION['lang']['transportir']." / ".$_SESSION['lang']['surveyor']."</td>";
			// $dat.="<td align=center>".$_SESSION['lang']['nilai']."</td>";
			
			case'ipk':
			case'spp':
				#= cek apakah sudah ada transaksi / belum
				#= perbandingan dari detail yang kepala 8, dengan 
				$str = "select sum(nilai) as nilai from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
					where tipeinvoice='".$param['jnsInvoice']."' and nopo='".$rPo['nospk']."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$sisa=$rPo['rupiah']-$bar['nilai'];
				
				if($sisa>0){
					@$no+=1;
					$optXsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rPo['transportir']."'");
					 $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nospk']."','".$rPo['rupiah']."','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','0','".$rPo['transportir']."','".$rPo['kodept']."','IDR','1')\" style='pointer:cursor;'>";				
						$dat.="<td>".$no."</td>";
						$dat.="<td>".$rPo['nospk']."</td>";
						$dat.="<td>".$rPo['tanggal']."</td>";
						$dat.="<td>".$rPo['nokontrak']."</td>";
						$dat.="<td>".$optXsup[$rPo['transportir']]."</td>";
						$dat.="<td>".$rPo['rupiah']."</td>";
					$dat.="</tr>";
				}
			
			break;
			
			
			
			
			
			case'sda':
			case'sp':
			case'sub':
			case'sum':
			
				#= cek apakah sudah ada transaksi / belum
				#= perbandingan dari detail yang kepala 8, dengan 
				$str = "select sum(nilai) as nilai from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
					where tipeinvoice='".$param['jnsInvoice']."' and nopo='".$rPo['nospk']."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$sisa=$rPo['rupiah']-$bar['nilai'];
				
				if($sisa>0){
				@$no+=1;
					$optXsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rPo['surveyor']."'");
					 $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nospk']."','".$sisa."','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','0','".$rPo['surveyor']."','".$rPo['kodept']."','IDR','1')\" style='pointer:cursor;'>";				
						$dat.="<td>".$no."</td>";
						$dat.="<td>".$rPo['nospk']."</td>";
						$dat.="<td>".$rPo['tanggal']."</td>";
						$dat.="<td>".$rPo['nokontrak']."</td>";
						$dat.="<td>".$optXsup[$rPo['surveyor']]."</td>";
						$dat.="<td>".$sisa."</td>";
					$dat.="</tr>";
				}
			
			break;
			
			
			
			
			case'tkbm':
				#= cek apakah sudah ada transaksi / belum
				#= perbandingan dari detail yang kepala 8, dengan 
				$str = "select sum(nilai) as nilai from ".$dbname.".keu_tagihandt a left join ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice
					where tipeinvoice='".$param['jnsInvoice']."' and nopo='".$rPo['nospk']."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar = $res->fetch();
				$sisa=$rPo['rupiah']-$bar['nilai'];
				
				if($sisa>0){
					@$no+=1;
					$optXsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rPo['bongkarmuat']."'");
					 $dat.="<tr class='rowcontent' onclick=\"setPo('".$rPo['nospk']."','".$rPo['rupiah']."','".$param['jnsInvoice']."','".$statusjurnal[$param['jnsInvoice']]."','0','".$rPo['bongkarmuat']."','".$rPo['kodept']."','IDR','1')\" style='pointer:cursor;'>";				
						$dat.="<td>".$no."</td>";
						$dat.="<td>".$rPo['nospk']."</td>";
						$dat.="<td>".$rPo['tanggal']."</td>";
						$dat.="<td>".$rPo['nokontrak']."</td>";
						$dat.="<td>".$optXsup[$rPo['bongkarmuat']]."</td>";
						$dat.="<td>".$rPo['rupiah']."</td>";
					$dat.="</tr>";
				}
				
			break;
			
			
			case'htg22':
			case'htg21':
			case'htg23':
			case'htg4':
			$optXsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rPo['kodesupplier']."'");
			$sCek="select * from ".$dbname.".keu_tagihandt where noinvoice_referensi='".$rPo['noinvoice']."'";

			$rCek=fetchData($sCek);
			if(count($rCek)==0){
				$sKasBank="select a.* from ".$dbname.".keu_kasbankdt a left join ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi 
				           where keterangan1='".$rPo['noinvoice']."' and left(a.noakun,3)='213' and b.posting=1";
				$rKasBank=fetchData($sKasBank);
				if(count($rKasBank)==0){
					break;
				}
				$no+=1;
		        $dat.="<tr class='rowcontent'>
		               <td>".$no."</td>";
		        $dat.="<td>".$optXsup[$rPo['kodesupplier']]."<input type=hidden id=suppId_".$no." value='".$rPo['kodesupplier']."' /></td>";
		        $dat.="<td>".$rPo['noinvoice']." <input type=hidden value='".$rPo['noinvoice']."' id=noinv_".$no." /></td>";
		        $dat.="<td>".$rPo['noinvoicesupplier']."</td>";
		        $dat.="<td>".tanggalnormal($rPo['tanggal'])."</td>";
		        $dat.="<td align=right>".number_format($rPo['nilai']*(-1),2)."<input type=hidden id=nilaiId_".$no." value='".$rPo['nilai']*(-1)."' /></td>";
		        $dat.="<td align=center><input type=checkbox id=pph22_".$no." checked /></td>";
		        $dat.="</tr>";
			}
				
			break;
			default:
				# code...
			break;
		}
	}
	if(substr($optJenis[$jenisInvoice],0,3)=='htg'){
		$dat.="<tr><td colspan=5><input type=hidden id=noakundetail value='".$noakunHtg."' /><input type=hidden id=totRowPPh value='".$no."' /></td>
			           <td colspan=2><button class=mybutton onclick=addToDetail(".$no.")>".$_SESSION['lang']['addtodetail']."</button></td></tr>";	
	}
	$dat.="</tbody></table></div></fieldset>";
	echo $dat;
}
?>