<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');

function valdefinition($post){
	$result = "";
	if(isset($_POST[$post])){
		$result	= $_POST[$post];
	}
	return $result;
}
$proses = "";
if(isset($_GET['proses'])){
	$proses	= $_GET['proses'];
}
$hal = 1;
if(isset($_GET['hal'])){
	$hal	= $_GET['hal'];
}

$unit	= valdefinition('unit');
$numrow	= valdefinition('numrow');
$kepada	= valdefinition('kepada');
$nomortransaksi	= valdefinition('nomortransaksi');
$notransaksi	= valdefinition('notransaksi');
$nomorurut		= valdefinition('nomorurut');
$tanggal		= valdefinition('tanggal');
$asisten		= valdefinition('asisten');
$divisi			= valdefinition('divisi');
$blok			= valdefinition('blok');
$kegiatan		= valdefinition('kegiatan');
$mandor			= valdefinition('mandor');
$statusblok		= valdefinition('statusblok');
$kodekegiatan	= valdefinition('kegiatan');
$rotasi			= valdefinition('rotasi');
$target			= valdefinition('target');
$pb				= valdefinition('pb');
$khl			= valdefinition('khl');
$bor			= valdefinition('nospk');
$unitangkut		= valdefinition('unitangkut');
$janjangtbs		= valdefinition('janjangtbs');
$kontan			= valdefinition('kontan');
$rpsatuan			= valdefinition('rpsatuan');



$result['err'] = "false";




function getMaxNotransaksi($data){
	$result = 1;
	if(count($data) > 0){
		$number = array();
		for($i=0; $i<count($data); $i++){
			list($date,$div,$num) = explode("/",$data[$i]['notransaksi']);
			$number[] = $num;
		}
		$result = max($number);
	}
	return $result;
}

switch($proses){
	default:
		OPEN_BOX('','<span class=judul></span>');
		
	?>
	<fieldset style="margin-bottom: 10px;">
		<legend id="title_Form"><b><?php echo getMenu('kebun_rkh'); ?></b></legend>
		<table class="sortable" cellspacing="1" style="width:100%" border="0">
			<thead>
				<tr>
					<th><?php echo $_SESSION['lang']['notransaksi']; ?></th>
					<th><?php echo $_SESSION['lang']['tanggal']; ?></th>
					<th><?php echo $_SESSION['lang']['divisi']; ?></th>
					<th><?php echo $_SESSION['lang']['asisten']; ?></th>
					<th><?php echo $_SESSION['lang']['status']; ?></th>
					<th colspan="3"><?php echo $_SESSION['lang']['view']; ?></th>
				</tr>
			</thead>
	<?php	
		if($tanggal != ""){
			$where .= " and a.tanggal = '".date('Y-m-d',strtotime($tanggal))."'";
		}
		if($nomortransaksi != ""){
			$where .= " and a.notransaksi = '".$nomortransaksi."'";
		}
		if($divisi != ""){
			$where .= " and a.divisi = '".$divisi."' or a.createby='".$_SESSION['standard']['userid']."' or a.createby='".$_SESSION['standard']['userid']."' or a.updateby='".$_SESSION['standard']['userid']."'";
		}
		else{
			if($_SESSION['empl']['subbagian'] != ""){
				$where .= " and a.divisi = '".$_SESSION['empl']['subbagian']."' or a.createby='".$_SESSION['standard']['userid']."' or a.createby='".$_SESSION['standard']['userid']."' or a.updateby='".$_SESSION['standard']['userid']."'";
			}
			else
			{
				$where .= " and a.divisi like '".$_SESSION['empl']['lokasitugas']."%' or a.createby='".$_SESSION['standard']['userid']."' or a.createby='".$_SESSION['standard']['userid']."' or a.updateby='".$_SESSION['standard']['userid']."'";
			}
		}
		@$limit = 20;//-Paging-
		@$halaman_aktif = $hal; //-Paging-Phalaman saat ini
		@$p = new Paging; // -Paging- Class paging
		@$posisi = @$p->cariPosisi(@$limit,@$halaman_aktif);// -Paging- Posisi Data
		
		$str = "select a.*,x.namakaryawan as namaasisten,y.namakaryawan as namaestatemanager,z.namakaryawan as namaaskep
		from " . $dbname . ".kebun_rkhht a
		left join datakaryawan x on a.asisten = x.karyawanid
		left join datakaryawan y on a.estatemanager = y.karyawanid
		left join datakaryawan z on a.askep = z.karyawanid
		where 1=1".$where." order by notransaksi desc";
		@$jpr = fetchData($str);
		@$jmldata = count($jpr);
		@$parnt = $str." LIMIT $posisi,$limit ";
		@$pr = fetchData($parnt);
		
		@$jml = @$p->jumlahHalaman(@$jmldata,@$limit);//-Paging- jumlah data
		
		$html = "<tbody>";
		if(count($pr) > 0){
			for($i=0; $i<count($pr); $i++){
				$html .= "<tr class=\"rowcontent\"  id=tr_$i>";
				$html .= "<td>".$pr[$i]['notransaksi']."</td>";
				$html .= "<td>".date('d-m-Y',strtotime($pr[$i]['tanggal']))."</td>";
				$html .= "<td>".$pr[$i]['divisi']."</td>";
				$html .= "<td>".$pr[$i]['namaasisten']."</td>";
				$html.="<td align=\"center\">";
				if($pr[$i]['status'] == 1){
					$html .= "Posted";
				}elseif($pr[$i]['status'] == 0){
					// $html .= "<button class=\"mybutton\" onclick=\"ajukanrkh('".$pr[$i]['notransaksi']."',this);\">".$_SESSION['lang']['posting']."</button>";
					$html .= "<img src=images/skyblue/posting.png class=resicon class=zImgBtn height='30'  title='Ajukan' onclick=\"form_ajukan('".$pr[$i]['notransaksi']."','".substr($pr[$i]['divisi'],0,4)."','".$i."');\" >";
					// $html .= "<button class=\"mybutton\" onclick=\"ajukanrkh('".$pr[$i]['notransaksi']."',this);\">".$_SESSION['lang']['posting']."</button>";
				}
				$html.="</td>";
				$html.="<td align='center'>";
				$html.="<a href='#' onclick=dataKeExcel(event,'kebun_slave_rkh.php','".$pr[$i]['notransaksi']."','".$pr[$i]['tanggal']."','".$pr[$i]['divisi']."')><img  src=images/excel.jpg class=resicon title='MS.Excel'>";				
				$html.=" Daftar KHL  Aktif</a></td>";
				$html.="<td align='center'>";
				$html.="<a href='#' onclick=dataKePDF(event,'kebun_slave_rkh.php','".$pr[$i]['notransaksi']."') ><img  src=images/excel.jpg class=resicon title='MS.Excel'>";				
				$html.="  RKH</a></td>";
				$html.='<td align="center">';
				if($pr[$i]['status'] == 0){
					$html.="<a href=\"#\" onclick=\"editdataHeader('showadd','".$pr[$i]['notransaksi']."')\" ><img src=\"images/application/application_edit.png\" class=\"resicon\" title=\"Edit\"></a>";
				}else{
					
				}
				$html.='&nbsp;<a href="#" onclick="viewdata(event,\'kebun_slave_rkh.php\',\''.$pr[$i]['notransaksi'].'\')" ><img src="images/zoom.png" class="resicon" title="Lihat">';				
				$html.='</a>';
				if($pr[$i]['status'] == 0){
					$html.="&nbsp;<a href='#' onclick=deleteall('".$pr[$i]['notransaksi']."') ><img src='images/delete_32.png' class='resicon' title='Hapus'></a>";
				}
				$html .= "</td></tr>";
			}
		}
		$html .= "</tbody>";	
		$html .= '<tfoot><tr><td colspan="9" align="center">';
		//insert Attribute action ex: href/onclick/onchange/etc..
		$buttonaction = array(
			'first' =>	'onclick="getSlave(\'&hal=1\');"',
			'prev' 	=> 	'onclick="getSlave(\'&hal='.($halaman_aktif-1).'\')"',
			'next' 	=> 	'onclick="getSlave(\'&hal='.($halaman_aktif+1).'\')"',
			'last' 	=> 	'onclick="getSlave(\'&hal='.($jml).'\')"',
			'pages'	=> 	'onchange="getSlave(\'&hal=\'+this.value);"'
		);
		$html .= $p->navHalaman($halaman_aktif,$jml,$buttonaction); //-Paging- Create Element Nav halaman; 
		$html .="</td></tr></tfoot></table></fieldset>";
		
		echo $html;
		CLOSE_BOX();
	break;
	
	
	
	#=buat ajukan
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='RKH' and a.level='1' and a.kodeunit='".$unit."'  
				  order by b.namakaryawan asc";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:99%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
	
	case'ajukan':
	
		try {
		$owlPDO->beginTransaction();
			if($kepada=='' or $notransaksi==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			//update flag menjadi 1
			$str = "update " . $dbname . ".kebun_rkhht set status='1' where notransaksi = '" . $notransaksi . "'";
			$owlPDO->exec($str);
			//insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','".$notransaksi."','RKH','1','" . $kepada."','0','','','')";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	case 'excel':
		
		$divisi = checkPostGet('divisi', '');
		$tanggal = checkPostGet('tanggal', '');
		
		$stream.= "<table cellspacing='1' border='1'>";
		$stream.= "<tr>";
			$stream.= "<td align=center>".$_SESSION['lang']['nourut']."</td>";
			$stream.= "<td align=center>".$_SESSION['lang']['nik']."</td>";
			$stream.= "<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
			$stream.= "<td align=center>".$_SESSION['lang']['divisi']."</td>";
		$stream.= "</tr>";		
		
		
		$str="select * from ".$dbname.".datakaryawan where lokasitugas='".substr($divisi,0,4)."' 
				and statuskaryawan != 'Keluar' and (tanggalkeluar='0000-00-00' or tanggalkeluar<'".$tanggal."') and tipekaryawan=4 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$stream.= "<tr>";
				@$no+=1;
				$stream.= "<td align=center>".$no."</td>";
				$stream.= "<td>".$bar['nik']."</td>";
				$stream.= "<td>".$bar['namakaryawan']."</td>";
				$stream.= "<td>".$bar['subbagian']."</td>";
			$stream.= "</tr>";		
		}
		
		
		$stream.= "</table>";
		
		$tglSkrg = date("Ymd");
        $nop_ = "daftar_karyawan_khl_aktif_";
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
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
	
	case'checkasisten':
	$str = "select Count(*) as jumlahdata, asisten,status from ".$dbname.".kebun_rkhht where tanggal='".tanggalsystem($tanggal)."' and divisi='".$divisi."' ";
	$res = fetchData($str);

	if($res[0]['jumlahdata']>0)
	{
		if($res[0]['status']==1)
		{
				echo "Data sudah di posting";
		}
		else{
			
				if(intval($res[0]['asisten'])!=intval($asisten))
				{
					echo "Asisten tidak boleh berbeda dari data sebelumnya";
				}
				else
				{
					echo "";
				}
		}
	}
	else
	{
		echo "";
	}
	break;
	
	case 'showadd':
	OPEN_BOX('','<span class=judul></span>');
		$whereorg="induk='".$_SESSION['empl']['lokasitugas']."' and tipe in ('AFDELING','BIBITAN')";
		$optdivisi = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereorg,null,true);
		$whereasistent="kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%asist%' or namajabatan like '%asst%' or namajabatan like '%assist%')";
		$optasisten = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whereasistent,null,true);
		$wheremandor = "kodejabatan in (select kodejabatan from ".$dbname.".sdm_5jabatan where namajabatan like '%mandor%')";
		$optmandor = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$wheremandor,null,true);

		$optkontan="<option value='KERJA'>KERJA</option>";
		$optkontan.="<option value='KONTAN'>KONTAN</option>";
		$asisten = "";
		$tanggal = "";
		$divisi = "";
		if($nomortransaksi != ""){
			$parnt = "select asisten,tanggal,divisi from " . $dbname . ".kebun_rkhht where notransaksi = '".$nomortransaksi."' limit 1";
			$pr = fetchData($parnt);
			if(count($pr) > 0){
				$asisten = $pr[0]['asisten'];
				$tanggal = $pr[0]['tanggal'];
				$divisi = $pr[0]['divisi'];
			}
		}
	
	?>
	<form id="insert_rkh" name="insert_rkh" method="POST" action="#" onsubmit="inputData(this);return false;">
		<fieldset style="margin-bottom: 10px;">
			<legend id="title_Form"><b><?php echo getMenu('kebun_rkh'); ?></b></legend>
			<div>
			<br/>
				<table border="0" cellspacing="0" cellpadding="1">
					<tbody>
						<tr>
							<td style="padding-right:20px;font-size:12px">
								<label for="tanggal"><?php echo $_SESSION['lang']['tanggal']; ?></label>
							</td>
							<td style="padding-right:20px;font-size:12px">
								<input id="tanggal" name="tanggal" class="myinputtext" type="text" onchange="getSlave('listprestasi');" style="width:150px" readonly="readonly" onmousemove="setCalendar(this.id)" value="<? echo $tanggal; ?>">
							</td>
							<td style="padding-right:20px;font-size:12px">
								<label for="asisten"><?php echo $_SESSION['lang']['asisten']; ?></label>
							</td>
							<td style="padding-right:20px;font-size:12px">
								<select id="asisten" name="asisten" style="width:155px" onchange="getSlave('listprestasi');">
								<?php foreach($optasisten as $k => $v ){ ?>
										<?php if($k == $asisten){ ?>
											<option value="<?php echo $k; ?>" selected><?php echo $v; ?></option>
										<?php }else{ ?>
											<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
										<?php }?>
								<?php } ?>
								</select>
								<img id="asisten_find" onclick="z.elSearch('asisten',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">
							</td>
							<td style="padding-right:20px;font-size:12px">
								<label for="divisi"><?php echo $_SESSION['lang']['divisi']; ?></label>
							</td>
							<td style="padding-right:20px;font-size:12px">
								<select id="divisi" name="divisi" style="width:155px" onchange="getSlave('findblok',this)">
								<?php foreach($optdivisi as $k => $v ){ ?>
										<?php if($k == $divisi){ ?>
											<option value="<?php echo $k; ?>" selected><?php echo $v; ?></option>
										<?php }else{ ?>
											<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
										<?php }?>
								<?php } ?>
								</select>
								<img id="divisi_find" onclick="z.elSearch('divisi',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">
							</td>
						</tr>
					</tbody>
				</table>
				<br>
			</div>
		<button id="btn_isidata" onclick="isidata(this);" class="mybutton" position="isidata">Tambah Data</button>
		</fieldset>
	<div id="detaildatainput" style="display:none;">	
			<div style="float:left; width:50%;">
				<fieldset>
					<legend id="title_Form"><b><?php echo $_SESSION['lang']['blok']; ?>: <span id="namablok">?</span></b></legend>
					<table border="0" cellspacing="0" cellpadding="1" style="width:100%;">
					<tbody>
						<tr>
							<td style="width:22%;">
								<label for="blok"><?php echo $_SESSION['lang']['blok']; ?></label>
							</td>
							<td colspan="3">
								<select id="blok" name="blok" onchange="getSlave('findblokinfo',this)" style="min-width:90%;">
									<option value="" selected><?php echo $_SESSION['lang']['pilih']; ?></option>
								</select>
								<input id="statusblok" name="statusblok" class="myinputtext" type="hidden" value="">
								<img id="blok_find" onclick="z.elSearch('blok',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">
							</td>
						</tr>
						<tr>
							<td style="width:22%;">
								<label for="kegiatan"><?php echo $_SESSION['lang']['pekerjaan']; ?></label>
							</td>
							<td colspan="3">
								<select id="kegiatan" name="kegiatan" style="min-width:90%;" onchange="getSlave('findbarang',this)">
									<option value="" selected><?php echo $_SESSION['lang']['pilih']; ?></option>
								</select>
								<img id="kegiatan_find" onclick="z.elSearch('kegiatan',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">
							</td>
						</tr>
						<tr>
							<td style="width:22%;"><?php echo $_SESSION['lang']['luas']; ?> </td>
							<td style="width:22%;padding-right:2%;">: <span id="luasareaproduktif">?</span></td>
							<td style="width:22%;"><?php echo $_SESSION['lang']['rotasi']; ?> </td>
							<td style="width:22%;"><input id="rotasi" name="rotasi" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text";" value="0" ></td>
						</tr>
						<tr>
							<td><?php echo $_SESSION['lang']['sph']; ?></td>
							<td style="padding-right:2%;">: <span id="sph">?</span></td>
							<td>Target &nbsp;&nbsp;<span id="satuankegiatan"></span></td>
							<td><input id="target" name="target" class="myinputtextnumber" value="0" onkeypress="return angka_doang(event)" type="text";" onblur="resetNorma()"></td>
						</tr>
						<tr>
							<td><?php echo $_SESSION['lang']['tahuntanam']; ?></td>
							<td>: <span id="tahuntanam">?</span></td>
							
							<td>Kontan</td>
							<td><select id="kontan"  name="kontan" style='width:100px;'><?php echo $optkontan ?></select></td>
							
						</tr>
						<tr>
							<td><?php echo @$_SESSION['lang']['rpsat']; ?></td>
							
							<td>:<input id="rpsatuan" name="rpsatuan" class="myinputtextnumber" value="0" onkeypress="return angka_doang(event)" type="text";"  style='width:100px;'></td>
						</tr>
					</body>
					</table>
				</fieldset>
			</div>
			<div style="float:right; width:50%;">
				<fieldset style="min-height:291px;">
					<legend id="title_Form"><b> <?php echo $_SESSION['lang']['material']; ?></b></legend>
					<table border="0" cellspacing="0" cellpadding="1" style="width:100%;">
					<tbody>
						<tr>
							<td>Jenis Material </td>
							<td style="padding-right:20px;">:
								<select id="material" style="width:155px" onchange="getSlave('findsatuanmaterial',this)">
									<option value="" selected><?php echo $_SESSION['lang']['pilih']; ?></option>
								</select>
								<img id="material_find" onclick="z.elSearch('material',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">
							</td>
							<td><?php echo $_SESSION['lang']['norma']; ?></td>
							<td style="padding-right:20px;">: <input id="normamaterial" class="myinputtext" type="decimal" onkeypress="return angka_doang(event)" type="text";" value="" placeholder="(Jml mat/Target)" onblur="resetNorma2()"></td>
						</tr>
						<tr>
							<td><?php echo $_SESSION['lang']['satuan']; ?></td>
							<td>: <span id="satuanmaterial">?</span></td>	
							<td><?php echo $_SESSION['lang']['jumlah']; ?></td>
							<td style="padding-right:20px;">: <input id="jumlahmaterial" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text";" value="0" onblur="resetNorma()" ></td>
						</tr>
					</body>
					</table>
					<br>
					<button type="button" class="mybutton" onclick="addMaterial()">Add Material</button>
					<hr>
					<table class="sortable" cellspacing="1" style="width:100%" border="0">
						<thead>
							<tr class="rowheader">
								<th>Jenis Material</th>
								<th><?php echo $_SESSION['lang']['norma']; ?></th>
								<th><?php echo $_SESSION['lang']['jumlah']; ?></th>
								<th><?php echo $_SESSION['lang']['satuan']; ?></th>
								<th>#</th>
							</tr>
						</thead>
						<tbody id="matrialbox">
						</tbody>
					</table>
				</fieldset>
			</div>
			<div style="float:left; width:50%;margin-top:10px;">
				<fieldset>
					<legend id="title_Form"><b><?php echo $_SESSION['lang']['kebutuhan']; ?> <?php echo $_SESSION['lang']['hk']; ?></b></legend>
					<table border="0" cellspacing="0" cellpadding="1" style="width:100%;">
					<tbody>
						<tr>
							<td style="width:22%;"><b><?php echo $_SESSION['lang']['hk']; ?></b></td>
							<td style="width:22%;"></td>
							<td style="width:22%;padding-left:2%;"><b>Jumlah HK</b></td>
							<td style="width:22%;"></td>
						</tr>
						<tr>
							<td>HK/Satuan</td>
							<td ><input placeholder="(jml hk/Target)" disabled id="norma" name="norma" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text";" value="" style="width:90%"></td>
							<td style="padding-left:2%;">PB</td>
							<td><input id="pb" name="pb" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text";" value="0" style="width:90%" onblur="resetNorma()"></td>
						</tr>
						<tr>
							<td>Kontrak/Borong</td>
							<td><input id="nospk" name="nospk" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text";" value="0" style="width:90%" onblur="resetNorma()"></td>
							<td style="padding-left:2%;"><?php echo $_SESSION['lang']['khl']; ?></td>
							<td><input id="khl" name="khl" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text";" value="0" style="width:90%" onblur="resetNorma()"></td>
						</tr>
					</body>
					</table>
				</fieldset>
			</div>
			<div style="float:left; width:50%;margin-top:10px;">
				<fieldset>
					<legend id="title_Form"><b><?php echo $_SESSION['lang']['estimasiproduksidanangkutan']; ?></b></legend>
					<table border="0" cellspacing="0" cellpadding="1" style="width:100%;">
					<tbody>
						<tr>
							<td style="width:22%;">
								<label for="Mandor">Mandor/Pengawas</label>
							</td>
							<td colspan="3">
								<select id="mandor" name="mandor" style="width:90%">
								<?php foreach($optmandor as $k => $v ){ ?>
										<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
								<?php } ?>
								</select>
								<img id="blok_find" onclick="z.elSearch('mandor',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;">
							</td>
						</tr>
						<tr>
							<td>Unit Angkut</td>
							<td colspan="3"><input id="unitangkut" name="unitangkut" class="myinputtextnumber" onkeypress="return angka_doang(event)" type="text";" value="0" ></td>
						</tr>
						<tr id="khususpemanen">
							<td><?php echo $_SESSION['lang']['janjang']; ?> TBS</td>
							<td style="">
							<input id="janjangtbs" name="janjangtbs" class="myinputtextnumber" onchange="getSlave('findbjr',this);" onkeypress="return angka_doang(event)" type="text";" value="0"> 
							<span id="tbskg"></span></td>
							<td></td>
							<td></td>
						</tr>
					</body>
					</table>
				</fieldset>
			</div>
		
		<div style="clear:both;">
		<br/>
			<input type="submit" class="mybutton" value="Submit">
			<input type="reset" class="mybutton" value="Clear">
		</div>
	</div>
	</form>
	<?php	
CLOSE_BOX();
OPEN_BOX('','<span class=judul></span>');
?>
	<fieldset>
		<table class="sortable" cellspacing="1" style="width:100%" border="0">
			<thead>
				<tr>
					<th rowspan="4">No.</th>
					<th rowspan="4"><?php echo $_SESSION['lang']['kegiatan']; ?></th>
					<th colspan="5" ><?php echo $_SESSION['lang']['blok']; ?></th>
					<th rowspan="4">Target (Ha/Pkk)</th>
					<th rowspan="4">Rp/Satuan</th>
					<th colspan="8">Kebutuhan</th>
					<th colspan="3">Estimasi Produksi & Angkutan</th>
					<th>Pengawas / Mandor</th>
					<th rowspan="4">KERJA<br>
									/<br>
									KONTAN</th>
					<th rowspan="4">#</th>
				</tr>
				<tr>
					<th rowspan="3"><?php echo $_SESSION['lang']['kode']; ?></th>
					<th rowspan="3"><?php echo $_SESSION['lang']['luas']; ?></th>
					<th rowspan="3">SPH</th>
					<th rowspan="3">TT</th>
					<th rowspan="3"><?php echo $_SESSION['lang']['rotasi']; ?></th>
					<th colspan="4">HK</th>
					<th colspan="4"><?php echo $_SESSION['lang']['material']; ?></th>
					<th rowspan="3"><?php echo $_SESSION['lang']['janjang']; ?> TBS</th>
					<th rowspan="3"><?php echo $_SESSION['lang']['kg']; ?></th>
					<th rowspan="3">Keb Unit Angkutan (Truk)</th>
					<th rowspan="3"><?php echo $_SESSION['lang']['nama']; ?></th>
				</tr>
				<tr>
					<th rowspan="2">HK/Satuan</th>
					<th colspan="2">Jumlah Hk</th>
					<th>Kontrak/Borong</th>
					<th rowspan="2"><?php echo $_SESSION['lang']['jenis']; ?></th>
					<th rowspan="2"><?php echo $_SESSION['lang']['norma']; ?></th>
					<th rowspan="2">Sat</th>
					<th rowspan="2">Jml</th>
				</tr>
				<tr>
					<th rowspan="2">PB</th>
					<th rowspan="2">KHL</th>
					<th rowspan="2">No SPK</th>
				</tr>
			</thead>
			<tbody id="datadetail">
				<tr><td colspan="20" align="center">No Data</td></tr>
			</tbody>
		</table>		
	</fieldset>
<?php
CLOSE_BOX();
	break;
	case 'listprestasi':
		$html = "";
		if($tanggal != "" or $asisten != "" or $divisi != "" ){
			$where	= "where tanggal='".date("Y-m-d",strtotime($_POST['tanggal']))."' and divisi='".$divisi."' and asisten='".$asisten."'";
			$parnt = "select notransaksi from " . $dbname . ".kebun_rkhht ".$where." limit 1";
			$pr = fetchData($parnt);
			if(count($pr) > 0){
				$str = "select a.*,b.namakegiatan,c.namakaryawan,d.tahuntanam,d.luasareaproduktif,d.jumlahpokok,d.statusblok from " . $dbname . ".kebun_rkh_dt a
				left join setup_kegiatan b on b.kodekegiatan = a.kodekegiatan
				left join datakaryawan c on c.karyawanid = a.mandor
				left join setup_blok d on d.kodeorg = a.kodeblok
				where a.notransaksi = '".$pr[0]['notransaksi']."' order by a.nourut";
				
				$r = fetchData($str);
				$prestasi = $r;
				if(count($r) > 0){
					$mat = "select a.*,b.namabarang,b.satuan from " . $dbname . ".kebun_rkh_dtmaterial a
					left join log_5masterbarang b on b.kodebarang = a.kodebarang
					where notransaksi = '".$pr[0]['notransaksi']."'";
					$matr = fetchData($mat);
					$matr;
					$material = array();
					for($i=0; $i<count($matr); $i++){
						$material[$matr[$i]['nourut']][] = $matr[$i];
						// Bentuk $material[]
					}
				}
				
				for($i=0; $i<count($prestasi); $i++){
					$matinpres = array();
					$rowmat = "";
					if(isset($material[$prestasi[$i]['nourut']])){
						$matinpres = $material[$prestasi[$i]['nourut']];
						$rowmat = 'rowspan="'.count($matinpres).'"';
					}
					$html .= "<tr class=\"rowcontent\">";
					$html .= "<td $rowmat align=center>".$prestasi[$i]['nourut']."</td>";
					$html .= "<td $rowmat>".$prestasi[$i]['namakegiatan']."</td>";
					$html .= "<td $rowmat>".$prestasi[$i]['kodeblok']."</td>";
					$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['luasareaproduktif'],2,".",",")."</td>";
					$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['jumlahpokok'] / $prestasi[$i]['luasareaproduktif'],0,".",",")."</td>";
					$html .= "<td $rowmat>".$prestasi[$i]['tahuntanam']."</td>";
					$html .= "<td $rowmat>".$prestasi[$i]['rotasi']."</td>";
					$html .= "<td $rowmat align=right>".$prestasi[$i]['target']."</td>";
					$html .= "<td $rowmat align=right>".$prestasi[$i]['rpsatuan']."</td>";
					@$html .= "<td $rowmat align=right>".number_format(($prestasi[$i]['hk_pb']+$prestasi[$i]['hk_bor']+$prestasi[$i]['hk_khl'])/$prestasi[$i]['target'],2)."</td>";
					$html .= "<td $rowmat align=right>".$prestasi[$i]['hk_pb']."</td>";
					$html .= "<td $rowmat align=right>".$prestasi[$i]['hk_khl']."</td>";
					$html .= "<td $rowmat align=right>".$prestasi[$i]['hk_bor']."</td>";
					if(count($matinpres)>0){
						$html .= "<td>".$matinpres[0]['namabarang']."</td>";
						$html .= "<td align=right>".$matinpres[0]['jumlah']/$prestasi[$i]['target']."</td>";
						$html .= "<td>".$matinpres[0]['satuan']."</td>";
						$html .= "<td align=right>".$matinpres[0]['jumlah']."</td>";
					}else{
						$html .= "<td></td><td></td><td></td><td></td>";
					}
					$html .= "<td $rowmat align=right>".$prestasi[$i]['jmlh_tbs']."</td>";
					$html .= "<td $rowmat>Kg</td>";
					$html .= "<td $rowmat align=right>".$prestasi[$i]['angkutan']."</td>";
					$html .= "<td $rowmat>".$prestasi[$i]['namakaryawan']."</td>";
					$html .= "<td $rowmat>".$prestasi[$i]['kontan']."</td>";
					$html .= '<td '.$rowmat.'><img src="images/application/application_edit.png" class="resicon" title="Edit" onclick="editdata(\''.$pr[0]['notransaksi'].'\',\''.$prestasi[$i]['nourut'].'\',\''.$prestasi[$i]['kontan'].'\');"></td>';
					$html .= "</tr>";
					//material
					if(count($matinpres)>1){
						$html .= "<tr class=\"rowcontent\">";
						for($ii=1; $ii<count($matinpres); $ii++){
							$html .= "<td>".$matinpres[$ii]['namabarang']."</td>";
							$html .= "<td align=right>".$matinpres[$ii]['jumlah']/$prestasi[$i]['target']."</td>";
							$html .= "<td>".$matinpres[$ii]['satuan']."</td>";
							$html .= "<td align=right>".$matinpres[$ii]['jumlah']."</td>";
						}
						$html .= "</tr>";
					}
					//END:material
				}
				
			}else{
				$html .='<tr><td colspan="20" align="center">No Data</td></tr>';
			}
		}else{
			$html .='<tr><td colspan="20" align="center">No Data</td></tr>';
		}
		echo $html;
	break;
	case 'insertdata':
		$where	= "where ";
		if(isset($_POST)){
			
			if($tanggal == "" or $asisten == "" or $divisi == "" or $blok == "" or $kegiatan == "" or $mandor == ""){
				$result['err'] = "Data Belum Lengkap!";
				exit();
			}


			$sInsert="";
			//query untuk mencari nummber
			$where	.= "tanggal='".date("Y-m-d",strtotime($tanggal))."' and divisi='".$divisi."'";
			$num = "select notransaksi from " . $dbname . ".kebun_rkhht ".$where."";
			//query untuk mencari data exist
			$str = "select * from " . $dbname . ".kebun_rkhht ".$where." limit 1";
			$strPDO = fetchData($str);
			if(count($strPDO) > 0){
				if($strPDO[0]['asisten']!=$asisten)
				{
					echo 'Asisten berbeda dari data yang di input sebelumnya';
					exit();
				}
				$createNotrans = $strPDO[0]['notransaksi'];
				$sInsert.="UPDATE ".$dbname.".kebun_rkhht set
				updateby 	= '".$_SESSION['standard']['userid']."',
				updatetime  = '".date("Y-m-d H:i:s")."' 
				where notransaksi = '".$createNotrans."';";
			}else{
				//find number
				$numPDO = fetchData($num);
				$num = getMaxNotransaksi($numPDO);
//---------------//Exec header
				if($num != ""){
					$createNotrans = date("Ymd",strtotime($tanggal))."/".$divisi."/".str_pad($num, 2, "0", STR_PAD_LEFT);// 20170120/SKLE01/000000012 varChar (25) 
					$sInsert.="insert into ".$dbname.".kebun_rkhht (notransaksi,asisten,tanggal,divisi,createby)
					value (
					'".$createNotrans."',
					'".$asisten."',
					'".date("Y-m-d",strtotime($tanggal))."',
					'".$divisi."',
					'".$_SESSION['standard']['userid']."'
					);";
				}
			}
			
//-------------//exec prestasi 
			$nourut = 1;
			$find = "select MAX(nourut) as maxnum from " . $dbname . ".kebun_rkh_dt where notransaksi='".$createNotrans."'";
			$prestasi = fetchData($find);
			if(count($prestasi) > 0 ){
				$nourut = $prestasi[0]['maxnum']+1;
			}
			$where2	= "where notransaksi='".$createNotrans."' and kodeblok='".$blok."' and kodekegiatan='".$kegiatan."'
						and kontan='".$kontan."'";
			$strprestasi = "select * from " . $dbname . ".kebun_rkh_dt ".$where2." limit 1";
			$presPDO = fetchData($strprestasi);
			if(count($presPDO) > 0){
				//jika sudah ada maka Update
				$nourut = $presPDO[0]['nourut']; // jika update nmor urut pakai yang sudah ada
				$sInsert.="UPDATE ".$dbname.".kebun_rkh_dt set
					mandor = '".$mandor."',
					statusblok = '".$statusblok."',
					rotasi = '".$rotasi."',
					target = '".$target."',
					hk_pb = '".$pb."',
					hk_khl = '".$khl."',
					hk_bor = '".$bor."',
					jmlh_tbs = '".$janjangtbs."',
					angkutan = '".$unitangkut."',
					rpsatuan = '".$rpsatuan."'
					".$where2." ;";
			}else{
				$sInsert.="insert into ".$dbname.".kebun_rkh_dt (
					notransaksi,
					nourut,
					mandor,
					kodeblok,
					statusblok,
					kodekegiatan,
					rotasi,
					target,
					hk_pb,
					hk_khl,
					hk_bor,
					jmlh_tbs,
					angkutan,
					kontan,
					rpsatuan
					)value (
					'".$createNotrans."',
					'".$nourut."',
					'".$mandor."',
					'".$blok."',
					'".$statusblok."',
					'".$kegiatan."',
					'".$rotasi."',
					'".$target."',
					'".$pb."',
					'".$khl."',
					'".$bor."',
					'".$janjangtbs."',
					'".$unitangkut."',
					'".$kontan."',
					'".$rpsatuan."'
				);";
			}
//-------------//exec MATERIAL
			
			$material = array();
			if(isset($_POST['material'])){
				$material = $_POST['material'];
			}
			$jumlah = array();
			if(isset($_POST['jumlahmaterial'])){
				$jumlahmaterial = $_POST['jumlahmaterial'];
			}
			
			//Delete terlebih dahulu jika data ada
			$where3	= "where notransaksi='".$createNotrans."' and nourut='".$nourut."'";
			$strmaterial = "select kodebarang from " . $dbname . ".kebun_rkh_dtmaterial ".$where3;
			$matPDO = fetchData($strmaterial);
			if(count($matPDO) > 0){
				$delete = "delete from kebun_rkh_dtmaterial ".$where3;
				try{$owlPDO->exec($delete); }
				catch (PDOException $e) {
					$result['err'] = "Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			
			if(count($material) > 0){
				$sInsert .="insert into ".$dbname.".kebun_rkh_dtmaterial (
					notransaksi,
					nourut,
					kodebarang,
					jumlah) VALUE";
				$sInsert .= "('".$createNotrans."','".$nourut."','".$material[0]."','".$jumlahmaterial[0]."')";
				for($i=1; $i<count($material); $i++){
					$sInsert .= ",('".$createNotrans."','".$nourut."','".$material[$i]."','".$jumlahmaterial[$i]."')";
				}
			}
			//Execution All Data Insert
			try{$owlPDO->exec($sInsert); }
			catch (PDOException $e) {
				$result['err'] = "Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		echo json_encode($result);
	break;
	case 'findblok':
		$afdeling = "";
		if(isset($_POST['value'])){
			$afdeling = $_POST['value'];
		}
		$where="kodeorg like '".$afdeling."%'";
		$opt = makeOption($dbname,'setup_blok','kodeorg',$where,null,true);
		foreach($opt as $k => $v ){
			echo '<option value="'.$k.'">'.$k.'</option>';
		}
	break;
	case 'findblokinfo':
		$blok = "";
		if(isset($_POST['value'])){
			$blok = $_POST['value'];
		}
		$results['blok'] = array();
		$results['kegiatan'] = "";
		$data = array();
		$str = "select kodeorg,tahuntanam,luasareaproduktif,luasareanonproduktif,jumlahpokok,statusblok from " . $dbname . ".setup_blok where kodeorg = '".$blok."' limit 1";
		$strSup =$owlPDO->query($str);
		$blok= $strSup->fetchAll(PDO::FETCH_ASSOC );
		if(count($blok) > 0){
			$statusblok = $blok[0]['statusblok'];
			//untuk select kegiatan
			if($statusblok=='TM'){
			$wherekeg="kelompok in ('".$statusblok."','PNN')";
			}
			else
			{
			$wherekeg="kelompok = '".$statusblok."'";
			}
			$optkegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$wherekeg,null,true);
			$keg = "";
			foreach($optkegiatan as $k => $v ){
				$keg .='<option value="'.$k.'">'.$v.'</option>';
			}
			$results['blok'] = $blok[0];
			$results['kegiatan'] = $keg;
		}
		echo json_encode($results);
	break;
	case 'findbjr':
		$jjg = 0 ;
		$tanggal=$result=$blok = "";
		if(isset($_POST['value'])){
			$jjg = $_POST['value'];
		}
		if(isset($_POST['tanggal'])){
			$tanggal = $_POST['tanggal'];
		}
		if(isset($_POST['blok'])){
			$blok = $_POST['blok'];
		}
		$data = array();
		$str = "select * from " . $dbname . ".kebun_5bjr where kodeorg = '".$blok."' and  periode = '".date('Y-m',strtotime($tanggal))."' limit 1";
		$strSup =$owlPDO->query($str);
		$r= $strSup->fetchAll(PDO::FETCH_ASSOC );
		if($blok != "" and $tanggal != ""){
			if(count($r) > 0){
				$result = $jjg*$r[0]['bjr']." Kg";
			}else{
				$result = "Tidak ada data di Blok ".$blok." & periode ".date('Y-m',strtotime($tanggal));
			}
		}else{
			$result = "Blok / Tanggal Kosong! ".$tanggal;
		}
		echo $result;
	break;
	case 'findsatuan':
		$kodekegiatan = "";
		if(isset($_POST['value'])){
			$kodekegiatan = $_POST['value'];
		}
		$data = array();
		$str = "select satuan from " . $dbname . ".setup_kegiatan where kodekegiatan = '".$kodekegiatan."' limit 1";
		$strSup =$owlPDO->query($str);
		$results= $strSup->fetchAll(PDO::FETCH_ASSOC );
		$satuan = $results[0]['satuan'];
		echo $satuan;
	break;
	case 'findsatuanmaterial':
		$material = "";
		if(isset($_POST['value'])){
			$material = $_POST['value'];
		}
		$data = array();
		$str = "select satuan from " . $dbname . ".log_5masterbarang where kodebarang = '".$material."' limit 1";
		$strSup =$owlPDO->query($str);
		$results= $strSup->fetchAll(PDO::FETCH_ASSOC );
		if(count($results) > 0){
			echo $results[0]['satuan'];
		}else{
			echo "?";
		}
	break;
	case 'findbarang':
		$kegiatan = "";
		if(isset($_POST['value'])){
			$kegiatan = $_POST['value'];
		}
		$postKodebarang = array();
		if(isset($_POST['kodebarang'])){
			$postKodebarang = $_POST['kodebarang'];//array
		}
		$str = "select kelompok,kodeorg,satuan from " . $dbname . ".setup_kegiatan where kodekegiatan = '".$kegiatan."' limit 1";
		$strSup =$owlPDO->query($str);
		$results = $strSup->fetchAll(PDO::FETCH_ASSOC );
		$data['satuan'] = "";
		$data['barang'] = "";
		if(count($results) > 0){
			$kelompok 	= $results[0]['kelompok'];
			$kodeorg 	= $results[0]['kodeorg'];
			$satuan 	= $results[0]['satuan'];
			
			$str2 = "select kodebarang from " . $dbname . ".setup_kegiatannorma where kodekegiatan = '".$kegiatan."' and kelompok = '".$kelompok."'";
			$strkodebarang =$owlPDO->query($str2);
			$resultsNorma = $strkodebarang->fetchAll(PDO::FETCH_ASSOC );
			
			$kodebarang = "";
			if(count($resultsNorma) > 0){
				$kodebarang .= "'".$resultsNorma[0]['kodebarang']."'";
				for($i=1; $i<count($resultsNorma); $i++){
					$kodebarang .= ",'".$resultsNorma[$i]['kodebarang']."'";
				}
				$barang = "";
				$where="kodebarang in (".$kodebarang.")";
				$optmaterial = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$where,null,true);
				foreach($optmaterial as $k => $v ){
					if (in_array($k, $postKodebarang)) {
						$barang .= '<option value="'.$k.'" style="display:none;">'.$v.'</option>';
					}else{
						$barang .= '<option value="'.$k.'">'.$v.'</option>';
					}
				}
				$data['barang'] = $barang;
			}
			$data['satuan'] = "(".$satuan.")";
		}
		echo json_encode($data);
	break;
	case 'finddataprestasi':
		$notransaksi = "";
		$nourut = "";
		if(isset($_POST['notransaksi'])){
			$notransaksi = $_POST['notransaksi'];
		}
		if(isset($_POST['nourut'])){
			$nourut = $_POST['nourut'];
		}
		$data = array();
		$r	  = array();
		$str = "select * from " . $dbname . ".kebun_rkh_dt where notransaksi = '".$notransaksi."' and nourut = '".$nourut."' and kontan='".$kontan."' limit 1";
		$strrkh =$owlPDO->query($str);
		$r = $strrkh->fetchAll(PDO::FETCH_ASSOC );
		$target = $r[0]['target'];
		$rmat = array();
		$str2 = "select a.*,b.namabarang,b.satuan,(a.jumlah/".$target.") as norma from " . $dbname . ".kebun_rkh_dtmaterial a
		left join log_5masterbarang b on a.kodebarang = b.kodebarang where a.notransaksi = '".$notransaksi."' and a.nourut = '".$nourut."'";
		$strrkh2 =$owlPDO->query($str2);
		$rmat = $strrkh2->fetchAll(PDO::FETCH_ASSOC );

		if(count($r) > 0){
			$result['data']['prestasi'] = $r;
			$result['data']['material'] = $rmat;
		}else{
			$result['err'] = "Data Tidak Ada";
		}
		echo json_encode($result);
	break;
	
	
	
	
	#=buat ajukan
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='RKH' and a.level='1' and a.kodeunit='".$unit."'  
				  order by b.namakaryawan asc";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:99%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
	
	
	case'ajukan':
	
		try {
		$owlPDO->beginTransaction();
			if($kepada=='' or $notransaksi==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			//update flag menjadi 1
			$str = "update " . $dbname . ".kebun_rkhht set posting='1' where notransaksi = '" . $notransaksi . "'";
			$owlPDO->exec($str);
			//insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','".$notransaksi."','RKH','1','" . $kepada."','0','','','')";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	case 'ajukanrkh':

		$notransaksi = "";
		if(isset($_POST['notransaksi'])){
			$notransaksi = $_POST['notransaksi'];
		}
		$sInsert ="UPDATE ".$dbname.".kebun_rkhht set
		status 		= '1',
		updateby 	= '".$_SESSION['standard']['userid']."',
		updatetime  = '".date("Y-m-d H:i:s")."' 
		where notransaksi = '".$notransaksi."';";
			// exit("Error:A".$sInsert);
		try{
			$owlPDO->exec($sInsert);
			$result['caption'] = "Posted";
		}
		catch (PDOException $e) {
			$result['err'] = "Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		echo json_encode($result);
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	case 'deleteall':
	$sqldel="delete from ".$dbname.".kebun_rkhht where notransaksi='".$nomortransaksi."' ";
	try{
			$owlPDO->exec($sqldel);
			echo "Berhasil Menghapus Data";
		}
		catch (PDOException $e) {
			echo "Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	case 'view':
		//require('lib/fpdf.php');
		//require('lib/htmlparser.inc');
		//require('lib/htmltofpdf.php');
		$html = "";
		$for = checkPostGet('for', '');
		$notransaksi = checkPostGet('notransaksi', '');
		if($notransaksi != ""){
			$str = "select a.*,b.namakegiatan,c.namakaryawan,d.tahuntanam,d.luasareaproduktif,d.jumlahpokok,d.statusblok from " . $dbname . ".kebun_rkh_dt a
			left join setup_kegiatan b on b.kodekegiatan = a.kodekegiatan
			left join datakaryawan c on c.karyawanid = a.mandor
			left join setup_blok d on d.kodeorg = a.kodeblok
			where a.notransaksi = '".$notransaksi."' order by a.nourut";
			
			$r = fetchData($str);
			$prestasi = $r;
			if(count($r) > 0){
				$mat = "select a.*,b.namabarang,b.satuan from " . $dbname . ".kebun_rkh_dtmaterial a
				left join log_5masterbarang b on b.kodebarang = a.kodebarang
				where notransaksi = '".$notransaksi."'";
				$matr = fetchData($mat);
				$matr;
				$material = array();
				for($i=0; $i<count($matr); $i++){
					$material[$matr[$i]['nourut']][] = $matr[$i];
					// Bentuk $material[]
				}
			}
			
			for($i=0; $i<count($prestasi); $i++){
				$matinpres = array();
				$rowmat = "";
				if(isset($material[$prestasi[$i]['nourut']])){
					$matinpres = $material[$prestasi[$i]['nourut']];
					$rowmat = 'rowspan="'.count($matinpres).'"';
				}
				$html .= "<tr class=\"rowcontent\">";
				$html .= "<td $rowmat align=right>".$prestasi[$i]['nourut']."</td>";
				$html .= "<td $rowmat >".$prestasi[$i]['namakegiatan']."</td>";
				$html .= "<td $rowmat>".$prestasi[$i]['kodeblok']."</td>";
				$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['luasareaproduktif'],2,".",",")."</td>";
				$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['luasareaproduktif']*$prestasi[$i]['jumlahpokok'] ,0,".",",")."</td>";
				$html .= "<td $rowmat>".$prestasi[$i]['tahuntanam']."</td>";
				$html .= "<td $rowmat align=right>".$prestasi[$i]['rotasi']."</td>";
				$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['target'],2)."</td>";
				$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['rpsatuan'],2)."</td>";
				$html .= "<td $rowmat align=right>".number_format(($prestasi[$i]['hk_pb']+$prestasi[$i]['hk_bor']+$prestasi[$i]['hk_khl'])/$prestasi[$i]['target'],2)."</td>";
				$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['hk_pb'],2)."</td>";
				$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['hk_khl'],2)."</td>";
				$html .= "<td $rowmat align=right>".number_format($prestasi[$i]['hk_bor'],2)."</td>";
				if(count($matinpres)>0){
					$html .= "<td>".$matinpres[0]['namabarang']."</td>";
					$html .= "<td align=right>".$matinpres[0]['jumlah']/$prestasi[$i]['target']."</td>";
					$html .= "<td>".$matinpres[0]['satuan']."</td>";
					$html .= "<td align=right>".$matinpres[0]['jumlah']."</td>";
				}else{
					$html .= "<td></td><td></td><td></td><td></td>";
				}
				$html .= "<td $rowmat align=right>".$prestasi[$i]['jmlh_tbs']."</td>";
				$html .= "<td $rowmat>Kg</td>";
				$html .= "<td $rowmat align=right>".$prestasi[$i]['angkutan']."</td>";
				$html .= "<td $rowmat>".$prestasi[$i]['namakaryawan']."</td>";
				$html .= "<td $rowmat>".$prestasi[$i]['kontan']."</td>";
				$html .= "</tr>";
				//material
				if(count($matinpres)>1){
					$html .= "<tr class=\"rowcontent\"> ";
					for($ii=1; $ii<count($matinpres); $ii++){
						$html .= "<td>".$matinpres[$ii]['namabarang']."</td>";
						$html .= "<td align=right>".$matinpres[$ii]['jumlah']/$prestasi[$i]['target']."</td>";
						$html .= "<td>".$matinpres[$ii]['satuan']."</td>";
						$html .= "<td align=right>".$matinpres[$ii]['jumlah']."</td>";
					}
					$html .= "</tr>";
				}
				//END:material
			}
			
		}else{
			$html .='<tr><td colspan="20" align="center">No Data</td></tr>';
		}
		if($for == 'excel'){
			$tableHtml = '<table class=sortable cellspacing=1 border=1>';
		}else{
			$tableHtml = '<table class=sortable cellspacing=1 border=0>';
		}
		$tableHtml.='<thead>
						<tr class=rowheader>
							<th rowspan="4">No.</th>
							<th rowspan="4">'.$_SESSION['lang']['kegiatan'].'</th>
							<th colspan="5">'. $_SESSION['lang']['blok'].'</th>
							<th rowspan="4">Target (Ha/Pkk)</th>
							<th rowspan="4">Rp/Satuan</th>
							<th colspan="8">Kebutuhan</th>
							<th colspan="3">Estimasi Produksi & Angkutan</th>
							<th>Pengawas / Mandor</th>
							<th rowspan="4">Kerja / Kontan</th>
						</tr>
						<tr class=rowheader>
							<th rowspan="3">'.$_SESSION['lang']['kode'].'</th>
							<th rowspan="3">'.$_SESSION['lang']['luas'].'</th>
							<th rowspan="3">SPH</th>
							<th rowspan="3">TT</th>
							<th rowspan="3">'.$_SESSION['lang']['rotasi'].'</th>
							<th colspan="4">HK</th>
							<th colspan="4">'.$_SESSION['lang']['material'].'</th>
							<th rowspan="3">'.$_SESSION['lang']['janjang'].' TBS</th>
							<th rowspan="3">'.$_SESSION['lang']['kg'].'</th>
							<th rowspan="3">Keb Unit Angkutan (Truk)</th>
							<th rowspan="3">'.$_SESSION['lang']['nama'].'</th>
						</tr>
						<tr class=rowheader>
							<th rowspan="2">HK/Satuan</th>
							<th colspan="2">Jumlah Hk</th>
							<th>Kontrak/Borong</th>
							<th rowspan="2">'.$_SESSION['lang']['jenis'].'</th>
							<th rowspan="2">'.$_SESSION['lang']['norma'].'</th>
							<th rowspan="2">Sat</th>
							<th rowspan="2">Jml</th>
						</tr>
						<tr class=rowheader>
							<th rowspan="1">PB</th>
							<th rowspan="1">KHL</th>
							<th rowspan="1">No SPK</th>
						</tr>
					</thead>
					<tbody>
						'.$html.'
					</tbody>
				</table>';
				
		$tableHtml .= "<br><br><br><br><br><br><br><br>
					<table>
					<tbody>
					<tr>";
		
		$tableHtml .= "<td></td>";
		$tableHtml .= "<td colspan='6' align='center' style='border-top:1px solid #000000;'>Askep</td>";
		$tableHtml .= "<td></td>";
		$tableHtml .= "<td colspan='6' align='center' style='border-top:1px solid #000000;'>Manager Kebun</td>";
					
		$tableHtml .= "</tr></tbody>
					</table>";
		//$tglSkrg = date("Ymd");
		if($for == 'excel'){
			$nop_ = "daftar_RKH";
			if (strlen($tableHtml) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $tableHtml)) {
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
		}else{
			echo $tableHtml;
		}
	break;
}

