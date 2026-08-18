<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$table = checkPostGet('table','');
$method = ($method==''?$table:$method);
$pages = checkPostGet('page','');

$crnotransaksi = checkPostGet('crnotransaksi','');
$crtanggal = checkPostGet('crtanggal','');

$notransaksi = checkPostGet('notransaksi','');
$unit = checkPostGet('unit','');
$tanggal = checkPostGet('tanggal','');
$keterangan = checkPostGet('keterangan','');

$srcpo = checkPostGet('srcpo','');
$nopo = checkPostGet('nopo','');
$nopp = checkPostGet('nopp','');
$kodebarang = checkPostGet('kodebarang','');
$jumlahterima = checkPostGet('jumlahterima','');
$nourut = checkPostGet('nourut','');


switch ($method){
	case'addpo';
		$tab='';
		
		$tab.="<table>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>
					<input type='text' id='srcpo' class='myinputtext' style='width:145px;' />
				</td>
				<td>
					<button class=mybutton onclick=\"searchpo()\">".$_SESSION['lang']['find']."</button>
				</td>
			</tr>
		</table>
		
		<table class=sortable cellspacing=1 cellpadding=2 border=0>
			<thead>
			<tr class=rowheader style='text-align:center'>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>Jumlah Item</td>
			</tr>
			</thead>
			<tbody id='srclistpo'>
			</tbody>
		</table>";
		
		echo $tab;
	break;
	
	case'searchpo':
		$tab="";
		
		if(strlen($srcpo) < 3){
			exit("Warning, Jumlah karakter cari minimal 3 karakter");
		}

		$str_f="SELECT id_franco FROM ".$dbname.".`setup_franco` where kodeunit LIKE '%".$unit."%'";
		$res_f=fetchdata($str_f);
		$tipeOrg = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");
		foreach($res_f as $val) {
			if ($tipeOrg[$unit] != "HOLDING" || $tipeOrg[$unit] != "KANWIL") {
				@$idfranco[$val['id_franco']] = $val['id_franco'];
			}
		}
		if (!empty($idfranco)) {
			$whereF = "AND idfranco IN ('".implode("','",$idfranco)."')";
		} else {
			$whereF = "";
		}

		// Ambil unit kanwil dan ho, untuk kebutuhan franco
		$str_0 = "SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe IN ('HOLDING','KANWIL')";
		$res_0 = fetchdata($str_0);
		$nofr = 0;
		$whereFr = "";
		foreach ($res_0 as $val) {
			$whereFr .= "kodeunit LIKE '%" . $val['kodeorganisasi'] . "%' OR "; // Menggabungkan klausa WHERE dengan OR
		}
		// Hapus 'OR' yang terakhir karena tidak diperlukan
		$whereFr = rtrim($whereFr, " OR ");
		
		$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
		$pt = "/".$optpt[$unit];
		$jnspo = '/PO';
		// $str="select nopo from ".$dbname.".log_poht where nopo like '%".$srcpo."%' and nopo like '%".$pt."' and nopo like '%".$jnspo."%' and stat_release='1'";
		// echo $str="select nopo from ".$dbname.".log_poht where nopo like '%".$srcpo."%' ".$whereF." and 
		// idfranco IN (SELECT id_franco FROM setup_franco WHERE kodeunit IN (SELECT kodeorganisasi FROM organisasi WHERE tipe in ('HOLDING','KANWIL') )) 
		// and nopo like '%".$pt."' and nopo like '%".$jnspo."%' and stat_release='1'";
		$str="select nopo from ".$dbname.".log_poht where nopo like '%".$srcpo."%' ".$whereF." and 
		idfranco IN (SELECT id_franco FROM ".$dbname.".setup_franco WHERE ".$whereFr." )
		and nopo like '%".$pt."' and nopo like '%".$jnspo."%' and stat_release='1' ";
		$res=fetchdata($str);
		if(count($res) > 0){
			$countitem = 0;
			foreach($res as $val){
				$valnopp = "";
				
				$strx="select nopp,kodebarang,jumlahpesan from ".$dbname.".log_podt where nopo='".$val['nopo']."' order by nopp asc";
				$resx=fetchdata($strx);
				$nox=0;
				$jlhitem = 0;
				$tempitem = "";
				foreach($resx as $valx){
					$jlhpesan = $valx['jumlahpesan'];
					
					$strxx="select kodebarang,sum(jumlah) as jumlah from ".$dbname.".log_transaksidt where nopo='".$val['nopo']."' and nopp='".$valx['nopp']."' and kodebarang='".$valx['kodebarang']."'";
					$resxx=fetchdata($strxx);
					$jlhrealisasi = (($resxx[0]['jumlah']==''||is_null($resxx[0]['jumlah']))?0:$resxx[0]['jumlah']);
					
					$strxx="select kodebarang,sum(qty) as qty from ".$dbname.".log_transit where nopo='".$val['nopo']."' and nopp='".$valx['nopp']."' and kodebarang='".$valx['kodebarang']."' and statusterima='0' and status='0'";
					$resxx=fetchdata($strxx);
					$jlhrealisasi = $jlhrealisasi + (($resxx[0]['qty']==''||is_null($resxx[0]['qty']))?0:$resxx[0]['qty']);
					
					if($jlhpesan > $jlhrealisasi){
						$jlhitem++;
						
						if($tempitem!=$valx['nopp']){
							$nox++;
							if($nox==1){
								$valnopp.=$nox.".".$valx['nopp'];
							}else{
								$valnopp.="<br>".$nox.".".$valx['nopp'];
							}
						}
						$tempitem = $valx['nopp'];
					}
				}
				
				if($valnopp!=''){
					$tab.="<tr class=rowcontent style='cursor:pointer' title='Klik untuk menambahkan item' onclick=\"insertpo('".$val['nopo']."')\">
						<td align=left>".$val['nopo']."</td>
						<td align=left>".$valnopp."</td>
						<td align=center>".$jlhitem."</td>
					</tr>";
					$countitem++;
				}
			}
			if($countitem==0){
				$tab="<tr class=rowcontent>
					<td colspan=3 align=center>".$_SESSION['lang']['datanotfound']."</td>
				</tr>";
			}
		}else{
			$tab.="<tr class=rowcontent>
				<td colspan=3 align=center>".$_SESSION['lang']['datanotfound']."</td>
			</tr>";
		}
		
		echo $tab;
	break;
	
	case'insertpo':
		$tab="";
		
		$_SESSION['transitpo'] = array();
		
		$no=0;
		$str="select nopo,nopp,kodebarang,jumlahpesan,satuan from ".$dbname.".log_podt where nopo='".$nopo."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			
			$strx="select sum(jumlah) as jumlah from ".$dbname.".log_transaksidt where nopo='".$val['nopo']."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			$jlhrealisasi = ($resx[0]['jumlah']==''?0:$resx[0]['jumlah']);
			
			$strx="select sum(qty) as qty from ".$dbname.".log_transit where nopo='".$val['nopo']."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."' and statusterima='0' and status='0'";
			$resx=fetchdata($strx);
			$jlhrealisasi = $jlhrealisasi + ($resx[0]['qty']==''?0:$resx[0]['qty']);
			
			$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			$newdata = array(
				'no'=>$no,
				'nopo'=>$val['nopo'],
				'nopp'=>$val['nopp'],
				'kodebarang'=>$val['kodebarang'],
				'namabarang'=>$optnmbarang[$val['kodebarang']],
				'satuan'=>$val['satuan'],
				'jumlahpesan'=>$val['jumlahpesan'],
				'jlhrealisasi'=>$jlhrealisasi,
				'jumlahterima'=>'0'
			);
			
			array_push($_SESSION['transitpo'],$newdata);
		}
		
		foreach($_SESSION['transitpo'] as $key=>$val){
			$tab.="<tr class=rowcontent>
				<td style='text-align:right'>".$val['no']."</td>
				<td id='nopo_".$val['no']."'>".$val['nopo']."</td>
				<td id='nopp_".$val['no']."'>".$val['nopp']."</td>
				<td id='kodebarang_".$val['no']."'>".$val['kodebarang']."</td>
				<td>".$val['namabarang']."</td>
				<td style='text-align:center'>".$val['satuan']."</td>
				<td style='text-align:right' id='jumlahpesan_".$val['no']."'>".hidezerodecimal($val['jumlahpesan'],0)."</td>
				<td style='text-align:right' id='jlhrealisasi_".$val['no']."'>".hidezerodecimal($val['jlhrealisasi'],0)."</td>
				<td style='text-align:center'>
					<input type=number id='jumlahterima_".$val['no']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" onblur=\"updateqty('".$val['no']."')\"  style='width:80px;' maxlength=100 placeholder='0'>
				</td>
			</tr>";
		}
		
		echo $tab;
	break;
	
	case'updateqty':
		foreach($_SESSION['transitpo'] as $key=>$val){
			if($val['no']==$nourut){
				$_SESSION['transitpo'][$key]['jumlahterima']=$jumlahterima;
			}
		}
	break;
	
	case'cleardt':
		$_SESSION['transitpo'] = array();
	break;
	
	case 'deltransit':
		// $str = "update ".$dbname.".log_transit set status='1' where notransaksi='".$notransaksi."'";
		$str="delete from ".$dbname.".log_transit where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case 'postingtransit':
		// new : ada bentuk jurnal
				

		try {
			$owlPDO->beginTransaction();
	
				$str = "select * from ".$dbname.".log_transit where notransaksi='".$notransaksi."'";
				$res=fetchdata($str);
				$tanggal = $res[0]['tanggal'];
				$unit = substr($res[0]['unit'],0,4);
				$kodebarang = $res[0]['kodebarang'];
				$nopo = $res[0]['nopo'];
				$post = $res[0]['posting'];
	
				$str2 = "select * from ".$dbname.".log_poht where nopo='".$nopo."'";
				$res2=fetchdata($str2);
				$supplerid = $res2[0]['kodesupplier'];
				$diskonpersen = $res2[0]['diskonpersen'];
				$persenppn = $res2[0]['persenppn'];
				$persenpph = $res2[0]['persenpph'];
				$pbbkb = $res2[0]['pbbkb'];
				
				## GET FRANCO INVOICE PO
				$unitinvc=getUnitInvPO($nopo);
				
				## CREATE JURNAL PENERIMAAN TRANSIT
				$kodejurnal="GRTRS";
				$kodekl = "SUPPLIER";
				$noakungrir='2111401';
				// cek apakah ada di klsup
				$str0 = "select tipesub from ".$dbname.".log_poht where nopo='".$nopo."' ";				
				$res0 = fetchData($str0);
				$tipesub = $res0[0]['tipesub'];	
				if($tipesub != ''){
					$str1 = "select noakun from ".$dbname.".log_5klsupplier where noakun!='' and tipe='".$tipesub."' ";
					$res1=fetchdata($str1);
					if(count($res1) > 0){
						$noakungrir = $res1[0]['noakun'];
					}
				}


				// transit
				//noaruskas komoditi
                $sappl="select * from ".$dbname.".setup_parameterappl where kodeaplikasi='GR' and kodeparameter='AKUNGRTR'";
                $rappl = fetchData($sappl);
                $noakundebet=$rappl[0]['nilai'];
				if($noakundebet != ''){
					$noakundebet=$rappl[0]['nilai'];
				}else{
					$noakundebet='2111401';
				}
				$noakunkr=$noakungrir;
				$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
				
				if($noakunkr==''){
					throw new PDOException("No. Akun masih kosong kredit masih kosong, silahkan cek di setup kelompok supplier, jika memakai konsep GR/IR cek juga akun GR/IR disetup tersebut");
				}
				
				## GET AKUN INTRA UNIT ASAL DAN TUJUAN
				$str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$unitinvc."' and jenis = 'intra'";
				$res=fetchdata($str);
				$akuncacotujuan=$res[0]['akunpiutang'];
				
				$str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$unit."' and jenis = 'intra'";
				$res=fetchdata($str);
				$akuncacoasal=$res[0]['akunhutang'];
				
				## CEK UNIT TUJUAN SUDAH CLOSING?
				$periode=substr($tanggal,0,4)."-".substr($tanggal,5,2);
				$str = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".$unitinvc."'";
				$res=fetchdata($str);
				$close = $res[0]['tutupbuku'];
				if ($close == '1'){
					throw new PDOException($unitinvc." sudah tutup buku");
				}
				## GET COUNTER JURNAL
				$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodekelompok='".$kodejurnal."' and kodeunit='".$unit."' and periode='".$periode."'";
				$res = fetchdata($str);
				$konter = $res[0]['nokounter'];
				if(count($res) <= 0){
					exit("warning : Belum ada setup kelompok jurnal untuk kode ".$kodejurnal." di unit ".$unit." periode ".$periode." ");
				}
				
				$str = "select nokounter from ".$dbname.".keu_5kelompokjurnal where kodekelompok='".$kodejurnal."' and kodeunit='".$unitinvc."' and periode='".$periode."'";
				$res = fetchdata($str);
				$kontertujuan = $res[0]['nokounter'];
				if(count($res) <= 0){
					exit("warning : Belum ada setup kelompok jurnal untuk kode ".$kodejurnal." di unit ".$unitinvc." periode ".$periode." ");
				}
				$data=array();
				$datatujuan=array();
				$noUrut=1;
				$notemp++;
				$notemptujuan++;
				$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
				
				# PREPATION NO JURNAL
				$nojurnal = str_replace('-', '',$tanggal)."/".$unit."/".$kodejurnal."/".addZero($konter+$notemp,3);
				$nojurnaltujuan = str_replace('-', '',$tanggal)."/".$unitinvc."/".$kodejurnal."/".addZero($kontertujuan+$notemptujuan,3);
				
					$str="select nopo,nopp,kodebarang,jumlahpesan,satuan,hargasatuan,hargasbldiskon from ".$dbname.".log_podt where nopo='".$nopo."'";
					$res=fetchdata($str);
					foreach($res as $val){
						$qtypo_jumlahpesan_v2+=$val['jumlahpesan'];
					}
	
					$str="select nopo,nopp,kodebarang,jumlahpesan,satuan,hargasatuan,hargasbldiskon from ".$dbname.".log_podt where nopo='".$nopo."'";
					$res=fetchdata($str);
					foreach($res as $val){
						$qtypo_jumlahpesan+=$val['jumlahpesan'];
	
						$strx="select * from ".$dbname.".log_transit where notransaksi='".$notransaksi."' and kodebarang='".$val['kodebarang']."' and nopp ='".$val['nopp']."' ";
						$resx=fetchdata($strx);
						foreach($resx as $valx){
							
							// hargasbldiskon karena dibawah ada ada proporsi diskon nya
							$hargarp+=$valx['qty']*$val['hargasbldiskon'];
							$qtypo+=$valx['qty'];
							
							$qtypo_v2=$valx['qty'];
							$hargarp_v2=$valx['qty']*$val['hargasbldiskon'];
								
							// add
							// penambahan biaya lain" 
							// biaya lain"
							// $strT="select nilai from ".$dbname.".log_ot where nopo='".$nopo."'";
							// $resT=fetchdata($strT);
							// $nilaibiayalain=$resT[0]['nilai'];
	
							// $proporsiBl = 0;
							// if($nilaibiayalain > 0){
							// 	$proporsiBl = ($qtypo_v2/$qtypo_jumlahpesan_v2)*$nilaibiayalain;
							// }
							// Diskon
							$nilaidiskon = 0;
							if($diskonpersen > 0){
								$nilaidiskon = ($hargarp_v2*$diskonpersen)/100;
							}
							// PPN
							$nilaippn = 0;
							if($persenppn > 0){
								$nilaippn = ( ($hargarp_v2-$nilaidiskon) * $persenppn)/100;
							}
							// PPH
							$nilaipph = 0;
							if($persenpph > 0){
								$nilaipph = ( ($hargarp_v2-$nilaidiskon) * $persenpph)/100;
							}
							// PBBKB
							$nilaipbbkb = 0;
							if($pbbkb > 0){
								$nilaipbbkb = ($qtypo_v2/$qtypo_jumlahpesan_v2)*$pbbkb;
							}
	
	
	
							// $hargarp=$hargarp+$nilaippn+round($nilaipbbkb)+round($proporsiBl) - $nilaidiskon - $nilaipph;
							$hargarp_v2untukHT+=$hargarp_v2+round($nilaipbbkb)+round($proporsiBl) - $nilaidiskon;
							$hargarp_v2=$hargarp_v2+round($nilaipbbkb)+round($proporsiBl) - $nilaidiskon;
	
	
							// UNTUK DETAIL
							#== detail
							#= debet
							$data['detail'][] = array(
								'nojurnal'=>$nojurnal,
								'tanggal'=>$tanggal,
								'nourut'=>$noUrut,
								'noakun'=>$noakundebet,
								'keterangan'=>'Penerimaan Barang Transit No Transaksi : '.$notransaksi.', jumlah: '.hidezerodecimal($hargarp_v2).', PO: '.$nopo.', PR: '.$valx['nopp'].', Barang : '.$optnmbarang[$valx['kodebarang']],
								'jumlah'=>$hargarp_v2,
								'matauang'=>'IDR',
								'kurs'=>'1',
								'kodeorg'=>$unit,
								'kodekegiatan'=>'',
								'kodeasset'=>'',
								'kodebarang'=>$valx['kodebarang'],
								'nik'=>$_SESSION['standard']['userid'],
								'kodecustomer'=>'',
								'kodesupplier'=>$supplerid,
								'noreferensi'=>$notransaksi,
								'noaruskas'=>'',
								'kodevhc'=>'',
								'nodok'=>$nopo,
								'kodeblok'=>'',
								'revisi'=>'0',
								'kodesegment' => $defSegment
							);
	
							$noUrut++;
							
							#= kredit
							// kalo ini RO, langsung ke GRIR 2021
							if($unit==$unitinvc){
								$akuncacotujuan=$noakunkr;
							}
							$data['detail'][] = array(
								'nojurnal'=>$nojurnal,
								'tanggal'=>$tanggal,
								'nourut'=>$noUrut,
								'noakun'=>$noakungrir,
								'keterangan'=>'Penerimaan Barang Transit No Transaksi : '.$notransaksi.', jumlah: '.hidezerodecimal($hargarp_v2).', PO: '.$nopo.', PR: '.$valx['nopp'].', Barang : '.$optnmbarang[$valx['kodebarang']],
								'jumlah'=>$hargarp_v2*(-1),
								'matauang'=>'IDR',
								'kurs'=>'1',
								'kodeorg'=>$unit,
								'kodekegiatan'=>'',
								'kodeasset'=>'',
								'kodebarang'=>$valx['kodebarang'],
								'nik'=>$_SESSION['standard']['userid'],
								'kodecustomer'=>'',
								'kodesupplier'=>$supplerid,
								'noreferensi'=>$notransaksi,
								'noaruskas'=>'',
								'kodevhc'=>'',
								'nodok'=>$nopo,
								'kodeblok'=>'',
								'revisi'=>'0',
								'kodesegment' => $defSegment
							);
							$noUrut++;
	
					}
	
				}

	
				#== header
				#= jurnal ht
				$data['header'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodejurnal,
					'tanggal'=>$tanggal,
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>$hargarp_v2untukHT,
					'totalkredit'=>$hargarp_v2untukHT*(-1),
					'amountkoreksi'=>'0',
					'noreferensi'=>$notransaksi,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
		
				$queryH = insertQuery($dbname,'keu_jurnalht',$data['header']);
				$owlPDO->exec($queryH);
				
				foreach($data['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					$owlPDO->exec($queryD);
				}
				// GRIR
				// if($unit!=$unitinvc){
					// $queryHro = insertQuery($dbname,'keu_jurnalht',$datatujuan['header']);
					// $owlPDO->exec($queryHro);
					
					// foreach($datatujuan['detail'] as $key=>$dataDetro) {
						// $queryDro = insertQuery($dbname,'keu_jurnaldt',$dataDetro);
						// $owlPDO->exec($queryDro);
					// }
				// }
				
				## Get Journal Counter
				$queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>($konter+$notemp)),
					"kodeunit='".$unit."' and  
					periode='".$periode."' and kodekelompok='".$kodejurnal."'");	
				$owlPDO->exec($queryJRB);						
	
				// if($unit!=$unitinvc){
					// $queryJRBro = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>($kontertujuan+$notemptujuan)),
						// "kodeunit='".$ronya."' and  
						// periode='".$periode."' and kodekelompok='".$kodejurnal."'");	
					// $owlPDO->exec($queryJRBro);
				// }
				
				#execute
				if ($post == '0') {
					$str = "update ".$dbname.".log_transit set posting='1', postedby='".$_SESSION['standard']['userid']."',postedtime='".date('Y-m-d H:i:s')."' where notransaksi='".$notransaksi."'";
					$owlPDO->exec($str); 
				} else {
					throw new PDOException($_SESSION['lang']['nodata']);
				}
	
				/*$str = "update ".$dbname.".log_transit set posting='1', postedby='".$_SESSION['standard']['userid']."',postedtime='".date('Y-m-d H:i:s')."' where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($str);
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}*/
	
				// exit("\n<br>error ");
				$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}
			
	break;
	
	case'simpan':
		$periode = explode('-',$tanggal);
		$srcperiode = $periode[2]."-".$periode[1];
		$srcperiode2 = $periode[2]."".$periode[1];
		$srctrk="-TS-".$unit;
				
		if($unit == ''){
			exit("Gagal, Unit Tidak Boleh Kosong...");
		}
		if(count($_SESSION['transitpo']) <= 0){
			exit("Gagal, Daftar PO masih belum ada");
		}else{
			$counterr = 0;
			foreach($_SESSION['transitpo'] as $key=>$val){
				if($val['jumlahterima']<=0){
					$counterr++;
				}
			}
			if(count($_SESSION['transitpo'])==$counterr){
				exit("Gagal, Jumlah realisasi masih belum ada");
			}
		}
		
		$tglskr = date("Y-m-d H:i:s");
		if($notransaksi!=''){
			$str="select createdby,createtime from ".$dbname.".log_transit where notransaksi='".$notransaksi."' limit 1";
			$res=fetchdata($str);
			$createdby=$res[0]['createdby'];
			$createtime=$res[0]['createtime'];
			
			$str="delete from ".$dbname.".log_transit where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
		}else{
			$str="select max(notransaksi) as maxtrk from ".$dbname.".log_transit where notransaksi like '%".$srctrk."' and tanggal like '".$srcperiode."%'";
			$res=fetchdata($str);
			$subnotrk = $res[0]['maxtrk'];
			if($subnotrk==''){
				$notransaksi = $srcperiode2."0001".$srctrk;
			}else{
				$expsubnotrk = explode('-',$subnotrk);
				$nourut = (substr($expsubnotrk[0],6,4)+1);
				$notransaksi = $srcperiode2."".addZero($nourut,4)."".$srctrk;
			}
			
			$createdby=$_SESSION['standard']['userid'];
			$createtime=$tglskr;
		}
		
		foreach($_SESSION['transitpo'] as $key=>$val){
			if($val['jumlahterima'] > 0){
				$str="insert into ".$dbname.".log_transit (notransaksi,unit,tipe,tanggal,kodebarang,satuan,qty,keterangan,nopp,nopo,createdby,createtime,updateby,updatetime) values ('".$notransaksi."','".$unit."','I','".tanggalsystem($tanggal)."','".$val['kodebarang']."','".$val['satuan']."','".$val['jumlahterima']."','".$keterangan."','".$val['nopp']."','".$val['nopo']."','".$createdby."','".$createtime."','".$_SESSION['standard']['userid']."','".$tglskr."')";
				$owlPDO->exec($str);
			}
		}	
		
		echo $notransaksi;
	break;
	
	case'fillField':
		$tab="";
		
		$_SESSION['transitpo'] = array();
		
		$no=0;
		$str="select * from ".$dbname.".log_transit where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			
			$strx="select jumlahpesan from ".$dbname.".log_podt where nopo='".$val['nopo']."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			$jumlahpesan=($resx[0]['jumlahpesan']==''?0:$resx[0]['jumlahpesan']);
			
			$strx="select sum(jumlah) as jumlah from ".$dbname.".log_transaksidt where nopo='".$val['nopo']."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			$jlhrealisasi = ($resx[0]['jumlah']==''?0:$resx[0]['jumlah']);
			
			$strx="select sum(qty) as qty from ".$dbname.".log_transit where nopo='".$val['nopo']."' and nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."' and statusterima='0' and status='0'";
			$resx=fetchdata($strx);
			$jlhrealisasi = $jlhrealisasi + ($resx[0]['qty']==''?0:$resx[0]['qty']);
			
			$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			$newdata = array(
				'no'=>$no,
				'nopo'=>$val['nopo'],
				'nopp'=>$val['nopp'],
				'kodebarang'=>$val['kodebarang'],
				'namabarang'=>$optnmbarang[$val['kodebarang']],
				'satuan'=>$val['satuan'],
				'jumlahpesan'=>$jumlahpesan,
				'jlhrealisasi'=>($jlhrealisasi-$val['qty']),
				'jumlahterima'=>$val['qty']
			);
			
			array_push($_SESSION['transitpo'],$newdata);
		}
		
		foreach($_SESSION['transitpo'] as $key=>$val){
			$tab.="<tr class=rowcontent>
				<td style='text-align:right'>".$val['no']."</td>
				<td id='nopo_".$val['no']."'>".$val['nopo']."</td>
				<td id='nopp_".$val['no']."'>".$val['nopp']."</td>
				<td id='kodebarang_".$val['no']."'>".$val['kodebarang']."</td>
				<td>".$val['namabarang']."</td>
				<td style='text-align:center'>".$val['satuan']."</td>
				<td style='text-align:right' id='jumlahpesan_".$val['no']."'>".hidezerodecimal(($val['jumlahpesan']),0)."</td>
				<td style='text-align:right' id='jlhrealisasi_".$val['no']."'>".hidezerodecimal(($val['jlhrealisasi']),0)."</td>
				<td style='text-align:center'>
					<input type=number id='jumlahterima_".$val['no']."' class=myinputtextnumber onkeypress=\"return angka_doang(event);\" onblur=\"updateqty('".$val['no']."')\"  style='width:80px;' maxlength=100 placeholder='0' value='".$val['jumlahterima']."'>
				</td>
			</tr>";
		}
		
		echo $tab;
	break;
	
	case'loaddata':
		$arrTipe = array('PR'=>'Purchase Request','SR'=>'Service Request');
		$arrorgdet = getOrgDetail(2);
		$where = "1=1";
		
		if($crnotransaksi!=''){
			$where.=" and notransaksi like '%".$crnotransaksi."%'";
		}
		
		if($crtanggal!=''){
			$where.=" and tanggal = '".tanggalsystem($crtanggal)."'";
		}
	
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$no=(($page*$limit));
		
		$str="select count(notransaksi) as countitem from ".$dbname.".log_transit where ".$where." and unit in (".$arrorgdet.") and tipe='I' and status='0' group by notransaksi";
		$res=fetchdata($str);
		$jlhbrs = $res[0]['countitem'];
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='10' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{			
			$str="select * from ".$dbname.".log_transit where ".$where." and unit in (".$arrorgdet.") and tipe='I'  and status='0' group by notransaksi order by posting asc, tanggal desc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				## GET PT
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$val['unit']."'");
				$pt = $optpt[$val['unit']];
				
				## GET CREATED BY
				$optpembuat = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createdby']."'");
				$pembuat = $optpembuat[$val['createdby']];
				$optpostedby = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['postedby']."'");
				$postedby = $optpostedby[$val['postedby']];
				
				$tab.="<tr class=rowcontent id='tr_".$no."'>
					<td align=center>".$no."</td>
					<td align=center>".$val['notransaksi']."</td>
					<td align=center>".$pt."</td>
					<td align=center>".$val['unit']."</td>
					<td align=center>".$val['nopo']."</td>
					<td align=center style='min-width:80px'>".tanggalnormal($val['tanggal'])."</td>
					<td align=center>".$pembuat."</td>
					<td align=center>".$postedby."</td>";
					
				if($val['posting']=='0'){
					$tab.="<td align=center>
						<img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"postingtransit('".$val['notransaksi']."');\" >
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$val['notransaksi']."','".$val['unit']."','".tanggalnormal($val['tanggal'])."','".$val['keterangan']."');\" >
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deltransit('".$val['notransaksi']."');\" >
						<!--<img onclick=\"previewDetail('".$val['nopp']."',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">-->
						<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pdf','".$val['notransaksi']."','','log_slave_transit',event);\">
					</td>";
				}else{
					$tab.="<td align=center>
						<img src=images/skyblue/posted.png class=resicon  title='Posted'>
						<!--<img onclick=\"previewDetail('".$val['nopp']."',event);\" title=\"Detail PP\" class=\"resicon\" src=\"images/zoom.png\">-->
						<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pdf','".$val['notransaksi']."','','log_slave_transit',event);\">
					</td>";
				}					
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,'10','loadData','getPage');
			$tab.="</table>";
		}
			// echo gethostbyaddr($_SERVER['REMOTE_ADDR']);
		echo $tab;
	break;	
	
	case'pdf':
		$notransaksi=checkPostGet('column','');
		
		$no=0;
		$str="select * from ".$dbname.".log_transit where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			
			if($no=='1'){
				$unit=$val['unit'];
				$nopo=$val['nopo'];
				$createdby=$val['createdby'];
				$postedby=$val['postedby'];
				$tanggal=$val['tanggal'];
				$hari = hari($tanggal,'ID');
				
				$optkdsup = makeOption($dbname,'log_poht','nopo,kodesupplier',"nopo='".$nopo."'");
				$optsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$optkdsup[$nopo]."'");
				$supplier=$optsup[$optkdsup[$nopo]];
			}
			
			$strx="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			
			$tabx.="<tr>
				<td style='text-align:right'>".$no."</td>
				<td style='text-align:center'>".$val['kodebarang']."</td>
				<td style='text-align:left'>".$resx[0]['namabarang']."</td>
				<td style='text-align:center'>".$val['satuan']."</td>
				<td style='text-align:right'>".hidezerodecimal($val['qty'],2)."</td>
				<td style='text-align:center'>".$val['nopp']."</td>
			</tr>";
		}
		
		## UNTUK KOP SURAT
		$tab.=headerreport($unit);
		
		## CONTENT
		$tab.="<table width=100% style=\"font-family:'Arial Narrow',Arial,sans-serif;\">
			<tr>
				<td style='text-align:center;font-size:18;'>BUKTI PENERIMAAN BARANG</td>
			</tr>
		</table><p>";
		
		$tab.="<table cellpadding=3 style=\"font-family:'Arial Narrow',Arial,sans-serif;font-size:10\" width='100%'>
			<tr>
				<td style='width:80px'>".$_SESSION['lang']['notransaksi']."</td>
				<td style='width:2px'>:</td>
				<td>".$notransaksi."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>".$unit."</td>
			</tr>
			<tr>
				<td colspan=3 style='padding-top:20px'>Pada hari ini, ".$hari.", ".tanggalnormal($tanggal)." ".$unit." telah menerima barang dari ".$supplier.", dengan detail sebagai berikut:</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>".$nopo."</td>
			</tr>
		</table>";
		
		$tab.="<table cellpadding=3 cellspacing=0 border=0.3 width=100% style=\"font-family:'Arial Narrow',Arial,sans-serif;font-size:10;\">
			<tr style='text-align:center;background-color:#DCDCDC;'>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['kuantitas']."</td>
				<td>".$_SESSION['lang']['nopp']."</td>
			</tr>
			".$tabx."";
		
		
			
		$tab.="</table>
		<p>";
		
		$optnmcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$createdby."'");
		$optnmposted = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$postedby."'");
		$createdby = $optnmcreate[$createdby];
		$postedby = $optnmcreate[$postedby];
		
		$tab.="<table cellpadding=3 style=\"font-family:'Arial Narrow',Arial,sans-serif;font-size:10\" width=100%>
			<tr>
				<td style='width:50%;text-align:center'>".$_SESSION['lang']['dibuat']."</td>
				<td style='width:50%;text-align:center'>".$_SESSION['lang']['posting']."</td>
			</tr>
			<tr>
				<td style='height:50px;text-align:center'>&nbsp;</td>
				<td style='text-align:center'>&nbsp;</td>
			</tr>
			<tr>
				<td style='width:50%;text-align:center'>".$createdby."</td>
				<td style='width:50%;text-align:center'>".$postedby."</td>
			</tr>
		</table>";
	
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream("Penerimaan Barang Transit",array("Attachment"=>0));
	break;
}
?>
