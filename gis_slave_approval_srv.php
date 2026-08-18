<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method = checkPostGet('method', '');
$proses = checkPostGet('proses', '');
$level = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom = checkPostGet('kolom', '');
$comment = checkPostGet('comment', '');
$userid = checkPostGet('userid', '');
$tipe = checkPostGet('tipe', '');
$tglskrng = date("Y-m-d H:i:s");
$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');
$path   = "fileupload/gis_survey/";
$data = $_POST;
//exit('error '.$method);
switch ($method) {
case 'getdetail':
	case'SRV':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
			<td align=center>".$_SESSION['lang']['tanggalselesai']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['dokumen']."</td>
			<td colspan='3' align='center'>Verification</td>";
		$countApp = getCountApproval('SRV');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('SRV');
		$str = "select * from ".$dbname.".approval a
			left join ".$dbname.".gis_survey b on a.notransaksi = b.notransaksi
			where a.jenispersetujuan='SRV' and a.status='0' and a.karyawanid='".$_SESSION['standard']['userid']."' order by a.tanggal asc";
		//exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$notransaksi = $bar['notransaksi'];
			$kodeorg = $bar['kodeorg'];
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$kodeorg."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".$bar['tanggalmulai']."</td>
				<td align=left>".$bar['tanggalselesai']."</td>
				<td align=left>".$kodeorg." - ".$optNmOrg[$kodeorg]."</td>
				<td align=left><img src='images/pdf.jpg' class=resicon  title='Print Rencana Anggaran' onclick=\"masterPDF('gis_survey','" . $notransaksi . "," . $kodeorg . "','','gis_slave_RABPDF',event)\"><img src='images/pdf.jpg' class=resicon  title='Print Form Survey' onclick=\"masterPDF('gis_survey','" . $notransaksi . "," . $kodeorg . "','','gis_slave_formsurveyPDF',event)\"><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$notransaksi."','1')\" src='images/upload-2-xxl.png'/></td><";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'SRV');
				if ($arrDetail['karyawanid'] == $_SESSION['standard']['userid'] && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					$level = $arrDetail['level'];
					$showaction = 1;
					if ($i >= 2) {
						$countubahjumlah = 1;
					}
				}
			}
			if ($showaction == 1) {
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatasrv('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolaksrv('".$bar['notransaksi']."','".$level."','koreksi')\">".$_SESSION['lang']['koreksi']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolaksrv('".$bar['notransaksi']."','".$level."','tolak')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=4>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'SRV');
				if ($arrDetail['nama'] != '') {
					$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
				} else {
					$tab.="<td style='text-align:center'>-</td>";
				}
			}
			$tab.="</tr>";
		}
		$tab.="</tbody>
			<tfoot>
			</tfoot>
			</table>
			</fieldset>";
		break;
	break;
	case 'showupload':
                        $tab="";
                        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
                        $tab.="<tr>
                        <td>".$_SESSION['lang']['notransaksi']."</td>
                        <td>:</td>
                        <td>
                        <label id='noupload' style='display:none'>".$data['notransaksi']."</label>
                        <label style='font-weight:bold'>".$data['notransaksi']."</label>
                        </td>
                        </tr>";

                        $tab.="<tr><td colspan=4><hr></td></tr>
                        <tr>
                        <td>Filename</td>
                        <td>:</td>
                        <td>
                        <input type='file' name='upload' id='upload' >
                        </td>
                        </tr>";
                        if($data['posting']<2 or $data['posting']=17 or $data['posting']=3)
                        {
                            $tab.="<tr>
                            <td colspan=2></td>
                            <td>
                            <button class=mybutton onclick=\"submitfile()\">Submit</button>
                            </td>
                            </tr>";
                        }
                        else
                        {
                            $tab.="<tr hidden>
                            <td colspan=2></td>
                            <td>
                            <button class=mybutton onclick=\"submitfile()\">Submit</button>
                            </td>
                            </tr>";
                           
                        }
                        
                        $tab.="</table>
                        <p />";
                        
                        $tab.="<fieldset>
                        <legend>".$_SESSION['lang']['list']."</legend>
                        <table class='sortable' cellspacing='1' border='0' width=100%>
                        <thead>
                        <tr class=rowheader>
                        <td align='center' width=50px>No.</td>
                        <td align='center' width=50px>File Type</td>
                        <td align='center'>Filename</td>
                        <td align='center' width=50px>Action</td>
                        </tr>
                        </thead>
                        <tbody id='listfiles'>
                        </tbody>
                        </table>
                        </fieldset> ";
                        
                        echo $tab;
                        break;
	case 'submitfile':
                        $tgl = date("YmdHis");
                        $his = date("His");
                        $nmTemp=str_replace('-','',str_replace('/','',$data['notransaksi']));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
                        if($data['fileupload']!=''){
                            if($_FILES['file']['error']==0){    
                                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                                $filename = $nmTemp."_".$his."".$filetype;
                                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                                
                                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                                    $str = "insert into ".$dbname.".listfile_gis_survey values ('','".$data['notransaksi']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
                                    exit("Warning : Format file upload tidak boleh ".$filetype);
                                }
                            }
                        }
                        break;
                        case'viewfile':
                        $tab="";
                        $tab.="<img src='".$path.$data['namafile']."' style='width:600px;height:400px;'>";
                        
                        echo $tab;
                        break;
                        
                        case 'deletefile':
                        $namafile=$data['namafile'];
        $str="delete from ".$dbname.".listfile_gis_survey where notransaksi='".$data['notransaksi']."' and namafile='".$data['namafile']."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
        case'viewlistfile':
        $tab.="<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
        <table class='sortable' cellspacing='1' border='0' style=min-width:350px>
        <thead>
        <tr class=rowheader>
        <td align='center' width=50px>No.</td>
        <td align='center' width=50px>File Type</td>
        <td align='center'>Filename</td>
        <td align='center' width=50px>Action</td>
        </tr>
        </thead>
        <tbody id='loadfilesdetail'>
        </tbody>
        </table>
        </fieldset> ";
        echo $tab;
        break;
        
        case 'deletefileall':
        $str="select * from ".$dbname.".listfile_gis_survey where notransaksi='".$data['notransaksi']."'"; //exit('error'.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $pathx = $path.$bar['namafile'];
            unlink($pathx);
        }
        
        $str="delete from ".$dbname.".listfile_gis_survey where notransaksi='".$data['notransaksi']."'";
        try{
            $owlPDO->exec($str);
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
        case 'loadfiles':
        $no = 0;
        $tab = "";  
        $str="select * from ".$dbname.".gis_survey where notransaksi = '".$data['notransaksi']."'";
        $res=fetchData($str);
        $posting=$res[0]['posting'];
        
        $str="select * from ".$dbname.".listfile_gis_survey where notransaksi = '".$data['notransaksi']."' and status='1'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>";
                @$icon = seticonfile($val['formaticon']);   
                $tab.="<td style='text-align:center'>
                <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";

                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($data['posting']<2 or $data['posting']=17 or $data['posting']=3){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";                 
                }
                
                $tab."  </td>
                </tr>";
            }   
        }
        
        echo $tab;
        break;
	case'get_form_approval':
		$tab="";
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[2];
		//print_r($temporg);
		$countApp = getCountApproval('SRV',$koderorg);
		//print_r($countApp);
		for($i=1;$i<=$countApp;$i++){
			$arrDetail = detailApprove($i,$notransaksi,'SRV');
			if($_SESSION['standard']['userid']==$arrDetail['karyawanid']){
				if($i == $countApp){
					$tab.="<div id=approve>
						<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						<table cellspacing=1 border=0>
							<tr>
								<td colspan=3>Approved</td>
							</tr>
							<tr>
								<td colspan=3><hr></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['note']."</td>
								<td>:</td>
								<td>
									<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button id=Ajukan class=mybutton onclick=nextapprovalsrv('approved') >Approved</button>
								</td>
							</tr>
						</table>
                    </div>";
				}else{
					$level = $i+1;
					$arrListApp = listApprove($level,'SRV',$koderorg);
					foreach($arrListApp as $key=>$val){
						$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
					}
					$tab.="<div id=test style=display:block>
                        <input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						<input hidden id=kolom value=".$_POST['kolom']."  />
                        <table cellspacing=1 border=0>
							<tr>
								<td colspan=3>Submit to the next approval :</td>
							</tr>
							<tr>
								<td colspan=3><hr></td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['namakaryawan']."</td>
								<td>:</td>
								<td valign=top>
									<select id=user_id name=user_id  style=\"width:150px;\">".$optKry."</select>
								</td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['note']."</td>
								<td>:</td>
								<td>
									<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
								</td>
							</tr>
								<td colspan=2></td>
								<td>
									<button class=mybutton onclick=nextapprovalsrv() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
								</td>
							</tr>
						</table>
                        <input type=hidden name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
					</div>";
				}
            }
        }
		echo $tab;
	break;
	case 'insert_nextapproval':
	if($userid==''){
		$user_id = $_SESSION['standard']['userid'];
	}else{
		$user_id = $userid;
	}
	
	$temporg = explode("/",$notransaksi);
	$koderorg=$temporg[2];
	$countApp = getCountApproval('SRV', $koderorg);
	$tglskrng = date("Y-m-d H:i:s");
	$str = "select * from ".$dbname.".gis_survey where `notransaksi`='".$notransaksi."'"; #exit('error sasas'.$str);
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	if ($bar['statuspersetujuan'] == 1) {
		exit("Warning : Sudah di Approved");
	}else if($bar['statuspersetujuan'] == 0) {
		$arrDetail = detailApprove($kolom, $notransaksi, 'SRV');
		$level = $kolom + 1;
		if ($kolom != $countApp) {
			if ($user_id == $arrDetail['karyawanid']) {
				exit("Warning : ".getNamaKaryawan($user_id)." Sudah di gunakan");
			}else if($user_id == $bar['createdby']) {
				exit("Warning : ".getNamaKaryawan($user_id)." Pembuat Transaksi");
			} else {
				$strx = "insert into ".$dbname.".approval values ('','".$notransaksi."','SRV','".$level."','".$user_id."','0','','','')";
				try {
					$owlPDO->exec($strx);
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$_SESSION['standard']['userid']."'";
					try {$owlPDO->exec($strx);
						#mailCoy($user_id);
						#exit();
					} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
					}
				} catch (PDOException $e) {
					print " Gagal  !: ".$e->getMessage()."\n";
					die();
				}
			}
		} else {
						$strz = "select * from ".$dbname.".organisasi where kodeorganisasi='".$bar['kodeorg']."'";
						$resz = $owlPDO->query($strz)or die(print " Gagal: ".PDOException::getMessage());
						$resz->setFetchMode(PDO::FETCH_ASSOC);
						$barz = $resz->fetch();
						$stru = "select * from ".$dbname.".gis_survey_anggaranht where notransaksi='".$notransaksi."'";
						$resu = $owlPDO->query($stru)or die(print " Gagal: ".PDOException::getMessage());
						$resu->setFetchMode(PDO::FETCH_ASSOC);
						$baru = $resu->fetch();
						$kodejurnal='UMPD';
		            	//Parameter jurnal noakun debet dan kredit
		            	$strpj="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$kodejurnal."'";
						$respj=fetchData($strpj);
						$barpj=$respj[0];
						$noakundebet=$barpj['noakundebet'];
						$noakunkredit=$barpj['noakunkredit'];

			            $noinvoice=date('Ymdhis');
		                $keterangan="Uang Survery berdasarkan notransaksi: ".$notransaksi."";

		                $insht="insert into ".$dbname.".keu_tagihanht(noinvoice, tipeinvoice, tanggal, nopo, kodesupplier, nilaiinvoice, keterangan, keterangan2, noakun, matauang, kurs, posting, kodeorg, unit, updateby, postingby) values 
		                ('".$noinvoice."','','".date('Y-m-d')."','".$notransaksi."','".$bar['createdby']."','".$baru['totalbiaya']."','','".$keterangan."','".$noakunkredit."','IDR','1','1','".$barz['induk']."','".$barz['kodeorganisasi']."','".$bar['createdby']."','".$bar['createdby']."')";
		                //exit($strpj);
		                try {
		                    $owlPDO->exec($insht);

		                    $ins="insert into ".$dbname.".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset) values 
		                      ('".$noinvoice."','".$noakundebet."','".$baru['totalbiaya']."','','')";
		                    try{
		                        $owlPDO->exec($ins);
		                    } catch (PDOException $e) {
		                        print " Gagal: " . $e->getMessage() . "\n";
		                        die();
		                    }

		                } catch (PDOException $e) {
		                    print " Gagal: " . $e->getMessage() . "\n";
		                    die();
		                }

		                $kodejurnal="TGH01";  
		                $tgljurnal=date('Ymd');

		                # Get Journal Counter
		                $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
		                    "kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
		                $tmpKonter = fetchData($queryJ);
		                $konter = addZero($tmpKonter[0]['nokounter']+1,3);
		                # Prep No Jurnal
		                $notrans=$tgljurnal."/".$bar['kodeorg']."/".$kodejurnal."/".$konter;

				        //insert jurnalht
						$strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) values 
								('".$notrans."','".$kodejurnal."','".$baru['totalbiaya']."','".-($baru['totalbiaya'])."','".$tgljurnal."','".date('Ymd')."','1','".$noinvoice."','IDR','1')";
						try
			            {
			                $owlPDO->exec($strht);

							//insert jurnalht debet
				            $str="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
				            values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','Uang  Survey berdasarkan noinvoice:".$noinvoice." dan notransaksi:".$notransaksi.";','".$baru['totalbiaya']."','IDR','1','".$bar['kodeorg']."','".$noinvoice."','".$notransaksi."')";
				            try
				            {
				                $owlPDO->exec($str);
				            }
				            catch (PDOException $e)
				            {
				                print " Gagal  !: " . $e->getMessage() . "\n";
				                die();
				            }

				            //insert jurnalht kredit
				            $str="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
				            values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','Jurnal Otomatis Atas Uang  Survey berdasarkan noinvoice:".$noinvoice." dan notransaksi:".$notransaksi.";','".-($baru['totalbiaya'])."','IDR','1','".$bar['kodeorg']."','".$noinvoice."','".$notransaksi."')";
				            try
				            {
				                $owlPDO->exec($str);
				            }
				            catch (PDOException $e)
				            {
				                print " Gagal  !: " . $e->getMessage() . "\n";
				                die();
				            }
				            
				        }catch (PDOException $e){
			                print " Gagal  !: " . $e->getMessage() . "\n";
			                die();
			            }

			            $strht="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
	                    try{
	                        $owlPDO->exec($strht);
	                    }catch (PDOException $e){
	                        echo "Gagal : ".$e->getMessage();
	                        die();
	                    }
					$strx = "update ".$dbname.".gis_survey set statuspersetujuan='1', posting='2' where `notransaksi`='".$notransaksi."'";
					try {
							$owlPDO->exec($strx);
							$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$_SESSION['standard']['userid']."'";
							try {
									$owlPDO->exec($strx);
								} catch (PDOException $e) {
									print " Gagal  !: ".$e->getMessage()."\n";
									die();
								}
						} catch (PDOException $e) {
						print " Gagal  !: ".$e->getMessage()."\n";
						die();
						} 
			}
		}
	
	break;
	case 'tolak':
		 echo"<div id=rejected_form>
		<input hidden id=notransaksi value=".$_POST['notransaksi']."  />
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		 Rejection</td></tr>
		<tr>
		<tr><td colspan=3><hr></td></tr>
		<td>".$_SESSION['lang']['note']."</td>
		<td>:</td>
		<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
		</tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=\"inserttolaksrv(".$_POST['kolom'].",'".$tipe."')\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;
		$temporg = explode("/",$notransaksi);
		$koderorg=$temporg[4];
		$countApp = getCountApproval('SRV',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'SRV');
		$tglskrng=date("Y-m-d H:i:s");
		//exit('Error :'.$tipe);
		if($tipe=='tolak'){
		$str="update ".$dbname.".gis_survey set statuspersetujuan='3',posting='15' where notransaksi='".$notransaksi."'" ;
		}
		else
		{
		$str="update ".$dbname.".gis_survey set statuspersetujuan='2',posting='17' where notransaksi='".$notransaksi."'" ;
		}
		try{$owlPDO->exec($str); 
			if($tipe=='tolak'){
			$str="update ".$dbname.".approval set status='3',komentar='".$comment."',tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			}
			else
			{
			$str="update ".$dbname.".approval set status='2',komentar='".$comment."',tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			}
			try{$owlPDO->exec($str); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
}
?>