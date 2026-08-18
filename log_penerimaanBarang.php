<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/log_transaksi.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
if(isTransactionPeriod())//check if transaction period is normal
{
	OPEN_BOX('','<span class=judul>'.getMenu('log_penerimaanBarang').'</span><br>');
	$frm[0]='';
	$frm[1]='';
	echo "<fieldset><legend>";
	echo" <b>".$_SESSION['lang']['periode'].": <span id=displayperiod>".tanggalnormal($_SESSION['org']['period']['start'])." - ".tanggalnormal($_SESSION['org']['period']['end'])."</span></b>";
	echo"</legend>";

	#kodeorganisasi untuk klinik harus berakhiran PK
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
		// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG' and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') and namaorganisasi not like '%NON AKTIF%' order by namaorganisasi";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."') and tipe='GUDANG' and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') and namaorganisasi not like '%NON AKTIF%' order by namaorganisasi";
	}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL' and substr($_SESSION['empl']['subbagian'],-2)!='PK'){
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG' and left(induk,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') and namaorganisasi not like '%NON AKTIF%' order by namaorganisasi";
	}else{
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (left(induk,4)='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."') and tipe = 'GUDANG' and namaorganisasi not like '%NON AKTIF%' order by namaorganisasi";
	}
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$optsloc="<option value=''></option>";
	while($bar=$res->fetch())
	{
		$optsloc.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	}
	
	$optpersetujuan1 = $optpersetujuan2 = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

	$str="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a join  ".$dbname.".setup_approval b where a.karyawanid = b.karyawanid and b.level = '1' and b.jenispersetujuan like 'GR' order by namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$optpersetujuan1.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." (".$bar->nik.")</option>";
	}

	// $str="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' and kodejabatan in ('77',116) order by namakaryawan asc";
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $res->setFetchMode(PDO::FETCH_OBJ);
	// while($bar=$res->fetch())
	// {
	// 	$optpersetujuan1.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." (".$bar->nik.")</option>";
	// }
	
	$str="select a.karyawanid,a.namakaryawan,a.nik from ".$dbname.".datakaryawan a join  ".$dbname.".setup_approval b where a.karyawanid = b.karyawanid and b.level = '2' and b.jenispersetujuan like 'GR' order by namakaryawan asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		$optpersetujuan2.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." (".$bar->nik.")</option>";
	}

	echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['daftargudang']."</legend>
		".$_SESSION['lang']['pilihgudang'].": <select id=sloc>".$optsloc."</select>
		<button onclick=setSloc('simpan') class=mybutton id=btnsloc>".$_SESSION['lang']['save']."</button>
		<button onclick=setSloc('ganti') class=mybutton>".$_SESSION['lang']['cancel']."</button>
	</fieldset>";

  // $strPoSolar = "select distinct(kodebarang) from ksp.log_podt where nopo='".$nopo."'";
  // $barPoSolar = fetchData($strPoSolar);

  // if($jns == 'PO') {
  // 	if($barPoSolar['kodebarang'] == '351010003') {
  // 		exit("Warning : No Surat Jalan");
  // 	}
  // }

  // exit("warning".print_r($barPoSolar));


//   joki (get surat jalan)
	## GET UNIT
	$optunit='';
	$arrorgdet = getOrgDetail(1);
	$no=0;
	foreach($arrorgdet as $key=>$val){
		$no++;
		if($no==1){
			$unitkerja = $key;
		}
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";	
	}
	$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unitkerja."'");
	$optsj="";
	$optsj.="<option value=''></option>";
	$str="select b.* from ".$dbname.".log_suratjalanht a left join ".$dbname.".log_suratjalandt b on a.nosj=b.nosj left join ".$dbname.".log_poht c on b.nopo=c.nopo where a.kodept='".$optpt[$unitkerja]."' 
	and a.franco like '".$unitkerja."%' and a.posting='1' and c.tipepo='PO' group by b.nosj,b.nopo,b.kodebarang";
	$res=fetchdata($str);
	foreach($res as $valxx){

		$strx="select nopo,jumlahpesan, jmlhstlhclose, kodebarang, satuan from ".$dbname.".log_podt where nopo='".$valxx['nopo']."' and kodebarang='".$valxx['kodebarang']."'";   
		$resx=fetchdata($strx);
		$counthasil = 0;
		foreach($resx as $valx){
			$strxx="select kodebarang, sum(jumlah) as jumlahterima, satuan from ".$dbname.".log_transaksidt where nopo='".$valx['nopo']."' and kodebarang='".$valx['kodebarang']."'";
			$resxx=fetchdata($strxx);
			$jlhterima = ($resxx[0]['jumlahterima']==''?0:$resxx[0]['jumlahterima']);
	
			if($valx['satuan']!=$resxx[0]['satuan']){ // Satuan PO tidak sama dengan Transaksi?
			  //konversi satuan jika satuan default kodebarang tidak sama dengan satuan po
			  $str1="select jumlah from ".$dbname.".log_5stkonversi 
					 where darisatuan='".$resxx[0]['satuan']."' and satuankonversi='".$valx['satuan']."'
					 and kodebarang='".$resxx[0]['kodebarang']."'";
			  $res3=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			  $res3->setFetchMode(PDO::FETCH_OBJ);
			  while($bar2=$res3->fetch())
			  {
				  $valx['jumlahpesan']=round(($valx['jumlahpesan']/$bar2->jumlah),6);//mengkonversi satuan
			  }	   
	
			}
			// exit("warning : ".$valx['jumlahpesan']." - ".$jlhterima." - ".$valx['jmlhstlhclose']." ");
			$hasil = $valx['jumlahpesan'] - $jlhterima - $valx['jmlhstlhclose'];
			if($hasil > 0){
				// $counthasil++;
				$array_nopoSJ[$valx['nopo']]=$valx['nopo'];
			}
		}
		
	}
	// Mendapatkan array kunci
	$keys = array_keys($array_nopoSJ);
	// Mengubah array kunci menjadi string yang dapat digunakan dalam klausa IN
	$inClause = "'" . implode("', '", $keys) . "'";

	$str="select nosj from ".$dbname.".log_suratjalandt where nopo in (".$inClause.") group by nosj";
	$res=fetchdata($str);
	foreach($res as $val){
		$optsj.="<option value='".$val['nosj']."'>".$val['nosj']."</option>";
	}
// end joki (get surat jalan)



	echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['info']."</legend>
		Jika setelah simpan gudang <b>No.Dokumen</b> tidak muncul, coba cek periode akuntansi gudangnya
	</fieldset>
	<div style=clear:both></div>";

	$frm[0].="<fieldset><legend>".$_SESSION['lang']['header']."</legend>";
	$frm[0].=$_SESSION['lang']['peringatanretur']."
		<table cellspacing=1 border=0>
			<tr>
				<td>".$_SESSION['lang']['momordok']."</td>
				<td>:</td>
				<td><input type=text id=nodok size=25 disabled class=myinputtext></td>	 
				<td class='bintang'>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext id=tanggal size=25 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" value='' readonly>
				</td>
				<td></td>
				<td rowspan=5 style='vertical-align:top' id='tdpersetujuan'></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['supplier']."</td>
				<td>:</td>
				<td><input type=hidden value='' id=idsupplier><input type=text id=supplier class=myinputtext size=25 maxength=25 onkeypress=\"return tanpa_kutip(event);\" disabled></td>
				<td>".$_SESSION['lang']['suratjalan']."</td>
				<td>:</td>
				<!--<td>
					<select class=select2 id='nosj' style='width:150px;'>".$optsj."</select>
					<img id='nosj' onclick=z.elSearch('nosj',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>-->
				<td>
					<input type=text id=nosj class=myinputtext size=25 maxength=25 onkeypress=\"return tanpa_kutip(event);\">				
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['faktur']."</td>
				<td>:</td>
				<td><input type=text id=nofaktur class=myinputtext size=25 maxength=25 onkeypress=\"return tanpa_kutip(event);\"></td>
				<td class='bintang'>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>
					<input type=text id=nopo class=myinputtext size=25 maxength=25 onkeypress=\"return tanpa_kutip(event);\" onclick=cariPO('".$_SESSION['lang']['find']."',event) readonly>
					<img src=images/zoom.png title='".$_SESSION['lang']['find']."' class=resicon onclick=cariPO('".$_SESSION['lang']['find']."',event)>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>
					<button class=mybutton onclick=getPOSupplier() id=btnheader>".$_SESSION['lang']['tampilkan']."</button>
				</td>
			</tr>
		</table>";
		
	//==================masukkan variable periode gudang
	//$sess=$_SESSION['gudang'];
	foreach($_SESSION['gudang'] as $key=>$val)
	{
		//  echo	$sess[$key]['start'];
		$frm[0].="<input type=hidden id='".$key."_start' value='".$_SESSION['gudang'][$key]['start']."'>
			<input type=hidden id='".$key."_end' value='".$_SESSION['gudang'][$key]['end']."'>";
	}	 
	$frm[0].="</fieldset>
	<fieldset>
		<legend>".$_SESSION['lang']['detail']."</legend>
		<div id=container></div>
	 </fieldset>";
		 
	$frm[1].="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<fieldset style=float:left>
			<table>
				<tr>
					<td>No. Transaksi</td>
					<td>:</td>
					<td>
						<input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\">
					</td>
				
					<td>No. PO/SO</td>
					<td>:</td>
					<td>
						<input type=text id=txtposo size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\">
					</td>
				
					<td>Nama Supplier</td>
					<td>:</td>
					<td>
						<input type=text id=txtnamasup size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\">
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=cariBapb()>".$_SESSION['lang']['find']."</button>
						<button class=mybutton onclick=resetBapb()>Reset</button>
					</td>
				</tr>
			</table>
		</fieldset>
		<div style='clear:both;padding-bottom:10px'></div>
		<table class=sortable cellspacing=1 border=0 cellpadding=5>
			<thead>
			<tr class=rowheader>
				<th rowspan=2 align=center>No.</th>
				<th rowspan=2 align=center  style='max-width:100px'>".$_SESSION['lang']['sloc']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['tipe']."</th>
				<th rowspan=2 align=center style='max-width:60x'>".$_SESSION['lang']['momordok']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['pt']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['nopo']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['supplier']."</th> 
				<th rowspan=2 align=center>".$_SESSION['lang']['dbuat_oleh']."</th>
				<th align=center id='tdapp1'>".$_SESSION['lang']['persetujuan']."</th>
				<th rowspan=2 align=center>".$_SESSION['lang']['posted']."</th>
				<th rowspan=2 colspan=4 align=center>Action</th>
			</tr>
			<tr id='trapp1'>
			</tr>
			</head>
			<tbody id=containerlist></tbody>
			<tfoot>
			</tfoot>
		</table>
	</fieldset>";
		 
	//========================
	$hfrm[0]=$_SESSION['lang']['penerimaanbarang'];
	$hfrm[1]=$_SESSION['lang']['list'];
	//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
	drawTab('FRM',$hfrm,$frm,200,'100%');
	//===============================================	 
}
else
{
	echo " Error: Transaction Period missing";
}
CLOSE_BOX();
close_body();
?>