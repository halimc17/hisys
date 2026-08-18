<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$kodeorg   =checkPostGet('kodeorg','');
$stts      =checkPostGet('stts','');
$nilai     =checkPostGet('nilai','0');
$method    =checkPostGet('method','');
$divisi    =checkPostGet('divisi','');
$tipetrans =checkPostGet('tipetrans','');
$harilibur =checkPostGet('harilibur','');
$tglberlaku=tanggalsystemn(checkPostGet('tglberlaku',''));
if(count($_POST)>0){
	$param = $_POST;
}else{
	$param = $_GET;
}

$arrtipe=array(
	'bkm' =>'Buku Kegiatan Mandor Rawat',
	'pnn' =>'Buku Kegiatan Mandor Panen',
	'rpnn'=>'Rekap Panen per Blok',
	'spb' =>'Surat Pengantar Buah [SPB]',
	'trk' =>'Traksi Kegiatan',
	'ws'  =>'Traksi Bengkel / Service',
	'log' =>'Pengeluaran Barang di Gudang',
	'gr'  =>'Penerimaan Barang dari Supplier',
	'kb'  =>'Kas dan Bank',
	'ksr' =>'Tanggal Bayar Kasir',
	'lbr' =>'SDM Lembur',
	
	'bkmpost' =>'Posting Buku Kegiatan Mandor Rawat',
	'pnnpost' =>'Posting Buku Kegiatan Mandor Panen',
	'rpnnpost'=>'Posting Rekap Panen per Blok',
	'spbpost' =>'Surat Pengantar Buah [SPB]',
	'trkpost' =>'Posting Traksi Kegiatan',
	'wspost'  =>'Posting Traksi Bengkel / Service',
	'logpost' =>'Posting Pengeluaran Barang di Gudang',
	'grpost'  =>'Posting Penerimaan Barang dari Supplier'
);

$arrlbr =array('0'=>'Abaikan hari libur (HKE)','1'=>'Hitung hari libur');
$arrsts =array('1'=>'Aktif','0'=>'Non Aktif');

// echo"<pre>";		
// print_r($param);
// exit("error");

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


switch($method){
	case'addnew':
		$optKebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$divisi  ="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKebun  ="select * from ".$dbname.".organisasi where length(kodeorganisasi) in ('4','6') order by induk, kodeorganisasi";
		$res = fetchdata($sKebun);
		foreach($res as $bar){
			$d=$bar['induk'];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				if(strlen($bar['kodeorganisasi'])=='6'){
					$divisi.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
				}
				if(strlen($bar['kodeorganisasi'])=='4'){
					$optKebun.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
				}
			}
			if(strlen($bar['kodeorganisasi'])=='4'){
				$sel="";
				if($param['kodeorg']==$bar['kodeorganisasi']){
					$sel="selected";					
				}
				$optKebun.="<option value=" . $bar['kodeorganisasi'] . " ".$sel.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			}
			if(strlen($bar['kodeorganisasi'])=='6'){			
				$sel="";
				if($param['divisi']==$bar['kodeorganisasi']){
					$sel="selected";					
				}
				$divisi.="<option value=" . $bar['kodeorganisasi'] . " ".$sel.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			}

			$n=$d;
			if($d!=$n){
				if(strlen($bar['kodeorganisasi'])=='6'){			
					$divisi.="</optgroup>";
				}
				if(strlen($bar['kodeorganisasi'])=='4'){			
					$divisi.="</optgroup>";
				}
			}
		}

		$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
		foreach($arrsts as $key => $val){
			$optsts.="<option value='".$key."'>".$val."</option>";
		}
		$arrlbr=array('0'=>'Abaikan hari libur (HKE)','1'=>'Hitung hari libur');
		foreach($arrlbr as $key => $val){
			$optlibur.="<option value='".$key."'>".$val."</option>";
		}


		foreach($arrtipe as $key => $val){
			if(substr($key,-4)=='post'){				
				$d='Posting';
			}else{
				$d='Transaksi';
			}
			if($d!=$n){			
				$opttipe.="<optgroup label='".$d."'>";
			}
			$opttipe.="<option value='".$key."'>".$val." (".$key.")</option>";
			$n=$d;
			if($d!=$n){			
				$opttipe.="</optgroup>";
			}
		}
		$str = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$admin=true;
		}else{
			$admin=false;
			exit("error : Anda tidak memiliki otorisasi.");
		}

		$tab.="
			<table cellspacing='1' border=0>
			<tr>
				<td>".$_SESSION['lang']['tipetransaksi']."</td>
				<td style=width:50px></td>
				<td colspan=4>
					<select class='select2' style='width:350px;' id='tipetrans' name='tipetrans'>
						".$opttipe ."
					</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kodeorg']."</td>
				<td></td>
				<td colspan=4>
					<select class='select2' style=width:350px onchange='getdivisi(this.value);' id='kd_org' name='kd_org'>
						".$optKebun ."
					</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['divisi']."</td>
				<td></td>
				<td colspan=4>
					<select class='select2' style=width:350px id='divisi' name='divisi'>
						".$divisi ."
					</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['hari'] ."</td>
				<td></td>
				<td colspan=4><input style='width:345px;height:30px;font-size:14px;' type=text id=nilai class=myinputtextnumber onKeyPress='return angka_doang(event);' value=0></td>
			
			</tr>
			<tr>
				<td>".$_SESSION['lang']['harilibur']."</td>
				<td></td>
				<td colspan=4>
					<select class='select2' style=width:350px id='harilibur' name='harilibur'>
						".$optlibur ."
					</select>
				</td>
			</tr>
			<tr>	
				<td>".$_SESSION['lang']['tanggalberlaku']."</td>
				<td></td>
				<td><input type='text' readonly=readonly class='myinputtext' id='tglberlaku' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:150px;height:30px;font-size:14px;'> 
				</td>
			
			
				<td>".$_SESSION['lang']['status']."</td>
				<td></td>
				<td><select class='select2' style=width:140px id='status' name='status'>
						".$optsts ."
					</select></td>
			</tr>
			<tr>
				<td><td><td align=center colspan=3>
					<fieldset>
						<input type=hidden id=idht  />
						<input type=hidden value=insert id=method>
						<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=btldendapanen()>".$_SESSION['lang']['cancel']."</button>
					</fieldset>	
				</td>
				<td align=center>
					<fieldset><button class=mybutton onclick=excel()>".$_SESSION['lang']['excel']."</button></fieldset>
				</td>
			</tr>
		</table>
		";
		
		echo $tab;
	break;
	case'excel':
		$tab.="<table class='sortable' cellspacing='1' cellpadding='5' border='1' width=100%>
		<thead>
			<tr class=rowheader>
				<th colspan=6 style='text-align:center;'>".$_SESSION['lang']['default']."</th>
				<th colspan=8 style='text-align:center;'>".$_SESSION['lang']['perubahan']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['kebun']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['divisi']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['tipetransaksi']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['hari']." + ?</th> 
				<th  style='text-align:center;'>".$_SESSION['lang']['harilibur']."</th> 
				<th width=60px>".$_SESSION['lang']['hari']." + ?</th>
				<th>".$_SESSION['lang']['harilibur']."</th>
				<th width=80px>".$_SESSION['lang']['mulai']."</th>
				<th width=80px>".$_SESSION['lang']['sampai']."</th>
				<th>".$_SESSION['lang']['keterangan']."</th>
				<th>".$_SESSION['lang']['status']."</th>
				<th>".$_SESSION['lang']['persetujuan']."</th>
				<th>".$_SESSION['lang']['updateby']."</th>
			</tr>
		</thead>
		<tbody >";
		$arrapproval=array("0"=>"Belum diajukan","1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['ditolak'],'9'=>'Proses Persetujuan');
		
		$str= "select b.*, b.status as statusdt, a.jenistransaksi, a.kodeorg, a.updateby as updatebyht,a.harilibur as hariliburht, a.jumlahhari as jumlahhariht, a.status as statusht, a.divisi as divisiht from ".$dbname.".setup_validasiinput_ht a left join ".$dbname.".setup_validasiinput_dt b on a.id=b.idht where 1=1 order by a.divisi";
		$bar= fetchdata($str);
		$n=0;
		foreach($bar as $res){
			$no+=1;
			if($res['divisiht']==''){
				$divisi=$_SESSION['lang']['all'];
			}else{
				$divisi=$res['divisiht']." - ".$nmorg[$res['divisiht']];
			}
			
			$tab.="<tr class=rowcontent style='vertical-align:top;'>
				<td ".$rowspan." style='text-align:center;'>".$no."</td>
				<td ".$rowspan.">".$res['kodeorg']." - ".$nmorg[$res['kodeorg']]."</td>
				<td ".$rowspan.">".$divisi."</td>
				<td ".$rowspan.">".$arrtipe[$res['jenistransaksi']]." (".$res['jenistransaksi'].")</td>
				<td ".$rowspan." style='text-align:center;'>H + ".$res['jumlahhariht']."</td>
				<td ".$rowspan.">".$arrlbr[$res['hariliburht']]."</td>
			";
			if($res['jumlahhari']!=""){				
				$tab.="<td style='text-align:center;'>H + ".$res['jumlahhari']."</td>";
			}else{
				$tab.="<td style='text-align:center;'></td>";
			}
			$tab.="<td>".$arrlbr[$res['harilibur']]."</td>
				<td align=center>".(tanggalnormal($res['berlakudari'])!='--'?tanggalnormal($res['berlakudari']):"")."</td>
				<td align=center>".(tanggalnormal($res['berlakusampai'])!='--'?tanggalnormal($res['berlakusampai']):"")."</td>
				<td>".$res['keterangan']."</td>
				<td style='text-align:center;'>".$arrapproval[$res['statusdt']]."</td>";
				if($res['status']!=0){						
					$tab.="<td style='text-align:center;cursor:pointer;color:blue;' onclick=gethistoriapproval('".$res['nopengajuan']."','event');>".$res['nopengajuan']."</td>";
				}else{
					$tab.="<td style='text-align:center;cursor:pointer;color:blue;' onclick=gethistoriapproval('".$res['nopengajuan']."','event');></td>";
				}
				
				$tab.="<td style='text-align:center;'>".getNamaKaryawan($res['updateby'])."</td>";
			
			$tab.="</tr>";			
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		
		$nop = "EoD.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("EoD", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
		
		// if($tipe=='excel'){	
		// }else{			
			// echo $tab;
		// }
		echo $tab;
	break;
	case'getdatapengajuan':
		$str = "select * from ".$dbname.".setup_validasiinput_dt where nopengajuan='".$param['nopengajuan']."'";
		$bar = fetchdata($str);
	
		$str = "select * from ".$dbname.".setup_validasiinput_ht where id='".$bar[0]['idht']."'";
		$bar = fetchdata($str);
		if($bar[0]['divisi']!=''){			
			$div=$nmorg[$bar[0]['divisi']];
		}else{
			$div=$_SESSION['lang']['all'];
		}
		
		$tab.="<table>";
		$tab.="<tr>
					<input id=tglmulaiold hidden>
					<input id=tglsampaiold hidden>
					<td>".$_SESSION['lang']['tipetransaksi']."</td><td>:</td><td>".$arrtipe[$bar[0]['jenistransaksi']]."</td>
				</tr>	
				<tr>	
					<td>".$_SESSION['lang']['kodeorg']."</td><td>:</td><td>".$nmorg[$bar[0]['kodeorg']]."</td>
				<tr>	
				</tr>	
					<td>".$_SESSION['lang']['divisi']."</td><td>:</td><td>".$div."</td>
				</tr>
				</tr>	
					<td>Jumlah Hari Default</td><td>:</td><td>H + <b>".$bar[0]['jumlahhari']."</b> ".$arrlbr[$bar[0]['harilibur']]."</td>
				</tr>
				</tr>	
					<td>".$_SESSION['lang']['tanggalberlaku']."</td><td>:</td><td>".tanggalnormal($bar[0]['berlaku'])."</td>
				</tr>
				";
		$tab.="</table>";

		$tab.="<table class='sortable' cellspacing='1' cellpadding='5' border='0'>";
		$tab.="<thead>
				<tr>
					<th>No</th>
					<th>".$_SESSION['lang']['hari']."</th>
					<th>".$_SESSION['lang']['harilibur']."</th>
					<th>".$_SESSION['lang']['mulai']."</th>
					<th>".$_SESSION['lang']['sampai']."</th>
					<th>".$_SESSION['lang']['keterangan']."</th>
					<th>".$_SESSION['lang']['status']."</th>
					<th>".$_SESSION['lang']['persetujuan']."</th>
					<th>".$_SESSION['lang']['updateby']."</th>
				</tr>
				</thead>";
		$arrapproval=array("0"=>"Belum diajukan","1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['ditolak'],'9'=>'Proses Persetujuan');
		
		$str = "select * from ".$dbname.".setup_validasiinput_dt where nopengajuan='".$param['nopengajuan']."' order by lastupdate  desc";
		$bar = fetchdata($str);
		foreach($bar as $res){
			$no++;
			$tab.="<tr class=rowcontent>
					<td style='text-align:center;'>".$no."</td>
					<td style='text-align:center;'>".$res['jumlahhari']."</td>
					<td>".$arrlbr[$res['harilibur']]."</td>
					<td align=center>".tanggalnormal($res['berlakudari'])."</td>
					<td align=center>".tanggalnormal($res['berlakusampai'])."</td>
					<td>".$res['keterangan']."</td>
					<td style='text-align:center;'>".$arrapproval[$res['status']]."</td>";
					if($res['status']!=0){						
						$tab.="<td style='text-align:center;cursor:pointer;color:blue;' onclick=gethistoriapproval('".$res['nopengajuan']."','event');>".$res['nopengajuan']."</td>";
					}else{
						$tab.="<td style='text-align:center;cursor:pointer;color:blue;' onclick=gethistoriapproval('".$res['nopengajuan']."','event');></td>";
					}
					
					$tab.="<td style='text-align:center;'>".getNamaKaryawan($res['updateby'])."</td>";
										
			$tab.="</tr>";
		}
		
		echo $tab;		
	break;
	case'adddetail':
		foreach($arrlbr as $key => $val){
			$optlibur.="<option value='".$key."'>".$val."</option>";
		}
		
		$str = "select * from ".$dbname.".setup_validasiinput_ht where id='".$param['id']."'";
		$bar = fetchdata($str);
		if($bar[0]['divisi']!=''){			
			$div=$nmorg[$bar[0]['divisi']];
		}else{
			$div=$_SESSION['lang']['all'];
		}
		
		$tab.="<table>";
		$tab.="<tr>
					<input id=tglmulaiold hidden>
					<input id=tglsampaiold hidden>
					<td>".$_SESSION['lang']['tipetransaksi']."</td><td>:</td><td>".$arrtipe[$bar[0]['jenistransaksi']]."</td>
				</tr>	
				<tr>	
					<td>".$_SESSION['lang']['kodeorg']."</td><td>:</td><td>".$nmorg[$bar[0]['kodeorg']]."</td>
				<tr>	
				</tr>	
					<td>".$_SESSION['lang']['divisi']."</td><td>:</td><td>".$div."</td>
				</tr>
				</tr>	
					<td>Jumlah Hari Default</td><td>:</td><td>H + <b>".$bar[0]['jumlahhari']."</b> ".$arrlbr[$bar[0]['harilibur']]."</td>
				</tr>
				</tr>	
					<td>".$_SESSION['lang']['tanggalberlaku']."</td><td>:</td><td>".tanggalnormal($bar[0]['berlaku'])."</td>
				</tr>
				";
		$tab.="</table>";

		$tab.="<table class='sortable' cellspacing='1' cellpadding='5' border='0' width='100%'>";
		$tab.="<thead>
				<tr>
					<th rowspan=2 width=20px>No</th>
					<th colspan=2>Tanggal Transaksi</th>
					<th rowspan=2 width=50px>".$_SESSION['lang']['hari']." + ?</th>
					<th rowspan=2 width=100px>".$_SESSION['lang']['harilibur']."</th>
					<th rowspan=2 width=200px>".$_SESSION['lang']['keterangan']."</th>
					<th rowspan=2>".$_SESSION['lang']['status']."</th>
					<th rowspan=2>".$_SESSION['lang']['persetujuan']."</th>
					<th rowspan=2>".$_SESSION['lang']['updateby']."</th>
					<th rowspan=2 colspan=3>".$_SESSION['lang']['action']."</th>
				</tr>
				<tr>
					<th rowspan=2 width=100px>".$_SESSION['lang']['mulai']."</th>
					<th rowspan=2 width=100px>".$_SESSION['lang']['sampai']."</th>
				</tr>
				</thead>";
				
		$tab.="<tbody>
				<tr class=rowcontent>
					<td align=center>#<input id=methoddt hidden value=insertdt></td>
					<td><input type='text' readonly=readonly class='myinputtext' id='tglmulai' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:100px;'  onblur=getjumlahhari(this.value)></td>
					<td><input type='text' readonly=readonly class='myinputtext' id='tglsampai' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:100px;'></td>
					<td align=center><input style='width:50px' id='nilaidt' class='myinputtextnumber' onkeypress='return angka_doang(event);' value='0'></td>
					<td><select style=\"width:150px;\" id=hariliburdt>" . $optlibur . "</select></td>
					<td align=center><input style='width:200px' id='ketdt' class='myinputtext'></td>
					<td></td><td></td><td></td>
					<td align=center colspan=3><img title='Simpan' class='zImgBtn' onclick=savedetail('".trim($param['id'])."') src='images/save.png' id=tombolsimpandt;></td>
					
				</tr>
				</tbody>";
		$tab.="<tbody id=listdatadt></tbody>";
		$tab.="</table>";
		
		echo $tab;
	break;
	case'getjumlahhari':		
		$jumlahhari = selisihari(tanggalsystemn($param['tanggal']),date('Y-m-d'));
		if($jumlahhari>=15){
			exit("error : Tidak bisa dilakukan karena tanggal terlalu jauh (lebih dari / sama dengan 15 hari), silahkan hubungi administrator untuk menonaktifkan validasi.");
		}
		
		echo $jumlahhari+3;
	break;
	case'loaddatadt':
	
		$str = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$admin=true;
		}else{
			$admin=false;
		}
	
		$arrapproval=array("0"=>"Belum diajukan","1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['ditolak'],'9'=>'Proses Persetujuan');
		
		$str = "select * from ".$dbname.".setup_validasiinput_dt where idht='".$param['id']."' order by lastupdate  desc";
		$bar = fetchdata($str);
		foreach($bar as $res){
			$no++;
			echo"<tr class=rowcontent style='vertical-align:top'>
					<td style='text-align:center;'>".$no."</td>
					<td align=center>".tanggalnormal($res['berlakudari'])."</td>
					<td align=center>".tanggalnormal($res['berlakusampai'])."</td>
					<td style='text-align:center;'>H + ".$res['jumlahhari']."</td>
					<td>".$arrlbr[$res['harilibur']]."</td>
					<td>".$res['keterangan']."</td>
					<td style='text-align:center;'>".$arrapproval[$res['status']]."</td>";
					if($res['status']!=0){						
						echo"<td style='text-align:center;cursor:pointer;color:blue;' onclick=gethistoriapproval('".$res['nopengajuan']."','event');>".$res['nopengajuan']."</td>";
					}else{
						echo"<td style='text-align:center;cursor:pointer;color:blue;' onclick=gethistoriapproval('".$res['nopengajuan']."','event');></td>";
					}
					
					echo"<td style='text-align:center;'>".getNamaKaryawan($res['updateby'])."<br>".$res['lastupdate']."</td>";
					if($res['status']=='0' or $res['status']=='2'){						
						echo"<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfielddt('".$res['jumlahhari']."','".$res['harilibur']."','".tanggalnormal($res['berlakudari'])."','".tanggalnormal($res['berlakusampai'])."','".$res['keterangan']."')\"></td>";
								
						echo"<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefielddt('".$res['kodeorg']."','".$res['divisi']."','".$res['jenistransaksi']."','".tanggalnormal($res['berlakudari'])."','".tanggalnormal($res['berlakusampai'])."','".$res['idht']."','".$res['nopengajuan']."')\"></td>";
						
						
						if($res['status']=='2'){
							echo"<td></td>";							
						}else{							
							echo"<td style='text-align:center;width:25px'><img src=images/skyblue/submit.jpg class=resicon height=30 title=Ajukan onclick=form_ajukan('".$res['nopengajuan']."','EOD".strtoupper($res['jenistransaksi'])."','".$res['kodeorg']."');></td>";
						}
					}else{
						
							
						if($admin==true){
								echo"<td colspan=3 align=center><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefielddt('".$res['kodeorg']."','".$res['divisi']."','".$res['jenistransaksi']."','".tanggalnormal($res['berlakudari'])."','".tanggalnormal($res['berlakusampai'])."','".$res['idht']."','".$res['nopengajuan']."')\"></td>";
						}else{
							echo"<td colspan=3></td>";
						}
					
					}
					
			echo"</tr>";
		}
		
		
	break;
	case 'insertdt':
		try {
		$owlPDO->beginTransaction();
		if($nilai=='0' || $param['tglmulai']=='' || $param['tglsampai']=='' || $harilibur=='' || $param['ketdt']==''){
			throw new PDOException("Semua field harus diisi.");
		}
		
		if($nilai>'15'){
			throw new PDOException("Hari tidak boleh lebih dari 15 hari.");
		}
		
		
		if(tanggalsystemn($param['tglmulai'])>tanggalsystemn($param['tglsampai'])){
			throw new PDOException("Tanggal mulai tidak boleh lebih besar dari tanggal sampai.");
		}
		
		$str = "select * from ".$dbname.".setup_validasiinput_ht where id='".$param['id']."'";
		$bar = fetchdata($str);
		$kodeorg  = $bar[0]['kodeorg'];
		$divisi   = $bar[0]['divisi'];
		$tipetrans= $bar[0]['jenistransaksi'];
		
		if($param['mode']=='updatedt'){
			$where=" and berlakudari!='".tanggalsystemn($param['tglmulai'])."' and berlakusampai!='".tanggalsystemn($param['tglsampai'])."'";
			
			$wheredel=" and berlakudari='".tanggalsystemn($param['tglmulaiold'])."' and berlakusampai='".tanggalsystemn($param['tglsampaiold'])."'";
			
			$strn = "select * from ".$dbname.".setup_validasiinput_dt where kodeorg='".$kodeorg."' and divisi ='".$divisi."' and jenistransaksi='".$tipetrans."' ".$wheredel."";
			$resn = fetchdata($strn);
			if(count($resn)>0){
				$strx="delete from ".$dbname.".approval where jenispersetujuan='EOD".strtoupper($tipetrans)."' and notransaksi ='".$resn[0]['nopengajuan']."'";
				$owlPDO->exec($strx); 
			}
			
			$str="delete from ".$dbname.".setup_validasiinput_dt where idht='".$param['id']."' and kodeorg='".$kodeorg."' and divisi ='".$divisi."' and jenistransaksi='".$tipetrans."' ".$wheredel."";
			$owlPDO->exec($str); 
		}
		
		
		$data=array();
		$str = "select * from ".$dbname.".setup_validasiinput_dt where kodeorg='".$kodeorg."' and divisi ='".$divisi."' and jenistransaksi='".$tipetrans."' ".$wheredel."";
		$res = fetchdata($str);
		foreach($res as $bar){
			$data[]=rangeTanggal($bar['berlakudari'],$bar['berlakusampai']);
		}
		$lsdate=array();
		foreach($data as $key => $v1){
			foreach($v1 as $date){
				$lsdate[$date]=$date;
			}
		}
		
		// echo"<pre>";
		// print_r($data);
		// exit("error");
		$tglada=array();
		$lsadd=rangeTanggal(tanggalsystemn($param['tglmulai']),tanggalsystemn($param['tglsampai']));
		foreach($lsadd as $tgladd){
			if($lsdate[$tgladd]){
				$tglada[tanggalnormal($tgladd)]=tanggalnormal($tgladd);
			}
		}
		
		if(count($tglada)>0){
			throw new PDOException("Tanggal : ".implode(", ",$tglada)."<br>sudah ada.");
		}
		
		$nopengajuan = "EOD/".strtoupper($tipetrans)."/".date('YmdHis');
		
		$str="INSERT INTO ".$dbname.".setup_validasiinput_dt (idht, kodeorg, divisi, jenistransaksi, jumlahhari, harilibur, berlakudari, berlakusampai, updateby, lastupdate, nopengajuan, keterangan) 
		VALUES ('".$param['id']."','".$kodeorg."','".$divisi."','".$tipetrans."','".$nilai."','".$harilibur."','".tanggalsystemn($param['tglmulai'])."','".tanggalsystemn($param['tglsampai'])."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."','".$nopengajuan."','".$param['ketdt']."')";
		
		$owlPDO->exec($str); 
		
		#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'form_ajukan':
		$kodeapproval=$param['kodeapproval'];
		
		$optKry="";
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='".$kodeapproval."' and a.level='1' and a.kodeunit='".$param['kodeorg']."'  order by b.namakaryawan asc";// exit('error'.$str);
		$res = fetchdata($str);
		if(count($res)==0)	{
			$tab.="Silahkan lakukan setup terlebih dahulu melalui menu :<br><b>Setup - Persetujuan</b>, dengan data sebagai berikut :<br>Kode Organisasi : <b>".$param['kodeorg']."</b><br>Kode Approval : <b>".$kodeapproval."</b>";
		}else{			
			foreach($res as $val){
				$optKry.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['lokasitugas']."]</option>";
			}
			$tab.="<table><input hidden id=unitajukan value=".$param['kodeorg'].">
					<tr>
						<td>No Pengajuan</td><td>:</td> 
						<td id=nopengajuan>".$param['nopengajuan']."</td> 
					</tr>
					<tr>
						<td>Kepada</td><td>:</td> 
						<td><select id=kepada style=\"width:200px;\">" . $optKry . "</select></td> 
					</tr>
					<tr>
						<td valign=top>Keterangan</td><td valign=top>:</td> 
						<td><textarea rows=3 maxlength=400 id=komentar  type='text' onkeypress='return tanpa_kutip(event)' style='width:180px;'></textarea></td> 
					</tr>
					<tr>
						<td valign=top></td><td valign=top></td> 
						<td><button onclick=ajukan('".$kodeapproval."') class=mybutton style=width:200px;height:30px>Ajukan</button></td> 
					</tr>
				</table>";
		}
		echo $tab;	
	break;
	case'ajukan':
	try {
		$owlPDO->beginTransaction();
			if($param['kepada']==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			if($param['nopengajuan']==''){
				throw new PDOException('Nomor pengajuan wajib terisi.');
			}
			if($param['jenispersetujuan']==''){
				throw new PDOException('Jenis Persetujuan wajib terisi.');
			}
			
			# update flag menjadi 1
			$str = "update " . $dbname . ".setup_validasiinput_dt set status='9' where nopengajuan ='".$param['nopengajuan']."' and kodeorg like '".$param['kodeorg']."%'";
			$owlPDO->exec($str);
			
			$str = "delete from ".$dbname.".approval where jenispersetujuan='".$param['jenispersetujuan']."' and status='0' and notransaksi not in (select nopengajuan from ".$dbname.".setup_validasiinput_dt)";
			$owlPDO->exec($str);
			
			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('','".$param['nopengajuan']."','".$param['jenispersetujuan']."','1','" . $param['kepada']."','0','".$param['komentar']."','','')";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'setaktifall':
		try {
		$owlPDO->beginTransaction();
			$inid=[];
			foreach($param['id'] as $id){
				$inid[$id]=$id;
			}
			
			$str = "update " . $dbname . ".setup_validasiinput_ht set status='".$param['stat']."', updateby ='".$_SESSION['standard']['userid']."' where id in ('".implode("','",$inid)."')";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	
	case 'loaddata':
		$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
		$optsts="<option value=''></option>";
		foreach($arrsts as $key => $val){
			$optsts.="<option value='".$key."'>".$val."</option>";
		}
		$str = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$stat="<br><select style='width:60px' id='aktifall' onchange=setaktifall(this.value)>".$optsts."</select>";
		}else{
			$stat="";
		}
		
		
		$tab.="<table id=mytable class='sortable nowrap' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kebun']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['divisi']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tipetransaksi']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['hari']." + ?</th> 
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['harilibur']."</th> 
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tanggalberlaku']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['status'].$stat."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tanggal']."</th>
				<th colspan='3' style='text-align:center;'>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr>
				<th style='display:none'></th>
				<th style='display:none'></th>
				<th style='display:none'></th>
			</tr>
		</thead>
		<tbody >";
		
		
	
		$no = 0;
		$str = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$admin=true;
			$where="";
		}else{
			$admin=false;
			$where=" and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		}
		
		if($param['cari']!=''){
			$where.=" and (kodeorg like '%".$param['cari']."%'";
			$where.=" or divisi like '%".$param['cari']."%'";
			$where.=" or jenistransaksi like '%".$param['cari']."%')";
		}

		$str= "select * from ".$dbname.".setup_validasiinput_ht where 1=1 ".$where." order by divisi";
		$bar= fetchdata($str);
		foreach($bar as $res){
			$no+=1;
			if($res['divisi']==''){
				$divisi=$_SESSION['lang']['all'];
			}else{
				$divisi=$res['divisi']." - ".$nmorg[$res['divisi']];
			}
			
			$tab.="<tr class=rowcontent>
					<td style='text-align:center;'>".$no."<input name=idall[] style=display:none value=".$res['id']."></td>
					<td>".$res['kodeorg']." - ".$nmorg[$res['kodeorg']]."</td>
					<td>".$divisi."</td>
					<td>".$arrtipe[$res['jenistransaksi']]." (".$res['jenistransaksi'].")</td>
					<td style='text-align:center;'>H + ".$res['jumlahhari']."</td>
					<td>".$arrlbr[$res['harilibur']]."</td>
					<td align=center>".tanggalnormal($res['berlaku'])."</td>
					<td align=center>".$arrsts[$res['status']]."</td>
					<td>".getNamaKaryawan($res['updateby'])."</td>
					<td align=center>".tanggalnormal($res['lastupdate'])."</td>";
					if($admin==true){
						$tab.="<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$res['kodeorg']."','".$res['divisi']."','".$res['jenistransaksi']."','".$res['jumlahhari']."','".$res['harilibur']."','".$res['status']."','".tanggalnormal($res['berlaku'])."','".$res['id']."')\"></td>
						<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$res['id']."')\"></td>";						
					}else{
						$tab.="<td style='text-align:center;width:25px'></td>";
						$tab.="<td style='text-align:center;width:25px'></td>";
					}
					
				$tab.="<td style='text-align:center;width:25px'><img src='images/zoom.png' class='resicon' title='Preview' onclick=adddetail('".$res['id']."');></td>
					
				</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
	case'getdivisi':
		$divisi="<option value=''>".$_SESSION['lang']['all']."</option>";

		$sKebun="select * from ".$dbname.".organisasi where length(kodeorganisasi)=6 and induk = '".$kodeorg."'  order by induk, kodeorganisasi";
		$res = fetchdata($sKebun);
		foreach($res as $bar){
			$d=$bar['induk'];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$divisi.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
				
			}
			$divisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			

			$n=$d;
			if($d!=$n){
				$divisi.="</optgroup>";
			}
		}
		echo $divisi;
	break;
	case 'insert':
		if($kodeorg==''||$stts==''||$nilai=='0' || $tglberlaku=='--' || $tipetrans=='' || $harilibur==''){
			echo "Gagal : Semua field harus diisi.";
			exit();
		}
		$str="select * from ".$dbname.".setup_validasiinput_ht where kodeorg='".$kodeorg."' and divisi ='".$divisi."' and jenistransaksi='".$tipetrans."'";
		$res = fetchdata($str);
		if(count($res)>0){
			echo "Gagal : Data sudah ada.";
		}else{
			$str="INSERT INTO ".$dbname.".setup_validasiinput_ht (kodeorg, divisi, jenistransaksi, jumlahhari, harilibur, status, updateby, lastupdate, berlaku) 
			VALUES ('".$kodeorg."','".$divisi."','".$tipetrans."','".$nilai."','".$harilibur."','".$stts."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."','".$tglberlaku."')";
			try{
				$owlPDO->exec($str); 
				//getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		}
	break;
		
	case 'edit':
		if($kodeorg==''||$stts==''||$nilai=='0' || $tglberlaku=='--' || $tipetrans=='' || $harilibur==''){
			echo "Gagal : Semua field harus diisi.";
			exit();
		}
		$str="UPDATE ".$dbname.".setup_validasiinput_ht SET 
				jumlahhari='".$nilai."', 
				status='".$stts."',
				harilibur='".$harilibur."',
				berlaku='".$tglberlaku."',
				kodeorg='".$kodeorg."',
				divisi='".$divisi."',
				jenistransaksi='".$tipetrans."',
				updateby='" . $_SESSION['standard']['userid'] . "',
				lastupdate='" .date('Y-m-d H:i:s'). "' WHERE id='".$param['id']."'"; #exit("error");
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".setup_validasiinput_ht where id='".$param['id']."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	case 'deletedt':
		$str="delete from ".$dbname.".setup_validasiinput_dt where idht='".$param['id']."' and jenistransaksi='".$param['tipetrans']."' and kodeorg='".$param['kodeorg']."' and divisi='".$param['divisi']."' and berlakudari='".tanggalsystemn($param['tglmulai'])."' and berlakusampai='".tanggalsystemn($param['tglsampai'])."'";
		
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
		
		#= delete approval
		if($param['nopengajuan']!=''){
			$str="delete from ".$dbname.".approval where notransaksi='".$param['nopengajuan']."'";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		}
		
	break;
	
	default:
	break;	
}

function getContainer(){
	global $owlPDO;
	global $dbname;
	
	$arrlbr=array('1'=>'Abaikan hari libur (HKE)','0'=>'Hitung hari libur');
	$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
	$no=0;
	$str="select * from ".$dbname.".kebun_5hariposting order by divisi";
	$bar = fetchdata($str);
	foreach($bar as $res){
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorg']."' or kodeorganisasi='".$res['divisi']."'");
		$no+=1;
		echo"<tr class=rowcontent>
				<td style='text-align:center;'>".$no."</td>
				<td>".$res['kodeorg']." - ".$nmorg[$res['kodeorg']]."</td>
				<td>".$res['divisi']." - ".$nmorg[$res['divisi']]."</td>
				<td>".$res['tipetransaksi']."</td>
				<td style='text-align:right;'>".$res['hari']."</td>
				<td>".$arrlbr[$res['harilibur']]."</td>
				<td>".$arrsts[$res['status']]."</td>
				<td>".getNamaKaryawan($res['updateby'])."</td>
				<td>".tanggalnormal($res['lastupdate'])."</td>
				<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$res['kodeorg']."','".$res['divisi']."','".$res['tipetransaksi']."','".$res['hari']."','".$res['harilibur']."','".$res['status']."')\"></td>
				<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$res['kodeorg']."','".$res['divisi']."','".$res['tipetransaksi']."')\"></td>
			</tr>";
	}
}
?>