<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$method         = checkPostGet('method', '');
$pages          = checkPostGet('page', '');
$param          = $_POST;

// insert
$kodegudang           = checkPostGet('kodegudang', '');
$kodebarang           = checkPostGet('kodebarang', '');
$jenis                = checkPostGet('jenis', '');
$jumlah               = checkPostGet('jumlah', '');
$harga                = checkPostGet('harga', '');
$notransreferensi     = checkPostGet('notransreferensi', '');
$keterangan           = checkPostGet('keterangan', '');
$id           = checkPostGet('id', '');
$tgladj               = tanggalsystemn(checkPostGet('tgladj', ''));
$urlefil=checkPostGet('urlefil','0');
$nmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optsatuan = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optnmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');


// posting
$id           = checkPostGet('id', '');

switch ($method) {

	case 'deletedata':
		try {
			$owlPDO->beginTransaction();

			$strUpdate_2 = "DELETE from " . $dbname . ".listfileupload WHERE notransaksi in (select notransaksi from " . $dbname . ".log_stopname_log_list WHERE id = '" . $id . "') ";
			$owlPDO->exec($strUpdate_2);
            
			$strUpdate = "DELETE from " . $dbname . ".log_stopname_log_list WHERE id = '" . $id . "'  ";
			$owlPDO->exec($strUpdate);


			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;

	case 'postingdata':
		try {
			$owlPDO->beginTransaction();

			$strUpdate = "UPDATE " . $dbname . ".log_stopname_log_list SET posting='1' WHERE id = '" . $id . "' ";
			$owlPDO->exec($strUpdate);

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;

	case 'savelistAdjustment':

		$owlPDO->beginTransaction();
		$harga      = str_replace(',', '', $harga);
		$jumlah     = str_replace(',', '', $jumlah);
		$tgl = date("YmdHis");

        $str="select left(notransaksi,3) as counter from ".$dbname.".log_stopname_log_list where 1=1 order by notransaksi desc limit 1";
        $res=fetchdata($str);
		$awal=$res[0]['counter']+1;
		$counter=addZero($awal,3);
			
        
        $bulan_a = substr($tgladj,5,2);
        $tahun_a = substr($tgladj,0,4);
        
		$notransaksi =  $counter."/".$bulan_a."/".$tahun_a."/ADJ"."/".substr($kodegudang,0,4);
        // echo "<pre>";
        // print_r($_FILES['file']);
        // exit("warning : ".$param['upload']." ");

		## INSERT
		$str = "insert into " . $dbname . ".log_stopname_log_list (id,notransaksi,kodegudang,kodebarang,jenis,jumlah,hargasatuan,tanggal,notransaksi_ref,keterangan,posting,createby,createtime,updateby,updatetime) 
        values ('','" . $notransaksi . "','" . $kodegudang . "','" . $kodebarang . "','" . $jenis . "','" . $jumlah . "','" . $harga . "','" . $tgladj . "','" . $notransreferensi . "','" . $keterangan . "','0','" . $_SESSION['standard']['userid'] . "','" . $tgl . "','" . $_SESSION['standard']['userid'] . "','" . $tgl . "')";
		// exit("warning : a");
		try {
			$owlPDO->exec($str);

            if ($param['upload'] != '') {
                
                $nopp = $notransaksi;

                // delete
                $strd = "delete from " . $dbname . ".listfileupload where notransaksi = '".$nopp."' ";
                $owlPDO->exec($strd);

                if ($_FILES['file']['error'] == 0) {
                    $filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
                    $newfilename = str_replace($filetype, '', $_FILES['file']['name']);
                    $filename = $newfilename . "_" . $tgl . "" . $filetype;
                    $file_tmpname = $_FILES['file']['tmp_name'];
                    // exit("warning : ".$filetype." - ".$_FILES['file']['name']." ");
                    if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx') || ($filetype == '.rar')) {
                        if ($_FILES['file']['size'] <= 250000) {
                            $str = "insert into " . $dbname . ".listfileupload values ('','" . $nopp . "','" . $filename . "','" . $filetype . "','others','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
                            try {
                                $owlPDO->exec($str);
                                move_uploaded_file($file_tmpname, "fileupload/adjustmentstock/$filename");
                            } catch (PDOException $e) {
                                echo " Gagal," . addslashes($e->getMessage());
                            }
                        } else {
                            exit("warning : Ukuran file upload maksimal 250kb");
                        }
                    } else {
                        exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
                    }
                }
            }
            $owlPDO->commit();
		} catch (PDOException $e) {
			$msgerr = $e->getMessage();
			if (strpos($msgerr, 'Duplicate entry') >= 0) {
				$msgerr = "Item ini sudah pernah diinput.";
			}
			print " Gagal : " . $msgerr . "\n";
			die();
		}
	break;

	case 'loaddata':
		$limit = 20;
		$page = 0;
		if (isset($pages)) {
			$page = $pages;
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$colspan = 24;

		$no = (($page * $limit));

		$str = "select * from " . $dbname . ".log_stopname_log_list where createby='" . $_SESSION['standard']['userid'] . "'  ";
		$res = fetchdata($str);
		$jlhbrs = count($res);

		if ($jlhbrs <= 0) {
			$tab .= "<tr class=rowcontent><td colspan='" . $colspan . "' style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			$str = "select * from " . $dbname . ".log_stopname_log_list where createby='" . $_SESSION['standard']['userid'] . "' order by createtime desc limit " . $offset . "," . $limit . "";
			$res = fetchdata($str);
			foreach ($res as $val) {
                $optnamakaryawan=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan");
                $optnamaorganisasi=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi");
				$no++;

				$tab .= "<tr class=rowcontent id='tr_" . $no . "'>
					<td align=center>" . $no . "</td>
					<td align=center>" . $val['notransaksi'] . "</td>
					<td align=center>" . $optnamaorganisasi[$val['kodegudang']] . "</td>
					<td align=center>" . $val['kodebarang'] . "</td>
					<td align=center>" . $nmbarang[$val['kodebarang']] . "</td>
					<td align=center>" . $val['jenis'] . "</td>
					<td align=center>" . number_format($val['jumlah']) . "</td>
					<td align=center>" . number_format($val['hargasatuan']) . "</td>
					<td align=center>" . $val['tanggal'] . "</td>
					<td align=center>" . $val['notransaksi_ref'] . "</td>
					<td align=center>" . $val['keterangan'] . "</td>
					<td align=center>" . $optnamakaryawan[$val['createby']] . "</td>";

				// ambil approval 				
				$str_x2 = "select status from " . $dbname . ".approval where notransaksi='" . $val['notransaksi'] . "' order by level desc limit 1 ";
				$res_x2 = fetchdata($str_x2);

				// belum di ajukan
				if ($res_x2[0]['status'] == '') {
					$tab .= "<td align=center><img src=images/skyblue/delete.png class=resicon class=zImgBtn height='30'  title='Delete' onclick=\"deletedata('" . $val['id'] . "');\" ></td>";
					$tab .= "<td align=center width=25px><img src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick=form_ajukan('" . $val['notransaksi'] . "','" . substr($val['kodegudang'], 0, 4) . "')></td>";
				}
				// proses persetujuan
				elseif ($res_x2[0]['status'] == '0') {
					$tab .= "<td align=center colspan='2'><label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('" . $val['notransaksi'] . "',event)\">Proses Persetujuan</label></td>";
					// $tab.="<td align=center><img src=images/skyblue/delete.png class=resicon class=zImgBtn height='30'  title='Delete' onclick=\"deletedata('".$val['id']."');\" ></td>";
					// $tab.="<td align=center width=25px><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted'></td>";
				} elseif ($res_x2[0]['status'] == '1') {
					if ($val['posting'] == 0) {
						$tab .= "<td align=center><img src=images/skyblue/delete.png class=resicon class=zImgBtn height='30'  title='Delete' onclick=\"deletedata('" . $val['id'] . "');\" ></td>";
						$tab .= "<td align=center width=25px><img src=images/icons/04/16/01.png class=zImgBtn height='30'  title='Posting' 
								onclick=\"saveAdjustmentPosting('" . $val['id'] . "','" . $val['kodebarang'] . "','" . $val['kodegudang'] . "','" . $val['jumlah'] . "','" . $val['hargasatuan'] . "','" . tanggalnormal($val['tanggal']) . "','" . $val['jenis'] . "','" . $val['notransaksi_ref'] . "','" . $val['keterangan'] . "');\" ></td>";
						
					} else {
						$tab .= "<td align=center width=25px></td>";
						$tab .= "<td align=center width=25px><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted'></td>";
					}
				} elseif ($res_x2[0]['status'] == '2') {
					$tab .= "<td align=center colspan='2'><label style='color:red;cursor:pointer' onclick=\"gethistoriapproval('" . $val['notransaksi'] . "',event)\">Ditolak</label></td>";
				}
				$tab .= "<td align=center width=25px><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"pdf('" . $val['id'] . "')\"></td>";



				$tab .= "</tr>";
			}

			## PAGING
			$tabft .= createpaging($jlhbrs, $limit, $page, $colspan, 'loadData', 'getPage');
		}

		echo $tab . "####" . $tabft;
	break;

	case 'form_ajukan':
		$notransaksi = checkPostGet('notransaksi', '');
		$unit = checkPostGet('unit', '');
		$tab = "<table cellspacing=1 border=0 class=sortable style='width:100%;margin-top:10px;padding:20px' cellpadding=5 align=center>";

        $jenisApp = 'ADJ';
        $countApp = getCountApproval($jenisApp, $unit);
        $namaorg_unit=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi");

        if($countApp <= 0){
            exit("warning : Untuk Unit ".$namaorg_unit[$unit]." jenis persetujuan ADJ belum di setupkan... ");
        }

        $i = 1;
        $arrList = listApprove($i, $jenisApp, $unit);
		$optpersetujuan="";
		foreach($arrList as $key => $val){
			$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
		}
        
        for ($i = 1; $i <= 1; $i++) {    
            $tab.="<tr>
            <td>".$_SESSION['lang']['kepada']."</td>
            <td>:</td>
            <td>
            <select id=persetujuan" . $i . ">". $optkaryawan."</select>
            </td>
            </tr>"; 
        }


		$tab .= "
		<tr>
		<style>
			.btn_ajukan_transit{
				width:100%;
				padding:7px;
				background-color:#c2d2e7;font-size:12px;color:green;border-color:green;
				font-weight:bold;
			}
		</style>
			<td align=center colspan=3><button id=ajukan_apv_aso  class='mybutton btn_ajukan_transit' onclick=ajukan_apv_aso('" . $notransaksi . "')>" . $_SESSION['lang']['diajukan'] . "</button></td>
		</tr>
		</table>";

		echo $tab;
	break;

	case 'ajukan_apv_aso':
		// Insert Approval
		$Arrpersetujuan = checkPostGet('level', '');
		$notransaksi = checkPostGet('notransaksi', '');
		$countApp_2 = count($Arrpersetujuan);
		$username_apv = makeOption($dbname, 'user', 'namauser,karyawanid');
		// for($i=1;$i<=$countApp_2;$i++)
		for ($i = 1; $i <= 1; $i++) {
			$str = "insert into " . $dbname . ".approval (nourut,notransaksi,jenispersetujuan,level,karyawanid,status,komentar,tanggal) values ('','" . $notransaksi . "','ADJ','" . $i . "','" . $_POST['persetujuan' . $i] . "','0','','')";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
	break;
 
	case'pdf':
			$tab='';
			$tab="<style>
				@page {
					margin-top: 15px;
					margin-left: 15px;
					margin-right: 15px;
					margin-bottom: 15px; 
				}
			</style>";
			 
			 
			$str = "SELECT * FROM ".$dbname.".log_stopname_log_list where id='".$id."'";
			$res=fetchdata($str);
			$notransaksi=$res[0]['notransaksi'];
			$kodebarang=$res[0]['kodebarang'];
			$jumlah=$res[0]['jumlah']; 
			$hargasatuan=$res[0]['hargasatuan']; 
			$jenis=$res[0]['jenis']; 
			$keterangan=$res[0]['keterangan']; 
			$tanggal=$res[0]['tanggal']; 
			$kodegudang=$res[0]['kodegudang']; 

			// $nmbarang=makeOption($dbname,'pmn_5ttd','nama,jabatan'); 
				
			
				$tab.="<table width=100% cellpadding=0 cellspacing=0 border=0 >
					<tr>
						<td width=18% style='vertical-align:top' align=left><label style='font-size:20px;font-weight:bold;' align=left> <b><u> PENYESUAIAN PERSEDIAAN </u></b></label></td>
						
					</tr>
				</table>  <br/>";
	 
	
				$tab.="<table width=100%  cellpadding=0 cellspacing=0 border=0 style='font-size:16px;'>
					<tr>
						<td> No  </td>    
						<td> : ".$notransaksi." </td>   
					</tr>
					<tr>
						<td> Tanggal </td>     
						<td>  : ".tglnmbln($tanggal,'I','long')." </td>   
					</tr>
					<tr>
						<td>Gudang </td>   
						<td> : ".$optnmorg[$kodegudang]." </td>   
					</tr>
				
	
				</table>   <br/>";
				$no=1;
				$tab.="<table width=100%  cellpadding=0 cellspacing=0 border=0 style='font-size:16px;'>
					<tr align=center>
						<td style='border:0.5px solid #000000;' rowspan=2> <b> No </b>  </td>    
						<td style='border:0.5px solid #000000;' colspan=3> <b> Barang </b></td>   
						<td style='border:0.5px solid #000000;' colspan=4> <b> Penyesuaian </b></td>   
						<td style='border:0.5px solid #000000;' rowspan=2> <b> Keterangan </b></td>   
					</tr> 
					<tr align=center>
						<td style='border:0.5px solid #000000;'> <b> Kode </b>   </td>    
						<td style='border:0.5px solid #000000;'> <b> Nama </b> </td>   
						<td style='border:0.5px solid #000000;'> <b> Satuan </b> </td>   
						<td style='border:0.5px solid #000000;'> <b> Jumlah </b> </td>   
						<td style='border:0.5px solid #000000;'> <b> Hrg  Satuan </b></td>   
						<td style='border:0.5px solid #000000;'> <b> Total </b> </td>   
						<td style='border:0.5px solid #000000;'> <b> Jenis </b> </td>   
					</tr> 

					<tr align=center>
						<td style='border:0.5px solid #000000;'> ".$no." </td>     
						<td style='border:0.5px solid #000000;'> ".$kodebarang." </td>     
						<td style='border:0.5px solid #000000;'> ".$nmbarang[$kodebarang]." </td>     
						<td style='border:0.5px solid #000000;'> ".$optsatuan[$kodebarang]." </td>     
						<td style='border:0.5px solid #000000;'> ".number_format($jumlah,2)." </td>     
						<td style='border:0.5px solid #000000;'> ".number_format($hargasatuan,2)." </td>     
						<td style='border:0.5px solid #000000;'> ".number_format($hargasatuan * $jumlah,2)." </td>     
						<td style='border:0.5px solid #000000;'> ".$jenis." </td>     
						<td style='border:0.5px solid #000000;'> ".$keterangan." </td>     
					</tr> 
				
	
				</table>   <br/>";

				$tab.="<table width=100% border=0 style='font-size:17px;'>
					<tr align=left>
						<td> <b> Catatan : </b> </td>     
					</tr>  
					<tr align=left>
						<td>".$keterangan."</td>     
					</tr>  
	
				</table>   ";

				$strc = "SELECT * FROM ".$dbname.".approval where notransaksi='".$notransaksi."'";
				$bar=fetchdata($strc);
				$jlhd=count($bar); 
				$notransaksi=$bar[0]['notransaksi'];
				$tgl1=$bar[0]['tanggal'];
				$tgl2=$bar[1]['tanggal'];
				$tglaju=$bar[0]['tanggalaju'];
				$nmaju=$bar[0]['namapengaju'];
				$p1=$bar[0]['username'];
				$p2=$bar[1]['username']; 

				if ($jlhd==1) { 
				
					$tab.="<table width=100% border=0  cellpadding=0 cellspacing=0 border=0 style='font-size:16px;'>";

						$tab.="<tr align=center>
							<td width=45%>  </td>     
							<td width=30% style='border:0.5px solid #000000;' align=center;> <b> Disetujui Oleh </b> </td>       
							<td width=30% style='border:0.5px solid #000000;' align=center;> <b> Dibuat Oleh </b> </td>     
						</tr>"; 
						$tab.="<tr align=left>
							<td height=8%> </td>     
							<td style='border:0.5px solid #000000;' > </td>       
							<td style='border:0.5px solid #000000;' > </td>     
						</tr> ";

						$tab.="<tr align=left>
							<td> </td>     
							<td style='border:0.5px solid #000000;' > Tanggal : ".tanggalnormal(substr($tgl1,0,10))."</td>    
							<td style='border:0.5px solid #000000;' > Tanggal : ".tanggalnormal(substr($tglaju,0,10))."</td>  
						</tr>"; 

						$tab.="<tr align=center>
							<td> </td>     
							<td style='border:0.5px solid #000000;'> ".$p1."</td>
							<td style='border:0.5px solid #000000;'> ".$nmaju."</td>  
						</tr>"; 
		
					$tab.="</table> ";
				} else { 
					$tab.="<table width=100% border=0  cellpadding=0 cellspacing=0 border=0 style='font-size:16px;'>";

						$tab.="<tr align=center>
							<td width=30%>  </td>     
							<td width=25% style='border:0.5px solid #000000;' align=center;> <b> Disetujui Oleh </b> </td>     
							<td width=25% style='border:0.5px solid #000000;' align=center;> <b> Diketahui Oleh</b> </td>     
							<td width=25% style='border:0.5px solid #000000;' align=center;> <b> Dibuat Oleh </b> </td>     
						</tr>"; 
						$tab.="<tr align=left>
							<td height=8%> </td>     
							<td style='border:0.5px solid #000000;' > </td>     
							<td style='border:0.5px solid #000000;' > </td>     
							<td style='border:0.5px solid #000000;' > </td>     
						</tr> ";

						$tab.="<tr align=left>
							<td> </td>     
							<td style='border:0.5px solid #000000;' > Tanggal : ".tanggalnormal(substr($tgl1,0,10))."</td>    
							<td style='border:0.5px solid #000000;' > Tanggal : ".tanggalnormal(substr($tgl2,0,10))."</td>    
							<td style='border:0.5px solid #000000;' > Tanggal : ".tanggalnormal(substr($tglaju,0,10))."</td>  
						</tr>"; 

						$tab.="<tr align=center>
							<td> </td>     
							<td style='border:0.5px solid #000000;'> ".strtoupper($p1)."</td>
							<td style='border:0.5px solid #000000;'> ".strtoupper($p2)."</td>
							<td style='border:0.5px solid #000000;'> ".strtoupper($nmaju)."</td>  
						</tr>";  
					$tab.="</table> ";  
				}
			$garis_bawah3 = str_repeat("__",47);
	
			$nmkarttd=getNamaKaryawan($penandatangan); 
			$garisbawahttd = str_repeat("_",$htgnmkar); 
  
	
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$dompdf->stream("PENYESUAIAN PERSEDIAAN",array("Attachment"=>0));
	break;
}
