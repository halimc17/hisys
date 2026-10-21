<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_po').'</span>');
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_po.js?v=<?php echo time(); ?>" /></script>
<script type="text/javascript" src="js/log_link.js?v=1.2" /></script>

<?php
$jenisApp = "PO";
$arrFilter=array("1"=>"Release","2"=>"Unrelease","3"=>"Become Out Standing","4"=>"Close","5"=>"Cancel");
$optFilter="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrFilter as $row=>$lst){
	$optFilter.="<option value='".$row."'>".$lst."</option>";
}
$_SESSION['sorefrensi']=array();
$_SESSION['somaterial']=array();
#tahun PO dan supplier (sesuai PO yang boleh dilihat pengguna): default tahun sekarang, "Semua Tahun" untuk seluruh tahun
$scopePo = ($_SESSION['empl']['kodejabatan'] == '5') ? "" : " and purchaser='".$_SESSION['standard']['userid']."'";
$tahunSkrg=date('Y');
$arrTahun=array($tahunSkrg=>$tahunSkrg);
$res=fetchdata("select distinct year(tanggal) as thn from ".$dbname.".log_poht where tanggal>'0000-00-00'".$scopePo);
foreach($res as $val){
	$arrTahun[$val['thn']]=$val['thn'];
}
krsort($arrTahun);
$optFilterTahun="<option value=''>Semua Tahun</option>";
foreach($arrTahun as $thn){
	$optFilterTahun.="<option value='".$thn."'".($thn==$tahunSkrg?" selected":"").">".$thn."</option>";
}
$namasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$arrSupplier=array();
$res=fetchdata("select distinct kodesupplier from ".$dbname.".log_poht where kodesupplier<>''".$scopePo);
foreach($res as $val){
	$arrSupplier[$val['kodesupplier']]=trim(isset($namasupplier[$val['kodesupplier']])?$namasupplier[$val['kodesupplier']]:$val['kodesupplier']);
}
asort($arrSupplier);
$optFilterSupplier="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrSupplier as $kode=>$nama){
	$optFilterSupplier.="<option value='".htmlspecialchars($kode,ENT_QUOTES)."'>".htmlspecialchars($nama)."</option>";
}
echo"<div id=dataAtas>
<div id='action_list'>
<table>
	<tr valign=moiddle>
		<td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
		<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
		</td>
		<td>
			<fieldset>
			<legend>".$_SESSION['lang']['find']."</legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['tahun']." PO/SO</td>
						<td>:</td>
						<td style='width:220px'>
							<select id=filterTahun class=select2 style='width:100%'>".$optFilterTahun."</select>
						</td>
						<td style='padding-left:15px;'>".$_SESSION['lang']['status']."</td>
						<td>:</td>
						<td style='width:260px'>
							<select id=filterId class=select2 style='width:100%'>".$optFilter."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['nopo']."</td>
						<td>:</td>
						<td><input type=text id=txtsearch maxlength=30 class=myinputtext style='width:100%;box-sizing:border-box'></td>
						<td style='padding-left:15px;'>".$_SESSION['lang']['tanggal']." PO/SO</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 readonly style='width:100%;box-sizing:border-box'/></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['nopp']."</td>
						<td>:</td>
						<td><input type=text id=txtsearch_nopp maxlength=30 class=myinputtext style='width:100%;box-sizing:border-box'></td>
						<td style='padding-left:15px;'>".$_SESSION['lang']['tanggal']." Rilis</td>
						<td>:</td>
						<td><input type=text class=myinputtext id=tglrilis_cari onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 readonly style='width:100%;box-sizing:border-box'/></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['namasupplier']."</td>
						<td>:</td>
						<td>
							<select id=filterSupplier class=select2 style='width:100%'>".$optFilterSupplier."</select>
						</td>
						<td colspan=3 style='padding-left:15px;'>
							<button class=mybutton onclick=load_new_data(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=exportExcelPO()>".$_SESSION['lang']['excel']."</button>
							<button class=mybutton onclick=exportPdfPO()>PDF</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
		<td style='display:none'>
			<fieldset>
			<legend>List Job</legend>
			<div id=notifikasiKerja>
				
			</div>
			</fieldset>
		</td>
	</tr>
</table>
</div> 
</div>"; 

CLOSE_BOX();

echo "<div id=\"list_po\">";
OPEN_BOX(); //2 O
$countApp = getCountApproval($jenisApp,'');
echo"<div class='table-scroll' style=height:68vh>
		<table cellspacing='1' cellpadding=5 border='0' class='sortable' style='width:100%'>
			<thead>
				<tr class=rowheader>
					<th rowspan=2 align=center>No</th>
					<th rowspan=2 align=center style='min-width:190px'>".$_SESSION['lang']['nopo']."</th>
					<th rowspan=2 align=center style='min-width:230px'>".$_SESSION['lang']['nopp']."</th>
					<th rowspan=2 align=center></th>
					<th rowspan=2 align=center>".$_SESSION['lang']['namasupplier']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['tgl_po']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['syaratPem']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['chat']."</th>
					<th colspan='".$countApp."' align=center>".$_SESSION['lang']['status']." Persetujuan</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['status']."</th>
					<th rowspan=2 align=center>PIC</th>
					<th rowspan=2 align=center>TERMIN</th>
					<th rowspan=2 align=center style='width:230px'>".$_SESSION['lang']['keterangan']."</th>
					<th rowspan=2 align=center>action</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['tanggalRelease']."</th>
					<th rowspan=2 align=center>".$_SESSION['lang']['print']."</th>
				</tr>
				<tr class=rowheader>";
				for($i=1;$i<=$countApp;$i++){
					echo"<th align=center>".$_SESSION['lang']['persetujuan']. "".$i."</th>";
				}
				echo"</tr>
			</thead>
			<tbody id='contain'><script>load_new_data(0)</script>
		</tbody>
		<tfoot id=contx></tfoot></table>
</div>";
CLOSE_BOX();
echo"</div>";


echo "<div id=list_pp name=list_pp style=display:none;>";
OPEN_BOX();
$optPt='';
$sql3="select * from ".$dbname.".organisasi where tipe='PT'";
$query3=$owlPDO->query($sql3) or die(print " Gagal: ".PDOException::getMessage());
$query3->setFetchMode(PDO::FETCH_OBJ);
while($res3=$query3->fetch())
{
	$optPt.="<option value='".$res3->kodeorganisasi."'>".$res3->namaorganisasi."</option>";
}

$frm[0]="<fieldset>
	<legend>".$_SESSION['lang']['list_pp']."</legend>
    <table cellspacing=1 border=0>
		<tr>
			<td>Please Select Company</td>
			<td>:</td>
			<td>
				<select id=kode_pt name=kode_pt onchange=cek_pp_pt()>
					<option value=''></option>".$optPt."</select>
			</td>
		</tr>
		<br />
		<input type=hidden id=proses name=proses value=insert />
		<table cellspacing=1 border=0 id=list_pp_table>
			<thead>
			<tr class=rowheader>
				<td>No.</td>
				<td align=center width=75px>".$_SESSION['lang']['tglPelimpahan']."</td>
				<td align=center>".$_SESSION['lang']['nopp']."</td>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center width=50px>".$_SESSION['lang']['jmlhDiminta']."</td>
				<td align=center>".$_SESSION['lang']['tgldibutuhkan']."</td>
				<td align=center width=50px>".$_SESSION['lang']['jmlh_brg_blm_po']."</td>
				<td align=center width=50px>".$_SESSION['lang']['jmlhPesan']."</td>
				<td align=center>Action</td>
			</tr>
			</thead>
			
			<tbody id=container_pp>
			<tr>
				<td colspan=9 align=center>
					<button name=proses id=proses onclick=process()>".$_SESSION['lang']['proses']."</button>
				</td>
			</tr>
			</tbody>
		</table>
        <input type=hidden id=user_id name=user_id value=".$_SESSION['standard']['userid']." />
        <input type=hidden id=pt_user value=".ptDariDetailAkses()." />
	</table>
</fieldset>";

$str="select distinct(a.nomor) as nomor from ".$dbname.".log_perintaanhargaht a left join ".$dbname.".log_permintaanhargadt b on a.nomor=b.nomor where 1=1 and a.purchaser='".$_SESSION['standard']['userid']."' and b.flag='1' and a.po=0";	

$query5=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$query5->setFetchMode(PDO::FETCH_OBJ);
$optRPH=$optsup="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($res5=$query5->fetch())
{
	$optRPH.="<option value='".$res5->nomor."'>".$res5->nomor."</option>";
}

$frm[1]="<fieldset>
	<legend>".$_SESSION['lang']['form']."</legend>
    <table cellspacing=1 border=0>
		</tr>
			<td>No ".$_SESSION['lang']['bandingHarga']."</td>
			<td>:</td>
			<td>
				<select id=nodph style='width:200px' onchange=getsuprph()>".$optRPH."</select>
				<img id='nodph' onclick=z.elSearch('nodph',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>	 
		</tr>
		<tr>
			<td> ".$_SESSION['lang']['namasupplier']."</td>
			<td>:</td>
			<td>
				<select id=suprph style='width:200px'>".$optsup."</select>
			</td>	 
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=adddph()>".$_SESSION['lang']['proses']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

$hfrm[0]=$_SESSION['lang']['daftarbarang'];
$hfrm[1]=$_SESSION['lang']['bandingHarga'];
drawTab('FRM',$hfrm,$frm,200,'100%');

CLOSE_BOX();
echo"</div>";























echo"<div id='form_po' style='display:none'>";
OPEN_BOX();

$isiOpt= array(1=>'Cash',2=>'Transfer',3=>'Giro',4=>'Cheque');
$optTermpay="";
foreach($isiOpt as $ter => $OptIsi)
{
	$optTermpay.="<option value='".$ter."'>".$OptIsi."</option>";
}

$optSupplier='';
$optSupkelompok='';
$optpil='';
$sql="select a.namasupplier,a.supplierid from ".$dbname.".log_5supplier a 
left join ".$dbname.".log_5supkelompok b on a.supplierid=b.supplierid 
where b.tipe='SUPPLIER' and a.status=1 order by a.namasupplier asc";    
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch())
{
	$optSupplier.="<option value='".$res['supplierid']."'>".$res['namasupplier']."</option>";
}

$sMt="select kode,kodeiso from ".$dbname.".setup_matauang order by kode desc";
$qMt=$owlPDO->query($sMt) or die(print " Gagal: ".PDOException::getMessage());
$qMt->setFetchMode(PDO::FETCH_ASSOC);
$optMt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while($rMt=$qMt->fetch())
{
	$optMt.="<option value='".$rMt['kode']."' ".($rMt['kode']=='IDR'?"selected":"").">".$rMt['kodeiso']."</option>";
}

$user_id=$_SESSION['standard']['userid'];
$klq="select namakaryawan,karyawanid,bagian,lokasitugas,kodejabatan from ".$dbname.".`datakaryawan` where lokasitugas like '%HO' and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') and karyawanid!='".$user_id."' and lokasitugas!='' and tanggalkeluar='0000-00-00' order by namakaryawan asc";
$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_OBJ);
$optPur=$optPur2="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while($rst=$qry->fetch())
{
	$sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
	$qBag=$owlPDO->query($sBag) or die(print " Gagal: ".PDOException::getMessage());
	$qBag->setFetchMode(PDO::FETCH_ASSOC);
    $rBag=$qBag->fetch();
    if($rst->kodejabatan != '173') 
	{
		$optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
	}
	
	$optPur2.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
}

$sKrm="select id_franco,franco_name from ".$dbname.".setup_franco where status=0 order by franco_name asc";
$qKrm=$owlPDO->query($sKrm) or die(print " Gagal: ".PDOException::getMessage());
$qKrm->setFetchMode(PDO::FETCH_ASSOC);
$optKrm.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rKrm=$qKrm->fetch())
{
	$optKrm.="<option value=".$rKrm['id_franco'].">".$rKrm['franco_name']."</option>";
}

$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar order by keterangan asc";
$qSyp=$owlPDO->query($sSyp) or die(print " Gagal: ".PDOException::getMessage());
$qSyp->setFetchMode(PDO::FETCH_ASSOC);
$optSyp="";
while($rSyp=$qSyp->fetch())
{
	$optSyp.="<option value=".$rSyp['kode'].">".$rSyp['keterangan']." (".$rSyp['jenis'].")</option>";
}

//Get Surat Jalan
$arrsurjal=makeOption($dbname,'log_po_sj','nosj,nosj');
		$str="select *  from ".$dbname.".log_suratjalanht";  
		$optSuratJalan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{

			@$optSupx = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['expeditor']."'");
			if(@$arrsurjal[$bar['nosj']]==''){
				@$optSuratJalan.="<option value='".$bar['nosj']."' selected>".$bar['nosj']." [".$optSupx[$bar['expeditor']]."]</option>";
			}
			
			
		}

echo"<fieldset>
	<table cellspacing='1' border='0'>
		<tr>
			<td style='vertical-align:top'>
				
				<table cellspacing='1' border='0'>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>
							<!--<input type='text' name='tgl_po' id='tgl_po' class='myinputtext' value='".date("d-m-Y")."'  readonly='readonly' style='width:170px;text-align:center' />-->
							<input type=text class=myinputtext style='width:170px;text-align:center' id=tgl_po onkeypress='return tanpa_kutip(event)' onmousemove='setCalendar(this.id)' value='".date("d-m-Y")."'>
						</td>
					</tr>
				
					<tr>
						<td>".$_SESSION['lang']['nopo']."</td>
						<td>:</td>
						<td>
							<input type='text' name='no_po' id='no_po' class='myinputtext' style='width:170px;' disabled='disabled' />
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['npwp']."</td>
						<td>:</td>
						<td>
							<select class=select2po id='npwporg' style='width:174px;'  name='npwporg'>".$optpil."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['matauang']."</td>
						<td>:</td>
						<td>
							<select class=select2po id='mtUang' style='width:174px;'  name='mtUang' disabled>".$optpil."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kurs']."</td>
						<td>:</td>
						<td>
							<input  type='text' style='width:170px;'  class='myinputtext' id='Kurs' name='Kurs' style='width:100px;' onkeypress='return angka_doang(event)' value='1' disabled />
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
			<td style='vertical-align:top'>
				<fieldset>
					<legend class='bintang'>".$_SESSION['lang']['supplier']."</legend>
					<table cellspacing='1' border=0>
						<tr>
							<td>".$_SESSION['lang']['namasupplier']."</td>
							<td>:</td>
							<td>
								<select class=select2po id='supplier_id' name='supplier_id' style='width:300px;'  onchange='get_supplier()'>
									<option value=''></option>
									".$optSupplier."
								</select>
							</td>
						</tr>
						<tr>
							<td>Sub Kelompok ".$_SESSION['lang']['supplier']."</td>
							<td>:</td>
							<td>
								<select class=select2po id='subsupplier_id' style='width:300px;' name='subsupplier_id'>
									<option value=''></option>
									".$optSupkelompok."
								</select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['alamat']." ".$_SESSION['lang']['supplier']."</td>
							<td>:</td>
							<td>
								<select class=select2po id=alamat_sup style='width:300px;'><option value=''></option></select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['npwp']." ".$_SESSION['lang']['supplier']."</td>
							<td>:</td>
							<td>
								<select class=select2po id=npwp_sup style='width:300px;'><option value=''></option></select>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['norekeningbank']."</td>
							<td>:</td>
							<td colspan=4>
								<select class=select2po id=bank_acc style='width:300px;'><option value=''></option></select>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
	</fieldset>
	
	<fieldset style='min-width:95%'>
		<legend>".$_SESSION['lang']['daftarbarang']."</legend>
		<table cellspacing='1' border='0' id='detail_content_table' name='detail_content_table' width=100%>
			<tbody id='detail_content' name='detail_content'>
			<tr>
				<td>
				<table id='ppDetailTable'>
				</table>
				<table cellspacing='1' border='0'>
					<tr>
						<td style='vertical-align:top'>
						<div>
							<table cellspacing='1' border='0'>
								<tr style='display:none;'>
									<td>".$_SESSION['lang']['tgl_kirim']."</td>
									<td>:</td>
									<td>
										<input type='text' class='myinputtext' id='tgl_krm' name='tgl_krm' onmousemove='setCalendar(this.id)' onkeypress='return false;'   maxlength='10'  style='width:200px;' value='0000-00-00' readonly/>
									</td>
								</tr>
								<tr hidden>
									<td>Internal Memo</td>
									<td>:</td>
									<td>
										<input name=fileupload type=file id=fileupload title='file hanya : JPG,JPEG,PNG,PDF' class=mybutton style=width:160px>
										<img  title='hapus file terpilih' class=zImgBtn onclick=clearfile() src=images/delete_32.png>
									</td>
								</tr>";
								
								$str="select *  from ".$dbname.".log_5delivtime";  
								$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
								$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
								$res->setFetchMode(PDO::FETCH_ASSOC);
								while($bar=$res->fetch())
								{
									$optjenis.="<option value='".$bar['kode']."'>".$bar['nama']."</option>";
								}
								
								echo"<tr>
									<td class='bintang'>".$_SESSION['lang']['waktupenyerahan']."</td>
									<td>:</td>
									<td>
										<select class=select2po id=delivtime style=\"width:200px;\">".$optjenis."</select>
									</td>
								</tr>
								
								<tr>
									<td>".$_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['barang']."</td>
									<td>:</td>
									<td>
										<select class=select2po style='width:200px' id='tmpt_krm' name='tmpt_krm' disabled>".$optKrm."</select>
									</td>
								</tr>
								<tr>
									<td class='bintang'>".$_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['invoice']."</td>
									<td>:</td>
									<td>
										<select class=select2po style='width:200px' id='invc_krm' name='tmpt_krm'>".$optKrm."</select>
									</td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['syaratPem']."</td>
									<td>:</td>
									<td>
										<select class=select2po style='width:200px' id='term_pay' name='term_pay' disabled>".$optSyp."</select>
									</td>
									
								</tr>
								<tr>
									<td valign='top'>".$_SESSION['lang']['keterangan']."</td>
									<td valign='top'>:</td>
									<td>
										<textarea style='width:180px' id='ketUraian' name='ketUraian' onkeypress='return tanpa_kutip(event);'></textarea>
									</td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['purchaser']."</td>
									<td>:</td>
									<td>
										<input style='width:195px' type='text' id='purchaser_id' name='purchaser_id' class='myinputtext' disabled='disabled' value='".$_SESSION['empl']['name']."'  style='width:200px;' />
									</td>
								</tr>
							</table>
						</div>
						</td>
						<td rowspan=5 style='padding-left:20px; vertical-align:top'>
							<div id='tdlistapproval'>
							</div>
						</td>
					</td>
				</table>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>

<table cellspacing='1' border='0'>
	<tr>
		<td colspan='3'>
			<input type='hidden' id='btncancel'>
			<button id='btnSaveHeader' class='mybutton' onclick='save_headher()'>".$_SESSION['lang']['save']."</button>
			<button class='mybutton' onclick='cancel_headher()'>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr> 
</table>";
		
CLOSE_BOX();
echo"</div>";






















echo close_body();
?>