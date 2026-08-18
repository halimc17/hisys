<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses','');
$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;	
}
$path = "fileupload/tiket/";
$param['jumlah']=str_replace(",","",$param['jumlah']);
$nmjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

switch($method){
    case 'previewdata':
		if($param['mode']=='baru'){
			$str = "SELECT max(convert(substring_index(notransaksi,'/',-1),unsigned integer)) as nomor FROM " . $dbname . ".sdm_tiket where kodeorg='".$param['kodeorg']."' and substr(tanggal,1,4) order by notransaksi desc";
			$res = fetchdata($str)[0];
			if($res['nomor']==0){
				$notransaksi=tanggalsystem($param['tgl'])."/".$param['kodeorg']."/TKT/0001";
			}else{
				$nmr=addZero($res['nomor']+1,4);
				
				$notransaksi=tanggalsystem($param['tgl'])."/".$param['kodeorg']."/TKT/".$nmr;
			}
			
			$data = array(
				'notransaksi'=> $notransaksi,
				'kodeorg'    => $param['kodeorg'],
				'sumber'     => $param['sumber'],
				'tanggal'    => tanggalsystemn($param['tgl']),
				'createdby'  => $_SESSION['standard']['userid'],
				'updatedby'  => $_SESSION['standard']['userid'],
				'createdtime'=> date("Y-m-d H:i:s"),
				'updatedtime'=> date("Y-m-d H:i:s"),
				'status'     => '0'
			);
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$query = insertQuery($dbname,'sdm_tiket',$data,$cols);
			try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}else{
			$notransaksi=$param['notransaksi'];
		}
		
	
	
		$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT *  FROM " . $dbname . ".sdm_5jenisbiayapjdinas where tiket='1' and status='1' order by id";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$optjenis.="<option value=".$bar['id'].">".$bar['keterangan']."</option>";
		}
		
		$optsupplier="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT *  FROM " . $dbname . ".log_5supplier where status='1' and statuspersetujuan='1' order by namasupplier";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$optsupplier.="<option value=".$bar['supplierid'].">".$bar['namasupplier']."</option>";
		}
		$byr=array('1'=>'Tunai','2'=>'Hutang');
		$optbayar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach ($byr as $bar => $val){
			$optbayar.="<option value=".$bar.">".$val."</option>";
		}
		
		if($param['sumber']!='1' and $param['sumber']!='3'){
			$style="disabled";
		}else{
			$stylename="disabled";
		}
		
        $tab.="<fieldset>
			<legend>Detail</legend>
				<table cellspacing=1 border=0>
				<tr>
					<td>" . $_SESSION['lang']['jenis'] . "</td> 
					<td>:</td>
					<td><select style=\"width:155px;\" onmousemove=hapuswarna(this.id); id=jenis>" . $optjenis . "</select></td>
					
					<td>".$_SESSION['lang']['noreferensi'] . "</td> 
					<td>:</td>
					<td><input ".$style." onmousemove=hapuswarna(this.id); id=nopjd style='width:150px;' onclick=getformnopjd(); readonly class='myinputtext'/></td>
				</tr> 
				<tr>	
					<td>".$_SESSION['lang']['nama'] . "</td> 
					<td>:</td>
					<td><input hidden id=idkaryawan style='width:150px;' class='myinputtext'/>
						<input ".$stylename." onmousemove=hapuswarna(this.id); id=namakaryawan style='width:150px;' class='myinputtext'/></td>
					
					<td>".$_SESSION['lang']['kodegolongan'] . "</td> 
					<td>:</td>
					<td><input hidden id=golongan style='width:150px;' class='myinputtext'/>
						<input ".$stylename." onmousemove=hapuswarna(this.id); id=namagolongan style='width:150px;' class='myinputtext'/>
					
					</td>
				</tr> 
				<tr>
					<td>".$_SESSION['lang']['tanggal'] . "</td> 
					<td>:</td>
					<td><input ".$stylename." readonly type=text class=myinputtext style='width:70px;' id=tgldinasdari onmousemove=setCalendar(this.id);hapuswarna(this.id); onkeypress=return false; maxlength=10 />
					<input ".$stylename." readonly type=text class=myinputtext style='width:73px;' id=tgldinassampai onmousemove=setCalendar(this.id);hapuswarna(this.id); onkeypress=return false; maxlength=10 />
					</td>

					<td>".$_SESSION['lang']['tgldibutuhkan'] . "</td> 
					<td>:</td>
					<td><input type=text class=myinputtext style='width:150px;' id=tgldibutuhkan onmousemove=setCalendar(this.id);hapuswarna(this.id); onkeypress=return false; maxlength=10 /></td>
				</tr> 
				<tr>
					<td>".$_SESSION['lang']['vendor'] . "</td> 
					<td>:</td>
					<td><select class=select2 style=\"width:155px;\" onmousemove=hapuswarna(this.id); id=supplier>" . $optsupplier . "</select>
					</td>
					
					<td>".$_SESSION['lang']['pembayaran'] . "</td> 
					<td>:</td>
					<td><select style=\"width:155px;\" onmousemove=hapuswarna(this.id); id=pembayaran>" . $optbayar . "</select>
					</td>
				</tr> 
				<tr>
					
					<td>".$_SESSION['lang']['jumlah'] . " (Rp)</td> 
					<td>:</td>
					<td><input type=text onmousemove=hapuswarna(this.id); id=jumlah class=myinputtextnumber onkeyup=\"z.numberFormat('jumlah',2)\" nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>
				</tr> 
				
				<tr>
					<td>".$_SESSION['lang']['keterangan'] . "</td> 
					<td>:</td>
					<td colspan=4><input onmousemove=hapuswarna(this.id); id=keterangan style='width:413px;' class='myinputtext'/></td>
				</tr> 
				
				<tr>
					<td colspan=2></td>
					<td colspan=44>
						<input hidden id=methoddetail value='simpandetail'>
						<input hidden id=id>
						<input hidden id=idpengajuan>
						<button class=mybutton onclick=simpandetail()>" . $_SESSION['lang']['save'] . "</button>
						<button class=mybutton onclick=canceldetail()>" . $_SESSION['lang']['cancel'] . "</button>
						<button class=mybutton onclick=loaddatadetail()>Refresh</button>
					</td>
				</tr>
			</table>
		</fieldset>
		<div style=clear:both></div>
		<div id=loaddatadetail></div>
		";
		echo $tab."####".$notransaksi;
    break;
	case'getformnopjd':
		$tab="";
		$nmjenis=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		if($param['jenis']==''){
			$param['jenis']='3';
		}
		
		$optjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT *  FROM " . $dbname . ".sdm_5jenisbiayapjdinas where tiket='1' and status='1' order by id";
		$res = fetchdata($str);
		foreach ($res as $bar){
			if($param['jenis']==$bar['id']){
				$optjenis.="<option value=".$bar['id']." selected>".$bar['keterangan']."</option>";
			}else{				
				$optjenis.="<option value=".$bar['id'].">".$bar['keterangan']."</option>";
			}
		}
		
		$tab.="<table cellspacing=1 border=0>
				<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td> 
					<td>:</td>
					<td><input id=notransaksisrc style='width:150px;' onkeypress='enterkey(event,getnopjd)' class='myinputtext'/></td>
					
					<td>".$_SESSION['lang']['nama'] . "</td> 
					<td>:</td>
					<td><input id=namakaryawansrc style='width:150px;' onkeypress='enterkey(event,getnopjd)' class='myinputtext'/></td>
				</tr> 
				<tr>
					<td>".$_SESSION['lang']['jenis']."</td> 
					<td>:</td>
					<td><select style=\"width:155px;\" id=jenisadd>" . $optjenis . "</select></td>
					
				</tr> 
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=getnopjd()>" . $_SESSION['lang']['find'] . "</button>
					</td>
				</tr>
			</table><hr>";
			
		$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable width=100%>
				<thead><tr class=rowheader>";
			$rows="rowspan=2";	
			$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['nomor']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['nama']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['kodegolongan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tujuan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tipe']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['keperluan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tanggal']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tgldibutuhkan']."</th>
				<th align=center colspan=2 ".$rows.">".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
			</thead>
				<tbody id=contformpjd>
				</tbody>
				<tfoot id=contformpjdfoot>
				</tfoot>
			</table>
			";
		echo $tab;
	break;
	case'getnopjd':
		switch($param['sumber']){
			case'1':
				$tab="";
				$arrtuj=array(
					'dndalkot'  =>'Dalam Negeri - Dalam Kota',
					'dnlukot'   =>'Dalam Negeri - Luar Kota',
					'lnkuching' =>'Luar Negeri - Area Kuching',
					'lnasean'   =>'Luar Negeri - Area Asean',
					'lnnonasean'=>'Luar Negeri - Area Non Asean'
				);		
				$arrperlu=array(
					'dinas'   =>'Kunjungan Dinas',
					'training'=>'Training',
					'lain'    =>'Lain - Lain'
				);	
				
				$where="";
				$where.=" and statuspengajuan='1'";
				$where.=" and statusrealisasi='0'";
				$where.=" and tgldinasdarireal>='".tanggalsystemn($param['tgl'])."'";
				
				if($param['jenis']==''){
					$param['jenis']='3';
				}
				if($param['jenis']=='3'){
					$where.=" and tiket='1'";
				}
				if($param['notransaksi']!=''){
					$where.=" and notransaksi like '%".$param['notransaksi']."%'";
				}
				if($param['namakaryawan']!=''){
					$where.=" and karyawanid in (SELECT karyawanid  FROM " . $dbname . ".datakaryawan where namakaryawan like '%".$param['namakaryawan']."%')";
				}
				
				#$where.=" and notransaksi not in (SELECT notranspjd  FROM " . $dbname . ".sdm_tiket)";
				$str = "SELECT distinct notransaksi  FROM " . $dbname . ".sdm_pjdinasht where 1=1 ".$where." order by createtime desc";
				$res = fetchdata($str);
				$jlhbrs = count($res);
				if(count($res)==0){
					echo"<tr class=rowcontent><td align=center colspan=12>Data tidak ditemukan.</td></tr>";
					exit();
				}
				$limit = 10;
				$page = 0;
				$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
				if (isset($_POST['page'])) {$page = intval($_POST['page']); if ($page < 0){$page = 0;}}

				$offset = floatval($page) * floatval($limit);
				$maxdisplay = (floatval($page) * floatval($limit));
				$no = 0;
				$no = $maxdisplay;
				$str = "SELECT *  FROM " . $dbname . ".sdm_pjdinasht where 1=1 ".$where." order by createtime desc limit " . $offset . "," . $limit . "";
				$res = fetchdata($str);
				foreach ($res as $bar){
					$no++;
					$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
					$kdgol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$bar['karyawanid']."'");
					$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$kdgol[$bar['karyawanid']]."'");
					
					$tglbutuh=array(); $rows="";
					
					//$rows="rowspan='1";
					
					$tab.="<tr class=rowcontent style=vertical-align:top;>";
					$tab.="<td align=center ".$rows.">".$no."</td>";
					$tab.="<td align=left ".$rows.">".$bar['notransaksi']."</td>";
					$tab.="<td align=left ".$rows.">".$nmkar[$bar['karyawanid']]."</td>";
					$tab.="<td align=left ".$rows.">".$nmgol[$kdgol[$bar['karyawanid']]]."</td>";
					$tab.="<td align=left ".$rows.">".$bar['pttujuan']."</td>";
					$tab.="<td align=left ".$rows.">".$arrtuj[$bar['regiontujuan']]."</td>";
					$tab.="<td align=left ".$rows.">".$arrperlu[$bar['keperluan']]."</td>";
					$tab.="<td align=left ".$rows.">".$bar['keterangan']."</td>";
					$tab.="<td align=center ".$rows.">".tanggalnormal($bar['tgldinasdarireal'])." s/d ".tanggalnormal($bar['tgldinassampaireal'])."</td>";
					$tab.="<td align=left>";
					
						$tab.="<table width=100%>";					
						$s = "select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$bar['notransaksi']."' and jenisbiaya='".$param['jenis']."' order by tanggal asc";
						$r = fetchdata($s);
						if(count($r)>0){					
							foreach($r as $b){
								$t = "select * from ".$dbname.".sdm_tiketdt a where a.notranspjd='".$bar['notransaksi']."' and a.jenis='".$b['jenisbiaya']."' and a.tanggal='".$b['tanggal']."'";
								$e = fetchdata($t);
								$color=$nomor="";
								$tab.="<tr style=vertical-align:top;>";
								if(count($e)>0){
									foreach($e as $a){
										$nomor.=$a['notransaksi']."\n";
									}
									$color="style=background-color:#FFBA71; title=\"sudah pernah ada data sebelumnya.\ndengan notransaksi = \n".$nomor."\"";
									$tab.="<td align=left ".$color.">".str_replace("\n","<br>",$nomor)."</td>";
								}else{							
									$tab.="<td align=center ".$color.">".tanggalnormal($b['tanggal'])."</td>";
								}
								$tab.="</tr>";
								
							}
						}else{
							$t = "select * from ".$dbname.".sdm_tiketdt a where a.notranspjd='".$bar['notransaksi']."' and a.jenis='".$param['jenis']."'";
							$e = fetchdata($t);
							$color=$nomor="";
							if(count($e)>0){
								foreach($e as $a){
									$nomor.=$a['notransaksi']."\n";
								}
								$color="style=background-color:#FFBA71; title=\"sudah pernah ada data sebelumnya.\ndengan notransaksi = \n".$nomor."\"";
							}
							$tab.="<tr>";
							$tab.="<td align=left ".$color.">".str_replace("\n","<br>",$nomor)."</td>";
							$tab.="</tr>";
						}
						$tab.="</table>";
					$tab.="</td>";
					$tab.="<td align=left>";
					$tab.="<table width=100%>";
					if(count($r)>0){					
						foreach($r as $b){
							$tab.="<tr style=vertical-align:top;>";
							$tab.="<td align=center ".$rows." style=width:20px><img src=images/plus.png class='zImgBtn' title=Add onclick=\"addpjd('".$bar['notransaksi']."','".$b['jenisbiaya']."','".tanggalnormal($b['tanggal'])."','".$bar['karyawanid']."','".$nmkar[$bar['karyawanid']]."','".$kdgol[$bar['karyawanid']]."','".$nmgol[$kdgol[$bar['karyawanid']]]."','".$bar['keterangan']."','".tanggalnormal($bar['tgldinasdarireal'])."','".tanggalnormal($bar['tgldinassampaireal'])."');\"></td>";
							$tab.="</tr>";
						}
					}else{
						$tab.="<tr>";
						$tab.="<td align=center ".$rows." style=width:20px><img src=images/plus.png class='zImgBtn' title=Add onclick=\"addpjd('".$bar['notransaksi']."','".$param['jenis']."','','".$bar['karyawanid']."','".$nmkar[$bar['karyawanid']]."','".$kdgol[$bar['karyawanid']]."','".$nmgol[$kdgol[$bar['karyawanid']]]."','".$bar['keterangan']."','".tanggalnormal($bar['tgldinasdarireal'])."','".tanggalnormal($bar['tgldinassampaireal'])."');\"></td>";
						$tab.="</tr>";
					}
					$tab.="</table>";
					$tab.="</td>";
					
					#$tab.="<td align=center ".$rows." style=width:20px><img src=images/plus.png class='zImgBtn' title=Add onclick=addpjd();></td>";
					$tab.="<td align=center ".$rows." style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"detailDataPJD('".$bar['notransaksi']."','event','html');\" ></td>";
					
				}
				
				$totrows = ceil($jlhbrs / $limit);
				if ($totrows == 0) {$totrows = 1;}
				$isiRow=$footd="";
				for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
				$footd.="</tr><tr><td colspan=12 align=center>";
				if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=getnopjd(" . ($page - 1) . ");>Prev</button>";}
				$footd.="<select id=\"pagesrc\" name=\"pagesrc\" style=\"min-width:20px\" onchange=\"getPageSrc()\">" . $isiRow . "</select>";
				if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=getnopjd(" . ($page + 1) . ");>Next</button>";}
				$footd.="</td></tr>";
			
			break;
			case'3':
				if($param['jenis']==''){
					exit("Warning : Jenis wajib diisi.");
				}
			
				$where="";
				$where.=" and a.status='1'";
				$where.=" and b.jenis ='".$param['jenis']."'";
				$where.=" and a.notransaksi in (SELECT notransaksi  FROM " . $dbname . ".sdm_pengajuantiketdt where tanggal >='".tanggalsystemn($param['tgl'])."')";
				
				if($param['notransaksi']!=''){
					$where.=" and a.notransaksi like '%".$param['notransaksi']."%'";
				}
				if($param['namakaryawan']!=''){
					$where.=" and a.notransaksi in (SELECT notransaksi  FROM " . $dbname . ".sdm_pengajuantiketdt where nama like '%".$param['namakaryawan']."%') or a.notransaksi in (SELECT notransaksi  FROM " . $dbname . ".sdm_pengajuantiketdt where nama in (SELECT karyawanid  FROM " . $dbname . ".datakaryawan where namakaryawan like '%".$param['namakaryawan']."%'))";
				}
				
				$str = "SELECT distinct a.notransaksi  FROM " . $dbname . ".sdm_pengajuantiket a left join " . $dbname . ".sdm_pengajuantiketdt b on a.notransaksi=b.notransaksi where 1=1 ".$where." and b.nama!=''";
				$res = fetchdata($str);
				$jlhbrs = count($res);
				if(count($res)==0){
					echo"<tr class=rowcontent><td align=center colspan=12>Data tidak ditemukan.</td></tr>";
					exit();
				}
				$limit = 10;
				$page = 0;
				$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
				if (isset($_POST['page'])) {$page = intval($_POST['page']); if ($page < 0){$page = 0;}}

				$offset = floatval($page) * floatval($limit);
				$maxdisplay = (floatval($page) * floatval($limit));
				$no = 0;
				$no = $maxdisplay;
				
				$data=$rdt=array();
				$str = "SELECT a.*,b.*,b.tanggal as tanggal, a.tanggal as tglht  FROM " . $dbname . ".sdm_pengajuantiket a left join " . $dbname . ".sdm_pengajuantiketdt b on a.notransaksi=b.notransaksi where 1=1 ".$where." and b.nama!='' order by a.notransaksi desc limit " . $offset . "," . $limit . "";
				$res = fetchdata($str);
				foreach ($res as $bar){
					$rdt[$bar['notransaksi']]+=1;
					$data[$bar['notransaksi']][$bar['id']]=$bar['nama'];
					$gol[$bar['notransaksi']][$bar['id']]=$bar['golongan'];
					$dari[$bar['notransaksi']][$bar['id']]=$bar['dari'];
					$ke[$bar['notransaksi']][$bar['id']]=$bar['ke'];
					$ket[$bar['notransaksi']][$bar['id']]=$bar['keterangan'];
					$kep[$bar['notransaksi']][$bar['id']]=$bar['keperluan'];
					$tgldari[$bar['notransaksi']][$bar['id']]=$bar['tgldari'];
					$tglsampai[$bar['notransaksi']][$bar['id']]=$bar['tglsampai'];
					$telp[$bar['notransaksi']][$bar['id']]=$bar['phone'];
					$tgl[$bar['notransaksi']][$bar['id']]=$bar['tanggal'];
					$jns[$bar['notransaksi']][$bar['id']]=$bar['jenis'];
				}
				$no=0;
				foreach($data as $notr => $vnm){
					$no++;
					if($rdt[$notr]>1){
						$rows="rowspan=".$rdt[$notr]."";
					}else{
						$rows="";
					}
					$tab.="<tr class=rowcontent style=vertical-align:top;>";
					$tab.="<td align=center ".$rows.">".$no."</td>";
					$tab.="<td align=left ".$rows.">".$notr."</td>";
					$i=0;
					foreach($vnm as $iddt => $nama){
						$i++;
						$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$nama."'");
						$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$gol[$notr][$iddt]."'");
						
						if($nmkar[$nama]!=''){
							$nmkr=$nmkar[$nama];
							$golkar=$nmgol[$gol[$notr][$iddt]];
							
							$tab.="<td align=left>".$nmkar[$nama]."<br><font style=font-size:10px;font-weight:bold;>".$telp[$notr][$iddt]."</font></td>";
							$tab.="<td align=left>".$nmgol[$gol[$notr][$iddt]]."</td>";
						}else{
							$nmkr=$nama;
							$golkar=$gol[$notr][$iddt];
							
							$tab.="<td align=left>".$nama."<br><font style=font-size:10px;font-weight:bold;>".$telp[$notr][$iddt]."</font></td>";
							$tab.="<td align=left>".$gol[$notr][$iddt]."</td>";
						}
						$tab.="<td align=left>".$dari[$notr][$iddt]." - ".$ke[$notr][$iddt]."</td>";
						$tab.="<td align=left></td>";
						$tab.="<td align=left>".$kep[$notr][$iddt]."</td>";
						$tab.="<td align=left>".$ket[$notr][$iddt]."</td>";
						$tab.="<td align=left>".tanggalnormal($tgldari[$notr][$iddt])." - ".tanggalnormal($tglsampai[$notr][$iddt])."</td>";
						
						$t = "select * from ".$dbname.".sdm_tiketdt a where a.notranspjd='".$notr."' and a.jenis='".$jns[$notr][$iddt]."' and a.idpengajuan='".$iddt."'";
						$e = fetchdata($t);
						$color=$nomor="";
						if(count($e)>0){
							foreach($e as $a){
								$nomor.=$a['notransaksi']."\n";
							}
							$color="style=background-color:#FFBA71; title=\"sudah pernah ada data sebelumnya.\ndengan notransaksi = \n".$nomor."\"";
							$tab.="<td align=left ".$color.">".str_replace("\n","<br>",$nomor)."</td>";
						}else{
							$tab.="<td align=center ".$color.">".tanggalnormal($tgl[$notr][$iddt])."</td>";
						}
						
						$tab.="<td align=center colspan=2 style=width:20px><img src=images/plus.png class='zImgBtn' title=Add onclick=\"addpjd('".$notr."','".$jns[$notr][$iddt]."','".tanggalnormal($tgl[$notr][$iddt])."','".$nama."','".$nmkr."','".$gol[$notr][$iddt]."','".$golkar."','".$ket[$notr][$iddt]."','".tanggalnormal($tgldari[$notr][$iddt])."','".tanggalnormal($tglsampai[$notr][$iddt])."','".$iddt."');\"></td>";


						if($rdt[$notr]>1 and $rdt[$notr]!=$i){
							$tab.="</tr>";
							$tab.="<tr class=rowcontent style=vertical-align:top;>";
						}else{
							$tab.="</tr>";
						}
					}					
				}
				
				$totrows = ceil($jlhbrs / $limit);
				if ($totrows == 0) {$totrows = 1;}
				$isiRow=$footd="";
				for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
				$footd.="</tr><tr><td colspan=12 align=center>";
				if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=getnopjd(" . ($page - 1) . ");>Prev</button>";}
				$footd.="<select id=\"pagesrc\" name=\"pagesrc\" style=\"min-width:20px\" onchange=\"getPageSrc()\">" . $isiRow . "</select>";
				if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=getnopjd(" . ($page + 1) . ");>Next</button>";}
				$footd.="</td></tr>";
				
			break;
		}

        echo $tab . "####" . $footd;
	break;
	case'viewlistdata':
		$tab="";
		$arrtuj=array(
			'dndalkot'  =>'Dalam Negeri - Dalam Kota',
			'dnlukot'   =>'Dalam Negeri - Luar Kota',
			'lnkuching' =>'Luar Negeri - Area Kuching',
			'lnasean'   =>'Luar Negeri - Area Asean',
			'lnnonasean'=>'Luar Negeri - Area Non Asean'
		);		
		$arrperlu=array(
			'dinas'   =>'Kunjungan Dinas',
			'training'=>'Training',
			'lain'    =>'Lain - Lain'
		);	
		
		$where=$whdt="";
		$where.=" and statuspengajuan='1'";
		$where.=" and statusrealisasi='0'";
		$where.=" and tgldinasdarireal>='2020-01-01'";
		#$where.=" and tgldinasdarireal>='".date("Y-m-d")."'";
		
		if($param['notransaksi']!=''){
			$where.=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		if($param['namakaryawan']!=''){
			$where.=" and karyawanid in (SELECT karyawanid  FROM " . $dbname . ".datakaryawan where namakaryawan like '%".$param['namakaryawan']."%')";
		}
		
		$str = "SELECT distinct notransaksi  FROM " . $dbname . ".sdm_pjdinasht where 1=1 ".$where." order by createtime desc";
		$res = fetchdata($str);
		$jlhbrs = count($res);
		if(count($res)==0){
			echo"<tr class=rowcontent><td align=center colspan=10>Data tidak ditemukan.</td></tr>";
			exit();
		}
		$limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']); if ($page < 0){$page = 0;}}

        $offset = floatval($page) * floatval($limit);
        $maxdisplay = (floatval($page) * floatval($limit));
        $no = 0;
        $no = $maxdisplay;
		$str = "SELECT *  FROM " . $dbname . ".sdm_pjdinasht where 1=1 ".$where." order by createtime desc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$no++;
			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
			$kdgol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$bar['karyawanid']."'");
			$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$kdgol[$bar['karyawanid']]."'");
			
			$tglbutuh=array(); $rows="";
			
			//$rows="rowspan='1";
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>";
			$tab.="<td align=center ".$rows.">".$no."</td>";
			$tab.="<td align=left ".$rows.">".$bar['notransaksi']."</td>";
			$tab.="<td align=left ".$rows.">".$bar['kodeorg']."</td>";
			$tab.="<td align=left ".$rows.">".$nmkar[$bar['karyawanid']]."</td>";
			$tab.="<td align=left ".$rows.">".$nmgol[$kdgol[$bar['karyawanid']]]."</td>";
			$tab.="<td align=left ".$rows.">".$bar['unittujuan']."</td>";
			$tab.="<td align=left ".$rows.">".$bar['keterangan']."</td>";
			$tab.="<td align=center ".$rows.">".tanggalnormal($bar['tgldinasdarireal'])." s/d ".tanggalnormal($bar['tgldinassampaireal'])."</td>";
			$tab.="<td align=left>";
			
				$tab.="<table width=100%>";					
				$s = "select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$bar['notransaksi']."' and jenisbiaya='".$param['jenis']."' order by tanggal asc";
				$r = fetchdata($s);
				if(count($r)>0){					
					foreach($r as $b){
						$t = "select * from ".$dbname.".sdm_tiketdt a where a.notranspjd='".$bar['notransaksi']."' and a.jenis='".$b['jenisbiaya']."' and a.tanggal='".$b['tanggal']."'";
						$e = fetchdata($t);
						$color=$nomor="";
						$tab.="<tr style=vertical-align:top;>";
						if(count($e)>0){
							foreach($e as $a){
								$nomor.=$a['notransaksi']."\n";
							}
							$color="style=background-color:#FFBA71; title=\"sudah pernah ada data sebelumnya.\ndengan notransaksi = \n".$nomor."\"";
							$tab.="<td align=left ".$color.">".str_replace("\n","<br>",$nomor)."</td>";
						}else{							
							$tab.="<td align=center ".$color.">".tanggalnormal($b['tanggal'])."</td>";
						}
						$tab.="</tr>";
						
					}
				}else{
					$t = "select * from ".$dbname.".sdm_tiketdt a where a.notranspjd='".$bar['notransaksi']."' and a.jenis='".$b['jenisbiaya']."'";
					$e = fetchdata($t);
					$color=$nomor="";
					if(count($e)>0){
						foreach($e as $a){
							$nomor.=$a['notransaksi']."\n";
						}
						$color="style=background-color:#FFBA71; title=\"sudah pernah ada data sebelumnya.\ndengan notransaksi = \n".$nomor."\"";
					}
					$tab.="<tr>";
					$tab.="<td align=left ".$color.">".str_replace("\n","<br>",$nomor)."</td>";
					$tab.="</tr>";
				}
				$tab.="</table>";
			$tab.="</td>";
			#$tab.="<td align=center ".$rows." style=width:20px><img src=images/plus.png class='zImgBtn' title=Add onclick=addpjd();></td>";
			$tab.="<td align=center ".$rows." style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"detailDataPJD('".$bar['notransaksi']."','event','html');\" ></td>";
			
		}
		
		$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {$totrows = 1;}
		$isiRow=$footd="";
        for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
        $footd.="</tr><tr><td colspan=10 align=center>";
        if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=viewlistdata(" . ($page - 1) . ");>Prev</button>";}
        $footd.="<select id=\"pagelist\" name=\"pagelist\" style=\"min-width:20px\" onchange=\"getPagelist()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=viewlistdata(" . ($page + 1) . ");>Next</button>";}
        $footd.="</td></tr>";

        echo $tab . "####" . $footd;
	break;
	
	case'simpandetail':
	try {
		$owlPDO->beginTransaction();
			if($param['sumber']=='1'){
				$t = "select * from ".$dbname.".sdm_tiketdt  where notransaksi='".$param['notransaksi']."' and notranspjd='".$param['nopjd']."' and jenis='".$param['jenis']."' and tanggal='".tanggalsystemn($param['tgldibutuhkan'])."' and supplierid='".$param['supplier']."'";
				$e = fetchdata($t);
				if(count($e)>0){
					throw new PDOException("Data sudah pernah ada.");
				}
			}
			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$param['karyawanid']."'");
			$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$param['golongan']."'");
			if($nmkar[$param['karyawanid']]!=''){
				$param['nama']=$param['karyawanid'];					
			}else{					
				$param['nama']=$param['namakaryawan'];
			}
			if($nmgol[$param['golongan']]!=''){
				$param['gol']=$param['golongan'];					
			}else{					
				$param['gol']=$param['namagolongan'];
			}
			
			if(strtotime($param['tgldibutuhkan'])<strtotime($param['tgldinasdari'])){
				throw new PDOException("Tanggal dibutuhkan tidak boleh lebih kecil dari tanggal mulai dinas.");
			}
			if($param['tgldibutuhkan']>$param['tgldinassampai']){
				#throw new PDOException("Tanggal dibutuhkan tidak boleh lebih besar dari tanggal sampai dinas.");
			}
			
			$data = array(
				'pembayaran'   => $param['pembayaran'],
				'notransaksi'   => $param['notransaksi'],
				'idpengajuan'   => $param['idpengajuan'],
				'notranspjd'    => $param['nopjd'],
				'nama'          => $param['nama'],
				'golongan'      => $param['gol'],
				'supplierid'    => $param['supplier'],
				'keterangan'    => $param['keterangan'],
				'jenis'         => $param['jenis'],
				'tanggal'       => tanggalsystemn($param['tgldibutuhkan']),
				'tgldinasdari'  => tanggalsystemn($param['tgldinasdari']),
				'tgldinassampai'=> tanggalsystemn($param['tgldinassampai']),
				'biaya'         => $param['jumlah'],
				'updateby'      => $_SESSION['standard']['userid'],
				'lastupdate'    => date("Y-m-d H:i:s")
			);
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$query = insertQuery($dbname,'sdm_tiketdt',$data,$cols);
			$owlPDO->exec($query);
			
			$t = "select sum(biaya) as biaya from ".$dbname.".sdm_tiketdt  where notransaksi='".$param['notransaksi']."'";
			$e = fetchdata($t)[0];
			$data = array(
				'totalbiaya' => $e['biaya'],
				'updatedby'  => $_SESSION['standard']['userid'],
				'updatedtime'=> date("Y-m-d H:i:s")
			);
			$where = "notransaksi='".$param['notransaksi']."'";
			$query = updateQuery($dbname,'sdm_tiket',$data,$where);
			$owlPDO->exec($query);
			
			
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'editdetail':
	try {
		$owlPDO->beginTransaction();
			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$param['karyawanid']."'");
			$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$param['golongan']."'");
			if($nmkar[$param['karyawanid']]!=''){
				$param['nama']=$param['karyawanid'];					
			}else{					
				$param['nama']=$param['namakaryawan'];
			}
			if($nmgol[$param['golongan']]!=''){
				$param['gol']=$param['golongan'];					
			}else{					
				$param['gol']=$param['namagolongan'];
			}
			
			if(strtotime($param['tgldibutuhkan'])<strtotime($param['tgldinasdari'])){
				throw new PDOException("Tanggal dibutuhkan tidak boleh lebih kecil dari tanggal mulai dinas.");
			}
			if($param['tgldibutuhkan']>$param['tgldinassampai']){
				#throw new PDOException("Tanggal dibutuhkan tidak boleh lebih besar dari tanggal sampai dinas.");
			}
			
			$data = array(
				'pembayaran'   => $param['pembayaran'],
				'idpengajuan'   => $param['idpengajuan'],
				'notranspjd'    => $param['nopjd'],
				'nama'          => $param['nama'],
				'golongan'      => $param['gol'],
				'supplierid'    => $param['supplier'],
				'keterangan'    => $param['keterangan'],
				'jenis'         => $param['jenis'],
				'tanggal'       => tanggalsystemn($param['tgldibutuhkan']),
				'tgldinasdari'  => tanggalsystemn($param['tgldinasdari']),
				'tgldinassampai'=> tanggalsystemn($param['tgldinassampai']),
				'biaya'         => $param['jumlah'],
				'updateby'      => $_SESSION['standard']['userid'],
				'lastupdate'    => date("Y-m-d H:i:s")
			);
		
			$where = "id='".$param['id']."'";
			$query = updateQuery($dbname,'sdm_tiketdt',$data,$where);
			$owlPDO->exec($query);
			
			$t = "select sum(biaya) as biaya from ".$dbname.".sdm_tiketdt  where notransaksi='".$param['notransaksi']."'";
			$e = fetchdata($t)[0];
			$data = array(
				'totalbiaya' => $e['biaya'],
				'updatedby'  => $_SESSION['standard']['userid'],
				'updatedtime'=> date("Y-m-d H:i:s")
			);
			$where = "notransaksi='".$param['notransaksi']."'";
			$query = updateQuery($dbname,'sdm_tiket',$data,$where);
			$owlPDO->exec($query);
			
			
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'loaddatadetail':
		$tab="";
		$tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=5 cellspacing=1 class=sortable>
				<thead><tr class=rowheader>";
			$rows="rowspan=2";	
			$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['jenis']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['noreferensi']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['nama']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['kodegolongan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tanggal']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tgldibutuhkan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['vendor']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
				<th align=center colspan=3 ".$rows.">".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
			</thead>
				<tbody>";
		$str = "SELECT *  FROM " . $dbname . ".sdm_tiketdt where notransaksi='".$param['notransaksi']."' order by id desc ";
		$res = fetchdata($str);
		$no=0;
		foreach ($res as $bar){
			$no++;
			
			$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['supplierid']."'");
			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['nama']."'");
			$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$bar['golongan']."'");
			if($nmkar[$bar['nama']]!=''){
				$nama=$nmkar[$bar['nama']];
			}else{
				$nama=$bar['nama'];
			}
			if($nmgol[$bar['golongan']]!=''){
				$gol=$nmgol[$bar['golongan']];
			}else{
				$gol=$bar['golongan'];
			}
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$nmjns[$bar['jenis']]."</td>";
			if($bar['notranspjd']!=''){				
				$tab.="<td align=left>".$bar['notranspjd']."</td>";
			}else{
				$tab.="<td align=left>Lainnya</td>";
			}
			$tab.="<td align=left>".$nama."</td>";
			$tab.="<td align=left>".$gol."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tgldinasdari'])." s/d ".tanggalnormal($bar['tgldinassampai'])."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=left>".$nmsup[$bar['supplierid']]."</td>";
			$tab.="<td align=right>".number_format($bar['biaya'])."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
			
			$tab.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editdetail('".$bar['id']."','".$bar['jenis']."','".$bar['notranspjd']."','".$bar['nama']."','".$nama."','".$bar['golongan']."','".$gol."','".tanggalnormal($bar['tgldinasdari'])."','".tanggalnormal($bar['tgldinassampai'])."','".tanggalnormal($bar['tanggal'])."','".$bar['supplierid']."','".$bar['biaya']."','".$bar['keterangan']."','".$bar['pembayaran']."');\" ></td>";
			$tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deldetail('".$bar['notransaksi']."','".$bar['id']."');\" ></td>";
			
			$tab.="<td align=center width=20px><img src=images/upload-2-xxl.png class=zImgBtn	title='Upload' onclick=\"showupload('event','".$bar['id']."','".$bar['notransaksi']."');\"></td>";
			
			$ttl+=$bar['biaya'];
			$tab.="</tr>";
		}
		
		$tab.="<tr class=rowcontent style=vertical-align:top;>";
		$tab.="<td align=center colspan=8>T O T A L</td>";
		$tab.="<td align=right>".number_format($ttl)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="</tr>";
				
		$tab.="</tbody></table></fieldset>";
		echo $tab;	
	break;
	case'loaddata':
		$tab="";
		$where="";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$_SESSION['empl']['kodeorganisasi']."')";
		} else {
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		
		if($param['notransaksi']!=''){
			$where.=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeorg like '%".$param['kodeorg']."%'";
		}
		if($param['tgl']!=''){
			$where.=" and tanggal like '%".tanggalsystemn($param['tgl'])."%'";
		}
		if($param['nopjd']!=''){
			$where.=" and notransaksi in (select notransaksi from ".$dbname.".sdm_tiketdt where notranspjd like '%".$param['nopjd']."%')";
		}
		if($param['nama']!=''){
			$where.=" and notransaksi in (select notransaksi from ".$dbname.".sdm_tiketdt where nama like '%".$param['nama']."%' or nama in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$param['nama']."%'))";
		}
		if($param['supplier']!=''){
			$where.=" and notransaksi in (select notransaksi from ".$dbname.".sdm_tiketdt where supplierid in (select supplierid from ".$dbname.".log_5supplier where namasupplier like '%".$param['supplier']."%'))";
		}
		
		$str = "SELECT distinct notransaksi  FROM " . $dbname . ".sdm_tiket where 1=1 ".$where." order by createdtime desc";
		$res = fetchdata($str);
		$jlhbrs = count($res);
		if(count($res)==0){
			$tab="<tr class=rowcontent><td align=center colspan=14>Data tidak ditemukan.</td></tr>"; $footd="";
			echo $tab . "####" . $footd;
			exit();
		}
		$limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']); if ($page < 0){$page = 0;}}

        $offset = floatval($page) * floatval($limit);
        $maxdisplay = (floatval($page) * floatval($limit));
        $no = 0;
        $no = $maxdisplay;
		
		$arsumber=array('1'=>$_SESSION['lang']['perjalanandinas'],'2'=>$_SESSION['lang']['lain'],'3'=>$_SESSION['lang']['pengajuan']);
		#$arrHslx=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['belumdiajukan'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['reconfirm']);
		$arrHslx=array("1"=>$_SESSION['lang']['posting'],"0"=>$_SESSION['lang']['belumposting']);
		
		$str = "SELECT *  FROM " . $dbname . ".sdm_tiket where 1=1 ".$where." order by notransaksi desc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$no++;
			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updatedby']."'");
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			
			$tab.="<tr class=rowcontent style=height:20px>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['notransaksi']."</td>";
			$tab.="<td align=left>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align=left>".$arsumber[$bar['sumber']]."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=right>".number_format($bar['totalbiaya'])."</td>";
			$tab.="<td align=left>".$nmkar[$bar['updatedby']]."</td>";
			
			$wr="";
			if($bar['status']=='3'){
				$wr="style=background-color:yellow";
			}elseif($bar['status']=='1'){
				$wr="style=background-color:green";
			}elseif($bar['status']=='2'){
				$wr="style=background-color:red";
			}
			
			$tab.="<td align=left ".$wr.">".$arrHslx[$bar['status']]."</td>";
			
			if($bar['status']=='0' or $bar['status']=='3'){				
				$tab.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$bar['notransaksi']."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeorg']."','".$bar['sumber']."');\" ></td>";
				$tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['notransaksi']."');\" ></td>";
				if($bar['totalbiaya']>0){					
					#$tab.="<td align=center style=width:20px><img src=images/skyblue/submit.jpg class=zImgBtn  title='Ajukan ?' onclick=\"form_ajukan('".$bar['notransaksi']."','TKT');\" ></td>";
					
					$tab.="<td align=center style=width:20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['notransaksi']."');\" ></td>";
				}else{					
					$tab.="<td style=width:20px;background-color:red; title=\"Data detail belum ada.\" ></td>";
				}
			}else{
				$tab.="<td style=width:20px></td>";
				$tab.="<td style=width:20px></td>";
				$tab.="<td align=center style=width:20px><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted'></td>";
			}
			$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailExcel('".$bar['notransaksi']."','pdf');\" ></td>";
			$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"preview('".$bar['notransaksi']."','html');\" ></td>";
			$tab.="<td align=center style=width:20px><img src=images/skyblue/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel' onclick=\"detailExcel('".$bar['notransaksi']."','excel');\" ></td>";
		}
		
		$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {$totrows = 1;}
		$isiRow=$footd="";
        for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
        $footd.="</tr><tr><td colspan=14 align=center>";
        if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";}
        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";}
        $footd.="</td></tr>";

        echo $tab . "####" . $footd;
	break;
	
	case'posting':
	try {
	$owlPDO->beginTransaction();
		$strx = "update ".$dbname.".sdm_tiket set status='1' where `notransaksi`='".$param['notransaksi']."'";
		$owlPDO->exec($strx);
		
		#jika ada pengajuan UM buatkan notifikasinya
		$rupiah=0;
		$strum="select sum(totalbiaya) as totalbiaya,kodeorg from ".$dbname.".sdm_tiket where notransaksi='".$param['notransaksi']."'";
		$resum=fetchdata($strum)[0];
		$rupiah=$resum['totalbiaya'];
		$kodeorg=$resum['kodeorg'];


		if($rupiah>0){
			#ambil datakaryawan KTU, Kasir, Finance, Accounting
			$wh="";
			$wh=" and (kodejabatan in ('9','62','30','31','51','52','58') or bagian in ('FNC','ACT'))";
			
			$strn="select * from ".$dbname.".datakaryawan where lokasitugas='".$kodeorg."' ".$wh."";
			$resn=fetchdata($strn);
			if(count($resn)>0){							
				foreach($resn as $barn){
					$msgdt = "Ada permintaan pembelian tiket dengan nomor ".$param['notransaksi'].", sebesar Rp. ".number_format($rupiah)."";
					
					createnotif($notransaksi,'TKT',$msgdt,$barn['karyawanid'],date('Y-m-d H:i:s'));
				}
			}
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;
	case'deldetail':
	try {
		$owlPDO->beginTransaction();
		$str="delete from ".$dbname.".sdm_tiketdt where id='".$param['id']."' and notransaksi='".$param['notransaksi']."'";
		$owlPDO->exec($str);
		
		$t = "select sum(biaya) as biaya from ".$dbname.".sdm_tiketdt  where notransaksi='".$param['notransaksi']."'";
		$e = fetchdata($t)[0];
		if($e['biaya']!=''){$rp=$e['biaya'];}else{$rp=0;}
		
		$data = array(
			'totalbiaya' => $rp,
			'updatedby'  => $_SESSION['standard']['userid'],
			'updatedtime'=> date("Y-m-d H:i:s")
		);
		$where = "notransaksi='".$param['notransaksi']."'";
		$query = updateQuery($dbname,'sdm_tiket',$data,$where); #exit("error".$query);
		$owlPDO->exec($query);
		
		$str="select * from ".$dbname.".listfile_tiket where notransaksi='".$param['notransaksi']."' and idtrans='".$param['id']."'";
		$res = fetchdata($str);
		foreach($res as $bar){			
			$str="delete from ".$dbname.".listfile_tiket where notransaksi='".$bar['notransaksi']."' and namafile='".$bar['namafile']."' and idtrans='".$bar['idtrans']."'";

			$owlPDO->exec($str);
			$pathx = $path.$bar['namafile'];
			if(file_exists($pathx)){
				unlink($pathx);
			}
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;
	case'del':
	try {
		$owlPDO->beginTransaction();
		
		$str="delete from ".$dbname.".sdm_tiket where notransaksi='".$param['notransaksi']."'";
		$owlPDO->exec($str);
		
		$str="select * from ".$dbname.".listfile_tiket where notransaksi='".$param['notransaksi']."'";
		$res = fetchdata($str);
		foreach($res as $bar){			
			$str="delete from ".$dbname.".listfile_tiket where notransaksi='".$bar['notransaksi']."' and namafile='".$bar['namafile']."' and idtrans='".$bar['idtrans']."'";

			$owlPDO->exec($str);
			$pathx = $path.$bar['namafile'];
			if(file_exists($pathx)){
				unlink($pathx);
			}
		}
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;
	case'preview':
		$tab="";
		if($param['tipe']!='pdf'){			
			// $tab.="<fieldset>";
		}else{
			$tab.="<table width=100%><td style=\"text-align:center;width:100%;text-weight:bold;font-size:25px;text-decoration:underline;\">Ticket Request Form</td></table>";
		}
		$str = "SELECT *  FROM " . $dbname . ".sdm_tiket where notransaksi='".$param['notransaksi']."'";
		$bar = fetchdata($str)[0];
		
		$arsumber=array('1'=>$_SESSION['lang']['perjalanandinas'],'2'=>$_SESSION['lang']['lain']);
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
		if($param['tipe']=='pdf'){				
			$fontsize="10px";
			$style="cellpadding=1 cellspacing=0 style=\"font-family:sans-serif;font-size:".$fontsize."\"";
		}
		
		$s = "SELECT *  FROM " . $dbname . ".keu_kasbankht where notransaksi='".$bar['noreferensi']."'";
		$b = fetchdata($s)[0];
		
		$tab.="<table ".$style.">";
		$tab.="<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td>".$bar['notransaksi']."</td>
				</tr><tr>	
					<td>".$_SESSION['lang']['kodeorganisasi']."</td>
					<td>:</td>
					<td>".$nmorg[$bar['kodeorg']]."</td>
				</tr>";
		
		$tab.="<tr>
					<td>".$_SESSION['lang']['sumber']."</td>
					<td>:</td>
					<td>".$arsumber[$bar['sumber']]."</td>
				</tr><tr>		
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
				</tr>
				<tr>		
					<td>".$_SESSION['lang']['noreferensi']."</td>
					<td>:</td>
					<td onclick=tampilDetail('".$bar['noreferensi']."','".$b['noakun']."','".$b['tipetransaksi']."','".$b['kodeorg']."'); style=cursor:pointer;color:blue; title=\"Click untuk melihat bukti pembayaran.\">".$bar['noreferensi']."</td>
				</tr>";		
			
			
		$tab.="</table><br>";
		if($param['tipe']=='pdf'){				
			$fontsize="10px";
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
		}elseif($param['tipe']=='excel'){
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
		}else{
			$style="cellpadding=5 cellspacing=1 border=0 class=sortable width=100%";
		}
		$tab.="<table ".$style.">
				<thead><tr class=rowheader>";
			$rows="rowspan=2";	
			$tab.="<th align=center ".$rows." width=20px>No</th>
				<th align=center ".$rows.">".$_SESSION['lang']['jenis']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['noreferensi']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['nama']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['kodegolongan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tanggal']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['tgldibutuhkan']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['supplier']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['jumlah']."</th>
				<th align=center ".$rows." >".$_SESSION['lang']['keterangan']."</th>
			</tr>
			<tr class=rowheader>
			</thead>
				<tbody>";
		$str = "SELECT *  FROM " . $dbname . ".sdm_tiketdt where notransaksi='".$param['notransaksi']."' order by id asc ";
		$res = fetchdata($str);
		$no=0;
		foreach ($res as $bar){
			$no++;
			
			$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['supplierid']."'");
			$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['nama']."'");
			$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$bar['golongan']."'");
			if($nmkar[$bar['nama']]!=''){
				$nama=$nmkar[$bar['nama']];
			}else{
				$nama=$bar['nama'];
			}
			if($nmgol[$bar['golongan']]!=''){
				$gol=$nmgol[$bar['golongan']];
			}else{
				$gol=$bar['golongan'];
			}
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$nmjns[$bar['jenis']]."</td>";
			if($bar['notranspjd']!=''){				
				$tab.="<td align=left>".$bar['notranspjd']."</td>";
			}else{
				$tab.="<td align=left>Lainnya</td>";
			}
			$tab.="<td align=left>".$nama."</td>";
			$tab.="<td align=left>".$gol."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tgldinasdari'])." s/d ".tanggalnormal($bar['tgldinassampai'])."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=left>".$nmsup[$bar['supplierid']]."</td>";
			$tab.="<td align=right>".number_format($bar['biaya'])."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
			$ttl+=$bar['biaya'];
			$tab.="</tr>";
		}
		
		$tab.="<tr class=rowcontent style=vertical-align:top;>";
		$tab.="<td align=center colspan=8>T O T A L</td>";
		$tab.="<td align=right>".number_format($ttl)."</td>";
		$tab.="<td></td>";
		$tab.="</tr>";
				
		$tab.="</tbody></table>";
		
		#list file
		if($param['tipe']=='pdf'){				
			$fontsize="10px";
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			$tab.="<br>";
		}elseif($param['tipe']=='excel'){
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			$tab.="<br>";
		}else{
			$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
			$tab.="<hr>";
		}
		$tab.="<table ".$style.">
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center'>File Type</th>
					<th align='center'>Filename</th>
					<th align='center' width=50px colspan=1>Action</th>
				</tr>
				</thead><tbody>";
				
			$str="select * from ".$dbname.".listfile_tiket where notransaksi = '".$param['notransaksi']."' and status='1'";
			$res=fetchData($str);
			if(empty($res)){
				$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			}else{
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					$tab.="<tr class=rowcontent>
							<td style='text-align:center'>".$no."</td>";
					$icon=seticonfile($val['formaticon']);
					$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
						</td>";
					$nfile = $val['namafile'];
					$tab.="<td style='text-align:left;cursor:pointer'>".$nfile."</td>";
					if($param['tipe']=='html'){						
						$tab.="<td align=center width=20px>
								<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
					}else{
						$tab.="<td align=center width=20px></td>";
					}
					
					$tab."</tr>";
				}
			}
		
		$tab.="</tbody></table>";
		
		#approval
		/* if($param['tipe']=='pdf'){				
			$fontsize="10px";
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			$tab.="<br>";
		}elseif($param['tipe']=='excel'){
			$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			$tab.="<br>";
		}else{
			$style="cellpadding=0 cellspacing=1 border=0 class=sortable";
			$tab.="<hr>";
		}
		
		$tab.="<span style=\"font-family:sans-serif;font-size:".$fontsize."\">".$_SESSION['lang']['approval_status']."</span><table ".$style.">
		<thead>
		<tr class=rowheader>";
			$tab.="
			<td align=center style=font-weight:bold; ".$row." width=20px>No</td>
			<td align=center style=font-weight:bold; ".$row." >".$_SESSION['lang']['nama']."</td>
			<td align=center style=font-weight:bold; ".$row." >".$_SESSION['lang']['status']."</td>
			<td align=center style=font-weight:bold; ".$row." >".$_SESSION['lang']['tanggal']."</td>
			<td align=center style=font-weight:bold; ".$row." >".$_SESSION['lang']['note']."</td>
		</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";
			
		$arrHslx=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['reconfirm']);
		
		$kdorg = makeOption($dbname, 'sdm_tiket', 'notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");
		$kodeorg = $kdorg[$param['notransaksi']];
		
		$countApprove= getCountApproval('TKT',$kodeorg);
		if($countApprove>0){			
			for($i=1;$i<=$countApprove;$i++){
			$arrApp = detailApprove($i,$param['notransaksi'],'TKT');
				if($arrApp['nama']!=''){						
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$i."</td>";
					$tab.="<td align=left>".$arrApp['nama']."</td>";
					$tab.="<td align=left>".$arrHslx[$arrApp['status']]."</td>";
					$tab.="<td align=center>".tanggalnormald($arrApp['tanggal'])."</td>";
					$tab.="<td align=left>".$arrApp['komentar']."</td>";
					$tab.="</tr>";
				}
			}
		} */
		
		if($param['tipe']!='pdf'){			
			$tab.="</fieldset>";
		}
		
		if($param['tipe']=='pdf'){		
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
			$dompdf->stream(str_replace("/","",$param['notransaksi']),array("Attachment"=>0));
		}elseif($param['tipe']=='html'){
			echo $tab;				
		}else{
			$nop = str_replace("/","",$param['notransaksi']).".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet(str_replace("/","",$param['notransaksi']), $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		
	break;
	case'form_ajukan':
		$wh="a.karyawanid!='".$_SESSION['standard']['userid']."'";
		
		$kodeorg=makeOption($dbname,'sdm_tiket','notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");
		$countApp = getCountApproval($param['kodeapproval'], $kodeorg[$param['notransaksi']]);
		$hide="";
		if(($countApp-1)==0){
			#ini BOD
			$hide="hidden";
		}
		
		$level="and a.level='1'";
		
		$optKry="";
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 
			and a.jenispersetujuan='".$param['kodeapproval']."' ".$level." and a.kodeunit='".$kodeorg[$param['notransaksi']]."' order by b.namakaryawan asc";
		$res = fetchdata($str);
		foreach($res as $rkry){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}

	$optjns=makeOption($dbname,'setup_jenisapproval','jenis,nama');
	$tab = "<table cellspacing=1 border=0 width=100%>
				<input hidden id=kodeapprovalaju value='".$param['kodeapproval']."'>
	
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['notransaksi']."</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$param['notransaksi']."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['jenis']."</td>
					<td width=5px>:</td>
					<td>".$optjns[$param['kodeapproval']]."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
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
		$notransaksi = checkPostGet('notransaksi','');
		$kepada      = checkPostGet('kepada','');
		$kodeapproval= checkPostGet('kodeapproval','');
		
		
		$kodeorg=makeOption($dbname,'sdm_tiket','notransaksi,kodeorg',"notransaksi='".$param['notransaksi']."'");
		$countApp = getCountApproval($param['kodeapproval'], $kodeorg[$param['notransaksi']]);
		
		#karyawan biasa
		if($kepada=='' or $notransaksi==''){
			throw new PDOException('Isikan nama penyetuju.');
		}
		
		# cari dulu apakah sudah pernah di ajukan sebelumnya
		$tglhi = date("Ymd");
		
		$str="select * from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['notransaksi']!=''){
				# jika ada pindahkan ke table ini
				$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
				$owlPDO->exec($str);
			}
		}
		
		#kemudian setelah di pindah, hapus persetujuan lama
		$str="delete from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."'";
		$owlPDO->exec($str);
		
		# update flag menjadi 9
		$str = "update " . $dbname . ".sdm_tiket set status='9', postingtime='" . date('Y-m-d H:i:s') . "', "."diajukanoleh='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'"; 
		#exit("error".$str);
		$owlPDO->exec($str);

		# insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
				`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				 values ('','".$notransaksi."','".$kodeapproval."','1','" . $kepada."','0','','','')";
		$owlPDO->exec($str);
		

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	case 'showupload':
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."','".$param['id']."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p/>";
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center' width=40px>File Type</th>
					<th align='center'>Filename</th>
					<th align='center' width=50px colspan=2>Action</th>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
	break;
	case 'submitfile':
		$data = $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = str_replace(" ","_",$_FILES['file']['name']);
				$str="select * from ".$dbname.".listfile_tiket where namafile = '".$filename."'";
				if(count(fetchData($str))>0){
					exit("Warning : Nama file sudah pernah digunakan untuk transaksi yang lain, silahkan rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfile_tiket (`notransaksi`, `idtrans`, `namafile`, `formaticon`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$param['id']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".$tgl."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file tidak mendukung.");
				}
			}
		}
	break;
	case 'loadfiles':
		$no = 0;
		$tab = $wh = "";
		
		if($param['id']!=''){
			$wh=" and idtrans='".$param['id']."'";
		}
		$str="select * from ".$dbname.".listfile_tiket where notransaksi = '".$param['notransaksi']."' and status='1' ".$wh."";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$nfile = $val['namafile'];
				$tab.="<td style='text-align:left;cursor:pointer'>".$nfile."</td>
					<td align=center width=20px>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
				$tab.="<td align=center width=20px>
					<img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['idtrans']."','".$val['namafile']."');\" >";
				$tab."	</td>
					</tr>";
			}
		}
	echo $tab;
	break;
	case 'deletefile':
		$str="delete from ".$dbname.".listfile_tiket where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."' and idtrans='".$param['id']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			unlink($pathx);
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
}
?>